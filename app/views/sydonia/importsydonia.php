<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h4>Sydonia Import - Bulk Update</h4>
                    </div>
                    <div class="card-body">

                        <input type="file" id="excelFile" accept=".xlsx, .xls" style="display: none;">

                        <div id="uploadZone" class="upload-zone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5>Drag & Drop Excel File Here</h5>
                            <p class="text-muted">or click to browse</p>
                            <button type="button" class="btn btn-outline-primary mt-2" id="browseBtn">
                                <i class="fas fa-file-excel"></i> Choose Excel File
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Excel Format:</strong><br>
                                    • <strong>Column A:</strong> MCA Ref (Required - must exist in database)<br>
                                    • <strong>Columns B-H:</strong> Optional data fields<br>
                                    • Only non-empty fields will be updated<br>
                                    • Empty fields preserve existing database values
                                </small>
                            </div>
                        </div>

                        <div id="processingIndicator" style="display: none;" class="text-center my-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Validating records against database...</p>
                        </div>

                        <div id="validationResults" style="display: none;" class="mt-4">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Validation Complete:</strong>
                                    <span id="validCount" class="badge bg-success ms-2">0 Valid</span>
                                    <span id="invalidCount" class="badge bg-danger ms-2">0 Invalid</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="resetUpload()">
                                    <i class="fas fa-redo"></i> Upload Another File
                                </button>
                            </div>

                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="12%">MCA Ref</th>
                                            <th>Declaration Ref</th>
                                            <th>Declaration Date</th>
                                            <th>Liquidation Ref</th>
                                            <th>Liquidation Date</th>
                                            <th>Quittance Ref</th>
                                            <th>Quittance Date</th>
                                            <th>Amount</th>
                                            <th width="10%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 text-end">
                                <button type="button" id="submitBtn" class="btn btn-success btn-lg" disabled>
                                    <i class="fas fa-check-circle"></i> Update Records
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<style>
.upload-zone {
    border: 3px dashed #dee2e6;
    border-radius: 10px;
    padding: 60px 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover {
    border-color: #0d6efd;
    background: #e7f1ff;
}

.upload-zone.dragover {
    border-color: #198754;
    background: #d1e7dd;
}

.row-valid {
    background-color: #d1e7dd !important;
}

.row-invalid {
    background-color: #f8d7da !important;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
let validatedData = [];
let validRecords = [];
let invalidRecords = [];

$(document).ready(function () {

    $("#uploadZone").on("dragover", function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("dragover");
    });

    $("#uploadZone").on("dragleave", function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");
    });

    $("#uploadZone").on("drop", function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");
        
        let files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    $("#browseBtn").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        $("#excelFile").click();
    });

    $("#excelFile").on("change", function (e) {
        let file = e.target.files[0];
        if (file) {
            handleFileSelect(file);
        }
    });

    $("#submitBtn").on("click", function() {
        if (validRecords.length === 0) {
            alert("⚠️ No valid records to update.");
            return;
        }

        let message = "Update " + validRecords.length + " valid record" + (validRecords.length > 1 ? "s" : "") + "?";
        
        if (invalidRecords.length > 0) {
            message += "\n\n⚠️ Note: " + invalidRecords.length + " invalid record" + (invalidRecords.length > 1 ? "s" : "") + " will be skipped (red rows).";
        }
        
        message += "\n\nOnly non-empty fields will be updated.";

        if (!confirm(message)) {
            return;
        }

        performUpdate();
    });
});

function handleFileSelect(file) {
    if (!file) return;

    let ext = file.name.split('.').pop().toLowerCase();
    if (ext !== "xlsx" && ext !== "xls") {
        alert("❌ Only Excel files (.xlsx, .xls) are allowed!");
        $("#excelFile").val("");
        return;
    }

    $("#uploadZone").hide();
    $("#processingIndicator").show();

    let reader = new FileReader();

    reader.onload = function (event) {
        try {
            let data = new Uint8Array(event.target.result);
            let workbook = XLSX.read(data, { type: "array" });
            let sheet = workbook.Sheets[workbook.SheetNames[0]];

            let rows = XLSX.utils.sheet_to_json(sheet, {
                header: 1,
                raw: false,
                defval: ""
            });

            console.log("=== EXCEL PARSING ===");
            console.log("Total Rows:", rows.length);

            if (!rows || rows.length === 0) {
                alert("⚠️ Excel file is empty!");
                resetUpload();
                return;
            }

            let startRow = 0;
            if (rows.length > 0 && rows[0][0]) {
                let firstCell = String(rows[0][0]).trim().toUpperCase();
                if ((firstCell.includes("MCA") || firstCell.includes("REF")) && 
                    !firstCell.includes("/") && !firstCell.includes("-")) {
                    startRow = 1;
                    console.log("Header row detected, skipping first row");
                }
            }

            let mappedRows = [];

            for (let i = startRow; i < rows.length; i++) {
                let r = rows[i];
                
                if (!r || r.every(cell => !cell || String(cell).trim() === "")) {
                    continue;
                }

                let mcaRef = String(r[0] ?? "").trim();
                
                if (!mcaRef || mcaRef === "") {
                    console.log("Skipping row " + (i+1) + " - no MCA ref");
                    continue;
                }

                mappedRows.push({
                    mca_ref: mcaRef,
                    declaration_reference: String(r[1] ?? "").trim(),
                    declaration_date: String(r[2] ?? "").trim(),
                    liquidation_reference: String(r[3] ?? "").trim(),
                    liquidation_date: String(r[4] ?? "").trim(),
                    quittance_reference: String(r[5] ?? "").trim(),
                    quittance_date: String(r[6] ?? "").trim(),
                    liquidation_amount: String(r[7] ?? "").trim()
                });
            }

            console.log("Total valid rows to process:", mappedRows.length);

            if (mappedRows.length === 0) {
                alert("⚠️ No valid data found in Excel!\n\nMake sure Column A contains MCA references.");
                resetUpload();
                return;
            }

            validateRecords(mappedRows);

        } catch (error) {
            console.error("Excel parsing error:", error);
            alert("❌ Error reading Excel file:\n" + error.message);
            resetUpload();
        }
    };

    reader.onerror = function() {
        alert("❌ Error reading file!");
        resetUpload();
    };

    reader.readAsArrayBuffer(file);
}

function validateRecords(rows) {
    console.log("Sending to server for validation:", rows);

    $.ajax({
        url: "<?php echo APP_URL; ?>importsydonia/validate_mca_refs",
        method: "POST",
        data: JSON.stringify({ records: rows }),
        contentType: "application/json",
        dataType: "json",
        success: function (res) {
            console.log("Validation response:", res);

            if (!res.success) {
                alert("❌ Validation failed:\n" + (res.msg || "Unknown error"));
                resetUpload();
                return;
            }

            validatedData = rows;
            validRecords = res.valid || [];
            invalidRecords = res.invalid || [];

            displayValidationResults();
        },
        error: function (xhr, status, error) {
            console.error("=== VALIDATION ERROR ===");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response Text:", xhr.responseText);
            
            alert("❌ Validation error! Please check console for details.");
            resetUpload();
        }
    });
}

function displayValidationResults() {
    $("#processingIndicator").hide();
    $("#validationResults").show();

    $("#validCount").text(validRecords.length + " Valid");
    $("#invalidCount").text(invalidRecords.length + " Invalid");

    let tableHTML = "";
    let rowNum = 1;

    validatedData.forEach(function(row) {
        let isValid = validRecords.includes(row.mca_ref);
        let rowClass = isValid ? "row-valid" : "row-invalid";
        let statusBadge = isValid 
            ? '<span class="badge bg-success">✓ Found</span>' 
            : '<span class="badge bg-danger">✗ Not Found</span>';

        tableHTML += `
            <tr class="${rowClass}">
                <td>${rowNum++}</td>
                <td><strong>${row.mca_ref}</strong></td>
                <td>${row.declaration_reference || '-'}</td>
                <td>${row.declaration_date || '-'}</td>
                <td>${row.liquidation_reference || '-'}</td>
                <td>${row.liquidation_date || '-'}</td>
                <td>${row.quittance_reference || '-'}</td>
                <td>${row.quittance_date || '-'}</td>
                <td>${row.liquidation_amount || '-'}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });

    $("#previewTableBody").html(tableHTML);

    if (validRecords.length > 0) {
        $("#submitBtn").prop("disabled", false);
    } else {
        $("#submitBtn").prop("disabled", true);
    }
}

function performUpdate() {
    $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

    let recordsToUpdate = validatedData.filter(r => validRecords.includes(r.mca_ref));

    console.log("Sending records for update:", recordsToUpdate);

    $.ajax({
        url: "<?php echo APP_URL; ?>importsydonia/update_mca_refs",
        method: "POST",
        data: JSON.stringify({ records: recordsToUpdate }),
        contentType: "application/json",
        dataType: "json",
        success: function (res) {
            console.log("Update response:", res);
            
            if (res.success) {
                let message = "✅ Successfully updated " + res.updated + " records!";
                if (res.failed > 0) {
                    message += "\n⚠️ Failed: " + res.failed + " records";
                }
                
                alert(message);
                
                $("#validationResults").html(`
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                        <h4>Update Complete!</h4>
                        <p class="mb-0">
                            Successfully updated <strong>${res.updated}</strong> records.
                            ${res.failed > 0 ? '<br><small class="text-warning">⚠️ ' + res.failed + ' records failed</small>' : ''}
                        </p>
                        <button class="btn btn-primary mt-3" onclick="resetUpload()">
                            <i class="fas fa-upload"></i> Upload Another File
                        </button>
                    </div>
                `);
            } else {
                alert("⚠️ Update failed: " + (res.msg || "Unknown error"));
                $("#submitBtn").prop("disabled", false).html('<i class="fas fa-check-circle"></i> Update Records');
            }
        },
        error: function (xhr, status, error) {
            console.error("Update error:", xhr.responseText);
            alert("❌ Error updating records!\n\nPlease check console for details.");
            $("#submitBtn").prop("disabled", false).html('<i class="fas fa-check-circle"></i> Update Records');
        }
    });
}

function resetUpload() {
    $("#excelFile").val("");
    $("#uploadZone").show();
    $("#validationResults").hide();
    $("#processingIndicator").hide();
    validatedData = [];
    validRecords = [];
    invalidRecords = [];
}
</script>