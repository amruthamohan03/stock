<?php
defined('PUBLIC_PATH') or define(
    'PUBLIC_PATH',
    realpath(__DIR__ . '/../../../public') . DIRECTORY_SEPARATOR
);
require_once __DIR__ . '/EmcfController.php';

class ExportinvoiceController extends EmcfController
{
  private $db;
  private $logFile;
  private $logoPath;
  private $signaturePath;
  private $defaultBccRate = 2500.00;

  public function __construct()
  {
    parent::__construct(); 
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/export_invoice.log';
    $this->logoPath = __DIR__ . '/../../../public/images/logo.jpg';

    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      @mkdir($logDir, 0755, true);
    }
  }

  public function index()
  {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['csrf_token_time'] = time();
    }

    $sql = "SELECT DISTINCT c.id, c.short_name, c.company_name 
            FROM clients_t c
            INNER JOIN licenses_t l ON c.id = l.client_id
            WHERE c.display = 'Y' AND l.display = 'Y' AND l.kind_id IN (3, 4, 7, 8)
            ORDER BY c.short_name ASC";
    $clients = $this->db->customQuery($sql) ?: [];

    $currencies = $this->db->selectData('currency_master_t', 'id, currency_name, currency_short_name', ['display' => 'Y'], 'currency_short_name ASC') ?: [];

    $exchangeBanks = $this->db->selectData('banklist_master_t', 'id, bank_name, bank_code', ['display' => 'Y', 'for_exchange' => 'Y'], 'bank_name ASC') ?: [];

    $transportModes = $this->db->selectData('transport_mode_master_t', 'id, transport_mode_name', ['display' => 'Y'], 'transport_mode_name ASC') ?: [];

    $data = [
      'title' => 'Export Invoice Management',
      'clients' => $this->sanitizeArray($clients),
      'currencies' => $this->sanitizeArray($currencies),
      'exchangeBanks' => $this->sanitizeArray($exchangeBanks),
      'transportModes' => $this->sanitizeArray($transportModes),
      'csrf_token' => $_SESSION['csrf_token']
    ];

    $this->viewWithLayout('invoices/exportinvoice', $data);
  }

  public function crudData($action = 'listing')
  {
    while (ob_get_level()) {
      ob_end_clean();
    }
    ob_start();

    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');

    try {
      switch ($action) {
        case 'insert':
        case 'insertion':
          $this->insertInvoice();
          break;
        case 'update':
          $this->updateInvoice();
          break;
        case 'getInvoice':
          $this->getInvoice();
          break;
        case 'listing':
          $this->listInvoices();
          break;
        case 'statistics':
          $this->getStatistics();
          break;
        case 'getLicenses':
          $this->getLicenses();
          break;
        case 'getMCAReferences':
          $this->getMCAReferences();
          break;
        case 'getMCADetails':
          $this->getMCADetails();
          break;
        case 'getBanks':
          $this->getBanks();
          break;
        case 'getNextInvoiceRefForClient':
          $this->getNextInvoiceRefForClient();
          break;
        case 'getClientDetails':
          $this->getClientDetails();
          break;
        case 'getAllQuotationsForClient':
          $this->getAllQuotationsForClient();
          break;
        case 'getQuotationItems':
          $this->getQuotationItems();
          break;
        case 'validateInvoice':
          $this->validateInvoice();
          break;
        case 'markDGI':
          $this->markDGI();
          break;
        case 'exportInvoice':
          $this->exportInvoice();
          break;
        case 'exportAllDebitNotes':
          $this->exportAllDebitNotes();
          break;
        case 'exportAllInvoices':
          $this->exportAllInvoices();
          break;
        case 'viewPDF':
        case 'viewFullPDF':
          $this->viewPDF();
          break;
        case 'viewPDFPage1':
          $this->viewPDFPage1();
          break;
        case 'viewPDFPages2to4':
          $this->viewPDFPages2to4();
          break;
        case 'finalizeEMCF':
          $this->finalizeEMCF();
          break;
        case 'getPendingInvoicing':
          $this->getPendingInvoicing();
          break;
        case 'exportPendingInvoicing':
          $this->exportPendingInvoicing();
          break;
        default:
          echo json_encode(['success' => false, 'message' => 'Invalid action']);
      }
    } catch (Exception $e) {
      $this->logError("Exception in crudData: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }

    ob_end_flush();
    exit;
  }

  // ========== INSERT INVOICE ==========
  private function insertInvoice()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $this->db->beginTransaction();
      $this->logError("=== STARTING NEW INVOICE INSERTION ===");

      $validation = $this->validateInvoiceData($_POST);
      if (!$validation['success']) {
        $this->db->rollback();
        echo json_encode($validation);
        return;
      }

      $userId = (int)($_SESSION['user_id'] ?? 1);
      
      // Parse MCA data
      $mcaDataArray = [];
      $totalWeight = 0;
      $totalDuty = 0;
      $totalFOB = 0;
      
      if (!empty($_POST['mca_data'])) {
        $mcaDataArray = json_decode($_POST['mca_data'], true);
        $this->logError("MCA Data JSON decoded, count: " . (is_array($mcaDataArray) ? count($mcaDataArray) : 0));
        
        if (json_last_error() !== JSON_ERROR_NONE) {
          $this->db->rollback();
          $this->logError("JSON decode error for MCA data: " . json_last_error_msg());
          echo json_encode(['success' => false, 'message' => 'Invalid MCA data format']);
          return;
        }
        
        if (!empty($mcaDataArray) && is_array($mcaDataArray)) {
          foreach ($mcaDataArray as $mca) {
            $weight = (float)($mca['weight'] ?? 0);
            $totalWeight += $weight;
            
            $totalDuty += (float)($mca['ceec_amount'] ?? 0);
            $totalDuty += (float)($mca['cgea_amount'] ?? 0);
            $totalDuty += (float)($mca['occ_amount'] ?? 0);
            $totalDuty += (float)($mca['lmc_amount'] ?? 0);
            $totalDuty += (float)($mca['ogefrem_amount'] ?? 0);
            
            $totalFOB += $weight * 8500;
          }
        }
      } else {
        $this->logError("WARNING: No MCA data received in POST");
      }
      
      if (empty($mcaDataArray)) {
        $this->db->rollback();
        $this->logError("ERROR: MCA data array is empty");
        echo json_encode(['success' => false, 'message' => 'At least one MCA Reference is required']);
        return;
      }
      
      $this->logError("Total Weight: $totalWeight, Total Duty: $totalDuty, Total FOB: $totalFOB");
      
      // Get quotation data
      $quotationId = $this->toInt($_POST['quotation_id'] ?? null);
      $quotationSubTotal = $quotationId ? $this->toDecimal($_POST['quotation_sub_total'] ?? 0) : null;
      $quotationVatAmount = $quotationId ? $this->toDecimal($_POST['quotation_vat_amount'] ?? 0) : null;
      $quotationTotalAmount = $quotationId ? $this->toDecimal($_POST['quotation_total_amount'] ?? 0) : null;
      
      $this->logError("Quotation ID: " . ($quotationId ?? 'NULL') . ", Subtotal: " . ($quotationSubTotal ?? 'NULL'));
      
      // Insert main invoice record
      $sql = "INSERT INTO export_invoices_t (
                client_id, license_id,
                kind_id, goods_type_id, transport_mode_id,
                invoice_ref, invoice_date,
                fob_usd, total_weight, total_duty_cdf,
                quotation_id, quotation_sub_total, quotation_vat_amount, quotation_total_amount,
                arsp, validated,
                created_by, updated_by, created_at, updated_at
              ) VALUES (
                ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, 0,
                ?, ?, NOW(), NOW()
              )";

      $params = [
        $this->toInt($_POST['client_id']),
        $this->toInt($_POST['license_id']),
        $this->toInt($_POST['kind_id'] ?? null),
        $this->toInt($_POST['goods_type_id'] ?? null),
        $this->toInt($_POST['transport_mode_id'] ?? null),
        $this->clean($_POST['invoice_ref']),
        $this->toDate($_POST['invoice_date'] ?? date('Y-m-d')),
        $this->toDecimal($totalFOB),
        $totalWeight,
        $totalDuty,
        $quotationId,
        $quotationSubTotal,
        $quotationVatAmount,
        $quotationTotalAmount,
        $this->clean($_POST['arsp'] ?? 'Disabled'),
        $userId,
        $userId
      ];

      $this->logError("Executing INSERT query for export_invoices_t");
      $this->db->customQuery($sql, $params);
      
      // Get the inserted ID
      $lastIdResult = $this->db->customQuery("SELECT LAST_INSERT_ID() as id");
      $insertId = (int)($lastIdResult[0]['id'] ?? 0);
      
      if ($insertId === 0) {
        $this->logError("LAST_INSERT_ID returned 0, trying alternate method");
        $findSql = "SELECT id FROM export_invoices_t WHERE invoice_ref = ? ORDER BY id DESC LIMIT 1";
        $findResult = $this->db->customQuery($findSql, [$this->clean($_POST['invoice_ref'])]);
        if (!empty($findResult)) {
          $insertId = (int)($findResult[0]['id'] ?? 0);
        }
      }

      if ($insertId <= 0) {
        $this->db->rollback();
        $this->logError("ERROR: Could not get inserted invoice ID");
        echo json_encode(['success' => false, 'message' => 'Failed to create invoice record']);
        return;
      }

      $this->logError("=== INVOICE CREATED WITH ID: $insertId ===");
      
      // Save MCA details
      $this->logError("Starting MCA details save - Count: " . count($mcaDataArray));
      $this->saveMCADetails($insertId, $mcaDataArray);
      
      // Save invoice items
      $itemsJson = $_POST['quotation_items'] ?? '';
      $this->logError("Items JSON received - Length: " . strlen($itemsJson));
      
      $itemsSaved = true;
      
      if (!empty($itemsJson)) {
        $this->logError("Items JSON preview: " . substr($itemsJson, 0, 200) . '...');
        
        $itemsSaved = $this->saveInvoiceItems($insertId, $itemsJson);
        $this->logError("Items save result: " . ($itemsSaved ? 'SUCCESS' : 'FAILED'));
        
        if (!$itemsSaved) {
          $this->db->rollback();
          $this->logError("❌ TRANSACTION ROLLED BACK: Items save failed");
          echo json_encode([
            'success' => false, 
            'message' => 'Failed to save invoice items. Please check the error logs for details.'
          ]);
          return;
        }
      } else {
        $this->logError("⚠️ WARNING: No items JSON received from frontend");
      }

      // All saves successful, commit transaction
      $this->logError("All data saved successfully, committing transaction...");

      try {
        $commitResult = $this->db->commit();
        $this->logError("Commit result: " . ($commitResult ? 'TRUE' : 'FALSE'));
        
        // Verify after commit
        $verifyItemsAfterCommit = $this->db->customQuery(
          "SELECT COUNT(*) as count FROM export_invoice_items_t WHERE export_invoice_id = ?", 
          [$insertId]
        );
        $itemsCountAfter = (int)($verifyItemsAfterCommit[0]['count'] ?? 0);
        $this->logError("✅ Items count AFTER COMMIT: $itemsCountAfter");
        
        $verifyMCAAfterCommit = $this->db->customQuery(
          "SELECT COUNT(*) as count FROM export_invoice_mca_details_t WHERE export_invoice_id = ?", 
          [$insertId]
        );
        $mcaCountAfter = (int)($verifyMCAAfterCommit[0]['count'] ?? 0);
        $this->logError("✅ MCA count AFTER COMMIT: $mcaCountAfter");
        
        if ($mcaCountAfter === 0) {
          $this->logError("❌ CRITICAL: No MCA details found even after commit!");
          echo json_encode([
            'success' => false, 
            'message' => 'Invoice created but MCA details failed to save. Please contact support.'
          ]);
          return;
        }
        
        $this->logError("=== INVOICE INSERTION SUCCESSFUL ===");
        
        echo json_encode([
          'success' => true, 
          'message' => 'Export invoice created successfully with ' . $mcaCountAfter . ' MCA reference(s) and ' . $itemsCountAfter . ' item(s)!', 
          'id' => $insertId,
          'mca_count' => $mcaCountAfter,
          'items_count' => $itemsCountAfter
        ]);
        
      } catch (Exception $commitEx) {
        $this->logError("❌ COMMIT FAILED: " . $commitEx->getMessage());
        $this->logError("Stack trace: " . $commitEx->getTraceAsString());
        throw $commitEx;
      }

    } catch (Exception $e) {
      if ($this->db) {
        $this->db->rollback();
      }
      $this->logError("❌ INSERT EXCEPTION: " . $e->getMessage());
      $this->logError("Stack trace: " . $e->getTraceAsString());
      echo json_encode([
        'success' => false, 
        'message' => 'System error: ' . $e->getMessage()
      ]);
    }
  }

  // ========== GET PENDING INVOICING DATA ==========
  private function getPendingInvoicing()
  {
    try {
      // Query to get all MCA files that have quittance_date but are NOT yet invoiced
      $sql = "SELECT 
                e.id as mca_id,
                e.mca_ref,
                e.subscriber_id as client_id,
                c.short_name as client_name,
                c.company_name,
                l.license_number,
                e.lot_number,
                e.quittance_reference as quittance_no,
                e.quittance_date,
                e.weight,
                e.fob,
                'PENDING' as status
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id
              LEFT JOIN licenses_t l ON e.license_id = l.id
              WHERE e.display = 'Y'
                AND e.quittance_date IS NOT NULL
                AND e.quittance_date != ''
                AND e.id NOT IN (
                  SELECT DISTINCT mca_id 
                  FROM export_invoice_mca_details_t 
                  WHERE mca_id IS NOT NULL
                )
              ORDER BY c.short_name ASC, e.quittance_date DESC";

      $pendingMCAs = $this->db->customQuery($sql) ?: [];

      if (empty($pendingMCAs)) {
        echo json_encode([
          'success' => true,
          'message' => 'No pending MCA files found',
          'data' => []
        ]);
        return;
      }

      // Sanitize and format the data
      $sanitizedData = [];
      foreach ($pendingMCAs as $mca) {
        $sanitizedData[] = [
          'mca_id' => (int)$mca['mca_id'],
          'mca_ref' => htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8'),
          'client_id' => (int)($mca['client_id'] ?? 0),
          'client_name' => htmlspecialchars($mca['client_name'] ?? '', ENT_QUOTES, 'UTF-8'),
          'company_name' => htmlspecialchars($mca['company_name'] ?? '', ENT_QUOTES, 'UTF-8'),
          'license_number' => htmlspecialchars($mca['license_number'] ?? '', ENT_QUOTES, 'UTF-8'),
          'lot_number' => htmlspecialchars($mca['lot_number'] ?? '', ENT_QUOTES, 'UTF-8'),
          'quittance_no' => htmlspecialchars($mca['quittance_no'] ?? '', ENT_QUOTES, 'UTF-8'),
          'quittance_date' => !empty($mca['quittance_date']) ? date('Y-m-d', strtotime($mca['quittance_date'])) : '',
          'weight' => number_format((float)($mca['weight'] ?? 0), 3),
          'fob_usd' => number_format((float)($mca['fob'] ?? 0), 2),
          'status' => 'PENDING'
        ];
      }

      echo json_encode([
        'success' => true,
        'data' => $sanitizedData,
        'count' => count($sanitizedData)
      ]);

    } catch (Exception $e) {
      $this->logError("Error in getPendingInvoicing: " . $e->getMessage());
      echo json_encode([
        'success' => false,
        'message' => 'Failed to load pending invoicing data: ' . $e->getMessage(),
        'data' => []
      ]);
    }
  }

  // ========== EXPORT PENDING INVOICING TO EXCEL ==========
  private function exportPendingInvoicing()
  {
    while (ob_get_level()) {
      ob_end_clean();
    }

    try {
      // Get pending MCA data with all required fields from exports_t table
      $sql = "SELECT 
                e.id as mca_id,
                e.mca_ref,
                e.lot_number,
                e.po_ref,
                e.subscriber_id as client_id,
                c.short_name as client_name,
                c.company_name,
                tg.goods_type as commodity,
                e.horse,
                e.trailer_1,
                e.trailer_2,
                e.wagon_ref,
                e.declaration_reference as e_ref,
                e.dgda_in_date as e_date,
                e.liquidation_reference as l_ref,
                e.liquidation_date as l_date,
                e.liquidation_amount as l_amount,
                e.quittance_reference as q_ref,
                e.quittance_date as q_date,
                e.dgda_out_date as dispatch_delivery_date,
                e.clearing_status,
                e.site_of_loading_id
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id
              LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id AND tg.display = 'Y'
              WHERE e.display = 'Y'
                AND e.quittance_date IS NOT NULL
                AND e.quittance_date != ''
                AND e.id NOT IN (
                  SELECT DISTINCT mca_id 
                  FROM export_invoice_mca_details_t 
                  WHERE mca_id IS NOT NULL
                )
              ORDER BY c.short_name ASC, e.quittance_date DESC";

      $pendingMCAs = $this->db->customQuery($sql) ?: [];

      if (empty($pendingMCAs)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Aucun dossier non facturé trouvé']);
        exit;
      }

      // Load PhpSpreadsheet
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'PhpSpreadsheet non trouvé']);
        exit;
      }

      require_once $vendorPath;

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Dossiers Non Facturés');

      // Headers EXACTLY as in your Excel file
      $headers = [
        '#',                          // Column A
        'MCA File Ref.',             // Column B
        'Tally Ref.',                // Column C
        'Support Documents',         // Column D
        'Lot Num. / Inv.No.',       // Column E
        'PO.No.',                    // Column F
        'Client',                    // Column G
        'Commodity',                 // Column H
        'Truck / Wagon / AWB',       // Column I
        'E.Ref.',                    // Column J
        'E.Date',                    // Column K
        'L.Ref.',                    // Column L
        'L.Date',                    // Column M
        'L.Amount',                  // Column N
        'Q.Ref.',                    // Column O
        'Q.Date',                    // Column P
        'Delay',                     // Column Q
        'Q.Encoding Date',           // Column R
        'BS Date',                   // Column S
        'Dispatch/Delivery Date',    // Column T
        'Clearing Status',           // Column U
        'General Status',            // Column V
        'Site'                       // Column W
      ];

      $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
      ];

      // Write headers
      $col = 'A';
      foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        $col++;
      }

      // Set column widths
      $sheet->getColumnDimension('A')->setWidth(6);   // #
      $sheet->getColumnDimension('B')->setWidth(18);  // MCA File Ref
      $sheet->getColumnDimension('C')->setWidth(15);  // Tally Ref
      $sheet->getColumnDimension('D')->setWidth(18);  // Support Documents
      $sheet->getColumnDimension('E')->setWidth(18);  // Lot Num / Inv.No
      $sheet->getColumnDimension('F')->setWidth(15);  // PO.No
      $sheet->getColumnDimension('G')->setWidth(20);  // Client
      $sheet->getColumnDimension('H')->setWidth(15);  // Commodity
      $sheet->getColumnDimension('I')->setWidth(20);  // Truck / Wagon / AWB
      $sheet->getColumnDimension('J')->setWidth(18);  // E.Ref
      $sheet->getColumnDimension('K')->setWidth(12);  // E.Date
      $sheet->getColumnDimension('L')->setWidth(18);  // L.Ref
      $sheet->getColumnDimension('M')->setWidth(12);  // L.Date
      $sheet->getColumnDimension('N')->setWidth(16);  // L.Amount
      $sheet->getColumnDimension('O')->setWidth(18);  // Q.Ref
      $sheet->getColumnDimension('P')->setWidth(12);  // Q.Date
      $sheet->getColumnDimension('Q')->setWidth(10);  // Delay
      $sheet->getColumnDimension('R')->setWidth(16);  // Q.Encoding Date
      $sheet->getColumnDimension('S')->setWidth(12);  // BS Date
      $sheet->getColumnDimension('T')->setWidth(18);  // Dispatch/Delivery Date
      $sheet->getColumnDimension('U')->setWidth(15);  // Clearing Status
      $sheet->getColumnDimension('V')->setWidth(15);  // General Status
      $sheet->getColumnDimension('W')->setWidth(12);  // Site

      // Write data
      $rowNum = 2;
      $counter = 1;

      foreach ($pendingMCAs as $mca) {
        // Calculate delay (days between Q.Date and today)
        $delay = '';
        if (!empty($mca['q_date'])) {
          $qDate = new DateTime($mca['q_date']);
          $today = new DateTime();
          $interval = $today->diff($qDate);
          $delay = $interval->days;
        }

        // Build Truck/Wagon/AWB field
        $truckWagonAwb = '';
        if (!empty($mca['horse'])) {
          $truckWagonAwb = $mca['horse'];
          if (!empty($mca['trailer_1'])) {
            $truckWagonAwb .= ' / ' . $mca['trailer_1'];
          }
          if (!empty($mca['trailer_2'])) {
            $truckWagonAwb .= ' / ' . $mca['trailer_2'];
          }
        } elseif (!empty($mca['wagon_ref'])) {
          $truckWagonAwb = $mca['wagon_ref'];
        }

        // Get site name if site_of_loading_id exists
        $site = '';
        if (!empty($mca['site_of_loading_id'])) {
          try {
            $siteSql = "SELECT site_name FROM site_master_t WHERE id = ? AND display = 'Y' LIMIT 1";
            $siteResult = $this->db->customQuery($siteSql, [$mca['site_of_loading_id']]);
            if (!empty($siteResult)) {
              $site = $siteResult[0]['site_name'] ?? '';
            }
          } catch (Exception $e) {
            // Site lookup failed, leave empty
            $site = '';
          }
        }

        // Clearing status - just use the ID value
        $clearingStatusText = $mca['clearing_status'] ?? '';

        $rowData = [
          $counter++,                                                                                     // #
          $mca['mca_ref'] ?? '',                                                                         // MCA File Ref
          '',                                                                                            // Tally Ref (empty - column doesn't exist)
          '',                                                                                            // Support Documents (empty)
          $mca['lot_number'] ?? '',                                                                      // Lot Num / Inv.No
          $mca['po_ref'] ?? '',                                                                          // PO.No
          $mca['client_name'] ?? '',                                                                     // Client
          $mca['commodity'] ?? '',                                                                       // Commodity
          $truckWagonAwb,                                                                                // Truck / Wagon / AWB
          $mca['e_ref'] ?? '',                                                                           // E.Ref
          !empty($mca['e_date']) ? date('Y-m-d', strtotime($mca['e_date'])) : '',                      // E.Date
          $mca['l_ref'] ?? '',                                                                           // L.Ref
          !empty($mca['l_date']) ? date('Y-m-d', strtotime($mca['l_date'])) : '',                      // L.Date
          $mca['l_amount'] ?? 0,                                                                         // L.Amount
          $mca['q_ref'] ?? '',                                                                           // Q.Ref
          !empty($mca['q_date']) ? date('Y-m-d', strtotime($mca['q_date'])) : '',                      // Q.Date
          $delay,                                                                                        // Delay
          '',                                                                                            // Q.Encoding Date (empty - column doesn't exist)
          '',                                                                                            // BS Date (empty - column doesn't exist)
          !empty($mca['dispatch_delivery_date']) ? date('Y-m-d', strtotime($mca['dispatch_delivery_date'])) : '', // Dispatch/Delivery Date
          $clearingStatusText,                                                                           // Clearing Status (just the ID)
          '',                                                                                            // General Status (empty - column doesn't exist)
          $site                                                                                          // Site
        ];

        $col = 'A';
        foreach ($rowData as $index => $value) {
          $cellRef = $col . $rowNum;
          $sheet->setCellValue($cellRef, $value);
          
          // Format numeric columns
          if ($col == 'N') {
            // L.Amount with 2 decimals
            $sheet->getStyle($cellRef)->getNumberFormat()->setFormatCode('#,##0.00');
          }
          
          $col++;
        }

        // Apply borders to row
        $sheet->getStyle('A' . $rowNum . ':W' . $rowNum)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $rowNum++;
      }

      // Freeze header row
      $sheet->freezePane('A2');

      // Generate filename
      $filename = 'Dossiers_Non_Facturés_' . date('Y_m_d_H_i_s') . '.xlsx';

      // Clear output buffer
      if (ob_get_length()) {
        ob_end_clean();
      }

      // Set headers for download
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      header('Cache-Control: max-age=1');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Pragma: public');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);

      exit;

    } catch (\Exception $e) {
      $this->logError('Error in exportPendingInvoicing: ' . $e->getMessage());

      while (ob_get_level()) {
        ob_end_clean();
      }

      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  // ========== UPDATE INVOICE ==========
  private function updateInvoice()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $this->db->beginTransaction();

      $invoiceId = (int)($_POST['invoice_id'] ?? 0);

      if ($invoiceId <= 0) {
        $this->db->rollback();
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id FROM export_invoices_t WHERE id = ?", [$invoiceId]);
      if (empty($existing)) {
        $this->db->rollback();
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $validation = $this->validateInvoiceData($_POST, $invoiceId);
      if (!$validation['success']) {
        $this->db->rollback();
        echo json_encode($validation);
        return;
      }

      $mcaDataArray = [];
      $totalWeight = 0;
      $totalDuty = 0;
      $totalFOB = 0;

      if (!empty($_POST['mca_data'])) {
        $mcaDataArray = json_decode($_POST['mca_data'], true);

        if (!empty($mcaDataArray) && is_array($mcaDataArray)) {
          foreach ($mcaDataArray as $mca) {
            $weight = (float)($mca['weight'] ?? 0);
            $totalWeight += $weight;

            $totalDuty += (float)($mca['ceec_amount'] ?? 0);
            $totalDuty += (float)($mca['cgea_amount'] ?? 0);
            $totalDuty += (float)($mca['occ_amount'] ?? 0);
            $totalDuty += (float)($mca['lmc_amount'] ?? 0);
            $totalDuty += (float)($mca['ogefrem_amount'] ?? 0);

            $totalFOB += $weight * 8500;
          }
        }
      }

      if (empty($mcaDataArray)) {
        $this->db->rollback();
        echo json_encode(['success' => false, 'message' => 'At least one MCA Reference is required']);
        return;
      }

      $quotationId = $this->toInt($_POST['quotation_id'] ?? null);
      $quotationSubTotal = $quotationId ? $this->toDecimal($_POST['quotation_sub_total'] ?? 0) : null;
      $quotationVatAmount = $quotationId ? $this->toDecimal($_POST['quotation_vat_amount'] ?? 0) : null;
      $quotationTotalAmount = $quotationId ? $this->toDecimal($_POST['quotation_total_amount'] ?? 0) : null;

      $sql = "UPDATE export_invoices_t SET
                client_id = ?, license_id = ?,
                kind_id = ?, goods_type_id = ?, transport_mode_id = ?,
                invoice_ref = ?, invoice_date = ?,
                fob_usd = ?, total_weight = ?, total_duty_cdf = ?,
                quotation_id = ?, quotation_sub_total = ?, quotation_vat_amount = ?, quotation_total_amount = ?,
                arsp = ?,
                updated_by = ?, updated_at = NOW()
              WHERE id = ?";

      $params = [
        $this->toInt($_POST['client_id']),
        $this->toInt($_POST['license_id']),
        $this->toInt($_POST['kind_id'] ?? null),
        $this->toInt($_POST['goods_type_id'] ?? null),
        $this->toInt($_POST['transport_mode_id'] ?? null),
        $this->clean($_POST['invoice_ref']),
        $this->toDate($_POST['invoice_date'] ?? date('Y-m-d')),
        $this->toDecimal($totalFOB),
        $totalWeight,
        $totalDuty,
        $quotationId,
        $quotationSubTotal,
        $quotationVatAmount,
        $quotationTotalAmount,
        $this->clean($_POST['arsp'] ?? 'Disabled'),
        (int)($_SESSION['user_id'] ?? 1),
        $invoiceId
      ];

      $this->db->customQuery($sql, $params);

      $this->saveMCADetails($invoiceId, $mcaDataArray);

      $itemsJson = $_POST['quotation_items'] ?? '';
      if (!empty($itemsJson)) {
        $this->saveInvoiceItems($invoiceId, $itemsJson);
      }

      $this->db->commit();

      echo json_encode(['success' => true, 'message' => 'Export invoice updated successfully!']);

    } catch (Exception $e) {
      $this->db->rollback();
      $this->logError("UPDATE Exception: " . $e->getMessage());
      $this->logError("Stack trace: " . $e->getTraceAsString());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }

  // ========== SAVE MCA DETAILS ==========
private function saveMCADetails($invoiceId, $mcaDataArray)
{
  $this->logError("========== saveMCADetails START ==========");
  $this->logError("Invoice ID: $invoiceId");

  if ($invoiceId <= 0 || empty($mcaDataArray) || !is_array($mcaDataArray)) {
    throw new Exception("Invalid invoiceId or MCA data");
  }

  // Clear existing rows
  $this->db->customQuery(
    "DELETE FROM export_invoice_mca_details_t WHERE export_invoice_id = ?",
    [(int)$invoiceId]
  );

  // ✅ FIXED: Added feet_container_id
  $sql = "INSERT INTO export_invoice_mca_details_t (
    export_invoice_id, mca_id, display_order,
    lot_number, declaration_no, declaration_date,
    liquidation_no, liquidation_date, liquidation_amount, liquidation_usd,
    quittance_no, quittance_date,
    horse, trailer_1, trailer_2, container, feet_container_id,
    weight, bcc_rate, buyer,
    ceec_amount, cgea_amount, occ_amount, lmc_amount, ogefrem_amount,
    created_at
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
  
  $insertCount = 0;

  foreach ($mcaDataArray as $index => $mca) {
    $mcaId = filter_var($mca['mca_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$mcaId) {
      throw new Exception("Invalid mca_id at index $index");
    }
    
    $params = [
      $invoiceId,
      $mcaId,
      $index + 1,
      $this->clean($mca['lot_number'] ?? null),
      $this->clean($mca['declaration_no'] ?? null),
      $this->toDate($mca['declaration_date'] ?? null),
      $this->clean($mca['liquidation_no'] ?? null),
      $this->toDate($mca['liquidation_date'] ?? null),
      $this->toDecimal($mca['liquidation_amount'] ?? 0, 2),
      $this->toDecimal($mca['liquidation_usd'] ?? 0, 2),
      $this->clean($mca['quittance_no'] ?? null),
      $this->toDate($mca['quittance_date'] ?? null),
      $this->clean($mca['horse'] ?? null),
      $this->clean($mca['trailer_1'] ?? null),
      $this->clean($mca['trailer_2'] ?? null),
      $this->clean($mca['container'] ?? null),
      $this->toInt($mca['feet_container_id'] ?? null),  // ✅ ADDED
      $this->toDecimal($mca['weight'] ?? 0, 3),
      $this->toDecimal($mca['bcc_rate'] ?? $this->defaultBccRate, 2),
      $this->clean($mca['buyer'] ?? null),
      $this->toDecimal($mca['ceec_amount'] ?? 0, 2),
      $this->toDecimal($mca['cgea_amount'] ?? 0, 2),
      $this->toDecimal($mca['occ_amount'] ?? 0, 2),
      $this->toDecimal($mca['lmc_amount'] ?? 0, 2),
      $this->toDecimal($mca['ogefrem_amount'] ?? 0, 2)
    ];

    $this->db->customQuery($sql, $params);
    $insertCount++;
  }

  if ($insertCount === 0) {
    throw new Exception("No MCA rows inserted");
  }

  $this->logError("✓ MCA INSERT SUCCESS — Rows: $insertCount");
  return true;
}

  // ========== SAVE INVOICE ITEMS ==========
  private function saveInvoiceItems($invoiceId, $itemsJson)
  {
    if (empty($itemsJson)) {
      return true;
    }

    $items = json_decode($itemsJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new Exception("Invalid items JSON: " . json_last_error_msg());
    }

    if (!is_array($items) || count($items) === 0) {
      return true;
    }

    // Clear existing items
    $this->db->customQuery(
      "DELETE FROM export_invoice_items_t WHERE export_invoice_id = ?",
      [$invoiceId]
    );

    $sql = "INSERT INTO export_invoice_items_t 
      (export_invoice_id, quotation_item_id,
       category_id, category_name, category_header, display_order,
       item_id, item_name,
       unit_id, unit_text,
       quantity, taux_usd, cost_usd,
       currency_id,
       has_tva, tva_usd, subtotal_usd, total_usd,
       created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $insertedCount = 0;

    foreach ($items as $item) {
      $params = [
        $invoiceId,
        $item['id'] ?? null,
        $item['category_id'] ?? null,
        $this->clean($item['category_name'] ?? 'UNCATEGORIZED'),
        $this->clean($item['category_header'] ?? $item['category_name'] ?? 'UNCATEGORIZED'),
        (int)($item['display_order'] ?? 999),
        $item['item_id'] ?? null,
        $this->clean($item['item_name'] ?? 'Unnamed Item'),
        $item['unit_id'] ?? null,
        $this->clean($item['unit_text'] ?? 'Unit'),
        (float)($item['quantity'] ?? 1),
        (float)($item['taux_usd'] ?? 0),
        (float)($item['cost_usd'] ?? 0),
        $item['currency_id'] ?? null,
        (int)($item['has_tva'] ?? 0),
        (float)($item['tva_usd'] ?? 0),
        (float)($item['subtotal_usd'] ?? 0),
        (float)($item['total_usd'] ?? 0),
      ];

      $this->db->customQuery($sql, $params);
      $insertedCount++;
    }

    if ($insertedCount === 0) {
      throw new Exception("No invoice items were inserted");
    }

    $this->logError("✓ Invoice items inserted: $insertedCount");
    return true;
  }

  // ========== GET INVOICE ITEMS ==========
  private function getInvoiceItems($invoiceId)
  {
    try {
      $sql = "SELECT * FROM export_invoice_items_t
              WHERE export_invoice_id = ?
              ORDER BY display_order ASC, category_id ASC, id ASC";

      $items = $this->db->customQuery($sql, [$invoiceId]) ?: [];
      return $this->sanitizeArray($items);

    } catch (Exception $e) {
      $this->logError("Error getting export invoice items: " . $e->getMessage());
      return [];
    }
  }

  // ========== GET INVOICE ==========
private function getInvoice()
{
    try {
        $invoiceId = (int)($_GET['id'] ?? 0);

        if ($invoiceId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
            return;
        }

        // Get invoice header
        $sql = "SELECT inv.*, 
                       c.short_name as client_name, c.company_name, 
                       l.license_number, 
                       k.kind_name,
                       tg.goods_type as goods_type_name,
                       tm.transport_mode_name
                FROM export_invoices_t inv
                LEFT JOIN clients_t c ON inv.client_id = c.id
                LEFT JOIN licenses_t l ON inv.license_id = l.id
                LEFT JOIN kind_master_t k ON inv.kind_id = k.id
                LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                LEFT JOIN transport_mode_master_t tm ON inv.transport_mode_id = tm.id
                WHERE inv.id = ?";

        $invoice = $this->db->customQuery($sql, [$invoiceId]);

        if (!empty($invoice)) {
            $invoiceData = $this->sanitizeArray($invoice)[0];
            $items = $this->getInvoiceItems($invoiceId);
            $clientId = (int)($invoiceData['client_id'] ?? 0);

            // ✅ SIMPLE FIX: Only get MCAs that are saved with THIS invoice
            // Join export_invoice_mca_details_t with exports_t to get MCA info
            $mcaSql = "SELECT e.id, e.mca_ref, e.feet_container, fcm.feet_container_size, e.container,
                              e.quittance_date, e.quittance_reference
                       FROM export_invoice_mca_details_t eimd
                       INNER JOIN exports_t e ON eimd.mca_id = e.id
                       LEFT JOIN feet_container_master_t fcm ON e.feet_container = fcm.id AND fcm.display = 'Y'
                       WHERE eimd.export_invoice_id = ?
                       ORDER BY eimd.display_order ASC, eimd.id ASC";
            
            $allMcas = $this->db->customQuery($mcaSql, [$invoiceId]) ?: [];

            $this->logError("Edit Mode - Invoice ID: $invoiceId, MCAs found: " . count($allMcas));

            // Get all quotations for client
            $allQuotations = [];
            if ($clientId > 0) {
                $quotSql = "SELECT q.id, q.quotation_ref, q.quotation_date, 
                                   q.sub_total, q.vat_amount, q.total_amount,
                                   q.kind_id, q.transport_mode_id, q.goods_type_id,
                                   k.kind_name,
                                   gt.goods_type as type_of_goods_name,
                                   tm.transport_mode_name
                            FROM quotations_t q
                            LEFT JOIN kind_master_t k ON q.kind_id = k.id
                            LEFT JOIN type_of_goods_master_t gt ON q.goods_type_id = gt.id
                            LEFT JOIN transport_mode_master_t tm ON q.transport_mode_id = tm.id
                            WHERE q.client_id = ? AND q.display = 'Y'
                            ORDER BY q.created_at DESC LIMIT 100";
                $allQuotations = $this->db->customQuery($quotSql, [$clientId]) ?: [];
            }

            // Get MCA details saved with this invoice (all the field values)
            $mcaData = [];
            $mcaDetailsSql = "SELECT eimd.*, fcm.feet_container_size
                              FROM export_invoice_mca_details_t eimd
                              LEFT JOIN feet_container_master_t fcm ON eimd.feet_container_id = fcm.id AND fcm.display = 'Y'
                              WHERE eimd.export_invoice_id = ? 
                              ORDER BY eimd.display_order ASC, eimd.id ASC";
            $mcaDetailsResults = $this->db->customQuery($mcaDetailsSql, [$invoiceId]);

            if (!empty($mcaDetailsResults)) {
                foreach ($mcaDetailsResults as $detail) {
                    $mcaData[] = [
                        'mca_id' => $detail['mca_id'],
                        'lot_number' => $detail['lot_number'] ?? '',
                        'declaration_no' => $detail['declaration_no'] ?? '',
                        'declaration_date' => $detail['declaration_date'] ?? '',
                        'liquidation_no' => $detail['liquidation_no'] ?? '',
                        'liquidation_date' => $detail['liquidation_date'] ?? '',
                        'liquidation_amount' => $detail['liquidation_amount'] ?? 0,
                        'liquidation_usd' => $detail['liquidation_usd'] ?? 0,
                        'quittance_no' => $detail['quittance_no'] ?? '',
                        'quittance_date' => $detail['quittance_date'] ?? '',
                        'horse' => $detail['horse'] ?? '',
                        'trailer_1' => $detail['trailer_1'] ?? '',
                        'trailer_2' => $detail['trailer_2'] ?? '',
                        'container' => $detail['container'] ?? '',
                        'feet_container_id' => $detail['feet_container_id'] ?? null,
                        'feet_container_size' => $detail['feet_container_size'] ?? '',
                        'weight' => $detail['weight'] ?? 0,
                        'bcc_rate' => $detail['bcc_rate'] ?? $this->defaultBccRate,
                        'buyer' => $detail['buyer'] ?? '',
                        'ceec_amount' => $detail['ceec_amount'] ?? 0,
                        'cgea_amount' => $detail['cgea_amount'] ?? 0,
                        'occ_amount' => $detail['occ_amount'] ?? 0,
                        'lmc_amount' => $detail['lmc_amount'] ?? 0,
                        'ogefrem_amount' => $detail['ogefrem_amount'] ?? 0
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $invoiceData,
                'items' => $items,
                'mca_data' => $mcaData,
                'mca_count' => count($mcaData),
                'all_mcas' => $this->sanitizeArray($allMcas),
                'all_quotations' => $this->sanitizeArray($allQuotations)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        }
    } catch (Exception $e) {
        $this->logError("Error getting export invoice: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load invoice data']);
    }
}
private function listInvoices()
{
  try {
    $draw = (int)($_GET['draw'] ?? 1);
    $start = (int)($_GET['start'] ?? 0);
    $length = (int)($_GET['length'] ?? 25);
    $searchValue = isset($_GET['search']['value']) ? $this->sanitizeInput(trim($_GET['search']['value'])) : '';
    $filter = $this->sanitizeInput($_GET['filter'] ?? 'all');
    $orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0);
    $orderDirection = (strtolower($_GET['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';

    $columns = ['inv.id', 'inv.invoice_ref', 'c.short_name', 'mca_count', 'tg.goods_type', 'u.full_name', 'inv.validated'];
    $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'inv.id';

    $baseQuery = "FROM export_invoices_t inv 
                  LEFT JOIN clients_t c ON inv.client_id = c.id 
                  LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                  LEFT JOIN users_t u ON inv.created_by = u.id
                  WHERE 1=1";

    // ✅ REMOVED: User-based filtering - Now everyone sees all invoices

    $filterCondition = "";
    if ($filter === 'validated')
      $filterCondition = " AND inv.validated = 1";
    elseif ($filter === 'not-validated')
      $filterCondition = " AND inv.validated = 0";
    elseif ($filter === 'dgi-verified')
      $filterCondition = " AND inv.validated = 2";
    elseif ($filter === 'pending')
      $filterCondition = " AND inv.validated = 0";

    $searchCondition = "";
    $searchParams = [];
    if (!empty($searchValue)) {
      $searchCondition = " AND (inv.invoice_ref LIKE ? OR c.short_name LIKE ? OR c.company_name LIKE ? OR tg.goods_type LIKE ? OR u.full_name LIKE ?)";
      $searchParam = "%{$searchValue}%";
      $searchParams = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    }

    // Total records (all invoices)
    $totalSql = "SELECT COUNT(*) as total FROM export_invoices_t inv";
    $totalResult = $this->db->customQuery($totalSql);
    $totalRecords = (int)($totalResult[0]['total'] ?? 0);

    // Filtered records count
    $filteredSql = "SELECT COUNT(*) as total {$baseQuery} {$filterCondition} {$searchCondition}";
    $filteredResult = $this->db->customQuery($filteredSql, $searchParams);
    $filteredRecords = (int)($filteredResult[0]['total'] ?? 0);

    // Data query
    $dataSql = "SELECT inv.id, inv.invoice_ref, inv.fob_usd, inv.total_duty_cdf, inv.validated, 
                       c.short_name as client_name, c.company_name, inv.created_at,
                       (SELECT COUNT(*) FROM export_invoice_mca_details_t WHERE export_invoice_id = inv.id) as mca_count,
                       COALESCE(tg.goods_type, 'N/A') as goods_type_name,
                       COALESCE(u.full_name, u.username, 'N/A') as encoded_by
                {$baseQuery} {$filterCondition} {$searchCondition} 
                ORDER BY {$orderColumn} {$orderDirection} LIMIT {$length} OFFSET {$start}";
    $invoices = $this->db->customQuery($dataSql, $searchParams);

    echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $filteredRecords,
      'data' => $this->sanitizeArray($invoices ?: [])
    ]);
  } catch (Exception $e) {
    $this->logError("Error listing export invoices: " . $e->getMessage());
    echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
  }
}

  private function getStatistics()
  {
    try {
      // Get invoice statistics
      $sql = "SELECT COUNT(*) as total_invoices,
                SUM(CASE WHEN validated = 1 THEN 1 ELSE 0 END) as validated_invoices,
                SUM(CASE WHEN validated = 0 THEN 1 ELSE 0 END) as not_validated_invoices,
                SUM(CASE WHEN validated = 2 THEN 1 ELSE 0 END) as dgi_verified_invoices
              FROM export_invoices_t";

      $stats = $this->db->customQuery($sql);
      
      // Get pending invoicing count (MCA files with quittance but not yet invoiced)
      $pendingSql = "SELECT COUNT(*) as pending_count
                     FROM exports_t e
                     WHERE e.display = 'Y'
                       AND e.quittance_date IS NOT NULL
                       AND e.quittance_date != ''
                       AND e.id NOT IN (
                         SELECT DISTINCT mca_id 
                         FROM export_invoice_mca_details_t 
                         WHERE mca_id IS NOT NULL
                       )";
      
      $pendingResult = $this->db->customQuery($pendingSql);
      $pendingCount = (int)($pendingResult[0]['pending_count'] ?? 0);
      
      echo json_encode([
        'success' => true,
        'data' => [
          'total_invoices' => (int)($stats[0]['total_invoices'] ?? 0),
          'validated_invoices' => (int)($stats[0]['validated_invoices'] ?? 0),
          'not_validated_invoices' => (int)($stats[0]['not_validated_invoices'] ?? 0),
          'dgi_verified_invoices' => (int)($stats[0]['dgi_verified_invoices'] ?? 0),
          'pending_invoicing' => $pendingCount
        ]
      ]);
    } catch (Exception $e) {
      $this->logError("Error getting statistics: " . $e->getMessage());
      echo json_encode([
        'success' => true, 
        'data' => [
          'total_invoices' => 0, 
          'validated_invoices' => 0, 
          'not_validated_invoices' => 0, 
          'dgi_verified_invoices' => 0,
          'pending_invoicing' => 0
        ]
      ]);
    }
  }

  // ========== VALIDATE INVOICE ==========
  private function validateInvoice()
  {
    $this->validateCsrfToken();

    try {
      $invoiceId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id, validated FROM export_invoices_t WHERE id = ?", [$invoiceId]);

      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $userId = (int)($_SESSION['user_id'] ?? 1);
      $sql = "UPDATE export_invoices_t SET validated = 1, updated_by = ?, updated_at = NOW() WHERE id = ?";
      $this->db->customQuery($sql, [$userId, $invoiceId]);

      echo json_encode([
        'success' => true,
        'message' => 'Invoice validated successfully!'
      ]);

    } catch (Exception $e) {
      $this->logError("Validate Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }

  // ========== MARK DGI VERIFIED ==========
  private function markDGI()
  {
    $this->validateCsrfToken();

    try {
      $invoiceId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id, validated FROM export_invoices_t WHERE id = ?", [$invoiceId]);

      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $currentValidated = (int)($existing[0]['validated'] ?? 0);
      if ($currentValidated !== 1) {
        echo json_encode(['success' => false, 'message' => 'Invoice must be validated first before marking as DGI Verified']);
        return;
      }

      $invoiceData = $this->buildEmcfPayload($invoiceId, 'EXPORT');
      echo $this->sendInvoiceToEmcf($invoiceId, 'EXPORT', $invoiceData);
    } catch (Exception $e) {
      $this->logError("Mark DGI Verified Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }

  private function buildEmcfPayload(int $invoiceId, string $type): array
  {
      $header = $this->db->customQuery("
          SELECT
              eit.invoice_ref,
              ct.nif_number   AS client_nif,
              ct.company_name AS client_name,
              ct.email        AS client_contact,
              ct.address      AS client_address,
              ut.id           AS operator_id,
              ut.full_name    AS operator_name,
              ei.uid          AS emcf_uid
          FROM export_invoices_t eit
          LEFT JOIN emcf_invoice ei ON ei.inv_type = 'EXPORT' AND ei.invoice_id = eit.id
          LEFT JOIN clients_t ct ON eit.client_id = ct.id
          LEFT JOIN users_t ut   ON eit.created_by = ut.id
          WHERE eit.id = ?
          LIMIT 1
      ",[$invoiceId]);
      $header = $header[0] ?? null;
      if (!$header) {
          throw new Exception('Invoice not found');
      }

      $res = $this->db->customQuery("
          SELECT
              eiit.item_name,
              (eiit.subtotal_usd * 2500) AS price,
              eiit.quantity,
              imt.id AS item_code,
              imt.tax_not_tax AS taxGroup
          FROM export_invoice_items_t eiit
          LEFT JOIN item_master_t imt ON eiit.item_id = imt.id
          WHERE eiit.export_invoice_id = ? AND eiit.category_id IN (3,4)
      ",[$invoiceId]);

      return $this->createEmcfPayload($res, $header);
  }

  private function finalizeEMCF()
  {
      try {
          $this->validateCsrfToken();
          $uid = trim($_POST['uid'] ?? '');
          $type = trim($_POST['type'] ?? '');
          $invoiceId = (int)($_POST['invoice_id'] ?? 0);

          if (!$uid) {
              echo json_encode(['success' => false, 'message' => 'Invalid UID']);
              return;
          }else if ($type == '') {
              echo json_encode(['success' => false, 'message' => 'Confirm/Cancel not specified']);
              return;
          }

          $response = $this->sendFinalizeEMCF($uid, $type);
          if($type === 'confirm' && $response['success']) {
              $userId = (int)($_SESSION['user_id'] ?? 1);
              $sql = "UPDATE export_invoices_t SET validated = 2, updated_by = ?, updated_at = NOW() WHERE id = ?";
              $this->db->customQuery($sql, [$userId, $invoiceId]);
              $response['msg'] = "e-MCF Invoice verified successfully!";
          }
          echo json_encode($response);

      } catch (Exception $e) {
          $this->logError("Finalize e-MCF Invoice Exception: " . $e->getMessage());
          echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
      }
  }

  // ========== HELPER METHODS ==========

  private function getClientDetails()
  {
    try {
      $clientId = (int)($_GET['client_id'] ?? 0);

      if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => null]);
        return;
      }

      $sql = "SELECT id, short_name, company_name, invoice_template FROM clients_t WHERE id = ? AND display = 'Y' LIMIT 1";
      $result = $this->db->customQuery($sql, [$clientId]);

      if (!empty($result)) {
        echo json_encode(['success' => true, 'data' => $this->sanitizeArray($result)[0]]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Client not found', 'data' => null]);
      }
    } catch (Exception $e) {
      $this->logError("Error getting client details: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load client details', 'data' => null]);
    }
  }

  private function getAllQuotationsForClient()
  {
    try {
      $clientId = (int)($_GET['client_id'] ?? 0);

      if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => []]);
        return;
      }

      $sql = "SELECT q.id, q.quotation_ref, q.quotation_date, 
                     q.sub_total, q.vat_amount, q.total_amount,
                     q.sub_total_cdf, q.vat_amount_cdf, q.total_amount_cdf,
                     q.kind_id, q.transport_mode_id, q.goods_type_id,
                     q.arsp, q.arsp_amount, q.client_id,
                     k.kind_name,
                     gt.goods_type as type_of_goods_name,
                     tm.transport_mode_name,
                     q.created_at
              FROM quotations_t q
              LEFT JOIN kind_master_t k ON q.kind_id = k.id AND k.display = 'Y'
              LEFT JOIN type_of_goods_master_t gt ON q.goods_type_id = gt.id AND gt.display = 'Y'
              LEFT JOIN transport_mode_master_t tm ON q.transport_mode_id = tm.id AND tm.display = 'Y'
              WHERE q.client_id = ? 
              AND q.display = 'Y'
              ORDER BY q.created_at DESC, q.id DESC
              LIMIT 100";

      $quotations = $this->db->customQuery($sql, [$clientId]);

      if (empty($quotations)) {
        echo json_encode([
          'success' => true,
          'message' => 'No quotations found for this client',
          'data' => []
        ]);
        return;
      }

      echo json_encode([
        'success' => true,
        'data' => $this->sanitizeArray($quotations),
        'count' => count($quotations)
      ]);

    } catch (Exception $e) {
      $this->logError("Error in getAllQuotationsForClient: " . $e->getMessage());
      echo json_encode([
        'success' => false,
        'message' => 'Failed to load quotations: ' . $e->getMessage(),
        'data' => []
      ]);
    }
  }

  private function getQuotationItems()
  {
    try {
      $quotationId = (int)($_GET['quotation_id'] ?? 0);
      $clientId = (int)($_GET['client_id'] ?? 0);

      if ($quotationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quotation ID', 'items' => [], 'categorized_items' => []]);
        return;
      }

      $quotationSql = "SELECT q.id, q.client_id, q.quotation_ref, q.quotation_date,
                              q.sub_total, q.vat_amount, q.total_amount,
                              q.sub_total_cdf, q.vat_amount_cdf, q.total_amount_cdf,
                              q.arsp, q.arsp_amount,
                              q.kind_id, q.transport_mode_id, q.goods_type_id,
                              k.kind_name,
                              gt.goods_type as type_of_goods_name,
                              tm.transport_mode_name
                       FROM quotations_t q
                       LEFT JOIN kind_master_t k ON q.kind_id = k.id
                       LEFT JOIN type_of_goods_master_t gt ON q.goods_type_id = gt.id
                       LEFT JOIN transport_mode_master_t tm ON q.transport_mode_id = tm.id
                       WHERE q.id = ?
                       LIMIT 1";

      $quotationResult = $this->db->customQuery($quotationSql, [$quotationId]);

      if (empty($quotationResult)) {
        echo json_encode(['success' => false, 'message' => 'Quotation not found', 'items' => [], 'categorized_items' => []]);
        return;
      }

      $quotation = $quotationResult[0];

      $displayFilteredSql = "SELECT * FROM quotation_items_t WHERE quotation_id = ? AND display = 'Y' ORDER BY id ASC";
      $displayFiltered = $this->db->customQuery($displayFilteredSql, [$quotationId]) ?: [];

      if (empty($displayFiltered)) {
        echo json_encode([
          'success' => true,
          'message' => 'No items with display=Y',
          'quotation' => $this->sanitizeArray($quotation),
          'items' => [],
          'categorized_items' => []
        ]);
        return;
      }

      $items = [];

      foreach ($displayFiltered as $rawItem) {
        $item = [
          'id' => $rawItem['id'],
          'quotation_id' => $rawItem['quotation_id'],
          'category_id' => $rawItem['category_id'] ?? 0,
          'item_id' => $rawItem['item_id'] ?? null,
          'quantity' => 1,
          'unit_id' => $rawItem['unit_id'] ?? null,
          'unit_text' => $rawItem['unit_text'] ?? 'Unit',
          'calculation_type' => $rawItem['calculation_type'] ?? 'per_file',
          'taux_usd' => $rawItem['taux_usd'] ?? 0,
          'cost_usd' => $rawItem['cost_usd'] ?? $rawItem['taux_usd'] ?? 0,
          'subtotal_usd' => $rawItem['subtotal_usd'] ?? 0,
          'currency_id' => $rawItem['currency_id'] ?? null,
          'has_tva' => $rawItem['has_tva'] ?? 0,
          'tva_usd' => $rawItem['tva_usd'] ?? 0,
          'total_usd' => $rawItem['total_usd'] ?? 0,
          'cif_split' => $rawItem['cif_split'] ?? 0,
          'percentage' => $rawItem['percentage'] ?? 0,
          'rate_cdf' => $rawItem['rate_cdf'] ?? 0,
          'vat_cdf' => $rawItem['vat_cdf'] ?? 0,
          'total_cdf' => $rawItem['total_cdf'] ?? 0
        ];

        $itemName = 'Item #' . $rawItem['id'];
        if (!empty($rawItem['item_id'])) {
          $itemNameSql = "SELECT item_name FROM item_master_t WHERE id = ? LIMIT 1";
          $itemNameResult = $this->db->customQuery($itemNameSql, [$rawItem['item_id']]);
          if (!empty($itemNameResult)) {
            $itemName = $itemNameResult[0]['item_name'];
          }
        }
        $item['item_name'] = $itemName;
        $item['item_description'] = '';

        $categoryName = 'UNCATEGORIZED';
        $categoryHeader = 'UNCATEGORIZED';
        $displayOrder = 999;

        if (!empty($rawItem['category_id']) && (int)$rawItem['category_id'] > 0) {
          $categorySql = "SELECT category_name, category_header, display_order FROM quotation_categories_t WHERE id = ? AND display = 'Y' LIMIT 1";
          $categoryResult = $this->db->customQuery($categorySql, [$rawItem['category_id']]);
          if (!empty($categoryResult)) {
            $categoryName = $categoryResult[0]['category_name'] ?? 'UNCATEGORIZED';
            $categoryHeader = $categoryResult[0]['category_header'] ?? $categoryName;
            $displayOrder = $categoryResult[0]['display_order'] ?? 999;
          } else {
            $this->logError("Category ID {$rawItem['category_id']} not found or display=N for item {$rawItem['id']}");
          }
        }

        $item['category_name'] = $categoryName;
        $item['category_header'] = $categoryHeader;
        $item['display_order'] = $displayOrder;

        $item['unit_name'] = '';
        if (!empty($rawItem['unit_id'])) {
          $unitSql = "SELECT unit_name FROM unit_master_t WHERE id = ? LIMIT 1";
          $unitResult = $this->db->customQuery($unitSql, [$rawItem['unit_id']]);
          if (!empty($unitResult)) {
            $item['unit_name'] = $unitResult[0]['unit_name'];
          }
        }

        if (empty($item['unit_text']) || $item['unit_text'] === 'Unit') {
          if (!empty($item['unit_name'])) {
            $item['unit_text'] = $item['unit_name'];
          }
        }

        if (!empty($rawItem['currency_id'])) {
          $currSql = "SELECT currency_short_name FROM currency_master_t WHERE id = ? LIMIT 1";
          $currResult = $this->db->customQuery($currSql, [$rawItem['currency_id']]);
          if (!empty($currResult)) {
            $item['currency_short_name'] = $currResult[0]['currency_short_name'];
          } else {
            $item['currency_short_name'] = 'USD';
          }
        } else {
          $item['currency_short_name'] = 'USD';
        }

        $items[] = $item;
      }

      $items = $this->sanitizeArray($items);

      $groupedItems = [];
      foreach ($items as $item) {
        $catId = $item['category_id'] ?? 0;
        $catName = $item['category_name'] ?? 'UNCATEGORIZED';
        $catHeader = $item['category_header'] ?? $catName;
        $displayOrder = isset($item['display_order']) ? (int)$item['display_order'] : 999;

        if (!isset($groupedItems[$catId])) {
          $groupedItems[$catId] = [
            'category_id' => $catId,
            'category_name' => $catName,
            'category_header' => strtoupper($catHeader),
            'display_order' => $displayOrder,
            'category_subtotal_usd' => 0,
            'category_tva_usd' => 0,
            'category_total_usd' => 0,
            'category_total_cdf' => 0,
            'items' => []
          ];
        }

        $itemSubtotal = (float)($item['subtotal_usd'] ?? 0);
        $itemTVA = (float)($item['tva_usd'] ?? 0);
        $itemTotal = (float)($item['total_usd'] ?? 0);
        $itemTotalCDF = (float)($item['total_cdf'] ?? 0);

        $groupedItems[$catId]['category_subtotal_usd'] += $itemSubtotal;
        $groupedItems[$catId]['category_tva_usd'] += $itemTVA;
        $groupedItems[$catId]['category_total_usd'] += $itemTotal;
        $groupedItems[$catId]['category_total_cdf'] += $itemTotalCDF;
        $groupedItems[$catId]['items'][] = $item;
      }

      $categorizedItems = array_values($groupedItems);
      usort($categorizedItems, function ($a, $b) {
        return ($a['display_order'] ?? 999) - ($b['display_order'] ?? 999);
      });

      echo json_encode([
        'success' => true,
        'quotation' => $this->sanitizeArray($quotation),
        'items' => $items,
        'categorized_items' => $categorizedItems
      ]);

    } catch (Exception $e) {
      $this->logError("Error in getQuotationItems: " . $e->getMessage());
      echo json_encode([
        'success' => false,
        'message' => 'Failed to load quotation items: ' . $e->getMessage(),
        'items' => [],
        'categorized_items' => []
      ]);
    }
  }

private function getLicenses()
{
  try {
    $clientId = (int)($_GET['client_id'] ?? 0);
    $invoiceId = (int)($_GET['invoice_id'] ?? 0);

    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => []]);
      return;
    }

    // ✅ NEW: Get the license from the invoice being edited (if any)
    $invoiceLicenseId = null;
    if ($invoiceId > 0) {
      $invoiceLicenseSql = "SELECT license_id FROM export_invoices_t WHERE id = ?";
      $invoiceLicenseResult = $this->db->customQuery($invoiceLicenseSql, [$invoiceId]);
      if (!empty($invoiceLicenseResult)) {
        $invoiceLicenseId = (int)$invoiceLicenseResult[0]['license_id'];
      }
    }

    // Get all used MCA IDs (already invoiced) EXCLUDING current invoice if editing
    if ($invoiceId > 0) {
      $usedMcaSql = "SELECT DISTINCT mca_id FROM export_invoice_mca_details_t 
                     WHERE mca_id IS NOT NULL AND export_invoice_id != ?";
      $usedMcaResults = $this->db->customQuery($usedMcaSql, [$invoiceId]) ?: [];
    } else {
      $usedMcaSql = "SELECT DISTINCT mca_id FROM export_invoice_mca_details_t WHERE mca_id IS NOT NULL";
      $usedMcaResults = $this->db->customQuery($usedMcaSql) ?: [];
    }
    
    $usedMcaIds = [];
    foreach ($usedMcaResults as $row) {
      if (!empty($row['mca_id'])) {
        $usedMcaIds[] = (int)$row['mca_id'];
      }
    }
    
    // Build the NOT IN clause for used MCAs
    $notInClause = '';
    if (!empty($usedMcaIds)) {
      $notInClause = " AND e.id NOT IN (" . implode(',', $usedMcaIds) . ")";
    }

    // Get licenses that have available MCAs with quittance dates
    $sql = "SELECT DISTINCT l.id, l.license_number, l.kind_id, k.kind_name, k.kind_short_name
            FROM licenses_t l
            LEFT JOIN kind_master_t k ON l.kind_id = k.id
            INNER JOIN exports_t e ON l.id = e.license_id 
              AND e.subscriber_id = l.client_id
              AND e.display = 'Y'
              AND e.quittance_date IS NOT NULL
              AND e.quittance_date != ''
              {$notInClause}
            WHERE l.client_id = ? 
            AND l.display = 'Y' 
            AND l.status = 'ACTIVE'
            AND l.kind_id IN (3, 4, 7, 8)
            ORDER BY l.license_number ASC";

    $licenses = $this->db->customQuery($sql, [$clientId]) ?: [];

    // ✅ NEW: If editing and the invoice's license is NOT in the results, add it manually
    if ($invoiceLicenseId > 0) {
      $licenseFound = false;
      foreach ($licenses as $license) {
        if ((int)$license['id'] === $invoiceLicenseId) {
          $licenseFound = true;
          break;
        }
      }
      
      if (!$licenseFound) {
        $this->logError("⚠️ Invoice license ID {$invoiceLicenseId} not found in available licenses - adding manually");
        
        $extraLicenseSql = "SELECT l.id, l.license_number, l.kind_id, k.kind_name, k.kind_short_name
                           FROM licenses_t l
                           LEFT JOIN kind_master_t k ON l.kind_id = k.id
                           WHERE l.id = ? AND l.display = 'Y'";
        $extraLicense = $this->db->customQuery($extraLicenseSql, [$invoiceLicenseId]);
        
        if (!empty($extraLicense)) {
          // Add to beginning of array
          array_unshift($licenses, $extraLicense[0]);
          $this->logError("✅ Added invoice license to results");
        }
      }
    }

    echo json_encode(['success' => true, 'data' => $this->sanitizeArray($licenses)]);
  } catch (Exception $e) {
    $this->logError("Error getting licenses: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load licenses', 'data' => []]);
  }
}

private function getMCAReferences()
{
  try {
    $clientId = (int)($_GET['client_id'] ?? 0);
    $licenseId = (int)($_GET['license_id'] ?? 0);
    $invoiceId = (int)($_GET['invoice_id'] ?? 0);

    $this->logError("=== getMCAReferences START ===");
    $this->logError("Client ID: $clientId, License ID: $licenseId, Invoice ID: $invoiceId");

    if ($clientId <= 0 || $licenseId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid IDs', 'data' => []]);
      return;
    }

    // ✅ FIX: Different query based on whether we're editing or creating new
    if ($invoiceId > 0) {
      // EDIT MODE: Show MCAs for this invoice + available MCAs
      $sql = "SELECT 
                e.id, 
                e.mca_ref, 
                e.subscriber_id,
                e.license_id,
                e.quittance_date,
                e.feet_container, 
                fcm.feet_container_size, 
                e.container,
                eimd.export_invoice_id,
                ei.invoice_ref as invoiced_in,
                CASE 
                  WHEN eimd.export_invoice_id IS NOT NULL AND eimd.export_invoice_id != ? THEN 1
                  ELSE 0
                END as is_invoiced,
                CASE 
                  WHEN eimd.export_invoice_id = ? THEN 1
                  ELSE 0
                END as is_current_invoice
              FROM exports_t e 
              LEFT JOIN feet_container_master_t fcm ON e.feet_container = fcm.id AND fcm.display = 'Y'
              LEFT JOIN export_invoice_mca_details_t eimd ON e.id = eimd.mca_id
              LEFT JOIN export_invoices_t ei ON eimd.export_invoice_id = ei.id
              WHERE e.license_id = ? 
              AND e.subscriber_id = ? 
              AND e.display = 'Y'
              AND e.quittance_date IS NOT NULL
              AND e.quittance_date != ''
              AND (
                eimd.export_invoice_id IS NULL 
                OR eimd.export_invoice_id = ?
              )
              ORDER BY 
                CASE WHEN eimd.export_invoice_id = ? THEN 0 ELSE 1 END,
                e.id DESC 
              LIMIT 500";

      $params = [
        $invoiceId,      // For is_invoiced check
        $invoiceId,      // For is_current_invoice check
        $licenseId, 
        $clientId,
        $invoiceId,      // Show only available OR current invoice's MCAs
        $invoiceId       // Sort current invoice MCAs first
      ];
    } else {
      // NEW INVOICE MODE: Show ONLY available (not yet invoiced) MCAs
      $sql = "SELECT 
                e.id, 
                e.mca_ref, 
                e.subscriber_id,
                e.license_id,
                e.quittance_date,
                e.feet_container, 
                fcm.feet_container_size, 
                e.container,
                NULL as export_invoice_id,
                NULL as invoiced_in,
                0 as is_invoiced,
                0 as is_current_invoice
              FROM exports_t e 
              LEFT JOIN feet_container_master_t fcm ON e.feet_container = fcm.id AND fcm.display = 'Y'
              WHERE e.license_id = ? 
              AND e.subscriber_id = ? 
              AND e.display = 'Y'
              AND e.quittance_date IS NOT NULL
              AND e.quittance_date != ''
              AND e.id NOT IN (
                SELECT DISTINCT mca_id 
                FROM export_invoice_mca_details_t 
                WHERE mca_id IS NOT NULL
              )
              ORDER BY e.id DESC 
              LIMIT 500";

      $params = [$licenseId, $clientId];
    }
    
    $this->logError("Executing MCA query with params: " . json_encode($params));
    
    $allMcas = $this->db->customQuery($sql, $params) ?: [];
    
    $this->logError("Total MCAs found: " . count($allMcas));
    
    // Log summary
    $available = 0;
    $currentInvoice = 0;
    
    foreach ($allMcas as &$mca) {
      if ((int)($mca['is_current_invoice'] ?? 0) === 1) {
        $currentInvoice++;
        $mca['status'] = 'CURRENT_INVOICE';
        $mca['status_label'] = 'In Current Invoice';
      } else {
        $available++;
        $mca['status'] = 'AVAILABLE';
        $mca['status_label'] = 'Available';
      }
    }
    
    $this->logError("Available: $available, Current Invoice: $currentInvoice");
    $this->logError("=== getMCAReferences END ===");

    echo json_encode([
      'success' => true, 
      'data' => $this->sanitizeArray($allMcas),
      'summary' => [
        'total' => count($allMcas),
        'available' => $available,
        'current_invoice' => $currentInvoice
      ]
    ]);

  } catch (Exception $e) {
    $this->logError("❌ Error in getMCAReferences: " . $e->getMessage());
    $this->logError("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Failed to load MCA references', 'data' => []]);
  }
}

private function getMCADetails()
{
  try {
    $mcaId = (int)($_GET['mca_id'] ?? 0);

    if ($mcaId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid MCA ID', 'data' => null]);
      return;
    }

    // ✅ UPDATED QUERY - Proper field mapping from exports_t
    $sql = "SELECT 
              e.id, 
              e.subscriber_id, 
              e.license_id,
              
              -- Map exports_t fields to expected names
              e.kind as kind_id,
              e.type_of_goods as goods_type_id,
              e.transport_mode as transport_mode_id,
              
              -- Get names from lookup tables
              k.kind_name,
              k.kind_short_name,
              tg.goods_type as type_of_goods_name,
              tm.transport_mode_name,
              
              e.mca_ref, 
              e.currency as currency_id, 
              e.buyer, 
              e.regime, 
              e.types_of_clearance,
              e.invoice as facture_pfi_no, 
              e.po_ref, 
              e.weight, 
              e.fob,
              e.horse, 
              e.trailer_1, 
              e.trailer_2, 
              e.feet_container, 
              fcm.feet_container_size, 
              e.wagon_ref as wagon, 
              e.container,
              e.transporter, 
              e.site_of_loading_id, 
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
              e.document_status, 
              e.customs_clearing_code,
              e.dgda_in_date as declaration_date, 
              e.declaration_reference as declaration_no,
              e.liquidation_reference as liquidation_no, 
              e.liquidation_date,
              e.liquidation_paid_by, 
              e.liquidation_amount,
              e.quittance_reference as quittance_no, 
              e.quittance_date,
              e.dgda_out_date as dispatch_deliver_date,
              e.gov_docs_in_date, 
              e.gov_docs_out_date,
              e.dispatch_deliver_date as delivery_date,
              e.kanyaka_arrival_date, 
              e.kanyaka_departure_date,
              e.border_arrival_date, 
              e.exit_drc_date,
              e.exit_point_id, 
              e.end_of_formalities_date,
              e.truck_status, 
              e.clearing_status,
              e.lmc_id, 
              e.ogefrem_inv_ref,
              e.loading_to_dispatch_date, 
              e.audited_date, 
              e.archived_date,
              e.remarks,
              curr.currency_short_name
            FROM exports_t e
            LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
            LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id AND tg.display = 'Y'
            LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id AND tm.display = 'Y'
            LEFT JOIN currency_master_t curr ON e.currency = curr.id AND curr.display = 'Y'
            LEFT JOIN feet_container_master_t fcm ON e.feet_container = fcm.id AND fcm.display = 'Y'
            WHERE e.id = ? AND e.display = 'Y'
            LIMIT 1";

    $mcaDetails = $this->db->customQuery($sql, [$mcaId]);

    if (empty($mcaDetails)) {
      echo json_encode(['success' => false, 'message' => 'MCA not found', 'data' => null]);
      return;
    }

    $result = $this->sanitizeArray($mcaDetails)[0];
    
    // ✅ LOG THE INITIAL DATA
    $this->logError("getMCADetails - Initial data for MCA ID $mcaId:");
    $this->logError("  kind_id: " . ($result['kind_id'] ?? 'NULL'));
    $this->logError("  goods_type_id: " . ($result['goods_type_id'] ?? 'NULL'));
    $this->logError("  transport_mode_id: " . ($result['transport_mode_id'] ?? 'NULL'));

    // ========== FALLBACK MECHANISM FOR NULL VALUES ==========
    // If kind, goods_type, or transport_mode are NULL, try to get them from the license
    $needsFallback = empty($result['kind_id']) || empty($result['goods_type_id']) || empty($result['transport_mode_id']);
    
    if ($needsFallback && !empty($result['license_id'])) {
      $this->logError("⚠️ NULL values detected, attempting fallback from license ID: " . $result['license_id']);
      
      // Get kind from license
      if (empty($result['kind_id'])) {
        $licenseSql = "SELECT l.kind_id, k.kind_name, k.kind_short_name 
                       FROM licenses_t l
                       LEFT JOIN kind_master_t k ON l.kind_id = k.id AND k.display = 'Y'
                       WHERE l.id = ? LIMIT 1";
        $licenseData = $this->db->customQuery($licenseSql, [$result['license_id']]);
        
        if (!empty($licenseData) && !empty($licenseData[0]['kind_id'])) {
          $result['kind_id'] = $licenseData[0]['kind_id'];
          $result['kind_name'] = $licenseData[0]['kind_name'];
          $result['kind_short_name'] = $licenseData[0]['kind_short_name'];
          $this->logError("✅ Fallback: Got kind_id from license: " . $result['kind_id']);
        }
      }
      
      // Try to get goods_type and transport_mode from most recent quotation for this client
      if ((empty($result['goods_type_id']) || empty($result['transport_mode_id'])) && !empty($result['subscriber_id'])) {
        $this->logError("⚠️ Attempting to get goods_type/transport_mode from recent quotation");
        
        $quotFallbackSql = "SELECT q.goods_type_id, q.transport_mode_id,
                                   tg.goods_type as type_of_goods_name,
                                   tm.transport_mode_name
                            FROM quotations_t q
                            LEFT JOIN type_of_goods_master_t tg ON q.goods_type_id = tg.id AND tg.display = 'Y'
                            LEFT JOIN transport_mode_master_t tm ON q.transport_mode_id = tm.id AND tm.display = 'Y'
                            WHERE q.client_id = ? 
                              AND q.display = 'Y'
                              AND q.kind_id = ?
                            ORDER BY q.created_at DESC
                            LIMIT 1";
        
        $quotFallback = $this->db->customQuery($quotFallbackSql, [$result['subscriber_id'], $result['kind_id']]);
        
        if (!empty($quotFallback)) {
          if (empty($result['goods_type_id']) && !empty($quotFallback[0]['goods_type_id'])) {
            $result['goods_type_id'] = $quotFallback[0]['goods_type_id'];
            $result['type_of_goods_name'] = $quotFallback[0]['type_of_goods_name'];
            $this->logError("✅ Fallback: Got goods_type_id from quotation: " . $result['goods_type_id']);
          }
          
          if (empty($result['transport_mode_id']) && !empty($quotFallback[0]['transport_mode_id'])) {
            $result['transport_mode_id'] = $quotFallback[0]['transport_mode_id'];
            $result['transport_mode_name'] = $quotFallback[0]['transport_mode_name'];
            $this->logError("✅ Fallback: Got transport_mode_id from quotation: " . $result['transport_mode_id']);
          }
        }
      }
    }
    
    // ✅ LOG THE FINAL DATA AFTER FALLBACK
    $this->logError("getMCADetails - Final data for MCA ID $mcaId:");
    $this->logError("  kind_id: " . ($result['kind_id'] ?? 'NULL'));
    $this->logError("  kind_name: " . ($result['kind_name'] ?? 'NULL'));
    $this->logError("  goods_type_id: " . ($result['goods_type_id'] ?? 'NULL'));
    $this->logError("  type_of_goods_name: " . ($result['type_of_goods_name'] ?? 'NULL'));
    $this->logError("  transport_mode_id: " . ($result['transport_mode_id'] ?? 'NULL'));
    $this->logError("  transport_mode_name: " . ($result['transport_mode_name'] ?? 'NULL'));

    // ✅ Auto-match quotation if subscriber_id exists
    if (!empty($result['subscriber_id'])) {
      $quotSql = "SELECT q.id as quotation_id, q.quotation_ref, q.arsp,
                         q.kind_id, q.transport_mode_id, q.goods_type_id
                  FROM quotations_t q
                  WHERE q.client_id = ? 
                    AND q.display = 'Y'
                    AND q.kind_id = ?
                    AND q.transport_mode_id = ?
                    AND q.goods_type_id = ?
                  ORDER BY q.quotation_date DESC
                  LIMIT 1";

      $quotation = $this->db->customQuery($quotSql, [
        $result['subscriber_id'],
        $result['kind_id'],
        $result['transport_mode_id'],
        $result['goods_type_id']
      ]);

      // Fallback: Try with just kind and transport mode
      if (empty($quotation)) {
        $quotSql = "SELECT q.id as quotation_id, q.quotation_ref, q.arsp
                    FROM quotations_t q
                    WHERE q.client_id = ? 
                      AND q.display = 'Y'
                      AND q.kind_id = ?
                      AND q.transport_mode_id = ?
                    ORDER BY q.quotation_date DESC
                    LIMIT 1";

        $quotation = $this->db->customQuery($quotSql, [
          $result['subscriber_id'],
          $result['kind_id'],
          $result['transport_mode_id']
        ]);
      }

      // Final fallback: Try with just kind
      if (empty($quotation)) {
        $quotSql = "SELECT q.id as quotation_id, q.quotation_ref, q.arsp
                    FROM quotations_t q
                    WHERE q.client_id = ? 
                      AND q.display = 'Y'
                      AND q.kind_id = ?
                    ORDER BY q.quotation_date DESC
                    LIMIT 1";

        $quotation = $this->db->customQuery($quotSql, [
          $result['subscriber_id'],
          $result['kind_id']
        ]);
      }

      if (!empty($quotation) && isset($quotation[0])) {
        $result['quotation_id'] = $quotation[0]['quotation_id'];
        $result['quotation_ref'] = $quotation[0]['quotation_ref'];
        $result['arsp'] = $quotation[0]['arsp'];
      }
    }

    echo json_encode(['success' => true, 'data' => $result]);

  } catch (Exception $e) {
    $this->logError("Error in getMCADetails: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading MCA details', 'data' => null]);
  }
}

  public function getBanks($clientId = null)
  {
    if ($clientId === null) {
      $clientId = (int)($_GET['client_id'] ?? 0);
    }

    if ($clientId <= 0) {
      return [];
    }

    $sql = "SELECT ibm.id,
                   ibm.invoice_bank_name,
                   ibm.invoice_bank_account_name,
                   ibm.invoice_bank_account_number,
                   ibm.invoice_bank_swift,
                   ibm.invoice_bank_address
            FROM client_bank_mapping_t cbm
            INNER JOIN invoice_bank_master_t ibm ON cbm.bank_id = ibm.id
            WHERE cbm.client_id = ? AND ibm.display = 'Y'
            ORDER BY cbm.id ASC";

    return $this->db->customQuery($sql, [$clientId]) ?: [];
  }

private function generateNextInvoiceRef($clientId)
{
  try {
    $clientResult = $this->db->customQuery("SELECT short_name FROM clients_t WHERE id = ? LIMIT 1", [$clientId]);
    if (empty($clientResult))
      throw new Exception("Client not found");

    $shortName = strtoupper($clientResult[0]['short_name']);
    $year = date('Y');

    $result = $this->db->customQuery(
      "SELECT invoice_ref FROM export_invoices_t WHERE client_id = ? AND invoice_ref LIKE ? ORDER BY id DESC LIMIT 1",
      [$clientId, "$year-$shortName-EXP-%"]
    );

    $nextNumber = 1;
    if (!empty($result)) {
      preg_match('/(\d{4})$/i', $result[0]['invoice_ref'], $matches);
      $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
    }

    // Format: 2025-CLIENTNAME-EXP-0001
    return sprintf('%s-%s-EXP-%04d', $year, $shortName, $nextNumber);
    
  } catch (Exception $e) {
    $this->logError("Error generating next export invoice ref: " . $e->getMessage());
    return sprintf('%s-XXX-EXP-0001', date('Y'));
  }
}
private function getNextInvoiceRefForClient()
{
  $clientId = (int)($_GET['client_id'] ?? 0);
  
  if ($clientId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
    return;
  }
  
  echo json_encode([
    'success' => true, 
    'invoice_ref' => $this->generateNextInvoiceRef($clientId)
  ]);
}


private function numberToFrenchWords($number, $currency = 'USD')
{
  $number = abs($number);
  
  // Separate integer and decimal parts
  $integerPart = floor($number);
  $decimalPart = round(($number - $integerPart) * 100);
  
  // French number words
  $units = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF'];
  $teens = ['DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'];
  $tens = ['', 'DIX', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE', 'QUATRE-VINGT', 'QUATRE-VINGT'];
  
  // Helper function to convert numbers under 1000
  $convertUnder1000 = function($n) use ($units, $teens, $tens) {
    if ($n == 0) return '';
    
    $result = '';
    
    // Hundreds
    if ($n >= 100) {
      $hundreds = floor($n / 100);
      if ($hundreds == 1) {
        $result .= 'CENT';
      } else {
        $result .= $units[$hundreds] . ' CENT';
        // Add 'S' for plural hundreds only if nothing follows
        if ($n % 100 == 0) {
          $result .= 'S';
        }
      }
      $n = $n % 100;
      if ($n > 0) $result .= ' ';
    }
    
    // Tens and units
    if ($n >= 10) {
      $tensDigit = floor($n / 10);
      $unitsDigit = $n % 10;
      
      if ($n >= 10 && $n <= 19) {
        $result .= $teens[$n - 10];
      } elseif ($tensDigit == 7) {
        // 70-79: SOIXANTE-DIX, SOIXANTE ET ONZE, etc.
        if ($unitsDigit == 0) {
          $result .= 'SOIXANTE-DIX';
        } elseif ($unitsDigit == 1) {
          $result .= 'SOIXANTE ET ONZE';
        } else {
          $result .= 'SOIXANTE-' . $teens[$unitsDigit];
        }
      } elseif ($tensDigit == 9) {
        // 90-99: QUATRE-VINGT-DIX, etc.
        if ($unitsDigit == 0) {
          $result .= 'QUATRE-VINGT-DIX';
        } else {
          $result .= 'QUATRE-VINGT-' . $teens[$unitsDigit];
        }
      } elseif ($tensDigit == 8) {
        // 80-89: QUATRE-VINGT, QUATRE-VINGT-UN, etc.
        if ($unitsDigit == 0) {
          $result .= 'QUATRE-VINGTS';
        } else {
          $result .= 'QUATRE-VINGT-' . $units[$unitsDigit];
        }
      } else {
        if ($unitsDigit == 0) {
          $result .= $tens[$tensDigit];
        } elseif ($unitsDigit == 1 && $tensDigit != 8) {
          $result .= $tens[$tensDigit] . ' ET UN';
        } else {
          $result .= $tens[$tensDigit] . '-' . $units[$unitsDigit];
        }
      }
    } elseif ($n > 0) {
      $result .= $units[$n];
    }
    
    return $result;
  };
  
  // Convert the integer part
  $integerWords = '';
  
  if ($integerPart == 0) {
    $integerWords = 'ZÉRO';
  } else {
    // Billions
    if ($integerPart >= 1000000000) {
      $billions = floor($integerPart / 1000000000);
      if ($billions == 1) {
        $integerWords .= 'UN MILLIARD';
      } else {
        $integerWords .= $convertUnder1000($billions) . ' MILLIARDS';
      }
      $integerPart = $integerPart % 1000000000;
      if ($integerPart > 0) $integerWords .= ' ';
    }
    
    // Millions
    if ($integerPart >= 1000000) {
      $millions = floor($integerPart / 1000000);
      if ($millions == 1) {
        $integerWords .= 'UN MILLION';
      } else {
        $integerWords .= $convertUnder1000($millions) . ' MILLIONS';
      }
      $integerPart = $integerPart % 1000000;
      if ($integerPart > 0) $integerWords .= ' ';
    }
    
    // Thousands
    if ($integerPart >= 1000) {
      $thousands = floor($integerPart / 1000);
      if ($thousands == 1) {
        $integerWords .= 'MILLE';
      } else {
        $integerWords .= $convertUnder1000($thousands) . ' MILLE';
      }
      $integerPart = $integerPart % 1000;
      if ($integerPart > 0) $integerWords .= ' ';
    }
    
    // Remainder under 1000
    if ($integerPart > 0) {
      $integerWords .= $convertUnder1000($integerPart);
    }
  }
  
  // Build the final result with currency
  $result = $integerWords;
  
  // Add decimal part if exists
  if ($decimalPart > 0) {
    $decimalWords = $convertUnder1000($decimalPart);
    $result .= ' VIRGULE ' . $decimalWords;
  }
  
  // Add currency name
  if ($currency == 'USD') {
    $result .= ' DOLLARS AMÉRICAINS';
  } elseif ($currency == 'CDF') {
    $result .= ' FRANCS CONGOLAIS';
  }
  
  return $result;
}
  // ========== EXPORT TO EXCEL - ALL DEBIT NOTES ==========
  private function exportAllDebitNotes()
  {
    while (ob_get_level()) {
      ob_end_clean();
    }

    try {
      $clientId = $_GET['client_id'] ?? null;
      $transportModeId = $_GET['transport_mode_id'] ?? null;
      $startDate = $_GET['start_date'] ?? null;
      $endDate = $_GET['end_date'] ?? null;

      $whereClauses = ["1=1"];
      $params = [];

      if (!empty($clientId)) {
        $whereClauses[] = "inv.client_id = ?";
        $params[] = $clientId;
      }

      if (!empty($transportModeId)) {
        $whereClauses[] = "inv.transport_mode_id = ?";
        $params[] = $transportModeId;
      }

      if (!empty($startDate)) {
        $whereClauses[] = "inv.invoice_date >= ?";
        $params[] = $startDate;
      }

      if (!empty($endDate)) {
        $whereClauses[] = "inv.invoice_date <= ?";
        $params[] = $endDate;
      }

      $whereSQL = implode(' AND ', $whereClauses);

      $sql = "SELECT 
              inv.*,
              c.short_name as client_name,
              l.license_number
            FROM export_invoices_t inv
            LEFT JOIN clients_t c ON inv.client_id = c.id
            LEFT JOIN licenses_t l ON inv.license_id = l.id
            WHERE {$whereSQL}
            ORDER BY inv.id DESC";

      $invoices = $this->db->customQuery($sql, $params);

      if (empty($invoices)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No invoices found']);
        exit;
      }

      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'PhpSpreadsheet not found']);
        exit;
      }

      require_once $vendorPath;

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Debit Notes');

      $headers = [
        '#',
        'Notre Nº Ref #',
        'Lot Num.',
        'License Num.',
        'Client',
        'Encoded By',
        'Declaration Ref.',
        'Declaration Date',
        'Liquidation Ref.',
        'Liquidation Date',
        'Quittance Ref.',
        'Quittance Date',
        'FACTURE Nº',
        'INV. DATE',
        'Nombre de Trucks',
        'Dossier(s):',
        'Qty(Mt)',
        'LIQ AMT CDF',
        'Rate(CDF/USD)',
        'AVG Ton Per USD',
        'Ton Per USD',
        'LIQ AMT/USD',
        'RIE-Ton Per USD',
        'RIE-Amount',
        'RLS-Ton Per USD',
        'RLS-Amount',
        'FSR-Ton Per USD',
        'FSR-Amount',
        'OGREFREM-Ton Per USD',
        'OGREFREM-Amount',
        'LMC-Ton Per USD',
        'LMC-Amount',
        'Tax Voire-Ton Per USD',
        'Tax Voire-Amount',
        'CEEC',
        'FINANCE COST',
        'OCC',
        'CGEA',
        'DGDA Security Seals',
        'ASSAY FEE',
        'Customs Clearence FEE',
        'OTHER CHARGES / AUTRES FRAIS',
        'TVA/USD',
        'Total',
        'OPERATIONAL COSTS / COUT OPERATIONEL',
        'TVA/USD',
        'Total',
        'AGENCY FEE',
        'TVA/USD',
        'Total',
        'Total Invoice',
        'Container',
        'Feet',
        'Amount',
        'Status',
        'Supporting Doc'
      ];

      $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
      ];

      $col = 'A';
      foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension($col)->setWidth(15);
        $col++;
      }

      $sheet->getColumnDimension('B')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('M')->setWidth(25);
      $sheet->getColumnDimension('P')->setWidth(30);

      $rowNum = 2;
      $counter = 1;

      foreach ($invoices as $invoice) {
        $mcaSql = "SELECT eimd.*, e.mca_ref 
                   FROM export_invoice_mca_details_t eimd
                   LEFT JOIN exports_t e ON eimd.mca_id = e.id
                   WHERE eimd.export_invoice_id = ?
                   ORDER BY eimd.display_order ASC";
        $mcaDetails = $this->db->customQuery($mcaSql, [$invoice['id']]);

        if (empty($mcaDetails)) {
          continue;
        }

        $itemsSql = "SELECT * FROM export_invoice_items_t WHERE export_invoice_id = ? AND category_id = 1";
        $reimbursableItems = $this->db->customQuery($itemsSql, [$invoice['id']]);

        $totalWeight = 0;
        $totalLiqCDF = 0;
        $mcaRefs = [];
        $firstMCA = null;

        foreach ($mcaDetails as $idx => $mca) {
          if ($idx === 0)
            $firstMCA = $mca;

          $mcaRefs[] = $mca['mca_ref'] ?? '';
          $totalWeight += floatval($mca['weight'] ?? 0);

          $ceec = floatval($mca['ceec_amount'] ?? 0);
          $cgea = floatval($mca['cgea_amount'] ?? 0);
          $occ = floatval($mca['occ_amount'] ?? 0);
          $lmc = floatval($mca['lmc_amount'] ?? 0);
          $ogefrem = floatval($mca['ogefrem_amount'] ?? 0);

          $totalLiqCDF += ($ceec + $cgea + $occ + $lmc + $ogefrem);
        }

        $bccRate = floatval($firstMCA['bcc_rate'] ?? 2208.556);
        $liqAmtUSD = $bccRate > 0 ? $totalLiqCDF / $bccRate : 0;
        $tonPerUSD = $totalWeight > 0 ? $liqAmtUSD / $totalWeight : 0;

        $reimbursableSubtotal = 0;
        $reimbursableTVA = 0;
        $reimbursableTotal = 0;

        $ceecTotal = 0;
        $occTotal = 0;
        $cgeaTotal = 0;
        $lmcTotal = 0;
        $ogefremTotal = 0;

        foreach ($mcaDetails as $mca) {
          $ceecTotal += floatval($mca['ceec_amount'] ?? 0);
          $occTotal += floatval($mca['occ_amount'] ?? 0);
          $cgeaTotal += floatval($mca['cgea_amount'] ?? 0);
          $lmcTotal += floatval($mca['lmc_amount'] ?? 0);
          $ogefremTotal += floatval($mca['ogefrem_amount'] ?? 0);
        }

        $ceecUSD = $bccRate > 0 ? $ceecTotal / $bccRate : 0;
        $occUSD = $bccRate > 0 ? $occTotal / $bccRate : 0;
        $cgeaUSD = $bccRate > 0 ? $cgeaTotal / $bccRate : 0;
        $lmcUSD = $bccRate > 0 ? $lmcTotal / $bccRate : 0;
        $ogefremUSD = $bccRate > 0 ? $ogefremTotal / $bccRate : 0;

        $lmcTonPerUSD = $totalWeight > 0 ? $lmcUSD / $totalWeight : 0;
        $ogefremTonPerUSD = $totalWeight > 0 ? $ogefremUSD / $totalWeight : 0;

        foreach ($reimbursableItems as $item) {
          $reimbursableSubtotal += floatval($item['subtotal_usd'] ?? 0);
          $reimbursableTVA += floatval($item['tva_usd'] ?? 0);
          $reimbursableTotal += floatval($item['total_usd'] ?? 0);
        }

        $totalInvoice = $reimbursableTotal;

        $validated = intval($invoice['validated'] ?? 0);
        $status = 'Not Validated';
        if ($validated === 1)
          $status = 'Validated';
        if ($validated === 2)
          $status = 'DGI Verified';

        $encodedBy = '';
        if (!empty($invoice['created_by'])) {
          $userSql = "SELECT username FROM users WHERE id = ? LIMIT 1";
          $userResult = $this->db->customQuery($userSql, [$invoice['created_by']]);
          if (!empty($userResult)) {
            $encodedBy = $userResult[0]['username'] ?? '';
          }
        }

        $rowData = [
          $counter++,
          $firstMCA['mca_ref'] ?? '',
          $firstMCA['lot_number'] ?? '',
          $invoice['license_number'] ?? '',
          $invoice['client_name'] ?? '',
          $encodedBy,
          $firstMCA['declaration_no'] ?? '',
          !empty($firstMCA['declaration_date']) ? date('Y-m-d', strtotime($firstMCA['declaration_date'])) : '',
          $firstMCA['liquidation_no'] ?? '',
          !empty($firstMCA['liquidation_date']) ? date('Y-m-d', strtotime($firstMCA['liquidation_date'])) : '',
          $firstMCA['quittance_no'] ?? '',
          !empty($firstMCA['quittance_date']) ? date('Y-m-d', strtotime($firstMCA['quittance_date'])) : '',
          $invoice['invoice_ref'] ?? '',
          !empty($invoice['invoice_date']) ? date('Y-m-d', strtotime($invoice['invoice_date'])) : '',
          count($mcaDetails),
          implode(', ', $mcaRefs),
          $totalWeight,
          $totalLiqCDF,
          $bccRate,
          0,
          $tonPerUSD,
          $liqAmtUSD,
          0,
          0,
          0,
          0,
          0,
          0,
          $ogefremTonPerUSD,
          $ogefremUSD,
          $lmcTonPerUSD,
          $lmcUSD,
          0,
          0,
          $ceecUSD,
          0,
          $occUSD,
          $cgeaUSD,
          0,
          0,
          $reimbursableSubtotal,
          0,
          $reimbursableTVA,
          $reimbursableTotal,
          0,
          0,
          0,
          0,
          0,
          0,
          $totalInvoice,
          $firstMCA['container'] ?? '',
          0,
          0,
          $status,
          ''
        ];

        $col = 'A';
        foreach ($rowData as $value) {
          $sheet->setCellValue($col . $rowNum, $value);

          if (in_array($col, ['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'BA', 'BB'])) {
            $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
          }

          $col++;
        }

        $sheet->getStyle('A' . $rowNum . ':' . 'BD' . $rowNum)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $rowNum++;
      }

      for ($colIndex = 1; $colIndex <= 56; $colIndex++) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
      }

      $sheet->freezePane('A2');

      $filename = 'Debit_Notes_Export_' . date('Y-m-d_His') . '.xlsx';

      if (ob_get_length()) {
        ob_end_clean();
      }

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      header('Cache-Control: max-age=1');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Pragma: public');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);

      exit;

    } catch (\Exception $e) {
      $this->logError('Error in exportAllDebitNotes: ' . $e->getMessage());

      while (ob_get_level()) {
        ob_end_clean();
      }

      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  // ========== EXPORT TO EXCEL - ALL INVOICES ==========
  private function exportAllInvoices()
  {
    while (ob_get_level()) {
      ob_end_clean();
    }

    try {
      $clientId = $_GET['client_id'] ?? null;
      $transportModeId = $_GET['transport_mode_id'] ?? null;
      $startDate = $_GET['start_date'] ?? null;
      $endDate = $_GET['end_date'] ?? null;

      $whereClauses = ["1=1"];
      $params = [];

      if (!empty($clientId)) {
        $whereClauses[] = "inv.client_id = ?";
        $params[] = $clientId;
      }

      if (!empty($transportModeId)) {
        $whereClauses[] = "inv.transport_mode_id = ?";
        $params[] = $transportModeId;
      }

      if (!empty($startDate)) {
        $whereClauses[] = "inv.invoice_date >= ?";
        $params[] = $startDate;
      }

      if (!empty($endDate)) {
        $whereClauses[] = "inv.invoice_date <= ?";
        $params[] = $endDate;
      }

      $whereSQL = implode(' AND ', $whereClauses);

      $sql = "SELECT 
              inv.*,
              c.short_name as client_name,
              l.license_number
            FROM export_invoices_t inv
            LEFT JOIN clients_t c ON inv.client_id = c.id
            LEFT JOIN licenses_t l ON inv.license_id = l.id
            WHERE {$whereSQL}
            ORDER BY inv.id DESC";

      $invoices = $this->db->customQuery($sql, $params);

      if (empty($invoices)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No invoices found']);
        exit;
      }

      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'PhpSpreadsheet not found']);
        exit;
      }

      require_once $vendorPath;

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Invoices');

      $headers = [
        '#',
        'Notre Nº Ref #',
        'Lot Num.',
        'License Num.',
        'Client',
        'Encoded By',
        'Declaration Ref.',
        'Declaration Date',
        'Liquidation Ref.',
        'Liquidation Date',
        'Quittance Ref.',
        'Quittance Date',
        'FACTURE Nº',
        'INV. DATE',
        'Nombre de Trucks',
        'Dossier(s):',
        'Qty(Mt)',
        'LIQ AMT CDF',
        'Rate(CDF/USD)',
        'AVG Ton Per USD',
        'Ton Per USD',
        'LIQ AMT/USD',
        'RIE-Ton Per USD',
        'RIE-Amount',
        'RLS-Ton Per USD',
        'RLS-Amount',
        'FSR-Ton Per USD',
        'FSR-Amount',
        'OGREFREM-Ton Per USD',
        'OGREFREM-Amount',
        'LMC-Ton Per USD',
        'LMC-Amount',
        'Tax Voire-Ton Per USD',
        'Tax Voire-Amount',
        'CEEC',
        'FINANCE COST',
        'OCC',
        'CGEA',
        'DGDA Security Seals',
        'ASSAY FEE',
        'Customs Clearence FEE',
        'OTHER CHARGES / AUTRES FRAIS',
        'TVA/USD',
        'Total',
        'OPERATIONAL COSTS / COUT OPERATIONEL',
        'TVA/USD',
        'Total',
        'AGENCY FEE',
        'TVA/USD',
        'Total',
        'Total Invoice',
        'Container',
        'Feet',
        'Amount',
        'Status'
      ];

      $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
      ];

      $col = 'A';
      foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension($col)->setWidth(15);
        $col++;
      }

      $sheet->getColumnDimension('B')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('M')->setWidth(25);
      $sheet->getColumnDimension('P')->setWidth(30);

      $rowNum = 2;
      $counter = 1;

      foreach ($invoices as $invoice) {
        $mcaSql = "SELECT eimd.*, e.mca_ref 
                   FROM export_invoice_mca_details_t eimd
                   LEFT JOIN exports_t e ON eimd.mca_id = e.id
                   WHERE eimd.export_invoice_id = ?
                   ORDER BY eimd.display_order ASC";
        $mcaDetails = $this->db->customQuery($mcaSql, [$invoice['id']]);

        if (empty($mcaDetails)) {
          continue;
        }

        $itemsSql = "SELECT * FROM export_invoice_items_t 
                     WHERE export_invoice_id = ? 
                     AND (category_id IS NULL OR category_id != 1)";
        $operationalItems = $this->db->customQuery($itemsSql, [$invoice['id']]);

        $totalWeight = 0;
        $totalLiqCDF = 0;
        $mcaRefs = [];
        $firstMCA = null;

        foreach ($mcaDetails as $idx => $mca) {
          if ($idx === 0)
            $firstMCA = $mca;

          $mcaRefs[] = $mca['mca_ref'] ?? '';
          $totalWeight += floatval($mca['weight'] ?? 0);

          $ceec = floatval($mca['ceec_amount'] ?? 0);
          $cgea = floatval($mca['cgea_amount'] ?? 0);
          $occ = floatval($mca['occ_amount'] ?? 0);
          $lmc = floatval($mca['lmc_amount'] ?? 0);
          $ogefrem = floatval($mca['ogefrem_amount'] ?? 0);

          $totalLiqCDF += ($ceec + $cgea + $occ + $lmc + $ogefrem);
        }

        $bccRate = floatval($firstMCA['bcc_rate'] ?? 2208.556);
        $liqAmtUSD = $bccRate > 0 ? $totalLiqCDF / $bccRate : 0;
        $tonPerUSD = $totalWeight > 0 ? $liqAmtUSD / $totalWeight : 0;

        $operationalSubtotal = 0;
        $operationalTVA = 0;
        $operationalTotal = 0;

        $ceecTotal = 0;
        $occTotal = 0;
        $cgeaTotal = 0;
        $lmcTotal = 0;
        $ogefremTotal = 0;

        foreach ($mcaDetails as $mca) {
          $ceecTotal += floatval($mca['ceec_amount'] ?? 0);
          $occTotal += floatval($mca['occ_amount'] ?? 0);
          $cgeaTotal += floatval($mca['cgea_amount'] ?? 0);
          $lmcTotal += floatval($mca['lmc_amount'] ?? 0);
          $ogefremTotal += floatval($mca['ogefrem_amount'] ?? 0);
        }

        $ceecUSD = $bccRate > 0 ? $ceecTotal / $bccRate : 0;
        $occUSD = $bccRate > 0 ? $occTotal / $bccRate : 0;
        $cgeaUSD = $bccRate > 0 ? $cgeaTotal / $bccRate : 0;
        $lmcUSD = $bccRate > 0 ? $lmcTotal / $bccRate : 0;
        $ogefremUSD = $bccRate > 0 ? $ogefremTotal / $bccRate : 0;

        $lmcTonPerUSD = $totalWeight > 0 ? $lmcUSD / $totalWeight : 0;
        $ogefremTonPerUSD = $totalWeight > 0 ? $ogefremUSD / $totalWeight : 0;

        foreach ($operationalItems as $item) {
          $operationalSubtotal += floatval($item['subtotal_usd'] ?? 0);
          $operationalTVA += floatval($item['tva_usd'] ?? 0);
          $operationalTotal += floatval($item['total_usd'] ?? 0);
        }

        $totalInvoice = $operationalTotal;

        $validated = intval($invoice['validated'] ?? 0);
        $status = 'Not Validated';
        if ($validated === 1)
          $status = 'Validated';
        if ($validated === 2)
          $status = 'DGI Verified';

        $encodedBy = '';
        if (!empty($invoice['created_by'])) {
          $userSql = "SELECT username FROM users WHERE id = ? LIMIT 1";
          $userResult = $this->db->customQuery($userSql, [$invoice['created_by']]);
          if (!empty($userResult)) {
            $encodedBy = $userResult[0]['username'] ?? '';
          }
        }

        $rowData = [
          $counter++,
          $firstMCA['mca_ref'] ?? '',
          $firstMCA['lot_number'] ?? '',
          $invoice['license_number'] ?? '',
          $invoice['client_name'] ?? '',
          $encodedBy,
          $firstMCA['declaration_no'] ?? '',
          !empty($firstMCA['declaration_date']) ? date('Y-m-d', strtotime($firstMCA['declaration_date'])) : '',
          $firstMCA['liquidation_no'] ?? '',
          !empty($firstMCA['liquidation_date']) ? date('Y-m-d', strtotime($firstMCA['liquidation_date'])) : '',
          $firstMCA['quittance_no'] ?? '',
          !empty($firstMCA['quittance_date']) ? date('Y-m-d', strtotime($firstMCA['quittance_date'])) : '',
          $invoice['invoice_ref'] ?? '',
          !empty($invoice['invoice_date']) ? date('Y-m-d', strtotime($invoice['invoice_date'])) : '',
          count($mcaDetails),
          implode(', ', $mcaRefs),
          $totalWeight,
          $totalLiqCDF,
          $bccRate,
          0,
          $tonPerUSD,
          $liqAmtUSD,
          0,
          0,
          0,
          0,
          0,
          0,
          $ogefremTonPerUSD,
          $ogefremUSD,
          $lmcTonPerUSD,
          $lmcUSD,
          0,
          0,
          $ceecUSD,
          0,
          $occUSD,
          $cgeaUSD,
          0,
          0,
          0,
          0,
          0,
          0,
          $operationalSubtotal,
          $operationalTVA,
          $operationalTotal,
          0,
          0,
          0,
          $totalInvoice,
          $firstMCA['container'] ?? '',
          0,
          0,
          $status
        ];

        $col = 'A';
        foreach ($rowData as $value) {
          $sheet->setCellValue($col . $rowNum, $value);

          if (in_array($col, ['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'BA', 'BB', 'BC'])) {
            $sheet->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
          }

          $col++;
        }

        $sheet->getStyle('A' . $rowNum . ':' . 'BC' . $rowNum)->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $rowNum++;
      }

      for ($colIndex = 1; $colIndex <= 55; $colIndex++) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
      }

      $sheet->freezePane('A2');

      $filename = 'Invoices_Export_' . date('Y-m-d_His') . '.xlsx';

      if (ob_get_length()) {
        ob_end_clean();
      }

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      header('Cache-Control: max-age=1');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Pragma: public');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');

      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);

      exit;

    } catch (\Exception $e) {
      $this->logError('Error in exportAllInvoices: ' . $e->getMessage());

      while (ob_get_level()) {
        ob_end_clean();
      }

      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  // ========== EXPORT SINGLE INVOICE TO EXCEL ==========
  private function exportInvoice()
  {
    $invoiceId = (int)($_GET['id'] ?? 0);
    if ($invoiceId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
      return;
    }

    try {
      while (ob_get_level()) {
        ob_end_clean();
      }

      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath))
        throw new Exception('PhpSpreadsheet not found');
      require_once $vendorPath;

      $result = $this->db->customQuery(
        "SELECT inv.*, c.short_name as client_name, c.company_name
         FROM export_invoices_t inv 
         LEFT JOIN clients_t c ON inv.client_id = c.id 
         WHERE inv.id = ?",
        [$invoiceId]
      );

      if (empty($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $data = $result[0];

      $mcaDataArray = [];
      $mcaDetailsSql = "SELECT eimd.*, 
                              e.mca_ref, e.loading_date, e.dgda_in_date as border_arrival_date,
                              e.liquidation_date as end_of_formalities, e.dgda_out_date as exit_drc_date,
                              e.buyer
                        FROM export_invoice_mca_details_t eimd
                        LEFT JOIN exports_t e ON eimd.mca_id = e.id
                        WHERE eimd.export_invoice_id = ? 
                        ORDER BY eimd.display_order ASC";
      $mcaDetailsResults = $this->db->customQuery($mcaDetailsSql, [$invoiceId]);

      if (!empty($mcaDetailsResults)) {
        $mcaDataArray = $mcaDetailsResults;
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      $sheetTitle = preg_replace('/[^a-zA-Z0-9 _-]/', '', $data['invoice_ref'] ?? 'Export Invoice');
      $sheetTitle = substr($sheetTitle, 0, 31);
      $sheet->setTitle($sheetTitle . ' DETAILS');

      $row = 3;

      $headers = [
        '#',
        'MCA B/REF',
        'Destination',
        'Transporter',
        'Horse',
        'Trailer 1',
        'Trailer 2',
        'Lot. No.',
        'Nbr of Bdles',
        'Qty(Mt)',
        'Loading Date',
        'Border Arrival Date',
        'End of formalities',
        'Exit DRC Date',
        'Status',
        'Duty in CDF',
        'RIE in CDF',
        'DDE in CDF',
        'RLS in CDF',
        'FSR in CDF',
        'Exchange Rate',
        'RIE in USD',
        'DDE in USD',
        'RLS in USD',
        'FSR in USD',
        'Duty in USD',
        'Per MT Rate'
      ];

      $sheet->fromArray($headers, NULL, 'A' . $row);
      $sheet->getStyle('A' . $row . ':AA' . $row)->getFont()->setBold(true);
      $sheet->getStyle('A' . $row . ':AA' . $row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD9D9D9');
      $row++;

      $mcaNum = 1;
      $destination = strtoupper($data['client_name'] ?? 'DESTINATION');

      foreach ($mcaDataArray as $detail) {
        $weight = (float)($detail['weight'] ?? 0);
        $bccRate = (float)($detail['bcc_rate'] ?? $this->defaultBccRate);
        $ceec = (float)($detail['ceec_amount'] ?? 0);
        $cgea = (float)($detail['cgea_amount'] ?? 0);
        $occ = (float)($detail['occ_amount'] ?? 0);
        $lmc = (float)($detail['lmc_amount'] ?? 0);
        $ogefrem = (float)($detail['ogefrem_amount'] ?? 0);

        $ddeCDF = $ceec + $cgea + $occ + $lmc + $ogefrem;
        $rieCDF = 0;
        $rlsCDF = 0;
        $fsrCDF = 0;
        $totalDutyCDF = $ddeCDF + $rieCDF + $rlsCDF + $fsrCDF;

        $rieUSD = $bccRate > 0 ? $rieCDF / $bccRate : 0;
        $ddeUSD = $bccRate > 0 ? $ddeCDF / $bccRate : 0;
        $rlsUSD = $bccRate > 0 ? $rlsCDF / $bccRate : 0;
        $fsrUSD = $bccRate > 0 ? $fsrCDF / $bccRate : 0;
        $dutyUSD = $bccRate > 0 ? $totalDutyCDF / $bccRate : 0;
        $perMTRate = $weight > 0 ? $dutyUSD / $weight : 0;

        $rowData = [
          $mcaNum++,
          $detail['mca_ref'] ?? '',
          $destination,
          $detail['buyer'] ?? '',
          $detail['horse'] ?? 'N/A',
          $detail['trailer_1'] ?? 'N/A',
          $detail['trailer_2'] ?? 'N/A',
          $detail['lot_number'] ?? '',
          '',
          $weight,
          !empty($detail['loading_date']) ? date('Y-m-d', strtotime($detail['loading_date'])) : '',
          !empty($detail['border_arrival_date']) ? date('Y-m-d', strtotime($detail['border_arrival_date'])) : '',
          !empty($detail['end_of_formalities']) ? date('Y-m-d', strtotime($detail['end_of_formalities'])) : '',
          !empty($detail['exit_drc_date']) ? date('Y-m-d', strtotime($detail['exit_drc_date'])) : '',
          'Cleared',
          $totalDutyCDF,
          $rieCDF,
          $ddeCDF,
          $rlsCDF,
          $fsrCDF,
          $bccRate,
          $rieUSD,
          $ddeUSD,
          $rlsUSD,
          $fsrUSD,
          $dutyUSD,
          $perMTRate
        ];

        $sheet->fromArray($rowData, NULL, 'A' . $row);
        $row++;
      }

      $highestColumn = $sheet->getHighestColumn();
      $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

      for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
      }

      $sheet->freezePane('A4');

      $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $data['invoice_ref'] ?? 'Invoice') . '_' . date('d_m_Y_H_i_s') . '.xlsx';

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
      $this->logError("Export error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
      exit;
    }
  }

  // ========== PDF GENERATION - COMMON HELPER METHODS ==========

  /**
   * Get common CSS styles used across all PDF pages
   */
  private function getPdfCommonCSS()
  {
    return '
      body { font-family: Arial, sans-serif; font-size: 8pt; margin: 0; padding: 8px; line-height: 1.2; }
      table { border-collapse: collapse; }
      
      .header-table { width: 100%; margin-bottom: 8mm; }
      .header-table td { border: none; padding: 0; vertical-align: top; }
      
      .doc-title { 
        border: 1px solid #000; 
        padding: 6px 0; 
        text-align: center; 
        font-weight: bold; 
        font-size: 14pt; 
        margin: 5mm 0 5mm 0;
        width: 45%;
      }
      
      .boxes-container { width: 100%; margin-bottom: 5mm; }
      .boxes-container td { vertical-align: top; border: none; padding: 0; }
      
      .client-box { width: 100%; border: 1px solid #000; }
      .client-box td { border: none; padding: 3px 5px; font-size: 6.5pt; line-height: 1.3; }
      .client-header { font-weight: bold; background: #d3d3d3; padding: 4px 6px; border-bottom: 1px solid #000; }
      
      .invoice-box { width: 100%; border: 1px solid #000; }
      .invoice-box td { border: 1px solid #000; padding: 4px 6px; font-size: 7.5pt; line-height: 1.3; }
      .invoice-label { font-weight: bold; width: 45%; }
      
      .section-header { 
        background: #000; color: #fff; padding: 5px 8px; font-weight: bold; 
        font-size: 8pt; text-align: center; margin-top: 3mm; margin-bottom: 2mm; 
      }
      
      .items-table { border: 1px solid #000; width: 100%; margin-top: 2mm; font-size: 6.5pt; }
      .items-table th { background: #e0e0e0; border: 1px solid #000; padding: 4px 3px; font-weight: bold; text-align: center; }
      .items-table td { border-left: 1px solid #000; border-right: 1px solid #000; padding: 3px 4px; }

      .subtotal-row { background: #d3d3d3; font-weight: bold; }
      .subtotal-row td { border: 1px solid #000; }
      .total-row { background: #000; color: #fff; font-weight: bold; }
      .total-row td { border: 1px solid #000; }
      .cdf-row { font-weight: bold; background: #f0f0f0; }
      .cdf-row td { border: 1px solid #000; }
      
      .bank-section { margin-top: 5mm; text-align: center; font-size: 7pt; font-weight: bold; }
      .thank-you { border: 1px solid #000; text-align: center; padding: 5px; margin-top: 4mm; font-size: 7pt; }
      
      .r { text-align: right; }
      .c { text-align: center; }
      .l { text-align: left; }
    ';
  }

  /**
   * Get landscape-specific CSS styles
   */
  private function getPdfLandscapeCSS()
  {
    return '
      .landscape-table { 
        font-size: 7pt; 
        width: 100%;
        border: 1px solid #000;
      }
      .landscape-table th { 
        background: #d3d3d3; 
        color: #000; 
        border: 1px solid #000; 
        padding: 5px 3px; 
        font-weight: bold; 
        text-align: center; 
        font-size: 7pt;
      }
      .landscape-table td { 
        border: 1px solid #000; 
        padding: 4px 3px; 
        font-size: 6.5pt;
      }
    ';
  }

  /**
   * Get formatted invoice data from database
   */
  private function getInvoiceDataForPDF($invoiceId)
  {
    $invoiceSql = "SELECT inv.*, c.short_name, c.company_name, c.address, c.rccm_number, 
                            c.nif_number, c.id_nat_number, c.import_export_number, l.license_number,
                            k.kind_name, tg.goods_type as goods_type_name, tm.transport_mode_name,
                            ei.qrcode, ei.codedefdgi
                     FROM export_invoices_t inv
                     LEFT JOIN clients_t c ON inv.client_id = c.id
                     LEFT JOIN licenses_t l ON inv.license_id = l.id
                     LEFT JOIN kind_master_t k ON inv.kind_id = k.id
                     LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                     LEFT JOIN transport_mode_master_t tm ON inv.transport_mode_id = tm.id
                     LEFT JOIN emcf_invoice ei ON inv.id = ei.invoice_id AND ei.inv_type = 'EXPORT'
                     WHERE inv.id = ? LIMIT 1";

    $invoiceResult = $this->db->customQuery($invoiceSql, [$invoiceId]);
    if (empty($invoiceResult)) {
      throw new Exception('Invoice not found');
    }

    return $invoiceResult[0];
  }

  /**
   * Get and group invoice items by category
   */
 /**
   * Get and group invoice items by category (EXCLUDING items with unit value 0 or empty)
   */
  private function getGroupedInvoiceItems($invoiceId)
  {
    $items = $this->getInvoiceItems($invoiceId);

    $groupedByCategory = [];
    foreach ($items as $item) {
      // ✅ SKIP ITEMS WITH UNIT VALUE 0 OR EMPTY BEFORE GROUPING
      $unitText = $item['unit_text'] ?? 'Unit';
      if ($unitText === '0' || empty($unitText) || $unitText === 'Unit') {
        continue; // Skip this item entirely
      }

      $catId = (int)($item['category_id'] ?? 0);
      if (!isset($groupedByCategory[$catId])) {
        $groupedByCategory[$catId] = [
          'category_id' => $catId,
          'category_header' => strtoupper($item['category_header'] ?? 'UNCATEGORIZED'),
          'display_order' => (int)($item['display_order'] ?? 999),
          'items' => [],
          'subtotal_usd' => 0,
          'tva_usd' => 0,
          'total_usd' => 0,
        ];
      }
      $groupedByCategory[$catId]['items'][] = $item;
      $groupedByCategory[$catId]['subtotal_usd'] += (float)$item['subtotal_usd'];
      $groupedByCategory[$catId]['tva_usd'] += (float)$item['tva_usd'];
      $groupedByCategory[$catId]['total_usd'] += (float)$item['total_usd'];
    }

    uasort($groupedByCategory, fn($a, $b) => $a['display_order'] <=> $b['display_order']);
    return array_values($groupedByCategory);
  }

  /**
   * Separate items into reimbursable and operational categories
   */
  private function categorizeItems($groupedItems)
  {
    $reimbursableItems = [];
    $operationalItems = [];

    foreach ($groupedItems as $category) {
      $categoryName = strtoupper($category['category_header']);
      
      if (
        stripos($categoryName, 'REIMBURSABLE') !== false ||
        stripos($categoryName, 'CUSTOMS') !== false ||
        stripos($categoryName, 'CLEARANCE') !== false ||
        stripos($categoryName, 'CHARGES') !== false ||
        stripos($categoryName, 'FRAIS') !== false ||
        $category['category_id'] == 1
      ) {
        $reimbursableItems[] = $category;
      } else {
        $operationalItems[] = $category;
      }
    }

    return [$reimbursableItems, $operationalItems];
  }

  /**
   * Get MCA details for invoice
   */
  /**
 * Get MCA details for invoice - pulling dates directly from exports_t
 */
private function getMCADataForInvoice($invoiceId)
{
  $mcaDetailsSql = "SELECT 
                      eimd.*,
                      e.mca_ref,
                      e.loading_date,
                      e.dgda_in_date as border_arrival_date,
                      e.exit_drc_date,
                      e.liquidation_date,
                      e.buyer,
                      e.declaration_reference,
                      e.liquidation_reference,
                      e.quittance_reference,
                      e.quittance_date
                    FROM export_invoice_mca_details_t eimd
                    LEFT JOIN exports_t e ON eimd.mca_id = e.id
                    WHERE eimd.export_invoice_id = ? 
                    ORDER BY eimd.display_order DESC";  // ✅ REVERSED
  
  return $this->db->customQuery($mcaDetailsSql, [$invoiceId]) ?: [];
}

  /**
   * Get user signature path and username
   */
  private function getUserSignatureData()
  {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $signaturePath = null;
    $username = '';
    
    if ($userId > 0) {
      $userResult = $this->db->customQuery("SELECT signature_image, username FROM users_t WHERE id = ? LIMIT 1", [$userId]);
      if (!empty($userResult)) {
        if (!empty($userResult[0]['signature_image'])) {
          $tempSignaturePath = __DIR__ . '/../../../public/uploads/signatures/' . $userResult[0]['signature_image'];
          if (file_exists($tempSignaturePath)) {
            $signaturePath = $tempSignaturePath;
          }
        }
        $username = $userResult[0]['username'] ?? '';
      }
    }

    return [$signaturePath, $username];
  }

  /**
   * Generate logo HTML
   */
  private function getLogoHTML()
  {
    return (!empty($this->logoPath) && file_exists($this->logoPath))
      ? '<img src="' . $this->logoPath . '" style="max-width:180px;max-height:60px;">'
      : '';
  }

  /**
   * Generate signature HTML
   */
  private function getSignatureHTML($signaturePath)
  {
    return (!empty($signaturePath) && file_exists($signaturePath))
      ? '<img src="' . $signaturePath . '" style="max-height:50px; max-width:150px;">'
      : '';
  }

  /**
   * Format invoice header data
   */
  private function formatInvoiceHeaderData($invoice)
  {
    return [
      'invoice_ref' => htmlspecialchars($invoice['invoice_ref'] ?? 'N/A', ENT_QUOTES, 'UTF-8'),
      'invoice_date' => !empty($invoice['invoice_date']) ? date('d-M-y', strtotime($invoice['invoice_date'])) : date('d-M-y'),
      'client_company' => htmlspecialchars($invoice['company_name'] ?? '', ENT_QUOTES, 'UTF-8'),
      'client_address' => htmlspecialchars($invoice['address'] ?? '', ENT_QUOTES, 'UTF-8'),
      'client_rccm' => htmlspecialchars($invoice['rccm_number'] ?? '', ENT_QUOTES, 'UTF-8'),
      'client_nif' => htmlspecialchars($invoice['nif_number'] ?? '', ENT_QUOTES, 'UTF-8'),
      'client_idnat' => htmlspecialchars($invoice['id_nat_number'] ?? '', ENT_QUOTES, 'UTF-8'),
      'client_import_export' => htmlspecialchars($invoice['import_export_number'] ?? '', ENT_QUOTES, 'UTF-8'),
    ];
  }

  /**
   * Format MCA references for display
   */
private function formatMCAReferences($mcaDataArray)
{
  if (empty($mcaDataArray)) {
    return ['', 0];
  }

  $mcaRefs = [];
  foreach ($mcaDataArray as $mca) {
    if (!empty($mca['mca_ref'])) {
      $mcaRefs[] = htmlspecialchars($mca['mca_ref'], ENT_QUOTES, 'UTF-8');
    }
  }

  $count = count($mcaRefs);
  
  if ($count === 0) {
    return ['', 0];
  } elseif ($count === 1) {
    return [$mcaRefs[0], 1];
  }

  // Extract numbers and sort
  $mcaData = [];
  foreach ($mcaRefs as $ref) {
    // Extract number from MCA reference (e.g., "MCA-001" -> 1, "2025-XXX-MCA-005" -> 5)
    if (preg_match('/(\d+)(?!.*\d)/', $ref, $matches)) {
      $mcaData[] = [
        'ref' => $ref,
        'num' => (int)$matches[1]
      ];
    }
  }

  // Sort by number
  usort($mcaData, function($a, $b) {
    return $a['num'] - $b['num'];
  });

  // Group consecutive sequences
  $ranges = [];
  $rangeStart = $mcaData[0];
  $rangeEnd = $mcaData[0];

  for ($i = 1; $i < count($mcaData); $i++) {
    $current = $mcaData[$i];
    $previous = $mcaData[$i - 1];

    // Check if consecutive
    if ($current['num'] == $previous['num'] + 1) {
      // Continue current range
      $rangeEnd = $current;
    } else {
      // Gap found - save current range and start new one
      if ($rangeStart['num'] == $rangeEnd['num']) {
        $ranges[] = $rangeStart['ref'];
      } else {
        $ranges[] = $rangeStart['ref'] . ' to ' . $rangeEnd['ref'];
      }
      
      $rangeStart = $current;
      $rangeEnd = $current;
    }
  }

  // Add last range
  if ($rangeStart['num'] == $rangeEnd['num']) {
    $ranges[] = $rangeStart['ref'];
  } else {
    $ranges[] = $rangeStart['ref'] . ' to ' . $rangeEnd['ref'];
  }

  return [implode(', ', $ranges), $count];
}


  /**
   * Calculate grand totals from items
   */
  private function calculateGrandTotals($itemCategories)
  {
    $grandSubtotal = 0;
    $grandTVA = 0;
    $grandTotal = 0;

    foreach ($itemCategories as $category) {
      $grandSubtotal += $category['subtotal_usd'];
      $grandTVA += $category['tva_usd'];
      $grandTotal += $category['total_usd'];
    }

    return [$grandSubtotal, $grandTVA, $grandTotal];
  }

  /**
   * Get BCC rate from MCA data
   */
  private function getBCCRate($mcaDataArray)
  {
    if (!empty($mcaDataArray)) {
      return (float)($mcaDataArray[0]['bcc_rate'] ?? 2208.556);
    }
    return 2208.556;
  }

  /**
   * Generate company header section HTML
   */
  private function generateCompanyHeaderHTML($logoHtml)
  {
    return '<table class="header-table">
      <tr>
        <td style="width: 35%;">' . $logoHtml . '</td>
        <td style="width: 30%;"></td>
        <td style="width: 35%; text-align: right; font-size: 6.5pt; line-height: 1.3;">
          No. 1068, Avenue Ruwe, Quartier Makutano,<br>
          Lubumbashi, DRC<br>
          RCCM: 13-B-1122, ID NAT. 6-9-N91867E<br>
          NIF : A 1309334 L<br>
          VAT Ref # 145/DGI/DGE/INF/BN/TVA/2020<br>
          Capital Social : 45.000.000 FC<br>
          Point de vente : Lubumbashi
        </td>
      </tr>
    </table>';
  }

  /**
   * Generate client and invoice info boxes HTML
   */
/**
 * Generate client and invoice info boxes HTML
 */
/**
 * Generate client and invoice info boxes HTML
 */
private function generateInfoBoxesHTML($headerData, $allMcaRefs, $mcaCount, $pageType = 'debit')
{
  // Determine label and invoice reference based on page type
  if ($pageType === 'facture') {
    $label = 'N.FACTURE';
    $invoiceRef = $headerData['invoice_ref']; // No prefix for facture
  } else {
    $label = 'DEBIT NOTE';
    $invoiceRef = 'ND-' . $headerData['invoice_ref']; // Add ND- prefix for debit note
  }
  
  return '<table class="boxes-container">
    <tr>
      <td style="width: 45%;">
        <table class="client-box">
          <tr><td class="client-header">CLIENT</td></tr>
          <tr><td style="font-weight: bold;">' . $headerData['client_company'] . '</td></tr>
          <tr><td>' . $headerData['client_address'] . '</td></tr>
          <tr><td>No.RCCM: ' . $headerData['client_rccm'] . '</td></tr>
          <tr><td>No.NIF.: ' . $headerData['client_nif'] . '</td></tr>
          <tr><td>No.IDN.: ' . $headerData['client_idnat'] . '</td></tr>
          <tr><td>No.IMPORT/EXPORT: ' . $headerData['client_import_export'] . '</td></tr>
          <tr><td>No.TVA:</td></tr>
        </table>
      </td>
      <td style="width: 10%;"></td>
      <td style="width: 45%;">
        <table class="invoice-box">
          <tr>
            <td class="invoice-label">' . $label . '</td>
            <td style="font-weight: bold;">' . $invoiceRef . '</td>
          </tr>
          <tr>
            <td class="invoice-label">Date</td>
            <td style="font-weight: bold;">' . $headerData['invoice_date'] . '</td>
          </tr>
          <tr>
            <td class="invoice-label">Nombre de Dossier(s):</td>
            <td style="font-weight: bold; text-align: center;">' . $mcaCount . '</td>
          </tr>
          <tr>
            <td class="invoice-label">Dossier(s):</td>
            <td style="font-weight: bold; font-size: 6.5pt; line-height: 1.3;">' . $allMcaRefs . '</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>';
}

private function generateItemsTableHTML($category)
  {
    $categoryHeader = htmlspecialchars($category['category_header'], ENT_QUOTES, 'UTF-8');
    
    $html = '<div class="section-header">' . $categoryHeader . '</div>';
    $html .= '<table class="items-table">
      <thead>
        <tr>
          <th style="width: 50%;">Description</th>
          <th style="width: 8%;">Unit</th>
          <th style="width: 14%;">COST /USD</th>
          <th style="width: 14%;">SUBTOTAL USD</th>
          <th style="width: 7%;">TVA- 16%</th>
          <th style="width: 14%;">TOTAL EN USD</th>
        </tr>
      </thead>
      <tbody>';

    // Variables to recalculate totals excluding hidden items
    $visibleSubtotal = 0;
    $visibleTVA = 0;
    $visibleTotal = 0;

    foreach ($category['items'] as $item) {
      $unitText = htmlspecialchars($item['unit_text'] ?? 'Unit', ENT_QUOTES, 'UTF-8');
      
      // ✅ SKIP ENTIRE ROW IF UNIT VALUE IS 0 OR EMPTY
      if ($unitText === '0' || empty($unitText) || $unitText === 'Unit') {
        continue; // Skip this entire item row
      }
      
      $itemName = htmlspecialchars($item['item_name'] ?? '', ENT_QUOTES, 'UTF-8');
      $costUSD = number_format((float)($item['cost_usd'] ?? 0), 2);
      $subtotalUSD = number_format((float)($item['subtotal_usd'] ?? 0), 2);
      $tvaUSD = number_format((float)($item['tva_usd'] ?? 0), 2);
      $totalUSD = number_format((float)($item['total_usd'] ?? 0), 2);

      // Add to visible totals
      $visibleSubtotal += (float)($item['subtotal_usd'] ?? 0);
      $visibleTVA += (float)($item['tva_usd'] ?? 0);
      $visibleTotal += (float)($item['total_usd'] ?? 0);

      $html .= '<tr>
        <td>' . $itemName . '</td>
        <td class="c">' . $unitText . '</td>
        <td class="r">' . $costUSD . '</td>
        <td class="r">' . $subtotalUSD . '</td>
        <td class="r">' . $tvaUSD . '</td>
        <td class="r">' . $totalUSD . '</td>
      </tr>';
    }

    // Use visible totals instead of category totals
    $catSubtotal = number_format($visibleSubtotal, 2);
    $catTVA = number_format($visibleTVA, 2);
    $catTotal = number_format($visibleTotal, 2);

    $html .= '<tr class="subtotal-row">
      <td colspan="3" class="r">SUB-TOTAL / SOUS-TOTAL</td>
      <td class="r">' . $catSubtotal . '</td>
      <td class="r">' . $catTVA . '</td>
      <td class="r">' . $catTotal . '</td>
    </tr>';

    $html .= '</tbody></table>';
    return $html;
  }

  /**
   * Generate grand total rows HTML
   */
  private function generateGrandTotalHTML($grandSubtotal, $grandTVA, $grandTotal, $bccRate)
  {
    $totalCDF = $grandTotal * $bccRate;

    return '<table class="items-table" style="margin-top: 3mm;">
      <tr class="total-row">
        <td style="width: 64%;" class="r">TOTAL (USD)</td>
        <td style="width: 8%;" class="r">$ 0.00</td>
        <td style="width: 14%;" class="r">$ ' . number_format($grandSubtotal, 2) . '</td>
        <td style="width: 7%;" class="r">$ ' . number_format($grandTVA, 2) . '</td>
        <td style="width: 14%;" class="r">$ ' . number_format($grandTotal, 2) . '</td>
      </tr>
      <tr class="cdf-row">
        <td style="width: 64%;" class="r">Total USD</td>
        <td style="width: 8%;" class="r">$ 0.00</td>
        <td style="width: 14%;" class="r">$ ' . number_format($grandSubtotal, 2) . '</td>
        <td style="width: 7%;" class="r">$ ' . number_format($grandTVA, 2) . '</td>
        <td style="width: 14%;" class="r">$ ' . number_format($grandTotal, 2) . '</td>
      </tr>
      <tr class="cdf-row">
        <td colspan="5" style="width: 100%;" class="r">CDF ' . number_format($totalCDF, 2) . '</td>
      </tr>
    </table>
    <div style="text-align: right; font-size: 7pt; font-style: italic; margin-top: 2mm;">
      Taux de change: 1 USD = ' . number_format($bccRate, 2) . ' CDF
    </div>';
  }

  /**
   * Generate bank details HTML with signature ABOVE the header text, centered
   */

private function generateBankDetailsHTML($banks, $signatureHtml, $username = '', $grandTotalUSD = 0, $bccRate = 0)
{
  if (empty($banks)) {
    return '';
  }

  // Use default BCC rate if not provided
  if ($bccRate <= 0) {
    $bccRate = $this->defaultBccRate; // Uses the class property (2500.00)
  }

  // Calculate CDF amount using the ACTUAL BCC rate from MCA data
  $grandTotalCDF = $grandTotalUSD * $bccRate;
  
  // Convert amounts to French words
  $amountInWordsUSD = $this->numberToFrenchWords($grandTotalUSD, 'USD');
  $amountInWordsCDF = $this->numberToFrenchWords($grandTotalCDF, 'CDF');

  // Start with the payment info + signature section (side by side)
  $html = '<table style="width: 100%; margin-top: 5mm; margin-bottom: 3mm; border: none;">
    <tr>
      <!-- LEFT SIDE: Payment Mode and Amount in Letters -->
      <td style="width: 65%; vertical-align: top; padding: 0; border: none;">
        <table style="width: 100%; font-size: 7pt; border: none;">
          <tr>
            <td style="border: none; padding: 2px 0;">
              <strong>Mode de paiement :</strong> CREDIT
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 2px 0;">&nbsp;</td>
          </tr>
          <tr>
            <td style="border: none; padding: 2px 0;">
              <strong>Montant en lettres (USD):</strong><br>
              ' . $amountInWordsUSD . '
            </td>
          </tr>
          <tr>
            <td style="border: none; padding: 2px 0;">&nbsp;</td>
          </tr>
          <tr>
            <td style="border: none; padding: 2px 0;">
              <strong>Montant en lettres (CDF):</strong><br>
              ' . $amountInWordsCDF . '
            </td>
          </tr>
        </table>
      </td>
      
      <!-- RIGHT SIDE: Signature -->
      <td style="width: 35%; vertical-align: top; text-align: center; padding: 0; border: none;">
        ' . $signatureHtml;
  
  if (!empty($username)) {
    $html .= '<div style="font-size: 6pt; margin-top: 2px;">Opérateur: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>';
  }
  
  $html .= '
      </td>
    </tr>
  </table>';
  
  // Header text centered below
  $html .= '<div style="font-size: 7pt; font-weight: bold; text-align: center; margin-bottom: 3mm;">
    VEUILLEZ TROUVER CI-DESSOUS LES DETAILS DE NOTRE COMPTE BANCAIRE
  </div>';
  
  $bankCount = count($banks);
  $html .= '<table style="width: 100%; border: none;"><tr>';
  
  // Calculate column widths
  if ($bankCount == 1) {
    $widths = [100];
    $gaps = [];
  } elseif ($bankCount == 2) {
    $widths = [48, 48];
    $gaps = [4];
  } elseif ($bankCount == 3) {
    $widths = [31, 31, 31];
    $gaps = [3.5, 3.5];
  } else {
    $totalGapWidth = ($bankCount - 1) * 2;
    $widthPerBank = floor((100 - $totalGapWidth) / $bankCount);
    $widths = array_fill(0, $bankCount, $widthPerBank);
    $gaps = array_fill(0, $bankCount - 1, 2);
  }
  
  foreach ($banks as $index => $bank) {
    $html .= '<td style="width: ' . $widths[$index] . '%; vertical-align: top; padding: 0; border: none;">
      <table style="border: 1px solid #000; width: 100%; font-size: 6.5pt;">
        <tr>
          <td style="border: none; padding: 3px 5px; font-weight: bold; width: 30%;">INTITULE</td>
          <td style="border: none; padding: 3px 5px;">' . htmlspecialchars($bank['invoice_bank_account_name'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>
        <tr>
          <td style="border: none; padding: 3px 5px; font-weight: bold;">N.COMPTE</td>
          <td style="border: none; padding: 3px 5px;">' . htmlspecialchars($bank['invoice_bank_account_number'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>
        <tr>
          <td style="border: none; padding: 3px 5px; font-weight: bold;">SWIFT</td>
          <td style="border: none; padding: 3px 5px;">' . htmlspecialchars($bank['invoice_bank_swift'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>
        <tr>
          <td style="border: none; padding: 3px 5px; font-weight: bold;">BANQUE</td>
          <td style="border: none; padding: 3px 5px;">' . htmlspecialchars($bank['invoice_bank_name'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>
        </tr>
      </table>
    </td>';
    
    if ($index < $bankCount - 1) {
      $gapWidth = isset($gaps[$index]) ? $gaps[$index] : 2;
      $html .= '<td style="width: ' . $gapWidth . '%; border: none; padding: 0;"></td>';
    }
  }
  
  $html .= '</tr></table>';
  return $html;
}

  /**
   * Generate QR code section HTML
   */
  private function generateQRCodeHTML($qrcode, $codedefdgi)
  {
    if (empty($qrcode)) {
      return '';
    }

    require_once APP_ROOT . '/app/libraries/phpqrcode/qrlib.php';
    ob_start();
    QRcode::png($qrcode, null, QR_ECLEVEL_L, 4, 2);
    $imageData = ob_get_clean();
    $base64Qr = 'data:image/png;base64,' . base64_encode($imageData);
    
    return '<table style="width: 100%; border: 1px solid #000; margin-top: 5mm; border-collapse: collapse;">
      <tr>
        <td style="width: 50%; text-align: center; vertical-align: middle; padding: 10px; border: none;">
          <img style="width:100px;" src="' . $base64Qr . '" alt="QR Code">
        </td>
        <td style="width: 50%; text-align: center; vertical-align: middle; padding: 10px; border: none;">
          <h2 style="margin: 0; font-size: 11pt;">' . htmlspecialchars($codedefdgi, ENT_QUOTES, 'UTF-8') . '</h2>
        </td>
      </tr>
    </table>';
  }

  // ========== VIEW COMPLETE PDF (ALL PAGES) ==========
  public function viewPDF()
  {
    ini_set('display_errors', 0);
    error_reporting(0);

    $invoiceId = (int)($_GET['id'] ?? 0);
    if ($invoiceId <= 0) {
      http_response_code(400);
      exit('Invalid invoice ID');
    }

    try {
      $autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
      if (!file_exists($autoload)) {
        throw new Exception('Composer autoload not found at: ' . $autoload);
      }
      require_once $autoload;

      // Get invoice data
      $invoice = $this->getInvoiceDataForPDF($invoiceId);
      $clientId = (int)$invoice['client_id'];
      $validated = (int)$invoice['validated'];

      // Get grouped items
      $groupedItems = $this->getGroupedInvoiceItems($invoiceId);
      list($reimbursableItems, $operationalItems) = $this->categorizeItems($groupedItems);

      // Get MCA data
      $mcaDataArray = $this->getMCADataForInvoice($invoiceId);

      // Get banks and signature
      $banks = $this->getBanks($clientId);
      list($signaturePath, $username) = $this->getUserSignatureData();

      // Prepare data payload
      $dataPayload = [
        'invoice' => $invoice,
        'reimbursable_items' => $reimbursableItems,
        'operational_items' => $operationalItems,
        'banks' => $banks,
        'mca_data' => $mcaDataArray,
        'client_name' => $invoice['short_name'],
        'signature_path' => $signaturePath,
        'username' => $username
      ];

      if (ob_get_length()) ob_end_clean();

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 5,
        'margin_bottom' => 5,
        'margin_left' => 5,
        'margin_right' => 5,
        'tempDir' => sys_get_temp_dir(),
        'allow_output_buffering' => true
      ]);

      if ($validated === 0) {
        $mpdf->SetWatermarkText('NOT VALID');
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.15;
      }

      // PAGE 1: Debit Note (Portrait)
      $page1HTML = $this->generateDebitNoteHTML($dataPayload);
      $mpdf->WriteHTML($page1HTML);

      // PAGE 2: Facture (Portrait)
      if (!empty($operationalItems)) {
        $mpdf->AddPage();
        $page2HTML = $this->generateFactureHTML($dataPayload);
        $mpdf->WriteHTML($page2HTML);
      }

      // PAGE 3: MCA Physical Details (Landscape)
      if (!empty($mcaDataArray)) {
        $mpdf->AddPageByArray([
          'orientation' => 'L',
          'margin-left' => 5,
          'margin-right' => 5,
          'margin-top' => 5,
          'margin-bottom' => 5,
        ]);
        $page3HTML = $this->generateMCAPhysicalDetailsHTML($dataPayload);
        $mpdf->WriteHTML($page3HTML);
      }

      // PAGE 4: MCA Financial Details (Landscape)
      if (!empty($mcaDataArray)) {
        $mpdf->AddPageByArray([
          'orientation' => 'L',
          'margin-left' => 5,
          'margin-right' => 5,
          'margin-top' => 5,
          'margin-bottom' => 5,
        ]);
        $page4HTML = $this->generateMCAFinancialDetailsHTML($dataPayload);
        $mpdf->WriteHTML($page4HTML);
      }

      $filename = 'Export_Invoice_Complete_' . preg_replace('/[^A-Za-z0-9]/', '_', $invoice['invoice_ref']) . '.pdf';
      $mpdf->Output($filename, 'I');
      exit;

    } catch (Throwable $e) {
      $this->logError('PDF Generation Error: ' . $e->getMessage());
      $this->logError('Stack trace: ' . $e->getTraceAsString());
      http_response_code(500);
      exit('PDF generation failed: ' . $e->getMessage());
    }
  }

  // ========== VIEW PDF PAGE 1 (DEBIT NOTE ONLY) ==========
  public function viewPDFPage1()
  {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $invoiceId = (int)($_GET['id'] ?? 0);
    if ($invoiceId <= 0) {
      die('Invalid invoice ID');
    }

    try {
      $this->logError("=== Starting PDF Page 1 Generation for Invoice ID: $invoiceId ===");

      $autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
      if (!file_exists($autoload)) {
        throw new Exception('Composer autoload not found');
      }
      require_once $autoload;

      // Get invoice data
      $invoice = $this->getInvoiceDataForPDF($invoiceId);
      $clientId = (int)$invoice['client_id'];
      $validated = (int)$invoice['validated'];

      // Get grouped items
      $groupedItems = $this->getGroupedInvoiceItems($invoiceId);
      list($reimbursableItems, $operationalItems) = $this->categorizeItems($groupedItems);

      $this->logError("Reimbursable categories found: " . count($reimbursableItems));

      // Get MCA data
      $mcaDataArray = $this->getMCADataForInvoice($invoiceId);

      // Get banks and signature
      $banks = $this->getBanks($clientId);
      list($signaturePath, $username) = $this->getUserSignatureData();

      // Generate HTML
      $html = $this->generateDebitNoteHTML([
        'invoice' => $invoice,
        'reimbursable_items' => $reimbursableItems,
        'banks' => $banks,
        'mca_data' => $mcaDataArray,
        'signature_path' => $signaturePath,
        'username' => $username
      ]);

      $this->logError("HTML generated, length: " . strlen($html));

      // Clear any output buffers
      while (ob_get_level()) {
        ob_end_clean();
      }

      // Generate PDF
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 5,
        'margin_bottom' => 5,
        'margin_left' => 5,
        'margin_right' => 5,
        'tempDir' => sys_get_temp_dir()
      ]);

      if ($validated === 0) {
        $mpdf->SetWatermarkText('NOT VALID');
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.15;
      }

      $mpdf->WriteHTML($html);

      $filename = 'Debit_Note_' . preg_replace('/[^A-Za-z0-9]/', '_', $invoice['invoice_ref']) . '.pdf';
      $this->logError("Outputting PDF: $filename");

      $mpdf->Output($filename, 'I');
      exit;

    } catch (Throwable $e) {
      $this->logError('PDF Page 1 Error: ' . $e->getMessage());
      $this->logError('Stack trace: ' . $e->getTraceAsString());
      die('<h1>PDF Generation Error</h1><pre>' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>');
    }
  }

  // ========== VIEW PDF PAGES 2-4 ==========
  public function viewPDFPages2to4()
  {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $invoiceId = (int)($_GET['id'] ?? 0);
    if ($invoiceId <= 0) {
      http_response_code(400);
      exit('Invalid invoice ID');
    }

    try {
      $autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
      if (!file_exists($autoload)) {
        throw new Exception('Composer autoload not found at: ' . $autoload);
      }
      require_once $autoload;

      // Get invoice data
      $invoice = $this->getInvoiceDataForPDF($invoiceId);
      $clientId = (int)$invoice['client_id'];
      $validated = (int)$invoice['validated'];

      // Get grouped items
      $groupedItems = $this->getGroupedInvoiceItems($invoiceId);
      list($reimbursableItems, $operationalItems) = $this->categorizeItems($groupedItems);

      // Get MCA data
      $mcaDataArray = $this->getMCADataForInvoice($invoiceId);

      // Get banks and signature
      $banks = $this->getBanks($clientId);
      list($signaturePath, $username) = $this->getUserSignatureData();

      $dataPayload = [
        'invoice' => $invoice,
        'operational_items' => $operationalItems,
        'banks' => $banks,
        'mca_data' => $mcaDataArray,
        'client_name' => $invoice['short_name'],
        'signature_path' => $signaturePath,
        'username' => $username
      ];

      if (ob_get_length()) ob_end_clean();

      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_top' => 5,
        'margin_bottom' => 5,
        'margin_left' => 5,
        'margin_right' => 5,
        'tempDir' => sys_get_temp_dir()
      ]);

      if ($validated === 0) {
        $mpdf->SetWatermarkText('NOT VALID');
        $mpdf->showWatermarkText = true;
        $mpdf->watermarkTextAlpha = 0.15;
      }

      // PAGE 1: Facture (Portrait)
      if (!empty($operationalItems)) {
        $page1Html = $this->generateFactureHTML($dataPayload);
        $mpdf->WriteHTML($page1Html);
      }

      // PAGE 2: MCA Physical Details (Landscape)
      if (!empty($mcaDataArray)) {
        $mpdf->AddPageByArray([
          'orientation' => 'L',
          'margin-left' => 5,
          'margin-right' => 5,
          'margin-top' => 5,
          'margin-bottom' => 5,
        ]);
        $page2Html = $this->generateMCAPhysicalDetailsHTML($dataPayload);
        $mpdf->WriteHTML($page2Html);
      }

      // PAGE 3: MCA Financial Details (Landscape)
      if (!empty($mcaDataArray)) {
        $mpdf->AddPageByArray([
          'orientation' => 'L',
          'margin-left' => 5,
          'margin-right' => 5,
          'margin-top' => 5,
          'margin-bottom' => 5,
        ]);
        $page3Html = $this->generateMCAFinancialDetailsHTML($dataPayload);
        $mpdf->WriteHTML($page3Html);
      }

      $filename = 'Export_Invoice_Facture_Details_' . preg_replace('/[^A-Za-z0-9]/', '_', $invoice['invoice_ref']) . '.pdf';
      $mpdf->Output($filename, 'I');
      exit;

    } catch (Throwable $e) {
      $this->logError('PDF Generation Error: ' . $e->getMessage());
      $this->logError('Stack trace: ' . $e->getTraceAsString());
      http_response_code(500);
      exit('PDF generation failed: ' . $e->getMessage());
    }
  }

// ========== GENERATE DEBIT NOTE HTML ==========
private function generateDebitNoteHTML($data)
{
  $invoice = $data['invoice'];
  $reimbursableItems = $data['reimbursable_items'] ?? [];
  $banks = $data['banks'] ?? [];
  $mcaDataArray = $data['mca_data'] ?? [];
  $signaturePath = $data['signature_path'] ?? null;
  $username = $data['username'] ?? '';

  $logoHtml = $this->getLogoHTML();
  $signatureHtml = $this->getSignatureHTML($signaturePath);
  $headerData = $this->formatInvoiceHeaderData($invoice);
  list($allMcaRefs, $mcaCount) = $this->formatMCAReferences($mcaDataArray);
  list($grandSubtotal, $grandTVA, $grandTotal) = $this->calculateGrandTotals($reimbursableItems);
  $bccRate = $this->getBCCRate($mcaDataArray);

  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
  $html .= $this->getPdfCommonCSS();
  $html .= '</style></head><body>';

  $html .= $this->generateCompanyHeaderHTML($logoHtml);
  $html .= '<div class="doc-title">DEBIT NOTE</div>';
  $html .= $this->generateInfoBoxesHTML($headerData, $allMcaRefs, $mcaCount, 'debit');
  
  $html .= '<div style="padding: 5px 8px; font-weight: bold; font-size: 8pt; text-align: center; margin-top: 3mm; margin-bottom: 2mm;">REIMBURSABLE CHARGES</div>';

  foreach ($reimbursableItems as $category) {
    $html .= $this->generateItemsTableHTML($category);
  }

  $html .= $this->generateGrandTotalHTML($grandSubtotal, $grandTVA, $grandTotal, $bccRate);
  
  // ✅ UPDATED: Pass grandTotal and bccRate to show amount in words
  $html .= $this->generateBankDetailsHTML($banks, $signatureHtml, $username, $grandTotal, $bccRate);

  $html .= '<div style="margin-top: 4mm; font-size: 7pt; font-weight: bold; text-align: center;">
    LE PAIEMENT DOIT S\'EFFECTUER ENDEANS 7 JOURS
  </div>';

  $html .= '<div class="thank-you">Thank you for your business!</div>';
  $html .= '</body></html>';

  return $html;
}


// ========== GENERATE FACTURE HTML ==========
private function generateFactureHTML($data)
{
  $invoice = $data['invoice'];
  $operationalItems = $data['operational_items'] ?? [];
  $banks = $data['banks'] ?? [];
  $mcaDataArray = $data['mca_data'] ?? [];
  $signaturePath = $data['signature_path'] ?? null;
  $username = $data['username'] ?? '';
  $qrcode = $invoice['qrcode'] ?? '';
  $codedefdgi = $invoice['codedefdgi'] ?? '';

  $logoHtml = $this->getLogoHTML();
  $signatureHtml = $this->getSignatureHTML($signaturePath);
  $headerData = $this->formatInvoiceHeaderData($invoice);
  list($allMcaRefs, $mcaCount) = $this->formatMCAReferences($mcaDataArray);
  list($grandSubtotal, $grandTVA, $grandTotal) = $this->calculateGrandTotals($operationalItems);
  $bccRate = $this->getBCCRate($mcaDataArray);

  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
  $html .= $this->getPdfCommonCSS();
  $html .= '</style></head><body>';

  $html .= $this->generateCompanyHeaderHTML($logoHtml);
  $html .= '<div class="doc-title">FACTURE</div>';
  $html .= $this->generateInfoBoxesHTML($headerData, $allMcaRefs, $mcaCount, 'facture');

  foreach ($operationalItems as $category) {
    $html .= $this->generateItemsTableHTML($category);
  }

  $html .= $this->generateGrandTotalHTML($grandSubtotal, $grandTVA, $grandTotal, $bccRate);
  
  // ✅ UPDATED: Pass grandTotal and bccRate to show amount in words
  $html .= $this->generateBankDetailsHTML($banks, $signatureHtml, $username, $grandTotal, $bccRate);

  $html .= '<div style="margin-top: 4mm; font-size: 7pt; font-weight: bold; text-align: center;">
    LE PAIEMENT DOIT S\'EFFECTUER ENDEANS 7 JOURS
  </div>';

  $html .= '<div class="thank-you">Thank you for your business!</div>';
  $html .= $this->generateQRCodeHTML($qrcode, $codedefdgi);
  $html .= '</body></html>';

  return $html;
}
private function generateMCAPhysicalDetailsHTML($data)
{
  $invoice = $data['invoice'];
  $mcaDataArray = $data['mca_data'] ?? [];
  $clientName = $data['client_name'] ?? '';
  $signaturePath = $data['signature_path'] ?? null;
  $username = $data['username'] ?? '';

  $logoHtml = $this->getLogoHTML();
  $signatureHtml = $this->getSignatureHTML($signaturePath);
  $invoiceRef = htmlspecialchars($invoice['invoice_ref'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
  $invoiceDate = !empty($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : date('d/m/Y');

  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
  $html .= $this->getPdfCommonCSS();
  $html .= $this->getPdfLandscapeCSS();
  $html .= '</style></head><body>';

  $html .= $this->generateCompanyHeaderHTML($logoHtml);
  
  $html .= '<div style="font-weight: bold; font-size: 8pt; margin-bottom: 3mm;">DETAILS - EXPORT CLEARING CUIVRE LOADS</div>';

  $html .= '<table class="landscape-table">
    <thead>
      <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 10%;">MCA File No</th>
        <th style="width: 8%;">Destination</th>
        <th style="width: 10%;">Transporter</th>
        <th style="width: 12%;">Horse/Wagon</th>
        <th style="width: 10%;">Trailer 1</th>
        <th style="width: 10%;">Trailer 2</th>
        <th style="width: 12%;">Lot. No.</th>
        <th style="width: 6%;">Qty(Mt)</th>
        <th style="width: 9%;">Loading Date</th>
        <th style="width: 9%;">Clearing Completed Date</th>
        <th style="width: 7%;">CLEARED</th>
      </tr>
    </thead>
    <tbody>';

  $counter = 1;
  $totalQty = 0;

  foreach ($mcaDataArray as $mca) {
    $mcaRef = htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8');
    $weight = (float)($mca['weight'] ?? 0);
    $totalQty += $weight;
    
    $destination = strtoupper($clientName);
    $transporter = htmlspecialchars($mca['buyer'] ?? '', ENT_QUOTES, 'UTF-8');
    $horse = htmlspecialchars($mca['horse'] ?? '', ENT_QUOTES, 'UTF-8');
    $trailer1 = htmlspecialchars($mca['trailer_1'] ?? '', ENT_QUOTES, 'UTF-8');
    $trailer2 = htmlspecialchars($mca['trailer_2'] ?? '', ENT_QUOTES, 'UTF-8');
    $lotNumber = htmlspecialchars($mca['lot_number'] ?? '', ENT_QUOTES, 'UTF-8');
    
    // ✅ Get dates directly from exports_t
    $loadingDate = !empty($mca['loading_date']) ? date('d/m/Y', strtotime($mca['loading_date'])) : '';
    $clearingDate = !empty($mca['exit_drc_date']) ? date('d/m/Y', strtotime($mca['exit_drc_date'])) : '';

    $html .= '<tr>
      <td class="c">' . $counter++ . '</td>
      <td>' . $mcaRef . '</td>
      <td>' . $destination . '</td>
      <td>' . $transporter . '</td>
      <td>' . $horse . '</td>
      <td>' . $trailer1 . '</td>
      <td>' . $trailer2 . '</td>
      <td>' . $lotNumber . '</td>
      <td class="r">' . number_format($weight, 3) . '</td>
      <td class="c">' . $loadingDate . '</td>
      <td class="c">' . $clearingDate . '</td>
      <td class="c">CLEARED</td>
    </tr>';
  }

  $html .= '<tr style="font-weight: bold; background: #f0f0f0;">
    <td colspan="8" class="r">Total</td>
    <td class="r">' . number_format($totalQty, 3) . '</td>
    <td colspan="3"></td>
  </tr>';
  $html .= '</tbody></table>';

  $html .= '<div style="margin-top: 5mm; font-size: 6.5pt; text-align: right;">
    Details INV No. ' . $invoiceRef . ' du ' . $invoiceDate . '
  </div>';

  if (!empty($signaturePath)) {
    $html .= '<div style="margin-top: 8mm; text-align: center;">
      ' . $signatureHtml . '<br>
      <div style="font-size: 6pt; margin-top: 2px;">Opérateur: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>
    </div>';
  }

  $html .= '</body></html>';
  return $html;
}

  // ========== GENERATE MCA FINANCIAL DETAILS HTML ==========
// ========== GENERATE MCA FINANCIAL DETAILS HTML ==========
private function generateMCAFinancialDetailsHTML($data)
{
  $invoice = $data['invoice'];
  $mcaDataArray = $data['mca_data'] ?? [];
  $signaturePath = $data['signature_path'] ?? null;
  $username = $data['username'] ?? '';

  $logoHtml = $this->getLogoHTML();
  $signatureHtml = $this->getSignatureHTML($signaturePath);
  $invoiceRef = htmlspecialchars($invoice['invoice_ref'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
  $invoiceDate = !empty($invoice['invoice_date']) ? date('d/m/Y', strtotime($invoice['invoice_date'])) : date('d/m/Y');

  $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
  $html .= $this->getPdfCommonCSS();
  $html .= $this->getPdfLandscapeCSS();
  $html .= '</style></head><body>';

  $html .= $this->generateCompanyHeaderHTML($logoHtml);
  
  $html .= '<div style="font-weight: bold; font-size: 8pt; margin-bottom: 3mm;">DETAILS - EXPORT CLEARING CUIVRE LOADS</div>';

  $html .= '<table class="landscape-table">
    <thead>
      <tr>
        <th style="width: 3%;">#</th>
        <th style="width: 10%;">MCA File No</th>
        <th style="width: 6%;">Qty(Mt)</th>
        <th style="width: 8%;">Loading Date</th>
        <th style="width: 9%;">Declaration Ref.</th>
        <th style="width: 8%;">Declaration Date</th>
        <th style="width: 9%;">Liquidation Ref.</th>
        <th style="width: 8%;">Liquidation Date</th>
        <th style="width: 9%;">Liq. Amt. CDF</th>
        <th style="width: 9%;">Quittance Ref.</th>
        <th style="width: 8%;">Quittance Date</th>
        <th style="width: 7%;">Rate</th>
        <th style="width: 9%;">Liq. Amt. USD</th>
      </tr>
    </thead>
    <tbody>';

  $counter = 1;
  $totalQty = 0;
  $totalLiqCDF = 0;
  $totalLiqUSD = 0;
  $avgRate = 0;

  foreach ($mcaDataArray as $mca) {
    $mcaRef = htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8');
    $weight = (float)($mca['weight'] ?? 0);
    $totalQty += $weight;
    
    $loadingDate = !empty($mca['loading_date']) ? date('d/m/Y', strtotime($mca['loading_date'])) : '';
    $declarationRef = htmlspecialchars($mca['declaration_reference'] ?? '', ENT_QUOTES, 'UTF-8');
    $declarationDate = !empty($mca['declaration_date']) ? date('d/m/Y', strtotime($mca['declaration_date'])) : '';
    $liquidationRef = htmlspecialchars($mca['liquidation_reference'] ?? '', ENT_QUOTES, 'UTF-8');
    $liquidationDate = !empty($mca['liquidation_date']) ? date('d/m/Y', strtotime($mca['liquidation_date'])) : '';
    $quittanceRef = htmlspecialchars($mca['quittance_reference'] ?? '', ENT_QUOTES, 'UTF-8');
    $quittanceDate = !empty($mca['quittance_date']) ? date('d/m/Y', strtotime($mca['quittance_date'])) : '';
    
    // ✅ FIX: Use the stored liquidation_amount directly instead of recalculating
    $liqAmtCDF = (float)($mca['liquidation_amount'] ?? 0);
    $totalLiqCDF += $liqAmtCDF;
    
    $bccRate = (float)($mca['bcc_rate'] ?? 2208.556);
    $liqAmtUSD = $bccRate > 0 ? $liqAmtCDF / $bccRate : 0;
    $totalLiqUSD += $liqAmtUSD;
    
    if ($counter == 1) {
      $avgRate = $bccRate;
    }

    $html .= '<tr>
      <td class="c">' . $counter++ . '</td>
      <td>' . $mcaRef . '</td>
      <td class="r">' . number_format($weight, 3) . '</td>
      <td class="c">' . $loadingDate . '</td>
      <td>' . $declarationRef . '</td>
      <td class="c">' . $declarationDate . '</td>
      <td>' . $liquidationRef . '</td>
      <td class="c">' . $liquidationDate . '</td>
      <td class="r">' . number_format($liqAmtCDF, 0) . '</td>
      <td>' . $quittanceRef . '</td>
      <td class="c">' . $quittanceDate . '</td>
      <td class="r">' . number_format($bccRate, 4) . '</td>
      <td class="r">' . number_format($liqAmtUSD, 2) . '</td>
    </tr>';
  }

  $html .= '<tr style="font-weight: bold; background: #f0f0f0;">
    <td colspan="2" class="r">Total</td>
    <td class="r">' . number_format($totalQty, 3) . '</td>
    <td colspan="5"></td>
    <td class="r">' . number_format($totalLiqCDF, 0) . '</td>
    <td colspan="2"></td>
    <td class="r">' . number_format($avgRate, 4) . '</td>
    <td class="r">' . number_format($totalLiqUSD, 2) . '</td>
  </tr>';
  $html .= '</tbody></table>';

  $html .= '<div style="margin-top: 10mm; font-size: 6.5pt; text-align: right;">
    Details INV No. ' . $invoiceRef . ' du ' . $invoiceDate . '
  </div>';

  if (!empty($signaturePath)) {
    $html .= '<div style="margin-top: 8mm; text-align: center;">
      ' . $signatureHtml . '<br>
      <div style="font-size: 6pt; margin-top: 2px;">Opérateur: ' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '</div>
    </div>';
  }

  $html .= '</body></html>';
  return $html;
}

  // ========== VALIDATION & SANITIZATION ==========

  private function validateInvoiceData($postData, $invoiceId = null)
  {
    $errors = [];

    if (empty($postData['client_id']) || !is_numeric($postData['client_id'])) {
      $errors[] = 'Valid client is required';
    }

    if (empty($postData['license_id']) || !is_numeric($postData['license_id'])) {
      $errors[] = 'Valid license is required';
    }

    if (empty($postData['invoice_ref'])) {
      $errors[] = 'Invoice reference is required';
    }

    if (!empty($postData['invoice_ref'])) {
      $checkSql = $invoiceId
        ? "SELECT id FROM export_invoices_t WHERE invoice_ref = ? AND id != ?"
        : "SELECT id FROM export_invoices_t WHERE invoice_ref = ?";
      $checkParams = $invoiceId ? [$postData['invoice_ref'], $invoiceId] : [$postData['invoice_ref']];
      $existing = $this->db->customQuery($checkSql, $checkParams);

      if (!empty($existing)) {
        $errors[] = 'Invoice reference already exists';
      }
    }

    if (empty($errors)) {
      return ['success' => true];
    }

    return ['success' => false, 'message' => implode(', ', $errors)];
  }

  private function validateCsrfToken()
  {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token']);
      exit;
    }
  }

  private function sanitizeInput($input)
  {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
  }

  private function sanitizeArray($data)
  {
    if (!is_array($data))
      return [];
    return array_map(function ($item) {
      if (is_array($item))
        return $this->sanitizeArray($item);
      return is_string($item) ? htmlspecialchars($item, ENT_QUOTES, 'UTF-8') : $item;
    }, $data);
  }

  private function clean($value)
  {
    if ($value === null || $value === '')
      return null;
    return trim($value);
  }

  private function toInt($value)
  {
    if ($value === null || $value === '')
      return null;
    return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : null;
  }

  private function toDecimal($value, $decimals = 2)
  {
    if ($value === null || $value === '')
      return null;
    return round((float)$value, $decimals);
  }

  private function toDate($value)
  {
    if (empty($value))
      return null;
    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
  }

  private function logError($message)
  {
    if ($this->logFile) {
      @file_put_contents($this->logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
  }
}