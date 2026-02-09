<?php

class ImportDashboardController extends Controller
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
      $_SERVER['DOCUMENT_ROOT'] . '/malabar/vendor/autoload.php',
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
      'title' => 'Import Dashboard',
      
      // Overview Tab Data
      'kpi_data' => $this->getKPIData(),
      'transport_mode_stats' => $this->getTransportModeStats(),
      'clearing_status_summary' => $this->getClearingStatusSummary(),
      'extended_kpi_data' => $this->getExtendedKPIData(),
      'clearing_status_distribution' => $this->getClearingStatusDistribution(),
      'document_status_distribution' => $this->getDocumentStatusDistribution(),
      'kind_distribution' => $this->getKindDistribution(),
      'clearance_type_distribution' => $this->getClearanceTypeDistribution(),
      'monthly_trend' => $this->getMonthlyTrend(),
      'goods_distribution' => $this->getGoodsDistribution(),
      'transport_distribution' => $this->getTransportDistribution(),
      'currency_distribution' => $this->getCurrencyDistribution(),
      'entry_point_distribution' => $this->getEntryPointDistribution(),
      'regime_distribution' => $this->getRegimeDistribution(),
      'timeline_analysis' => $this->getTimelineAnalysis(),
      'recent_imports' => $this->getRecentImports(),
      
      // Logistics Tab Data
      'logistics_overview' => $this->getLogisticsOverview(),
      'tracking_stages' => $this->getTrackingStages(),
      'transport_timeline' => $this->getTransportTimeline(),
      'warehouse_stats' => $this->getWarehouseStats(),
      'border_crossing_stats' => $this->getBorderCrossingStats(),
      'route_analysis' => $this->getRouteAnalysis(),
      'vehicle_stats' => $this->getVehicleStats(),
      'container_stats' => $this->getContainerStats(),
      'logistics_monthly_trend' => $this->getLogisticsMonthlyTrend(),
      
      // Delay KPI Tab Data
      'delay_overview' => $this->getDelayOverview(),
      'stage_delay_distribution' => $this->getStageDelayDistribution(),
      'bottleneck_analysis' => $this->getBottleneckAnalysis(),
      'client_delay_analysis' => $this->getClientDelayAnalysis(),
      'monthly_delay_trend' => $this->getMonthlyDelayTrend(),
      
      // Tri Phase Tab Data
      'triphase_overview' => $this->getTriPhaseOverview(),
      'triphase_monthly_breakdown' => $this->getTriPhaseMonthlyBreakdown(),
      'triphase_current_month' => $this->getTriPhaseCurrentMonth(),
      'triphase_by_client' => $this->getTriPhaseByClient(),
      'triphase_trend' => $this->getTriPhaseTrend(),
      
      // Location Based Tab Data
      'location_overview' => $this->getLocationOverview(),
      'declaration_office_analysis' => $this->getDeclarationOfficeAnalysis(),
      'office_performance' => $this->getOfficePerformance(),
      'office_monthly_trend' => $this->getOfficeMonthlyTrend(),
      'office_clearance_times' => $this->getOfficeClearanceTimes(),
      'sub_office_breakdown' => $this->getSubOfficeBreakdown(),
      'main_office_list' => $this->getMainOfficeList(),
      
      // Client Based Tab Data
      'client_stats' => $this->getClientStats(),
      'client_details' => $this->getClientDetails()
    ];

    $this->viewWithLayout('tracking/importdashboard', $data);
  }

  // ==================== OVERVIEW TAB METHODS ====================

  private function getKPIData()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_imports,
                SUM(CASE WHEN clearing_status = 5 THEN 1 ELSE 0 END) as in_progress_imports,
                SUM(CASE WHEN clearing_status = 6 THEN 1 ELSE 0 END) as clearing_completed,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_imports,
                SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) as this_week_imports,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_imports,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_year_imports
              FROM imports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("KPI Data Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTransportModeStats()
  {
    try {
      $sql = "SELECT 
                COALESCE(tm.transport_mode_name, 'Not Specified') as transport_name,
                tm.transport_letter,
                COUNT(i.id) as import_count,
                SUM(CASE WHEN i.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_count,
                SUM(CASE WHEN i.clearing_status = 5 THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN i.clearing_status = 4 THEN 1 ELSE 0 END) as in_transit_count
              FROM imports_t i
              LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id AND tm.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.transport_mode, tm.transport_mode_name, tm.transport_letter
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Transport Mode Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClearingStatusSummary()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN clearing_status = 4 THEN 1 END) as in_transit,
                COUNT(CASE WHEN clearing_status = 5 THEN 1 END) as in_progress,
                COUNT(CASE WHEN clearing_status = 6 THEN 1 END) as clearing_completed,
                COUNT(CASE WHEN clearing_status = 7 THEN 1 END) as cancelled,
                COUNT(CASE WHEN clearing_status = 8 THEN 1 END) as cleared_with_ir,
                COUNT(CASE WHEN clearing_status = 9 THEN 1 END) as cleared_with_ara
              FROM imports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Clearing Status Summary Error: " . $e->getMessage());
      return [];
    }
  }

  private function getExtendedKPIData()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT mca_ref) as unique_mca,
                COUNT(DISTINCT invoice) as unique_invoices,
                SUM(CASE WHEN document_status = 8 THEN 1 ELSE 0 END) as ready_to_declare,
                SUM(CASE WHEN document_status IN (1,2,3,4,5) THEN 1 ELSE 0 END) as pending_validation,
                COALESCE(AVG(DATEDIFF(dgda_out_date, dgda_in_date)), 0) as avg_customs_days,
                COUNT(CASE WHEN inspection_reports IS NOT NULL AND inspection_reports != '' THEN 1 END) as inspections_filed,
                COUNT(CASE WHEN archive_reference IS NOT NULL AND archive_reference != '' THEN 1 END) as archived_count,
                SUM(CASE WHEN audited_date IS NOT NULL THEN 1 ELSE 0 END) as audited_count
              FROM imports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Extended KPI Data Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClearingStatusDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(cs.clearing_status, 'Not Specified') as status_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id AND cs.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.clearing_status, cs.clearing_status
              HAVING COUNT(i.id) > 0
              ORDER BY i.clearing_status ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Clearing Status Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getDocumentStatusDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(ds.document_status, 'Not Specified') as status_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN document_status_master_t ds ON i.document_status = ds.id AND ds.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.document_status, ds.document_status
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC
              LIMIT 10";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Document Status Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getKindDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(k.kind_name, 'Not Specified') as kind_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN kind_master_t k ON i.kind = k.id AND k.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.kind, k.kind_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Kind Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClearanceTypeDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(ct.clearance_name, 'Not Specified') as clearance_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN clearance_master_t ct ON i.types_of_clearance = ct.id AND ct.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.types_of_clearance, ct.clearance_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Clearance Type Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(id) as import_count
              FROM imports_t
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
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN type_of_goods_master_t tg ON i.type_of_goods = tg.id AND tg.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.type_of_goods, tg.goods_type
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC
              LIMIT 10";
      
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
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id AND tm.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.transport_mode, tm.transport_mode_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
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
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN currency_master_t cur ON i.currency = cur.id AND cur.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.currency, cur.currency_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Currency Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getEntryPointDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as entry_point_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN transit_point_master_t tp ON i.entry_point_id = tp.id 
                AND tp.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.entry_point_id, tp.transit_point_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC
              LIMIT 10";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Entry Point Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRegimeDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(r.regime_name, 'Not Specified') as regime_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN regime_master_t r ON i.regime = r.id AND r.display = 'Y'
              WHERE i.display = 'Y'
              GROUP BY i.regime, r.regime_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Regime Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTimelineAnalysis()
  {
    try {
      $sql = "SELECT 
                AVG(DATEDIFF(dgda_in_date, pre_alert_date)) as avg_days_to_customs,
                AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_days_in_customs,
                AVG(DATEDIFF(warehouse_arrival_date, dgda_out_date)) as avg_days_to_warehouse,
                AVG(DATEDIFF(warehouse_arrival_date, pre_alert_date)) as avg_total_cycle_time
              FROM imports_t
              WHERE display = 'Y'
                AND pre_alert_date IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [
        'avg_days_to_customs' => 0,
        'avg_days_in_customs' => 0,
        'avg_days_to_warehouse' => 0,
        'avg_total_cycle_time' => 0
      ];
    } catch (Exception $e) {
      error_log("Timeline Analysis Error: " . $e->getMessage());
      return [
        'avg_days_to_customs' => 0,
        'avg_days_in_customs' => 0,
        'avg_days_to_warehouse' => 0,
        'avg_total_cycle_time' => 0
      ];
    }
  }

  private function getRecentImports()
  {
    try {
      $sql = "SELECT 
                i.id,
                i.mca_ref,
                i.invoice,
                i.pre_alert_date,
                i.dgda_in_date,
                i.dgda_out_date,
                i.clearing_status,
                i.created_at,
                COALESCE(c.company_name, 'N/A') as client_name,
                COALESCE(l.license_number, 'N/A') as license_number,
                COALESCE(k.kind_name, 'N/A') as kind_name,
                COALESCE(cs.clearing_status, 'Pending') as clearing_status_name
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN licenses_t l ON i.license_id = l.id AND l.display = 'Y'
              LEFT JOIN kind_master_t k ON i.kind = k.id AND k.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id AND cs.display = 'Y'
              WHERE i.display = 'Y'
              ORDER BY i.created_at DESC
              LIMIT 20";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Recent Imports Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== LOGISTICS TAB METHODS ====================

  private function getLogisticsOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN clearing_status IN (4, 5) THEN 1 END) as in_transit,
                COUNT(CASE WHEN arrival_date_zambia IS NOT NULL THEN 1 END) as zambia_arrivals,
                COUNT(CASE WHEN drc_entry_date IS NOT NULL THEN 1 END) as drc_entries,
                COUNT(CASE WHEN border_warehouse_arrival_date IS NOT NULL THEN 1 END) as border_warehouse_arrivals,
                COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL THEN 1 END) as kanyaka_arrivals,
                COUNT(CASE WHEN warehouse_arrival_date IS NOT NULL THEN 1 END) as warehouse_arrivals,
                COUNT(CASE WHEN container IS NOT NULL AND container != '' THEN 1 END) as container_shipments,
                COUNT(CASE WHEN transport_mode = 1 THEN 1 END) as road_shipments,
                COUNT(CASE WHEN wagon IS NOT NULL AND wagon != '' THEN 1 END) as rail_shipments,
                COUNT(CASE WHEN airway_bill IS NOT NULL AND airway_bill != '' THEN 1 END) as air_shipments,
                AVG(DATEDIFF(warehouse_arrival_date, pre_alert_date)) as avg_total_transit_time
              FROM imports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Logistics Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTrackingStages()
  {
    try {
      // Diagnostic check
      $checkSql = "SELECT 
                     COUNT(*) as total_road,
                     COUNT(CASE WHEN arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00' THEN 1 END) as has_zambia,
                     COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00' THEN 1 END) as has_kanyaka,
                     COUNT(CASE WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00' THEN 1 END) as has_warehouse
                   FROM imports_t 
                   WHERE display = 'Y' AND transport_mode = 1";
      
      $checkResult = $this->db->customQuery($checkSql);
      error_log("=== DIAGNOSTIC CHECK ===");
      error_log(print_r($checkResult[0], true));
      
      // CORRECT APPROACH: Each shipment goes into exactly ONE stage (furthest progress)
      $sql = "SELECT 
                COUNT(*) as total_road_shipments,
                
                -- Each CASE condition represents the FURTHEST stage reached
                -- Stages are checked in REVERSE order (completed first, then backwards)
                
                SUM(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as completed_road_journey,
                
                SUM(CASE 
                  WHEN (warehouse_arrival_date IS NULL OR warehouse_arrival_date = '0000-00-00')
                  AND kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_final_warehouse,
                
                SUM(CASE 
                  WHEN (kanyaka_dispatch_date IS NULL OR kanyaka_dispatch_date = '0000-00-00')
                  AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_kanyaka_dispatch,
                
                SUM(CASE 
                  WHEN (kanyaka_arrival_date IS NULL OR kanyaka_arrival_date = '0000-00-00')
                  AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_kanyaka_arrival,
                
                SUM(CASE 
                  WHEN (dispatch_from_border IS NULL OR dispatch_from_border = '0000-00-00')
                  AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_border_dispatch,
                
                SUM(CASE 
                  WHEN (border_warehouse_arrival_date IS NULL OR border_warehouse_arrival_date = '0000-00-00')
                  AND drc_entry_date IS NOT NULL AND drc_entry_date != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_border_warehouse,
                
                SUM(CASE 
                  WHEN (drc_entry_date IS NULL OR drc_entry_date = '0000-00-00')
                  AND dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_drc_entry,
                
                SUM(CASE 
                  WHEN (dispatch_from_zambia IS NULL OR dispatch_from_zambia = '0000-00-00')
                  AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_dispatch_zambia,
                
                SUM(CASE 
                  WHEN arrival_date_zambia IS NULL OR arrival_date_zambia = '0000-00-00'
                  THEN 1 ELSE 0 
                END) as waiting_arrival_zambia
                
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1";
      
      $result = $this->db->customQuery($sql);
      
      if (!empty($result) && is_array($result)) {
        $data = $result[0];
        
        error_log("=== TRACKING STAGES (MUTUALLY EXCLUSIVE) ===");
        error_log("Total Road Shipments: " . ($data['total_road_shipments'] ?? 0));
        error_log("Waiting Arrival Zambia: " . ($data['waiting_arrival_zambia'] ?? 0));
        error_log("Waiting Dispatch Zambia: " . ($data['waiting_dispatch_zambia'] ?? 0));
        error_log("Waiting DRC Entry: " . ($data['waiting_drc_entry'] ?? 0));
        error_log("Waiting Border Warehouse: " . ($data['waiting_border_warehouse'] ?? 0));
        error_log("Waiting Border Dispatch: " . ($data['waiting_border_dispatch'] ?? 0));
        error_log("Waiting Kanyaka Arrival: " . ($data['waiting_kanyaka_arrival'] ?? 0));
        error_log("Waiting Kanyaka Dispatch: " . ($data['waiting_kanyaka_dispatch'] ?? 0));
        error_log("Waiting Final Warehouse: " . ($data['waiting_final_warehouse'] ?? 0));
        error_log("Completed: " . ($data['completed_road_journey'] ?? 0));
        
        // Verification: Sum should equal total
        $sum = ($data['waiting_arrival_zambia'] ?? 0) +
               ($data['waiting_dispatch_zambia'] ?? 0) +
               ($data['waiting_drc_entry'] ?? 0) +
               ($data['waiting_border_warehouse'] ?? 0) +
               ($data['waiting_border_dispatch'] ?? 0) +
               ($data['waiting_kanyaka_arrival'] ?? 0) +
               ($data['waiting_kanyaka_dispatch'] ?? 0) +
               ($data['waiting_final_warehouse'] ?? 0) +
               ($data['completed_road_journey'] ?? 0);
        
        error_log("Sum of all stages: " . $sum);
        error_log("Should equal total: " . ($data['total_road_shipments'] ?? 0));
        error_log("Match: " . ($sum === (int)($data['total_road_shipments'] ?? 0) ? 'YES ✓' : 'NO ✗'));
        error_log("============================================");
        
        return $data;
      }
      
      return $this->getDefaultTrackingStages();
      
    } catch (Exception $e) {
      error_log("Tracking Stages Error: " . $e->getMessage());
      return $this->getDefaultTrackingStages();
    }
  }

  private function getDefaultTrackingStages()
  {
    return [
      'total_road_shipments' => 0,
      'waiting_arrival_zambia' => 0,
      'waiting_dispatch_zambia' => 0,
      'waiting_drc_entry' => 0,
      'waiting_border_warehouse' => 0,
      'waiting_border_dispatch' => 0,
      'waiting_kanyaka_arrival' => 0,
      'waiting_kanyaka_dispatch' => 0,
      'waiting_final_warehouse' => 0,
      'completed_road_journey' => 0
    ];
  }

  private function getTransportTimeline()
  {
    try {
      $sql = "SELECT 
                AVG(DATEDIFF(arrival_date_zambia, pre_alert_date)) as avg_to_zambia,
                AVG(DATEDIFF(drc_entry_date, arrival_date_zambia)) as avg_zambia_to_drc,
                AVG(DATEDIFF(border_warehouse_arrival_date, drc_entry_date)) as avg_to_border_warehouse,
                AVG(DATEDIFF(kanyaka_arrival_date, border_warehouse_arrival_date)) as avg_to_kanyaka,
                AVG(DATEDIFF(warehouse_arrival_date, kanyaka_arrival_date)) as avg_to_warehouse
              FROM imports_t
              WHERE display = 'Y'
                AND transport_mode = 1
                AND pre_alert_date IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Transport Timeline Error: " . $e->getMessage());
      return [];
    }
  }

  private function getWarehouseStats()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as warehouse_name,
                COUNT(i.id) as shipment_count,
                AVG(DATEDIFF(i.warehouse_departure_date, i.warehouse_arrival_date)) as avg_storage_days
              FROM imports_t i
              LEFT JOIN transit_point_master_t tp ON i.bonded_warehouse_id = tp.id AND tp.display = 'Y'
              WHERE i.display = 'Y' AND i.warehouse_arrival_date IS NOT NULL
              GROUP BY i.bonded_warehouse_id, tp.transit_point_name
              HAVING COUNT(i.id) > 0
              ORDER BY shipment_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Warehouse Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBorderCrossingStats()
  {
    try {
      $sql = "SELECT 
                COALESCE(bw.transit_point_name, 'Not Specified') as border_warehouse_name,
                COUNT(i.id) as crossing_count,
                AVG(DATEDIFF(i.dispatch_from_border, i.border_warehouse_arrival_date)) as avg_processing_days
              FROM imports_t i
              LEFT JOIN transit_point_master_t bw ON i.border_warehouse_id = bw.id AND bw.display = 'Y'
              WHERE i.display = 'Y' AND i.border_warehouse_arrival_date IS NOT NULL
              GROUP BY i.border_warehouse_id, bw.transit_point_name
              HAVING COUNT(i.id) > 0
              ORDER BY crossing_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Border Crossing Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRouteAnalysis()
  {
    try {
      $sql = "SELECT 
                'Zambia Route' as route_name,
                COUNT(id) as shipment_count,
                AVG(DATEDIFF(drc_entry_date, arrival_date_zambia)) as avg_transit_days
              FROM imports_t
              WHERE display = 'Y' AND arrival_date_zambia IS NOT NULL
              UNION ALL
              SELECT 
                'Direct DRC Entry' as route_name,
                COUNT(id) as shipment_count,
                AVG(DATEDIFF(border_warehouse_arrival_date, drc_entry_date)) as avg_transit_days
              FROM imports_t
              WHERE display = 'Y' AND drc_entry_date IS NOT NULL AND arrival_date_zambia IS NULL
              UNION ALL
              SELECT 
                'Kanyaka Route' as route_name,
                COUNT(id) as shipment_count,
                AVG(DATEDIFF(warehouse_arrival_date, kanyaka_arrival_date)) as avg_transit_days
              FROM imports_t
              WHERE display = 'Y' AND kanyaka_arrival_date IS NOT NULL";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Route Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getVehicleStats()
  {
    try {
      $sql = "SELECT 
                'Road Transport' as vehicle_type,
                COUNT(id) as shipment_count,
                COUNT(DISTINCT CONCAT(COALESCE(horse, ''), '-', COALESCE(trailer_1, ''))) as unique_vehicles
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
              UNION ALL
              SELECT 
                'Rail (Wagon)' as vehicle_type,
                COUNT(id) as shipment_count,
                COUNT(DISTINCT wagon) as unique_vehicles
              FROM imports_t
              WHERE display = 'Y' AND wagon IS NOT NULL AND wagon != ''
              UNION ALL
              SELECT 
                'Air (Flight)' as vehicle_type,
                COUNT(id) as shipment_count,
                COUNT(DISTINCT airway_bill) as unique_vehicles
              FROM imports_t
              WHERE display = 'Y' AND airway_bill IS NOT NULL AND airway_bill != ''";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Vehicle Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getContainerStats()
  {
    try {
      $sql = "SELECT 
                container,
                COUNT(id) as shipment_count
              FROM imports_t
              WHERE display = 'Y' 
                AND container IS NOT NULL 
                AND container != ''
              GROUP BY container
              ORDER BY shipment_count DESC
              LIMIT 20";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Container Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLogisticsMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(CASE WHEN arrival_date_zambia IS NOT NULL THEN 1 END) as zambia_route,
                COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL THEN 1 END) as kanyaka_route,
                COUNT(CASE WHEN warehouse_arrival_date IS NOT NULL THEN 1 END) as warehouse_arrivals
              FROM imports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Logistics Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== DELAY KPI TAB METHODS ====================

  private function getDelayOverview()
  {
    try {
      $sql = "SELECT 
                -- Overall Statistics
                COUNT(*) as total_road_shipments,
                COUNT(CASE WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00' THEN 1 END) as completed_shipments,
                
                -- Average Days at Each Stage (only for completed stages)
                AVG(CASE 
                  WHEN arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  THEN DATEDIFF(arrival_date_zambia, pre_alert_date) 
                END) as avg_days_to_zambia,
                
                AVG(CASE 
                  WHEN dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
                  AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  THEN DATEDIFF(dispatch_from_zambia, arrival_date_zambia) 
                END) as avg_days_at_zambia,
                
                AVG(CASE 
                  WHEN drc_entry_date IS NOT NULL AND drc_entry_date != '0000-00-00'
                  AND dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
                  THEN DATEDIFF(drc_entry_date, dispatch_from_zambia) 
                END) as avg_days_zambia_to_drc,
                
                AVG(CASE 
                  WHEN border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                  AND drc_entry_date IS NOT NULL AND drc_entry_date != '0000-00-00'
                  THEN DATEDIFF(border_warehouse_arrival_date, drc_entry_date) 
                END) as avg_days_drc_to_border_wh,
                
                AVG(CASE 
                  WHEN dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                  AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) 
                END) as avg_days_at_border_wh,
                
                AVG(CASE 
                  WHEN kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                  AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                  THEN DATEDIFF(kanyaka_arrival_date, dispatch_from_border) 
                END) as avg_days_border_to_kanyaka,
                
                AVG(CASE 
                  WHEN kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
                  AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                  THEN DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date) 
                END) as avg_days_at_kanyaka,
                
                AVG(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  AND kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
                  THEN DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date) 
                END) as avg_days_kanyaka_to_warehouse,
                
                -- Total End-to-End Time (Pre-Alert to Final Warehouse)
                AVG(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(warehouse_arrival_date, pre_alert_date) 
                END) as avg_total_journey_days,
                
                -- Customs Clearance Time (DGDA In to DGDA Out)
                AVG(CASE 
                  WHEN dgda_out_date IS NOT NULL AND dgda_out_date != '0000-00-00'
                  AND dgda_in_date IS NOT NULL AND dgda_in_date != '0000-00-00'
                  THEN DATEDIFF(dgda_out_date, dgda_in_date) 
                END) as avg_customs_clearance_days,
                
                -- Min and Max Journey Times
                MIN(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(warehouse_arrival_date, pre_alert_date) 
                END) as min_journey_days,
                
                MAX(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(warehouse_arrival_date, pre_alert_date) 
                END) as max_journey_days,
                
                -- Delay Counts (Define thresholds for each stage)
                COUNT(CASE 
                  WHEN arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  AND DATEDIFF(arrival_date_zambia, pre_alert_date) > 7 
                  THEN 1 
                END) as delayed_to_zambia,
                
                COUNT(CASE 
                  WHEN dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
                  AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  AND DATEDIFF(dispatch_from_zambia, arrival_date_zambia) > 2 
                  THEN 1 
                END) as delayed_at_zambia,
                
                COUNT(CASE 
                  WHEN dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                  AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                  AND DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) > 3 
                  THEN 1 
                END) as delayed_at_border_wh,
                
                COUNT(CASE 
                  WHEN kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
                  AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                  AND DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date) > 1 
                  THEN 1 
                END) as delayed_at_kanyaka,
                
                COUNT(CASE 
                  WHEN dgda_out_date IS NOT NULL AND dgda_out_date != '0000-00-00'
                  AND dgda_in_date IS NOT NULL AND dgda_in_date != '0000-00-00'
                  AND DATEDIFF(dgda_out_date, dgda_in_date) > 5 
                  THEN 1 
                END) as delayed_customs_clearance
                
              FROM imports_t
              WHERE display = 'Y' 
                AND transport_mode = 1
                AND pre_alert_date IS NOT NULL
                AND pre_alert_date != '0000-00-00'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Delay Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getStageDelayDistribution()
  {
    try {
      $sql = "SELECT 
                'To Zambia' as stage_name,
                'pre_alert → arrival_zambia' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(arrival_date_zambia, pre_alert_date)) as avg_days,
                MIN(DATEDIFF(arrival_date_zambia, pre_alert_date)) as min_days,
                MAX(DATEDIFF(arrival_date_zambia, pre_alert_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(arrival_date_zambia, pre_alert_date) <= 5 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(arrival_date_zambia, pre_alert_date) BETWEEN 6 AND 10 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(arrival_date_zambia, pre_alert_date) > 10 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                AND pre_alert_date IS NOT NULL AND pre_alert_date != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'At Zambia' as stage_name,
                'arrival_zambia → dispatch_zambia' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(dispatch_from_zambia, arrival_date_zambia)) as avg_days,
                MIN(DATEDIFF(dispatch_from_zambia, arrival_date_zambia)) as min_days,
                MAX(DATEDIFF(dispatch_from_zambia, arrival_date_zambia)) as max_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_zambia, arrival_date_zambia) <= 1 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_zambia, arrival_date_zambia) BETWEEN 2 AND 3 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_zambia, arrival_date_zambia) > 3 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
                AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'Zambia to DRC' as stage_name,
                'dispatch_zambia → drc_entry' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(drc_entry_date, dispatch_from_zambia)) as avg_days,
                MIN(DATEDIFF(drc_entry_date, dispatch_from_zambia)) as min_days,
                MAX(DATEDIFF(drc_entry_date, dispatch_from_zambia)) as max_days,
                COUNT(CASE WHEN DATEDIFF(drc_entry_date, dispatch_from_zambia) <= 1 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(drc_entry_date, dispatch_from_zambia) BETWEEN 2 AND 3 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(drc_entry_date, dispatch_from_zambia) > 3 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND drc_entry_date IS NOT NULL AND drc_entry_date != '0000-00-00'
                AND dispatch_from_zambia IS NOT NULL AND dispatch_from_zambia != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'DRC to Border WH' as stage_name,
                'drc_entry → border_wh_arrival' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(border_warehouse_arrival_date, drc_entry_date)) as avg_days,
                MIN(DATEDIFF(border_warehouse_arrival_date, drc_entry_date)) as min_days,
                MAX(DATEDIFF(border_warehouse_arrival_date, drc_entry_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(border_warehouse_arrival_date, drc_entry_date) <= 1 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(border_warehouse_arrival_date, drc_entry_date) BETWEEN 2 AND 3 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(border_warehouse_arrival_date, drc_entry_date) > 3 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                AND drc_entry_date IS NOT NULL AND drc_entry_date != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'At Border WH' as stage_name,
                'border_wh_arrival → border_dispatch' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(dispatch_from_border, border_warehouse_arrival_date)) as avg_days,
                MIN(DATEDIFF(dispatch_from_border, border_warehouse_arrival_date)) as min_days,
                MAX(DATEDIFF(dispatch_from_border, border_warehouse_arrival_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) <= 2 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) BETWEEN 3 AND 5 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) > 5 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'Border to Kanyaka' as stage_name,
                'border_dispatch → kanyaka_arrival' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(kanyaka_arrival_date, dispatch_from_border)) as avg_days,
                MIN(DATEDIFF(kanyaka_arrival_date, dispatch_from_border)) as min_days,
                MAX(DATEDIFF(kanyaka_arrival_date, dispatch_from_border)) as max_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_arrival_date, dispatch_from_border) <= 2 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_arrival_date, dispatch_from_border) BETWEEN 3 AND 5 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_arrival_date, dispatch_from_border) > 5 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'At Kanyaka' as stage_name,
                'kanyaka_arrival → kanyaka_dispatch' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date)) as avg_days,
                MIN(DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date)) as min_days,
                MAX(DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date) <= 1 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date) BETWEEN 2 AND 3 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(kanyaka_dispatch_date, kanyaka_arrival_date) > 3 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
                AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'Kanyaka to Final WH' as stage_name,
                'kanyaka_dispatch → warehouse_arrival' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date)) as avg_days,
                MIN(DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date)) as min_days,
                MAX(DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date) <= 2 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date) BETWEEN 3 AND 5 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(warehouse_arrival_date, kanyaka_dispatch_date) > 5 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                AND kanyaka_dispatch_date IS NOT NULL AND kanyaka_dispatch_date != '0000-00-00'
              
              UNION ALL
              
              SELECT 
                'Customs Clearance' as stage_name,
                'dgda_in → dgda_out' as stage_description,
                COUNT(*) as shipment_count,
                AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_days,
                MIN(DATEDIFF(dgda_out_date, dgda_in_date)) as min_days,
                MAX(DATEDIFF(dgda_out_date, dgda_in_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(dgda_out_date, dgda_in_date) <= 3 THEN 1 END) as within_5_days,
                COUNT(CASE WHEN DATEDIFF(dgda_out_date, dgda_in_date) BETWEEN 4 AND 7 THEN 1 END) as within_10_days,
                COUNT(CASE WHEN DATEDIFF(dgda_out_date, dgda_in_date) > 7 THEN 1 END) as over_10_days
              FROM imports_t
              WHERE display = 'Y' AND transport_mode = 1
                AND dgda_out_date IS NOT NULL AND dgda_out_date != '0000-00-00'
                AND dgda_in_date IS NOT NULL AND dgda_in_date != '0000-00-00'";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Stage Delay Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBottleneckAnalysis()
  {
    try {
      $sql = "SELECT 
                stage_name,
                avg_days,
                shipment_count,
                delayed_count,
                ROUND((delayed_count / shipment_count) * 100, 1) as delay_percentage
              FROM (
                SELECT 
                  'At Border Warehouse' as stage_name,
                  AVG(DATEDIFF(dispatch_from_border, border_warehouse_arrival_date)) as avg_days,
                  COUNT(*) as shipment_count,
                  COUNT(CASE WHEN DATEDIFF(dispatch_from_border, border_warehouse_arrival_date) > 3 THEN 1 END) as delayed_count
                FROM imports_t
                WHERE display = 'Y' AND transport_mode = 1
                  AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
                  AND border_warehouse_arrival_date IS NOT NULL AND border_warehouse_arrival_date != '0000-00-00'
                
                UNION ALL
                
                SELECT 
                  'Customs Clearance' as stage_name,
                  AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_days,
                  COUNT(*) as shipment_count,
                  COUNT(CASE WHEN DATEDIFF(dgda_out_date, dgda_in_date) > 5 THEN 1 END) as delayed_count
                FROM imports_t
                WHERE display = 'Y' AND transport_mode = 1
                  AND dgda_out_date IS NOT NULL AND dgda_out_date != '0000-00-00'
                  AND dgda_in_date IS NOT NULL AND dgda_in_date != '0000-00-00'
                
                UNION ALL
                
                SELECT 
                  'To Zambia Transit' as stage_name,
                  AVG(DATEDIFF(arrival_date_zambia, pre_alert_date)) as avg_days,
                  COUNT(*) as shipment_count,
                  COUNT(CASE WHEN DATEDIFF(arrival_date_zambia, pre_alert_date) > 7 THEN 1 END) as delayed_count
                FROM imports_t
                WHERE display = 'Y' AND transport_mode = 1
                  AND arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  AND pre_alert_date IS NOT NULL AND pre_alert_date != '0000-00-00'
                
                UNION ALL
                
                SELECT 
                  'Border to Kanyaka' as stage_name,
                  AVG(DATEDIFF(kanyaka_arrival_date, dispatch_from_border)) as avg_days,
                  COUNT(*) as shipment_count,
                  COUNT(CASE WHEN DATEDIFF(kanyaka_arrival_date, dispatch_from_border) > 4 THEN 1 END) as delayed_count
                FROM imports_t
                WHERE display = 'Y' AND transport_mode = 1
                  AND kanyaka_arrival_date IS NOT NULL AND kanyaka_arrival_date != '0000-00-00'
                  AND dispatch_from_border IS NOT NULL AND dispatch_from_border != '0000-00-00'
              ) as bottlenecks
              ORDER BY delay_percentage DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Bottleneck Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientDelayAnalysis()
  {
    try {
      $sql = "SELECT 
                COALESCE(c.company_name, 'Unknown') as client_name,
                c.short_name,
                COUNT(i.id) as total_shipments,
                COUNT(CASE WHEN i.warehouse_arrival_date IS NOT NULL AND i.warehouse_arrival_date != '0000-00-00' THEN 1 END) as completed_shipments,
                AVG(CASE 
                  WHEN i.warehouse_arrival_date IS NOT NULL AND i.warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(i.warehouse_arrival_date, i.pre_alert_date) 
                END) as avg_total_days,
                AVG(CASE 
                  WHEN i.dgda_out_date IS NOT NULL AND i.dgda_out_date != '0000-00-00'
                  AND i.dgda_in_date IS NOT NULL AND i.dgda_in_date != '0000-00-00'
                  THEN DATEDIFF(i.dgda_out_date, i.dgda_in_date) 
                END) as avg_customs_days,
                COUNT(CASE 
                  WHEN i.dgda_out_date IS NOT NULL AND i.dgda_out_date != '0000-00-00'
                  AND i.dgda_in_date IS NOT NULL AND i.dgda_in_date != '0000-00-00'
                  AND DATEDIFF(i.dgda_out_date, i.dgda_in_date) > 5 
                  THEN 1 
                END) as delayed_customs_count
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id AND c.display = 'Y'
              WHERE i.display = 'Y' 
                AND i.transport_mode = 1
                AND i.pre_alert_date IS NOT NULL
                AND i.pre_alert_date != '0000-00-00'
              GROUP BY i.subscriber_id, c.company_name, c.short_name
              HAVING COUNT(i.id) >= 5
              ORDER BY avg_total_days DESC
              LIMIT 15";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Delay Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getMonthlyDelayTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(pre_alert_date, '%Y-%m') as month,
                DATE_FORMAT(pre_alert_date, '%b %Y') as month_name,
                COUNT(*) as total_shipments,
                AVG(CASE 
                  WHEN arrival_date_zambia IS NOT NULL AND arrival_date_zambia != '0000-00-00'
                  THEN DATEDIFF(arrival_date_zambia, pre_alert_date) 
                END) as avg_to_zambia,
                AVG(CASE 
                  WHEN dgda_out_date IS NOT NULL AND dgda_out_date != '0000-00-00'
                  AND dgda_in_date IS NOT NULL AND dgda_in_date != '0000-00-00'
                  THEN DATEDIFF(dgda_out_date, dgda_in_date) 
                END) as avg_customs,
                AVG(CASE 
                  WHEN warehouse_arrival_date IS NOT NULL AND warehouse_arrival_date != '0000-00-00'
                  THEN DATEDIFF(warehouse_arrival_date, pre_alert_date) 
                END) as avg_total
              FROM imports_t
              WHERE display = 'Y' 
                AND transport_mode = 1
                AND pre_alert_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND pre_alert_date IS NOT NULL
                AND pre_alert_date != '0000-00-00'
              GROUP BY DATE_FORMAT(pre_alert_date, '%Y-%m'), DATE_FORMAT(pre_alert_date, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Monthly Delay Trend Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== TRI PHASE TAB METHODS ====================

  private function getTriPhaseOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_imports,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1_count,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2_count,
                COUNT(CASE WHEN DAY(created_at) >= 21 THEN 1 END) as phase3_count
              FROM imports_t
              WHERE display = 'Y'
                AND YEAR(created_at) = YEAR(CURDATE())
                AND MONTH(created_at) = MONTH(CURDATE())";
      
      $result = $this->db->customQuery($sql);
      $data = $result[0] ?? [
        'total_imports' => 0,
        'phase1_count' => 0,
        'phase2_count' => 0,
        'phase3_count' => 0
      ];
      
      $data['total_imports'] = (int)($data['total_imports'] ?? 0);
      $data['phase1_count'] = (int)($data['phase1_count'] ?? 0);
      $data['phase2_count'] = (int)($data['phase2_count'] ?? 0);
      $data['phase3_count'] = (int)($data['phase3_count'] ?? 0);
      
      return $data;
    } catch (Exception $e) {
      error_log("Tri Phase Overview Error: " . $e->getMessage());
      return [
        'total_imports' => 0,
        'phase1_count' => 0,
        'phase2_count' => 0,
        'phase3_count' => 0
      ];
    }
  }

  private function getTriPhaseMonthlyBreakdown()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1_count,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2_count,
                COUNT(CASE WHEN DAY(created_at) >= 21 THEN 1 END) as phase3_count,
                COUNT(id) as total
              FROM imports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Tri Phase Monthly Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTriPhaseCurrentMonth()
  {
    try {
      $sql = "SELECT 
                i.id,
                i.mca_ref,
                i.invoice,
                COALESCE(c.company_name, 'N/A') as client_name,
                i.created_at,
                DAY(i.created_at) as day_of_month,
                CASE 
                  WHEN DAY(i.created_at) BETWEEN 1 AND 10 THEN 'Phase 1'
                  WHEN DAY(i.created_at) BETWEEN 11 AND 20 THEN 'Phase 2'
                  ELSE 'Phase 3'
                END as phase_name
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id AND c.display = 'Y'
              WHERE i.display = 'Y'
                AND YEAR(i.created_at) = YEAR(CURDATE())
                AND MONTH(i.created_at) = MONTH(CURDATE())
              ORDER BY i.created_at ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Tri Phase Current Month Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTriPhaseByClient()
  {
    try {
      $sql = "SELECT 
                COALESCE(c.company_name, 'Unknown') as client_name,
                COUNT(i.id) as total_imports,
                COUNT(CASE WHEN DAY(i.created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1_count,
                COUNT(CASE WHEN DAY(i.created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2_count,
                COUNT(CASE WHEN DAY(i.created_at) >= 21 THEN 1 END) as phase3_count
              FROM imports_t i
              LEFT JOIN clients_t c ON i.subscriber_id = c.id AND c.display = 'Y'
              WHERE i.display = 'Y'
                AND i.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
              GROUP BY i.subscriber_id, c.company_name
              HAVING COUNT(i.id) > 0
              ORDER BY total_imports DESC
              LIMIT 15";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Tri Phase By Client Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTriPhaseTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2,
                COUNT(CASE WHEN DAY(created_at) >= 21 THEN 1 END) as phase3
              FROM imports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Tri Phase Trend Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== LOCATION BASED TAB METHODS ====================

  private function getLocationOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT i.declaration_office_id) as unique_offices,
                COUNT(i.id) as total_imports,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_imports,
                AVG(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as avg_clearance_days,
                COUNT(CASE WHEN DATE(i.created_at) = CURDATE() THEN 1 END) as today_imports,
                COUNT(CASE WHEN YEARWEEK(i.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as this_week_imports,
                COUNT(CASE WHEN YEAR(i.created_at) = YEAR(CURDATE()) AND MONTH(i.created_at) = MONTH(CURDATE()) THEN 1 END) as this_month_imports,
                COUNT(CASE WHEN YEAR(i.created_at) = YEAR(CURDATE()) THEN 1 END) as this_year_imports
              FROM imports_t i
              WHERE i.display = 'Y'
                AND i.declaration_office_id IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [
        'unique_offices' => 0,
        'total_imports' => 0,
        'cleared_imports' => 0,
        'avg_clearance_days' => 0,
        'today_imports' => 0,
        'this_week_imports' => 0,
        'this_month_imports' => 0,
        'this_year_imports' => 0
      ];
    } catch (Exception $e) {
      error_log("Location Overview Error: " . $e->getMessage());
      return [
        'unique_offices' => 0,
        'total_imports' => 0,
        'cleared_imports' => 0,
        'avg_clearance_days' => 0,
        'today_imports' => 0,
        'this_week_imports' => 0,
        'this_month_imports' => 0,
        'this_year_imports' => 0
      ];
    }
  }

  private function getDeclarationOfficeAnalysis()
  {
    try {
      $sql = "SELECT 
                i.declaration_office_id,
                COALESCE(mo.main_location_name, 'Not Specified') as office_name,
                COUNT(i.id) as import_count,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_count,
                COUNT(CASE WHEN i.clearing_status = 5 THEN 1 END) as in_progress_count,
                COUNT(CASE WHEN i.clearing_status = 4 THEN 1 END) as in_transit_count,
                AVG(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as avg_clearance_days,
                COUNT(CASE WHEN i.dgda_in_date IS NOT NULL THEN 1 END) as customs_entries,
                COUNT(CASE WHEN DATE(i.created_at) = CURDATE() THEN 1 END) as today_count,
                COUNT(CASE WHEN YEARWEEK(i.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as week_count,
                COUNT(CASE WHEN YEAR(i.created_at) = YEAR(CURDATE()) AND MONTH(i.created_at) = MONTH(CURDATE()) THEN 1 END) as month_count,
                COUNT(CASE WHEN YEAR(i.created_at) = YEAR(CURDATE()) THEN 1 END) as year_count
              FROM imports_t i
              LEFT JOIN main_office_master_t mo ON i.declaration_office_id = mo.id AND mo.display = 'Y'
              WHERE i.display = 'Y'
                AND i.declaration_office_id IS NOT NULL
              GROUP BY i.declaration_office_id, mo.main_location_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Declaration Office Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOfficePerformance()
  {
    try {
      $sql = "SELECT 
                COALESCE(mo.main_location_name, 'Not Specified') as office_name,
                COUNT(i.id) as total_imports,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_imports,
                COUNT(CASE WHEN i.clearing_status = 5 THEN 1 END) as in_progress_imports,
                COUNT(CASE WHEN DATEDIFF(COALESCE(i.dgda_out_date, CURDATE()), i.dgda_in_date) <= 5 THEN 1 END) as fast_clearance,
                COUNT(CASE WHEN DATEDIFF(COALESCE(i.dgda_out_date, CURDATE()), i.dgda_in_date) > 5 THEN 1 END) as delayed_clearance,
                ROUND((COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) / COUNT(i.id)) * 100, 1) as clearance_rate,
                AVG(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as avg_clearance_days
              FROM imports_t i
              LEFT JOIN main_office_master_t mo ON i.declaration_office_id = mo.id AND mo.display = 'Y'
              WHERE i.display = 'Y' AND i.declaration_office_id IS NOT NULL
              GROUP BY i.declaration_office_id, mo.main_location_name
              HAVING COUNT(i.id) > 0
              ORDER BY clearance_rate DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Office Performance Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOfficeMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(i.created_at, '%Y-%m') as month,
                DATE_FORMAT(i.created_at, '%b %Y') as month_name,
                i.declaration_office_id,
                COALESCE(mo.main_location_name, 'Not Specified') as office_name,
                COUNT(i.id) as import_count,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_count
              FROM imports_t i
              LEFT JOIN main_office_master_t mo ON i.declaration_office_id = mo.id AND mo.display = 'Y'
              WHERE i.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND i.display = 'Y'
                AND i.declaration_office_id IS NOT NULL
              GROUP BY DATE_FORMAT(i.created_at, '%Y-%m'), DATE_FORMAT(i.created_at, '%b %Y'), i.declaration_office_id, mo.main_location_name
              ORDER BY month ASC, office_name ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Office Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOfficeClearanceTimes()
  {
    try {
      $sql = "SELECT 
                COALESCE(mo.main_location_name, 'Not Specified') as office_name,
                COUNT(i.id) as total_imports,
                AVG(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as avg_days,
                MIN(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as min_days,
                MAX(DATEDIFF(i.dgda_out_date, i.dgda_in_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(i.dgda_out_date, i.dgda_in_date) <= 3 THEN 1 END) as within_3_days,
                COUNT(CASE WHEN DATEDIFF(i.dgda_out_date, i.dgda_in_date) BETWEEN 4 AND 7 THEN 1 END) as within_7_days,
                COUNT(CASE WHEN DATEDIFF(i.dgda_out_date, i.dgda_in_date) > 7 THEN 1 END) as over_7_days
              FROM imports_t i
              LEFT JOIN main_office_master_t mo ON i.declaration_office_id = mo.id AND mo.display = 'Y'
              WHERE i.display = 'Y' 
                AND i.dgda_in_date IS NOT NULL 
                AND i.dgda_out_date IS NOT NULL
                AND i.declaration_office_id IS NOT NULL
              GROUP BY i.declaration_office_id, mo.main_location_name
              HAVING COUNT(i.id) > 0
              ORDER BY avg_days ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Office Clearance Times Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSubOfficeBreakdown()
  {
    try {
      $sql = "SELECT 
                mo.id as main_office_id,
                mo.main_location_name as main_office,
                so.sub_office_name,
                COUNT(i.id) as import_count,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_count,
                COUNT(CASE WHEN i.clearing_status = 5 THEN 1 END) as in_progress_count,
                COUNT(CASE WHEN DATE(i.created_at) = CURDATE() THEN 1 END) as today_count,
                COUNT(CASE WHEN YEARWEEK(i.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as week_count,
                COUNT(CASE WHEN YEAR(i.created_at) = YEAR(CURDATE()) AND MONTH(i.created_at) = MONTH(CURDATE()) THEN 1 END) as month_count
              FROM sub_office_master_t so
              INNER JOIN main_office_master_t mo ON so.main_office_id = mo.id AND mo.display = 'Y'
              LEFT JOIN imports_t i ON i.declaration_office_id = mo.id AND i.display = 'Y'
              WHERE so.display = 'Y'
              GROUP BY mo.id, mo.main_location_name, so.sub_office_name
              ORDER BY mo.main_location_name, so.sub_office_name";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Sub Office Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getMainOfficeList()
  {
    try {
      $sql = "SELECT 
                mo.id,
                mo.main_location_name,
                COUNT(DISTINCT i.id) as total_imports,
                COUNT(CASE WHEN i.clearing_status = 6 THEN 1 END) as cleared_count,
                COUNT(DISTINCT so.id) as sub_office_count
              FROM main_office_master_t mo
              LEFT JOIN imports_t i ON i.declaration_office_id = mo.id AND i.display = 'Y'
              LEFT JOIN sub_office_master_t so ON so.main_office_id = mo.id AND so.display = 'Y'
              WHERE mo.display = 'Y'
              GROUP BY mo.id, mo.main_location_name
              ORDER BY total_imports DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Main Office List Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== CLIENT BASED TAB METHODS ====================

  private function getClientStats()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT i.subscriber_id) as total_clients,
                COUNT(i.id) as total_imports,
                SUM(CASE WHEN i.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_imports,
                SUM(CASE WHEN i.warehouse_arrival_date IS NOT NULL THEN 1 ELSE 0 END) as warehouse_arrivals
              FROM imports_t i
              WHERE i.display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Client Stats Error: " . $e->getMessage());
      return [];
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
                c.contact_person,
                c.email,
                COUNT(i.id) as total_imports,
                SUM(CASE WHEN i.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_imports,
                SUM(CASE WHEN i.warehouse_arrival_date IS NOT NULL THEN 1 ELSE 0 END) as warehouse_arrivals,
                MAX(i.created_at) as last_import_date
              FROM clients_t c
              LEFT JOIN imports_t i ON c.id = i.subscriber_id AND i.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name, c.client_type, c.contact_person, c.email
              HAVING total_imports > 0
              ORDER BY total_imports DESC";
      
      $clients = $this->db->customQuery($sql) ?: [];

      foreach ($clients as &$client) {
        $client_id = $client['id'];
        $client['transport_breakdown'] = $this->getClientTransportBreakdown($client_id);
        $client['goods_breakdown'] = $this->getClientGoodsBreakdown($client_id);
        $client['kind_breakdown'] = $this->getClientKindBreakdown($client_id);
        $client['status_breakdown'] = $this->getClientStatusBreakdown($client_id);
      }

      return $clients;
    } catch (Exception $e) {
      error_log("Client Details Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientTransportBreakdown($client_id)
  {
    try {
      $sql = "SELECT 
                COALESCE(tm.transport_mode_name, 'Not Specified') as transport_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN transport_mode_master_t tm ON i.transport_mode = tm.id AND tm.display = 'Y'
              WHERE i.subscriber_id = ? AND i.display = 'Y'
              GROUP BY i.transport_mode, tm.transport_mode_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql, [$client_id]) ?: [];
    } catch (Exception $e) {
      error_log("Client Transport Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientGoodsBreakdown($client_id)
  {
    try {
      $sql = "SELECT 
                COALESCE(tg.goods_type, 'Not Specified') as goods_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN type_of_goods_master_t tg ON i.type_of_goods = tg.id AND tg.display = 'Y'
              WHERE i.subscriber_id = ? AND i.display = 'Y'
              GROUP BY i.type_of_goods, tg.goods_type
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql, [$client_id]) ?: [];
    } catch (Exception $e) {
      error_log("Client Goods Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientKindBreakdown($client_id)
  {
    try {
      $sql = "SELECT 
                COALESCE(k.kind_name, 'Not Specified') as kind_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN kind_master_t k ON i.kind = k.id AND k.display = 'Y'
              WHERE i.subscriber_id = ? AND i.display = 'Y'
              GROUP BY i.kind, k.kind_name
              HAVING COUNT(i.id) > 0
              ORDER BY import_count DESC";
      
      return $this->db->customQuery($sql, [$client_id]) ?: [];
    } catch (Exception $e) {
      error_log("Client Kind Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientStatusBreakdown($client_id)
  {
    try {
      $sql = "SELECT 
                COALESCE(cs.clearing_status, 'Pending') as status_name,
                COUNT(i.id) as import_count
              FROM imports_t i
              LEFT JOIN clearing_status_master_t cs ON i.clearing_status = cs.id AND cs.display = 'Y'
              WHERE i.subscriber_id = ? AND i.display = 'Y'
              GROUP BY i.clearing_status, cs.clearing_status
              HAVING COUNT(i.id) > 0
              ORDER BY i.clearing_status ASC";
      
      return $this->db->customQuery($sql, [$client_id]) ?: [];
    } catch (Exception $e) {
      error_log("Client Status Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== EXPORT METHODS ====================

  public function exportDashboard()
  {
    try {
      if (!$this->loadPhpSpreadsheet()) {
        die('PhpSpreadsheet library not found');
      }

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      
      $sheet->setCellValue('A1', 'IMPORT DASHBOARD REPORT');
      $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
      
      $kpiData = $this->getKPIData();
      
      $row = 3;
      $sheet->setCellValue('A' . $row, 'KPI Summary');
      $sheet->getStyle('A' . $row)->getFont()->setBold(true);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'Total Imports');
      $sheet->setCellValue('B' . $row, $kpiData['total_imports'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'In Progress');
      $sheet->setCellValue('B' . $row, $kpiData['in_progress_imports'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'Clearing Completed');
      $sheet->setCellValue('B' . $row, $kpiData['clearing_completed'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'Today');
      $sheet->setCellValue('B' . $row, $kpiData['today_imports'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'This Week');
      $sheet->setCellValue('B' . $row, $kpiData['this_week_imports'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'This Month');
      $sheet->setCellValue('B' . $row, $kpiData['this_month_imports'] ?? 0);
      $row++;
      
      $sheet->setCellValue('A' . $row, 'This Year');
      $sheet->setCellValue('B' . $row, $kpiData['this_year_imports'] ?? 0);
      
      $sheet->getColumnDimension('A')->setAutoSize(true);
      $sheet->getColumnDimension('B')->setAutoSize(true);
      
      $filename = 'Import_Dashboard_' . date('Y-m-d_H-i-s') . '.xlsx';
      
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="' . $filename . '"');
      header('Cache-Control: max-age=0');
      
      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
      
    } catch (Exception $e) {
      error_log("Export Dashboard Error: " . $e->getMessage());
      die('Export failed: ' . $e->getMessage());
    }
  }
}