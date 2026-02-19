<div class="page-content">
<div class="page-container">
<div class="row">
<div class="col-12">
<div class="card">

    <div class="card-header border-bottom d-flex justify-content-between align-items-center no-print">
        <h5 class="mb-0">
            Day Book Entry —
            <span class="text-primary"><?= htmlspecialchars($master['provider_name'] ?? '') ?></span>
            <span class="badge bg-secondary ms-2"><?= htmlspecialchars($master['page_no']) ?></span>
            <span class="text-muted ms-2 fs-6"><?= date('d-m-Y', strtotime($master['document_date'])) ?></span>
        </h5>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="mdi mdi-printer"></i> Print
            </button>
            <a href="<?= APP_URL ?>daybook" class="btn btn-sm btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body" id="printArea">

        <!-- ── KFC Form 16 Print Header ──────────────────── -->
        <div class="text-center mb-3">
            <div style="font-size:11px;letter-spacing:1px;text-transform:uppercase;">K.F.C. FORM 16</div>
            <div class="text-muted" style="font-size:10px;">[See Chapter VII, Article 161 (a)]</div>
            <h5 class="fw-bold mt-1 mb-0">DAY BOOK OF STORES</h5>
        </div>

        <!-- ── Master Info ────────────────────────────────── -->
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:155px;">Stockbook</td>
                        <td>: <?= htmlspecialchars($master['stockbook_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Page No</td>
                        <td>: <span class="badge bg-secondary"><?= htmlspecialchars($master['page_no']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Class / Unit</td>
                        <td>: <?= htmlspecialchars(($master['class'] ?: '-') . ' / ' . ($master['unit_label'] ?: '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Document Date</td>
                        <td>: <strong><?= date('d-m-Y', strtotime($master['document_date'])) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">By Whom / Provider</td>
                        <td>: <strong><?= htmlspecialchars($master['provider_name'] ?? '-') ?></strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted fw-semibold" style="width:155px;">Issued To</td>
                        <td>: <strong><?= htmlspecialchars($master['issued_to_name'] ?? '-') ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Order / Ref No</td>
                        <td>: <?= htmlspecialchars($master['receipt_order_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Invoice Ref</td>
                        <td>: <?= htmlspecialchars($master['invoice_ref'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">Invoice Date</td>
                        <td>: <?= !empty($master['invoice_date']) ? date('d-m-Y', strtotime($master['invoice_date'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-semibold">CR Voucher Ref</td>
                        <td>: <?= htmlspecialchars($master['cr_voucher_ref'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($master['verifier_name']) || !empty($master['remarks'])): ?>
        <div class="row mb-3 pb-2 border-bottom">
            <?php if (!empty($master['verifier_name'])): ?>
            <div class="col-md-4">
                <span class="text-muted small">Verifier</span><br>
                <strong><?= htmlspecialchars($master['verifier_name']) ?></strong>
            </div>
            <?php endif; ?>
            <?php if (!empty($master['remarks'])): ?>
            <div class="col-md-8">
                <span class="text-muted small">Remarks</span><br>
                <em><?= htmlspecialchars($master['remarks']) ?></em>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════════
             RECEIPTS SECTION
        ════════════════════════════════════════════════ -->
        <div class="mb-4">
            <h6 class="text-success fw-bold mb-2">
                <i class="mdi mdi-arrow-down-circle"></i> RECEIPTS
                <span class="badge bg-success ms-1"><?= count($receiptItems) ?> line(s)</span>
            </h6>

            <?php if (!empty($receiptItems)): ?>
            <div class="table-responsive" style="overflow-x:auto;">
            <table class="table table-bordered table-sm align-middle" style="min-width:1200px;font-size:13px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" style="width:38px;text-align:center;">Sl</th>
                        <th rowspan="2" style="min-width:170px;">Item Name</th>
                        <th rowspan="2" style="min-width:150px;">Description</th>
                        <th rowspan="2" style="min-width:70px;text-align:center;">Unit</th>
                        <th colspan="2" class="text-center">Receipt Qty</th>
                        <th colspan="3" class="text-center">Receipt Value</th>
                        <th colspan="3" class="text-center">Balance</th>
                        <th rowspan="2" style="min-width:70px;text-align:center;">Verifier</th>
                    </tr>
                    <tr style="font-size:11px;">
                        <th style="min-width:90px;">No.</th>
                        <th style="min-width:90px;">Wt/Msr</th>
                        <th style="min-width:90px;">Rate</th>
                        <th style="min-width:100px;">Rs.</th>
                        <th style="min-width:60px;">Ps.</th>
                        <th style="min-width:90px;">Rate</th>
                        <th style="min-width:100px;">Rs.</th>
                        <th style="min-width:60px;">Ps.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rTotQN = $rTotQW = $rTotAmt = 0;
                    foreach ($receiptItems as $item):
                        $rTotQN  += (float)$item['receipt_qty_number'];
                        $rTotQW  += (float)$item['receipt_qty_weight'];
                        $rTotAmt += (float)$item['receipt_amount_rs'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $item['sl_no'] ?></td>
                        <td><strong><?= htmlspecialchars($item['item_name'] ?? '-') ?></strong></td>
                        <td class="text-muted small"><?= htmlspecialchars($item['item_description'] ?? '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                        <td class="text-end"><?= ($item['receipt_qty_number'] != 0) ? $item['receipt_qty_number'] : '—' ?></td>
                        <td class="text-end"><?= ($item['receipt_qty_weight'] != 0) ? $item['receipt_qty_weight'] : '—' ?></td>
                        <td class="text-end"><?= number_format($item['receipt_rate'],      2) ?></td>
                        <td class="text-end fw-bold text-success"><?= number_format($item['receipt_amount_rs'], 2) ?></td>
                        <td class="text-end"><?= $item['receipt_amount_ps'] ?: '—' ?></td>
                        <td class="text-end"><?= number_format($item['balance_rate'],      2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($item['balance_amount_rs'], 2) ?></td>
                        <td class="text-end"><?= $item['balance_amount_ps'] ?: '—' ?></td>
                        <td class="text-center small"><?= htmlspecialchars($item['value_verifier'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-success fw-bold">
                        <td colspan="4" class="text-end">RECEIPT TOTAL</td>
                        <td class="text-end"><?= $rTotQN ?></td>
                        <td class="text-end"><?= $rTotQW ?></td>
                        <td colspan="2" class="text-end"><?= number_format($rTotAmt, 2) ?></td>
                        <td colspan="5"></td>
                    </tr>
                </tbody>
            </table>
            </div>
            <?php else: ?>
                <div class="alert alert-secondary py-2 mb-0">No receipt items recorded.</div>
            <?php endif; ?>
        </div>

        <!-- ════════════════════════════════════════════════
             ISSUES SECTION
        ════════════════════════════════════════════════ -->
        <div class="mb-4">
            <h6 class="text-danger fw-bold mb-2">
                <i class="mdi mdi-arrow-up-circle"></i> ISSUES
                <span class="badge bg-danger ms-1"><?= count($issueItems) ?> line(s)</span>
            </h6>

            <?php if (!empty($issueItems)): ?>
            <div class="table-responsive" style="overflow-x:auto;">
            <table class="table table-bordered table-sm align-middle" style="min-width:1350px;font-size:13px;">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" style="width:38px;text-align:center;">Sl</th>
                        <th rowspan="2" style="min-width:170px;">Item Name</th>
                        <th rowspan="2" style="min-width:150px;">Description</th>
                        <th rowspan="2" style="min-width:70px;text-align:center;">Unit</th>
                        <th colspan="2" class="text-center">Issue Qty</th>
                        <th colspan="2" class="text-center">Issue Value</th>
                        <th colspan="3" class="text-center">Balance</th>
                        <th rowspan="2" style="min-width:130px;">Indent No</th>
                        <th rowspan="2" style="min-width:100px;">Indent Date</th>
                        <th rowspan="2" style="min-width:70px;text-align:center;">Issued To</th>
                    </tr>
                    <tr style="font-size:11px;">
                        <th style="min-width:90px;">No.</th>
                        <th style="min-width:90px;">Wt/Msr</th>
                        <th style="min-width:90px;">Rate</th>
                        <th style="min-width:100px;">Rs.</th>
                        <th style="min-width:90px;">Rate</th>
                        <th style="min-width:100px;">Rs.</th>
                        <th style="min-width:60px;">Ps.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $iTotQN = $iTotQW = $iTotAmt = 0;
                    foreach ($issueItems as $item):
                        $iTotQN  += (float)$item['issue_qty_number'];
                        $iTotQW  += (float)$item['issue_qty_weight'];
                        $iTotAmt += (float)$item['issue_amount_rs'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $item['sl_no'] ?></td>
                        <td><strong><?= htmlspecialchars($item['item_name'] ?? '-') ?></strong></td>
                        <td class="text-muted small"><?= htmlspecialchars($item['item_description'] ?? '-') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['unit_name'] ?? '-') ?></td>
                        <td class="text-end"><?= ($item['issue_qty_number'] != 0) ? $item['issue_qty_number'] : '—' ?></td>
                        <td class="text-end"><?= ($item['issue_qty_weight'] != 0) ? $item['issue_qty_weight'] : '—' ?></td>
                        <td class="text-end"><?= number_format($item['issue_rate'],        2) ?></td>
                        <td class="text-end fw-bold text-danger"><?= number_format($item['issue_amount_rs'],   2) ?></td>
                        <td class="text-end"><?= number_format($item['balance_rate'],      2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($item['balance_amount_rs'],   2) ?></td>
                        <td class="text-end"><?= $item['balance_amount_ps'] ?: '—' ?></td>
                        <td><?= htmlspecialchars($item['indent_no'] ?? '—') ?></td>
                        <td><?= !empty($item['indent_date']) ? date('d-m-Y', strtotime($item['indent_date'])) : '—' ?></td>
                        <td class="text-center small"><?= htmlspecialchars($item['issued_to_name'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-danger fw-bold">
                        <td colspan="4" class="text-end">ISSUE TOTAL</td>
                        <td class="text-end"><?= $iTotQN ?></td>
                        <td class="text-end"><?= $iTotQW ?></td>
                        <td></td>
                        <td class="text-end"><?= number_format($iTotAmt, 2) ?></td>
                        <td colspan="6"></td>
                    </tr>
                </tbody>
            </table>
            </div>
            <?php else: ?>
                <div class="alert alert-secondary py-2 mb-0">No issue items recorded.</div>
            <?php endif; ?>
        </div>

        <!-- ── Net Summary ────────────────────────────────── -->
        <?php
        $netR = $rTotAmt ?? 0;
        $netI = $iTotAmt ?? 0;
        $netB = $netR - $netI;
        ?>
        <div class="row justify-content-end mb-3">
            <div class="col-md-4">
                <table class="table table-sm table-bordered mb-0">
                    <tr class="table-success">
                        <td class="fw-bold">Total Receipts (Rs.)</td>
                        <td class="text-end fw-bold"><?= number_format($netR, 2) ?></td>
                    </tr>
                    <tr class="table-danger">
                        <td class="fw-bold">Total Issues (Rs.)</td>
                        <td class="text-end fw-bold"><?= number_format($netI, 2) ?></td>
                    </tr>
                    <tr class="table-warning">
                        <td class="fw-bold">Net Balance (Rs.)</td>
                        <td class="text-end fw-bold"><?= number_format($netB, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────── -->
        <div class="border-top pt-2 mt-2">
            <div class="row text-muted" style="font-size:12px;">
                <div class="col-6">
                    <span class="badge bg-<?= ($master['status'] == 'ACTIVE') ? 'success' : 'secondary' ?>">
                        <?= $master['status'] ?>
                    </span>
                    &nbsp;Created:
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
</div><!-- /page-container -->
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div><!-- /page-content -->

<style>
@media print {
    .no-print, .sidebar, .topbar, .footer, .page-header { display: none !important; }
    .page-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    table { font-size: 9pt !important; }
    .badge { border: 1px solid #999; color: #000 !important; background: #eee !important; }
}
</style>