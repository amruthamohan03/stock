<?php

class IndentController extends Controller
{

    public function index()
    {
        $db = new Database();

        // Get all items for dropdown
        $items = $db->selectData('item_master_t', 'id, item_name', ['display' => 'Y']);

        // Get all makes for dropdown
        $makes = $db->selectData('make_t', 'id, make_name', ['display' => 'Y']);

        // Get all indents with institution info (JOIN)
        $query = "SELECT im.*, c.college_name,
                  u1.username as created_by_name,
                  u2.username as verified_by_name,
                  u3.username as passed_by_name,
                  u4.username as issued_by_name,
                  u5.username as received_by_name
                  FROM indent_master_t im
                  LEFT JOIN college_t c ON im.institution_id = c.id
                  LEFT JOIN users_t u1 ON im.created_by = u1.id
                  LEFT JOIN users_t u2 ON im.verified_by = u2.id
                  LEFT JOIN users_t u3 ON im.passed_by = u3.id
                  LEFT JOIN users_t u4 ON im.issued_by = u4.id
                  LEFT JOIN users_t u5 ON im.received_by = u5.id
                  ORDER BY im.id DESC";
        $result = $db->customQuery($query);

        $data = [
            'title' => 'Indent Book Management',
            'items' => $items,
            'makes' => $makes,
            'result' => $result
        ];

        $this->viewWithLayout('stock/indent', $data);
    }

    public function create()
    {
        $db = new Database();

        // Get dropdowns data
        $institutions = $db->selectData('college_t', 'id, college_name', ['display' => 'Y']);
        $items = $db->selectData('item_master_t', 'id, item_name', ['display' => 'Y']);
        $makes = $db->selectData('make_t', 'id, make_name', ['display' => 'Y']);

        $data = [
            'title' => 'Create New Indent',
            'institutions' => $institutions,
            'items' => $items,
            'makes' => $makes
        ];

        $this->viewWithLayout('indent/indentView', $data);
    }

    public function crudData($action = 'insertion')
    {
        $db = new Database();

        // 🔹 INSERTION (Create new indent with items)
        if ($action === 'insertion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            // Validate main indent data
            $book_no = isset($_POST['book_no']) ? (int) $_POST['book_no'] : 0;
            $indent_no = isset($_POST['indent_no']) ? htmlspecialchars(trim($_POST['indent_no']), ENT_QUOTES) : '';
            $item_type = isset($_POST['item_type']) ? htmlspecialchars(trim($_POST['item_type']), ENT_QUOTES) : '';
            $indent_date = isset($_POST['indent_date']) ? $_POST['indent_date'] : '';
            $purpose = isset($_POST['purpose']) ? htmlspecialchars(trim($_POST['purpose']), ENT_QUOTES) : '';

            if ($book_no <= 0) {
                echo json_encode(['success' => false, 'message' => 'Book number is required']);
                exit;
            }
            if (empty($indent_no)) {
                echo json_encode(['success' => false, 'message' => 'Indent number is required']);
                exit;
            }
            if (empty($item_type)) {
                echo json_encode(['success' => false, 'message' => 'Item type is required']);
                exit;
            }
            if (empty($indent_date)) {
                echo json_encode(['success' => false, 'message' => 'Indent date is required']);
                exit;
            }

            // Check if book_no + indent_no combination already exists
            $existing = $db->selectData('indent_master_t', 'id', ['book_no' => $book_no, 'indent_no' => $indent_no]);
            if (!empty($existing)) {
                echo json_encode(['success' => false, 'message' => 'This Book No. and Indent No. combination already exists']);
                exit;
            }

            // Insert indent master
            $indentData = [
                'book_no' => $book_no,
                'indent_no' => $indent_no,
                'item_type' => $item_type,
                'indent_date' => $indent_date,
                'purpose' => $purpose,
                'created_by' => 1, // Replace with session user
                'status' => 'CREATED'
            ];

            $indent_id = $db->insertData('indent_master_t', $indentData);

            if ($indent_id) {
                // Insert indent items
                $items = isset($_POST['items']) ? $_POST['items'] : [];

                if (!empty($items)) {
                    foreach ($items as $item) {
                        $itemData = [
                            'indent_id' => $indent_id,
                            'sl_no' => (int) $item['sl_no'],
                            'item_id' => (int) $item['item_id'],
                            'make_id' => !empty($item['make_id']) ? (int) $item['make_id'] : null,
                            'model_id' => !empty($item['model_id']) ? (int) $item['model_id'] : null,
                            'item_description' => htmlspecialchars(trim($item['item_description']), ENT_QUOTES),
                            'item_purpose' => htmlspecialchars(trim($item['item_purpose']), ENT_QUOTES),
                            'qty_intended' => (int) $item['qty_intended'],
                            'qty_passed' => 0,
                            'qty_issued' => 0,
                            'remarks' => htmlspecialchars(trim($item['remarks']), ENT_QUOTES)
                        ];

                        $db->insertData('indent_item_t', $itemData);
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Indent created successfully', 'id' => $indent_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create indent']);
            }
            exit;
        }

        // 🔹 UPDATION (Edit indent)
        elseif ($action === 'updation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
                exit;
            }

            $indentData = [
                'book_no' => (int) $_POST['book_no'],
                'indent_no' => htmlspecialchars(trim($_POST['indent_no']), ENT_QUOTES),
                'item_type' => htmlspecialchars(trim($_POST['item_type']), ENT_QUOTES),
                'indent_date' => $_POST['indent_date'],
                'purpose' => htmlspecialchars(trim($_POST['purpose']), ENT_QUOTES)
            ];

            $update = $db->updateData('indent_master_t', $indentData, ['id' => $id]);

            if ($update) {
                echo json_encode(['success' => true, 'message' => 'Indent updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit;
        }

        // 🔹 DELETION
        elseif ($action === 'deletion') {
            header('Content-Type: application/json');
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
                exit;
            }

            $delete = $db->deleteData('indent_master_t', ['id' => $id]);
            if ($delete) {
                echo json_encode(['success' => true, 'message' => 'Indent deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
            exit;
        }
    }

    // Get indent by ID with items
    public function getIndentById()
    {
        header('Content-Type: application/json');
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }

        $db = new Database();

        // Get indent master
        $indent = $db->selectData('indent_master_t', '*', ['id' => $id]);

        if (empty($indent)) {
            echo json_encode(['success' => false, 'message' => 'Indent not found']);
            exit;
        }

        // Get indent items
        $items = $db->selectData('indent_item_t', '*', ['indent_id' => $id]);

        $data = [
            'indent' => $indent[0],
            'items' => $items
        ];

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Get models by make ID
    public function getModelsByMake()
    {
        header('Content-Type: application/json');
        $make_id = isset($_GET['make_id']) ? (int) $_GET['make_id'] : 0;

        if ($make_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Make ID']);
            exit;
        }

        $db = new Database();
        $models = $db->selectData('model_t', 'id, model_name', ['make_id' => $make_id, 'display' => 'Y']);

        echo json_encode(['success' => true, 'data' => $models]);
        exit;
    }

    // Verify indent (Workshop Instructor/Superintendent)
    public function verifyIndent()
    {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }

        $db = new Database();
        $update = $db->updateData('indent_master_t', [
            'verified_by' => 1, // Replace with session user
            'status' => 'VERIFIED'
        ], ['id' => $id]);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Indent verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Verification failed']);
        }
        exit;
    }

    // Pass indent (Superintendent)
    public function passIndent()
    {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }

        // Get items and update qty_passed
        $items = isset($_POST['items']) ? $_POST['items'] : [];

        $db = new Database();

        foreach ($items as $item) {
            $db->updateData('indent_item_t', [
                'qty_passed' => (int) $item['qty_passed']
            ], ['id' => (int) $item['id']]);
        }

        $update = $db->updateData('indent_master_t', [
            'passed_by' => 1, // Replace with session user
            'status' => 'PASSED'
        ], ['id' => $id]);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Indent passed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pass failed']);
        }
        exit;
    }

    // Issue indent (Store-keeper)
    public function issueIndent()
    {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }

        // Get items and update qty_issued
        $items = isset($_POST['items']) ? $_POST['items'] : [];

        $db = new Database();

        foreach ($items as $item) {
            $db->updateData('indent_item_t', [
                'qty_issued' => (int) $item['qty_issued']
            ], ['id' => (int) $item['id']]);
        }

        $update = $db->updateData('indent_master_t', [
            'issued_by' => 1, // Replace with session user
            'status' => 'ISSUED'
        ], ['id' => $id]);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Indent issued successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Issue failed']);
        }
        exit;
    }

    // Receive indent
    public function receiveIndent()
    {
        header('Content-Type: application/json');
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Indent ID']);
            exit;
        }

        $db = new Database();
        $update = $db->updateData('indent_master_t', [
            'received_by' => 1, // Replace with session user
            'status' => 'RECEIVED'
        ], ['id' => $id]);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Indent received successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Receive failed']);
        }
        exit;
    }

    // View indent details
    public function viewIndent($id = null)
    {
        if (empty($id)) {
            $this->redirect('indent');
            return;
        }

        $db = new Database();

        // Get indent with institution info
        $query = "SELECT im.*, c.college_name,
                  u1.username as created_by_name,
                  u2.username as verified_by_name,
                  u3.username as passed_by_name,
                  u4.username as issued_by_name,
                  u5.username as received_by_name
                  FROM indent_master_t im
                  LEFT JOIN college_t c ON im.institution_id = c.id
                  LEFT JOIN users_t u1 ON im.created_by = u1.id
                  LEFT JOIN users_t u2 ON im.verified_by = u2.id
                  LEFT JOIN users_t u3 ON im.passed_by = u3.id
                  LEFT JOIN users_t u4 ON im.issued_by = u4.id
                  LEFT JOIN users_t u5 ON im.received_by = u5.id
                  WHERE im.id = " . (int) $id;
        $indent = $db->customQuery($query);

        if (empty($indent)) {
            $this->redirect('indent');
            return;
        }

        // Get items with item, make, model info
        $itemsQuery = "SELECT ii.*, 
                       i.item_name,
                       mk.make_name,
                       md.model_name
                       FROM indent_item_t ii
                       LEFT JOIN item_master_t i ON ii.item_id = i.id
                       LEFT JOIN make_t mk ON ii.make_id = mk.id
                       LEFT JOIN model_t md ON ii.model_id = md.id
                       WHERE ii.indent_id = " . (int) $id . "
                       ORDER BY ii.sl_no";
        $items = $db->customQuery($itemsQuery);

        $data = [
            'title' => 'View Indent - ' . $indent[0]['indent_no'],
            'indent' => $indent[0],
            'items' => $items
        ];

        $this->viewWithLayout('stock/indentview', $data);
    }
    public function report()
    {
        $db = new Database();

        $data = [
            'title' => 'Indent Book Report'
        ];

        $this->viewWithLayout('stock/indent_report', $data);
    }
    public function fetchReport()
    {
        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";

        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";

        if ($type && $type != 'ALL')
            $where .= " AND im.item_type='$type'";

        $sql = "
        SELECT 
            im.id,
            im.indent_no,
            im.indent_date,
            im.item_type,
            ii.item_description,
            ii.qty_intended,
            ii.qty_passed,
            ii.qty_issued
        
        FROM indent_master_t im
        LEFT JOIN indent_item_t ii ON ii.indent_id = im.id
        
        $where
        ORDER BY im.indent_date DESC
    ";

        $rows = $db->customQuery($sql);

        echo json_encode([
            "success" => true,
            "data" => $rows
        ]);
    }
    public function exportPdf()
    {
        require_once APP_ROOT . '/vendor/autoload.php';

        $db = new Database();

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $type = $_GET['type'] ?? null;

        $where = "WHERE im.display='Y'";

        if ($from && $to)
            $where .= " AND im.indent_date BETWEEN '$from' AND '$to'";

        if ($type && $type != 'ALL')
            $where .= " AND im.item_type='$type'";

        $rows = $db->customQuery("
        SELECT im.indent_no, im.indent_date, im.item_type,
               ii.item_description, ii.qty_intended, ii.qty_passed, ii.qty_issued
        FROM indent_master_t im
        LEFT JOIN indent_item_t ii ON ii.indent_id=im.id
        $where
        ORDER BY im.indent_date DESC
    ");

        ob_start();
        ?>
        <style>
            body {
                font-family: DejaVu Sans;
                font-size: 12px
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px
            }

            th,
            td {
                border: 1px solid #333;
                padding: 6px
            }

            th {
                background: #2c3e50;
                color: #fff
            }

            h2 {
                text-align: center;
                margin-bottom: 0
            }

            small {
                color: #555
            }
        </style>

        <h2>Indent Book Report</h2>
        <small>Generated: <?= date('d-m-Y H:i') ?></small>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Indent No</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Intended</th>
                    <th>Passed</th>
                    <th>Issued</th>
                </tr>
            </thead>
            <tbody>

                <?php $i = 1;
                foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $r['indent_no'] ?></td>
                        <td><?= date('d-m-Y', strtotime($r['indent_date'])) ?></td>
                        <td><?= $r['item_type'] == 'C' ? 'Consumable' : 'Non-Consumable' ?></td>
                        <td><?= $r['item_description'] ?></td>
                        <td><?= $r['qty_intended'] ?></td>
                        <td><?= $r['qty_passed'] ?></td>
                        <td><?= $r['qty_issued'] ?></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>

        <?php
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("indent_report.pdf", ["Attachment" => false]);
    }

}