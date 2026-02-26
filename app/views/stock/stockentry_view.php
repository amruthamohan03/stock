<div class="page-content">
    <div class="page-container">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">Stock Entry Details #<?= $entry['id']; ?></h4>
                            <p class="text-muted mb-0">
                                <?= $entry['stock_entry_type'] === 'INDENT_BASED' ? 'Indent-Based Entry' : 'Transfer Entry'; ?> | 
                                Created on <?= date('d-m-Y H:i', strtotime($entry['created_at'])); ?>
                            </p>
                        </div>
                        <div>
                            <a href="<?= APP_URL; ?>stock/stock/viewAll" class="btn btn-sm btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Entry Details -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Entry Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Entry Type</small></label>
                                <p>
                                    <?php if ($entry['stock_entry_type'] === 'INDENT_BASED'): ?>
                                        <span class="badge bg-primary">Indent-Based</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Transfer</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Transaction Type</small></label>
                                <p>
                                    <span class="badge bg-info"><?= $entry['transaction_type']; ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Item Name</small></label>
                                <p><strong><?= htmlspecialchars($entry['item_name'] ?? 'N/A'); ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Make / Model</small></label>
                                <p>
                                    <?= htmlspecialchars($entry['make'] ?? 'N/A'); ?> / 
                                    <?= htmlspecialchars($entry['model'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label text-muted"><small>Description</small></label>
                                <p><?= htmlspecialchars($entry['item_description'] ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Transaction Date</small></label>
                                <p><?= date('d-m-Y H:i', strtotime($entry['transaction_date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Item Status</small></label>
                                <p>
                                    <span class="badge bg-secondary"><?= $entry['item_status'] ?? 'WORKING'; ?></span>
                                </p>
                            </div>
                        </div>

                        <?php if ($entry['stock_entry_type'] === 'INDENT_BASED'): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><small>Indent No</small></label>
                                    <p><?= htmlspecialchars($entry['indent_no'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><small>Received From</small></label>
                                    <p><?= htmlspecialchars($entry['received_from'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Receipt Quantity</small></label>
                                <p><strong><?= $entry['receipt_qty'] ?? '0'; ?></strong></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><small>Current Balance</small></label>
                                <p>
                                    <strong class="text-<?= $entry['balance_qty'] > 0 ? 'success' : 'danger'; ?>">
                                        <?= $entry['balance_qty'] ?? '0'; ?>
                                    </strong>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($entry['remarks'])): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label text-muted"><small>Remarks</small></label>
                                    <p class="bg-light p-2 rounded"><?= htmlspecialchars($entry['remarks']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted"><small>Verification Status</small></label>
                            <p>
                                <?php 
                                    $statusClass = match($entry['verification_status']) {
                                        'PENDING' => 'warning',
                                        'VERIFIED' => 'success',
                                        'REJECTED' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>
                                <span class="badge bg-<?= $statusClass; ?> p-2">
                                    <?= $entry['verification_status']; ?>
                                </span>
                            </p>
                        </div>

                        <?php if ($entry['verification_status'] !== 'PENDING'): ?>
                            <div class="mb-3">
                                <label class="form-label text-muted"><small>Verified By</small></label>
                                <p><?= htmlspecialchars($entry['verified_by_name'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted"><small>Verified At</small></label>
                                <p><?= date('d-m-Y H:i', strtotime($entry['verified_at'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label text-muted"><small>Created By</small></label>
                            <p><?= htmlspecialchars($entry['created_by_name'] ?? 'N/A'); ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted"><small>Created At</small></label>
                            <p><?= date('d-m-Y H:i', strtotime($entry['created_at'])); ?></p>
                        </div>

                        <!-- Action Buttons -->
                        <?php if ($entry['verification_status'] === 'PENDING'): ?>
                            <hr>
                            <button type="button" class="btn btn-sm btn-success w-100 mb-2" onclick="verifyEntry(<?= $entry['id']; ?>)">
                                <i class="mdi mdi-check-circle"></i> Verify
                            </button>
                            <button type="button" class="btn btn-sm btn-danger w-100" onclick="showRejectModal(<?= $entry['id']; ?>)">
                                <i class="mdi mdi-close-circle"></i> Reject
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Item Type Info -->
                <?php if (!empty($entry['item_type'])): ?>
                    <div class="card mt-3">
                        <div class="card-header border-bottom">
                            <h5 class="mb-0">Classification</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label text-muted"><small>Item Type</small></label>
                                <p><?= str_replace('_', ' ', $entry['item_type']); ?></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-muted"><small>Category</small></label>
                                <p><?= str_replace('_', ' ', $entry['item_category'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <label class="form-label text-muted"><small>Book Volume</small></label>
                                <p><?= $entry['book_volume'] ?? '1'; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transfer Details (if applicable) -->
        <?php if ($entry['stock_entry_type'] === 'TRANSFER' && !empty($transferDetail)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h5 class="mb-0">Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label text-muted"><small>Source Location ID</small></label>
                                    <p><?= $transferDetail[0]['source_location_id']; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted"><small>Destination Location ID</small></label>
                                    <p><?= $transferDetail[0]['destination_location_id']; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted"><small>Quantity Transferred</small></label>
                                    <p><strong><?= $transferDetail[0]['quantity_transferred']; ?></strong></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><small>Condition Status</small></label>
                                    <p><?= $transferDetail[0]['condition_status']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Activity Log -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Activity Log</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logs)): ?>
                            <div class="timeline">
                                <?php foreach ($logs as $log): ?>
                                    <div class="timeline-item mb-3">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    <?= date('d-m-Y H:i', strtotime($log['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <span class="badge bg-secondary"><?= $log['action']; ?></span>
                                            </div>
                                            <div class="col-md-7">
                                                <p class="text-muted mb-0">
                                                    <?php if ($log['action'] === 'CREATED'): ?>
                                                        Entry created
                                                    <?php elseif ($log['action'] === 'EDITED'): ?>
                                                        Entry edited
                                                    <?php elseif ($log['action'] === 'VERIFIED'): ?>
                                                        Entry verified
                                                    <?php elseif ($log['action'] === 'REJECTED'): ?>
                                                        Entry rejected
                                                    <?php else: ?>
                                                        <?= $log['action']; ?>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if (!empty($log['action_reason'])): ?>
                                                    <small class="text-danger">Reason: <?= htmlspecialchars($log['action_reason']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No activity log available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

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
let currentEntryId = <?= $entry['id']; ?>;

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
    currentEntryId = entryId;
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
        body: 'entry_id=' + currentEntryId + '&reason=' + encodeURIComponent(reason)
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
</script>

<style>
.timeline {
    border-left: 2px solid #ddd;
    padding-left: 1rem;
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 1rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #007bff;
    border: 2px solid white;
}

.badge {
    font-size: 0.75rem;
}
</style>
