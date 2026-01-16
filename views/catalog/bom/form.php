<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/bom" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('bom_list')]) ?></a>
</div>

<form method="POST" action="<?= $bom ? "/catalog/bom/{$bom['id']}" : '/catalog/bom' ?>" id="bom-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="detail-grid">
        <!-- BOM Header -->
        <div class="card">
            <div class="card-header"><?= $bom ? $this->__('edit_bom') : $this->__('create_new_bom') ?></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="variant_id"><?= $this->__('variant') ?> *</label>
                    <?php if ($bom): ?>
                    <input type="text" value="<?= $this->e($bom['variant_sku']) ?> - <?= $this->e($bom['variant_name']) ?>" disabled>
                    <input type="hidden" name="variant_id" value="<?= $bom['variant_id'] ?>">
                    <?php else: ?>
                    <select id="variant_id" name="variant_id" required>
                        <option value=""><?= $this->__('select_variant') ?></option>
                        <?php foreach ($variants as $variant): ?>
                        <option value="<?= $variant['id'] ?>" <?= $preselectedVariantId == $variant['id'] ? 'selected' : '' ?>>
                            <?= $this->e($variant['sku']) ?> - <?= $this->e($variant['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="version"><?= $this->__('version') ?> *</label>
                        <input type="text" id="version" name="version" required
                               value="<?= $this->e($bom['version'] ?? $this->old('version', '1.0')) ?>"
                               placeholder="<?= $this->__('version_placeholder') ?>">
                    </div>
                    <div class="form-group">
                        <label for="effective_date"><?= $this->__('effective_date') ?></label>
                        <input type="date" id="effective_date" name="effective_date"
                               value="<?= $this->e($bom['effective_date'] ?? $this->old('effective_date')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="name"><?= $this->__('name') ?></label>
                    <input type="text" id="name" name="name"
                           value="<?= $this->e($bom['name'] ?? $this->old('name')) ?>"
                           placeholder="<?= $this->__('optional_description') ?>">
                </div>

                <div class="form-group">
                    <label for="notes"><?= $this->__('notes') ?></label>
                    <textarea id="notes" name="notes" rows="3"><?= $this->e($bom['notes'] ?? $this->old('notes')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header"><?= $this->__('summary') ?></div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('total_lines') ?></span>
                    <span class="detail-value" id="total-lines">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?= $this->__('total_cost') ?></span>
                    <span class="detail-value" id="total-cost">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Lines -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <?= $this->__('materials') ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="addLine()" style="float: right;">+ <?= $this->__('add_material') ?></button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="lines-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 200px;"><?= $this->__('item') ?> *</th>
                            <th style="width: 100px;"><?= $this->__('quantity') ?> *</th>
                            <th style="width: 100px;"><?= $this->__('unit_cost') ?></th>
                            <th style="width: 80px;"><?= $this->__('waste_percent') ?></th>
                            <th style="width: 100px;"><?= $this->__('total') ?></th>
                            <th><?= $this->__('notes') ?></th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="5" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                        <td><strong id="footer-total">0.00</strong></td>
                        <td colspan="2"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary"><?= $bom ? $this->__('update_bom') : $this->__('create_bom') ?></button>
        <a href="/catalog/bom" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
    </div>
</form>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 100px; color: var(--text-muted); }
#lines-table input, #lines-table select { width: 100%; padding: 6px 8px; font-size: 13px; }
#lines-table td { padding: 5px; vertical-align: middle; }
.btn-remove { background: var(--danger); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
.text-right { text-align: right; }
</style>

<script>
const items = <?= json_encode($items) ?>;
const existingLines = <?= json_encode($lines ?? []) ?>;
let lineNumber = 0;

document.addEventListener('DOMContentLoaded', function() {
    if (existingLines.length > 0) {
        existingLines.forEach(function(line) { addLine(line); });
    } else {
        addLine();
    }
    updateTotals();
});

function addLine(data = null) {
    lineNumber++;
    const tbody = document.getElementById('lines-body');
    const row = document.createElement('tr');
    row.id = 'line-' + lineNumber;

    let itemOptions = '<option value=""><?= $this->__('select_item') ?></option>';
    items.forEach(function(item) {
        const selected = data && data.item_id == item.id ? 'selected' : '';
        itemOptions += `<option value="${item.id}" ${selected}>${item.sku} - ${item.name} (${item.unit})</option>`;
    });

    row.innerHTML = `
        <td>${lineNumber}</td>
        <td><select name="lines[${lineNumber}][item_id]" required onchange="updateTotals()">${itemOptions}</select></td>
        <td><input type="number" name="lines[${lineNumber}][quantity]" step="0.0001" min="0.0001" required value="${data ? data.quantity : ''}" onchange="updateTotals()"></td>
        <td><input type="number" name="lines[${lineNumber}][unit_cost]" step="0.0001" min="0" value="${data ? data.unit_cost : '0'}" onchange="updateTotals()"></td>
        <td><input type="number" name="lines[${lineNumber}][waste_percent]" step="0.01" min="0" max="100" value="${data ? data.waste_percent : '0'}" onchange="updateTotals()"></td>
        <td class="line-total">0.00</td>
        <td><input type="text" name="lines[${lineNumber}][notes]" value="${data ? (data.notes || '') : ''}"></td>
        <td><button type="button" class="btn-remove" onclick="removeLine(${lineNumber})">&times;</button></td>
    `;
    tbody.appendChild(row);
    updateTotals();
}

function removeLine(num) {
    const row = document.getElementById('line-' + num);
    if (row) { row.remove(); updateTotals(); }
}

function updateTotals() {
    const rows = document.querySelectorAll('#lines-body tr');
    let totalCost = 0;
    rows.forEach(function(row) {
        const qty = parseFloat(row.querySelector('input[name*="quantity"]').value) || 0;
        const cost = parseFloat(row.querySelector('input[name*="unit_cost"]').value) || 0;
        const waste = parseFloat(row.querySelector('input[name*="waste_percent"]').value) || 0;
        const total = qty * cost * (1 + waste/100);
        row.querySelector('.line-total').textContent = total.toFixed(2);
        totalCost += total;
    });
    document.getElementById('total-lines').textContent = rows.length;
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
    document.getElementById('footer-total').textContent = totalCost.toFixed(2);
}

document.getElementById('bom-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#lines-body tr');
    if (rows.length === 0) { e.preventDefault(); alert('<?= $this->__('add_material_required') ?>'); return false; }
});
</script>

<?php $this->endSection(); ?>
