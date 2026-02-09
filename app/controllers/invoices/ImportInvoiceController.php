<?php

require_once __DIR__ . '/EmcfController.php';
class ImportinvoiceController extends EmcfController
{
  private $db;
  private $logFile;
  private $logoPath;

  public function __construct()
  {
    parent::__construct();
    $this->db = new Database();
    $this->logFile = __DIR__ . '/../../logs/import_invoice.log';
    $this->logoPath = __DIR__ . '/../../../public/images/logo.jpg';
    
    $logDir = dirname($this->logFile);
    if (!is_dir($logDir)) {
      @mkdir($logDir, 0755, true);
    }
  }

  private function formatNumber($value, $decimals = 2)
  {
    $num = floatval($value);
    if ($num == 0) return '';
    
    $formatted = number_format($num, $decimals, '.', '');
    $formatted = rtrim($formatted, '0');
    $formatted = rtrim($formatted, '.');
    
    return $formatted === '' ? '' : $formatted;
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
            WHERE c.display = 'Y' AND l.display = 'Y' AND l.kind_id IN (1, 2, 5, 6)
            ORDER BY c.short_name ASC";
    $clients = $this->db->customQuery($sql) ?: [];
    
    $currencies = $this->db->selectData('currency_master_t', 'id, currency_name, currency_short_name', ['display' => 'Y'], 'currency_short_name ASC') ?: [];

    $data = [
      'title' => 'Import Invoice Management',
      'clients' => $this->sanitizeArray($clients),
      'currencies' => $this->sanitizeArray($currencies),
      'csrf_token' => $_SESSION['csrf_token']
    ];

    $this->viewWithLayout('invoices/importinvoice', $data);
  }

public function crudData($action = 'listing')
{
    if ($action === 'exportDebit' || $action === 'exportInvoiced') {
        if ($action === 'exportDebit') {
            $this->exportDebitInvoices();
        } else {
            $this->exportInvoicedInvoices();
        }
        return;
    }

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
                
            case 'deletion':
                $this->deleteInvoice();
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
                
            case 'viewPDF':
                $this->viewPDF();
                break;
                
            case 'getPendingMCAs':
                $this->getPendingMCAs();
                break;
                
            case 'getClientGroupedInvoices':
                $this->getClientGroupedInvoices();
                break;
                
            case 'exportPendingMCAs':
                $this->exportPendingMCAs();
                break;
            case 'finalizeEMCF':
                $this->finalizeEMCF();
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        $this->logError("FATAL Exception in crudData: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }

    ob_end_flush();
    exit;
}

private function getPendingMCAs()
{
    try {
      $sql = "SELECT i.id, i.mca_ref, i.fob, i.weight, i.quittance_date,
                     c.short_name as client_name, l.license_number
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id
              LEFT JOIN licenses_t l ON i.license_id = l.id
              WHERE i.display = 'Y'
              AND i.quittance_date IS NOT NULL
              AND i.quittance_date != ''
              AND i.kind IN (1, 2, 5, 6)
              AND NOT EXISTS (
                SELECT 1 
                FROM import_invoices_t inv
                WHERE FIND_IN_SET(i.id, inv.mca_ids) > 0
              )
              ORDER BY i.quittance_date DESC";
              // ⭐ REMOVED: LIMIT 100
      
      $result = $this->db->customQuery($sql);
      
      echo json_encode(['success' => true, 'data' => $this->sanitizeArray($result ?: [])]);
    } catch (Exception $e) {
      $this->logError("Error getting pending MCAs: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load pending MCAs', 'data' => []]);
    }
}

  private function getClientDetails()
  {
    try {
      $clientId = (int)($_GET['client_id'] ?? 0);
      
      if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => null]);
        return;
      }

      $sql = "SELECT id, short_name, company_name, invoice_template, liquidation_paid_by FROM clients_t WHERE id = ? AND display = 'Y' LIMIT 1";
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
      $kindId = (int)($_GET['kind_id'] ?? 0);
      $transportModeId = (int)($_GET['transport_mode_id'] ?? 0);
      $goodsTypeId = (int)($_GET['goods_type_id'] ?? 0);
      
      if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => []]);
        return;
      }

      $sql = "SELECT q.id, q.quotation_ref, q.quotation_date, 
                     q.sub_total, q.vat_amount, q.total_amount,
                     q.kind_id, q.transport_mode_id, q.goods_type_id
              FROM quotations_t q
              WHERE q.client_id = ? 
              AND q.display = 'Y' 
              AND q.kind_id IN (1, 2, 5, 6)";
      
      $params = [$clientId];
      
      if ($kindId > 0) {
        $sql .= " AND q.kind_id = ?";
        $params[] = $kindId;
      }
      
      if ($transportModeId > 0) {
        $sql .= " AND q.transport_mode_id = ?";
        $params[] = $transportModeId;
      }
      
      if ($goodsTypeId > 0) {
        $sql .= " AND q.goods_type_id = ?";
        $params[] = $goodsTypeId;
      }
      
      $sql .= " ORDER BY q.quotation_date DESC, q.id DESC";

      $quotations = $this->db->customQuery($sql, $params);
      
      $this->logError("Found " . count($quotations ?: []) . " quotations for client $clientId with filters: kind=$kindId, transport=$transportModeId, goods=$goodsTypeId");
      
      echo json_encode(['success' => true, 'data' => $this->sanitizeArray($quotations ?: [])]);

    } catch (Exception $e) {
      $this->logError("Error in getAllQuotationsForClient: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load quotations', 'data' => []]);
    }
  }

private function getQuotationItems()
{
    try {
      $quotationId = (int)($_GET['quotation_id'] ?? 0);
      $clientId = (int)($_GET['client_id'] ?? 0);

      if ($quotationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid quotation ID', 'data' => []]);
        return;
      }
      
      $quotationSql = "SELECT q.id, q.client_id, q.quotation_ref, q.sub_total, q.vat_amount, q.total_amount,
                              q.quotation_date, q.kind_id, q.transport_mode_id, q.goods_type_id
                       FROM quotations_t q
                       WHERE q.id = ? AND q.display = 'Y'
                       LIMIT 1";
      
      $quotationResult = $this->db->customQuery($quotationSql, [$quotationId]);
      
      if (empty($quotationResult)) {
        echo json_encode(['success' => false, 'message' => 'Quotation not found', 'data' => []]);
        return;
      }

      $quotation = $quotationResult[0];

      if ($clientId > 0 && (int)$quotation['client_id'] !== $clientId) {
        echo json_encode(['success' => false, 'message' => 'Quotation does not belong to selected client', 'data' => []]);
        return;
      }

      $itemsSql = "SELECT qi.id, qi.quotation_id, qi.category_id, qi.item_id,
                          qi.quantity, qi.unit_id, qi.unit_text,
                          qi.taux_usd, qi.cost_usd, qi.subtotal_usd,
                          qi.has_tva, qi.tva_usd, qi.total_usd,
                          qi.currency_id,
                          qi.percentage,
                          qd.item_name,
                          qc.category_name, qc.category_header, qc.display_order,
                          u.unit_name,
                          curr.currency_short_name
                   FROM quotation_items_t qi
                   LEFT JOIN item_master_t qd ON qi.item_id = qd.id
                   LEFT JOIN quotation_categories_t qc ON qi.category_id = qc.id
                   LEFT JOIN unit_master_t u ON qi.unit_id = u.id
                   LEFT JOIN currency_master_t curr ON qi.currency_id = curr.id
                   WHERE qi.quotation_id = ? 
                   AND qi.display = 'Y'
                   ORDER BY qc.display_order ASC, qi.id ASC";

      $items = $this->db->customQuery($itemsSql, [$quotationId]);
      $items = $this->sanitizeArray($items ?: []);

      $groupedItems = [];
      foreach ($items as $item) {
        $categoryId = $item['category_id'] ?? 0;
        $categoryName = $item['category_name'] ?? 'Uncategorized';
        $categoryHeader = $item['category_header'] ?? $categoryName;
        
        if (!isset($groupedItems[$categoryId])) {
          $groupedItems[$categoryId] = [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'category_header' => $categoryHeader,
            'display_order' => $item['display_order'] ?? 999,
            'category_total' => 0,
            'category_tva' => 0,
            'items' => []
          ];
        }
        
        $groupedItems[$categoryId]['category_total'] += (float)($item['subtotal_usd'] ?? 0);
        $groupedItems[$categoryId]['category_tva'] += (float)($item['tva_usd'] ?? 0);
        $groupedItems[$categoryId]['items'][] = $item;
      }

      $categorizedItems = array_values($groupedItems);
      usort($categorizedItems, function($a, $b) {
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
      echo json_encode(['success' => false, 'message' => 'Failed to load quotation items', 'data' => []]);
    }
}
  private function validateInvoice()
  {
    $this->validateCsrfToken();
    
    try {
      $invoiceId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
      
      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id, validated FROM import_invoices_t WHERE id = ?", [$invoiceId]);
      
      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $userId = (int)($_SESSION['user_id'] ?? 1);
      $sql = "UPDATE import_invoices_t SET validated = 1, updated_by = ?, updated_at = NOW() WHERE id = ?";
      $this->db->customQuery($sql, [$userId, $invoiceId]);
      
      $this->logError("Invoice ID $invoiceId validated by user ID $userId");
      
      echo json_encode([
        'success' => true, 
        'message' => 'Invoice validated successfully!'
      ]);

    } catch (Exception $e) {
      $this->logError("Validate Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }

  private function markDGI()
  {
    $this->validateCsrfToken();
    
    try {
      $invoiceId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
      
      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id, validated FROM import_invoices_t WHERE id = ?", [$invoiceId]);
      
      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $currentValidated = (int)($existing[0]['validated'] ?? 0);
      if ($currentValidated !== 1) {
        echo json_encode(['success' => false, 'message' => 'Invoice must be validated first before marking as DGI Verified']);
        return;
      }

      $invoiceData = $this->buildEmcfPayload($invoiceId, 'IMPORT');
      echo $this->sendInvoiceToEmcf($invoiceId, 'IMPORT', $invoiceData);
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
          FROM import_invoices_t eit
          LEFT JOIN emcf_invoice ei ON ei.inv_type = 'IMPORT' AND ei.invoice_id = eit.id
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
          FROM import_invoice_items_t eiit
          LEFT JOIN item_master_t imt ON eiit.item_id = imt.id
          WHERE eiit.invoice_id = ? AND eiit.category_id IN (3,4)
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
              $sql = "UPDATE import_invoices_t SET validated = 2, updated_by = ?, updated_at = NOW() WHERE id = ?";
              $this->db->customQuery($sql, [$userId, $invoiceId]);
              
              $response['msg'] = "e-MCF Invoice verified successfully!";
          }
          echo json_encode($response);

      } catch (EmcfException $e) {
          $this->logError('e-MCF Finalize Invoice Error: ' . $e->getMessage());
          echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
      } catch (Exception $e) {
          $this->logError("Finalize e-MCF Invoice Exception: " . $e->getMessage());
          echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
      }
  }

  private function saveInvoiceItems($invoiceId, $itemsJson)
  {
    try {
      if (empty($itemsJson)) {
        $this->logError("No items to save for invoice ID: $invoiceId");
        return true;
      }

      $items = json_decode($itemsJson, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->logError("JSON decode error: " . json_last_error_msg());
        return false;
      }
      
      if (!is_array($items) || count($items) === 0) {
        $this->logError("Items is not an array or empty");
        return true;
      }

      $this->db->customQuery("DELETE FROM import_invoice_items_t WHERE invoice_id = ?", [$invoiceId]);
      $this->logError("Deleted old items for invoice ID: $invoiceId");

      $sql = "INSERT INTO import_invoice_items_t 
              (invoice_id, quotation_item_id, category_id, category_name, category_header,
               item_id,item_name, item_description, unit_id, unit_name, unit_text,
               quantity, taux_usd, cost_usd, currency_id, currency_short_name,
               has_tva, tva_usd, subtotal_usd, total_usd,
               cif_split, percentage, rate_cdf, vat_cdf, total_cdf,
               sort_order, display, created_by)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Y', ?)";

      $userId = (int)($_SESSION['user_id'] ?? 1);
      $sortOrder = 0;
      $insertedCount = 0;

      foreach ($items as $item) {
        $params = [
          $invoiceId,
          isset($item['id']) && (int)$item['id'] > 0 ? (int)$item['id'] : null,
          isset($item['category_id']) && (int)$item['category_id'] > 0 ? (int)$item['category_id'] : null,
          $this->clean($item['category_name'] ?? ''),
          $this->clean($item['category_header'] ?? ''),
          isset($item['item_id']) && (int)$item['item_id'] > 0 ? (int)$item['item_id'] : null,
          $this->clean($item['item_name'] ?? ''),
          $this->clean($item['item_description'] ?? ''),
          isset($item['unit_id']) && (int)$item['unit_id'] > 0 ? (int)$item['unit_id'] : null,
          $this->clean($item['unit_name'] ?? ''),
          $this->clean($item['unit_text'] ?? $item['unit_name'] ?? 'Unit'),
          isset($item['quantity']) ? (float)$item['quantity'] : 1.00,
          isset($item['taux_usd']) ? (float)$item['taux_usd'] : (isset($item['cost_usd']) ? (float)$item['cost_usd'] : 0.00),
          isset($item['cost_usd']) ? (float)$item['cost_usd'] : (isset($item['taux_usd']) ? (float)$item['taux_usd'] : 0.00),
          isset($item['currency_id']) && (int)$item['currency_id'] > 0 ? (int)$item['currency_id'] : null,
          $this->clean($item['currency_short_name'] ?? 'USD'),
          isset($item['has_tva']) ? (int)$item['has_tva'] : 0,
          isset($item['tva_usd']) ? (float)$item['tva_usd'] : 0.00,
          isset($item['subtotal_usd']) ? (float)$item['subtotal_usd'] : 0.00,
          isset($item['total_usd']) ? (float)$item['total_usd'] : 0.00,
          isset($item['cif_split']) ? (float)$item['cif_split'] : 0.00,
          isset($item['percentage']) ? (float)$item['percentage'] : 0.0000,
          isset($item['rate_cdf']) ? (float)$item['rate_cdf'] : 0.00,
          isset($item['vat_cdf']) ? (float)$item['vat_cdf'] : 0.00,
          isset($item['total_cdf']) ? (float)$item['total_cdf'] : 0.00,
          $sortOrder++,
          $userId
        ];
        
        try {
          $this->db->customQuery($sql, $params);
          $insertedCount++;
        } catch (Exception $itemEx) {
          $this->logError("Error inserting item: " . $itemEx->getMessage());
        }
      }

      $this->logError("Successfully saved $insertedCount items for invoice ID: $invoiceId");
      return true;
      
    } catch (Exception $e) {
      $this->logError("Error in saveInvoiceItems: " . $e->getMessage());
      return false;
    }
  }

  private function getInvoiceItems($invoiceId)
  {
    try {
      $sql = "SELECT * FROM import_invoice_items_t
              WHERE invoice_id = ? AND display = 'Y'
              ORDER BY sort_order ASC";

      $items = $this->db->customQuery($sql, [$invoiceId]) ?: [];
      $this->logError("Retrieved " . count($items) . " items for invoice ID: $invoiceId");
      return $this->sanitizeArray($items);
      
    } catch (Exception $e) {
      $this->logError("Error getting invoice items: " . $e->getMessage());
      return [];
    }
  }


  
private function exportPendingMCAs()
{
    try {
        // Verify CSRF token
        $this->validateCSRFToken();
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            throw new Exception('PhpSpreadsheet library not found at: ' . $vendorPath);
        }
        require_once $vendorPath;

        // Get the data from POST
        $jsonData = $_POST['data'] ?? '[]';
        $mcasData = json_decode($jsonData, true);
        
        if (empty($mcasData)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No data to export']);
            exit;
        }
        
        $this->logError("Export Pending MCAs - Processing " . count($mcasData) . " MCAs");
        
        // Get detailed MCA data from database
        $mcaIds = array_column($mcasData, 'id');
        $placeholders = implode(',', array_fill(0, count($mcaIds), '?'));
        
        // Use basic query first to check what columns exist
        $sql = "SELECT i.id, i.mca_ref, i.fob, i.weight, i.quittance_date,
                       i.invoice, i.po_ref,
                       i.horse, i.trailer_1, i.trailer_2, i.container, i.wagon, i.airway_bill,
                       i.declaration_reference, i.dgda_in_date,
                       i.liquidation_reference, i.liquidation_date, i.liquidation_amount,
                       i.quittance_reference,
                       c.short_name as client_name,
                       cm.commodity_name,
                       l.license_number
                FROM imports_t i
                LEFT JOIN clients_t c ON i.subscriber_id = c.id
                LEFT JOIN commodity_master_t cm ON i.commodity = cm.id
                LEFT JOIN licenses_t l ON i.license_id = l.id
                WHERE i.id IN ($placeholders)
                ORDER BY i.id DESC";
        
        $this->logError("Executing query for " . count($mcaIds) . " MCA IDs");
        
        $detailedMCAs = $this->db->customQuery($sql, $mcaIds);
        
        if (empty($detailedMCAs)) {
            $this->logError("No MCA data found in database");
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No MCA data found in database']);
            exit;
        }
        
        $this->logError("Retrieved " . count($detailedMCAs) . " MCA records from database");
        
        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dossiers Non Facturés');
        
        // Create title with count
        $totalCount = count($detailedMCAs);
        $titleText = 'Dossiers Non Facturés - Total: ' . $totalCount;
        $sheet->setCellValue('A1', $titleText);
        $sheet->mergeCells('A1:W1');
        
        // Style title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F4F8']
            ]
        ];
        $sheet->getStyle('A1:W1')->applyFromArray($titleStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);
        
        // Set headers in row 2
        $headers = [
            'A2' => '#',
            'B2' => 'MCA File Ref.',
            'C2' => 'Tally Ref.',
            'D2' => 'Support Documents',
            'E2' => 'Lot Num. / Inv.No.',
            'F2' => 'PO.No.',
            'G2' => 'Client',
            'H2' => 'Commodity',
            'I2' => 'Truck / Wagon / AWB',
            'J2' => 'E.Ref.',
            'K2' => 'E.Date',
            'L2' => 'L.Ref.',
            'M2' => 'L.Date',
            'N2' => 'L.Amount',
            'O2' => 'Q.Ref.',
            'P2' => 'Q.Date',
            'Q2' => 'Delay',
            'R2' => 'Q.Encoding Date',
            'S2' => 'BS Date',
            'T2' => 'Dispatch/Delivery Date',
            'U2' => 'Clearing Status',
            'V2' => 'General Status',
            'W2' => 'Site'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
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
        ];
        $sheet->getStyle('A2:W2')->applyFromArray($headerStyle);
        $sheet->getRowDimension(2)->setRowHeight(35);
        
        // Add data rows starting from row 3
        $row = 3;
        $serialNumber = 1;
        
        foreach ($detailedMCAs as $mca) {
            // Calculate delay (days between quittance date and today)
            $delay = '';
            if (!empty($mca['quittance_date'])) {
                try {
                    $qDate = new DateTime($mca['quittance_date']);
                    $today = new DateTime();
                    $interval = $today->diff($qDate);
                    $delay = $interval->days;
                } catch (Exception $e) {
                    $delay = '';
                }
            }
            
            // Build truck/wagon/awb string
            $vehicle = '';
            if (!empty($mca['wagon'])) {
                $vehicle = trim($mca['wagon'] . ' / ' . ($mca['horse'] ?? '') . ' / ' . ($mca['trailer_1'] ?? ''), ' /');
            } else if (!empty($mca['airway_bill'])) {
                $vehicle = $mca['airway_bill'];
            } else {
                $vehicle = trim(($mca['horse'] ?? '') . ' / ' . ($mca['trailer_1'] ?? '') . ' / ' . ($mca['container'] ?? ''), ' /');
            }
            $vehicle = trim($vehicle, ' /');
            if (empty($vehicle)) $vehicle = 'N/A';
            
            // Format dates
            $eDate = !empty($mca['dgda_in_date']) ? date('d/m/Y', strtotime($mca['dgda_in_date'])) : '00/00/0000';
            $lDate = !empty($mca['liquidation_date']) ? date('d/m/Y', strtotime($mca['liquidation_date'])) : '00/00/0000';
            $qDate = !empty($mca['quittance_date']) ? date('d/m/Y', strtotime($mca['quittance_date'])) : '00/00/0000';
            
            // Format liquidation amount
            $lAmount = floatval($mca['liquidation_amount'] ?? 0);
            $lAmountFormatted = $lAmount > 0 ? number_format($lAmount, 2, ',', ' ') : '0';
            
            $rowData = [
                $serialNumber,
                $mca['mca_ref'] ?? '',
                '', // Tally Ref - not in imports_t
                'Unavailable', // Support Documents - default value
                $mca['invoice'] ?? 'N/A',
                $mca['po_ref'] ?? '',
                $mca['client_name'] ?? '',
                $mca['commodity_name'] ?? 'DIVERS',
                $vehicle,
                $mca['declaration_reference'] ?? '',
                $eDate,
                $mca['liquidation_reference'] ?? '',
                $lDate,
                $lAmountFormatted,
                $mca['quittance_reference'] ?? '',
                $qDate,
                $delay,
                '', // Q.Encoding Date - not in imports_t
                '', // BS Date - not in imports_t
                '', // Dispatch/Delivery Date - not in imports_t
                'Cleared', // Clearing Status - default
                'CLEARING COMPLETED', // General Status - default
                'Lubumbashi' // Site - default
            ];
            
            $sheet->fromArray([$rowData], null, 'A' . $row);
            
            // Center align specific columns
            $sheet->getStyle('A' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $row . ':M' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('P' . $row . ':T' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Right align amount
            $sheet->getStyle('N' . $row)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            
            $row++;
            $serialNumber++;
        }
        
        $this->logError("Added " . ($row - 3) . " rows to Excel");
        
        // Add borders to all data
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0']
                ]
            ]
        ];
        $sheet->getStyle('A2:W' . ($row - 1))->applyFromArray($styleArray);
        
        // Set column widths
        $columnWidths = [
            'A' => 5,   'B' => 18,  'C' => 20,  'D' => 15,  'E' => 15,  'F' => 20,
            'G' => 25,  'H' => 15,  'I' => 30,  'J' => 12,  'K' => 12,  'L' => 12,
            'M' => 12,  'N' => 15,  'O' => 12,  'P' => 12,  'Q' => 8,   'R' => 20,
            'S' => 12,  'T' => 20,  'U' => 15,  'V' => 20,  'W' => 15
        ];
        
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        
        // Freeze header rows
        $sheet->freezePane('A3');
        
        // Generate filename with timestamp
        $filename = 'Dossiers_Non_Facturés_' . date('Y_m_d_H_i_s') . '.xlsx';
        
        $this->logError("Generating Excel file: " . $filename);
        
        ob_end_clean();
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Write file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        
        $this->logError("Excel file generated successfully");
        
        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
        
    } catch (Exception $e) {
        $this->logError("Export Pending MCAs CRITICAL ERROR: " . $e->getMessage());
        $this->logError("Stack trace: " . $e->getTraceAsString());
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Export failed: ' . $e->getMessage()
        ]);
        exit;
    }
}
  private function generateNextInvoiceRef($clientId)
  {
    try {
      $clientResult = $this->db->customQuery("SELECT short_name FROM clients_t WHERE id = ? LIMIT 1", [$clientId]);
      if (empty($clientResult)) throw new Exception("Client not found");
      
      $shortName = strtoupper($clientResult[0]['short_name']);
      $year = date('Y');
      
      $result = $this->db->customQuery(
        "SELECT invoice_ref FROM import_invoices_t WHERE client_id = ? AND invoice_ref LIKE ? ORDER BY id DESC LIMIT 1",
        [$clientId, "$year-$shortName-%"]
      );
      
      $nextNumber = 1;
      if (!empty($result)) {
        preg_match('/(\d{4})$/i', $result[0]['invoice_ref'], $matches);
        $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
      }
      
      return sprintf('%s-%s-%04d', $year, $shortName, $nextNumber);
    } catch (Exception $e) {
      $this->logError("Error generating next invoice ref: " . $e->getMessage());
      return date('Y') . '-XXX-0001';
    }
  }

  private function getNextInvoiceRefForClient()
  {
    $clientId = (int)($_GET['client_id'] ?? 0);
    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID']);
      return;
    }
    echo json_encode(['success' => true, 'invoice_ref' => $this->generateNextInvoiceRef($clientId)]);
  }

private function getStatistics()
{
    try {
      $sql = "SELECT COUNT(*) as total_invoices,
                SUM(CASE WHEN validated = 1 THEN 1 ELSE 0 END) as validated_invoices,
                SUM(CASE WHEN validated = 0 THEN 1 ELSE 0 END) as not_validated_invoices,
                SUM(CASE WHEN validated = 2 THEN 1 ELSE 0 END) as dgi_verified_invoices,
                SUM(CASE WHEN MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_month_count
              FROM import_invoices_t";
      $stats = $this->db->customQuery($sql);
      
      // ⭐ CORRECTED QUERY - Using NOT EXISTS with FIND_IN_SET
      $mcaNotInvoicedSql = "SELECT COUNT(DISTINCT i.id) as not_invoiced_count
                            FROM imports_t i
                            WHERE i.display = 'Y'
                            AND i.quittance_date IS NOT NULL
                            AND i.quittance_date != ''
                            AND i.kind IN (1, 2, 5, 6)
                            AND NOT EXISTS (
                              SELECT 1 
                              FROM import_invoices_t inv
                              WHERE FIND_IN_SET(i.id, inv.mca_ids) > 0
                            )";
      
      $mcaStats = $this->db->customQuery($mcaNotInvoicedSql);
      $notInvoicedCount = (int)($mcaStats[0]['not_invoiced_count'] ?? 0);
      
      echo json_encode(['success' => true, 'data' => [
        'total_invoices' => (int)($stats[0]['total_invoices'] ?? 0),
        'validated_invoices' => (int)($stats[0]['validated_invoices'] ?? 0),
        'not_validated_invoices' => (int)($stats[0]['not_validated_invoices'] ?? 0),
        'dgi_verified_invoices' => (int)($stats[0]['dgi_verified_invoices'] ?? 0),
        'this_month_count' => (int)($stats[0]['this_month_count'] ?? 0),
        'not_invoiced_mcas' => $notInvoicedCount
      ]]);
    } catch (Exception $e) {
      $this->logError("Error getting statistics: " . $e->getMessage());
      echo json_encode(['success' => true, 'data' => [
        'total_invoices' => 0, 
        'validated_invoices' => 0, 
        'not_validated_invoices' => 0, 
        'dgi_verified_invoices' => 0,
        'this_month_count' => 0,
        'not_invoiced_mcas' => 0
      ]]);
    }
}

private function getLicenses()
{
  try {
    $clientId = (int)($_GET['client_id'] ?? 0);
    $invoiceId = (int)($_GET['invoice_id'] ?? 0); // ✅ Get current invoice ID if editing

    if ($clientId <= 0) {
      echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => []]);
      return;
    }

    // Get all used MCA IDs (already invoiced) EXCLUDING current invoice if editing
    if ($invoiceId > 0) {
      // ✅ Edit mode - exclude MCAs from current invoice
      $usedMcaSql = "SELECT DISTINCT mca_id FROM export_invoice_mca_details_t 
                     WHERE mca_id IS NOT NULL AND export_invoice_id != ?";
      $usedMcaResults = $this->db->customQuery($usedMcaSql, [$invoiceId]) ?: [];
    } else {
      // New invoice mode - get all used MCAs
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

    // ✅ ONLY get licenses that have available MCAs with quittance dates
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
      $licenseIds = $_GET['license_ids'] ?? '';
      $currentInvoiceId = (int)($_GET['current_invoice_id'] ?? 0);
      
      $this->logError("getMCAReferences called: clientId=$clientId, licenseIds=$licenseIds, currentInvoiceId=$currentInvoiceId");
      
      if ($clientId <= 0 || empty($licenseIds)) {
        $this->logError("Invalid parameters provided");
        echo json_encode(['success' => false, 'message' => 'Invalid parameters', 'data' => []]);
        return;
      }
      
      $licenseIdArray = array_filter(array_map('intval', explode(',', $licenseIds)));
      
      if (empty($licenseIdArray)) {
        $this->logError("No valid license IDs");
        echo json_encode(['success' => false, 'message' => 'No valid license IDs', 'data' => []]);
        return;
      }
      
      $placeholders = implode(',', array_fill(0, count($licenseIdArray), '?'));
      
      // Base query to get MCAs
      $sql = "SELECT i.id, i.mca_ref, i.fob, i.weight, i.commodity,
                     DATE_FORMAT(i.customs_manifest_date, '%Y-%m-%d') as customs_manifest_date
              FROM imports_t i 
              WHERE i.license_id IN ($placeholders)
              AND i.subscriber_id = ? 
              AND i.display = 'Y'
              AND i.quittance_date IS NOT NULL
              AND i.quittance_date != ''";
      
      $params = array_merge($licenseIdArray, [$clientId]);
      
      if ($currentInvoiceId > 0) {
        // When editing: Exclude MCAs in OTHER invoices, but include MCAs in current invoice
        $sql .= " AND (
                    i.id NOT IN (
                      SELECT ii.id 
                      FROM imports_t ii
                      INNER JOIN import_invoices_t inv ON FIND_IN_SET(ii.id, inv.mca_ids) > 0
                      WHERE inv.id != ?
                    )
                    OR i.id IN (
                      SELECT ii2.id
                      FROM imports_t ii2
                      INNER JOIN import_invoices_t inv2 ON FIND_IN_SET(ii2.id, inv2.mca_ids) > 0
                      WHERE inv2.id = ?
                    )
                  )";
        $params[] = $currentInvoiceId;
        $params[] = $currentInvoiceId;
      } else {
        // When creating new: Exclude all already-invoiced MCAs
        $sql .= " AND i.id NOT IN (
                    SELECT ii.id 
                    FROM imports_t ii
                    INNER JOIN import_invoices_t inv ON FIND_IN_SET(ii.id, inv.mca_ids) > 0
                  )";
      }
      
      $sql .= " ORDER BY i.id DESC LIMIT 100";
      
      $this->logError("Executing SQL query for MCAs with quittance_date filter for " . count($licenseIdArray) . " licenses");
      $result = $this->db->customQuery($sql, $params);
      
      $this->logError("Found " . count($result ?: []) . " available MCA references");
      
      echo json_encode(['success' => true, 'data' => $this->sanitizeArray($result ?: [])]);
    } catch (Exception $e) {
      $this->logError("Error getting MCA references: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load MCA references', 'data' => []]);
    }
}

private function getMCADetails()
{
  try {
    $mcaIds = $_GET['mca_ids'] ?? '';
    
    if (empty($mcaIds)) {
      echo json_encode(['success' => false, 'message' => 'No MCA IDs provided', 'data' => []]);
      return;
    }
    
    $mcaIdArray = array_map('intval', explode(',', $mcaIds));
    $mcaIdArray = array_filter($mcaIdArray, function($id) { return $id > 0; });
    
    if (empty($mcaIdArray)) {
      echo json_encode(['success' => false, 'message' => 'Invalid MCA IDs', 'data' => []]);
      return;
    }
    
    $placeholders = implode(',', array_fill(0, count($mcaIdArray), '?'));
    
   $sql = "SELECT i.id, i.mca_ref, i.fob, i.fret, i.weight, i.m3, i.supplier,
          i.currency as currency_id, 
          l.kind_id as kind_id,
          l.type_of_goods_id as goods_type_id,
          i.transport_mode as transport_mode_id,
          i.horse, i.trailer_1, i.trailer_2, i.container, i.wagon, i.airway_bill, i.airway_bill_weight,
          i.invoice as facture_pfi_no, i.po_ref, i.inspection_reports as bivac_inspection,
          i.declaration_reference as declaration_no, 
          i.liquidation_reference as liquidation_no, 
          i.liquidation_date,
          i.liquidation_amount,
          i.quittance_reference as quittance_no, 
          i.quittance_date, 
          i.dgda_out_date as dispatch_deliver_date,
          i.dgda_in_date,
          i.customs_manifest_date,
          cm.commodity_name as commodity,
          k.kind_name, 
          tg.goods_type as type_of_goods_name,
          tm.transport_mode_name, 
          curr.currency_short_name
        FROM imports_t i
        LEFT JOIN licenses_t l ON i.license_id = l.id
        LEFT JOIN kind_master_t k ON l.kind_id = k.id
        LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
        LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id
        LEFT JOIN currency_master_t curr ON i.currency = curr.id
        LEFT JOIN commodity_master_t cm ON i.commodity = cm.id
        WHERE i.id IN ($placeholders) AND i.display = 'Y'
        GROUP BY i.id
        ORDER BY i.id DESC";
            
    $mcaDetails = $this->db->customQuery($sql, $mcaIdArray);
    
    if (empty($mcaDetails)) {
      echo json_encode(['success' => false, 'message' => 'MCA not found', 'data' => []]);
      return;
    }
    
    if (!empty($mcaDetails) && isset($mcaDetails[0]['kind_id'])) {
      $clientId = $_GET['client_id'] ?? 0;
      if ($clientId > 0) {
        $arspQuery = "SELECT arsp FROM quotations_t WHERE client_id = ? LIMIT 1";
        $arspResult = $this->db->customQuery($arspQuery, [$clientId]);
        $arsp = !empty($arspResult) ? $arspResult[0]['arsp'] : null;
        
        foreach ($mcaDetails as &$mca) {
          $mca['arsp'] = $arsp;
        }
      }
    }
    
    $this->logError("=== MCA DETAILS DEBUG ===");
    $this->logError("Total unique MCAs returned: " . count($mcaDetails));
    foreach ($mcaDetails as $idx => $mca) {
      $this->logError("MCA #{$idx} (ID: {$mca['id']}, Ref: {$mca['mca_ref']}): kind_id={$mca['kind_id']}, kind_name={$mca['kind_name']}, goods_type_id={$mca['goods_type_id']}, type_of_goods_name={$mca['type_of_goods_name']}, transport_mode_id={$mca['transport_mode_id']}, transport_mode_name={$mca['transport_mode_name']}");
    }
    
    $mergedData = $this->mergeMCAData($mcaDetails);
    
    $this->logError("MERGED kind_id: " . ($mergedData['kind_id'] ?? 'NULL') . ", kind_name: " . ($mergedData['kind_name'] ?? 'NULL'));
    $this->logError("=== END DEBUG ===");
    
    echo json_encode(['success' => true, 'data' => $this->sanitizeArray($mergedData), 'all_mcas' => $this->sanitizeArray($mcaDetails)]);
  } catch (Exception $e) {
    $this->logError("Error getting MCA details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load MCA details', 'data' => []]);
  }
}
private function mergeMCAData($mcaDetailsArray)
{
  if (empty($mcaDetailsArray)) return [];
  
  $merged = $mcaDetailsArray[0];
  
  $mcaRefs = [];
  $totalFob = 0;
  $totalFret = 0;
  $totalWeight = 0;
  $totalM3 = 0;
  $totalLiquidationAmount = 0;
  $commodities = [];
  $suppliers = [];
  
  foreach ($mcaDetailsArray as $mca) {
    $mcaRefs[] = $mca['mca_ref'];
    $totalFob += (float)($mca['fob'] ?? 0);
    $totalFret += (float)($mca['fret'] ?? 0);
    $totalWeight += (float)($mca['weight'] ?? 0);
    $totalM3 += (float)($mca['m3'] ?? 0);
    $totalLiquidationAmount += (float)($mca['liquidation_amount'] ?? 0);
    
    if (!empty($mca['commodity']) && !in_array($mca['commodity'], $commodities)) {
      $commodities[] = $mca['commodity'];
    }
    if (!empty($mca['supplier']) && !in_array($mca['supplier'], $suppliers)) {
      $suppliers[] = $mca['supplier'];
    }
  }
  
  $merged['mca_ref'] = implode(', ', $mcaRefs);
  $merged['fob'] = $totalFob;
  $merged['fret'] = $totalFret;
  $merged['weight'] = $totalWeight;
  $merged['m3'] = $totalM3;
  $merged['liquidation_amount'] = $totalLiquidationAmount;
  $merged['commodity'] = implode(', ', $commodities);
  $merged['supplier'] = implode(', ', $suppliers);
  
  return $merged;
}


  private function getBanks()
  {
    try {
      $clientId = (int)($_GET['client_id'] ?? 0);
      if ($clientId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid client ID', 'data' => []]);
        return;
      }
      
      $sql = "SELECT ibm.id, ibm.invoice_bank_name as bank_name, 
                     ibm.invoice_bank_account_name as account_name,
                     ibm.invoice_bank_account_number as account_number, 
                     ibm.invoice_bank_swift as swift_code,
                     ibm.invoice_bank_address as branch
              FROM client_bank_mapping_t cbm
              INNER JOIN invoice_bank_master_t ibm ON cbm.bank_id = ibm.id
              WHERE cbm.client_id = ? AND ibm.display = 'Y' 
              ORDER BY cbm.id ASC";
      
      $banks = $this->db->customQuery($sql, [$clientId]) ?: [];
      
      echo json_encode(['success' => true, 'data' => $this->sanitizeArray($banks)]);
    } catch (Exception $e) {
      $this->logError("Error getting banks: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load banks', 'data' => []]);
    }
  }

  private function listInvoices()
  {
    try {
      $draw = (int)($_GET['draw'] ?? 1);
      $start = (int)($_GET['start'] ?? 0);
      $length = (int)($_GET['length'] ?? 25);
      $searchValue = $this->sanitizeInput(trim($_GET['search']['value'] ?? ''));
      $filter = $this->sanitizeInput($_GET['filter'] ?? 'all');
      $orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0);
      $orderDirection = (strtolower($_GET['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';

      $columns = ['inv.id', 'inv.invoice_ref', 'c.short_name', 'tg.goods_type', 'inv.created_at', 'u.username', 'inv.calculated_total_amount', 'inv.validated'];
      $orderColumn = ($orderColumnIndex > 0) ? $columns[$orderColumnIndex] : 'inv.created_at';

      $baseQuery = "FROM import_invoices_t inv 
                    LEFT JOIN clients_t c ON inv.client_id = c.id 
                    LEFT JOIN users_t u ON inv.created_by = u.id
                    LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                    WHERE 1=1";
      
      $filterCondition = "";
      if ($filter === 'validated') $filterCondition = " AND inv.validated = 1";
      elseif ($filter === 'not-validated') $filterCondition = " AND inv.validated = 0";
      elseif ($filter === 'dgi-verified') $filterCondition = " AND inv.validated = 2";

      $searchCondition = "";
      $params = [];
      if (!empty($searchValue)) {
        $searchCondition = " AND (inv.invoice_ref LIKE ? OR inv.mca_ids LIKE ? OR c.short_name LIKE ? OR u.username LIKE ? OR tg.goods_type LIKE ?)";
        $searchParam = "%{$searchValue}%";
        $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
      }

      $totalResult = $this->db->customQuery("SELECT COUNT(*) as total FROM import_invoices_t");
      $totalRecords = (int)($totalResult[0]['total'] ?? 0);
      
      $filteredResult = $this->db->customQuery("SELECT COUNT(*) as total {$baseQuery} {$filterCondition} {$searchCondition}", $params);
      $filteredRecords = (int)($filteredResult[0]['total'] ?? 0);

      $dataSql = "SELECT inv.id, inv.invoice_ref, inv.cif_usd, inv.calculated_total_amount, 
                         inv.validated, inv.mca_ids, tg.goods_type as type_of_goods,
                         c.short_name as client_name, 
                         inv.created_at, u.username as created_by_name 
                  {$baseQuery} {$filterCondition} {$searchCondition} 
                  ORDER BY {$orderColumn} {$orderDirection} 
                  LIMIT {$length} OFFSET {$start}";
      $invoices = $this->db->customQuery($dataSql, $params);

      echo json_encode(['draw' => $draw, 'recordsTotal' => $totalRecords, 'recordsFiltered' => $filteredRecords, 'data' => $this->sanitizeArray($invoices ?: [])]);
    } catch (Exception $e) {
      $this->logError("Error listing invoices: " . $e->getMessage());
      echo json_encode(['draw' => 1, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
    }
  }

private function insertInvoice()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $this->logError("========== INSERT INVOICE START ==========");
      
      $validation = $this->validateInvoiceData($_POST);
      if (!$validation['success']) {
        $this->logError("Validation failed: " . $validation['message']);
        echo json_encode($validation);
        return;
      }

      $userId = (int)($_SESSION['user_id'] ?? 1);
      
      $licenseIds = $_POST['license_ids'] ?? '';
      $licenseIdArray = array_filter(array_map('intval', explode(',', $licenseIds)));
      $primaryLicenseId = !empty($licenseIdArray) ? $licenseIdArray[0] : null;
      
      $mcaIds = $_POST['mca_ids'] ?? '';
      $mcaIdArray = array_filter(array_map('intval', explode(',', $mcaIds)));
      $primaryMcaId = !empty($mcaIdArray) ? $mcaIdArray[0] : null;
      
      $hiddenCategories = $_POST['hidden_categories'] ?? '[]';
      
      $calculatedSubTotal = $this->toDecimal($_POST['calculated_sub_total'] ?? 0);
      $calculatedVatAmount = $this->toDecimal($_POST['calculated_vat_amount'] ?? 0);
      $calculatedTotalAmount = $this->toDecimal($_POST['calculated_total_amount'] ?? 0);
      $calculatedTotalCdf = $this->toDecimal($_POST['calculated_total_cdf'] ?? 0);
      $itemsManuallyEdited = !empty($_POST['items_manually_edited']) ? 1 : 0;
      
      $paymentMethod = $this->clean($_POST['payment_method'] ?? 'CREDIT');
      
      $sql = "INSERT INTO import_invoices_t (
                client_id, license_id, license_ids, mca_id, mca_ids, kind_id, goods_type_id, transport_mode_id,
                invoice_ref, tally_ref, payment_method,
                fob_currency_id, fob_usd, fret_currency_id, fret_usd,
                assurance_currency_id, assurance_usd, autres_charges_currency_id, autres_charges_usd,
                rate_cdf_inv, rate_cdf_usd_bcc, cif_usd, cif_cdf, total_duty_cdf, poids_kg, m3,
                tariff_code_client, horse, trailer_1, trailer_2, container, wagon,
                airway_bill, airway_bill_weight, facture_pfi_no, po_ref, bivac_inspection,
                produit, exoneration_code, declaration_no, declaration_date, liquidation_no,
                liquidation_date, quittance_no, quittance_date, dispatch_deliver_date,
                bank_id, quotation_id, quotation_sub_total, quotation_vat_amount, quotation_total_amount,
                calculated_sub_total, calculated_vat_amount, calculated_total_amount, calculated_total_cdf,
                items_manually_edited, first_categoty_edited, invoice_template, arsp, validated, hidden_categories, created_by, updated_by
              ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, 0, ?, ?, ?
              )";

      $params = [
        $this->toInt($_POST['client_id']),
        $primaryLicenseId,
        $licenseIds,
        $primaryMcaId,
        $mcaIds,
        $this->toInt($_POST['kind_id'] ?? null),
        $this->toInt($_POST['goods_type_id'] ?? null),
        $this->toInt($_POST['transport_mode_id'] ?? null),
        $this->clean($_POST['invoice_ref']),
        $this->clean($_POST['tally_ref'] ?? null),
        $paymentMethod,
        $this->toInt($_POST['fob_currency_id'] ?? null),
        $this->toDecimal($_POST['fob_usd'] ?? 0),
        $this->toInt($_POST['fret_currency_id'] ?? null),
        $this->toDecimal($_POST['fret_usd'] ?? 0),
        $this->toInt($_POST['assurance_currency_id'] ?? null),
        $this->toDecimal($_POST['assurance_usd'] ?? 0),
        $this->toInt($_POST['autres_charges_currency_id'] ?? null),
        $this->toDecimal($_POST['autres_charges_usd'] ?? 0),
        $this->toDecimal4($_POST['rate_cdf_inv'] ?? 2500),
        $this->toDecimal4($_POST['rate_cdf_usd_bcc'] ?? 2500),
        $this->toDecimal($_POST['cif_usd'] ?? 0),
        $this->toDecimal($_POST['cif_cdf'] ?? 0),
        $this->toDecimal($_POST['total_duty_cdf'] ?? 0),
        $this->toDecimal($_POST['poids_kg'] ?? 0),
        $this->toDecimal($_POST['m3'] ?? null),
        $this->clean($_POST['tariff_code_client'] ?? null),
        $this->clean($_POST['horse'] ?? null),
        $this->clean($_POST['trailer_1'] ?? null),
        $this->clean($_POST['trailer_2'] ?? null),
        $this->clean($_POST['container'] ?? null),
        $this->clean($_POST['wagon'] ?? null),
        $this->clean($_POST['airway_bill'] ?? null),
        $this->toDecimal($_POST['airway_bill_weight'] ?? null),
        $this->clean($_POST['facture_pfi_no'] ?? null),
        $this->clean($_POST['po_ref'] ?? null),
        $this->clean($_POST['bivac_inspection'] ?? null),
        $this->clean($_POST['produit'] ?? 'Default Commodity'),
        $this->clean($_POST['exoneration_code'] ?? null),
        $this->clean($_POST['declaration_no'] ?? null),
        $this->toDate($_POST['declaration_date'] ?? null),
        $this->clean($_POST['liquidation_no'] ?? null),
        $this->toDate($_POST['liquidation_date'] ?? null),
        $this->clean($_POST['quittance_no'] ?? null),
        $this->toDate($_POST['quittance_date'] ?? null),
        $this->toDate($_POST['dispatch_deliver_date'] ?? null),
        $this->toInt($_POST['bank_id'] ?? null),
        $this->toInt($_POST['quotation_id'] ?? null),
        $this->toDecimal($_POST['quotation_sub_total'] ?? 0),
        $this->toDecimal($_POST['quotation_vat_amount'] ?? 0),
        $this->toDecimal($_POST['quotation_total_amount'] ?? 0),
        $calculatedSubTotal,
        $calculatedVatAmount,
        $calculatedTotalAmount,
        $calculatedTotalCdf,
        $itemsManuallyEdited,
        $this->clean($_POST['first_categoty_edited'] ?? 'H'),
        $this->clean($_POST['invoice_template'] ?? null),
        $this->clean($_POST['arsp'] ?? null),
        $hiddenCategories,
        $userId,
        $userId
      ];
      
      $this->db->customQuery($sql, $params);
      
      $lastIdResult = $this->db->customQuery("SELECT LAST_INSERT_ID() as id");
      $insertId = (int)($lastIdResult[0]['id'] ?? 0);

      if ($insertId > 0) {
        $itemsJson = $_POST['quotation_items'] ?? '';
        if (!empty($itemsJson)) {
          $this->saveInvoiceItems($insertId, $itemsJson);
        }

        $this->logError("========== INSERT SUCCESS ========== ID: " . $insertId);
        $this->logError("License IDs: " . $licenseIds);
        $this->logError("MCA IDs: " . $mcaIds);
        $this->logError("Payment Method: " . $paymentMethod);
        $this->logError("Tally Ref: " . ($_POST['tally_ref'] ?? 'N/A'));
        
        echo json_encode(['success' => true, 'message' => 'Invoice created successfully!', 'id' => $insertId]);
      } else {
        $this->logError("ERROR: Invoice insert failed");
        echo json_encode(['success' => false, 'message' => 'Failed to create invoice']);
      }

    } catch (Exception $e) {
      $this->logError("INSERT Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }
  }

 private function updateInvoice()
{ 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      echo json_encode(['success' => false, 'message' => 'Invalid request method']);
      return;
    }

    $this->validateCsrfToken();

    try {
      $invoiceId = (int)($_POST['invoice_id'] ?? 0);
      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id FROM import_invoices_t WHERE id = ?", [$invoiceId]);
      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $validation = $this->validateInvoiceData($_POST, $invoiceId);
      if (!$validation['success']) {
        echo json_encode($validation);
        return;
      }

      $licenseIds = $_POST['license_ids'] ?? '';
      $licenseIdArray = array_filter(array_map('intval', explode(',', $licenseIds)));
      $primaryLicenseId = !empty($licenseIdArray) ? $licenseIdArray[0] : null;
      
      $mcaIds = $_POST['mca_ids'] ?? '';
      $mcaIdArray = array_filter(array_map('intval', explode(',', $mcaIds)));
      $primaryMcaId = !empty($mcaIdArray) ? $mcaIdArray[0] : null;
      
      $hiddenCategories = $_POST['hidden_categories'] ?? '[]';
      
      $calculatedSubTotal = $this->toDecimal($_POST['calculated_sub_total'] ?? 0);
      $calculatedVatAmount = $this->toDecimal($_POST['calculated_vat_amount'] ?? 0);
      $calculatedTotalAmount = $this->toDecimal($_POST['calculated_total_amount'] ?? 0);
      $calculatedTotalCdf = $this->toDecimal($_POST['calculated_total_cdf'] ?? 0);
      $itemsManuallyEdited = !empty($_POST['items_manually_edited']) ? 1 : 0;
      
      $paymentMethod = $this->clean($_POST['payment_method'] ?? 'CREDIT');

      $sql = "UPDATE import_invoices_t SET
                client_id = ?, license_id = ?, license_ids = ?, mca_id = ?, mca_ids = ?, kind_id = ?, goods_type_id = ?, transport_mode_id = ?,
                invoice_ref = ?, tally_ref = ?, payment_method = ?, 
                fob_currency_id = ?, fob_usd = ?, fret_currency_id = ?, fret_usd = ?,
                assurance_currency_id = ?, assurance_usd = ?, autres_charges_currency_id = ?, autres_charges_usd = ?,
                rate_cdf_inv = ?, rate_cdf_usd_bcc = ?, cif_usd = ?, cif_cdf = ?, total_duty_cdf = ?, poids_kg = ?, m3 = ?,
                tariff_code_client = ?, horse = ?, trailer_1 = ?, trailer_2 = ?, container = ?, wagon = ?,
                airway_bill = ?, airway_bill_weight = ?, facture_pfi_no = ?, po_ref = ?, bivac_inspection = ?,
                produit = ?, exoneration_code = ?, declaration_no = ?, declaration_date = ?, liquidation_no = ?,
                liquidation_date = ?, quittance_no = ?, quittance_date = ?, dispatch_deliver_date = ?,
                bank_id = ?, quotation_id = ?, quotation_sub_total = ?, quotation_vat_amount = ?, quotation_total_amount = ?,
                calculated_sub_total = ?, calculated_vat_amount = ?, calculated_total_amount = ?, calculated_total_cdf = ?,
                items_manually_edited = ?, first_categoty_edited = ?, invoice_template = ?, arsp = ?, hidden_categories = ?, updated_by = ?, updated_at = NOW()
              WHERE id = ?";

      $params = [
        $this->toInt($_POST['client_id']),
        $primaryLicenseId,
        $licenseIds,
        $primaryMcaId,
        $mcaIds,
        $this->toInt($_POST['kind_id'] ?? null),
        $this->toInt($_POST['goods_type_id'] ?? null),
        $this->toInt($_POST['transport_mode_id'] ?? null),
        $this->clean($_POST['invoice_ref']),
        $this->clean($_POST['tally_ref'] ?? null),
        $paymentMethod,
        $this->toInt($_POST['fob_currency_id'] ?? null),
        $this->toDecimal($_POST['fob_usd'] ?? 0),
        $this->toInt($_POST['fret_currency_id'] ?? null),
        $this->toDecimal($_POST['fret_usd'] ?? 0),
        $this->toInt($_POST['assurance_currency_id'] ?? null),
        $this->toDecimal($_POST['assurance_usd'] ?? 0),
        $this->toInt($_POST['autres_charges_currency_id'] ?? null),
        $this->toDecimal($_POST['autres_charges_usd'] ?? 0),
        $this->toDecimal4($_POST['rate_cdf_inv'] ?? 2500),
        $this->toDecimal4($_POST['rate_cdf_usd_bcc'] ?? 2500),
        $this->toDecimal($_POST['cif_usd'] ?? 0),
        $this->toDecimal($_POST['cif_cdf'] ?? 0),
        $this->toDecimal($_POST['total_duty_cdf'] ?? 0),
        $this->toDecimal($_POST['poids_kg'] ?? 0),
        $this->toDecimal($_POST['m3'] ?? null),
        $this->clean($_POST['tariff_code_client'] ?? null),
        $this->clean($_POST['horse'] ?? null),
        $this->clean($_POST['trailer_1'] ?? null),
        $this->clean($_POST['trailer_2'] ?? null),
        $this->clean($_POST['container'] ?? null),
        $this->clean($_POST['wagon'] ?? null),
        $this->clean($_POST['airway_bill'] ?? null),
        $this->toDecimal($_POST['airway_bill_weight'] ?? null),
        $this->clean($_POST['facture_pfi_no'] ?? null),
        $this->clean($_POST['po_ref'] ?? null),
        $this->clean($_POST['bivac_inspection'] ?? null),
        $this->clean($_POST['produit'] ?? 'Default Commodity'),
        $this->clean($_POST['exoneration_code'] ?? null),
        $this->clean($_POST['declaration_no'] ?? null),
        $this->toDate($_POST['declaration_date'] ?? null),
        $this->clean($_POST['liquidation_no'] ?? null),
        $this->toDate($_POST['liquidation_date'] ?? null),
        $this->clean($_POST['quittance_no'] ?? null),
        $this->toDate($_POST['quittance_date'] ?? null),
        $this->toDate($_POST['dispatch_deliver_date'] ?? null),
        $this->toInt($_POST['bank_id'] ?? null),
        $this->toInt($_POST['quotation_id'] ?? null),
        $this->toDecimal($_POST['quotation_sub_total'] ?? 0),
        $this->toDecimal($_POST['quotation_vat_amount'] ?? 0),
        $this->toDecimal($_POST['quotation_total_amount'] ?? 0),
        $calculatedSubTotal,
        $calculatedVatAmount,
        $calculatedTotalAmount,
        $calculatedTotalCdf,
        $itemsManuallyEdited,
        $this->clean($_POST['first_categoty_edited'] ?? 'H'),
        $this->clean($_POST['invoice_template'] ?? null),
        $this->clean($_POST['arsp'] ?? null),
        $hiddenCategories,
        (int)($_SESSION['user_id'] ?? 1),
        $invoiceId
      ];
      
      $this->db->customQuery($sql, $params);

      $itemsJson = $_POST['quotation_items'] ?? '';
      if (!empty($itemsJson)) {
        $this->saveInvoiceItems($invoiceId, $itemsJson);
      }

      $this->logError("Invoice ID $invoiceId updated successfully");
      $this->logError("License IDs: " . $licenseIds);
      $this->logError("MCA IDs: " . $mcaIds);
      $this->logError("Payment Method: " . $paymentMethod);
      $this->logError("Tally Ref: " . ($_POST['tally_ref'] ?? 'N/A'));

      echo json_encode(['success' => true, 'message' => 'Invoice updated successfully!']);

    } catch (Exception $e) {
      $this->logError("Update Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }


  private function getClientGroupedInvoices()
{
    try {
        $filter = $_GET['filter'] ?? 'all';
        
        $filterCondition = "";
        if ($filter === 'validated') $filterCondition = " AND inv.validated = 1";
        elseif ($filter === 'not-validated') $filterCondition = " AND inv.validated = 0";
        elseif ($filter === 'dgi-verified') $filterCondition = " AND inv.validated = 2";
        
        $sql = "SELECT inv.id, inv.invoice_ref, inv.cif_usd, inv.calculated_total_amount, 
                       inv.validated, inv.mca_ids, inv.created_at,
                       tg.goods_type as type_of_goods,
                       c.short_name as client_name
                FROM import_invoices_t inv
                LEFT JOIN clients_t c ON inv.client_id = c.id
                LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                WHERE 1=1 {$filterCondition}
                ORDER BY c.short_name ASC, inv.created_at DESC";
        
        $invoices = $this->db->customQuery($sql);
        
        echo json_encode(['success' => true, 'data' => $this->sanitizeArray($invoices ?: [])]);
    } catch (Exception $e) {
        $this->logError("Error getting client-grouped invoices: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load invoices', 'data' => []]);
    }
}
  private function deleteInvoice()
  {
    $this->validateCsrfToken();
    
    try {
      $invoiceId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $existing = $this->db->customQuery("SELECT id FROM import_invoices_t WHERE id = ?", [$invoiceId]);
      if (empty($existing)) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $this->db->customQuery("DELETE FROM import_invoice_items_t WHERE invoice_id = ?", [$invoiceId]);
      $this->db->customQuery("DELETE FROM import_invoices_t WHERE id = ?", [$invoiceId]);
      
      echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully!']);

    } catch (Exception $e) {
      $this->logError("Delete Exception: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
  }

  private function getInvoice()
  {
    try {
      $invoiceId = (int)($_GET['id'] ?? 0);
      if ($invoiceId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        return;
      }

      $sql = "SELECT inv.*, c.short_name as client_name, c.company_name, l.license_number
              FROM import_invoices_t inv
              LEFT JOIN clients_t c ON inv.client_id = c.id
              LEFT JOIN licenses_t l ON inv.license_id = l.id
              WHERE inv.id = ?";
      $invoice = $this->db->customQuery($sql, [$invoiceId]);
      
      if (!empty($invoice)) {
        $invoiceData = $this->sanitizeArray($invoice)[0];
        $items = $this->getInvoiceItems($invoiceId);
        
        echo json_encode([
          'success' => true, 
          'data' => $invoiceData, 
          'items' => $items
        ]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
      }
    } catch (Exception $e) {
      $this->logError("Error getting invoice: " . $e->getMessage());
      echo json_encode(['success' => false, 'message' => 'Failed to load invoice data']);
    }
  }

  private function exportInvoice()
  {
    $invoiceId = (int)($_GET['id'] ?? 0);
    if ($invoiceId <= 0) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
      return;
    }

    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath)) throw new Exception('PhpSpreadsheet not found');
      require_once $vendorPath;

      $result = $this->db->customQuery("SELECT inv.*, c.short_name as client_name, l.license_number FROM import_invoices_t inv LEFT JOIN clients_t c ON inv.client_id = c.id LEFT JOIN licenses_t l ON inv.license_id = l.id WHERE inv.id = ?", [$invoiceId]);
      if (empty($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        return;
      }

      $data = $result[0];
      $items = $this->getInvoiceItems($invoiceId);
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Invoice');
      $sheet->setCellValue('A1', 'MALABAR RDC SARL - INVOICE');
      $sheet->mergeCells('A1:D1');

      $row = 3;
      foreach ([['Invoice Ref:', $data['invoice_ref'] ?? ''], ['Client:', $data['client_name'] ?? ''], ['License:', $data['license_number'] ?? ''], ['CIF (USD):', '$' . number_format((float)($data['cif_usd'] ?? 0), 2)]] as $header) {
        $sheet->setCellValue('A' . $row, $header[0]);
        $sheet->setCellValue('B' . $row, $header[1]);
        $row++;
      }
      
      $row++;
      $sheet->setCellValue('A' . $row, 'Items:');
      $row++;
      $sheet->setCellValue('A' . $row, 'Description');
      $sheet->setCellValue('B' . $row, 'Qty');
      $sheet->setCellValue('C' . $row, 'Rate USD');
      $sheet->setCellValue('D' . $row, 'Total USD');
      $row++;
      
      foreach ($items as $item) {
        $sheet->setCellValue('A' . $row, $item['item_name'] ?? '');
        $sheet->setCellValue('B' . $row, $item['quantity'] ?? 1);
        $sheet->setCellValue('C' . $row, number_format((float)($item['taux_usd'] ?? 0), 2));
        $sheet->setCellValue('D' . $row, number_format((float)($item['total_usd'] ?? 0), 2));
        $row++;
      }
      
      foreach (range('A', 'D') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

      $filename = 'Invoice_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['invoice_ref'] ?? 'INV') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      $spreadsheet->disconnectWorksheets();
      exit;

    } catch (Exception $e) {
      $this->logError("Export error: " . $e->getMessage());
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'Export failed']);
      exit;
    }
  }

private function exportDebitInvoices()
{
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            throw new Exception('PhpSpreadsheet library not found at: ' . $vendorPath);
        }
        require_once $vendorPath;

        $sql = "SELECT 
                  inv.id,
                  inv.invoice_ref,
                  inv.mca_ids,
                  c.short_name as client_name,
                  u.username as created_by_name,
                  tg.goods_type as product_category,
                  inv.tariff_code_client,
                  inv.poids_kg,
                  inv.fob_usd,
                  inv.declaration_no,
                  inv.declaration_date,
                  inv.liquidation_no,
                  inv.liquidation_date,
                  inv.quittance_no,
                  inv.quittance_date,
                  inv.invoice_ref as facture_no,
                  inv.quittance_date as inv_date,
                  inv.po_ref,
                  inv.total_duty_cdf as liq_amt_cdf,
                  inv.rate_cdf_usd_bcc,
                  inv.validated,
                  inv.created_at,
                  inv.payment_method,
                  k.kind_name,
                  tm.transport_mode_name
                FROM import_invoices_t inv
                LEFT JOIN clients_t c ON inv.client_id = c.id
                LEFT JOIN users_t u ON inv.created_by = u.id
                LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                LEFT JOIN kind_master_t k ON inv.kind_id = k.id
                LEFT JOIN transport_mode_master_t tm ON inv.transport_mode_id = tm.id
                ORDER BY inv.id DESC";
        
        $invoices = $this->db->customQuery($sql);
        
        if (empty($invoices)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No invoices found to export']);
            exit;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Debit Note');
        
        $headers = [
            '#', 'Notre Nº Ref #', 'Tally Ref #', 'Client', 'Encoded By', 'Product Category', 
            'Commodity', 'Tarif Code', 'Poids', 'FOB (USD)', 'Kind', 'Transport Mode',
            'Payment Method', 'Scelle Electr.', 'Scelle Electr. Amount',
            'Frais Tresco', 'Declaration Ref.', 'Declaration Date', 'Liquidation Ref.', 'Liquidation Date',
            'Quittance Ref.', 'Quittance Date', 'FACTURE Nº', 'INV. DATE', 'PO REF #', 'LIQ AMT CDF',
            'Rate(CDF/USD) BCC', 'LIQ AMT/USD', 'OTHER CHARGES / AUTRES FRAIS', 'TVA/USD', 'Total',
            'OPERATIONAL COSTS / COUT OPERATIONEL', 'TVA/USD', 'Total', 'Agency fee', 'TVA/USD', 'Total',
            'Total Invoice', 'Status'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:AM1')->applyFromArray($headerStyle);
        
        $rowIndex = 2;
        $serialNumber = 1;
        
        foreach ($invoices as $inv) {
            $items = $this->getInvoiceItems($inv['id']);
            
            $scelleElectrQty = 0;
            $scelleElectrAmount = 0;
            $fraisTresco = 0;
            $otherCharges = 0;
            $otherChargesTVA = 0;
            
            foreach ($items as $item) {
                $itemName = strtoupper($item['item_name'] ?? '');
                $categoryId = (int)($item['category_id'] ?? 0);
                $totalUSD = (float)($item['total_usd'] ?? 0);
                $tvaUSD = (float)($item['tva_usd'] ?? 0);
                $subtotalUSD = $totalUSD - $tvaUSD;
                
                if (strpos($itemName, 'SCELLE') !== false || strpos($itemName, 'SEAL') !== false) {
                    if ($categoryId === 1 || $categoryId === 2) {
                        $scelleElectrQty = (int)($item['quantity'] ?? 0);
                        $scelleElectrAmount += $subtotalUSD;
                    }
                }
                
                if (strpos($itemName, 'TRESCO') !== false) {
                    if ($categoryId === 1 || $categoryId === 2) {
                        $fraisTresco += $subtotalUSD;
                    }
                }
                
                if ($categoryId === 2) {
                    $otherCharges += $subtotalUSD;
                    $otherChargesTVA += $tvaUSD;
                }
            }
            
            $otherChargesTotal = $otherCharges + $otherChargesTVA;
            
            $liqAmtCDF = (float)($inv['liq_amt_cdf'] ?? 0);
            $rateCDF = (float)($inv['rate_cdf_usd_bcc'] ?? 2500);
            $liqAmtUSD = $rateCDF > 0 ? ($liqAmtCDF / $rateCDF) : 0;
            
            $validated = (int)($inv['validated'] ?? 0);
            $status = $validated === 0 ? 'Pending Validation' : ($validated === 1 ? 'Validated' : 'DGI Verified');
            
            $factureNo = 'ND-' . ($inv['facture_no'] ?? '');
            $kindName = $inv['kind_name'] ?? '';
            $transportMode = $inv['transport_mode_name'] ?? '';
            $paymentMethod = $inv['payment_method'] ?? '';
            
            $mcaCount = 1;
            if (!empty($inv['mca_ids'])) {
                $mcaIdArray = array_filter(array_map('intval', explode(',', $inv['mca_ids'])));
                $mcaCount = count($mcaIdArray);
                
                if ($mcaCount > 0) {
                    $placeholders = implode(',', array_fill(0, count($mcaIdArray), '?'));
                    
                    $mcaDataSql = "SELECT i.id, i.mca_ref, i.fob, i.weight, 
                                          i.declaration_reference, i.dgda_in_date,
                                          i.liquidation_reference, i.liquidation_date,
                                          i.quittance_reference, i.quittance_date,
                                          cm.commodity_name
                                   FROM imports_t i
                                   LEFT JOIN commodity_master_t cm ON i.commodity = cm.id
                                   WHERE i.id IN ($placeholders)
                                   ORDER BY i.id ASC";
                    
                    $mcaDataResults = $this->db->customQuery($mcaDataSql, $mcaIdArray);
                    
                    $scelleElectrAmountPerMCA = $scelleElectrAmount / $mcaCount;
                    $fraisTrescoPerMCA = $fraisTresco / $mcaCount;
                    $otherChargesPerMCA = $otherCharges / $mcaCount;
                    $otherChargesTVAPerMCA = $otherChargesTVA / $mcaCount;
                    $otherChargesTotalPerMCA = $otherChargesTotal / $mcaCount;
                    $liqAmtCDFPerMCA = $liqAmtCDF / $mcaCount;
                    $liqAmtUSDPerMCA = $liqAmtUSD / $mcaCount;
                    
                    foreach ($mcaDataResults as $mca) {
                        $mcaRef = htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8');
                        $commodityValue = htmlspecialchars($mca['commodity_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $mcaFob = (float)($mca['fob'] ?? 0);
                        $mcaWeight = (float)($mca['weight'] ?? 0);
                        
                        $declRef = htmlspecialchars($mca['declaration_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $declDate = !empty($mca['dgda_in_date']) ? date('Y-m-d H:i:s', strtotime($mca['dgda_in_date'])) : '';
                        
                        $liqRef = htmlspecialchars($mca['liquidation_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $liqDate = !empty($mca['liquidation_date']) ? date('Y-m-d H:i:s', strtotime($mca['liquidation_date'])) : '';
                        
                        $quitRef = htmlspecialchars($mca['quittance_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $quitDate = !empty($mca['quittance_date']) ? date('Y-m-d H:i:s', strtotime($mca['quittance_date'])) : '';
                        
                        $invDate = !empty($inv['inv_date']) ? date('Y-m-d H:i:s', strtotime($inv['inv_date'])) : '';
                        
                        $rowData = [
                            $serialNumber,
                            $mcaRef,
                            $inv['invoice_ref'] ?? '',
                            $inv['client_name'] ?? '',
                            $inv['created_by_name'] ?? '',
                            $inv['product_category'] ?? '',
                            $commodityValue,
                            $inv['tariff_code_client'] ?? '',
                            $mcaWeight,
                            $mcaFob,
                            $kindName,
                            $transportMode,
                            $paymentMethod,
                            $scelleElectrQty,
                            $scelleElectrAmountPerMCA,
                            $fraisTrescoPerMCA,
                            $declRef,
                            $declDate,
                            $liqRef,
                            $liqDate,
                            $quitRef,
                            $quitDate,
                            $factureNo,
                            $invDate,
                            $inv['po_ref'] ?? 'N/A',
                            $liqAmtCDFPerMCA,
                            number_format($rateCDF, 4),
                            $liqAmtUSDPerMCA,
                            $otherChargesPerMCA,
                            $otherChargesTVAPerMCA,
                            $otherChargesTotalPerMCA,
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            '=AB' . $rowIndex . '+AE' . $rowIndex,
                            $status
                        ];
                        
                        $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
                        $rowIndex++;
                        $serialNumber++;
                    }
                } else {
                    $this->createSingleDebitRow($sheet, $rowIndex, $serialNumber, $inv, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $otherCharges, $otherChargesTVA, $otherChargesTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod, $liqAmtCDF, $rateCDF, $liqAmtUSD);
                    $rowIndex++;
                    $serialNumber++;
                }
            } else {
                $this->createSingleDebitRow($sheet, $rowIndex, $serialNumber, $inv, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $otherCharges, $otherChargesTVA, $otherChargesTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod, $liqAmtCDF, $rateCDF, $liqAmtUSD);
                $rowIndex++;
                $serialNumber++;
            }
        }
        
        $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM'];
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Debit_Note_' . date('Ymd_His') . '.xlsx';
        
        ob_end_clean();
        
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
        $this->logError("Export Debit Note error: " . $e->getMessage());
        $this->logError("Stack trace: " . $e->getTraceAsString());
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Export failed: ' . $e->getMessage()
        ]);
        exit;
    }
}

private function createSingleDebitRow($sheet, $rowIndex, $serialNumber, $inv, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $otherCharges, $otherChargesTVA, $otherChargesTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod, $liqAmtCDF, $rateCDF, $liqAmtUSD)
{
    $declDate = !empty($inv['declaration_date']) ? date('Y-m-d H:i:s', strtotime($inv['declaration_date'])) : '';
    $liqDate = !empty($inv['liquidation_date']) ? date('Y-m-d H:i:s', strtotime($inv['liquidation_date'])) : '';
    $quitDate = !empty($inv['quittance_date']) ? date('Y-m-d H:i:s', strtotime($inv['quittance_date'])) : '';
    $invDate = !empty($inv['inv_date']) ? date('Y-m-d H:i:s', strtotime($inv['inv_date'])) : '';
    
    $rowData = [
        $serialNumber,
        '',
        $inv['invoice_ref'] ?? '',
        $inv['client_name'] ?? '',
        $inv['created_by_name'] ?? '',
        $inv['product_category'] ?? '',
        '',
        $inv['tariff_code_client'] ?? '',
        $inv['poids_kg'] ?? '',
        $inv['fob_usd'] ?? '',
        $kindName,
        $transportMode,
        $paymentMethod,
        $scelleElectrQty,
        $scelleElectrAmount,
        $fraisTresco,
        $inv['declaration_no'] ?? '',
        $declDate,
        $inv['liquidation_no'] ?? '',
        $liqDate,
        $inv['quittance_no'] ?? '',
        $quitDate,
        $factureNo,
        $invDate,
        $inv['po_ref'] ?? 'N/A',
        $liqAmtCDF,
        number_format($rateCDF, 4),
        $liqAmtUSD,
        $otherCharges,
        $otherChargesTVA,
        $otherChargesTotal,
        0,
        0,
        0,
        0,
        0,
        0,
        '=AB' . $rowIndex . '+AE' . $rowIndex,
        $status
    ];
    
    $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
}


private function exportInvoicedInvoices()
{
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
        if (!file_exists($vendorPath)) {
            throw new Exception('PhpSpreadsheet library not found at: ' . $vendorPath);
        }
        require_once $vendorPath;

        $sql = "SELECT 
                  inv.id,
                  inv.invoice_ref,
                  inv.mca_ids,
                  c.short_name as client_name,
                  u.username as created_by_name,
                  tg.goods_type as product_category,
                  inv.tariff_code_client,
                  inv.poids_kg,
                  inv.fob_usd,
                  inv.declaration_no,
                  inv.declaration_date,
                  inv.liquidation_no,
                  inv.liquidation_date,
                  inv.quittance_no,
                  inv.quittance_date,
                  inv.invoice_ref as facture_no,
                  inv.quittance_date as inv_date,
                  inv.po_ref,
                  inv.total_duty_cdf as liq_amt_cdf,
                  inv.rate_cdf_usd_bcc,
                  inv.validated,
                  inv.created_at,
                  inv.payment_method,
                  k.kind_name,
                  tm.transport_mode_name
                FROM import_invoices_t inv
                LEFT JOIN clients_t c ON inv.client_id = c.id
                LEFT JOIN users_t u ON inv.created_by = u.id
                LEFT JOIN type_of_goods_master_t tg ON inv.goods_type_id = tg.id
                LEFT JOIN kind_master_t k ON inv.kind_id = k.id
                LEFT JOIN transport_mode_master_t tm ON inv.transport_mode_id = tm.id
                WHERE inv.validated IN (1, 2)
                ORDER BY inv.id DESC";
        
        $invoices = $this->db->customQuery($sql);
        
        if (empty($invoices)) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No validated invoices found']);
            exit;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');
        
        $headers = [
            '#', 'Notre Nº Ref #', 'Tally Ref #', 'Client', 'Encoded By', 'Product Category', 
            'Commodity', 'Tarif Code', 'Poids', 'FOB (USD)', 'Kind', 'Transport Mode',
            'Payment Method', 'Scelle Electr.', 'Scelle Electr. Amount',
            'Frais Tresco', 'Declaration Ref.', 'Declaration Date', 'Liquidation Ref.', 'Liquidation Date',
            'Quittance Ref.', 'Quittance Date', 'FACTURE Nº', 'INV. DATE', 'PO REF #', 'LIQ AMT CDF',
            'Rate(CDF/USD) BCC', 'LIQ AMT/USD', 'OTHER CHARGES / AUTRES FRAIS', 'TVA/USD', 'Total',
            'OPERATIONAL COSTS / COUT OPERATIONEL', 'TVA/USD', 'Total', 'Agency fee', 'TVA/USD', 'Total',
            'Total Invoice', 'Status'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:AM1')->applyFromArray($headerStyle);
        
        $rowIndex = 2;
        $serialNumber = 1;
        
        foreach ($invoices as $inv) {
            $items = $this->getInvoiceItems($inv['id']);
            
            $scelleElectrQty = 0;
            $scelleElectrAmount = 0;
            $fraisTresco = 0;
            $otherCharges = 0;
            $otherChargesTVA = 0;
            $operationalCosts = 0;
            $operationalCostsTVA = 0;
            $agencyFee = 0;
            $agencyFeeTVA = 0;
            
            foreach ($items as $item) {
                $itemName = strtoupper($item['item_name'] ?? '');
                $categoryId = (int)($item['category_id'] ?? 0);
                $totalUSD = (float)($item['total_usd'] ?? 0);
                $tvaUSD = (float)($item['tva_usd'] ?? 0);
                $subtotalUSD = $totalUSD - $tvaUSD;
                
                if ($categoryId === 1 || $categoryId === 2) {
                    if (strpos($itemName, 'SCELLE') !== false || strpos($itemName, 'SEAL') !== false) {
                        $scelleElectrQty = (int)($item['quantity'] ?? 0);
                        $scelleElectrAmount = $subtotalUSD;
                    } elseif (strpos($itemName, 'TRESCO') !== false) {
                        $fraisTresco = $subtotalUSD;
                    } else {
                        $otherCharges += $subtotalUSD;
                        $otherChargesTVA += $tvaUSD;
                    }
                }
                
                if ($categoryId === 3) {
                    $operationalCosts += $subtotalUSD;
                    $operationalCostsTVA += $tvaUSD;
                }
                
                if ($categoryId === 4) {
                    $agencyFee += $subtotalUSD;
                    $agencyFeeTVA += $tvaUSD;
                }
            }
            
            $operationalCostsTotal = $operationalCosts + $operationalCostsTVA;
            $agencyFeeTotal = $agencyFee + $agencyFeeTVA;
            
            $validated = (int)($inv['validated'] ?? 0);
            $status = $validated === 1 ? 'Awaiting to send' : ($validated === 2 ? 'DGI Verified' : 'Validated');
            
            $factureNo = $inv['facture_no'] ?? '';
            $kindName = $inv['kind_name'] ?? '';
            $transportMode = $inv['transport_mode_name'] ?? '';
            $paymentMethod = $inv['payment_method'] ?? '';
            
            $mcaCount = 1;
            if (!empty($inv['mca_ids'])) {
                $mcaIdArray = array_filter(array_map('intval', explode(',', $inv['mca_ids'])));
                $mcaCount = count($mcaIdArray);
                
                if ($mcaCount > 0) {
                    $placeholders = implode(',', array_fill(0, count($mcaIdArray), '?'));
                    
                    $mcaDataSql = "SELECT i.id, i.mca_ref, i.fob, i.weight, 
                                          i.declaration_reference, i.dgda_in_date,
                                          i.liquidation_reference, i.liquidation_date,
                                          i.quittance_reference, i.quittance_date,
                                          cm.commodity_name
                                   FROM imports_t i
                                   LEFT JOIN commodity_master_t cm ON i.commodity = cm.id
                                   WHERE i.id IN ($placeholders)
                                   ORDER BY i.id ASC";
                    
                    $mcaDataResults = $this->db->customQuery($mcaDataSql, $mcaIdArray);
                    
                    $scelleElectrAmountPerMCA = $scelleElectrAmount / $mcaCount;
                    $fraisTrescoPerMCA = $fraisTresco / $mcaCount;
                    $operationalCostsPerMCA = $operationalCosts / $mcaCount;
                    $operationalCostsTVAPerMCA = $operationalCostsTVA / $mcaCount;
                    $operationalCostsTotalPerMCA = $operationalCostsTotal / $mcaCount;
                    $agencyFeePerMCA = $agencyFee / $mcaCount;
                    $agencyFeeTVAPerMCA = $agencyFeeTVA / $mcaCount;
                    $agencyFeeTotalPerMCA = $agencyFeeTotal / $mcaCount;
                    
                    foreach ($mcaDataResults as $mca) {
                        $mcaRef = htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8');
                        $commodityValue = htmlspecialchars($mca['commodity_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $mcaFob = (float)($mca['fob'] ?? 0);
                        $mcaWeight = (float)($mca['weight'] ?? 0);
                        
                        $declRef = htmlspecialchars($mca['declaration_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $declDate = !empty($mca['dgda_in_date']) ? date('Y-m-d H:i:s', strtotime($mca['dgda_in_date'])) : '';
                        
                        $liqRef = htmlspecialchars($mca['liquidation_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $liqDate = !empty($mca['liquidation_date']) ? date('Y-m-d H:i:s', strtotime($mca['liquidation_date'])) : '';
                        
                        $quitRef = htmlspecialchars($mca['quittance_reference'] ?? '', ENT_QUOTES, 'UTF-8');
                        $quitDate = !empty($mca['quittance_date']) ? date('Y-m-d H:i:s', strtotime($mca['quittance_date'])) : '';
                        
                        $invDate = !empty($inv['inv_date']) ? date('Y-m-d H:i:s', strtotime($inv['inv_date'])) : '';
                        
                        $rowData = [
                            $serialNumber,
                            $mcaRef,
                            $inv['invoice_ref'] ?? '',
                            $inv['client_name'] ?? '',
                            $inv['created_by_name'] ?? '',
                            $inv['product_category'] ?? '',
                            $commodityValue,
                            $inv['tariff_code_client'] ?? '',
                            $mcaWeight,
                            $mcaFob,
                            $kindName,
                            $transportMode,
                            $paymentMethod,
                            $scelleElectrQty,
                            $scelleElectrAmountPerMCA,
                            $fraisTrescoPerMCA,
                            $declRef,
                            $declDate,
                            $liqRef,
                            $liqDate,
                            $quitRef,
                            $quitDate,
                            $factureNo,
                            $invDate,
                            $inv['po_ref'] ?? 'N/A',
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            $operationalCostsPerMCA,
                            $operationalCostsTVAPerMCA,
                            $operationalCostsTotalPerMCA,
                            $agencyFeePerMCA,
                            $agencyFeeTVAPerMCA,
                            $agencyFeeTotalPerMCA,
                            '=AH' . $rowIndex . '+AK' . $rowIndex,
                            $status
                        ];
                        
                        $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
                        $rowIndex++;
                        $serialNumber++;
                    }
                } else {
                    $this->createSingleInvoicedRow($sheet, $rowIndex, $serialNumber, $inv, $items, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $operationalCosts, $operationalCostsTVA, $operationalCostsTotal, $agencyFee, $agencyFeeTVA, $agencyFeeTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod);
                    $rowIndex++;
                    $serialNumber++;
                }
            } else {
                $this->createSingleInvoicedRow($sheet, $rowIndex, $serialNumber, $inv, $items, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $operationalCosts, $operationalCostsTVA, $operationalCostsTotal, $agencyFee, $agencyFeeTVA, $agencyFeeTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod);
                $rowIndex++;
                $serialNumber++;
            }
        }
        
        $columns = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM'];
        foreach ($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Invoice_' . date('Ymd_His') . '.xlsx';
        
        ob_end_clean();
        
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
        $this->logError("Export Invoice error: " . $e->getMessage());
        $this->logError("Stack trace: " . $e->getTraceAsString());
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Export failed: ' . $e->getMessage()
        ]);
        exit;
    }
}

private function createSingleInvoicedRow($sheet, $rowIndex, $serialNumber, $inv, $items, $scelleElectrQty, $scelleElectrAmount, $fraisTresco, $operationalCosts, $operationalCostsTVA, $operationalCostsTotal, $agencyFee, $agencyFeeTVA, $agencyFeeTotal, $status, $factureNo, $kindName, $transportMode, $paymentMethod)
{
    $declDate = !empty($inv['declaration_date']) ? date('Y-m-d H:i:s', strtotime($inv['declaration_date'])) : '';
    $liqDate = !empty($inv['liquidation_date']) ? date('Y-m-d H:i:s', strtotime($inv['liquidation_date'])) : '';
    $quitDate = !empty($inv['quittance_date']) ? date('Y-m-d H:i:s', strtotime($inv['quittance_date'])) : '';
    $invDate = !empty($inv['inv_date']) ? date('Y-m-d H:i:s', strtotime($inv['inv_date'])) : '';
    
    $rowData = [
        $serialNumber,
        '',
        $inv['invoice_ref'] ?? '',
        $inv['client_name'] ?? '',
        $inv['created_by_name'] ?? '',
        $inv['product_category'] ?? '',
        '',
        $inv['tariff_code_client'] ?? '',
        $inv['poids_kg'] ?? '',
        $inv['fob_usd'] ?? '',
        $kindName,
        $transportMode,
        $paymentMethod,
        $scelleElectrQty,
        $scelleElectrAmount,
        $fraisTresco,
        $inv['declaration_no'] ?? '',
        $declDate,
        $inv['liquidation_no'] ?? '',
        $liqDate,
        $inv['quittance_no'] ?? '',
        $quitDate,
        $factureNo,
        $invDate,
        $inv['po_ref'] ?? 'N/A',
        0,
        0,
        0,
        0,
        0,
        0,
        $operationalCosts,
        $operationalCostsTVA,
        $operationalCostsTotal,
        $agencyFee,
        $agencyFeeTVA,
        $agencyFeeTotal,
        '=AH' . $rowIndex . '+AK' . $rowIndex,
        $status
    ];
    
    $sheet->fromArray([$rowData], null, 'A' . $rowIndex);
}

private function viewPDF()
{
    $invoiceId = (int)($_GET['id'] ?? 0);
    $pageParam = $_GET['page'] ?? null;
    
    if ($invoiceId <= 0) die("Invalid invoice ID");

    try {
      $vendorPath = __DIR__ . '/../../../vendor/autoload.php';
      if (!file_exists($vendorPath)) die("mPDF library not found");
      require_once $vendorPath;

$invoice = $this->db->customQuery("SELECT iit.*,ei.qrcode,ei.codedefdgi,ei.uid,ei.nim,ei.counters,ei.date_time,ei.final_req FROM import_invoices_t iit LEFT JOIN emcf_invoice ei ON iit.id=ei.invoice_id AND ei.inv_type='IMPORT' WHERE iit.id = ? LIMIT 1", [$invoiceId]);      if (empty($invoice)) die("Invoice not found");
      
      $invoice = $invoice[0];
      $clientId = $invoice['client_id'] ?? 0;
      $validated = (int)($invoice['validated'] ?? 0);
      $goodsTypeId = (int)($invoice['goods_type_id'] ?? 0);
      $hiddenCategories = json_decode($invoice['hidden_categories'] ?? '[]', true);

      $data = $this->preparePDFData($invoice, $clientId, $validated, $hiddenCategories);
      
      $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => 'A4', 
        'margin_top' => 5, 
        'margin_bottom' => 5, 
        'margin_left' => 5, 
        'margin_right' => 5
      ]);
      
      if ($validated === 0) {
        $mpdf->SetWatermarkText('NOT VALID', 0.15);
        $mpdf->watermarkTextAlpha = 0.15;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->showWatermarkText = true;
      }
      
      if ($pageParam === '1') {
        $htmlPage1 = $this->generateInvoicePDFPage1($data);
        $mpdf->WriteHTML($htmlPage1);
        
      } else if ($pageParam === '2') {
        $htmlPage2 = $this->generateInvoicePDFPage2($data);
        $mpdf->WriteHTML($htmlPage2);
        
        if ($goodsTypeId === 3) {
          $mpdf->AddPage('L');
          $htmlPage3 = $this->generateInvoicePDFPage3($data);
          $mpdf->WriteHTML($htmlPage3);
        }
        
      } else {
        $htmlPage1 = $this->generateInvoicePDFPage1($data);
        $mpdf->WriteHTML($htmlPage1);
        
        $mpdf->AddPage();
        $htmlPage2 = $this->generateInvoicePDFPage2($data);
        $mpdf->WriteHTML($htmlPage2);
        
        if ($goodsTypeId === 3) {
          $mpdf->AddPage('L');
          $htmlPage3 = $this->generateInvoicePDFPage3($data);
          $mpdf->WriteHTML($htmlPage3);
        }
      }
      
      $mpdf->Output('Invoice_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['invoice_ref']) . '.pdf', 'I');

    } catch (Exception $e) {
      $this->logError("PDF Exception: " . $e->getMessage());
      die("PDF generation failed: " . $e->getMessage());
    }
}

private function preparePDFData($invoice, $clientId, $validated, $hiddenCategories)
{
   $data = [
    'client_id' => $clientId,
    'invoice_ref' => $invoice['invoice_ref'] ?? '',
    'created_at' => $invoice['created_at'] ?? '',  // ADD THIS LINE
    'payment_method' => $invoice['payment_method'] ?? 'CREDIT',
        'fob_usd' => $invoice['fob_usd'] ?? 0,
        'fret_usd' => $invoice['fret_usd'] ?? 0,
        'assurance_usd' => $invoice['assurance_usd'] ?? 0,
        'autres_charges_usd' => $invoice['autres_charges_usd'] ?? 0,
        'rate_cdf_inv' => $invoice['rate_cdf_inv'] ?? 2500,
        'rate_cdf_usd_bcc' => $invoice['rate_cdf_usd_bcc'] ?? 2500,
        'cif_usd' => $invoice['cif_usd'] ?? 0,
        'cif_cdf' => $invoice['cif_cdf'] ?? 0,
        'total_duty_cdf' => $invoice['total_duty_cdf'] ?? 0,
        'poids_kg' => $invoice['poids_kg'] ?? 0,
        'm3' => $invoice['m3'] ?? 0,
        'horse' => $invoice['horse'] ?? '',
        'trailer_1' => $invoice['trailer_1'] ?? '',
        'container' => $invoice['container'] ?? '',
        'wagon' => $invoice['wagon'] ?? '',
        'airway_bill' => $invoice['airway_bill'] ?? '',
        'airway_bill_weight' => $invoice['airway_bill_weight'] ?? 0,
        'facture_pfi_no' => $invoice['facture_pfi_no'] ?? '',
        'po_ref' => $invoice['po_ref'] ?? '',
        'bivac_inspection' => $invoice['bivac_inspection'] ?? '',
        'produit' => '',
        'tariff_code_client' => $invoice['tariff_code_client'] ?? '',
        'exoneration_code' => $invoice['exoneration_code'] ?? '',
        'declaration_no' => $invoice['declaration_no'] ?? '',
        'declaration_date' => $invoice['declaration_date'] ?? '',
        'liquidation_no' => $invoice['liquidation_no'] ?? '',
        'liquidation_date' => $invoice['liquidation_date'] ?? '',
        'quittance_no' => $invoice['quittance_no'] ?? '',
        'quittance_date' => $invoice['quittance_date'] ?? '',
        'dispatch_deliver_date' => $invoice['dispatch_deliver_date'] ?? '',
        'arsp' => $invoice['arsp'] ?? 'Disabled',
        'validated' => $validated,
        'hidden_categories' => $hiddenCategories,
        'invoice_template' => '',
        'calculated_sub_total' => $invoice['calculated_sub_total'] ?? $invoice['quotation_sub_total'] ?? 0,
        'calculated_vat_amount' => $invoice['calculated_vat_amount'] ?? $invoice['quotation_vat_amount'] ?? 0,
        'calculated_total_amount' => $invoice['calculated_total_amount'] ?? $invoice['quotation_total_amount'] ?? 0,
        'calculated_total_cdf' => $invoice['calculated_total_cdf'] ?? 0,
        'items_manually_edited' => $invoice['items_manually_edited'] ?? 0,
        'transport_mode_id' => $invoice['transport_mode_id'] ?? 0,
        'first_categoty_edited' => $invoice['first_categoty_edited'] ?? 'H',
        'goods_type_id' => $invoice['goods_type_id'] ?? 0,
        'mca_ids' => $invoice['mca_ids'] ?? '',
'qrcode' => $invoice['qrcode'] ?? '',
'codedefdgi' => $invoice['codedefdgi'] ?? '',
'emcf_uid' => $invoice['uid'] ?? '',
'emcf_nim' => $invoice['nim'] ?? '',
'emcf_counters' => $invoice['counters'] ?? '',
'emcf_datetime' => $invoice['date_time'] ?? '',
'emcf_final_req' => $invoice['final_req'] ?? ''
    ];

    if (!empty($clientId)) {
        $client = $this->db->customQuery("SELECT short_name, company_name, address, rccm_number, nif_number, id_nat_number, import_export_number, invoice_template FROM clients_t WHERE id = ? LIMIT 1", [$clientId]);
        if (!empty($client)) {
            $data['client_name'] = $client[0]['short_name'] ?? '';
            $data['client_company'] = $client[0]['company_name'] ?? '';
            $data['client_address'] = $client[0]['address'] ?? '';
            $data['client_rccm'] = $client[0]['rccm_number'] ?? '';
            $data['client_nif'] = $client[0]['nif_number'] ?? '';
            $data['client_id_nat'] = $client[0]['id_nat_number'] ?? '';
            $data['client_import_export'] = $client[0]['import_export_number'] ?? '';
            $data['invoice_template'] = $client[0]['invoice_template'] ?? '';
        }
    }

    $data['client_name'] = $data['client_name'] ?? 'N/A';
    $data['client_company'] = $data['client_company'] ?? '';
    $data['client_address'] = $data['client_address'] ?? '';
    $data['client_rccm'] = $data['client_rccm'] ?? '';
    $data['client_nif'] = $data['client_nif'] ?? '';
    $data['client_id_nat'] = $data['client_id_nat'] ?? '';
    $data['client_import_export'] = $data['client_import_export'] ?? '';
    $data['client_tva'] = '';

    if (!empty($data['mca_ids'])) {
        $mcaIds = array_filter(array_map('intval', explode(',', $data['mca_ids'])));
        if (!empty($mcaIds)) {
            $placeholders = implode(',', array_fill(0, count($mcaIds), '?'));
            
            $mca = $this->db->customQuery(
                "SELECT GROUP_CONCAT(DISTINCT supplier SEPARATOR ', ') as suppliers, 
                        GROUP_CONCAT(DISTINCT cm.commodity_name SEPARATOR ', ') as commodities, 
                        GROUP_CONCAT(DISTINCT mca_ref SEPARATOR ', ') as mca_refs 
                 FROM imports_t i
                 LEFT JOIN commodity_master_t cm ON i.commodity = cm.id
                 WHERE i.id IN ($placeholders) 
                 LIMIT 1", 
                 $mcaIds
            );
            
            if (!empty($mca)) {
                $data['supplier'] = $mca[0]['suppliers'] ?? '';
                $data['file_ref'] = $mca[0]['mca_refs'] ?? '';
                $data['produit'] = $mca[0]['commodities'] ?? '';
            }
        }
    }
    
    $data['supplier'] = $data['supplier'] ?? '';
    $data['file_ref'] = $data['file_ref'] ?? '';
    if (empty($data['produit'])) $data['produit'] = 'N/A';

    if (!empty($invoice['license_id'])) {
        $license = $this->db->customQuery("SELECT license_number FROM licenses_t WHERE id = ? LIMIT 1", [$invoice['license_id']]);
        if (!empty($license)) $data['license_number'] = $license[0]['license_number'] ?? '';
    }
    $data['license_number'] = $data['license_number'] ?? '';

    if (!empty($invoice['transport_mode_id'])) {
        $transport = $this->db->customQuery("SELECT transport_mode_name FROM transport_mode_master_t WHERE id = ? LIMIT 1", [$invoice['transport_mode_id']]);
        if (!empty($transport)) $data['transport_mode_name'] = $transport[0]['transport_mode_name'] ?? 'ROAD';
    }
    $data['transport_mode_name'] = $data['transport_mode_name'] ?? 'ROAD';

    $banks = $this->db->customQuery("SELECT ibm.invoice_bank_name, ibm.invoice_bank_account_name, ibm.invoice_bank_account_number, ibm.invoice_bank_swift FROM client_bank_mapping_t cbm INNER JOIN invoice_bank_master_t ibm ON cbm.bank_id = ibm.id WHERE cbm.client_id = ? AND ibm.display = 'Y' ORDER BY cbm.id ASC", [$clientId]);
    $data['banks'] = $banks ?: [];
    
    $data['items'] = $this->getInvoiceItems($invoice['id'] ?? 0);

    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0) {
        $userResult = $this->db->customQuery("SELECT signature_image, username FROM users_t WHERE id = ? LIMIT 1", [$userId]);
        if (!empty($userResult)) {
            if (!empty($userResult[0]['signature_image'])) {
                $signaturePath = __DIR__ . '/../../../public/uploads/signatures/' . $userResult[0]['signature_image'];
                if (file_exists($signaturePath)) {
                    $data['signature_path'] = $signaturePath;
                }
            }
            $data['username'] = $userResult[0]['username'] ?? '';
        }
    }

    return $data;
}

private function generateInvoicePDFPage1($data)
{
    list($commonCSS, $headerHtml, $bankHtml, $footerHtml, $htmlSign, $totalHtmlPage1) = $this->generateCommonPDFComponents($data, true);
    
    list($page1Categories, $page2Categories) = $this->getCategoriesSplit($data);
    
    $client_id = (int)($data['client_id'] ?? 0);
    $exchange_rate = (float)($data['rate_cdf_usd_bcc'] ?? 2500);
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' . $commonCSS . '</head><body>';
    $html .= $headerHtml;
    $html .= '<div class="reimbursable-header">REIMBURSABLE CHARGES</div>';
    
    $total_duty_cdf = (float)($data['total_duty_cdf'] ?? 0);
    $html .= $this->renderCategories($page1Categories, $data['hidden_categories'], $data['first_categoty_edited'], $data['goods_type_id'], $data['m3'], $total_duty_cdf, $client_id, $exchange_rate);
    
    $html .= $totalHtmlPage1;
    $html .= $bankHtml;
    $html .= $footerHtml;
    $html .= '</body></html>';
    return $html;
}

private function generateInvoicePDFPage2($data)
{ 
    list($commonCSS, $headerHtml, $bankHtml, $footerHtml, $htmlSign, $totalHtmlPage2) = $this->generateCommonPDFComponents($data, false);
    
    list($page1Categories, $page2Categories) = $this->getCategoriesSplit($data);
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' . $commonCSS . '</head><body>';
    $html .= $headerHtml;
    
    $client_id = (int)($data['client_id'] ?? 0);
    $html .= $this->renderCategories($page2Categories, $data['hidden_categories'], '', $data['goods_type_id'], $data['m3'], 0, $client_id);
    
    $html .= $totalHtmlPage2;
    $html .= $bankHtml;
    $html .= $footerHtml;
    $html .= '</body></html>';
    
    return $html;
}

private function generateInvoicePDFPage3($data)
{
    $commonCSS = '<style>
  body{font-family:Arial,sans-serif;font-size:6.5pt;margin:0;padding:3mm;line-height:1.2;}
  table{border-collapse:collapse;width:100%;}
  td,th{padding:1px 2px;vertical-align:middle;word-wrap:break-word;}
  .b td,.b th{border:1px solid #000;}
  .r{text-align:right;}.c{text-align:center;}.bo{font-weight:bold;}.g{background:#e0e0e0;}
  .center-wrapper{display:flex;flex-direction:column;align-items:center;justify-content:center;}
  </style>';
    
    $logoPath = $this->logoPath;
    $logoHtml = file_exists($logoPath) ? '<img src="' . $logoPath . '" style="max-width:240px;max-height:60px;">' : '<b style="font-size:14pt;">MALABAR RDC SARL</b>';
    
   $headerHtml = '<table cellpadding="0" cellspacing="0" style="width:100%;border:none;margin-bottom:2mm;">
    <tr>
        <td style="width:50%;border:none;vertical-align:top;text-align:left;">' . $logoHtml . '</td>
        <td style="width:50%;text-align:right;font-size:6.5pt;line-height:1.5;border:none;vertical-align:top;">
            No. 1068, Avenue Ruwe, Quartier Makutano,<br>
            Lubumbashi, DRC<br>
            RCCM: 13-B-1122, ID NAT. 6-9-N91867E<br>
            NIF: A 1309334 L<br>
            VAT Ref # 145/DGI/DGE/INF/BN/TVA/2020<br>
            Capital Social: 45.000.000 FC
            <br>
            Point de vente : Lubumbashi
        </td>
    </tr>
    </table>';
    
    $titleHtml = '<div style="background:#000;color:#fff;padding:6px 12px;font-weight:bold;font-size:8pt;text-align:center;margin:3mm auto;text-transform:uppercase;max-width:90%;">DETAILS - CLEARING CONSUMABLES (FUEL) LOADS</div>';
    
    $mcaIds = $data['mca_ids'] ?? '';
    $mcaIdArray = array_filter(array_map('intval', explode(',', $mcaIds)));
    
    $client_id = (int)($data['client_id'] ?? 0);
    $goods_type_id = (int)($data['goods_type_id'] ?? 0);
    $isClient37GoodsType3 = ($client_id === 37 && $goods_type_id === 3);
    
    $mcaTableHtml = '<div style="text-align:center;margin:0 auto;max-width:95%;">
    <table class="b" style="width:100%;font-size:6.5pt;margin:0 auto;">
      <thead style="background:#e0e0e0;">
        <tr>
          <th style="width:3%;" class="c">#</th>
          <th style="width:' . ($isClient37GoodsType3 ? '11%' : '10%') . ';">MCA File No</th>
          <th style="width:' . ($isClient37GoodsType3 ? '11%' : '10%') . ';">License</th>
          <th style="width:' . ($isClient37GoodsType3 ? '16%' : '15%') . ';">Truck</th>';
    
    if (!$isClient37GoodsType3) {
      $mcaTableHtml .= '<th style="width:5%;" class="c">Qty(M3)</th>';
    }
    
    $mcaTableHtml .= '
          <th style="width:9%;">Declaration<br>Ref</th>
          <th style="width:7%;" class="c">Declaration<br>Date</th>
          <th style="width:9%;">Liquidation<br>Ref</th>
          <th style="width:7%;" class="c">Liquidation<br>Date</th>
          <th style="width:9%;">Quittance Ref</th>
          <th style="width:7%;" class="c">Quittance<br>Date</th>
        </tr>
      </thead>
      <tbody>';
    
    if (!empty($mcaIdArray)) {
      $placeholders = implode(',', array_fill(0, count($mcaIdArray), '?'));
      
      $sql = "SELECT i.id, i.mca_ref, i.m3, i.horse, i.trailer_1, i.trailer_2, i.container,
                     i.declaration_reference, i.dgda_in_date,
                     i.liquidation_reference, i.liquidation_date,
                     i.quittance_reference, i.quittance_date,
                     l.license_number
              FROM imports_t i
              LEFT JOIN licenses_t l ON i.license_id = l.id
              WHERE i.id IN ($placeholders) AND i.display = 'Y'
              ORDER BY i.id ASC";
      
      $mcas = $this->db->customQuery($sql, $mcaIdArray);
      
      if (!empty($mcas)) {
        $totalM3 = 0;
        $rowNum = 1;
        
        foreach ($mcas as $mca) {
          $mcaFileNo = htmlspecialchars($mca['mca_ref'] ?? '', ENT_QUOTES, 'UTF-8');
          $licenseNumber = htmlspecialchars($mca['license_number'] ?? '', ENT_QUOTES, 'UTF-8');
          
          $truck = trim(
            htmlspecialchars($mca['horse'] ?? '', ENT_QUOTES, 'UTF-8') . '/' . 
            htmlspecialchars($mca['trailer_1'] ?? '', ENT_QUOTES, 'UTF-8') . '/' . 
            htmlspecialchars($mca['trailer_2'] ?? '', ENT_QUOTES, 'UTF-8') . '/' . 
            htmlspecialchars($mca['container'] ?? '', ENT_QUOTES, 'UTF-8'), 
            '/'
          );
          
          $m3 = floatval($mca['m3'] ?? 0);
          $totalM3 += $m3;
          
          $declarationRef = htmlspecialchars($mca['declaration_reference'] ?? '', ENT_QUOTES, 'UTF-8');
          $declarationDate = !empty($mca['dgda_in_date']) ? date('d/m/Y', strtotime($mca['dgda_in_date'])) : '';
          
          $liquidationRef = htmlspecialchars($mca['liquidation_reference'] ?? '', ENT_QUOTES, 'UTF-8');
          $liquidationDate = !empty($mca['liquidation_date']) ? date('d/m/Y', strtotime($mca['liquidation_date'])) : '';
          
          $quittanceRef = htmlspecialchars($mca['quittance_reference'] ?? '', ENT_QUOTES, 'UTF-8');
          $quittanceDate = !empty($mca['quittance_date']) ? date('d/m/Y', strtotime($mca['quittance_date'])) : '';
          
          $mcaTableHtml .= '<tr>
            <td class="c">' . $rowNum . '</td>
            <td class="c">' . $mcaFileNo . '</td>
            <td class="c">' . $licenseNumber . '</td>
            <td class="c">' . $truck . '</td>';
          
          if (!$isClient37GoodsType3) {
            $mcaTableHtml .= '<td class="c">' . number_format($m3, 2) . '</td>';
          }
          
          $mcaTableHtml .= '
            <td class="c">' . $declarationRef . '</td>
            <td class="c">' . $declarationDate . '</td>
            <td class="c">' . $liquidationRef . '</td>
            <td class="c">' . $liquidationDate . '</td>
            <td class="c">' . $quittanceRef . '</td>
            <td class="c">' . $quittanceDate . '</td>
          </tr>';
          
          $rowNum++;
        }
        
        $totalColspan = $isClient37GoodsType3 ? 4 : 4;
        $mcaTableHtml .= '<tr class="g bo">
          <td colspan="' . $totalColspan . '" class="c" style="padding-right:10px;">Total</td>';
        
        if (!$isClient37GoodsType3) {
          $mcaTableHtml .= '<td class="c">' . number_format($totalM3, 2) . '</td>';
        }
        
        $remainingColspan = $isClient37GoodsType3 ? 6 : 6;
        $mcaTableHtml .= '<td colspan="' . $remainingColspan . '"></td>
        </tr>';
      } else {
        $totalColspan = $isClient37GoodsType3 ? 10 : 11;
        $mcaTableHtml .= '<tr><td colspan="' . $totalColspan . '" class="c" style="padding:20px;color:#999;">No MCA data found</td></tr>';
      }
    } else {
      $totalColspan = $isClient37GoodsType3 ? 10 : 11;
      $mcaTableHtml .= '<tr><td colspan="' . $totalColspan . '" class="c" style="padding:20px;color:#999;">No MCA IDs provided</td></tr>';
    }
    
    $mcaTableHtml .= '</tbody></table></div>';
    
    $invoiceRefText = 'Details INV No. ' . htmlspecialchars($data['invoice_ref'] ?? '', ENT_QUOTES, 'UTF-8') . ' du ' . date('d-M-y');
    
    $invoiceRefHtml = '<div style="text-align:center;font-size:6.5pt;margin-top:5mm;font-weight:600;">' . $invoiceRefText . '</div>';
    
    $htmlSign = $this->generateSignatureHTML($data);
    
    $footerHtml = '<div style="text-align:center;margin-top:15mm;">' . $htmlSign . '</div>';
    
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' . $commonCSS . '</head><body>';
    $html .= $headerHtml;
    $html .= $titleHtml;
    $html .= $mcaTableHtml;
    $html .= $invoiceRefHtml;
    $html .= $footerHtml;
    $html .= '</body></html>';
    
    return $html;
}


  private function getCategoriesSplit($data)
  {
    $items = $data['items'] ?? [];
    $hiddenCategories = $data['hidden_categories'] ?? [];
    
    $groupedByCategory = [];
    if (!empty($items)) {
        foreach ($items as $item) {
            $catId = (int)($item['category_id'] ?? 0);
            $catHeader = $item['category_header'] ?? $item['category_name'] ?? 'UNCATEGORIZED';
            
            if (!isset($groupedByCategory[$catId])) {
                $groupedByCategory[$catId] = [
                    'header' => $catHeader,
                    'items' => [],
                    'category_id' => $catId
                ];
            }
            $groupedByCategory[$catId]['items'][] = $item;
        }
    }
    
    $page1Categories = [];
    $page2Categories = [];
    
    foreach ($groupedByCategory as $catId => $categoryData) {
        if (in_array($catId, $hiddenCategories)) {
            continue;
        }
        
        if ($catId === 1 || $catId === 2) {
            $page1Categories[] = $categoryData;
        }
        else if ($catId === 3 || $catId === 4) {
            $page2Categories[] = $categoryData;
        }
    }
    return [$page1Categories, $page2Categories];
  }


private function renderCategories($categories, $hiddenCategories, $first_categoty_edited = '', $goods_type_id = 0, $total_m3 = 0, $total_duty_cdf = 0, $client_id = 0, $exchange_rate = 2500)
{
    $html = '';
    $isGoodsType3 = ((int)$goods_type_id === 3);
    
    $isClient37GoodsType3 = ((int)$client_id === 37 && (int)$goods_type_id === 3);
    
    foreach ($categories as $categoryData) { 
        $categoryHeader = $categoryData['header'];
        $categoryItems = $categoryData['items'];
        $catId = (int)($categoryData['category_id'] ?? 0);
        
        $isCategoryOne = ($catId === 1);
        $isCategoryTwo = ($catId === 2);
        
        if ($catId === 1 && $first_categoty_edited === 'H') {         
            continue;
        }
        
        if ($isCategoryOne && $total_duty_cdf > 0) {
            $sumOfOtherTotals = 0;
            $autresTaxesIndex = -1;
            
            foreach ($categoryItems as $idx => $item) {
                $itemName = strtoupper($item['item_name'] ?? '');
                if (strpos($itemName, 'AUTRES TAXES') !== false || strpos($itemName, 'REF LIQUIDATION') !== false) {
                    $autresTaxesIndex = $idx;
                } else {
                    $sumOfOtherTotals += (float)($item['total_cdf'] ?? 0);
                }
            }
            
            if ($autresTaxesIndex >= 0) {
                $autresTaxesValue = $total_duty_cdf - $sumOfOtherTotals;
                $categoryItems[$autresTaxesIndex]['rate_cdf'] = $autresTaxesValue;
                $categoryItems[$autresTaxesIndex]['total_cdf'] = $autresTaxesValue;
            }
        }
        
        $html .= '<div class="bk" style="margin-top:2mm;">' . strtoupper(htmlspecialchars($categoryHeader, ENT_QUOTES, 'UTF-8')) . '</div>';
        $html .= '<table class="items-table" style="width:100%;border-collapse:collapse;">';
        
        $html .= '<tr class="g">';
        
        if ($isCategoryOne) {
            $html .= '<th style="width:33%;border:1px solid #000;">Description</th>';
            $html .= '<th style="width:12%;border:1px solid #000;border-left:none;" class="r">CIF/Split</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;">Unit</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">%</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Rate/CDF</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TVA/CDF</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TOTAL EN CDF</th>';
        } else {
            $html .= '<th style="width:45%;border:1px solid #000;">Description</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;">Unit</th>';
            
            if ($isCategoryTwo && $isClient37GoodsType3) {
                $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Qty</th>';
            } else if ($isClient37GoodsType3) {
                $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Qty</th>';
            } else if ($isGoodsType3) {
                $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">M3</th>';
            } else {
                $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Qty</th>';
            }
            
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Taux/USD</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TVA/USD</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TOTAL EN USD</th>';
        }
        
        $html .= '</tr>';
        
        $categorySubtotal = 0;
        $categoryTVA = 0;
        $categoryTotal = 0;
        
        $itemIndex = 0;
        
        foreach ($categoryItems as $item) {
            if ($item['item_name'] === 'Frais Bancaires' && $first_categoty_edited === 'H') {
                continue;
            }
            
            $desc = htmlspecialchars($item['item_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $unit = htmlspecialchars($item['unit_text'] ?? $item['unit_name'] ?? 'Unit', ENT_QUOTES, 'UTF-8');
            
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #000;border-top:none;border-bottom:none;">' . $desc . '</td>';
            
            if ($isCategoryOne) {
                $cifSplit = (float)($item['cif_split'] ?? 0);
                $percentage = (float)($item['percentage'] ?? 0);
                $rate = (float)($item['rate_cdf'] ?? 0);
                $tva = (float)($item['vat_cdf'] ?? 0);
                $total = (float)($item['total_cdf'] ?? 0);
                
                $cifSplitFormatted = $cifSplit != 0 ? number_format($cifSplit, 2, '.', '') : '';
                
                $percentageFormatted = '';
                $showPercentageSymbol = true;

                if ($percentage != 0) {
                    $percentageFormatted = rtrim(rtrim(number_format($percentage, 3, '.', ''), '0'), '.');
                    
                    $itemName = strtoupper($desc ?? '');
                    if (strpos($itemName, 'RLS') !== false || 
                        strpos($itemName, 'REDEVANCE LOGISTIQUE') !== false) {
                        $showPercentageSymbol = false;
                    }
                }

                $rateFormatted = number_format($rate, 2, '.', '');
                $tvaFormatted = number_format($tva, 2, '.', '');
                $totalFormatted = number_format($total, 2, '.', '');

                $itemSubtotal = $rate;
                $itemTva = $tva;
                $itemTotal = $total;

                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $cifSplitFormatted . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $unit . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $percentageFormatted . ($percentageFormatted !== '' && $showPercentageSymbol ? '%' : '') . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $rateFormatted . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $tvaFormatted . '</td>';
                $html .= '<td class="r" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $totalFormatted . '</td>';
                
            } else {
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $unit . '</td>';
                
                if ($isCategoryTwo && $isClient37GoodsType3) {
                    // For Client 37 + Goods Type 3 + Category 2: Show quantity with % symbol
                    $qtyValue = (float)($item['quantity'] ?? 0);
                    if ($qtyValue != 0) {
                        $qty = rtrim(rtrim(number_format($qtyValue, 2, '.', ''), '0'), '.') . '%';
                    } else {
                        $qty = '';
                    }
                    $itemSubtotal = (float)($item['subtotal_usd'] ?? 0);
                    $itemTva = (float)($item['tva_usd'] ?? 0);
                    $itemTotal = (float)($item['total_usd'] ?? 0);
                    
                } else if ($isGoodsType3 && $itemIndex === 0) {
                    // Normal Goods Type 3: First item uses total M3
                    $qty = number_format((float)$total_m3, 2);
                    $qtyValue = (float)$total_m3;
                    $rateValue = (float)($item['taux_usd'] ?? 0);
                    
                    $itemSubtotal = $qtyValue * $rateValue;
                    $hasTVA = (int)($item['has_tva'] ?? 0) === 1;
                    $itemTva = $hasTVA ? ($itemSubtotal * 0.16) : 0;
                    $itemTotal = $itemSubtotal + $itemTva;
                    
                } else if ($isGoodsType3 && $itemIndex > 0) {
                    // Normal Goods Type 3: Subsequent items show nothing
                    $qty = '';
                    $itemSubtotal = 0;
                    $itemTva = 0;
                    $itemTotal = 0;
                    
                } else {
                    // Normal items
                    $qty = $this->formatNumber($item['quantity'] ?? 1, 2);
                    $itemSubtotal = (float)($item['subtotal_usd'] ?? 0);
                    $itemTva = (float)($item['tva_usd'] ?? 0);
                    $itemTotal = (float)($item['total_usd'] ?? 0);
                }
                
                $rate = number_format((float)($item['taux_usd'] ?? 0), 2);
                $tva = number_format($itemTva, 2);
                $total = number_format($itemTotal, 2);
                
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $qty . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $rate . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $tva . '</td>';
                $html .= '<td class="r" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $total . '</td>';
            }
            
            $html .= '</tr>';
            
            if ($itemSubtotal === 0 && $itemTotal > 0) {
                $itemSubtotal = $itemTotal - $itemTva;
            }
            
            $categorySubtotal += $itemSubtotal;
            $categoryTVA += $itemTva;
            $categoryTotal += $itemTotal;
            
            $itemIndex++;
        }
        
        // SUB-TOTAL ROW (CDF)
        $html .= '<tr class="g">';
        if ($isCategoryOne) {
            $html .= '<td colspan="4" class="bo" style="border:1px solid #000;">Sub-total (CDF)</td>';
        } else {
            $html .= '<td colspan="3" class="bo" style="border:1px solid #000;">Sub-total</td>';
        }
        
        $subtotalFormatted = number_format($categorySubtotal, 2, '.', '');
        $tvaFormatted = number_format($categoryTVA, 2, '.', '');
        $totalFormatted = number_format($categoryTotal, 2, '.', '');
        
        $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;">' . $subtotalFormatted . '</td>';
        $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;">' . $tvaFormatted . '</td>';
        $html .= '<td class="r bo" style="border:1px solid #000;border-left:none;">' . $totalFormatted . '</td>';
        $html .= '</tr>';
        
        // ADD USD CONVERSION ROW FOR CATEGORY 1
        if ($isCategoryOne && $exchange_rate > 0) {
            $subtotalUSD = $categorySubtotal / $exchange_rate;
            $tvaUSD = $categoryTVA / $exchange_rate;
            $totalUSD = $categoryTotal / $exchange_rate;
            
            $subtotalUSDFormatted = number_format($subtotalUSD, 2, '.', '');
            $tvaUSDFormatted = number_format($tvaUSD, 2, '.', '');
            $totalUSDFormatted = number_format($totalUSD, 2, '.', '');
            
            $html .= '<tr class="g">';
            $html .= '<td colspan="4" class="bo" style="border:1px solid #000;border-top:none;">Sub-total (USD)</td>';
            $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;border-top:none;">' . $subtotalUSDFormatted . '</td>';
            $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;border-top:none;">' . $tvaUSDFormatted . '</td>';
            $html .= '<td class="r bo" style="border:1px solid #000;border-left:none;border-top:none;">' . $totalUSDFormatted . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
    }
    
    return $html;
}


private function generateCommonPDFComponents($data, $isPage1)
{
    $invoiceRef = htmlspecialchars($data['invoice_ref'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $paymentMethod = htmlspecialchars($data['payment_method'] ?? 'CREDIT', ENT_QUOTES, 'UTF-8');
    $clientCompany = htmlspecialchars($data['client_company'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $clientAddress = htmlspecialchars($data['client_address'] ?? '', ENT_QUOTES, 'UTF-8');
    $clientRCCM = htmlspecialchars($data['client_rccm'] ?? '', ENT_QUOTES, 'UTF-8');
    $clientNIF = htmlspecialchars($data['client_nif'] ?? '', ENT_QUOTES, 'UTF-8');
    $clientIDNat = htmlspecialchars($data['client_id_nat'] ?? '', ENT_QUOTES, 'UTF-8');
    $clientImportExport = htmlspecialchars($data['client_import_export'] ?? '', ENT_QUOTES, 'UTF-8');
    $clientTVA = htmlspecialchars($data['client_tva'] ?? '', ENT_QUOTES, 'UTF-8');
    $supplier = htmlspecialchars($data['supplier'] ?? '', ENT_QUOTES, 'UTF-8');
    $commodity = htmlspecialchars($data['produit'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
    $facturePFI = htmlspecialchars($data['facture_pfi_no'] ?? '', ENT_QUOTES, 'UTF-8');
    $poFour = htmlspecialchars($data['po_ref'] ?? '', ENT_QUOTES, 'UTF-8');
    $bivacInspection = htmlspecialchars($data['bivac_inspection'] ?? '', ENT_QUOTES, 'UTF-8');
    $transportMode = strtoupper(htmlspecialchars($data['transport_mode_name'] ?? 'ROAD', ENT_QUOTES, 'UTF-8'));
    $transportModeId = (int)($data['transport_mode_id'] ?? 0);
    
    $goodsTypeId = (int)($data['goods_type_id'] ?? 0);
    $client_id = (int)($data['client_id'] ?? 0);
    
    if ($goodsTypeId === 3) {
        if ($transportModeId === 2) {
            $truckTrailerLabel = 'Airway Bill:';
        } else {
            $truckTrailerLabel = 'Truck/Trailer No.:';
        }
        $truckTrailerContainer = '';
    } else {
        if ($transportModeId === 2) {
            $airwayBill = htmlspecialchars($data['airway_bill'] ?? '', ENT_QUOTES, 'UTF-8');
            $truckTrailerLabel = 'Airway Bill:';
            $truckTrailerContainer = $airwayBill;
        } else {
            $horse = htmlspecialchars($data['horse'] ?? '', ENT_QUOTES, 'UTF-8');
            $trailer1 = htmlspecialchars($data['trailer_1'] ?? '', ENT_QUOTES, 'UTF-8');
            $container = htmlspecialchars($data['container'] ?? '', ENT_QUOTES, 'UTF-8');
            $truckTrailerLabel = 'Truck/Trailer No.:';
            $truckTrailerContainer = trim($horse . '/' . $trailer1 . '/' . $container, '/');
        }
    }
    
    $poidsKg = number_format((float)($data['poids_kg'] ?? 0), 2);
    $fretUSD = number_format((float)($data['fret_usd'] ?? 0), 2);
    $assuranceUSD = number_format((float)($data['assurance_usd'] ?? 0), 2);
    $autresChargesUSD = number_format((float)($data['autres_charges_usd'] ?? 0), 2);
    $cifUSD = number_format((float)($data['cif_usd'] ?? 0), 2);
    $cifCDF = number_format((float)($data['cif_cdf'] ?? 0), 2);
    
    $rateCDFInv = (float)($data['rate_cdf_inv'] ?? 2500);
    $rateCDFInvFormatted = number_format($rateCDFInv, 2);
    $rateCDFBCC = (float)($data['rate_cdf_usd_bcc'] ?? 2500);
    $rateCDFBCCFormatted = number_format($rateCDFBCC, 2);
    
    $tariffCode = htmlspecialchars($data['tariff_code_client'] ?? '', ENT_QUOTES, 'UTF-8');
    $fileRef = htmlspecialchars($data['file_ref'] ?? '', ENT_QUOTES, 'UTF-8');
    $exonerationCode = htmlspecialchars($data['exoneration_code'] ?? '', ENT_QUOTES, 'UTF-8');
    $licenseNumber = htmlspecialchars($data['license_number'] ?? '', ENT_QUOTES, 'UTF-8');
    $declarationNo = htmlspecialchars($data['declaration_no'] ?? '', ENT_QUOTES, 'UTF-8');
    $declarationDate = !empty($data['declaration_date']) ? date('d/m/Y', strtotime($data['declaration_date'])) : '';
    $liquidationNo = htmlspecialchars($data['liquidation_no'] ?? '', ENT_QUOTES, 'UTF-8');
    $liquidationDate = !empty($data['liquidation_date']) ? date('d/m/Y', strtotime($data['liquidation_date'])) : '';
    $quittanceNo = htmlspecialchars($data['quittance_no'] ?? '', ENT_QUOTES, 'UTF-8');
$quittanceDate = !empty($data['quittance_date']) ? date('d/m/Y', strtotime($data['quittance_date'])) : '';
$dispatchDate = !empty($data['dispatch_deliver_date']) ? date('d/m/Y', strtotime($data['dispatch_deliver_date'])) : '';
$invoiceCreatedDate = !empty($data['created_at']) ? date('d/m/Y H:i', strtotime($data['created_at'])) : date('d/m/Y H:i');
$invoiceDate = date('d/m/Y H:i');

$displayInvoiceRef = $isPage1 ? ('ND-' . $invoiceRef) : $invoiceRef;
$displayDate = $isPage1 ? $invoiceCreatedDate : $invoiceDate;  // DEBIT NOTE uses created date, FACTURE uses current
$pageTitle = $isPage1 ? 'DEBIT NOTE' : 'FACTURE';
$invoiceLabel = $isPage1 ? 'DEBIT NOTE N°' : 'FACTURE Nº';

    $logoPath = $this->logoPath;
    $logoHtml = file_exists($logoPath) ? '<img src="' . $logoPath . '" style="max-width:240px;max-height:60px;">' : '<b style="font-size:14pt;">MALABAR RDC SARL</b>';

    $commonCSS = '<style>
body{font-family:Arial,sans-serif;font-size:6.5pt;margin:0;padding:3mm;line-height:1.2;}
table{border-collapse:collapse;width:100%;}
td,th{padding:1px 2px;vertical-align:middle;word-wrap:break-word;}
.b td,.b th{border:1px solid #000;}
.r{text-align:right;}.c{text-align:center;}.bo{font-weight:bold;}.g{background:#e0e0e0;}
.bk{background:#000;color:#fff;padding:2px 4px;font-weight:bold;font-size:6.5pt;}
.items-table td{padding:3px 4px !important;line-height:1.4 !important;font-size:6.5pt !important;vertical-align:middle !important;}
.items-table th{padding:4px 5px !important;line-height:1.4 !important;font-size:6.5pt !important;text-align:center !important;vertical-align:middle !important;}
.total-table td{border:1px solid #000;padding:2px 3px;font-size:6.5pt;line-height:1.3;}
.total-table .no-top{border-top:none;}
.reimbursable-header{color:#000;padding:6px 12px;font-weight:bold;font-size:9pt;text-align:center;margin:3mm 0;text-transform:uppercase;letter-spacing:1px;}
</style>';

    $headerHtml = '<table cellpadding="0" cellspacing="0" style="width:100%;border:none;margin-bottom:2mm;">
  <tr>
      <td style="width:50%;border:none;vertical-align:top;">' . $logoHtml . '</td>
      <td style="width:50%;text-align:right;font-size:6.5pt;line-height:1.5;border:none;vertical-align:top;">
          No. 1068, Avenue Ruwe, Quartier Makutano,<br>
          Lubumbashi, DRC<br>
          RCCM: 13-B-1122, ID NAT. 6-9-N91867E<br>
          NIF: A 1309334 L<br>
          VAT Ref # 145/DGI/DGE/INF/BN/TVA/2020<br>
          Capital Social: 45.000.000 FC
          <br>
          Point de vente : Lubumbashi
      </td>
  </tr>
  </table>';

    $headerHtml .= '<div style="border:1px solid #000;padding:3px 10px;font-size:11pt;width:40%;text-align:center;margin:2mm 0;"><b>' . $pageTitle . '</b></div>';

    $headerHtml .= '<table cellpadding="0" cellspacing="0" style="width:100%;border:none;margin-top:2mm;table-layout:fixed;">
  <tr>
      <td style="width:42%;vertical-align:top;border:none;">
          <table style="width:100%;border:1px solid #000;border-collapse:collapse;table-layout:fixed;">
              <tr style="background:#e0e0e0;">
                  <td colspan="2" style="text-align:center;font-weight:bold;padding:4px 2px;border:1px solid #000;font-size:7pt;">CLIENT</td>
              </tr>
              <tr>
                  <td colspan="2" style="padding:5px 3px;border:1px solid #000;font-size:6.5pt;line-height:1.3;vertical-align:top;">
                      <strong style="font-size:7pt;">' . $clientCompany . '</strong> TYPE : PM <br>' . $clientAddress . '
                  </td>
              </tr>
              <tr>
                  <td style="width:43%;padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.RCCM:</td>
                  <td style="width:57%;padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . $clientRCCM . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.NIF.:</td>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . $clientNIF . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.IDN.:</td>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . $clientIDNat . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.IMPORT/EXPORT:</td>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . $clientImportExport . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.TVA:</td>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . $clientTVA . '</td>
              </tr>
          </table>';
  
    if ($goodsTypeId !== 3) {
        $headerHtml .= '
          <table style="width:100%;border:1px solid #000;border-collapse:collapse;margin-top:3mm;table-layout:fixed;">
              <tr>
                  <td style="width:43%;padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">Poids (Kg):</td>
                  <td style="width:57%;padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;vertical-align:middle;">' . $poidsKg . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">Fret/USD:</td>
                  <td style="padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;vertical-align:middle;">' . $fretUSD . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">Autres Charges/USD:</td>
                  <td style="padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;vertical-align:middle;">' . $autresChargesUSD . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">Assurance/USD:</td>
                  <td style="padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;vertical-align:middle;">' . $assuranceUSD . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;font-weight:bold;vertical-align:middle;text-align:left;">CIF/USD:</td>
                  <td style="padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;font-weight:bold;vertical-align:middle;">' . $cifUSD . '</td>
              </tr>
              <tr>
                  <td style="padding:3px;border:1px solid #000;font-size:6.5pt;font-weight:bold;vertical-align:middle;text-align:left;">CIF/CDF:</td>
                  <td style="padding:3px;border:1px solid #000;text-align:left;font-size:6.5pt;font-weight:bold;vertical-align:middle;">' . $cifCDF . '</td>
              </tr>
          </table>';
    }

    $headerHtml .= '
      </td>
      
      <td style="width:6%;border:none;"></td>
      
      <td style="width:49%;vertical-align:top;border:none;">
          <table style="width:100%;border:1px solid #000;border-collapse:collapse;table-layout:fixed;">
              <tr style="background:#e0e0e0;">
                  <td style="width:35%;padding:4px 2px;border:1px solid #000;font-weight:bold;font-size:6.5pt;text-align:left;">' . $invoiceLabel . '</td>
                  <td colspan="3" style="padding:4px 2px;border:1px solid #000;font-size:6.5pt;text-align:center;font-weight:bold;">' . $displayInvoiceRef . '</td>
              </tr>
              <tr>
                  <td style="width:25%;padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Date</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $displayDate . '</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Moyen de Transport</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $transportMode . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $truckTrailerLabel . '</td>
                  <td colspan="3" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $truckTrailerContainer . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Facture/PFI:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $facturePFI . '</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">PO Four.:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $poFour . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Produit.</td>
                  <td colspan="3" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $commodity . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">BIVAC Insp.:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $bivacInspection . '</td>';
    
    if ($goodsTypeId !== 3) {
        $headerHtml .= '
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">License:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $licenseNumber . '</td>';
    } else {
        $headerHtml .= '
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">&nbsp;</td>';
    }
    
    $headerHtml .= '
              </tr>';
  
    if ($goodsTypeId !== 3) {
        $headerHtml .= '
              <tr>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Tariff Code Client:</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $tariffCode . '</td>
              </tr>
              <tr>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Notre N° Ref #:</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $fileRef . '</td>
              </tr>
              <tr>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Exoneration/Code:</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $exonerationCode . '</td>
              </tr>';
    } else {
        $headerHtml .= '
              <tr>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Notre N° Ref #:</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;"></td>
              </tr>';
    }
  
    if ($goodsTypeId !== 3) {
        $headerHtml .= '
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Rate(CDF/USD) BCC:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $rateCDFBCCFormatted . '</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Rate(CDF/USD) Inv. ' . $rateCDFInvFormatted . '</td>
              </tr>';
    }

    if ($goodsTypeId === 3) {
        $m3Value = number_format((float)($data['m3'] ?? 0), 2);
        
        $isClient37GoodsType3 = ($client_id === 37 && $goodsTypeId === 3);
        $qtyLabel = $isClient37GoodsType3 ? 'Qty:' : 'M3:';
        
        $headerHtml .= '
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $qtyLabel . '</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $m3Value . '</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Rate:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $rateCDFBCCFormatted . '</td>
              </tr>';
    } else {
        $headerHtml .= '
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Bank:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">EQUITY BCDC CONGO SA</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Bank Rate:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $rateCDFBCCFormatted . '</td>
              </tr>';
    }
  
    if ($goodsTypeId !== 3) {
        $headerHtml .= '
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Declaration:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $declarationNo . '</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $declarationDate . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Liquidation:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $liquidationNo . '</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $liquidationDate . '</td>
              </tr>
              <tr>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Quittance:</td>
                  <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $quittanceNo . '</td>
                  <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $quittanceDate . '</td>
              </tr>';
    }
  
    $headerHtml .= '
          </table>
      </td>
  </tr>
  </table>';

    $htmlSign = $this->generateSignatureHTML($data);
    $bankHtml = $this->generateBankHTML($data['banks'] ?? []);
    $footerHtml = '<div style="border:1px solid #000;text-align:center;padding:3px;font-size:6.5pt;margin-top:2mm;">Thank you for your business!</div>';
   if (!$isPage1 && !empty($data['qrcode']) && !empty($data['codedefdgi'])) {
    require_once APP_ROOT . '/app/libraries/phpqrcode/qrlib.php';
    ob_start();
    QRcode::png($data['qrcode'], null, QR_ECLEVEL_L, 4, 2);
    $imageData = ob_get_clean();

    $base64Qr = 'data:image/png;base64,' . base64_encode($imageData);
    
    // Extract datetime from final_req JSON if available
    $emcfDateTime = '';
    if (!empty($data['emcf_final_req'])) {
        $finalReq = json_decode($data['emcf_final_req'], true);
        if (!empty($finalReq['dateTime'])) {
            $emcfDateTime = $finalReq['dateTime'];
        }
    }
    
    // Prepare display values (UID not needed)
    $defNim = htmlspecialchars($data['emcf_nim'] ?? '', ENT_QUOTES, 'UTF-8');
    $defHeure = htmlspecialchars($emcfDateTime, ENT_QUOTES, 'UTF-8');
    $codeDEFDGI = htmlspecialchars($data['codedefdgi'], ENT_QUOTES, 'UTF-8');
    $defCompteurs = htmlspecialchars($data['emcf_counters'] ?? '', ENT_QUOTES, 'UTF-8');
    
    $footerHtml .= '<div style="width: 100%; border: 2px solid #000; margin-top: 5mm; background: #fff;">
      <div style="text-align: center; padding: 8px; background: #000; color: #fff; font-weight: bold; font-size: 8.5pt; letter-spacing: 1px;">
        ÉLÉMENT DE SÉCURITÉ DE LA FACTURE NORMALISÉE
      </div>
      <table style="width: 100%; border-collapse: collapse; padding: 10px;">
        <tr>
          <td style="width: 25%; text-align: center; vertical-align: top; padding: 10px;">
            <img style="width:100px; height:100px; display: block; margin: 0 auto; border: 1px solid #ccc; padding: 2px; background: #fff;" src="' . $base64Qr . '" alt="QR Code">
          </td>
          <td style="width: 75%; vertical-align: top; padding: 10px 15px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
              <tr style="background: #f8f8f8;">
                <td style="width: 28%; padding: 3px 5px; font-weight: bold;">DEF NID:</td>
                <td style="padding: 3px 5px; font-weight: 600;">' . $defNim . '</td>
              </tr>
              <tr>
                <td style="padding: 3px 5px; font-weight: bold;">DEF Heure:</td>
                <td style="padding: 3px 5px;">' . $defHeure . '</td>
              </tr>
              <tr style="background: #f8f8f8;">
                <td style="padding: 3px 5px; font-weight: bold;">Code DEF/DGI:</td>
                <td style="padding: 3px 5px; font-weight: 600; font-size: 8pt;">' . $codeDEFDGI . '</td>
              </tr>
              <tr>
                <td style="padding: 3px 5px; font-weight: bold;">DEF Compteurs:</td>
                <td style="padding: 3px 5px; font-weight: 600;">' . $defCompteurs . '</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>';
}
    
    $items = $data['items'] ?? [];
    $hiddenCategories = $data['hidden_categories'] ?? [];
    $first_categoty_edited = $data['first_categoty_edited'] ?? 'H';

    $page1SubTotal = 0;
    $page1TVA = 0;
    $page2SubTotal = 0;
    $page2TVA = 0;

    $isGoodsType3 = ($goodsTypeId === 3);
    $totalM3 = (float)($data['m3'] ?? 0);

    $firstItemProcessed = [3 => false, 4 => false];

    foreach ($items as $item) {
        $categoryId = (int)($item['category_id'] ?? 0);
      
        if (in_array($categoryId, $hiddenCategories)) {
            continue;
        }
        
        // Skip Frais Bancaires if first_categoty_edited is 'H'
        $itemName = strtoupper(trim($item['item_name'] ?? ''));
        if ($itemName === 'FRAIS BANCAIRES' && $first_categoty_edited === 'H') {
            continue;
        }
        
        if ($categoryId === 1) {
            // Category 1: Convert CDF to USD
            $subtotalCDF = (float)($item['rate_cdf'] ?? 0);
            $tvaCDF = (float)($item['vat_cdf'] ?? 0);
            
            $subtotal = $rateCDFBCC > 0 ? ($subtotalCDF / $rateCDFBCC) : 0;
            $tva = $rateCDFBCC > 0 ? ($tvaCDF / $rateCDFBCC) : 0;
            
        } else if ($categoryId === 2) {
            // Category 2: Always use quantity * taux_usd (regardless of goods_type_id)
            $subtotal = (float)($item['subtotal_usd'] ?? 0);
            $tva = (float)($item['tva_usd'] ?? 0);
            
            // If subtotal is 0, calculate from quantity and rate
            if ($subtotal == 0) {
                $quantity = (float)($item['quantity'] ?? 1);
                $tauxUsd = (float)($item['taux_usd'] ?? 0);
                $subtotal = $quantity * $tauxUsd;
                
                $hasTVA = (int)($item['has_tva'] ?? 0) === 1;
                $tva = $hasTVA ? ($subtotal * 0.16) : 0;
            }
            
        } else if ($isGoodsType3 && ($categoryId === 3 || $categoryId === 4) && !$firstItemProcessed[$categoryId]) {
            // Category 3/4 for Goods Type 3: First item uses M3
            $tauxUsd = (float)($item['taux_usd'] ?? 0);
            $subtotal = $totalM3 * $tauxUsd;
          
            $hasTVA = (int)($item['has_tva'] ?? 0) === 1;
            $tva = $hasTVA ? ($subtotal * 0.16) : 0;
          
            $firstItemProcessed[$categoryId] = true;
            
        } else if ($isGoodsType3 && ($categoryId === 3 || $categoryId === 4) && $firstItemProcessed[$categoryId]) {
            // Category 3/4 for Goods Type 3: Skip subsequent items
            continue;
            
        } else {
            // All other items: Use quantity * taux_usd
            $subtotal = (float)($item['subtotal_usd'] ?? 0);
            $tva = (float)($item['tva_usd'] ?? 0);
            
            // If subtotal is 0, calculate from quantity and rate
            if ($subtotal == 0) {
                $quantity = (float)($item['quantity'] ?? 1);
                $tauxUsd = (float)($item['taux_usd'] ?? 0);
                $subtotal = $quantity * $tauxUsd;
                
                $hasTVA = (int)($item['has_tva'] ?? 0) === 1;
                $tva = $hasTVA ? ($subtotal * 0.16) : 0;
            }
        }
      
        if ($categoryId === 1 || $categoryId === 2) {
            $page1SubTotal += $subtotal;
            $page1TVA += $tva;
        }
        else if ($categoryId === 3 || $categoryId === 4) {
            $page2SubTotal += $subtotal;
            $page2TVA += $tva;
        }
    }

    $page1Total = $page1SubTotal + $page1TVA;
    $page1TotalCDF = $page1Total * $rateCDFBCC;

    $page2Total = $page2SubTotal + $page2TVA;

    $paymentMethod = $data['payment_method'] ?? 'CREDIT';

    if ($isPage1) {
        $totalHtml = $this->generatePage1TotalHTML($page1Total, $rateCDFBCC, $htmlSign, $paymentMethod, $goodsTypeId);
    } else {
        $arspEnabled = strtolower($data['arsp'] ?? 'disabled') === 'enabled';
        $totalHtml = $this->generatePage2TotalHTML($page2SubTotal, $page2TVA, $page2Total, $arspEnabled, $htmlSign, $rateCDFInv, $paymentMethod, $goodsTypeId);
    }

    return [$commonCSS, $headerHtml, $bankHtml, $footerHtml, $htmlSign, $totalHtml];
}
  private function generateSignatureHTML($data)
  {
    $html = "";
    if (!empty($data['signature_path']) && file_exists($data['signature_path'])) {
        $html .= '<div style="text-align:center;">';
        $html .= '<img src="' . $data['signature_path'] . '" style="max-width:120px;max-height:45px;display:block;margin:0 auto;">';
        if (!empty($data['username'])) {
            $html .= '<div style="font-size:6pt;margin-top:2px;">Opérateur: ' . htmlspecialchars($data['username'], ENT_QUOTES, 'UTF-8') . '</div>';
        }
        $html .= '</div>';
    }
    return $html;
  }

private function generatePage1TotalHTML($grandTotal, $rateCDFBCC, $htmlSign, $paymentMethod = 'CREDIT', $goods_type_id = 0)
{
    $grandTotalFormatted = number_format($grandTotal, 2);
    $equivalentCDF = $grandTotal * $rateCDFBCC;
    $equivalentCDFFormatted = number_format($equivalentCDF, 2);
    
    $grandTotalWords = $this->numberToWordsFrench($grandTotal);
    $equivalentCDFWords = $this->numberToWordsFrench($equivalentCDF);
    
    $html = '<table style="width:100%;border-collapse:collapse;margin-top:2mm;">
        <tr>
            <td style="width:67%;border:none;"></td>
            <td style="width:22%;padding:4px 6px;vertical-align:middle;border:1px solid #000;" class="r bo">Grand Total</td>
            <td style="width:11%;padding:4px 6px;vertical-align:middle;border:1px solid #000;border-left:none;" class="r bo">$ ' . $grandTotalFormatted . '</td>
        </tr>';
    
    $html .= '<tr>
        <td style="width:67%;border:none;"></td>
        <td style="width:22%;padding:4px 6px;vertical-align:middle;border:1px solid #000;border-top:none;" class="r">Equivalent en CDF:</td>
        <td style="width:11%;padding:4px 6px;vertical-align:middle;border:1px solid #000;border-left:none;border-top:none;" class="r bo">' . $equivalentCDFFormatted . ' </td>
    </tr>';
    
    $html .= '</table>';
    
    $html .= '<div style="text-align:right;font-size:6.5pt;margin-top:2mm;margin-right:5mm;font-weight:bold;">
        Taux de change: 1 USD = ' . number_format($rateCDFBCC, 2) . ' CDF
    </div>';
    
    $html .= '<div style="margin-top:-45px;padding-left:0px;">' . $htmlSign . '</div>';
    
    $html .= '<div style="margin-top:5mm;font-size:6.5pt;line-height:1.4;">
        <div style="margin-bottom:3mm;"><strong>Mode de paiement :</strong> ' . htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') . '</div>
        <div style="margin-bottom:2mm;"><strong>Montant en lettres (USD):</strong><br>' . $grandTotalWords . ' DOLLARS AMÉRICAINS</div>';
    
    $html .= '<div><strong>Montant en lettres (CDF):</strong><br>' . $equivalentCDFWords . ' FRANCS CONGOLAIS</div>';
    
    $html .= '</div>';
    
    return $html;
}
  
private function generatePage2TotalHTML($totalExclTVA, $totalTVA, $grandTotal, $arspEnabled, $htmlSign, $rateCDFInv, $paymentMethod = 'CREDIT', $goods_type_id = 0)
{
    $totalRowsPage2 = $arspEnabled ? 6 : 4;
    
    if ($arspEnabled) {
        $arspTax = $grandTotal * 0.012;
        $netPayable = $grandTotal + $arspTax;
        $equivalentCDF = $netPayable * $rateCDFInv;
        $grandTotalWords = $this->numberToWordsFrench($netPayable);
    } else {
        $equivalentCDF = $grandTotal * $rateCDFInv;
        $grandTotalWords = $this->numberToWordsFrench($grandTotal);
    }
    $equivalentCDFWords = $this->numberToWordsFrench($equivalentCDF);
    
    $html = '<table class="total-table" style="margin-top:2mm;"><tr><td style="width:67%;border:none;text-align:center;vertical-align:middle;" rowspan="' . $totalRowsPage2 . '">' . $htmlSign . '</td>';
    $html .= '<td style="width:22%;" class="r">Total excl. TVA</td>';
    $html .= '<td style="width:11%;border-left:none;" class="r">$ ' . number_format($totalExclTVA, 2) . '</td></tr>';
    $html .= '<tr><td style="width:22%;" class="no-top r">TVA 16%</td>';
    $html .= '<td style="width:11%;" class="no-top r" style="border-left:none;">$ ' . number_format($totalTVA, 2) . '</td></tr>';
    $html .= '<tr><td style="width:22%;" class="no-top r bo">Grand Total</td>';
    $html .= '<td style="width:11%;" class="no-top r bo" style="border-left:none;">$ ' . number_format($grandTotal, 2) . '</td></tr>';
    
    if ($arspEnabled) {
        $arspTax = $grandTotal * 0.012;
        $netPayable = $grandTotal + $arspTax;
        
        $html .= '<tr><td style="width:22%;" class="no-top r">ARSP Tax (1.2%)</td>';
        $html .= '<td style="width:11%;" class="no-top r" style="border-left:none;">$ ' . number_format($arspTax, 2) . '</td></tr>';
        $html .= '<tr><td style="width:22%;" class="no-top r bo">Net Payable</td>';
        $html .= '<td style="width:11%;" class="no-top r bo" style="border-left:none;">$ ' . number_format($netPayable, 2) . '</td></tr>';
    }
    
    $html .= '<tr><td style="width:22%;" class="no-top r">Equivalent en CDF</td>';
    $html .= '<td style="width:11%;" class="no-top r" style="border-left:none;">' . number_format($equivalentCDF, 2) . ' </td></tr>';
    
    $html .= '</table>';
    
    $html .= '<div style="text-align:right;font-size:6.5pt;margin-top:2mm;margin-right:5mm;font-weight:bold;">
        Taux de change: 1 USD = ' . number_format($rateCDFInv, 2) . ' CDF
    </div>';
    
    $html .= '<div style="margin-top:5mm;font-size:6.5pt;line-height:1.4;">
        <div style="margin-bottom:3mm;"><strong>Mode de paiement :</strong> ' . htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') . '</div>
        <div style="margin-bottom:2mm;"><strong>Montant en lettres (USD):</strong><br>' . $grandTotalWords . ' DOLLARS AMÉRICAINS</div>';
    
    $html .= '<div><strong>Montant en lettres (CDF):</strong><br>' . $equivalentCDFWords . ' FRANCS CONGOLAIS</div>';
    
    $html .= '</div>';
    
    return $html;
}


  private function generateBankHTML($banks)
  {
    $html = '';
    if (!empty($banks)) {
        $html .= '<div style="text-align:center;font-size:5pt;margin:3mm 0 2mm 0;text-transform:uppercase;">VEUILLEZ TROUVER CI-DESSOUS LES DETAILS DE NOTRE COMPTE BANCAIRE</div>';
        $html .= '<table style="width:100%;"><tr>';
        
        foreach ($banks as $index => $bank) {
            $bankNameFull = htmlspecialchars($bank['invoice_bank_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $accountName = htmlspecialchars($bank['invoice_bank_account_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $accountNumber = htmlspecialchars($bank['invoice_bank_account_number'] ?? '', ENT_QUOTES, 'UTF-8');
            $swift = htmlspecialchars($bank['invoice_bank_swift'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $tdStyle = 'width:49%;vertical-align:top;' . ($index > 0 ? 'padding-left:2%;' : '');
            
            $html .= '<td style="' . $tdStyle . '">';
            $html .= '<table style="width:100%;border:1px solid #000;">';
            $html .= '<tr><td style="width:25%;padding:2px 4px;">INTITULE</td><td style="padding:2px 4px;">' . $accountName . '</td></tr>';
            $html .= '<tr><td style="padding:2px 4px;">N.COMPTE</td><td style="padding:2px 4px;">' . $accountNumber . '</td></tr>';
            $html .= '<tr><td style="padding:2px 4px;">SWIFT</td><td style="padding:2px 4px;">' . $swift . '</td></tr>';
            $html .= '<tr><td style="padding:2px 4px;">BANQUE</td><td style="padding:2px 4px;">' . $bankNameFull . '</td></tr>';
            $html .= '</table></td>';
        }
        
        if (count($banks) == 1) {
            $html .= '<td style="width:49%;"></td>';
        }
        
        $html .= '</tr></table>';
    }
    return $html;
  }

  private function numberToWordsFrench($number)
  {
    $number = number_format($number, 2, '.', '');
    list($integer, $decimal) = explode('.', $number);
    
    $integer = (int)$integer;
    $decimal = (int)$decimal;
    
    $words = $this->convertNumberToWordsFrench($integer);
    
    if ($decimal > 0) {
      return strtoupper($words . ' VIRGULE ' . $this->convertNumberToWordsFrench($decimal));
    }
    
    return strtoupper($words);
  }

  private function convertNumberToWordsFrench($number)
  {
    if ($number == 0) return 'ZÉRO';
    
    $ones = ['', 'UN', 'DEUX', 'TROIS', 'QUATRE', 'CINQ', 'SIX', 'SEPT', 'HUIT', 'NEUF'];
    $tens = ['', 'DIX', 'VINGT', 'TRENTE', 'QUARANTE', 'CINQUANTE', 'SOIXANTE', 'SOIXANTE', 'QUATRE-VINGT', 'QUATRE-VINGT'];
    $teens = ['DIX', 'ONZE', 'DOUZE', 'TREIZE', 'QUATORZE', 'QUINZE', 'SEIZE', 'DIX-SEPT', 'DIX-HUIT', 'DIX-NEUF'];
    
    $words = '';
    
    if ($number >= 1000000000) {
      $billions = floor($number / 1000000000);
      if ($billions == 1) {
        $words .= 'UN MILLIARD ';
      } else {
        $words .= $this->convertNumberToWordsFrench($billions) . ' MILLIARDS ';
      }
      $number %= 1000000000;
    }
    
    if ($number >= 1000000) {
      $millions = floor($number / 1000000);
      if ($millions == 1) {
        $words .= 'UN MILLION ';
      } else {
        $words .= $this->convertNumberToWordsFrench($millions) . ' MILLIONS ';
      }
      $number %= 1000000;
    }
    
    if ($number >= 1000) {
      $thousands = floor($number / 1000);
      if ($thousands == 1) {
        $words .= 'MILLE ';
      } else {
        $words .= $this->convertNumberToWordsFrench($thousands) . ' MILLE ';
      }
      $number %= 1000;
    }
    
    if ($number >= 100) {
      $hundreds = floor($number / 100);
      if ($hundreds == 1) {
        $words .= 'CENT ';
      } else {
        $words .= $ones[$hundreds] . ' CENT ';
      }
      $number %= 100;
    }
    
    if ($number >= 20) {
      $tensDigit = floor($number / 10);
      $onesDigit = $number % 10;
      
      if ($tensDigit == 7 || $tensDigit == 9) {
        $words .= $tens[$tensDigit] . '-';
        if ($tensDigit == 7) {
          if ($onesDigit == 1) {
            $words .= 'ET-ONZE ';
          } else {
            $words .= $teens[$onesDigit] . ' ';
          }
        } else {
          $words .= $teens[$onesDigit] . ' ';
        }
        $number = 0;
      } else if ($tensDigit == 8) {
        if ($onesDigit == 0) {
          $words .= 'QUATRE-VINGTS ';
        } else {
          $words .= 'QUATRE-VINGT-' . $ones[$onesDigit] . ' ';
        }
        $number = 0;
      } else {
        $words .= $tens[$tensDigit];
        if ($onesDigit == 1 && $tensDigit != 8) {
          $words .= '-ET-UN ';
        } else if ($onesDigit > 0) {
          $words .= '-' . $ones[$onesDigit] . ' ';
        } else {
          $words .= ' ';
        }
        $number = 0;
      }
    }
    
    if ($number >= 10 && $number < 20) {
      $words .= $teens[$number - 10] . ' ';
      $number = 0;
    }
    
    if ($number > 0) {
      $words .= $ones[$number] . ' ';
    }
    
    return trim($words);
  }

private function validateInvoiceData($post, $invoiceId = null)
{
  $errors = [];
  
  if (empty($post['client_id'])) {
    $errors[] = 'Client is required';
  }
  
  if (empty($post['license_ids'])) {
    $errors[] = 'At least one License is required';
  }
  
  if (empty($post['mca_ids'])) {
    $errors[] = 'At least one MCA Reference is required';
  }
  
  if (empty($post['invoice_ref'])) {
    $errors[] = 'Invoice Reference is required';
  }
  
  if (empty($post['payment_method'])) {
    $errors[] = 'Payment Method is required';
  }

  if (!empty($post['invoice_ref'])) {
    $invoiceRef = $this->clean($post['invoice_ref']);
    $sql = "SELECT id FROM import_invoices_t WHERE invoice_ref = ?";
    $params = [$invoiceRef];
    
    if ($invoiceId) {
      $sql .= " AND id != ?";
      $params[] = $invoiceId;
    }
    
    $existing = $this->db->customQuery($sql, $params);
    if (!empty($existing)) {
      $errors[] = 'Invoice Reference already exists';
    }
  }

  if (!empty($errors)) {
    return ['success' => false, 'message' => implode(', ', $errors)];
  }

  return ['success' => true];
}

  private function validateCsrfToken()
  {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
      $this->logError("CSRF validation failed - empty token");
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
      exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
      $this->logError("CSRF validation failed - token mismatch");
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
      exit;
    }
  }

  private function sanitizeArray($data)
  {
    if (!is_array($data)) return [];
    return array_map(function($item) {
      if (is_array($item)) {
        return array_map(function($v) {
          return is_string($v) ? htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $v;
        }, $item);
      }
      return $item;
    }, $data);
  }

  private function sanitizeInput($value)
  {
    if (is_array($value)) return array_map([$this, 'sanitizeInput'], $value);
    if (!is_string($value)) return $value;
    return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace(chr(0), '', $value)));
  }

  private function clean($value)
  {
    if ($value === null || $value === '') return null;
    $value = $this->sanitizeInput($value);
    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return strlen($value) > 255 ? substr($value, 0, 255) : $value;
  }

  private function toInt($value)
  {
    if ($value === null || $value === '' || !is_numeric($value)) return null;
    $int = (int)$value;
    return $int > 0 ? $int : null;
  }

  private function toDecimal($value)
  {
    if ($value === null || $value === '') return null;
    if (!is_numeric($value)) return 0.00;
    return round((float)$value, 2);
  }

  private function toDecimal4($value)
  {
    if ($value === null || $value === '') return null;
    if (!is_numeric($value)) return 0.0000;
    return round((float)$value, 4);
  }

  private function toDate($value)
  {
    if (empty($value)) return null;
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : null;
  }

  private function logError($message)
  {
    @file_put_contents($this->logFile, "[" . date('Y-m-d H:i:s') . "] {$message}\n", FILE_APPEND);
  }
}