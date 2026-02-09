<link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">

<style>
  /* Modern Color Palette */
  :root {
    --primary-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
    --info-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  }

  /* Base Styles */
  .page-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 20px 0;
  }

  .dataTables_wrapper .dataTables_info { float: left; }
  .dataTables_wrapper .dataTables_paginate { float: right; text-align: right; }
  
  /* Card Styles */
  .card { 
    border: none; 
    border-radius: 16px; 
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: white;
    margin-bottom: 24px;
  }
  
  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.18);
  }

  .card-header { 
    background: var(--primary-gradient);
    border-bottom: none;
    padding: 16px 20px;
    color: white;
  }
  
  .header-title { 
    font-size: 1rem; 
    font-weight: 700; 
    color: white;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .header-title i {
    font-size: 1.2rem;
    opacity: 0.9;
  }
  
  /* ✅ ALL EXPORT BUTTONS - GREEN STYLING */
  .btn-export-all,
  .btn-export-partielle,
  .btn-export-files {
    background: linear-gradient(135deg, #56ab2f, #a8e063) !important;
    color: white !important;
    border: none !important;
    padding: 6px 14px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 0.75rem !important;
    box-shadow: 0 4px 12px rgba(86, 171, 47, 0.3);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    text-decoration: none;
  }
  
  .btn-export-all:hover,
  .btn-export-partielle:hover,
  .btn-export-files:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 8px 20px rgba(86, 171, 47, 0.4) !important;
    color: white !important;
  }

  .btn-export-all:active,
  .btn-export-partielle:active,
  .btn-export-files:active {
    transform: translateY(0);
  }
  
  /* Action Buttons */
  .btn-primary { 
    background: linear-gradient(135deg, #2c3e50, #34495e); 
    border: none; 
    width: 28px; 
    height: 28px; 
    padding: 0; 
    font-size: 0.75rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(44, 62, 80, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.5);
  }
  
  .btn-danger { 
    background: linear-gradient(135deg, #e74c3c, #c0392b); 
    border: none; 
    width: 28px; 
    height: 28px; 
    padding: 0; 
    font-size: 0.75rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.5);
  }

  /* Enhanced Filter Section */
  .filter-section {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  .filter-section .form-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .filter-section .form-label i {
    color: #2c3e50;
    font-size: 0.85rem;
  }

  .filter-section .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 6px 12px;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.3s ease;
    background: white;
  }

  .filter-section .form-select:focus {
    border-color: #2c3e50;
    box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    outline: none;
  }
  
  /* Modal Styles */
  .modal-content { 
    border: none; 
    border-radius: 16px; 
    overflow: hidden;
    box-shadow: 0 16px 48px rgba(0,0,0,0.25);
  }
  
  .modal-header { 
    background: var(--primary-gradient);
    color: white; 
    padding: 16px 20px;
    border-bottom: none;
  }
  
  .modal-header .btn-close { 
    filter: brightness(0) invert(1);
    opacity: 0.8;
    transition: opacity 0.3s ease;
  }

  .modal-header .btn-close:hover {
    opacity: 1;
  }
  
  .modal-header .modal-title { 
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .modal-body { 
    padding: 20px;
    background: #f8f9fa;
  }

  .modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 12px 20px;
  }
  
  /* Excel Form Table */
  .excel-form-wrapper {
    overflow-x: auto;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    margin-bottom: 16px;
    background: #fafafa;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.05);
  }
  
  .excel-form-table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
    background: white;
  }
  
  .excel-form-table th {
    background: linear-gradient(180deg, #f8f9fa, #e9ecef);
    border: 1px solid #ddd;
    padding: 8px 6px;
    font-size: 0.65rem;
    font-weight: 700;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    min-width: 80px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  
  .excel-form-table td {
    border: 1px solid #ddd;
    padding: 6px;
    vertical-align: middle;
    background: white;
  }
  
  .excel-form-table input {
    width: 100%;
    border: 2px solid #e9ecef;
    padding: 6px 8px;
    font-size: 0.75rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
  }
  
  .excel-form-table input:focus {
    border-color: #2c3e50;
    outline: none;
    box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
  }
  
  .excel-form-table .readonly-cell input {
    background: linear-gradient(135deg, #f5f5f5, #e9ecef);
    color: #6c757d;
    font-weight: 700;
    cursor: not-allowed;
    border-color: #dee2e6;
  }
  
  /* Section Headers with Gradients */
  .excel-form-table th.section-license { 
    background: linear-gradient(135deg, #2c3e50, #34495e); 
    color: white;
    font-weight: 700;
  }
  
  .excel-form-table th.section-partial { 
    background: linear-gradient(135deg, #9B59B6, #8E44AD); 
    color: white;
    font-weight: 700;
  }
  
  .excel-form-table th.section-used { 
    background: linear-gradient(135deg, #F39C12, #E67E22); 
    color: white;
    font-weight: 700;
  }
  
  .excel-form-table th.section-calculated { 
    background: linear-gradient(135deg, #1ABC9C, #16A085); 
    color: white;
    font-weight: 700;
  }
  
  /* License Info Bar - Enhanced */
  .license-info-bar {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid #e9ecef;
    border-left: 5px solid #2c3e50;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
  }
  
  .license-info-bar .info-item { 
    display: flex; 
    align-items: center; 
    gap: 6px;
    background: white;
    padding: 6px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  }
  
  .license-info-bar .info-label { 
    font-size: 0.6rem; 
    color: #7f8c8d; 
    font-weight: 700; 
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  
  .license-info-bar .info-value { 
    font-size: 0.8rem; 
    color: #2C3E50; 
    font-weight: 700;
  }
  
  /* Edit Mode Note - Enhanced */
  .edit-mode-note {
    background: linear-gradient(135deg, #fff3cd, #ffeeba);
    border: 2px solid #ffc107;
    border-left: 5px solid #ff9800;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.75rem;
    color: #856404;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 3px 10px rgba(255, 193, 7, 0.2);
  }

  .edit-mode-note i {
    font-size: 1rem;
    color: #ff9800;
  }
  
  .scroll-hint { 
    font-size: 0.65rem; 
    color: #95a5a6; 
    text-align: center; 
    margin-top: 6px; 
    font-style: italic;
    font-weight: 500;
  }
  
  /* Count Badge - Enhanced */
  .count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 24px;
    padding: 0 8px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .count-badge.has-records { 
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 2px 8px rgba(44, 62, 80, 0.3);
  }
  
  .count-badge.has-records:hover { 
    transform: scale(1.15); 
    box-shadow: 0 4px 16px rgba(44, 62, 80, 0.5);
  }
  
  .count-badge.no-records { 
    background: linear-gradient(135deg, #e9ecef, #dee2e6); 
    color: #6c757d; 
    cursor: default;
  }
  
  .badge { 
    padding: 4px 10px; 
    font-size: 0.65rem; 
    border-radius: 6px;
    font-weight: 600;
  }
  
  /* DataTable Styling - Enhanced */
  #licensesTable { 
    font-size: 0.75rem;
    border-radius: 10px;
    overflow: hidden;
  }
  
  #licensesTable thead th {
    background: linear-gradient(180deg, #2c3e50, #34495e);
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    color: white;
    padding: 10px 8px;
    white-space: nowrap;
    letter-spacing: 0.3px;
    border: none;
  }
  
  #licensesTable tbody td {
    padding: 8px 6px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.7rem;
  }

  #licensesTable tbody tr {
    transition: all 0.2s ease;
  }

  #licensesTable tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  
  /* Column widths */
  #licensesTable th:nth-child(1) { width: 40px; }
  #licensesTable th:nth-child(2) { min-width: 120px; max-width: 160px; }
  #licensesTable th:nth-child(3) { min-width: 90px; max-width: 110px; }
  #licensesTable th:nth-child(4) { width: 70px; }
  #licensesTable th:nth-child(5) { min-width: 150px; }
  #licensesTable th:nth-child(6) { min-width: 100px; }
  #licensesTable th:nth-child(7) { min-width: 120px; }
  #licensesTable th:nth-child(8) { width: 110px; }
  #licensesTable th:nth-child(9) { width: 110px; }
  #licensesTable th:nth-child(10) { width: 110px; }
  #licensesTable th:nth-child(11) { width: 110px; }
  #licensesTable th:nth-child(12) { width: 110px; }
  #licensesTable th:nth-child(13) { width: 120px; }
  #licensesTable th:nth-child(14) { width: 120px; }
  
  .edit-form-card { 
    border-left: 5px solid #9B59B6;
    animation: slideDown 0.4s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate {
    font-size: 0.75rem;
    font-weight: 500;
  }
  
  .dataTables_wrapper .dataTables_length select,
  .dataTables_wrapper .dataTables_filter input {
    font-size: 0.75rem;
    padding: 4px 10px;
    border-radius: 6px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
  }

  .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #2c3e50;
    box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    outline: none;
  }

  /* PARTIELLE List Section - Enhanced */
  #partielleListSection {
    display: none;
    margin-top: 28px;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  #partielleListSection.show {
    display: block;
  }

  .partielle-list-header {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 12px 16px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
  }

  .partielle-list-header h5 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .partielle-list-header .header-actions {
    display: flex;
    gap: 8px;
  }

  .btn-close-partielle,
  .btn-close-details {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition: all 0.3s ease;
    padding: 0 10px;
    gap: 5px;
    font-weight: 600;
  }

  .btn-close-partielle:hover,
  .btn-close-details:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
  }

  .partielle-list-body {
    background: white;
    border: 2px solid #e0e0e0;
    border-top: none;
    border-radius: 0 0 10px 10px;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  }

  /* License Summary Bar - Enhanced */
  .license-summary-bar {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid #e9ecef;
    border-left: 5px solid #2c3e50;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
  }

  .license-summary-bar .license-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
    background: white;
    padding: 8px 12px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }

  .license-summary-bar .license-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .license-summary-bar .license-label {
    font-size: 0.58rem;
    color: #7f8c8d;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .license-summary-bar .license-value {
    font-size: 0.75rem;
    color: #2c3e50;
    font-weight: 800;
  }

  #partielleListTable {
    font-size: 0.75rem;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
  }

  #partielleListTable thead th {
    background: linear-gradient(180deg, #f8f9fa, #e9ecef);
    font-weight: 700;
    font-size: 0.65rem;
    text-transform: uppercase;
    color: #495057;
    padding: 10px 8px;
    border: 1px solid #ddd;
    white-space: nowrap;
    letter-spacing: 0.3px;
  }

  #partielleListTable tbody td {
    padding: 8px;
    border: 1px solid #f0f0f0;
    vertical-align: middle;
    font-size: 0.75rem;
  }

  #partielleListTable tbody tr {
    transition: all 0.2s ease;
  }

  #partielleListTable tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  .empty-partielle-state {
    text-align: center;
    padding: 50px 20px;
    color: #adb5bd;
  }

  .empty-partielle-state i {
    font-size: 3rem;
    margin-bottom: 12px;
    opacity: 0.4;
  }

  .empty-partielle-state p {
    font-size: 0.85rem;
    font-weight: 500;
  }

  /* Files Details Section - Enhanced */
  #filesDetailsSection {
    display: none;
    margin-top: 28px;
    animation: fadeIn 0.4s ease;
  }

  #filesDetailsSection.show {
    display: block;
  }

  .files-details-header {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 12px 16px;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
  }

  .files-details-header h5 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .files-details-header .header-actions {
    display: flex;
    gap: 8px;
  }

  .files-details-body {
    background: white;
    border: 2px solid #e0e0e0;
    border-top: none;
    border-radius: 0 0 10px 10px;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  }

  /* AV Summary Bar - Enhanced */
  .av-summary-bar {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border: 2px solid #e9ecef;
    border-left: 5px solid #2c3e50;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
  }

  .av-summary-bar .av-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
    background: white;
    padding: 8px 14px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }

  .av-summary-bar .av-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .av-summary-bar .av-label {
    font-size: 0.6rem;
    color: #7f8c8d;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .av-summary-bar .av-value {
    font-size: 0.85rem;
    color: #2c3e50;
    font-weight: 800;
  }

  #filesDetailsTable {
    font-size: 0.68rem;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    table-layout: fixed;
  }

  #filesDetailsTable thead th {
    background: linear-gradient(180deg, #f8f9fa, #e9ecef);
    font-weight: 700;
    font-size: 0.6rem;
    text-transform: uppercase;
    color: #495057;
    padding: 8px 5px;
    border: 1px solid #ddd;
    white-space: normal;
    letter-spacing: 0.2px;
    line-height: 1.2;
  }

  #filesDetailsTable tbody td {
    padding: 6px 5px;
    border: 1px solid #f0f0f0;
    vertical-align: middle;
    font-size: 0.68rem;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Specific column widths for Files Details Table */
  #filesDetailsTable th:nth-child(1) { width: 35px; }
  #filesDetailsTable th:nth-child(2) { width: 90px; }
  #filesDetailsTable th:nth-child(3) { width: 90px; }
  #filesDetailsTable th:nth-child(4) { width: 90px; }
  #filesDetailsTable th:nth-child(5) { width: 80px; }
  #filesDetailsTable th:nth-child(6) { width: 90px; }
  #filesDetailsTable th:nth-child(7) { width: 80px; }
  #filesDetailsTable th:nth-child(8) { width: 90px; }
  #filesDetailsTable th:nth-child(9) { width: 80px; }
  #filesDetailsTable th:nth-child(10) { width: 75px; text-align: right; }
  #filesDetailsTable th:nth-child(11) { width: 75px; text-align: right; }

  #filesDetailsTable tbody td:nth-child(10),
  #filesDetailsTable tbody td:nth-child(11) {
    text-align: right;
    font-weight: 600;
  }

  #filesDetailsTable tbody tr {
    transition: all 0.2s ease;
  }

  #filesDetailsTable tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  .empty-files-state {
    text-align: center;
    padding: 50px 20px;
    color: #adb5bd;
  }

  .empty-files-state i {
    font-size: 3rem;
    margin-bottom: 12px;
    opacity: 0.4;
  }

  .empty-files-state p {
    font-size: 0.85rem;
    font-weight: 500;
  }

  /* Enhanced Form Buttons */
  .btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
    border-radius: 8px;
    padding: 6px 14px;
    font-weight: 600;
    font-size: 0.75rem;
    transition: all 0.3s ease;
  }

  .btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
  }

  #submitBtn {
    background: linear-gradient(135deg, #56ab2f, #a8e063);
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-weight: 600;
    font-size: 0.75rem;
    color: white;
    box-shadow: 0 4px 12px rgba(86, 171, 47, 0.3);
    transition: all 0.3s ease;
  }

  #submitBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(86, 171, 47, 0.4);
  }

  /* Close button in edit form */
  #closeFormBtn {
    font-size: 0.75rem !important; 
    padding: 5px 12px !important; 
    border-radius: 6px !important;
  }

  /* Spinner Enhancement */
  .spinner-border {
    border-width: 2px;
  }

  .spinner-border-sm {
    width: 1rem;
    height: 1rem;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .header-title {
      font-size: 0.85rem;
    }

    .license-info-bar,
    .license-summary-bar,
    .av-summary-bar {
      gap: 10px;
    }

    .filter-section {
      padding: 12px;
    }

    .card-header {
      padding: 12px 16px;
    }
  }

  /* Smooth Transitions */
  * {
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  /* Custom Scrollbar */
  ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }

  ::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 8px;
  }

  ::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    border-radius: 8px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #34495e, #2c3e50);
  }
</style>

<div class="page-content">
  <div class="page-container">
    <div class="row">
      <div class="col-12">

        <!-- Edit Partial Details Form Card -->
        <div class="card mb-4 edit-form-card" id="editFormCard" style="display:none;">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0">
              <i class="ti ti-edit"></i> 
              <span>Edit Partial Allocation</span>
              <small class="ms-2" id="editingPartielle" style="font-size: 0.75rem; opacity: 0.9;"></small>
            </h4>
            <button type="button" class="btn btn-sm btn-outline-light" id="closeFormBtn">
              <i class="ti ti-x"></i> Close
            </button>
          </div>

          <div class="card-body" style="background: #f8f9fa;">
            <form id="partielleForm" method="post" novalidate>
              <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $csrf_token ?>">
              <input type="hidden" name="partielle_id" id="partielle_id" value="">
              <input type="hidden" name="action" id="formAction" value="update">

              <div id="licenseInfoBar" class="license-info-bar">
                <div class="info-item">
                  <span class="info-label">PARTIELLE:</span>
                  <span class="info-value" id="info_partial_name">-</span>
                </div>
                <div class="info-item">
                  <span class="info-label">License:</span>
                  <span class="info-value" id="info_license_number">-</span>
                </div>
                <div class="info-item">
                  <span class="info-label">CRF:</span>
                  <span class="info-value" id="info_crf">-</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Client:</span>
                  <span class="info-value" id="info_client">-</span>
                </div>
              </div>

              <div class="edit-mode-note">
                <i class="ti ti-info-circle"></i>
                <span>Only <span style="color: #9B59B6; font-weight: 700;">AV Allocation</span> fields (purple) can be edited. Calculated fields (green) auto-update based on actual usage.</span>
              </div>

              <div class="excel-form-wrapper">
                <table class="excel-form-table">
                  <thead>
                    <tr>
                      <th class="section-license">License Wt</th>
                      <th class="section-license">License FOB</th>
                      <th class="section-license">License Ins</th>
                      <th class="section-license">License Frt</th>
                      <th class="section-license">License Other</th>
                      
                      <th class="section-partial">AV Wt</th>
                      <th class="section-partial">AV FOB</th>
                      <th class="section-partial">AV Ins</th>
                      <th class="section-partial">AV Frt</th>
                      <th class="section-partial">AV Other</th>
                      
                      <th class="section-used">Used Wt</th>
                      <th class="section-used">Used FOB</th>
                      
                      <th class="section-calculated">License-AV Wt</th>
                      <th class="section-calculated">License-AV FOB</th>
                      <th class="section-calculated">AV-Used Wt</th>
                      <th class="section-calculated">AV-Used FOB</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <!-- License Values (readonly) -->
                      <td class="readonly-cell"><input type="number" step="0.01" id="license_weight_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="license_fob_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="license_insurance_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="license_freight_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="license_other_costs_display" readonly></td>
                      
                      <!-- AV Values (editable) -->
                      <td><input type="number" step="0.01" name="partial_weight" id="partial_weight" min="0" placeholder="0.00"></td>
                      <td><input type="number" step="0.01" name="partial_fob" id="partial_fob" min="0" placeholder="0.00"></td>
                      <td><input type="number" step="0.01" name="partial_insurance" id="partial_insurance" min="0" placeholder="0.00"></td>
                      <td><input type="number" step="0.01" name="partial_freight" id="partial_freight" min="0" placeholder="0.00"></td>
                      <td><input type="number" step="0.01" name="partial_other_costs" id="partial_other_costs" min="0" placeholder="0.00"></td>
                      
                      <!-- Used Values (readonly - calculated from imports) -->
                      <td class="readonly-cell"><input type="number" step="0.01" id="used_weight_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="used_fob_display" readonly></td>
                      
                      <!-- Calculated Values (readonly) -->
                      <td class="readonly-cell"><input type="number" step="0.01" id="licenseweight_partial_weight_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="licensefob_partial_fob_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="partial_weight_usedweight_display" readonly></td>
                      <td class="readonly-cell"><input type="number" step="0.01" id="partial_fob_usedfob_display" readonly></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="scroll-hint">← Scroll horizontally to see all fields →</div>

              <div class="row mt-3">
                <div class="col-12 text-end">
                  <button type="button" class="btn btn-outline-secondary" id="cancelBtn">
                    <i class="ti ti-x me-1"></i> Cancel
                  </button>
                  <button type="submit" class="btn ms-2" id="submitBtn">
                    <i class="ti ti-check me-1"></i> Update
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>

        <!-- Licenses DataTable -->
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="header-title mb-0">
              <i class="ti ti-list"></i>
              Licenses Management
            </h4>
            <!-- ✅ GREEN EXPORT BUTTON 1: Licenses Table -->
            <button type="button" class="btn-export-all" id="exportLicensesBtn">
              <i class="ti ti-file-spreadsheet"></i>
              Export Excel
            </button>
          </div>
          <div class="card-body">
            <!-- Enhanced Filter Row -->
            <div class="filter-section">
              <div class="row">
                <div class="col-md-4">
                  <label for="clientFilter" class="form-label">
                    <i class="ti ti-filter"></i>
                    Filter by Client
                  </label>
                  <select id="clientFilter" class="form-select">
                    <option value="0">All Clients</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table id="licensesTable" class="table table-hover table-bordered dt-responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>License Number</th>
                    <th>CRF</th>
                    <th>PARTIELLE</th>
                    <th>Client</th>
                    <th>Currency</th>
                    <th>Type of Goods</th>
                    <th>License FOB</th>
                    <th>License Freight</th>
                    <th>License Insurance</th>
                    <th>License Other</th>
                    <th>License Weight</th>
                    <th>License Wt - Used Wt</th>
                    <th>License FOB - Used FOB</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <!-- PARTIELLE List Section (below licenses table) -->
            <div id="partielleListSection">
              <div class="partielle-list-header">
                <h5>
                  <i class="ti ti-file-text"></i>
                  <span id="partielleListTitle">PARTIELLE Details</span>
                </h5>
                <div class="header-actions">
                  <!-- ✅ GREEN EXPORT BUTTON 2: PARTIELLE Table -->
                  <button type="button" class="btn-export-partielle" id="exportPartielleBtn">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                  </button>
                  <button type="button" class="btn-close-partielle" id="closePartielleList">
                    <i class="ti ti-x"></i>
                  </button>
                </div>
              </div>
              <div class="partielle-list-body" id="partielleListBody">
                <!-- License summary and PARTIELLE table will be loaded here -->
              </div>
            </div>

            <!-- Files Details Section (below PARTIELLE list) -->
            <div id="filesDetailsSection">
              <div class="files-details-header">
                <h5>
                  <i class="ti ti-file-text"></i>
                  <span id="filesDetailsTitle">Files Details</span>
                </h5>
                <div class="header-actions">
                  <!-- ✅ GREEN EXPORT BUTTON 3: Files Table -->
                  <button type="button" class="btn-export-files" id="exportFilesBtn">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                  </button>
                  <button type="button" class="btn-close-details" id="closeFilesDetails">
                    <i class="ti ti-x"></i>
                  </button>
                </div>
              </div>
              <div class="files-details-body" id="filesDetailsBody">
                <!-- AV Summary and table will be loaded here -->
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
  <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

  const csrfToken = $('#csrf_token').val();

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return `${String(d.getDate()).padStart(2,'0')}-${String(d.getMonth()+1).padStart(2,'0')}-${d.getFullYear()}`;
  }

  function hideForm() {
    $('#editFormCard').slideUp(300);
    $('#partielleForm')[0].reset();
    $('#partielle_id').val('');
  }

  $('#closeFormBtn, #cancelBtn').on('click', hideForm);

  // Auto-calculate derived fields (SUBTRACTION)
  function calculateDerivedFields() {
    const licenseWeight = parseFloat($('#license_weight_display').val()) || 0;
    const licenseFob = parseFloat($('#license_fob_display').val()) || 0;
    const partialWeight = parseFloat($('#partial_weight').val()) || 0;
    const partialFob = parseFloat($('#partial_fob').val()) || 0;
    const usedWeight = parseFloat($('#used_weight_display').val()) || 0;
    const usedFob = parseFloat($('#used_fob_display').val()) || 0;

    // SUBTRACTION operations
    $('#licenseweight_partial_weight_display').val((licenseWeight - partialWeight).toFixed(2));
    $('#licensefob_partial_fob_display').val((licenseFob - partialFob).toFixed(2));
    $('#partial_weight_usedweight_display').val((partialWeight - usedWeight).toFixed(2));
    $('#partial_fob_usedfob_display').val((partialFob - usedFob).toFixed(2));
  }

  $('#partial_weight, #partial_fob, #partial_insurance, #partial_freight, #partial_other_costs').on('input', calculateDerivedFields);

  // Load clients for filter dropdown
  function loadClientFilter() {
    $.ajax({
      url: '<?= APP_URL ?>/bivac/crudData/getClients',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          let options = '<option value="0">All Clients</option>';
          res.data.forEach(function(client) {
            options += `<option value="${client.id}">${client.short_name}</option>`;
          });
          $('#clientFilter').html(options);
        }
      },
      error: function() {
        console.error('Failed to load client filter');
      }
    });
  }

  // Load clients on page load
  loadClientFilter();

  // Licenses DataTable
  var licensesTable = $('#licensesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= APP_URL ?>/bivac/crudData/licensesListing',
      type: 'GET',
      data: function(d) {
        d.client_filter = $('#clientFilter').val();
      },
      error: function() {
        Swal.fire('Error', 'Failed to load licenses data', 'error');
      }
    },
    columns: [
      { data: 'id' },
      { data: 'license_number' },
      { data: 'ref_cod' },
      { 
        data: 'partielle_count',
        render: function(d, t, row) {
          const count = d || 0;
          if (count > 0) {
            return `<span class="count-badge has-records view-license-partielle" data-license-id="${row.id}" data-license-number="${row.license_number}" data-count="${count}">${count}</span>`;
          } else {
            return `<span class="count-badge no-records">0</span>`;
          }
        }
      },
      { data: 'client_name' },
      { data: 'currency_name' },
      { data: 'type_of_goods_name' },
      { 
        data: 'fob_declared',
        render: function(d) {
          return parseFloat(d || 0).toFixed(2);
        }
      },
      { 
        data: 'freight',
        render: function(d) {
          return parseFloat(d || 0).toFixed(2);
        }
      },
      { 
        data: 'insurance',
        render: function(d) {
          return parseFloat(d || 0).toFixed(2);
        }
      },
      { 
        data: 'other_costs',
        render: function(d) {
          return parseFloat(d || 0).toFixed(2);
        }
      },
      { 
        data: 'weight',
        render: function(d) {
          return parseFloat(d || 0).toFixed(2) + ' KG';
        }
      },
      { 
        data: null,
        render: function(d, t, row) {
          const licWeight = parseFloat(row.weight || 0);
          const totalUsedWeight = parseFloat(row.total_used_weight || 0);
          const diff = licWeight - totalUsedWeight;
          return diff.toFixed(2) + ' KG';
        }
      },
      { 
        data: null,
        render: function(d, t, row) {
          const licFob = parseFloat(row.fob_declared || 0);
          const totalUsedFob = parseFloat(row.total_used_fob || 0);
          const diff = licFob - totalUsedFob;
          return diff.toFixed(2);
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 25,
    responsive: true,
    columnDefs: [
      { targets: [7, 8, 9, 10, 11, 12, 13], className: 'text-center' }
    ]
  });

  // Reload table when filter changes
  $('#clientFilter').on('change', function() {
    licensesTable.ajax.reload();
  });

  // ✅ EXPORT HANDLER 1: Export Licenses (Server-Side)
  $('#exportLicensesBtn').on('click', function() {
    const clientFilter = $('#clientFilter').val();
    window.location.href = '<?= APP_URL ?>/bivac/exportLicenses?client_filter=' + clientFilter;
  });

  // View PARTIELLE for License (from main table badge)
  $(document).on('click', '.view-license-partielle', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const licenseId = $(this).data('license-id');
    const licenseNumber = $(this).data('license-number');
    
    loadPartielleForLicense(licenseId, licenseNumber);
  });

  // Function to load PARTIELLE for a license
  function loadPartielleForLicense(licenseId, licenseNumber) {
    const row = licensesTable.rows(function(idx, data) {
      return data.id == licenseId;
    }).data()[0];
    
    if (!row) {
      Swal.fire('Error', 'Could not load license data', 'error');
      return;
    }
    
    $('#partielleListTitle').text('PARTIELLE for License: ' + licenseNumber);
    
    // Store license ID and number for export
    $('#partielleListBody').data('license-id', licenseId);
    $('#partielleListBody').data('license-number', licenseNumber);
    
    $('#partielleListBody').html(`
      <div class="license-summary-bar">
        <div class="license-item">
          <span class="license-label">CRF</span>
          <span class="license-value">${row.ref_cod || 'N/A'}</span>
        </div>
        <div class="license-item">
          <span class="license-label">Client</span>
          <span class="license-value">${row.client_name || 'N/A'}</span>
        </div>
        <div class="license-item">
          <span class="license-label">AV Wt</span>
          <span class="license-value" id="totalAvWeight">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">AV FOB</span>
          <span class="license-value" id="totalAvFob">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">AV Frt</span>
          <span class="license-value" id="totalAvFreight">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">AV Ins</span>
          <span class="license-value" id="totalAvInsurance">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">AV Other</span>
          <span class="license-value" id="totalAvOther">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">Used Wt</span>
          <span class="license-value" id="totalUsedWeight">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">Used FOB</span>
          <span class="license-value" id="totalUsedFob">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">Rem Wt</span>
          <span class="license-value" id="totalRemainingWeight">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">Rem FOB</span>
          <span class="license-value" id="totalRemainingFob">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">License Wt - Tot AV Wt</span>
          <span class="license-value" id="licWtMinusTotAvWt">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">License FOB - Tot AV FOB</span>
          <span class="license-value" id="licFobMinusTotAvFob">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
        <div class="license-item">
          <span class="license-label">Files</span>
          <span class="license-value" id="totalFiles">
            <div class="spinner-border spinner-border-sm"></div>
          </span>
        </div>
      </div>
      <div class="table-responsive">
        <table id="partielleListTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>PARTIELLE Name</th>
              <th>AV Weight</th>
              <th>AV FOB</th>
              <th>Used Weight</th>
              <th>Used FOB</th>
              <th>Remaining Wt</th>
              <th>Remaining FOB</th>
              <th>Files</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="partielleListBody2">
            <tr>
              <td colspan="10" class="text-center">
                <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;"></div>
                <p class="mt-2 mb-0 text-muted" style="font-size: 0.8rem;">Loading PARTIELLE...</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    `);
    
    $('#partielleListSection').addClass('show');
    $('#filesDetailsSection').removeClass('show');
    
    // Scroll to the PARTIELLE section
    $('html, body').animate({
      scrollTop: $('#partielleListSection').offset().top - 20
    }, 400);
    
    // Load PARTIELLE data
    $.ajax({
      url: '<?= APP_URL ?>/bivac/crudData/getPartielleByLicense',
      method: 'GET',
      data: { license_id: licenseId },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          let html = '';
          let totalFilesCount = 0;
          let totalAvWeightSum = 0;
          let totalAvFobSum = 0;
          let totalAvFreightSum = 0;
          let totalAvInsuranceSum = 0;
          let totalAvOtherSum = 0;
          let totalUsedWeightSum = 0;
          let totalUsedFobSum = 0;
          let totalRemainingWeightSum = 0;
          let totalRemainingFobSum = 0;
          
          res.data.forEach(function(p) {
            const avWeight = parseFloat(p.partial_weight || 0);
            const avFob = parseFloat(p.partial_fob || 0);
            const avFreight = parseFloat(p.partial_freight || 0);
            const avInsurance = parseFloat(p.partial_insurance || 0);
            const avOther = parseFloat(p.partial_other_costs || 0);
            const usedWeight = parseFloat(p.used_weight || 0);
            const usedFob = parseFloat(p.used_fob || 0);
            const remainingWt = parseFloat(p.partial_weight_usedweight || 0);
            const remainingFob = parseFloat(p.partial_fob_usedfob || 0);
            const importCount = parseInt(p.import_count || 0);
            
            // Sum up totals
            totalFilesCount += importCount;
            totalAvWeightSum += avWeight;
            totalAvFobSum += avFob;
            totalAvFreightSum += avFreight;
            totalAvInsuranceSum += avInsurance;
            totalAvOtherSum += avOther;
            totalUsedWeightSum += usedWeight;
            totalUsedFobSum += usedFob;
            totalRemainingWeightSum += remainingWt;
            totalRemainingFobSum += remainingFob;
            
            html += `
              <tr>
                <td>${p.id}</td>
                <td><strong>${p.partial_name || 'N/A'}</strong></td>
                <td>${avWeight.toFixed(2)} KG</td>
                <td>${avFob.toFixed(2)}</td>
                <td>${usedWeight.toFixed(2)} KG</td>
                <td>${usedFob.toFixed(2)}</td>
                <td>${remainingWt.toFixed(2)} KG</td>
                <td>${remainingFob.toFixed(2)}</td>
                <td>
                  ${importCount > 0 ? 
                    `<span class="count-badge has-records view-files-btn" 
                      data-partial-name="${p.partial_name}" 
                      data-count="${importCount}"
                      data-av-weight="${avWeight.toFixed(2)}"
                      data-av-fob="${avFob.toFixed(2)}"
                      data-used-weight="${usedWeight.toFixed(2)}"
                      data-used-fob="${usedFob.toFixed(2)}"
                      data-remaining-wt="${remainingWt.toFixed(2)}"
                      data-remaining-fob="${remainingFob.toFixed(2)}">${importCount}</span>` 
                    : 
                    `<span class="count-badge no-records">0</span>`
                  }
                </td>
                <td>
                  <button class="btn btn-sm btn-primary editPartielleBtn" data-id="${p.id}" title="Edit"><i class="ti ti-edit"></i></button>
                </td>
              </tr>
            `;
          });
          
          // Calculate License - Total AV
          const licWeight = parseFloat(row.weight || 0);
          const licFob = parseFloat(row.fob_declared || 0);
          const licWtMinusTotAv = licWeight - totalAvWeightSum;
          const licFobMinusTotAv = licFob - totalAvFobSum;
          
          // Update the summary counts
          $('#totalAvWeight').html(`<span style="font-weight: 800;">${totalAvWeightSum.toFixed(2)} KG</span>`);
          $('#totalAvFob').html(`<span style="font-weight: 800;">${totalAvFobSum.toFixed(2)}</span>`);
          $('#totalAvFreight').html(`<span style="font-weight: 800;">${totalAvFreightSum.toFixed(2)}</span>`);
          $('#totalAvInsurance').html(`<span style="font-weight: 800;">${totalAvInsuranceSum.toFixed(2)}</span>`);
          $('#totalAvOther').html(`<span style="font-weight: 800;">${totalAvOtherSum.toFixed(2)}</span>`);
          $('#totalUsedWeight').html(`<span style="font-weight: 800;">${totalUsedWeightSum.toFixed(2)} KG</span>`);
          $('#totalUsedFob').html(`<span style="font-weight: 800;">${totalUsedFobSum.toFixed(2)}</span>`);
          $('#totalRemainingWeight').html(`<span style="font-weight: 800;">${totalRemainingWeightSum.toFixed(2)} KG</span>`);
          $('#totalRemainingFob').html(`<span style="font-weight: 800;">${totalRemainingFobSum.toFixed(2)}</span>`);
          $('#licWtMinusTotAvWt').html(`<span style="font-weight: 800;">${licWtMinusTotAv.toFixed(2)} KG</span>`);
          $('#licFobMinusTotAvFob').html(`<span style="font-weight: 800;">${licFobMinusTotAv.toFixed(2)}</span>`);
          $('#totalFiles').html(`<span style="font-weight: 800;">${totalFilesCount}</span>`);
          
          $('#partielleListBody2').html(html);
        } else {
          // Update counts to 0
          $('#totalAvWeight, #totalAvFob, #totalAvFreight, #totalAvInsurance, #totalAvOther, #totalUsedWeight, #totalUsedFob, #totalRemainingWeight, #totalRemainingFob, #licWtMinusTotAvWt, #licFobMinusTotAvFob, #totalFiles').html('<span style="color: #6c757d; font-weight: 700;">0.00</span>');
          
          $('#partielleListBody2').html(`
            <tr>
              <td colspan="10">
                <div class="empty-partielle-state">
                  <i class="ti ti-file-off"></i>
                  <p>No PARTIELLE found for this license</p>
                </div>
              </td>
            </tr>
          `);
        }
      },
      error: function() {
        $('#totalAvWeight, #totalAvFob, #totalAvFreight, #totalAvInsurance, #totalAvOther, #totalUsedWeight, #totalUsedFob, #totalRemainingWeight, #totalRemainingFob, #licWtMinusTotAvWt, #licFobMinusTotAvFob, #totalFiles').html('<span style="color: #e74c3c; font-weight: 700;">Error</span>');
        
        $('#partielleListBody2').html(`
          <tr>
            <td colspan="10">
              <div class="empty-partielle-state">
                <i class="ti ti-alert-circle"></i>
                <p>Failed to load PARTIELLE</p>
              </div>
            </td>
          </tr>
        `);
      }
    });
  }

  // ✅ EXPORT HANDLER 2: Export PARTIELLE (Server-Side)
  $('#exportPartielleBtn').on('click', function() {
    const licenseId = $('#partielleListBody').data('license-id');
    if (!licenseId) {
      Swal.fire('Error', 'No license selected', 'error');
      return;
    }
    window.location.href = '<?= APP_URL ?>/bivac/exportPartielle?license_id=' + licenseId;
  });

  // Close PARTIELLE list section
  $('#closePartielleList').on('click', function() {
    $('#partielleListSection').removeClass('show');
    $('#filesDetailsSection').removeClass('show');
  });

  // View files for a PARTIELLE
  $(document).on('click', '.view-files-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const $btn = $(this);
    const partialName = $btn.data('partial-name');
    const count = $btn.data('count');
    const avWeight = $btn.data('av-weight');
    const avFob = $btn.data('av-fob');
    const usedWeight = $btn.data('used-weight');
    const usedFob = $btn.data('used-fob');
    const remainingWt = $btn.data('remaining-wt');
    const remainingFob = $btn.data('remaining-fob');
    
    $('#filesDetailsTitle').text('Files for: ' + partialName + ' (' + count + ' record(s))');
    
    // Store partial name for export
    $('#filesDetailsBody').data('partial-name', partialName);
    
    $('#filesDetailsBody').html(`
      <div class="av-summary-bar">
        <div class="av-item">
          <span class="av-label">AV Weight</span>
          <span class="av-value">${avWeight} KG</span>
        </div>
        <div class="av-item">
          <span class="av-label">AV FOB</span>
          <span class="av-value">${avFob}</span>
        </div>
        <div class="av-item">
          <span class="av-label">Used Weight</span>
          <span class="av-value">${usedWeight} KG</span>
        </div>
        <div class="av-item">
          <span class="av-label">Used FOB</span>
          <span class="av-value">${usedFob}</span>
        </div>
        <div class="av-item">
          <span class="av-label">Remaining Weight</span>
          <span class="av-value">${remainingWt} KG</span>
        </div>
        <div class="av-item">
          <span class="av-label">Remaining FOB</span>
          <span class="av-value">${remainingFob}</span>
        </div>
      </div>
      <div class="table-responsive">
        <table id="filesDetailsTable" class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>#</th>
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
          <tbody id="filesDetailsBody2">
            <tr>
              <td colspan="11" class="text-center">
                <div class="spinner-border text-primary" style="width: 1.5rem; height: 1.5rem;"></div>
                <p class="mt-2 mb-0 text-muted" style="font-size: 0.8rem;">Loading files...</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    `);
    
    $('#filesDetailsSection').addClass('show');
    
    // Scroll to the files section
    $('html, body').animate({
      scrollTop: $('#filesDetailsSection').offset().top - 20
    }, 400);
    
    // Load files data
    $.ajax({
      url: '<?= APP_URL ?>/bivac/crudData/getImportFiles',
      method: 'GET',
      data: { partial_name: partialName },
      dataType: 'json',
      success: function(res) {
        if (res.success && res.data && res.data.length > 0) {
          let html = '';
          res.data.forEach(function(file, index) {
            html += `
              <tr>
                <td><strong>${index + 1}</strong></td>
                <td>${file.mca_ref || 'N/A'}</td>
                <td>${file.inspection_reports || 'N/A'}</td>
                <td>${file.declaration_reference || 'N/A'}</td>
                <td>${formatDate(file.dgda_in_date)}</td>
                <td>${file.liquidation_reference || 'N/A'}</td>
                <td>${formatDate(file.liquidation_date)}</td>
                <td>${file.quittance_reference || 'N/A'}</td>
                <td>${formatDate(file.quittance_date)}</td>
                <td><strong>${parseFloat(file.weight || 0).toFixed(2)} KG</strong></td>
                <td><strong>${parseFloat(file.fob || 0).toFixed(2)}</strong></td>
              </tr>
            `;
          });
          $('#filesDetailsBody2').html(html);
        } else {
          $('#filesDetailsBody2').html(`
            <tr>
              <td colspan="11">
                <div class="empty-files-state">
                  <i class="ti ti-file-off"></i>
                  <p>No files found</p>
                </div>
              </td>
            </tr>
          `);
        }
      },
      error: function() {
        $('#filesDetailsBody2').html(`
          <tr>
            <td colspan="11">
              <div class="empty-files-state">
                <i class="ti ti-alert-circle"></i>
                <p>Failed to load files</p>
              </td>
            </tr>
          `);
        }
      });
    });

  // ✅ EXPORT HANDLER 3: Export Files (Server-Side)
  $('#exportFilesBtn').on('click', function() {
    const partialName = $('#filesDetailsBody').data('partial-name');
    if (!partialName) {
      Swal.fire('Error', 'No PARTIELLE selected', 'error');
      return;
    }
    window.location.href = '<?= APP_URL ?>/bivac/exportFiles?partial_name=' + encodeURIComponent(partialName);
  });

  // Close files details section
  $('#closeFilesDetails').on('click', function() {
    $('#filesDetailsSection').removeClass('show');
  });

  // Edit PARTIELLE
  $(document).on('click', '.editPartielleBtn', function() {
    const id = $(this).data('id');
    Swal.fire({ title: 'Loading...', didOpen: () => Swal.showLoading() });
    
    $.get('<?= APP_URL ?>/bivac/crudData/getPartielle', { id }, function(res) {
      Swal.close();
      if (res.success && res.data) {
        const d = res.data;
        
        $('#partielle_id').val(d.id);
        $('#editingPartielle').text('(' + d.partial_name + ')');
        
        $('#info_partial_name').text(d.partial_name || '');
        $('#info_license_number').text(d.license_number || '');
        $('#info_crf').text(d.ref_cod || '');
        $('#info_client').text(d.client_name || '');

        // License values (readonly)
        $('#license_weight_display').val(parseFloat(d.license_weight||0).toFixed(2));
        $('#license_fob_display').val(parseFloat(d.license_fob||0).toFixed(2));
        $('#license_insurance_display').val(parseFloat(d.license_insurance||0).toFixed(2));
        $('#license_freight_display').val(parseFloat(d.license_freight||0).toFixed(2));
        $('#license_other_costs_display').val(parseFloat(d.license_other_costs||0).toFixed(2));

        // AV values (editable)
        $('#partial_weight').val(parseFloat(d.partial_weight||0).toFixed(2));
        $('#partial_fob').val(parseFloat(d.partial_fob||0).toFixed(2));
        $('#partial_insurance').val(parseFloat(d.partial_insurance||0).toFixed(2));
        $('#partial_freight').val(parseFloat(d.partial_freight||0).toFixed(2));
        $('#partial_other_costs').val(parseFloat(d.partial_other_costs||0).toFixed(2));

        // Used values (readonly - from imports)
        $('#used_weight_display').val(parseFloat(d.used_weight||0).toFixed(2));
        $('#used_fob_display').val(parseFloat(d.used_fob||0).toFixed(2));

        // Calculated values (readonly)
        $('#licenseweight_partial_weight_display').val(parseFloat(d.licenseweight_partial_weight||0).toFixed(2));
        $('#licensefob_partial_fob_display').val(parseFloat(d.licensefob_partial_fob||0).toFixed(2));
        $('#partial_weight_usedweight_display').val(parseFloat(d.partial_weight_usedweight||0).toFixed(2));
        $('#partial_fob_usedfob_display').val(parseFloat(d.partial_fob_usedfob||0).toFixed(2));

        $('#editFormCard').slideDown(400);
        $('html, body').animate({ scrollTop: $('#editFormCard').offset().top - 20 }, 400);
      } else {
        Swal.fire('Error', res.message || 'Failed to load', 'error');
      }
    }, 'json').fail(() => {
      Swal.close();
      Swal.fire('Error', 'Failed to load data', 'error');
    });
  });

  // Submit Form
  $('#partielleForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#partielle_id').val();
    if (!id) {
      Swal.fire('Error', 'No PARTIELLE selected', 'error');
      return;
    }

    const btn = $('#submitBtn');
    const orig = btn.html();
    btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Updating...');

    const formData = new FormData(this);
    formData.set('csrf_token', csrfToken);
    
    $.ajax({
      url: '<?= APP_URL ?>/bivac/crudData/update',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        btn.prop('disabled', false).html(orig);
        if (res.success) {
          Swal.fire({ 
            icon: 'success', 
            title: 'Success!', 
            text: res.message, 
            timer: 2000, 
            showConfirmButton: false 
          });
          hideForm();
          licensesTable.ajax.reload(null, false);
          
          // Refresh PARTIELLE list if open
          if ($('#partielleListSection').hasClass('show')) {
            const licenseId = $('#partielleListBody').data('license-id');
            const licenseNumber = $('#partielleListBody').data('license-number');
            if (licenseId) {
              loadPartielleForLicense(licenseId, licenseNumber);
            }
          }
        } else {
          Swal.fire('Error', res.message || 'Update failed', 'error');
        }
      },
      error: function(xhr) {
        btn.prop('disabled', false).html(orig);
        Swal.fire('Error', xhr.status === 403 ? 'Token expired. Refresh page.' : 'Server error', 'error');
      }
    });
  });

});
</script>