<?php
// app/views/invoices/importinvoice.php
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
body { font-size: 0.8rem; }
.form-label { font-size: 0.7rem; font-weight: 600; margin-bottom: 0.25rem; color: #2c3e50; }
.form-control, .form-select { font-size: 0.75rem; padding: 0.3rem 0.45rem; height: auto; }
.mb-2 { margin-bottom: 0.5rem !important; }

.stats-card {
  border: none;
  border-radius: 12px;
  transition: all 0.3s ease;
  overflow: hidden;
  cursor: pointer;
  background: white;
  border: 2px solid transparent;
  position: relative;
}

.stats-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
.stats-card.active-filter { border-color: #667eea; box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3); transform: scale(1.02); }
.stats-card .card-body { padding: 13px; position: relative; }

.stats-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.stats-card-icon i { font-size: 18px; color: white; }
.icon-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.icon-green { background: linear-gradient(135deg, #2ECC71 0%, #27AE60 100%); }
.icon-orange { background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%); }
.icon-maroon { background: linear-gradient(135deg, #800000 0%, #A52A2A 100%); }
.icon-blue { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }

.stats-value { font-size: 1.5rem; font-weight: 700; color: #2C3E50; margin-bottom: 3px; line-height: 1; }
.stats-label { font-size: 0.65rem; color: #7F8C8D; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

.filter-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #667eea;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: none;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: bold;
}

.stats-card.active-filter .filter-indicator { display: flex; }

/* ========================================
   ENHANCED CLIENT GROUP CARDS
   ======================================== */

.client-group-card {
  border: 2px solid #e9ecef;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 10px;
  background: white;
  transition: all 0.2s;
}

.client-group-card:hover {
  box-shadow: 0 3px 10px rgba(52, 152, 219, 0.15);
}

.client-group-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 8px;
  padding: 8px;
  margin: -8px;
}

.client-group-header:hover {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
  transform: translateX(5px);
}

.client-group-header:active {
  transform: scale(0.98);
}

.client-name-badge {
  font-weight: 700;
  color: #2c3e50;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
}

.client-count-badge {
  background: #3498db;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.client-group-header:hover .client-count-badge {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.toggle-icon {
  font-size: 1.2rem;
  color: #3498db;
  transition: transform 0.3s ease;
  margin-left: 10px;
}

.invoice-files-container {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 2px solid #e9ecef;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
}

.invoice-files-container.show {
  max-height: 2000px;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.invoice-item-card {
  border: 1px solid #e9ecef;
  border-radius: 6px;
  padding: 8px 10px;
  margin-bottom: 6px;
  background: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.invoice-file-number {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.8rem;
}

.invoice-details {
  font-size: 0.65rem;
  color: #7f8c8d;
  margin-top: 3px;
}

.invoice-detail-item {
  display: inline-block;
  margin-right: 10px;
}

.invoice-detail-item i {
  color: #3498db;
  margin-right: 2px;
}

.empty-state {
  text-align: center;
  padding: 35px 18px;
  color: #7f8c8d;
}

.empty-state i {
  font-size: 42px;
  color: #bdc3c7;
  margin-bottom: 12px;
}

/* ========================================
   END CLIENT GROUP CARDS
   ======================================== */

.license-dropdown-wrapper, .mca-dropdown-wrapper {
  position: relative;
}

.license-dropdown-btn, .mca-dropdown-btn {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.4rem 0.6rem;
  font-size: 0.75rem;
  border: 2px solid #e9ecef;
  background: white;
  transition: all 0.3s;
}

.license-dropdown-btn.goods-type-3 {
  border-color: #9b59b6;
  background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
}

.license-dropdown-btn:hover, .license-dropdown-btn:focus,
.mca-dropdown-btn:hover, .mca-dropdown-btn:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.license-dropdown-menu, .mca-dropdown-menu {
  max-height: 320px;
  overflow-y: auto;
  padding: 0.4rem;
  border: 2px solid #667eea;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  width: 100% !important;
}

.license-dropdown-item, .mca-dropdown-item {
  padding: 0;
  margin-bottom: 0.2rem;
}

.license-checkbox-wrapper, .mca-checkbox-wrapper {
  display: flex;
  align-items: center;
  padding: 0.4rem 0.65rem;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
  border: 1px solid #e9ecef;
  background: white;
}

.license-checkbox-wrapper:hover, .mca-checkbox-wrapper:hover {
  background: #e3f2fd;
  border-color: #667eea;
}

.license-checkbox-wrapper input[type="checkbox"],
.mca-checkbox-wrapper input[type="checkbox"] {
  width: 16px;
  height: 16px;
  margin-right: 8px;
  cursor: pointer;
  flex-shrink: 0;
}

.license-checkbox-label, .mca-checkbox-label {
  flex: 1;
  cursor: pointer;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.license-ref-text, .mca-ref-text {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.75rem;
}

.license-details, .mca-details-text {
  font-size: 0.65rem;
  color: #7f8c8d;
}

.quittance-count-badge {
  background: #28a745;
  color: white;
  padding: 2px 7px;
  border-radius: 12px;
  font-size: 0.6rem;
  font-weight: 600;
  margin-left: 6px;
}

.license-selection-summary, .mca-selection-summary {
  margin-top: 6px;
  padding: 6px 10px;
  background: #d1ecf1;
  border: 1px solid #bee5eb;
  border-radius: 6px;
  font-size: 0.7rem;
  color: #0c5460;
  display: none;
}

.license-selection-summary strong, .mca-selection-summary strong {
  color: #004085;
}

.select-all-mca-btn {
  background: #667eea;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  margin-bottom: 8px;
  transition: all 0.2s;
}

.select-all-mca-btn:hover {
  background: #5568d3;
}

.select-all-mca-btn i {
  margin-right: 4px;
}

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

.btn-export-all {
  background: #F39C12 !important;
  border: none !important;
  color: white !important;
  font-weight: 600 !important;
  padding: 0.4rem 1rem !important;
  font-size: 0.75rem !important;
  border-radius: 0.375rem !important;
  cursor: pointer !important;
  display: flex !important;
  align-items: center !important;
  gap: 0.4rem !important;
  transition: all 0.3s !important;
}

.btn-export-all:hover {
  background: #E67E22 !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
}

.btn-export-validated {
  background: #007bff !important;
  border: none !important;
  color: white !important;
  font-weight: 600 !important;
  padding: 0.4rem 1rem !important;
  font-size: 0.75rem !important;
  border-radius: 0.375rem !important;
  cursor: pointer !important;
  display: flex !important;
  align-items: center !important;
  gap: 0.4rem !important;
  transition: all 0.3s !important;
}

.btn-export-validated:hover {
  background: #0056b3 !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.text-danger { color: #dc3545; font-weight: bold; }
.is-invalid { border-color: #dc3545 !important; }
.invalid-feedback { display: block; color: #dc3545; font-size: 0.65rem; margin-top: 0.15rem; }
.readonly-field { background-color: #e9ecef; cursor: not-allowed; }
.calculated-field { background-color: #d1ecf1; cursor: not-allowed; font-weight: 600; }
.auto-generated-field { background-color: #f8f9fa; cursor: not-allowed; }
.hidden-field { display: none !important; }

.invoice-layout { display: flex; gap: 18px; margin-top: 12px; }
.invoice-left-panel { flex: 0 0 25%; max-width: 25%; }
.invoice-right-panel { flex: 0 0 75%; max-width: 75%; }

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

.section-header {
  background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
  color: white;
  padding: 4px 8px;
  font-weight: 600;
  font-size: 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.financial-table { width: 100%; margin-bottom: 8px; }

.financial-table td {
  padding: 2px 6px;
  border-bottom: 1px solid #ecf0f1;
  font-size: 0.6rem;
}

.financial-table td:first-child {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: white;
  font-weight: 600;
  font-size: 0.55rem;
  text-transform: uppercase;
  width: 120px;
  border-radius: 4px;
}

.financial-table td:last-child { padding-left: 8px; }

.financial-table input,
.financial-table select {
  width: 100%;
  padding: 2px 6px;
  border: 1px solid #bdc3c7;
  border-radius: 4px;
  font-size: 0.6rem;
}

.financial-table .input-group { display: flex; gap: 3px; }
.financial-table .input-group select { width: 55px; flex-shrink: 0; }
.financial-table .input-group input { flex: 1; }

#quotationSelector {
  width: 100%;
  padding: 6px 10px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  font-size: 0.75rem;
  transition: all 0.3s;
  background: white;
}

#quotationSelector:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

#quotationSelector option { padding: 6px; }

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

.validation-badge {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.65rem;
  font-weight: 600;
  display: inline-block;
}

.validation-not-validated {
  background: linear-gradient(135deg, #fee 0%, #fcc 100%);
  color: #c00;
  border: 1px solid #fcc;
}

.validation-validated {
  background: linear-gradient(135deg, #cfe2ff 0%, #9ec5fe 100%);
  color: #084298;
  border: 1px solid #9ec5fe;
}

.validation-dgi-verified {
  background: linear-gradient(135deg, #d1e7dd 0%, #a3cfbb 100%);
  color: #0f5132;
  border: 1px solid #a3cfbb;
  font-weight: 700;
}

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

.btn-pdf-p1 {
  background: #ff6b6b !important;
  color: white !important;
  border: none !important;
}

.btn-pdf-p1:hover {
  background: #ee5a52 !important;
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
}

.btn-pdf-p2 {
  background: #4ecdc4 !important;
  color: white !important;
  border: none !important;
}

.btn-pdf-p2:hover {
  background: #45b7af !important;
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(78, 205, 196, 0.3);
}

.btn-validate {
  background: #17a2b8 !important;
  color: white !important;
  border: none !important;
}

.btn-validate:hover {
  background: #138496 !important;
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.btn-dgi {
  background: #800000 !important;
  color: white !important;
  border: none !important;
}

.btn-dgi:hover {
  background: #600000 !important;
  color: white !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(128, 0, 0, 0.3);
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

.quotation-panel {
  background: white;
  border: 1px solid #e9ecef;
  border-top: none;
  border-radius: 0 0 8px 8px;
  padding: 10px;
}

#quotationItemsContainer { margin-bottom: 12px; }

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

.category-totals-row {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  border: 2px solid #2196f3;
  border-radius: 6px;
  padding: 10px 13px;
  margin: 8px 0;
  box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
}

.category-totals-row table {
  width: 100%;
  margin: 0;
}

.category-totals-row td {
  padding: 4px 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #1976d2;
}

.category-totals-row td:first-child {
  text-align: left;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.category-totals-row td:last-child {
  text-align: right;
  font-size: 0.9rem;
  color: #0d47a1;
}

.category-toggle-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.6rem;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 3px;
}

.category-toggle-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.6);
}

.category-toggle-btn i {
  font-size: 0.75rem;
  transition: transform 0.3s;
}

.category-toggle-btn.collapsed i { transform: rotate(-180deg); }

.category-items-display {
  background: white;
  border: 1px solid #dee2e6;
  border-top: none;
  border-radius: 0 0 6px 6px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.3s;
}

.category-items-display.collapsed { display: none; }

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

.quotation-items-table .item-input {
  width: 100%;
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.65rem;
  font-family: 'Courier New', monospace;
  transition: all 0.2s;
}

.quotation-items-table .item-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

@media (max-width: 1400px) {
  .stats-card .card-body { padding: 11px; }
  .stats-value { font-size: 1.4rem; }

  .emcf-stats-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  #emcf-modal .modal-dialog {
    margin: 0.5rem;
  }
  
  #emcf-modal .modal-footer {
    flex-direction: column;
  }
  
  #emcf-modal .modal-footer .btn {
    width: 100%;
  }
}

@media (max-width: 992px) {
  .invoice-layout { flex-direction: column; }
  .invoice-left-panel { flex: 1; max-width: 100%; }
  .invoice-right-panel { flex: 1; max-width: 100%; }
  .datatable-header { flex-direction: column; gap: 8px; align-items: flex-start; }
  .datatable-actions { width: 100%; flex-direction: column; }
  .custom-search-box { width: 100%; }
  .btn-export-all, .btn-export-validated { width: 100%; justify-content: center !important; }
}

@media (max-width: 768px) {
  .stats-card .card-body { padding: 10px; }
  .stats-value { font-size: 1.3rem; }
  .stats-label { font-size: 0.6rem; }
  .quotation-items-table { font-size: 0.6rem; }
  .quotation-items-table thead th { padding: 6px 4px; font-size: 0.55rem; }
  .quotation-items-table tbody td { padding: 6px 4px; }
}
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <div class="row mb-4">
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card stats-card-clickable" data-card-type="pending-invoicing" style="border: 2px solid #3498db;">
              <div class="card-body">
                <div class="stats-card-icon icon-blue">
                  <i class="ti ti-clock-hour-4"></i>
                </div>
                <div class="stats-value" id="totalPendingInvoicing">0</div>
                <div class="stats-label">Pending for Invoicing</div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card stats-card-clickable" data-filter="validated" data-card-type="validated">
              <div class="card-body">
                <div class="stats-card-icon icon-green">
                  <i class="ti ti-circle-check"></i>
                </div>
                <div class="stats-value" id="totalValidated">0</div>
                <div class="stats-label">Validated</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card stats-card-clickable" data-filter="not-validated" data-card-type="not-validated">
              <div class="card-body">
                <div class="stats-card-icon icon-orange">
                  <i class="ti ti-alert-triangle"></i>
                </div>
                <div class="stats-value" id="totalNotValidated">0</div>
                <div class="stats-label">Not Validated</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card stats-card-clickable" data-filter="dgi-verified" data-card-type="dgi-verified">
              <div class="card-body">
                <div class="stats-card-icon icon-maroon">
                  <i class="ti ti-file-check"></i>
                </div>
                <div class="stats-value" id="totalDGIVerified">0</div>
                <div class="stats-label">DGI Verified</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card stats-card-clickable" data-filter="all" data-card-type="all">
              <div class="card-body">
                <div class="stats-card-icon icon-purple">
                  <i class="ti ti-file-invoice"></i>
                </div>
                <div class="stats-value" id="totalInvoices">0</div>
                <div class="stats-label">Total Invoices</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion mb-4" id="invoiceAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingInvoice">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvoice" aria-expanded="false" aria-controls="collapseInvoice">
                <div class="accordion-title-section">
                  <i class="ti ti-file-invoice"></i>
                  <span id="formTitle">Add New Import Invoice</span>
                </div>
              </button>
            </h2>
            <div id="collapseInvoice" class="accordion-collapse collapse" aria-labelledby="headingInvoice" data-bs-parent="#invoiceAccordion">
              <div class="accordion-body">
                
                <form id="invoiceForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="invoice_id" id="invoice_id" value="">
                  <input type="hidden" name="action" id="formAction" value="insert">
                  <input type="hidden" name="kind_id" id="kind_id" value="">
                  <input type="hidden" name="goods_type_id" id="goods_type_id" value="">
                  <input type="hidden" name="transport_mode_id" id="transport_mode_id" value="">
                  <input type="hidden" name="quotation_id" id="quotation_id" value="">
                  <input type="hidden" name="quotation_items" id="quotation_items" value="">
                  <input type="hidden" name="hidden_categories" id="hidden_categories" value="[]">
                  <input type="hidden" name="bank_exchange_rate_id" id="bank_exchange_rate_id" value="">
                  <input type="hidden" name="liquidation_paid_by" id="liquidation_paid_by" value="">
                  <input type="hidden" name="cif_cdf" id="cif_cdf" value="0.00">
                  <input type="hidden" name="calculated_sub_total" id="calculated_sub_total" value="0.00">
                  <input type="hidden" name="calculated_vat_amount" id="calculated_vat_amount" value="0.00">
                  <input type="hidden" name="calculated_total_amount" id="calculated_total_amount" value="0.00">
                  <input type="hidden" name="calculated_total_cdf" id="calculated_total_cdf" value="0.00">
                  <input type="hidden" name="items_manually_edited" id="items_manually_edited" value="0">
                  <input type="hidden" name="first_categoty_edited" id="first_categoty_edited" value="H">
                  <input type="hidden" name="license_ids" id="license_ids" value="">
                  <input type="hidden" name="mca_ids" id="mca_ids" value="">

                  <div class="row mb-2">
                    <div class="col-md-2 mb-2">
                      <label class="form-label">Client <span class="text-danger">*</span></label>
                      <select name="client_id" id="client_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($clients as $client): ?>
                          <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['short_name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="invalid-feedback" id="client_id_error"></div>
                    </div>

                    <div class="col-md-3 mb-2">
                      <label class="form-label">License Numbers <span class="text-danger">*</span></label>
                      <div class="dropdown license-dropdown-wrapper">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start license-dropdown-btn" type="button" id="licenseDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                          <span id="licenseDropdownText">Select Client First</span>
                        </button>
                        <ul class="dropdown-menu license-dropdown-menu" aria-labelledby="licenseDropdownButton" id="licenseDropdownMenu">
                          <li class="dropdown-item-text text-center text-muted py-3">
                            <i class="ti ti-info-circle me-1"></i> Select Client First
                          </li>
                        </ul>
                      </div>
                      <div class="invalid-feedback" id="license_ids_error"></div>
                    </div>

                    <div class="col-md-3 mb-2">
                      <label class="form-label">MCA References <span class="text-danger">*</span></label>
                      <div class="dropdown mca-dropdown-wrapper">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start mca-dropdown-btn" type="button" id="mcaDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                          <span id="mcaDropdownText">Select License First</span>
                        </button>
                        <ul class="dropdown-menu mca-dropdown-menu" aria-labelledby="mcaDropdownButton" id="mcaDropdownMenu">
                          <li class="dropdown-item-text text-center text-muted py-3">
                            <i class="ti ti-info-circle me-1"></i> Select License First
                          </li>
                        </ul>
                      </div>
                      <div class="invalid-feedback" id="mca_ids_error"></div>
                    </div>

                    <div class="col-md-2 mb-2">
                      <label class="form-label">Invoice Ref <span class="text-danger">*</span></label>
                      <input type="text" name="invoice_ref" id="invoice_ref" class="form-control" required maxlength="100" placeholder="Select Client First">
                      <div class="invalid-feedback" id="invoice_ref_error"></div>
                    </div>

                    <div class="col-md-2 mb-2">
                      <label class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                      <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="ESPECE">ESPECE</option>
                        <option value="MOBILE MONEY">MOBILE MONEY</option>
                        <option value="VIREMENT">VIREMENT</option>
                        <option value="CARTE BANCAIRE">CARTE BANCAIRE</option>
                        <option value="CHEQUES">CHEQUES</option>
                        <option value="CREDIT" selected>CREDIT</option>
                        <option value="AUTRE">AUTRE</option>
                      </select>
                      <div class="invalid-feedback" id="payment_method_error"></div>
                    </div>
                  </div>

                  <div class="row mb-2">
                    <div class="col-md-2 mb-2">
                      <label class="form-label">ARSP:</label>
                      <input type="text" name="arsp" id="arsp" class="form-control" value="Disabled" placeholder="Disabled">
                    </div>

                    <div class="col-md-2 mb-2">
                      <label class="form-label">Tally Ref:</label>
                      <input type="text" name="tally_ref" id="tally_ref" class="form-control auto-generated-field" readonly placeholder="Auto-generated">
                    </div>

                    <div class="col-md-2 mb-2">
                      <label class="form-label">Kind <span class="text-danger">*</span></label>
                      <input type="text" name="kind" id="kind" class="form-control readonly-field" readonly placeholder="From MCA">
                    </div>

                    <div class="col-md-3 mb-2">
                      <label class="form-label">Type of Goods <span class="text-danger">*</span></label>
                      <input type="text" name="type_of_goods" id="type_of_goods" class="form-control readonly-field" readonly placeholder="From MCA">
                    </div>

                    <div class="col-md-3 mb-2">
                      <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                      <input type="text" name="transport_mode" id="transport_mode" class="form-control readonly-field" readonly placeholder="From MCA">
                    </div>

                    <div class="col-md-2 mb-2">
                      <label class="form-label">Invoice Template:</label>
                      <select name="invoice_template" id="invoice_template" required class="form-select">
                        <option value="">Select Template</option>
                        <option value="I">Include</option>
                        <option value="E">Exclude</option>
                      </select>
                    </div>
                  </div>

                  <div class="invoice-layout">
                    
                    <div class="invoice-left-panel">
                      <div class="panel-header">
                        <i class="ti ti-receipt"></i> INVOICE DETAILS
                      </div>
                      <div class="panel-body">
                        
                        <div class="section-header">FINANCIAL INFO</div>
                        
                        <table class="financial-table">
                          <tr>
                            <td>FOB</td>
                            <td>
                              <div class="input-group">
                                <select name="fob_currency_id" id="fob_currency_id">
                                  <option value="">USD</option>
                                  <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= $curr['id'] ?>"><?= htmlspecialchars($curr['currency_short_name']) ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <input type="number" step="0.01" name="fob_usd" id="fob_usd" class="financial-calc-trigger" placeholder="0.00" min="0">
                              </div>
                            </td>
                          </tr>
                          
                          <tr>
                            <td>FRET</td>
                            <td>
                              <div class="input-group">
                                <select name="fret_currency_id" id="fret_currency_id">
                                  <option value="">USD</option>
                                  <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= $curr['id'] ?>"><?= htmlspecialchars($curr['currency_short_name']) ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <input type="number" step="0.01" name="fret_usd" id="fret_usd" class="financial-calc-trigger" placeholder="0.00" min="0">
                              </div>
                            </td>
                          </tr>
                          
                          <tr>
                            <td>ASSURANCE</td>
                            <td>
                              <div class="input-group">
                                <select name="assurance_currency_id" id="assurance_currency_id">
                                  <option value="">USD</option>
                                  <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= $curr['id'] ?>"><?= htmlspecialchars($curr['currency_short_name']) ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <input type="number" step="0.01" name="assurance_usd" id="assurance_usd" class="financial-calc-trigger" placeholder="0.00" min="0">
                              </div>
                            </td>
                          </tr>
                          
                          <tr>
                            <td>AUTRES CHG</td>
                            <td>
                              <div class="input-group">
                                <select name="autres_charges_currency_id" id="autres_charges_currency_id">
                                  <option value="">USD</option>
                                  <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= $curr['id'] ?>"><?= htmlspecialchars($curr['currency_short_name']) ?></option>
                                  <?php endforeach; ?>
                                </select>
                                <input type="number" step="0.01" name="autres_charges_usd" id="autres_charges_usd" class="financial-calc-trigger" placeholder="0.00" min="0">
                              </div>
                            </td>
                          </tr>
                          
                          <tr>
                            <td>RATE CDF/INV</td>
                            <td>
                              <input type="number" step="0.0001" name="rate_cdf_inv" id="rate_cdf_inv" class="financial-calc-trigger" placeholder="0.0000" min="0">
                            </td>
                          </tr>
                          
                          <tr>
                            <td>RATE CDF/BCC</td>
                            <td>
                              <input type="number" step="0.0001" name="rate_cdf_usd_bcc" id="rate_cdf_usd_bcc" class="financial-calc-trigger" placeholder="0.0000" min="0">
                            </td>
                          </tr>
                          
                          <tr>
                            <td>CIF USD</td>
                            <td>
                              <input type="number" step="0.01" name="cif_usd" id="cif_usd" class="calculated-field" readonly placeholder="0.00">
                            </td>
                          </tr>

                          <tr>
                            <td>CIF CDF</td>
                            <td>
                              <input type="number" step="0.01" name="cif_cdf_display" id="cif_cdf_display" class="calculated-field" readonly placeholder="0.00">
                            </td>
                          </tr>
                          
                          <tr>
                            <td>DUTY CDF</td>
                            <td>
                              <input type="number" step="0.01" name="total_duty_cdf" id="total_duty_cdf" placeholder="0">
                            </td>
                          </tr>
                          
                          <tr>
                            <td>POIDS (KG)</td>
                            <td>
                              <input type="number" step="0.01" name="poids_kg" id="poids_kg" placeholder="0.00" min="0">
                            </td>
                          </tr>
                          
                          <tr>
                            <td>TARIFF CODE</td>
                            <td>
                              <input type="text" name="tariff_code_client" id="tariff_code_client" maxlength="100">
                            </td>
                          </tr>
                        </table>
                        
                        <div id="roadTransportSection" class="hidden-field">
                          <div class="section-header" style="margin-top: 6px;">ROAD TRANSPORT</div>
                          
                          <table class="financial-table">
                            <tr><td>HORSE</td><td><input type="text" name="horse" id="horse" maxlength="100"></td></tr>
                            <tr><td>TRAILER 1</td><td><input type="text" name="trailer_1" id="trailer_1" maxlength="100"></td></tr>
                            <tr><td>TRAILER 2</td><td><input type="text" name="trailer_2" id="trailer_2" maxlength="100"></td></tr>
                            <tr><td>CONTAINER</td><td><input type="text" name="container" id="container" maxlength="100"></td></tr>
                          </table>
                        </div>
                        
                        <div id="railTransportSection" class="hidden-field">
                          <div class="section-header" style="margin-top: 6px;">WAGON TRANSPORT</div>
                          
                          <table class="financial-table">
                            <tr><td>WAGON</td><td><input type="text" name="wagon" id="wagon" maxlength="100"></td></tr>
                            <tr><td>HORSE</td><td><input type="text" name="horse_rail" id="horse_rail" maxlength="100"></td></tr>
                            <tr><td>TRAILER 1</td><td><input type="text" name="trailer_1_rail" id="trailer_1_rail" maxlength="100"></td></tr>
                            <tr><td>TRAILER 2</td><td><input type="text" name="trailer_2_rail" id="trailer_2_rail" maxlength="100"></td></tr>
                            <tr><td>CONTAINER</td><td><input type="text" name="container_rail" id="container_rail" maxlength="100"></td></tr>
                          </table>
                        </div>
                        
                        <div id="airTransportSection" class="hidden-field">
                          <div class="section-header" style="margin-top: 6px;">AIR TRANSPORT</div>
                          
                          <table class="financial-table">
                            <tr><td>AIRWAY BILL</td><td><input type="text" name="airway_bill" id="airway_bill" maxlength="100"></td></tr>
                            <tr><td>AWB WEIGHT</td><td><input type="number" step="0.01" name="airway_bill_weight" id="airway_bill_weight" placeholder="0.00" min="0"></td></tr>
                            <tr><td>CONTAINER</td><td><input type="text" name="container_air" id="container_air" maxlength="100"></td></tr>
                          </table>
                        </div>
                        
                        <div class="section-header" style="margin-top: 6px;">DOCUMENTS</div>
                        
                        <table class="financial-table">
                          <tr><td>FACTURE/PFI</td><td><input type="text" name="facture_pfi_no" id="facture_pfi_no" maxlength="100"></td></tr>
                          <tr><td>PO REF</td><td><input type="text" name="po_ref" id="po_ref" maxlength="100"></td></tr>
                          <tr><td>BIVAC</td><td><input type="text" name="bivac_inspection" id="bivac_inspection" maxlength="100"></td></tr>
                          <tr><td>PRODUIT</td><td><input type="text" name="produit" id="produit" value="Default Commodity" maxlength="255"></td></tr>
                          <tr><td>EXONER CODE</td><td><input type="text" name="exoneration_code" id="exoneration_code" maxlength="100"></td></tr>
                          
                          <tr id="m3Section" class="hidden-field">
                            <td>M3</td>
                            <td><input type="number" step="0.01" name="m3" id="m3" placeholder="0.00" min="0"></td>
                          </tr>
                          
                          <tr><td>DECLAR NO</td><td><input type="text" name="declaration_no" id="declaration_no" maxlength="100"></td></tr>
                          <tr><td>DECLAR DATE</td><td><input type="date" name="declaration_date" id="declaration_date"></td></tr>
                          <tr><td>LIQUID NO</td><td><input type="text" name="liquidation_no" id="liquidation_no" maxlength="100"></td></tr>
                          <tr><td>LIQUID DATE</td><td><input type="date" name="liquidation_date" id="liquidation_date"></td></tr>
                          <tr><td>QUITTANCE NO</td><td><input type="text" name="quittance_no" id="quittance_no" maxlength="100"></td></tr>
                          <tr><td>QUIT DATE</td><td><input type="date" name="quittance_date" id="quittance_date"></td></tr>
                          <tr><td>DISPATCH</td><td><input type="date" name="dispatch_deliver_date" id="dispatch_deliver_date"></td></tr>
                        </table>
                        
                      </div>
                    </div>
                    
                    <div class="invoice-right-panel">
                      <div class="section-header">
                        <span>QUOTATION SELECTION</span>
                      </div>
                      <div class="quotation-panel">
                        
                        <div style="margin-bottom: 12px;">
                          <label class="form-label" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 6px; display: block;">
                            Select Quotation <span class="text-danger">*</span>
                          </label>
                          <select id="quotationSelector" class="form-select" style="padding: 6px 10px; font-size: 0.75rem;">
                            <option value="">-- Select Client First --</option>
                          </select>
                          <div style="margin-top: 3px; font-size: 0.65rem; color: #7f8c8d;">
                            <i class="ti ti-info-circle"></i> Quotations are automatically loaded when you select a Client
                          </div>
                        </div>
                        
                        <div id="quotationItemsContainer" style="min-height: 45px;">
                          <div class="quotation-content">
                            <p style="margin: 0;"><i class="ti ti-info-circle me-1"></i> Select a quotation from the dropdown above</p>
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
                            <tr>
                              <td>Equivalent CDF</td>
                              <td id="equivalentCDF">0.00 FC</td>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </div>
                    
                  </div>

                  <div class="row mt-4">
                    <div class="col-12 text-end">
                      <button type="button" class="btn btn-secondary btn-sm" id="cancelBtn">
                        <i class="ti ti-x me-1"></i> Cancel
                      </button>
                      <button type="submit" class="btn btn-primary btn-sm ms-2" id="submitBtn">
                        <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save Invoice</span>
                      </button>
                    </div>
                  </div>

                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="datatable-header">
            <div class="datatable-title">
              <i class="ti ti-list"></i>
              <span>Import Invoices List</span>
            </div>
            <div class="datatable-actions">
              <div class="custom-search-box">
                <input type="text" id="customSearchBox" placeholder="Search invoices..." autocomplete="off">
                <i class="ti ti-search"></i>
              </div>
              <button type="button" class="btn btn-export-all" onclick="exportAllInvoices();">
                <i class="ti ti-file-spreadsheet"></i> Debit Note
              </button>
              <button type="button" class="btn btn-export-validated" onclick="exportValidatedInvoices();">
                <i class="ti ti-file-check"></i> Invoice
              </button>
            </div>
          </div>
          
          <div class="card-body">
            <div class="table-responsive">
              <table id="invoicesTable" class="table table-striped table-bordered nowrap w-100" style="width: 100%;">
                <thead>
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 150px;">INVOICE REF</th>
                    <th style="width: 120px;">CLIENT</th>
                    <th style="width: 130px;">TYPE OF GOODS</th>
                    <th style="width: 100px;">INVOICE DATE</th>
                    <th style="width: 100px;">CREATED BY</th>
                    <th style="width: 90px;">AMOUNT</th>
                    <th style="width: 110px;">VALIDATION</th>
                    <th style="width: 230px;">ACTIONS</th>
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

<div class="modal fade" id="clientGroupModal" tabindex="-1" aria-labelledby="clientGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" id="modalHeader" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
        <h5 class="modal-title" id="clientGroupModalLabel">
          <i class="ti ti-building me-2"></i><span id="modalTitle">Client-wise View</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="clientGroupModalContent" style="min-height: 280px; max-height: 550px; overflow-y: auto;">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading data...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="exportPendingMCAsBtn" style="display: none;">
          <i class="ti ti-file-spreadsheet me-1"></i> Export to Excel
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Close
        </button>
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

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
var isEditMode = false;
var category1TotalUSD = 0;
var category1TotalCDF = 0;
var pendingMCAsData = [];

$(document).ready(function () {
  let invoicesTable;
  let currentFilter = 'all';
  let quotationItemsData = [];
  let quotationData = null;
  let hiddenCategoryIndices = [];
  let selectedLicenseIds = [];
  let selectedMCAIds = [];

  let baseUrl = '<?= rtrim(APP_URL, "/") ?>';
  const CONTROLLER_URL = baseUrl + '/importinvoice';
  const csrfToken = $('#invoiceForm').data('csrf-token');
  
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#039;'};
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  // ========== GENERATE INVOICE REFERENCE ==========
function generateInvoiceReference() {
  const clientId = $('#client_id').val();
  
  if (isEditMode) return;
  if (!clientId) return;
  
  $.ajax({
    url: CONTROLLER_URL + '/crudData/getNextInvoiceRefForClient',
    method: 'GET',
    data: { 
      client_id: clientId
    },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        $('#invoice_ref').val(res.invoice_ref);
      }
    }
  });
}
  
  function sanitizeNumber(value) {
    const num = parseFloat(value);
    return isNaN(num) || num < 0 ? 0 : num;
  }

  function formatNumber(value, maxDecimals = 4) {
    const num = parseFloat(value);
    if (isNaN(num) || num === 0) return '';
    
    let formatted = num.toFixed(maxDecimals);
    formatted = formatted.replace(/\.?0+$/, '');
    
    return formatted;
  }

  $(document).on('blur', 'input[type="number"]', function() {
    const $input = $(this);
    const value = $input.val();
    
    if (value && !isNaN(value)) {
      const step = $input.attr('step');
      let decimals = 2;
      
      if (step === '0.0001') decimals = 4;
      else if (step === '0.001') decimals = 3;
      else if (step === '0.01') decimals = 2;
      else if (step === '1') decimals = 0;
      
      $input.val(formatNumber(value, decimals));
    }
  });

 // ========== TALLY REF AUTO-GENERATION WITH TRANSPORT & GOODS TYPE ==========
function generateTallyRef(invoiceRef) {
  if (!invoiceRef) {
    $('#tally_ref').val('');
    return '';
  }
  
  // Get current year's last 2 digits
  const currentYear = new Date().getFullYear();
  const yearSuffix = String(currentYear).slice(-2);
  
  // Get transport mode ID and goods type ID
  const transportModeId = parseInt($('#transport_mode_id').val()) || 0;
  const goodsTypeId = parseInt($('#goods_type_id').val()) || 0;
  
  // Map Transport Mode ID to code
  let transportCode = 'XX'; // Default when not selected yet
  if (transportModeId === 1) {
    transportCode = 'RF'; // Road/Route
  } else if (transportModeId === 2) {
    transportCode = 'AW'; // Airway
  } else if (transportModeId === 3) {
    transportCode = 'WA'; // Wagon/Rail
  }
  
  // Map Goods Type ID to code
  let goodsCode = 'X'; // Default when not selected yet
  if (goodsTypeId === 1) {
    goodsCode = 'C';
  } else if (goodsTypeId === 2) {
    goodsCode = 'D';
  } else if (goodsTypeId === 3) {
    goodsCode = 'F';
  }
  
  // Extract parts from Invoice Ref
  // Example: INV-KAM-0006 → parts = ['INV', 'KAM', '0006']
  const parts = invoiceRef.split('-');
  
  let tallyRef = '';
  
  if (parts.length >= 2) {
    // Remove first part (INV) and get client code and number
    const clientCodeAndRest = parts.slice(1); // ['KAM', '0006']
    
    if (clientCodeAndRest.length >= 2) {
      // Get client code and number separately
      const clientCode = clientCodeAndRest[0]; // 'KAM'
      const number = clientCodeAndRest.slice(1).join('-'); // '0006'
      
      // Format: IMP-{TRANSPORT}-{GOODS}-{clientCode}{yearSuffix}-{number}
      tallyRef = `IMP-${transportCode}-${goodsCode}-${clientCode}${yearSuffix}-${number}`;
    } else {
      // If format doesn't match expected pattern
      tallyRef = `IMP-${transportCode}-${goodsCode}-${clientCodeAndRest.join('')}${yearSuffix}`;
    }
  } else {
    // Fallback if format is completely different
    tallyRef = `IMP-${transportCode}-${goodsCode}-${invoiceRef}${yearSuffix}`;
  }
  
  $('#tally_ref').val(tallyRef);
  return tallyRef;
}

// Auto-generate Tally Ref when Invoice Ref changes
$('#invoice_ref').on('input change blur', function() {
  const invoiceRef = $(this).val().trim();
  generateTallyRef(invoiceRef);
});

  // Auto-generate Tally Ref when Invoice Ref changes
  $('#invoice_ref').on('input change blur', function() {
    const invoiceRef = $(this).val().trim();
    generateTallyRef(invoiceRef);
  });

  window.exportAllInvoices = function() {
    window.location.href = CONTROLLER_URL + '/crudData/exportDebit';
    Swal.fire({
      icon: 'success', 
      title: 'Exporting...', 
      text: 'Your Debit Note Excel file will download shortly', 
      timer: 1500, 
      showConfirmButton: false
    });
  };

  window.exportValidatedInvoices = function() {
    window.location.href = CONTROLLER_URL + '/crudData/exportInvoiced';
    Swal.fire({
      icon: 'success', 
      title: 'Exporting...', 
      text: 'Your Invoice Excel file will download shortly', 
      timer: 1500, 
      showConfirmButton: false
    });
  };

  $('.filter-card').on('click', function() {
    $('.filter-card').removeClass('active-filter');
    $(this).addClass('active-filter');
    currentFilter = $(this).data('filter');
    if (invoicesTable) invoicesTable.ajax.reload();
  });

$('.stats-card-clickable').on('click', function() {
  const cardType = $(this).data('card-type');
  
  $('#exportPendingMCAsBtn').hide();
  
  let modalTitle = '';
  let modalColor = '';
  
  if (cardType === 'all') {
    modalTitle = '<i class="ti ti-file-invoice me-2"></i>All Invoices - Client-wise';
    modalColor = 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;';
  } else if (cardType === 'validated') {
    modalTitle = '<i class="ti ti-circle-check me-2"></i>Validated Invoices - Client-wise';
    modalColor = 'background: linear-gradient(135deg, #2ECC71 0%, #27AE60 100%); color: white;';
  } else if (cardType === 'not-validated') {
    modalTitle = '<i class="ti ti-alert-triangle me-2"></i>Not Validated Invoices - Client-wise';
    modalColor = 'background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%); color: white;';
  } else if (cardType === 'dgi-verified') {
    modalTitle = '<i class="ti ti-file-check me-2"></i>DGI Verified Invoices - Client-wise';
    modalColor = 'background: linear-gradient(135deg, #800000 0%, #A52A2A 100%); color: white;';
  } else if (cardType === 'pending-invoicing') {
    modalTitle = '<i class="ti ti-clock-hour-4 me-2"></i>Pending for Invoicing - MCA Files';
    modalColor = 'background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;';
  }
  
  $('#modalTitle').html(modalTitle);
  $('#modalHeader').attr('style', modalColor);
  
  $('#clientGroupModal').modal('show');
  
  if (cardType === 'pending-invoicing') {
    loadPendingMCAs();
  } else {
    loadClientGroupedInvoices(cardType);
  }
});
function loadClientGroupedInvoices(filterType) {
    $('#clientGroupModalContent').html(`
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Loading invoices...</p>
      </div>
    `);
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getClientGroupedInvoices',
      method: 'GET',
      data: { filter: filterType },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          const groupedByClient = {};
          
          res.data.forEach(function(invoice) {
            const clientName = invoice.client_name || 'Unknown Client';
            if (!groupedByClient[clientName]) {
              groupedByClient[clientName] = [];
            }
            groupedByClient[clientName].push(invoice);
          });
          
          let html = '<div class="p-3">';
          html += '<div class="mb-3"><strong>Total: ' + res.data.length + ' invoice(s) from ' + Object.keys(groupedByClient).length + ' client(s)</strong></div>';
          
          Object.keys(groupedByClient).sort().forEach(function(clientName) {
            const invoices = groupedByClient[clientName];
            const clientId = 'client-inv-' + clientName.replace(/[^a-zA-Z0-9]/g, '-');
            
            html += '<div class="client-group-card">';
            html += '<div class="client-group-header" data-client-id="' + clientId + '" style="cursor: pointer;">';
            html += '<div class="client-name-badge">';
            html += '<i class="ti ti-building me-2"></i>' + escapeHtml(clientName);
            html += '</div>';
            html += '<div class="client-count-badge">' + invoices.length + ' invoice(s)</div>';
            html += '</div>';
            
            html += '<div class="invoice-files-container" id="' + clientId + '">';
            
            invoices.forEach(function(invoice, index) {
              const invoiceRef = escapeHtml(invoice.invoice_ref || 'N/A');
              const typeOfGoods = escapeHtml(invoice.type_of_goods || 'N/A');
              const invoiceDate = invoice.created_at ? new Date(invoice.created_at).toLocaleDateString('en-GB') : 'N/A';
              const amount = parseFloat(invoice.calculated_total_amount || invoice.cif_usd || 0);
              const validated = parseInt(invoice.validated || 0);
              
              let validationBadge = '';
              if (validated === 0) {
                validationBadge = '<span class="validation-badge validation-not-validated">NOT VALIDATED</span>';
              } else if (validated === 1) {
                validationBadge = '<span class="validation-badge validation-validated">✓ VALIDATED</span>';
              } else if (validated === 2) {
                validationBadge = '<span class="validation-badge validation-dgi-verified">DGI VERIFIED</span>';
              }
              
              html += '<div class="invoice-item-card">';
              html += '<div>';
              html += '<div class="invoice-file-number">';
              html += '<span class="badge bg-primary me-2" style="font-size: 0.6rem;">' + (index + 1) + '</span>';
              html += invoiceRef;
              html += '</div>';
              html += '<div class="invoice-details">';
              html += '<div class="invoice-detail-item"><i class="ti ti-package"></i> ' + typeOfGoods + '</div>';
              html += '<div class="invoice-detail-item"><i class="ti ti-calendar"></i> ' + invoiceDate + '</div>';
              html += '<div class="invoice-detail-item"><i class="ti ti-currency-dollar"></i> $' + amount.toFixed(2) + '</div>';
              html += '</div>';
              html += '</div>';
              html += '<div>' + validationBadge + '</div>';
              html += '</div>';
            });
            
            html += '</div>';
            html += '</div>';
          });
          
          html += '</div>';
          $('#clientGroupModalContent').html(html);
          
          // ⭐ ADD CLICK EVENT TO TOGGLE INVOICE DETAILS
          $('.client-group-header').on('click', function() {
            const clientId = $(this).data('client-id');
            const $container = $('#' + clientId);
            const $badge = $(this).find('.client-count-badge');
            
            if ($container.hasClass('show')) {
              $container.removeClass('show').slideUp(300);
              $(this).css('background', '');
            } else {
              $container.addClass('show').slideDown(300);
              $(this).css('background', 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)');
            }
          });
          
        } else {
          $('#clientGroupModalContent').html(`
            <div class="empty-state">
              <i class="ti ti-file-off"></i>
              <h5>No Invoices Found</h5>
              <p>No invoices match this filter.</p>
            </div>
          `);
        }
      },
      error: function(xhr, status, error) {
        $('#clientGroupModalContent').html(`
          <div class="empty-state">
            <i class="ti ti-alert-triangle" style="color: #e74c3c;"></i>
            <h5>Error Loading Data</h5>
            <p>Failed to load invoices. Please try again.</p>
          </div>
        `);
      }
    });
  }

 function loadPendingMCAs() {
  $('#clientGroupModalContent').html(`
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading pending MCAs...</p>
    </div>
  `);
  
  $.ajax({
    url: CONTROLLER_URL + '/crudData/getPendingMCAs',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        pendingMCAsData = res.data;
        
        $('#exportPendingMCAsBtn').show();
        
        const groupedByClient = {};
        
        res.data.forEach(function(mca) {
          const clientName = mca.client_name || 'Unknown Client';
          if (!groupedByClient[clientName]) {
            groupedByClient[clientName] = [];
          }
          groupedByClient[clientName].push(mca);
        });
        
        let html = '<div class="p-3">';
        html += '<div class="mb-3"><strong>Total: ' + res.data.length + ' MCA(s) from ' + Object.keys(groupedByClient).length + ' client(s)</strong></div>';
        
        Object.keys(groupedByClient).sort().forEach(function(clientName) {
          const mcas = groupedByClient[clientName];
          const clientId = 'client-mca-' + clientName.replace(/[^a-zA-Z0-9]/g, '-');
          
          html += '<div class="client-group-card">';
          html += '<div class="client-group-header" data-client-id="' + clientId + '" style="cursor: pointer;">';
          html += '<div class="client-name-badge">';
          html += '<i class="ti ti-building me-2"></i>' + escapeHtml(clientName);
          html += '</div>';
          html += '<div class="client-count-badge">' + mcas.length + ' MCA(s)</div>';
          html += '</div>';
          
          html += '<div class="invoice-files-container" id="' + clientId + '">';
          
          mcas.forEach(function(mca, index) {
            const mcaRef = escapeHtml(mca.mca_ref || 'N/A');
            const license = escapeHtml(mca.license_number || 'N/A');
            const quittanceDate = mca.quittance_date ? new Date(mca.quittance_date).toLocaleDateString('en-GB') : 'N/A';
            const fob = parseFloat(mca.fob || 0);
            const weight = parseFloat(mca.weight || 0);
            
            html += '<div class="invoice-item-card">';
            html += '<div>';
            html += '<div class="invoice-file-number">';
            html += '<span class="badge bg-primary me-2" style="font-size: 0.6rem;">' + (index + 1) + '</span>';
            html += mcaRef;
            html += '</div>';
            html += '<div class="invoice-details">';
            html += '<div class="invoice-detail-item"><i class="ti ti-file-certificate"></i> ' + license + '</div>';
            html += '<div class="invoice-detail-item"><i class="ti ti-calendar"></i> ' + quittanceDate + '</div>';
            if (fob > 0) {
              html += '<div class="invoice-detail-item"><i class="ti ti-currency-dollar"></i> $' + fob.toFixed(2) + '</div>';
            }
            if (weight > 0) {
              html += '<div class="invoice-detail-item"><i class="ti ti-weight"></i> ' + weight.toFixed(2) + ' kg</div>';
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';
          });
          
          html += '</div>';
          html += '</div>';
        });
        
        html += '</div>';
        $('#clientGroupModalContent').html(html);
        
        $('.client-group-header').on('click', function() {
          const clientId = $(this).data('client-id');
          $('#' + clientId).toggleClass('show');
        });
        
      } else {
        $('#exportPendingMCAsBtn').hide();
        
        $('#clientGroupModalContent').html(`
          <div class="empty-state">
            <i class="ti ti-check-circle"></i>
            <h5>All Clear!</h5>
            <p>No MCAs are currently pending for invoicing.</p>
          </div>
        `);
      }
    },
    error: function(xhr, status, error) {
      $('#exportPendingMCAsBtn').hide();
      
      $('#clientGroupModalContent').html(`
        <div class="empty-state">
          <i class="ti ti-alert-triangle" style="color: #e74c3c;"></i>
          <h5>Error Loading Data</h5>
          <p>Failed to load pending MCAs. Please try again.</p>
        </div>
      `);
    }
  });
}

$('#exportPendingMCAsBtn').on('click', function() {
  if (!pendingMCAsData || pendingMCAsData.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'No Data',
      text: 'No pending MCAs to export'
    });
    return;
  }
  
  const $btn = $(this);
  const originalHtml = $btn.html();
  $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Exporting...');
  
  $.ajax({
    url: CONTROLLER_URL + '/crudData/exportPendingMCAs',
    method: 'POST',
    data: {
      csrf_token: csrfToken,
      data: JSON.stringify(pendingMCAsData)
    },
    xhrFields: {
      responseType: 'blob'
    },
    success: function(blob, status, xhr) {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      
      const disposition = xhr.getResponseHeader('Content-Disposition');
      let filename = 'Pending_MCAs_' + new Date().toISOString().slice(0,10) + '.xlsx';
      
      if (disposition && disposition.indexOf('filename=') !== -1) {
        const matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (matches != null && matches[1]) {
          filename = matches[1].replace(/['"]/g, '');
        }
      }
      
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
      
      $btn.prop('disabled', false).html(originalHtml);
      
      Swal.fire({
        icon: 'success',
        title: 'Exported!',
        text: 'Pending MCAs exported successfully',
        timer: 1500,
        showConfirmButton: false
      });
    },
    error: function(xhr, status, error) {
      $btn.prop('disabled', false).html(originalHtml);
      
      Swal.fire({
        icon: 'error',
        title: 'Export Failed',
        text: 'Failed to export pending MCAs. Please try again.'
      });
    }
  });
});

 $('#client_id').on('change', function() {
  const clientId = $(this).val();
  
  if (!isEditMode) {
    $('#license_id').html('<option value="">-- Select License --</option>');
    $('#quotationSelector').html('<option value="">-- Select Client First --</option>');
    $('#quotationRefDisplay').val('Select MCA to auto-load quotation');
    $('#invoice_ref').val('').attr('placeholder', 'Auto-generated');
    $('#commonBccRate').val('');
    clearMCATable();
    clearQuotationItems();
    allQuotationsData = [];
    updateMCACountBadge();
    
    // Auto-generate invoice reference when client is selected
    if (clientId) {
      setTimeout(function() {
        generateInvoiceReference();
      }, 200);
    }
  }
  
  if (!clientId) return;
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getClientDetails',
      method: 'GET',
      data: { client_id: clientId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          if (res.data.invoice_template) {
            $('#invoice_template').val(res.data.invoice_template);
            $('#liquidation_paid_by').val(res.data.liquidation_paid_by);
          }
        }
      }
    });
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getNextInvoiceRefForClient',
      method: 'GET',
      data: { client_id: clientId },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          $('#invoice_ref').val(res.invoice_ref).attr('placeholder', res.invoice_ref);
          
          // Auto-generate Tally Ref
          generateTallyRef(res.invoice_ref);
        }
      }
    });

    $.ajax({
      url: CONTROLLER_URL + '/crudData/getLicenses',
      method: 'GET',
      data: { client_id: clientId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          displayLicenseDropdown(res.data);
        } else {
          $('#licenseDropdownMenu').html('<li class="dropdown-item-text text-center text-warning py-3"><i class="ti ti-alert-triangle me-1"></i> No licenses found</li>');
          $('#licenseDropdownText').text('No Licenses Available');
        }
      }
    });
  });

  function displayLicenseDropdown(licenseList) {
    let html = '';
    
    licenseList.forEach(function(license) {
      const licenseId = license.id;
      const licenseNumber = escapeHtml(license.license_number || 'N/A');
      const kindName = escapeHtml(license.kind_name || '');
      const quittanceCount = parseInt(license.quittance_count || 0);
      const goodsTypeId = parseInt(license.goods_type_id || 0);
      
      const isChecked = selectedLicenseIds.includes(licenseId) ? 'checked' : '';
      
      let licenseStyle = '';
      if (goodsTypeId === 3) {
        licenseStyle = 'border: 2px solid #9b59b6; background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);';
      }
      
      html += '<li class="dropdown-item license-dropdown-item">';
      html += '  <div class="license-checkbox-wrapper" data-license-id="' + licenseId + '" style="' + licenseStyle + '">';
      html += '    <input type="checkbox" class="license-checkbox" value="' + licenseId + '" id="license_' + licenseId + '" ' + isChecked + '>';
      html += '    <label class="license-checkbox-label" for="license_' + licenseId + '">';
      html += '      <span class="license-ref-text">' + licenseNumber;
      
      if (quittanceCount > 0) {
        html += ' <span class="quittance-count-badge">' + quittanceCount + ' files</span>';
      }
      
      html += '      </span>';
      html += '      <span class="license-details">' + kindName + '</span>';
      html += '    </label>';
      html += '  </div>';
      html += '</li>';
    });
    
    $('#licenseDropdownMenu').html(html);
    updateLicenseDropdownText();
    
    $('.license-checkbox').on('change', function(e) {
      e.stopPropagation();
      handleLicenseCheckboxChange();
    });
    
    $('.license-checkbox-wrapper').on('click', function(e) {
      if (!$(e.target).is('input[type="checkbox"]')) {
        const checkbox = $(this).find('.license-checkbox');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
      }
    });
  }

  function handleLicenseCheckboxChange() {
    selectedLicenseIds = [];
    
    $('.license-checkbox:checked').each(function() {
      selectedLicenseIds.push(parseInt($(this).val()));
    });
    
    $('#license_ids').val(selectedLicenseIds.join(','));
    updateLicenseDropdownText();
    
    clearMCADropdown();
    clearMCAFields();
    $('#quotationSelector').html('<option value="">-- Select MCA First --</option>');
    clearQuotationItems();
    
    if (selectedLicenseIds.length > 0) {
      loadMCAsForLicenses(selectedLicenseIds);
    }
  }

  function updateLicenseDropdownText() {
    const count = selectedLicenseIds.length;
    
    if (count === 0) {
      $('#licenseDropdownText').text('Select Licenses');
      $('#licenseDropdownButton').removeClass('goods-type-3');
    } else if (count === 1) {
      const selectedCheckbox = $('.license-checkbox:checked').first();
      const licenseNumber = selectedCheckbox.closest('.license-checkbox-wrapper').find('.license-ref-text').text();
      $('#licenseDropdownText').text(licenseNumber);
      
      const selectedWrapper = selectedCheckbox.closest('.license-checkbox-wrapper');
      if (selectedWrapper.css('border-color') === 'rgb(155, 89, 182)') {
        $('#licenseDropdownButton').addClass('goods-type-3');
      } else {
        $('#licenseDropdownButton').removeClass('goods-type-3');
      }
    } else {
      $('#licenseDropdownText').text(count + ' Licenses Selected');
      $('#licenseDropdownButton').removeClass('goods-type-3');
    }
  }

  function clearLicenseDropdown() {
    selectedLicenseIds = [];
    $('#license_ids').val('');
    $('#licenseDropdownMenu').html('<li class="dropdown-item-text text-center text-muted py-3"><i class="ti ti-info-circle me-1"></i> Select Client First</li>');
    $('#licenseDropdownText').text('Select Client First');
    $('#licenseDropdownButton').removeClass('goods-type-3');
  }

  function loadMCAsForLicenses(licenseIds) {
    if (!licenseIds || licenseIds.length === 0) {
      clearMCADropdown();
      return;
    }

    const clientId = $('#client_id').val();
    if (!clientId) {
      return;
    }

    const currentInvoiceId = $('#invoice_id').val() || 0;
    
    $('#mcaDropdownMenu').html('<li class="dropdown-item-text text-center text-info py-3"><i class="spinner-border spinner-border-sm me-2"></i> Loading MCAs...</li>');
    $('#mcaDropdownText').text('Loading...');

    $.ajax({
      url: CONTROLLER_URL + '/crudData/getMCAReferences',
      method: 'GET',
      data: { 
        client_id: clientId, 
        license_ids: licenseIds.join(','),
        current_invoice_id: currentInvoiceId
      },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          displayMCADropdown(res.data);
        } else {
          $('#mcaDropdownMenu').html('<li class="dropdown-item-text text-center text-warning py-3"><i class="ti ti-alert-triangle me-1"></i> No available MCA references found</li>');
          $('#mcaDropdownText').text('No MCAs Available');
        }
      },
      error: function(xhr, status, error) {
        $('#mcaDropdownMenu').html('<li class="dropdown-item-text text-center text-danger py-3"><i class="ti ti-x-circle me-1"></i> Error loading MCAs</li>');
        $('#mcaDropdownText').text('Error Loading MCAs');
      }
    });
  }

  function displayMCADropdown(mcaList) {
    let html = '';
    
    html += '<li style="padding: 0 0.4rem;">';
    html += '<button type="button" class="select-all-mca-btn" id="selectAllMCABtn">';
    html += '<i class="ti ti-checkbox"></i> Select All MCAs';
    html += '</button>';
    html += '</li>';
    
    mcaList.forEach(function(mca) {
      const mcaId = mca.id;
      const mcaRef = escapeHtml(mca.mca_ref || 'N/A');
      const fob = parseFloat(mca.fob || 0);
      const weight = parseFloat(mca.weight || 0);
      const date = mca.customs_manifest_date ? new Date(mca.customs_manifest_date).toLocaleDateString('en-GB') : '';
      
      const isChecked = selectedMCAIds.includes(mcaId) ? 'checked' : '';
      
      html += '<li class="dropdown-item mca-dropdown-item">';
      html += '  <div class="mca-checkbox-wrapper" data-mca-id="' + mcaId + '">';
      html += '    <input type="checkbox" class="mca-checkbox" value="' + mcaId + '" id="mca_' + mcaId + '" ' + isChecked + '>';
      html += '    <label class="mca-checkbox-label" for="mca_' + mcaId + '">';
      html += '      <span class="mca-ref-text">' + mcaRef + '</span>';
      html += '      <span class="mca-details-text">';
      if (fob > 0) html += 'FOB: $' + fob.toFixed(2) + ' | ';
      if (weight > 0) html += weight.toFixed(2) + ' kg';
      if (date) html += ' | ' + date;
      html += '      </span>';
      html += '    </label>';
      html += '  </div>';
      html += '</li>';
    });
    
    $('#mcaDropdownMenu').html(html);
    updateMCADropdownText();
    
    $('#selectAllMCABtn').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const allChecked = $('.mca-checkbox').length === $('.mca-checkbox:checked').length;
      
      if (allChecked) {
        $('.mca-checkbox').prop('checked', false);
        $(this).html('<i class="ti ti-checkbox"></i> Select All MCAs');
      } else {
        $('.mca-checkbox').prop('checked', true);
        $(this).html('<i class="ti ti-checkbox-off"></i> Deselect All MCAs');
      }
      
      handleMCACheckboxChange();
    });
    
    $('.mca-checkbox').on('change', function(e) {
      e.stopPropagation();
      handleMCACheckboxChange();
      
      const allChecked = $('.mca-checkbox').length === $('.mca-checkbox:checked').length;
      if (allChecked) {
        $('#selectAllMCABtn').html('<i class="ti ti-checkbox-off"></i> Deselect All MCAs');
      } else {
        $('#selectAllMCABtn').html('<i class="ti ti-checkbox"></i> Select All MCAs');
      }
    });
    
    $('.mca-checkbox-wrapper').on('click', function(e) {
      if (!$(e.target).is('input[type="checkbox"]')) {
        const checkbox = $(this).find('.mca-checkbox');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
      }
    });
  }

  function handleMCACheckboxChange() {
    selectedMCAIds = [];
    
    $('.mca-checkbox:checked').each(function() {
      selectedMCAIds.push(parseInt($(this).val()));
    });
    
    $('#mca_ids').val(selectedMCAIds.join(','));
    updateMCADropdownText();
    
    if (selectedMCAIds.length > 0) {
      loadMCADetails(selectedMCAIds);
    } else {
      clearMCAFields();
      $('#quotationSelector').html('<option value="">-- Select MCA First --</option>');
      clearQuotationItems();
    }
    
    // ⭐ NEW: Auto-populate M3 for Client 37 with Goods Type 3
    autoPopulateM3ForClient37();
    
    setTimeout(() => calculateFinancials(), 300);
  }

  function updateMCADropdownText() {
    const count = selectedMCAIds.length;
    
    if (count === 0) {
      $('#mcaDropdownText').text('Select MCA References');
    } else if (count === 1) {
      const selectedCheckbox = $('.mca-checkbox:checked').first();
      const mcaRef = selectedCheckbox.closest('.mca-checkbox-wrapper').find('.mca-ref-text').text();
      $('#mcaDropdownText').text(mcaRef);
    } else {
      $('#mcaDropdownText').text(count + ' MCAs Selected');
    }
  }

  function clearMCADropdown() {
    selectedMCAIds = [];
    $('#mca_ids').val('');
    $('#mcaDropdownMenu').html('<li class="dropdown-item-text text-center text-muted py-3"><i class="ti ti-info-circle me-1"></i> Select License First</li>');
    $('#mcaDropdownText').text('Select License First');
  }

// ⭐ NEW FUNCTION: Auto-populate M3 field for Client ID 37 with Goods Type 3
function autoPopulateM3ForClient37() {
  const clientId = parseInt($('#client_id').val() || 0);
  const goodsTypeId = parseInt($('#goods_type_id').val() || 0);
  
  // Only apply this logic for Client ID 37 AND Goods Type 3
  if (clientId === 37 && goodsTypeId === 3) {
    const mcaCount = selectedMCAIds.length;
    
    if (mcaCount > 0) {
      $('#m3').val(mcaCount);
      
      // Visual feedback
      $('#m3').css('background-color', '#d4edda');
      setTimeout(() => {
        $('#m3').css('background-color', '');
      }, 1000);
      
      console.log(`[Client 37 - Goods Type 3] M3 auto-populated with MCA count: ${mcaCount}`);
    } else {
      $('#m3').val('');
    }
  }
}

  $('#quotationSelector').on('change', function() {
    const quotationId = $(this).val();
    const clientId = $('#client_id').val();
    
    $('#quotation_id').val(quotationId);
    
    if (!quotationId || !clientId) {
      clearQuotationItems();
      return;
    }

    getQuotationItems(quotationId, clientId);
  });
  
  function getQuotationItems(quotationId, clientId) {
    if (isEditMode && quotationItemsData && quotationItemsData.length > 0) {
        displaySavedItems(quotationItemsData);
        return;
    }
    
    $.ajax({
        url: CONTROLLER_URL + '/crudData/getQuotationItems',
        method: 'GET',
        data: { quotation_id: quotationId, client_id: clientId },
        dataType: 'json',
        success: function(itemsRes) {
            if (itemsRes.success) {
                quotationData = itemsRes.quotation;
                
                if (itemsRes.categorized_items && itemsRes.categorized_items.length > 0) {
                    displayQuotationItemsByCategory(itemsRes.categorized_items, quotationData);
                    
                    const subTotal = parseFloat(quotationData.sub_total || 0);
                    const vatAmount = parseFloat(quotationData.vat_amount || 0);
                    const totalAmount = parseFloat(quotationData.total_amount || 0);
                    updateSummaryTotals(subTotal, vatAmount, totalAmount);
                } else {
                    $('#quotationItemsContainer').html('<div class="quotation-content"><p style="margin: 0; color: #e67e22;"><i class="ti ti-alert-triangle me-1"></i> No items found in quotation</p></div>');
                }
            } else {
                $('#quotationItemsContainer').html('<div class="quotation-content"><p style="margin: 0; color: #e74c3c;"><i class="ti ti-x-circle me-1"></i> ' + (itemsRes.message || 'Error loading quotation items') + '</p></div>');
            }
        },
        error: function(xhr, status, error) {
            $('#quotationItemsContainer').html('<div class="quotation-content"><p style="margin: 0; color: #e74c3c;"><i class="ti ti-x-circle me-1"></i> Error loading quotation items</p></div>');
        }
    });
  }

  function displaySavedItems(items) {
    if (!items || items.length === 0) {
        $('#quotationItemsContainer').html('<div class="quotation-content"><p style="margin: 0; color: #e67e22;"><i class="ti ti-alert-triangle me-1"></i> No saved items found.</p></div>');
        return;
    }
    
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
    displayQuotationItemsByCategory(categorizedItems, quotationData);
  }
  
 function loadMCADetails(mcaIds) {
    if (!mcaIds || mcaIds.length === 0) {
      clearMCAFields();
      return;
    }

    $.ajax({
      url: CONTROLLER_URL + '/crudData/getMCADetails',
      method: 'GET',
      data: { mca_ids: mcaIds.join(',') },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          const mca = res.data;
          
          $('#kind_id').val(mca.kind_id || '');
          $('#goods_type_id').val(mca.goods_type_id || '');
          $('#transport_mode_id').val(mca.transport_mode_id || '');
          $('#arsp').val(mca.arsp || '');
          $('#kind').val(escapeHtml(mca.kind_name || ''));
          $('#type_of_goods').val(escapeHtml(mca.type_of_goods_name || ''));
          $('#transport_mode').val(escapeHtml(mca.transport_mode_name || ''));
          
          $('#fob_usd').val(formatNumber(mca.fob || 0, 2));
          $('#fret_usd').val(formatNumber(mca.fret || 0, 2));
          $('#poids_kg').val(formatNumber(mca.weight || 0, 2));
          $('#produit').val(escapeHtml(mca.commodity || 'Default Commodity'));
          
          const liquidationPaidBy = parseInt($('#liquidation_paid_by').val() || 0);
          if (liquidationPaidBy === 1) {
            $('#total_duty_cdf').val('');
          } else {
            if (mca.liquidation_amount !== undefined && mca.liquidation_amount !== null) {
              $('#total_duty_cdf').val(formatNumber(mca.liquidation_amount, 2));
            } else {
              $('#total_duty_cdf').val('');
            }
          }
          
          const goodsTypeId = parseInt(mca.goods_type_id || 0);
          if (goodsTypeId === 3) {
            const m3Value = parseFloat(mca.m3 || 0);
            $('#m3').val(formatNumber(m3Value, 2));
            $('#m3Section').removeClass('hidden-field');
          } else {
            $('#m3').val('');
            $('#m3Section').addClass('hidden-field');
          }
          
          $('#facture_pfi_no').val(escapeHtml(mca.facture_pfi_no || ''));
          $('#po_ref').val(escapeHtml(mca.po_ref || ''));
          $('#bivac_inspection').val(escapeHtml(mca.bivac_inspection || ''));
          $('#declaration_no').val(escapeHtml(mca.declaration_no || ''));
          $('#declaration_date').val(mca.dgda_in_date || '');
          $('#liquidation_no').val(escapeHtml(mca.liquidation_no || ''));
          $('#liquidation_date').val(mca.liquidation_date || '');
          $('#quittance_no').val(escapeHtml(mca.quittance_no || ''));
          $('#quittance_date').val(mca.quittance_date || '');
          $('#dispatch_deliver_date').val(mca.dispatch_deliver_date || '');
          
          const transportModeId = parseInt(mca.transport_mode_id);
          $('#roadTransportSection, #railTransportSection, #airTransportSection').addClass('hidden-field');
          
          if (transportModeId === 1) {
            $('#roadTransportSection').removeClass('hidden-field');
            $('#horse').val(escapeHtml(mca.horse || ''));
            $('#trailer_1').val(escapeHtml(mca.trailer_1 || ''));
            $('#trailer_2').val(escapeHtml(mca.trailer_2 || ''));
            $('#container').val(escapeHtml(mca.container || ''));
          } 
          else if (transportModeId === 2) {
            $('#airTransportSection').removeClass('hidden-field');
            $('#airway_bill').val(escapeHtml(mca.airway_bill || ''));
            $('#airway_bill_weight').val(formatNumber(mca.airway_bill_weight || 0, 2));
            $('#container_air').val(escapeHtml(mca.container || ''));
          }
          else if (transportModeId === 3) {
            $('#railTransportSection').removeClass('hidden-field');
            $('#wagon').val(escapeHtml(mca.wagon || ''));
            $('#horse_rail').val(escapeHtml(mca.horse || ''));
            $('#trailer_1_rail').val(escapeHtml(mca.trailer_1 || ''));
            $('#trailer_2_rail').val(escapeHtml(mca.trailer_2 || ''));
            $('#container_rail').val(escapeHtml(mca.container || ''));
          }
          
          const clientId = $('#client_id').val();
          const kindId = mca.kind_id || 0;
          const transportId = mca.transport_mode_id || 0;
          const goodsId = mca.goods_type_id || 0;
          
          $.ajax({
            url: CONTROLLER_URL + '/crudData/getAllQuotationsForClient',
            method: 'GET',
            data: { 
              client_id: clientId,
              kind_id: kindId,
              transport_mode_id: transportId,
              goods_type_id: goodsId
            },
            dataType: 'json',
            success: function(quotRes) {
              if (quotRes.success && quotRes.data && quotRes.data.length > 0) {
                let options = '<option value="">-- Select Quotation --</option>';
                quotRes.data.forEach(function(quot) {
                  options += `<option value="${quot.id}">${escapeHtml(quot.quotation_ref)}</option>`;
                });
                $('#quotationSelector').html(options);
                
                if (quotRes.data.length === 1) {
                  const firstQuotId = quotRes.data[0].id;
                  $('#quotationSelector').val(firstQuotId);
                  $('#quotation_id').val(firstQuotId);
                  getQuotationItems(firstQuotId, clientId);
                }
              } else {
                $('#quotationSelector').html('<option value="">-- No Matching Quotations Found --</option>');
              }
            },
            error: function(xhr, status, error) {
              $('#quotationSelector').html('<option value="">-- Error Loading Quotations --</option>');
            }
          });
          
          // ⭐ NEW: Auto-populate M3 for Client 37 with Goods Type 3
          autoPopulateM3ForClient37();
          
          setTimeout(() => updateRLSPercentage(), 500);
          calculateFinancials();
          
          // ⭐ AUTO-UPDATE TALLY REF AFTER MCA DETAILS ARE LOADED
          const invoiceRef = $('#invoice_ref').val().trim();
          if (invoiceRef) {
            generateTallyRef(invoiceRef);
            
            // Visual feedback - highlight the Tally Ref field
            $('#tally_ref').css('background-color', '#d4edda');
            setTimeout(() => {
              $('#tally_ref').css('background-color', '');
            }, 1000);
          }
        }
      }
    });
  }

  function displayQuotationItemsByCategory(categorizedItems, quotation) {
    let html = '';
    quotationItemsData = [];
    hiddenCategoryIndices = [];
    category1TotalUSD = 0;
    category1TotalCDF = 0;
          
    if (!categorizedItems || categorizedItems.length === 0) {
      html = '<p style="margin: 18px 0; color: #e67e22; text-align: center;"><i class="ti ti-alert-triangle me-1"></i> No items found in quotation.</p>';
      $('#quotationItemsContainer').html(html);
      return;
    }
    
    var hideShow = $("#liquidation_paid_by").val();
    const goodsTypeId = parseInt($('#goods_type_id').val() || 0);
    const isGoodsType3 = (goodsTypeId === 3);
    const totalM3 = parseFloat($('#m3').val() || 0);
    
    categorizedItems.forEach(function(category, categoryIndex) {
      if (!category.items || category.items.length === 0) return;
      
      const isFirstCategory = categoryIndex === 0;
      const categoryId = parseInt(category.category_id || 0);
      
      let shouldBeHidden = false;
      let buttonHTML = '';
      if (isFirstCategory == 0) {
        if (hideShow == 1) {
          $('#first_categoty_edited').val('H');
        } else {
          $('#first_categoty_edited').val('S');
        }
      }
      
      if (isFirstCategory) {
        if (hideShow == 1) {
          shouldBeHidden = true;
          buttonHTML = '<button type="button" class="category-toggle-btn" data-value="S" data-category-index="0">';
          buttonHTML += '<i class="ti ti-chevron-down"></i> Show';
          buttonHTML += '</button>';
          if (!hiddenCategoryIndices.includes(0)) {
            hiddenCategoryIndices.push(0);
          }
        } else if (hideShow == 2) {
          shouldBeHidden = false;
          buttonHTML = '<button type="button" class="category-toggle-btn" data-value="H" data-category-index="0">';
          buttonHTML += '<i class="ti ti-chevron-up"></i> Hide';
          buttonHTML += '</button>';
          hiddenCategoryIndices = hiddenCategoryIndices.filter(i => i !== 0);
        }
      }
      
      html += '<div class="quotation-category-header">';
      html += '<span>' + escapeHtml(category.category_header || category.category_name || 'UNCATEGORIZED') + '</span>';
      
      if (isFirstCategory) {
        html += buttonHTML;
      }
      
      html += '</div>';
      
      const collapsedClass = shouldBeHidden ? 'collapsed' : '';
      const displayStyle = shouldBeHidden ? 'display: none;' : 'display: block;';
      
      html += '<div class="category-items-display ' + collapsedClass + '" data-category-index="' + categoryIndex + '" style="' + displayStyle + '">';
      html += '<table class="quotation-items-table">';
      
      html += '<thead>';
      html += '<tr>';
      
      if (isFirstCategory) {
        html += '<th style="width: 20%;">DESCRIPTION</th>';
        html += '<th style="width: 10%;" class="text-center">UNIT</th>';
        html += '<th style="width: 12%;" class="text-center">CIF/Split</th>';
        html += '<th style="width: 8%;" class="text-center">%</th>';
        html += '<th style="width: 12%;" class="text-right">Rate/CDF</th>';
        html += '<th style="width: 12%;" class="text-right">VAT/CDF</th>';
        html += '<th style="width: 14%;" class="text-right">Total/CDF</th>';
        html += '<th style="width: 10%;" class="text-center">ACTION</th>';
      } else {
        html += '<th style="width: 22%;">DESCRIPTION</th>';
        html += '<th style="width: 10%;" class="text-center">UNIT</th>';
        html += '<th style="width: 10%;" class="text-center">QTY</th>';
        html += '<th style="width: 12%;" class="text-right">TAUX/USD</th>';
        html += '<th style="width: 10%;" class="text-center">CURRENCY</th>';
        html += '<th style="width: 8%;" class="text-center">TVA</th>';
        html += '<th style="width: 12%;" class="text-right">TVA/USD</th>';
        html += '<th style="width: 16%;" class="text-right">TOTAL EN USD</th>';
      }
      
      html += '</tr>';
      html += '</thead>';
      
      html += '<tbody>';
      
      let isFirstItemInCategory = true;
      
      category.items.forEach(function(item, itemIndex) {
        const globalItemIndex = quotationItemsData.length;
        
        if (isFirstCategory && !isEditMode) {
          const itemName = (item.item_name || '').toUpperCase();
          
          if (itemName.includes('TPI')) {
            item.percentage = 1.84;
          } else if (itemName.includes('RII')) {
            item.percentage = 2.25;
          } else if (itemName.includes('COG')) {
            item.percentage = 0.457;
          }
        }
        
        if (isGoodsType3 && (categoryId === 3 || categoryId === 4) && isFirstItemInCategory && totalM3 > 0) {
          item.quantity = totalM3;
        }
        
        quotationItemsData.push(item);
        
        const hasTVA = parseInt(item.has_tva || 0) === 1;
        const currency = escapeHtml(item.currency_short_name || 'USD');
        const quantity = parseFloat(item.quantity || 1);
        const tauxUsd = parseFloat(item.taux_usd || item.cost_usd || 0);
        const tvaUsd = parseFloat(item.tva_usd || 0);
        const totalUsd = parseFloat(item.total_usd || 0);
        
        const cifSplit = parseFloat(item.cif_split || 0);
        const percentage = parseFloat(item.percentage || 0);
        const rateCdf = parseFloat(item.rate_cdf || 0);
        const vatCdf = parseFloat(item.vat_cdf || 0);
        const totalCdf = parseFloat(item.total_cdf || 0);
        
        let itemHiddenStyle = '';
        if (item.item_id == 23 && hideShow == 1) {
          itemHiddenStyle = 'display:none;';
        }

        html += `<tr data-item-index="${globalItemIndex}" data-is-first-category="${isFirstCategory}" data-category-id="${categoryId}" data-is-first-item="${isFirstItemInCategory}" style="${itemHiddenStyle}">`;

        html += '<td class="item-description">' + escapeHtml(item.item_name || 'N/A') + '</td>';
        html += '<td class="text-center item-unit">' + escapeHtml(item.unit_text || item.unit_name || 'Unit') + '</td>';
        
        if (isFirstCategory) {
          html += '<td class="text-center"><input type="number" class="item-input cif-input" data-field="cif_split" value="' + formatNumber(cifSplit, 2) + '" step="0.01" min="0" style="max-width: 100px;"></td>';
          html += '<td class="text-center"><input type="number" class="item-input percentage-input" data-field="percentage" value="' + formatNumber(percentage, 3) + '" step="0.001" min="0" max="100" style="max-width: 80px;"></td>';
          html += '<td class="text-right"><input type="number" class="item-input rate-cdf-input" data-field="rate_cdf" value="' + formatNumber(rateCdf, 2) + '" step="0.01" min="0" style="max-width: 110px;"></td>';
          html += '<td class="text-right"><input type="number" class="item-input vat-cdf-input" data-field="vat_cdf" value="' + formatNumber(vatCdf, 2) + '" step="0.01" min="0" style="max-width: 110px;"></td>';
          html += '<td class="text-right"><input type="number" class="item-input total-cdf-input" data-field="total_cdf" value="' + formatNumber(totalCdf, 2) + '" step="0.01" min="0" style="max-width: 120px;"></td>';
          html += '<td class="text-center"><button type="button" class="delete-item-btn" data-item-index="' + globalItemIndex + '" title="Delete"><i class="ti ti-trash"></i></button></td>';
        } else {
          const displayQty = (isGoodsType3 && (categoryId === 3 || categoryId === 4) && isFirstItemInCategory && totalM3 > 0) ? formatNumber(totalM3, 2) : formatNumber(quantity, 2);
          
          html += '<td class="text-center"><input type="number" class="item-input qty-input" data-field="quantity" value="' + displayQty + '" step="0.01" min="0" style="max-width: 80px;"></td>';
          html += '<td class="text-right"><input type="number" class="item-input rate-input" data-field="taux_usd" value="' + formatNumber(tauxUsd, 2) + '" step="0.01" min="0" style="max-width: 100px;"></td>';
          html += '<td class="text-center item-currency">' + currency + '</td>';
          html += '<td class="text-center"><input type="checkbox" class="tva-display" ' + (hasTVA ? 'checked' : '') + ' disabled></td>';
          html += '<td class="text-right"><span class="item-tva-display">' + formatNumber(tvaUsd, 2) + '</span></td>';
          html += '<td class="text-right"><span class="item-total-display">' + formatNumber(totalUsd, 2) + '</span></td>';
        }
        
        html += '</tr>';
        
        isFirstItemInCategory = false;
      });
      
      html += '</tbody>';
      html += '</table>';
      
      if (isFirstCategory) {
        html += '<div class="category-totals-row" id="category1TotalsRow">';
        html += '  <table>';
        html += '    <tr>';
        html += '      <td><i class="ti ti-currency-franc me-2"></i>Liquidation in CDF:</td>';
        html += '      <td id="category1TotalCDFDisplay">0.00 FC</td>';
        html += '    </tr>';
        html += '    <tr>';
        html += '      <td><i class="ti ti-currency-dollar me-2"></i>Liquidation in USD:</td>';
        html += '      <td id="category1TotalUSDDisplay">$0.00</td>';
        html += '    </tr>';
        html += '  </table>';
        html += '</div>';
      }
      
      html += '</div>';
      html += '<div style="margin-bottom: 12px;"></div>';
    });
    
    if (html === '') {
      html = '<p style="margin: 18px 0; color: #e67e22; text-align: center;"><i class="ti ti-alert-triangle me-1"></i> No categories with items found.</p>';
    }
    
    $('#quotationItemsContainer').html(html);
    $('#hidden_categories').val(JSON.stringify(hiddenCategoryIndices));
    attachItemEventListeners();
    attachCategoryToggleListener();
    updateRLSPercentage();
    recalculateAllTotals();
  }

  function attachCategoryToggleListener() {
    $(document).off('click', '.category-toggle-btn');

    $(document).on('click', '.category-toggle-btn', function () {

      const $btn = $(this);
      const categoryIndex = Number($btn.data('category-index'));
      const $itemsContainer = $('.category-items-display[data-category-index="' + categoryIndex + '"]');

      if ($itemsContainer.hasClass('collapsed')) {
        $itemsContainer.removeClass('collapsed').slideDown(300);
        $btn.html('<i class="ti ti-chevron-up"></i> Hide');

        hiddenCategoryIndices = hiddenCategoryIndices.filter(i => i !== categoryIndex);

        if (categoryIndex === 0) {
          $('#first_categoty_edited').val('S');
        }

      } else {
        $itemsContainer.addClass('collapsed').slideUp(300);
        $btn.html('<i class="ti ti-chevron-down"></i> Show');

        if (!hiddenCategoryIndices.includes(categoryIndex)) {
          hiddenCategoryIndices.push(categoryIndex);
        }

        if (categoryIndex === 0) {
          $('#first_categoty_edited').val('H');
        }
      }

      $('#hidden_categories').val(JSON.stringify(hiddenCategoryIndices));
    });
  }

  function attachItemEventListeners() {
  $(document).off('input change', '.item-input');
  $(document).on('input change', '.item-input', function() {
    const $row = $(this).closest('tr');
    const itemIndex = parseInt($row.data('item-index'));
    const isFirstCategory = $row.data('is-first-category') === true;
    const field = $(this).data('field');
    const value = parseFloat($(this).val()) || 0;
    
    if (quotationItemsData[itemIndex]) {
      if (field === 'quantity') {
        quotationItemsData[itemIndex].quantity = value;
      } else if (field === 'taux_usd') {
        quotationItemsData[itemIndex].taux_usd = value;
        quotationItemsData[itemIndex].cost_usd = value;
      } else if (field === 'cif_split') {
        quotationItemsData[itemIndex].cif_split = value;
      } else if (field === 'percentage') {
        quotationItemsData[itemIndex].percentage = value;
        
        if (isFirstCategory) {
          const itemName = (quotationItemsData[itemIndex].item_name || '').toUpperCase();
          
          if (itemName.includes('RLS') || itemName.includes('REDEVANCE LOGISTIQUE')) {
            const rateCDFINV = parseFloat($('#rate_cdf_inv').val()) || 2500;
            const calculatedRateCdf = value * rateCDFINV * 85;
            
            quotationItemsData[itemIndex].rate_cdf = calculatedRateCdf;
            $row.find('.rate-cdf-input').val(formatNumber(calculatedRateCdf, 2));
            
            const vatCdf = parseFloat($row.find('.vat-cdf-input').val()) || 0;
            const totalCdf = calculatedRateCdf + vatCdf;
            
            quotationItemsData[itemIndex].total_cdf = totalCdf;
            $row.find('.total-cdf-input').val(formatNumber(totalCdf, 2));
            
            $row.find('.rate-cdf-input').css('background-color', '#d4edda');
            $row.find('.total-cdf-input').css('background-color', '#d4edda');
            setTimeout(() => {
              $row.find('.rate-cdf-input').css('background-color', '');
              $row.find('.total-cdf-input').css('background-color', '');
            }, 1000);
          } else {
            const totalCdf = parseFloat($row.find('.total-cdf-input').val()) || 0;
            const calculatedCifSplit = totalCdf * value;
            
            quotationItemsData[itemIndex].cif_split = calculatedCifSplit;
            $row.find('.cif-input').val(formatNumber(calculatedCifSplit, 2));
            
            $row.find('.cif-input').css('background-color', '#d4edda');
            setTimeout(() => $row.find('.cif-input').css('background-color', ''), 800);
          }
        }
        
      } else if (field === 'rate_cdf') {
        quotationItemsData[itemIndex].rate_cdf = value;
        
        const vatCdf = parseFloat($row.find('.vat-cdf-input').val()) || 0;
        const totalCdf = value + vatCdf;
        
        quotationItemsData[itemIndex].total_cdf = totalCdf;
        
        const $totalCdfInput = $row.find('.total-cdf-input');
        $totalCdfInput.val(formatNumber(totalCdf, 2));
        
        $totalCdfInput.css('background-color', '#d4edda');
        setTimeout(() => $totalCdfInput.css('background-color', ''), 800);
        
      } else if (field === 'vat_cdf') {
        quotationItemsData[itemIndex].vat_cdf = value;
        
        const rateCdf = parseFloat($row.find('.rate-cdf-input').val()) || 0;
        const totalCdf = rateCdf + value;
        
        quotationItemsData[itemIndex].total_cdf = totalCdf;
        
        const $totalCdfInput = $row.find('.total-cdf-input');
        $totalCdfInput.val(formatNumber(totalCdf, 2));
        
        $totalCdfInput.css('background-color', '#d4edda');
        setTimeout(() => $totalCdfInput.css('background-color', ''), 800);
        
      } else if (field === 'total_cdf') {
        quotationItemsData[itemIndex].total_cdf = value;
        
        const vatCdf = parseFloat($row.find('.vat-cdf-input').val()) || 0;
        const rateCdf = value - vatCdf;
        
        quotationItemsData[itemIndex].rate_cdf = rateCdf;
        
        const $rateCdfInput = $row.find('.rate-cdf-input');
        $rateCdfInput.val(formatNumber(rateCdf, 2));
        
        $rateCdfInput.css('background-color', '#d4edda');
        setTimeout(() => $rateCdfInput.css('background-color', ''), 800);
      }
      
      if (isFirstCategory) {
        setTimeout(() => {
          calculateAutresTaxes();
          recalculateAllTotals();
        }, 100);
      }
      if (!isFirstCategory) {
        recalculateRowTotal($row, itemIndex);
      }
      
      $('#items_manually_edited').val('1');
    }
  });
  
  $(document).off('click', '.delete-item-btn');
  $(document).on('click', '.delete-item-btn', function() {
    const itemIndex = parseInt($(this).data('item-index'));
    const $row = $(this).closest('tr');
    
    Swal.fire({
      title: 'Delete Item?',
      text: "Are you sure you want to remove this item?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $row.fadeOut(300, function() {
          $(this).remove();
          quotationItemsData.splice(itemIndex, 1);
          $('.quotation-items-table tbody tr').each(function(idx) {
            $(this).attr('data-item-index', idx);
            $(this).find('.delete-item-btn').attr('data-item-index', idx);
          });
          recalculateAllTotals();
          
          $('#items_manually_edited').val('1');
          
          Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false });
        });
      }
    });
  });
}

 function recalculateRowTotal($row, itemIndex) {
  const item = quotationItemsData[itemIndex];
  if (!item) return;
  
  const itemName = (item.item_name || '').toUpperCase();
  const quantity = parseFloat(item.quantity || 1);
  const rate = parseFloat(item.taux_usd || item.cost_usd || 0);
  const hasTVA = parseInt(item.has_tva || 0) === 1;
  
  let subtotal = quantity * rate;
  
  if (itemName.includes('FRAIS BANCAIRES')) {
    subtotal = (category1TotalUSD * quantity) / 100;
    
    quotationItemsData[itemIndex].taux_usd = subtotal;
    quotationItemsData[itemIndex].cost_usd = subtotal;
    
    $row.find('.rate-input').val(formatNumber(subtotal, 2));
    
    $row.find('.rate-input').css('background-color', '#d4edda');
    setTimeout(() => $row.find('.rate-input').css('background-color', ''), 1000);
  }
  
  let tvaAmount = 0;
  if (hasTVA) {
    tvaAmount = subtotal * 0.16;
  }
  const total = subtotal + tvaAmount;
  
  quotationItemsData[itemIndex].subtotal_usd = subtotal;
  quotationItemsData[itemIndex].tva_usd = tvaAmount;
  quotationItemsData[itemIndex].total_usd = total;
  
  $row.find('.item-tva-display').text(formatNumber(tvaAmount, 2));
  $row.find('.item-total-display').text(formatNumber(total, 2));
  
  $row.find('.item-tva-display').css('background-color', '#d4edda');
  $row.find('.item-total-display').css('background-color', '#d4edda');
  setTimeout(() => {
    $row.find('.item-tva-display').css('background-color', '');
    $row.find('.item-total-display').css('background-color', '');
  }, 1000);
  
  recalculateAllTotals();
}

 function recalculateAllTotals() {
  let totalExclTVA = 0;
  let totalTVA = 0;
  let page1TotalUSD = 0;
  category1TotalUSD = 0;
  category1TotalCDF = 0;
  
  quotationItemsData.forEach((item, idx) => {
    const isFirstCategory = $('.quotation-items-table tbody tr[data-item-index="' + idx + '"]').data('is-first-category') === true;
    
    if (isFirstCategory) {
      const totalCdf = parseFloat(item.total_cdf || 0);
      category1TotalCDF += totalCdf;
    }
  });
  
  const rateCDFINV = sanitizeNumber($('#rate_cdf_inv').val()) || 2500;
  const rateCDF = sanitizeNumber($('#rate_cdf_usd_bcc').val()) || 2500;
  
  category1TotalUSD = category1TotalCDF / rateCDFINV;
  
  $('#category1TotalCDFDisplay').text(category1TotalCDF.toFixed(2) + ' FC');
  $('#category1TotalUSDDisplay').text('$' + category1TotalUSD.toFixed(2));
  
  quotationItemsData.forEach((item, idx) => {
    const isFirstCategory = $('.quotation-items-table tbody tr[data-item-index="' + idx + '"]').data('is-first-category') === true;
    
    if (!isFirstCategory) {
      const quantity = parseFloat(item.quantity || 1);
      const itemName = (item.item_name || '').toUpperCase();
      const hasTVA = parseInt(item.has_tva || 0) === 1;
      
      let rate = parseFloat(item.taux_usd || item.cost_usd || 0);
      let subtotal = quantity * rate;
      
      if (itemName.includes('FRAIS BANCAIRES')) {
        subtotal = (category1TotalUSD * quantity) / 100;
        
        item.taux_usd = subtotal;
        item.cost_usd = subtotal;
        
        const $row = $('.quotation-items-table tbody tr[data-item-index="' + idx + '"]');
        $row.find('.rate-input').val(formatNumber(subtotal, 2));
      }
      
      let tva = 0;
      if (hasTVA) {
        tva = subtotal * 0.16;
      }
      
      item.subtotal_usd = subtotal;
      item.tva_usd = tva;
      item.total_usd = subtotal + tva;
      
      totalExclTVA += subtotal;
      totalTVA += tva;
      
      const categoryId = parseInt(item.category_id || 0);
      if (categoryId === 1 || categoryId === 2) {
        const itemTotal = subtotal + tva;
        page1TotalUSD += itemTotal;
        
        if (categoryId === 1) {
          category1TotalUSD += itemTotal;
        }
      }
    }
  });
  
  $('.quotation-items-table tbody tr').each(function() {
    const itemIndex = parseInt($(this).data('item-index'));
    const isFirstCategory = $(this).data('is-first-category') === true;
    
    if (!isFirstCategory) {
      const item = quotationItemsData[itemIndex];
      if (item) {
        $(this).find('.item-tva-display').text(formatNumber(item.tva_usd, 2));
        $(this).find('.item-total-display').text(formatNumber(item.total_usd, 2));
      }
    }
  });
  
  const grandTotal = totalExclTVA + totalTVA;
  const equivalentCDF = grandTotal * rateCDF;
  const page1TotalCDF = page1TotalUSD * rateCDFINV;
  
  $('#calculated_sub_total').val(totalExclTVA.toFixed(2));
  $('#calculated_vat_amount').val(totalTVA.toFixed(2));
  $('#calculated_total_amount').val(grandTotal.toFixed(2));
  $('#calculated_total_cdf').val(page1TotalCDF.toFixed(2));
  
  updateSummaryTotals(totalExclTVA, totalTVA, grandTotal);
}

  function clearQuotationItems() {
    quotationItemsData = [];
    quotationData = null;
    hiddenCategoryIndices = [];
    category1TotalUSD = 0;
    category1TotalCDF = 0;
    $('#quotation_id').val('');
    $('#hidden_categories').val('[]');
    
    $('#calculated_sub_total').val('0.00');
    $('#calculated_vat_amount').val('0.00');
    $('#calculated_total_amount').val('0.00');
    $('#calculated_total_cdf').val('0.00');
    $('#items_manually_edited').val('0');
    
    $('#quotationItemsContainer').html('<div class="quotation-content"><p style="margin: 0;"><i class="ti ti-info-circle me-1"></i> Select a quotation from the dropdown above</p></div>');
    updateSummaryTotals(0, 0, 0);
  }

 function clearMCAFields() {
  $('#kind_id, #goods_type_id, #transport_mode_id').val('');
  $('#kind, #type_of_goods, #transport_mode').val('');
  $('#fob_usd, #fret_usd, #assurance_usd, #autres_charges_usd, #poids_kg').val('');
  $('#cif_usd').val('');
  $('#cif_cdf').val('');
  $('#cif_cdf_display').val('');
  $('#horse, #trailer_1, #trailer_2, #container').val('');
    $('#wagon, #horse_rail, #trailer_1_rail, #trailer_2_rail, #container_rail').val('');
    $('#airway_bill, #airway_bill_weight, #container_air').val('');
    $('#facture_pfi_no, #po_ref, #bivac_inspection, #tariff_code_client').val('');
    $('#declaration_no, #declaration_date').val('');
    $('#liquidation_no, #liquidation_date').val('');
    $('#quittance_no, #quittance_date').val('');
    $('#dispatch_deliver_date').val('');
    $('#produit').val('Default Commodity');
    $('#m3').val('');
    $('#total_duty_cdf').val('');
    $('#m3Section').addClass('hidden-field');
    $('#roadTransportSection, #railTransportSection, #airTransportSection').addClass('hidden-field');
    calculateFinancials();
  }

  $(document).on('input change', '.financial-calc-trigger', calculateFinancials);

  $(document).on('input change', '#rate_cdf_inv', function() {
    calculateFinancials();
    
    $('.quotation-items-table tbody tr[data-is-first-category="true"]').each(function() {
      const $row = $(this);
      const itemIndex = parseInt($row.data('item-index'));
      const item = quotationItemsData[itemIndex];
      
      if (item) {
        const itemName = (item.item_name || '').toUpperCase();
        
        if (itemName.includes('RLS') || itemName.includes('REDEVANCE LOGISTIQUE')) {
          const percentage = parseFloat($row.find('.percentage-input').val()) || 0;
          const rateCDFINV = parseFloat($('#rate_cdf_inv').val()) || 2500;
          
          const calculatedRateCdf = percentage * rateCDFINV * 85;
          
          quotationItemsData[itemIndex].rate_cdf = calculatedRateCdf;
          $row.find('.rate-cdf-input').val(formatNumber(calculatedRateCdf, 2));
          
          const vatCdf = parseFloat($row.find('.vat-cdf-input').val()) || 0;
          const totalCdf = calculatedRateCdf + vatCdf;
          
          quotationItemsData[itemIndex].total_cdf = totalCdf;
          $row.find('.total-cdf-input').val(formatNumber(totalCdf, 2));
          
          $row.find('.rate-cdf-input').css('background-color', '#d4edda');
          $row.find('.total-cdf-input').css('background-color', '#d4edda');
          setTimeout(() => {
            $row.find('.rate-cdf-input').css('background-color', '');
            $row.find('.total-cdf-input').css('background-color', '');
          }, 1000);
        }
      }
    });
    
    setTimeout(() => {
      calculateAutresTaxes();
      recalculateAllTotals();
    }, 100);
  });

function calculateFinancials() {
  const fob = sanitizeNumber($('#fob_usd').val());
  const fret = sanitizeNumber($('#fret_usd').val());
  const assurance = sanitizeNumber($('#assurance_usd').val());
  const autresCharges = sanitizeNumber($('#autres_charges_usd').val());
  const rateCDFINV = sanitizeNumber($('#rate_cdf_inv').val()) || 2500;
  
  const cifUSD = fob + fret + assurance + autresCharges;
  $('#cif_usd').val(formatNumber(cifUSD, 2));
  
  // Calculate CIF CDF = CIF USD × RATE CDF/INV
  const cifCDF = cifUSD * rateCDFINV;
  $('#cif_cdf').val(formatNumber(cifCDF, 2));
  $('#cif_cdf_display').val(formatNumber(cifCDF, 2));
  
  recalculateAllTotals();
}
  
  function updateSummaryTotals(subTotal, vatAmount, totalAmount) {
    const rateCDF = sanitizeNumber($('#rate_cdf_inv').val()) || 2500;
    const equivalentCDF = totalAmount * rateCDF;
    
    $('#totalExclTVA').text('$' + subTotal.toFixed(2));
    $('#tvaAmount').text('$' + vatAmount.toFixed(2));
    $('#grandTotal').text('$' + totalAmount.toFixed(2));
    $('#equivalentCDF').text(equivalentCDF.toFixed(2) + ' FC');
  }

  function updateRLSPercentage() {
    const values = [
      $('#horse').val(),
      $('#trailer_1').val(),
      $('#trailer_2').val()
    ];
    const count = parseFloat(values.filter(v => v !== null && v !== '').length);
    
    if (count > 0) {
      const rateCDFINV = parseFloat($('#rate_cdf_inv').val()) || 2500;
      
      $('.quotation-items-table tbody tr').each(function() {
        const $row = $(this);
        const itemIndex = parseInt($row.data('item-index'));
        const description = $row.find('.item-description').text().toUpperCase();
        
        if (description.includes('REDEVANCE LOGISTIQUE TERRESTRE') || 
            description.includes('RLS')) {
          
          const $percentageInput = $row.find('.percentage-input');
          $percentageInput.val(count);
          
          const calculatedRateCdf = count * rateCDFINV * 85;
          
          if (quotationItemsData[itemIndex]) {
            quotationItemsData[itemIndex].percentage = count;
            quotationItemsData[itemIndex].rate_cdf = calculatedRateCdf;
            
            $row.find('.rate-cdf-input').val(formatNumber(calculatedRateCdf, 2));
            
            const vatCdf = parseFloat($row.find('.vat-cdf-input').val()) || 0;
            const totalCdf = calculatedRateCdf + vatCdf;
            
            quotationItemsData[itemIndex].total_cdf = totalCdf;
            $row.find('.total-cdf-input').val(formatNumber(totalCdf, 2));
          }
          
          $percentageInput.css('background-color', '#d4edda');
          $row.find('.rate-cdf-input').css('background-color', '#d4edda');
          $row.find('.total-cdf-input').css('background-color', '#d4edda');
          setTimeout(() => {
            $percentageInput.css('background-color', '');
            $row.find('.rate-cdf-input').css('background-color', '');
            $row.find('.total-cdf-input').css('background-color', '');
          }, 1000);
          
          $('#items_manually_edited').val('1');
        }
        
        if (description.includes('SCELLES ELECTRONIQUES') || 
            description.includes('SCELLES ÉLECTRONIQUES')) {
          
          const scellesQty = Math.max(0, count - 1);
          
          const $qtyInput = $row.find('.qty-input');
          $qtyInput.val(scellesQty);
          
          if (quotationItemsData[itemIndex]) {
            quotationItemsData[itemIndex].quantity = scellesQty;
            
            const rate = parseFloat(quotationItemsData[itemIndex].taux_usd || quotationItemsData[itemIndex].cost_usd || 0);
            const hasTVA = parseInt(quotationItemsData[itemIndex].has_tva || 0) === 1;
            
            const subtotal = scellesQty * rate;
            let tvaAmount = 0;
            if (hasTVA) {
              tvaAmount = subtotal * 0.16;
            }
            const total = subtotal + tvaAmount;
            
            quotationItemsData[itemIndex].subtotal_usd = subtotal;
            quotationItemsData[itemIndex].tva_usd = tvaAmount;
            quotationItemsData[itemIndex].total_usd = total;
            
            $row.find('.item-tva-display').text(formatNumber(tvaAmount, 2));
            $row.find('.item-total-display').text(formatNumber(total, 2));
            
            $qtyInput.css('background-color', '#d4edda');
            $row.find('.item-tva-display').css('background-color', '#d4edda');
            $row.find('.item-total-display').css('background-color', '#d4edda');
            
            setTimeout(() => {
              $qtyInput.css('background-color', '');
              $row.find('.item-tva-display').css('background-color', '');
              $row.find('.item-total-display').css('background-color', '');
            }, 1000);
          }
          
          $('#items_manually_edited').val('1');
        }
      });
      
      recalculateAllTotals();
    }
  }

  $(document).on('input change', '#horse, #trailer_1, #trailer_2', function() {
    updateRLSPercentage();
  });

  $(document).on('input change', '#m3', function() {
    const goodsTypeId = parseInt($('#goods_type_id').val() || 0);
    if (goodsTypeId === 3) {
      const m3Value = parseFloat($(this).val() || 0);
      
      $('.quotation-items-table tbody tr').each(function() {
        const $row = $(this);
        const categoryId = parseInt($row.data('category-id') || 0);
        const isFirstItem = $row.data('is-first-item');
        const itemIndex = parseInt($row.data('item-index'));
        
        if ((categoryId === 3 || categoryId === 4) && isFirstItem && m3Value > 0) {
          const $qtyInput = $row.find('.qty-input');
          $qtyInput.val(formatNumber(m3Value, 2));
          
          if (quotationItemsData[itemIndex]) {
            quotationItemsData[itemIndex].quantity = m3Value;
            recalculateRowTotal($row, itemIndex);
          }
        }
      });
    }
  });

  function calculateAutresTaxes() {
    const dutyCdf = parseFloat($('#total_duty_cdf').val()) || 0;
    
    let autresTaxesRow = null;
    let autresTaxesItemIndex = -1;
    let sumOfOtherTotalsCdf = 0;
    
    $('.quotation-items-table tbody tr[data-is-first-category="true"]').each(function() {
      const $row = $(this);
      const description = $row.find('.item-description').text().toUpperCase();
      const itemIndex = parseInt($row.data('item-index'));
      
      if (description.includes('AUTRES TAXES') || description.includes('REF LIQUIDATION')) {
        autresTaxesRow = $row;
        autresTaxesItemIndex = itemIndex;
      } else {
        const totalCdfInput = $row.find('.total-cdf-input');
        if (totalCdfInput.length > 0) {
          const totalCdf = parseFloat(totalCdfInput.val()) || 0;
          sumOfOtherTotalsCdf += totalCdf;
        }
      }
    });
    
    if (autresTaxesRow && autresTaxesItemIndex >= 0) {
      const autresTaxesValue = dutyCdf - sumOfOtherTotalsCdf;
      
      const $totalCdfInput = autresTaxesRow.find('.total-cdf-input');
      $totalCdfInput.val(autresTaxesValue.toFixed(2));
      
      const $rateCdfInput = autresTaxesRow.find('.rate-cdf-input');
      $rateCdfInput.val(autresTaxesValue.toFixed(2));
      
      $totalCdfInput.css('background-color', '#d4edda');
      $rateCdfInput.css('background-color', '#d4edda');
      setTimeout(() => {
        $totalCdfInput.css('background-color', '');
        $rateCdfInput.css('background-color', '');
      }, 1000);
      
      if (quotationItemsData[autresTaxesItemIndex]) {
        quotationItemsData[autresTaxesItemIndex].total_cdf = autresTaxesValue;
        quotationItemsData[autresTaxesItemIndex].rate_cdf = autresTaxesValue;
      }
      
      recalculateAllTotals();
      
      $('#items_manually_edited').val('1');
    }
  }

 $(document).on('input change', '#total_duty_cdf', function() {
  calculateFinancials();
  calculateAutresTaxes();
});

 function validateForm() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    
    let errors = [];
    const required = [
      { id: 'client_id', label: 'Client' },
      { id: 'invoice_ref', label: 'Invoice Reference' },
      { id: 'payment_method', label: 'Payment Method' }
    ];

    required.forEach(field => {
      if (!$(`#${field.id}`).val()) {
        $(`#${field.id}`).addClass('is-invalid');
        $(`#${field.id}_error`).text(`${field.label} is required`).show();
        errors.push(`${field.label} is required`);
      }
    });

    if (selectedLicenseIds.length === 0) {
      $('.license-dropdown-btn').addClass('is-invalid');
      $('#license_ids_error').text('At least one License is required').show();
      errors.push('At least one License is required');
    }

    if (selectedMCAIds.length === 0) {
      $('.mca-dropdown-btn').addClass('is-invalid');
      $('#mca_ids_error').text('At least one MCA Reference is required').show();
      errors.push('At least one MCA Reference is required');
    }

    return { isValid: errors.length === 0, errors };
  }

 function resetForm() {
    isEditMode = false;
    
    $('#invoiceForm')[0].reset();
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    $('#invoice_id').val('');
    $('#formAction').val('insert');
    $('#formTitle').text('Add New Import Invoice');
    $('#submitBtnText').text('Save Invoice');
    $('#quotationSelector').html('<option value="">-- Select MCA First --</option>');
    $('#quotation_id').val('');
    
    $('#payment_method').val('CREDIT');
    
    $('#calculated_sub_total').val('0.00');
    $('#calculated_vat_amount').val('0.00');
    $('#calculated_total_amount').val('0.00');
    $('#calculated_total_cdf').val('0.00');
    $('#items_manually_edited').val('0');
    
    category1TotalUSD = 0;
    category1TotalCDF = 0;
    
    clearMCAFields();
    clearLicenseDropdown();
    clearMCADropdown();
    clearQuotationItems();
    $('#produit').val('Default Commodity');
    $('#invoice_ref').val('').attr('placeholder', 'Select Client First');
    $('#tally_ref').val('');
    updateSummaryTotals(0, 0, 0);
    $('#collapseInvoice').collapse('hide');
  }

  $('#invoiceForm').on('submit', function (e) {
    e.preventDefault();
    
    const validation = validateForm();
    if (!validation.isValid) {
      Swal.fire({
        icon: 'error', 
        title: 'Validation Error', 
        html: '<ul style="text-align:left;"><li>' + validation.errors.join('</li><li>') + '</li></ul>'
      });
      $('#collapseInvoice').collapse('show');
      return;
    }

    const transportModeId = parseInt($('#transport_mode_id').val());
    if (transportModeId === 3) {
      $('input[name="horse"]').val($('#horse_rail').val());
      $('input[name="trailer_1"]').val($('#trailer_1_rail').val());
      $('input[name="trailer_2"]').val($('#trailer_2_rail').val());
      $('input[name="container"]').val($('#container_rail').val());
    } else if (transportModeId === 2) {
      $('input[name="container"]').val($('#container_air').val());
    }

    $('#quotation_items').val(JSON.stringify(quotationItemsData));
    $('#hidden_categories').val(JSON.stringify(hiddenCategoryIndices));

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
          Swal.fire({ icon: 'success', title: 'Success!', text: res.message, timer: 1500, showConfirmButton: false });
          resetForm();
          if (invoicesTable) invoicesTable.ajax.reload(null, false);
          updateStatistics();
        } else {
          Swal.fire({ icon: 'error', title: 'Error!', html: res.message });
        }
      },
      error: function (xhr, status, error) {
        submitBtn.prop('disabled', false).html(originalText);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save invoice' });
      }
    });
  });

  $('#cancelBtn').on('click', (e) => { e.preventDefault(); resetForm(); });

  function initDataTable() {
    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
      $('#invoicesTable').DataTable().destroy();
    }

    invoicesTable = $('#invoicesTable').DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      autoWidth: false,
      ajax: { 
        url: CONTROLLER_URL + '/crudData/listing',
        type: 'GET',
        data: function(d) { 
          d.filter = currentFilter;
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
          data: 'type_of_goods',
          render: function(data, type, row) {
            return escapeHtml(data || 'N/A');
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
            const amount = parseFloat(data || row.cif_usd || 0);
            return '$' + amount.toFixed(2);
          }
        },
        { 
          data: 'validated',
          render: function(data, type, row) {
            const validated = parseInt(data || 0);
            if (validated === 0) {
              return '<span class="validation-badge validation-not-validated">NOT VALIDATED</span>';
            } else if (validated === 1) {
              return '<span class="validation-badge validation-validated">✓ VALIDATED</span>';
            } else if (validated === 2) {
              return '<span class="validation-badge validation-dgi-verified">DGI VERIFIED</span>';
            }
            return '<span class="validation-badge validation-not-validated">UNKNOWN</span>';
          }
        },
        { 
          data: null,
          orderable: false,
          searchable: false,
          render: function(data, type, row) {
            const validated = parseInt(row.validated || 0);
            
            let html = `
              <button class="btn btn-sm btn-pdf-page1 pdfBtn" data-id="${row.id}" title="View PDF (Both Pages)">
                <i class="ti ti-file-type-pdf"></i> PDF
              </button>
              <button class="btn btn-sm btn-pdf-p1 pdfP1Btn" data-id="${row.id}" title="View PDF Page 1 (Debit Note)">
                <i class="ti ti-file-text"></i> P1
              </button>
              <button class="btn btn-sm btn-pdf-p2 pdfP2Btn" data-id="${row.id}" title="View PDF Page 2 (Services)">
                <i class="ti ti-file-invoice"></i> P2
              </button>
              <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}" title="Edit">
                <i class="ti ti-edit"></i>
              </button>`;
            
            if (validated === 0) {
              html += `
              <button class="btn btn-sm btn-validate validateBtn" data-id="${row.id}" title="Validate Invoice">
                <i class="ti ti-circle-check"></i>
              </button>`;
            }
            
            if (validated === 1) {
              html += `
              <button class="btn btn-sm btn-dgi dgiBtn" data-id="${row.id}" title="Mark as DGI Verified">
                <i class="ti ti-file-check"></i>
              </button>`;
            }
            
            return html;
          }
        }
      ],
      order: [[4, 'desc']],
      pageLength: 25,
      dom: 'rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      drawCallback: function() { 
        updateStatistics(); 
      }
    });

    $('#customSearchBox').on('keyup', function() {
      invoicesTable.ajax.reload();
    });
  }

 function updateStatistics() {
    $.ajax({
      url: CONTROLLER_URL + '/crudData/statistics',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          $('#totalInvoices').text(res.data.total_invoices || 0);
          $('#totalValidated').text(res.data.validated_invoices || 0);
          $('#totalNotValidated').text(res.data.not_validated_invoices || 0);
          $('#totalDGIVerified').text(res.data.dgi_verified_invoices || 0);
          $('#totalPendingInvoicing').text(res.data.not_invoiced_mcas || 0);
        }
      }
    });
  }

  $(document).on('click', '.pdfBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    window.open(CONTROLLER_URL + '/crudData/viewPDF?id=' + id, '_blank');
  });

  // ⭐ NEW: PDF Page 1 Button (Debit Note only)
  $(document).on('click', '.pdfP1Btn', function() {
    const id = $(this).data('id');
    if (!id) return;
    window.open(CONTROLLER_URL + '/crudData/viewPDF?id=' + id + '&page=1', '_blank');
  });

  // ⭐ NEW: PDF Page 2 Button (Services only)
  $(document).on('click', '.pdfP2Btn', function() {
    const id = $(this).data('id');
    if (!id) return;
    window.open(CONTROLLER_URL + '/crudData/viewPDF?id=' + id + '&page=2', '_blank');
  });

  $(document).on('click', '.validateBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    Swal.fire({
      title: 'Validate Invoice?',
      html: 'This will remove the watermark from the PDF.<br><br>Are you sure you want to validate this invoice?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#17a2b8',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="ti ti-circle-check me-1"></i> Yes, validate it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: CONTROLLER_URL + '/crudData/validateInvoice',
          method: 'POST',
          data: { id: id, csrf_token: csrfToken },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: 'success', 
                title: 'Validated!', 
                text: res.message, 
                timer: 2000, 
                showConfirmButton: false
              });
              invoicesTable.ajax.reload(null, false);
              updateStatistics();
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          },
          error: function(xhr, status, error) {
            Swal.fire('Error', 'Failed to validate invoice', 'error');
          }
        });
      }
    });
  });

  $(document).on('click', '.dgiBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    Swal.fire({
      title: 'Mark as DGI Verified?',
      text: 'This will verify the invoice with DGI system.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#800000',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="ti ti-check me-2"></i>Yes, verify!',
      cancelButtonText: '<i class="ti ti-x me-2"></i>Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Processing...',
          text: 'Verifying invoice with DGI system',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        $.ajax({
          url: CONTROLLER_URL + '/crudData/markDGI',
          method: 'POST',
          data: { id: id, csrf_token: csrfToken },
          dataType: 'json',
          success: function(res) {
            Swal.close();
            
            if (res.success && res.data) {
              $("#total-badge").text(res.data.total || '0.00');
              $("#dgi-total-badge").text(res.data.vtotal || '0.00');
              $("#emcf-invoice-uid").val(res.data.uid || '');
              $("#emcf-invoice-id").val(id);
              
              $("#emcf-modal").modal('show');
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: res.message || 'Failed to verify invoice with DGI',
                confirmButtonColor: '#800000'
              });
            }
          },
          error: function(xhr) {
            Swal.close();
            
            let errorMessage = 'Failed to connect to DGI system';
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              errorMessage = errorResponse.message || errorMessage;
            } catch (e) {
              errorMessage = xhr.responseText || errorMessage;
            }
            
            Swal.fire({
              icon: 'error',
              title: 'Connection Error',
              text: errorMessage,
              confirmButtonColor: '#800000'
            });
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

  $("#btn-cancel-emcf").on('click', function() {
    const uid = $("#emcf-invoice-uid").val();
    
    if (!uid) {
      $("#emcf-modal").modal('hide');
      return;
    }
    
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

$(document).on('click', '.editBtn', function() {
    const id = $(this).data('id');
    isEditMode = true;
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getInvoice',
      method: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          const inv = res.data;
          
          $('#invoice_id').val(inv.id);
          $('#formAction').val('update');
          $('#formTitle').text('Edit Import Invoice');
          $('#submitBtnText').text('Update Invoice');
          
          if (inv.payment_method) {
            $('#payment_method').val(inv.payment_method);
          }
          
          if (res.items && res.items.length > 0) {
            quotationItemsData = res.items;
          }
          
          if (inv.calculated_sub_total !== undefined) {
            $('#calculated_sub_total').val(inv.calculated_sub_total);
            $('#calculated_vat_amount').val(inv.calculated_vat_amount);
            $('#calculated_total_amount').val(inv.calculated_total_amount);
            $('#calculated_total_cdf').val(inv.calculated_total_cdf);
            $('#items_manually_edited').val(inv.items_manually_edited || '0');
          }
          
          if (inv.hidden_categories) {
            try {
              hiddenCategoryIndices = JSON.parse(inv.hidden_categories);
              $('#hidden_categories').val(inv.hidden_categories);
            } catch(e) {
              hiddenCategoryIndices = [];
              $('#hidden_categories').val('[]');
            }
          }
          
          if (inv.first_categoty_edited) {
            $('#first_categoty_edited').val(inv.first_categoty_edited);
          }
          
          if (inv.mca_ids) {
            selectedMCAIds = inv.mca_ids.split(',').map(id => parseInt(id)).filter(id => id > 0);
            $('#mca_ids').val(inv.mca_ids);
          } else {
            selectedMCAIds = [];
          }
          
          if (inv.license_ids) {
            selectedLicenseIds = inv.license_ids.split(',').map(id => parseInt(id)).filter(id => id > 0);
            $('#license_ids').val(inv.license_ids);
          } else {
            selectedLicenseIds = [];
          }
          
          if (inv.m3 !== undefined && inv.m3 !== null) {
            $('#m3').val(formatNumber(inv.m3, 2));
          }
          
          if (inv.kind_id) $('#kind_id').val(inv.kind_id);
          if (inv.goods_type_id) $('#goods_type_id').val(inv.goods_type_id);
          if (inv.transport_mode_id) $('#transport_mode_id').val(inv.transport_mode_id);
          
          $('#client_id').val(inv.client_id);
          
          $.ajax({
            url: CONTROLLER_URL + '/crudData/getLicenses',
            method: 'GET',
            data: { 
              client_id: inv.client_id,
              current_invoice_id: inv.id
            },
            dataType: 'json',
            success: function(licenseRes) {
              if (licenseRes.success && licenseRes.data && licenseRes.data.length > 0) {
                displayLicenseDropdown(licenseRes.data);
                
                if (selectedLicenseIds.length > 0) {
                  selectedLicenseIds.forEach(function(licenseId) {
                    $('.license-checkbox[value="' + licenseId + '"]').prop('checked', true);
                  });
                  
                  updateLicenseDropdownText();
                }
                
                if (selectedLicenseIds.length > 0) {
                  $.ajax({
                    url: CONTROLLER_URL + '/crudData/getMCAReferences',
                    method: 'GET',
                    data: { 
                      client_id: inv.client_id, 
                      license_ids: selectedLicenseIds.join(','),
                      current_invoice_id: inv.id
                    },
                    dataType: 'json',
                    success: function(mcaRes) {
                      if (mcaRes.success && mcaRes.data && mcaRes.data.length > 0) {
                        displayMCADropdown(mcaRes.data);
                        
                        if (selectedMCAIds.length > 0) {
                          selectedMCAIds.forEach(function(mcaId) {
                            $('.mca-checkbox[value="' + mcaId + '"]').prop('checked', true);
                          });
                          
                          updateMCADropdownText();
                          loadMCADetails(selectedMCAIds);
                        }
                      } else {
                        clearMCADropdown();
                      }
                      
                      setTimeout(() => {
                        $.ajax({
                          url: CONTROLLER_URL + '/crudData/getAllQuotationsForClient',
                          method: 'GET',
                          data: { 
                            client_id: inv.client_id,
                            kind_id: inv.kind_id || 0,
                            transport_mode_id: inv.transport_mode_id || 0,
                            goods_type_id: inv.goods_type_id || 0
                          },
                          dataType: 'json',
                          success: function(quotRes) {
                            if (quotRes.success && quotRes.data && quotRes.data.length > 0) {
                              let options = '<option value="">-- Select Quotation --</option>';
                              quotRes.data.forEach(function(quot) {
                                options += `<option value="${quot.id}">${escapeHtml(quot.quotation_ref)}</option>`;
                              });
                              $('#quotationSelector').html(options);
                              
                              if (inv.quotation_id) {
                                $('#quotation_id').val(inv.quotation_id);
                                $('#quotationSelector').val(inv.quotation_id);
                                
                                if (quotationItemsData && quotationItemsData.length > 0) {
                                  setTimeout(() => {
                                    displaySavedItems(quotationItemsData);
                                  }, 300);
                                }
                              }
                            } else {
                              $('#quotationSelector').html('<option value="">-- No Quotations Found --</option>');
                            }
                            
                            setTimeout(() => {
                              Object.keys(inv).forEach(key => {
                                const $field = $(`#${key}`);
                                if ($field.length && inv[key] !== null && inv[key] !== undefined) {
                                  if ($field.attr('type') === 'number') {
                                    const step = $field.attr('step');
                                    let decimals = 2;
                                    if (step === '0.0001') decimals = 4;
                                    else if (step === '0.001') decimals = 3;
                                    else if (step === '0.01') decimals = 2;
                                    else if (step === '1') decimals = 0;
                                    $field.val(formatNumber(inv[key], decimals));
                                  } else {
                                    $field.val(inv[key]);
                                  }
                                }
                              });
                              
                              if (inv.cif_cdf !== undefined && inv.cif_cdf !== null) {
                                $('#cif_cdf').val(formatNumber(inv.cif_cdf, 2));
                                $('#cif_cdf_display').val(formatNumber(inv.cif_cdf, 2));
                              }
                              
                              // Generate Tally Ref from Invoice Ref in edit mode
                              if (inv.invoice_ref) {
                                generateTallyRef(inv.invoice_ref);
                              }
                              
                              // If tally_ref exists in database, use it instead
                              if (inv.tally_ref) {
                                $('#tally_ref').val(inv.tally_ref);
                              }
                              
                              const goodsTypeId = parseInt(inv.goods_type_id || 0);
                              if (goodsTypeId === 3) {
                                $('#m3Section').removeClass('hidden-field');
                              } else {
                                $('#m3Section').addClass('hidden-field');
                              }
                              
                              const transportModeId = parseInt(inv.transport_mode_id || 0);
                              $('#roadTransportSection, #railTransportSection, #airTransportSection').addClass('hidden-field');
                              
                              if (transportModeId === 1) {
                                $('#roadTransportSection').removeClass('hidden-field');
                              } else if (transportModeId === 2) {
                                $('#airTransportSection').removeClass('hidden-field');
                              } else if (transportModeId === 3) {
                                $('#railTransportSection').removeClass('hidden-field');
                              }
                              
                              calculateFinancials();
                              updateRLSPercentage();
                              
                              $('#collapseInvoice').collapse('show');
                              $('html, body').animate({ 
                                scrollTop: $('#invoiceForm').offset().top - 100 
                              }, 500);
                              
                              setTimeout(() => {
                                isEditMode = false;
                              }, 1000);
                              
                            }, 500);
                          },
                          error: function(xhr, status, error) {
                            $('#quotationSelector').html('<option value="">-- Error Loading Quotations --</option>');
                            isEditMode = false;
                          }
                        });
                      }, 500);
                      
                    },
                    error: function(xhr, status, error) {
                      clearMCADropdown();
                      isEditMode = false;
                    }
                  });
                } else {
                  isEditMode = false;
                }
                
              } else {
                $('#licenseDropdownMenu').html('<li class="dropdown-item-text text-center text-warning py-3"><i class="ti ti-alert-triangle me-1"></i> No licenses found</li>');
                isEditMode = false;
              }
            },
            error: function(xhr, status, error) {
              isEditMode = false;
            }
          });
          
        } else {
          Swal.fire({ 
            icon: 'error', 
            title: 'Error', 
            text: 'Failed to load invoice data' 
          });
          isEditMode = false;
        }
      },
      error: function(xhr, status, error) {
        Swal.fire({ 
          icon: 'error', 
          title: 'Error', 
          text: 'Failed to load invoice' 
        });
        isEditMode = false;
      }
    });
  });

  initDataTable();
  updateStatistics();
  // ⭐ REMOVED: No default values for exchange rates
  // $('#rate_cdf_inv, #rate_cdf_usd_bcc').val('2500');
  $('#produit').val('Default Commodity');
  $('#payment_method').val('CREDIT');
  updateSummaryTotals(0, 0, 0);
  $('.filter-card[data-filter="all"]').addClass('active-filter');
});
</script>