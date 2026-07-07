{{!-- FILE: app/views/stock/kfc_form13_view.php --}}
<div class="page-content">
<div class="page-container">
<div class="row">
<div class="col-12">
<div class="card">

    <!-- ═══ Card Header ══════════════════════════════════════ -->
    <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
        <div>
            <h4 class="header-title mb-0">KFC Form 13 — Indent Details</h4>
            <small class="text-muted">
                <?= htmlspecialchars($form['college_name'] ?? '') ?>
                <?php if (!empty($form['department_name'])): ?> &mdash; <?= htmlspecialchars($form['department_name']) ?><?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($form['status'] === 'DRAFT'): ?>
            <a href="<?= APP_URL ?>kfc13" class="btn btn-sm btn-outline-warning">
                <i class="ti ti-pencil"></i> Edit
            </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-printer"></i> Print
            </button>
            <a href="<?= APP_URL ?>kfc13/exportPdf/<?= $form['id'] ?>" target="_blank"
               class="btn btn-sm btn-outline-dark">
                <i class="ti ti-file-type-pdf"></i> Export PDF
            </a>
            <a href="<?= APP_URL ?>kfc13" class="btn btn-sm btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body" id="printArea">

        <!-- ═══ Government Header ════════════════════════════ -->
        <div class="text-center mb-3">
            <p class="text-muted small mb-0">K.F.C. FORM 13 — Government of Kerala</p>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($form['college_name'] ?? '') ?></h5>
            <?php if (!empty($form['department_name'])): ?>
                <p class="mb-0 text-secondary"><?= htmlspecialchars($form['department_name']) ?></p>
            <?php elseif (!empty($form['dept_name_free'])): ?>
                <p class="mb-0 text-secondary"><?= htmlspecialchars($form['dept_name_free']) ?></p>
            <?php endif; ?>
            <h6 class="mt-1 fw-bold text-uppercase">Annual Indent for Stores</h6>
        </div>

        <!-- ═══ Meta Row ══════════════════════════════════════ -->
        <div class="row mb-3">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0" style="width:auto; font-size:13px">
                    <tr>
                        <td class="fw-bold pe-3">Indent No.</td>
                        <td><?= htmlspecialchars($form['form_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-3">Financial Year</td>
                        <td><?= htmlspecialchars($form['financial_year']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-3">Date</td>
                        <td><?= date('d.m.Y', strtotime($form['indent_date'])) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-3">Indent Type</td>
                        <td><?php
                            $tl = ['GENERAL'=>'General','DIETARY'=>'Dietary','SPC'=>'SPC (Stores Purchase Committee)'];
                            echo $tl[$form['indent_type']] ?? $form['indent_type'];
                        ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6 text-end">
                <?php
                    $sc = ['DRAFT'=>'secondary','SUBMITTED'=>'primary','APPROVED'=>'success','REJECTED'=>'danger','CLOSED'=>'dark'];
                    $cc = $sc[$form['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $cc ?> fs-6"><?= $form['status'] ?></span>
                <?php if (!empty($form['approved_by_name'])): ?>
                    <div class="mt-1 small text-muted">Approved by: <strong><?= htmlspecialchars($form['approved_by_name']) ?></strong></div>
                <?php endif; ?>
                <?php if ($form['status'] === 'REJECTED' && !empty($form['rejection_reason'])): ?>
                    <div class="mt-1 small text-danger">Reason: <?= htmlspecialchars($form['rejection_reason']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ Form Fields ═══════════════════════════════════ -->
        <div class="row g-2 mb-3">
            <?php
            $metaFields = [
                'Prev. Correspondence Ref'  => $form['prev_correspondence_ref'],
                'Funds Provided'            => ['Y'=>'Yes','N'=>'No','PARTIAL'=>'Partial'][$form['funds_provided']] ?? '',
                'Funds Remark'              => $form['funds_remark'],
                'Delivery Address'          => $form['delivery_address'],
                'Nearest Railway Station'   => $form['nearest_railway_station'],
                'Place of Delivery'         => $form['delivery_place'],
                'Inspecting Officer'        => trim(($form['inspecting_officer_name'] ?? '') . ', ' . ($form['inspecting_officer_desig'] ?? ''), ', '),
                'Special Instructions'      => $form['special_instructions'],
                'Sanction Authority'        => $form['sanction_authority'],
                'Sanction Order No.'        => $form['sanction_order_no'],
                'Sanction Order Date'       => $form['sanction_order_date'] ? date('d-m-Y', strtotime($form['sanction_order_date'])) : '',
            ];
            foreach ($metaFields as $label => $val):
                if (empty($val)) continue;
            ?>
            <div class="col-md-4">
                <div class="border rounded p-2" style="font-size:12px">
                    <div class="text-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:0.4px"><?= $label ?></div>
                    <div class="fw-semibold"><?= nl2br(htmlspecialchars($val)) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ═══ Items Table ═══════════════════════════════════ -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm align-middle" style="font-size:12px">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width:32px">Sl.</th>
                        <th>Article / Description</th>
                        <th class="text-center">Group</th>
                        <th class="text-center">Unit</th>
                        <th class="text-end">Stock on Hand</th>
                        <th class="text-end">Purchases This Yr</th>
                        <th class="text-end">Qty Required</th>
                        <th class="text-end">By Wt.</th>
                        <th class="text-end">By No.</th>
                        <th class="text-end">By Vol.</th>
                        <th class="text-end">Last Rate / Est.</th>
                        <th class="text-end">Amount (Rs.)</th>
                        <th>Last Supplier</th>
                        <th>Purpose</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    if (!empty($items)):
                        foreach ($items as $item):
                            $total += (float)($item['amount'] ?? 0);
                    ?>
                    <tr>
                        <td class="text-center"><?= $item['sl_no'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['article_description'] ?: ($item['item_name'] ?? '')) ?></strong>
                            <?php if (!empty($item['trade_name'])): ?>
                                <br><small class="text-muted">Trade: <?= htmlspecialchars($item['trade_name']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($item['size_spec'])): ?>
                                <br><small class="text-muted">Size: <?= htmlspecialchars($item['size_spec']) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($item['make_name'])): ?>
                                <br><small class="text-muted">Make: <?= htmlspecialchars($item['make_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($item['group_name'] ?? '—') ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                        <td class="text-end"><?= $item['stock_on_hand'] ?: '—' ?></td>
                        <td class="text-end"><?= $item['purchases_this_year'] ?: '—' ?></td>
                        <td class="text-end fw-bold"><?= $item['qty_required'] ?: '—' ?></td>
                        <td class="text-end"><?= $item['qty_required_by_weight'] ?: '—' ?></td>
                        <td class="text-end"><?= $item['qty_required_by_number'] ?: '—' ?></td>
                        <td class="text-end"><?= $item['qty_required_by_volume'] ?: '—' ?></td>
                        <td class="text-end">
                            <?php if (!empty($item['last_purchase_rate'])): ?>
                                <?= number_format($item['last_purchase_rate'], 2) ?>
                            <?php elseif (!empty($item['estimated_cost'])): ?>
                                ~<?= number_format($item['estimated_cost'], 2) ?>
                            <?php else: echo '—'; endif; ?>
                        </td>
                        <td class="text-end"><?= !empty($item['amount']) ? number_format($item['amount'], 2) : '—' ?></td>
                        <td><?= htmlspecialchars($item['last_supplier_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['purpose'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- Totals row -->
                    <tr class="table-warning fw-bold">
                        <td colspan="11" class="text-end">Total Estimated Amount:</td>
                        <td class="text-end">Rs. <?= number_format($total, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center text-muted py-3">No items found for this indent.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ═══ Certification ════════════════════════════════ -->
        <div class="alert alert-light border mb-3 py-2" style="font-style:italic; font-size:13px;">
            I hereby certify that the purchase of the stores has been sanctioned by the competent authority
            <?php if (!empty($form['sanction_order_no'])): ?>
                vide sanction order no. <strong><?= htmlspecialchars($form['sanction_order_no']) ?></strong>
            <?php endif; ?>
            and that the funds required for the expenditure involved have been provided in the budget for the year.
        </div>

        <!-- ═══ Signature Row ════════════════════════════════ -->
        <div class="row text-center mt-4">
            <div class="col-4">
                <div class="border-top pt-2">
                    <strong><?= htmlspecialchars($form['created_by_name'] ?? '') ?></strong>
                    <div class="small text-muted">Prepared By</div>
                    <div class="small text-muted"><?= date('d-m-Y', strtotime($form['created_at'])) ?></div>
                </div>
            </div>
            <div class="col-4">
                <div class="border border-dashed rounded p-3 text-muted small">Office Seal</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-2">
                    <strong><?= htmlspecialchars($form['approved_by_name'] ?? '') ?></strong>
                    <div class="small text-muted">Authorised Signatory</div>
                    <div class="small text-muted">
                        <?= $form['approved_on'] ? date('d-m-Y', strtotime($form['approved_on'])) : 'Date:' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ Workflow Buttons (no-print) ══════════════════ -->
        <div class="text-center mt-4 no-print d-flex justify-content-center gap-2 flex-wrap">
            <?php if ($form['status'] === 'DRAFT'): ?>
            <button class="btn btn-primary" onclick="submitForm(<?= $form['id'] ?>)">
                <i class="ti ti-send"></i> Submit for Approval
            </button>
            <?php endif; ?>
            <?php if ($form['status'] === 'SUBMITTED'): ?>
            <button class="btn btn-success" onclick="approveForm(<?= $form['id'] ?>)">
                <i class="ti ti-check"></i> Approve
            </button>
            <button class="btn btn-danger" onclick="rejectForm(<?= $form['id'] ?>)">
                <i class="ti ti-x"></i> Reject
            </button>
            <?php endif; ?>
        </div>

    </div><!-- /card-body -->
</div><!-- /card -->
</div>
</div>
</div>
</div><!-- /page-content -->

<!-- ══ REJECT MODAL ══════════════════════════════════════════ -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Form 13</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger btn-sm" onclick="submitReject()">Confirm Reject</button>
            </div>
        </div>
    </div>
</div>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
<style>
@media print {
    .no-print, .sidebar, .topbar, .footer { display: none !important; }
    .page-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { display: none !important; }
}
</style>

<script>
const APP_URL  = '<?= APP_URL ?>';
const FORM_ID  = <?= (int)$form['id'] ?>;

function submitForm(id) {
    Swal.fire({ title:'Submit Form 13?', icon:'question', showCancelButton:true,
        confirmButtonColor:'#0d6efd', confirmButtonText:'Yes, Submit!'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'kfc13/changeStatus', { id, action:'submit' }, function(res) {
            if (res.success) { Swal.fire({icon:'success',title:'Submitted!',text:res.message,
                showConfirmButton:false,timer:1500}).then(()=>location.reload()); }
            else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}

function approveForm(id) {
    Swal.fire({ title:'Approve Form 13?', icon:'question', showCancelButton:true,
        confirmButtonColor:'#198754', confirmButtonText:'Approve'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'kfc13/changeStatus', { id, action:'approve' }, function(res) {
            if (res.success) { Swal.fire({icon:'success',title:'Approved!',text:res.message,
                showConfirmButton:false,timer:1500}).then(()=>location.reload()); }
            else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}

function rejectForm(id) {
    $('#rejection_reason').val('');
    new bootstrap.Modal('#rejectModal').show();
}

function submitReject() {
    const reason = $('#rejection_reason').val().trim();
    if (!reason) { Swal.fire('Required','Enter rejection reason','warning'); return; }
    $.post(APP_URL + 'kfc13/changeStatus',
        { id: FORM_ID, action:'reject', rejection_reason: reason },
        function(res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#rejectModal').hide();
                Swal.fire({icon:'success',title:'Rejected!',text:res.message,
                    showConfirmButton:false,timer:1500}).then(()=>location.reload());
            } else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
}
</script>