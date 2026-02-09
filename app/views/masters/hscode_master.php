<?php $types = ['I'=>'IMPORT', 'E'=>'EXPORT']; ?>

<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">

                <!-- Add New HS Code -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Add New HS Code</h4>
                        <div>
                            <a href="<?= APP_URL ?>hscode/downloadTemplate" class="btn btn-success">
                                <i class="ti ti-download"></i> Download Template
                            </a>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                                <i class="ti ti-upload"></i> Import Data
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="hscodeInsertForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>HS Code Number</label>
                                    <input type="text" id="hscode_number" name="hscode_number" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label>DDI</label>
                                    <input type="number" step="0.01" id="hscode_ddi" name="hscode_ddi" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label>ICA</label>
                                    <input type="number" step="0.01" id="hscode_ica" name="hscode_ica" class="form-control" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label>DCI</label>
                                    <input type="number" step="0.01" id="hscode_dci" name="hscode_dci" class="form-control" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label>DCL</label>
                                    <input type="number" step="0.01" id="hscode_dcl" name="hscode_dcl" class="form-control" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label>TPI</label>
                                    <input type="number" step="0.01" id="hscode_tpi" name="hscode_tpi" class="form-control" value="0">
                                </div>
                                <div class="col-md-2">
                                    <label>Display</label>
                                    <select name="display" class="form-select">
                                        <option value="Y" selected>Yes</option>
                                        <option value="N">No</option>
                                    </select>
                                </div>

                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- HS Code List -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4>HS Code List</h4>
                    </div>
                    <div class="card-body">
                        <table id="hscode-datatable" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>HS Code</th>
                                    <th>DDI</th>
                                    <th>ICA</th>
                                    <th>DCI</th>
                                    <th>DCL</th>
                                    <th>TPI</th>
                                    <th>Display</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($result)): $i=1; foreach($result as $row): ?>
                                    <tr id="hscodeRow_<?= $row['id'] ?>">
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['hscode_number']) ?></td>
                                        <td><?= $row['hscode_ddi'] ?></td>
                                        <td><?= $row['hscode_ica'] ?></td>
                                        <td><?= $row['hscode_dci'] ?></td>
                                        <td><?= $row['hscode_dcl'] ?></td>
                                        <td><?= $row['hscode_tpi'] ?></td>
                                        <td><?= $row['display'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editHscodeBtn" data-id="<?= $row['id'] ?>"><i class="ti ti-edit"></i></button>
                                            <button class="btn btn-sm btn-danger deleteHscodeBtn" data-id="<?= $row['id'] ?>"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
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
<div class="modal fade" id="editHscodeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="hscodeUpdateForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit HS Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label>HS Code Number</label>
                            <input type="text" name="hscode_number" id="edit_hscode_number" class="form-control">
                        </div>
                        <div class="col-md-2"><label>DDI</label><input type="number" step="0.01" name="hscode_ddi" id="edit_hscode_ddi" class="form-control"></div>
                        <div class="col-md-2"><label>ICA</label><input type="number" step="0.01" name="hscode_ica" id="edit_hscode_ica" class="form-control"></div>
                        <div class="col-md-2"><label>DCI</label><input type="number" step="0.01" name="hscode_dci" id="edit_hscode_dci" class="form-control"></div>
                        <div class="col-md-2"><label>DCL</label><input type="number" step="0.01" name="hscode_dcl" id="edit_hscode_dcl" class="form-control"></div>
                        <div class="col-md-2"><label>TPI</label><input type="number" step="0.01" name="hscode_tpi" id="edit_hscode_tpi" class="form-control"></div>
                        <div class="col-md-2">
                            <label>Display</label>
                            <select name="display" id="edit_display" class="form-select">
                                <option value="Y">Yes</option>
                                <option value="N">No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-upload"></i> Import HS Code Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download the template first</li>
                            <li>Fill in the data (do not modify headers)</li>
                            <li>Upload the filled Excel file (.xlsx)</li>
                            <li>Duplicate HS Codes will be skipped</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Excel File (.xlsx)</label>
                        <input type="file" name="import_file" id="import_file" class="form-control" accept=".xlsx" required>
                    </div>
                    <div id="importProgress" class="d-none">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <p class="text-center mt-2 mb-0">Processing... Please wait</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="ti ti-upload"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Result Modal -->
<div class="modal fade" id="importResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="importResultBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#hscode-datatable').DataTable();

    // INSERT
    $('#hscodeInsertForm').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url:'<?= APP_URL ?>hscode/crudData/insertion',
            type:'POST',
            data:$(this).serialize(),
            dataType:'json',
            success:function(res){
                if(res.success){ 
                    alert('✅ Inserted Successfully'); 
                    location.reload(); 
                } else {
                    alert('❌ '+res.message);
                    $('#hscode_number').val('');
                    $('#hscode_ddi').val('');
                    $('#hscode_ica').val('0');
                    $('#hscode_dci').val('0');
                    $('#hscode_dcl').val('0');
                    $('#hscode_tpi').val('0');
                    $('#hscode_number').focus();
                }
            }
        });
    });

    // EDIT FETCH
    $(document).on('click','.editHscodeBtn',function(){
        let id = $(this).data('id');
        $.get('<?= APP_URL ?>hscode/getHscodeById',{id:id},function(res){
            if(res.success){
                $('#edit_hscode_number').val(res.data.hscode_number);
                $('#edit_hscode_ddi').val(res.data.hscode_ddi);
                $('#edit_hscode_ica').val(res.data.hscode_ica);
                $('#edit_hscode_dci').val(res.data.hscode_dci);
                $('#edit_hscode_dcl').val(res.data.hscode_dcl);
                $('#edit_hscode_tpi').val(res.data.hscode_tpi);
                $('#edit_display').val(res.data.display);
                $('#editHscodeModal').data('id',id).modal('show');
            } else alert(res.message);
        },'json');
    });

    // UPDATE
    $('#hscodeUpdateForm').on('submit',function(e){
        e.preventDefault();
        let id = $('#editHscodeModal').data('id');
        $.ajax({
            url:'<?= APP_URL ?>hscode/crudData/updation&id='+id,
            type:'POST',
            data:$(this).serialize(),
            dataType:'json',
            success:function(res){
                if(res.success){ 
                    alert('✅ Data Updated Successfully'); 
                    $('#editHscodeModal').modal('hide'); 
                    location.reload(); 
                } else alert('❌ '+res.message);
            }
        });
    });

    // DELETE
    $(document).on('click','.deleteHscodeBtn',function(){
        if(!confirm('Delete this HS Code?')) return;
        let id = $(this).data('id');
        $.post('<?= APP_URL ?>hscode/crudData/deletion&id='+id,function(res){
            if(res.success){ 
                alert('✅ Data Deleted Successfully'); 
                location.reload(); 
            } else alert('❌ '+res.message);
        },'json');
    });

    // IMPORT FORM SUBMIT
    $('#importForm').on('submit', function(e){
        e.preventDefault();
        
        let formData = new FormData(this);
        let fileInput = $('#import_file')[0];
        
        if(fileInput.files.length === 0){
            alert('❌ Please select a file');
            return;
        }
        
        $('#importBtn').prop('disabled', true);
        $('#importProgress').removeClass('d-none');
        
        $.ajax({
            url: '<?= APP_URL ?>hscode/importData',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res){
                $('#importBtn').prop('disabled', false);
                $('#importProgress').addClass('d-none');
                $('#importModal').modal('hide');
                
                let resultHtml = '';
                if(res.success){
                    resultHtml = `
                        <div class="alert alert-success">
                            <i class="ti ti-check"></i> Import completed successfully!
                        </div>
                        <table class="table table-sm table-bordered">
                            <tr><td>Total Rows</td><td><strong>${res.total}</strong></td></tr>
                            <tr class="table-success"><td>Inserted</td><td><strong>${res.inserted}</strong></td></tr>
                            <tr class="table-warning"><td>Skipped (Duplicates)</td><td><strong>${res.skipped}</strong></td></tr>
                        </table>
                    `;
                    if(res.errors && res.errors.length > 0){
                        resultHtml += `<div class="alert alert-warning mt-2"><strong>Errors:</strong><ul class="mb-0">`;
                        res.errors.forEach(function(err){
                            resultHtml += `<li>${err}</li>`;
                        });
                        resultHtml += `</ul></div>`;
                    }
                } else {
                    resultHtml = `
                        <div class="alert alert-danger">
                            <i class="ti ti-x"></i> ${res.message}
                        </div>
                    `;
                }
                
                $('#importResultBody').html(resultHtml);
                $('#importResultModal').modal('show');
            },
            error: function(xhr){
                $('#importBtn').prop('disabled', false);
                $('#importProgress').addClass('d-none');
                alert('❌ Error occurred during import');
            }
        });
    });
    
    // Reset import form when modal closes
    $('#importModal').on('hidden.bs.modal', function(){
        $('#importForm')[0].reset();
        $('#importProgress').addClass('d-none');
    });
});
</script>