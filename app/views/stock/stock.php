<div class="page-content">
    <div class="page-container">

        <!-- ====================================================
             STOCK ENTRY FORM
             ==================================================== -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">Stock Book Entry</h4>
                        <div>
                            <a href="<?= APP_URL; ?>stock/stockBooks" class="btn btn-sm btn-info">
                                <i class="mdi mdi-book-open-variant"></i> View Stock Books
                            </a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="resetForm()">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <form id="stockForm" method="post">

                            <div class="row">

                                <!-- Indent Reference -->
                                <div class="col-md-3 mb-3">
                                    <label for="indent_id" class="form-label">Indent No</label>
                                    <select class="form-select select2" id="indent_id" name="indent_id">
                                        <option value="">-- Select Indent --</option>
                                        <?php foreach ($indents as $row): ?>
                                            <option value="<?= $row['id'] ?>">
                                                Indent No: <?= htmlspecialchars($row['indent_no']) ?> Dated <?= $row['indent_date'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select indent to auto-fill item</small>
                                </div>

                                <!-- Indent Item -->
                                <div class="col-md-3 mb-3">
                                    <label for="indent_item_id" class="form-label">Item from Indent</label>
                                    <select class="form-select select2" id="indent_item_id" name="indent_item_id">
                                        <option value="">-- Select Item --</option>
                                    </select>
                                </div>

                                <!-- Direct Item Selection -->
                                <div class="col-md-3 mb-3">
                                    <label for="item_id" class="form-label">Or Select Item Directly <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="item_id" name="item_id">
                                        <option value="">-- Select Item --</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?= $item['id'] ?>">
                                                <?= htmlspecialchars($item['item_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Transaction Date -->
                                <div class="col-md-3 mb-3">
                                    <label for="transaction_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="transaction_date"
                                           name="transaction_date" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <!-- Storage Location -->
                                <div class="col-md-3 mb-3">
                                    <label for="location" class="form-label">Storage Location/Lab <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="location" name="location" required>
                                        <option value="">-- Select Location --</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= htmlspecialchars($loc['location_name']) ?>">
                                                <?= htmlspecialchars($loc['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Transaction Type -->
                                <div class="col-md-3 mb-3">
                                    <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="transaction_type" name="transaction_type" required>
                                        <option value="">-- Select --</option>
                                        <option value="BROUGHT_FORWARD">Brought Forward</option>
                                        <option value="RECEIPT">Receipt (Stock In)</option>
                                        <option value="ISSUE">Issue (Stock Out)</option>
                                        <option value="TRANSFER">Transfer Received (Stock In)</option>
                                        <option value="ADJUSTMENT">Adjustment</option>
                                    </select>
                                </div>

                                <!-- ── ISSUE: Issued To Location ──────────────────── -->
                                <div class="col-md-3 mb-3 type-field" id="issued_to_location_field" style="display:none;">
                                    <label for="issued_to_location_id" class="form-label">Issued To (Location) <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="issued_to_location_id" name="issued_to_location_id">
                                        <option value="">-- Select Location --</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= $loc['id'] ?>">
                                                <?= htmlspecialchars($loc['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- ── TRANSFER: Transferred From Location ────────── -->
                                <div class="col-md-3 mb-3 type-field" id="transferred_from_field" style="display:none;">
                                    <label for="transferred_from_location_id" class="form-label">
                                        Transferred From (Location) <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2" id="transferred_from_location_id" name="transferred_from_location_id">
                                        <option value="">-- Select Source Location --</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= $loc['id'] ?>">
                                                <?= htmlspecialchars($loc['location_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Location that sent these items to us</small>
                                </div>

                                <!-- Item Status -->
                                <div class="col-md-3 mb-3 type-field" id="item_status_field" style="display:none;">
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
                                <div class="col-md-3 mb-3 type-field" id="brought_forward_field" style="display:none;">
                                    <label for="brought_forward" class="form-label">Brought Forward</label>
                                    <input type="number" class="form-control" id="brought_forward"
                                           name="brought_forward" min="0" value="0">
                                </div>

                                <!-- Received From (RECEIPT / TRANSFER) -->
                                <div class="col-md-3 mb-3 type-field" id="received_from_field" style="display:none;">
                                    <label for="received_from" class="form-label">Received From (Supplier)</label>
                                    <input type="text" class="form-control" id="received_from"
                                           name="received_from" placeholder="Vendor / Supplier Name">
                                </div>

                                <!-- Receipt Qty (RECEIPT / TRANSFER / ADJUSTMENT) -->
                                <div class="col-md-3 mb-3 type-field" id="receipt_qty_field" style="display:none;">
                                    <label for="receipt_qty" class="form-label">Receipt Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="receipt_qty"
                                           name="receipt_qty" min="0" value="0">
                                    <small class="text-muted" id="indent_qty_hint"></small>
                                </div>

                                <!-- Issue Qty (ISSUE / ADJUSTMENT) -->
                                <div class="col-md-3 mb-3 type-field" id="issue_qty_field" style="display:none;">
                                    <label for="issue_qty" class="form-label">Issue Qty <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="issue_qty"
                                           name="issue_qty" min="0" value="0">
                                </div>

                                <!-- Serial No -->
                                <div class="col-md-3 mb-3 type-field" id="serial_no_field" style="display:none;">
                                    <label for="serial_no" class="form-label">Serial No</label>
                                    <input type="text" class="form-control" id="serial_no"
                                           name="serial_no" placeholder="Optional">
                                </div>

                                <!-- Carried Over -->
                                <div class="col-md-3 mb-3 type-field" id="carried_over_field" style="display:none;">
                                    <label for="carried_over" class="form-label">Carried Over</label>
                                    <input type="number" class="form-control" id="carried_over"
                                           name="carried_over" min="0" value="0">
                                </div>

                                <!-- Voucher No -->
                                <div class="col-md-3 mb-3">
                                    <label for="voucher_no" class="form-label">Voucher / Invoice No</label>
                                    <input type="text" class="form-control" id="voucher_no"
                                           name="voucher_no" placeholder="Invoice No">
                                </div>

                                <!-- Voucher Date -->
                                <div class="col-md-3 mb-3">
                                    <label for="voucher_date" class="form-label">Voucher Date</label>
                                    <input type="date" class="form-control" id="voucher_date" name="voucher_date">
                                </div>

                                <!-- Receiver Initial -->
                                <div class="col-md-3 mb-3">
                                    <label for="receiver_initial" class="form-label">Receiver Initial / Name</label>
                                    <input type="text" class="form-control" id="receiver_initial"
                                           name="receiver_initial" placeholder="Who received">
                                </div>

                                <!-- Remarks -->
                                <div class="col-md-5 mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="1"
                                              placeholder="Additional notes"></textarea>
                                </div>

                            </div><!-- /row -->

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-content-save"></i> Record Transaction
                                </button>
                            </div>
                        </form>
                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div>
        </div>

        <!-- ====================================================
             RECENT TRANSACTIONS (Item Wise Accordion)
             ==================================================== -->
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
                                    <h2 class="accordion-header" id="heading<?= $itemIndex ?>">
                                        <button class="accordion-button <?= $itemIndex > 1 ? 'collapsed' : '' ?>"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?= $itemIndex ?>"
                                                aria-expanded="<?= $itemIndex == 1 ? 'true' : 'false' ?>">
                                            <strong><?= htmlspecialchars($itemData['item_name']) ?></strong>
                                            <span class="badge bg-primary ms-2"><?= count($itemData['transactions']) ?> Transactions</span>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $itemIndex ?>"
                                         class="accordion-collapse collapse <?= $itemIndex == 1 ? 'show' : '' ?>"
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
                                                            <th>Transferred From / Issued To</th>
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
                                                            <td><small><?= $trans['id'] ?></small></td>
                                                            <td><small><?= date('d-m-Y', strtotime($trans['transaction_date'])) ?></small></td>
                                                            <td><small><?= htmlspecialchars($trans['location']) ?></small></td>
                                                            <td>
                                                                <?php
                                                                $typeCss = match($trans['transaction_type']) {
                                                                    'RECEIPT'        => 'success',
                                                                    'ISSUE'          => 'danger',
                                                                    'TRANSFER'       => 'info',
                                                                    'BROUGHT_FORWARD'=> 'secondary',
                                                                    default          => 'warning'
                                                                };
                                                                $typeLabel = match($trans['transaction_type']) {
                                                                    'RECEIPT'        => 'REC',
                                                                    'ISSUE'          => 'ISS',
                                                                    'TRANSFER'       => 'TRF',
                                                                    'BROUGHT_FORWARD'=> 'B/F',
                                                                    default          => 'ADJ'
                                                                };
                                                                ?>
                                                                <span class="badge bg-<?= $typeCss ?>"><?= $typeLabel ?></span>
                                                            </td>
                                                            <td><small><?= htmlspecialchars($trans['indent_no'] ?? '-') ?></small></td>
                                                            <td>
                                                                <small>
                                                                <?php if ($trans['transaction_type'] === 'TRANSFER' && !empty($trans['transferred_from_location_name'])): ?>
                                                                    <span class="text-info"><i class="mdi mdi-arrow-right"></i> From: <?= htmlspecialchars($trans['transferred_from_location_name']) ?></span>
                                                                <?php elseif ($trans['transaction_type'] === 'ISSUE' && !empty($trans['issued_to_location_name'])): ?>
                                                                    <span class="text-danger"><i class="mdi mdi-arrow-left"></i> To: <?= htmlspecialchars($trans['issued_to_location_name']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($trans['serial_no'])): ?>
                                                                    <span class="badge bg-info"><?= htmlspecialchars($trans['serial_no']) ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $stCss = $trans['item_status'] === 'WORKING' ? 'success' : ($trans['item_status'] === 'DELETED' ? 'secondary' : 'warning');
                                                                ?>
                                                                <span class="badge bg-<?= $stCss ?>"><?= substr($trans['item_status'], 0, 3) ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= $trans['receipt_qty'] > 0 ? '<span class="text-success fw-bold">' . $trans['receipt_qty'] . '</span>' : '-' ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?= $trans['issue_qty'] > 0 ? '<span class="text-danger fw-bold">' . $trans['issue_qty'] . '</span>' : '-' ?>
                                                            </td>
                                                            <td class="text-center"><strong><?= $trans['balance_qty'] ?></strong></td>
                                                            <td><small><?= htmlspecialchars(substr($trans['remarks'] ?? '', 0, 30)) ?></small></td>
                                                            <td class="text-center">
                                                                <button class="btn btn-sm btn-warning editTransBtn"
                                                                        data-id="<?= $trans['id'] ?>"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editTransactionModal"
                                                                        title="Edit">
                                                                    <i class="ti ti-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger deleteTransBtn"
                                                                        data-id="<?= $trans['id'] ?>" title="Delete">
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
                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i> No transactions recorded yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /page-container -->

    <!-- ====================================================
         EDIT TRANSACTION MODAL
         ==================================================== -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1"
         aria-labelledby="editTransactionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransactionLabel">Edit Stock Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editTransactionForm" method="post">
                        <input type="hidden" id="edit_transaction_id" name="transaction_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="edit_item_name" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Type</label>
                                <input type="text" class="form-control" id="edit_transaction_type" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_transaction_date"
                                       name="transaction_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Voucher / Invoice No</label>
                                <input type="text" class="form-control" id="edit_voucher_no" name="voucher_no">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Voucher Date</label>
                                <input type="date" class="form-control" id="edit_voucher_date" name="voucher_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Receipt Qty</label>
                                <input type="number" class="form-control" id="edit_receipt_qty"
                                       name="receipt_qty" min="0" value="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issue Qty</label>
                                <input type="number" class="form-control" id="edit_issue_qty"
                                       name="issue_qty" min="0" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Serial No</label>
                                <input type="text" class="form-control" id="edit_serial_no" name="serial_no">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Status</label>
                                <select class="form-select" id="edit_item_status" name="item_status">
                                    <option value="WORKING">WORKING</option>
                                    <option value="NOT WORKING">NOT WORKING</option>
                                    <option value="DELETED">DELETED</option>
                                    <option value="REPAIRED">REPAIRED</option>
                                    <option value="PENDING">PENDING</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issued To (Location)</label>
                                <select class="form-select" id="edit_issued_to_location_id" name="issued_to_location_id">
                                    <option value="">-- Select Location --</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>">
                                            <?= htmlspecialchars($loc['location_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Transferred From (shown for TRANSFER type) -->
                        <div class="row" id="edit_transfer_row" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transferred From (Location)</label>
                                <select class="form-select" id="edit_transferred_from_location_id"
                                        name="transferred_from_location_id">
                                    <option value="">-- Select Source Location --</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>">
                                            <?= htmlspecialchars($loc['location_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Received From</label>
                                <input type="text" class="form-control" id="edit_received_from" name="received_from">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Receiver Initial</label>
                                <input type="text" class="form-control" id="edit_receiver_initial" name="receiver_initial">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" id="edit_remarks" name="remarks" rows="3"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="updateTransaction()">
                        <i class="mdi mdi-content-save"></i> Update Transaction
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div><!-- /page-content -->

<!-- ====================================================
     JAVASCRIPT
     ==================================================== -->
<script>

// ── Global helpers (must be outside $(document).ready so onclick="" can find them) ──

/**
 * FIX: resetForm was inside $(document).ready – now global.
 */
function resetForm() {
    $('#stockForm')[0].reset();
    $('#transaction_type').val('').trigger('change');
    $('#item_status').val('WORKING');
    $('#brought_forward').val(0);
    $('#carried_over').val(0);
    $('#indent_qty_hint').text('');
}

/**
 * FIX: updateTransaction was inside $(document).ready – now global.
 * Submits the edit modal form via AJAX.
 */
function updateTransaction() {
    $.ajax({
        url      : '<?= APP_URL ?>stock/updateTransaction',
        type     : 'POST',
        data     : $('#editTransactionForm').serialize(),
        dataType : 'json',
        beforeSend: function () {
            Swal.fire({ title: 'Updating…', allowOutsideClick: false,
                        didOpen: () => Swal.showLoading() });
        },
        success: function (res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Success!', text: res.message,
                            showConfirmButton: false, timer: 1500
                }).then(() => location.reload());
            } else {
                Swal.fire('Error!', res.message, 'error');
            }
        },
        error: function (xhr) {
            // Show actual server error text if available for easier debugging
            const msg = xhr.responseText
                ? 'Server error: ' + xhr.responseText.substring(0, 200)
                : 'Something went wrong. Please try again.';
            Swal.fire('Error!', msg, 'error');
        }
    });
}

// ─────────────────────────────────────────────────────────────────────────────

$(document).ready(function () {

    // ── Indent → Items cascade ───────────────────────────────────────────────
    loadAvailableIndents();

    $('#indent_id').change(function () {
        const indent_id = $(this).val();
        if (indent_id) {
            $.ajax({
                url      : '<?= APP_URL ?>stock/getIndentItems?indent_id=' + indent_id,
                type     : 'GET',
                dataType : 'json',
                success  : function (res) {
                    if (res.success) {
                        let opts = '<option value="">-- Select Item --</option>';
                        res.data.forEach(item => {
                            // FIX: added data-item-id attribute so submitForm can resolve item_id
                            opts += `<option value="${item.id}"
                                             data-qty="${item.qty_intended}"
                                             data-item-id="${item.item_id}">
                                        ${item.item_name} (Qty: ${item.qty_intended})
                                     </option>`;
                        });
                        $('#indent_item_id').html(opts).select2();
                    }
                }
            });
        } else {
            $('#indent_item_id').html('<option value="">-- Select Item --</option>').select2();
        }
    });

    $('#indent_item_id').change(function () {
        const selected = $(this).find('option:selected');
        const qty      = selected.data('qty');
        const itemId   = selected.data('item-id');   // FIX: use data-item-id

        if (qty)    { $('#receipt_qty').val(qty); $('#indent_qty_hint').text('Intended Qty: ' + qty); }
        if (itemId) { $('#item_id').val(itemId).trigger('change'); }
    });

    // ── Transaction type → show/hide contextual fields ───────────────────────
    $('#transaction_type').change(function () {
        const type = $(this).val();

        // Hide everything first
        $('.type-field').hide();
        $('#receipt_qty, #issue_qty').prop('required', false);

        // Show per type
        if (type === 'BROUGHT_FORWARD') {
            $('#brought_forward_field, #carried_over_field, #serial_no_field, #item_status_field').show();

        } else if (type === 'RECEIPT') {
            $('#received_from_field, #receipt_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
            $('#receipt_qty').prop('required', true);

        } else if (type === 'ISSUE') {
            $('#issued_to_location_field, #issue_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
            $('#issue_qty').prop('required', true);

        } else if (type === 'TRANSFER') {
            // TRANSFER = stock IN from another location
            $('#transferred_from_field, #receipt_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
            $('#receipt_qty').prop('required', true);

        } else if (type === 'ADJUSTMENT') {
            $('#receipt_qty_field, #issue_qty_field, #carried_over_field, #serial_no_field, #item_status_field').show();
        }
    });

    // ── Load available indents ───────────────────────────────────────────────
    function loadAvailableIndents() {
        $.ajax({
            url      : '<?= APP_URL ?>stock/getAvailableIndents',
            type     : 'GET',
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    let opts = '<option value="">-- Select Indent --</option>';
                    res.data.forEach(i => {
                        opts += `<option value="${i.id}">
                                    ${i.indent_no} | Book: ${i.book_no} | ${i.college_name}
                                 </option>`;
                    });
                    $('#indent_id').html(opts).select2();
                }
            }
        });
    }

    // ── Submit new transaction ───────────────────────────────────────────────
    $('#stockForm').submit(function (e) {
        e.preventDefault();

        // Resolve item_id: indent selection takes precedence
        let itemId = $('#item_id').val();
        if (!itemId && $('#indent_item_id').val()) {
            // FIX: use data-item-id (now correctly set in options)
            itemId = $('#indent_item_id').find('option:selected').data('item-id');
            $('#item_id').val(itemId);
        }
        if (!itemId) {
            Swal.fire('Error!', 'Please select an item', 'error');
            return;
        }

        // Validate ISSUE must have issued_to_location
        if ($('#transaction_type').val() === 'ISSUE' && !$('#issued_to_location_id').val()) {
            Swal.fire('Error!', 'Please select the location to issue to', 'error');
            return;
        }

        // Validate TRANSFER must have transferred_from_location
        if ($('#transaction_type').val() === 'TRANSFER' && !$('#transferred_from_location_id').val()) {
            Swal.fire('Error!', 'Please select the location transferred from', 'error');
            return;
        }

        $.ajax({
            url      : '<?= APP_URL ?>stock/createTransaction',
            type     : 'POST',
            data     : $(this).serialize(),
            dataType : 'json',
            beforeSend: function () {
                Swal.fire({ title: 'Processing…', allowOutsideClick: false,
                            didOpen: () => Swal.showLoading() });
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Success!', text: res.message,
                                showConfirmButton: false, timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseText
                    ? 'Server error: ' + xhr.responseText.substring(0, 200)
                    : 'Something went wrong. Please try again.';
                Swal.fire('Error!', msg, 'error');
            }
        });
    });

    // ── Open Edit modal and populate ────────────────────────────────────────
    $(document).on('click', '.editTransBtn', function () {
        const id = $(this).data('id');

        $.ajax({
            url      : '<?= APP_URL ?>stock/getTransaction?id=' + id,
            type     : 'GET',
            dataType : 'json',
            success  : function (res) {
                if (res.success) {
                    const t = res.data;
                    $('#edit_transaction_id').val(t.id);
                    $('#edit_item_name').val(t.item_name);
                    $('#edit_transaction_type').val(t.transaction_type);
                    $('#edit_transaction_date').val(t.transaction_date);
                    $('#edit_voucher_no').val(t.voucher_no || '');
                    $('#edit_voucher_date').val(t.voucher_date || '');
                    $('#edit_receipt_qty').val(t.receipt_qty);
                    $('#edit_issue_qty').val(t.issue_qty);
                    $('#edit_serial_no').val(t.serial_no || '');
                    $('#edit_item_status').val(t.item_status);
                    $('#edit_issued_to_location_id').val(t.issued_to_location_id || '');
                    $('#edit_received_from').val(t.received_from || '');
                    $('#edit_receiver_initial').val(t.receiver_initial || '');
                    $('#edit_remarks').val(t.remarks || '');

                    // Show/hide transferred_from row based on type
                    if (t.transaction_type === 'TRANSFER') {
                        $('#edit_transfer_row').show();
                        $('#edit_transferred_from_location_id').val(t.transferred_from_location_id || '');
                    } else {
                        $('#edit_transfer_row').hide();
                        $('#edit_transferred_from_location_id').val('');
                    }
                }
            },
            error: function () {
                Swal.fire('Error!', 'Failed to load transaction details', 'error');
            }
        });
    });

    // ── Delete transaction ───────────────────────────────────────────────────
    $(document).on('click', '.deleteTransBtn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text : 'This will recalculate all stock balances!',
            icon : 'warning',
            showCancelButton    : true,
            confirmButtonColor  : '#d33',
            cancelButtonColor   : '#6c757d',
            confirmButtonText   : 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url      : '<?= APP_URL ?>stock/deleteTransaction?id=' + id,
                    type     : 'POST',
                    dataType : 'json',
                    success  : function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: res.message,
                                        showConfirmButton: false, timer: 1500
                            }).then(() => location.reload());
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