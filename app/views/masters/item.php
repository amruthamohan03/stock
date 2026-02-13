<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Item Master</h4>
                    </div>

                    <div class="card-body">
                        <form id="itemInsertForm">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="item_name" required>
                                </div>

                                <!-- CATEGORY DROPDOWN -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">-- Select Category --</option>
                                        <?php if (!empty($categories)): ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- TAX NOT TAX DROPDOWN -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Consumable/ Non-consumable<span class="text-danger">*</span></label>
                                    <select class="form-select" name="tax_not_tax" required>
                                        <option value="C">Consumable</option>
                                        <option value="N">Non-Consumable</option>
                                    </select>
                                </div>
                                <!-- DISPLAY -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Display</label>
                                    <select class="form-select" name="display">
                                        <option value="Y">Yes</option>
                                        <option value="N">No</option>
                                    </select>
                                </div>

                            </div>

                            <div class="text-end">
                                <button class="btn btn-primary">Save Item</button>
                                <a href="" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                    <!-- ITEM LIST -->
                    <div class="card-body">
                        <h4 class="header-title">Item List</h4>

                        <table id="item-datatable" class="table table-striped nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Consumable/ Non-consumable</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($result)): $i=1; ?>
                                    <?php foreach ($result as $row): ?>

                                        <tr id="row_<?= $row['id'] ?>">
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>

                                            <!-- CATEGORY NAME -->
                                            <td>
                                                <?php if (!empty($row['category_name'])): ?>
                                                    <span class='badge bg-info'><?= htmlspecialchars($row['category_name']) ?></span>
                                                <?php else: ?>
                                                    <span class='badge bg-secondary'>Not Set</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- TAX NOT TAX -->
                                            <td>
                                                <?php if ($row['tax_not_tax'] == 'C'): ?>
                                                    <span class="badge bg-success">Consumable</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Non-consumable</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary editItemBtn" data-id="<?= $row['id'] ?>">
                                                    <i class="ti ti-edit"></i>
                                                </a>

                                                <a href="#" class="btn btn-sm btn-danger deleteItemBtn" data-id="<?= $row['id'] ?>">
                                                    <i class="ti ti-trash"></i>
                                                </a>
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

<!-- EDIT MODAL -->
<div class="modal fade" id="itemEditModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="itemUpdateForm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
                    </div>

                    <!-- CATEGORY DROPDOWN -->
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" id="edit_category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- TAX NOT TAX DROPDOWN -->
                    <div class="mb-3">
                        <label class="form-label">Taxable/ Non-taxable<span class="text-danger">*</span></label>
                        <select class="form-select" name="tax_not_tax" id="edit_tax_not_tax" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                        </select>
                    </div>

                    <!-- ITEM TYPE DROPDOWN -->
                    <div class="mb-3">
                        <label class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="item_type" id="edit_item_type" required>
                            <option value="">-- Select Item Type --</option>
                            <?php if (!empty($transactionEnum)): ?>
                                <?php foreach ($transactionEnum as $key => $value): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($value) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Display</label>
                        <select name="display" class="form-select" id="edit_display">
                            <option value="Y">Yes</option>
                            <option value="N">No</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){

    $('#item-datatable').DataTable();

    /* INSERT */
    $('#itemInsertForm').submit(function(e){
        e.preventDefault();

        $.post("<?= APP_URL ?>item/crudData/insertion", $(this).serialize(), function(res){
            alert(res.message);
            if(res.success) location.reload();
        }, 'json');
    });

    /* DELETE */
    $(document).on('click','.deleteItemBtn',function(){
        if(!confirm("Delete this item?")) return;
        let id = $(this).data('id');

        $.post("<?= APP_URL ?>item/crudData/deletion?id="+id, function(res){
            alert(res.message);
            if(res.success) location.reload();
        }, 'json');
    });

    /* OPEN EDIT MODAL */
    $(document).on('click','.editItemBtn',function(e){
        e.preventDefault();
        let id = $(this).data('id');

        $.get("<?= APP_URL ?>item/getItemById",{id:id}, function(res){
            if(res.success){
                let d = res.data;

                $('#edit_item_name').val(d.item_name);
                $('#edit_category_id').val(d.category_id);
                $('#edit_tax_not_tax').val(d.tax_not_tax);
                $('#edit_item_type').val(d.item_type);
                $('#edit_display').val(d.display);

                $('#itemEditModal').data('id', id).modal('show');
            }
        }, 'json');
    });

    /* UPDATE */
    $('#itemUpdateForm').submit(function(e){
        e.preventDefault();

        let id = $('#itemEditModal').data('id');

        $.post("<?= APP_URL ?>item/crudData/updation?id="+id, $(this).serialize(), function(res){
            alert(res.message);
            if(res.success){
                $('#itemEditModal').modal('hide');
                location.reload();
            }
        }, 'json');
    });

});
</script>