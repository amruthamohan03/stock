<?php
// Stock Entry Details - Clean & Compact Version
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --light: #F3F4F6;
        }

        body {
            background-color: #F9FAFB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #374151;
        }

        .page-container {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header-card {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white;
            border: none;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .header-card h4 {
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0;
        }

        .header-card p {
            font-size: 0.9rem;
            margin: 0.3rem 0 0 0;
            opacity: 0.95;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #F9FAFB;
            padding: 1rem;
        }

        .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Row with two columns */
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .info-row.full {
            grid-template-columns: 1fr;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .field {
            display: flex;
            flex-direction: column;
        }

        .field-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .field-value {
            font-size: 0.95rem;
            color: #111827;
            font-weight: 500;
        }

        .field-value.strong {
            font-weight: 700;
            color: var(--primary);
        }

        /* Badges */
        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 0.4rem;
            display: inline-block;
            width: fit-content;
        }

        .badge-primary {
            background-color: #E0E7FF;
            color: #4F46E5;
        }

        .badge-success {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .badge-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .badge-info {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .badge-secondary {
            background-color: #E5E7EB;
            color: #374151;
        }

        /* Status Colors */
        .status-success {
            color: var(--success);
            font-weight: 600;
        }

        .status-danger {
            color: var(--danger);
            font-weight: 600;
        }

        .status-warning {
            color: var(--warning);
            font-weight: 600;
        }

        /* Main Content Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 992px) {
            .main-grid {
                grid-template-columns: 1fr;
            }

            .info-row {
                grid-template-columns: 1fr;
            }
        }

        /* Status Card */
        .status-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .status-item {
            padding: 1rem;
            background-color: #F9FAFB;
            border-radius: 0.6rem;
        }

        .status-item-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .status-item-value {
            font-size: 0.95rem;
            color: #111827;
            font-weight: 500;
        }

        /* Buttons */
        .btn-verify, .btn-reject {
            width: 100%;
            padding: 0.7rem;
            border: none;
            border-radius: 0.6rem;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-verify {
            background-color: var(--success);
            color: white;
        }

        .btn-verify:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-reject {
            background-color: var(--danger);
            color: white;
        }

        .btn-reject:hover {
            background-color: #DC2626;
            transform: translateY(-1px);
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.4rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #E5E7EB;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
            padding: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.75rem;
            top: 0.3rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--primary);
            border: 2px solid white;
            box-shadow: 0 0 0 2px var(--primary);
        }

        .timeline-time {
            font-size: 0.8rem;
            color: #6B7280;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .timeline-action {
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            background-color: #F3F4F6;
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            margin-bottom: 0.3rem;
        }

        .timeline-content {
            font-size: 0.85rem;
            color: #6B7280;
            margin: 0;
        }

        .timeline-reason {
            font-size: 0.8rem;
            color: var(--danger);
            margin-top: 0.3rem;
        }

        /* Transfer Grid */
        .transfer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .transfer-item {
            padding: 1rem;
            background-color: #F9FAFB;
            border-radius: 0.6rem;
        }

        .transfer-item-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6B7280;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
        }

        .transfer-item-value {
            font-size: 0.95rem;
            color: #111827;
            font-weight: 500;
        }

        /* Remarks */
        .remarks-text {
            background-color: #F9FAFB;
            padding: 0.75rem;
            border-left: 3px solid var(--primary);
            font-size: 0.9rem;
            color: #374151;
            border-radius: 0.4rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #9CA3AF;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Modal */
        .modal-content {
            border: none;
            border-radius: 0.75rem;
        }

        .modal-header {
            background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
            color: white;
            border: none;
        }

        .modal-header .modal-title {
            font-weight: 600;
        }

        .modal-footer {
            padding: 1rem;
        }

        .alert {
            border-radius: 0.6rem;
            border: 1px solid;
        }

        .alert-warning {
            background-color: #FFFBEB;
            border-color: #FEF3C7;
            color: #92400E;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-container {
                padding: 1rem;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .btn-verify, .btn-reject {
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <div class="header-card">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h4>#<?= $entry['id']; ?> - <?= htmlspecialchars($entry['item_name'] ?? 'Stock Entry'); ?></h4>
                    <p><?= $entry['stock_entry_type'] === 'INDENT_BASED' ? 'Indent-Based Entry' : 'Transfer Entry'; ?> • <?= date('d M Y H:i', strtotime($entry['created_at'])); ?></p>
                </div>
                <a href="<?= APP_URL; ?>stock/stock/viewAll" class="btn-back">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="main-grid">
            <!-- Left Column - Entry Details -->
            <div>
                <!-- Entry Information -->
                <div class="card">
                    <div class="card-header">
                        <h5>Entry Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="field">
                                <span class="field-label">Entry Type</span>
                                <span class="badge <?= $entry['stock_entry_type'] === 'INDENT_BASED' ? 'badge-primary' : 'badge-success'; ?>">
                                    <?= $entry['stock_entry_type'] === 'INDENT_BASED' ? 'Indent-Based' : 'Transfer'; ?>
                                </span>
                            </div>
                            <div class="field">
                                <span class="field-label">Transaction Type</span>
                                <span class="badge badge-info"><?= $entry['transaction_type']; ?></span>
                            </div>
                        </div>

                        <div class="info-row full">
                            <div class="field">
                                <span class="field-label">Item Name</span>
                                <span class="field-value strong"><?= htmlspecialchars($entry['item_name'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="field">
                                <span class="field-label">Make</span>
                                <span class="field-value"><?= htmlspecialchars($entry['make'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="field">
                                <span class="field-label">Model</span>
                                <span class="field-value"><?= htmlspecialchars($entry['model'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="info-row full">
                            <div class="field">
                                <span class="field-label">Description</span>
                                <span class="field-value"><?= htmlspecialchars($entry['item_description'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="field">
                                <span class="field-label">Transaction Date</span>
                                <span class="field-value"><?= date('d M Y H:i', strtotime($entry['transaction_date'])); ?></span>
                            </div>
                            <div class="field">
                                <span class="field-label">Item Status</span>
                                <span class="badge badge-secondary"><?= $entry['item_status'] ?? 'WORKING'; ?></span>
                            </div>
                        </div>

                        <?php if ($entry['stock_entry_type'] === 'INDENT_BASED'): ?>
                            <div class="info-row">
                                <div class="field">
                                    <span class="field-label">Indent Number</span>
                                    <span class="field-value"><?= htmlspecialchars($entry['indent_no'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="field">
                                    <span class="field-label">Received From</span>
                                    <span class="field-value"><?= htmlspecialchars($entry['received_from'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="info-row">
                            <div class="field">
                                <span class="field-label">Receipt/Issue Quantity</span>transaction_type
                                <?= $quantity = ($entry['transaction_type']=='ISSUE') ? $entry['issue_qty']:$entry['receipt_qty']; ?>
                                <span class="field-value strong"><?= $quantity ?? '0'; ?> Units</span>
                            </div>
                            <div class="field">
                                <span class="field-label">Current Balance</span>
                                <span class="field-value <?= $entry['balance_qty'] > 0 ? 'status-success' : 'status-danger'; ?>">
                                    <?= $entry['balance_qty'] ?? '0'; ?> Units
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($entry['remarks'])): ?>
                            <div class="info-row full">
                                <div class="field">
                                    <span class="field-label">Remarks</span>
                                    <p class="remarks-text"><?= htmlspecialchars($entry['remarks']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Transfer Details -->
                <?php if ($entry['stock_entry_type'] === 'TRANSFER' && !empty($transferDetail)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="transfer-grid">
                                <div class="transfer-item">
                                    <div class="transfer-item-label">Source Location</div>
                                    <div class="transfer-item-value"><?= $transferDetail[0]['source_location']; ?></div>
                                </div>
                                <div class="transfer-item">
                                    <div class="transfer-item-label">Destination</div>
                                    <div class="transfer-item-value"><?= $transferDetail[0]['destination_location']; ?></div>
                                </div>
                                <div class="transfer-item">
                                    <div class="transfer-item-label">Quantity</div>
                                    <div class="transfer-item-value status-success"><?= $transferDetail[0]['quantity_transferred']; ?> Units</div>
                                </div>
                                <div class="transfer-item">
                                    <div class="transfer-item-label">Condition</div>
                                    <div class="transfer-item-value"><?= $transferDetail[0]['condition_status']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Activity Log -->
                <div class="card">
                    <div class="card-header">
                        <h5>Activity Timeline</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logs)): ?>
                            <div class="timeline">
                                <?php foreach ($logs as $log): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-time"><?= date('d M Y H:i', strtotime($log['created_at'])); ?></div>
                                        <div class="timeline-action"><?= $log['action']; ?></div>
                                        <p class="timeline-content">
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
                                            <div class="timeline-reason">Reason: <?= htmlspecialchars($log['action_reason']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="mdi mdi-history"></i>
                                <p>No activity log available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Status & Classification -->
            <div>
                <!-- Verification Status -->
                <div class="card">
                    <div class="card-header">
                        <h5>Verification Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="status-section">
                            <div class="status-item">
                                <div class="status-item-label">Current Status</div>
                                <div class="status-item-value">
                                    <span class="badge <?php 
                                        echo match($entry['verification_status']) {
                                            'PENDING' => 'badge-warning',
                                            'VERIFIED' => 'badge-success',
                                            'REJECTED' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                    ?>"><?= $entry['verification_status']; ?></span>
                                </div>
                            </div>

                            <?php if ($entry['verification_status'] !== 'PENDING'): ?>
                                <div class="status-item">
                                    <div class="status-item-label">Verified By</div>
                                    <div class="status-item-value"><?= htmlspecialchars($entry['verified_by_name'] ?? 'N/A'); ?></div>
                                </div>

                                <div class="status-item">
                                    <div class="status-item-label">Verified At</div>
                                    <div class="status-item-value"><?= date('d M Y H:i', strtotime($entry['verified_at'])); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="status-item">
                                <div class="status-item-label">Created By</div>
                                <div class="status-item-value"><?= htmlspecialchars($entry['created_by_name'] ?? 'N/A'); ?></div>
                            </div>

                            <div class="status-item">
                                <div class="status-item-label">Created At</div>
                                <div class="status-item-value"><?= date('d M Y H:i', strtotime($entry['created_at'])); ?></div>
                            </div>

                            <?php if ($entry['verification_status'] === 'PENDING'): ?>
                                <button type="button" class="btn-verify" onclick="verifyEntry(<?= $entry['id']; ?>)">
                                    <i class="mdi mdi-check-circle"></i> Verify
                                </button>
                                <button type="button" class="btn-reject" onclick="showRejectModal(<?= $entry['id']; ?>)">
                                    <i class="mdi mdi-close-circle"></i> Reject
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Classification -->
                <?php if (!empty($entry['item_type'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Classification</h5>
                        </div>
                        <div class="card-body">
                            <div class="status-section">
                                <div class="status-item">
                                    <div class="status-item-label">Item Type</div>
                                    <div class="status-item-value"><?= str_replace('_', ' ', $entry['item_type']); ?></div>
                                </div>

                                <div class="status-item">
                                    <div class="status-item-label">Category</div>
                                    <div class="status-item-value"><?= str_replace('_', ' ', $entry['item_category'] ?? 'N/A'); ?></div>
                                </div>

                                <div class="status-item">
                                    <div class="status-item-label">Book Volume</div>
                                    <div class="status-item-value"><?= $entry['book_volume'] ?? '1'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Confirm Rejection</strong><br>
                        Please provide a reason for rejecting this entry.
                    </div>
                    <label for="rejectReason" class="form-label" style="font-weight: 600;">Reason</label>
                    <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter rejection reason..." style="border-radius: 0.6rem; border: 1px solid #E5E7EB;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
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
            const reason = document.getElementById('rejectReason').value.trim();
            
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
</body>
</html>