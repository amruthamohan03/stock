<div class="page-content">
    <div class="page-container">

        <!-- Edit Entry Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">Edit Stock Entry #<?= $entry['id']; ?></h4>
                            <p class="text-muted mb-0">Modify pending entry details</p>
                        </div>
                        <a href="<?= APP_URL; ?>stock/viewSingle?id=<?= $entry['id']; ?>" class="btn btn-sm btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="card-body">
                        <form id="editEntryForm">
                            <input type="hidden" name="entry_id" value="<?= $entry['id']; ?>">

                            <!-- Item Information (Read-only) -->
                            <div class="alert alert-info mb-4">
                                <i class="mdi mdi-information"></i> <strong>Item Information</strong>
                                <p class="mb-0 mt-2">
                                    Item: <strong><?= htmlspecialchars($entry['item_name']); ?></strong> | 
                                    Location: <strong><?= htmlspecialchars($entry['location']); ?></strong>
                                </p>
                            </div>

                            <!-- Row 1: Quantity -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="quantity" class="form-label">
                                        <strong>Quantity</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" id="quantity" name="quantity" class="form-control" 
                                           value="<?= $entry['receipt_qty'] ?? $entry['issue_qty'] ?? 0; ?>" 
                                           min="1" required>
                                    <small class="text-muted d-block mt-1">Current value</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="itemStatus" class="form-label">
                                        <strong>Item Status</strong>
                                    </label>
                                    <select id="itemStatus" name="item_status" class="form-select">
                                        <option value="WORKING" <?= ($entry['item_status'] ?? 'WORKING') === 'WORKING' ? 'selected' : ''; ?>>Working</option>
                                        <option value="NOT WORKING" <?= ($entry['item_status'] ?? '') === 'NOT WORKING' ? 'selected' : ''; ?>>Not Working</option>                                        
                                        <option value="DELETED" <?= ($entry['item_status'] ?? '') === 'DELETED' ? 'selected' : ''; ?>>Entry Deleted From Stock Book</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Remarks -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Add any notes...">
<?= htmlspecialchars($entry['remarks'] ?? ''); ?>
                                    </textarea>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save"></i> Save Changes
                                    </button>
                                    <a href="<?= APP_URL; ?>stock/stock/view?id=<?= $entry['id']; ?>" class="btn btn-secondary ms-2">
                                        <i class="mdi mdi-close"></i> Cancel
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Change History</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-center py-3">
                            <i class="mdi mdi-history"></i> Entry changes will be tracked here
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Success</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="window.location.href='<?= APP_URL; ?>stock/stock/view?id=<?= $entry['id']; ?>'">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('<?= APP_URL; ?>stock/updateEntry', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('successMessage').textContent = data.message;
            new bootstrap.Modal(document.getElementById('successModal')).show();

        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<!-- ==================================================================================
     SUMMARY REPORT VIEW - stockentry_summary.php
     ================================================================================== -->
<style>
    .stat-card {
        border-left: 4px solid #007bff;
    }
</style>

<!-- REPLACE WITH SUMMARY VIEW CONTENT BELOW -->

<?php 
// If this is a summary page request
if (isset($_GET['view']) && $_GET['view'] === 'summary'):
?>

<div class="page-content">
    <div class="page-container">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">Stock Entry Summary Report</h4>
                            <p class="text-muted mb-0">Overview of all stock entries and transactions</p>
                        </div>
                        <a href="<?= APP_URL; ?>stock/stockentry" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus-circle"></i> New Entry
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row 1 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Transactions</h6>
                        <h2 class="mb-0 text-primary"><?= $summary['total_transactions'] ?? 0; ?></h2>
                        <small class="text-muted">All entries combined</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #0dcaf0;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Indent-Based Entries</h6>
                        <h2 class="mb-0 text-info"><?= $summary['indent_entries'] ?? 0; ?></h2>
                        <small class="text-muted">From purchase indents</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #198754;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Transfer Entries</h6>
                        <h2 class="mb-0 text-success"><?= $summary['transfer_entries'] ?? 0; ?></h2>
                        <small class="text-muted">Between locations</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Batches</h6>
                        <h2 class="mb-0 text-warning"><?= $batchSummary['total_batches'] ?? 0; ?></h2>
                        <small class="text-muted">Grouped entries</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row 2 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Verification</h6>
                        <h2 class="mb-0 text-danger"><?= $summary['pending_entries'] ?? 0; ?></h2>
                        <small class="text-muted">Awaiting approval</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #198754;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Verified Entries</h6>
                        <h2 class="mb-0 text-success"><?= $summary['verified_entries'] ?? 0; ?></h2>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #fd7e14;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Rejected Entries</h6>
                        <h2 class="mb-0 text-warning"><?= $summary['rejected_entries'] ?? 0; ?></h2>
                        <small class="text-muted">Declined</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card" style="border-left-color: #6f42c1;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Quantity</h6>
                        <h2 class="mb-0 text-purple">
                            <?= ($summary['total_received'] ?? 0) + ($summary['total_issued'] ?? 0); ?>
                        </h2>
                        <small class="text-muted">Items moved</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Receipt vs Issue</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <h6 class="text-muted mb-2">Total Received</h6>
                                <h3 class="text-success"><?= $summary['total_received'] ?? 0; ?></h3>
                            </div>
                            <div class="col-6 text-center">
                                <h6 class="text-muted mb-2">Total Issued</h6>
                                <h3 class="text-danger"><?= $summary['total_issued'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Batch Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <h6 class="text-muted mb-2">Submitted Batches</h6>
                                <h3 class="text-info"><?= $batchSummary['submitted'] ?? 0; ?></h3>
                            </div>
                            <div class="col-6 text-center">
                                <h6 class="text-muted mb-2">Verified Batches</h6>
                                <h3 class="text-success"><?= $batchSummary['verified'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= APP_URL; ?>stock/viewAll?status=PENDING" class="btn btn-warning me-2">
                            <i class="mdi mdi-clock-outline"></i> View Pending Entries
                        </a>
                        <a href="<?= APP_URL; ?>stock/viewAll?type=INDENT_BASED" class="btn btn-primary me-2">
                            <i class="mdi mdi-checkbox-marked-circle"></i> Indent-Based Only
                        </a>
                        <a href="<?= APP_URL; ?>stock/viewAll?type=TRANSFER" class="btn btn-success me-2">
                            <i class="mdi mdi-transfer"></i> Transfer Only
                        </a>
                        <a href="<?= APP_URL; ?>stock/viewByGroup" class="btn btn-info">
                            <i class="mdi mdi-folder-multiple"></i> View by Group
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<?php endif; ?>
