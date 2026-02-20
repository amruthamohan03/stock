<?php

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get dashboard cards based on user's role
     * @param int $roleId - The role ID of the logged-in user
     * @return array - Array of card objects
     */
    public function getCardsByRole($roleId,$menuId)
    {
        $this->db->query("
            SELECT 
                dcm.id,
                dcm.card_key,
                dcm.card_content_id,
                dcm.card_title,
                dcm.card_subtitle,
                dcm.card_icon,
                dcm.card_color,
                dcm.card_url,
                dcm.card_category,
                dcm.data_source,
                COALESCE(rdcm.card_order, dcm.card_order) AS card_order
            FROM dashboard_card_master_t dcm
            INNER JOIN role_dashboard_card_mapping_t rdcm 
                ON dcm.id = rdcm.card_id
            WHERE rdcm.role_id = :role_id AND rdcm.menu_id = :menu_id
                AND rdcm.is_visible = 1
                AND dcm.display = 'Y'
            ORDER BY dcm.card_order ASC
        ");
        
        $this->db->bind(':role_id', (int)$roleId);
        $this->db->bind(':menu_id', (int)$menuId);
        return $this->db->resultSet();
    }

    /**
     * Get all active cards (fallback when no role specified)
     * @return array - Array of all active card objects
     */
    public function getAllActiveCards()
    {
        $this->db->query("
            SELECT 
                id,
                card_key,
                card_title,
                card_subtitle,
                card_icon,
                card_color,
                card_url,
                card_category,
                data_source,
                card_order
            FROM dashboard_card_master_t
            WHERE display = 'Y'
            ORDER BY card_order ASC
        ");
        
        return $this->db->resultSet();
    }

    /**
     * Get count data for all cards
     * @param array $cards - Array of card objects
     * @return array - Associative array with card_key => ['total', 'active', 'label']
     */
    public function getCardData($cards)
    {
        // Map card_key to table names and labels
        $cardDataMap = [
            'total_users'       => ['table' => 'users_t',           'label' => 'Active Users'],            
            'system_settings'   => ['table' => null,                'label' => 'Configuration'],
        ];

        $cardData = [];
        
        foreach ($cards as $card) {
            $key = $card->card_key;
            
            if (!isset($cardDataMap[$key]) || $cardDataMap[$key]['table'] === null) {
                $cardData[$key] = [
                    'total'  => 0,
                    'active' => 0,
                    'label'  => $cardDataMap[$key]['label'] ?? $card->card_subtitle
                ];
                continue;
            }

            $table = $cardDataMap[$key]['table'];
            $label = $cardDataMap[$key]['label'];

            $cardData[$key] = [
                'total'  => $this->count($table),
                'active' => $this->countTableActive($table),
                'label'  => $label
            ];
        }
        
        return $cardData;
    }

    /**
     * Legacy method - kept for backward compatibility
     */
    public function getCounts()
    {
        return [
            'total_users'     => $this->count('users_t'),
            'active_users'    => $this->countTableActive('users_t'),
        ];
    }

    private function count($table)
    {
        $this->db->query("SELECT COUNT(*) AS total FROM {$table}");
        $result = $this->db->single();
        return (int) $result->total;
    }

    private function countTableActive($table)
    {
        $this->db->query("SELECT COUNT(*) AS total FROM {$table} WHERE display = 'Y'");
        $result = $this->db->single();
        return (int) $result->total;
    }
}