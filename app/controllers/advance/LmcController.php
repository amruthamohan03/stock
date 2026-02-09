<?php
class LMCController extends Controller
{
    /*
     * INDEX PAGE
     */
    public function __construct()
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . 'login');
            exit;
        }
    }

    public function index()
    {
        $db = new Database();

        // Get Banks for dropdown
        $sqlBank = "
            SELECT 
                id,
                bank_name,
                bank_code
            FROM banklist_master_t
            WHERE display = 'Y'
            ORDER BY bank_name ASC
        ";
        $banks = $db->customQuery($sqlBank);

        // Generate CSRF Token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'LMC Management',
            'banks' => $banks,
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('advance/lmc', $data);
    }

    /*
     * CRUD - INSERT, UPDATE, DELETE, LISTING
     */
    public function crudData($action = 'listing')
    {
        $db = new Database();
        $table = 'lmc_t';

        function s($v) {
            return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
        }

        /*
         * DATATABLE LISTING
         */
        if ($action === 'listing' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                $draw = intval($_GET['draw'] ?? 1);
                $start = intval($_GET['start'] ?? 0);
                $length = intval($_GET['length'] ?? 25);
                $searchValue = trim($_GET['search']['value'] ?? '');
                $orderColumnIndex = intval($_GET['order'][0]['column'] ?? 0);
                $orderDir = strtolower($_GET['order'][0]['dir'] ?? 'desc');
                
                // Validate order direction
                $orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

                $columns = ['c.id', 'b.bank_name', 'c.prepayment_date', 'c.amount', 'c.display'];
                $orderColumn = $columns[$orderColumnIndex] ?? 'c.id';

                // Base query
                $baseQuery = "
                    FROM lmc_t c
                    LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                ";

                $whereClause = " WHERE 1=1 ";
                $params = [];

                // Search filter
                if (!empty($searchValue)) {
                    $whereClause .= " AND (
                        b.bank_name LIKE ? OR
                        b.bank_code LIKE ? OR
                        c.prepayment_date LIKE ? OR
                        CAST(c.amount AS CHAR) LIKE ?
                    )";
                    $searchParam = "%{$searchValue}%";
                    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
                }

                // Bank filter
                if (!empty($_GET['bank_filter']) && $_GET['bank_filter'] != '0') {
                    $bankFilter = intval($_GET['bank_filter']);
                    $whereClause .= " AND c.bank_id = ? ";
                    $params[] = $bankFilter;
                }

                // Date range filter
                if (!empty($_GET['date_from'])) {
                    $whereClause .= " AND c.prepayment_date >= ? ";
                    $params[] = $_GET['date_from'];
                }
                if (!empty($_GET['date_to'])) {
                    $whereClause .= " AND c.prepayment_date <= ? ";
                    $params[] = $_GET['date_to'];
                }

                // Total records
                $totalRecordsQuery = "SELECT COUNT(*) as total " . $baseQuery . $whereClause;
                $totalRecordsResult = $db->customQuery($totalRecordsQuery, $params);
                $totalRecords = $totalRecordsResult[0]['total'] ?? 0;

                // Fetch data
                $dataQuery = "
                    SELECT 
                        c.id,
                        c.bank_id,
                        c.prepayment_date,
                        c.amount,
                        c.used_amount,
                        c.balance_amount,
                        c.display,
                        c.created_at,
                        c.updated_at,
                        b.bank_name,
                        b.bank_code
                    " . $baseQuery . $whereClause . "
                    ORDER BY $orderColumn $orderDir
                    LIMIT ?, ?
                ";
                
                $params[] = $start;
                $params[] = $length;

                $records = $db->customQuery($dataQuery, $params);

                $data = [];
                foreach ($records as $row) {
                    $data[] = [
                        'id' => $row['id'],
                        'bank_id' => $row['bank_id'],
                        'bank_name' => $row['bank_name'] ?? 'N/A',
                        'bank_code' => $row['bank_code'] ?? 'N/A',
                        'prepayment_date' => $row['prepayment_date'] ?? '',
                        'amount' => $row['amount'] ?? 0,
                        'used_amount' => $row['used_amount'] ?? 0,
                        'balance_amount' => $row['balance_amount'] ?? 0,
                        'display' => $row['display'] ?? 'Y',
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at']
                    ];
                }

                echo json_encode([
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'draw' => intval($_GET['draw'] ?? 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * GET STATISTICS
         */
        if ($action === 'statistics' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                // Total LMC records
                $totalQuery = "SELECT COUNT(*) as total FROM lmc_t WHERE display = 'Y'";
                $totalRecords = $db->customQuery($totalQuery)[0]['total'] ?? 0;

                // Total amount
                $amountQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM lmc_t WHERE display = 'Y'";
                $totalAmount = $db->customQuery($amountQuery)[0]['total'] ?? 0;

                // Total used amount
                $usedAmountQuery = "SELECT COALESCE(SUM(used_amount), 0) as total FROM lmc_t WHERE display = 'Y'";
                $totalUsedAmount = $db->customQuery($usedAmountQuery)[0]['total'] ?? 0;

                // Total balance amount
                $balanceAmountQuery = "SELECT COALESCE(SUM(balance_amount), 0) as total FROM lmc_t WHERE display = 'Y'";
                $totalBalanceAmount = $db->customQuery($balanceAmountQuery)[0]['total'] ?? 0;

                // This Month records
                $thisMonthQuery = "
                    SELECT COUNT(*) as total 
                    FROM lmc_t 
                    WHERE display = 'Y' 
                    AND YEAR(prepayment_date) = YEAR(CURDATE())
                    AND MONTH(prepayment_date) = MONTH(CURDATE())
                ";
                $thisMonthRecords = $db->customQuery($thisMonthQuery)[0]['total'] ?? 0;

                // Bank-wise breakdown
                $bankQuery = "
                    SELECT 
                        b.id,
                        b.bank_name,
                        b.bank_code,
                        COUNT(c.id) as record_count,
                        COALESCE(SUM(c.amount), 0) as total_amount,
                        COALESCE(SUM(c.used_amount), 0) as total_used,
                        COALESCE(SUM(c.balance_amount), 0) as total_balance
                    FROM banklist_master_t b
                    LEFT JOIN lmc_t c ON b.id = c.bank_id AND c.display = 'Y'
                    WHERE b.display = 'Y'
                    GROUP BY b.id, b.bank_name, b.bank_code
                    HAVING record_count > 0
                    ORDER BY total_amount DESC
                    LIMIT 10
                ";
                $bankCounts = $db->customQuery($bankQuery);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total_records' => $totalRecords,
                        'total_amount' => $totalAmount,
                        'total_used_amount' => $totalUsedAmount,
                        'total_balance_amount' => $totalBalanceAmount,
                        'this_month_records' => $thisMonthRecords,
                        'bank_counts' => $bankCounts
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to load statistics: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        /*
         * GET SINGLE LMC
         */
        if ($action === 'getLMC' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $query = "
                SELECT 
                    c.*,
                    b.bank_name,
                    b.bank_code
                FROM lmc_t c
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.id = ?
            ";
            $result = $db->customQuery($query, [$id]);

            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'LMC record not found']);
            }
            exit;
        }

        /*
         * INSERT - NO CLIENT_ID
         */
        if ($action === 'insert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // CSRF validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            // NO CLIENT_ID - Completely removed
            $bank_id = (int)($_POST['bank_id'] ?? 0);
            $prepayment_date = s($_POST['prepayment_date'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            // Validation
            if ($bank_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Bank']);
                exit;
            }

            if ($prepayment_date === '') {
                echo json_encode(['success' => false, 'message' => 'Please select Prepayment Date']);
                exit;
            }

            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
                exit;
            }

            if ($amount > 9999999999999.99) {
                echo json_encode(['success' => false, 'message' => 'Amount cannot exceed $9,999,999,999,999.99']);
                exit;
            }

            // Used amount starts at 0, balance = total amount
            $used_amount = 0.00;
            $balance_amount = $amount;

            $data = [
                'bank_id' => $bank_id,
                'prepayment_date' => $prepayment_date,
                'amount' => $amount,
                'used_amount' => $used_amount,
                'balance_amount' => $balance_amount,
                'display' => $display,
                'created_by' => $_SESSION['user_id'] ?? 1,
                'updated_by' => $_SESSION['user_id'] ?? 1,
            ];

            try {
                $insertId = $db->insertData($table, $data);

                echo json_encode($insertId
                    ? ['success' => true, 'message' => 'LMC record added successfully!', 'id' => $insertId]
                    : ['success' => false, 'message' => 'Insert failed.']
                );
            } catch (Exception $e) {
                error_log('LMC Insert Error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
        }

        /*
         * UPDATE - NO CLIENT_ID
         */
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // CSRF validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['lmc_id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            // NO CLIENT_ID - Completely removed
            $bank_id = (int)($_POST['bank_id'] ?? 0);
            $prepayment_date = s($_POST['prepayment_date'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            // Validation
            if ($bank_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Bank']);
                exit;
            }

            if ($prepayment_date === '') {
                echo json_encode(['success' => false, 'message' => 'Please select Prepayment Date']);
                exit;
            }

            if ($amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
                exit;
            }

            if ($amount > 9999999999999.99) {
                echo json_encode(['success' => false, 'message' => 'Amount cannot exceed $9,999,999,999,999.99']);
                exit;
            }

            // Check if record exists and get current used_amount
            $oldRow = $db->selectData('lmc_t', '*', ['id' => $id]);
            if (empty($oldRow)) {
                echo json_encode(['success' => false, 'message' => 'LMC record not found']);
                exit;
            }

            // Keep existing used_amount, recalculate balance
            $used_amount = floatval($oldRow[0]['used_amount'] ?? 0);
            $balance_amount = $amount - $used_amount;

            // Validate that new amount is not less than used amount
            if ($amount < $used_amount) {
                echo json_encode(['success' => false, 'message' => 'Total Amount cannot be less than Used Amount ($' . number_format($used_amount, 2) . ')']);
                exit;
            }

            $data = [
                'bank_id' => $bank_id,
                'prepayment_date' => $prepayment_date,
                'amount' => $amount,
                'balance_amount' => $balance_amount,
                'display' => $display,
                'updated_by' => $_SESSION['user_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            try {
                $update = $db->updateData($table, $data, ['id' => $id]);

                echo json_encode([
                    'success' => $update ? true : false,
                    'message' => $update ? 'LMC record updated successfully!' : 'Update failed.'
                ]);
            } catch (Exception $e) {
                error_log('LMC Update Error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
        }

        /*
         * DELETE
         */
        if ($action === 'deletion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // CSRF validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid delete ID']);
                exit;
            }

            $delete = $db->deleteData($table, ['id' => $id]);

            echo json_encode([
                'success' => $delete ? true : false,
                'message' => $delete ? 'LMC record deleted successfully!' : 'Delete failed.'
            ]);
            exit;
        }
if ($action === 'clientSummary' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $draw   = intval($_GET['draw'] ?? 1);
    $start  = intval($_GET['start'] ?? 0);
    $length = intval($_GET['length'] ?? 25);

    /*
     |--------------------------------------------------------
     | TOTAL COUNTS (lmc ONLY)
     |--------------------------------------------------------
     */
    $totalQuery = "
        SELECT 
            COUNT(*) AS record_count,
            SUM(amount) AS total_amount
        FROM lmc_t
        WHERE display = 'Y'
    ";
//echo $totajlQuery;exit;
    $totalResult = $db->customQuery($totalQuery);
    $recordCount = $totalResult[0]['record_count'] ?? 0;
    $totalAmount = $totalResult[0]['total_amount'] ?? 0;

    /*
     |--------------------------------------------------------
     | USED AMOUNT (EXPORTS)
     |--------------------------------------------------------
     */
    $usedQuery = "
        SELECT 
            SUM(lmc_amount) AS total_used
        FROM exports_t
        WHERE display = 'Y'
    ";

    $usedResult = $db->customQuery($usedQuery);
    $totalUsed = $usedResult[0]['total_used'] ?? 0;

    /*
     |--------------------------------------------------------
     | FINAL RESPONSE ROW (SINGLE ROW)
     |--------------------------------------------------------
     */
    $balanceAmount = $totalAmount - $totalUsed;

    $data = [[
        'record_count'  => (int)$recordCount,
        'total_amount'  => (float)$totalAmount,
        'total_used'    => (float)$totalUsed,
        'total_balance' => (float)$balanceAmount
    ]];

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => 1,
        'recordsFiltered' => 1,
        'data'            => $data
    ]);
    exit;
}
if ($action === 'getlmcDetails' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    // Fetch all LMC records (NO CLIENT LOGIC)
    $lmcQuery = "
        SELECT 
            c.*,
            b.bank_name,
            b.bank_code,
            IFNULL(c.amount, 0) AS amount,
            IFNULL(c.used_amount, 0) AS used_amount,
            (IFNULL(c.amount, 0) - IFNULL(c.used_amount, 0)) AS balance_amount
        FROM lmc_t c
        LEFT JOIN banklist_master_t b ON c.bank_id = b.id
        WHERE c.display = 'Y'
        ORDER BY c.prepayment_date DESC
    ";

    $lmcRecords = $db->customQuery($lmcQuery);

    // SAFETY: ensure array
    if (!is_array($lmcRecords)) {
        $lmcRecords = [];
    }

    // Totals
    $totalAmount  = 0;
    //$totalUsed    = 0;
    $totalBalance = 0;
    $usedQuery = "
        SELECT 
            SUM(lmc_amount) AS total_used
        FROM exports_t
        WHERE display = 'Y'
    ";

    $usedResult = $db->customQuery($usedQuery);
    $totalUsed = $usedResult[0]['total_used'] ?? 0;


    foreach ($lmcRecords as $record) {
        $totalAmount  += (float) $record['amount'];
        //$totalUsed    += (float) $record['used_amount'];
        //$totalBalance += (float) $record['balance_amount'];
    }
    $totalBalance = $totalAmount - $totalUsed;

    echo json_encode([
        'success' => true,
        'data' => [
            'records' => $lmcRecords,
            'totals' => [
                'total_amount'  => $totalAmount,
                'total_used'    => $totalUsed,
                'total_balance' => $totalBalance,
                'record_count'  => count($lmcRecords)
            ]
        ]
    ]);
    exit;
}


        /*
         * EXPORT LMC RECORDS LIST TO EXCEL (WITHOUT CLIENT)
         */
        if ($action === 'exportLMCRecordsList' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get current filters
            $bankFilter = !empty($_GET['bank_filter']) && $_GET['bank_filter'] != '0' ? intval($_GET['bank_filter']) : 0;
            $dateFrom = !empty($_GET['date_from']) ? $_GET['date_from'] : '';
            $dateTo = !empty($_GET['date_to']) ? $_GET['date_to'] : '';
            
            // Build WHERE clause
            $whereClause = " WHERE c.display = 'Y' ";
            $params = [];
            
            if ($bankFilter > 0) {
                $whereClause .= " AND c.bank_id = ?";
                $params[] = $bankFilter;
            }
            
            if (!empty($dateFrom)) {
                $whereClause .= " AND c.prepayment_date >= ?";
                $params[] = $dateFrom;
            }
            
            if (!empty($dateTo)) {
                $whereClause .= " AND c.prepayment_date <= ?";
                $params[] = $dateTo;
            }
            
            $query = "
                SELECT 
                    b.bank_name,
                    c.prepayment_date,
                    c.amount
                FROM lmc_t c
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                {$whereClause}
                ORDER BY c.prepayment_date DESC, c.id DESC
            ";
            
            $lmcs = $db->customQuery($query, $params);

            // Correct path to vendor autoload
            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('LMC Records');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
            ];

            // Title
            $sheet->mergeCells('A1:C1');
            $sheet->setCellValue('A1', 'LMC RECORDS LIST');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Headers row
            $headers = ['Bank', 'Prepayment Date', 'Amount'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->applyFromArray($headerStyle);
                $col++;
            }
            $sheet->getRowDimension(3)->setRowHeight(25);

            // Data rows
            $row = 4;
            $totalAmount = 0;
            
            foreach ($lmcs as $lmc) {
                $sheet->setCellValue('A' . $row, $lmc['bank_name'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($lmc['prepayment_date'])));
                $sheet->setCellValue('C' . $row, '$' . number_format($lmc['amount'], 2));
                
                // Center align all cells
                $sheet->getStyle('A' . $row . ':C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row . ':C' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
                $totalAmount += $lmc['amount'];
                
                $row++;
            }

            // Add totals row
            $row++;
            $sheet->setCellValue('B' . $row, 'TOTAL:');
            $sheet->setCellValue('C' . $row, '$' . number_format($totalAmount, 2));
            $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('B' . $row . ':C' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB3B');
            $sheet->getStyle('B' . $row . ':C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Auto-size columns
            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Add borders to all data
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A3:C' . $row)->applyFromArray($styleArray);

            // Download
            $filename = 'LMC_Records_List_' . date('Y-m-d_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        
        /*
         * EXPORT SINGLE LMC TO EXCEL
         */
        if ($action === 'exportLMC' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                die('Invalid ID');
            }

            $query = "
                SELECT 
                    c.*,
                    b.bank_name,
                    b.bank_code
                FROM lmc_t c
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.id = ?
            ";
            $lmcData = $db->customQuery($query, [$id]);

            if (empty($lmcData)) {
                die('LMC record not found');
            }

            $lmc = $lmcData[0];

            // Correct path to vendor autoload
            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('LMC Details');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            // Title
            $sheet->mergeCells('A1:D1');
            $sheet->setCellValue('A1', 'LMC RECORD DETAILS');
            $sheet->getStyle('A1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Data
            $row = 3;
            
            $fields = [
                'Bank' => $lmc['bank_name'] ?? 'N/A',
                'Prepayment Date' => date('d-m-Y', strtotime($lmc['prepayment_date'])),
                'Amount' => '$' . number_format($lmc['amount'], 2),
                'Used Amount' => '$' . number_format($lmc['used_amount'], 2),
                'Balance Amount' => '$' . number_format($lmc['balance_amount'], 2)
            ];

            foreach ($fields as $label => $value) {
                $sheet->setCellValue('A' . $row, $label);
                $sheet->setCellValue('B' . $row, $value);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Download
            $filename = 'LMC_' . $lmc['id'] . '_' . date('Y-m-d') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
        
        

        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    
}
?>