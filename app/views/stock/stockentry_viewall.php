<div class="page-content">
    <div class="page-container">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">View Stock Entries</h4>
                            <p class="text-muted mb-0">Total: <?= $total; ?> entries</p>
                        </div>
                        <div>
                            <a href="<?= APP_URL; ?>stock/stockentry" class="btn btn-sm btn-primary me-2">
                                <i class="mdi mdi-plus-circle"></i> New Entry
                            </a>
                            <a href="<?= APP_URL; ?>stock/stock/summary" class="btn btn-sm btn-info">
                                <i class="mdi mdi-chart-line"></i> Summary
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label"><strong>Entry Type</strong></label>
                                <select id="filterType" class="form-select">
                                    <option value="ALL">All Types</option>
                                    <option value="INDENT_BASED" <?= $entryType === 'INDENT_BASED' ? 'selected' : ''; ?>>Indent-Based</option>
                                    <option value="TRANSFER" <?= $entryType === 'TRANSFER' ? 'selected' : ''; ?>>Transfer</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label"><strong>Verification Status</strong></label>
                                <select id="filterStatus" class="form-select">
                                    <option value="ALL">All Status</option>
                                    <option value="PENDING" <?= $status === 'PENDING' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="VERIFIED" <?= $status === 'VERIFIED' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="REJECTED" <?= $status === 'REJECTED' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" onclick="applyFilters()">
                                    <i class="mdi mdi-filter"></i> Apply Filters
                                </button>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-secondary w-100" onclick="resetFilters()">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive-horizontal">
                        <table id="stock-datatable" class="table table-striped w-100" >
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Entry Type</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Transaction Date</th>
                                    <th>Status</th>
                                    <th>Verified By</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($entries)): ?>
                                    <?php 
                                        $i=0;
                                        foreach ($entries as $entry): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <strong><?= ++$i; ?></strong>
                                            </td>
                                            <td class="align-middle">
                                                <?php if ($entry['stock_entry_type'] === 'INDENT_BASED'): ?>
                                                    <span class="badge bg-primary">Indent-Based</span>
                                                    <span class="badge bg-warning"><?= htmlspecialchars($entry['indent_no'] ?? 'N/A'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Transfer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <div>
                                                    <span class="badge bg-secondary mb-1">
                                                        <?= htmlspecialchars($entry['group_name'] ?? 'Ungrouped'); ?>
                                                    </span>

                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($entry['item_name'] ?? 'N/A'); ?>
                                                    </div>

                                                    <small class="text-primary">
                                                        <?= htmlspecialchars(($entry['make_name'] ?? '') . ' ' . ($entry['model_name'] ?? '')); ?>
                                                    </small>

                                                    <div class="text-muted small">
                                                        <?= nl2br(htmlspecialchars($entry['item_description'] ?? '')); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php 
                                                    $quantity = ($entry['transaction_type']=='ISSUE') ? $entry['issue_qty']:$entry['receipt_qty'];
                                                    $qty = $entry['receipt_qty'] ?? $entry['issue_qty'] ?? 0;
                                                    echo $quantity;
                                                ?>
                                            </td>
                                            <td class="align-middle">
                                                <small><?= date('d-m-Y', strtotime($entry['transaction_date'])); ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <?php 
                                                    $statusClass = match($entry['verification_status']) {
                                                        'PENDING' => 'warning',
                                                        'VERIFIED' => 'success',
                                                        'REJECTED' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>
                                                <span class="badge bg-<?= $statusClass; ?>">
                                                    <?= $entry['verification_status']; ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <small><?= htmlspecialchars($entry['verified_by_name'] ?? '-'); ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <small><?= htmlspecialchars($entry['created_by_name'] ?? '-'); ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= APP_URL; ?>stock/viewSingle?id=<?= $entry['id']; ?>" 
                                                       class="btn btn-outline-info" title="View">
                                                        <i class="ti ti-eye"></i>
                                                    </a>

                                                    <!-- ISSUE BUTTON (NEW) -->
                                                    <?php if ($entry['verification_status'] === 'VERIFIED' && $entry['transaction_type'] === 'RECEIPT'): ?>
                                                        <button type="button" class="btn btn-outline-success issueBtn" 
                                                                onclick="showIssueModal(<?= $entry['id']; ?>, '<?= htmlspecialchars($entry['item_name']); ?>', <?= $entry['balance_qty'] ?? 0; ?>)" 
                                                                title="Issue Item">
                                                            <i class="ti ti-send"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($entry['verification_status'] === 'PENDING'): ?>
                                                        <a href="<?= APP_URL; ?>stock/edit?id=<?= $entry['id']; ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>

                                                        <button type="button" class="btn btn-outline-success" 
                                                                onclick="verifyEntry(<?= $entry['id']; ?>)" 
                                                                title="Verify">
                                                            <i class="ti ti-check"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="showRejectModal(<?= $entry['id']; ?>)" 
                                                                title="Reject">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($entry['verification_status'] === 'PENDING'): ?>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteEntry(<?= $entry['id']; ?>)" 
                                                                title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">No entries found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL; ?>stock/stock/viewAll?page=1&type=<?= $entryType; ?>&status=<?= $status; ?>">
                                        First
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL; ?>stock/stock/viewAll?page=<?= $currentPage - 1; ?>&type=<?= $entryType; ?>&status=<?= $status; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php 
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            for ($i = $start; $i <= $end; $i++): 
                            ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= APP_URL; ?>stock/stock/viewAll?page=<?= $i; ?>&type=<?= $entryType; ?>&status=<?= $status; ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL; ?>stock/stock/viewAll?page=<?= $currentPage + 1; ?>&type=<?= $entryType; ?>&status=<?= $status; ?>">
                                        Next
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= APP_URL; ?>stock/stock/viewAll?page=<?= $totalPages; ?>&type=<?= $entryType; ?>&status=<?= $status; ?>">
                                        Last
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="mdi mdi-alert"></i> Are you sure you want to reject this entry?
                </div>
                <div>
                    <label for="rejectReason" class="form-label"><strong>Reason for Rejection</strong></label>
                    <textarea id="rejectReason" class="form-control" rows="3" placeholder="Please provide reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- ISSUE MODAL (NEW) -->
<div class="modal fade" id="issueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title">
                    <i class="mdi mdi-send text-success me-2"></i> Issue Item
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Item Information (Read-only) -->
                <div class="alert alert-info mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Item Name:</strong> <span id="issueItemName">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Available Qty:</strong> <span id="issueAvailableQty" class="badge bg-primary">0</span>
                        </div>
                    </div>
                </div>

                <!-- Issue Form -->
                <form id="issueForm">
                    <input type="hidden" id="issueTransactionId" name="transaction_id" value="">

                    <!-- Quantity Issued -->
                    <div class="mb-3">
                        <label for="issueQuantity" class="form-label"><strong>Quantity to Issue <span class="text-danger">*</span></strong></label>
                        <input type="number" id="issueQuantity" class="form-control" min="1" required 
                               placeholder="Enter quantity">
                        <small class="form-text text-muted">Must not exceed available quantity</small>
                    </div>

                    <!-- Issued To Location -->
                    <div class="mb-3">
                        <label for="issueLocation" class="form-label"><strong>Issued To Location <span class="text-danger">*</span></strong></label>
                        <select id="issueLocation" class="form-select" required>
                            <option value="">-- Select Location --</option>
                            <?php if (!empty($locations)): ?>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id']; ?>">
                                        <?= htmlspecialchars($location['location_name']); ?>
                                        <?php if (!empty($location['contact_person'])): ?>
                                            (<?= htmlspecialchars($location['contact_person']); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Issue Date -->
                    <div class="mb-3">
                        <label for="issueDate" class="form-label"><strong>Issue Date <span class="text-danger">*</span></strong></label>
                        <input type="date" id="issueDate" class="form-control" required 
                               value="<?= date('Y-m-d'); ?>">
                    </div>

                    <!-- Serial Number -->
                    <div class="mb-3">
                        <label for="issueSerialNo" class="form-label"><strong>Serial Number</strong></label>
                        <input type="text" id="issueSerialNo" class="form-control" 
                               placeholder="Enter serial number (if applicable)">
                        <small class="form-text text-muted">Optional - only if item has a serial number</small>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-3">
                        <label for="issueRemarks" class="form-label"><strong>Remarks</strong></label>
                        <textarea id="issueRemarks" class="form-control" rows="3" 
                                  placeholder="Any additional remarks..."></textarea>
                    </div>

                    <!-- Location Details Preview -->
                    <div id="locationDetails" class="alert alert-light" style="display: none;">
                        <h6 class="alert-heading">Selected Location Details</h6>
                        <div id="locationContent"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmIssue()">
                    <i class="mdi mdi-check me-1"></i> Issue Item
                </button>
            </div>
        </div>
    </div>
</div>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
<script>
    $(document).ready(function () {
        // DataTable with horizontal scroll
        $('#stock-datatable').DataTable({
            scrollX: true,
            scrollCollapse: true,
            responsive: false,
            paging: false,
            info: false,
            searching: false,
            columnDefs: [
                { width: "40px", targets: 0 },      // #
                { width: "120px", targets: 1 },     // Entry Type
                { width: "280px", targets: 2 },     // Item
                { width: "80px", targets: 3 },      // Quantity
                { width: "110px", targets: 4 },     // Transaction Date
                { width: "90px", targets: 5 },      // Status
                { width: "120px", targets: 6 },     // Verified By
                { width: "120px", targets: 7 },     // Created By
                { width: "200px", targets: 8 }      // Actions
            ]
        });
    });

let rejectEntryId = null;
let issueTransactionId = null;
let issueMaxQuantity = 0;
const locations = <?= isset($locations) ? json_encode($locations) : '[]'; ?>;

// ════════════════════════════════════════════════════════════════════
// FILTER FUNCTIONS
// ════════════════════════════════════════════════════════════════════

function applyFilters() {
    const type = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    window.location.href = '<?= APP_URL; ?>stock/stock/viewAll?type=' + type + '&status=' + status;
}

function resetFilters() {
    window.location.href = '<?= APP_URL; ?>stock/stock/viewAll';
}

// ════════════════════════════════════════════════════════════════════
// VERIFY & REJECT FUNCTIONS
// ════════════════════════════════════════════════════════════════════

function verifyEntry(entryId) {
    if (confirm('Verify this entry?')) {
        fetch('<?= APP_URL; ?>stock/verifyEntry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'entry_id=' + entryId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success', 'Entry verified successfully', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function showRejectModal(entryId) {
    rejectEntryId = entryId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmReject() {
    const reason = document.getElementById('rejectReason').value;
    
    if (!reason) {
        Swal.fire('Warning', 'Please provide a reason for rejection', 'warning');
        return;
    }

    fetch('<?= APP_URL; ?>stock/rejectEntry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'entry_id=' + rejectEntryId + '&reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Entry rejected successfully', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteEntry(entryId) {
    Swal.fire({
        title: 'Delete Entry?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('<?= APP_URL; ?>stock/deleteEntry', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'entry_id=' + entryId + '&reason=Manual deletion'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Entry deleted successfully', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}

// ════════════════════════════════════════════════════════════════════
// ISSUE FUNCTIONS (NEW)
// ════════════════════════════════════════════════════════════════════

function showIssueModal(transactionId, itemName, availableQty) {
    issueTransactionId = transactionId;
    issueMaxQuantity = availableQty;

    // Set modal data
    document.getElementById('issueTransactionId').value = transactionId;
    document.getElementById('issueItemName').textContent = itemName;
    document.getElementById('issueAvailableQty').textContent = availableQty;
    document.getElementById('issueQuantity').value = '';
    document.getElementById('issueQuantity').max = availableQty;
    document.getElementById('issueLocation').value = '';
    document.getElementById('issueDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('issueSerialNo').value = '';
    document.getElementById('issueRemarks').value = '';
    document.getElementById('locationDetails').style.display = 'none';

    // Show modal
    new bootstrap.Modal(document.getElementById('issueModal')).show();
}

// Update location details preview
document.getElementById('issueLocation')?.addEventListener('change', function() {
    if (!this.value) {
        document.getElementById('locationDetails').style.display = 'none';
        return;
    }

    const location = locations.find(l => l.id == this.value);
    if (location) {
        let html = '<dl class="row">';
        html += '<dt class="col-sm-4">Location Code:</dt>';
        html += '<dd class="col-sm-8">' + location.location_code + '</dd>';
        html += '<dt class="col-sm-4">Contact Person:</dt>';
        html += '<dd class="col-sm-8">' + (location.contact_person || '-') + '</dd>';
        html += '<dt class="col-sm-4">Phone:</dt>';
        html += '<dd class="col-sm-8">' + (location.phone || '-') + '</dd>';
        html += '<dt class="col-sm-4">Description:</dt>';
        html += '<dd class="col-sm-8">' + (location.description || '-') + '</dd>';
        html += '</dl>';
        
        document.getElementById('locationContent').innerHTML = html;
        document.getElementById('locationDetails').style.display = 'block';
    }
});

// Validate quantity on input
document.getElementById('issueQuantity')?.addEventListener('input', function() {
    const qty = parseInt(this.value) || 0;
    if (qty > issueMaxQuantity) {
        this.classList.add('is-invalid');
        this.nextElementSibling.textContent = 'Quantity cannot exceed ' + issueMaxQuantity;
    } else {
        this.classList.remove('is-invalid');
        this.nextElementSibling.textContent = 'Must not exceed available quantity';
    }
});

function confirmIssue() {
    const qty = parseInt(document.getElementById('issueQuantity').value) || 0;
    const locationId = document.getElementById('issueLocation').value;
    const issueDate = document.getElementById('issueDate').value;
    const serialNo = document.getElementById('issueSerialNo').value;
    const remarks = document.getElementById('issueRemarks').value;

    // Validation
    if (!qty || qty <= 0) {
        Swal.fire('Error', 'Please enter a valid quantity', 'error');
        return;
    }

    if (qty > issueMaxQuantity) {
        Swal.fire('Error', 'Quantity exceeds available stock', 'error');
        return;
    }

    if (!locationId) {
        Swal.fire('Error', 'Please select a location', 'error');
        return;
    }

    if (!issueDate) {
        Swal.fire('Error', 'Please select an issue date', 'error');
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Issuing item...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // Send request
    fetch('<?= APP_URL; ?>stock/issueItem', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'transaction_id=' + issueTransactionId + 
              '&quantity=' + qty + 
              '&location_id=' + locationId + 
              '&issue_date=' + issueDate + 
              '&serial_no=' + encodeURIComponent(serialNo) + 
              '&remarks=' + encodeURIComponent(remarks)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('issueModal')).hide();
            Swal.fire('Success', 'Item issued successfully', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Failed to issue item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'An error occurred', 'error');
    });
}
</script>

<style>
.table-responsive-horizontal {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    min-width: 1200px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    white-space: nowrap;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.modal-body .is-invalid {
    border-color: #dc3545;
}

.alert-info {
    background-color: #e7f3ff;
    border-left: 4px solid #2196F3;
}

.alert-light {
    border-left: 4px solid #dee2e6;
}

/* DataTables Scroll Fix */
.dataTables_wrapper .dataTables_scroll {
    clear: both;
}

.dataTables_wrapper .dataTables_scrollBody {
    position: relative;
    overflow: auto;
    max-width: 100%;
}

/* Column alignment */
th, td {
    text-align: left;
}

th:nth-child(1), td:nth-child(1),
th:nth-child(4), td:nth-child(4) {
    text-align: center;
}
</style>