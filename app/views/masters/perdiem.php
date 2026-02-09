<style>
/* Custom Dropdown with Checkboxes */
.multiselect-dropdown {
    position: relative;
    width: 100%;
}

.multiselect-dropdown .dropdown-toggle {
    width: 100%;
    text-align: left;
    background: white;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.multiselect-dropdown .dropdown-toggle:after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    border: none;
}

.multiselect-dropdown .dropdown-menu {
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
}

.multiselect-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    cursor: pointer;
}

.multiselect-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.multiselect-dropdown input[type="checkbox"] {
    margin-right: 8px;
    cursor: pointer;
}

.multiselect-dropdown label {
    margin-bottom: 0;
    cursor: pointer;
    width: 100%;
}

.select-all-option {
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
    background-color: #f8f9fa;
}

.selected-count {
    color: #0d6efd;
    font-weight: 600;
}

.filter-section {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    margin-bottom: 1rem;
}
</style>

<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Per Diem Master</h4>
                    </div>

                    <div class="card-body">
                        <form id="perdiemInsertForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">
                                        Client <span class="text-danger">*</span>
                                    </label>
                                    <div class="multiselect-dropdown">
                                        <button type="button" class="dropdown-toggle" id="clientDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span id="selectedClientsText">Select clients...</span>
                                        </button>
                                        <ul class="dropdown-menu" id="clientDropdownMenu">
                                            <li class="select-all-option">
                                                <div class="dropdown-item">
                                                    <label>
                                                        <input type="checkbox" id="selectAllClients">
                                                        <strong>Select All</strong>
                                                    </label>
                                                </div>
                                            </li>
                                            <?php if (!empty($clients)): ?>
                                                <?php foreach ($clients as $client): ?>
                                                    <li>
                                                        <div class="dropdown-item">
                                                            <label>
                                                                <input type="checkbox" class="client-checkbox" name="client_id[]" value="<?= $client['id'] ?>">
                                                                <?= htmlspecialchars($client['short_name']) ?>
                                                            </label>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <small class="text-info">💡 Select one or more clients</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="transport_mode_id" class="form-label">Transport Mode <span class="text-danger">*</span></label>
                                    <select class="form-select" id="transport_mode_id" name="transport_mode_id" required>
                                        <option value="">-- Select Transport --</option>
                                        <?php if (!empty($transportModes)): ?>
                                            <?php foreach ($transportModes as $transport): ?>
                                                <option value="<?= $transport['id'] ?>">
                                                    <?= htmlspecialchars($transport['transport_mode_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="im_ex_lo_id" class="form-label">Import\Export\Local <span class="text-danger">*</span></label>
                                    <select class="form-select" id="im_ex_lo_id" name="im_ex_lo_id" required>
                                        <option value="">-- Select --</option>
                                        <option value="1">Import</option>
                                        <option value="2">Export</option>
                                        <option value="3">Local</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="goods_type_id" class="form-label">Type of Goods <span class="text-danger">*</span></label>
                                    <select class="form-select" id="goods_type_id" name="goods_type_id" required>
                                        <option value="">-- Select Goods --</option>
                                        <?php if (!empty($goodsTypes)): ?>
                                            <?php foreach ($goodsTypes as $goods): ?>
                                                <option value="<?= $goods['id'] ?>">
                                                    <?= htmlspecialchars($goods['goods_type']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                                    <select class="form-select" id="location_id" name="location_id" required>
                                        <option value="">-- Select Location --</option>
                                        <?php if (!empty($locations)): ?>
                                            <?php foreach ($locations as $location): ?>
                                                <option value="<?= $location['id'] ?>">
                                                    <?= htmlspecialchars($location['main_location_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="perdiem_amount" class="form-label">Amount (USD) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="perdiem_amount" name="perdiem_amount" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info mb-3">
                                        <i class="mdi mdi-information"></i> 
                                        <strong>Bulk Insert:</strong> Select multiple clients to create per diem records for all of them with the same transport, goods type, location, and amount.
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Per Diem (Bulk)
                                </button>
                                <button type="reset" class="btn btn-secondary" id="resetFormBtn">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title mb-0">Per Diem List</h4>
                            <button type="button" class="btn btn-success" id="exportToExcelBtn">
                                <i class="mdi mdi-file-excel"></i> Export to Excel
                            </button>
                        </div>

                        <!-- Filter Section -->
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Filter by Client</label>
                                    <select class="form-select form-select-sm" id="filter_client_id">
                                        <option value="">-- All Clients --</option>
                                        <?php if (!empty($clients)): ?>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?= $client['id'] ?>">
                                                    <?= htmlspecialchars($client['short_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Transport Mode</label>
                                    <select class="form-select form-select-sm" id="filter_transport_mode_id">
                                        <option value="">-- All --</option>
                                        <?php if (!empty($transportModes)): ?>
                                            <?php foreach ($transportModes as $transport): ?>
                                                <option value="<?= $transport['id'] ?>">
                                                    <?= htmlspecialchars($transport['transport_mode_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Goods Type</label>
                                    <select class="form-select form-select-sm" id="filter_goods_type_id">
                                        <option value="">-- All --</option>
                                        <?php if (!empty($goodsTypes)): ?>
                                            <?php foreach ($goodsTypes as $goods): ?>
                                                <option value="<?= $goods['id'] ?>">
                                                    <?= htmlspecialchars($goods['goods_type']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Location</label>
                                    <select class="form-select form-select-sm" id="filter_location_id">
                                        <option value="">-- All --</option>
                                        <?php if (!empty($locations)): ?>
                                            <?php foreach ($locations as $location): ?>
                                                <option value="<?= $location['id'] ?>">
                                                    <?= htmlspecialchars($location['main_location_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Mode</label>
                                    <select class="form-select form-select-sm" id="filter_im_ex_lo_id">
                                        <option value="">-- All --</option>
                                        <option value="1">Import</option>
                                        <option value="2">Export</option>
                                        <option value="3">Local</option>
                                    </select>
                                </div>

                                <div class="col-md-1 mb-2">
                                    <label class="form-label fw-bold">&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-warning w-100" id="clearFiltersBtn">
                                        <i class="mdi mdi-filter-remove"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <table id="perdiem-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Transport</th>
                                    <th>Goods Type</th>
                                    <th>Location</th>
                                    <th>Mode</th>
                                    <th>Amount (USD)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result) && is_array($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr>
                                            <td><?= isset($row['id']) ? htmlspecialchars($row['id']) : '' ?></td>
                                            <td><strong><?= isset($row['client_code']) ? htmlspecialchars($row['client_code']) : '' ?></strong></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= isset($row['transport_mode_name']) ? htmlspecialchars($row['transport_mode_name']) : '' ?>
                                                </span>
                                            </td>
                                            <td><?= isset($row['goods_type']) ? htmlspecialchars($row['goods_type']) : '' ?></td>
                                            <td><?= isset($row['main_location_name']) ? htmlspecialchars($row['main_location_name']) : '' ?></td>
                                            <td>
                                                <?php
                                                    $map = [
                                                        1 => 'Import',
                                                        2 => 'Export',
                                                        3 => 'Local'
                                                    ];
                                                    echo isset($map[$row['im_ex_lo_id']]) ? $map[$row['im_ex_lo_id']] : '';
                                                ?>
                                            </td>
                                            <td class="text-end"><strong><?= isset($row['perdiem_amount']) ? number_format($row['perdiem_amount'], 2) : '0.00' ?></strong></td>
                                            <td>
                                                <?php if (isset($row['display']) && $row['display'] == 'Y'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary editPerdiemBtn" 
                                                   data-id="<?= isset($row['id']) ? $row['id'] : '' ?>" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-info copyPerdiemBtn" 
                                                   data-id="<?= isset($row['id']) ? $row['id'] : '' ?>" title="Copy">
                                                    <i class="ti ti-copy"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger deletePerdiemBtn" 
                                                   data-id="<?= isset($row['id']) ? $row['id'] : '' ?>" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Edit Modal with Bulk Client Selection -->
<div class="modal fade" id="perdiemEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="perdiemUpdateForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Per Diem (Bulk Update)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i> 
                        <strong>Bulk Edit:</strong> Select multiple clients to apply the same changes to all of them. The first selected client will UPDATE the existing record, others will CREATE new records.
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <div class="multiselect-dropdown">
                                <button type="button" class="dropdown-toggle" id="editClientDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="editSelectedClientsText">Select clients...</span>
                                </button>
                                <ul class="dropdown-menu" id="editClientDropdownMenu">
                                    <li class="select-all-option">
                                        <div class="dropdown-item">
                                            <label>
                                                <input type="checkbox" id="editSelectAllClients">
                                                <strong>Select All</strong>
                                            </label>
                                        </div>
                                    </li>
                                    <?php if (!empty($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                            <li>
                                                <div class="dropdown-item">
                                                    <label>
                                                        <input type="checkbox" class="edit-client-checkbox" name="client_id[]" value="<?= $client['id'] ?>">
                                                        <?= htmlspecialchars($client['short_name']) ?>
                                                    </label>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <small class="text-info">💡 Select one or more clients to apply changes</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                            <select name="transport_mode_id" id="edit_transport_mode_id" class="form-select" required>
                                <option value="">-- Select Transport --</option>
                                <?php if (!empty($transportModes)): ?>
                                    <?php foreach ($transportModes as $transport): ?>
                                        <option value="<?= $transport['id'] ?>">
                                            <?= htmlspecialchars($transport['transport_mode_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="edit_im_ex_lo_id" class="form-label">Import\Export\Local <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_im_ex_lo_id" name="im_ex_lo_id" required>
                                <option value="">-- Select --</option>
                                <option value="1">Import</option>
                                <option value="2">Export</option>
                                <option value="3">Local</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type of Goods <span class="text-danger">*</span></label>
                            <select name="goods_type_id" id="edit_goods_type_id" class="form-select" required>
                                <option value="">-- Select Goods Type --</option>
                                <?php if (!empty($goodsTypes)): ?>
                                    <?php foreach ($goodsTypes as $goods): ?>
                                        <option value="<?= $goods['id'] ?>">
                                            <?= htmlspecialchars($goods['goods_type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <select name="location_id" id="edit_location_id" class="form-select" required>
                                <option value="">-- Select Location --</option>
                                <?php if (!empty($locations)): ?>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['id'] ?>">
                                            <?= htmlspecialchars($location['main_location_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (USD) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="perdiem_amount" id="edit_perdiem_amount" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display</label>
                            <select name="display" id="edit_display" class="form-select">
                                <option value="Y">Active</option>
                                <option value="N">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check"></i> Update Per Diem
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Copy Modal -->
<div class="modal fade" id="perdiemCopyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="perdiemCopyForm">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-copy"></i> Copy Per Diem Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i> 
                        <strong>Note:</strong> Modify the fields as needed. The system will prevent duplicate combinations.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select name="client_id" id="copy_client_id" class="form-select" required>
                                <option value="">-- Select Client --</option>
                                <?php if (!empty($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>">
                                            <?= htmlspecialchars($client['short_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transport Mode <span class="text-danger">*</span></label>
                            <select name="transport_mode_id" id="copy_transport_mode_id" class="form-select" required>
                                <option value="">-- Select Transport --</option>
                                <?php if (!empty($transportModes)): ?>
                                    <?php foreach ($transportModes as $transport): ?>
                                        <option value="<?= $transport['id'] ?>">
                                            <?= htmlspecialchars($transport['transport_mode_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="copy_im_ex_lo_id" class="form-label">Import\Export\Local <span class="text-danger">*</span></label>
                            <select class="form-select" id="copy_im_ex_lo_id" name="im_ex_lo_id" required>
                                <option value="">-- Select --</option>
                                <option value="1">Import</option>
                                <option value="2">Export</option>
                                <option value="3">Local</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type of Goods <span class="text-danger">*</span></label>
                            <select name="goods_type_id" id="copy_goods_type_id" class="form-select" required>
                                <option value="">-- Select Goods Type --</option>
                                <?php if (!empty($goodsTypes)): ?>
                                    <?php foreach ($goodsTypes as $goods): ?>
                                        <option value="<?= $goods['id'] ?>">
                                            <?= htmlspecialchars($goods['goods_type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location <span class="text-danger">*</span></label>
                            <select name="location_id" id="copy_location_id" class="form-select" required>
                                <option value="">-- Select Location --</option>
                                <?php if (!empty($locations)): ?>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?= $location['id'] ?>">
                                            <?= htmlspecialchars($location['main_location_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount (USD) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="perdiem_amount" id="copy_perdiem_amount" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display</label>
                            <select name="display" id="copy_display" class="form-select">
                                <option value="Y">Active</option>
                                <option value="N">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="ti ti-copy"></i> Create Copy
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    // Store original data for filtering
    var tableData = [];
    
    $('#perdiem-datatable tbody tr').each(function() {
        var $row = $(this);
        tableData.push({
            row: $row,
            client: $row.find('td').eq(1).text().trim(),
            transport: $row.find('td').eq(2).text().trim(),
            goods: $row.find('td').eq(3).text().trim(),
            location: $row.find('td').eq(4).text().trim(),
            mode: $row.find('td').eq(5).text().trim()
        });
    });

    // Initialize DataTable
    var table = $('#perdiem-datatable').DataTable({
        "columnDefs": [{ "orderable": false, "targets": -1 }],
        "language": { "emptyTable": "No per diem records found" },
        "pageLength": 25
    });

    // ==================== CUSTOM FILTER FUNCTIONALITY ====================
    function applyFilters() {
        var filterClient = $('#filter_client_id option:selected').text().trim();
        var filterTransport = $('#filter_transport_mode_id option:selected').text().trim();
        var filterGoods = $('#filter_goods_type_id option:selected').text().trim();
        var filterLocation = $('#filter_location_id option:selected').text().trim();
        var filterMode = $('#filter_im_ex_lo_id option:selected').text().trim();

        // Clear DataTable search
        table.search('').columns().search('').draw();

        // Hide all rows first
        $('#perdiem-datatable tbody tr').hide();

        // Filter and show matching rows
        tableData.forEach(function(item) {
            var showRow = true;

            // Client filter
            if (filterClient && filterClient !== '-- All Clients --') {
                if (item.client !== filterClient) {
                    showRow = false;
                }
            }

            // Transport filter
            if (filterTransport && filterTransport !== '-- All --') {
                if (item.transport !== filterTransport) {
                    showRow = false;
                }
            }

            // Goods filter
            if (filterGoods && filterGoods !== '-- All --') {
                if (item.goods !== filterGoods) {
                    showRow = false;
                }
            }

            // Location filter
            if (filterLocation && filterLocation !== '-- All --') {
                if (item.location !== filterLocation) {
                    showRow = false;
                }
            }

            // Mode filter
            if (filterMode && filterMode !== '-- All --') {
                if (item.mode !== filterMode) {
                    showRow = false;
                }
            }

            if (showRow) {
                item.row.show();
            }
        });

        // Update DataTable info
        table.draw(false);
    }

    // Attach filter events
    $('#filter_client_id, #filter_transport_mode_id, #filter_goods_type_id, #filter_location_id, #filter_im_ex_lo_id').on('change', function() {
        applyFilters();
    });

    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#filter_client_id, #filter_transport_mode_id, #filter_goods_type_id, #filter_location_id, #filter_im_ex_lo_id').val('');
        $('#perdiem-datatable tbody tr').show();
        table.draw(false);
    });

    // ==================== EXPORT TO EXCEL ====================
    $('#exportToExcelBtn').on('click', function() {
        var params = [];
        
        // Get filter values (IDs, not text)
        var clientId = $('#filter_client_id').val();
        var transportId = $('#filter_transport_mode_id').val();
        var goodsId = $('#filter_goods_type_id').val();
        var locationId = $('#filter_location_id').val();
        var modeId = $('#filter_im_ex_lo_id').val();
        
        // Build query string
        if (clientId) params.push('filter_client_id=' + clientId);
        if (transportId) params.push('filter_transport_mode_id=' + transportId);
        if (goodsId) params.push('filter_goods_type_id=' + goodsId);
        if (locationId) params.push('filter_location_id=' + locationId);
        if (modeId) params.push('filter_im_ex_lo_id=' + modeId);
        
        var queryString = params.length > 0 ? '?' + params.join('&') : '';
        
        // Redirect to export
        window.location.href = '<?php echo APP_URL; ?>perdiem/exportToExcel' + queryString;
    });

    // ==================== INSERT FORM MULTISELECT ====================
    function updateSelectedText() {
        const checkedBoxes = $('.client-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            $('#selectedClientsText').html('Select clients...');
        } else if (count === 1) {
            const label = checkedBoxes.closest('label').text().trim();
            $('#selectedClientsText').html(label);
        } else {
            $('#selectedClientsText').html('<span class="selected-count">' + count + ' clients selected</span>');
        }
    }

    $('.client-checkbox').on('change', function() {
        updateSelectedText();
        const totalCheckboxes = $('.client-checkbox').length;
        const checkedCheckboxes = $('.client-checkbox:checked').length;
        $('#selectAllClients').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    $('#selectAllClients').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.client-checkbox').prop('checked', isChecked);
        updateSelectedText();
    });

    $('#clientDropdownMenu').on('click', function(e) {
        e.stopPropagation();
    });

    $('#resetFormBtn').on('click', function() {
        $('.client-checkbox').prop('checked', false);
        $('#selectAllClients').prop('checked', false);
        updateSelectedText();
    });

    // ==================== EDIT FORM MULTISELECT ====================
    function updateEditSelectedText() {
        const checkedBoxes = $('.edit-client-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count === 0) {
            $('#editSelectedClientsText').html('Select clients...');
        } else if (count === 1) {
            const label = checkedBoxes.closest('label').text().trim();
            $('#editSelectedClientsText').html(label);
        } else {
            $('#editSelectedClientsText').html('<span class="selected-count">' + count + ' clients selected</span>');
        }
    }

    $('.edit-client-checkbox').on('change', function() {
        updateEditSelectedText();
        const totalCheckboxes = $('.edit-client-checkbox').length;
        const checkedCheckboxes = $('.edit-client-checkbox:checked').length;
        $('#editSelectAllClients').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    $('#editSelectAllClients').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.edit-client-checkbox').prop('checked', isChecked);
        updateEditSelectedText();
    });

    $('#editClientDropdownMenu').on('click', function(e) {
        e.stopPropagation();
    });

    // ==================== INSERT FORM SUBMIT ====================
    $('#perdiemInsertForm').submit(function (e) {
        e.preventDefault();
        
        var selectedClients = $('.client-checkbox:checked').length;
        if (selectedClients === 0) {
            alert('❌ Please select at least one client');
            return;
        }

        var confirmMsg = 'You are about to create per diem records for ' + selectedClients + ' client(s). Continue?';
        if (!confirm(confirmMsg)) {
            return;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/crudData/insertion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });

    // ==================== EDIT BUTTON CLICK ====================
    $(document).on('click', '.editPerdiemBtn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/getPerdiemById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    // Clear all checkboxes first
                    $('.edit-client-checkbox').prop('checked', false);
                    
                    // Check only the current client
                    $('.edit-client-checkbox[value="' + res.data.client_id + '"]').prop('checked', true);
                    
                    $('#edit_transport_mode_id').val(res.data.transport_mode_id);
                    $('#edit_goods_type_id').val(res.data.goods_type_id);
                    $('#edit_location_id').val(res.data.location_id);
                    $('#edit_im_ex_lo_id').val(res.data.im_ex_lo_id);
                    $('#edit_perdiem_amount').val(res.data.perdiem_amount);
                    $('#edit_display').val(res.data.display);
                    
                    updateEditSelectedText();
                    $('#perdiemEditModal').data('id', id).modal('show');
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function () {
                alert('Error fetching per diem data.');
            }
        });
    });

    // ==================== UPDATE FORM SUBMIT ====================
    $('#perdiemUpdateForm').on('submit', function (e) {
        e.preventDefault();
        var id = $('#perdiemEditModal').data('id');
        
        var selectedClients = $('.edit-client-checkbox:checked').length;
        if (selectedClients === 0) {
            alert('❌ Please select at least one client');
            return;
        }

        var confirmMsg = 'You are about to update per diem records for ' + selectedClients + ' client(s). Continue?';
        if (!confirm(confirmMsg)) {
            return;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/crudData/updation?id=' + id,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert('✅ ' + res.message);
                    $('#perdiemEditModal').modal('hide');
                    location.reload();
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function (xhr) {
                alert('AJAX Error: ' + xhr.responseText);
            }
        });
    });

    // ==================== COPY BUTTON CLICK ====================
    $(document).on('click', '.copyPerdiemBtn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/getPerdiemById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#copy_client_id').val(res.data.client_id);
                    $('#copy_transport_mode_id').val(res.data.transport_mode_id);
                    $('#copy_goods_type_id').val(res.data.goods_type_id);
                    $('#copy_location_id').val(res.data.location_id);
                    $('#copy_im_ex_lo_id').val(res.data.im_ex_lo_id);
                    $('#copy_perdiem_amount').val(res.data.perdiem_amount);
                    $('#copy_display').val(res.data.display);
                    
                    $('#perdiemCopyModal').modal('show');
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function () {
                alert('Error fetching per diem data.');
            }
        });
    });

    // ==================== COPY FORM SUBMIT ====================
    $('#perdiemCopyForm').on('submit', function (e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to create a copy of this per diem record?')) {
            return;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/crudData/insertion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert('✅ ' + res.message);
                    $('#perdiemCopyModal').modal('hide');
                    location.reload();
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function (xhr) {
                alert('AJAX Error: ' + xhr.responseText);
            }
        });
    });

    // ==================== DELETE BUTTON CLICK ====================
    $(document).on('click', '.deletePerdiemBtn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        if (!confirm('⚠️ Are you sure you want to delete this per diem record?')) {
            return;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>perdiem/crudData/deletion?id=' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert('✅ ' + res.message);
                    location.reload();
                } else {
                    alert('❌ ' + res.message);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    });
});
</script>