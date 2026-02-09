<?php
class CEECController extends Controller
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

        // Get Clients for dropdown - using short_name
        $sql = "
            SELECT 
                id,
                company_name,
                short_name
            FROM clients_t
            WHERE display = 'Y'
            ORDER BY short_name ASC
        ";
        $clients = $db->customQuery($sql);

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
            'title' => 'CEEC Management',
            'clients' => $clients,
            'banks' => $banks,
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('advance/ceec', $data);
    }

    /*
     * CRUD - INSERT, UPDATE, DELETE, LISTING
     */
    public function crudData($action = 'listing')
    {
        $db = new Database();
        $table = 'ceec_t';

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

                $columns = ['c.id', 'cl.short_name', 'b.bank_name', 'c.prepayment_date', 'c.amount', 'c.used_amount', 'c.balance_amount', 'c.display'];
                $orderColumn = $columns[$orderColumnIndex] ?? 'c.id';

                // Base query
                $baseQuery = "
                    FROM ceec_t c
                    LEFT JOIN clients_t cl ON c.client_id = cl.id
                    LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                ";

                $whereClause = " WHERE 1=1 ";
                $params = [];

                // Search filter
                if (!empty($searchValue)) {
                    $whereClause .= " AND (
                        cl.short_name LIKE ? OR
                        cl.company_name LIKE ? OR
                        b.bank_name LIKE ? OR
                        b.bank_code LIKE ? OR
                        c.prepayment_date LIKE ? OR
                        CAST(c.amount AS CHAR) LIKE ? OR
                        CAST(c.used_amount AS CHAR) LIKE ? OR
                        CAST(c.balance_amount AS CHAR) LIKE ?
                    )";
                    $searchParam = "%{$searchValue}%";
                    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
                }

                // Client filter
                if (!empty($_GET['client_filter']) && $_GET['client_filter'] != '0') {
                    $clientFilter = intval($_GET['client_filter']);
                    $whereClause .= " AND c.client_id = ? ";
                    $params[] = $clientFilter;
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
                        c.client_id,
                        c.bank_id,
                        c.prepayment_date,
                        c.amount,
                        c.used_amount,
                        c.balance_amount,
                        c.display,
                        c.created_at,
                        c.updated_at,
                        cl.short_name,
                        cl.company_name,
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
                        'client_id' => $row['client_id'],
                        'bank_id' => $row['bank_id'],
                        'client_short_name' => $row['short_name'] ?? 'N/A',
                        'client_company_name' => $row['company_name'] ?? 'N/A',
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
         * GET CLIENT-WISE CEEC SUMMARY
         */
        if ($action === 'clientSummary' && $_SERVER['REQUEST_METHOD'] === 'GET') {
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

                $columns = ['cl.short_name', 'cl.company_name', 'total_amount', 'total_used', 'total_balance', 'record_count'];
                $orderColumn = $columns[$orderColumnIndex] ?? 'total_amount';

                // Base query - Group by client
                $baseQuery = "
                FROM clients_t cl

                INNER JOIN (
                    SELECT 
                        client_id,
                        COUNT(*) AS record_count,
                        SUM(amount) AS total_amount
                    FROM ceec_t
                    WHERE display = 'Y'
                    GROUP BY client_id
                ) c ON cl.id = c.client_id

                LEFT JOIN (
                    SELECT 
                        subscriber_id AS client_id,
                        SUM(ceec_amount) AS total_used
                    FROM exports_t
                    WHERE display = 'Y'
                    GROUP BY subscriber_id
                ) e ON e.client_id = cl.id

                WHERE cl.display = 'Y'
                ";


                $whereClause = "";
                $params = [];

                // Search filter
                if (!empty($searchValue)) {
                    $whereClause .= " AND (
                        cl.short_name LIKE ? OR
                        cl.company_name LIKE ?
                    )";
                    $searchParam = "%{$searchValue}%";
                    $params = array_merge($params, [$searchParam, $searchParam]);
                }

                // Client filter
                if (!empty($_GET['client_filter']) && $_GET['client_filter'] != '0') {
                    $clientFilter = intval($_GET['client_filter']);
                    $whereClause .= " AND cl.id = ? ";
                    $params[] = $clientFilter;
                }

                // Total records
                $totalRecordsQuery = "SELECT COUNT(*) as total " . $baseQuery . $whereClause;
                $totalRecordsResult = $db->customQuery($totalRecordsQuery, $params);
                $totalRecords = $totalRecordsResult[0]['total'] ?? 0;

                // Fetch data
                $dataQuery = "
                    SELECT 
                        cl.id as client_id,
                        cl.short_name,
                        cl.company_name,
                        c.record_count,
                        c.total_amount,
                            IFNULL(e.total_used, 0) AS total_used,
    (c.total_amount - IFNULL(e.total_used, 0)) AS total_balance

                    " . $baseQuery . $whereClause . "
                    ORDER BY $orderColumn $orderDir
                    LIMIT ?, ?
                ";
                //echo $dataQuery;exit;
                $params[] = $start;
                $params[] = $length;

                $records = $db->customQuery($dataQuery, $params);

                $data = [];
                foreach ($records as $row) {
                    $data[] = [
                        'client_id' => $row['client_id'],
                        'client_short_name' => $row['short_name'] ?? 'N/A',
                        'client_company_name' => $row['company_name'] ?? 'N/A',
                        'record_count' => $row['record_count'] ?? 0,
                        'total_amount' => $row['total_amount'] ?? 0,
                        'total_used' => $row['total_used'] ?? 0,
                        'total_balance' => $row['total_balance'] ?? 0
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
         * GET CLIENT CEEC DETAILS (for view modal)
         */
        if ($action === 'getClientCEECDetails' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $clientId = intval($_GET['client_id'] ?? 0);
            if ($clientId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Client ID']);
                exit;
            }

            // Get client info
            $clientQuery = "SELECT id, short_name, company_name FROM clients_t WHERE id = ? AND display = 'Y'";
            $clientInfo = $db->customQuery($clientQuery, [$clientId]);
            
            if (empty($clientInfo)) {
                echo json_encode(['success' => false, 'message' => 'Client not found']);
                exit;
            }

            // Get all CEEC records for this client
            $ceecQuery = "
                SELECT 
                    c.*,
                    b.bank_name,
                    b.bank_code
                FROM ceec_t c
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.client_id = ? AND c.display = 'Y'
                ORDER BY c.prepayment_date DESC
            ";
            $ceecRecords = $db->customQuery($ceecQuery, [$clientId]);

            // Calculate totals
            $totalAmount = 0;
            $totalUsed = 0;
            $totalBalance = 0;
            foreach ($ceecRecords as $record) {
                $totalAmount += $record['amount'];
                $totalUsed += $record['used_amount'];
                $totalBalance += $record['balance_amount'];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'client' => $clientInfo[0],
                    'records' => $ceecRecords,
                    'totals' => [
                        'total_amount' => $totalAmount,
                        'total_used' => $totalUsed,
                        'total_balance' => $totalBalance,
                        'record_count' => count($ceecRecords)
                    ]
                ]
            ]);
            exit;
        }

        /*
         * GET STATISTICS
         */
        if ($action === 'statistics' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            try {
                // Total CEEC records
                $totalQuery = "SELECT COUNT(*) as total FROM ceec_t WHERE display = 'Y'";
                $totalRecords = $db->customQuery($totalQuery)[0]['total'] ?? 0;

                // Total amount
                $amountQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM ceec_t WHERE display = 'Y'";
                $totalAmount = $db->customQuery($amountQuery)[0]['total'] ?? 0;

                // Total used amount
                $usedAmountQuery = "SELECT COALESCE(SUM(used_amount), 0) as total FROM ceec_t WHERE display = 'Y'";
                $totalUsedAmount = $db->customQuery($usedAmountQuery)[0]['total'] ?? 0;

                // Total balance amount
                $balanceAmountQuery = "SELECT COALESCE(SUM(balance_amount), 0) as total FROM ceec_t WHERE display = 'Y'";
                $totalBalanceAmount = $db->customQuery($balanceAmountQuery)[0]['total'] ?? 0;

                // This Month records
                $thisMonthQuery = "
                    SELECT COUNT(*) as total 
                    FROM ceec_t 
                    WHERE display = 'Y' 
                    AND YEAR(prepayment_date) = YEAR(CURDATE())
                    AND MONTH(prepayment_date) = MONTH(CURDATE())
                ";
                $thisMonthRecords = $db->customQuery($thisMonthQuery)[0]['total'] ?? 0;

                // Client-wise breakdown
                $clientQuery = "
                    SELECT 
                        cl.id,
                        cl.short_name,
                        cl.company_name,
                        COUNT(c.id) as record_count,
                        COALESCE(SUM(c.amount), 0) as total_amount,
                        COALESCE(SUM(c.used_amount), 0) as total_used,
                        COALESCE(SUM(c.balance_amount), 0) as total_balance
                    FROM clients_t cl
                    LEFT JOIN ceec_t c ON cl.id = c.client_id AND c.display = 'Y'
                    WHERE cl.display = 'Y'
                    GROUP BY cl.id, cl.short_name, cl.company_name
                    HAVING record_count > 0
                    ORDER BY total_amount DESC
                    LIMIT 10
                ";
                $clientCounts = $db->customQuery($clientQuery);

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
                    LEFT JOIN ceec_t c ON b.id = c.bank_id AND c.display = 'Y'
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
                        'client_counts' => $clientCounts,
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
         * GET SINGLE CEEC
         */
        if ($action === 'getCEEC' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $query = "
                SELECT 
                    c.*,
                    cl.short_name,
                    cl.company_name,
                    b.bank_name,
                    b.bank_code
                FROM ceec_t c
                LEFT JOIN clients_t cl ON c.client_id = cl.id
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.id = ?
            ";
            $result = $db->customQuery($query, [$id]);

            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'CEEC record not found']);
            }
            exit;
        }

        /*
         * INSERT
         */
        if ($action === 'insert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // CSRF validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $client_id = (int)($_POST['client_id'] ?? 0);
            $bank_id = (int)($_POST['bank_id'] ?? 0);
            $prepayment_date = s($_POST['prepayment_date'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            // Validation
            if ($client_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Client']);
                exit;
            }

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
                'client_id' => $client_id,
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
                    ? ['success' => true, 'message' => 'CEEC record added successfully!', 'id' => $insertId]
                    : ['success' => false, 'message' => 'Insert failed.']
                );
            } catch (Exception $e) {
                error_log('CEEC Insert Error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            exit;
        }

        /*
         * UPDATE
         */
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // CSRF validation
            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['ceec_id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $client_id = (int)($_POST['client_id'] ?? 0);
            $bank_id = (int)($_POST['bank_id'] ?? 0);
            $prepayment_date = s($_POST['prepayment_date'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            // Validation
            if ($client_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Client']);
                exit;
            }

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
            $oldRow = $db->selectData('ceec_t', '*', ['id' => $id]);
            if (empty($oldRow)) {
                echo json_encode(['success' => false, 'message' => 'CEEC record not found']);
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
                'client_id' => $client_id,
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
                    'message' => $update ? 'CEEC record updated successfully!' : 'Update failed.'
                ]);
            } catch (Exception $e) {
                error_log('CEEC Update Error: ' . $e->getMessage());
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
                'message' => $delete ? 'CEEC record deleted successfully!' : 'Delete failed.'
            ]);
            exit;
        }

        /*
         * EXPORT CEEC RECORDS LIST TO EXCEL (SIMPLE - ONLY NECESSARY COLUMNS)
         */
        /*
 * EXPORT CEEC RECORDS LIST TO EXCEL (SIMPLE - ONLY NECESSARY COLUMNS)
 */
if ($action === 'exportCEECRecordsList' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get current filters
    $clientFilter = !empty($_GET['client_filter']) && $_GET['client_filter'] != '0' ? intval($_GET['client_filter']) : 0;
    $bankFilter = !empty($_GET['bank_filter']) && $_GET['bank_filter'] != '0' ? intval($_GET['bank_filter']) : 0;
    $dateFrom = !empty($_GET['date_from']) ? $_GET['date_from'] : '';
    $dateTo = !empty($_GET['date_to']) ? $_GET['date_to'] : '';
    
    // Build WHERE clause
    $whereClause = " WHERE c.display = 'Y' ";
    $params = [];
    
    if ($clientFilter > 0) {
        $whereClause .= " AND c.client_id = ?";
        $params[] = $clientFilter;
    }
    
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
            cl.short_name as client_code,
            b.bank_name,
            c.prepayment_date,
            c.amount
        FROM ceec_t c
        LEFT JOIN clients_t cl ON c.client_id = cl.id
        LEFT JOIN banklist_master_t b ON c.bank_id = b.id
        {$whereClause}
        ORDER BY c.prepayment_date DESC, c.id DESC
    ";
    
    $ceecs = $db->customQuery($query, $params);

    // Correct path to vendor autoload
    $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
    
    if (!file_exists($vendorPath)) {
        die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
    }
    
    require_once $vendorPath;
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('CEEC Records');

    // Header styling
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
    ];

    // Title
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', 'CEEC RECORDS LIST');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // Headers row
    $headers = ['Client', 'Bank', 'Prepayment Date', 'Amount'];
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
    
    foreach ($ceecs as $ceec) {
        $sheet->setCellValue('A' . $row, $ceec['client_code'] ?? 'N/A');
        $sheet->setCellValue('B' . $row, $ceec['bank_name'] ?? 'N/A');
        $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($ceec['prepayment_date'])));
        $sheet->setCellValue('D' . $row, '$' . number_format($ceec['amount'], 2));
        
        // Center align all cells
        $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        
        $totalAmount += $ceec['amount'];
        
        $row++;
    }

    // Add totals row
    $row++;
    $sheet->setCellValue('C' . $row, 'TOTAL:');
    $sheet->setCellValue('D' . $row, '$' . number_format($totalAmount, 2));
    $sheet->getStyle('C' . $row . ':D' . $row)->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('C' . $row . ':D' . $row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FFEB3B');
    $sheet->getStyle('C' . $row . ':D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Auto-size columns
    foreach (range('A', 'D') as $col) {
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
    $sheet->getStyle('A3:D' . $row)->applyFromArray($styleArray);

    // Download
    $filename = 'CEEC_Records_List_' . date('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

        /*
         * EXPORT SINGLE CEEC TO EXCEL
         */
        if ($action === 'exportCEEC' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                die('Invalid ID');
            }

            $query = "
                SELECT 
                    c.*,
                    cl.short_name,
                    cl.company_name,
                    b.bank_name,
                    b.bank_code
                FROM ceec_t c
                LEFT JOIN clients_t cl ON c.client_id = cl.id
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.id = ?
            ";
            $ceecData = $db->customQuery($query, [$id]);

            if (empty($ceecData)) {
                die('CEEC record not found');
            }

            $ceec = $ceecData[0];

            // Correct path to vendor autoload
            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('CEEC Details');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            // Title
            $sheet->mergeCells('A1:D1');
            $sheet->setCellValue('A1', 'CEEC RECORD DETAILS');
            $sheet->getStyle('A1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(30);

            // Data
            $row = 3;
            
            $fields = [
                'Client Code' => $ceec['short_name'] ?? 'N/A',
                'Bank' => $ceec['bank_name'] ?? 'N/A',
                'Prepayment Date' => date('d-m-Y', strtotime($ceec['prepayment_date'])),
                'Amount' => '$' . number_format($ceec['amount'], 2),
                'Used Amount' => '$' . number_format($ceec['used_amount'], 2),
                'Balance Amount' => '$' . number_format($ceec['balance_amount'], 2)
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
            $filename = 'CEEC_' . $ceec['id'] . '_' . date('Y-m-d') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        /*
         * EXPORT CLIENT-WISE SUMMARY TO EXCEL
         */
        if ($action === 'exportClientSummary' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get filter
            $clientFilter = !empty($_GET['client_filter']) && $_GET['client_filter'] != '0' ? intval($_GET['client_filter']) : 0;
            
            // Build query
            $whereClause = "";
            $params = [];
            
            if ($clientFilter > 0) {
                $whereClause = " AND cl.id = ?";
                $params[] = $clientFilter;
            }
            
            $query = "
                SELECT 
                    cl.id as client_id,
                    cl.short_name,
                    cl.company_name,
                    COUNT(c.id) as record_count,
                    COALESCE(SUM(c.amount), 0) as total_amount,
                    COALESCE(SUM(c.used_amount), 0) as total_used,
                    COALESCE(SUM(c.balance_amount), 0) as total_balance
                FROM clients_t cl
                INNER JOIN ceec_t c ON cl.id = c.client_id AND c.display = 'Y'
                WHERE cl.display = 'Y' {$whereClause}
                GROUP BY cl.id, cl.short_name, cl.company_name
                ORDER BY total_amount DESC
            ";
            
            $summaryData = $db->customQuery($query, $params);

            // Correct path to vendor autoload
            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Client Summary');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            // Title
            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue('A1', 'CLIENT-WISE CEEC AMOUNT BREAKDOWN & BALANCE SUMMARY');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(35);

            // Headers
            $headers = ['Client Code', 'Client Name', 'Total Records', 'Total Amount', 'Used Amount', 'Balance Amount'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->applyFromArray($headerStyle);
                $col++;
            }

            // Data
            $row = 4;
            $grandTotalRecords = 0;
            $grandTotalAmount = 0;
            $grandTotalUsed = 0;
            $grandTotalBalance = 0;
            
            foreach ($summaryData as $client) {
                $sheet->setCellValue('A' . $row, $client['short_name'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, $client['company_name'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $client['record_count']);
                $sheet->setCellValue('D' . $row, '$' . number_format($client['total_amount'], 2));
                $sheet->setCellValue('E' . $row, '$' . number_format($client['total_used'], 2));
                $sheet->setCellValue('F' . $row, '$' . number_format($client['total_balance'], 2));
                
                // Calculate percentage
                $balancePercent = $client['total_amount'] > 0 ? ($client['total_balance'] / $client['total_amount']) * 100 : 0;
                
                // Color code based on balance percentage
                if ($balancePercent >= 70) {
                    $color = 'C6EFCE'; // Light green
                } elseif ($balancePercent >= 30) {
                    $color = 'FFEB9C'; // Light yellow
                } else {
                    $color = 'FFC7CE'; // Light red
                }
                
                $sheet->getStyle('F' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($color);
                
                $grandTotalRecords += $client['record_count'];
                $grandTotalAmount += $client['total_amount'];
                $grandTotalUsed += $client['total_used'];
                $grandTotalBalance += $client['total_balance'];
                
                $row++;
            }

            // Add grand total row
            $row++;
            $sheet->setCellValue('B' . $row, 'GRAND TOTALS:');
            $sheet->setCellValue('C' . $row, $grandTotalRecords);
            $sheet->setCellValue('D' . $row, '$' . number_format($grandTotalAmount, 2));
            $sheet->setCellValue('E' . $row, '$' . number_format($grandTotalUsed, 2));
            $sheet->setCellValue('F' . $row, '$' . number_format($grandTotalBalance, 2));
            $sheet->getStyle('B' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':F' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('667eea');
            $sheet->getStyle('B' . $row . ':F' . $row)->getFont()->getColor()->setRGB('FFFFFF');

            // Auto-size columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Download
            $filename = 'Client_CEEC_Summary_' . date('Y-m-d_His') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        /*
         * EXPORT INDIVIDUAL CLIENT CEEC SUMMARY TO EXCEL
         */
        if ($action === 'exportClientCEECSummary' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $clientId = intval($_GET['client_id'] ?? 0);
            
            if ($clientId <= 0) {
                die('Invalid Client ID');
            }
            
            // Get client info
            $clientQuery = "SELECT id, short_name, company_name FROM clients_t WHERE id = ? AND display = 'Y'";
            $clientInfo = $db->customQuery($clientQuery, [$clientId]);
            
            if (empty($clientInfo)) {
                die('Client not found');
            }
            
            $client = $clientInfo[0];
            
            // Get all CEEC records for this client
            $query = "
                SELECT 
                    c.*,
                    b.bank_name,
                    b.bank_code
                FROM ceec_t c
                LEFT JOIN banklist_master_t b ON c.bank_id = b.id
                WHERE c.client_id = ? AND c.display = 'Y'
                ORDER BY c.prepayment_date DESC, c.id DESC
            ";
            $ceecRecords = $db->customQuery($query, [$clientId]);
            
            // Calculate totals
            $totalAmount = 0;
            $totalUsed = 0;
            $totalBalance = 0;
            foreach ($ceecRecords as $record) {
                $totalAmount += $record['amount'];
                $totalUsed += $record['used_amount'];
                $totalBalance += $record['balance_amount'];
            }

            // Correct path to vendor autoload
            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Client CEEC Summary');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            // Title
            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue('A1', 'CEEC RECORDS FOR CLIENT: ' . $client['short_name'] . ' - ' . $client['company_name']);
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(35);

            // Headers
            $headers = ['Bank', 'Prepayment Date', 'Amount', 'Used Amount', 'Balance Amount', 'Status'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->applyFromArray($headerStyle);
                $col++;
            }

            // Data
            $row = 4;
            foreach ($ceecRecords as $record) {
                $balancePercent = $record['amount'] > 0 ? ($record['balance_amount'] / $record['amount']) * 100 : 0;
                $status = $balancePercent >= 70 ? 'Healthy' : ($balancePercent >= 30 ? 'Moderate' : 'Low');
                
                $sheet->setCellValue('A' . $row, $record['bank_name'] ?? 'N/A');
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($record['prepayment_date'])));
                $sheet->setCellValue('C' . $row, '$' . number_format($record['amount'], 2));
                $sheet->setCellValue('D' . $row, '$' . number_format($record['used_amount'], 2));
                $sheet->setCellValue('E' . $row, '$' . number_format($record['balance_amount'], 2));
                $sheet->setCellValue('F' . $row, $status);
                $row++;
            }

            // Add totals row
            $row++;
            $sheet->setCellValue('B' . $row, 'TOTALS:');
            $sheet->setCellValue('C' . $row, '$' . number_format($totalAmount, 2));
            $sheet->setCellValue('D' . $row, '$' . number_format($totalUsed, 2));
            $sheet->setCellValue('E' . $row, '$' . number_format($totalBalance, 2));
            $sheet->getStyle('B' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row . ':E' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB3B');

            // Summary section
            $row += 3;
            $sheet->setCellValue('A' . $row, 'SUMMARY');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $summary = [
                'Total Records' => count($ceecRecords),
                'Total Amount' => '$' . number_format($totalAmount, 2),
                'Total Used Amount' => '$' . number_format($totalUsed, 2),
                'Total Balance Amount' => '$' . number_format($totalBalance, 2),
                'Balance Percentage' => $totalAmount > 0 ? number_format(($totalBalance / $totalAmount) * 100, 2) . '%' : '0%'
            ];
            
            foreach ($summary as $label => $value) {
                $sheet->setCellValue('A' . $row, $label);
                $sheet->setCellValue('B' . $row, $value);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Download
            $filename = 'Client_' . $client['short_name'] . '_CEEC_' . date('Y-m-d_His') . '.xlsx';
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