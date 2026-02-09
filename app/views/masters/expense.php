<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Expense Type Master</h4>
                    </div>

                    <div class="card-body">
                        <form id="expenseInsertForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expense_type_name" class="form-label">Expense Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="expense_type_name"
                                        name="expense_type_name" placeholder="Enter Expense Type name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type</label><br>
                                    <?php
                                    $types = ['import', 'export', 'local', 'advance', 'other'];
                                    foreach ($types as $t): ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="type[]" 
                                                value="<?= $t ?>" id="insert_type_<?= $t ?>">
                                            <label class="form-check-label" for="insert_type_<?= $t ?>">
                                                <?= ucfirst($t) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="display" class="form-label">Display</label>
                                    <select class="form-select" id="display" name="display">
                                        <option value="Y" selected>Yes</option>
                                        <option value="N">No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> Save Expense Type
                                </button>
                                <button type="reset" class="btn btn-secondary">Clear</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <h4 class="header-title mb-3">Expense Type List</h4>
                        <table id="expense_type-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expense Type Name</th>
                                    <th>Type</th>
                                    <th>Display</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result)): ?>
                                <?php 
                                    $i = 0;
                                    foreach ($result as $row): 
                                    // Build type badges
                                    $typeLabels = [];
                                    $allTypes = ['import', 'export', 'local', 'advance', 'other'];
                                    foreach($allTypes as $t) {
                                        if(!empty($row[$t]) && $row[$t] == 1) {
                                            $typeLabels[] = '<span class="badge bg-info">' . ucfirst($t) . '</span>';
                                        }
                                    }
                                    $typeBadges = !empty($typeLabels) ? implode(' ', $typeLabels) : '<span class="badge bg-secondary">None</span>';
                                ?>
                                <tr>
                                    <td><?php echo  ++$i; ?></td>
                                    <td><?= htmlspecialchars($row['expense_type_name']); ?></td>
                                    <td><?= $typeBadges; ?></td>
                                    <td>
                                        <?php if($row['display'] == 'Y'): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                                    <td><?= htmlspecialchars($row['updated_at']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary editExpenseBtn"
                                            data-id="<?= $row['id']; ?>" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger deleteExpenseBtn"
                                            data-id="<?= $row['id'] ?>" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No records found</td>
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

<!-- Edit Modal -->
<div class="modal fade" id="expense_typeEditModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="expense_typeUpdateForm" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Expense Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="expense_type_name" id="edit_expense_type_name" 
                            class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Type</label><br>
                        <?php
                        $types = ['import', 'export', 'local', 'advance', 'other'];
                        foreach ($types as $t): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="type[]" 
                                    value="<?= $t ?>" id="edit_type_<?= $t ?>">
                                <label class="form-check-label" for="edit_type_<?= $t ?>">
                                    <?= ucfirst($t) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Display</label>
                        <select name="display" id="edit_display" class="form-select">
                            <option value="Y">Yes</option>
                            <option value="N">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-check"></i> Update
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#expense_type-datatable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25
    });

    // Insert Form Submit
    $('#expenseInsertForm').submit(function(e) {
        e.preventDefault();
        
        // Check if at least one type is selected
        const typesSelected = $('input[name="type[]"]:checked').length;
        if (typesSelected === 0) {
            alert('⚠️ Please select at least one type.');
            return false;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>expense/crudData/insertion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Saving...');
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert(res.message);
                    $('button[type="submit"]').prop('disabled', false).html('<i class="mdi mdi-content-save"></i> Save Expense Type');
                }
            },
            error: function(xhr) {
                alert('❌ AJAX Error: ' + xhr.responseText);
                $('button[type="submit"]').prop('disabled', false).html('<i class="mdi mdi-content-save"></i> Save Expense Type');
            }
        });
    });

    // Delete Expense
    $(document).on('click', '.deleteExpenseBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        if (!confirm('⚠️ Are you sure you want to delete this expense type?')) {
            return false;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>expense/crudData/deletion?id=' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                alert(res.message);
                if (res.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('❌ AJAX Error: ' + xhr.responseText);
            }
        });
    });

    // Edit Modal Load
    $(document).on('click', '.editExpenseBtn', function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.ajax({
            url: '<?php echo APP_URL; ?>expense/getExpenseById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                console.log('Response:', res);

                if (res.success) {
                    let data = res.data;

                    // Set name and display
                    $('#edit_expense_type_name').val(data.expense_type_name);
                    $('#edit_display').val(data.display);

                    // Uncheck all checkboxes first
                    $('#expense_typeUpdateForm input[name="type[]"]').prop('checked', false);

                    // Check the appropriate checkboxes based on database values
                    let typeList = ['import', 'export', 'local', 'advance', 'other'];
                    
                    typeList.forEach(t => {
                        // Check if column value is 1
                        if (data[t] == 1) {
                            $('#edit_type_' + t).prop('checked', true);
                        }
                    });

                    // Store ID in modal data attribute and show modal
                    $('#expense_typeEditModal').data('id', id).modal('show');
                } 
                else {
                    alert('❌ ' + res.message);
                }
            },
            error: function(xhr) {
                alert('❌ Error fetching expense type data: ' + xhr.responseText);
            }
        });
    });

    // Update Form Submit
    $('#expense_typeUpdateForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#expense_typeEditModal').data('id');

        // Check if at least one type is selected
        const typesSelected = $('#expense_typeUpdateForm input[name="type[]"]:checked').length;
        if (typesSelected === 0) {
            alert('⚠️ Please select at least one type.');
            return false;
        }

        $.ajax({
            url: '<?php echo APP_URL; ?>expense/crudData/updation?id=' + id,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Updating...');
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    $('#expense_typeEditModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message);
                    $('button[type="submit"]').prop('disabled', false).html('<i class="mdi mdi-check"></i> Update');
                }
            },
            error: function(xhr) {
                alert('❌ AJAX Error: ' + xhr.responseText);
                $('button[type="submit"]').prop('disabled', false).html('<i class="mdi mdi-check"></i> Update');
            }
        });
    });
});
</script>