<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Day Book Entry Details</h4>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                            <a href="<?= APP_URL ?>daybook" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body" id="printArea">

                        <!-- ── KFC Form 16 header ──────────────── -->
                        <div class="text-center mb-3">
                            <h6 class="mb-0">K.F.C. FORM 16</h6>
                            <small class="text-muted">[See Chapter VII, Article 161 (a)]</small>
                            <h5 class="mt-1 mb-0"><strong>DAY BOOK OF STORES</strong></h5>
                        </div>

                        <!-- ── Meta info ──────────────────────── -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width:160px;">Stockbook</td>
                                        <td>: <?= htmlspecialchars($master['stockbook_name'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Class</td>
                                        <td>: <?= htmlspecialchars($master['class'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Unit</td>
                                        <td>: <?= htmlspecialchars($master['unit_label'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Transaction</td>
                                        <td>:
                                            <?php $tColor = ($master['transaction_type'] == 'RECEIPT') ? 'success' : 'danger'; ?>
                                            <span class="badge bg-<?= $tColor ?>">
                                                <?= $master['transaction_type'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width:160px;">Page No</td>
                                        <td>: <?= htmlspecialchars($master['page_no']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date</td>
                                        <td>: <?= date('d-m-Y', strtotime($master['document_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Order / Ref No</td>
                                        <td>: <?= htmlspecialchars($master['receipt_order_no'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Invoice Ref</td>
                                        <td>: <?= htmlspecialchars($master['invoice_ref'] ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- ── Provider / Indent info ─────────── -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <strong>By Whom Received / To Whom Issued:</strong><br>
                                    <?= htmlspecialchars($master['provider_name'] ?? 'N/A') ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($master['indent_no'])): ?>
                                    <p class="mb-1">
                                        <strong>Indent No:</strong> <?= htmlspecialchars($master['indent_no']) ?>
                                        <?php if (!empty($master['indent_date'])): ?>
                                            &nbsp;|&nbsp; <strong>Date:</strong>
                                            <?= date('d-m-Y', strtotime($master['indent_date'])) ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($master['cr_voucher_ref'])): ?>
                                    <p class="mb-1">
                                        <strong>CR Voucher Ref:</strong>
                                        <?= htmlspecialchars($master['cr_voucher_ref']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($master['remarks'])): ?>
                                    <p class="mb-1">
                                        <strong>Remarks:</strong>
                                        <?= htmlspecialchars($master['remarks']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ── Items Table ────────────────────── -->
                        <div class="table-responsive mb-4" style="overflow-x:auto;">
                            <table class="table table-bordered table-sm align-middle"
                                   style="min-width:1600px;">
                                <thead class="table-dark">
                                    <tr>
                                        <th rowspan="3" style="width:42px;text-align:center;">Sl</th>
                                        <th rowspan="3" style="min-width:180px;">Item Name</th>
                                        <th rowspan="3" style="min-width:120px;">Type</th>
                                        <th rowspan="3" style="min-width:120px;">Category</th>
                                        <th rowspan="3" style="min-width:70px;text-align:center;">Unit</th>
                                        <th colspan="6" class="text-center">Quantities</th>
                                        <th colspan="8" class="text-center">Value (Rs.)</th>
                                        <th rowspan="3" style="min-width:70px;text-align:center;">Verifier</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="text-center table-secondary">Receipts</th>
                                        <th colspan="2" class="text-center table-secondary">Issues</th>
                                        <th colspan="2" class="text-center table-secondary">Balance</th>
                                        <th colspan="3" class="text-center table-secondary">Receipts</th>
                                        <th colspan="2" class="text-center table-secondary">Issues</th>
                                        <th colspan="3" class="text-center table-secondary">Balance</th>
                                    </tr>
                                    <tr class="table-light" style="font-size:11px;">
                                        <th style="min-width:90px;">No.</th>
                                        <th style="min-width:90px;">Wt/Msr</th>
                                        <th style="min-width:90px;">No.</th>
                                        <th style="min-width:90px;">Wt/Msr</th>
                                        <th style="min-width:90px;">No.</th>
                                        <th style="min-width:90px;">Wt/Msr</th>
                                        <th style="min-width:100px;">Rate</th>
                                        <th style="min-width:110px;">Rs.</th>
                                        <th style="min-width:70px;">P.</th>
                                        <th style="min-width:100px;">Rate</th>
                                        <th style="min-width:110px;">Rs.</th>
                                        <th style="min-width:100px;">Rate</th>
                                        <th style="min-width:110px;">Rs.</th>
                                        <th style="min-width:70px;">P.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php
                                        $totRqn = $totIqn = $totBqn = 0;
                                        $totRqw = $totIqw = $totBqw = 0;
                                        $totRamt = $totIamt = $totBamt = 0;
                                        ?>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td class="text-center"><?= $item['sl_no'] ?></td>
                                                <td><?= htmlspecialchars($item['item_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($item['item_type_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['item_category_name'] ?? '-') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                                                <!-- Quantities: Receipts -->
                                                <td class="text-end"><?= $item['receipt_qty_number'] ?: '-' ?></td>
                                                <td class="text-end"><?= $item['receipt_qty_weight'] ?: '-' ?></td>
                                                <!-- Quantities: Issues -->
                                                <td class="text-end"><?= $item['issue_qty_number'] ?: '-' ?></td>
                                                <td class="text-end"><?= $item['issue_qty_weight'] ?: '-' ?></td>
                                                <!-- Quantities: Balance -->
                                                <td class="text-end fw-bold"><?= $item['balance_qty_number'] ?: '-' ?></td>
                                                <td class="text-end fw-bold"><?= $item['balance_qty_weight'] ?: '-' ?></td>
                                                <!-- Value: Receipts -->
                                                <td class="text-end"><?= number_format($item['receipt_rate'], 2) ?></td>
                                                <td class="text-end"><?= number_format($item['receipt_amount_rs'], 2) ?></td>
                                                <td class="text-end"><?= $item['receipt_amount_ps'] ?: '-' ?></td>
                                                <!-- Value: Issues -->
                                                <td class="text-end"><?= number_format($item['issue_rate'], 2) ?></td>
                                                <td class="text-end"><?= number_format($item['issue_amount_rs'], 2) ?></td>
                                                <!-- Value: Balance -->
                                                <td class="text-end"><?= number_format($item['balance_rate'], 2) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($item['balance_amount_rs'], 2) ?></td>
                                                <td class="text-end"><?= $item['balance_amount_ps'] ?: '-' ?></td>
                                                <td class="text-center"><?= htmlspecialchars($item['value_verifier'] ?? '') ?></td>
                                            </tr>
                                            <?php
                                            $totRqn  += (float)$item['receipt_qty_number'];
                                            $totIqn  += (float)$item['issue_qty_number'];
                                            $totBqn  += (float)$item['balance_qty_number'];
                                            $totRqw  += (float)$item['receipt_qty_weight'];
                                            $totIqw  += (float)$item['issue_qty_weight'];
                                            $totBqw  += (float)$item['balance_qty_weight'];
                                            $totRamt += (float)$item['receipt_amount_rs'];
                                            $totIamt += (float)$item['issue_amount_rs'];
                                            $totBamt += (float)$item['balance_amount_rs'];
                                            ?>
                                        <?php endforeach; ?>

                                        <!-- Totals row -->
                                        <tr class="table-warning fw-bold">
                                            <td colspan="5" class="text-end">TOTAL</td>
                                            <td class="text-end"><?= $totRqn ?></td>
                                            <td class="text-end"><?= $totRqw ?></td>
                                            <td class="text-end"><?= $totIqn ?></td>
                                            <td class="text-end"><?= $totIqw ?></td>
                                            <td class="text-end"><?= $totBqn ?></td>
                                            <td class="text-end"><?= $totBqw ?></td>
                                            <td></td>
                                            <td class="text-end"><?= number_format($totRamt, 2) ?></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end"><?= number_format($totIamt, 2) ?></td>
                                            <td></td>
                                            <td class="text-end text-success"><?= number_format($totBamt, 2) ?></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="22" class="text-center text-muted">No item lines found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- ── Verifier / Timestamps ──────────── -->
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <?php if (!empty($master['verifier_initials'])): ?>
                                    <p class="mb-0"><strong>Verifier Initials:</strong>
                                        <?= htmlspecialchars($master['verifier_initials']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-secondary fs-6">Status: <?= $master['status'] ?></span>
                            </div>
                        </div>

                        <!-- ── Timestamps ────────────────────── -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="row text-muted small">
                                <div class="col-6">
                                    <strong>Created:</strong>
                                    <?= date('d-m-Y H:i', strtotime($master['created_at'])) ?>
                                    by <?= htmlspecialchars($master['created_by_name'] ?? 'N/A') ?>
                                </div>
                                <div class="col-6 text-end">
                                    <strong>Last Updated:</strong>
                                    <?= date('d-m-Y H:i', strtotime($master['updated_at'])) ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- /printArea -->
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
    .page-content { margin: 0 !important; padding: 0 !important; }
    .card         { border: none !important; box-shadow: none !important; }
}
</style>