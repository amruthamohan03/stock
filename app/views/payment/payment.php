<?php 
if (file_exists(VIEW_PATH . 'layouts/partials/header.php')) {
  include(VIEW_PATH . 'layouts/partials/header.php'); 
}
?>

<!-- Include Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<link href="<?= BASE_URL ?>/assets/pages/css/payment_request_styles.css" rel="stylesheet" type="text/css">

<style>
    .dataTables_wrapper .dataTables_info {
        float: left;
    }

    .dataTables_wrapper .dataTables_paginate {
        float: none !important;
        text-align: center !important;
        margin-top: 10px;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5em;
        padding: 5px 10px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_length select {
        padding: 5px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    /* Custom DataTable Header with Clear Filter Button */
    .datatable-header-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .datatable-left-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .clear-filter-btn-table {
        display: none;
        padding: 6px 16px;
        font-size: 0.85rem;
        border-radius: 4px;
    }

    .clear-filter-btn-table.show {
        display: inline-block;
    }

    .page-content {
        padding: 15px 0;
    }

    .card {
        margin-bottom: 15px;
    }

    .card-header {
        padding: 12px 20px;
    }

    .card-header h4 {
        font-size: 1.1rem;
        margin: 0;
    }

    .accordion-button {
        padding: 12px 20px;
        font-size: 1rem;
    }

    .accordion-button.collapsed {
        background-color: #fff;
    }

    .accordion-body {
        padding: 20px;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 6px;
        font-size: 0.875rem;
        display: block;
        color: #666;
    }

    .form-control,
    .form-select {
        height: 38px;
        padding: 8px 12px;
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    textarea.form-control {
        height: auto;
        padding: 10px 12px;
        resize: none;
    }

    .input-group-text {
        padding: 8px 12px;
        font-size: 0.875rem;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .section-header {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-header i {
        font-size: 1.1rem;
        color: #6c757d;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-grid-motif-docs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
        margin-top: 20px;
    }

    .motif-column {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .documents-column {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        align-content: start;
    }

    @media (max-width: 1400px) {
        .form-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .form-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .form-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-grid-motif-docs {
            grid-template-columns: 1fr;
        }

        .documents-column {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-grid,
        .form-grid-4 {
            grid-template-columns: 1fr;
        }

        .documents-column {
            grid-template-columns: 1fr;
        }
    }

    /* 7 Cards Layout */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 12px;
        margin-bottom: 25px;
    }

    @media (max-width: 1600px) {
        .stats-cards {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 1200px) {
        .stats-cards {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 18px 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        border-left: 4px solid;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        opacity: 0.1;
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .stat-card.active {
        box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        transform: translateY(-3px);
        border-left-width: 6px;
    }

    .stat-card.active::after {
        content: '✓';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        background: rgba(0,0,0,0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .stat-card.total {
        border-left-color: #0d6efd;
        background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
    }

    .stat-card.total::before {
        background: #0d6efd;
    }

    .stat-card.total.active {
        background: linear-gradient(135deg, #e7f1ff 0%, #cce5ff 100%);
    }

    .stat-card.waiting-dept {
        border-left-color: #ffc107;
        background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
    }

    .stat-card.waiting-dept::before {
        background: #ffc107;
    }

    .stat-card.waiting-dept.active {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
    }

    .stat-card.waiting-finance {
        border-left-color: #17a2b8;
        background: linear-gradient(135deg, #ffffff 0%, #f0fbfc 100%);
    }

    .stat-card.waiting-finance::before {
        background: #17a2b8;
    }

    .stat-card.waiting-finance.active {
        background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
    }

    .stat-card.waiting-mgmt {
        border-left-color: #6f42c1;
        background: linear-gradient(135deg, #ffffff 0%, #f7f3fc 100%);
    }

    .stat-card.waiting-mgmt::before {
        background: #6f42c1;
    }

    .stat-card.waiting-mgmt.active {
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    }

    .stat-card.waiting-payment {
        border-left-color: #fd7e14;
        background: linear-gradient(135deg, #ffffff 0%, #fff8f0 100%);
    }

    .stat-card.waiting-payment::before {
        background: #fd7e14;
    }

    .stat-card.waiting-payment.active {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    }

    .stat-card.paid {
        border-left-color: #28a745;
        background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    }

    .stat-card.paid::before {
        background: #28a745;
    }

    .stat-card.paid.active {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    }

    .stat-card.rejected {
        border-left-color: #dc3545;
        background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
    }

    .stat-card.rejected::before {
        background: #dc3545;
    }

    .stat-card.rejected.active {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    }

    .stat-card-title {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: #212529;
        line-height: 1;
    }

    .stat-card-icon {
        font-size: 1.1rem;
    }

    .mca-wrapper {
        max-width: 100%;
    }

    .mca-toolbar {
        background: #f8f9fa;
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .mca-toolbar-left {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .mca-toolbar input[type="number"] {
        width: 70px;
        height: 32px;
        font-size: 0.85rem;
        padding: 6px 8px;
    }

    .mca-toolbar .btn {
        height: 32px;
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .mca-toolbar .btn i {
        font-size: 0.9rem;
        margin-right: 3px;
    }

    .mca-total {
        font-size: 0.9rem;
        font-weight: 600;
        color: #495057;
    }

    .mca-total span {
        color: #0d6efd;
        font-size: 1.1rem;
    }

    .table-mca-small {
        font-size: 0.85rem;
        margin-bottom: 0;
    }

    .table-mca-small thead th {
        padding: 8px 10px;
        background: #f8f9fa;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        font-size: 0.85rem;
    }

    .table-mca-small tbody td {
        padding: 6px 10px;
        vertical-align: middle;
    }

    .table-mca-small input {
        height: 32px;
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    .table-mca-small .btn-sm {
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    .btn {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .btn-xs {
        padding: 3px 8px;
        font-size: 0.75rem;
    }

    .btn i {
        margin-right: 3px;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .action-footer {
        background: #f8f9fa;
        padding: 12px 20px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .progress {
        height: 3px;
        border-radius: 0;
    }

    .text-danger {
        color: #dc3545;
    }

    small.text-muted {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-top: 4px;
    }

    .invalid-feedback {
        font-size: 0.8rem;
    }

    .form-file-label {
        display: block;
        font-size: 0.875rem;
        margin-bottom: 6px;
        font-weight: 500;
        color: #666;
    }

    /* Updated File Link Styles */
    .file-links-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 5px;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        color: white !important;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 3px 6px rgba(102, 126, 234, 0.3);
    }

    .file-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.5);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        color: white !important;
    }

    .file-link i {
        margin-right: 8px;
        font-size: 1.1rem;
    }

    .file-link:active {
        transform: translateY(-1px);
    }

    .modal-body-scrollable {
        max-height: 70vh;
        overflow-y: auto;
    }

    .info-table {
        font-size: 0.85rem;
    }

    .info-table th {
        width: 40%;
        background: #f8f9fa;
        font-weight: 600;
        padding: 6px 10px;
    }

    .info-table td {
        padding: 6px 10px;
    }

    .timeline-table {
        font-size: 0.85rem;
    }

    .timeline-table th {
        width: 25%;
        background: #f8f9fa;
        font-weight: 600;
        padding: 8px 10px;
        text-align: center;
    }

    .timeline-table td {
        padding: 8px 10px;
        text-align: center;
    }

    .timeline-user {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-top: 3px;
    }

    .timeline-time {
        font-size: 0.7rem;
        color: #999;
        display: block;
        margin-top: 2px;
    }

    .mca-search-box {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .mca-search-box input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .modal-mca-small {
        max-width: 700px;
    }

    .mca-refs-list {
        max-height: 450px;
        overflow-y: auto;
        padding: 5px;
    }

    .mca-ref-item {
        padding: 14px 15px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .mca-ref-item:hover {
        background: #f8f9fa;
    }

    .mca-ref-item:last-child {
        border-bottom: none;
    }

    .mca-ref-check {
        width: 20px;
        height: 20px;
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 0;
    }

    .mca-ref-item .form-check-label {
        cursor: pointer;
        margin-bottom: 0;
        flex-grow: 1;
        font-size: 0.95rem;
        color: #333;
    }

    .mca-reference-input.is-validating {
        border-color: #0d6efd;
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230d6efd' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px 16px;
    }

    .mca-reference-input.is-valid {
        border-color: #28a745 !important;
        background-color: #f0fff4;
    }

    /* DataTable Font Size Reduction */
    #paymentRequestsTable {
        font-size: 0.75rem;
    }

    #paymentRequestsTable thead th {
        font-size: 0.75rem;
        padding: 8px 6px;
        font-weight: 600;
        white-space: nowrap;
    }

    #paymentRequestsTable tbody td {
        font-size: 0.72rem;
        padding: 6px 6px;
        vertical-align: middle;
    }

    #paymentRequestsTable .btn-xs {
        padding: 2px 6px;
        font-size: 0.68rem;
    }

    #paymentRequestsTable .btn-xs i {
        font-size: 0.75rem;
        margin-right: 2px;
    }

    #paymentRequestsTable .badge {
        font-size: 0.68rem;
        padding: 3px 8px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Status Badge Colors */
    .badge.status-total {
        background-color: #0d6efd !important;
        color: white;
    }

    .badge.status-pending-dept {
        background-color: #ffc107 !important;
        color: #000;
    }

    .badge.status-dept-approved {
        background-color: #0d6efd !important;
        color: white;
    }

    .badge.status-pending-finance {
        background-color: #17a2b8 !important;
        color: white;
    }

    .badge.status-finance-approved {
        background-color: #17a2b8 !important;
        color: white;
    }

    .badge.status-pending-mgmt {
        background-color: #6f42c1 !important;
        color: white;
    }

    .badge.status-mgmt-approved {
        background-color: #6f42c1 !important;
        color: white;
    }

    .badge.status-pending-payment {
        background-color: #fd7e14 !important;
        color: white;
    }

    .badge.status-paid {
        background-color: #28a745 !important;
        color: white;
    }

    .badge.status-rejected {
        background-color: #dc3545 !important;
        color: white;
    }

   /* ======================================== */
    /* PDF-MATCHING PRINT STYLES */
    /* ======================================== */
    @media print {
        body * {
            visibility: hidden;
        }
        
        #hiddenPrintSection,
        #hiddenPrintSection * {
            visibility: visible;
        }
        
        #hiddenPrintSection {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
        
        .print-container {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            color: #000;
            max-width: 100%;
            line-height: 1.4;
        }
        
        /* ===== HEADER SECTION ===== */
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .print-header-left {
            flex: 0 0 auto;
        }
        
        .print-header-logo {
            width: 200px;
            height: auto;
        }
        
        .print-header-right {
            text-align: right;
            font-size: 8pt;
            line-height: 1.6;
            color: #000;
        }
        
        .print-header-right strong {
            font-weight: bold;
        }
        
        /* ===== TITLE SECTION ===== */
        .print-title-section {
            text-align: center;
            margin: 25px 0;
        }
        
        .print-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        
        .print-datetime {
            text-align: right;
            font-size: 10pt;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        /* ===== MAIN INFO TABLE ===== */
        .print-main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        
        .print-main-table td {
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 10pt;
            vertical-align: top;
        }
        
        .print-main-table td.label {
            font-weight: bold;
            width: 22%;
            background: #f5f5f5;
        }
        
        .print-main-table td.value {
            width: 28%;
        }
        
        .print-main-table td.full-width {
            width: 100%;
        }
        
        /* ===== AUTHORIZATION SECTION ===== */
        .print-section-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 25px 0 10px 0;
            text-transform: uppercase;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 8px 0;
        }
        
        .print-auth-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        
        .print-auth-table td {
            padding: 10px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
        
        .print-auth-table td strong {
            display: block;
            margin-bottom: 5px;
            font-size: 11pt;
        }
        
        .print-auth-table .auth-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 3px;
        }
        
        .print-auth-table .auth-datetime {
            font-size: 9pt;
            color: #333;
        }
        
        /* ===== RETRAIT (WITHDRAWAL) SECTION ===== */
        .print-retrait-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        
        .print-retrait-table td {
            padding: 40px 10px;
            border: 1px solid #000;
            width: 50%;
            vertical-align: top;
        }
        
        .print-retrait-table td strong {
            display: block;
            font-size: 11pt;
            margin-bottom: 10px;
        }
        
        /* ===== PAGE 2: DETAILS TABLE ===== */
        .print-page-2 {
            page-break-before: always;
        }
        
        .print-details-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        
        .print-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        
        .print-details-table th {
            padding: 10px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #000;
            font-size: 10pt;
            background: #f5f5f5;
        }
        
        .print-details-table th:first-child {
            text-align: center;
            width: 50px;
        }
        
        .print-details-table th:last-child {
            text-align: right;
            width: 120px;
        }
        
        .print-details-table td {
            padding: 8px 10px;
            border: 1px solid #000;
            font-size: 10pt;
        }
        
        .print-details-table td:first-child {
            text-align: center;
            font-weight: bold;
        }
        
        .print-details-table td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .print-details-table tfoot td {
            font-weight: bold;
            background: #f5f5f5;
            padding: 10px;
            font-size: 11pt;
        }
        
        /* ===== FOOTER ===== */
        .print-footer {
            text-align: center;
            font-size: 8pt;
            margin-top: 30px;
            color: #666;
        }
        
        /* Hide on screen */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    }

    @media screen {
        #hiddenPrintSection {
            display: none !important;
        }
    }
</style>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>

<div class="page-content">
    <div class="container-fluid">
        
        <!-- STATUS CARDS - 7 CARDS IN A ROW -->
        <div class="stats-cards">
            <div class="stat-card total filter-card active" data-filter="all">
                <div class="stat-card-title">
                    <i class="ti ti-file-invoice stat-card-icon"></i>
                    TOTAL
                </div>
                <div class="stat-card-value" id="totalCount">0</div>
            </div>

            <div class="stat-card waiting-dept filter-card" data-filter="waiting_dept">
                <div class="stat-card-title">
                    <i class="ti ti-hourglass-empty stat-card-icon"></i>
                    PENDING DEPT
                </div>
                <div class="stat-card-value" id="waitingDeptCount">0</div>
            </div>

            <div class="stat-card waiting-finance filter-card" data-filter="waiting_finance">
                <div class="stat-card-title">
                    <i class="ti ti-calculator stat-card-icon"></i>
                    PENDING FINANCE
                </div>
                <div class="stat-card-value" id="waitingFinanceCount">0</div>
            </div>

            <div class="stat-card waiting-mgmt filter-card" data-filter="waiting_mgmt">
                <div class="stat-card-title">
                    <i class="ti ti-user-check stat-card-icon"></i>
                    PENDING MGMT
                </div>
                <div class="stat-card-value" id="waitingMgmtCount">0</div>
            </div>

            <div class="stat-card waiting-payment filter-card" data-filter="waiting_payment">
                <div class="stat-card-title">
                    <i class="ti ti-wallet stat-card-icon"></i>
                    PENDING PAYMENT
                </div>
                <div class="stat-card-value" id="waitingPaymentCount">0</div>
            </div>

            <div class="stat-card paid filter-card" data-filter="paid">
                <div class="stat-card-title">
                    <i class="ti ti-circle-check stat-card-icon"></i>
                    PAID
                </div>
                <div class="stat-card-value" id="paidCount">0</div>
            </div>

            <div class="stat-card rejected filter-card" data-filter="rejected">
                <div class="stat-card-title">
                    <i class="ti ti-x-circle stat-card-icon"></i>
                    REJECTED
                </div>
                <div class="stat-card-value" id="rejectedCount">0</div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Payment Request Form Card -->
                <div class="card shadow-sm">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                        <h4>
                            <i class="ti ti-file-invoice-dollar me-2"></i>
                            <span id="formTitle">New Payment Request</span>
                        </h4>
                        <div>
                            <button type="button" class="btn btn-sm btn-success" id="exportToExcelBtn">
                                <i class="ti ti-file-excel"></i> Export to Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn" style="display:none;">
                                <i class="ti ti-plus"></i> Add New
                            </button>
                        </div>
                    </div>

                    <div class="progress">
                        <div class="progress-bar bg-primary" id="formProgress" role="progressbar" style="width: 50%">
                        </div>
                    </div>

                    <form id="paymentRequestForm" method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" id="formAction" value="insert">
                        <input type="hidden" name="payment_id" id="recordId" value="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="accordion" id="paymentAccordion">
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#paymentFormContent">
                                        <i class="ti ti-file-invoice me-2"></i>
                                        Payment Request Details
                                    </button>
                                </h2>

                                <div id="paymentFormContent" class="accordion-collapse collapse"
                                    data-bs-parent="#paymentAccordion">
                                    <div class="accordion-body">

                                        <div class="section-header">
                                            <i class="ti ti-info-circle"></i>
                                            <span>Basic Information</span>
                                        </div>

                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                                <select name="department" id="department" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <?php foreach ($dept as $p): ?>
                                                    <option value="<?= $p['id'] ?>">
                                                        <?= htmlspecialchars($p['department_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                                <select name="location" id="location" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <?php foreach ($loc as $p): ?>
                                                    <option value="<?= $p['id'] ?>">
                                                        <?= htmlspecialchars($p['main_location_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Beneficiary <span class="text-danger">*</span></label>
                                                <input type="text" name="beneficiary" id="beneficiary"
                                                    class="form-control" required minlength="2" maxlength="200"
                                                    placeholder="Enter name">
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Requestee <span class="text-danger">*</span></label>
                                                <input type="text" name="requestee" id="requestee"
                                                    class="form-control" required minlength="2" maxlength="200" readonly 
                                                    value="<?php echo $_SESSION['user_data']['fullname']; ?>"
                                                    placeholder="Enter name">
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Client</label>
                                                <select name="client_id" id="client_id" class="form-select">
                                                    <option value="">Select</option>
                                                    <?php foreach ($client as $p): ?>
                                                    <option value="<?= $p['id'] ?>">
                                                        <?= htmlspecialchars($p['short_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-grid">
                                            <div>
                                                <label class="form-label">Payment For <span class="text-danger">*</span></label>
                                                <select name="pay_for" id="pay_for" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <option value="0">Import Tracking</option>
                                                    <option value="1">Export Tracking</option>
                                                    <option value="2">Local Tracking</option>
                                                    <option value="3">Other</option>
                                                    <option value="4">Pre Payment</option>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                                                <select name="payment_type" id="payment_type" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <option value="Bank">Bank</option>
                                                    <option value="Cash">Cash</option>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                <select name="currency" id="currency" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <?php foreach ($currency as $p): ?>
                                                    <option value="<?= $p['id'] ?>">
                                                        <?= htmlspecialchars($p['currency_short_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="amount" id="amount" class="form-control"
                                                        step="0.01" min="0" required placeholder="0.00">
                                                </div>
                                                <div class="invalid-feedback">Required</div>
                                            </div>

                                            <div>
                                                <label class="form-label">Expense Type <span class="text-danger">*</span></label>
                                                <select name="expense_type" id="expense_type" class="form-select" required>
                                                    <option value="">Select</option>
                                                    <?php foreach ($expense as $p): ?>
                                                    <option value="<?= $p['id'] ?>">
                                                        <?= htmlspecialchars($p['expense_type_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">Required</div>
                                            </div>
                                        </div>

                                        <div style="margin-top: 30px;">
                                            <div class="section-header">
                                                <i class="ti ti-file-text"></i>
                                                <span>MCA References</span>
                                            </div>

                                            <div class="mca-wrapper">
                                                <div class="mca-toolbar">
                                                    <div class="mca-toolbar-left">
                                                        <input type="number" id="num_mca_refs" class="form-control"
                                                            min="0" max="50" value="0" placeholder="0-50">
                                                        <button type="button" class="btn btn-primary"
                                                            id="add_mca_refs_btn">
                                                            <i class="ti ti-plus"></i> Add
                                                        </button>
                                                        <button type="button" class="btn btn-success"
                                                            id="selectMcaRefsBtn">
                                                            <i class="ti ti-list-check"></i> Select
                                                        </button>
                                                        <label for="excel_import" class="btn btn-info mb-0"
                                                            style="cursor: pointer;">
                                                            <i class="ti ti-file-excel"></i> Import
                                                            <input type="file" id="excel_import" accept=".xlsx,.xls"
                                                                class="d-none">
                                                        </label>
                                                    </div>
                                                    <div class="mca-total">
                                                        Total: <span id="mca_total_amount">0.00</span>
                                                    </div>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover table-mca-small"
                                                        id="mcaFilesTable">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 40px;">#</th>
                                                                <th id="refHeading" style="width: 250px;">MCA Reference</th>
                                                                <th style="width: 120px;">Amount</th>
                                                                <th style="width: 60px;" class="text-center">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="mcaFilesList">
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted"
                                                                    style="padding: 15px;">
                                                                    <i class="ti ti-info-circle me-1"></i>No MCA
                                                                    references added yet
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="form-grid-motif-docs">
                                                    <div class="motif-column">
                                                        <div>
                                                            <label class="form-label">Motif / Reason <span class="text-danger">*</span></label>
                                                            <textarea name="motif" id="motif" class="form-control" rows="3" required
                                                                minlength="10" maxlength="500"
                                                                placeholder="Enter reason for payment"></textarea>
                                                            <small class="text-muted">Min 10 chars, Max 500 chars</small>
                                                            <div class="invalid-feedback">Required (10-500 characters)</div>
                                                        </div>
                                                    </div>

                                                    <div class="documents-column">
                                                        <div>
                                                            <label class="form-file-label">Document 1</label>
                                                            <input type="file" name="file1" id="file1" class="form-control"
                                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                            <small class="text-muted">Max 5MB</small>
                                                        </div>

                                                        <div>
                                                            <label class="form-file-label">Document 2</label>
                                                            <input type="file" name="file2" id="file2" class="form-control"
                                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                            <small class="text-muted">Max 5MB</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="action-footer">
                            <button type="button" class="btn btn-outline-secondary" id="clearBtn">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="ti ti-check"></i> Submit
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Payment Requests List Table -->
                <div class="card shadow-sm">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title mb-0"><i class="ti ti-list me-2"></i> List of Payment Requests</h4>
                    </div>
                    <div class="card-body">
                        <!-- Custom DataTable Header Controls -->
                        <div class="datatable-header-controls">
                            <div class="datatable-left-controls">
                                <button type="button" class="btn btn-danger btn-sm clear-filter-btn-table" id="clearFilterBtnTable">
                                    <i class="ti ti-x"></i> Clear Filter
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="paymentRequestsTable"
                                class="table table-striped table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Requestee</th>
                                        <th>Beneficiary</th>
                                        <th>Client</th>
                                        <th>Payment For</th>
                                        <th>Payment Type</th>
                                        <th>Currency</th>
                                        <th>Expense Type</th>
                                        <th>Amount</th>
                                        <th>Refs</th>
                                        <th>Status</th>
                                        <th>Date</th>
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

        <!-- Department Approval Modal -->
        <div class="modal fade" id="deptApprovalModal" tabindex="-1">
            <input type="hidden" id="currentApprovalId">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Department Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable">
                        <div id="approvalDetails"></div>
                        <div class="mt-3">
                            <button id="approveBtn" class="btn btn-success btn-sm">
                                <i class="ti ti-check"></i> Approve
                            </button>
                            <button id="rejectBtn" class="btn btn-danger btn-sm">
                                <i class="ti ti-x"></i> Reject
                            </button>
                        </div>
                        <div id="rejectSection" class="mt-3" style="display:none;">
                            <label>Reason for Rejection:</label>
                            <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter reason for rejection"></textarea>
                            <button id="submitRejectBtn" class="btn btn-danger btn-sm mt-2">
                                <i class="ti ti-send"></i> Submit Rejection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finance Approval Modal -->
        <div class="modal fade" id="financeApprovalModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Finance Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable">
                        <div id="financeApprovalDetails"></div>
                        <div class="mt-3">
                            <button id="financeApproveBtn" class="btn btn-success btn-sm">
                                <i class="ti ti-check"></i> Approve
                            </button>
                            <button id="financeRejectBtn" class="btn btn-danger btn-sm">
                                <i class="ti ti-x"></i> Reject
                            </button>
                        </div>
                        <div id="financeRejectSection" class="mt-3" style="display:none;">
                            <label>Reason for Rejection:</label>
                            <textarea id="financeRejectReason" class="form-control" rows="3" placeholder="Enter reason for rejection"></textarea>
                            <button id="financeSubmitRejectBtn" class="btn btn-danger btn-sm mt-2">
                                <i class="ti ti-send"></i> Submit Rejection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Approval Modal -->
        <div class="modal fade" id="mgmtApprovalModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Management Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable">
                        <div id="mgmtApprovalDetails"></div>
                        <div class="mt-3">
                            <button id="mgmtApproveBtn" class="btn btn-success btn-sm">
                                <i class="ti ti-check"></i> Approve
                            </button>
                            <button id="mgmtRejectBtn" class="btn btn-danger btn-sm">
                                <i class="ti ti-x"></i> Reject
                            </button>
                        </div>
                        <div id="mgmtRejectSection" class="mt-3" style="display:none;">
                            <label>Reason for Rejection:</label>
                            <textarea id="mgmtRejectReason" class="form-control" rows="3" placeholder="Enter reason for rejection"></textarea>
                            <button id="mgmtSubmitRejectBtn" class="btn btn-danger btn-sm mt-2">
                                <i class="ti ti-send"></i> Submit Rejection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAID APPROVAL MODAL -->
        <div class="modal fade" id="paidApprovalModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark as Paid</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable">
                        <div id="paidApprovalDetails"></div>
                        
                        <div class="mt-3">
                            <label class="form-label">Cash Collector <span class="text-danger">*</span></label>
                            <input type="text" id="paidCashCollector" class="form-control" 
                                   placeholder="Enter cash collector name" maxlength="100" required>
                        </div>

                        <div class="mt-3">
                            <h6 class="mb-2">Payment Documents</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-file-label">Document 3</label>
                                    <input type="file" id="paidFile3" class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="text-muted">Max 5MB</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-file-label">Document 4</label>
                                    <input type="file" id="paidFile4" class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <small class="text-muted">Max 5MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button id="paidApproveBtn" class="btn btn-success btn-sm">
                                <i class="ti ti-check"></i> Mark as Paid
                            </button>
                            <button id="paidRejectBtn" class="btn btn-danger btn-sm">
                                <i class="ti ti-x"></i> Reject
                            </button>
                        </div>

                        <div id="paidRejectSection" class="mt-3" style="display:none;">
                            <label>Reason for Rejection:</label>
                            <textarea id="paidRejectReason" class="form-control" rows="3" placeholder="Enter reason for rejection"></textarea>
                            <button id="paidSubmitRejectBtn" class="btn btn-danger btn-sm mt-2">
                                <i class="ti ti-send"></i> Submit Rejection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW MODAL -->
        <div class="modal fade" id="viewModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-eye"></i> Payment Request Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable" id="viewModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MCA Reference Selection Modal -->
        <div class="modal fade" id="mcaRefModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-mca-small">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select MCA References</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mca-search-box">
                            <input type="text" id="mcaSearchInput" class="form-control" 
                                   placeholder="🔍 Search MCA references...">
                        </div>
                        
                        <div id="mcaRefList" class="mca-refs-list"></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-sm" id="applyMcaRefsBtn">Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MCA References View Modal -->
        <div class="modal fade" id="mcaRefsViewModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">MCA References</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body modal-body-scrollable" id="mcaRefsViewBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HIDDEN PRINT SECTION -->
        <div id="hiddenPrintSection"></div>

    </div>
</div>

<script src="<?= BASE_URL ?>/js/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function () {
    // ============================================
    // XSS PROTECTION & HELPER FUNCTIONS
    // ============================================
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    // ============================================
    // FORMAT NUMBER WITH SPACES (French format: 5 000,00)
    // ============================================
    function formatAmountFrench(amount) {
        if (!amount) return '0,00';
        const num = parseFloat(amount);
        const parts = num.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        return parts.join(',');
    }

    // ============================================
    // CONVERT NUMBER TO FRENCH WORDS
    // ============================================
    function numberToFrenchWords(num) {
        if (num === 0) return 'zéro';
        
        const ones = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        const tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
        const teens = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
        
        function convertLessThanThousand(n) {
            if (n === 0) return '';
            if (n < 10) return ones[n];
            if (n < 20) return teens[n - 10];
            if (n < 100) {
                const ten = Math.floor(n / 10);
                const one = n % 10;
                if (ten === 7 || ten === 9) {
                    return tens[ten - 1] + (one > 0 ? '-' + teens[one] : '-dix');
                }
                return tens[ten] + (one > 0 ? '-' + ones[one] : '');
            }
            
            const hundred = Math.floor(n / 100);
            const remainder = n % 100;
            let result = hundred > 1 ? ones[hundred] + ' cent' : 'cent';
            if (remainder > 0) result += ' ' + convertLessThanThousand(remainder);
            return result;
        }
        
        const integer = Math.floor(num);
        const decimal = Math.round((num - integer) * 100);
        
        if (integer >= 1000000) {
            const millions = Math.floor(integer / 1000000);
            const remainder = integer % 1000000;
            let result = convertLessThanThousand(millions) + ' million';
            if (millions > 1) result += 's';
            if (remainder > 0) result += ' ' + numberToFrenchWords(remainder).split(' centime')[0];
            return result;
        }
        
        if (integer >= 1000) {
            const thousands = Math.floor(integer / 1000);
            const remainder = integer % 1000;
            let result = thousands === 1 ? 'mille' : convertLessThanThousand(thousands) + ' mille';
            if (remainder > 0) result += ' ' + convertLessThanThousand(remainder);
            return result + (decimal > 0 ? ', ' + convertLessThanThousand(decimal) + ' centime' + (decimal > 1 ? 's' : '') : '');
        }
        
        let result = convertLessThanThousand(integer);
        if (decimal > 0) result += ', ' + convertLessThanThousand(decimal) + ' centime' + (decimal > 1 ? 's' : '');
        return result;
    }

    // ============================================
    // FILE ICON & URL HELPERS
    // ============================================
    function getFileIcon(extension) {
        const iconMap = {
            'pdf': 'ti-file-type-pdf',
            'jpg': 'ti-file-type-jpg',
            'jpeg': 'ti-file-type-jpg',
            'png': 'ti-file-type-png',
            'doc': 'ti-file-type-doc',
            'docx': 'ti-file-type-docx'
        };
        return iconMap[extension] || 'ti-file';
    }

    function getFileExtension(filepath) {
        if (!filepath) return '';
        return filepath.split('.').pop().toLowerCase();
    }

    function buildFileLinks(data) {
        let filesHTML = '';

        const files = [
            { path: data.file1_path, label: 'Document 1' },
            { path: data.file2_path, label: 'Document 2' },
            { path: data.file3_path, label: 'Document 3' },
            { path: data.file4_path, label: 'Document 4' }
        ];

        const validFiles = files.filter(f => f.path);

        if (!validFiles.length) {
            return '<p class="text-muted"><i class="ti ti-info-circle"></i> No documents attached</p>';
        }

        const base = BASE_URL.endsWith('/') ? BASE_URL : BASE_URL + '/';

        filesHTML += '<div class="file-links-container">';

        validFiles.forEach(file => {
            const ext = getFileExtension(file.path);
            const icon = getFileIcon(ext);
            const fileUrl = base + file.path;

            filesHTML += `
                <a href="${escapeHtml(fileUrl)}" target="_blank" class="file-link">
                    <i class="ti ${icon}"></i>
                    ${escapeHtml(file.label)} (.${ext})
                </a>
            `;
        });

        filesHTML += '</div>';
        return filesHTML;
    }

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    function showLoading(buttonId, text = 'Loading...') {
        $(buttonId).prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> ' + text);
    }

    function hideLoading(buttonId, originalText) {
        $(buttonId).prop('disabled', false).html(originalText);
    }

    function ajaxRequest(url, data, successCallback, errorCallback) {
        data.csrf_token = '<?php echo $csrf_token; ?>';
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    successCallback(res);
                } else {
                    toastr.error(res.message || 'Operation failed');
                    if (errorCallback) errorCallback(res);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred. Please try again.');
                if (errorCallback) errorCallback({ message: error });
            }
        });
    }

    function buildApprovalDetailsHTML(data, refs) {
        const payForMap = {
            0: "Import Tracking",
            1: "Export Tracking",
            2: "Local Tracking",
            3: "Other",
            4: "Pre Payment"
        };
        data.pay_for = payForMap[data.pay_for] || data.pay_for;

        let mcaHTML = '';
        if (refs.length > 0) {
            mcaHTML = `
                <div style="max-height: 150px; overflow-y: auto;">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr><th>#</th><th>Reference</th><th>Amount</th></tr>
                        </thead>
                        <tbody>
            `;
            refs.forEach((mca, index) => {
                mcaHTML += `<tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(mca.mca_ref)}</td>
                    <td>${parseFloat(mca.amount).toFixed(2)}</td>
                </tr>`;
            });
            mcaHTML += `</tbody></table></div>`;
        } else {
            mcaHTML = '<p class="text-muted">No references found</p>';
        }

        let filesHTML = buildFileLinks(data); 

        return `
            <h6 class="mb-2">Payment Information</h6>
            <table class="table table-bordered table-sm info-table">
                <tr><th>ID</th><td>#${escapeHtml(data.id)}</td></tr>
                <tr><th>Department</th><td>${escapeHtml(data.department_name)}</td></tr>
                <tr><th>Beneficiary</th><td>${escapeHtml(data.beneficiary)}</td></tr>
                <tr><th>Requestee</th><td>${escapeHtml(data.requestee)}</td></tr>
                <tr><th>Client</th><td>${escapeHtml(data.client_name)}</td></tr>
                <tr><th>Payment For</th><td>${escapeHtml(data.pay_for)}</td></tr>
                <tr><th>Payment Type</th><td>${escapeHtml(data.payment_type)}</td></tr>
                <tr><th>Currency</th><td>${escapeHtml(data.currency_short_name)}</td></tr>
                <tr><th>Amount</th><td><strong>${parseFloat(data.amount).toFixed(2)}</strong></td></tr>
                <tr><th>Expense Type</th><td>${escapeHtml(data.expense_type_name)}</td></tr>
                <tr><th>Cash Collector</th><td>${escapeHtml(data.cash_collector || 'N/A')}</td></tr>
                ${data.chargeback ? `<tr><th>Chargeback</th><td>${parseFloat(data.chargeback).toFixed(2)}</td></tr>` : ''}
            </table>

            <h6 class="mt-3 mb-2">Motif / Reason</h6>
            <div class="alert alert-secondary p-2" style="font-size: 0.85rem;">${escapeHtml(data.motif)}</div>

            <h6 class="mt-3 mb-2">Attached Documents</h6>
            ${filesHTML}

            <h6 class="mt-3 mb-2">References (${refs.length})</h6>
            ${mcaHTML}
        `;
    }

    // ============================================
    // ENHANCED MCA VALIDATION WITH TRACKING CHECK
    // ============================================
    
    function validateMcaBeforeAdd(mcaRef, expenseType, paymentId) {
        return new Promise((resolve, reject) => {
            if (!expenseType) {
                toastr.warning('Please select Expense Type first');
                resolve(false);
                return;
            }
            
            const payFor = $('#pay_for').val();
            const clientId = $('#client_id').val();
            
            // Step 1: Validate existence in tracking system (except for Other/Pre Payment)
            if (payFor === '0' || payFor === '1' || payFor === '2') {
                // For Import/Export/Local, must check tracking system first
                if (!clientId) {
                    toastr.warning('Please select a Client first for tracking validation');
                    resolve(false);
                    return;
                }
                
                $.ajax({
                    url: "<?php echo APP_URL; ?>payment/validate_mca_exists",
                    type: "POST",
                    data: {
                        csrf_token: '<?php echo $csrf_token; ?>',
                        mca_ref: mcaRef,
                        pay_for: payFor,
                        client_id: clientId
                    },
                    dataType: "json",
                    success: function(trackingRes) {
                        if (!trackingRes.success || !trackingRes.data.exists) {
                            toastr.error(trackingRes.message || 'Reference not found in tracking system');
                            resolve(false);
                            return;
                        }
                        
                        // Tracking validation passed, now check for duplicates
                        checkDuplicateInPayments();
                    },
                    error: function(xhr) {
                        toastr.error('Failed to validate reference in tracking system');
                        console.error('Tracking validation error:', xhr);
                        resolve(false);
                    }
                });
            } else {
                // For Other/Pre Payment, only check duplicates
                checkDuplicateInPayments();
            }
            
            function checkDuplicateInPayments() {
                ajaxRequest(
                    "<?php echo APP_URL; ?>payment/check_mca_duplicate",
                    {
                        mca_ref: mcaRef,
                        expense_type: expenseType,
                        payment_id: paymentId
                    },
                    function(res) {
                        if (res.data && res.data.exists) {
                            toastr.error(res.message);
                            resolve(false);
                        } else {
                            resolve(true);
                        }
                    },
                    function() {
                        reject(new Error('Duplicate check failed'));
                    }
                );
            }
        });
    }

    async function validateMcaBatch(refs) {
        const expenseType = $('#expense_type').val();
        const paymentId = $('#recordId').val();
        const payFor = $('#pay_for').val();
        const clientId = $('#client_id').val();
        
        if (!expenseType) {
            toastr.warning('Please select Expense Type first');
            return [];
        }
        
        // For tracking-based payments, require client
        if ((payFor === '0' || payFor === '1' || payFor === '2') && !clientId) {
            toastr.warning('Please select a Client first');
            return [];
        }
        
        const validatedRefs = [];
        const invalidRefs = [];
        
        for (const item of refs) {
            try {
                const isValid = await validateMcaBeforeAdd(item.ref, expenseType, paymentId);
                if (isValid) {
                    validatedRefs.push(item);
                } else {
                    invalidRefs.push(item.ref);
                }
            } catch (error) {
                console.error('Validation error for:', item.ref, error);
                invalidRefs.push(item.ref);
            }
        }
        
        // Show summary
        if (validatedRefs.length > 0) {
            toastr.success(`✓ ${validatedRefs.length} valid reference(s)`);
        }
        
        if (invalidRefs.length > 0) {
            toastr.warning(`✗ ${invalidRefs.length} invalid/duplicate reference(s) skipped`);
        }
        
        return validatedRefs;
    }

    // ============================================
    // VARIABLES - INCLUDE NEW ROLES
    // ============================================
    let placeholderText = 'Enter MCA reference';
    var userRoleId = <?php echo $_SESSION['user_data']['role_id']; ?>;
    var currentUserId = <?php echo $current_user_id; ?>;
    var currentFilter = 'all';

    // ============================================
    // LOAD STATUS COUNTS (7 CARDS)
    // ============================================
    function loadStatusCounts() {
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_status_counts",
            {},
            function(res) {
                $('#totalCount').text(res.data.total);
                $('#waitingDeptCount').text(res.data.waiting_dept);
                $('#waitingFinanceCount').text(res.data.waiting_finance);
                $('#waitingMgmtCount').text(res.data.waiting_mgmt);
                $('#waitingPaymentCount').text(res.data.waiting_payment); 
                $('#paidCount').text(res.data.paid);
                $('#rejectedCount').text(res.data.rejected);
            }
        );
    }

    loadStatusCounts();

    // ============================================
    // CARD FILTERS & CLEAR BUTTON
    // ============================================
    $('.filter-card').on('click', function() {
        var filter = $(this).data('filter');
        currentFilter = filter;
        
        $('.filter-card').removeClass('active');
        $(this).addClass('active');
        
        if (filter === 'all') {
            $('#clearFilterBtnTable').removeClass('show');
        } else {
            $('#clearFilterBtnTable').addClass('show');
        }
        
        paymentTable.ajax.reload();
    });

    $('#clearFilterBtnTable').on('click', function() {
        currentFilter = 'all';
        $('.filter-card').removeClass('active');
        $('.filter-card[data-filter="all"]').addClass('active');
        $(this).removeClass('show');
        paymentTable.ajax.reload();
    });

    // ============================================
    // PAYMENT FOR DROPDOWN CHANGE
    // ============================================
    $('#pay_for').on('change', function () {
        let val = $(this).val();

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/getExpenseTypesByCategory",
            { pay_for: val },
            function(res) {
                let html = '<option value="">Select</option>';
                res.forEach(row => {
                    html += `<option value="${escapeHtml(row.id)}">${escapeHtml(row.expense_type_name)}</option>`;
                });
                $('#expense_type').html(html);
            }
        );

        let selected = $(this).val();
        let sectionTitle = 'MCA References';
        let tableHeading = 'MCA Reference';

        switch (selected) {
            case '0':
                sectionTitle = 'Import Tracking References';
                tableHeading = 'Import Tracking Reference';
                placeholderText = 'Enter Import tracking reference';
                break;
            case '1':
                sectionTitle = 'Export Tracking References';
                tableHeading = 'Export Tracking Reference';
                placeholderText = 'Enter Export tracking reference';
                break;
            case '2':
                sectionTitle = 'Local Tracking References';
                tableHeading = 'Local Tracking Reference';
                placeholderText = 'Enter Local tracking reference';
                break;
            case '3':
                sectionTitle = 'Other References';
                tableHeading = 'Other Reference';
                placeholderText = 'Enter Other reference';
                break;
            case '4':
                sectionTitle = 'Pre Payment References';
                tableHeading = 'Pre Payment Reference';
                placeholderText = 'Enter Pre Payment reference';
                break;
            default:
                sectionTitle = 'MCA References';
                tableHeading = 'MCA Reference';
                placeholderText = 'Enter MCA reference';
        }

        $('.section-header span').text(sectionTitle);
        $('#refHeading').text(tableHeading);
        $('.mca-reference-input').attr('placeholder', placeholderText);
        $('#mcaFilesList').html(`
            <tr>
                <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                    <i class="ti ti-info-circle me-1"></i>No references added yet
                </td>
            </tr>
        `);
        $('#mca_total_amount').text('0.00');
        $('#num_mca_refs').val(0);
    });

    // ============================================
    // MCA SEARCH FUNCTIONALITY
    // ============================================
    $('#mcaSearchInput').on('keyup', function() {
        let searchTerm = $(this).val().toLowerCase();
        
        $('.mca-ref-item').each(function() {
            let refText = $(this).find('label').text().toLowerCase();
            if (refText.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $(document).on('click', '.mca-ref-item', function(e) {
        if (e.target.type === 'checkbox') {
            return;
        }
        
        let checkbox = $(this).find('.mca-ref-check');
        checkbox.prop('checked', !checkbox.prop('checked'));
    });

    // ============================================
    // VALIDATE AND CALCULATE MCA AMOUNTS
    // ============================================
    function validateMcaAmounts() {
        let mainAmount = parseFloat($('#amount').val()) || 0;
        let total = 0;
        let hasError = false;

        $('.mca-amount-input').each(function () {
            let rowVal = parseFloat($(this).val()) || 0;

            if (mainAmount > 0 && rowVal > mainAmount) {
                toastr.error(`Individual row amount (${rowVal}) cannot exceed the main amount (${mainAmount})`);
                $(this).val('');
                hasError = true;
                rowVal = 0;
            }

            total += rowVal;
        });

        $('#mca_total_amount').text(total.toFixed(2));

        if (mainAmount > 0 && total > mainAmount) {
            toastr.error(`Total of all references (${total.toFixed(2)}) exceeds main amount (${mainAmount})`);
            $('#mca_total_amount').css('color', 'red');
            hasError = true;
        } else {
            $('#mca_total_amount').css('color', '');
        }

        $('#submitBtn').prop('disabled', hasError);
    }

    $(document).on('input', '.mca-amount-input', validateMcaAmounts);
    $('#amount').on('input', validateMcaAmounts);

    // ============================================
    // BLUR VALIDATION WITH TRACKING CHECK
    // ============================================
    $(document).on('blur', '.mca-reference-input', async function() {
        const mcaRef = $(this).val().trim();
        const $input = $(this);
        
        if (!mcaRef) {
            return;
        }
        
        const expenseType = $('#expense_type').val();
        if (!expenseType) {
            toastr.warning('Please select Expense Type first');
            $input.val('');
            return;
        }
        
        const payFor = $('#pay_for').val();
        const clientId = $('#client_id').val();
        
        // For tracking payments, require client
        if ((payFor === '0' || payFor === '1' || payFor === '2') && !clientId) {
            toastr.warning('Please select a Client first');
            $input.val('');
            return;
        }
        
        const paymentId = $('#recordId').val();
        
        try {
            $input.addClass('is-validating');
            
            const isValid = await validateMcaBeforeAdd(mcaRef, expenseType, paymentId);
            
            if (!isValid) {
                $input.val('').focus();
            } else {
                $input.removeClass('is-invalid').addClass('is-valid');
                setTimeout(() => $input.removeClass('is-valid'), 2000);
            }
        } catch (error) {
            console.error('Validation error:', error);
            toastr.error('Validation failed. Please try again.');
        } finally {
            $input.removeClass('is-validating');
        }
    });

    // ============================================
    // INITIALIZE DATATABLE
    // ============================================
    var paymentTable = $('#paymentRequestsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: "<?php echo APP_URL; ?>payment/get_list",
            type: "GET",
            data: function(d) {
                d.status_filter = currentFilter;
                d.csrf_token = '<?php echo $csrf_token; ?>';
                return d;
            },
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            { 
                data: 'id', 
                width: '60px',
                render: function(data) {
                    return '<strong>#' + escapeHtml(data) + '</strong>';
                }
            },
            { data: 'requestee', render: function(data) { return escapeHtml(data); } },
            { data: 'beneficiary', render: function(data) { return escapeHtml(data); } },
            { data: 'client_name', render: function(data) { return data ? escapeHtml(data) : 'N/A'; } },
            {
                data: 'pay_for',
                render: function (data) {
                    const payForMap = {'0': 'Import', '1': 'Export', '2': 'Local', '3': 'Other', '4': 'Pre Payment'};
                    return escapeHtml(payForMap[data] || 'N/A');
                }
            },
            { data: 'payment_type', render: function(data) { return escapeHtml(data); } },
            { data: 'currency_short_name', render: function(data) { return escapeHtml(data || 'N/A'); } },
            { data: 'expense_type_name', render: function(data) { return escapeHtml(data); } },
            { data: 'amount', render: function (data) { return parseFloat(data).toFixed(2); } },
            {
                data: 'mca_data',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let count = 0;
                    if (data) {
                        try {
                            count = JSON.parse(data).length;
                        } catch(e) {}
                    }
                    return `<button class="btn btn-xs btn-info view-mca-refs" data-id="${row.id}"><i class="ti ti-eye"></i> ${count}</button>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let status = '', badgeClass = '';
                    
                    if (row.dept_approval == -1 || row.finance_approval == -1 || row.management_approval == -1 || row.paid_approval == -1) {
                        status = 'Rejected'; badgeClass = 'status-rejected';
                    } else if (row.paid_approval == 1) {
                        status = 'Paid'; badgeClass = 'status-paid';
                    } else if (row.management_approval == 1) {
                        status = 'Pending Payment'; badgeClass = 'status-pending-payment';
                    } else if (row.finance_approval == 1) {
                        status = 'Pending Mgmt'; badgeClass = 'status-pending-mgmt';
                    } else if (row.dept_approval == 1) {
                        status = 'Pending Finance'; badgeClass = 'status-pending-finance';
                    } else {
                        status = 'Pending Dept'; badgeClass = 'status-pending-dept';
                    }
                    
                    return `<span class="badge ${badgeClass}">${status}</span>`;
                }
            },
            { data: 'created_at', render: function (data) { return data ? new Date(data).toLocaleDateString() : 'N/A'; } },
          {
    data: null,
    orderable: false,
    searchable: false,
    width: '200px',
    render: function (data, type, row) {
        let editBtn = '', extraBtn = '';
        
        // ============================================
        // EDIT BUTTON LOGIC
        // ============================================
        if (row.created_by == currentUserId) {
            if (row.dept_approval == -1 || row.finance_approval == -1 || row.management_approval == -1 || row.paid_approval == -1) {
                editBtn = `<button class="btn btn-xs btn-warning edit-payment" data-id="${row.id}"><i class="ti ti-edit"></i></button>`;
            } else if (row.dept_approval === null) {
                editBtn = `<button class="btn btn-xs btn-primary edit-payment" data-id="${row.id}"><i class="ti ti-edit"></i></button>`;
            }
        }
        
        // ============================================
        // APPROVAL BUTTONS LOGIC
        // ============================================
        
       // DEPARTMENT APPROVAL
if (row.dept_approval === null) {
    if (userRoleId == 33 && row.location_id == 2) {
        // Role 33 for Location 2
        extraBtn = `<button class="btn btn-xs btn-warning dept-approve" data-id="${row.id}"><i class="ti ti-check"></i> Dept</button>`;
    } else if (userRoleId == 27 && row.location_id == 3) {
        // Role 27 for Location 3
        extraBtn = `<button class="btn btn-xs btn-warning dept-approve" data-id="${row.id}"><i class="ti ti-check"></i> Dept</button>`;
    } else if (userRoleId == 3 && row.location_id != 2 && row.location_id != 3) {
        // Role 3 for all other locations (excluding Location 2 and 3)
        extraBtn = `<button class="btn btn-xs btn-warning dept-approve" data-id="${row.id}"><i class="ti ti-check"></i> Dept</button>`;
    }
}
        // FINANCE APPROVAL
        else if (row.dept_approval == 1 && row.finance_approval === null) {
            if (userRoleId == 34 && row.location_id == 2) {
                // Role 34 for Location 2
                extraBtn = `<button class="btn btn-xs btn-success finance-approve" data-id="${row.id}"><i class="ti ti-check"></i> Finance</button>`;
            } else if (userRoleId == 4 && row.location_id != 2) {
                // Role 4 for all other locations
                extraBtn = `<button class="btn btn-xs btn-success finance-approve" data-id="${row.id}"><i class="ti ti-check"></i> Finance</button>`;
            } else if (userRoleId == 5 && row.payment_type == 'Bank' && row.location_id != 2) {
    // Role 5 for Bank payments only (excluding location 2)
    extraBtn = `<button class="btn btn-xs btn-success finance-approve" data-id="${row.id}"><i class="ti ti-check"></i> Finance</button>`;
}
        }
        // MANAGEMENT APPROVAL
        else if (row.dept_approval == 1 && row.finance_approval == 1 && row.management_approval === null) {
            if (userRoleId == 1) {
                // Role 1 can approve ALL locations
                extraBtn = `<button class="btn btn-xs btn-dark mgmt-approve" data-id="${row.id}"><i class="ti ti-user-check"></i> Mgmt</button>`;
            } else if (userRoleId == 27 && row.location_id == 3) {
                // Role 27 for Location 3
                extraBtn = `<button class="btn btn-xs btn-dark mgmt-approve" data-id="${row.id}"><i class="ti ti-user-check"></i> Mgmt</button>`;
            } else if (userRoleId == 33 && row.location_id == 2) {
                // Role 33 for Location 2
                extraBtn = `<button class="btn btn-xs btn-dark mgmt-approve" data-id="${row.id}"><i class="ti ti-user-check"></i> Mgmt</button>`;
            } else if (userRoleId == 39 && row.location_id == 1) {
                // Role 39 for Location 1
                extraBtn = `<button class="btn btn-xs btn-dark mgmt-approve" data-id="${row.id}"><i class="ti ti-user-check"></i> Mgmt</button>`;
            }
        }
        // MARK AS PAID
        else if (row.dept_approval == 1 && row.finance_approval == 1 && row.management_approval == 1 && row.paid_approval === null) {
            if (userRoleId == 34 && row.location_id == 2) {
                // Role 34 for Location 2 (both Cash & Bank)
                extraBtn = `<button class="btn btn-xs btn-success paid-approve" data-id="${row.id}"><i class="ti ti-currency-dollar"></i> Paid</button>`;
            } else if (userRoleId == 27 && row.location_id == 3 && row.payment_type == 'Cash') {
                // Role 27 for Location 3 (Cash only)
                extraBtn = `<button class="btn btn-xs btn-success paid-approve" data-id="${row.id}"><i class="ti ti-currency-dollar"></i> Paid</button>`;
            } else if (userRoleId == 10 && row.payment_type == 'Cash' && row.location_id != 2 && row.location_id != 3) {
                // Role 10 (Cashier) for Cash payments (excluding location 2 & 3)
                extraBtn = `<button class="btn btn-xs btn-success paid-approve" data-id="${row.id}"><i class="ti ti-currency-dollar"></i> Paid</button>`;
            } else if (userRoleId == 11 && row.payment_type == 'Bank') {
    // Role 11 (Bank Officer) for Bank payments (ALL locations)
    extraBtn = `<button class="btn btn-xs btn-success paid-approve" data-id="${row.id}"><i class="ti ti-currency-dollar"></i> Paid</button>`;
}
        }

        return `
            <button class="btn btn-xs btn-info view-payment" data-id="${row.id}"><i class="ti ti-eye"></i></button>
            <button class="btn btn-xs btn-secondary print-payment" data-id="${row.id}"><i class="ti ti-printer"></i></button>
            ${editBtn}${extraBtn}
        `;
    }
}
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        dom: 'lfrtip',
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
            emptyTable: "No payment requests found",
            search: "Search:"
        },
        drawCallback: function() {
            loadStatusCounts();
        }
    });

    // ============================================
    // VIEW MCA REFERENCES
    // ============================================
    $('#paymentRequestsTable').on('click', '.view-mca-refs', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let id = $(this).data('id');
        
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(res) {
                let refs = res.data.mca_refs || [];
                let html = '';
                
                if (refs.length > 0) {
                    html = `
                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Reference</th>
                                    <th style="width: 120px;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    refs.forEach((r, i) => {
                        html += `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${escapeHtml(r.mca_ref)}</td>
                                <td>${parseFloat(r.amount).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    html += `</tbody></table>`;
                } else {
                    html = '<p class="text-center text-muted">No references found</p>';
                }
                
                $('#mcaRefsViewBody').html(html);
                var mcaModal = new bootstrap.Modal(document.getElementById('mcaRefsViewModal'));
                mcaModal.show();
            }
        );
    });

   // ============================================
    // PDF-MATCHING PRINT DESIGN
    // ============================================
    $('#paymentRequestsTable').on('click', '.print-payment', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        
        // Show loading state
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');
        
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(response) {
                $btn.prop('disabled', false).html(originalHtml);
                
                const data = response.data.data;
                const refs = response.data.mca_refs || [];
                
                // Format date and time
                function formatDate(datetime) {
                    if (!datetime) return 'N/A';
                    let d = new Date(datetime);
                    return d.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }

                function formatTime(datetime) {
                    if (!datetime) return 'N/A';
                    let d = new Date(datetime);
                    return d.toLocaleTimeString('en-GB', { 
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                }

                function formatDateTimeFrench(datetime) {
                    if (!datetime) return 'N/A';
                    let d = new Date(datetime);
                    return d.toLocaleString('fr-FR', { 
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    });
                }

                // Currency name lookup
                const currencyNames = {
                    'USD': 'Dollars Américain',
                    'EUR': 'Euros',
                    'CDF': 'Francs Congolais',
                    'GBP': 'Livres Sterling'
                };
                
                const currencyName = currencyNames[data.currency_short_name] || data.currency_short_name;
                
                // Amount in words
                const amountWords = numberToFrenchWords(parseFloat(data.amount));
                const amountWordsCapitalized = amountWords.charAt(0).toUpperCase() + amountWords.slice(1);
                
                // Chargeback in words (if exists)
                let chargebackWords = '';
                if (data.chargeback) {
                    chargebackWords = numberToFrenchWords(parseFloat(data.chargeback));
                    chargebackWords = chargebackWords.charAt(0).toUpperCase() + chargebackWords.slice(1);
                }

// PAGE 1 CONTENT
const printContentPage1 = `
    <div class="print-container">
        <!-- HEADER -->
        <div class="print-header">
            <div class="print-header-left">
                <img src="${BASE_URL}/assets/images/logo.png" alt="Malabar Logo" class="print-header-logo">
            </div>
            <div class="print-header-right">
                No. 1068, Avenue Ruwe, Quartier Makutano,<br>
                Lubumbashi, DRC<br>
                RCCM: 13-B-1122, ID NAT. 6-9-N91867E<br>
                NIF : A 1309334 L<br>
                VAT Ref # 145/DGI/DGE/INF/BN/TVA/2020<br>
                Capital Social : 45.000.000 FC
            </div>
        </div>

        <!-- TITLE -->
        <div class="print-title-section">
            <div class="print-title">DEMANDE DE FONDS No. ${escapeHtml(data.id)}</div>
        </div>

        <!-- DATE TIME -->
        <div class="print-datetime">
            ${formatDate(data.created_at)}<br>
            ${formatTime(data.created_at)}
        </div>

        <!-- MAIN INFO TABLE -->
        <table class="print-main-table">
            <tr>
                <td class="label">Department:</td>
                <td class="value"><strong>${escapeHtml(data.department_name)}</strong></td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
            <tr>
                <td class="label">Beneficiare:</td>
                <td class="value"><strong>${escapeHtml(data.beneficiary)}</strong></td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
            <tr>
                <td class="label">Montant:</td>
                <td class="value"><strong>${formatAmountFrench(data.amount)}</strong></td>
                <td class="label">Devise:</td>
                <td class="value"><strong>${escapeHtml(data.currency_short_name)}</strong></td>
            </tr>
            <tr>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label">Type:</td>
                <td class="value"><strong>${escapeHtml(data.payment_type)}</strong></td>
            </tr>
            <tr>
                <td class="label">Montant en lettre:</td>
                <td colspan="3" class="value full-width">
                    <strong>${amountWordsCapitalized} ${currencyName}, zéro centime</strong>
                </td>
            </tr>
            ${data.chargeback ? `
            <tr>
                <td class="label">Chargeback:</td>
                <td class="value"><strong>${formatAmountFrench(data.chargeback)}</strong></td>
                <td class="label">Devise:</td>
                <td class="value"><strong>${escapeHtml(data.currency_short_name)}</strong></td>
            </tr>
            <tr>
                <td class="label">Montant en lettre:</td>
                <td colspan="3" class="value full-width">
                    <strong>${chargebackWords} ${currencyName}, zéro centime</strong>
                </td>
            </tr>` : ''}
            <tr>
                <td class="label">Client:</td>
                <td colspan="3" class="value full-width"><strong>${escapeHtml(data.client_name || 'N/A')}</strong></td>
            </tr>
            <tr>
                <td class="label">Motif:</td>
                <td colspan="3" class="value full-width"><strong>${escapeHtml(data.motif)}</strong></td>
            </tr>
        </table>

        <!-- AUTORISATION SECTION -->
        <div class="print-section-title">AUTORISATION</div>
        <table class="print-auth-table">
            <tr>
                <td>
                    <strong>Department:</strong><br><br>
                    ${data.dept_approval == 1 ? `
                        <div class="auth-name">${escapeHtml(data.dept_approved_by_name || 'N/A')}</div>
                        <div class="auth-datetime">${formatDateTimeFrench(data.dept_approved_at)}</div>
                    ` : ''}
                </td>
                <td>
                    <strong>Management Approval:</strong><br><br>
                    ${data.management_approval == 1 ? `
                        <div class="auth-name">${escapeHtml(data.management_approved_by_name || 'N/A')}</div>
                        <div class="auth-datetime">${formatDateTimeFrench(data.management_approved_at)}</div>
                    ` : ''}
                </td>
                <td>
                    <strong>Finance:</strong><br><br>
                    ${data.finance_approval == 1 ? `
                        <div class="auth-name">${escapeHtml(data.finance_approved_by_name || 'N/A')}</div>
                        <div class="auth-datetime">${formatDateTimeFrench(data.finance_approved_at)}</div>
                    ` : ''}
                </td>
            </tr>
        </table>

        <!-- RETRAIT SECTION -->
        <div class="print-section-title">RETRAIT</div>
        <table class="print-retrait-table">
            <tr>
                <td><strong>Caissier:</strong></td>
                <td><strong>Pour Reception:</strong></td>
            </tr>
        </table>
    </div>
`;

// PAGE 2 CONTENT (MCA Details)
let mcaTableHTML = '';
if (refs.length > 0) {
    mcaTableHTML = `
        <div class="print-page-2">
            <div class="print-container">
                <!-- Logo on page 2 -->
                <div class="print-header">
                    <div class="print-header-left">
                        <img src="${BASE_URL}/assets/images/logo.png" alt="Malabar Logo" class="print-header-logo">
                    </div>
                </div>

                <div class="print-details-title">DETAILS - DEMANDE DE FONDS No. ${escapeHtml(data.id)}</div>
                
                <table class="print-details-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>MCA File No</th>
                            <th>Expense</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    let totalAmount = 0;
    refs.forEach((mca, index) => {
        const amount = parseFloat(mca.amount);
        totalAmount += amount;
        mcaTableHTML += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${escapeHtml(mca.mca_ref)}</strong></td>
                <td>${escapeHtml(data.expense_type_name)}</td>
                <td><strong>${formatAmountFrench(amount)}</strong></td>
            </tr>
        `;
    });
    
    mcaTableHTML += `
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: right;"><strong>Total</strong></td>
                    <td><strong>${formatAmountFrench(totalAmount)}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="print-footer">
            Powered by TCPDF (www.tcpdf.org)
        </div>
    </div>
</div>
    `;
}

              // Combine both pages
                const completePrintContent = printContentPage1 + mcaTableHTML;

                // Insert content into hidden section
                $('#hiddenPrintSection').html(completePrintContent);
                
                // ============================================
                // SET CUSTOM PDF FILENAME
                // ============================================
                // Save original title
                const originalTitle = document.title;
                
                // Set new title with payment ID (used as PDF filename by most browsers)
                document.title = `DEMANDE_DE_FONDS_${data.id}`;
                
                // Trigger print after a short delay to ensure rendering
                setTimeout(function() {
                    window.print();
                    
                    // Restore original title after print dialog closes or after 2 seconds
                    setTimeout(function() {
                        document.title = originalTitle;
                    }, 2000);
                }, 200);
            },
            function() {
                $btn.prop('disabled', false).html(originalHtml);
            }
        );
    });

    // ============================================
    // FORM FUNCTIONS
    // ============================================
    
    function closeAccordion() {
        $('#paymentFormContent').removeClass('show');
        $('.accordion-button').addClass('collapsed').attr('aria-expanded', 'false');
    }

    function openAccordion() {
        $('#paymentFormContent').addClass('show');
        $('.accordion-button').removeClass('collapsed').attr('aria-expanded', 'true');
    }

    function resetForm() {
        $('#paymentRequestForm')[0].reset();
        $('#formAction').val('insert');
        $('#recordId').val('');
        $('#formTitle').text('New Payment Request');
        $('#resetFormBtn').hide();
        
        $('#mcaFilesList').html(`
            <tr>
                <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                    <i class="ti ti-info-circle me-1"></i>No MCA references added yet
                </td>
            </tr>
        `);
        
        $('#mca_total_amount').text('0.00');
        $('#paymentRequestForm').removeClass('was-validated');
    }

    $('#resetFormBtn').on('click', function() {
        resetForm();
        closeAccordion();
    });

    $('#clearBtn').on('click', function() {
        closeAccordion();
        resetForm();
    });

    $('#pay_for').change(autoFillOtherReferences);
    $('#location').change(autoFillOtherReferences);
// ============================================
    // ADD MCA REFERENCE ROWS
    // ============================================
    $('#add_mca_refs_btn').on('click', function () {
        const numRefs = parseInt($('#num_mca_refs').val()) || 0;

        if (numRefs <= 0) {
            toastr.warning('Please enter a valid number of references');
            return;
        }

        if (numRefs > 50) {
            toastr.warning('Maximum 50 references allowed');
            return;
        }

        const tbody = $('#mcaFilesList');
        tbody.find('td[colspan="4"]').closest('tr').remove();
        const currentRows = tbody.find('tr').length;

        for (let i = 0; i < numRefs; i++) {
            const rowNum = currentRows + i + 1;
            let autoRef = "";

            const payFor = $('#pay_for').val();
            if (payFor == "3") {
                const locText = $('#location option:selected').text().trim();
                const firstTwo = locText.substring(0, 2).toUpperCase();
                autoRef = `OTH-${firstTwo}-${rowNum}`;
            }
            if (payFor == "4") {
                const locText = $('#location option:selected').text().trim();
                const firstTwo = locText.substring(0, 2).toUpperCase();
                autoRef = `PRE-${firstTwo}-${rowNum}`;
            }
            const readonlyAttr = ((payFor == "4") || (payFor == "3")) ? "readonly" : "";
            
            const newRow = `
                <tr>
                    <td class="text-center">${rowNum}</td>
                    <td>
                        <input type="text" class="form-control mca-reference-input" 
                               name="mca_reference[]" value="${escapeHtml(autoRef)}" ${readonlyAttr}
                               placeholder="${placeholderText}" required>
                    </td>
                    <td>
                        <input type="number" class="form-control mca-amount-input" 
                               name="mca_amount[]" step="0.01" min="0" placeholder="0.00" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-mca-row">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(newRow);
        }

        $('#num_mca_refs').val(0);
        calculateMcaTotal();
        autoFillOtherReferences();
    });

    function autoFillOtherReferences() {
        const payFor = $('#pay_for').val();
        if (payFor != "3" && payFor != "4") return;

        const locText = $('#location option:selected').text().trim();
        if (!locText) return;

        const firstTwo = locText.substring(0, 2).toUpperCase();
        const prefix = payFor == "4" ? "PRE" : "OTH";

        $('.mca-reference-input').each(function (index) {
            const rowNum = index + 1;
            $(this).val(`${prefix}-${firstTwo}-${rowNum}`);
        });
    }

    $('#mcaFilesList').on('click', '.delete-mca-row', function () {
        $(this).closest('tr').remove();
        renumberMcaRows();
        calculateMcaTotal();

        if ($('#mcaFilesList tr').length === 0) {
            $('#mcaFilesList').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                        <i class="ti ti-info-circle me-1"></i>No MCA references added yet
                    </td>
                </tr>
            `);
        }
    });

    function addMcaRow(mcaRef = "", amount = "") {
        const tbody = $('#mcaFilesList');
        tbody.find('td[colspan="4"]').closest('tr').remove();

        const rowNum = tbody.find('tr').length + 1;
        const payFor = $('#pay_for').val();
        const readonlyAttr = ((payFor == "3") || (payFor == "4")) ? "readonly" : "";

        const newRow = `
            <tr>
                <td class="text-center">${rowNum}</td>
                <td>
                    <input type="text" class="form-control mca-reference-input"
                           name="mca_reference[]" value="${escapeHtml(mcaRef)}" ${readonlyAttr} 
                           placeholder="${placeholderText}" required>
                </td>
                <td>
                    <input type="number" class="form-control mca-amount-input"
                           name="mca_amount[]" step="0.01" min="0" value="${amount}" 
                           placeholder="0.00" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger delete-mca-row">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.append(newRow);
        
        if (amount !== '' && amount > 0) {
            const totalRows = tbody.find('tr').length;
            const totalAmount = totalRows * parseFloat(amount);
            $('#amount').val(totalAmount.toFixed(2));
        }
        
        renumberMcaRows();
        calculateMcaTotal();
    }

    // ============================================
    // EXCEL IMPORT WITH TRACKING VALIDATION
    // ============================================
    $('#excel_import').on('change', async function (e) {
        const file = this.files[0];
        
        if (!file) {
            return;
        }
        
        if (!$('#expense_type').val()) {
            toastr.warning('Please select Expense Type before importing');
            $(this).val('');
            return;
        }
        
        const payFor = $('#pay_for').val();
        const clientId = $('#client_id').val();
        
        if ((payFor === '0' || payFor === '1' || payFor === '2') && !clientId) {
            toastr.error('Please select Client before importing tracking references');
            $(this).val('');
            return;
        }
        
        try {
            const reader = new FileReader();
            
            reader.onload = async function (e) {
                try {
                    const data = e.target.result;
                    const workbook = XLSX.read(data, {
                        type: 'binary',
                        cellDates: true,
                        cellNF: false,
                        cellText: false
                    });

                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    const json = XLSX.utils.sheet_to_json(worksheet, {
                        raw: false,
                        dateNF: 'yyyy-mm-dd',
                        header: 1,
                        defval: ''
                    });

                    if (json.length > 0) {
                        const firstRow = json[0];
                        const hasHeader = firstRow.some(cell => {
                            const cellText = String(cell).toLowerCase();
                            return cellText.includes('mca') ||
                                cellText.includes('reference') ||
                                cellText.includes('ref') ||
                                cellText.includes('amount');
                        });

                        if (hasHeader) {
                            json.shift();
                        }
                    }

                    const mcaRefColumn = 0;
                    const amountColumn = 1;

                    const existingRows = document.querySelectorAll('#mcaFilesList tr');
                    if (existingRows.length > 0 && json.length > 0) {
                        if (confirm('Do you want to replace existing MCA references? Click OK to replace, Cancel to append.')) {
                            document.getElementById('mcaFilesList').innerHTML = '';
                        }
                    }

                    await processImportedData(json, mcaRefColumn, amountColumn);
                    
                } catch (error) {
                    console.error('Excel processing error:', error);
                    toastr.error('Failed to process the Excel file. Please check the file format.');
                }
            };

            reader.onerror = function () {
                toastr.error('Failed to read the Excel file.');
            };

            reader.readAsBinaryString(file);
            
        } catch (error) {
            console.error('Import error:', error);
            toastr.error('An unexpected error occurred while importing the file.');
        } finally {
            $(this).val('');
        }
    });

    async function processImportedData(json, mcaRefColumn, amountColumn) {
        try {
            const rowsToProcess = [];
            
            json.forEach(row => {
                if (row && row.length > 0 && row[mcaRefColumn]) {
                    const mcaRef = row[mcaRefColumn].toString().trim();
                    const amount = row.length > amountColumn && row[amountColumn]
                        ? parseFloat(row[amountColumn]) || 0
                        : 0;

                    if (mcaRef) {
                        rowsToProcess.push({ ref: mcaRef, amount: amount });
                    }
                }
            });

            if (rowsToProcess.length === 0) {
                toastr.warning('No valid MCA references found in Excel file');
                return;
            }
            
            toastr.info(`Validating ${rowsToProcess.length} references from Excel...`, 'Please wait', {
                timeOut: 0,
                extendedTimeOut: 0,
                closeButton: true
            });
            
            const validatedRows = await validateMcaBatch(rowsToProcess);
            
            // Clear the "validating" toastr
            toastr.clear();
            
            if (validatedRows.length > 0) {
                validatedRows.forEach(item => {
                    addMcaRow(item.ref, item.amount);
                });

                const numMcaRefs = document.getElementById('num_mca_refs');
                if (numMcaRefs) {
                    const currentRows = document.querySelectorAll('#mcaFilesList tr').length;
                    numMcaRefs.value = currentRows;
                }

                calculateMcaTotal();
                
                const skipped = rowsToProcess.length - validatedRows.length;
                if (skipped > 0) {
                    toastr.success(`✓ ${validatedRows.length} imported, ${skipped} skipped (invalid/duplicate)`, 'Import Complete');
                } else {
                    toastr.success(`✓ All ${validatedRows.length} reference(s) imported successfully!`, 'Import Complete');
                }
            } else {
                toastr.error('All imported references are invalid, duplicates, or not found in tracking system');
            }

        } catch (error) {
            console.error('Import processing error:', error);
            toastr.error('Failed to process the imported data');
        }
    }

    // ============================================
    // FORM SUBMISSION
    // ============================================
    $('#paymentRequestForm').on('submit', function (e) {
        e.preventDefault();

        const mainAmount = parseFloat($('#amount').val()) || 0;
        let mcaTotal = 0;
        $('.mca-amount-input').each(function() {
            mcaTotal += parseFloat($(this).val()) || 0;
        });
        
        if (mcaTotal > 0 && Math.abs(mcaTotal - mainAmount) > 0.01) {
            toastr.error(`MCA total (${mcaTotal.toFixed(2)}) must match Amount (${mainAmount.toFixed(2)})`);
            return;
        }

        const formData = new FormData(this);
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        showLoading('#submitBtn', 'Submitting...');

        $.ajax({
            url: "<?php echo APP_URL; ?>payment/store",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (res) {
                hideLoading('#submitBtn', originalText);
                
                if (res.success) {
                    toastr.success(res.message);
                    paymentTable.ajax.reload(null, false);
                    closeAccordion();
                    resetForm();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr, status, error) {
                hideLoading('#submitBtn', originalText);
                toastr.error('An error occurred. Please try again.');
            }
        });
    });

    // ============================================
    // VIEW PAYMENT DETAILS
    // ============================================
    $('#paymentRequestsTable').on('click', '.view-payment', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(response) {
                const data = response.data.data;
                const refs = response.data.mca_refs || [];
                
                const payForMap = {
                    '0': 'Import Tracking',
                    '1': 'Export Tracking',
                    '2': 'Local Tracking',
                    '3': 'Other',
                    '4': 'Pre Payment'
                };

                let approvalStatus = '';
                if (data.dept_approval == -1 || data.finance_approval == -1 || data.management_approval == -1 || data.paid_approval == -1) {
                    approvalStatus = '<span class="badge status-rejected">Rejected</span>';
                } else if (data.paid_approval == 1) {
                    approvalStatus = '<span class="badge status-paid">Paid</span>';
                } else if (data.management_approval == 1) {
                    approvalStatus = '<span class="badge status-pending-payment">Pending Payment</span>';
                } else if (data.finance_approval == 1) {
                    approvalStatus = '<span class="badge status-pending-mgmt">Pending Mgmt</span>';
                } else if (data.dept_approval == 1) {
                    approvalStatus = '<span class="badge status-pending-finance">Pending Finance</span>';
                } else {
                    approvalStatus = '<span class="badge status-pending-dept">Pending Dept</span>';
                }

                let mcaHTML = '';
                if (refs.length > 0) {
                    mcaHTML = `
                        <div style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Reference</th>
                                        <th style="width: 100px;">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    refs.forEach((mca, index) => {
                        mcaHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${escapeHtml(mca.mca_ref)}</td>
                                <td>${parseFloat(mca.amount).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    mcaHTML += `</tbody></table></div>`;
                } else {
                    mcaHTML = '<p class="text-muted">No references found</p>';
                }

                let filesHTML = buildFileLinks(data);

                function formatDateTime(datetime) {
                    if (!datetime) return 'N/A';
                    let d = new Date(datetime);
                    return d.toLocaleString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                const viewHTML = `
                    <h6 class="mb-2">Payment Information</h6>
                    <table class="table table-sm table-bordered info-table">
                        <tr><th>ID</th><td>#${escapeHtml(data.id)}</td></tr>
                        <tr><th>Status</th><td>${approvalStatus}</td></tr>
                        <tr><th>Department</th><td>${escapeHtml(data.department_name || 'N/A')}</td></tr>
                        <tr><th>Location</th><td>${escapeHtml(data.main_location_name || 'N/A')}</td></tr>
                        <tr><th>Beneficiary</th><td>${escapeHtml(data.beneficiary)}</td></tr>
                        <tr><th>Requestee</th><td>${escapeHtml(data.requestee)}</td></tr>
                        <tr><th>Client</th><td>${escapeHtml(data.client_name || 'N/A')}</td></tr>
                        <tr><th>Payment For</th><td>${escapeHtml(payForMap[data.pay_for] || 'N/A')}</td></tr>
                        <tr><th>Payment Type</th><td>${escapeHtml(data.payment_type)}</td></tr>
                        <tr><th>Currency</th><td>${escapeHtml(data.currency_short_name || 'N/A')}</td></tr>
                        <tr><th>Amount</th><td><strong>${parseFloat(data.amount).toFixed(2)}</strong></td></tr>
                        <tr><th>Expense Type</th><td>${escapeHtml(data.expense_type_name || 'N/A')}</td></tr>
                        <tr><th>Cash Collector</th><td>${escapeHtml(data.cash_collector || 'N/A')}</td></tr>
                        <tr><th>Chargeback</th><td>${data.chargeback ? parseFloat(data.chargeback).toFixed(2) : 'N/A'}</td></tr>
                        <tr><th>Created Date</th><td>${escapeHtml(data.created_at)}</td></tr>
                        <tr><th>Updated Date</th><td>${escapeHtml(data.updated_at || 'N/A')}</td></tr>
                    </table>

                    <h6 class="mt-3 mb-2">Motif / Reason</h6>
                    <div class="alert alert-secondary p-2" style="font-size: 0.85rem;">${escapeHtml(data.motif)}</div>

                    <h6 class="mt-3 mb-2">Attached Documents</h6>
                    ${filesHTML}

                    <h6 class="mt-3 mb-2">References (${refs.length})</h6>
                    ${mcaHTML}

                    <h6 class="mt-3 mb-2">Approval Timeline</h6>
                    <table class="table table-sm table-bordered timeline-table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Finance</th>
                                <th>Management</th>
                                <th>Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    ${data.dept_approval == 1 ? '<span class="badge status-dept-approved">Approved</span>' : 
                                     data.dept_approval == -1 ? '<span class="badge status-rejected">Rejected</span>' : 
                                     '<span class="badge status-pending-dept">Pending Dept</span>'}
                                    ${data.dept_approval == 1 ? `
                                        <span class="timeline-user">${escapeHtml(data.dept_approved_by_name || 'N/A')}</span>
                                        <span class="timeline-time">${formatDateTime(data.dept_approved_at)}</span>
                                    ` : ''}
                                </td>
                                <td>
                                    ${data.finance_approval == 1 ? '<span class="badge status-pending-finance">Approved</span>' : 
                                     data.finance_approval == -1 ? '<span class="badge status-rejected">Rejected</span>' : 
                                     '<span class="badge status-pending-finance">Pending Finance</span>'}
                                    ${data.finance_approval == 1 ? `
                                        <span class="timeline-user">${escapeHtml(data.finance_approved_by_name || 'N/A')}</span>
                                        <span class="timeline-time">${formatDateTime(data.finance_approved_at)}</span>
                                    ` : ''}
                                </td>
                                <td>
                                    ${data.management_approval == 1 ? '<span class="badge status-pending-mgmt">Approved</span>' : 
                                     data.management_approval == -1 ? '<span class="badge status-rejected">Rejected</span>' : 
                                     '<span class="badge status-pending-mgmt">Pending Mgmt</span>'}
                                    ${data.management_approval == 1 ? `
                                        <span class="timeline-user">${escapeHtml(data.management_approved_by_name || 'N/A')}</span>
                                        <span class="timeline-time">${formatDateTime(data.management_approved_at)}</span>
                                    ` : ''}
                                </td>
                                <td>
                                    ${data.paid_approval == 1 ? '<span class="badge status-paid">Paid</span>' : 
                                     data.paid_approval == -1 ? '<span class="badge status-rejected">Rejected</span>' : 
                                     '<span class="badge bg-warning">Pending</span>'}
                                    ${data.paid_approval == 1 ? `
                                        <span class="timeline-user">${escapeHtml(data.paid_approved_by_name || 'N/A')}</span>
                                        <span class="timeline-time">${formatDateTime(data.paid_approved_at)}</span>
                                    ` : ''}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    ${data.dept_notes ? `<div class="alert alert-danger p-2 mt-2"><strong>Dept Rejection:</strong> ${escapeHtml(data.dept_notes)}</div>` : ''}
                    ${data.finance_notes ? `<div class="alert alert-danger p-2 mt-2"><strong>Finance Rejection:</strong> ${escapeHtml(data.finance_notes)}</div>` : ''}
                    ${data.management_notes ? `<div class="alert alert-danger p-2 mt-2"><strong>Mgmt Rejection:</strong> ${escapeHtml(data.management_notes)}</div>` : ''}
                    ${data.paid_notes ? `<div class="alert alert-danger p-2 mt-2"><strong>Payment Rejection:</strong> ${escapeHtml(data.paid_notes)}</div>` : ''}
                `;

                $('#viewModalBody').html(viewHTML);
                var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                viewModal.show();
            }
        );
    });

    // ============================================
    // EDIT PAYMENT
    // ============================================
    $('#paymentRequestsTable').on('click', '.edit-payment', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(response) {
                const data = response.data.data;
                const refs = response.data.mca_refs || [];
                
                $('#formAction').val('update');
                $('#recordId').val(data.id);
                $('#formTitle').text('Edit Payment Request - #' + data.id);
                $('#resetFormBtn').show();

                $('#department').val(data.department);
                $('#location').val(data.location_id);
                $('#beneficiary').val(data.beneficiary);
                $('#requestee').val(data.requestee);
                $('#client_id').val(data.client_id);
                $('#pay_for').val(data.pay_for).trigger('change');
                $('#payment_type').val(data.payment_type);
                $('#currency').val(data.currency);
                $('#amount').val(data.amount);
                
                setTimeout(function() {
                    $('#expense_type').val(data.expense_type);
                }, 300);
                
                $('#motif').val(data.motif);

                $('#mcaFilesList').empty();
                
                if (refs.length > 0) {
                    refs.forEach((mca, index) => {
                        const newRow = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>
                                    <input type="text" class="form-control mca-reference-input" 
                                           name="mca_reference[]" value="${escapeHtml(mca.mca_ref)}"
                                           placeholder="Enter MCA Reference" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control mca-amount-input" 
                                           name="mca_amount[]" step="0.01" min="0" 
                                           value="${mca.amount}" placeholder="0.00" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger delete-mca-row">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        $('#mcaFilesList').append(newRow);
                    });
                } else {
                    $('#mcaFilesList').html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding: 15px;">
                                <i class="ti ti-info-circle me-1"></i>No MCA references added yet
                            </td>
                        </tr>
                    `);
                }

                calculateMcaTotal();
                openAccordion();

                $('html, body').animate({
                    scrollTop: $('#paymentRequestForm').offset().top - 100
                }, 500);
            }
        );
    });

    // ============================================
    // DEPARTMENT APPROVAL
    // ============================================
    $('#paymentRequestsTable').on("click", ".dept-approve", function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        let id = $(this).data("id");
        $('#currentApprovalId').val(id);

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(res) {
                let data = res.data.data;
                let refs = res.data.mca_refs || [];

                let detailsHTML = buildApprovalDetailsHTML(data, refs);
                detailsHTML += `
                    <hr>
                    <h6>Chargeback</h6>
                    <div class="form-group mt-2">
                        <label>Chargeback Needed?</label><br>
                        <label><input type="radio" name="chargeback_needed" value="1"> Yes</label>
                        &nbsp;&nbsp;
                        <label><input type="radio" name="chargeback_needed" value="0" checked> No</label>
                    </div>

                    <div class="form-group mt-2" id="chargebackValueBox" style="display:none;">
                        <label>Chargeback Amount</label>
                        <input type="number" id="chargeback_value" class="form-control form-control-sm" min="0" placeholder="Enter amount">
                    </div>
                `;

                $("#approvalDetails").html(detailsHTML);
                $("#rejectSection").hide();
                $("#rejectReason").val('');
                var deptModal = new bootstrap.Modal(document.getElementById('deptApprovalModal'));
                deptModal.show();
            }
        );
    });

    $(document).on("change", "input[name='chargeback_needed']", function () {
        if ($(this).val() == "1") {
            $("#chargebackValueBox").show();
        } else {
            $("#chargebackValueBox").hide();
            $("#chargeback_value").val('');
        }
    });

    $("#approveBtn").click(function () {
        let id = $('#currentApprovalId').val();
        let chargeback_needed = $("input[name='chargeback_needed']:checked").val();
        let chargeback_value  = $("#chargeback_value").val();

        if (chargeback_needed == "1" && (!chargeback_value || chargeback_value < 1)) {
            toastr.warning("Enter valid chargeback amount");
            return;
        }

        showLoading("#approveBtn", "Approving...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/update_approval",
            {
                id: id,
                action: "dept_approve",
                chargeback_needed: chargeback_needed,
                chargeback_value: chargeback_value
            },
            function(res) {
                hideLoading("#approveBtn", '<i class="ti ti-check"></i> Approve');
                toastr.success("Department Approved Successfully");
                var modal = bootstrap.Modal.getInstance(document.getElementById('deptApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#approveBtn", '<i class="ti ti-check"></i> Approve');
            }
        );
    });

    $("#rejectBtn").click(function() {
        $("#rejectSection").show();
    });

    $("#submitRejectBtn").on("click", function() {
        let id = $('#currentApprovalId').val();
        let reason = $("#rejectReason").val().trim();

        if (reason === "") {
            toastr.warning("Please enter a reason for rejection");
            return;
        }

        showLoading("#submitRejectBtn", "Rejecting...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/reject_request",
            {
                id: id,
                reject_type: "dept",
                reason: reason
            },
            function(res) {
                hideLoading("#submitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
                toastr.success("Rejected Successfully");
                $("#rejectSection").hide();
                $("#rejectReason").val("");
                var modal = bootstrap.Modal.getInstance(document.getElementById('deptApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#submitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
            }
        );
    });

    // ============================================
    // FINANCE APPROVAL
    // ============================================
    $('#paymentRequestsTable').on('click', '.finance-approve', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        let id = $(this).data('id');

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(res) {
                let data = res.data.data;
                let refs = res.data.mca_refs || [];

                let detailsHTML = buildApprovalDetailsHTML(data, refs);
                $("#financeApprovalDetails").html(detailsHTML);

                $("#financeApproveBtn").data("id", id);
                $("#financeRejectBtn").data("id", id);
                $("#financeRejectSection").hide();
                $("#financeRejectReason").val('');
                var finModal = new bootstrap.Modal(document.getElementById('financeApprovalModal'));
                finModal.show();
            }
        );
    });

    $("#financeApproveBtn").click(function () {
        let id = $(this).data("id");
        showLoading("#financeApproveBtn", "Approving...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/update_approval",
            {
                id: id,
                action: "finance_approve"
            },
            function(res) {
                hideLoading("#financeApproveBtn", '<i class="ti ti-check"></i> Approve');
                toastr.success("Finance Approved Successfully");
                var modal = bootstrap.Modal.getInstance(document.getElementById('financeApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#financeApproveBtn", '<i class="ti ti-check"></i> Approve');
            }
        );
    });

    $("#financeRejectBtn").click(function() {
        $("#financeRejectSection").show();
    });

    $("#financeSubmitRejectBtn").on("click", function() {
        let id = $("#financeApproveBtn").data("id");
        let reason = $("#financeRejectReason").val().trim();

        if (reason === "") {
            toastr.warning("Please enter a reason for rejection");
            return;
        }

        showLoading("#financeSubmitRejectBtn", "Rejecting...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/reject_request",
            {
                id: id,
                reject_type: "finance",
                reason: reason
            },
            function(res) {
                hideLoading("#financeSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
                toastr.success("Rejected Successfully");
                $("#financeRejectSection").hide();
                $("#financeRejectReason").val("");
                var modal = bootstrap.Modal.getInstance(document.getElementById('financeApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#financeSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
            }
        );
    });

    // ============================================
    // MANAGEMENT APPROVAL
    // ============================================
    $('#paymentRequestsTable').on('click', '.mgmt-approve', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        let id = $(this).data('id');

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(res) {
                let data = res.data.data;
                let refs = res.data.mca_refs || [];

                let detailsHTML = buildApprovalDetailsHTML(data, refs);
                $("#mgmtApprovalDetails").html(detailsHTML);

                $("#mgmtApproveBtn").data("id", id);
                $("#mgmtRejectBtn").data("id", id);
                $("#mgmtRejectSection").hide();
                $("#mgmtRejectReason").val('');
                var mgmtModal = new bootstrap.Modal(document.getElementById('mgmtApprovalModal'));
                mgmtModal.show();
            }
        );
    });

    $("#mgmtApproveBtn").click(function () {
        let id = $(this).data("id");
        showLoading("#mgmtApproveBtn", "Approving...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/update_approval",
            {
                id: id,
                action: "management_approve"
            },
            function(res) {
                hideLoading("#mgmtApproveBtn", '<i class="ti ti-check"></i> Approve');
                toastr.success("Management Approved Successfully");
                var modal = bootstrap.Modal.getInstance(document.getElementById('mgmtApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#mgmtApproveBtn", '<i class="ti ti-check"></i> Approve');
            }
        );
    });

    $("#mgmtRejectBtn").click(function() {
        $("#mgmtRejectSection").show();
    });

    $("#mgmtSubmitRejectBtn").on("click", function() {
        let id = $("#mgmtApproveBtn").data("id");
        let reason = $("#mgmtRejectReason").val().trim();

        if (reason === "") {
            toastr.warning("Please enter a reason for rejection");
            return;
        }

        showLoading("#mgmtSubmitRejectBtn", "Rejecting...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/reject_request",
            {
                id: id,
                reject_type: "management",
                reason: reason
            },
            function(res) {
                hideLoading("#mgmtSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
                toastr.success("Rejected Successfully");
                $("#mgmtRejectSection").hide();
                $("#mgmtRejectReason").val("");
                var modal = bootstrap.Modal.getInstance(document.getElementById('mgmtApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#mgmtSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
            }
        );
    });

    // ============================================
    // PAID APPROVAL WITH REJECTION
    // ============================================
    $('#paymentRequestsTable').on('click', '.paid-approve', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        let id = $(this).data('id');

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_single",
            { id: id },
            function(res) {
                let data = res.data.data;
                let refs = res.data.mca_refs || [];

                let detailsHTML = buildApprovalDetailsHTML(data, refs);
                $("#paidApprovalDetails").html(detailsHTML);

                $('#paidCashCollector').val(data.cash_collector || '');
                $("#paidApproveBtn").data("id", id);
                $("#paidRejectBtn").data("id", id);
                $("#paidRejectSection").hide();
                $("#paidRejectReason").val('');
                
                var paidModal = new bootstrap.Modal(document.getElementById('paidApprovalModal'));
                paidModal.show();
            }
        );
    });

    $("#paidApproveBtn").click(function () {
        let id = $(this).data("id");
        let cash_collector = $('#paidCashCollector').val().trim();

        if (!cash_collector) {
            toastr.warning("Please enter Cash Collector name");
            return;
        }

        let formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'paid_approve');
        formData.append('cash_collector', cash_collector);
        formData.append('csrf_token', '<?php echo $csrf_token; ?>');

        let file3 = $('#paidFile3')[0].files[0];
        let file4 = $('#paidFile4')[0].files[0];

        if (file3) {
            formData.append('file3', file3);
        }
        if (file4) {
            formData.append('file4', file4);
        }

        showLoading("#paidApproveBtn", "Processing...");

        $.ajax({
            url: "<?php echo APP_URL; ?>payment/update_approval",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res) {
                hideLoading("#paidApproveBtn", '<i class="ti ti-check"></i> Mark as Paid');
                
                if (res.success) {
                    toastr.success("Marked as Paid Successfully");
                    var modal = bootstrap.Modal.getInstance(document.getElementById('paidApprovalModal'));
                    modal.hide();
                    $('#paidFile3').val('');
                    $('#paidFile4').val('');
                    paymentTable.ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                hideLoading("#paidApproveBtn", '<i class="ti ti-check"></i> Mark as Paid');
                toastr.error("An error occurred");
            }
        });
    });

    $("#paidRejectBtn").click(function() {
        $("#paidRejectSection").show();
    });

    $("#paidSubmitRejectBtn").on("click", function() {
        let id = $("#paidApproveBtn").data("id");
        let reason = $("#paidRejectReason").val().trim();

        if (reason === "") {
            toastr.warning("Please enter a reason for rejection");
            return;
        }

        showLoading("#paidSubmitRejectBtn", "Rejecting...");

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/reject_request",
            {
                id: id,
                reject_type: "paid",
                reason: reason
            },
            function(res) {
                hideLoading("#paidSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
                toastr.success("Rejected Successfully");
                $("#paidRejectSection").hide();
                $("#paidRejectReason").val("");
                var modal = bootstrap.Modal.getInstance(document.getElementById('paidApprovalModal'));
                modal.hide();
                paymentTable.ajax.reload(null, false);
            },
            function() {
                hideLoading("#paidSubmitRejectBtn", '<i class="ti ti-send"></i> Submit Rejection');
            }
        );
    });

    // ============================================
    // SELECT MCA REFERENCES WITH VALIDATION
    // ============================================
    $('#selectMcaRefsBtn').on('click', function () {
        let clientId = $('#client_id').val();
        let paymentfor = $('#pay_for').val();
        let expenseType = $('#expense_type').val();
        let location = $('#location').val();

        if (!clientId) {
            toastr.warning('Please select a client first');
            return;
        }
        if (!paymentfor) {
            toastr.warning('Please select a payment for');
            return;
        }
        if (!expenseType) {
            toastr.warning('Please select an expense type first');
            return;
        }

        // Show loading state
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Loading...');

        ajaxRequest(
            "<?php echo APP_URL; ?>payment/get_mca_refs_by_client",
            { client_id: clientId, paymentfor: paymentfor, expenseType: expenseType, location: location },
            function(res) {
                $btn.prop('disabled', false).html(originalText);
                
                if (res.data.length === 0) {
                    toastr.info('No MCA references found for this client in tracking system');
                    return;
                }

                let html = '';
                res.data.forEach(item => {
                    html += `
                        <div class="mca-ref-item">
                            <input class="form-check-input mca-ref-check"
                                type="checkbox"
                                id="mca_${escapeHtml(item.mca_ref)}"
                                data-ref="${escapeHtml(item.mca_ref)}"
                                data-amount="${item.perdiem_amount ? item.perdiem_amount : 0}">
                            <label class="form-check-label" for="mca_${escapeHtml(item.mca_ref)}">
                                <b>${escapeHtml(item.mca_ref)}</b>
                                ${item.perdiem_amount ? ` <span class="badge bg-info">${parseFloat(item.perdiem_amount).toFixed(2)}</span>` : ''}
                            </label>
                        </div>
                    `;
                });

                $('#mcaRefList').html(html);
                $('#mcaSearchInput').val('');
                
                toastr.success(`${res.data.length} valid reference(s) found in tracking system`);
                
                var mcaModal = new bootstrap.Modal(document.getElementById('mcaRefModal'));
                mcaModal.show();
            },
            function() {
                $btn.prop('disabled', false).html(originalText);
            }
        );
    });

    $('#applyMcaRefsBtn').on('click', async function () {
        const $btn = $(this);
        const originalText = $btn.html();
        
        const selectedRefs = [];
        $('.mca-ref-check:checked').each(function () {
            selectedRefs.push({
                ref: $(this).data('ref'),
                amount: $(this).data('amount') || 0
            });
        });
        
        if (selectedRefs.length === 0) {
            toastr.warning('Please select at least one reference');
            return;
        }
        
        $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Validating...');
        
        try {
            const validatedRefs = await validateMcaBatch(selectedRefs);
            
            if (validatedRefs.length > 0) {
                $('#mcaFilesList').html('');
                
                validatedRefs.forEach(item => {
                    addMcaRow(item.ref, item.amount);
                });
                
                calculateMcaTotal();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('mcaRefModal'));
                modal.hide();
                
                toastr.success(`${validatedRefs.length} reference(s) added successfully`);
            } else {
                toastr.error('All selected references are duplicates or invalid');
            }
        } catch (error) {
            console.error('MCA validation error:', error);
            toastr.error('An error occurred during validation');
        } finally {
            $btn.prop('disabled', false).html(originalText);
        }
    });

    // ============================================
    // EXPORT TO EXCEL
    // ============================================
    $('#exportToExcelBtn').on('click', function() {
        showLoading('#exportToExcelBtn', 'Exporting...');
        
        ajaxRequest(
            "<?php echo APP_URL; ?>payment/export_to_excel",
            {},
            function(res) {
                hideLoading('#exportToExcelBtn', '<i class="ti ti-file-excel"></i> Export to Excel');
                
                if (res.data.length > 0) {
                    const payForMap = {
                        '0': 'Import Tracking',
                        '1': 'Export Tracking',
                        '2': 'Local Tracking',
                        '3': 'Other',
                        '4': 'Pre Payment'
                    };

                    let exportData = res.data.map(row => ({
                        'ID': row.id,
                        'Requestee': row.requestee,
                        'Beneficiary': row.beneficiary,
                        'Client': row.client_name,
                        'Payment For': payForMap[row.pay_for] || 'N/A',
                        'Payment Type': row.payment_type,
                        'Currency': row.currency_short_name,
                        'Expense Type': row.expense_type_name,
                        'Amount': parseFloat(row.amount).toFixed(2),
                        'Location': row.main_location_name,
                        'Cash Collector': row.cash_collector || 'N/A',
                        'Motif': row.motif,
                        'Date': row.created_at
                    }));

                    let ws = XLSX.utils.json_to_sheet(exportData);
                    let wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Payment Requests");
                    
                    XLSX.writeFile(wb, "Payment_Requests_" + new Date().toISOString().slice(0,10) + ".xlsx");
                    toastr.success('Data exported successfully');
                } else {
                    toastr.info('No data to export');
                }
            },
            function() {
                hideLoading('#exportToExcelBtn', '<i class="ti ti-file-excel"></i> Export to Excel');
            }
        );
    });

    // ============================================
    // HELPER FUNCTIONS FOR MCA
    // ============================================
    function renumberMcaRows() {
        $('#mcaFilesList tr').each(function (index) {
            const firstCell = $(this).find('td:first-child');
            if (!firstCell.attr('colspan')) {
                firstCell.text(index + 1);
            }
        });
    }

    function calculateMcaTotal() {
        let total = 0;
        $('.mca-amount-input').each(function () {
            const value = parseFloat($(this).val()) || 0;
            total += value;
        });
        $('#mca_total_amount').text(total.toFixed(2));
    }

    $('#mcaFilesList').on('input', '.mca-amount-input', calculateMcaTotal);

    console.log('✅ Payment System v12.0 - PDF-Matching Print Design + Location 2 Support');
    console.log('✅ All Features: Form, Approvals, Excel, MCA Validation, PDF Print, Location-Based Access');
});
</script>

<?php 
if (file_exists(VIEW_PATH . 'layouts/partials/footer.php')) {
  include(VIEW_PATH . 'layouts/partials/footer.php'); 
}
?>