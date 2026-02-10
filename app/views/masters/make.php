<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Add New Make</h4>
                    </div>

                    <div class="card-body">
                        <form id="makeInsertForm" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="make_name" class="form-label">Make Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="make_name" name="make_name"
                                        placeholder="Enter make name (e.g., Toyota, Honda)" required>
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
                                    <i class="mdi mdi-content-save"></i> Save Make
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="mdi mdi-refresh"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div> <!-- end card-body -->

                    <div class="card-body">
                        <h4 class="header-title">Makes List</h4>

                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Make Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr id="makeRow_<?= $row['id']; ?>">
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['make_name']); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary editMakeBtn"
                                                    data-id="<?= $row['id']; ?>" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger deleteMakeBtn"
                                                    data-id="<?= $row['id']; ?>" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No makes found</td>
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

<!-- Edit Make Modal -->
<div class="modal fade" id="makeEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="makeUpdateForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Make</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Make Name -->
                    <div class="mb-3">
                        <label class="form-label">Make Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="make_name" id="make_name_edit" required>
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
                        <i class="mdi mdi-content-save"></i> Update Make
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Insert Form Submission
        $('#makeInsertForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?php echo APP_URL; ?>make/crudData/insertion',
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
        $(document).on('click', '.editMakeBtn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: '<?php echo APP_URL; ?>make/getMakeById',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if(res.success) {
                        const data = res.data;
                        $('#make_name_edit').val(data.make_name);
                        $('#display_edit').val(data.display);
                        $('#makeEditModal').data('id', id).modal('show');
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
                        text: 'Error fetching make data.'
                    });
                }
            });
        });

        // Submit Update Form
        $('#makeUpdateForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#makeEditModal').data('id');

            $.ajax({
                url: '<?php echo APP_URL; ?>make/crudData/updation?id=' + id,
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
                            $('#makeEditModal').modal('hide');
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
                        text: 'Error updating make.'
                    });
                }
            });
        });

        // Delete Make
        $(document).on('click', '.deleteMakeBtn', function(e) {
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
                        url: '<?php echo APP_URL; ?>make/crudData/deletion?id=' + id,
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
                                    $('#makeRow_' + id).fadeOut(500, function() {
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
                                text: 'Error deleting make.'
                            });
                        }
                    });
                }
            });
        });

    });
</script>