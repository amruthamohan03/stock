<?php

class StockController extends Controller{
    
    public function index()
    {
        $db = new Database();
        
        // Get all items for dropdown
        $items = $db->selectData('item_master_t', 'id, item_name', ['display' => 'Y']);
        
        // Get recent stock transactions (last 50)
        $query = "SELECT st.*, 
                  sb.location,
                  i.item_name,
                  im.indent_no,
                  u.username as created_by_name
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  LEFT JOIN indent_master_t im ON st.indent_id = im.id
                  LEFT JOIN users_t u ON st.created_by = u.id
                  ORDER BY st.transaction_date DESC, st.id DESC
                  LIMIT 50";
        $transactions = $db->customQuery($query);
        
        $data = [
            'title'        => 'Stock Book Management',
            'items'        => $items,
            'transactions' => $transactions
        ];
        
        $this->viewWithLayout('stock/stock', $data);
    }

    // Get available indents for stock issue (indents that are ISSUED or RECEIVED but not yet used in stock)
    public function getAvailableIndents()
    {
        header('Content-Type: application/json');
        
        $db = new Database();
        
        // Get indents that are ISSUED/RECEIVED but stock_issued = 'N'
        $query = "SELECT im.id, im.indent_no, im.book_no, im.indent_date, 
                  c.college_name, im.item_type
                  FROM indent_master_t im
                  LEFT JOIN college_t c ON im.institution_id = c.id
                  WHERE im.status IN ('ISSUED', 'RECEIVED') 
                  AND im.stock_issued = 'N'
                  ORDER BY im.indent_date DESC, im.id DESC";
        
        $indents = $db->customQuery($query);
        
        echo json_encode(['success' => true, 'data' => $indents]);
        exit;
    }

    // Get indent items for selected indent
    public function getIndentItems()
    {
        header('Content-Type: application/json');
        $indent_id = isset($_GET['indent_id']) ? (int)$_GET['indent_id'] : 0;
        
        if ($indent_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }
        
        $db = new Database();
        
        // Get indent items with item details
        $query = "SELECT ii.*, i.item_name 
                  FROM indent_item_t ii
                  LEFT JOIN item_master_t i ON ii.item_id = i.id
                  WHERE ii.indent_id = " . $indent_id . "
                  ORDER BY ii.sl_no";
        
        $items = $db->customQuery($query);
        
        echo json_encode(['success' => true, 'data' => $items]);
        exit;
    }

    // Create stock transaction (Receipt or Issue)
    public function createTransaction()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $db = new Database();
        
        // Get form data
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location']), ENT_QUOTES) : '';
        $transaction_type = isset($_POST['transaction_type']) ? $_POST['transaction_type'] : '';
        $transaction_date = isset($_POST['transaction_date']) ? $_POST['transaction_date'] : '';
        $voucher_no = isset($_POST['voucher_no']) ? htmlspecialchars(trim($_POST['voucher_no']), ENT_QUOTES) : null;
        $voucher_date = isset($_POST['voucher_date']) ? $_POST['voucher_date'] : null;
        $indent_id = isset($_POST['indent_id']) ? (int)$_POST['indent_id'] : null;
        $received_from = isset($_POST['received_from']) ? htmlspecialchars(trim($_POST['received_from']), ENT_QUOTES) : null;
        $issued_to = isset($_POST['issued_to']) ? htmlspecialchars(trim($_POST['issued_to']), ENT_QUOTES) : null;
        $receipt_qty = isset($_POST['receipt_qty']) ? (int)$_POST['receipt_qty'] : 0;
        $issue_qty = isset($_POST['issue_qty']) ? (int)$_POST['issue_qty'] : 0;
        $receiver_initial = isset($_POST['receiver_initial']) ? htmlspecialchars(trim($_POST['receiver_initial']), ENT_QUOTES) : null;
        $remarks = isset($_POST['remarks']) ? htmlspecialchars(trim($_POST['remarks']), ENT_QUOTES) : null;
        
        // Validate
        if ($item_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select an item']);
            exit;
        }
        if (empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Location is required']);
            exit;
        }
        if (empty($transaction_type)) {
            echo json_encode(['success' => false, 'message' => 'Transaction type is required']);
            exit;
        }
        if (empty($transaction_date)) {
            echo json_encode(['success' => false, 'message' => 'Transaction date is required']);
            exit;
        }
        
        // Get or create stock book entry for this item + location
        $stockBook = $db->selectData('stock_book_t', '*', [
            'item_id' => $item_id,
            'location' => $location
        ]);
        
        if (empty($stockBook)) {
            // Create new stock book entry
            $stockBookData = [
                'item_id' => $item_id,
                'location' => $location,
                'opening_balance' => 0,
                'current_balance' => 0,
                'created_by' => 1 // Replace with session user
            ];
            $stock_book_id = $db->insertData('stock_book_t', $stockBookData);
            $current_balance = 0;
        } else {
            $stock_book_id = $stockBook[0]['id'];
            $current_balance = $stockBook[0]['current_balance'];
        }
        
        // Calculate new balance
        if ($transaction_type == 'RECEIPT' || $transaction_type == 'BROUGHT_FORWARD') {
            $new_balance = $current_balance + $receipt_qty;
        } elseif ($transaction_type == 'ISSUE') {
            if ($issue_qty > $current_balance) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock. Current balance: ' . $current_balance]);
                exit;
            }
            $new_balance = $current_balance - $issue_qty;
        } elseif ($transaction_type == 'ADJUSTMENT') {
            $new_balance = $current_balance + $receipt_qty - $issue_qty;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
            exit;
        }
        
        // Insert transaction
        $transactionData = [
            'stock_book_id' => $stock_book_id,
            'transaction_date' => $transaction_date,
            'transaction_type' => $transaction_type,
            'voucher_no' => $voucher_no,
            'voucher_date' => !empty($voucher_date) ? $voucher_date : null,
            'indent_id' => $indent_id,
            'received_from' => $received_from,
            'issued_to' => $issued_to,
            'receipt_qty' => $receipt_qty,
            'issue_qty' => $issue_qty,
            'balance_qty' => $new_balance,
            'receiver_initial' => $receiver_initial,
            'remarks' => $remarks,
            'created_by' => 1 // Replace with session user
        ];
        
        $transaction_id = $db->insertData('stock_transaction_t', $transactionData);
        
        if ($transaction_id) {
            // Update stock book balance
            $db->updateData('stock_book_t', [
                'current_balance' => $new_balance
            ], ['id' => $stock_book_id]);
            
            // If this is an issue transaction with indent, mark indent as stock_issued
            if ($transaction_type == 'ISSUE' && !empty($indent_id)) {
                $db->updateData('indent_master_t', [
                    'stock_issued' => 'Y',
                    'stock_issued_date' => date('Y-m-d H:i:s')
                ], ['id' => $indent_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Transaction recorded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record transaction']);
        }
        exit;
    }

    // View stock ledger for a specific item
    public function viewLedger($stock_book_id = null)
    {
        if (empty($stock_book_id)) {
            $this->redirect('stock');
            return;
        }
        
        $db = new Database();
        
        // Get stock book details
        $query = "SELECT sb.*, i.item_name 
                  FROM stock_book_t sb
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE sb.id = " . (int)$stock_book_id;
        $stockBook = $db->customQuery($query);
        
        if (empty($stockBook)) {
            $this->redirect('stock');
            return;
        }
        
        // Get all transactions for this stock book
        $query = "SELECT st.*, 
                  im.indent_no,
                  im.book_no,
                  u.username as created_by_name
                  FROM stock_transaction_t st
                  LEFT JOIN indent_master_t im ON st.indent_id = im.id
                  LEFT JOIN users_t u ON st.created_by = u.id
                  WHERE st.stock_book_id = " . (int)$stock_book_id . "
                  ORDER BY st.transaction_date ASC, st.id ASC";
        $transactions = $db->customQuery($query);
        
        $data = [
            'title' => 'Stock Ledger - ' . $stockBook[0]['item_name'],
            'stockBook' => $stockBook[0],
            'transactions' => $transactions
        ];
        
        $this->viewWithLayout('stock/stockledger', $data);
    }

    // Get stock book list
    public function stockBooks()
    {
        $db = new Database();
        
        $query = "SELECT sb.*, i.item_name 
                  FROM stock_book_t sb
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE sb.display = 'Y'
                  ORDER BY i.item_name, sb.location";
        $stockBooks = $db->customQuery($query);
        
        $data = [
            'title' => 'Stock Books',
            'stockBooks' => $stockBooks
        ];
        
        $this->viewWithLayout('stock/stockbooks', $data);
    }

    // Delete transaction
    public function deleteTransaction()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Transaction ID']);
            exit;
        }
        
        $db = new Database();
        
        // Get transaction details first
        $transaction = $db->selectData('stock_transaction_t', '*', ['id' => $id]);
        if (empty($transaction)) {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
            exit;
        }
        
        // Delete transaction
        $delete = $db->deleteData('stock_transaction_t', ['id' => $id]);
        
        if ($delete) {
            // Recalculate stock balance
            $this->recalculateBalance($transaction[0]['stock_book_id']);
            
            echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        exit;
    }

    // Recalculate stock balance after deletion
    private function recalculateBalance($stock_book_id)
    {
        $db = new Database();
        
        // Get stock book
        $stockBook = $db->selectData('stock_book_t', '*', ['id' => $stock_book_id]);
        if (empty($stockBook)) return;
        
        $balance = $stockBook[0]['opening_balance'];
        
        // Get all transactions in chronological order
        $query = "SELECT * FROM stock_transaction_t 
                  WHERE stock_book_id = " . (int)$stock_book_id . "
                  ORDER BY transaction_date ASC, id ASC";
        $transactions = $db->customQuery($query);
        
        foreach ($transactions as $trans) {
            $balance = $balance + $trans['receipt_qty'] - $trans['issue_qty'];
            
            // Update transaction balance
            $db->updateData('stock_transaction_t', [
                'balance_qty' => $balance
            ], ['id' => $trans['id']]);
        }
        
        // Update stock book balance
        $db->updateData('stock_book_t', [
            'current_balance' => $balance
        ], ['id' => $stock_book_id]);
    }
}