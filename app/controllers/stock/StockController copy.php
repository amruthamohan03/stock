<?php

class StockController extends Controller {

    public function index()
    {
        $db = new Database();

        $items    = $db->selectData('item_master_t', 'id, item_name', conditions: ['display' => 'Y']);
        $sql = "SELECT im.id, im.indent_no, DATE_FORMAT(im.indent_date, '%d-%m-%Y') AS indent_date
                FROM indent_master_t im
                WHERE im.display = 'Y'
                AND EXISTS (
                    SELECT 1 FROM indent_item_t ii
                    WHERE ii.indent_id = im.id AND ii.status_id <> 3
                )
                ORDER BY im.id DESC";
        $indents  = $db->customQuery($sql);
        $locations = $db->selectData('issued_to_master_t', 'id, location_code, location_name', ['display' => 'Y']);

        $query = "SELECT st.*,
                  sb.location, sb.item_id,
                  i.item_name,
                  im.indent_no,
                  lt_to.location_name   AS issued_to_location_name,
                  lt_from.location_name AS transferred_from_location_name,
                  u.username            AS created_by_name
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb             ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i             ON sb.item_id = i.id
                  LEFT JOIN indent_master_t im          ON st.indent_id = im.id
                  LEFT JOIN issued_to_master_t lt_to    ON st.issued_to_location_id = lt_to.id
                  LEFT JOIN issued_to_master_t lt_from  ON st.transferred_from_location_id = lt_from.id
                  LEFT JOIN users_t u                   ON st.created_by = u.id
                  ORDER BY st.transaction_date DESC, st.id DESC
                  LIMIT 100";
        $transactions = $db->customQuery($query);

        $transactionsByItem = [];
        if (!empty($transactions)) {
            foreach ($transactions as $trans) {
                $itemId = $trans['item_id'];
                if (!isset($transactionsByItem[$itemId])) {
                    $transactionsByItem[$itemId] = [
                        'item_name'    => $trans['item_name'],
                        'transactions' => []
                    ];
                }
                $transactionsByItem[$itemId]['transactions'][] = $trans;
            }
        }

        $data = [
            'title'              => 'Stock Book Management',
            'items'              => $items,
            'locations'          => $locations,
            'transactions'       => $transactions,
            'transactionsByItem' => $transactionsByItem,
            'indents'            => $indents
        ];

        $this->viewWithLayout('stock/stock', $data);
    }

    // ---------------------------------------------------------------------------
    // AJAX helpers
    // ---------------------------------------------------------------------------

    public function getAvailableIndents()
    {
        header('Content-Type: application/json');
        $db = new Database();
        $query = "SELECT im.id, im.indent_no, im.book_no, im.indent_date,
                  c.college_name, im.item_type, im.institution_id
                  FROM indent_master_t im
                  LEFT JOIN college_t c ON im.institution_id = c.id
                  WHERE im.status IN ('ISSUED', 'RECEIVED') AND im.stock_issued = 'N'
                  ORDER BY im.indent_date DESC, im.id DESC";
        echo json_encode(['success' => true, 'data' => $db->customQuery($query)]);
        exit;
    }

    public function getIndentItems()
    {
        header('Content-Type: application/json');
        $indent_id = isset($_GET['indent_id']) ? (int)$_GET['indent_id'] : 0;
        if ($indent_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }
        $db    = new Database();
        $query = "SELECT ii.*, i.item_name, im.indent_no
                  FROM indent_item_t ii
                  LEFT JOIN item_master_t i ON ii.item_id = i.id
                  LEFT JOIN indent_master_t im ON ii.indent_id = im.id
                  WHERE ii.indent_id = " . $indent_id . " AND status_id = 1
                  ORDER BY ii.sl_no";
        echo json_encode(['success' => true, 'data' => $db->customQuery($query)]);
        exit;
    }

    public function getLocations()
    {
        header('Content-Type: application/json');
        $db = new Database();
        echo json_encode(['success' => true,
            'data' => $db->selectData('issued_to_master_t', 'id, location_code, location_name', ['display' => 'Y'])
        ]);
        exit;
    }

    public function getTransaction()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Transaction ID']);
            exit;
        }
        $db    = new Database();
        $query = "SELECT st.*,
                  sb.location, sb.item_id,
                  i.item_name, im.indent_no,
                  lt_to.location_name   AS issued_to_location_name,
                  lt_from.location_name AS transferred_from_location_name
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb             ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i             ON sb.item_id = i.id
                  LEFT JOIN indent_master_t im          ON st.indent_id = im.id
                  LEFT JOIN issued_to_master_t lt_to    ON st.issued_to_location_id = lt_to.id
                  LEFT JOIN issued_to_master_t lt_from  ON st.transferred_from_location_id = lt_from.id
                  WHERE st.id = " . $id;
        $transaction = $db->customQuery($query);
        if (empty($transaction)) {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $transaction[0]]);
        exit;
    }

    // ---------------------------------------------------------------------------
    // CREATE transaction  (Bug fix: proper null handling for all FK fields)
    // ---------------------------------------------------------------------------
    public function createTransaction()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $db = new Database();

        // ── Core fields ──────────────────────────────────────────────────────
        $item_id          = isset($_POST['item_id'])          ? (int)$_POST['item_id']          : 0;
        $location         = isset($_POST['location'])         ? htmlspecialchars(trim($_POST['location']), ENT_QUOTES) : '';
        $transaction_type = isset($_POST['transaction_type']) ? trim($_POST['transaction_type']) : '';
        $transaction_date = isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : '';

        // ── Optional FK fields – MUST use null when empty, NOT 0 ─────────────
        $indent_id          = $this->nullableInt($_POST['indent_id']          ?? '');
        $indent_item_id     = $this->nullableInt($_POST['indent_item_id']     ?? '');
        $issued_to_location_id        = $this->nullableInt($_POST['issued_to_location_id']          ?? '');
        $transferred_from_location_id = $this->nullableInt($_POST['transferred_from_location_id']   ?? '');

        // ── String fields ────────────────────────────────────────────────────
        $voucher_no       = $this->nullableStr($_POST['voucher_no']       ?? '');
        $voucher_date     = $this->nullableDate($_POST['voucher_date']    ?? '');
        $received_from    = $this->nullableStr($_POST['received_from']    ?? '');
        $receipt_qty      = isset($_POST['receipt_qty'])   ? (int)$_POST['receipt_qty']   : 0;
        $issue_qty        = isset($_POST['issue_qty'])     ? (int)$_POST['issue_qty']     : 0;
        $brought_forward  = isset($_POST['brought_forward'])? (int)$_POST['brought_forward'] : 0;
        $carried_over     = isset($_POST['carried_over'])  ? (int)$_POST['carried_over']  : 0;
        $item_status      = !empty($_POST['item_status'])  ? $_POST['item_status']         : 'WORKING';
        $serial_no        = $this->nullableStr($_POST['serial_no']        ?? '');
        $receiver_initial = $this->nullableStr($_POST['receiver_initial'] ?? '');
        $remarks          = $this->nullableStr($_POST['remarks']          ?? '');

        // ── Validations ───────────────────────────────────────────────────────
        if ($item_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select an item']); exit;
        }
        if (empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Storage location is required']); exit;
        }
        if (empty($transaction_type)) {
            echo json_encode(['success' => false, 'message' => 'Transaction type is required']); exit;
        }
        if (empty($transaction_date)) {
            echo json_encode(['success' => false, 'message' => 'Transaction date is required']); exit;
        }
        $validTypes = ['RECEIPT', 'ISSUE', 'BROUGHT_FORWARD', 'ADJUSTMENT', 'TRANSFER'];
        if (!in_array($transaction_type, $validTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid transaction type']); exit;
        }

        // TRANSFER requires a source location
        if ($transaction_type === 'TRANSFER' && empty($transferred_from_location_id)) {
            echo json_encode(['success' => false, 'message' => 'Please select the location transferred FROM']); exit;
        }

        // ── Get or create stock book ──────────────────────────────────────────
        $stockBook = $db->selectData('stock_book_t', '*', ['item_id' => $item_id, 'location' => $location]);

        if (empty($stockBook)) {
            $stock_book_id = $db->insertData('stock_book_t', [
                'item_id'          => $item_id,
                'location'         => $location,
                'opening_balance'  => $brought_forward,
                'current_balance'  => $brought_forward,
                'created_by'       => 1 // Replace with session user id
            ]);
            $current_balance = $brought_forward;
            if ($indent_id && $indent_item_id) {
                $db->updateData('indent_item_t', ['status_id' => 3], ['indent_id' => $indent_id, 'id' => $indent_item_id]);
            }
        } else {
            $stock_book_id   = $stockBook[0]['id'];
            $current_balance = $stockBook[0]['current_balance'];
        }

        // ── Calculate new balance ─────────────────────────────────────────────
        if (in_array($transaction_type, ['RECEIPT', 'BROUGHT_FORWARD', 'TRANSFER'])) {
            // TRANSFER into our stock = stock IN
            $new_balance = $current_balance + $receipt_qty;
        } elseif ($transaction_type === 'ISSUE') {
            $new_balance = $current_balance - $issue_qty;
        } else { // ADJUSTMENT
            $new_balance = $current_balance + $receipt_qty - $issue_qty;
        }

        // ── Insert transaction ────────────────────────────────────────────────
        $transactionData = [
            'stock_book_id'                => $stock_book_id,
            'transaction_date'             => $transaction_date,
            'transaction_type'             => $transaction_type,
            'indent_id'                    => $indent_id,
            'indent_item_id'               => $indent_item_id,
            'voucher_no'                   => $voucher_no,
            'voucher_date'                 => $voucher_date,
            'received_from'                => $received_from,
            'receipt_qty'                  => $receipt_qty,
            'issue_qty'                    => $issue_qty,
            'balance_qty'                  => $new_balance,
            'brought_forward'              => $brought_forward,
            'carried_over'                 => $carried_over,
            'item_status'                  => $item_status,
            'serial_no'                    => $serial_no,
            'issued_to_location_id'        => $issued_to_location_id,
            'transferred_from_location_id' => $transferred_from_location_id,
            'receiver_initial'             => $receiver_initial,
            'remarks'                      => $remarks,
            'created_by'                   => 1 // Replace with session user id
        ];

        $transaction_id = $db->insertData('stock_transaction_t', $transactionData);

        if ($transaction_id) {
            $db->updateData('stock_book_t', ['current_balance' => $new_balance], ['id' => $stock_book_id]);

            // Serial number tracking
            if (!empty($serial_no)) {
                $db->insertData('stock_serial_tracking_t', [
                    'stock_transaction_id'  => $transaction_id,
                    'stock_book_id'         => $stock_book_id,
                    'serial_no'             => $serial_no,
                    'item_status'           => $item_status,
                    'issued_to_location_id' => $issued_to_location_id,
                    'issued_date'           => $transaction_date,
                    'issue_remarks'         => $remarks,
                    'created_by'            => 1
                ]);
            }

            // Mark indent as stock-issued on ISSUE
            if ($transaction_type === 'ISSUE' && !empty($indent_id)) {
                $db->updateData('indent_master_t', [
                    'stock_issued'      => 'Y',
                    'stock_issued_date' => date('Y-m-d H:i:s')
                ], ['id' => $indent_id]);
            }

            echo json_encode(['success' => true, 'message' => 'Transaction recorded successfully', 'transaction_id' => $transaction_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction. Please try again.']);
        }
        exit;
    }

    // ---------------------------------------------------------------------------
    // UPDATE transaction
    // ---------------------------------------------------------------------------
    public function updateTransaction()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $db             = new Database();
        $transaction_id = isset($_POST['transaction_id']) ? (int)$_POST['transaction_id'] : 0;

        if ($transaction_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Transaction ID']);
            exit;
        }

        $oldTransaction = $db->selectData('stock_transaction_t', '*', ['id' => $transaction_id]);
        if (empty($oldTransaction)) {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
            exit;
        }

        $stockBook = $db->selectData('stock_book_t', '*', ['id' => $oldTransaction[0]['stock_book_id']]);
        if (empty($stockBook)) {
            echo json_encode(['success' => false, 'message' => 'Stock book not found']);
            exit;
        }

        // ── Collect update fields ─────────────────────────────────────────────
        $updateData = [
            'transaction_date'             => isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : '',
            'voucher_no'                   => $this->nullableStr($_POST['voucher_no']       ?? ''),
            'voucher_date'                 => $this->nullableDate($_POST['voucher_date']    ?? ''),
            'received_from'                => $this->nullableStr($_POST['received_from']    ?? ''),
            'receipt_qty'                  => isset($_POST['receipt_qty']) ? (int)$_POST['receipt_qty'] : 0,
            'issue_qty'                    => isset($_POST['issue_qty'])   ? (int)$_POST['issue_qty']   : 0,
            'carried_over'                 => isset($_POST['carried_over'])? (int)$_POST['carried_over']: 0,
            'item_status'                  => !empty($_POST['item_status']) ? $_POST['item_status'] : 'WORKING',
            'serial_no'                    => $this->nullableStr($_POST['serial_no']        ?? ''),
            'issued_to_location_id'        => $this->nullableInt($_POST['issued_to_location_id']          ?? ''),
            'transferred_from_location_id' => $this->nullableInt($_POST['transferred_from_location_id']   ?? ''),
            'receiver_initial'             => $this->nullableStr($_POST['receiver_initial'] ?? ''),
            'remarks'                      => $this->nullableStr($_POST['remarks']          ?? ''),
            'updated_by'                   => 1 // Replace with session user id
        ];

        $update = $db->updateData('stock_transaction_t', $updateData, ['id' => $transaction_id]);

        if ($update) {
            $this->recalculateBalance($oldTransaction[0]['stock_book_id']);
            echo json_encode(['success' => true, 'message' => 'Transaction updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed. Please try again.']);
        }
        exit;
    }

    // ---------------------------------------------------------------------------
    // VIEW LEDGER
    // ---------------------------------------------------------------------------
    public function viewLedger($stock_book_id = null)
    {
        if (empty($stock_book_id)) {
            $this->redirect('stock');
            return;
        }

        $db    = new Database();
        $query = "SELECT sb.*, i.item_name
                  FROM stock_book_t sb
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE sb.id = " . (int)$stock_book_id;
        $stockBook = $db->customQuery($query);

        if (empty($stockBook)) {
            $this->redirect('stock');
            return;
        }

        $query = "SELECT st.*,
                  im.indent_no, im.book_no,
                  lt_to.location_name   AS issued_to_location_name,
                  lt_from.location_name AS transferred_from_location_name,
                  u.username            AS created_by_name
                  FROM stock_transaction_t st
                  LEFT JOIN indent_master_t im          ON st.indent_id = im.id
                  LEFT JOIN issued_to_master_t lt_to    ON st.issued_to_location_id = lt_to.id
                  LEFT JOIN issued_to_master_t lt_from  ON st.transferred_from_location_id = lt_from.id
                  LEFT JOIN users_t u                   ON st.created_by = u.id
                  WHERE st.stock_book_id = " . (int)$stock_book_id . "
                  ORDER BY st.transaction_date ASC, st.id ASC";
        $transactions = $db->customQuery($query);

        $this->viewWithLayout('stock/stockledger', [
            'title'        => 'Stock Ledger - ' . $stockBook[0]['item_name'],
            'stockBook'    => $stockBook[0],
            'transactions' => $transactions
        ]);
    }

    // ---------------------------------------------------------------------------
    // STOCK BOOKS list
    // ---------------------------------------------------------------------------
    public function stockBooks()
    {
        $db    = new Database();
        $query = "SELECT sb.*, i.item_name
                  FROM stock_book_t sb
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE sb.display = 'Y'
                  ORDER BY i.item_name, sb.location";
        $this->viewWithLayout('stock/stockbooks', [
            'title'      => 'Stock Books',
            'stockBooks' => $db->customQuery($query)
        ]);
    }

    // ---------------------------------------------------------------------------
    // DELETE transaction
    // ---------------------------------------------------------------------------
    public function deleteTransaction()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Transaction ID']);
            exit;
        }

        $db          = new Database();
        $transaction = $db->selectData('stock_transaction_t', '*', ['id' => $id]);

        if (empty($transaction)) {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
            exit;
        }

        if (!empty($transaction[0]['serial_no'])) {
            $db->deleteData('stock_serial_tracking_t', ['stock_transaction_id' => $id]);
        }

        if ($db->deleteData('stock_transaction_t', ['id' => $id])) {
            $this->recalculateBalance($transaction[0]['stock_book_id']);
            echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        exit;
    }

    // ---------------------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------------------

    /**
     * Returns null when the value is empty/zero, otherwise casts to int.
     * Prevents inserting 0 into nullable FK columns.
     */
    private function nullableInt($value): ?int
    {
        $v = (int)$value;
        return ($v > 0) ? $v : null;
    }

    /**
     * Returns null when string is blank, otherwise sanitises and returns it.
     */
    private function nullableStr($value): ?string
    {
        $v = trim((string)$value);
        return ($v !== '') ? htmlspecialchars($v, ENT_QUOTES) : null;
    }

    /**
     * Returns null for blank/invalid dates.
     */
    private function nullableDate($value): ?string
    {
        $v = trim((string)$value);
        return ($v !== '' && $v !== '0000-00-00') ? $v : null;
    }

    /**
     * Recalculate running balance for all transactions in a stock book.
     * Called after every update or delete.
     */
    private function recalculateBalance(int $stock_book_id): void
    {
        $db        = new Database();
        $stockBook = $db->selectData('stock_book_t', '*', ['id' => $stock_book_id]);
        if (empty($stockBook)) return;

        $balance = (int)$stockBook[0]['opening_balance'];

        $transactions = $db->customQuery(
            "SELECT * FROM stock_transaction_t
             WHERE stock_book_id = " . $stock_book_id . "
             ORDER BY transaction_date ASC, id ASC"
        );

        foreach ($transactions as $trans) {
            // TRANSFER into stock counts as receipt
            if (in_array($trans['transaction_type'], ['RECEIPT', 'BROUGHT_FORWARD', 'TRANSFER'])) {
                $balance += (int)$trans['receipt_qty'];
            } elseif ($trans['transaction_type'] === 'ISSUE') {
                $balance -= (int)$trans['issue_qty'];
            } else { // ADJUSTMENT
                $balance += (int)$trans['receipt_qty'] - (int)$trans['issue_qty'];
            }

            $db->updateData('stock_transaction_t', [
                'balance_qty' => $balance,
                'carried_over' => $balance
            ], ['id' => $trans['id']]);
        }

        $db->updateData('stock_book_t', ['current_balance' => $balance], ['id' => $stock_book_id]);
    }
}