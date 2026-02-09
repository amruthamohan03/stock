<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
  :root {
    --bg-primary: #f5f7fa;
    --bg-secondary: #ffffff;
    --text-primary: #2c3e50;
    --text-secondary: #7c8a96;
    --border-color: rgba(0, 0, 0, 0.08);
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --table-hover: #f8f9fa;
    --table-border: #e9ecef;
  }

  [data-bs-theme="dark"] {
    --bg-primary: #1a1d29;
    --bg-secondary: #252836;
    --text-primary: #e4e6eb;
    --text-secondary: #b8bbc5;
    --border-color: rgba(255, 255, 255, 0.08);
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.3);
    --table-hover: #2d3142;
    --table-border: #3a3d4f;
  }

  body { background: var(--bg-primary); }

  .dashboard-header {
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
  }

  .dashboard-header h2 {
    margin: 0;
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.8rem;
  }

  .dashboard-header p {
    margin: 5px 0 0;
    color: var(--text-secondary);
    font-size: 0.95rem;
  }

  .theme-btn {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 600;
  }

  .theme-btn:hover { 
    background: var(--border-color);
    transform: translateY(-2px);
  }

  .export-btn-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn-excel {
    background: linear-gradient(135deg, #217346, #185c37);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(33, 115, 70, 0.3);
  }

  .btn-excel:hover {
    background: linear-gradient(135deg, #185c37, #0f3d24);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.4);
    color: white;
  }

  .nav-tabs {
    background: var(--bg-secondary);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 25px;
    border: 1px solid var(--border-color);
    flex-wrap: wrap;
    box-shadow: var(--shadow-sm);
  }

  .nav-tabs .nav-link {
    color: var(--text-primary);
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 0.95rem;
    margin: 3px;
  }

  .nav-tabs .nav-link:hover { 
    background: var(--bg-primary);
    transform: translateY(-2px);
  }

  .nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .kpi-card {
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    cursor: pointer;
  }

  .kpi-card:hover { 
    transform: translateY(-10px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  }

  .kpi-card.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .kpi-card.green { background: linear-gradient(135deg, #10c469 0%, #0e9f5a 100%); }
  .kpi-card.orange { background: linear-gradient(135deg, #f9c851 0%, #f7a842 100%); }
  .kpi-card.red { background: linear-gradient(135deg, #fa5c7c 0%, #f83e5e 100%); }
  .kpi-card.purple { background: linear-gradient(135deg, #5b69bc 0%, #3f4d96 100%); }
  .kpi-card.cyan { background: linear-gradient(135deg, #35b8e0 0%, #2a9dc7 100%); }
  .kpi-card.teal { background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); }
  .kpi-card.indigo { background: linear-gradient(135deg, #6610f2 0%, #5a3baa 100%); }
  .kpi-card.pink { background: linear-gradient(135deg, #e83e8c 0%, #d63384 100%); }
  .kpi-card.dark { background: linear-gradient(135deg, #343a40 0%, #23272b 100%); }
  .kpi-card.warning { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
  .kpi-card.info { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }
  .kpi-card.lime { background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%); }
  .kpi-card.amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .kpi-card.emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .kpi-card.sky { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .kpi-card.violet { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
  .kpi-card.fuchsia { background: linear-gradient(135deg, #d946ef 0%, #c026d3 100%); }
  .kpi-card.rose { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
  .kpi-card.slate { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }

  .kpi-card h3 {
    font-size: 2.8rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }

  .kpi-card h4 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
  }

  .kpi-card p {
    margin: 8px 0 0;
    font-size: 0.95rem;
    opacity: 0.95;
    font-weight: 500;
  }

  .kpi-card .icon {
    font-size: 3.5rem;
    opacity: 0.25;
    position: absolute;
    right: 25px;
    top: 25px;
  }

  .phase-card, .tracking-stage-card {
    background: linear-gradient(135deg, #ffffff, #f8fafc);
    border-radius: 16px;
    padding: 32px;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
    margin-bottom: 20px;
  }

  .phase-card:hover, .tracking-stage-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
    transform: translateY(-4px);
  }

  .phase-card::before, .tracking-stage-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
  }

  .phase-1::before { background: linear-gradient(90deg, #10b981, #059669); }
  .phase-2::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
  .phase-3::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
  .phase-total::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }

  .tracking-stage-card.stage-blue::before { background: linear-gradient(90deg, #667eea, #764ba2); }
  .tracking-stage-card.stage-orange::before { background: linear-gradient(90deg, #f9c851, #f7a842); }
  .tracking-stage-card.stage-amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
  .tracking-stage-card.stage-lime::before { background: linear-gradient(90deg, #84cc16, #65a30d); }
  .tracking-stage-card.stage-emerald::before { background: linear-gradient(90deg, #10b981, #059669); }
  .tracking-stage-card.stage-sky::before { background: linear-gradient(90deg, #0ea5e9, #0284c7); }
  .tracking-stage-card.stage-violet::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
  .tracking-stage-card.stage-green::before { background: linear-gradient(90deg, #10c469, #0e9f5a); }

  .phase-stat-number, .tracking-stat-number {
    font-size: 3.2rem;
    font-weight: 900;
    margin-bottom: 12px;
  }

  .phase-1 .phase-stat-number { color: #10b981; }
  .phase-2 .phase-stat-number { color: #f59e0b; }
  .phase-3 .phase-stat-number { color: #ef4444; }
  .phase-total .phase-stat-number { color: #3b82f6; }

  .tracking-stage-card.stage-blue .tracking-stat-number { color: #667eea; }
  .tracking-stage-card.stage-orange .tracking-stat-number { color: #f9c851; }
  .tracking-stage-card.stage-amber .tracking-stat-number { color: #f59e0b; }
  .tracking-stage-card.stage-lime .tracking-stat-number { color: #84cc16; }
  .tracking-stage-card.stage-emerald .tracking-stat-number { color: #10b981; }
  .tracking-stage-card.stage-sky .tracking-stat-number { color: #0ea5e9; }
  .tracking-stage-card.stage-violet .tracking-stat-number { color: #8b5cf6; }
  .tracking-stage-card.stage-green .tracking-stat-number { color: #10c469; }

  .phase-stat-label, .tracking-stat-label {
    font-size: 1.15rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    color: #374151;
  }

  .phase-stat-desc, .tracking-stat-desc {
    font-size: 0.9rem;
    color: #6b7280;
    font-weight: 600;
  }

  .phase-icon, .tracking-stage-icon-large {
    font-size: 3.8rem;
    margin-bottom: 16px;
  }

  .phase-1 .phase-icon { color: #10b981; }
  .phase-2 .phase-icon { color: #f59e0b; }
  .phase-3 .phase-icon { color: #ef4444; }
  .phase-total .phase-icon { color: #3b82f6; }

  [data-bs-theme="dark"] .phase-card,
  [data-bs-theme="dark"] .tracking-stage-card {
    background: linear-gradient(135deg, #252836, #1a1d29);
    border-color: #3a3d4f;
  }

  [data-bs-theme="dark"] .phase-stat-label,
  [data-bs-theme="dark"] .tracking-stat-label,
  [data-bs-theme="dark"] .phase-stat-desc,
  [data-bs-theme="dark"] .tracking-stat-desc {
    color: #e4e6eb;
  }

  .section-header {
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    border: 2px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .section-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.6rem;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .section-header p {
    margin: 8px 0 0;
    color: var(--text-secondary);
    font-size: 0.95rem;
  }

  .card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 25px;
    border-radius: 12px;
    transition: all 0.3s;
  }

  .card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
  }

  .card-body { padding: 25px; }

  .header-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-primary);
  }

  .table {
    color: var(--text-primary);
    width: 100%;
    border-collapse: collapse;
  }

  .table thead th {
    background: #f8fafc;
    font-weight: 700;
    color: #374151;
    padding: 14px 12px;
    text-align: center;
    border: 1px solid var(--border-color);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .table tbody td {
    padding: 12px;
    text-align: center;
    border: 1px solid var(--border-color);
    font-size: 0.875rem;
  }

  .table tbody tr:hover {
    background: var(--table-hover);
  }

  .table .grand-total-row {
    background: #f0f9ff !important;
    font-weight: 700;
    border-top: 3px solid #3b82f6;
  }

  .table .grand-total-row td {
    color: #1e40af;
    font-weight: 800;
    font-size: 0.95rem;
  }

  [data-bs-theme="dark"] .table thead th {
    background: var(--bg-primary);
    color: var(--text-primary);
  }

  [data-bs-theme="dark"] .table .grand-total-row {
    background: rgba(59, 130, 246, 0.15) !important;
  }

  .dataTables_wrapper .dataTables_length select,
  .dataTables_wrapper .dataTables_filter input {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    padding: 6px 12px;
    border-radius: 6px;
  }

  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate {
    color: var(--text-primary);
  }

  .page-link {
    background: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--text-primary);
  }

  .page-link:hover {
    background: var(--bg-primary);
    border-color: var(--border-color);
  }

  .page-item.active .page-link {
    background: #667eea;
    border-color: #667eea;
  }

  .tab-export-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px 20px;
    background: var(--bg-secondary);
    border-radius: 10px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .tab-export-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary);
  }

  /* DataTables Buttons Styling */
  .dt-buttons {
    margin-bottom: 15px;
  }

  .dt-button {
    background: linear-gradient(135deg, #217346, #185c37) !important;
    color: white !important;
    border: none !important;
    padding: 8px 16px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    transition: all 0.3s !important;
    box-shadow: 0 2px 8px rgba(33, 115, 70, 0.3) !important;
    margin-right: 5px !important;
  }

  .dt-button:hover {
    background: linear-gradient(135deg, #185c37, #0f3d24) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.4) !important;
  }

  [data-bs-theme="dark"] .dt-button {
    background: linear-gradient(135deg, #10b981, #059669) !important;
  }

  @media (max-width: 768px) {
    .export-btn-group {
      width: 100%;
    }
    
    .btn-excel {
      width: 100%;
    }

    .tab-export-header {
      flex-direction: column;
      gap: 15px;
    }
  }
</style>

<div class="page-content">
  <div class="page-container">

    <!-- Header -->
    <div class="dashboard-header">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h2><i class="ti ti-chart-line me-2"></i> Enhanced Export Dashboard</h2>
          <p class="mb-0">Comprehensive export analytics with maximum data insights</p>
        </div>
        <div class="export-btn-group">
          <button class="theme-btn" id="themeToggle">
            <i class="ti ti-sun" id="themeIcon"></i>
          </button>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportAllData'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export All Data
          </button>
          <a href="<?= APP_URL ?>/export" class="btn btn-secondary">
            <i class="ti ti-file-export me-1"></i> Exports
          </a>
        </div>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
          <i class="ti ti-chart-line me-1"></i>Overview
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="logistics-tab" data-bs-toggle="tab" data-bs-target="#logistics" type="button" role="tab">
          <i class="ti ti-truck me-1"></i>Logistics
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="delay-tab" data-bs-toggle="tab" data-bs-target="#delay" type="button" role="tab">
          <i class="ti ti-clock-exclamation me-1"></i>Delay KPI
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="triphase-tab" data-bs-toggle="tab" data-bs-target="#triphase" type="button" role="tab">
          <i class="ti ti-timeline me-1"></i>Tri Phase
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
          <i class="ti ti-building-warehouse me-1"></i>Site
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="prepayment-tab" data-bs-toggle="tab" data-bs-target="#prepayment" type="button" role="tab">
          <i class="ti ti-currency-dollar me-1"></i>Prepayment
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="seal-tab" data-bs-toggle="tab" data-bs-target="#seal" type="button" role="tab">
          <i class="ti ti-lock me-1"></i>Seals
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="assay-tab" data-bs-toggle="tab" data-bs-target="#assay" type="button" role="tab">
          <i class="ti ti-flask me-1"></i>Assay
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="buyer-tab" data-bs-toggle="tab" data-bs-target="#buyer" type="button" role="tab">
          <i class="ti ti-building-store me-1"></i>Buyers
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="client-tab" data-bs-toggle="tab" data-bs-target="#client" type="button" role="tab">
          <i class="ti ti-users me-1"></i>Clients
        </button>
      </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="dashboardTabsContent">
      
      <!-- ==================== OVERVIEW TAB ==================== -->
      <div class="tab-pane fade show active" id="overview" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-chart-line me-2"></i>Overview Analytics</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportOverview'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <!-- Primary KPI Row -->
        <div class="row">
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-file-export icon"></i>
              <h3><?= number_format($kpi_data['total_exports'] ?? 0) ?></h3>
              <p>Total Exports</p>
            </div>
          </div>
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="kpi-card cyan">
              <i class="ti ti-truck-delivery icon"></i>
              <h3><?= number_format($clearing_status_summary['in_transit'] ?? 0) ?></h3>
              <p>In Transit</p>
            </div>
          </div>
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-progress icon"></i>
              <h3><?= number_format($kpi_data['in_progress_exports'] ?? 0) ?></h3>
              <p>In Progress</p>
            </div>
          </div>
          <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-circle-check icon"></i>
              <h3><?= number_format($kpi_data['clearing_completed'] ?? 0) ?></h3>
              <p>Clearing Completed</p>
            </div>
          </div>
        </div>

        <!-- Time Period KPI Row -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card red">
              <i class="ti ti-calendar-event icon"></i>
              <h3><?= number_format($kpi_data['today_exports'] ?? 0) ?></h3>
              <p>Today</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-calendar-week icon"></i>
              <h3><?= number_format($kpi_data['this_week_exports'] ?? 0) ?></h3>
              <p>This Week</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card teal">
              <i class="ti ti-calendar-month icon"></i>
              <h3><?= number_format($kpi_data['this_month_exports'] ?? 0) ?></h3>
              <p>This Month</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card indigo">
              <i class="ti ti-calendar icon"></i>
              <h3><?= number_format($kpi_data['this_year_exports'] ?? 0) ?></h3>
              <p>This Year</p>
            </div>
          </div>
        </div>

        <!-- Extended KPI Row -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card violet">
              <i class="ti ti-file-invoice icon"></i>
              <h3><?= number_format($extended_kpi_data['unique_invoices'] ?? 0) ?></h3>
              <p>Unique Invoices</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card pink">
              <i class="ti ti-users icon"></i>
              <h3><?= number_format($extended_kpi_data['unique_buyers'] ?? 0) ?></h3>
              <p>Unique Buyers</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card lime">
              <i class="ti ti-lock icon"></i>
              <h3><?= number_format($extended_kpi_data['total_seals'] ?? 0) ?></h3>
              <p>Total Seals</p>
            </div>
          </div>
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card amber">
              <i class="ti ti-package icon"></i>
              <h3><?= number_format($extended_kpi_data['total_bags'] ?? 0) ?></h3>
              <p>Total Bags</p>
            </div>
          </div>
        </div>

        <!-- Transport Mode Section -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-truck me-2"></i>Transport Mode Statistics</h3>
              <p>Export distribution by transport method</p>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if (!empty($transport_mode_stats) && is_array($transport_mode_stats)): ?>
            <?php 
            $transport_colors = ['ROAD' => 'blue', 'AIR' => 'sky', 'WAGON' => 'emerald'];
            $transport_icons = ['ROAD' => 'car', 'AIR' => 'plane', 'WAGON' => 'train'];
            foreach ($transport_mode_stats as $transport): 
              $color = $transport_colors[$transport['transport_name']] ?? 'slate';
              $icon = $transport_icons[$transport['transport_name']] ?? 'truck';
            ?>
              <div class="col-xl-4 col-md-6">
                <div class="kpi-card <?= $color ?>">
                  <i class="ti ti-<?= $icon ?> icon"></i>
                  <h3><?= number_format($transport['export_count'] ?? 0) ?></h3>
                  <p><?= htmlspecialchars($transport['transport_name']) ?></p>
                  <div style="display: flex; gap: 15px; margin-top: 15px; font-size: 0.9rem; flex-wrap: wrap;">
                    <span><i class="ti ti-truck-delivery"></i> <?= number_format($transport['in_transit_count'] ?? 0) ?> Transit</span>
                    <span><i class="ti ti-progress"></i> <?= number_format($transport['in_progress_count'] ?? 0) ?> Progress</span>
                    <span><i class="ti ti-circle-check"></i> <?= number_format($transport['cleared_count'] ?? 0) ?> Cleared</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12"><div class="alert alert-info">No transport mode data available</div></div>
          <?php endif; ?>
        </div>

        <!-- Kind Distribution -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-category me-2"></i>Export Kind Distribution</h3>
              <p>Breakdown by export type</p>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if (!empty($kind_distribution) && is_array($kind_distribution)): ?>
            <?php 
            $kind_colors = ['violet', 'pink', 'amber', 'lime', 'sky', 'rose', 'emerald', 'fuchsia'];
            $kind_icons = ['tag', 'tags', 'bookmark', 'bookmarks', 'box', 'package', 'packages', 'archive'];
            foreach ($kind_distribution as $index => $kind): 
              $color = $kind_colors[$index % count($kind_colors)];
              $icon = $kind_icons[$index % count($kind_icons)];
            ?>
              <div class="col-xl-3 col-md-6">
                <div class="kpi-card <?= $color ?>">
                  <i class="ti ti-<?= $icon ?> icon"></i>
                  <h4><?= number_format($kind['export_count'] ?? 0) ?></h4>
                  <p><?= htmlspecialchars($kind['kind_name']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12"><div class="alert alert-info">No kind distribution data available</div></div>
          <?php endif; ?>
        </div>

        <!-- Recent Exports with MCA Reference -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-clock me-2"></i>Recent Exports (Last 50)
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="recentExportsTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Buyer</th>
                        <th>Kind</th>
                        <th>Weight</th>
                        <th>FOB</th>
                        <th>Status</th>
                        <th>Created</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($recent_exports) && is_array($recent_exports)): ?>
                        <?php foreach ($recent_exports as $export): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($export['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($export['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($export['client_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($export['buyer'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($export['kind_name'] ?? 'N/A') ?></td>
                            <td><?= number_format($export['weight'] ?? 0, 2) ?></td>
                            <td><?= number_format($export['fob'] ?? 0, 2) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($export['clearing_status_name'] ?? 'Pending') ?></span></td>
                            <td><?= date('M d, Y', strtotime($export['created_at'])) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END OVERVIEW TAB -->

<!-- ==================== LOGISTICS TAB ==================== -->
      <div class="tab-pane fade" id="logistics" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-truck me-2"></i>Logistics Analytics</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportLogistics'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-route me-2"></i>Road Shipment Tracking Journey</h3>
              <p>Track your shipments through every stage from loading to exit</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-blue">
              <div class="tracking-stage-icon-large"><i class="ti ti-truck"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['total_road_shipments'] ?? 0) ?></div>
              <div class="tracking-stat-label">Total Road Shipments</div>
              <div class="tracking-stat-desc">All active road transport entries</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-orange">
              <div class="tracking-stage-icon-large"><i class="ti ti-clock-pause"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_loading'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Loading</div>
              <div class="tracking-stat-desc">Not yet loaded</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-amber">
              <div class="tracking-stage-icon-large"><i class="ti ti-clock-hour-4"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_dispatch'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Dispatch</div>
              <div class="tracking-stat-desc">Loaded but not dispatched</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-lime">
              <div class="tracking-stage-icon-large"><i class="ti ti-flag"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_kanyaka_arrival'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Kanyaka Arrival</div>
              <div class="tracking-stat-desc">Dispatched but not at Kanyaka</div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-emerald">
              <div class="tracking-stage-icon-large"><i class="ti ti-send"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_kanyaka_departure'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Kanyaka Departure</div>
              <div class="tracking-stat-desc">At Kanyaka but not departed</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-sky">
              <div class="tracking-stage-icon-large"><i class="ti ti-map-pin"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_border_arrival'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Border Arrival</div>
              <div class="tracking-stat-desc">Departed but not at border</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-violet">
              <div class="tracking-stage-icon-large"><i class="ti ti-arrows-right"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['waiting_exit'] ?? 0) ?></div>
              <div class="tracking-stat-label">Waiting Exit DRC</div>
              <div class="tracking-stat-desc">At border but not exited</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="tracking-stage-card stage-green">
              <div class="tracking-stage-icon-large"><i class="ti ti-circle-check"></i></div>
              <div class="tracking-stat-number"><?= number_format($tracking_stages['completed_road_journey'] ?? 0) ?></div>
              <div class="tracking-stat-label">Completed Road Journey</div>
              <div class="tracking-stat-desc">All stages complete</div>
            </div>
          </div>
        </div>

        <!-- Logistics Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Logistics Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="logisticsDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Loading Date</th>
                        <th>Kanyaka Arrival</th>
                        <th>Border Arrival</th>
                        <th>Exit DRC</th>
                        <th>Horse</th>
                        <th>Container</th>
                        <th>Weight</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($logistics_detailed) && is_array($logistics_detailed)): ?>
                        <?php foreach ($logistics_detailed as $log): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($log['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($log['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($log['client_name'] ?? 'N/A') ?></td>
                            <td><?= $log['loading_date'] ? date('M d, Y', strtotime($log['loading_date'])) : '-' ?></td>
                            <td><?= $log['kanyaka_arrival_date'] ? date('M d, Y', strtotime($log['kanyaka_arrival_date'])) : '-' ?></td>
                            <td><?= $log['border_arrival_date'] ? date('M d, Y', strtotime($log['border_arrival_date'])) : '-' ?></td>
                            <td><?= $log['exit_drc_date'] ? date('M d, Y', strtotime($log['exit_drc_date'])) : '-' ?></td>
                            <td><?= htmlspecialchars($log['horse'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($log['container'] ?? '-') ?></td>
                            <td><?= number_format($log['weight'] ?? 0, 2) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($log['status'] ?? 'N/A') ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="11" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Agency Processing Times -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-building-bank me-2"></i>Agency Processing Times</h3>
              <p>Average processing days by government agencies</p>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if (!empty($agency_processing_times) && is_array($agency_processing_times)): ?>
            <?php 
            $agency_colors = ['blue', 'green', 'orange', 'purple'];
            foreach ($agency_processing_times as $index => $agency): 
              $color = $agency_colors[$index % count($agency_colors)];
            ?>
              <div class="col-xl-3 col-md-6">
                <div class="kpi-card <?= $color ?>">
                  <i class="ti ti-building icon"></i>
                  <h3><?= number_format($agency['avg_processing_days'] ?? 0, 1) ?></h3>
                  <p><?= htmlspecialchars($agency['agency_name']) ?> - Avg Days</p>
                  <div style="font-size: 0.9rem; margin-top: 12px;">
                    <span><i class="ti ti-file"></i> <?= number_format($agency['processed_count'] ?? 0) ?> Processed</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12"><div class="alert alert-info">No agency processing data available</div></div>
          <?php endif; ?>
        </div>

      </div>
      <!-- END LOGISTICS TAB -->

      <!-- ==================== DELAY KPI TAB ==================== -->
      <div class="tab-pane fade" id="delay" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-clock-exclamation me-2"></i>Delay Analysis</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportDelay'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-clock-exclamation me-2"></i>Delay Analysis Overview</h3>
              <p>Comprehensive delay metrics across all export stages</p>
            </div>
          </div>
        </div>

        <!-- Delay Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-building icon"></i>
              <h3><?= number_format($delay_overview['ceec_delays'] ?? 0) ?></h3>
              <p>CEEC Delays (>3 days)</p>
              <div style="font-size: 0.9rem; margin-top: 12px;">
                <span>Avg: <?= number_format($delay_overview['avg_ceec_delay'] ?? 0, 1) ?> days</span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-building-bank icon"></i>
              <h3><?= number_format($delay_overview['mindiv_delays'] ?? 0) ?></h3>
              <p>Min Div Delays (>2 days)</p>
              <div style="font-size: 0.9rem; margin-top: 12px;">
                <span>Avg: <?= number_format($delay_overview['avg_mindiv_delay'] ?? 0, 1) ?> days</span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card red">
              <i class="ti ti-file-certificate icon"></i>
              <h3><?= number_format($delay_overview['customs_delays'] ?? 0) ?></h3>
              <p>DGDA Delays (>5 days)</p>
              <div style="font-size: 0.9rem; margin-top: 12px;">
                <span>Avg: <?= number_format($delay_overview['avg_customs_delay'] ?? 0, 1) ?> days</span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card cyan">
              <i class="ti ti-arrow-right icon"></i>
              <h3><?= number_format($delay_overview['exit_delays'] ?? 0) ?></h3>
              <p>Exit Delays (>3 days)</p>
              <div style="font-size: 0.9rem; margin-top: 12px;">
                <span>Avg: <?= number_format($delay_overview['avg_exit_delay'] ?? 0, 1) ?> days</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Delay Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Delay Analysis
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="delayDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>CEEC Days</th>
                        <th>MinDiv Days</th>
                        <th>Customs Days</th>
                        <th>Exit Days</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($delay_detailed) && is_array($delay_detailed)): ?>
                        <?php foreach ($delay_detailed as $delay): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($delay['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($delay['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($delay['client_name'] ?? 'N/A') ?></td>
                            <td><?= $delay['ceec_days'] ?? '-' ?></td>
                            <td><?= $delay['mindiv_days'] ?? '-' ?></td>
                            <td>
                              <?php 
                              $customsDays = $delay['customs_days'] ?? 0;
                              $badgeClass = $customsDays > 5 ? 'bg-danger' : 'bg-success';
                              ?>
                              <span class="badge <?= $badgeClass ?>"><?= $customsDays ?></span>
                            </td>
                            <td><?= $delay['exit_days'] ?? '-' ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Delay by Client -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-users me-2"></i>Top 10 Clients with Delays
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="delayByClientTable">
                    <thead>
                      <tr>
                        <th>Client Name</th>
                        <th>Total Exports</th>
                        <th>Avg Customs Delay</th>
                        <th>Delayed Count</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($delay_by_client) && is_array($delay_by_client)): ?>
                        <?php foreach ($delay_by_client as $client): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($client['client_name']) ?></strong></td>
                            <td><?= number_format($client['total_exports']) ?></td>
                            <td><span class="badge bg-warning"><?= number_format($client['avg_customs_delay'], 1) ?> days</span></td>
                            <td><span class="badge bg-danger"><?= number_format($client['delayed_count']) ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END DELAY KPI TAB -->

      <!-- ==================== TRI PHASE TAB ==================== -->
      <div class="tab-pane fade" id="triphase" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-timeline me-2"></i>Tri-Phase Analysis</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportTriPhase'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-timeline me-2"></i>Tri-Phase Monthly Division</h3>
              <p>Exports divided into 3 phases per month: Days 1-10, 11-20, 21-End based on creation date</p>
            </div>
          </div>
        </div>

        <!-- Current Month Overview -->
        <div class="row">
          <div class="col-12">
            <div class="alert alert-info">
              <h5><i class="ti ti-calendar me-2"></i>Current Month: <?= date('F Y') ?></h5>
              <p class="mb-0">Showing export distribution across three phases of the month</p>
            </div>
          </div>
        </div>

        <!-- Phase Cards -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="phase-card phase-1">
              <div class="phase-icon"><i class="ti ti-calendar-event"></i></div>
              <div class="phase-stat-number"><?= number_format($triphase_overview['phase1_count'] ?? 0) ?></div>
              <div class="phase-stat-label">01 To 10 Days</div>
              <div class="phase-stat-desc">Early month activities</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="phase-card phase-2">
              <div class="phase-icon"><i class="ti ti-calendar-week"></i></div>
              <div class="phase-stat-number"><?= number_format($triphase_overview['phase2_count'] ?? 0) ?></div>
              <div class="phase-stat-label">11 To 20 Days</div>
              <div class="phase-stat-desc">Mid month activities</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="phase-card phase-3">
              <div class="phase-icon"><i class="ti ti-calendar-exclamation"></i></div>
              <div class="phase-stat-number"><?= number_format($triphase_overview['phase3_count'] ?? 0) ?></div>
              <div class="phase-stat-label">21 To EOM</div>
              <div class="phase-stat-desc">Late month activities</div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="phase-card phase-total">
              <div class="phase-icon"><i class="ti ti-folder-open"></i></div>
              <div class="phase-stat-number"><?= number_format(($triphase_overview['phase1_count'] ?? 0) + ($triphase_overview['phase2_count'] ?? 0) + ($triphase_overview['phase3_count'] ?? 0)) ?></div>
              <div class="phase-stat-label">Grand Total</div>
              <div class="phase-stat-desc">All files in period</div>
            </div>
          </div>
        </div>

        <!-- Tri-Phase Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Tri-Phase Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="triphaseDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Day of Month</th>
                        <th>Phase</th>
                        <th>Kind</th>
                        <th>Weight</th>
                        <th>FOB</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($triphase_detailed) && is_array($triphase_detailed)): ?>
                        <?php foreach ($triphase_detailed as $tri): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($tri['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($tri['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($tri['client_name'] ?? 'N/A') ?></td>
                            <td><?= $tri['day_of_month'] ?></td>
                            <td>
                              <?php
                              $badgeClass = 'bg-info';
                              if ($tri['phase_name'] == 'Phase 1 (1-10)') $badgeClass = 'bg-success';
                              elseif ($tri['phase_name'] == 'Phase 2 (11-20)') $badgeClass = 'bg-warning';
                              elseif ($tri['phase_name'] == 'Phase 3 (21-End)') $badgeClass = 'bg-danger';
                              ?>
                              <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($tri['phase_name']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($tri['kind_name'] ?? 'N/A') ?></td>
                            <td><?= number_format($tri['weight'] ?? 0, 2) ?></td>
                            <td><?= number_format($tri['fob'] ?? 0, 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Client Performance by Phase -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">Client Performance by Phase</h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="triphaseClientTable">
                    <thead>
                      <tr>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Phase 1 (1-10)</th>
                        <th>Phase 2 (11-20)</th>
                        <th>Phase 3 (21-End)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($triphase_by_client) && is_array($triphase_by_client)): ?>
                        <?php foreach ($triphase_by_client as $client): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($client['client_name'] ?? 'N/A') ?></strong></td>
                            <td><?= number_format($client['total_exports'] ?? 0) ?></td>
                            <td><span class="badge bg-success"><?= number_format($client['phase1_count'] ?? 0) ?></span></td>
                            <td><span class="badge bg-warning"><?= number_format($client['phase2_count'] ?? 0) ?></span></td>
                            <td><span class="badge bg-danger"><?= number_format($client['phase3_count'] ?? 0) ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END TRI PHASE TAB -->

      <!-- ==================== LOADING SITE TAB ==================== -->
      <div class="tab-pane fade" id="site" role="tabpanel">

        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-building-warehouse me-2"></i>Site Analytics</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportSite'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <!-- Period Cards Row -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-calendar me-2"></i>Export Activity by Period</h3>
              <p>Loading site exports based on creation date</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card red">
              <i class="ti ti-calendar-event icon"></i>
              <h3><?= number_format($site_overview['today_exports'] ?? 0) ?></h3>
              <p>Today's Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-calendar-week icon"></i>
              <h3><?= number_format($site_overview['this_week_exports'] ?? 0) ?></h3>
              <p>This Week's Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card teal">
              <i class="ti ti-calendar-month icon"></i>
              <h3><?= number_format($site_overview['this_month_exports'] ?? 0) ?></h3>
              <p>This Month's Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card indigo">
              <i class="ti ti-calendar icon"></i>
              <h3><?= number_format($site_overview['this_year_exports'] ?? 0) ?></h3>
              <p>This Year's Exports</p>
            </div>
          </div>
        </div>

        <!-- Site-wise Breakdown Cards -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-chart-bar me-2"></i>Site-wise Export Distribution</h3>
              <p>Detailed breakdown by loading site</p>
            </div>
          </div>
        </div>

        <div class="row">
          <?php if (!empty($loading_site_analysis) && is_array($loading_site_analysis)): ?>
            <?php 
            $site_colors = ['blue', 'green', 'orange', 'purple', 'cyan', 'teal', 'indigo', 'pink', 'amber', 'lime', 'sky', 'violet', 'rose', 'emerald', 'fuchsia'];
            foreach ($loading_site_analysis as $index => $site): 
              $color = $site_colors[$index % count($site_colors)];
            ?>
              <div class="col-xl-4 col-md-6">
                <div class="kpi-card <?= $color ?>">
                  <i class="ti ti-building-warehouse icon"></i>
                  <h3><?= number_format($site['export_count'] ?? 0) ?></h3>
                  <p><?= htmlspecialchars($site['site_name']) ?></p>
                  <div style="display: flex; gap: 15px; margin-top: 12px; font-size: 0.9rem; flex-wrap: wrap;">
                    <span><i class="ti ti-circle-check"></i> <?= number_format($site['cleared_count'] ?? 0) ?> Cleared</span>
                    <span><i class="ti ti-progress"></i> <?= number_format($site['in_progress_count'] ?? 0) ?> Progress</span>
                    <span><i class="ti ti-truck"></i> <?= number_format($site['in_transit_count'] ?? 0) ?> Transit</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>No loading site data available
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Site Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Site Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="siteDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Loading Site</th>
                        <th>Loading Date</th>
                        <th>Exit Date</th>
                        <th>Processing Days</th>
                        <th>Weight</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($site_detailed) && is_array($site_detailed)): ?>
                        <?php foreach ($site_detailed as $site): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($site['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($site['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($site['client_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($site['loading_site'] ?? 'N/A') ?></td>
                            <td><?= $site['loading_date'] ? date('M d, Y', strtotime($site['loading_date'])) : '-' ?></td>
                            <td><?= $site['exit_drc_date'] ? date('M d, Y', strtotime($site['exit_drc_date'])) : '-' ?></td>
                            <td><?= $site['processing_days'] ?? '-' ?></td>
                            <td><?= number_format($site['weight'] ?? 0, 2) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($site['status'] ?? 'N/A') ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END LOADING SITE TAB -->

<!-- ==================== PREPAYMENT TAB ==================== -->
      <div class="tab-pane fade" id="prepayment" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-currency-dollar me-2"></i>Prepayment Analytics</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportFinancial'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-currency-dollar me-2"></i>Prepayment Overview</h3>
              <p>Comprehensive prepayment and agency fee analytics</p>
            </div>
          </div>
        </div>

        <!-- Prepayment Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-building icon"></i>
              <h3>$<?= number_format($financial_overview['total_ceec'] ?? 0, 0) ?></h3>
              <p>Total CEEC</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-building-bank icon"></i>
              <h3>$<?= number_format($financial_overview['total_cgea'] ?? 0, 0) ?></h3>
              <p>Total CGEA</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-certificate icon"></i>
              <h3>$<?= number_format($financial_overview['total_occ'] ?? 0, 0) ?></h3>
              <p>Total OCC</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-file-dollar icon"></i>
              <h3>$<?= number_format($financial_overview['total_lmc'] ?? 0, 0) ?></h3>
              <p>Total LMC</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card cyan">
              <i class="ti ti-ship icon"></i>
              <h3>$<?= number_format($financial_overview['total_ogefrem'] ?? 0, 0) ?></h3>
              <p>Total OGEFREM</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card teal">
              <i class="ti ti-cash icon"></i>
              <h3>$<?= number_format($financial_overview['total_liquidation'] ?? 0, 0) ?></h3>
              <p>Total Liquidation</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card indigo">
              <i class="ti ti-chart-pie icon"></i>
              <h3>$<?= number_format($financial_overview['total_agency_fees'] ?? 0, 0) ?></h3>
              <p>Total Agency Fees</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card pink">
              <i class="ti ti-file-check icon"></i>
              <h3><?= number_format($financial_overview['liquidated_count'] ?? 0) ?></h3>
              <p>Liquidated Count</p>
            </div>
          </div>
        </div>

        <!-- Prepayment Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Prepayment Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="prepaymentDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Client</th>
                        <th>Weight</th>
                        <th>CEEC</th>
                        <th>CGEA</th>
                        <th>OCC</th>
                        <th>LMC</th>
                        <th>OGEFREM</th>
                        <th>Total Fees</th>
                        <th>Liquidation</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($financial_detailed) && is_array($financial_detailed)): ?>
                        <?php foreach ($financial_detailed as $fin): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($fin['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($fin['client_name'] ?? 'N/A') ?></td>
                            <td><?= number_format($fin['weight'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($fin['ceec_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($fin['cgea_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($fin['occ_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($fin['lmc_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($fin['ogefrem_amount'] ?? 0, 2) ?></td>
                            <td><strong>$<?= number_format($fin['total_agency_fees'] ?? 0, 2) ?></strong></td>
                            <td>$<?= number_format($fin['liquidation_amount'] ?? 0, 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Agency Fees Summary -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-building-bank me-2"></i>Agency Fees Breakdown
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="agencyFeesTable">
                    <thead>
                      <tr>
                        <th>Agency</th>
                        <th>Total Amount</th>
                        <th>Processed Count</th>
                        <th>Avg Amount</th>
                        <th>Max Amount</th>
                        <th>Min Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($agency_fees_summary) && is_array($agency_fees_summary)): ?>
                        <?php foreach ($agency_fees_summary as $agency): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($agency['agency_name']) ?></strong></td>
                            <td>$<?= number_format($agency['total_amount'] ?? 0, 2) ?></td>
                            <td><?= number_format($agency['processed_count'] ?? 0) ?></td>
                            <td>$<?= number_format($agency['avg_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($agency['max_amount'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($agency['min_amount'] ?? 0, 2) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END PREPAYMENT TAB -->

      <!-- ==================== SEAL MANAGEMENT TAB ==================== -->
      <div class="tab-pane fade" id="seal" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-lock me-2"></i>Seal Management</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportSeal'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-lock me-2"></i>Seal Management Overview</h3>
              <p>Comprehensive seal tracking and analytics</p>
            </div>
          </div>
        </div>

        <!-- Seal Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-lock icon"></i>
              <h3><?= number_format($seal_overview['total_seals_used'] ?? 0) ?></h3>
              <p>Total Seals on Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-package icon"></i>
              <h3><?= number_format($seal_overview['shipments_with_seals'] ?? 0) ?></h3>
              <p>Shipments with Seals</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-chart-line icon"></i>
              <h3><?= number_format($seal_overview['avg_seals_per_shipment'] ?? 0, 1) ?></h3>
              <p>Avg Seals/Shipment</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-arrow-up icon"></i>
              <h3><?= number_format($seal_overview['max_seals_shipment'] ?? 0) ?></h3>
              <p>Max Seals (Single)</p>
            </div>
          </div>
        </div>

        <!-- Individual Seal Tracking -->
        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-database me-2"></i>Individual Seal Inventory</h3>
              <p>Track individual seal numbers and their status</p>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card cyan">
              <i class="ti ti-tag icon"></i>
              <h3><?= number_format($seal_individual_tracking['total_seals'] ?? 0) ?></h3>
              <p>Total Individual Seals</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card teal">
              <i class="ti ti-circle-check icon"></i>
              <h3><?= number_format($seal_individual_tracking['available_seals'] ?? 0) ?></h3>
              <p>Available</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card indigo">
              <i class="ti ti-lock-check icon"></i>
              <h3><?= number_format($seal_individual_tracking['used_seals'] ?? 0) ?></h3>
              <p>Used</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card red">
              <i class="ti ti-alert-triangle icon"></i>
              <h3><?= number_format($seal_individual_tracking['damaged_seals'] ?? 0) ?></h3>
              <p>Damaged</p>
            </div>
          </div>
        </div>

        <!-- Seal Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Seal Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="sealDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Number of Seals</th>
                        <th>DGDA Seal No</th>
                        <th>Loading Site</th>
                        <th>Created</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($seal_detailed) && is_array($seal_detailed)): ?>
                        <?php foreach ($seal_detailed as $seal): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($seal['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($seal['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($seal['client_short_name'] ?? 'N/A') ?></td>
                            <td><span class="badge bg-primary"><?= number_format($seal['number_of_seals'] ?? 0) ?></span></td>
                            <td><?= htmlspecialchars($seal['dgda_seal_no'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($seal['loading_site'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($seal['created_at'])) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seals by Master -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-package me-2"></i>Seals by Master Number
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="sealsByMasterTable">
                    <thead>
                      <tr>
                        <th>Master Seal</th>
                        <th>Total</th>
                        <th>Available</th>
                        <th>Used</th>
                        <th>Damaged</th>
                        <th>Usage %</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($seals_by_master) && is_array($seals_by_master)): ?>
                        <?php foreach ($seals_by_master as $seal): ?>
                          <?php 
                          $usage_percent = $seal['total_individual_seals'] > 0 
                            ? round(($seal['used'] / $seal['total_individual_seals']) * 100, 1) 
                            : 0;
                          ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($seal['master_seal']) ?></strong></td>
                            <td><?= number_format($seal['total_individual_seals']) ?></td>
                            <td><span class="badge bg-success"><?= number_format($seal['available']) ?></span></td>
                            <td><span class="badge bg-info"><?= number_format($seal['used']) ?></span></td>
                            <td><span class="badge bg-danger"><?= number_format($seal['damaged']) ?></span></td>
                            <td>
                              <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $usage_percent ?>%">
                                  <?= $usage_percent ?>%
                                </div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No seal data available</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END SEAL TAB -->

      <!-- ==================== ASSAY & LOT TAB ==================== -->
      <div class="tab-pane fade" id="assay" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-flask me-2"></i>Assay & Lot Management</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportAssay'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-flask me-2"></i>Assay & Lot Management</h3>
              <p>Assay analytics and lot tracking</p>
            </div>
          </div>
        </div>

        <!-- Assay Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-flask icon"></i>
              <h3><?= number_format($assay_overview['total_assays'] ?? 0) ?></h3>
              <p>Total Assays</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-barcode icon"></i>
              <h3><?= number_format($assay_overview['unique_lots'] ?? 0) ?></h3>
              <p>Unique Lots</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-package icon"></i>
              <h3><?= number_format($bags_analytics['total_bags'] ?? 0) ?></h3>
              <p>Total Bags</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-clock icon"></i>
              <h3><?= number_format($assay_overview['avg_days_to_assay'] ?? 0, 1) ?></h3>
              <p>Avg Days to Assay</p>
            </div>
          </div>
        </div>

        <!-- Assay Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Assay Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="assayDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Lot Number</th>
                        <th>Bags</th>
                        <th>Assay Date</th>
                        <th>Weight</th>
                        <th>Loading Site</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($assay_detailed) && is_array($assay_detailed)): ?>
                        <?php foreach ($assay_detailed as $assay): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($assay['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($assay['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($assay['client_short_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($assay['lot_number'] ?? 'N/A') ?></td>
                            <td><?= number_format($assay['number_of_bags'] ?? 0) ?></td>
                            <td><?= $assay['assay_date'] ? date('M d, Y', strtotime($assay['assay_date'])) : '-' ?></td>
                            <td><?= number_format($assay['weight'] ?? 0, 2) ?></td>
                            <td><?= htmlspecialchars($assay['loading_site'] ?? 'N/A') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Lot Analytics -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-barcode me-2"></i>Lot Analytics (Top 50)
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="lotAnalyticsTable">
                    <thead>
                      <tr>
                        <th>Lot Number</th>
                        <th>Shipments</th>
                        <th>Total Weight</th>
                        <th>Total FOB</th>
                        <th>First Assay</th>
                        <th>Last Assay</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($lot_analytics) && is_array($lot_analytics)): ?>
                        <?php foreach ($lot_analytics as $lot): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($lot['lot_number']) ?></strong></td>
                            <td><?= number_format($lot['shipment_count']) ?></td>
                            <td><?= number_format($lot['total_weight'], 2) ?> kg</td>
                            <td>$<?= number_format($lot['total_fob'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($lot['first_assay'])) ?></td>
                            <td><?= date('M d, Y', strtotime($lot['last_assay'])) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END ASSAY TAB -->

      <!-- ==================== BUYER ANALYTICS TAB ==================== -->
      <div class="tab-pane fade" id="buyer" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-building-store me-2"></i>Buyer Analytics</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportBuyer'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-building-store me-2"></i>Buyer Analytics</h3>
              <p>Comprehensive buyer performance metrics</p>
            </div>
          </div>
        </div>

        <!-- Buyer Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-users icon"></i>
              <h3><?= number_format($buyer_overview['total_buyers'] ?? 0) ?></h3>
              <p>Total Buyers</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-file-export icon"></i>
              <h3><?= number_format($buyer_overview['total_exports'] ?? 0) ?></h3>
              <p>Total Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-weight icon"></i>
              <h3><?= number_format($buyer_overview['total_weight'] ?? 0, 0) ?></h3>
              <p>Total Weight (kg)</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-currency-dollar icon"></i>
              <h3>$<?= number_format($buyer_overview['total_fob'] ?? 0, 0) ?></h3>
              <p>Total FOB Value</p>
            </div>
          </div>
        </div>

        <!-- Buyer Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Buyer Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="buyerDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Buyer</th>
                        <th>Client</th>
                        <th>Kind</th>
                        <th>Weight</th>
                        <th>FOB</th>
                        <th>Created</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($buyer_detailed) && is_array($buyer_detailed)): ?>
                        <?php foreach ($buyer_detailed as $buyer): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($buyer['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($buyer['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($buyer['buyer'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($buyer['client_short_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($buyer['kind_name'] ?? 'N/A') ?></td>
                            <td><?= number_format($buyer['weight'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($buyer['fob'] ?? 0, 2) ?></td>
                            <td><?= date('M d, Y', strtotime($buyer['created_at'])) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Buyer Analytics Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-chart-bar me-2"></i>Top 100 Buyers
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="buyerAnalyticsTable">
                    <thead>
                      <tr>
                        <th>Buyer</th>
                        <th>Exports</th>
                        <th>Weight</th>
                        <th>FOB</th>
                        <th>Avg Weight</th>
                        <th>Avg FOB</th>
                        <th>Clients</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($buyer_analytics) && is_array($buyer_analytics)): ?>
                        <?php foreach ($buyer_analytics as $buyer): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($buyer['buyer']) ?></strong></td>
                            <td><?= number_format($buyer['export_count']) ?></td>
                            <td><?= number_format($buyer['total_weight'], 2) ?></td>
                            <td>$<?= number_format($buyer['total_fob'], 2) ?></td>
                            <td><?= number_format($buyer['avg_weight'], 2) ?></td>
                            <td>$<?= number_format($buyer['avg_fob'], 2) ?></td>
                            <td><span class="badge bg-primary"><?= $buyer['unique_clients'] ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END BUYER TAB -->

      <!-- ==================== CLIENT ANALYSIS TAB ==================== -->
      <div class="tab-pane fade" id="client" role="tabpanel">
        
        <!-- Export Button -->
        <div class="tab-export-header">
          <h4><i class="ti ti-users me-2"></i>Client Analysis</h4>
          <button class="btn btn-excel" onclick="window.location.href='<?= APP_URL ?>/exportdashboard/exportClient'">
            <i class="ti ti-file-spreadsheet me-2"></i> Export to Excel
          </button>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="section-header">
              <h3><i class="ti ti-users me-2"></i>Comprehensive Client Analysis</h3>
              <p>Detailed client performance and analytics</p>
            </div>
          </div>
        </div>

        <!-- Client Overview KPIs -->
        <div class="row">
          <div class="col-xl-3 col-md-6">
            <div class="kpi-card blue">
              <i class="ti ti-users icon"></i>
              <h3><?= number_format($client_overview['total_clients'] ?? 0) ?></h3>
              <p>Total Clients</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card green">
              <i class="ti ti-file-export icon"></i>
              <h3><?= number_format($client_overview['total_exports'] ?? 0) ?></h3>
              <p>Total Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card orange">
              <i class="ti ti-circle-check icon"></i>
              <h3><?= number_format($client_overview['cleared_exports'] ?? 0) ?></h3>
              <p>Cleared Exports</p>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="kpi-card purple">
              <i class="ti ti-currency-dollar icon"></i>
              <h3>$<?= number_format($client_overview['total_agency_fees'] ?? 0, 0) ?></h3>
              <p>Total Agency Fees</p>
            </div>
          </div>
        </div>

        <!-- Client Detailed Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-table me-2"></i>Detailed Client Data
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="clientDetailedTable">
                    <thead>
                      <tr>
                        <th>MCA Ref</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Buyer</th>
                        <th>Weight</th>
                        <th>FOB</th>
                        <th>Agency Fees</th>
                        <th>Seals</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($client_detailed) && is_array($client_detailed)): ?>
                        <?php foreach ($client_detailed as $client): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($client['mca_ref'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($client['invoice'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($client['client_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($client['buyer'] ?? 'N/A') ?></td>
                            <td><?= number_format($client['weight'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($client['fob'] ?? 0, 2) ?></td>
                            <td>$<?= number_format($client['total_agency_fees'] ?? 0, 2) ?></td>
                            <td><?= number_format($client['number_of_seals'] ?? 0) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($client['status'] ?? 'N/A') ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Client Comparison Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h4 class="header-title mb-3">
                  <i class="ti ti-chart-bar me-2"></i>Client Comparison
                </h4>
                <div class="table-responsive">
                  <table class="table table-hover" id="clientComparisonTable">
                    <thead>
                      <tr>
                        <th>Client</th>
                        <th>Exports</th>
                        <th>Weight</th>
                        <th>FOB Value</th>
                        <th>$/kg</th>
                        <th>Clear Rate</th>
                        <th>Avg Days</th>
                        <th>Agency Fees</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($client_comparison) && is_array($client_comparison)): ?>
                        <?php foreach ($client_comparison as $client): ?>
                          <tr>
                            <td><strong><?= htmlspecialchars($client['client_name']) ?></strong></td>
                            <td><?= number_format($client['total_exports']) ?></td>
                            <td><?= number_format($client['total_weight'], 0) ?></td>
                            <td>$<?= number_format($client['total_fob_value'], 0) ?></td>
                            <td>$<?= number_format($client['fob_per_kg'], 2) ?></td>
                            <td>
                              <div class="progress" style="height: 20px; min-width: 80px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $client['clearance_rate'] ?>%">
                                  <?= number_format($client['clearance_rate'], 1) ?>%
                                </div>
                              </div>
                            </td>
                            <td><?= number_format($client['avg_cycle_time'], 1) ?></td>
                            <td>$<?= number_format($client['total_agency_fees'], 0) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted">No data</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- END CLIENT ANALYSIS TAB -->

    </div>
  </div>
</div>

<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
  'use strict';

  // Theme Toggle
  const html = document.documentElement;
  const themeBtn = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');

  function setTheme(theme) {
    html.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);
    themeIcon.className = theme === 'dark' ? 'ti ti-moon' : 'ti ti-sun';
  }

  const savedTheme = localStorage.getItem('theme') || 'light';
  setTheme(savedTheme);
  
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      const currentTheme = html.getAttribute('data-bs-theme');
      setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });
  }

  // ==================== DATATABLES INITIALIZATION ====================

  function initializeDataTables() {
    if (!$.fn.DataTable) {
      console.log('DataTables not loaded');
      return;
    }

    const tables = [
      '#recentExportsTable',
      '#logisticsDetailedTable',
      '#delayDetailedTable',
      '#delayByClientTable',
      '#triphaseDetailedTable',
      '#triphaseClientTable',
      '#siteDetailedTable',
      '#prepaymentDetailedTable',
      '#agencyFeesTable', 
      '#sealDetailedTable',
      '#sealsByMasterTable',
      '#assayDetailedTable',
      '#lotAnalyticsTable',
      '#buyerDetailedTable',
      '#buyerAnalyticsTable',
      '#clientDetailedTable',
      '#clientComparisonTable'
    ];

    tables.forEach(tableId => {
      const $table = $(tableId);
      if ($table.length) {
        // Destroy existing instance
        if ($.fn.DataTable.isDataTable(tableId)) {
          $table.DataTable().destroy();
        }
        
        // Check if table has data
        const rowCount = $table.find('tbody tr').length;
        const hasData = rowCount > 0 && !$table.find('tbody tr td[colspan]').length;
        
        if (hasData) {
          try {
            $table.DataTable({
              pageLength: 25,
              order: [[0, 'desc']],
              responsive: true,
              dom: 'Bfrtip',
              buttons: [
                {
                  extend: 'excelHtml5',
                  text: '<i class="ti ti-file-spreadsheet me-1"></i> Export to Excel',
                  className: 'btn btn-excel',
                  title: 'Export Dashboard - ' + tableId.replace('#', '').replace('Table', ''),
                  exportOptions: {
                    columns: ':visible'
                  }
                }
              ],
              language: {
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
              },
              columnDefs: [{
                targets: '_all',
                defaultContent: '-'
              }]
            });
          } catch (e) {
            console.error('Error initializing DataTable ' + tableId + ':', e);
          }
        } else {
          console.log('Table ' + tableId + ' has no data, skipping DataTable initialization');
        }
      }
    });
  }

  // Initialize DataTables on tab show
  $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
    setTimeout(() => {
      initializeDataTables();
    }, 100);
  });

  // Initialize DataTables on page load for active tab
  setTimeout(() => {
    initializeDataTables();
  }, 500);

});
</script>