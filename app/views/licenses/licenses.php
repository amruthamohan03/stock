<!-- include any head / css you already have -->
<link href="<?= BASE_URL ?>/assets/pages/css/license_styles.css" rel="stylesheet" type="text/css">

<style>
  /* ===== ADVANCED FILTERS STYLING ===== */
  .advanced-filters-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    padding: 20px 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  
  .filters-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
  }
  
  .filters-header i {
    color: #667eea;
    font-size: 1.3rem;
  }
  
  .filters-header h5 {
    margin: 0;
    color: #2d3748;
    font-weight: 600;
    font-size: 1.1rem;
  }
  
  .filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr auto;
    gap: 15px;
    align-items: end;
  }
  
  .filter-group label {
    display: block;
    margin-bottom: 8px;
    color: #4a5568;
    font-weight: 500;
    font-size: 0.9rem;
  }
  
  .filter-group .form-select,
  .filter-group .form-control {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.95rem;
    transition: all 0.2s;
  }
  
  .filter-group .form-select:focus,
  .filter-group .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
  
  .btn-apply-filters {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 30px;
    border-radius: 8px;
    font-weight: 600;
    height: 42px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    white-space: nowrap;
    font-size: 0.95rem;
  }
  
  .btn-apply-filters:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }
  
  .btn-apply-filters i {
    font-size: 1.1rem;
  }
  
  .btn-clear-filters {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    height: 42px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    white-space: nowrap;
    font-size: 0.95rem;
    margin-left: 10px;
  }
  
  .btn-clear-filters:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    color: white;
  }
  
  @media (max-width: 1400px) {
    .filter-row {
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    
    .filter-row > div:last-child {
      grid-column: 1 / -1;
      display: flex;
      gap: 10px;
    }
  }
  
  @media (max-width: 768px) {
    .filter-row {
      grid-template-columns: 1fr;
    }
    
    .advanced-filters-card {
      padding: 15px;
    }
  }

  /* ===== DATATABLE STYLING ===== */
  .dataTables_wrapper .dataTables_info {
    float: left;
  }
  .dataTables_wrapper .dataTables_paginate {
    float: right;
    text-align: right;
  }
  
  /* ===== BUTTON STYLING ===== */
  .btn-export-all {
    background: #28a745 !important;
    color: white !important;
    border: none !important;
    padding: 8px 20px !important;
    border-radius: 5px !important;
    font-weight: 500 !important;
    transition: all 0.3s !important;
    box-shadow: none !important;
  }
  
  .btn-export-all:hover {
    background: #218838 !important;
    color: white !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4) !important;
  }
  
  /* ===== VALIDATION STYLING ===== */
  .text-danger {
    color: #dc3545;
    font-weight: bold;
  }
  
  .is-invalid {
    border-color: #dc3545 !important;
  }
  
  .invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  
  .is-invalid ~ .invalid-feedback {
    display: block;
  }
  
  /* ===== ACTION BUTTON STYLING ===== */
  .btn-view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
  }
  .btn-view:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
  }
  
  .btn-export {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
  }
  .btn-export:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
  }
  
  /* ===== STATISTICS CARDS ===== */
  .stats-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden;
    cursor: pointer;
    min-height: 120px;
  }
  
  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  }
  
  .stats-card.active-filter {
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
    transform: scale(1.05);
  }
  
  .stats-card-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
  .stats-card-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
  .stats-card-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
  .stats-card-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
  .stats-card-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
  .stats-card-6 { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); color: white; }
  .stats-card-7 { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; }
  
  .stats-card .card-body {
    padding: 20px 15px;
    position: relative;
  }
  
  .stats-icon {
    font-size: 2.5rem;
    opacity: 0.3;
    position: absolute;
    right: 15px;
    top: 15px;
  }
  
  .stats-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
  }
  
  .stats-label {
    font-size: 0.75rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  /* Responsive adjustments for 7 cards */
  @media (min-width: 1400px) {
    .stats-col {
      flex: 0 0 auto;
      width: 14.285714%; /* 100% / 7 cards */
    }
  }
  
  @media (max-width: 1399px) {
    .stats-card .card-body {
      padding: 15px 10px;
    }
    .stats-value {
      font-size: 1.75rem;
    }
    .stats-icon {
      font-size: 2rem;
    }
  }
  
  /* ===== ACCORDION STYLING ===== */
  .accordion-button {
    font-weight: 600;
    background-color: #f8f9fa;
  }
  
  .accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .accordion-button:not(.collapsed)::after {
    filter: brightness(0) invert(1);
  }
  
  .accordion-item {
    border: none;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  
  .accordion-body {
    background: #ffffff;
  }
  
  /* ===== MODAL STYLING ===== */
  .modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
  }
  
  .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 20px 30px;
  }
  
  .modal-header .btn-close {
    filter: brightness(0) invert(1);
  }
  
  .detail-row {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
  }
  
  .detail-row:hover {
    background: #f8f9fa;
  }
  
  .detail-row:last-child {
    border-bottom: none;
  }
  
  .detail-label {
    font-weight: 600;
    color: #667eea;
    font-size: 0.9rem;
    margin-bottom: 5px;
  }
  
  .detail-value {
    color: #2d3748;
    font-size: 1rem;
    font-weight: 500;
  }
  
  .detail-icon {
    color: #667eea;
    margin-right: 8px;
  }

  /* ===== CARD SHADOWS ===== */
  .card {
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: none;
    border-radius: 15px;
  }

  /* ===== STATUS BADGE STYLING ===== */
  .badge {
    padding: 6px 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
  }

  /* ===== FORM SECTION HEADERS ===== */
  .section-header h5 {
    color: #667eea;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .section-header {
    position: relative;
    padding-bottom: 10px;
  }

  .section-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 2px;
  }

  .form-section {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 25px;
    margin-bottom: 25px;
  }

  .form-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
  }
  
  /* ===== FORM INPUT STYLING ===== */
  .form-control, .form-select {
    height: 38px;
  }
  
  input[type="file"].form-control {
    padding: 6px 12px;
  }
  
  /* ===== DESTINATION/ORIGIN WITH ADD BUTTON ===== */
  .input-with-button {
    display: flex;
    gap: 8px;
    align-items: center;
  }
  
  .input-with-button .form-select {
    flex: 1;
  }
  
  .btn-add-origin {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    white-space: nowrap;
    height: 38px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .btn-add-origin:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
  }
  
  .btn-add-origin i {
    font-size: 1.1rem;
  }
  
  /* ===== MODAL LIST ITEMS ===== */
  .expired-license-item,
  .expiring-license-item,
  .incomplete-license-item {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background 0.2s;
  }
  
  .expired-license-item:hover,
  .expiring-license-item:hover,
  .incomplete-license-item:hover {
    background: #f8f9fa;
  }
  
  .expired-license-item:last-child,
  .expiring-license-item:last-child,
  .incomplete-license-item:last-child {
    border-bottom: none;
  }
  
  .days-expired-badge {
    font-size: 1.2rem;
    padding: 8px 15px;
    background: #dc3545;
    color: white;
  }
  
  .days-badge {
    font-size: 1.2rem;
    padding: 8px 15px;
  }
  
  .days-critical { background: #dc3545; color: white; }
  .days-warning { background: #ffc107; color: #000; }
  .days-notice { background: #17a2b8; color: white; }
  
  .missing-fields-list {
    margin-top: 10px;
  }
  
  .missing-field-badge {
    display: inline-block;
    background: #ffc107;
    color: #000;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.85rem;
    margin: 3px;
    font-weight: 500;
  }
  
  .badge-required { background: #dc3545; color: white; }
  .badge-optional { background: #6c757d; color: white; }
  
  /* ===== DUAL COLOR DATATABLE STYLING ===== */
  .dataTables_wrapper {
    padding: 20px 0;
  }
  
  .dataTables_wrapper .dataTables_length {
    float: left;
    margin-bottom: 15px;
  }
  
  .dataTables_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 15px;
  }
  
  .dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 5px 10px;
    margin-left: 5px;
  }
  
  .dataTables_wrapper .dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 0 5px;
  }
  
  /* ===== IMPORT TABLE - BLUE/PURPLE THEME ===== */
  #importLicenseTable thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 12px 8px;
  }
  
  #importLicenseTable tbody tr {
    transition: background 0.2s;
  }
  
  #importLicenseTable tbody tr:hover {
    background: #f0f4ff;
  }
  
  #importLicenseTable tbody td {
    padding: 10px 8px;
    vertical-align: middle;
  }
  
  #importLicenseTable_wrapper .dataTables_paginate .paginate_button:hover {
    background: #667eea;
    color: white !important;
    border-color: #667eea;
  }
  
  #importLicenseTable_wrapper .dataTables_paginate .paginate_button.current {
    background: #667eea;
    color: white !important;
    border-color: #667eea;
  }
  
  /* ===== EXPORT TABLE - GREEN/TEAL THEME ===== */
  #exportLicenseTable thead th {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 12px 8px;
  }
  
  #exportLicenseTable tbody tr {
    transition: background 0.2s;
  }
  
  #exportLicenseTable tbody tr:hover {
    background: #f0fff4;
  }
  
  #exportLicenseTable tbody td {
    padding: 10px 8px;
    vertical-align: middle;
  }
  
  #exportLicenseTable_wrapper .dataTables_paginate .paginate_button:hover {
    background: #11998e;
    color: white !important;
    border-color: #11998e;
  }
  
  #exportLicenseTable_wrapper .dataTables_paginate .paginate_button.current {
    background: #11998e;
    color: white !important;
    border-color: #11998e;
  }
  
  /* ===== COMMON DATATABLE STYLING ===== */
  .dataTables_wrapper .dataTables_paginate {
    padding-top: 15px;
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 5px 12px;
    margin: 0 2px;
    border-radius: 5px;
    border: 1px solid #ddd;
    background: white;
    transition: all 0.2s;
  }

  /* ===== IMPORT/EXPORT TABLE HEADER STYLING ===== */
  .table-type-header {
    padding: 15px 20px;
    border-radius: 10px 10px 0 0;
    margin-bottom: 0;
  }
  
  /* Import Header - Blue/Purple */
  .table-type-header.import-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  /* Export Header - Green/Teal */
  .table-type-header.export-header {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
  }

  .table-type-header h5 {
    margin: 0;
    font-weight: 600;
  }

  .table-type-header i {
    margin-right: 10px;
  }
  
  /* ===== CARD BORDERS TO MATCH THEMES ===== */
  .import-card {
    border-top: 3px solid #667eea;
  }
  
  .export-card {
    border-top: 3px solid #11998e;
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- ADVANCED FILTERS SECTION -->
        <div class="advanced-filters-card">
          <div class="filters-header">
            <i class="ti ti-filter"></i>
            <h5>Advanced Filters</h5>
          </div>
          <div class="filter-row">
            <div class="filter-group">
              <label for="filterClient">Client</label>
              <select id="filterClient" class="form-select">
                <option value="">All Clients</option>
                <?php foreach ($clients as $client): ?>
                  <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['short_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="filter-group">
              <label for="filterTransportMode">Transport Mode</label>
              <select id="filterTransportMode" class="form-select">
                <option value="">All Transport Modes</option>
                <?php foreach ($transport_modes as $mode): ?>
                  <option value="<?= $mode['id'] ?>"><?= htmlspecialchars($mode['transport_mode_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="filter-group">
              <label for="filterStartDate">Start Date</label>
              <input type="date" id="filterStartDate" class="form-control" placeholder="dd-mm-yyyy">
            </div>
            
            <div class="filter-group">
              <label for="filterEndDate">End Date</label>
              <input type="date" id="filterEndDate" class="form-control" placeholder="dd-mm-yyyy">
            </div>
            
            <div class="filter-group">
              <label>&nbsp;</label>
              <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-apply-filters" id="applyAdvancedFiltersBtn">
                  <i class="ti ti-check"></i> Apply Filters
                </button>
                <button type="button" class="btn btn-clear-filters" id="clearAdvancedFiltersBtn" style="display: none;">
                  <i class="ti ti-x"></i> Clear
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Cards - 7 CARDS IN ONE ROW -->
        <div class="row mb-4">
          <!-- Card 1: Total Licenses -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-1 shadow-sm filter-card" data-filter="all">
              <div class="card-body position-relative">
                <i class="ti ti-license stats-icon"></i>
                <div class="stats-value" id="totalLicenses">0</div>
                <div class="stats-label">Total</div>
              </div>
            </div>
          </div>
          
          <!-- Card 2: EXPIRED (WITH MODAL) -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-2 shadow-sm filter-card" data-filter="expired" id="expiredCard">
              <div class="card-body position-relative">
                <i class="ti ti-calendar-x stats-icon"></i>
                <div class="stats-value" id="expiredLicenses">0</div>
                <div class="stats-label">Expired</div>
              </div>
            </div>
          </div>
          
          <!-- Card 3: Expiring Soon -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-3 shadow-sm filter-card" data-filter="expiring" id="expiringCard">
              <div class="card-body position-relative">
                <i class="ti ti-clock-exclamation stats-icon"></i>
                <div class="stats-value" id="expiringLicenses">0</div>
                <div class="stats-label">Expiring</div>
              </div>
            </div>
          </div>
          
          <!-- Card 4: Incomplete -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-4 shadow-sm filter-card" data-filter="incomplete" id="incompleteCard">
              <div class="card-body position-relative">
                <i class="ti ti-alert-triangle stats-icon"></i>
                <div class="stats-value" id="incompleteLicenses">0</div>
                <div class="stats-label">Incomplete</div>
              </div>
            </div>
          </div>
          
          <!-- Card 5: Annulated -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-5 shadow-sm filter-card" data-filter="annulated">
              <div class="card-body position-relative">
                <i class="ti ti-ban stats-icon"></i>
                <div class="stats-value" id="annulatedLicenses">0</div>
                <div class="stats-label">Annulated</div>
              </div>
            </div>
          </div>
          
          <!-- Card 6: MODIFIED -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-6 shadow-sm filter-card" data-filter="modified">
              <div class="card-body position-relative">
                <i class="ti ti-edit stats-icon"></i>
                <div class="stats-value" id="modifiedLicenses">0</div>
                <div class="stats-label">Modified</div>
              </div>
            </div>
          </div>
          
          <!-- Card 7: Prorogated -->
          <div class="col-xl col-lg-3 col-md-4 col-sm-6 mb-3 stats-col">
            <div class="card stats-card stats-card-7 shadow-sm filter-card" data-filter="prorogated">
              <div class="card-body position-relative">
                <i class="ti ti-clock stats-icon"></i>
                <div class="stats-value" id="prorogatedLicenses">0</div>
                <div class="stats-label">Prorogated</div>
              </div>
            </div>
          </div>
        </div>

        <!-- License Form Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-license me-2"></i> <span id="formTitle">Add New License</span></h4>
            <div>
              <button type="button" class="btn btn-export-all me-2" id="exportAllBtn">
                <i class="ti ti-file-spreadsheet me-1"></i> Export All to Excel
              </button>
              <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn" style="display:none;">
                <i class="ti ti-plus"></i> Add New
              </button>
            </div>
          </div>

          <div class="card-body">
            <form id="licenseForm" method="post" enctype="multipart/form-data" novalidate>
              <input type="hidden" name="license_id" id="license_id" value="">
              <input type="hidden" name="action" id="formAction" value="insert">

              <div class="accordion" id="licenseAccordion">
                
                <!-- CREATE LICENSE ACCORDION -->
                <div class="accordion-item mb-3">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#createLicense">
                      <i class="ti ti-license me-2"></i> Create License
                    </button>
                  </h2>

                  <div id="createLicense" class="accordion-collapse collapse" data-bs-parent="#licenseAccordion">
                    <div class="accordion-body">

                      <!-- 1. BASIC INFORMATION SECTION -->
                      <div class="form-section mb-4" id="basicInfoSection">
                        <div class="section-header mb-3">
                          <h5 class="mb-0">
                            <i class="ti ti-info-circle me-2"></i>Basic Information
                          </h5>
                        </div>
                        <div class="row" id="basicInfoRow">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Kind <span class="text-danger">*</span></label>
                            <select name="kind_id" id="kind_id" class="form-select" required>
                              <option value="">-- Select Kind --</option>
                              <?php foreach ($kinds as $kind): ?>
                                <option value="<?= $kind['id'] ?>" data-kind-short="<?= htmlspecialchars($kind['kind_short_name']) ?>">
                                  <?= htmlspecialchars($kind['kind_name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a kind</div>
                          </div>

                          <div class="col-md-4 mb-3" id="bankField">
                            <label class="form-label">Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-select" required>
                              <option value="">-- Select Bank --</option>
                              <?php foreach ($banks as $bank): ?>
                                <option value="<?= $bank['id'] ?>"><?= htmlspecialchars($bank['bank_name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a bank</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-select" required>
                              <option value="">-- Select Client --</option>
                              <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" data-client-short="<?= htmlspecialchars($client['short_name']) ?>">
                                  <?= htmlspecialchars($client['short_name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a client</div>
                          </div>

                          <div class="col-md-4 mb-3" id="licenseClearedByField">
                            <label class="form-label">License Cleared By <span class="text-danger">*</span></label>
                            <select name="license_cleared_by" id="license_cleared_by" class="form-select" required>
                              <option value="">-- Select Option --</option>
                              <?php foreach ($done_by_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>"><?= htmlspecialchars($opt['done_by_name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select who cleared the license</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Type of Goods <span class="text-danger">*</span></label>
                            <select name="type_of_goods_id" id="type_of_goods_id" class="form-select" required>
                              <option value="">-- Select Type --</option>
                              <?php foreach ($type_of_goods as $type): ?>
                                <option value="<?= $type['id'] ?>" data-goods-short="<?= htmlspecialchars($type['goods_short_name']) ?>">
                                  <?= htmlspecialchars($type['goods_type']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select type of goods</div>
                          </div>

                          <div class="col-md-4 mb-3" id="weightField">
                            <label class="form-label">Weight <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="weight" id="weight" class="form-control" required>
                            <div class="invalid-feedback">Weight is required and must be positive</div>
                          </div>

                          <!-- NEW M3 FIELD - Shows only when type_of_goods_id = 3 -->
                          <div class="col-md-4 mb-3" id="m3Field" style="display: none;">
                            <label class="form-label">M3 <span class="text-danger" id="m3Required">*</span></label>
                            <input type="number" step="0.01" min="0" name="m3" id="m3" class="form-control">
                            <div class="invalid-feedback">M3 is required and must be positive</div>
                          </div>
                        </div>
                      </div>

                      <!-- 2. FINANCIAL INFORMATION SECTION -->
                      <div class="form-section mb-4" id="financialInfoSection">
                        <div class="section-header mb-3">
                          <h5 class="mb-0">
                            <i class="ti ti-currency-dollar me-2"></i>Financial Information
                          </h5>
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Unit of Measurement <span class="text-danger">*</span></label>
                            <select name="unit_of_measurement_id" id="unit_of_measurement_id" class="form-select" required>
                              <option value="">-- Select Unit --</option>
                              <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select unit of measurement</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select name="currency_id" id="currency_id" class="form-select" required>
                              <option value="">-- Select Currency --</option>
                              <?php foreach ($currencies as $currency): ?>
                                <option value="<?= $currency['id'] ?>">
                                  <?= htmlspecialchars($currency['currency_short_name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select currency</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">FOB Declared <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="fob_declared" id="fob_declared" class="form-control" required>
                            <div class="invalid-feedback">FOB Declared is required and must be positive</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Insurance</label>
                            <input type="number" step="0.01" min="0" name="insurance" id="insurance" class="form-control">
                            <div class="invalid-feedback">Insurance must be positive</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Freight</label>
                            <input type="number" step="0.01" min="0" name="freight" id="freight" class="form-control">
                            <div class="invalid-feedback">Freight must be positive</div>
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">Other Costs</label>
                            <input type="number" step="0.01" min="0" name="other_costs" id="other_costs" class="form-control">
                            <div class="invalid-feedback">Other costs must be positive</div>
                          </div>
                        </div>
                      </div>

                      <!-- 3. INVOICE & TRANSPORT INFORMATION SECTION -->
                      <div class="form-section mb-4" id="invoiceTransportSection">
                        <div class="section-header mb-3">
                          <h5 class="mb-0">
                            <i class="ti ti-file-invoice me-2"></i>Invoice & Transport Information
                          </h5>
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                            <select name="transport_mode_id" id="transport_mode_id" class="form-select" required>
                              <option value="">-- Select Transport Mode --</option>
                              <?php foreach ($transport_modes as $mode): ?>
                                <option value="<?= $mode['id'] ?>" data-transport-letter="<?= htmlspecialchars($mode['transport_letter']) ?>">
                                  <?= htmlspecialchars($mode['transport_mode_name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select transport mode</div>
                          </div>

                          <div class="col-md-4 mb-3" id="invoiceNumberField">
                            <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control" required maxlength="50">
                            <div class="invalid-feedback">Invoice number is required</div>
                          </div>

                          <div class="col-md-4 mb-3" id="invoiceDateField">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
                            <div class="invalid-feedback">Invoice date is required and cannot be in the future</div>
                          </div>

                          <div class="col-md-4 mb-3" id="invoiceFileField">
                            <label class="form-label">Invoice File (PDF only)</label>
                            <input type="file" name="invoice_file" id="invoice_file" class="form-control" accept="application/pdf">
                            <small id="current_invoice_file" class="form-text text-muted"></small>
                            <div class="invalid-feedback">Only PDF files are allowed</div>
                          </div>

                          <div class="col-md-8 mb-3" id="supplierField">
                            <label class="form-label">Supplier/Buyer <span class="text-danger">*</span></label>
                            <input type="text" name="supplier" id="supplier" class="form-control" required maxlength="255">
                            <div class="invalid-feedback">Supplier/Buyer is required</div>
                          </div>
                        </div>
                      </div>

                      <!-- 4. LICENSE DETAILS SECTION -->
                      <div class="form-section mb-4" id="licenseDetailsSection">
                        <div class="section-header mb-3">
                          <h5 class="mb-0">
                            <i class="ti ti-calendar-event me-2"></i>License Details
                          </h5>
                        </div>
                        
                        <!-- ROW 1: License Applied Date, FSI/FSO, AUR -->
                        <div class="row">
                          <div class="col-md-4 mb-3" id="licenseAppliedDateField">
                            <label class="form-label">License Applied Date <span class="text-danger">*</span></label>
                            <input type="date" name="license_applied_date" id="license_applied_date" class="form-control" required>
                            <div class="invalid-feedback">License applied date is required</div>
                          </div>

                          <div class="col-md-4 mb-3" id="fsiField">
                            <label class="form-label">FSI/FSO</label>
                            <input type="text" name="fsi" id="fsi" class="form-control" maxlength="100">
                          </div>

                          <div class="col-md-4 mb-3" id="aurField">
                            <label class="form-label">AUR</label>
                            <input type="text" name="aur" id="aur" class="form-control" maxlength="100">
                          </div>
                        </div>

                        <!-- ROW 2: License Number, Entry Post, REF. COD -->
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">License Number <span class="text-danger">*</span></label>
                            <input type="text" name="license_number" id="license_number" class="form-control" required maxlength="50">
                            <small class="form-text text-muted" id="licenseNumberHelp"></small>
                            <div class="invalid-feedback">License number is required and must be unique</div>
                          </div>

                          <div class="col-md-4 mb-3" id="entryPostField">
                            <label class="form-label">Entry Post/Exit Post <span class="text-danger">*</span></label>
                            <select name="entry_post_id" id="entry_post_id" class="form-select" required>
                              <option value="">-- Select Entry Post/Exit Post --</option>
                              <?php foreach ($entry_posts as $post): ?>
                                <option value="<?= $post['id'] ?>"><?= htmlspecialchars($post['transit_point_name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an entry post</div>
                          </div>

                          <div class="col-md-4 mb-3" id="refCodField">
                            <label class="form-label">REF. COD</label>
                            <input type="text" name="ref_cod" id="ref_cod" class="form-control" maxlength="50">
                          </div>
                        </div>

                        <!-- ROW 3: License Validation Date, License Expiry Date, License File -->
                        <div class="row">
                          <div class="col-md-4 mb-3" id="licenseValidationDateField">
                            <label class="form-label">License Validation Date <span class="text-danger">*</span></label>
                            <input type="date" name="license_validation_date" id="license_validation_date" class="form-control" required>
                            <div class="invalid-feedback">Validation date must be ≥ applied date</div>
                          </div>

                          <div class="col-md-4 mb-3" id="licenseExpiryDateField">
                            <label class="form-label">License Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="license_expiry_date" id="license_expiry_date" class="form-control" required>
                            <div class="invalid-feedback">Expiry date must be ≥ validation date</div>
                          </div>

                          <div class="col-md-4 mb-3" id="licenseFileField">
                            <label class="form-label">License File (PDF only)</label>
                            <input type="file" name="license_file" id="license_file" class="form-control" accept="application/pdf">
                            <small id="current_license_file" class="form-text text-muted"></small>
                            <div class="invalid-feedback">Only PDF files are allowed</div>
                          </div>
                        </div>
                      </div>

                      <!-- 5. PAYMENT INFORMATION SECTION WITH ADD ORIGIN BUTTON -->
                      <div class="form-section mb-4" id="paymentInfoSection">
                        <div class="section-header mb-3">
                          <h5 class="mb-0">
                            <i class="ti ti-credit-card me-2"></i>Payment Information
                          </h5>
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3" id="paymentMethodField">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method_id" id="payment_method_id" class="form-select" required>
                              <option value="">-- Select Payment Method --</option>
                              <?php foreach ($payment_methods as $method): ?>
                                <option value="<?= $method['id'] ?>"><?= htmlspecialchars($method['payment_method_name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a payment method</div>
                          </div>

                          <div class="col-md-4 mb-3" id="paymentSubtypeField">
                            <label class="form-label">Payment Subtype</label>
                            <select name="payment_subtype_id" id="payment_subtype_id" class="form-select">
                              <option value="">-- Select Payment Subtype --</option>
                              <?php foreach ($payment_subtypes as $subtype): ?>
                                <option value="<?= $subtype['id'] ?>"><?= htmlspecialchars($subtype['payment_subtype']) ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-md-4 mb-3" id="destinationField">
                            <label class="form-label">Destination/Origin <span class="text-danger">*</span></label>
                            <div class="input-with-button">
                              <select name="destination_id" id="destination_id" class="form-select" required>
                                <option value="">-- Select Destination/Origin --</option>
                                <?php foreach ($origins as $origin): ?>
                                  <option value="<?= $origin['id'] ?>"><?= htmlspecialchars($origin['origin_name']) ?></option>
                                <?php endforeach; ?>
                              </select>
                              <button type="button" class="btn btn-add-origin" id="addOriginBtn">
                                <i class="ti ti-plus"></i> Add
                              </button>
                            </div>
                            <div class="invalid-feedback">Please select a destination/origin</div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>

              </div>

              <!-- Form Buttons -->
              <div class="row mt-4">
                <div class="col-12 text-end">
                  <button type="button" class="btn btn-secondary" id="cancelBtn">
                    <i class="ti ti-x me-1"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary ms-2" id="submitBtn">
                    <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save License</span>
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- IMPORT LICENSES DataTable (kind_id 1, 2, 5, 6) - BLUE/PURPLE THEME -->
        <div class="card shadow-sm mb-4 import-card">
          <div class="card-header table-type-header import-header">
            <h5><i class="ti ti-file-import"></i> Import License Records</h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-end align-items-center mb-3">
              <button type="button" class="btn btn-sm btn-secondary" id="clearFilterBtnImport" style="display:none;">
                <i class="ti ti-filter-off me-1"></i> Clear Filter
              </button>
            </div>
            <div class="table-responsive">
              <table id="importLicenseTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>License Number</th>
                    <th>Client</th>
                    <th>Bank</th>
                    <th>Invoice Number</th>
                    <th>Applied Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- EXPORT LICENSES DataTable (kind_id 3, 4) - GREEN/TEAL THEME -->
        <div class="card shadow-sm mb-4 export-card">
          <div class="card-header table-type-header export-header">
            <h5><i class="ti ti-file-export"></i> Export License Records</h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-end align-items-center mb-3">
              <button type="button" class="btn btn-sm btn-secondary" id="clearFilterBtnExport" style="display:none;">
                <i class="ti ti-filter-off me-1"></i> Clear Filter
              </button>
            </div>
            <div class="table-responsive">
              <table id="exportLicenseTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>License Number</th>
                    <th>Client</th>
                    <th>Bank</th>
                    <th>Invoice Number</th>
                    <th>Applied Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewLicenseModal" tabindex="-1" aria-labelledby="viewLicenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewLicenseModalLabel">
          <i class="ti ti-eye me-2"></i> License Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="modalDetailsContent">
          <!-- Details will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Add Origin/Destination Modal -->
<div class="modal fade" id="addOriginModal" tabindex="-1" aria-labelledby="addOriginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addOriginModalLabel">
          <i class="ti ti-plus me-2"></i> Add New Destination/Origin
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addOriginForm">
          <div class="mb-3">
            <label for="new_origin_name" class="form-label">Destination/Origin Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="new_origin_name" name="origin_name" required maxlength="255" placeholder="Enter destination/origin name">
            <div class="invalid-feedback">Please enter a destination/origin name</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="saveOriginBtn">
          <i class="ti ti-check me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- EXPIRED Licenses Modal -->
<div class="modal fade" id="expiredLicensesModal" tabindex="-1" aria-labelledby="expiredLicensesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="expiredLicensesModalLabel">
          <i class="ti ti-calendar-x me-2"></i> Expired Licenses
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="expiredLicensesContent">
          <!-- Expired licenses will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Expiring Licenses Modal -->
<div class="modal fade" id="expiringLicensesModal" tabindex="-1" aria-labelledby="expiringLicensesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="expiringLicensesModalLabel">
          <i class="ti ti-clock-exclamation me-2"></i> Licenses Expiring Within 30 Days
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="expiringLicensesContent">
          <!-- Expiring licenses will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Incomplete Licenses Modal -->
<div class="modal fade" id="incompleteLicensesModal" tabindex="-1" aria-labelledby="incompleteLicensesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="incompleteLicensesModalLabel">
          <i class="ti ti-alert-triangle me-2"></i> Incomplete Licenses - Missing Fields
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="incompleteLicensesContent">
          <!-- Incomplete licenses will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ===== PRODUCTION-READY JAVASCRIPT - WITH SEPARATE DATATABLES ===== -->
<script>
/**
 * License Management System - JavaScript Module
 * 
 * @version 4.0.0 - PRODUCTION READY - SEPARATE IMPORT/EXPORT TABLES
 */

// ===== CONSTANTS =====
const MCA_KIND_IDS = [5, 6];
const SPECIAL_KIND_IDS = [3, 4];
const IMPORT_KIND_IDS = [1, 2, 5, 6]; // Import licenses
const EXPORT_KIND_IDS = [3, 4]; // Export licenses
const TYPE_OF_GOODS_M3_ID = 3;
const EXPIRING_DAYS_CRITICAL = 7;
const EXPIRING_DAYS_WARNING = 15;
const MCA_LICENSE_FORMAT = 'CLIENT-KIND-GOODS-TRANSPORT';

// ===== GLOBAL VARIABLES =====
let importLicensesTable;
let exportLicensesTable;
let currentFilter = 'all';
let currentSearch = '';
let isMCAType = false;
let isSpecialType = false;
let requiresM3 = false;
const today = new Date().toISOString().split('T')[0];

let advancedFilters = {
  client_id: '',
  transport_mode_id: '',
  start_date: '',
  end_date: ''
};

// ===== INITIALIZATION =====
$(document).ready(function () {
  initializeDateConstraints();
  initializeEventHandlers();
  initDataTables();
  updateStatistics();
  setActiveFilter('all');
});

/**
 * Initialize date input constraints
 */
function initializeDateConstraints() {
  $('#invoice_date').attr('max', today);
}

/**
 * Initialize all event handlers
 */
function initializeEventHandlers() {
  $('#kind_id').on('change', handleKindChange);
  $('#type_of_goods_id').on('change', handleTypeOfGoodsChange);
  $('#client_id').on('change', handleClientChange);
  $('#transport_mode_id').on('change', function() {
    if (isMCAType) {
      generateMCAReference();
    }
  });
  $('#license_applied_date').on('change', handleAppliedDateChange);
  $('#license_validation_date').on('change', handleValidationDateChange);
  $('input[type="number"]').on('input', validateNumericInput);
  $('input[type="file"]').on('change', validateFileInput);
  $('#licenseForm').on('submit', handleFormSubmit);
  $('.filter-card').on('click', handleFilterCardClick);
  $('#clearFilterBtnImport').on('click', clearFilter);
  $('#clearFilterBtnExport').on('click', clearFilter);
  $('#applyAdvancedFiltersBtn').on('click', applyAdvancedFilters);
  $('#clearAdvancedFiltersBtn').on('click', clearAdvancedFilters);
  $('#exportAllBtn').on('click', exportAllLicenses);
  $(document).on('click', '.exportBtn', exportSingleLicense);
  $(document).on('click', '.viewBtn', viewLicenseDetails);
  $(document).on('click', '.editBtn', editLicense);
  $('#addOriginBtn').on('click', openAddOriginModal);
  $('#saveOriginBtn').on('click', saveNewOrigin);
  $('#expiredCard').on('click', showExpiredLicensesModal);
  $('#expiringCard').on('click', showExpiringLicensesModal);
  $('#incompleteCard').on('click', showIncompleteLicensesModal);
  $('#cancelBtn, #resetFormBtn').on('click', resetForm);
}

// ===== ADVANCED FILTERS =====

function applyAdvancedFilters() {
  advancedFilters = {
    client_id: $('#filterClient').val(),
    transport_mode_id: $('#filterTransportMode').val(),
    start_date: $('#filterStartDate').val(),
    end_date: $('#filterEndDate').val()
  };
  
  if (advancedFilters.start_date && advancedFilters.end_date) {
    if (new Date(advancedFilters.start_date) > new Date(advancedFilters.end_date)) {
      showErrorMessage('Start Date cannot be after End Date');
      return;
    }
  }
  
  const hasFilters = advancedFilters.client_id || advancedFilters.transport_mode_id || 
                     advancedFilters.start_date || advancedFilters.end_date;
  
  if (hasFilters) {
    $('#clearAdvancedFiltersBtn').show();
  }
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

function clearAdvancedFilters() {
  $('#filterClient').val('');
  $('#filterTransportMode').val('');
  $('#filterStartDate').val('');
  $('#filterEndDate').val('');
  
  advancedFilters = {
    client_id: '',
    transport_mode_id: '',
    start_date: '',
    end_date: ''
  };
  
  $('#clearAdvancedFiltersBtn').hide();
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

// ===== KIND TYPE CHECKING =====

function isMCAKind(kindId) {
  return MCA_KIND_IDS.includes(parseInt(kindId));
}

function isSpecialKind(kindId) {
  return SPECIAL_KIND_IDS.includes(parseInt(kindId));
}

function handleKindChange() {
  const kindId = parseInt($(this).val());
  
  if (!kindId || kindId === 0 || isNaN(kindId)) {
    resetFieldStates();
    return;
  }
  
  isMCAType = isMCAKind(kindId);
  isSpecialType = isSpecialKind(kindId);
  
  resetFieldStates();
  
  if (isMCAType) {
    toggleFieldsForMCAType(true);
  } else if (isSpecialType) {
    toggleFieldsForSpecialType(true);
  } else {
    showStandardFields();
  }
}

function handleTypeOfGoodsChange() {
  const typeOfGoodsId = parseInt($(this).val());
  
  requiresM3 = (typeOfGoodsId === TYPE_OF_GOODS_M3_ID);
  
  if (requiresM3) {
    $('#m3Field').show();
    $('#m3').attr('required', true);
    $('#m3Required').show();
  } else {
    $('#m3Field').hide();
    $('#m3').removeAttr('required').val('');
    $('#m3Required').hide();
  }
  
  if (isMCAType) {
    generateMCAReference();
  }
}

function resetFieldStates() {
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').show();
  $('#bankField, #licenseClearedByField, #weightField').show();
  $('#invoiceNumberField, #invoiceDateField, #invoiceFileField, #supplierField').show();
  $('#licenseAppliedDateField, #fsiField, #aurField, #entryPostField, #refCodField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  $('#m3Field').hide();
  $('#m3').removeAttr('required').val('');
  requiresM3 = false;
  $('label[for="entry_post_id"]').html('Entry Post <span class="text-danger">*</span>');
  addRequiredToStandardFields();
  $('#mcaTransportField, #mcaCurrencyField, #mcaLicenseNumberField').remove();
  $('#license_number').attr('readonly', false);
  $('#licenseNumberHelp').text('');
}

// ===== SPECIAL TYPE HANDLING =====

function toggleFieldsForSpecialType(isSpecial) {
  if (isSpecial) {
    showSpecialTypeFields();
  } else {
    showStandardFields();
  }
}

function showSpecialTypeFields() {
  $('#financialInfoSection').show();
  $('#invoiceTransportSection').show();
  $('#licenseDetailsSection').show();
  $('#paymentInfoSection').show();
  $('#bankField').show();
  $('#licenseClearedByField').show();
  $('#weightField').show();
  
  const typeOfGoodsId = parseInt($('#type_of_goods_id').val());
  if (typeOfGoodsId === TYPE_OF_GOODS_M3_ID) {
    $('#m3Field').show();
    $('#m3').attr('required', true);
  }
  
  $('#supplierField').show();
  $('#invoiceNumberField').hide();
  $('#invoiceDateField').hide();
  $('#invoiceFileField').hide();
  $('#licenseAppliedDateField').hide();
  $('#refCodField').hide();
  $('#fsiField, #aurField, #entryPostField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  
  $('#invoice_number').removeAttr('required');
  $('#invoice_date').removeAttr('required');
  $('#license_applied_date').removeAttr('required');
  
  $('label[for="entry_post_id"]').html('Entry Post/Exit Post <span class="text-danger">*</span>');
  
  addRequiredToSpecialTypeFields();
  
  $('#license_number').attr('readonly', false);
  $('#licenseNumberHelp').text('');
}

function showStandardFields() {
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').show();
  $('#bankField, #licenseClearedByField, #weightField').show();
  $('#invoiceNumberField, #invoiceDateField, #invoiceFileField, #supplierField').show();
  $('#licenseAppliedDateField, #fsiField, #aurField, #entryPostField, #refCodField').show();
  $('#licenseValidationDateField, #licenseExpiryDateField, #licenseFileField').show();
  $('#paymentMethodField, #paymentSubtypeField, #destinationField').show();
  
  const typeOfGoodsId = parseInt($('#type_of_goods_id').val());
  if (typeOfGoodsId === TYPE_OF_GOODS_M3_ID) {
    $('#m3Field').show();
    $('#m3').attr('required', true);
  } else {
    $('#m3Field').hide();
    $('#m3').removeAttr('required').val('');
  }
  
  $('#mcaTransportField, #mcaCurrencyField, #mcaLicenseNumberField').remove();
  
  addRequiredToStandardFields();
  $('#transport_mode_id, #currency_id').attr('required', true);
  
  $('label[for="entry_post_id"]').html('Entry Post <span class="text-danger">*</span>');
  
  $('#license_number').attr('readonly', false).val('');
  $('#licenseNumberHelp').text('');
}

function addRequiredToSpecialTypeFields() {
  const requiredFields = [
    '#kind_id', '#client_id', '#type_of_goods_id',
    '#bank_id', '#license_cleared_by', '#weight', '#unit_of_measurement_id',
    '#currency_id', '#fob_declared', '#transport_mode_id',
    '#license_validation_date', '#license_expiry_date',
    '#license_number', '#entry_post_id', '#payment_method_id', '#destination_id',
    '#supplier'
  ];
  
  requiredFields.forEach(field => $(field).attr('required', true));
  
  if (requiresM3) {
    $('#m3').attr('required', true);
  }
}

function addRequiredToStandardFields() {
  const requiredFields = [
    '#kind_id', '#client_id', '#type_of_goods_id',
    '#bank_id', '#license_cleared_by', '#weight', '#unit_of_measurement_id',
    '#currency_id', '#fob_declared', '#transport_mode_id', '#invoice_number',
    '#invoice_date', '#supplier', '#license_applied_date', '#license_validation_date',
    '#license_expiry_date', '#license_number', '#entry_post_id', '#payment_method_id', '#destination_id'
  ];
  
  requiredFields.forEach(field => $(field).attr('required', true));
  
  if (requiresM3) {
    $('#m3').attr('required', true);
  }
}

// ===== MCA TYPE HANDLING =====

function toggleFieldsForMCAType(isMCA) {
  if (isMCA) {
    showMCAFields();
  }
}

function showMCAFields() {
  $('#financialInfoSection, #invoiceTransportSection, #licenseDetailsSection, #paymentInfoSection').hide();
  $('#bankField, #licenseClearedByField, #weightField, #m3Field').hide();
  
  if ($('#mcaTransportField').length === 0) {
    appendMCAFields();
  }
  
  removeRequiredFromHiddenFields();
  $('#transport_mode_id, #currency_id').removeAttr('required');
  setupMCALicenseNumber();
  generateMCAReference();
}

function appendMCAFields() {
  $('#basicInfoRow').append(getMCAFieldsHTML());
  $('#transport_mode_id_mca').val($('#transport_mode_id').val());
  $('#currency_id_mca').val($('#currency_id').val());
  $('#transport_mode_id_mca, #currency_id_mca').on('change', generateMCAReference);
}

function getMCAFieldsHTML() {
  return `
    <div class="col-md-4 mb-3" id="mcaTransportField">
      <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
      <select name="transport_mode_id_mca" id="transport_mode_id_mca" class="form-select" required>
        <option value="">-- Select Transport Mode --</option>
        ${getTransportModeOptions()}
      </select>
      <div class="invalid-feedback">Please select transport mode</div>
    </div>
    <div class="col-md-4 mb-3" id="mcaCurrencyField">
      <label class="form-label">Currency <span class="text-danger">*</span></label>
      <select name="currency_id_mca" id="currency_id_mca" class="form-select" required>
        <option value="">-- Select Currency --</option>
        ${getCurrencyOptions()}
      </select>
      <div class="invalid-feedback">Please select currency</div>
    </div>
    <div class="col-md-12 mb-3" id="mcaLicenseNumberField">
      <label class="form-label">License Number <span class="text-danger">*</span> (Auto-generated)</label>
      <input type="text" name="license_number_mca" id="license_number_mca" class="form-control" required readonly>
      <small class="text-muted">Format: ${MCA_LICENSE_FORMAT}</small>
      <div class="invalid-feedback">License number is required</div>
    </div>
  `;
}

function getTransportModeOptions() {
  let options = '';
  $('#transport_mode_id option').each(function() {
    if ($(this).val()) {
      options += `<option value="${$(this).val()}" data-transport-letter="${$(this).data('transport-letter')}">${$(this).text()}</option>`;
    }
  });
  return options;
}

function getCurrencyOptions() {
  let options = '';
  $('#currency_id option').each(function() {
    if ($(this).val()) {
      options += `<option value="${$(this).val()}">${$(this).text()}</option>`;
    }
  });
  return options;
}

function setupMCALicenseNumber() {
  $('#license_number').attr('readonly', true).val('');
  $('#licenseNumberHelp').text('Auto-generated: ' + MCA_LICENSE_FORMAT);
}

function generateMCAReference() {
  const clientId = $('#client_id').val();
  const kindId = $('#kind_id').val();
  const goodsId = $('#type_of_goods_id').val();
  const transportId = $('#transport_mode_id_mca').val() || $('#transport_mode_id').val();
  
  if (!clientId || !kindId || !goodsId || !transportId || !isMCAType) {
    return;
  }
  
  const clientShort = $('#client_id option:selected').data('client-short');
  const kindShort = $('#kind_id option:selected').data('kind-short');
  const goodsShort = $('#type_of_goods_id option:selected').data('goods-short');
  const transportLetter = $('#transport_mode_id_mca option:selected').data('transport-letter') || 
                         $('#transport_mode_id option:selected').data('transport-letter');
  
  if (clientShort && kindShort && goodsShort && transportLetter) {
    const mcaRef = `${clientShort}-${kindShort}-${goodsShort}-${transportLetter}`;
    $('#license_number').val(mcaRef);
    $('#license_number_mca').val(mcaRef);
  }
}

function removeRequiredFromHiddenFields() {
  const hiddenFields = [
    '#bank_id', '#license_cleared_by', '#weight', '#m3', '#unit_of_measurement_id',
    '#fob_declared', '#invoice_number', '#invoice_date', '#supplier',
    '#license_applied_date', '#license_validation_date', '#license_expiry_date',
    '#entry_post_id', '#payment_method_id', '#destination_id'
  ];
  hiddenFields.forEach(field => $(field).removeAttr('required'));
}

// ===== CLIENT HANDLING =====

function handleClientChange() {
  if (isMCAType) {
    generateMCAReference();
    return;
  }
  loadClientLicenseSetting($(this).val());
}

function loadClientLicenseSetting(clientId) {
  if (!clientId) {
    $('#license_cleared_by').val('');
    return;
  }
  $.ajax({
    url: BASE_URL + '/license/getClientLicenseSetting',
    method: 'GET',
    data: { client_id: clientId },
    dataType: 'json',
    success: function (res) {
      if (res.success) {
        $('#license_cleared_by').val(res.license_cleared_by);
      } else {
        $('#license_cleared_by').val('');
      }
    },
    error: function() {
      console.error('Failed to load client license setting');
    }
  });
}

// ===== STATISTICS =====

function updateStatistics() {
  $.ajax({
    url: BASE_URL + '/license/crudData/statistics',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        updateStatisticsUI(res.data);
      }
    },
    error: function() {
      console.error('Failed to load statistics');
    }
  });
}

function updateStatisticsUI(data) {
  $('#totalLicenses, #statTotal').text(data.total_licenses || 0);
  $('#expiredLicenses, #statExpired').text(data.expired_licenses || 0);
  $('#expiringLicenses, #statExpiring').text(data.expiring_licenses || 0);
  $('#incompleteLicenses, #statIncomplete').text(data.incomplete_licenses || 0);
  $('#inactiveLicenses, #statInactive').text(data.inactive_licenses || 0);
  $('#annulatedLicenses, #statAnnulated').text(data.annulated_licenses || 0);
  $('#modifiedLicenses, #statModified').text(data.modified_licenses || 0);
  $('#prorogatedLicenses, #statProrogated').text(data.prorogated_licenses || 0);
  $('#statTotalFob').text(data.total_fob_value || '0.00');
}

// ===== FILTER HANDLING =====

function handleFilterCardClick() {
  const cardId = $(this).attr('id');
  
  if (['expiredCard', 'expiringCard', 'incompleteCard'].includes(cardId)) {
    return;
  }
  
  const filter = $(this).data('filter');
  setActiveFilter(filter);
  
  if (filter === 'all') {
    $('#clearFilterBtnImport, #clearFilterBtnExport').hide();
  } else {
    $('#clearFilterBtnImport, #clearFilterBtnExport').show();
  }
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

function setActiveFilter(filter) {
  $('.filter-card').removeClass('active-filter');
  $(`.filter-card[data-filter="${filter}"]`).addClass('active-filter');
  currentFilter = filter;
}

function clearFilter() {
  setActiveFilter('all');
  $('#clearFilterBtnImport, #clearFilterBtnExport').hide();
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

// ===== ORIGIN MANAGEMENT =====

function openAddOriginModal(e) {
  e.preventDefault();
  $('#addOriginForm')[0].reset();
  $('#new_origin_name').removeClass('is-invalid');
  $('#addOriginModal').modal('show');
}

function saveNewOrigin() {
  const originName = $('#new_origin_name').val().trim();
  
  if (!originName) {
    $('#new_origin_name').addClass('is-invalid');
    return;
  }
  
  $('#new_origin_name').removeClass('is-invalid');
  
  const btn = $(this);
  const originalText = btn.html();
  btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/addOrigin',
    method: 'POST',
    data: { origin_name: originName },
    dataType: 'json',
    success: function(res) {
      btn.prop('disabled', false).html(originalText);
      
      if (res.success) {
        showSuccessMessage(res.message);
        refreshOriginDropdown(res.data);
        $('#addOriginModal').modal('hide');
      } else {
        showErrorMessage(res.message);
      }
    },
    error: function() {
      btn.prop('disabled', false).html(originalText);
      showErrorMessage('Failed to add destination/origin. Please try again.');
    }
  });
}

function refreshOriginDropdown(newOrigin) {
  $.ajax({
    url: BASE_URL + '/license/crudData/getOrigins',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data) {
        const $dropdown = $('#destination_id');
        const currentValue = $dropdown.val();
        
        $dropdown.find('option:not(:first)').remove();
        
        res.data.forEach(function(origin) {
          $dropdown.append(new Option(origin.origin_name, origin.id));
        });
        
        if (newOrigin && newOrigin.id) {
          $dropdown.val(newOrigin.id);
        } else if (currentValue) {
          $dropdown.val(currentValue);
        }
      }
    },
    error: function() {
      console.error('Failed to refresh origin dropdown');
    }
  });
}

// ===== MODAL HANDLERS =====

function showExpiredLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $.ajax({
    url: BASE_URL + '/license/crudData/expiredLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#expiredLicensesContent').html(buildExpiredLicensesHTML(res.data));
      } else {
        $('#expiredLicensesContent').html(getNoDataAlertHTML('No expired licenses found.'));
      }
      $('#expiredLicensesModal').modal('show');
    },
    error: function() {
      showErrorMessage('Failed to load expired licenses');
    }
  });
  
  setActiveFilter('expired');
  $('#clearFilterBtnImport, #clearFilterBtnExport').show();
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

function buildExpiredLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const daysExpired = parseInt(license.days_expired);
    const expiryDate = new Date(license.license_expiry_date).toLocaleDateString('en-US');
    const appliedDate = license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A';
    
    html += `
      <div class="expired-license-item">
        <div class="row align-items-center">
          <div class="col-md-2">
            <span class="badge days-expired-badge">${daysExpired} days ago</span>
          </div>
          <div class="col-md-10">
            <h6 class="mb-1"><strong>License:</strong> ${license.license_number || 'N/A'}</h6>
            <div class="row">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${license.client_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${license.bank_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Applied:</strong> ${appliedDate}</small>
              </div>
            </div>
            <div class="mt-1">
              <small class="text-danger"><strong>Expired:</strong> ${expiryDate}</small>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

function showExpiringLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $.ajax({
    url: BASE_URL + '/license/crudData/expiringLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#expiringLicensesContent').html(buildExpiringLicensesHTML(res.data));
      } else {
        $('#expiringLicensesContent').html(getNoDataAlertHTML('No licenses expiring within 30 days.'));
      }
      $('#expiringLicensesModal').modal('show');
    },
    error: function() {
      showErrorMessage('Failed to load expiring licenses');
    }
  });
  
  setActiveFilter('expiring');
  $('#clearFilterBtnImport, #clearFilterBtnExport').show();
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

function buildExpiringLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const daysRemaining = parseInt(license.days_remaining);
    let badgeClass = 'days-notice';
    if (daysRemaining <= EXPIRING_DAYS_CRITICAL) {
      badgeClass = 'days-critical';
    } else if (daysRemaining <= EXPIRING_DAYS_WARNING) {
      badgeClass = 'days-warning';
    }
    
    const expiryDate = new Date(license.license_expiry_date).toLocaleDateString('en-US');
    const appliedDate = license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A';
    
    html += `
      <div class="expiring-license-item">
        <div class="row align-items-center">
          <div class="col-md-2">
            <span class="badge days-badge ${badgeClass}">${daysRemaining} days</span>
          </div>
          <div class="col-md-10">
            <h6 class="mb-1"><strong>License:</strong> ${license.license_number || 'N/A'}</h6>
            <div class="row">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${license.client_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${license.bank_name || 'N/A'}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Applied:</strong> ${appliedDate}</small>
              </div>
            </div>
            <div class="mt-1">
              <small class="text-danger"><strong>Expires:</strong> ${expiryDate}</small>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

function showIncompleteLicensesModal(e) {
  e.preventDefault();
  e.stopPropagation();
  
  $('#incompleteLicensesContent').html(getLoadingHTML('Loading incomplete licenses...'));
  $('#incompleteLicensesModal').modal('show');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/incompleteLicenses',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        $('#incompleteLicensesContent').html(buildIncompleteLicensesHTML(res.data));
      } else {
        $('#incompleteLicensesContent').html(getNoDataAlertHTML('No incomplete licenses found.'));
      }
    },
    error: function() {
      $('#incompleteLicensesContent').html(getErrorAlertHTML('Failed to load incomplete licenses.'));
    }
  });
  
  setActiveFilter('incomplete');
  $('#clearFilterBtnImport, #clearFilterBtnExport').show();
  
  // Reload both tables
  if (importLicensesTable) {
    importLicensesTable.ajax.reload();
  }
  if (exportLicensesTable) {
    exportLicensesTable.ajax.reload();
  }
}

function buildIncompleteLicensesHTML(licenses) {
  let html = '<div class="list-group">';
  
  licenses.forEach(function(license) {
    const createdDate = license.created_at ? new Date(license.created_at).toLocaleDateString('en-US') : 'N/A';
    const licenseNum = license.license_number || '<span class="text-danger">Not Set</span>';
    const clientName = license.client_name || '<span class="text-danger">Not Set</span>';
    const bankName = license.bank_name || '<span class="text-danger">Not Set</span>';
    
    html += `
      <div class="incomplete-license-item">
        <div class="row">
          <div class="col-md-12">
            <h6 class="mb-2">
              <strong>License:</strong> ${licenseNum}
              <span class="badge bg-warning text-dark ms-2">${license.missing_fields.length} Missing Field${license.missing_fields.length > 1 ? 's' : ''}</span>
            </h6>
            <div class="row mb-2">
              <div class="col-md-4">
                <small><strong>Client:</strong> ${clientName}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Bank:</strong> ${bankName}</small>
              </div>
              <div class="col-md-4">
                <small><strong>Created:</strong> ${createdDate}</small>
              </div>
            </div>
            <div class="missing-fields-list">
              <small><strong class="text-danger">Missing Fields:</strong></small><br>
    `;
    
    if (license.missing_fields && license.missing_fields.length > 0) {
      license.missing_fields.forEach(function(field) {
        const badgeClass = field.includes('(Required)') ? 'badge-required' : 'badge-optional';
        html += `<span class="missing-field-badge ${badgeClass}">${field}</span>`;
      });
    } else {
      html += `<span class="text-muted">No missing fields identified</span>`;
    }
    
    html += `
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  return html;
}

function getNoDataAlertHTML(message) {
  return `<div class="alert alert-info mb-0"><i class="ti ti-info-circle me-2"></i>${message}</div>`;
}

function getErrorAlertHTML(message) {
  return `<div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-2"></i>${message}</div>`;
}

function getLoadingHTML(message) {
  return `
    <div class="text-center p-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">${message}</p>
    </div>
  `;
}

// ===== DATATABLES INITIALIZATION - SEPARATE IMPORT/EXPORT =====

function initDataTables() {
  // Initialize Import Licenses Table (kind_id 1, 2, 5, 6)
  if ($.fn.DataTable.isDataTable('#importLicenseTable')) {
    $('#importLicenseTable').DataTable().destroy();
  }
  
  importLicensesTable = $('#importLicenseTable').DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    ajax: {
      url: BASE_URL + '/license/crudData/listing',
      type: 'GET',
      data: function(d) {
        d.filter = currentFilter;
        d.license_type = 'import'; // Add license type filter
        d.kind_ids = IMPORT_KIND_IDS.join(','); // Pass import kind IDs
        
        if (d.search && d.search.value) {
          d.searchValue = d.search.value;
        }
        
        d.client_id = advancedFilters.client_id;
        d.transport_mode_id = advancedFilters.transport_mode_id;
        d.start_date = advancedFilters.start_date;
        d.end_date = advancedFilters.end_date;
        
        return d;
      },
      error: function (xhr, error, thrown) {
        console.error('Import DataTable Error:', error);
      }
    },
    columns: [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
          return meta.settings._iDisplayStart + meta.row + 1;
        }
      },
      { data: 'license_number', searchable: true },
      { data: 'client_name', searchable: true },
      { data: 'bank_name', searchable: true },
      { 
        data: 'invoice_number',
        searchable: true,
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'license_applied_date',
        searchable: false,
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'license_expiry_date',
        searchable: false,
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'status',
        searchable: false,
        render: function (data) {
          const badges = {
            'ACTIVE': 'success',
            'INACTIVE': 'secondary',
            'ANNULATED': 'danger',
            'MODIFIED': 'warning',
            'PROROGATED': 'info'
          };
          const badge = badges[data] || 'secondary';
          return `<span class="badge bg-${badge}">${data}</span>`;
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return getActionButtonsHTML(row);
        }
      }
    ],
    order: [[1, 'desc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    responsive: true,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    drawCallback: function() {
      updateStatistics();
    },
    language: {
      search: "Search:",
      searchPlaceholder: "License, Client, Bank...",
      emptyTable: "No import licenses found",
      zeroRecords: "No matching import licenses found"
    }
  });

  // Initialize Export Licenses Table (kind_id 3, 4)
  if ($.fn.DataTable.isDataTable('#exportLicenseTable')) {
    $('#exportLicenseTable').DataTable().destroy();
  }
  
  exportLicensesTable = $('#exportLicenseTable').DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    ajax: {
      url: BASE_URL + '/license/crudData/listing',
      type: 'GET',
      data: function(d) {
        d.filter = currentFilter;
        d.license_type = 'export'; // Add license type filter
        d.kind_ids = EXPORT_KIND_IDS.join(','); // Pass export kind IDs
        
        if (d.search && d.search.value) {
          d.searchValue = d.search.value;
        }
        
        d.client_id = advancedFilters.client_id;
        d.transport_mode_id = advancedFilters.transport_mode_id;
        d.start_date = advancedFilters.start_date;
        d.end_date = advancedFilters.end_date;
        
        return d;
      },
      error: function (xhr, error, thrown) {
        console.error('Export DataTable Error:', error);
      }
    },
    columns: [
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row, meta) {
          return meta.settings._iDisplayStart + meta.row + 1;
        }
      },
      { data: 'license_number', searchable: true },
      { data: 'client_name', searchable: true },
      { data: 'bank_name', searchable: true },
      { 
        data: 'invoice_number',
        searchable: true,
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: 'license_applied_date',
        searchable: false,
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'license_expiry_date',
        searchable: false,
        render: function (data) {
          return data ? new Date(data).toLocaleDateString('en-US') : '-';
        }
      },
      {
        data: 'status',
        searchable: false,
        render: function (data) {
          const badges = {
            'ACTIVE': 'success',
            'INACTIVE': 'secondary',
            'ANNULATED': 'danger',
            'MODIFIED': 'warning',
            'PROROGATED': 'info'
          };
          const badge = badges[data] || 'secondary';
          return `<span class="badge bg-${badge}">${data}</span>`;
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return getActionButtonsHTML(row);
        }
      }
    ],
    order: [[1, 'desc']],
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    responsive: true,
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    drawCallback: function() {
      // Statistics already updated by import table
    },
    language: {
      search: "Search:",
      searchPlaceholder: "License, Client, Bank...",
      emptyTable: "No export licenses found",
      zeroRecords: "No matching export licenses found"
    }
  });
}

function getActionButtonsHTML(row) {
  return `
    <button class="btn btn-sm btn-view viewBtn" data-id="${row.id}" title="View Details">
      <i class="ti ti-eye"></i>
    </button>
    <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}" title="Edit">
      <i class="ti ti-edit"></i>
    </button>
    <button class="btn btn-sm btn-export exportBtn" data-id="${row.id}" data-license="${row.license_number}" title="Export to Excel">
      <i class="ti ti-file-spreadsheet"></i>
    </button>
  `;
}

// ===== EXPORT FUNCTIONS =====

function exportAllLicenses() {
  showLoadingMessage('Generating Excel with Multiple Sheets...', 'Please wait while we export licenses grouped by Transport Mode');

  let url = BASE_URL + '/license/crudData/exportAll';
  url += '?filter=' + encodeURIComponent(currentFilter);
  url += '&search=' + encodeURIComponent(currentSearch);
  url += '&client_id=' + encodeURIComponent(advancedFilters.client_id);
  url += '&transport_mode_id=' + encodeURIComponent(advancedFilters.transport_mode_id);
  url += '&start_date=' + encodeURIComponent(advancedFilters.start_date);
  url += '&end_date=' + encodeURIComponent(advancedFilters.end_date);

  window.location.href = url;
  
  setTimeout(function() {
    Swal.close();
  }, 2000);
}

function exportSingleLicense() {
  const id = $(this).data('id');
  
  showLoadingMessage('Generating Excel...', 'Please wait');

  window.location.href = BASE_URL + '/license/crudData/exportLicense?id=' + id;
  
  setTimeout(function() {
    Swal.close();
  }, 1000);
}

// ===== CRUD OPERATIONS =====

function viewLicenseDetails() {
  const id = $(this).data('id');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/getLicense',
    method: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function (res) {
      if (res.success && res.data) {
        $('#modalDetailsContent').html(buildLicenseDetailsHTML(res.data));
        $('#viewLicenseModal').modal('show');
      } else {
        showErrorMessage(res.message || 'Failed to load license data');
      }
    },
    error: function () {
      showErrorMessage('Failed to load license data');
    }
  });
}

function buildLicenseDetailsHTML(license) {
  const isSpecial = isSpecialKind(license.kind_id);
  const entryPostLabel = isSpecial ? 'Entry Post/Exit Post' : 'Entry Post';
  
  let m3Row = '';
  if (license.m3 && license.m3 > 0) {
    m3Row = `
      <div class="detail-row">
        <div class="row">
          <div class="col-md-6">
            <div class="detail-label">
              <i class="ti ti-box detail-icon"></i>M3
            </div>
            <div class="detail-value">${license.m3}</div>
          </div>
        </div>
      </div>
    `;
  }
  
  let invoiceSection = '';
  if (!isSpecial) {
    invoiceSection = `
      <div class="detail-row">
        <div class="row">
          <div class="col-md-6">
            <div class="detail-label">
              <i class="ti ti-file-invoice detail-icon"></i>Invoice Number
            </div>
            <div class="detail-value">${license.invoice_number || 'N/A'}</div>
          </div>
          <div class="col-md-6">
            <div class="detail-label">
              <i class="ti ti-calendar detail-icon"></i>Invoice Date
            </div>
            <div class="detail-value">${license.invoice_date ? new Date(license.invoice_date).toLocaleDateString('en-US') : 'N/A'}</div>
          </div>
        </div>
      </div>
    `;
  }
  
  let appliedDateSection = '';
  if (!isSpecial) {
    appliedDateSection = `
      <div class="col-md-4">
        <div class="detail-label">
          <i class="ti ti-calendar-event detail-icon"></i>Applied Date
        </div>
        <div class="detail-value">${license.license_applied_date ? new Date(license.license_applied_date).toLocaleDateString('en-US') : 'N/A'}</div>
      </div>
    `;
  }
  
  let refCodSection = '';
  if (!isSpecial) {
    refCodSection = `
      <div class="col-md-6">
        <div class="detail-label">
          <i class="ti ti-hash detail-icon"></i>REF. COD
        </div>
        <div class="detail-value">${license.ref_cod || 'N/A'}</div>
      </div>
    `;
  }
  
  return `
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-file-text detail-icon"></i>License Number
          </div>
          <div class="detail-value">${license.license_number || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-building detail-icon"></i>Client
          </div>
          <div class="detail-value">${license.client_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-building-bank detail-icon"></i>Bank
          </div>
          <div class="detail-value">${license.bank_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-category detail-icon"></i>Kind
          </div>
          <div class="detail-value">${license.kind_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    ${invoiceSection}
    
    <div class="detail-row">
      <div class="row">
        ${appliedDateSection}
        <div class="col-md-${isSpecial ? '6' : '4'}">
          <div class="detail-label">
            <i class="ti ti-calendar-check detail-icon"></i>Validation Date
          </div>
          <div class="detail-value">${license.license_validation_date ? new Date(license.license_validation_date).toLocaleDateString('en-US') : 'N/A'}</div>
        </div>
        <div class="col-md-${isSpecial ? '6' : '4'}">
          <div class="detail-label">
            <i class="ti ti-calendar-x detail-icon"></i>Expiry Date
          </div>
          <div class="detail-value">${license.license_expiry_date ? new Date(license.license_expiry_date).toLocaleDateString('en-US') : 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-weight detail-icon"></i>Weight
          </div>
          <div class="detail-value">${license.weight || 'N/A'} ${license.unit_name || ''}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-currency-dollar detail-icon"></i>FOB Declared
          </div>
          <div class="detail-value">${license.fob_declared || 'N/A'} ${license.currency_short_name || ''}</div>
        </div>
      </div>
    </div>
    
    ${m3Row}
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-truck-delivery detail-icon"></i>Transport Mode
          </div>
          <div class="detail-value">${license.transport_mode_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-user detail-icon"></i>Supplier/Buyer
          </div>
          <div class="detail-value">${license.supplier || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    <div class="detail-row">
      <div class="row">
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-map-pin detail-icon"></i>${entryPostLabel}
          </div>
          <div class="detail-value">${license.entry_post_name || 'N/A'}</div>
        </div>
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-world detail-icon"></i>Destination/Origin
          </div>
          <div class="detail-value">${license.destination_name || 'N/A'}</div>
        </div>
      </div>
    </div>
    
    ${!isSpecial ? `
    <div class="detail-row">
      <div class="row">
        ${refCodSection}
        <div class="col-md-6">
          <div class="detail-label">
            <i class="ti ti-flag detail-icon"></i>Status
          </div>
          <div class="detail-value">
            <span class="badge bg-${license.status === 'ACTIVE' ? 'success' : 'secondary'}">${license.status || 'N/A'}</span>
          </div>
        </div>
      </div>
    </div>
    ` : `
    <div class="detail-row">
      <div class="row">
        <div class="col-md-12">
          <div class="detail-label">
            <i class="ti ti-flag detail-icon"></i>Status
          </div>
          <div class="detail-value">
            <span class="badge bg-${license.status === 'ACTIVE' ? 'success' : 'secondary'}">${license.status || 'N/A'}</span>
          </div>
        </div>
      </div>
    </div>
    `}
  `;
}

function editLicense() {
  const id = $(this).data('id');
  
  $.ajax({
    url: BASE_URL + '/license/crudData/getLicense',
    method: 'GET',
    data: { id: id },
    dataType: 'json',
    success: function (res) {
      if (res.success && res.data) {
        populateFormForEdit(res.data);
      } else {
        showErrorMessage(res.message || 'Failed to load license data');
      }
    },
    error: function () {
      showErrorMessage('Failed to load license data');
    }
  });
}

function populateFormForEdit(license) {
  $('.form-control, .form-select').removeClass('is-invalid');
  
  $('#license_id').val(license.id);
  $('#formAction').val('update');
  $('#formTitle').text('Edit License');
  $('#submitBtnText').text('Update License');
  $('#resetFormBtn').show();

  $('#kind_id').val(license.kind_id);
  
  isMCAType = isMCAKind(license.kind_id);
  isSpecialType = isSpecialKind(license.kind_id);
  
  resetFieldStates();
  
  if (isMCAType) {
    toggleFieldsForMCAType(true);
  } else if (isSpecialType) {
    toggleFieldsForSpecialType(true);
  } else {
    showStandardFields();
  }

  const fillableFields = [
    'kind_id', 'bank_id', 'client_id', 'license_cleared_by', 'type_of_goods_id', 'weight', 'm3',
    'unit_of_measurement_id', 'currency_id', 'fob_declared', 'insurance', 'freight', 'other_costs',
    'transport_mode_id', 'invoice_number', 'invoice_date', 'supplier',
    'license_applied_date', 'license_validation_date', 'license_expiry_date',
    'fsi', 'aur', 'license_number', 'entry_post_id', 'ref_cod',
    'payment_method_id', 'payment_subtype_id', 'destination_id', 'status'
  ];

  fillableFields.forEach(function (key) {
    if (typeof license[key] !== 'undefined' && license[key] !== null) {
      $('#' + key).val(license[key]);
    } else {
      $('#' + key).val('');
    }
  });
  
  const typeOfGoodsId = parseInt(license.type_of_goods_id);
  if (typeOfGoodsId === TYPE_OF_GOODS_M3_ID && !isMCAType) {
    $('#m3Field').show();
    $('#m3').attr('required', true);
    if (license.m3) {
      $('#m3').val(license.m3);
    }
    requiresM3 = true;
  }

  if (isMCAType) {
    $('#transport_mode_id').val(license.transport_mode_id);
    $('#currency_id').val(license.currency_id);
    $('#transport_mode_id_mca').val(license.transport_mode_id);
    $('#currency_id_mca').val(license.currency_id);
    $('#license_number').val(license.license_number);
    $('#license_number_mca').val(license.license_number);
  }

  if (!isMCAType && license.client_id) {
    setTimeout(function () {
      $('#license_cleared_by').val(license.license_cleared_by);
    }, 100);
  }

  if (license.invoice_file) {
    $('#current_invoice_file').html(`<a href="${BASE_URL}/${license.invoice_file}" target="_blank" class="text-primary"><i class="ti ti-file-text"></i> View Current Invoice</a>`);
  } else {
    $('#current_invoice_file').text('');
  }
  
  if (license.license_file) {
    $('#current_license_file').html(`<a href="${BASE_URL}/${license.license_file}" target="_blank" class="text-primary"><i class="ti ti-file-text"></i> View Current License</a>`);
  } else {
    $('#current_license_file').text('');
  }

  $('#createLicense').collapse('show');
  $('html, body').animate({ scrollTop: $('#licenseForm').offset().top - 100 }, 500);
}

// ===== FORM VALIDATION =====

function handleAppliedDateChange() {
  const appliedDate = $(this).val();
  if (appliedDate) {
    $('#license_validation_date').attr('min', appliedDate);
    $('#license_expiry_date').attr('min', appliedDate);
  }
}

function handleValidationDateChange() {
  const validationDate = $(this).val();
  if (validationDate) {
    $('#license_expiry_date').attr('min', validationDate);
  }
}

function validateNumericInput() {
  const value = parseFloat($(this).val());
  if (value < 0) {
    $(this).val(0);
  }
}

function validateFileInput() {
  const file = this.files[0];
  if (file) {
    const fileType = file.type;
    if (fileType !== 'application/pdf') {
      $(this).val('');
      $('#createLicense').collapse('show');
      showErrorMessage('Only PDF files are allowed');
    }
  }
}

// ===== FORM SUBMISSION =====

function handleFormSubmit(e) {
  e.preventDefault();

  $('.form-control, .form-select').removeClass('is-invalid');
  
  if (isMCAType) {
    $('#transport_mode_id').val($('#transport_mode_id_mca').val());
    $('#currency_id').val($('#currency_id_mca').val());
  }
  
  if (requiresM3 && !isMCAType) {
    const m3Value = $('#m3').val();
    if (!m3Value || parseFloat(m3Value) <= 0) {
      $('#m3').addClass('is-invalid');
      $('#createLicense').collapse('show');
      showErrorMessage('M3 is required and must be positive when Type of Goods requires it');
      return;
    }
  }
  
  if (!this.checkValidity()) {
    e.stopPropagation();
    $(this).find(':invalid').addClass('is-invalid');
    $('#createLicense').collapse('show');
    
    scrollToFirstError();
    return;
  }

  if (!isMCAType && !validateDates()) {
    return;
  }

  submitForm();
}

function validateDates() {
  if (!isSpecialType) {
    const invoiceDate = new Date($('#invoice_date').val());
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);

    if ($('#invoice_date').val() && invoiceDate > todayDate) {
      $('#invoice_date').addClass('is-invalid');
      $('#createLicense').collapse('show');
      showErrorMessage('Invoice date cannot be in the future');
      return false;
    }
    
    const appliedDate = new Date($('#license_applied_date').val());
    const validationDate = new Date($('#license_validation_date').val());

    if ($('#license_applied_date').val() && $('#license_validation_date').val() && appliedDate > validationDate) {
      $('#license_validation_date').addClass('is-invalid');
      $('#createLicense').collapse('show');
      showErrorMessage('Validation date must be greater than or equal to applied date');
      return false;
    }
  }

  const validationDate = new Date($('#license_validation_date').val());
  const expiryDate = new Date($('#license_expiry_date').val());

  if ($('#license_validation_date').val() && $('#license_expiry_date').val() && validationDate > expiryDate) {
    $('#license_expiry_date').addClass('is-invalid');
    $('#createLicense').collapse('show');
    showErrorMessage('Expiry date must be greater than or equal to validation date');
    return false;
  }
  
  return true;
}

function submitForm() {
  const formData = new FormData($('#licenseForm')[0]);
  const action = $('#formAction').val();
  const url = action === 'update'
    ? BASE_URL + '/license/crudData/update'
    : BASE_URL + '/license/crudData/insertion';

  const submitBtn = $('#submitBtn');
  const originalText = submitBtn.html();
  submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

  $.ajax({
    url: url,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (res) {
      submitBtn.prop('disabled', false).html(originalText);

      if (res.success) {
        showSuccessMessage(res.message || 'Saved successfully', 1500);
        resetForm();
        
        // Reload both tables
        if (importLicensesTable) {
          importLicensesTable.ajax.reload(null, false);
        }
        if (exportLicensesTable) {
          exportLicensesTable.ajax.reload(null, false);
        }
        
        updateStatistics();
      } else {
        if (res.message) {
          showErrorMessage(res.message);
          $('#createLicense').collapse('show');
        }
      }
    },
    error: function (xhr) {
      submitBtn.prop('disabled', false).html(originalText);
      let errorMsg = 'An error occurred while processing your request';
      try {
        const response = JSON.parse(xhr.responseText);
        errorMsg = response.message || errorMsg;
      } catch (e) {
        errorMsg = xhr.responseText || errorMsg;
      }
      showErrorMessage(errorMsg);
    }
  });
}

function scrollToFirstError() {
  const firstError = $('.is-invalid').first();
  if (firstError.length) {
    $('html, body').animate({
      scrollTop: firstError.offset().top - 100
    }, 300);
  }
}

// ===== FORM RESET =====

function resetForm(e) {
  if (e) {
    e.preventDefault();
  }
  
  $('#licenseForm')[0].reset();
  $('.form-control, .form-select').removeClass('is-invalid');
  $('#license_id').val('');
  $('#formAction').val('insert');
  $('#formTitle').text('Add New License');
  $('#submitBtnText').text('Save License');
  $('#resetFormBtn').hide();
  $('#current_invoice_file, #current_license_file').text('');
  $('#createLicense').collapse('hide');
  $('#license_validation_date').removeAttr('min');
  $('#license_expiry_date').removeAttr('min');
  $('#invoice_date').attr('max', today);
  $('#license_number').attr('readonly', false).val('');
  $('#licenseNumberHelp').text('');
  
  isMCAType = false;
  isSpecialType = false;
  requiresM3 = false;
  
  resetFieldStates();
  
  $('html, body').animate({ scrollTop: $('#licenseForm').offset().top - 100 }, 200);
}

// ===== UI HELPER FUNCTIONS =====

function showSuccessMessage(message, timer = null) {
  const config = {
    icon: 'success',
    title: 'Success!',
    text: message
  };
  
  if (timer) {
    config.timer = timer;
    config.showConfirmButton = false;
  }
  
  Swal.fire(config);
}

function showErrorMessage(message) {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    html: message
  });
}

function showLoadingMessage(title, text) {
  Swal.fire({
    title: title,
    text: text,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
}
</script>