<div class="page-content">
    <div class="page-container">

        <!-- ══════════════════════════════════════════════════════════
             CREATE FORM CARD
        ═══════════════════════════════════════════════════════════ -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">K.F.C. Form 16 — Create Day Book Entry</h4>
                        <button type="button" class="btn btn-sm btn-secondary"
                                onclick="$('#daybookForm')[0].reset(); $('#itemsTableBody').html(''); itemCounter = 0;">
                            <i class="mdi mdi-refresh"></i> Reset
                        </button>
                    </div>

                    <div class="card-body">
                        <form id="daybookForm" method="post">

                            <!-- ── Row 1: Register Info ────────────── -->
                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Page No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="page_no" name="page_no"
                                           placeholder="e.g. 132" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Stockbook Type <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="stockbook_type_id" name="stockbook_type_id" required>
                                        <option value="">-- Select Stockbook --</option>
                                        <?php if (!empty($stockbookTypes)): ?>
                                            <?php foreach ($stockbookTypes as $sbt): ?>
                                                <option value="<?= $sbt['id'] ?>">
                                                    <?= htmlspecialchars($sbt['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="transaction_type" name="transaction_type" required>
                                        <option value="">-- Select --</option>
                                        <option value="RECEIPT">📥 Receipt</option>
                                        <option value="ISSUE">📤 Issue</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Class</label>
                                    <input type="text" class="form-control" name="class" placeholder="Class">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Unit</label>
                                    <input type="text" class="form-control" name="unit_label" placeholder="Unit">
                                </div>
                            </div>

                            <!-- ── Row 2: Document Details ────────── -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">By Whom Received / To Whom Issued <span class="text-danger">*</span></label>
                                    <select class="form-select select2" name="service_provider_id" id="service_provider_id" required>
                                        <option value="">-- Select Provider / Supplier --</option>
                                        <?php if (!empty($serviceProviders)): ?>
                                            <?php foreach ($serviceProviders as $sp): ?>
                                                <option value="<?= $sp['id'] ?>">
                                                    <?= htmlspecialchars($sp['provider_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="document_date" name="document_date" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Receipt / Issue Order No</label>
                                    <input type="text" class="form-control" name="receipt_order_no"
                                           placeholder="e.g. GraNkm/261/2025-P">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Invoice / Challan Ref</label>
                                    <input type="text" class="form-control" name="invoice_ref"
                                           placeholder="Invoice or Challan No">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Indent No</label>
                                    <input type="text" class="form-control" name="indent_no"
                                           placeholder="e.g. Tkp/1432/25-26">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Indent Date</label>
                                    <input type="date" class="form-control" name="indent_date">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">CR Voucher Ref</label>
                                    <input type="text" class="form-control" name="cr_voucher_ref"
                                           placeholder="e.g. NCR Vol II p.179">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Verifier Initials</label>
                                    <input type="text" class="form-control" name="verifier_initials"
                                           placeholder="Initials">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Remarks</label>
                                    <input type="text" class="form-control" name="remarks"
                                           placeholder="e.g. Issued as per Indent No. 28">
                                </div>
                            </div>

                            <!-- ── Item Lines ─────────────────────── -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-2">Item Lines — Quantities &amp; Values</h5>
                                    <div class="table-responsive" style="overflow-x:auto;">
                                        <table class="table table-bordered table-sm align-middle" id="itemsTable"
                                               style="min-width:1800px;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th rowspan="3" style="width:42px;text-align:center;">Sl</th>
                                                    <th rowspan="3" style="min-width:180px;">Item <span class="text-danger">*</span></th>
                                                    <th rowspan="3" style="min-width:130px;">Item Type</th>
                                                    <th rowspan="3" style="min-width:130px;">Category</th>
                                                    <th rowspan="3" style="min-width:90px;">Unit</th>
                                                    <th colspan="6" class="text-center">Quantities</th>
                                                    <th colspan="8" class="text-center">Value (Rs.)</th>
                                                    <th rowspan="3" style="min-width:80px;">Verifier</th>
                                                    <th rowspan="3" style="width:42px;"></th>
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
                                                    <th style="min-width:100px;">No.</th>
                                                    <th style="min-width:100px;">Wt/Msr</th>
                                                    <th style="min-width:100px;">No.</th>
                                                    <th style="min-width:100px;">Wt/Msr</th>
                                                    <th style="min-width:100px;">No.</th>
                                                    <th style="min-width:100px;">Wt/Msr</th>
                                                    <th style="min-width:110px;">Rate</th>
                                                    <th style="min-width:120px;">Rs.</th>
                                                    <th style="min-width:80px;">P.</th>
                                                    <th style="min-width:110px;">Rate</th>
                                                    <th style="min-width:120px;">Rs.</th>
                                                    <th style="min-width:110px;">Rate</th>
                                                    <th style="min-width:120px;">Rs.</th>
                                                    <th style="min-width:80px;">P.</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTableBody">
                                                <!-- rows added dynamically -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success mt-2" id="addItemBtn">
                                        <i class="mdi mdi-plus"></i> Add Item
                                    </button>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Entry
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             LIST TABLE CARD
        ═══════════════════════════════════════════════════════════ -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title">Day Book Entries</h4>
                    </div>

                    <div class="card-body">
                        <table id="daybook-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Page No</th>
                                    <th>Stockbook</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>By / To (Provider)</th>
                                    <th>Order / Ref No</th>
                                    <th>Indent No</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr id="daybookRow_<?= $row['id'] ?>">
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['page_no']) ?></td>
                                            <td><?= htmlspecialchars($row['stockbook_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php $typeColor = ($row['transaction_type'] == 'RECEIPT') ? 'success' : 'danger'; ?>
                                                <span class="badge bg-<?= $typeColor ?>">
                                                    <?= $row['transaction_type'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d-m-Y', strtotime($row['document_date'])) ?></td>
                                            <td><?= htmlspecialchars($row['provider_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['receipt_order_no'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['indent_no'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['created_by_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <a href="<?= APP_URL ?>daybook/viewDayBook/<?= $row['id'] ?>"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-warning editDaybookBtn"
                                                        data-id="<?= $row['id'] ?>" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger deleteDaybookBtn"
                                                        data-id="<?= $row['id'] ?>" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No entries found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     EDIT MODAL
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Day Book Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <input type="hidden" id="edit_id" name="edit_id">
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Page No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="page_no" id="edit_page_no" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stockbook Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="stockbook_type_id" id="edit_stockbook_type_id" required>
                                <option value="">-- Select --</option>
                                <?php if (!empty($stockbookTypes)): ?>
                                    <?php foreach ($stockbookTypes as $sbt): ?>
                                        <option value="<?= $sbt['id'] ?>"><?= htmlspecialchars($sbt['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="transaction_type" id="edit_transaction_type" required>
                                <option value="">-- Select --</option>
                                <option value="RECEIPT">📥 Receipt</option>
                                <option value="ISSUE">📤 Issue</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Class</label>
                            <input type="text" class="form-control" name="class" id="edit_class">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" name="unit_label" id="edit_unit_label">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">By Whom / To Whom <span class="text-danger">*</span></label>
                            <select class="form-select" name="service_provider_id" id="edit_service_provider_id" required>
                                <option value="">-- Select Provider --</option>
                                <?php if (!empty($serviceProviders)): ?>
                                    <?php foreach ($serviceProviders as $sp): ?>
                                        <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['provider_name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="document_date" id="edit_doc_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Order No</label>
                            <input type="text" class="form-control" name="receipt_order_no" id="edit_order_no">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Invoice Ref</label>
                            <input type="text" class="form-control" name="invoice_ref" id="edit_invoice_ref">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Indent No</label>
                            <input type="text" class="form-control" name="indent_no" id="edit_indent_no">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Indent Date</label>
                            <input type="date" class="form-control" name="indent_date" id="edit_indent_date">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">CR Voucher Ref</label>
                            <input type="text" class="form-control" name="cr_voucher_ref" id="edit_cr_voucher">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Verifier Initials</label>
                            <input type="text" class="form-control" name="verifier_initials" id="edit_verifier">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Remarks</label>
                            <input type="text" class="form-control" name="remarks" id="edit_remarks">
                        </div>
                    </div>

                    <!-- Edit items table -->
                    <h6 class="mb-2">Item Lines</h6>
                    <div class="table-responsive" style="overflow-x:auto;">
                        <table class="table table-bordered table-sm align-middle" id="editItemsTable"
                               style="min-width:1800px;">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="3" style="width:42px;text-align:center;">Sl</th>
                                    <th rowspan="3" style="min-width:180px;">Item *</th>
                                    <th rowspan="3" style="min-width:130px;">Item Type</th>
                                    <th rowspan="3" style="min-width:130px;">Category</th>
                                    <th rowspan="3" style="min-width:90px;">Unit</th>
                                    <th colspan="6" class="text-center">Quantities</th>
                                    <th colspan="8" class="text-center">Value (Rs.)</th>
                                    <th rowspan="3" style="min-width:80px;">Verifier</th>
                                    <th rowspan="3" style="width:42px;"></th>
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
                                    <th style="min-width:100px;">No.</th><th style="min-width:100px;">Wt/Msr</th>
                                    <th style="min-width:100px;">No.</th><th style="min-width:100px;">Wt/Msr</th>
                                    <th style="min-width:100px;">No.</th><th style="min-width:100px;">Wt/Msr</th>
                                    <th style="min-width:110px;">Rate</th><th style="min-width:120px;">Rs.</th><th style="min-width:80px;">P.</th>
                                    <th style="min-width:110px;">Rate</th><th style="min-width:120px;">Rs.</th>
                                    <th style="min-width:110px;">Rate</th><th style="min-width:120px;">Rs.</th><th style="min-width:80px;">P.</th>
                                </tr>
                            </thead>
                            <tbody id="editItemsTableBody"></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="addEditItemBtn">
                        <i class="mdi mdi-plus"></i> Add Item
                    </button>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="mdi mdi-content-save"></i> Update Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let itemCounter     = 0;
    let editItemCounter = 0;

    // ── PHP data passed to JS ─────────────────────────────────
    const allItems      = <?= json_encode($items          ?? []) ?>;
    const itemTypes     = <?= json_encode($itemTypes      ?? []) ?>;
    const itemCategories= <?= json_encode($itemCategories ?? []) ?>;
    const units         = <?= json_encode($units          ?? []) ?>;

    // ── Init DataTable ────────────────────────────────────────
    if (!$.fn.DataTable.isDataTable('#daybook-datatable')) {
        $('#daybook-datatable').DataTable({
            pageLength : 10,
            ordering   : true,
            searching  : true,
            order      : [[0, 'DESC']]
        });
    }

    // ── Init Select2 ──────────────────────────────────────────
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // Set today's date
    $('#document_date').val(new Date().toISOString().split('T')[0]);

    // ── Option builders ───────────────────────────────────────
    function buildItemOptions(selectedId = '') {
        let o = '<option value="">-- Select Item --</option>';
        allItems.forEach(i => {
            o += `<option value="${i.id}" ${i.id == selectedId ? 'selected' : ''}>${i.item_name}</option>`;
        });
        return o;
    }

    function buildTypeOptions(selectedId = '') {
        let o = '<option value="">-- Item Type --</option>';
        itemTypes.forEach(t => {
            o += `<option value="${t.id}" ${t.id == selectedId ? 'selected' : ''}>${t.name}</option>`;
        });
        return o;
    }

    function buildCatOptions(selectedId = '', filterTypeId = '') {
        let o = '<option value="">-- Category --</option>';
        itemCategories.forEach(c => {
            if (filterTypeId && c.item_type_id != filterTypeId) return;
            o += `<option value="${c.id}" data-type="${c.item_type_id}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`;
        });
        return o;
    }

    function buildUnitOptions(selectedId = '') {
        let o = '<option value="">--</option>';
        units.forEach(u => {
            o += `<option value="${u.id}" ${u.id == selectedId ? 'selected' : ''}>${u.name}</option>`;
        });
        return o;
    }

    // ── Build one item row ────────────────────────────────────
    function buildItemRow(prefix, idx, d = {}) {
        const sl = idx + 1;
        return `
        <tr class="item-row">
          <td class="text-center fw-bold text-secondary">${sl}
            <input type="hidden" name="${prefix}[${idx}][sl_no]" value="${sl}">
          </td>

          <td>
            <select class="form-select form-select-sm" name="${prefix}[${idx}][item_id]" required>
              ${buildItemOptions(d.item_id || '')}
            </select>
          </td>

          <td>
            <select class="form-select form-select-sm item-type-sel"
                    name="${prefix}[${idx}][item_type_id]"
                    data-idx="${idx}" data-prefix="${prefix}">
              ${buildTypeOptions(d.item_type_id || '')}
            </select>
          </td>

          <td>
            <select class="form-select form-select-sm item-cat-sel"
                    name="${prefix}[${idx}][item_category_id]"
                    data-idx="${idx}">
              ${buildCatOptions(d.item_category_id || '', d.item_type_id || '')}
            </select>
          </td>

          <td>
            <select class="form-select form-select-sm" name="${prefix}[${idx}][unit_id]">
              ${buildUnitOptions(d.unit_id || '')}
            </select>
          </td>

          <!-- Quantities: Receipts -->
          <td><input type="number" step="0.001" class="form-control form-control-sm rqn text-end"
                     name="${prefix}[${idx}][receipt_qty_number]" value="${d.receipt_qty_number||''}" placeholder="0"></td>
          <td><input type="number" step="0.001" class="form-control form-control-sm rqw text-end"
                     name="${prefix}[${idx}][receipt_qty_weight]" value="${d.receipt_qty_weight||''}" placeholder="0"></td>

          <!-- Quantities: Issues -->
          <td><input type="number" step="0.001" class="form-control form-control-sm iqn text-end"
                     name="${prefix}[${idx}][issue_qty_number]" value="${d.issue_qty_number||''}" placeholder="0"></td>
          <td><input type="number" step="0.001" class="form-control form-control-sm iqw text-end"
                     name="${prefix}[${idx}][issue_qty_weight]" value="${d.issue_qty_weight||''}" placeholder="0"></td>

          <!-- Quantities: Balance (auto) -->
          <td><input type="number" step="0.001" class="form-control form-control-sm bqn text-end"
                     name="${prefix}[${idx}][balance_qty_number]" value="${d.balance_qty_number||''}"
                     placeholder="auto" readonly style="background:#f8f9fa;"></td>
          <td><input type="number" step="0.001" class="form-control form-control-sm bqw text-end"
                     name="${prefix}[${idx}][balance_qty_weight]" value="${d.balance_qty_weight||''}"
                     placeholder="auto" readonly style="background:#f8f9fa;"></td>

          <!-- Value: Receipts -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
                     name="${prefix}[${idx}][receipt_rate]" value="${d.receipt_rate||''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm ramt text-end"
                     name="${prefix}[${idx}][receipt_amount_rs]" value="${d.receipt_amount_rs||''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
                     name="${prefix}[${idx}][receipt_amount_ps]" value="${d.receipt_amount_ps||''}" placeholder="0"></td>

          <!-- Value: Issues -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
                     name="${prefix}[${idx}][issue_rate]" value="${d.issue_rate||''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm iamt text-end"
                     name="${prefix}[${idx}][issue_amount_rs]" value="${d.issue_amount_rs||''}" placeholder="0.00"></td>

          <!-- Value: Balance (auto) -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
                     name="${prefix}[${idx}][balance_rate]" value="${d.balance_rate||''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm bamt text-end"
                     name="${prefix}[${idx}][balance_amount_rs]" value="${d.balance_amount_rs||''}"
                     placeholder="auto" readonly style="background:#f8f9fa;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
                     name="${prefix}[${idx}][balance_amount_ps]" value="${d.balance_amount_ps||''}" placeholder="0"></td>

          <td><input type="text" class="form-control form-control-sm"
                     name="${prefix}[${idx}][value_verifier]" value="${d.value_verifier||''}" placeholder="Initials"></td>

          <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-item">
              <i class="ti ti-trash"></i>
            </button>
          </td>
        </tr>`;
    }

    // ── Add item row (create form) ────────────────────────────
    $('#addItemBtn').click(function () {
        $('#itemsTableBody').append(buildItemRow('items', itemCounter));
        itemCounter++;
    });

    // ── Add item row (edit modal) ─────────────────────────────
    $('#addEditItemBtn').click(function () {
        $('#editItemsTableBody').append(buildItemRow('items', editItemCounter));
        editItemCounter++;
    });

    // ── Remove row ────────────────────────────────────────────
    $(document).on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
    });

    // ── Item type → filter categories cascade ─────────────────
    $(document).on('change', '.item-type-sel', function () {
        const typeId = $(this).val();
        const idx    = $(this).data('idx');
        $(`.item-cat-sel[data-idx="${idx}"]`).html(buildCatOptions('', typeId));
    });

    // ── Auto-compute balance qty and balance amount ───────────
    $(document).on('input', '.rqn,.iqn,.rqw,.iqw,.ramt,.iamt', function () {
        const row  = $(this).closest('tr');
        const rqn  = parseFloat(row.find('.rqn').val())  || 0;
        const iqn  = parseFloat(row.find('.iqn').val())  || 0;
        const rqw  = parseFloat(row.find('.rqw').val())  || 0;
        const iqw  = parseFloat(row.find('.iqw').val())  || 0;
        const ramt = parseFloat(row.find('.ramt').val()) || 0;
        const iamt = parseFloat(row.find('.iamt').val()) || 0;
        row.find('.bqn').val((rqn  - iqn ).toFixed(3));
        row.find('.bqw').val((rqw  - iqw ).toFixed(3));
        row.find('.bamt').val((ramt - iamt).toFixed(2));
    });

    // ── Submit create form ────────────────────────────────────
    $('#daybookForm').submit(function (e) {
        e.preventDefault();

        if ($('#itemsTableBody .item-row').length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one item line' });
            return;
        }

        $.ajax({
            url      : '<?= APP_URL ?>daybook/crudData/insertion',
            type     : 'POST',
            data     : $(this).serialize(),
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: res.message,
                                showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: res.message });
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'An error occurred: ' + xhr.responseText });
            }
        });
    });

    // ── Open edit modal ───────────────────────────────────────
    $(document).on('click', '.editDaybookBtn', function () {
        const id = $(this).data('id');

        $.ajax({
            url      : '<?= APP_URL ?>daybook/getDayBookById',
            type     : 'GET',
            data     : { id: id },
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    const m     = res.data.master;
                    const items = res.data.items;

                    $('#edit_id').val(m.id);
                    $('#edit_page_no').val(m.page_no);
                    $('#edit_stockbook_type_id').val(m.stockbook_type_id);
                    $('#edit_transaction_type').val(m.transaction_type);
                    $('#edit_class').val(m.class);
                    $('#edit_unit_label').val(m.unit_label);
                    $('#edit_service_provider_id').val(m.service_provider_id);
                    $('#edit_doc_date').val(m.document_date);
                    $('#edit_order_no').val(m.receipt_order_no);
                    $('#edit_invoice_ref').val(m.invoice_ref);
                    $('#edit_indent_no').val(m.indent_no);
                    $('#edit_indent_date').val(m.indent_date);
                    $('#edit_cr_voucher').val(m.cr_voucher_ref);
                    $('#edit_verifier').val(m.verifier_initials);
                    $('#edit_remarks').val(m.remarks);

                    $('#editItemsTableBody').html('');
                    editItemCounter = 0;
                    items.forEach((item, idx) => {
                        $('#editItemsTableBody').append(buildItemRow('items', idx, item));
                        editItemCounter = idx + 1;
                    });

                    $('#editModal').modal('show');
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }
        });
    });

    // ── Submit edit form ──────────────────────────────────────
    $('#editForm').submit(function (e) {
        e.preventDefault();
        const id = $('#edit_id').val();

        $.ajax({
            url      : '<?= APP_URL ?>daybook/crudData/updation?id=' + id,
            type     : 'POST',
            data     : $(this).serialize(),
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Updated!', text: res.message,
                                showConfirmButton: false, timer: 1500 })
                        .then(() => { $('#editModal').modal('hide'); location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error!', text: res.message });
                }
            }
        });
    });

    // ── Delete entry ──────────────────────────────────────────
    $(document).on('click', '.deleteDaybookBtn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title             : 'Are you sure?',
            text              : "You won't be able to revert this!",
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonColor: '#d33',
            cancelButtonColor : '#6c757d',
            confirmButtonText : 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url      : '<?= APP_URL ?>daybook/crudData/deletion?id=' + id,
                    type     : 'POST',
                    dataType : 'json',
                    success  : function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message,
                                        showConfirmButton: false, timer: 1500 })
                                .then(() => {
                                    $('#daybookRow_' + id).fadeOut(500, function () { $(this).remove(); });
                                });
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }
                });
            }
        });
    });

});
</script>