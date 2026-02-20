<div class="page-content">
    <div class="page-container">
        <!-- Create Indent Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <h4 class="header-title">Create New Indent</h4>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="$('#indentForm')[0].reset(); $('#itemsTableBody').html('');">
                            <i class="mdi mdi-refresh"></i> Reset
                        </button>
                    </div>

                    <div class="card-body">
                        <form id="indentForm" method="post">
                            <!-- Header Information -->
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="institution_id" name="institution_id" required>
                                        <option value="">-- Select Institution --</option>
                                        <?php if (!empty($institutions)): ?>
                                            <?php foreach ($institutions as $inst): ?>
                                                <option value="<?= $inst['id'] ?>">
                                                    <?= htmlspecialchars($inst['college_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="book_no" class="form-label">Book No <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="book_no" name="book_no" min="1" required>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="indent_no" class="form-label">Indent No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="indent_no" name="indent_no" required>
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label for="item_type" class="form-label">Item Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="item_type" name="item_type" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="C">Consumable</option>
                                        <option value="N">Non-Consumable</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="indent_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="indent_date" name="indent_date" required>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="purpose" class="form-label">Purpose</label>
                                    <textarea class="form-control" id="purpose" name="purpose" rows="2" 
                                        placeholder="Please sanction the issue of the following materials for use in..."></textarea>
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Items</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="itemsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50px;">Sl.No</th>
                                                    <th style="width: 150px;">Item <span class="text-danger">*</span></th>
                                                    <th style="width: 120px;">Make</th>
                                                    <th style="width: 120px;">Model</th>
                                                    <th>Description</th>
                                                    <th>Purpose</th>
                                                    <th style="width: 80px;">Qty <span class="text-danger">*</span></th>
                                                    <th>Remarks</th>
                                                    <th style="width: 60px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTableBody">
                                                <!-- Dynamic rows will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                                        <i class="mdi mdi-plus"></i> Add Item
                                    </button>
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Indent
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indent List -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="header-title">Indent List</h4>
                    </div>

                    <div class="card-body">
                        <table id="indent-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Book No</th>
                                    <th>Indent No</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Institution</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr id="indentRow_<?= $row['id']; ?>">
                                            <td><?= $row['id']; ?></td>
                                            <td><?= $row['book_no']; ?></td>
                                            <td><?= $row['indent_no']; ?></td>
                                            <td>
                                                <?php 
                                                    $itemType = ($row['item_type']=='C')? 'Consumable':'Non-Consumable';
                                                    $typeColor = ($row['item_type'] ?? '') == 'Consumable' ? 'success' : 'warning';
                                                ?>
                                                <span class="badge bg-<?= $typeColor ?>"><?= htmlspecialchars($itemType ?? 'N/A'); ?></span>
                                            </td>
                                            <td><?= date('d-m-Y', strtotime($row['indent_date'])); ?></td>
                                            <td><?= htmlspecialchars($row['college_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'CREATED' => 'secondary',
                                                    'VERIFIED' => 'info',
                                                    'PASSED' => 'primary',
                                                    'ISSUED' => 'warning',
                                                    'RECEIVED' => 'success'
                                                ];
                                                $color = $statusColors[$row['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>"><?= $row['status']; ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($row['created_by_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="<?= APP_URL; ?>indent/viewIndent/<?= $row['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                
                                                <?php if ($row['status'] == 'CREATED'): ?>
                                                    <button class="btn btn-sm btn-success verifyBtn" 
                                                            data-id="<?= $row['id']; ?>" title="Verify">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($row['status'] == 'VERIFIED'): ?>
                                                    <button class="btn btn-sm btn-primary passBtn" 
                                                            data-id="<?= $row['id']; ?>" title="Pass">
                                                        <i class="ti ti-check-double"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($row['status'] == 'PASSED'): ?>
                                                    <button class="btn btn-sm btn-warning issueBtn" 
                                                            data-id="<?= $row['id']; ?>" title="Issue">
                                                        <i class="ti ti-package"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($row['status'] == 'CREATED'): ?>
                                                    <button class="btn btn-sm btn-danger deleteIndentBtn" 
                                                            data-id="<?= $row['id']; ?>" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No indents found</td>
                                    </tr>
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

<!-- Pass Indent Modal (for entering qty_passed) -->
<div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Pass Indent - Enter Quantities</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="passForm">
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty Intended</th>
                                <th>Qty Passed</th>
                            </tr>
                        </thead>
                        <tbody id="passItemsBody">
                            <!-- Will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Pass Indent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Issue Indent Modal (for entering qty_issued) -->
<div class="modal fade" id="issueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Issue Indent - Enter Quantities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="issueForm">
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty Passed</th>
                                <th>Qty Issued</th>
                            </tr>
                        </thead>
                        <tbody id="issueItemsBody">
                            <!-- Will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Issue Indent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let itemCounter = 0;
    
    // Items dropdown data (from PHP)
    const items = <?= json_encode($items ?? []); ?>;
    const makes = <?= json_encode($makes ?? []); ?>;
    
    // Initialize DataTable
    if (!$.fn.DataTable.isDataTable('#indent-datatable')) {
        $('#indent-datatable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "order": [[0, 'ASC']]
        });
    }
    
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }
    
    // Set today's date as default
    $('#indent_date').val(new Date().toISOString().split('T')[0]);
    
    // Add Item Row
    $('#addItemBtn').click(function() {
        itemCounter++;
        let itemOptions = '<option value="">-- Select Item --</option>';
        items.forEach(item => {
            itemOptions += `<option value="${item.id}">${item.item_name}</option>`;
        });
        
        let makeOptions = '<option value="">-- Select Make --</option>';
        makes.forEach(make => {
            makeOptions += `<option value="${make.id}">${make.make_name}</option>`;
        });
        
        const row = `
            <tr class="item-row">
                <td><input type="number" class="form-control form-control-sm" name="items[${itemCounter}][sl_no]" value="${itemCounter}" readonly></td>
                <td>
                    <select class="form-select form-select-sm item-select" name="items[${itemCounter}][item_id]" required>
                        ${itemOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm make-select" name="items[${itemCounter}][make_id]" data-counter="${itemCounter}">
                        ${makeOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm model-select" name="items[${itemCounter}][model_id]" data-counter="${itemCounter}">
                        <option value="">-- Select Model --</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="items[${itemCounter}][item_description]"></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${itemCounter}][item_purpose]"></td>
                <td><input type="number" class="form-control form-control-sm" name="items[${itemCounter}][qty_intended]" min="1" required></td>
                <td><input type="text" class="form-control form-control-sm" name="items[${itemCounter}][remarks]"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="ti ti-trash"></i></button></td>
            </tr>
        `;
        
        $('#itemsTableBody').append(row);
    });
    
    // Remove Item Row
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
    });
    
    // Cascading Dropdown: Make -> Model
    $(document).on('change', '.make-select', function() {
        const makeId = $(this).val();
        const counter = $(this).data('counter');
        const modelSelect = $(`.model-select[data-counter="${counter}"]`);
        
        if (makeId) {
            $.ajax({
                url: '<?= APP_URL; ?>indent/getModelsByMake',
                type: 'GET',
                data: { make_id: makeId },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        let options = '<option value="">-- Select Model --</option>';
                        res.data.forEach(model => {
                            options += `<option value="${model.id}">${model.model_name}</option>`;
                        });
                        modelSelect.html(options);
                    }
                }
            });
        } else {
            modelSelect.html('<option value="">-- Select Model --</option>');
        }
    });
    
    // Submit Indent Form
    $('#indentForm').submit(function(e) {
        e.preventDefault();
        
        // Check if at least one item is added
        if ($('.item-row').length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Items',
                text: 'Please add at least one item to the indent'
            });
            return;
        }
        
        $.ajax({
            url: '<?= APP_URL; ?>indent/crudData/insertion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred: ' + xhr.responseText
                });
            }
        });
    });
    
    // Verify Indent
    $(document).on('click', '.verifyBtn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Verify Indent?',
            text: "This will mark the indent as verified",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Verify!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= APP_URL; ?>indent/verifyIndent',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Verified!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Pass Indent (with qty_passed entry)
    $(document).on('click', '.passBtn', function() {
        const id = $(this).data('id');
        
        // Get indent items
        $.ajax({
            url: '<?= APP_URL; ?>indent/getIndentById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const items = res.data.items;
                    let rows = '';
                    items.forEach(item => {
                        rows += `
                            <tr>
                                <td>
                                    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                                    ${item.item_description || 'Item #' + item.item_id}
                                </td>
                                <td>${item.qty_intended}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[${item.id}][qty_passed]" 
                                           value="${item.qty_intended}" 
                                           min="0" max="${item.qty_intended}" required>
                                </td>
                            </tr>
                        `;
                    });
                    $('#passItemsBody').html(rows);
                    $('#passModal').data('indent-id', id).modal('show');
                }
            }
        });
    });
    
    // Submit Pass Form
    $('#passForm').submit(function(e) {
        e.preventDefault();
        const indentId = $('#passModal').data('indent-id');
        
        $.ajax({
            url: '<?= APP_URL; ?>indent/passIndent',
            type: 'POST',
            data: $(this).serialize() + '&id=' + indentId,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Passed!',
                        text: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#passModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }
        });
    });
    
    // Issue Indent (with qty_issued entry)
    $(document).on('click', '.issueBtn', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '<?= APP_URL; ?>indent/getIndentById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const items = res.data.items;
                    let rows = '';
                    items.forEach(item => {
                        rows += `
                            <tr>
                                <td>
                                    <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
                                    ${item.item_description || 'Item #' + item.item_id}
                                </td>
                                <td>${item.qty_passed}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[${item.id}][qty_issued]" 
                                           value="${item.qty_passed}" 
                                           min="0" max="${item.qty_passed}" required>
                                </td>
                            </tr>
                        `;
                    });
                    $('#issueItemsBody').html(rows);
                    $('#issueModal').data('indent-id', id).modal('show');
                }
            }
        });
    });
    
    // Submit Issue Form
    $('#issueForm').submit(function(e) {
        e.preventDefault();
        const indentId = $('#issueModal').data('indent-id');
        
        $.ajax({
            url: '<?= APP_URL; ?>indent/issueIndent',
            type: 'POST',
            data: $(this).serialize() + '&id=' + indentId,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Issued!',
                        text: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#issueModal').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }
        });
    });
    
    // Delete Indent
    $(document).on('click', '.deleteIndentBtn', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= APP_URL; ?>indent/crudData/deletion?id=' + id,
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
                                $('#indentRow_' + id).fadeOut(500, function() {
                                    $(this).remove();
                                });
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