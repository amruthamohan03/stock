<?php
// app/controllers/invoices/ImportcreditController.php
require_once __DIR__ . '/EmcfController.php';
class ImportcreditController extends EmcfController
{
    private $db;
    private $logoPath;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
        $this->logoPath = __DIR__ . '/../../../public/images/logo.jpg';
    }
  /**
     * Display the credit note creation page
     */
    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            redirect('login');
        }

        // Generate CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'Credit Notes Management',
            'csrf_token' => $_SESSION['csrf_token']
        ];

        $this->viewWithLayout('invoices/importcredit', $data);
    }

    /**
     * Handle all CRUD operations
     */
    public function crudData($action = '')
    {
        header('Content-Type: application/json');

        // Verify CSRF token for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }
        }

        try {
            switch ($action) {
                case 'getValidatedInvoices':
                    $this->getValidatedInvoices($this->db);
                    break;
                case 'getInvoiceItemsForCredit':
                    $this->getInvoiceItemsForCredit($this->db);
                    break;
                case 'insert':
                    $this->insert($this->db);
                    break;
                case 'listingCreditNotes':
                    $this->listingCreditNotes($this->db);
                    break;
                case 'deleteCreditNote':
                    $this->deleteCreditNote($this->db);
                    break;
                case 'sendToDGI':
                    $this->sendToDGI($this->db);
                    break;
                case 'viewCreditNotePDF':
                    $this->viewCreditNotePDF($this->db);
                    break;
                case 'finalizeEMCF':
                    $this->finalizeEMCF();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
        } catch (Exception $e) {
            error_log("Credit Note Error [{$action}]: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get validated invoices (validated = 2 only - DGI verified)
     */
    private function getValidatedInvoices($db)
    {
        try {
            $sql = "SELECT 
                        ii.id,
                        ii.invoice_ref,
                        COALESCE(c.short_name, c.company_name) as client_name,
                        ei.codedefdgi
                    FROM import_invoices_t ii
                    LEFT JOIN clients_t c ON ii.client_id = c.id
                    LEFT JOIN emcf_invoice ei ON ii.id = ei.invoice_id AND ei.inv_type = 'IMPORT'
                    WHERE ii.validated = 2
                    AND ii.display = 'Y'
                    AND NOT EXISTS (
                        SELECT 1 FROM credit_notes_t cn 
                        WHERE cn.invoice_id = ii.id
                    )
                    ORDER BY ii.invoice_ref DESC";

            $invoices = $db->customQuery($sql);

            if ($invoices === false || !is_array($invoices)) {
                echo json_encode([
                    'success' => true,
                    'data' => [],
                    'message' => 'No validated invoices found'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $invoices,
                'count' => count($invoices)
            ]);
        } catch (Exception $e) {
            error_log("getValidatedInvoices Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error loading invoices: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get invoice items for credit (categories 3 & 4 only)
     */
    private function getInvoiceItemsForCredit($db)
    {
        try {
            $invoiceId = intval($_GET['invoice_id'] ?? 0);

            if (!$invoiceId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invoice ID is required'
                ]);
                return;
            }

            // Check if credit note already exists
            $checkSql = "SELECT id FROM credit_notes_t WHERE invoice_id = ? LIMIT 1";
            $existing = $db->customQuery($checkSql, [$invoiceId]);
            
            if (!empty($existing)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'A credit note already exists for this invoice'
                ]);
                return;
            }

            $sql = "SELECT 
                        iit.id,
                        iit.invoice_id,
                        iit.category_id,
                        iit.item_id,
                        iit.item_name,
                        iit.unit_id,
                        iit.unit_text,
                        iit.unit_name,
                        iit.quantity,
                        iit.taux_usd,
                        iit.cost_usd,
                        iit.has_tva,
                        iit.tva_usd,
                        iit.total_usd,
                        iit.currency_id,
                        iit.currency_short_name,
                        qc.category_name,
                        qc.category_header as category_header
                    FROM import_invoice_items_t iit
                    LEFT JOIN quotation_categories_t qc ON iit.category_id = qc.id
                    WHERE iit.invoice_id = ?
                    AND iit.category_id IN (3, 4)
                    AND iit.display = 'Y'
                    ORDER BY iit.category_id, iit.sort_order, iit.id";

            $items = $db->customQuery($sql, [$invoiceId]);

            if ($items === false || !is_array($items)) {
                echo json_encode([
                    'success' => true,
                    'items' => []
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error loading items: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Insert new credit note
     */
    private function insert($db)
    {
        try {
            $invoiceId = intval($_POST['invoice_id'] ?? 0);
            $creditNoteRef = trim($_POST['credit_note_ref'] ?? '');
            $creditNoteType = trim($_POST['credit_note_type'] ?? '');
            $codedefDgi = trim($_POST['codedefdgi'] ?? '');
            $creditNoteItems = json_decode($_POST['credit_note_items'] ?? '[]', true);
            $calculatedSubTotal = floatval($_POST['calculated_sub_total'] ?? 0);
            $calculatedVatAmount = floatval($_POST['calculated_vat_amount'] ?? 0);
            $calculatedTotalAmount = floatval($_POST['calculated_total_amount'] ?? 0);
            $userId = $_SESSION['user_id'] ?? 1;

            // Validation
            if (!$invoiceId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invoice is required'
                ]);
                return;
            }

            if (!$creditNoteType || !in_array($creditNoteType, ['COR', 'RAN', 'RAM', 'RRR'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Valid credit note type is required (COR, RAN, RAM, or RRR)'
                ]);
                return;
            }

            if (empty($creditNoteItems)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'At least one item is required'
                ]);
                return;
            }

            // Check if credit note already exists for this invoice
            $checkSql = "SELECT id FROM credit_notes_t WHERE invoice_id = ? LIMIT 1";
            $existing = $db->customQuery($checkSql, [$invoiceId]);
            
            if (!empty($existing)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'A credit note already exists for this invoice'
                ]);
                return;
            }

            if (empty($codedefDgi)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Original invoice DGI reference (codedefdgi) is required'
                ]);
                return;
            }

            // Verify invoice is validated
            $invoiceSql = "SELECT validated FROM import_invoices_t WHERE id = ? AND validated = 2 AND display = 'Y'";
            $invoiceCheck = $db->customQuery($invoiceSql, [$invoiceId]);
            
            if (empty($invoiceCheck)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invoice must be DGI verified (validated = 2) to create credit note'
                ]);
                return;
            }

            // Ensure all totals are NEGATIVE
            $calculatedSubTotal = -abs($calculatedSubTotal);
            $calculatedVatAmount = -abs($calculatedVatAmount);
            $calculatedTotalAmount = -abs($calculatedTotalAmount);

            // Insert credit note
            $creditNoteData = [
                'invoice_id' => $invoiceId,
                'credit_note_ref' => $creditNoteRef,
                'credit_note_type' => $creditNoteType,
                'codedefdgi' => $codedefDgi,
                'calculated_sub_total' => $calculatedSubTotal,
                'calculated_vat_amount' => $calculatedVatAmount,
                'calculated_total_amount' => $calculatedTotalAmount,
                'display' => 'Y',
                'created_by' => $userId
            ];

            $creditNoteId = $db->insertData('credit_notes_t', $creditNoteData);

            if (!$creditNoteId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create credit note'
                ]);
                return;
            }

            // Insert credit note items
            $itemsInserted = 0;
            foreach ($creditNoteItems as $item) {
                // Validate category is 3 or 4
                $catId = intval($item['category_id'] ?? 0);
                if (!in_array($catId, [3, 4])) {
                    continue; // Skip invalid categories
                }

                $itemData = [
                    'credit_note_id' => $creditNoteId,
                    'invoice_item_id' => intval($item['id'] ?? 0),
                    'category_id' => $catId,
                    'item_id' => intval($item['item_id'] ?? 0),
                    'item_name' => $item['item_name'] ?? '',
                    'unit' => $item['unit_id'] ?? null,
                    'unit_text' => $item['unit_text'] ?? 'Unit',
                    'unit_name' => $item['unit_name'] ?? null,
                    'quantity' => floatval($item['quantity'] ?? 1),
                    'taux_usd' => floatval($item['taux_usd'] ?? 0),
                    'has_tva' => intval($item['has_tva'] ?? 0),
                    'tva_usd' => floatval($item['tva_usd'] ?? 0),
                    'total_usd' => floatval($item['total_usd'] ?? 0),
                    'currency_short_name' => $item['currency_short_name'] ?? 'USD',
                    'created_by' => $userId
                ];

                $result = $db->insertData('credit_note_items_t', $itemData);
                if ($result) {
                    $itemsInserted++;
                }
            }

            if ($itemsInserted === 0) {
                // Rollback: delete credit note if no items were inserted
                $db->deleteData('credit_notes_t', ['id' => $creditNoteId]);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to insert credit note items'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Credit note created successfully',
                'credit_note_id' => $creditNoteId
            ]);

        } catch (Exception $e) {
            error_log("Insert Credit Note Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error creating credit note: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * DataTable listing for credit notes
     */
    private function listingCreditNotes($db)
    {
        try {
            $draw = intval($_GET['draw'] ?? 1);
            $start = intval($_GET['start'] ?? 0);
            $length = intval($_GET['length'] ?? 25);
            $searchValue = $_GET['search']['value'] ?? '';
            $orderColumnIndex = intval($_GET['order'][0]['column'] ?? 4);
            $orderDir = $_GET['order'][0]['dir'] ?? 'desc';

            // Column mapping
            $columns = [
                1 => 'cn.credit_note_ref',
                2 => 'ii.invoice_ref',
                3 => 'client_name',
                4 => 'cn.credit_note_type',
                5 => 'cn.created_at',
                6 => 'u.full_name',
                7 => 'cn.calculated_total_amount'
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'cn.created_at';

            // Base query
            $sql = "SELECT 
                        cn.id,
                        cn.credit_note_ref,
                        cn.credit_note_type,
                        cn.codedefdgi,
                        cn.calculated_sub_total,
                        cn.calculated_vat_amount,
                        cn.calculated_total_amount,
                        cn.created_at,
                        ii.invoice_ref,
                        COALESCE(c.short_name, c.company_name) as client_name,
                        u.full_name as created_by_name
                    FROM credit_notes_t cn
                    LEFT JOIN import_invoices_t ii ON cn.invoice_id = ii.id
                    LEFT JOIN clients_t c ON ii.client_id = c.id
                    LEFT JOIN users_t u ON cn.created_by = u.id
                    WHERE cn.display = 'Y'";

            $params = [];

            // Search
            if (!empty($searchValue)) {
                $sql .= " AND (
                    cn.credit_note_ref LIKE ? OR
                    ii.invoice_ref LIKE ? OR
                    cn.credit_note_type LIKE ? OR
                    c.short_name LIKE ? OR
                    c.company_name LIKE ? OR
                    u.full_name LIKE ?
                )";
                $searchParam = "%$searchValue%";
                $params = array_fill(0, 6, $searchParam);
            }

            // Count total records
            $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
            $countResult = $db->customQuery($countSql, $params);
            $totalRecords = $countResult[0]['total'] ?? 0;

            // Add order and limit
            $sql .= " ORDER BY $orderColumn $orderDir LIMIT $start, $length";

            $creditNotes = $db->customQuery($sql, $params);

            if ($creditNotes === false || !is_array($creditNotes)) {
                $creditNotes = [];
            }

            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $creditNotes
            ]);

        } catch (Exception $e) {
            error_log("Listing Credit Notes Error: " . $e->getMessage());
            echo json_encode([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete credit note
     */
    private function deleteCreditNote($db)
    {
        try {
            $id = intval($_POST['id'] ?? 0);

            if (!$id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Credit note ID is required'
                ]);
                return;
            }

            // Delete credit note items first (cascade should handle, but explicit for safety)
            $db->deleteData('credit_note_items_t', ['credit_note_id' => $id]);

            // Delete credit note
            $result = $db->deleteData('credit_notes_t', ['id' => $id]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Credit note deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to delete credit note'
                ]);
            }

        } catch (Exception $e) {
            error_log("Delete Credit Note Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error deleting credit note: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send credit note to DGI (placeholder for now)
     */
    private function sendToDGI($db)
    {
        try {
            $creditNoteId = (int)($_POST['id'] ?? 0);
            
            if ($creditNoteId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid credit note ID']);
                return;
            }

            // Check if credit note exists
            $creditNote = $db->customQuery("SELECT id, credit_note_ref FROM credit_notes_t WHERE id = ?", [$creditNoteId]);
            
            if (empty($creditNote)) {
                echo json_encode(['success' => false, 'message' => 'Credit note not found']);
                return;
            }

            $invoiceData = $this->buildEmcfPayload($creditNoteId);
            //print_r($invoiceData);exit;
            echo $this->sendInvoiceToEmcf($creditNoteId, 'IMP_CREDIT', $invoiceData);

        } catch (Exception $e) {
            error_log("Send to DGI Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error sending to DGI: ' . $e->getMessage()
            ]);
        }
    }

    private function buildEmcfPayload(int $invoiceId): array
    {
        $header = $this->db->customQuery("
            SELECT 
                cnt.credit_note_ref AS invoice_ref, 
                ct.nif_number AS client_nif, 
                ct.company_name AS client_name, 
                ct.email AS client_contact, 
                ct.address AS client_address, 
                ut.id AS operator_id, 
                ut.full_name AS operator_name, 
                ei_credit.uid AS emcf_uid,
                COALESCE(ei_original.codedefdgi, cnt.codedefdgi) AS codedefdgi
            FROM credit_notes_t cnt 
            LEFT JOIN import_invoices_t iit ON iit.id = cnt.invoice_id 
            LEFT JOIN emcf_invoice ei_original ON ei_original.inv_type = 'IMPORT' AND ei_original.invoice_id = cnt.invoice_id 
            LEFT JOIN emcf_invoice ei_credit ON ei_credit.inv_type = 'IMP_CREDIT' AND ei_credit.invoice_id = cnt.id
            LEFT JOIN clients_t ct ON iit.client_id = ct.id 
            LEFT JOIN users_t ut ON cnt.created_by = ut.id 
            WHERE cnt.id = ? LIMIT 1
        ",[$invoiceId]);
        
        $header = $header[0] ?? null;
        if (!$header) {
            throw new Exception('Credit Note not found');
        }
        
        // Validate that original invoice has codedefdgi
        if (empty($header['codedefdgi'])) {
            throw new Exception('Original invoice DGI reference is missing. Cannot send credit note to DGI without the original invoice reference.');
        }

        $res = $this->db->customQuery("
            SELECT
                cnit.item_name,
                (cnit.taux_usd * 2500) AS price,
                cnit.quantity,
                imt.id AS item_code,
                imt.tax_not_tax AS taxGroup
            FROM credit_note_items_t cnit
            LEFT JOIN item_master_t imt ON cnit.item_id = imt.id
            WHERE cnit.credit_note_id = ?
        ",[$invoiceId]);

        return $this->createEmcfPayload($res, $header, $header['codedefdgi']);
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
                /*$sql = "UPDATE import_invoices_t SET validated = 2, updated_by = ?, updated_at = NOW() WHERE id = ?";
                $this->db->customQuery($sql, [$userId, $invoiceId]);*/
                
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

    /**
     * Generate and view credit note PDF
     */
    private function viewCreditNotePDF($db)
    {
        try {
            $id = intval($_GET['id'] ?? 0);

            if (!$id) {
                die('Credit note ID is required');
            }

            // Get credit note data with invoice and client details
            $sql = "SELECT 
                        cn.*,
                        ii.invoice_ref,
                        ii.client_id,
                        ii.payment_method,
                        ii.rate_cdf_inv,
                        ii.rate_cdf_usd_bcc,
                        ii.created_at as invoice_date,
                        ii.transport_mode_id,
                        COALESCE(c.short_name, c.company_name) as client_name,
                        c.company_name as client_full_name,
                        c.address as client_address,
                        c.rccm_number as client_rccm,
                        c.nif_number as client_nif,
                        c.id_nat_number as client_id_nat,
                        c.import_export_number as client_import_export,
                        ei_credit.qrcode,
                        ei_credit.codedefdgi as credit_codedefdgi,
                        ei_credit.uid as emcf_uid,
                        ei_credit.nim as emcf_nim,
                        ei_credit.counters as emcf_counters,
                        ei_credit.date_time as emcf_datetime,
                        ei_credit.final_req as emcf_final_req,
                        ei_original.codedefdgi as original_invoice_codedefdgi
                    FROM credit_notes_t cn
                    LEFT JOIN import_invoices_t ii ON cn.invoice_id = ii.id 
                    LEFT JOIN emcf_invoice ei_credit ON cn.id=ei_credit.invoice_id AND ei_credit.inv_type='IMP_CREDIT'
                    LEFT JOIN emcf_invoice ei_original ON ii.id=ei_original.invoice_id AND ei_original.inv_type='IMPORT'
                    LEFT JOIN clients_t c ON ii.client_id = c.id
                    WHERE cn.id = ? AND cn.display = 'Y'";

            $result = $db->customQuery($sql, [$id]);

            if (empty($result)) {
                die('Credit note not found');
            }

            $creditNote = $result[0];

            // Get credit note items
            $itemsSql = "SELECT 
                            cni.*,
                            qc.category_name,
                            qc.category_header as category_header
                        FROM credit_note_items_t cni
                        LEFT JOIN quotation_categories_t qc ON cni.category_id = qc.id
                        WHERE cni.credit_note_id = ?
                        ORDER BY cni.category_id, cni.id";

            $items = $db->customQuery($itemsSql, [$id]);

            if ($items === false) {
                $items = [];
            }

            // Get user signature
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $signaturePath = null;
            $username = '';
            
            if ($userId > 0) {
                $userResult = $this->db->customQuery("SELECT signature_image, username FROM users_t WHERE id = ? LIMIT 1", [$userId]);
                if (!empty($userResult)) {
                    if (!empty($userResult[0]['signature_image'])) {
                        $signaturePath = __DIR__ . '/../../../public/uploads/signatures/' . $userResult[0]['signature_image'];
                        if (!file_exists($signaturePath)) {
                            $signaturePath = null;
                        }
                    }
                    $username = $userResult[0]['username'] ?? '';
                }
            }

            // Generate PDF
            $this->generateCreditNotePDF($creditNote, $items, $signaturePath, $username);

        } catch (Exception $e) {
            die('Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate Credit Note PDF using mPDF with adapted design from ImportinvoiceController
     */
    private function generateCreditNotePDF($creditNote, $items, $signaturePath = null, $username = '')
    {
        require_once __DIR__ . '/../../../vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_left' => 5,
            'margin_right' => 5
        ]);

        // Prepare data similar to invoice controller
        $data = $this->prepareCreditNotePDFData($creditNote, $items, $signaturePath, $username);
        
        // Generate HTML with adapted Page 2 design
        $html = $this->generateCreditNotePDFHTML($data);
        
        $mpdf->WriteHTML($html);
        $mpdf->Output('Credit_Note_' . preg_replace('/[^a-zA-Z0-9]/', '_', $creditNote['credit_note_ref']) . '.pdf', 'I');
    }

    /**
     * Prepare credit note PDF data (similar to preparePDFData from invoice controller)
     */
    private function prepareCreditNotePDFData($creditNote, $items, $signaturePath, $username)
    {
        $data = [
            'credit_note_ref' => $creditNote['credit_note_ref'] ?? '',
            'credit_note_type' => $creditNote['credit_note_type'] ?? '',
            'created_at' => $creditNote['created_at'] ?? '',
            'invoice_ref' => $creditNote['invoice_ref'] ?? '',
            'invoice_date' => $creditNote['invoice_date'] ?? '',
            'client_name' => $creditNote['client_name'] ?? 'N/A',
            'client_company' => $creditNote['client_full_name'] ?? '',
            'client_address' => $creditNote['client_address'] ?? '',
            'client_rccm' => $creditNote['client_rccm'] ?? '',
            'client_nif' => $creditNote['client_nif'] ?? '',
            'client_id_nat' => $creditNote['client_id_nat'] ?? '',
            'client_import_export' => $creditNote['client_import_export'] ?? '',
            'payment_method' => $creditNote['payment_method'] ?? 'CREDIT',
            'rate_cdf_inv' => $creditNote['rate_cdf_inv'] ?? 2500,
            'rate_cdf_usd_bcc' => $creditNote['rate_cdf_usd_bcc'] ?? 2500,
            'calculated_sub_total' => abs($creditNote['calculated_sub_total'] ?? 0), // Show as positive for display
            'calculated_vat_amount' => abs($creditNote['calculated_vat_amount'] ?? 0),
            'calculated_total_amount' => abs($creditNote['calculated_total_amount'] ?? 0),
            'items' => $items,
            'signature_path' => $signaturePath,
            'username' => $username,
            'qrcode' => $creditNote['qrcode'] ?? '',
            'codedefdgi' => $creditNote['credit_codedefdgi'] ?? '',
            'ref_codedefdgi' => $creditNote['original_invoice_codedefdgi'] ?? '', // Use original invoice's codedefdgi
            'emcf_uid' => $creditNote['emcf_uid'] ?? '',
            'emcf_nim' => $creditNote['emcf_nim'] ?? '',
            'emcf_counters' => $creditNote['emcf_counters'] ?? '',
            'emcf_datetime' => $creditNote['emcf_datetime'] ?? '',
            'emcf_final_req' => $creditNote['emcf_final_req'] ?? '',
            'transport_mode_id' => $creditNote['transport_mode_id'] ?? 0
        ];

        return $data;
    }

    /**
     * Generate credit note PDF HTML (adapted from Page 2 design of invoice controller)
     */
    private function generateCreditNotePDFHTML($data)
    {
        $logoPath = $this->logoPath;
        $logoHtml = file_exists($logoPath) ? '<img src="' . $logoPath . '" style="max-width:240px;max-height:60px;">' : '<b style="font-size:14pt;">MALABAR RDC SARL</b>';

        // Common CSS (adapted from invoice controller)
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
</style>';

        $creditNoteRef = htmlspecialchars($data['credit_note_ref'] ?? '', ENT_QUOTES, 'UTF-8');
        $invoiceRef = htmlspecialchars($data['invoice_ref'] ?? '', ENT_QUOTES, 'UTF-8');
        $createdDate = !empty($data['created_at']) ? date('d/m/Y H:i', strtotime($data['created_at'])) : date('d/m/Y H:i');
        $invoiceDate = !empty($data['invoice_date']) ? date('d/m/Y', strtotime($data['invoice_date'])) : '';
        
        $transportModeId = (int)($data['transport_mode_id'] ?? 0);
        $transportMode = 'ROAD';
        if ($transportModeId > 0) {
            $tmResult = $this->db->customQuery("SELECT transport_mode_name FROM transport_mode_master_t WHERE id = ? LIMIT 1", [$transportModeId]);
            if (!empty($tmResult)) {
                $transportMode = strtoupper($tmResult[0]['transport_mode_name'] ?? 'ROAD');
            }
        }

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' . $commonCSS . '</head><body>';

        // Header section (adapted from invoice controller)
        $html .= '<table cellpadding="0" cellspacing="0" style="width:100%;border:none;margin-bottom:2mm;">
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

        $html .= '<div style="border:1px solid #000;padding:3px 10px;font-size:11pt;width:40%;text-align:center;margin:2mm 0;"><b>FACTURE D\'AVOIR</b></div>';

        // Client and credit note info section (adapted from invoice controller Page 2 design)
        $html .= '<table cellpadding="0" cellspacing="0" style="width:100%;border:none;margin-top:2mm;table-layout:fixed;">
        <tr>
            <td style="width:42%;vertical-align:top;border:none;">
                <table style="width:100%;border:1px solid #000;border-collapse:collapse;table-layout:fixed;">
                    <tr style="background:#e0e0e0;">
                        <td colspan="2" style="text-align:center;font-weight:bold;padding:4px 2px;border:1px solid #000;font-size:7pt;">CLIENT</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:5px 3px;border:1px solid #000;font-size:6.5pt;line-height:1.3;vertical-align:top;">
                            <strong style="font-size:7pt;">' . htmlspecialchars($data['client_company'], ENT_QUOTES, 'UTF-8') . '</strong> TYPE : PM <br>' . htmlspecialchars($data['client_address'], ENT_QUOTES, 'UTF-8') . '
                        </td>
                    </tr>
                    <tr>
                        <td style="width:43%;padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.RCCM:</td>
                        <td style="width:57%;padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . htmlspecialchars($data['client_rccm'], ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                    <tr>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.NIF.:</td>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . htmlspecialchars($data['client_nif'], ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                    <tr>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.IDN.:</td>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . htmlspecialchars($data['client_id_nat'], ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                    <tr>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">No.IMPORT/EXPORT:</td>
                        <td style="padding:3px;border:1px solid #000;font-size:6.5pt;vertical-align:middle;text-align:left;">' . htmlspecialchars($data['client_import_export'], ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                </table>
            </td>
            
            <td style="width:6%;border:none;"></td>
            
            <td style="width:49%;vertical-align:top;border:none;">
                <table style="width:100%;border:1px solid #000;border-collapse:collapse;table-layout:fixed;">
                    <tr style="background:#e0e0e0;">
                        <td style="width:35%;padding:4px 2px;border:1px solid #000;font-weight:bold;font-size:6.5pt;text-align:left;">Facture d Avoir Nº</td>
                        <td colspan="3" style="padding:4px 2px;border:1px solid #000;font-size:6.5pt;text-align:center;font-weight:bold;">' . $creditNoteRef . '</td>
                    </tr>
                    <tr>
                        <td style="width:25%;padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Date</td>
                        <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $createdDate . '</td>
                        <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Moyen de Transport</td>
                        <td style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $transportMode . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Facture Originale:</td>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . $invoiceRef . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Date de Facture:</td>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">' . $invoiceDate . '</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;">Type de Facture d Avoir:</td>
                        <td colspan="2" style="padding:3px 2px;border:1px solid #000;font-size:6.5pt;text-align:left;font-weight:bold;">' . htmlspecialchars($data['credit_note_type'], ENT_QUOTES, 'UTF-8') . '</td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>';

        // DGI Cancelled Invoice Reference - centered below both columns
        $html .= '<div style="margin-top:5mm;text-align:center;">
            <div style="display:inline-block;border:1px solid #000;padding:4px 15px;background:#f8f8f8;">
                <span style="font-size:6.5pt;font-weight:bold;">Référence Facture Annulée DGI: </span>
                <span style="color:#c00;font-size:7pt;font-weight:bold;">' . htmlspecialchars($data['ref_codedefdgi'], ENT_QUOTES, 'UTF-8') . '</span>
            </div>
        </div>';

        // Add spacing before items section
        $html .= '<div style="margin-top:8mm;"></div>';
        
        // Items section (adapted from renderCategories in invoice controller)
        $html .= $this->renderCreditNoteItems($data['items']);

        // Totals section (adapted from generatePage2TotalHTML in invoice controller)
        $html .= $this->generateCreditNoteTotalsHTML($data);

        // Footer with signature and QR code
        $html .= $this->generateCreditNoteFooterHTML($data);

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Render credit note items (adapted from renderCategories in invoice controller)
     */
    private function renderCreditNoteItems($items)
    {
        $html = '';
        
        // Group items by category
        $groupedItems = [];
        foreach ($items as $item) {
            $categoryId = (int)($item['category_id'] ?? 0);
            $categoryHeader = $item['category_header'] ?? $item['category_name'] ?? 'Uncategorized';
            
            if (!isset($groupedItems[$categoryId])) {
                $groupedItems[$categoryId] = [
                    'header' => $categoryHeader,
                    'items' => []
                ];
            }
            $groupedItems[$categoryId]['items'][] = $item;
        }

        foreach ($groupedItems as $categoryData) {
            $categoryHeader = $categoryData['header'];
            $categoryItems = $categoryData['items'];
            
            $html .= '<div class="bk" style="margin-top:2mm;">' . strtoupper(htmlspecialchars($categoryHeader, ENT_QUOTES, 'UTF-8')) . '</div>';
            $html .= '<table class="items-table" style="width:100%;border-collapse:collapse;">';
            
            $html .= '<tr class="g">';
            $html .= '<th style="width:45%;border:1px solid #000;">Description</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;">Unit</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Qty</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">Rate/USD</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TVA/USD</th>';
            $html .= '<th style="width:11%;border:1px solid #000;border-left:none;" class="r">TOTAL/USD</th>';
            $html .= '</tr>';
            
            $categorySubtotal = 0;
            $categoryTVA = 0;
            $categoryTotal = 0;
            
            foreach ($categoryItems as $item) {
                $desc = htmlspecialchars($item['item_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                $unit = htmlspecialchars($item['unit_text'] ?? $item['unit_name'] ?? 'Unit', ENT_QUOTES, 'UTF-8');
                $qty = number_format(abs($item['quantity'] ?? 1), 2);
                $rate = number_format(abs($item['taux_usd'] ?? 0), 2);
                
                $itemTva = abs($item['tva_usd'] ?? 0);
                $itemTotal = abs($item['total_usd'] ?? 0);
                $itemSubtotal = $itemTotal - $itemTva;
                
                $tva = number_format($itemTva, 2);
                $total = '-' . number_format($itemTotal, 2); // Show as negative
                
                $html .= '<tr>';
                $html .= '<td style="border:1px solid #000;border-top:none;border-bottom:none;">' . $desc . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $unit . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $qty . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $rate . '</td>';
                $html .= '<td class="c" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $tva . '</td>';
                $html .= '<td class="r" style="border:1px solid #000;border-top:none;border-bottom:none;border-left:none;">' . $total . '</td>';
                $html .= '</tr>';
                
                $categorySubtotal += $itemSubtotal;
                $categoryTVA += $itemTva;
                $categoryTotal += $itemTotal;
            }
            
            // Sub-total row
            $html .= '<tr class="g">';
            $html .= '<td colspan="3" class="bo" style="border:1px solid #000;">Sub-total</td>';
            $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;">-' . number_format($categorySubtotal, 2) . '</td>';
            $html .= '<td class="c bo" style="border:1px solid #000;border-left:none;">' . number_format($categoryTVA, 2) . '</td>';
            $html .= '<td class="r bo" style="border:1px solid #000;border-left:none;">-' . number_format($categoryTotal, 2) . '</td>';
            $html .= '</tr>';
            
            $html .= '</table>';
        }
        
        return $html;
    }

    /**
     * Generate credit note totals HTML (adapted from generatePage2TotalHTML in invoice controller)
     */
    private function generateCreditNoteTotalsHTML($data)
    {
        $totalExclTVA = abs($data['calculated_sub_total']);
        $totalTVA = abs($data['calculated_vat_amount']);
        $grandTotal = abs($data['calculated_total_amount']);
        $rateCDFInv = (float)($data['rate_cdf_inv'] ?? 2500);
        $paymentMethod = htmlspecialchars($data['payment_method'] ?? 'CREDIT', ENT_QUOTES, 'UTF-8');
        
        $equivalentCDF = $grandTotal * $rateCDFInv;
        $grandTotalWords = $this->numberToWordsFrench($grandTotal);
        $equivalentCDFWords = $this->numberToWordsFrench($equivalentCDF);
        
        // Generate signature HTML
        $htmlSign = $this->generateCreditNoteSignatureHTML($data);
        
        $html = '<table class="total-table" style="margin-top:2mm;"><tr><td style="width:67%;border:none;text-align:center;vertical-align:middle;" rowspan="4">' . $htmlSign . '</td>';
        $html .= '<td style="width:22%;" class="r">Total excl. TVA</td>';
        $html .= '<td style="width:11%;border-left:none;" class="r">-$ ' . number_format($totalExclTVA, 2) . '</td></tr>';
        $html .= '<tr><td style="width:22%;" class="no-top r">TVA 16%</td>';
        $html .= '<td style="width:11%;" class="no-top r" style="border-left:none;">$ ' . number_format($totalTVA, 2) . '</td></tr>';
        $html .= '<tr><td style="width:22%;" class="no-top r bo">Grand Total</td>';
        $html .= '<td style="width:11%;" class="no-top r bo" style="border-left:none;">-$ ' . number_format($grandTotal, 2) . '</td></tr>';
        $html .= '<tr><td style="width:22%;" class="no-top r">Equivalent en CDF</td>';
        $html .= '<td style="width:11%;" class="no-top r" style="border-left:none;">-' . number_format($equivalentCDF, 2, ',', ' ') . ' </td></tr>';
        $html .= '</table>';
        
        $html .= '<div style="text-align:right;font-size:6.5pt;margin-top:2mm;margin-right:5mm;font-weight:bold;">
            Taux de change: 1 USD = ' . number_format($rateCDFInv, 2, ',', ' ') . ' CDF
        </div>';
        
        $html .= '<div style="margin-top:5mm;font-size:6.5pt;line-height:1.4;">
            <div style="margin-bottom:3mm;"><strong>Mode de paiement :</strong> ' . $paymentMethod . '</div>
            <div style="margin-bottom:2mm;"><strong>Montant en lettres (USD):</strong><br>' . $grandTotalWords . ' DOLLARS AMÉRICAINS</div>
            <div><strong>Montant en lettres (CDF):</strong><br>' . $equivalentCDFWords . ' FRANCS CONGOLAIS</div>
        </div>';
        
        return $html;
    }

    /**
     * Generate credit note signature HTML
     */
    private function generateCreditNoteSignatureHTML($data)
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

    /**
     * Generate credit note footer with QR code (adapted from invoice controller)
     */
    private function generateCreditNoteFooterHTML($data)
    {
        $html = '<div style="border:1px solid #000;text-align:center;padding:3px;font-size:6.5pt;margin-top:2mm;">Thank you for your business!</div>';
        
        if (!empty($data['qrcode']) && !empty($data['codedefdgi'])) {
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
            
            $defNim = htmlspecialchars($data['emcf_nim'] ?? '', ENT_QUOTES, 'UTF-8');
            $defHeure = htmlspecialchars($emcfDateTime, ENT_QUOTES, 'UTF-8');
            $codeDEFDGI = htmlspecialchars($data['codedefdgi'], ENT_QUOTES, 'UTF-8');
            $defCompteurs = htmlspecialchars($data['emcf_counters'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $html .= '<div style="width: 100%; border: 2px solid #000; margin-top: 5mm; background: #fff;">
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
        
        return $html;
    }

    /**
     * Convert number to French words (copied from invoice controller)
     */
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

    /**
     * Convert number to French words helper (copied from invoice controller)
     */
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
    
    private function logError($message)
    {
        error_log("[" . date('Y-m-d H:i:s') . "] {$message}");
    }
}