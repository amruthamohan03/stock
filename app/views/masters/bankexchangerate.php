<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                
                <!-- Exchange Rate Management -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Bank Exchange Rate Management</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Exchange Rates Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="ratesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 150px;">Date</th>
                                        <th class="text-center" style="width: 150px;">Currency</th>
                                        <th class="text-center" style="width: 150px;">BCC Rate</th>
                                        <?php if(!empty($banks)): ?>
                                            <?php foreach($banks as $bank): ?>
                                                <th class="text-center" style="min-width: 150px;" data-bank-id="<?= $bank['id'] ?>">
                                                    <?= strtoupper(htmlspecialchars($bank['bank_name'])) ?>
                                                </th>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <th class="text-center" style="width: 200px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <!-- Date Input -->
                                        <td class="text-center">
                                            <input type="date" 
                                                   id="exchange_date" 
                                                   class="form-control text-center date-input" 
                                                   value="<?= htmlspecialchars($selectedDate) ?>" 
                                                   max="<?= date('Y-m-d') ?>">
                                        </td>
                                        
                                        <!-- Currency Select -->
                                        <td class="text-center">
                                            <select id="currency_id" class="form-select text-center currency-select">
                                                <?php if(!empty($currencies)): ?>
                                                    <?php foreach($currencies as $currency): ?>
                                                        <option value="<?= $currency['id'] ?>" 
                                                                <?= ($currency['currency_short_name'] == 'CDF') ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($currency['currency_short_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        
                                        <!-- BCC Rate Input -->
                                        <td class="text-center">
                                            <input type="number" 
                                                   step="0.01" 
                                                   class="form-control text-center rate-input bcc-rate" 
                                                   id="bcc_rate"
                                                   value="<?= $bccRate ? number_format($bccRate, 2, '.', '') : '' ?>"
                                                   placeholder="0.00">
                                        </td>
                                        
                                        <!-- Bank Rates -->
                                        <?php if(!empty($banks)): ?>
                                            <?php foreach($banks as $bank): ?>
                                                <?php $rate = $exchangeRates[$bank['id']] ?? null; ?>
                                                <td class="text-center">
                                                    <input type="number" 
                                                           step="0.01" 
                                                           class="form-control text-center rate-input bank-rate" 
                                                           data-bank-id="<?= $bank['id'] ?>"
                                                           value="<?= $rate ? number_format($rate['bank_rate'], 2, '.', '') : '' ?>"
                                                           placeholder="0.00">
                                                </td>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <td colspan="2" class="text-center text-muted">
                                                No banks configured for exchange rates.
                                            </td>
                                        <?php endif; ?>
                                        
                                        <!-- Action Buttons -->
                                        <td class="text-center">
                                            <button id="saveRatesBtn" class="btn btn-success btn-sm me-1">
                                                <i class="ti ti-device-floppy"></i> Save
                                            </button>
                                            <button id="clearBtn" class="btn btn-warning btn-sm">
                                                <i class="ti ti-eraser"></i> Clear
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Bottom Action Buttons -->
                        <div class="row mt-3">
                            <div class="col-md-12 text-end">
                                <button id="loadRateBtn" class="btn btn-info me-2">
                                    <i class="ti ti-refresh"></i> Load Rate
                                </button>
                                <button id="exportBtn" class="btn btn-primary">
                                    <i class="ti ti-download"></i> Export
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Exchange Rate History -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0">Exchange Rate History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="history-table" class="table table-striped table-hover dt-responsive nowrap w-100">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">BCC Rate</th>
                                        <?php if(!empty($banks)): ?>
                                            <?php foreach($banks as $bank): ?>
                                                <th class="text-center">
                                                    <?= htmlspecialchars(substr($bank['bank_name'], 0, 15)) ?>
                                                </th>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <th class="text-center">Updated</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <!-- Will be populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ti ti-circle-check me-2"></i>Success
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p id="successMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="ti ti-alert-circle me-2"></i>Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p id="errorMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="ti ti-alert-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p id="confirmDeleteMessage" class="mb-0">Are you sure you want to delete this rate?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    
    let deleteId = null;
    let deleteDate = null;
    let deleteCurrency = null;
    
    // Helper functions
    function showSuccess(message) {
        $('#successMessage').text(message);
        $('#successModal').modal('show');
    }
    
    function showError(message) {
        $('#errorMessage').text(message);
        $('#errorModal').modal('show');
    }
    
    function formatDate(dateStr) {
        let d = new Date(dateStr);
        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();
        return `${day}-${month}-${year}`;
    }
    
    // Load rates for selected date and currency
    function loadRates() {
        let date = $('#exchange_date').val();
        let currency_id = $('#currency_id').val();
        
        if (!date || !currency_id) {
            return;
        }
        
        $.ajax({
            url: '<?= APP_URL ?>bankExchangeRate/getRatesForDate',
            type: 'GET',
            data: { 
                date: date, 
                currency_id: currency_id 
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Set BCC rate
                    if (res.bcc_rate) {
                        $('#bcc_rate').val(parseFloat(res.bcc_rate).toFixed(2));
                    } else {
                        $('#bcc_rate').val('');
                    }
                    
                    // Clear all bank rates first
                    $('.bank-rate').val('');
                    
                    // Set bank rates
                    if (res.rates && Object.keys(res.rates).length > 0) {
                        $.each(res.rates, function(bankId, rate) {
                            $(`.bank-rate[data-bank-id="${bankId}"]`).val(
                                rate.bank_rate ? parseFloat(rate.bank_rate).toFixed(2) : ''
                            );
                        });
                    }
                }
            },
            error: function() {
                showError('Error loading rates');
            }
        });
    }
    
    // Load Rate button
    $('#loadRateBtn').click(function() {
        let date = $('#exchange_date').val();
        let currency = $('#currency_id').val();
        
        if (!date) {
            showError('Please select a date first');
            return;
        }
        
        if (!currency) {
            showError('Please select a currency first');
            return;
        }
        
        loadRates();
    });
    
    // Date change handler
    $('#exchange_date').change(function() {
        loadRates();
        loadHistory();
    });
    
    // Currency change handler
    $('#currency_id').change(function() {
        loadRates();
        loadHistory();
    });
    
    // Save rates
    $('#saveRatesBtn').click(function() {
        let date = $('#exchange_date').val();
        let currency_id = $('#currency_id').val();
        let bcc_rate = $('#bcc_rate').val();
        
        if (!date) {
            showError('Please select a date');
            return;
        }
        
        if (!currency_id) {
            showError('Please select a currency');
            return;
        }
        
        if (!bcc_rate || parseFloat(bcc_rate) <= 0) {
            showError('Please enter BCC Rate (must be greater than 0)');
            return;
        }
        
        // Collect bank rates
        let rates = {};
        let hasAnyRate = false;
        
        $('.bank-rate').each(function() {
            let bankId = $(this).data('bank-id');
            let bankRate = $(this).val();
            
            if (bankRate && parseFloat(bankRate) > 0) {
                rates[bankId] = {
                    bank_rate: bankRate
                };
                hasAnyRate = true;
            }
        });
        
        if (!hasAnyRate) {
            showError('Please enter at least one bank rate');
            return;
        }
        
        // Disable button to prevent double submission
        $(this).prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: '<?= APP_URL ?>bankExchangeRate/saveRates',
            type: 'POST',
            data: {
                exchange_date: date,
                bcc_rate: bcc_rate,
                currency_id: currency_id,
                rates: rates
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showSuccess(res.message);
                    loadHistory();
                } else {
                    showError(res.message);
                }
                $('#saveRatesBtn').prop('disabled', false);
            },
            error: function() {
                showError('Error saving rates');
                $('#saveRatesBtn').prop('disabled', false);
            }
        });
    });
    
    // Clear button
    $('#clearBtn').click(function() {
        if (confirm('Clear all rate fields?')) {
            $('#bcc_rate').val('');
            $('.bank-rate').val('');
            showSuccess('All rate fields cleared');
        }
    });
    
    // Load history
    function loadHistory() {
        let currency_id = $('#currency_id').val();
        $.ajax({
            url: '<?= APP_URL ?>bankExchangeRate/getHistoryHorizontal',
            type: 'GET',
            data: { 
                currency_id: currency_id,
                limit: 100
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let tbody = $('#historyTableBody');
                    tbody.empty();
                    
                    if (res.data && res.data.length > 0) {
                        var i = 0;
                        $.each(res.data, function(idx, row) {
                            let tr = '<tr>';
                            tr += '<td class="text-center">' + (++i) + '</td>';
                            tr += '<td class="text-center">' + row.exchange_date + '</td>';
                            tr += '<td class="text-center"><span class="badge bg-info">' + row.currency_name + '</span></td>';
                            tr += '<td class="text-center"><strong class="text-primary">' + row.bcc_rate + '</strong></td>';
                            
                            // Add bank rates
                            $.each(row.banks, function(bankId, rate) {
                                tr += '<td class="text-center">' + (rate || '-') + '</td>';
                            });
                            
                            tr += '<td class="text-center"><small class="text-muted">' + row.updated_at + '</small></td>';
                            
                            // Action buttons
                            tr += '<td class="text-center">';
                            tr += '<button class="btn btn-sm btn-danger delete-rate" data-date="' + row.exchange_date_raw + '" data-currency="' + currency_id + '">';
                            tr += '<i class="ti ti-trash"></i> Delete';
                            tr += '</button>';
                            tr += '</td>';
                            
                            tr += '</tr>';
                            tbody.append(tr);
                        });
                        // Destroy existing DataTable if it exists
                        if ($.fn.DataTable.isDataTable('#history-table')) {
                            $('#history-table').DataTable().destroy();
                        }
                        // Initialize DataTable
                        $('#history-table').DataTable({
                            "columnDefs": [
                                { "orderable": false, "targets": [-1] }
                            ],
                            "language": { 
                                "emptyTable": "No history found" 
                            },
                            "order": [[1, 'asc']],
                            "pageLength": 25,
                            "responsive": true
                        });
                    } else {
                        tbody.append('<tr><td colspan="10" class="text-center text-muted">No history found</td></tr>');
                        // $('#history-table').DataTable();
                    }
                }
            }
        });
    }
    
    // Delete rate
    $(document).on('click', '.delete-rate', function() {
        deleteDate = $(this).data('date');
        deleteCurrency = $(this).data('currency');
        
        $('#confirmDeleteMessage').text('Delete all rates for ' + formatDate(deleteDate) + '?');
        $('#confirmDeleteModal').modal('show');
    });
    
    // Confirm delete
    $('#confirmDeleteBtn').click(function() {
        if (deleteDate && deleteCurrency) {
            $.ajax({
                url: '<?= APP_URL ?>bankExchangeRate/deleteRatesForDate',
                type: 'POST',
                data: {
                    exchange_date: deleteDate,
                    currency_id: deleteCurrency
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showSuccess(res.message);
                        loadHistory();
                        
                        // Clear form if deleting current date
                        if (deleteDate === $('#exchange_date').val()) {
                            $('#bcc_rate').val('');
                            $('.bank-rate').val('');
                        }
                    } else {
                        showError(res.message);
                    }
                    $('#confirmDeleteModal').modal('hide');
                    deleteDate = null;
                    deleteCurrency = null;
                },
                error: function() {
                    showError('Error deleting rates');
                    $('#confirmDeleteModal').modal('hide');
                }
            });
        }
    });
    
    // Export rates
    $('#exportBtn').click(function() {
        let date = $('#exchange_date').val();
        let currency_id = $('#currency_id').val();
        
        if (!date) {
            showError('Please select a date');
            return;
        }
        
        if (!currency_id) {
            showError('Please select a currency');
            return;
        }
        
        window.location.href = `<?= APP_URL ?>bankExchangeRate/exportRatesHorizontal?date=${date}&currency_id=${currency_id}`;
    });
    
    // Initial load
    loadRates();
    loadHistory();
    
});
</script>

<style>
/* Modern styling matching the uploaded image */
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-radius: 8px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    padding: 1.25rem 1.5rem;
    border-radius: 8px 8px 0 0 !important;
}

.card-header h4 {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.1rem;
}

/* Table styling */
#ratesTable {
    margin-bottom: 0;
}

#ratesTable thead th {
    background-color: #f1f3f5;
    color: #495057;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 1rem 0.75rem;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

#ratesTable tbody td {
    padding: 0.75rem;
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

/* Input styling - matching the image */
.date-input,
.currency-select,
.rate-input {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    min-width: 120px;
}

.date-input {
    background-color: #f8f9fa;
    border-color: #198754;
    font-weight: 500;
}

.date-input:focus {
    border-color: #157347;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    outline: none;
}

.currency-select {
    background-color: #fff;
    font-weight: 600;
    color: #0d6efd;
    border-color: #0d6efd;
    cursor: pointer;
}

.currency-select:focus {
    border-color: #0a58ca;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.rate-input {
    background-color: #fff;
    text-align: center;
    font-weight: 500;
}

.rate-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    outline: none;
}

.bcc-rate {
    border-color: #0d6efd !important;
    border-width: 2px !important;
    font-weight: 600 !important;
    color: #0d6efd;
}

.bcc-rate:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.bank-rate {
    border-color: #adb5bd;
}

.bank-rate:focus {
    border-color: #86b7fe;
}

/* Number input spinners */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    opacity: 1;
    height: 30px;
}

/* Button styling */
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 5px;
    font-weight: 500;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #000;
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: #fff;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.btn-primary {
    background-color: #5a6fce;
    border-color: #5a6fce;
}

.btn-primary:hover {
    background-color: #4a5fb8;
    border-color: #4a5fb8;
}

/* History table */
#history-table thead th {
    background-color: #cfe2ff !important;
    color: #084298;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.75rem 0.5rem;
    white-space: nowrap;
}

#history-table tbody td {
    padding: 0.6rem 0.5rem;
    font-size: 0.9rem;
}

#history-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Responsive */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Modal styling */
.modal-content {
    border-radius: 8px;
    border: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.modal-header {
    border-radius: 8px 8px 0 0;
}

/* Badge */
.badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
    font-size: 0.85rem;
}

/* Icons */
.ti {
    font-size: 1rem;
    vertical-align: middle;
}

/* Loading state */
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Print friendly */
@media print {
    .btn, .card-header {
        display: none;
    }
}
</style>