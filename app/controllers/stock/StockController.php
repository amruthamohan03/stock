<?php

/**
 * StockEntryController - Two-Type Stock Entry System
 * Type 1: Indent-based Entry (from purchase indents)
 * Type 2: Transfer Entry (from other locations)
 */
class StockController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // ========================================================================
    // INDEX - Main entry page with accordion forms
    // ========================================================================

    public function index()
    {
        // FIXED: Changed 'group_item_name_master_t' to 'item_group_t'
        // If your table has a different name, update accordingly
        $itemGroups = $this->db->selectData('group_item_name_master_t', '*', ['display' => 'Y']);
        // Alternative if table is named differently:
        // $itemGroups = $this->db->selectData('group_item_name_master_t', '*', ['display' => 'Y']);
        
        $locations = $this->db->selectData('issued_to_master_t', '*', ['display' => 'Y']);
        $items = $this->db->selectData('item_master_t', '*', ['display' => 'Y']);
        $itemTypes = ['CONSUMABLE', 'NON_CONSUMABLE'];
        $categories = ['FURNITURE', 'ELECTRONIC_EQUIPMENT', 'CONSUMABLES', 'LAB_EQUIPMENT', 'STATIONERY'];

        $data = [
            'title' => 'Two-Type Stock Entry System',
            'itemGroups' => $itemGroups,
            'locations' => $locations,
            'items' => $items,
            'itemTypes' => $itemTypes,
            'categories' => $categories
        ];

        $this->viewWithLayout('stock/stock', $data);
    }

    // ========================================================================
    // TYPE 1: INDENT-BASED ENTRY
    // ========================================================================

    /**
     * Get indents for selected group
     */
    public function getIndentsByGroup()
    { 
        header('Content-Type: application/json');
        
        try {
            $group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
            if ($group_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
                exit;
            }

            // FIXED: Changed i.group_id to i.item_group_id
            $query =  "SELECT 
                        ii.id,
                        ii.indent_id,
                        ii.item_id,
                        ii.qty_issued AS quantity,
                        ii.status_id,
                        i.item_name,
                        mk.make_name,
                        md.model_name,
                        ii.item_description,
                        im.indent_no
                    FROM indent_item_t ii
                    LEFT JOIN item_master_t i ON ii.item_id = i.id
                    LEFT JOIN indent_master_t im ON ii.indent_id = im.id
                    LEFT JOIN make_t mk ON ii.make_id = mk.id
                    LEFT JOIN model_t md ON ii.model_id = md.id
                    WHERE ii.group_id = '$group_id'
                    AND ii.status_id <= 2
                    ORDER BY im.indent_no DESC, ii.sl_no;";

            $items = $this->db->customQuery($query);

            echo json_encode([
                'success' => true,
                'data' => $items ?: []
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get indent items for selected indent
     */
    public function getIndentItems()
    {
        header('Content-Type: application/json');

        try {
            $indent_id = isset($_GET['indent_id']) ? (int)$_GET['indent_id'] : 0;

            if ($indent_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid indent ID']);
                exit;
            }

            $query = "SELECT ii.id, ii.indent_id, ii.item_id, ii.qty_issued as quantity, ii.status_id,
                             i.item_name, ii.make_id, ii.model_id, ii.description,
                             im.indent_no
                      FROM indent_item_t ii
                      LEFT JOIN item_master_t i ON ii.item_id = i.id
                      LEFT JOIN indent_master_t im ON ii.indent_id = im.id
                      WHERE ii.indent_id = $indent_id 
                      AND ii.status_id <= 2
                      ORDER BY ii.sl_no";

            $items = $this->db->customQuery($query);

            echo json_encode([
                'success' => true,
                'data' => $items ?: []
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Save indent-based stock entry
     */
    public function saveIndentEntry()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $indent_id = isset($_POST['indent_id']) ? (int)$_POST['indent_id'] : 0;
            $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
            $transaction_date = isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : date('Y-m-d');
            $received_from = isset($_POST['received_from']) ? htmlspecialchars(trim($_POST['received_from'])) : '';
            $items_data = isset($_POST['items']) ? $_POST['items'] : [];
            $remarks = isset($_POST['remarks']) ? htmlspecialchars(trim($_POST['remarks'])) : null;

            // Validation
            if ($indent_id <= 0) {
                throw new Exception('Invalid indent ID');
            }
            if (empty($location)) {
                throw new Exception('Location is required');
            }
            if (empty($items_data)) {
                throw new Exception('Please select at least one item');
            }

            $userId = $_SESSION['user_id'] ?? 1;
            $batchCode = 'INDENT_' . date('Ymd_His');
            
            // Create batch
            $batchId = $this->db->insertData('stock_entry_batch_t', [
                'batch_code' => $batchCode,
                'entry_type' => 'INDENT_BASED',
                'batch_status' => 'SUBMITTED',
                'batch_date' => $transaction_date,
                'notes' => $remarks,
                'created_by' => $userId,
                'item_count' => count($items_data)
            ]);

            $totalQuantity = 0;

            // Process each item
            foreach ($items_data as $item) {
                $item_id = (int)$item['item_id'];
                $indent_item_id = (int)$item['indent_item_id'];
                $quantity = (int)$item['quantity'];
                $item_status = isset($item['item_status']) ? $item['item_status'] : 'WORKING';

                if ($item_id <= 0 || $quantity <= 0) {
                    continue;
                }

                // Get or create stock book
                $stockBook = $this->db->selectData('stock_book_t', '*', 
                    ['item_id' => $item_id, 'location' => $location]);

                if (empty($stockBook)) {
                    $stock_book_id = $this->db->insertData('stock_book_t', [
                        'item_id' => $item_id,
                        'location' => $location,
                        'opening_balance' => $quantity,
                        'current_balance' => $quantity,
                        'created_by' => $userId
                    ]);
                    $currentBalance = $quantity;
                } else {
                    $stock_book_id = $stockBook[0]['id'];
                    $currentBalance = $stockBook[0]['current_balance'];
                }

                $newBalance = $currentBalance + $quantity;

                // Create transaction record
                $transactionId = $this->db->insertData('stock_transaction_t', [
                    'stock_book_id' => $stock_book_id,
                    'transaction_date' => $transaction_date,
                    'transaction_type' => 'RECEIPT',
                    'stock_entry_type' => 'INDENT_BASED',
                    'indent_id' => $indent_id,
                    'indent_item_id' => $indent_item_id,
                    'receipt_qty' => $quantity,
                    'balance_qty' => $newBalance,
                    'item_status' => $item_status,
                    'received_from' => $received_from,
                    'verification_status' => 'PENDING',
                    'created_by' => $userId,
                    'batch_code' => $batchCode
                ]);

                // Update indent item status to 3 (Accepted)
                $this->db->updateData('indent_item_t', 
                    ['status_id' => 3], 
                    ['id' => $indent_item_id, 'indent_id' => $indent_id]
                );

                // Update stock book balance
                $this->db->updateData('stock_book_t',
                    ['current_balance' => $newBalance],
                    ['id' => $stock_book_id]
                );

                // Create log entry
                $this->createEntryLog($transactionId, 'CREATED', null, [
                    'item_id' => $item_id,
                    'quantity' => $quantity,
                    'balance' => $newBalance
                ], $userId);

                $totalQuantity += $quantity;
            }

            // Update batch
            $this->db->updateData('stock_entry_batch_t',
                ['total_quantity' => $totalQuantity],
                ['id' => $batchId]
            );

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Stock entry saved successfully',
                'batch_code' => $batchCode
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========================================================================
    // TYPE 2: TRANSFER ENTRY
    // ========================================================================

    /**
     * Get items for transfer from selected location
     */
    public function getTransferItems()
    {
        header('Content-Type: application/json');

        $location_id = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;

        if ($location_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid location ID']);
            exit;
        }

        // Get location details
        $location = $this->db->selectData('issued_to_master_t', '*', ['id' => $location_id]);
        if (empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Location not found']);
            exit;
        }

        $locationCode = $location[0]['location_code'];

        // Get available stock items at this location
        $query = "SELECT sb.id as stock_book_id, sb.item_id, sb.location, sb.current_balance,
                         i.item_name, i.make, i.model, i.description
                  FROM stock_book_t sb
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE sb.location = '$locationCode' 
                  AND sb.current_balance > 0
                  ORDER BY i.item_name ASC";

        $items = $this->db->customQuery($query);

        echo json_encode([
            'success' => true,
            'data' => $items
        ]);
        exit;
    }

    /**
     * Get all available items for dropdown (transfer type)
     */
    public function getAvailableItems()
    {
        header('Content-Type: application/json');

        $query = "SELECT DISTINCT i.id, i.item_name, i.make, i.model, i.description,
                         ig.group_name
                  FROM item_master_t i
                  LEFT JOIN item_group_t ig ON i.item_group_id = ig.id
                  WHERE i.display = 'Y'
                  ORDER BY i.item_name ASC";

        $items = $this->db->customQuery($query);

        echo json_encode([
            'success' => true,
            'data' => $items
        ]);
        exit;
    }

    /**
     * Save transfer-based stock entry
     */
    public function saveTransferEntry()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $source_location_id = isset($_POST['source_location_id']) ? (int)$_POST['source_location_id'] : 0;
            $dest_location_id = isset($_POST['dest_location_id']) ? (int)$_POST['dest_location_id'] : 0;
            $transaction_date = isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : date('Y-m-d');
            $item_type = isset($_POST['item_type']) ? $_POST['item_type'] : 'CONSUMABLE';
            $category = isset($_POST['category']) ? $_POST['category'] : '';
            $book_volume = isset($_POST['book_volume']) ? (int)$_POST['book_volume'] : 1;
            $items_data = isset($_POST['items']) ? $_POST['items'] : [];
            $remarks = isset($_POST['remarks']) ? htmlspecialchars(trim($_POST['remarks'])) : null;

            // Validation
            if ($source_location_id <= 0) {
                throw new Exception('Source location is required');
            }
            if ($dest_location_id <= 0) {
                throw new Exception('Destination location is required');
            }
            if ($source_location_id === $dest_location_id) {
                throw new Exception('Source and destination cannot be the same');
            }
            if (empty($items_data)) {
                throw new Exception('Please add at least one item to transfer');
            }

            $userId = $_SESSION['user_id'] ?? 1;
            $batchCode = 'TRANSFER_' . date('Ymd_His');

            // Get location codes
            $sourceLoc = $this->db->selectData('issued_to_master_t', '*', ['id' => $source_location_id]);
            $destLoc = $this->db->selectData('issued_to_master_t', '*', ['id' => $dest_location_id]);

            if (empty($sourceLoc) || empty($destLoc)) {
                throw new Exception('Invalid location selected');
            }

            $sourceLocCode = $sourceLoc[0]['location_code'];
            $destLocCode = $destLoc[0]['location_code'];

            // Create batch
            $batchId = $this->db->insertData('stock_entry_batch_t', [
                'batch_code' => $batchCode,
                'entry_type' => 'TRANSFER',
                'batch_status' => 'SUBMITTED',
                'batch_date' => $transaction_date,
                'notes' => $remarks,
                'created_by' => $userId,
                'item_count' => count($items_data)
            ]);

            $totalQuantity = 0;

            // Process each item
            foreach ($items_data as $item) {
                $item_id = (int)$item['item_id'];
                $quantity = (int)$item['quantity'];
                $condition = isset($item['condition']) ? $item['condition'] : 'GOOD';
                $item_status = isset($item['item_status']) ? $item['item_status'] : 'WORKING';

                if ($item_id <= 0 || $quantity <= 0) {
                    continue;
                }

                // Deduct from source location
                $sourceBook = $this->db->selectData('stock_book_t', '*',
                    ['item_id' => $item_id, 'location' => $sourceLocCode]);

                if (empty($sourceBook) || $sourceBook[0]['current_balance'] < $quantity) {
                    throw new Exception('Insufficient stock at source location for item ID: ' . $item_id);
                }

                $sourceBookId = $sourceBook[0]['id'];
                $sourceNewBalance = $sourceBook[0]['current_balance'] - $quantity;

                // Get or create stock book at destination
                $destBook = $this->db->selectData('stock_book_t', '*',
                    ['item_id' => $item_id, 'location' => $destLocCode]);

                if (empty($destBook)) {
                    $destBookId = $this->db->insertData('stock_book_t', [
                        'item_id' => $item_id,
                        'location' => $destLocCode,
                        'opening_balance' => $quantity,
                        'current_balance' => $quantity,
                        'created_by' => $userId
                    ]);
                    $destNewBalance = $quantity;
                } else {
                    $destBookId = $destBook[0]['id'];
                    $destNewBalance = $destBook[0]['current_balance'] + $quantity;
                }

                // Create deduction transaction (from source)
                $deductionTransId = $this->db->insertData('stock_transaction_t', [
                    'stock_book_id' => $sourceBookId,
                    'transaction_date' => $transaction_date,
                    'transaction_type' => 'TRANSFER',
                    'stock_entry_type' => 'TRANSFER',
                    'issued_to_location_id' => $source_location_id,
                    'transferred_from_location_id' => $source_location_id,
                    'issue_qty' => $quantity,
                    'balance_qty' => $sourceNewBalance,
                    'item_type' => $item_type,
                    'item_category' => $category,
                    'book_volume' => $book_volume,
                    'item_status' => $item_status,
                    'verification_status' => 'PENDING',
                    'created_by' => $userId,
                    'batch_code' => $batchCode
                ]);

                // Create receipt transaction (to destination)
                $receiptTransId = $this->db->insertData('stock_transaction_t', [
                    'stock_book_id' => $destBookId,
                    'transaction_date' => $transaction_date,
                    'transaction_type' => 'RECEIPT',
                    'stock_entry_type' => 'TRANSFER',
                    'transferred_from_location_id' => $source_location_id,
                    'issued_to_location_id' => $dest_location_id,
                    'receipt_qty' => $quantity,
                    'balance_qty' => $destNewBalance,
                    'item_type' => $item_type,
                    'item_category' => $category,
                    'book_volume' => $book_volume,
                    'item_status' => $item_status,
                    'verification_status' => 'PENDING',
                    'created_by' => $userId,
                    'batch_code' => $batchCode
                ]);

                // Create transfer detail record
                $this->db->insertData('stock_transfer_detail_t', [
                    'stock_transaction_id' => $receiptTransId,
                    'source_location_id' => $source_location_id,
                    'destination_location_id' => $dest_location_id,
                    'quantity_transferred' => $quantity,
                    'condition_status' => $condition
                ]);

                // Update stock books
                $this->db->updateData('stock_book_t',
                    ['current_balance' => $sourceNewBalance],
                    ['id' => $sourceBookId]
                );

                $this->db->updateData('stock_book_t',
                    ['current_balance' => $destNewBalance],
                    ['id' => $destBookId]
                );

                // Create log entries
                $this->createEntryLog($deductionTransId, 'CREATED', null, [
                    'quantity' => $quantity,
                    'type' => 'TRANSFER_OUT'
                ], $userId);

                $this->createEntryLog($receiptTransId, 'CREATED', null, [
                    'quantity' => $quantity,
                    'type' => 'TRANSFER_IN'
                ], $userId);

                $totalQuantity += $quantity;
            }

            // Update batch
            $this->db->updateData('stock_entry_batch_t',
                ['total_quantity' => $totalQuantity],
                ['id' => $batchId]
            );

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Transfer entry saved successfully',
                'batch_code' => $batchCode
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========================================================================
    // CRUD OPERATIONS
    // ========================================================================

    /**
     * View all entries with filters
     */
    public function viewAll()
    {
        $entryType = isset($_GET['type']) ? $_GET['type'] : 'ALL';
        $status = isset($_GET['status']) ? $_GET['status'] : 'ALL';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $conditions = [];
        if ($entryType !== 'ALL') {
            $conditions['stock_entry_type'] = $entryType;
        }
        if ($status !== 'ALL') {
            $conditions['verification_status'] = $status;
        }

        $query = "SELECT st.*, sb.item_id, i.item_name, i.make, i.model,
                         u.username as created_by_name,
                         u_ver.username as verified_by_name
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  LEFT JOIN users_t u ON st.created_by = u.id
                  LEFT JOIN users_t u_ver ON st.verified_by = u_ver.id
                  WHERE 1=1";

        if ($entryType !== 'ALL') {
            $query .= " AND st.stock_entry_type = '$entryType'";
        }
        if ($status !== 'ALL') {
            $query .= " AND st.verification_status = '$status'";
        }

        $query .= " ORDER BY st.created_at DESC LIMIT $limit OFFSET $offset";

        $entries = $this->db->customQuery($query);

        $countQuery = "SELECT COUNT(*) as total FROM stock_transaction_t st WHERE 1=1";
        if ($entryType !== 'ALL') {
            $countQuery .= " AND st.stock_entry_type = '$entryType'";
        }
        if ($status !== 'ALL') {
            $countQuery .= " AND st.verification_status = '$status'";
        }
        $countResult = $this->db->customQuery($countQuery);
        $total = $countResult[0]['total'];
        $pages = ceil($total / $limit);

        $data = [
            'title' => 'View Stock Entries',
            'entries' => $entries,
            'entryType' => $entryType,
            'status' => $status,
            'currentPage' => $page,
            'totalPages' => $pages,
            'total' => $total
        ];

        $this->viewWithLayout('stock/stockentry_viewall', $data);
    }

    /**
     * View single entry details
     */
    public function viewSingle()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . APP_URL . 'stock/stockentry/viewAll');
            exit;
        }

        $query = "SELECT st.*, sb.item_id, sb.location, i.item_name, i.make, i.model,
                         u.username as created_by_name,
                         u_ver.username as verified_by_name,
                         im.indent_no
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  LEFT JOIN users_t u ON st.created_by = u.id
                  LEFT JOIN users_t u_ver ON st.verified_by = u_ver.id
                  LEFT JOIN indent_master_t im ON st.indent_id = im.id
                  WHERE st.id = $id";

        $result = $this->db->customQuery($query);
        if (empty($result)) {
            header('Location: ' . APP_URL . 'stock/stockentry/viewAll');
            exit;
        }

        $entry = $result[0];

        // Get logs
        $logs = $this->db->selectData('stock_entry_log_t', '*', 
            ['stock_transaction_id' => $id], 'created_at DESC');

        // Get transfer details if applicable
        $transferDetail = $this->db->selectData('stock_transfer_detail_t', '*',
            ['stock_transaction_id' => $id]);

        $data = [
            'title' => 'View Stock Entry',
            'entry' => $entry,
            'logs' => $logs,
            'transferDetail' => $transferDetail
        ];

        $this->viewWithLayout('stock/stockentry_view', $data);
    }

    /**
     * Edit entry
     */
    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . APP_URL . 'stock/stockentry/viewAll');
            exit;
        }

        $query = "SELECT st.*, sb.item_id, sb.location, i.item_name
                  FROM stock_transaction_t st
                  LEFT JOIN stock_book_t sb ON st.stock_book_id = sb.id
                  LEFT JOIN item_master_t i ON sb.item_id = i.id
                  WHERE st.id = $id AND st.verification_status = 'PENDING'";

        $result = $this->db->customQuery($query);
        if (empty($result)) {
            header('Location: ' . APP_URL . 'stock/stockentry/viewAll');
            exit;
        }

        $entry = $result[0];
        $locations = $this->db->selectData('issued_to_master_t', '*', ['display' => 'Y']);
        $itemTypes = ['CONSUMABLE', 'NON_CONSUMABLE'];
        $categories = ['FURNITURE', 'ELECTRONIC_EQUIPMENT', 'CONSUMABLES', 'LAB_EQUIPMENT', 'STATIONERY'];

        $data = [
            'title' => 'Edit Stock Entry',
            'entry' => $entry,
            'locations' => $locations,
            'itemTypes' => $itemTypes,
            'categories' => $categories
        ];

        $this->viewWithLayout('stock/stockentry_edit', $data);
    }

    /**
     * Update entry via AJAX
     */
    public function updateEntry()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $entry_id = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
            $item_status = isset($_POST['item_status']) ? $_POST['item_status'] : 'WORKING';
            $remarks = isset($_POST['remarks']) ? htmlspecialchars(trim($_POST['remarks'])) : null;

            if ($entry_id <= 0) {
                throw new Exception('Invalid entry ID');
            }

            // Get current entry
            $entry = $this->db->selectData('stock_transaction_t', '*', ['id' => $entry_id]);
            if (empty($entry) || $entry[0]['verification_status'] !== 'PENDING') {
                throw new Exception('Entry cannot be modified');
            }

            $currentEntry = $entry[0];
            $userId = $_SESSION['user_id'] ?? 1;

            // Save old values for log
            $oldValues = [
                'quantity' => $currentEntry['receipt_qty'] ?: $currentEntry['issue_qty'],
                'item_status' => $currentEntry['item_status']
            ];

            $newValues = [
                'quantity' => $quantity,
                'item_status' => $item_status
            ];

            // Update entry
            $updateData = [
                'item_status' => $item_status,
                'remarks' => $remarks
            ];

            if ($currentEntry['receipt_qty'] > 0) {
                $updateData['receipt_qty'] = $quantity;
            } else {
                $updateData['issue_qty'] = $quantity;
            }

            $this->db->updateData('stock_transaction_t', $updateData, ['id' => $entry_id]);

            // Create log
            $this->createEntryLog($entry_id, 'EDITED', $oldValues, $newValues, $userId);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Entry updated successfully'
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Delete entry
     */
    public function deleteEntry()
    {
        header('Content-Type: application/json');

        $entry_id = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
        $reason = isset($_POST['reason']) ? htmlspecialchars(trim($_POST['reason'])) : '';

        if ($entry_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid entry ID']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $entry = $this->db->selectData('stock_transaction_t', '*', ['id' => $entry_id]);
            if (empty($entry) || $entry[0]['verification_status'] !== 'PENDING') {
                throw new Exception('Only pending entries can be deleted');
            }

            $userId = $_SESSION['user_id'] ?? 1;
            $currentEntry = $entry[0];

            // Reverse stock balance if needed
            $stockBook = $this->db->selectData('stock_book_t', '*', 
                ['id' => $currentEntry['stock_book_id']]);

            if (!empty($stockBook)) {
                $currentBalance = $stockBook[0]['current_balance'];
                
                if ($currentEntry['receipt_qty'] > 0) {
                    $newBalance = $currentBalance - $currentEntry['receipt_qty'];
                } else {
                    $newBalance = $currentBalance + $currentEntry['issue_qty'];
                }

                $this->db->updateData('stock_book_t',
                    ['current_balance' => max(0, $newBalance)],
                    ['id' => $currentEntry['stock_book_id']]
                );
            }

            // Create log
            $this->createEntryLog($entry_id, 'DELETED', ['original_data' => $currentEntry], null, $userId, $reason);

            // Delete entry
            $this->db->deleteData('stock_transaction_t', ['id' => $entry_id]);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Entry deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========================================================================
    // VERIFICATION OPERATIONS
    // ========================================================================

    /**
     * Verify entry
     */
    public function verifyEntry()
    {
        header('Content-Type: application/json');

        $entry_id = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
        $reason = isset($_POST['reason']) ? htmlspecialchars(trim($_POST['reason'])) : '';

        if ($entry_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid entry ID']);
            exit;
        }

        try {
            $userId = $_SESSION['user_id'] ?? 1;

            $entry = $this->db->selectData('stock_transaction_t', '*', ['id' => $entry_id]);
            if (empty($entry) || $entry[0]['verification_status'] !== 'PENDING') {
                throw new Exception('Entry cannot be verified');
            }

            $this->db->updateData('stock_transaction_t', [
                'verification_status' => 'VERIFIED',
                'verified_by' => $userId,
                'verified_at' => date('Y-m-d H:i:s')
            ], ['id' => $entry_id]);

            $this->createEntryLog($entry_id, 'VERIFIED', null, ['status' => 'VERIFIED'], $userId, $reason);

            echo json_encode([
                'success' => true,
                'message' => 'Entry verified successfully'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Reject entry
     */
    public function rejectEntry()
    {
        header('Content-Type: application/json');

        $entry_id = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
        $reason = isset($_POST['reason']) ? htmlspecialchars(trim($_POST['reason'])) : '';

        if ($entry_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid entry ID']);
            exit;
        }

        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            $userId = $_SESSION['user_id'] ?? 1;

            $entry = $this->db->selectData('stock_transaction_t', '*', ['id' => $entry_id]);
            if (empty($entry) || $entry[0]['verification_status'] !== 'PENDING') {
                throw new Exception('Entry cannot be rejected');
            }

            $currentEntry = $entry[0];

            // Reverse stock balance
            $stockBook = $this->db->selectData('stock_book_t', '*',
                ['id' => $currentEntry['stock_book_id']]);

            if (!empty($stockBook)) {
                $currentBalance = $stockBook[0]['current_balance'];

                if ($currentEntry['receipt_qty'] > 0) {
                    $newBalance = $currentBalance - $currentEntry['receipt_qty'];
                } else {
                    $newBalance = $currentBalance + $currentEntry['issue_qty'];
                }

                $this->db->updateData('stock_book_t',
                    ['current_balance' => max(0, $newBalance)],
                    ['id' => $currentEntry['stock_book_id']]
                );
            }

            $this->db->updateData('stock_transaction_t', [
                'verification_status' => 'REJECTED',
                'verified_by' => $userId,
                'verified_at' => date('Y-m-d H:i:s')
            ], ['id' => $entry_id]);

            $this->createEntryLog($entry_id, 'REJECTED', null, ['status' => 'REJECTED'], $userId, $reason);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Entry rejected successfully'
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========================================================================
    // REPORTING
    // ========================================================================

    /**
     * View stock by group
     */
    public function viewByGroup()
    {
        $group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

        $itemGroups = $this->db->selectData('item_group_t', '*', ['display' => 'Y']);

        $items = [];
        if ($group_id > 0) {
            $query = "SELECT i.*, sb.location, sb.opening_balance, sb.current_balance,
                             COUNT(st.id) as transaction_count
                      FROM item_master_t i
                      LEFT JOIN stock_book_t sb ON i.id = sb.item_id
                      LEFT JOIN stock_transaction_t st ON sb.id = st.stock_book_id
                      WHERE i.item_group_id = $group_id
                      GROUP BY i.id, sb.id
                      ORDER BY i.item_name ASC";

            $items = $this->db->customQuery($query);
        }

        $data = [
            'title' => 'Stock by Group',
            'itemGroups' => $itemGroups,
            'selectedGroupId' => $group_id,
            'items' => $items
        ];

        $this->viewWithLayout('stock/stockentry_bygroup', $data);
    }

    /**
     * Stock summary report
     */
    public function summary()
    {
        $query = "SELECT 
                    COUNT(DISTINCT st.id) as total_transactions,
                    SUM(CASE WHEN st.stock_entry_type = 'INDENT_BASED' THEN 1 ELSE 0 END) as indent_entries,
                    SUM(CASE WHEN st.stock_entry_type = 'TRANSFER' THEN 1 ELSE 0 END) as transfer_entries,
                    SUM(CASE WHEN st.verification_status = 'VERIFIED' THEN 1 ELSE 0 END) as verified_entries,
                    SUM(CASE WHEN st.verification_status = 'PENDING' THEN 1 ELSE 0 END) as pending_entries,
                    SUM(CASE WHEN st.verification_status = 'REJECTED' THEN 1 ELSE 0 END) as rejected_entries,
                    SUM(CASE WHEN st.receipt_qty IS NOT NULL THEN st.receipt_qty ELSE 0 END) as total_received,
                    SUM(CASE WHEN st.issue_qty IS NOT NULL THEN st.issue_qty ELSE 0 END) as total_issued
                  FROM stock_transaction_t st";

        $summary = $this->db->customQuery($query);

        $batchQuery = "SELECT COUNT(*) as total_batches, 
                             SUM(CASE WHEN batch_status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
                             SUM(CASE WHEN batch_status = 'VERIFIED' THEN 1 ELSE 0 END) as verified
                      FROM stock_entry_batch_t";
        $batchSummary = $this->db->customQuery($batchQuery);

        $data = [
            'title' => 'Stock Entry Summary',
            'summary' => $summary[0] ?? [],
            'batchSummary' => $batchSummary[0] ?? []
        ];

        $this->viewWithLayout('stock/stockentry_summary', $data);
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Create entry log
     */
    private function createEntryLog($transactionId, $action, $oldValues, $newValues, $userId, $reason = null)
    {
        return $this->db->insertData('stock_entry_log_t', [
            'stock_transaction_id' => $transactionId,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'action_by' => $userId,
            'action_reason' => $reason
        ]);
    }

    /**
     * Nullable integer helper
     */
    private function nullableInt($value)
    {
        return !empty($value) ? (int)$value : null;
    }

    /**
     * Nullable string helper
     */
    private function nullableStr($value)
    {
        return !empty($value) ? htmlspecialchars(trim($value), ENT_QUOTES) : null;
    }

    /**
     * Nullable date helper
     */
    private function nullableDate($value)
    {
        return !empty($value) ? trim($value) : null;
    }
}