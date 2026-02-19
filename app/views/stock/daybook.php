<div class="page-content">
    <div class="page-container">

        <!-- ══════════════════════════════════════════════════════════
     CREATE FORM
═══════════════════════════════════════════════════════════ -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div
                        class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title mb-0">K.F.C. Form 16 — Create Day Book Entry</h4>
                        <button type="button" class="btn btn-sm btn-secondary" id="resetFormBtn">
                            <i class="mdi mdi-refresh"></i> Reset
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="daybookForm">

                            <!-- ── Master Header ──────────────────────────────── -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Page No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="page_no" placeholder="e.g. 132"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Stockbook Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2" name="stockbook_type_id" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($stockbookTypes as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Class</label>
                                    <input type="text" class="form-control" name="class" placeholder="Class">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Unit</label>
                                    <input type="text" class="form-control" name="unit_label" placeholder="Unit">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">By Whom Received / Provider <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2" name="service_provider_id" required>
                                        <option value="">-- Select Provider --</option>
                                        <?php foreach ($serviceProviders as $sp): ?>
                                            <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['provider_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Document Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="document_date" name="document_date"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Order / Ref No</label>
                                    <input type="text" class="form-control" name="receipt_order_no"
                                        placeholder="e.g. GraNkm/261/2025-P">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Invoice Ref</label>
                                    <input type="text" class="form-control" name="invoice_ref"
                                        placeholder="Invoice / Challan No">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Invoice Date</label>
                                    <input type="date" class="form-control" name="invoice_date">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">CR Voucher Ref</label>
                                    <input type="text" class="form-control" name="cr_voucher_ref"
                                        placeholder="e.g. NCR Vol II p.179">
                                </div>
                            </div>

                            <div class="row g-2 mb-3">                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Verifier</label>
                                    <select class="form-select select2" name="verifier_id">
                                        <option value="">-- Select Verifier --</option>
                                        <?php foreach ($users as $u): ?>
                                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Remarks</label>
                                    <input type="text" class="form-control" name="remarks"
                                        placeholder="Remarks / notes">
                                </div>
                            </div>

                            <!-- ══════════════════════════════════════════════════
             RECEIPT ITEMS
        ══════════════════════════════════════════════════ -->
                            <div class="card border border-success mb-3">
                                <div
                                    class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="mdi mdi-arrow-down-circle me-1"></i> Receipt
                                        Items</span>
                                    <button type="button" class="btn btn-sm btn-light" id="addReceiptBtn">
                                        <i class="mdi mdi-plus"></i> Add Receipt Row
                                    </button>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive" style="overflow-x:auto;">
                                        <table class="table table-bordered table-sm align-middle mb-0"
                                            style="min-width:1380px;">
                                            <thead class="table-dark" style="font-size:12px;">
                                                <tr>
                                                    <th rowspan="2" style="width:36px;text-align:center;">#</th>
                                                    <th rowspan="2" style="min-width:180px;">Item Name<span
                                                            class="text-danger">*</span></th>
                                                    <th rowspan="2" style="min-width:160px;">Description</th>
                                                    <th rowspan="2" style="min-width:80px;">Unit</th>
                                                    <th colspan="2" class="text-center">Receipt Qty</th>
                                                    <th colspan="3" class="text-center">Receipt Value</th>
                                                    <th colspan="3" class="text-center">Balance</th>                                                    
                                                    <th rowspan="2" style="width:36px;"></th>
                                                </tr>
                                                <tr style="font-size:11px;">
                                                    <th style="min-width:100px;">No.</th>
                                                    <th style="min-width:100px;">Wt/Msr</th>
                                                    <th style="min-width:100px;">Rate</th>
                                                    <th style="min-width:110px;">Rs.</th>
                                                    <th style="min-width:70px;">Ps.</th>
                                                    <th style="min-width:100px;">Rate</th>
                                                    <th style="min-width:110px;">Rs.</th>
                                                    <th style="min-width:70px;">Ps.</th>
                                                </tr>
                                            </thead>
                                            <tbody id="receiptTableBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- ══════════════════════════════════════════════════
             ISSUE ITEMS
        ══════════════════════════════════════════════════ -->
                            <div class="card border border-danger mb-3">
                                <div
                                    class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="mdi mdi-arrow-up-circle me-1"></i> Issue
                                        Items</span>
                                    <button type="button" class="btn btn-sm btn-light" id="addIssueBtn">
                                        <i class="mdi mdi-plus"></i> Add Issue Row
                                    </button>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive" style="overflow-x:auto;">
                                        <table class="table table-bordered table-sm align-middle mb-0"
                                            style="min-width:1600px;">
                                            <thead class="table-dark" style="font-size:12px;">
                                                <tr>
                                                    <th rowspan="2" style="width:36px;text-align:center;">#</th>
                                                    <th rowspan="2" style="min-width:180px;">Item Name<span
                                                            class="text-danger">*</span></th>
                                                    <th rowspan="2" style="min-width:160px;">Description</th>
                                                    <th rowspan="2" style="min-width:80px;">Unit</th>
                                                    <th colspan="2" class="text-center">Issue Qty</th>
                                                    <th colspan="2" class="text-center">Issue Value</th>
                                                    <th colspan="3" class="text-center">Balance</th>
                                                    <th rowspan="2" style="min-width:130px;">Indent No</th>
                                                    <th rowspan="2" style="min-width:120px;">Indent Date</th>
                                                    <th rowspan="2" style="min-width:75px;">Issued To</th>
                                                    <th rowspan="2" style="width:36px;"></th>
                                                </tr>
                                                <tr style="font-size:11px;">
                                                    <th style="min-width:100px;">No.</th>
                                                    <th style="min-width:100px;">Wt/Msr</th>
                                                    <th style="min-width:100px;">Rate</th>
                                                    <th style="min-width:110px;">Rs.</th>
                                                    <th style="min-width:100px;">Rate</th>
                                                    <th style="min-width:110px;">Rs.</th>
                                                    <th style="min-width:70px;">Ps.</th>
                                                </tr>
                                            </thead>
                                            <tbody id="issueTableBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="mdi mdi-content-save"></i> Save Entry
                                </button>
                            </div>

                        </form>
                    </div><!-- /card-body -->
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
     LIST TABLE
═══════════════════════════════════════════════════════════ -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title mb-0">
                            Day Book Entries
                            <small class="text-muted fw-normal fs-6 ms-2">— click a row to view full detail</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        <table id="daybook-datatable"
                            class="table table-hover table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Page</th>
                                    <th>Stockbook</th>
                                    <th>Date</th>
                                    <th>Provider (By/To)</th>
                                    <th>Issued To</th>
                                    <th>Invoice Ref</th>
                                    <th>📥 Lines</th>
                                    <th>📤 Lines</th>
                                    <th>Receipt (Rs.)</th>
                                    <th>Issue (Rs.)</th>
                                    <th>Verifier</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr class="clickable-row" id="dbRow_<?= $row['id'] ?>"
                                            data-href="<?= APP_URL ?>daybook/viewDayBook/<?= $row['id'] ?>">
                                            <td><?= $row['id'] ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['page_no']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($row['stockbook_name'] ?? '-') ?></td>
                                            <td><?= date('d-m-Y', strtotime($row['document_date'])) ?></td>
                                            <td><strong><?= htmlspecialchars($row['provider_name'] ?? '-') ?></strong></td>
                                            <td><?= htmlspecialchars($row['issued_to_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['invoice_ref'] ?? '-') ?></td>
                                            <td class="text-center"><span
                                                    class="badge bg-success"><?= (int) $row['receipt_lines'] ?></span></td>
                                            <td class="text-center"><span
                                                    class="badge bg-danger"><?= (int) $row['issue_lines'] ?></span></td>
                                            <td class="text-end fw-bold text-success">
                                                <?= number_format($row['total_receipt_amt'], 2) ?></td>
                                            <td class="text-end fw-bold text-danger">
                                                <?= number_format($row['total_issue_amt'], 2) ?></td>
                                            <td><?= htmlspecialchars($row['verifier_name'] ?? '-') ?></td>
                                            <td class="action-cell" >
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
                                        <td colspan="13" class="text-center text-muted">No entries found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /page-container -->
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div><!-- /page-content -->

<!-- ══════════════════════════════════════════════════════════
     EDIT MODAL
═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">✏️ Edit Day Book Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <input type="hidden" id="edit_id">
                <div class="modal-body">

                    <div class="row g-2 mb-2">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Page No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="e_page_no" name="page_no" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stockbook Type</label>
                            <select class="form-select" id="e_stockbook_type_id" name="stockbook_type_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($stockbookTypes as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Class</label>
                            <input type="text" class="form-control" id="e_class" name="class">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Unit</label>
                            <input type="text" class="form-control" id="e_unit_label" name="unit_label">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Provider <span class="text-danger">*</span></label>
                            <select class="form-select" id="e_service_provider_id" name="service_provider_id" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($serviceProviders as $sp): ?>
                                    <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['provider_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Document Date</label>
                            <input type="date" class="form-control" id="e_document_date" name="document_date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Order / Ref No</label>
                            <input type="text" class="form-control" id="e_receipt_order_no" name="receipt_order_no">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice Ref</label>
                            <input type="text" class="form-control" id="e_invoice_ref" name="invoice_ref">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Invoice Date</label>
                            <input type="date" class="form-control" id="e_invoice_date" name="invoice_date">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">CR Voucher Ref</label>
                            <input type="text" class="form-control" id="e_cr_voucher_ref" name="cr_voucher_ref">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Issued To</label>
                            <select class="form-select" id="e_issued_to_id" name="issued_to_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($issuedToList as $ito): ?>
                                    <option value="<?= $ito['id'] ?>"><?= htmlspecialchars($ito['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Verifier</label>
                            <select class="form-select" id="e_verifier_id" name="verifier_id">
                                <option value="">-- Select --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" class="form-control" id="e_remarks" name="remarks">
                        </div>
                    </div>

                    <!-- Edit Receipt Items -->
                    <div class="card border border-success mb-3">
                        <div
                            class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold"><i class="mdi mdi-arrow-down-circle me-1"></i> Receipt Items</span>
                            <button type="button" class="btn btn-sm btn-light" id="addEditReceiptBtn">
                                <i class="mdi mdi-plus"></i> Add Row
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0"
                                    style="min-width:1380px;">
                                    <?= $GLOBALS['receiptTableHead'] ?? '' ?>
                                    <tbody id="editReceiptTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Issue Items -->
                    <div class="card border border-danger mb-3">
                        <div
                            class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold"><i class="mdi mdi-arrow-up-circle me-1"></i> Issue Items</span>
                            <button type="button" class="btn btn-sm btn-light" id="addEditIssueBtn">
                                <i class="mdi mdi-plus"></i> Add Row
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0"
                                    style="min-width:1600px;">
                                    <?= $GLOBALS['issueTableHead'] ?? '' ?>
                                    <tbody id="editIssueTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="mdi mdi-content-save"></i> Update Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover td {
        background-color: #eef3ff !important;
    }

    .action-cell {
        white-space: nowrap;
    }

    .table-sm input.form-control-sm,
    .table-sm select.form-select-sm {
        font-size: 12px;
    }
</style>

<script>
    $(function () {

        /* ── PHP → JS data ──────────────────────────────────── */
        const allItems = <?= json_encode($items ?? []) ?>;
        const units = <?= json_encode($units ?? []) ?>;

        /* ── DataTable ──────────────────────────────────────── */
        if (!$.fn.DataTable.isDataTable('#daybook-datatable')) {
            $('#daybook-datatable').DataTable({
                pageLength: 15,
                order: [[0, 'DESC']],
                columnDefs: [{ orderable: false, targets: 12 }]
            });
        }

        /* ── Select2 ────────────────────────────────────────── */
        if ($.fn.select2) { $('.select2').select2({ width: '100%' }); }

        /* ── Default date ───────────────────────────────────── */
        $('#document_date').val(new Date().toISOString().split('T')[0]);

        /* ── Clickable rows ─────────────────────────────────── */
        $(document).on('click', '.clickable-row', function (e) {
            if ($(e.target).closest('.action-cell').length) return;
            window.location.href = $(this).data('href');
        });

        /* ── Option builders ────────────────────────────────── */
        function itemOptions(sel) {
            let o = '<option value="">-- Item --</option>';
            allItems.forEach(i => o += `<option value="${i.id}"${i.id == sel ? ' selected' : ''}>${i.item_name}</option>`);
            return o;
        }
        function unitOptions(sel) {
            let o = '<option value="">-- Unit --</option>';
            units.forEach(u => o += `<option value="${u.id}"${u.id == sel ? ' selected' : ''}>${u.name}</option>`);
            return o;
        }

        /* ════════════════════════════════════════════════════
           BUILD RECEIPT ROW
        ════════════════════════════════════════════════════ */
        function buildReceiptRow(prefix, idx, d = {}) {
            const sl = idx + 1;
            const n = `${prefix}[${idx}]`;
            return `<tr class="receipt-row">
          <td class="text-center text-muted" style="font-size:12px;">${sl}
            <input type="hidden" name="${n}[sl_no]" value="${sl}">
          </td>
          <td><select class="form-select form-select-sm" name="${n}[item_id]" required>${itemOptions(d.item_id || '')}</select></td>
          <td><input type="text" class="form-control form-control-sm" name="${n}[item_description]"
               value="${esc(d.item_description)}" placeholder="Description"></td>
          <td><select class="form-select form-select-sm" name="${n}[unit_id]">${unitOptions(d.unit_id || '')}</select></td>
          <!-- Receipt Qty -->
          <td><input type="number" step="0.001" class="form-control form-control-sm rqn text-end"
               name="${n}[receipt_qty_number]" value="${d.receipt_qty_number || ''}" placeholder="0"></td>
          <td><input type="number" step="0.001" class="form-control form-control-sm rqw text-end"
               name="${n}[receipt_qty_weight]" value="${d.receipt_qty_weight || ''}" placeholder="0"></td>
          <!-- Receipt Value -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[receipt_rate]" value="${d.receipt_rate || ''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm ramt text-end"
               name="${n}[receipt_amount_rs]" value="${d.receipt_amount_rs || ''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[receipt_amount_ps]" value="${d.receipt_amount_ps || ''}" placeholder="0"></td>
          <!-- Balance (auto) -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[balance_rate]" value="${d.balance_rate || ''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm bamt text-end"
               name="${n}[balance_amount_rs]" value="${d.balance_amount_rs || ''}" placeholder="auto"
               readonly style="background:#f0f0f0;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[balance_amount_ps]" value="${d.balance_amount_ps || ''}" placeholder="0"></td>          
            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ti ti-trash"></i></button>
          </td>
        </tr>`;
        }

        /* ════════════════════════════════════════════════════
           BUILD ISSUE ROW
        ════════════════════════════════════════════════════ */
        function buildIssueRow(prefix, idx, d = {}) {
            const sl = idx + 1;
            const n = `${prefix}[${idx}]`;
            return `<tr class="issue-row">
          <td class="text-center text-muted" style="font-size:12px;">${sl}
            <input type="hidden" name="${n}[sl_no]" value="${sl}">
          </td>
          <td><select class="form-select form-select-sm" name="${n}[item_id]" required>${itemOptions(d.item_id || '')}</select></td>
          <td><input type="text" class="form-control form-control-sm" name="${n}[item_description]"
               value="${esc(d.item_description)}" placeholder="Description"></td>
          <td><select class="form-select form-select-sm" name="${n}[unit_id]">${unitOptions(d.unit_id || '')}</select></td>
          <!-- Issue Qty -->
          <td><input type="number" step="0.001" class="form-control form-control-sm iqn text-end"
               name="${n}[issue_qty_number]" value="${d.issue_qty_number || ''}" placeholder="0"></td>
          <td><input type="number" step="0.001" class="form-control form-control-sm iqw text-end"
               name="${n}[issue_qty_weight]" value="${d.issue_qty_weight || ''}" placeholder="0"></td>
          <!-- Issue Value -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[issue_rate]" value="${d.issue_rate || ''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm iamt text-end"
               name="${n}[issue_amount_rs]" value="${d.issue_amount_rs || ''}" placeholder="0.00"></td>
          <!-- Balance (auto) -->
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[balance_rate]" value="${d.balance_rate || ''}" placeholder="0.00"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm bamt text-end"
               name="${n}[balance_amount_rs]" value="${d.balance_amount_rs || ''}" placeholder="auto"
               readonly style="background:#f0f0f0;"></td>
          <td><input type="number" step="0.01" class="form-control form-control-sm text-end"
               name="${n}[balance_amount_ps]" value="${d.balance_amount_ps || ''}" placeholder="0"></td>
          <!-- Indent fields (issue-specific) -->
          <td><input type="text" class="form-control form-control-sm"
               name="${n}[indent_no]" value="${esc(d.indent_no)}" placeholder="e.g. Tkp/1432"></td>
          <td><input type="date" class="form-control form-control-sm"
               name="${n}[indent_date]" value="${d.indent_date || ''}"></td>
               <td>
<select class="form-select form-select-sm" name="${n}[issued_to_id]">
<option value="">-- Select --</option>
${issuedToOptions(d.issued_to_id||'')}
</select>
</td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ti ti-trash"></i></button>
          </td>
        </tr>`;
        }

        function esc(v) { return (v || '').toString().replace(/"/g, '&quot;'); }

        /* ── Counters ───────────────────────────────────────── */
        let rIdx = 0, iIdx = 0, erIdx = 0, eiIdx = 0;

        /* ── Add rows (create form) ─────────────────────────── */
        $('#addReceiptBtn').click(() => {
            $('#receiptTableBody').append(buildReceiptRow('receipt_items', rIdx++));
        });
        $('#addIssueBtn').click(() => {
            $('#issueTableBody').append(buildIssueRow('issue_items', iIdx++));
        });

        /* ── Add rows (edit modal) ──────────────────────────── */
        $('#addEditReceiptBtn').click(() => {
            $('#editReceiptTableBody').append(buildReceiptRow('receipt_items', erIdx++));
        });
        $('#addEditIssueBtn').click(() => {
            $('#editIssueTableBody').append(buildIssueRow('issue_items', eiIdx++));
        });

        /* ── Remove rows ────────────────────────────────────── */
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });

        /* ── Auto-compute balance amount ────────────────────── */
        $(document).on('input', '.ramt, .iamt', function () {
            const row = $(this).closest('tr');
            const ramt = parseFloat(row.find('.ramt').val()) || 0;
            const iamt = parseFloat(row.find('.iamt').val()) || 0;
            row.find('.bamt').val((ramt - iamt).toFixed(2));
        });

        /* ── Reset create form ──────────────────────────────── */
        $('#resetFormBtn').click(() => {
            $('#daybookForm')[0].reset();
            $('#receiptTableBody, #issueTableBody').html('');
            rIdx = iIdx = 0;
            $('#document_date').val(new Date().toISOString().split('T')[0]);
            if ($.fn.select2) { $('.select2').val('').trigger('change'); }
        });

        /* ── Submit create ──────────────────────────────────── */
        $('#daybookForm').submit(function (e) {
            e.preventDefault();

            if ($('#receiptTableBody .receipt-row').length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Receipt Items', text: 'Add at least one receipt item.' });
                return;
            }

            $.ajax({
                url: '<?= APP_URL ?>daybook/crudData/insertion',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success', title: 'Created!', text: res.message,
                            showConfirmButton: false, timer: 1500
                        })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                },
                error: xhr => Swal.fire({ icon: 'error', title: 'Server Error', text: xhr.responseText })
            });
        });

        /* ── Open edit modal ────────────────────────────────── */
        $(document).on('click', '.editDaybookBtn', function () {
            const id = $(this).data('id');

            $.ajax({
                url: '<?= APP_URL ?>daybook/getDayBookById',
                type: 'GET',
                data: { id },
                dataType: 'json',
                success: function (res) {
                    if (!res.success) { Swal.fire('Error', res.message, 'error'); return; }

                    const m = res.data.master;
                    const ri = res.data.receipt_items;
                    const ii = res.data.issue_items;

                    /* Prefill master fields */
                    $('#edit_id').val(m.id);
                    $('#e_page_no').val(m.page_no);
                    $('#e_stockbook_type_id').val(m.stockbook_type_id);
                    $('#e_class').val(m.class);
                    $('#e_unit_label').val(m.unit_label);
                    $('#e_service_provider_id').val(m.service_provider_id);
                    $('#e_document_date').val(m.document_date);
                    $('#e_receipt_order_no').val(m.receipt_order_no);
                    $('#e_invoice_ref').val(m.invoice_ref);
                    $('#e_invoice_date').val(m.invoice_date || '');
                    $('#e_issued_to_id').val(m.issued_to_id || '');
                    $('#e_cr_voucher_ref').val(m.cr_voucher_ref || '');
                    $('#e_verifier_id').val(m.verifier_id || '');
                    $('#e_remarks').val(m.remarks || '');

                    /* Populate receipt rows */
                    $('#editReceiptTableBody').html('');
                    erIdx = 0;
                    (ri || []).forEach((row, i) => {
                        $('#editReceiptTableBody').append(buildReceiptRow('receipt_items', i, row));
                        erIdx = i + 1;
                    });

                    /* Populate issue rows */
                    $('#editIssueTableBody').html('');
                    eiIdx = 0;
                    (ii || []).forEach((row, i) => {
                        $('#editIssueTableBody').append(buildIssueRow('issue_items', i, row));
                        eiIdx = i + 1;
                    });

                    $('#editModal').modal('show');
                }
            });
        });

        /* ── Submit edit ────────────────────────────────────── */
        $('#editForm').submit(function (e) {
            e.preventDefault();
            const id = $('#edit_id').val();

            $.ajax({
                url: '<?= APP_URL ?>daybook/crudData/updation?id=' + id,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success', title: 'Updated!', text: res.message,
                            showConfirmButton: false, timer: 1500
                        })
                            .then(() => { $('#editModal').modal('hide'); location.reload(); });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                }
            });
        });

        /* ── Delete ─────────────────────────────────────────── */
        $(document).on('click', '.deleteDaybookBtn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete this entry?',
                text: 'All receipt and issue lines will also be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete'
            }).then(r => {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: '<?= APP_URL ?>daybook/crudData/deletion?id=' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success', title: 'Deleted!', text: res.message,
                                showConfirmButton: false, timer: 1500
                            })
                                .then(() => $('#dbRow_' + id).fadeOut(400, function () { $(this).remove(); }));
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            });
        });

        /* ── Shared receipt table header (also used in modal via PHP) ── */
        const receiptHead = `<thead class="table-dark" style="font-size:12px;">
        <tr>
          <th rowspan="2" style="width:36px;text-align:center;">#</th>
          <th rowspan="2" style="min-width:180px;">Item</th>
          <th rowspan="2" style="min-width:160px;">Description</th>
          <th rowspan="2" style="min-width:80px;">Unit</th>
          <th colspan="2" class="text-center">Receipt Qty</th>
          <th colspan="3" class="text-center">Receipt Value</th>
          <th colspan="3" class="text-center">Balance</th>
          <th rowspan="2" style="min-width:75px;">Verifier</th>
          <th rowspan="2" style="width:36px;"></th>
        </tr>
        <tr style="font-size:11px;">
          <th style="min-width:100px;">No.</th><th style="min-width:100px;">Wt/Msr</th>
          <th style="min-width:100px;">Rate</th><th style="min-width:110px;">Rs.</th><th style="min-width:70px;">Ps.</th>
          <th style="min-width:100px;">Rate</th><th style="min-width:110px;">Rs.</th><th style="min-width:70px;">Ps.</th>
        </tr></thead>`;

        const issueHead = `<thead class="table-dark" style="font-size:12px;">
        <tr>
          <th rowspan="2" style="width:36px;text-align:center;">#</th>
          <th rowspan="2" style="min-width:180px;">Item</th>
          <th rowspan="2" style="min-width:160px;">Description</th>
          <th rowspan="2" style="min-width:80px;">Unit</th>
          <th colspan="2" class="text-center">Issue Qty</th>
          <th colspan="2" class="text-center">Issue Value</th>
          <th colspan="3" class="text-center">Balance</th>
          <th rowspan="2" style="min-width:130px;">Indent No</th>
          <th rowspan="2" style="min-width:120px;">Indent Date</th>
          <th rowspan="2" style="min-width:120px;">Issued To</th>
          <th rowspan="2" style="width:36px;"></th>
        </tr>
        <tr style="font-size:11px;">
          <th style="min-width:100px;">No.</th><th style="min-width:100px;">Wt/Msr</th>
          <th style="min-width:100px;">Rate</th><th style="min-width:110px;">Rs.</th>
          <th style="min-width:100px;">Rate</th><th style="min-width:110px;">Rs.</th><th style="min-width:70px;">Ps.</th>
        </tr></thead>`;

        /* Inject headers into modal tables */
        $('#editModal table').eq(0).prepend(receiptHead);
        $('#editModal table').eq(1).prepend(issueHead);

    });
    const issuedList = <?= json_encode($issuedToList ?? []) ?>;

function issuedToOptions(sel){
    let o='';
    issuedList.forEach(i=>{
        o+=`<option value="${i.id}" ${i.id==sel?'selected':''}>${i.name}</option>`;
    });
    return o;
}
</script>