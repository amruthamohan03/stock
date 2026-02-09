<?php

class ExpenseController extends Controller
{
    public function index()
    {
        $db = new Database();
        $result = $db->selectData('expense_type_master_t', '*', []);
        $data = [
            'title' => 'Expense Type Master',
            'result' => $result
        ];
        $this->viewWithLayout('masters/expense', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db = new Database();
        $table = 'expense_type_master_t';

        function sanitize($value)
        {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }

        // All possible checkbox fields
        $allTypes = ['import', 'export', 'local', 'advance', 'other'];

        // INSERTION
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? 1;

            $data = [
                'expense_type_name' => sanitize($_POST['expense_type_name'] ?? ''),
                'display' => in_array($_POST['display'] ?? 'Y', ['Y', 'N']) ? $_POST['display'] : 'Y',
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            // Set 1 / null for each checkbox column (consistent with update)
            foreach ($allTypes as $t) {
                $data[$t] = (isset($_POST['type']) && in_array($t, $_POST['type'])) ? 1 : null;
            }

            if (empty($data['expense_type_name'])) {
                echo json_encode(['success' => false, 'message' => '❌ Expense Name is required.']);
                exit;
            }

            try {
                $insertId = $db->insertData($table, $data);
                echo json_encode($insertId
                    ? ['success' => true, 'message' => '✅ Expense type added successfully!', 'id' => $insertId]
                    : ['success' => false, 'message' => '❌ Insert failed.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
            }
            exit;
        }

        // UPDATION
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => '❌ Invalid Expense Type ID.']);
                exit;
            }

            $userId = $_SESSION['user_id'] ?? 1;

            // Build update array
            $data = [
                'expense_type_name' => sanitize($_POST['expense_type_name'] ?? ''),
                'display' => in_array($_POST['display'] ?? 'Y', ['Y', 'N']) ? $_POST['display'] : 'Y',
                'updated_by' => $userId,
            ];

            // Set 1 / null for each checkbox column
            foreach ($allTypes as $t) {
                $data[$t] = (isset($_POST['type']) && in_array($t, $_POST['type'])) ? 1 : null;
            }

            if (empty($data['expense_type_name'])) {
                echo json_encode(['success' => false, 'message' => '❌ Expense Name is required.']);
                exit;
            }

            try {
                $update = $db->updateData($table, $data, ['id' => $id]);
                echo json_encode([
                    'success' => $update ? true : false,
                    'message' => $update ? '✅ Expense type updated successfully!' : '❌ Update failed.'
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
            }
            exit;
        }

        // DELETION
        elseif ($action === 'deletion') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => '❌ Invalid ID.']);
                exit;
            }

            try {
                $delete = $db->deleteData($table, ['id' => $id]);
                echo json_encode($delete
                    ? ['success' => true, 'message' => '✅ Expense type deleted successfully!']
                    : ['success' => false, 'message' => '❌ Delete failed.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => '⚠️ Invalid request.']);
        exit;
    }

    public function getExpenseById()
    {
        header('Content-Type: application/json');
        $db = new Database();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Invalid ID.']);
            exit;
        }

        try {
            $expense_type = $db->selectData('expense_type_master_t', '*', ['id' => $id]);
            if (!empty($expense_type)) {
                echo json_encode(['success' => true, 'data' => $expense_type[0]]);
            } else {
                echo json_encode(['success' => false, 'message' => '❌ Expense type not found.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
        }
        exit;
    }
}