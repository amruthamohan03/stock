<?php

class ProviderController extends Controller
{
    public function index()
    {
        $db = new Database();

        // Get service providers with created_by user name using JOIN
        $query = "SELECT sp.*, u.username AS created_by_name
                  FROM service_providers_t sp
                  LEFT JOIN users_t u ON sp.created_by = u.id
                  ORDER BY sp.id DESC";
        $result = $db->customQuery($query);

        $data = [
            'title'  => 'Service Providers',
            'result' => $result,
        ];

        $this->viewWithLayout('masters/service_provider', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db    = new Database();
        $table = 'service_providers_t';

        function s($v) { return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8'); }

        /* ------------------------------------
         * INSERTION
         * ------------------------------------ */
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] == 'POST') {

            $data = [
                'name'       => s($_POST['name']    ?? ''),
                'type'       => s($_POST['type']    ?? ''),
                'phone_no'   => s($_POST['phone_no'] ?? ''),
                'email'      => s($_POST['email']   ?? ''),
                'gst_no'     => s($_POST['gst_no']  ?? '') ?: null,
                'address'    => s($_POST['address'] ?? ''),
                'created_by' => 1,   // replace with session user id
                'updated_by' => 1,
            ];

            if (!$data['name']) {
                echo json_encode(['success' => false, 'message' => '⚠ Provider name is required.']);
                exit;
            }
            if (!in_array($data['type'], ['public', 'private'])) {
                echo json_encode(['success' => false, 'message' => '⚠ Please select a valid type.']);
                exit;
            }
            if (!$data['phone_no']) {
                echo json_encode(['success' => false, 'message' => '⚠ Phone number is required.']);
                exit;
            }
            if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => '⚠ Please enter a valid email.']);
                exit;
            }
            if (!$data['address']) {
                echo json_encode(['success' => false, 'message' => '⚠ Address is required.']);
                exit;
            }

            // Check duplicate email
            $existing = $db->selectData($table, 'id', ['email' => $data['email']]);
            if (!empty($existing)) {
                echo json_encode(['success' => false, 'message' => '⚠ This email is already registered.']);
                exit;
            }

            $insertId = $db->insertData($table, $data);

            echo json_encode([
                'success' => $insertId ? true : false,
                'message' => $insertId ? '✅ Service provider added successfully!' : '❌ Insert failed.',
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
                'name'       => s($_POST['name']    ?? ''),
                'type'       => s($_POST['type']    ?? ''),
                'phone_no'   => s($_POST['phone_no'] ?? ''),
                'email'      => s($_POST['email']   ?? ''),
                'gst_no'     => s($_POST['gst_no']  ?? '') ?: null,
                'address'    => s($_POST['address'] ?? ''),
                'updated_by' => 1,   // replace with session user id
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!$data['name']) {
                echo json_encode(['success' => false, 'message' => '⚠ Provider name is required.']);
                exit;
            }
            if (!in_array($data['type'], ['public', 'private'])) {
                echo json_encode(['success' => false, 'message' => '⚠ Please select a valid type.']);
                exit;
            }
            if (!$data['phone_no']) {
                echo json_encode(['success' => false, 'message' => '⚠ Phone number is required.']);
                exit;
            }
            if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => '⚠ Please enter a valid email.']);
                exit;
            }
            if (!$data['address']) {
                echo json_encode(['success' => false, 'message' => '⚠ Address is required.']);
                exit;
            }

            // Check duplicate email (ignore current record)
            $existing = $db->customQuery(
                "SELECT id FROM {$table} WHERE email = '" . addslashes($data['email']) . "' AND id != {$id}"
            );
            if (!empty($existing)) {
                echo json_encode(['success' => false, 'message' => '⚠ Email is already used by another provider.']);
                exit;
            }

            $update = $db->updateData($table, $data, ['id' => $id]);

            echo json_encode([
                'success' => $update ? true : false,
                'message' => $update ? '✅ Service provider updated successfully!' : '❌ Update failed.',
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
                'message' => $delete ? '✅ Service provider deleted successfully!' : '❌ Delete failed.',
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => '❌ Invalid request']);
        exit;
    }

    public function getById()
    {
        header('Content-Type: application/json');

        $db = new Database();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '❌ Invalid ID']);
            exit;
        }

        $row = $db->selectData('service_providers_t', '*', ['id' => $id]);

        echo json_encode(!empty($row)
            ? ['success' => true, 'data' => $row[0]]
            : ['success' => false, 'message' => '❌ Record not found']
        );
        exit;
    }
}
?>