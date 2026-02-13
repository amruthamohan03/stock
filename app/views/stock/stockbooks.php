<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">Stock Books Summary</h4>
                        <a href="<?= APP_URL; ?>stock" class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> New Transaction
                        </a>
                    </div>

                    <div class="card-body">
                        <table id="stockbooks-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Name</th>
                                    <th>Location</th>
                                    <th>Opening Balance</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stockBooks)): ?>
                                    <?php foreach ($stockBooks as $stock): ?>
                                        <tr>
                                            <td><?= $stock['id']; ?></td>
                                            <td><strong><?= htmlspecialchars($stock['item_name']); ?></strong></td>
                                            <td><?= htmlspecialchars($stock['location']); ?></td>
                                            <td class="text-center"><?= $stock['opening_balance']; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $stock['current_balance'] > 0 ? 'success' : ($stock['current_balance'] == 0 ? 'secondary' : 'danger') ?> fs-6">
                                                    <?= $stock['current_balance']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($stock['current_balance'] > 0): ?>
                                                    <span class="badge bg-success">In Stock</span>
                                                <?php elseif ($stock['current_balance'] == 0): ?>
                                                    <span class="badge bg-warning">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Negative</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d-m-Y H:i', strtotime($stock['updated_at'])); ?></td>
                                            <td>
                                                <a href="<?= APP_URL; ?>stock/viewLedger/<?= $stock['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Ledger">
                                                    <i class="ti ti-book"></i> View Ledger
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No stock books found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Stock Books</h6>
                        <h3 class="mb-0"><?= count($stockBooks ?? []); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Items in Stock</h6>
                        <h3 class="text-success mb-0">
                            <?php 
                            $inStock = 0;
                            if (!empty($stockBooks)) {
                                foreach ($stockBooks as $s) {
                                    if ($s['current_balance'] > 0) $inStock++;
                                }
                            }
                            echo $inStock;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Out of Stock</h6>
                        <h3 class="text-warning mb-0">
                            <?php 
                            $outOfStock = 0;
                            if (!empty($stockBooks)) {
                                foreach ($stockBooks as $s) {
                                    if ($s['current_balance'] == 0) $outOfStock++;
                                }
                            }
                            echo $outOfStock;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Current Stock</h6>
                        <h3 class="text-primary mb-0">
                            <?php 
                            $totalStock = 0;
                            if (!empty($stockBooks)) {
                                foreach ($stockBooks as $s) {
                                    $totalStock += $s['current_balance'];
                                }
                            }
                            echo $totalStock;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#stockbooks-datatable').DataTable({
        order: [[1, 'asc']]
    });
});
</script>