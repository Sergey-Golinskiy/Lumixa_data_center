<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/routing" class="btn btn-secondary">&laquo; Back to Routings</a>
</div>

<form method="POST" action="<?= $routing ? "/catalog/routing/{$routing['id']}" : '/catalog/routing' ?>" id="routing-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="detail-grid">
        <!-- Header -->
        <div class="card">
            <div class="card-header"><?= $routing ? 'Edit Routing' : 'Create New Routing' ?></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="variant_id">Variant *</label>
                    <?php if ($routing): ?>
                    <input type="text" value="<?= $this->e($routing['variant_sku']) ?> - <?= $this->e($routing['variant_name']) ?>" disabled>
                    <input type="hidden" name="variant_id" value="<?= $routing['variant_id'] ?>">
                    <?php else: ?>
                    <select id="variant_id" name="variant_id" required>
                        <option value="">Select Variant</option>
                        <?php foreach ($variants as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= $preselectedVariantId == $v['id'] ? 'selected' : '' ?>>
                            <?= $this->e($v['sku']) ?> - <?= $this->e($v['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="version">Version *</label>
                        <input type="text" id="version" name="version" required
                               value="<?= $this->e($routing['version'] ?? '1.0') ?>">
                    </div>
                    <div class="form-group">
                        <label for="effective_date">Effective Date</label>
                        <input type="date" id="effective_date" name="effective_date"
                               value="<?= $this->e($routing['effective_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= $this->e($routing['name'] ?? '') ?>" placeholder="Optional name">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2"><?= $this->e($routing['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card">
            <div class="card-header">Summary</div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-label">Operations</span>
                    <span class="detail-value" id="total-ops">0</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Time</span>
                    <span class="detail-value" id="total-time">0 min</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Cost</span>
                    <span class="detail-value" id="total-cost">0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Operations -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            Operations
            <button type="button" class="btn btn-primary btn-sm" onclick="addOperation()" style="float: right;">+ Add Operation</button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="ops-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Op #</th>
                            <th style="width: 180px;">Operation Name *</th>
                            <th style="width: 120px;">Work Center</th>
                            <th style="width: 80px;">Setup (min)</th>
                            <th style="width: 80px;">Run (min)</th>
                            <th style="width: 90px;">Labor Cost</th>
                            <th style="width: 90px;">Overhead</th>
                            <th>Instructions</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="ops-body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary"><?= $routing ? 'Update' : 'Create' ?> Routing</button>
        <a href="/catalog/routing" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 100px; color: var(--text-muted); }
#ops-table input, #ops-table select { width: 100%; padding: 6px 8px; font-size: 13px; }
#ops-table td { padding: 5px; vertical-align: top; }
.btn-remove { background: var(--danger); color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
</style>

<script>
const workCenters = <?= json_encode($workCenters) ?>;
const existingOps = <?= json_encode($operations ?? []) ?>;
let opNumber = 0;

document.addEventListener('DOMContentLoaded', function() {
    if (existingOps.length > 0) {
        existingOps.forEach(function(op) { addOperation(op); });
    } else {
        addOperation();
    }
    updateTotals();
});

function addOperation(data = null) {
    opNumber++;
    const tbody = document.getElementById('ops-body');
    const row = document.createElement('tr');
    row.id = 'op-' + opNumber;

    let wcOptions = '<option value="">-</option>';
    workCenters.forEach(function(wc) {
        const sel = data && data.work_center === wc.code ? 'selected' : '';
        wcOptions += `<option value="${wc.code}" ${sel}>${wc.code} - ${wc.name}</option>`;
    });

    const opNum = data ? data.operation_number : (opNumber * 10);
    row.innerHTML = `
        <td><input type="number" name="ops[${opNumber}][op_number]" value="${opNum}" min="1" onchange="updateTotals()"></td>
        <td><input type="text" name="ops[${opNumber}][name]" value="${data ? data.name : ''}" required placeholder="Operation name"></td>
        <td><select name="ops[${opNumber}][work_center]">${wcOptions}</select></td>
        <td><input type="number" name="ops[${opNumber}][setup_time]" value="${data ? data.setup_time_minutes : 0}" min="0" onchange="updateTotals()"></td>
        <td><input type="number" name="ops[${opNumber}][run_time]" value="${data ? data.run_time_minutes : 0}" min="0" onchange="updateTotals()"></td>
        <td><input type="number" name="ops[${opNumber}][labor_cost]" step="0.01" value="${data ? data.labor_cost : 0}" min="0" onchange="updateTotals()"></td>
        <td><input type="number" name="ops[${opNumber}][overhead_cost]" step="0.01" value="${data ? data.overhead_cost : 0}" min="0" onchange="updateTotals()"></td>
        <td><input type="text" name="ops[${opNumber}][instructions]" value="${data ? (data.instructions || '') : ''}" placeholder="Instructions"></td>
        <td><button type="button" class="btn-remove" onclick="removeOp(${opNumber})">&times;</button></td>
    `;
    tbody.appendChild(row);
    updateTotals();
}

function removeOp(num) {
    const row = document.getElementById('op-' + num);
    if (row) { row.remove(); updateTotals(); }
}

function updateTotals() {
    const rows = document.querySelectorAll('#ops-body tr');
    let totalTime = 0, totalCost = 0;
    rows.forEach(function(row) {
        const setup = parseFloat(row.querySelector('input[name*="setup_time"]').value) || 0;
        const run = parseFloat(row.querySelector('input[name*="run_time"]').value) || 0;
        const labor = parseFloat(row.querySelector('input[name*="labor_cost"]').value) || 0;
        const overhead = parseFloat(row.querySelector('input[name*="overhead_cost"]').value) || 0;
        totalTime += setup + run;
        totalCost += labor + overhead;
    });
    document.getElementById('total-ops').textContent = rows.length;
    document.getElementById('total-time').textContent = totalTime + ' min';
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
}

document.getElementById('routing-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#ops-body tr');
    if (rows.length === 0) { e.preventDefault(); alert('Please add at least one operation.'); return false; }
});
</script>

<?php $this->endSection(); ?>
