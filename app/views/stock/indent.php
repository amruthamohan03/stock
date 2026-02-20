<!-- ═════════════════════════════════════════════════════
     INDENT REPORT SECTION
═════════════════════════════════════════════════════ -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">

            <div class="card-header border-bottom border-dashed d-flex justify-content-between align-items-center">
                <h4 class="header-title mb-0">Indent Book Report</h4>
            </div>

            <div class="card-body">

                <!-- Filters -->
                <div class="row g-2 mb-3">

                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" id="r_from" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" id="r_to" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Item Type</label>
                        <select id="r_type" class="form-select">
                            <option value="ALL">All</option>
                            <option value="C">Consumable</option>
                            <option value="N">Non-Consumable</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="loadReportBtn">
                            <i class="mdi mdi-magnify"></i> Generate Report
                        </button>
                    </div>

                </div>

                <!-- PDF Button -->
                <div class="text-end mb-3">
                    <button class="btn btn-danger" id="exportPdfBtn">
                        <i class="mdi mdi-file-pdf-box"></i> Export PDF
                    </button>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="reportTable" class="table table-bordered table-striped table-sm w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Indent No</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Intended</th>
                                <th>Passed</th>
                                <th>Issued</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    /* ===============================
   LOAD REPORT
================================ */
    $('#loadReportBtn').click(function () {

        let from = $('#r_from').val();
        let to = $('#r_to').val();
        let type = $('#r_type').val();

        $.get('<?= APP_URL ?>indent/fetchReport', { from, to, type }, function (res) {

            let rows = res.data || [];
            let html = '';

            rows.forEach((r, i) => {

                html += `
<tr>
<td>${i + 1}</td>
<td>${r.indent_no}</td>
<td>${r.indent_date}</td>
<td>${r.item_type == 'C' ? 'Consumable' : 'Non-Consumable'}</td>
<td>${r.item_description ?? ''}</td>
<td>${r.qty_intended ?? 0}</td>
<td>${r.qty_passed ?? 0}</td>
<td>${r.qty_issued ?? 0}</td>
</tr>
`;

            });

            $('#reportTable tbody').html(html);

        }, 'json');

    });


    /* ===============================
       EXPORT PDF
    ================================ */
    $('#exportPdfBtn').click(function () {

        let from = $('#r_from').val();
        let to = $('#r_to').val();
        let type = $('#r_type').val();

        window.open(`<?= APP_URL ?>indent/exportPdf?from=${from}&to=${to}&type=${type}`);
    });

</script>