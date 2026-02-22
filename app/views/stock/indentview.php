<div class="page-content">
<div class="page-container">
<div class="row">
<div class="col-12">
<div class="card">

    <!-- Card Header -->
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h4 class="header-title mb-0">Indent Details</h4>
            <small class="text-muted">
                <?= htmlspecialchars($indent['college_name'] ?? '') ?>
                <?php if (!empty($indent['department_name'])): ?>
                    &mdash; <?= htmlspecialchars($indent['department_name']) ?>
                <?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2 no-print">
            <!-- Edit button — only when still CREATED -->
            <?php if ($indent['status'] === 'CREATED'): ?>
            <a href="<?= APP_URL ?>indent"
               class="btn btn-secondary btn-sm"
               title="Go back to list and edit">
                <i class="ti ti-pencil"></i> Edit
            </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                <i class="mdi mdi-printer"></i> Print
            </button>
            <a href="<?= APP_URL ?>indent" class="btn btn-secondary btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body" id="printArea">

        <!-- ═══ Institution Header (Physical Form Style) ══ -->
        <div class="text-center mb-3">
            <p class="text-muted small mb-0">Engineering College / Govt. Polytechnic / Technical High School</p>
            <h5 class="fw-bold mb-0"><?= htmlspecialchars($indent['college_name'] ?? '') ?></h5>
            <?php if (!empty($indent['department_name'])): ?>
                <p class="mb-0 text-secondary"><?= htmlspecialchars($indent['department_name']) ?></p>
            <?php endif; ?>
        </div>

        <!-- ═══ Indent Meta Info ══════════════════════════ -->
        <div class="row mb-3">
            <div class="col-6">
                <table class="table table-sm table-borderless mb-0" style="width:auto">
                    <tr>
                        <td class="fw-bold pe-3">Book No.</td>
                        <td><?= htmlspecialchars($indent['book_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-3">Indent No.</td>
                        <td><?= htmlspecialchars($indent['indent_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold pe-3">Date</td>
                        <td><?= date('d.m.Y', strtotime($indent['indent_date'])) ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-6 text-end">
                <?php
                    $typeLabel  = ($indent['item_type'] === 'C') ? 'Consumable' : 'Non-Consumable';
                    $typeColor  = ($indent['item_type'] === 'C') ? 'success' : 'info';
                    $statusColors = [
                        'CREATED'  => 'secondary', 'VERIFIED' => 'info',
                        'PASSED'   => 'primary',   'ISSUED'   => 'warning',
                        'RECEIVED' => 'success',
                    ];
                    $statusColor = $statusColors[$indent['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $typeColor ?> fs-6 mb-2"><?= $typeLabel ?></span><br>
                <span class="badge bg-<?= $statusColor ?> fs-6">
                    <?= htmlspecialchars($indent['status']) ?>
                </span>
            </div>
        </div>

        <!-- ═══ Purpose ══════════════════════════════════ -->
        <?php if (!empty($indent['purpose'])): ?>
        <div class="alert alert-light border mb-3 py-2">
            <strong>Please sanction the issue of the following materials for use in</strong><br>
            <?= htmlspecialchars($indent['purpose']) ?>
        </div>
        <?php endif; ?>

        <!-- ═══ Items Table ═══════════════════════════════ -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width:46px">Sl. No.</th>
                        <th>Particulars</th>
                        <th>For what Purpose</th>
                        <th class="text-center" style="width:90px">Qty. Intended</th>
                        <th class="text-center" style="width:90px">Qty. Passed</th>
                        <th class="text-center" style="width:90px">Qty. Issued</th>
                        <th>Remarks</th>
                        <th class="text-center" style="width:110px">Stock Book<br><small class="fw-normal">Page / Vol</small></th>
                        <th class="text-center" style="width:110px">Day Book<br><small class="fw-normal">Page / Vol</small></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($item['sl_no']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($item['item_name'] ?? '—') ?></strong>
                                <?php if (!empty($item['make_name'])): ?>
                                    <br><small class="text-muted">Make: <?= htmlspecialchars($item['make_name']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($item['model_name'])): ?>
                                    <br><small class="text-muted">Model: <?= htmlspecialchars($item['model_name']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($item['item_description'])): ?>
                                    <br><small><?= htmlspecialchars($item['item_description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['item_purpose'] ?? '') ?></td>
                            <td class="text-center fw-bold"><?= $item['qty_intended'] ?></td>
                            <td class="text-center"><?= $item['qty_passed'] ?: '<span class="text-muted">—</span>' ?></td>
                            <td class="text-center"><?= $item['qty_issued']  ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars($item['remarks'] ?? '') ?></td>
                            <td class="text-center">
                                <?php if (!empty($item['stock_book_page_no'])): ?>
                                    <span class="d-block">Pg. <?= (int)$item['stock_book_page_no'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['stock_book_volume'])): ?>
                                    <small class="text-muted">Vol <?= (int)$item['stock_book_volume'] ?></small>
                                <?php endif; ?>
                                <?php if (empty($item['stock_book_page_no']) && empty($item['stock_book_volume'])): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($item['day_book_page_no'])): ?>
                                    <span class="d-block">Pg. <?= (int)$item['day_book_page_no'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['day_book_volume'])): ?>
                                    <small class="text-muted">Vol <?= (int)$item['day_book_volume'] ?></small>
                                <?php endif; ?>
                                <?php if (empty($item['day_book_page_no']) && empty($item['day_book_volume'])): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ═══ Workflow Action Buttons ══════════════════ -->
        <div class="text-center mb-4 no-print d-flex justify-content-center gap-2 flex-wrap">
            <?php if ($indent['status'] === 'CREATED'): ?>
                <button class="btn btn-success" onclick="verifyIndent(<?= $indent['id'] ?>)">
                    <i class="ti ti-check"></i> Verify Indent
                </button>
            <?php endif; ?>
            <?php if ($indent['status'] === 'VERIFIED'): ?>
                <button class="btn btn-primary" onclick="openPassModal(<?= $indent['id'] ?>)">
                    <i class="ti ti-check-double"></i> Pass Indent
                </button>
            <?php endif; ?>
            <?php if ($indent['status'] === 'PASSED'): ?>
                <button class="btn btn-warning" onclick="openIssueModal(<?= $indent['id'] ?>)">
                    <i class="ti ti-package"></i> Issue Indent
                </button>
            <?php endif; ?>
            <?php if ($indent['status'] === 'ISSUED'): ?>
                <button class="btn btn-success" onclick="receiveIndent(<?= $indent['id'] ?>)">
                    <i class="ti ti-check-circle"></i> Mark as Received
                </button>
            <?php endif; ?>
        </div>

        <!-- ═══ Signatures Section ════════════════════════ -->
        <div class="row g-3 mt-2">
            <!-- Workshop Instructor / Verified -->
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 <?= $indent['verified_by'] ? 'border-success bg-light' : '' ?>">
                    <p class="fw-bold mb-1 text-muted small">WORKSHOP INSTRUCTOR / SUPERINTENDENT</p>
                    <?php if ($indent['verified_by']): ?>
                        <p class="mb-1 text-success fw-bold">
                            <i class="ti ti-check-circle"></i>
                            <?= htmlspecialchars($indent['verified_by_name'] ?? '') ?>
                        </p>
                        <span class="badge bg-success">Verified</span>
                    <?php else: ?>
                        <p class="text-muted mb-1">____________________________</p>
                        <span class="badge bg-secondary">Pending Verification</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- W/Shop Foreman -->
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <p class="fw-bold mb-1 text-muted small">W/SHOP FOREMAN</p>
                    <p class="text-muted mb-1">____________________________</p>
                </div>
            </div>

            <!-- Passed -->
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 <?= $indent['passed_by'] ? 'border-primary bg-light' : '' ?>">
                    <p class="fw-bold mb-1 text-muted small">PASSED BY (SUPERINTENDENT)</p>
                    <?php if ($indent['passed_by']): ?>
                        <p class="mb-1 text-primary fw-bold">
                            <i class="ti ti-check-circle"></i>
                            <?= htmlspecialchars($indent['passed_by_name'] ?? '') ?>
                        </p>
                        <span class="badge bg-primary">Passed</span>
                    <?php else: ?>
                        <p class="text-muted mb-1">____________________________</p>
                        <span class="badge bg-secondary">Pending</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Issued -->
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 <?= $indent['issued_by'] ? 'border-warning bg-light' : '' ?>">
                    <p class="fw-bold mb-1 text-muted small">ISSUED BY (STORE-KEEPER)</p>
                    <?php if ($indent['issued_by']): ?>
                        <p class="mb-1 text-warning fw-bold">
                            <i class="ti ti-check-circle"></i>
                            <?= htmlspecialchars($indent['issued_by_name'] ?? '') ?>
                        </p>
                        <span class="badge bg-warning text-dark">Issued</span>
                    <?php else: ?>
                        <p class="text-muted mb-1">____________________________</p>
                        <span class="badge bg-secondary">Pending</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Received -->
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 <?= $indent['received_by'] ? 'border-success bg-light' : '' ?>">
                    <p class="fw-bold mb-1 text-muted small">RECEIVED BY</p>
                    <?php if ($indent['received_by']): ?>
                        <p class="mb-1 text-success fw-bold">
                            <i class="ti ti-check-circle"></i>
                            <?= htmlspecialchars($indent['received_by_name'] ?? '') ?>
                        </p>
                        <span class="badge bg-success">Received</span>
                    <?php else: ?>
                        <p class="text-muted mb-1">____________________________</p>
                        <span class="badge bg-secondary">Pending</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ═══ Timestamps ════════════════════════════════ -->
        <div class="mt-4 pt-3 border-top">
            <div class="row text-muted small">
                <div class="col-md-6">
                    <i class="mdi mdi-clock-outline me-1"></i>
                    <strong>Created:</strong>
                    <?= date('d-m-Y H:i', strtotime($indent['created_at'])) ?>
                    by <?= htmlspecialchars($indent['created_by_name'] ?? '—') ?>
                </div>
                <div class="col-md-6 text-md-end">
                    <i class="mdi mdi-update me-1"></i>
                    <strong>Last Updated:</strong>
                    <?= date('d-m-Y H:i', strtotime($indent['updated_at'])) ?>
                </div>
            </div>
        </div>

    </div><!-- card-body -->
</div><!-- card -->
</div>
</div>
</div><!-- page-container -->

<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div><!-- page-content -->

<!-- ══ PASS MODAL ══════════════════════════════════════════ -->
<div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Pass Indent — Enter Quantities</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="passForm">
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr>
                            <th>Item</th>
                            <th class="text-center" style="width:110px">Qty Intended</th>
                            <th class="text-center" style="width:130px">Qty Passed</th>
                        </tr></thead>
                        <tbody id="passItemsBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Pass Indent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══ ISSUE MODAL ═════════════════════════════════════════ -->
<div class="modal fade" id="issueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Issue Indent — Enter Quantities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="issueForm">
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr>
                            <th>Item</th>
                            <th class="text-center" style="width:110px">Qty Passed</th>
                            <th class="text-center" style="width:130px">Qty Issued</th>
                        </tr></thead>
                        <tbody id="issueItemsBody"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Issue Indent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .topbar, .footer, .card-header .btn { display: none !important; }
    .page-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>

<script>
const INDENT_ID = <?= (int)$indent['id'] ?>;
const APP_URL   = '<?= APP_URL ?>';

function verifyIndent(id) {
    Swal.fire({
        title:'Verify Indent?', text:'This locks the indent from further editing.',
        icon:'question', showCancelButton:true,
        confirmButtonColor:'#28a745', confirmButtonText:'Yes, Verify!'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'indent/verifyIndent', { id }, function(res) {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Verified!', text:res.message,
                    showConfirmButton:false, timer:1500 }).then(() => location.reload());
            } else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}

function receiveIndent(id) {
    Swal.fire({
        title:'Mark as Received?', text:'This completes the indent process.',
        icon:'question', showCancelButton:true,
        confirmButtonColor:'#28a745', confirmButtonText:'Yes, Received!'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'indent/receiveIndent', { id }, function(res) {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Received!', text:res.message,
                    showConfirmButton:false, timer:1500 }).then(() => location.reload());
            } else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}

function openPassModal(id) {
    $.get(APP_URL + 'indent/getIndentById', { id }, function(res) {
        if (!res.success) return;
        let rows = '';
        res.data.items.forEach(item => {
            rows += `<tr>
                <td>
                    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                    ${item.item_name || 'Item #' + item.item_id}
                    ${item.item_description ? '<br><small class="text-muted">' + item.item_description + '</small>' : ''}
                </td>
                <td class="text-center">${item.qty_intended}</td>
                <td>
                    <input type="number" class="form-control form-control-sm"
                           name="items[${item.id}][qty_passed]"
                           value="${item.qty_intended}" min="0" max="${item.qty_intended}" required>
                </td>
            </tr>`;
        });
        $('#passItemsBody').html(rows);
        $('#passModal').data('indent-id', id);
        new bootstrap.Modal('#passModal').show();
    }, 'json');
}

function openIssueModal(id) {
    $.get(APP_URL + 'indent/getIndentById', { id }, function(res) {
        if (!res.success) return;
        let rows = '';
        res.data.items.forEach(item => {
            rows += `<tr>
                <td>
                    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                    ${item.item_name || 'Item #' + item.item_id}
                    ${item.item_description ? '<br><small class="text-muted">' + item.item_description + '</small>' : ''}
                </td>
                <td class="text-center">${item.qty_passed}</td>
                <td>
                    <input type="number" class="form-control form-control-sm"
                           name="items[${item.id}][qty_issued]"
                           value="${item.qty_passed}" min="0" max="${item.qty_passed}" required>
                </td>
            </tr>`;
        });
        $('#issueItemsBody').html(rows);
        $('#issueModal').data('indent-id', id);
        new bootstrap.Modal('#issueModal').show();
    }, 'json');
}

$('#passForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: APP_URL + 'indent/passIndent', type: 'POST',
        data: $(this).serialize() + '&id=' + $('#passModal').data('indent-id'),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Passed!', text:res.message,
                    showConfirmButton:false, timer:1500 }).then(() => {
                    bootstrap.Modal.getInstance('#passModal').hide();
                    location.reload();
                });
            } else { Swal.fire('Error!', res.message, 'error'); }
        }
    });
});

$('#issueForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: APP_URL + 'indent/issueIndent', type: 'POST',
        data: $(this).serialize() + '&id=' + $('#issueModal').data('indent-id'),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Issued!', text:res.message,
                    showConfirmButton:false, timer:1500 }).then(() => {
                    bootstrap.Modal.getInstance('#issueModal').hide();
                    location.reload();
                });
            } else { Swal.fire('Error!', res.message, 'error'); }
        }
    });
});
</script>