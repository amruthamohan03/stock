<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Stock Ledger</h4>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                            <a href="<?= APP_URL; ?>stock" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body" id="printArea">
                        <!-- Ledger Header (Physical Form Style) -->
                        <div class="text-center mb-4">
                            <h5>STOCK BOOK</h5>
                            <p class="mb-1"><strong>Name of Article:</strong> <?= htmlspecialchars($stockBook['item_name']); ?></p>
                            <p class="mb-0"><strong>Location:</strong> <?= htmlspecialchars($stockBook['location']); ?></p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Opening Balance:</strong> <?= $stockBook['opening_balance']; ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Current Balance:</strong> 
                                <span class="badge bg-<?= $stockBook['current_balance'] > 0 ? 'success' : 'danger' ?> fs-6">
                                    <?= $stockBook['current_balance']; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Ledger Table (Like Physical Stock Book) -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">Date</th>
                                        <th style="width: 120px;">No. and date of<br>voucher or invoice</th>
                                        <th style="width: 80px;">Brought<br>Forward</th>
                                        <th style="width: 180px;">From whom received<br>or to whom issued</th>
                                        <th style="width: 70px;">Receipt</th>
                                        <th style="width: 70px;">Issued</th>
                                        <th style="width: 80px;">Balance after<br>each transaction</th>
                                        <th style="width: 100px;">Initial of<br>Receiver</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($transactions)): ?>
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr>
                                                <!-- Date -->
                                                <td><?= date('d.m.Y', strtotime($trans['transaction_date'])); ?></td>
                                                
                                                <!-- Voucher No and Date -->
                                                <td>
                                                    <?php if (!empty($trans['voucher_no'])): ?>
                                                        <?= htmlspecialchars($trans['voucher_no']); ?>
                                                        <?php if (!empty($trans['voucher_date'])): ?>
                                                            <br><small><?= date('d.m.y', strtotime($trans['voucher_date'])); ?></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($trans['indent_no'])): ?>
                                                        <br><small class="text-primary">Indent: <?= $trans['indent_no']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Brought Forward -->
                                                <td class="text-center">
                                                    <?php if ($trans['transaction_type'] == 'BROUGHT_FORWARD'): ?>
                                                        <i class="ti ti-check text-success"></i>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- From/To -->
                                                <td>
                                                    <?php if ($trans['transaction_type'] == 'RECEIPT' || $trans['transaction_type'] == 'BROUGHT_FORWARD'): ?>
                                                        <small><strong>From:</strong> <?= htmlspecialchars($trans['received_from'] ?? '-'); ?></small>
                                                    <?php elseif ($trans['transaction_type'] == 'ISSUE'): ?>
                                                        <small><strong>To:</strong> <?= htmlspecialchars($trans['issued_to'] ?? '-'); ?></small>
                                                    <?php else: ?>
                                                        <small>Adjustment</small>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Receipt -->
                                                <td class="text-center">
                                                    <?php if ($trans['receipt_qty'] > 0): ?>
                                                        <span class="text-success fw-bold"><?= $trans['receipt_qty']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Issued -->
                                                <td class="text-center">
                                                    <?php if ($trans['issue_qty'] > 0): ?>
                                                        <span class="text-danger fw-bold"><?= $trans['issue_qty']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Balance -->
                                                <td class="text-center">
                                                    <strong><?= $trans['balance_qty']; ?></strong>
                                                </td>
                                                
                                                <!-- Receiver Initial -->
                                                <td>
                                                    <small><?= htmlspecialchars($trans['receiver_initial'] ?? ''); ?></small>
                                                </td>
                                                
                                                <!-- Remarks -->
                                                <td>
                                                    <small><?= htmlspecialchars($trans['remarks'] ?? ''); ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Closing Balance:</th>
                                        <th colspan="2"></th>
                                        <th class="text-center">
                                            <span class="badge bg-<?= $stockBook['current_balance'] > 0 ? 'success' : 'danger' ?> fs-6">
                                                <?= $stockBook['current_balance']; ?>
                                            </span>
                                        </th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Total Receipts</h6>
                                        <h3 class="text-success mb-0">
                                            <?php 
                                            $totalReceipts = 0;
                                            if (!empty($transactions)) {
                                                foreach ($transactions as $t) {
                                                    $totalReceipts += $t['receipt_qty'];
                                                }
                                            }
                                            echo $totalReceipts;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Total Issues</h6>
                                        <h3 class="text-danger mb-0">
                                            <?php 
                                            $totalIssues = 0;
                                            if (!empty($transactions)) {
                                                foreach ($transactions as $t) {
                                                    $totalIssues += $t['issue_qty'];
                                                }
                                            }
                                            echo $totalIssues;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">Current Stock</h6>
                                        <h3 class="text-primary mb-0"><?= $stockBook['current_balance']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="row text-muted small">
                                <div class="col-6">
                                    <strong>Created:</strong> <?= date('d-m-Y H:i', strtotime($stockBook['created_at'])); ?>
                                </div>
                                <div class="col-6 text-end">
                                    <strong>Last Updated:</strong> <?= date('d-m-Y H:i', strtotime($stockBook['updated_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<style>
@media print {
    .no-print, .sidebar, .topbar, .card-header .btn, .footer {
        display: none !important;
    }
    .page-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        font-size: 11px;
    }
    .table thead th {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>