<?php

class DaybookController extends Controller
{

    // ─────────────────────────────────────────────────────────────────
    // INDEX — list page + form dropdowns
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $db = new Database();

        $stockbookTypes   = $db->selectData('stockbook_type_t',    'id, name, code',   ['display' => 'Y']);
        $units            = $db->selectData('daybook_unit_t',      'id, name',         ['display' => 'Y']);
        $items            = $db->selectData('item_master_t',       'id, item_name',    ['display' => 'Y']);
        $serviceProviders = $db->selectData('service_providers_t', 'id, provider_name',['display' => 'Y']);
        $issuedToList     = $db->selectData('issued_to_master_t',  'id, name',         ['display' => 'Y']);
        $users            = $db->selectData('users_t',             'id, username',     ['display' => 'Y']);

        $query = "SELECT dm.*,
                  st.name           AS stockbook_name,
                  sp.provider_name  AS provider_name,
                  ito.name          AS issued_to_name,
                  uv.username       AS verifier_name,
                  uc.username       AS created_by_name,
                  SUM(CASE WHEN di.transaction_type = 'RECEIPT' THEN 1 ELSE 0 END) AS receipt_lines,
                  SUM(CASE WHEN di.transaction_type = 'ISSUE'   THEN 1 ELSE 0 END) AS issue_lines,
                  COALESCE(SUM(CASE WHEN di.transaction_type = 'RECEIPT' THEN di.receipt_amount_rs ELSE 0 END), 0) AS total_receipt_amt,
                  COALESCE(SUM(CASE WHEN di.transaction_type = 'ISSUE'   THEN di.issue_amount_rs   ELSE 0 END), 0) AS total_issue_amt
                  FROM daybook_master_t dm
                  LEFT JOIN stockbook_type_t    st  ON st.id  = dm.stockbook_type_id
                  LEFT JOIN service_providers_t sp  ON sp.id  = dm.service_provider_id
                  LEFT JOIN issued_to_master_t  ito ON ito.id = dm.issued_to_id
                  LEFT JOIN users_t             uv  ON uv.id  = dm.verifier_id
                  LEFT JOIN users_t             uc  ON uc.id  = dm.created_by
                  LEFT JOIN daybook_item_t      di  ON di.daybook_id = dm.id AND di.display = 'Y'
                  WHERE dm.display = 'Y'
                  GROUP BY dm.id
                  ORDER BY dm.document_date DESC, dm.id DESC";
        $result = $db->customQuery($query);

        $data = [
            'title'            => 'Day Book of Stores',
            'stockbookTypes'   => $stockbookTypes,
            'units'            => $units,
            'items'            => $items,
            'serviceProviders' => $serviceProviders,
            'issuedToList'     => $issuedToList,
            'users'            => $users,
            'result'           => $result,
        ];

        $this->viewWithLayout('stock/daybook', $data);
    }

    // ─────────────────────────────────────────────────────────────────
    // CRUD — insertion / updation / deletion
    // ─────────────────────────────────────────────────────────────────
    public function crudData($action = 'insertion')
    {
        $db = new Database();

        // ══ INSERTION ════════════════════════════════════════════════
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $page_no             = htmlspecialchars(trim($_POST['page_no']          ?? ''), ENT_QUOTES);
            $stockbook_type_id   = (int)($_POST['stockbook_type_id']                ?? 0);
            $class               = htmlspecialchars(trim($_POST['class']            ?? ''), ENT_QUOTES);
            $unit_label          = htmlspecialchars(trim($_POST['unit_label']       ?? ''), ENT_QUOTES);
            $receipt_order_no    = htmlspecialchars(trim($_POST['receipt_order_no'] ?? ''), ENT_QUOTES);
            $document_date       = $_POST['document_date']       ?? '';
            $service_provider_id = (int)($_POST['service_provider_id']              ?? 0);
            $invoice_ref         = htmlspecialchars(trim($_POST['invoice_ref']      ?? ''), ENT_QUOTES);
            $invoice_date        = !empty($_POST['invoice_date'])  ? $_POST['invoice_date']       : null;
            $issued_to_id        = !empty($_POST['issued_to_id'])  ? (int)$_POST['issued_to_id']  : null;
            $cr_voucher_ref      = htmlspecialchars(trim($_POST['cr_voucher_ref']   ?? ''), ENT_QUOTES);
            $verifier_id         = !empty($_POST['verifier_id'])   ? (int)$_POST['verifier_id']   : null;
            $remarks             = htmlspecialchars(trim($_POST['remarks']          ?? ''), ENT_QUOTES);

            // ── Validation ───────────────────────────────────────────
            if (empty($page_no)) {
                echo json_encode(['success' => false, 'message' => 'Page number is required']); exit;
            }
            if ($stockbook_type_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a stockbook type']); exit;
            }
            if (empty($document_date)) {
                echo json_encode(['success' => false, 'message' => 'Document date is required']); exit;
            }
            if ($service_provider_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a service provider']); exit;
            }

            // Unique: one record per provider + date
            $existing = $db->customQuery(
                "SELECT id FROM daybook_master_t
                 WHERE service_provider_id = $service_provider_id
                   AND document_date = '$document_date'
                   AND display = 'Y'
                 LIMIT 1"
            );
            if (!empty($existing)) {
                echo json_encode(['success' => false,
                    'message' => 'An entry already exists for this provider on this date. Please edit the existing entry.']);
                exit;
            }

            // Must have at least one receipt item
            $receiptLines = $_POST['receipt_items'] ?? [];
            $hasReceipt   = false;
            foreach ($receiptLines as $r) {
                if ((int)($r['item_id'] ?? 0) > 0) { $hasReceipt = true; break; }
            }
            if (!$hasReceipt) {
                echo json_encode(['success' => false, 'message' => 'Please add at least one receipt item line']); exit;
            }

            // ── Insert master ─────────────────────────────────────────
            $masterData = [
                'page_no'             => $page_no,
                'stockbook_type_id'   => $stockbook_type_id,
                'class'               => $class,
                'unit_label'          => $unit_label,
                'receipt_order_no'    => $receipt_order_no,
                'document_date'       => $document_date,
                'service_provider_id' => $service_provider_id,
                'invoice_ref'         => $invoice_ref,
                'invoice_date'        => $invoice_date,
                'issued_to_id'        => $issued_to_id,
                'cr_voucher_ref'      => $cr_voucher_ref,
                'verifier_id'         => $verifier_id,
                'remarks'             => $remarks,
                'created_by'          => $_SESSION['user_id'] ?? 1,
                'status'              => 'ACTIVE',
                'display'             => 'Y',
            ];

            $daybook_id = $db->insertData('daybook_master_t', $masterData);

            if ($daybook_id) {
                $sl = 1;
                foreach ($receiptLines as $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $daybook_id, $sl++, 'RECEIPT', $item);
                }
                $sl = 1;
                $issueLines = $_POST['issue_items'] ?? [];
                foreach ($issueLines as $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $daybook_id, $sl++, 'ISSUE', $item);
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Day Book entry created successfully',
                    'id'      => $daybook_id,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create Day Book entry']);
            }
            exit;
        }

        // ══ UPDATION ═════════════════════════════════════════════════
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = (int)($_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Day Book ID']); exit;
            }

            $service_provider_id = (int)($_POST['service_provider_id'] ?? 0);
            $document_date       = $_POST['document_date'] ?? '';

            // Uniqueness (exclude self)
            $dup = $db->customQuery(
                "SELECT id FROM daybook_master_t
                 WHERE service_provider_id = $service_provider_id
                   AND document_date = '$document_date'
                   AND id != $id
                   AND display = 'Y'
                 LIMIT 1"
            );
            if (!empty($dup)) {
                echo json_encode(['success' => false,
                    'message' => 'Another entry already exists for this provider on this date.']);
                exit;
            }

            $masterData = [
                'page_no'             => htmlspecialchars(trim($_POST['page_no']          ?? ''), ENT_QUOTES),
                'stockbook_type_id'   => (int)($_POST['stockbook_type_id']                ?? 0),
                'class'               => htmlspecialchars(trim($_POST['class']            ?? ''), ENT_QUOTES),
                'unit_label'          => htmlspecialchars(trim($_POST['unit_label']       ?? ''), ENT_QUOTES),
                'receipt_order_no'    => htmlspecialchars(trim($_POST['receipt_order_no'] ?? ''), ENT_QUOTES),
                'document_date'       => $document_date,
                'service_provider_id' => $service_provider_id,
                'invoice_ref'         => htmlspecialchars(trim($_POST['invoice_ref']      ?? ''), ENT_QUOTES),
                'invoice_date'        => !empty($_POST['invoice_date'])  ? $_POST['invoice_date']       : null,
                'issued_to_id'        => !empty($_POST['issued_to_id'])  ? (int)$_POST['issued_to_id']  : null,
                'cr_voucher_ref'      => htmlspecialchars(trim($_POST['cr_voucher_ref']   ?? ''), ENT_QUOTES),
                'verifier_id'         => !empty($_POST['verifier_id'])   ? (int)$_POST['verifier_id']   : null,
                'remarks'             => htmlspecialchars(trim($_POST['remarks']          ?? ''), ENT_QUOTES),
            ];

            $update = $db->updateData('daybook_master_t', $masterData, ['id' => $id]);

            if ($update !== false) {
                // Soft-delete old item lines, then re-insert
                $db->updateData('daybook_item_t', ['display' => 'N'], ['daybook_id' => $id]);

                $receiptLines = $_POST['receipt_items'] ?? [];
                $issueLines   = $_POST['issue_items']   ?? [];
                $sl = 1;
                foreach ($receiptLines as $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $id, $sl++, 'RECEIPT', $item);
                }
                $sl = 1;
                foreach ($issueLines as $item) {
                    if ((int)($item['item_id'] ?? 0) <= 0) continue;
                    $this->insertItemLine($db, $id, $sl++, 'ISSUE', $item);
                }
                echo json_encode(['success' => true, 'message' => 'Day Book entry updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit;
        }

        // ══ DELETION ═════════════════════════════════════════════════
        elseif ($action === 'deletion') {
            header('Content-Type: application/json');
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Day Book ID']); exit;
            }

            // Soft-delete master and items
            $db->updateData('daybook_item_t',  ['display' => 'N'], ['daybook_id' => $id]);
            $del = $db->updateData('daybook_master_t', ['display' => 'N'], ['id' => $id]);

            if ($del !== false) {
                echo json_encode(['success' => true, 'message' => 'Day Book entry deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
            exit;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // getDayBookById — AJAX for edit modal
    // Returns master + receipt_items + issue_items
    // ─────────────────────────────────────────────────────────────────
    public function getDayBookById()
    {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

        $db     = new Database();
        $master = $db->selectData('daybook_master_t', '*', ['id' => $id, 'display' => 'Y']);
        if (empty($master)) { echo json_encode(['success' => false, 'message' => 'Record not found']); exit; }

        $base = "SELECT di.*, im.item_name
                 FROM daybook_item_t di
                 LEFT JOIN item_master_t im ON im.id = di.item_id
                 WHERE di.daybook_id = $id AND di.display = 'Y'";

        $receiptItems = $db->customQuery($base . " AND di.transaction_type = 'RECEIPT' ORDER BY di.sl_no");
        $issueItems   = $db->customQuery($base . " AND di.transaction_type = 'ISSUE'   ORDER BY di.sl_no");

        echo json_encode([
            'success' => true,
            'data'    => [
                'master'        => $master[0],
                'receipt_items' => $receiptItems ?: [],
                'issue_items'   => $issueItems   ?: [],
            ],
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // viewDayBook — full detail page
    // ─────────────────────────────────────────────────────────────────
    public function viewDayBook($id = null)
{
    if (empty($id)) {
        $this->redirect('daybook');
        return;
    }

    $db = new Database();
    $id = (int)$id;

    /* =====================================================
       MASTER RECORD
    ====================================================== */
    $masterQuery = "
        SELECT 
            dm.*,
            st.name           AS stockbook_name,
            sp.provider_name  AS provider_name,
            ito.name          AS issued_to_name,
            uv.username       AS verifier_name,
            uc.username       AS created_by_name
        
        FROM daybook_master_t dm
        
        LEFT JOIN stockbook_type_t    st  ON st.id  = dm.stockbook_type_id
        LEFT JOIN service_providers_t sp  ON sp.id  = dm.service_provider_id
        LEFT JOIN issued_to_master_t  ito ON ito.id = dm.issued_to_id
        LEFT JOIN users_t             uv  ON uv.id  = dm.verifier_id
        LEFT JOIN users_t             uc  ON uc.id  = dm.created_by
        
        WHERE dm.id = {$id}
        AND dm.display = 'Y'
    ";

    $master = $db->customQuery($masterQuery);

    if (empty($master)) {
        $this->redirect('daybook');
        return;
    }

    /* =====================================================
       BASE ITEM QUERY (COMMON PART)
    ====================================================== */
    $itemBase = "
        SELECT 
            di.*,
            im.item_name,
            un.name AS unit_name,
            ito.name AS issued_to_name
        
        FROM daybook_item_t di
        
        LEFT JOIN item_master_t      im  ON im.id  = di.item_id
        LEFT JOIN daybook_unit_t     un  ON un.id  = di.unit_id
        LEFT JOIN issued_to_master_t ito ON ito.id = di.issued_to_id
        
        WHERE di.daybook_id = {$id}
        AND di.display = 'Y'
    ";

    /* =====================================================
       RECEIPT ITEMS
    ====================================================== */
    $receiptItems = $db->customQuery(
        $itemBase . " AND di.transaction_type = 'RECEIPT' ORDER BY di.sl_no"
    );

    /* =====================================================
       ISSUE ITEMS
    ====================================================== */
    $issueItems = $db->customQuery(
        $itemBase . " AND di.transaction_type = 'ISSUE' ORDER BY di.sl_no"
    );

    /* =====================================================
       VIEW DATA
    ====================================================== */
    $data = [
        'title'        => 'Day Book — '
                          . ($master[0]['provider_name'] ?? '')
                          . ' | '
                          . date('d-m-Y', strtotime($master[0]['document_date'])),

        'master'       => $master[0],
        'receiptItems' => $receiptItems ?: [],
        'issueItems'   => $issueItems   ?: [],
    ];

    /* =====================================================
       LOAD VIEW
    ====================================================== */
    $this->viewWithLayout('stock/daybookview', $data);
}

    // ─────────────────────────────────────────────────────────────────
    // Private helper — insert one item line
    // ─────────────────────────────────────────────────────────────────
    private function insertItemLine($db, int $daybookId, int $slNo, string $transType, array $item): void
    {
        $rQtyN = (float)($item['receipt_qty_number'] ?? 0);
        $rQtyW = (float)($item['receipt_qty_weight'] ?? 0);
        $iQtyN = (float)($item['issue_qty_number']   ?? 0);
        $iQtyW = (float)($item['issue_qty_weight']   ?? 0);
        $rAmt  = (float)($item['receipt_amount_rs']  ?? 0);
        $iAmt  = (float)($item['issue_amount_rs']    ?? 0);

        $db->insertData('daybook_item_t', [
            'daybook_id'         => $daybookId,
            'sl_no'              => $slNo,
            'transaction_type'   => $transType,
            'item_id'            => (int)($item['item_id']          ?? 0),
            'item_description'   => htmlspecialchars(trim($item['item_description'] ?? ''), ENT_QUOTES),
            'unit_id'            => (int)($item['unit_id']          ?? 0),
            'receipt_qty_number' => $rQtyN,
            'receipt_qty_weight' => $rQtyW,
            'issue_qty_number'   => $iQtyN,
            'issue_qty_weight'   => $iQtyW,
            'balance_qty_number' => $rQtyN - $iQtyN,
            'balance_qty_weight' => $rQtyW - $iQtyW,
            'receipt_rate'       => (float)($item['receipt_rate']       ?? 0),
            'receipt_amount_rs'  => $rAmt,
            'receipt_amount_ps'  => (float)($item['receipt_amount_ps']  ?? 0),
            'issue_rate'         => (float)($item['issue_rate']         ?? 0),
            'issue_amount_rs'    => $iAmt,
            'issue_amount_ps'    => (float)($item['issue_amount_ps']    ?? 0),
            'balance_rate'       => (float)($item['balance_rate']       ?? 0),
            'balance_amount_rs'  => $rAmt - $iAmt,
            'balance_amount_ps'  => (float)($item['balance_amount_ps']  ?? 0),
            'value_verifier'     => htmlspecialchars(trim($item['value_verifier'] ?? ''), ENT_QUOTES),
            // Issue-specific indent (null for RECEIPT lines)
            'indent_no'          => ($transType === 'ISSUE' && !empty($item['indent_no']))
                                    ? htmlspecialchars(trim($item['indent_no']), ENT_QUOTES) : null,
            'indent_date'        => ($transType === 'ISSUE' && !empty($item['indent_date']))
                                    ? $item['indent_date'] : null,
            'issued_to_id' => $item['issued_to_id'] ?? null,
            'display'            => 'Y',
        ]);
    }
}