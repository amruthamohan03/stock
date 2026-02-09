<?php
// app/views/invoices/exportinvoice.php - UPDATED: Removed Advanced Filters, Added Pending Card, Reduced Card Sizes
?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<style>
  body { font-size: 12px; }
  
  .stats-card {
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
    cursor: pointer;
    background: white;
    border: 2px solid transparent;
    position: relative;
  }

  .stats-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0,0,0,0.12); }
  .stats-card.active-filter { border-color: #667eea; box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3); transform: scale(1.02); }
  .stats-card .card-body { padding: 12px; position: relative; }
  
  .stats-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
  }
  
  .stats-card-icon i { font-size: 16px; color: white; }
  .icon-purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .icon-green { background: linear-gradient(135deg, #2ECC71 0%, #27AE60 100%); }
  .icon-orange { background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%); }
  .icon-maroon { background: linear-gradient(135deg, #800000 0%, #A52A2A 100%); }
  .icon-blue { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
  
  .stats-value { font-size: 1.4rem; font-weight: 700; color: #2C3E50; margin-bottom: 4px; line-height: 1; }
  .stats-label { font-size: 0.65rem; color: #7F8C8D; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
  
  .filter-indicator {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: bold;
  }
  
  .stats-card.active-filter .filter-indicator { display: flex; }
  
  .accordion-button {
    font-weight: 600;
    background-color: #f8f9fa !important;
    color: #333 !important;
    padding: 0.8rem 1rem;
    border: none;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    font-size: 0.9rem;
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
    border-radius: 10px !important;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 1.2rem;
  }
  
  .accordion-body { background: #ffffff; padding: 1.2rem; }
  
  .invoice-header-row {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    align-items: flex-end;
  }
  
  .invoice-header-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 15px;
  }
  
  .invoice-header-field { flex: 1; }
  
  .invoice-header-field label {
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
    color: #2c3e50;
  }
  
  .invoice-header-field .required::after {
    content: ' *';
    color: #dc3545;
    font-weight: bold;
  }
  
  .invoice-header-field input,
  .invoice-header-field select {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    font-size: 0.8rem;
    height: 32px;
  }
  
  .invoice-header-field input[readonly] {
    background-color: #e9ecef;
    cursor: not-allowed;
    color: #495057;
    font-weight: 500;
  }
  
  .section-header-dark {
    background: #2c3e50;
    color: white;
    padding: 10px 12px;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 20px;
    margin-bottom: 0;
    border-radius: 5px 5px 0 0;
  }
  
  .mca-table-wrapper {
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 5px 5px;
    background: white;
    max-height: 500px;
    overflow-y: auto;
  }
  
  .mca-selection-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
    margin: 0;
  }
  
  .mca-selection-table thead {
    background: #34495e;
    color: white;
    position: sticky;
    top: 0;
    z-index: 10;
  }
  
  .mca-selection-table thead th {
    padding: 10px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    border-bottom: 2px solid #667eea;
    white-space: nowrap;
  }
  
  .mca-selection-table tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
  }
  
  .mca-selection-table tbody tr:hover { background-color: #f8f9fa; }
  .mca-selection-table tbody tr.selected { background-color: #e3f2fd; }
  
  .mca-selection-table tbody td {
    padding: 5px 6px;
    vertical-align: middle;
    white-space: nowrap;
  }
  
  .mca-selection-table tbody td input[type="text"],
  .mca-selection-table tbody td input[type="date"],
  .mca-selection-table tbody td input[type="number"] {
    width: 100%;
    min-width: 85px;
    padding: 4px 6px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 500;
    color: #2c3e50;
    background: #ffffff;
    transition: all 0.2s ease;
    height: 28px;
    line-height: 1.2;
  }
  
  .mca-selection-table tbody td input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.12);
    background: #f8f9ff;
    transform: scale(1.01);
  }
  
  /* Auto-calculated field styling */
  .mca-selection-table tbody td input.auto-calculated-field {
    background: #fff3cd !important;
    border-color: #ffc107 !important;
    font-weight: 700 !important;
    cursor: not-allowed;
  }
  
  .mca-selection-table tbody td input.auto-calculated-field:focus {
    background: #fff3cd !important;
    border-color: #ffc107 !important;
  }
  
  .mca-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
  }
  
  .mca-ref-link {
    color: #667eea;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    font-size: 0.8rem;
  }
  
  .mca-ref-link:hover {
    text-decoration: underline;
    color: #5568d3;
  }
  
  .quotation-section { margin-top: 20px; }
  .quotation-header { margin-bottom: 12px; }
  .quotation-selector { width: 100%; }
  
  .quotation-selector label {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
    color: #2c3e50;
  }
  
  .quotation-selector select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #2c3e50;
    background: white;
    transition: all 0.2s ease;
    height: 36px;
  }
  
  .quotation-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    overflow: hidden;
  }
  
  .quotation-items-table thead {
    background: #34495e;
    color: white;
  }
  
  .quotation-items-table thead th {
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    border-bottom: 2px solid #667eea;
  }
  
  .quotation-items-table tbody tr { border-bottom: 1px solid #e9ecef; }
  .quotation-items-table tbody tr:hover { background-color: #f8f9fa; }
  
  .quotation-items-table tbody tr.category-header-row:hover,
  .quotation-items-table tbody tr.category-subtotal-row:hover,
  .quotation-items-table tbody tr.category-spacer:hover {
    background-color: inherit !important;
  }
  
  .quotation-items-table tbody td {
    padding: 8px 10px;
    vertical-align: middle;
  }
  
  .quotation-items-table tfoot {
    background: #000;
    color: white;
    font-weight: 700;
  }
  
  .quotation-items-table tfoot td {
    padding: 10px;
    border-top: 2px solid #dee2e6;
    font-size: 0.75rem;
  }
  
  .quotation-items-table .form-control-sm {
    font-size: 0.72rem;
    padding: 4px 6px;
    height: 28px;
  }
  
  /* NEW: Editable field styling */
  .quotation-items-table input.editable-field {
    background: #fff !important;
    border-color: #28a745 !important;
    font-weight: 600;
  }
  
  .quotation-items-table input.editable-field:focus {
    background: #f0fff4 !important;
    border-color: #28a745 !important;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.15) !important;
  }
  
  .quotation-items-table input.auto-calculated {
    background: #fff3cd !important;
    border-color: #ffc107 !important;
    font-weight: 700;
  }
  
  .text-right { text-align: right; }
  .text-center { text-align: center; }
  
  .validation-badge {
    padding: 4px 10px;
    border-radius: 15px;
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
  
  .datatable-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 12px 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
  }
  
  .datatable-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .datatable-actions { 
    display: flex; 
    align-items: center; 
    gap: 8px; 
    flex-wrap: wrap;
  }
  
  .custom-search-box { position: relative; width: 220px; }
  
  .custom-search-box input {
    width: 100%;
    padding: 0.4rem 2rem 0.4rem 0.8rem;
    border: 1px solid #e9ecef;
    border-radius: 0.3rem;
    font-size: 0.75rem;
    transition: all 0.3s;
    height: 32px;
  }
  
  .custom-search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.25);
  }
  
  .custom-search-box i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    pointer-events: none;
    font-size: 0.85rem;
  }
  
  .btn-export-dn {
    background: #ff9800 !important;
    border: none !important;
    color: white !important;
    font-weight: 600 !important;
    padding: 0.4rem 1rem !important;
    font-size: 0.75rem !important;
    border-radius: 0.3rem !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.4rem !important;
    transition: all 0.3s !important;
    height: 32px;
  }
  
  .btn-export-dn:hover {
    background: #f57c00 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
  }
  
  .btn-export-inv {
    background: #28a745 !important;
    border: none !important;
    color: white !important;
    font-weight: 600 !important;
    padding: 0.4rem 1rem !important;
    font-size: 0.75rem !important;
    border-radius: 0.3rem !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.4rem !important;
    transition: all 0.3s !important;
    height: 32px;
  }
  
  .btn-export-inv:hover {
    background: #218838 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
  }
  
  .card {
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: none;
    border-radius: 10px;
  }
  
  .btn-sm { 
    padding: 0.3rem 0.55rem; 
    font-size: 0.75rem;
    height: 28px;
    line-height: 1.2;
  }
  
  .btn-pdf-p1 {
    background: #dc3545 !important;
    color: white !important;
    border: none !important;
    font-weight: 600 !important;
  }
  
  .btn-pdf-p1:hover {
    background: #c82333 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
  }
  
  .btn-pdf-p2 {
    background: #e74c3c !important;
    color: white !important;
    border: none !important;
    font-weight: 600 !important;
  }
  
  .btn-pdf-p2:hover {
    background: #c0392b !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
  }
  
  .btn-details {
    background: #17a2b8 !important;
    color: white !important;
    border: none !important;
  }
  
  .btn-details:hover {
    background: #138496 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
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
  
  .is-invalid { border-color: #dc3545 !important; }
  
  .invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.65rem;
    margin-top: 0.2rem;
  }
  
  .mca-table-wrapper::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  
  .mca-table-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 8px;
  }
  
  .mca-table-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
  }
  
  .dataTables_wrapper .dataTables_info {
    font-size: 0.75rem;
    padding-top: 0.5rem;
  }
  
  .dataTables_wrapper .dataTables_paginate {
    font-size: 0.75rem;
    padding-top: 0.5rem;
  }
  
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.3rem 0.6rem;
    font-size: 0.75rem;
  }
  
  .table { font-size: 0.75rem; }
  
  .table thead th {
    font-size: 0.7rem;
    padding: 0.6rem;
  }
  
  .table tbody td {
    padding: 0.5rem;
    font-size: 0.75rem;
  }
  
  /* ========================================
     DGI MODAL STYLES - IMPROVED DESIGN
     ======================================== */
  
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
    .stats-card .card-body { padding: 10px; }
    .stats-value { font-size: 1.2rem; }
  }
  
  @media (max-width: 992px) {
    .invoice-header-row {
      flex-wrap: wrap;
    }
    
    .invoice-header-field {
      flex: 1 1 45%;
      min-width: 180px;
    }
    
    .datatable-header {
      flex-direction: column;
      gap: 8px;
      align-items: flex-start;
    }
    
    .datatable-actions {
      width: 100%;
      flex-direction: column;
    }
    
    .custom-search-box { width: 100%; }
    
    .btn-export-dn,
    .btn-export-inv {
      width: 100%;
      justify-content: center !important;
    }
    
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
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">
        
        <!-- STATISTICS CARDS - 5 CARDS WITH REDUCED SIZE -->
        <div class="row mb-3">
          <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card" data-filter="all">
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
          
          <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card" data-filter="validated">
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
          
          <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card" data-filter="not-validated">
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
          
          <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card" data-filter="dgi-verified">
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
          
          <div class="col-xl col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card filter-card" data-filter="pending">
              <div class="card-body">
                <div class="stats-card-icon icon-blue">
                  <i class="ti ti-clock-pause"></i>
                </div>
                <div class="stats-value" id="totalPending">0</div>
                <div class="stats-label">Pending Invoicing</div>
                <div class="filter-indicator">✓</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ACCORDION WITH FORM -->
        <div class="accordion mb-4" id="invoiceAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingInvoice">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvoice" aria-expanded="false" aria-controls="collapseInvoice">
                <div class="accordion-title-section">
                  <i class="ti ti-file-invoice"></i>
                  <span id="formTitle">New Export Invoice</span>
                </div>
              </button>
            </h2>
            <div id="collapseInvoice" class="accordion-collapse collapse" aria-labelledby="headingInvoice" data-bs-parent="#invoiceAccordion">
              <div class="accordion-body">
                
                <form id="invoiceForm" method="post" novalidate data-csrf-token="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="invoice_id" id="invoice_id" value="">
                  <input type="hidden" name="action" id="formAction" value="insert">
                  <input type="hidden" name="mca_id" id="mca_id" value="">
                  <input type="hidden" name="mca_data" id="mca_data" value="">
                  <input type="hidden" name="quotation_id" id="quotation_id" value="">
                  <input type="hidden" name="quotation_sub_total" id="quotation_sub_total" value="">
                  <input type="hidden" name="quotation_vat_amount" id="quotation_vat_amount" value="">
                  <input type="hidden" name="quotation_total_amount" id="quotation_total_amount" value="">
                  <input type="hidden" name="quotation_items" id="quotation_items" value="">
                  <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                  <!-- HEADER ROW -->
                  <div class="invoice-header-row">
                    <div class="invoice-header-field">
                      <label class="required">Client</label>
                      <select name="client_id" id="client_id" class="form-select" required>
                        <option value="">-- Select Client --</option>
                        <?php foreach ($clients as $client): ?>
                          <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['short_name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="invalid-feedback" id="client_id_error"></div>
                    </div>

                    <div class="invoice-header-field">
                      <label class="required">License Number</label>
                      <select name="license_id" id="license_id" class="form-select" required>
                        <option value="">-- Select License --</option>
                      </select>
                      <div class="invalid-feedback" id="license_id_error"></div>
                    </div>

                    <div class="invoice-header-field">
                      <label class="required">Invoice Reference</label>
                      <input type="text" name="invoice_ref" id="invoice_ref" class="form-control" required readonly placeholder="Auto-generated">
                      <div class="invalid-feedback" id="invoice_ref_error"></div>
                    </div>

                    <div class="invoice-header-field">
                      <label>Invoice Date</label>
                      <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="invoice-header-field">
                      <label>ARSP</label>
                      <select name="arsp" id="arsp" class="form-select">
                        <option value="Disabled">Disabled</option>
                        <option value="Enabled">Enabled</option>
                      </select>
                    </div>
                  </div>

                  <!-- SECOND ROW -->
                  <div class="invoice-header-grid" style="margin-top: 15px;">
                    <div class="invoice-header-field">
                      <label class="required">Kind</label>
                      <input type="text" name="kind_name" id="kind_name" class="form-control" readonly placeholder="Select MCA to auto-fill">
                      <input type="hidden" name="kind_id" id="kind_id">
                      <div class="invalid-feedback" id="kind_id_error"></div>
                    </div>

                    <div class="invoice-header-field">
                      <label class="required">Type of Goods</label>
                      <input type="text" name="goods_type_name" id="goods_type_name" class="form-control" readonly placeholder="Select MCA to auto-fill">
                      <input type="hidden" name="goods_type_id" id="goods_type_id">
                      <div class="invalid-feedback" id="goods_type_id_error"></div>
                    </div>

                    <div class="invoice-header-field">
                      <label class="required">Transport Mode</label>
                      <input type="text" name="transport_mode_name" id="transport_mode_name" class="form-control" readonly placeholder="Select MCA to auto-fill">
                      <input type="hidden" name="transport_mode_id" id="transport_mode_id">
                      <div class="invalid-feedback" id="transport_mode_id_error"></div>
                    </div>
                  </div>

                  <!-- MCA REFERENCES -->
                  <div class="section-header-dark">
                    MCA References - Select Files 
                    <span id="mcaCountBadge" style="background: #667eea; color: white; padding: 3px 10px; border-radius: 10px; font-size: 0.75rem; margin-left: 8px; display: none;">0 Selected</span>
                  </div>
                  <div class="mca-table-wrapper">
                    <table class="mca-selection-table">
                      <thead>
                        <tr>
                          <th style="width: 50px;"><input type="checkbox" id="selectAllMCA"></th>
                          <th style="width: 150px;">MCA Ref</th>
                          <th style="width: 120px;">Lot Number</th>
                          <th style="width: 130px;">Declaration No</th>
                          <th style="width: 120px;">Declaration Date</th>
                          <th style="width: 130px;">Liquidation No</th>
                          <th style="width: 120px;">Liquidation Date</th>
                          <th style="width: 130px;">Liquidation CDF</th>
                          <th style="width: 130px;">Liquidation USD</th>
                          <th style="width: 130px;">Quittance No</th>
                          <th style="width: 120px;">Quittance Date</th>
                          <th style="width: 100px;">Horse</th>
                          <th style="width: 100px;">Trailer 1</th>
                          <th style="width: 100px;">Trailer 2</th>
                          <th style="width: 120px;">Container</th>
                          <th style="width: 120px;">Feet Container</th>
                          <th style="width: 100px;">Weight (MT)</th>
                          <th style="width: 100px;">BCC Rate</th>
                          <th style="width: 150px;">Buyer</th>
                          <th style="width: 100px;">CEEC (CDF)</th>
                          <th style="width: 100px;">CGEA (CDF)</th>
                          <th style="width: 100px;">OCC (CDF)</th>
                          <th style="width: 100px;">LMC (CDF)</th>
                          <th style="width: 100px;">OGEFREM (CDF)</th>
                        </tr>
                      </thead>
                      <tbody id="mcaTableBody">
                        <tr>
                          <td colspan="24" class="text-center" style="padding: 30px;">
                            <i class="ti ti-info-circle me-2" style="font-size: 1.5rem; color: #9ca3af;"></i>
                            <div style="margin-top: 8px; color: #6b7280; font-weight: 600; font-size: 0.75rem;">Select a Client and License to load MCA references</div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- QUOTATION ITEMS -->
                  <div class="quotation-section">
                    <div class="section-header-dark">
                      Quotation Items
                    </div>
                    
                    <div style="background: white; border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 5px 5px; padding: 15px;">
                      <div class="quotation-header">
                        <div class="quotation-selector">
                          <label>
                            <i class="ti ti-file-text me-2"></i>
                            Quotation Reference (Auto-matched)
                          </label>
                          <input type="text" id="quotationRefDisplay" class="form-control" readonly placeholder="Will auto-select when MCA is chosen" style="background-color: #e9ecef; font-weight: 600;">
                          <input type="hidden" id="quotationSelector" value="">
                        </div>
                      </div>

                      <div id="quotationItemsContainer">
                        <table class="quotation-items-table">
                          <thead>
                            <tr>
                              <th style="width: 38%;">Description</th>
                              <th style="width: 8%;" class="text-center">Unit</th>
                              <th style="width: 11%;" class="text-right">Cost/USD</th>
                              <th style="width: 12%;" class="text-right">Subtotal USD</th>
                              <th style="width: 12%;" class="text-right">TVA 16%</th>
                              <th style="width: 12%;" class="text-right">Total USD</th>
                            </tr>
                          </thead>
                          <tbody id="quotationItemsBody">
                            <tr>
                              <td colspan="6" class="text-center" style="padding: 30px;">
                                <i class="ti ti-info-circle me-2" style="font-size: 1.5rem; color: #9ca3af;"></i>
                                <div style="margin-top: 8px; color: #6b7280; font-weight: 600; font-size: 0.75rem;">Select a quotation to display items</div>
                              </td>
                            </tr>
                          </tbody>
                          <tfoot id="quotationItemsFooter" style="display: none;">
                            <tr>
                              <td colspan="3" class="text-right" style="background: #000; color: #fff; font-weight: 700; text-transform: uppercase; padding: 10px; font-size: 0.65rem;">TOTAL CLEARING COST IN USD / COUT TOTAL EN USD:</td>
                              <td class="text-right" style="background: #000; color: #fff; font-weight: 700; padding: 10px;" id="footerSubtotal">0.00</td>
                              <td class="text-right" style="background: #000; color: #fff; font-weight: 700; padding: 10px;" id="footerTVA">0.00</td>
                              <td class="text-right" style="background: #000; color: #fff; font-weight: 700; padding: 10px; font-size: 0.9rem;" id="footerTotal">0.00</td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>

                  <!-- Form Buttons -->
                  <div class="row mt-3">
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

        <!-- Invoices DataTable Card -->
        <div class="card shadow-sm">
          <div class="datatable-header">
            <div class="datatable-title">
              <i class="ti ti-list"></i>
              <span>Export Invoices List</span>
            </div>
            <div class="datatable-actions">
              <button type="button" class="btn btn-sm btn-secondary" id="clearFilterBtn" style="display:none;">
                <i class="ti ti-filter-off me-1"></i> Clear Filter
              </button>
              <div class="custom-search-box">
                <input type="text" id="customSearchBox" placeholder="Search invoices..." autocomplete="off">
                <i class="ti ti-search"></i>
              </div>
              <button type="button" class="btn btn-export-dn" onclick="exportAllDebitNotes();">
                <i class="ti ti-file-spreadsheet"></i> Export DN
              </button>
              <button type="button" class="btn btn-export-inv" onclick="exportAllInvoices();">
                <i class="ti ti-file-spreadsheet"></i> Export INV
              </button>
            </div>
          </div>
          
          <div class="card-body">
            <div class="table-responsive">
              <table id="invoicesTable" class="table table-striped table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Invoice Ref</th>
                    <th>Client</th>
                    <th>MCA Count</th>
                    <th>FOB USD</th>
                    <th>Total Duty</th>
                    <th>Validation</th>
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

<!-- ========================================
     DGI VERIFICATION MODAL - IMPROVED DESIGN
     ======================================== -->
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
$(document).ready(function () {
  let invoicesTable;
  let currentFilter = 'all';
  let quotationItemsData = [];
  let originalQuotationItems = [];
  let quotationData = null;
  let isEditMode = false;
  let allQuotationsData = [];
  let editModeDataLoaded = false;

  let baseUrl = '<?= rtrim(APP_URL, "/") ?>';
  const CONTROLLER_URL = baseUrl + '/exportinvoice';
  const csrfToken = $('#invoiceForm').data('csrf-token');
  
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#039;'};
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  // ========== AUTO-CALCULATE LIQUIDATION USD ==========
  function calculateLiquidationUSD($row) {
    const liquidationCDF = parseFloat($row.find('[data-field="liquidation_amount"]').val()) || 0;
    const bccRate = parseFloat($row.find('[data-field="bcc_rate"]').val()) || 2500;
    
    const liquidationUSD = bccRate > 0 ? liquidationCDF / bccRate : 0;
    
    $row.find('[data-field="liquidation_usd"]').val(liquidationUSD.toFixed(2));
    
    console.log('💵 Liquidation USD calculated:', liquidationUSD.toFixed(2), '(CDF:', liquidationCDF, '/ BCC Rate:', bccRate, ')');
  }

  // ========== Calculate Total Liquidation USD from all selected MCAs ==========
  function calculateTotalLiquidationUSD() {
    let totalLiquidationUSD = 0;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const liquidationUSD = parseFloat($row.find('[data-field="liquidation_usd"]').val()) || 0;
      totalLiquidationUSD += liquidationUSD;
    });
    
    console.log('💵 Total Liquidation USD:', totalLiquidationUSD.toFixed(2));
    return totalLiquidationUSD;
  }

  // ========== Get actual subtotal of a special item from DOM ==========
  function getSpecialItemSubtotal(itemType) {
    let subtotal = 0;
    
    $('.quotation-item-row:visible').each(function() {
      const $row = $(this);
      const itemName = $row.find('.item-desc').val();
      const specialType = isSpecialItem(itemName);
      
      if (specialType === itemType) {
        subtotal = parseFloat($row.find('.item-subtotal').val()) || 0;
        return false; // Break loop
      }
    });
    
    return subtotal;
  }

  // ========== Helper to check if item is FICHE ELECTRONIQUE ==========
  function isFicheElectroniqueItem(itemName) {
    if (!itemName) return false;
    
    const upperName = itemName.toUpperCase();
    
    const hasFiche = upperName.includes('FICHE') && upperName.includes('ELECTRONIQUE');
    const hasRenseignement = upperName.includes('RENSEIGNEMENT');
    const hasExportation = upperName.includes('EXPORTATION');
    
    return hasFiche && hasRenseignement && hasExportation;
  }

  // ========== Check if item is Finance Cost 1% ==========
  function isFinanceCost1PercentItem(itemName) {
    if (!itemName) return false;
    
    const upperName = itemName.toUpperCase();
    
    return upperName.includes('FINANCE') && 
           upperName.includes('COST') && 
           upperName.includes('1%') &&
           upperName.includes('DROIT') &&
           upperName.includes('DOUANE');
  }

  // ========== Calculate CUSTOMS CLEARANCE FEES category subtotal ==========
  function calculateCustomsClearanceSubtotal() {
    let customsSubtotal = 0;
    
    $('.quotation-item-row:visible').each(function() {
      const categoryName = ($(this).data('category-name') || '').toUpperCase();
      const categoryHeader = ($(this).data('category-header') || '').toUpperCase();
      const itemName = $(this).find('.item-desc').val();
      
      // Skip Finance Cost item itself
      if (isFinanceCost1PercentItem(itemName)) {
        return;
      }
      
      // Check if this item belongs to CUSTOMS CLEARANCE FEES category
      if ((categoryName.includes('CUSTOMS') && categoryName.includes('CLEARANCE')) ||
          (categoryName.includes('FRAIS') && categoryName.includes('DEDOUANEMENT')) ||
          (categoryHeader.includes('CUSTOMS') && categoryHeader.includes('CLEARANCE')) ||
          (categoryHeader.includes('FRAIS') && categoryHeader.includes('DEDOUANEMENT'))) {
        
        const itemSubtotal = parseFloat($(this).find('.item-subtotal').val()) || 0;
        customsSubtotal += itemSubtotal;
      }
    });
    
    console.log('💰 Customs Clearance Subtotal:', customsSubtotal.toFixed(2));
    return customsSubtotal;
  }

  // ========== Update Finance Cost items with customs subtotal ==========
  function updateFinanceCostItems() {
    const customsSubtotal = calculateCustomsClearanceSubtotal();
    
    $('.quotation-item-row:visible').each(function() {
      const $row = $(this);
      const itemName = $row.find('.item-desc').val();
      const isFinanceCost = isFinanceCost1PercentItem(itemName);
      
      if (isFinanceCost) {
        console.log('💳 Updating Finance Cost item unit to:', customsSubtotal.toFixed(2));
        
        // Set unit to customs clearance subtotal
        $row.find('.item-unit').val(customsSubtotal.toFixed(2));
        
        // Calculate Finance Cost subtotal (1% of customs clearance)
        const financeSubtotal = customsSubtotal * 0.01;
        $row.find('.item-subtotal').val(financeSubtotal.toFixed(2));
        
        // Calculate TVA and Total
        const hasTVA = parseInt($row.data('has-tva')) || 0;
        const tva = hasTVA === 1 ? financeSubtotal * 0.16 : 0;
        const total = financeSubtotal + tva;
        
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      }
    });
  }

  // ========== Update FSR items with liquidation calculation ==========
  function updateFSRItems() {
    const totalLiquidationUSD = calculateTotalLiquidationUSD();
    const rieSubtotal = getSpecialItemSubtotal('RIE');
    const rlsSubtotal = getSpecialItemSubtotal('RLS');
    const lseSubtotal = getSpecialItemSubtotal('LSE');
    
    const fsrSubtotal = totalLiquidationUSD - rieSubtotal - rlsSubtotal - lseSubtotal;
    
    console.log('🧮 FSR Calculation:');
    console.log('  Total Liquidation USD:', totalLiquidationUSD.toFixed(2));
    console.log('  - RIE Subtotal:', rieSubtotal.toFixed(2));
    console.log('  - RLS Subtotal:', rlsSubtotal.toFixed(2));
    console.log('  - LSE Subtotal:', lseSubtotal.toFixed(2));
    console.log('  = FSR Subtotal:', fsrSubtotal.toFixed(2));
    
    $('.quotation-item-row:visible').each(function() {
      const $row = $(this);
      const itemName = $row.find('.item-desc').val();
      const specialType = isSpecialItem(itemName);
      
      if (specialType === 'FSR') {
        console.log('📋 Updating FSR item');
        
        // Set subtotal to calculated FSR value
        $row.find('.item-subtotal').val(fsrSubtotal.toFixed(2));
        
        // Calculate cost per unit
        const unit = parseFloat($row.find('.item-unit').val()) || 1;
        const costUsd = unit > 0 ? fsrSubtotal / unit : 0;
        $row.find('.item-cost-usd').val(costUsd.toFixed(2));
        
        // Add styling to indicate auto-calculation
        $row.find('.item-subtotal').addClass('auto-calculated');
        $row.find('.item-cost-usd').addClass('auto-calculated');
        
        // Calculate TVA and Total
        const hasTVA = parseInt($row.data('has-tva')) || 0;
        const tva = hasTVA === 1 ? fsrSubtotal * 0.16 : 0;
        const total = fsrSubtotal + tva;
        
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      }
    });
  }

  // ========== FIXED: Calculate split unit count based on item type ==========
  function calculateSplitUnitForItem(itemName) {
    if (!itemName) return null;
    
    const upperName = itemName.toUpperCase();
    
    // ========== OGEFREM CONTENEUR LOGIC ==========
    if (upperName.includes('OGEFREM') && upperName.includes('CONTENEUR')) {
      const matches = upperName.match(/CONTENEUR\s*(\d+)/);
      if (matches && matches[1]) {
        const itemContainerSize = matches[1];
        
        console.log('📦 OGEFREM Conteneur ' + itemContainerSize + ' - Checking MCAs...');
        
        let matchingCount = 0;
        $('.mca-checkbox:checked').each(function() {
          const $row = $(this).closest('tr');
          const mcaFeetSize = ($row.attr('data-feet-container-size') || '').trim();
          
          const mcaSizeMatch = mcaFeetSize.match(/\d+/);
          if (mcaSizeMatch && mcaSizeMatch[0] === itemContainerSize) {
            matchingCount++;
            console.log('  ✅ Matched MCA with ' + mcaFeetSize);
          }
        });
        
        console.log('✨ OGEFREM ' + itemContainerSize + "' → Unit: " + matchingCount);
        return matchingCount;
      }
    }
    
    // ========== CEEC CERTIFICATE WEIGHT RANGE LOGIC ==========
    if ((upperName.includes('CEEC') && upperName.includes('CERTIF')) || 
        (upperName.includes('CERTIFICAT') && upperName.includes('CEEC'))) {
      
      console.log('🔍 CEEC Item detected:', itemName.substring(0, 80));
      
      // Pattern 1: LESS THAN 30MT
      if (upperName.includes('LESS THAN 30') || 
          upperName.includes('MOINS DE 30') || 
          upperName.includes('<30') ||
          upperName.includes('< 30') ||
          upperName.match(/BELOW\s*30/i) ||
          upperName.match(/UNDER\s*30/i)) {
        
        console.log('  📏 Range: LESS THAN 30MT (0 < weight ≤ 30)');
        
        let count = 0;
        $('.mca-checkbox:checked').each(function() {
          const $row = $(this).closest('tr');
          const weightInput = $row.find('[data-field="weight"]');
          const weight = parseFloat(weightInput.val() || 0);
          
          console.log('    MCA Weight:', weight);
          
          if (weight > 0 && weight <= 30) {
            count++;
            console.log('      ✅ MATCHED (weight ≤ 30)');
          } else if (weight > 0) {
            console.log('      ❌ Not matched (weight > 30)');
          } else {
            console.log('      ⚠️ Weight is 0 or empty');
          }
        });
        
        console.log('  ✨ Total CEEC <30MT → Unit:', count);
        return count;
      }
      
      // Pattern 2: 30MT TO 60MT
      else if ((upperName.includes('30MT') && upperName.includes('60MT')) ||
               (upperName.includes('30 MT') && upperName.includes('60 MT')) ||
               (upperName.includes('30') && upperName.includes('60') && 
                (upperName.includes('TO') || upperName.includes('A') || upperName.includes('-')))) {
        
        console.log('  📏 Range: 30MT TO 60MT (30 < weight ≤ 60)');
        
        let count = 0;
        $('.mca-checkbox:checked').each(function() {
          const $row = $(this).closest('tr');
          const weightInput = $row.find('[data-field="weight"]');
          const weight = parseFloat(weightInput.val() || 0);
          
          console.log('    MCA Weight:', weight);
          
          if (weight > 30 && weight <= 60) {
            count++;
            console.log('      ✅ MATCHED (30 < weight ≤ 60)');
          } else if (weight > 0) {
            console.log('      ❌ Not matched (outside range)');
          } else {
            console.log('      ⚠️ Weight is 0 or empty');
          }
        });
        
        console.log('  ✨ Total CEEC 30-60MT → Unit:', count);
        return count;
      }
      
      // Pattern 3: 60MT TO 90MT
      else if ((upperName.includes('60MT') && upperName.includes('90MT')) ||
               (upperName.includes('60 MT') && upperName.includes('90 MT')) ||
               (upperName.includes('60') && upperName.includes('90') && 
                (upperName.includes('TO') || upperName.includes('A') || upperName.includes('-')))) {
        
        console.log('  📏 Range: 60MT TO 90MT (60 < weight ≤ 90)');
        
        let count = 0;
        $('.mca-checkbox:checked').each(function() {
          const $row = $(this).closest('tr');
          const weightInput = $row.find('[data-field="weight"]');
          const weight = parseFloat(weightInput.val() || 0);
          
          console.log('    MCA Weight:', weight);
          
          if (weight > 60 && weight <= 90) {
            count++;
            console.log('      ✅ MATCHED (60 < weight ≤ 90)');
          } else if (weight > 0) {
            console.log('      ❌ Not matched (outside range)');
          } else {
            console.log('      ⚠️ Weight is 0 or empty');
          }
        });
        
        console.log('  ✨ Total CEEC 60-90MT → Unit:', count);
        return count;
      }
      
      // Pattern 4: ABOVE 90MT
      else if (upperName.includes('ABOVE 90') || 
               upperName.includes('PLUS DE 90') || 
               upperName.includes('MORE THAN 90') ||
               upperName.includes('>90') ||
               upperName.includes('> 90') ||
               upperName.match(/OVER\s*90/i)) {
        
        console.log('  📏 Range: ABOVE 90MT (weight > 90)');
        
        let count = 0;
        $('.mca-checkbox:checked').each(function() {
          const $row = $(this).closest('tr');
          const weightInput = $row.find('[data-field="weight"]');
          const weight = parseFloat(weightInput.val() || 0);
          
          console.log('    MCA Weight:', weight);
          
          if (weight > 90) {
            count++;
            console.log('      ✅ MATCHED (weight > 90)');
          } else if (weight > 0) {
            console.log('      ❌ Not matched (weight ≤ 90)');
          } else {
            console.log('      ⚠️ Weight is 0 or empty');
          }
        });
        
        console.log('  ✨ Total CEEC >90MT → Unit:', count);
        return count;
      } else {
        console.log('  ⚠️ CEEC item found but no weight range pattern matched');
      }
    }
    
    return null;
  }

  // ========== Calculate Total Weight Function ==========
  function calculateTotalWeight() {
    let totalWeight = 0;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const weight = parseFloat($row.find('[data-field="weight"]').val()) || 0;
      totalWeight += weight;
    });
    
    return totalWeight;
  }

  // ========== Calculate VRAC Weight Only ==========
  function calculateVRACWeight() {
    let vracWeight = 0;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const feetSize = ($row.attr('data-feet-container-size') || '').toUpperCase().trim();
      
      // Check if this is VRAC
      if (feetSize === 'VRAC' || feetSize === 'V RAC' || feetSize.replace(/\s/g, '') === 'VRAC') {
        const weight = parseFloat($row.find('[data-field="weight"]').val()) || 0;
        vracWeight += weight;
        console.log('  ✅ VRAC MCA - Weight:', weight);
      }
    });
    
    console.log('💼 Total VRAC Weight:', vracWeight.toFixed(3), 'MT');
    return vracWeight;
  }

  // ========== Calculate Vehicle Count ==========
  function calculateVehicleCount() {
    let vehicleCount = 0;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const horse = $row.find('[data-field="horse"]').val();
      const trailer1 = $row.find('[data-field="trailer_1"]').val();
      const trailer2 = $row.find('[data-field="trailer_2"]').val();
      
      if (horse && horse.trim() !== '' && horse.trim().toLowerCase() !== 'n/a') vehicleCount++;
      if (trailer1 && trailer1.trim() !== '' && trailer1.trim().toLowerCase() !== 'n/a') vehicleCount++;
      if (trailer2 && trailer2.trim() !== '' && trailer2.trim().toLowerCase() !== 'n/a') vehicleCount++;
    });
    
    return vehicleCount;
  }

  // ========== Calculate Trailer Count ==========
  function calculateTrailerCount() {
    let trailerCount = 0;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const trailer1 = $row.find('[data-field="trailer_1"]').val();
      const trailer2 = $row.find('[data-field="trailer_2"]').val();
      
      if (trailer1 && trailer1.trim() !== '' && trailer1.trim().toLowerCase() !== 'n/a') trailerCount++;
      if (trailer2 && trailer2.trim() !== '' && trailer2.trim().toLowerCase() !== 'n/a') trailerCount++;
    });
    
    return trailerCount;
  }

  // ========== MCA Count Badge with Weight ==========
  function updateMCACountBadge() {
    const count = $('.mca-checkbox:checked').length;
    const totalWeight = calculateTotalWeight();
    
    if (count > 0) {
      $('#mcaCountBadge')
        .html(`${count} Selected | ${totalWeight.toFixed(3)} MT`)
        .show();
    } else {
      $('#mcaCountBadge').hide();
    }
    
    console.log('\n========================================');
    console.log('📊 MCA BADGE UPDATE');
    console.log('Selected MCAs:', count);
    console.log('Total Weight:', totalWeight.toFixed(3), 'MT');
    console.log('========================================');
    
    updateAllItemUnitsAndSpecialItems(count, totalWeight);
  }
  
  // ========== Check if item name contains special keywords ==========
  function isSpecialItem(itemName) {
    if (!itemName) return null;
    
    const upperName = itemName.toUpperCase();
    
    if (upperName.includes('(RIE)')) return 'RIE';
    if (upperName.includes('(RLS)')) return 'RLS';
    if (upperName.includes('(LSE)')) return 'LSE';
    if (upperName.includes('(FSR)')) return 'FSR';
    
    return null;
  }
  
  // ========== Calculate special item subtotals ==========
  function calculateSpecialItemSubtotal(itemType) {
    switch(itemType) {
      case 'RIE':
        return 50.00;
        
      case 'RLS':
        return calculateVehicleCount() * 140.00;
        
      case 'LSE':
        return calculateTrailerCount() * 30.00;
        
      case 'FSR':
        // FSR = Total Liquidation USD - RIE - RLS - LSE
        const totalLiquidationUSD = calculateTotalLiquidationUSD();
        const rieSubtotal = getSpecialItemSubtotal('RIE');
        const rlsSubtotal = getSpecialItemSubtotal('RLS');
        const lseSubtotal = getSpecialItemSubtotal('LSE');
        
        const fsrSubtotal = totalLiquidationUSD - rieSubtotal - rlsSubtotal - lseSubtotal;
        
        console.log('🧮 FSR = ' + totalLiquidationUSD.toFixed(2) + ' - ' + rieSubtotal.toFixed(2) + ' - ' + rlsSubtotal.toFixed(2) + ' - ' + lseSubtotal.toFixed(2) + ' = ' + fsrSubtotal.toFixed(2));
        
        return fsrSubtotal;
        
      default:
        return 0;
    }
  }
  
  // ========== FIXED: Update all items including split calculations ==========
  function updateAllItemUnitsAndSpecialItems(mcaCount, totalWeight) {
    if (mcaCount <= 0) {
      mcaCount = 1;
      totalWeight = 0;
    }
    
    console.log('\n🔄 ========== UPDATING ALL ITEM UNITS ==========');
    console.log('MCA Count:', mcaCount, '| Total Weight:', totalWeight.toFixed(3), 'MT');
    
    const vracWeight = calculateVRACWeight();
    
    $('.item-unit').each(function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      const itemName = $row.find('.item-desc').val();
      const specialType = isSpecialItem(itemName);
      const isFinanceCost = isFinanceCost1PercentItem(itemName);
      const isFicheElectronique = isFicheElectroniqueItem(itemName);
      
      const unitId = $row.data('unit-id');
      const hasUnit = unitId && unitId !== '' && unitId !== 'null' && unitId !== null;
      
      // Check if item needs split unit calculation
      const splitUnit = calculateSplitUnitForItem(itemName);
      
      let unitType, newUnitValue;
      
      // Skip updating unit for Finance Cost items (they use customs clearance subtotal)
      if (isFinanceCost) {
        console.log('  💰 Finance Cost item - will be updated by updateFinanceCostItems()');
        return;
      }
      
      // FICHE ELECTRONIQUE uses VRAC weight only
      if (isFicheElectronique) {
        unitType = 'weight';
        newUnitValue = vracWeight;
        console.log('  📋 FICHE ELECTRONIQUE → VRAC Weight:', newUnitValue.toFixed(3), 'MT');
      } else if (splitUnit !== null) {
        unitType = 'count';
        newUnitValue = splitUnit;
        console.log('  ✨ Split unit:', itemName.substring(0, 60) + '... → ' + newUnitValue);
      } else {
        unitType = hasUnit ? 'count' : 'weight';
        newUnitValue = hasUnit ? mcaCount : totalWeight;
      }
      
      const currentUnit = parseFloat($(this).val()) || 1;
      
      // Always update unit value
      $(this).val(newUnitValue.toFixed(unitType === 'weight' ? 3 : 0));
      
      if (Math.abs(currentUnit - newUnitValue) > 0.001) {
        console.log('    ↻ Unit changed from', currentUnit, 'to', newUnitValue);
      }
      
      const unit = parseFloat($(this).val()) || 1;
      
      // Handle special items
      if (specialType) {
        const specialSubtotal = calculateSpecialItemSubtotal(specialType);
        
        $row.find('.item-subtotal').val(specialSubtotal.toFixed(2));
        
        const costUsd = unit > 0 ? specialSubtotal / unit : 0;
        $row.find('.item-cost-usd').val(costUsd.toFixed(2));
        
        $row.find('.item-subtotal').addClass('auto-calculated');
        $row.find('.item-cost-usd').addClass('auto-calculated');
        
        const hasTVA = parseInt($row.data('has-tva')) || 0;
        const tva = hasTVA === 1 ? specialSubtotal * 0.16 : 0;
        const total = specialSubtotal + tva;
        
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      } else {
        // Regular item
        const baseSubtotal = parseFloat($row.data('base-subtotal')) || 0;
        const baseTVA = parseFloat($row.data('base-tva')) || 0;
        const baseTotal = parseFloat($row.data('base-total')) || 0;
        
        const subtotal = baseSubtotal * unit;
        const tva = baseTVA * unit;
        const total = baseTotal * unit;
        
        $row.find('.item-subtotal').val(subtotal.toFixed(2));
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      }
    });
    
    console.log('✅ ========== UNIT UPDATE COMPLETE ==========\n');
    
    recalculateTotals();
  }

  // Filter cards click
  $('.filter-card').on('click', function() {
    $('.filter-card').removeClass('active-filter');
    $(this).addClass('active-filter');
    currentFilter = $(this).data('filter');
    $('#clearFilterBtn').toggle(currentFilter !== 'all');
    if (invoicesTable) invoicesTable.ajax.reload();
  });

  $('#clearFilterBtn').on('click', function() {
    $('.filter-card').removeClass('active-filter');
    $('.filter-card[data-filter="all"]').addClass('active-filter');
    currentFilter = 'all';
    $(this).hide();
    if (invoicesTable) invoicesTable.ajax.reload();
  });

  window.exportAllDebitNotes = function() {
    window.location.href = CONTROLLER_URL + '/crudData/exportAllDebitNotes';
    Swal.fire({icon: 'success', title: 'Exporting Debit Notes...', timer: 1500, showConfirmButton: false});
  };

  window.exportAllInvoices = function() {
    window.location.href = CONTROLLER_URL + '/crudData/exportAllInvoices';
    Swal.fire({icon: 'success', title: 'Exporting Invoices...', timer: 1500, showConfirmButton: false});
  };

  $('#client_id').on('change', function() {
    const clientId = $(this).val();
    
    if (!isEditMode) {
      $('#license_id').html('<option value="">-- Select License --</option>');
      $('#quotationSelector').html('<option value="">-- Select Client First --</option>');
      $('#invoice_ref').val('').attr('placeholder', 'Auto-generated');
      clearMCATable();
      clearQuotationItems();
      allQuotationsData = [];
      updateMCACountBadge();
    }
    
    if (!clientId) return;
    
    if (!isEditMode) {
      $.ajax({
        url: CONTROLLER_URL + '/crudData/getNextInvoiceRefForClient',
        method: 'GET',
        data: { client_id: clientId },
        dataType: 'json',
        success: function(res) {
          if (res.success) $('#invoice_ref').val(res.invoice_ref);
        }
      });
    }

    $.ajax({
      url: CONTROLLER_URL + '/crudData/getLicenses',
      method: 'GET',
      data: { client_id: clientId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          let options = '<option value="">-- Select License --</option>';
          res.data.forEach(function(license) {
            options += `<option value="${license.id}">${escapeHtml(license.license_number)}</option>`;
          });
          $('#license_id').html(options);
        }
      }
    });

    loadAllQuotations(clientId);
  });

  function loadAllQuotations(clientId) {
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getAllQuotationsForClient',
      method: 'GET',
      data: { client_id: clientId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          allQuotationsData = res.data;
          
          let options = '<option value="">-- Auto-selected after MCA --</option>';
          res.data.forEach(function(quot) {
            options += `<option value="${quot.id}" 
                          data-kind-id="${quot.kind_id || ''}" 
                          data-goods-type-id="${quot.goods_type_id || ''}" 
                          data-transport-mode-id="${quot.transport_mode_id || ''}">${escapeHtml(quot.quotation_ref)}</option>`;
          });
          $('#quotationSelector').html(options);
        } else {
          $('#quotationSelector').html('<option value="">-- No Quotations Available --</option>');
        }
      }
    });
  }

  $('#license_id').on('change', function() {
    const clientId = $('#client_id').val();
    const licenseId = $(this).val();
    
    if (!isEditMode) {
      clearMCATable();
      updateMCACountBadge();
    }
    
    if (!clientId || !licenseId) return;

    $.ajax({
      url: CONTROLLER_URL + '/crudData/getMCAReferences',
      method: 'GET',
      data: { client_id: clientId, license_id: licenseId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          displayMCATable(res.data);
        } else {
          $('#mcaTableBody').html('<tr><td colspan="24" class="text-center" style="padding: 30px; font-size: 0.75rem;">No available MCA references (all may be invoiced)</td></tr>');
        }
      }
    });
  });

  // ========== Display MCA Table ==========
  function displayMCATable(mcaList) {
    let html = '';
    
    mcaList.forEach((mca, index) => {
      const mcaRef = escapeHtml(mca.mca_ref || '');
      const feetContainerId = mca.feet_container || '';
      const feetContainerSize = escapeHtml(mca.feet_container_size || '');
      const hasContainer = mca.container && mca.container.trim() !== '';
      const liquidationAmount = mca.liquidation_amount || 0;
      
      html += '<tr data-mca-index="' + index + '" data-has-container="' + (hasContainer ? '1' : '0') + '" data-feet-container-size="' + feetContainerSize + '">';
      html += '<td class="text-center">';
      html += '<input type="checkbox" class="mca-checkbox" data-mca-id="' + mca.id + '">';
      html += '</td>';
      html += '<td><a href="#" class="mca-ref-link" data-mca-id="' + mca.id + '">' + mcaRef + '</a></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="lot_number" placeholder="Lot Number"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="declaration_no" placeholder="Declaration No"></td>';
      html += '<td><input type="date" class="form-control form-control-sm mca-field" data-field="declaration_date"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="liquidation_no" placeholder="Liquidation No"></td>';
      html += '<td><input type="date" class="form-control form-control-sm mca-field" data-field="liquidation_date"></td>';
      
      // Liquidation CDF
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="liquidation_amount" placeholder="0.00" step="0.01" value="' + liquidationAmount + '"></td>';
      
      // Liquidation USD (auto-calculated)
      html += '<td><input type="number" class="form-control form-control-sm mca-field auto-calculated-field" data-field="liquidation_usd" placeholder="0.00" step="0.01" value="0.00" readonly title="Auto-calculated: Liquidation CDF / BCC Rate"></td>';
      
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="quittance_no" placeholder="Quittance No"></td>';
      html += '<td><input type="date" class="form-control form-control-sm mca-field" data-field="quittance_date"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="horse" placeholder="Horse"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="trailer_1" placeholder="Trailer 1"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="trailer_2" placeholder="Trailer 2"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="container" placeholder="Container"></td>';
      
      html += '<td>';
      html += '<input type="text" class="form-control form-control-sm mca-field" data-field="feet_container_size" value="' + feetContainerSize + '" placeholder="e.g. 20">';
      html += '<input type="hidden" class="mca-field" data-field="feet_container_id" value="' + feetContainerId + '">';
      html += '</td>';
      
      html += '<td><input type="number" class="form-control form-control-sm mca-field weight-field" data-field="weight" placeholder="0.000" step="0.001" min="0"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field bcc-rate-field" data-field="bcc_rate" placeholder="2500.00" step="0.01" value="2500.00"></td>';
      html += '<td><input type="text" class="form-control form-control-sm mca-field" data-field="buyer" placeholder="Buyer"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="ceec_amount" placeholder="0.00" step="0.01" value="0.00"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="cgea_amount" placeholder="0.00" step="0.01" value="0.00"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="occ_amount" placeholder="0.00" step="0.01" value="0.00"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="lmc_amount" placeholder="0.00" step="0.01" value="0.00"></td>';
      html += '<td><input type="number" class="form-control form-control-sm mca-field" data-field="ogefrem_amount" placeholder="0.00" step="0.01" value="0.00"></td>';
      html += '</tr>';
    });
    
    $('#mcaTableBody').html(html);
    applyContainerConditionalHiding();
  }

  // ========== EVENT LISTENERS ==========
  
// ========== MCA CHECKBOX CHANGE - COMPLETE FIX ==========
$(document).on('change', '.mca-checkbox', function() {
  const $row = $(this).closest('tr');
  const mcaId = $(this).data('mca-id');
  const isFirstSelection = $('.mca-checkbox:checked').length === 1;
  
  if ($(this).is(':checked')) {
    $row.addClass('selected');
    
    // ========== FETCH MCA DETAILS FOR EVERY SELECTED MCA ==========
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getMCADetails',
      method: 'GET',
      data: { mca_id: mcaId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          const mcaData = res.data;
          
          console.log('✅ MCA Data fetched for MCA ID:', mcaId, mcaData);
          
          // ========== POPULATE INVOICE HEADER FIELDS (ONLY FOR FIRST MCA) ==========
          if (!isEditMode && isFirstSelection) {
            if (mcaData.kind_id && mcaData.kind_name) {
              $('#kind_id').val(mcaData.kind_id);
              $('#kind_name').val(mcaData.kind_name);
            }
            
            if (mcaData.goods_type_id && mcaData.type_of_goods_name) {
              $('#goods_type_id').val(mcaData.goods_type_id);
              $('#goods_type_name').val(mcaData.type_of_goods_name);
            }
            
            if (mcaData.transport_mode_id && mcaData.transport_mode_name) {
              $('#transport_mode_id').val(mcaData.transport_mode_id);
              $('#transport_mode_name').val(mcaData.transport_mode_name);
            }
            
            // Auto-match quotation only for first MCA
            autoMatchQuotation(mcaData.kind_id, mcaData.goods_type_id, mcaData.transport_mode_id);
          }
          
          // ========== POPULATE MCA ROW FIELDS (FOR ALL SELECTED MCAs) ==========
          
          // Lot Number
          if (mcaData.lot_number) {
            $row.find('[data-field="lot_number"]').val(mcaData.lot_number);
          }
          
          // Declaration Details
          if (mcaData.declaration_no) {
            $row.find('[data-field="declaration_no"]').val(mcaData.declaration_no);
          }
          if (mcaData.declaration_date) {
            $row.find('[data-field="declaration_date"]').val(mcaData.declaration_date);
          }
          
          // Liquidation Details
          if (mcaData.liquidation_no) {
            $row.find('[data-field="liquidation_no"]').val(mcaData.liquidation_no);
          }
          if (mcaData.liquidation_date) {
            $row.find('[data-field="liquidation_date"]').val(mcaData.liquidation_date);
          }
          if (mcaData.liquidation_amount) {
            $row.find('[data-field="liquidation_amount"]').val(parseFloat(mcaData.liquidation_amount).toFixed(2));
          }
          
          // Quittance Details
          if (mcaData.quittance_no) {
            $row.find('[data-field="quittance_no"]').val(mcaData.quittance_no);
          }
          if (mcaData.quittance_date) {
            $row.find('[data-field="quittance_date"]').val(mcaData.quittance_date);
          }
          
          // Vehicle Details
          if (mcaData.horse) {
            $row.find('[data-field="horse"]').val(mcaData.horse);
          }
          if (mcaData.trailer_1) {
            $row.find('[data-field="trailer_1"]').val(mcaData.trailer_1);
          }
          if (mcaData.trailer_2) {
            $row.find('[data-field="trailer_2"]').val(mcaData.trailer_2);
          }
          
          // Container Details
          if (mcaData.container) {
            $row.find('[data-field="container"]').val(mcaData.container);
            $row.attr('data-has-container', '1');
          }
          if (mcaData.feet_container_size) {
            $row.find('[data-field="feet_container_size"]').val(mcaData.feet_container_size);
            $row.attr('data-feet-container-size', mcaData.feet_container_size);
          }
          
          // Weight
          if (mcaData.weight) {
            $row.find('[data-field="weight"]').val(parseFloat(mcaData.weight).toFixed(3));
          }
          
          // Buyer
          if (mcaData.buyer) {
            $row.attr('data-buyer', mcaData.buyer);
          }
          
          // ========== DUTY AMOUNTS ==========
          $row.find('[data-field="ceec_amount"]').val(parseFloat(mcaData.ceec_amount || 0).toFixed(2));
          $row.find('[data-field="cgea_amount"]').val(parseFloat(mcaData.cgea_amount || 0).toFixed(2));
          $row.find('[data-field="occ_amount"]').val(parseFloat(mcaData.occ_amount || 0).toFixed(2));
          $row.find('[data-field="lmc_amount"]').val(parseFloat(mcaData.lmc_amount || 0).toFixed(2));
          $row.find('[data-field="ogefrem_amount"]').val(parseFloat(mcaData.ogefrem_amount || 0).toFixed(2));
          
          // ========== BCC RATE ==========
          if (!$row.find('[data-field="bcc_rate"]').val() || $row.find('[data-field="bcc_rate"]').val() === '0') {
            $row.find('[data-field="bcc_rate"]').val('2500.00');
          }
          
          // ========== CALCULATE LIQUIDATION USD ==========
          calculateLiquidationUSD($row);
          
          console.log('✅ MCA row populated successfully for MCA ID:', mcaId);
        }
      },
      error: function(xhr) {
        console.error('❌ Failed to fetch MCA details for MCA ID:', mcaId, xhr);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to load MCA details from database for MCA ID: ' + mcaId,
          confirmButtonColor: '#dc3545'
        });
      }
    });
  } else {
    // Checkbox unchecked
    $row.removeClass('selected');
  }
  
  applyContainerConditionalHiding();
  updateMCACountBadge();
  
  // Update FSR when MCA selection changes
  setTimeout(function() {
    updateFSRItems();
    recalculateTotals();
  }, 100);
});
  
  // MCA Ref Link click
  $(document).on('click', '.mca-ref-link', function(e) {
    e.preventDefault();
    const mcaId = $(this).data('mca-id');
    const $checkbox = $(this).closest('tr').find('.mca-checkbox');
    
    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
  });

  // ========== Liquidation CDF changes - auto-calculate Liquidation USD ==========
  $(document).on('input change keyup blur', '[data-field="liquidation_amount"]', function() {
    const $row = $(this).closest('tr');
    calculateLiquidationUSD($row);
    
    // Update FSR when liquidation amount changes
    setTimeout(function() {
      updateFSRItems();
      recalculateTotals();
    }, 100);
  });

  // ========== BCC Rate changes - recalculate Liquidation USD ==========
  $(document).on('input change keyup blur', '.bcc-rate-field, [data-field="bcc_rate"]', function() {
    const $row = $(this).closest('tr');
    calculateLiquidationUSD($row);
    
    // Update FSR when BCC rate changes
    setTimeout(function() {
      updateFSRItems();
      recalculateTotals();
    }, 100);
  });

  // ========== Weight field changes ==========
  $(document).on('input change keyup blur', '.weight-field, [data-field="weight"]', function() {
    const $input = $(this);
    const weight = $input.val();
    
    console.log('\n⚖️ ========== WEIGHT CHANGED ==========');
    console.log('New weight value:', weight);
    
    // Clear any existing timeout
    if (window.weightChangeTimeout) {
      clearTimeout(window.weightChangeTimeout);
    }
    
    // Set new timeout to trigger recalculation
    window.weightChangeTimeout = setTimeout(function() {
      const count = $('.mca-checkbox:checked').length;
      const totalWeight = calculateTotalWeight();
      
      console.log('🔄 Triggering recalculation...');
      console.log('  MCA Count:', count);
      console.log('  Total Weight:', totalWeight.toFixed(3), 'MT');
      
      updateAllItemUnitsAndSpecialItems(count, totalWeight);
      updateMCACountBadge();
      
      // Update Finance Cost and FSR items
      setTimeout(function() {
        updateFinanceCostItems();
        updateFSRItems();
        recalculateTotals();
      }, 50);
      
      console.log('========== WEIGHT CHANGE COMPLETE ==========\n');
    }, 150);
  });

  // Vehicle/Trailer fields
  $(document).on('input change', '[data-field="horse"], [data-field="trailer_1"], [data-field="trailer_2"]', function() {
    setTimeout(function() {
      const count = $('.mca-checkbox:checked').length;
      const totalWeight = calculateTotalWeight();
      
      updateAllItemUnitsAndSpecialItems(count, totalWeight);
      updateMCACountBadge();
      
      // Update FSR when vehicle/trailer changes (affects RLS and LSE)
      setTimeout(function() {
        updateFSRItems();
        recalculateTotals();
      }, 50);
    }, 50);
  });

  // Container field
  $(document).on('input change', '[data-field="container"]', function() {
    const $row = $(this).closest('tr');
    const containerValue = $(this).val().trim();
    
    if (containerValue !== '') {
      $row.attr('data-has-container', '1');
    } else {
      $row.attr('data-has-container', '0');
    }
    
    applyContainerConditionalHiding();
  });

  // Feet Container Size field
  $(document).on('input change blur', '[data-field="feet_container_size"]', function() {
    const $row = $(this).closest('tr');
    const feetSizeValue = $(this).val().trim();
    
    console.log('\n📦 Feet container size changed to:', feetSizeValue);
    
    if (feetSizeValue !== '') {
      $row.attr('data-feet-container-size', feetSizeValue);
    } else {
      $row.attr('data-feet-container-size', '');
    }
    
    setTimeout(function() {
      const count = $('.mca-checkbox:checked').length;
      const totalWeight = calculateTotalWeight();
      
      console.log('🔄 Recalculating OGEFREM items and VRAC weight');
      
      updateAllItemUnitsAndSpecialItems(count, totalWeight);
      applyContainerConditionalHiding();
    }, 150);
  });

  // ========== Apply conditional hiding ==========
  function applyContainerConditionalHiding() {
    let hasVRAC = false;
    let feetContainerSizes = [];
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const feetSize = ($row.attr('data-feet-container-size') || '').toUpperCase().trim();
      
      if (feetSize === 'VRAC' || feetSize === 'V RAC' || feetSize.replace(/\s/g, '') === 'VRAC') {
        hasVRAC = true;
      }
      
      if (feetSize !== '') {
        const sizeMatch = feetSize.match(/\d+/);
        if (sizeMatch) {
          feetContainerSizes.push(sizeMatch[0]);
        }
      }
    });
    
    $('.quotation-item-row').each(function() {
      const itemName = $(this).find('.item-desc').val();
      
      if (isFicheElectroniqueItem(itemName)) {
        if (hasVRAC) {
          $(this).show();
        } else {
          $(this).hide();
        }
      }
      
      const upperItemName = itemName.toUpperCase();
      if (upperItemName.includes('OGEFREM') && upperItemName.includes('CONTENEUR')) {
        let itemContainerSize = null;
        
        const matches = upperItemName.match(/CONTENEUR\s*(\d+)/);
        if (matches && matches[1]) {
          itemContainerSize = matches[1];
        }
        
        if (feetContainerSizes.length > 0) {
          if (itemContainerSize) {
            if (feetContainerSizes.includes(itemContainerSize)) {
              $(this).show();
            } else {
              $(this).hide();
            }
          } else {
            $(this).hide();
          }
        } else if (hasVRAC) {
          $(this).hide();
        } else {
          $(this).show();
        }
      }
    });
    
    // Update Finance Cost and FSR items after hiding/showing
    setTimeout(function() {
      updateFinanceCostItems();
      updateFSRItems();
      recalculateTotals();
    }, 50);
  }

  function autoMatchQuotation(kindId, goodsTypeId, transportModeId) {
    if (allQuotationsData.length === 0) {
      $('#quotationRefDisplay').val('No quotations available');
      $('#quotationSelector').val('');
      return;
    }
    
    // First try: exact match on all three criteria
    let matchedQuotation = allQuotationsData.find(quot => {
      return parseInt(quot.kind_id) === parseInt(kindId) &&
             parseInt(quot.goods_type_id) === parseInt(goodsTypeId) &&
             parseInt(quot.transport_mode_id) === parseInt(transportModeId);
    });
    
    // Second try: match on kind and transport mode only
    if (!matchedQuotation) {
      matchedQuotation = allQuotationsData.find(quot => {
        return parseInt(quot.kind_id) === parseInt(kindId) &&
               parseInt(quot.transport_mode_id) === parseInt(transportModeId);
      });
    }
    
    // Third try: match on kind only
    if (!matchedQuotation) {
      matchedQuotation = allQuotationsData.find(quot => {
        return parseInt(quot.kind_id) === parseInt(kindId);
      });
    }
    
    // Last resort: take the most recent quotation
    if (!matchedQuotation && allQuotationsData.length > 0) {
      matchedQuotation = allQuotationsData[0];
    }
    
    if (matchedQuotation) {
      $('#quotationSelector').val(matchedQuotation.id);
      $('#quotationRefDisplay').val(matchedQuotation.quotation_ref);
      
      const clientId = $('#client_id').val();
      getQuotationItems(matchedQuotation.id, clientId);
      
      console.log('✅ Auto-matched quotation:', matchedQuotation.quotation_ref);
    } else {
      $('#quotationRefDisplay').val('No matching quotation found');
      $('#quotationSelector').val('');
      clearQuotationItems();
    }
  }

  function clearMCATable() {
    $('#mcaTableBody').html('<tr><td colspan="24" class="text-center" style="padding: 30px; font-size: 0.75rem;">Select a Client and License to load MCA references</td></tr>');
  }

  $('#quotationSelector').on('change', function() {
    const quotationId = $(this).val();
    const clientId = $('#client_id').val();
    
    $('#quotation_id').val(quotationId);
    
    if (!quotationId || !clientId) {
      clearQuotationItems();
      return;
    }

    if (editModeDataLoaded) {
      getQuotationItems(quotationId, clientId);
    } else if (!isEditMode) {
      getQuotationItems(quotationId, clientId);
    }
  });
  
  function getQuotationItems(quotationId, clientId) {
    $('#quotation_id').val(quotationId);
    
    $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="spinner-border spinner-border-sm me-2"></i><div style="margin-top: 8px; color: #667eea; font-weight: 600; font-size: 0.75rem;">Loading quotation items...</div></td></tr>');
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getQuotationItems',
      method: 'GET',
      data: { quotation_id: quotationId, client_id: clientId },
      dataType: 'json',
      success: function(itemsRes) {
        if (itemsRes.success) {
          quotationData = itemsRes.quotation;
          
          if (quotationData) {
            $('#quotation_sub_total').val(quotationData.sub_total || 0);
            $('#quotation_vat_amount').val(quotationData.vat_amount || 0);
            $('#quotation_total_amount').val(quotationData.total_amount || 0);
          }
          
          if (itemsRes.categorized_items && itemsRes.categorized_items.length > 0) {
            displayQuotationItemsByCategory(itemsRes.categorized_items);
          } else if (itemsRes.items && itemsRes.items.length > 0) {
            displayQuotationItems(itemsRes.items);
          } else {
            $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem; color: #f39c12;"></i><div style="margin-top: 8px; color: #f39c12; font-weight: 600; font-size: 0.75rem;">No items found in quotation</div></td></tr>');
            $('#quotationItemsFooter').hide();
          }
        } else {
          $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="ti ti-x-circle me-2" style="font-size: 1.5rem; color: #e74c3c;"></i><div style="margin-top: 8px; color: #e74c3c; font-weight: 600; font-size: 0.75rem;">' + (itemsRes.message || 'Error loading items') + '</div></td></tr>');
          $('#quotationItemsFooter').hide();
        }
      },
      error: function() {
        $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="ti ti-x-circle me-2" style="font-size: 1.5rem; color: #e74c3c;"></i><div style="margin-top: 8px; color: #e74c3c; font-weight: 600; font-size: 0.75rem;">Error loading quotation items</div></td></tr>');
        $('#quotationItemsFooter').hide();
      }
    });
  }

  function displayQuotationItems(items) {
    quotationItemsData = items;
    originalQuotationItems = JSON.parse(JSON.stringify(items));
    
    let html = '';
    let subtotal = 0, tva = 0, total = 0;
    
    $('.quotation-items-table thead').show();
    
    const mcaCount = $('.mca-checkbox:checked').length || 1;
    const totalWeight = calculateTotalWeight();
    const vracWeight = calculateVRACWeight();
    
    items.forEach((item, index) => {
      const desc = escapeHtml(item.item_name || 'N/A');
      const costUsd = parseFloat(item.cost_usd || item.taux_usd || 0);
      const baseSubtotal = parseFloat(item.subtotal_usd || 0);
      const baseTVA = parseFloat(item.tva_usd || 0);
      const baseTotal = parseFloat(item.total_usd || 0);
      
      const unitId = item.unit_id;
      const hasUnit = unitId && unitId !== '' && unitId !== 'null' && unitId !== null;
      const unitType = hasUnit ? 'count' : 'weight';
      
      const specialType = isSpecialItem(desc);
      const isFinanceCostItem = isFinanceCost1PercentItem(desc);
      const isFicheElectronique = isFicheElectroniqueItem(desc);
      
      // Determine unit value based on item type
      let unitValue;
      if (isFicheElectronique) {
        unitValue = vracWeight;
      } else {
        unitValue = hasUnit ? mcaCount : totalWeight;
      }
      
      const unitLabel = hasUnit ? 'Files' : 'MT';
      const unitStep = hasUnit ? '1' : '0.001';
      
      let itemSubtotal, itemTVA, itemTotal;
      let displayCostUsd = costUsd;
      let displayUnit = unitValue;
      
      if (specialType) {
        itemSubtotal = calculateSpecialItemSubtotal(specialType);
        itemTVA = parseInt(item.has_tva) === 1 ? itemSubtotal * 0.16 : 0;
        itemTotal = itemSubtotal + itemTVA;
      } else {
        itemSubtotal = baseSubtotal * unitValue;
        itemTVA = baseTVA * unitValue;
        itemTotal = baseTotal * unitValue;
      }
      
      // Special handling for Finance Cost 1% item - unit will be set after all items load
      if (isFinanceCostItem) {
        displayCostUsd = '1%';
        displayUnit = 0; // Will be calculated after all items load
      }
      
      subtotal += itemSubtotal;
      tva += itemTVA;
      total += itemTotal;
      
      html += '<tr class="quotation-item-row" data-item-index="' + index + '" ';
      html += 'data-quotation-item-id="' + (item.id || '') + '" ';
      html += 'data-item-id="' + (item.item_id || '') + '" ';
      html += 'data-category-id="' + (item.category_id || 0) + '" ';
      html += 'data-category-name="' + escapeHtml(item.category_name || 'UNCATEGORIZED') + '" ';
      html += 'data-category-header="' + escapeHtml(item.category_header || item.category_name || 'UNCATEGORIZED') + '" ';
      html += 'data-display-order="' + (item.display_order || 999) + '" ';
      html += 'data-unit-type="' + unitType + '" ';
      html += 'data-unit-id="' + (item.unit_id || '') + '" ';
      html += 'data-currency-id="' + (item.currency_id || '') + '" ';
      html += 'data-has-tva="' + (item.has_tva || 0) + '" ';
      html += 'data-base-subtotal="' + baseSubtotal + '" ';
      html += 'data-base-tva="' + baseTVA + '" ';
      html += 'data-base-total="' + baseTotal + '" ';
      html += 'data-is-finance-cost="' + (isFinanceCostItem ? '1' : '0') + '" ';
      html += 'data-is-fiche-electronique="' + (isFicheElectronique ? '1' : '0') + '">';
      
      html += '<td><input type="text" class="form-control form-control-sm item-desc" data-item-id="' + index + '" value="' + desc + '"></td>';
      html += '<td>';
      
      if (isFinanceCostItem) {
        html += '  <input type="text" class="form-control form-control-sm item-unit editable-field" ';
        html += '         data-item-id="' + index + '" ';
        html += '         value="0.00" ';
        html += '         readonly ';
        html += '         placeholder="Customs Subtotal" ';
        html += '         title="Customs Clearance Fees Subtotal" ';
        html += '         style="text-align: center; background: #fff3cd !important;">';
      } else if (isFicheElectronique) {
        html += '  <input type="number" class="form-control form-control-sm item-unit editable-field" ';
        html += '         data-item-id="' + index + '" ';
        html += '         value="' + unitValue.toFixed(3) + '" ';
        html += '         min="0.001" ';
        html += '         step="0.001" ';
        html += '         placeholder="VRAC Weight" ';
        html += '         title="VRAC Container Weight Only" ';
        html += '         style="text-align: center; background: #e3f2fd !important;">';
      } else {
        html += '  <input type="number" class="form-control form-control-sm item-unit editable-field" ';
        html += '         data-item-id="' + index + '" ';
        html += '         value="' + unitValue.toFixed(unitType === 'weight' ? 3 : 0) + '" ';
        html += '         min="' + (unitType === 'weight' ? '0.001' : '1') + '" ';
        html += '         step="' + unitStep + '" ';
        html += '         placeholder="' + unitLabel + '" ';
        html += '         title="' + (unitType === 'weight' ? 'Editable - Based on weight' : 'Editable - Based on file count') + '" ';
        html += '         style="text-align: center;">';
      }
      
      html += '</td>';
      
      if (isFinanceCostItem) {
        html += '<td><input type="text" class="form-control form-control-sm item-cost-usd" data-item-id="' + index + '" value="1%" readonly style="text-align: right; background: #fff3cd !important; font-weight: 700;"></td>';
      } else {
        html += '<td><input type="number" class="form-control form-control-sm item-cost-usd editable-field" data-item-id="' + index + '" value="' + costUsd.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
      }
      
      html += '<td><input type="number" class="form-control form-control-sm item-subtotal editable-field" data-item-id="' + index + '" value="' + itemSubtotal.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
      html += '<td><input type="number" class="form-control form-control-sm item-tva editable-field" data-item-id="' + index + '" value="' + itemTVA.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
      html += '<td><input type="number" class="form-control form-control-sm item-total editable-field" data-item-id="' + index + '" value="' + itemTotal.toFixed(2) + '" style="text-align: right;"></td>';
      html += '</tr>';
    });
    
    $('#quotationItemsBody').html(html);
    $('#footerSubtotal').text(subtotal.toFixed(2));
    $('#footerTVA').text(tva.toFixed(2));
    $('#footerTotal').text(total.toFixed(2));
    $('#quotationItemsFooter').show();
    
    updateAllItemUnitsAndSpecialItems(mcaCount, totalWeight);
    
    // IMPORTANT: Update Finance Cost and FSR items after all items are loaded
    setTimeout(function() {
      updateFinanceCostItems();
      updateFSRItems();
      applyContainerConditionalHiding();
      recalculateTotals();
    }, 100);
    
    attachQuotationItemCalculators();
  }

  function displayQuotationItemsByCategory(categorizedItems) {
    quotationItemsData = [];
    originalQuotationItems = [];
    let html = '';
    let grandSubtotal = 0, grandTVA = 0, grandTotal = 0;
    
    if (!categorizedItems || categorizedItems.length === 0) {
      clearQuotationItems();
      return;
    }
    
    $('.quotation-items-table thead').hide();
    let itemCounter = 0;
    
    const mcaCount = $('.mca-checkbox:checked').length || 1;
    const totalWeight = calculateTotalWeight();
    const vracWeight = calculateVRACWeight();
    
    categorizedItems.forEach((category, catIndex) => {
      if (!category.items || category.items.length === 0) return;
      
      const categoryName = escapeHtml(category.category_header || category.category_name || 'CATEGORY');
      const categoryId = category.category_id || 0;
      const displayOrder = category.display_order || 999;
      
      html += '<tr class="category-header-row">';
      html += '  <td colspan="6" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 700; padding: 10px 12px; text-transform: uppercase; font-size: 0.75rem;">' + categoryName + '</td>';
      html += '</tr>';
      
      html += '<tr style="background: #34495e; color: white;">';
      html += '  <th style="width: 38%; padding: 8px; font-size: 0.7rem;">Description</th>';
      html += '  <th style="width: 8%; padding: 8px; text-align: center; font-size: 0.7rem;">Unit</th>';
      html += '  <th style="width: 11%; padding: 8px; text-align: right; font-size: 0.7rem;">Cost/USD</th>';
      html += '  <th style="width: 12%; padding: 8px; text-align: right; font-size: 0.7rem;">Subtotal USD</th>';
      html += '  <th style="width: 12%; padding: 8px; text-align: right; font-size: 0.7rem;">TVA 16%</th>';
      html += '  <th style="width: 12%; padding: 8px; text-align: right; font-size: 0.7rem;">Total USD</th>';
      html += '</tr>';
      
      let categorySubtotal = 0, categoryTVA = 0, categoryTotal = 0;
      
      category.items.forEach((item, itemIndexInCategory) => {
        const itemId = itemCounter++;
        
        originalQuotationItems.push(JSON.parse(JSON.stringify(item)));
        quotationItemsData.push(item);
        
        const unitId = item.unit_id;
        const hasUnit = unitId && unitId !== '' && unitId !== 'null' && unitId !== null;
        
        const unitType = hasUnit ? 'count' : 'weight';
        const unitLabel = hasUnit ? 'Files' : 'MT';
        const unitStep = hasUnit ? '1' : '0.001';
        const unitMin = hasUnit ? '1' : '0.001';
        
        const desc = escapeHtml(item.item_name || '');
        const costUsd = parseFloat(item.cost_usd || item.taux_usd || 0);
        const baseSubtotal = parseFloat(item.subtotal_usd || 0);
        const baseTVA = parseFloat(item.tva_usd || 0);
        const baseTotal = parseFloat(item.total_usd || 0);
        
        const specialType = isSpecialItem(desc);
        const isFinanceCostItem = isFinanceCost1PercentItem(desc);
        const isFicheElectronique = isFicheElectroniqueItem(desc);
        
        // Determine unit value based on item type
        let unitValue;
        if (isFicheElectronique) {
          unitValue = vracWeight;
        } else {
          unitValue = hasUnit ? mcaCount : totalWeight;
        }
        
        let itemSubtotal, itemTVA, itemTotal;
        let displayCostUsd = costUsd;
        let displayUnit = unitValue;
        
        if (specialType) {
          itemSubtotal = calculateSpecialItemSubtotal(specialType);
          itemTVA = parseInt(item.has_tva) === 1 ? itemSubtotal * 0.16 : 0;
          itemTotal = itemSubtotal + itemTVA;
        } else {
          itemSubtotal = baseSubtotal * unitValue;
          itemTVA = baseTVA * unitValue;
          itemTotal = baseTotal * unitValue;
        }
        
        // Special handling for Finance Cost 1% item - unit will be set after all items load
        if (isFinanceCostItem) {
          displayCostUsd = '1%';
          displayUnit = 0; // Will be calculated after all items load
        }
        
        categorySubtotal += itemSubtotal;
        categoryTVA += itemTVA;
        categoryTotal += itemTotal;
        
        html += '<tr class="quotation-item-row" data-item-index="' + itemId + '" ';
        html += 'data-quotation-item-id="' + (item.id || '') + '" ';
        html += 'data-item-id="' + (item.item_id || '') + '" ';
        html += 'data-category-id="' + categoryId + '" ';
        html += 'data-category-index="' + catIndex + '" ';
        html += 'data-item-index-in-category="' + itemIndexInCategory + '" ';
        html += 'data-category-name="' + escapeHtml(category.category_name || '') + '" ';
        html += 'data-category-header="' + escapeHtml(category.category_header || '') + '" ';
        html += 'data-display-order="' + displayOrder + '" ';
        html += 'data-unit-type="' + unitType + '" ';
        html += 'data-unit-id="' + (item.unit_id || '') + '" ';
        html += 'data-currency-id="' + (item.currency_id || '') + '" ';
        html += 'data-has-tva="' + (item.has_tva || 0) + '" ';
        html += 'data-base-subtotal="' + baseSubtotal + '" ';
        html += 'data-base-tva="' + baseTVA + '" ';
        html += 'data-base-total="' + baseTotal + '" ';
        html += 'data-is-finance-cost="' + (isFinanceCostItem ? '1' : '0') + '" ';
        html += 'data-is-fiche-electronique="' + (isFicheElectronique ? '1' : '0') + '">';
        
        html += '  <td><input type="text" class="form-control form-control-sm item-desc" data-item-id="' + itemId + '" value="' + desc + '"></td>';
        html += '  <td>';
        
        if (isFinanceCostItem) {
          html += '    <input type="text" class="form-control form-control-sm item-unit editable-field" ';
          html += '           data-item-id="' + itemId + '" ';
          html += '           value="0.00" ';
          html += '           readonly ';
          html += '           placeholder="Customs Subtotal" ';
          html += '           title="Customs Clearance Fees Subtotal" ';
          html += '           style="text-align: center; background: #fff3cd !important;">';
        } else if (isFicheElectronique) {
          html += '    <input type="number" class="form-control form-control-sm item-unit editable-field" ';
          html += '           data-item-id="' + itemId + '" ';
          html += '           value="' + unitValue.toFixed(3) + '" ';
          html += '           min="0.001" ';
          html += '           step="0.001" ';
          html += '           placeholder="VRAC Weight" ';
          html += '           title="VRAC Container Weight Only" ';
          html += '           style="text-align: center; background: #e3f2fd !important;">';
        } else {
          html += '    <input type="number" class="form-control form-control-sm item-unit editable-field" ';
          html += '           data-item-id="' + itemId + '" ';
          html += '           value="' + unitValue.toFixed(unitType === 'weight' ? 3 : 0) + '" ';
          html += '           min="' + unitMin + '" ';
          html += '           step="' + unitStep + '" ';
          html += '           placeholder="' + unitLabel + '" ';
          html += '           title="' + (unitType === 'weight' ? 'Editable - Based on weight' : 'Editable - Based on file count') + '" ';
          html += '           style="text-align: center;">';
        }
        
        html += '  </td>';
        
        if (isFinanceCostItem) {
          html += '  <td><input type="text" class="form-control form-control-sm item-cost-usd" data-item-id="' + itemId + '" value="1%" readonly style="text-align: right; background: #fff3cd !important; font-weight: 700;"></td>';
        } else {
          html += '  <td><input type="number" class="form-control form-control-sm item-cost-usd editable-field" data-item-id="' + itemId + '" value="' + costUsd.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
        }
        
        html += '  <td><input type="number" class="form-control form-control-sm item-subtotal editable-field" data-item-id="' + itemId + '" value="' + itemSubtotal.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
        html += '  <td><input type="number" class="form-control form-control-sm item-tva editable-field" data-item-id="' + itemId + '" value="' + itemTVA.toFixed(2) + '" step="0.01" style="text-align: right;"></td>';
        html += '  <td><input type="number" class="form-control form-control-sm item-total editable-field" data-item-id="' + itemId + '" value="' + itemTotal.toFixed(2) + '" style="text-align: right;"></td>';
        html += '</tr>';
      });
      
      html += '<tr class="category-subtotal-row" style="background: #f8f9fa; font-weight: bold;">';
      html += '  <td colspan="3" style="text-align: right; padding: 8px; font-size: 0.72rem;">SUBTOTAL:</td>';
      html += '  <td class="category-subtotal" data-category="' + catIndex + '" style="text-align: right; padding: 8px; font-size: 0.72rem;">' + categorySubtotal.toFixed(2) + '</td>';
      html += '  <td class="category-tva" data-category="' + catIndex + '" style="text-align: right; padding: 8px; font-size: 0.72rem;">' + categoryTVA.toFixed(2) + '</td>';
      html += '  <td class="category-total" data-category="' + catIndex + '" style="text-align: right; padding: 8px; font-size: 0.72rem;">' + categoryTotal.toFixed(2) + '</td>';
      html += '</tr>';
      html += '<tr class="category-spacer"><td colspan="6" style="height: 12px;"></td></tr>';
      
      grandSubtotal += categorySubtotal;
      grandTVA += categoryTVA;
      grandTotal += categoryTotal;
    });
    
    $('#quotationItemsBody').html(html);
    $('#footerSubtotal').text(grandSubtotal.toFixed(2));
    $('#footerTVA').text(grandTVA.toFixed(2));
    $('#footerTotal').text(grandTotal.toFixed(2));
    $('#quotationItemsFooter').show();
    
    updateAllItemUnitsAndSpecialItems(mcaCount, totalWeight);
    
    // IMPORTANT: Update Finance Cost and FSR items after all items are loaded
    setTimeout(function() {
      updateFinanceCostItems();
      updateFSRItems();
      applyContainerConditionalHiding();
      recalculateTotals();
    }, 100);
    
    attachQuotationItemCalculators();
  }

  // ========== Attach calculators for editable fields ==========
  function attachQuotationItemCalculators() {
    $(document).off('input change', '.item-unit, .item-cost-usd, .item-subtotal, .item-tva, .item-total');
    
    $(document).on('input change', '.item-unit', function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      // Skip if Finance Cost item (unit is readonly)
      const isFinanceCost = $row.data('is-finance-cost') === 1;
      if (isFinanceCost) return;
      
      const unit = parseFloat($(this).val()) || 1;
      const itemName = $row.find('.item-desc').val();
      const specialType = isSpecialItem(itemName);
      
      if (specialType) {
        const specialSubtotal = calculateSpecialItemSubtotal(specialType);
        $row.find('.item-subtotal').val(specialSubtotal.toFixed(2));
        
        const costUsd = unit > 0 ? specialSubtotal / unit : 0;
        $row.find('.item-cost-usd').val(costUsd.toFixed(2));
        
        const hasTVA = parseInt($row.data('has-tva')) || 0;
        const tva = hasTVA === 1 ? specialSubtotal * 0.16 : 0;
        const total = specialSubtotal + tva;
        
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      } else {
        const costUsd = parseFloat($row.find('.item-cost-usd').val()) || 0;
        const subtotal = costUsd * unit;
        
        $row.find('.item-subtotal').val(subtotal.toFixed(2));
        
        const hasTVA = parseInt($row.data('has-tva')) || 0;
        const tva = hasTVA === 1 ? subtotal * 0.16 : 0;
        const total = subtotal + tva;
        
        $row.find('.item-tva').val(tva.toFixed(2));
        $row.find('.item-total').val(total.toFixed(2));
      }
      
      recalculateTotals();
    });
    
    $(document).on('input change', '.item-cost-usd', function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      // Skip if Finance Cost item (cost is readonly and shows 1%)
      const isFinanceCost = $row.data('is-finance-cost') === 1;
      if (isFinanceCost) return;
      
      const costUsd = parseFloat($(this).val()) || 0;
      const unit = parseFloat($row.find('.item-unit').val()) || 1;
      const subtotal = costUsd * unit;
      
      $row.find('.item-subtotal').val(subtotal.toFixed(2));
      
      const hasTVA = parseInt($row.data('has-tva')) || 0;
      const tva = hasTVA === 1 ? subtotal * 0.16 : 0;
      const total = subtotal + tva;
      
      $row.find('.item-tva').val(tva.toFixed(2));
      $row.find('.item-total').val(total.toFixed(2));
      
      recalculateTotals();
    });
    
    $(document).on('input change', '.item-subtotal', function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      const subtotal = parseFloat($(this).val()) || 0;
      const isFinanceCost = $row.data('is-finance-cost') === 1;
      const specialType = isSpecialItem($row.find('.item-desc').val());
      
      // Don't update unit for Finance Cost or special items
      if (!isFinanceCost && !specialType) {
        const unit = parseFloat($row.find('.item-unit').val()) || 1;
        const costUsd = unit > 0 ? subtotal / unit : 0;
        $row.find('.item-cost-usd').val(costUsd.toFixed(2));
      }
      
      const hasTVA = parseInt($row.data('has-tva')) || 0;
      const tva = hasTVA === 1 ? subtotal * 0.16 : 0;
      const total = subtotal + tva;
      
      $row.find('.item-tva').val(tva.toFixed(2));
      $row.find('.item-total').val(total.toFixed(2));
      
      recalculateTotals();
      
      // Update Finance Cost and FSR when any subtotal changes
      setTimeout(function() {
        updateFinanceCostItems();
        updateFSRItems();
      }, 50);
    });
    
    $(document).on('input change', '.item-tva', function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      const subtotal = parseFloat($row.find('.item-subtotal').val()) || 0;
      const tva = parseFloat($(this).val()) || 0;
      const total = subtotal + tva;
      
      $row.find('.item-total').val(total.toFixed(2));
      
      recalculateTotals();
    });
    
    $(document).on('input change', '.item-total', function() {
      const itemId = $(this).data('item-id');
      const $row = $(`.quotation-item-row[data-item-index="${itemId}"]`);
      
      if ($row.length === 0) return;
      
      const total = parseFloat($(this).val()) || 0;
      const subtotal = parseFloat($row.find('.item-subtotal').val()) || 0;
      const tva = total - subtotal;
      
      $row.find('.item-tva').val(tva.toFixed(2));
      
      recalculateTotals();
    });
  }

  function recalculateTotals() {
    let grandSubtotal = 0, grandTVA = 0, grandTotal = 0;
    
    if ($('.category-subtotal-row').length > 0) {
      $('.category-subtotal-row').each(function() {
        const $categoryRow = $(this);
        let categorySubtotal = 0, categoryTVA = 0, categoryTotal = 0;
        
        $categoryRow.prevUntil('.category-header-row').filter('.quotation-item-row:visible').each(function() {
          categorySubtotal += parseFloat($(this).find('.item-subtotal').val()) || 0;
          categoryTVA += parseFloat($(this).find('.item-tva').val()) || 0;
          categoryTotal += parseFloat($(this).find('.item-total').val()) || 0;
        });
        
        $categoryRow.find('.category-subtotal').text(categorySubtotal.toFixed(2));
        $categoryRow.find('.category-tva').text(categoryTVA.toFixed(2));
        $categoryRow.find('.category-total').text(categoryTotal.toFixed(2));
        
        grandSubtotal += categorySubtotal;
        grandTVA += categoryTVA;
        grandTotal += categoryTotal;
      });
    } else {
      $('.quotation-item-row:visible').each(function() {
        grandSubtotal += parseFloat($(this).find('.item-subtotal').val()) || 0;
        grandTVA += parseFloat($(this).find('.item-tva').val()) || 0;
        grandTotal += parseFloat($(this).find('.item-total').val()) || 0;
      });
    }
    
    $('#footerSubtotal').text(grandSubtotal.toFixed(2));
    $('#footerTVA').text(grandTVA.toFixed(2));
    $('#footerTotal').text(grandTotal.toFixed(2));
  }

  function clearQuotationItems() {
    quotationItemsData = [];
    originalQuotationItems = [];
    quotationData = null;
    $('#quotation_id, #quotation_sub_total, #quotation_vat_amount, #quotation_total_amount').val('');
    $('#quotationSelector').val('');
    $('#quotationRefDisplay').val('Select Client and MCA to auto-load quotation');
    $('.quotation-items-table thead').show();
    $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="ti ti-info-circle me-2" style="font-size: 1.5rem; color: #9ca3af;"></i><div style="margin-top: 8px; color: #6b7280; font-weight: 600; font-size: 0.75rem;">Select a quotation to display items</div></td></tr>');
    $('#quotationItemsFooter').hide();
  }

  function validateForm() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    
    let errors = [];
    
    if (!$('#client_id').val()) {
      $('#client_id').addClass('is-invalid');
      $('#client_id_error').text('Client is required').show();
      errors.push('Client is required');
    }
    
    if (!$('#license_id').val()) {
      $('#license_id').addClass('is-invalid');
      $('#license_id_error').text('License Number is required').show();
      errors.push('License Number is required');
    }
    
    if (!$('#invoice_ref').val()) {
      $('#invoice_ref').addClass('is-invalid');
      $('#invoice_ref_error').text('Invoice Reference is required').show();
      errors.push('Invoice Reference is required');
    }
    
    if ($('.mca-checkbox:checked').length === 0) {
      errors.push('Please select at least one MCA Reference');
      Swal.fire({
        icon: 'warning',
        title: 'MCA Reference Required',
        text: 'Please select at least one MCA reference',
        confirmButtonColor: '#667eea'
      });
    }

    return { isValid: errors.length === 0, errors };
  }

function resetForm() {
    isEditMode = false;
    editModeDataLoaded = false;
    $('#invoiceForm')[0].reset();
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
    $('#invoice_id').val('');
    $('#formAction').val('insert');
    $('#formTitle').text('New Export Invoice');
    $('#submitBtnText').text('Save Invoice');
    $('#quotationSelector').html('<option value="">-- Select Client First --</option>');
    $('#invoice_ref').val('').attr('placeholder', 'Auto-generated');
    $('#invoice_date').val('<?= date('Y-m-d') ?>');
    $('#kind_id, #kind_name, #goods_type_id, #goods_type_name, #transport_mode_id, #transport_mode_name').val('');
    $('#quotation_id, #quotation_sub_total, #quotation_vat_amount, #quotation_total_amount').val('');
    allQuotationsData = [];
    updateMCACountBadge();
    clearMCATable();
    clearQuotationItems();
    $('#collapseInvoice').collapse('hide');
  }

  $('#invoiceForm').on('submit', function (e) {
    e.preventDefault();
    
    const validation = validateForm();
    if (!validation.isValid) {
      Swal.fire({
        icon: 'error', 
        title: 'Validation Error', 
        html: '<ul style="text-align:left; font-size:0.8rem;"><li>' + validation.errors.join('</li><li>') + '</li></ul>'
      });
      return;
    }

    quotationItemsData = [];
    $('.quotation-item-row:visible').each(function() {
      const $row = $(this);
      
      quotationItemsData.push({
        id: $row.data('quotation-item-id') || null,
        item_id: $row.data('item-id') || null,
        item_name: $row.find('.item-desc').val(),
        unit_id: $row.data('unit-id') || null,
        unit_text: $row.find('.item-unit').val(),
        quantity: 1,
        taux_usd: 0,
        cost_usd: parseFloat($row.find('.item-cost-usd').val()) || 0,
        subtotal_usd: parseFloat($row.find('.item-subtotal').val()) || 0,
        tva_usd: parseFloat($row.find('.item-tva').val()) || 0,
        total_usd: parseFloat($row.find('.item-total').val()) || 0,
        category_id: $row.data('category-id') || 0,
        category_name: $row.data('category-name') || 'UNCATEGORIZED',
        category_header: $row.data('category-header') || $row.data('category-name') || 'UNCATEGORIZED',
        display_order: $row.data('display-order') || 999,
        currency_id: $row.data('currency-id') || null,
        has_tva: $row.data('has-tva') || 0
      });
    });

    if (quotationItemsData.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'No Items',
        text: 'Please select a quotation and ensure items are loaded before saving.',
        confirmButtonColor: '#f39c12'
      });
      return;
    }
    
    const quotationId = $('#quotation_id').val();
    if (!quotationId || quotationId === '') {
      Swal.fire({
        icon: 'warning',
        title: 'No Quotation Selected',
        text: 'Please select a quotation before saving the invoice.',
        confirmButtonColor: '#f39c12'
      });
      return;
    }

    let mcaDataArray = [];
    let firstMcaId = null;
    
    $('.mca-checkbox:checked').each(function() {
      const $row = $(this).closest('tr');
      const mcaId = $(this).data('mca-id');
      
      if (firstMcaId === null) firstMcaId = mcaId;
      
      mcaDataArray.push({
        mca_id: mcaId,
        lot_number: $row.find('[data-field="lot_number"]').val(),
        declaration_no: $row.find('[data-field="declaration_no"]').val(),
        declaration_date: $row.find('[data-field="declaration_date"]').val(),
        liquidation_no: $row.find('[data-field="liquidation_no"]').val(),
        liquidation_date: $row.find('[data-field="liquidation_date"]').val(),
        liquidation_amount: $row.find('[data-field="liquidation_amount"]').val(),
        liquidation_usd: $row.find('[data-field="liquidation_usd"]').val(),
        quittance_no: $row.find('[data-field="quittance_no"]').val(),
        quittance_date: $row.find('[data-field="quittance_date"]').val(),
        horse: $row.find('[data-field="horse"]').val(),
        trailer_1: $row.find('[data-field="trailer_1"]').val(),
        trailer_2: $row.find('[data-field="trailer_2"]').val(),
        container: $row.find('[data-field="container"]').val(),
        feet_container_id: $row.find('[data-field="feet_container_id"]').val(),
        feet_container_size: $row.find('[data-field="feet_container_size"]').val(),
        weight: $row.find('[data-field="weight"]').val(),
        bcc_rate: $row.find('[data-field="bcc_rate"]').val(),
        ceec_amount: $row.find('[data-field="ceec_amount"]').val(),
        cgea_amount: $row.find('[data-field="cgea_amount"]').val(),
        occ_amount: $row.find('[data-field="occ_amount"]').val(),
        lmc_amount: $row.find('[data-field="lmc_amount"]').val(),
        ogefrem_amount: $row.find('[data-field="ogefrem_amount"]').val(),
        buyer: ''
      });
    });

    if (firstMcaId !== null) $('#mca_id').val(firstMcaId);

    $('input[name="mca_data"]').val(JSON.stringify(mcaDataArray));
    $('#quotation_items').val(JSON.stringify(quotationItemsData));

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
          Swal.fire({ icon: 'error', title: 'Error!', html: '<div style="text-align:left; font-size:0.85rem;">' + (res.message || 'Unknown error') + '</div>' });
        }
      },
      error: function (xhr) {
        submitBtn.prop('disabled', false).html(originalText);
        
        let errorMessage = 'Failed to save export invoice';
        try {
          const errorResponse = JSON.parse(xhr.responseText);
          errorMessage = errorResponse.message || errorMessage;
        } catch (e) {
          errorMessage = xhr.responseText || errorMessage;
        }
        
        Swal.fire({ icon: 'error', title: 'Server Error', html: '<div style="text-align:left; font-size:0.85rem;">' + errorMessage + '</div>' });
      }
    });
  });

  $('#cancelBtn').on('click', (e) => { e.preventDefault(); resetForm(); });

  $(document).on('click', '.editBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    isEditMode = true;
    editModeDataLoaded = false;
    
    $('#formAction').val('update');
    $('#invoice_id').val(id);
    $('#formTitle').text('Edit Export Invoice');
    $('#submitBtnText').text('Update Invoice');
    
    $('#quotationItemsBody').html('<tr><td colspan="6" class="text-center" style="padding: 30px;"><i class="spinner-border spinner-border-sm me-2"></i><div style="margin-top: 8px; color: #667eea; font-weight: 600; font-size: 0.75rem;">Loading invoice data...</div></td></tr>');
    
    $.ajax({
      url: CONTROLLER_URL + '/crudData/getInvoice',
      method: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data) {
          const inv = res.data;
          
          $('#client_id').val(inv.client_id);
          
          if (inv.client_id) {
            $.ajax({
              url: CONTROLLER_URL + '/crudData/getLicenses',
              method: 'GET',
              data: { client_id: inv.client_id },
              dataType: 'json',
              async: false,
              success: function(licensesRes) {
                if (licensesRes.success && licensesRes.data) {
                  let options = '<option value="">-- Select License --</option>';
                  licensesRes.data.forEach(function(license) {
                    const selected = license.id == inv.license_id ? 'selected' : '';
                    options += `<option value="${license.id}" ${selected}>${escapeHtml(license.license_number)}</option>`;
                  });
                  $('#license_id').html(options);
                }
              }
            });
            
            $.ajax({
              url: CONTROLLER_URL + '/crudData/getAllQuotationsForClient',
              method: 'GET',
              data: { client_id: inv.client_id },
              dataType: 'json',
              async: false,
              success: function(quotRes) {
                if (quotRes.success && quotRes.data && quotRes.data.length > 0) {
                  allQuotationsData = quotRes.data;
                  
                  let options = '<option value="">-- Select Quotation --</option>';
                  quotRes.data.forEach(function(quot) {
                    const isSelected = (quot.id == inv.quotation_id) ? ' selected' : '';
                    options += `<option value="${quot.id}"${isSelected}>${escapeHtml(quot.quotation_ref)}</option>`;
                  });
                  $('#quotationSelector').html(options);
                } else {
                  $('#quotationSelector').html('<option value="">-- No Quotations Available --</option>');
                }
              }
            });
          }
          
          $('#invoice_ref').val(inv.invoice_ref || '');
          $('#invoice_date').val(inv.invoice_date || '');
          $('#arsp').val(inv.arsp || 'Disabled');
          $('#kind_id').val(inv.kind_id || '');
          $('#kind_name').val(inv.kind_name || '');
          $('#goods_type_id').val(inv.goods_type_id || '');
          $('#goods_type_name').val(inv.goods_type_name || '');
          $('#transport_mode_id').val(inv.transport_mode_id || '');
          $('#transport_mode_name').val(inv.transport_mode_name || '');
          $('#quotation_id').val(inv.quotation_id || '');
          
          if (res.all_mcas && res.all_mcas.length > 0) {
            displayMCATable(res.all_mcas);
            
            setTimeout(function() {
              if (res.mca_data && res.mca_data.length > 0) {
                res.mca_data.forEach(function(mcaData) {
                  const $checkbox = $('.mca-checkbox[data-mca-id="' + mcaData.mca_id + '"]');
                  
                  if ($checkbox.length > 0) {
                    $checkbox.prop('checked', true);
                    $checkbox.closest('tr').addClass('selected');
                    
                    const $mcaRow = $checkbox.closest('tr');
                    $mcaRow.find('[data-field="lot_number"]').val(mcaData.lot_number || '');
                    $mcaRow.find('[data-field="declaration_no"]').val(mcaData.declaration_no || '');
                    $mcaRow.find('[data-field="declaration_date"]').val(mcaData.declaration_date || '');
                    $mcaRow.find('[data-field="liquidation_no"]').val(mcaData.liquidation_no || '');
                    $mcaRow.find('[data-field="liquidation_date"]').val(mcaData.liquidation_date || '');
                    $mcaRow.find('[data-field="liquidation_amount"]').val(mcaData.liquidation_amount || '0.00');
                    $mcaRow.find('[data-field="liquidation_usd"]').val(mcaData.liquidation_usd || '0.00');
                    $mcaRow.find('[data-field="quittance_no"]').val(mcaData.quittance_no || '');
                    $mcaRow.find('[data-field="quittance_date"]').val(mcaData.quittance_date || '');
                    $mcaRow.find('[data-field="horse"]').val(mcaData.horse || '');
                    $mcaRow.find('[data-field="trailer_1"]').val(mcaData.trailer_1 || '');
                    $mcaRow.find('[data-field="trailer_2"]').val(mcaData.trailer_2 || '');
                    $mcaRow.find('[data-field="container"]').val(mcaData.container || '');
                    $mcaRow.find('[data-field="feet_container_id"]').val(mcaData.feet_container_id || '');
                    $mcaRow.find('[data-field="feet_container_size"]').val(mcaData.feet_container_size || '');
                    $mcaRow.find('[data-field="weight"]').val(mcaData.weight || '');
                    $mcaRow.find('[data-field="bcc_rate"]').val(mcaData.bcc_rate || '2500.00');
                    $mcaRow.find('[data-field="ceec_amount"]').val(mcaData.ceec_amount || '0.00');
                    $mcaRow.find('[data-field="cgea_amount"]').val(mcaData.cgea_amount || '0.00');
                    $mcaRow.find('[data-field="occ_amount"]').val(mcaData.occ_amount || '0.00');
                    $mcaRow.find('[data-field="lmc_amount"]').val(mcaData.lmc_amount || '0.00');
                    $mcaRow.find('[data-field="ogefrem_amount"]').val(mcaData.ogefrem_amount || '0.00');
                    
                    if (mcaData.container && mcaData.container.trim() !== '') {
                      $mcaRow.attr('data-has-container', '1');
                    } else {
                      $mcaRow.attr('data-has-container', '0');
                    }
                    
                    if (mcaData.feet_container_size) {
                      $mcaRow.attr('data-feet-container-size', mcaData.feet_container_size);
                    }
                  }
                });
              }
              
              updateMCACountBadge();
              
              setTimeout(function() {
                applyContainerConditionalHiding();
              }, 200);
              
              loadInvoiceItems(inv, res);
              
            }, 400);
          } else {
            loadInvoiceItems(inv, res);
          }

          editModeDataLoaded = true;
          $('#collapseInvoice').collapse('show');
          
        } else {
          Swal.fire('Error', res.message || 'Failed to load invoice data', 'error');
          isEditMode = false;
        }
      },
      error: function(xhr) {
        console.error('Error loading invoice:', xhr);
        Swal.fire('Error', 'Failed to load invoice', 'error');
        isEditMode = false;
      }
    });
  });

  function loadInvoiceItems(inv, res) {
    if (res.items && res.items.length > 0) {
      originalQuotationItems = JSON.parse(JSON.stringify(res.items));
      quotationItemsData = JSON.parse(JSON.stringify(res.items));
      
      displayStoredItemsByCategory(res.items);
      return;
    }
    
    if (inv.quotation_id) {
      const clientId = $('#client_id').val();
      
      if (clientId) {
        $.ajax({
          url: CONTROLLER_URL + '/crudData/getQuotationItems',
          method: 'GET',
          data: { 
            quotation_id: inv.quotation_id, 
            client_id: clientId 
          },
          dataType: 'json',
          success: function(itemsRes) {
            if (itemsRes.success) {
              quotationData = itemsRes.quotation;
              
              if (itemsRes.categorized_items && itemsRes.categorized_items.length > 0) {
                displayQuotationItemsByCategory(itemsRes.categorized_items);
              } else if (itemsRes.items && itemsRes.items.length > 0) {
                displayQuotationItems(itemsRes.items);
              } else {
                showNoItemsMessage('No items found in quotation');
              }
            } else {
              showNoItemsMessage(itemsRes.message || 'Failed to load quotation items');
            }
          },
          error: function() {
            showNoItemsMessage('Error loading quotation items');
          }
        });
      } else {
        showNoItemsMessage('Missing client ID');
      }
    } else {
      showNoItemsMessage('No items found');
    }
  }

  function showNoItemsMessage(message) {
    message = message || 'No items found';
    
    $('#quotationItemsBody').html(
      '<tr><td colspan="6" class="text-center" style="padding: 30px;">' +
      '<i class="ti ti-alert-triangle me-2" style="font-size: 1.5rem; color: #f39c12;"></i>' +
      '<div style="margin-top: 8px; color: #f39c12; font-weight: 600; font-size: 0.75rem;">' + 
      escapeHtml(message) + 
      '</div></td></tr>'
    );
    $('#quotationItemsFooter').hide();
  }

  function displayStoredItemsByCategory(items) {
    if (!items || items.length === 0) {
      showNoItemsMessage();
      return;
    }
    
    const grouped = {};
    items.forEach((item) => {
      const catId = item.category_id || 0;
      const catName = item.category_name || 'UNCATEGORIZED';
      const catHeader = item.category_header || item.category_name || 'UNCATEGORIZED';
      const displayOrder = parseInt(item.display_order) || 999;
      
      if (!grouped[catId]) {
        grouped[catId] = {
          category_id: catId,
          category_name: catName,
          category_header: catHeader,
          display_order: displayOrder,
          items: []
        };
      }
      grouped[catId].items.push(item);
    });
    
    const categorizedItems = Object.values(grouped).sort((a, b) => a.display_order - b.display_order);
    
    if (categorizedItems.length === 0) {
      showNoItemsMessage('Error grouping items');
      return;
    }
    
    displayQuotationItemsByCategory(categorizedItems);
  }

  $(document).on('click', '.pdfP1Btn', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    const id = $(this).data('id');
    if (!id) return;

    window.open(CONTROLLER_URL + '/crudData/viewPDFPage1?id=' + id, '_blank');
  });

  $(document).on('click', '.pdfP2Btn', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    const id = $(this).data('id');
    if (!id) return;
    
    window.open(CONTROLLER_URL + '/crudData/viewPDFPages2to4?id=' + id, '_blank');
  });

  $(document).on('click', '.validateBtn', function() {
    const id = $(this).data('id');
    if (!id) return;
    
    Swal.fire({
      title: 'Validate Invoice?',
      text: 'Are you sure?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#17a2b8',
      confirmButtonText: 'Yes, validate!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: CONTROLLER_URL + '/crudData/validateInvoice',
          method: 'POST',
          data: { id: id, csrf_token: csrfToken },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              Swal.fire({ icon: 'success', title: 'Validated!', timer: 2000, showConfirmButton: false });
              invoicesTable.ajax.reload(null, false);
              updateStatistics();
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          }
        });
      }
    });
  });

  // ========== DGI BUTTON HANDLER - IMPROVED ==========
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
        // Show loading state
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
              // Populate modal with data
              $("#total-badge").text(res.data.total || '0.00');
              $("#dgi-total-badge").text(res.data.vtotal || '0.00');
              $("#emcf-invoice-uid").val(res.data.uid || '');
              $("#emcf-invoice-id").val(id);
              
              // Show the modal
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

  // ========== DGI MODAL - CONFIRM BUTTON ==========
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

  // ========== RESET MODAL ON CLOSE ==========
  $('#emcf-modal').on('hidden.bs.modal', function () {
    $('#btn-confirm-emcf, #btn-cancel-emcf').prop('disabled', false);
    $('#btn-confirm-emcf').html('<i class="ti ti-check"></i> Confirm DGI Verification');
    $('#btn-cancel-emcf').html('<i class="ti ti-x"></i> Cancel');
    $("#emcf-invoice-uid").val('');
    $("#emcf-invoice-id").val('');
    $("#total-badge").text('0.00');
    $("#dgi-total-badge").text('0.00');
  });

  function initDataTable() {
    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
      $('#invoicesTable').DataTable().destroy();
    }

    invoicesTable = $('#invoicesTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      searching: false,
      ajax: { 
        url: CONTROLLER_URL + '/crudData/listing',
        type: 'GET',
        data: function(d) { 
          d.filter = currentFilter;
          d.search = { value: $('#customSearchBox').val() };
        }
      },
      columns: [
        { data: 'id' },
        { data: 'invoice_ref', render: data => escapeHtml(data || '') },
        { data: 'client_name', render: data => escapeHtml(data || '') },
        { data: 'mca_count', render: data => (data || 1) + ' file(s)' },
        { data: 'fob_usd', render: data => data ? '$' + parseFloat(data).toFixed(2) : '$0.00' },
        { data: 'total_duty_cdf', render: data => data ? parseFloat(data).toFixed(0) + ' CDF' : '0 CDF' },
        { 
          data: 'validated',
          render: (data) => {
            const validated = parseInt(data || 0);
            if (validated === 0) return '<span class="validation-badge validation-not-validated">NOT VALIDATED</span>';
            else if (validated === 1) return '<span class="validation-badge validation-validated">✓ VALIDATED</span>';
            else if (validated === 2) return '<span class="validation-badge validation-dgi-verified">DGI VERIFIED</span>';
            return '<span class="validation-badge validation-not-validated">UNKNOWN</span>';
          }
        },
        { 
          data: null, 
          orderable: false, 
          searchable: false, 
          render: (data, type, row) => {
            const validated = parseInt(row.validated || 0);
            let html = `
              <button class="btn btn-sm btn-pdf-p1 pdfP1Btn" data-id="${row.id}" title="View PDF P1 (Debit Note)">
                <i class="ti ti-file-type-pdf"></i> P1
              </button>
              <button class="btn btn-sm btn-pdf-p2 pdfP2Btn" data-id="${row.id}" title="View PDF P2 (Facture + Details)">
                <i class="ti ti-file-type-pdf"></i> P2
              </button>
              <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}" title="Edit"><i class="ti ti-edit"></i></button>`;
            
            if (validated === 0) {
              html += `<button class="btn btn-sm btn-validate validateBtn" data-id="${row.id}" title="Validate"><i class="ti ti-circle-check"></i></button>`;
            }
            
            if (validated === 1) {
              html += `<button class="btn btn-sm btn-dgi dgiBtn" data-id="${row.id}" title="Mark DGI Verified"><i class="ti ti-file-check"></i></button>`;
            }
            
            return html;
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      dom: 'rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      language: {
        processing: '<i class="spinner-border spinner-border-sm"></i> Loading...',
        info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
        infoEmpty: 'No invoices to display',
        infoFiltered: '(filtered from _MAX_ total)',
        zeroRecords: 'No matching invoices found',
        paginate: {
          first: '&laquo;',
          previous: '&lsaquo;',
          next: '&rsaquo;',
          last: '&raquo;'
        }
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
          $('#totalPending').text(res.data.pending_invoicing || 0);
        }
      }
    });
  }

  initDataTable();
  updateStatistics();
  $('.filter-card[data-filter="all"]').addClass('active-filter');
});
</script>