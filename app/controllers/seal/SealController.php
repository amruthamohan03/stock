<?php
class SealController extends Controller
{
    /*
     * INDEX PAGE
     */
    public function index()
    {
        $db = new Database();

        // Office Location cards (exclude ID 3)
        $sql = "
            SELECT 
                id,
                main_location_name
            FROM main_office_master_t
            WHERE display = 'Y' AND id != 3
            ORDER BY main_location_name ASC
        ";
        $officeLocations = $db->customQuery($sql);

        // Generate CSRF Token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'Seal Master',
            'officeLocations' => $officeLocations,
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('seal/seal', $data);
    }

    /*
     * RELEASE/FREE SEAL - Change status back to Available
     */
    public function releaseSeal($seal_number)
    {
        $db = new Database();
        
        try {
            $seal = $db->selectData('seal_individual_numbers_t', '*', ['seal_number' => $seal_number]);
            
            if (empty($seal)) {
                return ['success' => false, 'message' => 'Seal number not found: ' . $seal_number];
            }
            
            $sealId = $seal[0]['id'];
            $currentStatus = $seal[0]['status'];
            
            if ($currentStatus !== 'Used') {
                return ['success' => false, 'message' => 'Seal is not in Used status', 'seal_number' => $seal_number];
            }
            
            $updateData = [
                'status' => 'Available',
                'notes' => 'Released from import assignment on ' . date('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['user_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = $db->updateData('seal_individual_numbers_t', $updateData, ['id' => $sealId]);
            
            if ($updated) {
                return ['success' => true, 'message' => 'Seal released successfully', 'seal_number' => $seal_number];
            } else {
                return ['success' => false, 'message' => 'Failed to release seal', 'seal_number' => $seal_number];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'seal_number' => $seal_number];
        }
    }

    /*
     * RELEASE MULTIPLE SEALS - For bulk operations
     */
    public function releaseMultipleSeals($seal_numbers_array)
    {
        $db = new Database();
        $released = 0;
        $failed = [];
        
        if (!is_array($seal_numbers_array)) {
            return ['success' => false, 'message' => 'Invalid seal numbers array'];
        }
        
        foreach ($seal_numbers_array as $seal_number) {
            $seal_number = trim($seal_number);
            if (empty($seal_number)) continue;
            
            $result = $this->releaseSeal($seal_number);
            
            if ($result['success']) {
                $released++;
            } else {
                $failed[] = $seal_number;
            }
        }
        
        return [
            'success' => true,
            'released' => $released,
            'failed' => $failed,
            'total' => count($seal_numbers_array),
            'message' => "$released seal(s) released successfully" . (count($failed) > 0 ? ", " . count($failed) . " failed" : "")
        ];
    }

    /*
     * MARK SEAL AS USED - When assigning to import
     */
    public function markSealAsUsed($seal_number, $reference_info = '')
    {
        $db = new Database();
        
        try {
            $seal = $db->selectData('seal_individual_numbers_t', '*', ['seal_number' => $seal_number]);
            
            if (empty($seal)) {
                return ['success' => false, 'message' => 'Seal number not found: ' . $seal_number];
            }
            
            $sealId = $seal[0]['id'];
            $currentStatus = $seal[0]['status'];
            
            if ($currentStatus === 'Used') {
                return ['success' => false, 'message' => 'Seal is already in use', 'seal_number' => $seal_number];
            }
            
            if ($currentStatus === 'Damaged') {
                return ['success' => false, 'message' => 'Seal is damaged and cannot be used', 'seal_number' => $seal_number];
            }
            
            $notes = 'Assigned to import';
            if (!empty($reference_info)) {
                $notes .= ': ' . $reference_info;
            }
            
            $updateData = [
                'status' => 'Used',
                'notes' => $notes,
                'updated_by' => $_SESSION['user_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $updated = $db->updateData('seal_individual_numbers_t', $updateData, ['id' => $sealId]);
            
            if ($updated) {
                return ['success' => true, 'message' => 'Seal marked as used', 'seal_number' => $seal_number];
            } else {
                return ['success' => false, 'message' => 'Failed to mark seal as used', 'seal_number' => $seal_number];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'seal_number' => $seal_number];
        }
    }

    /*
     * MARK MULTIPLE SEALS AS USED - For bulk operations
     */
    public function markMultipleSealsAsUsed($seal_numbers_array, $reference_info = '')
    {
        $db = new Database();
        $marked = 0;
        $failed = [];
        
        if (!is_array($seal_numbers_array)) {
            return ['success' => false, 'message' => 'Invalid seal numbers array'];
        }
        
        foreach ($seal_numbers_array as $seal_number) {
            $seal_number = trim($seal_number);
            if (empty($seal_number)) continue;
            
            $result = $this->markSealAsUsed($seal_number, $reference_info);
            
            if ($result['success']) {
                $marked++;
            } else {
                $failed[] = $seal_number;
            }
        }
        
        return [
            'success' => true,
            'marked' => $marked,
            'failed' => $failed,
            'total' => count($seal_numbers_array),
            'message' => "$marked seal(s) marked as used" . (count($failed) > 0 ? ", " . count($failed) . " failed" : "")
        ];
    }

    /*
     * CHECK SEAL AVAILABILITY
     */
    public function checkSealAvailability($seal_number)
    {
        $db = new Database();
        
        $seal = $db->selectData('seal_individual_numbers_t', '*', ['seal_number' => $seal_number]);
        
        if (empty($seal)) {
            return [
                'success' => false, 
                'available' => false, 
                'message' => 'Seal number not found',
                'seal_number' => $seal_number
            ];
        }
        
        $status = $seal[0]['status'];
        $available = ($status === 'Available');
        
        return [
            'success' => true,
            'available' => $available,
            'status' => $status,
            'seal_number' => $seal_number,
            'message' => $available ? 'Seal is available' : "Seal is $status"
        ];
    }

    /*
     * GET AVAILABLE SEALS BY LOCATION
     */
    public function getAvailableSeals($office_location_id = null, $limit = 100)
    {
        $db = new Database();
        
        $query = "
            SELECT 
                sin.id,
                sin.seal_number,
                sin.status,
                sn.office_location_id,
                mo.main_location_name
            FROM seal_individual_numbers_t sin
            INNER JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
            LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
            WHERE sin.status = 'Available' AND sin.display = 'Y'
        ";
        
        $params = [];
        
        if ($office_location_id !== null && $office_location_id > 0) {
            $query .= " AND sn.office_location_id = ?";
            $params[] = $office_location_id;
        }
        
        $query .= " ORDER BY sin.id ASC LIMIT ?";
        $params[] = (int)$limit;
        
        $seals = $db->customQuery($query, $params);
        
        return [
            'success' => true,
            'count' => count($seals),
            'seals' => $seals
        ];
    }

    /*
     * CRUD - INSERT, UPDATE, DELETE, LISTING
     */
    public function crudData($action = 'insertion')
    {
        $db = new Database();
        $table = 'seal_nos_t';

        function s($v) {
            return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
        }

        /*
         * RELEASE SEAL API ENDPOINT
         */
        if ($action === 'releaseSeal' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $seal_number = s($_POST['seal_number'] ?? '');

            if (empty($seal_number)) {
                echo json_encode(['success' => false, 'message' => 'Seal number is required']);
                exit;
            }

            $result = $this->releaseSeal($seal_number);
            echo json_encode($result);
            exit;
        }

        /*
         * RELEASE MULTIPLE SEALS API ENDPOINT
         */
        if ($action === 'releaseMultipleSeals' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $seal_numbers = $_POST['seal_numbers'] ?? '';

            if (empty($seal_numbers)) {
                echo json_encode(['success' => false, 'message' => 'Seal numbers are required']);
                exit;
            }

            $seal_numbers_array = preg_split('/[\r\n,]+/', $seal_numbers);
            $seal_numbers_array = array_filter(array_map('trim', $seal_numbers_array));

            $result = $this->releaseMultipleSeals($seal_numbers_array);
            echo json_encode($result);
            exit;
        }

        /*
         * MARK SEAL AS USED API ENDPOINT
         */
        if ($action === 'markSealAsUsed' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $seal_number = s($_POST['seal_number'] ?? '');
            $reference_info = s($_POST['reference_info'] ?? '');

            if (empty($seal_number)) {
                echo json_encode(['success' => false, 'message' => 'Seal number is required']);
                exit;
            }

            $result = $this->markSealAsUsed($seal_number, $reference_info);
            echo json_encode($result);
            exit;
        }

        /*
         * CHECK SEAL AVAILABILITY API ENDPOINT
         */
        if ($action === 'checkSealAvailability' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $seal_number = s($_GET['seal_number'] ?? '');

            if (empty($seal_number)) {
                echo json_encode(['success' => false, 'message' => 'Seal number is required']);
                exit;
            }

            $result = $this->checkSealAvailability($seal_number);
            echo json_encode($result);
            exit;
        }

        /*
         * GET AVAILABLE SEALS API ENDPOINT
         */
        if ($action === 'getAvailableSeals' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $office_location_id = isset($_GET['office_location_id']) ? (int)$_GET['office_location_id'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

            $result = $this->getAvailableSeals($office_location_id, $limit);
            echo json_encode($result);
            exit;
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
                
                $orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';

                $columns = ['sn.id', 'mo.main_location_name', 'sn.purchase_date', 'sn.total_amount', 'sn.total_seal', 'sn.display'];
                $orderColumn = $columns[$orderColumnIndex] ?? 'sn.id';

                $baseQuery = "
                    FROM seal_nos_t sn
                    LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                ";

                $whereClause = " WHERE 1=1 ";
                $params = [];

                if (!empty($searchValue)) {
                    $whereClause .= " AND (
                        mo.main_location_name LIKE ? OR
                        sn.purchase_date LIKE ? OR
                        CAST(sn.total_amount AS CHAR) LIKE ? OR
                        CAST(sn.total_seal AS CHAR) LIKE ?
                    )";
                    $searchParam = "%{$searchValue}%";
                    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
                }

                if (!empty($_GET['location_filter']) && $_GET['location_filter'] != '0') {
                    $locationFilter = intval($_GET['location_filter']);
                    $whereClause .= " AND sn.office_location_id = ? ";
                    $params[] = $locationFilter;
                }

                if (!empty($_GET['status_filter'])) {
                    $statusFilter = $db->escapeString($_GET['status_filter']);
                    $whereClause .= " AND sn.id IN (
                        SELECT DISTINCT seal_master_id 
                        FROM seal_individual_numbers_t 
                        WHERE status = ?
                    )";
                    $params[] = $statusFilter;
                }

                $totalRecordsQuery = "SELECT COUNT(*) as total " . $baseQuery . $whereClause;
                $totalRecordsResult = $db->customQuery($totalRecordsQuery, $params);
                $totalRecords = $totalRecordsResult[0]['total'] ?? 0;

                $dataQuery = "
                    SELECT 
                        sn.id,
                        sn.office_location_id,
                        sn.purchase_date,
                        sn.total_amount,
                        sn.total_seal,
                        sn.display,
                        sn.created_at,
                        sn.updated_at,
                        mo.main_location_name,
                        (SELECT COUNT(*) FROM seal_individual_numbers_t WHERE seal_master_id = sn.id) as added_seals
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
                        'office_location_id' => $row['office_location_id'],
                        'location_name' => $row['main_location_name'] ?? 'N/A',
                        'purchase_date' => $row['purchase_date'] ?? '',
                        'total_amount' => $row['total_amount'] ?? 0,
                        'total_seal' => $row['total_seal'] ?? 0,
                        'added_seals' => $row['added_seals'] ?? 0,
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
                $totalQuery = "SELECT COALESCE(SUM(total_seal), 0) as total FROM seal_nos_t WHERE display = 'Y'";
                $totalSeals = $db->customQuery($totalQuery)[0]['total'] ?? 0;

                $addedQuery = "
                    SELECT COUNT(*) as total 
                    FROM seal_individual_numbers_t sin
                    INNER JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                    WHERE sn.display = 'Y'
                ";
                $addedSeals = $db->customQuery($addedQuery)[0]['total'] ?? 0;

                $usedQuery = "
                    SELECT COUNT(*) as total 
                    FROM seal_individual_numbers_t sin
                    INNER JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                    WHERE sn.display = 'Y' AND sin.status = 'Used'
                ";
                $usedSeals = $db->customQuery($usedQuery)[0]['total'] ?? 0;

                $damagedQuery = "
                    SELECT COUNT(*) as total 
                    FROM seal_individual_numbers_t sin
                    INNER JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                    WHERE sn.display = 'Y' AND sin.status = 'Damaged'
                ";
                $damagedSeals = $db->customQuery($damagedQuery)[0]['total'] ?? 0;

                $locationQuery = "
                    SELECT 
                        mo.id,
                        mo.main_location_name,
                        COALESCE(SUM(sn.total_seal), 0) as seal_count,
                        (SELECT COUNT(*) 
                         FROM seal_individual_numbers_t sin2 
                         INNER JOIN seal_nos_t sn2 ON sin2.seal_master_id = sn2.id 
                         WHERE sn2.office_location_id = mo.id AND sn2.display = 'Y'
                        ) as added_count
                    FROM main_office_master_t mo
                    LEFT JOIN seal_nos_t sn ON mo.id = sn.office_location_id AND sn.display = 'Y'
                    WHERE mo.display = 'Y' AND mo.id != 3
                    GROUP BY mo.id, mo.main_location_name
                    ORDER BY mo.main_location_name ASC
                ";
                $locationCounts = $db->customQuery($locationQuery);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total_seals' => $totalSeals,
                        'added_seals' => $addedSeals,
                        'used_seals' => $usedSeals,
                        'damaged_seals' => $damagedSeals,
                        'location_counts' => $locationCounts
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
         * GET SINGLE SEAL
         */
        if ($action === 'getSeal' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $query = "
                SELECT 
                    sn.*,
                    mo.main_location_name
                FROM seal_nos_t sn
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sn.id = ?
            ";
            $result = $db->customQuery($query, [$id]);

            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Seal not found']);
            }
            exit;
        }

        /*
         * GET SEAL NUMBERS FOR A MASTER
         */
        if ($action === 'getSealNumbers' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $seal_master_id = intval($_GET['seal_master_id'] ?? 0);
            if ($seal_master_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Seal Master ID']);
                exit;
            }

            $query = "
                SELECT 
                    sin.id,
                    sin.seal_number,
                    sin.status,
                    sin.notes,
                    sin.display,
                    sin.created_at,
                    mo.main_location_name as location
                FROM seal_individual_numbers_t sin
                LEFT JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sin.seal_master_id = ?
                ORDER BY sin.id DESC
            ";
            $sealNumbers = $db->customQuery($query, [$seal_master_id]);

            echo json_encode([
                'success' => true,
                'data' => $sealNumbers
            ]);
            exit;
        }

        /*
         * GET SINGLE SEAL NUMBER
         */
        if ($action === 'getSingleSealNumber' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Content-Type: application/json');

            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $query = "
                SELECT 
                    sin.*,
                    mo.main_location_name as location
                FROM seal_individual_numbers_t sin
                LEFT JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sin.id = ?
            ";
            $result = $db->customQuery($query, [$id]);

            if (!empty($result)) {
                echo json_encode(['success' => true, 'data' => $result[0]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Seal number not found']);
            }
            exit;
        }

        /*
         * ADD SEAL NUMBERS
         */
        if ($action === 'addSealNumbers' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $seal_master_id = (int)($_POST['seal_master_id'] ?? 0);
            $seal_numbers = $_POST['seal_numbers'] ?? '';

            if ($seal_master_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Seal Master ID']);
                exit;
            }

            if (empty($seal_numbers)) {
                echo json_encode(['success' => false, 'message' => 'Please enter seal numbers']);
                exit;
            }

            $sealNumbersArray = preg_split('/[\r\n,]+/', $seal_numbers);
            $sealNumbersArray = array_filter(array_map('trim', $sealNumbersArray));

            if (empty($sealNumbersArray)) {
                echo json_encode(['success' => false, 'message' => 'No valid seal numbers found']);
                exit;
            }

            $masterInfo = $db->selectData('seal_nos_t', 'total_seal', ['id' => $seal_master_id]);
            if (empty($masterInfo)) {
                echo json_encode(['success' => false, 'message' => 'Seal Master not found']);
                exit;
            }

            $totalAllowed = (int)$masterInfo[0]['total_seal'];
            
            $currentCount = $db->customQuery(
                "SELECT COUNT(*) as cnt FROM seal_individual_numbers_t WHERE seal_master_id = ?",
                [$seal_master_id]
            )[0]['cnt'];

            $newCount = $currentCount + count($sealNumbersArray);
            if ($newCount > $totalAllowed) {
                echo json_encode([
                    'success' => false,
                    'message' => "Cannot add " . count($sealNumbersArray) . " seal number(s). Limit: $totalAllowed, Current: $currentCount, Available: " . ($totalAllowed - $currentCount)
                ]);
                exit;
            }

            $duplicates = [];
            foreach ($sealNumbersArray as $sealNum) {
                $check = $db->selectData('seal_individual_numbers_t', 'id', ['seal_number' => $sealNum]);
                if (!empty($check)) {
                    $duplicates[] = $sealNum;
                }
            }

            if (!empty($duplicates)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Duplicate seal numbers found: ' . implode(', ', $duplicates)
                ]);
                exit;
            }

            $inserted = 0;
            foreach ($sealNumbersArray as $sealNum) {
                $data = [
                    'seal_master_id' => $seal_master_id,
                    'seal_number' => $sealNum,
                    'status' => 'Available',
                    'display' => 'Y',
                    'created_by' => $_SESSION['user_id'] ?? 1,
                    'updated_by' => $_SESSION['user_id'] ?? 1,
                ];

                if ($db->insertData('seal_individual_numbers_t', $data)) {
                    $inserted++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "$inserted seal number(s) added successfully!"
            ]);
            exit;
        }

        /*
         * UPDATE SEAL NUMBER - WITH STATUS VALIDATION
         */
        if ($action === 'updateSealNumber' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['seal_number_id'] ?? 0);
            $seal_number = s($_POST['seal_number'] ?? '');
            $status = s($_POST['status'] ?? 'Available');
            $notes = s($_POST['notes'] ?? '');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            if (empty($seal_number)) {
                echo json_encode(['success' => false, 'message' => 'Seal number is required']);
                exit;
            }

            $currentData = $db->selectData('seal_individual_numbers_t', '*', ['id' => $id]);
            if (empty($currentData)) {
                echo json_encode(['success' => false, 'message' => 'Seal number not found']);
                exit;
            }

            $currentStatus = $currentData[0]['status'];

            if ($currentStatus === 'Used' && $status === 'Damaged') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot change status from "Used" to "Damaged". Once a seal is marked as Used, it cannot be changed to Damaged.'
                ]);
                exit;
            }

            $checkDuplicate = $db->customQuery(
                "SELECT id FROM seal_individual_numbers_t WHERE seal_number = ? AND id != ?",
                [$seal_number, $id]
            );

            if (!empty($checkDuplicate)) {
                echo json_encode(['success' => false, 'message' => 'This seal number already exists']);
                exit;
            }

            $data = [
                'seal_number' => $seal_number,
                'status' => $status,
                'notes' => $notes,
                'updated_by' => $_SESSION['user_id'] ?? 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $update = $db->updateData('seal_individual_numbers_t', $data, ['id' => $id]);

            echo json_encode([
                'success' => $update ? true : false,
                'message' => $update ? 'Seal number updated successfully!' : 'Update failed.'
            ]);
            exit;
        }

        /*
         * DELETE SEAL NUMBER
         */
        if ($action === 'deleteSealNumber' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $delete = $db->deleteData('seal_individual_numbers_t', ['id' => $id]);

            echo json_encode([
                'success' => $delete ? true : false,
                'message' => $delete ? 'Seal number deleted successfully!' : 'Delete failed.'
            ]);
            exit;
        }

        /*
         * INSERT
         */
        if ($action === 'insert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $office_location_id = (int)($_POST['office_location_id'] ?? 0);
            $purchase_date = s($_POST['purchase_date'] ?? '');
            $total_amount = floatval($_POST['total_amount'] ?? 0);
            $total_seal = intval($_POST['total_seal'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            if ($office_location_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Office Location']);
                exit;
            }

            if ($purchase_date === '') {
                echo json_encode(['success' => false, 'message' => 'Please select Purchase Date']);
                exit;
            }

            if ($total_amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Total Amount must be greater than 0']);
                exit;
            }

            $data = [
                'office_location_id' => $office_location_id,
                'purchase_date'      => $purchase_date,
                'total_amount'       => $total_amount,
                'total_seal'         => $total_seal,
                'display'            => $display,
                'created_by'         => $_SESSION['user_id'] ?? 1,
                'updated_by'         => $_SESSION['user_id'] ?? 1,
            ];

            $insertId = $db->insertData($table, $data);

            echo json_encode($insertId
                ? ['success' => true, 'message' => 'Seal added successfully!', 'id' => $insertId]
                : ['success' => false, 'message' => 'Insert failed.']
            );
            exit;
        }

        /*
         * UPDATE
         */
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid security token']);
                exit;
            }

            $id = (int)($_POST['seal_id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $office_location_id = (int)($_POST['office_location_id'] ?? 0);
            $purchase_date = s($_POST['purchase_date'] ?? '');
            $total_amount = floatval($_POST['total_amount'] ?? 0);
            $total_seal = intval($_POST['total_seal'] ?? 0);
            $display = ($_POST['display'] ?? 'Y') === 'N' ? 'N' : 'Y';

            if ($office_location_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select Office Location']);
                exit;
            }

            if ($purchase_date === '') {
                echo json_encode(['success' => false, 'message' => 'Please select Purchase Date']);
                exit;
            }

            if ($total_amount <= 0) {
                echo json_encode(['success' => false, 'message' => 'Total Amount must be greater than 0']);
                exit;
            }

            $oldRow = $db->selectData('seal_nos_t', 'office_location_id', ['id' => $id]);
            if (empty($oldRow)) {
                echo json_encode(['success' => false, 'message' => 'Seal not found']);
                exit;
            }

            $data = [
                'office_location_id' => $office_location_id,
                'purchase_date'      => $purchase_date,
                'total_amount'       => $total_amount,
                'total_seal'         => $total_seal,
                'display'            => $display,
                'updated_by'         => $_SESSION['user_id'] ?? 1,
                'updated_at'         => date('Y-m-d H:i:s'),
            ];

            $update = $db->updateData($table, $data, ['id' => $id]);

            echo json_encode([
                'success' => $update ? true : false,
                'message' => $update ? 'Seal updated successfully!' : 'Update failed.'
            ]);
            exit;
        }

        /*
         * EXPORT SINGLE SEAL TO EXCEL - SIMPLIFIED
         */
        if ($action === 'exportSeal' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                die('Invalid ID');
            }

            $query = "
                SELECT 
                    sn.*,
                    mo.main_location_name
                FROM seal_nos_t sn
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sn.id = ?
            ";
            $sealData = $db->customQuery($query, [$id]);

            if (empty($sealData)) {
                die('Seal not found');
            }

            $seal = $sealData[0];

            $sealNumbersQuery = "
                SELECT 
                    sin.seal_number, 
                    sin.status, 
                    sin.notes,
                    sin.created_at
                FROM seal_individual_numbers_t sin
                WHERE sin.seal_master_id = ? 
                ORDER BY sin.id
            ";
            $sealNumbers = $db->customQuery($sealNumbersQuery, [$id]);

            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Seal Details');

            // Header
            $sheet->setCellValue('A1', 'SEAL MASTER DETAILS');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A1:D1');

            $row = 3;
            
            $fields = [
                'ID' => $seal['id'],
                'Purchase Location' => $seal['main_location_name'] ?? 'N/A',
                'Purchase Date' => date('d-m-Y', strtotime($seal['purchase_date'])),
                'Total Amount' => '$' . number_format($seal['total_amount'], 2),
                'Total Seal' => $seal['total_seal'],
                'Added Seals' => count($sealNumbers),
                'Per Seal Amount' => '$10.00',
                'Display' => $seal['display'] == 'Y' ? 'Yes' : 'No',
                'Updated At' => date('d-m-Y H:i', strtotime($seal['updated_at']))
            ];

            foreach ($fields as $label => $value) {
                $sheet->setCellValue('A' . $row, $label);
                $sheet->setCellValue('B' . $row, $value);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                $row++;
            }

            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            if (!empty($sealNumbers)) {
                $sheet2 = $spreadsheet->createSheet();
                $sheet2->setTitle('Seal Numbers');
                
                $sheet2->setCellValue('A1', 'ALL SEAL NUMBERS - ' . ($seal['main_location_name'] ?? 'N/A'));
                $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet2->mergeCells('A1:D1');
                
                $row2 = 3;
                $headers2 = ['#', 'Seal Number', 'Status', 'Notes'];
                $col2 = 'A';
                foreach ($headers2 as $header) {
                    $sheet2->setCellValue($col2 . $row2, $header);
                    $sheet2->getStyle($col2 . $row2)->getFont()->setBold(true);
                    $col2++;
                }
                
                $row2++;
                $counter = 1;
                foreach ($sealNumbers as $sn) {
                    $sheet2->setCellValue('A' . $row2, $counter);
                    $sheet2->setCellValue('B' . $row2, $sn['seal_number']);
                    $sheet2->setCellValue('C' . $row2, $sn['status']);
                    $sheet2->setCellValue('D' . $row2, $sn['notes'] ?? '-');
                    $row2++;
                    $counter++;
                }
                
                foreach (range('A', 'D') as $col) {
                    $sheet2->getColumnDimension($col)->setAutoSize(true);
                }
            }

            $spreadsheet->setActiveSheetIndex(0);

            $filename = 'Seal_' . $seal['id'] . '_' . date('Y-m-d') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        /*
         * EXPORT ALL SEALS TO EXCEL - 2026 ONWARDS WITH MONTHLY REPORT BY LOCATION
         */
        if ($action === 'exportAll' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get all locations (EXCLUDE LIKASI AND KINSHASA)
            $locationsQuery = "
                SELECT 
                    id,
                    main_location_name
                FROM main_office_master_t
                WHERE display = 'Y' 
                AND id != 3 
                AND LOWER(main_location_name) NOT LIKE '%likasi%'
                AND LOWER(main_location_name) NOT LIKE '%kinshasa%'
                ORDER BY main_location_name ASC
            ";
            $locations = $db->customQuery($locationsQuery);
            
            // Get location IDs to filter
            $locationIds = array_column($locations, 'id');
            
            if (empty($locationIds)) {
                die('No valid locations found');
            }
            
            $locationIdsString = implode(',', $locationIds);
            
            // Get all seal masters - 2026 ONWARDS, EXCLUDE LIKASI AND KINSHASA
            $query = "
                SELECT 
                    sn.*,
                    mo.main_location_name,
                    (SELECT COUNT(*) FROM seal_individual_numbers_t WHERE seal_master_id = sn.id) as added_seals,
                    (SELECT COUNT(*) FROM seal_individual_numbers_t WHERE seal_master_id = sn.id AND status = 'Available') as available_seals,
                    (SELECT COUNT(*) FROM seal_individual_numbers_t WHERE seal_master_id = sn.id AND status = 'Used') as used_seals,
                    (SELECT COUNT(*) FROM seal_individual_numbers_t WHERE seal_master_id = sn.id AND status = 'Damaged') as damaged_seals
                FROM seal_nos_t sn
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sn.purchase_date >= '2026-01-01'
                AND sn.office_location_id IN ($locationIdsString)
                ORDER BY mo.main_location_name ASC, sn.purchase_date DESC, sn.id DESC
            ";
            $seals = $db->customQuery($query);
            
            // Get all seal numbers - 2026 ONWARDS, EXCLUDE LIKASI AND KINSHASA
            $allSealsQuery = "
                SELECT 
                    sin.id,
                    sin.seal_number,
                    sin.status,
                    sin.notes,
                    sin.created_at,
                    sn.id as seal_master_id,
                    sn.purchase_date,
                    sn.total_amount,
                    mo.id as location_id,
                    mo.main_location_name as location
                FROM seal_individual_numbers_t sin
                INNER JOIN seal_nos_t sn ON sin.seal_master_id = sn.id
                LEFT JOIN main_office_master_t mo ON sn.office_location_id = mo.id
                WHERE sn.purchase_date >= '2026-01-01'
                AND sn.office_location_id IN ($locationIdsString)
                ORDER BY mo.main_location_name ASC, sn.purchase_date DESC, sn.id, sin.id
            ";
            $allSealNumbers = $db->customQuery($allSealsQuery);

            $vendorPath = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            
            if (!file_exists($vendorPath)) {
                die('PhpSpreadsheet library not found');
            }
            
            require_once $vendorPath;
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // ===== SHEET 1: SUMMARY =====
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Summary');

            $headerStyle = [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            $sheet->setCellValue('A1', 'SEAL MANAGEMENT SUMMARY REPORT (2026 ONWARDS)');
            $sheet->getStyle('A1')->applyFromArray($headerStyle);
            $sheet->getStyle('A1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet->mergeCells('A1:F1');
            $sheet->getRowDimension(1)->setRowHeight(25);

            $sheet->setCellValue('A2', 'Generated: ' . date('d-m-Y H:i:s'));
            $sheet->mergeCells('A2:F2');

            // Calculate totals
            $totalSealsAdded = count($allSealNumbers);
            $totalAvailable = 0;
            $totalUsed = 0;
            $totalDamaged = 0;
            
            foreach ($allSealNumbers as $sn) {
                if ($sn['status'] === 'Available') $totalAvailable++;
                elseif ($sn['status'] === 'Used') $totalUsed++;
                elseif ($sn['status'] === 'Damaged') $totalDamaged++;
            }

            $row = 4;
            $sheet->setCellValue('A' . $row, 'OVERALL STATISTICS');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $row++;

            $stats = [
                count($locations),
                count($seals),
                $totalSealsAdded,
                $totalAvailable,
                $totalUsed,
                $totalDamaged,
                $totalAvailable - $totalUsed - $totalDamaged
            ];

            $statLabels = ['Locations', 'Purchases', 'Added', 'Available', 'Used', 'Damaged', 'Remaining'];
            
            foreach ($statLabels as $index => $label) {
                $sheet->setCellValue('A' . $row, $stats[$index]);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
                $sheet->setCellValue('B' . $row, $label);
                $row++;
            }

            // Location-wise breakdown
            $row += 2;
            $sheet->setCellValue('A' . $row, 'LOCATION BREAKDOWN');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $row++;

            $subHeaderStyle = [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ]
            ];

            $headers = ['Location', 'Purchases', 'Added', 'Available', 'Used', 'Damaged'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->applyFromArray($subHeaderStyle);
                $col++;
            }
            $row++;

            // Calculate stats per location
            foreach ($locations as $location) {
                $locationId = $location['id'];
                $locationName = $location['main_location_name'];
                
                $locationSeals = array_filter($seals, function($seal) use ($locationId) {
                    return $seal['office_location_id'] == $locationId;
                });
                
                $locationSealNumbers = array_filter($allSealNumbers, function($sn) use ($locationId) {
                    return $sn['location_id'] == $locationId;
                });
                
                $locAdded = count($locationSealNumbers);
                $locAvailable = count(array_filter($locationSealNumbers, function($sn) {
                    return $sn['status'] === 'Available';
                }));
                $locUsed = count(array_filter($locationSealNumbers, function($sn) {
                    return $sn['status'] === 'Used';
                }));
                $locDamaged = count(array_filter($locationSealNumbers, function($sn) {
                    return $sn['status'] === 'Damaged';
                }));
                
                $sheet->setCellValue('A' . $row, $locationName);
                $sheet->setCellValue('B' . $row, count($locationSeals));
                $sheet->setCellValue('C' . $row, $locAdded);
                $sheet->setCellValue('D' . $row, $locAvailable);
                $sheet->setCellValue('E' . $row, $locUsed);
                $sheet->setCellValue('F' . $row, $locDamaged);
                $row++;
            }

            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // ===== SHEET 2: MONTHLY REPORT BY LOCATION =====
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Monthly Report');

            $sheet2->setCellValue('A1', 'MONTHLY SEAL REPORT BY LOCATION (2026 ONWARDS)');
            $sheet2->getStyle('A1')->applyFromArray($headerStyle);
            $sheet2->getStyle('A1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet2->mergeCells('A1:E1');
            $sheet2->getRowDimension(1)->setRowHeight(25);

            $row2 = 3;
            
            foreach ($locations as $location) {
                $locationId = $location['id'];
                $locationName = $location['main_location_name'];
                
                // Get seals for this location
                $locationSeals = array_filter($seals, function($seal) use ($locationId) {
                    return $seal['office_location_id'] == $locationId;
                });
                
                if (count($locationSeals) === 0) {
                    continue;
                }
                
                // Location header
                $sheet2->setCellValue('A' . $row2, strtoupper($locationName));
                $sheet2->getStyle('A' . $row2)->getFont()->setBold(true)->setSize(11);
                $sheet2->getStyle('A' . $row2)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet2->getStyle('A' . $row2)->getFill()->getStartColor()->setRGB('28a745');
                $sheet2->getStyle('A' . $row2)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet2->mergeCells('A' . $row2 . ':E' . $row2);
                $row2++;
                
                // Group by month for this location
                $monthlyData = [];
                foreach ($locationSeals as $seal) {
                    $yearMonth = date('Y-m', strtotime($seal['purchase_date']));
                    if (!isset($monthlyData[$yearMonth])) {
                        $monthlyData[$yearMonth] = [
                            'added_seals' => 0,
                            'available' => 0,
                            'used' => 0,
                            'damaged' => 0
                        ];
                    }
                    
                    $monthlyData[$yearMonth]['added_seals'] += $seal['added_seals'];
                    $monthlyData[$yearMonth]['available'] += $seal['available_seals'];
                    $monthlyData[$yearMonth]['used'] += $seal['used_seals'];
                    $monthlyData[$yearMonth]['damaged'] += $seal['damaged_seals'];
                }
                
                krsort($monthlyData);
                
                // Column headers
                $headers2 = ['Year-Month', 'Added', 'Available', 'Used', 'Damaged'];
                $col2 = 'A';
                foreach ($headers2 as $header) {
                    $sheet2->setCellValue($col2 . $row2, $header);
                    $sheet2->getStyle($col2 . $row2)->applyFromArray($subHeaderStyle);
                    $col2++;
                }
                $row2++;
                
                foreach ($monthlyData as $yearMonth => $data) {
                    $sheet2->setCellValue('A' . $row2, date('F Y', strtotime($yearMonth . '-01')));
                    $sheet2->setCellValue('B' . $row2, $data['added_seals']);
                    $sheet2->setCellValue('C' . $row2, $data['available']);
                    $sheet2->setCellValue('D' . $row2, $data['used']);
                    $sheet2->setCellValue('E' . $row2, $data['damaged']);
                    $row2++;
                }
                
                $row2 += 2;
            }

            foreach (range('A', 'E') as $col) {
                $sheet2->getColumnDimension($col)->setAutoSize(true);
            }

            // ===== SHEET 3: SEAL MASTERS BY LOCATION =====
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Seal Masters by Location');

            $sheet3->setCellValue('A1', 'SEAL MASTERS BY LOCATION (2026 ONWARDS)');
            $sheet3->getStyle('A1')->applyFromArray($headerStyle);
            $sheet3->getStyle('A1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet3->mergeCells('A1:I1');
            $sheet3->getRowDimension(1)->setRowHeight(25);

            $row3 = 3;
            
            foreach ($locations as $location) {
                $locationId = $location['id'];
                $locationName = $location['main_location_name'];
                
                $locationSeals = array_filter($seals, function($seal) use ($locationId) {
                    return $seal['office_location_id'] == $locationId;
                });
                
                if (count($locationSeals) === 0) {
                    continue;
                }
                
                // Location header
                $sheet3->setCellValue('A' . $row3, strtoupper($locationName));
                $sheet3->getStyle('A' . $row3)->getFont()->setBold(true)->setSize(11);
                $sheet3->getStyle('A' . $row3)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet3->getStyle('A' . $row3)->getFill()->getStartColor()->setRGB('28a745');
                $sheet3->getStyle('A' . $row3)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet3->mergeCells('A' . $row3 . ':I' . $row3);
                $row3++;
                
                // Column headers
                $headers3 = ['ID', 'Purchase Date', 'Total Amount', 'Total Seal', 'Added', 'Available', 'Used', 'Damaged', 'Display'];
                $col3 = 'A';
                foreach ($headers3 as $header) {
                    $sheet3->setCellValue($col3 . $row3, $header);
                    $sheet3->getStyle($col3 . $row3)->applyFromArray($subHeaderStyle);
                    $col3++;
                }
                $row3++;
                
                // Data rows
                foreach ($locationSeals as $seal) {
                    $sheet3->setCellValue('A' . $row3, $seal['id']);
                    $sheet3->setCellValue('B' . $row3, date('d-m-Y', strtotime($seal['purchase_date'])));
                    $sheet3->setCellValue('C' . $row3, '$' . number_format($seal['total_amount'], 2));
                    $sheet3->setCellValue('D' . $row3, $seal['total_seal']);
                    $sheet3->setCellValue('E' . $row3, $seal['added_seals']);
                    $sheet3->setCellValue('F' . $row3, $seal['available_seals']);
                    $sheet3->setCellValue('G' . $row3, $seal['used_seals']);
                    $sheet3->setCellValue('H' . $row3, $seal['damaged_seals']);
                    $sheet3->setCellValue('I' . $row3, $seal['display'] == 'Y' ? 'Yes' : 'No');
                    $row3++;
                }
                
                $row3 += 2;
            }

            foreach (range('A', 'I') as $col) {
                $sheet3->getColumnDimension($col)->setAutoSize(true);
            }

            // ===== SHEET 4: ALL SEAL NUMBERS BY LOCATION =====
            $sheet4 = $spreadsheet->createSheet();
            $sheet4->setTitle('Seal Numbers by Location');

            $sheet4->setCellValue('A1', 'ALL SEAL NUMBERS BY LOCATION (2026 ONWARDS)');
            $sheet4->getStyle('A1')->applyFromArray($headerStyle);
            $sheet4->getStyle('A1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $sheet4->mergeCells('A1:F1');
            $sheet4->getRowDimension(1)->setRowHeight(25);

            $row4 = 3;

            foreach ($locations as $location) {
                $locationId = $location['id'];
                $locationName = $location['main_location_name'];
                
                $locationSealNumbers = array_filter($allSealNumbers, function($sn) use ($locationId) {
                    return $sn['location_id'] == $locationId;
                });
                
                if (count($locationSealNumbers) === 0) {
                    continue;
                }
                
                // Location header
                $sheet4->setCellValue('A' . $row4, strtoupper($locationName) . ' - ' . count($locationSealNumbers) . ' SEAL NUMBERS');
                $sheet4->getStyle('A' . $row4)->getFont()->setBold(true)->setSize(11);
                $sheet4->getStyle('A' . $row4)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet4->getStyle('A' . $row4)->getFill()->getStartColor()->setRGB('667eea');
                $sheet4->getStyle('A' . $row4)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $sheet4->mergeCells('A' . $row4 . ':F' . $row4);
                $row4++;
                
                // Column headers
                $headers4 = ['ID', 'Seal Number', 'Status', 'Purchase Date', 'Total Amount', 'Notes'];
                $col4 = 'A';
                foreach ($headers4 as $header) {
                    $sheet4->setCellValue($col4 . $row4, $header);
                    $sheet4->getStyle($col4 . $row4)->applyFromArray($subHeaderStyle);
                    $col4++;
                }
                $row4++;
                
                // Data rows
                foreach ($locationSealNumbers as $sn) {
                    $sheet4->setCellValue('A' . $row4, $sn['id']);
                    $sheet4->setCellValue('B' . $row4, $sn['seal_number']);
                    $sheet4->setCellValue('C' . $row4, $sn['status']);
                    $sheet4->setCellValue('D' . $row4, date('d-m-Y', strtotime($sn['purchase_date'])));
                    $sheet4->setCellValue('E' . $row4, '$' . number_format($sn['total_amount'], 2));
                    $sheet4->setCellValue('F' . $row4, $sn['notes'] ?? '-');
                    $row4++;
                }
                
                $row4 += 2;
            }

            foreach (range('A', 'F') as $col) {
                $sheet4->getColumnDimension($col)->setAutoSize(true);
            }

            $spreadsheet->setActiveSheetIndex(0);

            $filename = 'Seals_Report_2026_Onwards_' . date('Y-m-d_His') . '.xlsx';
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