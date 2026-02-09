<?php

class DashboardCardController extends Controller
{
    private $db;
    private $table = 'dashboard_card_master_t';

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index()
    {
        // Get cards with menu names using JOIN
        $result = $this->db->customQuery("
            SELECT c.*, m.menu_name 
            FROM {$this->table} c
            LEFT JOIN menu_master_t m ON c.menu_id = m.id
            ORDER BY c.card_order ASC
        ");
        
        // Get categories for dropdown
        $categories = [
            'general' => 'General',
            'import' => 'Import',
            'export' => 'Export',
            'finance' => 'Finance',
            'admin' => 'Admin'
        ];
        
        // Get color options
        $colors = [
            'primary' => 'Primary (Blue)',
            'success' => 'Success (Green)',
            'warning' => 'Warning (Yellow)',
            'danger' => 'Danger (Red)',
            'info' => 'Info (Cyan)',
            'purple' => 'Purple',
            'teal' => 'Teal',
            'pink' => 'Pink'
        ];

        // Get menus/pages for dropdown
        $menus = $this->db->customQuery("
            SELECT id, menu_name 
            FROM menu_master_t 
            WHERE display = 'Y' AND menu_id != 3 AND url != '#'
            ORDER BY menu_name ASC
        ");

        $data = [
            'title' => 'Dashboard Card Master',
            'result' => $result,
            'categories' => $categories,
            'colors' => $colors,
            'menus' => $menus
        ];
        
        $this->viewWithLayout('masters/dashboard_card', $data);
    }

    public function crudData($action = 'insertion')
    {
        header('Content-Type: application/json');

        try {
            switch ($action) {
                case 'insertion':
                    $this->insertCard();
                    break;
                case 'updation':
                    $this->updateCard();
                    break;
                case 'deletion':
                    $this->deleteCard();
                    break;
                case 'getById':
                    $this->getCardById();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function sanitize($value)
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    private function insertCard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $cardKey = $this->sanitize($_POST['card_key'] ?? '');
        $cardTitle = $this->sanitize($_POST['card_title'] ?? '');
        $menuId = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;

        // Validation
        if (empty($cardKey)) {
            echo json_encode(['success' => false, 'message' => 'Card Key is required']);
            return;
        }

        if (empty($cardTitle)) {
            echo json_encode(['success' => false, 'message' => 'Card Title is required']);
            return;
        }

        // Check for duplicate card_key
        $existing = $this->db->selectData($this->table, 'id', ['card_key' => $cardKey]);
        if (!empty($existing)) {
            echo json_encode(['success' => false, 'message' => 'Card Key already exists']);
            return;
        }

        $data = [
            'card_key' => $cardKey,
            'card_title' => $cardTitle,
            'card_subtitle' => $this->sanitize($_POST['card_subtitle'] ?? ''),
            'card_icon' => $this->sanitize($_POST['card_icon'] ?? 'bi-card-text'),
            'card_color' => $this->sanitize($_POST['card_color'] ?? 'primary'),
            'card_url' => $this->sanitize($_POST['card_url'] ?? ''),
            'card_order' => isset($_POST['card_order']) ? (int)$_POST['card_order'] : 0,
            'card_category' => $this->sanitize($_POST['card_category'] ?? 'general'),
            'menu_id' => $menuId,
            'data_source' => $this->sanitize($_POST['data_source'] ?? ''),
            'display' => isset($_POST['display']) && in_array($_POST['display'], ['Y', 'N']) ? $_POST['display'] : 'Y',
            'created_by' => $_SESSION['user_id'] ?? 1,
            'updated_by' => $_SESSION['user_id'] ?? 1
        ];

        $insertId = $this->db->insertData($this->table, $data);

        if ($insertId) {
            echo json_encode(['success' => true, 'message' => 'Dashboard Card added successfully', 'id' => $insertId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add Dashboard Card']);
        }
    }

    private function updateCard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Card ID']);
            return;
        }

        $cardKey = $this->sanitize($_POST['card_key'] ?? '');
        $cardTitle = $this->sanitize($_POST['card_title'] ?? '');
        $menuId = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : 0;

        // Validation
        if (empty($cardKey)) {
            echo json_encode(['success' => false, 'message' => 'Card Key is required']);
            return;
        }

        if (empty($cardTitle)) {
            echo json_encode(['success' => false, 'message' => 'Card Title is required']);
            return;
        }

        // Check for duplicate card_key (excluding current record)
        $sql = "SELECT id FROM {$this->table} WHERE card_key = :card_key AND id != :id";
        $existing = $this->db->customQuery($sql, [':card_key' => $cardKey, ':id' => $id]);
        if (!empty($existing)) {
            echo json_encode(['success' => false, 'message' => 'Card Key already exists']);
            return;
        }

        $data = [
            'card_key' => $cardKey,
            'card_title' => $cardTitle,
            'card_subtitle' => $this->sanitize($_POST['card_subtitle'] ?? ''),
            'card_icon' => $this->sanitize($_POST['card_icon'] ?? 'bi-card-text'),
            'card_color' => $this->sanitize($_POST['card_color'] ?? 'primary'),
            'card_url' => $this->sanitize($_POST['card_url'] ?? ''),
            'card_order' => isset($_POST['card_order']) ? (int)$_POST['card_order'] : 0,
            'card_category' => $this->sanitize($_POST['card_category'] ?? 'general'),
            'menu_id' => $menuId,
            'data_source' => $this->sanitize($_POST['data_source'] ?? ''),
            'display' => isset($_POST['display']) && in_array($_POST['display'], ['Y', 'N']) ? $_POST['display'] : 'Y',
            'updated_by' => $_SESSION['user_id'] ?? 1
        ];

        $update = $this->db->updateData($this->table, $data, ['id' => $id]);

        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Dashboard Card updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update Dashboard Card']);
        }
    }

    private function deleteCard()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Card ID']);
            return;
        }

        $delete = $this->db->deleteData($this->table, ['id' => $id]);

        if ($delete) {
            echo json_encode(['success' => true, 'message' => 'Dashboard Card deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete Dashboard Card']);
        }
    }

    private function getCardById()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid Card ID']);
            return;
        }

        $card = $this->db->customQuery("
            SELECT c.*, m.menu_name 
            FROM {$this->table} c
            LEFT JOIN menu_master_t m ON c.menu_id = m.id
            WHERE c.id = {$id}
        ");

        if (!empty($card)) {
            echo json_encode(['success' => true, 'data' => $card[0]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Dashboard Card not found']);
        }
    }

    public function getCardByIdPublic()
    {
        header('Content-Type: application/json');
        $this->getCardById();
        exit;
    }

    /**
     * Get cards by menu/page
     */
    public function getCardsByMenu()
    {
        header('Content-Type: application/json');
        
        $menuId = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;

        if ($menuId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid menu ID']);
            exit;
        }

        $cards = $this->db->customQuery("
            SELECT * FROM {$this->table} 
            WHERE menu_id = {$menuId} AND display = 'Y'
            ORDER BY card_order ASC
        ");

        echo json_encode(['success' => true, 'data' => $cards]);
        exit;
    }

    /**
     * Get all menus/pages
     */
    public function getMenus()
    {
        header('Content-Type: application/json');
        
        $menus = $this->db->customQuery("
            SELECT id, menu_name 
            FROM menu_master_t 
            WHERE display = 'Y' 
            ORDER BY menu_name ASC
        ");

        echo json_encode(['success' => true, 'data' => $menus]);
        exit;
    }
}