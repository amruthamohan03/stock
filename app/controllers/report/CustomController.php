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
        $stockbook_types = $db->selectData('stockbook_type_t', 'id, name, code', ['display' => 'Y']);  // ADD THIS

        $data = [
            'title' => 'Custom Stock Reports',
            'institutions' => $institutions ?: [],
            'stockbook_types' => $stockbook_types ?: [],  // ADD THIS
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
    
    try {
        $db = new Database();

        // Validate and sanitize inputs
        $from = isset($_GET['from']) && !empty($_GET['from']) ? $_GET['from'] : null;
        $to = isset($_GET['to']) && !empty($_GET['to']) ? $_GET['to'] : null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
        $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;

        $where = "WHERE sb.display='Y' AND st.display='Y'";
        
        if ($from && $to) {
            // Validate date format (YYYY-MM-DD)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                throw new Exception("Invalid date format. Use YYYY-MM-DD");
            }
            // $from = $db->escape($from);
            // $to = $db->escape($to);
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }
        
        if ($stockbookTypeId > 0) {
            $where .= " AND dm.stockbook_type_id = $stockbookTypeId";
        }

        $query = "
            SELECT 
                COALESCE(gm.group_name, 'Ungrouped') AS group_name,
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
            LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id
            LEFT JOIN indent_item_t ii ON ii.id = st.indent_item_id
            LEFT JOIN item_master_t im ON im.id = sb.item_id
            LEFT JOIN group_item_name_master_t gm ON gm.id = im.group_id
            $where
            GROUP BY gm.id
            ORDER BY group_name ASC
        ";

        // Execute query
        $rows = $db->customQuery($query);

        // Ensure rows is an array
        if (!is_array($rows)) {
            $rows = [];
        }

        echo json_encode([
            'success' => true,
            'data' => $rows,
            'count' => count($rows),
            'message' => 'Summary data loaded successfully'
        ]);
        exit;

    } catch (Exception $e) {
        // Return error as valid JSON
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'data' => [],
            'count' => 0,
            'message' => 'Error loading summary data: ' . $e->getMessage(),
            'error' => true
        ]);
        exit;
    }
}

    /* ═════════════════════════════════════════════════════════════
       DETAILED DATA  — Using your custom query with group & item name grouping
    ═════════════════════════════════════════════════════════════ */
    public function getDetailedData()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
        $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;

        $where = "WHERE st.display='Y'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }
        // if ($stockbookTypeId > 0) {
        //     $where .= " AND sb.stockbook_type_id = $stockbookTypeId";
        // }

        $rows = $db->customQuery("
            SELECT 
                gm.group_name,
                idm.indent_no,
                im.item_name,
                ii.item_description,
                mk.make_name,
                md.model_name,
                st.transaction_type,
                st.stock_entry_type,
                st.department_id,
                st.receipt_qty,
                st.issue_qty,
                /* Deleted quantity */
                CASE 
                    WHEN st.item_status = 'DELETED' 
                    THEN st.receipt_qty
                    ELSE 0
                END AS deleted_quantity,
                /* Balance */
                GREATEST(
                    st.receipt_qty - (st.issue_qty
                        +
                        CASE 
                            WHEN st.item_status = 'DELETED' 
                            THEN st.receipt_qty
                            ELSE 0
                        END
                    ),
                0) AS balance
            FROM stock_transaction_t st
            LEFT JOIN stock_book_t sb 
                ON st.stock_book_id = sb.id 
            LEFT JOIN indent_master_t idm 
                ON st.indent_id = idm.id
            LEFT JOIN item_master_t im 
                ON sb.item_id = im.id
            LEFT JOIN group_item_name_master_t gm 
                ON im.group_id = gm.id
            LEFT JOIN indent_item_t ii 
                ON st.indent_item_id = ii.id
            LEFT JOIN make_t mk
                ON ii.make_id = mk.id
            LEFT JOIN model_t md
                ON ii.model_id = md.id
            $where
            ORDER BY gm.group_name ASC, ii.indent_id ASC, im.item_name ASC
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
        $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;
        [$collegeName, $deptName] = $this->_resolveInstDept($db);
        // Get Institution, Department, and StockBook Type names
        $collegeName = '';
        $deptName = '';
        $registerName = '';

        if ($instId > 0) {
            $inst = $db->selectData('college_t', 'college_name', ['id' => $instId]);
            $collegeName = $inst[0]['college_name'] ?? '';
        }
        if ($deptId > 0) {
            $dept = $db->selectData('department_master_t', 'department_name', ['id' => $deptId]);
            $deptName = $dept[0]['department_name'] ?? '';
        }
        if ($stockbookTypeId > 0) {
            $sbt = $db->selectData('stockbook_type_t', 'name', ['id' => $stockbookTypeId]);
            $registerName = $sbt[0]['name'] ?? '';
        }
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
        // if ($stockbookTypeId > 0) {
        //     $where .= " AND dm.stockbook_type_id = $stockbookTypeId";
        // }

        $rows = $db->customQuery("
            SELECT 
                COALESCE(gm.group_name, 'Ungrouped') AS group_name,
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
            LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id
            LEFT JOIN indent_item_t ii ON ii.id = st.indent_item_id
            LEFT JOIN item_master_t im ON im.id = sb.item_id
            LEFT JOIN group_item_name_master_t gm ON gm.id = im.group_id
            $where
            GROUP BY gm.id
            ORDER BY group_name ASC
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
            '.$registerName.'(SUMMARY REPORT)
        </div>
        ';

        /* Summary Table */
        $pdf .= '
        <table style="width:100%;border-collapse:collapse;font-size:10px">
            <thead>
                <tr style="background:#f0f0f0;border-bottom:2px solid #111">
                    <th style="border:1px solid #ccc;padding:6px;text-align:center;width:40px">S.No</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left">Group/Item Name</th>
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
                <td style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold">' . $row['total_received'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;color:#c62828">' . $row['total_issued'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;color:#d32f2f;font-weight:bold">' . $row['total_deleted'] . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold;background:#e8f5e9">' . $row['total_balance'] . '</td>
            </tr>
            ';
            
            $totalItems += $row['total_items'];
            $totalIndentRecv += $row['indent_received'];
            $totalTransferRecv += $row['transfer_received'];
            $totalRecv += $row['total_received'];
            $totalIssued += $row['total_issued'];
            $totalDeleted += $row['total_deleted'];
            $totalBalance += $row['total_balance'];
        }

        $pdf .= '
            <tr style="background:#f0f0f0;font-weight:bold;border-top:2px solid #111">
                <td style="border:1px solid #ccc;padding:6px;text-align:center" colspan="2">TOTAL</td>
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
       EXPORT DETAILED PDF — Grouped by Group Name then Item Name
    ═════════════════════════════════════════════════════════════ */
    public function exportDetailedPdf()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
        $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
        $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;

        [$collegeName, $deptName] = $this->_resolveInstDept($db);

        $where = "WHERE st.display='Y' AND item_status !='DELETED'";
        
        if ($from && $to) {
            $where .= " AND st.transaction_date BETWEEN '$from' AND '$to'";
        }
        if ($instId > 0) {
            $where .= " AND st.institution_id = $instId";
        }
        if ($deptId > 0) {
            $where .= " AND st.department_id = $deptId";
        }
        if ($stockbookTypeId > 0) {
            $where .= " AND sb.stockbook_type_id = $stockbookTypeId";
        }

        $rows = $db->customQuery("
            SELECT 
                gm.group_name,
                idm.indent_no,
                im.item_name,
                ii.item_description,
                mk.make_name,
                md.model_name,
                st.transaction_type,
                st.stock_entry_type,
                st.department_id,
                st.receipt_qty,
                st.issue_qty,
                /* Deleted quantity */
                CASE 
                    WHEN st.item_status = 'DELETED' 
                    THEN st.receipt_qty
                    ELSE 0
                END AS deleted_quantity,
                /* Balance */
                GREATEST(
                    st.receipt_qty - (st.issue_qty
                        +
                        CASE 
                            WHEN st.item_status = 'DELETED' 
                            THEN st.receipt_qty
                            ELSE 0
                        END
                    ),
                0) AS balance
            FROM stock_transaction_t st
            LEFT JOIN stock_book_t sb 
                ON st.stock_book_id = sb.id 
            LEFT JOIN indent_master_t idm 
                ON st.indent_id = idm.id
            LEFT JOIN item_master_t im 
                ON sb.item_id = im.id
            LEFT JOIN group_item_name_master_t gm 
                ON im.group_id = gm.id
            LEFT JOIN indent_item_t ii 
                ON st.indent_item_id = ii.id
            LEFT JOIN make_t mk
                ON ii.make_id = mk.id
            LEFT JOIN model_t md
                ON ii.model_id = md.id
            $where
            ORDER BY gm.group_name ASC, ii.indent_id ASC, im.item_name ASC
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
            STOCK DETAILED REPORT (GROUPED BY GROUP & ITEM NAME)
        </div>
        ';

        /* Detailed Table with Group & Item Grouping */
        $pdf .= '
        <table style="width:100%;border-collapse:collapse;font-size:9px">
            <thead>
                <tr style="background:#f0f0f0;border-bottom:2px solid #111">
                    <th style="border:1px solid #ccc;padding:4px;text-align:center;width:35px">S.No</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:left">Item Name</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:left">Description</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Indent No</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Make/Model</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Type</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Received</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Issued</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center">Deleted</th>
                    <th style="border:1px solid #ccc;padding:4px;text-align:center;font-weight:bold">Balance</th>
                </tr>
            </thead>
            <tbody>
        ';

        $sno = 1;
        $currentGroup = '';
        $currentItem = '';
        $totalRecvGrand = 0;
        $totalIssuedGrand = 0;
        $totalDeletedGrand = 0;
        $totalBalanceGrand = 0;
        
        // Track group totals
        $groupRecv = 0;
        $groupIssued = 0;
        $groupDeleted = 0;
        $groupBalance = 0;
        
        // Track item totals
        $itemRecv = 0;
        $itemIssued = 0;
        $itemDeleted = 0;
        $itemBalance = 0;
        
        // Convert to array to check next element
        $rowsArray = is_array($rows) ? $rows : [];
        $totalRows = count($rowsArray);

        foreach ($rowsArray as $currentRowIndex => $row) {
            $nextRow = $currentRowIndex + 1 < $totalRows ? $rowsArray[$currentRowIndex + 1] : null;
            
            // Add Group Header when group changes
            if ($currentGroup !== $row['group_name']) {
                $currentGroup = $row['group_name'];
                $currentItem = ''; // Reset item when group changes
                
                // Reset group totals
                $groupRecv = 0;
                $groupIssued = 0;
                $groupDeleted = 0;
                $groupBalance = 0;
                
                // Add group header
                $pdf .= '
                <tr style="background:#f3f4f6;border:2px solid #374151">
                    <td colspan="10" style="border:2px solid #374151;padding:6px;font-weight:bold;color:#1f2937;font-size:11px">
                        📁 ' . htmlspecialchars($currentGroup ?: 'Ungrouped') . '
                    </td>
                </tr>
                ';
            }

            // Add Item Header when item changes (within the group)
            if ($currentItem !== $row['item_name']) {
                $currentItem = $row['item_name'];
                
                // Reset item totals
                $itemRecv = 0;
                $itemIssued = 0;
                $itemDeleted = 0;
                $itemBalance = 0;
                
                // Add item subheader
                $pdf .= '
                <tr style="background:#e3f2fd;border:1px solid #1976d2">
                    <td colspan="10" style="border:1px solid #1976d2;padding:5px;font-weight:bold;color:#1565c0;font-size:10px">
                        &nbsp;&nbsp;📦 ' . htmlspecialchars($currentItem) . '
                    </td>
                </tr>
                ';
            }

            $makeModel = ($row['make_name'] ? $row['make_name'] : '') . 
                        ($row['model_name'] ? ' / ' . $row['model_name'] : '');
            
            $pdf .= '
            <tr>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $sno++ . '</td>
                <td style="border:1px solid #ccc;padding:4px;font-size:9px">' . htmlspecialchars($row['item_name']) . '</td>
                <td style="border:1px solid #ccc;padding:4px;font-size:8px">' . htmlspecialchars($row['item_description'] ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;font-size:9px">' . htmlspecialchars($row['indent_no'] ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;font-size:8px">' . htmlspecialchars($makeModel ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;font-size:8px">' . htmlspecialchars($row['transaction_type'] ?: 'N/A') . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center">' . (int)$row['receipt_qty'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#c62828">' . (int)$row['issue_qty'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#d32f2f">' . (int)$row['deleted_quantity'] . '</td>
                <td style="border:1px solid #ccc;padding:4px;text-align:center;font-weight:bold;background:#e8f5e9">' . (int)$row['balance'] . '</td>
            </tr>
            ';

            $totalRecvGrand += (int)$row['receipt_qty'];
            $totalIssuedGrand += (int)$row['issue_qty'];
            $totalDeletedGrand += (int)$row['deleted_quantity'];
            $totalBalanceGrand += (int)$row['balance'];
            
            // Add to item totals
            $itemRecv += (int)$row['receipt_qty'];
            $itemIssued += (int)$row['issue_qty'];
            $itemDeleted += (int)$row['deleted_quantity'];
            $itemBalance += (int)$row['balance'];
            
            // Add to group totals
            $groupRecv += (int)$row['receipt_qty'];
            $groupIssued += (int)$row['issue_qty'];
            $groupDeleted += (int)$row['deleted_quantity'];
            $groupBalance += (int)$row['balance'];
            
            // Check if next row is different item - if so, add item subtotal
            if ($nextRow === null || $nextRow['item_name'] !== $row['item_name']) {
                // Add Item Subtotal
                $pdf .= '
                <tr style="background:#dbeafe;font-weight:bold;border-top:1px solid #3b82f6;border-bottom:1px solid #3b82f6">
                    <td colspan="2" style="border:1px solid #ccc;padding:4px;text-align:center;font-size:9px;color:#1e40af">Item Subtotal: ' . htmlspecialchars($currentItem) . '</td>
                    <td colspan="4" style="border:1px solid #ccc;padding:4px;text-align:center"></td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#1e40af">' . $itemRecv . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#1e40af">' . $itemIssued . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center;color:#1e40af">' . $itemDeleted . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center;background:#bfdbfe;color:#1e40af">' . $itemBalance . '</td>
                </tr>
                ';
            }
            
            // Check if next row is different group - if so, add group subtotal
            if ($nextRow === null || $nextRow['group_name'] !== $row['group_name']) {
                // Add Group Subtotal
                $pdf .= '
                <tr style="background:#fef3c7;font-weight:bold;border-top:1px solid #f59e0b;border-bottom:1px solid #f59e0b">
                    <td colspan="2" style="border:1px solid #ccc;padding:4px;text-align:center;font-size:9px">Subtotal: ' . htmlspecialchars($currentGroup ?: 'Ungrouped') . '</td>
                    <td colspan="4" style="border:1px solid #ccc;padding:4px;text-align:center"></td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $groupRecv . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $groupIssued . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center">' . $groupDeleted . '</td>
                    <td style="border:1px solid #ccc;padding:4px;text-align:center;background:#fcd34d">' . $groupBalance . '</td>
                </tr>
                ';
            }
        }

        // Add Grand Total
        $pdf .= '
            <tr style="background:#f0f0f0;font-weight:bold;border-top:2px solid #111">
                <td style="border:1px solid #ccc;padding:6px;text-align:center" colspan="2">GRAND TOTAL</td>
                <td colspan="4" style="border:1px solid #ccc;padding:6px;text-align:center"></td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalRecvGrand . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalIssuedGrand . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center">' . $totalDeletedGrand . '</td>
                <td style="border:1px solid #ccc;padding:6px;text-align:center;background:#c8e6c9">' . $totalBalanceGrand . '</td>
            </tr>
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
}