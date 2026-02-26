<div class="page-content">
    <div class="page-container">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">Stock by Item Group</h4>
                            <p class="text-muted mb-0">View stock distribution across different item groups</p>
                        </div>
                        <a href="<?= APP_URL; ?>stock/stock" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus-circle"></i> New Entry
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <label class="form-label"><strong>Select Item Group</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="groupSelect" class="form-select" onchange="selectGroup()">
                                    <option value="">-- Select a group to view details --</option>
                                    <?php foreach ($itemGroups as $group): ?>
                                        <option value="<?= $group['id']; ?>" <?= $selectedGroupId == $group['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($group['group_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items by Group -->
        <?php if (!empty($items)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <?= htmlspecialchars($itemGroups[array_search($selectedGroupId, array_column($itemGroups, 'id'))]['group_name'] ?? 'Items'); ?>
                            </h5>
                            <span class="badge bg-primary"><?= count($items); ?> items</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th width="12%">Make</th>
                                        <th width="12%">Model</th>
                                        <th width="12%">Location</th>
                                        <th width="10%">Opening Bal</th>
                                        <th width="10%">Current Bal</th>
                                        <th width="12%">Transactions</th>
                                        <th width="8%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalOpening = 0;
                                    $totalCurrent = 0;
                                    $totalTransactions = 0;
                                    foreach ($items as $item): 
                                        $totalOpening += $item['opening_balance'] ?? 0;
                                        $totalCurrent += $item['current_balance'] ?? 0;
                                        $totalTransactions += $item['transaction_count'] ?? 0;
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($item['item_name']); ?></strong></td>
                                            <td><small><?= htmlspecialchars($item['make'] ?? '-'); ?></small></td>
                                            <td><small><?= htmlspecialchars($item['model'] ?? '-'); ?></small></td>
                                            <td><small><?= htmlspecialchars($item['location'] ?? '-'); ?></small></td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    <?= $item['opening_balance'] ?? 0; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    $currentBal = $item['current_balance'] ?? 0;
                                                    $balClass = $currentBal > 0 ? 'success' : ($currentBal == 0 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?= $balClass; ?>">
                                                    <?= $currentBal; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <?= $item['transaction_count'] ?? 0; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($currentBal > 0): ?>
                                                    <span class="badge bg-success">In Stock</span>
                                                <?php elseif ($currentBal == 0): ?>
                                                    <span class="badge bg-warning">Out</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Negative</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4"><strong>TOTALS</strong></td>
                                        <td class="text-center"><strong><?= $totalOpening; ?></strong></td>
                                        <td class="text-center"><strong><?= $totalCurrent; ?></strong></td>
                                        <td class="text-center"><strong><?= $totalTransactions; ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($selectedGroupId > 0): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="mdi mdi-inbox-outline" style="font-size: 3rem;"></i>
                            <p class="mt-3">No items found for this group</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Item Groups</h6>
                        <h3 class="mb-0"><?= count($itemGroups); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Items in Selected Group</h6>
                        <h3 class="mb-0"><?= count($items); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Stock (All Groups)</h6>
                        <h3 class="mb-0 text-success">
                            <?php 
                            $query = "SELECT SUM(current_balance) as total FROM stock_book_t";
                            // Assuming this is available in context
                            echo '0';
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">View Details</h6>
                        <a href="<?= APP_URL; ?>stock/stock//viewAll" class="btn btn-sm btn-primary w-100">
                            <i class="mdi mdi-eye"></i> All Entries
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script>
function selectGroup() {
    const groupId = document.getElementById('groupSelect').value;
    if (groupId) {
        window.location.href = '<?= APP_URL; ?>stock/stock//viewByGroup?group_id=' + groupId;
    }
}
</script>

<style>
table tfoot tr {
    font-weight: bold;
    background-color: #f8f9fa;
}

table tfoot td {
    padding: 1rem;
    border-top: 2px solid #dee2e6;
}
</style>
