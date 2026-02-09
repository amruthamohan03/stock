<?php

class LicenseDashboardController extends Controller
{
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  private function loadPhpSpreadsheet()
  {
    $possiblePaths = [
      __DIR__ . '/../../../vendor/autoload.php',
      __DIR__ . '/../../vendor/autoload.php',
      $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
      dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
    ];

    foreach ($possiblePaths as $path) {
      if (file_exists($path)) {
        require_once $path;
        return true;
      }
    }
    return false;
  }

  public function index()
  {
    $data = [
      'title' => 'License Dashboard',
      'kpi_data' => $this->getKPIData(),
      'status_distribution' => $this->getStatusDistribution(),
      'bank_distribution' => $this->getBankDistribution(),
      'expiry_status' => $this->getExpiryStatus(),
      'monthly_trend' => $this->getMonthlyTrend(),
      'goods_distribution' => $this->getGoodsDistribution(),
      'transport_distribution' => $this->getTransportDistribution(),
      'currency_distribution' => $this->getCurrencyDistribution(),
      'weight_distribution' => $this->getWeightDistribution(),
      'value_weight_scatter' => $this->getValueWeightScatter(),
      'entry_post_distribution' => $this->getEntryPostDistribution(),
      'recent_licenses' => $this->getRecentLicenses(),
      'client_stats' => $this->getClientStats(),
      'client_details' => $this->getClientDetails(),
      'expiry_details' => $this->getExpiryDetails()
    ];

    $this->viewWithLayout('licenses/licensedashboard', $data);
  }

  private function getKPIData()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_licenses,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active_licenses,
                SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) as inactive_licenses,
                SUM(CASE WHEN status = 'ANNULATED' THEN 1 ELSE 0 END) as annulated_licenses,
                SUM(CASE WHEN status = 'MODIFIED' THEN 1 ELSE 0 END) as modified_licenses,
                SUM(CASE WHEN status = 'PROROGATED' THEN 1 ELSE 0 END) as prorogated_licenses,
                SUM(CASE WHEN license_expiry_date >= CURDATE() THEN 1 ELSE 0 END) as valid_licenses,
                SUM(CASE WHEN license_expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired_licenses,
                SUM(CASE WHEN license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as expiring_soon_7,
                SUM(CASE WHEN license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 1 ELSE 0 END) as expiring_soon_15,
                SUM(CASE WHEN license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon_30,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_licenses,
                SUM(CASE WHEN DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as yesterday_licenses,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week_licenses,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_licenses,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) as last_month_licenses,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_year_licenses,
                COALESCE(SUM(fob_declared), 0) as total_fob_value,
                COALESCE(SUM(CASE WHEN status = 'ACTIVE' THEN fob_declared ELSE 0 END), 0) as active_fob_value,
                COALESCE(SUM(weight), 0) as total_weight,
                COALESCE(AVG(fob_declared), 0) as avg_fob_value,
                COALESCE(MAX(fob_declared), 0) as max_fob_value,
                COALESCE(MIN(fob_declared), 0) as min_fob_value,
                COUNT(DISTINCT client_id) as unique_clients,
                COUNT(DISTINCT bank_id) as unique_banks
              FROM licenses_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("KPI Data Error: " . $e->getMessage());
      return [];
    }
  }

  private function getStatusDistribution()
  {
    try {
      $sql = "SELECT 
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'INACTIVE' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN status = 'ANNULATED' THEN 1 ELSE 0 END) as annulated,
                SUM(CASE WHEN status = 'MODIFIED' THEN 1 ELSE 0 END) as modified,
                SUM(CASE WHEN status = 'PROROGATED' THEN 1 ELSE 0 END) as prorogated
              FROM licenses_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? ['active' => 0, 'inactive' => 0, 'annulated' => 0, 'modified' => 0, 'prorogated' => 0];
    } catch (Exception $e) {
      error_log("Status Distribution Error: " . $e->getMessage());
      return ['active' => 0, 'inactive' => 0, 'annulated' => 0, 'modified' => 0, 'prorogated' => 0];
    }
  }

  private function getBankDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(b.bank_name, 'Not Specified') as bank_name,
                COUNT(l.id) as license_count,
                SUM(l.fob_declared) as total_fob
              FROM licenses_t l
              LEFT JOIN banklist_master_t b ON l.bank_id = b.id AND b.display = 'Y'
              WHERE l.display = 'Y'
              GROUP BY l.bank_id, b.bank_name
              HAVING COUNT(l.id) > 0
              ORDER BY license_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Bank Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getExpiryStatus()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_licenses,
                SUM(CASE WHEN license_expiry_date >= CURDATE() THEN 1 ELSE 0 END) as valid,
                SUM(CASE WHEN license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon,
                SUM(CASE WHEN license_expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired
              FROM licenses_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? ['total_licenses' => 0, 'valid' => 0, 'expiring_soon' => 0, 'expired' => 0];
    } catch (Exception $e) {
      error_log("Expiry Status Error: " . $e->getMessage());
      return ['total_licenses' => 0, 'valid' => 0, 'expiring_soon' => 0, 'expired' => 0];
    }
  }

  private function getExpiryDetails()
  {
    try {
      $sql = "SELECT 
                l.id,
                l.license_number,
                COALESCE(c.short_name, 'N/A') as client_name,
                COALESCE(b.bank_name, 'N/A') as bank_name,
                l.fob_declared,
                l.license_applied_date,
                l.license_expiry_date,
                l.status,
                DATEDIFF(l.license_expiry_date, CURDATE()) as days_to_expiry,
                CASE 
                  WHEN l.license_expiry_date < CURDATE() THEN 'Expired'
                  WHEN l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Expiring in 7 Days'
                  WHEN l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 'Expiring in 15 Days'
                  WHEN l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring in 30 Days'
                  ELSE 'Valid'
                END as expiry_category
              FROM licenses_t l
              LEFT JOIN clients_t c ON l.client_id = c.id AND c.display = 'Y'
              LEFT JOIN banklist_master_t b ON l.bank_id = b.id AND b.display = 'Y'
              WHERE l.display = 'Y'
              ORDER BY l.license_expiry_date ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Expiry Details Error: " . $e->getMessage());
      return [];
    }
  }

  private function getMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(id) as license_count,
                COALESCE(SUM(fob_declared), 0) as total_fob_value,
                COALESCE(SUM(weight), 0) as total_weight
              FROM licenses_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  private function getGoodsDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tg.goods_type, 'Not Specified') as goods_name,
                COUNT(l.id) as license_count,
                SUM(l.weight) as total_weight,
                SUM(l.fob_declared) as total_fob
              FROM licenses_t l
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id AND tg.display = 'Y'
              WHERE l.display = 'Y'
              GROUP BY l.type_of_goods_id, tg.goods_type
              HAVING COUNT(l.id) > 0
              ORDER BY license_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Goods Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTransportDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tm.transport_mode_name, 'Not Specified') as transport_name,
                COUNT(l.id) as license_count
              FROM licenses_t l
              LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id AND tm.display = 'Y'
              WHERE l.display = 'Y'
              GROUP BY l.transport_mode_id, tm.transport_mode_name
              HAVING COUNT(l.id) > 0
              ORDER BY license_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Transport Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getCurrencyDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(cur.currency_name, 'USD') as currency_name,
                COUNT(l.id) as license_count,
                SUM(l.fob_declared) as total_fob
              FROM licenses_t l
              LEFT JOIN currency_master_t cur ON l.currency_id = cur.id AND cur.display = 'Y'
              WHERE l.display = 'Y'
              GROUP BY l.currency_id, cur.currency_name
              HAVING COUNT(l.id) > 0
              ORDER BY license_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Currency Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getWeightDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tg.goods_type, 'Not Specified') as goods_name,
                COALESCE(SUM(l.weight), 0) as total_weight
              FROM licenses_t l
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id AND tg.display = 'Y'
              WHERE l.display = 'Y' AND l.weight > 0
              GROUP BY l.type_of_goods_id, tg.goods_type
              HAVING total_weight > 0
              ORDER BY total_weight DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Weight Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getValueWeightScatter()
  {
    try {
      $sql = "SELECT 
                weight,
                fob_declared
              FROM licenses_t
              WHERE display = 'Y'
                AND weight > 0
                AND fob_declared > 0
              ORDER BY created_at DESC
              LIMIT 100";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Value Weight Scatter Error: " . $e->getMessage());
      return [];
    }
  }

  private function getEntryPostDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as entry_post_name,
                COUNT(l.id) as license_count
              FROM licenses_t l
              LEFT JOIN transit_point_master_t tp ON l.entry_post_id = tp.id 
                AND tp.display = 'Y' 
                AND tp.entry_point = 'Y'
              WHERE l.display = 'Y'
              GROUP BY l.entry_post_id, tp.transit_point_name
              HAVING COUNT(l.id) > 0
              ORDER BY license_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Entry Post Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRecentLicenses()
  {
    try {
      $sql = "SELECT 
                l.id,
                l.license_number,
                l.invoice_number,
                l.fob_declared,
                l.weight,
                l.license_applied_date,
                l.license_expiry_date,
                l.status,
                COALESCE(c.short_name, 'N/A') as client_name,
                COALESCE(b.bank_name, 'N/A') as bank_name,
                COALESCE(u.unit_name, 'KG') as unit_name
              FROM licenses_t l
              LEFT JOIN clients_t c ON l.client_id = c.id AND c.display = 'Y'
              LEFT JOIN banklist_master_t b ON l.bank_id = b.id AND b.display = 'Y'
              LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id AND u.display = 'Y'
              WHERE l.display = 'Y'
              ORDER BY l.created_at DESC
              LIMIT 20";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Recent Licenses Error: " . $e->getMessage());
      return [];
    }
  }

  private function getAllLicenses()
  {
    try {
      $sql = "SELECT 
                l.id,
                l.license_number,
                l.invoice_number,
                l.fob_declared,
                l.weight,
                l.license_applied_date,
                l.license_expiry_date,
                l.status,
                COALESCE(c.short_name, 'N/A') as client_name,
                COALESCE(b.bank_name, 'N/A') as bank_name,
                COALESCE(u.unit_name, 'KG') as unit_name,
                COALESCE(tg.goods_type, 'N/A') as goods_type,
                COALESCE(tm.transport_mode_name, 'N/A') as transport_mode,
                COALESCE(cur.currency_name, 'USD') as currency
              FROM licenses_t l
              LEFT JOIN clients_t c ON l.client_id = c.id AND c.display = 'Y'
              LEFT JOIN banklist_master_t b ON l.bank_id = b.id AND b.display = 'Y'
              LEFT JOIN unit_master_t u ON l.unit_of_measurement_id = u.id AND u.display = 'Y'
              LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id AND tg.display = 'Y'
              LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id AND tm.display = 'Y'
              LEFT JOIN currency_master_t cur ON l.currency_id = cur.id AND cur.display = 'Y'
              WHERE l.display = 'Y'
              ORDER BY l.created_at DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("All Licenses Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientStats()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_clients,
                SUM(CASE WHEN display = 'Y' THEN 1 ELSE 0 END) as active_clients,
                SUM(CASE WHEN verified_by_id IS NOT NULL THEN 1 ELSE 0 END) as verified_clients,
                (SELECT COUNT(DISTINCT client_id) FROM licenses_t WHERE display = 'Y') as clients_with_licenses
              FROM clients_t";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? ['total_clients' => 0, 'active_clients' => 0, 'verified_clients' => 0, 'clients_with_licenses' => 0];
    } catch (Exception $e) {
      error_log("Client Stats Error: " . $e->getMessage());
      return ['total_clients' => 0, 'active_clients' => 0, 'verified_clients' => 0, 'clients_with_licenses' => 0];
    }
  }

  private function getClientDetails()
  {
    try {
      $sql = "SELECT 
                c.id,
                c.short_name,
                c.company_name,
                c.client_type,
                c.display,
                c.contact_person,
                c.email,
                c.payment_term,
                COUNT(l.id) as total_licenses,
                SUM(CASE WHEN l.status = 'ACTIVE' THEN 1 ELSE 0 END) as active_licenses,
                COALESCE(SUM(l.fob_declared), 0) as total_fob_value,
                COALESCE(SUM(l.weight), 0) as total_weight,
                MAX(l.created_at) as last_license_date,
                CASE 
                  WHEN COUNT(l.id) > 0 THEN ROUND((SUM(CASE WHEN l.status = 'ACTIVE' THEN 1 ELSE 0 END) / COUNT(l.id)) * 100, 0)
                  ELSE 0 
                END as success_rate
              FROM clients_t c
              LEFT JOIN licenses_t l ON c.id = l.client_id AND l.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name, c.client_type, c.display, c.contact_person, c.email, c.payment_term
              HAVING total_licenses > 0
              ORDER BY total_licenses DESC, total_fob_value DESC";
      
      $clients = $this->db->customQuery($sql) ?: [];

      foreach ($clients as &$client) {
        $client_id = $client['id'];
        
        $transport_sql = "SELECT 
                            COALESCE(tm.transport_mode_name, 'Not Specified') as transport_name, 
                            COUNT(l.id) as license_count
                          FROM licenses_t l
                          LEFT JOIN transport_mode_master_t tm ON l.transport_mode_id = tm.id
                          WHERE l.client_id = ? AND l.display = 'Y'
                          GROUP BY l.transport_mode_id
                          HAVING COUNT(l.id) > 0";
        $client['transport_breakdown'] = $this->db->customQuery($transport_sql, [$client_id]) ?: [];
        
        $goods_sql = "SELECT 
                        COALESCE(tg.goods_type, 'Not Specified') as goods_name, 
                        COUNT(l.id) as license_count
                      FROM licenses_t l
                      LEFT JOIN type_of_goods_master_t tg ON l.type_of_goods_id = tg.id
                      WHERE l.client_id = ? AND l.display = 'Y'
                      GROUP BY l.type_of_goods_id
                      HAVING COUNT(l.id) > 0";
        $client['goods_breakdown'] = $this->db->customQuery($goods_sql, [$client_id]) ?: [];
        
        $bank_sql = "SELECT 
                       COALESCE(b.bank_name, 'Not Specified') as bank_name, 
                       COUNT(l.id) as license_count
                     FROM licenses_t l
                     LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                     WHERE l.client_id = ? AND l.display = 'Y'
                     GROUP BY l.bank_id
                     HAVING COUNT(l.id) > 0";
        $client['bank_breakdown'] = $this->db->customQuery($bank_sql, [$client_id]) ?: [];
        
        $payment_sql = "SELECT 
                          COALESCE(pm.method_name, 'Not Specified') as payment_method, 
                          COUNT(l.id) as license_count
                        FROM licenses_t l
                        LEFT JOIN payment_method_master_t pm ON l.payment_method_id = pm.id
                        WHERE l.client_id = ? AND l.display = 'Y'
                        GROUP BY l.payment_method_id
                        HAVING COUNT(l.id) > 0";
        $client['payment_breakdown'] = $this->db->customQuery($payment_sql, [$client_id]) ?: [];
      }

      return $clients;
    } catch (Exception $e) {
      error_log("Client Details Error: " . $e->getMessage());
      return [];
    }
  }

  public function getModalData()
  {
    header('Content-Type: application/json');
    
    $type = $_POST['type'] ?? '';
    
    $validTypes = [
      'allLicenses', 'activeLicenses', 'monthLicenses', 
      'todayLicenses', 'validLicenses', 'expiredLicenses', 
      'expiringSoon', 'fobValue'
    ];
    
    if (!in_array($type, $validTypes)) {
      echo json_encode(['success' => false, 'error' => 'Invalid type']);
      exit;
    }
    
    try {
      $sql = "";
      
      switch ($type) {
        case 'allLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y'
                  ORDER BY l.created_at DESC
                  LIMIT 100";
          break;
          
        case 'activeLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' AND l.status = 'ACTIVE'
                  ORDER BY l.created_at DESC
                  LIMIT 100";
          break;
          
        case 'monthLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' 
                    AND YEAR(l.created_at) = YEAR(CURDATE()) 
                    AND MONTH(l.created_at) = MONTH(CURDATE())
                  ORDER BY l.created_at DESC
                  LIMIT 100";
          break;
          
        case 'todayLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' AND DATE(l.created_at) = CURDATE()
                  ORDER BY l.created_at DESC
                  LIMIT 100";
          break;
          
        case 'validLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' AND l.license_expiry_date >= CURDATE()
                  ORDER BY l.license_expiry_date ASC
                  LIMIT 100";
          break;
          
        case 'expiredLicenses':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' AND l.license_expiry_date < CURDATE()
                  ORDER BY l.license_expiry_date DESC
                  LIMIT 100";
          break;
          
        case 'expiringSoon':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' 
                    AND l.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                  ORDER BY l.license_expiry_date ASC
                  LIMIT 100";
          break;
          
        case 'fobValue':
          $sql = "SELECT l.license_number, c.short_name as client_name, b.bank_name, l.fob_declared, 
                         DATE_FORMAT(l.license_expiry_date, '%Y-%m-%d') as license_expiry_date, l.status
                  FROM licenses_t l
                  LEFT JOIN clients_t c ON l.client_id = c.id
                  LEFT JOIN banklist_master_t b ON l.bank_id = b.id
                  WHERE l.display = 'Y' AND l.fob_declared > 0
                  ORDER BY l.fob_declared DESC
                  LIMIT 100";
          break;
      }
      
      $data = $this->db->customQuery($sql);
      echo json_encode(['success' => true, 'data' => $data ?: []]);
      
    } catch (Exception $e) {
      error_log("Modal Data Error: " . $e->getMessage());
      echo json_encode(['success' => false, 'error' => 'Data retrieval failed']);
    }
    exit;
  }

public function exportDashboard()
{
  try {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found. Please run: composer require phpoffice/phpspreadsheet');
    }

    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
      die('PhpSpreadsheet class not available');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    $spreadsheet->getProperties()
      ->setCreator("Malabar Group")
      ->setTitle("License Dashboard - Complete Export")
      ->setSubject("Comprehensive License Analytics & Reports")
      ->setDescription("Generated from License Management System")
      ->setKeywords("licenses, dashboard, analytics, export")
      ->setCategory("Reports");

    // ===================== PREMIUM STYLES =====================
    
    $titleStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 16, 'name' => 'Calibri'],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
        'rotation' => 90,
        'startColor' => ['rgb' => '667eea'],
        'endColor' => ['rgb' => '764ba2']
      ],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ]
    ];

    $headerStyle = [
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12, 'name' => 'Calibri'],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A5568']],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ],
      'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '2D3748']]]
    ];

    $subHeaderStyle = [
      'font' => ['bold' => true, 'size' => 11, 'name' => 'Calibri', 'color' => ['rgb' => '2D3748']],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ],
      'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E0']]]
    ];

    $dataCellStyle = [
      'font' => ['size' => 10, 'name' => 'Calibri'],
      'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
      'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
    ];

    $currencyFormat = '#,##0.00';
    $numberFormat = '#,##0';
    $percentFormat = '0"%"';

    // ===================== SHEET 1: EXECUTIVE SUMMARY =====================
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('📊 Executive Summary');
    $sheet1->getTabColor()->setRGB('667eea');
    
    $kpiData = $this->getKPIData();
    
    $sheet1->setCellValue('A1', 'LICENSE DASHBOARD - EXECUTIVE SUMMARY');
    $sheet1->mergeCells('A1:F1');
    $sheet1->getRowDimension('1')->setRowHeight(35);
    $sheet1->getStyle('A1')->applyFromArray($titleStyle);
    
    $sheet1->setCellValue('A2', 'Generated: ' . date('l, F j, Y - g:i A'));
    $sheet1->mergeCells('A2:F2');
    $sheet1->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
    $sheet1->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    $row = 4;
    
    // LICENSE OVERVIEW
    $sheet1->setCellValue('A' . $row, '📋 LICENSE OVERVIEW');
    $sheet1->mergeCells('A' . $row . ':F' . $row);
    $sheet1->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '667eea']],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF4FF']],
      'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '667eea']]]
    ]);
    $sheet1->getRowDimension($row)->setRowHeight(25);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Metric');
    $sheet1->setCellValue('B' . $row, 'Value');
    $sheet1->setCellValue('C' . $row, 'Metric');
    $sheet1->setCellValue('D' . $row, 'Value');
    $sheet1->setCellValue('E' . $row, 'Metric');
    $sheet1->setCellValue('F' . $row, 'Value');
    $sheet1->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
    $sheet1->getRowDimension($row)->setRowHeight(20);
    
    $kpiGroups = [
      [
        ['Total Licenses', 'total_licenses', 'number'],
        ['Active Licenses', 'active_licenses', 'number'],
        ['Valid Licenses', 'valid_licenses', 'number']
      ],
      [
        ['Inactive Licenses', 'inactive_licenses', 'number'],
        ['Expired Licenses', 'expired_licenses', 'number'],
        ['Expiring Soon (15d)', 'expiring_soon_15', 'number']
      ],
      [
        ['Modified', 'modified_licenses', 'number'],
        ['Annulated', 'annulated_licenses', 'number'],
        ['Prorogated', 'prorogated_licenses', 'number']
      ]
    ];
    
    foreach ($kpiGroups as $group) {
      $row++;
      $col = 0;
      foreach ($group as $item) {
        $colLetter = chr(65 + $col);
        $valueLetter = chr(65 + $col + 1);
        
        $sheet1->setCellValue($colLetter . $row, $item[0]);
        $value = isset($kpiData[$item[1]]) && !is_null($kpiData[$item[1]]) ? $kpiData[$item[1]] : 0;
        $sheet1->setCellValue($valueLetter . $row, $value);
        
        $sheet1->getStyle($colLetter . $row)->applyFromArray($subHeaderStyle);
        $sheet1->getStyle($valueLetter . $row)->applyFromArray($dataCellStyle);
        $sheet1->getStyle($valueLetter . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet1->getStyle($valueLetter . $row)->getFont()->setBold(true)->setSize(11);
        $sheet1->getStyle($valueLetter . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $col += 2;
      }
      $sheet1->getRowDimension($row)->setRowHeight(22);
    }
    
    $row += 2;
    
    // FINANCIAL OVERVIEW
    $sheet1->setCellValue('A' . $row, '💰 FINANCIAL OVERVIEW');
    $sheet1->mergeCells('A' . $row . ':F' . $row);
    $sheet1->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '10c469']],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4EDDA']],
      'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '10c469']]]
    ]);
    $sheet1->getRowDimension($row)->setRowHeight(25);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Metric');
    $sheet1->setCellValue('B' . $row, 'Value');
    $sheet1->setCellValue('C' . $row, 'Metric');
    $sheet1->setCellValue('D' . $row, 'Value');
    $sheet1->setCellValue('E' . $row, 'Metric');
    $sheet1->setCellValue('F' . $row, 'Value');
    $sheet1->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
    
    $financialMetrics = [
      [
        ['Total FOB Value', 'total_fob_value', 'currency'],
        ['Active FOB Value', 'active_fob_value', 'currency'],
        ['Average FOB', 'avg_fob_value', 'currency']
      ],
      [
        ['Maximum FOB', 'max_fob_value', 'currency'],
        ['Minimum FOB', 'min_fob_value', 'currency'],
        ['Total Weight (KG)', 'total_weight', 'weight']
      ]
    ];
    
    foreach ($financialMetrics as $group) {
      $row++;
      $col = 0;
      foreach ($group as $item) {
        $colLetter = chr(65 + $col);
        $valueLetter = chr(65 + $col + 1);
        
        $sheet1->setCellValue($colLetter . $row, $item[0]);
        $value = isset($kpiData[$item[1]]) && !is_null($kpiData[$item[1]]) ? $kpiData[$item[1]] : 0;
        $sheet1->setCellValue($valueLetter . $row, $value);
        
        $sheet1->getStyle($colLetter . $row)->applyFromArray($subHeaderStyle);
        $sheet1->getStyle($valueLetter . $row)->applyFromArray($dataCellStyle);
        
        if ($item[2] === 'currency') {
          $sheet1->getStyle($valueLetter . $row)->getNumberFormat()->setFormatCode($currencyFormat);
          $sheet1->getStyle($valueLetter . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F0FFF4');
        } elseif ($item[2] === 'weight') {
          $sheet1->getStyle($valueLetter . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        $sheet1->getStyle($valueLetter . $row)->getFont()->setBold(true)->setSize(11);
        $sheet1->getStyle($valueLetter . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $col += 2;
      }
    }
    
    $row += 2;
    
    // ACTIVITY TIMELINE
    $sheet1->setCellValue('A' . $row, '📅 ACTIVITY TIMELINE');
    $sheet1->mergeCells('A' . $row . ':F' . $row);
    $sheet1->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'f9c851']],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
      'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => 'f9c851']]]
    ]);
    $sheet1->getRowDimension($row)->setRowHeight(25);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Period');
    $sheet1->setCellValue('B' . $row, 'Count');
    $sheet1->setCellValue('C' . $row, 'Period');
    $sheet1->setCellValue('D' . $row, 'Count');
    $sheet1->setCellValue('E' . $row, 'Period');
    $sheet1->setCellValue('F' . $row, 'Count');
    $sheet1->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
    
    $timelineMetrics = [
      [
        ['Today', 'today_licenses', 'number'],
        ['Yesterday', 'yesterday_licenses', 'number'],
        ['This Week', 'this_week_licenses', 'number']
      ],
      [
        ['This Month', 'this_month_licenses', 'number'],
        ['Last Month', 'last_month_licenses', 'number'],
        ['This Year', 'this_year_licenses', 'number']
      ]
    ];
    
    foreach ($timelineMetrics as $group) {
      $row++;
      $col = 0;
      foreach ($group as $item) {
        $colLetter = chr(65 + $col);
        $valueLetter = chr(65 + $col + 1);
        
        $sheet1->setCellValue($colLetter . $row, $item[0]);
        $value = isset($kpiData[$item[1]]) && !is_null($kpiData[$item[1]]) ? $kpiData[$item[1]] : 0;
        $sheet1->setCellValue($valueLetter . $row, $value);
        
        $sheet1->getStyle($colLetter . $row)->applyFromArray($subHeaderStyle);
        $sheet1->getStyle($valueLetter . $row)->applyFromArray($dataCellStyle);
        $sheet1->getStyle($valueLetter . $row)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet1->getStyle($valueLetter . $row)->getFont()->setBold(true)->setSize(11);
        $sheet1->getStyle($valueLetter . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $col += 2;
      }
    }
    
    $row += 2;
    
    // KEY METRICS
    $sheet1->setCellValue('A' . $row, '🔑 KEY METRICS');
    $sheet1->mergeCells('A' . $row . ':F' . $row);
    $sheet1->getStyle('A' . $row)->applyFromArray([
      'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '5b69bc']],
      'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1ECF1']],
      'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['rgb' => '5b69bc']]]
    ]);
    $sheet1->getRowDimension($row)->setRowHeight(25);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Metric');
    $sheet1->setCellValue('B' . $row, 'Value');
    $sheet1->setCellValue('C' . $row, 'Metric');
    $sheet1->setCellValue('D' . $row, 'Value');
    $sheet1->setCellValue('E' . $row, 'Metric');
    $sheet1->setCellValue('F' . $row, 'Value');
    $sheet1->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Unique Clients');
    $sheet1->setCellValue('B' . $row, isset($kpiData['unique_clients']) ? $kpiData['unique_clients'] : 0);
    $sheet1->setCellValue('C' . $row, 'Unique Banks');
    $sheet1->setCellValue('D' . $row, isset($kpiData['unique_banks']) ? $kpiData['unique_banks'] : 0);
    $sheet1->setCellValue('E' . $row, 'Expiring (7 days)');
    $sheet1->setCellValue('F' . $row, isset($kpiData['expiring_soon_7']) ? $kpiData['expiring_soon_7'] : 0);
    
    $sheet1->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
    $sheet1->getStyle('C' . $row)->applyFromArray($subHeaderStyle);
    $sheet1->getStyle('E' . $row)->applyFromArray($subHeaderStyle);
    $sheet1->getStyle('B' . $row . ':F' . $row)->applyFromArray($dataCellStyle);
    $sheet1->getStyle('B' . $row . ':F' . $row)->getNumberFormat()->setFormatCode($numberFormat);
    $sheet1->getStyle('B' . $row . ':F' . $row)->getFont()->setBold(true);
    $sheet1->getStyle('B' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
    $row++;
    $sheet1->setCellValue('A' . $row, 'Expiring (30 days)');
    $sheet1->setCellValue('B' . $row, isset($kpiData['expiring_soon_30']) ? $kpiData['expiring_soon_30'] : 0);
    $sheet1->mergeCells('C' . $row . ':F' . $row);
    
    $sheet1->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
    $sheet1->getStyle('B' . $row)->applyFromArray($dataCellStyle);
    $sheet1->getStyle('B' . $row)->getNumberFormat()->setFormatCode($numberFormat);
    $sheet1->getStyle('B' . $row)->getFont()->setBold(true);
    $sheet1->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
    $sheet1->getColumnDimension('A')->setWidth(25);
    $sheet1->getColumnDimension('B')->setWidth(18);
    $sheet1->getColumnDimension('C')->setWidth(25);
    $sheet1->getColumnDimension('D')->setWidth(18);
    $sheet1->getColumnDimension('E')->setWidth(25);
    $sheet1->getColumnDimension('F')->setWidth(18);

    // ===================== SHEET 2: ALL LICENSES =====================
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('📋 All Licenses');
    $sheet2->getTabColor()->setRGB('10c469');
    
    $allLicenses = $this->getAllLicenses();
    
    $sheet2->setCellValue('A1', 'COMPLETE LICENSE REGISTER');
    $sheet2->mergeCells('A1:N1');
    $sheet2->getRowDimension('1')->setRowHeight(35);
    $sheet2->getStyle('A1')->applyFromArray($titleStyle);
    
    $sheet2->setCellValue('A2', 'Total Records: ' . count($allLicenses) . ' | Generated: ' . date('Y-m-d H:i:s'));
    $sheet2->mergeCells('A2:N2');
    $sheet2->getStyle('A2')->getFont()->setItalic(true)->setBold(true);
    $sheet2->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    $row = 4;
    $headers = ['ID', 'License #', 'Client', 'Bank', 'Invoice #', 'FOB Value', 'Weight', 'Unit', 'Goods Type', 'Transport', 'Currency', 'Applied', 'Expiry', 'Status'];
    $col = 'A';
    foreach ($headers as $header) {
      $sheet2->setCellValue($col . $row, $header);
      $col++;
    }
    $sheet2->getStyle('A' . $row . ':N' . $row)->applyFromArray($headerStyle);
    $sheet2->getRowDimension($row)->setRowHeight(25);
    
    foreach ($allLicenses as $index => $license) {
      $row++;
      $sheet2->setCellValue('A' . $row, $license['id']);
      $sheet2->setCellValue('B' . $row, $license['license_number']);
      $sheet2->setCellValue('C' . $row, $license['client_name']);
      $sheet2->setCellValue('D' . $row, $license['bank_name']);
      $sheet2->setCellValue('E' . $row, $license['invoice_number']);
      
      $fob = isset($license['fob_declared']) && !is_null($license['fob_declared']) ? (float)$license['fob_declared'] : 0;
      $sheet2->setCellValue('F' . $row, $fob);
      
      $weight = isset($license['weight']) && !is_null($license['weight']) ? (float)$license['weight'] : 0;
      $sheet2->setCellValue('G' . $row, $weight);
      
      $sheet2->setCellValue('H' . $row, $license['unit_name']);
      $sheet2->setCellValue('I' . $row, $license['goods_type']);
      $sheet2->setCellValue('J' . $row, $license['transport_mode']);
      $sheet2->setCellValue('K' . $row, $license['currency']);
      $sheet2->setCellValue('L' . $row, $license['license_applied_date']);
      $sheet2->setCellValue('M' . $row, $license['license_expiry_date']);
      $sheet2->setCellValue('N' . $row, $license['status']);
      
      $sheet2->getStyle('A' . $row . ':N' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet2->getStyle('A' . $row . ':N' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet2->getStyle('F' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet2->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      
      $status = $license['status'];
      if ($status === 'ACTIVE') {
        $sheet2->getStyle('N' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6F6D5');
        $sheet2->getStyle('N' . $row)->getFont()->getColor()->setRGB('22543D');
      } elseif ($status === 'EXPIRED') {
        $sheet2->getStyle('N' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet2->getStyle('N' . $row)->getFont()->getColor()->setRGB('742A2A');
      } elseif ($status === 'ANNULATED') {
        $sheet2->getStyle('N' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FBD38D');
        $sheet2->getStyle('N' . $row)->getFont()->getColor()->setRGB('744210');
      }
    }
    
    foreach (range('A', 'N') as $col) {
      $sheet2->getColumnDimension($col)->setAutoSize(true);
    }
    
    $sheet2->freezePane('A5');

    // ===================== SHEET 3: STATUS ANALYSIS =====================
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('📊 Status Analysis');
    $sheet3->getTabColor()->setRGB('f9c851');
    
    $statusData = $this->getStatusDistribution();
    
    $sheet3->setCellValue('A1', 'LICENSE STATUS DISTRIBUTION');
    $sheet3->mergeCells('A1:D1');
    $sheet3->getRowDimension('1')->setRowHeight(35);
    $sheet3->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet3->setCellValue('A' . $row, 'Status');
    $sheet3->setCellValue('B' . $row, 'Count');
    $sheet3->setCellValue('C' . $row, 'Percentage');
    $sheet3->setCellValue('D' . $row, 'Visual');
    $sheet3->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
    $sheet3->getRowDimension($row)->setRowHeight(25);
    
    $totalStatuses = (isset($statusData['active']) ? $statusData['active'] : 0) +
                      (isset($statusData['inactive']) ? $statusData['inactive'] : 0) +
                      (isset($statusData['annulated']) ? $statusData['annulated'] : 0) +
                      (isset($statusData['modified']) ? $statusData['modified'] : 0) +
                      (isset($statusData['prorogated']) ? $statusData['prorogated'] : 0);
    $totalStatuses = $totalStatuses > 0 ? $totalStatuses : 1;
    
    $statuses = [
      ['Active', 'active', 'C6F6D5', '22543D'],
      ['Inactive', 'inactive', 'E2E8F0', '2D3748'],
      ['Annulated', 'annulated', 'FED7D7', '742A2A'],
      ['Modified', 'modified', 'FBD38D', '744210'],
      ['Prorogated', 'prorogated', 'BEE3F8', '2C5282']
    ];
    
    foreach ($statuses as $statusInfo) {
      $row++;
      $value = isset($statusData[$statusInfo[1]]) && !is_null($statusData[$statusInfo[1]]) ? (int)$statusData[$statusInfo[1]] : 0;
      $percentage = round(($value / $totalStatuses) * 100, 1);
      
      $sheet3->setCellValue('A' . $row, $statusInfo[0]);
      $sheet3->setCellValue('B' . $row, $value);
      $sheet3->setCellValue('C' . $row, $percentage / 100);
      $sheet3->setCellValue('D' . $row, str_repeat('█', (int)($percentage / 2)));
      
      $sheet3->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
      $sheet3->getStyle('B' . $row)->applyFromArray($dataCellStyle);
      $sheet3->getStyle('C' . $row)->applyFromArray($dataCellStyle);
      $sheet3->getStyle('D' . $row)->applyFromArray($dataCellStyle);
      
      $sheet3->getStyle('B' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet3->getStyle('C' . $row)->getNumberFormat()->setFormatCode($percentFormat);
      $sheet3->getStyle('B' . $row)->getFont()->setBold(true)->setSize(12);
      
      $sheet3->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($statusInfo[2]);
      $sheet3->getStyle('A' . $row)->getFont()->getColor()->setRGB($statusInfo[3]);
      $sheet3->getStyle('D' . $row)->getFont()->getColor()->setRGB($statusInfo[3]);
      
      $sheet3->getRowDimension($row)->setRowHeight(22);
    }
    
    $row += 2;
    $sheet3->setCellValue('A' . $row, 'TOTAL');
    $sheet3->setCellValue('B' . $row, $totalStatuses);
    $sheet3->setCellValue('C' . $row, 1);
    $sheet3->mergeCells('D' . $row . ':D' . $row);
    
    $sheet3->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
    $sheet3->getStyle('B' . $row)->getNumberFormat()->setFormatCode($numberFormat);
    $sheet3->getStyle('C' . $row)->getNumberFormat()->setFormatCode($percentFormat);
    
    $sheet3->getColumnDimension('A')->setWidth(20);
    $sheet3->getColumnDimension('B')->setWidth(15);
    $sheet3->getColumnDimension('C')->setWidth(15);
    $sheet3->getColumnDimension('D')->setWidth(50);

    // ===================== SHEET 4: BANK ANALYSIS =====================
    $sheet4 = $spreadsheet->createSheet();
    $sheet4->setTitle('🏦 Bank Analysis');
    $sheet4->getTabColor()->setRGB('35b8e0');
    
    $bankData = $this->getBankDistribution();
    
    $sheet4->setCellValue('A1', 'BANK PERFORMANCE ANALYSIS');
    $sheet4->mergeCells('A1:E1');
    $sheet4->getRowDimension('1')->setRowHeight(35);
    $sheet4->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet4->setCellValue('A' . $row, 'Rank');
    $sheet4->setCellValue('B' . $row, 'Bank Name');
    $sheet4->setCellValue('C' . $row, 'License Count');
    $sheet4->setCellValue('D' . $row, 'Total FOB Value');
    $sheet4->setCellValue('E' . $row, 'Avg FOB/License');
    $sheet4->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);
    $sheet4->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($bankData as $index => $bank) {
      $row++;
      $count = isset($bank['license_count']) && !is_null($bank['license_count']) ? (int)$bank['license_count'] : 0;
      $totalFob = isset($bank['total_fob']) && !is_null($bank['total_fob']) ? (float)$bank['total_fob'] : 0;
      $avgFob = $count > 0 ? $totalFob / $count : 0;
      
      $sheet4->setCellValue('A' . $row, $rank);
      $sheet4->setCellValue('B' . $row, $bank['bank_name']);
      $sheet4->setCellValue('C' . $row, $count);
      $sheet4->setCellValue('D' . $row, $totalFob);
      $sheet4->setCellValue('E' . $row, $avgFob);
      
      $sheet4->getStyle('A' . $row . ':E' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet4->getStyle('A' . $row . ':E' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet4->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet4->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet4->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet4->getStyle('D' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet4->getStyle('E' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      
      if ($rank <= 3) {
        $colors = ['FFD700', 'C0C0C0', 'CD7F32'];
        $sheet4->getStyle('A' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB($colors[$rank - 1]);
      }
      
      $rank++;
    }
    
    foreach (range('A', 'E') as $col) {
      $sheet4->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 5: GOODS ANALYSIS =====================
    $sheet5 = $spreadsheet->createSheet();
    $sheet5->setTitle('📦 Goods Analysis');
    $sheet5->getTabColor()->setRGB('8f75da');
    
    $goodsData = $this->getGoodsDistribution();
    
    $sheet5->setCellValue('A1', 'GOODS TYPE ANALYSIS');
    $sheet5->mergeCells('A1:F1');
    $sheet5->getRowDimension('1')->setRowHeight(35);
    $sheet5->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet5->setCellValue('A' . $row, 'Rank');
    $sheet5->setCellValue('B' . $row, 'Goods Type');
    $sheet5->setCellValue('C' . $row, 'License Count');
    $sheet5->setCellValue('D' . $row, 'Total Weight (KG)');
    $sheet5->setCellValue('E' . $row, 'Total FOB Value');
    $sheet5->setCellValue('F' . $row, 'Avg FOB/License');
    $sheet5->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
    $sheet5->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($goodsData as $index => $goods) {
      $row++;
      $count = isset($goods['license_count']) && !is_null($goods['license_count']) ? (int)$goods['license_count'] : 0;
      $weight = isset($goods['total_weight']) && !is_null($goods['total_weight']) ? (float)$goods['total_weight'] : 0;
      $fob = isset($goods['total_fob']) && !is_null($goods['total_fob']) ? (float)$goods['total_fob'] : 0;
      $avgFob = $count > 0 ? $fob / $count : 0;
      
      $sheet5->setCellValue('A' . $row, $rank);
      $sheet5->setCellValue('B' . $row, $goods['goods_name']);
      $sheet5->setCellValue('C' . $row, $count);
      $sheet5->setCellValue('D' . $row, $weight);
      $sheet5->setCellValue('E' . $row, $fob);
      $sheet5->setCellValue('F' . $row, $avgFob);
      
      $sheet5->getStyle('A' . $row . ':F' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet5->getStyle('A' . $row . ':F' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet5->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet5->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet5->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet5->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet5->getStyle('E' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet5->getStyle('F' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      
      $rank++;
    }
    
    foreach (range('A', 'F') as $col) {
      $sheet5->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 6: TRANSPORT ANALYSIS =====================
    $sheet6 = $spreadsheet->createSheet();
    $sheet6->setTitle('🚚 Transport Analysis');
    $sheet6->getTabColor()->setRGB('1abc9c');
    
    $transportData = $this->getTransportDistribution();
    
    $sheet6->setCellValue('A1', 'TRANSPORT MODE ANALYSIS');
    $sheet6->mergeCells('A1:C1');
    $sheet6->getRowDimension('1')->setRowHeight(35);
    $sheet6->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet6->setCellValue('A' . $row, 'Rank');
    $sheet6->setCellValue('B' . $row, 'Transport Mode');
    $sheet6->setCellValue('C' . $row, 'License Count');
    $sheet6->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
    $sheet6->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($transportData as $index => $transport) {
      $row++;
      $count = isset($transport['license_count']) && !is_null($transport['license_count']) ? (int)$transport['license_count'] : 0;
      
      $sheet6->setCellValue('A' . $row, $rank);
      $sheet6->setCellValue('B' . $row, $transport['transport_name']);
      $sheet6->setCellValue('C' . $row, $count);
      
      $sheet6->getStyle('A' . $row . ':C' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet6->getStyle('A' . $row . ':C' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet6->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet6->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet6->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      
      $rank++;
    }
    
    foreach (range('A', 'C') as $col) {
      $sheet6->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 7: CURRENCY ANALYSIS =====================
    $sheet7 = $spreadsheet->createSheet();
    $sheet7->setTitle('💱 Currency Analysis');
    $sheet7->getTabColor()->setRGB('f39c12');
    
    $currencyData = $this->getCurrencyDistribution();
    
    $sheet7->setCellValue('A1', 'CURRENCY DISTRIBUTION ANALYSIS');
    $sheet7->mergeCells('A1:D1');
    $sheet7->getRowDimension('1')->setRowHeight(35);
    $sheet7->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet7->setCellValue('A' . $row, 'Rank');
    $sheet7->setCellValue('B' . $row, 'Currency');
    $sheet7->setCellValue('C' . $row, 'License Count');
    $sheet7->setCellValue('D' . $row, 'Total FOB Value');
    $sheet7->getStyle('A' . $row . ':D' . $row)->applyFromArray($headerStyle);
    $sheet7->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($currencyData as $index => $currency) {
      $row++;
      $count = isset($currency['license_count']) && !is_null($currency['license_count']) ? (int)$currency['license_count'] : 0;
      $totalFob = isset($currency['total_fob']) && !is_null($currency['total_fob']) ? (float)$currency['total_fob'] : 0;
      
      $sheet7->setCellValue('A' . $row, $rank);
      $sheet7->setCellValue('B' . $row, $currency['currency_name']);
      $sheet7->setCellValue('C' . $row, $count);
      $sheet7->setCellValue('D' . $row, $totalFob);
      
      $sheet7->getStyle('A' . $row . ':D' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet7->getStyle('A' . $row . ':D' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet7->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet7->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet7->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet7->getStyle('D' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      
      $rank++;
    }
    
    foreach (range('A', 'D') as $col) {
      $sheet7->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 8: MONTHLY TREND =====================
    $sheet8 = $spreadsheet->createSheet();
    $sheet8->setTitle('📈 Monthly Trend');
    $sheet8->getTabColor()->setRGB('e74c3c');
    
    $trendData = $this->getMonthlyTrend();
    
    $sheet8->setCellValue('A1', 'MONTHLY LICENSE TREND ANALYSIS');
    $sheet8->mergeCells('A1:E1');
    $sheet8->getRowDimension('1')->setRowHeight(35);
    $sheet8->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet8->setCellValue('A' . $row, 'Month');
    $sheet8->setCellValue('B' . $row, 'License Count');
    $sheet8->setCellValue('C' . $row, 'Total FOB Value');
    $sheet8->setCellValue('D' . $row, 'Total Weight (KG)');
    $sheet8->setCellValue('E' . $row, 'Avg FOB/License');
    $sheet8->getStyle('A' . $row . ':E' . $row)->applyFromArray($headerStyle);
    $sheet8->getRowDimension($row)->setRowHeight(25);
    
    foreach ($trendData as $index => $month) {
      $row++;
      $count = isset($month['license_count']) && !is_null($month['license_count']) ? (int)$month['license_count'] : 0;
      $fob = isset($month['total_fob_value']) && !is_null($month['total_fob_value']) ? (float)$month['total_fob_value'] : 0;
      $weight = isset($month['total_weight']) && !is_null($month['total_weight']) ? (float)$month['total_weight'] : 0;
      $avgFob = $count > 0 ? $fob / $count : 0;
      
      $sheet8->setCellValue('A' . $row, $month['month_name']);
      $sheet8->setCellValue('B' . $row, $count);
      $sheet8->setCellValue('C' . $row, $fob);
      $sheet8->setCellValue('D' . $row, $weight);
      $sheet8->setCellValue('E' . $row, $avgFob);
      
      $sheet8->getStyle('A' . $row . ':E' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet8->getStyle('A' . $row . ':E' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet8->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet8->getStyle('B' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet8->getStyle('C' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet8->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet8->getStyle('E' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
    }
    
    foreach (range('A', 'E') as $col) {
      $sheet8->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 9: EXPIRY ANALYSIS =====================
    $sheet9 = $spreadsheet->createSheet();
    $sheet9->setTitle('⏰ Expiry Analysis');
    $sheet9->getTabColor()->setRGB('fa5c7c');
    
    $expiryDetails = $this->getExpiryDetails();
    
    $sheet9->setCellValue('A1', 'COMPREHENSIVE EXPIRY ANALYSIS');
    $sheet9->mergeCells('A1:I1');
    $sheet9->getRowDimension('1')->setRowHeight(35);
    $sheet9->getStyle('A1')->applyFromArray($titleStyle);
    
    $sheet9->setCellValue('A2', 'Total Records: ' . count($expiryDetails) . ' | Generated: ' . date('Y-m-d H:i:s'));
    $sheet9->mergeCells('A2:I2');
    $sheet9->getStyle('A2')->getFont()->setItalic(true)->setBold(true);
    $sheet9->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    $row = 4;
    $headers = ['License #', 'Client', 'Bank', 'FOB Value', 'Applied Date', 'Expiry Date', 'Days to Expiry', 'Category', 'Status'];
    $col = 'A';
    foreach ($headers as $header) {
      $sheet9->setCellValue($col . $row, $header);
      $col++;
    }
    $sheet9->getStyle('A' . $row . ':I' . $row)->applyFromArray($headerStyle);
    $sheet9->getRowDimension($row)->setRowHeight(25);
    
    foreach ($expiryDetails as $index => $license) {
      $row++;
      $sheet9->setCellValue('A' . $row, $license['license_number']);
      $sheet9->setCellValue('B' . $row, $license['client_name']);
      $sheet9->setCellValue('C' . $row, $license['bank_name']);
      
      $fob = isset($license['fob_declared']) && !is_null($license['fob_declared']) ? (float)$license['fob_declared'] : 0;
      $sheet9->setCellValue('D' . $row, $fob);
      
      $sheet9->setCellValue('E' . $row, $license['license_applied_date']);
      $sheet9->setCellValue('F' . $row, $license['license_expiry_date']);
      
      $days = isset($license['days_to_expiry']) ? $license['days_to_expiry'] : 0;
      $sheet9->setCellValue('G' . $row, $days);
      $sheet9->setCellValue('H' . $row, $license['expiry_category']);
      $sheet9->setCellValue('I' . $row, $license['status']);
      
      $sheet9->getStyle('A' . $row . ':I' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet9->getStyle('A' . $row . ':I' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet9->getStyle('D' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet9->getStyle('G' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      
      if ($days < 0) {
        $sheet9->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet9->getStyle('G' . $row)->getFont()->getColor()->setRGB('742A2A');
      } elseif ($days <= 7) {
        $sheet9->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet9->getStyle('G' . $row)->getFont()->getColor()->setRGB('742A2A');
      } elseif ($days <= 15) {
        $sheet9->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FBD38D');
        $sheet9->getStyle('G' . $row)->getFont()->getColor()->setRGB('744210');
      } elseif ($days <= 30) {
        $sheet9->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('BEE3F8');
        $sheet9->getStyle('G' . $row)->getFont()->getColor()->setRGB('2C5282');
      } else {
        $sheet9->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6F6D5');
        $sheet9->getStyle('G' . $row)->getFont()->getColor()->setRGB('22543D');
      }
      
      $category = $license['expiry_category'];
      if ($category === 'Expired' || $category === 'Expiring in 7 Days') {
        $sheet9->getStyle('H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet9->getStyle('H' . $row)->getFont()->getColor()->setRGB('742A2A');
      } elseif ($category === 'Expiring in 15 Days') {
        $sheet9->getStyle('H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FBD38D');
        $sheet9->getStyle('H' . $row)->getFont()->getColor()->setRGB('744210');
      } elseif ($category === 'Expiring in 30 Days') {
        $sheet9->getStyle('H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('BEE3F8');
        $sheet9->getStyle('H' . $row)->getFont()->getColor()->setRGB('2C5282');
      } else {
        $sheet9->getStyle('H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6F6D5');
        $sheet9->getStyle('H' . $row)->getFont()->getColor()->setRGB('22543D');
      }
      
      $status = $license['status'];
      if ($status === 'ACTIVE') {
        $sheet9->getStyle('I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6F6D5');
        $sheet9->getStyle('I' . $row)->getFont()->getColor()->setRGB('22543D');
      } elseif ($status === 'EXPIRED') {
        $sheet9->getStyle('I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet9->getStyle('I' . $row)->getFont()->getColor()->setRGB('742A2A');
      }
    }
    
    foreach (range('A', 'I') as $col) {
      $sheet9->getColumnDimension($col)->setAutoSize(true);
    }
    
    $sheet9->freezePane('A5');

    // ===================== SHEET 10: CLIENT ANALYSIS =====================
    $sheet10 = $spreadsheet->createSheet();
    $sheet10->setTitle('👥 Client Analysis');
    $sheet10->getTabColor()->setRGB('9b59b6');
    
    $clientDetails = $this->getClientDetails();
    
    $sheet10->setCellValue('A1', 'CLIENT PERFORMANCE ANALYSIS');
    $sheet10->mergeCells('A1:H1');
    $sheet10->getRowDimension('1')->setRowHeight(35);
    $sheet10->getStyle('A1')->applyFromArray($titleStyle);
    
    $sheet10->setCellValue('A2', 'Total Clients: ' . count($clientDetails) . ' | Generated: ' . date('Y-m-d H:i:s'));
    $sheet10->mergeCells('A2:H2');
    $sheet10->getStyle('A2')->getFont()->setItalic(true)->setBold(true);
    $sheet10->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    $row = 4;
    $headers = ['Client Code', 'Company Name', 'Total Licenses', 'Active Licenses', 'Success Rate', 'Total FOB Value', 'Total Weight', 'Last License'];
    $col = 'A';
    foreach ($headers as $header) {
      $sheet10->setCellValue($col . $row, $header);
      $col++;
    }
    $sheet10->getStyle('A' . $row . ':H' . $row)->applyFromArray($headerStyle);
    $sheet10->getRowDimension($row)->setRowHeight(25);
    
    foreach ($clientDetails as $index => $client) {
      $row++;
      $sheet10->setCellValue('A' . $row, $client['short_name']);
      $sheet10->setCellValue('B' . $row, $client['company_name']);
      
      $totalLicenses = isset($client['total_licenses']) ? $client['total_licenses'] : 0;
      $activeLicenses = isset($client['active_licenses']) ? $client['active_licenses'] : 0;
      $successRate = isset($client['success_rate']) ? $client['success_rate'] : 0;
      $totalFob = isset($client['total_fob_value']) && !is_null($client['total_fob_value']) ? (float)$client['total_fob_value'] : 0;
      $totalWeight = isset($client['total_weight']) && !is_null($client['total_weight']) ? (float)$client['total_weight'] : 0;
      
      $sheet10->setCellValue('C' . $row, $totalLicenses);
      $sheet10->setCellValue('D' . $row, $activeLicenses);
      $sheet10->setCellValue('E' . $row, $successRate / 100);
      $sheet10->setCellValue('F' . $row, $totalFob);
      $sheet10->setCellValue('G' . $row, $totalWeight);
      $sheet10->setCellValue('H' . $row, $client['last_license_date']);
      
      $sheet10->getStyle('A' . $row . ':H' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet10->getStyle('A' . $row . ':H' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet10->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet10->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet10->getStyle('D' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      $sheet10->getStyle('E' . $row)->getNumberFormat()->setFormatCode($percentFormat);
      $sheet10->getStyle('F' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
      $sheet10->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      
      if ($successRate >= 75) {
        $sheet10->getStyle('E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('C6F6D5');
        $sheet10->getStyle('E' . $row)->getFont()->getColor()->setRGB('22543D');
      } elseif ($successRate >= 50) {
        $sheet10->getStyle('E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FBD38D');
        $sheet10->getStyle('E' . $row)->getFont()->getColor()->setRGB('744210');
      } else {
        $sheet10->getStyle('E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7D7');
        $sheet10->getStyle('E' . $row)->getFont()->getColor()->setRGB('742A2A');
      }
    }
    
    foreach (range('A', 'H') as $col) {
      $sheet10->getColumnDimension($col)->setAutoSize(true);
    }
    
    $sheet10->freezePane('A5');

    // ===================== SHEET 11: ENTRY POINTS =====================
    $sheet11 = $spreadsheet->createSheet();
    $sheet11->setTitle('📍 Entry Points');
    $sheet11->getTabColor()->setRGB('3498db');
    
    $entryPostData = $this->getEntryPostDistribution();
    
    $sheet11->setCellValue('A1', 'ENTRY POINT DISTRIBUTION');
    $sheet11->mergeCells('A1:C1');
    $sheet11->getRowDimension('1')->setRowHeight(35);
    $sheet11->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet11->setCellValue('A' . $row, 'Rank');
    $sheet11->setCellValue('B' . $row, 'Entry Point Name');
    $sheet11->setCellValue('C' . $row, 'License Count');
    $sheet11->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
    $sheet11->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($entryPostData as $index => $entry) {
      $row++;
      $count = isset($entry['license_count']) && !is_null($entry['license_count']) ? (int)$entry['license_count'] : 0;
      
      $sheet11->setCellValue('A' . $row, $rank);
      $sheet11->setCellValue('B' . $row, $entry['entry_post_name']);
      $sheet11->setCellValue('C' . $row, $count);
      
      $sheet11->getStyle('A' . $row . ':C' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet11->getStyle('A' . $row . ':C' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet11->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet11->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet11->getStyle('C' . $row)->getNumberFormat()->setFormatCode($numberFormat);
      
      $rank++;
    }
    
    foreach (range('A', 'C') as $col) {
      $sheet11->getColumnDimension($col)->setAutoSize(true);
    }

    // ===================== SHEET 12: WEIGHT DISTRIBUTION =====================
    $sheet12 = $spreadsheet->createSheet();
    $sheet12->setTitle('⚖️ Weight Distribution');
    $sheet12->getTabColor()->setRGB('27ae60');
    
    $weightData = $this->getWeightDistribution();
    
    $sheet12->setCellValue('A1', 'WEIGHT DISTRIBUTION BY GOODS TYPE');
    $sheet12->mergeCells('A1:C1');
    $sheet12->getRowDimension('1')->setRowHeight(35);
    $sheet12->getStyle('A1')->applyFromArray($titleStyle);
    
    $row = 3;
    $sheet12->setCellValue('A' . $row, 'Rank');
    $sheet12->setCellValue('B' . $row, 'Goods Type');
    $sheet12->setCellValue('C' . $row, 'Total Weight (KG)');
    $sheet12->getStyle('A' . $row . ':C' . $row)->applyFromArray($headerStyle);
    $sheet12->getRowDimension($row)->setRowHeight(25);
    
    $rank = 1;
    foreach ($weightData as $index => $weight) {
      $row++;
      $totalWeight = isset($weight['total_weight']) && !is_null($weight['total_weight']) ? (float)$weight['total_weight'] : 0;
      
      $sheet12->setCellValue('A' . $row, $rank);
      $sheet12->setCellValue('B' . $row, $weight['goods_name']);
      $sheet12->setCellValue('C' . $row, $totalWeight);
      
      $sheet12->getStyle('A' . $row . ':C' . $row)->applyFromArray($dataCellStyle);
      
      if ($index % 2 == 0) {
        $sheet12->getStyle('A' . $row . ':C' . $row)->getFill()
          ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
          ->getStartColor()->setRGB('F7FAFC');
      }
      
      $sheet12->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet12->getStyle('A' . $row)->getFont()->setBold(true);
      $sheet12->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      
      $rank++;
    }
    
    foreach (range('A', 'C') as $col) {
      $sheet12->getColumnDimension($col)->setAutoSize(true);
    }

    // Set active sheet to first
    $spreadsheet->setActiveSheetIndex(0);
    
    $filename = 'License_Dashboard_Premium_Export_' . date('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    exit;
    
  } catch (Exception $e) {
    error_log("Export Dashboard Error: " . $e->getMessage());
    die('Export failed: ' . $e->getMessage());
  }
}

  public function exportClientData()
  {
    try {
      if (!$this->loadPhpSpreadsheet()) {
        die('PhpSpreadsheet library not found');
      }

      if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        die('PhpSpreadsheet class not available');
      }

      $client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT);
      
      if (!$client_id || $client_id <= 0) {
        die('Invalid client ID');
      }
      
      $sql = "SELECT 
                c.id,
                c.short_name,
                c.company_name,
                c.client_type,
                c.display,
                c.contact_person,
                c.email,
                c.payment_term,
                COUNT(l.id) as total_licenses,
                SUM(CASE WHEN l.status = 'ACTIVE' THEN 1 ELSE 0 END) as active_licenses,
                COALESCE(SUM(l.fob_declared), 0) as total_fob_value,
                COALESCE(SUM(l.weight), 0) as total_weight,
                CASE 
                  WHEN COUNT(l.id) > 0 THEN ROUND((SUM(CASE WHEN l.status = 'ACTIVE' THEN 1 ELSE 0 END) / COUNT(l.id)) * 100, 0)
                  ELSE 0 
                END as success_rate
              FROM clients_t c
              LEFT JOIN licenses_t l ON c.id = l.client_id AND l.display = 'Y'
              WHERE c.id = ?
              GROUP BY c.id";
      
      $result = $this->db->customQuery($sql, [$client_id]);
      
      if (empty($result)) {
        die('Client not found');
      }
      
      $client = $result[0];
      
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      
      $sheet->setCellValue('A1', 'CLIENT REPORT - ' . $client['short_name']);
      $sheet->mergeCells('A1:B1');
      $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
      $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('667eea');
      $sheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
      
      $sheet->setCellValue('A2', 'Generated:');
      $sheet->setCellValue('B2', date('Y-m-d H:i:s'));
      
      $row = 4;
      $clientInfo = [
        'Client Code' => 'short_name',
        'Company Name' => 'company_name',
        'Contact Person' => 'contact_person',
        'Email' => 'email',
        'Payment Term' => 'payment_term',
        'Total Licenses' => 'total_licenses',
        'Active Licenses' => 'active_licenses',
        'Success Rate' => 'success_rate',
        'Total FOB Value' => 'total_fob_value',
        'Total Weight (KG)' => 'total_weight',
        'Status' => 'display'
      ];
      
      foreach ($clientInfo as $label => $key) {
        $sheet->setCellValue('A' . $row, $label . ':');
        $value = $client[$key] ?? 'N/A';
        
        if ($key === 'total_fob_value') {
          $value = '$' . number_format((float)$value, 2);
        } elseif ($key === 'total_weight') {
          $value = number_format((float)$value, 2);
        } elseif ($key === 'success_rate') {
          $value = $value . '%';
        } elseif ($key === 'display') {
          $value = $value === 'Y' ? 'Active' : 'Inactive';
        }
        
        $sheet->setCellValue('B' . $row, $value);
        $row++;
      }
      
      $sheet->getColumnDimension('A')->setWidth(25);
      $sheet->getColumnDimension('B')->setWidth(35);
      
      $filename = 'Client_' . $client['short_name'] . '_Report_' . date('Y-m-d') . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      
      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      
      $spreadsheet->disconnectWorksheets();
      unset($spreadsheet);
      exit;
      
    } catch (Exception $e) {
      error_log("Export Client Data Error: " . $e->getMessage());
      die('Export failed: ' . $e->getMessage());
    }
  }
}