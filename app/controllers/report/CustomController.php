<?php
/**
 * FILE: app/controllers/CustomReportController.php
 *
 * Custom Stock Report Controller
 * — Summary Report: Group-wise count (Received, Issued, Deleted, Balance)
 * — Detailed Report: Indent-wise with stock book page numbers
 * — Institution & Department filtering
 * — PDF export for both reports
 *
 * Routes:
 *   GET  customreport/                        → index()
 *   GET  customreport/getDepartments          → getDepartments()
 *   GET  customreport/getSummaryData          → getSummaryData()
 *   GET  customreport/getDetailedData         → getDetailedData()
 *   GET  customreport/exportSummaryPdf        → exportSummaryPdf()
 *   GET  customreport/exportDetailedPdf       → exportDetailedPdf()
 */
class CustomController extends Controller
{
    /* ─────────────────────────────────────────────
       INDEX  — render view with institution list
    ───────────────────────────────────────────── */
    public function index()
    {
        $db = new Database();
        $institutions = $db->selectData('college_t', 'id, college_name', ['display' => 'Y']);

        $data = [
            'title' => 'Custom Stock Reports',
            'institutions' => $institutions ?: [],
        ];

        $this->viewWithLayout('reports/custom_report_view', $data);
    }

    /* ─────────────────────────────────────────────
       GET DEPARTMENTS  (AJAX cascade)
    ───────────────────────────────────────────── */
    public function getDepartments()
    {
        header('Content-Type: application/json');
        $db = new Database();
        $inst = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;

        $rows = $inst
            ? $db->customQuery("
                SELECT id, department_name
                FROM department_master_t
                WHERE display='Y' AND (college_id=$inst OR college_id=0)
                ORDER BY department_name")
            : $db->selectData('department_master_t', 'id, department_name', ['display' => 'Y']);

        echo json_encode(['success' => true, 'data' => $rows ?: []]);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       SUMMARY DATA  — Group-wise count
    ═════════════════════════════════════════════════════════════ */
    public function getSummaryData()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        $where = "WHERE sb.display='Y' AND st.display='Y'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }

        $rows = $db->customQuery("
                            SELECT 
                        COALESCE(gm.group_name,'Ungrouped') AS group_name,
                
                        COUNT(DISTINCT sb.id) AS total_items,

                        /* RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type IN ('RECEIPT','TRANSFER')
                            THEN st.receipt_qty ELSE 0 END
                        ) AS total_received,

                        /* INDENT RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type='RECEIPT'
                            AND st.stock_entry_type='INDENT_BASED'
                            THEN st.receipt_qty ELSE 0 END
                        ) AS indent_received,

                        /* TRANSFER RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type='TRANSFER'
                            THEN st.receipt_qty ELSE 0 END
                        ) AS transfer_received,

                        /* ISSUED */
                        SUM(CASE 
                            WHEN st.transaction_type='ISSUE'
                            AND st.item_status <> 'DELETED'
                            THEN st.issue_qty ELSE 0 END
                        ) AS total_issued,

                        /* DELETED FROM INDENT */
                        SUM(CASE 
                            WHEN st.item_status='DELETED'
                            THEN ii.qty_intended ELSE 0 END
                        ) AS total_deleted,

                        /* BALANCE */
                        (
                            SUM(CASE 
                                WHEN st.transaction_type IN ('RECEIPT','TRANSFER')
                                THEN st.receipt_qty ELSE 0 END)
                            -
                            SUM(CASE 
                                WHEN st.transaction_type='ISSUE'
                                AND st.item_status <> 'DELETED'
                                THEN st.issue_qty ELSE 0 END)
                            -
                            SUM(CASE 
                                WHEN st.item_status='DELETED'
                                THEN ii.qty_intended ELSE 0 END)
                        ) AS total_balance

                    FROM stock_book_t sb

                    LEFT JOIN stock_transaction_t st 
                        ON st.stock_book_id = sb.id

                    LEFT JOIN indent_item_t ii 
                        ON ii.id = st.indent_item_id
                    LEFT JOIN item_master_t im 
                        ON im.id = sb.item_id
                    LEFT JOIN group_item_name_master_t gm 
                        ON gm.id = im.group_id
                    $where
                    GROUP BY gm.id
                    ORDER BY group_name ASC;
        ");

        echo json_encode([
            'success' => true,
            'data' => $rows ?: [],
            'count' => count($rows ?? [])
        ]);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       DETAILED DATA  — Indent-wise with stock book page no
    ═════════════════════════════════════════════════════════════ */
    public function getDetailedData()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
        $group = $_GET['group'] ?? null;

        $where = "WHERE sb.display='Y' AND st.display='Y'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }
        if ($group && $group !== 'ALL') {
            $where .= " AND COALESCE(gm.group_name, 'Ungrouped') = '" . $db->escape($group) . "'";
        }

        $rows = $db->customQuery("
            SELECT 
                ROW_NUMBER() OVER (
                    PARTITION BY ii.group_id 
                    ORDER BY gm.group_name, im_i.indent_no, st.transaction_date
                ) AS serial_no,

                COALESCE(gm.group_name, 'Ungrouped') AS group_name,
                COALESCE(im_i.indent_no, 'N/A') AS indent_no,
                DATE_FORMAT(im_i.indent_date, '%d-%m-%Y') AS indent_date,

                sb.id AS stock_book_id,
                ii.stock_book_page_no AS stockbook_page_no,
                sb.item_id,
                imt.item_name,
                ii.item_description,
                mk.make_name,
                md.model_name,

                /* RECEIVED */
                COALESCE(SUM(CASE 
                    WHEN st.transaction_type IN ('RECEIPT','TRANSFER') 
                    THEN st.receipt_qty END),0) AS total_received,

                /* INDENT RECEIVED */
                COALESCE(SUM(CASE 
                    WHEN st.transaction_type='RECEIPT'
                    AND st.stock_entry_type='INDENT_BASED'
                    THEN st.receipt_qty END),0) AS indent_received,

                /* TRANSFER RECEIVED */
                COALESCE(SUM(CASE 
                    WHEN st.transaction_type='TRANSFER'
                    THEN st.receipt_qty END),0) AS transfer_received,

                /* ISSUED */
                COALESCE(SUM(CASE 
                    WHEN st.transaction_type='ISSUE'
                    THEN st.issue_qty END),0) AS total_issued,

                /* DELETED */
                COALESCE(SUM(CASE 
                    WHEN st.item_status='DELETED' AND st.transaction_type <> 'ISSUE'
                    THEN ii.qty_intended 
                    ELSE 0 
                END),0) AS total_deleted,

                /* BALANCE */
                COALESCE(MAX(st.balance_qty),0) AS balance_qty,

                /* FIXED COLUMN SOURCE */
                sb.location AS location,

                /* remarks should not be grouped raw → aggregate */
                MAX(st.remarks) AS remarks

            FROM stock_book_t sb

            LEFT JOIN indent_item_t ii 
                ON ii.id = (
                    SELECT ii2.id 
                    FROM indent_item_t ii2
                    WHERE ii2.item_id = sb.item_id
                    ORDER BY ii2.created_at DESC
                    LIMIT 1
                )

            LEFT JOIN group_item_name_master_t gm ON gm.id = ii.group_id
            LEFT JOIN indent_master_t im_i ON im_i.id = ii.indent_id
            LEFT JOIN item_master_t imt ON imt.id = sb.item_id
            LEFT JOIN make_t mk ON mk.id = ii.make_id
            LEFT JOIN model_t md ON md.id = ii.model_id
            LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id

            $where

            GROUP BY 
                sb.id, ii.id, gm.id, im_i.id, imt.id, mk.id, md.id

            ORDER BY 
                gm.group_name ASC,
                im_i.indent_no ASC,
                im_i.indent_date ASC,
                sb.id ASC;
        ");

        echo json_encode([
            'success' => true,
            'data' => $rows ?: [],
            'count' => count($rows ?? [])
        ]);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       EXPORT SUMMARY PDF
    ═════════════════════════════════════════════════════════════ */
    public function exportSummaryPdf()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE sb.display='Y' AND st.display='Y'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }

        $rows = $db->customQuery("
            SELECT 
                        COALESCE(gm.group_name,'Ungrouped') AS group_name,
                
                        COUNT(DISTINCT sb.id) AS total_items,

                        /* RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type IN ('RECEIPT','TRANSFER')
                            THEN st.receipt_qty ELSE 0 END
                        ) AS total_received,

                        /* INDENT RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type='RECEIPT'
                            AND st.stock_entry_type='INDENT_BASED'
                            THEN st.receipt_qty ELSE 0 END
                        ) AS indent_received,

                        /* TRANSFER RECEIVED */
                        SUM(CASE 
                            WHEN st.transaction_type='TRANSFER'
                            THEN st.receipt_qty ELSE 0 END
                        ) AS transfer_received,

                        /* ISSUED */
                        SUM(CASE 
                            WHEN st.transaction_type='ISSUE'
                            AND st.item_status <> 'DELETED'
                            THEN st.issue_qty ELSE 0 END
                        ) AS total_issued,

                        /* DELETED FROM INDENT */
                        SUM(CASE 
                            WHEN st.item_status='DELETED'
                            THEN ii.qty_intended ELSE 0 END
                        ) AS deleted_count,

                        /* BALANCE */
                        (
                            SUM(CASE 
                                WHEN st.transaction_type IN ('RECEIPT','TRANSFER')
                                THEN st.receipt_qty ELSE 0 END)
                            -
                            SUM(CASE 
                                WHEN st.transaction_type='ISSUE'
                                AND st.item_status <> 'DELETED'
                                THEN st.issue_qty ELSE 0 END)
                            -
                            SUM(CASE 
                                WHEN st.item_status='DELETED'
                                THEN ii.qty_intended ELSE 0 END)
                        ) AS total_balance

                    FROM stock_book_t sb

                    LEFT JOIN stock_transaction_t st 
                        ON st.stock_book_id = sb.id

                    LEFT JOIN indent_item_t ii 
                        ON ii.id = st.indent_item_id
                    LEFT JOIN item_master_t im 
                        ON im.id = sb.item_id
                    LEFT JOIN group_item_name_master_t gm 
                        ON gm.id = im.group_id
                    $where
                    GROUP BY gm.id
                    ORDER BY group_name ASC;
        ");

        $pdf = $this->_pdfBaseStyles('landscape');
        $pdf .= '<div class="page-wrap">';
        
        /* Header */
        $pdf .= '
        <table style="width:100%;margin-bottom:15px">
            <tr>
                <td>
                    <div style="font-size:14px;font-weight:bold">' . htmlspecialchars($collegeName ?: 'Government Polytechnic College') . '</div>
                    ' . ($deptName ? '<div style="font-size:11px;font-weight:bold;color:#333">' . htmlspecialchars($deptName) . '</div>' : '') . '
                </td>
                <td style="text-align:right">
                    <div style="font-size:12px;color:#666">Period: ' . ($from && $to ? date('d M Y', strtotime($from)) . ' to ' . date('d M Y', strtotime($to)) : 'All') . '</div>
                </td>
            </tr>
        </table>

        <div style="text-align:center;font-size:13px;font-weight:bold;border-top:2px solid #111;border-bottom:2px solid #111;padding:6px 0;margin-bottom:12px">
            STOCK SUMMARY REPORT
        </div>
        ';

        /* Summary Table */
        $pdf .= '
        <table style="width:100%;border-collapse:collapse;font-size:10px">
            <thead>
                <tr style="background:#f0f0f0;border-bottom:2px solid #111">
                    <th style="border:1px solid #ccc;padding:6px;text-align:center;width:40px">S.No</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left">Group/Item Name</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Total Items</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Indent Received</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Transfer Received</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Total Received</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Total Issued</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center">Deleted</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold">Balance</th>
                </tr>
            </thead>
            <tbody>
        ';

        $sno = 1;
        $totalItems = 0;
        $totalIndentRecv = 0;
        $totalTransferRecv = 0;
        $totalRecv = 0;
        $totalIssued = 0;
        $totalDeleted = 0;
        $totalBalance = 0;

        foreach ($rows as $row) {
            $pdf .= '
            <tr>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $sno++ . '</td>
                <td style="border:1px solid #ccc;padding:6px">' . htmlspecialchars($row['group_name']) . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $row['total_items'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $row['indent_received'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $row['transfer_received'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold">' . $row['total_received'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;color:#c62828">' . $row['total_issued'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;color:#d32f2f;font-weight:bold">' . $row['deleted_count'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold;background:#e8f5e9">' . $row['total_balance'] . '</td>
            </tr>
            ';
            
            $totalItems += $row['total_items'];
            $totalIndentRecv += $row['indent_received'];
            $totalTransferRecv += $row['transfer_received'];
            $totalRecv += $row['total_received'];
            $totalIssued += $row['total_issued'];
            $totalDeleted += $row['deleted_count'];
            $totalBalance += $row['total_balance'];
        }

        $pdf .= '
            <tr style="background:#f0f0f0;font-weight:bold;border-top:2px solid #111">
                <td style="border:1px solid #ccc;padding:6px;text-align:center" colspan="2">TOTAL</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalItems . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalIndentRecv . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalTransferRecv . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalRecv . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalIssued . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalDeleted . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;background:#c8e6c9">' . $totalBalance . '</td>
            </tr>
        </tbody>
        </table>

        <div style="margin-top:15px;font-size:9px;color:#666">
            Generated: ' . date('d M Y, h:i A') . '
        </div>';

        $pdf .= '</div>';
        $this->_renderPdf($pdf, 'stock_summary_report', 'landscape');
    }

    /* ═════════════════════════════════════════════════════════════
       EXPORT DETAILED PDF
    ═════════════════════════════════════════════════════════════ */
    public function exportDetailedPdf()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE sb.display='Y' AND st.display='Y'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }

        $rows = $db->customQuery("
            SELECT 
                ROW_NUMBER() OVER (PARTITION BY ii.group_id ORDER BY gm.group_name, im_i.indent_no, st.transaction_date) AS serial_no,
                COALESCE(gm.group_name, 'Ungrouped') AS group_name,
                COALESCE(im_i.indent_no, 'N/A') AS indent_no,
                DATE_FORMAT(im_i.indent_date, '%d-%m-%Y') AS indent_date,
                sb.id AS stock_book_id,
                ii.stock_book_page_no AS stockbook_page_no,
                sb.item_id,
                imt.item_name,
                ii.item_description,
                mk.make_name,
                md.model_name,
                COALESCE(SUM(CASE WHEN st.transaction_type IN ('RECEIPT', 'TRANSFER') THEN st.receipt_qty ELSE 0 END), 0) AS total_received,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'RECEIPT' AND st.stock_entry_type = 'INDENT_BASED' THEN st.receipt_qty ELSE 0 END), 0) AS indent_received,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'TRANSFER' THEN st.receipt_qty ELSE 0 END), 0) AS transfer_received,
                COALESCE(SUM(CASE WHEN st.transaction_type = 'ISSUE' THEN st.issue_qty ELSE 0 END), 0) AS total_issued,
                COALESCE(SUM(CASE WHEN st.item_status = 'DELETED' THEN 1 ELSE 0 END), 0) AS deleted_count,
                COALESCE(MAX(st.balance_qty), 0) AS balance_qty
            FROM stock_book_t sb
            LEFT JOIN indent_item_t ii ON ii.id = (
                SELECT ii2.id FROM indent_item_t ii2 
                WHERE ii2.item_id = sb.item_id 
                ORDER BY ii2.created_at DESC LIMIT 1
            )
            LEFT JOIN group_item_name_master_t gm ON gm.id = ii.group_id
            LEFT JOIN indent_master_t im_i ON im_i.id = ii.indent_id
            LEFT JOIN item_master_t imt ON imt.id = sb.item_id
            LEFT JOIN make_t mk ON mk.id = ii.make_id
            LEFT JOIN model_t md ON md.id = ii.model_id
            LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id
            $where
            GROUP BY sb.id, ii.id, gm.id, im_i.id, imt.id, mk.id, md.id
            ORDER BY gm.group_name ASC, im_i.indent_no ASC, im_i.indent_date ASC, sb.id ASC
        ");

        $pdf = $this->_pdfBaseStyles('landscape');
        $pdf .= '<div class="page-wrap">';
        
        /* Header */
        $pdf .= '
        <table style="width:100%;margin-bottom:15px">
            <tr>
                <td>
                    <div style="font-size:14px;font-weight:bold">' . htmlspecialchars($collegeName ?: 'Government Polytechnic College') . '</div>
                    ' . ($deptName ? '<div style="font-size:11px;font-weight:bold;color:#333">' . htmlspecialchars($deptName) . '</div>' : '') . '
                </td>
                <td style="text-align:right">
                    <div style="font-size:12px;color:#666">Period: ' . ($from && $to ? date('d M Y', strtotime($from)) . ' to ' . date('d M Y', strtotime($to)) : 'All') . '</div>
                </td>
            </tr>
        </table>

        <div style="text-align:center;font-size:13px;font-weight:bold;border-top:2px solid #111;border-bottom:2px solid #111;padding:6px 0;margin-bottom:12px">
            STOCK DETAILED REPORT
        </div>
        ';

        /* Detailed Table */
        $pdf .= '
        <table style="width:100%;border-collapse:collapse;font-size:9px">
            <thead>
                <tr style="background:#f0f0f0;border-bottom:2px solid #111">
                    <th style="border:1px solid #ccc;padding:4px;text-align:center;width:35px">S.No</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:left">Group/Item</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Indent No</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Indent Date</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">SB Page</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Make/Model</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Rcvd(I)</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Rcvd(T)</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Total Rcvd</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Issued</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Deleted</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center;font-weight:bold">Balance</th>
                </tr>
            </thead>
            <tbody>
        ';

        $sno = 1;
        foreach ($rows as $row) {
            $makeModel = $row['item_name'].'-'.($row['make_name'] ? $row['make_name'] : '') . 
                        ($row['model_name'] ? ' / ' . $row['model_name'] : '').'-'.$row['item_description'];
            
            $pdf .= '
            <tr>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $sno++ . '</td>
                <td style="border:1px solid #ccc;padding:4px">' . htmlspecialchars($row['group_name']) .''. '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . htmlspecialchars($row['indent_no']) . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $row['indent_date'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . ($row['stockbook_page_no'] ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;font-size:8px">' . htmlspecialchars($makeModel ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $row['indent_received'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $row['transfer_received'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;font-weight:bold">' . $row['total_received'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#c62828">' . $row['total_issued'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#d32f2f">' . $row['deleted_count'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;font-weight:bold;background:#e8f5e9">' . $row['balance_qty'] . '</td>
            </tr>
            ';
        }

        $pdf .= '
            </tbody>
        </table>

        <div style="margin-top:15px;font-size:9px;color:#666">
            Generated: ' . date('d M Y, h:i A') . '
        </div>';

        $pdf .= '</div>';
        $this->_renderPdf($pdf, 'stock_detailed_report', 'landscape');
    }

    /* ═════════════════════════════════════════════════════════════
       SHARED HELPERS
    ═════════════════════════════════════════════════════════════ */

    private function _resolveInstDept(Database $db): array
    {
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;

        $college = '';
        $dept = '';

        if ($instId > 0) {
            $r = $db->selectData('college_t', 'college_name', ['id' => $instId]);
            $college = $r[0]['college_name'] ?? '';
        }
        if ($deptId > 0) {
            $r = $db->selectData('department_master_t', 'department_name', ['id' => $deptId]);
            $dept = $r[0]['department_name'] ?? '';
        }

        return [$college, $dept];
    }

    private function _pdfBaseStyles(string $orientation = 'portrait'): string
    {
        $margin = $orientation === 'landscape' ? '0.5cm' : '1cm';
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                * { margin: 0; padding: 0; }
                body { font-family: Arial, sans-serif; line-height: 1.3; }
                .page-wrap { padding: ' . $margin . '; }
                table { width: 100%; }
                th, td { word-wrap: break-word; }
            </style>
        </head>
        <body>
        ';
    }
    private function _renderPdf(string $html, string $filename, string $orientation)
    {
        $html .= '</body></html>';

        /* ── Dompdf options ── */
        $opt = new \Dompdf\Options();
        $opt->set('isRemoteEnabled', false);
        $opt->set('defaultFont', 'DejaVu Sans');
        $opt->set('isPhpEnabled', false);
        $opt->set('isHtml5ParserEnabled', true);    // better CSS parsing
        $opt->set('isFontSubsettingEnabled', true);  // smaller file size

        /* ── Build & render ── */
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        /* ── Canvas helpers ── */
        $canvas = $dompdf->getCanvas();
        $W = $canvas->get_width();   // points (1 pt = 1/72 inch)
        $H = $canvas->get_height();

        /*
         * Dompdf canvas coordinates:
         *   Origin (0,0) is TOP-LEFT.
         *   page_text(x, y, text, font, size, color, word_spacing, char_spacing, angle)
         *
         * Margins in points (1 mm ≈ 2.835 pt):
         *   Portrait  left=18mm→51pt  right=12mm→34pt  top=15mm→43pt  bottom=18mm→51pt
         *   Landscape left=14mm→40pt  right=10mm→28pt  top=12mm→34pt  bottom=16mm→45pt
         */
        $isLandscape = strtolower($orientation) === 'landscape';

        $marginRight = $isLandscape ? 28 : 34;   // pt from right edge
        $marginBottom = $isLandscape ? 45 : 51;   // pt from bottom edge
        $marginLeft = $isLandscape ? 40 : 51;   // pt from left edge
        $marginTop = $isLandscape ? 34 : 43;   // pt from top edge

        $grey = [0.45, 0.45, 0.45];
        $fntSize = 7;

        /* ── Page number — bottom right ── */
        $canvas->page_text(
            $W - $marginRight - 55,   // x  (leave room for "Page 99 / 99")
            $H - $marginBottom + 10,  // y  (just inside bottom margin)
            'Page {PAGE_NUM} / {PAGE_COUNT}',
            null,
            $fntSize,
            $grey
        );

        /* ── Institution stamp — bottom left (filename used as fallback label) ── */
        $canvas->page_text(
            $marginLeft,
            $H - $marginBottom + 10,
            strtoupper(str_replace('_', ' ', $filename)),
            null,
            $fntSize,
            $grey
        );

        /* ── Print timestamp — bottom centre ── */
        $canvas->page_text(
            $W / 2 - 40,              // rough centre
            $H - $marginBottom + 10,
            'Printed: ' . date('d M Y, h:i A'),
            null,
            $fntSize,
            $grey
        );

        /* ── Stream to browser (inline, not download) ── */
        $dompdf->stream(
            $filename . '_' . date('Ymd_His') . '.pdf',
            ['Attachment' => false]
        );
        exit;
    }
    private function _renderPdf2(string $html, string $filename, string $orientation = 'portrait'): void
    {
        $html .= '</body></html>';
        
        // Using DOMPDF or similar PDF generation library
        // Assuming you have a PDF service configured
        try {
            $pdf = \PDF::loadHTML($html);
            $pdf->setPaper('A4', $orientation);
            $pdf->download($filename . '_' . date('YmdHis') . '.pdf');
        } catch (\Exception $e) {
            echo "Error generating PDF: " . $e->getMessage();
        }
    }
}