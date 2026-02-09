<!-- FILE: views/tracking/imports.php -->
<link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<style>
  /* ========================================
     DATATABLE WRAPPER STYLES
     ======================================== */
  .dataTables_wrapper .dataTables_info { float: left; }
  .dataTables_wrapper .dataTables_paginate { float: right; text-align: right; }
  
  .dt-buttons { float: left; margin-bottom: 10px; }
  .buttons-excel, .btn-export-all {
    background: #28a745 !important; color: white !important; border: none !important;
    padding: 8px 20px !important; border-radius: 5px !important; font-weight: 500 !important;
    transition: all 0.3s !important; box-shadow: none !important;
  }
  .buttons-excel:hover, .btn-export-all:hover {
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
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(243, 156, 18, 0.4);
  }
  .btn-bulk-update:disabled {
    background: #95a5a6 !important;
    cursor: not-allowed;
    opacity: 0.6;
  }

  /* Add after .btn-export styles */

.btn-border-team {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white !important;
  border: none !important;
  padding: 8px 20px !important;
  border-radius: 5px !important;
  font-weight: 500 !important;
  transition: all 0.3s !important;
  box-shadow: none !important;
}

.btn-border-team:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
  color: white !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4) !important;
}
  
  .text-danger { color: #dc3545; font-weight: bold; }
  .is-invalid { border-color: #dc3545 !important; }
  .invalid-feedback { display: block; color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; }

  /* ========================================
     ENHANCED DATATABLE HOVER STYLES
     ======================================== */
  
  #importsTable {
    font-size: 0.8rem;
  }

  #importsTable thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 10px 6px;
    white-space: nowrap;
    border: none;
    font-size: 0.85rem;
  }

  #importsTable tbody td {
    vertical-align: middle;
    padding: 8px 6px;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
    font-size: 0.8rem;
    line-height: 1.4;
  }

  #importsTable tbody td .badge {
    font-size: 0.75rem;
    padding: 2px 6px;
  }

  #importsTable tbody td small {
    font-size: 0.7rem;
  }

  #importsTable tbody td strong {
    font-size: 0.8rem;
    font-weight: 600;
  }

  #importsTable tbody tr {
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 3px solid transparent;
  }

  #importsTable tbody tr:hover {
    background: linear-gradient(to right, #f0f4ff 0%, #e8f0fe 100%) !important;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
    border-left: 3px solid #667eea;
  }

  #importsTable tbody tr:hover td {
    color: #2d3748;
  }

  #importsTable tbody tr:nth-child(even) {
    background-color: #f8f9fa;
  }

  #importsTable tbody tr:nth-child(odd) {
    background-color: #ffffff;
  }

  #importsTable tbody tr.selected {
    background: linear-gradient(to right, #e7f3ff 0%, #d4e9ff 100%) !important;
    border-left: 3px solid #007bff;
  }

  #importsTable .btn-group .btn {
    transition: all 0.2s ease;
    font-size: 0.8rem;
    padding: 3px 6px;
  }

  #importsTable .btn-group .btn i {
    font-size: 0.9rem;
  }

  #importsTable .btn-group .btn:hover {
    transform: scale(1.1);
    z-index: 1;
  }

  /* ========================================
     PARTIELLE TABLE STYLES
     ======================================== */

  #partielleTable {
    font-size: 0.85rem;
    margin-bottom: 0;
  }

  #partielleTable thead th {
    background: #1f2937;
    color: white;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    padding: 10px 6px;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 0.85rem;
  }

  #partielleTable tbody td {
    vertical-align: middle;
    padding: 8px 6px;
    text-align: center;
    font-size: 0.85rem;
  }

  #partielleTable tbody tr {
    transition: background-color 0.2s;
  }

  #partielleTable tbody tr:hover {
    background-color: #f3f4f6;
  }

  .partielle-row-number {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
  }

  .badge-count {
    background: #3b82f6;
    color: white;
    padding: 3px 6px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
  }

  .badge-count:hover {
    background: #2563eb;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
  }

  .text-balance-positive {
    color: #10b981;
    font-weight: 600;
  }

  .text-balance-negative {
    color: #ef4444;
    font-weight: 600;
  }

  /* ========================================
     MODAL STYLES
     ======================================== */

  .modal.show ~ .modal {
    z-index: 1060;
  }

  .modal-backdrop.show ~ .modal-backdrop {
    z-index: 1055;
  }

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

  #bulkUpdateModal .modal-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
  }

  #commodityModal .modal-header, 
  #partielleModal .modal-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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

  /* ========================================
     STATISTICS CARDS
     ======================================== */
  
  .stats-card {
    border: none; 
    border-radius: 10px;
    transition: all 0.3s ease; 
    overflow: hidden;
    background: white; 
    border: 1px solid #e9ecef;
    cursor: pointer; 
    position: relative;
  }

  .stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #007bff;
  }

  .stats-card.active {
    border-color: #007bff; 
    background: #f8f9ff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
  }

  .stats-card .card-body {
    padding: 15px; 
    position: relative;
  }
  
  .stats-card-icon {
    width: 35px; 
    height: 35px;
    border-radius: 8px; 
    display: flex;
    align-items: center; 
    justify-content: center;
    margin-bottom: 8px; 
    float: left; 
    margin-right: 10px;
  }

  .stats-card-icon i { 
    font-size: 18px; 
    color: white; 
  }
  
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
  .icon-yellow { background: linear-gradient(135deg, #FFC107 0%, #FF9800 100%); }
  .icon-brown { background: linear-gradient(135deg, #795548 0%, #5D4037 100%); }
  .icon-lime { background: linear-gradient(135deg, #CDDC39 0%, #AFB42B 100%); }
  
  .stats-value {
    font-size: 1.4rem; 
    font-weight: 700; 
    color: #2C3E50;
    margin-bottom: 2px; 
    line-height: 1.2;
  }

  .stats-label {
    font-size: 0.75rem; 
    color: #7F8C8D;
    font-weight: 500; 
    line-height: 1.2;
  }
  
  .stats-card .card-body::after {
    content: ""; 
    display: table; 
    clear: both;
  }

  .filter-indicator {
    position: absolute; 
    top: 8px; 
    right: 8px;
    background: #007bff; 
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

  .stats-card.active .filter-indicator { 
    display: flex; 
  }

  /* ========================================
     FILTER CONTROLS
     ======================================== */

  .filter-controls {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 2px solid #e9ecef;
  }

  .filter-controls h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
  }

  .filter-controls h6 i {
    margin-right: 8px;
    color: #667eea;
  }

  .filter-controls .form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 5px;
    font-size: 0.9rem;
  }

  .filter-controls .form-label.fw-bold {
    font-weight: 600;
    color: #2d3748;
  }

  .filter-controls .form-select,
  .filter-controls .form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
    font-size: 0.9rem;
  }

  .filter-controls .form-select:focus,
  .filter-controls .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }

  /* ========================================
     CHECKBOX DROPDOWN STYLES
     ======================================== */

  #clientDropdownBtn {
    border: 2px solid #e2e8f0;
    background: white;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.95rem;
    transition: all 0.2s;
  }

  #clientDropdownBtn:hover {
    border-color: #667eea;
    background: #f8f9ff;
  }

  #clientDropdownBtn:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  #clientDropdownBtn .dropdown-toggle::after {
    margin-left: auto;
  }

  #clientDropdownMenu {
    max-width: 100%;
    min-width: 100%;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 0;
  }

  #clientDropdownMenu li {
    list-style: none;
  }

  #clientDropdownMenu .form-check {
    padding: 8px 12px;
    border-radius: 6px;
    transition: background 0.2s;
    margin-bottom: 4px;
  }

  #clientDropdownMenu .form-check:hover {
    background: #f7fafc;
  }

  #clientDropdownMenu .form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    border: 2px solid #cbd5e0;
    margin-top: 2px;
  }

  #clientDropdownMenu .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
  }

  #clientDropdownMenu .form-check-label {
    cursor: pointer;
    margin-left: 8px;
    font-size: 0.95rem;
    user-select: none;
    width: calc(100% - 30px);
  }

  #clientCountBadge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 10px;
    margin-left: 8px;
  }

  #selectAllClientsBtn,
  #clearAllClientsBtn {
    font-size: 0.85rem;
    padding: 0;
    color: #667eea;
    text-decoration: none;
  }

  #selectAllClientsBtn:hover,
  #clearAllClientsBtn:hover {
    color: #764ba2;
    text-decoration: underline;
  }

  .dropdown-divider {
    margin: 8px 0;
    border-top: 1px solid #e2e8f0;
  }

  /* Prevent dropdown from closing on click inside */
  .dropdown-menu {
    cursor: default;
  }

  /* Filter button styles */
  .btn-apply-filters {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 500;
    padding: 8px 20px;
    border-radius: 6px;
    transition: all 0.3s;
  }

  .btn-apply-filters:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    color: white;
  }

  .btn-reset-filters {
    background: #6c757d;
    color: white;
    border: none;
    font-weight: 500;
    padding: 8px 20px;
    border-radius: 6px;
    transition: all 0.3s;
  }

  .btn-reset-filters:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
  }

  /* ========================================
     EXPORT PROGRESS TOAST STYLES
     ======================================== */

  .export-progress-toast {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    max-width: 400px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    padding: 20px;
    border-left: 5px solid #28a745;
  }

  .export-progress-header {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 15px;
    color: #2d3748;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
  }

  .export-progress-item {
    padding: 8px;
    margin-bottom: 6px;
    border-radius: 6px;
    background: #f8f9fa;
    font-size: 0.9rem;
  }

  .export-progress-item.success {
    background: #d4edda;
    color: #155724;
  }

  .export-progress-item.error {
    background: #f8d7da;
    color: #721c24;
  }

  .export-progress-item.pending {
    background: #e2e3e5;
    color: #6c757d;
  }

  .export-progress-item.downloading {
    background: #cfe2ff;
    color: #004085;
    font-weight: 600;
  }

  .export-progress-close {
    position: absolute;
    top: 8px;
    right: 10px;
    background: transparent;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0 8px;
  }

  .export-progress-close:hover {
    color: #dc3545;
  }

  .download-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.85rem;
  }

  .download-link:hover {
    text-decoration: underline;
  }

  /* ========================================
     BULK UPDATE TABLE STYLES
     ======================================== */
  
  .bulk-update-table {
    width: 100%;
    margin-bottom: 1rem;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.85rem;
  }
  
  .bulk-update-table thead th {
    background: #667eea;
    color: white;
    padding: 10px 6px;
    font-weight: 600;
    font-size: 0.8rem;
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
    padding: 8px 6px;
    vertical-align: middle;
    font-size: 0.85rem;
  }
  
  .bulk-update-table .form-control,
  .bulk-update-table .form-select {
    font-size: 0.8rem;
    padding: 5px 8px;
    height: auto;
    width: 100%;
  }
  
  .bulk-update-table .form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  
  .mca-ref-badge {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
  }
  
  .pre-alert-date-text {
    color: #6c757d;
    font-size: 0.7rem;
    display: block;
    margin-top: 2px;
  }
  
  .bulk-update-summary {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
  }
  
  .bulk-update-summary h6 {
    color: #856404;
    margin-bottom: 10px;
    font-weight: 600;
  }
  
  .bulk-table-container {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
  }

  /* ========================================
     FORM FIELD STYLES
     ======================================== */

  .auto-generated-field { 
    background-color: #f8f9fa; 
    cursor: not-allowed; 
  }

  .readonly-field { 
    background-color: #e9ecef; 
    cursor: not-allowed; 
  }
  
  .clearing-status-auto-mode {
    border-left: 4px solid #28a745 !important;
  }

  .border-success {
    border-color: #28a745 !important;
    transition: border-color 0.3s ease;
  }
  
  .remarks-entry {
    border: 1px solid #dee2e6; 
    border-radius: 8px;
    padding: 15px; 
    margin-bottom: 15px; 
    background: #f8f9fa;
    position: relative;
  }

  .remarks-entry .btn-remove { 
    position: absolute; 
    top: 10px; 
    right: 10px; 
  }
  
  .accordion-button:not(.collapsed) { 
    background-color: #667eea; 
    color: white; 
  }
  
  .group-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; 
    padding: 10px 15px; 
    border-radius: 8px;
    margin-bottom: 20px; 
    margin-top: 20px;
  }

  .group-header i { 
    margin-right: 10px; 
  }
  
  .date-sequence-error {
    border-color: #dc3545 !important;
    animation: shake 0.5s;
  }
  
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }

  /* ========================================
     COMMODITY & PARTIELLE BUTTONS
     ======================================== */

  #addCommodityBtn, #addPartielleBtn {
    padding: 0.375rem 0.75rem;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    background: #28a745;
    border: 1px solid #28a745;
  }

  #addCommodityBtn:hover, #addPartielleBtn:hover {
    background: #218838;
    border-color: #1e7e34;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
  }

  #partielle_preview {
    font-size: 0.95rem;
    border-left: 4px solid #28a745;
    background: #d4edda;
    border-color: #c3e6cb;
  }

  #partielle_number {
    border-left: none;
  }

  .input-group-text {
    min-width: 60px;
    justify-content: center;
  }

  #partielle_prefix {
    background-color: #28a745;
    color: white;
    font-weight: 600;
    border-color: #28a745;
  }

  /* ========================================
     CURRENCY INPUT GROUP
     ======================================== */
  
  .currency-input-group {
    position: relative;
    display: flex;
    align-items: stretch;
  }
  
  .currency-input-group input {
    flex: 1;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    border-right: none !important;
  }
  
  .currency-input-group select {
    width: 100px;
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
    border-left: 1px solid #dee2e6 !important;
    background-color: #f8f9fa;
  }
  
  .currency-input-group select:focus {
    border-color: #80bdff;
    box-shadow: none;
  }
  
  .currency-input-group input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    z-index: 3;
  }

  /* ========================================
     FILES DETAIL TABLE
     ======================================== */

  .files-detail-table {
    font-size: 0.85rem;
    margin-bottom: 0;
  }

  .files-detail-table thead th {
    background: #667eea;
    color: white;
    font-weight: 600;
    text-align: center;
    padding: 10px 6px;
    white-space: nowrap;
    font-size: 0.85rem;
  }

  .files-detail-table tbody td {
    vertical-align: middle;
    padding: 8px 6px;
    text-align: center;
    font-size: 0.85rem;
  }

  .files-detail-table tbody tr:hover {
    background-color: #f3f4f6;
  }

  /* ========================================
     SWEETALERT OVERRIDES
     ======================================== */
  
  .swal2-popup {
    font-size: 1rem !important;
  }
  
  .swal2-title {
    font-size: 1.5rem !important;
  }
  
  .swal2-html-container {
    font-size: 0.95rem !important;
  }

  /* ========================================
     RESPONSIVE UTILITIES
     ======================================== */
  
  @media (min-width: 768px) {
    .col-md-2-4 {
      flex: 0 0 auto;
      width: 20%;
    }
  }
  
  .dataTables_wrapper .dataTables_scroll {
    overflow-x: auto;
  }
  
  .dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto;
  }

  /* ========================================
     SMALL TEXT UTILITIES
     ======================================== */

  .text-muted {
    color: #6c757d !important;
  }

  small, .small {
    font-size: 0.875em;
  }

  kbd {
    padding: 2px 6px;
    font-size: 0.875rem;
    color: #fff;
    background-color: #212529;
    border-radius: 3px;
  }

  /* ========================================
     EXPORT BUTTONS RIGHT ALIGNMENT
     ======================================== */
  .export-buttons-right {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 15px;
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- Statistics Cards with Icons -->
        <div class="row mb-4">
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="all">
              <div class="card-body">
                <div class="stats-card-icon icon-blue">
                  <i class="ti ti-truck-delivery"></i>
                </div>
                <div class="stats-value" id="totalTrackings">0</div>
                <div class="stats-label">Total Imports</div>
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
            <div class="card stats-card shadow-sm" data-filter="crf_missing">
              <div class="card-body">
                <div class="stats-card-icon icon-purple">
                  <i class="ti ti-file-text"></i>
                </div>
                <div class="stats-value" id="totalCRFMissing">0</div>
                <div class="stats-label">CRF Missing</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="ad_missing">
              <div class="card-body">
                <div class="stats-card-icon icon-cyan">
                  <i class="ti ti-file-alert"></i>
                </div>
                <div class="stats-value" id="totalADMissing">0</div>
                <div class="stats-label">AD Missing</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="insurance_missing">
              <div class="card-body">
                <div class="stats-card-icon icon-pink">
                  <i class="ti ti-shield-off"></i>
                </div>
                <div class="stats-value" id="totalInsuranceMissing">0</div>
                <div class="stats-label">Insurance Missing</div>
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
                <div class="stats-card-icon icon-yellow">
                  <i class="ti ti-building"></i>
                </div>
                <div class="stats-value" id="totalDgdaInPending">0</div>
                <div class="stats-label">DGDA In Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="liquidation_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-brown">
                  <i class="ti ti-cash"></i>
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
                  <i class="ti ti-receipt"></i>
                </div>
                <div class="stats-value" id="totalQuittancePending">0</div>
                <div class="stats-label">Quittance Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="dgda_out_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-orange">
                  <i class="ti ti-file-export"></i>
                </div>
                <div class="stats-value" id="totalDgdaOutPending">0</div>
                <div class="stats-label">DGDA Out Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-3">
            <div class="card stats-card shadow-sm" data-filter="dispatch_deliver_pending">
              <div class="card-body">
                <div class="stats-card-icon icon-lime">
                  <i class="ti ti-truck-loading"></i>
                </div>
                <div class="stats-value" id="totalDispatchDeliverPending">0</div>
                <div class="stats-label">Dispatch/Deliver Pending</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
        </div>

<!-- Advanced Filter Controls -->
<div class="card shadow-sm mb-4 filter-controls">
  <h6><i class="ti ti-filter"></i> Advanced Filters</h6>
  <div class="row">
    <!-- CLIENT DROPDOWN WITH CHECKBOXES -->
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-users me-1"></i> Filter by Clients
      </label>
      <div class="dropdown w-100">
        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" 
                type="button" 
                id="clientDropdownBtn" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
          <span id="clientDropdownLabel">All Clients</span>
          <span class="badge bg-primary ms-2" id="clientCountBadge" style="display:none;">0</span>
        </button>
        <ul class="dropdown-menu w-100 p-2" aria-labelledby="clientDropdownBtn" id="clientDropdownMenu">
          <li class="px-2 mb-2">
            <button type="button" class="btn btn-sm btn-link p-0" id="selectAllClientsBtn">Select All</button> | 
            <button type="button" class="btn btn-sm btn-link p-0" id="clearAllClientsBtn">Clear All</button>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li class="px-2" style="max-height: 300px; overflow-y: auto;">
            <?php foreach ($subscribers as $sub): ?>
            <div class="form-check mb-2">
              <input class="form-check-input client-filter-checkbox" 
                     type="checkbox" 
                     value="<?= $sub['id'] ?>" 
                     id="client_filter_<?= $sub['id'] ?>"
                     data-client-name="<?= htmlspecialchars($sub['short_name'], ENT_QUOTES, 'UTF-8') ?>">
              <label class="form-check-label w-100" for="client_filter_<?= $sub['id'] ?>">
                <?= htmlspecialchars($sub['short_name'], ENT_QUOTES, 'UTF-8') ?>
              </label>
            </div>
            <?php endforeach; ?>
          </li>
        </ul>
      </div>
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i>
        <span id="selectedClientCountText">No clients selected - showing all</span>
      </small>
    </div>

    <!-- TYPE OF GOODS FILTER -->
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-box me-1"></i> Type of Goods
      </label>
      <select class="form-select" id="filterTypeOfGoodsSelect">
        <option value="">All Types of Goods</option>
        <?php foreach ($type_of_goods as $type): ?>
          <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['goods_type'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i>
        <span id="selectedTypeOfGoodsText">All types</span>
      </small>
    </div>

    <!-- ENTRY POINT FILTER -->
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-map-pin me-1"></i> Entry Point
      </label>
      <select class="form-select" id="filterEntryPointSelect">
        <option value="">All Entry Points</option>
        <?php foreach ($entry_points as $point): ?>
          <option value="<?= $point['id'] ?>"><?= htmlspecialchars($point['transit_point_name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i>
        <span id="selectedEntryPointText">All entry points</span>
      </small>
    </div>

    <!-- TRANSPORT MODE FILTER -->
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-truck me-1"></i> Transport Mode
      </label>
      <select class="form-select" id="filterTransportModeSelect">
        <option value="">All Transport Modes</option>
        <?php foreach ($transport_modes as $mode): ?>
          <option value="<?= $mode['id'] ?>"><?= $mode['transport_mode_name'] ?></option>
        <?php endforeach; ?>
      </select>
      <small class="text-muted">
        <i class="ti ti-info-circle me-1"></i>
        <span id="selectedTransportModeText">All modes</span>
      </small>
    </div>
  </div>

  <!-- DATE FILTERS -->
  <div class="row">
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-calendar me-1"></i> Start Date
      </label>
      <input type="date" class="form-control" id="filterStartDate">
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">
        <i class="ti ti-calendar me-1"></i> End Date
      </label>
      <input type="date" class="form-control" id="filterEndDate">
    </div>
  </div>

  <!-- ACTION BUTTONS -->
  <div class="row mt-2">
    <div class="col-12 d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-reset-filters" id="resetFiltersBtn">
        <i class="ti ti-refresh me-1"></i> Reset All Filters
      </button>
    </div>
  </div>
</div>

        <!-- Export Buttons - RIGHT ALIGNED -->
        <div class="export-buttons-right">
          <button type="button" class="btn btn-sm btn-border-team" id="exportBorderTeamBtn">
            <i class="ti ti-building me-1"></i> Border Team
          </button>
          <button type="button" class="btn btn-sm btn-export-all" id="exportAllBtn">
            <i class="ti ti-file-spreadsheet me-1"></i> Export All to Excel
          </button>
        </div>

        <!-- Card with Form and Table -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <form id="importForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="import_id" id="import_id" value="">
              <input type="hidden" name="action" id="formAction" value="insert">

              <div class="accordion" id="importAccordion">
                
                <!-- IMPORT TRACKING ACCORDION -->
                <div class="accordion-item mb-3">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#importTracking">
                      <i class="ti ti-file-import me-2"></i> Import Tracking
                    </button>
                  </h2>

                  <div id="importTracking" class="accordion-collapse collapse" data-bs-parent="#importAccordion">
                    <div class="accordion-body">

                      <!-- DOCUMENTATION -->
                      <div class="group-header">
                        <i class="ti ti-file-text"></i>Documentation
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Client <span class="text-danger">*</span></label>
                          <select name="subscriber_id" id="subscriber_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            <?php foreach ($subscribers as $sub): ?>
                              <option value="<?= $sub['id'] ?>" data-liquidation="<?= $sub['liquidation_paid_by'] ?? '' ?>"><?= $sub['short_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="subscriber_id_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>License Number <span class="text-danger">*</span></label>
                          <select name="license_id" id="license_id" class="form-select" required>
                            <option value="">-- Select License --</option>
                          </select>
                          <div class="invalid-feedback" id="license_id_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Kind <span class="text-danger">*</span></label>
                          <input type="hidden" name="kind" id="kind_hidden">
                          <input type="text" id="kind_display" class="form-control readonly-field" readonly placeholder="From License">
                          <div class="invalid-feedback" id="kind_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Type of Goods <span class="text-danger">*</span></label>
                          <input type="hidden" name="type_of_goods" id="type_of_goods_hidden">
                          <input type="text" id="type_of_goods_display" class="form-control readonly-field" readonly placeholder="From License">
                          <div class="invalid-feedback" id="type_of_goods_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Transport Mode <span class="text-danger">*</span></label>
                          <input type="hidden" name="transport_mode" id="transport_mode_hidden">
                          <input type="text" id="transport_mode_display" class="form-control readonly-field" readonly placeholder="From License">
                          <div class="invalid-feedback" id="transport_mode_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>MCA Reference <span class="text-danger">*</span> <small class="text-muted">(Auto)</small></label>
                          <input type="text" name="mca_ref" id="mca_ref" class="form-control auto-generated-field" required readonly placeholder="Auto-generated">
                          <div class="invalid-feedback" id="mca_ref_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Currency <span class="text-danger">*</span></label>
                          <input type="hidden" name="currency" id="currency_hidden">
                          <input type="text" id="currency_display" class="form-control readonly-field" readonly placeholder="From License">
                          <div class="invalid-feedback" id="currency_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>License Invoice Number <small class="text-muted">(From License)</small></label>
                          <input type="text" name="license_invoice_number" id="license_invoice_number" class="form-control readonly-field" readonly placeholder="From License">
                          <div class="invalid-feedback" id="license_invoice_number_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Supplier <span class="text-danger">*</span></label>
                          <input type="text" name="supplier" id="supplier" class="form-control" placeholder="Enter supplier name" maxlength="255">
                          <div class="invalid-feedback" id="supplier_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Regime <span class="text-danger">*</span></label>
                          <select name="regime" id="regime" class="form-select" required>
                            <option value="">-- Select Regime --</option>
                            <?php foreach ($regimes as $regime): ?>
                              <option value="<?= $regime['id'] ?>"><?= $regime['regime_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="regime_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Types of Clearance <span class="text-danger">*</span></label>
                          <select name="types_of_clearance" id="types_of_clearance" class="form-select" required>
                            <option value="">-- Select Clearance --</option>
                            <?php foreach ($clearance_types as $type): ?>
                              <option value="<?= $type['id'] ?>" <?= ($type['id'] == 1) ? 'selected' : '' ?>><?= $type['clearance_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="types_of_clearance_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Declaration Office</label>
                          <select name="declaration_office_id" id="declaration_office_id" class="form-select">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($sub_offices as $office): ?>
                              <option value="<?= $office['id'] ?>"><?= $office['sub_office_name'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="declaration_office_id_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Pre-Alert Date <span class="text-danger">*</span></label>
                          <input type="date" name="pre_alert_date" id="pre_alert_date" class="form-control" required>
                          <div class="invalid-feedback" id="pre_alert_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Invoice <span class="text-danger">*</span></label>
                          <input type="text" name="invoice" id="invoice" class="form-control" required maxlength="100">
                          <div class="invalid-feedback" id="invoice_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Commodity <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <select name="commodity" id="commodity" class="form-select" required>
                              <option value="">-- Select Commodity --</option>
                              <?php foreach ($commodities as $commodity): ?>
                                <option value="<?= $commodity['id'] ?>"><?= $commodity['commodity_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-success" id="addCommodityBtn" title="Add Commodity">
                              <i class="ti ti-plus"></i>
                            </button>
                          </div>
                          <div class="invalid-feedback" id="commodity_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>PO Reference</label>
                          <input type="text" name="po_ref" id="po_ref" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="po_ref_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Fret</label>
                          <div class="currency-input-group">
                            <input type="number" step="0.01" name="fret" id="fret" class="form-control" min="0" placeholder="Amount">
                            <select name="fret_currency" id="fret_currency" class="form-select">
                              <option value="">Currency</option>
                              <?php foreach ($currencies as $curr): ?>
                                <option value="<?= $curr['id'] ?>"><?= $curr['currency_short_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="invalid-feedback" id="fret_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Other Charges</label>
                          <div class="currency-input-group">
                            <input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control" min="0" placeholder="Amount">
                            <select name="other_charges_currency" id="other_charges_currency" class="form-select">
                              <option value="">Currency</option>
                              <?php foreach ($currencies as $curr): ?>
                                <option value="<?= $curr['id'] ?>"><?= $curr['currency_short_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="invalid-feedback" id="other_charges_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>CRF Reference <small class="text-muted">(From License)</small></label>
                          <input type="text" name="crf_reference" id="crf_reference" class="form-control readonly-field" readonly maxlength="100" placeholder="From License">
                          <div class="invalid-feedback" id="crf_reference_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>CRF Received Date <small class="text-muted">(>= Pre-Alert)</small></label>
                          <input type="date" name="crf_received_date" id="crf_received_date" class="form-control document-status-trigger date-after-prealert">
                          <div class="invalid-feedback" id="crf_received_date_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Clearing Based On</label>
                          <select name="clearing_based_on" id="clearing_based_on" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach ($clearing_based_on_options as $option): ?>
                              <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="clearing_based_on_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>AD Date <small class="text-muted">(>= Pre-Alert)</small></label>
                          <input type="date" name="ad_date" id="ad_date" class="form-control document-status-trigger date-after-prealert">
                          <div class="invalid-feedback" id="ad_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Insurance Date <small class="text-muted">(>= Pre-Alert)</small></label>
                          <input type="date" name="insurance_date" id="insurance_date" class="form-control document-status-trigger date-after-prealert">
                          <div class="invalid-feedback" id="insurance_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Insurance Amount</label>
                          <div class="currency-input-group">
                            <input type="number" step="0.01" name="insurance_amount" id="insurance_amount" class="form-control" min="0" placeholder="Amount">
                            <select name="insurance_amount_currency" id="insurance_amount_currency" class="form-select">
                              <option value="">Currency</option>
                              <?php foreach ($currencies as $curr): ?>
                                <option value="<?= $curr['id'] ?>"><?= $curr['currency_short_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="invalid-feedback" id="insurance_amount_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Insurance Reference</label>
                          <input type="text" name="insurance_reference" id="insurance_reference" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="insurance_reference_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Inspection Reports (PARTIELLE) <span class="text-danger" id="partielle_required_indicator" style="display:none;">*</span></label>
                          <div class="input-group">
                            <select name="inspection_reports" id="inspection_reports" class="form-select">
                              <option value="">-- Select PARTIELLE --</option>
                            </select>
                            <button type="button" class="btn btn-success" id="addPartielleBtn" title="Manage PARTIELLE">
                              <i class="ti ti-settings"></i>
                            </button>
                          </div>
                          <div class="invalid-feedback" id="inspection_reports_error"></div>
                        </div>

                     <div class="col-md-2-4 mb-3">
                        <label>Remaining Weight</label>
                        <input type="number" step="0.01" name="rem_weight" id="rem_weight" disabled class="form-control" min="0" placeholder="Enter Remaining weight in KG">
                        <input type="hidden" name="rem_weight_hidden" id="rem_weight_hidden">
                        <div class="invalid-feedback" id="rem_weight_error"></div>
                      </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Weight <span class="text-danger">*</span></label>
                          <input type="number" step="0.01" name="weight" id="weight" class="form-control" required min="0" placeholder="Enter weight in KG">
                          <div class="invalid-feedback" id="weight_error"></div>
                        </div>
                        <div class="col-md-2-4 mb-3" id="r_m3_field" style="display:none;">
                          <label>Remaining M3 <small class="text-muted">(Liquid)</small></label>
                         <input type="number" step="any" name="r_m3" id="r_m3" class="form-control" min="0" placeholder="Remaining M3">
                         <input type="hidden" name="r_m3_hidden" id="r_m3_hidden">
                          <div class="invalid-feedback" id="r_m3_error"></div>
                        </div>
                        <div class="col-md-2-4 mb-3" id="m3_field" style="display:none;">
                          <label>M3 <small class="text-muted">(Liquid)</small></label>
                         <input type="number" step="any" name="m3" id="m3" class="form-control" min="0" placeholder="Enter M3">
                          <div class="invalid-feedback" id="m3_error"></div>
                        </div>
                        
                        <div class="col-md-2-4 mb-3" id="cession_date_field" style="display:none;">
                          <label>Cession Date <small class="text-muted">(Liquid)</small></label>
                          <input type="date" name="cession_date" id="cession_date" class="form-control">
                          <div class="invalid-feedback" id="cession_date_error"></div>
                        </div>

                       <div class="col-md-2-4 mb-3">
                          <label>Remaining FOB</label>
                          <div class="currency-input-group">
                            <input type="number" step="0.01" name="r_fob" id="r_fob" disabled class="form-control" min="0" placeholder="Amount">
                            <input type="hidden" name="r_fob_hidden" id="r_fob_hidden">
                            <select name="r_fob_currency" id="r_fob_currency" class="form-select">                          
                              <option value="">Currency</option>
                              <?php foreach ($currencies as $curr): ?>
                                <option value="<?= $curr['id'] ?>"><?= $curr['currency_short_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="invalid-feedback" id="r_fob_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>FOB <span class="text-danger">*</span></label>
                          <div class="currency-input-group">
                            <input type="number" step="0.01" name="fob" id="fob" class="form-control" required min="0" placeholder="Amount">
                            <select name="fob_currency" id="fob_currency" class="form-select">
                              <option value="">Currency</option>
                              <?php foreach ($currencies as $curr): ?>
                                <option value="<?= $curr['id'] ?>"><?= $curr['currency_short_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="invalid-feedback" id="fob_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Archive Reference</label>
                          <input type="text" name="archive_reference" id="archive_reference" class="form-control" maxlength="100" placeholder="Enter archive reference">
                          <div class="invalid-feedback" id="archive_reference_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Audited Date <small class="text-muted">(>= Pre-Alert)</small></label>
                          <input type="date" name="audited_date" id="audited_date" class="form-control date-after-prealert">
                          <div class="invalid-feedback" id="audited_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Archived Date <small class="text-muted">(>= Pre-Alert)</small></label>
                          <input type="date" name="archived_date" id="archived_date" class="form-control date-after-prealert">
                          <div class="invalid-feedback" id="archived_date_error"></div>
                        </div>
                      </div>

                      <!-- AIR FIELDS IN DOCUMENTATION -->
                      <div id="air_fields_documentation" style="display:none;">
                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Airway Bill <small class="text-muted">(Air)</small></label>
                            <input type="text" name="airway_bill" id="airway_bill" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="airway_bill_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Airway Bill Weight <small class="text-muted">(Air)</small></label>
                            <input type="number" step="0.01" name="airway_bill_weight" id="airway_bill_weight" class="form-control" min="0">
                            <div class="invalid-feedback" id="airway_bill_weight_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Entry Point <span class="text-danger">*</span></label>
                            <select name="entry_point_id_air" id="entry_point_id_air" class="form-select" required>
                              <option value="">-- Select --</option>
                              <?php foreach ($entry_points as $point): ?>
                                <option value="<?= $point['id'] ?>"><?= $point['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="entry_point_id_air_error"></div>
                          </div>
                        </div>
                      </div>

                      <!-- ROAD/RAIL FIELDS -->
                      <div id="road_rail_fields" style="display:none;">
                        <div class="row">
                          <div class="col-md-2-4 mb-3" id="road_manifest_field">
                            <label>Road Manifest <small class="text-muted">(Road/Rail)</small></label>
                            <input type="text" name="road_manif" id="road_manif" class="form-control" maxlength="100" placeholder="Enter road manifest number">
                            <div class="invalid-feedback" id="road_manif_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="wagon_field">
                            <label>Wagon <small class="text-muted">(Rail)</small></label>
                            <input type="text" name="wagon" id="wagon" class="form-control" maxlength="100" placeholder="Enter wagon number">
                            <div class="invalid-feedback" id="wagon_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="horse_field">
                            <label>Horse <small class="text-muted">(Road)</small></label>
                            <input type="text" name="horse" id="horse" class="form-control" maxlength="100" placeholder="Enter horse number">
                            <small id="horse_msg" class="text-danger d-none"></small>
                            <div class="invalid-feedback" id="horse_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="trailer_1_field">
                            <label>Trailer 1 <small class="text-muted">(Road)</small></label>
                            <input type="text" name="trailer_1" id="trailer_1" class="form-control" maxlength="100" placeholder="Enter trailer 1 number">
                            <div class="invalid-feedback" id="trailer_1_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="trailer_2_field">
                            <label>Trailer 2 <small class="text-muted">(Road)</small></label>
                            <input type="text" name="trailer_2" id="trailer_2" class="form-control" maxlength="100" placeholder="Enter trailer 2 number">
                            <div class="invalid-feedback" id="trailer_2_error"></div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Container <small class="text-muted">(Road/Rail)</small></label>
                            <input type="text" name="container" id="container" class="form-control" maxlength="100" placeholder="Enter container number">
                            <div class="invalid-feedback" id="container_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Entry Point <span class="text-danger">*</span></label>
                            <select name="entry_point_id" id="entry_point_id" class="form-select" required>
                              <option value="">-- Select --</option>
                              <?php foreach ($entry_points as $point): ?>
                                <option value="<?= $point['id'] ?>"><?= $point['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="entry_point_id_error"></div>
                          </div>
                        </div>
                      </div>

                      <!-- DECLARATION -->
                      <div class="group-header">
                        <i class="ti ti-file-certificate"></i>Declaration
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>DGDA In Date</label>
                          <input type="date" name="dgda_in_date" id="dgda_in_date" class="form-control">
                          <div class="invalid-feedback" id="dgda_in_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Declaration Reference</label>
                          <input type="text" name="declaration_reference" id="declaration_reference" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="declaration_reference_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>SEGUES RCV Reference</label>
                          <input type="text" name="segues_rcv_ref" id="segues_rcv_ref" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="segues_rcv_ref_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>SEGUES Payment Date</label>
                          <input type="date" name="segues_payment_date" id="segues_payment_date" class="form-control">
                          <div class="invalid-feedback" id="segues_payment_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Customs Manifest Number</label>
                          <input type="text" name="customs_manifest_number" id="customs_manifest_number" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="customs_manifest_number_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Customs Manifest Date</label>
                          <input type="date" name="customs_manifest_date" id="customs_manifest_date" class="form-control">
                          <div class="invalid-feedback" id="customs_manifest_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Liquidation Reference</label>
                          <input type="text" name="liquidation_reference" id="liquidation_reference" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="liquidation_reference_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Liquidation Date</label>
                          <input type="date" name="liquidation_date" id="liquidation_date" class="form-control">
                          <div class="invalid-feedback" id="liquidation_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Liquidation Paid By <small class="text-muted">(From Client)</small></label>
                          <input type="text" name="liquidation_paid_by" id="liquidation_paid_by" class="form-control readonly-field" readonly placeholder="From Client">
                          <div class="invalid-feedback" id="liquidation_paid_by_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Liquidation Amount</label>
                          <input type="number" step="0.01" name="liquidation_amount" id="liquidation_amount" class="form-control" min="0">
                          <div class="invalid-feedback" id="liquidation_amount_error"></div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2-4 mb-3">
                          <label>Quittance Reference</label>
                          <input type="text" name="quittance_reference" id="quittance_reference" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="quittance_reference_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Quittance Date <small class="text-muted">(Triggers Status)</small></label>
                          <input type="date" name="quittance_date" id="quittance_date" class="form-control clearing-status-trigger">
                          <div class="invalid-feedback" id="quittance_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>DGDA Out Date</label>
                          <input type="date" name="dgda_out_date" id="dgda_out_date" class="form-control">
                          <div class="invalid-feedback" id="dgda_out_date_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Document Status <small class="text-muted">(Auto)</small></label>
                          <select name="document_status" id="document_status" class="form-select auto-generated-field" readonly disabled>
                            <option value="">-- Select --</option>
                            <?php foreach ($document_statuses as $status): ?>
                              <option value="<?= $status['id'] ?>" <?= ($status['id'] == 1) ? 'selected' : '' ?>><?= $status['document_status'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <div class="invalid-feedback" id="document_status_error"></div>
                        </div>

                        <div class="col-md-2-4 mb-3">
                          <label>Customs Clearance Code</label>
                          <input type="text" name="customs_clearance_code" id="customs_clearance_code" class="form-control" maxlength="100">
                          <div class="invalid-feedback" id="customs_clearance_code_error"></div>
                        </div>
                      </div>

                      <!-- LOGISTICS & TRANSPORT -->
                      <div class="group-header">
                        <i class="ti ti-truck-delivery"></i>Logistics & Transport
                      </div>

                      <!-- AIR LOGISTICS FIELDS -->
                      <div id="air_logistics_fields" style="display:none;">
                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Airport Arrival Date <small class="text-muted">(Air)</small></label>
                            <input type="date" name="airport_arrival_date" id="airport_arrival_date" class="form-control">
                            <div class="invalid-feedback" id="airport_arrival_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Dispatch from Airport <small class="text-muted">(>= Arrival)</small></label>
                            <input type="date" name="dispatch_from_airport" id="dispatch_from_airport" class="form-control airport-date-validate">
                            <div class="invalid-feedback" id="dispatch_from_airport_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Operating Company <small class="text-muted">(Air)</small></label>
                            <select name="operating_company" id="operating_company" class="form-select">
                              <option value="">-- Select --</option>
                              <option value="AGL">AGL</option>
                              <option value="DGI">DGI</option>
                            </select>
                            <div class="invalid-feedback" id="operating_company_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Dispatch/Deliver Date <small class="text-muted">(Air)</small></label>
                            <input type="date" name="dispatch_deliver_date_air" id="dispatch_deliver_date_air" class="form-control">
                            <div class="invalid-feedback" id="dispatch_deliver_date_air_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Operating Days <small class="text-muted">(Air)</small></label>
                            <input type="number" name="operating_days" id="operating_days" class="form-control" min="0" placeholder="Enter operating days">
                            <div class="invalid-feedback" id="operating_days_error"></div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Operating Amount <small class="text-muted">(Air)</small></label>
                            <input type="number" step="0.01" name="operating_amount" id="operating_amount" class="form-control" min="0" placeholder="Enter operating amount">
                            <div class="invalid-feedback" id="operating_amount_error"></div>
                          </div>
                        </div>
                      </div>

                      <!-- ROAD/RAIL LOGISTICS FIELDS -->
                      <div id="road_rail_logistics_fields" style="display:none;">
                        <div class="row">
                          <div class="col-md-2-4 mb-3" id="t1_number_col">
                            <label>T1 Number <small class="text-muted">(Transfer)</small></label>
                            <input type="text" name="t1_number" id="t1_number" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="t1_number_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="t1_date_col">
                            <label>T1 Date <small class="text-muted">(Transfer)</small></label>
                            <input type="date" name="t1_date" id="t1_date" class="form-control">
                            <div class="invalid-feedback" id="t1_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Arrival Date Zambia</label>
                            <input type="date" name="arrival_date_zambia" id="arrival_date_zambia" class="form-control date-sequence-field" data-seq="1">
                            <div class="invalid-feedback" id="arrival_date_zambia_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Dispatch from Zambia</label>
                            <input type="date" name="dispatch_from_zambia" id="dispatch_from_zambia" class="form-control date-sequence-field" data-seq="2">
                            <div class="invalid-feedback" id="dispatch_from_zambia_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>DRC Entry Date</label>
                            <input type="date" name="drc_entry_date" id="drc_entry_date" class="form-control date-sequence-field" data-seq="3">
                            <div class="invalid-feedback" id="drc_entry_date_error"></div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-2-4 mb-3" id="border_arrival_col">
                            <label>Border Warehouse Arrival <small class="text-muted">(Triggers Status)</small></label>
                            <input type="date" name="border_warehouse_arrival_date" id="border_warehouse_arrival_date" class="form-control date-sequence-field clearing-status-trigger" data-seq="4">
                            <div class="invalid-feedback" id="border_warehouse_arrival_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="border_dispatch_col">
                            <label>Dispatch from Border</label>
                            <input type="date" name="dispatch_from_border" id="dispatch_from_border" class="form-control date-sequence-field" data-seq="5">
                            <div class="invalid-feedback" id="dispatch_from_border_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>IBS Coupon Reference</label>
                            <input type="text" name="ibs_coupon_reference" id="ibs_coupon_reference" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="ibs_coupon_reference_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Border Warehouse</label>
                            <select name="border_warehouse_id" id="border_warehouse_id" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($border_warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= $wh['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="border_warehouse_id_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Entry Coupon</label>
                            <input type="text" name="entry_coupon" id="entry_coupon" class="form-control" maxlength="100">
                            <div class="invalid-feedback" id="entry_coupon_error"></div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Bonded Warehouse</label>
                            <select name="bonded_warehouse_id" id="bonded_warehouse_id" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($bonded_warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= $wh['transit_point_name'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="bonded_warehouse_id_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Truck Status</label>
                            <select name="truck_status" id="truck_status" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($truck_statuses as $status): ?>
                                <option value="<?= htmlspecialchars($status['truck_status'], ENT_QUOTES, 'UTF-8') ?>"><?= $status['truck_status'] ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="truck_status_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Kanyaka Arrival Date</label>
                            <input type="date" name="kanyaka_arrival_date" id="kanyaka_arrival_date" class="form-control">
                            <div class="invalid-feedback" id="kanyaka_arrival_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Kanyaka Dispatch Date</label>
                            <input type="date" name="kanyaka_dispatch_date" id="kanyaka_dispatch_date" class="form-control">
                            <div class="invalid-feedback" id="kanyaka_dispatch_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Warehouse Arrival Date</label>
                            <input type="date" name="warehouse_arrival_date" id="warehouse_arrival_date" class="form-control">
                            <div class="invalid-feedback" id="warehouse_arrival_date_error"></div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-2-4 mb-3">
                            <label>Warehouse Departure Date</label>
                            <input type="date" name="warehouse_departure_date" id="warehouse_departure_date" class="form-control">
                            <div class="invalid-feedback" id="warehouse_departure_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3">
                            <label>Dispatch/Deliver Date</label>
                            <input type="date" name="dispatch_deliver_date" id="dispatch_deliver_date" class="form-control">
                            <div class="invalid-feedback" id="dispatch_deliver_date_error"></div>
                          </div>

                          <div class="col-md-2-4 mb-3" id="declaration_validity_col" style="display:none;">
                            <label>Declaration Validity <small class="text-muted">(Temp)</small></label>
                            <select name="declaration_validity" id="declaration_validity" class="form-select">
                              <option value="">-- Select --</option>
                              <?php foreach ($declaration_validity_options as $option): ?>
                                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                              <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="declaration_validity_error"></div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label>Clearing Status <span class="text-danger">*</span> 
                            <span class="badge bg-success">Auto</span>
                          </label>
                          <select name="clearing_status" id="clearing_status" class="form-select clearing-status-auto-mode" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($clearing_statuses as $status): ?>
                              <option value="<?= $status['id'] ?>"><?= $status['clearing_status'] ?></option>
                            <?php endforeach; ?>
                          </select>
                          <small class="text-muted">Automatically updated based on dates and progress</small>
                          <div class="invalid-feedback" id="clearing_status_error"></div>
                        </div>
                      </div>

                      <!-- REMARKS -->
                      <div class="mt-4 mb-3">
                        <h6><i class="ti ti-message-circle me-2"></i>Remarks</h6>
                        <input type="hidden" name="remarks" id="remarks_hidden" value="">
                        
                        <div id="remarksContainer"></div>

                        <button type="button" class="btn btn-sm btn-success" id="addRemarkBtn">
                          <i class="ti ti-plus me-1"></i> Add Remark
                        </button>
                      </div>

                    </div>
                  </div>
                </div>

              </div>

              <div class="row mt-4">
                <div class="col-12 text-end">
                  <button type="button" class="btn btn-secondary" id="cancelBtn">
                    <i class="ti ti-x me-1"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary ms-2" id="submitBtn">
                    <i class="ti ti-check me-1"></i> <span id="submitBtnText">Save Import</span>
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Imports DataTable -->
        <div class="card shadow-sm">
          <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0"><i class="ti ti-list me-2"></i> Imports List</h4>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-bulk-update" id="bulkUpdateBtn" disabled>
                <i class="ti ti-edit me-1"></i> Bulk Update
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">
                <i class="ti ti-filter-off me-1"></i> Clear All Filters
              </button>
              <span class="badge bg-primary" id="activeFiltersBadge" style="display: none;">0 Filters Active</span>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="importsTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>MCA Ref</th>
                    <th>Client</th>
                    <th>License</th>
                    <th>Invoice</th>
                    <th>Pre-Alert Date</th>
                    <th>Weight</th>
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

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="ti ti-edit me-2"></i> Bulk Update Imports
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="bulk-update-summary">
          <h6><i class="ti ti-info-circle me-2"></i>Filter Summary</h6>
          <p class="mb-0" id="bulkFilterSummary">No filter active</p>
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

<!-- Commodity Modal -->
<div class="modal fade" id="commodityModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title">
          <i class="ti ti-plus-circle me-2"></i> Create New Commodity
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: brightness(0) invert(1);"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Commodity Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="commodity_name_input" placeholder="Enter commodity name" maxlength="255">
          <small class="text-muted">Enter the name of the commodity</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-success" id="saveCommodityBtn">
          <i class="ti ti-check me-1"></i> Create Commodity
        </button>
      </div>
    </div>
  </div>
</div>

<!-- PARTIELLE Management Modal -->
<div class="modal fade" id="partielleModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="ti ti-table me-2"></i>PARTIELLE Management - <span id="partielle_license_display_main"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="partielle_license_id_hidden">
        <input type="hidden" id="partielle_subscriber_id_hidden">
        <input type="hidden" id="partielle_crf_reference_hidden">

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="alert alert-info mb-0">
              <strong>Client:</strong> <span id="partielle_client_display_main"></span><br>
              <strong>CRF Reference:</strong> <span id="partielle_crf_display_main"></span><br>
              <strong>License Weight:</strong> <span id="partielle_license_weight_display_main">0.00</span> KG<br>
              <strong>License FOB:</strong> <span id="partielle_license_fob_display_main">0.00</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="alert alert-success mb-0">
              <strong>Available Weight:</strong> <span id="partielle_available_weight_display_main" class="text-success fw-bold">0.00</span> KG<br>
              <strong>Available FOB:</strong> <span id="partielle_available_fob_display_main" class="text-success fw-bold">0.00</span>
            </div>
          </div>
        </div>

        <div class="mb-3">
          <button type="button" class="btn btn-success" id="addNewPartielleBtn">
            <i class="ti ti-plus me-1"></i> Add New PARTIELLE
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="partielleTable">
            <thead class="table-dark">
              <tr>
                <th style="width: 50px;">#</th>
                <th>REF COD</th>
                <th>PARTIELLE Number</th>
                <th>License Weight</th>
                <th>License FOB</th>
                <th>No of Files</th>
                <th>Partial Weight</th>
                <th>Partial FOB</th>
                <th>Weight Used in Files</th>
                <th>FOB Used in Files</th>
                <th>Partial Weight - Weight Used</th>
                <th>Partial FOB - FOB Used</th>
                <th style="width: 80px;">Action</th>
              </tr>
            </thead>
            <tbody id="partielleTableBody">
              <tr>
                <td colspan="13" class="text-center text-muted">No PARTIELLEs found. Click "Add New PARTIELLE" to create one.</td>
              </tr>
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

<!-- Add/Edit PARTIELLE Sub-Modal -->
<div class="modal fade" id="addPartielleSubModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="ti ti-plus-circle me-2"></i><span id="subModalTitle">Add New PARTIELLE</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_partielle_id">
        
        <div class="mb-3">
          <label class="form-label fw-bold">PARTIELLE Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text bg-primary text-white fw-bold" id="sub_partielle_prefix"></span>
            <input type="text" class="form-control" id="sub_partielle_number" 
                   placeholder="0001" required pattern="[0-9]{3,4}" maxlength="4">
          </div>
          <small class="text-muted">Enter 3-4 digit number (e.g., 0001, 0002)</small>
          <div class="invalid-feedback" id="sub_partielle_number_error"></div>
        </div>

        <div class="mb-3" id="sub_partielle_preview" style="display: none;">
          <div class="alert alert-info mb-0">
            <strong>Preview:</strong> <span id="sub_partielle_preview_text" class="text-primary fw-bold"></span>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">
            <i class="ti ti-weight me-1"></i>Partial Weight (KG)
          </label>
          <input type="number" class="form-control" id="sub_partial_weight" 
                 step="0.01" min="0" placeholder="0.00">
          <div class="invalid-feedback" id="sub_partial_weight_error"></div>
          <small class="text-muted">
            <strong>Available:</strong> <span id="sub_available_weight_display" class="text-success fw-bold">0.00</span> KG
          </small>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">
            <i class="ti ti-currency-dollar me-1"></i>Partial FOB
          </label>
          <input type="number" class="form-control" id="sub_partial_fob" 
                 step="0.01" min="0" placeholder="0.00">
          <div class="invalid-feedback" id="sub_partial_fob_error"></div>
          <small class="text-muted">
            <strong>Available:</strong> <span id="sub_available_fob_display" class="text-success fw-bold">0.00</span>
          </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-success" id="saveSubPartielleBtn">
          <i class="ti ti-check me-1"></i> Save PARTIELLE
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Files Detail Modal -->
<div class="modal fade" id="filesDetailModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="ti ti-files me-2"></i>Files Using PARTIELLE: <span id="files_partielle_name"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>Total Files:</strong> <span id="files_total_count">0</span> | 
          <strong>Total Weight:</strong> <span id="files_total_weight">0.00</span> KG | 
          <strong>Total FOB:</strong> <span id="files_total_fob">0.00</span>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover files-detail-table">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>MCA Reference</th>
                <th>Inspection Reports</th>
                <th>Declaration Reference</th>
                <th>DGDA In Date</th>
                <th>Liquidation Reference</th>
                <th>Liquidation Date</th>
                <th>Quittance Reference</th>
                <th>Quittance Date</th>
                <th>Weight</th>
                <th>FOB</th>
              </tr>
            </thead>
            <tbody id="filesDetailTableBody">
              <tr>
                <td colspan="11" class="text-center text-muted">Loading files...</td>
              </tr>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
  // ========================================
  // PRODUCTION CONFIGURATION
  // ========================================
  
  const DEBUG = false; // Set to false for production
  const SUBMIT_COOLDOWN = 2000; // 2 seconds between submissions
  const AUTO_SAVE_INTERVAL = 30000; // Auto-save every 30 seconds
  
  // Debug logger (only logs in debug mode)
  const log = {
    info: (msg, data) => DEBUG && console.log(msg, data),
    error: (msg, data) => DEBUG && console.error(msg, data),
    warn: (msg, data) => DEBUG && console.warn(msg, data)
  };
  
  // User-friendly error messages
  const ERROR_MESSAGES = {
    NETWORK_ERROR: 'Unable to connect. Please check your internet connection.',
    SERVER_ERROR: 'Something went wrong. Please try again.',
    VALIDATION_ERROR: 'Please check the form for errors.',
    TIMEOUT_ERROR: 'Request timed out. Please try again.',
    LICENSE_LOAD_ERROR: 'Failed to load license information.',
    PARTIELLE_LOAD_ERROR: 'Failed to load PARTIELLE data.',
    SAVE_ERROR: 'Failed to save. Please try again.',
    DEFAULT: 'An unexpected error occurred. Please refresh and try again.'
  };
  
  // ========================================
  // VARIABLES
  // ========================================
  
  const csrfToken = $('#importForm').data('csrf-token');
  
  let clearingStatusIds = { in_transit_id: null, in_progress_id: null, completed_id: null };
  let selectedClientId = null;
  let selectedClientIds = []; // Array for multiple client selection
  let selectedTransportModeId = null;
  let selectedTypeOfGoodsId = null;
  let selectedEntryPointId = null;
  let selectedStartDate = null;
  let selectedEndDate = null;
  let currentTypeOfGoodsId = null;
  let currentKindId = null;
  let remarksArray = [];
  let remarkCounter = 0;
  let activeFilters = [];
  let bulkUpdateData = { data: [], relevant_fields: [] };
  let currentPartielleData = [];
  let currentLicenseData = {
    subscriber_id: null,
    license_id: null,
    crf_reference: null,
    license_weight: 0,
    license_fob: 0,
    available_weight: 0,
    available_fob: 0
  };
  
  // Rate limiting
  let lastSubmitTime = 0;
  let autoSaveInterval = null;

  // ========================================
  // ERROR HANDLING
  // ========================================
  
  function handleAjaxError(xhr, customMessage = null) {
    let message = customMessage || ERROR_MESSAGES.DEFAULT;
    
    log.error('AJAX Error:', {
      status: xhr?.status,
      statusText: xhr?.statusText,
      response: xhr?.responseJSON
    });
    
    if (!xhr) {
      message = ERROR_MESSAGES.SERVER_ERROR;
    } else if (xhr.status === 0) {
      message = ERROR_MESSAGES.NETWORK_ERROR;
    } else if (xhr.status === 403) {
      message = 'Your session has expired. Please refresh the page.';
    } else if (xhr.status === 404) {
      message = 'The requested resource was not found.';
    } else if (xhr.status >= 500) {
      message = ERROR_MESSAGES.SERVER_ERROR;
    } else if (xhr.status === 408) {
      message = ERROR_MESSAGES.TIMEOUT_ERROR;
    }
    
    Swal.fire({
      icon: 'error',
      title: 'Oops!',
      text: message,
      confirmButtonText: 'OK'
    });
  }

  // ========================================
  // FORM AUTO-SAVE
  // ========================================
  
  function autoSaveForm() {
    const formAction = $('#formAction').val();
    
    if (formAction !== 'insert') return;
    
    const formData = {
      subscriber_id: $('#subscriber_id').val(),
      license_id: $('#license_id').val(),
      regime: $('#regime').val(),
      types_of_clearance: $('#types_of_clearance').val(),
      pre_alert_date: $('#pre_alert_date').val(),
      invoice: $('#invoice').val(),
      commodity: $('#commodity').val(),
      supplier: $('#supplier').val(),
      weight: $('#weight').val(),
      fob: $('#fob').val(),
      fob_currency: $('#fob_currency').val(),
      inspection_reports: $('#inspection_reports').val(),
      timestamp: new Date().toISOString()
    };
    
    if (formData.subscriber_id || formData.invoice || formData.weight) {
      localStorage.setItem('import_form_draft', JSON.stringify(formData));
      log.info('Form auto-saved', formData);
    }
  }
  
  function restoreFormDraft() {
    const draft = localStorage.getItem('import_form_draft');
    if (!draft) return;
    
    try {
      const data = JSON.parse(draft);
      const draftDate = new Date(data.timestamp);
      const now = new Date();
      const hoursDiff = (now - draftDate) / (1000 * 60 * 60);
      
      if (hoursDiff > 24) {
        localStorage.removeItem('import_form_draft');
        return;
      }
      
      Swal.fire({
        title: 'Restore Unsaved Data?',
        html: `Found unsaved form data from <strong>${formatDate(data.timestamp)}</strong>.<br>Would you like to restore it?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Restore',
        cancelButtonText: 'Discard',
        confirmButtonColor: '#3085d6'
      }).then((result) => {
        if (result.isConfirmed) {
          Object.keys(data).forEach(key => {
            if (key !== 'timestamp') {
              const $field = $(`#${key}`);
              if ($field.length) {
                $field.val(data[key]);
                
                if ($field.is('select')) {
                  $field.trigger('change');
                }
              }
            }
          });
          
          Swal.fire({
            icon: 'success',
            title: 'Restored!',
            text: 'Your unsaved data has been restored.',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          clearFormDraft();
        }
      });
    } catch (error) {
      log.error('Failed to restore draft:', error);
      localStorage.removeItem('import_form_draft');
    }
  }
  
  function clearFormDraft() {
    localStorage.removeItem('import_form_draft');
    if (autoSaveInterval) {
      clearInterval(autoSaveInterval);
      autoSaveInterval = null;
    }
    log.info('Form draft cleared');
  }
  
  autoSaveInterval = setInterval(autoSaveForm, AUTO_SAVE_INTERVAL);
  restoreFormDraft();

  // ========================================
  // HELPER FUNCTIONS
  // ========================================
  
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
  }

  function clearValidationErrors() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
  }

  function showFieldError(fieldId, errorMessage) {
    $('#' + fieldId).addClass('is-invalid');
    $('#' + fieldId + '_error').text(errorMessage).show();
  }

  function checkPartielleRequired() {
    const kindId = parseInt($('#kind_hidden').val());
    
    if (kindId === 1 || kindId === 2) {
      $('#partielle_required_indicator').show();
      $('#inspection_reports').attr('required', true);
      return true;
    } else {
      $('#partielle_required_indicator').hide();
      $('#inspection_reports').removeAttr('required');
      return false;
    }
  }

  // ========================================
  // GLOBAL EVENT HANDLERS (SET UP ONCE)
  // ========================================
  
  $('#entry_point_id, #entry_point_id_air').on('change', function() {
    const val = $(this).val();
    if (val) {
      $('#entry_point_id').val(val);
      $('#entry_point_id_air').val(val);
    }
  });

  $('#types_of_clearance').on('change', function() {
    adjustLogisticsLayout();
  });

  $('.date-after-prealert').on('change', function() {
    validateDateAgainstPreAlert($(this));
  });

  $('#pre_alert_date').on('change', function() {
    $('.date-after-prealert').each(function() {
      if ($(this).val()) {
        validateDateAgainstPreAlert($(this));
      }
    });
  });

  $('#dispatch_from_airport').on('change', function() {
    validateAirportDateSequence();
  });

  $('#airport_arrival_date').on('change', function() {
    if ($('#dispatch_from_airport').val()) {
      validateAirportDateSequence();
    }
  });

  $('.date-sequence-field').on('change', function() {
    validateDateSequence($(this));
  });

  $('.document-status-trigger').on('change', function() {
    updateDocumentStatus();
  });

  $('.clearing-status-trigger').on('change', function() {
    suggestClearingStatus();
  });

  $('#addRemarkBtn').on('click', () => addRemarkEntry());
  $(document).on('change input', '.remark-date, .remark-text', updateRemarksHidden);

  // ========================================
  // ✅ NEW: DISPATCH/DELIVER DATE HANDLERS
  // ========================================
  
  // Prevent Air dispatch date from auto-overwriting Road/Rail dispatch date
  $('#dispatch_deliver_date_air').on('change', function() {
    const airDate = $(this).val();
    const roadRailDate = $('#dispatch_deliver_date').val();
    const transportMode = $('#transport_mode_display').val().toUpperCase();
    
    // Only update road/rail date if it's empty AND we're in Air mode
    if (airDate && !roadRailDate && transportMode.includes('AIR')) {
      $('#dispatch_deliver_date').val(airDate);
      log.info('Auto-synced Air dispatch date to main field (field was empty)');
    }
  });

  // Prevent Road/Rail dispatch date from auto-overwriting Air dispatch date
  $('#dispatch_deliver_date').on('change', function() {
    const roadRailDate = $(this).val();
    const airDate = $('#dispatch_deliver_date_air').val();
    const transportMode = $('#transport_mode_display').val().toUpperCase();
    
    // Only update air date if it's empty AND we're NOT in Air mode
    if (roadRailDate && !airDate && !transportMode.includes('AIR')) {
      $('#dispatch_deliver_date_air').val(roadRailDate);
      log.info('Auto-synced Road/Rail dispatch date to air field (field was empty)');
    }
  });

  // ========================================
  // INITIALIZATION
  // ========================================
  
  function loadClearingStatusIds() {
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getClearingStatusIds',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          clearingStatusIds = res.data;
          if (clearingStatusIds.in_transit_id) {
            $('#clearing_status').val(clearingStatusIds.in_transit_id);
          }
        }
      },
      error: function(xhr) {
        log.error('Failed to load clearing status IDs:', xhr);
      }
    });
  }
  
  loadClearingStatusIds();

  // ========================================
  // STATISTICS UPDATE
  // ========================================

  function updateStatistics() {
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/statistics',
      method: 'GET',
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success && res.data) {
          $('#totalTrackings').text(res.data.total_imports || 0);
          $('#totalCompleted').text(res.data.total_completed || 0);
          $('#totalInProgress').text(res.data.in_progress || 0);
          $('#totalInTransit').text(res.data.in_transit || 0);
          $('#totalCRFMissing').text(res.data.crf_missing || 0);
          $('#totalADMissing').text(res.data.ad_missing || 0);
          $('#totalInsuranceMissing').text(res.data.insurance_missing || 0);
          $('#totalAuditedPending').text(res.data.audited_pending || 0);
          $('#totalArchivedPending').text(res.data.archived_pending || 0);
          $('#totalDgdaInPending').text(res.data.dgda_in_pending || 0);
          $('#totalLiquidationPending').text(res.data.liquidation_pending || 0);
          $('#totalQuittancePending').text(res.data.quittance_pending || 0);
          $('#totalDgdaOutPending').text(res.data.dgda_out_pending || 0);
          $('#totalDispatchDeliverPending').text(res.data.dispatch_deliver_pending || 0);
        }
      },
      error: function(xhr) {
        log.error('Failed to update statistics:', xhr);
      }
    });
  }

  // ========================================
  // CHECKBOX DROPDOWN AUTO-FILTER
  // ========================================

  // Prevent dropdown from closing when clicking inside
  $('#clientDropdownMenu').on('click', function(e) {
    e.stopPropagation();
  });

function updateSelectedClients() {
    selectedClientIds = [];
    const clientNames = [];
    
    $('.client-filter-checkbox:checked').each(function() {
      selectedClientIds.push(parseInt($(this).val()));
      clientNames.push($(this).data('client-name'));
    });
    
    const count = selectedClientIds.length;
    
    // Update dropdown button text
    if (count === 0) {
      $('#clientDropdownLabel').text('All Clients');
      $('#clientCountBadge').hide();
      $('#selectedClientCountText').text('No clients selected - showing all');
      selectedClientId = null;
    } else if (count === 1) {
      $('#clientDropdownLabel').text(clientNames[0]);
      $('#clientCountBadge').text('1').show();
      $('#selectedClientCountText').text(`1 client selected: ${clientNames[0]}`);
      selectedClientId = selectedClientIds[0];
    } else {
      $('#clientDropdownLabel').text(`${count} Clients Selected`);
      $('#clientCountBadge').text(count).show();
      $('#selectedClientCountText').text(`${count} clients selected`);
      selectedClientId = null;
    }
    
    // AUTO-FILTER TABLE
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
    
    updateActiveFiltersDisplay();
    
    log.info('Selected clients:', selectedClientIds);
  }

  // Checkbox change handler - auto-filter on change
  $(document).on('change', '.client-filter-checkbox', function() {
    updateSelectedClients();
  });

  // Select All Clients button
  $('#selectAllClientsBtn').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $('.client-filter-checkbox').prop('checked', true);
    updateSelectedClients();
  });

  // Clear All Clients button
  $('#clearAllClientsBtn').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $('.client-filter-checkbox').prop('checked', false);
    updateSelectedClients();
  });

  // Transport mode filter change
  $('#filterTransportModeSelect').on('change', function() {
    selectedTransportModeId = $(this).val() ? parseInt($(this).val()) : null;
    
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
    
    updateActiveFiltersDisplay();
  });
  
  // Types Of Goods filter change
  $('#filterTypeOfGoodsSelect').on('change', function() {
    selectedTypeOfGoodsId = $(this).val() ? parseInt($(this).val()) : null;
    
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
    
    updateActiveFiltersDisplay();
  });
  
  // Entry Point filter change
  $('#filterEntryPointSelect').on('change', function() {
    selectedEntryPointId = $(this).val() ? parseInt($(this).val()) : null;
    
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
    
    updateActiveFiltersDisplay();
  });

  // Date filter changes
  $('#filterStartDate, #filterEndDate').on('change', function() {
    selectedStartDate = $('#filterStartDate').val() || null;
    selectedEndDate = $('#filterEndDate').val() || null;
    
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
    
    updateActiveFiltersDisplay();
  });

  // Reset Filters Button
  $('#resetFiltersBtn').on('click', function() {
    $('.stats-card').removeClass('active');
    $('.stats-card[data-filter="all"]').addClass('active');
    activeFilters = [];
    selectedClientId = null;
    selectedClientIds = [];
    selectedTransportModeId = null;
    selectedTypeOfGoodsId = null;
    selectedEntryPointId = null;
    selectedStartDate = null;
    selectedEndDate = null;
    
    $('.client-filter-checkbox').prop('checked', false);
    $('#filterTransportModeSelect').val('');
    $('#filterTypeOfGoodsSelect').val('');
    $('#filterEntryPointSelect').val('');
    $('#filterStartDate').val('');
    $('#filterEndDate').val('');
    
    updateSelectedClients();
    updateActiveFiltersDisplay();
    applyFiltersToTable();
    updateBulkUpdateButton();
  });

  // Stats card click handler
  $('.stats-card').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
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
        
        if (activeFilters.length === 0) {
          $('.stats-card[data-filter="all"]').addClass('active');
        }
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

  function updateActiveFiltersDisplay() {
    const totalFilters = activeFilters.length + 
                        selectedClientIds.length + 
                        (selectedTransportModeId ? 1 : 0) + 
                        (selectedTypeOfGoodsId ? 1 : 0) + 
                        (selectedEntryPointId ? 1 : 0) + 
                        (selectedStartDate ? 1 : 0) + 
                        (selectedEndDate ? 1 : 0);
    
    if (totalFilters > 0) {
      $('#activeFiltersBadge').show().text(totalFilters + ' Filter' + (totalFilters > 1 ? 's' : '') + ' Active');
    } else {
      $('#activeFiltersBadge').hide();
    }
  }

  function applyFiltersToTable() {
    if (typeof importsTable !== 'undefined') {
      importsTable.ajax.reload();
    }
  }

  function updateBulkUpdateButton() {
    const allowedFiltersForBulkUpdate = [
      'crf_missing', 
      'ad_missing', 
      'insurance_missing', 
      'audited_pending', 
      'archived_pending',
      'dgda_in_pending',
      'liquidation_pending',
      'quittance_pending',
      'dgda_out_pending',
      'dispatch_deliver_pending'
    ];
    
    const hasAllowedFilters = activeFilters.some(filter => 
      allowedFiltersForBulkUpdate.includes(filter)
    );
    
    $('#bulkUpdateBtn').prop('disabled', !hasAllowedFilters);
  }

  // ========================================
  // TRANSPORT MODE & M3 FIELD MANAGEMENT  
  // ========================================

  function handleTransportModeFields(transportModeId, transportModeName) {
    $('#air_fields_documentation, #air_logistics_fields, #road_rail_fields, #road_rail_logistics_fields').hide();
    $('#m3_field,#r_m3_field, #cession_date_field, #wagon_field, #road_manifest_field, #declaration_validity_col').hide();
    $('#t1_number_col, #t1_date_col, #border_arrival_col, #border_dispatch_col').hide();
    $('#horse_field, #trailer_1_field, #trailer_2_field').hide();
    
    const modeId = parseInt(transportModeId);
    const modeName = (transportModeName || '').toUpperCase();
    
    // AIR mode
    if (modeId === 2 || modeName.includes('AIR')) {
      $('#air_fields_documentation').show();
      $('#air_logistics_fields').show();
    }
    // RAIL/WAGON mode
    else if (modeId === 3 || modeName.includes('RAIL') || modeName.includes('WAGON')) {
      $('#road_rail_fields').show();
      $('#road_rail_logistics_fields').show();
      $('#wagon_field').show();
      $('#road_manifest_field').show();
      adjustLogisticsLayout();
    }
    // ROAD mode
    else if (modeName.includes('ROAD')) {
      $('#road_rail_fields').show();
      $('#road_rail_logistics_fields').show();
      $('#road_manifest_field').show();
      $('#horse_field, #trailer_1_field, #trailer_2_field').show();
      adjustLogisticsLayout();
    }
    // Other modes
    else {
      $('#road_rail_logistics_fields').show();
      adjustLogisticsLayout();
    }
  }

  function handleTypeOfGoodsChange(typeOfGoodsId) {
    currentTypeOfGoodsId = typeOfGoodsId;
    
    // Show M3 and Cession Date fields only for Type of Goods = 3 (LIQUID)
    if (parseInt(typeOfGoodsId) === 3) {
      $('#m3_field').show();
      $('#r_m3_field').show();
      $('#cession_date_field').show();
    } else {
      $('#m3_field').hide();
      $('#cession_date_field').hide();
      $('#m3').val('');
      $('#cession_date').val('');
    }
  }

  function adjustLogisticsLayout() {
    const clearanceId = parseInt($('#types_of_clearance').val());
    const transportMode = $('#transport_mode_display').val().toUpperCase();
    const kindId = parseInt($('#kind_hidden').val());
    
    if (clearanceId === 3 && !transportMode.includes('AIR')) {
      // For TRANSFER clearance, show BOTH T1 fields AND Border warehouse fields
      $('#t1_number_col, #t1_date_col').show();
      $('#border_arrival_col, #border_dispatch_col').show();
    } else {
      // For other clearance types: Hide T1 fields, show Border warehouse fields
      $('#t1_number_col, #t1_date_col').hide();
      $('#t1_number, #t1_date').val('');
      $('#border_arrival_col, #border_dispatch_col').show();
    }
    
    // Only show Declaration Validity if Kind = 2 (IMPORT TEMPORARY)
    if (kindId === 2) {
      $('#declaration_validity_col').show();
    } else {
      $('#declaration_validity_col').hide();
      $('#declaration_validity').val('');
    }
  }

  // ========================================
  // COMMODITY FUNCTIONALITY
  // ========================================

  $('#addCommodityBtn').on('click', function() {
    $('#commodity_name_input').val('');
    $('#commodityModal').modal('show');
  });

  $('#saveCommodityBtn').on('click', function() {
    const commodityName = $('#commodity_name_input').val().trim();
    
    if (!commodityName) {
      Swal.fire({
        icon: 'warning',
        title: 'Name Required',
        text: 'Please enter a commodity name.',
        confirmButtonText: 'OK'
      });
      return;
    }
    
    const $saveBtn = $('#saveCommodityBtn');
    const originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Creating...');
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/createCommodity',
      method: 'POST',
      data: {
        commodity_name: commodityName,
        csrf_token: csrfToken
      },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        $saveBtn.prop('disabled', false).html(originalText);
        
        if (res.success) {
          $('#commodity').append(new Option(commodityName, res.id));
          $('#commodity').val(res.id);
          $('#commodityModal').modal('hide');
          
          Swal.fire({
            icon: 'success',
            title: 'Created!',
            text: 'Commodity created successfully!',
            timer: 1500,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: res.message || 'Failed to create commodity',
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        $saveBtn.prop('disabled', false).html(originalText);
        handleAjaxError(xhr, 'Failed to create commodity');
      }
    });
  });

  function loadCommoditiesForLicense() {
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getCommodities',
      method: 'GET',
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          $('#commodity').html('<option value="">-- Select Commodity --</option>');
          res.data.forEach(function(commodity) {
            $('#commodity').append(new Option(commodity.commodity_name, commodity.id));
          });
        }
      },
      error: function(xhr) {
        log.error('Failed to load commodities:', xhr);
      }
    });
  }

  // ========================================
  // LICENSE & CLIENT MANAGEMENT (PROMISE-BASED)
  // ========================================

  function loadLicensesForClient(subscriberId) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: '<?= APP_URL ?>/import/crudData/getLicenses',
        method: 'GET',
        data: { subscriber_id: subscriberId },
        dataType: 'json',
        timeout: 10000,
        success: function(res) {
          if (res.success && res.data.length > 0) {
            $('#license_id').html('<option value="">-- Select License --</option>');
            res.data.forEach(function(license) {
              $('#license_id').append(`<option value="${license.id}">${escapeHtml(license.license_number)} (${escapeHtml(license.ref_cod)})</option>`);
            });
            resolve(res.data);
          } else {
            Swal.fire({
              icon: 'info',
              title: 'No Import Licenses',
              text: 'No active import licenses found for this client.',
              timer: 3000,
              showConfirmButton: false
            });
            reject(new Error('No licenses found'));
          }
        },
        error: function(xhr) {
          handleAjaxError(xhr, ERROR_MESSAGES.LICENSE_LOAD_ERROR);
          reject(xhr);
        }
      });
    });
  }

  $('#subscriber_id').on('change', function() {
    const subscriberId = $(this).val();
    const selectedOption = $(this).find('option:selected');
    const liquidationPaidBy = selectedOption.data('liquidation');

    $('#license_id').html('<option value="">-- Select License --</option>');
    
    if (!subscriberId) {
      clearLicenseFields();
      $('#liquidation_paid_by').val('');
      return;
    }

    if (liquidationPaidBy == 1) {
      $('#liquidation_paid_by').val('Client');
    } else if (liquidationPaidBy == 2) {
      $('#liquidation_paid_by').val('Malabar');
    } else {
      $('#liquidation_paid_by').val('');
    }

    loadLicensesForClient(subscriberId).catch(() => {
      // Error already handled by loadLicensesForClient
    });
  });

  $('#license_id').on('change', function() {
    const licenseId = $(this).val();
    
    if (!licenseId) {
      $('#inspection_reports').html('<option value="">-- Select PARTIELLE --</option>');
      clearLicenseFields();
      return;
    }
    
    $('#kind_display, #type_of_goods_display, #transport_mode_display, #currency_display, #supplier, #crf_reference, #license_invoice_number').val('Loading...');

    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getLicenseDetails',
      method: 'GET',
      data: { license_id: licenseId },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success && res.data) {
          const license = res.data;
          
          $('#kind_hidden').val(license.kind_id || '');
          $('#type_of_goods_hidden').val(license.type_of_goods_id || '');
          $('#transport_mode_hidden').val(license.transport_mode_id || '');
          $('#currency_hidden').val(license.currency_id || '');
          $('#rem_weight').val(license.rem_weight || '');
          $('#r_fob').val(license.r_fob || '');
          $('#r_m3').val(license.r_m3 || '');
          $('#r_m3_hidden').val(license.r_m3 || '');
          $('#rem_weight_hidden').val(license.rem_weight || '');
          $('#r_fob_hidden').val(license.r_fob || '');
          currentTypeOfGoodsId = license.type_of_goods_id || null;
          currentKindId = license.kind_id || null;
          
          $('#kind_display').val(escapeHtml(license.kind_name || ''));
          $('#type_of_goods_display').val(escapeHtml(license.type_of_goods_name || ''));
          $('#transport_mode_display').val(escapeHtml(license.transport_mode_name || ''));
          $('#currency_display').val(escapeHtml(license.currency_name || ''));
          $('#supplier').val(escapeHtml(license.supplier || ''));
          $('#crf_reference').val(escapeHtml(license.ref_cod || ''));
          $('#license_invoice_number').val(escapeHtml(license.invoice_number || ''));
          
          syncCurrencyFields(license.currency_id);
          generateMCAReference();
          handleTransportModeFields(license.transport_mode_id, license.transport_mode_name);
          handleTypeOfGoodsChange(license.type_of_goods_id);
          checkPartielleRequired();
          adjustLogisticsLayout();
        } else {
          clearLicenseFields();
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res.message || ERROR_MESSAGES.LICENSE_LOAD_ERROR,
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        clearLicenseFields();
        handleAjaxError(xhr, ERROR_MESSAGES.LICENSE_LOAD_ERROR);
      }
    });
    
    loadPartielleOptionsForLicense(licenseId);
  });

  function syncCurrencyFields(currencyId) {
    if (currencyId) {
      $('#fret_currency').val(currencyId);
      $('#other_charges_currency').val(currencyId);
      $('#fob_currency').val(currencyId);
      $('#r_fob_currency').val(currencyId);
      $('#insurance_amount_currency').val(currencyId);
    }
  }

  function clearLicenseFields() {
    $('#kind_hidden, #type_of_goods_hidden, #transport_mode_hidden, #currency_hidden').val('');
    $('#kind_display, #type_of_goods_display, #transport_mode_display, #currency_display').val('');
    $('#supplier, #crf_reference, #license_invoice_number').val('');
    $('#commodity').html('<option value="">-- Select Commodity --</option>');
    $('#mca_ref').val('');
    $('#fret_currency, #other_charges_currency, #fob_currency, #r_fob_currency, #insurance_amount_currency').val('');
    $('#liquidation_paid_by').val('');
    $('#air_fields_documentation, #air_logistics_fields, #road_rail_fields, #road_rail_logistics_fields').hide();
    $('#m3_field, #r_m3_field,#cession_date_field, #wagon_field, #road_manifest_field, #declaration_validity_col').hide();
    $('#t1_number_col, #t1_date_col, #border_arrival_col, #border_dispatch_col').hide();
    $('#horse_field, #trailer_1_field, #trailer_2_field').hide();
    $('#inspection_reports').html('<option value="">-- Select PARTIELLE --</option>');
    $('#m3, #cession_date').val('');
    currentTypeOfGoodsId = null;
    currentKindId = null;
    $('#partielle_required_indicator').hide();
    $('#inspection_reports').removeAttr('required');
  }

  function generateMCAReference() {
    const formAction = $('#formAction').val();
    if (formAction === 'update') return;

    const subscriberId = $('#subscriber_id').val();
    const licenseId = $('#license_id').val();
    
    if (!subscriberId || !licenseId) {
      $('#mca_ref').val('');
      return;
    }

    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getNextMCASequence',
      method: 'POST',
      data: { csrf_token: csrfToken, subscriber_id: subscriberId, license_id: licenseId },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success) {
          $('#mca_ref').val(res.mca_ref);
        }
      },
      error: function(xhr) {
        log.error('Failed to generate MCA reference:', xhr);
      }
    });
  }

  // ========================================
  // DATE VALIDATIONS
  // ========================================

  function validateDateAgainstPreAlert($field) {
    const preAlertDate = $('#pre_alert_date').val();
    const fieldValue = $field.val();
    const fieldId = $field.attr('id');
    const fieldLabel = $field.closest('.mb-3').find('label').first().text().replace('*', '').trim();

    if (!fieldValue || !preAlertDate) {
      $field.removeClass('is-invalid date-sequence-error');
      $(`#${fieldId}_error`).text('').hide();
      return true;
    }

    if (fieldValue < preAlertDate) {
      $field.addClass('is-invalid date-sequence-error');
      $(`#${fieldId}_error`).text(`${fieldLabel} cannot be before Pre-Alert Date (${formatDate(preAlertDate)})`).show();

      Swal.fire({
        icon: 'warning',
        title: 'Date Validation Error',
        html: `<strong>${escapeHtml(fieldLabel)}</strong> cannot be before <strong>Pre-Alert Date</strong>.<br><br>Pre-Alert Date: <strong>${escapeHtml(formatDate(preAlertDate))}</strong><br>Please select a date on or after the Pre-Alert Date.`,
        confirmButtonText: 'OK'
      });

      $field.val('');

      setTimeout(() => {
        $field.removeClass('date-sequence-error is-invalid');
        $(`#${fieldId}_error`).text('').hide();
      }, 3000);

      return false;
    }

    $field.removeClass('is-invalid date-sequence-error');
    $(`#${fieldId}_error`).text('').hide();
    return true;
  }

  function validateAirportDateSequence() {
    const arrivalDate = $('#airport_arrival_date').val();
    const dispatchDate = $('#dispatch_from_airport').val();

    if (!dispatchDate || !arrivalDate) {
      $('#dispatch_from_airport').removeClass('is-invalid date-sequence-error');
      $('#dispatch_from_airport_error').text('').hide();
      return true;
    }

    if (dispatchDate < arrivalDate) {
      $('#dispatch_from_airport').addClass('is-invalid date-sequence-error');
      $('#dispatch_from_airport_error').text(`Dispatch from Airport cannot be before Airport Arrival Date (${formatDate(arrivalDate)})`).show();

      Swal.fire({
        icon: 'warning',
        title: 'Airport Date Validation Error',
        html: `<strong>Dispatch from Airport</strong> cannot be before <strong>Airport Arrival Date</strong>.<br><br>Airport Arrival Date: <strong>${escapeHtml(formatDate(arrivalDate))}</strong><br>Please select a valid dispatch date.`,
        confirmButtonText: 'OK'
      });

      $('#dispatch_from_airport').val('');

      setTimeout(() => {
        $('#dispatch_from_airport').removeClass('date-sequence-error is-invalid');
        $('#dispatch_from_airport_error').text('').hide();
      }, 3000);

      return false;
    }

    $('#dispatch_from_airport').removeClass('is-invalid date-sequence-error');
    $('#dispatch_from_airport_error').text('').hide();
    return true;
  }

  const dateSequenceFields = [
    'arrival_date_zambia',
    'dispatch_from_zambia',
    'drc_entry_date',
    'border_warehouse_arrival_date',
    'dispatch_from_border'
  ];

  function validateDateSequence($field) {
    const fieldId = $field.attr('id');
    const sequence = parseInt($field.data('seq'));
    const currentValue = $field.val();

    if (!currentValue) {
      $field.removeClass('date-sequence-error is-invalid');
      $(`#${fieldId}_error`).text('').hide();
      return true;
    }

    const currentDate = new Date(currentValue);

    if (sequence > 1) {
      const prevFieldId = dateSequenceFields[sequence - 2];
      const prevValue = $(`#${prevFieldId}`).val();

      if (prevValue) {
        const prevDate = new Date(prevValue);

        if (currentDate < prevDate) {
          $field.val(prevValue);
          $field.addClass('date-sequence-error is-invalid');
          $(`#${fieldId}_error`).text(`Date cannot be before ${getFieldLabel(prevFieldId)} (${formatDate(prevValue)}). Auto-adjusted.`).show();

          Swal.fire({
            icon: 'warning',
            title: 'Date Sequence Error',
            html: `<strong>${escapeHtml(getFieldLabel(fieldId))}</strong> cannot be before <strong>${escapeHtml(getFieldLabel(prevFieldId))}</strong>.<br><br>Date auto-adjusted to: <strong>${escapeHtml(formatDate(prevValue))}</strong>`,
            confirmButtonText: 'OK',
            timer: 5000
          });

          setTimeout(() => {
            $field.removeClass('date-sequence-error is-invalid');
            $(`#${fieldId}_error`).text('').hide();
          }, 3000);

          return false;
        }
      }
    }

    $field.removeClass('date-sequence-error is-invalid');
    $(`#${fieldId}_error`).text('').hide();
    return true;
  }

  function getFieldLabel(fieldId) {
    const labels = {
      'arrival_date_zambia': 'Arrival Date Zambia',
      'dispatch_from_zambia': 'Dispatch from Zambia',
      'drc_entry_date': 'DRC Entry Date',
      'border_warehouse_arrival_date': 'Border Warehouse Arrival',
      'dispatch_from_border': 'Dispatch from Border'
    };
    return labels[fieldId] || fieldId;
  }

  // ========================================
  // STATUS UPDATES
  // ========================================

  function updateDocumentStatus() {
    const crfDate = $('#crf_received_date').val();
    const adDate = $('#ad_date').val();
    const insuranceDate = $('#insurance_date').val();
    
    let statusId = 1;
    
    if (crfDate && adDate && insuranceDate) {
      statusId = 7;
    } else if (crfDate && insuranceDate) {
      statusId = 6;
    } else if (adDate && insuranceDate) {
      statusId = 4;
    } else if (crfDate && adDate) {
      statusId = 3;
    } else if (crfDate) {
      statusId = 2;
    }
    
    $('#document_status').val(statusId);
  }

  function suggestClearingStatus() {
    const borderWarehouseArrival = $('#border_warehouse_arrival_date').val();
    const quittanceDate = $('#quittance_date').val();
    
    let suggestedStatus = null;
    
    if (quittanceDate && clearingStatusIds.completed_id) {
      suggestedStatus = clearingStatusIds.completed_id;
    } else if (borderWarehouseArrival && clearingStatusIds.in_progress_id) {
      suggestedStatus = clearingStatusIds.in_progress_id;
    } else if (clearingStatusIds.in_transit_id) {
      suggestedStatus = clearingStatusIds.in_transit_id;
    }
    
    if (suggestedStatus) {
      $('#clearing_status').val(suggestedStatus);
      $('#clearing_status').addClass('border-success');
      setTimeout(() => {
        $('#clearing_status').removeClass('border-success');
      }, 1000);
    }
  }

  // ========================================
  // REMARKS
  // ========================================

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

  function updateRemarksHidden() {
    const remarks = [];
    $('.remarks-entry').each(function() {
      const date = $(this).find('.remark-date').val();
      const text = $(this).find('.remark-text').val();
      if (date || text) remarks.push({ date, text });
    });
    $('#remarks_hidden').val(JSON.stringify(remarks));
  }

  // ========================================
  // PARTIELLE MANAGEMENT
  // ========================================

  function loadPartielleOptionsForLicense(licenseId) {
    $('#inspection_reports').html('<option value="">-- Loading... --</option>');
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getPartielleForLicense',
      method: 'GET',
      data: { license_id: licenseId },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        log.info('PARTIELLE Options Response:', res);
        
        $('#inspection_reports').html('<option value="">-- Select PARTIELLE --</option>');
        
        if (res.success && res.data && res.data.length > 0) {
          res.data.forEach(function(partielle) {
            const optionText = `${escapeHtml(partielle.partial_name)} (Available: ${parseFloat(partielle.remaining_weight || 0).toFixed(2)} KG)`;
            $('#inspection_reports').append(new Option(optionText, partielle.partial_name));
          });
        }
      },
      error: function(xhr) {
        log.error('PARTIELLE Load Error:', xhr);
        $('#inspection_reports').html('<option value="">-- Error loading --</option>');
        handleAjaxError(xhr, ERROR_MESSAGES.PARTIELLE_LOAD_ERROR);
      }
    });
  }

function loadPartielleForEdit(licenseId, selectedPartielleName) {
    log.info('Loading PARTIELLE for edit:', { licenseId, selectedPartielleName });
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getPartielleForLicense',
      method: 'GET',
      data: { license_id: licenseId },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        $('#inspection_reports').html('<option value="">-- Select PARTIELLE --</option>');
        
        if (res.success && res.data && res.data.length > 0) {
          res.data.forEach(function(partielle) {
            const optionText = `${escapeHtml(partielle.partial_name)} (Available: ${parseFloat(partielle.remaining_weight || 0).toFixed(2)} KG)`;
            
            const isSelected = partielle.partial_name === selectedPartielleName;
            const option = new Option(optionText, partielle.partial_name, false, isSelected);
            $('#inspection_reports').append(option);
          });
          
          log.info('PARTIELLE dropdown populated, selected:', selectedPartielleName);
        }
      },
      error: function(xhr) {
        log.error('Failed to load PARTIELLE for edit:', xhr);
        handleAjaxError(xhr, ERROR_MESSAGES.PARTIELLE_LOAD_ERROR);
      }
    });
  }

  $('#addPartielleBtn').on('click', function() {
    const licenseId = $('#license_id').val();
    const subscriberId = $('#subscriber_id').val();
    
    if (!licenseId || !subscriberId) {
      Swal.fire({
        icon: 'warning',
        title: 'License Required',
        text: 'Please select a client and license first.',
        confirmButtonText: 'OK'
      });
      return;
    }
    
    loadPartielleManagement(subscriberId, licenseId);
  });

  function loadPartielleManagement(subscriberId, licenseId) {
    $('#partielle_license_id_hidden').val(licenseId);
    $('#partielle_subscriber_id_hidden').val(subscriberId);
    
    const clientName = $('#subscriber_id option:selected').text();
    const licenseNumber = $('#license_id option:selected').text();
    const crfReference = $('#crf_reference').val();
    
    $('#partielle_client_display_main').text(clientName);
    $('#partielle_license_display_main').text(licenseNumber);
    $('#partielle_crf_display_main').text(crfReference || 'N/A');
    $('#partielle_crf_reference_hidden').val(crfReference);
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getPartielleManagementData',
      method: 'GET',
      data: { 
        license_id: licenseId,
        subscriber_id: subscriberId 
      },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success && res.data) {
          currentLicenseData = {
            subscriber_id: subscriberId,
            license_id: licenseId,
            crf_reference: crfReference,
            license_weight: parseFloat(res.data.license_weight || 0),
            license_fob: parseFloat(res.data.license_fob || 0),
            available_weight: parseFloat(res.data.available_weight || 0),
            available_fob: parseFloat(res.data.available_fob || 0)
          };
          
          currentPartielleData = res.data.partielles || [];
          
          $('#partielle_license_weight_display_main').text(currentLicenseData.license_weight.toFixed(2));
          $('#partielle_license_fob_display_main').text(currentLicenseData.license_fob.toFixed(2));
          $('#partielle_available_weight_display_main').text(currentLicenseData.available_weight.toFixed(2));
          $('#partielle_available_fob_display_main').text(currentLicenseData.available_fob.toFixed(2));
          
          renderPartielleTable();
          $('#partielleModal').modal('show');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res.message || ERROR_MESSAGES.PARTIELLE_LOAD_ERROR,
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        handleAjaxError(xhr, ERROR_MESSAGES.PARTIELLE_LOAD_ERROR);
      }
    });
  }

  function renderPartielleTable() {
    if (!currentPartielleData || currentPartielleData.length === 0) {
      $('#partielleTableBody').html('<tr><td colspan="13" class="text-center text-muted">No PARTIELLEs found. Click "Add New PARTIELLE" to create one.</td></tr>');
      return;
    }
    
    let html = '';
    currentPartielleData.forEach((partielle, index) => {
      const weightBalance = parseFloat(partielle.partial_weight || 0) - parseFloat(partielle.weight_used || 0);
      const fobBalance = parseFloat(partielle.partial_fob || 0) - parseFloat(partielle.fob_used || 0);
      
      const weightBalanceClass = weightBalance >= 0 ? 'text-balance-positive' : 'text-balance-negative';
      const fobBalanceClass = fobBalance >= 0 ? 'text-balance-positive' : 'text-balance-negative';
      
      html += `
        <tr>
          <td class="partielle-row-number">${index + 1}</td>
          <td><small class="text-muted">${escapeHtml(partielle.crf_reference || 'N/A')}</small></td>
          <td><strong>${escapeHtml(partielle.partial_name)}</strong></td>
          <td>${parseFloat(partielle.license_weight || 0).toFixed(2)}</td>
          <td>${parseFloat(partielle.license_fob || 0).toFixed(2)}</td>
          <td>
            ${partielle.no_of_files > 0 ? 
              `<span class="badge-count view-files-btn" data-partielle-name="${escapeHtml(partielle.partial_name)}">${partielle.no_of_files}</span>` : 
              '<span class="text-muted">0</span>'
            }
          </td>
          <td><strong>${parseFloat(partielle.partial_weight || 0).toFixed(2)}</strong></td>
          <td><strong>${parseFloat(partielle.partial_fob || 0).toFixed(2)}</strong></td>
          <td>${parseFloat(partielle.weight_used || 0).toFixed(2)}</td>
          <td>${parseFloat(partielle.fob_used || 0).toFixed(2)}</td>
          <td class="${weightBalanceClass}"><strong>${weightBalance.toFixed(2)}</strong></td>
          <td class="${fobBalanceClass}"><strong>${fobBalance.toFixed(2)}</strong></td>
          <td>
            <button type="button" class="btn btn-sm btn-warning edit-partielle-btn" data-partielle-id="${partielle.id}" data-partielle-name="${escapeHtml(partielle.partial_name)}" title="Edit">
              <i class="ti ti-edit"></i>
            </button>
          </td>
        </tr>
      `;
    });
    
    $('#partielleTableBody').html(html);
  }

  $('#addNewPartielleBtn').on('click', function() {
    openPartielleSubModal();
  });

  $(document).on('click', '.edit-partielle-btn', function() {
    const partielleId = $(this).data('partielle-id');
    const partielleName = $(this).data('partielle-name');
    
    log.info('Edit button clicked:', { partielleId, partielleName });
    
    const partielle = currentPartielleData.find(p => p.id == partielleId);
    
    if (partielle) {
      openPartielleSubModal(partielle);
    } else {
      log.error('PARTIELLE not found:', partielleId);
    }
  });

  function openPartielleSubModal(editData = null) {
    if (editData) {
      log.info('Opening edit modal with data:', editData);
      
      $('#subModalTitle').text('Edit PARTIELLE');
      $('#edit_partielle_id').val(editData.id);
      
      const parts = editData.partial_name.split('-');
      const numberPart = parts.length > 1 ? parts[parts.length - 1] : '';
      
      log.info('Extracted number from', editData.partial_name, ':', numberPart);
      
      $('#sub_partielle_number').val(numberPart);
      $('#sub_partial_weight').val(parseFloat(editData.partial_weight || 0).toFixed(2));
      $('#sub_partial_fob').val(parseFloat(editData.partial_fob || 0).toFixed(2));
      
      const availableWeight = currentLicenseData.available_weight + parseFloat(editData.partial_weight || 0);
      const availableFob = currentLicenseData.available_fob + parseFloat(editData.partial_fob || 0);
      
      $('#sub_available_weight_display').text(availableWeight.toFixed(2));
      $('#sub_available_fob_display').text(availableFob.toFixed(2));
    } else {
      $('#subModalTitle').text('Add New PARTIELLE');
      $('#edit_partielle_id').val('');
      $('#sub_partielle_number').val('');
      $('#sub_partial_weight').val('');
      $('#sub_partial_fob').val('');
      
      $('#sub_available_weight_display').text(currentLicenseData.available_weight.toFixed(2));
      $('#sub_available_fob_display').text(currentLicenseData.available_fob.toFixed(2));
    }
    
    const crfReference = $('#partielle_crf_reference_hidden').val();
    $('#sub_partielle_prefix').text(crfReference + '-');
    
    updatePartiellePreview();
    $('#addPartielleSubModal').modal('show');
  }

  $('#sub_partielle_number').on('input', function() {
    updatePartiellePreview();
  });

  function updatePartiellePreview() {
    const prefix = $('#sub_partielle_prefix').text();
    const number = $('#sub_partielle_number').val();
    
    if (number) {
      $('#sub_partielle_preview_text').text(prefix + number);
      $('#sub_partielle_preview').show();
    } else {
      $('#sub_partielle_preview').hide();
    }
  }

  $('#saveSubPartielleBtn').on('click', function() {
    const partielleId = $('#edit_partielle_id').val();
    const number = $('#sub_partielle_number').val().trim();
    const weight = parseFloat($('#sub_partial_weight').val()) || 0;
    const fob = parseFloat($('#sub_partial_fob').val()) || 0;
    
    if (!number || !/^[0-9]{3,4}$/.test(number)) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Number',
        text: 'Please enter a 3-4 digit number (e.g., 0001, 0002)',
        confirmButtonText: 'OK'
      });
      return;
    }
    
    const prefix = $('#sub_partielle_prefix').text();
    const fullPartielleNumber = prefix + number;
    
    log.info('Saving PARTIELLE:', { partielleId, fullPartielleNumber, weight, fob });
    
    const maxWeight = partielleId ? 
      currentLicenseData.available_weight + parseFloat(currentPartielleData.find(p => p.id == partielleId)?.partial_weight || 0) : 
      currentLicenseData.available_weight;
    
    const maxFob = partielleId ? 
      currentLicenseData.available_fob + parseFloat(currentPartielleData.find(p => p.id == partielleId)?.partial_fob || 0) : 
      currentLicenseData.available_fob;
    
    if (weight > maxWeight) {
      Swal.fire({
        icon: 'error',
        title: 'Weight Exceeds Available',
        text: `Partial weight (${weight.toFixed(2)} KG) exceeds available weight (${maxWeight.toFixed(2)} KG)`,
        confirmButtonText: 'OK'
      });
      return;
    }
    
    if (fob > maxFob) {
      Swal.fire({
        icon: 'error',
        title: 'FOB Exceeds Available',
        text: `Partial FOB (${fob.toFixed(2)}) exceeds available FOB (${maxFob.toFixed(2)})`,
        confirmButtonText: 'OK'
      });
      return;
    }
    
    const $saveBtn = $('#saveSubPartielleBtn');
    const originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Saving...');
    
    const url = partielleId ? 
      '<?= APP_URL ?>/import/crudData/updatePartielle' : 
      '<?= APP_URL ?>/import/crudData/createPartielle';
    
    const data = {
      csrf_token: csrfToken,
      license_id: currentLicenseData.license_id,
      subscriber_id: currentLicenseData.subscriber_id,
      partial_name: fullPartielleNumber,
      partial_weight: weight,
      partial_fob: fob
    };
    
    if (partielleId) {
      data.id = partielleId;
    }
    
    log.info('Sending data:', data);
    
    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        log.info('Save response:', res);
        $saveBtn.prop('disabled', false).html(originalText);
        
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: res.message || 'PARTIELLE saved successfully',
            timer: 1500,
            showConfirmButton: false
          });
          
          $('#addPartielleSubModal').modal('hide');
          loadPartielleManagement(currentLicenseData.subscriber_id, currentLicenseData.license_id);
          loadPartielleOptionsForLicense(currentLicenseData.license_id);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res.message || 'Failed to save PARTIELLE',
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        log.error('Save error:', xhr);
        $saveBtn.prop('disabled', false).html(originalText);
        handleAjaxError(xhr, ERROR_MESSAGES.SAVE_ERROR);
      }
    });
  });

  $(document).on('click', '.view-files-btn', function() {
    const partielleName = $(this).data('partielle-name');
    loadFilesForPartielle(partielleName);
  });

  function loadFilesForPartielle(partielleName) {
    log.info('Loading files for PARTIELLE:', partielleName);
    
    $('#files_partielle_name').text(partielleName);
    $('#filesDetailTableBody').html('<tr><td colspan="11" class="text-center"><div class="spinner-border text-primary" role="status"></div><br>Loading files...</td></tr>');
    
    $('#filesDetailModal').modal('show');
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getFilesForPartielle',
      method: 'GET',
      data: { partial_name: partielleName },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        log.info('Files response:', res);
        if (res.success && res.data) {
          renderFilesTable(res.data);
        } else {
          $('#filesDetailTableBody').html('<tr><td colspan="11" class="text-center text-muted">No files found for this PARTIELLE</td></tr>');
        }
      },
      error: function(xhr) {
        log.error('Failed to load files:', xhr);
        $('#filesDetailTableBody').html('<tr><td colspan="11" class="text-center text-danger">Failed to load files</td></tr>');
      }
    });
  }

  function renderFilesTable(data) {
    if (!data.files || data.files.length === 0) {
      $('#filesDetailTableBody').html('<tr><td colspan="11" class="text-center text-muted">No files found for this PARTIELLE</td></tr>');
      $('#files_total_count').text('0');
      $('#files_total_weight').text('0.00');
      $('#files_total_fob').text('0.00');
      return;
    }
    
    let html = '';
    let totalWeight = 0;
    let totalFob = 0;
    
    data.files.forEach((file, index) => {
      totalWeight += parseFloat(file.weight || 0);
      totalFob += parseFloat(file.fob || 0);
      
      html += `
        <tr>
          <td>${index + 1}</td>
          <td><span class="badge bg-primary">${escapeHtml(file.mca_ref)}</span></td>
          <td><small>${escapeHtml(file.inspection_reports || 'N/A')}</small></td>
          <td><small>${escapeHtml(file.declaration_reference || '-')}</small></td>
          <td><small>${file.dgda_in_date ? formatDate(file.dgda_in_date) : '-'}</small></td>
          <td><small>${escapeHtml(file.liquidation_reference || '-')}</small></td>
          <td><small>${file.liquidation_date ? formatDate(file.liquidation_date) : '-'}</small></td>
          <td><small>${escapeHtml(file.quittance_reference || '-')}</small></td>
          <td><small>${file.quittance_date ? formatDate(file.quittance_date) : '-'}</small></td>
          <td><strong>${parseFloat(file.weight || 0).toFixed(2)}</strong> KG</td>
          <td><strong>${parseFloat(file.fob || 0).toFixed(2)}</strong></td>
        </tr>
      `;
    });
    
    $('#filesDetailTableBody').html(html);
    $('#files_total_count').text(data.files.length);
    $('#files_total_weight').text(totalWeight.toFixed(2));
    $('#files_total_fob').text(totalFob.toFixed(2));
  }

  // ========================================
  // FORM VALIDATION & SUBMISSION (WITH RATE LIMITING)
  // ========================================

  function validateForm() {
    clearValidationErrors();
    let errors = [];
    
    const requiredFields = [
      { id: 'subscriber_id', label: 'Client' },
      { id: 'license_id', label: 'License Number' },
      { id: 'regime', label: 'Regime' },
      { id: 'types_of_clearance', label: 'Types of Clearance' },
      { id: 'pre_alert_date', label: 'Pre-Alert Date' },
      { id: 'invoice', label: 'Invoice' },
      { id: 'commodity', label: 'Commodity' },
      { id: 'weight', label: 'Weight' },
      { id: 'fob', label: 'FOB' },
      { id: 'clearing_status', label: 'Clearing Status' }
    ];
    
    const kindId = parseInt($('#kind_hidden').val());
    if (kindId === 1 || kindId === 2) {
      const inspectionReports = $('#inspection_reports').val();
      if (!inspectionReports || inspectionReports === '') {
        showFieldError('inspection_reports', 'Inspection Reports (PARTIELLE) is required for this Kind');
        errors.push('Inspection Reports (PARTIELLE) is required for IMPORT DEFINITIVE and IMPORT TEMPORARY');
      }
    }
    
    const transportMode = $('#transport_mode_display').val().toUpperCase();
    if (transportMode.includes('AIR')) {
      requiredFields.push({ id: 'entry_point_id_air', label: 'Entry Point' });
    } else {
      requiredFields.push({ id: 'entry_point_id', label: 'Entry Point' });
    }

    requiredFields.forEach(field => {
      const value = $(`#${field.id}`).val();
      if (!value || value === '') {
        showFieldError(field.id, `${field.label} is required`);
        errors.push(`${field.label} is required`);
      }
    });

    const weight = parseFloat($('#weight').val());
    const fob = parseFloat($('#fob').val());
    
    if (isNaN(weight) || weight < 0) {
      showFieldError('weight', 'Weight must be a positive number or zero');
      errors.push('Invalid weight');
    }
    
    if (isNaN(fob) || fob < 0) {
      showFieldError('fob', 'FOB must be a positive number');
      errors.push('Invalid FOB');
    }

    const preAlertDate = $('#pre_alert_date').val();
    if (preAlertDate) {
      $('.date-after-prealert').each(function() {
        const fieldVal = $(this).val();
        if (fieldVal && fieldVal < preAlertDate) {
          const fieldId = $(this).attr('id');
          const label = $(this).closest('.mb-3').find('label').first().text().replace('*', '').trim();
          showFieldError(fieldId, `${label} cannot be before Pre-Alert Date`);
          errors.push(`${label} cannot be before Pre-Alert Date`);
        }
      });
    }

    $('.date-sequence-field').each(function() {
      validateDateSequence($(this));
    });

    validateAirportDateSequence();

    return { isValid: errors.length === 0, errors };
  }

  $('#importForm').on('submit', function (e) {
    e.preventDefault();
    
    // Rate limiting check
    const now = Date.now();
    if (now - lastSubmitTime < SUBMIT_COOLDOWN) {
      Swal.fire({
        icon: 'warning',
        title: 'Please Wait',
        text: 'Please wait a moment before submitting again.',
        timer: 1500,
        showConfirmButton: false
      });
      return false;
    }
    
    lastSubmitTime = now;
    
    updateDocumentStatus();
    
    // ========================================
    // ✅ UPDATED: SMART FIELD SYNCING BASED ON TRANSPORT MODE
    // ========================================
    const transportMode = $('#transport_mode_display').val().toUpperCase();
    
    if (transportMode.includes('AIR')) {
      // For AIR mode: sync entry point
      $('#entry_point_id').val($('#entry_point_id_air').val());
      
      // ✅ ONLY copy Air dispatch date to main field if main field is empty
      const airDispatchDate = $('#dispatch_deliver_date_air').val();
      const mainDispatchDate = $('#dispatch_deliver_date').val();
      
      if (airDispatchDate && !mainDispatchDate) {
        $('#dispatch_deliver_date').val(airDispatchDate);
        log.info('Synced Air dispatch date to main field (was empty)');
      }
    } else {
      // For ROAD/RAIL mode: ensure entry point is set correctly
      if ($('#entry_point_id_air').val() && !$('#entry_point_id').val()) {
        $('#entry_point_id').val($('#entry_point_id_air').val());
      }
      
      // ✅ ONLY copy Road/Rail dispatch date to Air field if Air field is empty
      const mainDispatchDate = $('#dispatch_deliver_date').val();
      const airDispatchDate = $('#dispatch_deliver_date_air').val();
      
      if (mainDispatchDate && !airDispatchDate) {
        $('#dispatch_deliver_date_air').val(mainDispatchDate);
        log.info('Synced Road/Rail dispatch date to Air field (was empty)');
      }
    }
    
    const validation = validateForm();
    
    if (!validation.isValid) {
      $('#importTracking').collapse('show');
      
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: '<ul style="text-align:left;"><li>' + validation.errors.map(err => escapeHtml(err)).join('</li><li>') + '</li></ul>',
        confirmButtonText: 'OK'
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
    
    if (currentTypeOfGoodsId !== null) {
      formData.set('type_of_goods', currentTypeOfGoodsId);
    }
    
    $('#document_status').prop('disabled', false);

    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/' + $('#formAction').val(),
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      timeout: 30000,
      success: function (res) {
        submitBtn.prop('disabled', false).html(originalText);
        $('#document_status').prop('disabled', true);
        
        if (res.success) {
          clearFormDraft();
          
          Swal.fire({ 
            icon: 'success', 
            title: 'Success!', 
            text: res.message, 
            timer: 1500, 
            showConfirmButton: false 
          });
          
          resetForm();
          $('#importTracking').collapse('hide');
          
          if (typeof importsTable !== 'undefined') {
            importsTable.ajax.reload(null, false);
          }
          updateStatistics();
        } else {
          Swal.fire({ 
            icon: 'error', 
            title: 'Error!', 
            text: res.message || ERROR_MESSAGES.SAVE_ERROR,
            confirmButtonText: 'OK'
          });
        }
      },
      error: function (xhr) {
        submitBtn.prop('disabled', false).html(originalText);
        $('#document_status').prop('disabled', true);
        
        handleAjaxError(xhr, ERROR_MESSAGES.SAVE_ERROR);
      }
    });
  });

  function resetForm() {
    $('#importForm')[0].reset();
    clearValidationErrors();
    $('#import_id, #mca_ref, #crf_reference, #license_invoice_number').val('');
    $('#formAction').val('insert');
    $('#formTitle').text('Add New Import');
    $('#submitBtnText').text('Save Import');
    $('#resetFormBtn').hide();
    $('#remarksContainer').empty();
    clearLicenseFields();
    
    $('#types_of_clearance').val('1');
    $('#document_status').val('1');
    
    if (clearingStatusIds.in_transit_id) {
      $('#clearing_status').val(clearingStatusIds.in_transit_id);
    }
    
    $('#liquidation_paid_by').val('');
    $('#importTracking').collapse('hide');
    loadCommoditiesForLicense();
  }

  $('#cancelBtn, #resetFormBtn').on('click', (e) => { 
    e.preventDefault(); 
    resetForm(); 
  });

  // ========================================
  // BULK UPDATE FUNCTIONALITY
  // ========================================

  $('#bulkUpdateBtn').on('click', function() {
    loadBulkUpdateData();
    $('#bulkUpdateModal').modal('show');
  });

  function loadBulkUpdateData() {
    $('#bulkUpdateContent').html('<p class="text-center"><div class="spinner-border text-primary" role="status"></div><br>Loading imports for bulk update...</p>');
    
    const filterSummary = [];
    if (activeFilters.length > 0) {
      const formattedFilters = activeFilters.map(f => 
        f.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
      ).join(', ');
      filterSummary.push('Status Filters: ' + formattedFilters);
    }
    if (selectedClientIds.length > 0) {
      const clientNames = [];
      selectedClientIds.forEach(id => {
        const name = $(`.client-filter-checkbox[value="${id}"]`).data('client-name');
        if (name) clientNames.push(name);
      });
      filterSummary.push('Clients: ' + clientNames.join(', '));
    }
    if (selectedTransportModeId) {
      filterSummary.push('Transport Mode: ' + $('#filterTransportModeSelect option:selected').text());
    }
    if (selectedTypeOfGoodsId) {
      filterSummary.push('Types Of Goods: ' + $('#filterTypeOfGoodsSelect option:selected').text());
    }
    if (selectedEntryPointId) {
      filterSummary.push('Entry Point: ' + $('#filterEntryPointSelect option:selected').text());
    }
    if (selectedStartDate) {
      filterSummary.push('Start Date: ' + formatDate(selectedStartDate));
    }
    if (selectedEndDate) {
      filterSummary.push('End Date: ' + formatDate(selectedEndDate));
    }
    
    $('#bulkFilterSummary').html(filterSummary.length > 0 ? filterSummary.join(' | ') : 'All imports');

    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/getBulkUpdateData',
      method: 'GET',
      data: {
        filters: activeFilters,
        client_ids: selectedClientIds,
        transport_mode_id: selectedTransportModeId,
        type_of_goods_id: selectedTypeOfGoodsId,
        entry_point_id: selectedEntryPointId,
        start_date: selectedStartDate,
        end_date: selectedEndDate
      },
      dataType: 'json',
      timeout: 30000,
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          bulkUpdateData = res;
          renderBulkUpdateTable(res.data);
        } else {
          $('#bulkUpdateContent').html('<div class="alert alert-warning"><i class="ti ti-info-circle me-2"></i>No imports found matching your filters.</div>');
        }
      },
      error: function(xhr) {
        $('#bulkUpdateContent').html('<div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i>Failed to load imports. Please try again.</div>');
        handleAjaxError(xhr, 'Failed to load bulk update data');
      }
    });
  }

  function renderBulkUpdateTable(data) {
    const relevantFields = bulkUpdateData.relevant_fields || [];
    
    const fieldConfig = {
      'pre_alert_date': { label: 'Pre-Alert Date', type: 'date' },
      'crf_reference': { label: 'CRF Reference', type: 'text', maxlength: 100 },
      'crf_received_date': { label: 'CRF Received Date', type: 'date' },
      'ad_date': { label: 'AD Date', type: 'date' },
      'insurance_date': { label: 'Insurance Date', type: 'date' },
      'insurance_amount': { label: 'Insurance Amount', type: 'number', step: '0.01' },
      'insurance_reference': { label: 'Insurance Reference', type: 'text', maxlength: 100 },
      'audited_date': { label: 'Audited Date', type: 'date' },
      'archived_date': { label: 'Archived Date', type: 'date' },
      'archive_reference': { label: 'Archive Reference', type: 'text', maxlength: 100 },
      'dgda_in_date': { label: 'DGDA In Date', type: 'date' },
      'declaration_reference': { label: 'Declaration Reference', type: 'text', maxlength: 100 },
      'liquidation_date': { label: 'Liquidation Date', type: 'date' },
      'liquidation_reference': { label: 'Liquidation Reference', type: 'text', maxlength: 100 },
      'quittance_date': { label: 'Quittance Date', type: 'date' },
      'quittance_reference': { label: 'Quittance Reference', type: 'text', maxlength: 100 },
      'dgda_out_date': { label: 'DGDA Out Date', type: 'date' },
      'warehouse_arrival_date': { label: 'Warehouse Arrival Date', type: 'date' },
      'warehouse_departure_date': { label: 'Warehouse Departure Date', type: 'date' },
      'dispatch_deliver_date': { label: 'Dispatch/Deliver Date', type: 'date' }
    };
    
    let filterHtml = `
      <div class="row mb-3 bg-light p-3 rounded border">
        <div class="col-md-4">
          <label class="form-label fw-bold"><i class="ti ti-user me-1"></i>Filter by Client</label>
          <select class="form-select" id="bulkClientFilter">
            <option value="">All Clients</option>
            <?php foreach ($subscribers as $sub): ?>
              <option value="<?= htmlspecialchars($sub['short_name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($sub['short_name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold"><i class="ti ti-search me-1"></i>Search Horse/Trailers</label>
          <input type="text" class="form-control" id="bulkHorseTrailerSearch" placeholder="Search horse, trailer 1 or 2...">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold"><i class="ti ti-search me-1"></i>Search Containers</label>
          <input type="text" class="form-control" id="bulkContainerSearch" placeholder="Search container...">
        </div>
      </div>
    `;
    
    let headerHtml = '<th style="width: 50px;">#</th>';
    headerHtml += '<th>MCA Ref</th>';
    headerHtml += '<th>Client</th>';
    
   // Add Horse, Trailer, and Container columns for dispatch_deliver_pending filter
if (activeFilters.includes('dispatch_deliver_pending') || activeFilters.includes('dgda_out_pending')) {
  headerHtml += '<th>Horse</th>';
  headerHtml += '<th>Trailer 1</th>';
  headerHtml += '<th>Trailer 2</th>';
  headerHtml += '<th>Container</th>';
}
    
    relevantFields.forEach(field => {
      if (fieldConfig[field]) {
        headerHtml += `<th>${fieldConfig[field].label}</th>`;
      }
    });
    
    let html = filterHtml + `
      <div class="bulk-table-container">
        <table class="bulk-update-table" id="bulkDataTable">
          <thead>
            <tr>
              ${headerHtml}
            </tr>
          </thead>
          <tbody>
    `;

    data.forEach((item, index) => {
      const horse = item.horse || '';
      const trailer1 = item.trailer_1 || '';
      const trailer2 = item.trailer_2 || '';
      const horseTrailerCombined = (horse + ' ' + trailer1 + ' ' + trailer2).trim();
      
      html += `
        <tr data-import-id="${item.id}" 
            data-client="${escapeHtml(item.subscriber_name || '')}"
            data-horse-trailer="${escapeHtml(horseTrailerCombined)}"
            data-container="${escapeHtml(item.container || '')}">
          <td><span class="text-muted fw-bold">${index + 1}</span></td>
          <td>
            <span class="mca-ref-badge">${escapeHtml(item.mca_ref)}</span>
            <span class="pre-alert-date-text">${formatDate(item.pre_alert_date)}</span>
          </td>
          <td><small>${escapeHtml(item.subscriber_name || '-')}</small></td>
      `;
      
   // Add Horse, Trailer, and Container data columns for dispatch_deliver_pending filter
if (activeFilters.includes('dispatch_deliver_pending') || activeFilters.includes('dgda_out_pending')) {
  const container = item.container || '';
  html += `
    <td><small>${escapeHtml(horse || '-')}</small></td>
    <td><small>${escapeHtml(trailer1 || '-')}</small></td>
    <td><small>${escapeHtml(trailer2 || '-')}</small></td>
    <td><small>${escapeHtml(container || '-')}</small></td>
  `;
}
      
      relevantFields.forEach(field => {
        if (fieldConfig[field]) {
          const config = fieldConfig[field];
          const value = item[field] || '';
          
          if (config.type === 'date') {
            html += `
              <td>
                <input type="date" 
                       class="form-control bulk-field" 
                       name="${field}" 
                       value="${escapeHtml(value)}" 
                       data-index="${index}">
              </td>
            `;
          } else if (config.type === 'number') {
            html += `
              <td>
                <input type="number" 
                       class="form-control bulk-field" 
                       name="${field}" 
                       value="${escapeHtml(value)}" 
                       step="${config.step || '1'}"
                       data-index="${index}">
              </td>
            `;
          } else {
            html += `
              <td>
                <input type="text" 
                       class="form-control bulk-field" 
                       name="${field}" 
                       value="${escapeHtml(value)}" 
                       maxlength="${config.maxlength || 255}"
                       data-index="${index}">
              </td>
            `;
          }
        }
      });
      
      html += '</tr>';
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    $('#bulkUpdateContent').html(html);

    $('#bulkClientFilter').on('change', function() {
      filterBulkTable();
    });

    $('#bulkHorseTrailerSearch').on('input', function() {
      filterBulkTable();
    });

    $('#bulkContainerSearch').on('input', function() {
      filterBulkTable();
    });
  }

 
function filterBulkTable() {
    const clientFilter = $('#bulkClientFilter').val().toLowerCase();
    const horseTrailerSearch = $('#bulkHorseTrailerSearch').val().toLowerCase();
    const containerSearch = $('#bulkContainerSearch').val().toLowerCase();
    
    $('#bulkDataTable tbody tr').each(function() {
      const $row = $(this);
      const client = $row.data('client').toString().toLowerCase();
      const horseTrailer = $row.data('horse-trailer').toString().toLowerCase();
      const container = $row.data('container').toString().toLowerCase();
      
      let show = true;
      
      if (clientFilter && !client.includes(clientFilter)) {
        show = false;
      }
      
      if (horseTrailerSearch && !horseTrailer.includes(horseTrailerSearch)) {
        show = false;
      }
      
      if (containerSearch && !container.includes(containerSearch)) {
        show = false;
      }
      
      $row.toggle(show);
    });
    
    const visibleCount = $('#bulkDataTable tbody tr:visible').length;
    const totalCount = $('#bulkDataTable tbody tr').length;
    
    if (visibleCount < totalCount) {
      if ($('#bulkVisibleCount').length === 0) {
        $('#bulkUpdateContent').prepend(`
          <div class="alert alert-info mb-3" id="bulkVisibleCount">
            <i class="ti ti-info-circle me-2"></i>
            Showing <strong>${visibleCount}</strong> of <strong>${totalCount}</strong> imports
          </div>
        `);
      } else {
        $('#bulkVisibleCount').html(`
          <i class="ti ti-info-circle me-2"></i>
          Showing <strong>${visibleCount}</strong> of <strong>${totalCount}</strong> imports
        `);
      }
    } else {
      $('#bulkVisibleCount').remove();
    }
  }

  $('#saveBulkUpdateBtn').on('click', function() {
    const visibleRows = $('#bulkDataTable tbody tr:visible');
    
    if (visibleRows.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'No Data',
        text: 'No imports to update. Please adjust your filters.',
        confirmButtonText: 'OK'
      });
      return;
    }

    const updates = [];
    const relevantFields = bulkUpdateData.relevant_fields || [];
    
    visibleRows.each(function() {
      const $row = $(this);
      const importId = $row.data('import-id');
      const index = $row.find('.bulk-field').first().data('index');
      
      const updateData = {
        import_id: importId
      };
      
      if (relevantFields.length > 0) {
        relevantFields.forEach(field => {
          const $input = $row.find(`[name="${field}"][data-index="${index}"]`);
          if ($input.length) {
            const value = $input.val();
            if (value && value.trim() !== '') {
              updateData[field] = value;
            }
          }
        });
      }
      
      if (Object.keys(updateData).length > 1) {
        updates.push(updateData);
      }
    });

    if (updates.length === 0) {
      Swal.fire({
        icon: 'info',
        title: 'No Changes',
        text: 'No data to update. Please fill in some fields before saving.',
        confirmButtonText: 'OK'
      });
      return;
    }

    Swal.fire({
      title: 'Confirm Bulk Update',
      html: `You are about to update <strong>${updates.length}</strong> import(s).<br><br>This action cannot be undone.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Update All',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#f39c12'
    }).then((result) => {
      if (result.isConfirmed) {
        performBulkUpdate(updates);
      }
    });
  });

  function performBulkUpdate(updates) {
    const $saveBtn = $('#saveBulkUpdateBtn');
    const originalText = $saveBtn.html();
    $saveBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Updating...');

    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/bulkUpdate',
      method: 'POST',
      data: {
        update_data: JSON.stringify(updates),
        csrf_token: csrfToken
      },
      dataType: 'json',
      timeout: 60000,
      success: function(res) {
        $saveBtn.prop('disabled', false).html(originalText);
        
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: res.message || `Successfully updated ${updates.length} import(s)`,
            timer: 2000,
            showConfirmButton: false
          });
          
          $('#bulkUpdateModal').modal('hide');
          
          if (typeof importsTable !== 'undefined') {
            importsTable.ajax.reload();
          }
          
          updateStatistics();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: res.message || 'Failed to update imports',
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        $saveBtn.prop('disabled', false).html(originalText);
        handleAjaxError(xhr, 'Failed to perform bulk update');
      }
    });
  }

  // ========================================
  // EXPORT FUNCTIONALITY
  // ========================================

  function exportToExcel(importId) {
    window.location.href = '<?= APP_URL ?>/import/crudData/exportImport?id=' + importId;
    
    Swal.fire({
      icon: 'success',
      title: 'Exporting...',
      text: 'Your export will download shortly',
      timer: 2000,
      showConfirmButton: false
    });
  }

  // EXPORT ALL BUTTON - Now supports multiple selected clients
  $('#exportAllBtn').on('click', function() {
    let url = '<?= APP_URL ?>/import/crudData/exportAll';
    const params = new URLSearchParams();
    
    // If multiple clients are selected, export them sequentially
    if (selectedClientIds.length > 1) {
      exportMultipleClients();
      return;
    }
    
    // Single client or no client selected - normal export
    if (activeFilters.length > 0) {
      activeFilters.forEach(filter => {
        params.append('filters[]', filter);
      });
    }
    
    if (selectedClientId) {
      params.append('client_id', selectedClientId);
    }
    
    if (selectedTransportModeId) {
      params.append('transport_mode_id', selectedTransportModeId);
    }
    if (selectedTypeOfGoodsId) {
      params.append('type_of_goods_id', selectedTypeOfGoodsId);
    }
    if (selectedEntryPointId) {
      params.append('entry_point_id', selectedEntryPointId);
    }
    
    if (selectedStartDate) {
      params.append('start_date', selectedStartDate);
    }
    
    if (selectedEndDate) {
      params.append('end_date', selectedEndDate);
    }
    
    if (params.toString()) {
      url += '?' + params.toString();
    }
    
    window.location.href = url;
    
    Swal.fire({
      icon: 'success',
      title: 'Exporting...',
      text: 'Generating Excel file',
      timer: 2000,
      showConfirmButton: false
    });
  });

  // ========================================
  // BORDER TEAM EXPORT BUTTON
  // ========================================

  $('#exportBorderTeamBtn').on('click', function() {
    let url = '<?= APP_URL ?>/import/crudData/exportBorderTeam';
    const params = new URLSearchParams();
    
    // If multiple clients are selected, export them sequentially
    if (selectedClientIds.length > 1) {
      exportMultipleBorderTeamClients();
      return;
    }
    
    // Single client or no client selected - normal export
    if (activeFilters.length > 0) {
      activeFilters.forEach(filter => {
        params.append('filters[]', filter);
      });
    }
    
    if (selectedClientId) {
      params.append('client_id', selectedClientId);
    }
    
    if (selectedTransportModeId) {
      params.append('transport_mode_id', selectedTransportModeId);
    }

    if (selectedTypeOfGoodsId) {
      params.append('type_of_goods_id', selectedTypeOfGoodsId);
    }
    if (selectedEntryPointId) {
      params.append('entry_point_id', selectedEntryPointId);
    }
    
    if (selectedStartDate) {
      params.append('start_date', selectedStartDate);
    }
    
    if (selectedEndDate) {
      params.append('end_date', selectedEndDate);
    }
    
    if (params.toString()) {
      url += '?' + params.toString();
    }
    
    window.location.href = url;
    
    Swal.fire({
      icon: 'success',
      title: 'Exporting Border Team Data...',
      text: 'Generating Excel file for Border Team operations',
      timer: 2000,
      showConfirmButton: false
    });
  });

  // Export multiple clients for Border Team sequentially
  function exportMultipleBorderTeamClients() {
    const clients = [];
    
    $('.client-filter-checkbox:checked').each(function() {
      clients.push({
        id: parseInt($(this).val()),
        name: $(this).data('client-name')
      });
    });
    
    if (clients.length === 0) return;
    
    // Create simple progress tracker
    const progressHtml = `
      <div class="export-progress-toast" id="exportBorderTeamProgressToast">
        <button class="export-progress-close" onclick="$('#exportBorderTeamProgressToast').remove();">&times;</button>
        <div class="export-progress-header">
          <i class="ti ti-building me-2"></i>Exporting ${clients.length} Border Team File${clients.length > 1 ? 's' : ''}
        </div>
        <div id="exportBorderTeamProgressList"></div>
      </div>
    `;
    
    $('#exportBorderTeamProgressToast').remove();
    $('body').append(progressHtml);
    
    // Initialize progress list with download links
    const progressListHtml = clients.map((client, index) => `
      <div class="export-progress-item success" id="progress_border_${index}">
        <i class="ti ti-circle-check me-2"></i>
        <strong>${escapeHtml(client.name)}</strong>
        <a href="#" class="float-end download-link" id="download_border_${index}">Download</a>
      </div>
    `).join('');
    
    $('#exportBorderTeamProgressList').html(progressListHtml);
    $('#exportBorderTeamProgressToast').hide().fadeIn(300);
    
    // Generate download URLs
    clients.forEach((client, index) => {
      const params = new URLSearchParams();
      params.append('client_id', client.id);
      
      if (activeFilters.length > 0) {
        activeFilters.forEach(filter => params.append('filters[]', filter));
      }
      if (selectedTransportModeId) params.append('transport_mode_id', selectedTransportModeId);
      if (selectedTypeOfGoodsId) params.append('type_of_goods_id', selectedTypeOfGoodsId);
      if (selectedEntryPointId) params.append('entry_point_id', selectedEntryPointId);
      if (selectedStartDate) params.append('start_date', selectedStartDate);
      if (selectedEndDate) params.append('end_date', selectedEndDate);
      
      const url = '<?= APP_URL ?>/import/crudData/exportBorderTeam?' + params.toString();
      
      $(`#download_border_${index}`).attr('href', url).attr('download', '');
    });
    
    // Auto-trigger downloads
    let currentIndex = 0;
    
    function triggerNextDownload() {
      if (currentIndex >= clients.length) return;
      
      setTimeout(() => {
        const url = $(`#download_border_${currentIndex}`).attr('href');
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        log.info('Border Team download triggered:', clients[currentIndex].name);
        
        currentIndex++;
        triggerNextDownload();
      }, 1000);
    }
    
    triggerNextDownload();
    
    // Auto-close after 10 seconds
    setTimeout(() => {
      $('#exportBorderTeamProgressToast').fadeOut(300, function() {
        $(this).remove();
      });
    }, 10000);
  }

  // Export multiple clients sequentially
  function exportMultipleClients() {
    const clients = [];
    
    $('.client-filter-checkbox:checked').each(function() {
      clients.push({
        id: parseInt($(this).val()),
        name: $(this).data('client-name')
      });
    });
    
    if (clients.length === 0) return;
    
    // Create simple progress tracker
    const progressHtml = `
      <div class="export-progress-toast" id="exportProgressToast">
        <button class="export-progress-close" onclick="$('#exportProgressToast').remove();">&times;</button>
        <div class="export-progress-header">
          <i class="ti ti-download me-2"></i>Exporting ${clients.length} File${clients.length > 1 ? 's' : ''}
        </div>
        <div id="exportProgressList"></div>
      </div>
    `;
    
    $('#exportProgressToast').remove();
    $('body').append(progressHtml);
    
    // Initialize progress list with download links
    const progressListHtml = clients.map((client, index) => `
      <div class="export-progress-item success" id="progress_${index}">
        <i class="ti ti-circle-check me-2"></i>
        <strong>${escapeHtml(client.name)}</strong>
        <a href="#" class="float-end download-link" id="download_${index}">Download</a>
      </div>
    `).join('');
    
    $('#exportProgressList').html(progressListHtml);
    $('#exportProgressToast').hide().fadeIn(300);
    
    // Generate download URLs
    clients.forEach((client, index) => {
      const params = new URLSearchParams();
      params.append('client_id', client.id);
      
      if (activeFilters.length > 0) {
        activeFilters.forEach(filter => params.append('filters[]', filter));
      }
      if (selectedTransportModeId) params.append('transport_mode_id', selectedTransportModeId);
      if (selectedTypeOfGoodsId) params.append('type_of_goods_id', selectedTypeOfGoodsId);
      if (selectedEntryPointId) params.append('entry_point_id', selectedEntryPointId);
      if (selectedStartDate) params.append('start_date', selectedStartDate);
      if (selectedEndDate) params.append('end_date', selectedEndDate);
      
      const url = '<?= APP_URL ?>/import/crudData/exportAll?' + params.toString();
      
      $(`#download_${index}`).attr('href', url).attr('download', '');
    });
    
    // Auto-trigger downloads
    let currentIndex = 0;
    
    function triggerNextDownload() {
      if (currentIndex >= clients.length) return;
      
      setTimeout(() => {
        const url = $(`#download_${currentIndex}`).attr('href');
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        log.info('Download triggered:', clients[currentIndex].name);
        
        currentIndex++;
        triggerNextDownload();
      }, 1000);
    }
    
    triggerNextDownload();
    
    // Auto-close after 10 seconds
    setTimeout(() => {
      $('#exportProgressToast').fadeOut(300, function() {
        $(this).remove();
      });
    }, 10000);
  }

  // ========================================
  // DATATABLE INITIALIZATION
  // ========================================

  var importsTable;
  function initDataTable() {
    if ($.fn.DataTable.isDataTable('#importsTable')) {
      $('#importsTable').DataTable().destroy();
    }

    // Determine sort order based on active filters
    let defaultOrder = [[4, 'asc']]; // Default: ascending (oldest first)
    
    // If no filters are active, show newest first
    if (activeFilters.length === 0 && selectedClientIds.length === 0 && !selectedTransportModeId && !selectedTypeOfGoodsId && !selectedEntryPointId && !selectedStartDate && !selectedEndDate) {
      defaultOrder = [[4, 'desc']]; // Show newest first when no filters
    }

    importsTable = $('#importsTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: { 
        url: '<?= APP_URL ?>/import/crudData/listing', 
        type: 'GET',
        timeout: 30000,
        data: function(d) {
          d.filters = activeFilters;
          
          if (selectedClientIds && selectedClientIds.length > 0) {
            d.client_ids = selectedClientIds;
          } else if (selectedClientId) {
            d.client_id = selectedClientId;
          }
          
          if (selectedTransportModeId) {
            d.transport_mode_id = selectedTransportModeId;
          }

          if (selectedTypeOfGoodsId) {
            d.type_of_goods_id = selectedTypeOfGoodsId;
          }

          if (selectedEntryPointId) {
            d.entry_point_id = selectedEntryPointId;
          }
          
          if (selectedStartDate) {
            d.start_date = selectedStartDate;
          }
          
          if (selectedEndDate) {
            d.end_date = selectedEndDate;
          }
          
          return d;
        },
        dataSrc: function(json) {
          return json.data || [];
        },
        error: function(xhr) {
          log.error('DataTable load error:', xhr);
        }
      },
      columns: [
        { 
          data: 'mca_ref',
          name: 'mca_ref',
          render: function(data, type, row) {
            if (type !== 'display') {
              return data || '';
            }
            
            let bgStyle = '';
            
            // Rule 1: Liquidation Paid By Client → Always GREEN
            if (row.liquidation_paid_by === 'Client') {
              bgStyle = 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);';
            }
            // Rule 2: Liquidation Paid By Malabar
            else if (row.liquidation_paid_by === 'Malabar') {
              const typeId = parseInt(row.type_of_goods || 0);
              
              // Rule 2B: Fuel (Type of Goods = LIQUID/3)
              if (typeId === 3) {
                const cessionDate = row.cession_date;
                
                if (!cessionDate || cessionDate === '' || cessionDate === null) {
                  bgStyle = 'background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);';
                } else {
                  const cessionTime = new Date(cessionDate).getTime();
                  const currentTime = new Date().getTime();
                  const hoursPassed = (currentTime - cessionTime) / (1000 * 60 * 60);
                  
                  if (hoursPassed > 48) {
                    bgStyle = 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);';
                  } else {
                    bgStyle = 'background: linear-gradient(135deg, #9B59B6 0%, #8E44AD 100%);';
                  }
                }
              }
              // Rule 2A: Consumable & Divers (NOT Fuel)
              else {
                const drcEntryDate = row.drc_entry_date;
                
                if (!drcEntryDate || drcEntryDate === '' || drcEntryDate === null) {
                  bgStyle = 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);';
                } else {
                  const entryTime = new Date(drcEntryDate).getTime();
                  const currentTime = new Date().getTime();
                  const hoursPassed = (currentTime - entryTime) / (1000 * 60 * 60);
                  
                  if (hoursPassed > 48) {
                    bgStyle = 'background: linear-gradient(135deg, #28a745 0%, #20c997 100%);';
                  } else {
                    bgStyle = 'background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);';
                  }
                }
              }
            }
            else {
              bgStyle = 'background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);';
            }
            
            return '<span style="' + bgStyle + ' color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; display: inline-block;">' + escapeHtml(data || '') + '</span>'; 
          }
        },
        { 
          data: 'subscriber_name',
          name: 'subscriber_name',
          render: function(data) { 
            return escapeHtml(data || '-'); 
          } 
        },
        { 
          data: 'license_number',
          name: 'license_number',
          render: function(data) { 
            return escapeHtml(data || '-'); 
          } 
        },
        { 
          data: 'invoice',
          name: 'invoice',
          render: function(data) { 
            return escapeHtml(data || '-'); 
          } 
        },
        { 
          data: 'pre_alert_date',
          name: 'pre_alert_date',
          render: function(data) {
            return data ? '<small>' + escapeHtml(formatDate(data)) + '</small>' : '-';
          }
        },
        { 
          data: 'weight',
          name: 'weight',
          render: function(data) {
            return '<strong>' + (data ? parseFloat(data).toFixed(2) : '0.00') + '</strong> <small>KG</small>';
          }
        },
        { 
          data: 'fob',
          name: 'fob',
          render: function(data) {
            return '<strong>' + (data ? parseFloat(data).toFixed(2) : '0.00') + '</strong>';
          }
        },
        { 
          data: 'clearing_status',
          name: 'clearing_status',
          render: function(data) { 
            let badgeClass = 'bg-secondary';
            if (data) {
              const status = data.toUpperCase();
              if (status.includes('COMPLETED')) badgeClass = 'bg-success';
              else if (status.includes('PROGRESS')) badgeClass = 'bg-warning';
              else if (status.includes('TRANSIT')) badgeClass = 'bg-info';
            }
            return '<span class="badge ' + badgeClass + '">' + escapeHtml(data || '-') + '</span>';
          }
        },
        {
          data: null,
          name: 'actions',
          orderable: false, 
          searchable: false,
          width: '120px',
          render: function(data, type, row) {
            return `
              <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-primary editBtn" data-id="${parseInt(row.id)}" title="Edit">
                  <i class="ti ti-edit"></i>
                </button>
                <button class="btn btn-success exportBtn" data-id="${parseInt(row.id)}" title="Export">
                  <i class="ti ti-file-spreadsheet"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      order: defaultOrder,
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      responsive: false,
      searching: true,
      language: {
        search: "Search:",
        searchPlaceholder: "Type to search...",
        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
        emptyTable: "No imports found",
        zeroRecords: "No matching imports found",
        info: "Showing _START_ to _END_ of _TOTAL_ imports",
        infoEmpty: "Showing 0 to 0 of 0 imports",
        infoFiltered: "(filtered from _MAX_ total imports)",
        lengthMenu: "Show _MENU_ imports",
        paginate: {
          first: "First",
          last: "Last",
          next: "Next",
          previous: "Previous"
        }
      },
      dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
           "<'row'<'col-sm-12'tr>>" +
           "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
  }

  // ========================================
  // EDIT & DELETE HANDLERS
  // ========================================

  $(document).on('click', '.exportBtn', function () {
    const id = parseInt($(this).data('id'));
    exportToExcel(id);
  });

  $(document).on('click', '.editBtn', function () {
    const id = parseInt($(this).data('id'));
    
    $.ajax({
      url: '<?= APP_URL ?>/import/crudData/details',
      method: 'GET',
      data: { id: id },
      dataType: 'json',
      timeout: 10000,
      success: function(res) {
        if (res.success && res.data) {
          populateEditForm(res.data);
          $('#importTracking').collapse('show');
          $('html, body').animate({ scrollTop: 0 }, 300);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res.message || 'Failed to load import details',
            confirmButtonText: 'OK'
          });
        }
      },
      error: function(xhr) {
        handleAjaxError(xhr, 'Failed to load import details');
      }
    });
  });

  async function populateEditForm(data) {
    $('#import_id').val(data.id);
    $('#formAction').val('update');
    $('#formTitle').text('Edit Import');
    $('#submitBtnText').text('Update Import');
    $('#resetFormBtn').show();

    $('#subscriber_id').val(data.subscriber_id);
    
    try {
      await loadLicensesForClient(data.subscriber_id);
      
      $('#license_id').val(data.license_id);
      
      $('#kind_hidden').val(data.kind || '');
      $('#type_of_goods_hidden').val(data.type_of_goods || '');
      $('#transport_mode_hidden').val(data.transport_mode || '');
      $('#currency_hidden').val(data.currency || '');
      
      currentTypeOfGoodsId = data.type_of_goods || null;
      currentKindId = data.kind || null;
      
      $('#kind_display').val(data.kind_name || '');
      $('#type_of_goods_display').val(data.type_of_goods_name || '');
      $('#transport_mode_display').val(data.transport_mode_name || '');
      $('#currency_display').val(data.currency_name || '');
      $('#license_invoice_number').val(data.license_invoice_number || '');
      $('#rem_weight').val(data.rem_weight || '');
      $('#r_fob').val(data.r_fob || '');
      $('#r_m3').val(data.r_m3 || '');
      $('#rem_weight_hidden').val(data.rem_weight || '');
      $('#r_fob_hidden').val(data.r_fob || '');
      $('#r_m3_hidden').val(data.r_m3 || '');
      handleTransportModeFields(data.transport_mode, data.transport_mode_name);
      handleTypeOfGoodsChange(data.type_of_goods);
      checkPartielleRequired();
      adjustLogisticsLayout();
      
      if (data.license_id) {
        loadPartielleForEdit(data.license_id, data.inspection_reports);
      }
    } catch (error) {
      log.error('Error in populateEditForm:', error);
    }

    $('#mca_ref').val(data.mca_ref || '');
    $('#supplier').val(data.supplier || '');
    $('#regime').val(data.regime || '');
    $('#types_of_clearance').val(data.types_of_clearance || '');
    $('#declaration_office_id').val(data.declaration_office_id || '');
    $('#pre_alert_date').val(data.pre_alert_date || '');
    $('#invoice').val(data.invoice || '');
    $('#commodity').val(data.commodity || '');
    $('#po_ref').val(data.po_ref || '');
    
    $('#fret').val(data.fret || '');
    $('#fret_currency').val(data.fret_currency || '');
    $('#other_charges').val(data.other_charges || '');
    $('#other_charges_currency').val(data.other_charges_currency || '');
    $('#rem_weight').val(data.rem_weight || '');
    $('#weight').val(data.weight || '');
    $('#m3').val(data.m3 || '');
    $('#r_m3').val(data.r_m3 || '');
    $('#cession_date').val(data.cession_date || '');
    $('#r_fob').val(data.r_fob || '');
    $('#r_fob_currency').val(data.r_fob_currency || '');
    $('#fob').val(data.fob || '');
    $('#fob_currency').val(data.fob_currency || '');
    
    $('#crf_reference').val(data.crf_reference || '');
    $('#crf_received_date').val(data.crf_received_date || '');
    $('#clearing_based_on').val(data.clearing_based_on || '');
    $('#ad_date').val(data.ad_date || '');
    $('#insurance_date').val(data.insurance_date || '');
    $('#insurance_amount').val(data.insurance_amount || '');
    $('#insurance_amount_currency').val(data.insurance_amount_currency || '');
    $('#insurance_reference').val(data.insurance_reference || '');
    
    $('#archive_reference').val(data.archive_reference || '');
    $('#audited_date').val(data.audited_date || '');
    $('#archived_date').val(data.archived_date || '');
    
    $('#road_manif').val(data.road_manif || '');
    $('#wagon').val(data.wagon || '');
    $('#horse').val(data.horse || '');
    $('#trailer_1').val(data.trailer_1 || '');
    $('#trailer_2').val(data.trailer_2 || '');
    $('#container').val(data.container || '');
    $('#entry_point_id').val(data.entry_point_id || '');
    $('#entry_point_id_air').val(data.entry_point_id || '');
    
    $('#dgda_in_date').val(data.dgda_in_date || '');
    $('#declaration_reference').val(data.declaration_reference || '');
    $('#segues_rcv_ref').val(data.segues_rcv_ref || '');
    $('#segues_payment_date').val(data.segues_payment_date || '');
    $('#customs_manifest_number').val(data.customs_manifest_number || '');
    $('#customs_manifest_date').val(data.customs_manifest_date || '');
    $('#liquidation_reference').val(data.liquidation_reference || '');
    $('#liquidation_date').val(data.liquidation_date || '');
    $('#liquidation_paid_by').val(data.liquidation_paid_by || '');
    $('#liquidation_amount').val(data.liquidation_amount || '');
    $('#quittance_reference').val(data.quittance_reference || '');
    $('#quittance_date').val(data.quittance_date || '');
    $('#dgda_out_date').val(data.dgda_out_date || '');
    $('#document_status').val(data.document_status || '');
    $('#customs_clearance_code').val(data.customs_clearance_code || '');
    
    $('#airway_bill').val(data.airway_bill || '');
    $('#airway_bill_weight').val(data.airway_bill_weight || '');
    $('#airport_arrival_date').val(data.airport_arrival_date || '');
    $('#dispatch_from_airport').val(data.dispatch_from_airport || '');
    $('#operating_company').val(data.operating_company || '');
    $('#declaration_validity').val(data.declaration_validity || '');
    $('#operating_days').val(data.operating_days || '');
    $('#operating_amount').val(data.operating_amount || '');
    
    $('#t1_number').val(data.t1_number || '');
    $('#t1_date').val(data.t1_date || '');
    $('#arrival_date_zambia').val(data.arrival_date_zambia || '');
    $('#dispatch_from_zambia').val(data.dispatch_from_zambia || '');
    $('#drc_entry_date').val(data.drc_entry_date || '');
    $('#border_warehouse_arrival_date').val(data.border_warehouse_arrival_date || '');
    $('#dispatch_from_border').val(data.dispatch_from_border || '');
    $('#ibs_coupon_reference').val(data.ibs_coupon_reference || '');
    $('#border_warehouse_id').val(data.border_warehouse_id || '');
    $('#entry_coupon').val(data.entry_coupon || '');
    $('#bonded_warehouse_id').val(data.bonded_warehouse_id || '');
    $('#truck_status').val(data.truck_status || '');
    $('#kanyaka_arrival_date').val(data.kanyaka_arrival_date || '');
    $('#kanyaka_dispatch_date').val(data.kanyaka_dispatch_date || '');
    $('#warehouse_arrival_date').val(data.warehouse_arrival_date || '');
    $('#warehouse_departure_date').val(data.warehouse_departure_date || '');
    
    // ========================================
    // ✅ UPDATED: SET DISPATCH/DELIVER DATES BASED ON TRANSPORT MODE
    // ========================================
    const transportMode = (data.transport_mode_name || '').toUpperCase();
    
    if (transportMode.includes('AIR')) {
      // For AIR mode: populate Air field
      $('#dispatch_deliver_date_air').val(data.dispatch_deliver_date || '');
      // Optionally populate main field too
      $('#dispatch_deliver_date').val(data.dispatch_deliver_date || '');
    } else {
      // For ROAD/RAIL mode: populate main field
      $('#dispatch_deliver_date').val(data.dispatch_deliver_date || '');
      // Optionally populate air field too for syncing
      $('#dispatch_deliver_date_air').val(data.dispatch_deliver_date || '');
    }
    
    $('#clearing_status').val(data.clearing_status || '');
    
    $('#remarksContainer').empty();
    if (data.remarks) {
      let remarks = [];
      try {
        remarks = typeof data.remarks === 'string' ? JSON.parse(data.remarks) : data.remarks;
      } catch (e) {
        log.error('Failed to parse remarks:', e);
      }
      
      if (Array.isArray(remarks) && remarks.length > 0) {
        remarks.forEach(remark => {
          addRemarkEntry(remark.date || '', remark.text || '');
        });
      }
    }
    
    adjustLogisticsLayout();
  }

  // ========================================
  // INITIALIZATION
  // ========================================

  initDataTable();
  updateStatistics();
  loadCommoditiesForLicense();

  // Cleanup on page unload
  $(window).on('beforeunload', function() {
    if (autoSaveInterval) {
      clearInterval(autoSaveInterval);
    }
  });
  
  // Calculate remaining weight when weight changes
  $('#weight').on('change input', function() {
    const remWeight = parseFloat($('#rem_weight_hidden').val()) || 0;
    const weight = parseFloat($('#weight').val()) || 0;
    const remaining = remWeight - weight;
    
    // Display the calculated remaining weight
    $('#rem_weight').val(remaining.toFixed(2));
    
    // Add visual feedback
    if (remaining < 0) {
      $('#rem_weight').addClass('text-danger').removeClass('text-success');
    } else {
      $('#rem_weight').addClass('text-success').removeClass('text-danger');
    }
  });
  
  // Calculate remaining FOB when FOB changes
  $('#fob').on('change input', function() {
    const remFob = parseFloat($('#r_fob_hidden').val()) || 0;
    const fob    = parseFloat($('#fob').val()) || 0;

    const remainingFob = remFob - fob;

    // Display the calculated remaining FOB
    $('#r_fob').val(remainingFob.toFixed(2));

    // Visual feedback
    if (remainingFob < 0) {
      $('#r_fob').addClass('text-danger').removeClass('text-success');
    } else {
      $('#r_fob').addClass('text-success').removeClass('text-danger');
    }
  });
  // Calculate remaining M3 when M3 changes
  $('#m3').on('change input', function() {
      const remM3 = parseFloat($('#r_m3_hidden').val()) || 0;
      const m3    = parseFloat($('#m3').val()) || 0;
      const remainingM3 = remM3 - m3;
      
      // Display the calculated remaining M3
      $('#r_m3').val(remainingM3.toFixed(2));
      
      // Visual feedback
      if (remainingM3 < 0) {
          $('#r_m3').addClass('text-danger').removeClass('text-success');
      } else {
          $('#r_m3').addClass('text-success').removeClass('text-danger');
      }
  });
  $('#horse').on('change input', function () {
    const horse = $(this).val().trim();

    if (horse === '') {
        $('#horse_msg').addClass('d-none').text('');
        return;
    }

    $.ajax({
        url: '<?= APP_URL ?>/import/crudData/checkHorse',
        type: 'POST',
        dataType: 'json',
        data: { horse: horse },
        success: function (res) {
            if (res.exists) { 
                $('#horse_msg')
                    .removeClass('d-none')
                    .text('⚠️ Horse already exists in import tracking');
            } else {
                $('#horse_msg')
                    .addClass('d-none')
                    .text('');
            }
        },
        error: function () {
            console.error('Horse check failed');
        }
    });
  });
});
</script>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>