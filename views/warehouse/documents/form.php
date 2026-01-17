<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/documents" class="btn btn-secondary">&laquo; Back to Documents</a>
</div>

<form method="POST" action="<?= $document ? "/warehouse/documents/{$document['id']}" : '/warehouse/documents' ?>" id="document-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="detail-grid">
        <!-- Document Header -->
        <div class="card">
            <div class="card-header">Document Information</div>
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
                    <label for="document_date">Document Date *</label>
                    <input type="date" id="document_date" name="document_date" required
                           value="<?= $this->e($document['document_date'] ?? $this->old('document_date', date('Y-m-d'))) ?>">
                    <?php if ($this->hasError('document_date')): ?>
                    <span class="error"><?= $this->error('document_date') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="partner_id">Partner</label>
                    <select id="partner_id" name="partner_id">
                        <option value="">-- No Partner --</option>
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

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"><?= $this->e($document['notes'] ?? $this->old('notes')) ?></textarea>
                    <?php if ($this->hasError('notes')): ?>
                    <span class="error"><?= $this->error('notes') ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header">Summary</div>
            <div class="card-body">
                <?php if ($document): ?>
                <div class="detail-row">
                    <span class="detail-label">Document #</span>
                    <span class="detail-value"><strong><?= $this->e($document['document_number']) ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="badge badge-warning">Draft</span>
                    </span>
                </div>
                <?php else: ?>
                <div class="detail-row">
                    <span class="detail-label">Document #</span>
                    <span class="detail-value"><em>Will be assigned on save</em></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Total Lines</span>
                    <span class="detail-value" id="total-lines">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value" id="total-amount">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Lines -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            Document Lines
            <button type="button" class="btn btn-primary btn-sm" onclick="addLine()" style="float: right;">+ Add Line</button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="lines-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 200px;">Item *</th>
                            <th style="width: 100px;">Quantity *</th>
                            <th style="width: 120px;">Unit Price</th>
                            <th style="width: 120px;">Total</th>
                            <th style="width: 150px;">Notes</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        <!-- Lines will be added dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                            <td><strong id="footer-total">0.00</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="text-muted" style="margin-top: 10px; font-size: 13px;">
                * Required fields. Add at least one line to save the document.
            </p>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary">Save Document</button>
        <a href="/warehouse/documents" class="btn btn-secondary">Cancel</a>
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load existing lines if editing
    if (existingLines.length > 0) {
        existingLines.forEach(function(line) {
            addLine(line);
        });
    } else {
        // Add one empty line by default
        addLine();
    }
    updateTotals();
});

function addLine(data = null) {
    lineNumber++;
    const tbody = document.getElementById('lines-body');
    const row = document.createElement('tr');
    row.id = 'line-' + lineNumber;
    row.dataset.lineNumber = lineNumber;

    // Build item options
    let itemOptions = '<option value="">Select Item</option>';
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
        <td>
            <input type="text" name="lines[${lineNumber}][notes]" placeholder="Notes" value="${data ? (data.notes || '') : ''}">
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

// Form validation
document.getElementById('document-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#lines-body tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('Please add at least one line to the document.');
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
        alert('Please select an item for all lines.');
        return false;
    }
});
</script>

<?php $this->endSection(); ?>
