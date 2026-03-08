<?php
/**
 * FILE: app/controllers/CustomController.php
 *
 * Custom Stock Report Controller
 * — Summary Report: Group-wise count
 * — Detailed Report: Indent-wise with stock book page numbers
 * — Asset Register: Indent-wise asset details (when register type = ASSET)
 * — Institution & Department filtering
 * — Status & Register Type filtering
 * — PDF export for both reports
 *
 * Routes:
 *   GET  custom/                          → index()
 *   GET  custom/getDepartments            → getDepartments()
 *   GET  custom/getSummaryData            → getSummaryData()
 *   GET  custom/getDetailedData           → getDetailedData()
 *   GET  custom/getAssetRegisterData      → getAssetRegisterData()
 *   GET  custom/exportSummaryPdf          → exportSummaryPdf()
 *   GET  custom/exportDetailedPdf         → exportDetailedPdf()
 */
class CustomController extends Controller
{
    /* ─────────────────────────────────────────────
       INDEX  — render view with institution & register type list
    ───────────────────────────────────────────── */
    public function index()
    {
        $db = new Database();
        $institutions = $db->selectData('college_t', 'id, college_name', ['display' => 'Y']);
        $stockbook_types = $db->selectData('stockbook_type_t', 'id, name, code', ['display' => 'Y']);
        $status_master = $db->selectData('status_master_t', 'id, status_name', ['display' => 'Y']);

        $data = [
            'title' => 'Custom Stock Reports',
            'institutions' => $institutions ?: [],
            'stockbook_types' => $stockbook_types ?: [],
            'status_master' => $status_master ?: [],
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
            $statusId = isset($_GET['status_master_id']) ? (int) $_GET['status_master_id'] : 0;

            $where = "WHERE sb.display='Y' AND st.display='Y'";
            
            if ($from && $to) {
                // Validate date format (YYYY-MM-DD)
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                    throw new Exception("Invalid date format. Use YYYY-MM-DD");
                }
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

            if ($statusId > 0) {
                $where .= " AND ii.status_id = $statusId";
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
       DETAILED DATA  — Indent-wise stock details
    ═════════════════════════════════════════════════════════════ */
    public function getDetailedData()
    {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();

            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
            $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
            $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;
            $statusId = isset($_GET['status_master_id']) ? (int) $_GET['status_master_id'] : 0;

            $where = "WHERE st.display='Y'";
            
            if ($from && $to) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                    throw new Exception("Invalid date format. Use YYYY-MM-DD");
                }
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
            if ($statusId > 0) {
                $where .= " AND ii.status_id = $statusId";
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

            // Ensure rows is an array
            if (!is_array($rows)) {
                $rows = [];
            }

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'count' => count($rows),
                'message' => 'Detailed data loaded successfully'
            ]);
            exit;

        } catch (Exception $e) {
            // Return error as valid JSON
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'data' => [],
                'count' => 0,
                'message' => 'Error loading detailed data: ' . $e->getMessage(),
                'error' => true
            ]);
            exit;
        }
    }

    /* ═════════════════════════════════════════════════════════════
       ASSET REGISTER DATA  — Indent-wise asset listing (register type = ASSET)
    ═════════════════════════════════════════════════════════════ */
    public function getAssetRegisterData()
    {
        header('Content-Type: application/json');
        
        try {
            $db = new Database();

            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $instId = isset($_GET['institution_id']) ? (int) $_GET['institution_id'] : 0;
            $deptId = isset($_GET['dept_id']) ? (int) $_GET['dept_id'] : 0;
            $statusId = isset($_GET['status_master_id']) ? (int) $_GET['status_master_id'] : 0;

            $where = "WHERE ii.display='Y' ";
            
            if ($from && $to) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
                    throw new Exception("Invalid date format. Use YYYY-MM-DD");
                }
                $where .= " AND idm.indent_date BETWEEN '$from' AND '$to'";
            }
            if ($instId > 0) {
                $where .= " AND idm.institution_id = $instId";
            }
            if ($deptId > 0) {
                $where .= " AND idm.department_id = $deptId";
            }
            if ($statusId > 0) {
                $where .= " AND ii.status_id = $statusId";
            }

            // Query to get asset register data with issued quantity calculations
            $rows = $db->customQuery("
                SELECT 
                    idm.indent_no,
                    idm.indent_date,
                    (CASE WHEN idm.item_type = 'C' THEN 'CONSUMABLE' ELSE 'NON CONSUMABLE' END) AS item_type,
                    im.item_name,
                    ii.item_description,
                    mk.make_name,
                    md.model_name,
                    sm.status_name,
                    ii.qty_intended AS asset_count,
                    COALESCE(SUM(CASE WHEN st.transaction_type = 'ISSUE' THEN st.issue_qty ELSE 0 END), 0) AS issued_count,
                    (ii.qty_intended - COALESCE(SUM(CASE WHEN st.transaction_type = 'ISSUE' THEN st.issue_qty ELSE 0 END), 0)) AS remaining_count
                FROM indent_item_t ii
                LEFT JOIN indent_master_t idm ON ii.indent_id = idm.id
                LEFT JOIN item_master_t im ON ii.item_id = im.id
                LEFT JOIN group_item_name_master_t gm ON im.group_id = gm.id
                LEFT JOIN make_t mk ON ii.make_id = mk.id
                LEFT JOIN model_t md ON ii.model_id = md.id
                LEFT JOIN stock_book_t sb ON sb.item_id = ii.item_id
                LEFT JOIN status_master_t sm ON ii.status_id = sm.id
                LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id AND st.transaction_type = 'ISSUE'
                $where
                GROUP BY ii.id, idm.indent_no, idm.indent_date, im.item_name, ii.item_description, mk.make_name, md.model_name, sm.status_name, ii.qty_intended
                ORDER BY idm.indent_date ASC, idm.indent_no ASC, im.item_name ASC;
            ");

            // Ensure rows is an array
            if (!is_array($rows)) {
                $rows = [];
            }

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'count' => count($rows),
                'message' => 'Asset register data loaded successfully'
            ]);
            exit;

        } catch (Exception $e) {
            // Return error as valid JSON
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'data' => [],
                'count' => 0,
                'message' => 'Error loading asset register data: ' . $e->getMessage(),
                'error' => true
            ]);
            exit;
        }
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
        $statusId = isset($_GET['status_master_id']) ? (int) $_GET['status_master_id'] : 0;

        // Get Institution, Department, and StockBook Type names
        $collegeName = '';
        $deptName = '';
        $registerName = '';
        $statusName = '';

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
        if ($statusId > 0) {
            $st = $db->selectData('status_master_t', 'status_name', ['id' => $statusId]);
            $statusName = $st[0]['status_name'] ?? '';
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
        //     $where .= " AND sb.stockbook_type_id = $stockbookTypeId";
        // }
        if ($statusId > 0) {
            $where .= " AND ii.status_id = $statusId";
        }

        $query = "
            SELECT 
                COALESCE(gm.group_name, 'Ungrouped') AS group_name,
                COUNT(DISTINCT sb.id) AS total_items,
                SUM(CASE WHEN st.transaction_type IN ('RECEIPT','TRANSFER') THEN st.receipt_qty ELSE 0 END) AS total_received,
                SUM(CASE WHEN st.transaction_type='RECEIPT' AND st.stock_entry_type='INDENT_BASED' THEN st.receipt_qty ELSE 0 END) AS indent_received,
                SUM(CASE WHEN st.transaction_type='TRANSFER' THEN st.receipt_qty ELSE 0 END) AS transfer_received,
                SUM(CASE WHEN st.transaction_type='ISSUE' AND st.item_status <> 'DELETED' THEN st.issue_qty ELSE 0 END) AS total_issued,
                SUM(CASE WHEN st.item_status='DELETED' THEN ii.qty_intended ELSE 0 END) AS total_deleted,
                (SUM(CASE WHEN st.transaction_type IN ('RECEIPT','TRANSFER') THEN st.receipt_qty ELSE 0 END) 
                    - SUM(CASE WHEN st.transaction_type='ISSUE' AND st.item_status <> 'DELETED' THEN st.issue_qty ELSE 0 END)
                    - SUM(CASE WHEN st.item_status='DELETED' THEN ii.qty_intended ELSE 0 END)) AS total_balance
            FROM stock_book_t sb
            LEFT JOIN stock_transaction_t st ON st.stock_book_id = sb.id
            LEFT JOIN indent_item_t ii ON ii.id = st.indent_item_id
            LEFT JOIN item_master_t im ON im.id = sb.item_id
            LEFT JOIN group_item_name_master_t gm ON gm.id = im.group_id
            $where
            GROUP BY gm.id
            ORDER BY group_name ASC
        ";

        $rows = $db->customQuery($query);
        $rows = is_array($rows) ? $rows : [];

        // Build PDF
        $pdf = $this->_pdfBaseStyles('portrait');

        $pdf .= '<div class="page-wrap">';
        $pdf .= '<h2 style="text-align:center;color:#333">Summary Stock Report</h2>';
        $pdf .= '<div style="text-align:center;margin-bottom:20px;font-size:10px">';
        if ($collegeName) $pdf .= '<strong>Institution:</strong> ' . htmlspecialchars($collegeName) . '<br>';
        if ($deptName) $pdf .= '<strong>Department:</strong> ' . htmlspecialchars($deptName) . '<br>';
        if ($registerName) $pdf .= '<strong>Register:</strong> ' . htmlspecialchars($registerName) . '<br>';
        if ($statusName) $pdf .= '<strong>Status:</strong> ' . htmlspecialchars($statusName) . '<br>';
        if ($from && $to) $pdf .= '<strong>Period:</strong> ' . $from . ' to ' . $to . '<br>';
        $pdf .= '</div>';

        $pdf .= '<table style="width:100%;border-collapse:collapse">';
        $pdf .= '<thead>';
        $pdf .= '<tr style="background:#f0f0f0;border:1px solid #ccc">';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">S.No</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:left">Group Name</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Total Items</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Indent Rcvd</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Transfer Rcvd</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Total Rcvd</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Issued</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Deleted</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:5px;font-size:9px;text-align:center">Balance</th>';
        $pdf .= '</tr>';
        $pdf .= '</thead>';
        $pdf .= '<tbody>';

        $sno = 1;
        foreach ($rows as $row) {
            $pdf .= '<tr style="border:1px solid #ccc">';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . $sno++ . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px">' . htmlspecialchars($row['group_name'] ?? 'N/A') . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['total_items'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['indent_received'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['transfer_received'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['total_received'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['total_issued'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['total_deleted'] ?? 0) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:4px;font-size:9px;text-align:center">' . ($row['total_balance'] ?? 0) . '</td>';
            $pdf .= '</tr>';
        }

        $pdf .= '</tbody>';
        $pdf .= '</table>';
        $pdf .= '<div style="margin-top:15px;font-size:9px;color:#666">';
        $pdf .= 'Generated: ' . date('d M Y, h:i A');
        $pdf .= '</div>';
        $pdf .= '</div>';

        $this->_renderPdf($pdf, 'stock_summary_report', 'portrait');
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
        $stockbookTypeId = isset($_GET['stockbook_type_id']) ? (int) $_GET['stockbook_type_id'] : 0;
        $statusId = isset($_GET['status_master_id']) ? (int) $_GET['status_master_id'] : 0;

        // Get Institution, Department names
        $collegeName = '';
        $deptName = '';
        $registerName = '';
        $statusName = '';

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
        if ($statusId > 0) {
            $st = $db->selectData('status_master_t', 'status_name', ['id' => $statusId]);
            $statusName = $st[0]['status_name'] ?? '';
        }

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
        if ($statusId > 0) {
            $where .= " AND ii.status_id = $statusId";
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
                st.receipt_qty,
                st.issue_qty,
                CASE WHEN st.item_status = 'DELETED' THEN st.receipt_qty ELSE 0 END AS deleted_quantity,
                GREATEST(st.receipt_qty - (st.issue_qty + CASE WHEN st.item_status = 'DELETED' THEN st.receipt_qty ELSE 0 END), 0) AS balance
            FROM stock_transaction_t st
            LEFT JOIN stock_book_t sb ON st.stock_book_id = sb.id 
            LEFT JOIN indent_master_t idm ON st.indent_id = idm.id
            LEFT JOIN item_master_t im ON sb.item_id = im.id
            LEFT JOIN group_item_name_master_t gm ON im.group_id = gm.id
            LEFT JOIN indent_item_t ii ON st.indent_item_id = ii.id
            LEFT JOIN make_t mk ON ii.make_id = mk.id
            LEFT JOIN model_t md ON ii.model_id = md.id
            $where
            ORDER BY gm.group_name ASC, ii.indent_id ASC, im.item_name ASC
        ");

        $rows = is_array($rows) ? $rows : [];

        // Build PDF
        $pdf = $this->_pdfBaseStyles('landscape');
        $pdf .= '<div class="page-wrap">';
        $pdf .= '<h2 style="text-align:center;color:#333">Detailed Stock Report</h2>';
        $pdf .= '<div style="text-align:center;margin-bottom:15px;font-size:9px">';
        if ($collegeName) $pdf .= '<strong>Institution:</strong> ' . htmlspecialchars($collegeName) . ' &nbsp; ';
        if ($deptName) $pdf .= '<strong>Department:</strong> ' . htmlspecialchars($deptName) . ' &nbsp; ';
        if ($registerName) $pdf .= '<strong>Register:</strong> ' . htmlspecialchars($registerName) . ' &nbsp; ';
        if ($statusName) $pdf .= '<strong>Status:</strong> ' . htmlspecialchars($statusName) . ' &nbsp; ';
        if ($from && $to) $pdf .= '<strong>Period:</strong> ' . $from . ' to ' . $to;
        $pdf .= '</div>';

        $pdf .= '<table style="width:100%;border-collapse:collapse;font-size:8px">';
        $pdf .= '<thead><tr style="background:#f0f0f0;border:1px solid #ccc">';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">S.No</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px">Item</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Indent No</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Make/Model</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Rcvd (I)</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Rcvd (T)</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Total</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Issued</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Deleted</th>';
        $pdf .= '<th style="border:1px solid #ccc;padding:3px;text-align:center">Balance</th>';
        $pdf .= '</tr></thead>';
        $pdf .= '<tbody>';

        $sno = 1;
        $totalRecvGrand = 0;
        $totalIssuedGrand = 0;
        $totalDeletedGrand = 0;
        $totalBalanceGrand = 0;

        foreach ($rows as $row) {
            $rcvdIndent = ($row['transaction_type'] === 'RECEIPT' && $row['stock_entry_type'] === 'INDENT_BASED') ? ($row['receipt_qty'] ?? 0) : 0;
            $rcvdTransfer = ($row['transaction_type'] === 'TRANSFER') ? ($row['receipt_qty'] ?? 0) : 0;
            $totalRcvd = $rcvdIndent + $rcvdTransfer;
            $issued = ($row['transaction_type'] === 'ISSUE') ? ($row['issue_qty'] ?? 0) : 0;
            $deleted = $row['deleted_quantity'] ?? 0;
            $balance = $row['balance'] ?? 0;

            $pdf .= '<tr style="border:1px solid #ccc">';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $sno++ . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px">' . htmlspecialchars($row['item_name'] ?? 'N/A') . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . htmlspecialchars($row['indent_no'] ?? 'N/A') . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . htmlspecialchars(($row['make_name'] ?? '') . ($row['model_name'] ? ' / ' . $row['model_name'] : '')) . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $rcvdIndent . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $rcvdTransfer . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $totalRcvd . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $issued . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $deleted . '</td>';
            $pdf .= '<td style="border:1px solid #ccc;padding:3px;text-align:center">' . $balance . '</td>';
            $pdf .= '</tr>';

            $totalRecvGrand += $totalRcvd;
            $totalIssuedGrand += $issued;
            $totalDeletedGrand += $deleted;
            $totalBalanceGrand += $balance;
        }

        $pdf .= '<tr style="background:#f0f0f0;font-weight:bold;border:1px solid #111">';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center" colspan="4">GRAND TOTAL</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">-</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">-</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">' . $totalRecvGrand . '</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">' . $totalIssuedGrand . '</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">' . $totalDeletedGrand . '</td>';
        $pdf .= '<td style="border:1px solid #ccc;padding:4px;text-align:center">' . $totalBalanceGrand . '</td>';
        $pdf .= '</tr>';
        $pdf .= '</tbody>';
        $pdf .= '</table>';

        $pdf .= '<div style="margin-top:15px;font-size:9px;color:#666">';
        $pdf .= 'Generated: ' . date('d M Y, h:i A');
        $pdf .= '</div>';
        $pdf .= '</div>';

        $this->_renderPdf($pdf, 'stock_detailed_report', 'landscape');
    }

    /* ═════════════════════════════════════════════════════════════
       SHARED HELPERS
    ═════════════════════════════════════════════════════════════ */

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
        $opt->set('isHtml5ParserEnabled', true);
        $opt->set('isFontSubsettingEnabled', true);

        /* ── Build & render ── */
        $dompdf = new \Dompdf\Dompdf($opt);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        /* ── Canvas helpers ── */
        $canvas = $dompdf->getCanvas();
        $W = $canvas->get_width();
        $H = $canvas->get_height();

        $isLandscape = strtolower($orientation) === 'landscape';

        $marginRight = $isLandscape ? 28 : 34;
        $marginBottom = $isLandscape ? 45 : 51;
        $marginLeft = $isLandscape ? 40 : 51;
        $marginTop = $isLandscape ? 34 : 43;

        $grey = [0.45, 0.45, 0.45];
        $fntSize = 7;

        /* ── Page number — bottom right ── */
        $canvas->page_text(
            $W - $marginRight - 55,
            $H - $marginBottom + 10,
            'Page {PAGE_NUM} / {PAGE_COUNT}',
            null,
            $fntSize,
            $grey
        );

        /* ── Institution stamp — bottom left ── */
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
            $W / 2 - 40,
            $H - $marginBottom + 10,
            'Printed: ' . date('d M Y, h:i A'),
            null,
            $fntSize,
            $grey
        );

        /* ── Stream to browser ── */
        $dompdf->stream(
            $filename . '_' . date('Ymd_His') . '.pdf',
            ['Attachment' => false]
        );
        exit;
    }
}