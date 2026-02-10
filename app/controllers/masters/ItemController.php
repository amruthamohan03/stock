<?php

class ItemController extends Controller
{
    public function index()
    {
        $db = new Database();
        
        // Get items with category name using JOIN
        $query = "SELECT im.*, qc.category_name 
                  FROM item_master_t im 
                  LEFT JOIN quotation_categories_t qc ON im.category_id = qc.id 
                  ORDER BY im.id DESC";
        $result = $db->customQuery($query);
        
        // Get all categories for dropdown
        $categories = $db->selectData('quotation_categories_t', 'id, category_name', ['display' => 'Y'], 'category_name ASC');

        // Define transaction type enum for dropdown
        $transactionEnum = [
            "I"   => "Import",
            "E"   => "Export",
            "U"   => "Under Value",
            "IE"  => "Import, Export",
            "IU"  => "Import, Under Value",
            "EU"  => "Export, Under Value",
            "IEU" => "Import, Export, Under Value"
        ];

        $data = [
            'title' => 'Item Master',
            'result' => $result,
            'categories' => $categories,
            'transactionEnum' => $transactionEnum
        ];

        $this->viewWithLayout('masters/item', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db = new Database();
        $table = 'item_master_t';

        function s($v) { return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'); }

        /* ------------------------------------
         * INSERTION
         * ------------------------------------ */
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] == 'POST') {

            $data = [
                'item_name'           => s($_POST['item_name']),
                'category_id'         => (int)($_POST['category_id'] ?? 0),
                'tax_not_tax'         => s($_POST['tax_not_tax'] ?? 'N'),
                'display'             => ($_POST['display'] ?? 'Y'),
                'created_by'          => 1,
                'updated_by'          => 1,
            ];

            if (!$data['item_name']) {
                echo json_encode(['success' => false, 'message' => '⚠ Please fill required fields.']);
                exit;
            }

            if ($data['category_id'] <= 0) {
                echo json_encode(['success' => false, 'message' => '⚠ Please select a category.']);
                exit;
            }

            $insertId = $db->insertData($table, $data);

            echo json_encode([
                'success' => $insertId ? true : false,
                'message' => $insertId ? '✅ Item added successfully!' : '❌ Insert failed.'
            ]);
            exit;
        }

        /* ------------------------------------
         * UPDATION
         * ------------------------------------ */
        if ($action === 'updation' && $_SERVER['REQUEST_METHOD'] == 'POST') {

            header('Content-Type: application/json');

            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => '❌ Invalid ID']);
                exit;
            }

            $data = [
                'item_name'           => s($_POST['item_name']),
                'category_id'         => (int)($_POST['category_id'] ?? 0),
                'tax_not_tax'         => s($_POST['tax_not_tax'] ?? 'N'),
                'display'             => ($_POST['display'] ?? 'Y'),
                'updated_by'          => 1,
                'updated_at'          => date('Y-m-d H:i:s')
            ];

            if (!$data['item_name']) {
                echo json_encode(['success' => false, 'message' => '⚠ Please fill required fields.']);
                exit;
            }

            if ($data['category_id'] <= 0) {
                echo json_encode(['success' => false, 'message' => '⚠ Please select a category.']);
                exit;
            }

            if (!$data['item_type']) {
                echo json_encode(['success' => false, 'message' => '⚠ Please select item type.']);
                exit;
            }

            $update = $db->updateData($table, $data, ['id' => $id]);

            echo json_encode([
                'success' => $update ? true : false,
                'message' => $update ? '✅ Item updated successfully!' : '❌ Update failed.'
            ]);
            exit;
        }

        /* ------------------------------------
         * DELETION
         * ------------------------------------ */
        if ($action === 'deletion') {
            $id = (int)($_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => '❌ Invalid ID']);
                exit;
            }

            $delete = $db->deleteData($table, ['id' => $id]);

            echo json_encode([
                'success' => $delete ? true : false,
                'message' => $delete ? '✅ Item deleted successfully!' : '❌ Delete failed.'
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => '❌ Invalid request']);
        exit;
    }

    public function getItemById()
    {
        header('Content-Type: application/json');

        $db = new Database();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Invalid ID']);
            exit;
        }

        $row = $db->selectData('item_master_t', '*', ['id' => $id]);

        echo json_encode(!empty($row)
            ? ['success' => true, 'data' => $row[0]]
            : ['success' => false, 'message' => '❌ Item not found']
        );
        exit;
    }
}
?>