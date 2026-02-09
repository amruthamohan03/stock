<?php

class ExportController extends Controller
{
  private $db;
  private $logFile;
  private $Dashboard; // ✅ ADD THIS
private $allowedFilters = [
  'completed', 'in_progress', 'in_transit', 'ceec_pending', 'min_div_pending', 
  'gov_docs_pending', 'audited_pending', 'archived_pending', 'dgda_in_pending', 
  'liquidation_pending', 'quittance_pending', 'dispatch_pending', 'lmc_pending', 
  'ogefrem_pending', 'seal_pending' ,'lmc_id_pending','lmc_date_pending','ogefrem_ref_pending','ogefrem_date_pending' // ✅ ADDED
];
  public function __construct()
  {
    parent::__construct(); 
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/export_operations.log';
    $this->Dashboard = $this->model('Dashboard');
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
  }

  public function index()
  {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['csrf_token_time'] = time();
    }
    
    // Get current user
        $user = $this->getCurrentUser(); 
        // Get role_id from session or user object
        // Adjust this based on how you store role_id in your app
        $roleId = $user['role_id']; 
        // Get cards based on user's role
        if($roleId) { 
            $cards = $this->Dashboard->getCardsByRole($roleId,$menuId=60);
        } else {
            // Fallback: Get all active cards if no role found
            $cards = $this->Dashboard->getAllActiveCards();
        }
        // Get data (counts) for each card
        $cardData = $this->Dashboard->getCardData($cards);

        // Legacy counts for backward compatibility
        $counts = $this->Dashboard->getCounts();

    $sql = "SELECT DISTINCT c.id, c.short_name, c.liquidation_paid_by 
            FROM clients_t c
            INNER JOIN licenses_t l ON c.id = l.client_id
            WHERE c.display = 'Y' 
              AND c.client_type LIKE '%E%'
              AND l.kind_id IN (3, 4)
              AND l.display = 'Y'
              AND l.status != 'ANNULATED'
              AND (l.license_expiry_date IS NULL OR l.license_expiry_date >= CURDATE())
            ORDER BY c.short_name ASC";
    $subscribers = $this->db->customQuery($sql) ?: [];

    $regimes = $this->db->selectData('regime_master_t', 'id, regime_name', ['display' => 'Y', 'type' => 'E'], 'regime_name ASC') ?: [];
    $currencies = $this->db->selectData('currency_master_t', 'id, currency_name, currency_short_name', ['display' => 'Y'], 'currency_short_name ASC') ?: [];
    $exit_points = $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['display' => 'Y', 'exit_point' => 'Y'], 'transit_point_name ASC') ?: [];
    $loading_sites = $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['display' => 'Y', 'loading' => 'Y'], 'transit_point_name ASC') ?: [];
    $clearance_types = $this->db->selectData('clearance_master_t', 'id, clearance_name', ['display' => 'Y'], 'clearance_name ASC') ?: [];
    $clearing_statuses = $this->db->selectData('clearing_status_master_t', 'id, clearing_status', ['display' => 'Y'], 'clearing_status ASC') ?: [];
    $document_statuses = $this->db->selectData('document_status_master_t', 'id, document_status', ['display' => 'Y', 'type' => 'E'], 'document_status ASC') ?: [];
    $truck_statuses = $this->db->selectData('truck_status_master_t', 'id, truck_status', ['display' => 'Y'], 'truck_status ASC') ?: [];
    $feet_containers = $this->db->selectData('feet_container_master_t', 'id, feet_container_size', ['display' => 'Y'], 'feet_container_size ASC') ?: [];
    $transport_modes = $this->db->selectData('transport_mode_master_t', 'id, transport_mode_name', ['display' => 'Y'], 'transport_mode_name ASC') ?: [];

    $data = [
      'title' => 'Export Management',
      'subscribers' => $this->sanitizeArray($subscribers),
      'regimes' => $this->sanitizeArray($regimes),
      'currencies' => $this->sanitizeArray($currencies),
      'exit_points' => $this->sanitizeArray($exit_points),
      'loading_sites' => $this->sanitizeArray($loading_sites),
      'clearance_types' => $this->sanitizeArray($clearance_types),
      'clearing_statuses' => $this->sanitizeArray($clearing_statuses),
      'document_statuses' => $this->sanitizeArray($document_statuses),
      'truck_statuses' => $this->sanitizeArray($truck_statuses),
      'feet_containers' => $this->sanitizeArray($feet_containers),
      'transport_modes' => $this->sanitizeArray($transport_modes),
      'csrf_token' => $_SESSION['csrf_token'],
      'cards'       => $cards,      // Dynamic cards based on role
      'cardData'    => $cardData,   // Card counts/data
      'counts'      => $counts,  
    ];
    $this->viewWithLayout('tracking/exports', $data);
  }

  private function sanitizeArray($data)
  {
    if (!is_array($data)) return [];
    
    return array_map(function($item) {
      if (is_array($item)) {
        return array_map(function($value) {
          return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $value;
        }, $item);
      }
      return $item;
    }, $data);
  }

  public function crudData($action = 'insertion')
  {
    header('Content-Type: application/json');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');

    try {
      switch ($action) {
        case 'insert':
        case 'insertion':
          $this->insertExport();
          break;
        case 'bulkInsertFromModal':
          $this->bulkInsertFromModal();
          break;
        case 'update':
          $this->updateExport();
          break;
        case 'getExport':
          $this->getExport();
          break;
        case 'listing':
          $this->listExports();
          break;
        case 'getStatistics':
        case 'statistics':
          $this->getStatistics();
          break;
        case 'getLicenses':
          $this->getLicenses();
          break;
        case 'getLicenseDetails':
          $this->getLicenseDetails();
          break;
        case 'getLicenseUsage':
          $this->getLicenseUsage();
          break;
        case 'getNextMCASequence':
          $this->getNextMCASequence();
          break;
        case 'getClearingStatusIds':
          $this->getClearingStatusIds();
          break;
        case 'exportExport':
          $this->exportExport();
          break;
        case 'exportAll':
          $this->exportAllExports();
          break;
        case 'getBulkUpdateData':
          $this->getBulkUpdateData();
          break;
        case 'bulkUpdate':
          $this->bulkUpdate();
          break;
        case 'getAvailableSeals':
          $this->getAvailableSeals();
          break;
        default:
          $this->logError('Invalid action attempted', ['action' => $action]);
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      $this->logError('Server error in crudData', [
        'action' => $action,
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Server error occurred. Please try again.']);
    }
    exit;
  }

  private function getAvailableSeals()
  {
    try {
      $sql = "SELECT id, seal_number 
              FROM seal_individual_numbers_t 
              WHERE status = 'Available' 
                AND display = 'Y'
              ORDER BY seal_number ASC";
      
      $seals = $this->db->customQuery($sql);
      $seals = $this->sanitizeArray($seals);
      
      echo json_encode([
        'success' => true,
        'data' => $seals ?: []
      ]);
    } catch (Exception $e) {
      $this->logError('Failed to get available seals', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load available seals']);
    }
  }

  private function validateWeight($input, $fieldName = 'Weight')
  {
    if ($input === '' || $input === null) {
      return ['valid' => true, 'value' => 0.000];
    }

    if (!is_numeric($input)) {
      return ['valid' => false, 'message' => "{$fieldName} must be a valid number"];
    }
    
    $weight = floatval($input);
    $weight = abs($weight);
    
    if ($weight > 999999.999) {
      return ['valid' => false, 'message' => "{$fieldName} exceeds maximum allowed (999,999.999)"];
    }
    
    return ['valid' => true, 'value' => round($weight, 3)];  // ✅ Changed to 3 decimals
  }

  private function validateAgainstLicense($licenseId, $requestedWeight, $requestedFOB, $excludeExportId = null)
  {
    $license = $this->db->selectData('licenses_t', 
      'weight, fob_declared', 
      ['id' => $licenseId, 'display' => 'Y']
    );
    
    if (empty($license)) {
      return ['success' => false, 'message' => 'License not found'];
    }
    
    $sql = "SELECT COALESCE(SUM(weight), 0) as used_weight,
                   COALESCE(SUM(fob), 0) as used_fob
            FROM exports_t 
            WHERE license_id = :license_id 
            AND display = 'Y'";
    
    $params = [':license_id' => $licenseId];
    
    if ($excludeExportId) {
      $sql .= " AND id < :exclude_id";
      $params[':exclude_id'] = $excludeExportId;
    }
    
    $used = $this->db->customQuery($sql, $params)[0];
    
    $availableWeight = floatval($license[0]['weight']) - floatval($used['used_weight']);
    $availableFOB = floatval($license[0]['fob_declared']) - floatval($used['used_fob']);
    
    if ($requestedWeight > $availableWeight) {
      return [
        'success' => false, 
        'message' => sprintf(
          "Weight exceeds license limit. Requested: %.3f MT, Available: %.3f MT", 
          $requestedWeight, 
          $availableWeight
        )
      ];
    }
    
    if ($requestedFOB > $availableFOB) {
      return [
        'success' => false, 
        'message' => sprintf(
          "FOB exceeds license limit. Requested: %.2f, Available: %.2f", 
          $requestedFOB, 
          $availableFOB
        )
      ];
    }
    
    return ['success' => true];
  }

  private function validateAndReserveSeals($sealIds, $mcaRef, $userId)
  {
    if (empty($sealIds) || !is_array($sealIds)) {
      return ['success' => true];
    }
    
    $sealIds = array_map('intval', array_filter($sealIds));
    
    if (empty($sealIds)) {
      return ['success' => true];
    }
    
    $placeholders = implode(',', array_fill(0, count($sealIds), '?'));
    $sql = "SELECT id, seal_number, status 
            FROM seal_individual_numbers_t 
            WHERE id IN ($placeholders) 
            AND display = 'Y'";
    
    $seals = $this->db->customQuery($sql, $sealIds);
    
    if (count($seals) !== count($sealIds)) {
      return ['success' => false, 'message' => 'Some selected seals were not found in the system'];
    }
    
    $unavailableSeals = [];
    foreach ($seals as $seal) {
      if ($seal['status'] !== 'Available') {
        $unavailableSeals[] = $seal['seal_number'] . ' (' . $seal['status'] . ')';
      }
    }
    
    if (!empty($unavailableSeals)) {
      return [
        'success' => false, 
        'message' => 'The following seals are not available: ' . implode(', ', $unavailableSeals)
      ];
    }
    
    foreach ($sealIds as $sealId) {
      $this->db->updateData('seal_individual_numbers_t', [
        'status' => 'Used',
        'notes' => 'Used in Export MCA: ' . $mcaRef,
        'updated_by' => $userId,
        'updated_at' => date('Y-m-d H:i:s')
      ], ['id' => $sealId]);
    }
    
    return ['success' => true];
  }

  private function releaseSeals($sealNumbers, $userId)
  {
    if (empty($sealNumbers)) return;
    
    $sealArray = explode(',', $sealNumbers);
    foreach ($sealArray as $sealNumber) {
      $sealNumber = trim($sealNumber);
      if (!empty($sealNumber)) {
        $this->db->customQuery(
          "UPDATE seal_individual_numbers_t 
           SET status = 'Available', notes = NULL, updated_by = :user_id, updated_at = NOW() 
           WHERE seal_number = :seal_number",
          [':user_id' => $userId, ':seal_number' => $sealNumber]
        );
      }
    }
  }

  private function calculateCEECAmount($weight)
  {
    $weight = floatval($weight);
    return ($weight >= 30.00) ? 800.00 : 600.00;
  }

  private function calculateCGEAAmount()
  {
    return 80.00;
  }

  private function calculateOCCAmount()
  {
    return 250.00;
  }

  private function calculateLMCAmount($typeOfGoodsId, $weight)
  {
    $typeOfGoodsId = intval($typeOfGoodsId);
    $weight = floatval($weight);
    
    if ($typeOfGoodsId === 8) {
      return $weight * 8.00;
    }
    
    return $weight * 5.00;
  }

  private function calculateOGEFREMAmount($feetContainerId)
  {
    if (empty($feetContainerId)) {
      return null;
    }
    
    $feetContainerId = intval($feetContainerId);
    
    switch ($feetContainerId) {
      case 1:
        return 50.00;
      case 2:
      case 3:
        return 100.00;
      case 4:
        return 150.00;
      case 5:
        return 30.00;
      default:
        return null;
    }
  }

  private function calculateAllAmounts($weight, $typeOfGoodsId, $feetContainerId = null)
  {
    $weight = floatval($weight);
    
    $amounts = [
      'ceec_amount' => null,
      'cgea_amount' => 80.00,
      'occ_amount' => 250.00,
      'lmc_amount' => null,
      'ogefrem_amount' => null
    ];
    
    if ($weight > 0) {
      $amounts['ceec_amount'] = $this->calculateCEECAmount($weight);
      $amounts['lmc_amount'] = $this->calculateLMCAmount($typeOfGoodsId, $weight);
    }
    
    if ($feetContainerId) {
      $amounts['ogefrem_amount'] = $this->calculateOGEFREMAmount($feetContainerId);
    }
    
    return $amounts;
  }

  private function checkDuplicateExport($data, $excludeId = null)
  {
    $horse = $data['horse'] ?? null;
    $trailer1 = $data['trailer_1'] ?? null;
    $trailer2 = $data['trailer_2'] ?? null;
    $weight = $data['weight'] ?? 0;
    $lotNumber = $data['lot_number'] ?? null;
    
    if (empty($horse) && empty($trailer1) && empty($trailer2) && empty($lotNumber)) {
      return ['success' => true];
    }
    
    $sql = "SELECT id, mca_ref FROM exports_t WHERE display = 'Y'";
    $params = [];
    $conditions = [];
    
    if (!empty($horse)) {
      $conditions[] = "horse = :horse";
      $params[':horse'] = $horse;
    } else {
      $conditions[] = "(horse IS NULL OR horse = '')";
    }
    
    if (!empty($trailer1)) {
      $conditions[] = "trailer_1 = :trailer1";
      $params[':trailer1'] = $trailer1;
    } else {
      $conditions[] = "(trailer_1 IS NULL OR trailer_1 = '')";
    }
    
    if (!empty($trailer2)) {
      $conditions[] = "trailer_2 = :trailer2";
      $params[':trailer2'] = $trailer2;
    } else {
      $conditions[] = "(trailer_2 IS NULL OR trailer_2 = '')";
    }
    
    $conditions[] = "weight = :weight";
    $params[':weight'] = $weight;
    
    if (!empty($lotNumber)) {
      $conditions[] = "lot_number = :lot_number";
      $params[':lot_number'] = $lotNumber;
    } else {
      $conditions[] = "(lot_number IS NULL OR lot_number = '')";
    }
    
    if ($excludeId) {
      $conditions[] = "id != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }
    
    $sql .= " AND " . implode(" AND ", $conditions);
    
    $existing = $this->db->customQuery($sql, $params);
    
    if (!empty($existing)) {
      return [
        'success' => false,
        'message' => 'Duplicate export found with same Horse, Trailer 1, Trailer 2, Weight, and Lot Number. Existing MCA Ref: ' . $existing[0]['mca_ref']
      ];
    }
    
    return ['success' => true];
  }

private function insertExport()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $validation = $this->validateExportData($_POST);
      if (!$validation['success']) {
        echo json_encode($validation);
        return;
      }

      $data = $this->prepareExportData($_POST);
      
      $weightValidation = $this->validateWeight($data['weight'], 'Weight');
      if (!$weightValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $weightValidation['message']]);
        return;
      }
      $data['weight'] = $weightValidation['value'];
      
      $fobValidation = $this->validateWeight($data['fob'], 'FOB');
      if (!$fobValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $fobValidation['message']]);
        return;
      }
      $data['fob'] = $fobValidation['value'];

      $licenseValidation = $this->validateAgainstLicense(
        $data['license_id'], 
        $data['weight'], 
        $data['fob']
      );
      if (!$licenseValidation['success']) {
        echo json_encode($licenseValidation);
        return;
      }

      $duplicateCheck = $this->checkDuplicateExport($data);
      if (!$duplicateCheck['success']) {
        echo json_encode($duplicateCheck);
        return;
      }

      $amounts = $this->calculateAllAmounts(
        $data['weight'], 
        $data['type_of_goods'],
        $data['feet_container']
      );
      
      $data = array_merge($data, $amounts);
      
      if (empty($data['clearing_status'])) {
        $defaultClearingStatus = 5;
        $statusResult = $this->db->customQuery("SELECT id FROM clearing_status_master_t WHERE clearing_status LIKE '%IN TRANSIT%' AND display = 'Y' LIMIT 1");
        if (!empty($statusResult)) {
          $defaultClearingStatus = (int)$statusResult[0]['id'];
        }
        $data['clearing_status'] = $defaultClearingStatus;
      }
      
      $userId = (int)($_SESSION['user_id'] ?? 1);
      $data['created_by'] = $userId;
      $data['updated_by'] = $userId;
      $data['display'] = 'Y';

      // ✅ FIX: Handle both JSON array and comma-separated string formats
      $sealIdsRaw = $_POST['dgda_seal_ids'] ?? '';

      if (empty($sealIdsRaw)) {
          $sealIds = [];
      } elseif (is_string($sealIdsRaw) && strlen($sealIdsRaw) > 0 && $sealIdsRaw[0] === '[') {
          // JSON array format: ["1","2","3"]
          $sealIds = json_decode($sealIdsRaw, true);
          if (!is_array($sealIds)) {
              $sealIds = [];
          }
      } else {
          // Comma-separated string format: "1,2,3"
          $sealIds = array_filter(
              array_map('intval', 
              array_map('trim', explode(',', $sealIdsRaw)))
          );
      }

      $sealValidation = $this->validateAndReserveSeals($sealIds, $data['mca_ref'], $userId);
      if (!$sealValidation['success']) {
        echo json_encode($sealValidation);
        return;
      }

      $insertId = $this->db->insertData('exports_t', $data);

      if ($insertId) {
        $this->logInfo('Export created successfully', ['export_id' => $insertId]);
        echo json_encode(['success' => true, 'message' => 'Export created successfully!', 'id' => $insertId]);
      } else {
        if (!empty($sealIds)) {
          $this->releaseSeals($data['dgda_seal_no'], $userId);
        }
        echo json_encode(['success' => false, 'message' => 'Failed to save export. Please check all required fields.']);
      }
    } catch (Exception $e) {
      $this->logError('Exception during export insert', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

  private function bulkInsertFromModal()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $commonData = json_decode($_POST['common_data'] ?? '{}', true);
      $rowsData = json_decode($_POST['rows_data'] ?? '[]', true);
      
      if (empty($commonData) || empty($rowsData)) {
        echo json_encode(['success' => false, 'message' => 'No data provided']);
        return;
      }

      if (count($rowsData) > 3000) {
        echo json_encode(['success' => false, 'message' => 'Maximum 500 exports can be created at once']);
        return;
      }

      $requiredFields = ['subscriber_id', 'license_id', 'regime', 'types_of_clearance'];
      foreach ($requiredFields as $field) {
        if (empty($commonData[$field])) {
          echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
          return;
        }
      }

      $totalWeight = 0;
      $totalFOB = 0;
      $hasWeight = false;
      
      foreach ($rowsData as $row) {
        $weight = isset($row['weight']) && is_numeric($row['weight']) ? abs(floatval($row['weight'])) : 0;
        $fob = isset($row['fob']) && is_numeric($row['fob']) ? abs(floatval($row['fob'])) : 0;
        
        if ($weight > 0) $hasWeight = true;
        
        $totalWeight += $weight;
        $totalFOB += $fob;
      }

      if (!$hasWeight) {
        echo json_encode(['success' => false, 'message' => 'At least one entry must have weight > 0']);
        return;
      }

      $licenseValidation = $this->validateAgainstLicense(
        $commonData['license_id'], 
        $totalWeight, 
        $totalFOB
      );
      if (!$licenseValidation['success']) {
        echo json_encode($licenseValidation);
        return;
      }

      $defaultClearingStatus = 5;
      $statusResult = $this->db->customQuery("SELECT id FROM clearing_status_master_t WHERE clearing_status LIKE '%IN TRANSIT%' AND display = 'Y' LIMIT 1");
      if (!empty($statusResult)) {
        $defaultClearingStatus = (int)$statusResult[0]['id'];
      }

      $userId = (int)($_SESSION['user_id'] ?? 1);
      
      $this->db->beginTransaction();

      $successCount = 0;
      $errorCount = 0;
      $errors = [];
      $createdIds = [];
      $usedSeals = [];

      try {
        foreach ($rowsData as $index => $row) {
          $entryNum = $index + 1;
          
          $mcaRef = $this->clean($row['mca_ref'] ?? '');
          
          if (empty($mcaRef)) {
            $errors[] = "Entry #{$entryNum}: MCA Reference is missing";
            $errorCount++;
            continue;
          }
          
          $existingMCA = $this->db->selectData('exports_t', 'id', ['mca_ref' => $mcaRef, 'display' => 'Y']);
          if (!empty($existingMCA)) {
            $errors[] = "Entry #{$entryNum}: MCA Reference {$mcaRef} already exists";
            $errorCount++;
            continue;
          }

          $weightValidation = $this->validateWeight($row['weight'] ?? 0);
          if (!$weightValidation['valid']) {
            $errors[] = "Entry #{$entryNum}: " . $weightValidation['message'];
            $errorCount++;
            continue;
          }
          $weight = $weightValidation['value'];
          
          $fobValidation = $this->validateWeight($row['fob'] ?? 0, 'FOB');
          if (!$fobValidation['valid']) {
            $errors[] = "Entry #{$entryNum}: " . $fobValidation['message'];
            $errorCount++;
            continue;
          }
          $fob = $fobValidation['value'];
          
          $numberOfBags = isset($row['number_of_bags']) && is_numeric($row['number_of_bags']) 
            ? abs((int)$row['number_of_bags']) 
            : null;
          
          $exportData = [
            'subscriber_id' => (int)$commonData['subscriber_id'],
            'license_id' => (int)$commonData['license_id'],
            'kind' => !empty($commonData['kind']) ? (int)$commonData['kind'] : null,
            'type_of_goods' => !empty($commonData['type_of_goods']) ? (int)$commonData['type_of_goods'] : null,
            'transport_mode' => !empty($commonData['transport_mode']) ? (int)$commonData['transport_mode'] : null,
            'mca_ref' => $mcaRef,
            'currency' => !empty($commonData['currency']) ? (int)$commonData['currency'] : null,
            'buyer' => !empty($commonData['buyer']) ? $this->clean($commonData['buyer']) : null,
            'regime' => (int)$commonData['regime'],
            'types_of_clearance' => (int)$commonData['types_of_clearance'],
            
            'loading_date' => !empty($row['loading_date']) && $this->isValidDate($row['loading_date']) ? $row['loading_date'] : null,
            'bp_date' => !empty($row['bp_date']) && $this->isValidDate($row['bp_date']) ? $row['bp_date'] : null,
            'site_of_loading_id' => !empty($row['site_of_loading_id']) ? (int)$row['site_of_loading_id'] : null,
            'destination' => !empty($row['destination']) ? $this->clean($row['destination']) : null,
            'horse' => !empty($row['horse']) ? $this->clean($row['horse']) : null,
            'trailer_1' => !empty($row['trailer_1']) ? $this->clean($row['trailer_1']) : null,
            'trailer_2' => !empty($row['trailer_2']) ? $this->clean($row['trailer_2']) : null,
            'feet_container' => !empty($row['feet_container']) ? $this->clean($row['feet_container']) : null,
            'wagon_ref' => !empty($row['wagon_ref']) ? $this->clean($row['wagon_ref']) : null,
            'container' => !empty($row['container']) ? $this->clean($row['container']) : null,
            'transporter' => !empty($row['transporter']) ? $this->clean($row['transporter']) : null,
            'exit_point_id' => !empty($row['exit_point_id']) ? (int)$row['exit_point_id'] : null,
            'weight' => $weight,
            'fob' => $fob,
            'number_of_bags' => $numberOfBags,
            'lot_number' => !empty($row['lot_number']) ? $this->clean($row['lot_number']) : null,
            'dgda_seal_no' => !empty($row['dgda_seal_no']) ? $this->clean($row['dgda_seal_no']) : null,
            'number_of_seals' => !empty($row['number_of_seals']) ? (int)$row['number_of_seals'] : null,
            'lmc_id' => !empty($row['lmc_id']) ? $this->clean($row['lmc_id']) : null,
            'lmc_date' => !empty($row['lmc_date']) && $this->isValidDate($row['lmc_date']) ? $row['lmc_date'] : null,
            'ogefrem_inv_ref' => !empty($row['ogefrem_inv_ref']) ? $this->clean($row['ogefrem_inv_ref']) : null,
            'ogefrem_date' => !empty($row['ogefrem_date']) && $this->isValidDate($row['ogefrem_date']) ? $row['ogefrem_date'] : null,
            'invoice' => null,
            'clearing_status' => $defaultClearingStatus,
            'created_by' => $userId,
            'updated_by' => $userId,
            'display' => 'Y'
          ];
          
          $amounts = $this->calculateAllAmounts(
            $weight, 
            $commonData['type_of_goods'],
            $exportData['feet_container']
          );
          $exportData = array_merge($exportData, $amounts);

          $duplicateCheck = $this->checkDuplicateExport($exportData);
          if (!$duplicateCheck['success']) {
            $errors[] = "Entry #{$entryNum}: " . $duplicateCheck['message'];
            $errorCount++;
            continue;
          }

          $sealIds = !empty($row['seal_ids']) && is_array($row['seal_ids']) ? $row['seal_ids'] : [];
          
          if (!empty($sealIds)) {
            $sealValidation = $this->validateAndReserveSeals($sealIds, $mcaRef, $userId);
            if (!$sealValidation['success']) {
              $errors[] = "Entry #{$entryNum}: " . $sealValidation['message'];
              $errorCount++;
              continue;
            }
            $usedSeals[] = ['mca_ref' => $mcaRef, 'seal_no' => $exportData['dgda_seal_no']];
          }

          $insertId = $this->db->insertData('exports_t', $exportData);

          if ($insertId) {
            $successCount++;
            $createdIds[] = $insertId;
          } else {
            $errors[] = "Entry #{$entryNum}: Database insert failed for MCA {$mcaRef}";
            $errorCount++;
          }
        }

        $this->db->commit();

      } catch (Exception $e) {
        $this->db->rollback();
        
        foreach ($usedSeals as $seal) {
          $this->releaseSeals($seal['seal_no'], $userId);
        }
        
        throw $e;
      }

      $message = "Bulk insert completed: {$successCount} exports created successfully";
      if (!empty($errors)) {
        $message .= ". " . count($errors) . " failed.";
      }

      echo json_encode([
        'success' => true,
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        'success_count' => $successCount,
        'error_count' => count($errors),
        'errors' => array_map(function($error) {
          return htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
        }, $errors),
        'created_ids' => $createdIds
      ]);

    } catch (Exception $e) {
      $this->logError('Exception during bulk insert', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred during bulk insert. All changes have been rolled back.']);
    }
  }

  private function getClearingStatusIds()
  {
    try {
      $sql = "SELECT id, clearing_status 
              FROM clearing_status_master_t 
              WHERE display = 'Y'
              ORDER BY id ASC";
      
      $statuses = $this->db->customQuery($sql);
      
      $statusMap = [
        'in_transit_id' => null,
        'in_progress_id' => null,
        'completed_id' => null
      ];
      
      foreach ($statuses as $status) {
        $statusText = strtoupper(trim($status['clearing_status']));
        
        if (strpos($statusText, 'IN TRANSIT') !== false || strpos($statusText, 'TRANSIT') !== false) {
          $statusMap['in_transit_id'] = (int)$status['id'];
        } elseif (strpos($statusText, 'IN PROGRESS') !== false || strpos($statusText, 'PROGRESS') !== false) {
          $statusMap['in_progress_id'] = (int)$status['id'];
        } elseif (strpos($statusText, 'CLEARING COMPLETED') !== false || strpos($statusText, 'COMPLETED') !== false) {
          $statusMap['completed_id'] = (int)$status['id'];
        }
      }
      
      echo json_encode([
        'success' => true,
        'data' => $statusMap
      ]);
      
    } catch (Exception $e) {
      $this->logError('Failed to get clearing status IDs', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load clearing status IDs']);
    }
  }

  private function calculateDocumentStatus($ceecIn, $ceecOut, $minDivIn, $minDivOut)
  {
    if ($ceecIn && $ceecOut && $minDivIn && $minDivOut) {
      return 5;
    } elseif ($ceecIn && $ceecOut) {
      return 3;
    } elseif ($minDivIn && $minDivOut) {
      return 4;
    } elseif ($ceecIn || $minDivIn) {
      return 2;
    }
    return 1;
  }

  private function getVendorPath()
  {
    if (defined('VENDOR_PATH')) {
      return VENDOR_PATH;
    }
    
    $possiblePaths = [
      __DIR__ . '/../../vendor/autoload.php',
      $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
      __DIR__ . '/../../../vendor/autoload.php',
    ];
    
    foreach ($possiblePaths as $path) {
      if (file_exists($path)) {
        return $path;
      }
    }
    
    throw new Exception('PhpSpreadsheet vendor autoload not found. Please run: composer require phpoffice/phpspreadsheet');
  }

private function exportExport()
{
    $exportId = (int) ($_GET['id'] ?? 0);

    if ($exportId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid export ID']);
      return;
    }

    try {
      $vendorPath = $this->getVendorPath();
      require_once $vendorPath;

    $sql = "SELECT 
          e.id,
          e.mca_ref,
          e.license_id,
          c.short_name as client_name,
          c.company_name as client_full_name,
          l.license_number,
          l.weight as license_weight,
          l.fob_declared as license_fob,
          k.kind_name,
          tg.goods_type as type_of_goods,
          tm.transport_mode_name,
          curr.currency_short_name as currency,
          e.buyer,
          rm.regime_name,
          ct.clearance_name,
          e.invoice,
          e.po_ref,
          e.weight,
          e.fob,
          e.horse,
          e.trailer_1,
          e.trailer_2,
          e.feet_container,
          e.wagon_ref,
          e.container,
          e.transporter,
          ls.transit_point_name as loading_site,
          e.destination,
          e.loading_date,
          e.pv_date,
          e.bp_date,
          e.demande_attestation_date,
          e.assay_date,
          e.lot_number,
          e.number_of_seals,
          e.dgda_seal_no,
          e.number_of_bags,
          e.ceec_amount,
          e.cgea_amount,
          e.occ_amount,
          e.lmc_amount,
          e.ogefrem_amount,
          e.archive_reference,
          e.ceec_in_date,
          e.ceec_out_date,
          e.min_div_in_date,
          e.min_div_out_date,
          e.cgea_doc_ref,
          e.segues_rcv_ref,
          e.segues_payment_date,
          ds.document_status,  -- ✅ ADDED
          e.customs_clearing_code,
          e.dgda_in_date,
          e.declaration_reference,
          e.liquidation_reference,
          e.liquidation_date,
          e.liquidation_paid_by,
          e.liquidation_amount,
          e.quittance_reference,
          e.quittance_date,
          e.dgda_out_date,
          e.gov_docs_in_date,
          e.gov_docs_out_date,
          e.dispatch_deliver_date,
          e.kanyaka_arrival_date,
          e.kanyaka_departure_date,
          e.border_arrival_date,
          e.exit_drc_date,
          ep.transit_point_name as exit_point,
          e.end_of_formalities_date,
          ts.truck_status,
          cs.clearing_status,
          e.lmc_id,
          e.ogefrem_inv_ref,
          e.audited_date,
          e.archived_date,
          e.lmc_date, 
          e.ogefrem_date,
          e.remarks
        FROM exports_t e
        LEFT JOIN clients_t c ON e.subscriber_id = c.id
        LEFT JOIN licenses_t l ON e.license_id = l.id
        LEFT JOIN kind_master_t k ON e.kind = k.id
        LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id
        LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id
        LEFT JOIN currency_master_t curr ON e.currency = curr.id
        LEFT JOIN regime_master_t rm ON e.regime = rm.id
        LEFT JOIN clearance_master_t ct ON e.types_of_clearance = ct.id
        LEFT JOIN transit_point_master_t ls ON e.site_of_loading_id = ls.id
        LEFT JOIN transit_point_master_t ep ON e.exit_point_id = ep.id
        LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id
        LEFT JOIN document_status_master_t ds ON e.document_status = ds.id  -- ✅ ADDED
        LEFT JOIN truck_status_master_t ts ON e.truck_status = ts.id
        WHERE e.id = :id AND e.display = 'Y'";

      $result = $this->db->customQuery($sql, [':id' => $exportId]);
      
      if (empty($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Export not found']);
        return;
      }

      $data = $result[0];
      
      $usedSql = "SELECT COALESCE(SUM(weight), 0) as used_weight, COALESCE(SUM(fob), 0) as used_fob
                  FROM exports_t 
                  WHERE license_id = :license_id 
                  AND id < :current_id
                  AND display = 'Y'";
      $usedResult = $this->db->customQuery($usedSql, [
        ':license_id' => $data['license_id'],
        ':current_id' => $data['id']
      ]);
      $usedWeightBefore = floatval($usedResult[0]['used_weight'] ?? 0);
      $usedFOBBefore = floatval($usedResult[0]['used_fob'] ?? 0);
      
      $licenseWeight = floatval($data['license_weight'] ?? 0);
      $licenseFOB = floatval($data['license_fob'] ?? 0);
      
      $currentExportWeight = floatval($data['weight'] ?? 0);
      $currentExportFOB = floatval($data['fob'] ?? 0);
      
      $remainingWeight = $licenseWeight - ($usedWeightBefore + $currentExportWeight);
      $remainingFOB = $licenseFOB - ($usedFOBBefore + $currentExportFOB);
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Export Details');

    $headers = [
  'MCA Reference', 'Client Code', 'Client Full Name', 'License Number', 'Kind', 'Type of Goods',
  'Transport Mode', 'Currency', 'Buyer', 'Regime', 'Clearance Type', 'Invoice', 'PO Reference',
  'License Weight (MT)', 'Weight (MT)', 'FOB', 'Remaining Weight (MT)', 'Remaining FOB',
  'Horse', 'Trailer 1', 'Trailer 2', 'Feet Container', 'Wagon Reference',
  'Container', 'Transporter', 'Site of Loading', 'Destination', 'Loading Date',
  'PV Date', 'BP Date', 'Demande d\'Attestation', 'Assay Date', 'Lot Number', 'Number of Seals',
  'DGDA Seal No', 'Number of Bags', 
  'CEEC Amount (USD)', 'CGEA Amount (USD)', 'OCC Amount (USD)', 'LMC Amount (USD)', 'OGEFREM Amount (USD)',
  'Archive Reference', 'CEEC In Date', 'CEEC Out Date',
  'Min Div In Date', 'Min Div Out Date', 'CGEA Doc Ref', 'SEGUES RCV Ref', 'SEGUES Payment Date',
  'Document Status',  // ✅ ADDED
  'Customs Clearing Code', 'DGDA In Date', 'Declaration Reference',
  'Liquidation Reference', 'Liquidation Date', 'Liquidation Paid By', 'Liquidation Amount',
  'Quittance Reference', 'Quittance Date', 'DGDA Out Date', 'Gov Docs In Date', 'Gov Docs Out Date',
  'Dispatch/Deliver Date', 'Kanyaka Arrival Date', 'Kanyaka Departure Date', 'Border Arrival Date',
  'Exit DRC Date', 'Exit Point', 'End of Formalities Date', 'Truck Status', 'Clearing Status',
  'LMC ID', 'OGEFREM Inv.Ref.', 'Audited Date', 'Archived Date', 'LMC Date', 'OGEFREM Date', 'Remarks'
];

$values = [
  $data['mca_ref'] ?? '',
  $data['client_name'] ?? '',
  $data['client_full_name'] ?? '',
  $data['license_number'] ?? '',
  $data['kind_name'] ?? '',
  $data['type_of_goods'] ?? '',
  $data['transport_mode_name'] ?? '',
  $data['currency'] ?? '',
  $data['buyer'] ?? '',
  $data['regime_name'] ?? '',
  $data['clearance_name'] ?? '',
  $data['invoice'] ?? '',
  $data['po_ref'] ?? '',
  $licenseWeight ? number_format($licenseWeight, 3, '.', '') : '0.000', // ✅ 3 DECIMALS
  $data['weight'] ? number_format((float)$data['weight'], 3, '.', '') : '0.000', // ✅ 3 DECIMALS
  $data['fob'] ? number_format((float)$data['fob'], 2, '.', '') : '0.00',
  number_format($remainingWeight, 3, '.', ''), // ✅ 3 DECIMALS
  number_format($remainingFOB, 2, '.', ''),
  $data['horse'] ?? '',
  $data['trailer_1'] ?? '',
  $data['trailer_2'] ?? '',
  $data['feet_container'] ?? '',
  $data['wagon_ref'] ?? '',
  $data['container'] ?? '',
  $data['transporter'] ?? '',
  $data['loading_site'] ?? '',
  $data['destination'] ?? '',
  $data['loading_date'] ? date('d-m-Y', strtotime($data['loading_date'])) : '',
  $data['pv_date'] ? date('d-m-Y', strtotime($data['pv_date'])) : '',
  $data['bp_date'] ? date('d-m-Y', strtotime($data['bp_date'])) : '',
  $data['demande_attestation_date'] ? date('d-m-Y', strtotime($data['demande_attestation_date'])) : '',
  $data['assay_date'] ? date('d-m-Y', strtotime($data['assay_date'])) : '',
  $data['lot_number'] ?? '',
  $data['number_of_seals'] ?? '',
  $data['dgda_seal_no'] ?? '',
  $data['number_of_bags'] ?? '',
  $data['ceec_amount'] ? number_format((float)$data['ceec_amount'], 2, '.', '') : '',
  $data['cgea_amount'] ? number_format((float)$data['cgea_amount'], 2, '.', '') : '',
  $data['occ_amount'] ? number_format((float)$data['occ_amount'], 2, '.', '') : '250.00',
  $data['lmc_amount'] ? number_format((float)$data['lmc_amount'], 2, '.', '') : '',
  $data['ogefrem_amount'] ? number_format((float)$data['ogefrem_amount'], 2, '.', '') : '',
  $data['archive_reference'] ?? '',
  $data['ceec_in_date'] ? date('d-m-Y', strtotime($data['ceec_in_date'])) : '',
  $data['ceec_out_date'] ? date('d-m-Y', strtotime($data['ceec_out_date'])) : '',
  $data['min_div_in_date'] ? date('d-m-Y', strtotime($data['min_div_in_date'])) : '',
  $data['min_div_out_date'] ? date('d-m-Y', strtotime($data['min_div_out_date'])) : '',
  $data['cgea_doc_ref'] ?? '',
  $data['segues_rcv_ref'] ?? '',
  $data['segues_payment_date'] ? date('d-m-Y', strtotime($data['segues_payment_date'])) : '',
  $data['document_status'] ?? '', // ✅ ADDED
  $data['customs_clearing_code'] ?? '',
  $data['dgda_in_date'] ? date('d-m-Y', strtotime($data['dgda_in_date'])) : '',
  $data['declaration_reference'] ?? '',
  $data['liquidation_reference'] ?? '',
  $data['liquidation_date'] ? date('d-m-Y', strtotime($data['liquidation_date'])) : '',
  $data['liquidation_paid_by'] ?? '',
  $data['liquidation_amount'] ? number_format((float)$data['liquidation_amount'], 2, '.', '') : '',
  $data['quittance_reference'] ?? '',
  $data['quittance_date'] ? date('d-m-Y', strtotime($data['quittance_date'])) : '',
  $data['dgda_out_date'] ? date('d-m-Y', strtotime($data['dgda_out_date'])) : '',
  $data['gov_docs_in_date'] ? date('d-m-Y', strtotime($data['gov_docs_in_date'])) : '',
  $data['gov_docs_out_date'] ? date('d-m-Y', strtotime($data['gov_docs_out_date'])) : '',
  $data['dispatch_deliver_date'] ? date('d-m-Y', strtotime($data['dispatch_deliver_date'])) : '',
  $data['kanyaka_arrival_date'] ? date('d-m-Y', strtotime($data['kanyaka_arrival_date'])) : '',
  $data['kanyaka_departure_date'] ? date('d-m-Y', strtotime($data['kanyaka_departure_date'])) : '',
  $data['border_arrival_date'] ? date('d-m-Y', strtotime($data['border_arrival_date'])) : '',
  $data['exit_drc_date'] ? date('d-m-Y', strtotime($data['exit_drc_date'])) : '',
  $data['exit_point'] ?? '',
  $data['end_of_formalities_date'] ? date('d-m-Y', strtotime($data['end_of_formalities_date'])) : '',
  $data['truck_status'] ?? '',
  $data['clearing_status'] ?? '',
  $data['lmc_id'] ?? '',
  $data['ogefrem_inv_ref'] ?? '',
  $data['audited_date'] ? date('d-m-Y', strtotime($data['audited_date'])) : '',
  $data['archived_date'] ? date('d-m-Y', strtotime($data['archived_date'])) : '',
  $data['lmc_date'] ? date('d-m-Y', strtotime($data['lmc_date'])) : '',
  $data['ogefrem_date'] ? date('d-m-Y', strtotime($data['ogefrem_date'])) : '',
  ''
];

      if (!empty($data['remarks'])) {
        try {
          $remarksArray = json_decode($data['remarks'], true);
          if (is_array($remarksArray)) {
            $remarksLines = [];
            foreach ($remarksArray as $remark) {
              $date = isset($remark['date']) ? date('d-m-Y', strtotime($remark['date'])) : '';
              $text = isset($remark['text']) ? $remark['text'] : '';
              if ($date || $text) {
                $remarksLines[] = ($date ? "[$date] " : '') . $text;
              }
            }
            $values[count($values) - 1] = implode("\n", $remarksLines);
          }
        } catch (Exception $e) {
          $values[count($values) - 1] = $data['remarks'];
        }
      }

      $sheet->fromArray([$headers], null, 'A1');
      $sheet->fromArray([$values], null, 'A2');

      $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
      ];
      
      $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
      $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

      $valueStyle = [
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'BDC3C7']]]
      ];
      $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray($valueStyle);

      foreach (range(1, count($headers)) as $colIndex) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($column)->setWidth(18);
      }

      $sheet->getRowDimension(1)->setRowHeight(30);
      $sheet->getRowDimension(2)->setRowHeight(25);

      $today = date('d-m-Y');
      $mcaRef = $data['mca_ref'] ?? 'Export';
      $filename = 'Export_' . str_replace(['/', '\\', '-'], '_', $mcaRef) . '_' . $today . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      header('Pragma: public');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      $this->logError('Export Export Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
}

private function exportAllExports()
{
    ini_set('memory_limit', '512M');
    set_time_limit(300);
    gc_enable();
    
    try {
      $vendorPath = $this->getVendorPath();
      require_once $vendorPath;

      $filters = [];
      $params = [];
      
      // ✅ Collect selected client IDs
      $selectedClientIds = [];
      if (!empty($_GET['subscriber_id'])) {
        if (is_array($_GET['subscriber_id'])) {
          $selectedClientIds = array_map('intval', array_filter($_GET['subscriber_id']));
        } else {
          $selectedClientIds = [(int)$_GET['subscriber_id']];
        }
        
        if (empty($selectedClientIds)) {
          header('Content-Type: application/json');
          echo json_encode(['success' => false, 'message' => 'No clients selected']);
          return;
        }
      }
      
      // ✅ If multiple clients selected, generate files and return download URLs
      if (count($selectedClientIds) > 1) {
        $this->generateMultipleClientExports($selectedClientIds, $_GET);
        return;
      }
      
      // ✅ Single client - direct download (existing behavior)
      if (!empty($selectedClientIds)) {
        $filters[] = "e.subscriber_id = " . $selectedClientIds[0];
      }
      
      if (!empty($_GET['license_id'])) {
        $filters[] = "e.license_id = :license_id";
        $params[':license_id'] = (int)$_GET['license_id'];
      }
      
      if (!empty($_GET['transport_mode'])) {
        $filters[] = "e.transport_mode = :transport_mode";
        $params[':transport_mode'] = (int)$_GET['transport_mode'];
      }
      
      if (!empty($_GET['start_date']) && $this->isValidDate($_GET['start_date'])) {
        $filters[] = "e.loading_date >= :start_date";
        $params[':start_date'] = $_GET['start_date'];
      }
      
      if (!empty($_GET['end_date']) && $this->isValidDate($_GET['end_date'])) {
        $filters[] = "e.loading_date <= :end_date";
        $params[':end_date'] = $_GET['end_date'];
      }
      
      $whereClause = "WHERE e.display = 'Y'";
      if (!empty($filters)) {
        $whereClause .= " AND " . implode(" AND ", $filters);
      }

      $countSql = "SELECT COUNT(*) as total FROM exports_t e {$whereClause}";
      $countResult = $this->db->customQuery($countSql, $params);
      $totalRecords = (int)($countResult[0]['total'] ?? 0);
      
      if ($totalRecords === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No exports found']);
        return;
      }
      
      if ($totalRecords > 50000) {
        header('Content-Type: application/json');
        echo json_encode([
          'success' => false, 
          'message' => "Too many records to export ({$totalRecords}). Please use filters to reduce to under 50,000 records."
        ]);
        return;
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $spreadsheet->removeSheetByIndex(0);

      $chunkSize = 5000;
      $offset = 0;
      
      $roadExports = [];
      $airExports = [];
      $railExports = [];
      
      while ($offset < $totalRecords) {
       $sql = "SELECT 
          e.id, e.subscriber_id, e.license_id, e.kind, e.type_of_goods,
          e.transport_mode, e.mca_ref, e.currency, e.buyer, e.regime,
          e.types_of_clearance, e.invoice, e.po_ref, e.weight, e.fob,
          e.horse, e.trailer_1, e.trailer_2, e.feet_container, e.wagon_ref,
          e.container, e.transporter, e.site_of_loading_id, e.destination,
          e.loading_date, e.pv_date, e.bp_date, e.demande_attestation_date,
          e.assay_date, e.lot_number, e.number_of_seals, e.dgda_seal_no,
          e.number_of_bags, e.ceec_amount, e.cgea_amount, e.occ_amount,
          e.lmc_amount, e.ogefrem_amount, e.archive_reference,
          e.ceec_in_date, e.ceec_out_date, e.min_div_in_date, e.min_div_out_date,
          e.cgea_doc_ref, e.segues_rcv_ref, e.segues_payment_date,
          e.document_status, e.customs_clearing_code, e.dgda_in_date,
          e.declaration_reference, e.liquidation_reference, e.liquidation_date,
          e.liquidation_paid_by, e.liquidation_amount, e.quittance_reference,
          e.quittance_date, e.dgda_out_date, e.gov_docs_in_date,
          e.gov_docs_out_date, e.dispatch_deliver_date, e.kanyaka_arrival_date,
          e.kanyaka_departure_date, e.border_arrival_date, e.exit_drc_date,
          e.exit_point_id, e.end_of_formalities_date, e.truck_status,
          e.clearing_status, e.lmc_id, e.ogefrem_inv_ref, e.audited_date,
          e.archived_date,e.ogefrem_date,e.lmc_date,e.remarks, e.display,
          c.short_name as client_name,
          l.license_number, l.weight as license_weight, l.fob_declared as license_fob,
          k.kind_name,
          tg.goods_type as type_of_goods_name,
          tm.transport_mode_name,
          curr.currency_short_name as currency_name,
          rm.regime_name,
          ct.clearance_name,
          ls.transit_point_name as loading_site,
          ep.transit_point_name as exit_point,
          cs.clearing_status as clearing_status_name,
          ds.document_status as document_status_name, -- ✅ ADDED
          ts.truck_status as truck_status_name,
          fs.feet_container_size
        FROM exports_t e
        LEFT JOIN clients_t c ON e.subscriber_id = c.id
        LEFT JOIN licenses_t l ON e.license_id = l.id
        LEFT JOIN kind_master_t k ON e.kind = k.id
        LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id
        LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id
        LEFT JOIN currency_master_t curr ON e.currency = curr.id
        LEFT JOIN regime_master_t rm ON e.regime = rm.id
        LEFT JOIN clearance_master_t ct ON e.types_of_clearance = ct.id
        LEFT JOIN transit_point_master_t ls ON e.site_of_loading_id = ls.id
        LEFT JOIN transit_point_master_t ep ON e.exit_point_id = ep.id
        LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id
        LEFT JOIN document_status_master_t ds ON e.document_status = ds.id
        LEFT JOIN truck_status_master_t ts ON e.truck_status = ts.id
        LEFT JOIN feet_container_master_t fs ON e.feet_container = fs.id
        {$whereClause}
        ORDER BY e.id ASC
        LIMIT {$chunkSize} OFFSET {$offset}";
        $chunk = $this->db->customQuery($sql, $params);
        
        if (empty($chunk)) {
          break;
        }

        foreach ($chunk as $key => $exp) {
          $usedSql = "SELECT COALESCE(SUM(weight), 0) as used_weight, COALESCE(SUM(fob), 0) as used_fob
                      FROM exports_t 
                      WHERE license_id = :license_id 
                      AND id < :current_id
                      AND display = 'Y'";
          $usedResult = $this->db->customQuery($usedSql, [
            ':license_id' => $exp['license_id'],
            ':current_id' => $exp['id']
          ]);
          
          $usedWeightBefore = floatval($usedResult[0]['used_weight'] ?? 0);
          $usedFOBBefore = floatval($usedResult[0]['used_fob'] ?? 0);
          
          $licenseWeight = floatval($exp['license_weight'] ?? 0);
          $licenseFOB = floatval($exp['license_fob'] ?? 0);
          
          $currentExportWeight = floatval($exp['weight'] ?? 0);
          $currentExportFOB = floatval($exp['fob'] ?? 0);
          
          $chunk[$key]['remaining_weight'] = $licenseWeight - ($usedWeightBefore + $currentExportWeight);
          $chunk[$key]['remaining_fob'] = $licenseFOB - ($usedFOBBefore + $currentExportFOB);
        }

        foreach ($chunk as $exp) {
          $transportModeId = (int)($exp['transport_mode'] ?? 0);
          switch ($transportModeId) {
            case 1:
              $roadExports[] = $exp;
              break;
            case 2:
              $airExports[] = $exp;
              break;
            case 3:
              $railExports[] = $exp;
              break;
            default:
              $roadExports[] = $exp;
          }
        }
        
        $offset += $chunkSize;
        
        unset($chunk);
        gc_collect_cycles();
      }

      if (!empty($roadExports)) {
        $roadSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Road');
        $spreadsheet->addSheet($roadSheet, 0);
        $this->populateExportSheet($roadSheet, $roadExports, 'Road');
        unset($roadExports);
        gc_collect_cycles();
      }

      if (!empty($airExports)) {
        $airSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Air');
        $spreadsheet->addSheet($airSheet, 1);
        $this->populateExportSheet($airSheet, $airExports, 'Air');
        unset($airExports);
        gc_collect_cycles();
      }

      if (!empty($railExports)) {
        $railSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Rail');
        $spreadsheet->addSheet($railSheet, 2);
        $this->populateExportSheet($railSheet, $railExports, 'Rail');
        unset($railExports);
        gc_collect_cycles();
      }

      $spreadsheet->setActiveSheetIndex(0);

      // Get client name for filename
      $clientNameForFile = 'All_Clients';

      if (!empty($selectedClientIds) && count($selectedClientIds) === 1) {
        $client_id = $selectedClientIds[0];
        
        $clientsql = "SELECT short_name
                      FROM clients_t 
                      WHERE id = :client_id 
                      AND display = 'Y'";
        
        $clientResult = $this->db->customQuery($clientsql, [':client_id' => $client_id]);
        
        if (!empty($clientResult) && isset($clientResult[0]['short_name'])) {
          $clientNameForFile = $this->sanitizeInput($clientResult[0]['short_name']);
          $clientNameForFile = preg_replace('/[^A-Za-z0-9_\-]/', '_', $clientNameForFile);
        }
      }

      $filename = $clientNameForFile . '_Export_' . date('d_m_Y_H_i_s') . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      header('Pragma: public');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      $this->logError('Export All Error', ['error' => $e->getMessage()]);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
}

// ✅ NEW METHOD: Generate multiple client exports
private function generateMultipleClientExports($clientIds, $filters)
{
    try {
      $vendorPath = $this->getVendorPath();
      require_once $vendorPath;

      // Get client names
      $clientPlaceholders = implode(',', $clientIds);
      $clientSql = "SELECT id, short_name FROM clients_t WHERE id IN ($clientPlaceholders) AND display = 'Y' ORDER BY short_name ASC";
      $clients = $this->db->customQuery($clientSql);
      
      $downloadUrls = [];
      
      foreach ($clients as $client) {
        $clientId = (int)$client['id'];
        $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->sanitizeInput($client['short_name']));
        
        // Build query for this specific client
        $whereFilters = ["e.subscriber_id = $clientId", "e.display = 'Y'"];
        $params = [];
        
        if (!empty($filters['license_id'])) {
          $whereFilters[] = "e.license_id = :license_id";
          $params[':license_id'] = (int)$filters['license_id'];
        }
        
        if (!empty($filters['transport_mode'])) {
          $whereFilters[] = "e.transport_mode = :transport_mode";
          $params[':transport_mode'] = (int)$filters['transport_mode'];
        }
        
        if (!empty($filters['start_date']) && $this->isValidDate($filters['start_date'])) {
          $whereFilters[] = "e.loading_date >= :start_date";
          $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date']) && $this->isValidDate($filters['end_date'])) {
          $whereFilters[] = "e.loading_date <= :end_date";
          $params[':end_date'] = $filters['end_date'];
        }
        
        $whereClause = "WHERE " . implode(" AND ", $whereFilters);
        
        // Check if client has data
        $countSql = "SELECT COUNT(*) as total FROM exports_t e {$whereClause}";
        $countResult = $this->db->customQuery($countSql, $params);
        $totalRecords = (int)($countResult[0]['total'] ?? 0);
        
        if ($totalRecords === 0) {
          continue; // Skip clients with no data
        }
        
        // Create download URL for this client
        $queryParams = http_build_query([
          'subscriber_id' => $clientId,
          'license_id' => $filters['license_id'] ?? '',
          'transport_mode' => $filters['transport_mode'] ?? '',
          'start_date' => $filters['start_date'] ?? '',
          'end_date' => $filters['end_date'] ?? ''
        ]);
        
        $downloadUrls[] = [
          'client_id' => $clientId,
          'client_name' => $client['short_name'],
          'filename' => $clientName . '_Export_' . date('d_m_Y_H_i_s') . '.xlsx',
          'url' => APP_URL . '/export/crudData/exportAll?' . $queryParams,
          'record_count' => $totalRecords
        ];
      }
      
      if (empty($downloadUrls)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No data found for selected clients']);
        return;
      }
      
      // Return JSON with download URLs
      header('Content-Type: application/json');
      echo json_encode([
        'success' => true,
        'multiple_files' => true,
        'files' => $downloadUrls,
        'total_clients' => count($downloadUrls)
      ]);
      
    } catch (Exception $e) {
      $this->logError('Generate Multiple Exports Error', ['error' => $e->getMessage()]);
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Failed to generate exports: ' . $e->getMessage()]);
    }
}

 private function populateExportSheet($sheet, $exports, $transportType)
{
    $headers = [
      'MCA Reference', 'Client Name', 'License Number', 'Kind', 'Type of Goods', 'Transport Mode',
      'Currency', 'Buyer', 'Regime', 'Clearance Type', 'Invoice', 'PO Reference',
      'License Weight (MT)', 'Weight (MT)', 'FOB', 'Remaining Weight (MT)', 'Remaining FOB'
    ];
    
    if ($transportType === 'Road') {
      $headers[] = 'Horse';
      $headers[] = 'Trailer 1';
      $headers[] = 'Trailer 2';
      $headers[] = 'Feet Container';
    } elseif ($transportType === 'Air' || $transportType === 'Rail') {
      $headers[] = 'Wagon Reference';
      $headers[] = 'Feet Container';
    }
    
    $headers = array_merge($headers, [
      'Container', 'Transporter',
      'Site of Loading', 'Destination', 'Loading Date', 'PV Date', 'BP Date',
      'Demande d\'Attestation', 'Assay Date', 'Lot Number', 
      'DGDA Seal No', 'Number of Seals', 'Number of Bags',
      'CEEC Amount (USD)', 'CGEA Amount (USD)', 'OCC Amount (USD)', 'LMC Amount (USD)', 'OGEFREM Amount (USD)',
      'Archive Reference',
      'CEEC In Date', 'CEEC Out Date', 'Min Div In Date', 'Min Div Out Date',
      'CGEA Doc Ref', 'SEGUES RCV Ref', 'SEGUES Payment Date',
      'Customs Clearing Code', 'DGDA In Date',
      'Declaration Reference', 'Liquidation Reference', 'Liquidation Date',
      'Liquidation Paid By', 'Liquidation Amount',
      'Quittance Reference', 'Quittance Date', 'DGDA Out Date',
      'Gov Docs In Date', 'Gov Docs Out Date',
      'Dispatch/Deliver Date', 'Kanyaka Arrival Date', 'Kanyaka Departure Date',
      'Border Arrival Date', 'Exit DRC Date', 'Exit Point',
      'End of Formalities Date', 'Truck Status', 'Clearing Status',
      'LMC ID', 'OGEFREM Inv.Ref.', 'LMC Date', 'OGEFREM Date',
      'Audited Date', 'Archived Date', 'Remarks'
    ]);
    
    $sheet->fromArray([$headers], null, 'A1');
    
    $rowNum = 2;
    foreach ($exports as $exp) {
      $row = [
        $exp['mca_ref'] ?? '',
        $exp['client_name'] ?? '',
        $exp['license_number'] ?? '',
        $exp['kind_name'] ?? '',
        $exp['type_of_goods_name'] ?? '',
        $exp['transport_mode_name'] ?? '',
        $exp['currency_name'] ?? '',
        $exp['buyer'] ?? '',
        $exp['regime_name'] ?? '',
        $exp['clearance_name'] ?? '',
        $exp['invoice'] ?? '',
        $exp['po_ref'] ?? '',
        $exp['license_weight'] ? number_format((float)$exp['license_weight'], 3, '.', '') : '0.000', // ✅ 3 DECIMALS
        $exp['weight'] ? number_format((float)$exp['weight'], 3, '.', '') : '0.000', // ✅ 3 DECIMALS
        $exp['fob'] ? number_format((float)$exp['fob'], 2, '.', '') : '0.00',
        number_format((float)($exp['remaining_weight'] ?? 0), 3, '.', ''), // ✅ 3 DECIMALS
        number_format((float)($exp['remaining_fob'] ?? 0), 2, '.', '')
      ];
      
      if ($transportType === 'Road') {
        $row[] = $exp['horse'] ?? '';
        $row[] = $exp['trailer_1'] ?? '';
        $row[] = $exp['trailer_2'] ?? '';
        $row[] = $exp['feet_container_size'] ?? '';
      } elseif ($transportType === 'Air' || $transportType === 'Rail') {
        $row[] = $exp['wagon_ref'] ?? '';
        $row[] = $exp['feet_container_size'] ?? '';
      }
      
      $row = array_merge($row, [
        $exp['container'] ?? '',
        $exp['transporter'] ?? '',
        $exp['loading_site'] ?? '',
        $exp['destination'] ?? '',
        $exp['loading_date'] ? date('d-m-Y', strtotime($exp['loading_date'])) : '',
        $exp['pv_date'] ? date('d-m-Y', strtotime($exp['pv_date'])) : '',
        $exp['bp_date'] ? date('d-m-Y', strtotime($exp['bp_date'])) : '',
        $exp['demande_attestation_date'] ? date('d-m-Y', strtotime($exp['demande_attestation_date'])) : '',
        $exp['assay_date'] ? date('d-m-Y', strtotime($exp['assay_date'])) : '',
        $exp['lot_number'] ?? '',
        $exp['dgda_seal_no'] ?? '',
        $exp['number_of_seals'] ?? '',
        $exp['number_of_bags'] ?? '',
        $exp['ceec_amount'] ? number_format((float)$exp['ceec_amount'], 2, '.', '') : '',
        $exp['cgea_amount'] ? number_format((float)$exp['cgea_amount'], 2, '.', '') : '',
        $exp['occ_amount'] ? number_format((float)$exp['occ_amount'], 2, '.', '') : '250.00',
        $exp['lmc_amount'] ? number_format((float)$exp['lmc_amount'], 2, '.', '') : '',
        $exp['ogefrem_amount'] ? number_format((float)$exp['ogefrem_amount'], 2, '.', '') : '',
        $exp['archive_reference'] ?? '',
        $exp['ceec_in_date'] ? date('d-m-Y', strtotime($exp['ceec_in_date'])) : '',
        $exp['ceec_out_date'] ? date('d-m-Y', strtotime($exp['ceec_out_date'])) : '',
        $exp['min_div_in_date'] ? date('d-m-Y', strtotime($exp['min_div_in_date'])) : '',
        $exp['min_div_out_date'] ? date('d-m-Y', strtotime($exp['min_div_out_date'])) : '',
        $exp['cgea_doc_ref'] ?? '',
        $exp['segues_rcv_ref'] ?? '',
        $exp['segues_payment_date'] ? date('d-m-Y', strtotime($exp['segues_payment_date'])) : '',
        $exp['customs_clearing_code'] ?? '',
        $exp['dgda_in_date'] ? date('d-m-Y', strtotime($exp['dgda_in_date'])) : '',
        $exp['declaration_reference'] ?? '',
        $exp['liquidation_reference'] ?? '',
        $exp['liquidation_date'] ? date('d-m-Y', strtotime($exp['liquidation_date'])) : '',
        $exp['liquidation_paid_by'] ?? '',
        $exp['liquidation_amount'] ? number_format((float)$exp['liquidation_amount'], 2, '.', '') : '',
        $exp['quittance_reference'] ?? '',
        $exp['quittance_date'] ? date('d-m-Y', strtotime($exp['quittance_date'])) : '',
        $exp['dgda_out_date'] ? date('d-m-Y', strtotime($exp['dgda_out_date'])) : '',
        $exp['gov_docs_in_date'] ? date('d-m-Y', strtotime($exp['gov_docs_in_date'])) : '',
        $exp['gov_docs_out_date'] ? date('d-m-Y', strtotime($exp['gov_docs_out_date'])) : '',
        $exp['dispatch_deliver_date'] ? date('d-m-Y', strtotime($exp['dispatch_deliver_date'])) : '',
        $exp['kanyaka_arrival_date'] ? date('d-m-Y', strtotime($exp['kanyaka_arrival_date'])) : '',
        $exp['kanyaka_departure_date'] ? date('d-m-Y', strtotime($exp['kanyaka_departure_date'])) : '',
        $exp['border_arrival_date'] ? date('d-m-Y', strtotime($exp['border_arrival_date'])) : '',
        $exp['exit_drc_date'] ? date('d-m-Y', strtotime($exp['exit_drc_date'])) : '',
        $exp['exit_point'] ?? '',
        $exp['end_of_formalities_date'] ? date('d-m-Y', strtotime($exp['end_of_formalities_date'])) : '',
        $exp['truck_status_name'] ?? '',
        $exp['clearing_status_name'] ?? '',
        $exp['lmc_id'] ?? '',
        $exp['ogefrem_inv_ref'] ?? '',
        $exp['lmc_date'] ? date('d-m-Y', strtotime($exp['lmc_date'])) : '',
        $exp['ogefrem_date'] ? date('d-m-Y', strtotime($exp['ogefrem_date'])) : '',
        $exp['audited_date'] ? date('d-m-Y', strtotime($exp['audited_date'])) : '',
        $exp['archived_date'] ? date('d-m-Y', strtotime($exp['archived_date'])) : '',
        ''
      ]);
      
      if (!empty($exp['remarks'])) {
        try {
          $remarksArray = json_decode($exp['remarks'], true);
          if (is_array($remarksArray)) {
            $remarksLines = [];
            foreach ($remarksArray as $remark) {
              $date = isset($remark['date']) ? date('d-m-Y', strtotime($remark['date'])) : '';
              $text = isset($remark['text']) ? $remark['text'] : '';
              if ($date || $text) {
                $remarksLines[] = ($date ? "[$date] " : '') . $text;
              }
            }
            $row[count($row) - 1] = implode("\n", $remarksLines);
          }
        } catch (Exception $e) {
          $row[count($row) - 1] = $exp['remarks'];
        }
      }
      
      $sheet->fromArray([$row], null, 'A' . $rowNum);
      $rowNum++;
    }

    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '667eea']],
      'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
      'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
    ];
    
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

    $sheet->freezePane('B2');

    $sheet->getColumnDimension('A')->setWidth(25);
    foreach (range(2, count($headers)) as $colIndex) {
      $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
      $sheet->getColumnDimension($column)->setAutoSize(true);
    }
}

 private function getBulkUpdateData()
{
  try {
    $filters = isset($_GET['filters']) ? $_GET['filters'] : [];

    if (!is_array($filters)) {
      echo json_encode(['success' => false, 'message' => 'Invalid filters']);
      return;
    }

    $filters = array_filter($filters, function($filter) {
      return in_array($filter, $this->allowedFilters);
    });

    if (empty($filters)) {
      echo json_encode(['success' => false, 'message' => 'Please select a valid filter first']);
      return;
    }

    $baseQuery = "FROM exports_t e
                  LEFT JOIN clients_t c ON e.subscriber_id = c.id
                  LEFT JOIN licenses_t l ON e.license_id = l.id
                  LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id
                  WHERE e.display = 'Y'";

    $filterClauses = [];
    $params = [];
    
    foreach ($filters as $filter) {
      switch ($filter) {
        case 'completed':
          $filterClauses[] = "cs.clearing_status = :status_completed";
          $params[':status_completed'] = 'CLEARING COMPLETED';
          break;
        case 'in_progress':
          $filterClauses[] = "cs.clearing_status = :status_in_progress";
          $params[':status_in_progress'] = 'IN PROGRESS';
          break;
        case 'in_transit':
          $filterClauses[] = "cs.clearing_status = :status_in_transit";
          $params[':status_in_transit'] = 'IN TRANSIT';
          break;
        case 'ceec_pending':
          $filterClauses[] = "(e.ceec_in_date IS NULL OR e.ceec_out_date IS NULL)";
          break;
        case 'min_div_pending':
          $filterClauses[] = "(e.min_div_in_date IS NULL OR e.min_div_out_date IS NULL)";
          break;
        case 'gov_docs_pending':
          $filterClauses[] = "(e.gov_docs_in_date IS NULL OR e.gov_docs_out_date IS NULL)";
          break;
        case 'audited_pending':
          $filterClauses[] = "e.audited_date IS NULL";
          break;
        case 'archived_pending':
          $filterClauses[] = "e.archived_date IS NULL";
          break;
        case 'lmc_pending':
          $filterClauses[] = "e.lmc_date IS NULL";
          break;
        case 'ogefrem_pending':
          $filterClauses[] = "e.ogefrem_date IS NULL";
          break;
        case 'dgda_in_pending':
          $filterClauses[] = "e.dgda_in_date IS NULL";
          break;
        case 'liquidation_pending':
          $filterClauses[] = "e.liquidation_date IS NULL";
          break;
        case 'quittance_pending':
          $filterClauses[] = "e.quittance_date IS NULL";
          break;
        case 'dispatch_pending':
          $filterClauses[] = "e.dispatch_deliver_date IS NULL";
          break;
        case 'lmc_id_pending': // ✅ NEW
          $filterClauses[] = "e.lmc_id IS NULL";
          break;
        case 'lmc_date_pending': // ✅ NEW
          $filterClauses[] = "e.lmc_date IS NULL OR e.lmc_date = ''";
          break;
        case 'ogefrem_ref_pending': // ✅ NEW
          $filterClauses[] = "e.ogefrem_inv_ref IS NULL";
          break;
        case 'ogefrem_date_pending': // ✅ NEW
          $filterClauses[] = "e.ogefrem_date IS NULL OR e.ogefrem_date = ''";
          break;
      }
    }
    
    $filterCondition = "";
    if (!empty($filterClauses)) {
      $filterCondition = " AND (" . implode(' OR ', $filterClauses) . ")";
    }

    $sql = "SELECT 
              e.id,
              e.mca_ref,
              e.subscriber_id,
              e.loading_date,
              e.ceec_in_date,
              e.ceec_out_date,
              e.min_div_in_date,
              e.min_div_out_date,
              e.gov_docs_in_date,
              e.gov_docs_out_date,
              e.dgda_in_date,
              e.liquidation_date,
              e.quittance_date,
              e.dispatch_deliver_date,
              e.audited_date,
              e.archived_date,
              e.lmc_date,
              e.ogefrem_date,
              e.pv_date,
              e.demande_attestation_date,
              e.assay_date,
              e.dgda_seal_no,      -- ✅ NEW
              e.number_of_seals,   -- ✅ NEW
              e.lmc_id,      -- ✅ NEW
              e.ogefrem_inv_ref,  
              e.number_of_seals,   -- ✅ NEW
              c.short_name as subscriber_name,
              e.transport_mode
            {$baseQuery}
            {$filterCondition}
            ORDER BY e.id ASC";

    $exports = $this->db->customQuery($sql, $params);

    $relevantFields = [];
    
    $fieldMap = [
      'ceec_pending' => ['ceec_in_date', 'ceec_out_date', 'pv_date', 'demande_attestation_date', 'assay_date'],
      'min_div_pending' => ['min_div_in_date', 'min_div_out_date'],
      'gov_docs_pending' => ['gov_docs_in_date', 'gov_docs_out_date'],
      'dgda_in_pending' => ['dgda_in_date'],
      'liquidation_pending' => ['liquidation_date'],
      'quittance_pending' => ['quittance_date'],
      'audited_pending' => ['audited_date'],
      'archived_pending' => ['archived_date'],
      'lmc_pending' => ['lmc_date'],
      'ogefrem_pending' => ['ogefrem_date'],
      'dispatch_pending' => ['dispatch_deliver_date'],
      'seal_pending' => ['dgda_seal_no', 'number_of_seals'], // ✅ NEW
      'lmc_id_pending' => ['lmc_id'],
      'lmc_date_pending' => ['lmc_date'],
      'ogefrem_date_pending' => ['ogefrem_date'],
      'ogefrem_ref_pending' => ['ogefrem_inv_ref'],
    ];
    
    foreach ($filters as $filter) {
      if (isset($fieldMap[$filter])) {
        $relevantFields = array_merge($relevantFields, $fieldMap[$filter]);
      }
    }
    
    $relevantFields = array_unique($relevantFields);

    $exports = $this->sanitizeArray($exports);

    echo json_encode([
      'success' => true,
      'data' => $exports ?: [],
      'relevant_fields' => $relevantFields,
      'active_filters' => $filters,
      'count' => count($exports)
    ]);

  } catch (Exception $e) {
    $this->logError('Failed to get bulk update data', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'Failed to load bulk update data']);
  }
}

private function bulkUpdate()
{
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    return;
  }

  $this->validateCsrfToken();

  try {
    $updateData = isset($_POST['update_data']) ? json_decode($_POST['update_data'], true) : null;
    
    if (empty($updateData) || !is_array($updateData)) {
      echo json_encode(['success' => false, 'message' => 'No update data provided']);
      return;
    }

    if (count($updateData) > 3000) {
      echo json_encode(['success' => false, 'message' => 'Maximum 500 records can be updated at once']);
      return;
    }

    $userId = (int)($_SESSION['user_id'] ?? 1);

    $this->db->beginTransaction();

    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    try {
      foreach ($updateData as $update) {
        $exportId = (int)($update['export_id'] ?? 0);
        
        if ($exportId <= 0) {
          $errorCount++;
          continue;
        }

        $export = $this->db->selectData('exports_t', 
          'loading_date, ceec_in_date, ceec_out_date, min_div_in_date, min_div_out_date, gov_docs_in_date, gov_docs_out_date, dgda_seal_no, mca_ref', 
          ['id' => $exportId, 'display' => 'Y']
        );
        
        if (empty($export)) {
          $errorCount++;
          $errors[] = "Export ID {$exportId}: Not found";
          continue;
        }

        $loadingDate = $export[0]['loading_date'];
        $currentCeecIn = $export[0]['ceec_in_date'];
        $currentCeecOut = $export[0]['ceec_out_date'];
        $currentMinDivIn = $export[0]['min_div_in_date'];
        $currentMinDivOut = $export[0]['min_div_out_date'];
        $currentGovDocsIn = $export[0]['gov_docs_in_date'];
        $currentGovDocsOut = $export[0]['gov_docs_out_date'];
        $oldSeal = $export[0]['dgda_seal_no'] ?? '';
        $mcaRef = $export[0]['mca_ref'];

        $data = [];
        $allowedFields = ['ceec_in_date', 'ceec_out_date', 'min_div_in_date', 'min_div_out_date', 
               'gov_docs_in_date', 'gov_docs_out_date', 'dgda_in_date', 'liquidation_date', 
               'quittance_date', 'dispatch_deliver_date', 'audited_date', 'archived_date','lmc_date','ogefrem_date',
               'pv_date', 'demande_attestation_date', 'assay_date',
               'dgda_seal_no', 'number_of_seals','lmc_id','ogefrem_inv_ref']; // ✅ SEAL FIELDS ADDED
        
        foreach ($update as $field => $value) {
          if ($field === 'export_id' || $field === 'seal_ids') continue;
          
          if (!in_array($field, $allowedFields)) {
            continue;
          }
          
          // Skip seal fields here - they'll be handled separately
          if ($field === 'dgda_seal_no' || $field === 'number_of_seals') {
            continue;
          }
          if ($field === 'lmc_id' || $field === 'ogefrem_inv_ref') {
            continue;
          }
          if (empty($value)) {
            $data[$field] = null;
          } else {
            $value = $this->sanitizeInput($value);
            
            if (!$this->isValidDate($value)) {
              $errorCount++;
              $errors[] = "Export ID {$exportId}: Invalid {$field} format";
              continue 2;
            }
            
            $data[$field] = $value;
          }
        }
        
        // ✅ HANDLE SEAL UPDATES
        if (isset($update['seal_ids'])) {
          $sealIdsJson = $update['seal_ids'];
          
          if ($sealIdsJson === '' || $sealIdsJson === '[]' || $sealIdsJson === 'null') {
            // Clear seals
            if (!empty($oldSeal)) {
              $this->releaseSeals($oldSeal, $userId);
            }
            $data['dgda_seal_no'] = null;
            $data['number_of_seals'] = null;
          } else {
            $sealIds = json_decode($sealIdsJson, true);
            
            if (is_array($sealIds) && !empty($sealIds)) {
              // Release old seals
              if (!empty($oldSeal)) {
                $this->releaseSeals($oldSeal, $userId);
              }
              
              // Validate and reserve new seals
              $sealValidation = $this->validateAndReserveSeals($sealIds, $mcaRef, $userId);
              if (!$sealValidation['success']) {
                $errorCount++;
                $errors[] = "Export ID {$exportId}: " . $sealValidation['message'];
                continue;
              }
              
              // Get seal numbers for display
              $sealNumbers = [];
              foreach ($sealIds as $sealId) {
                $seal = $this->db->selectData('seal_individual_numbers_t', 'seal_number', ['id' => (int)$sealId, 'display' => 'Y']);
                if (!empty($seal)) {
                  $sealNumbers[] = $seal[0]['seal_number'];
                }
              }
              
              if (!empty($sealNumbers)) {
                $data['dgda_seal_no'] = implode(', ', $sealNumbers);
                $data['number_of_seals'] = count($sealNumbers);
              }
            }
          }
        }
        if ($field === 'lmc_id') {
          $data['lmc_id'] = $update['lmc_id'];
        } 
        if ($field === 'ogefrem_inv_ref') {
          $data['ogefrem_inv_ref'] = $update['ogefrem_inv_ref'];
        }  
        $ceecIn = $data['ceec_in_date'] ?? $currentCeecIn;
        $ceecOut = $data['ceec_out_date'] ?? $currentCeecOut;
        $minDivIn = $data['min_div_in_date'] ?? $currentMinDivIn;
        $minDivOut = $data['min_div_out_date'] ?? $currentMinDivOut;
        $govDocsIn = $data['gov_docs_in_date'] ?? $currentGovDocsIn;
        $govDocsOut = $data['gov_docs_out_date'] ?? $currentGovDocsOut;

        if ($ceecIn && $ceecOut && strtotime($ceecOut) < strtotime($ceecIn)) {
          $errorCount++;
          $errors[] = "Export ID {$exportId}: CEEC Out date cannot be before CEEC In date";
          continue;
        }

        if ($minDivIn && $minDivOut && strtotime($minDivOut) < strtotime($minDivIn)) {
          $errorCount++;
          $errors[] = "Export ID {$exportId}: Min Div Out date cannot be before Min Div In date";
          continue;
        }

        if ($govDocsIn && $govDocsOut && strtotime($govDocsOut) < strtotime($govDocsIn)) {
          $errorCount++;
          $errors[] = "Export ID {$exportId}: Gov Docs Out date cannot be before Gov Docs In date";
          continue;
        }

        if (empty($data)) {
          continue;
        }

        $data['updated_by'] = $userId;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (isset($data['ceec_in_date']) || isset($data['ceec_out_date']) || isset($data['min_div_in_date']) || isset($data['min_div_out_date'])) {
          $data['document_status'] = $this->calculateDocumentStatus($ceecIn, $ceecOut, $minDivIn, $minDivOut);
        }

        $success = $this->db->updateData('exports_t', $data, ['id' => $exportId]);

        if ($success) {
          $successCount++;
        } else {
          $errorCount++;
          $errors[] = "Export ID {$exportId}: Update failed";
        }
      }

      $this->db->commit();

    } catch (Exception $e) {
      $this->db->rollback();
      throw $e;
    }

    $message = "Bulk update completed: {$successCount} successful";
    if ($errorCount > 0) {
      $message .= ", {$errorCount} failed";
    }

    echo json_encode([
      'success' => true,
      'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
      'success_count' => $successCount,
      'error_count' => count($errors),
      'errors' => array_map(function($error) {
        return htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
      }, $errors)
    ]);

  } catch (Exception $e) {
    $this->logError('Exception during bulk update', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'An error occurred during bulk update. All changes have been rolled back.']);
  }
}

  private function getLicenses()
{
  try {
    // ✅ FIX: Handle subscriber_id as array or single value
    if (empty($_GET['subscriber_id'])) {
      echo json_encode(['success' => false, 'message' => 'Invalid subscriber ID']);
      return;
    }

    // Convert to array if single value
    $subscriberIds = is_array($_GET['subscriber_id']) 
      ? array_map('intval', array_filter($_GET['subscriber_id']))
      : [(int)$_GET['subscriber_id']];

    // Remove any zero or negative values
    $subscriberIds = array_filter($subscriberIds, function($id) {
      return $id > 0;
    });

    if (empty($subscriberIds)) {
      echo json_encode(['success' => false, 'message' => 'Invalid subscriber ID']);
      return;
    }

    // ✅ Build IN clause for multiple clients
    $placeholders = implode(',', $subscriberIds);

    $sql = "SELECT l.id, l.license_number, l.client_id
            FROM licenses_t l
            WHERE l.client_id IN ($placeholders)
            AND l.display = 'Y' 
            AND l.status != 'ANNULATED'
            AND (l.license_expiry_date IS NULL OR l.license_expiry_date >= CURDATE())
            AND l.kind_id IN (3, 4)
            ORDER BY l.client_id ASC, l.license_number ASC";

    $licenses = $this->db->customQuery($sql);
    $licenses = $this->sanitizeArray($licenses);
    
    echo json_encode([
      'success' => true,
      'data' => $licenses ?: []
    ]);

  } catch (Exception $e) {
    $this->logError('Failed to get licenses', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'Failed to load licenses']);
  }
}

private function getLicenseUsage()
{
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);
      $excludeExportId = isset($_GET['exclude_export_id']) ? (int)$_GET['exclude_export_id'] : null;

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
        return;
      }

      $sql = "SELECT 
                COALESCE(SUM(weight), 0) as used_weight,
                COALESCE(SUM(fob), 0) as used_fob
              FROM exports_t 
              WHERE license_id = :license_id 
              AND display = 'Y'";
      
      $params = [':license_id' => $licenseId];
      
      if ($excludeExportId) {
        $sql .= " AND id <= :exclude_export_id";  
        $params[':exclude_export_id'] = $excludeExportId;
      }
      
      $result = $this->db->customQuery($sql, $params);
      
      echo json_encode([
        'success' => true,
        'used_weight' => floatval($result[0]['used_weight']),
        'used_fob' => floatval($result[0]['used_fob'])
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get license usage', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load license usage']);
    }
}

  private function getLicenseDetails()
  {
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
        return;
      }

      $sql = "SELECT 
                l.id, l.license_number, l.client_id as subscriber_id,
                l.kind_id, l.type_of_goods_id, l.transport_mode_id,
                l.currency_id, l.supplier as buyer, l.weight, l.fob_declared,
                k.kind_name, k.kind_short_name,
                tg.goods_type as type_of_goods_name, tg.goods_short_name,
                tm.transport_mode_name, tm.transport_letter,
                c.currency_short_name as currency_name
              FROM licenses_t l
              LEFT JOIN kind_master_t k ON l.kind_id = k.id
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
              LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
              LEFT JOIN currency_master_t c ON l.currency_id = c.id
              WHERE l.id = :license_id AND l.display = 'Y'";

      $license = $this->db->customQuery($sql, [':license_id' => $licenseId]);

      if (empty($license)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $licenseData = $license[0];
      $licenseData = $this->sanitizeArray([$licenseData])[0];

      echo json_encode([
        'success' => true,
        'data' => $licenseData
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get license details', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load license details']);
    }
  }

  private function getNextMCASequence()
  {
    $this->validateCsrfToken();

    try {
      $subscriberId = (int)($_POST['subscriber_id'] ?? 0);
      $licenseId = (int)($_POST['license_id'] ?? 0);

      if ($subscriberId <= 0 || $licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
      }

      $subscriber = $this->db->selectData('clients_t', 'short_name', ['id' => $subscriberId]);
      if (empty($subscriber)) {
        echo json_encode(['success' => false, 'message' => 'Subscriber not found']);
        return;
      }
      $clientShortName = strtoupper(trim($this->sanitizeInput($subscriber[0]['short_name'])));

      $sql = "SELECT k.kind_short_name, tg.goods_short_name, tm.transport_letter
              FROM licenses_t l
              LEFT JOIN kind_master_t k ON l.kind_id = k.id
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
              LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
              WHERE l.id = :license_id AND l.display = 'Y'";

      $license = $this->db->customQuery($sql, [':license_id' => $licenseId]);

      if (empty($license)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $kindShortName = strtoupper(trim($this->sanitizeInput($license[0]['kind_short_name'] ?? '')));
      $goodsShortName = strtoupper(trim($this->sanitizeInput($license[0]['goods_short_name'] ?? '')));
      $transportLetter = strtoupper(trim($this->sanitizeInput($license[0]['transport_letter'] ?? '')));

      $year = substr(date('Y'), -2);
      $combinedCode = "{$kindShortName}{$goodsShortName}{$transportLetter}{$year}";
      $prefix = "{$clientShortName}-{$combinedCode}-";

      $sql = "SELECT mca_ref 
              FROM exports_t 
              WHERE mca_ref LIKE :prefix 
              AND display = 'Y'
              ORDER BY mca_ref DESC 
              LIMIT 1";

      $result = $this->db->customQuery($sql, [':prefix' => $prefix . '%']);

      $nextSequence = 1;

      if (!empty($result)) {
        $lastRef = $result[0]['mca_ref'];
        if (preg_match('/-(\d{4})$/', $lastRef, $matches)) {
          $lastSequence = (int)$matches[1];
          $nextSequence = $lastSequence + 1;
        }
      }

      $sequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
      $mcaRef = "{$prefix}{$sequence}";

      echo json_encode([
        'success' => true,
        'mca_ref' => htmlspecialchars($mcaRef, ENT_QUOTES, 'UTF-8'),
        'sequence' => $nextSequence
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to generate MCA sequence', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to generate MCA reference']);
    }
  }

private function updateExport()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        return;
    }

    $this->validateCsrfToken();

    try {
        $exportId = (int)($_POST['export_id'] ?? 0);
        if ($exportId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid export ID']);
            return;
        }

        $existing = $this->db->selectData('exports_t', '*', ['id' => $exportId, 'display' => 'Y']);
        if (empty($existing)) {
            echo json_encode(['success' => false, 'message' => 'Export not found']);
            return;
        }

        $validation = $this->validateExportData($_POST, $exportId);
        if (!$validation['success']) {
            echo json_encode($validation);
            return;
        }

        $dates = [
            'ceec_in_date' => 'ceec_out_date',
            'min_div_in_date' => 'min_div_out_date',
            'gov_docs_in_date' => 'gov_docs_out_date'
        ];
        foreach ($dates as $in => $out) {
            $inDate = !empty($_POST[$in]) ? $_POST[$in] : null;
            $outDate = !empty($_POST[$out]) ? $_POST[$out] : null;
            if ($inDate && $outDate && strtotime($outDate) < strtotime($inDate)) {
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_',' ',$out)) . ' cannot be before ' . ucfirst(str_replace('_',' ',$in))]);
                return;
            }
        }

        $data = $this->prepareExportData($_POST);

        foreach (['weight', 'fob'] as $field) {
            $validation = $this->validateWeight($data[$field] ?? 0, strtoupper($field));
            if (!$validation['valid']) {
                echo json_encode(['success' => false, 'message' => $validation['message']]);
                return;
            }
            $data[$field] = $validation['value'];
        }

        $licenseValidation = $this->validateAgainstLicense(
            $data['license_id'],
            $data['weight'],
            $data['fob'],
            $exportId
        );
        if (!$licenseValidation['success']) {
            echo json_encode($licenseValidation);
            return;
        }

        $duplicateCheck = $this->checkDuplicateExport($data, $exportId);
        if (!$duplicateCheck['success']) {
            echo json_encode($duplicateCheck);
            return;
        }

        $recalculate = false;
        foreach (['weight', 'type_of_goods', 'feet_container'] as $key) {
            if (isset($data[$key]) && $data[$key] !== $existing[0][$key]) {
                $recalculate = true;
            }
        }

        if ($recalculate) {
            $amounts = $this->calculateAllAmounts(
                $data['weight'] ?? $existing[0]['weight'],
                $data['type_of_goods'] ?? $existing[0]['type_of_goods'],
                $data['feet_container'] ?? $existing[0]['feet_container']
            );
            $data = array_merge($data, $amounts);
        }

        $userId = (int)($_SESSION['user_id'] ?? 1);
        $data['updated_by'] = $userId;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $oldSeal = $existing[0]['dgda_seal_no'] ?? '';
        $newSeal = $data['dgda_seal_no'] ?? '';

        if ($oldSeal !== $newSeal) {
            if (!empty($oldSeal)) {
                $this->releaseSeals($oldSeal, $userId);
            }
            
            if (!empty($newSeal)) {
                // ✅ FIX: Handle both JSON array and comma-separated string formats
                $sealIdsRaw = $_POST['dgda_seal_ids'] ?? '';
                
                if (empty($sealIdsRaw)) {
                    $sealIds = [];
                } elseif (is_string($sealIdsRaw) && strlen($sealIdsRaw) > 0 && $sealIdsRaw[0] === '[') {
                    // JSON array format: ["1","2","3"]
                    $sealIds = json_decode($sealIdsRaw, true);
                    if (!is_array($sealIds)) {
                        $sealIds = [];
                    }
                } else {
                    // Comma-separated string format: "1,2,3"
                    $sealIds = array_filter(
                        array_map('intval', 
                        array_map('trim', explode(',', $sealIdsRaw)))
                    );
                }
                
                if (!empty($sealIds)) {
                    $sealValidation = $this->validateAndReserveSeals($sealIds, $data['mca_ref'], $userId);
                    if (!$sealValidation['success']) {
                        echo json_encode($sealValidation);
                        return;
                    }
                }
            }
        }

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = implode(',', $v);
            }
            if ($v === '') {
                $data[$k] = null;
            }
        }

        $result = $this->db->updateData('exports_t', $data, ['id' => $exportId]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Export updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update export.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

  private function getExport()
  {
    try {
      $exportId = (int)($_GET['id'] ?? 0);

      if ($exportId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid export ID']);
        return;
      }

      $sql = "SELECT e.*, 
                c.short_name as subscriber_name, 
                c.liquidation_paid_by as client_liquidation_paid_by, 
                l.license_number, 
                l.weight as license_weight,
                l.fob_declared as license_fob,
                l.transport_mode_id as license_transport_mode_id,
                k.kind_name,
                tg.goods_type as type_of_goods_name,
                tm.transport_mode_name,
                curr.currency_short_name as currency_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id
              LEFT JOIN licenses_t l ON e.license_id = l.id
              LEFT JOIN kind_master_t k ON e.kind = k.id
              LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id
              LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id
              LEFT JOIN currency_master_t curr ON e.currency = curr.id
              WHERE e.id = :id AND e.display = 'Y'";

      $export = $this->db->customQuery($sql, [':id' => $exportId]);

      if (!empty($export)) {
        $exportData = $export[0];
        $exportData = $this->sanitizeArray([$exportData])[0];
        echo json_encode(['success' => true, 'data' => $exportData]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Export not found']);
      }
    } catch (Exception $e) {
      $this->logError('Exception while fetching export', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load export data']);
    }
  }

private function listExports()
{
  try {
    $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
    $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $length = isset($_GET['length']) ? (int)$_GET['length'] : 25;
    $searchValue = isset($_GET['search']['value']) ? trim($_GET['search']['value']) : '';
    
    $filters = isset($_GET['filters']) ? $_GET['filters'] : [];
    if (!is_array($filters)) {
      $filters = [];
    }
    $filters = array_filter($filters, function($filter) {
      return in_array($filter, $this->allowedFilters);
    });
    
    $advancedFilters = [];
    $advancedParams = [];
    
    // ✅ FIX: Handle subscriber_filter as array or single value
    if (!empty($_GET['subscriber_filter'])) {
      if (is_array($_GET['subscriber_filter'])) {
        // Multiple clients selected
        $clientIds = array_map('intval', array_filter($_GET['subscriber_filter']));
        if (!empty($clientIds)) {
          $placeholders = implode(',', $clientIds);
          $advancedFilters[] = "e.subscriber_id IN ($placeholders)";
          // No params needed - we sanitized with intval and embedded directly
        }
      } else {
        // Single client selected (backwards compatibility)
        $clientId = (int)$_GET['subscriber_filter'];
        if ($clientId > 0) {
          $advancedFilters[] = "e.subscriber_id = $clientId";
        }
      }
    }
    
    if (!empty($_GET['license_filter'])) {
      $advancedFilters[] = "e.license_id = :license_filter";
      $advancedParams[':license_filter'] = (int)$_GET['license_filter'];
    }
    
    if (!empty($_GET['transport_mode_filter'])) {
      $advancedFilters[] = "e.transport_mode = :transport_mode_filter";
      $advancedParams[':transport_mode_filter'] = (int)$_GET['transport_mode_filter'];
    }
    
    if (!empty($_GET['start_date_filter']) && $this->isValidDate($_GET['start_date_filter'])) {
      $advancedFilters[] = "e.loading_date >= :start_date_filter";
      $advancedParams[':start_date_filter'] = $_GET['start_date_filter'];
    }
    
    if (!empty($_GET['end_date_filter']) && $this->isValidDate($_GET['end_date_filter'])) {
      $advancedFilters[] = "e.loading_date <= :end_date_filter";
      $advancedParams[':end_date_filter'] = $_GET['end_date_filter'];
    }
    
    $orderColumnIndex = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
    $orderDirection = isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
    
    $columns = ['e.id', 'e.mca_ref', 'c.short_name', 'l.license_number', 'e.invoice', 
                'e.loading_date', 'e.weight', 'e.fob', 'cs.clearing_status'];
    $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'e.id';

    $baseQuery = "FROM exports_t e
                  LEFT JOIN clients_t c ON e.subscriber_id = c.id
                  LEFT JOIN licenses_t l ON e.license_id = l.id
                  LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id
                  WHERE e.display = 'Y'";

    $searchCondition = "";
    $filterCondition = "";
    $advancedFilterCondition = "";
    $params = [];
    
    if (!empty($searchValue)) {
      $searchValue = $this->sanitizeInput($searchValue);
      $searchCondition = " AND (
        e.mca_ref LIKE :search1 OR
        e.invoice LIKE :search2 OR
        c.short_name LIKE :search3 OR
        c.company_name LIKE :search4 OR
        l.license_number LIKE :search5 OR
        cs.clearing_status LIKE :search6 OR
        e.horse LIKE :search7 OR
        e.trailer_1 LIKE :search8 OR
        e.trailer_2 LIKE :search9 OR
        e.lot_number LIKE :search10 OR
        e.destination LIKE :search11 OR
        e.transporter LIKE :search12 OR
        e.wagon_ref LIKE :search13 OR
        e.container LIKE :search14 OR
        e.buyer LIKE :search15
      )";
      $searchParam = "%{$searchValue}%";
      for ($i = 1; $i <= 15; $i++) {
        $params[":search{$i}"] = $searchParam;
      }
    }

    if (!empty($filters)) {
      $filterClauses = [];
      
      foreach ($filters as $filter) {
        switch ($filter) {
          case 'completed':
            $filterClauses[] = "cs.clearing_status = :status_completed";
            $params[':status_completed'] = 'CLEARING COMPLETED';
            break;
          case 'in_progress':
            $filterClauses[] = "cs.clearing_status = :status_in_progress";
            $params[':status_in_progress'] = 'IN PROGRESS';
            break;
          case 'in_transit':
            $filterClauses[] = "cs.clearing_status = :status_in_transit";
            $params[':status_in_transit'] = 'IN TRANSIT';
            break;
          case 'ceec_pending':
            $filterClauses[] = "(e.ceec_in_date IS NULL OR e.ceec_out_date IS NULL)";
            break;
          case 'min_div_pending':
            $filterClauses[] = "(e.min_div_in_date IS NULL OR e.min_div_out_date IS NULL)";
            break;
          case 'gov_docs_pending':
            $filterClauses[] = "(e.gov_docs_in_date IS NULL OR e.gov_docs_out_date IS NULL)";
            break;
          case 'audited_pending':
            $filterClauses[] = "e.audited_date IS NULL";
            break;
          case 'archived_pending':
            $filterClauses[] = "e.archived_date IS NULL";
            break;
          case 'lmc_pending':
            $filterClauses[] = "e.lmc_date IS NULL";
            break;
          case 'ogefrem_pending':
            $filterClauses[] = "e.ogefrem_date IS NULL";
            break;
          case 'dgda_in_pending':
            $filterClauses[] = "e.dgda_in_date IS NULL";
            break;
          case 'liquidation_pending':
            $filterClauses[] = "e.liquidation_date IS NULL";
            break;
          case 'quittance_pending':
            $filterClauses[] = "e.quittance_date IS NULL";
            break;
          case 'dispatch_pending':
            $filterClauses[] = "e.dispatch_deliver_date IS NULL";
            break;
          case 'seal_pending': // ✅ ADD THIS CASE
            $filterClauses[] = "(e.dgda_seal_no IS NULL OR e.dgda_seal_no = '' OR e.number_of_seals IS NULL OR e.number_of_seals = 0)";
            break;
          case 'lmc_id_pending':
            $filterClauses[] = "e.lmc_date IS NULL";
            break;
          case 'ogefrem_ref_pending':
            $filterClauses[] = "e.ogefrem_date IS NULL";
        }
      }
      
      if (!empty($filterClauses)) {
        $filterCondition = " AND (" . implode(' OR ', $filterClauses) . ")";
      }
    }
    
    // ✅ FIX: Build advanced filter condition
    if (!empty($advancedFilters)) {
      $advancedFilterCondition = " AND (" . implode(' AND ', $advancedFilters) . ")";
      $params = array_merge($params, $advancedParams);
    }

    $totalSql = "SELECT COUNT(*) as total FROM exports_t WHERE display = 'Y'";
    $totalResult = $this->db->customQuery($totalSql);
    $totalRecords = (int)($totalResult[0]['total'] ?? 0);

    $filteredSql = "SELECT COUNT(*) as total {$baseQuery} {$searchCondition} {$filterCondition} {$advancedFilterCondition}";
    $filteredResult = $this->db->customQuery($filteredSql, $params);
    $filteredRecords = (int)($filteredResult[0]['total'] ?? 0);

    $dataSql = "SELECT 
                  e.id, e.mca_ref, e.invoice, e.loading_date,
                  e.weight, e.fob,
                  c.short_name as subscriber_name,
                  l.license_number,
                  cs.clearing_status AS clearing_status_name
                {$baseQuery}
                {$searchCondition}
                {$filterCondition}
                {$advancedFilterCondition}
                ORDER BY {$orderColumn} {$orderDirection}
                LIMIT :limit OFFSET :offset";

    $params[':limit'] = $length;
    $params[':offset'] = $start;

    $exports = $this->db->customQuery($dataSql, $params);
    $exports = $this->sanitizeArray($exports);

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $filteredRecords,
      'data' => $exports ?: []
    ]);

  } catch (Exception $e) {
    $this->logError('Exception in listExports', ['error' => $e->getMessage()]);
    echo json_encode([
      'draw' => $_GET['draw'] ?? 1,
      'recordsTotal' => 0,
      'recordsFiltered' => 0,
      'data' => []
    ]);
  }
}

 private function getStatistics()
{
  try {
    $totalSql = "SELECT COUNT(*) as total FROM exports_t WHERE display = 'Y'";
    $totalResult = $this->db->customQuery($totalSql);
    $totalExports = (int)($totalResult[0]['total'] ?? 0);

    $statusSql = "SELECT id, clearing_status FROM clearing_status_master_t WHERE display = 'Y'";
    $statuses = $this->db->customQuery($statusSql);
    
    $completedId = null;
    $inProgressId = null;
    $inTransitId = null;
    
    foreach ($statuses as $status) {
      $statusText = strtoupper(trim($status['clearing_status']));
      if (strpos($statusText, 'CLEARING COMPLETED') !== false || strpos($statusText, 'COMPLETED') !== false) {
        $completedId = (int)$status['id'];
      } elseif (strpos($statusText, 'IN PROGRESS') !== false || strpos($statusText, 'PROGRESS') !== false) {
        $inProgressId = (int)$status['id'];
      } elseif (strpos($statusText, 'IN TRANSIT') !== false || strpos($statusText, 'TRANSIT') !== false) {
        $inTransitId = (int)$status['id'];
      }
    }

    $totalCompleted = 0;
    $inProgress = 0;
    $inTransit = 0;
    
    if ($completedId) {
      $sql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND clearing_status = :status_id";
      $result = $this->db->customQuery($sql, [':status_id' => $completedId]);
      $totalCompleted = (int)($result[0]['count'] ?? 0);
    }
    
    if ($inProgressId) {
      $sql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND clearing_status = :status_id";
      $result = $this->db->customQuery($sql, [':status_id' => $inProgressId]);
      $inProgress = (int)($result[0]['count'] ?? 0);
    }
    
    if ($inTransitId) {
      $sql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND clearing_status = :status_id";
      $result = $this->db->customQuery($sql, [':status_id' => $inTransitId]);
      $inTransit = (int)($result[0]['count'] ?? 0);
    }

    $ceecPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND (ceec_in_date IS NULL OR ceec_out_date IS NULL)";
    $ceecPending = $this->db->customQuery($ceecPendingSql);
    
    $minDivPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND (min_div_in_date IS NULL OR min_div_out_date IS NULL)";
    $minDivPending = $this->db->customQuery($minDivPendingSql);
    
    $govDocsPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND (gov_docs_in_date IS NULL OR gov_docs_out_date IS NULL)";
    $govDocsPending = $this->db->customQuery($govDocsPendingSql);
    
    $auditedPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND audited_date IS NULL";
    $auditedPending = $this->db->customQuery($auditedPendingSql);
    
    $archivedPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND archived_date IS NULL";
    $archivedPending = $this->db->customQuery($archivedPendingSql);

    $lmcPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND lmc_date IS NULL";
    $lmcPending = $this->db->customQuery($lmcPendingSql);

    $ogefremPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND ogefrem_date IS NULL";
    $ogefremPending = $this->db->customQuery($ogefremPendingSql);
    
    $dgdaInPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND dgda_in_date IS NULL";
    $dgdaInPending = $this->db->customQuery($dgdaInPendingSql);
    
    $liquidationPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND liquidation_date IS NULL";
    $liquidationPending = $this->db->customQuery($liquidationPendingSql);
    
    $quittancePendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND quittance_date IS NULL";
    $quittancePending = $this->db->customQuery($quittancePendingSql);
    
    $dispatchPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND dispatch_deliver_date IS NULL";
    $dispatchPending = $this->db->customQuery($dispatchPendingSql);

    // ✅ NEW: Seal Pending Count
    $sealPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND (dgda_seal_no IS NULL OR dgda_seal_no = '' OR number_of_seals IS NULL OR number_of_seals = 0)";
    $sealPending = $this->db->customQuery($sealPendingSql);

    // ✅ NEW: Seal Pending Count
    $sealLmcIdSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND lmc_id IS NULL OR lmc_id = ''";
    $lmcIdPending = $this->db->customQuery($sealLmcIdSql);

    // ✅ NEW: Seal Pending Count
    $ogefremRefPendingSql = "SELECT COUNT(*) as count FROM exports_t WHERE display = 'Y' AND ogefrem_inv_ref IS NULL OR ogefrem_inv_ref = ''";
    $ogefremRefPending = $this->db->customQuery($ogefremRefPendingSql);

    echo json_encode([
      'success' => true,
      'data' => [
        'total_exports' => $totalExports,
        'total_completed' => $totalCompleted,
        'in_progress' => $inProgress,
        'in_transit' => $inTransit,
        'ceec_pending' => (int)($ceecPending[0]['count'] ?? 0),
        'min_div_pending' => (int)($minDivPending[0]['count'] ?? 0),
        'gov_docs_pending' => (int)($govDocsPending[0]['count'] ?? 0),
        'audited_pending' => (int)($auditedPending[0]['count'] ?? 0),
        'archived_pending' => (int)($archivedPending[0]['count'] ?? 0),
        'lmc_pending' => (int)($lmcPending[0]['count'] ?? 0),
        'ogefrem_pending' => (int)($ogefremPending[0]['count'] ?? 0),
        'dgda_in_pending' => (int)($dgdaInPending[0]['count'] ?? 0),
        'liquidation_pending' => (int)($liquidationPending[0]['count'] ?? 0),
        'quittance_pending' => (int)($quittancePending[0]['count'] ?? 0),
        'dispatch_pending' => (int)($dispatchPending[0]['count'] ?? 0),
        'seal_pending' => (int)($sealPending[0]['count'] ?? 0) ,// ✅ NEW
        'lmc_id_pending' => (int)($lmcIdPending[0]['count'] ?? 0),
        'ogefrem_ref_pending' => (int)($ogefremRefPending[0]['count'] ?? 0),
      ]
    ]);

  } catch (Exception $e) {
    $this->logError('Failed to get statistics', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'Failed to load statistics']);
  }
}

  private function validateExportData($post, $exportId = null)
  {
    $errors = [];

    $requiredFields = [
      'subscriber_id' => 'Subscriber',
      'license_id' => 'License Number',
      'regime' => 'Regime',
      'types_of_clearance' => 'Types of Clearance'
    ];

    foreach ($requiredFields as $field => $label) {
      if (empty($post[$field])) {
        $errors[] = htmlspecialchars("{$label} is required", ENT_QUOTES, 'UTF-8');
      }
    }

    if (empty($post['mca_ref'])) {
      $errors[] = 'MCA Reference is required';
    } else {
      $mcaRef = $this->sanitizeInput(trim($post['mca_ref']));
      
      if (!preg_match('/^[A-Z0-9]+-[A-Z0-9]+\d{2}-\d{4}$/', $mcaRef)) {
        $errors[] = 'MCA Reference has invalid format';
      }
      
      $sql = "SELECT id FROM exports_t WHERE mca_ref = :mca_ref AND display = 'Y'";
      $params = [':mca_ref' => $mcaRef];
      
      if ($exportId) {
        $sql .= " AND id != :export_id";
        $params[':export_id'] = $exportId;
      }
      
      $exists = $this->db->customQuery($sql, $params);
      if ($exists) {
        $errors[] = 'MCA Reference already exists';
      }
    }

    if (!empty($errors)) {
      return [
        'success' => false,
        'message' => '<ul style="text-align:left;"><li>' . implode('</li><li>', $errors) . '</li></ul>'
      ];
    }

    return ['success' => true];
  }

  private function prepareExportData($post)
  {
    return [
      'subscriber_id' => !empty($post['subscriber_id']) ? $this->toInt($post['subscriber_id']) : null,
      'license_id' => !empty($post['license_id']) ? $this->toInt($post['license_id']) : null,
      'kind' => !empty($post['kind']) ? $this->toInt($post['kind']) : null,
      'type_of_goods' => !empty($post['type_of_goods']) ? $this->toInt($post['type_of_goods']) : null,
      'transport_mode' => !empty($post['transport_mode']) ? $this->toInt($post['transport_mode']) : null,
      'mca_ref' => !empty($post['mca_ref']) ? $this->clean($post['mca_ref']) : null,
      'currency' => !empty($post['currency']) ? $this->toInt($post['currency']) : null,
      'buyer' => !empty($post['buyer']) ? $this->clean($post['buyer']) : null,
      'regime' => !empty($post['regime']) ? $this->toInt($post['regime']) : null,
      'types_of_clearance' => !empty($post['types_of_clearance']) ? $this->toInt($post['types_of_clearance']) : null,
      'invoice' => !empty($post['invoice']) ? $this->clean($post['invoice']) : null,
      'po_ref' => !empty($post['po_ref']) ? $this->clean($post['po_ref']) : null,
      'weight' => !empty($post['weight']) && is_numeric($post['weight']) ? abs(round((float)$post['weight'], 3)) : 0,  // ✅ Changed to 3
      'fob' => !empty($post['fob']) && is_numeric($post['fob']) ? abs(round((float)$post['fob'], 2)) : 0,
      'horse' => !empty($post['horse']) ? $this->clean($post['horse']) : null,
      'trailer_1' => !empty($post['trailer_1']) ? $this->clean($post['trailer_1']) : null,
      'trailer_2' => !empty($post['trailer_2']) ? $this->clean($post['trailer_2']) : null,
      'feet_container' => !empty($post['feet_container']) ? $this->clean($post['feet_container']) : null,
      'wagon_ref' => !empty($post['wagon_ref']) ? $this->clean($post['wagon_ref']) : null,
      'container' => !empty($post['container']) ? $this->clean($post['container']) : null,
      'transporter' => !empty($post['transporter']) ? $this->clean($post['transporter']) : null,
      'site_of_loading_id' => !empty($post['site_of_loading_id']) ? $this->toInt($post['site_of_loading_id']) : null,
      'destination' => !empty($post['destination']) ? $this->clean($post['destination']) : null,
      'loading_date' => !empty($post['loading_date']) && $this->isValidDate($post['loading_date']) ? $post['loading_date'] : null,
      'pv_date' => !empty($post['pv_date']) && $this->isValidDate($post['pv_date']) ? $post['pv_date'] : null,
      'bp_date' => !empty($post['bp_date']) && $this->isValidDate($post['bp_date']) ? $post['bp_date'] : null,
      'demande_attestation_date' => !empty($post['demande_attestation_date']) && $this->isValidDate($post['demande_attestation_date']) ? $post['demande_attestation_date'] : null,
      'assay_date' => !empty($post['assay_date']) && $this->isValidDate($post['assay_date']) ? $post['assay_date'] : null,
      'lot_number' => !empty($post['lot_number']) ? $this->clean($post['lot_number']) : null,
      'number_of_seals' => !empty($post['number_of_seals']) ? $this->toInt($post['number_of_seals']) : null,
      'dgda_seal_no' => !empty($post['dgda_seal_no']) ? $this->clean($post['dgda_seal_no']) : null,
      'number_of_bags' => isset($post['number_of_bags']) && is_numeric($post['number_of_bags']) ? abs((int)$post['number_of_bags']) : null,
      'archive_reference' => !empty($post['archive_reference']) ? $this->clean($post['archive_reference']) : null,
      'ceec_in_date' => !empty($post['ceec_in_date']) && $this->isValidDate($post['ceec_in_date']) ? $post['ceec_in_date'] : null,
      'ceec_out_date' => !empty($post['ceec_out_date']) && $this->isValidDate($post['ceec_out_date']) ? $post['ceec_out_date'] : null,
      'min_div_in_date' => !empty($post['min_div_in_date']) && $this->isValidDate($post['min_div_in_date']) ? $post['min_div_in_date'] : null,
      'min_div_out_date' => !empty($post['min_div_out_date']) && $this->isValidDate($post['min_div_out_date']) ? $post['min_div_out_date'] : null,
      'cgea_doc_ref' => !empty($post['cgea_doc_ref']) ? $this->clean($post['cgea_doc_ref']) : null,
      'segues_rcv_ref' => !empty($post['segues_rcv_ref']) ? $this->clean($post['segues_rcv_ref']) : null,
      'segues_payment_date' => !empty($post['segues_payment_date']) && $this->isValidDate($post['segues_payment_date']) ? $post['segues_payment_date'] : null,
      'document_status' => !empty($post['document_status']) ? $this->toInt($post['document_status']) : null,
      'customs_clearing_code' => !empty($post['customs_clearing_code']) ? $this->clean($post['customs_clearing_code']) : null,
      'dgda_in_date' => !empty($post['dgda_in_date']) && $this->isValidDate($post['dgda_in_date']) ? $post['dgda_in_date'] : null,
      'declaration_reference' => !empty($post['declaration_reference']) ? $this->clean($post['declaration_reference']) : null,
      'liquidation_reference' => !empty($post['liquidation_reference']) ? $this->clean($post['liquidation_reference']) : null,
      'liquidation_date' => !empty($post['liquidation_date']) && $this->isValidDate($post['liquidation_date']) ? $post['liquidation_date'] : null,
      'liquidation_paid_by' => !empty($post['liquidation_paid_by']) ? $this->clean($post['liquidation_paid_by']) : null,
      'liquidation_amount' => !empty($post['liquidation_amount']) && is_numeric($post['liquidation_amount']) ? round((float)$post['liquidation_amount'], 2) : null,
      'quittance_reference' => !empty($post['quittance_reference']) ? $this->clean($post['quittance_reference']) : null,
      'quittance_date' => !empty($post['quittance_date']) && $this->isValidDate($post['quittance_date']) ? $post['quittance_date'] : null,
      'dgda_out_date' => !empty($post['dgda_out_date']) && $this->isValidDate($post['dgda_out_date']) ? $post['dgda_out_date'] : null,
      'gov_docs_in_date' => !empty($post['gov_docs_in_date']) && $this->isValidDate($post['gov_docs_in_date']) ? $post['gov_docs_in_date'] : null,
      'gov_docs_out_date' => !empty($post['gov_docs_out_date']) && $this->isValidDate($post['gov_docs_out_date']) ? $post['gov_docs_out_date'] : null,
      'dispatch_deliver_date' => !empty($post['dispatch_deliver_date']) && $this->isValidDate($post['dispatch_deliver_date']) ? $post['dispatch_deliver_date'] : null,
      'kanyaka_arrival_date' => !empty($post['kanyaka_arrival_date']) && $this->isValidDate($post['kanyaka_arrival_date']) ? $post['kanyaka_arrival_date'] : null,
      'kanyaka_departure_date' => !empty($post['kanyaka_departure_date']) && $this->isValidDate($post['kanyaka_departure_date']) ? $post['kanyaka_departure_date'] : null,
      'border_arrival_date' => !empty($post['border_arrival_date']) && $this->isValidDate($post['border_arrival_date']) ? $post['border_arrival_date'] : null,
      'exit_drc_date' => !empty($post['exit_drc_date']) && $this->isValidDate($post['exit_drc_date']) ? $post['exit_drc_date'] : null,
      'exit_point_id' => !empty($post['exit_point_id']) ? $this->toInt($post['exit_point_id']) : null,
      'end_of_formalities_date' => !empty($post['end_of_formalities_date']) && $this->isValidDate($post['end_of_formalities_date']) ? $post['end_of_formalities_date'] : null,
      'truck_status' => !empty($post['truck_status']) ? $this->toInt($post['truck_status']) : null,
      'lmc_id' => !empty($post['lmc_id']) ? $this->clean($post['lmc_id']) : null,
      'ogefrem_inv_ref' => !empty($post['ogefrem_inv_ref']) ? $this->clean($post['ogefrem_inv_ref']) : null,
      'audited_date' => !empty($post['audited_date']) && $this->isValidDate($post['audited_date']) ? $post['audited_date'] : null,
      'archived_date' => !empty($post['archived_date']) && $this->isValidDate($post['archived_date']) ? $post['archived_date'] : null,
      'lmc_date' => !empty($post['lmc_date']) && $this->isValidDate($post['lmc_date']) ? $post['lmc_date'] : null,
      'ogefrem_date' => !empty($post['ogefrem_date']) && $this->isValidDate($post['ogefrem_date']) ? $post['ogefrem_date'] : null,
      'remarks' => !empty($post['remarks']) ? $this->sanitizeJson($post['remarks']) : null,
      'clearing_status' => !empty($post['clearing_status']) ? $this->toInt($post['clearing_status']) : null,
      'ceec_amount' => !empty($post['ceec_amount']) && is_numeric($post['ceec_amount']) ? round((float)$post['ceec_amount'], 2) : null,
      'cgea_amount' => !empty($post['cgea_amount']) && is_numeric($post['cgea_amount']) ? round((float)$post['cgea_amount'], 2) : null,
      'occ_amount' => !empty($post['occ_amount']) && is_numeric($post['occ_amount']) ? round((float)$post['occ_amount'], 2) : null,
      'lmc_amount' => !empty($post['lmc_amount']) && is_numeric($post['lmc_amount']) ? round((float)$post['lmc_amount'], 2) : null,
      'ogefrem_amount' => !empty($post['ogefrem_amount']) && is_numeric($post['ogefrem_amount']) ? round((float)$post['ogefrem_amount'], 2) : null,
    ];
  }

  private function sanitizeInput($value)
  {
    if (is_array($value)) {
      return array_map([$this, 'sanitizeInput'], $value);
    }
    
    if (!is_string($value)) {
      return $value;
    }
    
    $value = str_replace(chr(0), '', $value);
    $value = trim($value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    
    return $value;
  }

  private function sanitizeJson($jsonString)
  {
    if (empty($jsonString)) return null;
    
    $decoded = json_decode($jsonString, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
      return null;
    }
    
    if (is_array($decoded)) {
      $decoded = $this->sanitizeInput($decoded);
      return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    return null;
  }

  private function clean($value)
  {
    if (empty($value)) return null;
    
    $value = $this->sanitizeInput($value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
    $value = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $value);
    $value = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);
    
    if (strlen($value) > 255) {
      $value = substr($value, 0, 255);
    }
    
    return $value;
  }

  private function toInt($value)
  {
    if (!is_numeric($value)) {
      return null;
    }
    
    $int = (int)$value;
    return $int > 0 ? $int : null;
  }

  private function isValidDate($date)
  {
    if (empty($date)) {
      return false;
    }
    
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
  }

  private function validateCsrfToken()
  {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    
    if (empty($token) || empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
      $this->logError('CSRF token missing or expired', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
      exit;
    }

    if ((time() - $_SESSION['csrf_token_time']) > 3600) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
      exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
      $this->logError('CSRF token validation failed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
      exit;
    }
  }

  private function logError($message, $context = [])
  {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'level' => 'ERROR',
      'message' => $message,
      'user_id' => $_SESSION['user_id'] ?? 'guest',
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
      'context' => $context
    ];
    
    $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
  }

  private function logInfo($message, $context = [])
  {
    $logEntry = [
      'timestamp' => date('Y-m-d H:i:s'),
      'level' => 'INFO',
      'message' => $message,
      'user_id' => $_SESSION['user_id'] ?? 'guest',
      'context' => $context
    ];
    
    $logLine = json_encode($logEntry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
  }
}