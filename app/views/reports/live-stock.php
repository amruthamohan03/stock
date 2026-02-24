<!-- ============================================================
     Live Stock Register  |  live-stock.php
     Path : views/stock/live-stock.php
     Controller: LiveController
     ============================================================ -->
<div class="page-content">
    <div class="page-container">

        <!-- ── Page Header ── -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h4 class="page-title mb-0">
                    <i class="mdi mdi-chart-bar text-primary me-1"></i>
                    Live Stock Register
                    <span class="badge bg-success ms-2 fs-12" id="live-badge">
                        <i class="mdi mdi-circle-medium blink-dot"></i> LIVE
                    </span>
                </h4>
                <small class="text-muted">Last refreshed: <span id="last-refreshed">—</span></small>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-sm btn-outline-secondary me-1" id="btn-pause" onclick="toggleAutoRefresh()">
                    <i class="mdi mdi-pause-circle-outline"></i> Pause
                </button>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="refreshData()">
                    <i class="mdi mdi-refresh"></i> Refresh Now
                </button>
                <a href="<?= APP_URL; ?>stock" class="btn btn-sm btn-outline-dark">
                    <i class="mdi mdi-arrow-left"></i> Stock Entry
                </a>
            </div>
        </div>

        <!-- ── Filters ── -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1 text-muted small">Filter by Item</label>
                        <select class="form-select form-select-sm select2" id="filter-item">
                            <option value="">All Items</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1 text-muted small">Filter by Location</label>
                        <select class="form-select form-select-sm select2" id="filter-location">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc['location_name']) ?>">
                                    <?= htmlspecialchars($loc['location_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary w-100" onclick="refreshData()">
                            <i class="mdi mdi-filter"></i> Apply
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-light w-100" onclick="clearFilters()">
                            <i class="mdi mdi-filter-off"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Summary Cards ── -->
        <div class="row mb-3" id="summary-cards">
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-primary fs-28 fw-bold" id="card-receipt"><?= $totals['total_receipt'] ?></div>
                        <div class="text-muted small">Total Receipted</div>
                        <i class="mdi mdi-inbox-arrow-down text-primary fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-danger fs-28 fw-bold" id="card-issued"><?= $totals['total_issued'] ?></div>
                        <div class="text-muted small">Total Issued</div>
                        <i class="mdi mdi-inbox-arrow-up text-danger fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-warning fs-28 fw-bold" id="card-transferred"><?= $totals['transferred_qty'] ?></div>
                        <div class="text-muted small">Transferred</div>
                        <i class="mdi mdi-transfer text-warning fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-secondary fs-28 fw-bold" id="card-deleted"><?= $totals['deleted_receipt'] ?></div>
                        <div class="text-muted small">Deleted / Written-off</div>
                        <i class="mdi mdi-delete-empty text-secondary fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-success fs-28 fw-bold" id="card-working"><?= $totals['working_receipt'] ?></div>
                        <div class="text-muted small">Working</div>
                        <i class="mdi mdi-check-circle text-success fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-xl mb-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body p-3">
                        <div class="text-info fs-28 fw-bold" id="card-balance"><?= $totals['current_balance'] ?></div>
                        <div class="text-muted small">Current Balance</div>
                        <i class="mdi mdi-package-variant-closed text-info fs-20 mt-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Main Data Table ── -->
        <div class="card">
            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h5 class="header-title mb-0">Item-Wise Stock Summary</h5>
                <div>
                    <button class="btn btn-sm btn-outline-success" onclick="exportTable()">
                        <i class="mdi mdi-microsoft-excel"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0 align-middle" id="live-stock-table">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th class="text-center">
                                    <i class="mdi mdi-inbox-arrow-down"></i> Receipted
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-inbox-arrow-up text-danger"></i> Issued
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-transfer text-warning"></i> Transferred
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-delete-empty text-danger"></i> Deleted
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-check-circle text-success"></i> Working
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-alert-circle text-danger"></i> Not Working
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-wrench text-info"></i> Repaired
                                </th>
                                <th class="text-center">
                                    <i class="mdi mdi-clock-outline text-warning"></i> Pending
                                </th>
                                <th class="text-center">Balance</th>
                                <th>Locations</th>
                                <th class="text-center">Last Txn</th>
                            </tr>
                        </thead>
                        <tbody id="live-stock-tbody">
                            <?php if (!empty($liveStock)): ?>
                                <?php foreach ($liveStock as $idx => $row): ?>
                                    <tr>
                                        <td class="text-muted small"><?= $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['item_name']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $row['transaction_count'] ?> txn(s) &bull; <?= $row['location_count'] ?> location(s)</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fs-13 px-2"><?= (int)$row['total_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger-subtle text-danger fs-13 px-2"><?= (int)$row['total_issued'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning-subtle text-warning fs-13 px-2"><?= (int)$row['transferred_qty'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary fs-13 px-2"><?= (int)$row['deleted_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success fs-13 px-2"><?= (int)$row['working_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger-subtle text-danger fs-13 px-2"><?= (int)$row['not_working_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-info fs-13 px-2"><?= (int)$row['repaired_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning-subtle text-dark fs-13 px-2"><?= (int)$row['pending_receipt'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                                $bal = (int)$row['total_receipt']-((int)$row['transferred_qty']+ (int)$row['deleted_receipt']);
                                                $balClass = $bal > 0 ? 'bg-success' : ($bal == 0 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <span class="badge <?= $balClass ?> fs-13 px-2"><?= $bal ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= htmlspecialchars($row['locations_list'] ?? '—') ?></small>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <?= $row['last_transaction_date'] ? date('d-m-Y', strtotime($row['last_transaction_date'])) : '—' ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">No stock records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                        <!-- Totals footer -->
                        <tfoot class="table-dark fw-bold" id="live-stock-tfoot">
                            <tr>
                                <td colspan="2" class="text-end">TOTAL</td>
                                <td class="text-center" id="foot-receipt"><?= $totals['total_receipt'] ?></td>
                                <td class="text-center" id="foot-issued"><?= $totals['total_issued'] ?></td>
                                <td class="text-center" id="foot-transferred"><?= $totals['transferred_qty'] ?></td>
                                <td class="text-center" id="foot-deleted"><?= $totals['deleted_receipt'] ?></td>
                                <td class="text-center" id="foot-working"><?= $totals['working_receipt'] ?></td>
                                <td class="text-center" id="foot-not-working"><?= $totals['not_working_receipt'] ?></td>
                                <td class="text-center" id="foot-repaired"><?= $totals['repaired_receipt'] ?></td>
                                <td class="text-center" id="foot-pending"><?= $totals['pending_receipt'] ?></td>
                                <td class="text-center" id="foot-balance"><?= $totals['current_balance'] ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div><!-- /card-body -->
        </div><!-- /card -->

    </div><!-- /page-container -->
</div><!-- /page-content -->

<!-- ============================================================
     Styles
     ============================================================ -->
<style>
    .blink-dot {
        animation: blink 1.4s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0; }
    }
    #live-stock-table thead th {
        white-space: nowrap;
        font-size: 12px;
        vertical-align: middle;
    }
    #live-stock-table tbody td {
        vertical-align: middle;
    }
    .fs-28 { font-size: 28px !important; }
    .fs-20 { font-size: 20px !important; }
    .fs-13 { font-size: 13px !important; }
    .col-xl { flex: 1 0 0%; }
</style>

<!-- ============================================================
     JavaScript – Auto-refresh + AJAX update
     ============================================================ -->
<script>
(function () {
    'use strict';

    /* ── State ── */
    var refreshInterval = 30000; // 30 seconds
    var timer           = null;
    var isPaused        = false;

    /* ── DataTable init ── */
    var dt;
    $(document).ready(function () {
        dt = $('#live-stock-table').DataTable({
            paging      : true,
            pageLength  : 25,
            searching   : true,
            ordering    : true,
            info        : true,
            responsive  : true,
            dom         : '<"d-flex align-items-center justify-content-between mb-2"fl>rt<"d-flex align-items-center justify-content-between mt-2"ip>',
            columnDefs  : [
                { orderable: false, targets: [11, 12] }
            ],
        });

        startAutoRefresh();
        updateTimestamp();
    });

    /* ── Auto-refresh ── */
    function startAutoRefresh() {
        if (timer) clearInterval(timer);
        timer = setInterval(function () {
            if (!isPaused) refreshData();
        }, refreshInterval);
    }

    window.toggleAutoRefresh = function () {
        isPaused = !isPaused;
        var btn = document.getElementById('btn-pause');
        if (isPaused) {
            btn.innerHTML = '<i class="mdi mdi-play-circle-outline"></i> Resume';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-success');
            document.getElementById('live-badge').classList.replace('bg-success', 'bg-secondary');
        } else {
            btn.innerHTML = '<i class="mdi mdi-pause-circle-outline"></i> Pause';
            btn.classList.replace('btn-outline-success', 'btn-outline-secondary');
            document.getElementById('live-badge').classList.replace('bg-secondary', 'bg-success');
            refreshData();
        }
    };

    /* ── Main refresh function ── */
    window.refreshData = function () {
        var itemId   = $('#filter-item').val()   || '';
        var location = $('#filter-location').val() || '';

        $.ajax({
            url      : '<?= APP_URL ?>live/getLiveData',
            type     : 'GET',
            data     : { item_id: itemId, location: location },
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    rebuildTable(res.data);
                    updateSummaryCards(res.totals);
                    updateFooter(res.totals);
                    document.getElementById('last-refreshed').textContent = res.timestamp;
                }
            },
            error    : function () {
                console.warn('Live stock refresh failed.');
            }
        });
    };

    /* ── Rebuild tbody via DataTable ── */
    function rebuildTable(rows) {
        dt.clear();

        if (!rows || rows.length === 0) {
            dt.draw();
            return;
        }

        rows.forEach(function (row, idx) {
            var bal      = parseInt(row.current_balance) || 0;
            var balClass = bal > 0 ? 'bg-success' : (bal === 0 ? 'bg-warning' : 'bg-danger');
            var lastDate = row.last_transaction_date
                ? formatDate(row.last_transaction_date)
                : '—';

            dt.row.add([
                idx + 1,
                '<strong>' + esc(row.item_name) + '</strong><br>'
                    + '<small class="text-muted">' + row.transaction_count + ' txn(s) &bull; ' + row.location_count + ' location(s)</small>',
                badge(row.total_receipt,      'bg-primary-subtle text-primary'),
                badge(row.total_issued,       'bg-danger-subtle text-danger'),
                badge(row.transferred_qty,    'bg-warning-subtle text-warning'),
                badge(row.deleted_receipt,    'bg-secondary-subtle text-secondary'),
                badge(row.working_receipt,    'bg-success-subtle text-success'),
                badge(row.not_working_receipt,'bg-danger-subtle text-danger'),
                badge(row.repaired_receipt,   'bg-info-subtle text-info'),
                badge(row.pending_receipt,    'bg-warning-subtle text-dark'),
                '<span class="badge ' + balClass + ' fs-13 px-2">' + bal + '</span>',
                '<small class="text-muted">' + esc(row.locations_list || '—') + '</small>',
                '<small class="text-muted">' + lastDate + '</small>',
            ]);
        });

        dt.draw();
    }

    /* ── Summary cards ── */
    function updateSummaryCards(t) {
        animCount('card-receipt',     t.total_receipt);
        animCount('card-issued',      t.total_issued);
        animCount('card-transferred', t.transferred_qty);
        animCount('card-deleted',     t.deleted_receipt);
        animCount('card-working',     t.working_receipt);
        animCount('card-balance',     t.current_balance);
    }

    function animCount(id, newVal) {
        var el = document.getElementById(id);
        if (!el) return;
        var old = parseInt(el.textContent) || 0;
        var diff = newVal - old;
        if (diff === 0) return;
        var steps = 20, step = 0;
        var iv = setInterval(function () {
            step++;
            el.textContent = Math.round(old + (diff * step / steps));
            if (step >= steps) { el.textContent = newVal; clearInterval(iv); }
        }, 20);
    }

    /* ── Footer ── */
    function updateFooter(t) {
        setText('foot-receipt',      t.total_receipt);
        setText('foot-issued',       t.total_issued);
        setText('foot-transferred',  t.transferred_qty);
        setText('foot-deleted',      t.deleted_receipt);
        setText('foot-working',      t.working_receipt);
        setText('foot-not-working',  t.not_working_receipt);
        setText('foot-repaired',     t.repaired_receipt);
        setText('foot-pending',      t.pending_receipt);
        setText('foot-balance',      t.current_balance);
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    /* ── Helpers ── */
    function badge(val, cls) {
        return '<span class="badge ' + cls + ' fs-13 px-2 text-center">' + (parseInt(val) || 0) + '</span>';
    }

    function esc(str) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    function formatDate(dateStr) {
        var d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        return ('0' + d.getDate()).slice(-2) + '-'
             + ('0' + (d.getMonth() + 1)).slice(-2) + '-'
             + d.getFullYear();
    }

    function updateTimestamp() {
        var now = new Date();
        document.getElementById('last-refreshed').textContent =
            ('0' + now.getDate()).slice(-2) + '-' +
            ('0' + (now.getMonth() + 1)).slice(-2) + '-' +
            now.getFullYear() + ' ' +
            ('0' + now.getHours()).slice(-2) + ':' +
            ('0' + now.getMinutes()).slice(-2) + ':' +
            ('0' + now.getSeconds()).slice(-2);
    }

    /* ── Clear filters ── */
    window.clearFilters = function () {
        $('#filter-item').val('').trigger('change');
        $('#filter-location').val('').trigger('change');
        refreshData();
    };

    /* ── Export ── */
    window.exportTable = function () {
        if (dt) dt.button('.buttons-excel').trigger();
        else alert('DataTable export not available.');
    };

}());
</script>