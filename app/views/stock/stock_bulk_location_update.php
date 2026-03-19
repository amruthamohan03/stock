<?php
/**
 * Bulk Location Update View
 * Allows users to select multiple stock items and update their location in bulk
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 d-flex align-items-center">
                <i class="bi bi-arrow-repeat text-primary me-2"></i>
                Bulk Location Update
            </h1>
            <p class="text-muted">Select multiple items and update their location in one action</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <form id="bulkUpdateForm">
        <div class="row">
            <!-- Left Column - Item Selection -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-boxes me-2"></i> Select Items
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="groupFilter" class="form-label">Filter by Group</label>
                                <select id="groupFilter" class="form-select">
                                    <option value="">All Groups</option>
                                    <?php foreach ($itemGroups as $group): ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo htmlspecialchars($group['group_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="itemSearch" class="form-label">Search Item</label>
                                <input type="text" id="itemSearch" class="form-control" placeholder="Search by item name or code...">
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-sm" id="itemsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                            </div>
                                        </th>
                                        <th>Item Name</th>
                                        <th>Item Code</th>
                                        <th>Group</th>
                                        <th>Current Location</th>
                                        <th style="width: 100px;">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <?php foreach ($stockItems as $item): ?>
                                        <tr class="item-row">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox" 
                                                           value="<?php echo $item['id']; ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                                <?php if (!empty($item['make_name']) || !empty($item['model_name'])): ?>
                                                    <br><small class="text-muted">
                                                        <?php echo htmlspecialchars($item['make_name'] ?? ''); ?>
                                                        <?php echo !empty($item['make_name']) && !empty($item['model_name']) ? ' / ' : ''; ?>
                                                        <?php echo htmlspecialchars($item['model_name'] ?? ''); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($item['item_code'] ?? ''); ?></code></td>
                                            <td><?php echo htmlspecialchars($item['group_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($item['location']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">
                                                    <?php echo $item['current_balance']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Selection Summary -->
                        <div class="bg-light p-3 rounded mt-3">
                            <small class="text-muted">
                                Selected: <strong id="selectedCount">0</strong> item(s)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Action Panel -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-gear me-2"></i> Update Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- New Location Selection -->
                        <div class="mb-4">
                            <label for="newLocation" class="form-label fw-bold">
                                New Location <span class="text-danger">*</span>
                            </label>
                            <select id="newLocation" name="new_location" class="form-select form-select-lg" required>
                                <option value="">-- Select Location --</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['id']; ?>">
                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                        <small><?php echo !empty($location['location_code']) ? '(' . htmlspecialchars($location['location_code']) . ')' : ''; ?></small>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> Select the destination location for all selected items
                            </small>
                        </div>

                        <!-- Reason/Remarks -->
                        <div class="mb-4">
                            <label for="updateReason" class="form-label fw-bold">Reason for Update</label>
                            <textarea id="updateReason" name="reason" class="form-control" rows="4" 
                                      placeholder="Enter reason for location update (optional)"></textarea>
                            <small class="form-text text-muted">
                                This will help track changes for audit purposes
                            </small>
                        </div>

                        <!-- Statistics Card -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading mb-2">Update Summary</h6>
                            <div class="row">
                                <div class="col-6">
                                    <small>Items Selected:</small><br>
                                    <strong id="summaryCount">0</strong>
                                </div>
                                <div class="col-6">
                                    <small>New Location:</small><br>
                                    <strong id="summaryLocation">Not Selected</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" id="submitBtn" class="btn btn-primary btn-lg" disabled>
                                <i class="bi bi-check-circle me-2"></i> Confirm Update
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i> Clear Selection
                            </button>
                        </div>

                        <!-- Danger Zone -->
                        <hr class="my-4">
                        <div class="alert alert-warning" role="alert">
                            <small>
                                <strong><i class="bi bi-exclamation-triangle me-2"></i>Note:</strong>
                                This action will update location for all selected items and create audit logs.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- History Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i> Recent Location Updates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Old Location</th>
                                    <th>New Location</th>
                                    <th>Updated By</th>
                                    <th>Date & Time</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> No update history yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-circle me-2"></i>Confirm Bulk Location Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to update the location for <strong id="confirmCount">0</strong> item(s).</p>
                
                <div class="alert alert-info">
                    <strong>Update Details:</strong>
                    <ul class="mb-0 mt-2">
                        <li>New Location: <strong id="confirmLocation">N/A</strong></li>
                        <li id="reasonLine" style="display: none;">
                            Reason: <strong id="confirmReason"></strong>
                        </li>
                    </ul>
                </div>

                <p class="text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>This action cannot be undone.</strong> All changes will be logged for audit purposes.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmBtn" class="btn btn-warning">
                    <i class="bi bi-check-circle me-2"></i>Proceed with Update
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .sticky-top {
        top: 80px;
    }

    #itemsTable tbody tr {
        transition: background-color 0.2s;
    }

    #itemsTable tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === VARIABLES ===
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const newLocationSelect = document.getElementById('newLocation');
    const updateReasonField = document.getElementById('updateReason');
    const submitBtn = document.getElementById('submitBtn');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmBtn = document.getElementById('confirmBtn');
    const groupFilter = document.getElementById('groupFilter');
    const itemSearch = document.getElementById('itemSearch');
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    let formData = {
        selected_items: [],
        new_location: 0,
        reason: ''
    };

    // === INITIALIZATION ===
    loadUpdateHistory();

    // === EVENT LISTENERS ===

    // Select All Checkbox
    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectionUI();
    });

    // Individual Item Checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectionUI();
            
            // Update select all checkbox state
            const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        });
    });

    // New Location Change
    newLocationSelect.addEventListener('change', function() {
        updateSelectionUI();
    });

    // Group Filter
    groupFilter.addEventListener('change', function() {
        filterItems();
    });

    // Item Search
    itemSearch.addEventListener('keyup', function() {
        filterItems();
    });

    // Submit Button
    submitBtn.addEventListener('click', function() {
        if (!validateSelection()) {
            return;
        }
        prepareConfirmationModal();
        confirmModal.show();
    });

    // Confirm Button
    confirmBtn.addEventListener('click', function() {
        performBulkUpdate();
    });

    // Form Reset
    bulkUpdateForm.addEventListener('reset', function() {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        newLocationSelect.value = '';
        updateReasonField.value = '';
        updateSelectionUI();
    });

    // === FUNCTIONS ===

    function updateSelectionUI() {
        // Get selected items
        formData.selected_items = Array.from(itemCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        formData.new_location = parseInt(newLocationSelect.value) || 0;
        formData.reason = updateReasonField.value;

        // Update UI
        const selectedCount = formData.selected_items.length;
        document.getElementById('selectedCount').textContent = selectedCount;
        document.getElementById('summaryCount').textContent = selectedCount;

        const selectedOption = newLocationSelect.options[newLocationSelect.selectedIndex];
        const locationName = selectedOption.text.split('(')[0].trim() || 'Not Selected';
        document.getElementById('summaryLocation').textContent = locationName;

        // Enable/Disable submit button
        submitBtn.disabled = selectedCount === 0 || formData.new_location === 0;
    }

    function filterItems() {
        const groupId = groupFilter.value;
        const searchTerm = itemSearch.value.toLowerCase();

        document.querySelectorAll('.item-row').forEach(row => {
            let show = true;

            // Filter by group
            if (groupId) {
                const groupCell = row.querySelector('td:nth-child(4)').textContent;
                show = show && (row.dataset.groupId === groupId || row.textContent.includes(groupId));
            }

            // Filter by search term
            if (searchTerm) {
                const itemName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const itemCode = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                show = show && (itemName.includes(searchTerm) || itemCode.includes(searchTerm));
            }

            row.style.display = show ? '' : 'none';
        });
    }

    function validateSelection() {
        if (formData.selected_items.length === 0) {
            showAlert('Please select at least one item', 'warning');
            return false;
        }

        if (formData.new_location === 0) {
            showAlert('Please select a new location', 'warning');
            return false;
        }

        return true;
    }

    function prepareConfirmationModal() {
        document.getElementById('confirmCount').textContent = formData.selected_items.length;
        
        const selectedOption = newLocationSelect.options[newLocationSelect.selectedIndex];
        const locationName = selectedOption.text.split('(')[0].trim();
        document.getElementById('confirmLocation').textContent = locationName;

        const reasonLine = document.getElementById('reasonLine');
        if (formData.reason) {
            document.getElementById('confirmReason').textContent = formData.reason;
            reasonLine.style.display = 'block';
        } else {
            reasonLine.style.display = 'none';
        }
    }

    function performBulkUpdate() {
        const submitData = new FormData();
        submitData.append('selected_items', JSON.stringify(formData.selected_items));
        submitData.append('new_location', formData.new_location);
        submitData.append('reason', formData.reason);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        fetch('<?php echo BASE_URL; ?>stock/performBulkLocationUpdate', {
            method: 'POST',
            body: submitData
        })
        .then(response => response.json())
        .then(data => {
            confirmModal.hide();
            
            if (data.success) {
                showAlert(data.message, 'success');
                bulkUpdateForm.reset();
                updateSelectionUI();
                
                // Reload history and items
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            confirmModal.hide();
            showAlert('Error: ' + error.message, 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Confirm Update';
        });
    }

    function loadUpdateHistory() {
        fetch('<?php echo BASE_URL; ?>stock/getBulkUpdateHistory')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    populateHistory(data.data);
                }
            })
            .catch(error => console.error('Error loading history:', error));
    }

    function populateHistory(historyData) {
        const historyBody = document.getElementById('historyBody');
        historyBody.innerHTML = '';

        historyData.forEach(item => {
            try {
                const oldValues = JSON.parse(item.old_values);
                const newValues = JSON.parse(item.new_values);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <small>${htmlEscape(item.item_name || 'N/A')}</small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${htmlEscape(oldValues.location)}</span>
                    </td>
                    <td>
                        <span class="badge bg-success">${htmlEscape(newValues.location)}</span>
                    </td>
                    <td>
                        <small>${htmlEscape(item.updated_by_name || 'System')}</small>
                    </td>
                    <td>
                        <small>${new Date(item.action_at).toLocaleString()}</small>
                    </td>
                    <td>
                        <small class="text-muted">${item.action_reason ? htmlEscape(item.action_reason) : '-'}</small>
                    </td>
                `;
                historyBody.appendChild(row);
            } catch (e) {
                console.error('Error parsing history item:', e);
            }
        });
    }

    function showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();
        
        const alertHTML = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${getAlertIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.insertAdjacentHTML('beforeend', alertHTML);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    function getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    function htmlEscape(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>