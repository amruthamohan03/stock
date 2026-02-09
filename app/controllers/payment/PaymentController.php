<?php

class PaymentController extends Controller
{
    // Role constants
    const ROLE_MANAGEMENT = 1;
    const ROLE_DEPARTMENT = 3;
    const ROLE_FINANCE_CASH = 4;
    const ROLE_FINANCE_BANK = 5;
    const ROLE_CASHIER = 10;
    const ROLE_BANK_OFFICER = 11;
    const ROLE_LOCATION_MANAGER = 27; // Location 3
    
    // NEW: Location 2 Roles
    const ROLE_LOCATION_2_DEPT_MGMT = 33; // Department + Management for Location 2
    const ROLE_LOCATION_2_FINANCE_PAID = 34; // Finance + Paid (Cash & Bank) for Location 2
    
    // NEW: Location 1 Role
    const ROLE_LOCATION_1_MANAGER = 39; // Management approval ONLY for Location 1

    // File upload constants
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    private $uploadDir;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $this->uploadDir = UPLOAD_PATH.'/payments/';
        
        if (!file_exists($this->uploadDir)) {
            if (!@mkdir($this->uploadDir, 0755, true)) {
                error_log("CRITICAL: Cannot create upload directory: " . $this->uploadDir);
                error_log("Error: " . (error_get_last()['message'] ?? 'Unknown error'));
            } else {
                error_log("SUCCESS: Created upload directory: " . $this->uploadDir);
            }
        }
        
        if (!is_writable($this->uploadDir)) {
            error_log("WARNING: Upload directory not writable: " . $this->uploadDir);
            @chmod($this->uploadDir, 0755);
            
            if (!is_writable($this->uploadDir)) {
                error_log("CRITICAL: Still not writable after chmod. Check server permissions.");
            }
        } else {
            error_log("SUCCESS: Upload directory is writable: " . $this->uploadDir);
        }
    }

    private function validateCSRF()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $this->jsonResponse(false, 'Invalid security token. Please refresh the page.');
            }
        }
    }

    private function jsonResponse($success, $message, $data = null)
    {
        header('Content-Type: application/json');
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit;
    }

    private function validateSession()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_data']['role_id'])) {
            $this->jsonResponse(false, 'Unauthorized access. Please login.');
        }
        return [
            'user_id' => $_SESSION['user_id'],
            'role_id' => $_SESSION['user_data']['role_id']
        ];
    }

    private function validateFileUpload($fileKey)
    {
        if (empty($_FILES[$fileKey]['name'])) {
            return null;
        }

        $file = $_FILES[$fileKey];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload',
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? "Unknown upload error: " . $file['error'];
            throw new Exception("File upload error for {$fileKey}: " . $errorMsg);
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new Exception("File {$fileKey} exceeds maximum size of 5MB (uploaded: " . round($file['size']/1024/1024, 2) . "MB)");
        }

        if ($file['size'] == 0) {
            throw new Exception("File {$fileKey} is empty (0 bytes)");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception("File {$fileKey} has invalid extension '{$extension}'. Allowed: " . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        if (!file_exists($file['tmp_name'])) {
            throw new Exception("Temporary file not found for {$fileKey}");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new Exception("File {$fileKey} has invalid MIME type: {$mimeType}. This might be a security risk.");
        }

        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $uniqueName = time() . '_' . uniqid() . '_' . $sanitizedName;

        return [
            'tmp_name' => $file['tmp_name'],
            'unique_name' => $uniqueName,
            'original_name' => $file['name'],
            'size' => $file['size']
        ];
    }

    private function uploadFile($fileInfo)
    {
        if (!$fileInfo) {
            return null;
        }

        $destination = $this->uploadDir . $fileInfo['unique_name'];

        error_log("Attempting to upload file to: " . $destination);
        error_log("Temp file location: " . $fileInfo['tmp_name']);
        error_log("Temp file exists: " . (file_exists($fileInfo['tmp_name']) ? 'YES' : 'NO'));
        error_log("Destination directory exists: " . (file_exists($this->uploadDir) ? 'YES' : 'NO'));
        error_log("Destination directory writable: " . (is_writable($this->uploadDir) ? 'YES' : 'NO'));

        if (!move_uploaded_file($fileInfo['tmp_name'], $destination)) {
            $error = error_get_last();
            throw new Exception("Failed to upload file. Error: " . ($error['message'] ?? 'Unknown error') . " | Destination: " . $destination);
        }

        if (!file_exists($destination)) {
            throw new Exception("File upload verification failed - file does not exist at: " . $destination);
        }

        error_log("SUCCESS: File uploaded to: " . $destination);

        return "uploads/payments/" . $fileInfo['unique_name'];
    }

    private function getFileUrl($relativePath)
    {
        if (empty($relativePath)) {
            return null;
        }
        
        $fullPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($relativePath, '/');

        if (file_exists($fullPath) && is_readable($fullPath)) {
            return BASE_URL . $relativePath;
        }
        
        error_log("File not found or not readable: " . $fullPath);
        return null;
    }

    private function deleteOldFiles($payment_id, $fileKeys = ['file1', 'file2'])
    {
        $db = new Database();
        
        $columns = [];
        foreach ($fileKeys as $key) {
            $columns[] = $key . '_path';
        }
        
        $sql = "SELECT " . implode(', ', $columns) . " FROM payment_requests WHERE id = :id";
        $existingFiles = $db->customQuery($sql, [':id' => $payment_id]);
        
        if (!empty($existingFiles)) {
            $existingFiles = $existingFiles[0];
            
            foreach ($fileKeys as $key) {
                $pathKey = $key . '_path';
                if (!empty($existingFiles[$pathKey])) {
                    $oldPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($existingFiles[$pathKey], '/');
                    if (file_exists($oldPath)) {
                        if (@unlink($oldPath)) {
                            error_log("Deleted old file: " . $oldPath);
                        } else {
                            error_log("Failed to delete old file: " . $oldPath);
                        }
                    }
                }
            }
        }
    }

    public function index()
    {
        $db = new Database();
        $dept     = $db->selectData('department_master_t','*',[]);
        $loc      = $db->selectData('main_office_master_t','*',[]);
        $client   = $db->selectData('clients_t','*',[]);
        $currency = $db->selectData('currency_master_t','*',[],2);
        $expense  = $db->selectData('expense_type_master_t','*',[]);

        $data = [
            'title'     => 'Payment Request',
            'dept'      => $dept,
            'loc'       => $loc,
            'client'    => $client,
            'currency'  => $currency,
            'expense'   => $expense,
            'csrf_token' => $_SESSION['csrf_token'],
            'current_user_id' => $_SESSION['user_id']
        ];

        $this->viewWithLayout('payment/payment', $data);
    }

    public function get_status_counts()
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();
        $userId = $user['user_id'];
        $userRole = $user['role_id'];
        
        try {
            $whereConditions = [];
            $params = [];
            
            if ($userRole == self::ROLE_FINANCE_BANK) {
                $whereConditions[] = "payment_type = :payment_type_bank";
                $params[':payment_type_bank'] = 'Bank';
            }
            // Location 3 Manager (Role 27)
            else if ($userRole == self::ROLE_LOCATION_MANAGER) {
                $whereConditions[] = "location_id = :location_id";
                $params[':location_id'] = 3;
            }
            // NEW: Location 2 - Dept/Mgmt (Role 33)
            else if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT) {
                $whereConditions[] = "location_id = :location_id";
                $params[':location_id'] = 2;
            }
            // NEW: Location 2 - Finance/Paid (Role 34)
            else if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID) {
                $whereConditions[] = "location_id = :location_id";
                $params[':location_id'] = 2;
            }
            // NEW: Location 1 Manager (Role 39)
            else if ($userRole == self::ROLE_LOCATION_1_MANAGER) {
                $whereConditions[] = "location_id = :location_id";
                $params[':location_id'] = 1;
            }
            else if (!in_array($userRole, [self::ROLE_MANAGEMENT, self::ROLE_DEPARTMENT, self::ROLE_FINANCE_CASH])) {
                $visibilitySQL = "";
                
                switch ($userRole) {
                    case self::ROLE_CASHIER:
                        // Cashier can see: their own requests OR all Cash payment requests (excluding location 2 & 3)
                        $visibilitySQL = "(
                            created_by = :cuid1 
                            OR (payment_type = 'Cash' AND location_id NOT IN (2, 3))
                            OR paid_approved_by = :cuid2
                        )";
                        $params[':cuid1'] = $userId;
                        $params[':cuid2'] = $userId;
                        break;
                        
                    case self::ROLE_BANK_OFFICER:
                        // Bank Officer can see: their own requests OR all Bank payment requests (ALL locations)
                        $visibilitySQL = "(
                            created_by = :cuid1 
                            OR payment_type = 'Bank'
                            OR paid_approved_by = :cuid2
                        )";
                        $params[':cuid1'] = $userId;
                        $params[':cuid2'] = $userId;
                        break;
                        
                    default:
                        $visibilitySQL = "created_by = :cuid1";
                        $params[':cuid1'] = $userId;
                        break;
                }
                
                if ($visibilitySQL) {
                    $whereConditions[] = $visibilitySQL;
                }
            }
            
            $whereClause = '';
            if (!empty($whereConditions)) {
                $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
            }
            
            $sql = "SELECT 
                COUNT(*) as total,
                
                -- PENDING DEPT: dept not processed yet AND no rejections anywhere
                SUM(CASE 
                    WHEN dept_approval IS NULL 
                    AND COALESCE(finance_approval, 0) != -1
                    AND COALESCE(management_approval, 0) != -1
                    AND COALESCE(paid_approval, 0) != -1
                    THEN 1 ELSE 0 END) as waiting_dept,
                
                -- PENDING FINANCE: dept approved, finance not processed yet, no rejections
                SUM(CASE 
                    WHEN dept_approval = 1 
                    AND finance_approval IS NULL 
                    AND COALESCE(management_approval, 0) != -1
                    AND COALESCE(paid_approval, 0) != -1
                    THEN 1 ELSE 0 END) as waiting_finance,
                
                -- PENDING MGMT: dept & finance approved, mgmt not processed yet, no rejections
                SUM(CASE 
                    WHEN dept_approval = 1 
                    AND finance_approval = 1 
                    AND management_approval IS NULL
                    AND COALESCE(paid_approval, 0) != -1
                    THEN 1 ELSE 0 END) as waiting_mgmt,
                
                -- PENDING PAYMENT: all approved, payment not processed yet
                SUM(CASE 
                    WHEN dept_approval = 1 
                    AND finance_approval = 1 
                    AND management_approval = 1 
                    AND paid_approval IS NULL
                    THEN 1 ELSE 0 END) as waiting_payment,
                
                -- PAID: payment approved
                SUM(CASE WHEN paid_approval = 1 THEN 1 ELSE 0 END) as paid,
                
                -- REJECTED: any stage rejected
                SUM(CASE 
                    WHEN dept_approval = -1 
                    OR finance_approval = -1 
                    OR management_approval = -1 
                    OR paid_approval = -1 
                    THEN 1 ELSE 0 END) as rejected
                FROM payment_requests" . $whereClause;
            
            $result = $db->customQuery($sql, $params);
            $counts = $result[0] ?? [
                'total' => 0,
                'waiting_dept' => 0,
                'waiting_finance' => 0,
                'waiting_mgmt' => 0,
                'waiting_payment' => 0,
                'paid' => 0,
                'rejected' => 0
            ];
            
            $this->jsonResponse(true, 'Status counts retrieved', $counts);
            
        } catch (Exception $e) {
            error_log("Status counts error: " . $e->getMessage());
            $this->jsonResponse(false, 'Error fetching status counts');
        }
    }

    public function get_list()
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();
        
        $draw = intval($_GET['draw'] ?? 1);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        $statusFilter = $_GET['status_filter'] ?? 'all';
        
        $search = '';
        if (isset($_GET['search']) && is_array($_GET['search'])) {
            $search = trim($_GET['search']['value'] ?? '');
        }
        
        $whereConditions = [];
        $params = [];
        
        $userId = $user['user_id'];
        $userRole = $user['role_id'];
        
        if ($userRole == self::ROLE_FINANCE_BANK) {
            $whereConditions[] = "pr.payment_type = :payment_type_bank";
            $params[':payment_type_bank'] = 'Bank';
        }
        // Location 3 Manager (Role 27)
        else if ($userRole == self::ROLE_LOCATION_MANAGER) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 3;
        }
        // NEW: Location 2 - Dept/Mgmt (Role 33)
        else if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 2;
        }
        // NEW: Location 2 - Finance/Paid (Role 34)
        else if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 2;
        }
        // NEW: Location 1 Manager (Role 39)
        else if ($userRole == self::ROLE_LOCATION_1_MANAGER) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 1;
        }
        else if (!in_array($userRole, [self::ROLE_MANAGEMENT, self::ROLE_DEPARTMENT, self::ROLE_FINANCE_CASH])) {
            $visibilitySQL = "";
            
            switch ($userRole) {
                case self::ROLE_CASHIER:
                    // Cashier can see: their own requests OR all Cash payment requests (excluding location 2 & 3)
                    $visibilitySQL = "(
                        pr.created_by = :uid1 
                        OR (pr.payment_type = 'Cash' AND pr.location_id NOT IN (2, 3))
                        OR pr.paid_approved_by = :uid2
                    )";
                    $params[':uid1'] = $userId;
                    $params[':uid2'] = $userId;
                    break;
                    
                case self::ROLE_BANK_OFFICER:
                    // Bank Officer can see: their own requests OR all Bank payment requests (ALL locations)
                    $visibilitySQL = "(
                        pr.created_by = :uid1 
                        OR pr.payment_type = 'Bank'
                        OR pr.paid_approved_by = :uid2
                    )";
                    $params[':uid1'] = $userId;
                    $params[':uid2'] = $userId;
                    break;
                    
                default:
                    $visibilitySQL = "pr.created_by = :uid1";
                    $params[':uid1'] = $userId;
                    break;
            }
            
            if ($visibilitySQL) {
                $whereConditions[] = $visibilitySQL;
            }
        }
        
        switch ($statusFilter) {
            case 'waiting_dept':
                $whereConditions[] = "pr.dept_approval IS NULL 
                    AND COALESCE(pr.finance_approval, 0) != -1
                    AND COALESCE(pr.management_approval, 0) != -1
                    AND COALESCE(pr.paid_approval, 0) != -1";
                break;
                
            case 'waiting_finance':
                $whereConditions[] = "pr.dept_approval = 1 
                    AND pr.finance_approval IS NULL
                    AND COALESCE(pr.management_approval, 0) != -1
                    AND COALESCE(pr.paid_approval, 0) != -1";
                break;
                
            case 'waiting_mgmt':
                $whereConditions[] = "pr.dept_approval = 1 
                    AND pr.finance_approval = 1 
                    AND pr.management_approval IS NULL
                    AND COALESCE(pr.paid_approval, 0) != -1";
                break;
                
            case 'waiting_payment':
                $whereConditions[] = "pr.dept_approval = 1 
                    AND pr.finance_approval = 1 
                    AND pr.management_approval = 1 
                    AND pr.paid_approval IS NULL";
                break;
                
            case 'paid':
                $whereConditions[] = "pr.paid_approval = 1";
                break;
                
            case 'rejected':
                $whereConditions[] = "(pr.dept_approval = -1 
                    OR pr.finance_approval = -1 
                    OR pr.management_approval = -1 
                    OR pr.paid_approval = -1)";
                break;
        }
        
        if (!empty($search)) {
            $searchPattern = "%{$search}%";
            
            $whereConditions[] = "(
                pr.beneficiary LIKE :search1
                OR pr.motif LIKE :search2
                OR pr.requestee LIKE :search3
                OR COALESCE(pr.cash_collector, '') LIKE :search4
                OR CAST(pr.id AS CHAR) LIKE :search5
                OR CAST(pr.amount AS CHAR) LIKE :search6
                OR COALESCE(c.short_name, '') LIKE :search7
                OR ex.expense_type_name LIKE :search8
                OR pr.payment_type LIKE :search9
                OR cu.currency_short_name LIKE :search10
            )";
            
            for ($i = 1; $i <= 10; $i++) {
                $params[":search{$i}"] = $searchPattern;
            }
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql = "SELECT pr.*, 
                       d.department_name, 
                       COALESCE(c.short_name, 'N/A') AS client_name,
                       ex.expense_type_name,
                       cu.currency_short_name
                FROM payment_requests pr
                LEFT JOIN department_master_t d ON d.id = pr.department
                LEFT JOIN clients_t c ON c.id = pr.client_id
                LEFT JOIN expense_type_master_t ex ON ex.id = pr.expense_type
                LEFT JOIN currency_master_t cu ON cu.id = pr.currency"
                . $whereClause;
        
        $countSql = "SELECT COUNT(*) AS total 
                     FROM payment_requests pr
                     LEFT JOIN department_master_t d ON d.id = pr.department
                     LEFT JOIN clients_t c ON c.id = pr.client_id
                     LEFT JOIN expense_type_master_t ex ON ex.id = pr.expense_type
                     LEFT JOIN currency_master_t cu ON cu.id = pr.currency"
                     . $whereClause;
        
        try {
            $countResult = $db->customQuery($countSql, $params);
            $recordsFiltered = $countResult[0]['total'] ?? 0;
            
            $totalParams = [];
            $totalWhereConditions = [];
            
            if ($userRole == self::ROLE_FINANCE_BANK) {
                $totalWhereConditions[] = "payment_type = :total_payment_type_bank";
                $totalParams[':total_payment_type_bank'] = 'Bank';
            }
            // Location 3 Manager
            else if ($userRole == self::ROLE_LOCATION_MANAGER) {
                $totalWhereConditions[] = "location_id = :total_location_id";
                $totalParams[':total_location_id'] = 3;
            }
            // NEW: Location 2 Roles
            else if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT) {
                $totalWhereConditions[] = "location_id = :total_location_id";
                $totalParams[':total_location_id'] = 2;
            }
            else if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID) {
                $totalWhereConditions[] = "location_id = :total_location_id";
                $totalParams[':total_location_id'] = 2;
            }
            // NEW: Location 1 Manager
            else if ($userRole == self::ROLE_LOCATION_1_MANAGER) {
                $totalWhereConditions[] = "location_id = :total_location_id";
                $totalParams[':total_location_id'] = 1;
            }
            else if (!in_array($userRole, [self::ROLE_MANAGEMENT, self::ROLE_DEPARTMENT, self::ROLE_FINANCE_CASH])) {
                $totalVisibilitySQL = "";
                
                switch ($userRole) {
                    case self::ROLE_CASHIER:
                        $totalVisibilitySQL = "(created_by = :tuid1 OR (payment_type = 'Cash' AND location_id NOT IN (2, 3)) OR paid_approved_by = :tuid2)";
                        $totalParams[':tuid1'] = $userId;
                        $totalParams[':tuid2'] = $userId;
                        break;
                        
                    case self::ROLE_BANK_OFFICER:
                        $totalVisibilitySQL = "(created_by = :tuid1 OR payment_type = 'Bank' OR paid_approved_by = :tuid2)";
                        $totalParams[':tuid1'] = $userId;
                        $totalParams[':tuid2'] = $userId;
                        break;
                        
                    default:
                        $totalVisibilitySQL = "created_by = :tuid1";
                        $totalParams[':tuid1'] = $userId;
                        break;
                }
                
                if ($totalVisibilitySQL) {
                    $totalWhereConditions[] = $totalVisibilitySQL;
                }
            }
            
            $totalWhere = "";
            if (!empty($totalWhereConditions)) {
                $totalWhere = " WHERE " . implode(" AND ", $totalWhereConditions);
            }
            
            $totalSql = "SELECT COUNT(*) AS total FROM payment_requests" . $totalWhere;
            $totalResult = $db->customQuery($totalSql, $totalParams);
            $recordsTotal = $totalResult[0]['total'] ?? 0;
            
            $start = (int)$start;
            $length = (int)$length;
            $sql .= sprintf(" ORDER BY pr.id DESC LIMIT %d, %d", $start, $length);
            
            $data = $db->customQuery($sql, $params);
            if (!is_array($data)) {
                $data = [];
            }
            
            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $data
            ]);
            
        } catch (Exception $e) {
            error_log("Payment list error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load payment requests: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }

    public function check_mca_duplicate()
    {
        $this->validateCSRF();
        
        $mca_ref = $_POST['mca_ref'] ?? '';
        $expense_type = $_POST['expense_type'] ?? '';
        $current_payment_id = $_POST['payment_id'] ?? null;
        
        if (empty($mca_ref) || empty($expense_type)) {
            $this->jsonResponse(false, 'Invalid parameters', ['exists' => false]);
        }
        
        $db = new Database();
        
        $sql = "SELECT pr.id, pr.mca_data 
                FROM payment_requests pr 
                WHERE pr.expense_type = :expense_type";
        
        if ($current_payment_id) {
            $sql .= " AND pr.id != :payment_id";
        }
        
        $params = [':expense_type' => $expense_type];
        if ($current_payment_id) {
            $params[':payment_id'] = $current_payment_id;
        }
        
        $results = $db->customQuery($sql, $params);
        
        foreach ($results as $row) {
            if (!empty($row['mca_data'])) {
                $mcaData = json_decode($row['mca_data'], true);
                if ($mcaData) {
                    foreach ($mcaData as $mca) {
                        if (isset($mca['mca_ref']) && $mca['mca_ref'] === $mca_ref) {
                            $this->jsonResponse(true, "MCA Reference '{$mca_ref}' is already used for this Expense Type in Payment Request #{$row['id']}", [
                                'exists' => true,
                                'payment_id' => $row['id']
                            ]);
                        }
                    }
                }
            }
        }
        
        $this->jsonResponse(true, 'Reference available', ['exists' => false]);
    }

    public function validate_mca_exists()
    {
        $this->validateCSRF();
        
        $mca_ref = $_POST['mca_ref'] ?? '';
        $pay_for = $_POST['pay_for'] ?? '';
        $client_id = $_POST['client_id'] ?? null;
        
        if (empty($mca_ref) || !isset($pay_for)) {
            $this->jsonResponse(false, 'Invalid parameters', ['exists' => false]);
        }
        
        $db = new Database();
        
        $mapping = [
            '0' => ['table' => 'imports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
            '1' => ['table' => 'exports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
            '2' => ['table' => 'locals_t', 'client_col' => 'clients_id', 'ref_col' => 'lt_reference']
        ];
        
        if (!isset($mapping[$pay_for])) {
            $this->jsonResponse(true, 'Reference valid for this payment type', [
                'exists' => true,
                'skip_validation' => true
            ]);
            return;
        }
        
        $config = $mapping[$pay_for];
        $table = $config['table'];
        $clientCol = $config['client_col'];
        $refCol = $config['ref_col'];
        
        $sql = "SELECT id, {$refCol} as ref, {$clientCol} as client_id 
                FROM {$table} 
                WHERE ";
        
        $params = [];
        
        if ($table === 'locals_t') {
            $sql .= "{$refCol} = :mca_ref";
            $params[':mca_ref'] = $mca_ref;
        } else {
            $sql .= "({$refCol} = :mca_ref OR {$refCol} LIKE :mca_ref_json)";
            $params[':mca_ref'] = $mca_ref;
            $params[':mca_ref_json'] = '%"mca_ref":"' . $mca_ref . '"%';
        }
        
        if ($client_id) {
            $sql .= " AND {$clientCol} = :client_id";
            $params[':client_id'] = $client_id;
        }
        
        $sql .= " LIMIT 1";
        
        try {
            $results = $db->customQuery($sql, $params);
            
            if (empty($results)) {
                $this->jsonResponse(false, "MCA Reference '{$mca_ref}' not found in tracking system", [
                    'exists' => false,
                    'table_checked' => $table
                ]);
                return;
            }
            
            if ($client_id && $results[0]['client_id'] != $client_id) {
                $this->jsonResponse(false, "MCA Reference '{$mca_ref}' belongs to a different client", [
                    'exists' => false,
                    'wrong_client' => true,
                    'actual_client_id' => $results[0]['client_id']
                ]);
                return;
            }
            
            $this->jsonResponse(true, 'Reference exists in tracking system', [
                'exists' => true,
                'tracking_id' => $results[0]['id'],
                'table' => $table
            ]);
            
        } catch (Exception $e) {
            error_log("MCA validation error: " . $e->getMessage());
            $this->jsonResponse(false, 'Error validating MCA reference: ' . $e->getMessage(), ['exists' => false]);
        }
    }

    public function store()
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();

        $action        = $_POST['action'] ?? 'insert';
        $payment_id    = $_POST['payment_id'] ?? null;

        $department_id = $_POST['department'] ?? null; 
        $location_id   = $_POST['location'] ?? null;
        $beneficiary   = $_POST['beneficiary'] ?? null;
        $requestee     = $_POST['requestee'] ?? null;

        $client_id     = $_POST['client_id'] ?? 0;
        $client_id = ($client_id === '' || !is_numeric($client_id)) ? null : (int)$client_id;
        
        if (empty($requestee)) {
            $this->jsonResponse(false, 'Requestee is required');
        }
        
        $pay_for       = $_POST['pay_for'] ?? null;
        $currency_id   = $_POST['currency'] ?? null;
        $amount        = $_POST['amount'] ?? 0;
        $payment_type  = $_POST['payment_type'] ?? null;
        $expense_type  = $_POST['expense_type'] ?? null;
        $motif         = $_POST['motif'] ?? null;
        $cash_collector = $_POST['cash_collector'] ?? null;
        
        $mca_refs      = $_POST['mca_reference'] ?? [];
        $mca_amounts   = $_POST['mca_amount'] ?? [];

        if (!$department_id || !$location_id || !$currency_id || !$amount || !$expense_type) {
            $this->jsonResponse(false, 'All required fields must be filled');
        }

        if (!empty($mca_amounts)) {
            $totalMca = array_sum(array_map('floatval', $mca_amounts));

            if (abs((float)$totalMca - (float)$amount) > 0.01) {
                $this->jsonResponse(false, "MCA total ($totalMca) must match Amount ($amount)");
            }
        }
         
        $db->beginTransaction();

        try {
            $file_paths = [];
            
            foreach (['file1', 'file2'] as $fileKey) {
                try {
                    error_log("Processing {$fileKey}...");
                    $fileInfo = $this->validateFileUpload($fileKey);
                    
                    if ($fileInfo) {
                        error_log("{$fileKey} validated successfully. Size: " . $fileInfo['size'] . " bytes");
                        
                        if ($action === "update" && $payment_id) {
                            $this->deleteOldFiles($payment_id, [$fileKey]);
                        }
                        
                        $uploaded_path = $this->uploadFile($fileInfo);
                        
                        if ($uploaded_path) {
                            $file_paths[$fileKey] = $uploaded_path;
                            error_log("Successfully uploaded {$fileKey}: {$uploaded_path}");
                        } else {
                            error_log("Upload returned null for {$fileKey}");
                        }
                    } else {
                        error_log("{$fileKey} - No file uploaded (this is OK if optional)");
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log("File upload error for {$fileKey}: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    $this->jsonResponse(false, "File upload failed for {$fileKey}: " . $e->getMessage());
                }
            }

            $mca_data = [];
            foreach ($mca_refs as $i => $ref) {
                if (!trim($ref) || !isset($mca_amounts[$i])) continue;

                $mca_data[] = [
                    "mca_ref" => trim($ref),
                    "amount"  => (float)$mca_amounts[$i]
                ];
            }

            $paymentData = [
                "beneficiary"  => $beneficiary,
                "requestee"    => $requestee,
                "department"   => $department_id,
                "location_id"  => $location_id,
                "client_id"    => $client_id,
                "pay_for"      => $pay_for,
                "currency"     => $currency_id,
                "amount"       => $amount,
                "payment_type" => $payment_type,
                "expense_type" => $expense_type,
                "motif"        => $motif,
                "cash_collector" => $cash_collector,
                "mca_ref"      => $mca_data[0]['mca_ref'] ?? null,
                "mca_data"     => json_encode($mca_data),
            ];

            if (isset($file_paths['file1'])) {
                $paymentData['file1_path'] = $file_paths['file1'];
            }
            if (isset($file_paths['file2'])) {
                $paymentData['file2_path'] = $file_paths['file2'];
            }

            if ($action === "update" && $payment_id) {
                $paymentData['updated_at'] = date('Y-m-d H:i:s');
                $paymentData['updated_by'] = $user['user_id'];
                
                $existingSql = "SELECT * FROM payment_requests WHERE id = :id";
                $existingResult = $db->customQuery($existingSql, [':id' => $payment_id]);
                $existingPayment = $existingResult[0] ?? null;
                
                if ($existingPayment && (
                    $existingPayment['dept_approval'] == -1 ||
                    $existingPayment['finance_approval'] == -1 ||
                    $existingPayment['management_approval'] == -1 ||
                    $existingPayment['paid_approval'] == -1
                )) {
                    $resetFields = [
                        'dept_approval', 'dept_approved_at', 'dept_approved_by', 'dept_notes',
                        'finance_approval', 'finance_approved_at', 'finance_approved_by', 'finance_notes',
                        'management_approval', 'management_approved_at', 'management_approved_by', 'management_notes',
                        'paid_approval', 'paid_approved_at', 'paid_approved_by', 'paid_notes'
                    ];
                    
                    foreach ($resetFields as $field) {
                        $paymentData[$field] = null;
                    }
                }

                $db->updateData('payment_requests', $paymentData, ['id' => $payment_id]);

            } else {
                $paymentData['created_at'] = date('Y-m-d H:i:s');
                $paymentData['created_by'] = $user['user_id'];

                $payment_id = $db->insertData('payment_requests', $paymentData);
            }

            $db->commit();

            $this->jsonResponse(true, ($action === 'update') ? 'Payment Updated Successfully' : 'Payment Created Successfully');

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Payment store error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->jsonResponse(false, 'An error occurred: ' . $e->getMessage());
        }
    }

    public function get_single()
    {
        $this->validateCSRF();

        $id = $_POST['id'] ?? null;

        if (empty($id) || !is_numeric($id)) {
            $this->jsonResponse(false, "Invalid Payment ID");
        }

        $db = new Database();

        $sql = "SELECT pr.*, 
                       d.department_name, 
                       c.short_name AS client_name, 
                       cu.currency_short_name, 
                       of.main_location_name, 
                       ex.expense_type_name,
                       u1.full_name as dept_approved_by_name,
                       u2.full_name as finance_approved_by_name,
                       u3.full_name as management_approved_by_name,
                       u4.full_name as paid_approved_by_name
                FROM payment_requests pr
                LEFT JOIN department_master_t d ON d.id = pr.department
                LEFT JOIN clients_t c ON c.id = pr.client_id
                LEFT JOIN currency_master_t cu ON cu.id = pr.currency
                LEFT JOIN main_office_master_t of ON of.id = pr.location_id
                LEFT JOIN expense_type_master_t ex ON ex.id = pr.expense_type
                LEFT JOIN users_t u1 ON u1.id = pr.dept_approved_by
                LEFT JOIN users_t u2 ON u2.id = pr.finance_approved_by
                LEFT JOIN users_t u3 ON u3.id = pr.management_approved_by
                LEFT JOIN users_t u4 ON u4.id = pr.paid_approved_by
                WHERE pr.id = :id
                LIMIT 1";

        $payment = $db->customQuery($sql, [':id' => $id]);

        if (!$payment || count($payment) == 0) {
            $this->jsonResponse(false, "Payment record not found");
        }

        $row = $payment[0];

        $row['file1_url'] = $this->getFileUrl($row['file1_path']);
        $row['file2_url'] = $this->getFileUrl($row['file2_path']);
        $row['file3_url'] = $this->getFileUrl($row['file3_path']);
        $row['file4_url'] = $this->getFileUrl($row['file4_path']);

        $mcaData = [];
        if (!empty($row["mca_data"])) {
            $decoded = json_decode($row["mca_data"], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $mcaData = $decoded;
            }
        }

        $this->jsonResponse(true, "Payment retrieved", [
            'data' => $row,
            'mca_refs' => $mcaData
        ]);
    }

    public function delete($id)
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        if ($user['role_id'] != self::ROLE_MANAGEMENT) {
            $this->jsonResponse(false, "You don't have permission to delete payment requests");
        }
        
        $db = new Database();

        $db->deleteData("payment_requests", ["id" => $id]);

        $this->jsonResponse(true, "Payment Request Deleted Successfully");
    }

    public function getExpenseTypesByCategory()
    { 
        $this->validateCSRF();

        $category = $_POST['pay_for'] ?? null;
        
        $columns = [
            '0' => 'import',
            '1' => 'export',
            '2' => 'local',
            '3' => 'other',
            '4' => 'advance'
        ];

        if (!isset($columns[$category])) {
            echo json_encode([]);
            exit;
        }

        $column = $columns[$category];

        $db = new Database();

        $expenseTypes = $db->selectData(
            "expense_type_master_t",
            "*",
            [$column => 1]
        );

        echo json_encode($expenseTypes);
        exit;
    }

    private function validateApprovalWorkflow($payment, $action)
    {
        switch ($action) {
            case "finance_approve":
                if ($payment['dept_approval'] != 1) {
                    $this->jsonResponse(false, "Department approval is required before Finance approval");
                }
                if ($payment['dept_approval'] == -1) {
                    $this->jsonResponse(false, "Cannot approve - Department has rejected this request");
                }
                break;
                
            case "management_approve":
                if ($payment['dept_approval'] != 1) {
                    $this->jsonResponse(false, "Department approval is required before Management approval");
                }
                if ($payment['finance_approval'] != 1) {
                    $this->jsonResponse(false, "Finance approval is required before Management approval");
                }
                if ($payment['dept_approval'] == -1 || $payment['finance_approval'] == -1) {
                    $this->jsonResponse(false, "Cannot approve - A prior stage has rejected this request");
                }
                break;
                
            case "paid_approve":
                if ($payment['dept_approval'] != 1) {
                    $this->jsonResponse(false, "Department approval is required before marking as Paid");
                }
                if ($payment['finance_approval'] != 1) {
                    $this->jsonResponse(false, "Finance approval is required before marking as Paid");
                }
                if ($payment['management_approval'] != 1) {
                    $this->jsonResponse(false, "Management approval is required before marking as Paid");
                }
                if ($payment['dept_approval'] == -1 || 
                    $payment['finance_approval'] == -1 || 
                    $payment['management_approval'] == -1) {
                    $this->jsonResponse(false, "Cannot mark as paid - A prior stage has rejected this request");
                }
                break;
        }
        
        $stageMap = [
            'dept_approve' => 'dept_approval',
            'finance_approve' => 'finance_approval',
            'management_approve' => 'management_approval',
            'paid_approve' => 'paid_approval'
        ];
        
        if (isset($stageMap[$action]) && $payment[$stageMap[$action]] == 1) {
            $this->jsonResponse(false, "This request has already been approved at this stage");
        }
    }

    private function handleApproval($approvalType)
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(false, "Invalid Request");
        }

        $paymentSql = "SELECT * FROM payment_requests WHERE id = :id";
        $paymentResult = $db->customQuery($paymentSql, [':id' => $id]);
        
        if (empty($paymentResult)) {
            $this->jsonResponse(false, "Payment request not found");
        }
        
        $payment = $paymentResult[0];
        
        $actionMap = [
            'dept' => 'dept_approve',
            'finance' => 'finance_approve',
            'management' => 'management_approve',
            'paid' => 'paid_approve'
        ];
        
        if (isset($actionMap[$approvalType])) {
            $this->validateApprovalWorkflow($payment, $actionMap[$approvalType]);
        }

        $updateData = [];
        $timestamp = date("Y-m-d H:i:s");

        switch ($approvalType) {
            case "dept":
                $chargeback_needed = $_POST['chargeback_needed'] ?? 0;
                $chargeback_value  = $_POST['chargeback_value'] ?? null;

                $updateData = [
                    "dept_approval" => 1,
                    "dept_approved_at" => $timestamp,
                    "dept_approved_by" => $user['user_id'],
                    "chargeback"  => ($chargeback_needed == 1 ? $chargeback_value : null)
                ];
                break;

            case "finance":
                $updateData = [
                    "finance_approval" => 1,
                    "finance_approved_at" => $timestamp,
                    "finance_approved_by" => $user['user_id']
                ];
                break;

            case "management":
                $updateData = [
                    "management_approval" => 1,
                    "management_approved_at" => $timestamp,
                    "management_approved_by" => $user['user_id']
                ];
                break;

            case "paid":
                $cash_collector = $_POST['cash_collector'] ?? null;
                
                if (empty($cash_collector)) {
                    $this->jsonResponse(false, "Cash collector is required");
                }
                
                $updateData = [
                    "paid_approval" => 1,
                    "paid_approved_at" => $timestamp,
                    "paid_approved_by" => $user['user_id'],
                    "cash_collector" => $cash_collector
                ];
                
                try {
                    foreach (['file3', 'file4'] as $fileKey) {
                        $fileInfo = $this->validateFileUpload($fileKey);
                        if ($fileInfo) {
                            $uploaded_path = $this->uploadFile($fileInfo);
                            if ($uploaded_path) {
                                $updateData[$fileKey . '_path'] = $uploaded_path;
                                error_log("Successfully uploaded {$fileKey} for payment: {$uploaded_path}");
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error uploading payment files: " . $e->getMessage());
                    $this->jsonResponse(false, $e->getMessage());
                }
                break;

            default:
                $this->jsonResponse(false, "Invalid Action");
        }

        $result = $db->updateData("payment_requests", $updateData, ["id" => $id]);

        if ($result) {
            $this->jsonResponse(true, ucfirst($approvalType) . " approval completed successfully");
        } else {
            $this->jsonResponse(false, "Database update failed");
        }
    }

    public function update_approval()
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $action = $_POST['action'] ?? null;
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $this->jsonResponse(false, "Invalid Request");
        }
        
        $db = new Database();
        
        // Get payment details
        $paymentSql = "SELECT * FROM payment_requests WHERE id = :id";
        $paymentResult = $db->customQuery($paymentSql, [':id' => $id]);
        
        if (empty($paymentResult)) {
            $this->jsonResponse(false, "Payment request not found");
        }
        
        $payment = $paymentResult[0];
        $userRole = $user['role_id'];
        
        // **Location-based permission checks**
        if ($userRole == self::ROLE_LOCATION_MANAGER && $payment['location_id'] != 3) {
            $this->jsonResponse(false, "You can only approve requests from Location 3");
        }
        
        if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT && $payment['location_id'] != 2) {
            $this->jsonResponse(false, "You can only approve requests from Location 2");
        }
        
        if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID && $payment['location_id'] != 2) {
            $this->jsonResponse(false, "You can only approve requests from Location 2");
        }
        
        // **NEW: Check if Location 1 user is trying to approve Location 1 requests**
        if ($userRole == self::ROLE_LOCATION_1_MANAGER && $payment['location_id'] != 1) {
            $this->jsonResponse(false, "You can only approve requests from Location 1");
        }
        
        // Define which actions each role can perform
        $rolePermissions = [
            self::ROLE_DEPARTMENT => ['dept_approve'],
            self::ROLE_FINANCE_CASH => ['finance_approve'],
            self::ROLE_FINANCE_BANK => ['finance_approve'],
            self::ROLE_MANAGEMENT => ['management_approve'],
            self::ROLE_CASHIER => ['paid_approve'],
            self::ROLE_BANK_OFFICER => ['paid_approve'],
            
            // Location 3 Manager - All approvals for Location 3
            self::ROLE_LOCATION_MANAGER => ['dept_approve', 'finance_approve', 'management_approve', 'paid_approve'],
            
            // Location 2 Roles
            self::ROLE_LOCATION_2_DEPT_MGMT => ['dept_approve', 'management_approve'],
            self::ROLE_LOCATION_2_FINANCE_PAID => ['finance_approve', 'paid_approve'],
            
            // NEW: Location 1 Role
            self::ROLE_LOCATION_1_MANAGER => ['management_approve']
        ];
        
        // Check if user has permission for this action
        if (!isset($rolePermissions[$userRole]) || !in_array($action, $rolePermissions[$userRole])) {
            $this->jsonResponse(false, "You don't have permission to perform this action");
        }
        
        $actionMap = [
            'dept_approve' => 'dept',
            'finance_approve' => 'finance',
            'management_approve' => 'management',
            'paid_approve' => 'paid'
        ];
        
        if (isset($actionMap[$action])) {
            $this->handleApproval($actionMap[$action]);
        } else {
            $this->jsonResponse(false, "Invalid action");
        }
    }

    public function reject_request() 
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();

        $id          = $_POST['id'] ?? null;
        $rejectType  = $_POST['reject_type'] ?? null;
        $reason      = $_POST['reason'] ?? null;

        if (!$id || !$rejectType || !$reason) {
            $this->jsonResponse(false, "Missing required data");
        }
        
        // Get payment details
        $paymentSql = "SELECT * FROM payment_requests WHERE id = :id";
        $paymentResult = $db->customQuery($paymentSql, [':id' => $id]);
        
        if (empty($paymentResult)) {
            $this->jsonResponse(false, "Payment request not found");
        }
        
        $payment = $paymentResult[0];
        $userRole = $user['role_id'];
        
        // **Location-based permission checks**
        if ($userRole == self::ROLE_LOCATION_MANAGER && $payment['location_id'] != 3) {
            $this->jsonResponse(false, "You can only reject requests from Location 3");
        }
        
        if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT && $payment['location_id'] != 2) {
            $this->jsonResponse(false, "You can only reject requests from Location 2");
        }
        
        if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID && $payment['location_id'] != 2) {
            $this->jsonResponse(false, "You can only reject requests from Location 2");
        }
        
        // **NEW: Location 1 permission check**
        if ($userRole == self::ROLE_LOCATION_1_MANAGER && $payment['location_id'] != 1) {
            $this->jsonResponse(false, "You can only reject requests from Location 1");
        }
        
        // Define which rejection types each role can perform
        $rolePermissions = [
            self::ROLE_DEPARTMENT => ['dept'],
            self::ROLE_FINANCE_CASH => ['finance'],
            self::ROLE_FINANCE_BANK => ['finance'],
            self::ROLE_MANAGEMENT => ['management'],
            self::ROLE_CASHIER => ['paid'],
            self::ROLE_BANK_OFFICER => ['paid'],
            
            // Location 3 Manager - All rejections for Location 3
            self::ROLE_LOCATION_MANAGER => ['dept', 'finance', 'management', 'paid'],
            
            // Location 2 Roles
            self::ROLE_LOCATION_2_DEPT_MGMT => ['dept', 'management'],
            self::ROLE_LOCATION_2_FINANCE_PAID => ['finance', 'paid'],
            
            // NEW: Location 1 Role
            self::ROLE_LOCATION_1_MANAGER => ['management']
        ];
        
        // Check if user has permission for this rejection type
        if (!isset($rolePermissions[$userRole]) || !in_array($rejectType, $rolePermissions[$userRole])) {
            $this->jsonResponse(false, "You don't have permission to reject at this stage");
        }

        $timestamp = date("Y-m-d H:i:s");
        
        $rejectMap = [
            'dept' => [
                'approval' => 'dept_approval',
                'approved_at' => 'dept_approved_at',
                'approved_by' => 'dept_approved_by',
                'notes' => 'dept_notes'
            ],
            'finance' => [
                'approval' => 'finance_approval',
                'approved_at' => 'finance_approved_at',
                'approved_by' => 'finance_approved_by',
                'notes' => 'finance_notes'
            ],
            'management' => [
                'approval' => 'management_approval',
                'approved_at' => 'management_approved_at',
                'approved_by' => 'management_approved_by',
                'notes' => 'management_notes'
            ],
            'paid' => [
                'approval' => 'paid_approval',
                'approved_at' => 'paid_approved_at',
                'approved_by' => 'paid_approved_by',
                'notes' => 'paid_notes'
            ]
        ];

        if (!isset($rejectMap[$rejectType])) {
            $this->jsonResponse(false, "Invalid rejection type");
        }

        $fields = $rejectMap[$rejectType];
        $updateData = [
            $fields['approval'] => -1,
            $fields['approved_at'] => $timestamp,
            $fields['approved_by'] => $user['user_id'],
            $fields['notes'] => $reason,
        ];

        $updated = $db->updateData("payment_requests", $updateData, ["id" => $id]);

        if ($updated) {
            $this->jsonResponse(true, "Request rejected successfully");
        } else {
            $this->jsonResponse(false, "Failed to update");
        }
    }

    public function get_mca_refs_by_client()
    {
        $this->validateCSRF();

        $client_id   = $_POST['client_id'] ?? null;
        $paymentfor  = isset($_POST['paymentfor']) ? (int)$_POST['paymentfor'] : null;
        $expenseType = isset($_POST['expenseType']) ? (int)$_POST['expenseType'] : null;

        if (!$client_id || !isset($_POST['paymentfor'])) {
            $this->jsonResponse(false, 'Invalid request', []);
        }

        $db = new Database();

        if ($expenseType == 28) {
            $mapping = [
                0 => ['table' => 'imports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
                1 => ['table' => 'exports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
                2 => ['table' => 'locals_t',  'client_col' => 'clients_id',    'ref_col' => 'lt_reference']
            ];

            if (!isset($mapping[$paymentfor])) {
                $this->jsonResponse(false, 'Invalid payment_for value', []);
            }

            $t = $mapping[$paymentfor];

            $sql = "
                SELECT 
                    t.id AS payment_id,
                    t.{$t['ref_col']} AS mca_ref,
                    t.type_of_goods,
                    t.transport_mode,
                    p.perdiem_amount
                FROM {$t['table']} t
                LEFT JOIN perdiem_master_t p
                    ON p.client_id = t.{$t['client_col']}
                    AND p.goods_type_id = t.type_of_goods
                    AND p.transport_mode_id = t.transport_mode
                WHERE t.{$t['client_col']} = :client_id
            ";

            $rows = $db->customQuery($sql, [':client_id' => $client_id]);

            $final = [];
            foreach ($rows as $r) {
                if (!$r['mca_ref']) continue;

                $final[] = [
                    'payment_id'     => $r['payment_id'],
                    'mca_ref'        => $r['mca_ref'],
                    'type_of_goods'  => $r['type_of_goods'],
                    'transport_mode' => $r['transport_mode'],
                    'perdiem_amount' => $r['perdiem_amount'],
                    'transport_name' => $t['table']
                ];
            }

            $this->jsonResponse(true, 'Records retrieved', $final);
        }

        $mapping = [
            0 => ['table' => 'imports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
            1 => ['table' => 'exports_t', 'client_col' => 'subscriber_id', 'ref_col' => 'mca_ref'],
            2 => ['table' => 'locals_t',  'client_col' => 'clients_id',    'ref_col' => 'lt_reference']
        ];

        if (!isset($mapping[$paymentfor])) {
            $this->jsonResponse(false, 'Invalid payment_for value', []);
        }

        $table     = $mapping[$paymentfor]['table'];
        $clientCol = $mapping[$paymentfor]['client_col'];
        $refCol    = $mapping[$paymentfor]['ref_col'];

        $sql = "SELECT type_of_goods, transport_mode, id, $refCol AS ref 
                FROM $table WHERE $clientCol = :client_id";

        $rows = $db->customQuery($sql, [':client_id' => $client_id]);

        $finalList = [];

        foreach ($rows as $row) {
            if (!$row['ref']) continue;

            $decoded = json_decode($row['ref'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $finalList[] = [
                    'payment_id'     => $row['id'],
                    'mca_ref'        => $row['ref'],
                    'type_of_goods'  => $row['type_of_goods'],
                    'transport_mode' => $row['transport_mode']
                ];
                continue;
            }

            foreach ($decoded as $item) {
                $finalList[] = [
                    'payment_id'     => $row['id'],
                    'mca_ref'        => $item['mca_ref'],
                    'type_of_goods'  => $row['type_of_goods'],
                    'transport_mode' => $row['transport_mode']
                ];
            }
        }

        $this->jsonResponse(true, 'Records retrieved', $finalList);
    }

    public function export_to_excel()
    {
        $this->validateCSRF();
        $user = $this->validateSession();
        
        $db = new Database();
        $userId = $user['user_id'];
        $userRole = $user['role_id'];
        
        $whereConditions = [];
        $params = [];
        
        if ($userRole == self::ROLE_FINANCE_BANK) {
            $whereConditions[] = "pr.payment_type = :payment_type_bank";
            $params[':payment_type_bank'] = 'Bank';
        }
        // Location 3 Manager
        else if ($userRole == self::ROLE_LOCATION_MANAGER) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 3;
        }
        // NEW: Location 2 Roles
        else if ($userRole == self::ROLE_LOCATION_2_DEPT_MGMT) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 2;
        }
        else if ($userRole == self::ROLE_LOCATION_2_FINANCE_PAID) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 2;
        }
        // NEW: Location 1 Manager
        else if ($userRole == self::ROLE_LOCATION_1_MANAGER) {
            $whereConditions[] = "pr.location_id = :location_id";
            $params[':location_id'] = 1;
        }
        else if (!in_array($userRole, [self::ROLE_MANAGEMENT, self::ROLE_DEPARTMENT, self::ROLE_FINANCE_CASH])) {
            $visibilitySQL = "";
            
            switch ($userRole) {
                case self::ROLE_CASHIER:
                    // Cashier: their own requests OR all Cash (excluding locations 2 & 3)
                    $visibilitySQL = "(
                        pr.created_by = :uid1 
                        OR (pr.payment_type = 'Cash' AND pr.location_id NOT IN (2, 3))
                        OR pr.paid_approved_by = :uid2
                    )";
                    $params[':uid1'] = $userId;
                    $params[':uid2'] = $userId;
                    break;
                    
                case self::ROLE_BANK_OFFICER:
                    // Bank Officer: their own requests OR all Bank payments (ALL locations)
                    $visibilitySQL = "(
                        pr.created_by = :uid1 
                        OR pr.payment_type = 'Bank'
                        OR pr.paid_approved_by = :uid2
                    )";
                    $params[':uid1'] = $userId;
                    $params[':uid2'] = $userId;
                    break;
                    
                default:
                    // Other roles: only their own requests
                    $visibilitySQL = "pr.created_by = :uid1";
                    $params[':uid1'] = $userId;
                    break;
            }
            
            if ($visibilitySQL) {
                $whereConditions[] = $visibilitySQL;
            }
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql = "SELECT pr.*, d.department_name, c.short_name AS client_name,
                       cu.currency_short_name, of.main_location_name, ex.expense_type_name
                FROM payment_requests pr
                LEFT JOIN department_master_t d ON d.id = pr.department
                LEFT JOIN clients_t c ON c.id = pr.client_id
                LEFT JOIN currency_master_t cu ON cu.id = pr.currency
                LEFT JOIN main_office_master_t of ON of.id = pr.location_id
                LEFT JOIN expense_type_master_t ex ON ex.id = pr.expense_type"
                . $whereClause . "
                ORDER BY pr.id DESC";
        
        try {
            $data = $db->customQuery($sql, $params);
            $this->jsonResponse(true, 'Data retrieved', $data);
        } catch (Exception $e) {
            error_log("Excel export error: " . $e->getMessage());
            $this->jsonResponse(false, 'Error exporting data: ' . $e->getMessage());
        }
    }
}