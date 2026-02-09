<?php
/* View: tracking/exports.php - Complete Export Management View - PRODUCTION READY */
?>
<!-- <link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> -->

<style>
  .dataTables_wrapper .dataTables_info { float: left; }
  .dataTables_wrapper .dataTables_paginate { float: right; text-align: right; }
  
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
  
  .btn-export {
    background: #28a745; color: white; border: none;
  }
  .btn-export:hover {
    background: #218838; color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
  }
  
  .btn-bulk-update {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white; border: none; font-weight: 500;
  }
  .btn-bulk-update:hover {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    color: white; transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.4);
  }
  .btn-bulk-update:disabled {
    background: #95a5a6 !important;
    cursor: not-allowed; opacity: 0.6;
  }
  
  .text-danger { color: #dc3545; font-weight: bold; }
  .is-invalid { border-color: #dc3545 !important; }
  .invalid-feedback { display: block; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }
  
  .btn-view {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; border: none;
  }
  .btn-view:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white; transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
  }
  
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
  .icon-red { background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%); }
  .icon-purple { background: linear-gradient(135deg, #9B59B6 0%, #8E44AD 100%); }
  .icon-cyan { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
  .icon-pink { background: linear-gradient(135deg, #E91E63 0%, #C2185B 100%); }
  .icon-teal { background: linear-gradient(135deg, #1ABC9C 0%, #16A085 100%); }
  .icon-indigo { background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); }
  .icon-amber { background: linear-gradient(135deg, #FFC107 0%, #FF9800 100%); }
  .icon-lime { background: linear-gradient(135deg, #8BC34A 0%, #689F38 100%); }
  .icon-brown { background: linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%); }
  
  .stats-value {
    font-size: 1.4rem; font-weight: 700; color: #2C3E50;
    margin-bottom: 2px; line-height: 1.2;
  }
  .stats-label {
    font-size: 0.75rem; color: #7F8C8D;
    font-weight: 500; line-height: 1.2;
  }
  
  .stats-card .card-body::after {
    content: ""; display: table; clear: both;
  }
  
  .modal-content { border: none; border-radius: 15px; overflow: hidden; }
  .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; border: none; padding: 20px 30px;
  }
  .modal-header .btn-close { filter: brightness(0) invert(1); }
  
  .auto-generated-field { background-color: #f8f9fa; cursor: not-allowed; }
  .readonly-field { background-color: #e9ecef; cursor: not-allowed; }
  
  .accordion-button:not(.collapsed) { background-color: #667eea; color: white; }
  
  .accordion-body {
    padding: 0.75rem;
  }
  
  /* 5 COLUMNS PER ROW - EQUAL WIDTH */
  .col-5-per-row {
    flex: 0 0 auto;
    width: 20%;
    padding-right: 4px;
    padding-left: 4px;
  }
  
  @media (max-width: 1400px) {
    .col-5-per-row { width: 25%; }
  }
  
  @media (max-width: 992px) {
    .col-5-per-row { width: 33.333%; }
  }
  
  @media (max-width: 768px) {
    .col-5-per-row { width: 50%; }
  }
  
  @media (max-width: 576px) {
    .col-5-per-row { width: 100%; }
  }
  
  .form-control, .form-select {
    height: 38px;
    font-size: 0.875rem;
  }
  
  .form-label {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.15rem;
    color: #495057;
  }
  
  .mb-3 {
    margin-bottom: 0.5rem !important;
  }

  .row {
    margin-left: -4px;
    margin-right: -4px;
    margin-bottom: 0.25rem;
    display: flex;
    flex-wrap: wrap;
  }
  
  .row:last-child {
    margin-bottom: 0;
  }

  .filter-indicator {
    position: absolute; top: 8px; right: 8px;
    background: #007bff; color: white; border-radius: 50%;
    width: 20px; height: 20px; display: none;
    align-items: center; justify-content: center;
    font-size: 10px; font-weight: bold;
  }
  .stats-card.active .filter-indicator { display: flex; }
  
  .dataTables_wrapper .dataTables_scroll {
    overflow-x: auto;
  }
  
  .dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto;
  }

  #exportsTable {
    width: 100% !important;
  }

  #exportsTable th, #exportsTable td {
    white-space: nowrap;
    padding: 8px 12px;
  }
  
  /* ✅ Multi-Select Client Dropdown Styles */
  #clientDropdownBtn {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-overflow: ellipsis;
    overflow: hidden;
  }
  
  #clientDropdownMenu {
    max-height: 400px;
    overflow-y: auto;
    min-width: 280px;
    padding: 10px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  
  #clientDropdownMenu .form-check {
    padding: 8px 10px;
    margin: 2px 0;
    border-radius: 4px;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
  }
  
  #clientDropdownMenu .form-check:hover {
    background-color: #e9ecef;
  }
  
  #clientDropdownMenu .form-check-input {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    margin-top: 0;
    cursor: pointer;
    flex-shrink: 0;
  }
  
  #clientDropdownMenu .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
  }
  
  #clientDropdownMenu .form-check-label {
    cursor: pointer;
    font-size: 0.9rem;
    margin-bottom: 0;
    flex-grow: 1;
  }
  
  #clientCountBadge {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 10px;
  }
  
  .client-filter-checkbox:checked + .form-check-label {
    font-weight: 600;
    color: #667eea;
  }
  
  #selectAllClientsBtn, #clearAllClientsBtn {
    font-size: 0.85rem;
    text-decoration: none;
    color: #667eea;
  }
  
  #selectAllClientsBtn:hover, #clearAllClientsBtn:hover {
    text-decoration: underline;
  }
  
  .client-list-container {
    max-height: 300px;
    overflow-y: auto;
  }
  
  .bulk-create-info {
    background: #d1ecf1;
    border: 1px solid #17a2b8;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    text-align: center;
  }
  
  .bulk-create-info p {
    margin-bottom: 0;
    font-size: 0.95rem;
  }
  
  .bulk-create-info .text-warning {
    margin-top: 8px;
  }
  
  #numEntriesInput {
    width: 100%;
    font-weight: bold;
    font-size: 1rem;
  }
  
  .bulk-create-summary {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
  }
  
  .bulk-create-summary h6 {
    color: #856404;
    margin-bottom: 10px;
    font-weight: 600;
  }

  .section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 15px;
    border-radius: 8px;
    margin-top: 10px;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 0.95rem;
  }
  
  .section-header:first-child {
    margin-top: 0;
  }

  .edit-only-section {
    display: none;
  }
  
  .edit-only-section.show {
    display: block;
  }

  #bulkUpdateModal .modal-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  }
  
  .bulk-update-table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
  }
  
  .bulk-update-table thead th {
    background: #667eea;
    color: white;
    padding: 12px 8px;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
    text-align: left;
  }
  
  .bulk-update-table tbody tr {
    border-bottom: 1px solid #dee2e6;
    transition: background 0.2s;
  }
  
  .bulk-update-table tbody tr:hover {
    background: #f8f9fa;
  }
  
  .bulk-update-table tbody tr.selected {
    background: #e7f3ff;
  }
  
  .bulk-update-table td {
    padding: 10px 8px;
    vertical-align: middle;
  }
  
  .bulk-update-table .form-control,
  .bulk-update-table .form-select {
    font-size: 0.85rem;
    padding: 6px 10px;
    height: auto;
    width: 100%;
  }
  
  .bulk-update-table .form-check-input {
    width: 20px;
    height: 20px;
    cursor: pointer;    
  }
  
  .mca-ref-badge {
    background: #667eea;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
  }
  
  .loading-date-text {
    color: #6c757d;
    font-size: 0.75rem;
    display: block;
    margin-top: 2px;
  }
  
  .bulk-table-container {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
  }

  #bulkInsertModal .modal-dialog {
    max-width: 98%;
    width: 98%;
    margin: 1rem auto;
  }

  #bulkInsertModal .modal-body {
    padding: 20px;
    max-height: calc(100vh - 250px);
    overflow-y: auto;
  }

  .bulk-insert-scrollable {
    overflow-x: auto;
    overflow-y: visible;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    position: relative;
  }

  .bulk-insert-table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
    margin: 0;
  }

  .bulk-insert-table thead th {
    background: #667eea;
    color: white;
    padding: 12px 10px;
    font-weight: 600;
    border: 1px solid #5568d3;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.8rem;
    white-space: nowrap;
    min-width: 140px;
  }

  .bulk-insert-table tbody td {
    padding: 8px 10px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    background: white;
  }

  .bulk-insert-table .form-control,
  .bulk-insert-table .form-select {
    font-size: 0.8rem;
    padding: 6px 10px;
    height: 36px;
    min-width: 130px;
    width: 100%;
  }

  .bulk-insert-table input[type="date"] {
    font-size: 0.75rem;
    min-width: 150px;
  }

  .bulk-insert-table input[type="number"] {
    text-align: right;
    min-width: 110px;
  }

  .bulk-insert-table input[type="text"] {
    min-width: 130px;
  }

  .row-number {
    background: #f8f9fa;
    font-weight: 600;
    text-align: center;
    min-width: 50px !important;
    position: sticky;
    left: 0;
    z-index: 5;
    border-right: 2px solid #667eea !important;
  }

  .mca-ref-cell {
    position: sticky;
    left: 50px;
    z-index: 5;
    background: white;
    border-right: 2px solid #667eea !important;
    min-width: 180px !important;
  }

  .bulk-insert-table thead th:first-child,
  .bulk-insert-table thead th:nth-child(2) {
    position: sticky;
    z-index: 15;
    background: #667eea;
  }

  .bulk-insert-table thead th:first-child {
    left: 0;
  }

  .bulk-insert-table thead th:nth-child(2) {
    left: 50px;
  }

 .auto-propagate-field {
  border: 2px solid #28a745 !important;
  background-color: #d4edda !important;
  transition: all 0.3s ease;
}
  
  .date-validation-error {
    border: 2px solid #dc3545 !important;
    background-color: #ffe6e6 !important;
  }

  .seal-input-group {
    display: flex;
    width: 100%;
  }

  .seal-input-group input.form-control {
    flex: 1;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    height: 38px;
  }

  .seal-input-group .btn-select-seals {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: 1px solid #28a745;
    border-left: 1px solid #28a745;
    padding: 0;
    margin-left: -1px;
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-top-right-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
    font-weight: 700;
    transition: all 0.3s;
    font-size: 1.3rem;
    line-height: 1;
    min-width: 40px;
    width: 40px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
  }
  
  .seal-input-group .btn-select-seals:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    color: white;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
  }

  .transport-conditional-field {
    display: none !important;
  }

  .transport-conditional-field.show {
    display: flex !important;
  }

  .select2-container {
    width: 100% !important;
  }

  #sealSelectionModal {
    z-index: 9999 !important;
  }

  #sealSelectionModal .modal-backdrop {
    z-index: 9998 !important;
  }

  .seal-checkbox-item {
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    transition: background 0.2s;
  }

  .seal-checkbox-item:hover {
    background: #f8f9fa;
  }

  .seal-checkbox-item input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .seal-checkbox-item label {
    margin: 0;
    cursor: pointer;
    font-size: 0.9rem;
    user-select: none;
  }

  #sealSelectionModal .modal-body {
    max-height: 400px;
    overflow-y: auto;
  }

  .auto-calculated-field {
    background-color: #e7f3ff;
    cursor: not-allowed;
    font-weight: 600;
    color: #0056b3;
  }
  
  .ogefrem-calculated-field {
    background-color: #fff3e0;
    cursor: not-allowed;
    font-weight: 600;
    color: #e65100;
  }
  
  .exceeded-warning {
    border: 2px solid #dc3545 !important;
    background-color: #ffe6e6 !important;
  }

  .cgea-fixed-field {
    background-color: #f0f0f0;
    cursor: not-allowed;
    font-weight: 600;
  }
  
  .wagon-field {
    display: none;
  }
  
  .wagon-field.show {
    display: flex !important;
  }

  .advanced-filters-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
  }

  .advanced-filters-card h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-apply-filters {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 500;
  }

  .btn-apply-filters:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
  }

  .license-info-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    display: none;
  }

  .license-info-box.show {
    display: block;
  }

  .license-info-box h6 {
    color: white;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 0.95rem;
  }

  .license-info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.9rem;
  }

  .license-info-label {
    font-weight: 500;
  }

  .license-info-value {
    font-weight: 700;
  }

  .license-info-value.warning {
    color: #ffc107;
  }

  .license-info-value.danger {
    color: #ff6b6b;
  }

  .license-info-value.success {
    color: #51cf66;
  }
  
  .bulk-road-only {
    display: table-cell;
  }
  
  .bulk-wagon-only {
    display: table-cell;
  }
  
  .hide-column {
    display: none !important;
  }
</style>
<?php
  $displayByRole = '';
  $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
  $currentPath = preg_replace('#^malabar/#', '', $currentPath);

  $currentMenuId = 0;
  foreach ($_SESSION['menu_permissions'] as $menuId => $perm) {
    if (($perm['menu_key'] ?? '') === $currentPath) {
      $currentMenuId = (int)$menuId;
      break;
    }
  }

  $displayByRole = (
    ($_SESSION['menu_permissions'][$currentMenuId]['can_add'] ?? 0) == 0
  ) ? "style='display: none;'" : '';
?>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- Statistics Cards with Icons - 13 Cards -->
        <!-- <div class="row mb-4">
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="all">
              <div class="card-body">
                <div class="stats-card-icon icon-blue">
                  <i class="ti ti-truck-delivery"></i>
                </div>
                <div class="stats-value" id="totalTrackings">0</div>
                <div class="stats-label">Total Exports</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="completed">
              <div class="card-body">
                <div class="stats-card-icon icon-green">
                  <i class="ti ti-circle-check"></i>
                </div>
                <div class="stats-value" id="totalCompleted">0</div>
                <div class="stats-label">Completed</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="in_progress">
              <div class="card-body">
                <div class="stats-card-icon icon-orange">
                  <i class="ti ti-loader"></i>
                </div>
                <div class="stats-value" id="totalInProgress">0</div>
                <div class="stats-label">In Progress</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="in_transit">
              <div class="card-body">
                <div class="stats-card-icon icon-gray">
                  <i class="ti ti-package"></i>
                </div>
                <div class="stats-value" id="totalInTransit">0</div>
                <div class="stats-label">In Transit</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="ceec_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-purple">
                  <i class="ti ti-file-certificate"></i>
                </div>
                <div class="stats-value" id="totalCEECPending">0</div>
                <div class="stats-label">CEEC Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="min_div_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-cyan">
                  <i class="ti ti-file-alert"></i>
                </div>
                <div class="stats-value" id="totalMinDivPending">0</div>
                <div class="stats-label">Min Div Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="gov_docs_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-pink">
                  <i class="ti ti-file-text"></i>
                </div>
                <div class="stats-value" id="totalGovDocsPending">0</div>
                <div class="stats-label">Gov Docs Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="audited_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-teal">
                  <i class="ti ti-calendar-check"></i>
                </div>
                <div class="stats-value" id="totalAuditedPending">0</div>
                <div class="stats-label">Audited Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="archived_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-indigo">
                  <i class="ti ti-archive"></i>
                </div>
                <div class="stats-value" id="totalArchivedPending">0</div>
                <div class="stats-label">Archived Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="dgda_in_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-amber">
                  <i class="ti ti-calendar-event"></i>
                </div>
                <div class="stats-value" id="totalDGDAInPending">0</div>
                <div class="stats-label">DGDA In Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="liquidation_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-lime">
                  <i class="ti ti-receipt"></i>
                </div>
                <div class="stats-value" id="totalLiquidationPending">0</div>
                <div class="stats-label">Liquidation Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="quittance_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-red">
                  <i class="ti ti-file-invoice"></i>
                </div>
                <div class="stats-value" id="totalQuittancePending">0</div>
                <div class="stats-label">Quittance Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="dispatch_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-brown">
                  <i class="ti ti-truck"></i>
                </div>
                <div class="stats-value" id="totalDispatchPending">0</div>
                <div class="stats-label">Dispatch Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="seal_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-orange">
                  <i class="ti ti-lock"></i>
                </div>
                <div class="stats-value" id="totalSealPending">0</div>
                <div class="stats-label">Seal Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
        </div> -->
        <div class="row">
        <?php if (!empty($cards)): ?>
            <?php foreach ($cards as $card):?>

                <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
                  <div class="card stats-card shadow-sm"
                      data-filter="<?= htmlspecialchars($card->card_key) ?>">

                    <div class="card-body">
                      <div class="stats-card-icon icon-<?= htmlspecialchars($card->card_color) ?>">
                        <i class="<?= htmlspecialchars($card->card_icon) ?>"></i>
                      </div>

                      <div class="stats-value" id="<?= htmlspecialchars($card->card_content_id) ?>">0
                      </div>

                      <div class="stats-label">
                        <?= htmlspecialchars($card->card_subtitle) ?>
                      </div>

                      <div class="filter-indicator">✓</div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No cards available</p>
        <?php endif; ?>
        </div>

        <!-- Export Form Card -->
        <div class="card shadow-sm mb-4">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-file-export me-2"></i> <span id="formTitle">Create New Exports</span></h4>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-export" id="exportAllBtn">
                <i class="ti ti-file-spreadsheet me-1"></i> Export ALL to Excel
              </button>
              <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn" style="display:none;">
                <i class="ti ti-plus"></i> Add New
              </button>
            </div>
          </div>

          <div class="card-body" <?= $displayByRole ?>>
            <form id="exportForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="export_id" id="export_id" value="">
              <input type="hidden" name="action" id="formAction" value="insert">
              <input type="hidden" name="dgda_seal_ids" id="dgda_seal_ids" value="">

              <div class="accordion" id="exportAccordion">
                
                <div class="accordion-item mb-3">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#exportDetailsSection">
                      <i class="ti ti-file-export me-2"></i> Export Details
                    </button>
                  </h2>

                  <div id="exportDetailsSection" class="accordion-collapse collapse show" data-bs-parent="#exportAccordion">
                    <div class="accordion-body">

                      <!-- SECTION 1: DOCUMENTATION -->
                      <div class="section-header">
                        <i class="ti ti-file-text me-2"></i> Documentation
                      </div>

                      <!-- ROW 1 -->
                      <div class="row">
                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Client <span class="text-danger">*</span></label>
                          <select name="subscriber_id" id="subscriber_id" class="form-select common-field" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($subscribers as $sub): ?>
                              <option value="<?= $sub['id'] ?>" data-liquidation="<?= $sub['liquidation_paid_by'] ?? '' ?>"><?= $sub['short_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">License Number <span class="text-danger">*</span></label>
                          <select name="license_id" id="license_id" class="form-select common-field" required>
                            <option value="">-- Select --</option>
                          </select>
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Kind</label>
                          <input type="hidden" name="kind" id="kind_hidden" class="common-field">
                          <input type="text" id="kind_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Type of Goods</label>
                          <input type="hidden" name="type_of_goods" id="type_of_goods_hidden" class="common-field">
                          <input type="text" id="type_of_goods_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Transport Mode</label>
                          <input type="hidden" name="transport_mode" id="transport_mode_hidden" class="common-field">
                          <input type="text" id="transport_mode_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>
                      </div>

                      <!-- ROW 2 -->
                      <div class="row">
                        <div class="col-5-per-row mb-3">
                          <label class="form-label">MCA Ref <span class="text-danger">*</span></label>
                          <input type="text" name="mca_ref" id="mca_ref" class="form-control auto-generated-field common-field" required readonly placeholder="Auto-generated">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Currency</label>
                          <input type="hidden" name="currency" id="currency_hidden" class="common-field">
                          <input type="text" id="currency_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Buyer</label>
                          <input type="text" name="buyer" id="buyer" class="form-control readonly-field common-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Regime <span class="text-danger">*</span></label>
                          <select name="regime" id="regime" class="form-select common-field" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($regimes as $regime): ?>
                              <option value="<?= $regime['id'] ?>"><?= $regime['regime_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Types of Clearance <span class="text-danger">*</span></label>
                          <select name="types_of_clearance" id="types_of_clearance" class="form-select common-field" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($clearance_types as $type): ?>
                              <option value="<?= $type['id'] ?>" <?= ($type['id'] == 1) ? 'selected' : '' ?>><?= $type['clearance_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>

                      <!-- ROW 3 - LICENSE INFO -->
                      <div class="row">
                        <div class="col-5-per-row mb-3">
                          <label class="form-label">License Weight (MT)</label>
                          <input type="text" id="license_weight_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">License FOB</label>
                          <input type="text" id="license_fob_display" class="form-control readonly-field" readonly placeholder="From License">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Remaining Weight (MT)</label>
                          <input type="text" id="remaining_weight_display" class="form-control readonly-field" readonly placeholder="0.00" style="font-weight: 700; color: #28a745;">
                        </div>

                        <div class="col-5-per-row mb-3">
                          <label class="form-label">Remaining FOB</label>
                          <input type="text" id="remaining_fob_display" class="form-control readonly-field" readonly placeholder="0.00" style="font-weight: 700; color: #28a745;">
                        </div>

                        <div class="col-5-per-row mb-3" id="numEntriesContainer">
                          <label class="form-label">Number of Entries <span class="text-danger">*</span></label>
                          <input type="number" id="numEntriesInput" class="form-control" min="1" max="100" value="1">
                        </div>
                      </div>

                      <!-- ROW 4 - PROCEED BUTTON -->
                      <div class="row">
                        <div class="col-12">
                          <button type="button" class="btn btn-primary w-100" id="proceedBulkBtn">
                            <i class="ti ti-arrow-right me-1"></i> Proceed to Create Exports
                          </button>
                        </div>
                      </div>

                      <!-- EDIT MODE ONLY FIELDS -->
                      <div class="edit-only-section" id="documentationRestFields">
                        
                        <!-- ROW 5 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Invoice</label>
                            <input type="text" name="invoice" id="invoice" class="form-control" maxlength="255">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">PO Ref</label>
                            <input type="text" name="po_ref" id="po_ref" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Weight (MT) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="weight" id="weight" class="form-control" min="0">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">FOB</label>
                            <input type="number" step="0.01" name="fob" id="fob" class="form-control" min="0">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Transporter</label>
                            <input type="text" name="transporter" id="transporter" class="form-control" maxlength="255">
                          </div>
                        </div>

                        <!-- ROW 6 - ROAD FIELDS + CONTAINER (✅ UPDATED) -->
                       <div class="row transport-conditional-field" id="road_fields_row">
  <div class="col-5-per-row mb-3">
    <label class="form-label">Horse</label>
    <input type="text" name="horse" id="horse" class="form-control" maxlength="100">
  </div>

  <div class="col-5-per-row mb-3">
    <label class="form-label">Trailer 1</label>
    <input type="text" name="trailer_1" id="trailer_1" class="form-control" maxlength="100">
  </div>

  <div class="col-5-per-row mb-3">
    <label class="form-label">Trailer 2</label>
    <input type="text" name="trailer_2" id="trailer_2" class="form-control" maxlength="100">
  </div>
</div>

<!-- ROW 6B - FEET CONTAINER & CONTAINER (ALWAYS VISIBLE) -->
<div class="row">
  <div class="col-5-per-row mb-3">
    <label class="form-label">Feet Container</label>
    <select name="feet_container" id="feet_container" class="form-select">
      <option value="">-- Select --</option>
      <?php foreach ($feet_containers as $fc): ?>
        <option value="<?= htmlspecialchars($fc['id'], ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($fc['feet_container_size'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-5-per-row mb-3">
    <label class="form-label">Container</label>
    <input type="text" name="container" id="container" class="form-control" maxlength="100">
  </div>
</div>

                        <!-- ROW 7 - ROAD Additional -->
                        <div class="row transport-conditional-field" id="road_fields_row2">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Site of Loading</label>
                            <select name="site_of_loading_id" id="site_of_loading_id" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($loading_sites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= $site['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" id="destination" class="form-control" maxlength="255">
                          </div>
                        </div>

                        <!-- ROW 8 - AIR/WAGON FIELDS -->
                        <div class="row transport-conditional-field wagon-field" id="wagon_fields_row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Wagon Reference</label>
                            <input type="text" name="wagon_ref" id="wagon_ref" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Container</label>
                            <input type="text" class="form-control container-duplicate" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Site of Loading</label>
                            <select class="form-select site-of-loading-duplicate">
                              <option value="">-- Select --</option>
                              <?php foreach ($loading_sites as $site): ?>
                                <option value="<?= $site['id'] ?>"><?= $site['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" class="form-control destination-duplicate" maxlength="255">
                          </div>
                        </div>

                        <!-- ROW 9 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Loading Date</label>
                            <input type="date" name="loading_date" id="loading_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">PV Date</label>
                            <input type="date" name="pv_date" id="pv_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">BP Date</label>
                            <input type="date" name="bp_date" id="bp_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Demande d'Attestation</label>
                            <input type="date" name="demande_attestation_date" id="demande_attestation_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Assay Date</label>
                            <input type="date" name="assay_date" id="assay_date" class="form-control">
                          </div>
                        </div>

                        <!-- ROW 10 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Lot Number</label>
                            <input type="text" name="lot_number" id="lot_number" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Seal DGDA</label>
                            <div class="seal-input-group">
                              <input type="text" name="dgda_seal_no" id="dgda_seal_no" class="form-control" readonly placeholder="No seals selected">
                              <button type="button" class="btn-select-seals" id="editModeSealBtn" title="Select Seals">+</button>
                            </div>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">No. of Seals</label>
                            <input type="number" name="number_of_seals" id="number_of_seals" class="form-control readonly-field" readonly placeholder="0">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Number of Bags</label>
                            <input type="number" name="number_of_bags" id="number_of_bags" class="form-control" min="0">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Archive Reference</label>
                            <input type="text" name="archive_reference" id="archive_reference" class="form-control" maxlength="255">
                          </div>
                        </div>

                        <!-- ROW 11 - All 5 Amounts including OGEFREM -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">CEEC Amount (USD)</label>
                            <input type="number" step="0.01" name="ceec_amount" id="ceec_amount" class="form-control auto-calculated-field" readonly placeholder="Auto">
                            <small class="text-muted">Weight ≥ 30: 800, else 600</small>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">CGEA Amount (USD)</label>
                            <input type="number" step="0.01" name="cgea_amount" id="cgea_amount" class="form-control cgea-fixed-field" readonly value="80.00">
                            <small class="text-muted">Fixed: 80.00</small>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">OCC Amount (USD)</label>
                            <input type="number" step="0.01" name="occ_amount" id="occ_amount" class="form-control auto-calculated-field" readonly value="250.00">
                            <small class="text-muted">Fixed: 250.00</small>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">LMC Amount (USD)</label>
                            <input type="number" step="0.01" name="lmc_amount" id="lmc_amount" class="form-control auto-calculated-field" readonly placeholder="Auto">
                            <small class="text-muted">Type 8: w×8, Others: w×5</small>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">OGEFREM Amount (USD)</label>
                            <input type="number" step="0.01" name="ogefrem_amount" id="ogefrem_amount" class="form-control ogefrem-calculated-field" readonly placeholder="Auto">
                            <small class="text-muted">Based on Feet Container</small>
                          </div>
                        </div>

                      </div>

                      <!-- SECTION 2: DECLARATION (EDIT MODE ONLY) -->
                      <div class="edit-only-section" id="declarationSection">
                        <div class="section-header">
                          <i class="ti ti-file-certificate me-2"></i> Declaration
                        </div>

                        <!-- ROW 1 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">CEEC In</label>
                            <input type="date" name="ceec_in_date" id="ceec_in_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">CEEC Out</label>
                            <input type="date" name="ceec_out_date" id="ceec_out_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Min Div In</label>
                            <input type="date" name="min_div_in_date" id="min_div_in_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Min Div Out</label>
                            <input type="date" name="min_div_out_date" id="min_div_out_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">CGEA Doc Ref</label>
                            <input type="text" name="cgea_doc_ref" id="cgea_doc_ref" class="form-control" maxlength="100">
                          </div>
                        </div>

                        <!-- ROW 2 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Segues RCV Ref</label>
                            <input type="text" name="segues_rcv_ref" id="segues_rcv_ref" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Segues Date of Payment</label>
                            <input type="date" name="segues_payment_date" id="segues_payment_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Document Status</label>
                            <select name="document_status" id="document_status" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($document_statuses as $status): ?>
                                <option value="<?= $status['id'] ?>"><?= $status['document_status'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Customs Clearing Code</label>
                            <input type="text" name="customs_clearing_code" id="customs_clearing_code" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">DGDA In Date</label>
                            <input type="date" name="dgda_in_date" id="dgda_in_date" class="form-control">
                          </div>
                        </div>

                        <!-- ROW 3 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Declaration Reference</label>
                            <input type="text" name="declaration_reference" id="declaration_reference" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Liquidation Reference</label>
                            <input type="text" name="liquidation_reference" id="liquidation_reference" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Date Liquidation</label>
                            <input type="date" name="liquidation_date" id="liquidation_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Liquidation Paid By</label>
                            <input type="text" name="liquidation_paid_by" id="liquidation_paid_by" class="form-control readonly-field" readonly placeholder="From Client">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Liquidation Amount</label>
                            <input type="number" step="0.01" name="liquidation_amount" id="liquidation_amount" class="form-control" min="0">
                          </div>
                        </div>

                        <!-- ROW 4 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Quittance Reference</label>
                            <input type="text" name="quittance_reference" id="quittance_reference" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Date Quittance</label>
                            <input type="date" name="quittance_date" id="quittance_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">DGDA Out Date</label>
                            <input type="date" name="dgda_out_date" id="dgda_out_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Gov Docs In</label>
                            <input type="date" name="gov_docs_in_date" id="gov_docs_in_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Gov Docs Out</label>
                            <input type="date" name="gov_docs_out_date" id="gov_docs_out_date" class="form-control">
                          </div>
                        </div>

                        <!-- ROW 5 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Declaration Status</label>
                            <select name="clearing_status" id="clearing_status" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($clearing_statuses as $status): ?>
                                <option value="<?= $status['id'] ?>"><?= $status['clearing_status'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>

                      </div>

                      <!-- SECTION 3: LOGISTICS (EDIT MODE ONLY) -->
                      <div class="edit-only-section" id="logisticsSection">
                        <div class="section-header">
                          <i class="ti ti-truck me-2"></i> Logistics
                        </div>

                        <!-- ROW 1 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Dispatch/Deliver Date</label>
                            <input type="date" name="dispatch_deliver_date" id="dispatch_deliver_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Kanyaka Arrival Date</label>
                            <input type="date" name="kanyaka_arrival_date" id="kanyaka_arrival_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Kanyaka Departure Date</label>
                            <input type="date" name="kanyaka_departure_date" id="kanyaka_departure_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Border Arrival</label>
                            <input type="date" name="border_arrival_date" id="border_arrival_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Exit DRC Date</label>
                            <input type="date" name="exit_drc_date" id="exit_drc_date" class="form-control">
                          </div>
                        </div>

                        <!-- ROW 2 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Exit Point</label>
                            <select name="exit_point_id" id="exit_point_id" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($exit_points as $point): ?>
                                <option value="<?= $point['id'] ?>"><?= $point['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">End of Formalities Date</label>
                            <input type="date" name="end_of_formalities_date" id="end_of_formalities_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Truck Status</label>
                            <select name="truck_status" id="truck_status" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($truck_statuses as $status): ?>
                                <option value="<?= $status['id'] ?>"><?= $status['truck_status'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">LMC ID</label>
                            <input type="text" name="lmc_id" id="lmc_id" class="form-control" maxlength="100">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">OGEFREM Inv.Ref.</label>
                            <input type="text" name="ogefrem_inv_ref" id="ogefrem_inv_ref" class="form-control" maxlength="100">
                          </div>
                        </div> 

                        <!-- ROW 3 -->
                        <div class="row">
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">LMC Date</label>
                            <input type="date" name="lmc_date" id="lmc_date" class="form-control">
                          </div>
                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Ogefrem Date</label>
                            <input type="date" name="ogefrem_date" id="ogefrem_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Audited Date</label>
                            <input type="date" name="audited_date" id="audited_date" class="form-control">
                          </div>

                          <div class="col-5-per-row mb-3">
                            <label class="form-label">Archived Date</label>
                            <input type="date" name="archived_date" id="archived_date" class="form-control">
                          </div>
                        </div>
                      <div class="row"><div class="mt-4 mb-3">
                        <h6><i class="ti ti-message-circle me-2"></i>Remarks</h6>
                        <input type="hidden" name="remarks" id="remarks_hidden" value="">
                        
                        <div id="remarksContainer"></div>

                        <button type="button" class="btn btn-sm btn-success" id="addRemarkBtn">
                          <i class="ti ti-plus me-1"></i> Add Remark
                        </button>
                      </div></div>
                      </div>

                    </div>
                  </div>
                </div>

              </div>

              <!-- Form Buttons - EDIT MODE ONLY -->
              <div class="row mt-4" id="singleFormButtons" style="display:none;">
                <div class="col-12 text-end">
                  <button type="button" class="btn btn-secondary" id="cancelBtn">
                    <i class="ti ti-x me-1"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary ms-2" id="submitBtn">
                    <i class="ti ti-check me-1"></i> <span id="submitBtnText">Update Export</span>
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- ADVANCED FILTERS CARD -->
        <div class="card shadow-sm mb-4 advanced-filters-card">
          <h5><i class="ti ti-filter me-2"></i> Advanced Filters</h5>
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label fw-bold">
                <i class="ti ti-users me-1"></i> Filter by Clients
              </label>

              <div class="dropdown w-100">
                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start"
                        type="button"
                        id="clientDropdownBtn"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false">
                  <span id="clientDropdownLabel">All Clients</span>
                  <span class="badge bg-primary ms-2" id="clientCountBadge" style="display:none;">0</span>
                </button>

                <div class="dropdown-menu w-100 p-2" id="clientDropdownMenu">
                  <div class="px-2 mb-2">
                    <button type="button" class="btn btn-sm btn-link p-0" id="selectAllClientsBtn">Select All</button> |
                    <button type="button" class="btn btn-sm btn-link p-0" id="clearAllClientsBtn">Clear All</button>
                  </div>
                  <hr class="dropdown-divider">

                  <div class="client-list-container">
                    <?php foreach ($subscribers as $sub): ?>
                      <div class="form-check" style="padding-left: 20pt !important;">
                        <input class="form-check-input client-filter-checkbox"
                              type="checkbox"
                              id="client_<?= $sub['id'] ?>"
                              value="<?= $sub['id'] ?>"
                              data-client-name="<?= htmlspecialchars($sub['short_name'],ENT_QUOTES) ?>">
                        <label class="form-check-label" for="client_<?= $sub['id'] ?>">
                          <?= htmlspecialchars($sub['short_name'],ENT_QUOTES) ?>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>

              <small class="text-muted">
                <span id="selectedClientCountText">No clients selected - showing all</span>
              </small>
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">License No</label>
              <select id="advancedFilterLicense" class="form-select" disabled>
                <option value="">Select Client First</option>
              </select>
            </div>
            <div class="col-md-2 mb-3">
              <label class="form-label">Transport Mode</label>
              <select id="advancedFilterTransport" class="form-select">
                <option value="">All Transport Modes</option>
                <?php foreach ($transport_modes as $tm): ?>
                  <option value="<?= $tm['id'] ?>"><?= $tm['transport_mode_name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">Start Date</label>
              <input type="date" id="advancedFilterStartDate" class="form-control">
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">End Date</label>
              <input type="date" id="advancedFilterEndDate" class="form-control">
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">&nbsp;</label>
              <button type="button" class="btn btn-apply-filters w-100" id="applyAdvancedFiltersBtn">
                <i class="ti ti-check me-1"></i> Apply Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Exports DataTable -->
        <div class="card shadow-sm">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-list me-2"></i> Exports List</h4>
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-sm btn-bulk-update me-2" id="bulkUpdateBtn" disabled>
                <i class="ti ti-edit me-1"></i> Bulk Update
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="clearFilters">
                <i class="ti ti-filter-off me-1"></i> Clear Filters
              </button>
              <span class="badge bg-primary" id="activeFiltersBadge" style="display: none;">0 Filters Active</span>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="exportsTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>MCA Ref</th>
                    <th>Client</th>
                    <th>License</th>
                    <th>Invoice</th>
                    <th>Loading Date</th>
                    <th>Weight (MT)</th>
                    <th>FOB</th>
                    <th>Clearing Status</th>
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

<!-- Seal Selection Modal -->
<div class="modal fade" id="sealSelectionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-shield-check me-2"></i> Select DGDA Seals
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="sealSearchInput" placeholder="Search seals...">
        </div>
        <div id="sealCheckboxList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="confirmSealSelection">
          <i class="ti ti-check me-1"></i> Confirm Selection
        </button>
      </div>
    </div>
  </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewExportModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-eye me-2"></i> Export Details
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

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-edit me-2"></i> Bulk Update Exports
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="bulk-create-summary">
          <h6><i class="ti ti-info-circle me-2"></i>Filter Summary</h6>
          <p class="mb-0" id="bulkFilterSummary">No filter active</p>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <label class="form-label">Filter by Client</label>
            <select id="bulkUpdateClientFilter" class="form-select">
              <option value="">All Clients</option>
              <?php foreach ($subscribers as $sub): ?>
                <option value="<?= $sub['id'] ?>"><?= $sub['short_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Filter by Transport Mode</label>
            <select id="bulkUpdateTransportModeFilter" class="form-select">
              <option value="">All Transport Modes</option>
              <?php foreach ($transport_modes as $mode): ?>
                <option value="<?= $mode['id'] ?>"><?= $mode['transport_mode_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div id="bulkUpdateContent">
          <p class="text-center text-muted">Loading...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="saveBulkUpdateBtn">
          <i class="ti ti-check me-1"></i> Save All Changes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bulk Insert Modal -->
<div class="modal fade" id="bulkInsertModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-file-plus me-2"></i> New Exports - <span id="bulkEntriesCount">0</span> Entries
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="bulk-create-info">
          <p><strong>License Weight:</strong> <span id="bulk_license_weight">0.00</span> MT | <strong>License FOB:</strong> <span id="bulk_license_fob">0.00</span></p>
          <p><strong>Used Weight:</strong> <span id="bulk_used_weight">0.00</span> MT | <strong>Used FOB:</strong> <span id="bulk_used_fob">0.00</span></p>
          <p><strong>Remaining:</strong> <span id="bulk_remaining_weight" class="text-success">0.00</span> MT | <span id="bulk_remaining_fob" class="text-success">0.00</span> FOB</p>
          <p class="text-warning mb-0"><i class="ti ti-alert-triangle me-1"></i> At least one entry must have weight > 0 to create exports.</p>
        </div>

        <div class="bulk-insert-scrollable">
          <table class="bulk-insert-table" id="bulkInsertTable">
            <thead>
              <tr>
                <th style="min-width: 50px;">#</th>
                <th style="min-width: 180px;">MCA File Ref</th>
                <th style="min-width: 150px;">License Number</th>
                <th style="min-width: 160px;">Loading Date</th>
                <th style="min-width: 160px;">BP Receive Date</th>
                <th style="min-width: 160px;">Site of Loading</th>
                <th style="min-width: 140px;">Destination</th>
                <th class="bulk-road-only" style="min-width: 120px;">Horse</th>
                <th class="bulk-road-only" style="min-width: 120px;">Trailer 1</th>
                <th class="bulk-road-only" style="min-width: 120px;">Trailer 2</th>
                <th style="min-width: 130px;">Feet Container</th>
                <th class="bulk-wagon-only" style="min-width: 130px;">Wagon Reference</th>
                <th style="min-width: 130px;">Container</th>
                <th style="min-width: 150px;">Transporter</th>
                <th style="min-width: 150px;">Exit Point</th>
                <th style="min-width: 130px;">Weight (MT) *</th>
                <th style="min-width: 130px;">FOB *</th>
                <th style="min-width: 120px;">No. of Bags</th>
                <th style="min-width: 130px;">Lot Number</th>
                <th style="min-width: 200px;">Seal DGDA</th>
                <th style="min-width: 120px;">No. of Seals</th>
                <th style="min-width: 130px;">CEEC Amount</th>
                <th style="min-width: 130px;">CGEA Amount</th>
                <th style="min-width: 130px;">OCC Amount</th>
                <th style="min-width: 130px;">LMC Amount</th>
                <th style="min-width: 130px;">OGEFREM Amount</th>
              </tr>
            </thead>
            <tbody id="bulkInsertTableBody">
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="saveBulkInsertBtn">
          <i class="ti ti-check me-1"></i> Create All Exports
        </button>
      </div>
    </div>
  </div>
</div>

<!-- <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->

<script>
  let selectedClientIds = [];
  $(document).ready(function () {

    const csrfToken = $('#exportForm').data('csrf-token');
    const transportModes = <?= json_encode($transport_modes) ?>;
    
    // ✅ CRITICAL FIX: Initialize CEEC In auto-propagation on page load
    // This handles case where user might refresh page while in edit mode
    setTimeout(function() {
      setupCEECInAutoPropagation();
    }, 500);
    // Remarks handlers
      let remarkCounter = 0;

  $('#addRemarkBtn').on('click', () => addRemarkEntry());
  $(document).on('change input', '.remark-date, .remark-text', updateRemarksHidden);
  function addRemarkEntry(date = '', text = '') {
    remarkCounter++;
    const remarkId = `remark_${remarkCounter}`;
    const remarkHtml = `
      <div class="remarks-entry" id="${remarkId}">
        <button type="button" class="btn btn-sm btn-danger btn-remove" onclick="$('#${remarkId}').remove(); updateRemarksHidden();">
          <i class="ti ti-x"></i>
        </button>
        <div class="row">
          <div class="col-md-3 mb-2">
            <label>Date</label>
            <input type="date" class="form-control remark-date" value="${escapeHtml(date)}">
          </div>
          <div class="col-md-9 mb-2">
            <label>Remark Text</label>
            <textarea class="form-control remark-text" rows="2">${escapeHtml(text)}</textarea>
          </div>
        </div>
      </div>
    `;
    $('#remarksContainer').append(remarkHtml);
    updateRemarksHidden();
  }

  $('#clientDropdownMenu').on('click', function(e){
    e.stopPropagation();
  });

  function updateSelectedClients(){
    selectedClientIds = [];
    const names = [];

    $('.client-filter-checkbox:checked').each(function(){
      selectedClientIds.push($(this).val());
      names.push($(this).data('client-name'));
    });

    const count = selectedClientIds.length;

    if(count === 0){
      $('#clientDropdownLabel').text('All Clients');
      $('#clientCountBadge').hide();
      $('#selectedClientCountText').text('No clients selected - showing all');
      // ✅ Disable license dropdown when no client selected
      $('#advancedFilterLicense').html('<option value="">Select Client First</option>').prop('disabled', true);
    }
    else if(count === 1){
      $('#clientDropdownLabel').text(names[0]);
      $('#clientCountBadge').text(1).show();
      $('#selectedClientCountText').text('1 client selected: '+names[0]);
      // ✅ Enable license dropdown for single client
      loadLicensesForSelectedClients();
    }
    else{
      $('#clientDropdownLabel').text(count+' Clients Selected');
      $('#clientCountBadge').text(count).show();
      $('#selectedClientCountText').text(count+' clients selected');
      // ✅ Load licenses for all selected clients (combined)
      loadLicensesForSelectedClients();
    }
    
    // ✅ Auto-filter when checkbox changes - no Apply button needed
    advancedFiltersActive = true;
    if (typeof exportsTable !== 'undefined') {
      exportsTable.ajax.reload();
    }
  }

  // checkbox change
  $(document).on('change','.client-filter-checkbox',updateSelectedClients);

  // select / clear all
  $('#selectAllClientsBtn').on('click',function(e){
    e.preventDefault(); e.stopPropagation();
    $('.client-filter-checkbox').prop('checked',true);
    updateSelectedClients();
  });

  $('#clearAllClientsBtn').on('click',function(e){
    e.preventDefault(); e.stopPropagation();
    $('.client-filter-checkbox').prop('checked',false);
    updateSelectedClients();
  });
  function updateRemarksHidden() {
    const remarks = [];
    $('.remarks-entry').each(function() {
      const date = $(this).find('.remark-date').val();
      const text = $(this).find('.remark-text').val();
      if (date || text) remarks.push({ date, text });
    });
    $('#remarks_hidden').val(JSON.stringify(remarks));
  }


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
    
    function sanitizeNumber(value) {
      const num = parseFloat(value);
      return isNaN(num) || num < 0 ? 0 : num;
    }

    function calculateCEECAmount(weight) {
      const w = parseFloat(weight);
      return (w >= 30.00) ? 800.00 : 600.00;
    }

    function calculateCGEAAmount() {
      return 80.00;
    }

    function calculateOCCAmount() {
      return 250.00;
    }

    function calculateLMCAmount(typeOfGoodsId, weight) {
      const typeId = parseInt(typeOfGoodsId);
      const w = parseFloat(weight);
      
      if (typeId === 8) {
        return w * 8.00;
      }
      
      return w * 5.00;
    }

    function calculateOGEFREMAmount(feetContainerId) {
      const feetId = parseInt(feetContainerId);
      
      switch (feetId) {
        case 1:
          return 50.00;
        case 2:
        case 3:
          return 100.00;
        case 4:
          return 150.00;
        case 5:
          return 30.00;
        default:
          return null;
      }
    }

    // ✅ AUTO-UPDATE DECLARATION STATUS BASED ON DATES
    function updateDeclarationStatus() {
      const dispatchDate = $('#dispatch_deliver_date').val();
      const quittanceDate = $('#quittance_date').val();
      
      if (quittanceDate) {
        $('#clearing_status').val('3');
      } else if (dispatchDate) {
        $('#clearing_status').val('2');
      } else {
        $('#clearing_status').val('1');
      }
    }

    $(document).on('change', '#dispatch_deliver_date, #quittance_date', function() {
      updateDeclarationStatus();
    });

    $(document).on('input change', '#weight, #number_of_bags, #feet_container', function() {
      const weight = parseFloat($('#weight').val()) || 0;
      const numberOfBags = parseInt($('#number_of_bags').val()) || 0;
      const feetContainerId = $('#feet_container').val();
      
      if (weight < 0) {
        $('#weight').val(Math.abs(weight));
      }
      
      if (numberOfBags < 0) {
        $('#number_of_bags').val(Math.abs(numberOfBags));
      }
      
      const finalWeight = Math.abs(weight);
      
      if (finalWeight > 0) {
        const typeOfGoodsId = $('#type_of_goods_hidden').val();
        
        const ceecAmount = calculateCEECAmount(finalWeight);
        const cgeaAmount = calculateCGEAAmount();
        const occAmount = calculateOCCAmount();
        const lmcAmount = calculateLMCAmount(typeOfGoodsId, finalWeight);
        
        $('#ceec_amount').val(ceecAmount.toFixed(2));
        $('#cgea_amount').val(cgeaAmount.toFixed(2));
        $('#occ_amount').val(occAmount.toFixed(2));
        $('#lmc_amount').val(lmcAmount.toFixed(2));
      } else {
        $('#ceec_amount').val('');
        $('#cgea_amount').val('');
        $('#occ_amount').val('250.00');
        $('#lmc_amount').val('');
      }
      
      if (feetContainerId) {
        const ogefremAmount = calculateOGEFREMAmount(feetContainerId);
        $('#ogefrem_amount').val(ogefremAmount ? ogefremAmount.toFixed(2) : '');
      } else {
        $('#ogefrem_amount').val('');
      }
      
      if (isEditMode) {
        updateRemainingLicenseInfo();
      }
    });

    let activeFilters = [];
    let bulkUpdateData = [];
    let isEditMode = false;
    let currentEditExportId = null;
    let loadingSiteOptions = <?= json_encode($loading_sites) ?>;
    let exitPointOptions = <?= json_encode($exit_points) ?>;
    let feetContainerOptions = <?= json_encode($feet_containers) ?>;
    let availableSeals = [];
    let selectedSealIds = [];
    let currentModalContext = null;
    let advancedFiltersActive = false;

    function loadAvailableSeals() {
      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getAvailableSeals',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            availableSeals = res.data || [];
          }
        },
        error: function() {
          console.error('Failed to load available seals');
        }
      });
    }

    function updateTransportConditionalFields() {
      const transportModeId = parseInt($('#transport_mode_hidden').val());
      
      $('#road_fields_row, #road_fields_row2, #wagon_fields_row').removeClass('show');
      $('.road-field, .wagon-field').hide();
      
      $('#horse, #trailer_1, #trailer_2, #feet_container, #wagon_ref, #container').val('');
      
      if (transportModeId === 1) {
        $('#road_fields_row').addClass('show');
        $('#road_fields_row2').addClass('show');
        $('.road-field').show();
        
        const siteVal = $('.site-of-loading-duplicate').val();
        const destVal = $('.destination-duplicate').val();
        if (siteVal) $('#site_of_loading_id').val(siteVal);
        if (destVal) $('#destination').val(destVal);
      } else if (transportModeId === 2 || transportModeId === 3) {
        $('#wagon_fields_row').addClass('show');
        $('.wagon-field').show();
        
        const siteVal = $('#site_of_loading_id').val();
        const destVal = $('#destination').val();
        const containerVal = $('#container').val();
        $('.site-of-loading-duplicate').val(siteVal);
        $('.destination-duplicate').val(destVal);
        $('.container-duplicate').val(containerVal);
      }
    }
    
    $(document).on('change', '#site_of_loading_id', function() {
      $('.site-of-loading-duplicate').val($(this).val());
    });
    
    $(document).on('change', '.site-of-loading-duplicate', function() {
      $('#site_of_loading_id').val($(this).val());
    });
    
    $(document).on('input', '#destination', function() {
      $('.destination-duplicate').val($(this).val());
    });
    
    $(document).on('input', '.destination-duplicate', function() {
      $('#destination').val($(this).val());
    });
    
    $(document).on('input', '#container', function() {
      $('.container-duplicate').val($(this).val());
    });
    
    $(document).on('input', '.container-duplicate', function() {
      $('#container').val($(this).val());
    });

    function validateDatePairs() {
      let isValid = true;
      let errorMsg = '';

      const ceecIn = $('#ceec_in_date').val();
      const ceecOut = $('#ceec_out_date').val();
      if (ceecIn && ceecOut && new Date(ceecOut) < new Date(ceecIn)) {
        $('#ceec_out_date').addClass('date-validation-error');
        errorMsg += 'CEEC Out date cannot be before CEEC In date.<br>';
        isValid = false;
      } else {
        $('#ceec_out_date').removeClass('date-validation-error');
      }

      const minDivIn = $('#min_div_in_date').val();
      const minDivOut = $('#min_div_out_date').val();
      if (minDivIn && minDivOut && new Date(minDivOut) < new Date(minDivIn)) {
        $('#min_div_out_date').addClass('date-validation-error');
        errorMsg += 'Min Div Out date cannot be before Min Div In date.<br>';
        isValid = false;
      } else {
        $('#min_div_out_date').removeClass('date-validation-error');
      }

      const govDocsIn = $('#gov_docs_in_date').val();
      const govDocsOut = $('#gov_docs_out_date').val();
      if (govDocsIn && govDocsOut && new Date(govDocsOut) < new Date(govDocsIn)) {
        $('#gov_docs_out_date').addClass('date-validation-error');
        errorMsg += 'Gov Docs Out date cannot be before Gov Docs In date.<br>';
        isValid = false;
      } else {
        $('#gov_docs_out_date').removeClass('date-validation-error');
      }

      if (!isValid) {
        Swal.fire({
          icon: 'error',
          title: 'Date Validation Error',
          html: errorMsg
        });
      }

      return isValid;
    }

    $('#ceec_out_date, #min_div_out_date, #gov_docs_out_date').on('change', function() {
      validateDatePairs();
    });

// ✅ CEEC In Auto-Propagation - Fixed Implementation
function setupCEECInAutoPropagation() {
  // Unbind any existing handlers to prevent duplicates
  $(document).off('change.ceecin input.ceecin', '#ceec_in_date');
  
  // Check if CEEC In field exists
  const $ceecIn = $('#ceec_in_date');
  
  if ($ceecIn.length === 0) {
    // Field doesn't exist yet, will be bound when edit mode is activated
    return;
  }
  
  // Bind change and input events with namespace for easy management
  $ceecIn.on('change.ceecin input.ceecin', function() {
    const selectedValue = $(this).val();
    
    // Exit if no value
    if (!selectedValue) {
      return;
    }
    
    // Get target fields
    const $pvDate = $('#pv_date');
    const $demandeDate = $('#demande_attestation_date');
    const $assayDate = $('#assay_date');
    
    // Only set values if fields exist and are visible
    if ($pvDate.length && $pvDate.is(':visible')) {
      $pvDate.val(selectedValue).addClass('auto-propagate-field');
    }
    
    if ($demandeDate.length && $demandeDate.is(':visible')) {
      $demandeDate.val(selectedValue).addClass('auto-propagate-field');
    }
    
    if ($assayDate.length && $assayDate.is(':visible')) {
      $assayDate.val(selectedValue).addClass('auto-propagate-field');
    }
    
    // Remove visual feedback after 1 second
    setTimeout(function() {
      $('#pv_date, #demande_attestation_date, #assay_date').removeClass('auto-propagate-field');
    }, 1000);
  });
}

// ✅ Also trigger on input event (more responsive)
$(document).on('input', '#ceec_in_date', function() {
  $(this).trigger('change');
});

    $('#editModeSealBtn').on('click', function() {
      currentModalContext = 'edit';
      const currentSealIds = $('#dgda_seal_ids').val().split(',').filter(id => id);
      
      renderSealSelection(currentSealIds);
      $('#sealSelectionModal').modal('show');
    });

    function renderSealSelection(currentSealIds) {
      const $list = $('#sealCheckboxList');
      $list.empty();

      if (availableSeals.length === 0) {
        $list.html('<p class="text-muted text-center">No seals available</p>');
        return;
      }

      availableSeals.forEach(seal => {
        const isChecked = currentSealIds.includes(seal.id.toString());
        const $item = $(`
          <div class="seal-checkbox-item">
            <input type="checkbox" id="seal_${seal.id}" value="${seal.id}" ${isChecked ? 'checked' : ''}>
            <label for="seal_${seal.id}">${escapeHtml(seal.seal_number)}</label>
          </div>
        `);
        $list.append($item);
      });
    }

    $('#sealSearchInput').on('input', function() {
      const searchTerm = $(this).val().toLowerCase();
      
      if (currentModalContext === 'edit') {
        const currentSealIds = $('#dgda_seal_ids').val().split(',').filter(id => id);
        const filteredSeals = searchTerm 
          ? availableSeals.filter(seal => seal.seal_number.toLowerCase().includes(searchTerm))
          : availableSeals;

        const $list = $('#sealCheckboxList');
        $list.empty();

        if (filteredSeals.length === 0) {
          $list.html('<p class="text-muted text-center">No seals found</p>');
          return;
        }

        filteredSeals.forEach(seal => {
          const isChecked = currentSealIds.includes(seal.id.toString());
          const $item = $(`
            <div class="seal-checkbox-item">
              <input type="checkbox" id="seal_${seal.id}" value="${seal.id}" ${isChecked ? 'checked' : ''}>
              <label for="seal_${seal.id}">${escapeHtml(seal.seal_number)}</label>
            </div>
          `);
          $list.append($item);
        });
      } else if (currentModalContext === 'bulk') {
        const currentSealIds = $(`.seal-ids-hidden[data-row="${currentBulkRow}"]`).val().split(',').filter(id => id);
        const filteredSeals = searchTerm 
          ? availableSeals.filter(seal => seal.seal_number.toLowerCase().includes(searchTerm))
          : availableSeals;

        const $list = $('#sealCheckboxList');
        $list.empty();

        if (filteredSeals.length === 0) {
          $list.html('<p class="text-muted text-center">No seals found</p>');
          return;
        }

        filteredSeals.forEach(seal => {
          const isChecked = currentSealIds.includes(seal.id.toString());
          const $item = $(`
            <div class="seal-checkbox-item">
              <input type="checkbox" id="bulk_seal_${seal.id}" value="${seal.id}" ${isChecked ? 'checked' : ''}>
              <label for="bulk_seal_${seal.id}">${escapeHtml(seal.seal_number)}</label>
            </div>
          `);
          $list.append($item);
        });
      } else if (currentModalContext === 'bulkUpdate') {
        let currentSealIds = [];
        const sealIdsVal = $(`.bulk-seal-ids[data-export-id="${currentBulkUpdateExportId}"]`).val();
        if (sealIdsVal) {
          try {
            currentSealIds = JSON.parse(sealIdsVal);
          } catch(e) {
            currentSealIds = [];
          }
        }
        
        const filteredSeals = searchTerm 
          ? availableSeals.filter(seal => seal.seal_number.toLowerCase().includes(searchTerm))
          : availableSeals;

        const $list = $('#sealCheckboxList');
        $list.empty();

        if (filteredSeals.length === 0) {
          $list.html('<p class="text-muted text-center">No seals found</p>');
          return;
        }

        filteredSeals.forEach(seal => {
          const isChecked = currentSealIds.includes(seal.id.toString());
          const $item = $(`
            <div class="seal-checkbox-item">
              <input type="checkbox" id="bulkupdate_seal_${seal.id}" value="${seal.id}" ${isChecked ? 'checked' : ''}>
              <label for="bulkupdate_seal_${seal.id}">${escapeHtml(seal.seal_number)}</label>
            </div>
          `);
          $list.append($item);
        });
      }
    });

    $('#confirmSealSelection').on('click', function() {
      const selectedSeals = [];
      const selectedIds = [];

      $('#sealCheckboxList input[type="checkbox"]:checked').each(function() {
        const sealId = $(this).val();
        selectedIds.push(sealId);
        
        const seal = availableSeals.find(s => s.id.toString() === sealId);
        if (seal) {
          selectedSeals.push(seal);
        }
      });

      if (currentModalContext === 'edit') {
        updateEditModeSealDisplay(selectedSeals, selectedIds);
      } else if (currentModalContext === 'bulk') {
        updateBulkSealDisplay(currentBulkRow, selectedSeals, selectedIds);
      } else if (currentModalContext === 'bulkUpdate') {
        updateBulkUpdateSealDisplay(currentBulkUpdateExportId, selectedSeals, selectedIds);
      }

      $('#sealSelectionModal').modal('hide');
    });

    // Track current bulk update export ID for seal selection
    let currentBulkUpdateExportId = null;

    // Update seal display for bulk update context
    function updateBulkUpdateSealDisplay(exportId, seals, sealIds) {
      if (seals.length === 0) {
        $(`.bulk-field[data-export-id="${exportId}"][data-field="dgda_seal_no"]`).val('');
        $(`.bulk-field[data-export-id="${exportId}"][data-field="number_of_seals"]`).val('');
        $(`.bulk-seal-ids[data-export-id="${exportId}"]`).val('');
      } else {
        const sealNumbers = seals.map(s => s.seal_number).join(', ');
        $(`.bulk-field[data-export-id="${exportId}"][data-field="dgda_seal_no"]`).val(sealNumbers);
        $(`.bulk-field[data-export-id="${exportId}"][data-field="number_of_seals"]`).val(seals.length);
        $(`.bulk-seal-ids[data-export-id="${exportId}"]`).val(JSON.stringify(sealIds));
      }
    }

   function updateEditModeSealDisplay(seals, sealIds) {
  if (seals.length === 0) {
    $('#dgda_seal_no').val('');
    $('#number_of_seals').val('');
    $('#dgda_seal_ids').val('');
  } else {
    const sealNumbers = seals.map(s => s.seal_number).join(', ');
    $('#dgda_seal_no').val(sealNumbers);
    $('#number_of_seals').val(seals.length);
    
    // ✅ FIX: Always send as JSON array for consistency
    $('#dgda_seal_ids').val(JSON.stringify(sealIds));
  }
}

    $('#applyAdvancedFiltersBtn').on('click', function() {
      advancedFiltersActive = true;
      if (typeof exportsTable !== 'undefined') {
        exportsTable.ajax.reload();
      }
    });

   $('#exportAllBtn').on('click', function() {
  let exportUrl = '<?= APP_URL ?>/export/crudData/exportAll';
  
  const params = new URLSearchParams();
  
  // ✅ Collect all filters
  if (selectedClientIds.length > 0) {
    selectedClientIds.forEach(id => {
      params.append('subscriber_id[]', id);
    });
  }
  
  const licenseFilter = $('#advancedFilterLicense').val();
  const transportFilter = $('#advancedFilterTransport').val();
  const startDate = $('#advancedFilterStartDate').val();
  const endDate = $('#advancedFilterEndDate').val();
  
  if (licenseFilter) params.append('license_id', licenseFilter);
  if (transportFilter) params.append('transport_mode', transportFilter);
  if (startDate) params.append('start_date', startDate);
  if (endDate) params.append('end_date', endDate);
  
  if (params.toString()) {
    exportUrl += '?' + params.toString();
  }
  
  // ✅ Show loading indicator
  Swal.fire({
    title: 'Preparing Export...',
    html: 'Please wait while we generate your Excel files.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  
  // ✅ Make AJAX call first to check if multiple files
  $.ajax({
    url: exportUrl,
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.multiple_files) {
        // ✅ Multiple clients - download each file sequentially
        Swal.close();
        
        Swal.fire({
          icon: 'info',
          title: 'Multiple Files Ready',
          html: `<p>You selected <strong>${response.total_clients} clients</strong>.</p>
                 <p>Your browser will download <strong>${response.files.length} Excel files</strong> (one per client).</p>
                 <p class="text-muted mt-2"><small>Downloads will start automatically...</small></p>`,
          confirmButtonText: 'Start Downloads',
          showCancelButton: true,
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            downloadMultipleFiles(response.files);
          }
        });
      } else if (response.success === false) {
        Swal.fire({
          icon: 'error',
          title: 'Export Failed',
          text: response.message || 'Failed to prepare export'
        });
      }
    },
    error: function(xhr) {
      // ✅ Not JSON response = direct file download (single client)
      if (xhr.status === 200 || xhr.responseJSON === undefined) {
        Swal.close();
        window.location.href = exportUrl;
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Export Error',
          text: 'An error occurred while preparing the export.'
        });
      }
    }
  });
});

// ✅ Function to download multiple files sequentially
function downloadMultipleFiles(files) {
  let currentIndex = 0;
  
  Swal.fire({
    title: 'Downloading Files...',
    html: `<p>Downloading file <strong><span id="currentFileNum">1</span> of ${files.length}</strong></p>
           <p class="text-primary"><strong id="currentFileName">${files[0].client_name}</strong></p>
           <p class="text-muted"><small>${files[0].record_count} records</small></p>`,
    allowOutsideClick: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
  
  function downloadNext() {
    if (currentIndex >= files.length) {
      Swal.fire({
        icon: 'success',
        title: 'Downloads Complete!',
        html: `<p>Successfully downloaded <strong>${files.length} Excel files</strong></p>
               <ul style="text-align: left; max-height: 200px; overflow-y: auto;">
                 ${files.map(f => `<li>${f.filename} <span class="text-muted">(${f.record_count} records)</span></li>`).join('')}
               </ul>`,
        confirmButtonText: 'OK'
      });
      return;
    }
    
    const file = files[currentIndex];
    
    // Update progress display
    $('#currentFileNum').text(currentIndex + 1);
    $('#currentFileName').text(file.client_name);
    
    // Create hidden iframe to trigger download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = file.url;
    document.body.appendChild(iframe);
    
    // Wait 2 seconds before next download (browser compatibility)
    setTimeout(() => {
      document.body.removeChild(iframe);
      currentIndex++;
      downloadNext();
    }, 2000);
  }
  
  downloadNext();
}

    $('#proceedBulkBtn').on('click', function() {
      const numEntries = parseInt($('#numEntriesInput').val());
      
      if (numEntries < 1 || numEntries > 100) {
        Swal.fire('Error', 'Number of entries must be between 1 and 100', 'error');
        return;
      }

      const validation = validateCommonFields();
      if (!validation.success) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          html: '<ul style="text-align:left;"><li>' + validation.errors.map(err => escapeHtml(err)).join('</li><li>') + '</li></ul>'
        });
        return;
      }

      generateBulkInsertModal(numEntries);
    });

    function validateCommonFields() {
      let errors = [];
      
      const requiredFields = [
        { id: 'subscriber_id', label: 'Client' },
        { id: 'license_id', label: 'License Number' },
        { id: 'regime', label: 'Regime' },
        { id: 'types_of_clearance', label: 'Types of Clearance' }
      ];

      requiredFields.forEach(field => {
        const value = $(`#${field.id}`).val();
        if (!value || value === '') {
          errors.push(`${field.label} is required`);
        }
      });

      const mcaRef = $('#mca_ref').val();
      if (!mcaRef) {
        errors.push('MCA Reference is required');
      }

      return { success: errors.length === 0, errors };
    }

    function generateBulkInsertModal(numEntries) {
      const baseMCARef = $('#mca_ref').val();
      const licenseNumber = $('#license_id option:selected').text();
      const licenseWeight = parseFloat($('#license_weight_display').val()) || 0;
      const licenseFOB = parseFloat($('#license_fob_display').val()) || 0;
      const transportModeId = parseInt($('#transport_mode_hidden').val());
      const typeOfGoodsId = parseInt($('#type_of_goods_hidden').val());

      const mcaParts = baseMCARef.match(/^(.+-)(\d{4})$/);
      if (!mcaParts) {
        Swal.fire('Error', 'Invalid MCA Reference format. Expected format: XXX-XXXXX-0001', 'error');
        return;
      }

      const mcaPrefix = mcaParts[1];
      const startSequence = parseInt(mcaParts[2]);

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getLicenseUsage',
        method: 'GET',
        data: { license_id: $('#license_id').val() },
        dataType: 'json',
        success: function(res) {
          const usedWeight = res.used_weight || 0;
          const usedFOB = res.used_fob || 0;
          const availableWeight = licenseWeight - usedWeight;
          const availableFOB = licenseFOB - usedFOB;

          $('#bulkEntriesCount').text(numEntries);
          $('#bulk_license_weight').text(licenseWeight.toFixed(3));
          $('#bulk_license_fob').text(licenseFOB.toFixed(2));
          $('#bulk_used_weight').text(usedWeight.toFixed(3));
          $('#bulk_used_fob').text(usedFOB.toFixed(2));
          $('#bulk_remaining_weight').text(availableWeight.toFixed(3)).removeClass('text-danger').addClass('text-success');
          $('#bulk_remaining_fob').text(availableFOB.toFixed(2)).removeClass('text-danger').addClass('text-success');

          if (transportModeId === 1) {
            $('.bulk-road-only').removeClass('hide-column');
            $('.bulk-wagon-only').addClass('hide-column');
          } else if (transportModeId === 2 || transportModeId === 3) {
            $('.bulk-road-only').addClass('hide-column');
            $('.bulk-wagon-only').removeClass('hide-column');
          } else {
            $('.bulk-road-only, .bulk-wagon-only').removeClass('hide-column');
          }

          let tableHTML = '';
          for (let i = 0; i < numEntries; i++) {
            const rowNum = i + 1;
            const sequence = startSequence + i;
            const mcaRef = mcaPrefix + String(sequence).padStart(4, '0');

            tableHTML += `
              <tr data-row="${rowNum}">
                <td class="row-number">${rowNum}</td>
                <td class="mca-ref-cell"><input type="text" class="form-control mca-ref-input" data-row="${rowNum}" readonly value="${escapeHtml(mcaRef)}"></td>
                <td><input type="text" class="form-control" readonly value="${escapeHtml(licenseNumber)}"></td>
                <td><input type="date" class="form-control loading-date-input" data-row="${rowNum}"></td>
                <td><input type="date" class="form-control bp-date-input" data-row="${rowNum}"></td>
                <td>
                  <select class="form-select site-loading-input" data-row="${rowNum}">
                    <option value="">-- Select --</option>
                    ${loadingSiteOptions.map(site => `<option value="${site.id}">${escapeHtml(site.transit_point_name)}</option>`).join('')}
                  </select>
                </td>
                <td><input type="text" class="form-control destination-input" data-row="${rowNum}" maxlength="255"></td>
            `;

            if (transportModeId === 1) {
              tableHTML += `
                <td><input type="text" class="form-control horse-input" data-row="${rowNum}" maxlength="100"></td>
                <td><input type="text" class="form-control trailer1-input" data-row="${rowNum}" maxlength="100"></td>
                <td><input type="text" class="form-control trailer2-input" data-row="${rowNum}" maxlength="100"></td>
              `;
            }

            tableHTML += `
              <td>
                <select class="form-select feet-container-input" data-row="${rowNum}">
                  <option value="">-- Select --</option>
                  ${feetContainerOptions.map(fc => `<option value="${fc.id}">${escapeHtml(fc.feet_container_size)}</option>`).join('')}
                </select>
              </td>
            `;

            if (transportModeId === 2 || transportModeId === 3) {
              tableHTML += `
                <td><input type="text" class="form-control wagon-ref-input" data-row="${rowNum}" maxlength="100"></td>
              `;
            }

            tableHTML += `
                <td><input type="text" class="form-control container-input" data-row="${rowNum}" maxlength="100"></td>
                <td><input type="text" class="form-control transporter-input" data-row="${rowNum}" maxlength="255"></td>
                <td>
                  <select class="form-select exit-point-input" data-row="${rowNum}">
                    <option value="">-- Select --</option>
                    ${exitPointOptions.map(point => `<option value="${point.id}">${escapeHtml(point.transit_point_name)}</option>`).join('')}
                  </select>
                </td>
                <td><input type="number" step="0.01" class="form-control bulk-weight-input" data-row="${rowNum}" min="0" placeholder="0.00"></td>
                <td><input type="number" step="0.01" class="form-control bulk-fob-input" data-row="${rowNum}" min="0" placeholder="0.00"></td>
                <td><input type="number" class="form-control bags-input" data-row="${rowNum}" min="0"></td>
                <td><input type="text" class="form-control lot-input" data-row="${rowNum}" maxlength="100"></td>
                <td>
                  <input type="hidden" class="seal-ids-hidden" data-row="${rowNum}">
                  <div class="seal-input-group">
                    <input type="text" class="form-control seal-display-input" data-row="${rowNum}" readonly placeholder="No seals">
                    <button type="button" class="btn-select-seals bulk-seal-btn" data-row="${rowNum}" title="Select Seals">+</button>
                  </div>
                </td>
                <td><input type="number" class="form-control seals-count-input" data-row="${rowNum}" readonly></td>
                <td><input type="number" step="0.01" class="form-control ceec-amount-input auto-calculated-field" data-row="${rowNum}" readonly placeholder="Auto"></td>
                <td><input type="number" step="0.01" class="form-control cgea-amount-input cgea-fixed-field" data-row="${rowNum}" readonly value="80.00"></td>
                <td><input type="number" step="0.01" class="form-control occ-amount-input auto-calculated-field" data-row="${rowNum}" readonly value="250.00"></td>
                <td><input type="number" step="0.01" class="form-control lmc-amount-input auto-calculated-field" data-row="${rowNum}" readonly placeholder="Auto"></td>
                <td><input type="number" step="0.01" class="form-control ogefrem-amount-input ogefrem-calculated-field" data-row="${rowNum}" readonly placeholder="Auto"></td>
              </tr>
            `;
          }

          $('#bulkInsertTableBody').html(tableHTML);

          $(document).off('click', '.bulk-seal-btn');
          $(document).on('click', '.bulk-seal-btn', function() {
            currentModalContext = 'bulk';
            const rowNum = $(this).data('row');
            currentBulkRow = rowNum;
            const currentSealIds = $(`.seal-ids-hidden[data-row="${rowNum}"]`).val().split(',').filter(id => id);
            
            renderSealSelection(currentSealIds);
            $('#sealSelectionModal').modal('show');
          });

          $(document).off('change', '.loading-date-input');
          $(document).on('change', '.loading-date-input', function() {
            const selectedValue = $(this).val();
            if (selectedValue) {
              $('.loading-date-input').not(this).val(selectedValue).addClass('auto-propagate-field');
              setTimeout(() => {
                $('.loading-date-input').removeClass('auto-propagate-field');
              }, 1000);
              
              $('.loading-date-input').each(function() {
                const row = $(this).data('row');
                updateAllAmountsForRow(row, typeOfGoodsId);
              });
            }
          });

          $(document).off('change', '.bp-date-input');
          $(document).on('change', '.bp-date-input', function() {
            const selectedValue = $(this).val();
            if (selectedValue) {
              $('.bp-date-input').not(this).val(selectedValue).addClass('auto-propagate-field');
              setTimeout(() => {
                $('.bp-date-input').removeClass('auto-propagate-field');
              }, 1000);
            }
          });

          $(document).off('change', '.site-loading-input');
          $(document).on('change', '.site-loading-input', function() {
            const selectedValue = $(this).val();
            if (selectedValue) {
              $('.site-loading-input').not(this).val(selectedValue).addClass('auto-propagate-field');
              setTimeout(() => {
                $('.site-loading-input').removeClass('auto-propagate-field');
              }, 1000);
            }
          });

          $(document).off('change', '.feet-container-input');
          $(document).on('change', '.feet-container-input', function() {
            const row = $(this).data('row');
            updateAllAmountsForRow(row, typeOfGoodsId);
          });

          $(document).off('input', '.bulk-weight-input, .bulk-fob-input, .bags-input');
          $(document).on('input', '.bulk-weight-input, .bulk-fob-input, .bags-input', function() {
            const row = $(this).data('row');
            let weight = parseFloat($(`.bulk-weight-input[data-row="${row}"]`).val()) || 0;
            let fob = parseFloat($(`.bulk-fob-input[data-row="${row}"]`).val()) || 0;
            let bags = parseInt($(`.bags-input[data-row="${row}"]`).val()) || 0;
            
            if (weight < 0) {
              weight = Math.abs(weight);
              $(`.bulk-weight-input[data-row="${row}"]`).val(weight.toFixed(3));
            }
            
            if (fob < 0) {
              fob = Math.abs(fob);
              $(`.bulk-fob-input[data-row="${row}"]`).val(fob.toFixed(2));
            }
            
            if (bags < 0) {
              bags = Math.abs(bags);
              $(`.bags-input[data-row="${row}"]`).val(bags);
            }
            
            updateAllAmountsForRow(row, typeOfGoodsId);
            updateBulkBalance(availableWeight, availableFOB);
          });

          updateBulkBalance(availableWeight, availableFOB);

          $('#bulkInsertModal').modal('show');
        },
        error: function() {
          Swal.fire('Error', 'Failed to get license usage data', 'error');
        }
      });
    }

    function updateBulkBalance(availableWeight, availableFOB) {
      let totalWeight = 0;
      let totalFOB = 0;

      $('.bulk-weight-input').each(function() {
        totalWeight += Math.abs(parseFloat($(this).val()) || 0);
      });

      $('.bulk-fob-input').each(function() {
        totalFOB += Math.abs(parseFloat($(this).val()) || 0);
      });

      const remainingWeight = availableWeight - totalWeight;
      const remainingFOB = availableFOB - totalFOB;

      $('#bulk_used_weight').text(totalWeight.toFixed(3));
      $('#bulk_used_fob').text(totalFOB.toFixed(2));
      $('#bulk_remaining_weight').text(remainingWeight.toFixed(3));
      $('#bulk_remaining_fob').text(remainingFOB.toFixed(2));

      if (remainingWeight < 0) {
        $('#bulk_remaining_weight').removeClass('text-success').addClass('text-danger');
      } else {
        $('#bulk_remaining_weight').removeClass('text-danger').addClass('text-success');
      }

      if (remainingFOB < 0) {
        $('#bulk_remaining_fob').removeClass('text-success').addClass('text-danger');
      } else {
        $('#bulk_remaining_fob').removeClass('text-danger').addClass('text-success');
      }
    }

    function updateAllAmountsForRow(rowNum, typeOfGoodsId) {
      const weight = Math.abs(parseFloat($(`.bulk-weight-input[data-row="${rowNum}"]`).val()) || 0);
      const feetContainerId = $(`.feet-container-input[data-row="${rowNum}"]`).val();
      
      if (weight > 0) {
        const ceecAmount = calculateCEECAmount(weight);
        const cgeaAmount = calculateCGEAAmount();
        const occAmount = calculateOCCAmount();
        const lmcAmount = calculateLMCAmount(typeOfGoodsId, weight);
        
        $(`.ceec-amount-input[data-row="${rowNum}"]`).val(ceecAmount.toFixed(2));
        $(`.cgea-amount-input[data-row="${rowNum}"]`).val(cgeaAmount.toFixed(2));
        $(`.occ-amount-input[data-row="${rowNum}"]`).val(occAmount.toFixed(2));
        $(`.lmc-amount-input[data-row="${rowNum}"]`).val(lmcAmount.toFixed(2));
      } else {
        $(`.ceec-amount-input[data-row="${rowNum}"]`).val('');
        $(`.cgea-amount-input[data-row="${rowNum}"]`).val('80.00');
        $(`.occ-amount-input[data-row="${rowNum}"]`).val('250.00');
        $(`.lmc-amount-input[data-row="${rowNum}"]`).val('');
      }
      
      if (feetContainerId) {
        const ogefremAmount = calculateOGEFREMAmount(feetContainerId);
        $(`.ogefrem-amount-input[data-row="${rowNum}"]`).val(ogefremAmount ? ogefremAmount.toFixed(2) : '');
      } else {
        $(`.ogefrem-amount-input[data-row="${rowNum}"]`).val('');
      }
    }

    let currentBulkRow = null;

    function updateBulkSealDisplay(rowNum, seals, sealIds) {
      if (seals.length === 0) {
        $(`.seal-display-input[data-row="${rowNum}"]`).val('');
        $(`.seals-count-input[data-row="${rowNum}"]`).val('');
        $(`.seal-ids-hidden[data-row="${rowNum}"]`).val('');
      } else {
        const sealNumbers = seals.map(s => s.seal_number).join(', ');
        $(`.seal-display-input[data-row="${rowNum}"]`).val(sealNumbers);
        $(`.seals-count-input[data-row="${rowNum}"]`).val(seals.length);
        $(`.seal-ids-hidden[data-row="${rowNum}"]`).val(sealIds.join(','));
      }
    }

    $('#saveBulkInsertBtn').on('click', function() {
      const rows = [];
      let hasWeight = false;
      const transportModeId = parseInt($('#transport_mode_hidden').val());

      $('#bulkInsertTableBody tr').each(function() {
        const rowNum = $(this).data('row');
        const weight = Math.abs(parseFloat($(`.bulk-weight-input[data-row="${rowNum}"]`).val()) || 0);
        const fob = Math.abs(parseFloat($(`.bulk-fob-input[data-row="${rowNum}"]`).val()) || 0);
        const bags = Math.abs(parseInt($(`.bags-input[data-row="${rowNum}"]`).val()) || 0);
        
        if (weight > 0) hasWeight = true;

        const sealIds = ($(`.seal-ids-hidden[data-row="${rowNum}"]`).val() || '').split(',').filter(id => id);
        const sealNumbers = $(`.seal-display-input[data-row="${rowNum}"]`).val();
        const loadingDate = $(`.loading-date-input[data-row="${rowNum}"]`).val() || null;

        const rowData = {
          mca_ref: $(`.mca-ref-input[data-row="${rowNum}"]`).val(),
          loading_date: loadingDate,
          bp_date: $(`.bp-date-input[data-row="${rowNum}"]`).val() || null,
          site_of_loading_id: $(`.site-loading-input[data-row="${rowNum}"]`).val() || null,
          destination: $(`.destination-input[data-row="${rowNum}"]`).val() || null,
          feet_container: $(`.feet-container-input[data-row="${rowNum}"]`).val() || null,
          container: $(`.container-input[data-row="${rowNum}"]`).val() || null,
          transporter: $(`.transporter-input[data-row="${rowNum}"]`).val() || null,
          exit_point_id: $(`.exit-point-input[data-row="${rowNum}"]`).val() || null,
          weight: weight,
          fob: fob,
          number_of_bags: bags || null,
          lot_number: $(`.lot-input[data-row="${rowNum}"]`).val() || null,
          dgda_seal_no: sealNumbers || null,
          number_of_seals: sealIds.length || null,
          seal_ids: sealIds
        };

        if (transportModeId === 1) {
          rowData.horse = $(`.horse-input[data-row="${rowNum}"]`).val() || null;
          rowData.trailer_1 = $(`.trailer1-input[data-row="${rowNum}"]`).val() || null;
          rowData.trailer_2 = $(`.trailer2-input[data-row="${rowNum}"]`).val() || null;
          rowData.wagon_ref = null;
        } else if (transportModeId === 2 || transportModeId === 3) {
          rowData.wagon_ref = $(`.wagon-ref-input[data-row="${rowNum}"]`).val() || null;
          rowData.horse = null;
          rowData.trailer_1 = null;
          rowData.trailer_2 = null;
        }

        rows.push(rowData);
      });

      if (!hasWeight) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Data',
          text: 'At least one entry must have weight > 0',
        });
        return;
      }

      const remainingWeight = parseFloat($('#bulk_remaining_weight').text());
      const remainingFOB = parseFloat($('#bulk_remaining_fob').text());

      if (remainingWeight < 0) {
        Swal.fire({
          icon: 'error',
          title: 'Exceeded Available Weight',
          text: 'Total weight exceeds available license weight',
        });
        return;
      }

      if (remainingFOB < 0) {
        Swal.fire({
          icon: 'error',
          title: 'Exceeded Available FOB',
          text: 'Total FOB exceeds available license FOB',
        });
        return;
      }

      const commonData = {
        subscriber_id: $('#subscriber_id').val(),
        license_id: $('#license_id').val(),
        kind: $('#kind_hidden').val() || null,
        type_of_goods: $('#type_of_goods_hidden').val() || null,
        transport_mode: $('#transport_mode_hidden').val() || null,
        currency: $('#currency_hidden').val() || null,
        buyer: $('#buyer').val() || null,
        regime: $('#regime').val(),
        types_of_clearance: $('#types_of_clearance').val(),
        liquidation_paid_by: $('#liquidation_paid_by').val() || null
      };

      const saveBtn = $('#saveBulkInsertBtn');
      const originalText = saveBtn.html();
      saveBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Creating...');

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/bulkInsertFromModal',
        method: 'POST',
        data: {
          csrf_token: csrfToken,
          common_data: JSON.stringify(commonData),
          rows_data: JSON.stringify(rows)
        },
        dataType: 'json',
        success: function(res) {
          saveBtn.prop('disabled', false).html(originalText);
          
          if (res.success) {
            let messageHTML = '<p>' + escapeHtml(res.message) + '</p>';
            
            if (res.errors && res.errors.length > 0) {
              messageHTML += '<hr><p style="text-align:left;"><strong>Error Details:</strong></p>';
              messageHTML += '<ul style="text-align:left; color:#dc3545;">';
              res.errors.forEach(function(error) {
                messageHTML += '<li>' + escapeHtml(error) + '</li>';
              });
              messageHTML += '</ul>';
            }
            
            const icon = (res.error_count > 0) ? 'warning' : 'success';
            
            Swal.fire({
              icon: icon,
              title: (res.error_count > 0) ? 'Partial Success' : 'Success!',
              html: messageHTML,
              confirmButtonText: 'OK',
              width: '600px'
            }).then(() => {
              if (res.success_count > 0) {
                $('#bulkInsertModal').modal('hide');
                resetForm();
                if (typeof exportsTable !== 'undefined') {
                  exportsTable.ajax.reload(null, false);
                }
                updateStatistics();
                loadAvailableSeals();
              }
            });
          } else {
            Swal.fire('Error', res.message || 'Bulk insert failed', 'error');
          }
        },
        error: function(xhr, status, error) {
          saveBtn.prop('disabled', false).html(originalText);
          
          let errorMsg = 'An error occurred during bulk insert';
          
          if (xhr.status === 403) {
            errorMsg = 'Security token expired. Please refresh the page and try again.';
          } else if (xhr.responseText) {
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              errorMsg = errorResponse.message || errorMsg;
            } catch (e) {
              errorMsg = 'Server error: ' + xhr.status;
            }
          }
          
          Swal.fire('Error', errorMsg, 'error');
        }
      });
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
      updateBulkUpdateButton();
    });

    $('#clearFilters').on('click', function() {
      $('.stats-card').removeClass('active');
      activeFilters = [];
      
      // ✅ Clear multi-select client checkboxes
      $('.client-filter-checkbox').prop('checked', false);
      selectedClientIds = [];
      $('#clientDropdownLabel').text('All Clients');
      $('#clientCountBadge').hide();
      $('#selectedClientCountText').text('No clients selected - showing all');
      
      $('#advancedFilterClient').val('');
      $('#advancedFilterLicense').html('<option value="">Select Client First</option>').prop('disabled', true);
      $('#advancedFilterTransport').val('');
      $('#advancedFilterStartDate').val('');
      $('#advancedFilterEndDate').val('');
      advancedFiltersActive = false;
      
      updateActiveFiltersDisplay();
      applyFiltersToTable();
      updateBulkUpdateButton();
    });

    function updateActiveFiltersDisplay() {
      if (activeFilters.length > 0) {
        $('#activeFiltersBadge').show().text(activeFilters.length + ' Filter' + (activeFilters.length > 1 ? 's' : '') + ' Active');
      } else {
        $('#activeFiltersBadge').hide();
      }
    }

    function applyFiltersToTable() {
      if (typeof exportsTable !== 'undefined') {
        exportsTable.ajax.reload();
      }
    }

    function updateBulkUpdateButton() {
      if (activeFilters.length > 0) {
        $('#bulkUpdateBtn').prop('disabled', false);
      } else {
        $('#bulkUpdateBtn').prop('disabled', true);
      }
    }

    function updateRemainingLicenseInfo() {
      const licenseId = $('#license_id').val();
      
      if (!licenseId) {
        $('#remaining_weight_display').val('0.00');
        $('#remaining_fob_display').val('0.00');
        return;
      }
      
      const requestData = { license_id: licenseId };
      
      if (isEditMode && currentEditExportId) {
        requestData.exclude_export_id = currentEditExportId;
      }
      
      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getLicenseUsage',
        method: 'GET',
        data: requestData,
        dataType: 'json',
        success: function(res) {
          const licenseWeight = parseFloat($('#license_weight_display').val()) || 0;
          const licenseFOB = parseFloat($('#license_fob_display').val()) || 0;
          const usedWeight = res.used_weight || 0;
          const usedFOB = res.used_fob || 0;
          
          const remainingWeight = licenseWeight - usedWeight;
          const remainingFOB = licenseFOB - usedFOB;
          
          $('#remaining_weight_display').val(remainingWeight.toFixed(3));
          $('#remaining_fob_display').val(remainingFOB.toFixed(2));
          
          if (remainingWeight < 0) {
            $('#remaining_weight_display').css('color', '#dc3545');
          } else if (remainingWeight < licenseWeight * 0.1) {
            $('#remaining_weight_display').css('color', '#ffc107');
          } else {
            $('#remaining_weight_display').css('color', '#28a745');
          }
          
          if (remainingFOB < 0) {
            $('#remaining_fob_display').css('color', '#dc3545');
          } else if (remainingFOB < licenseFOB * 0.1) {
            $('#remaining_fob_display').css('color', '#ffc107');
          } else {
            $('#remaining_fob_display').css('color', '#28a745');
          }
        },
        error: function() {
          console.error('Failed to update remaining license info');
          $('#remaining_weight_display').val('0.00');
          $('#remaining_fob_display').val('0.00');
        }
      });
    }

    $('#subscriber_id').on('change', function() {
      const subscriberId = $(this).val();
      const selectedOption = $(this).find('option:selected');
      const liquidationPaidBy = selectedOption.data('liquidation');

      setLiquidationPaidBy(liquidationPaidBy);

      $('#license_id').html('<option value="">-- Select --</option>');
      
      $('#remaining_weight_display').val('0.00');
      $('#remaining_fob_display').val('0.00');
      
      if (!subscriberId) {
        clearLicenseFields();
        return;
      }

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getLicenses',
        method: 'GET',
        data: { subscriber_id: subscriberId },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data.length > 0) {
            res.data.forEach(function(license) {
              $('#license_id').append(`<option value="${license.id}">${escapeHtml(license.license_number)}</option>`);
            });
          } else {
            Swal.fire({
              icon: 'info',
              title: 'No Export Licenses',
              text: 'No active export licenses found for this client.',
              timer: 3000
            });
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load licenses', 'error');
        }
      });
    });

    function setLiquidationPaidBy(value) {
      if (value == 1) {
        $('#liquidation_paid_by').val('Client');
      } else if (value == 2) {
        $('#liquidation_paid_by').val('Malabar');
      } else {
        $('#liquidation_paid_by').val('');
      }
    }

    $('#license_id').on('change', function() {
      const licenseId = $(this).val();
      
      if (!licenseId) {
        clearLicenseFields();
        return;
      }

      $('#kind_display, #type_of_goods_display, #transport_mode_display, #currency_display, #buyer').val('Loading...');

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getLicenseDetails',
        method: 'GET',
        data: { license_id: licenseId },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data) {
            const license = res.data;
            
            $('#kind_hidden').val(license.kind_id || '');
            $('#type_of_goods_hidden').val(license.type_of_goods_id || '');
            $('#transport_mode_hidden').val(license.transport_mode_id || '');
            $('#currency_hidden').val(license.currency_id || '');
            
            $('#kind_display').val(escapeHtml(license.kind_name || ''));
            $('#type_of_goods_display').val(escapeHtml(license.type_of_goods_name || ''));
            $('#transport_mode_display').val(escapeHtml(license.transport_mode_name || ''));
            $('#currency_display').val(escapeHtml(license.currency_name || ''));
            $('#buyer').val(escapeHtml(license.buyer || ''));
            
            $('#license_weight_display').val(license.weight ? parseFloat(license.weight).toFixed(3) : '0.00');
            $('#license_fob_display').val(license.fob_declared ? parseFloat(license.fob_declared).toFixed(2) : '0.00');
            
            updateRemainingLicenseInfo();
            
            updateTransportConditionalFields();
            
            if (!isEditMode) {
              generateMCAReference();
            }
          } else {
            clearLicenseFields();
            Swal.fire('Error', res.message || 'Failed to load license details', 'error');
          }
        },
        error: function() {
          clearLicenseFields();
          Swal.fire('Error', 'Failed to load license details', 'error');
        }
      });
    });

    function clearLicenseFields() {
      $('#kind_hidden, #type_of_goods_hidden, #transport_mode_hidden, #currency_hidden').val('');
      $('#kind_display, #type_of_goods_display, #transport_mode_display, #currency_display').val('');
      $('#buyer, #license_weight_display, #license_fob_display, #liquidation_paid_by').val('');
      $('#remaining_weight_display, #remaining_fob_display').val('0.00');
      $('#mca_ref').val('');
      $('.transport-conditional-field').removeClass('show');
      $('.wagon-field').removeClass('show');
    }

    function generateMCAReference() {
      const subscriberId = $('#subscriber_id').val();
      const licenseId = $('#license_id').val();
      
      if (!subscriberId || !licenseId) {
        $('#mca_ref').val('');
        return;
      }

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getNextMCASequence',
        method: 'POST',
        data: { csrf_token: csrfToken, subscriber_id: subscriberId, license_id: licenseId },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#mca_ref').val(res.mca_ref);
          }
        },
        error: function() {
          console.error('Failed to generate MCA reference');
        }
      });
    }

    function resetForm() {
  $('#exportForm')[0].reset();
  $('#export_id, #mca_ref').val('');
  $('#formAction').val('insert');
  $('#formTitle').text('Create New Exports');
  $('#submitBtnText').text('Update Export');
  $('#resetFormBtn').hide();
  $('#numEntriesInput').val(1);
  $('#numEntriesContainer').show();
  $('#proceedBulkBtn').show();
  $('#singleFormButtons').hide();
  clearLicenseFields();
  
  // ✅ Hide edit-only sections
  $('.edit-only-section').removeClass('show');
  
  $('#types_of_clearance').val('1');
  $('#clearing_status').val('1');
  
  $('#exportDetailsSection').collapse('show');
  isEditMode = false;
  currentEditExportId = null;
  
  $('#subscriber_id, #license_id').prop('disabled', false);
  
  selectedSealIds = [];
  $('#dgda_seal_no').val('');
  $('#number_of_seals').val('');
  $('#dgda_seal_ids').val('');
  $('#ceec_amount').val('');
  $('#cgea_amount').val('');
  $('#occ_amount').val('250.00');
  $('#lmc_amount').val('');
  $('#ogefrem_amount').val('');
  
  $('.date-validation-error').removeClass('date-validation-error');
  $('.exceeded-warning').removeClass('exceeded-warning');
  
  // ✅ CRITICAL FIX: Unbind CEEC In handler when in create mode
  setTimeout(function() {
    $(document).off('change.ceecin input.ceecin', '#ceec_in_date');
  }, 100);
}

    $('#cancelBtn, #resetFormBtn').on('click', (e) => { 
      e.preventDefault(); 
      resetForm(); 
    });

    $(document).on('click', '.editBtn', function () {
      const id = parseInt($(this).data('id'));
      loadExportForEdit(id);
    });

function loadExportForEdit(exportId) {
  $.ajax({
    url: '<?= APP_URL ?>/export/crudData/getExport',
    method: 'GET',
    data: { id: exportId },
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data) {
        const exp = res.data;
        
        isEditMode = true;
        currentEditExportId = exportId;
        $('#formAction').val('update');
        $('#export_id').val(exp.id);
        $('#formTitle').text('Edit Export');
        $('#submitBtnText').text('Update Export');
        $('#resetFormBtn').show();
        
        $('#numEntriesContainer').hide();
        $('#proceedBulkBtn').hide();
        $('#singleFormButtons').show();
        
        // ✅ CRITICAL: Show edit-only fields FIRST
        $('.edit-only-section').addClass('show');
        
        $('#subscriber_id').val(exp.subscriber_id).prop('disabled', false);
        
        const selectedSubscriber = $('#subscriber_id option:selected');
        const liquidationValue = selectedSubscriber.data('liquidation');
        setLiquidationPaidBy(liquidationValue);
        
        $.ajax({
          url: '<?= APP_URL ?>/export/crudData/getLicenses',
          method: 'GET',
          data: { subscriber_id: exp.subscriber_id },
          dataType: 'json',
          success: function(licRes) {
            if (licRes.success && licRes.data.length > 0) {
              $('#license_id').html('<option value="">-- Select License --</option>');
              licRes.data.forEach(function(license) {
                $('#license_id').append(`<option value="${license.id}">${escapeHtml(license.license_number)}</option>`);
              });
              $('#license_id').val(exp.license_id).prop('disabled', false);
            }
          }
        });
        
        $('#mca_ref').val(exp.mca_ref);
        $('#kind_hidden').val(exp.kind);
        $('#type_of_goods_hidden').val(exp.type_of_goods);
        $('#transport_mode_hidden').val(exp.transport_mode);
        $('#currency_hidden').val(exp.currency);
        
        $('#kind_display').val(exp.kind_name || '');
        $('#type_of_goods_display').val(exp.type_of_goods_name || '');
        $('#transport_mode_display').val(exp.transport_mode_name || '');
        $('#currency_display').val(exp.currency_name || '');
        
        $('#regime').val(exp.regime);
        $('#types_of_clearance').val(exp.types_of_clearance);
        $('#buyer').val(exp.buyer);
        
        $('#license_weight_display').val(exp.license_weight ? parseFloat(exp.license_weight).toFixed(3) : '0.00');
        $('#license_fob_display').val(exp.license_fob ? parseFloat(exp.license_fob).toFixed(2) : '0.00');

        setTimeout(function() {
          updateRemainingLicenseInfo();
        }, 100);
        
        updateTransportConditionalFields();
        
        $('#invoice').val(exp.invoice);
        $('#po_ref').val(exp.po_ref);
        $('#weight').val(exp.weight);
        $('#fob').val(exp.fob);
        
        const weight = parseFloat(exp.weight) || 0;
        if (weight > 0) {
          $('#ceec_amount').val(exp.ceec_amount ? parseFloat(exp.ceec_amount).toFixed(2) : '');
          $('#cgea_amount').val(exp.cgea_amount ? parseFloat(exp.cgea_amount).toFixed(2) : '80.00');
        } else {
          $('#ceec_amount').val('');
          $('#cgea_amount').val('');
        }
        $('#occ_amount').val(exp.occ_amount ? parseFloat(exp.occ_amount).toFixed(2) : '250.00');
        $('#lmc_amount').val(exp.lmc_amount ? parseFloat(exp.lmc_amount).toFixed(2) : '');
        $('#ogefrem_amount').val(exp.ogefrem_amount ? parseFloat(exp.ogefrem_amount).toFixed(2) : '');
        
        $('#horse').val(exp.horse);
        $('#trailer_1').val(exp.trailer_1);
        $('#trailer_2').val(exp.trailer_2);
        $('#feet_container').val(exp.feet_container);
        $('#wagon_ref').val(exp.wagon_ref);
        $('#container').val(exp.container);
        $('#transporter').val(exp.transporter);
        $('#site_of_loading_id').val(exp.site_of_loading_id);
        $('.site-of-loading-duplicate').val(exp.site_of_loading_id);
        $('#destination').val(exp.destination);
        $('.destination-duplicate').val(exp.destination);
        $('.container-duplicate').val(exp.container);
        $('#loading_date').val(exp.loading_date);
        $('#pv_date').val(exp.pv_date);
        $('#bp_date').val(exp.bp_date);
        $('#demande_attestation_date').val(exp.demande_attestation_date);
        $('#assay_date').val(exp.assay_date);
        $('#lot_number').val(exp.lot_number);
        $('#number_of_bags').val(exp.number_of_bags);
        $('#archive_reference').val(exp.archive_reference);
        
        $('#dgda_seal_no').val(exp.dgda_seal_no || '');
        $('#number_of_seals').val(exp.number_of_seals || '');
        
        if (exp.dgda_seal_no) {
          const sealNumbers = exp.dgda_seal_no.split(',').map(s => s.trim());
          selectedSealIds = [];
          sealNumbers.forEach(sealNum => {
            const seal = availableSeals.find(s => s.seal_number === sealNum);
            if (seal) {
              selectedSealIds.push(seal.id.toString());
            }
          });
          $('#dgda_seal_ids').val(JSON.stringify(selectedSealIds));
        }
        
        $('#ceec_in_date').val(exp.ceec_in_date);
        $('#ceec_out_date').val(exp.ceec_out_date);
        $('#min_div_in_date').val(exp.min_div_in_date);
        $('#min_div_out_date').val(exp.min_div_out_date);
        $('#cgea_doc_ref').val(exp.cgea_doc_ref);
        $('#segues_rcv_ref').val(exp.segues_rcv_ref);
        $('#segues_payment_date').val(exp.segues_payment_date);
        $('#document_status').val(exp.document_status);
        $('#customs_clearing_code').val(exp.customs_clearing_code);
        $('#dgda_in_date').val(exp.dgda_in_date);
        $('#declaration_reference').val(exp.declaration_reference);
        $('#liquidation_reference').val(exp.liquidation_reference);
        $('#liquidation_date').val(exp.liquidation_date);
        
        if (exp.liquidation_paid_by) {
          $('#liquidation_paid_by').val(exp.liquidation_paid_by);
        }
        
        $('#liquidation_amount').val(exp.liquidation_amount);
        $('#quittance_reference').val(exp.quittance_reference);
        $('#quittance_date').val(exp.quittance_date);
        $('#dgda_out_date').val(exp.dgda_out_date);
        $('#gov_docs_in_date').val(exp.gov_docs_in_date);
        $('#gov_docs_out_date').val(exp.gov_docs_out_date);
        $('#clearing_status').val(exp.clearing_status);
        
        $('#dispatch_deliver_date').val(exp.dispatch_deliver_date);
        $('#kanyaka_arrival_date').val(exp.kanyaka_arrival_date);
        $('#kanyaka_departure_date').val(exp.kanyaka_departure_date);
        $('#border_arrival_date').val(exp.border_arrival_date);
        $('#exit_drc_date').val(exp.exit_drc_date);
        $('#exit_point_id').val(exp.exit_point_id);
        $('#end_of_formalities_date').val(exp.end_of_formalities_date);
        $('#truck_status').val(exp.truck_status);
        $('#lmc_id').val(exp.lmc_id);
        $('#ogefrem_inv_ref').val(exp.ogefrem_inv_ref);
        $('#audited_date').val(exp.audited_date);
        $('#archived_date').val(exp.archived_date);
        $('#lmc_date').val(exp.lmc_date);
        $('#ogefrem_date').val(exp.ogefrem_date);
        
        $('#exportDetailsSection').collapse('show');
        
        // ✅ CRITICAL FIX: Re-initialize CEEC In auto-propagation after all fields are loaded
        setTimeout(function() {
          setupCEECInAutoPropagation();
        }, 200);
        
        $('html, body').animate({
          scrollTop: $('#exportForm').offset().top - 100
        }, 500);
        
      } else {
        Swal.fire('Error', res.message || 'Failed to load export data', 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to load export data', 'error');
    }
  });
}

    $('#exportForm').on('submit', function (e) {
      e.preventDefault();
      
      if (!isEditMode) {
        Swal.fire('Error', 'Please use the "Proceed to Create" button to create exports', 'error');
        return;
      }

      if (!validateDatePairs()) {
        return;
      }

      $('#subscriber_id, #license_id').prop('disabled', false);

      const submitBtn = $('#submitBtn');
      const originalText = submitBtn.html();
      submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');

      const formData = $(this).serialize() + '&csrf_token=' + encodeURIComponent(csrfToken);

      $.ajax({
        url: '<?= APP_URL ?>export/crudData/update',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
          submitBtn.prop('disabled', false).html(originalText);
          
          if (res.success) {
            Swal.fire({ 
              icon: 'success', 
              title: 'Success!', 
              html: res.message, 
              timer: 2000, 
              showConfirmButton: true 
            });
            
            resetForm();
            
            if (typeof exportsTable !== 'undefined') {
              exportsTable.ajax.reload(null, false);
            }
            updateStatistics();
            loadAvailableSeals();
          } else {
            Swal.fire({ 
              icon: 'error', 
              title: 'Error!', 
              html: res.message 
            });
            
            $('#subscriber_id, #license_id').prop('disabled', false);
          }
        },
        error: function(xhr) {
          submitBtn.prop('disabled', false).html(originalText);
          
          $('#subscriber_id, #license_id').prop('disabled', false);
          
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

$('#bulkUpdateBtn').on('click', function() {
  if (activeFilters.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'No Filter Selected',
      text: 'Please select at least one filter (e.g., CEEC Pending, DGDA In Pending) to perform bulk update.',
      confirmButtonText: 'OK'
    });
    return;
  }

  $.ajax({
    url: '<?= APP_URL ?>/export/crudData/getBulkUpdateData',
    method: 'GET',
    data: { filters: activeFilters },
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data.length > 0) {
        currentBulkData = res.data;
        
        // ✅ AUTO-ADD RELATED FIELDS FOR CEEC IN AUTO-PROPAGATION
        let fieldsToShow = [...res.relevant_fields];
        
        // If CEEC In or CEEC Out is present, also add the auto-propagated fields
        if (fieldsToShow.includes('ceec_in_date') || fieldsToShow.includes('ceec_out_date')) {
          const relatedFields = ['pv_date', 'demande_attestation_date', 'assay_date'];
          relatedFields.forEach(field => {
            if (!fieldsToShow.includes(field)) {
              fieldsToShow.push(field);
            }
          });
        }
        
        currentBulkFields = fieldsToShow;
        
        renderBulkUpdateTable(res.data, fieldsToShow);
        
        $('#bulkUpdateCount').text(res.data.length);
        $('#bulkUpdateModal').modal('show');
      } else {
        Swal.fire('Info', res.message || 'No exports found for the selected filters', 'info');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to load bulk update data', 'error');
    }
  });
});
    $(document).on('change', '#bulkUpdateClientFilter', function() {
      const selectedClient = $(this).val();
      
      if (!selectedClient) {
        $('#bulkUpdateContent tbody tr').show();
      } else {
        $('#bulkUpdateContent tbody tr').each(function() {
          const rowClientId = $(this).data('client-id');
          if (rowClientId == selectedClient) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }
    });
    $(document).on('change', '#bulkUpdateTransportModeFilter', function() {
      const selectedTransportMode = $(this).val();
      
      if (!selectedTransportMode) {
        $('#bulkUpdateContent tbody tr').show();
      } else {
        $('#bulkUpdateContent tbody tr').each(function() {
          const rowTransportModeId = $(this).data('transport-mode-id');
          if (rowTransportModeId == selectedTransportMode) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }
    });
    // ✅ BULK UPDATE - AUTO-PROPAGATE PV DATE, DEMANDE D'ATTESTATION, ASSAY DATE WHEN CEEC IN CHANGES
    $(document).on('change', '.bulk-field[data-field="ceec_in_date"]', function() {
      const selectedValue = $(this).val();
      const exportId = $(this).data('export-id');
      
      if (selectedValue) {
        $(`.bulk-field[data-export-id="${exportId}"][data-field="pv_date"]`).val(selectedValue).addClass('auto-propagate-field');
        $(`.bulk-field[data-export-id="${exportId}"][data-field="demande_attestation_date"]`).val(selectedValue).addClass('auto-propagate-field');
        $(`.bulk-field[data-export-id="${exportId}"][data-field="assay_date"]`).val(selectedValue).addClass('auto-propagate-field');
        
        setTimeout(() => {
          $(`.bulk-field[data-export-id="${exportId}"]`).removeClass('auto-propagate-field');
        }, 1000);
      }
    });

function renderBulkUpdateTable(data, fields) {

    const fieldLabels = {
      'ceec_in_date': 'CEEC In',
      'ceec_out_date': 'CEEC Out',
      'min_div_in_date': 'Min Div In',
      'min_div_out_date': 'Min Div Out',
      'gov_docs_in_date': 'Gov Docs In',
      'gov_docs_out_date': 'Gov Docs Out',
      'dgda_in_date': 'DGDA In',
      'liquidation_date': 'Liquidation',
      'quittance_date': 'Quittance',
      'audited_date': 'Audited',
      'archived_date': 'Archived',
      'lmc_date': 'LMC',
      'ogefrem_date': 'Ogefrem',
      'dispatch_deliver_date': 'Dispatch/Deliver',
      'pv_date': 'PV Date',
      'demande_attestation_date': "Demande d'Attestation",
      'assay_date': 'Assay Date',
      'dgda_seal_no': 'Seal DGDA',
      'number_of_seals': 'No. of Seals',
      'lmc_id': 'LMC ID',
      'ogefrem_inv_ref': 'Ogefrem Ref'
    };

    const numericFields = ['number_of_seals'];
    const textFields    = ['dgda_seal_no','lmc_id','ogefrem_inv_ref'];

    let tableHTML = '<div class="bulk-table-container"><table class="bulk-update-table"><thead><tr>';
    tableHTML += '<th style="width:50px;">MCA Ref</th>';
    tableHTML += '<th style="width:100px;">Client</th>';
    tableHTML += '<th style="width:100px;">Loading Date</th>';

    fields.forEach(field => {
      tableHTML += `<th>${fieldLabels[field] || field}</th>`;
    });

    tableHTML += '</tr></thead><tbody>';

    data.forEach(exp => {
      tableHTML += `<tr data-client-id="${exp.subscriber_id || ''}" data-export-id="${exp.id}" data-transport-mode-id="${exp.transport_mode}">`;
      tableHTML += `<td><span class="mca-ref-badge">${escapeHtml(exp.mca_ref)}</span></td>`;
      tableHTML += `<td>${escapeHtml(exp.subscriber_name || '')}</td>`;
      tableHTML += `<td><span class="loading-date-text">${exp.loading_date ? formatDate(exp.loading_date) : 'N/A'}</span></td>`;

      fields.forEach(field => {
        let value = exp[field] || '';
        let cellHtml = '';

        if (field === 'dgda_seal_no') {
          // ✅ Parse existing seal IDs from seal numbers
          let sealIds = [];
          if (value) {
            const sealNumbers = value.split(',').map(s => s.trim());
            sealNumbers.forEach(sealNum => {
              const seal = availableSeals.find(s => s.seal_number === sealNum);
              if (seal) {
                sealIds.push(seal.id.toString());
              }
            });
          }

          cellHtml = `
            <td>
              <div class="seal-input-group">
                <input 
                  type="text"
                  class="form-control bulk-field"
                  data-export-id="${exp.id}"
                  data-field="dgda_seal_no"
                  value="${escapeHtml(value)}"
                  readonly
                  placeholder="No seals selected">
                <button type="button"
                  class="btn-select-seals bulk-update-seal-btn"
                  data-export-id="${exp.id}"
                  title="Select Seals">+</button>
                <input type="hidden" class="bulk-seal-ids" data-export-id="${exp.id}" value="${escapeHtml(JSON.stringify(sealIds))}">
              </div>
            </td>`;
        }
        else {
          let inputType = 'date';
          if (numericFields.includes(field)) {
            inputType = 'number';
          }
          else if (textFields.includes(field)) {
            inputType = 'text';
          } 

          cellHtml = `
            <td>
              <input 
                type="${inputType}"
                class="form-control bulk-field"
                data-export-id="${exp.id}"
                data-field="${field}"
                value="${escapeHtml(value)}"
                ${field === 'number_of_seals' ? 'readonly' : ''}>
            </td>`;
        }

        tableHTML += cellHtml;
      });

      tableHTML += '</tr>';
    });

    tableHTML += '</tbody></table></div>';
    $('#bulkUpdateContent').html(tableHTML);

    // ✅ Click handler for bulk update seal selection buttons
    $(document).off('click', '.bulk-update-seal-btn');
    $(document).on('click', '.bulk-update-seal-btn', function() {
      currentModalContext = 'bulkUpdate';
      const exportId = $(this).data('export-id');
      currentBulkUpdateExportId = exportId;
      
      // Get current seal IDs for this export
      let currentSealIds = [];
      const sealIdsVal = $(`.bulk-seal-ids[data-export-id="${exportId}"]`).val();
      if (sealIdsVal) {
        try {
          currentSealIds = JSON.parse(sealIdsVal);
        } catch(e) {
          currentSealIds = [];
        }
      }
      
      renderSealSelection(currentSealIds);
      $('#sealSelectionModal').modal('show');
    });
}


    $('#saveBulkUpdateBtn').on('click', function() {
      const updateData = [];

      $('.bulk-field').each(function() {
        const exportId = $(this).data('export-id');
        const field = $(this).data('field');
        const value = $(this).val();

        let existingUpdate = updateData.find(u => u.export_id === exportId);
        if (!existingUpdate) {
          existingUpdate = { export_id: exportId };
          updateData.push(existingUpdate);
        }

        existingUpdate[field] = value;
      });

      // Add seal_ids from hidden inputs
      $('.bulk-seal-ids').each(function() {
        const exportId = $(this).data('export-id');
        const sealIdsVal = $(this).val();
        
        let existingUpdate = updateData.find(u => u.export_id === exportId);
        if (existingUpdate && sealIdsVal) {
          existingUpdate.seal_ids = sealIdsVal;
        }
      });

      if (updateData.length === 0) {
        Swal.fire('Error', 'No data to update', 'error');
        return;
      }

      const saveBtn = $('#saveBulkUpdateBtn');
      const originalText = saveBtn.html();
      saveBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Updating...');

      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/bulkUpdate',
        method: 'POST',
        data: {
          csrf_token: csrfToken,
          update_data: JSON.stringify(updateData)
        },
        dataType: 'json',
        success: function(res) {
          saveBtn.prop('disabled', false).html(originalText);

          if (res.success) {
            let messageHTML = '<p>' + escapeHtml(res.message) + '</p>';

            if (res.errors && res.errors.length > 0) {
              messageHTML += '<hr><p style="text-align:left;"><strong>Error Details:</strong></p>';
              messageHTML += '<ul style="text-align:left; color:#dc3545;">';
              res.errors.forEach(function(error) {
                messageHTML += '<li>' + escapeHtml(error) + '</li>';
              });
              messageHTML += '</ul>';
            }

            const icon = (res.error_count > 0) ? 'warning' : 'success';

            Swal.fire({
              icon: icon,
              title: (res.error_count > 0) ? 'Partial Success' : 'Success!',
              html: messageHTML,
              confirmButtonText: 'OK',
              width: '600px'
            }).then(() => {
              if (res.success_count > 0) {
                $('#bulkUpdateModal').modal('hide');
                if (typeof exportsTable !== 'undefined') {
                  exportsTable.ajax.reload(null, false);
                }
                updateStatistics();
                loadAvailableSeals();
              }
            });
          } else {
            Swal.fire('Error', res.message || 'Bulk update failed', 'error');
          }
        },
        error: function(xhr) {
          saveBtn.prop('disabled', false).html(originalText);

          let errorMsg = 'An error occurred during bulk update';

          if (xhr.status === 403) {
            errorMsg = 'Security token expired. Please refresh the page and try again.';
          }

          Swal.fire('Error', errorMsg, 'error');
        }
      });
    });

    $(document).on('click', '.viewBtn', function () {
      const id = parseInt($(this).data('id'));
      
      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getExport',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(res) {
          if (res.success && res.data) {
            renderExportDetails(res.data);
            $('#viewExportModal').modal('show');
          } else {
            Swal.fire('Error', res.message || 'Failed to load export details', 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to load export details', 'error');
        }
      });
    });

    function renderExportDetails(exp) {
      let html = '<div class="container-fluid p-4">';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Basic Information</h5>';
      html += '<div class="row">';
      html += renderDetailField('MCA Reference', exp.mca_ref, 'col-md-3');
      html += renderDetailField('Client', exp.subscriber_name, 'col-md-3');
      html += renderDetailField('License Number', exp.license_number, 'col-md-3');
      html += renderDetailField('Invoice', exp.invoice, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Kind', exp.kind_name, 'col-md-3');
      html += renderDetailField('Type of Goods', exp.type_of_goods_name, 'col-md-3');
      html += renderDetailField('Transport Mode', exp.transport_mode_name, 'col-md-3');
      html += renderDetailField('Currency', exp.currency_name, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Buyer', exp.buyer, 'col-md-3');
      html += renderDetailField('Regime', exp.regime_name, 'col-md-3');
      html += renderDetailField('Types of Clearance', exp.clearance_name, 'col-md-3');
      html += renderDetailField('PO Reference', exp.po_ref, 'col-md-3');
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-scale me-2"></i>Weight & Financial</h5>';
      html += '<div class="row">';
      html += renderDetailField('Weight (MT)', exp.weight ? parseFloat(exp.weight).toFixed(3) : '', 'col-md-3');
      html += renderDetailField('FOB', exp.fob ? parseFloat(exp.fob).toFixed(2) : '', 'col-md-3');
      html += renderDetailField('Number of Bags', exp.number_of_bags, 'col-md-3');
      html += renderDetailField('Lot Number', exp.lot_number, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('CEEC Amount', exp.ceec_amount ? parseFloat(exp.ceec_amount).toFixed(2) : '', 'col-md-3');
      html += renderDetailField('CGEA Amount', exp.cgea_amount ? parseFloat(exp.cgea_amount).toFixed(2) : '', 'col-md-3');
      html += renderDetailField('OCC Amount', exp.occ_amount ? parseFloat(exp.occ_amount).toFixed(2) : '', 'col-md-3');
      html += renderDetailField('LMC Amount', exp.lmc_amount ? parseFloat(exp.lmc_amount).toFixed(2) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('OGEFREM Amount', exp.ogefrem_amount ? parseFloat(exp.ogefrem_amount).toFixed(2) : '', 'col-md-3');
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-truck me-2"></i>Transport Details</h5>';
      html += '<div class="row">';
      html += renderDetailField('Transporter', exp.transporter, 'col-md-3');
      html += renderDetailField('Horse', exp.horse, 'col-md-3');
      html += renderDetailField('Trailer 1', exp.trailer_1, 'col-md-3');
      html += renderDetailField('Trailer 2', exp.trailer_2, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Feet Container', exp.feet_container_size, 'col-md-3');
      html += renderDetailField('Wagon Reference', exp.wagon_ref, 'col-md-3');
      html += renderDetailField('Container', exp.container, 'col-md-3');
      html += renderDetailField('Site of Loading', exp.site_of_loading_name, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Destination', exp.destination, 'col-md-3');
      html += renderDetailField('Exit Point', exp.exit_point_name, 'col-md-3');
      html += renderDetailField('Truck Status', exp.truck_status_name, 'col-md-3');
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-calendar me-2"></i>Important Dates</h5>';
      html += '<div class="row">';
      html += renderDetailField('Loading Date', exp.loading_date ? formatDate(exp.loading_date) : '', 'col-md-3');
      html += renderDetailField('PV Date', exp.pv_date ? formatDate(exp.pv_date) : '', 'col-md-3');
      html += renderDetailField('BP Date', exp.bp_date ? formatDate(exp.bp_date) : '', 'col-md-3');
      html += renderDetailField('Demande Attestation', exp.demande_attestation_date ? formatDate(exp.demande_attestation_date) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Assay Date', exp.assay_date ? formatDate(exp.assay_date) : '', 'col-md-3');
      html += renderDetailField('Dispatch/Deliver', exp.dispatch_deliver_date ? formatDate(exp.dispatch_deliver_date) : '', 'col-md-3');
      html += renderDetailField('Kanyaka Arrival', exp.kanyaka_arrival_date ? formatDate(exp.kanyaka_arrival_date) : '', 'col-md-3');
      html += renderDetailField('Kanyaka Departure', exp.kanyaka_departure_date ? formatDate(exp.kanyaka_departure_date) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Border Arrival', exp.border_arrival_date ? formatDate(exp.border_arrival_date) : '', 'col-md-3');
      html += renderDetailField('Exit DRC', exp.exit_drc_date ? formatDate(exp.exit_drc_date) : '', 'col-md-3');
      html += renderDetailField('End of Formalities', exp.end_of_formalities_date ? formatDate(exp.end_of_formalities_date) : '', 'col-md-3');
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-file-certificate me-2"></i>Declaration</h5>';
      html += '<div class="row">';
      html += renderDetailField('CEEC In', exp.ceec_in_date ? formatDate(exp.ceec_in_date) : '', 'col-md-3');
      html += renderDetailField('CEEC Out', exp.ceec_out_date ? formatDate(exp.ceec_out_date) : '', 'col-md-3');
      html += renderDetailField('Min Div In', exp.min_div_in_date ? formatDate(exp.min_div_in_date) : '', 'col-md-3');
      html += renderDetailField('Min Div Out', exp.min_div_out_date ? formatDate(exp.min_div_out_date) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('CGEA Doc Ref', exp.cgea_doc_ref, 'col-md-3');
      html += renderDetailField('Segues RCV Ref', exp.segues_rcv_ref, 'col-md-3');
      html += renderDetailField('Segues Payment', exp.segues_payment_date ? formatDate(exp.segues_payment_date) : '', 'col-md-3');
      html += renderDetailField('Document Status', exp.document_status_name, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Customs Clearing Code', exp.customs_clearing_code, 'col-md-3');
      html += renderDetailField('DGDA In', exp.dgda_in_date ? formatDate(exp.dgda_in_date) : '', 'col-md-3');
      html += renderDetailField('DGDA Out', exp.dgda_out_date ? formatDate(exp.dgda_out_date) : '', 'col-md-3');
      html += renderDetailField('Declaration Reference', exp.declaration_reference, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Liquidation Reference', exp.liquidation_reference, 'col-md-3');
      html += renderDetailField('Liquidation Date', exp.liquidation_date ? formatDate(exp.liquidation_date) : '', 'col-md-3');
      html += renderDetailField('Liquidation Paid By', exp.liquidation_paid_by, 'col-md-3');
      html += renderDetailField('Liquidation Amount', exp.liquidation_amount ? parseFloat(exp.liquidation_amount).toFixed(2) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Quittance Reference', exp.quittance_reference, 'col-md-3');
      html += renderDetailField('Quittance Date', exp.quittance_date ? formatDate(exp.quittance_date) : '', 'col-md-3');
      html += renderDetailField('Gov Docs In', exp.gov_docs_in_date ? formatDate(exp.gov_docs_in_date) : '', 'col-md-3');
      html += renderDetailField('Gov Docs Out', exp.gov_docs_out_date ? formatDate(exp.gov_docs_out_date) : '', 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('Declaration Status', exp.clearing_status_name, 'col-md-3');
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '<div class="row mb-4">';
      html += '<div class="col-12">';
      html += '<h5 class="text-primary mb-3"><i class="ti ti-shield-check me-2"></i>Seals & Miscellaneous</h5>';
      html += '<div class="row">';
      html += renderDetailField('Seal DGDA', exp.dgda_seal_no, 'col-md-4');
      html += renderDetailField('Number of Seals', exp.number_of_seals, 'col-md-2');
      html += renderDetailField('Archive Reference', exp.archive_reference, 'col-md-3');
      html += renderDetailField('LMC ID', exp.lmc_id, 'col-md-3');
      html += '</div>';
      html += '<div class="row">';
      html += renderDetailField('OGEFREM Inv.Ref.', exp.ogefrem_inv_ref, 'col-md-3');
      html += renderDetailField('Audited Date', exp.audited_date ? formatDate(exp.audited_date) : '', 'col-md-3');
      html += renderDetailField('Archived Date', exp.archived_date ? formatDate(exp.archived_date) : '', 'col-md-3');
      html += renderDetailField('LMC Date', exp.lmc_date ? formatDate(exp.lmc_date) : '', 'col-md-3');
      html += renderDetailField('Ogefrem Date', exp.ogefrem_date ? formatDate(exp.ogefrem_date) : '', 'col-md-3');
      
      html += '</div>';
      html += '</div>';
      html += '</div>';
      
      html += '</div>';
      
      $('#modalDetailsContent').html(html);
    }

    function renderDetailField(label, value, colClass = 'col-md-3') {
      const displayValue = value || '<span class="text-muted">N/A</span>';
      return `
        <div class="${colClass} mb-3">
          <label class="form-label text-muted fw-bold small">${escapeHtml(label)}</label>
          <div class="border-bottom pb-2">${displayValue}</div>
        </div>
      `;
    }

    function formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    }

    var exportsTable = $('#exportsTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: { 
        url: '<?= APP_URL ?>/export/crudData/listing', 
        type: 'GET',
        data: function(d) {
          d.filters = activeFilters;
          
          if (advancedFiltersActive) {
            d.subscriber_filter = selectedClientIds;
            // d.subscriber_filter = $('#advancedFilterClient').val();
            d.transport_mode_filter = $('#advancedFilterTransport').val();
            d.start_date_filter = $('#advancedFilterStartDate').val();
            d.end_date_filter = $('#advancedFilterEndDate').val();
            d.license_filter = $('#advancedFilterLicense').val();
          }
        }
      },
      columns: [
        { data: 'id', className: 'text-center' },
        { 
          data: 'mca_ref',
          render: function(data, type, row) {
            return `<span class="mca-ref-badge">${escapeHtml(data || '')}</span>`;
          }
        },
        { data: 'subscriber_name', render: (d) => escapeHtml(d || '') },
        { data: 'license_number', render: (d) => escapeHtml(d || '') },
        { data: 'invoice', render: (d) => escapeHtml(d || '') },
        { 
          data: 'loading_date',
          render: function(data) {
            return data ? formatDate(data) : '';
          }
        },
        { 
          data: 'weight',
          render: function(data) {
            return data ? parseFloat(data).toFixed(3) : '0.00';
          }
        },
        { 
          data: 'fob',
          render: function(data) {
            return data ? parseFloat(data).toFixed(2) : '0.00';
          }
        },
        { data: 'clearing_status_name', render: (d) => escapeHtml(d || '') },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: 'text-center',
          render: function (data, type, row) {
            return `
              <button type="button" class="btn btn-sm btn-view viewBtn me-1" data-id="${row.id}" title="View">
                <i class="ti ti-eye"></i>
              </button>
              <button type="button" class="btn btn-sm btn-warning editBtn" data-id="${row.id}" title="Edit">
                <i class="ti ti-edit"></i>
              </button>
            `;
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        emptyTable: "No exports available",
        zeroRecords: "No matching exports found"
      }
    });

    function updateStatistics() {
      $.ajax({
        url: '<?= APP_URL ?>/export/crudData/getStatistics',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            $('#totalTrackings').text(res.data.total_exports || 0);
            $('#totalCompleted').text(res.data.total_completed || 0);
            $('#totalInProgress').text(res.data.in_progress || 0);
            $('#totalInTransit').text(res.data.in_transit || 0);
            $('#totalCEECPending').text(res.data.ceec_pending || 0);
            $('#totalMinDivPending').text(res.data.min_div_pending || 0);
            $('#totalGovDocsPending').text(res.data.gov_docs_pending || 0);
            $('#totalAuditedPending').text(res.data.audited_pending || 0);
            $('#totalArchivedPending').text(res.data.archived_pending || 0);
            $('#totalDGDAInPending').text(res.data.dgda_in_pending || 0);
            $('#totalLiquidationPending').text(res.data.liquidation_pending || 0);
            $('#totalQuittancePending').text(res.data.quittance_pending || 0);
            $('#totalDispatchPending').text(res.data.dispatch_pending || 0);
            $('#totalSealPending').text(res.data.seal_pending || 0);

            $('#totalLMCIdPending').text(res.data.lmc_id_pending || 0);
            $('#totalLMCDatePending').text(res.data.lmc_pending || 0);
            $('#totalOgefremRefPending').text(res.data.ogefrem_ref_pending || 0);
            $('#totalOgefremDatePending').text(res.data.ogefrem_pending || 0);
          }
        }
      });
    }

    updateStatistics();
    loadAvailableSeals();

  });

  function loadLicensesForSelectedClients(){
    if(selectedClientIds.length === 0){
      $('#advancedFilterLicense').html('<option value="">Select Client First</option>');
      return;
    }

    $.get('<?= APP_URL ?>/export/crudData/getLicenses',
      { subscriber_id: selectedClientIds },
      function(res){
        let html = '<option value="">All Licenses</option>';
        res.data.forEach(l=>{
          html += `<option value="${l.id}">${l.license_number}</option>`;
        });
        $('#advancedFilterLicense').html(html).prop('disabled',false);
      },'json');
  }

  

</script>

<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
