<link href="<?= BASE_URL ?>/assets/pages/css/local_styles.css" rel="stylesheet" type="text/css">

<style>
  /* Modern Color Palette */
  :root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
    --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  }

  /* Base Styles */
  .page-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 20px 0;
  }
  
  /* Card Styles */
  .card { 
    border: none; 
    border-radius: 16px; 
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    background: white;
    margin-bottom: 24px;
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

  /* Client Selection Modal */
  .modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
  }

  .modal-header {
    background: var(--primary-gradient);
    color: white;
    border-bottom: none;
    padding: 20px 24px;
    border-radius: 16px 16px 0 0;
  }

  .modal-header .modal-title {
    font-size: 1.2rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
  }

  .modal-header .btn-close:hover {
    opacity: 1;
  }

  .modal-body {
    padding: 24px;
    background: #f8f9fa;
    max-height: 70vh;
    overflow-y: auto;
  }

  /* Search Box */
  .client-search-box {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa;
    padding-bottom: 15px;
    margin-bottom: 15px;
  }

  .search-input-wrapper {
    position: relative;
  }

  .search-input-wrapper i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 1.1rem;
  }

  #clientSearchInput {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
  }

  #clientSearchInput:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  #clientSearchInput::placeholder {
    color: #999;
  }

  /* Pending Files Modal */
  .pending-modal .modal-dialog {
    max-width: 98%;
    margin: 1.75rem auto;
  }

  .pending-modal .modal-header {
    background: var(--warning-gradient);
    color: #000;
  }

  .pending-modal .modal-body {
    padding: 0;
    background: white;
    max-height: calc(100vh - 250px);
    overflow: hidden;
  }

  /* Edit Modal Styles */
  .edit-modal .modal-dialog {
    max-width: 600px;
  }

  .edit-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .edit-modal .modal-body {
    padding: 30px;
    background: white;
  }

  /* New Transmission Modal */
  .transmission-modal .modal-dialog {
    max-width: 95%;
    margin: 1.75rem auto;
  }

  .transmission-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
  }

  .transmission-modal .modal-body {
    padding: 30px;
    background: white;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    display: block;
    font-size: 0.9rem;
  }

  .form-group label .required {
    color: #dc3545;
    margin-left: 3px;
  }

  .form-control, .form-select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
  }

  .form-control:focus, .form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .btn-save, .btn-valider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-save:hover, .btn-valider:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-cancel, .btn-annuler {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 30px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-cancel:hover, .btn-annuler:hover {
    background: #c82333;
    color: white;
  }

  /* Table Container with Horizontal Scroll */
  .table-container {
    position: relative;
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
    max-height: calc(100vh - 250px);
    border: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
  }

  /* Custom Scrollbar */
  .table-container::-webkit-scrollbar {
    height: 12px;
    width: 12px;
  }

  .table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .table-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
  }

  .table-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  }

  /* Table Styles */
  #pendingFilesTable {
    margin-bottom: 0;
    font-size: 0.85rem;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  #pendingFilesTable thead {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #ffffff;
  }

  #pendingFilesTable thead th {
    background: linear-gradient(135deg, #4472C4 0%, #2E5090 100%);
    color: #ffffff;
    font-weight: 700;
    border: 1px solid #2E5090;
    padding: 14px 10px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
  }

  /* Column specific widths - ALL 26 COLUMNS */
  #pendingFilesTable th:nth-child(1) { min-width: 50px; }
  #pendingFilesTable th:nth-child(2) { min-width: 140px; }
  #pendingFilesTable th:nth-child(3) { min-width: 120px; }
  #pendingFilesTable th:nth-child(4) { min-width: 110px; }
  #pendingFilesTable th:nth-child(5) { min-width: 110px; }
  #pendingFilesTable th:nth-child(6) { min-width: 110px; }
  #pendingFilesTable th:nth-child(7) { min-width: 120px; }
  #pendingFilesTable th:nth-child(8) { min-width: 110px; }
  #pendingFilesTable th:nth-child(9) { min-width: 130px; }
  #pendingFilesTable th:nth-child(10) { min-width: 120px; }
  #pendingFilesTable th:nth-child(11) { min-width: 130px; }
  #pendingFilesTable th:nth-child(12) { min-width: 120px; }
  #pendingFilesTable th:nth-child(13) { min-width: 130px; }
  #pendingFilesTable th:nth-child(14) { min-width: 120px; }
  #pendingFilesTable th:nth-child(15) { min-width: 90px; }
  #pendingFilesTable th:nth-child(16) { min-width: 120px; }
  #pendingFilesTable th:nth-child(17) { min-width: 120px; }
  #pendingFilesTable th:nth-child(18) { min-width: 110px; }
  #pendingFilesTable th:nth-child(19) { min-width: 130px; }
  #pendingFilesTable th:nth-child(20) { min-width: 120px; }
  #pendingFilesTable th:nth-child(21) { min-width: 120px; }
  #pendingFilesTable th:nth-child(22) { min-width: 120px; }
  #pendingFilesTable th:nth-child(23) { min-width: 120px; }
  #pendingFilesTable th:nth-child(24) { min-width: 100px; }
  #pendingFilesTable th:nth-child(25) { min-width: 150px; }
  #pendingFilesTable th:nth-child(26) { min-width: 100px; }

  #pendingFilesTable tbody tr {
    border-bottom: 1px solid #e9ecef;
    background: #ffffff;
  }

  #pendingFilesTable tbody tr:nth-child(even) {
    background: #f8f9fa;
  }

  #pendingFilesTable tbody tr:hover {
    background: #e3f2fd;
  }

  #pendingFilesTable tbody td {
    padding: 12px 10px;
    vertical-align: middle;
    font-size: 0.82rem;
    white-space: nowrap;
    border: 1px solid #e9ecef;
  }

  /* Column Alignments */
  #pendingFilesTable td:nth-child(1) {
    text-align: center;
    font-weight: 600;
    color: #667eea;
  }

  #pendingFilesTable td:nth-child(4),
  #pendingFilesTable td:nth-child(5),
  #pendingFilesTable td:nth-child(6),
  #pendingFilesTable td:nth-child(7),
  #pendingFilesTable td:nth-child(8),
  #pendingFilesTable td:nth-child(18),
  #pendingFilesTable td:nth-child(20) {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 600;
  }

  #pendingFilesTable td:nth-child(26) {
    text-align: center;
  }

  /* Edit Button */
  .btn-edit {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 6px 15px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
  }

  .btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
    color: white;
  }

  /* Client List */
  .client-item {
    padding: 15px 20px;
    background: white;
    border-left: 4px solid #667eea;
    margin-bottom: 10px;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
  }

  .client-item:hover {
    background: #f8f9fa;
    border-left-color: #764ba2;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .client-item.hidden {
    display: none;
  }

  .client-name {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .client-name i {
    color: #667eea;
    font-size: 1.2rem;
  }

  .client-company {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 4px;
    padding-left: 32px;
  }

  /* No Results Message */
  .no-results-message {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
  }

  .no-results-message i {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 15px;
  }

  .no-results-message.hidden {
    display: none;
  }

  /* Page Header */
  .page-header {
    background: var(--primary-gradient);
    color: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .page-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .client-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Statistics Cards */
  .stats-card {
    border: none;
    border-radius: 15px;
    min-height: 140px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
  }
  
  .stats-card-pending {
    background: var(--warning-gradient);
    color: #000;
  }
  
  .stats-card-transmission {
    background: var(--info-gradient);
    color: white;
  }
  
  .stats-card-no-receipt {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
  }
  
  .stats-card-apured {
    background: var(--success-gradient);
    color: white;
  }
  
  .stats-card .card-body {
    padding: 25px 20px;
    position: relative;
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
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
  }
  
  .stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
    position: relative;
    z-index: 1;
  }

  /* Buttons */
  .btn-change-client {
    background: white;
    color: #667eea;
    border: none;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
  }
  
  .btn-change-client:hover {
    background: #f8f9fa;
    color: #667eea;
    transform: translateY(-2px);
  }

  .btn-export-excel {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    transition: all 0.3s ease;
  }

  .btn-export-excel:hover {
    background: #218838;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
  }

  /* NEW TABLE SECTION STYLES */
  .table-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-top: 20px;
  }

  .table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
  }

  .table-header-left {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .table-header-right {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .btn-filter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-new-transmission {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-new-transmission:hover {
    background: #138496;
    transform: translateY(-2px);
    color: white;
  }

  .btn-export {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-export:hover {
    background: #218838;
    transform: translateY(-2px);
    color: white;
  }

  .search-box {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .search-input {
    padding: 8px 15px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.9rem;
    width: 250px;
  }

  .search-input:focus {
    outline: none;
    border-color: #667eea;
  }

  /* Data Table */
  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }

  .data-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 12px 10px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.85rem;
    white-space: nowrap;
  }

  .data-table tbody td {
    padding: 12px 10px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.85rem;
  }

  .data-table tbody tr:hover {
    background: #f8f9fa;
  }

  /* Transmission Table Styles */
  .transmission-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #2d3748;
  }

  .transmission-table thead th {
    background: #2d3748;
    color: white;
    font-weight: 600;
    padding: 14px 10px;
    text-align: left;
    border: 1px solid #4a5568;
    font-size: 0.85rem;
  }

  .transmission-table tbody td {
    background: #2d3748;
    color: white;
    padding: 12px 10px;
    border: 1px solid #4a5568;
    font-size: 0.85rem;
  }

  .client-name-red {
    color: #dc3545;
    font-weight: 600;
  }

  .reference-number {
    color: #007bff;
    font-weight: 500;
  }

  .action-buttons {
    display: flex;
    gap: 5px;
  }

  .btn-action {
    padding: 6px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s ease;
  }

  .btn-action:hover {
    transform: translateY(-2px);
  }

  .btn-download {
    background: #28a745;
    color: white;
  }

  .btn-view {
    background: #6f42c1;
    color: white;
  }

  /* Pagination */
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
  }

  .pagination-info {
    color: #6c757d;
    font-size: 0.9rem;
  }

  .pagination {
    display: flex;
    gap: 5px;
  }

  .page-btn {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s ease;
  }

  .page-btn:hover {
    background: #f8f9fa;
  }

  .page-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
  }

  .page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  /* Loading Spinner */
  .spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
  }

  .loading-container {
    padding: 40px;
    text-align: center;
  }

  /* Modal Footer */
  .modal-footer {
    background: #f8f9fa;
    border-top: 2px solid #dee2e6;
    padding: 15px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .total-records {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
  }

  /* Form Row for Inline Fields */
  .form-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .page-header {
      flex-direction: column;
      text-align: center;
      gap: 15px;
    }

    .page-title {
      font-size: 1.5rem;
    }

    .stats-value {
      font-size: 2rem;
    }

    .table-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .form-row {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .page-header {
      padding: 15px;
    }

    .stats-card .card-body {
      padding: 20px 15px;
    }

    .stats-icon {
      font-size: 2.5rem;
    }

    .search-input {
      width: 100%;
    }

    .data-table {
      font-size: 0.75rem;
    }

    .data-table thead th,
    .data-table tbody td {
      padding: 8px 5px;
    }
  }
</style>

<div class="page-content">
  <div class="page-container">
    
    <!-- Client Selection Modal -->
    <div class="modal fade" id="clientSelectionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ti ti-building me-2"></i> Select a Client
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Search Box -->
            <div class="client-search-box">
              <div class="search-input-wrapper">
                <i class="ti ti-search"></i>
                <input type="text" 
                       id="clientSearchInput" 
                       class="form-control" 
                       placeholder="Search by client name or code...">
              </div>
            </div>

            <!-- Client List Container -->
            <div id="clientListContainer">
              <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading clients...</p>
              </div>
            </div>

            <!-- No Results Message -->
            <div class="no-results-message hidden" id="noResultsMessage">
              <i class="ti ti-mood-sad"></i>
              <p class="mb-0"><strong>No clients found</strong></p>
              <p class="small text-muted">Try adjusting your search</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Files Modal (ONLY SHOWS FILES WITH QUITTANCE_DATE & PENDING STATUS) -->
    <div class="modal fade pending-modal" id="pendingFilesModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ti ti-clock-pause me-2"></i> Files Ready for Clearance - <span id="modalClientName"></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info mb-3">
              <i class="ti ti-info-circle me-2"></i> 
              <strong>Note:</strong> Only showing import files that have completed customs clearance (have Quittance) and are pending bank transmission.
            </div>
            <div class="table-container">
              <table id="pendingFilesTable">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>NUM LICENCE</th>
                    <th>MCA REF</th>
                    <th>FOB</th>
                    <th>FRET</th>
                    <th>ASSURANCE</th>
                    <th>AUTRES FRETS</th>
                    <th>CIF</th>
                    <th>REF DECL</th>
                    <th>DATE DECL</th>
                    <th>REF LIQUID</th>
                    <th>DATE LIQUID</th>
                    <th>REF QUIT</th>
                    <th>DATE QUIT</th>
                    <th>MONNAIE</th>
                    <th>DATE VAL</th>
                    <th>DATE ECH</th>
                    <th>CIF LIC</th>
                    <th>NUM AV</th>
                    <th>MONTANT AV</th>
                    <th>ASS REF</th>
                    <th>FACTURE</th>
                    <th>BL/LTA</th>
                    <th>Type</th>
                    <th>Remarque</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody id="pendingFilesTableBody">
                  <tr>
                    <td colspan="26">
                      <div class="loading-container">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 mb-0">Loading pending files...</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <div class="total-records">
              Total Records: <strong id="totalRecords">0</strong>
            </div>
            <div>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="ti ti-x me-1"></i> Close
              </button>
              <button type="button" class="btn btn-export-excel" id="exportPendingBtn">
                <i class="ti ti-file-spreadsheet me-1"></i> Export to Excel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Import File Modal -->
    <div class="modal fade edit-modal" id="editImportFileModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ti ti-edit me-2"></i> Edit Import File
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="editImportFileForm">
            <input type="hidden" id="editImportId" name="import_id">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="modal-body">
              <div class="form-group">
                <label for="editAssRef">
                  ASS REF
                  <span class="required">*</span>
                </label>
                <input type="text" 
                       class="form-control" 
                       id="editAssRef" 
                       name="ass_ref" 
                       placeholder="Enter ASS REF"
                       required>
              </div>

              <div class="form-group">
                <label for="editType">
                  Type
                  <span class="required">*</span>
                </label>
                <select class="form-select" id="editType" name="clearance_type" required>
                  <option value="">-- Select Type --</option>
                  <option value="Partial">Partial</option>
                  <option value="Total">Total</option>
                </select>
              </div>

              <div class="form-group">
                <label for="editRemarque">
                  Remarque
                  <span class="required">*</span>
                </label>
                <select class="form-select" id="editRemarque" name="clearance_remarks" required>
                  <option value="">-- Select Remarque --</option>
                  <option value="AV Provissional">AV Provissional</option>
                  <option value="License Provissional">License Provissional</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                <i class="ti ti-x me-1"></i> Cancel
              </button>
              <button type="submit" class="btn btn-save">
                <i class="ti ti-device-floppy me-1"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- NEW TRANSMISSION MODAL -->
    <div class="modal fade transmission-modal" id="newTransmissionModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ti ti-plus me-2"></i> Nouvelle Transmission Apurement IB | <span id="transmissionClientName">KAMOA</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="newTransmissionForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" id="transmissionClientId" name="client_id">
            
            <div class="modal-body">
              <!-- Form Fields Row -->
              <div class="form-row">
                <div class="form-group">
                  <label for="transBanque">Banque</label>
                  <select class="form-select" id="transBanque" name="banque" required>
                    <option value="">-- Select Bank --</option>
                    <!-- Banks loaded dynamically from database -->
                  </select>
                </div>

                <div class="form-group">
                  <label for="transReference">Reference</label>
                  <input type="text" class="form-control" id="transReference" name="reference" placeholder="0276-IB-26" required>
                </div>

                <div class="form-group">
                  <label for="transDate">Date</label>
                  <input type="date" class="form-control" id="transDate" name="date" required>
                </div>

                <div class="form-group">
                  <label for="transLicence">Licence</label>
                  <select class="form-select" id="transLicence" name="licence" required>
                    <option value="">-- Select --</option>
                  </select>
                </div>
              </div>

              <!-- Transmission Table -->
              <table class="transmission-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>MCA Ref.</th>
                    <th>Licence</th>
                    <th>Montant Decl.</th>
                    <th>Ref. Decl.</th>
                    <th>Ref. Liq.</th>
                    <th>Ref. Quit.</th>
                  </tr>
                </thead>
                <tbody id="transmissionTableBody">
                  <tr>
                    <td colspan="7" class="text-center p-4">
                      <i class="ti ti-info-circle me-2"></i> Select a licence to load import files
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-annuler" data-bs-dismiss="modal">
                Annuler
              </button>
              <button type="submit" class="btn btn-valider">
                Valider
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="ti ti-file-text me-2"></i> Import Files Details - <span id="detailsLicenseNumber"></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="detailsModalBody">
            <div class="text-center p-4">
              <div class="spinner-border text-primary"></div>
              <p class="mt-2">Loading details...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard -->
    <div id="mainDashboard" style="display: none;">
      
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h4 class="page-title">
            <i class="ti ti-file-check me-2"></i> IMPORT LICENSE CLEARANCE (IB)
          </h4>
          <div class="client-badge mt-2">
            <i class="ti ti-building me-2"></i> <span id="selectedClientName">-</span>
          </div>
        </div>
        <div>
          <button type="button" class="btn btn-change-client" id="changeClientBtn">
            <i class="ti ti-refresh me-1"></i> Change Client
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card stats-card stats-card-pending" id="pendingCard">
            <div class="card-body">
              <i class="ti ti-clock-pause stats-icon"></i>
              <div class="stats-value" id="statPending">0</div>
              <div class="stats-label">Pending Clearance</div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card stats-card stats-card-transmission">
            <div class="card-body">
              <i class="ti ti-send stats-icon"></i>
              <div class="stats-value" id="statPartial">0</div>
              <div class="stats-label">Partial Clearance</div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card stats-card stats-card-no-receipt">
            <div class="card-body">
              <i class="ti ti-alert-circle stats-icon"></i>
              <div class="stats-value" id="statTotal">0</div>
              <div class="stats-label">Total Import Files</div>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="card stats-card stats-card-apured">
            <div class="card-body">
              <i class="ti ti-check stats-icon"></i>
              <div class="stats-value" id="statApured">0</div>
              <div class="stats-label">Cleared Files</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TRANSMISSION LIST TABLE -->
      <div class="table-section">
        <div class="table-header">
          <div class="table-header-left">
            <button type="button" class="btn btn-filter" id="filterClientBtn">
              <i class="ti ti-filter me-1"></i> Filtrage Client
            </button>
          </div>
          <div class="table-header-right">
            <button type="button" class="btn btn-new-transmission" id="newTransmissionBtn">
              <i class="ti ti-plus me-1"></i> Nouvelle Transmission Apurement
            </button>
            <button type="button" class="btn btn-export" id="exportBtn">
              <i class="ti ti-download me-1"></i> Export
            </button>
          </div>
        </div>

        <div class="table-header">
          <div class="table-header-left">
            <button type="button" class="btn btn-action btn-download">
              <i class="ti ti-file-spreadsheet"></i>
            </button>
            <button type="button" class="btn btn-action btn-view">
              <i class="ti ti-list"></i>
            </button>
          </div>
          <div class="table-header-right">
            <div class="search-box">
              <label>Search:</label>
              <input type="text" class="search-input" id="tableSearch" placeholder="Search...">
            </div>
          </div>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Client</th>
              <th>Reference</th>
              <th>Date Creation</th>
              <th>Date Depot</th>
              <th>Banque</th>
              <th>Nbre. Licences</th>
              <th>Nbre. Dossiers</th>
              <th style="width: 200px;">Actions</th>
            </tr>
          </thead>
          <tbody id="dataTableBody">
            <tr>
              <td colspan="9" class="text-center p-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Loading transmissions...</p>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="pagination-container">
          <div class="pagination-info" id="paginationInfo">
            Showing 0 to 0 of 0 entries
          </div>
          <div class="pagination" id="paginationButtons">
            <!-- Pagination buttons -->
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let selectedClientId = 0;
let selectedClientName = '';
let currentPage = 1;
let perPage = 10;
let searchTimeout = null;

$(document).ready(function() {
  $('#clientSelectionModal').modal('show');
  loadClients();
  initializeEventHandlers();
  
  // Set today's date as default
  const today = new Date().toISOString().split('T')[0];
  $('#transDate').val(today);
});

function initializeEventHandlers() {
  $(document).on('click', '.client-item', handleClientSelection);
  $('#changeClientBtn, #filterClientBtn').on('click', showClientSelectionModal);
  $('#pendingCard').on('click', showPendingFilesModal);
  $('#exportPendingBtn').on('click', exportPendingFiles);
  $('#clientSearchInput').on('input', handleClientSearch);
  $('#tableSearch').on('input', handleTableSearch);
  $(document).on('click', '.btn-view', handleViewClick);
  $(document).on('click', '.page-btn', handlePageClick);
  $(document).on('click', '.btn-edit', handleEditClick);
  $('#editImportFileForm').on('submit', handleEditFormSubmit);
  
  // NEW TRANSMISSION HANDLERS
  $('#newTransmissionBtn').on('click', showNewTransmissionModal);
  $('#transLicence').on('change', loadTransmissionFiles);
  $('#newTransmissionForm').on('submit', handleNewTransmissionSubmit);
}

// CLIENT HANDLING
function loadClients() {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getClientsWithLicenses',
    method: 'GET',
    dataType: 'json',
    beforeSend: function() {
      $('#clientListContainer').html(`
        <div class="text-center p-4">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">Loading clients...</p>
        </div>
      `);
    },
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        renderClientList(res.data);
      } else {
        $('#clientListContainer').html(`
          <div class="alert alert-warning">
            <i class="ti ti-alert-circle me-2"></i> No clients with pending clearance found
          </div>
        `);
      }
    },
    error: function(xhr) {
      console.error('Load clients error:', xhr.responseText);
      $('#clientListContainer').html(`
        <div class="alert alert-danger">
          <i class="ti ti-alert-triangle me-2"></i> Error loading clients
        </div>
      `);
    }
  });
}

function renderClientList(clients) {
  let html = '';
  clients.forEach(function(client) {
    html += `
      <div class="client-item" 
           data-client-id="${client.id}" 
           data-client-name="${htmlEscape(client.short_name)}"
           data-client-code="${htmlEscape(client.short_name)}">
        <div class="client-name">
          <i class="ti ti-building"></i>
          <span>${htmlEscape(client.short_name)}</span>
        </div>
        <div class="client-company">${htmlEscape(client.company_name)}</div>
      </div>
    `;
  });
  $('#clientListContainer').html(html);
}

function handleClientSelection() {
  selectedClientId = parseInt($(this).data('client-id'));
  selectedClientName = $(this).data('client-name');
  
  if (selectedClientId > 0) {
    $('#selectedClientName').text(selectedClientName);
    $('#clientSelectionModal').modal('hide');
    $('#mainDashboard').show();
    $('#clientSearchInput').val('');
    loadDashboardData();
  }
}

function showClientSelectionModal() {
  $('#clientSearchInput').val('');
  $('.client-item').removeClass('hidden');
  $('#noResultsMessage').addClass('hidden');
  loadClients();
  $('#clientSelectionModal').modal('show');
}

function handleClientSearch() {
  const searchTerm = $(this).val().toLowerCase().trim();
  
  if (searchTerm === '') {
    $('.client-item').removeClass('hidden');
    $('#noResultsMessage').addClass('hidden');
    return;
  }
  
  let visibleCount = 0;
  
  $('.client-item').each(function() {
    const clientName = $(this).data('client-name').toLowerCase();
    const clientCode = $(this).data('client-code') ? $(this).data('client-code').toLowerCase() : '';
    
    if (clientName.includes(searchTerm) || clientCode.includes(searchTerm)) {
      $(this).removeClass('hidden');
      visibleCount++;
    } else {
      $(this).addClass('hidden');
    }
  });
  
  if (visibleCount === 0) {
    $('#noResultsMessage').removeClass('hidden');
  } else {
    $('#noResultsMessage').addClass('hidden');
  }
}

// DASHBOARD DATA
function loadDashboardData() {
  updateStatistics();
  loadTableData();
}

function updateStatistics() {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/statistics',
    method: 'GET',
    data: { client_id: selectedClientId },
    dataType: 'json',
    success: function(res) {
      console.log('Statistics Response:', res);
      if (res.success) {
        $('#statTotal').text(res.data.total_import_files || 0);
        $('#statPending').text(res.data.pending_files || 0);
        $('#statApured').text(res.data.apured_files || 0);
        $('#statPartial').text(res.data.partial_files || 0);
      }
    },
    error: function(xhr) {
      console.error('Statistics Error:', xhr.responseText);
    }
  });
}

// NEW TRANSMISSION MODAL
function showNewTransmissionModal() {
  $('#transmissionClientName').text(selectedClientName);
  $('#transmissionClientId').val(selectedClientId);
  
  // Load banks and licenses
  loadBanksForTransmission();
  loadLicensesForTransmission();
  
  // Reset form
  $('#newTransmissionForm')[0].reset();
  const today = new Date().toISOString().split('T')[0];
  $('#transDate').val(today);
  
  // Clear table
  $('#transmissionTableBody').html(`
    <tr>
      <td colspan="7" class="text-center p-4">
        <i class="ti ti-info-circle me-2"></i> Select a licence to load import files
      </td>
    </tr>
  `);
  
  $('#newTransmissionModal').modal('show');
}

function loadBanksForTransmission() {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getBanksList',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data) {
        let options = '<option value="">-- Select Bank --</option>';
        res.data.forEach(function(bank) {
          options += `<option value="${htmlEscape(bank.bank_name)}">${htmlEscape(bank.bank_name)}</option>`;
        });
        $('#transBanque').html(options);
      } else {
        $('#transBanque').html('<option value="">-- No Banks Available --</option>');
      }
    },
    error: function(xhr) {
      console.error('Load banks error:', xhr.responseText);
      $('#transBanque').html('<option value="">-- Error Loading Banks --</option>');
    }
  });
}

function loadLicensesForTransmission() {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getLicensesForClient',
    method: 'GET',
    data: { client_id: selectedClientId },
    dataType: 'json',
    success: function(res) {
      if (res.success && res.data) {
        let options = '<option value="">-- Select --</option>';
        res.data.forEach(function(item) {
          options += `<option value="${item.id}">${item.license_number}</option>`;
        });
        $('#transLicence').html(options);
      }
    }
  });
}

function loadTransmissionFiles() {
  const licenseId = $(this).val();
  
  if (!licenseId) {
    $('#transmissionTableBody').html(`
      <tr>
        <td colspan="7" class="text-center p-4">
          <i class="ti ti-info-circle me-2"></i> Select a licence to load import files
        </td>
      </tr>
    `);
    return;
  }
  
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getImportFileDetails',
    method: 'GET',
    data: { license_id: licenseId },
    dataType: 'json',
    beforeSend: function() {
      $('#transmissionTableBody').html(`
        <tr>
          <td colspan="7" class="text-center p-4">
            <div class="spinner-border spinner-border-sm text-light me-2"></div>
            Loading files...
          </td>
        </tr>
      `);
    },
    success: function(res) {
      if (res.success && res.data) {
        renderTransmissionTable(res.data);
      } else {
        $('#transmissionTableBody').html(`
          <tr>
            <td colspan="7" class="text-center p-4">No files found</td>
          </tr>
        `);
      }
    }
  });
}

function renderTransmissionTable(files) {
  let html = '';
  
  files.forEach(function(file, index) {
    html += `
      <tr>
        <td>${index + 1}</td>
        <td>${file.mca_ref || '-'}</td>
        <td>${file.license_number || '-'}</td>
        <td>${formatNumber(file.cif)}</td>
        <td>${file.declaration_reference || '-'}</td>
        <td>${file.liquidation_reference || '-'}</td>
        <td>${file.quittance_reference || '-'}</td>
      </tr>
    `;
  });
  
  $('#transmissionTableBody').html(html);
}

function handleNewTransmissionSubmit(e) {
  e.preventDefault();
  
  const formData = $(this).serialize();
  
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/createTransmission',
    method: 'POST',
    data: formData,
    dataType: 'json',
    beforeSend: function() {
      Swal.fire({
        title: 'Creating...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    },
    success: function(res) {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Transmission created successfully!',
          timer: 2000,
          showConfirmButton: false
        });
        
        $('#newTransmissionModal').modal('hide');
        loadTableData();
        updateStatistics();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: res.message || 'Failed to create transmission'
        });
      }
    },
    error: function(xhr) {
      console.error('Create transmission error:', xhr.responseText);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Server error occurred'
      });
    }
  });
}

// PENDING FILES MODAL (ONLY SHOWS FILES WITH QUITTANCE_DATE & PENDING STATUS)
function showPendingFilesModal() {
  $('#modalClientName').text(selectedClientName);
  $('#pendingFilesModal').modal('show');
  loadPendingFiles();
}

function loadPendingFiles() {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getPendingImportFiles',
    method: 'GET',
    data: { client_id: selectedClientId },
    dataType: 'json',
    beforeSend: function() {
      $('#pendingFilesTableBody').html(`
        <tr>
          <td colspan="26">
            <div class="loading-container">
              <div class="spinner-border text-primary"></div>
              <p class="mt-2 mb-0">Loading files...</p>
            </div>
          </td>
        </tr>
      `);
      $('#totalRecords').text('0');
    },
    success: function(res) {
      console.log('Pending Files Response:', res);
      if (res.success && res.data && res.data.length > 0) {
        renderPendingFilesTable(res.data);
        $('#totalRecords').text(res.data.length);
      } else {
        $('#pendingFilesTableBody').html(`
          <tr>
            <td colspan="26">
              <div class="loading-container">
                <i class="ti ti-info-circle" style="font-size: 3rem; color: #6c757d;"></i>
                <p class="mt-2 mb-0">No pending files found</p>
                <p class="text-muted small">All files have been cleared or don't have quittance yet</p>
              </div>
            </td>
          </tr>
        `);
        $('#totalRecords').text('0');
      }
    },
    error: function(xhr) {
      console.error('Pending Files Error:', xhr.responseText);
    }
  });
}

function renderPendingFilesTable(files) {
  let html = '';
  
  files.forEach(function(file, index) {
    html += `
      <tr>
        <td>${index + 1}</td>
        <td>${file.num_licence || '-'}</td>
        <td>${file.mca_ref || '-'}</td>
        <td>${formatNumber(file.fob)}</td>
        <td>${formatNumber(file.fret)}</td>
        <td>${formatNumber(file.assurance)}</td>
        <td>${formatNumber(file.autres_frets)}</td>
        <td>${formatNumber(file.cif)}</td>
        <td>${file.ref_decl || '-'}</td>
        <td>${file.date_decl ? formatDate(file.date_decl) : '-'}</td>
        <td>${file.ref_liquid || '-'}</td>
        <td>${file.date_liquid ? formatDate(file.date_liquid) : '-'}</td>
        <td>${file.ref_quit || '-'}</td>
        <td>${file.date_quit ? formatDate(file.date_quit) : '-'}</td>
        <td>${file.monnaie || '-'}</td>
        <td>${file.date_val ? formatDate(file.date_val) : '-'}</td>
        <td>${file.date_ech ? formatDate(file.date_ech) : '-'}</td>
        <td>${formatNumber(file.cif_lic)}</td>
        <td>${file.num_av || '-'}</td>
        <td>${formatNumber(file.montant_av)}</td>
        <td>${file.ass_ref || '-'}</td>
        <td>${file.facture || '-'}</td>
        <td>${file.bl_lta || '-'}</td>
        <td>${file.clearance_type || '-'}</td>
        <td>${file.clearance_remarks || '-'}</td>
        <td>
          <button class="btn-edit" 
                  data-import-id="${file.id}"
                  data-ass-ref="${file.ass_ref || ''}"
                  data-clearance-type="${file.clearance_type || ''}"
                  data-clearance-remarks="${file.clearance_remarks || ''}">
            <i class="ti ti-edit"></i> Edit
          </button>
        </td>
      </tr>
    `;
  });
  
  $('#pendingFilesTableBody').html(html);
}

function exportPendingFiles() {
  Swal.fire({
    icon: 'info',
    title: 'Coming Soon',
    text: 'Export functionality will be available soon'
  });
}

// EDIT FUNCTIONALITY
function handleEditClick(e) {
  e.preventDefault();
  
  const importId = $(this).data('import-id');
  const assRef = $(this).data('ass-ref');
  const clearanceType = $(this).data('clearance-type');
  const clearanceRemarks = $(this).data('clearance-remarks');
  
  $('#editImportId').val(importId);
  $('#editAssRef').val(assRef);
  $('#editType').val(clearanceType);
  $('#editRemarque').val(clearanceRemarks);
  
  $('#editImportFileModal').modal('show');
}

function handleEditFormSubmit(e) {
  e.preventDefault();
  
  const formData = $(this).serialize();
  
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/updateImportFile',
    method: 'POST',
    data: formData,
    dataType: 'json',
    beforeSend: function() {
      Swal.fire({
        title: 'Saving...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    },
    success: function(res) {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: res.message || 'Import file updated successfully',
          timer: 2000,
          showConfirmButton: false
        });
        
        $('#editImportFileModal').modal('hide');
        loadPendingFiles();
        updateStatistics();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: res.message || 'Failed to update import file'
        });
      }
    },
    error: function(xhr) {
      console.error('Update error:', xhr.responseText);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Server error occurred'
      });
    }
  });
}

// TABLE DATA - LOADS TRANSMISSIONS
function loadTableData() {
  const searchTerm = $('#tableSearch').val();
  
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getTransmissionsList',
    method: 'GET',
    data: { 
      client_id: selectedClientId,
      page: currentPage,
      per_page: perPage,
      search: searchTerm
    },
    dataType: 'json',
    beforeSend: function() {
      $('#dataTableBody').html(`
        <tr>
          <td colspan="9" class="text-center p-4">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading data...</p>
          </td>
        </tr>
      `);
    },
    success: function(res) {
      if (res.success && res.data && res.data.length > 0) {
        renderTableData(res.data);
        updatePagination(res.pagination);
      } else {
        $('#dataTableBody').html(`
          <tr>
            <td colspan="9" class="text-center p-4">No transmissions created yet</td>
          </tr>
        `);
        updatePagination({ total: 0, per_page: perPage, current_page: 1, total_pages: 0 });
      }
    }
  });
}

function renderTableData(data) {
  let html = '';
  const startIndex = (currentPage - 1) * perPage;
  
  data.forEach(function(item, index) {
    html += `
      <tr>
        <td>${startIndex + index + 1}</td>
        <td class="client-name-red">${item.client_name || '-'}</td>
        <td class="reference-number">${item.license_number || '-'}</td>
        <td>${item.date_creation ? formatDate(item.date_creation) : '-'}</td>
        <td>${item.date_depot || '-'}</td>
        <td>${item.banque || '-'}</td>
        <td class="text-center">1</td>
        <td class="text-center">${item.nbre_dossiers || 0}</td>
        <td>
          <div class="action-buttons">
            <button class="btn-action btn-download" title="Download">
              <i class="ti ti-download"></i>
            </button>
            <button class="btn-action btn-view" data-license-id="${item.license_id}" data-license="${item.original_license}" title="View">
              <i class="ti ti-eye"></i>
            </button>
            <button class="btn-action btn-edit" style="background: #ffc107; color: #000;" title="Edit">
              <i class="ti ti-edit"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  });
  
  $('#dataTableBody').html(html);
}

function updatePagination(pagination) {
  const startRecord = pagination.total > 0 ? ((pagination.current_page - 1) * pagination.per_page) + 1 : 0;
  const endRecord = Math.min(pagination.current_page * pagination.per_page, pagination.total);
  
  $('#paginationInfo').text(`Showing ${startRecord} to ${endRecord} of ${pagination.total} entries`);
  
  let paginationHtml = '';
  
  paginationHtml += `
    <button class="page-btn" data-page="${pagination.current_page - 1}" ${pagination.current_page === 1 ? 'disabled' : ''}>
      Previous
    </button>
  `;
  
  for (let i = 1; i <= pagination.total_pages; i++) {
    if (i <= 5 || i > pagination.total_pages - 2 || Math.abs(i - pagination.current_page) <= 1) {
      paginationHtml += `
        <button class="page-btn ${i === pagination.current_page ? 'active' : ''}" data-page="${i}">
          ${i}
        </button>
      `;
    } else if (i === 6 && pagination.current_page > 8) {
      paginationHtml += `<span>...</span>`;
    }
  }
  
  paginationHtml += `
    <button class="page-btn" data-page="${pagination.current_page + 1}" ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
      Next
    </button>
  `;
  
  $('#paginationButtons').html(paginationHtml);
}

function handlePageClick() {
  if ($(this).prop('disabled')) return;
  
  const page = parseInt($(this).data('page'));
  if (page > 0) {
    currentPage = page;
    loadTableData();
  }
}

function handleTableSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(function() {
    currentPage = 1;
    loadTableData();
  }, 500);
}

// VIEW DETAILS
function handleViewClick() {
  const licenseId = $(this).data('license-id');
  const licenseNumber = $(this).data('license');
  
  loadFileDetails(licenseId, licenseNumber);
}

function loadFileDetails(licenseId, licenseNumber) {
  $.ajax({
    url: '<?= APP_URL ?>/importapurment/crudData/getImportFileDetails',
    method: 'GET',
    data: { license_id: licenseId },
    dataType: 'json',
    beforeSend: function() {
      $('#detailsLicenseNumber').text(licenseNumber);
      $('#detailsModal').modal('show');
      $('#detailsModalBody').html(`
        <div class="text-center p-4">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">Loading details...</p>
        </div>
      `);
    },
    success: function(res) {
      if (res.success && res.data) {
        renderFileDetails(res.data);
      } else {
        $('#detailsModalBody').html(`
          <div class="alert alert-warning">No details found</div>
        `);
      }
    }
  });
}

function renderFileDetails(files) {
  let html = `
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>MCA REF</th>
          <th>FOB</th>
          <th>FRET</th>
          <th>ASSURANCE</th>
          <th>CIF</th>
          <th>Déclaration</th>
          <th>Liquidation</th>
          <th>Quittance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
  `;
  
  files.forEach(function(file, index) {
    const statusBadge = file.apurement_status === 'APURED' ? 
      '<span class="badge bg-success">APURED</span>' : 
      file.apurement_status === 'PARTIAL' ?
      '<span class="badge bg-warning text-dark">PARTIAL</span>' :
      '<span class="badge bg-secondary">PENDING</span>';
      
    html += `
      <tr>
        <td>${index + 1}</td>
        <td>${file.mca_ref || '-'}</td>
        <td>${formatNumber(file.fob)}</td>
        <td>${formatNumber(file.fret)}</td>
        <td>${formatNumber(file.insurance_amount)}</td>
        <td>${formatNumber(file.cif)}</td>
        <td>${file.declaration_reference || '-'}<br><small>${file.dgda_in_date ? formatDate(file.dgda_in_date) : ''}</small></td>
        <td>${file.liquidation_reference || '-'}<br><small>${file.liquidation_date ? formatDate(file.liquidation_date) : ''}</small></td>
        <td>${file.quittance_reference || '-'}<br><small>${file.quittance_date ? formatDate(file.quittance_date) : ''}</small></td>
        <td>${statusBadge}</td>
      </tr>
    `;
  });
  
  html += `</tbody></table>`;
  $('#detailsModalBody').html(html);
}

// HELPER FUNCTIONS
function htmlEscape(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}

function formatNumber(value) {
  const num = parseFloat(value || 0);
  return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}
</script>