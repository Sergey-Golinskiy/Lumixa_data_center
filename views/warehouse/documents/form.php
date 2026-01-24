<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/documents" class="btn btn-secondary">&laquo; <?= $this->__('back_to_documents') ?></a>
</div>

<form method="POST" action="<?= $document ? "/warehouse/documents/{$document['id']}" : '/warehouse/documents' ?>" id="document-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="detail-grid">
        <!-- Document Header -->
        <div class="card">
            <div class="card-header"><?= $this->__('document_information') ?></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="type"><?= $this->__('document_type') ?> *</label>
                    <?php $currentType = $document['type'] ?? $selectedType ?? $this->old('type'); ?>
                    <select id="type" name="type" required <?= $document ? 'disabled' : '' ?>>
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
                    <span class="error"><?= $this->error('type') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="document_date"><?= $this->__('document_date') ?> *</label>
                    <input type="date" id="document_date" name="document_date" required
                           value="<?= $this->e($document['document_date'] ?? $this->old('document_date', date('Y-m-d'))) ?>">
                    <?php if ($this->hasError('document_date')): ?>
                    <span class="error"><?= $this->error('document_date') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="partner_id"><?= $this->__('partner') ?></label>
                    <select id="partner_id" name="partner_id">
                        <option value="">-- <?= $this->__('no_partner') ?> --</option>
                        <?php foreach ($partners as $partner): ?>
                        <option value="<?= $partner['id'] ?>" <?= ($document['partner_id'] ?? $this->old('partner_id')) == $partner['id'] ? 'selected' : '' ?>>
                            <?= $this->e($partner['name']) ?> (<?= $this->e($partner['type']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($this->hasError('partner_id')): ?>
                    <span class="error"><?= $this->error('partner_id') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group costing-method-field">
                    <label for="costing_method"><?= $this->__('issue_costing_method') ?></label>
                    <?php
                    $currentCostingMethod = $document['costing_method'] ?? $this->old('costing_method', $defaultCostingMethod ?? 'FIFO');
                    ?>
                    <select id="costing_method" name="costing_method" <?= !($allowCostingOverride ?? true) ? 'disabled' : '' ?>>
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

                <div class="form-group">
                    <label for="notes"><?= $this->__('notes') ?></label>
                    <textarea id="notes" name="notes" rows="3"><?= $this->e($document['notes'] ?? $this->old('notes')) ?></textarea>
                    <?php if ($this->hasError('notes')): ?>
                    <span class="error"><?= $this->error('notes') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header"><?= $this->__('summary') ?></div>
            <div class="card-body">
                <?php if ($document): ?>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('document_number') ?></span>
                    <span class="detail-value"><strong><?= $this->e($document['document_number']) ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('status') ?></span>
                    <span class="detail-value">
                        <span class="badge badge-warning"><?= $this->__('draft') ?></span>
                    </span>
                </div>
                <?php else: ?>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('document_number') ?></span>
                    <span class="detail-value"><em><?= $this->__('will_be_assigned_on_save') ?></em></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('total_lines') ?></span>
                    <span class="detail-value" id="total-lines">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('total_amount') ?></span>
                    <span class="detail-value" id="total-amount">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Lines -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <?= $this->__('document_lines') ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="addLine()" style="float: right;">+ <?= $this->__('add_line') ?></button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="lines-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 200px;"><?= $this->__('item') ?> *</th>
                            <th style="width: 100px;"><?= $this->__('quantity') ?> *</th>
                            <th style="width: 120px;"><?= $this->__('unit_price') ?></th>
                            <th style="width: 120px;"><?= $this->__('total') ?></th>
                            <th class="batch-column" style="width: 180px;"><?= $this->__('batch_allocations') ?></th>
                            <th style="width: 150px;"><?= $this->__('notes') ?></th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        <!-- Lines will be added dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong><?= $this->__('total') ?>:</strong></td>
                            <td><strong id="footer-total">0.00</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="text-muted" style="margin-top: 10px; font-size: 13px;">
                * <?= $this->__('required_fields_add_line') ?>
            </p>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary"><?= $this->__('save_document') ?></button>
        <a href="/warehouse/documents" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
    </div>
</form>

<style>
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}
.detail-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    flex: 0 0 120px;
    color: var(--text-muted);
    font-size: 13px;
}
.detail-value {
    flex: 1;
}
#lines-table input,
#lines-table select {
    width: 100%;
    padding: 6px 8px;
    font-size: 13px;
}
#lines-table td {
    padding: 5px;
    vertical-align: middle;
}
.batch-column {
    display: none;
}
.costing-method-field {
    display: none;
}
.btn-remove {
    background: var(--danger);
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}
.btn-remove:hover {
    background: #c82333;
}
</style>

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
            <select name="lines[${lineNumber}][item_id]" required onchange="updateLineUnit(${lineNumber})">
                ${itemOptions}
            </select>
        </td>
        <td>
            <input type="number" name="lines[${lineNumber}][quantity]" step="0.001" min="0.001" required
                   value="${data ? data.quantity : ''}" onchange="updateLineTotal(${lineNumber})">
        </td>
        <td>
            <input type="number" name="lines[${lineNumber}][unit_price]" step="0.01" min="0"
                   value="${data ? data.unit_price : '0.00'}" onchange="updateLineTotal(${lineNumber})">
        </td>
        <td class="line-total">${data ? formatNumber(data.total_price || 0) : '0.00'}</td>
        <td class="batch-column">
            <input type="text" name="lines[${lineNumber}][batch_allocations]" placeholder="<?= $this->__('batch_allocations_hint') ?>"
                   value="${data && data.batch_allocations_raw ? data.batch_allocations_raw : ''}">
        </td>
        <td>
            <input type="text" name="lines[${lineNumber}][notes]" placeholder="<?= $this->__('notes') ?>" value="${data ? (data.notes || '') : ''}">
        </td>
        <td>
            <button type="button" class="btn-remove" onclick="removeLine(${lineNumber})">&times;</button>
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
