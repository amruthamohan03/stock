{{!-- FILE: app/views/stock/kfc_form13.php --}}
<div class="page-content">
<div class="page-container">
<div class="row">
<div class="col-12">
<div class="card">

    <!-- ═══ Card Header ══════════════════════════════════════ -->
    <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="header-title mb-0">
                <i class="ti ti-file-invoice me-1"></i> KFC Form 13 — Annual Indent for Stores
            </h4>
            <small class="text-muted">
                <?= htmlspecialchars($inst_name) ?>
                <?php if (!empty($dept_name)): ?> &mdash; <?= htmlspecialchars($dept_name) ?><?php endif; ?>
            </small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kfcFormModal"
                onclick="openCreateModal()">
            <i class="ti ti-plus"></i> New KFC Form 13
        </button>
    </div>

    <!-- ═══ Table ════════════════════════════════════════════ -->
    <div class="card-body">
        <div class="table-responsive">
            <table id="kfcTable" class="table table-hover table-bordered align-middle nowrap w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Form / Indent No</th>
                        <th>Financial Year</th>
                        <th>Indent Date</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Prepared By</th>
                        <th class="no-sort">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sl = 1; foreach ($result as $row): ?>
                    <?php
                        $statusColors = [
                            'DRAFT'     => 'secondary',
                            'SUBMITTED' => 'primary',
                            'APPROVED'  => 'success',
                            'REJECTED'  => 'danger',
                            'CLOSED'    => 'dark',
                        ];
                        $sc = $statusColors[$row['status']] ?? 'secondary';
                        $typeLabels = ['GENERAL'=>'General','DIETARY'=>'Dietary','SPC'=>'SPC'];
                        $tl = $typeLabels[$row['indent_type']] ?? $row['indent_type'];
                    ?>
                    <tr>
                        <td><?= $sl++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['form_no']) ?></td>
                        <td><?= htmlspecialchars($row['financial_year']) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['indent_date'])) ?></td>
                        <td><span class="badge bg-secondary"><?= $tl ?></span></td>
                        <td class="text-center"><?= (int)$row['item_count'] ?></td>
                        <td><span class="badge bg-<?= $sc ?>"><?= $row['status'] ?></span></td>
                        <td><?= htmlspecialchars($row['created_by_name'] ?? '') ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="<?= APP_URL ?>kfcform13/viewForm/<?= $row['id'] ?>"
                                   class="btn btn-xs btn-outline-info" title="View">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <?php if ($row['status'] === 'DRAFT'): ?>
                                <button class="btn btn-xs btn-outline-warning"
                                        onclick="editForm(<?= $row['id'] ?>)" title="Edit">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-primary"
                                        onclick="submitForm(<?= $row['id'] ?>)" title="Submit">
                                    <i class="ti ti-send"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($row['status'] === 'SUBMITTED'): ?>
                                <button class="btn btn-xs btn-outline-success"
                                        onclick="approveForm(<?= $row['id'] ?>)" title="Approve">
                                    <i class="ti ti-check"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-danger"
                                        onclick="rejectForm(<?= $row['id'] ?>)" title="Reject">
                                    <i class="ti ti-x"></i>
                                </button>
                                <?php endif; ?>
                                <a href="<?= APP_URL ?>kfcform13/exportPdf/<?= $row['id'] ?>"
                                   target="_blank" class="btn btn-xs btn-outline-dark" title="Export PDF">
                                    <i class="ti ti-file-type-pdf"></i>
                                </a>
                                <?php if ($row['status'] === 'DRAFT'): ?>
                                <button class="btn btn-xs btn-outline-danger"
                                        onclick="deleteForm(<?= $row['id'] ?>)" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div><!-- /card-body -->
</div><!-- /card -->
</div>
</div>
</div>
</div><!-- /page-content -->


<!-- ═══════════════════════════════════════════════════════════
     CREATE / EDIT MODAL
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="kfcFormModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="kfcModalTitle">
                    <i class="ti ti-file-invoice me-1"></i> New KFC Form 13
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="kfc_edit_id" value="">

                <!-- ── Section A: Header ── -->
                <h6 class="text-primary fw-bold border-bottom pb-1 mb-3">
                    <i class="ti ti-id"></i> Indent Header
                </h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm">Indent / Form No <span class="text-danger">*</span></label>
                        <input type="text" id="form_no" class="form-control form-control-sm"
                               placeholder="e.g. IND/2025-26/001" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm">Financial Year <span class="text-danger">*</span></label>
                        <input type="text" id="financial_year" class="form-control form-control-sm"
                               placeholder="e.g. 2025-2026" maxlength="9" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm">Indent Date <span class="text-danger">*</span></label>
                        <input type="date" id="indent_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-label-sm">Indent Type</label>
                        <select id="indent_type" class="form-select form-select-sm">
                            <option value="GENERAL">General</option>
                            <option value="DIETARY">Dietary</option>
                            <option value="SPC">SPC (Stores Purchase Committee)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm">Funds in Budget?</label>
                        <select id="funds_provided" class="form-select form-select-sm">
                            <option value="Y">Yes</option>
                            <option value="N">No</option>
                            <option value="PARTIAL">Partial</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Reference to Previous Correspondence</label>
                        <input type="text" id="prev_correspondence_ref" class="form-control form-control-sm"
                               placeholder="Letter No / Date (leave blank if none)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-label-sm">Funds Remark</label>
                        <input type="text" id="funds_remark" class="form-control form-control-sm"
                               placeholder="Budget head / remark (if any)">
                    </div>
                </div>

                <!-- ── Section B: Delivery ── -->
                <h6 class="text-primary fw-bold border-bottom pb-1 mb-3">
                    <i class="ti ti-truck"></i> Delivery &amp; Inspection
                </h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Delivery Address</label>
                        <textarea id="delivery_address" class="form-control form-control-sm" rows="2"
                                  placeholder="Full delivery address"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Nearest Railway Station</label>
                        <input type="text" id="nearest_railway_station" class="form-control form-control-sm"
                               placeholder="Railway station name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Place of Delivery</label>
                        <input type="text" id="delivery_place" class="form-control form-control-sm"
                               placeholder="Place where stores are to be delivered">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Inspecting Officer Name</label>
                        <input type="text" id="inspecting_officer_name" class="form-control form-control-sm"
                               placeholder="Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Designation</label>
                        <input type="text" id="inspecting_officer_desig" class="form-control form-control-sm"
                               placeholder="Designation">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Special Instructions</label>
                        <textarea id="special_instructions" class="form-control form-control-sm" rows="2"
                                  placeholder="Any urgent/special notes"></textarea>
                    </div>
                </div>

                <!-- ── Section C: Sanction ── -->
                <h6 class="text-primary fw-bold border-bottom pb-1 mb-3">
                    <i class="ti ti-stamp"></i> Sanction Details
                </h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Sanctioning Authority</label>
                        <input type="text" id="sanction_authority" class="form-control form-control-sm"
                               placeholder="Name / Designation">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Sanction Order No</label>
                        <input type="text" id="sanction_order_no" class="form-control form-control-sm"
                               placeholder="Order No">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label form-label-sm">Sanction Order Date</label>
                        <input type="date" id="sanction_order_date" class="form-control form-control-sm">
                    </div>
                </div>

                <!-- ── Section D: Line Items ── -->
                <h6 class="text-primary fw-bold border-bottom pb-1 mb-2">
                    <i class="ti ti-list"></i> Indent Items
                    <button type="button" class="btn btn-xs btn-success float-end" onclick="addItemRow()">
                        <i class="ti ti-plus"></i> Add Item
                    </button>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" id="itemsTable" style="font-size:12px;">
                        <thead class="table-secondary">
                            <tr>
                                <th style="width:34px">#</th>
                                <th>Group</th>
                                <th>Article / Description <span class="text-danger">*</span></th>
                                <th>Trade Name</th>
                                <th>Size / Spec</th>
                                <th>Unit</th>
                                <th>Stock on Hand</th>
                                <th>Purchases This Year</th>
                                <th>Qty Required <span class="text-danger">*</span></th>
                                <th>By Weight</th>
                                <th>By Number</th>
                                <th>By Volume</th>
                                <th>Last Rate</th>
                                <th>Est. Cost</th>
                                <th>Last Supplier</th>
                                <th>Purpose</th>
                                <th style="width:36px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- rows added dynamically -->
                        </tbody>
                    </table>
                </div>
            </div><!-- /modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveForm()">
                    <i class="ti ti-device-floppy"></i> Save Form 13
                </button>
            </div>
        </div>
    </div>
</div>

<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
<!-- ═══════════════════════════════════════════════════════════
     REJECT MODAL
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Form 13</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject_form_id">
                <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" class="form-control" rows="3"
                          placeholder="Specify the reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger btn-sm" onclick="submitReject()">
                    <i class="ti ti-x"></i> Confirm Reject
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
const APP_URL   = '<?= APP_URL ?>';
const GROUPS    = <?= json_encode(array_column($groups, 'group_name', 'id')) ?>;

/* ── DataTable init ──────────────────────────────────────── */
$(function () {
    $('#kfcTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [{ targets: [8], orderable: false }],
        responsive: true,
        language: { emptyTable: 'No KFC Form 13 records found.' }
    });
});

/* ── Build group <options> ───────────────────────────────── */
function buildGroupOptions(selVal = '') {
    let html = '<option value="">-- Group --</option>';
    <?php foreach ($groups as $g): ?>
    html += `<option value="<?= $g['id'] ?>" ${selVal == <?= $g['id'] ?> ? 'selected' : ''}><?= addslashes($g['group_name']) ?></option>`;
    <?php endforeach; ?>
    return html;
}

/* ── Add a row to items table ────────────────────────────── */
let rowIdx = 0;
function addItemRow(d = {}) {
    rowIdx++;
    const i = rowIdx;
    const row = `
    <tr id="irow_${i}">
        <td class="text-center fw-bold">${i}</td>
        <td><select name="items[${i}][group_id]" class="form-select form-select-sm" style="min-width:90px">${buildGroupOptions(d.group_id||'')}</select></td>
        <td><input  name="items[${i}][article_description]" class="form-control form-control-sm" style="min-width:140px" value="${esc(d.article_description||d.item_name||'')}" required></td>
        <td><input  name="items[${i}][trade_name]"          class="form-control form-control-sm" style="min-width:90px"  value="${esc(d.trade_name||'')}"></td>
        <td><input  name="items[${i}][size_spec]"           class="form-control form-control-sm" style="min-width:80px"  value="${esc(d.size_spec||'')}"></td>
        <td><input  name="items[${i}][unit]"                class="form-control form-control-sm" style="min-width:60px"  value="${esc(d.unit||'')}"></td>
        <td><input  name="items[${i}][stock_on_hand]"       class="form-control form-control-sm text-end" style="width:70px" type="number" step="0.001" value="${d.stock_on_hand||0}"></td>
        <td><input  name="items[${i}][purchases_this_year]" class="form-control form-control-sm text-end" style="width:70px" type="number" step="0.001" value="${d.purchases_this_year||0}"></td>
        <td><input  name="items[${i}][qty_required]"        class="form-control form-control-sm text-end qty-req" style="width:70px" type="number" step="0.001" value="${d.qty_required||''}" required></td>
        <td><input  name="items[${i}][qty_required_by_weight]" class="form-control form-control-sm text-end" style="width:65px" type="number" step="0.001" value="${d.qty_required_by_weight||''}"></td>
        <td><input  name="items[${i}][qty_required_by_number]" class="form-control form-control-sm text-end" style="width:65px" type="number" step="0.001" value="${d.qty_required_by_number||''}"></td>
        <td><input  name="items[${i}][qty_required_by_volume]" class="form-control form-control-sm text-end" style="width:65px" type="number" step="0.001" value="${d.qty_required_by_volume||''}"></td>
        <td><input  name="items[${i}][last_purchase_rate]"  class="form-control form-control-sm text-end" style="width:80px" type="number" step="0.01" value="${d.last_purchase_rate||''}"></td>
        <td><input  name="items[${i}][estimated_cost]"      class="form-control form-control-sm text-end" style="width:80px" type="number" step="0.01" value="${d.estimated_cost||''}"></td>
        <td><input  name="items[${i}][last_supplier_name]"  class="form-control form-control-sm" style="min-width:110px" value="${esc(d.last_supplier_name||'')}"></td>
        <td><input  name="items[${i}][purpose]"             class="form-control form-control-sm" style="min-width:110px" value="${esc(d.purpose||'')}"></td>
        <td><button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRow(${i})"><i class="ti ti-trash"></i></button></td>
    </tr>`;
    $('#itemsBody').append(row);
}

function removeRow(i) { $(`#irow_${i}`).remove(); }
function esc(s)        { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ── Open create modal ───────────────────────────────────── */
function openCreateModal() {
    $('#kfcModalTitle').html('<i class="ti ti-file-invoice me-1"></i> New KFC Form 13');
    $('#kfcFormModal input, #kfcFormModal textarea, #kfcFormModal select').val('');
    $('#funds_provided').val('N');
    $('#indent_type').val('GENERAL');
    $('#kfc_edit_id').val('');
    $('#itemsBody').empty();
    rowIdx = 0;
    addItemRow(); // start with 1 blank row
}

/* ── Load for edit ───────────────────────────────────────── */
function editForm(id) {
    $.get(APP_URL + 'kfcform13/getFormItems', { id }, function(res) {
        if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }
        const f = res.form;
        $('#kfcModalTitle').html('<i class="ti ti-pencil me-1"></i> Edit KFC Form 13 — ' + f.form_no);
        $('#kfc_edit_id').val(id);
        $('#form_no').val(f.form_no);
        $('#financial_year').val(f.financial_year);
        $('#indent_date').val(f.indent_date);
        $('#indent_type').val(f.indent_type);
        $('#funds_provided').val(f.funds_provided);
        $('#prev_correspondence_ref').val(f.prev_correspondence_ref || '');
        $('#funds_remark').val(f.funds_remark || '');
        $('#delivery_address').val(f.delivery_address || '');
        $('#nearest_railway_station').val(f.nearest_railway_station || '');
        $('#delivery_place').val(f.delivery_place || '');
        $('#inspecting_officer_name').val(f.inspecting_officer_name || '');
        $('#inspecting_officer_desig').val(f.inspecting_officer_desig || '');
        $('#special_instructions').val(f.special_instructions || '');
        $('#sanction_authority').val(f.sanction_authority || '');
        $('#sanction_order_no').val(f.sanction_order_no || '');
        $('#sanction_order_date').val(f.sanction_order_date || '');

        $('#itemsBody').empty();
        rowIdx = 0;
        res.items.forEach(item => addItemRow(item));
        if (res.items.length === 0) addItemRow();

        new bootstrap.Modal('#kfcFormModal').show();
    }, 'json');
}

/* ── Save (insert / update) ──────────────────────────────── */
function saveForm() {
    const id  = $('#kfc_edit_id').val();
    const url = APP_URL + 'kfcform13/crudData/' + (id ? 'updation' : 'insertion');

    // Collect form data
    const fd = new FormData();
    if (id) fd.append('id', id);
    const fields = ['form_no','financial_year','indent_date','indent_type','funds_provided',
        'prev_correspondence_ref','funds_remark','delivery_address','nearest_railway_station',
        'delivery_place','inspecting_officer_name','inspecting_officer_desig','special_instructions',
        'sanction_authority','sanction_order_no','sanction_order_date'];
    fields.forEach(f => fd.append(f, $(`#${f}`).val() || ''));

    // Items
    $('#itemsBody tr').each(function () {
        $(this).find('[name]').each(function () {
            fd.append($(this).attr('name'), $(this).val() || '');
        });
    });

    // Validate
    if (!$('#form_no').val().trim()) { Swal.fire('Required','Indent / Form No is required','warning'); return; }
    if (!$('#financial_year').val().trim()) { Swal.fire('Required','Financial Year is required','warning'); return; }
    if (!$('#indent_date').val()) { Swal.fire('Required','Indent Date is required','warning'); return; }

    $.ajax({ url, type:'POST', data:fd, contentType:false, processData:false, dataType:'json',
        success: function(res) {
            if (res.success) {
                Swal.fire({ icon:'success', title:'Saved!', text:res.message,
                    showConfirmButton:false, timer:1500 }).then(() => location.reload());
                bootstrap.Modal.getInstance('#kfcFormModal').hide();
            } else { Swal.fire('Error!', res.message, 'error'); }
        }
    });
}

/* ── Workflow helpers ────────────────────────────────────── */
function submitForm(id) {
    Swal.fire({ title:'Submit Form 13?', text:'It will be sent for approval and locked from editing.',
        icon:'question', showCancelButton:true, confirmButtonColor:'#0d6efd',
        confirmButtonText:'Yes, Submit!'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'kfcform13/changeStatus', { id, action:'submit' }, function(res) {
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
        $.post(APP_URL + 'kfcform13/changeStatus', { id, action:'approve' }, function(res) {
            if (res.success) { Swal.fire({icon:'success',title:'Approved!',text:res.message,
                showConfirmButton:false,timer:1500}).then(()=>location.reload()); }
            else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}

function rejectForm(id) {
    $('#reject_form_id').val(id);
    $('#rejection_reason').val('');
    new bootstrap.Modal('#rejectModal').show();
}

function submitReject() {
    const id     = $('#reject_form_id').val();
    const reason = $('#rejection_reason').val().trim();
    if (!reason) { Swal.fire('Required','Please enter a reason for rejection','warning'); return; }

    $.post(APP_URL + 'kfcform13/changeStatus',
        { id, action:'reject', rejection_reason:reason },
        function(res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#rejectModal').hide();
                Swal.fire({icon:'success',title:'Rejected!',text:res.message,
                    showConfirmButton:false,timer:1500}).then(()=>location.reload());
            } else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
}

function deleteForm(id) {
    Swal.fire({ title:'Delete this Form 13?', text:'This action cannot be undone.',
        icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545',
        confirmButtonText:'Yes, Delete!'
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post(APP_URL + 'kfcform13/crudData/deletion', { id }, function(res) {
            if (res.success) { Swal.fire({icon:'success',title:'Deleted!',text:res.message,
                showConfirmButton:false,timer:1300}).then(()=>location.reload()); }
            else { Swal.fire('Error!', res.message, 'error'); }
        }, 'json');
    });
}
</script>