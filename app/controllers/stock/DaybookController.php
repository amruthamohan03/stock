<?php

class DaybookController extends Controller
{

    public function index()
    {
        $db = new Database();

        // Lookup dropdowns
        $stockbookTypes   = $db->selectData('stockbook_type_t',        'id, name, code',         ['display' => 'Y']);
        $itemTypes        = $db->selectData('daybook_item_type_t',      'id, name',               ['display' => 'Y']);
        $itemCategories   = $db->selectData('daybook_item_category_t',  'id, name, item_type_id', ['display' => 'Y']);
        $units            = $db->selectData('daybook_unit_t',           'id, name',               ['display' => 'Y']);
        $items            = $db->selectData('item_master_t',            'id, item_name',          ['display' => 'Y']);
        $serviceProviders = $db->selectData('service_providers_t',       'id, provider_name',      ['display' => 'Y']);

        // All daybook header records with joined names
        $query = "SELECT dm.*,
                  st.name          AS stockbook_name,
                  sp.provider_name AS provider_name,
                  u1.username      AS created_by_name
                  FROM daybook_master_t dm
                  LEFT JOIN stockbook_type_t  st ON st.id = dm.stockbook_type_id
                  LEFT JOIN service_providers_t sp ON sp.id = dm.service_provider_id
                  LEFT JOIN users_t            u1 ON u1.id = dm.created_by
                  ORDER BY dm.id DESC";
        $result = $db->customQuery($query);

        $data = [
            'title'            => 'Day Book of Stores',
            'stockbookTypes'   => $stockbookTypes,
            'itemTypes'        => $itemTypes,
            'itemCategories'   => $itemCategories,
            'units'            => $units,
            'items'            => $items,
            'serviceProviders' => $serviceProviders,
            'result'           => $result
        ];

        $this->viewWithLayout('stock/daybook', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    // CRUD  (insertion / updation / deletion)
    // ─────────────────────────────────────────────────────────────────

    public function crudData($action = 'insertion')
    {
        $db = new Database();

        // ── INSERTION ────────────────────────────────────────────────
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $page_no             = isset($_POST['page_no'])             ? htmlspecialchars(trim($_POST['page_no']),             ENT_QUOTES) : '';
            $stockbook_type_id   = isset($_POST['stockbook_type_id'])   ? (int)$_POST['stockbook_type_id']                                  : 0;
            $class               = isset($_POST['class'])               ? htmlspecialchars(trim($_POST['class']),               ENT_QUOTES) : '';
            $unit_label          = isset($_POST['unit_label'])          ? htmlspecialchars(trim($_POST['unit_label']),          ENT_QUOTES) : '';
            $transaction_type    = isset($_POST['transaction_type'])    ? htmlspecialchars(trim($_POST['transaction_type']),    ENT_QUOTES) : '';
            $receipt_order_no    = isset($_POST['receipt_order_no'])    ? htmlspecialchars(trim($_POST['receipt_order_no']),    ENT_QUOTES) : '';
            $document_date       = isset($_POST['document_date'])       ? $_POST['document_date']                                           : '';
            $service_provider_id = isset($_POST['service_provider_id']) ? (int)$_POST['service_provider_id']                               : 0;
            $invoice_ref         = isset($_POST['invoice_ref'])         ? htmlspecialchars(trim($_POST['invoice_ref']),         ENT_QUOTES) : '';
            $indent_no           = isset($_POST['indent_no'])           ? htmlspecialchars(trim($_POST['indent_no']),           ENT_QUOTES) : '';
            $indent_date         = !empty($_POST['indent_date'])        ? $_POST['indent_date']                                             : null;
            $verifier_initials   = isset($_POST['verifier_initials'])   ? htmlspecialchars(trim($_POST['verifier_initials']),   ENT_QUOTES) : '';
            $cr_voucher_ref      = isset($_POST['cr_voucher_ref'])      ? htmlspecialchars(trim($_POST['cr_voucher_ref']),      ENT_QUOTES) : '';
            $remarks             = isset($_POST['remarks'])             ? htmlspecialchars(trim($_POST['remarks']),             ENT_QUOTES) : '';

            // Validate required fields
            if (empty($page_no)) {
                echo json_encode(['success' => false, 'message' => 'Page number is required']);
                exit;
            }
            if ($stockbook_type_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a stockbook type']);
                exit;
            }
            if (empty($transaction_type)) {
                echo json_encode(['success' => false, 'message' => 'Transaction type is required']);
                exit;
            }
            if (empty($document_date)) {
                echo json_encode(['success' => false, 'message' => 'Document date is required']);
                exit;
            }
            if ($service_provider_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a service provider / supplier']);
                exit;
            }

            // At least one item line
            $items   = isset($_POST['items']) ? $_POST['items'] : [];
            $hasItem = false;
            foreach ($items as $it) {
                if ((int)($it['item_id'] ?? 0) > 0) { $hasItem = true; break; }
            }
            if (!$hasItem) {
                echo json_encode(['success' => false, 'message' => 'Please add at least one item line']);
                exit;
            }

            // Insert master
            $masterData = [
                'page_no'             => $page_no,
                'stockbook_type_id'   => $stockbook_type_id,
                'class'               => $class,
                'unit_label'          => $unit_label,
                'transaction_type'    => $transaction_type,
                'receipt_order_no'    => $receipt_order_no,
                'document_date'       => $document_date,
                'service_provider_id' => $service_provider_id,
                'invoice_ref'         => $invoice_ref,
                'indent_no'           => $indent_no,
                'indent_date'         => $indent_date,
                'verifier_initials'   => $verifier_initials,
                'cr_voucher_ref'      => $cr_voucher_ref,
                'remarks'             => $remarks,
                'created_by'          => $_SESSION['user_id'] ?? 1,
                'status'              => 'ACTIVE'
            ];

            $daybook_id = $db->insertData('daybook_master_t', $masterData);

            if ($daybook_id) {
                foreach ($items as $slNo => $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $daybook_id, $slNo + 1, $item);
                }
                echo json_encode(['success' => true, 'message' => 'Day Book entry created successfully', 'id' => $daybook_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create Day Book entry']);
            }
            exit;
        }

        // ── UPDATION ─────────────────────────────────────────────────
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Day Book ID']);
                exit;
            }

            $masterData = [
                'page_no'             => htmlspecialchars(trim($_POST['page_no']            ?? ''), ENT_QUOTES),
                'stockbook_type_id'   => (int)($_POST['stockbook_type_id']                  ?? 0),
                'class'               => htmlspecialchars(trim($_POST['class']              ?? ''), ENT_QUOTES),
                'unit_label'          => htmlspecialchars(trim($_POST['unit_label']         ?? ''), ENT_QUOTES),
                'transaction_type'    => htmlspecialchars(trim($_POST['transaction_type']   ?? ''), ENT_QUOTES),
                'receipt_order_no'    => htmlspecialchars(trim($_POST['receipt_order_no']   ?? ''), ENT_QUOTES),
                'document_date'       => $_POST['document_date']                              ?? '',
                'service_provider_id' => (int)($_POST['service_provider_id']                ?? 0),
                'invoice_ref'         => htmlspecialchars(trim($_POST['invoice_ref']        ?? ''), ENT_QUOTES),
                'indent_no'           => htmlspecialchars(trim($_POST['indent_no']          ?? ''), ENT_QUOTES),
                'indent_date'         => !empty($_POST['indent_date']) ? $_POST['indent_date'] : null,
                'verifier_initials'   => htmlspecialchars(trim($_POST['verifier_initials']  ?? ''), ENT_QUOTES),
                'cr_voucher_ref'      => htmlspecialchars(trim($_POST['cr_voucher_ref']     ?? ''), ENT_QUOTES),
                'remarks'             => htmlspecialchars(trim($_POST['remarks']            ?? ''), ENT_QUOTES),
            ];

            $update = $db->updateData('daybook_master_t', $masterData, ['id' => $id]);

            if ($update) {
                $db->deleteData('daybook_item_t', ['daybook_id' => $id]);
                $items = isset($_POST['items']) ? $_POST['items'] : [];
                foreach ($items as $slNo => $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $id, $slNo + 1, $item);
                }
                echo json_encode(['success' => true, 'message' => 'Day Book entry updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit;
        }

        // ── DELETION ─────────────────────────────────────────────────
        elseif ($action === 'deletion') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Day Book ID']);
                exit;
            }

            $delete = $db->deleteData('daybook_master_t', ['id' => $id]);
            if ($delete) {
                echo json_encode(['success' => true, 'message' => 'Day Book entry deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
            exit;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Get single entry by ID  (AJAX — for edit modal prefill)
    // ─────────────────────────────────────────────────────────────────

    public function getDayBookById()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $db     = new Database();
        $master = $db->selectData('daybook_master_t', '*', ['id' => $id]);

        if (empty($master)) {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
            exit;
        }

        // Items with item_master name joined
        $itemsQuery = "SELECT di.*, im.item_name
                       FROM daybook_item_t di
                       LEFT JOIN item_master_t im ON im.id = di.item_id
                       WHERE di.daybook_id = " . $id . "
                       ORDER BY di.sl_no";
        $items = $db->customQuery($itemsQuery);

        echo json_encode(['success' => true, 'data' => ['master' => $master[0], 'items' => $items]]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // Cascade: item categories by item_type_id
    // ─────────────────────────────────────────────────────────────────

    public function getCategoriesByType()
    {
        header('Content-Type: application/json');
        $type_id = isset($_GET['type_id']) ? (int)$_GET['type_id'] : 0;

        if ($type_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid type ID']);
            exit;
        }

        $db         = new Database();
        $categories = $db->selectData('daybook_item_category_t', 'id, name', ['item_type_id' => $type_id, 'display' => 'Y']);

        echo json_encode(['success' => true, 'data' => $categories]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // View single record (detail page)
    // ─────────────────────────────────────────────────────────────────

    public function viewDayBook($id = null)
    {
        if (empty($id)) {
            $this->redirect('daybook');
            return;
        }

        $db = new Database();

        $query = "SELECT dm.*,
                  st.name          AS stockbook_name,
                  sp.provider_name AS provider_name,
                  u1.username      AS created_by_name
                  FROM daybook_master_t dm
                  LEFT JOIN stockbook_type_t   st ON st.id = dm.stockbook_type_id
                  LEFT JOIN service_providers_t sp ON sp.id = dm.service_provider_id
                  LEFT JOIN users_t            u1 ON u1.id = dm.created_by
                  WHERE dm.id = " . (int)$id;
        $master = $db->customQuery($query);

        if (empty($master)) {
            $this->redirect('daybook');
            return;
        }

        $itemsQuery = "SELECT di.*,
                       im.item_name,
                       it.name AS item_type_name,
                       ic.name AS item_category_name,
                       un.name AS unit_name
                       FROM daybook_item_t di
                       LEFT JOIN item_master_t          im ON im.id = di.item_id
                       LEFT JOIN daybook_item_type_t    it ON it.id = di.item_type_id
                       LEFT JOIN daybook_item_category_t ic ON ic.id = di.item_category_id
                       LEFT JOIN daybook_unit_t          un ON un.id = di.unit_id
                       WHERE di.daybook_id = " . (int)$id . "
                       ORDER BY di.sl_no";
        $items = $db->customQuery($itemsQuery);

        $data = [
            'title'  => 'Day Book Entry — Page ' . $master[0]['page_no'],
            'master' => $master[0],
            'items'  => $items
        ];

        $this->viewWithLayout('stock/daybookview', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helper: insert one item line
    // ─────────────────────────────────────────────────────────────────

    private function insertItemLine($db, int $daybookId, int $slNo, array $item): void
    {
        $receiptQtyNum = (float)($item['receipt_qty_number'] ?? 0);
        $issueQtyNum   = (float)($item['issue_qty_number']   ?? 0);
        $receiptQtyWt  = (float)($item['receipt_qty_weight'] ?? 0);
        $issueQtyWt    = (float)($item['issue_qty_weight']   ?? 0);
        $receiptAmt    = (float)($item['receipt_amount_rs']  ?? 0);
        $issueAmt      = (float)($item['issue_amount_rs']    ?? 0);

        $db->insertData('daybook_item_t', [
            'daybook_id'          => $daybookId,
            'sl_no'               => $slNo,
            'item_id'             => (int)($item['item_id']          ?? 0),
            'item_type_id'        => (int)($item['item_type_id']     ?? 0),
            'item_category_id'    => (int)($item['item_category_id'] ?? 0),
            'unit_id'             => (int)($item['unit_id']          ?? 0),
            'receipt_qty_number'  => $receiptQtyNum,
            'receipt_qty_weight'  => $receiptQtyWt,
            'issue_qty_number'    => $issueQtyNum,
            'issue_qty_weight'    => $issueQtyWt,
            'balance_qty_number'  => $receiptQtyNum - $issueQtyNum,
            'balance_qty_weight'  => $receiptQtyWt  - $issueQtyWt,
            'receipt_rate'        => (float)($item['receipt_rate']      ?? 0),
            'receipt_amount_rs'   => $receiptAmt,
            'receipt_amount_ps'   => (float)($item['receipt_amount_ps'] ?? 0),
            'issue_rate'          => (float)($item['issue_rate']        ?? 0),
            'issue_amount_rs'     => $issueAmt,
            'issue_amount_ps'     => (float)($item['issue_amount_ps']   ?? 0),
            'balance_rate'        => (float)($item['balance_rate']      ?? 0),
            'balance_amount_rs'   => $receiptAmt - $issueAmt,
            'balance_amount_ps'   => (float)($item['balance_amount_ps'] ?? 0),
            'value_verifier'      => htmlspecialchars(trim($item['value_verifier'] ?? ''), ENT_QUOTES),
        ]);
    }
}