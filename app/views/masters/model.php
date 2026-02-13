<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Add New Model</h4>
                    </div>

                    <div class="card-body">
                        <form id="modelInsertForm" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="make_id" class="form-label">Make <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="make_id" name="make_id" required>
                                        <option value="">-- Select Make --</option>
                                        <?php if (!empty($makes)): ?>
                                            <?php foreach ($makes as $make): ?>
                                                <option value="<?= htmlspecialchars($make['id']) ?>">
                                                    <?= htmlspecialchars($make['make_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="model_name" class="form-label">Model Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="model_name" name="model_name"
                                        placeholder="Enter model name (e.g., Camry, Accord)" required>
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
                                    <i class="mdi mdi-content-save"></i> Save Model
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div> <!-- end card-body -->

                    <div class="card-body">
                        <h4 class="header-title">Models List</h4>

                        <table id="model-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Make</th>
                                    <th>Model Name</th>
                                    <th>Display</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr id="modelRow_<?= $row['id']; ?>">
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['make_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['model_name']); ?></td>
                                            <td>
                                                <?php if($row['display'] === 'Y'): ?>
                                                    <span class="badge bg-success">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d-m-Y', strtotime($row['created_at'])); ?></td>
                                            <td><?= date('d-m-Y', strtotime($row['updated_at'])); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary editModelBtn"
                                                    data-id="<?= $row['id']; ?>" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger deleteModelBtn"
                                                    data-id="<?= $row['id']; ?>" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No models found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div> <!-- end card body-->

                </div> <!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div> <!-- end container -->

    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Edit Model Modal -->
<div class="modal fade" id="modelEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="modelUpdateForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Model</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Make -->
                    <div class="mb-3">
                        <label class="form-label">Make <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="make_id" id="make_id_edit" required>
                            <option value="">-- Select Make --</option>
                            <?php if (!empty($makes)): ?>
                                <?php foreach ($makes as $make): ?>
                                    <option value="<?= htmlspecialchars($make['id']) ?>">
                                        <?= htmlspecialchars($make['make_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Model Name -->
                    <div class="mb-3">
                        <label class="form-label">Model Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="model_name" id="model_name_edit" required>
                    </div>

                    <!-- Display -->
                    <div class="mb-3">
                        <label class="form-label">Display</label>
                        <select class="form-select" name="display" id="display_edit">
                            <option value="Y">Yes</option>
                            <option value="N">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-content-save"></i> Update Model
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable with unique ID
        if (!$.fn.DataTable.isDataTable('#model-datatable')) {
            $('#model-datatable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "searching": true
            });
        }

        // Initialize Select2 for dropdowns
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%',
                placeholder: function(){
                    $(this).data('placeholder');
                }
            });
        }

        // Insert Form Submission
        $('#modelInsertForm').on('submit', function(e) {
            e.preventDefault();

            // Validate make selection
            if (!$('#make_id').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a Make'
                });
                return;
            }

            $.ajax({
                url: '<?php echo APP_URL; ?>model/crudData/insertion',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if(res.success) {
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
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred: ' + error
                    });
                }
            });
        });

        // Open Edit Modal and load data
        $(document).on('click', '.editModelBtn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: '<?php echo APP_URL; ?>model/getModelById',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if(res.success) {
                        const data = res.data;
                        $('#make_id_edit').val(data.make_id).trigger('change');
                        $('#model_name_edit').val(data.model_name);
                        $('#display_edit').val(data.display);
                        $('#modelEditModal').data('id', id).modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error fetching model data.'
                    });
                }
            });
        });

        // Submit Update Form
        $('#modelUpdateForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#modelEditModal').data('id');

            // Validate make selection
            if (!$('#make_id_edit').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please select a Make'
                });
                return;
            }

            $.ajax({
                url: '<?php echo APP_URL; ?>model/crudData/updation?id=' + id,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if(res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            $('#modelEditModal').modal('hide');
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
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error updating model.'
                    });
                }
            });
        });

        // Delete Model
        $(document).on('click', '.deleteModelBtn', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?php echo APP_URL; ?>model/crudData/deletion?id=' + id,
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if(res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: res.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    $('#modelRow_' + id).fadeOut(500, function() {
                                        $(this).remove();
                                    });
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Error deleting model.'
                            });
                        }
                    });
                }
            });
        });

    });
</script>