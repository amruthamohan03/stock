<?php
/**
 * RoleDashboardCardController
 * Handles mapping of dashboard cards to user roles
 * Updated to support page/menu filtering
 */
class RoleDashboardCardController extends Controller
{
    /**
     * Display the mapping page
     */
    public function index()
    {
        $db = new Database();

        // Fetch active roles
        $roles = $db->selectData('role_master_t', '*', ['display' => 'Y']);
        
        // Fetch active dashboard cards with menu names
        $cards = $db->customQuery("
            SELECT c.id, c.card_key, c.card_title, c.card_subtitle, c.card_icon, 
                   c.card_color, c.card_category, c.card_order, c.menu_id,
                   COALESCE(m.menu_name, 'Unassigned') AS menu_name
            FROM dashboard_card_master_t c
            LEFT JOIN menu_master_t m ON c.menu_id = m.id
            WHERE c.display='Y' 
            ORDER BY m.menu_name, c.card_category, c.card_order
        ");

        // Get unique categories for filtering
        $categories = $db->customQuery("
            SELECT DISTINCT card_category 
            FROM dashboard_card_master_t 
            WHERE display='Y' 
            ORDER BY card_category
        ");

        // Fetch active menus/pages for filtering
        $menus = $db->customQuery("
            SELECT m.id, m.menu_name, COUNT(c.id) AS card_count
            FROM menu_master_t m
            LEFT JOIN dashboard_card_master_t c ON c.menu_id = m.id AND c.display = 'Y'
            WHERE m.display = 'Y'
            GROUP BY m.id, m.menu_name
            HAVING card_count > 0
            ORDER BY m.menu_name
        ");

        $data = [
            'title' => 'Dashboard Card Mapping',
            'roles' => $roles,
            'cards' => $cards,
            'categories' => $categories,
            'menus' => $menus
        ];

        $this->viewWithLayout('mapping/role_dashboard_card_mapping', $data);
    }

    /**
     * Get card mapping for a specific role (AJAX)
     * Optionally filter by menu_id
     */
    public function getMapping()
    {
        header('Content-Type: application/json');
        $db = new Database();
        
        $role_id = (int)($_GET['role_id'] ?? 0);
        $menu_id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : null;

        if ($role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
            exit;
        }

        $menuFilter = "";
        if ($menu_id !== null && $menu_id > 0) {
            $menuFilter = "AND c.menu_id = {$menu_id}";
        }

        $sql = "
            SELECT 
                c.id AS card_id,
                c.card_key,
                c.card_title,
                c.card_subtitle,
                c.card_icon,
                c.card_color,
                c.card_category,
                c.card_order AS default_order,
                c.menu_id,
                COALESCE(menu.menu_name, 'Unassigned') AS menu_name,
                CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END AS is_mapped,
                COALESCE(m.card_order, c.card_order) AS display_order
            FROM dashboard_card_master_t c
            LEFT JOIN menu_master_t menu ON c.menu_id = menu.id
            LEFT JOIN role_dashboard_card_mapping_t m 
                ON c.id = m.card_id AND m.role_id = {$role_id}
            WHERE c.display = 'Y'
            {$menuFilter}
            ORDER BY menu.menu_name, c.card_category, c.card_order
        ";

        $result = $db->customQuery($sql);
        
        // Group by menu/page for frontend
        $groupedByMenu = [];
        foreach ($result as $card) {
            $menuName = $card['menu_name'];
            if (!isset($groupedByMenu[$menuName])) {
                $groupedByMenu[$menuName] = [
                    'menu_id' => $card['menu_id'],
                    'menu_name' => $menuName,
                    'cards' => []
                ];
            }
            $groupedByMenu[$menuName]['cards'][] = $card;
        }

        // Group by category for frontend
        $groupedByCategory = [];
        foreach ($result as $card) {
            $category = $card['card_category'];
            if (!isset($groupedByCategory[$category])) {
                $groupedByCategory[$category] = [];
            }
            $groupedByCategory[$category][] = $card;
        }

        echo json_encode([
            'success' => true, 
            'data' => $result,
            'grouped_by_menu' => array_values($groupedByMenu),
            'grouped_by_category' => $groupedByCategory
        ]);
        exit;
    }

    /**
     * Save card mapping for a role (AJAX)
     */
    public function saveMapping()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $role_id = (int)($_POST['role_id'] ?? 0);
        $card_ids = $_POST['card_ids'] ?? [];
        $menu_id = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : null;

        if ($role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
            exit;
        }

        try {
            // Start transaction
            $db->beginTransaction();

            if ($menu_id !== null && $menu_id > 0) {
                // Delete existing mappings for this role and specific menu only
                $db->customQuery("
                    DELETE m FROM role_dashboard_card_mapping_t m
                    INNER JOIN dashboard_card_master_t c ON m.card_id = c.id
                    WHERE m.role_id = {$role_id} AND c.menu_id = {$menu_id}
                ");
            } else {
                // Delete all existing mappings for this role
                $db->deleteData('role_dashboard_card_mapping_t', ['role_id' => $role_id]);
            }

            // Insert new mappings
            $order = 1;
            foreach ($card_ids as $card_id) {
                $card_id = (int)$card_id;
                if ($card_id > 0) {
                    $data = [
                        'role_id' => $role_id,
                        'card_id' => $card_id,
                        'menu_id' => $menu_id,
                        'is_visible' => 1,
                        'card_order' => $order++,
                        'created_by' => $_SESSION['user_id'] ?? 1
                    ];
                    $db->insertData('role_dashboard_card_mapping_t', $data);
                }
            }

            // Commit transaction
            $db->commit();

            echo json_encode([
                'success' => true, 
                'message' => '✅ Dashboard card mapping saved successfully!',
                'mapped_count' => count($card_ids)
            ]);

        } catch (Exception $e) {
            $db->rollback();
            echo json_encode([
                'success' => false, 
                'message' => '❌ Error saving mapping: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get all cards (for card master management)
     */
    public function getAllCards()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $cards = $db->customQuery("
            SELECT c.*, COALESCE(m.menu_name, 'Unassigned') AS menu_name
            FROM dashboard_card_master_t c
            LEFT JOIN menu_master_t m ON c.menu_id = m.id
            ORDER BY m.menu_name, c.card_category, c.card_order
        ");

        echo json_encode(['success' => true, 'data' => $cards]);
        exit;
    }

    /**
     * Get dashboard cards for current user's role
     * Use this method to fetch cards for the dashboard
     */
    public function getUserDashboardCards()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $role_id = $_SESSION['role_id'] ?? 0;
        $menu_id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : null;

        if ($role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'No role assigned']);
            exit;
        }

        $menuFilter = "";
        if ($menu_id !== null && $menu_id > 0) {
            $menuFilter = "AND c.menu_id = {$menu_id}";
        }

        $sql = "
            SELECT 
                c.id,
                c.card_key,
                c.card_title,
                c.card_subtitle,
                c.card_icon,
                c.card_color,
                c.card_url,
                c.data_source,
                c.menu_id,
                m.card_order
            FROM dashboard_card_master_t c
            INNER JOIN role_dashboard_card_mapping_t m 
                ON c.id = m.card_id
            WHERE m.role_id = {$role_id}
                AND c.display = 'Y'
                AND m.is_visible = 1
                {$menuFilter}
            ORDER BY m.card_order
        ";

        $cards = $db->customQuery($sql);
        echo json_encode(['success' => true, 'data' => $cards]);
        exit;
    }

    /**
     * Get cards by menu/page for current user's role
     */
    public function getUserCardsByMenu()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $role_id = $_SESSION['role_id'] ?? 0;
        $menu_id = (int)($_GET['menu_id'] ?? 0);

        if ($role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'No role assigned']);
            exit;
        }

        if ($menu_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid menu/page']);
            exit;
        }

        $sql = "
            SELECT 
                c.id,
                c.card_key,
                c.card_title,
                c.card_subtitle,
                c.card_icon,
                c.card_color,
                c.card_url,
                c.data_source,
                m.card_order
            FROM dashboard_card_master_t c
            INNER JOIN role_dashboard_card_mapping_t m 
                ON c.id = m.card_id
            WHERE m.role_id = {$role_id}
                AND c.menu_id = {$menu_id}
                AND c.display = 'Y'
                AND m.is_visible = 1
            ORDER BY m.card_order
        ";

        $cards = $db->customQuery($sql);
        echo json_encode(['success' => true, 'data' => $cards]);
        exit;
    }

    /**
     * Copy mapping from one role to another
     */
    public function copyMapping()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $source_role_id = (int)($_POST['source_role_id'] ?? 0);
        $target_role_id = (int)($_POST['target_role_id'] ?? 0);
        $menu_id = isset($_POST['menu_id']) ? (int)$_POST['menu_id'] : null;

        if ($source_role_id <= 0 || $target_role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid role selection']);
            exit;
        }

        if ($source_role_id === $target_role_id) {
            echo json_encode(['success' => false, 'message' => 'Source and target roles cannot be the same']);
            exit;
        }

        try {
            $db->beginTransaction();

            if ($menu_id !== null && $menu_id > 0) {
                // Delete existing mappings for target role and specific menu only
                $db->customQuery("
                    DELETE m FROM role_dashboard_card_mapping_t m
                    INNER JOIN dashboard_card_master_t c ON m.card_id = c.id
                    WHERE m.role_id = {$target_role_id} AND c.menu_id = {$menu_id}
                ");

                // Copy mappings from source to target for specific menu
                $sql = "
                    INSERT INTO role_dashboard_card_mapping_t (role_id, card_id, is_visible, card_order, created_by)
                    SELECT {$target_role_id}, m.card_id, m.is_visible, m.card_order, " . ($_SESSION['user_id'] ?? 1) . "
                    FROM role_dashboard_card_mapping_t m
                    INNER JOIN dashboard_card_master_t c ON m.card_id = c.id
                    WHERE m.role_id = {$source_role_id} AND c.menu_id = {$menu_id}
                ";
            } else {
                // Delete all existing mappings for target role
                $db->deleteData('role_dashboard_card_mapping_t', ['role_id' => $target_role_id]);

                // Copy all mappings from source to target
                $sql = "
                    INSERT INTO role_dashboard_card_mapping_t (role_id, card_id, is_visible, card_order, created_by)
                    SELECT {$target_role_id}, card_id, is_visible, card_order, " . ($_SESSION['user_id'] ?? 1) . "
                    FROM role_dashboard_card_mapping_t
                    WHERE role_id = {$source_role_id}
                ";
            }
            
            $db->customQuery($sql);

            $db->commit();

            echo json_encode([
                'success' => true, 
                'message' => '✅ Mapping copied successfully!'
            ]);

        } catch (Exception $e) {
            $db->rollback();
            echo json_encode([
                'success' => false, 
                'message' => '❌ Error copying mapping: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Get mapping summary for a role (cards per page)
     */
    public function getMappingSummary()
    {
        header('Content-Type: application/json');
        $db = new Database();

        $role_id = (int)($_GET['role_id'] ?? 0);

        if ($role_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
            exit;
        }

        $sql = "
            SELECT 
                COALESCE(menu.menu_name, 'Unassigned') AS menu_name,
                menu.id AS menu_id,
                COUNT(m.card_id) AS mapped_count,
                (SELECT COUNT(*) FROM dashboard_card_master_t WHERE menu_id = menu.id AND display = 'Y') AS total_cards
            FROM menu_master_t menu
            LEFT JOIN dashboard_card_master_t c ON c.menu_id = menu.id AND c.display = 'Y'
            LEFT JOIN role_dashboard_card_mapping_t m ON c.id = m.card_id AND m.role_id = {$role_id}
            WHERE menu.display = 'Y'
            GROUP BY menu.id, menu.menu_name
            HAVING total_cards > 0
            ORDER BY menu.menu_name
        ";

        $summary = $db->customQuery($sql);
        echo json_encode(['success' => true, 'data' => $summary]);
        exit;
    }
}
?>