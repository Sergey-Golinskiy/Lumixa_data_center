<?php $this->section('content'); ?>

<!-- Back button -->
<div class="mb-3">
    <a href="/warehouse/documents" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_documents') ?>
    </a>
</div>

<form method="POST" action="<?= $document ? "/warehouse/documents/{$document['id']}" : '/warehouse/documents' ?>" id="document-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="row">
        <!-- Document Header -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-file-text-line me-2"></i><?= $this->__('document_information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label"><?= $this->__('document_type') ?> <span class="text-danger">*</span></label>
                            <?php $currentType = $document['type'] ?? $selectedType ?? $this->old('type'); ?>
                            <select id="type" name="type" class="form-select" required <?= $document ? 'disabled' : '' ?>>
                                <option value=""><?= $this->__('select_type') ?></option>
                                <?php foreach ($types as $value => $label): ?>
                                <option value="<?= $this->e($value) ?>" <?= $currentType === $value ? 'selected' : '' ?>>
                                    <?= $this->e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($document): ?>
                            <input type="hidden" name="type" value="<?= $this->e($document['type']) ?>">
                            <?php endif; ?>
                            <?php if ($this->hasError('type')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('type') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="document_date" class="form-label"><?= $this->__('document_date') ?> <span class="text-danger">*</span></label>
                            <input type="date" id="document_date" name="document_date" class="form-control" required
                                   value="<?= $this->e($document['document_date'] ?? $this->old('document_date', date('Y-m-d'))) ?>">
                            <?php if ($this->hasError('document_date')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('document_date') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="partner_id" class="form-label"><?= $this->__('partner') ?></label>
                            <select id="partner_id" name="partner_id" class="form-select">
                                <option value="">-- <?= $this->__('no_partner') ?> --</option>
                                <?php foreach ($partners as $partner): ?>
                                <option value="<?= $partner['id'] ?>" <?= ($document['partner_id'] ?? $this->old('partner_id')) == $partner['id'] ? 'selected' : '' ?>>
                                    <?= $this->e($partner['name']) ?> (<?= $this->e($partner['type']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($this->hasError('partner_id')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('partner_id') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3 costing-method-field" style="display: none;">
                            <label for="costing_method" class="form-label"><?= $this->__('issue_costing_method') ?></label>
                            <?php
                            $currentCostingMethod = $document['costing_method'] ?? $this->old('costing_method', $defaultCostingMethod ?? 'FIFO');
                            ?>
                            <select id="costing_method" name="costing_method" class="form-select" <?= !($allowCostingOverride ?? true) ? 'disabled' : '' ?>>
                                <?php foreach (($costingMethods ?? []) as $value => $label): ?>
                                <option value="<?= $this->e($value) ?>" <?= $currentCostingMethod === $value ? 'selected' : '' ?>>
                                    <?= $this->e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!($allowCostingOverride ?? true)): ?>
                            <small class="text-muted"><?= $this->__('issue_costing_method_locked') ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"><?= $this->e($document['notes'] ?? $this->old('notes')) ?></textarea>
                        <?php if ($this->hasError('notes')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('notes') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-calculator-line me-2"></i><?= $this->__('summary') ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <?php if ($document): ?>
                                <tr>
                                    <th class="ps-0 text-muted"><?= $this->__('document_number') ?></th>
                                    <td class="text-primary fw-semibold"><?= $this->e($document['document_number']) ?></td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted"><?= $this->__('status') ?></th>
                                    <td><span class="badge bg-warning-subtle text-warning"><?= $this->__('draft') ?></span></td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <th class="ps-0 text-muted"><?= $this->__('document_number') ?></th>
                                    <td class="text-muted fst-italic"><?= $this->__('will_be_assigned_on_save') ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th class="ps-0 text-muted"><?= $this->__('total_lines') ?></th>
                                    <td id="total-lines">0</td>
                                </tr>
                                <tr>
                                    <th class="ps-0 text-muted"><?= $this->__('total_amount') ?></th>
                                    <td class="fw-semibold" id="total-amount">0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Lines -->
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="ri-list-check me-2"></i><?= $this->__('document_lines') ?></h5>
            <button type="button" class="btn btn-success btn-sm" onclick="addLine()">
                <i class="ri-add-line me-1"></i> <?= $this->__('add_line') ?>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="lines-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 200px;"><?= $this->__('item') ?> <span class="text-danger">*</span></th>
                            <th style="width: 100px;"><?= $this->__('quantity') ?> <span class="text-danger">*</span></th>
                            <th style="width: 120px;"><?= $this->__('unit_price') ?></th>
                            <th style="width: 120px;"><?= $this->__('total') ?></th>
                            <th class="batch-column" style="width: 180px; display: none;"><?= $this->__('batch_allocations') ?></th>
                            <th style="width: 150px;"><?= $this->__('notes') ?></th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        <!-- Lines will be added dynamically -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-semibold"><?= $this->__('total') ?>:</td>
                            <td class="fw-semibold" id="footer-total">0.00</td>
                            <td class="batch-column" style="display: none;"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="p-3">
                <small class="text-muted"><span class="text-danger">*</span> <?= $this->__('required_fields_add_line') ?></small>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-success">
            <i class="ri-save-line me-1"></i> <?= $this->__('save_document') ?>
        </button>
        <a href="/warehouse/documents" class="btn btn-soft-secondary"><?= $this->__('cancel') ?></a>
    </div>
</form>

<script>
// Available items for selection
const items = <?= json_encode($items) ?>;
const existingLines = <?= json_encode($lines ?? []) ?>;
let lineNumber = 0;
const batchColumns = () => document.querySelectorAll('.batch-column');
const costingField = document.querySelector('.costing-method-field');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load existing lines if editing
    if (existingLines.length > 0) {
        existingLines.forEach(function(line) {
            if (line.batch_allocations) {
                line.batch_allocations_raw = line.batch_allocations.map(function(allocation) {
                    return `${allocation.batch_id}:${allocation.quantity}`;
                }).join(', ');
            }
            addLine(line);
        });
    } else {
        // Add one empty line by default
        addLine();
    }
    updateTotals();
    updateCostingVisibility();

    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.addEventListener('change', updateCostingVisibility);
    }

    const costingSelect = document.getElementById('costing_method');
    if (costingSelect) {
        costingSelect.addEventListener('change', updateCostingVisibility);
    }
});

function addLine(data = null) {
    lineNumber++;
    const tbody = document.getElementById('lines-body');
    const row = document.createElement('tr');
    row.id = 'line-' + lineNumber;
    row.dataset.lineNumber = lineNumber;

    // Build item options
    let itemOptions = '<option value=""><?= $this->__('select_item') ?></option>';
    items.forEach(function(item) {
        const selected = data && data.item_id == item.id ? 'selected' : '';
        itemOptions += `<option value="${item.id}" data-unit="${item.unit}" ${selected}>${item.sku} - ${item.name}</option>`;
    });

    row.innerHTML = `
        <td>${lineNumber}</td>
        <td>
            <select name="lines[${lineNumber}][item_id]" class="form-select form-select-sm" required onchange="updateLineUnit(${lineNumber})">
                ${itemOptions}
            </select>
        </td>
        <td>
            <input type="number" name="lines[${lineNumber}][quantity]" class="form-control form-control-sm" step="0.001" min="0.001" required
                   value="${data ? data.quantity : ''}" onchange="updateLineTotal(${lineNumber})">
        </td>
        <td>
            <input type="number" name="lines[${lineNumber}][unit_price]" class="form-control form-control-sm" step="0.01" min="0"
                   value="${data ? data.unit_price : '0.00'}" onchange="updateLineTotal(${lineNumber})">
        </td>
        <td class="line-total fw-medium">${data ? formatNumber(data.total_price || 0) : '0.00'}</td>
        <td class="batch-column" style="display: none;">
            <input type="text" name="lines[${lineNumber}][batch_allocations]" class="form-control form-control-sm" placeholder="<?= $this->__('batch_allocations_hint') ?>"
                   value="${data && data.batch_allocations_raw ? data.batch_allocations_raw : ''}">
        </td>
        <td>
            <input type="text" name="lines[${lineNumber}][notes]" class="form-control form-control-sm" placeholder="<?= $this->__('notes') ?>" value="${data ? (data.notes || '') : ''}">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-soft-danger" onclick="removeLine(${lineNumber})">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);
    updateTotals();
}

function removeLine(num) {
    const row = document.getElementById('line-' + num);
    if (row) {
        row.remove();
        updateTotals();
    }
}

function updateLineUnit(num) {
    const row = document.getElementById('line-' + num);
    const select = row.querySelector('select');
    const option = select.options[select.selectedIndex];
    // Could display unit label if needed
}

function updateLineTotal(num) {
    const row = document.getElementById('line-' + num);
    const qty = parseFloat(row.querySelector('input[name*="quantity"]').value) || 0;
    const price = parseFloat(row.querySelector('input[name*="unit_price"]').value) || 0;
    const total = qty * price;
    row.querySelector('.line-total').textContent = formatNumber(total);
    updateTotals();
}

function updateTotals() {
    const rows = document.querySelectorAll('#lines-body tr');
    let totalAmount = 0;
    let totalLines = 0;

    rows.forEach(function(row) {
        totalLines++;
        const totalText = row.querySelector('.line-total').textContent;
        totalAmount += parseFloat(totalText.replace(/\s/g, '')) || 0;
    });

    document.getElementById('total-lines').textContent = totalLines;
    document.getElementById('total-amount').textContent = formatNumber(totalAmount);
    document.getElementById('footer-total').textContent = formatNumber(totalAmount);
}

function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function updateCostingVisibility() {
    const typeSelect = document.getElementById('type');
    const costingSelect = document.getElementById('costing_method');
    const typeValue = typeSelect ? typeSelect.value : '';
    const costingValue = costingSelect ? costingSelect.value : '';
    const isIssueType = ['issue', 'adjustment', 'stocktake'].includes(typeValue);
    if (costingField) {
        costingField.style.display = isIssueType ? 'block' : 'none';
    }
    const showBatch = isIssueType && costingValue === 'MANUAL';
    batchColumns().forEach(function(col) {
        col.style.display = showBatch ? '' : 'none';
    });
}

// Form validation
document.getElementById('document-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#lines-body tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('<?= $this->__('alert_add_one_line') ?>');
        return false;
    }

    // Check all lines have items selected
    let valid = true;
    rows.forEach(function(row) {
        const itemSelect = row.querySelector('select');
        if (!itemSelect.value) {
            valid = false;
        }
    });

    if (!valid) {
        e.preventDefault();
        alert('<?= $this->__('alert_select_item_all_lines') ?>');
        return false;
    }
});
</script>

<?php $this->endSection(); ?>
