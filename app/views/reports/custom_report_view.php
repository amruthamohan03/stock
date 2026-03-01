<?php
/**
 * FILE: resources/views/reports/custom_report_view.php
 *
 * Custom Stock Reports View
 * — Summary Report with DataTable
 * — Detailed Report with DataTable
 * — PDF Export for both
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
        }

        body {
            background-color: #F9FAFB;
        }

        .page-container {
            padding: 2rem 0;
        }

        .header-section {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }

        .header-section h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .header-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.95;
        }

        .filter-section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .filter-section h5 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
        }

        .form-control, .form-select {
            border: 1px solid #E5E7EB;
            border-radius: 0.5rem;
            padding: 0.6rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-apply {
            background: linear-gradient(135deg, var(--primary) 0%, #667eea 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-apply:hover {
            background: linear-gradient(135deg, #3f37e5 0%, #5568ea 100%);
            transform: translateY(-2px);
        }

        .btn-export {
            background: var(--success);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .report-section {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .report-section h5 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-section h5 i {
            font-size: 1.25rem;
        }

        .datatable-wrapper {
            overflow-x: auto;
        }

        table.dataTable {
            font-size: 0.85rem;
        }

        table.dataTable thead th {
            background-color: #F3F4F6;
            color: #111827;
            font-weight: 600;
            border-bottom: 2px solid #E5E7EB;
            padding: 0.75rem !important;
        }

        table.dataTable tbody td {
            padding: 0.6rem !important;
            color: #374151;
        }

        table.dataTable tbody tr:hover {
            background-color: #F9FAFB !important;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.35rem 0.6rem;
            border-radius: 0.3rem;
        }

        .badge-success {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .badge-info {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .summary-stat {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 1.5rem;
            border-radius: 0.6rem;
            text-align: center;
        }

        .stat-card h6 {
            color: #6B7280;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert-info {
            background-color: #DBEAFE;
            border: 1px solid #93c5fd;
            color: #1E40AF;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .tab-navigation {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #E5E7EB;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: #6B7280;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @media (max-width: 768px) {
            .filter-section {
                padding: 1rem;
            }

            .report-section {
                padding: 1rem;
            }

            .summary-stat {
                grid-template-columns: 1fr;
            }
        }
    </style>
<!-- </head>
<body> -->
    <div class="page-content">
<div class="page-container">
    <div class="container-fluid page-container">
        <!-- Header -->
        <div class="header-section">
            <h2><i class="fas fa-chart-bar"></i> Custom Stock Reports</h2>
            <p>Generate Summary and Detailed Reports on Stock Transactions</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Reports</h5>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Institution</label>
                    <select id="institution_id" class="form-select">
                        <option value="">-- Select Institution --</option>
                        <?php foreach ($institutions as $inst): ?>
                            <option value="<?= $inst['id']; ?>"><?= htmlspecialchars($inst['college_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select id="dept_id" class="form-select">
                        <option value="">-- Select Department --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" id="from_date" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" id="to_date" class="form-control">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn-apply w-100" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div id="summary-stats" class="summary-stat" style="display:none;"></div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" onclick="switchTab('summary')">
                <i class="fas fa-table"></i> Summary Report
            </button>
            <button class="tab-btn" onclick="switchTab('detailed')">
                <i class="fas fa-list"></i> Detailed Report
            </button>
        </div>

        <!-- Summary Report -->
        <div id="summary-tab" class="tab-content active">
            <div class="report-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h5><i class="fas fa-chart-pie"></i> Summary Report - Group-wise Stock Count</h5>
                    <button class="btn-export" onclick="exportSummaryPdf()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>

                <div class="alert-info">
                    Shows total count of items received (Indent-based + Transfer), issued, deleted, and balance by item group.
                </div>

                <div class="loading" id="summary-loading">
                    <div class="spinner"></div>
                    <p>Loading data...</p>
                </div>

                <div class="datatable-wrapper">
                    <table id="summary-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width:50px">S.No</th>
                                <th>Group/Item Name</th>
                                <th style="text-align:center">Total Items</th>
                                <th style="text-align:center">Indent Received</th>
                                <th style="text-align:center">Transfer Received</th>
                                <th style="text-align:center;font-weight:700">Total Received</th>
                                <th style="text-align:center;color:#c62828">Total Issued</th>
                                <th style="text-align:center;color:#d32f2f">Deleted</th>
                                <th style="text-align:center;font-weight:700;background:#e8f5e9">Balance</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Report -->
        <div id="detailed-tab" class="tab-content">
            <div class="report-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h5><i class="fas fa-list"></i> Detailed Report - Indent-wise Stock Details</h5>
                    <button class="btn-export" onclick="exportDetailedPdf()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>

                <div class="alert-info">
                    Detailed view showing Indent numbers, Stock Book page numbers, Make/Model, and transaction counts for each item.
                </div>

                <div class="loading" id="detailed-loading">
                    <div class="spinner"></div>
                    <p>Loading data...</p>
                </div>

                <div class="datatable-wrapper">
                    <table id="detailed-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width:50px">S.No</th>
                                <th>Group/Item</th>
                                <th style="text-align:center">Indent No</th>
                                <th style="text-align:center">Indent Date</th>
                                <th style="text-align:center">SB Page</th>
                                <th>Make/Model</th>
                                <th style="text-align:center">Rcvd (I)</th>
                                <th style="text-align:center">Rcvd (T)</th>
                                <th style="text-align:center;font-weight:700">Total Rcvd</th>
                                <th style="text-align:center;color:#c62828">Issued</th>
                                <th style="text-align:center;color:#d32f2f">Deleted</th>
                                <th style="text-align:center;font-weight:700;background:#e8f5e9">Balance</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<div class="page-content">
<div class="page-container">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>

    <script>
        const baseUrl = '<?= APP_URL; ?>';
        let summaryTable = null;
        let detailedTable = null;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Cascade departments on institution change
            document.getElementById('institution_id').addEventListener('change', function() {
                loadDepartments(this.value);
            });

            // Initialize DataTables
            initSummaryTable();
            initDetailedTable();

            // Load initial data
            applyFilters();
        });

        // Load departments
        function loadDepartments(instId) {
            const deptSelect = document.getElementById('dept_id');
            deptSelect.innerHTML = '<option value="">-- Select Department --</option>';

            if (!instId) return;

            fetch(baseUrl + 'custom/getDepartments?institution_id=' + instId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length) {
                        data.data.forEach(dept => {
                            deptSelect.innerHTML += '<option value="' + dept.id + '">' + 
                                htmlspecialchars(dept.department_name) + '</option>';
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Initialize Summary Table
        function initSummaryTable() {
            summaryTable = new DataTable('#summary-table', {
                processing: false,
                searching: true,
                ordering: true,
                paging: true,
                pageLength: 25,
                language: {
                    emptyTable: "No data available",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                }
            });
        }

        // Initialize Detailed Table
        function initDetailedTable() {
            detailedTable = new DataTable('#detailed-table', {
                processing: false,
                searching: true,
                ordering: true,
                paging: true,
                pageLength: 50,
                scrollX: true,
                language: {
                    emptyTable: "No data available",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                }
            });
        }

        // Apply Filters
        function applyFilters() {
            loadSummaryReport();
            loadDetailedReport();
        }

        // Load Summary Report
        function loadSummaryReport() {
            const params = buildFilterParams();
            const loading = document.getElementById('summary-loading');
            
            loading.classList.add('active');

            fetch(baseUrl + 'custom/getSummaryData?' + params)
                .then(response => response.json())
                .then(data => {
                    loading.classList.remove('active');
                    if (data.success) {
                        populateSummaryTable(data.data);
                        updateSummaryStats(data.data);
                    }
                })
                .catch(error => {
                    loading.classList.remove('active');
                    console.error('Error:', error);
                    alert('Error loading summary report');
                });
        }

        // Load Detailed Report
        function loadDetailedReport() {
            const params = buildFilterParams();
            const loading = document.getElementById('detailed-loading');
            
            loading.classList.add('active');

            fetch(baseUrl + 'custom/getDetailedData?' + params)
                .then(response => response.json())
                .then(data => {
                    loading.classList.remove('active');
                    if (data.success) {
                        populateDetailedTable(data.data);
                    }
                })
                .catch(error => {
                    loading.classList.remove('active');
                    // console.error('Error:', error);
                    // alert('Error loading detailed report');
                });
        }

        // Build Filter Parameters
        function buildFilterParams() {
            const instId = document.getElementById('institution_id').value || '';
            const deptId = document.getElementById('dept_id').value || '';
            const fromDate = document.getElementById('from_date').value || '';
            const toDate = document.getElementById('to_date').value || '';

            let params = '';
            if (instId) params += 'institution_id=' + instId + '&';
            if (deptId) params += 'dept_id=' + deptId + '&';
            if (fromDate) params += 'from=' + fromDate + '&';
            if (toDate) params += 'to=' + toDate + '&';

            return params.slice(0, -1);
        }

        // Populate Summary Table
        function populateSummaryTable(data) {
            summaryTable.clear();
            
            data.forEach((row, index) => {
                summaryTable.row.add([
                    index + 1,
                    row.group_name,
                    row.total_items,
                    row.indent_received,
                    row.transfer_received,
                    '<strong>' + row.total_received + '</strong>',
                    '<span style="color:#c62828">' + row.total_issued + '</span>',
                    '<span style="color:#d32f2f"><strong>' + row.total_deleted + '</strong></span>',
                    '<span style="background:#e8f5e9;padding:2px 6px;border-radius:3px;font-weight:bold">' + row.total_balance + '</span>'
                ]);
            });
            
            summaryTable.draw();
        }

        // Populate Detailed Table
        function populateDetailedTable(data) {
            detailedTable.clear();
            let total_balance;
            data.forEach((row, index) => {
                const makeModel = (row.make_name ? row.make_name : '') + 
                    (row.model_name ? ' / ' + row.model_name : '');
                total_balance = row.total_received-(row.total_issued+row.total_deleted);
                detailedTable.row.add([
                    index + 1,
                    row.group_name+'<br>'+row.item_name,
                    row.indent_no,
                    row.indent_date,
                    row.stockbook_page_no || 'N/A',
                    makeModel+'<br>'+row.item_description || 'N/A',
                    row.indent_received,
                    row.transfer_received,
                    '<strong>' + row.total_received + '</strong>',
                    '<span style="color:#c62828">' + row.total_issued + '</span>',
                    '<span style="color:#d32f2f">' + row.total_deleted + '</span>',
                    '<span style="background:#e8f5e9;padding:2px 6px;border-radius:3px;font-weight:bold">' + total_balance + '</span>'
                ]);
            });
            
            detailedTable.draw();
        }

        // Update Summary Stats
        function updateSummaryStats(data) {
            let totalItems = 0;
            let totalRecv = 0;
            let totalIssued = 0;
            let totalDeleted = 0;
            let totalBalance = 0;

            data.forEach(row => {
                totalItems += parseInt(row.total_items);
                totalRecv += parseInt(row.total_received);
                totalIssued += parseInt(row.total_issued);
                totalDeleted += parseInt(row.deleted_count);
                totalBalance += parseInt(row.total_balance);
            });

            const statsHtml = `
                <div class="stat-card">
                    <h6>Total Item Groups</h6>
                    <div class="value">${data.length}</div>
                </div>
                <div class="stat-card">
                    <h6>Total Items</h6>
                    <div class="value">${totalItems}</div>
                </div>
                <div class="stat-card">
                    <h6>Total Received</h6>
                    <div class="value" style="color:#10B981">${totalRecv}</div>
                </div>
                <div class="stat-card">
                    <h6>Total Issued</h6>
                    <div class="value" style="color:#EF4444">${totalIssued}</div>
                </div>
                <div class="stat-card">
                    <h6>Deleted</h6>
                    <div class="value" style="color:#F59E0B">${totalDeleted}</div>
                </div>
                <div class="stat-card">
                    <h6>Current Balance</h6>
                    <div class="value" style="color:#4F46E5">${totalBalance}</div>
                </div>
            `;

            document.getElementById('summary-stats').innerHTML = statsHtml;
            document.getElementById('summary-stats').style.display = 'grid';
        }

        // Export Summary PDF
        function exportSummaryPdf() {
            const params = buildFilterParams();
            window.location.href = baseUrl + 'custom/exportSummaryPdf?' + params;
        }

        // Export Detailed PDF
        function exportDetailedPdf() {
            const params = buildFilterParams();
            window.location.href = baseUrl + 'custom/exportDetailedPdf?' + params;
        }

        // Switch Tab
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        // HTML Escape
        function htmlspecialchars(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>