<div class="page-content">
<div class="page-container">

<!-- ═══════════════════════════════════════════════════════
     CREATE / EDIT INDENT FORM
═══════════════════════════════════════════════════════ -->
<div class="row">
<div class="col-12">
<div class="card">
    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
        <div>
            <h4 class="header-title mb-0" id="formTitle">Create New Indent</h4>
            <!-- Session institution & department shown as read-only info -->
            <small class="text-muted">
                <i class="mdi mdi-domain me-1"></i><?= htmlspecialchars($inst_name ?? '') ?>
                <?php if (!empty($dept_name)): ?>
                    &nbsp;&mdash;&nbsp;<i class="mdi mdi-sitemap me-1"></i><?= htmlspecialchars($dept_name) ?>
                <?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn">
                <i class="mdi mdi-refresh"></i> Reset
            </button>
        </div>
    </div>

    <div class="card-body">
        <form id="indentForm" method="post">
            <input type="hidden" id="editIndentId" name="edit_id" value="">

            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Book No <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="book_no" name="book_no" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Indent No <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="indent_no" name="indent_no" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Item Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="item_type" name="item_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="C">Consumable</option>
                        <option value="N">Non-Consumable</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="indent_date" name="indent_date" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Purpose</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="2"
                        placeholder="Please sanction the issue of the following materials for use in..."></textarea>
                </div>
            </div>

            <!-- Items Section -->
            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0">Items</h5>
                    <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                        <i class="mdi mdi-plus"></i> Add Item
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="itemsTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:60px">Sl.No</th>
                                <th style="width:160px">Item Name<span class="text-danger">*</span></th>
                                <th style="width:130px">Make</th>
                                <th style="width:130px">Model</th>
                                <th>Description</th>
                                <th>Purpose</th>
                                <th style="width:75px">Qty <span class="text-danger">*</span></th>
                                <th>Remarks</th>
                                <th style="width:160px">Stock Book<br><small class="fw-normal text-muted">Page / Volume</small></th>
                                <th style="width:160px">Day Book<br><small class="fw-normal text-muted">Page / Volume</small></th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="text-end mt-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary d-none" id="cancelEditBtn">
                    <i class="mdi mdi-close"></i> Cancel Edit
                </button>
                <button type="submit" class="btn btn-primary" id="saveBtn">
                    <i class="mdi mdi-content-save"></i> Save Indent
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════
     INDENT LIST
═══════════════════════════════════════════════════════ -->
<div class="row mt-4">
<div class="col-12">
<div class="card">
    <div class="card-header border-bottom border-dashed">
        <h4 class="header-title">Indent List</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table id="indent-datatable" class="table table-striped dt-responsive nowrap w-100">
            <thead>
                <tr>
                    <th>Sl.</th>
                    <th>Book No</th>
                    <th>Indent No</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Institution / Dept</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($result)): ?>
                    <?php
                    /* Serial number is a simple ascending counter regardless of sort order */
                    $sl = 1;
                    foreach ($result as $row):
                        $statusColors = [
                            'CREATED'  => 'secondary',
                            'VERIFIED' => 'info',
                            'PASSED'   => 'primary',
                            'ISSUED'   => 'warning',
                            'RECEIVED' => 'success',
                        ];
                        $statusColor = $statusColors[$row['status']] ?? 'secondary';
                        $typeLabel   = ($row['item_type'] === 'C') ? 'Consumable' : 'Non-Consumable';
                        $typeColor   = ($row['item_type'] === 'C') ? 'success' : 'info';
                        $isCreated   = ($row['status'] === 'CREATED');
                    ?>
                    <tr id="indentRow_<?= $row['id'] ?>">
                        <!-- Sl. No. — ascending counter (not DB id) -->
                        <td><?= $sl++ ?></td>
                        <td><?= htmlspecialchars($row['book_no']) ?></td>
                        <td><strong><?= htmlspecialchars($row['indent_no']) ?></strong></td>
                        <td>
                            <span class="badge bg-<?= $typeColor ?>">
                                <?= htmlspecialchars($typeLabel) ?>
                            </span>
                        </td>
                        <td><?= date('d-m-Y', strtotime($row['indent_date'])) ?></td>
                        <td>
                            <span class="d-block"><?= htmlspecialchars($row['college_name'] ?? '—') ?></span>
                            <?php if (!empty($row['department_name'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($row['department_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['created_by_name'] ?? '—') ?></td>
                        <td>
                            <!-- View (always available) -->
                            <a href="<?= APP_URL ?>indent/viewIndent/<?= $row['id'] ?>"
                               class="btn btn-sm btn-info" title="View">
                                <i class="ti ti-eye"></i>
                            </a>

                            <!-- EDIT — only when CREATED (not yet verified) -->
                            <?php if ($isCreated): ?>
                            <button class="btn btn-sm btn-secondary editIndentBtn"
                                    data-id="<?= $row['id'] ?>" title="Edit">
                                <i class="ti ti-pencil"></i>
                            </button>
                            <?php endif; ?>

                            <!-- VERIFY — CREATED -->
                            <?php if ($isCreated): ?>
                            <button class="btn btn-sm btn-success verifyBtn"
                                    data-id="<?= $row['id'] ?>" title="Verify">
                                <i class="ti ti-check"></i>
                            </button>
                            <?php endif; ?>

                            <!-- PASS — VERIFIED -->
                            <?php if ($row['status'] === 'VERIFIED'): ?>
                            <button class="btn btn-sm btn-primary passBtn"
                                    data-id="<?= $row['id'] ?>" title="Pass">
                                <i class="ti ti-certificate"></i>
                            </button>
                            <?php endif; ?>

                            <!-- ISSUE — PASSED -->
                            <?php if ($row['status'] === 'PASSED'): ?>
                            <button class="btn btn-sm btn-warning issueBtn"
                                    data-id="<?= $row['id'] ?>" title="Issue">
                                <i class="ti ti-package"></i>
                            </button>
                            <?php endif; ?>

                            <!-- RECEIVE — ISSUED -->
                            <?php if ($row['status'] === 'ISSUED'): ?>
                            <button class="btn btn-sm btn-success receiveBtn"
                                    data-id="<?= $row['id'] ?>" title="Receive">
                                <i class="ti ti-file-check"></i>
                            </button>
                            <?php endif; ?>

                            <!-- DELETE — only CREATED -->
                            <?php if ($isCreated): ?>
                            <button class="btn btn-sm btn-danger deleteIndentBtn"
                                    data-id="<?= $row['id'] ?>" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No indents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</div>
</div>

</div><!-- page-container -->

<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>

<!-- ═══════════════════════════════════════════════════════
     PASS MODAL
═══════════════════════════════════════════════════════ -->
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
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th class="text-center" style="width:110px">Qty Intended</th>
                                <th class="text-center" style="width:130px">Qty Passed</th>
                            </tr>
                        </thead>
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

<!-- ═══════════════════════════════════════════════════════
     ISSUE MODAL
═══════════════════════════════════════════════════════ -->
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
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center" style="width:110px">Qty Passed</th>
                                <th class="text-center" style="width:130px">Qty Issued</th>
                            </tr>
                        </thead>
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

</div><!-- page-content -->

<!-- ═══════════════════════════════════════════════════════
     SCRIPT
═══════════════════════════════════════════════════════ -->
<script>
$(function () {

    /* ── PHP data ────────────────────────────────────────── */
    const itemsList = <?= json_encode($items ?? []) ?>;
    const makesList = <?= json_encode($makes ?? []) ?>;
    let   itemCounter = 0;
    let   isEditMode  = false;

    /* ── DataTable ───────────────────────────────────────── */
    if (!$.fn.DataTable.isDataTable('#indent-datatable')) {
        $('#indent-datatable').DataTable({
            pageLength : 10,
            ordering   : true,
            searching  : true,
            /* Column 0 (Sl.) is already pre-rendered ascending; disable re-sort on it */
            columnDefs : [{ orderable: false, targets: [0, 8] }],
            order      : []   /* preserve PHP render order */
        });
    }

    /* ── Defaults ────────────────────────────────────────── */
    $('#indent_date').val(new Date().toISOString().split('T')[0]);

    /* ── Build one item row ──────────────────────────────── */
    function buildItemRow(idx, data) {
        data = data || {};
        let itemOpts = '<option value="">-- Select Item --</option>';
        itemsList.forEach(i => {
            itemOpts += `<option value="${i.id}"${data.item_id == i.id ? ' selected' : ''}>${i.item_name}</option>`;
        });
        let makeOpts = '<option value="">-- Select Make --</option>';
        makesList.forEach(m => {
            makeOpts += `<option value="${m.id}"${data.make_id == m.id ? ' selected' : ''}>${m.make_name}</option>`;
        });
        return `
        <tr class="item-row">
            <td>
                <input type="number" class="form-control form-control-sm"
                       name="items[${idx}][sl_no]" value="${data.sl_no || idx}" style="width:80px;min-width:80px" readonly>
                       <input type="hidden" name="items[${idx}][id]" value="${data.id || 0}">
            </td>
            <td>
                <select class="form-select form-select-sm item-select"
                        name="items[${idx}][item_id]" required>${itemOpts}</select>
            </td>
            <td>
                <select class="form-select form-select-sm make-select"
                        name="items[${idx}][make_id]" data-idx="${idx}">${makeOpts}</select>
            </td>
            <td>
                <select class="form-select form-select-sm model-select"
                        name="items[${idx}][model_id]" data-idx="${idx}">
                    <option value="">-- Select Model --</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm"
                       name="items[${idx}][item_description]"
                       value="${data.item_description || ''}"></td>
            <td><input type="text" class="form-control form-control-sm"
                       name="items[${idx}][item_purpose]"
                       value="${data.item_purpose || ''}"></td>
            <td><input type="number" class="form-control form-control-sm"
                       name="items[${idx}][qty_intended]"
                       value="${data.qty_intended || ''}" min="1" required></td>
            <td><input type="text" class="form-control form-control-sm"
                       name="items[${idx}][remarks]"
                       value="${data.remarks || ''}"></td>
            <td>
                <div class="d-flex gap-1">
                    <input type="number" class="form-control form-control-sm" style="width:70px"
                           name="items[${idx}][stock_book_page_no]"
                           placeholder="Page" min="1"
                           value="${data.stock_book_page_no || ''}">
                    <select class="form-select form-select-sm" style="width:80px"
                            name="items[${idx}][stock_book_volume]">
                        <option value="">Vol</option>
                        <option value="1"${data.stock_book_volume == 1 ? ' selected' : ''}>Vol 1</option>
                        <option value="2"${data.stock_book_volume == 2 ? ' selected' : ''}>Vol 2</option>
                        <option value="3"${data.stock_book_volume == 3 ? ' selected' : ''}>Vol 3</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <input type="number" class="form-control form-control-sm" style="width:70px"
                           name="items[${idx}][day_book_page_no]"
                           placeholder="Page" min="1"
                           value="${data.day_book_page_no || ''}">
                    <select class="form-select form-select-sm" style="width:80px"
                            name="items[${idx}][day_book_volume]">
                        <option value="">Vol</option>
                        <option value="1"${data.day_book_volume == 1 ? ' selected' : ''}>Vol 1</option>
                        <option value="2"${data.day_book_volume == 2 ? ' selected' : ''}>Vol 2</option>
                        <option value="3"${data.day_book_volume == 3 ? ' selected' : ''}>Vol 3</option>
                    </select>
                </div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="ti ti-trash"></i>
                </button>
            </td>
        </tr>`;
    }

    /* ── Add blank row ───────────────────────────────────── */
    $('#addItemBtn').click(function () {
        itemCounter++;
        $('#itemsTableBody').append(buildItemRow(itemCounter));
    });

    /* ── Remove row ──────────────────────────────────────── */
    $(document).on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
    });

    /* ── Make → Model cascade ────────────────────────────── */
    $(document).on('change', '.make-select', function () {
        const makeId = $(this).val();
        const idx    = $(this).data('idx');
        const $model = $(`.model-select[data-idx="${idx}"]`);

        $model.html('<option value="">-- Select Model --</option>');
        if (!makeId) return;

        $.get('<?= APP_URL ?>indent/getModelsByMake', { make_id: makeId }, function (res) {
            if (res.success) {
                res.data.forEach(m => {
                    $model.append(`<option value="${m.id}">${m.model_name}</option>`);
                });
            }
        }, 'json');
    });

    /* ── Reset / Cancel Edit ─────────────────────────────── */
    function resetForm() {
        isEditMode = false;
        $('#indentForm')[0].reset();
        $('#itemsTableBody').html('');
        $('#editIndentId').val('');
        $('#formTitle').text('Create New Indent');
        $('#saveBtn').html('<i class="mdi mdi-content-save"></i> Save Indent');
        $('#cancelEditBtn').addClass('d-none');
        $('#indent_date').val(new Date().toISOString().split('T')[0]);
        itemCounter = 0;
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    $('#resetFormBtn').click(resetForm);
    $('#cancelEditBtn').click(resetForm);

    /* ── SUBMIT (Create or Update) ───────────────────────── */
    $('#indentForm').submit(function (e) {
        e.preventDefault();

        if ($('.item-row').length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one item' });
            return;
        }

        const editId = $('#editIndentId').val();
        const url    = editId
            ? '<?= APP_URL ?>indent/crudData/updation?id=' + editId
            : '<?= APP_URL ?>indent/crudData/insertion';

        $.ajax({
            url      : url,
            type     : 'POST',
            data     : $(this).serialize(),
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success', title: 'Success!', text: res.message,
                        showConfirmButton: false, timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: res.message });
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseText });
            }
        });
    });

    /* ══════════════════════════════════════════════════════
       EDIT BUTTON — load indent into form (CREATED only)
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.editIndentBtn', function () {
        const id = $(this).data('id');

        $.get('<?= APP_URL ?>indent/getIndentById', { id }, function (res) {
            if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }

            const { indent, items } = res.data;

            /* Guard: only editable if CREATED */
            if (indent.status !== 'CREATED') {
                Swal.fire('Cannot Edit', 'Only CREATED indents can be edited.', 'warning');
                return;
            }

            isEditMode = true;
            $('#editIndentId').val(indent.id);
            $('#book_no').val(indent.book_no);
            $('#indent_no').val(indent.indent_no);
            $('#item_type').val(indent.item_type);
            $('#indent_date').val(indent.indent_date);
            $('#purpose').val(indent.purpose);
            $('#formTitle').text('Edit Indent — ' + indent.indent_no);
            $('#saveBtn').html('<i class="mdi mdi-content-save-edit"></i> Update Indent');
            $('#cancelEditBtn').removeClass('d-none');

            /* Populate items */
            $('#itemsTableBody').html('');
            itemCounter = 0;
            items.forEach(item => {
                itemCounter++;
                const $row = $(buildItemRow(itemCounter, {
                    id                   : item.id,   // ← add this line
                    sl_no                : item.sl_no,
                    item_id              : item.item_id,
                    make_id              : item.make_id,
                    model_id             : item.model_id,
                    item_description     : item.item_description,
                    item_purpose         : item.item_purpose,
                    qty_intended         : item.qty_intended,
                    remarks              : item.remarks,
                    stock_book_page_no   : item.stock_book_page_no,
                    stock_book_volume    : item.stock_book_volume,
                    day_book_page_no     : item.day_book_page_no,
                    day_book_volume      : item.day_book_volume,
                }));
                $('#itemsTableBody').append($row);

                /* Load model options if make is set */
                if (item.make_id) {
                    const $model = $row.find('.model-select');
                    $.get('<?= APP_URL ?>indent/getModelsByMake', { make_id: item.make_id }, function (r) {
                        if (r.success) {
                            r.data.forEach(m => {
                                $model.append(`<option value="${m.id}"${m.id == item.model_id ? ' selected' : ''}>${m.model_name}</option>`);
                            });
                        }
                    }, 'json');
                }
            });

            /* Scroll to form */
            $('html, body').animate({ scrollTop: 0 }, 400);

        }, 'json');
    });

    /* ══════════════════════════════════════════════════════
       VERIFY
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.verifyBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Verify Indent?',
            text: 'This will mark the indent as verified and lock editing.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Verify!'
        }).then(r => {
            if (!r.isConfirmed) return;
            $.post('<?= APP_URL ?>indent/verifyIndent', { id }, function (res) {
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Verified!', text:res.message,
                        showConfirmButton:false, timer:1500 }).then(() => location.reload());
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }, 'json');
        });
    });

    /* ══════════════════════════════════════════════════════
       PASS
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.passBtn', function () {
        const id = $(this).data('id');
        $.get('<?= APP_URL ?>indent/getIndentById', { id }, function (res) {
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
    });

    $('#passForm').submit(function (e) {
        e.preventDefault();
        const id = $('#passModal').data('indent-id');
        $.ajax({
            url: '<?= APP_URL ?>indent/passIndent', type: 'POST',
            data: $(this).serialize() + '&id=' + id, dataType: 'json',
            success: function (res) {
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

    /* ══════════════════════════════════════════════════════
       ISSUE
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.issueBtn', function () {
        const id = $(this).data('id');
        $.get('<?= APP_URL ?>indent/getIndentById', { id }, function (res) {
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
    });

    $('#issueForm').submit(function (e) {
        e.preventDefault();
        const id = $('#issueModal').data('indent-id');
        $.ajax({
            url: '<?= APP_URL ?>indent/issueIndent', type: 'POST',
            data: $(this).serialize() + '&id=' + id, dataType: 'json',
            success: function (res) {
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

    /* ══════════════════════════════════════════════════════
       RECEIVE
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.receiveBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Mark as Received?', text: 'This completes the indent process.',
            icon: 'question', showCancelButton: true,
            confirmButtonColor: '#28a745', confirmButtonText: 'Yes, Received!'
        }).then(r => {
            if (!r.isConfirmed) return;
            $.post('<?= APP_URL ?>indent/receiveIndent', { id }, function (res) {
                if (res.success) {
                    Swal.fire({ icon:'success', title:'Received!', text:res.message,
                        showConfirmButton:false, timer:1500 }).then(() => location.reload());
                } else { Swal.fire('Error!', res.message, 'error'); }
            }, 'json');
        });
    });

    /* ══════════════════════════════════════════════════════
       DELETE
    ══════════════════════════════════════════════════════ */
    $(document).on('click', '.deleteIndentBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Indent?', text: "This cannot be undone!",
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Yes, Delete!'
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '<?= APP_URL ?>indent/crudData/deletion?id=' + id,
                type: 'POST', dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon:'success', title:'Deleted!', text:res.message,
                            showConfirmButton:false, timer:1500 }).then(() => {
                            $('#indentRow_' + id).fadeOut(400, function () { $(this).remove(); });
                        });
                    } else { Swal.fire('Error!', res.message, 'error'); }
                }
            });
        });
    });

});
</script>