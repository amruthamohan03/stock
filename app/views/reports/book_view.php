<?php
/**
 * FILE: views/reports/book_view.php
 * Unified Report Printing — Indent Book | Stock Book | Day Book
 * Institution & Department from DB dropdowns
 * Academic year auto-derived from date period
 */
?>

<div class="page-content">
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <h4 class="mb-0 fw-bold">
                <i class="mdi mdi-printer-outline me-2 text-primary"></i>
                Stock Register — Report Print
            </h4>
            <small class="text-muted"><i class="mdi mdi-calendar me-1"></i><?= date('d M Y') ?></small>
        </div>
    </div>

    <div class="row">
    <div class="col-12">
    <div class="card shadow-sm">

        <!-- Book Type Tabs -->
        <div class="card-header p-0 bg-dark">
            <ul class="nav nav-pills nav-fill p-2 gap-1" id="bookTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-indent" type="button">
                        <i class="mdi mdi-file-document-edit-outline me-1"></i> Indent Book
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-stock" type="button">
                        <i class="mdi mdi-package-variant-closed me-1"></i> Stock Book
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-daybook" type="button">
                        <i class="mdi mdi-calendar-text-outline me-1"></i> Day Book
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
        <div class="tab-content">

            <!-- ══════════════════════════
                 TAB 1  INDENT BOOK
            ══════════════════════════ -->
            <div class="tab-pane fade show active" id="tab-indent">

                <!-- Institution / Dept / Academic Year -->
                <div class="row g-3 mb-3 p-3 bg-light rounded border">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-domain me-1 text-primary"></i>Institution
                        </label>
                        <select id="ib_institution_id" class="form-select form-select-sm inst-select"
                                data-dept-target="#ib_dept_id">
                            <option value="">— All Institutions —</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['college_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-sitemap me-1 text-primary"></i>Department
                        </label>
                        <select id="ib_dept_id" class="form-select form-select-sm">
                            <option value="">— All Departments —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-calendar-range me-1 text-primary"></i>Academic Year
                            <span class="text-muted fw-normal">(auto from period)</span>
                        </label>
                        <input type="text" id="ib_acyear" class="form-control form-control-sm bg-white"
                               readonly placeholder="Select date period below" style="cursor:default">
                    </div>
                </div>

                <!-- Filters -->
                <h6 class="text-muted fw-semibold mb-3 border-bottom pb-2">
                    <i class="mdi mdi-filter me-1"></i>Filters
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">From Date</label>
                        <input type="date" id="ib_from" class="form-control form-control-sm period-from"
                               data-acyear="#ib_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">To Date</label>
                        <input type="date" id="ib_to" class="form-control form-control-sm period-to"
                               data-acyear="#ib_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Item Type</label>
                        <select id="ib_item_type" class="form-select form-select-sm">
                            <option value="ALL">All Types</option>
                            <option value="C">Consumable</option>
                            <option value="N">Non-Consumable</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Category</label>
                        <select id="ib_eq_type" class="form-select form-select-sm">
                            <option value="ALL">All Categories</option>
                            <option value="FURNITURE">Furniture</option>
                            <option value="ELECTRONIC">Electronic / Equipment</option>
                            <option value="OTHERS">Others</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Book No.</label>
                        <input type="number" id="ib_book_no" class="form-control form-control-sm" placeholder="All">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select id="ib_status" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="CREATED">Created</option>
                            <option value="VERIFIED">Verified</option>
                            <option value="PASSED">Passed</option>
                            <option value="ISSUED">Issued</option>
                            <option value="RECEIVED">Received</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-primary btn-sm px-4" id="ib_searchBtn">
                            <i class="mdi mdi-magnify"></i> Search
                        </button>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-danger btn-sm px-4" id="ib_pdfBtn">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="ibTable">
                        <thead class="table-dark" style="font-size:11px">
                            <tr>
                                <th rowspan="2" class="align-middle text-center">Sl.</th>
                                <th rowspan="2" class="align-middle">Indent No.</th>
                                <th rowspan="2" class="align-middle">Date</th>
                                <th rowspan="2" class="align-middle">Bk.</th>
                                <th rowspan="2" class="align-middle">Institution</th>
                                <th rowspan="2" class="align-middle">Department</th>
                                <th rowspan="2" class="align-middle">Particulars</th>
                                <th rowspan="2" class="align-middle">Make / Model</th>
                                <th rowspan="2" class="align-middle">Purpose</th>
                                <th rowspan="2" class="align-middle">Type</th>
                                <th colspan="3" class="text-center">Quantity</th>
                                <th rowspan="2" class="align-middle">Status</th>
                                <th rowspan="2" class="align-middle">Remarks</th>
                            </tr>
                            <tr>
                                <th class="text-center">Intended</th>
                                <th class="text-center">Passed</th>
                                <th class="text-center">Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="15" class="text-center text-muted py-4 small">
                                Select filters and click Search
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- /tab-indent -->


            <!-- ══════════════════════════
                 TAB 2  STOCK BOOK
            ══════════════════════════ -->
            <div class="tab-pane fade" id="tab-stock">

                <div class="row g-3 mb-3 p-3 bg-light rounded border">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-domain me-1 text-primary"></i>Institution
                        </label>
                        <select id="sb_institution_id" class="form-select form-select-sm inst-select"
                                data-dept-target="#sb_dept_id">
                            <option value="">— All Institutions —</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['college_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-sitemap me-1 text-primary"></i>Department
                        </label>
                        <select id="sb_dept_id" class="form-select form-select-sm">
                            <option value="">— All Departments —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-calendar-range me-1 text-primary"></i>Academic Year
                            <span class="text-muted fw-normal">(auto from period)</span>
                        </label>
                        <input type="text" id="sb_acyear" class="form-control form-control-sm bg-white"
                               readonly placeholder="Select date period below" style="cursor:default">
                    </div>
                </div>

                <h6 class="text-muted fw-semibold mb-3 border-bottom pb-2">
                    <i class="mdi mdi-filter me-1"></i>Filters
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">From Date</label>
                        <input type="date" id="sb_from" class="form-control form-control-sm period-from"
                               data-acyear="#sb_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">To Date</label>
                        <input type="date" id="sb_to" class="form-control form-control-sm period-to"
                               data-acyear="#sb_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Transaction Type</label>
                        <select id="sb_txn_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="RECEIPT">Receipt</option>
                            <option value="ISSUE">Issue</option>
                            <option value="BROUGHT_FORWARD">Brought Forward</option>
                            <option value="ADJUSTMENT">Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Category</label>
                        <select id="sb_eq_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="FURNITURE">Furniture</option>
                            <option value="ELECTRONIC">Electronic</option>
                            <option value="OTHERS">Others</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Item Type</label>
                        <select id="sb_item_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="C">Consumable</option>
                            <option value="N">Non-Consumable</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-primary btn-sm px-4" id="sb_searchBtn">
                            <i class="mdi mdi-magnify"></i> Search
                        </button>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-danger btn-sm px-4" id="sb_pdfBtn">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="sbTable">
                        <thead class="table-dark" style="font-size:11px">
                            <tr>
                                <th>Date</th>
                                <th>Pg.</th>
                                <th>Institution</th>
                                <th>Department</th>
                                <th>Voucher / Invoice</th>
                                <th>From / To</th>
                                <th>Item Name</th>
                                <th>Make / Model</th>
                                <th class="text-center">Receipt</th>
                                <th class="text-center">Issued</th>
                                <th class="text-center">Balance</th>
                                <th>Initials</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="13" class="text-center text-muted py-4 small">
                                Select filters and click Search
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- /tab-stock -->


            <!-- ══════════════════════════
                 TAB 3  DAY BOOK
            ══════════════════════════ -->
            <div class="tab-pane fade" id="tab-daybook">

                <div class="row g-3 mb-3 p-3 bg-light rounded border">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-domain me-1 text-primary"></i>Institution
                        </label>
                        <select id="db_institution_id" class="form-select form-select-sm inst-select"
                                data-dept-target="#db_dept_id">
                            <option value="">— All Institutions —</option>
                            <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= htmlspecialchars($inst['college_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-sitemap me-1 text-primary"></i>Department
                        </label>
                        <select id="db_dept_id" class="form-select form-select-sm">
                            <option value="">— All Departments —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="mdi mdi-calendar-range me-1 text-primary"></i>Academic Year
                            <span class="text-muted fw-normal">(auto from period)</span>
                        </label>
                        <input type="text" id="db_acyear" class="form-control form-control-sm bg-white"
                               readonly placeholder="Select date period below" style="cursor:default">
                    </div>
                </div>

                <h6 class="text-muted fw-semibold mb-3 border-bottom pb-2">
                    <i class="mdi mdi-filter me-1"></i>Filters
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">From Date</label>
                        <input type="date" id="db_from" class="form-control form-control-sm period-from"
                               data-acyear="#db_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">To Date</label>
                        <input type="date" id="db_to" class="form-control form-control-sm period-to"
                               data-acyear="#db_acyear">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Stock Book Type</label>
                        <select id="db_sb_type" class="form-select form-select-sm">
                            <option value="ALL">All Books</option>
                            <option value="MAIN">Main Stock Book</option>
                            <option value="DEAD">Dead Stock Book</option>
                            <option value="PERISH">Perishable</option>
                            <option value="CONSUM">Consumable</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Item Type</label>
                        <select id="db_item_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="CONSUMABLE">Consumable</option>
                            <option value="NON_CONSUMABLE">Non-Consumable</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Category</label>
                        <select id="db_eq_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="FURNITURE">Furniture</option>
                            <option value="ELECTRONIC">Electronic</option>
                            <option value="OTHERS">Others</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Txn Type</label>
                        <select id="db_txn_type" class="form-select form-select-sm">
                            <option value="ALL">All</option>
                            <option value="RECEIPT">Receipt</option>
                            <option value="ISSUE">Issue</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-primary btn-sm px-4" id="db_searchBtn">
                            <i class="mdi mdi-magnify"></i> Search
                        </button>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button class="btn btn-danger btn-sm px-4" id="db_pdfBtn">
                            <i class="mdi mdi-file-pdf-box me-1"></i> Export PDF
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="dbTable">
                        <thead class="table-dark" style="font-size:11px">
                            <tr>
                                <th rowspan="2" class="align-middle">Date</th>
                                <th rowspan="2" class="align-middle">Pg.</th>
                                <th rowspan="2" class="align-middle">Institution</th>
                                <th rowspan="2" class="align-middle">Department</th>
                                <th rowspan="2" class="align-middle">Rcpt Order / Issue Note</th>
                                <th rowspan="2" class="align-middle">Item</th>
                                <th colspan="2" class="text-center">Receipts</th>
                                <th colspan="2" class="text-center">Issues</th>
                                <th colspan="2" class="text-center">Balance</th>
                                <th rowspan="2" class="align-middle">Verifier</th>
                                <th rowspan="2" class="align-middle">Remarks</th>
                            </tr>
                            <tr>
                                <th class="text-center">No.</th><th class="text-center">Wt/Msr</th>
                                <th class="text-center">No.</th><th class="text-center">Wt/Msr</th>
                                <th class="text-center">No.</th><th class="text-center">Wt/Msr</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="14" class="text-center text-muted py-4 small">
                                Select filters and click Search
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- /tab-daybook -->

        </div><!-- tab-content -->
        </div><!-- card-body -->
    </div><!-- card -->
    </div>
    </div>
</div>
</div>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>

<style>
#bookTabs .nav-link          { color:rgba(255,255,255,.7); border-radius:5px; font-size:13px; font-weight:500; }
#bookTabs .nav-link.active   { background:#fff; color:#212529; font-weight:700; }
#bookTabs .nav-link:hover:not(.active) { background:rgba(255,255,255,.15); color:#fff; }
.table thead th              { font-size:10.5px; vertical-align:middle; white-space:nowrap; }
.table tbody td              { font-size:11px; vertical-align:middle; }
.bg-light.rounded.border     { background:#f8f9fb !important; }
#ib_acyear, #sb_acyear, #db_acyear {
    font-weight:700; color:#0d6efd; letter-spacing:0.5px;
}
</style>

<script>
/* ══════════════════════════════════════════════
   ACADEMIC YEAR AUTO-CALCULATION
   Kerala polytechnic: June – May cycle
   If from-month >= 6  →  YYYY-(YYYY+1)
   If from-month < 6   →  (YYYY-1)-YYYY
══════════════════════════════════════════════ */
function calcAcademicYear(fromVal) {
    if (!fromVal) return '';
    const d = new Date(fromVal);
    const m = d.getMonth() + 1; // 1-12
    const y = d.getFullYear();
    return m >= 6
        ? y + '-' + String(y + 1).slice(-2)
        : (y - 1) + '-' + String(y).slice(-2);
}

/* Auto-update academic year when FROM date changes */
$(document).on('change', '.period-from', function () {
    const target = $(this).data('acyear');
    const yr = calcAcademicYear($(this).val());
    $(target).val(yr || '');
});

/* ══════════════════════════════════════════════
   DEPARTMENT CASCADE  (institution → dept)
══════════════════════════════════════════════ */
$(document).on('change', '.inst-select', function () {
    const instId   = $(this).val();
    const deptSel  = $($(this).data('dept-target'));

    deptSel.html('<option value="">Loading...</option>').prop('disabled', true);

    if (!instId) {
        deptSel.html('<option value="">— All Departments —</option>').prop('disabled', false);
        return;
    }

    $.get('<?= APP_URL ?>book/getDepartments', { institution_id: instId }, function (res) {
        let opts = '<option value="">— All Departments —</option>';
        (res.data || []).forEach(d => {
            opts += `<option value="${d.id}">${d.department_name}</option>`;
        });
        deptSel.html(opts).prop('disabled', false);
    }, 'json').fail(function () {
        deptSel.html('<option value="">— All Departments —</option>').prop('disabled', false);
    });
});

/* ══════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════ */
function qp(obj) { return new URLSearchParams(obj).toString(); }

function statusBadge(s) {
    const c = { CREATED:'secondary', VERIFIED:'info', PASSED:'warning', ISSUED:'primary', RECEIVED:'success' };
    return `<span class="badge bg-${c[s]||'secondary'}" style="font-size:9px">${s}</span>`;
}
function typeBadge(t) {
    return t === 'C'
        ? `<span class="badge bg-success" style="font-size:9px">Consumable</span>`
        : `<span class="badge bg-primary" style="font-size:9px">Non-Consumable</span>`;
}
function txnBadge(t) {
    const c = { RECEIPT:'success', ISSUE:'danger', BROUGHT_FORWARD:'info', ADJUSTMENT:'warning' };
    return `<span class="badge bg-${c[t]||'secondary'}" style="font-size:9px">${t.replace(/_/g,' ')}</span>`;
}
function noData(cols) {
    return `<tr><td colspan="${cols}" class="text-center text-muted py-4 small">No records found</td></tr>`;
}

/* ══════════════════════════════════════════════
   TAB 1 — INDENT BOOK
══════════════════════════════════════════════ */
$('#ib_searchBtn').on('click', function () {
    const p = {
        from:           $('#ib_from').val(),
        to:             $('#ib_to').val(),
        institution_id: $('#ib_institution_id').val(),
        dept_id:        $('#ib_dept_id').val(),
        item_type:      $('#ib_item_type').val(),
        eq_type:        $('#ib_eq_type').val(),
        book_no:        $('#ib_book_no').val(),
        status:         $('#ib_status').val()
    };

    $.get('<?= APP_URL ?>book/fetchIndentBook', p, function (res) {
        const rows = res.data || [];
        if (!rows.length) { $('#ibTable tbody').html(noData(15)); return; }

        let html = '', prev = null, sl = 1;
        rows.forEach((r) => {
            const isNew = r.indent_no !== prev;
            if (isNew && prev !== null)
                html += `<tr><td colspan="15" class="p-0" style="background:#ced4da;height:2px"></td></tr>`;
            prev = r.indent_no;

            html += `<tr${isNew ? ' class="table-light fw-semibold"' : ''}>
                <td class="text-center">${isNew ? sl++ : ''}</td>
                <td>${isNew ? `<b>${r.indent_no}</b>` : ''}</td>
                <td>${isNew ? r.indent_date : ''}</td>
                <td>${isNew ? r.book_no : ''}</td>
                <td style="font-size:10px">${isNew ? (r.college_name || '—') : ''}</td>
                <td style="font-size:10px">${isNew ? (r.department_name || '—') : ''}</td>
                <td><b>${r.item_name || '—'}</b>
                    ${r.item_description ? `<br><small class="text-muted">${r.item_description}</small>` : ''}
                </td>
                <td style="font-size:10px">${[r.make_name, r.model_name].filter(Boolean).join(' / ') || '—'}</td>
                <td style="font-size:10px">${r.item_purpose || '—'}</td>
                <td>${isNew ? typeBadge(r.item_type) : ''}</td>
                <td class="text-center">${r.qty_intended ?? 0}</td>
                <td class="text-center">${r.qty_passed ?? 0}</td>
                <td class="text-center">${r.qty_issued ?? 0}</td>
                <td>${isNew ? statusBadge(r.status) : ''}</td>
                <td style="font-size:10px">${r.remarks || ''}</td>
            </tr>`;
        });
        $('#ibTable tbody').html(html);
    }, 'json');
});

$('#ib_pdfBtn').on('click', function () {
    const p = qp({
        from:           $('#ib_from').val(),           to:       $('#ib_to').val(),
        institution_id: $('#ib_institution_id').val(), dept_id:  $('#ib_dept_id').val(),
        acyear:         $('#ib_acyear').val(),
        item_type:      $('#ib_item_type').val(),       eq_type:  $('#ib_eq_type').val(),
        book_no:        $('#ib_book_no').val(),         status:   $('#ib_status').val()
    });
    window.open(`<?= APP_URL ?>book/exportPdf/indent_book?${p}`);
});

/* ══════════════════════════════════════════════
   TAB 2 — STOCK BOOK
══════════════════════════════════════════════ */
$('#sb_searchBtn').on('click', function () {
    const p = {
        from:           $('#sb_from').val(),
        to:             $('#sb_to').val(),
        institution_id: $('#sb_institution_id').val(),
        dept_id:        $('#sb_dept_id').val(),
        txn_type:       $('#sb_txn_type').val(),
        eq_type:        $('#sb_eq_type').val(),
        item_type:      $('#sb_item_type').val()
    };

    $.get('<?= APP_URL ?>book/fetchStockBook', p, function (res) {
        const rows = res.data || [];
        if (!rows.length) { $('#sbTable tbody').html(noData(13)); return; }

        let html = '';
        rows.forEach((r) => {
            html += `<tr>
                <td>${r.transaction_date}</td>
                <td><b>${r.page_no}</b></td>
                <td style="font-size:10px">${r.college_name || '—'}</td>
                <td style="font-size:10px">${r.department_name || '—'}</td>
                <td style="font-size:10px">${r.voucher_no || '—'}
                    ${r.voucher_date ? `<br><small class="text-muted">${r.voucher_date}</small>` : ''}
                </td>
                <td style="font-size:10px">${r.received_from || r.issued_to || '—'}</td>
                <td><b>${r.item_name || '—'}</b>
                    ${r.item_description ? `<br><small class="text-muted">${r.item_description}</small>` : ''}
                    ${r.make_name || r.model_name ? `<br><small class="text-muted">${[r.make_name,r.model_name].filter(Boolean).join('/')}</small>` : ''}
                </td>
                <td>${txnBadge(r.transaction_type)}</td>
                <td class="text-center text-success fw-bold">${r.receipt_qty || 0}</td>
                <td class="text-center text-danger fw-bold">${r.issue_qty || 0}</td>
                <td class="text-center fw-bold">${r.balance_qty || 0}</td>
                <td style="font-size:10px">${r.receiver_initial || '—'}</td>
                <td style="font-size:10px">${r.remarks || ''}</td>
            </tr>`;
        });
        $('#sbTable tbody').html(html);
    }, 'json');
});

$('#sb_pdfBtn').on('click', function () {
    const p = qp({
        from:           $('#sb_from').val(),           to:       $('#sb_to').val(),
        institution_id: $('#sb_institution_id').val(), dept_id:  $('#sb_dept_id').val(),
        acyear:         $('#sb_acyear').val(),
        txn_type:       $('#sb_txn_type').val(),       eq_type:  $('#sb_eq_type').val(),
        item_type:      $('#sb_item_type').val()
    });
    window.open(`<?= APP_URL ?>book/exportPdf/stock_book?${p}`);
});

/* ══════════════════════════════════════════════
   TAB 3 — DAY BOOK
══════════════════════════════════════════════ */
$('#db_searchBtn').on('click', function () {
    const p = {
        from:           $('#db_from').val(),
        to:             $('#db_to').val(),
        institution_id: $('#db_institution_id').val(),
        dept_id:        $('#db_dept_id').val(),
        sb_type:        $('#db_sb_type').val(),
        item_type:      $('#db_item_type').val(),
        eq_type:        $('#db_eq_type').val(),
        txn_type:       $('#db_txn_type').val()
    };

    $.get('<?= APP_URL ?>book/fetchDayBook', p, function (res) {
        const rows = res.data || [];
        if (!rows.length) { $('#dbTable tbody').html(noData(14)); return; }

        let html = '';
        rows.forEach((r) => {
            html += `<tr>
                <td>${r.document_date}</td>
                <td><b>${r.page_no || '—'}</b></td>
                <td style="font-size:10px">${r.college_name || '—'}</td>
                <td style="font-size:10px">${r.department_name || '—'}</td>
                <td style="font-size:10px">${r.receipt_order_no || '—'}
                    ${r.indent_no ? `<br><small class="text-muted">Indent: ${r.indent_no}</small>` : ''}
                    ${r.invoice_ref ? `<br><small class="text-muted">${r.invoice_ref}</small>` : ''}
                </td>
                <td><b>${r.item_name || '—'}</b>
                    ${r.item_description ? `<br><small class="text-muted">${r.item_description}</small>` : ''}
                </td>
                <td class="text-center text-success fw-bold">${r.receipt_qty_number || 0}</td>
                <td class="text-center text-muted">${r.receipt_qty_weight || ''}</td>
                <td class="text-center text-danger fw-bold">${r.issue_qty_number || 0}</td>
                <td class="text-center text-muted">${r.issue_qty_weight || ''}</td>
                <td class="text-center fw-bold">${r.balance_qty_number || 0}</td>
                <td class="text-center text-muted">${r.balance_qty_weight || ''}</td>
                <td style="font-size:10px">${r.verifier_name || '—'}</td>
                <td style="font-size:10px">${r.remarks || ''}</td>
            </tr>`;
        });
        $('#dbTable tbody').html(html);
    }, 'json');
});

$('#db_pdfBtn').on('click', function () {
    const p = qp({
        from:           $('#db_from').val(),           to:       $('#db_to').val(),
        institution_id: $('#db_institution_id').val(), dept_id:  $('#db_dept_id').val(),
        acyear:         $('#db_acyear').val(),
        sb_type:        $('#db_sb_type').val(),        item_type:$('#db_item_type').val(),
        eq_type:        $('#db_eq_type').val(),        txn_type: $('#db_txn_type').val()
    });
    window.open(`<?= APP_URL ?>book/exportPdf/day_book?${p}`);
});
</script>