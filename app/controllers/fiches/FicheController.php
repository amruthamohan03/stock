<?php

class FicheController extends Controller
{
  private $db;
  private $logFile;
  private $allowedFilters = ['created', 'verified', 'audited'];
  private $incoterms;

  public function __construct()
  {
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/fiche_operations.log';
    
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    
    // INCOTERM data
    $this->incoterms = [
      'EXW' => "L'acheteur est libre de venir chercher les marchandises à l'usine du vendeur (EXW = nom de l'usine, de l'entrepot).",
      'CPT' => "Le vendeur paie le transport jusqu'au lieu convenu avec l'acheteur (CPT = lieu convenu, généralement villes). CPT est utilisé pour toutes formes de tranport (routier, aérien et maritime).",
      'CFR' => "S'applique uniquement par la voie de mer. Le vendeur prend en charge le fret jusqu'au port de déchargement (CFR = port de débarquement)",
      'FOB' => "Uniquement aux transports maritimes. Les risques sont pour l'acheteur dès le moment où la marchandise est chargée sur le navire et ce dernier paie le transport du navire qu'il a choisi (FOB = nom du port d'embarquement)",
      'FAS' => "Uniquement aux transports maritimes. Le vendeur doit livrer les marchandises et assume les coûts de dédouanement et risques jusqu'à ce que les marchandises soient placées au quai du port le long du navire (FAS = Port d'embarquement)",
      'CIF' => "Uniquement aux transports maritimes. Le vendeur supporte les coûts du fret et de l'assurance, celle-ci n'est pas à tous risques (CIF = port de destination)",
      'CIP' => "Peut-être utilisé à l'ensemble des modes de transport (route, maritime, aérien, fluvial). Assurance tous risques et le vendeur supporte les coûts de transport jusqu'au lieu de destination indiqué par l'incoterm (CIP = lieu convenu)",
      'FCA' => "Le vendeur paie la douane de son pays lorsqu'il remet la marchandise au transporteur choisi par l'acheteur en un lieu convenu (port, aéroport, un terminal de transport). FCA = port d'embarquement",
      'DDP' => "Le vendeur assume l'entière responsabilité des risques, coûts et formalités liées à la livraison jusqu'à ce que la marchandise arrive à son destinataire (même le déchargement), DDP = sur le site",
      'DAP' => "Le vendeur livre les marchandises à un endroit convenu au préalable entre lui et son acheteur. L'acheteur a l'obligation de régler les taxes d'importation, DAP = sur le site"
    ];
  }

  /**
   * Index page - Display fiche form and list
   */
  public function index()
  {
    // Generate new CSRF token
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['csrf_token_time'] = time();
    }

    
    try {
      
      $sql = "SELECT DISTINCT license_number FROM tracking WHERE status = 'active' ORDER BY license_number";
      $licenses = $this->db->customQuery($sql);

    } catch (PDOException $e) {
      $this->logError('Error fetching licenses', ['error' => $e->getMessage()]);
      $licenses = [];
    }

    $currencies = ['USD', 'EUR', 'GBP', 'CDF', 'ZAR', 'CNY', 'JPY', 'AUD', 'CAD'];

    $data = [
      'title' => 'Fiche De Calculs Management',
      'licenses' => $this->sanitizeArray($licenses),
      'currencies' => $currencies,
      'incoterms' => $this->incoterms,
      'csrf_token' => $_SESSION['csrf_token']
    ];

    $this->viewWithLayout('fiches/fiches', $data);
  }

  /**
   * CRUD Data Router
   */
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
          $this->insertFiche();
          break;
        case 'update':
          $this->updateFiche();
          break;
        case 'deletion':
          $this->deleteFiche();
          break;
        case 'getFiche':
          $this->getFiche();
          break;
        case 'listing':
          $this->listFiches();
          break;
        case 'statistics':
          $this->getStatistics();
          break;
        case 'getMCAReferences':
          $this->getMCAReferences();
          break;
        case 'getMCADetails':
          $this->getMCADetails();
          break;
        case 'exportFiche':
          $this->exportFiche();
          break;
        case 'getFicheItems':
          $this->getFicheItems();
          break;
        case 'getPositionTarifs':
          $this->getPositionTarifs();
          break;
        case 'verifyFiche':
          $this->verifyFiche();
          break;
        case 'auditFiche':
          $this->auditFiche();
          break;
        case 'getPrintContent':
          $this->getPrintContent();
          break;
        default:
          $this->logError('Invalid action attempted', ['action' => $action]);
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      $this->logError('Server error in crudData', [
        'action' => $action,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
    }
    exit;
  }

  /**
   * Get Position Tarifs for license
   */
  private function getPositionTarifs()
  {
    try {
      $licenseNumber = $this->sanitizeInput($_GET['license_number'] ?? '');
      $search = $this->sanitizeInput($_GET['search'] ?? '');

      if (empty($licenseNumber)) {
        echo json_encode(['success' => false, 'message' => 'Invalid license number']);
        return;
      }

      $sql = "SELECT id, tarif_code, ddi, ica, dci, dcl, tpi
              FROM license_products 
              WHERE license_number = :license_number";
      
      $params = [':license_number' => $licenseNumber];
      
      if (!empty($search)) {
        $sql .= " AND (tarif_code LIKE :search OR description LIKE :search)";
        $params[':search'] = "%{$search}%";
      }
      
      $sql .= " ORDER BY tarif_code ASC";

      $tarifs = $this->db->customQuery($sql, $params);
      $tarifs = $this->sanitizeArray($tarifs);

      echo json_encode([
        'success' => true,
        'data' => $tarifs ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get position tarifs', [
        'license_number' => $licenseNumber ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load position tarifs: ' . $e->getMessage()]);
    }
  }

  /**
   * Get Fiche Items
   */
  private function getFicheItems()
  {
    try {
      $ficheId = (int)($_POST['fiche_id'] ?? $_GET['fiche_id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      $sql = "SELECT * FROM fiche_items WHERE fiche_id = :fiche_id ORDER BY numero ASC";
      $items = $this->db->customQuery($sql, [':fiche_id' => $ficheId]);
      $items = $this->sanitizeArray($items);

      echo json_encode([
        'success' => true,
        'data' => $items ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get fiche items', [
        'fiche_id' => $ficheId ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load fiche items: ' . $e->getMessage()]);
    }
  }

  /**
   * Verify Fiche (created -> verified)
   */
  private function verifyFiche()
  {
    $this->validateCsrfToken();

    try {
      $ficheId = (int)($_POST['fiche_id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      // Check current status
      $sql = "SELECT status FROM fiche_de_calculs WHERE id = :id";
      $result = $this->db->customQuery($sql, [':id' => $ficheId]);

      if (empty($result)) {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
        return;
      }

      $currentStatus = $result[0]['status'];

      if ($currentStatus !== 'created') {
        echo json_encode(['success' => false, 'message' => 'Only fiches with created status can be verified']);
        return;
      }

      // Update to verified
      $updateSql = "UPDATE fiche_de_calculs 
                    SET status = 'verified', 
                        updated_by = :updated_by, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id";

      $this->db->customQuery($updateSql, [
        ':id' => $ficheId,
        ':updated_by' => $_SESSION['user_id'] ?? 1
      ]);

      $this->logInfo('Fiche verified successfully', ['fiche_id' => $ficheId]);

      echo json_encode([
        'success' => true,
        'message' => 'Fiche de calcul has been verified successfully'
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to verify fiche', [
        'fiche_id' => $ficheId ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to verify fiche: ' . $e->getMessage()]);
    }
  }

  /**
   * Audit Fiche (verified -> audited)
   */
  private function auditFiche()
  {
    $this->validateCsrfToken();

    try {
      $ficheId = (int)($_POST['fiche_id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      // Check current status
      $sql = "SELECT status FROM fiche_de_calculs WHERE id = :id";
      $result = $this->db->customQuery($sql, [':id' => $ficheId]);

      if (empty($result)) {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
        return;
      }

      $currentStatus = $result[0]['status'];

      if ($currentStatus !== 'verified') {
        echo json_encode(['success' => false, 'message' => 'Only verified fiches can be audited']);
        return;
      }

      // Update to audited
      $updateSql = "UPDATE fiche_de_calculs 
                    SET status = 'audited', 
                        updated_by = :updated_by, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id";

      $this->db->customQuery($updateSql, [
        ':id' => $ficheId,
        ':updated_by' => $_SESSION['user_id'] ?? 1
      ]);

      $this->logInfo('Fiche audited successfully', ['fiche_id' => $ficheId]);

      echo json_encode([
        'success' => true,
        'message' => 'Fiche de calcul has been audited successfully'
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to audit fiche', [
        'fiche_id' => $ficheId ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to audit fiche: ' . $e->getMessage()]);
    }
  }

  /**
   * Get Print Content
   */
  private function getPrintContent()
  {
    try {
      $ficheId = (int)($_POST['fiche_id'] ?? $_GET['fiche_id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      $sql = "SELECT f.*, t.subscriber, t.supplier, t.type_of_goods, t.transport_mode, t.weight
              FROM fiche_de_calculs f
              LEFT JOIN tracking t ON f.tracking_id = t.id
              WHERE f.id = :id";

      $fiche = $this->db->customQuery($sql, [':id' => $ficheId]);

      if (empty($fiche)) {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
        return;
      }

      $ficheData = $fiche[0];

      // Get fiche items
      $itemsSql = "SELECT * FROM fiche_items WHERE fiche_id = :fiche_id ORDER BY numero ASC";
      $items = $this->db->customQuery($itemsSql, [':fiche_id' => $ficheId]);

      // Get company info
      try {
        $companySql = "SELECT short_name, company_name, id_nat_number, rccm_number, attestation_number
                       FROM clients LIMIT 1";
        $companyInfo = $this->db->customQuery($companySql);
        $companyInfo = !empty($companyInfo) ? $companyInfo[0] : [
          'short_name' => 'Company Name',
          'company_name' => 'Full Company Name',
          'id_nat_number' => '',
          'rccm_number' => '',
          'attestation_number' => ''
        ];
      } catch (Exception $e) {
        $companyInfo = [
          'short_name' => 'Company Name',
          'company_name' => 'Full Company Name',
          'id_nat_number' => '',
          'rccm_number' => '',
          'attestation_number' => ''
        ];
      }

      // Generate print HTML
      ob_start();
      include(VIEW_PATH . 'fiches/fiche_print_template.php');
      $printContent = ob_get_clean();

      echo json_encode([
        'success' => true,
        'content' => $printContent
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get print content', [
        'fiche_id' => $ficheId ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load print content: ' . $e->getMessage()]);
    }
  }

  /**
   * Export fiche to Excel
   */
  private function exportFiche()
  {
    try {
      $ficheId = (int)($_GET['id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      $sql = "SELECT f.*, t.subscriber, t.supplier, t.transport_mode, t.type_of_goods 
              FROM fiche_de_calculs f 
              LEFT JOIN tracking t ON f.tracking_id = t.id 
              WHERE f.id = :id";

      $fiche = $this->db->customQuery($sql, [':id' => $ficheId]);

      if (empty($fiche)) {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
        return;
      }

      $data = $fiche[0];

      // Generate filename
      $ficheRef = preg_replace('/[^A-Za-z0-9_-]/', '_', $data['fiche_reference']);
      $todayDate = date('Y-m-d');
      $filename = htmlspecialchars("{$ficheRef}_{$todayDate}", ENT_QUOTES, 'UTF-8');

      // Sanitize data
      $data = $this->sanitizeArray([$data])[0];

      // Check if currency is USD
      $isUSD = strtoupper($data['currency']) === 'USD';

      // Create Excel data array
      $excelData = [];
      
      $headers = [
        'FICHE REFERENCE', 'LICENSE NUMBER', 'MCA REF', 'SUBSCRIBER', 'GOODS TYPE', 
        'REGIME', 'FICHE DATE', 'FOB', 'FOB CURRENCY', 'INSURANCE', 'INSURANCE CURRENCY',
        'TRANSPORT MODE', 'FRET', 'FRET CURRENCY', 'AUTRES CHARGES', 'AUTRES CHARGES CURRENCY',
        'CIF', 'CIF CURRENCY', 'USD TO CURRENCY RATE', 'EXCHANGE RATE', 'WEIGHT', 
        'PROVENCE', 'INCOTERM SHORT', 'INCOTERM FULL', 'STATUS'
      ];
      
      $excelData[] = $headers;

      $values = [
        $data['fiche_reference'] ?? '',
        $data['license_number'] ?? '',
        $data['mca_ref'] ?? '',
        $data['subscriber'] ?? '',
        $data['type_of_goods'] ?? '',
        $data['regime'] ?? '',
        $data['fiche_date'] ?? '',
        number_format($data['fob'] ?? 0, 2),
        $data['fob_currency'] ?? '',
        number_format($data['insurance_amount'] ?? 0, 2),
        $data['insurance_amount_currency'] ?? '',
        $data['transport_mode'] ?? '',
        number_format($data['fret'] ?? 0, 2),
        $data['fret_currency'] ?? '',
        number_format($data['autres_charges'] ?? 0, 2),
        $data['autres_charges_currency'] ?? '',
        number_format($data['cif'] ?? 0, 2),
        $data['currency'] ?? '',
        number_format($data['usd_to_currency_rate'] ?? 1, 2),
        number_format($data['tx_de_change'] ?? 0, 4),
        number_format($data['poids'] ?? 0, 2),
        $data['provence'] ?? '',
        $data['incoterm_short'] ?? '',
        $data['incoterm_full'] ?? '',
        ucfirst($data['status'] ?? '')
      ];
      
      $excelData[] = $values;

      echo json_encode([
        'success' => true,
        'filename' => $filename,
        'data' => $excelData
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to export fiche', [
        'fiche_id' => $ficheId ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to export data: ' . $e->getMessage()]);
    }
  }

  /**
   * Get MCA references for license
   */
  private function getMCAReferences()
  {
    try {
      $licenseNumber = $this->sanitizeInput($_GET['license_number'] ?? '');
      $editFicheId = (int)($_GET['edit_fiche_id'] ?? 0);

      if (empty($licenseNumber)) {
        echo json_encode(['success' => false, 'message' => 'Invalid license number']);
        return;
      }

      // Get available MCA refs (not already used, except current one being edited)
      if ($editFicheId > 0) {
        $sql = "SELECT t.id, t.mca_ref, t.regime, t.fob, t.insurance_amount, t.currency, t.fret, t.other_charges,
                       t.fob_currency, t.fret_currency, t.other_charges_currency, t.insurance_amount_currency,
                       t.crf_reference, t.invoice, t.weight, t.transport_mode
                FROM tracking t
                LEFT JOIN fiche_de_calculs f ON t.mca_ref = f.mca_ref AND t.license_number = f.license_number AND f.id != :edit_fiche_id
                WHERE t.license_number = :license_number 
                AND t.status = 'active'
                AND f.mca_ref IS NULL
                ORDER BY t.created_at DESC";
        
        $params = [':license_number' => $licenseNumber, ':edit_fiche_id' => $editFicheId];
      } else {
        $sql = "SELECT t.id, t.mca_ref, t.regime, t.fob, t.insurance_amount, t.currency, t.fret, t.other_charges,
                       t.fob_currency, t.fret_currency, t.other_charges_currency, t.insurance_amount_currency,
                       t.crf_reference, t.invoice, t.weight, t.transport_mode
                FROM tracking t
                LEFT JOIN fiche_de_calculs f ON t.mca_ref = f.mca_ref AND t.license_number = f.license_number
                WHERE t.license_number = :license_number 
                AND t.status = 'active'
                AND f.mca_ref IS NULL
                ORDER BY t.created_at DESC";
        
        $params = [':license_number' => $licenseNumber];
      }

      $mcaRefs = $this->db->customQuery($sql, $params);
      $mcaRefs = $this->sanitizeArray($mcaRefs);

      echo json_encode([
        'success' => true,
        'data' => $mcaRefs ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get MCA references', [
        'license_number' => $licenseNumber ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load MCA references: ' . $e->getMessage()]);
    }
  }

  /**
   * Get MCA details from tracking
   */
  private function getMCADetails()
  {
    try {
      $licenseNumber = $this->sanitizeInput($_GET['license_number'] ?? '');
      $mcaRef = $this->sanitizeInput($_GET['mca_ref'] ?? '');

      if (empty($licenseNumber) || empty($mcaRef)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
      }

      $sql = "SELECT t.*, 
              COALESCE(t.currency, 'USD') as currency
              FROM tracking t
              WHERE t.license_number = :license_number 
              AND t.mca_ref = :mca_ref 
              AND t.status = 'active'
              LIMIT 1";

      $mca = $this->db->customQuery($sql, [
        ':license_number' => $licenseNumber,
        ':mca_ref' => $mcaRef
      ]);

      if (empty($mca)) {
        echo json_encode(['success' => false, 'message' => 'MCA reference not found']);
        return;
      }

      $mcaData = $mca[0];
      
      // Sanitize data
      $mcaData = $this->sanitizeArray([$mcaData])[0];
      
      // Generate Fiche Reference
      $mcaData['fiche_reference'] = $this->generateFicheReferenceFromMCA($mcaRef);
      
      // Check if currency is USD
      $mcaData['is_usd'] = strtoupper($mcaData['currency'] ?? 'USD') === 'USD';
      
      // Calculate coefficient
      $fob = (float)($mcaData['fob'] ?? 0);
      $fret = (float)($mcaData['fret'] ?? 0);
      $insurance = (float)($mcaData['insurance_amount'] ?? 0);
      $otherCharges = (float)($mcaData['other_charges'] ?? 0);
      
      if ($fob > 0) {
        $cif = $fob + $fret + $insurance + $otherCharges;
        $coefficient = $cif / $fob;
        $mcaData['coefficient'] = round($coefficient, 2);
      } else {
        $mcaData['coefficient'] = 1.00;
      }

      echo json_encode([
        'success' => true,
        'data' => $mcaData
      ]);

    } catch (Exception $e) {
      $this->logError('Failed to get MCA details', [
        'license_number' => $licenseNumber ?? 'unknown',
        'mca_ref' => $mcaRef ?? 'unknown',
        'error' => $e->getMessage()
      ]);
      echo json_encode(['success' => false, 'message' => 'Failed to load MCA details: ' . $e->getMessage()]);
    }
  }

  /**
   * Generate Fiche Reference from MCA Reference
   */
  private function generateFicheReferenceFromMCA($mcaRef)
  {
    if (empty($mcaRef)) {
      $datePart = date('Ymd');
      $randomPart = mt_rand(1000, 9999);
      return "FICHE-{$datePart}-{$randomPart}";
    }
    
    return "FICHE-{$mcaRef}";
  }

  /**
   * Insert new fiche
   */
  private function insertFiche()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $conn = $this->db->getConnection();
      $conn->beginTransaction();

      // Validate required fields
      $validation = $this->validateFicheData($_POST);
      if (!$validation['success']) {
        echo json_encode($validation);
        return;
      }

      // Check if MCA Ref is already used
      $mcaRef = $this->sanitizeInput($_POST['mca_ref']);
      $licenseNumber = $this->sanitizeInput($_POST['license_number']);
      
      $checkSql = "SELECT COUNT(*) as count FROM fiche_de_calculs 
                   WHERE mca_ref = :mca_ref AND license_number = :license_number";
      $checkResult = $this->db->customQuery($checkSql, [
        ':mca_ref' => $mcaRef,
        ':license_number' => $licenseNumber
      ]);
      
      if ($checkResult[0]['count'] > 0) {
        throw new Exception("This MCA Reference has already been used for creating a fiche");
      }

      // Prepare fiche data
      $ficheData = $this->prepareFicheData($_POST);
      $ficheData['created_by'] = (int)($_SESSION['user_id'] ?? 1);
      $ficheData['updated_by'] = (int)($_SESSION['user_id'] ?? 1);
      $ficheData['status'] = 'created';

      // Insert fiche
      $insertSql = "INSERT INTO fiche_de_calculs (
        license_number, tracking_id, mca_ref, regime, fiche_reference, fiche_date,
        fob, fob_currency, insurance_amount, insurance_amount_currency, transport_mode,
        fret, autres_charges, autres_charges_currency, cif, coefficient, fret_currency,
        currency, tx_de_change, usd_to_currency_rate, poids, provence, 
        incoterm_short, incoterm_full, status, created_by
      ) VALUES (
        :license_number, :tracking_id, :mca_ref, :regime, :fiche_reference, :fiche_date,
        :fob, :fob_currency, :insurance_amount, :insurance_amount_currency, :transport_mode,
        :fret, :autres_charges, :autres_charges_currency, :cif, :coefficient, :fret_currency,
        :currency, :tx_de_change, :usd_to_currency_rate, :poids, :provence,
        :incoterm_short, :incoterm_full, :status, :created_by
      )";

      $stmt = $conn->prepare($insertSql);
      $stmt->execute($ficheData);
      $ficheId = $conn->lastInsertId();

      // Insert items
      if (isset($_POST['items']) && !empty($_POST['items'])) {
        $itemsData = json_decode($_POST['items'], true);
        if (is_array($itemsData)) {
          $this->insertFicheItems($conn, $ficheId, $itemsData, (float)($ficheData['tx_de_change'] ?? 1));
        }
      }

      $conn->commit();

      $this->logInfo('Fiche created successfully', ['fiche_id' => $ficheId]);
      echo json_encode([
        'success' => true,
        'message' => 'Fiche created successfully!',
        'data' => ['id' => $ficheId]
      ]);

    } catch (Exception $e) {
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }
      $this->logError('Exception during fiche insert', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  /**
   * Update existing fiche
   */
  private function updateFiche()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $conn = $this->db->getConnection();
      $conn->beginTransaction();

      $ficheId = (int)($_POST['id'] ?? 0);
      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      // Check if fiche can be edited
      $sql = "SELECT status FROM fiche_de_calculs WHERE id = :id";
      $result = $this->db->customQuery($sql, [':id' => $ficheId]);
      
      if (empty($result)) {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
        return;
      }

      $currentStatus = $result[0]['status'];
      
      if (!in_array($currentStatus, ['created', 'verified'])) {
        throw new Exception("This fiche cannot be edited as it has been audited");
      }

      // Validate required fields
      $validation = $this->validateFicheData($_POST, $ficheId);
      if (!$validation['success']) {
        echo json_encode($validation);
        return;
      }

      // Prepare fiche data
      $ficheData = $this->prepareFicheData($_POST);
      $ficheData['updated_by'] = (int)($_SESSION['user_id'] ?? 1);

      // Update fiche
      $updateSql = "UPDATE fiche_de_calculs SET
        license_number = :license_number, tracking_id = :tracking_id, mca_ref = :mca_ref,
        regime = :regime, fiche_reference = :fiche_reference, fiche_date = :fiche_date,
        fob = :fob, fob_currency = :fob_currency, insurance_amount = :insurance_amount,
        insurance_amount_currency = :insurance_amount_currency, transport_mode = :transport_mode,
        fret = :fret, autres_charges = :autres_charges, autres_charges_currency = :autres_charges_currency,
        cif = :cif, coefficient = :coefficient, fret_currency = :fret_currency,
        currency = :currency, tx_de_change = :tx_de_change, usd_to_currency_rate = :usd_to_currency_rate,
        poids = :poids, provence = :provence, incoterm_short = :incoterm_short,
        incoterm_full = :incoterm_full, updated_by = :updated_by, updated_at = CURRENT_TIMESTAMP
        WHERE id = :id";

      $ficheData['id'] = $ficheId;
      $stmt = $conn->prepare($updateSql);
      $stmt->execute($ficheData);

      // Delete existing items
      $deleteSql = "DELETE FROM fiche_items WHERE fiche_id = :fiche_id";
      $conn->prepare($deleteSql)->execute([':fiche_id' => $ficheId]);

      // Insert updated items
      if (isset($_POST['items']) && !empty($_POST['items'])) {
        $itemsData = json_decode($_POST['items'], true);
        if (is_array($itemsData)) {
          $this->insertFicheItems($conn, $ficheId, $itemsData, (float)($ficheData['tx_de_change'] ?? 1));
        }
      }

      $conn->commit();

      $this->logInfo('Fiche updated successfully', ['fiche_id' => $ficheId]);
      echo json_encode(['success' => true, 'message' => 'Fiche updated successfully!']);

    } catch (Exception $e) {
      if ($conn->inTransaction()) {
        $conn->rollBack();
      }
      $this->logError('Exception during fiche update', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  /**
   * Insert fiche items
   */
  private function insertFicheItems($conn, $ficheId, $itemsData, $txDeChange)
  {
    $itemSql = "INSERT INTO fiche_items (
      fiche_id, description, no_bivac, no_facture, numero, position_tarrif, 
      ddi_percent, av, org, prov, code_add, colis, qte, net, brut, 
      fob_article, coef, cif_article, ddi
    ) VALUES (
      :fiche_id, :description, :no_bivac, :no_facture, :numero, :position_tarrif,
      :ddi_percent, :av, :org, :prov, :code_add, :colis, :qte, :net, :brut,
      :fob_article, :coef, :cif_article, :ddi
    )";
    
    $itemStmt = $conn->prepare($itemSql);
    
    foreach ($itemsData as $item) {
      // Calculate DDI: (CIF PAR ARTICLE * Exchange Rate * DDI %) / 100
      $cif_article = (float)($item['cif_article'] ?? 0);
      $ddi_percent = (float)($item['ddi_percent'] ?? 0);
      $calculated_ddi = ($cif_article * $txDeChange * $ddi_percent) / 100;
      $ddi_value = floor($calculated_ddi);
      
      $itemParams = [
        ':fiche_id' => $ficheId,
        ':description' => $this->sanitizeInput($item['description'] ?? ''),
        ':no_bivac' => $this->sanitizeInput($item['no_bivac'] ?? ''),
        ':no_facture' => $this->sanitizeInput($item['no_facture'] ?? ''),
        ':numero' => (int)($item['numero'] ?? 0),
        ':position_tarrif' => $this->sanitizeInput($item['position_tarrif'] ?? ''),
        ':ddi_percent' => (float)($item['ddi_percent'] ?? 0),
        ':av' => $this->sanitizeInput($item['av'] ?? ''),
        ':org' => $this->sanitizeInput($item['org'] ?? ''),
        ':prov' => $this->sanitizeInput($item['prov'] ?? ''),
        ':code_add' => $this->sanitizeInput($item['code_add'] ?? ''),
        ':colis' => (float)($item['colis'] ?? 0),
        ':qte' => (float)($item['qte'] ?? 0),
        ':net' => (float)($item['net'] ?? 0),
        ':brut' => (float)($item['brut'] ?? 0),
        ':fob_article' => (float)($item['fob_article'] ?? 0),
        ':coef' => (float)($item['coef'] ?? 1),
        ':cif_article' => (float)($item['cif_article'] ?? 0),
        ':ddi' => $ddi_value
      ];
      
      $itemStmt->execute($itemParams);
    }
  }

  /**
   * Delete fiche
   */
  private function deleteFiche()
  {
    $this->validateCsrfToken();

    try {
      $ficheId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      // Soft delete
      $sql = "UPDATE fiche_de_calculs 
              SET display = 'N', updated_by = :updated_by, updated_at = CURRENT_TIMESTAMP 
              WHERE id = :id";

      $this->db->customQuery($sql, [
        ':id' => $ficheId,
        ':updated_by' => $_SESSION['user_id'] ?? 1
      ]);

      $this->logInfo('Fiche deleted successfully', ['fiche_id' => $ficheId]);
      echo json_encode(['success' => true, 'message' => 'Fiche deleted successfully!']);

    } catch (Exception $e) {
      $this->logError('Exception during fiche delete', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to delete fiche']);
    }
  }

  /**
   * Get single fiche for editing
   */
  private function getFiche()
  {
    try {
      $ficheId = (int)($_GET['id'] ?? 0);

      if ($ficheId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid fiche ID']);
        return;
      }

      $sql = "SELECT f.*, t.subscriber 
              FROM fiche_de_calculs f
              LEFT JOIN tracking t ON f.tracking_id = t.id
              WHERE f.id = :id";

      $fiche = $this->db->customQuery($sql, [':id' => $ficheId]);

      if (!empty($fiche)) {
        $fiche = $this->sanitizeArray($fiche);
        $fiche[0]['is_usd'] = strtoupper($fiche[0]['currency']) === 'USD';
        
        echo json_encode(['success' => true, 'data' => $fiche[0]]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Fiche not found']);
      }
    } catch (Exception $e) {
      $this->logError('Exception while fetching fiche', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load fiche data']);
    }
  }

  /**
   * List all fiches for DataTable
   */
  private function listFiches()
  {
    try {
      $draw = (int)($_GET['draw'] ?? 1);
      $start = (int)($_GET['start'] ?? 0);
      $length = (int)($_GET['length'] ?? 25);
      $searchValue = $this->sanitizeInput(trim($_GET['search']['value'] ?? ''));
      
      $filters = $_GET['filters'] ?? [];
      if (!is_array($filters)) {
        $filters = [];
      }
      $filters = array_filter($filters, function($filter) {
        return in_array($filter, $this->allowedFilters);
      });
      
      $orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0);
      $orderDirection = strtoupper($_GET['order'][0]['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
      
      $columns = ['f.id', 'f.fiche_reference', 't.subscriber', 'f.mca_ref', 'f.fiche_date', 'f.poids', 'f.cif', 'f.status'];
      $orderColumn = $columns[$orderColumnIndex] ?? 'f.id';

      $baseQuery = "FROM fiche_de_calculs f
                    LEFT JOIN tracking t ON f.tracking_id = t.id
                    WHERE f.display = 'Y'";

      $searchCondition = "";
      $filterCondition = "";
      $params = [];
      
      if (!empty($searchValue)) {
        $searchCondition = " AND (
          f.fiche_reference LIKE :search OR
          t.subscriber LIKE :search OR
          f.mca_ref LIKE :search OR
          f.status LIKE :search
        )";
        $params[':search'] = "%{$searchValue}%";
      }

      if (!empty($filters)) {
        $filterClauses = [];
        foreach ($filters as $filter) {
          $filterClauses[] = "f.status = :status_" . $filter;
          $params[':status_' . $filter] = $filter;
        }
        if (!empty($filterClauses)) {
          $filterCondition = " AND (" . implode(' OR ', $filterClauses) . ")";
        }
      }

      $totalSql = "SELECT COUNT(*) as total FROM fiche_de_calculs WHERE display = 'Y'";
      $totalResult = $this->db->customQuery($totalSql);
      $totalRecords = (int)($totalResult[0]['total'] ?? 0);

      $filteredSql = "SELECT COUNT(*) as total {$baseQuery} {$searchCondition} {$filterCondition}";
      $filteredResult = $this->db->customQuery($filteredSql, $params);
      $filteredRecords = (int)($filteredResult[0]['total'] ?? 0);

      $dataSql = "SELECT 
                    f.id, f.fiche_reference, f.fiche_date, f.poids, f.cif, f.status,
                    t.subscriber, f.license_number, f.mca_ref, f.currency
                  {$baseQuery}
                  {$searchCondition}
                  {$filterCondition}
                  ORDER BY {$orderColumn} {$orderDirection}
                  LIMIT :limit OFFSET :offset";

      $stmt = $this->db->getConnection()->prepare($dataSql);
      
      foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
      }
      $stmt->bindValue(':limit', $length, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $start, PDO::PARAM_INT);
      
      $stmt->execute();
      $fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $fiches = $this->sanitizeArray($fiches);

      echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $fiches ?: []
      ]);

    } catch (Exception $e) {
      $this->logError('Exception in listFiches', ['error' => $e->getMessage()]);
      echo json_encode([
        'draw' => $_GET['draw'] ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
      ]);
    }
  }

  /**
   * Get statistics
   */
  private function getStatistics()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_fiches,
                COALESCE(SUM(poids), 0) as total_weight,
                COALESCE(SUM(cif), 0) as total_cif
              FROM fiche_de_calculs
              WHERE display = 'Y'";

      $stats = $this->db->customQuery($sql);

      $statusSql = "SELECT f.status, COUNT(f.id) as count
                    FROM fiche_de_calculs f
                    WHERE f.display = 'Y'
                    GROUP BY f.status";

      $statusCounts = $this->db->customQuery($statusSql);

      $statusData = [];
      foreach ($statusCounts as $status) {
        $statusKey = strtolower(str_replace(' ', '_', $status['status'] ?? 'unknown'));
        $statusData[$statusKey] = (int)$status['count'];
      }

      if (!empty($stats)) {
        echo json_encode([
          'success' => true,
          'data' => [
            'total_fiches' => (int)$stats[0]['total_fiches'],
            'total_weight' => number_format((float)$stats[0]['total_weight'], 2, '.', ''),
            'total_cif' => number_format((float)$stats[0]['total_cif'], 2, '.', ''),
            'created' => $statusData['created'] ?? 0,
            'verified' => $statusData['verified'] ?? 0,
            'audited' => $statusData['audited'] ?? 0
          ]
        ]);
      }
    } catch (Exception $e) {
      $this->logError('Failed to get statistics', ['error' => $e->getMessage()]);
      echo json_encode(['success' => false, 'message' => 'Failed to load statistics']);
    }
  }

  /**
   * Validate fiche data
   */
  private function validateFicheData($post, $ficheId = null)
  {
    $errors = [];

    $requiredFields = [
      'license_number' => 'License Number',
      'mca_ref' => 'MCA Reference',
      'regime' => 'Regime',
      'fiche_date' => 'Fiche Date',
      'poids' => 'Weight',
      'fob' => 'FOB',
      'fret' => 'Fret',
      'tx_de_change' => 'Exchange Rate'
    ];

    foreach ($requiredFields as $field => $label) {
      if (empty($post[$field])) {
        $errors[] = htmlspecialchars("{$label} is required", ENT_QUOTES, 'UTF-8');
      }
    }

    if (empty($post['fiche_reference'])) {
      $errors[] = 'Fiche Reference is required';
    } else {
      $ficheRef = $this->sanitizeInput(trim($post['fiche_reference']));
      
      $sql = "SELECT id FROM fiche_de_calculs WHERE fiche_reference = :fiche_reference";
      $params = [':fiche_reference' => $ficheRef];
      
      if ($ficheId) {
        $sql .= " AND id != :fiche_id";
        $params[':fiche_id'] = $ficheId;
      }
      
      $exists = $this->db->customQuery($sql, $params);
      if ($exists) {
        $errors[] = 'Fiche Reference already exists';
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

  /**
   * Prepare fiche data for database
   */
  private function prepareFicheData($post)
  {
    $currency = $this->sanitizeInput($post['currency'] ?? 'USD');
    $usdToCurrencyRate = (float)($post['usd_to_currency_rate'] ?? 1);
    $fob = (float)($post['fob'] ?? 0);
    $insurance = (float)($post['insurance_amount'] ?? 0);
    $fret = (float)($post['fret'] ?? 0);
    $autresCharges = (float)($post['autres_charges'] ?? 0);
    
    // Calculate CIF based on currency
    if (strtoupper($currency) === 'USD') {
      $cif = $fob + $fret + $autresCharges + $insurance;
    } else {
      $insuranceConverted = $insurance * $usdToCurrencyRate;
      $fretConverted = $fret * $usdToCurrencyRate;
      $autresChargesConverted = $autresCharges * $usdToCurrencyRate;
      $cif = $fob + $insuranceConverted + $fretConverted + $autresChargesConverted;
    }
    
    // Calculate coefficient
    $coefficient = ($fob > 0) ? ($cif / $fob) : 1.0;

    $data = [
      ':license_number' => $this->sanitizeInput($post['license_number'] ?? ''),
      ':tracking_id' => !empty($post['tracking_id']) ? (int)$post['tracking_id'] : null,
      ':mca_ref' => $this->sanitizeInput($post['mca_ref'] ?? ''),
      ':regime' => $this->sanitizeInput($post['regime'] ?? ''),
      ':fiche_reference' => $this->sanitizeInput($post['fiche_reference'] ?? ''),
      ':fiche_date' => $this->convertDateToMysqlFormat($post['fiche_date'] ?? ''),
      ':fob' => $fob,
      ':fob_currency' => $this->sanitizeInput($post['fob_currency'] ?? $currency),
      ':insurance_amount' => $insurance,
      ':insurance_amount_currency' => $this->sanitizeInput($post['insurance_amount_currency'] ?? $currency),
      ':transport_mode' => $this->sanitizeInput($post['transport_mode'] ?? ''),
      ':fret' => $fret,
      ':autres_charges' => $autresCharges,
      ':autres_charges_currency' => $this->sanitizeInput($post['autres_charges_currency'] ?? $currency),
      ':cif' => $cif,
      ':coefficient' => $coefficient,
      ':fret_currency' => $this->sanitizeInput($post['fret_currency'] ?? $currency),
      ':currency' => $currency,
      ':tx_de_change' => (float)($post['tx_de_change'] ?? 0),
      ':usd_to_currency_rate' => $usdToCurrencyRate,
      ':poids' => (float)($post['poids'] ?? 0),
      ':provence' => $this->sanitizeInput($post['provence'] ?? ''),
      ':incoterm_short' => $this->sanitizeInput($post['incoterm_short'] ?? ''),
      ':incoterm_full' => $this->sanitizeInput($post['incoterm_full'] ?? ''),
      ':status' => $this->sanitizeInput($post['status'] ?? 'created'),
      ':created_by' => (int)($_SESSION['user_id'] ?? 1),
      ':updated_by' => (int)($_SESSION['user_id'] ?? 1)
    ];

    return $data;
  }

  /**
   * Convert date to MySQL format
   */
  private function convertDateToMysqlFormat($dateString)
  {
    if (empty($dateString)) {
      return null;
    }

    $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y-m-d H:i:s', 'd-m-Y H:i:s'];

    foreach ($formats as $format) {
      $date = DateTime::createFromFormat($format, $dateString);
      if ($date && $date->format($format) === $dateString) {
        return $date->format('Y-m-d');
      }
    }

    try {
      $date = new DateTime($dateString);
      return $date->format('Y-m-d');
    } catch (Exception $e) {
      $this->logError("Failed to parse date: " . $dateString, ['error' => $e->getMessage()]);
      return null;
    }
  }

  /**
   * Sanitize array of data
   */
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

  /**
   * Sanitize input
   */
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

  /**
   * Validate CSRF Token
   */
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

  /**
   * Log errors
   */
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

  /**
   * Log info
   */
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