<?php

class ExportdashboardController extends Controller
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
      'title' => 'Export Dashboard - Enhanced',
      
      // Overview Tab Data
      'kpi_data' => $this->getKPIData(),
      'transport_mode_stats' => $this->getTransportModeStats(),
      'clearing_status_summary' => $this->getClearingStatusSummary(),
      'extended_kpi_data' => $this->getExtendedKPIData(),
      'clearing_status_distribution' => $this->getClearingStatusDistribution(),
      'kind_distribution' => $this->getKindDistribution(),
      'clearance_type_distribution' => $this->getClearanceTypeDistribution(),
      'monthly_trend' => $this->getMonthlyTrend(),
      'goods_distribution' => $this->getGoodsDistribution(),
      'transport_distribution' => $this->getTransportDistribution(),
      'currency_distribution' => $this->getCurrencyDistribution(),
      'exit_point_distribution' => $this->getExitPointDistribution(),
      'regime_distribution' => $this->getRegimeDistribution(),
      'timeline_analysis' => $this->getTimelineAnalysis(),
      'recent_exports' => $this->getRecentExports(),
      'all_exports_detailed' => $this->getAllExportsDetailed(),
      
      // Logistics Tab Data
      'logistics_overview' => $this->getLogisticsOverview(),
      'tracking_stages' => $this->getTrackingStages(),
      'export_timeline' => $this->getExportTimeline(),
      'border_crossing_stats' => $this->getBorderCrossingStats(),
      'route_analysis' => $this->getRouteAnalysis(),
      'vehicle_stats' => $this->getVehicleStats(),
      'container_stats' => $this->getContainerStats(),
      'container_type_stats' => $this->getContainerTypeStats(),
      'logistics_monthly_trend' => $this->getLogisticsMonthlyTrend(),
      'agency_processing_times' => $this->getAgencyProcessingTimes(),
      'truck_status_distribution' => $this->getTruckStatusDistribution(),
      'logistics_detailed' => $this->getLogisticsDetailed(),
      
      // Delay KPI Tab Data
      'delay_overview' => $this->getDelayOverview(),
      'ceec_delay_analysis' => $this->getCEECDelayAnalysis(),
      'mindiv_delay_analysis' => $this->getMinDivDelayAnalysis(),
      'customs_delay_analysis' => $this->getCustomsDelayAnalysis(),
      'transport_delay_analysis' => $this->getTransportDelayAnalysis(),
      'overall_delay_trends' => $this->getOverallDelayTrends(),
      'delay_by_client' => $this->getDelayByClient(),
      'delay_by_kind' => $this->getDelayByKind(),
      'delay_detailed' => $this->getDelayDetailed(),
      
      // Tri Phase Tab Data
      'triphase_overview' => $this->getTriPhaseOverview(),
      'triphase_monthly_breakdown' => $this->getTriPhaseMonthlyBreakdown(),
      'triphase_current_month' => $this->getTriPhaseCurrentMonth(),
      'triphase_by_client' => $this->getTriPhaseByClient(),
      'triphase_trend' => $this->getTriPhaseTrend(),
      'triphase_detailed' => $this->getTriPhaseDetailed(),
      
      // Loading Site Based Tab Data
      'site_overview' => $this->getSiteOverview(),
      'loading_site_analysis' => $this->getLoadingSiteAnalysis(),
      'site_performance' => $this->getSitePerformance(),
      'site_monthly_trend' => $this->getSiteMonthlyTrend(),
      'site_processing_times' => $this->getSiteProcessingTimes(),
      'site_detailed' => $this->getSiteDetailed(),
      
      // Prepayment & Agency Tab Data (renamed from Financial)
      'financial_overview' => $this->getFinancialOverview(),
      'agency_fees_summary' => $this->getAgencyFeesSummary(),
      'ceec_analytics' => $this->getCEECAnalytics(),
      'cgea_analytics' => $this->getCGEAAnalytics(),
      'occ_analytics' => $this->getOCCAnalytics(),
      'lmc_analytics' => $this->getLMCAnalytics(),
      'ogefrem_analytics' => $this->getOGEFREMAnalytics(),
      'liquidation_analytics' => $this->getLiquidationAnalytics(),
      'quittance_analytics' => $this->getQuittanceAnalytics(),
      'financial_monthly_trend' => $this->getFinancialMonthlyTrend(),
      'financial_detailed' => $this->getFinancialDetailed(),
      
      // Seal Management Tab Data
      'seal_overview' => $this->getSealOverview(),
      'seal_distribution' => $this->getSealDistribution(),
      'seal_by_site' => $this->getSealBySite(),
      'seal_monthly_trend' => $this->getSealMonthlyTrend(),
      'seal_individual_tracking' => $this->getSealIndividualTracking(),
      'seals_by_master' => $this->getSealsByMaster(),
      'recent_seal_usage' => $this->getRecentSealUsage(),
      'seal_detailed' => $this->getSealDetailed(),
      
      // Assay & Lot Tab Data
      'assay_overview' => $this->getAssayOverview(),
      'lot_analytics' => $this->getLotAnalytics(),
      'bags_analytics' => $this->getBagsAnalytics(),
      'assay_by_site' => $this->getAssayBySite(),
      'assay_detailed' => $this->getAssayDetailed(),
      
      // Buyer Analytics Tab Data
      'buyer_overview' => $this->getBuyerOverview(),
      'buyer_analytics' => $this->getBuyerAnalytics(),
      'buyer_monthly_trend' => $this->getBuyerMonthlyTrend(),
      'buyer_detailed' => $this->getBuyerDetailed(),
      
      // Client Based Tab Data (COMPREHENSIVE)
      'client_overview' => $this->getClientOverview(),
      'client_details' => $this->getClientDetails(),
      'client_financial_summary' => $this->getClientFinancialSummary(),
      'client_monthly_performance' => $this->getClientMonthlyPerformance(),
      'client_agency_breakdown' => $this->getClientAgencyBreakdown(),
      'client_seal_usage' => $this->getClientSealUsage(),
      'client_buyer_relationship' => $this->getClientBuyerRelationship(),
      'client_comparison' => $this->getClientComparison(),
      'client_detailed' => $this->getClientDetailed()
    ];

    $this->viewWithLayout('tracking/exportdashboard', $data);
  }

  // ==================== ENHANCED DETAILED DATA METHODS ====================

  private function getAllExportsDetailed()
  {
    try {
      $sql = "SELECT 
                e.*,
                COALESCE(c.short_name, c.company_name, 'N/A') as client_name,
                COALESCE(l.license_number, 'N/A') as license_number,
                COALESCE(k.kind_name, 'N/A') as kind_name,
                COALESCE(cs.clearing_status, 'Pending') as clearing_status_name,
                COALESCE(ds.document_status, 'Pending') as document_status_name,
                COALESCE(tm.transport_mode_name, 'N/A') as transport_mode_name,
                COALESCE(tg.goods_type, 'N/A') as goods_type_name,
                COALESCE(tp.transit_point_name, 'N/A') as loading_site_name,
                COALESCE(cur.currency_name, 'USD') as currency_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN licenses_t l ON e.license_id = l.id AND l.display = 'Y'
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              LEFT JOIN document_status_master_t ds ON e.document_status = ds.id AND ds.display = 'Y'
              LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id AND tm.display = 'Y'
              LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id AND tg.display = 'Y'
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              LEFT JOIN currency_master_t cur ON e.currency = cur.id AND cur.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY e.created_at DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("All Exports Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLogisticsDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_name,
                e.loading_date,
                e.dispatch_deliver_date,
                e.kanyaka_arrival_date,
                e.kanyaka_departure_date,
                e.border_arrival_date,
                e.exit_drc_date,
                e.horse,
                e.trailer_1,
                e.trailer_2,
                e.container,
                e.wagon_ref,
                e.weight,
                COALESCE(tp.transit_point_name, 'N/A') as loading_site,
                COALESCE(cs.clearing_status, 'Pending') as status,
                DATEDIFF(e.exit_drc_date, e.loading_date) as total_days
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY e.loading_date DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Logistics Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getDelayDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_name,
                DATEDIFF(COALESCE(e.ceec_out_date, CURDATE()), e.ceec_in_date) as ceec_days,
                DATEDIFF(COALESCE(e.min_div_out_date, CURDATE()), e.min_div_in_date) as mindiv_days,
                DATEDIFF(COALESCE(e.dgda_out_date, CURDATE()), e.dgda_in_date) as customs_days,
                DATEDIFF(COALESCE(e.exit_drc_date, CURDATE()), e.border_arrival_date) as exit_days,
                e.ceec_in_date,
                e.ceec_out_date,
                e.min_div_in_date,
                e.min_div_out_date,
                e.dgda_in_date,
                e.dgda_out_date,
                e.border_arrival_date,
                e.exit_drc_date
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY customs_days DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Delay Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTriPhaseDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_name,
                e.created_at,
                DAY(e.created_at) as day_of_month,
                CASE 
                  WHEN DAY(e.created_at) BETWEEN 1 AND 10 THEN 'Phase 1 (1-10)'
                  WHEN DAY(e.created_at) BETWEEN 11 AND 20 THEN 'Phase 2 (11-20)'
                  ELSE 'Phase 3 (21-End)'
                END as phase_name,
                e.weight,
                e.fob,
                COALESCE(k.kind_name, 'N/A') as kind_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY e.created_at DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Tri Phase Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSiteDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_name,
                COALESCE(tp.transit_point_name, 'N/A') as loading_site,
                e.loading_date,
                e.exit_drc_date,
                DATEDIFF(e.exit_drc_date, e.loading_date) as processing_days,
                e.weight,
                e.fob,
                COALESCE(cs.clearing_status, 'Pending') as status
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY e.loading_date DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Site Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getFinancialDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                COALESCE(c.short_name, c.company_name) as client_name,
                e.weight,
                e.ceec_amount,
                e.cgea_amount,
                e.occ_amount,
                e.lmc_amount,
                e.ogefrem_amount,
                e.liquidation_amount,
                (e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees,
                e.liquidation_date,
                e.quittance_date,
                e.liquidation_reference,
                e.quittance_reference
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY total_agency_fees DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Financial Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_short_name,
                e.number_of_seals,
                e.dgda_seal_no,
                COALESCE(tp.transit_point_name, 'N/A') as loading_site,
                e.created_at
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' AND e.number_of_seals > 0
              ORDER BY e.number_of_seals DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Seal Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getAssayDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_short_name,
                e.lot_number,
                e.number_of_bags,
                e.assay_date,
                e.weight,
                COALESCE(tp.transit_point_name, 'N/A') as loading_site
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' AND e.assay_date IS NOT NULL
              ORDER BY e.assay_date DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Assay Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBuyerDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                e.buyer,
                COALESCE(c.short_name, c.company_name) as client_short_name,
                e.weight,
                e.fob,
                e.created_at,
                COALESCE(k.kind_name, 'N/A') as kind_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              WHERE e.display = 'Y' AND e.buyer IS NOT NULL AND e.buyer != ''
              ORDER BY e.created_at DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Buyer Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientDetailed()
  {
    try {
      $sql = "SELECT 
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name) as client_name,
                e.buyer,
                e.weight,
                e.fob,
                (e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees,
                e.number_of_seals,
                COALESCE(cs.clearing_status, 'Pending') as status,
                e.created_at
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY c.short_name, e.created_at DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Detailed Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== EXISTING METHODS (UPDATED WITH short_name) ====================

  private function getKPIData()
  {
    try {
      $sql = "SELECT 
                COUNT(*) as total_exports,
                SUM(CASE WHEN clearing_status = 5 THEN 1 ELSE 0 END) as in_progress_exports,
                SUM(CASE WHEN clearing_status = 6 THEN 1 ELSE 0 END) as clearing_completed,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_exports,
                SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) as this_week_exports,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_exports,
                SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as this_year_exports,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob_value,
                AVG(weight) as avg_weight,
                AVG(fob) as avg_fob_value
              FROM exports_t
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
                COUNT(e.id) as export_count,
                SUM(CASE WHEN e.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_count,
                SUM(CASE WHEN e.clearing_status = 5 THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN e.clearing_status = 4 THEN 1 ELSE 0 END) as in_transit_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id AND tm.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.transport_mode, tm.transport_mode_name, tm.transport_letter
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
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
              FROM exports_t
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
                COUNT(DISTINCT buyer) as unique_buyers,
                COALESCE(AVG(DATEDIFF(dgda_out_date, dgda_in_date)), 0) as avg_customs_days,
                SUM(number_of_seals) as total_seals,
                SUM(number_of_bags) as total_bags,
                COUNT(CASE WHEN assay_date IS NOT NULL THEN 1 END) as assay_completed
              FROM exports_t
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
                COUNT(e.id) as export_count
              FROM exports_t e
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.clearing_status, cs.clearing_status
              HAVING COUNT(e.id) > 0
              ORDER BY e.clearing_status ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Clearing Status Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getKindDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(k.kind_name, 'Not Specified') as kind_name,
                COUNT(e.id) as export_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.kind, k.kind_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
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
                COUNT(e.id) as export_count
              FROM exports_t e
              LEFT JOIN clearance_master_t ct ON e.types_of_clearance = ct.id AND ct.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.types_of_clearance, ct.clearance_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
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
                COUNT(id) as export_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob
              FROM exports_t
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
                COUNT(e.id) as export_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN type_of_goods_master_t tg ON e.type_of_goods = tg.id AND tg.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.type_of_goods, tg.goods_type
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC
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
                COUNT(e.id) as export_count
              FROM exports_t e
              LEFT JOIN transport_mode_master_t tm ON e.transport_mode = tm.id AND tm.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.transport_mode, tm.transport_mode_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
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
                COUNT(e.id) as export_count,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN currency_master_t cur ON e.currency = cur.id AND cur.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.currency, cur.currency_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Currency Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getExitPointDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as exit_point_name,
                COUNT(e.id) as export_count,
                SUM(e.weight) as total_weight
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.exit_point_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.exit_point_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC
              LIMIT 10";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Exit Point Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRegimeDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(r.regime_name, 'Not Specified') as regime_name,
                COUNT(e.id) as export_count
              FROM exports_t e
              LEFT JOIN regime_master_t r ON e.regime = r.id AND r.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.regime, r.regime_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
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
                AVG(DATEDIFF(pv_date, loading_date)) as avg_loading_to_pv,
                AVG(DATEDIFF(dgda_in_date, loading_date)) as avg_loading_to_customs,
                AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_days_in_customs,
                AVG(DATEDIFF(exit_drc_date, dgda_out_date)) as avg_customs_to_exit,
                AVG(DATEDIFF(exit_drc_date, loading_date)) as avg_total_cycle_time,
                AVG(DATEDIFF(ceec_out_date, ceec_in_date)) as avg_ceec_processing,
                AVG(DATEDIFF(min_div_out_date, min_div_in_date)) as avg_mindiv_processing,
                AVG(DATEDIFF(liquidation_date, dgda_out_date)) as avg_to_liquidation,
                AVG(DATEDIFF(quittance_date, liquidation_date)) as avg_liquidation_to_quittance
              FROM exports_t
              WHERE display = 'Y'
                AND loading_date IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Timeline Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRecentExports()
  {
    try {
      $sql = "SELECT 
                e.id,
                e.mca_ref,
                e.invoice,
                e.buyer,
                e.loading_date,
                e.dgda_in_date,
                e.dgda_out_date,
                e.exit_drc_date,
                e.clearing_status,
                e.weight,
                e.fob,
                e.created_at,
                COALESCE(c.short_name, c.company_name, 'N/A') as client_name,
                COALESCE(l.license_number, 'N/A') as license_number,
                COALESCE(k.kind_name, 'N/A') as kind_name,
                COALESCE(cs.clearing_status, 'Pending') as clearing_status_name,
                COALESCE(cur.currency_name, 'USD') as currency_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              LEFT JOIN licenses_t l ON e.license_id = l.id AND l.display = 'Y'
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              LEFT JOIN clearing_status_master_t cs ON e.clearing_status = cs.id AND cs.display = 'Y'
              LEFT JOIN currency_master_t cur ON e.currency = cur.id AND cur.display = 'Y'
              WHERE e.display = 'Y'
              ORDER BY e.created_at DESC
              LIMIT 50";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Recent Exports Error: " . $e->getMessage());
      return [];
    }
  }

  // Continue with remaining methods... (due to length, I'll provide key updates)

  // Update all client-related queries to use short_name
  private function getDelayByClient()
  {
    try {
      $sql = "SELECT 
                COALESCE(c.short_name, c.company_name, 'Unknown') as client_name,
                COUNT(e.id) as total_exports,
                AVG(DATEDIFF(COALESCE(e.dgda_out_date, CURDATE()), e.dgda_in_date)) as avg_customs_delay,
                COUNT(CASE WHEN DATEDIFF(COALESCE(e.dgda_out_date, CURDATE()), e.dgda_in_date) > 5 THEN 1 END) as delayed_count
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y' AND e.dgda_in_date IS NOT NULL
              GROUP BY e.subscriber_id, c.short_name, c.company_name
              HAVING COUNT(e.id) > 0
              ORDER BY delayed_count DESC
              LIMIT 10";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Delay By Client Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== LOGISTICS TAB METHODS ====================

  private function getLogisticsOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN clearing_status IN (4, 5) THEN 1 END) as in_transit,
                COUNT(CASE WHEN loading_date IS NOT NULL THEN 1 END) as loaded,
                COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL THEN 1 END) as kanyaka_arrivals,
                COUNT(CASE WHEN kanyaka_departure_date IS NOT NULL THEN 1 END) as kanyaka_departures,
                COUNT(CASE WHEN border_arrival_date IS NOT NULL THEN 1 END) as border_arrivals,
                COUNT(CASE WHEN exit_drc_date IS NOT NULL THEN 1 END) as drc_exits,
                COUNT(CASE WHEN container IS NOT NULL AND container != '' THEN 1 END) as container_shipments,
                COUNT(CASE WHEN horse IS NOT NULL AND horse != '' THEN 1 END) as road_shipments,
                COUNT(CASE WHEN wagon_ref IS NOT NULL AND wagon_ref != '' THEN 1 END) as rail_shipments,
                AVG(DATEDIFF(exit_drc_date, loading_date)) as avg_total_transit_time,
                SUM(weight) as total_weight_in_transit,
                SUM(number_of_seals) as total_seals_used
              FROM exports_t
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
      $sql = "SELECT 
                COUNT(CASE WHEN (horse IS NOT NULL AND horse != '') OR (trailer_1 IS NOT NULL AND trailer_1 != '') THEN 1 END) as total_road_shipments,
                COUNT(CASE WHEN (horse IS NOT NULL AND horse != '') AND loading_date IS NULL THEN 1 END) as waiting_loading,
                COUNT(CASE WHEN loading_date IS NOT NULL AND dispatch_deliver_date IS NULL THEN 1 END) as waiting_dispatch,
                COUNT(CASE WHEN dispatch_deliver_date IS NOT NULL AND kanyaka_arrival_date IS NULL THEN 1 END) as waiting_kanyaka_arrival,
                COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL AND kanyaka_departure_date IS NULL THEN 1 END) as waiting_kanyaka_departure,
                COUNT(CASE WHEN kanyaka_departure_date IS NOT NULL AND border_arrival_date IS NULL THEN 1 END) as waiting_border_arrival,
                COUNT(CASE WHEN border_arrival_date IS NOT NULL AND exit_drc_date IS NULL THEN 1 END) as waiting_exit,
                COUNT(CASE WHEN exit_drc_date IS NOT NULL THEN 1 END) as completed_road_journey
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Tracking Stages Error: " . $e->getMessage());
      return [];
    }
  }

  private function getExportTimeline()
  {
    try {
      $sql = "SELECT 
                AVG(DATEDIFF(kanyaka_arrival_date, loading_date)) as avg_to_kanyaka,
                AVG(DATEDIFF(border_arrival_date, kanyaka_departure_date)) as avg_kanyaka_to_border,
                AVG(DATEDIFF(exit_drc_date, border_arrival_date)) as avg_border_to_exit,
                AVG(DATEDIFF(exit_drc_date, loading_date)) as avg_total_journey
              FROM exports_t
              WHERE display = 'Y'
                AND loading_date IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Export Timeline Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBorderCrossingStats()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as exit_point_name,
                COUNT(e.id) as crossing_count,
                AVG(DATEDIFF(e.exit_drc_date, e.border_arrival_date)) as avg_processing_days,
                SUM(e.weight) as total_weight
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.exit_point_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' AND e.border_arrival_date IS NOT NULL
              GROUP BY e.exit_point_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
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
                'Kanyaka Route' as route_name,
                COUNT(id) as shipment_count,
                AVG(DATEDIFF(border_arrival_date, kanyaka_departure_date)) as avg_transit_days,
                SUM(weight) as total_weight
              FROM exports_t
              WHERE display = 'Y' AND kanyaka_departure_date IS NOT NULL
              UNION ALL
              SELECT 
                'Direct Border' as route_name,
                COUNT(id) as shipment_count,
                AVG(DATEDIFF(exit_drc_date, border_arrival_date)) as avg_transit_days,
                SUM(weight) as total_weight
              FROM exports_t
              WHERE display = 'Y' AND border_arrival_date IS NOT NULL AND kanyaka_departure_date IS NULL";
      
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
                'Road (Horse/Trailer)' as vehicle_type,
                COUNT(id) as shipment_count,
                COUNT(DISTINCT horse) as unique_vehicles,
                SUM(weight) as total_weight
              FROM exports_t
              WHERE display = 'Y' AND horse IS NOT NULL AND horse != ''
              UNION ALL
              SELECT 
                'Rail (Wagon)' as vehicle_type,
                COUNT(id) as shipment_count,
                COUNT(DISTINCT wagon_ref) as unique_vehicles,
                SUM(weight) as total_weight
              FROM exports_t
              WHERE display = 'Y' AND wagon_ref IS NOT NULL AND wagon_ref != ''";
      
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
                COUNT(id) as shipment_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob
              FROM exports_t
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

  private function getContainerTypeStats()
  {
    try {
      $sql = "SELECT 
                COALESCE(feet_container, 'Not Specified') as container_type,
                COUNT(id) as shipment_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob
              FROM exports_t
              WHERE display = 'Y' 
                AND feet_container IS NOT NULL 
                AND feet_container != ''
              GROUP BY feet_container
              ORDER BY shipment_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Container Type Stats Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLogisticsMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(CASE WHEN kanyaka_arrival_date IS NOT NULL THEN 1 END) as kanyaka_route,
                COUNT(CASE WHEN border_arrival_date IS NOT NULL THEN 1 END) as border_arrivals,
                COUNT(CASE WHEN exit_drc_date IS NOT NULL THEN 1 END) as drc_exits,
                SUM(weight) as total_weight
              FROM exports_t
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

  private function getAgencyProcessingTimes()
  {
    try {
      $sql = "SELECT 
                'CEEC' as agency_name,
                COUNT(id) as processed_count,
                AVG(DATEDIFF(ceec_out_date, ceec_in_date)) as avg_processing_days,
                SUM(ceec_amount) as total_amount
              FROM exports_t
              WHERE display = 'Y' AND ceec_in_date IS NOT NULL
              UNION ALL
              SELECT 
                'Min Div' as agency_name,
                COUNT(id) as processed_count,
                AVG(DATEDIFF(min_div_out_date, min_div_in_date)) as avg_processing_days,
                0 as total_amount
              FROM exports_t
              WHERE display = 'Y' AND min_div_in_date IS NOT NULL
              UNION ALL
              SELECT 
                'DGDA' as agency_name,
                COUNT(id) as processed_count,
                AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_processing_days,
                0 as total_amount
              FROM exports_t
              WHERE display = 'Y' AND dgda_in_date IS NOT NULL
              UNION ALL
              SELECT 
                'Gov Docs' as agency_name,
                COUNT(id) as processed_count,
                AVG(DATEDIFF(gov_docs_out_date, gov_docs_in_date)) as avg_processing_days,
                0 as total_amount
              FROM exports_t
              WHERE display = 'Y' AND gov_docs_in_date IS NOT NULL";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Agency Processing Times Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTruckStatusDistribution()
  {
    try {
      $sql = "SELECT 
                COALESCE(ts.truck_status, 'Not Specified') as status_name,
                COUNT(e.id) as export_count
              FROM exports_t e
              LEFT JOIN truck_status_master_t ts ON e.truck_status = ts.id AND ts.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.truck_status, ts.truck_status
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Truck Status Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== DELAY KPI TAB METHODS ====================

  private function getDelayOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date) > 3 THEN 1 END) as ceec_delays,
                COUNT(CASE WHEN DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date) > 2 THEN 1 END) as mindiv_delays,
                COUNT(CASE WHEN DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date) > 5 THEN 1 END) as customs_delays,
                COUNT(CASE WHEN DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date) > 3 THEN 1 END) as exit_delays,
                AVG(DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date)) as avg_ceec_delay,
                AVG(DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date)) as avg_mindiv_delay,
                AVG(DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date)) as avg_customs_delay,
                AVG(DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date)) as avg_exit_delay
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Delay Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getCEECDelayAnalysis()
  {
    try {
      $sql = "SELECT 
                CASE 
                  WHEN DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date) <= 1 THEN '0-1 day'
                  WHEN DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date) <= 3 THEN '2-3 days'
                  WHEN DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date) <= 5 THEN '4-5 days'
                  WHEN DATEDIFF(COALESCE(ceec_out_date, CURDATE()), ceec_in_date) <= 7 THEN '6-7 days'
                  ELSE '7+ days'
                END as delay_range,
                COUNT(id) as export_count
              FROM exports_t
              WHERE display = 'Y' AND ceec_in_date IS NOT NULL
              GROUP BY delay_range
              ORDER BY FIELD(delay_range, '0-1 day', '2-3 days', '4-5 days', '6-7 days', '7+ days')";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("CEEC Delay Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getMinDivDelayAnalysis()
  {
    try {
      $sql = "SELECT 
                CASE 
                  WHEN DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date) <= 1 THEN '0-1 day'
                  WHEN DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date) <= 2 THEN '2 days'
                  WHEN DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date) <= 3 THEN '3 days'
                  WHEN DATEDIFF(COALESCE(min_div_out_date, CURDATE()), min_div_in_date) <= 5 THEN '4-5 days'
                  ELSE '5+ days'
                END as delay_range,
                COUNT(id) as export_count
              FROM exports_t
              WHERE display = 'Y' AND min_div_in_date IS NOT NULL
              GROUP BY delay_range
              ORDER BY FIELD(delay_range, '0-1 day', '2 days', '3 days', '4-5 days', '5+ days')";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("MinDiv Delay Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getCustomsDelayAnalysis()
  {
    try {
      $sql = "SELECT 
                CASE 
                  WHEN DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date) <= 3 THEN '0-3 days'
                  WHEN DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date) <= 5 THEN '4-5 days'
                  WHEN DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date) <= 7 THEN '6-7 days'
                  WHEN DATEDIFF(COALESCE(dgda_out_date, CURDATE()), dgda_in_date) <= 10 THEN '8-10 days'
                  ELSE '10+ days'
                END as delay_range,
                COUNT(id) as export_count
              FROM exports_t
              WHERE display = 'Y' AND dgda_in_date IS NOT NULL
              GROUP BY delay_range
              ORDER BY FIELD(delay_range, '0-3 days', '4-5 days', '6-7 days', '8-10 days', '10+ days')";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Customs Delay Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getTransportDelayAnalysis()
  {
    try {
      $sql = "SELECT 
                CASE 
                  WHEN DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date) <= 2 THEN '0-2 days'
                  WHEN DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date) <= 3 THEN '3 days'
                  WHEN DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date) <= 5 THEN '4-5 days'
                  WHEN DATEDIFF(COALESCE(exit_drc_date, CURDATE()), border_arrival_date) <= 7 THEN '6-7 days'
                  ELSE '7+ days'
                END as delay_range,
                COUNT(id) as export_count
              FROM exports_t
              WHERE display = 'Y' AND border_arrival_date IS NOT NULL
              GROUP BY delay_range
              ORDER BY FIELD(delay_range, '0-2 days', '3 days', '4-5 days', '6-7 days', '7+ days')";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Transport Delay Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOverallDelayTrends()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                AVG(DATEDIFF(ceec_out_date, ceec_in_date)) as avg_ceec_days,
                AVG(DATEDIFF(min_div_out_date, min_div_in_date)) as avg_mindiv_days,
                AVG(DATEDIFF(dgda_out_date, dgda_in_date)) as avg_customs_days,
                AVG(DATEDIFF(exit_drc_date, border_arrival_date)) as avg_exit_days
              FROM exports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Overall Delay Trends Error: " . $e->getMessage());
      return [];
    }
  }

  private function getDelayByKind()
  {
    try {
      $sql = "SELECT 
                COALESCE(k.kind_name, 'Not Specified') as kind_name,
                COUNT(e.id) as total_exports,
                AVG(DATEDIFF(COALESCE(e.dgda_out_date, CURDATE()), e.dgda_in_date)) as avg_customs_delay,
                COUNT(CASE WHEN DATEDIFF(COALESCE(e.dgda_out_date, CURDATE()), e.dgda_in_date) > 5 THEN 1 END) as delayed_count
              FROM exports_t e
              LEFT JOIN kind_master_t k ON e.kind = k.id AND k.display = 'Y'
              WHERE e.display = 'Y' AND e.dgda_in_date IS NOT NULL
              GROUP BY e.kind, k.kind_name
              HAVING COUNT(e.id) > 0
              ORDER BY avg_customs_delay DESC
              LIMIT 10";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Delay By Kind Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== TRI PHASE TAB METHODS ====================

  private function getTriPhaseOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_exports,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1_count,
                COUNT(CASE WHEN DAY(created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2_count,
                COUNT(CASE WHEN DAY(created_at) >= 21 THEN 1 END) as phase3_count
              FROM exports_t
              WHERE display = 'Y'
                AND YEAR(created_at) = YEAR(CURDATE())
                AND MONTH(created_at) = MONTH(CURDATE())";
      
      $result = $this->db->customQuery($sql);
      $data = $result[0] ?? [
        'total_exports' => 0,
        'phase1_count' => 0,
        'phase2_count' => 0,
        'phase3_count' => 0
      ];
      
      $data['total_exports'] = (int)($data['total_exports'] ?? 0);
      $data['phase1_count'] = (int)($data['phase1_count'] ?? 0);
      $data['phase2_count'] = (int)($data['phase2_count'] ?? 0);
      $data['phase3_count'] = (int)($data['phase3_count'] ?? 0);
      
      return $data;
    } catch (Exception $e) {
      error_log("Tri Phase Overview Error: " . $e->getMessage());
      return [
        'total_exports' => 0,
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
              FROM exports_t
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
                e.id,
                e.mca_ref,
                e.invoice,
                COALESCE(c.short_name, c.company_name, 'N/A') as client_name,
                e.created_at,
                DAY(e.created_at) as day_of_month,
                CASE 
                  WHEN DAY(e.created_at) BETWEEN 1 AND 10 THEN 'Phase 1'
                  WHEN DAY(e.created_at) BETWEEN 11 AND 20 THEN 'Phase 2'
                  ELSE 'Phase 3'
                END as phase_name
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y'
                AND YEAR(e.created_at) = YEAR(CURDATE())
                AND MONTH(e.created_at) = MONTH(CURDATE())
              ORDER BY e.created_at ASC";
      
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
                COALESCE(c.short_name, c.company_name, 'Unknown') as client_name,
                COUNT(e.id) as total_exports,
                COUNT(CASE WHEN DAY(e.created_at) BETWEEN 1 AND 10 THEN 1 END) as phase1_count,
                COUNT(CASE WHEN DAY(e.created_at) BETWEEN 11 AND 20 THEN 1 END) as phase2_count,
                COUNT(CASE WHEN DAY(e.created_at) >= 21 THEN 1 END) as phase3_count
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y'
                AND e.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
              GROUP BY e.subscriber_id, c.short_name, c.company_name
              HAVING COUNT(e.id) > 0
              ORDER BY total_exports DESC
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
              FROM exports_t
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

  // ==================== LOADING SITE BASED TAB METHODS ====================

  private function getSiteOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT e.site_of_loading_id) as unique_sites,
                COUNT(e.id) as total_exports,
                COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) as cleared_exports,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_processing_days,
                COUNT(CASE WHEN DATE(e.created_at) = CURDATE() THEN 1 END) as today_exports,
                COUNT(CASE WHEN YEARWEEK(e.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as this_week_exports,
                COUNT(CASE WHEN YEAR(e.created_at) = YEAR(CURDATE()) AND MONTH(e.created_at) = MONTH(CURDATE()) THEN 1 END) as this_month_exports,
                COUNT(CASE WHEN YEAR(e.created_at) = YEAR(CURDATE()) THEN 1 END) as this_year_exports,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              WHERE e.display = 'Y'
                AND e.site_of_loading_id IS NOT NULL";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Site Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLoadingSiteAnalysis()
  {
    try {
      $sql = "SELECT 
                e.site_of_loading_id,
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                COUNT(e.id) as export_count,
                COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) as cleared_count,
                COUNT(CASE WHEN e.clearing_status = 5 THEN 1 END) as in_progress_count,
                COUNT(CASE WHEN e.clearing_status = 4 THEN 1 END) as in_transit_count,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_processing_days,
                COUNT(CASE WHEN e.loading_date IS NOT NULL THEN 1 END) as loaded_count,
                COUNT(CASE WHEN DATE(e.created_at) = CURDATE() THEN 1 END) as today_count,
                COUNT(CASE WHEN YEARWEEK(e.created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as week_count,
                COUNT(CASE WHEN YEAR(e.created_at) = YEAR(CURDATE()) AND MONTH(e.created_at) = MONTH(CURDATE()) THEN 1 END) as month_count,
                COUNT(CASE WHEN YEAR(e.created_at) = YEAR(CURDATE()) THEN 1 END) as year_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y'
                AND e.site_of_loading_id IS NOT NULL
              GROUP BY e.site_of_loading_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
              ORDER BY export_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Loading Site Analysis Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSitePerformance()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                COUNT(e.id) as total_exports,
                COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) as cleared_exports,
                COUNT(CASE WHEN e.clearing_status = 5 THEN 1 END) as in_progress_exports,
                COUNT(CASE WHEN DATEDIFF(COALESCE(e.exit_drc_date, CURDATE()), e.loading_date) <= 10 THEN 1 END) as fast_clearance,
                COUNT(CASE WHEN DATEDIFF(COALESCE(e.exit_drc_date, CURDATE()), e.loading_date) > 10 THEN 1 END) as delayed_clearance,
                ROUND((COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) / COUNT(e.id)) * 100, 1) as clearance_rate,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_processing_days,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' AND e.site_of_loading_id IS NOT NULL
              GROUP BY e.site_of_loading_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
              ORDER BY clearance_rate DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Site Performance Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSiteMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as month,
                DATE_FORMAT(e.created_at, '%b %Y') as month_name,
                e.site_of_loading_id,
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                COUNT(e.id) as export_count,
                COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) as cleared_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND e.display = 'Y'
                AND e.site_of_loading_id IS NOT NULL
              GROUP BY DATE_FORMAT(e.created_at, '%Y-%m'), DATE_FORMAT(e.created_at, '%b %Y'), e.site_of_loading_id, tp.transit_point_name
              ORDER BY month ASC, site_name ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Site Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSiteProcessingTimes()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                COUNT(e.id) as total_exports,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_days,
                MIN(DATEDIFF(e.exit_drc_date, e.loading_date)) as min_days,
                MAX(DATEDIFF(e.exit_drc_date, e.loading_date)) as max_days,
                COUNT(CASE WHEN DATEDIFF(e.exit_drc_date, e.loading_date) <= 7 THEN 1 END) as within_7_days,
                COUNT(CASE WHEN DATEDIFF(e.exit_drc_date, e.loading_date) BETWEEN 8 AND 14 THEN 1 END) as within_14_days,
                COUNT(CASE WHEN DATEDIFF(e.exit_drc_date, e.loading_date) > 14 THEN 1 END) as over_14_days
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' 
                AND e.loading_date IS NOT NULL 
                AND e.exit_drc_date IS NOT NULL
                AND e.site_of_loading_id IS NOT NULL
              GROUP BY e.site_of_loading_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
              ORDER BY avg_days ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Site Processing Times Error: " . $e->getMessage());
      return [];
    }
  }

// ==================== PREPAYMENT & AGENCY TAB METHODS ====================

  private function getFinancialOverview()
  {
    try {
      $sql = "SELECT 
                SUM(ceec_amount) as total_ceec,
                SUM(cgea_amount) as total_cgea,
                SUM(occ_amount) as total_occ,
                SUM(lmc_amount) as total_lmc,
                SUM(ogefrem_amount) as total_ogefrem,
                SUM(liquidation_amount) as total_liquidation,
                SUM(ceec_amount + cgea_amount + occ_amount + lmc_amount + ogefrem_amount) as total_agency_fees,
                COUNT(CASE WHEN liquidation_date IS NOT NULL THEN 1 END) as liquidated_count,
                COUNT(CASE WHEN quittance_date IS NOT NULL THEN 1 END) as quittance_count,
                AVG(liquidation_amount) as avg_liquidation_amount
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Financial Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getAgencyFeesSummary()
  {
    try {
      $sql = "SELECT 
                'CEEC' as agency_name,
                SUM(ceec_amount) as total_amount,
                COUNT(CASE WHEN ceec_amount > 0 THEN 1 END) as processed_count,
                AVG(ceec_amount) as avg_amount,
                MAX(ceec_amount) as max_amount,
                MIN(ceec_amount) as min_amount
              FROM exports_t
              WHERE display = 'Y' AND ceec_amount > 0
              UNION ALL
              SELECT 
                'CGEA' as agency_name,
                SUM(cgea_amount) as total_amount,
                COUNT(CASE WHEN cgea_amount > 0 THEN 1 END) as processed_count,
                AVG(cgea_amount) as avg_amount,
                MAX(cgea_amount) as max_amount,
                MIN(cgea_amount) as min_amount
              FROM exports_t
              WHERE display = 'Y' AND cgea_amount > 0
              UNION ALL
              SELECT 
                'OCC' as agency_name,
                SUM(occ_amount) as total_amount,
                COUNT(CASE WHEN occ_amount > 0 THEN 1 END) as processed_count,
                AVG(occ_amount) as avg_amount,
                MAX(occ_amount) as max_amount,
                MIN(occ_amount) as min_amount
              FROM exports_t
              WHERE display = 'Y' AND occ_amount > 0
              UNION ALL
              SELECT 
                'LMC' as agency_name,
                SUM(lmc_amount) as total_amount,
                COUNT(CASE WHEN lmc_amount > 0 THEN 1 END) as processed_count,
                AVG(lmc_amount) as avg_amount,
                MAX(lmc_amount) as max_amount,
                MIN(lmc_amount) as min_amount
              FROM exports_t
              WHERE display = 'Y' AND lmc_amount > 0
              UNION ALL
              SELECT 
                'OGEFREM' as agency_name,
                SUM(ogefrem_amount) as total_amount,
                COUNT(CASE WHEN ogefrem_amount > 0 THEN 1 END) as processed_count,
                AVG(ogefrem_amount) as avg_amount,
                MAX(ogefrem_amount) as max_amount,
                MIN(ogefrem_amount) as min_amount
              FROM exports_t
              WHERE display = 'Y' AND ogefrem_amount > 0";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Agency Fees Summary Error: " . $e->getMessage());
      return [];
    }
  }

  private function getCEECAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_processed,
                SUM(ceec_amount) as total_amount,
                AVG(ceec_amount) as avg_amount,
                AVG(DATEDIFF(ceec_out_date, ceec_in_date)) as avg_processing_days,
                COUNT(CASE WHEN ceec_in_date IS NOT NULL THEN 1 END) as in_count,
                COUNT(CASE WHEN ceec_out_date IS NOT NULL THEN 1 END) as out_count,
                COUNT(CASE WHEN ceec_in_date IS NOT NULL AND ceec_out_date IS NULL THEN 1 END) as pending_count
              FROM exports_t
              WHERE display = 'Y' AND ceec_amount > 0";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("CEEC Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getCGEAAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_processed,
                SUM(cgea_amount) as total_amount,
                AVG(cgea_amount) as avg_amount,
                COUNT(CASE WHEN cgea_doc_ref IS NOT NULL AND cgea_doc_ref != '' THEN 1 END) as with_doc_ref
              FROM exports_t
              WHERE display = 'Y' AND cgea_amount > 0";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("CGEA Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOCCAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_processed,
                SUM(occ_amount) as total_amount,
                AVG(occ_amount) as avg_amount
              FROM exports_t
              WHERE display = 'Y' AND occ_amount > 0";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("OCC Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLMCAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_processed,
                SUM(lmc_amount) as total_amount,
                AVG(lmc_amount) as avg_amount,
                COUNT(CASE WHEN lmc_id IS NOT NULL AND lmc_id != '' THEN 1 END) as with_lmc_id
              FROM exports_t
              WHERE display = 'Y' AND lmc_amount > 0";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("LMC Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getOGEFREMAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_processed,
                SUM(ogefrem_amount) as total_amount,
                AVG(ogefrem_amount) as avg_amount,
                COUNT(CASE WHEN ogefrem_inv_ref IS NOT NULL AND ogefrem_inv_ref != '' THEN 1 END) as with_inv_ref
              FROM exports_t
              WHERE display = 'Y' AND ogefrem_amount > 0";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("OGEFREM Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLiquidationAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN liquidation_date IS NOT NULL THEN 1 END) as total_liquidations,
                SUM(liquidation_amount) as total_amount,
                AVG(liquidation_amount) as avg_amount,
                MAX(liquidation_amount) as max_amount,
                MIN(liquidation_amount) as min_amount,
                AVG(DATEDIFF(liquidation_date, dgda_out_date)) as avg_days_to_liquidation,
                COUNT(CASE WHEN liquidation_reference IS NOT NULL AND liquidation_reference != '' THEN 1 END) as with_reference
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Liquidation Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getQuittanceAnalytics()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN quittance_date IS NOT NULL THEN 1 END) as total_quittances,
                AVG(DATEDIFF(quittance_date, liquidation_date)) as avg_days_from_liquidation,
                COUNT(CASE WHEN quittance_reference IS NOT NULL AND quittance_reference != '' THEN 1 END) as with_reference
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Quittance Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getFinancialMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                SUM(ceec_amount) as total_ceec,
                SUM(cgea_amount) as total_cgea,
                SUM(occ_amount) as total_occ,
                SUM(lmc_amount) as total_lmc,
                SUM(ogefrem_amount) as total_ogefrem,
                SUM(liquidation_amount) as total_liquidation
              FROM exports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Financial Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== SEAL MANAGEMENT TAB METHODS ====================

  private function getSealOverview()
  {
    try {
      $sql = "SELECT 
                SUM(number_of_seals) as total_seals_used,
                COUNT(CASE WHEN number_of_seals > 0 THEN 1 END) as shipments_with_seals,
                AVG(number_of_seals) as avg_seals_per_shipment,
                MAX(number_of_seals) as max_seals_shipment,
                COUNT(CASE WHEN dgda_seal_no IS NOT NULL AND dgda_seal_no != '' THEN 1 END) as with_dgda_seal
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Seal Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealDistribution()
  {
    try {
      $sql = "SELECT 
                number_of_seals as seal_count,
                COUNT(id) as shipment_count
              FROM exports_t
              WHERE display = 'Y' AND number_of_seals > 0
              GROUP BY number_of_seals
              ORDER BY number_of_seals ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Seal Distribution Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealBySite()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                SUM(e.number_of_seals) as total_seals,
                AVG(e.number_of_seals) as avg_seals,
                COUNT(e.id) as shipment_count
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y' AND e.number_of_seals > 0
              GROUP BY e.site_of_loading_id, tp.transit_point_name
              HAVING COUNT(e.id) > 0
              ORDER BY total_seals DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Seal By Site Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                SUM(number_of_seals) as total_seals,
                AVG(number_of_seals) as avg_seals,
                COUNT(id) as shipment_count
              FROM exports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
                AND number_of_seals > 0
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Seal Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealIndividualTracking()
  {
    try {
      $sql = "SELECT 
                COUNT(id) as total_seals,
                COUNT(CASE WHEN status = 'Available' THEN 1 END) as available_seals,
                COUNT(CASE WHEN status = 'Used' THEN 1 END) as used_seals,
                COUNT(CASE WHEN status = 'Damaged' THEN 1 END) as damaged_seals
              FROM seal_individual_numbers_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Seal Individual Tracking Error: " . $e->getMessage());
      return [];
    }
  }

  private function getSealsByMaster()
  {
    try {
      $sql = "SELECT 
                sm.seal_no as master_seal,
                COUNT(sin.id) as total_individual_seals,
                COUNT(CASE WHEN sin.status = 'Available' THEN 1 END) as available,
                COUNT(CASE WHEN sin.status = 'Used' THEN 1 END) as used,
                COUNT(CASE WHEN sin.status = 'Damaged' THEN 1 END) as damaged
              FROM seal_nos_t sm
              LEFT JOIN seal_individual_numbers_t sin ON sm.id = sin.seal_master_id AND sin.display = 'Y'
              WHERE sm.display = 'Y'
              GROUP BY sm.id, sm.seal_no
              ORDER BY total_individual_seals DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Seals By Master Error: " . $e->getMessage());
      return [];
    }
  }

  private function getRecentSealUsage()
  {
    try {
      $sql = "SELECT 
                sin.seal_number,
                sin.status,
                sm.seal_no as master_seal,
                sin.notes,
                sin.created_at,
                sin.updated_at
              FROM seal_individual_numbers_t sin
              LEFT JOIN seal_nos_t sm ON sin.seal_master_id = sm.id AND sm.display = 'Y'
              WHERE sin.display = 'Y'
              ORDER BY sin.updated_at DESC
              LIMIT 100";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Recent Seal Usage Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== ASSAY & LOT TAB METHODS ====================

  private function getAssayOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(CASE WHEN assay_date IS NOT NULL THEN 1 END) as total_assays,
                COUNT(CASE WHEN lot_number IS NOT NULL AND lot_number != '' THEN 1 END) as with_lot_number,
                COUNT(DISTINCT lot_number) as unique_lots,
                AVG(DATEDIFF(assay_date, loading_date)) as avg_days_to_assay
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Assay Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getLotAnalytics()
  {
    try {
      $sql = "SELECT 
                lot_number,
                COUNT(id) as shipment_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob,
                MIN(assay_date) as first_assay,
                MAX(assay_date) as last_assay
              FROM exports_t
              WHERE display = 'Y' 
                AND lot_number IS NOT NULL 
                AND lot_number != ''
              GROUP BY lot_number
              ORDER BY shipment_count DESC
              LIMIT 50";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Lot Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBagsAnalytics()
  {
    try {
      $sql = "SELECT 
                SUM(number_of_bags) as total_bags,
                AVG(number_of_bags) as avg_bags_per_shipment,
                MAX(number_of_bags) as max_bags,
                COUNT(CASE WHEN number_of_bags > 0 THEN 1 END) as shipments_with_bags
              FROM exports_t
              WHERE display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Bags Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getAssayBySite()
  {
    try {
      $sql = "SELECT 
                COALESCE(tp.transit_point_name, 'Not Specified') as site_name,
                COUNT(CASE WHEN e.assay_date IS NOT NULL THEN 1 END) as assay_count,
                AVG(DATEDIFF(e.assay_date, e.loading_date)) as avg_days_to_assay
              FROM exports_t e
              LEFT JOIN transit_point_master_t tp ON e.site_of_loading_id = tp.id AND tp.display = 'Y'
              WHERE e.display = 'Y'
              GROUP BY e.site_of_loading_id, tp.transit_point_name
              HAVING assay_count > 0
              ORDER BY assay_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Assay By Site Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== BUYER ANALYTICS TAB METHODS ====================

  private function getBuyerOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT buyer) as total_buyers,
                COUNT(id) as total_exports,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob
              FROM exports_t
              WHERE display = 'Y' 
                AND buyer IS NOT NULL 
                AND buyer != ''";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Buyer Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBuyerAnalytics()
  {
    try {
      $sql = "SELECT 
                buyer,
                COUNT(id) as export_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob,
                AVG(weight) as avg_weight,
                AVG(fob) as avg_fob,
                COUNT(DISTINCT COALESCE(c.short_name, c.company_name, 'Unknown')) as unique_clients,
                MIN(created_at) as first_export,
                MAX(created_at) as last_export
              FROM exports_t e
              LEFT JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.display = 'Y' 
                AND e.buyer IS NOT NULL 
                AND e.buyer != ''
              GROUP BY e.buyer
              ORDER BY export_count DESC
              LIMIT 100";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Buyer Analytics Error: " . $e->getMessage());
      return [];
    }
  }

  private function getBuyerMonthlyTrend()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                DATE_FORMAT(created_at, '%b %Y') as month_name,
                COUNT(DISTINCT buyer) as unique_buyers,
                COUNT(id) as export_count,
                SUM(weight) as total_weight,
                SUM(fob) as total_fob
              FROM exports_t
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND display = 'Y'
                AND buyer IS NOT NULL 
                AND buyer != ''
              GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
              ORDER BY month ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Buyer Monthly Trend Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== CLIENT BASED TAB METHODS (WITH short_name) ====================

  private function getClientOverview()
  {
    try {
      $sql = "SELECT 
                COUNT(DISTINCT e.subscriber_id) as total_clients,
                COUNT(e.id) as total_exports,
                SUM(CASE WHEN e.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_exports,
                SUM(CASE WHEN e.exit_drc_date IS NOT NULL THEN 1 ELSE 0 END) as exited_drc,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob_value,
                SUM(e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees,
                SUM(e.liquidation_amount) as total_liquidation,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_processing_time
              FROM exports_t e
              WHERE e.display = 'Y'";
      
      $result = $this->db->customQuery($sql);
      return $result[0] ?? [];
    } catch (Exception $e) {
      error_log("Client Overview Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientDetails()
  {
    try {
      $sql = "SELECT 
                c.id,
                COALESCE(c.short_name, c.company_name) as client_name,
                c.company_name as full_name,
                c.client_type,
                c.contact_person,
                c.email,
                COUNT(e.id) as total_exports,
                SUM(CASE WHEN e.clearing_status = 6 THEN 1 ELSE 0 END) as cleared_exports,
                SUM(CASE WHEN e.clearing_status = 5 THEN 1 ELSE 0 END) as in_progress_exports,
                SUM(CASE WHEN e.exit_drc_date IS NOT NULL THEN 1 ELSE 0 END) as exited_drc,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob_value,
                AVG(e.weight) as avg_weight,
                AVG(e.fob) as avg_fob_value,
                SUM(e.ceec_amount) as total_ceec,
                SUM(e.cgea_amount) as total_cgea,
                SUM(e.occ_amount) as total_occ,
                SUM(e.lmc_amount) as total_lmc,
                SUM(e.ogefrem_amount) as total_ogefrem,
                SUM(e.liquidation_amount) as total_liquidation,
                SUM(e.number_of_seals) as total_seals,
                SUM(e.number_of_bags) as total_bags,
                COUNT(DISTINCT e.buyer) as unique_buyers,
                COUNT(DISTINCT e.site_of_loading_id) as unique_sites,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_processing_days,
                MIN(e.created_at) as first_export_date,
                MAX(e.created_at) as last_export_date
              FROM clients_t c
              LEFT JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name, c.client_type, c.contact_person, c.email
              HAVING total_exports > 0
              ORDER BY total_exports DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Details Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientFinancialSummary()
  {
    try {
      $sql = "SELECT 
                c.id,
                COALESCE(c.short_name, c.company_name) as client_name,
                SUM(e.ceec_amount) as total_ceec,
                SUM(e.cgea_amount) as total_cgea,
                SUM(e.occ_amount) as total_occ,
                SUM(e.lmc_amount) as total_lmc,
                SUM(e.ogefrem_amount) as total_ogefrem,
                SUM(e.liquidation_amount) as total_liquidation,
                SUM(e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees,
                AVG(e.ceec_amount) as avg_ceec,
                AVG(e.cgea_amount) as avg_cgea,
                AVG(e.liquidation_amount) as avg_liquidation
              FROM clients_t c
              INNER JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name
              ORDER BY total_agency_fees DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Financial Summary Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientMonthlyPerformance()
  {
    try {
      $sql = "SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as month,
                DATE_FORMAT(e.created_at, '%b %Y') as month_name,
                c.id as client_id,
                COALESCE(c.short_name, c.company_name) as client_name,
                COUNT(e.id) as export_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob,
                SUM(e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees
              FROM exports_t e
              INNER JOIN clients_t c ON e.subscriber_id = c.id AND c.display = 'Y'
              WHERE e.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND e.display = 'Y'
              GROUP BY DATE_FORMAT(e.created_at, '%Y-%m'), DATE_FORMAT(e.created_at, '%b %Y'), c.id, c.short_name, c.company_name
              ORDER BY month ASC, export_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Monthly Performance Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientAgencyBreakdown()
  {
    try {
      $sql = "SELECT 
                c.id,
                COALESCE(c.short_name, c.company_name) as client_name,
                COUNT(CASE WHEN e.ceec_amount > 0 THEN 1 END) as ceec_count,
                COUNT(CASE WHEN e.cgea_amount > 0 THEN 1 END) as cgea_count,
                COUNT(CASE WHEN e.occ_amount > 0 THEN 1 END) as occ_count,
                COUNT(CASE WHEN e.lmc_amount > 0 THEN 1 END) as lmc_count,
                COUNT(CASE WHEN e.ogefrem_amount > 0 THEN 1 END) as ogefrem_count,
                AVG(DATEDIFF(e.ceec_out_date, e.ceec_in_date)) as avg_ceec_days,
                AVG(DATEDIFF(e.min_div_out_date, e.min_div_in_date)) as avg_mindiv_days
              FROM clients_t c
              INNER JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name
              ORDER BY client_name ASC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Agency Breakdown Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientSealUsage()
  {
    try {
      $sql = "SELECT 
                c.id,
                COALESCE(c.short_name, c.company_name) as client_name,
                SUM(e.number_of_seals) as total_seals,
                AVG(e.number_of_seals) as avg_seals_per_export,
                MAX(e.number_of_seals) as max_seals,
                COUNT(CASE WHEN e.dgda_seal_no IS NOT NULL AND e.dgda_seal_no != '' THEN 1 END) as with_dgda_seal
              FROM clients_t c
              INNER JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y' AND e.number_of_seals > 0
              GROUP BY c.id, c.short_name, c.company_name
              ORDER BY total_seals DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Seal Usage Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientBuyerRelationship()
  {
    try {
      $sql = "SELECT 
                c.id as client_id,
                COALESCE(c.short_name, c.company_name) as client_name,
                e.buyer,
                COUNT(e.id) as export_count,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob
              FROM clients_t c
              INNER JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y' 
                AND e.buyer IS NOT NULL 
                AND e.buyer != ''
              GROUP BY c.id, c.short_name, c.company_name, e.buyer
              ORDER BY client_name ASC, export_count DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Buyer Relationship Error: " . $e->getMessage());
      return [];
    }
  }

  private function getClientComparison()
  {
    try {
      $sql = "SELECT 
                c.id,
                COALESCE(c.short_name, c.company_name) as client_name,
                COUNT(e.id) as total_exports,
                SUM(e.weight) as total_weight,
                SUM(e.fob) as total_fob_value,
                ROUND((SUM(e.fob) / NULLIF(SUM(e.weight), 0)), 2) as fob_per_kg,
                ROUND((COUNT(CASE WHEN e.clearing_status = 6 THEN 1 END) / COUNT(e.id)) * 100, 1) as clearance_rate,
                AVG(DATEDIFF(e.exit_drc_date, e.loading_date)) as avg_cycle_time,
                SUM(e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) as total_agency_fees,
                ROUND((SUM(e.ceec_amount + e.cgea_amount + e.occ_amount + e.lmc_amount + e.ogefrem_amount) / COUNT(e.id)), 2) as avg_fees_per_export
              FROM clients_t c
              INNER JOIN exports_t e ON c.id = e.subscriber_id AND e.display = 'Y'
              WHERE c.display = 'Y'
              GROUP BY c.id, c.short_name, c.company_name
              HAVING total_exports > 0
              ORDER BY total_exports DESC";
      
      return $this->db->customQuery($sql) ?: [];
    } catch (Exception $e) {
      error_log("Client Comparison Error: " . $e->getMessage());
      return [];
    }
  }

  // ==================== EXCEL EXPORT METHODS ====================

  public function exportOverview()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getAllExportsDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Buyer', 'Kind', 'Transport Mode', 'Weight', 'FOB', 'Currency', 'Status', 'Loading Site', 'Loading Date', 'Exit Date', 'Created'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    // Style header
    $sheet->getStyle('A1:N1')->getFont()->setBold(true);
    $sheet->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
    $sheet->getStyle('A1:N1')->getFont()->getColor()->setRGB('FFFFFF');
    
    // Data
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['buyer']);
      $sheet->setCellValue('E' . $row, $item['kind_name']);
      $sheet->setCellValue('F' . $row, $item['transport_mode_name']);
      $sheet->setCellValue('G' . $row, $item['weight']);
      $sheet->setCellValue('H' . $row, $item['fob']);
      $sheet->setCellValue('I' . $row, $item['currency_name']);
      $sheet->setCellValue('J' . $row, $item['clearing_status_name']);
      $sheet->setCellValue('K' . $row, $item['loading_site_name']);
      $sheet->setCellValue('L' . $row, $item['loading_date']);
      $sheet->setCellValue('M' . $row, $item['exit_drc_date']);
      $sheet->setCellValue('N' . $row, $item['created_at']);
      $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'N') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Overview_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportLogistics()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getLogisticsDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Loading Date', 'Dispatch Date', 'Kanyaka Arrival', 'Kanyaka Departure', 'Border Arrival', 'Exit DRC', 'Horse', 'Trailer 1', 'Container', 'Wagon', 'Weight', 'Site', 'Status', 'Total Days'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
    $sheet->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('70AD47');
    $sheet->getStyle('A1:Q1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['loading_date']);
      $sheet->setCellValue('E' . $row, $item['dispatch_deliver_date']);
      $sheet->setCellValue('F' . $row, $item['kanyaka_arrival_date']);
      $sheet->setCellValue('G' . $row, $item['kanyaka_departure_date']);
      $sheet->setCellValue('H' . $row, $item['border_arrival_date']);
      $sheet->setCellValue('I' . $row, $item['exit_drc_date']);
      $sheet->setCellValue('J' . $row, $item['horse']);
      $sheet->setCellValue('K' . $row, $item['trailer_1']);
      $sheet->setCellValue('L' . $row, $item['container']);
      $sheet->setCellValue('M' . $row, $item['wagon_ref']);
      $sheet->setCellValue('N' . $row, $item['weight']);
      $sheet->setCellValue('O' . $row, $item['loading_site']);
      $sheet->setCellValue('P' . $row, $item['status']);
      $sheet->setCellValue('Q' . $row, $item['total_days']);
      $row++;
    }
    
    foreach (range('A', 'Q') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Logistics_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

public function exportDelay()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getDelayDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'CEEC In', 'CEEC Out', 'CEEC Days', 'MinDiv In', 'MinDiv Out', 'MinDiv Days', 'DGDA In', 'DGDA Out', 'Customs Days', 'Border Arrival', 'Exit DRC', 'Exit Days'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:O1')->getFont()->setBold(true);
    $sheet->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFC000');
    $sheet->getStyle('A1:O1')->getFont()->getColor()->setRGB('000000');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['ceec_in_date']);
      $sheet->setCellValue('E' . $row, $item['ceec_out_date']);
      $sheet->setCellValue('F' . $row, $item['ceec_days']);
      $sheet->setCellValue('G' . $row, $item['min_div_in_date']);
      $sheet->setCellValue('H' . $row, $item['min_div_out_date']);
      $sheet->setCellValue('I' . $row, $item['mindiv_days']);
      $sheet->setCellValue('J' . $row, $item['dgda_in_date']);
      $sheet->setCellValue('K' . $row, $item['dgda_out_date']);
      $sheet->setCellValue('L' . $row, $item['customs_days']);
      $sheet->setCellValue('M' . $row, $item['border_arrival_date']);
      $sheet->setCellValue('N' . $row, $item['exit_drc_date']);
      $sheet->setCellValue('O' . $row, $item['exit_days']);
      
      // Color code delays
      if (isset($item['customs_days']) && $item['customs_days'] > 5) {
        $sheet->getStyle('L' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000');
        $sheet->getStyle('L' . $row)->getFont()->getColor()->setRGB('FFFFFF');
      }
      
      $row++;
    }
    
    foreach (range('A', 'O') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Delay_Analysis_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportTriPhase()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getTriPhaseDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Created Date', 'Day of Month', 'Phase', 'Kind', 'Weight', 'FOB'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('5B9BD5');
    $sheet->getStyle('A1:I1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['created_at']);
      $sheet->setCellValue('E' . $row, $item['day_of_month']);
      $sheet->setCellValue('F' . $row, $item['phase_name']);
      $sheet->setCellValue('G' . $row, $item['kind_name']);
      $sheet->setCellValue('H' . $row, $item['weight']);
      $sheet->setCellValue('I' . $row, $item['fob']);
      
      // Color code by phase
      $phaseColor = '';
      if ($item['phase_name'] == 'Phase 1 (1-10)') $phaseColor = 'C6EFCE';
      elseif ($item['phase_name'] == 'Phase 2 (11-20)') $phaseColor = 'FFEB9C';
      else $phaseColor = 'FFC7CE';
      
      $sheet->getStyle('F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($phaseColor);
      
      $row++;
    }
    
    foreach (range('A', 'I') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_TriPhase_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportSite()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getSiteDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Loading Site', 'Loading Date', 'Exit Date', 'Processing Days', 'Weight', 'FOB', 'Status'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ED7D31');
    $sheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['loading_site']);
      $sheet->setCellValue('E' . $row, $item['loading_date']);
      $sheet->setCellValue('F' . $row, $item['exit_drc_date']);
      $sheet->setCellValue('G' . $row, $item['processing_days']);
      $sheet->setCellValue('H' . $row, $item['weight']);
      $sheet->setCellValue('I' . $row, $item['fob']);
      $sheet->setCellValue('J' . $row, $item['status']);
      $row++;
    }
    
    foreach (range('A', 'J') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Site_Analysis_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportFinancial()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getFinancialDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Updated headers: removed Invoice, added Weight
    $headers = ['MCA Ref', 'Client', 'Weight', 'CEEC', 'CGEA', 'OCC', 'LMC', 'OGEFREM', 'Total Agency Fees', 'Liquidation'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('44546A');
    $sheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['client_name']);
      $sheet->setCellValue('C' . $row, $item['weight']);
      $sheet->setCellValue('D' . $row, $item['ceec_amount']);
      $sheet->setCellValue('E' . $row, $item['cgea_amount']);
      $sheet->setCellValue('F' . $row, $item['occ_amount']);
      $sheet->setCellValue('G' . $row, $item['lmc_amount']);
      $sheet->setCellValue('H' . $row, $item['ogefrem_amount']);
      $sheet->setCellValue('I' . $row, $item['total_agency_fees']);
      $sheet->setCellValue('J' . $row, $item['liquidation_amount']);
      
      // Format currency columns
      $sheet->getStyle('D' . $row . ':J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
      
      $row++;
    }
    
    foreach (range('A', 'J') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Prepayment_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportSeal()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getSealDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Number of Seals', 'DGDA Seal No', 'Loading Site', 'Created Date'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:G1')->getFont()->setBold(true);
    $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('A5A5A5');
    $sheet->getStyle('A1:G1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_short_name']);
      $sheet->setCellValue('D' . $row, $item['number_of_seals']);
      $sheet->setCellValue('E' . $row, $item['dgda_seal_no']);
      $sheet->setCellValue('F' . $row, $item['loading_site']);
      $sheet->setCellValue('G' . $row, $item['created_at']);
      $row++;
    }
    
    foreach (range('A', 'G') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Seal_Management_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportAssay()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getAssayDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Lot Number', 'Number of Bags', 'Assay Date', 'Weight', 'Loading Site'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('843C0C');
    $sheet->getStyle('A1:H1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_short_name']);
      $sheet->setCellValue('D' . $row, $item['lot_number']);
      $sheet->setCellValue('E' . $row, $item['number_of_bags']);
      $sheet->setCellValue('F' . $row, $item['assay_date']);
      $sheet->setCellValue('G' . $row, $item['weight']);
      $sheet->setCellValue('H' . $row, $item['loading_site']);
      $row++;
    }
    
    foreach (range('A', 'H') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Assay_Lot_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportBuyer()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getBuyerDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Buyer', 'Client', 'Kind', 'Weight', 'FOB', 'Created Date'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('7030A0');
    $sheet->getStyle('A1:H1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['buyer']);
      $sheet->setCellValue('D' . $row, $item['client_short_name']);
      $sheet->setCellValue('E' . $row, $item['kind_name']);
      $sheet->setCellValue('F' . $row, $item['weight']);
      $sheet->setCellValue('G' . $row, $item['fob']);
      $sheet->setCellValue('H' . $row, $item['created_at']);
      $row++;
    }
    
    foreach (range('A', 'H') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Buyer_Analytics_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportClient()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $data = $this->getClientDetailed();
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Buyer', 'Weight', 'FOB', 'Agency Fees', 'Seals', 'Status', 'Created Date'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    $sheet->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('203864');
    $sheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet->setCellValue('A' . $row, $item['mca_ref']);
      $sheet->setCellValue('B' . $row, $item['invoice']);
      $sheet->setCellValue('C' . $row, $item['client_name']);
      $sheet->setCellValue('D' . $row, $item['buyer']);
      $sheet->setCellValue('E' . $row, $item['weight']);
      $sheet->setCellValue('F' . $row, $item['fob']);
      $sheet->setCellValue('G' . $row, $item['total_agency_fees']);
      $sheet->setCellValue('H' . $row, $item['number_of_seals']);
      $sheet->setCellValue('I' . $row, $item['status']);
      $sheet->setCellValue('J' . $row, $item['created_at']);
      $row++;
    }
    
    foreach (range('A', 'J') as $col) {
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $filename = 'Export_Client_Analysis_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }

  public function exportAllData()
  {
    if (!$this->loadPhpSpreadsheet()) {
      die('PhpSpreadsheet library not found');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    // ===== OVERVIEW SHEET =====
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Overview');
    $data = $this->getAllExportsDetailed();
    
    $headers = ['MCA Ref', 'Invoice', 'Client', 'Buyer', 'Kind', 'Transport Mode', 'Goods Type', 'Weight', 'FOB', 'Currency', 'Clearing Status', 'Document Status', 'Loading Site', 'Loading Date', 'Exit Date', 'License', 'Created'];
    $sheet1->fromArray($headers, NULL, 'A1');
    $sheet1->getStyle('A1:Q1')->getFont()->setBold(true);
    $sheet1->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
    $sheet1->getStyle('A1:Q1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($data as $item) {
      $sheet1->setCellValue('A' . $row, $item['mca_ref']);
      $sheet1->setCellValue('B' . $row, $item['invoice']);
      $sheet1->setCellValue('C' . $row, $item['client_name']);
      $sheet1->setCellValue('D' . $row, $item['buyer']);
      $sheet1->setCellValue('E' . $row, $item['kind_name']);
      $sheet1->setCellValue('F' . $row, $item['transport_mode_name']);
      $sheet1->setCellValue('G' . $row, $item['goods_type_name']);
      $sheet1->setCellValue('H' . $row, $item['weight']);
      $sheet1->setCellValue('I' . $row, $item['fob']);
      $sheet1->setCellValue('J' . $row, $item['currency_name']);
      $sheet1->setCellValue('K' . $row, $item['clearing_status_name']);
      $sheet1->setCellValue('L' . $row, $item['document_status_name']);
      $sheet1->setCellValue('M' . $row, $item['loading_site_name']);
      $sheet1->setCellValue('N' . $row, $item['loading_date']);
      $sheet1->setCellValue('O' . $row, $item['exit_drc_date']);
      $sheet1->setCellValue('P' . $row, $item['license_number']);
      $sheet1->setCellValue('Q' . $row, $item['created_at']);
      $row++;
    }
    
    foreach (range('A', 'Q') as $col) {
      $sheet1->getColumnDimension($col)->setAutoSize(true);
    }
    
    // ===== PREPAYMENT SHEET (renamed from Financial) =====
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Prepayment');
    $finData = $this->getFinancialDetailed();
    
    // Updated headers: removed Invoice, added Weight
    $finHeaders = ['MCA Ref', 'Client', 'Weight', 'CEEC', 'CGEA', 'OCC', 'LMC', 'OGEFREM', 'Total Fees', 'Liquidation'];
    $sheet2->fromArray($finHeaders, NULL, 'A1');
    $sheet2->getStyle('A1:J1')->getFont()->setBold(true);
    $sheet2->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('44546A');
    $sheet2->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($finData as $item) {
      $sheet2->setCellValue('A' . $row, $item['mca_ref']);
      $sheet2->setCellValue('B' . $row, $item['client_name']);
      $sheet2->setCellValue('C' . $row, $item['weight']);
      $sheet2->setCellValue('D' . $row, $item['ceec_amount']);
      $sheet2->setCellValue('E' . $row, $item['cgea_amount']);
      $sheet2->setCellValue('F' . $row, $item['occ_amount']);
      $sheet2->setCellValue('G' . $row, $item['lmc_amount']);
      $sheet2->setCellValue('H' . $row, $item['ogefrem_amount']);
      $sheet2->setCellValue('I' . $row, $item['total_agency_fees']);
      $sheet2->setCellValue('J' . $row, $item['liquidation_amount']);
      $row++;
    }
    
    foreach (range('A', 'J') as $col) {
      $sheet2->getColumnDimension($col)->setAutoSize(true);
    }
    
    // ===== CLIENT SUMMARY SHEET =====
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('Client Summary');
    $clientData = $this->getClientComparison();
    
    $clientHeaders = ['Client', 'Total Exports', 'Weight', 'FOB', '$/kg', 'Clear Rate %', 'Avg Days', 'Agency Fees', 'Avg Fees/Export'];
    $sheet3->fromArray($clientHeaders, NULL, 'A1');
    $sheet3->getStyle('A1:I1')->getFont()->setBold(true);
    $sheet3->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('203864');
    $sheet3->getStyle('A1:I1')->getFont()->getColor()->setRGB('FFFFFF');
    
    $row = 2;
    foreach ($clientData as $item) {
      $sheet3->setCellValue('A' . $row, $item['client_name']);
      $sheet3->setCellValue('B' . $row, $item['total_exports']);
      $sheet3->setCellValue('C' . $row, $item['total_weight']);
      $sheet3->setCellValue('D' . $row, $item['total_fob_value']);
      $sheet3->setCellValue('E' . $row, $item['fob_per_kg']);
      $sheet3->setCellValue('F' . $row, $item['clearance_rate']);
      $sheet3->setCellValue('G' . $row, $item['avg_cycle_time']);
      $sheet3->setCellValue('H' . $row, $item['total_agency_fees']);
      $sheet3->setCellValue('I' . $row, $item['avg_fees_per_export']);
      $row++;
    }
    
    foreach (range('A', 'I') as $col) {
      $sheet3->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Set active sheet to first
    $spreadsheet->setActiveSheetIndex(0);
    
    $filename = 'Export_Complete_Dashboard_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
  }
}