<?php

/**
 * License Management Controller
 * 
 * Handles all license-related operations including CRUD, exports, 
 * statistics, and file management for standard and MCA type licenses.
 * 
 * @version 3.0.0 - DUAL TABLE SUPPORT (Import/Export)
 */
class LicenseController extends Controller
{
  // ===== CONSTANTS =====
  
  /** MCA (Modification de Certification d'Accompagnement) Kind IDs */
  const MCA_IMPORT_KIND_ID = 5;
  const MCA_EXPORT_KIND_ID = 6;
  
  /** Special Kind IDs (No Invoice/Applied Date required) */
  const SPECIAL_KIND_ID_3 = 3;
  const SPECIAL_KIND_ID_4 = 4;
  
  /** Import/Export Kind IDs for dual-table filtering */
  const IMPORT_KIND_IDS = [1, 2, 5, 6];
  const EXPORT_KIND_IDS = [3, 4];
  
  /** Type of Goods that requires M3 field */
  const TYPE_OF_GOODS_M3_ID = 3;
  
  /** File upload configuration */
  const MAX_FILE_SIZE = 5242880; // 5MB in bytes
  const ALLOWED_FILE_EXTENSIONS = ['pdf'];
  const ALLOWED_MIME_TYPES = ['application/pdf'];
  
  /** Directory permissions */
  const DIR_PERMISSIONS = 0755;
  const FILE_PERMISSIONS = 0644;
  
  /** Export configuration */
  const EXCEL_HEADER_COLOR = '667eea';
  const EXCEL_HEADER_COLOR_ALL = '28a745';
  const EXCEL_IMPORT_COLOR = '667eea'; // Blue/Purple for Import
  const EXCEL_EXPORT_COLOR = '11998e'; // Green/Teal for Export
  
  /** Statistics configuration */
  const EXPIRING_DAYS_THRESHOLD = 30;
  
  // ===== PROPERTIES =====
  
  private $db;
  
  /**
   * Constructor - Initialize database and upload directory
   */
  public function __construct()
  {
    $this->db = new Database();
    $this->initializeUploadDirectory();
  }

  /**
   * Initialize upload directory with secure permissions
   */
  private function initializeUploadDirectory()
  {
    $uploadDir = UPLOAD_PATH . '/licenses';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, self::DIR_PERMISSIONS, true);
    }
    
    $subFolders = ['INVOICES', 'LICENSES'];
    foreach ($subFolders as $folder) {
      $folderPath = $uploadDir . '/' . $folder;
      if (!is_dir($folderPath)) {
        mkdir($folderPath, self::DIR_PERMISSIONS, true);
      }
    }
  }

  /**
   * Check if kind is MCA type
   */
  private function isMCAType($kindId)
  {
    return in_array((int) $kindId, [self::MCA_IMPORT_KIND_ID, self::MCA_EXPORT_KIND_ID]);
  }

  /**
   * Check if kind is Special type (3 or 4)
   */
  private function isSpecialType($kindId)
  {
    return in_array((int) $kindId, [self::SPECIAL_KIND_ID_3, self::SPECIAL_KIND_ID_4]);
  }

  /**
   * Main index page
   */
  public function index()
  {
    $data = [
      'title' => 'Licenses Management',
      'clients' => $this->db->selectData('clients_t', 'id, short_name', [], 'short_name ASC'),
      'banks' => $this->db->selectData('banklist_master_t', 'id, bank_name', [], 'bank_name ASC'),
      'entry_posts' => $this->db->selectData('transit_point_master_t', 'id, transit_point_name', ['entry_point' => 'Y'], 'transit_point_name ASC'),
      'type_of_goods' => $this->db->selectData('type_of_goods_master_t', 'id, goods_type, goods_short_name', [], 'goods_type ASC'),
      'units' => $this->db->customQuery("SELECT id, unit_name FROM unit_master_t WHERE id IN (1, 2, 3) ORDER BY id ASC"),
      'transport_modes' => $this->db->selectData('transport_mode_master_t', 'id, transport_mode_name, transport_letter', [], 'transport_mode_name ASC'),
      'currencies' => $this->db->selectData('currency_master_t', 'id, currency_name, currency_short_name', [], 'currency_name ASC'),
      'kinds' => $this->db->selectData('kind_master_t', 'id, kind_name, kind_short_name', [], 'kind_name ASC'),
      'payment_methods' => $this->db->selectData('payment_method_master_t', 'id, payment_method_name', [], 'payment_method_name ASC'),
      'payment_subtypes' => $this->db->selectData('payment_subtype_master_t', 'id, payment_subtype', [], 'payment_subtype ASC'),
      'origins' => $this->db->selectData('origin_master_t', 'id, origin_name', ['display' => 'Y'], 'origin_name ASC'),
      'done_by_options' => $this->db->selectData('done_by_t', 'id, done_by_name', ['display' => 'Y'], 'done_by_name ASC'),
      'special_kind_ids' => [self::SPECIAL_KIND_ID_3, self::SPECIAL_KIND_ID_4],
      'mca_kind_ids' => [self::MCA_IMPORT_KIND_ID, self::MCA_EXPORT_KIND_ID]
    ];

    $this->viewWithLayout('licenses/licenses', $data);
  }

  /**
   * Get client license setting
   */
  public function getClientLicenseSetting()
  {
    header('Content-Type: application/json');

    $clientId = (int) ($_GET['client_id'] ?? 0);

    if ($clientId <= 0) {
      echo json_encode(['success' => false]);
      return;
    }

    $row = $this->db->selectData('clients_t', 'license_cleared_by', ['id' => $clientId]);
    $licenseBy = $row[0]['license_cleared_by'] ?? '';

    echo json_encode(['success' => true, 'license_cleared_by' => $licenseBy]);
  }

  /**
   * Get kind type info
   */
  public function getKindTypeInfo()
  {
    header('Content-Type: application/json');

    $kindId = (int) ($_GET['kind_id'] ?? 0);

    echo json_encode([
      'success' => true,
      'is_mca_type' => $this->isMCAType($kindId),
      'is_special_type' => $this->isSpecialType($kindId),
      'kind_id' => $kindId
    ]);
  }

  /**
   * Main CRUD data handler
   */
  public function crudData($action = 'insertion')
  {
    if (ob_get_level()) {
      ob_clean();
    }
    header('Content-Type: application/json');

    try {
      switch ($action) {
        case 'insertion':
          $this->insertLicense();
          break;
        case 'update':
          $this->updateLicense();
          break;
        case 'deletion':
          $this->deleteLicense();
          break;
        case 'getLicense':
          $this->getLicense();
          break;
        case 'listing':
          $this->listLicenses();
          break;
        case 'statistics':
          $this->getStatistics();
          break;
        case 'expiredLicenses':
          $this->getExpiredLicenses();
          break;
        case 'expiringLicenses':
          $this->getExpiringLicenses();
          break;
        case 'incompleteLicenses':
          $this->getIncompleteLicenses();
          break;
        case 'exportLicense':
          $this->exportLicense();
          break;
        case 'exportAll':
          $this->exportAllLicenses();
          break;
        case 'addOrigin':
          $this->addOrigin();
          break;
        case 'getOrigins':
          $this->getOrigins();
          break;
        case 'getKindTypeInfo':
          $this->getKindTypeInfo();
          break;
        default:
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      error_log("License CRUD Error [{$action}]: " . $e->getMessage());
      error_log("Stack trace: " . $e->getTraceAsString());
      echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
  }

  // ===== ORIGIN MANAGEMENT =====

  /**
   * Add new origin/destination
   */
  private function addOrigin()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $originName = trim($_POST['origin_name'] ?? '');

    if (empty($originName)) {
      echo json_encode(['success' => false, 'message' => 'Origin/Destination name is required']);
      return;
    }

    $existing = $this->db->selectData('origin_master_t', 'id', ['origin_name' => $originName, 'display' => 'Y']);
    if (!empty($existing)) {
      echo json_encode(['success' => false, 'message' => 'This Origin/Destination already exists']);
      return;
    }

    $data = [
      'origin_name' => htmlspecialchars($originName, ENT_QUOTES, 'UTF-8'),
      'display' => 'Y',
      'created_by' => $_SESSION['user_id'] ?? 1,
      'updated_by' => $_SESSION['user_id'] ?? 1
    ];

    $insertId = $this->db->insertData('origin_master_t', $data);

    if ($insertId) {
      $newOrigin = $this->db->selectData('origin_master_t', 'id, origin_name', ['id' => $insertId]);
      
      echo json_encode([
        'success' => true,
        'message' => 'Origin/Destination added successfully!',
        'data' => $newOrigin[0] ?? null
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to add Origin/Destination']);
    }
  }

  /**
   * Get all active origins
   */
  private function getOrigins()
  {
    $origins = $this->db->selectData('origin_master_t', 'id, origin_name', ['display' => 'Y'], 'origin_name ASC');
    
    echo json_encode([
      'success' => true,
      'data' => $origins
    ]);
  }

  // ===== STATISTICS =====

  /**
   * Get license statistics
   */
  private function getStatistics()
  {
    $sql = "SELECT 
              COUNT(*) as total_licenses,
              SUM(CASE WHEN license_expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired_licenses,
              SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) as inactive_licenses,
              SUM(CASE WHEN status = 'ANNULATED' THEN 1 ELSE 0 END) as annulated_licenses,
              SUM(CASE WHEN status = 'MODIFIED' THEN 1 ELSE 0 END) as modified_licenses,
              SUM(CASE WHEN status = 'PROROGATED' THEN 1 ELSE 0 END) as prorogated_licenses,
              SUM(CASE WHEN license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . self::EXPIRING_DAYS_THRESHOLD . " DAY) THEN 1 ELSE 0 END) as expiring_licenses,
              COALESCE(SUM(fob_declared), 0) as total_fob_value,
              COUNT(CASE WHEN " . $this->getIncompleteConditions() . " THEN 1 END) as incomplete_licenses
            FROM licenses_t";
    
    $result = $this->db->customQuery($sql);
    
    if ($result) {
      $stats = $result[0];
      echo json_encode([
        'success' => true,
        'data' => [
          'total_licenses' => $stats['total_licenses'] ?? 0,
          'expired_licenses' => $stats['expired_licenses'] ?? 0,
          'inactive_licenses' => $stats['inactive_licenses'] ?? 0,
          'annulated_licenses' => $stats['annulated_licenses'] ?? 0,
          'modified_licenses' => $stats['modified_licenses'] ?? 0,
          'prorogated_licenses' => $stats['prorogated_licenses'] ?? 0,
          'expiring_licenses' => $stats['expiring_licenses'] ?? 0,
          'incomplete_licenses' => $stats['incomplete_licenses'] ?? 0,
          'total_fob_value' => number_format($stats['total_fob_value'] ?? 0, 2)
        ]
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to load statistics']);
    }
  }

  /**
   * Get SQL conditions for incomplete licenses
   */
  private function getIncompleteConditions()
  {
    return "(
      (kind_id IS NULL OR kind_id = 0)
      OR (kind_id NOT IN (" . self::MCA_IMPORT_KIND_ID . ", " . self::MCA_EXPORT_KIND_ID . ") AND (
        bank_id IS NULL OR bank_id = 0
        OR client_id IS NULL OR client_id = 0
        OR license_cleared_by IS NULL OR license_cleared_by = 0
        OR type_of_goods_id IS NULL OR type_of_goods_id = 0
        OR weight IS NULL OR weight = 0
        OR unit_of_measurement_id IS NULL OR unit_of_measurement_id = 0
        OR currency_id IS NULL OR currency_id = 0
        OR fob_declared IS NULL OR fob_declared = 0
        OR transport_mode_id IS NULL OR transport_mode_id = 0
        OR license_validation_date IS NULL
        OR license_expiry_date IS NULL
        OR license_number IS NULL OR license_number = ''
        OR entry_post_id IS NULL OR entry_post_id = 0
        OR payment_method_id IS NULL OR payment_method_id = 0
        OR destination_id IS NULL OR destination_id = 0
        OR (type_of_goods_id = " . self::TYPE_OF_GOODS_M3_ID . " AND (m3 IS NULL OR m3 = 0))
      ))
      OR (kind_id NOT IN (" . self::MCA_IMPORT_KIND_ID . ", " . self::MCA_EXPORT_KIND_ID . ", " . self::SPECIAL_KIND_ID_3 . ", " . self::SPECIAL_KIND_ID_4 . ") AND (
        invoice_number IS NULL OR invoice_number = ''
        OR invoice_date IS NULL
        OR license_applied_date IS NULL
        OR supplier IS NULL OR supplier = ''
      ))
    )";
  }

  /**
   * Get expired licenses
   */
  private function getExpiredLicenses()
  {
    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              DATEDIFF(CURDATE(), l.license_expiry_date) as days_expired
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            WHERE l.license_expiry_date < CURDATE()
            ORDER BY l.license_expiry_date DESC";

    $result = $this->db->customQuery($sql);

    echo json_encode([
      'success' => $result !== false,
      'data' => $result ?: [],
      'message' => $result !== false ? '' : 'Failed to load expired licenses'
    ]);
  }

  /**
   * Get licenses expiring soon
   */
  private function getExpiringLicenses()
  {
    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              DATEDIFF(l.license_expiry_date, CURDATE()) as days_remaining
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            WHERE l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL " . self::EXPIRING_DAYS_THRESHOLD . " DAY)
            ORDER BY l.license_expiry_date ASC";

    $result = $this->db->customQuery($sql);

    echo json_encode([
      'success' => $result !== false,
      'data' => $result ?: [],
      'message' => $result !== false ? '' : 'Failed to load expiring licenses'
    ]);
  }

  /**
   * Get incomplete licenses with missing field details
   */
  private function getIncompleteLicenses()
  {
    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              k.kind_name,
              tg.goods_type,
              u.unit_name,
              cur.currency_short_name,
              tm.transport_mode_name,
              ep.transit_point_name as entry_post_name,
              pm.payment_method_name,
              ps.payment_subtype,
              o.origin_name as destination_name
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            LEFT JOIN kind_master_t k ON l.kind_id = k.id
            LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
            LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
            LEFT JOIN transit_point_master_t ep ON l.entry_post_id = ep.id
            LEFT JOIN payment_method_master_t pm ON l.payment_method_id = pm.id
            LEFT JOIN payment_subtype_master_t ps ON l.payment_subtype_id = ps.id
            LEFT JOIN origin_master_t o ON l.destination_id = o.id
            WHERE " . $this->getIncompleteConditions() . "
            ORDER BY l.created_at DESC";

    $result = $this->db->customQuery($sql);

    if ($result !== false) {
      $processedData = [];
      foreach ($result as $license) {
        $license['missing_fields'] = $this->getMissingFields($license);
        $processedData[] = $license;
      }
      
      echo json_encode([
        'success' => true,
        'data' => $processedData,
        'count' => count($processedData)
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to load incomplete licenses']);
    }
  }

  /**
   * Get list of missing fields for a license
   */
  private function getMissingFields($license)
  {
    $missingFields = [];
    $kindId = (int) ($license['kind_id'] ?? 0);
    $typeOfGoodsId = (int) ($license['type_of_goods_id'] ?? 0);
    $isMCAType = $this->isMCAType($kindId);
    $isSpecialType = $this->isSpecialType($kindId);
    
    if ($isMCAType) {
      return $missingFields;
    }
    
    $requiredFields = [
      'kind_id' => 'Kind',
      'bank_id' => 'Bank',
      'client_id' => 'Client',
      'license_cleared_by' => 'License Cleared By',
      'type_of_goods_id' => 'Type of Goods',
      'weight' => 'Weight',
      'unit_of_measurement_id' => 'Unit of Measurement',
      'currency_id' => 'Currency',
      'fob_declared' => 'FOB Declared',
      'transport_mode_id' => 'Transport Mode',
      'license_validation_date' => 'License Validation Date',
      'license_expiry_date' => 'License Expiry Date',
      'license_number' => 'License Number',
      'entry_post_id' => $isSpecialType ? 'Entry Post/Exit Post' : 'Entry Post',
      'payment_method_id' => 'Payment Method',
      'destination_id' => 'Destination/Origin'
    ];

    if ($typeOfGoodsId === self::TYPE_OF_GOODS_M3_ID) {
      $requiredFields['m3'] = 'M3';
    }

    if (!$isSpecialType) {
      $requiredFields['invoice_number'] = 'Invoice Number';
      $requiredFields['invoice_date'] = 'Invoice Date';
      $requiredFields['license_applied_date'] = 'License Applied Date';
      $requiredFields['supplier'] = 'Supplier/Buyer';
    } else {
      $requiredFields['supplier'] = 'Supplier/Buyer';
    }

    foreach ($requiredFields as $field => $label) {
      if (empty($license[$field]) || $license[$field] == 0) {
        $missingFields[] = $label . ' (Required)';
      }
    }

    $optionalFields = [
      'insurance' => 'Insurance',
      'freight' => 'Freight',
      'other_costs' => 'Other Costs',
      'fsi' => 'FSI/FSO',
      'aur' => 'AUR',
      'payment_subtype_id' => 'Payment Subtype',
      'license_file' => 'License File'
    ];

    if (!$isSpecialType) {
      $optionalFields['ref_cod'] = 'REF. COD';
      $optionalFields['invoice_file'] = 'Invoice File';
    }

    foreach ($optionalFields as $field => $label) {
      if ($license[$field] === null || (is_string($license[$field]) && $license[$field] === '') || $license[$field] == 0) {
        $missingFields[] = $label . ' (Optional)';
      }
    }
    
    return $missingFields;
  }

  // ===== LICENSE CRUD OPERATIONS =====

  /**
   * Insert new license
   */
  private function insertLicense()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
      echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
      return;
    }

    $kindId = (int) ($_POST['kind_id'] ?? 0);
    $isMCAType = $this->isMCAType($kindId);
    $isSpecialType = $this->isSpecialType($kindId);

    $validation = $this->validateLicenseData($_POST, null, $isMCAType, $isSpecialType);
    if (!$validation['success']) {
      echo json_encode($validation);
      return;
    }

    $data = $this->prepareLicenseData($_POST, $isMCAType, $isSpecialType);

    // Check uniqueness
    if (!$this->checkLicenseNumberUniqueness($data['license_number'])) {
      echo json_encode(['success' => false, 'message' => 'License Number already exists. Please use a unique License Number.']);
      return;
    }

    // Check invoice uniqueness only for non-MCA and non-special types
    if (!$isMCAType && !$isSpecialType && !empty($data['invoice_number'])) {
      if (!$this->checkInvoiceNumberUniqueness($data['invoice_number'])) {
        echo json_encode(['success' => false, 'message' => 'An invoice with this number already exists']);
        return;
      }
    }

    try {
      $this->db->beginTransaction();

      $fileUploadResult = $this->handleFileUploads(false, $isSpecialType);
      if (!$fileUploadResult['success'] && !empty($fileUploadResult['errors'])) {
        throw new Exception(implode('<br>', $fileUploadResult['errors']));
      }

      $data = array_merge($data, $fileUploadResult['files']);
      $data['created_by'] = (int) $_SESSION['user_id'];
      $data['updated_by'] = (int) $_SESSION['user_id'];

      $insertId = $this->db->insertData('licenses_t', $data);

      if (!$insertId) {
        throw new Exception('Failed to save license to database');
      }

      $this->db->commit();
      echo json_encode([
        'success' => true,
        'message' => 'License created successfully!',
        'id' => $insertId
      ]);

    } catch (Exception $e) {
      $this->db->rollback();
      $this->cleanupFiles($fileUploadResult['files'] ?? []);
      error_log("Insert License Error [User: {$_SESSION['user_id']}]: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  /**
   * Update existing license
   */
  private function updateLicense()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
      echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
      return;
    }

    $licenseId = (int) ($_POST['license_id'] ?? 0);
    if ($licenseId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
      return;
    }

    $existing = $this->db->selectData('licenses_t', '*', ['id' => $licenseId]);
    if (empty($existing)) {
      echo json_encode(['success' => false, 'message' => 'License not found']);
      return;
    }

    $kindId = (int) ($_POST['kind_id'] ?? 0);
    $isMCAType = $this->isMCAType($kindId);
    $isSpecialType = $this->isSpecialType($kindId);

    $validation = $this->validateLicenseData($_POST, $licenseId, $isMCAType, $isSpecialType);
    if (!$validation['success']) {
      echo json_encode($validation);
      return;
    }

    try {
      $this->db->beginTransaction();

      $data = $this->prepareLicenseData($_POST, $isMCAType, $isSpecialType);

      $fileUploadResult = $this->handleFileUploads(true, $isSpecialType);
      if (!$fileUploadResult['success'] && !empty($fileUploadResult['errors'])) {
        throw new Exception(implode('<br>', $fileUploadResult['errors']));
      }

      // Handle file replacements
      foreach ($fileUploadResult['files'] as $key => $value) {
        if (!empty($value)) {
          $oldFile = $existing[0][$key] ?? '';
          if (!empty($oldFile) && file_exists(PUBLIC_PATH . '/' . $oldFile)) {
            unlink(PUBLIC_PATH . '/' . $oldFile);
          }
          $data[$key] = $value;
        }
      }

      $data['updated_by'] = (int) $_SESSION['user_id'];
      $data['updated_at'] = date('Y-m-d H:i:s');

      $success = $this->db->updateData('licenses_t', $data, ['id' => $licenseId]);

      if (!$success) {
        throw new Exception('Failed to update license in database');
      }

      $this->db->commit();
      echo json_encode([
        'success' => true,
        'message' => 'License updated successfully!'
      ]);

    } catch (Exception $e) {
      $this->db->rollback();
      error_log("Update License Error [User: {$_SESSION['user_id']}, License: {$licenseId}]: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  /**
   * Delete license
   */
  private function deleteLicense()
  {
    $licenseId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

    if ($licenseId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
      return;
    }

    $license = $this->db->selectData('licenses_t', '*', ['id' => $licenseId]);
    if (empty($license)) {
      echo json_encode(['success' => false, 'message' => 'License not found']);
      return;
    }

    $success = $this->db->deleteData('licenses_t', ['id' => $licenseId]);

    if ($success) {
      $this->deleteLicenseFiles($license[0]);
      echo json_encode([
        'success' => true,
        'message' => 'License deleted successfully!'
      ]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to delete license. Please try again.']);
    }
  }

  /**
   * Get single license details
   */
  private function getLicense()
  {
    $licenseId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($licenseId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
      return;
    }

    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              k.kind_name,
              k.kind_short_name,
              tg.goods_type,
              tg.goods_short_name,
              u.unit_name,
              cur.currency_name,
              cur.currency_short_name,
              tm.transport_mode_name,
              tm.transport_letter,
              ep.transit_point_name as entry_post_name,
              pm.payment_method_name,
              ps.payment_subtype,
              o.origin_name as destination_name,
              db.done_by_name as license_cleared_by_name
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            LEFT JOIN kind_master_t k ON l.kind_id = k.id
            LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
            LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
            LEFT JOIN transit_point_master_t ep ON l.entry_post_id = ep.id
            LEFT JOIN payment_method_master_t pm ON l.payment_method_id = pm.id
            LEFT JOIN payment_subtype_master_t ps ON l.payment_subtype_id = ps.id
            LEFT JOIN origin_master_t o ON l.destination_id = o.id
            LEFT JOIN done_by_t db ON l.license_cleared_by = db.id
            WHERE l.id = :id";

    $license = $this->db->customQuery($sql, [':id' => $licenseId]);

    if (!empty($license)) {
      $licenseData = $license[0];
      $kindId = (int) ($licenseData['kind_id'] ?? 0);
      $licenseData['is_mca_type'] = $this->isMCAType($kindId);
      $licenseData['is_special_type'] = $this->isSpecialType($kindId);
      
      echo json_encode([
        'success' => true,
        'data' => $licenseData,
        'message' => ''
      ]);
    } else {
      echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'License not found'
      ]);
    }
  }

  /**
   * ✅ UPDATED: List licenses with DUAL-TABLE support (Import/Export filtering)
   */
  private function listLicenses()
  {
    $draw = (int) ($_GET['draw'] ?? 1);
    $start = (int) ($_GET['start'] ?? 0);
    $length = (int) ($_GET['length'] ?? 25);
    
    // Get search value
    $searchValue = '';
    if (isset($_GET['searchValue']) && !empty($_GET['searchValue'])) {
      $searchValue = trim($_GET['searchValue']);
    } elseif (isset($_GET['search']) && is_array($_GET['search']) && isset($_GET['search']['value']) && !empty($_GET['search']['value'])) {
      $searchValue = trim($_GET['search']['value']);
    }
    
    // ✅ NEW: Get kind_ids for import/export dual-table filtering
    $kindIds = trim($_GET['kind_ids'] ?? '');
    $kindIdsArray = !empty($kindIds) ? explode(',', $kindIds) : null;
    
    // Get advanced filters
    $clientId = (int) ($_GET['client_id'] ?? 0);
    $transportModeId = (int) ($_GET['transport_mode_id'] ?? 0);
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate = trim($_GET['end_date'] ?? '');
    
    $orderColumn = (int) ($_GET['order'][0]['column'] ?? 1);
    $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
    $filter = $_GET['filter'] ?? 'all';

    $columns = [
      'l.id',
      'l.license_number',
      'c.short_name',
      'b.bank_name',
      'l.invoice_number',
      'l.license_applied_date',
      'l.license_expiry_date',
      'l.status'
    ];

    $orderBy = $columns[$orderColumn] ?? 'l.license_number';

    // Build base query
    $baseSql = "FROM licenses_t l 
                LEFT JOIN clients_t c ON l.client_id = c.id 
                LEFT JOIN banklist_master_t b ON l.bank_id = b.id 
                WHERE 1=1";

    $whereConditions = "";
    $params = [];
    
    // ✅ NEW: Add kind_ids filter for dual-table support (HIGHEST PRIORITY)
    if ($kindIdsArray && !empty($kindIdsArray)) {
      $placeholders = [];
      foreach ($kindIdsArray as $index => $kindId) {
        $paramKey = ':kind_id_' . $index;
        $placeholders[] = $paramKey;
        $params[$paramKey] = (int) $kindId;
      }
      $whereConditions .= " AND l.kind_id IN (" . implode(',', $placeholders) . ")";
    }
    
    // Add filter conditions
    if ($filter === 'expired') {
      $whereConditions .= " AND l.license_expiry_date < CURDATE()";
    } elseif ($filter === 'expiring') {
      $whereConditions .= " AND l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($filter === 'incomplete') {
      $whereConditions .= " AND (" . $this->getIncompleteConditions() . ")";
    } elseif ($filter === 'annulated') {
      $whereConditions .= " AND l.status = 'ANNULATED'";
    } elseif ($filter === 'modified') {
      $whereConditions .= " AND l.status = 'MODIFIED'";
    } elseif ($filter === 'prorogated') {
      $whereConditions .= " AND l.status = 'PROROGATED'";
    }

    // Add advanced filters
    if ($clientId > 0) {
      $whereConditions .= " AND l.client_id = :client_id";
      $params[':client_id'] = $clientId;
    }
    
    if ($transportModeId > 0) {
      $whereConditions .= " AND l.transport_mode_id = :transport_mode_id";
      $params[':transport_mode_id'] = $transportModeId;
    }
    
    // Date filters use created_at
    if (!empty($startDate)) {
      $whereConditions .= " AND DATE(l.created_at) >= :start_date";
      $params[':start_date'] = $startDate;
    }
    
    if (!empty($endDate)) {
      $whereConditions .= " AND DATE(l.created_at) <= :end_date";
      $params[':end_date'] = $endDate;
    }

    // Add search filter
    if (!empty($searchValue)) {
      $searchParam = "%$searchValue%";
      $whereConditions .= " AND (
        l.license_number LIKE :search1
        OR c.short_name LIKE :search2
        OR b.bank_name LIKE :search3
        OR (l.invoice_number IS NOT NULL AND l.invoice_number LIKE :search4)
        OR (l.ref_cod IS NOT NULL AND l.ref_cod LIKE :search5)
        OR (l.fsi IS NOT NULL AND l.fsi LIKE :search6)
        OR (l.aur IS NOT NULL AND l.aur LIKE :search7)
        OR (l.supplier IS NOT NULL AND l.supplier LIKE :search8)
      )";
      $params[':search1'] = $searchParam;
      $params[':search2'] = $searchParam;
      $params[':search3'] = $searchParam;
      $params[':search4'] = $searchParam;
      $params[':search5'] = $searchParam;
      $params[':search6'] = $searchParam;
      $params[':search7'] = $searchParam;
      $params[':search8'] = $searchParam;
    }

    try {
      // Get count
      $countSql = "SELECT COUNT(*) as total " . $baseSql . $whereConditions;
      $totalRecords = $this->db->customQuery($countSql, $params);
      $recordsTotal = ($totalRecords && isset($totalRecords[0]['total'])) ? $totalRecords[0]['total'] : 0;

      // Get data
      $dataSql = "SELECT l.*, c.short_name as client_name, b.bank_name " . $baseSql . $whereConditions . " ORDER BY $orderBy $orderDir LIMIT $start, $length";
      $data = $this->db->customQuery($dataSql, $params);
      
      if (!$data || !is_array($data)) {
        $data = [];
      }

      echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
      ]);
      
    } catch (Exception $e) {
      error_log("List Licenses Error: " . $e->getMessage());
      error_log("SQL: " . ($dataSql ?? 'N/A'));
      error_log("Params: " . print_r($params, true));
      
      echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Failed to load licenses'
      ]);
    }
  }

  // ===== EXCEL EXPORT =====

  /**
   * Export single license to Excel
   */
  private function exportLicense()
  {
    $licenseId = (int) ($_GET['id'] ?? 0);

    if ($licenseId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid license ID']);
      return;
    }

    try {
      $this->requirePhpSpreadsheet();

      $license = $this->fetchLicenseForExport($licenseId);
      
      if (empty($license)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'License not found']);
        return;
      }

      $spreadsheet = $this->createLicenseSpreadsheet($license);
      
      $filename = 'License_' . ($license['license_number'] ?? 'Export') . '.xlsx';
      
      $this->outputExcelFile($spreadsheet, $filename);
      
    } catch (Exception $e) {
      error_log("Export Error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  /**
   * Export all licenses to Excel with separate sheets per Transport Mode
   */
  private function exportAllLicenses()
  {
    try {
      $this->requirePhpSpreadsheet();

      $filter = $_GET['filter'] ?? 'all';
      $searchValue = $_GET['search'] ?? '';

      $licenses = $this->fetchAllLicensesForExport($filter, $searchValue);

      if (empty($licenses)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No licenses found to export']);
        return;
      }

      // Group licenses by transport mode
      $groupedLicenses = [];
      foreach ($licenses as $license) {
        $transportMode = $license['transport_mode_name'] ?? 'Unknown';
        if (!isset($groupedLicenses[$transportMode])) {
          $groupedLicenses[$transportMode] = [];
        }
        $groupedLicenses[$transportMode][] = $license;
      }

      // Create spreadsheet with multiple sheets
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheetIndex = 0;

      foreach ($groupedLicenses as $transportMode => $modeLicenses) {
        if ($sheetIndex === 0) {
          $sheet = $spreadsheet->getActiveSheet();
        } else {
          $sheet = $spreadsheet->createSheet();
        }
        
        // Set sheet title (max 31 chars for Excel compatibility)
        $sheetTitle = substr($transportMode, 0, 31);
        $sheet->setTitle($sheetTitle);

        // Add headers
        $headers = [
          'ID', 'License Number', 'Kind', 'Bank', 'Client', 'License Cleared By', 
          'Type of Goods', 'Weight', 'M3', 'Unit', 'Currency', 'FOB Declared', 
          'Insurance', 'Freight', 'Other Costs', 'Transport Mode', 'Invoice Number', 
          'Invoice Date', 'Supplier', 'Applied Date', 'Validation Date', 'Expiry Date', 
          'FSI/FSO', 'AUR', 'Entry Post/Exit Post', 'REF. COD', 'Payment Method', 
          'Payment Subtype', 'Destination/Origin', 'Status'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Add data rows
        $rowIndex = 2;
        foreach ($modeLicenses as $license) {
          $rowData = $this->prepareLicenseRowData($license, false);
          $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
          $rowIndex++;
        }

        // Style the sheet
        $this->styleExcelHeader($sheet, count($headers), self::EXCEL_HEADER_COLOR_ALL);
        $this->styleExcelBody($sheet, count($headers), $rowIndex - 1);
        $this->autoSizeColumns($sheet, count($headers), 15);
        $sheet->setAutoFilter('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1');

        $sheetIndex++;
      }

      // Set first sheet as active
      $spreadsheet->setActiveSheetIndex(0);

      $filterName = $filter !== 'all' ? '_' . ucfirst($filter) : '';
      $filename = 'All_Licenses' . $filterName . '.xlsx';
      
      $this->outputExcelFile($spreadsheet, $filename);
      
    } catch (Exception $e) {
      error_log("Export All Error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  /**
   * Require PhpSpreadsheet library
   */
  private function requirePhpSpreadsheet()
  {
    $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
    
    if (!file_exists($vendorPath)) {
      throw new Exception('PhpSpreadsheet not found. Please run: composer require phpoffice/phpspreadsheet');
    }
    
    require_once $vendorPath;
  }

  /**
   * Fetch license data for export
   */
  private function fetchLicenseForExport($licenseId)
  {
    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              k.kind_name,
              tg.goods_type,
              u.unit_name,
              cur.currency_name,
              cur.currency_short_name,
              tm.transport_mode_name,
              ep.transit_point_name as entry_post_name,
              pm.payment_method_name,
              ps.payment_subtype,
              o.origin_name as destination_name,
              db.done_by_name as license_cleared_by_name
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            LEFT JOIN kind_master_t k ON l.kind_id = k.id
            LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
            LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
            LEFT JOIN transit_point_master_t ep ON l.entry_post_id = ep.id
            LEFT JOIN payment_method_master_t pm ON l.payment_method_id = pm.id
            LEFT JOIN payment_subtype_master_t ps ON l.payment_subtype_id = ps.id
            LEFT JOIN origin_master_t o ON l.destination_id = o.id
            LEFT JOIN done_by_t db ON l.license_cleared_by = db.id
            WHERE l.id = :id";

    $result = $this->db->customQuery($sql, [':id' => $licenseId]);
    
    return $result[0] ?? null;
  }

  /**
   * ✅ UPDATED: Fetch all licenses for export with dual-table support
   */
  private function fetchAllLicensesForExport($filter, $searchValue)
  {
    // ✅ NEW: Get kind_ids for import/export filtering
    $kindIds = trim($_GET['kind_ids'] ?? '');
    $kindIdsArray = !empty($kindIds) ? explode(',', $kindIds) : null;
    
    $clientId = (int) ($_GET['client_id'] ?? 0);
    $transportModeId = (int) ($_GET['transport_mode_id'] ?? 0);
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate = trim($_GET['end_date'] ?? '');
    
    $sql = "SELECT l.*, 
              c.short_name as client_name,
              b.bank_name,
              k.kind_name,
              tg.goods_type,
              u.unit_name,
              cur.currency_short_name,
              tm.transport_mode_name,
              ep.transit_point_name as entry_post_name,
              pm.payment_method_name,
              ps.payment_subtype,
              o.origin_name as destination_name,
              db.done_by_name as license_cleared_by_name
            FROM licenses_t l
            LEFT JOIN clients_t c ON l.client_id = c.id
            LEFT JOIN banklist_master_t b ON l.bank_id = b.id
            LEFT JOIN kind_master_t k ON l.kind_id = k.id
            LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
            LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id
            LEFT JOIN currency_master_t cur ON l.currency_id = cur.id
            LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
            LEFT JOIN transit_point_master_t ep ON l.entry_post_id = ep.id
            LEFT JOIN payment_method_master_t pm ON l.payment_method_id = pm.id
            LEFT JOIN payment_subtype_master_t ps ON l.payment_subtype_id = ps.id
            LEFT JOIN origin_master_t o ON l.destination_id = o.id
            LEFT JOIN done_by_t db ON l.license_cleared_by = db.id
            WHERE 1=1";

    $params = [];

    // ✅ NEW: Add kind_ids filter for dual-table support
    if ($kindIdsArray && !empty($kindIdsArray)) {
      $placeholders = [];
      foreach ($kindIdsArray as $index => $kindId) {
        $paramKey = ':kind_id_' . $index;
        $placeholders[] = $paramKey;
        $params[$paramKey] = (int) $kindId;
      }
      $sql .= " AND l.kind_id IN (" . implode(',', $placeholders) . ")";
    }

    // Add filter conditions
    if ($filter === 'expired') {
      $sql .= " AND l.license_expiry_date < CURDATE()";
    } elseif ($filter === 'expiring') {
      $sql .= " AND l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($filter === 'incomplete') {
      $sql .= " AND (" . $this->getIncompleteConditions() . ")";
    } elseif ($filter === 'annulated') {
      $sql .= " AND l.status = 'ANNULATED'";
    } elseif ($filter === 'modified') {
      $sql .= " AND l.status = 'MODIFIED'";
    } elseif ($filter === 'prorogated') {
      $sql .= " AND l.status = 'PROROGATED'";
    }

    // Apply advanced filters
    if ($clientId > 0) {
      $sql .= " AND l.client_id = :client_id";
      $params[':client_id'] = $clientId;
    }
    
    if ($transportModeId > 0) {
      $sql .= " AND l.transport_mode_id = :transport_mode_id";
      $params[':transport_mode_id'] = $transportModeId;
    }
    
    // Date filters use created_at
    if (!empty($startDate)) {
      $sql .= " AND DATE(l.created_at) >= :start_date";
      $params[':start_date'] = $startDate;
    }
    
    if (!empty($endDate)) {
      $sql .= " AND DATE(l.created_at) <= :end_date";
      $params[':end_date'] = $endDate;
    }

    // Apply search
    if (!empty($searchValue)) {
      $searchParam = "%$searchValue%";
      $sql .= " AND (
        l.license_number LIKE :search1
        OR c.short_name LIKE :search2
        OR b.bank_name LIKE :search3
        OR (l.invoice_number IS NOT NULL AND l.invoice_number LIKE :search4)
        OR (l.ref_cod IS NOT NULL AND l.ref_cod LIKE :search5)
        OR (l.fsi IS NOT NULL AND l.fsi LIKE :search6)
        OR (l.aur IS NOT NULL AND l.aur LIKE :search7)
        OR (l.supplier IS NOT NULL AND l.supplier LIKE :search8)
      )";
      $params[':search1'] = $searchParam;
      $params[':search2'] = $searchParam;
      $params[':search3'] = $searchParam;
      $params[':search4'] = $searchParam;
      $params[':search5'] = $searchParam;
      $params[':search6'] = $searchParam;
      $params[':search7'] = $searchParam;
      $params[':search8'] = $searchParam;
    }

    $sql .= " ORDER BY tm.transport_mode_name ASC, l.id DESC";

    return $this->db->customQuery($sql, $params);
  }

  /**
   * Create Excel spreadsheet for single license
   */
  private function createLicenseSpreadsheet($data)
  {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('License Details');

    $headers = [
      'ID', 'License Number', 'Kind', 'Bank', 'Client', 'License Cleared By', 'Type of Goods', 'Weight', 'M3',
      'Unit of Measurement', 'Currency', 'FOB Declared', 'Insurance', 'Freight', 'Other Costs',
      'Transport Mode', 'Invoice Number', 'Invoice Date', 'Supplier', 'Invoice File',
      'License Applied Date', 'License Validation Date', 'License Expiry Date', 'FSI/FSO', 'AUR',
      'Entry Post/Exit Post', 'REF. COD', 'License File', 'Payment Method', 'Payment Subtype', 'Destination/Origin',
      'Status', 'Created At', 'Updated At'
    ];
    
    $values = $this->prepareLicenseRowData($data, true);

    $excelData = [$headers, $values];
    $sheet->fromArray($excelData, null, 'A1');

    $this->styleExcelHeader($sheet, count($headers), self::EXCEL_HEADER_COLOR);
    $this->styleExcelBody($sheet, count($headers), 2);
    $this->autoSizeColumns($sheet, count($headers));

    return $spreadsheet;
  }

  /**
   * Prepare license data for Excel row
   */
  private function prepareLicenseRowData($license, $detailed = true)
  {
    $baseData = [
      $license['id'] ?? '',
      $license['license_number'] ?? 'N/A',
      $license['kind_name'] ?? 'N/A',
      $license['bank_name'] ?? 'N/A',
      $license['client_name'] ?? 'N/A',
      $license['license_cleared_by_name'] ?? 'N/A',
      $license['goods_type'] ?? 'N/A',
      $license['weight'] ?? 'N/A',
      $license['m3'] !== null && $license['m3'] > 0 ? $license['m3'] : 'N/A',
      $license['unit_name'] ?? 'N/A',
      $license['currency_short_name'] ?? 'N/A',
      $license['fob_declared'] ?? 'N/A',
      $license['insurance'] !== null ? $license['insurance'] : 'N/A',
      $license['freight'] !== null ? $license['freight'] : 'N/A',
      $license['other_costs'] !== null ? $license['other_costs'] : 'N/A',
      $license['transport_mode_name'] ?? 'N/A',
      $license['invoice_number'] ?? 'N/A',
      $license['invoice_date'] ? date('Y-m-d', strtotime($license['invoice_date'])) : 'N/A',
      $license['supplier'] ?? 'N/A',
    ];

    if ($detailed) {
      $baseData[] = $license['invoice_file'] ?? 'N/A';
    }

    $baseData = array_merge($baseData, [
      $license['license_applied_date'] ? date('Y-m-d', strtotime($license['license_applied_date'])) : 'N/A',
      $license['license_validation_date'] ? date('Y-m-d', strtotime($license['license_validation_date'])) : 'N/A',
      $license['license_expiry_date'] ? date('Y-m-d', strtotime($license['license_expiry_date'])) : 'N/A',
      $license['fsi'] ?? 'N/A',
      $license['aur'] ?? 'N/A',
      $license['entry_post_name'] ?? 'N/A',
      $license['ref_cod'] ?? 'N/A',
    ]);

    if ($detailed) {
      $baseData[] = $license['license_file'] ?? 'N/A';
    }

    $baseData = array_merge($baseData, [
      $license['payment_method_name'] ?? 'N/A',
      $license['payment_subtype'] ?? 'N/A',
      $license['destination_name'] ?? 'N/A',
      $license['status'] ?? 'N/A'
    ]);

    if ($detailed) {
      $baseData[] = $license['created_at'] ?? 'N/A';
      $baseData[] = $license['updated_at'] ?? 'N/A';
    }

    return $baseData;
  }

  /**
   * Style Excel header row
   */
  private function styleExcelHeader($sheet, $columnCount, $color)
  {
    $headerStyle = [
      'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11
      ],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => $color]
      ],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ],
      'borders' => [
        'allBorders' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => '000000']
        ]
      ]
    ];
    
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
    $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(25);
  }

  /**
   * Style Excel body rows
   */
  private function styleExcelBody($sheet, $columnCount, $lastRow)
  {
    $bodyStyle = [
      'borders' => [
        'allBorders' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
          'color' => ['rgb' => 'CCCCCC']
        ]
      ],
      'alignment' => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        'wrapText' => true
      ]
    ];
    
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
    $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray($bodyStyle);
  }

  /**
   * Auto-size columns in Excel
   */
  private function autoSizeColumns($sheet, $columnCount, $width = 18)
  {
    for ($i = 1; $i <= $columnCount; $i++) {
      $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
      $sheet->getColumnDimension($column)->setWidth($width);
    }
  }

  /**
   * Output Excel file to browser
   */
  private function outputExcelFile($spreadsheet, $filename)
  {
    $filepath = UPLOAD_PATH . '/' . $filename;

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filepath);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: max-age=0');

    readfile($filepath);
    
    unlink($filepath);
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
  }

  // ===== VALIDATION =====

  /**
   * Validate license data
   */
  private function validateLicenseData($post, $licenseId = null, $isMCAType = false, $isSpecialType = false)
  {
    $errors = [];

    // Always required fields
    $alwaysRequired = ['kind_id', 'client_id', 'type_of_goods_id'];
    foreach ($alwaysRequired as $field) {
      if (empty($post[$field]) || (int) $post[$field] <= 0) {
        $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
      }
    }

    // MCA type specific validation
    if ($isMCAType) {
      $mcaRequired = ['transport_mode_id', 'currency_id', 'license_number'];
      foreach ($mcaRequired as $field) {
        if (empty(trim($post[$field] ?? ''))) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
        }
      }

      if (!empty($errors)) {
        return ['success' => false, 'message' => '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>'];
      }

      // Check uniqueness
      if (!$this->checkLicenseNumberUniqueness($post['license_number'], $licenseId)) {
        $errors[] = 'License Number already exists';
      }

      if (!empty($errors)) {
        return ['success' => false, 'message' => '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>'];
      }

      return ['success' => true];
    }

    // Standard validation for non-MCA types
    $standardRequired = [
      'bank_id', 'license_cleared_by', 'unit_of_measurement_id', 'currency_id',
      'transport_mode_id', 'license_validation_date', 'license_expiry_date',
      'license_number', 'entry_post_id', 'payment_method_id', 'destination_id',
      'supplier'
    ];

    // Add invoice-related fields only for non-special types
    if (!$isSpecialType) {
      $standardRequired[] = 'invoice_number';
      $standardRequired[] = 'invoice_date';
      $standardRequired[] = 'license_applied_date';
    }

    foreach ($standardRequired as $field) {
      if (empty($post[$field]) || (is_numeric($post[$field]) && (int) $post[$field] <= 0)) {
        if ($field === 'entry_post_id' && $isSpecialType) {
          $errors[] = 'Entry Post/Exit Post is required';
        } else {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required';
        }
      }
    }

    // Numeric validations
    $numericFields = ['weight', 'fob_declared', 'insurance', 'freight', 'other_costs', 'm3'];
    foreach ($numericFields as $field) {
      if (isset($post[$field]) && $post[$field] !== '' && (float) $post[$field] < 0) {
        $errors[] = ucwords(str_replace('_', ' ', $field)) . ' cannot be negative';
      }
    }

    // M3 validation when type_of_goods_id = 3
    $typeOfGoodsId = (int) ($post['type_of_goods_id'] ?? 0);
    if ($typeOfGoodsId === self::TYPE_OF_GOODS_M3_ID && !$isMCAType && !$isSpecialType) {
      if (empty($post['m3']) || (float) $post['m3'] <= 0) {
        $errors[] = 'M3 is required and must be positive for this type of goods';
      }
    }

    // Date validations - only for non-special types
    if (!$isSpecialType) {
      if (!empty($post['invoice_date']) && strtotime($post['invoice_date']) > time()) {
        $errors[] = 'Invoice Date cannot be in the future';
      }

      if (!empty($post['license_applied_date']) && !empty($post['license_validation_date'])) {
        if (strtotime($post['license_applied_date']) > strtotime($post['license_validation_date'])) {
          $errors[] = 'Validation Date must be ≥ Applied Date';
        }
      }
    }

    if (!empty($post['license_validation_date']) && !empty($post['license_expiry_date'])) {
      if (strtotime($post['license_validation_date']) > strtotime($post['license_expiry_date'])) {
        $errors[] = 'Expiry Date must be ≥ Validation Date';
      }
    }

    // Uniqueness checks
    if (!$this->checkLicenseNumberUniqueness($post['license_number'], $licenseId)) {
      $errors[] = 'License Number already exists';
    }

    // Invoice uniqueness only for non-special types
    if (!$isSpecialType && !empty($post['invoice_number']) && !$this->checkInvoiceNumberUniqueness($post['invoice_number'], $licenseId)) {
      $errors[] = 'Invoice Number already exists';
    }

    if (!empty($errors)) {
      return ['success' => false, 'message' => '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>'];
    }

    return ['success' => true];
  }

  /**
   * Check license number uniqueness
   */
  private function checkLicenseNumberUniqueness($licenseNumber, $excludeId = null)
  {
    $sql = "SELECT id FROM licenses_t WHERE license_number = :license_number";
    $params = [':license_number' => trim($licenseNumber)];

    if ($excludeId) {
      $sql .= " AND id != :id";
      $params[':id'] = $excludeId;
    }

    $exists = $this->db->customQuery($sql, $params);
    return empty($exists);
  }

  /**
   * Check invoice number uniqueness
   */
  private function checkInvoiceNumberUniqueness($invoiceNumber, $excludeId = null)
  {
    $sql = "SELECT id FROM licenses_t WHERE invoice_number = :invoice_number";
    $params = [':invoice_number' => trim($invoiceNumber)];

    if ($excludeId) {
      $sql .= " AND id != :id";
      $params[':id'] = $excludeId;
    }

    $exists = $this->db->customQuery($sql, $params);
    return empty($exists);
  }

  /**
   * Prepare license data for database
   */
  private function prepareLicenseData($post, $isMCAType = false, $isSpecialType = false)
  {
    $data = [
      'kind_id' => $this->toInt($post['kind_id'] ?? 0),
      'client_id' => $this->toInt($post['client_id'] ?? 0),
      'type_of_goods_id' => $this->toInt($post['type_of_goods_id'] ?? 0),
      'transport_mode_id' => $this->toInt($post['transport_mode_id'] ?? 0),
      'currency_id' => $this->toInt($post['currency_id'] ?? 0),
      'license_number' => $this->clean($post['license_number'] ?? ''),
      'status' => $this->clean($post['status'] ?? 'ACTIVE')
    ];

    if ($isMCAType) {
      $data = array_merge($data, $this->getMCADefaultValues());
    } elseif ($isSpecialType) {
      $data = array_merge($data, $this->getSpecialTypeValues($post));
    } else {
      $data = array_merge($data, $this->getStandardLicenseValues($post));
    }

    // Auto-sync all currency fields
    $currencyId = $this->toInt($post['currency_id'] ?? 1);
    $data['fob_currency_id'] = $currencyId;
    $data['insurance_currency_id'] = $currencyId;
    $data['freight_currency_id'] = $currencyId;
    $data['other_costs_currency_id'] = $currencyId;

    return $data;
  }

  /**
   * Get default values for MCA type licenses
   */
  private function getMCADefaultValues()
  {
    return [
      'bank_id' => null,
      'license_cleared_by' => null,
      'weight' => null,
      'm3' => null,
      'unit_of_measurement_id' => null,
      'fob_declared' => null,
      'insurance' => null,
      'freight' => null,
      'other_costs' => null,
      'invoice_number' => null,
      'invoice_date' => null,
      'supplier' => null,
      'license_applied_date' => null,
      'license_validation_date' => null,
      'license_expiry_date' => null,
      'fsi' => null,
      'aur' => null,
      'entry_post_id' => null,
      'ref_cod' => null,
      'payment_method_id' => null,
      'payment_subtype_id' => null,
      'destination_id' => null,
      'fob_currency_id' => null,
      'insurance_currency_id' => null,
      'freight_currency_id' => null,
      'other_costs_currency_id' => null
    ];
  }

  /**
   * Get field values for Special type licenses
   */
  private function getSpecialTypeValues($post)
  {
    return [
      'bank_id' => $this->toInt($post['bank_id'] ?? 0),
      'license_cleared_by' => $this->toInt($post['license_cleared_by'] ?? 0),
      'weight' => (float) ($post['weight'] ?? 0),
      'm3' => isset($post['m3']) && $post['m3'] !== '' ? (float) $post['m3'] : null,
      'unit_of_measurement_id' => $this->toInt($post['unit_of_measurement_id'] ?? 0),
      'fob_declared' => (float) ($post['fob_declared'] ?? 0),
      'insurance' => isset($post['insurance']) && $post['insurance'] !== '' ? (float) $post['insurance'] : null,
      'freight' => isset($post['freight']) && $post['freight'] !== '' ? (float) $post['freight'] : null,
      'other_costs' => isset($post['other_costs']) && $post['other_costs'] !== '' ? (float) $post['other_costs'] : null,
      'invoice_number' => !empty($post['invoice_number']) ? $this->clean($post['invoice_number']) : null,
      'invoice_date' => !empty($post['invoice_date']) ? date('Y-m-d', strtotime($post['invoice_date'])) : null,
      'supplier' => $this->clean($post['supplier'] ?? ''),
      'license_applied_date' => !empty($post['license_applied_date']) ? date('Y-m-d', strtotime($post['license_applied_date'])) : null,
      'license_validation_date' => !empty($post['license_validation_date']) ? date('Y-m-d', strtotime($post['license_validation_date'])) : null,
      'license_expiry_date' => !empty($post['license_expiry_date']) ? date('Y-m-d', strtotime($post['license_expiry_date'])) : null,
      'fsi' => $this->clean($post['fsi'] ?? ''),
      'aur' => $this->clean($post['aur'] ?? ''),
      'entry_post_id' => $this->toInt($post['entry_post_id'] ?? 0),
      'ref_cod' => !empty($post['ref_cod']) ? $this->clean($post['ref_cod']) : null,
      'payment_method_id' => $this->toInt($post['payment_method_id'] ?? 0),
      'payment_subtype_id' => $this->toInt($post['payment_subtype_id'] ?? null),
      'destination_id' => $this->toInt($post['destination_id'] ?? 0)
    ];
  }

  /**
   * Get standard license field values
   */
  private function getStandardLicenseValues($post)
  {
    return [
      'bank_id' => $this->toInt($post['bank_id'] ?? 0),
      'license_cleared_by' => $this->toInt($post['license_cleared_by'] ?? 0),
      'weight' => (float) ($post['weight'] ?? 0),
      'm3' => isset($post['m3']) && $post['m3'] !== '' ? (float) $post['m3'] : null,
      'unit_of_measurement_id' => $this->toInt($post['unit_of_measurement_id'] ?? 0),
      'fob_declared' => (float) ($post['fob_declared'] ?? 0),
      'insurance' => isset($post['insurance']) && $post['insurance'] !== '' ? (float) $post['insurance'] : null,
      'freight' => isset($post['freight']) && $post['freight'] !== '' ? (float) $post['freight'] : null,
      'other_costs' => isset($post['other_costs']) && $post['other_costs'] !== '' ? (float) $post['other_costs'] : null,
      'invoice_number' => $this->clean($post['invoice_number'] ?? ''),
      'invoice_date' => !empty($post['invoice_date']) ? date('Y-m-d', strtotime($post['invoice_date'])) : null,
      'supplier' => $this->clean($post['supplier'] ?? ''),
      'license_applied_date' => !empty($post['license_applied_date']) ? date('Y-m-d', strtotime($post['license_applied_date'])) : null,
      'license_validation_date' => !empty($post['license_validation_date']) ? date('Y-m-d', strtotime($post['license_validation_date'])) : null,
      'license_expiry_date' => !empty($post['license_expiry_date']) ? date('Y-m-d', strtotime($post['license_expiry_date'])) : null,
      'fsi' => $this->clean($post['fsi'] ?? ''),
      'aur' => $this->clean($post['aur'] ?? ''),
      'entry_post_id' => $this->toInt($post['entry_post_id'] ?? 0),
      'ref_cod' => $this->clean($post['ref_cod'] ?? ''),
      'payment_method_id' => $this->toInt($post['payment_method_id'] ?? 0),
      'payment_subtype_id' => $this->toInt($post['payment_subtype_id'] ?? null),
      'destination_id' => $this->toInt($post['destination_id'] ?? 0)
    ];
  }

  // ===== FILE HANDLING =====

  /**
   * Handle file uploads with validation
   */
  private function handleFileUploads($isUpdate = false, $isSpecialType = false)
  {
    $fileFields = ['invoice_file', 'license_file'];
    $uploadedFiles = [];
    $errors = [];
    $license_number = preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['license_number']);
    $baseDir = UPLOAD_PATH . '/licenses/';

    foreach ($fileFields as $field) {
      // Skip invoice_file validation for special types
      if ($field === 'invoice_file' && $isSpecialType) {
        if (!empty($_FILES[$field]['name'])) {
          $file = $_FILES[$field];
          
          if ($file['size'] > self::MAX_FILE_SIZE) {
            $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be less than 5MB';
            continue;
          }

          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          if (!in_array($ext, self::ALLOWED_FILE_EXTENSIONS)) {
            $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be PDF only';
            continue;
          }

          if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
              $errors[] = ucwords(str_replace('_', ' ', $field)) . ' has invalid file type - PDF only';
              continue;
            }
          }

          $handle = fopen($file['tmp_name'], 'rb');
          $header = fread($handle, 4);
          fclose($handle);
          
          if ($header !== '%PDF') {
            $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is not a valid PDF file';
            continue;
          }

          $subFolder = 'INVOICES';
          $targetDir = $baseDir . $subFolder . '/';
          if (!is_dir($targetDir)) {
            mkdir($targetDir, self::DIR_PERMISSIONS, true);
          }

          $fileName = $license_number . '_' . $field . '.' . $ext;
          $targetPath = $targetDir . $fileName;

          if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, self::FILE_PERMISSIONS);
            $uploadedFiles[$field] = 'uploads/licenses/' . $subFolder . '/' . $fileName;
          } else {
            $errors[] = 'Failed to upload ' . ucwords(str_replace('_', ' ', $field));
          }
        } else {
          $uploadedFiles[$field] = null;
        }
        continue;
      }

      if (!empty($_FILES[$field]['name'])) {
        $file = $_FILES[$field];

        if ($file['size'] > self::MAX_FILE_SIZE) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be less than 5MB';
          continue;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_FILE_EXTENSIONS)) {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' must be PDF only';
          continue;
        }

        if (function_exists('mime_content_type')) {
          $mimeType = mime_content_type($file['tmp_name']);
          if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            $errors[] = ucwords(str_replace('_', ' ', $field)) . ' has invalid file type - PDF only';
            continue;
          }
        }
        
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        
        if ($header !== '%PDF') {
          $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is not a valid PDF file';
          continue;
        }

        $subFolder = ($field === 'invoice_file') ? 'INVOICES' : 'LICENSES';
        
        $targetDir = $baseDir . $subFolder . '/';
        if (!is_dir($targetDir)) {
          mkdir($targetDir, self::DIR_PERMISSIONS, true);
        }

        $fileName = $license_number . '_' . $field . '.' . $ext;
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
          chmod($targetPath, self::FILE_PERMISSIONS);
          $uploadedFiles[$field] = 'uploads/licenses/' . $subFolder . '/' . $fileName;
        } else {
          $errors[] = 'Failed to upload ' . ucwords(str_replace('_', ' ', $field));
        }
      } else {
        $uploadedFiles[$field] = null;
      }
    }

    return [
      'success' => empty($errors),
      'files' => $uploadedFiles,
      'errors' => $errors
    ];
  }

  /**
   * Delete license files from filesystem
   */
  private function deleteLicenseFiles($license)
  {
    $fileFields = [
      'invoice_file' => 'INVOICES',
      'license_file' => 'LICENSES'
    ];

    $baseDir = UPLOAD_PATH . '/licenses/';

    foreach ($fileFields as $field => $folder) {
      if (!empty($license[$field])) {
        $filePath = $baseDir . $folder . '/' . basename($license[$field]);

        if (file_exists($filePath)) {
          unlink($filePath);
          error_log("Deleted file: " . $filePath);
        }
      }
    }
  }

  /**
   * Cleanup uploaded files (on error)
   */
  private function cleanupFiles($files)
  {
    foreach ($files as $fileName) {
      if (!empty($fileName)) {
        $fullPath = PUBLIC_PATH . '/' . $fileName;
        if (file_exists($fullPath)) {
          unlink($fullPath);
        }
      }
    }
  }

  // ===== HELPER METHODS =====

  /**
   * Clean and sanitize input string
   */
  private function clean($value)
  {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Convert to integer or null
   */
  private function toInt($value)
  {
    return $value && (int) $value > 0 ? (int) $value : null;
  }
}