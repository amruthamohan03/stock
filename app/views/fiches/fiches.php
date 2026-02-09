<!-- include any head / css you already have -->
<link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">

<!-- DataTables Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<!-- Select2 CSS for Searchable Dropdowns -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- SheetJS for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
  .dataTables_wrapper .dataTables_info { float: left; }
  .dataTables_wrapper .dataTables_paginate { float: right; text-align: right; }
  
  /* Export Button Styling - Green */
  .dt-buttons { float: left; margin-bottom: 10px; }
  .buttons-excel {
    background: #28a745 !important; color: white !important; border: none !important;
    padding: 8px 20px !important; border-radius: 5px !important; font-weight: 500 !important;
    transition: all 0.3s !important; box-shadow: none !important;
  }
  .buttons-excel:hover {
    background: #218838 !important; color: white !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4) !important;
  }
  
  /* Individual Export Button */
  .btn-export {
    background: #28a745; color: white; border: none;
  }
  .btn-export:hover {
    background: #218838; color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
  }
  
  /* Required field indicator */
  .text-danger { color: #dc3545; font-weight: bold; }
  
  /* Validation Error Styling */
  .is-invalid { border-color: #dc3545 !important; }
  .invalid-feedback { display: block; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
  
  /* Colorful View Button */
  .btn-view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; border: none;
  }
  .btn-view:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white; transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
  }
  
  /* Stats Cards - Smaller and Clickable */
  .stats-card {
    border: none; border-radius: 10px;
    transition: all 0.3s ease; overflow: hidden;
    background: white; border: 1px solid #e9ecef;
    cursor: pointer; position: relative;
  }
  .stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #007bff;
  }
  .stats-card.active {
    border-color: #007bff; background: #f8f9ff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
  }
  .stats-card .card-body {
    padding: 15px; position: relative;
  }
  
  /* Stats Card Icons - Smaller */
  .stats-card-icon {
    width: 35px; height: 35px;
    border-radius: 8px; display: flex;
    align-items: center; justify-content: center;
    margin-bottom: 8px; float: left; margin-right: 10px;
  }
  .stats-card-icon i { font-size: 18px; color: white; }
  
  .icon-blue { background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); }
  .icon-green { background: linear-gradient(135deg, #2ECC71 0%, #27AE60 100%); }
  .icon-orange { background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%); }
  .icon-gray { background: linear-gradient(135deg, #95A5A6 0%, #7F8C8D 100%); }
  
  .stats-value {
    font-size: 1.4rem; font-weight: 700; color: #2C3E50;
    margin-bottom: 2px; line-height: 1.2;
  }
  .stats-label {
    font-size: 0.75rem; color: #7F8C8D;
    font-weight: 500; line-height: 1.2;
  }
  
  /* Clear float */
  .stats-card .card-body::after {
    content: ""; display: table; clear: both;
  }
  
  /* Modal Styling */
  .modal-content { border: none; border-radius: 15px; overflow: hidden; }
  .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; border: none; padding: 20px 30px;
  }
  .modal-header .btn-close { filter: brightness(0) invert(1); }
  .detail-row {
    padding: 15px; border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
  }
  .detail-row:hover { background: #f8f9fa; }
  .detail-row:last-child { border-bottom: none; }
  .detail-label {
    font-weight: 600; color: #667eea;
    font-size: 0.9rem; margin-bottom: 5px;
  }
  .detail-value { color: #2d3748; font-size: 1rem; font-weight: 500; }
  .detail-icon { color: #667eea; margin-right: 8px; }

  /* Auto-generated field styling */
  .auto-generated-field { 
    background-color: #d4edda !important; 
    cursor: not-allowed; 
    font-weight: 600;
  }
  .readonly-field { background-color: #e9ecef; cursor: not-allowed; }
  
  /* CIF Field - Green Background */
  .cif-field { 
    background-color: #d4edda !important; 
    font-weight: 700; 
    font-size: 1.1rem;
    border: 2px solid #28a745;
  }
  
  /* Coefficient Field - Auto Calculated */
  .coefficient-field {
    background-color: #fff3cd !important;
    font-weight: 700;
    border: 2px solid #ffc107;
    cursor: not-allowed;
  }
  
  .accordion-button:not(.collapsed) { background-color: #667eea; color: white; }

  /* Custom 5 columns per row */
  @media (min-width: 768px) {
    .col-md-2-4 {
      flex: 0 0 auto;
      width: 20%; /* 100% / 5 = 20% */
    }
  }

  /* Filter indicator */
  .filter-indicator {
    position: absolute; top: 8px; right: 8px;
    background: #007bff; color: white; border-radius: 50%;
    width: 20px; height: 20px; display: none;
    align-items: center; justify-content: center;
    font-size: 10px; font-weight: bold;
  }
  .stats-card.active .filter-indicator { display: flex; }
  
  /* Horizontal scroll for datatable */
  .dataTables_wrapper .dataTables_scroll {
    overflow-x: auto;
  }
  
  .dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto;
  }

  /* USD Rate fixed field */
  .usd-rate-fixed {
    background: #e3f2fd;
    font-weight: 600;
    cursor: not-allowed;
  }
  
  /* Select2 Custom Styling */
  .select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
    border-color: #ced4da;
  }
  
  .select2-container--bootstrap-5 .select2-selection--single {
    padding: 0.375rem 0.75rem;
  }
  
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 1.5;
    padding-left: 0;
  }
  
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 36px;
  }
  
  .select2-dropdown {
    border-color: #ced4da;
  }
  
  .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
    border-color: #ced4da;
    padding: 0.375rem 0.75rem;
  }
  
  /* Items Table Styling */
  #itemsTable {
    font-size: 0.85rem;
  }
  
  #itemsTable thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.75rem;
    font-weight: 700;
    text-align: center;
    padding: 10px 5px;
    white-space: nowrap;
  }
  
  #itemsTable tbody td {
    padding: 5px;
    vertical-align: middle;
  }
  
  #itemsTable .form-control-sm {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
  }
  
  #itemsTable tfoot td {
    padding: 10px 5px;
    text-align: center;
    font-weight: 700;
    background: #f8f9fa;
    font-size: 0.85rem;
  }
  
  .item-row {
    transition: background-color 0.2s;
  }
  
  .item-row:hover {
    background-color: #f8f9fa;
  }
  
  .table-responsive {
    max-height: 500px;
    overflow-y: auto;
  }

  /* Conversion Fields Styling */
  .conversion-fields {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    padding: 20px;
    border-radius: 10px;
    margin-top: 15px;
    margin-bottom: 15px;
    border: 2px dashed #2196f3;
    display: none; /* Hidden by default */
  }

  .conversion-display {
    background: #bbdefb !important;
    font-weight: 700;
    color: #0d47a1;
    cursor: not-allowed;
    border: 2px solid #2196f3;
    font-size: 1.05rem;
  }

  .conversion-label {
    color: #1976d2;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .conversion-icon {
    color: #2196f3;
    margin-right: 5px;
  }
  
  /* Items Management Header with Buttons */
  .items-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .items-title {
    color: #f39c12;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
  }
  
  .items-buttons {
    display: flex;
    gap: 10px;
  }

  /* Enhanced Status Dropdown Styling */
  #status {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #ced4da;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    color: #495057;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  #status:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background: white;
  }

  #status:hover {
    border-color: #007bff;
    background: white;
  }

  #status option {
    padding: 10px;
    font-weight: 600;
  }

  /* Wider CIF and DDI fields */
  .item-cif-wide, .item-ddi-wide {
    min-width: 150px !important;
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- Statistics Cards with Icons -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="all">
              <div class="card-body">
                <div class="stats-card-icon icon-blue">
                  <i class="ti ti-file-invoice"></i>
                </div>
                <div class="stats-value" id="totalFiches">0</div>
                <div class="stats-label">Total Fiches</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="created">
              <div class="card-body">
                <div class="stats-card-icon icon-gray">
                  <i class="ti ti-file-plus"></i>
                </div>
                <div class="stats-value" id="totalCreated">0</div>
                <div class="stats-label">Created</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="verified">
              <div class="card-body">
                <div class="stats-card-icon icon-orange">
                  <i class="ti ti-circle-check"></i>
                </div>
                <div class="stats-value" id="totalVerified">0</div>
                <div class="stats-label">Verified</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="audited">
              <div class="card-body">
                <div class="stats-card-icon icon-green">
                  <i class="ti ti-file-check"></i>
                </div>
                <div class="stats-value" id="totalAudited">0</div>
                <div class="stats-label">Audited</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Fiche Form Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-file-invoice me-2"></i> <span id="formTitle">Add New Fiche</span></h4>
            <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn" style="display:none;">
              <i class="ti ti-plus"></i> Add New
            </button>
          </div>

          <div class="card-body">
            <!-- SECURE: CSRF token in data attribute -->
            <form id="ficheForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="id" id="fiche_id" value="">
              <input type="hidden" name="action" id="formAction" value="insert">
              
              <!-- Hidden fields for actual values that controller expects -->
              <input type="hidden" name="license_number" id="license_number">
              <input type="hidden" name="tracking_id" id="tracking_id">
              <input type="hidden" name="mca_ref" id="mca_ref">
              <input type="hidden" name="regime" id="regime">
              <input type="hidden" name="currency" id="currency">
              <input type="hidden" name="fob_currency" id="fob_currency_value">
              <input type="hidden" name="fret_currency" id="fret_currency_value">
              <input type="hidden" name="insurance_amount_currency" id="insurance_amount_currency_value">
              <input type="hidden" name="autres_charges_currency" id="autres_charges_currency_value">
              <input type="hidden" name="incoterm_short" id="incoterm_short">
              <input type="hidden" name="incoterm_full" id="incoterm_full">

              <div class="accordion" id="ficheAccordion">
                
                <!-- COMBINED FICHE & ITEMS ACCORDION -->
                <div class="accordion-item mb-3">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ficheDetailsSection">
                      <i class="ti ti-file-invoice me-2"></i> Fiche & Items Management
                    </button>
                  </h2>

                  <div id="ficheDetailsSection" class="accordion-collapse collapse" data-bs-parent="#ficheAccordion">
                    <div class="accordion-body">

                      <!-- SUB-SECTION: FICHE DETAILS -->
                      <div class="mb-4 pb-3" style="border-bottom: 2px solid #e9ecef;">
                        <h5 class="mb-3" style="color: #667eea; font-weight: 600;">
                          <i class="ti ti-file-text me-2"></i>Fiche Details
                        </h5>

                      <!-- Row 1: License, MCA, Regime, Fiche Ref -->
                      <div class="row">
                        <div class="col-md-3 mb-3">
                          <label>License Number <span class="text-danger">*</span></label>
                          <select name="license_number_select" id="license_number_select" class="form-select" required>
                            <option value="">-- Select License --</option>
                            <?php foreach ($licenses as $lic): ?>
                              <option value="<?= htmlspecialchars($lic['license_number'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($lic['license_number'], ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="license_number_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>MCA Reference <span class="text-danger">*</span></label>
                          <select name="mca_ref_select" id="mca_ref_select" class="form-select" required>
                            <option value="">-- Select MCA Ref --</option>
                          </select>
                          <div class="invalid-feedback" id="mca_ref_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Regime <span class="text-danger">*</span> <small class="text-muted">(From MCA)</small></label>
                          <input type="text" id="regime_display" class="form-control readonly-field" readonly placeholder="From MCA">
                          <div class="invalid-feedback" id="regime_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Fiche Reference <span class="text-danger">*</span> <small class="text-muted">(from MCA)</small></label>
                          <input type="text" name="fiche_reference" id="fiche_reference" class="form-control auto-generated-field" required readonly placeholder="Auto-generated from MCA">
                          <div class="invalid-feedback" id="fiche_reference_error"></div>
                        </div>
                      </div>

                      <!-- Row 2: Fiche Date, Currency, Transport Mode, Weight, FOB -->
                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Fiche Date <span class="text-danger">*</span> <small class="text-muted">(Auto Today)</small></label>
                          <input type="date" name="fiche_date" id="fiche_date" class="form-control" required>
                          <div class="invalid-feedback" id="fiche_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Currency <span class="text-danger">*</span> <small class="text-muted">(From MCA)</small></label>
                          <select name="currency_select" id="currency_select" class="form-select" required>
                            <option value="">-- Select Currency --</option>
                            <?php foreach ($currencies as $curr): ?>
                              <option value="<?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="currency_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Transport Mode <small class="text-muted">(From MCA)</small></label>
                          <input type="text" name="transport_mode" id="transport_mode" class="form-control readonly-field" readonly placeholder="From MCA">
                          <div class="invalid-feedback" id="transport_mode_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Poids (Weight) <span class="text-danger">*</span> <small class="text-muted">(From MCA)</small></label>
                          <input type="number" step="0.01" name="poids" id="poids" class="form-control" required min="0" placeholder="From MCA">
                          <div class="invalid-feedback" id="poids_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>FOB <span class="text-danger">*</span> <small class="text-muted">(From MCA)</small></label>
                          <input type="number" step="0.01" name="fob" id="fob" class="form-control cif-trigger" required min="0" placeholder="From MCA">
                          <div class="invalid-feedback" id="fob_error"></div>
                        </div>
                      </div>

                      <!-- Row 3: FOB Currency, Insurance Amount, Insurance Currency, Exchange Rate, Fret -->
                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>FOB Currency <small class="text-muted">(From MCA)</small></label>
                          <select name="fob_currency_select" id="fob_currency_select" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($currencies as $curr): ?>
                              <option value="<?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="fob_currency_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Insurance Amount <small class="text-muted">(From MCA)</small></label>
                          <input type="number" step="0.01" name="insurance_amount" id="insurance_amount" class="form-control cif-trigger" min="0" placeholder="From MCA">
                          <div class="invalid-feedback" id="insurance_amount_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Insurance Currency <small class="text-muted">(From MCA)</small></label>
                          <select name="insurance_amount_currency_select" id="insurance_amount_currency_select" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($currencies as $curr): ?>
                              <option value="<?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="insurance_currency_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Exchange Rate (CDF) <span class="text-danger">*</span></label>
                          <input type="number" step="0.000001" name="tx_de_change" id="tx_de_change" class="form-control cif-trigger" required min="0" placeholder="0.000000">
                          <div class="invalid-feedback" id="tx_de_change_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Fret <small class="text-muted">(From MCA)</small></label>
                          <input type="number" step="0.01" name="fret" id="fret" class="form-control cif-trigger" min="0" placeholder="From MCA">
                          <div class="invalid-feedback" id="fret_error"></div>
                        </div>
                      </div>

                      <!-- Row 4: Fret Currency, Other Charges, Other Charges Currency, USD to Currency Rate -->
                      <div class="row">
                        <div class="col-md-3 mb-3">
                          <label>Fret Currency <small class="text-muted">(From MCA)</small></label>
                          <select name="fret_currency_select" id="fret_currency_select" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($currencies as $curr): ?>
                              <option value="<?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="fret_currency_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Other Charges <small class="text-muted">(From MCA)</small></label>
                          <input type="number" step="0.01" name="autres_charges" id="autres_charges" class="form-control cif-trigger" min="0" value="0" placeholder="From MCA">
                          <div class="invalid-feedback" id="autres_charges_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Other Currency <small class="text-muted">(From MCA)</small></label>
                          <select name="autres_charges_currency_select" id="autres_charges_currency_select" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($currencies as $curr): ?>
                              <option value="<?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($curr, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="autres_charges_currency_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label id="usd_rate_label">USD to Currency Rate</label>
                          <input type="number" step="0.01" name="usd_to_currency_rate" id="usd_to_currency_rate" class="form-control usd-rate-fixed" value="1.00" readonly>
                        </div>
                      </div>

                      <!-- Row 5: Provence, CIF, Coefficient, INCOTERM Short, INCOTERM Full -->
                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Provence (Origin)</label>
                          <input type="text" name="provence" id="provence" class="form-control" maxlength="100" placeholder="Enter origin">
                          <div class="invalid-feedback" id="provence_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label id="cif_label">CIF <small class="text-muted">(Auto)</small></label>
                          <input type="number" step="0.01" name="cif" id="cif" class="form-control cif-field" readonly placeholder="0.00">
                          <div class="invalid-feedback" id="cif_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Coefficient <small class="text-muted">(CIF/FOB Auto)</small></label>
                          <input type="number" step="0.000001" name="coefficient" id="coefficient" class="form-control coefficient-field" min="0" value="1.00" placeholder="1.00" readonly>
                          <div class="invalid-feedback" id="coefficient_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>INCOTERM Short</label>
                          <select name="incoterm_short_select" id="incoterm_short_select" class="form-select">
                            <option value="">-- Select INCOTERM --</option>
                            <?php foreach ($incoterms as $key => $value): ?>
                              <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" data-full="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="incoterm_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>INCOTERM Full</label>
                          <input type="text" id="incoterm_full_display" class="form-control readonly-field" readonly placeholder="Full INCOTERM description">
                        </div>
                      </div>

                      </div>

                      <!-- SUB-SECTION: ITEMS MANAGEMENT -->
                      <div class="mt-4">
                        <!-- Items Management Header with Buttons -->
                        <div class="items-header">
                          <h5 class="items-title">
                            <i class="ti ti-package me-2"></i>Items Management
                          </h5>
                          <div class="items-buttons">
                            <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                              <i class="ti ti-plus me-1"></i> Add Item
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="getPositionTarifsBtn">
                              <i class="ti ti-barcode me-1"></i> Get Position Tarifs
                            </button>
                          </div>
                        </div>
                      
                      <!-- Items Table -->
                      <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="itemsTable">
                          <thead style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white;">
                            <tr>
                              <th style="min-width: 150px;">DESCRIPTION</th>
                              <th style="min-width: 100px;">N° BIVAC</th>
                              <th style="min-width: 100px;">N° FACTURE</th>
                              <th style="min-width: 80px;">N°</th>
                              <th style="min-width: 120px;">POSITION TARIF</th>
                              <th style="min-width: 80px;">DDI %</th>
                              <th style="min-width: 80px;">AV</th>
                              <th style="min-width: 80px;">ORG</th>
                              <th style="min-width: 80px;">PROV</th>
                              <th style="min-width: 100px;">CODE ADD</th>
                              <th style="min-width: 80px;">COLIS</th>
                              <th style="min-width: 80px;">QTE</th>
                              <th style="min-width: 100px;">NET</th>
                              <th style="min-width: 100px;">BRUT</th>
                              <th style="min-width: 120px;">FOB PAR ARTICLE</th>
                              <th style="min-width: 80px;">COEF</th>
                              <th style="min-width: 150px;">CIF PAR ARTICLE</th>
                              <th style="min-width: 150px;">DDI</th>
                              <th style="min-width: 100px;">ACTION</th>
                            </tr>
                          </thead>
                          <tbody id="itemsTableBody">
                            <tr class="item-row" data-row="0">
                              <td><input type="text" class="form-control form-control-sm item-description" data-field="description" placeholder="Description"></td>
                              <td><input type="text" class="form-control form-control-sm item-bivac" data-field="no_bivac" placeholder="BIVAC"></td>
                              <td><input type="text" class="form-control form-control-sm item-facture" data-field="no_facture" placeholder="From MCA" readonly style="background: #e9ecef;"></td>
                              <td><input type="number" class="form-control form-control-sm item-number" data-field="numero" value="1" readonly style="background: #e9ecef;"></td>
                              <td><input type="text" class="form-control form-control-sm item-position-tarif" data-field="position_tarrif" placeholder="Position"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi-percent" data-field="ddi_percent" placeholder="0"></td>
                              <td><input type="text" class="form-control form-control-sm item-av" data-field="av" placeholder="AV"></td>
                              <td><input type="text" class="form-control form-control-sm item-org" data-field="org" placeholder="ORG"></td>
                              <td><input type="text" class="form-control form-control-sm item-prov" data-field="prov" placeholder="PROV"></td>
                              <td><input type="text" class="form-control form-control-sm item-code-add" data-field="code_add" placeholder="Code Add"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-colis" data-field="colis" placeholder="0.00"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-qte" data-field="qte" placeholder="0.00"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-net" data-field="net" placeholder="0.00"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-brut" data-field="brut" placeholder="0.00"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-fob" data-field="fob_article" placeholder="0.00"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-coef" data-field="coef" value="1.00" placeholder="1.00" readonly style="background: #fff3cd;"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-cif item-cif-wide" data-field="cif_article" placeholder="0.00" readonly style="background: #d4edda; font-weight: 600;"></td>
                              <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi item-ddi-wide" data-field="ddi" placeholder="0.00" readonly style="background: #fff3cd; font-weight: 600;"></td>
                              <td>
                                <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
                                  <i class="ti ti-trash"></i>
                                </button>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot style="background: #f8f9fa; font-weight: 700;">
                            <tr>
                              <td colspan="10" class="text-end">TOTALS</td>
                              <td><span id="total_colis">0.00</span></td>
                              <td><span id="total_qte">0.00</span></td>
                              <td><span id="total_net">0.00</span></td>
                              <td><span id="total_brut">0.00</span></td>
                              <td><span id="total_fob">0.00</span></td>
                              <td></td>
                              <td><span id="total_cif">0.00</span></td>
                              <td><span id="total_ddi">0.00</span></td>
                              <td></td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      </div>
                      <!-- End Items Management Sub-Section -->

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
                    <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save Fiche</span>
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Fiches DataTable -->
        <div class="card shadow-sm">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-list me-2"></i> Fiches List</h4>
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="clearFilters">
                <i class="ti ti-filter-off me-1"></i> Clear Filters
              </button>
              <span class="badge bg-primary" id="activeFiltersBadge" style="display: none;">0 Filters Active</span>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="fichesTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Fiche Ref</th>
                    <th>Subscriber</th>
                    <th>MCA Ref</th>
                    <th>Fiche Date</th>
                    <th>Weight</th>
                    <th>CIF</th>
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

<!-- Select2 JS for Searchable Dropdowns -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- View Details Modal -->
<div class="modal fade" id="viewFicheModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-eye me-2"></i> Fiche Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="modalDetailsContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Position Tarifs Selection Modal -->
<div class="modal fade" id="positionTarifsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-barcode me-2"></i> Select Position Tarifs
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        
        <div class="alert alert-info">
          <i class="ti ti-info-circle me-2"></i>
          <strong>Instructions:</strong> Select position tarifs from the license. Each selection will create a new item row with the Position Tarif and DDI %.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="positionTarifsTable">
            <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
              <tr>
                <th width="10%">
                  <input type="checkbox" id="selectAllTarifs" style="width: 18px; height: 18px;">
                </th>
                <th width="45%">Position Tarif</th>
                <th width="45%">DDI (%)</th>
              </tr>
            </thead>
            <tbody id="positionTarifsTableBody">
              <tr>
                <td colspan="3" class="text-center text-muted">
                  Please select a license first
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="applyTarifsBtn">
          <i class="ti ti-check me-1"></i> Apply Selected Tarifs
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {

    // SECURE: Get CSRF token from form data attribute
    const csrfToken = $('#ficheForm').data('csrf-token');
    
    // SECURE: Escape HTML to prevent XSS
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // SECURE: Validate and sanitize numeric input
    function sanitizeNumber(value) {
      const num = parseFloat(value);
      return isNaN(num) || num < 0 ? 0 : num;
    }

    let activeFilters = [];
    let itemRowCounter = 0;
    let isUSDCurrency = false;
    let mcaInvoiceNumber = '';

    // ===== CURRENCY CHANGE HANDLER =====
    $('#currency_select').on('change', function() {
      const currencyValue = $(this).val();
      $('#currency').val(currencyValue);
      
      if (!currencyValue) {
        isUSDCurrency = false;
        updateLabels('USD');
        return;
      }

      isUSDCurrency = currencyValue.toUpperCase() === 'USD';
      updateLabels(currencyValue);
      calculateCIF();
    });

    // Update currency hidden fields when selects change
    $('#fob_currency_select').on('change', function() {
      $('#fob_currency_value').val($(this).val());
    });

    $('#fret_currency_select').on('change', function() {
      $('#fret_currency_value').val($(this).val());
    });

    $('#insurance_amount_currency_select').on('change', function() {
      $('#insurance_amount_currency_value').val($(this).val());
    });

    $('#autres_charges_currency_select').on('change', function() {
      $('#autres_charges_currency_value').val($(this).val());
    });

    function updateLabels(currencyShortName) {
      if (isUSDCurrency) {
        $('#usd_rate_label').text('USD to USD Rate');
        $('#cif_label').html('CIF <small class="text-muted">(Auto)</small>');
      } else {
        $('#usd_rate_label').text('USD to ' + currencyShortName + ' Rate');
        $('#cif_label').html('CIF (' + currencyShortName + ') <small class="text-muted">(Auto)</small>');
      }
    }

    // ===== PROVENCE TO PROV AUTO-POPULATION =====
    $('#provence').on('change keyup', function() {
      const provenceValue = $(this).val();
      $('.item-prov').each(function() {
        $(this).val(provenceValue);
      });
    });

    // ===== COEFFICIENT AUTO-SYNC TO ITEMS COEF =====
    $('#coefficient').on('change', function() {
      const coefficientValue = parseFloat($(this).data('full-precision')) || parseFloat($(this).val()) || 1.00;
      
      $('.item-coef').each(function() {
        $(this).data('full-precision', coefficientValue);
        $(this).val(coefficientValue.toFixed(2));
      });
      
      $('.item-row').each(function() {
        calculateItemCIF($(this));
      });
      
      updateItemTotals();
    });

    // ===== GET POSITION TARIFS BUTTON =====
    $('#getPositionTarifsBtn').on('click', function() {
      const licenseNumber = $('#license_number_select').val();
      
      if (!licenseNumber) {
        Swal.fire({
          icon: 'warning',
          title: 'No License Selected',
          text: 'Please select a license first to load position tarifs.',
          timer: 2000
        });
        return;
      }

      loadPositionTarifs(licenseNumber);
      $('#positionTarifsModal').modal('show');
    });

    // Function to load position tarifs for license
    function loadPositionTarifs(licenseNumber) {
      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/getPositionTarifs',
        method: 'GET',
        data: { license_number: licenseNumber },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data.length > 0) {
            displayPositionTarifs(res.data);
          } else {
            $('#positionTarifsTableBody').html(`
              <tr>
                <td colspan="3" class="text-center text-warning">
                  <i class="ti ti-alert-circle me-2"></i>
                  No position tarifs found for this license
                </td>
              </tr>
            `);
            
            Swal.fire({
              icon: 'info',
              title: 'No Position Tarifs',
              text: 'No position tarifs have been assigned to this license yet.',
              timer: 3000
            });
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load position tarifs', 'error');
        }
      });
    }

    // Function to display position tarifs in modal table
    function displayPositionTarifs(tarifs) {
      let html = '';
      
      tarifs.forEach(function(tarif, index) {
        html += `
          <tr>
            <td class="text-center">
              <input type="checkbox" class="tarif-checkbox" 
                data-id="${tarif.id}"
                data-code="${escapeHtml(tarif.tarif_code)}"
                data-ddi="${tarif.ddi}"
                style="width: 18px; height: 18px;">
            </td>
            <td><strong>${escapeHtml(tarif.tarif_code)}</strong></td>
            <td class="text-center">
              <span style="background: #667eea; color: white; padding: 5px 15px; border-radius: 5px; font-weight: 600;">
                ${tarif.ddi}%
              </span>
            </td>
          </tr>
        `;
      });
      
      $('#positionTarifsTableBody').html(html);
    }

    // Select all tarifs checkbox
    $(document).on('change', '#selectAllTarifs', function() {
      const isChecked = $(this).is(':checked');
      $('.tarif-checkbox').prop('checked', isChecked);
    });

    // Apply selected position tarifs to items
    $('#applyTarifsBtn').on('click', function() {
      const selectedTarifs = [];
      
      $('.tarif-checkbox:checked').each(function() {
        selectedTarifs.push({
          id: $(this).data('id'),
          code: $(this).data('code'),
          ddi: $(this).data('ddi')
        });
      });
      
      if (selectedTarifs.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'No Selection',
          text: 'Please select at least one position tarif',
          timer: 2000
        });
        return;
      }
      
      selectedTarifs.forEach(function(tarif) {
        addItemFromTarif(tarif);
      });
      
      $('#positionTarifsModal').modal('hide');
      updateItemTotals();
      
      Swal.fire({
        icon: 'success',
        title: 'Position Tarifs Applied!',
        text: `${selectedTarifs.length} item(s) added from position tarifs`,
        timer: 2000,
        showConfirmButton: false
      });
    });

    // Function to add item row from position tarif
    function addItemFromTarif(tarif) {
      itemRowCounter++;
      const itemNumber = itemRowCounter + 1;
      const provenceValue = $('#provence').val() || '';
      const coefficientValue = parseFloat($('#coefficient').data('full-precision')) || parseFloat($('#coefficient').val()) || 1.00;
      
      const newRow = `
        <tr class="item-row" data-row="${itemRowCounter}">
          <td><input type="text" class="form-control form-control-sm item-description" data-field="description" placeholder="Description"></td>
          <td><input type="text" class="form-control form-control-sm item-bivac" data-field="no_bivac" placeholder="BIVAC"></td>
          <td><input type="text" class="form-control form-control-sm item-facture" data-field="no_facture" value="${escapeHtml(mcaInvoiceNumber)}" placeholder="From MCA" readonly style="background: #e9ecef;"></td>
          <td><input type="number" class="form-control form-control-sm item-number" data-field="numero" value="${itemNumber}" readonly style="background: #e9ecef;"></td>
          <td><input type="text" class="form-control form-control-sm item-position-tarif" data-field="position_tarrif" value="${escapeHtml(tarif.code)}" placeholder="Position"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi-percent" data-field="ddi_percent" value="${tarif.ddi}" placeholder="0"></td>
          <td><input type="text" class="form-control form-control-sm item-av" data-field="av" placeholder="AV"></td>
          <td><input type="text" class="form-control form-control-sm item-org" data-field="org" placeholder="ORG"></td>
          <td><input type="text" class="form-control form-control-sm item-prov" data-field="prov" value="${escapeHtml(provenceValue)}" placeholder="PROV"></td>
          <td><input type="text" class="form-control form-control-sm item-code-add" data-field="code_add" placeholder="Code Add"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-colis" data-field="colis" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-qte" data-field="qte" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-net" data-field="net" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-brut" data-field="brut" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-fob" data-field="fob_article" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-coef" data-field="coef" value="${coefficientValue.toFixed(2)}" placeholder="1.00" readonly style="background: #fff3cd;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-cif item-cif-wide" data-field="cif_article" placeholder="0.00" readonly style="background: #d4edda; font-weight: 600;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi item-ddi-wide" data-field="ddi" placeholder="0.00" readonly style="background: #fff3cd; font-weight: 600;"></td>
          <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
              <i class="ti ti-trash"></i>
            </button>
          </td>
        </tr>
      `;
      
      $('#itemsTableBody').append(newRow);
      
      const newRowElement = $('.item-row[data-row="' + itemRowCounter + '"]');
      newRowElement.find('.item-coef').data('full-precision', coefficientValue);
    }

    // ===== ITEMS MANAGEMENT =====
    $('#addItemBtn').on('click', function() {
      itemRowCounter++;
      const itemNumber = itemRowCounter + 1;
      const provenceValue = $('#provence').val() || '';
      const coefficientValue = parseFloat($('#coefficient').data('full-precision')) || parseFloat($('#coefficient').val()) || 1.00;
      
      const newRow = `
        <tr class="item-row" data-row="${itemRowCounter}">
          <td><input type="text" class="form-control form-control-sm item-description" data-field="description" placeholder="Description"></td>
          <td><input type="text" class="form-control form-control-sm item-bivac" data-field="no_bivac" placeholder="BIVAC"></td>
          <td><input type="text" class="form-control form-control-sm item-facture" data-field="no_facture" value="${escapeHtml(mcaInvoiceNumber)}" placeholder="From MCA" readonly style="background: #e9ecef;"></td>
          <td><input type="number" class="form-control form-control-sm item-number" data-field="numero" value="${itemNumber}" readonly style="background: #e9ecef;"></td>
          <td><input type="text" class="form-control form-control-sm item-position-tarif" data-field="position_tarrif" placeholder="Position"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi-percent" data-field="ddi_percent" placeholder="0"></td>
          <td><input type="text" class="form-control form-control-sm item-av" data-field="av" placeholder="AV"></td>
          <td><input type="text" class="form-control form-control-sm item-org" data-field="org" placeholder="ORG"></td>
          <td><input type="text" class="form-control form-control-sm item-prov" data-field="prov" value="${escapeHtml(provenceValue)}" placeholder="PROV"></td>
          <td><input type="text" class="form-control form-control-sm item-code-add" data-field="code_add" placeholder="Code Add"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-colis" data-field="colis" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-qte" data-field="qte" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-net" data-field="net" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-brut" data-field="brut" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-fob" data-field="fob_article" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-coef" data-field="coef" value="${coefficientValue.toFixed(2)}" placeholder="1.00" readonly style="background: #fff3cd;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-cif item-cif-wide" data-field="cif_article" placeholder="0.00" readonly style="background: #d4edda; font-weight: 600;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi item-ddi-wide" data-field="ddi" placeholder="0.00" readonly style="background: #fff3cd; font-weight: 600;"></td>
          <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
              <i class="ti ti-trash"></i>
            </button>
          </td>
        </tr>
      `;
      
      $('#itemsTableBody').append(newRow);
      
      const newRowElement = $('.item-row[data-row="' + itemRowCounter + '"]');
      newRowElement.find('.item-coef').data('full-precision', coefficientValue);
      
      updateItemTotals();
    });

    $(document).on('click', '.remove-item-btn', function() {
      const row = $(this).closest('tr');
      
      if ($('.item-row').length > 1) {
        row.remove();
        renumberItems();
        updateItemTotals();
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'Cannot Remove',
          text: 'At least one item must remain.',
          timer: 2000
        });
      }
    });

    function renumberItems() {
      $('.item-row').each(function(index) {
        $(this).attr('data-row', index);
        $(this).find('.item-number').val(index + 1);
      });
      
      itemRowCounter = $('.item-row').length - 1;
    }

    function initializeSelect2() {
      if ($('#license_number_select').hasClass('select2-hidden-accessible')) {
        $('#license_number_select').select2('destroy');
      }
      
      $('#license_number_select').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select License --',
        allowClear: true,
        width: '100%'
      });
    }

    // License change handler
    $('#license_number_select').on('change', function() {
      const licenseNumber = $(this).val();
      $('#license_number').val(licenseNumber);
      
      $('#mca_ref_select').html('<option value="">-- Select MCA Ref --</option>');
      
      if (!licenseNumber) {
        clearMCAFields();
        return;
      }

      // Get edit fiche ID if in edit mode
      const editFicheId = $('#fiche_id').val() || 0;

      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/getMCAReferences',
        method: 'GET',
        data: { 
          license_number: licenseNumber,
          edit_fiche_id: editFicheId
        },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data.length > 0) {
            res.data.forEach(function(mca) {
              $('#mca_ref_select').append(`<option value="${escapeHtml(mca.mca_ref)}" data-tracking-id="${mca.id}">${escapeHtml(mca.mca_ref)}</option>`);
            });
          } else {
            Swal.fire({
              icon: 'info',
              title: 'No MCA References',
              text: 'No available MCA references found for this license.',
              timer: 3000
            });
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load MCA references', 'error');
        }
      });
    });

    $(document).on('change keyup', '.item-fob', function() {
      const row = $(this).closest('tr');
      calculateItemCIF(row);
      updateItemTotals();
    });

    $(document).on('change keyup', '.item-cif, .item-ddi-percent', function() {
      const row = $(this).closest('tr');
      calculateItemDDI(row);
      updateItemTotals();
    });

    $(document).on('change keyup', '.item-colis, .item-qte, .item-net, .item-brut', function() {
      updateItemTotals();
    });

    function calculateItemCIF(row) {
      const fob = parseFloat(row.find('.item-fob').val()) || 0;
      const coefField = row.find('.item-coef');
      const coef = parseFloat(coefField.data('full-precision')) || parseFloat(coefField.val()) || 1;
      const cif = fob * coef;
      
      row.find('.item-cif').val(cif.toFixed(2));
      
      calculateItemDDI(row);
    }

    function calculateItemDDI(row) {
      const cif = parseFloat(row.find('.item-cif').val()) || 0;
      const ddiPercent = parseFloat(row.find('.item-ddi-percent').val()) || 0;
      const txDeChange = parseFloat($('#tx_de_change').val()) || 1;
      
      // DDI = (CIF * Exchange Rate * DDI %) / 100, then floored
      const ddiCalculated = (cif * txDeChange * ddiPercent) / 100;
      const ddi = Math.floor(ddiCalculated);
      
      row.find('.item-ddi').val(ddi.toFixed(2));
    }

    function updateItemTotals() {
      let totalColis = 0;
      let totalQte = 0;
      let totalNet = 0;
      let totalBrut = 0;
      let totalFob = 0;
      let totalCif = 0;
      let totalDdi = 0;

      $('.item-row').each(function() {
        totalColis += parseFloat($(this).find('.item-colis').val()) || 0;
        totalQte += parseFloat($(this).find('.item-qte').val()) || 0;
        totalNet += parseFloat($(this).find('.item-net').val()) || 0;
        totalBrut += parseFloat($(this).find('.item-brut').val()) || 0;
        totalFob += parseFloat($(this).find('.item-fob').val()) || 0;
        totalCif += parseFloat($(this).find('.item-cif').val()) || 0;
        totalDdi += parseFloat($(this).find('.item-ddi').val()) || 0;
      });

      $('#total_colis').text(totalColis.toFixed(2));
      $('#total_qte').text(totalQte.toFixed(2));
      $('#total_net').text(totalNet.toFixed(2));
      $('#total_brut').text(totalBrut.toFixed(2));
      $('#total_fob').text(totalFob.toFixed(2));
      $('#total_cif').text(totalCif.toFixed(2));
      $('#total_ddi').text(totalDdi.toFixed(2));
    }

    function setTodayDate() {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      const todayFormatted = `${year}-${month}-${day}`;
      $('#fiche_date').val(todayFormatted);
    }

    function calculateCIF() {
      const fob = parseFloat($('#fob').val()) || 0;
      const fret = parseFloat($('#fret').val()) || 0;
      const insurance = parseFloat($('#insurance_amount').val()) || 0;
      const autresCharges = parseFloat($('#autres_charges').val()) || 0;
      
      const usdToCurrencyRate = parseFloat($('#usd_to_currency_rate').val()) || 1;
      
      let cif = 0;
      
      if (isUSDCurrency) {
        // Direct calculation for USD
        cif = fob + fret + insurance + autresCharges;
      } else {
        // Convert non-USD amounts to USD
        const insuranceConverted = insurance * usdToCurrencyRate;
        const fretConverted = fret * usdToCurrencyRate;
        const autresChargesConverted = autresCharges * usdToCurrencyRate;
        
        cif = fob + insuranceConverted + fretConverted + autresChargesConverted;
      }

      $('#cif').val(cif.toFixed(2));
      
      calculateCoefficient();
    }

    function calculateCoefficient() {
      const fob = parseFloat($('#fob').val()) || 0;
      const cif = parseFloat($('#cif').val()) || 0;
      
      if (fob > 0) {
        const coefficient = cif / fob;
        $('#coefficient').data('full-precision', coefficient);
        $('#coefficient').val(coefficient.toFixed(6));
        
        $('#coefficient').trigger('change');
      } else {
        $('#coefficient').data('full-precision', 1.00);
        $('#coefficient').val('1.00');
        $('#coefficient').trigger('change');
      }
    }

    $('.cif-trigger').on('change keyup', function() {
      calculateCIF();
    });

    $('#incoterm_short_select').on('change', function() {
      const selectedOption = $(this).find('option:selected');
      const shortIncoterm = selectedOption.val();
      const fullIncoterm = selectedOption.data('full');
      
      $('#incoterm_short').val(shortIncoterm);
      $('#incoterm_full').val(fullIncoterm);
      $('#incoterm_full_display').val(fullIncoterm || '');
    });

    $('.stats-card').on('click', function() {
      const filter = $(this).data('filter');
      
      if (filter === 'all') {
        $('.stats-card').removeClass('active');
        $(this).addClass('active');
        activeFilters = [];
      } else {
        $('.stats-card[data-filter="all"]').removeClass('active');
        
        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeFilters = activeFilters.filter(f => f !== filter);
        } else {
          $(this).addClass('active');
          if (!activeFilters.includes(filter)) {
            activeFilters.push(filter);
          }
        }
      }
      
      updateActiveFiltersDisplay();
      applyFiltersToTable();
    });

    $('#clearFilters').on('click', function() {
      $('.stats-card').removeClass('active');
      activeFilters = [];
      updateActiveFiltersDisplay();
      applyFiltersToTable();
    });

    function updateActiveFiltersDisplay() {
      if (activeFilters.length > 0) {
        $('#activeFiltersBadge').show().text(activeFilters.length + ' Filter' + (activeFilters.length > 1 ? 's' : '') + ' Active');
      } else {
        $('#activeFiltersBadge').hide();
      }
    }

    function applyFiltersToTable() {
      if (typeof fichesTable !== 'undefined') {
        fichesTable.ajax.reload();
      }
    }

    // MCA Reference change handler
    $('#mca_ref_select').on('change', function() {
      const mcaRef = $(this).val();
      const selectedOption = $(this).find('option:selected');
      const trackingId = selectedOption.data('tracking-id');
      
      $('#mca_ref').val(mcaRef);
      $('#tracking_id').val(trackingId);
      
      if (!mcaRef) {
        $('#regime').val('');
        $('#regime_display').val('');
        $('#currency_select').val('');
        $('#currency').val('');
        $('#transport_mode').val('');
        $('#fiche_reference').val('');
        $('#poids').val('');
        $('#fob').val('');
        $('#fob_currency_select').val('');
        $('#fret').val('');
        $('#fret_currency_select').val('');
        $('#insurance_amount').val('');
        $('#insurance_amount_currency_select').val('');
        $('#autres_charges').val('0');
        $('#autres_charges_currency_select').val('');
        $('#coefficient').val('1.00');
        mcaInvoiceNumber = '';
        $('.item-facture').val('');
        isUSDCurrency = false;
        return;
      }

      const licenseNumber = $('#license_number_select').val();

      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/getMCADetails',
        method: 'GET',
        data: { 
          license_number: licenseNumber,
          mca_ref: mcaRef
        },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data) {
            const mca = res.data;
            
            $('#regime').val(mca.regime || '');
            $('#regime_display').val(escapeHtml(mca.regime || ''));
            
            $('#currency_select').val(mca.currency || 'USD');
            $('#currency').val(mca.currency || 'USD');
            isUSDCurrency = mca.is_usd || false;
            
            if (isUSDCurrency) {
              updateLabels('USD');
            } else {
              updateLabels(mca.currency || 'USD');
            }
            
            $('#transport_mode').val(escapeHtml(mca.transport_mode || ''));
            $('#fiche_reference').val(escapeHtml(mca.fiche_reference || ''));
            
            if (mca.weight) {
              $('#poids').val(parseFloat(mca.weight).toFixed(2));
            }
            
            if (mca.fob) {
              $('#fob').val(parseFloat(mca.fob).toFixed(2));
            }
            if (mca.fob_currency) {
              $('#fob_currency_select').val(mca.fob_currency);
              $('#fob_currency_value').val(mca.fob_currency);
            }
            
            if (mca.fret) {
              $('#fret').val(parseFloat(mca.fret).toFixed(2));
            }
            if (mca.fret_currency) {
              $('#fret_currency_select').val(mca.fret_currency);
              $('#fret_currency_value').val(mca.fret_currency);
            }
            
            if (mca.insurance_amount) {
              $('#insurance_amount').val(parseFloat(mca.insurance_amount).toFixed(2));
            }
            if (mca.insurance_amount_currency) {
              $('#insurance_amount_currency_select').val(mca.insurance_amount_currency);
              $('#insurance_amount_currency_value').val(mca.insurance_amount_currency);
            }
            
            if (mca.other_charges) {
              $('#autres_charges').val(parseFloat(mca.other_charges).toFixed(2));
            } else {
              $('#autres_charges').val('0');
            }
            if (mca.other_charges_currency) {
              $('#autres_charges_currency_select').val(mca.other_charges_currency);
              $('#autres_charges_currency_value').val(mca.other_charges_currency);
            }
            
            if (mca.coefficient) {
              const fullCoef = parseFloat(mca.coefficient);
              $('#coefficient').data('full-precision', fullCoef);
              $('#coefficient').val(fullCoef.toFixed(6));
            }
            
            mcaInvoiceNumber = mca.invoice || '';
            $('.item-facture').val(escapeHtml(mcaInvoiceNumber));
            
            calculateCIF();
          } else {
            Swal.fire('Error', res.message || 'Failed to load MCA details', 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load MCA details', 'error');
        }
      });
    });

    function clearMCAFields() {
      $('#regime').val('');
      $('#regime_display').val('');
      $('#currency_select').val('');
      $('#currency').val('');
      $('#transport_mode').val('');
      $('#fiche_reference').val('');
      $('#poids').val('');
      $('#fob').val('');
      $('#fob_currency_select').val('');
      $('#fret').val('');
      $('#fret_currency_select').val('');
      $('#insurance_amount').val('');
      $('#insurance_amount_currency_select').val('');
      $('#autres_charges').val('0');
      $('#autres_charges_currency_select').val('');
      $('#coefficient').val('1.00');
      mcaInvoiceNumber = '';
      $('.item-facture').val('');
      isUSDCurrency = false;
    }

    function exportToExcel(ficheId) {
      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/exportFiche',
        method: 'GET',
        data: { id: ficheId },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(res.data);
            
            const colWidths = res.data[0].map((header, idx) => {
              const maxLen = Math.max(
                header.toString().length,
                ...(res.data.slice(1).map(row => (row[idx] || '').toString().length))
              );
              return { wch: Math.min(maxLen + 2, 50) };
            });
            ws['!cols'] = colWidths;
            
            XLSX.utils.book_append_sheet(wb, ws, 'Fiche Details');
            XLSX.writeFile(wb, res.filename + '.xlsx');
            
            Swal.fire({
              icon: 'success',
              title: 'Exported!',
              text: 'Fiche data exported successfully',
              timer: 1500,
              showConfirmButton: false
            });
          } else {
            Swal.fire('Error', res.message || 'Failed to export', 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to export fiche data', 'error');
        }
      });
    }

    function validateForm() {
      clearValidationErrors();
      let errors = [];
      
      const requiredFields = [
        { id: 'license_number_select', label: 'License Number', hiddenId: 'license_number' },
        { id: 'mca_ref_select', label: 'MCA Reference', hiddenId: 'mca_ref' },
        { id: 'regime', label: 'Regime' },
        { id: 'fiche_date', label: 'Fiche Date' },
        { id: 'poids', label: 'Weight' },
        { id: 'fob', label: 'FOB' },
        { id: 'tx_de_change', label: 'Exchange Rate' },
        { id: 'currency_select', label: 'Currency', hiddenId: 'currency' }
      ];

      requiredFields.forEach(field => {
        const value = $(`#${field.id}`).val();
        if (!value || value === '') {
          showFieldError(field.id, `${field.label} is required`);
          errors.push(`${field.label} is required`);
        }
      });

      const poids = parseFloat($('#poids').val());
      const fob = parseFloat($('#fob').val());
      const txDeChange = parseFloat($('#tx_de_change').val());
      
      if (isNaN(poids) || poids < 0) {
        showFieldError('poids', 'Weight must be a positive number');
        errors.push('Invalid weight');
      }
      
      if (isNaN(fob) || fob < 0) {
        showFieldError('fob', 'FOB must be a positive number');
        errors.push('Invalid FOB');
      }

      if (isNaN(txDeChange) || txDeChange <= 0) {
        showFieldError('tx_de_change', 'Exchange Rate must be greater than 0');
        errors.push('Invalid Exchange Rate');
      }

      return { isValid: errors.length === 0, errors };
    }

    function clearValidationErrors() {
      $('.form-control, .form-select').removeClass('is-invalid');
      $('.invalid-feedback').text('').hide();
    }

    function showFieldError(fieldId, errorMessage) {
      $('#' + fieldId).addClass('is-invalid');
      $('#' + fieldId + '_error').text(errorMessage).show();
    }

    // Collect items data before form submission
    function collectItemsData() {
      const items = [];
      
      $('.item-row').each(function() {
        const item = {};
        $(this).find('input[data-field]').each(function() {
          const fieldName = $(this).data('field');
          let value = $(this).val();
          
          // Handle numeric fields
          if ($(this).attr('type') === 'number') {
            value = parseFloat(value) || 0;
          }
          
          item[fieldName] = value;
        });
        items.push(item);
      });
      
      return JSON.stringify(items);
    }

    $('#ficheForm').on('submit', function (e) {
      e.preventDefault();
      
      calculateCIF();
      
      const validation = validateForm();
      
      if (!validation.isValid) {
        $('#ficheDetailsSection').collapse('show');
        
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          html: '<ul style="text-align:left;"><li>' + validation.errors.map(err => escapeHtml(err)).join('</li><li>') + '</li></ul>'
        });
        
        const firstError = $('.is-invalid').first();
        if (firstError.length) {
          $('html, body').animate({
            scrollTop: firstError.offset().top - 100
          }, 300);
        }
        
        return false;
      }

      const submitBtn = $('#submitBtn');
      const originalText = submitBtn.html();
      submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');

      const formData = new FormData(this);
      formData.set('csrf_token', csrfToken);
      
      // Add items data
      formData.set('items', collectItemsData());

      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/' + $('#formAction').val(),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
          submitBtn.prop('disabled', false).html(originalText);
          
          if (res.success) {
            Swal.fire({ 
              icon: 'success', 
              title: 'Success!', 
              text: res.message, 
              timer: 1500, 
              showConfirmButton: false 
            });
            
            resetForm();
            $('#ficheDetailsSection').collapse('hide');
            
            if (typeof fichesTable !== 'undefined') {
              fichesTable.ajax.reload(null, false);
            }
            updateStatistics();
          } else {
            Swal.fire({ 
              icon: 'error', 
              title: 'Error!', 
              html: res.message 
            });
          }
        },
        error: function (xhr) {
          submitBtn.prop('disabled', false).html(originalText);
          
          let errorMsg = 'An error occurred while processing your request';
          
          if (xhr.status === 403) {
            errorMsg = 'Security token expired. Please refresh the page and try again.';
          }
          
          Swal.fire({ 
            icon: 'error', 
            title: 'Server Error', 
            html: errorMsg 
          });
        }
      });
    });

    function resetForm() {
      $('#ficheForm')[0].reset();
      clearValidationErrors();
      $('#fiche_id, #fiche_reference').val('');
      
      // Reset hidden fields
      $('#license_number, #tracking_id, #mca_ref, #regime, #currency').val('');
      $('#fob_currency_value, #fret_currency_value, #insurance_amount_currency_value, #autres_charges_currency_value').val('');
      $('#incoterm_short, #incoterm_full').val('');
      
      $('#formAction').val('insert');
      $('#formTitle').text('Add New Fiche');
      $('#submitBtnText').text('Save Fiche');
      $('#resetFormBtn').hide();
      clearMCAFields();
      
      $('#autres_charges').val('0');
      $('#coefficient').val('1.00');
      $('#cif').val('0.00');
      $('#incoterm_full_display').val('');
      
      mcaInvoiceNumber = '';
      
      isUSDCurrency = false;
      updateLabels('USD');
      
      // Reset items table
      $('#itemsTableBody').html(`
        <tr class="item-row" data-row="0">
          <td><input type="text" class="form-control form-control-sm item-description" data-field="description" placeholder="Description"></td>
          <td><input type="text" class="form-control form-control-sm item-bivac" data-field="no_bivac" placeholder="BIVAC"></td>
          <td><input type="text" class="form-control form-control-sm item-facture" data-field="no_facture" placeholder="From MCA" readonly style="background: #e9ecef;"></td>
          <td><input type="number" class="form-control form-control-sm item-number" data-field="numero" value="1" readonly style="background: #e9ecef;"></td>
          <td><input type="text" class="form-control form-control-sm item-position-tarif" data-field="position_tarrif" placeholder="Position"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi-percent" data-field="ddi_percent" placeholder="0"></td>
          <td><input type="text" class="form-control form-control-sm item-av" data-field="av" placeholder="AV"></td>
          <td><input type="text" class="form-control form-control-sm item-org" data-field="org" placeholder="ORG"></td>
          <td><input type="text" class="form-control form-control-sm item-prov" data-field="prov" placeholder="PROV"></td>
          <td><input type="text" class="form-control form-control-sm item-code-add" data-field="code_add" placeholder="Code Add"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-colis" data-field="colis" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-qte" data-field="qte" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-net" data-field="net" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-brut" data-field="brut" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-fob" data-field="fob_article" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-coef" data-field="coef" value="1.00" placeholder="1.00" readonly style="background: #fff3cd;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-cif item-cif-wide" data-field="cif_article" placeholder="0.00" readonly style="background: #d4edda; font-weight: 600;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi item-ddi-wide" data-field="ddi" placeholder="0.00" readonly style="background: #fff3cd; font-weight: 600;"></td>
          <td>
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
              <i class="ti ti-trash"></i>
            </button>
          </td>
        </tr>
      `);
      
      $('.item-row[data-row="0"]').find('.item-coef').data('full-precision', 1.00);
      
      itemRowCounter = 0;
      updateItemTotals();
      setTodayDate();
      $('#ficheDetailsSection').collapse('hide');
    }

    $('#cancelBtn, #resetFormBtn').on('click', (e) => { 
      e.preventDefault(); 
      resetForm(); 
    });

    var fichesTable;
    function initDataTable() {
      if ($.fn.DataTable.isDataTable('#fichesTable')) {
        $('#fichesTable').DataTable().destroy();
      }

      fichesTable = $('#fichesTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: { 
          url: '<?= APP_URL ?>/fiche/crudData/listing', 
          type: 'GET',
          data: function(d) {
            d.filters = activeFilters;
          },
          error: function(xhr, error, code) {
            console.error('DataTable error:', error, code);
          }
        },
        columns: [
          { data: 'id' },
          { data: 'fiche_reference', render: function(data) { return escapeHtml(data); } },
          { data: 'subscriber', render: function(data) { return escapeHtml(data); } },
          { data: 'mca_ref', render: function(data) { return escapeHtml(data); } },
          { 
            data: 'fiche_date',
            render: function(data) {
              return data ? escapeHtml(formatDate(data)) : '';
            }
          },
          { 
            data: 'poids', 
            render: (data) => data ? parseFloat(data).toFixed(2) : '0.00' 
          },
          { 
            data: 'cif', 
            render: (data) => data ? parseFloat(data).toFixed(2) : '0.00' 
          },
          { 
            data: 'status', 
            render: function(data) { 
              const status = escapeHtml(data);
              let badgeClass = 'secondary';
              if (status === 'created') badgeClass = 'secondary';
              else if (status === 'verified') badgeClass = 'warning';
              else if (status === 'audited') badgeClass = 'success';
              
              return `<span class="badge bg-${badgeClass}">${status.toUpperCase()}</span>`;
            } 
          },
          {
            data: null, 
            orderable: false, 
            searchable: false,
            render: (data, type, row) => {
              let buttons = `
                <button class="btn btn-sm btn-view viewBtn" data-id="${parseInt(row.id)}" title="View">
                  <i class="ti ti-eye"></i>
                </button>
              `;
              
              // Only allow edit if status is created or verified
              if (row.status === 'created' || row.status === 'verified') {
                buttons += `
                  <button class="btn btn-sm btn-primary editBtn" data-id="${parseInt(row.id)}" title="Edit">
                    <i class="ti ti-edit"></i>
                  </button>
                `;
              }
              
              // Verify button (created -> verified)
              if (row.status === 'created') {
                buttons += `
                  <button class="btn btn-sm btn-warning verifyBtn" data-id="${parseInt(row.id)}" title="Verify">
                    <i class="ti ti-circle-check"></i>
                  </button>
                `;
              }
              
              // Audit button (verified -> audited)
              if (row.status === 'verified') {
                buttons += `
                  <button class="btn btn-sm btn-success auditBtn" data-id="${parseInt(row.id)}" title="Audit">
                    <i class="ti ti-file-check"></i>
                  </button>
                `;
              }
              
              buttons += `
                <button class="btn btn-sm btn-export exportBtn" data-id="${parseInt(row.id)}" title="Export to Excel">
                  <i class="ti ti-file-spreadsheet"></i>
                </button>
                <button class="btn btn-sm btn-danger deleteBtn" data-id="${parseInt(row.id)}" title="Delete">
                  <i class="ti ti-trash"></i>
                </button>
              `;
              
              return buttons;
            }
          }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: false,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>Brt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
          {
            extend: 'excel',
            text: '<i class="ti ti-file-spreadsheet me-1"></i> Export All to Excel',
            className: 'buttons-excel',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7]
            }
          }
        ]
      });
    }

    function updateStatistics() {
      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/statistics',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#totalFiches').text(res.data.total_fiches || 0);
            $('#totalCreated').text(res.data.created || 0);
            $('#totalVerified').text(res.data.verified || 0);
            $('#totalAudited').text(res.data.audited || 0);
          }
        },
        error: function() {
          console.error('Failed to load statistics');
        }
      });
    }

    function formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    $(document).on('click', '.exportBtn', function () {
      const id = parseInt($(this).data('id'));
      exportToExcel(id);
    });

    $(document).on('click', '.verifyBtn', function() {
      const id = parseInt($(this).data('id'));
      
      Swal.fire({
        title: 'Verify Fiche?',
        text: "This will change the status from Created to Verified.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, verify it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '<?= APP_URL ?>/fiche/crudData/verifyFiche',
            method: 'POST',
            data: { fiche_id: id, csrf_token: csrfToken },
            dataType: 'json',
            success: function(res) {
              if (res.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Verified!',
                  text: res.message,
                  timer: 1500,
                  showConfirmButton: false
                });
                fichesTable.ajax.reload(null, false);
                updateStatistics();
              } else {
                Swal.fire('Error', res.message || 'Verification failed', 'error');
              }
            },
            error: function() {
              Swal.fire('Error', 'Failed to verify fiche', 'error');
            }
          });
        }
      });
    });

    $(document).on('click', '.auditBtn', function() {
      const id = parseInt($(this).data('id'));
      
      Swal.fire({
        title: 'Audit Fiche?',
        text: "This will change the status from Verified to Audited.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, audit it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '<?= APP_URL ?>/fiche/crudData/auditFiche',
            method: 'POST',
            data: { fiche_id: id, csrf_token: csrfToken },
            dataType: 'json',
            success: function(res) {
              if (res.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Audited!',
                  text: res.message,
                  timer: 1500,
                  showConfirmButton: false
                });
                fichesTable.ajax.reload(null, false);
                updateStatistics();
              } else {
                Swal.fire('Error', res.message || 'Audit failed', 'error');
              }
            },
            error: function() {
              Swal.fire('Error', 'Failed to audit fiche', 'error');
            }
          });
        }
      });
    });

    $(document).on('click', '.editBtn', function () {
      const id = parseInt($(this).data('id'));
      
      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/getFiche',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function (res) {
          if (res.success && res.data) {
            const data = res.data;
            
            clearValidationErrors();
            
            $('#fiche_id').val(data.id);
            $('#formAction').val('update');
            $('#formTitle').text('Edit Fiche');
            $('#submitBtnText').text('Update Fiche');
            $('#resetFormBtn').show();
            
            // Set license
            $('#license_number_select').val(data.license_number).trigger('change');
            $('#license_number').val(data.license_number);
            
            // Wait for MCA refs to load, then set MCA ref
            setTimeout(() => {
              $('#mca_ref_select').val(data.mca_ref).trigger('change');
              $('#mca_ref').val(data.mca_ref);
              $('#tracking_id').val(data.tracking_id);
              
              // Wait for MCA details to populate, then override with saved data
              setTimeout(() => {
                // Set all form fields from saved data
                $('#regime').val(data.regime);
                $('#regime_display').val(data.regime);
                $('#fiche_reference').val(data.fiche_reference);
                $('#fiche_date').val(data.fiche_date);
                
                $('#currency_select').val(data.currency);
                $('#currency').val(data.currency);
                $('#transport_mode').val(data.transport_mode);
                $('#poids').val(data.poids);
                
                $('#fob').val(data.fob);
                $('#fob_currency_select').val(data.fob_currency);
                $('#fob_currency_value').val(data.fob_currency);
                
                $('#insurance_amount').val(data.insurance_amount);
                $('#insurance_amount_currency_select').val(data.insurance_amount_currency);
                $('#insurance_amount_currency_value').val(data.insurance_amount_currency);
                
                $('#tx_de_change').val(data.tx_de_change);
                
                $('#fret').val(data.fret);
                $('#fret_currency_select').val(data.fret_currency);
                $('#fret_currency_value').val(data.fret_currency);
                
                $('#autres_charges').val(data.autres_charges);
                $('#autres_charges_currency_select').val(data.autres_charges_currency);
                $('#autres_charges_currency_value').val(data.autres_charges_currency);
                
                $('#usd_to_currency_rate').val(data.usd_to_currency_rate);
                $('#provence').val(data.provence);
                $('#cif').val(data.cif);
                $('#coefficient').val(data.coefficient);
                $('#coefficient').data('full-precision', parseFloat(data.coefficient));
                
                $('#incoterm_short_select').val(data.incoterm_short);
                $('#incoterm_short').val(data.incoterm_short);
                $('#incoterm_full').val(data.incoterm_full);
                $('#incoterm_full_display').val(data.incoterm_full);
                
                isUSDCurrency = data.is_usd || false;
                updateLabels(data.currency || 'USD');
                
                // Load items
                $.ajax({
                  url: '<?= APP_URL ?>/fiche/crudData/getFicheItems',
                  method: 'GET',
                  data: { fiche_id: id },
                  dataType: 'json',
                  success: function(itemsRes) {
                    if (itemsRes.success && itemsRes.data.length > 0) {
                      $('#itemsTableBody').empty();
                      itemRowCounter = -1;
                      
                      itemsRes.data.forEach(function(item) {
                        itemRowCounter++;
                        const row = `
                          <tr class="item-row" data-row="${itemRowCounter}">
                            <td><input type="text" class="form-control form-control-sm item-description" data-field="description" value="${escapeHtml(item.description || '')}" placeholder="Description"></td>
                            <td><input type="text" class="form-control form-control-sm item-bivac" data-field="no_bivac" value="${escapeHtml(item.no_bivac || '')}" placeholder="BIVAC"></td>
                            <td><input type="text" class="form-control form-control-sm item-facture" data-field="no_facture" value="${escapeHtml(item.no_facture || '')}" placeholder="From MCA" readonly style="background: #e9ecef;"></td>
                            <td><input type="number" class="form-control form-control-sm item-number" data-field="numero" value="${item.numero || 1}" readonly style="background: #e9ecef;"></td>
                            <td><input type="text" class="form-control form-control-sm item-position-tarif" data-field="position_tarrif" value="${escapeHtml(item.position_tarrif || '')}" placeholder="Position"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi-percent" data-field="ddi_percent" value="${item.ddi_percent || 0}" placeholder="0"></td>
                            <td><input type="text" class="form-control form-control-sm item-av" data-field="av" value="${escapeHtml(item.av || '')}" placeholder="AV"></td>
                            <td><input type="text" class="form-control form-control-sm item-org" data-field="org" value="${escapeHtml(item.org || '')}" placeholder="ORG"></td>
                            <td><input type="text" class="form-control form-control-sm item-prov" data-field="prov" value="${escapeHtml(item.prov || '')}" placeholder="PROV"></td>
                            <td><input type="text" class="form-control form-control-sm item-code-add" data-field="code_add" value="${escapeHtml(item.code_add || '')}" placeholder="Code Add"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-colis" data-field="colis" value="${item.colis || 0}" placeholder="0.00"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-qte" data-field="qte" value="${item.qte || 0}" placeholder="0.00"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-net" data-field="net" value="${item.net || 0}" placeholder="0.00"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-brut" data-field="brut" value="${item.brut || 0}" placeholder="0.00"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-fob" data-field="fob_article" value="${item.fob_article || 0}" placeholder="0.00"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-coef" data-field="coef" value="${item.coef || 1.00}" placeholder="1.00" readonly style="background: #fff3cd;"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-cif item-cif-wide" data-field="cif_article" value="${item.cif_article || 0}" placeholder="0.00" readonly style="background: #d4edda; font-weight: 600;"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm item-ddi item-ddi-wide" data-field="ddi" value="${item.ddi || 0}" placeholder="0.00" readonly style="background: #fff3cd; font-weight: 600;"></td>
                            <td>
                              <button type="button" class="btn btn-sm btn-danger remove-item-btn" title="Remove Item">
                                <i class="ti ti-trash"></i>
                              </button>
                            </td>
                          </tr>
                        `;
                        $('#itemsTableBody').append(row);
                        
                        $('.item-row[data-row="' + itemRowCounter + '"]').find('.item-coef').data('full-precision', parseFloat(item.coef || 1.00));
                      });
                      
                      updateItemTotals();
                    }
                  }
                });
                
                $('#ficheDetailsSection').collapse('show');
                $('html, body').animate({ 
                  scrollTop: $('#ficheForm').offset().top - 100 
                }, 500);
              }, 800);
            }, 600);
          } else {
            Swal.fire('Error', res.message || 'Failed to load fiche data', 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'Failed to load fiche data', 'error');
        }
      });
    });

    $(document).on('click', '.deleteBtn', function () {
      const id = parseInt($(this).data('id'));
      
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: '<?= APP_URL ?>/fiche/crudData/deletion',
            method: 'POST',
            data: { id: id, csrf_token: csrfToken },
            dataType: 'json',
            success: function (res) {
              if (res.success) {
                Swal.fire({ 
                  icon: 'success', 
                  title: 'Deleted!', 
                  text: res.message,
                  timer: 1500, 
                  showConfirmButton: false 
                });
                fichesTable.ajax.reload(null, false);
                updateStatistics();
              } else {
                Swal.fire('Error', res.message || 'Delete failed', 'error');
              }
            },
            error: function (xhr) {
              let errorMsg = 'Failed to delete fiche';
              
              if (xhr.status === 403) {
                errorMsg = 'Security token expired. Please refresh the page and try again.';
              }
              
              Swal.fire('Error', errorMsg, 'error');
            }
          });
        }
      });
    });

    $(document).on('click', '.viewBtn', function() {
      const id = parseInt($(this).data('id'));
      
      $.ajax({
        url: '<?= APP_URL ?>/fiche/crudData/getPrintContent',
        method: 'GET',
        data: { fiche_id: id },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#modalDetailsContent').html(res.content);
            $('#viewFicheModal').modal('show');
          } else {
            Swal.fire('Error', res.message || 'Failed to load fiche details', 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load fiche details', 'error');
        }
      });
    });

    setTodayDate();
    initializeSelect2();
    initDataTable();
    updateStatistics();
    calculateCIF();
    
    $('#coefficient').data('full-precision', 1.00);
    $('.item-coef').each(function() {
      $(this).data('full-precision', 1.00);
    });
  });
</script>