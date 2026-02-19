<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center no-print">
                        <h4 class="header-title mb-0">
                            Day Book Entry —
                            <span class="text-primary"><?= htmlspecialchars($master['provider_name'] ?? '') ?></span>
                            <span class="text-muted fs-6 ms-2"><?= date('d-m-Y', strtotime($master['document_date'])) ?></span>
                        </h4>
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

                        <!-- ── KFC Form 16 Heading ──────────────── -->
                        <div class="text-center mb-3">
                            <div style="font-size:12px;text-transform:uppercase;letter-spacing:1px;">K.F.C. FORM 16</div>
                            <div class="text-muted" style="font-size:11px;">[See Chapter VII, Article 161 (a)]</div>
                            <h5 class="mt-1 mb-0 fw-bold">DAY BOOK OF STORES</h5>
                        </div>

                        <!-- ── Master Info Grid ─────────────────── -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="fw-semibold text-muted" style="width:170px;">Stockbook</td>
                                        <td>: <?= htmlspecialchars($master['stockbook_name'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Page No</td>
                                        <td>:
                                            <span class="badge bg-secondary px-2">
                                                <?= htmlspecialchars($master['page_no']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Class</td>
                                        <td>: <?= htmlspecialchars($master['class'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Unit</td>
                                        <td>: <?= htmlspecialchars($master['unit_label'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Document Date</td>
                                        <td>: <strong><?= date('d-m-Y', strtotime($master['document_date'])) ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="fw-semibold text-muted" style="width:170px;">By Whom / To Whom</td>
                                        <td>: <strong><?= htmlspecialchars($master['provider_name'] ?? '-') ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Issued To</td>
                                        <td>: <?= htmlspecialchars($master['issued_to'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Order / Ref No</td>
                                        <td>: <?= htmlspecialchars($master['receipt_order_no'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Invoice Ref</td>
                                        <td>: <?= htmlspecialchars($master['invoice_ref'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-muted">Invoice Date</td>
                                        <td>:
                                            <?= !empty($master['invoice_date'])
                                                ? date('d-m-Y', strtotime($master['invoice_date']))
                                                : '-' ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- ── Indent / Voucher / Verifier row ─── -->
                        <div class="row mb-3 pb-2 border-bottom">
                            <div class="col-md-3">
                                <span class="text-muted small">Indent No</span><br>
                                <strong><?= htmlspecialchars($master['indent_no'] ?? '-') ?></strong>
                                <?php if (!empty($master['indent_date'])): ?>
                                    <span class="text-muted ms-2 small">
                                        (<?= date('d-m-Y', strtotime($master['indent_date'])) ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">CR Voucher Ref</span><br>
                                <strong><?= htmlspecialchars($master['cr_voucher_ref'] ?? '-') ?></strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">Verifier</span><br>
                                <strong><?= htmlspecialchars($master['verifier_name'] ?? '-') ?></strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">Remarks</span><br>
                                <em><?= htmlspecialchars($master['remarks'] ?? '-') ?></em>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════
                             SECTION 1: RECEIPTS
                        ════════════════════════════════════════ -->
                        <div class="mb-4">
                            <h6 class="text-success fw-bold mb-2">
                                <i class="mdi mdi-arrow-down-circle"></i> RECEIPTS
                                <span class="badge bg-success ms-1"><?= count($receiptItems) ?> item(s)</span>
                            </h6>

                            <?php if (!empty($receiptItems)): ?>
                                <div class="table-responsive" style="overflow-x:auto;">
                                    <table class="table table-bordered table-sm align-middle"
                                           style="min-width:1700px;">
                                        <?php echo '<thead class="table-dark"><tr>
<th rowspan="3" style="width:38px;text-align:center;">Sl</th>
<th rowspan="3" style="min-width:170px;">Item Name</th>
<th rowspan="3" style="min-width:145px;">Description</th>
<th rowspan="3" style="min-width:110px;">Type</th>
<th rowspan="3" style="min-width:110px;">Category</th>
<th rowspan="3" style="min-width:65px;text-align:center;">Unit</th>
<th colspan="6" class="text-center">Quantities</th>
<th colspan="8" class="text-center">Value (Rs.)</th>
<th rowspan="3" style="min-width:65px;text-align:center;">Verifier</th>
</tr><tr>
<th colspan="2" class="text-center table-secondary">Receipts</th>
<th colspan="2" class="text-center table-secondary">Issues</th>
<th colspan="2" class="text-center table-secondary">Balance</th>
<th colspan="3" class="text-center table-secondary">Receipts</th>
<th colspan="2" class="text-center table-secondary">Issues</th>
<th colspan="3" class="text-center table-secondary">Balance</th>
</tr><tr class="table-light" style="font-size:11px;">
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th><th style="min-width:65px;">P.</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th><th style="min-width:65px;">P.</th>
</tr></thead>'; ?>
                                        <tbody>
                                            <?php
                                            $rTotRqn = $rTotRqw = $rTotRamt = 0;
                                            foreach ($receiptItems as $item):
                                                $rTotRqn  += (float)$item['receipt_qty_number'];
                                                $rTotRqw  += (float)$item['receipt_qty_weight'];
                                                $rTotRamt += (float)$item['receipt_amount_rs'];
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $item['sl_no'] ?></td>
                                                <td><?= htmlspecialchars($item['item_name']         ?? '-') ?></td>
                                                <td class="text-muted small"><?= htmlspecialchars($item['item_description']  ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['item_type_name']     ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['item_category_name'] ?? '-') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                                                <td class="text-end"><?= ($item['receipt_qty_number'] != 0) ? $item['receipt_qty_number'] : '-' ?></td>
                                                <td class="text-end"><?= ($item['receipt_qty_weight'] != 0) ? $item['receipt_qty_weight'] : '-' ?></td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end fw-bold"><?= ($item['balance_qty_number'] != 0) ? $item['balance_qty_number'] : '-' ?></td>
                                                <td class="text-end fw-bold"><?= ($item['balance_qty_weight'] != 0) ? $item['balance_qty_weight'] : '-' ?></td>
                                                <td class="text-end"><?= number_format($item['receipt_rate'],       2) ?></td>
                                                <td class="text-end fw-bold text-success"><?= number_format($item['receipt_amount_rs'], 2) ?></td>
                                                <td class="text-end text-muted"><?= $item['receipt_amount_ps'] ?: '-' ?></td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end"><?= number_format($item['balance_rate'],       2) ?></td>
                                                <td class="text-end fw-bold"><?= number_format($item['balance_amount_rs'], 2) ?></td>
                                                <td class="text-end"><?= $item['balance_amount_ps'] ?: '-' ?></td>
                                                <td class="text-center small"><?= htmlspecialchars($item['value_verifier'] ?? '') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-success fw-bold">
                                                <td colspan="6" class="text-end">RECEIPT TOTAL</td>
                                                <td class="text-end"><?= $rTotRqn ?></td>
                                                <td class="text-end"><?= $rTotRqw ?></td>
                                                <td colspan="4"></td>
                                                <td></td>
                                                <td class="text-end"><?= number_format($rTotRamt, 2) ?></td>
                                                <td colspan="7"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary py-2">No receipt items recorded.</div>
                            <?php endif; ?>
                        </div>

                        <!-- ════════════════════════════════════════
                             SECTION 2: ISSUES
                        ════════════════════════════════════════ -->
                        <div class="mb-4">
                            <h6 class="text-danger fw-bold mb-2">
                                <i class="mdi mdi-arrow-up-circle"></i> ISSUES
                                <span class="badge bg-danger ms-1"><?= count($issueItems) ?> item(s)</span>
                            </h6>

                            <?php if (!empty($issueItems)): ?>
                                <div class="table-responsive" style="overflow-x:auto;">
                                    <table class="table table-bordered table-sm align-middle"
                                           style="min-width:1700px;">
                                        <?php echo '<thead class="table-dark"><tr>
<th rowspan="3" style="width:38px;text-align:center;">Sl</th>
<th rowspan="3" style="min-width:170px;">Item Name</th>
<th rowspan="3" style="min-width:145px;">Description</th>
<th rowspan="3" style="min-width:110px;">Type</th>
<th rowspan="3" style="min-width:110px;">Category</th>
<th rowspan="3" style="min-width:65px;text-align:center;">Unit</th>
<th colspan="6" class="text-center">Quantities</th>
<th colspan="8" class="text-center">Value (Rs.)</th>
<th rowspan="3" style="min-width:65px;text-align:center;">Verifier</th>
</tr><tr>
<th colspan="2" class="text-center table-secondary">Receipts</th>
<th colspan="2" class="text-center table-secondary">Issues</th>
<th colspan="2" class="text-center table-secondary">Balance</th>
<th colspan="3" class="text-center table-secondary">Receipts</th>
<th colspan="2" class="text-center table-secondary">Issues</th>
<th colspan="3" class="text-center table-secondary">Balance</th>
</tr><tr class="table-light" style="font-size:11px;">
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:95px;">No.</th><th style="min-width:95px;">Wt/Msr</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th><th style="min-width:65px;">P.</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th>
<th style="min-width:105px;">Rate</th><th style="min-width:115px;">Rs.</th><th style="min-width:65px;">P.</th>
</tr></thead>'; ?>
                                        <tbody>
                                            <?php
                                            $iTotIqn = $iTotIqw = $iTotIamt = 0;
                                            foreach ($issueItems as $item):
                                                $iTotIqn  += (float)$item['issue_qty_number'];
                                                $iTotIqw  += (float)$item['issue_qty_weight'];
                                                $iTotIamt += (float)$item['issue_amount_rs'];
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $item['sl_no'] ?></td>
                                                <td><?= htmlspecialchars($item['item_name']         ?? '-') ?></td>
                                                <td class="text-muted small"><?= htmlspecialchars($item['item_description']  ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['item_type_name']     ?? '-') ?></td>
                                                <td><?= htmlspecialchars($item['item_category_name'] ?? '-') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end"><?= ($item['issue_qty_number'] != 0) ? $item['issue_qty_number'] : '-' ?></td>
                                                <td class="text-end"><?= ($item['issue_qty_weight'] != 0) ? $item['issue_qty_weight'] : '-' ?></td>
                                                <td class="text-end fw-bold"><?= ($item['balance_qty_number'] != 0) ? $item['balance_qty_number'] : '-' ?></td>
                                                <td class="text-end fw-bold"><?= ($item['balance_qty_weight'] != 0) ? $item['balance_qty_weight'] : '-' ?></td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end text-muted">—</td>
                                                <td class="text-end"><?= number_format($item['issue_rate'],       2) ?></td>
                                                <td class="text-end fw-bold text-danger"><?= number_format($item['issue_amount_rs'], 2) ?></td>
                                                <td class="text-end"><?= number_format($item['balance_rate'],     2) ?></td>
                                                <td class="text-end fw-bold"><?= number_format($item['balance_amount_rs'], 2) ?></td>
                                                <td class="text-end"><?= $item['balance_amount_ps'] ?: '-' ?></td>
                                                <td class="text-center small"><?= htmlspecialchars($item['value_verifier'] ?? '') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-danger fw-bold">
                                                <td colspan="6" class="text-end">ISSUE TOTAL</td>
                                                <td colspan="2"></td>
                                                <td class="text-end"><?= $iTotIqn ?></td>
                                                <td class="text-end"><?= $iTotIqw ?></td>
                                                <td colspan="5"></td>
                                                <td></td>
                                                <td class="text-end"><?= number_format($iTotIamt, 2) ?></td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary py-2">No issue items recorded.</div>
                            <?php endif; ?>
                        </div>

                        <!-- ── Net Summary ──────────────────────── -->
                        <?php if (!empty($receiptItems) || !empty($issueItems)):
                            $netTotRamt = $rTotRamt ?? 0;
                            $netTotIamt = $iTotIamt ?? 0;
                            $netBalance = $netTotRamt - $netTotIamt;
                        ?>
                        <div class="row justify-content-end mt-2">
                            <div class="col-md-5">
                                <table class="table table-sm table-bordered mb-0">
                                    <tr class="table-success">
                                        <td class="fw-bold">Total Receipts (Rs.)</td>
                                        <td class="text-end fw-bold"><?= number_format($netTotRamt, 2) ?></td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td class="fw-bold">Total Issues (Rs.)</td>
                                        <td class="text-end fw-bold"><?= number_format($netTotIamt, 2) ?></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td class="fw-bold">Net Balance (Rs.)</td>
                                        <td class="text-end fw-bold"><?= number_format($netBalance, 2) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ── Status / Timestamps ──────────────── -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="row text-muted small">
                                <div class="col-6">
                                    <span class="badge bg-<?= ($master['status'] == 'ACTIVE') ? 'success' : 'secondary' ?>">
                                        <?= $master['status'] ?>
                                    </span>
                                    &nbsp; Created:
                                    <?= date('d-m-Y H:i', strtotime($master['created_at'])) ?>
                                    by <strong><?= htmlspecialchars($master['created_by_name'] ?? 'N/A') ?></strong>
                                </div>
                                <div class="col-6 text-end">
                                    Last Updated: <?= date('d-m-Y H:i', strtotime($master['updated_at'])) ?>
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
    .no-print, .sidebar, .topbar, .page-header, .footer { display: none !important; }
    .page-content { margin: 0 !important; padding: 0 !important; }
    .card         { border: none !important; box-shadow: none !important; }
    table         { font-size: 9pt !important; }
}
</style>