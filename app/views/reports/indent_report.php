<div class="page-content">
    <div class="page-container">
        <!-- Stock Entry Form -->
        <div class="row">

            ><div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Indent Book Report</h4>
                </div>

                <div class="card-body">

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" id="from" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" id="to" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Book Type</label>
                            <select id="type" class="form-control">
                                <option value="ALL">All</option>
                                <option value="C">Consumable</option>
                                <option value="N">Non Consumable</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="searchBtn">Search</button>
                        </div>

                    </div>

                    <div class="mb-3 text-end">
                        <button class="btn btn-danger" id="pdfBtn">
                            <i class="ti ti-file-download"></i> Export PDF
                        </button>
                    </div>

                    <table class="table table-bordered table-striped" id="reportTable">
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
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
<script>
    function loadReport() {

        let from = $('#from').val();
        let to = $('#to').val();
        let type = $('#type').val();

        $.get("<?= APP_URL ?>indentbook/fetchReport", { from, to, type }, res => {

            let rows = JSON.parse(res).data;
            let html = '';

            rows.forEach((r, i) => {

                html += `<tr>
<td>${i + 1}</td>
<td>${r.indent_no}</td>
<td>${r.indent_date}</td>
<td>${r.item_type == 'C' ? 'Consumable' : 'Non-Consumable'}</td>
<td>${r.item_description ?? ''}</td>
<td>${r.qty_intended}</td>
<td>${r.qty_passed}</td>
<td>${r.qty_issued}</td>
</tr>`;
            });

            $('#reportTable tbody').html(html);

        });
    }

    $('#searchBtn').click(loadReport);

    $('#pdfBtn').click(() => {
        let from = $('#from').val();
        let to = $('#to').val();
        let type = $('#type').val();

        window.open(`<?= APP_URL ?>indentbook/exportPdf?from=${from}&to=${to}&type=${type}`);
    });
</script>