<div class="page-content">
    <div class="page-container">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="header-title">Two-Type Stock Entry System</h4>
                            <p class="text-muted mb-0">Type 1: Indent-based Entry | Type 2: Transfer Entry</p>
                        </div>
                        <a href="<?= APP_URL; ?>stock/viewAll" class="btn btn-sm btn-info">
                            <i class="mdi mdi-eye"></i> View All Entries
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Transactions</h6>
                        <h3 class="mb-0" id="total-transactions">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Pending Verification</h6>
                        <h3 class="text-warning mb-0" id="pending-count">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Verified</h6>
                        <h3 class="text-success mb-0" id="verified-count">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Rejected</h6>
                        <h3 class="text-danger mb-0" id="rejected-count">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="accordion" id="stockEntryAccordion">

                            <!-- ========================================================================
                                 TYPE 1: INDENT-BASED ENTRY
                                 ======================================================================== -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIndent">
                                        <i class="mdi mdi-checkbox-marked-circle text-primary me-2"></i> 
                                        <strong>Type 1: Indent-Based Stock Entry</strong>
                                        <span class="badge bg-primary ms-auto">From Purchase Indent</span>
                                    </button>
                                </h2>
                                <div id="collapseIndent" class="accordion-collapse collapse" data-bs-parent="#stockEntryAccordion">
                                    <div class="accordion-body">
                                        <form id="indentEntryForm" method="POST" action="">
                                            <!-- Row 1: Classification -->
                                            <div class="row mb-4">
                                                <div class="col-md-4">
                                                    <label for="indentItemType" class="form-label">
                                                        <strong>Item Type</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="indentItemType" class="form-select" required>
                                                        <option value="">-- Select Item Type --</option>
                                                        <?php foreach ($itemTypes as $type): ?>
                                                            <option value="<?= $type; ?>">
                                                                <?= str_replace('_', ' ', $type); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="indentCategory" class="form-label">
                                                        <strong>Category</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="indentCategory" class="form-select" required>
                                                        <option value="">-- Select Category --</option>
                                                        <?php foreach ($categories as $category): ?>
                                                            <option value="<?= $category; ?>">
                                                                <?= str_replace('_', ' ', $category); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="indentBookVolume" class="form-label">
                                                        <strong>Book Volume</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="indentBookVolume" class="form-select" required>
                                                        <option value="">-- Select Volume --</option>
                                                        <option value="1">Volume 1</option>
                                                        <option value="2">Volume 2</option>
                                                        <option value="3">Volume 3</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!-- Row 1: Group and Indent Selection -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="indentGroupId" class="form-label">
                                                        <strong>Item Group</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="indentGroupId" class="form-select" required>
                                                        <option value="">-- Select Item Group --</option>
                                                        <?php foreach ($itemGroups as $group): ?>
                                                            <option value="<?= $group['id']; ?>">
                                                                <?= htmlspecialchars($group['group_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-1">Choose the item group to see available indents</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="indentTransactionDate" class="form-label">
                                                        <strong>Transaction Date</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" id="indentTransactionDate" class="form-control" required>
                                                    <script>
                                                        document.getElementById('indentTransactionDate').valueAsDate = new Date();
                                                    </script>
                                                </div>
                                            </div>

                                            <!-- Row 3: Received From -->
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <label for="indentReceivedFrom" class="form-label">
                                                        <strong>Received From</strong>
                                                    </label>
                                                    <input type="text" id="indentReceivedFrom" class="form-control" placeholder="e.g., Vendor name, Purchase order reference">
                                                </div>
                                            </div>

                                            <!-- Items Table -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label"><strong>Indent Items to Accept</strong></label>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered" id="indentItemsTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%">
                                                                        <input type="checkbox" id="selectAllIndentItems" title="Select all">
                                                                    </th>
                                                                    <th>Item Name</th>
                                                                    <th>Make</th>
                                                                    <th>Model</th>
                                                                    <th>Description</th>
                                                                    <th width="10%">Qty</th>
                                                                    <th width="15%">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="indentItemsBody">
                                                                <tr>
                                                                    <td colspan="7" class="text-center text-muted py-3">
                                                                        Select group and indent to load items
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Remarks -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="indentRemarks" class="form-label">Remarks</label>
                                                    <textarea id="indentRemarks" class="form-control" rows="3" placeholder="Any additional notes..."></textarea>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary" id="indentSubmitBtn">
                                                        <i class="mdi mdi-check-circle"></i> Accept to Stock Register
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary ms-2">
                                                        <i class="mdi mdi-refresh"></i> Reset
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================================================
                                 TYPE 2: TRANSFER ENTRY
                                 ======================================================================== -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTransfer">
                                        <i class="mdi mdi-transfer text-success me-2"></i>
                                        <strong>Type 2: Transfer Stock Entry</strong>
                                        <span class="badge bg-success ms-auto">Between Locations</span>
                                    </button>
                                </h2>
                                <div id="collapseTransfer" class="accordion-collapse collapse" data-bs-parent="#stockEntryAccordion">
                                    <div class="accordion-body">
                                        <form id="transferEntryForm" method="POST" action="">

                                            <!-- Row 1: Classification -->
                                            <div class="row mb-4">
                                                <div class="col-md-4">
                                                    <label for="transferItemType" class="form-label">
                                                        <strong>Item Type</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="transferItemType" class="form-select" required>
                                                        <option value="">-- Select Item Type --</option>
                                                        <?php foreach ($itemTypes as $type): ?>
                                                            <option value="<?= $type; ?>">
                                                                <?= str_replace('_', ' ', $type); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="transferCategory" class="form-label">
                                                        <strong>Category</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="transferCategory" class="form-select" required>
                                                        <option value="">-- Select Category --</option>
                                                        <?php foreach ($categories as $category): ?>
                                                            <option value="<?= $category; ?>">
                                                                <?= str_replace('_', ' ', $category); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="transferBookVolume" class="form-label">
                                                        <strong>Book Volume</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="transferBookVolume" class="form-select" required>
                                                        <option value="">-- Select Volume --</option>
                                                        <option value="1">Volume 1</option>
                                                        <option value="2">Volume 2</option>
                                                        <option value="3">Volume 3</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Row 2: Locations -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="transferSourceLocation" class="form-label">
                                                        <strong>Transfer From (Source Location)</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="transferSourceLocation" class="form-select" required>
                                                        <option value="">-- Select Source Location --</option>
                                                        <?php foreach ($locations as $loc): ?>
                                                            <option value="<?= $loc['id']; ?>">
                                                                [<?= htmlspecialchars($loc['location_code']); ?>] 
                                                                <?= htmlspecialchars($loc['location_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-1">Location where items currently exist</small>
                                                </div>

                                                <div class="col-md-6">
                                                    <label for="transferDestLocation" class="form-label">
                                                        <strong>Transfer To (Destination Location)</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select id="transferDestLocation" class="form-select" required>
                                                        <option value="">-- Select Destination Location --</option>
                                                        <?php foreach ($locations as $loc): ?>
                                                            <option value="<?= $loc['id']; ?>">
                                                                [<?= htmlspecialchars($loc['location_code']); ?>] 
                                                                <?= htmlspecialchars($loc['location_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-1">Location where items will be transferred</small>
                                                </div>
                                            </div>

                                            <!-- Row 3: Date -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="transferTransactionDate" class="form-label">
                                                        <strong>Transfer Date</strong>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" id="transferTransactionDate" class="form-control" required>
                                                    <script>
                                                        document.getElementById('transferTransactionDate').valueAsDate = new Date();
                                                    </script>
                                                </div>
                            
                                                <div class="col-md-6">
                                                    <label for="transferReceivedFrom" class="form-label">Remarks</label>
                                                    <input type="text" id="transferReceivedFrom" class="form-control" placeholder="Reference/Notes">
                                                </div>
                                            </div>

                                            <!-- Items Table -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="form-label mb-0"><strong>Items to Transfer</strong></label>
                                                        <button type="button" class="btn btn-sm btn-success" id="addTransferItemBtn">
                                                            <i class="mdi mdi-plus-circle"></i> Add Item
                                                        </button>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered" id="transferItemsTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Item Name</th>
                                                                    <th width="12%">Make</th>
                                                                    <th width="12%">Model</th>
                                                                    <th>Description</th>
                                                                    <th width="10%">Qty</th>
                                                                    <th width="12%">Condition</th>
                                                                    <th width="12%">Status</th>
                                                                    <th width="5%">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="transferItemsBody">
                                                                <tr id="emptyTransferRow">
                                                                    <td colspan="8" class="text-center text-muted py-3">
                                                                        No items added. Click "Add Item" to add items to transfer.
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Remarks -->
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label for="transferRemarks" class="form-label">Additional Remarks</label>
                                                    <textarea id="transferRemarks" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-success" id="transferSubmitBtn">
                                                        <i class="mdi mdi-transfer"></i> Process Transfer
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary ms-2">
                                                        <i class="mdi mdi-refresh"></i> Reset
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Hidden modal for adding transfer items -->
<div class="modal fade" id="addTransferItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item to Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="modalItemName" class="form-label"><strong>Item Name</strong></label>
                        <select id="modalItemName" class="form-select" required>
                            <option value="">-- Select Item --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id']; ?>" 
                                        data-make="<?= htmlspecialchars($item['make'] ?? ''); ?>"
                                        data-model="<?= htmlspecialchars($item['model'] ?? ''); ?>"
                                        data-description="<?= htmlspecialchars($item['description'] ?? ''); ?>">
                                    <?= htmlspecialchars($item['item_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="modalMake" class="form-label">Make</label>
                        <input type="text" id="modalMake" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="modalModel" class="form-label">Model</label>
                        <input type="text" id="modalModel" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="modalQuantity" class="form-label"><strong>Quantity</strong></label>
                        <input type="number" id="modalQuantity" class="form-control" min="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="modalCondition" class="form-label">Condition</label>
                        <select id="modalCondition" class="form-select">
                            <option value="GOOD">Good</option>
                            <option value="DAMAGED">Damaged</option>
                            <option value="FUNCTIONAL">Functional</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modalItemStatus" class="form-label">Status</label>
                        <select id="modalItemStatus" class="form-select">
                            <option value="WORKING">Working</option>
                            <option value="FAULTY">Faulty</option>
                            <option value="UNDER_REPAIR">Under Repair</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label for="modalDescription" class="form-label">Description</label>
                        <textarea id="modalDescription" class="form-control" rows="3" readonly></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="addItemConfirmBtn">Add Item</button>
            </div>
        </div>
    </div>
</div>
<?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
<script>
// ============================================================================
// TYPE 1: INDENT-BASED ENTRY JAVASCRIPT
// ============================================================================

// Get indents when group is selected
document.getElementById('indentGroupId').addEventListener('change', function() {
    const groupId = this.value;
    const tbody = document.getElementById('indentItemsBody');

    if (!groupId) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Select group to load items</td></tr>';
        return;
    }

    fetch('<?= APP_URL; ?>stock/getIndentsByGroup?group_id=' + groupId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadIndentItems(data.data); // ✅ directly load table
            }
        })
        .catch(err => console.error(err));
});


function loadIndentItems(items) {
    const tbody = document.getElementById('indentItemsBody');
    
    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No items found</td></tr>';
        return;
    }

    tbody.innerHTML = items.map((item, index) => `
        <tr>
            <td>
                <input type="checkbox" class="item-checkbox" 
                       data-item-id="${item.item_id}" 
                       data-indent-item-id="${item.id}"
                       data-quantity="${item.quantity}"
                       data-indent-id="${item.indent_id}">
            </td>
            <td><strong>${item.item_name || 'N/A'}</strong></td>
            <td>${item.make_name || '-'}</td>
            <td>${item.model_name || '-'}</td>
            <td><small>${item.itemdescription || '-'}</small></td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm quantity-input" 
                       value="${item.quantity}" min="1" data-indent-item-id="${item.id}">
            </td>
            <td>
                <select class="form-select form-select-sm status-select" data-indent-item-id="${item.id}">
                    <option value="WORKING">Working</option>
                    <option value="FAULTY">Faulty</option>
                    <option value="UNDER_REPAIR">Under Repair</option>
                </select>
            </td>
        </tr>
    `).join('');
}

// Select all checkbox for indent items
document.getElementById('selectAllIndentItems').addEventListener('change', function() {
    document.querySelectorAll('#indentItemsTable .item-checkbox').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Handle indent form submission
// document.getElementById('indentEntryForm').addEventListener('submit', function(e) {
//     e.preventDefault();

//     const selectedItems = Array.from(document.querySelectorAll('#indentItemsTable .item-checkbox:checked'))
//         .map(checkbox => ({
//             item_id: checkbox.dataset.itemId,
//             indent_item_id: checkbox.dataset.indentItemId,
//             quantity: parseInt(document.querySelector(`.quantity-input[data-indent-item-id="${checkbox.dataset.indentItemId}"]`).value),
//             item_status: document.querySelector(`.status-select[data-indent-item-id="${checkbox.dataset.indentItemId}"]`).value
//         }));

//     if (selectedItems.length === 0) {
//         alert('Please select at least one item');
//         return;
//     }

//     const formData = new FormData();
//     formData.append('indent_id', document.getElementById('indentId').value);
//     formData.append('location', document.getElementById('indentLocation').value);
//     formData.append('transaction_date', document.getElementById('indentTransactionDate').value);
//     formData.append('received_from', document.getElementById('indentReceivedFrom').value);
//     formData.append('remarks', document.getElementById('indentRemarks').value);
    
//     selectedItems.forEach((item, index) => {
//         formData.append(`items[${index}][item_id]`, item.item_id);
//         formData.append(`items[${index}][indent_item_id]`, item.indent_item_id);
//         formData.append(`items[${index}][quantity]`, item.quantity);
//         formData.append(`items[${index}][item_status]`, item.item_status);
//     });

//     fetch('<?= APP_URL; ?>stock/stock/saveIndentEntry', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             alert('✓ Stock entry saved successfully!\nBatch Code: ' + data.batch_code);
//             document.getElementById('indentEntryForm').reset();
//             document.getElementById('indentId').disabled = true;
//             document.getElementById('indentItemsBody').innerHTML = 
//                 '<tr><td colspan="7" class="text-center text-muted py-3">Select indent to load items</td></tr>';
//         } else {
//             alert('✗ Error: ' + data.message);
//         }
//     })
//     .catch(error => console.error('Error:', error));
// });

document.getElementById('indentEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const groupId = document.getElementById('indentGroupId').value;
    const itemType = document.getElementById('indentItemType').value;
    const category = document.getElementById('indentCategory').value;
    const bookVolume = document.getElementById('indentBookVolume').value;
    const transactionDate = document.getElementById('indentTransactionDate').value;
    const receivedFrom = document.getElementById('indentReceivedFrom').value;
    const remarks = document.getElementById('indentRemarks').value;

    // Get all checked items
    const checkedItems = Array.from(document.querySelectorAll('#indentItemsTable .item-checkbox:checked'));
    
    if (!groupId) {
        alert('❌ Please select Item Group');
        return;
    }
    if (!itemType) {
        alert('❌ Please select Item Type');
        return;
    }
    if (!category) {
        alert('❌ Please select Category');
        return;
    }
    if (!bookVolume) {
        alert('❌ Please select Book Volume');
        return;
    }
    if (!location) {
        alert('❌ Please enter Storage Location');
        return;
    }
    if (checkedItems.length === 0) {
        alert('❌ Please select at least one item');
        return;
    }

    const items = checkedItems.map(checkbox => ({
        indent_item_id: checkbox.dataset.indentItemId,
        item_id: parseInt(checkbox.dataset.itemId),
        indent_id: parseInt(checkbox.dataset.indentId),
        quantity: parseInt(checkbox.dataset.quantity),
        item_status: document.querySelector(`.status-select[data-indent-item-id="${checkbox.dataset.indentItemId}"]`).value
    }));

    const formData = new FormData();
    formData.append('group_id', groupId);
    formData.append('item_type', itemType);
    formData.append('category', category);
    formData.append('book_volume', bookVolume);
    formData.append('location', location);
    formData.append('transaction_date', transactionDate);
    formData.append('received_from', receivedFrom);
    formData.append('remarks', remarks);
    
    items.forEach((item, index) => {
        formData.append(`items[${index}][indent_item_id]`, item.indent_item_id);
        formData.append(`items[${index}][item_id]`, item.item_id);
        formData.append(`items[${index}][indent_id]`, item.indent_id);
        formData.append(`items[${index}][quantity]`, item.quantity);
        formData.append(`items[${index}][item_status]`, item.item_status);
    });

    fetch('<?= APP_URL; ?>stock/saveIndentEntry', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            alert('✓ ' + items.length + ' items accepted!\nBatch: ' + data.batch_code);
            document.getElementById('indentEntryForm').reset();
            document.getElementById('indentItemsBody').innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Select Item Group to load items</td></tr>';
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(e => alert('✗ Error: ' + e.message));
});

// ============================================================================
// TYPE 2: TRANSFER ENTRY JAVASCRIPT
// ============================================================================

let transferItemCount = 0;

// Add transfer item row
document.getElementById('addTransferItemBtn').addEventListener('click', function() {
    document.getElementById('addTransferItemModal').querySelector('.modal-dialog').style.width = 'auto';
    new bootstrap.Modal(document.getElementById('addTransferItemModal')).show();
});

// Auto-fill item details when selected
document.getElementById('modalItemName').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('modalMake').value = selectedOption.dataset.make || '';
    document.getElementById('modalModel').value = selectedOption.dataset.model || '';
    document.getElementById('modalDescription').value = selectedOption.dataset.description || '';
});

// Confirm adding transfer item
document.getElementById('addItemConfirmBtn').addEventListener('click', function() {
    const itemId = document.getElementById('modalItemName').value;
    const itemName = document.getElementById('modalItemName').options[document.getElementById('modalItemName').selectedIndex].text;
    const make = document.getElementById('modalMake').value;
    const model = document.getElementById('modalModel').value;
    const description = document.getElementById('modalDescription').value;
    const quantity = document.getElementById('modalQuantity').value;
    const condition = document.getElementById('modalCondition').value;
    const status = document.getElementById('modalItemStatus').value;

    if (!itemId || !quantity) {
        alert('Please fill all required fields');
        return;
    }

    addTransferItemRow(itemId, itemName, make, model, description, quantity, condition, status);

    // Reset modal
    document.getElementById('addTransferItemModal').querySelector('form').reset();
    bootstrap.Modal.getInstance(document.getElementById('addTransferItemModal')).hide();
});

function addTransferItemRow(itemId, itemName, make, model, description, quantity, condition, status) {
    const tbody = document.getElementById('transferItemsBody');
    const emptyRow = document.getElementById('emptyTransferRow');
    
    if (emptyRow) {
        emptyRow.remove();
    }

    const row = document.createElement('tr');
    row.id = 'transferItem_' + (transferItemCount++);
    row.innerHTML = `
        <td><strong>${itemName}</strong><input type="hidden" class="item-id" value="${itemId}"></td>
        <td><small>${make}</small></td>
        <td><small>${model}</small></td>
        <td><small>${description}</small></td>
        <td><input type="number" class="form-control form-control-sm quantity-field" value="${quantity}" min="1"></td>
        <td>
            <select class="form-select form-select-sm condition-field">
                <option value="GOOD" ${condition === 'GOOD' ? 'selected' : ''}>Good</option>
                <option value="DAMAGED" ${condition === 'DAMAGED' ? 'selected' : ''}>Damaged</option>
                <option value="FUNCTIONAL" ${condition === 'FUNCTIONAL' ? 'selected' : ''}>Functional</option>
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm status-field">
                <option value="WORKING" ${status === 'WORKING' ? 'selected' : ''}>Working</option>
                <option value="FAULTY" ${status === 'FAULTY' ? 'selected' : ''}>Faulty</option>
                <option value="UNDER_REPAIR" ${status === 'UNDER_REPAIR' ? 'selected' : ''}>Under Repair</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-item" title="Remove">
                <i class="mdi mdi-delete-outline"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);

    // Add remove button handler
    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        if (document.querySelectorAll('#transferItemsTable tbody tr').length === 0) {
            const tbody = document.getElementById('transferItemsBody');
            tbody.innerHTML = '<tr id="emptyTransferRow"><td colspan="8" class="text-center text-muted py-3">No items added. Click "Add Item" to add items to transfer.</td></tr>';
        }
    });
}

// Handle transfer form submission
document.getElementById('transferEntryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const items = [];
    document.querySelectorAll('#transferItemsTable tbody tr').forEach(row => {
        const itemId = row.querySelector('.item-id')?.value;
        if (itemId) {
            items.push({
                item_id: itemId,
                quantity: parseInt(row.querySelector('.quantity-field').value),
                condition: row.querySelector('.condition-field').value,
                item_status: row.querySelector('.status-field').value
            });
        }
    });

    if (items.length === 0) {
        alert('Please add at least one item to transfer');
        return;
    }

    const formData = new FormData();
    formData.append('source_location_id', document.getElementById('transferSourceLocation').value);
    formData.append('dest_location_id', document.getElementById('transferDestLocation').value);
    formData.append('transaction_date', document.getElementById('transferTransactionDate').value);
    formData.append('item_type', document.getElementById('transferItemType').value);
    formData.append('category', document.getElementById('transferCategory').value);
    formData.append('book_volume', document.getElementById('transferBookVolume').value);
    formData.append('remarks', document.getElementById('transferRemarks').value);
    
    items.forEach((item, index) => {
        formData.append(`items[${index}][item_id]`, item.item_id);
        formData.append(`items[${index}][quantity]`, item.quantity);
        formData.append(`items[${index}][condition]`, item.condition);
        formData.append(`items[${index}][item_status]`, item.item_status);
    });

    fetch('<?= APP_URL; ?>stock/saveTransferEntry', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Transfer entry saved successfully!\nBatch Code: ' + data.batch_code);
            document.getElementById('transferEntryForm').reset();
            document.getElementById('transferItemsBody').innerHTML = 
                '<tr id="emptyTransferRow"><td colspan="8" class="text-center text-muted py-3">No items added. Click "Add Item" to add items to transfer.</td></tr>';
            transferItemCount = 0;
        } else {
            alert('✗ Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

// Load statistics on page load
window.addEventListener('load', function() {
    fetch('<?= APP_URL; ?>stock/summary')
        .then(response => response.text())
        .then(data => {
            // Parse and update statistics
            console.log('Stats loaded');
        })
        .catch(error => console.error('Error:', error));
});
</script>

<style>
.accordion-button {
    padding: 1rem;
    font-size: 1rem;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #000;
}

.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}

.form-control-sm, .form-select-sm {
    height: 32px;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35rem 0.65rem;
}
</style>