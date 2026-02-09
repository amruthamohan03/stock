<?php

class ImportapurmentController extends Controller
{
    // Import Kind IDs (IB kinds: 1, 2, 5, 6)
    const IMPORT_KIND_IDS = [1, 2, 5, 6];
    
    // Apurement Status Constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_APURED = 'APURED';
    const STATUS_PARTIAL = 'PARTIAL';

    /**
     * Main index page
     */
    public function index()
    {
        $db = new Database();

        // Ensure tables exist
        $this->ensureApurementTableExists($db);
        $this->ensureTransmissionTableExists($db);

        // Generate CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'Import License Clearance',
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('licenses/importapurment', $data);
    }

    /**
     * Main CRUD data handler
     */
    public function crudData($action = 'listing')
    {
        header('Content-Type: application/json');
        $db = new Database();

        // Ensure tables exist before any operation
        $this->ensureApurementTableExists($db);
        $this->ensureTransmissionTableExists($db);

        try {
            switch ($action) {
                case 'statistics':
                    $this->getStatistics($db);
                    break;
                    
                case 'getClientsWithLicenses':
                    $this->getClientsWithLicenses($db);
                    break;
                    
                case 'getBanksList':
                    $this->getBanksList($db);
                    break;
                    
                case 'getPendingImportFiles':
                    $this->getPendingImportFiles($db);
                    break;
                    
                case 'getTransmissionsList':
                    $this->getTransmissionsList($db);
                    break;
                    
                case 'getImportFileDetails':
                    $this->getImportFileDetails($db);
                    break;
                    
                case 'getLicensesForClient':
                    $this->getLicensesForClient($db);
                    break;
                    
                case 'updateImportFile':
                    $this->updateImportFile($db);
                    break;
                    
                case 'createTransmission':
                    $this->createTransmission($db);
                    break;
                    
                case 'exportPendingFiles':
                    $this->exportPendingFiles($db);
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } catch (Exception $e) {
            error_log("Import Apurement Error [{$action}]: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get all clients with import files that have quittance_date (ready for clearance)
     */
    private function getClientsWithLicenses($db)
    {
        $kindIdsStr = implode(',', self::IMPORT_KIND_IDS);
        
        $sql = "SELECT DISTINCT
                  c.id,
                  c.short_name,
                  c.company_name
                FROM imports_t i
                INNER JOIN licenses_t l ON i.license_id = l.id
                INNER JOIN clients_t c ON l.client_id = c.id
                WHERE i.display = 'Y'
                AND i.quittance_date IS NOT NULL
                AND l.display = 'Y' 
                AND l.kind_id IN ({$kindIdsStr})
                AND c.display = 'Y'
                ORDER BY c.short_name ASC";
        
        $clients = $db->customQuery($sql);
        
        if ($clients === false || !is_array($clients)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load clients'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $clients
        ]);
    }

    /**
     * Get all banks from banklist_master_t
     */
    private function getBanksList($db)
    {
        $sql = "SELECT 
                  id,
                  bank_name,
                  bank_code
                FROM banklist_master_t
                WHERE display = 'Y'
                ORDER BY bank_name ASC";
        
        $banks = $db->customQuery($sql);
        
        if ($banks === false || !is_array($banks)) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load banks'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $banks
        ]);
    }

    /**
     * Get apurement statistics for a client
     * FIXED: Only counts files with quittance_date
     */
    private function getStatistics($db)
    {
        $kindIdsStr = implode(',', self::IMPORT_KIND_IDS);
        $clientId = intval($_GET['client_id'] ?? 0);
        
        $whereClause = " WHERE i.display = 'Y' 
                         AND i.quittance_date IS NOT NULL
                         AND l.kind_id IN ({$kindIdsStr}) 
                         AND l.display = 'Y' ";
        $params = [];
        
        if ($clientId > 0) {
            $whereClause .= " AND l.client_id = ? ";
            $params[] = $clientId;
        }
        
        // Get all statistics in ONE query for accuracy
        $sql = "SELECT 
                  COUNT(DISTINCT i.id) as total_files,
                  COUNT(DISTINCT l.id) as total_licenses,
                  SUM(CASE 
                    WHEN ia.apurement_status = 'APURED' THEN 1 
                    ELSE 0 
                  END) as apured_count,
                  SUM(CASE 
                    WHEN ia.apurement_status = 'PARTIAL' THEN 1 
                    ELSE 0 
                  END) as partial_count,
                  SUM(CASE 
                    WHEN ia.apurement_status IS NULL OR ia.apurement_status = 'PENDING' THEN 1 
                    ELSE 0 
                  END) as pending_count
                FROM imports_t i
                INNER JOIN licenses_t l ON i.license_id = l.id
                LEFT JOIN import_apurement_t ia ON i.id = ia.import_id AND ia.display = 'Y'
                {$whereClause}";
        
        $result = $db->customQuery($sql, $params);
        
        if ($result && is_array($result) && !empty($result)) {
            $stats = $result[0];
            
            $data = [
                'total_import_files' => intval($stats['total_files'] ?? 0),
                'total_licenses' => intval($stats['total_licenses'] ?? 0),
                'pending_files' => intval($stats['pending_count'] ?? 0),
                'apured_files' => intval($stats['apured_count'] ?? 0),
                'partial_files' => intval($stats['partial_count'] ?? 0)
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load statistics'
            ]);
        }
    }
 
    /**
     * Get PENDING import files for a client
     * FIXED: Only returns files with quittance_date AND status PENDING/NULL
     */
    private function getPendingImportFiles($db)
    {
        $clientId = intval($_GET['client_id'] ?? 0);
        
        if ($clientId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
            return;
        }
        
        $kindIdsStr = implode(',', self::IMPORT_KIND_IDS);
        
        $sql = "SELECT 
                  i.id,
                  l.id as license_id,
                  l.license_number as num_licence,
                  l.license_expiry_date as date_ech,
                  i.mca_ref,
                  COALESCE(i.fob, 0) as fob,
                  COALESCE(i.fret, 0) as fret,
                  COALESCE(i.insurance_amount, 0) as assurance,
                  COALESCE(i.other_charges, 0) as autres_frets,
                  (COALESCE(i.fob, 0) + COALESCE(i.fret, 0) + COALESCE(i.insurance_amount, 0) + COALESCE(i.other_charges, 0)) as cif,
                  i.declaration_reference as ref_decl,
                  i.dgda_in_date as date_decl,
                  i.liquidation_reference as ref_liquid,
                  i.liquidation_date as date_liquid,
                  i.quittance_reference as ref_quit,
                  i.quittance_date as date_quit,
                  COALESCE(curr.currency_short_name, '') as monnaie,
                  l.license_applied_date as date_val,
                  (COALESCE(l.fob_declared, 0) + COALESCE(l.freight, 0) + COALESCE(l.insurance, 0) + COALESCE(l.other_costs, 0)) as cif_lic,
                  COALESCE(ia.ass_ref, '') as ass_ref,
                  COALESCE(ia.clearance_type, '') as clearance_type,
                  COALESCE(ia.clearance_remarks, '') as clearance_remarks,
                  COALESCE(ia.num_av, '') as num_av,
                  COALESCE(ia.montant_av, 0) as montant_av,
                  COALESCE(ia.facture, '') as facture,
                  COALESCE(ia.bl_lta, '') as bl_lta,
                  COALESCE(ia.apurement_status, 'PENDING') as apurement_status,
                  COALESCE(i.weight, 0) as weight,
                  i.inspection_reports,
                  c.short_name as client_name
                FROM imports_t i
                INNER JOIN licenses_t l ON i.license_id = l.id
                INNER JOIN clients_t c ON l.client_id = c.id
                LEFT JOIN currency_master_t curr ON i.currency = curr.id
                LEFT JOIN import_apurement_t ia ON i.id = ia.import_id AND ia.display = 'Y'
                WHERE i.display = 'Y'
                AND l.display = 'Y'
                AND l.kind_id IN ({$kindIdsStr})
                AND l.client_id = ?
                AND i.quittance_date IS NOT NULL
                AND (ia.apurement_status IS NULL OR ia.apurement_status = 'PENDING')
                ORDER BY i.quittance_date DESC, l.license_number ASC";
        
        $result = $db->customQuery($sql, [$clientId]);
        
        if ($result === false) {
            error_log("SQL Query failed for getPendingImportFiles");
            echo json_encode([
                'success' => false,
                'message' => 'Database query failed'
            ]);
            return;
        }
        
        if (!is_array($result)) {
            echo json_encode([
                'success' => true,
                'data' => []
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ]);
    }

    /**
     * Get transmissions list for main table
     */
    private function getTransmissionsList($db)
    {
        $clientId = intval($_GET['client_id'] ?? 0);
        $search = $_GET['search'] ?? '';
        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 10);
        $offset = ($page - 1) * $perPage;
        
        $whereClause = " WHERE t.display = 'Y' ";
        $params = [];
        
        if ($clientId > 0) {
            $whereClause .= " AND c.id = ? ";
            $params[] = $clientId;
        }
        
        if (!empty($search)) {
            $whereClause .= " AND (t.transmission_reference LIKE ? OR c.short_name LIKE ? OR l.license_number LIKE ?) ";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Get total count
        $countSql = "SELECT COUNT(DISTINCT t.id) as total
                     FROM import_transmission_t t
                     INNER JOIN clients_t c ON t.client_id = c.id
                     INNER JOIN licenses_t l ON t.license_id = l.id
                     {$whereClause}";
        
        $countResult = $db->customQuery($countSql, $params);
        $totalRecords = $countResult[0]['total'] ?? 0;
        
        // Get paginated data
        $sql = "SELECT 
                  t.id,
                  t.transmission_reference as license_number,
                  t.transmission_date as date_creation,
                  t.banque,
                  c.short_name as client_name,
                  c.id as client_id,
                  l.id as license_id,
                  l.license_number as original_license,
                  COUNT(DISTINCT i.id) as nbre_dossiers,
                  '' as date_depot
                FROM import_transmission_t t
                INNER JOIN clients_t c ON t.client_id = c.id
                INNER JOIN licenses_t l ON t.license_id = l.id
                LEFT JOIN imports_t i ON i.license_id = l.id AND i.display = 'Y'
                {$whereClause}
                GROUP BY t.id, t.transmission_reference, t.transmission_date, t.banque, 
                         c.short_name, c.id, l.id, l.license_number
                ORDER BY t.transmission_date DESC, t.transmission_reference DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $result = $db->customQuery($sql, $params);
        
        if ($result && is_array($result)) {
            echo json_encode([
                'success' => true,
                'data' => $result,
                'pagination' => [
                    'total' => $totalRecords,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => ceil($totalRecords / $perPage)
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'total_pages' => 0
                ]
            ]);
        }
    }

    /**
     * Get licenses for a specific client
     */
    private function getLicensesForClient($db)
    {
        $clientId = intval($_GET['client_id'] ?? 0);
        
        if ($clientId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
            return;
        }
        
        $kindIdsStr = implode(',', self::IMPORT_KIND_IDS);
        
        $sql = "SELECT DISTINCT
                  l.id,
                  l.license_number
                FROM licenses_t l
                INNER JOIN imports_t i ON i.license_id = l.id
                WHERE l.client_id = ?
                AND l.display = 'Y'
                AND l.kind_id IN ({$kindIdsStr})
                AND i.display = 'Y'
                ORDER BY l.license_number DESC";
        
        $result = $db->customQuery($sql, [$clientId]);
        
        if ($result && is_array($result)) {
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => []
            ]);
        }
    }

    /**
     * Get import file details for editing
     */
    private function getImportFileDetails($db)
    {
        $licenseId = intval($_GET['license_id'] ?? 0);
        
        if ($licenseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
            return;
        }
        
        $kindIdsStr = implode(',', self::IMPORT_KIND_IDS);
        
        $sql = "SELECT 
                  i.id,
                  i.mca_ref,
                  COALESCE(i.fob, 0) as fob,
                  COALESCE(i.fret, 0) as fret,
                  COALESCE(i.insurance_amount, 0) as insurance_amount,
                  COALESCE(i.other_charges, 0) as other_charges,
                  (COALESCE(i.fob, 0) + COALESCE(i.fret, 0) + COALESCE(i.insurance_amount, 0) + COALESCE(i.other_charges, 0)) as cif,
                  i.declaration_reference,
                  i.dgda_in_date,
                  i.liquidation_reference,
                  i.liquidation_date,
                  i.quittance_reference,
                  i.quittance_date,
                  COALESCE(i.weight, 0) as weight,
                  l.license_number,
                  l.license_applied_date,
                  l.license_expiry_date,
                  (COALESCE(l.fob_declared, 0) + COALESCE(l.freight, 0) + COALESCE(l.insurance, 0) + COALESCE(l.other_costs, 0)) as cif_license,
                  c.short_name as client_name,
                  COALESCE(curr.currency_short_name, '') as currency_short_name,
                  COALESCE(ia.ass_ref, '') as ass_ref,
                  COALESCE(ia.clearance_type, '') as clearance_type,
                  COALESCE(ia.clearance_remarks, '') as clearance_remarks,
                  COALESCE(ia.apurement_status, 'PENDING') as apurement_status
                FROM imports_t i
                INNER JOIN licenses_t l ON i.license_id = l.id
                INNER JOIN clients_t c ON l.client_id = c.id
                LEFT JOIN currency_master_t curr ON i.currency = curr.id
                LEFT JOIN import_apurement_t ia ON i.id = ia.import_id AND ia.display = 'Y'
                WHERE l.id = ?
                AND i.display = 'Y'
                AND l.display = 'Y'
                AND l.kind_id IN ({$kindIdsStr})
                ORDER BY i.quittance_date DESC";
        
        $result = $db->customQuery($sql, [$licenseId]);
        
        if ($result && is_array($result)) {
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No files found'
            ]);
        }
    }

    /**
     * Update import file (INSERT or UPDATE in import_apurement_t)
     */
    private function updateImportFile($db)
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }
        
        $importId = intval($_POST['import_id'] ?? 0);
        $assRef = trim($_POST['ass_ref'] ?? '');
        $clearanceType = trim($_POST['clearance_type'] ?? '');
        $clearanceRemarks = trim($_POST['clearance_remarks'] ?? '');
        
        if ($importId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid import ID']);
            return;
        }
        
        if (empty($assRef) || empty($clearanceType) || empty($clearanceRemarks)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }
        
        try {
            // Get license_id for this import
            $licenseQuery = "SELECT license_id FROM imports_t WHERE id = ? AND display = 'Y'";
            $licenseResult = $db->customQuery($licenseQuery, [$importId]);
            
            if (!$licenseResult || empty($licenseResult)) {
                echo json_encode(['success' => false, 'message' => 'Import file not found']);
                return;
            }
            
            $licenseId = $licenseResult[0]['license_id'];
            $userId = $_SESSION['user_id'] ?? 1;
            
            // Check if record exists
            $checkQuery = "SELECT id FROM import_apurement_t 
                          WHERE import_id = ? AND display = 'Y'";
            $existing = $db->customQuery($checkQuery, [$importId]);
            
            if ($existing && !empty($existing)) {
                // UPDATE existing record
                $updateQuery = "UPDATE import_apurement_t 
                               SET ass_ref = ?,
                                   clearance_type = ?,
                                   clearance_remarks = ?,
                                   updated_by = ?,
                                   updated_at = NOW()
                               WHERE import_id = ? 
                               AND display = 'Y'";
                
                $params = [$assRef, $clearanceType, $clearanceRemarks, $userId, $importId];
                $result = $db->customQuery($updateQuery, $params);
                
            } else {
                // INSERT new record
                $insertQuery = "INSERT INTO import_apurement_t 
                               (import_id, license_id, ass_ref, clearance_type, clearance_remarks, 
                                apurement_status, created_by, created_at, display)
                               VALUES (?, ?, ?, ?, ?, 'PENDING', ?, NOW(), 'Y')";
                
                $params = [$importId, $licenseId, $assRef, $clearanceType, $clearanceRemarks, $userId];
                $result = $db->customQuery($insertQuery, $params);
            }
            
            if ($result !== false) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Import file updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update import file'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Update import file error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new transmission
     */
    private function createTransmission($db)
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            return;
        }
        
        $clientId = intval($_POST['client_id'] ?? 0);
        $banque = trim($_POST['banque'] ?? '');
        $reference = trim($_POST['reference'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $licenseId = intval($_POST['licence'] ?? 0);
        
        if ($clientId <= 0 || $licenseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid client or license']);
            return;
        }
        
        if (empty($banque) || empty($reference) || empty($date)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'] ?? 1;
            
            // Insert transmission
            $insertQuery = "INSERT INTO import_transmission_t 
                           (client_id, license_id, banque, transmission_reference, transmission_date, 
                            created_by, created_at, display)
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Y')";
            
            $params = [$clientId, $licenseId, $banque, $reference, $date, $userId];
            $result = $db->customQuery($insertQuery, $params);
            
            if ($result !== false) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Transmission created successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create transmission'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Create transmission error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export pending files to Excel
     */
    private function exportPendingFiles($db)
    {
        $clientId = intval($_GET['client_id'] ?? 0);
        
        if ($clientId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
            return;
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Export functionality coming soon'
        ]);
    }

    /**
     * Ensure import_apurement_t table exists with all required columns
     */
    private function ensureApurementTableExists($db)
    {
        try {
            $checkQuery = "SHOW TABLES LIKE 'import_apurement_t'";
            $result = $db->customQuery($checkQuery);
            
            if (empty($result)) {
                $this->createApurementTable($db);
                error_log("import_apurement_t table created successfully");
            } else {
                $this->migrateApurementTable($db);
            }
        } catch (Exception $e) {
            error_log("Error ensuring import_apurement_t table: " . $e->getMessage());
        }
    }

    /**
     * Create the apurement table
     */
    private function createApurementTable($db)
    {
        $createQuery = "CREATE TABLE `import_apurement_t` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `import_id` INT(11) NOT NULL COMMENT 'FK to imports_t',
          `license_id` INT(11) NOT NULL COMMENT 'FK to licenses_t',
          
          -- Clearance Details
          `ass_ref` VARCHAR(100) NULL COMMENT 'ASS Reference',
          `clearance_type` ENUM('Partial', 'Total') NULL COMMENT 'Clearance Type',
          `clearance_remarks` ENUM('AV Provissional', 'License Provissional') NULL COMMENT 'Clearance Remarks',
          
          -- Additional Fields
          `num_av` VARCHAR(100) NULL COMMENT 'NUM AV',
          `montant_av` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'MONTANT AV',
          `facture` VARCHAR(100) NULL COMMENT 'FACTURE',
          `bl_lta` VARCHAR(100) NULL COMMENT 'BL/LTA',
          
          -- Apurement Status
          `apurement_status` ENUM('PENDING', 'PARTIAL', 'APURED') NOT NULL DEFAULT 'PENDING',
          
          -- Transmission Details
          `transmission_date` DATE NULL,
          `transmission_reference` VARCHAR(100) NULL,
          
          -- Reception Details
          `reception_date` DATE NULL,
          `reception_reference` VARCHAR(100) NULL,
          
          -- Audit Fields
          `created_by` INT(11) NOT NULL,
          `updated_by` INT(11) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          `display` ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
          
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_import_id` (`import_id`, `display`),
          KEY `idx_license_id` (`license_id`),
          KEY `idx_apurement_status` (`apurement_status`),
          KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
          COMMENT='Import License Clearance/Apurement Tracking Table'";
        
        $db->customQuery($createQuery);
    }

    /**
     * Migrate existing table to add missing columns
     */
    private function migrateApurementTable($db)
    {
        try {
            $columnsQuery = "SHOW COLUMNS FROM import_apurement_t";
            $existingColumns = $db->customQuery($columnsQuery);
            
            if (!$existingColumns || !is_array($existingColumns)) {
                return;
            }
            
            $columnNames = array_column($existingColumns, 'Field');
            
            $requiredColumns = [
                'ass_ref' => "ADD COLUMN `ass_ref` VARCHAR(100) NULL COMMENT 'ASS Reference' AFTER `license_id`",
                'clearance_type' => "ADD COLUMN `clearance_type` ENUM('Partial', 'Total') NULL COMMENT 'Clearance Type' AFTER `ass_ref`",
                'clearance_remarks' => "ADD COLUMN `clearance_remarks` ENUM('AV Provissional', 'License Provissional') NULL COMMENT 'Clearance Remarks' AFTER `clearance_type`",
                'num_av' => "ADD COLUMN `num_av` VARCHAR(100) NULL COMMENT 'NUM AV' AFTER `clearance_remarks`",
                'montant_av' => "ADD COLUMN `montant_av` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'MONTANT AV' AFTER `num_av`",
                'facture' => "ADD COLUMN `facture` VARCHAR(100) NULL COMMENT 'FACTURE' AFTER `montant_av`",
                'bl_lta' => "ADD COLUMN `bl_lta` VARCHAR(100) NULL COMMENT 'BL/LTA' AFTER `facture`"
            ];
            
            foreach ($requiredColumns as $columnName => $alterSql) {
                if (!in_array($columnName, $columnNames)) {
                    try {
                        $db->customQuery("ALTER TABLE import_apurement_t {$alterSql}");
                        error_log("Added column '{$columnName}' to import_apurement_t");
                    } catch (Exception $e) {
                        error_log("Failed to add column '{$columnName}': " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Migration error: " . $e->getMessage());
        }
    }

    /**
     * Ensure import_transmission_t table exists
     */
    private function ensureTransmissionTableExists($db)
    {
        try {
            $checkQuery = "SHOW TABLES LIKE 'import_transmission_t'";
            $result = $db->customQuery($checkQuery);
            
            if (empty($result)) {
                $createQuery = "CREATE TABLE `import_transmission_t` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `client_id` INT(11) NOT NULL COMMENT 'FK to clients_t',
                  `license_id` INT(11) NOT NULL COMMENT 'FK to licenses_t',
                  `banque` VARCHAR(100) NOT NULL COMMENT 'Bank name',
                  `transmission_reference` VARCHAR(100) NOT NULL COMMENT 'Transmission reference',
                  `transmission_date` DATE NOT NULL COMMENT 'Transmission date',
                  
                  -- Audit Fields
                  `created_by` INT(11) NOT NULL,
                  `updated_by` INT(11) NULL,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                  `display` ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
                  
                  PRIMARY KEY (`id`),
                  KEY `idx_client_id` (`client_id`),
                  KEY `idx_license_id` (`license_id`),
                  KEY `idx_transmission_date` (`transmission_date`),
                  KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
                  COMMENT='Import Transmission/Apurement Tracking Table'";
                
                $db->customQuery($createQuery);
                error_log("import_transmission_t table created successfully");
            }
        } catch (Exception $e) {
            error_log("Error ensuring import_transmission_t table: " . $e->getMessage());
        }
    }
}