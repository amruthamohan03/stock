<style>
    .color-preview {
        width: 25px;
        height: 25px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }
    .bg-purple { background-color: #6f42c1 !important; }
    .bg-teal { background-color: #20c997 !important; }
    .bg-pink { background-color: #e83e8c !important; }
    .icon-preview {
        font-size: 1.2rem;
        width: 30px;
        text-align: center;
    }
    .badge-category {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .card-preview {
        padding: 15px;
        border-radius: 8px;
        color: white;
        margin-top: 10px;
    }
    .menu-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 12px;
    }
</style>

<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center">
                        <h4 class="header-title"><i class="bi bi-grid-fill me-2"></i>Add New Dashboard Card</h4>
                    </div>

                    <div class="card-body">
                        <form id="cardInsertForm" method="post">
                            <div class="row">
                                <!-- Card Key -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_key" class="form-label">Card Key <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="card_key" name="card_key" 
                                        placeholder="e.g., total_users, import_dashboard" required>
                                    <small class="text-muted">Unique identifier (lowercase, underscores)</small>
                                </div>

                                <!-- Card Title -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_title" class="form-label">Card Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="card_title" name="card_title" 
                                        placeholder="e.g., Total Users" required>
                                </div>

                                <!-- Card Subtitle -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_subtitle" class="form-label">Card Subtitle</label>
                                    <input type="text" class="form-control" id="card_subtitle" name="card_subtitle" 
                                        placeholder="e.g., Active Users">
                                </div>

                                <!-- Page/Menu Selection -->
                                <div class="col-md-4 mb-3">
                                    <label for="menu_id" class="form-label">Page <span class="text-danger">*</span></label>
                                    <select class="form-select" id="menu_id" name="menu_id" required>
                                        <option value="">-- Select Page --</option>
                                        <?php foreach ($menus as $menu): ?>
                                            <option value="<?= $menu['id'] ?>"><?= htmlspecialchars($menu['menu_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select the page where this card will appear</small>
                                </div>

                                <!-- Card Icon -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_icon" class="form-label">Card Icon</label>
                                    <div class="input-group">
                                        <span class="input-group-text icon-preview" id="iconPreview"><i class="bi bi-card-text"></i></span>
                                        <input type="text" class="form-control" id="card_icon" name="card_icon" 
                                            placeholder="e.g., bi-people-fill" value="bi-card-text">
                                    </div>
                                    <small class="text-muted">Bootstrap Icons class</small>
                                </div>

                                <!-- Card Color -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_color" class="form-label">Card Color</label>
                                    <select class="form-select" id="card_color" name="card_color">
                                        <?php foreach ($colors as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $key === 'primary' ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Card Category -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_category" class="form-label">Category</label>
                                    <select class="form-select" id="card_category" name="card_category">
                                        <?php foreach ($categories as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Card URL -->
                                <div class="col-md-4 mb-3">
                                    <label for="card_url" class="form-label">Card URL</label>
                                    <input type="text" class="form-control" id="card_url" name="card_url" 
                                        placeholder="e.g., /users or /import-dashboard">
                                </div>

                                <!-- Card Order -->
                                <div class="col-md-2 mb-3">
                                    <label for="card_order" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="card_order" name="card_order" 
                                        placeholder="e.g., 1, 2, 3" value="0" min="0">
                                </div>

                                <!-- Display -->
                                <div class="col-md-2 mb-3">
                                    <label for="display" class="form-label">Display</label>
                                    <select class="form-select" id="display" name="display">
                                        <option value="Y" selected>Yes (Active)</option>
                                        <option value="N">No (Inactive)</option>
                                    </select>
                                </div>

                                <!-- Data Source -->
                                <div class="col-md-12 mb-3">
                                    <label for="data_source" class="form-label">Data Source (Optional)</label>
                                    <textarea class="form-control" id="data_source" name="data_source" rows="2"
                                        placeholder="SQL query or API endpoint for card data (optional)"></textarea>
                                </div>
                            </div>

                            <!-- Card Preview -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Card Preview</label>
                                    <div class="card-preview bg-primary" id="cardPreview">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-card-text me-3" style="font-size: 2rem;" id="previewIcon"></i>
                                            <div>
                                                <h5 class="mb-0" id="previewTitle">Card Title</h5>
                                                <small id="previewSubtitle">Card Subtitle</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Card
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Cards List -->
                    <div class="card-body border-top">
                        <h4 class="header-title mb-3"><i class="bi bi-list-ul me-2"></i>Dashboard Cards List</h4>

                        <table id="cardsTable" class="table table-striped table-hover dt-responsive nowrap w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th width="4%">ID</th>
                                    <th width="10%">Key</th>
                                    <th width="10%">Title</th>
                                    <th width="10%">Page</th>
                                    <th width="8%">Icon</th>
                                    <th width="7%">Color</th>
                                    <th width="8%">Category</th>
                                    <th width="10%">URL</th>
                                    <th width="5%">Order</th>
                                    <th width="6%">Status</th>
                                    <th width="8%">Updated</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($result)): ?>
                                    <?php foreach ($result as $row): ?>
                                        <tr id="cardRow_<?= $row['id'] ?>">
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><code><?= htmlspecialchars($row['card_key']) ?></code></td>
                                            <td><?= htmlspecialchars($row['card_title']) ?></td>
                                            <td>
                                                <?php if (!empty($row['menu_name'])): ?>
                                                    <span class="badge menu-badge bg-info">
                                                        <i class="bi bi-file-earmark-text me-1"></i><?= htmlspecialchars($row['menu_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <i class="<?= htmlspecialchars($row['card_icon']) ?> text-<?= htmlspecialchars($row['card_color']) ?>"></i>
                                                <small class="text-muted d-block"><?= htmlspecialchars($row['card_icon']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= htmlspecialchars($row['card_color']) ?>">
                                                    <?= ucfirst(htmlspecialchars($row['card_color'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-category bg-secondary">
                                                    <?= ucfirst(htmlspecialchars($row['card_category'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['card_url'])): ?>
                                                    <a href="<?= htmlspecialchars($row['card_url']) ?>" target="_blank" class="text-primary">
                                                        <?= htmlspecialchars($row['card_url']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($row['card_order']) ?></td>
                                            <td>
                                                <?php if ($row['display'] === 'Y'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d-m-Y', strtotime($row['updated_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary editCardBtn" 
                                                    data-id="<?= $row['id'] ?>" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger deleteCardBtn" 
                                                    data-id="<?= $row['id'] ?>" 
                                                    data-title="<?= htmlspecialchars($row['card_title']) ?>" 
                                                    title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">No dashboard cards found</td>
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

<!-- Edit Card Modal -->
<div class="modal fade" id="cardEditModal" tabindex="-1" aria-labelledby="cardEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="cardUpdateForm">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cardEditModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Dashboard Card
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Card Key -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="card_key" id="card_key_edit" required>
                        </div>

                        <!-- Card Title -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="card_title" id="card_title_edit" required>
                        </div>

                        <!-- Card Subtitle -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Subtitle</label>
                            <input type="text" class="form-control" name="card_subtitle" id="card_subtitle_edit">
                        </div>

                        <!-- Page/Menu Selection -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Page <span class="text-danger">*</span></label>
                            <select class="form-select" name="menu_id" id="menu_id_edit" required>
                                <option value="">-- Select Page --</option>
                                <?php foreach ($menus as $menu): ?>
                                    <option value="<?= $menu['id'] ?>"><?= htmlspecialchars($menu['menu_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Card Icon -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Icon</label>
                            <div class="input-group">
                                <span class="input-group-text icon-preview" id="iconPreviewEdit"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" name="card_icon" id="card_icon_edit">
                            </div>
                        </div>

                        <!-- Card Color -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card Color</label>
                            <select class="form-select" name="card_color" id="card_color_edit">
                                <?php foreach ($colors as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Card Category -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="card_category" id="card_category_edit">
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Card URL -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Card URL</label>
                            <input type="text" class="form-control" name="card_url" id="card_url_edit">
                        </div>

                        <!-- Card Order -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="card_order" id="card_order_edit" min="0">
                        </div>

                        <!-- Display -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Display</label>
                            <select class="form-select" name="display" id="display_edit">
                                <option value="Y">Yes (Active)</option>
                                <option value="N">No (Inactive)</option>
                            </select>
                        </div>

                        <!-- Data Source -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Data Source</label>
                            <textarea class="form-control" name="data_source" id="data_source_edit" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Update Card
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#cardsTable').DataTable({
        responsive: true,
        order: [[8, 'asc']], // Order by card_order
        pageLength: 10,
        language: {
            emptyTable: "No dashboard cards found"
        }
    });

    // Live preview for icon
    $('#card_icon').on('input', function() {
        const iconClass = $(this).val() || 'bi-card-text';
        $('#iconPreview').html('<i class="' + iconClass + '"></i>');
        $('#previewIcon').attr('class', iconClass + ' me-3').css('font-size', '2rem');
    });

    // Live preview for color
    $('#card_color').on('change', function() {
        const color = $(this).val();
        $('#cardPreview').removeClass().addClass('card-preview bg-' + color);
    });

    // Live preview for title
    $('#card_title').on('input', function() {
        $('#previewTitle').text($(this).val() || 'Card Title');
    });

    // Live preview for subtitle
    $('#card_subtitle').on('input', function() {
        $('#previewSubtitle').text($(this).val() || 'Card Subtitle');
    });

    // Icon preview in edit modal
    $('#card_icon_edit').on('input', function() {
        const iconClass = $(this).val() || 'bi-card-text';
        $('#iconPreviewEdit').html('<i class="' + iconClass + '"></i>');
    });

    // Insert Form Submission
    $('#cardInsertForm').on('submit', function(e) {
        e.preventDefault();

        // Validate menu_id
        if (!$('#menu_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select a page for the card'
            });
            return;
        }

        $.ajax({
            url: '<?= APP_URL ?>/dashboardCard/crudData/insertion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
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
                    text: 'An error occurred while saving the card'
                });
            }
        });
    });

    // Edit Button Click
    $(document).on('click', '.editCardBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.ajax({
            url: '<?= APP_URL ?>/dashboardCard/crudData/getById',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    const data = res.data;

                    $('#card_key_edit').val(data.card_key);
                    $('#card_title_edit').val(data.card_title);
                    $('#card_subtitle_edit').val(data.card_subtitle);
                    $('#card_icon_edit').val(data.card_icon);
                    $('#iconPreviewEdit').html('<i class="' + (data.card_icon || 'bi-card-text') + '"></i>');
                    $('#card_color_edit').val(data.card_color);
                    $('#card_category_edit').val(data.card_category);
                    $('#menu_id_edit').val(data.menu_id);
                    $('#card_url_edit').val(data.card_url);
                    $('#card_order_edit').val(data.card_order);
                    $('#display_edit').val(data.display);
                    $('#data_source_edit').val(data.data_source);

                    $('#cardEditModal').data('id', id).modal('show');
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
                    text: 'Failed to load card data'
                });
            }
        });
    });

    // Update Form Submission
    $('#cardUpdateForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#cardEditModal').data('id');

        // Validate menu_id
        if (!$('#menu_id_edit').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please select a page for the card'
            });
            return;
        }

        $.ajax({
            url: '<?= APP_URL ?>/dashboardCard/crudData/updation?id=' + id,
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#cardEditModal').modal('hide');
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
                    text: 'Failed to update card'
                });
            }
        });
    });

    // Delete Button Click
    $(document).on('click', '.deleteCardBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const title = $(this).data('title');

        Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete <strong>"${title}"</strong>.<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= APP_URL ?>/dashboardCard/crudData/deletion?id=' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                $('#cardRow_' + id).fadeOut(500, function() {
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
                            text: 'Failed to delete card'
                        });
                    }
                });
            }
        });
    });

    // Format card_key on input (lowercase, underscores)
    $('#card_key, #card_key_edit').on('input', function() {
        let value = $(this).val().toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
        $(this).val(value);
    });
});
</script>