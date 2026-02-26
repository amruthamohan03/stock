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
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Entry Type</th>
                                    <th>Item</th>
                                    <th>Make/Model</th>
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
                                    <?php foreach ($entries as $entry): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?= $entry['id']; ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($entry['stock_entry_type'] === 'INDENT_BASED'): ?>
                                                    <span class="badge bg-primary">Indent-Based</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Transfer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($entry['item_name'] ?? 'N/A'); ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($entry['make'] ?? ''); ?>
                                                    <?= !empty($entry['model']) ? ' / ' . htmlspecialchars($entry['model']) : ''; ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    $qty = $entry['receipt_qty'] ?? $entry['issue_qty'] ?? 0;
                                                    echo $qty;
                                                ?>
                                            </td>
                                            <td>
                                                <small><?= date('d-m-Y', strtotime($entry['transaction_date'])); ?></small>
                                            </td>
                                            <td>
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
                                            <td>
                                                <small><?= htmlspecialchars($entry['verified_by_name'] ?? '-'); ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($entry['created_by_name'] ?? '-'); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= APP_URL; ?>stock/stock/view?id=<?= $entry['id']; ?>" 
                                                       class="btn btn-outline-info" title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>

                                                    <?php if ($entry['verification_status'] === 'PENDING'): ?>
                                                        <a href="<?= APP_URL; ?>stock/stock/edit?id=<?= $entry['id']; ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>

                                                        <button type="button" class="btn btn-outline-success" 
                                                                onclick="verifyEntry(<?= $entry['id']; ?>)" 
                                                                title="Verify">
                                                            <i class="mdi mdi-check-circle"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="showRejectModal(<?= $entry['id']; ?>)" 
                                                                title="Reject">
                                                            <i class="mdi mdi-close-circle"></i>
                                                        </button>

                                                        <button type="button" class="btn btn-outline-dark" 
                                                                onclick="deleteEntry(<?= $entry['id']; ?>)" 
                                                                title="Delete">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="mdi mdi-inbox-outline" style="font-size: 2rem;"></i>
                                            <p class="mt-2">No entries found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav>
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
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
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

<script>
let rejectEntryId = null;

function applyFilters() {
    const type = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    window.location.href = '<?= APP_URL; ?>stock/stock/viewAll?type=' + type + '&status=' + status;
}

function resetFilters() {
    window.location.href = '<?= APP_URL; ?>stock/stock/viewAll';
}

function verifyEntry(entryId) {
    if (confirm('Verify this entry?')) {
        fetch('<?= APP_URL; ?>stock/stock/verifyEntry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'entry_id=' + entryId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Entry verified successfully');
                location.reload();
            } else {
                alert('✗ Error: ' + data.message);
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
        alert('Please provide a reason for rejection');
        return;
    }

    fetch('<?= APP_URL; ?>stock/stock/rejectEntry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'entry_id=' + rejectEntryId + '&reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Entry rejected successfully');
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            location.reload();
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteEntry(entryId) {
    if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
        fetch('<?= APP_URL; ?>stock/stock/deleteEntry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'entry_id=' + entryId + '&reason=Manual deletion'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Entry deleted successfully');
                location.reload();
            } else {
                alert('✗ Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

<style>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
