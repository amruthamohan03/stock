<div class="page-content">
    <div class="page-container">
        <!-- Stock Entry Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">Stock Book Entry (Enhanced)</h4>
                        <div>
                            <a href="<?= APP_URL; ?>stock/stockBooks" class="btn btn-sm btn-info">
                                <i class="mdi mdi-book-open-variant"></i> View Stock Books
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="resetForm();">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <form id="stockForm" method="post">
                            <!-- ROW 1: Basic Item Selection & Date -->
                            <div class="row">
                                <!-- Indent Reference -->
                                <div class="col-md-3 mb-3">
                                    <label for="indent_id" class="form-label">Indent No <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="indent_id" name="indent_id">
                                        <option value="">-- Select Indent --</option>
                                        <?php
                                        if (!empty($indents)) { 
                                            foreach ($indents as $row) {
                                                echo '<option value="' . $row['id'] . '">Indent No: ' . htmlspecialchars($row['indent_no']) . ' Dated '.$row['indent_date'].'</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <small class="text-muted">Select indent to auto-populate items</small>
                                </div>

                                <!-- Indent Item Selection -->
                                <div class="col-md-3 mb-3">
                                    <label for="indent_item_id" class="form-label">Select Item from Indent <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="indent_item_id" name="indent_item_id" required>
                                        <option value="">-- Select Item --</option>
                                    </select>
                                </div>

                                <!-- Item Selection (Alternative) -->
                                <div class="col-md-3 mb-3">
                                    <label for="item_id" class="form-label">Or Select Item Directly</label>
                                    <select class="form-select select2" id="item_id" name="item_id">
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

                                <!-- Transaction Date -->
                                <div class="col-md-3 mb-3">
                                    <label for="transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="transaction_date" name="transaction_date" 
                                           value="<?= date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <!-- ROW 2: Location Info -->
                            <div class="row">
                                <!-- Storage Location - NOW DROPDOWN FROM ISSUED_TO_MASTER_T -->
                                <div class="col-md-3 mb-3">
                                    <label for="location" class="form-label">Storage Location/Lab <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="location" name="location" required>
                                        <option value="">-- Select Location --</option>
                                        <?php if (!empty($locations)): ?>
                                            <?php foreach ($locations as $loc): ?>
                                                <option value="<?= htmlspecialchars($loc['location_name']) ?>">
                                                    <?= htmlspecialchars($loc['location_name']) ?> (<?= $loc['location_type'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Transaction Type -->
                                <div class="col-md-2 mb-3">
                                    <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="transaction_type" name="transaction_type" required>
                                        <option value="">-- Select --</option>
                                        <option value="BROUGHT_FORWARD">Brought Forward</option>
                                        <option value="RECEIPT">Receipt (Stock In)</option>
                                        <option value="ISSUE">Issue (Stock Out)</option>
                                        <option value="ADJUSTMENT">Adjustment</option>
                                    </select>
                                </div>

                                <!-- Issue To Location (Dropdown from master) -->
                                <div class="col-md-3 mb-3" id="issued_to_location_field" style="display: none;">
                                    <label for="issued_to_location_id" class="form-label">Issued To (Location)</label>
                                    <select class="form-select select2" id="issued_to_location_id" name="issued_to_location_id">
                                        <option value="">-- Select Location --</option>
                                        <?php if (!empty($locations)): ?>
                                            <?php foreach ($locations as $loc): ?>
                                                <option value="<?= $loc['id'] ?>">
                                                    <?= htmlspecialchars($loc['location_name']) ?> (<?= $loc['location_type'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Item Status -->
                                <div class="col-md-2 mb-3" id="item_status_field" style="display: none;">
                                    <label for="item_status" class="form-label">Item Status</label>
                                    <select class="form-select" id="item_status" name="item_status">
                                        <option value="WORKING">WORKING</option>
                                        <option value="NOT WORKING">NOT WORKING</option>
                                        <option value="DELETED">DELETED</option>
                                        <option value="REPAIRED">REPAIRED</option>
                                        <option value="PENDING">PENDING</option>
                                    </select>
                                </div>

                                <!-- Brought Forward -->
                                <div class="col-md-2 mb-3" id="brought_forward_field" style="display: none;">
                                    <label for="brought_forward" class="form-label">Brought Forward</label>
                                    <input type="number" class="form-control" id="brought_forward" name="brought_forward" 
                                           min="0" value="0" placeholder="Opening balance">
                                </div>
                            </div>

                            <!-- ROW 3: Quantities -->
                            <div class="row">
                                <!-- Received From -->
                                <div class="col-md-3 mb-3" id="received_from_field" style="display: none;">
                                    <label for="received_from" class="form-label">Received From (Supplier)</label>
                                    <input type="text" class="form-control" id="received_from" name="received_from" 
                                           placeholder="Vendor/Supplier Name">
                                </div>

                                <!-- Receipt Quantity (from Indent) -->
                                <div class="col-md-2 mb-3" id="receipt_qty_field" style="display: none;">
                                    <label for="receipt_qty" class="form-label">Receipt Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="receipt_qty" name="receipt_qty" 
                                           min="0" value="0" placeholder="Qty">
                                    <small class="text-muted" id="indent_qty_hint"></small>
                                </div>

                                <!-- Issue Quantity -->
                                <div class="col-md-2 mb-3" id="issue_qty_field" style="display: none;">
                                    <label for="issue_qty" class="form-label">Issue Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="issue_qty" name="issue_qty" 
                                           min="0" value="0" placeholder="Qty">
                                </div>

                                <!-- Serial Number (Optional) -->
                                <div class="col-md-2 mb-3" id="serial_no_field" style="display: none;">
                                    <label for="serial_no" class="form-label">Serial No</label>
                                    <input type="text" class="form-control" id="serial_no" name="serial_no" 
                                           placeholder="Serial number (optional)">
                                    <small class="text-muted">Optional field</small>
                                </div>

                                <!-- Carried Over -->
                                <div class="col-md-2 mb-3" id="carried_over_field" style="display: none;">
                                    <label for="carried_over" class="form-label">Carried Over</label>
                                    <input type="number" class="form-control" id="carried_over" name="carried_over" 
                                           min="0" value="0" placeholder="Closing balance">
                                </div>
                            </div>

                            <!-- ROW 4: Voucher & Additional Info -->
                            <div class="row">
                                <!-- Voucher No -->
                                <div class="col-md-2 mb-3">
                                    <label for="voucher_no" class="form-label">Voucher/Invoice No</label>
                                    <input type="text" class="form-control" id="voucher_no" name="voucher_no" 
                                           placeholder="Invoice No">
                                </div>

                                <!-- Voucher Date -->
                                <div class="col-md-2 mb-3">
                                    <label for="voucher_date" class="form-label">Voucher Date</label>
                                    <input type="date" class="form-control" id="voucher_date" name="voucher_date">
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
                                    <textarea class="form-control" id="remarks" name="remarks" rows="1"
                                              placeholder="Additional notes"></textarea>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-content-save"></i> Record Transaction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Stock Transactions - ITEM WISE -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title">Recent Stock Transactions (Item Wise)</h4>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($transactionsByItem)): ?>
                            <div class="accordion" id="transactionAccordion">
                                <?php 
                                $itemIndex = 0;
                                foreach ($transactionsByItem as $itemId => $itemData): 
                                    $itemIndex++;
                                ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?= $itemIndex; ?>">
                                            <button class="accordion-button <?= $itemIndex > 1 ? 'collapsed' : ''; ?>" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#collapse<?= $itemIndex; ?>" 
                                                    aria-expanded="<?= $itemIndex == 1 ? 'true' : 'false'; ?>">
                                                <strong><?= htmlspecialchars($itemData['item_name']); ?></strong>
                                                <span class="badge bg-primary ms-2"><?= count($itemData['transactions']); ?> Transactions</span>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $itemIndex; ?>" class="accordion-collapse collapse <?= $itemIndex == 1 ? 'show' : ''; ?>" 
                                             data-bs-parent="#transactionAccordion">
                                            <div class="accordion-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Date</th>
                                                                <th>Location</th>
                                                                <th>Type</th>
                                                                <th>Indent</th>
                                                                <th>Serial No</th>
                                                                <th>Status</th>
                                                                <th class="text-center">Receipt</th>
                                                                <th class="text-center">Issue</th>
                                                                <th class="text-center">Balance</th>
                                                                <th>Remarks</th>
                                                                <th class="text-center">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($itemData['transactions'] as $trans): ?>
                                                                <tr>
                                                                    <td><small><?= $trans['id']; ?></small></td>
                                                                    <td><small><?= date('d-m-Y', strtotime($trans['transaction_date'])); ?></small></td>
                                                                    <td><small><?= htmlspecialchars($trans['location']); ?></small></td>
                                                                    <td>
                                                                        <span class="badge bg-<?= 
                                                                            $trans['transaction_type'] == 'RECEIPT' ? 'success' : 
                                                                            ($trans['transaction_type'] == 'ISSUE' ? 'danger' : 'warning')
                                                                        ?> fs-6">
                                                                            <?= substr($trans['transaction_type'], 0, 3); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><small><?= htmlspecialchars($trans['indent_no'] ?? '-'); ?></small></td>
                                                                    <td>
                                                                        <?php if (!empty($trans['serial_no'])): ?>
                                                                            <span class="badge bg-info fs-6"><?= htmlspecialchars($trans['serial_no']); ?></span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-<?= 
                                                                            $trans['item_status'] == 'WORKING' ? 'success' : 'warning'
                                                                        ?> fs-6">
                                                                            <?= substr($trans['item_status'], 0, 3); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?= $trans['receipt_qty'] > 0 ? '<span class="text-success fw-bold">' . $trans['receipt_qty'] . '</span>' : '-'; ?>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <?= $trans['issue_qty'] > 0 ? '<span class="text-danger fw-bold">' . $trans['issue_qty'] . '</span>' : '-'; ?>
                                                                    </td>
                                                                    <td class="text-center"><strong><?= $trans['balance_qty']; ?></strong></td>
                                                                    <td><small><?= htmlspecialchars(substr($trans['remarks'] ?? '', 0, 30)); ?></small></td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-sm btn-warning editTransBtn" 
                                                                                data-id="<?= $trans['id']; ?>" title="Edit"
                                                                                data-bs-toggle="modal" data-bs-target="#editTransactionModal">
                                                                            <i class="ti ti-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-danger deleteTransBtn" 
                                                                                data-id="<?= $trans['id']; ?>" title="Delete">
                                                                            <i class="ti ti-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" role="alert">
                                <i class="mdi mdi-information"></i> No transactions recorded yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransactionLabel">Edit Stock Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTransactionForm" method="post">
                        <input type="hidden" id="edit_transaction_id" name="transaction_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_item_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_item_name" name="edit_item_name" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_transaction_type" class="form-label">Transaction Type</label>
                                <input type="text" class="form-control" id="edit_transaction_type" name="edit_transaction_type" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_transaction_date" name="transaction_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_voucher_no" class="form-label">Voucher/Invoice No</label>
                                <input type="text" class="form-control" id="edit_voucher_no" name="voucher_no">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_voucher_date" class="form-label">Voucher Date</label>
                                <input type="date" class="form-control" id="edit_voucher_date" name="voucher_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_receipt_qty" class="form-label">Receipt Qty</label>
                                <input type="number" class="form-control" id="edit_receipt_qty" name="receipt_qty" min="0" value="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_issue_qty" class="form-label">Issue Qty</label>
                                <input type="number" class="form-control" id="edit_issue_qty" name="issue_qty" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_serial_no" class="form-label">Serial No</label>
                                <input type="text" class="form-control" id="edit_serial_no" name="serial_no">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_item_status" class="form-label">Item Status</label>
                                <select class="form-select" id="edit_item_status" name="item_status">
                                    <option value="WORKING">WORKING</option>
                                    <option value="NOT WORKING">NOT WORKING</option>
                                    <option value="DELETED">DELETED</option>
                                    <option value="REPAIRED">REPAIRED</option>
                                    <option value="PENDING">PENDING</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_issued_to_location_id" class="form-label">Issued To (Location)</label>
                                <select class="form-select" id="edit_issued_to_location_id" name="issued_to_location_id">
                                    <option value="">-- Select Location --</option>
                                    <?php if (!empty($locations)): ?>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= $loc['id'] ?>">
                                                <?= htmlspecialchars($loc['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_received_from" class="form-label">Received From</label>
                                <input type="text" class="form-control" id="edit_received_from" name="received_from">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_receiver_initial" class="form-label">Receiver Initial</label>
                                <input type="text" class="form-control" id="edit_receiver_initial" name="receiver_initial">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="edit_remarks" name="remarks" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateTransaction();">Update Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Load indents on page load
    loadAvailableIndents();

    // Load indent items when indent is selected
    $('#indent_id').change(function() {
        const indent_id = $(this).val();
        if (indent_id) {
            $.ajax({
                url: '<?= APP_URL; ?>stock/getIndentItems?indent_id=' + indent_id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        let options = '<option value="">-- Select Item --</option>';
                        res.data.forEach(item => {
                            options += `<option value="${item.id}" data-qty="${item.qty_intended}">
                                ${item.item_name} (Qty: ${item.qty_intended})
                            </option>`;
                        });
                        $('#indent_item_id').html(options).select2();
                    }
                }
            });
        } else {
            $('#indent_item_id').html('<option value="">-- Select Item --</option>').select2();
        }
    });

    // Auto-populate receipt qty from indent when item is selected
    $('#indent_item_id').change(function() {
        const qty = $(this).find('option:selected').data('qty');
        const item_id = $(this).val();
        
        if (qty) {
            $('#receipt_qty').val(qty);
            $('#indent_qty_hint').text('Intended Qty: ' + qty);
        }
        
        if (item_id) {
            $('#item_id').val(item_id).trigger('change');
        }
    });

    // Handle transaction type changes
    $('#transaction_type').change(function() {
        const type = $(this).val();
        
        // Hide all conditional fields
        $('#brought_forward_field, #received_from_field, #receipt_qty_field, #issue_qty_field, #carried_over_field, #serial_no_field, #item_status_field, #issued_to_location_field').hide();
        
        $('#receipt_qty, #issue_qty').prop('required', false);
        
        if (type === 'BROUGHT_FORWARD') {
            $('#brought_forward_field, #carried_over_field, #serial_no_field, #item_status_field').show();
        } else if (type === 'RECEIPT') {
            $('#received_from_field, #receipt_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
            $('#receipt_qty').prop('required', true);
        } else if (type === 'ISSUE') {
            $('#issued_to_location_field, #issue_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
            $('#issue_qty').prop('required', true);
        } else if (type === 'ADJUSTMENT') {
            $('#receipt_qty_field, #issue_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
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
                    let options = '<option value="">-- Select Indent --</option>';
                    res.data.forEach(indent => {
                        options += `<option value="${indent.id}">
                            ${indent.indent_no} | Book: ${indent.book_no} | ${indent.college_name}
                        </option>`;
                    });
                    $('#indent_id').html(options).select2();
                }
            }
        });
    }

    // Reset form
    function resetForm() {
        $('#stockForm')[0].reset();
        $('#transaction_type').trigger('change');
        $('#item_status').val('WORKING');
        $('#brought_forward').val(0);
        $('#carried_over').val(0);
    }

    // Submit form
    $('#stockForm').submit(function(e) {
        e.preventDefault();
        
        // Validate item selection
        const item_id = $('#item_id').val() || $('#indent_item_id').val();
        if (!item_id) {
            Swal.fire('Error!', 'Please select an item', 'error');
            return;
        }
        
        // Set item_id if indent item is selected
        if (!$('#item_id').val() && $('#indent_item_id').val()) {
            $('#item_id').val($('#indent_item_id').find('option:selected').data('item-id'));
        }
        
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

    // Edit transaction button click
    $(document).on('click', '.editTransBtn', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?= APP_URL; ?>stock/getTransaction?id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const trans = res.data;
                    $('#edit_transaction_id').val(trans.id);
                    $('#edit_item_name').val(trans.item_name);
                    $('#edit_transaction_type').val(trans.transaction_type);
                    $('#edit_transaction_date').val(trans.transaction_date);
                    $('#edit_voucher_no').val(trans.voucher_no || '');
                    $('#edit_voucher_date').val(trans.voucher_date || '');
                    $('#edit_receipt_qty').val(trans.receipt_qty);
                    $('#edit_issue_qty').val(trans.issue_qty);
                    $('#edit_serial_no').val(trans.serial_no || '');
                    $('#edit_item_status').val(trans.item_status);
                    $('#edit_issued_to_location_id').val(trans.issued_to_location_id || '');
                    $('#edit_received_from').val(trans.received_from || '');
                    $('#edit_receiver_initial').val(trans.receiver_initial || '');
                    $('#edit_remarks').val(trans.remarks || '');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load transaction details', 'error');
            }
        });
    });

    // Update transaction
    function updateTransaction() {
        $.ajax({
            url: '<?= APP_URL; ?>stock/updateTransaction',
            type: 'POST',
            data: $('#editTransactionForm').serialize(),
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Updating...',
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
    }

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