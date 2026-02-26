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
                                <th style="width:140px">Group Item Name<span class="text-danger">*</span></th>
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
                    <th>Items</th>
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
                        <td><?= htmlspecialchars($row['indent_no']) ?></td>
                        <td><span class="badge bg-<?= $typeColor ?>"><?= $typeLabel ?></span></td>
                        <td><?= date('d.m.Y', strtotime($row['indent_date'])) ?></td>
                        <td><?= !empty($row['item_names']) ? $row['item_names'] : 'No Items'; ?>
                        </td>
                        <td><span class="badge bg-<?= $statusColor ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td><?= htmlspecialchars($row['created_by_name'] ?? '') ?></td>
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
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</div>
</div>

<!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT  
═══════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Data from server ──────────────────────────────────── */
    let itemsList  = <?= json_encode($items ?? []) ?>;
    let makesList  = <?= json_encode($makes ?? []) ?>;
    let groupsList = <?= json_encode($groups ?? []) ?>;

    let isEditMode  = false;
    let itemCounter = 0;

    const APP_URL = '<?= APP_URL ?>';

    /* ── Build one item row ──────────────────────────────── */
    function buildItemRow(idx, data) {
        data = data || {};

        // Build Item options
        let itemOpts = '<option value="">-- Select Item --</option>';
        itemsList.forEach(i => {
            itemOpts += `<option value="${i.id}"${data.item_id == i.id ? ' selected' : ''}>${i.item_name}</option>`;
        });

        // Build Group options
        let groupOpts = '<option value="">-- Select Group --</option>';
        groupsList.forEach(g => {
            groupOpts += `<option value="${g.id}"${data.group_id == g.id ? ' selected' : ''}>${g.group_name}</option>`;
        });

        // Build Make options
        let makeOpts = '<option value="">-- Select Make --</option>';
        makesList.forEach(m => {
            makeOpts += `<option value="${m.id}"${data.make_id == m.id ? ' selected' : ''}>${m.make_name}</option>`;
        });

        return `
        <tr class="item-row">
            <td>
                <input type="number" class="form-control form-control-sm"
                       name="items[${idx}][sl_no]" value="${data.sl_no || idx}" style="width:80px;min-width:80px">
                       <input type="hidden" name="items[${idx}][id]" value="${data.id || 0}">
            </td>
            <td>
                <select class="form-select form-select-sm group-select"
                        name="items[${idx}][group_id]" data-idx="${idx}">
                    ${groupOpts}
                </select>
                <small class="text-muted d-block mt-1">
                    <button type="button" class="btn-link text-primary add-new-group" data-idx="${idx}" style="padding:0; border:none; background:none; font-size:11px;">
                        <i class="ti ti-plus"></i> Add New
                    </button>
                </small>
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

        $.get(APP_URL + 'indent/getModelsByMake', { make_id: makeId }, function (res) {
            if (res.success) {
                res.data.forEach(m => {
                    $model.append(`<option value="${m.id}">${m.model_name}</option>`);
                });
            }
        }, 'json');
    });

    /* ── Add New Group Modal/Dialog ──────────────────────── */
    $(document).on('click', '.add-new-group', function (e) {
        e.preventDefault();
        const idx = $(this).data('idx');

        Swal.fire({
            title: 'Add New Group Item Name',
            input: 'text',
            inputLabel: 'Enter group name (e.g., Inspection Reports, Equipment, etc.)',
            inputPlaceholder: 'Type group name here...',
            showCancelButton: true,
            confirmButtonText: 'Add Group',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Group name is required!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const groupName = result.value;

                // Send to server to create new group
                $.ajax({
                    url: APP_URL + 'indent/addGroupItem',
                    type: 'POST',
                    data: { group_name: groupName },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            // Add to groupsList
                            groupsList.push({
                                id: res.data.id,
                                group_name: res.data.group_name,
                                group_code: res.data.group_code
                            });

                            // Update dropdown for current row
                            const $select = $(`.group-select[data-idx="${idx}"]`);
                            $select.append(
                                `<option value="${res.data.id}" selected>${res.data.group_name}</option>`
                            );

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Failed to add group', 'error');
                    }
                });
            }
        });
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
            ? APP_URL + 'indent/crudData/updation?id=' + editId
            : APP_URL + 'indent/crudData/insertion';

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

        $.get(APP_URL + 'indent/getIndentById', { id }, function (res) {
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
                    id                   : item.id,
                    sl_no                : item.sl_no,
                    group_id             : item.group_id,
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
                    day_book_volume      : item.day_book_volume
                }));
                $('#itemsTableBody').append($row);

                // Trigger model load if make_id exists
                if (item.make_id) {
                    $.get(APP_URL + 'indent/getModelsByMake', { make_id: item.make_id }, function (res) {
                        if (res.success) {
                            const $model = $(`.model-select[data-idx="${itemCounter}"]`);
                            res.data.forEach(m => {
                                $model.append(`<option value="${m.id}"${m.id == item.model_id ? ' selected' : ''}>${m.model_name}</option>`);
                            });
                        }
                    }, 'json');
                }
            });

            $('html, body').animate({ scrollTop: 0 }, 300);
        }, 'json');
    });

    /* ── Delete Indent ───────────────────────────────────── */
    $(document).on('click', '.deleteIndentBtn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Delete Indent?',
            text: 'Are you sure? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: APP_URL + 'indent/crudData/deletion?id=' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

    /* ── Verify Indent ───────────────────────────────────── */
    $(document).on('click', '.verifyIndentBtn', function () {
        const id = $(this).data('id');
        $.post(APP_URL + 'indent/verifyIndent', { id }, function (res) {
            if (res.success) {
                Swal.fire('Success!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    /* ── Pass Indent ─────────────────────────────────────── */
    $(document).on('click', '.passIndentBtn', function () {
        const id = $(this).data('id');
        $.post(APP_URL + 'indent/passIndent', { id }, function (res) {
            if (res.success) {
                Swal.fire('Success!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    /* ── Issue Indent ────────────────────────────────────── */
    $(document).on('click', '.issueIndentBtn', function () {
        const id = $(this).data('id');
        $.post(APP_URL + 'indent/issueIndent', { id }, function (res) {
            if (res.success) {
                Swal.fire('Success!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    /* ── Receive Indent ──────────────────────────────────── */
    $(document).on('click', '.receiveIndentBtn', function () {
        const id = $(this).data('id');
        $.post(APP_URL + 'indent/receiveIndent', { id }, function (res) {
            if (res.success) {
                Swal.fire('Success!', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    // DataTable initialization
    let table;
    if ($.fn.dataTable && document.getElementById('indent-datatable')) {
        table = $('#indent-datatable').DataTable({
            paging      : true,
            pageLength  : 10,
            ordering    : true,
            searching   : true,
            responsive  : true,
            columnDefs  : [
                { orderable: false, targets: [-1, -2] }
            ]
        });
    }
});
</script>