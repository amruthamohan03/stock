<link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">

<style>
  .dataTables_wrapper .dataTables_info {
    float: left;
  }
  .dataTables_wrapper .dataTables_paginate {
    float: right;
    text-align: right;
  }
  
  /* Export Button Styling - Green */
  .btn-export-all {
    background: #28a745 !important;
    color: white !important;
    border: none !important;
    padding: 8px 20px !important;
    border-radius: 5px !important;
    font-weight: 500 !important;
    transition: all 0.3s !important;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3) !important;
  }
  
  .btn-export-all:hover {
    background: #218838 !important;
    color: white !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4) !important;
  }
  
  /* Export Individual Button */
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

  /* View Button */
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
  
  /* Required field indicator */
  .text-danger {
    color: #dc3545;
    font-weight: bold;
  }
  
  /* Validation Error Styling */
  .is-invalid {
    border-color: #dc3545 !important;
  }
  
  .invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  
  /* Stats Cards */
  .stats-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden;
    cursor: pointer;
  }
  
  .stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  }
  
  .stats-card-1 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .stats-card-2 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
  }
  
  .stats-card-client, .stats-card-bank {
    color: white;
  }
  
  .stats-card.active {
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
    transform: translateY(-5px) scale(1.02);
  }
  
  .stats-card .card-body {
    padding: 25px;
  }
  
  .stats-icon {
    font-size: 3rem;
    opacity: 0.3;
    position: absolute;
    right: 20px;
    top: 20px;
  }
  
  .stats-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
  }
  
  .stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .stats-subtitle {
    font-size: 0.85rem;
    opacity: 0.8;
    margin-top: 5px;
  }
  
  /* Modal Styling */
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

  /* Badge styles */
  .badge {
    padding: 6px 12px;
    font-size: 0.85rem;
  }
  
  /* Filter Section */
  .filter-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
  }

  /* SIMPLE & CLEAN DESIGN - Client-wise Summary Table */
  #ceecDetailsTable thead th {
    background: #6c757d !important;
    color: white !important;
    text-align: center !important;
    padding: 8px 6px !important;
    font-weight: 600 !important;
    font-size: 0.75rem !important;
    border: 1px solid #5a6268 !important;
  }

  #ceecDetailsTable tbody td {
    vertical-align: middle !important;
    text-align: center !important;
    padding: 8px 6px !important;
    font-size: 0.8rem !important;
  }

  /* Client Code - Simple Badge */
  .summary-client-code {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.75rem;
  }

  /* Client Name - Simple Text */
  .summary-client-name {
    color: #495057;
    font-weight: 500;
    font-size: 0.8rem;
  }

  /* Record Count - Simple Badge */
  .summary-record-count {
    display: inline-block;
    background: #17a2b8;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.7rem;
  }

  /* Amount Display - Simple & Clean */
  .summary-amount {
    font-weight: 600;
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
    min-width: 90px;
  }

  .summary-amount.total {
    color: #0056b3;
    background: #e7f1ff;
  }

  .summary-amount.used {
    color: #dc3545;
    background: #ffe5e5;
  }

  .summary-amount.balance {
    color: #28a745;
    background: #e7f9e7;
  }

  /* Progress Bar - Better Design with Proper Alignment */
  .summary-progress {
    width: 100%;
    margin-top: 5px;
  }

  .summary-progress-bar {
    background: #e9ecef;
    border-radius: 12px;
    height: 20px;
    overflow: hidden;
    position: relative;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
  }

  .summary-progress-fill {
    height: 100%;
    border-radius: 12px;
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    min-width: 35px;
  }

  .summary-progress-fill.high {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
  }

  .summary-progress-fill.medium {
    background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
  }

  .summary-progress-fill.low {
    background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
  }

  .summary-progress-text {
    color: white;
    font-weight: 700;
    font-size: 0.7rem;
    line-height: 20px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.4);
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    white-space: nowrap;
    z-index: 10;
  }

  /* For very low percentages, show text outside */
  .summary-progress-fill[style*="width: 0%"] .summary-progress-text,
  .summary-progress-fill[style*="width: 1%"] .summary-progress-text,
  .summary-progress-fill[style*="width: 2%"] .summary-progress-text,
  .summary-progress-fill[style*="width: 3%"] .summary-progress-text,
  .summary-progress-fill[style*="width: 4%"] .summary-progress-text,
  .summary-progress-fill[style*="width: 5%"] .summary-progress-text {
    color: #6c757d;
    text-shadow: none;
  }

  /* Action Buttons - Smaller */
  #ceecDetailsTable .btn-sm {
    padding: 3px 8px;
    font-size: 0.75rem;
  }

  /* Client Details Modal - CEEC Records Table */
  #clientCEECRecordsTable {
    font-size: 0.8rem;
  }

  #clientCEECRecordsTable thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px;
    font-weight: 600;
    font-size: 0.8rem;
  }

  #clientCEECRecordsTable tbody td {
    padding: 8px;
    vertical-align: middle;
    font-size: 0.8rem;
  }

  .client-summary-box {
    background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
    border: 2px solid #667eea;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
  }

  .client-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
  }

  .client-summary-item:last-child {
    border-bottom: none;
    font-weight: 700;
    font-size: 1rem;
    padding-top: 12px;
    border-top: 2px solid #667eea;
  }

  .client-summary-label {
    font-weight: 600;
    color: #495057;
    font-size: 0.85rem;
  }

  .client-summary-value {
    font-weight: 700;
    color: #667eea;
    font-size: 0.9rem;
  }

  .client-summary-value.total {
    color: #0056b3;
  }

  .client-summary-value.used {
    color: #dc3545;
  }

  .client-summary-value.balance {
    color: #28a745;
  }

  /* DataTable Customization */
  #ceecDetailsTable_wrapper .dataTables_length,
  #ceecDetailsTable_wrapper .dataTables_filter {
    font-size: 0.85rem;
  }

  #ceecDetailsTable_wrapper .dataTables_info,
  #ceecDetailsTable_wrapper .dataTables_paginate {
    font-size: 0.8rem;
  }

  /* Hide fields during edit */
  .hide-on-edit {
    display: none;
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- Statistics Cards (3 cards only) -->
        <div class="row mb-4" id="statsCardsContainer">
          <!-- Total Records Card -->
          <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stats-card stats-card-1 shadow-sm active" data-filter-type="all">
              <div class="card-body position-relative">
                <i class="ti ti-file-text stats-icon"></i>
                <div class="stats-value" id="totalRecords">0</div>
                <div class="stats-label">Total Records</div>
                <div class="stats-subtitle">All CEEC entries</div>
              </div>
            </div>
          </div>
          
          <!-- This Month Records Card -->
          <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stats-card stats-card-2 shadow-sm">
              <div class="card-body position-relative">
                <i class="ti ti-calendar-month stats-icon"></i>
                <div class="stats-value" id="thisMonthRecords">0</div>
                <div class="stats-label">This Month</div>
                <div class="stats-subtitle">Current month entries</div>
              </div>
            </div>
          </div>
          
          <!-- Top Client Card -->
          <div class="col-xl-4 col-md-6 mb-3">
            <div class="card stats-card stats-card-client shadow-sm" data-filter-type="client" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
              <div class="card-body position-relative">
                <i class="ti ti-users stats-icon"></i>
                <div class="stats-value" id="topClientValue">-</div>
                <div class="stats-label" id="topClientName">Top Client</div>
                <div class="stats-subtitle" id="topClientAmount">$0</div>
              </div>
            </div>
          </div>
        </div>

        <!-- CEEC Form Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0">
              <i class="ti ti-file-text me-2"></i> 
              <span id="formTitle">Add New CEEC</span>
            </h4>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn" style="display:none;">
                <i class="ti ti-plus"></i> Add New
              </button>
            </div>
          </div>

          <div class="card-body">
            <form id="ceecForm" method="post" novalidate>
              <!-- CSRF Token -->
              <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $csrf_token ?>">
              <input type="hidden" name="ceec_id" id="ceec_id" value="">
              <input type="hidden" name="action" id="formAction" value="insert">

              <div class="accordion" id="ceecAccordion">
                
                <!-- CEEC INFORMATION -->
                <div class="accordion-item mb-3">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ceecInfo">
                      <i class="ti ti-file-text me-2"></i> CEEC Information
                    </button>
                  </h2>

                  <div id="ceecInfo" class="accordion-collapse collapse" data-bs-parent="#ceecAccordion">
                    <div class="accordion-body">
                      
                      <div class="row">
                        <div class="col-md-3 mb-3">
                          <label>Prepayment Date <span class="text-danger">*</span></label>
                          <input type="date" name="prepayment_date" id="prepayment_date" class="form-control" required>
                          <div class="invalid-feedback" id="prepayment_date_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Client <span class="text-danger">*</span></label>
                          <select name="client_id" id="client_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            <?php foreach ($clients as $client): ?>
                              <option value="<?= $client['id'] ?>">
                                <?= htmlspecialchars($client['short_name']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="client_id_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Bank <span class="text-danger">*</span></label>
                          <select name="bank_id" id="bank_id" class="form-select" required>
                            <option value="">-- Select Bank --</option>
                            <?php foreach ($banks as $bank): ?>
                              <option value="<?= $bank['id'] ?>">
                                <?= htmlspecialchars($bank['bank_name']) ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="bank_id_error"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                          <label>Display <span class="text-danger">*</span></label>
                          <select name="display" id="display" class="form-select" required>
                            <option value="Y" selected>Yes</option>
                            <option value="N">No</option>
                          </select>
                        </div>
                      </div>

                      <!-- Amount Fields Row -->
                      <div class="row">
                        <div class="col-md-12 mb-3">
                          <label>Total Amount ($) <span class="text-danger">*</span></label>
                          <input type="number" step="0.01" name="amount" id="amount" class="form-control" required min="0" max="9999999999999.99" placeholder="Enter total amount">
                          <div class="invalid-feedback" id="amount_error"></div>
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
                    <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save CEEC</span>
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <div class="filter-section">
              <div class="row">
                <div class="col-md-3 mb-2">
                  <label class="form-label">Filter by Client</label>
                  <select id="clientFilter" class="form-select">
                    <option value="0">All Clients</option>
                    <?php foreach ($clients as $client): ?>
                      <option value="<?= $client['id'] ?>">
                        <?= htmlspecialchars($client['short_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="col-md-3 mb-2">
                  <label class="form-label">Filter by Bank</label>
                  <select id="bankFilter" class="form-select">
                    <option value="0">All Banks</option>
                    <?php foreach ($banks as $bank): ?>
                      <option value="<?= $bank['id'] ?>">
                        <?= htmlspecialchars($bank['bank_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                
                <div class="col-md-2 mb-2">
                  <label class="form-label">Date From</label>
                  <input type="date" id="dateFrom" class="form-control">
                </div>
                
                <div class="col-md-2 mb-2">
                  <label class="form-label">Date To</label>
                  <input type="date" id="dateTo" class="form-control">
                </div>
                
                <div class="col-md-2 mb-2 d-flex align-items-end">
                  <button type="button" class="btn btn-secondary w-100" id="resetFiltersBtn">
                    <i class="ti ti-x me-1"></i> Reset Filters
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- FIRST DataTable - Basic CEEC Records (WITH EXPORT ALL BUTTON) -->
        <div class="card shadow-sm mb-4">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-list me-2"></i> CEEC Records List</h4>
            <button type="button" class="btn btn-export-all" id="exportCEECRecordsListBtn">
              <i class="ti ti-file-spreadsheet me-1"></i> Export All
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="ceecTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Bank</th>
                    <th>Prepayment Date</th>
                    <th>Amount</th>
                    <th>Display</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- SECOND DataTable - Client-wise Amount Breakdown Summary (WITH VIEW & EXPORT) -->
        <div class="card shadow-sm">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0">
              <i class="ti ti-wallet me-2"></i> Client-wise CEEC Amount Breakdown & Balance Summary
            </h4>
            <button type="button" class="btn btn-export-all" id="exportClientSummaryBtn">
              <i class="ti ti-file-spreadsheet me-1"></i> Export Summary
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="ceecDetailsTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>Client Code</th>
                    <th>Client Name</th>
                    <th>Records</th>
                    <th>Total Amount</th>
                    <th>Used Amount</th>
                    <th>Balance Amount</th>
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

<!-- View Client CEEC Details Modal -->
<div class="modal fade" id="viewClientCEECModal" tabindex="-1" aria-labelledby="viewClientCEECModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewClientCEECModalLabel">
          <i class="ti ti-eye me-2"></i> Client CEEC Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Client Summary Box -->
        <div class="client-summary-box" id="clientSummaryBox">
          <h5 class="mb-3"><i class="ti ti-user me-2"></i> <span id="modalClientName"></span></h5>
          <div class="client-summary-item">
            <span class="client-summary-label">Total Records:</span>
            <span class="client-summary-value" id="modalTotalRecords">0</span>
          </div>
          <div class="client-summary-item">
            <span class="client-summary-label">Total Amount:</span>
            <span class="client-summary-value total" id="modalTotalAmount">$0.00</span>
          </div>
          <div class="client-summary-item">
            <span class="client-summary-label">Used Amount:</span>
            <span class="client-summary-value used" id="modalUsedAmount">$0.00</span>
          </div>
          <div class="client-summary-item">
            <span class="client-summary-label">Balance Amount:</span>
            <span class="client-summary-value balance" id="modalBalanceAmount">$0.00</span>
          </div>
        </div>

        <!-- CEEC Records Table -->
        <h6 class="mb-3"><i class="ti ti-list me-2"></i> All CEEC Records</h6>
        <div class="table-responsive">
          <table id="clientCEECRecordsTable" class="table table-striped table-bordered w-100">
            <thead>
              <tr>
                <th>ID</th>
                <th>Bank</th>
                <th>Bank Code</th>
                <th>Prepayment Date</th>
                <th>Amount</th>
                <th>Used Amount</th>
                <th>Balance Amount</th>
                <th>Display</th>
              </tr>
            </thead>
            <tbody id="clientCEECRecordsBody">
              <!-- Records will be loaded here -->
            </tbody>
          </table>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function () {

    // CSRF Token
    const csrfToken = $('#csrf_token').val();
    
    // Current filters
    let currentClientFilter = 0;
    let currentBankFilter = 0;
    let currentDateFrom = '';
    let currentDateTo = '';

    // Store current used_amount for editing
    let currentUsedAmount = 0;
    let isEditMode = false;

    // ===== CLIENT-SIDE VALIDATION FUNCTIONS =====
    
    function clearValidationErrors() {
      $('.form-control, .form-select').removeClass('is-invalid');
      $('.invalid-feedback').text('').hide();
    }

    function showFieldError(fieldId, errorMessage) {
      $('#' + fieldId).addClass('is-invalid');
      $('#' + fieldId + '_error').text(errorMessage).show();
    }

    function validateForm() {
      clearValidationErrors();
      
      let errors = [];
      let hasError = false;

      const clientId = $('#client_id').val();
      const bankId = $('#bank_id').val();
      const prepaymentDate = $('#prepayment_date').val();
      const amount = parseFloat($('#amount').val()) || 0;

      if (!clientId || clientId === '') {
        showFieldError('client_id', 'Please select Client (Required)');
        errors.push('Client is required');
        hasError = true;
      }

      if (!bankId || bankId === '') {
        showFieldError('bank_id', 'Please select Bank (Required)');
        errors.push('Bank is required');
        hasError = true;
      }

      if (!prepaymentDate || prepaymentDate === '') {
        showFieldError('prepayment_date', 'Please select Prepayment Date (Required)');
        errors.push('Prepayment Date is required');
        hasError = true;
      }

      if (amount <= 0) {
        showFieldError('amount', 'Amount must be greater than 0 (Required)');
        errors.push('Amount must be greater than 0');
        hasError = true;
      }

      if (amount > 9999999999999.99) {
        showFieldError('amount', 'Amount cannot exceed $9,999,999,999,999.99');
        errors.push('Amount cannot exceed $9,999,999,999,999.99');
        hasError = true;
      }

      // Check if new amount is less than used amount (for edit)
      if (isEditMode && currentUsedAmount > 0 && amount < currentUsedAmount) {
        showFieldError('amount', 'Total Amount cannot be less than Used Amount (' + formatLargeAmount(currentUsedAmount) + ')');
        errors.push('Total Amount cannot be less than Used Amount');
        hasError = true;
      }

      return {
        isValid: !hasError,
        errors: errors
      };
    }

    // ===== REAL-TIME VALIDATION =====
    
    $('#client_id').on('change', function() {
      const value = $(this).val();
      if (!value || value === '') {
        $(this).addClass('is-invalid');
        $('#client_id_error').text('Please select Client (Required)').show();
      } else {
        $(this).removeClass('is-invalid');
        $('#client_id_error').text('').hide();
      }
    });

    $('#bank_id').on('change', function() {
      const value = $(this).val();
      if (!value || value === '') {
        $(this).addClass('is-invalid');
        $('#bank_id_error').text('Please select Bank (Required)').show();
      } else {
        $(this).removeClass('is-invalid');
        $('#bank_id_error').text('').hide();
      }
    });

    $('#prepayment_date').on('change', function() {
      const value = $(this).val();
      if (!value || value === '') {
        $(this).addClass('is-invalid');
        $('#prepayment_date_error').text('Please select Prepayment Date (Required)').show();
      } else {
        $(this).removeClass('is-invalid');
        $('#prepayment_date_error').text('').hide();
      }
    });

    $('#amount').on('input', function() {
      const value = parseFloat($(this).val()) || 0;
      
      if (value <= 0) {
        $(this).addClass('is-invalid');
        $('#amount_error').text('Amount must be greater than 0 (Required)').show();
      } else if (value > 9999999999999.99) {
        $(this).addClass('is-invalid');
        $('#amount_error').text('Amount cannot exceed $9,999,999,999,999.99').show();
      } else if (isEditMode && currentUsedAmount > 0 && value < currentUsedAmount) {
        $(this).addClass('is-invalid');
        $('#amount_error').text('Total Amount cannot be less than Used Amount (' + formatLargeAmount(currentUsedAmount) + ')').show();
      } else {
        $(this).removeClass('is-invalid');
        $('#amount_error').text('').hide();
      }
    });

    // Helper function to format large numbers properly
    function formatLargeAmount(amount) {
      return '$' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    // Date formatting helper function
    function formatDateToDDMMYYYY(dateStr) {
      if (!dateStr) return '';
      
      const date = new Date(dateStr);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      
      return `${day}-${month}-${year}`;
    }

    // Get balance percentage and status
    function getBalanceStatus(total, balance) {
      if (total <= 0) return { percent: 0, status: 'low', color: '#dc3545' };
      
      const percent = (balance / total) * 100;
      
      if (percent >= 70) {
        return { percent: percent, status: 'high', color: '#28a745' };
      } else if (percent >= 30) {
        return { percent: percent, status: 'medium', color: '#ffc107' };
      } else {
        return { percent: percent, status: 'low', color: '#dc3545' };
      }
    }

    // Initialize FIRST DataTable - Basic Records (NO VIEW BUTTON)
    var ceecTable;
    function initBasicDataTable() {
      if ($.fn.DataTable.isDataTable('#ceecTable')) {
        $('#ceecTable').DataTable().destroy();
      }

      ceecTable = $('#ceecTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '<?= APP_URL ?>/ceec/crudData/listing',
          type: 'GET',
          data: function(d) {
            d.client_filter = currentClientFilter;
            d.bank_filter = currentBankFilter;
            d.date_from = currentDateFrom;
            d.date_to = currentDateTo;
          },
          error: function(xhr, error, code) {
            console.error('DataTable error:', error, code, xhr.responseText);
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Failed to load data. Please try again.',
              confirmButtonText: 'OK'
            });
          }
        },
        columns: [
          { data: 'id' },
          { 
            data: 'client_short_name',
            render: function(data, type, row) {
              return `<span class="badge bg-primary">${data}</span>`;
            }
          },
          { data: 'bank_name' },
          { 
            data: 'prepayment_date',
            render: function(data, type, row) {
              return formatDateToDDMMYYYY(data);
            }
          },
          { 
            data: 'amount',
            render: function(data, type, row) {
              return formatLargeAmount(data);
            }
          },
          { 
            data: 'display',
            render: function(data, type, row) {
              if (data === 'Y') {
                return '<span class="badge bg-success">Yes</span>';
              } else {
                return '<span class="badge bg-danger">No</span>';
              }
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              return `
                <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}" title="Edit">
                  <i class="ti ti-edit"></i>
                </button>
                <button class="btn btn-sm btn-export exportBtn" data-id="${row.id}" title="Export to Excel">
                  <i class="ti ti-file-spreadsheet"></i>
                </button>
                <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.id}" title="Delete">
                  <i class="ti ti-trash"></i>
                </button>
              `;
            }
          }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
          processing: '<i class="spinner-border spinner-border-sm"></i> Loading...',
          emptyTable: 'No CEEC records found',
          zeroRecords: 'No matching records found'
        },
        drawCallback: function() {
          updateStatistics();
        }
      });
    }

    // Initialize SECOND DataTable - Client-wise Summary (WITH VIEW & EXPORT BUTTONS)
    var ceecDetailsTable;
    function initDetailsDataTable() {
      if ($.fn.DataTable.isDataTable('#ceecDetailsTable')) {
        $('#ceecDetailsTable').DataTable().destroy();
      }

      ceecDetailsTable = $('#ceecDetailsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '<?= APP_URL ?>/ceec/crudData/clientSummary',
          type: 'GET',
          data: function(d) {
            d.client_filter = currentClientFilter;
          },
          error: function(xhr, error, code) {
            console.error('Details DataTable error:', error, code, xhr.responseText);
          }
        },
        columns: [
          { 
            data: 'client_short_name',
            render: function(data, type, row) {
              return `<span class="summary-client-code">${data}</span>`;
            }
          },
          { 
            data: 'client_company_name',
            render: function(data, type, row) {
              return `<span class="summary-client-name">${data}</span>`;
            }
          },
          { 
            data: 'record_count',
            render: function(data, type, row) {
              return `<span class="summary-record-count">${data} Records</span>`;
            }
          },
          { 
            data: 'total_amount',
            render: function(data, type, row) {
              return `<div class="summary-amount total">${formatLargeAmount(data)}</div>`;
            }
          },
          { 
            data: 'total_used',
            render: function(data, type, row) {
              return `<div class="summary-amount used">${formatLargeAmount(data)}</div>`;
            }
          },
          { 
            data: 'total_balance',
            render: function(data, type, row) {
              const balanceStatus = getBalanceStatus(parseFloat(row.total_amount), parseFloat(data));
              const percentage = balanceStatus.percent.toFixed(0);
              return `
                <div>
                  <div class="summary-amount balance">${formatLargeAmount(data)}</div>
                  <div class="summary-progress">
                    <div class="summary-progress-bar">
                      <div class="summary-progress-fill ${balanceStatus.status}" style="width: ${balanceStatus.percent}%">
                        <span class="summary-progress-text">${percentage}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              `;
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              return `
                <button class="btn btn-sm btn-view viewClientBtn" data-client-id="${row.client_id}" title="View Details">
                  <i class="ti ti-eye"></i>
                </button>
                <button class="btn btn-sm btn-export exportClientBtn" data-client-id="${row.client_id}" title="Export to Excel">
                  <i class="ti ti-file-spreadsheet"></i>
                </button>
              `;
            }
          }
        ],
        order: [[3, 'desc']], // Order by total amount
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
          processing: '<i class="spinner-border spinner-border-sm"></i> Loading...',
          emptyTable: 'No client summary data found',
          zeroRecords: 'No matching records found'
        }
      });
    }

    // View Client CEEC Details Button Handler
    $(document).on('click', '.viewClientBtn', function () {
      const clientId = $(this).data('client-id');
      
      // Show loading
      Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      $.ajax({
        url: '<?= APP_URL ?>/ceec/crudData/getClientCEECDetails',
        method: 'GET',
        data: { client_id: clientId },
        dataType: 'json',
        success: function (res) {
          Swal.close();
          
          if (res.success && res.data) {
            const client = res.data.client;
            const records = res.data.records;
            const totals = res.data.totals;
            
            // Update modal title and summary
            $('#modalClientName').text(client.short_name + ' - ' + client.company_name);
            $('#modalTotalRecords').text(totals.record_count);
            $('#modalTotalAmount').text(formatLargeAmount(totals.total_amount));
            $('#modalUsedAmount').text(formatLargeAmount(totals.total_used));
            $('#modalBalanceAmount').text(formatLargeAmount(totals.total_balance));
            
            // Populate records table
            let recordsHtml = '';
            records.forEach(record => {
              recordsHtml += `
                <tr>
                  <td>${record.id}</td>
                  <td>${record.bank_name || 'N/A'}</td>
                  <td>${record.bank_code || 'N/A'}</td>
                  <td>${formatDateToDDMMYYYY(record.prepayment_date)}</td>
                  <td>${formatLargeAmount(record.amount)}</td>
                  <td>${formatLargeAmount(record.used_amount)}</td>
                  <td>${formatLargeAmount(record.balance_amount)}</td>
                  <td>${record.display === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                </tr>
              `;
            });
            
            $('#clientCEECRecordsBody').html(recordsHtml);
            
            // Show modal
            $('#viewClientCEECModal').modal('show');
          } else {
            Swal.fire('Error', res.message || 'Failed to load client CEEC data', 'error');
          }
        },
        error: function () {
          Swal.close();
          Swal.fire('Error', 'Failed to load client CEEC data. Please try again.', 'error');
        }
      });
    });

    // Export CEEC Records List Button Handler (NEW - WITH FILTERS)
    $('#exportCEECRecordsListBtn').on('click', function() {
      Swal.fire({
        title: 'Exporting...',
        text: 'Please wait while we prepare your CEEC records Excel export',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const exportUrl = '<?= APP_URL ?>/ceec/crudData/exportCEECRecordsList?client_filter=' + currentClientFilter + '&bank_filter=' + currentBankFilter + '&date_from=' + currentDateFrom + '&date_to=' + currentDateTo;
      window.location.href = exportUrl;
      
      setTimeout(function() {
        Swal.close();
      }, 2000);
    });

    // Export Client Summary Button Handler (All Clients)
    $('#exportClientSummaryBtn').on('click', function() {
      Swal.fire({
        title: 'Exporting...',
        text: 'Please wait while we prepare your client summary Excel export',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      const exportUrl = '<?= APP_URL ?>/ceec/crudData/exportClientSummary?client_filter=' + currentClientFilter;
      window.location.href = exportUrl;
      
      setTimeout(function() {
        Swal.close();
      }, 2000);
    });

    // Export Individual Client CEEC Summary
    $(document).on('click', '.exportClientBtn', function () {
      const clientId = $(this).data('client-id');
      const exportUrl = '<?= APP_URL ?>/ceec/crudData/exportClientCEECSummary?client_id=' + clientId;
      
      Swal.fire({
        title: 'Exporting...',
        text: 'Preparing client CEEC data for export',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      window.location.href = exportUrl;
      
      setTimeout(function() {
        Swal.close();
      }, 2000);
    });

    // Export individual CEEC
    $(document).on('click', '.exportBtn', function () {
      const id = $(this).data('id');
      const exportUrl = '<?= APP_URL ?>/ceec/crudData/exportCEEC?id=' + id;
      
      Swal.fire({
        title: 'Exporting...',
        text: 'Preparing CEEC data for export',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      window.location.href = exportUrl;
      
      setTimeout(function() {
        Swal.close();
      }, 2000);
    });

    // Update Statistics Cards
    function updateStatistics() {
      $.ajax({
        url: '<?= APP_URL ?>/ceec/crudData/statistics',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#totalRecords').text(res.data.total_records || 0);
            $('#thisMonthRecords').text(res.data.this_month_records || 0);
            
            // Update top client
            if (res.data.client_counts && res.data.client_counts.length > 0) {
              const topClient = res.data.client_counts[0];
              $('#topClientValue').text(topClient.record_count || 0);
              $('#topClientName').text(topClient.short_name || 'Top Client');
              $('#topClientAmount').text(formatLargeAmount(topClient.total_amount || 0));
            }
          }
        },
        error: function() {
          console.error('Failed to load statistics');
        }
      });
    }

    // Handle stats card click for filtering
    $(document).on('click', '.stats-card', function() {
      const filterType = $(this).data('filter-type');
      
      $('.stats-card').removeClass('active');
      $(this).addClass('active');
      
      if (filterType === 'all') {
        currentClientFilter = 0;
        currentBankFilter = 0;
        $('#clientFilter').val('0');
        $('#bankFilter').val('0');
      }
      
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
      if (typeof ceecDetailsTable !== 'undefined') {
        ceecDetailsTable.ajax.reload();
      }
    });

    // Filter change handlers
    $('#clientFilter').on('change', function() {
      currentClientFilter = $(this).val();
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
      if (typeof ceecDetailsTable !== 'undefined') {
        ceecDetailsTable.ajax.reload();
      }
    });

    $('#bankFilter').on('change', function() {
      currentBankFilter = $(this).val();
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
    });

    $('#dateFrom').on('change', function() {
      currentDateFrom = $(this).val();
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
    });

    $('#dateTo').on('change', function() {
      currentDateTo = $(this).val();
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
    });

    // Reset Filters
    $('#resetFiltersBtn').on('click', function() {
      $('#clientFilter').val('0');
      $('#bankFilter').val('0');
      $('#dateFrom').val('');
      $('#dateTo').val('');
      
      currentClientFilter = 0;
      currentBankFilter = 0;
      currentDateFrom = '';
      currentDateTo = '';
      
      $('.stats-card').removeClass('active');
      $('.stats-card-1').addClass('active');
      
      if (typeof ceecTable !== 'undefined') {
        ceecTable.ajax.reload();
      }
      if (typeof ceecDetailsTable !== 'undefined') {
        ceecDetailsTable.ajax.reload();
      }
    });

    // Form submission
    $('#ceecForm').on('submit', function (e) {
      e.preventDefault();

      const validation = validateForm();
      
      if (!validation.isValid) {
        // Open accordion if validation fails
        $('#ceecInfo').collapse('show');
        
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          html: '<ul style="text-align:left;"><li>' + validation.errors.join('</li><li>') + '</li></ul>',
          confirmButtonText: 'OK'
        });
        
        const firstError = $('.is-invalid').first();
        if (firstError.length) {
          $('html, body').animate({
            scrollTop: firstError.offset().top - 100
          }, 300);
          firstError.focus();
        }
        
        return false;
      }

      const submitBtn = $('#submitBtn');
      const originalText = submitBtn.html();
      submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');

      const formData = new FormData(this);
      formData.set('csrf_token', csrfToken);
      
      const action = $('#formAction').val();

      $.ajax({
        url: '<?= APP_URL ?>/ceec/crudData/' + action,
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
              text: res.message || 'Saved successfully', 
              timer: 1500, 
              showConfirmButton: false 
            });
            resetForm();
            if (typeof ceecTable !== 'undefined') {
              ceecTable.ajax.reload(null, false);
            }
            if (typeof ceecDetailsTable !== 'undefined') {
              ceecDetailsTable.ajax.reload(null, false);
            }
            updateStatistics();
          } else {
            Swal.fire({ 
              icon: 'error', 
              title: 'Error!', 
              html: res.message || 'Unable to save' 
            });
          }
        },
        error: function (xhr) {
          submitBtn.prop('disabled', false).html(originalText);
          
          let errorMsg = 'An error occurred while processing your request';
          
          if (xhr.status === 403) {
            errorMsg = 'Security token expired. Please refresh the page and try again.';
          } else {
            try {
              const response = JSON.parse(xhr.responseText);
              errorMsg = response.message || errorMsg;
            } catch (e) {
              errorMsg = xhr.responseText || errorMsg;
            }
          }
          
          Swal.fire({ 
            icon: 'error', 
            title: 'Server Error', 
            html: errorMsg 
          });
        }
      });
    });

    // Reset form function
    function resetForm() {
      $('#ceecForm')[0].reset();
      clearValidationErrors();
      $('#ceec_id').val('');
      $('#client_id').val('');
      $('#bank_id').val('');
      $('#formAction').val('insert');
      $('#formTitle').text('Add New CEEC');
      $('#submitBtnText').text('Save CEEC');
      $('#resetFormBtn').hide();
      $('#display').val('Y');
      currentUsedAmount = 0;
      isEditMode = false;
      
      // Collapse accordion
      $('#ceecInfo').collapse('hide');

      $('html, body').animate({ scrollTop: $('#ceecForm').offset().top - 100 }, 200);
    }

    $('#cancelBtn, #resetFormBtn').on('click', function (e) {
      e.preventDefault();
      resetForm();
    });

    // Edit CEEC
    $(document).on('click', '.editBtn', function () {
      const id = $(this).data('id');
      
      // Show loading
      Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      $.ajax({
        url: '<?= APP_URL ?>/ceec/crudData/getCEEC',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function (res) {
          Swal.close();
          
          if (res.success && res.data) {
            const ceec = res.data;

            clearValidationErrors();

            $('#ceec_id').val(ceec.id);
            $('#formAction').val('update');
            $('#formTitle').text('Edit CEEC');
            $('#submitBtnText').text('Update CEEC');
            $('#resetFormBtn').show();

            $('#client_id').val(ceec.client_id || '');
            $('#bank_id').val(ceec.bank_id || '');
            $('#prepayment_date').val(ceec.prepayment_date || '');
            $('#amount').val(ceec.amount || '');
            $('#display').val(ceec.display || 'Y');

            // Store current used amount
            currentUsedAmount = parseFloat(ceec.used_amount || 0);
            isEditMode = true;

            // Open accordion when editing
            $('#ceecInfo').collapse('show');

            $('html, body').animate({ scrollTop: $('#ceecForm').offset().top - 100 }, 500);
          } else {
            Swal.fire('Error', res.message || 'Failed to load CEEC data', 'error');
          }
        },
        error: function () {
          Swal.close();
          Swal.fire('Error', 'Failed to load CEEC data. Please try again.', 'error');
        }
      });
    });

    // Delete CEEC
    $(document).on('click', '.deleteBtn', function () {
      const id = $(this).data('id');
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
            url: '<?= APP_URL ?>/ceec/crudData/deletion',
            method: 'POST',
            data: { 
              id: id,
              csrf_token: csrfToken
            },
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
                ceecTable.ajax.reload(null, false);
                ceecDetailsTable.ajax.reload(null, false);
                updateStatistics();
              } else {
                Swal.fire('Error', res.message || 'Delete failed', 'error');
              }
            },
            error: function (xhr) {
              let errorMsg = 'Failed to delete CEEC record';
              
              if (xhr.status === 403) {
                errorMsg = 'Security token expired. Please refresh the page and try again.';
              }
              
              Swal.fire('Error', errorMsg, 'error');
            }
          });
        }
      });
    });

    // Initialize Both DataTables on page load
    initBasicDataTable();
    initDetailsDataTable();
    
    // Initial statistics load
    updateStatistics();
  });
</script>