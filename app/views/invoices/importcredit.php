<?php
// app/views/invoices/importcredit.php
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
  body { font-size: 0.8rem; }
  .form-label { font-size: 0.7rem; font-weight: 600; margin-bottom: 0.25rem; color: #2c3e50; }
  .form-control, .form-select { font-size: 0.75rem; padding: 0.3rem 0.45rem; height: auto; }
  .mb-2 { margin-bottom: 0.5rem !important; }
  
  .accordion-button {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #333 !important;
    padding: 0.8rem 1rem;
    border: none;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    font-size: 0.85rem;
  }
  
  .accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    box-shadow: none !important;
  }
  
  .accordion-button::after { margin-left: 0 !important; }
  .accordion-button:not(.collapsed)::after { filter: brightness(0) invert(1); }
  .accordion-button:focus { box-shadow: none !important; border-color: rgba(0,0,0,.125) !important; }
  .accordion-button:hover { background-color: #e9ecef !important; }
  .accordion-button:not(.collapsed):hover { background: linear-gradient(135deg, #5568d3 0%, #6a4893 100%) !important; }
  
  .accordion-title-section { display: flex; align-items: center; gap: 8px; flex: 1; }
  
  .accordion-item {
    border: none;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 1.3rem;
  }
  
  .accordion-body { background: #ffffff; padding: 1.1rem; }
  
  .datatable-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 11px 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
  }
  
  .datatable-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .datatable-actions { display: flex; align-items: center; gap: 8px; }
  
  .custom-search-box { position: relative; width: 230px; }
  
  .custom-search-box input {
    width: 100%;
    padding: 0.4rem 2.3rem 0.4rem 0.8rem;
    border: 2px solid #e9ecef;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    transition: all 0.3s;
  }
  
  .custom-search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }
  
  .custom-search-box i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    pointer-events: none;
  }
  
  .text-danger { color: #dc3545; font-weight: bold; }
  .is-invalid { border-color: #dc3545 !important; }
  .invalid-feedback { display: block; color: #dc3545; font-size: 0.65rem; margin-top: 0.15rem; }
  .readonly-field { background-color: #e9ecef; cursor: not-allowed; }
  
  .panel-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 6px 10px;
    font-weight: 600;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 8px 8px 0 0;
  }
  
  .panel-body {
    background: white;
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 8px 8px;
    padding: 10px;
  }
  
  .quotation-category-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 12px;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 12px;
    margin-bottom: 0;
    border-radius: 6px 6px 0 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .quotation-category-header:first-child { margin-top: 0; }
  
  .category-items-display {
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 6px 6px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.3s;
  }
  
  .quotation-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.7rem;
  }
  
  .quotation-items-table thead {
    background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
    color: white;
  }
  
  .quotation-items-table thead th {
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #667eea;
  }
  
  .quotation-items-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
  }
  
  .quotation-items-table tbody tr:hover { background-color: #f8f9fa; }
  .quotation-items-table tbody tr:last-child { border-bottom: none; }
  .quotation-items-table tbody td { padding: 8px 10px; vertical-align: middle; }
  .quotation-items-table .text-center { text-align: center; }
  .quotation-items-table .text-right { text-align: right; }
  .quotation-items-table .item-description { font-weight: 500; color: #2c3e50; }
  
  .quotation-items-table .item-unit,
  .quotation-items-table .item-currency {
    text-transform: uppercase;
    font-size: 0.6rem;
    color: #7f8c8d;
    font-weight: 500;
  }
  
  .quotation-items-table .item-total {
    font-weight: 700;
    color: #2c3e50;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
  }
  
  .quotation-items-table .tva-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  
  .quotation-items-table .item-quantity-input,
  .quotation-items-table .item-taux-input {
    width: 100%;
    padding: 4px 6px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 0.7rem;
    text-align: right;
    transition: all 0.2s;
  }
  
  .quotation-items-table .item-quantity-input:focus,
  .quotation-items-table .item-taux-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.25);
    background-color: #f8f9fa;
  }
  
  .quotation-items-table .tva-display {
    width: 15px;
    height: 15px;
    pointer-events: none;
    opacity: 0.7;
  }
  
  .delete-item-btn {
    padding: 4px 10px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 600;
  }
  
  .delete-item-btn:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
  }
  
  .delete-item-btn i { font-size: 0.8rem; }
  
  .summary-totals {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 16px;
    border: 1px solid #dee2e6;
  }
  
  .summary-totals table { width: 100%; }
  .summary-totals td { padding: 6px 0; font-size: 0.8rem; }
  .summary-totals td:first-child { color: #495057; font-weight: 500; }
  .summary-totals td:last-child { text-align: right; font-weight: 600; color: #212529; font-size: 0.85rem; }
  
  .summary-totals .grand-total td {
    font-size: 0.95rem;
    padding-top: 10px;
    border-top: 2px solid #adb5bd;
    font-weight: 700;
  }
  
  .summary-totals .grand-total td:last-child { color: #28a745; }
  
  .btn-sm { 
    padding: 0.25rem 0.5rem !important; 
    font-size: 0.75rem !important;
    min-width: 32px !important;
    height: 28px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s !important;
  }
  
  .btn-pdf-page1 {
    background: #dc3545 !important;
    color: white !important;
    border: none !important;
  }
  
  .btn-pdf-page1:hover {
    background: #c82333 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
  }
  
  .btn-danger {
    background: #dc3545 !important;
    color: white !important;
    border: none !important;
  }

  .btn-danger:hover {
    background: #c82333 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
  }
  
  .card {
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: none;
    border-radius: 12px;
  }
  
  .quotation-content {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    text-align: center;
    color: #7f8c8d;
    font-size: 0.8rem;
  }

  #emcf-modal .modal-dialog {
    max-width: 550px;
    margin: 1.75rem auto;
  }
  
  #emcf-modal .modal-content {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
  }
  
  #emcf-modal .modal-header {
    background: linear-gradient(135deg, #800000 0%, #A52A2A 100%);
    color: white;
    border: none;
    padding: 20px 25px;
    position: relative;
  }
  
  #emcf-modal .modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
  }
  
  #emcf-modal .modal-title i {
    font-size: 1.5rem;
  }
  
  #emcf-modal .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
  }
  
  #emcf-modal .btn-close:hover {
    opacity: 1;
  }
  
  #emcf-modal .modal-body {
    padding: 30px 25px;
    background: #f8f9fa;
  }
  
  .emcf-info-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }
  
  .emcf-info-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .emcf-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .emcf-stat-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 18px;
    text-align: center;
    border: 2px solid transparent;
    transition: all 0.3s ease;
  }
  
  .emcf-stat-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }
  
  .emcf-stat-box.primary-stat {
    border-color: #667eea;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  }
  
  .emcf-stat-box.danger-stat {
    border-color: #dc3545;
    background: linear-gradient(135deg, #fee 0%, #fcc 100%);
  }
  
  .emcf-stat-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }
  
  .emcf-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 5px;
  }
  
  .emcf-stat-box.primary-stat .emcf-stat-value {
    color: #1976d2;
  }
  
  .emcf-stat-box.danger-stat .emcf-stat-value {
    color: #dc3545;
  }
  
  .emcf-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
  }
  
  .emcf-stat-box.primary-stat .emcf-stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .emcf-stat-box.danger-stat .emcf-stat-icon {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }
  
  .emcf-alert {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    border: 2px solid #ffc107;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }
  
  .emcf-alert-icon {
    width: 35px;
    height: 35px;
    background: #ffc107;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
  }
  
  .emcf-alert-content {
    flex: 1;
  }
  
  .emcf-alert-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #856404;
    margin-bottom: 5px;
  }
  
  .emcf-alert-text {
    font-size: 0.75rem;
    color: #856404;
    margin: 0;
    line-height: 1.5;
  }
  
  #emcf-modal .modal-footer {
    background: white;
    border-top: 2px solid #e9ecef;
    padding: 20px 25px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }
  
  #emcf-modal .modal-footer .btn {
    padding: 10px 24px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
  }
  
  #btn-cancel-emcf {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
  }
  
  #btn-cancel-emcf:hover {
    background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }
  
  #btn-confirm-emcf {
    background: linear-gradient(135deg, #800000 0%, #A52A2A 100%);
    color: white;
  }
  
  #btn-confirm-emcf:hover {
    background: linear-gradient(135deg, #600000 0%, #8B0000 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(128, 0, 0, 0.4);
  }
  
  #btn-confirm-emcf:disabled,
  #btn-cancel-emcf:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }

  @media (max-width: 768px) {
    .quotation-items-table { font-size: 0.6rem; }
    .quotation-items-table thead th { padding: 6px 4px; font-size: 0.55rem; }
    .quotation-items-table tbody td { padding: 6px 4px; }
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- Credit Note Form Accordion -->
        <div class="accordion mb-4" id="creditNoteAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingCreditNote">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCreditNote" aria-expanded="false" aria-controls="collapseCreditNote">
                <div class="accordion-title-section">
                  <i class="ti ti-file-minus"></i>
                  <span id="formTitle">Create New Credit Note</span>
                </div>
              </button>
            </h2>
            <div id="collapseCreditNote" class="accordion-collapse collapse" aria-labelledby="headingCreditNote" data-bs-parent="#creditNoteAccordion">
              <div class="accordion-body">
                
                <form id="creditNoteForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="credit_note_id" id="credit_note_id" value="">
                  <input type="hidden" name="action" id="formAction" value="insert">
                  <input type="hidden" name="invoice_id" id="invoice_id" value="">
                  <input type="hidden" name="credit_note_items" id="credit_note_items" value="">
                  <input type="hidden" name="calculated_sub_total" id="calculated_sub_total" value="0.00">
                  <input type="hidden" name="calculated_vat_amount" id="calculated_vat_amount" value="0.00">
                  <input type="hidden" name="calculated_total_amount" id="calculated_total_amount" value="0.00">

                  <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                      <label class="form-label">Select Invoice <span class="text-danger">*</span></label>
                      <select name="invoice_ref_select" id="invoice_ref_select" class="form-select" required>
                        <option value="">-- Select Invoice --</option>
                      </select>
                      <div class="invalid-feedback" id="invoice_ref_select_error"></div>
                    </div>

                    <div class="col-md-4 mb-2">
                      <label class="form-label">Credit Note Reference</label>
                      <input type="text" name="credit_note_ref" id="credit_note_ref" class="form-control readonly-field" readonly placeholder="Auto-generated">
                    </div>

                    <div class="col-md-4 mb-2">
                      <label class="form-label">Credit Note Type <span class="text-danger">*</span></label>
                      <select name="credit_note_type" id="credit_note_type" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="COR">COR</option>
                        <option value="RAN">RAN</option>
                        <option value="RAM">RAM</option>
                        <option value="RRR">RRR</option>
                      </select>
                      <div class="invalid-feedback" id="credit_note_type_error"></div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-12 mb-2">
                      <label class="form-label">CODEDEF/DGI</label>
                      <input type="text" name="codedefdgi" id="codedefdgi" class="form-control readonly-field" readonly placeholder="Auto-populated from invoice">
                    </div>
                  </div>

                  <div class="panel-header">
                    <i class="ti ti-receipt"></i> CREDIT NOTE ITEMS (Categories 3 & 4)
                  </div>
                  <div class="panel-body">
                    <div id="creditNoteItemsContainer" style="min-height: 45px;">
                      <div class="quotation-content">
                        <p style="margin: 0;"><i class="ti ti-info-circle me-1"></i> Select an invoice to load items</p>
                      </div>
                    </div>
                    
                    <div class="summary-totals">
                      <table>
                        <tr>
                          <td>Total excl. TVA</td>
                          <td id="totalExclTVA">$0.00</td>
                        </tr>
                        <tr>
                          <td>TVA (16%)</td>
                          <td id="tvaAmount">$0.00</td>
                        </tr>
                        <tr class="grand-total">
                          <td>Grand Total</td>
                          <td id="grandTotal">$0.00</td>
                        </tr>
                      </table>
                    </div>
                  </div>

                  <div class="row mt-4">
                    <div class="col-12 text-end">
                      <button type="button" class="btn btn-secondary btn-sm" id="cancelBtn">
                        <i class="ti ti-x me-1"></i> Cancel
                      </button>
                      <button type="submit" class="btn btn-primary btn-sm ms-2" id="submitBtn">
                        <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save Credit Note</span>
                      </button>
                    </div>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Credit Notes DataTable -->
        <div class="card shadow-sm">
          <div class="datatable-header">
            <div class="datatable-title">
              <i class="ti ti-list"></i>
              <span>Credit Notes List</span>
            </div>
            <div class="datatable-actions">
              <div class="custom-search-box">
                <input type="text" id="customSearchBox" placeholder="Search credit notes..." autocomplete="off">
                <i class="ti ti-search"></i>
              </div>
            </div>
          </div>
          
          <div class="card-body">
            <div class="table-responsive">
              <table id="creditNotesTable" class="table table-striped table-bordered nowrap w-100" style="width: 100%;">
                <thead>
                  <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 140px;">CREDIT NOTE REF</th>
                    <th style="width: 130px;">INVOICE REF</th>
                    <th style="width: 110px;">CLIENT</th>
                    <th style="width: 60px;">TYPE</th>
                    <th style="width: 90px;">CREATED DATE</th>
                    <th style="width: 90px;">CREATED BY</th>
                    <th style="width: 80px;">AMOUNT</th>
                    <th style="width: 160px;">ACTIONS</th>
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
<div class="modal fade" id="emcf-modal" tabindex="-1" aria-labelledby="emcf-modal-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="emcf-modal-title">
          <i class="ti ti-file-check"></i>
          DGI Verification
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Info Card -->
        <div class="emcf-info-card">
          <div class="emcf-info-title">
            <i class="ti ti-info-circle"></i>
            <span>Invoice Amounts</span>
          </div>
          
          <div class="emcf-stats-grid">
            <!-- Total Amount -->
            <div class="emcf-stat-box primary-stat">
              <div class="emcf-stat-icon">
                <i class="ti ti-receipt"></i>
              </div>
              <div class="emcf-stat-label">Total Amount</div>
              <div class="emcf-stat-value" id="total-badge">0.00</div>
            </div>
            
            <!-- VAT Total -->
            <div class="emcf-stat-box danger-stat">
              <div class="emcf-stat-icon">
                <i class="ti ti-calculator"></i>
              </div>
              <div class="emcf-stat-label">VAT Total (16%)</div>
              <div class="emcf-stat-value" id="dgi-total-badge">0.00</div>
            </div>
          </div>
        </div>
        
        <!-- Alert Message -->
        <div class="emcf-alert">
          <div class="emcf-alert-icon">
            <i class="ti ti-alert-triangle"></i>
          </div>
          <div class="emcf-alert-content">
            <div class="emcf-alert-title">Confirmation Required</div>
            <p class="emcf-alert-text">
              Please verify the amounts above before confirming DGI verification. This action will mark the invoice as DGI verified in the system.
            </p>
          </div>
        </div>
        
        <!-- Hidden Inputs -->
        <input type="hidden" id="emcf-invoice-uid" value="">
        <input type="hidden" id="emcf-invoice-id" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" id="btn-cancel-emcf">
          <i class="ti ti-x"></i>
          Cancel
        </button>
        <button type="button" class="btn" id="btn-confirm-emcf">
          <i class="ti ti-check"></i>
          Confirm DGI Verification
        </button>
      </div>
    </div>
  </div>
</div>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
var creditNoteItemsData = [];

$(document).ready(function () {
  let creditNotesTable;
  let baseUrl = '<?= rtrim(APP_URL, "/") ?>';
  const CONTROLLER_URL = baseUrl + '/importcredit';
  const csrfToken = $('#creditNoteForm').data('csrf-token');
  
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#039;'};
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  }
  
  function formatNumber(value, maxDecimals = 4) {
    const num = parseFloat(value);
    if (isNaN(num) || num === 0) return '';
    
    let formatted = num.toFixed(maxDecimals);
    formatted = formatted.replace(/\.?0+$/, '');
    
    return formatted;
  }

  // Load validated invoices for dropdown
  function loadInvoices() {
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getValidatedInvoices',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          let options = '<option value="">-- Select Invoice --</option>';
          res.data.forEach(function(invoice) {
            options += `<option value="${invoice.id}" data-invoice-ref="${escapeHtml(invoice.invoice_ref)}" data-codedefdgi="${escapeHtml(invoice.codedefdgi || '')}">${escapeHtml(invoice.invoice_ref)}</option>`;
          });
          $('#invoice_ref_select').html(options);
        } else {
          $('#invoice_ref_select').html('<option value="">-- No Validated Invoices Available --</option>');
        }
      },
      error: function() {
        $('#invoice_ref_select').html('<option value="">-- Error Loading Invoices --</option>');
      }
    });
  }

  // When invoice is selected
  $('#invoice_ref_select').on('change', function() {
    const invoiceId = $(this).val();
    const selectedOption = $(this).find('option:selected');
    const invoiceRef = selectedOption.data('invoice-ref');
    const codedefDgi = selectedOption.data('codedefdgi');
    
    $('#invoice_id').val(invoiceId);
    
    if (!invoiceId) {
      $('#credit_note_ref').val('');
      $('#codedefdgi').val('');
      clearCreditNoteItems();
      return;
    }
    
    // Set credit note reference
    $('#credit_note_ref').val('CN-' + invoiceRef);
    
    // Set CODEDEF/DGI
    $('#codedefdgi').val(codedefDgi || 'N/A');
    
    // Load invoice items (categories 3 & 4 only)
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getInvoiceItemsForCredit',
      method: 'GET',
      data: { invoice_id: invoiceId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.items && res.items.length > 0) {
          creditNoteItemsData = res.items;
          displayCreditNoteItems(res.items);
          recalculateAllTotals();
        } else {
          clearCreditNoteItems();
          Swal.fire({
            icon: 'warning',
            title: 'No Items',
            text: res.message || 'This invoice has no operational/agency items (Categories 3 & 4)'
          });
        }
      },
      error: function() {
        clearCreditNoteItems();
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load invoice items'
        });
      }
    });
  });

  function displayCreditNoteItems(items) {
    if (!items || items.length === 0) {
      $('#creditNoteItemsContainer').html('<div class="quotation-content"><p style="margin: 0; color: #e67e22;"><i class="ti ti-alert-triangle me-1"></i> No items found.</p></div>');
      return;
    }
    
    // Group by category
    var groupedByCategory = {};
    items.forEach(function(item) {
      var catId = parseInt(item.category_id || 0);
      var catName = item.category_name || 'Uncategorized';
      var catHeader = item.category_header || catName;
      
      if (!groupedByCategory[catId]) {
        groupedByCategory[catId] = {
          category_id: catId,
          category_name: catName,
          category_header: catHeader,
          items: []
        };
      }
      groupedByCategory[catId].items.push(item);
    });
    
    var categorizedItems = Object.values(groupedByCategory);
    
    let html = '';
    
    categorizedItems.forEach(function(category, categoryIndex) {
      if (!category.items || category.items.length === 0) return;
      
      html += '<div class="quotation-category-header">';
      html += '<span>' + escapeHtml(category.category_header || category.category_name || 'UNCATEGORIZED') + '</span>';
      html += '</div>';
      
      html += '<div class="category-items-display">';
      html += '<table class="quotation-items-table">';
      
      html += '<thead>';
      html += '<tr>';
      html += '<th style="width: 35%;">DESCRIPTION</th>';
      html += '<th style="width: 8%;">UNIT</th>';
      html += '<th style="width: 12%;">QTY</th>';
      html += '<th style="width: 12%;">TAUX/USD</th>';
      html += '<th style="width: 8%;" class="text-center">TVA</th>';
      html += '<th style="width: 10%;" class="text-right">TVA/USD</th>';
      html += '<th style="width: 10%;" class="text-right">TOTAL USD</th>';
      html += '<th style="width: 5%;" class="text-center">ACTION</th>';
      html += '</tr>';
      html += '</thead>';
      
      html += '<tbody>';
      
      category.items.forEach(function(item, itemIndex) {
        const globalItemIndex = creditNoteItemsData.findIndex(i => i.id === item.id);
        
        const hasTVA = parseInt(item.has_tva || 0) === 1;
        const currency = escapeHtml(item.currency_short_name || 'USD');
        const quantity = parseFloat(item.quantity || 1);
        const tauxUsd = parseFloat(item.taux_usd || item.cost_usd || 0);
        const tvaUsd = parseFloat(item.tva_usd || 0);
        const totalUsd = parseFloat(item.total_usd || 0);

        html += `<tr data-item-index="${globalItemIndex}">`;
        html += '<td class="item-description">' + escapeHtml(item.item_name || 'N/A') + '</td>';
        html += '<td class="text-center item-unit">' + escapeHtml(item.unit_text || item.unit_name || 'Unit') + '</td>';
        
        // Editable Quantity
        html += '<td><input type="number" class="form-control form-control-sm text-right item-quantity-input" data-item-index="' + globalItemIndex + '" value="' + quantity.toFixed(3) + '" step="0.001" min="0"></td>';
        
        // Editable Taux USD
        html += '<td><input type="number" class="form-control form-control-sm text-right item-taux-input" data-item-index="' + globalItemIndex + '" value="' + tauxUsd.toFixed(2) + '" step="0.01" min="0"></td>';
        
        // Editable TVA Checkbox
        html += '<td class="text-center"><input type="checkbox" class="tva-checkbox" data-item-index="' + globalItemIndex + '" ' + (hasTVA ? 'checked' : '') + '></td>';
        
        // Auto-calculated TVA USD (read-only display)
        html += '<td class="text-right"><span class="item-tva-display">' + formatNumber(tvaUsd, 2) + '</span></td>';
        
        // Auto-calculated Total USD (read-only display)
        html += '<td class="text-right"><span class="item-total-display">' + formatNumber(totalUsd, 2) + '</span></td>';
        
        // Delete button
        html += '<td class="text-center"><button type="button" class="delete-item-btn" data-item-index="' + globalItemIndex + '"><i class="ti ti-trash"></i></button></td>';
        html += '</tr>';
      });
      
      html += '</tbody>';
      html += '</table>';
      html += '</div>';
      html += '<div style="margin-bottom: 12px;"></div>';
    });
    
    if (html === '') {
      html = '<p style="margin: 18px 0; color: #e67e22; text-align: center;"><i class="ti ti-alert-triangle me-1"></i> No categories with items found.</p>';
    }
    
    $('#creditNoteItemsContainer').html(html);
    attachItemEventListeners();
  }

  function attachItemEventListeners() {
    // Quantity input change handler
    $(document).off('input', '.item-quantity-input');
    $(document).on('input', '.item-quantity-input', function() {
      const itemIndex = parseInt($(this).data('item-index'));
      const newQuantity = parseFloat($(this).val()) || 0;
      
      if (creditNoteItemsData[itemIndex]) {
        creditNoteItemsData[itemIndex].quantity = newQuantity;
        recalculateItemRow(itemIndex);
        recalculateAllTotals();
      }
    });
    
    // Taux USD input change handler
    $(document).off('input', '.item-taux-input');
    $(document).on('input', '.item-taux-input', function() {
      const itemIndex = parseInt($(this).data('item-index'));
      const newTaux = parseFloat($(this).val()) || 0;
      
      if (creditNoteItemsData[itemIndex]) {
        creditNoteItemsData[itemIndex].taux_usd = newTaux;
        recalculateItemRow(itemIndex);
        recalculateAllTotals();
      }
    });
    
    // TVA checkbox change handler
    $(document).off('change', '.tva-checkbox');
    $(document).on('change', '.tva-checkbox', function() {
      const itemIndex = parseInt($(this).data('item-index'));
      const hasTVA = $(this).is(':checked') ? 1 : 0;
      
      if (creditNoteItemsData[itemIndex]) {
        creditNoteItemsData[itemIndex].has_tva = hasTVA;
        recalculateItemRow(itemIndex);
        recalculateAllTotals();
      }
    });
    
    // Delete item button handler
    $(document).off('click', '.delete-item-btn');
    $(document).on('click', '.delete-item-btn', function() {
      const itemIndex = parseInt($(this).data('item-index'));
      const $row = $(this).closest('tr');
      
      Swal.fire({
        title: 'Delete Item?',
        text: 'Are you sure you want to delete this item from the credit note?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-trash me-1"></i> Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          // Remove from creditNoteItemsData
          if (creditNoteItemsData[itemIndex]) {
            creditNoteItemsData.splice(itemIndex, 1);
          }
          
          // Remove row from table
          $row.remove();
          
          // Update all item indices in remaining rows
          $('.quotation-items-table tbody tr').each(function(idx) {
            $(this).attr('data-item-index', idx);
            $(this).find('.item-quantity-input').attr('data-item-index', idx);
            $(this).find('.item-taux-input').attr('data-item-index', idx);
            $(this).find('.tva-checkbox').attr('data-item-index', idx);
            $(this).find('.delete-item-btn').attr('data-item-index', idx);
          });
          
          // Recalculate totals
          recalculateAllTotals();
          
          Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Item has been removed from credit note.',
            timer: 1500,
            showConfirmButton: false
          });
        }
      });
    });
  }
  
  function recalculateItemRow(itemIndex) {
    if (!creditNoteItemsData[itemIndex]) return;
    
    const item = creditNoteItemsData[itemIndex];
    const quantity = parseFloat(item.quantity || 0);
    const tauxUsd = parseFloat(item.taux_usd || 0);
    const hasTVA = parseInt(item.has_tva || 0) === 1;
    
    // Calculate subtotal
    const subtotal = quantity * tauxUsd;
    
    // Calculate TVA
    const tvaUsd = hasTVA ? (subtotal * 0.16) : 0;
    
    // Calculate total
    const totalUsd = subtotal + tvaUsd;
    
    // Update data
    creditNoteItemsData[itemIndex].tva_usd = tvaUsd;
    creditNoteItemsData[itemIndex].total_usd = totalUsd;
    
    // Update display in the row
    const $row = $(`.quotation-items-table tbody tr[data-item-index="${itemIndex}"]`);
    $row.find('.item-tva-display').text(formatNumber(tvaUsd, 2));
    $row.find('.item-total-display').text(formatNumber(totalUsd, 2));
  }

  function recalculateAllTotals() {
    let totalExclTVA = 0;
    let totalTVA = 0;
    
    creditNoteItemsData.forEach((item) => {
      const quantity = parseFloat(item.quantity || 1);
      const tauxUsd = parseFloat(item.taux_usd || 0);
      
      const subtotal = quantity * tauxUsd;
      
      const hasTVA = parseInt(item.has_tva || 0) === 1;
      const tva = hasTVA ? (subtotal * 0.16) : 0;
      
      totalExclTVA += subtotal;
      totalTVA += tva;
    });
    
    const grandTotal = totalExclTVA + totalTVA;
    
    // MAKE ALL VALUES NEGATIVE (Credit Note)
    totalExclTVA = -Math.abs(totalExclTVA);
    totalTVA = -Math.abs(totalTVA);
    const negativeGrandTotal = -Math.abs(grandTotal);
    
    $('#calculated_sub_total').val(totalExclTVA.toFixed(2));
    $('#calculated_vat_amount').val(totalTVA.toFixed(2));
    $('#calculated_total_amount').val(negativeGrandTotal.toFixed(2));
    
    updateSummaryTotals(totalExclTVA, totalTVA, negativeGrandTotal);
  }

  function clearCreditNoteItems() {
    creditNoteItemsData = [];
    $('#calculated_sub_total').val('0.00');
    $('#calculated_vat_amount').val('0.00');
    $('#calculated_total_amount').val('0.00');
    $('#creditNoteItemsContainer').html('<div class="quotation-content"><p style="margin: 0;"><i class="ti ti-info-circle me-1"></i> Select an invoice to load items</p></div>');
    updateSummaryTotals(0, 0, 0);
  }

  function updateSummaryTotals(subTotal, vatAmount, totalAmount) {
    $('#totalExclTVA').text('$' + subTotal.toFixed(2));
    $('#tvaAmount').text('$' + vatAmount.toFixed(2));
    $('#grandTotal').text('$' + totalAmount.toFixed(2));
  }

  function validateForm() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    
    let errors = [];

    if (!$('#invoice_ref_select').val()) {
      $('#invoice_ref_select').addClass('is-invalid');
      $('#invoice_ref_select_error').text('Invoice is required').show();
      errors.push('Invoice is required');
    }

    if (!$('#credit_note_type').val()) {
      $('#credit_note_type').addClass('is-invalid');
      $('#credit_note_type_error').text('Credit note type is required').show();
      errors.push('Credit note type is required');
    }

    if (creditNoteItemsData.length === 0) {
      errors.push('At least one item is required in the credit note');
    }

    return { isValid: errors.length === 0, errors };
  }

  function resetForm() {
    $('#creditNoteForm')[0].reset();
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    $('#credit_note_id').val('');
    $('#invoice_id').val('');
    $('#formAction').val('insert');
    $('#formTitle').text('Create New Credit Note');
    $('#submitBtnText').text('Save Credit Note');
    $('#credit_note_ref').val('');
    $('#credit_note_type').val('');
    $('#codedefdgi').val('');
    $('#calculated_sub_total').val('0.00');
    $('#calculated_vat_amount').val('0.00');
    $('#calculated_total_amount').val('0.00');
    clearCreditNoteItems();
    $('#collapseCreditNote').collapse('hide');
    loadInvoices(); // Reload invoices to refresh available list
  }

  $('#creditNoteForm').on('submit', function (e) {
    e.preventDefault();
    
    const validation = validateForm();
    if (!validation.isValid) {
      Swal.fire({
        icon: 'error', 
        title: 'Validation Error', 
        html: '<ul style="text-align:left;"><li>' + validation.errors.join('</li><li>') + '</li></ul>'
      });
      $('#collapseCreditNote').collapse('show');
      return;
    }

    $('#credit_note_items').val(JSON.stringify(creditNoteItemsData));

    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');

    const formData = new FormData(this);
    formData.set('csrf_token', csrfToken);

    $.ajax({
      url: CONTROLLER_URL + '/crudData/' + $('#formAction').val(),
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
          if (creditNotesTable) creditNotesTable.ajax.reload(null, false);
        } else {
          Swal.fire({ 
            icon: 'error', 
            title: 'Error!', 
            html: res.message 
          });
        }
      },
      error: function (xhr, status, error) {
        submitBtn.prop('disabled', false).html(originalText);
        Swal.fire({ 
          icon: 'error', 
          title: 'Error', 
          text: 'Failed to save credit note' 
        });
      }
    });
  });

  $('#cancelBtn').on('click', (e) => { e.preventDefault(); resetForm(); });

  function initDataTable() {
    if ($.fn.DataTable.isDataTable('#creditNotesTable')) {
      $('#creditNotesTable').DataTable().destroy();
    }

    creditNotesTable = $('#creditNotesTable').DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      autoWidth: false,
      ajax: { 
        url: CONTROLLER_URL + '/crudData/listingCreditNotes',
        type: 'GET',
        data: function(d) { 
          d.search = { value: $('#customSearchBox').val() };
        },
        dataSrc: function(json) {
          return json.data || [];
        }
      },
      columns: [
        { 
          data: null,
          orderable: false,
          searchable: false,
          render: function(data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          }
        },
        { 
          data: 'credit_note_ref',
          render: function(data, type, row) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'invoice_ref',
          render: function(data, type, row) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'client_name',
          render: function(data, type, row) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'credit_note_type',
          render: function(data, type, row) {
            const typeColors = {
              'COR': 'primary',
              'RAN': 'warning',
              'RAM': 'success',
              'RRR': 'info'
            };
            const color = typeColors[data] || 'secondary';
            return `<span class="badge bg-${color}">${escapeHtml(data || 'N/A')}</span>`;
          }
        },
        { 
          data: 'created_at',
          render: function(data, type, row) {
            if (!data) return '';
            return new Date(data).toLocaleDateString('en-GB');
          }
        },
        { 
          data: 'created_by_name',
          render: function(data, type, row) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'calculated_total_amount',
          render: function(data, type, row) {
            const amount = parseFloat(data || 0);
            return '<span style="color: #dc3545; font-weight: 600;">$' + amount.toFixed(2) + '</span>';
          }
        },
        { 
          data: null,
          orderable: false,
          searchable: false,
          render: function(data, type, row) {
            return `
              <button class="btn btn-sm btn-success dgiBtn" data-id="${row.id}" title="Send to DGI">
                <i class="ti ti-send"></i> DGI
              </button>
              <button class="btn btn-sm btn-pdf-page1 pdfBtn" data-id="${row.id}" title="View PDF">
                <i class="ti ti-file-type-pdf"></i>
              </button>`;
          }
        }
      ],
      order: [[5, 'desc']],
      pageLength: 25,
      dom: 'rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      drawCallback: function() { }
    });

    $('#customSearchBox').on('keyup', function() {
      creditNotesTable.ajax.reload();
    });
  }

  $(document).on('click', '.pdfBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    window.open(CONTROLLER_URL + '/crudData/viewCreditNotePDF?id=' + id, '_blank');
  });

  $(document).on('click', '.dgiBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    Swal.fire({
      title: 'Send to DGI?',
      html: 'Are you sure you want to send this credit note to DGI for verification?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="ti ti-send me-1"></i> Yes, send to DGI!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: CONTROLLER_URL + '/crudData/sendToDGI',
          method: 'POST',
          data: { id: id, csrf_token: csrfToken },
          dataType: 'json',
          success: function(res) {
            if (res.success && res.data) {
              // Populate modal with data
              $("#total-badge").text(res.data.total || '0.00');
              $("#dgi-total-badge").text(res.data.vtotal || '0.00');
              $("#emcf-invoice-uid").val(res.data.uid || '');
              $("#emcf-invoice-id").val(id);
              
              // Show the modal
              $("#emcf-modal").modal('show');
            }
            /*if (res.success) {
              Swal.fire({
                icon: 'success', 
                title: 'Success!', 
                text: res.message || 'Credit note sent to DGI successfully!', 
                timer: 2000, 
                showConfirmButton: false
              });
              creditNotesTable.ajax.reload(null, false);
            }*/ else {
              Swal.fire('Error', res.message, 'error');
            }
          },
          error: function(xhr, status, error) {
            Swal.fire('Error', 'Failed to send credit note to DGI', 'error');
          }
        });
      }
    });
  });

   $("#btn-confirm-emcf").on('click', function() {
    const uid = $("#emcf-invoice-uid").val();
    const invoiceId = $("#emcf-invoice-id").val();
    
    if (!uid || !invoiceId) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Missing required information',
        confirmButtonColor: '#800000'
      });
      return;
    }
    
    // Disable buttons during processing
    $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', true);
    $('#btn-confirm-emcf').html('<i class="spinner-border spinner-border-sm me-2"></i>Processing...');
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/finalizeEMCF',
      method: 'POST',
      data: { 
        type: 'confirm', 
        csrf_token: csrfToken, 
        uid: uid, 
        invoice_id: invoiceId 
      },
      dataType: 'json',
      success: function(res) {
        $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
        $('#btn-confirm-emcf').html('<i class="ti ti-check"></i> Confirm DGI Verification');
        
        if (res.success) {
          $("#emcf-modal").modal('hide');
          
          Swal.fire({ 
            icon: 'success', 
            title: 'DGI Verified!', 
            text: 'Invoice has been successfully verified with DGI',
            timer: 2000, 
            showConfirmButton: false 
          });
          
          if (invoicesTable) invoicesTable.ajax.reload(null, false);
          updateStatistics();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Confirmation Failed',
            text: res.message || 'Failed to confirm DGI verification',
            confirmButtonColor: '#800000'
          });
        }
      },
      error: function(xhr) {
        $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
        $('#btn-confirm-emcf').html('<i class="ti ti-check"></i> Confirm DGI Verification');
        
        let errorMessage = 'Failed to confirm verification';
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          errorMessage = errorResponse.message || errorMessage;
        } catch (e) {
          errorMessage = xhr.responseText || errorMessage;
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMessage,
          confirmButtonColor: '#800000'
        });
      }
    });
  });

  // ========== DGI MODAL - CANCEL BUTTON ==========
  $("#btn-cancel-emcf").on('click', function() {
    const uid = $("#emcf-invoice-uid").val();
    
    if (!uid) {
      $("#emcf-modal").modal('hide');
      return;
    }
    
    // Disable buttons during processing
    $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', true);
    $('#btn-cancel-emcf').html('<i class="spinner-border spinner-border-sm me-2"></i>Canceling...');
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/finalizeEMCF',
      method: 'POST',
      data: { 
        type: 'cancel', 
        csrf_token: csrfToken, 
        uid: uid 
      },
      dataType: 'json',
      success: function(res) {
        $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
        $('#btn-cancel-emcf').html('<i class="ti ti-x"></i> Cancel');
        
        $("#emcf-modal").modal('hide');
        
        if (res.success) {
          Swal.fire({ 
            icon: 'info', 
            title: 'Canceled', 
            text: 'DGI verification has been canceled',
            timer: 1500, 
            showConfirmButton: false 
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'Notice',
            text: res.message || 'Verification canceled',
            confirmButtonColor: '#6c757d'
          });
        }
      },
      error: function(xhr) {
        $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
        $('#btn-cancel-emcf').html('<i class="ti ti-x"></i> Cancel');
        
        $("#emcf-modal").modal('hide');
        
        console.error('Cancel error:', xhr);
      }
    });
  });

    $('#emcf-modal').on('hidden.bs.modal', function () {
    $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
    $('#btn-confirm-emcf').html('<i class="ti ti-check"></i> Confirm DGI Verification');
    $('#btn-cancel-emcf').html('<i class="ti ti-x"></i> Cancel');
    $("#emcf-invoice-uid").val('');
    $("#emcf-invoice-id").val('');
    $("#total-badge").text('0.00');
    $("#dgi-total-badge").text('0.00');
  });

  loadInvoices();
  initDataTable();
  updateSummaryTotals(0, 0, 0);
});
</script>