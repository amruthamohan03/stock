<?php

class ImportController extends Controller
{
  private $db;
  private $logFile;
  private $allowedFilters = [
    'completed', 
    'in_progress', 
    'in_transit', 
    'crf_missing', 
    'ad_missing', 
    'insurance_missing', 
    'audited_pending', 
    'archived_pending',
    'dgda_in_pending',
    'liquidation_pending',
    'quittance_pending',
    'dgda_out_pending',
    'dispatch_deliver_pending'
  ];
  
  private $bulkUpdateLimit = 500;
  private $dataTablesMaxLength = 500;
  private $maxExportRecords = 10000;
  private $rateLimitStore = [];

  public function __construct()
  {
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/import_operations.log';
    
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

    $sql = "SELECT DISTINCT c.id, c.short_name, c.liquidation_paid_by 
            FROM clients_t c
            INNER JOIN licenses_t l ON c.id = l.client_id
            WHERE c.display = 'Y' 
              AND c.client_type LIKE '%I%'
              AND l.display = 'Y'
              AND l.kind_id IN (1, 2, 5, 6)
            ORDER BY c.short_name ASC";
    $subscribers = $this->db->customQuery($sql) ?: [];

    $regimes = $this->db->selectData('regime_master_t', 'id, regime_name', ['display' => 'Y', 'type' => 'I'], 'regime_name ASC') ?: [];
    $currencies = $this->db->selectData('currency_master_t', 'id, currency_name, currency_short_name', ['display' => 'Y'], 'currency_short_name ASC') ?: [];
    $sub_offices = $this->db->selectData('sub_office_master_t', 'id, sub_office_name', ['display' => 'Y'], 'sub_office_name ASC') ?: [];
    $entry_points = $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['display' => 'Y', 'entry_point' => 'Y'], 'transit_point_name ASC') ?: [];
    $border_warehouses = $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['display' => 'Y', 'warehouse' => 'Y'], 'transit_point_name ASC') ?: [];
    $bonded_warehouses = $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['display' => 'Y', 'warehouse' => 'Y'], 'transit_point_name ASC') ?: [];
    $clearance_types = $this->db->selectData('clearance_master_t', 'id, clearance_name', ['display' => 'Y'], 'clearance_name ASC') ?: [];
    $clearing_statuses = $this->db->selectData('clearing_status_master_t', 'id, clearing_status', ['display' => 'Y'], 'clearing_status ASC') ?: [];
    $document_statuses = $this->db->selectData('document_status_master_t', 'id, document_status', ['display' => 'Y', 'type' => 'I'], 'document_status ASC') ?: [];
    $truck_statuses = $this->db->selectData('truck_status_master_t', 'id, truck_status', ['display' => 'Y'], 'truck_status ASC') ?: [];
    $commodities = $this->db->selectData('commodity_master_t', 'id, commodity_name', ['display' => 'Y'], 'commodity_name ASC') ?: [];
    $transport_modes = $this->db->selectData('transport_mode_master_t', 'id, transport_mode_name', ['display' => 'Y'], 'transport_mode_name ASC') ?: [];
    $type_of_goods = $this->db->selectData('type_of_goods_master_t', 'id, goods_type, goods_short_name', [], 'goods_type ASC');
    $data = [
      'title' => 'Import Management',
      'subscribers' => $this->sanitizeArray($subscribers),
      'regimes' => $this->sanitizeArray($regimes),
      'currencies' => $this->sanitizeArray($currencies),
      'sub_offices' => $this->sanitizeArray($sub_offices),
      'entry_points' => $this->sanitizeArray($entry_points),
      'border_warehouses' => $this->sanitizeArray($border_warehouses),
      'bonded_warehouses' => $this->sanitizeArray($bonded_warehouses),
      'clearance_types' => $this->sanitizeArray($clearance_types),
      'clearing_statuses' => $this->sanitizeArray($clearing_statuses),
      'document_statuses' => $this->sanitizeArray($document_statuses),
      'truck_statuses' => $this->sanitizeArray($truck_statuses),
      'commodities' => $this->sanitizeArray($commodities),
      'transport_modes' => $this->sanitizeArray($transport_modes),
      'type_of_goods' => $this->sanitizeArray($type_of_goods),
      'clearing_based_on_options' => ['IR', 'ARA'],
      'declaration_validity_options' => ['3 MONTHS', '6 MONTHS', '12 MONTHS'],
      'csrf_token' => $_SESSION['csrf_token']
    ];

    $this->viewWithLayout('tracking/imports', $data);
  }

  public function crudData($action = 'insertion')
  {
    // Don't set JSON headers for file download actions
    if ($action !== 'exportImport' && $action !== 'exportAll' && $action !== 'exportBorderTeam') {
      header('Content-Type: application/json');
      header('X-Content-Type-Options: nosniff');
      header('X-Frame-Options: DENY');
      header('X-XSS-Protection: 1; mode=block');
    }

    try {
      switch ($action) {
        case 'insert':
        case 'insertion':
          $this->insertImport();
          break;
        case 'update':
          $this->updateImport();
          break;
        case 'delete':
        case 'deletion':
          $this->deleteImport();
          break;
        case 'details':
        case 'getImport':
          $this->getImport();
          break;
        case 'listing':
          $this->listImports();
          break;
        case 'statistics':
          $this->getStatistics();
          break;
        case 'getLicenses':
          $this->getLicenses();
          break;
        case 'getLicenseDetails':
          $this->getLicenseDetails();
          break;
        case 'getNextMCASequence':
          $this->getNextMCASequence();
          break;
        case 'getClearingStatusIds':
          $this->getClearingStatusIds();
          break;
        case 'exportImport':
          $this->exportImport();
          break;
        case 'exportAll':
          $this->exportAllImports();
          break;
        case 'exportBorderTeam':
          $this->exportBorderTeam();
          break;
        case 'getBulkUpdateData':
          $this->getBulkUpdateData();
          break;
        case 'bulkUpdate':
          $this->bulkUpdate();
          break;
        case 'getPartielleOptions':
          $this->getPartielleOptions();
          break;
        case 'getPartielleAvailability':
          $this->getPartielleAvailability();
          break;
        case 'getPartielleDetails':
          $this->getPartielleDetails();
          break;
        case 'getPartielleManagement':
        case 'getPartielleManagementData':
          $this->getPartielleManagement();
          break;
        case 'createPartielle':
          $this->createPartielle();
          break;
        case 'updatePartielle':
          $this->updatePartielle();
          break;
        case 'deletePartielle':
          $this->deletePartielle();
          break;
        case 'getFilesForPartielle':
          $this->getFilesForPartielle();
          break;
        case 'getCommodities':
          $this->getCommodities();
          break;
        case 'createCommodity':
          $this->createCommodity();
          break;
        case 'getPartielleForLicense':
          $this->getPartielleForLicense();
          break;
        case 'checkHorse':
          $this->checkHorse();
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
      
      if ($action !== 'exportImport' && $action !== 'exportAll' && $action !== 'exportBorderTeam') {
        echo json_encode(['success' => false, 'message' => 'Server error occurred. Please try again.']);
      }
    }
    
    if ($action !== 'exportImport' && $action !== 'exportAll' && $action !== 'exportBorderTeam') {
      exit;
    }
  }

  // ========================================
  // RATE LIMITING
  // ========================================
  
  private function isRateLimited()
  {
    $userId = $_SESSION['user_id'] ?? 'guest';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $userId . '_' . $ipAddress;
    
    $currentTime = time();
    $timeWindow = 900;
    $maxRequests = 100;
    
    if (!isset($this->rateLimitStore[$key])) {
      $this->rateLimitStore[$key] = [];
    }
    
    $this->rateLimitStore[$key] = array_filter($this->rateLimitStore[$key], function($timestamp) use ($currentTime, $timeWindow) {
      return ($currentTime - $timestamp) < $timeWindow;
    });
    
    if (count($this->rateLimitStore[$key]) >= $maxRequests) {
      $this->logError('Rate limit exceeded', [
        'user_id' => $userId,
        'ip' => $ipAddress,
        'requests' => count($this->rateLimitStore[$key])
      ]);
      return true;
    }
    
    $this->rateLimitStore[$key][] = $currentTime;
    
    return false;
  }

  // ========================================
  // COMMODITY FUNCTIONS
  // ========================================
  
  private function getCommodities()
  {
    try {
      $sql = "SELECT id, commodity_name 
              FROM commodity_master_t 
              WHERE display = 'Y'
              ORDER BY commodity_name ASC";

      $commodities = $this->db->customQuery($sql);
      $commodities = $this->sanitizeArray($commodities);

      echo json_encode([
        'success' => true,
        'data' => $commodities ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get commodities', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load commodities']);
    }
  }

  private function createCommodity()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $commodityName = $this->sanitizeInput($_POST['commodity_name'] ?? '');

      if (empty($commodityName)) {
        echo json_encode(['success' => false, 'message' => 'Commodity name is required']);
        return;
      }

      if (strlen($commodityName) > 255) {
        echo json_encode(['success' => false, 'message' => 'Commodity name cannot exceed 255 characters']);
        return;
      }

      $existing = $this->db->selectData('commodity_master_t', 'id', ['commodity_name' => $commodityName, 'display' => 'Y']);
      
      if (!empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'This commodity already exists']);
        return;
      }

      $data = [
        'commodity_name' => $commodityName,
        'created_by' => (int)($_SESSION['user_id'] ?? 1),
        'display' => 'Y'
      ];

      $insertId = $this->db->insertData('commodity_master_t', $data);

      if ($insertId) {
        $this->logInfo('Commodity created successfully', ['commodity_id' => $insertId, 'name' => $commodityName]);
        echo json_encode([
          'success' => true,
          'message' => 'Commodity created successfully!',
          'id' => $insertId,
          'commodity_name' => htmlspecialchars($commodityName, ENT_QUOTES, 'UTF-8')
        ]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create commodity']);
      }

    } catch (Exception $e) {
      $this->logError('Exception during commodity creation', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while creating commodity']);
    }
  }

  // ========================================
  // PARTIELLE FUNCTIONS
  // ========================================

  private function getPartielleForLicense()
  {
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'License ID required']);
        return;
      }

      $sql = "SELECT 
                p.id, 
                p.partial_name,
                p.partial_weight,
                p.partial_fob,
                (SELECT COALESCE(SUM(i.weight), 0) 
                 FROM imports_t i 
                 WHERE i.inspection_reports = p.partial_name 
                 AND i.display = 'Y') as weight_used,
                (SELECT COALESCE(SUM(i.fob), 0) 
                 FROM imports_t i 
                 WHERE i.inspection_reports = p.partial_name 
                 AND i.display = 'Y') as fob_used
              FROM partial_t p
              WHERE p.license_id = :license_id 
              AND p.display = 'Y'
              ORDER BY p.partial_name ASC";

      $partielles = $this->db->customQuery($sql, [':license_id' => $licenseId]);

      if (!empty($partielles)) {
        foreach ($partielles as &$partielle) {
          $partielle['remaining_weight'] = max(0, (float)$partielle['partial_weight'] - (float)$partielle['weight_used']);
          $partielle['remaining_fob'] = max(0, (float)$partielle['partial_fob'] - (float)$partielle['fob_used']);
        }
      }

      $partielles = $this->sanitizeArray($partielles);

      echo json_encode([
        'success' => true,
        'data' => $partielles ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get PARTIELLE for license', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load PARTIELLE options']);
    }
  }

  private function getPartielleOptions()
  {
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'License ID required']);
        return;
      }

      $sql = "SELECT 
                p.id, 
                p.partial_name, 
                c.short_name as client_name,
                p.partial_weight, 
                p.partial_fob,
                p.license_weight,
                p.license_fob,
                (p.license_weight - p.partial_weight) as available_weight,
                (p.license_fob - p.partial_fob) as available_fob,
                (SELECT COUNT(*) 
                 FROM imports_t i 
                 WHERE i.inspection_reports = p.partial_name 
                 AND i.display = 'Y') as no_of_files,
                (SELECT COALESCE(SUM(i.weight), 0) 
                 FROM imports_t i 
                 WHERE i.inspection_reports = p.partial_name 
                 AND i.display = 'Y') as weight_in_files,
                (SELECT COALESCE(SUM(i.fob), 0) 
                 FROM imports_t i 
                 WHERE i.inspection_reports = p.partial_name 
                 AND i.display = 'Y') as fob_in_files
              FROM partial_t p
              LEFT JOIN clients_t c ON p.client_id = c.id
              WHERE p.license_id = :license_id 
              AND p.display = 'Y'
              ORDER BY p.created_at DESC";

      $partials = $this->db->customQuery($sql, [':license_id' => $licenseId]);
      $partials = $this->sanitizeArray($partials);

      echo json_encode([
        'success' => true,
        'data' => $partials ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get PARTIELLE options', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load PARTIELLE options']);
    }
  }

  private function getPartielleAvailability()
  {
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'License ID required']);
        return;
      }

      $licenseSql = "SELECT 
                      l.id,
                      l.weight as license_weight,
                      l.fob_declared as license_fob,
                      l.insurance as license_insurance,
                      l.freight as license_freight,
                      l.other_costs as license_other_costs,
                      l.ref_cod as crf_reference
                     FROM licenses_t l
                     WHERE l.id = :license_id 
                     AND l.display = 'Y'
                     LIMIT 1";
      
      $licenseData = $this->db->customQuery($licenseSql, [':license_id' => $licenseId]);

      if (empty($licenseData)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $license = $licenseData[0];

      $partialSql = "SELECT 
                      COALESCE(SUM(partial_weight), 0) as total_used_weight,
                      COALESCE(SUM(partial_fob), 0) as total_used_fob,
                      COALESCE(SUM(partial_insurance), 0) as total_used_insurance,
                      COALESCE(SUM(partial_freight), 0) as total_used_freight,
                      COALESCE(SUM(partial_other_costs), 0) as total_used_other_costs
                     FROM partial_t
                     WHERE license_id = :license_id 
                     AND display = 'Y'";
      
      $partialData = $this->db->customQuery($partialSql, [':license_id' => $licenseId]);

      $usedWeight = !empty($partialData) ? (float)($partialData[0]['total_used_weight'] ?? 0) : 0;
      $usedFob = !empty($partialData) ? (float)($partialData[0]['total_used_fob'] ?? 0) : 0;
      $usedInsurance = !empty($partialData) ? (float)($partialData[0]['total_used_insurance'] ?? 0) : 0;
      $usedFreight = !empty($partialData) ? (float)($partialData[0]['total_used_freight'] ?? 0) : 0;
      $usedOtherCosts = !empty($partialData) ? (float)($partialData[0]['total_used_other_costs'] ?? 0) : 0;

      $licenseWeight = (float)($license['license_weight'] ?? 0);
      $licenseFob = (float)($license['license_fob'] ?? 0);
      $licenseInsurance = (float)($license['license_insurance'] ?? 0);
      $licenseFreight = (float)($license['license_freight'] ?? 0);
      $licenseOtherCosts = (float)($license['license_other_costs'] ?? 0);

      $availableWeight = max(0, $licenseWeight - $usedWeight);
      $availableFob = max(0, $licenseFob - $usedFob);
      $availableInsurance = max(0, $licenseInsurance - $usedInsurance);
      $availableFreight = max(0, $licenseFreight - $usedFreight);
      $availableOtherCosts = max(0, $licenseOtherCosts - $usedOtherCosts);

      echo json_encode([
        'success' => true,
        'data' => [
          'license_weight' => round($licenseWeight, 2),
          'license_fob' => round($licenseFob, 2),
          'license_insurance' => round($licenseInsurance, 2),
          'license_freight' => round($licenseFreight, 2),
          'license_other_costs' => round($licenseOtherCosts, 2),
          'available_weight' => round($availableWeight, 2),
          'available_fob' => round($availableFob, 2),
          'available_insurance' => round($availableInsurance, 2),
          'available_freight' => round($availableFreight, 2),
          'available_other_costs' => round($availableOtherCosts, 2),
          'crf_reference' => $license['crf_reference'] ?? ''
        ]
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get PARTIELLE availability', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load license details']);
    }
  }

  private function getPartielleManagement()
  {
    try {
      $licenseId = (int)($_GET['license_id'] ?? 0);
      $subscriberId = (int)($_GET['subscriber_id'] ?? 0);

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'License ID required']);
        return;
      }

      $licenseSql = "SELECT 
                      l.id,
                      l.weight,
                      l.fob_declared,
                      l.ref_cod
                     FROM licenses_t l
                     WHERE l.id = :license_id 
                     AND l.display = 'Y'
                     LIMIT 1";
      
      $licenseData = $this->db->customQuery($licenseSql, [':license_id' => $licenseId]);

      if (empty($licenseData)) {
        echo json_encode([
          'success' => false, 
          'message' => 'License not found. License ID: ' . $licenseId
        ]);
        return;
      }

      $license = $licenseData[0];
      $licenseWeight = (float)($license['weight'] ?? 0);
      $licenseFob = (float)($license['fob_declared'] ?? 0);
      $crfReference = $license['ref_cod'] ?? '';

      $partiellesSql = "SELECT 
                      p.id,
                      p.partial_name,
                      p.license_id,
                      p.partial_weight,
                      p.partial_fob,
                      l.ref_cod as crf_reference,
                      l.weight as license_weight,
                      l.fob_declared as license_fob,
                      (SELECT COUNT(*) 
                       FROM imports_t i 
                       WHERE i.inspection_reports = p.partial_name 
                       AND i.display = 'Y') as no_of_files,
                      (SELECT COALESCE(SUM(i.weight), 0) 
                       FROM imports_t i 
                       WHERE i.inspection_reports = p.partial_name 
                       AND i.display = 'Y') as weight_used,
                      (SELECT COALESCE(SUM(i.fob), 0) 
                       FROM imports_t i 
                       WHERE i.inspection_reports = p.partial_name 
                       AND i.display = 'Y') as fob_used
                    FROM partial_t p
                    INNER JOIN licenses_t l ON p.license_id = l.id
                    WHERE p.license_id = :license_id 
                    AND p.display = 'Y'
                    ORDER BY p.created_at DESC";
      
      $partielles = $this->db->customQuery($partiellesSql, [':license_id' => $licenseId]);

      $totalUsedWeight = 0;
      $totalUsedFob = 0;

      if (!empty($partielles)) {
        foreach ($partielles as $p) {
          $totalUsedWeight += (float)($p['partial_weight'] ?? 0);
          $totalUsedFob += (float)($p['partial_fob'] ?? 0);
        }
      }

      $availableWeight = max(0, $licenseWeight - $totalUsedWeight);
      $availableFob = max(0, $licenseFob - $totalUsedFob);

      $partielles = $this->sanitizeArray($partielles ?? []);

      echo json_encode([
        'success' => true,
        'data' => [
          'license_weight' => round($licenseWeight, 2),
          'license_fob' => round($licenseFob, 2),
          'available_weight' => round($availableWeight, 2),
          'available_fob' => round($availableFob, 2),
          'crf_reference' => $crfReference,
          'total_used_weight' => round($totalUsedWeight, 2),
          'total_used_fob' => round($totalUsedFob, 2),
          'partielles' => $partielles
        ]
      ]);

      $this->logInfo('PARTIELLE management data loaded', [
        'license_id' => $licenseId,
        'partielle_count' => count($partielles)
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get PARTIELLE management data', [
        'license_id' => $_GET['license_id'] ?? null,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode([
        'success' => false, 
        'message' => 'Failed to load PARTIELLE management data: ' . $e->getMessage()
      ]);
    }
  }

  private function getPartielleDetails()
  {
    try {
      $partialName = $this->sanitizeInput($_GET['partial_name'] ?? '');

      if (empty($partialName)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE name required']);
        return;
      }

      $sql = "SELECT 
                p.id,
                p.partial_name,
                p.license_id,
                p.client_id,
                p.partial_weight,
                p.partial_fob,
                p.license_weight,
                p.license_fob,
                l.ref_cod as crf_reference
              FROM partial_t p
              LEFT JOIN licenses_t l ON p.license_id = l.id
              WHERE p.partial_name = :partial_name 
              AND p.display = 'Y'
              LIMIT 1";
      
      $partial = $this->db->customQuery($sql, [':partial_name' => $partialName]);

      if (empty($partial)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE not found']);
        return;
      }

      $partialData = $partial[0];

      $usageSql = "SELECT 
                    COALESCE(SUM(weight), 0) as weight_in_files,
                    COALESCE(SUM(fob), 0) as fob_in_files
                   FROM imports_t
                   WHERE inspection_reports = :partial_name 
                   AND display = 'Y'";
      
      $usage = $this->db->customQuery($usageSql, [':partial_name' => $partialName]);

      $weightInFiles = !empty($usage) ? (float)($usage[0]['weight_in_files'] ?? 0) : 0;
      $fobInFiles = !empty($usage) ? (float)($usage[0]['fob_in_files'] ?? 0) : 0;

      $licenseId = (int)$partialData['license_id'];
      $totalAllocatedSql = "SELECT 
                             COALESCE(SUM(partial_weight), 0) as total_allocated_weight,
                             COALESCE(SUM(partial_fob), 0) as total_allocated_fob
                            FROM partial_t
                            WHERE license_id = :license_id 
                            AND display = 'Y'";
      
      $totalAllocated = $this->db->customQuery($totalAllocatedSql, [':license_id' => $licenseId]);

      $totalAllocatedWeight = !empty($totalAllocated) ? (float)($totalAllocated[0]['total_allocated_weight'] ?? 0) : 0;
      $totalAllocatedFob = !empty($totalAllocated) ? (float)($totalAllocated[0]['total_allocated_fob'] ?? 0) : 0;

      $licenseWeight = (float)($partialData['license_weight'] ?? 0);
      $licenseFob = (float)($partialData['license_fob'] ?? 0);
      $partialWeight = (float)($partialData['partial_weight'] ?? 0);
      $partialFob = (float)($partialData['partial_fob'] ?? 0);

      $availableLicenseWeight = max(0, $licenseWeight - $totalAllocatedWeight);
      $availableLicenseFob = max(0, $licenseFob - $totalAllocatedFob);

      $remainingWeight = max(0, $partialWeight - $weightInFiles);
      $remainingFob = max(0, $partialFob - $fobInFiles);

      $sanitizedData = $this->sanitizeArray([$partialData])[0];

      echo json_encode([
        'success' => true,
        'data' => [
          'id' => (int)$sanitizedData['id'],
          'partial_name' => $sanitizedData['partial_name'],
          'license_id' => (int)$sanitizedData['license_id'],
          'client_id' => !empty($sanitizedData['client_id']) ? (int)$sanitizedData['client_id'] : null,
          'crf_reference' => $sanitizedData['crf_reference'] ?? '',
          'license_weight' => round($licenseWeight, 2),
          'license_fob' => round($licenseFob, 2),
          'partial_weight' => round($partialWeight, 2),
          'partial_fob' => round($partialFob, 2),
          'weight_in_files' => round($weightInFiles, 2),
          'fob_in_files' => round($fobInFiles, 2),
          'available_license_weight' => round($availableLicenseWeight, 2),
          'available_license_fob' => round($availableLicenseFob, 2),
          'remaining_weight' => round($remainingWeight, 2),
          'remaining_fob' => round($remainingFob, 2)
        ]
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get PARTIELLE details', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load PARTIELLE details']);
    }
  }

  private function createPartielle()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $partialName = $this->sanitizeInput($_POST['partial_name'] ?? '');
      $subscriberId = !empty($_POST['subscriber_id']) ? (int)$_POST['subscriber_id'] : null;
      $licenseId = (int)($_POST['license_id'] ?? 0);
      $clientId = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
      
      $partialWeight = isset($_POST['partial_weight']) && is_numeric($_POST['partial_weight']) 
        ? round((float)$_POST['partial_weight'], 2) 
        : 0.00;
      $partialFob = isset($_POST['partial_fob']) && is_numeric($_POST['partial_fob']) 
        ? round((float)$_POST['partial_fob'], 2) 
        : 0.00;

      if (empty($partialName)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE name is required']);
        return;
      }

      if (strlen($partialName) > 100) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE name cannot exceed 100 characters']);
        return;
      }

      if ($licenseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'License ID is required']);
        return;
      }

      $existing = $this->db->selectData('partial_t', 'id', ['partial_name' => $partialName, 'display' => 'Y']);
      
      if (!empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'This PARTIELLE already exists']);
        return;
      }

      $licenseSql = "SELECT weight, fob_declared, insurance, freight, other_costs, client_id
                     FROM licenses_t
                     WHERE id = :license_id 
                     AND display = 'Y'
                     LIMIT 1";
      
      $licenseData = $this->db->customQuery($licenseSql, [':license_id' => $licenseId]);

      if (empty($licenseData)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $license = $licenseData[0];
      
      if ($clientId === null && $subscriberId !== null) {
        $clientId = $subscriberId;
      }
      
      if ($clientId === null) {
        $clientId = !empty($license['client_id']) ? (int)$license['client_id'] : null;
      }

      $data = [
        'partial_name' => $partialName,
        'license_id' => $licenseId,
        'client_id' => $clientId,
        
        'license_weight' => round((float)($license['weight'] ?? 0), 2),
        'license_fob' => round((float)($license['fob_declared'] ?? 0), 2),
        'license_insurance' => round((float)($license['insurance'] ?? 0), 2),
        'license_freight' => round((float)($license['freight'] ?? 0), 2),
        'license_other_costs' => round((float)($license['other_costs'] ?? 0), 2),
        
        'partial_weight' => $partialWeight,
        'partial_fob' => $partialFob,
        'partial_insurance' => 0.00,
        'partial_freight' => 0.00,
        'partial_other_costs' => 0.00,
        
        'created_by' => (int)($_SESSION['user_id'] ?? 1),
        'display' => 'Y'
      ];

      $insertId = $this->db->insertData('partial_t', $data);

      if ($insertId) {
        $this->logInfo('PARTIELLE created with client_id', [
          'partial_id' => $insertId, 
          'name' => $partialName,
          'license_id' => $licenseId,
          'client_id' => $clientId,
          'partial_weight' => $partialWeight,
          'partial_fob' => $partialFob
        ]);
        
        echo json_encode([
          'success' => true,
          'message' => 'PARTIELLE created successfully!',
          'id' => $insertId,
          'partial_name' => htmlspecialchars($partialName, ENT_QUOTES, 'UTF-8')
        ]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create PARTIELLE']);
      }

    } catch (Exception $e) {
      $this->logError('Exception during PARTIELLE creation', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while creating PARTIELLE']);
    }
  }

  private function updatePartielle()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $partialId = (int)($_POST['id'] ?? 0);
      $partialWeight = isset($_POST['partial_weight']) && is_numeric($_POST['partial_weight']) 
        ? round((float)$_POST['partial_weight'], 2) 
        : null;
      $partialFob = isset($_POST['partial_fob']) && is_numeric($_POST['partial_fob']) 
        ? round((float)$_POST['partial_fob'], 2) 
        : null;

      if ($partialId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid PARTIELLE ID']);
        return;
      }

      if ($partialWeight === null || $partialFob === null) {
        echo json_encode(['success' => false, 'message' => 'Weight and FOB are required']);
        return;
      }

      $existing = $this->db->selectData('partial_t', '*', ['id' => $partialId, 'display' => 'Y']);
      
      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE not found']);
        return;
      }

      $partialData = $existing[0];
      $partialName = $partialData['partial_name'];

      $usageSql = "SELECT COUNT(*) as count 
                   FROM imports_t 
                   WHERE inspection_reports = :partial_name 
                   AND display = 'Y'";
      
      $usageResult = $this->db->customQuery($usageSql, [':partial_name' => $partialName]);
      $inUse = !empty($usageResult) && (int)($usageResult[0]['count'] ?? 0) > 0;

      if ($inUse) {
        $usedSql = "SELECT 
                      COALESCE(SUM(weight), 0) as weight_in_files,
                      COALESCE(SUM(fob), 0) as fob_in_files
                     FROM imports_t
                     WHERE inspection_reports = :partial_name 
                     AND display = 'Y'";
        
        $used = $this->db->customQuery($usedSql, [':partial_name' => $partialName]);
        
        $weightInFiles = !empty($used) ? (float)($used[0]['weight_in_files'] ?? 0) : 0;
        $fobInFiles = !empty($used) ? (float)($used[0]['fob_in_files'] ?? 0) : 0;

        if ($partialWeight < $weightInFiles) {
          echo json_encode([
            'success' => false, 
            'message' => "Cannot reduce weight below used amount ({$weightInFiles} KG already used in imports)"
          ]);
          return;
        }

        if ($partialFob < $fobInFiles) {
          echo json_encode([
            'success' => false, 
            'message' => "Cannot reduce FOB below used amount (\${$fobInFiles} already used in imports)"
          ]);
          return;
        }
      }

      $licenseId = (int)$partialData['license_id'];
      $currentPartialWeight = (float)($partialData['partial_weight'] ?? 0);
      $currentPartialFob = (float)($partialData['partial_fob'] ?? 0);

      $licenseSql = "SELECT weight, fob_declared FROM licenses_t WHERE id = :license_id LIMIT 1";
      $license = $this->db->customQuery($licenseSql, [':license_id' => $licenseId]);
      
      if (empty($license)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $licenseWeight = (float)($license[0]['weight'] ?? 0);
      $licenseFob = (float)($license[0]['fob_declared'] ?? 0);

      $otherAllocatedSql = "SELECT 
                             COALESCE(SUM(partial_weight), 0) as other_weight,
                             COALESCE(SUM(partial_fob), 0) as other_fob
                            FROM partial_t
                            WHERE license_id = :license_id 
                            AND id != :partial_id
                            AND display = 'Y'";
      
      $otherAllocated = $this->db->customQuery($otherAllocatedSql, [
        ':license_id' => $licenseId,
        ':partial_id' => $partialId
      ]);

      $otherWeight = !empty($otherAllocated) ? (float)($otherAllocated[0]['other_weight'] ?? 0) : 0;
      $otherFob = !empty($otherAllocated) ? (float)($otherAllocated[0]['other_fob'] ?? 0) : 0;

      $availableWeight = $licenseWeight - $otherWeight;
      $availableFob = $licenseFob - $otherFob;

      if ($partialWeight > $availableWeight) {
        echo json_encode([
          'success' => false, 
          'message' => "Weight exceeds available license capacity: {$availableWeight} KG available"
        ]);
        return;
      }

      if ($partialFob > $availableFob) {
        echo json_encode([
          'success' => false, 
          'message' => "FOB exceeds available license capacity: \${$availableFob} available"
        ]);
        return;
      }

      $updateData = [
        'partial_weight' => $partialWeight,
        'partial_fob' => $partialFob,
        'updated_by' => (int)($_SESSION['user_id'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s')
      ];

      $success = $this->db->updateData('partial_t', $updateData, ['id' => $partialId]);

      if ($success) {
        $this->logInfo('PARTIELLE updated successfully', [
          'partial_id' => $partialId,
          'partial_name' => $partialName,
          'old_weight' => $currentPartialWeight,
          'new_weight' => $partialWeight,
          'old_fob' => $currentPartialFob,
          'new_fob' => $partialFob
        ]);

        echo json_encode([
          'success' => true,
          'message' => 'PARTIELLE updated successfully!'
        ]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update PARTIELLE']);
      }

    } catch (Exception $e) {
      $this->logError('Exception during PARTIELLE update', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while updating PARTIELLE']);
    }
  }

  private function deletePartielle()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $partialId = (int)($_POST['partial_id'] ?? 0);

      if ($partialId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid PARTIELLE ID']);
        return;
      }

      $partielle = $this->db->selectData('partial_t', 'partial_name', ['id' => $partialId, 'display' => 'Y']);
      
      if (empty($partielle)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE not found']);
        return;
      }

      $partialName = $partielle[0]['partial_name'];

      $usageSql = "SELECT COUNT(*) as count 
                   FROM imports_t 
                   WHERE inspection_reports = :partial_name 
                   AND display = 'Y'";
      
      $usageResult = $this->db->customQuery($usageSql, [':partial_name' => $partialName]);
      $inUseCount = !empty($usageResult) ? (int)($usageResult[0]['count'] ?? 0) : 0;

      if ($inUseCount > 0) {
        echo json_encode([
          'success' => false, 
          'message' => "Cannot delete PARTIELLE: {$inUseCount} import(s) are using this PARTIELLE. Please remove or reassign those imports first."
        ]);
        return;
      }

      $success = $this->db->updateData('partial_t', [
        'display' => 'N',
        'updated_by' => (int)($_SESSION['user_id'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s')
      ], ['id' => $partialId]);

      if ($success) {
        $this->logInfo('PARTIELLE deleted successfully', [
          'partial_id' => $partialId,
          'partial_name' => $partialName
        ]);

        echo json_encode([
          'success' => true,
          'message' => 'PARTIELLE deleted successfully!'
        ]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete PARTIELLE']);
      }

    } catch (Exception $e) {
      $this->logError('Exception during PARTIELLE deletion', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while deleting PARTIELLE']);
    }
  }

  private function getFilesForPartielle()
  {
    try {
      $partialName = $this->sanitizeInput($_GET['partial_name'] ?? '');

      if (empty($partialName)) {
        echo json_encode(['success' => false, 'message' => 'PARTIELLE name required']);
        return;
      }

      $sql = "SELECT 
                i.id,
                i.mca_ref,
                i.inspection_reports,
                i.declaration_reference,
                i.dgda_in_date,
                i.liquidation_reference,
                i.liquidation_date,
                i.quittance_reference,
                i.quittance_date,
                i.weight,
                i.fob,
                i.pre_alert_date,
                c.short_name as client_name,
                l.license_number
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id
              LEFT JOIN licenses_t l ON i.license_id = l.id
              WHERE i.inspection_reports = :partial_name 
              AND i.display = 'Y'
              ORDER BY i.dgda_in_date DESC, i.id DESC";
      
      $files = $this->db->customQuery($sql, [':partial_name' => $partialName]);

      if (empty($files)) {
        echo json_encode([
          'success' => true,
          'data' => [
            'files' => [],
            'total_weight' => 0.00,
            'total_fob' => 0.00
          ],
          'message' => 'No files found using this PARTIELLE'
        ]);
        return;
      }

      $totalWeight = 0;
      $totalFob = 0;

      foreach ($files as $file) {
        $totalWeight += (float)($file['weight'] ?? 0);
        $totalFob += (float)($file['fob'] ?? 0);
      }

      $files = $this->sanitizeArray($files);

      echo json_encode([
        'success' => true,
        'data' => [
          'files' => $files,
          'total_weight' => round($totalWeight, 2),
          'total_fob' => round($totalFob, 2)
        ]
      ]);

      $this->logInfo('Files for PARTIELLE retrieved', [
        'partial_name' => $partialName,
        'file_count' => count($files),
        'total_weight' => round($totalWeight, 2),
        'total_fob' => round($totalFob, 2)
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get files for PARTIELLE', [
        'partial_name' => $_GET['partial_name'] ?? null,
        'error' => $e->getMessage()
      ]);
      echo json_encode([
        'success' => false, 
        'message' => 'Failed to load files for PARTIELLE: ' . $e->getMessage()
      ]);
    }
  }

  // ========================================
  // IMPORT CRUD FUNCTIONS
  // ========================================

  private function insertImport()
  {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          echo json_encode(['success' => false, 'message' => 'Invalid request method']);
          return;
      }

      $this->validateCsrfToken();

      try {
          $validation = $this->validateImportData($_POST);
          if (!$validation['success']) {
              echo json_encode($validation);
              return;
          }

          $data = $this->prepareImportData($_POST);

          $data['created_by'] = (int)($_SESSION['user_id'] ?? 1);
          $data['updated_by'] = (int)($_SESSION['user_id'] ?? 1);
          $data['display'] = 'Y';

          $insertId = $this->db->insertData('imports_t', $data);

          if ($insertId) {
              $this->logInfo('Import created successfully', ['import_id' => $insertId]);
              echo json_encode([
                  'success' => true,
                  'message' => 'Import created successfully!',
                  'id'      => $insertId
              ]);
          } else {
              echo json_encode([
                  'success' => false,
                  'message' => 'Failed to save import.'
              ]);
          }

      } catch (PDOException $e) {

          // 🔐 UNIQUE KEY violation (Duplicate entry)
          if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {

              $this->logError('Duplicate import entry', [
                  'horse'          => $_POST['horse'] ?? null,
                  'weight'         => $_POST['weight'] ?? null,
                  'fob'            => $_POST['fob'] ?? null,
                  'invoice_number' => $_POST['invoice_number'] ?? null
              ]);

              echo json_encode([
                  'success' => false,
                  'message' => 'Duplicate entry: Horse, Weight, FOB and Invoice Number already exist.'
              ]);
              return;
          }

          // Other DB errors
          $this->logError('Database error during import insert', [
              'error' => $e->getMessage()
          ]);

          echo json_encode([
              'success' => false,
              'message' => 'Database error occurred while saving.'
          ]);

      } catch (Exception $e) {

          $this->logError('Exception during import insert', [
              'error' => $e->getMessage()
          ]);

          echo json_encode([
              'success' => false,
              'message' => 'An error occurred while saving.'
          ]);
      }
  }


  private function updateImport()
  {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          echo json_encode(['success' => false, 'message' => 'Invalid request method']);
          return;
      }

      $this->validateCsrfToken();

      try {
          $importId = (int)($_POST['import_id'] ?? 0);
          if ($importId <= 0) {
              echo json_encode(['success' => false, 'message' => 'Invalid import ID']);
              return;
          }

          $existing = $this->db->selectData('imports_t', '*', [
              'id' => $importId,
              'display' => 'Y'
          ]);

          if (empty($existing)) {
              echo json_encode(['success' => false, 'message' => 'Import not found']);
              return;
          }

          $validation = $this->validateImportData($_POST, $importId);
          if (!$validation['success']) {
              echo json_encode($validation);
              return;
          }

          $data = $this->prepareImportData($_POST);
          $data['updated_by'] = (int)($_SESSION['user_id'] ?? 1);
          $data['updated_at'] = date('Y-m-d H:i:s');

          $this->logInfo('Starting import update', [
              'import_id' => $importId,
              'data_keys' => array_keys($data)
          ]);

          /** ---------------- SAFE UPDATE ---------------- */
          $safeData = $data;
          unset($safeData['rem_weight'], $safeData['r_fob'], $safeData['r_fob_currency']);

          try {
              $this->db->updateData('imports_t', $safeData, ['id' => $importId]);
          } catch (PDOException $e) {

              // 🔐 UNIQUE KEY violation
              if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                  $this->logError('Duplicate import update', [
                      'import_id' => $importId,
                      'horse' => $_POST['horse'] ?? null,
                      'weight' => $_POST['weight'] ?? null,
                      'fob' => $_POST['fob'] ?? null,
                      'invoice_number' => $_POST['invoice_number'] ?? null
                  ]);

                  echo json_encode([
                      'success' => false,
                      'message' => 'Duplicate entry: Horse, Weight, FOB and Invoice Number already exist.'
                  ]);
                  return;
              }

              throw $e;
          }

          /** ---------------- NEW FIELDS UPDATE ---------------- */
          if (isset($data['rem_weight']) || isset($data['r_fob']) || isset($data['r_fob_currency'])) {

              $updateParts = [];
              $params = [':id' => $importId];

              if (isset($data['rem_weight'])) {
                  $updateParts[] = "rem_weight = :rem_weight";
                  $params[':rem_weight'] = $data['rem_weight'];
              }

              if (isset($data['r_fob'])) {
                  $updateParts[] = "r_fob = :r_fob";
                  $params[':r_fob'] = $data['r_fob'];
              }

              if (isset($data['r_fob_currency'])) {
                  $updateParts[] = "r_fob_currency = :r_fob_currency";
                  $params[':r_fob_currency'] = $data['r_fob_currency'];
              }

              if (!empty($updateParts)) {
                  try {
                      $sql = "UPDATE imports_t SET " . implode(', ', $updateParts) . " WHERE id = :id";
                      $this->db->customQuery($sql, $params);
                  } catch (PDOException $e) {

                      // 🔐 UNIQUE KEY violation (edge case)
                      if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                          echo json_encode([
                              'success' => false,
                              'message' => 'Duplicate entry detected while updating remaining values.'
                          ]);
                          return;
                      }

                      // Ignore non-critical failure
                      $this->logError('New fields update failed', ['error' => $e->getMessage()]);
                  }
              }
          }

          $this->logInfo('Import updated successfully', ['import_id' => $importId]);
          echo json_encode([
              'success' => true,
              'message' => 'Import updated successfully!'
          ]);

      } catch (Exception $e) {
          $this->logError('Exception during import update', [
              'error' => $e->getMessage(),
              'trace' => $e->getTraceAsString()
          ]);

          echo json_encode([
              'success' => false,
              'message' => 'An error occurred while updating.'
          ]);
      }
  }

  private function deleteImport()
  {
    $this->validateCsrfToken();

    try {
      $importId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

      if ($importId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid import ID']);
        return;
      }

      $import = $this->db->selectData('imports_t', '*', ['id' => $importId, 'display' => 'Y']);
      if (empty($import)) {
        echo json_encode(['success' => false, 'message' => 'Import not found']);
        return;
      }

      $success = $this->db->updateData('imports_t', [
        'display' => 'N',
        'updated_by' => (int)($_SESSION['user_id'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s')
      ], ['id' => $importId]);

      if ($success) {
        $this->logInfo('Import deleted successfully', ['import_id' => $importId]);
        echo json_encode(['success' => true, 'message' => 'Import deleted successfully!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete import']);
      }
    } catch (Exception $e) {
      $this->logError('Exception during import delete', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred while deleting.']);
    }
  }

  private function getImport()
  {
    try {
      $importId = (int)($_GET['id'] ?? 0);

      if ($importId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid import ID']);
        return;
      }

      $sql = "SELECT i.*, 
                     c.short_name as subscriber_name, 
                     c.liquidation_paid_by as client_liquidation_paid_by, 
                     l.license_number,
                     l.invoice_number as license_invoice_number,
                     k.kind_name,
                     tg.goods_type as type_of_goods_name,
                     tm.transport_mode_name,
                     curr.currency_short_name as currency_name,
                     (l.weight-(SELECT SUM(weight) FROM imports_t WHERE license_id=i.license_id)) as rem_weight,
                     (l.fob_declared -(SELECT SUM(fob) FROM imports_t WHERE license_id=i.license_id))as r_fob
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id
              LEFT JOIN licenses_t l ON i.license_id = l.id
              LEFT JOIN kind_master_t k ON i.kind = k.id
              LEFT JOIN type_of_goods_master_t tg ON i.type_of_goods = tg.id
              LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
              LEFT JOIN currency_master_t curr ON i.currency = curr.id
              WHERE i.id = :id AND i.display = 'Y'";

      $import = $this->db->customQuery($sql, [':id' => $importId]);

      if (!empty($import)) {
        $import = $this->sanitizeArray($import);
        echo json_encode(['success' => true, 'data' => $import[0]]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Import not found']);
      }
    } catch (Exception $e) {
      $this->logError('Exception while fetching import', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load import data']);
    }
  }

  private function listImports()
  {
    try {
      $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
      $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
      $length = isset($_GET['length']) ? min($this->dataTablesMaxLength, max(10, (int)$_GET['length'])) : 25;
      
      $searchValue = '';
      $params = [];
      
      if (isset($_GET['search']) && is_array($_GET['search']) && isset($_GET['search']['value'])) {
        $searchValue = trim($_GET['search']['value']);
        
        if (strlen($searchValue) > 100) {
          $searchValue = substr($searchValue, 0, 100);
        }
        
        $searchValue = $this->sanitizeInput($searchValue);
      }
      
      $filters = isset($_GET['filters']) && is_array($_GET['filters']) ? $_GET['filters'] : [];
      $filters = array_filter($filters, function($filter) {
        return in_array($filter, $this->allowedFilters);
      });
      
      // Handle both single client_id and multiple client_ids
      $clientIds = [];
      if (isset($_GET['client_ids']) && is_array($_GET['client_ids'])) {
        $clientIds = array_filter($_GET['client_ids'], 'is_numeric');
        $clientIds = array_map('intval', $clientIds);
      } elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
        $clientIds = [(int)$_GET['client_id']];
      }

      $transportModeId = isset($_GET['transport_mode_id']) && is_numeric($_GET['transport_mode_id']) ? (int)$_GET['transport_mode_id'] : null;
      $entryPointId = isset($_GET['entry_point_id']) && is_numeric($_GET['entry_point_id']) ? (int)$_GET['entry_point_id'] : null;
      $typeOfGoodsId = isset($_GET['type_of_goods_id']) && is_numeric($_GET['type_of_goods_id']) ? (int)$_GET['type_of_goods_id'] : null;
      $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
      $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
      
      $orderColumnIndex = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 0;
      $orderDirection = isset($_GET['order'][0]['dir']) && strtolower($_GET['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
      
      $allowedColumns = [
        'i.mca_ref',
        'c.short_name',
        'l.license_number',
        'i.invoice',
        'i.pre_alert_date',
        'i.weight',
        'i.fob',
        'cs.clearing_status'
      ];
      
      $orderColumn = isset($allowedColumns[$orderColumnIndex]) ? $allowedColumns[$orderColumnIndex] : 'i.mca_ref';

      $baseFrom = "FROM imports_t i
                   LEFT JOIN clients_t c ON i.subscriber_id = c.id
                   LEFT JOIN licenses_t l ON i.license_id = l.id
                   LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                   LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id";
      
      $baseWhere = "WHERE i.display = 'Y'";
      
      $searchCondition = "";
      if (!empty($searchValue)) {
        $escapedSearch = addslashes($searchValue);
        $escapedSearch = str_replace(['%', '_'], ['\\%', '\\_'], $escapedSearch);
        
        $searchCondition = " AND (
          i.mca_ref LIKE '%{$escapedSearch}%' OR
          i.invoice LIKE '%{$escapedSearch}%' OR
          i.po_ref LIKE '%{$escapedSearch}%' OR
          c.short_name LIKE '%{$escapedSearch}%' OR
          l.license_number LIKE '%{$escapedSearch}%' OR
          i.horse LIKE '%{$escapedSearch}%' OR
          i.trailer_1 LIKE '%{$escapedSearch}%' OR
          i.trailer_2 LIKE '%{$escapedSearch}%' OR
          i.container LIKE '%{$escapedSearch}%' OR
          i.wagon LIKE '%{$escapedSearch}%' OR
          i.airway_bill LIKE '%{$escapedSearch}%' OR
          i.road_manif LIKE '%{$escapedSearch}%' OR
          i.supplier LIKE '%{$escapedSearch}%' OR
          i.crf_reference LIKE '%{$escapedSearch}%' OR
          i.declaration_reference LIKE '%{$escapedSearch}%' OR
          i.liquidation_reference LIKE '%{$escapedSearch}%' OR
          i.quittance_reference LIKE '%{$escapedSearch}%'
        )";
      }
      
      $filterCondition = "";
      $filterClauses = [];
      
      if (!empty($filters)) {
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
            case 'crf_missing':
              $filterClauses[] = "(i.crf_reference IS NULL OR i.crf_reference = '' OR i.crf_received_date IS NULL)";
              break;
            case 'ad_missing':
              $filterClauses[] = "i.ad_date IS NULL";
              break;
            case 'insurance_missing':
              $filterClauses[] = "(i.insurance_date IS NULL OR i.insurance_amount IS NULL)";
              break;
            case 'audited_pending':
              $filterClauses[] = "i.audited_date IS NULL";
              break;
            case 'archived_pending':
              $filterClauses[] = "i.archived_date IS NULL";
              break;
            case 'dgda_in_pending':
              $filterClauses[] = "i.dgda_in_date IS NULL";
              break;
            case 'liquidation_pending':
              $filterClauses[] = "i.liquidation_date IS NULL";
              break;
            case 'quittance_pending':
              $filterClauses[] = "i.quittance_date IS NULL";
              break;
           case 'dgda_out_pending':
  $filterClauses[] = "(i.dgda_out_date IS NULL AND i.quittance_date IS NOT NULL)";
  break;
           case 'dispatch_deliver_pending':
  $filterClauses[] = "i.dispatch_deliver_date IS NULL";
  break;
          }
        }
      }
      
      if (!empty($clientIds)) {
        if (count($clientIds) === 1) {
          $filterClauses[] = "i.subscriber_id = :client_id";
          $params[':client_id'] = $clientIds[0];
        } else {
          $placeholders = [];
          foreach ($clientIds as $index => $id) {
            $placeholder = ":client_id_{$index}";
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
          }
          $filterClauses[] = "i.subscriber_id IN (" . implode(',', $placeholders) . ")";
        }
      }
      
      if ($transportModeId) {
        $filterClauses[] = "i.transport_mode = :transport_mode_id";
        $params[':transport_mode_id'] = $transportModeId;
      }
      
      if ($entryPointId) {
        $filterClauses[] = "i.entry_point_id = :entry_point_id";
        $params[':entry_point_id'] = $entryPointId;
      }
      if ($typeOfGoodsId) {
        $filterClauses[] = "i.type_of_goods = :type_of_goods_id";
        $params[':type_of_goods_id'] = $typeOfGoodsId;
      }
      
      if ($startDate && $this->isValidDate($startDate)) {
        $filterClauses[] = "i.pre_alert_date >= :start_date";
        $params[':start_date'] = $startDate;
      }
      
      if ($endDate && $this->isValidDate($endDate)) {
        $filterClauses[] = "i.pre_alert_date <= :end_date";
        $params[':end_date'] = $endDate;
      }
      
      if (!empty($filterClauses)) {
        $filterCondition = " AND (" . implode(' AND ', $filterClauses) . ")";
      }

      $completeWhere = $baseWhere . $searchCondition . $filterCondition;

      $totalSql = "SELECT COUNT(*) as total FROM imports_t WHERE display = 'Y'";
      $totalResult = $this->db->customQuery($totalSql);
      $totalRecords = (int)($totalResult[0]['total'] ?? 0);

      $filteredSql = "SELECT COUNT(*) as total {$baseFrom} {$completeWhere}";
      $filteredResult = $this->db->customQuery($filteredSql, $params);
      $filteredRecords = (int)($filteredResult[0]['total'] ?? 0);

      $dataSql = "SELECT 
              i.id, 
              i.mca_ref, 
              i.invoice, 
              i.pre_alert_date,
              i.weight, 
              i.fob,
              i.liquidation_paid_by,
              i.type_of_goods,
              i.drc_entry_date,
              i.cession_date,
              c.short_name as subscriber_name,
              l.license_number,
              cs.clearing_status,
              tm.transport_mode_name
            {$baseFrom}
            {$completeWhere}
            ORDER BY {$orderColumn} {$orderDirection}
            LIMIT :limit OFFSET :offset";

      $params[':limit'] = $length;
      $params[':offset'] = $start;

      $imports = $this->db->customQuery($dataSql, $params);
      
      if (!empty($imports) && is_array($imports)) {
        $imports = $this->sanitizeArray($imports);
      } else {
        $imports = [];
      }

      echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $imports
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } catch (Exception $e) {
      $this->logError('Exception in listImports', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'search' => $_GET['search']['value'] ?? '',
        'user_id' => $_SESSION['user_id'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
      ]);
      
      http_response_code(500);
      echo json_encode([
        'draw' => $_GET['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'An error occurred while loading data. Please try again.'
      ]);
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

  private function calculateDocumentStatus($crfDate, $adDate, $insuranceDate)
  {
    if ($crfDate && $adDate && $insuranceDate) {
      return 7;
    } elseif ($crfDate && $insuranceDate) {
      return 6;
    } elseif ($adDate && $insuranceDate) {
      return 4;
    } elseif ($crfDate && $adDate) {
      return 3;
    } elseif ($crfDate) {
      return 2;
    }
    return 1;
  }

  private function getLicenses()
  {
    try {
      $subscriberId = (int)($_GET['subscriber_id'] ?? 0);

      if ($subscriberId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid subscriber ID']);
        return;
      }

      $sql = "SELECT l.id, l.license_number,l.ref_cod 
              FROM licenses_t l
              WHERE l.client_id = :subscriber_id 
              AND l.display = 'Y' 
              AND l.status = 'ACTIVE'
              AND l.status != 'ANNULATED'
              AND (l.license_expiry_date IS NULL OR l.license_expiry_date >= CURDATE())
              AND l.kind_id IN (1, 2, 5, 6)
              ORDER BY l.license_number ASC";

      $licenses = $this->db->customQuery($sql, [':subscriber_id' => $subscriberId]);
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
                l.currency_id, l.supplier, l.ref_cod,
                l.invoice_number,
                k.kind_name, k.kind_short_name,
                tg.goods_type as type_of_goods_name, tg.goods_short_name,
                tm.transport_mode_name, tm.transport_letter,
                c.currency_short_name as currency_name,
                (l.weight - IFNULL(
                    (SELECT SUM(weight) FROM imports_t WHERE license_id = :license_id1), 0
                )) AS rem_weight,

                (l.fob_declared - IFNULL(
                    (SELECT SUM(fob) FROM imports_t WHERE license_id = :license_id2), 0
                )) AS r_fob,
                (l.m3 - IFNULL(
                    (SELECT SUM(m3) FROM imports_t WHERE license_id = :license_id4), 0
                )) AS r_m3

              FROM licenses_t l
              LEFT JOIN kind_master_t k ON l.kind_id = k.id
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
              LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
              LEFT JOIN currency_master_t c ON l.currency_id = c.id
              WHERE l.id = :license_id3 AND l.display = 'Y'";

      $license = $this->db->customQuery($sql, [':license_id1' => $licenseId,':license_id2' => $licenseId,':license_id3' => $licenseId,':license_id4' => $licenseId]);

      if (empty($license)) {
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $licenseData = $this->sanitizeArray([$license[0]])[0];

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
              FROM imports_t 
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

  private function getStatistics()
  {
    try {
      $totalSql = "SELECT COUNT(*) as total_imports FROM imports_t WHERE display = 'Y'";
      $totalResult = $this->db->customQuery($totalSql);
      $totalImports = (int)($totalResult[0]['total_imports'] ?? 0);

      $completedSql = "SELECT COUNT(*) as count 
                       FROM imports_t i
                       LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                       WHERE i.display = 'Y' 
                       AND cs.clearing_status = 'CLEARING COMPLETED'";
      $completedResult = $this->db->customQuery($completedSql);
      $completedCount = (int)($completedResult[0]['count'] ?? 0);

      $inProgressSql = "SELECT COUNT(*) as count 
                        FROM imports_t i
                        LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                        WHERE i.display = 'Y' 
                        AND cs.clearing_status = 'IN PROGRESS'";
      $inProgressResult = $this->db->customQuery($inProgressSql);
      $inProgressCount = (int)($inProgressResult[0]['count'] ?? 0);

      $inTransitSql = "SELECT COUNT(*) as count 
                       FROM imports_t i
                       LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                       WHERE i.display = 'Y' 
                       AND cs.clearing_status = 'IN TRANSIT'";
      $inTransitResult = $this->db->customQuery($inTransitSql);
      $inTransitCount = (int)($inTransitResult[0]['count'] ?? 0);

      $crfMissingSql = "SELECT COUNT(*) as count 
                        FROM imports_t 
                        WHERE display = 'Y' 
                        AND (crf_reference IS NULL OR crf_reference = '' OR crf_received_date IS NULL)";
      $crfMissingResult = $this->db->customQuery($crfMissingSql);
      $crfMissingCount = (int)($crfMissingResult[0]['count'] ?? 0);

      $adMissingSql = "SELECT COUNT(*) as count 
                       FROM imports_t 
                       WHERE display = 'Y' 
                       AND ad_date IS NULL";
      $adMissingResult = $this->db->customQuery($adMissingSql);
      $adMissingCount = (int)($adMissingResult[0]['count'] ?? 0);

      $insuranceMissingSql = "SELECT COUNT(*) as count 
                              FROM imports_t 
                              WHERE display = 'Y' 
                              AND (insurance_date IS NULL OR insurance_amount IS NULL)";
      $insuranceMissingResult = $this->db->customQuery($insuranceMissingSql);
      $insuranceMissingCount = (int)($insuranceMissingResult[0]['count'] ?? 0);

      $auditedPendingSql = "SELECT COUNT(*) as count 
                            FROM imports_t 
                            WHERE display = 'Y' 
                            AND audited_date IS NULL";
      $auditedPendingResult = $this->db->customQuery($auditedPendingSql);
      $auditedPendingCount = (int)($auditedPendingResult[0]['count'] ?? 0);

      $archivedPendingSql = "SELECT COUNT(*) as count 
                             FROM imports_t 
                             WHERE display = 'Y' 
                             AND archived_date IS NULL";
      $archivedPendingResult = $this->db->customQuery($archivedPendingSql);
      $archivedPendingCount = (int)($archivedPendingResult[0]['count'] ?? 0);

      $dgdaInPendingSql = "SELECT COUNT(*) as count 
                           FROM imports_t 
                           WHERE display = 'Y' 
                           AND dgda_in_date IS NULL";
      $dgdaInPendingResult = $this->db->customQuery($dgdaInPendingSql);
      $dgdaInPendingCount = (int)($dgdaInPendingResult[0]['count'] ?? 0);

      $liquidationPendingSql = "SELECT COUNT(*) as count 
                                FROM imports_t 
                                WHERE display = 'Y' 
                                AND liquidation_date IS NULL";
      $liquidationPendingResult = $this->db->customQuery($liquidationPendingSql);
      $liquidationPendingCount = (int)($liquidationPendingResult[0]['count'] ?? 0);

      $quittancePendingSql = "SELECT COUNT(*) as count 
                              FROM imports_t 
                              WHERE display = 'Y' 
                              AND quittance_date IS NULL";
      $quittancePendingResult = $this->db->customQuery($quittancePendingSql);
      $quittancePendingCount = (int)($quittancePendingResult[0]['count'] ?? 0);

      $dgdaOutPendingSql = "SELECT COUNT(*) as count 
                            FROM imports_t 
                            WHERE display = 'Y' 
                            AND dgda_out_date IS NULL";
      $dgdaOutPendingResult = $this->db->customQuery($dgdaOutPendingSql);
      $dgdaOutPendingCount = (int)($dgdaOutPendingResult[0]['count'] ?? 0);

     $dispatchDeliverPendingSql = "SELECT COUNT(*) as count 
                              FROM imports_t 
                              WHERE display = 'Y' 
                              AND dispatch_deliver_date IS NULL";
      $dispatchDeliverPendingResult = $this->db->customQuery($dispatchDeliverPendingSql);
      $dispatchDeliverPendingCount = (int)($dispatchDeliverPendingResult[0]['count'] ?? 0);

      $this->logInfo('Statistics calculated', [
        'total_imports' => $totalImports,
        'total_completed' => $completedCount,
        'in_progress' => $inProgressCount,
        'in_transit' => $inTransitCount,
        'crf_missing' => $crfMissingCount,
        'ad_missing' => $adMissingCount,
        'insurance_missing' => $insuranceMissingCount,
        'audited_pending' => $auditedPendingCount,
        'archived_pending' => $archivedPendingCount,
        'dgda_in_pending' => $dgdaInPendingCount,
        'liquidation_pending' => $liquidationPendingCount,
        'quittance_pending' => $quittancePendingCount,
        'dgda_out_pending' => $dgdaOutPendingCount,
        'dispatch_deliver_pending' => $dispatchDeliverPendingCount
      ]);

      echo json_encode([
        'success' => true,
        'data' => [
          'total_imports' => $totalImports,
          'total_completed' => $completedCount,
          'in_progress' => $inProgressCount,
          'in_transit' => $inTransitCount,
          'crf_missing' => $crfMissingCount,
          'ad_missing' => $adMissingCount,
          'insurance_missing' => $insuranceMissingCount,
          'audited_pending' => $auditedPendingCount,
          'archived_pending' => $archivedPendingCount,
          'dgda_in_pending' => $dgdaInPendingCount,
          'liquidation_pending' => $liquidationPendingCount,
          'quittance_pending' => $quittancePendingCount,
          'dgda_out_pending' => $dgdaOutPendingCount,
          'dispatch_deliver_pending' => $dispatchDeliverPendingCount
        ]
      ]);
      
    } catch (Exception $e) {
      $this->logError('Failed to get statistics', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode([
        'success' => false, 
        'message' => 'Failed to load statistics: ' . $e->getMessage()
      ]);
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

      // ✅ FIX: Handle multiple client IDs properly
      $clientIds = [];
      if (isset($_GET['client_ids']) && is_array($_GET['client_ids'])) {
        $clientIds = array_filter($_GET['client_ids'], 'is_numeric');
        $clientIds = array_map('intval', $clientIds);
      } elseif (isset($_GET['client_id']) && is_numeric($_GET['client_id'])) {
        $clientIds = [(int)$_GET['client_id']];
      }

      $transportModeId = isset($_GET['transport_mode_id']) && is_numeric($_GET['transport_mode_id']) ? (int)$_GET['transport_mode_id'] : null;
      $entryPointId = isset($_GET['entry_point_id']) && is_numeric($_GET['entry_point_id']) ? (int)$_GET['entry_point_id'] : null;
      $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
      $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

      if (empty($filters) && empty($clientIds) && !$transportModeId && !$entryPointId && !$startDate && !$endDate) {
        echo json_encode(['success' => false, 'message' => 'Please select at least one filter']);
        return;
      }

      $baseQuery = "FROM imports_t i
                    LEFT JOIN clients_t c ON i.subscriber_id = c.id
                    LEFT JOIN licenses_t l ON i.license_id = l.id
                    LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                    LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
                    WHERE i.display = 'Y'";

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
          case 'crf_missing':
            $filterClauses[] = "(i.crf_reference IS NULL OR i.crf_reference = '' OR i.crf_received_date IS NULL)";
            break;
          case 'ad_missing':
            $filterClauses[] = "i.ad_date IS NULL";
            break;
          case 'insurance_missing':
            $filterClauses[] = "(i.insurance_date IS NULL OR i.insurance_amount IS NULL)";
            break;
          case 'audited_pending':
            $filterClauses[] = "i.audited_date IS NULL";
            break;
          case 'archived_pending':
            $filterClauses[] = "i.archived_date IS NULL";
            break;
          case 'dgda_in_pending':
            $filterClauses[] = "i.dgda_in_date IS NULL";
            break;
          case 'liquidation_pending':
            $filterClauses[] = "i.liquidation_date IS NULL";
            break;
          case 'quittance_pending':
            $filterClauses[] = "i.quittance_date IS NULL";
            break;
          case 'dgda_out_pending':
  $filterClauses[] = "(i.dgda_out_date IS NULL AND i.quittance_date IS NOT NULL)";
  break;
         case 'dispatch_deliver_pending':
  $filterClauses[] = "i.dispatch_deliver_date IS NULL";
  break;
        }
      }
      
      // ✅ FIX: Handle multiple client IDs
      if (!empty($clientIds)) {
        if (count($clientIds) === 1) {
          $filterClauses[] = "i.subscriber_id = :client_id";
          $params[':client_id'] = $clientIds[0];
        } else {
          $placeholders = [];
          foreach ($clientIds as $index => $id) {
            $placeholder = ":client_id_{$index}";
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
          }
          $filterClauses[] = "i.subscriber_id IN (" . implode(',', $placeholders) . ")";
        }
      }
      
      if ($transportModeId) {
        $filterClauses[] = "i.transport_mode = :transport_mode_id";
        $params[':transport_mode_id'] = $transportModeId;
      }
      
      if ($entryPointId) {
        $filterClauses[] = "i.entry_point_id = :entry_point_id";
        $params[':entry_point_id'] = $entryPointId;
      }
      
      if ($startDate && $this->isValidDate($startDate)) {
        $filterClauses[] = "i.pre_alert_date >= :start_date";
        $params[':start_date'] = $startDate;
      }
      
      if ($endDate && $this->isValidDate($endDate)) {
        $filterClauses[] = "i.pre_alert_date <= :end_date";
        $params[':end_date'] = $endDate;
      }
      
      $filterCondition = "";
      if (!empty($filterClauses)) {
        $filterCondition = " AND (" . implode(' AND ', $filterClauses) . ")";
      }

      $sql = "SELECT 
                i.id,
                i.mca_ref,
                i.pre_alert_date,
                i.weight,
                i.fob,
                i.crf_reference,
                i.crf_received_date,
                i.ad_date,
                i.insurance_date,
                i.insurance_amount,
                i.insurance_reference,
                i.archive_reference,
                i.audited_date,
                i.archived_date,
                i.dgda_in_date,
                i.dgda_out_date,
                i.horse,
                i.trailer_1,
                i.trailer_2,
                i.container,
                i.declaration_reference,
                i.liquidation_date,
                i.liquidation_reference,
                i.quittance_date,
                i.quittance_reference,
                i.warehouse_arrival_date,
                i.warehouse_departure_date,
                i.dispatch_deliver_date,
                c.short_name as subscriber_name,
                tm.transport_mode_name
              " . $baseQuery . "
              " . $filterCondition . "
              ORDER BY i.id ASC
              LIMIT " . $this->bulkUpdateLimit;

      $imports = $this->db->customQuery($sql, $params);

      $relevantFields = [];
      $fieldMap = [
        'crf_missing' => ['crf_reference', 'crf_received_date'],
        'ad_missing' => ['ad_date'],
        'insurance_missing' => ['insurance_date', 'insurance_amount', 'insurance_reference'],
        'audited_pending' => ['audited_date'],
        'archived_pending' => ['archived_date', 'archive_reference'],
        'dgda_in_pending' => ['dgda_in_date', 'declaration_reference'],
        'liquidation_pending' => ['liquidation_date', 'liquidation_reference'],
        'quittance_pending' => ['quittance_date', 'quittance_reference'],
  'dgda_out_pending' => ['quittance_date', 'quittance_reference', 'dgda_out_date'], // ✅ Added Quittance fields
        'dispatch_deliver_pending' => ['warehouse_arrival_date', 'warehouse_departure_date', 'dispatch_deliver_date'] // ✅ Already correct
      ];
      
      foreach ($filters as $filter) {
        if (isset($fieldMap[$filter])) {
          $relevantFields = array_merge($relevantFields, $fieldMap[$filter]);
        }
      }
      
      $relevantFields = array_unique($relevantFields);
      
      if (empty($relevantFields)) {
        $relevantFields = ['pre_alert_date', 'dgda_in_date', 'declaration_reference', 'liquidation_date', 'quittance_date'];
      }

      $imports = $this->sanitizeArray($imports);

      echo json_encode([
        'success' => true,
        'data' => $imports ?: [],
        'relevant_fields' => $relevantFields,
        'active_filters' => $filters,
        'count' => count($imports)
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

    if ($this->isRateLimited()) {
      http_response_code(429);
      echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait a moment and try again.']);
      return;
    }

    try {
      $updateData = isset($_POST['update_data']) ? json_decode($_POST['update_data'], true) : null;
      
      if (empty($updateData) || !is_array($updateData)) {
        echo json_encode(['success' => false, 'message' => 'No update data provided']);
        return;
      }

      if (count($updateData) > $this->bulkUpdateLimit) {
        echo json_encode(['success' => false, 'message' => "Maximum {$this->bulkUpdateLimit} records can be updated at once"]);
        return;
      }

      $this->db->beginTransaction();

      $successCount = 0;
      $errorCount = 0;
      $errors = [];

      try {
        foreach ($updateData as $update) {
          $importId = (int)($update['import_id'] ?? 0);
          
          if ($importId <= 0) {
            $errorCount++;
            continue;
          }

          $import = $this->db->selectData('imports_t', 'pre_alert_date, crf_received_date, ad_date, insurance_date, weight, fob', ['id' => $importId, 'display' => 'Y']);
          
          if (empty($import)) {
            throw new Exception("Import ID {$importId}: Not found");
          }

          $preAlertDate = $import[0]['pre_alert_date'];
          $currentCrfDate = $import[0]['crf_received_date'];
          $currentAdDate = $import[0]['ad_date'];
          $currentInsuranceDate = $import[0]['insurance_date'];

          $data = [];
          $allowedFields = [
            'weight',
            'fob',
            'crf_reference', 
            'crf_received_date', 
            'ad_date', 
            'insurance_date', 
            'insurance_amount', 
            'audited_date', 
            'archived_date',
            'dgda_in_date',
            'liquidation_date',
            'quittance_date',
            'dgda_out_date',
            'warehouse_arrival_date',
            'warehouse_departure_date',
            'dispatch_deliver_date'
          ];
          
          foreach ($update as $field => $value) {
            if ($field === 'import_id') continue;
            
            if (!in_array($field, $allowedFields)) {
              continue;
            }
            
            if (empty($value)) {
              $data[$field] = null;
            } else {
              $value = $this->sanitizeInput($value);
              
              if (in_array($field, ['crf_received_date', 'ad_date', 'insurance_date', 'audited_date', 'archived_date', 'dgda_in_date', 'liquidation_date', 'quittance_date', 'dgda_out_date', 'warehouse_arrival_date', 'warehouse_departure_date', 'dispatch_deliver_date'])) {
                if (!$this->isValidDate($value)) {
                  throw new Exception("Import ID {$importId}: Invalid {$field} format");
                }
                
                if ($preAlertDate && $value < $preAlertDate) {
                  throw new Exception("Import ID {$importId}: {$field} cannot be before Pre-Alert Date");
                }
              }
              
              if (in_array($field, ['insurance_amount', 'weight', 'fob'])) {
                if (!is_numeric($value) || $value < 0) {
                  throw new Exception("Import ID {$importId}: Invalid {$field}");
                }
                $value = round((float)$value, 2);
              }
              
              $data[$field] = $value;
            }
          }

          $data['updated_by'] = $_SESSION['user_id'] ?? 1;
          $data['updated_at'] = date('Y-m-d H:i:s');

          if (isset($data['crf_received_date']) || isset($data['ad_date']) || isset($data['insurance_date'])) {
            $crfDate = $data['crf_received_date'] ?? $currentCrfDate;
            $adDate = $data['ad_date'] ?? $currentAdDate;
            $insuranceDate = $data['insurance_date'] ?? $currentInsuranceDate;
            
            $data['document_status'] = $this->calculateDocumentStatus($crfDate, $adDate, $insuranceDate);
          }

          $success = $this->db->updateData('imports_t', $data, ['id' => $importId]);

          if ($success) {
            $successCount++;
          } else {
            throw new Exception("Import ID {$importId}: Update failed");
          }
        }

        $this->db->commit();

        $message = "Bulk update completed: {$successCount} successful";
        if ($errorCount > 0) {
          $message .= ", {$errorCount} skipped";
        }

        $this->logInfo('Bulk update completed', [
          'success_count' => $successCount,
          'error_count' => $errorCount
        ]);

        echo json_encode([
          'success' => true,
          'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
          'success_count' => $successCount,
          'error_count' => $errorCount,
          'errors' => array_map(function($error) {
            return htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
          }, $errors)
        ]);

      } catch (Exception $e) {
        $this->db->rollback();
        
        $this->logError('Bulk update failed, rolled back', [
          'error' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
          'success_before_failure' => $successCount
        ]);
        
        echo json_encode([
          'success' => false,
          'message' => 'Bulk update failed at import #' . ($successCount + 1) . ': ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
          'imports_processed' => $successCount
        ]);
      }

    } catch (Exception $e) {
      $this->logError('Exception during bulk update', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'An error occurred during bulk update.']);
    }
  }

  // ========================================
  // EXPORT FUNCTIONS
  // ========================================

private function setCellValueAsText($sheet, $cell, $value)
{
    if (empty($value)) {
        $sheet->setCellValue($cell, '');
    } else {
        // Force text format to prevent scientific notation
        $sheet->setCellValueExplicit(
            $cell, 
            $value, 
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
    }
}

private function exportImport()
{
    try {
      require_once __DIR__ . '/../../../vendor/autoload.php';
      
      $importId = (int)($_GET['id'] ?? 0);

      if ($importId <= 0) {
        http_response_code(400);
        die('Invalid import ID');
      }

      $sql = "SELECT i.*, 
                     c.short_name as client_name,
                     l.license_number,
                     l.invoice_number as license_invoice_number,
                     r.regime_name,
                     k.kind_name,
                     tg.goods_type,
                     tm.transport_mode_name,
                     curr.currency_short_name as main_currency,
                     fobc.currency_short_name as fob_currency_name,
                     fc.currency_short_name as freight_currency_name,
                     occ.currency_short_name as other_charges_currency_name,
                     ic.currency_short_name as insurance_currency_name,
                     cs.clearing_status,
                     ds.document_status,
                     ct.clearance_name,
                     doff.sub_office_name as declaration_office_name,
                     ep.transit_point_name as entry_point_name,
                     bw.transit_point_name as border_warehouse_name,
                     bondw.transit_point_name as bonded_warehouse_name,
                     comm.commodity_name
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id
              LEFT JOIN licenses_t l ON i.license_id = l.id
              LEFT JOIN regime_master_t r ON i.regime = r.id
              LEFT JOIN kind_master_t k ON i.kind = k.id
              LEFT JOIN type_of_goods_master_t tg ON i.type_of_goods = tg.id
              LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
              LEFT JOIN currency_master_t curr ON i.currency = curr.id
              LEFT JOIN currency_master_t fobc ON i.fob_currency = fobc.id
              LEFT JOIN currency_master_t fc ON i.fret_currency = fc.id
              LEFT JOIN currency_master_t occ ON i.other_charges_currency = occ.id
              LEFT JOIN currency_master_t ic ON i.insurance_amount_currency = ic.id
              LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
              LEFT JOIN document_status_master_t ds ON i.document_status = ds.id
              LEFT JOIN clearance_master_t ct ON i.types_of_clearance = ct.id
              LEFT JOIN sub_office_master_t doff ON i.declaration_office_id = doff.id
              LEFT JOIN transit_point_master_t ep ON i.entry_point_id = ep.id
              LEFT JOIN transit_point_master_t bw ON i.border_warehouse_id = bw.id
              LEFT JOIN transit_point_master_t bondw ON i.bonded_warehouse_id = bondw.id
              LEFT JOIN commodity_master_t comm ON i.commodity = comm.id
              WHERE i.id = :id AND i.display = 'Y'";

      $import = $this->db->customQuery($sql, [':id' => $importId]);

      if (empty($import)) {
        http_response_code(404);
        die('Import not found');
      }

      $data = $import[0];
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Import Details');

      $row = 1;
      $lastCol = 'CD';
      $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
      $sheet->setCellValue('A' . $row, 'IMPORT SHIPMENT DETAILS - ' . ($data['mca_ref'] ?? 'N/A'));
      $sheet->getStyle('A' . $row)->applyFromArray([
        'font' => [
          'bold' => true,
          'size' => 16,
          'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => '059669']
        ],
        'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]
      ]);
      $sheet->getRowDimension($row)->setRowHeight(30);

      $row++;
      $headers = [
        'MCA Ref', 'Client', 'License', 'Invoice', 'License Invoice Number', 'PO Ref', 'Pre-Alert Date',
        'Regime', 'Kind', 'Type of Goods', 'Transport Mode', 'Commodity', 'Supplier',
        'Weight (KG)', 'M3 (Liquid)', 'Cession Date', 'FOB', 'FOB Currency', 'Freight', 'Freight Currency',
        'Other Charges', 'Other Charges Currency', 'Main Currency',
        'CRF Reference', 'CRF Received Date', 'Clearing Based On', 'AD Date',
        'Insurance Date', 'Insurance Amount', 'Insurance Currency', 'Insurance Reference',
        'Inspection Reports', 'Archive Reference', 'Audited Date', 'Archived Date',
        'Road Manifest', 'Airway Bill', 'Airway Bill Weight', 'Horse', 'Trailer 1', 'Trailer 2', 
        'Container', 'Wagon',
        'Types of Clearance', 'Declaration Office', 'Entry Point', 'DGDA In Date', 'DGDA Out Date',
        'Declaration Reference', 'Declaration Validity',
        'SEGUES RCV Ref', 'SEGUES Payment Date', 'Customs Manifest Number', 'Customs Manifest Date',
        'Customs Clearance Code',
        'Liquidation Reference', 'Liquidation Date', 'Liquidation Amount', 'Liquidation Paid By',
        'Quittance Reference', 'Quittance Date',
        'T1 Number', 'T1 Date', 'Airport Arrival Date', 'Dispatch From Airport',
        'Operating Company', 'Operating Days', 'Operating Amount',
        'Arrival Date Zambia', 'Dispatch From Zambia', 'DRC Entry Date',
        'IBS Coupon Reference', 'Border Warehouse', 'Entry Coupon', 
        'Border Warehouse Arrival', 'Dispatch From Border',
        'Kanyaka Arrival', 'Kanyaka Dispatch',
        'Bonded Warehouse', 'Warehouse Arrival', 'Warehouse Departure', 'Dispatch Deliver Date',
        'Truck Status', 'Clearing Status', 'Document Status',
        'Remarks'
      ];

      $col = 'A';
      foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $col++;
      }

      $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
        'font' => [
          'bold' => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size' => 10
        ],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => '1f2937']
        ],
        'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
          'wrapText' => true
        ],
        'borders' => [
          'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
          ]
        ]
      ]);
      $sheet->getRowDimension($row)->setRowHeight(40);

      $row++;
      
      // ✅ Use setCellValueAsText for all reference/ID fields
      $this->setCellValueAsText($sheet, 'A' . $row, $data['mca_ref'] ?? '');
      $sheet->setCellValue('B' . $row, $data['client_name'] ?? '');
      $sheet->setCellValue('C' . $row, $data['license_number'] ?? '');
      $this->setCellValueAsText($sheet, 'D' . $row, $data['invoice'] ?? '');
      $this->setCellValueAsText($sheet, 'E' . $row, $data['license_invoice_number'] ?? '');
      $this->setCellValueAsText($sheet, 'F' . $row, $data['po_ref'] ?? '');
      $sheet->setCellValue('G' . $row, $data['pre_alert_date'] ?? '');
      $sheet->setCellValue('H' . $row, $data['regime_name'] ?? '');
      $sheet->setCellValue('I' . $row, $data['kind_name'] ?? '');
      $sheet->setCellValue('J' . $row, $data['goods_type'] ?? '');
      $sheet->setCellValue('K' . $row, $data['transport_mode_name'] ?? '');
      $sheet->setCellValue('L' . $row, $data['commodity_name'] ?? '');
      $sheet->setCellValue('M' . $row, $data['supplier'] ?? '');
      $sheet->setCellValue('N' . $row, $data['weight'] ?? 0);
      $sheet->setCellValue('O' . $row, $data['m3'] ?? 0);
      $sheet->setCellValue('P' . $row, $data['cession_date'] ?? '');
      $sheet->setCellValue('Q' . $row, $data['fob'] ?? 0);
      $sheet->setCellValue('R' . $row, $data['fob_currency_name'] ?? '');
      $sheet->setCellValue('S' . $row, $data['fret'] ?? 0);
      $sheet->setCellValue('T' . $row, $data['freight_currency_name'] ?? '');
      $sheet->setCellValue('U' . $row, $data['other_charges'] ?? 0);
      $sheet->setCellValue('V' . $row, $data['other_charges_currency_name'] ?? '');
      $sheet->setCellValue('W' . $row, $data['main_currency'] ?? '');
      $this->setCellValueAsText($sheet, 'X' . $row, $data['crf_reference'] ?? '');
      $sheet->setCellValue('Y' . $row, $data['crf_received_date'] ?? '');
      $sheet->setCellValue('Z' . $row, $data['clearing_based_on'] ?? '');
      $sheet->setCellValue('AA' . $row, $data['ad_date'] ?? '');
      $sheet->setCellValue('AB' . $row, $data['insurance_date'] ?? '');
      $sheet->setCellValue('AC' . $row, $data['insurance_amount'] ?? 0);
      $sheet->setCellValue('AD' . $row, $data['insurance_currency_name'] ?? '');
      $this->setCellValueAsText($sheet, 'AE' . $row, $data['insurance_reference'] ?? '');
      $this->setCellValueAsText($sheet, 'AF' . $row, $data['inspection_reports'] ?? '');
      $this->setCellValueAsText($sheet, 'AG' . $row, $data['archive_reference'] ?? '');
      $sheet->setCellValue('AH' . $row, $data['audited_date'] ?? '');
      $sheet->setCellValue('AI' . $row, $data['archived_date'] ?? '');
      $this->setCellValueAsText($sheet, 'AJ' . $row, $data['road_manif'] ?? '');
      $this->setCellValueAsText($sheet, 'AK' . $row, $data['airway_bill'] ?? '');
      $sheet->setCellValue('AL' . $row, $data['airway_bill_weight'] ?? 0);
      $this->setCellValueAsText($sheet, 'AM' . $row, $data['horse'] ?? '');
      $this->setCellValueAsText($sheet, 'AN' . $row, $data['trailer_1'] ?? '');
      $this->setCellValueAsText($sheet, 'AO' . $row, $data['trailer_2'] ?? '');
      $this->setCellValueAsText($sheet, 'AP' . $row, $data['container'] ?? '');
      $this->setCellValueAsText($sheet, 'AQ' . $row, $data['wagon'] ?? '');
      $sheet->setCellValue('AR' . $row, $data['clearance_name'] ?? '');
      $sheet->setCellValue('AS' . $row, $data['declaration_office_name'] ?? '');
      $sheet->setCellValue('AT' . $row, $data['entry_point_name'] ?? '');
      $sheet->setCellValue('AU' . $row, $data['dgda_in_date'] ?? '');
      $sheet->setCellValue('AV' . $row, $data['dgda_out_date'] ?? '');
      $this->setCellValueAsText($sheet, 'AW' . $row, $data['declaration_reference'] ?? '');
      $sheet->setCellValue('AX' . $row, $data['declaration_validity'] ?? '');
      $this->setCellValueAsText($sheet, 'AY' . $row, $data['segues_rcv_ref'] ?? '');
      $sheet->setCellValue('AZ' . $row, $data['segues_payment_date'] ?? '');
      $this->setCellValueAsText($sheet, 'BA' . $row, $data['customs_manifest_number'] ?? '');
      $sheet->setCellValue('BB' . $row, $data['customs_manifest_date'] ?? '');
      $this->setCellValueAsText($sheet, 'BC' . $row, $data['customs_clearance_code'] ?? '');
      $this->setCellValueAsText($sheet, 'BD' . $row, $data['liquidation_reference'] ?? '');
      $sheet->setCellValue('BE' . $row, $data['liquidation_date'] ?? '');
      $sheet->setCellValue('BF' . $row, $data['liquidation_amount'] ?? 0);
      $sheet->setCellValue('BG' . $row, $data['liquidation_paid_by'] ?? '');
      $this->setCellValueAsText($sheet, 'BH' . $row, $data['quittance_reference'] ?? '');
      $sheet->setCellValue('BI' . $row, $data['quittance_date'] ?? '');
      $this->setCellValueAsText($sheet, 'BJ' . $row, $data['t1_number'] ?? '');
      $sheet->setCellValue('BK' . $row, $data['t1_date'] ?? '');
      $sheet->setCellValue('BL' . $row, $data['airport_arrival_date'] ?? '');
      $sheet->setCellValue('BM' . $row, $data['dispatch_from_airport'] ?? '');
      $sheet->setCellValue('BN' . $row, $data['operating_company'] ?? '');
      $sheet->setCellValue('BO' . $row, $data['operating_days'] ?? 0);
      $sheet->setCellValue('BP' . $row, $data['operating_amount'] ?? 0);
      $sheet->setCellValue('BQ' . $row, $data['arrival_date_zambia'] ?? '');
      $sheet->setCellValue('BR' . $row, $data['dispatch_from_zambia'] ?? '');
      $sheet->setCellValue('BS' . $row, $data['drc_entry_date'] ?? '');
      $this->setCellValueAsText($sheet, 'BT' . $row, $data['ibs_coupon_reference'] ?? ''); // ✅ TEXT
      $sheet->setCellValue('BU' . $row, $data['border_warehouse_name'] ?? '');
      $this->setCellValueAsText($sheet, 'BV' . $row, $data['entry_coupon'] ?? ''); // ✅ TEXT
      $sheet->setCellValue('BW' . $row, $data['border_warehouse_arrival_date'] ?? '');
      $sheet->setCellValue('BX' . $row, $data['dispatch_from_border'] ?? '');
      $sheet->setCellValue('BY' . $row, $data['kanyaka_arrival_date'] ?? '');
      $sheet->setCellValue('BZ' . $row, $data['kanyaka_dispatch_date'] ?? '');
      $sheet->setCellValue('CA' . $row, $data['bonded_warehouse_name'] ?? '');
      $sheet->setCellValue('CB' . $row, $data['warehouse_arrival_date'] ?? '');
      $sheet->setCellValue('CC' . $row, $data['warehouse_departure_date'] ?? '');
      $sheet->setCellValue('CD' . $row, $data['dispatch_deliver_date'] ?? '');
      $sheet->setCellValue('CE' . $row, $data['truck_status'] ?? '');
      $sheet->setCellValue('CF' . $row, $data['clearing_status'] ?? '');
      $sheet->setCellValue('CG' . $row, $data['document_status'] ?? '');
$sheet->setCellValue('CH' . $row, $this->formatRemarks($data['remarks'] ?? ''));

      $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'f3f4f6']
        ],
        'borders' => [
          'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'e5e7eb']
          ]
        ]
      ]);

      for ($col = 'A'; $col != 'CE'; $col++) {
        $sheet->getColumnDimension($col)->setWidth(15);
      }

      $sheet->setAutoFilter('A2:' . $lastCol . $row);
      $sheet->freezePane('A3');

      $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', ($data['mca_ref'] ?? 'Export')) . '_' . date('YmdHis') . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      
      exit;

    } catch (Exception $e) {
      $this->logError('Failed to export import', ['error' => $e->getMessage()]);
      http_response_code(500);
      die('Export failed: ' . $e->getMessage());
    }
}
   private function formatDate($date) {
    if (!empty($date) && $date != '0000-00-00') {
        return date('d-m-Y', strtotime($date));
    }
    return '';
  }

  // ========================================
// REMARKS FORMATTING HELPER
// ========================================

private function formatRemarks($remarks)
{
    if (empty($remarks)) {
        return '';
    }
    
    // Check if it's JSON format
    $decoded = json_decode($remarks, true);
    
    // If it's not valid JSON or empty array, return original value
    if (json_last_error() !== JSON_ERROR_NONE || empty($decoded) || !is_array($decoded)) {
        return $remarks;
    }
    
    // Format each remark entry
    $formattedRemarks = [];
    foreach ($decoded as $entry) {
        if (isset($entry['date']) && isset($entry['text'])) {
            $date = $entry['date'];
            $text = $entry['text'];
            $formattedRemarks[] = "{$date}: {$text}";
        }
    }
    
    // Join all remarks with line breaks
    return implode("\n", $formattedRemarks);
}
private function exportAllImports()
{ 
    try { 
      require_once __DIR__ . '/../../../vendor/autoload.php';
      
      $filters = isset($_GET['filters']) && is_array($_GET['filters']) ? $_GET['filters'] : [];
      $filters = array_filter($filters, function($filter) {
        return in_array($filter, $this->allowedFilters);
      });
      
      $clientId = isset($_GET['client_id']) && is_numeric($_GET['client_id']) ? (int)$_GET['client_id'] : null;
      $transportModeId = isset($_GET['transport_mode_id']) && is_numeric($_GET['transport_mode_id']) ? (int)$_GET['transport_mode_id'] : null;
      $entryPointId = isset($_GET['entry_point_id']) && is_numeric($_GET['entry_point_id']) ? (int)$_GET['entry_point_id'] : null;
      $typeOfGoodsId = isset($_GET['type_of_goods_id']) && is_numeric($_GET['type_of_goods_id']) ? (int)$_GET['type_of_goods_id'] : null;
      $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
      $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
      
      $baseFrom = "FROM imports_t i
                   LEFT JOIN clients_t c ON i.subscriber_id = c.id
                   LEFT JOIN licenses_t l ON i.license_id = l.id
                   LEFT JOIN regime_master_t r ON i.regime = r.id
                   LEFT JOIN kind_master_t k ON i.kind = k.id
                   LEFT JOIN type_of_goods_master_t tg ON i.type_of_goods = tg.id
                   LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
                   LEFT JOIN currency_master_t curr ON i.currency = curr.id
                   LEFT JOIN currency_master_t fobc ON i.fob_currency = fobc.id
                   LEFT JOIN currency_master_t fc ON i.fret_currency = fc.id
                   LEFT JOIN currency_master_t occ ON i.other_charges_currency = occ.id
                   LEFT JOIN currency_master_t ic ON i.insurance_amount_currency = ic.id
                   LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                   LEFT JOIN document_status_master_t ds ON i.document_status = ds.id
                   LEFT JOIN clearance_master_t ct ON i.types_of_clearance = ct.id
                   LEFT JOIN sub_office_master_t doff ON i.declaration_office_id = doff.id
                   LEFT JOIN transit_point_master_t ep ON i.entry_point_id = ep.id
                   LEFT JOIN transit_point_master_t bw ON i.border_warehouse_id = bw.id
                   LEFT JOIN transit_point_master_t bondw ON i.bonded_warehouse_id = bondw.id
                   LEFT JOIN commodity_master_t comm ON i.commodity = comm.id";
      
      $baseWhere = "WHERE i.display = 'Y'";
      
      $filterCondition = "";
      $filterClauses = [];
      $params = [];
      
      if (!empty($filters)) {
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
            case 'crf_missing':
              $filterClauses[] = "(i.crf_reference IS NULL OR i.crf_reference = '' OR i.crf_received_date IS NULL)";
              break;
            case 'ad_missing':
              $filterClauses[] = "i.ad_date IS NULL";
              break;
            case 'insurance_missing':
              $filterClauses[] = "(i.insurance_date IS NULL OR i.insurance_amount IS NULL)";
              break;
            case 'audited_pending':
              $filterClauses[] = "i.audited_date IS NULL";
              break;
            case 'archived_pending':
              $filterClauses[] = "i.archived_date IS NULL";
              break;
            case 'dgda_in_pending':
              $filterClauses[] = "i.dgda_in_date IS NULL";
              break;
            case 'liquidation_pending':
              $filterClauses[] = "i.liquidation_date IS NULL";
              break;
            case 'quittance_pending':
              $filterClauses[] = "i.quittance_date IS NULL";
              break;
            case 'dgda_out_pending':
              $filterClauses[] = "i.dgda_out_date IS NULL";
              break;
            case 'dispatch_deliver_pending':
              $filterClauses[] = "i.dispatch_deliver_date IS NULL";
              break;
          }
        }
      }
      
      if ($clientId) {
        $filterClauses[] = "i.subscriber_id = :client_id";
        $params[':client_id'] = $clientId;
      }
      
      if ($transportModeId) {
        $filterClauses[] = "i.transport_mode = :transport_mode_id";
        $params[':transport_mode_id'] = $transportModeId;
      }
      
      if ($entryPointId) {
        $filterClauses[] = "i.entry_point_id = :entry_point_id";
        $params[':entry_point_id'] = $entryPointId;
      }

      if ($typeOfGoodsId) {
        $filterClauses[] = "i.type_of_goods = :type_of_goods_id";
        $params[':type_of_goods_id'] = $typeOfGoodsId;
      }
      
      if ($startDate && $this->isValidDate($startDate)) {
        $filterClauses[] = "i.pre_alert_date >= :start_date";
        $params[':start_date'] = $startDate;
      }
      
      if ($endDate && $this->isValidDate($endDate)) {
        $filterClauses[] = "i.pre_alert_date <= :end_date";
        $params[':end_date'] = $endDate;
      }
      
      if (!empty($filterClauses)) {
        $filterCondition = " AND (" . implode(' AND ', $filterClauses) . ")";
      }
      
      $completeWhere = $baseWhere . $filterCondition;
      
      $countSql = "SELECT COUNT(*) as total {$baseFrom} {$completeWhere}";
      $countResult = $this->db->customQuery($countSql, $params);
      $totalCount = (int)($countResult[0]['total'] ?? 0);
      
      if ($totalCount > $this->maxExportRecords) {
        http_response_code(400);
        die("Too many records ({$totalCount}). Maximum {$this->maxExportRecords} records allowed for export. Please use more specific filters.");
      }
      
      if ($totalCount === 0) {
        http_response_code(404);
        die('No imports found matching your filters');
      }
      
      $sql = "SELECT i.*, 
                     c.short_name as client_name,
                     l.license_number,
                     l.invoice_number as license_invoice_number,
                     r.regime_name,
                     k.kind_name,
                     tg.goods_type,
                     tm.transport_mode_name,
                     curr.currency_short_name as main_currency,
                     fobc.currency_short_name as fob_currency_name,
                     fc.currency_short_name as freight_currency_name,
                     occ.currency_short_name as other_charges_currency_name,
                     ic.currency_short_name as insurance_currency_name,
                     cs.clearing_status,
                     ds.document_status,
                     ct.clearance_name,
                     doff.sub_office_name as declaration_office_name,
                     ep.transit_point_name as entry_point_name,
                     bw.transit_point_name as border_warehouse_name,
                     bondw.transit_point_name as bonded_warehouse_name,
                     comm.commodity_name
              {$baseFrom}
              {$completeWhere}
              ORDER BY tm.transport_mode_name, i.id DESC";

      $imports = $this->db->customQuery($sql, $params);
      
      $groupedImports = [];
      $transportModes = [];
      
      foreach ($imports as $import) {
        $mode = $import['transport_mode_name'] ?? 'Unknown';
        if (!isset($groupedImports[$mode])) {
          $groupedImports[$mode] = [];
          $transportModes[] = $mode;
        }
        $groupedImports[$mode][] = $import;
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      
      $spreadsheet->removeSheetByIndex(0);
      
      $summarySheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Summary');
      $spreadsheet->addSheet($summarySheet, 0);
      
      $row = 1;
      $summarySheet->setCellValue('A' . $row, 'IMPORT SHIPMENTS EXPORT SUMMARY');
      $summarySheet->getStyle('A' . $row)->applyFromArray([
        'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
      ]);
      $summarySheet->mergeCells('A1:D1');
      
      $row += 2;
      $summarySheet->setCellValue('A' . $row, 'Transport Mode');
      $summarySheet->setCellValue('B' . $row, 'Count');
      $summarySheet->setCellValue('C' . $row, 'Total Weight (KG)');
      $summarySheet->setCellValue('D' . $row, 'Total FOB');
      $summarySheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1f2937']]
      ]);
      
      $row++;
      foreach ($transportModes as $mode) {
        $modeImports = $groupedImports[$mode];
        $count = count($modeImports);
        $totalWeight = array_sum(array_column($modeImports, 'weight'));
        $totalFob = array_sum(array_column($modeImports, 'fob'));
        
        $summarySheet->setCellValue('A' . $row, $mode);
        $summarySheet->setCellValue('B' . $row, $count);
        $summarySheet->setCellValue('C' . $row, number_format($totalWeight, 2));
        $summarySheet->setCellValue('D' . $row, number_format($totalFob, 2));
        $row++;
      }
      
      foreach ($summarySheet->getColumnIterator() as $column) {
        $summarySheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
      }
      
      $headers = [
        'MCA Ref', 'Client', 'License', 'Invoice', 'PO Ref', 'Pre-Alert Date',
        'Regime', 'Kind', 'Type of Goods', 'Transport Mode', 'Commodity', 'Supplier',
        'Weight (KG)', 'M3', 'Cession Date', 'FOB', 'FOB Currency', 'Freight', 'Freight Currency',
        'CRF Reference', 'CRF Received Date', 'Clearing Based On', 'AD Date',
        'Insurance Date', 'Insurance Amount', 
        'Inspection Reports', 
        'Road Manifest', 'Airway Bill', 'Airway Bill Weight', 'Horse', 'Trailer 1', 'Trailer 2', 
        'Container', 'Wagon',
        'Types of Clearance', 'Entry Point', 'DGDA In Date', 'DGDA Out Date',
        'Declaration Reference', 'Declaration Validity',
        'Liquidation Reference', 'Liquidation Date', 'Liquidation Amount', 'Liquidation Paid By',
        'Quittance Reference', 'Quittance Date',
        'T1 Number', 'T1 Date', 'Airport Arrival Date', 'Dispatch From Airport',
        'Arrival Date Zambia', 'Dispatch From Zambia', 'DRC Entry Date',
        'IBS Coupon Reference', 'Border Warehouse', 'Entry Coupon', 
        'Border Warehouse Arrival', 'Dispatch From Border',
        'Kanyaka Arrival', 'Kanyaka Dispatch',
        'Bonded Warehouse', 'Warehouse Arrival', 'Warehouse Departure', 'Dispatch Deliver Date',
        'Truck Status', 'Clearing Status', 'Document Status',
        'Remarks'
      ];
      
      foreach ($transportModes as $mode) {
        $safeName = preg_replace('/[^A-Za-z0-9 ]/', '', $mode);
        $safeName = substr($safeName, 0, 31);
        
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $safeName);
        $spreadsheet->addSheet($sheet);
        
        $row = 1;
        $sheet->setCellValue('A' . $row, 'IMPORTS - ' . strtoupper($mode));
        $sheet->getStyle('A' . $row)->applyFromArray([
          'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
          'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
          'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->mergeCells('A1:BP1');
        
        $row++;
        $col = 'A';
        foreach ($headers as $header) {
          $sheet->setCellValue($col . $row, $header);
          $col++;
        }
        
        $sheet->getStyle('A' . $row . ':BP' . $row)->applyFromArray([
          'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
          'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1f2937']],
          'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
        ]);
        
        $row++;
        
        foreach ($groupedImports[$mode] as $data) {
          // ✅ Use setCellValueAsText for all reference/ID fields
          $this->setCellValueAsText($sheet, 'A' . $row, $data['mca_ref'] ?? '');
          $sheet->setCellValue('B' . $row, $data['client_name'] ?? '');
          $sheet->setCellValue('C' . $row, $data['license_number'] ?? '');
          $this->setCellValueAsText($sheet, 'D' . $row, $data['invoice'] ?? '');
          $this->setCellValueAsText($sheet, 'E' . $row, $data['po_ref'] ?? '');
          $sheet->setCellValue('F' . $row, $this->formatDate($data['pre_alert_date'] ?? ''));
          $sheet->setCellValue('G' . $row, $data['regime_name'] ?? '');
          $sheet->setCellValue('H' . $row, $data['kind_name'] ?? '');
          $sheet->setCellValue('I' . $row, $data['goods_type'] ?? '');
          $sheet->setCellValue('J' . $row, $data['transport_mode_name'] ?? '');
          $sheet->setCellValue('K' . $row, $data['commodity_name'] ?? '');
          $sheet->setCellValue('L' . $row, $data['supplier'] ?? '');
          $sheet->setCellValue('M' . $row, $data['weight'] ?? 0);
          $sheet->setCellValue('N' . $row, $data['m3'] ?? 0);
          $sheet->setCellValue('O' . $row, $this->formatDate($data['cession_date'] ?? ''));
          $sheet->setCellValue('P' . $row, $data['fob'] ?? 0);
          $sheet->setCellValue('Q' . $row, $data['fob_currency_name'] ?? '');
          $sheet->setCellValue('R' . $row, $data['fret'] ?? 0);
          $sheet->setCellValue('S' . $row, $data['freight_currency_name'] ?? '');
          $this->setCellValueAsText($sheet, 'T' . $row, $data['crf_reference'] ?? '');
          $sheet->setCellValue('U' . $row, $this->formatDate($data['crf_received_date'] ?? ''));
          $sheet->setCellValue('V' . $row, $data['clearing_based_on'] ?? '');
          $sheet->setCellValue('W' . $row, $this->formatDate($data['ad_date'] ?? ''));
          $sheet->setCellValue('X' . $row, $this->formatDate($data['insurance_date'] ?? ''));
          $sheet->setCellValue('Y' . $row, $data['insurance_amount'] ?? 0);
          $this->setCellValueAsText($sheet, 'Z' . $row, $data['inspection_reports'] ?? '');
          $this->setCellValueAsText($sheet, 'AA' . $row, $data['road_manif'] ?? '');
          $this->setCellValueAsText($sheet, 'AB' . $row, $data['airway_bill'] ?? '');
          $sheet->setCellValue('AC' . $row, $data['airway_bill_weight'] ?? 0);
          $this->setCellValueAsText($sheet, 'AD' . $row, $data['horse'] ?? '');
          $this->setCellValueAsText($sheet, 'AE' . $row, $data['trailer_1'] ?? '');
          $this->setCellValueAsText($sheet, 'AF' . $row, $data['trailer_2'] ?? '');
          $this->setCellValueAsText($sheet, 'AG' . $row, $data['container'] ?? '');
          $this->setCellValueAsText($sheet, 'AH' . $row, $data['wagon'] ?? '');
          $sheet->setCellValue('AI' . $row, $data['clearance_name'] ?? '');
          $sheet->setCellValue('AJ' . $row, $data['entry_point_name'] ?? '');
          $sheet->setCellValue('AK' . $row, $this->formatDate($data['dgda_in_date'] ?? ''));
          $sheet->setCellValue('AL' . $row, $this->formatDate($data['dgda_out_date'] ?? ''));
          $this->setCellValueAsText($sheet, 'AM' . $row, $data['declaration_reference'] ?? '');
          $sheet->setCellValue('AN' . $row, $this->formatDate($data['declaration_validity'] ?? ''));
          $this->setCellValueAsText($sheet, 'AO' . $row, $data['liquidation_reference'] ?? '');
          $sheet->setCellValue('AP' . $row, $this->formatDate($data['liquidation_date'] ?? ''));
          $sheet->setCellValue('AQ' . $row, $data['liquidation_amount'] ?? 0);
          $sheet->setCellValue('AR' . $row, $data['liquidation_paid_by'] ?? '');
          $this->setCellValueAsText($sheet, 'AS' . $row, $data['quittance_reference'] ?? '');
          $sheet->setCellValue('AT' . $row, $this->formatDate($data['quittance_date'] ?? ''));
          $this->setCellValueAsText($sheet, 'AU' . $row, $data['t1_number'] ?? '');
          $sheet->setCellValue('AV' . $row, $this->formatDate($data['t1_date'] ?? ''));
          $sheet->setCellValue('AW' . $row, $this->formatDate($data['airport_arrival_date'] ?? ''));
          $sheet->setCellValue('AX' . $row, $this->formatDate($data['dispatch_from_airport'] ?? ''));
          $sheet->setCellValue('AY' . $row, $this->formatDate($data['arrival_date_zambia'] ?? ''));
          $sheet->setCellValue('AZ' . $row, $this->formatDate($data['dispatch_from_zambia'] ?? ''));
          $sheet->setCellValue('BA' . $row, $this->formatDate($data['drc_entry_date'] ?? ''));
          $this->setCellValueAsText($sheet, 'BB' . $row, $data['ibs_coupon_reference'] ?? ''); // ✅ TEXT
          $sheet->setCellValue('BC' . $row, $data['border_warehouse_name'] ?? '');
          $this->setCellValueAsText($sheet, 'BD' . $row, $data['entry_coupon'] ?? ''); // ✅ TEXT
          $sheet->setCellValue('BE' . $row, $this->formatDate($data['border_warehouse_arrival_date'] ?? ''));
          $sheet->setCellValue('BF' . $row, $this->formatDate($data['dispatch_from_border'] ?? ''));
          $sheet->setCellValue('BG' . $row, $this->formatDate($data['kanyaka_arrival_date'] ?? ''));
          $sheet->setCellValue('BH' . $row, $this->formatDate($data['kanyaka_dispatch_date'] ?? ''));
          $sheet->setCellValue('BI' . $row, $data['bonded_warehouse_name'] ?? '');
          $sheet->setCellValue('BJ' . $row, $this->formatDate($data['warehouse_arrival_date'] ?? ''));
          $sheet->setCellValue('BK' . $row, $this->formatDate($data['warehouse_departure_date'] ?? ''));
          $sheet->setCellValue('BL' . $row, $this->formatDate($data['dispatch_deliver_date'] ?? ''));
          $sheet->setCellValue('BM' . $row, $data['truck_status'] ?? '');
          $sheet->setCellValue('BN' . $row, $data['clearing_status'] ?? '');
          $sheet->setCellValue('BO' . $row, $data['document_status'] ?? '');
          $sheet->setCellValue('BP' . $row, $this->formatRemarks($data['remarks'] ?? ''));


          
          $fillColor = ($row % 2 == 0) ? 'f3f4f6' : 'FFFFFF';
          $sheet->getStyle('A' . $row . ':BP' . $row)->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillColor]]
          ]);

          $row++;
        }

        $sheet->freezePane('B3');
        
        $sheet->getColumnDimension('A')->setWidth(20);
        for ($col = 'B'; $col != 'BQ'; $col++) {
          $sheet->getColumnDimension($col)->setWidth(15);
        }
        
        $sheet->setAutoFilter('A2:BP' . ($row - 1));
      }

      $clientNameForFile = 'All_Clients';
      if ($clientId && !empty($imports)) {
        $clientNameForFile = $imports[0]['client_name'] ?? 'Client';
      }

      $filename = $clientNameForFile.'_Import_'. date('d_m_Y_H_i_s').'.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      
      exit;

    } catch (Exception $e) {
      $this->logError('Failed to export all imports', ['error' => $e->getMessage()]);
      http_response_code(500);
      die('Export failed: ' . $e->getMessage());
    }
}

private function exportBorderTeam()
{
    try {
      require_once __DIR__ . '/../../../vendor/autoload.php';
      
      $filters = isset($_GET['filters']) && is_array($_GET['filters']) ? $_GET['filters'] : [];
      $filters = array_filter($filters, function($filter) {
        return in_array($filter, $this->allowedFilters);
      });
      
      $clientId = isset($_GET['client_id']) && is_numeric($_GET['client_id']) ? (int)$_GET['client_id'] : null;
      $transportModeId = isset($_GET['transport_mode_id']) && is_numeric($_GET['transport_mode_id']) ? (int)$_GET['transport_mode_id'] : null;
      $entryPointId = isset($_GET['entry_point_id']) && is_numeric($_GET['entry_point_id']) ? (int)$_GET['entry_point_id'] : null;
      $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
      $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
      
      $baseFrom = "FROM imports_t i
                   LEFT JOIN clients_t c ON i.subscriber_id = c.id
                   LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
                   LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id
                   LEFT JOIN transit_point_master_t ep ON i.entry_point_id = ep.id
                   LEFT JOIN commodity_master_t cm ON i.commodity = cm.id";
      
      $baseWhere = "WHERE i.display = 'Y'";
      
      $filterCondition = "";
      $filterClauses = [];
      $params = [];
      
      if (!empty($filters)) {
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
            case 'crf_missing':
              $filterClauses[] = "(i.crf_reference IS NULL OR i.crf_reference = '' OR i.crf_received_date IS NULL)";
              break;
            case 'ad_missing':
              $filterClauses[] = "i.ad_date IS NULL";
              break;
            case 'insurance_missing':
              $filterClauses[] = "(i.insurance_date IS NULL OR i.insurance_amount IS NULL)";
              break;
            case 'audited_pending':
              $filterClauses[] = "i.audited_date IS NULL";
              break;
            case 'archived_pending':
              $filterClauses[] = "i.archived_date IS NULL";
              break;
            case 'dgda_in_pending':
              $filterClauses[] = "i.dgda_in_date IS NULL";
              break;
            case 'liquidation_pending':
              $filterClauses[] = "i.liquidation_date IS NULL";
              break;
            case 'quittance_pending':
              $filterClauses[] = "i.quittance_date IS NULL";
              break;
            case 'dgda_out_pending':
              $filterClauses[] = "i.dgda_out_date IS NULL";
              break;
            case 'dispatch_deliver_pending':
              $filterClauses[] = "i.dispatch_deliver_date IS NULL";
              break;
          }
        }
      }
      
      if ($clientId) {
        $filterClauses[] = "i.subscriber_id = :client_id";
        $params[':client_id'] = $clientId;
      }
      
      if ($transportModeId) {
        $filterClauses[] = "i.transport_mode = :transport_mode_id";
        $params[':transport_mode_id'] = $transportModeId;
      }
      
      if ($entryPointId) {
        $filterClauses[] = "i.entry_point_id = :entry_point_id";
        $params[':entry_point_id'] = $entryPointId;
      }
      
      if ($startDate && $this->isValidDate($startDate)) {
        $filterClauses[] = "i.pre_alert_date >= :start_date";
        $params[':start_date'] = $startDate;
      }
      
      if ($endDate && $this->isValidDate($endDate)) {
        $filterClauses[] = "i.pre_alert_date <= :end_date";
        $params[':end_date'] = $endDate;
      }
      
      if (!empty($filterClauses)) {
        $filterCondition = " AND (" . implode(' AND ', $filterClauses) . ")";
      }
      
      $completeWhere = $baseWhere . $filterCondition;
      
      $countSql = "SELECT COUNT(*) as total {$baseFrom} {$completeWhere}";
      $countResult = $this->db->customQuery($countSql, $params);
      $totalCount = (int)($countResult[0]['total'] ?? 0);
      
      if ($totalCount > $this->maxExportRecords) {
        http_response_code(400);
        die("Too many records ({$totalCount}). Maximum {$this->maxExportRecords} records allowed for export.");
      }
      
      if ($totalCount === 0) {
        http_response_code(404);
        die('No imports found for Border Team export');
      }
      
      $sql = "SELECT 
                i.mca_ref,
                DATE_FORMAT(i.pre_alert_date, '%d-%m-%Y') as pre_alert_date,
                i.road_manif,
                i.horse,
                i.trailer_1,
                i.trailer_2,
                ep.transit_point_name as entry_point,
                i.border_warehouse_arrival_date,
                i.dispatch_from_border,
                i.remarks,
                cm.commodity_name
              {$baseFrom}
              {$completeWhere}
              ORDER BY i.pre_alert_date DESC, i.id DESC";

      $imports = $this->db->customQuery($sql, $params);
      
      if (empty($imports)) {
        http_response_code(404);
        die('No data found for Border Team export');
      }
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Border Team');
      
      $row = 1;
      $headers = [
        'MCA REF',
        'PRE-ALERTE DATE',
        'COMMODITY',
        'ROAD MANIF',
        'HORSE',
        'TRAILER 1',
        'TRAILER 2',
        'Entry Point',
        'Border warehouse arrival date',
        'Dispatch from Border',
        'Remarks'
      ];
      
      $col = 'A';
      foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $col++;
      }
      
      $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
        'font' => [
          'bold' => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size' => 11
        ],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => '000000']
        ],
        'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
          'wrapText' => true
        ],
        'borders' => [
          'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'FFFFFF']
          ]
        ]
      ]);
      $sheet->getRowDimension($row)->setRowHeight(40);
      
      $row++;
      foreach ($imports as $data) {
        // ✅ Use setCellValueAsText for reference fields
        $this->setCellValueAsText($sheet, 'A' . $row, $data['mca_ref'] ?? '');
        $sheet->setCellValue('B' . $row, $data['pre_alert_date'] ?? '');
        $sheet->setCellValue('C' . $row, $data['commodity_name'] ?? '');
        $this->setCellValueAsText($sheet, 'D' . $row, $data['road_manif'] ?? '');
        $this->setCellValueAsText($sheet, 'E' . $row, $data['horse'] ?? '');
        $this->setCellValueAsText($sheet, 'F' . $row, $data['trailer_1'] ?? '');
        $this->setCellValueAsText($sheet, 'G' . $row, $data['trailer_2'] ?? '');
        $sheet->setCellValue('H' . $row, $data['entry_point'] ?? '');
        $sheet->setCellValue('I' . $row, $data['border_warehouse_arrival_date'] ?? '');
        $sheet->setCellValue('J' . $row, $data['dispatch_from_border'] ?? '');
$sheet->setCellValue('K' . $row, $this->formatRemarks($data['remarks'] ?? ''));
        
        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['rgb' => 'CCCCCC']
            ]
          ]
        ]);
        
        $row++;
      }
      
      foreach (range('A', 'K') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
      }
      
      $sheet->freezePane('A2');
      $sheet->setAutoFilter('A1:K' . ($row - 1));
      
      $clientNameForFile = 'All_Clients';
      if ($clientId && !empty($imports)) {
        $clientSql = "SELECT short_name FROM clients_t WHERE id = :client_id";
        $clientResult = $this->db->customQuery($clientSql, [':client_id' => $clientId]);
        if (!empty($clientResult)) {
          $clientNameForFile = $clientResult[0]['short_name'];
        }
      }
      
      $filename = $clientNameForFile . '_Border_Team_' . date('d_m_Y_H_i_s') . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      
      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      
      $this->logInfo('Border Team export completed', [
        'client_id' => $clientId,
        'total_records' => count($imports),
        'filename' => $filename
      ]);
      
      exit;

    } catch (Exception $e) {
      $this->logError('Failed to export Border Team data', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      http_response_code(500);
      die('Export failed: ' . $e->getMessage());
    }
}

  // ========================================
  // VALIDATION HELPER METHODS
  // ========================================

  private function validateImportData($post, $importId = null)
  {
    $errors = [];

    if (empty($post['subscriber_id'])) {
      $errors[] = 'Client selection is required';
    }

    $requiredFields = [
      'license_id' => 'License Number',
      'regime' => 'Regime',
      'types_of_clearance' => 'Types of Clearance',
      'pre_alert_date' => 'Pre-Alert Date',
      'invoice' => 'Invoice',
      'commodity' => 'Commodity',
      'weight' => 'Weight',
      'fob' => 'FOB',
      'entry_point_id' => 'Entry Point',
      'clearing_status' => 'Clearing Status'
    ];

    foreach ($requiredFields as $field => $label) {
      if (empty($post[$field]) && $post[$field] !== '0' && $post[$field] !== 0) {
        $errors[] = htmlspecialchars("{$label} is required", ENT_QUOTES, 'UTF-8');
      }
    }

    $textFields = [
      'mca_ref' => ['label' => 'MCA Reference', 'max' => 100],
      'invoice' => ['label' => 'Invoice', 'max' => 100],
      'po_ref' => ['label' => 'PO Reference', 'max' => 100],
      'supplier' => ['label' => 'Supplier', 'max' => 255],
      'crf_reference' => ['label' => 'CRF Reference', 'max' => 100],
      'horse' => ['label' => 'Horse', 'max' => 50],
      'trailer_1' => ['label' => 'Trailer 1', 'max' => 50],
      'trailer_2' => ['label' => 'Trailer 2', 'max' => 50],
      'container' => ['label' => 'Container', 'max' => 50],
      'wagon' => ['label' => 'Wagon', 'max' => 50],
      'commodity' => ['label' => 'Commodity', 'max' => 255],
    ];

    foreach ($textFields as $field => $config) {
      if (!empty($post[$field]) && strlen($post[$field]) > $config['max']) {
        $errors[] = htmlspecialchars("{$config['label']} cannot exceed {$config['max']} characters", ENT_QUOTES, 'UTF-8');
      }
    }

    if (empty($post['mca_ref'])) {
      $errors[] = 'MCA Reference is required';
    } else {
      $mcaRef = $this->sanitizeInput(trim($post['mca_ref']));
      
      $sql = "SELECT id FROM imports_t WHERE mca_ref = :mca_ref AND display = 'Y'";
      $params = [':mca_ref' => $mcaRef];
      
      if ($importId) {
        $sql .= " AND id != :import_id";
        $params[':import_id'] = $importId;
      }
      
      $exists = $this->db->customQuery($sql, $params);
      if ($exists) {
        $errors[] = 'MCA Reference already exists';
      }
    }

    if (!empty($post['license_id'])) {
      $licenseId = (int)$post['license_id'];
      $licenseSql = "SELECT status, license_expiry_date 
                     FROM licenses_t 
                     WHERE id = :license_id AND display = 'Y'";
      $licenseCheck = $this->db->customQuery($licenseSql, [':license_id' => $licenseId]);

      if (!empty($licenseCheck)) {
        $licenseStatus = $licenseCheck[0]['status'];
        $expiryDate = $licenseCheck[0]['license_expiry_date'];
        
        if ($licenseStatus !== 'ACTIVE') {
          $errors[] = "Selected license is not active (Status: {$licenseStatus})";
        }
        
        if ($expiryDate && $expiryDate < date('Y-m-d')) {
          $errors[] = "Selected license has expired on {$expiryDate}";
        }
      }
    }

    $dateFields = [
      'pre_alert_date', 'crf_received_date', 'ad_date', 'insurance_date', 
      'audited_date', 'archived_date', 'dgda_in_date', 'dgda_out_date',
      'liquidation_date', 'quittance_date', 'segues_payment_date',
      'customs_manifest_date', 'airport_arrival_date', 'dispatch_from_airport',
      't1_date', 'arrival_date_zambia', 'dispatch_from_zambia', 'drc_entry_date',
      'border_warehouse_arrival_date', 'dispatch_from_border', 
      'kanyaka_arrival_date', 'kanyaka_dispatch_date',
      'warehouse_arrival_date', 'warehouse_departure_date', 'dispatch_deliver_date',
      'cession_date' // ✅ Added cession_date
    ];

    foreach ($dateFields as $field) {
      if (!empty($post[$field])) {
        $dateValidation = $this->validateDateRange($post[$field], $field);
        if (!$dateValidation['valid']) {
          $errors[] = $dateValidation['error'];
        }
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

  private function validateDateRange($date, $fieldName)
  {
    if (!$this->isValidDate($date)) {
      return [
        'valid' => false,
        'error' => htmlspecialchars("Invalid date format for {$fieldName}", ENT_QUOTES, 'UTF-8')
      ];
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    $minDate = new DateTime('2000-01-01');
    $maxDate = new DateTime('+10 years');
    
    if ($dateObj < $minDate) {
      return [
        'valid' => false,
        'error' => htmlspecialchars("{$fieldName} cannot be before year 2000", ENT_QUOTES, 'UTF-8')
      ];
    }
    
    if ($dateObj > $maxDate) {
      return [
        'valid' => false,
        'error' => htmlspecialchars("{$fieldName} cannot be more than 10 years in the future", ENT_QUOTES, 'UTF-8')
      ];
    }
    
    return ['valid' => true];
  }

private function prepareImportData($post)
{
    $data = [
      'subscriber_id' => !empty($post['subscriber_id']) ? $this->toInt($post['subscriber_id']) : null,
      'license_id' => !empty($post['license_id']) ? $this->toInt($post['license_id']) : null,
      'kind' => !empty($post['kind']) ? $this->toInt($post['kind']) : null,
      'type_of_goods' => !empty($post['type_of_goods']) ? $this->toInt($post['type_of_goods']) : null,
      'transport_mode' => !empty($post['transport_mode']) ? $this->toInt($post['transport_mode']) : null,
      'mca_ref' => !empty($post['mca_ref']) ? $this->clean($post['mca_ref']) : null,
      'currency' => !empty($post['currency']) ? $this->toInt($post['currency']) : null,
      'supplier' => !empty($post['supplier']) ? $this->clean($post['supplier']) : null,
      'regime' => !empty($post['regime']) ? $this->toInt($post['regime']) : null,
      'types_of_clearance' => !empty($post['types_of_clearance']) ? $this->toInt($post['types_of_clearance']) : null,
      'declaration_office_id' => !empty($post['declaration_office_id']) ? $this->toInt($post['declaration_office_id']) : null,
      'pre_alert_date' => !empty($post['pre_alert_date']) && $this->isValidDate($post['pre_alert_date']) ? $post['pre_alert_date'] : null,
      'invoice' => !empty($post['invoice']) ? $this->clean($post['invoice']) : null,
      'license_invoice_number' => !empty($post['license_invoice_number']) ? $this->clean($post['license_invoice_number']) : null,
      'commodity' => !empty($post['commodity']) ? $this->toInt($post['commodity']) : null,
      'po_ref' => !empty($post['po_ref']) ? $this->clean($post['po_ref']) : null,
      'fret' => !empty($post['fret']) && is_numeric($post['fret']) ? round((float)$post['fret'], 2) : null,
      'fret_currency' => !empty($post['fret_currency']) ? $this->toInt($post['fret_currency']) : null,
      'other_charges' => !empty($post['other_charges']) && is_numeric($post['other_charges']) ? round((float)$post['other_charges'], 2) : null,
      'other_charges_currency' => !empty($post['other_charges_currency']) ? $this->toInt($post['other_charges_currency']) : null,
      
      // ✅ FIXED: Added rem_weight field
      'rem_weight' => !empty($post['rem_weight']) && is_numeric($post['rem_weight']) ? round((float)$post['rem_weight'], 2) : null,
      
      'weight' => isset($post['weight']) && is_numeric($post['weight']) && $post['weight'] >= 0 ? round((float)$post['weight'], 2) : null,
      'm3' => !empty($post['m3']) && is_numeric($post['m3']) ? round((float)$post['m3'], 2) : null,
      'cession_date' => !empty($post['cession_date']) && $this->isValidDate($post['cession_date']) ? $post['cession_date'] : null,
      
      'fob' => !empty($post['fob']) && is_numeric($post['fob']) ? round((float)$post['fob'], 2) : null,
      'fob_currency' => !empty($post['fob_currency']) ? $this->toInt($post['fob_currency']) : null,
      
      // ✅ FIXED: Added r_fob and r_fob_currency fields
      'r_fob' => !empty($post['r_fob']) && is_numeric($post['r_fob']) ? round((float)$post['r_fob'], 2) : null,
      'r_fob_currency' => !empty($post['r_fob_currency']) ? $this->toInt($post['r_fob_currency']) : null,
      
      'crf_reference' => !empty($post['crf_reference']) ? $this->clean($post['crf_reference']) : null,
      'crf_received_date' => !empty($post['crf_received_date']) && $this->isValidDate($post['crf_received_date']) ? $post['crf_received_date'] : null,
      'clearing_based_on' => !empty($post['clearing_based_on']) ? $this->clean($post['clearing_based_on']) : null,
      'ad_date' => !empty($post['ad_date']) && $this->isValidDate($post['ad_date']) ? $post['ad_date'] : null,
      'insurance_date' => !empty($post['insurance_date']) && $this->isValidDate($post['insurance_date']) ? $post['insurance_date'] : null,
      'insurance_amount' => !empty($post['insurance_amount']) && is_numeric($post['insurance_amount']) ? round((float)$post['insurance_amount'], 2) : null,
      'insurance_amount_currency' => !empty($post['insurance_amount_currency']) ? $this->toInt($post['insurance_amount_currency']) : null,
      'insurance_reference' => !empty($post['insurance_reference']) ? $this->clean($post['insurance_reference']) : null,
      'inspection_reports' => !empty($post['inspection_reports']) ? $this->clean($post['inspection_reports']) : null,
      'archive_reference' => !empty($post['archive_reference']) ? $this->clean($post['archive_reference']) : null,
      'audited_date' => !empty($post['audited_date']) && $this->isValidDate($post['audited_date']) ? $post['audited_date'] : null,
      'archived_date' => !empty($post['archived_date']) && $this->isValidDate($post['archived_date']) ? $post['archived_date'] : null,
      'road_manif' => !empty($post['road_manif']) ? $this->clean($post['road_manif']) : null,
      'airway_bill' => !empty($post['airway_bill']) ? $this->clean($post['airway_bill']) : null,
      'horse' => !empty($post['horse']) ? $this->clean($post['horse']) : null,
      'trailer_1' => !empty($post['trailer_1']) ? $this->clean($post['trailer_1']) : null,
      'trailer_2' => !empty($post['trailer_2']) ? $this->clean($post['trailer_2']) : null,
      'container' => !empty($post['container']) ? $this->clean($post['container']) : null,
      'entry_point_id' => !empty($post['entry_point_id']) ? $this->toInt($post['entry_point_id']) : null,
      'dgda_in_date' => !empty($post['dgda_in_date']) && $this->isValidDate($post['dgda_in_date']) ? $post['dgda_in_date'] : null,
      'declaration_reference' => !empty($post['declaration_reference']) ? $this->clean($post['declaration_reference']) : null,
      'segues_rcv_ref' => !empty($post['segues_rcv_ref']) ? $this->clean($post['segues_rcv_ref']) : null,
      'segues_payment_date' => !empty($post['segues_payment_date']) && $this->isValidDate($post['segues_payment_date']) ? $post['segues_payment_date'] : null,
      'customs_manifest_number' => !empty($post['customs_manifest_number']) ? $this->clean($post['customs_manifest_number']) : null,
      'customs_manifest_date' => !empty($post['customs_manifest_date']) && $this->isValidDate($post['customs_manifest_date']) ? $post['customs_manifest_date'] : null,
      'liquidation_reference' => !empty($post['liquidation_reference']) ? $this->clean($post['liquidation_reference']) : null,
      'liquidation_date' => !empty($post['liquidation_date']) && $this->isValidDate($post['liquidation_date']) ? $post['liquidation_date'] : null,
      'liquidation_paid_by' => !empty($post['liquidation_paid_by']) ? $this->clean($post['liquidation_paid_by']) : null,
      'liquidation_amount' => !empty($post['liquidation_amount']) && is_numeric($post['liquidation_amount']) ? round((float)$post['liquidation_amount'], 2) : null,
      'quittance_reference' => !empty($post['quittance_reference']) ? $this->clean($post['quittance_reference']) : null,
      'quittance_date' => !empty($post['quittance_date']) && $this->isValidDate($post['quittance_date']) ? $post['quittance_date'] : null,
      'dgda_out_date' => !empty($post['dgda_out_date']) && $this->isValidDate($post['dgda_out_date']) ? $post['dgda_out_date'] : null,
      'customs_clearance_code' => !empty($post['customs_clearance_code']) ? $this->clean($post['customs_clearance_code']) : null,
      'wagon' => !empty($post['wagon']) ? $this->clean($post['wagon']) : null,
      'airway_bill_weight' => !empty($post['airway_bill_weight']) && is_numeric($post['airway_bill_weight']) ? round((float)$post['airway_bill_weight'], 2) : null,
      'airport_arrival_date' => !empty($post['airport_arrival_date']) && $this->isValidDate($post['airport_arrival_date']) ? $post['airport_arrival_date'] : null,
      'dispatch_from_airport' => !empty($post['dispatch_from_airport']) && $this->isValidDate($post['dispatch_from_airport']) ? $post['dispatch_from_airport'] : null,
      'operating_company' => !empty($post['operating_company']) ? $this->clean($post['operating_company']) : null,
      'operating_days' => !empty($post['operating_days']) && is_numeric($post['operating_days']) ? (int)$post['operating_days'] : null,
      'operating_amount' => !empty($post['operating_amount']) && is_numeric($post['operating_amount']) ? round((float)$post['operating_amount'], 2) : null,
      'declaration_validity' => !empty($post['declaration_validity']) ? $this->clean($post['declaration_validity']) : null,
      't1_number' => !empty($post['t1_number']) ? $this->clean($post['t1_number']) : null,
      't1_date' => !empty($post['t1_date']) && $this->isValidDate($post['t1_date']) ? $post['t1_date'] : null,
      'arrival_date_zambia' => !empty($post['arrival_date_zambia']) && $this->isValidDate($post['arrival_date_zambia']) ? $post['arrival_date_zambia'] : null,
      'dispatch_from_zambia' => !empty($post['dispatch_from_zambia']) && $this->isValidDate($post['dispatch_from_zambia']) ? $post['dispatch_from_zambia'] : null,
      'drc_entry_date' => !empty($post['drc_entry_date']) && $this->isValidDate($post['drc_entry_date']) ? $post['drc_entry_date'] : null,
      'ibs_coupon_reference' => !empty($post['ibs_coupon_reference']) ? $this->clean($post['ibs_coupon_reference']) : null,
      'border_warehouse_id' => !empty($post['border_warehouse_id']) ? $this->toInt($post['border_warehouse_id']) : null,
      'entry_coupon' => !empty($post['entry_coupon']) ? $this->clean($post['entry_coupon']) : null,
      'border_warehouse_arrival_date' => !empty($post['border_warehouse_arrival_date']) && $this->isValidDate($post['border_warehouse_arrival_date']) ? $post['border_warehouse_arrival_date'] : null,
      'dispatch_from_border' => !empty($post['dispatch_from_border']) && $this->isValidDate($post['dispatch_from_border']) ? $post['dispatch_from_border'] : null,
      'kanyaka_arrival_date' => !empty($post['kanyaka_arrival_date']) && $this->isValidDate($post['kanyaka_arrival_date']) ? $post['kanyaka_arrival_date'] : null,
      'kanyaka_dispatch_date' => !empty($post['kanyaka_dispatch_date']) && $this->isValidDate($post['kanyaka_dispatch_date']) ? $post['kanyaka_dispatch_date'] : null,
      'bonded_warehouse_id' => !empty($post['bonded_warehouse_id']) ? $this->toInt($post['bonded_warehouse_id']) : null,
      'truck_status' => !empty($post['truck_status']) ? $this->clean($post['truck_status']) : null,
      'warehouse_arrival_date' => !empty($post['warehouse_arrival_date']) && $this->isValidDate($post['warehouse_arrival_date']) ? $post['warehouse_arrival_date'] : null,
      'warehouse_departure_date' => !empty($post['warehouse_departure_date']) && $this->isValidDate($post['warehouse_departure_date']) ? $post['warehouse_departure_date'] : null,
      'dispatch_deliver_date' => !empty($post['dispatch_deliver_date']) && $this->isValidDate($post['dispatch_deliver_date']) ? $post['dispatch_deliver_date'] : null,
      'clearing_status' => !empty($post['clearing_status']) ? $this->toInt($post['clearing_status']) : null,
      'remarks' => !empty($post['remarks']) ? $post['remarks'] : null,
    ];

    $preAlertDate = $data['pre_alert_date'];
    $crfDate = $data['crf_received_date'];
    $adDate = $data['ad_date'];
    $insuranceDate = $data['insurance_date'];
    
    $data['document_status'] = $this->calculateDocumentStatus($crfDate, $adDate, $insuranceDate);

    return $data;
}



  // ========================================
  // VALIDATION & SANITIZATION
  // ========================================

  private function validateCsrfToken()
  {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token']);
      exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token']);
      exit;
    }

    if (empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
      exit;
    }
  }

  private function sanitizeInput($value)
  {
    if (is_array($value)) {
      return array_map([$this, 'sanitizeInput'], $value);
    }
    
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    
    return $value;
  }

  private function sanitizeArray($array)
  {
    if (!is_array($array)) {
      return [];
    }

    return array_map(function($item) {
      if (is_array($item)) {
        return $this->sanitizeArray($item);
      }
      if (is_string($item)) {
        return htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
      }
      return $item;
    }, $array);
  }

  private function clean($value)
  {
    return trim(strip_tags($value));
  }

  private function toInt($value)
  {
    return (int)$value;
  }

  private function isValidDate($date)
  {
    if (empty($date)) {
      return false;
    }
    
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
  }

  // ========================================
  // LOGGING
  // ========================================

  private function logInfo($message, $context = [])
  {
    $this->log('INFO', $message, $context);
  }

  private function logError($message, $context = [])
  {
    $this->log('ERROR', $message, $context);
  }

  private function log($level, $message, $context = [])
  {
    try {
      $timestamp = date('Y-m-d H:i:s');
      $userId = $_SESSION['user_id'] ?? 'guest';
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      
      $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
      
      $logMessage = "[{$timestamp}] [{$level}] [User: {$userId}] [IP: {$ip}] {$message}";
      if ($contextStr) {
        $logMessage .= " | Context: {$contextStr}";
      }
      $logMessage .= PHP_EOL;
      
      error_log($logMessage, 3, $this->logFile);
      
    } catch (Exception $e) {
      error_log("Failed to write to log file: " . $e->getMessage());
    }
  }
  public function checkHorse()
  {
      try {
          $horse = trim($_POST['horse'] ?? '');

          if ($horse === '') {
              echo json_encode(['exists' => false]);
              return;
          }

          $sql = "SELECT COUNT(*) AS total
                  FROM imports_t
                  WHERE horse = :horse";

          $result = $this->db->customQuery($sql, [':horse' => $horse]);

          $count = $result[0]['total'] ?? 0;

          echo json_encode([
              'exists' => $count > 0
          ]);
      } catch (Exception $e) {
          echo json_encode([
              'exists' => false,
              'error' => 'Server error'
          ]);
      }
  }

}