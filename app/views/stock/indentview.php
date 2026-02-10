<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="header-title mb-0">Indent Details</h4>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-printer"></i> Print
                            </button>
                            <a href="<?= APP_URL; ?>indent" class="btn btn-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body" id="printArea">
                        <!-- Indent Header (Physical Form Style) -->
                        <div class="text-center mb-4">
                            <h5>Engineering College/Govt. Polytechnic/Technical High School</h5>
                            <p class="mb-0"><?= htmlspecialchars($indent['college_name']); ?></p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Book No.</strong> <?= $indent['book_no']; ?>
                            </div>
                            <div class="col-6 text-end">
                                <strong>Indent No.</strong> <?= $indent['indent_no']; ?><br>
                                <strong>Date:</strong> <?= date('d.m.Y', strtotime($indent['indent_date'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($indent['purpose'])): ?>
                        <div class="mb-3">
                            <p><strong>Please sanction the issue of the following materials for use in</strong></p>
                            <p><?= htmlspecialchars($indent['purpose']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Items Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sl. No.</th>
                                        <th>Particulars</th>
                                        <th>For what purpose</th>
                                        <th>Qty. intended</th>
                                        <th>Qty. passed</th>
                                        <th>Qty. issued</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?= $item['sl_no']; ?></td>
                                                <td>
                                                    <?= htmlspecialchars($item['item_name'] ?? 'N/A'); ?>
                                                    <?php if (!empty($item['make_name'])): ?>
                                                        <br><small class="text-muted">Make: <?= $item['make_name']; ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['model_name'])): ?>
                                                        <br><small class="text-muted">Model: <?= $item['model_name']; ?></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['item_description'])): ?>
                                                        <br><small><?= htmlspecialchars($item['item_description']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($item['item_purpose'] ?? ''); ?></td>
                                                <td class="text-center"><?= $item['qty_intended']; ?></td>
                                                <td class="text-center"><?= $item['qty_passed'] ?: '-'; ?></td>
                                                <td class="text-center"><?= $item['qty_issued'] ?: '-'; ?></td>
                                                <td><?= htmlspecialchars($item['remarks'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No items found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Signatures Section -->
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>Workshop Instructor/Superintendent:</strong>
                                    <?php if ($indent['verified_by']): ?>
                                        <span class="text-success">
                                            <?= htmlspecialchars($indent['verified_by_name'] ?? ''); ?>
                                            <i class="ti ti-check-circle"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </p>
                                <p class="ms-3 mb-3">
                                    <small>
                                        <?php if ($indent['verified_by']): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Verified</span>
                                        <?php endif; ?>
                                    </small>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>W/Shop Foreman:</strong>
                                    <span class="text-muted">________________</span>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>Passed:</strong>
                                    <?php if ($indent['passed_by']): ?>
                                        <span class="text-success">
                                            <?= htmlspecialchars($indent['passed_by_name'] ?? ''); ?>
                                            <i class="ti ti-check-circle"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </p>
                                <p class="ms-3 mb-3">
                                    <small>
                                        <?php if ($indent['passed_by']): ?>
                                            <span class="badge bg-primary">Passed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Passed</span>
                                        <?php endif; ?>
                                    </small>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>Superintendent:</strong>
                                    <span class="text-muted">________________</span>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>Issued:</strong>
                                    <?php if ($indent['issued_by']): ?>
                                        <span class="text-warning">
                                            <?= htmlspecialchars($indent['issued_by_name'] ?? ''); ?>
                                            <i class="ti ti-check-circle"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </p>
                                <p class="ms-3 mb-3">
                                    <small><strong>Store-keeper</strong></small>
                                </p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1">
                                    <strong>Received:</strong>
                                    <?php if ($indent['received_by']): ?>
                                        <span class="text-success">
                                            <?= htmlspecialchars($indent['received_by_name'] ?? ''); ?>
                                            <i class="ti ti-check-circle"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Pending</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="text-center mt-4">
                            <?php
                            $statusColors = [
                                'CREATED' => 'secondary',
                                'VERIFIED' => 'info',
                                'PASSED' => 'primary',
                                'ISSUED' => 'warning',
                                'RECEIVED' => 'success'
                            ];
                            $color = $statusColors[$indent['status']] ?? 'secondary';
                            ?>
                            <h4>
                                <span class="badge bg-<?= $color ?>">
                                    Current Status: <?= $indent['status']; ?>
                                </span>
                            </h4>
                        </div>

                        <!-- Action Buttons (based on status) -->
                        <div class="text-center mt-4 no-print">
                            <?php if ($indent['status'] == 'CREATED'): ?>
                                <button class="btn btn-success" onclick="verifyIndent(<?= $indent['id']; ?>)">
                                    <i class="ti ti-check"></i> Verify Indent
                                </button>
                            <?php endif; ?>

                            <?php if ($indent['status'] == 'VERIFIED'): ?>
                                <button class="btn btn-primary" onclick="openPassModal(<?= $indent['id']; ?>)">
                                    <i class="ti ti-check-double"></i> Pass Indent
                                </button>
                            <?php endif; ?>

                            <?php if ($indent['status'] == 'PASSED'): ?>
                                <button class="btn btn-warning" onclick="openIssueModal(<?= $indent['id']; ?>)">
                                    <i class="ti ti-package"></i> Issue Indent
                                </button>
                            <?php endif; ?>

                            <?php if ($indent['status'] == 'ISSUED'): ?>
                                <button class="btn btn-success" onclick="receiveIndent(<?= $indent['id']; ?>)">
                                    <i class="ti ti-check-circle"></i> Mark as Received
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Timestamps -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="row text-muted small">
                                <div class="col-6">
                                    <strong>Created:</strong> <?= date('d-m-Y H:i', strtotime($indent['created_at'])); ?>
                                    by <?= htmlspecialchars($indent['created_by_name'] ?? 'N/A'); ?>
                                </div>
                                <div class="col-6 text-end">
                                    <strong>Last Updated:</strong> <?= date('d-m-Y H:i', strtotime($indent['updated_at'])); ?>
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

<style>
@media print {
    .no-print, .sidebar, .topbar, .card-header .btn, .footer {
        display: none !important;
    }
    .page-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<script>
function verifyIndent(id) {
    Swal.fire({
        title: 'Verify Indent?',
        text: "This will mark the indent as verified",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Verify!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= APP_URL; ?>indent/verifyIndent',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Verified!',
                            text: res.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                }
            });
        }
    });
}

function receiveIndent(id) {
    Swal.fire({
        title: 'Mark as Received?',
        text: "This will complete the indent process",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Mark Received!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= APP_URL; ?>indent/receiveIndent',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Received!',
                            text: res.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }
                }
            });
        }
    });
}
</script>