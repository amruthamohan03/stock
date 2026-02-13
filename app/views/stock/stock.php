<div class="page-content">
    <div class="page-container">
        <!-- Stock Entry Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">Stock Book Entry</h4>
                        <div>
                            <a href="<?= APP_URL; ?>stock/stockBooks" class="btn btn-sm btn-info">
                                <i class="mdi mdi-book-open-variant"></i> View Stock Books
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="$('#stockForm')[0].reset();">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <form id="stockForm" method="post">
                            <div class="row">
                                <!-- Item Selection -->
                                <div class="col-md-4 mb-3">
                                    <label for="item_id" class="form-label">Item/Article Name <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="item_id" name="item_id" required>
                                        <option value="">-- Select Item --</option>
                                        <?php if (!empty($items)): ?>
                                            <?php foreach ($items as $item): ?>
                                                <option value="<?= $item['id'] ?>">
                                                    <?= htmlspecialchars($item['item_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Location -->
                                <div class="col-md-3 mb-3">
                                    <label for="location" class="form-label">Location/Lab <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           placeholder="e.g., C.I.T. LAB, Computer Lab" required>
                                </div>

                                <!-- Transaction Type -->
                                <div class="col-md-3 mb-3">
                                    <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="transaction_type" name="transaction_type" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="RECEIPT">Receipt (Stock In)</option>
                                        <option value="ISSUE">Issue (Stock Out)</option>
                                        <option value="BROUGHT_FORWARD">Brought Forward</option>
                                        <option value="ADJUSTMENT">Adjustment</option>
                                    </select>
                                </div>

                                <!-- Transaction Date -->
                                <div class="col-md-2 mb-3">
                                    <label for="transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                           value="<?= date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Voucher/Invoice Number -->
                                <div class="col-md-3 mb-3">
                                    <label for="voucher_no" class="form-label">Voucher/Invoice No</label>
                                    <input type="text" class="form-control" id="voucher_no" name="voucher_no" 
                                           placeholder="Invoice/Bill No">
                                </div>

                                <!-- Voucher Date -->
                                <div class="col-md-2 mb-3">
                                    <label for="voucher_date" class="form-label">Voucher Date</label>
                                    <input type="date" class="form-control" id="voucher_date" name="voucher_date">
                                </div>

                                <!-- Indent Reference (for Issues) -->
                                <div class="col-md-3 mb-3" id="indent_field" style="display: none;">
                                    <label for="indent_id" class="form-label">Indent Reference</label>
                                    <select class="form-select select2" id="indent_id" name="indent_id">
                                        <option value="">-- Select Indent --</option>
                                    </select>
                                </div>

                                <!-- Received From -->
                                <div class="col-md-4 mb-3" id="received_from_field" style="display: none;">
                                    <label for="received_from" class="form-label">Received From</label>
                                    <input type="text" class="form-control" id="received_from" name="received_from" 
                                           placeholder="Supplier/Vendor Name">
                                </div>

                                <!-- Issued To -->
                                <div class="col-md-4 mb-3" id="issued_to_field" style="display: none;">
                                    <label for="issued_to" class="form-label">Issued To</label>
                                    <input type="text" class="form-control" id="issued_to" name="issued_to" 
                                           placeholder="Department/Person Name">
                                </div>
                            </div>

                            <div class="row">
                                <!-- Receipt Quantity -->
                                <div class="col-md-2 mb-3" id="receipt_qty_field" style="display: none;">
                                    <label for="receipt_qty" class="form-label">Receipt Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="receipt_qty" name="receipt_qty" 
                                           min="0" value="0">
                                </div>

                                <!-- Issue Quantity -->
                                <div class="col-md-2 mb-3" id="issue_qty_field" style="display: none;">
                                    <label for="issue_qty" class="form-label">Issue Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="issue_qty" name="issue_qty" 
                                           min="0" value="0">
                                </div>

                                <!-- Receiver Initial -->
                                <div class="col-md-3 mb-3">
                                    <label for="receiver_initial" class="form-label">Receiver Initial/Name</label>
                                    <input type="text" class="form-control" id="receiver_initial" name="receiver_initial" 
                                           placeholder="Who received">
                                </div>

                                <!-- Remarks -->
                                <div class="col-md-5 mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <input type="text" class="form-control" id="remarks" name="remarks" 
                                           placeholder="Additional notes">
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Record Transaction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions List -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title">Recent Stock Transactions</h4>
                    </div>

                    <div class="card-body">
                        <table id="stock-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Voucher No</th>
                                    <th>From/To</th>
                                    <th>Receipt</th>
                                    <th>Issue</th>
                                    <th>Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $trans): ?>
                                        <tr id="transRow_<?= $trans['id']; ?>">
                                            <td><?= $trans['id']; ?></td>
                                            <td><?= date('d-m-Y', strtotime($trans['transaction_date'])); ?></td>
                                            <td><?= htmlspecialchars($trans['item_name']); ?></td>
                                            <td><?= htmlspecialchars($trans['location']); ?></td>
                                            <td>
                                                <?php
                                                $typeColors = [
                                                    'RECEIPT' => 'success',
                                                    'ISSUE' => 'danger',
                                                    'BROUGHT_FORWARD' => 'info',
                                                    'ADJUSTMENT' => 'warning'
                                                ];
                                                $color = $typeColors[$trans['transaction_type']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>"><?= $trans['transaction_type']; ?></span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($trans['voucher_no'] ?? '-'); ?>
                                                <?php if (!empty($trans['indent_no'])): ?>
                                                    <br><small class="text-muted">Indent: <?= $trans['indent_no']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($trans['transaction_type'] == 'RECEIPT'): ?>
                                                    <small><?= htmlspecialchars($trans['received_from'] ?? '-'); ?></small>
                                                <?php elseif ($trans['transaction_type'] == 'ISSUE'): ?>
                                                    <small><?= htmlspecialchars($trans['issued_to'] ?? '-'); ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $trans['receipt_qty'] > 0 ? '<span class="text-success">' . $trans['receipt_qty'] . '</span>' : '-'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $trans['issue_qty'] > 0 ? '<span class="text-danger">' . $trans['issue_qty'] . '</span>' : '-'; ?>
                                            </td>
                                            <td class="text-center"><strong><?= $trans['balance_qty']; ?></strong></td>
                                            <td>
                                                <a href="<?= APP_URL; ?>stock/viewLedger/<?= $trans['stock_book_id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Ledger">
                                                    <i class="ti ti-book"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger deleteTransBtn" 
                                                        data-id="<?= $trans['id']; ?>" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
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

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#stock-datatable').DataTable({
        order: [[0, 'desc']]
    });

    // Transaction type change event
    $('#transaction_type').change(function() {
        const type = $(this).val();
        
        // Hide all conditional fields first
        $('#indent_field, #received_from_field, #issued_to_field, #receipt_qty_field, #issue_qty_field').hide();
        $('#receipt_qty, #issue_qty').val(0);
        
        if (type === 'RECEIPT' || type === 'BROUGHT_FORWARD') {
            $('#received_from_field, #receipt_qty_field').show();
            $('#receipt_qty').prop('required', true);
        } else if (type === 'ISSUE') {
            $('#indent_field, #issued_to_field, #issue_qty_field').show();
            $('#issue_qty').prop('required', true);
            
            // Load available indents
            loadAvailableIndents();
        } else if (type === 'ADJUSTMENT') {
            $('#receipt_qty_field, #issue_qty_field').show();
        }
    });

    // Load available indents
    function loadAvailableIndents() {
        $.ajax({
            url: '<?= APP_URL; ?>stock/getAvailableIndents',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let options = '<option value="">-- Select Indent (Optional) --</option>';
                    res.data.forEach(indent => {
                        options += `<option value="${indent.id}">
                            Indent No: ${indent.indent_no} | Book: ${indent.book_no} | 
                            ${indent.college_name} | Type: ${indent.item_type}
                        </option>`;
                    });
                    $('#indent_id').html(options);
                }
            }
        });
    }

    // Submit form
    $('#stockForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= APP_URL; ?>stock/createTransaction',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Something went wrong', 'error');
            }
        });
    });

    // Delete transaction
    $(document).on('click', '.deleteTransBtn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will recalculate stock balances!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= APP_URL; ?>stock/deleteTransaction?id=' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
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