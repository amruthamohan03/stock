<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title">Service Providers</h4>
                    </div>

                    <!-- INSERT FORM -->
                    <div class="card-body">
                        <form id="spInsertForm">

                            <div class="row">

                                <!-- Provider Name -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" placeholder="e.g. ABC Pvt Ltd" required>
                                </div>

                                <!-- Type -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="">-- Select Type --</option>
                                        <option value="public">Public</option>
                                        <option value="private">Private</option>
                                    </select>
                                </div>

                                <!-- Phone No -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Phone No. <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone_no" placeholder="+91 9XXXXXXXXX" required>
                                </div>

                                <!-- Email -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" placeholder="contact@provider.com" required>
                                </div>

                                <!-- GST No -->
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">GST No. <small class="text-muted">(optional)</small></label>
                                    <input type="text" class="form-control" name="gst_no" placeholder="29ABCDE1234F1Z5" maxlength="15">
                                </div>

                                <!-- Address -->
                                <div class="col-md-9 mb-3">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="address" placeholder="Street, City, State, PIN" required>
                                </div>

                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Save Provider</button>
                                <button type="reset" class="btn btn-secondary">Cancel</button>
                            </div>

                        </form>
                    </div>

                    <!-- LIST TABLE -->
                    <div class="card-body">
                        <h4 class="header-title">Service Provider List</h4>

                        <table id="sp-datatable" class="table table-striped nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Provider Name</th>
                                    <th>Type</th>
                                    <th>Phone No.</th>
                                    <th>Email</th>
                                    <th>GST No.</th>
                                    <th>Address</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($result)): $i = 1; ?>
                                    <?php foreach ($result as $row): ?>

                                        <tr id="row_<?= $row['id'] ?>">
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($row['name']) ?></td>

                                            <!-- TYPE BADGE -->
                                            <td>
                                                <?php if ($row['type'] === 'public'): ?>
                                                    <span class="badge bg-info text-dark">Public</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Private</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?= htmlspecialchars($row['phone_no']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>

                                            <!-- GST -->
                                            <td>
                                                <?php if (!empty($row['gst_no'])): ?>
                                                    <span class="badge bg-success"><?= htmlspecialchars($row['gst_no']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                <?php endif; ?>
                                            </td>

                                            <td><?= htmlspecialchars($row['address']) ?></td>

                                            <!-- CREATED BY -->
                                            <td><?= htmlspecialchars($row['created_by_name'] ?? '—') ?></td>

                                            <!-- ACTIONS -->
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary editSpBtn" data-id="<?= $row['id'] ?>">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-danger deleteSpBtn" data-id="<?= $row['id'] ?>">
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
<div class="modal fade" id="spEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="spUpdateForm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <!-- Provider Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="type" id="edit_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone_no" id="edit_phone_no" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>

                        <!-- GST No -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST No. <small class="text-muted">(optional)</small></label>
                            <input type="text" class="form-control" name="gst_no" id="edit_gst_no" maxlength="15">
                        </div>

                        <!-- Address -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" id="edit_address" required>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </div>
        </form>
    </div>
</div>


<script>
$(document).ready(function () {

    $('#sp-datatable').DataTable();

    /* ── INSERT ─────────────────────────────────────────── */
    $('#spInsertForm').submit(function (e) {
        e.preventDefault();

        $.post("<?= APP_URL ?>provider/crudData/insertion", $(this).serialize(), function (res) {
            alert(res.message);
            if (res.success) location.reload();
        }, 'json');
    });

    /* ── DELETE ─────────────────────────────────────────── */
    $(document).on('click', '.deleteSpBtn', function (e) {
        e.preventDefault();
        if (!confirm('Delete this service provider?')) return;

        let id = $(this).data('id');

        $.post("<?= APP_URL ?>provider/crudData/deletion?id=" + id, function (res) {
            alert(res.message);
            if (res.success) $('#row_' + id).fadeOut(300, function () { $(this).remove(); });
        }, 'json');
    });

    /* ── OPEN EDIT MODAL ────────────────────────────────── */
    $(document).on('click', '.editSpBtn', function (e) {
        e.preventDefault();
        let id = $(this).data('id');

        $.get("<?= APP_URL ?>provider/getById", { id: id }, function (res) {
            if (res.success) {
                let d = res.data;
                $('#edit_name').val(d.name);
                $('#edit_type').val(d.type);
                $('#edit_phone_no').val(d.phone_no);
                $('#edit_email').val(d.email);
                $('#edit_gst_no').val(d.gst_no ?? '');
                $('#edit_address').val(d.address);

                $('#spEditModal').data('id', id).modal('show');
            } else {
                alert(res.message);
            }
        }, 'json');
    });

    /* ── UPDATE ─────────────────────────────────────────── */
    $('#spUpdateForm').submit(function (e) {
        e.preventDefault();
        let id = $('#spEditModal').data('id');

        $.post("<?= APP_URL ?>provider/crudData/updation?id=" + id, $(this).serialize(), function (res) {
            alert(res.message);
            if (res.success) {
                $('#spEditModal').modal('hide');
                location.reload();
            }
        }, 'json');
    });

});
</script>