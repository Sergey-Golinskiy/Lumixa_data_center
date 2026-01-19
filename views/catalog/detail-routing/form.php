<?php $this->section('content'); ?>

<a href="/catalog/detail-routing" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('detail_routing')]) ?></a>

<form method="POST" enctype="multipart/form-data" action="<?= $routing ? "/catalog/detail-routing/{$routing['id']}" : '/catalog/detail-routing' ?>" id="detail-routing-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <div class="card" style="margin-top: 20px;">
        <div class="card-header"><?= $routing ? $this->__('edit_detail_routing') : $this->__('create_new_detail_routing') ?></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label><?= $this->__('detail') ?> *</label>
                    <?php if ($routing): ?>
                    <input type="text" value="<?= $this->e($routing['detail_sku']) ?> - <?= $this->e($routing['detail_name']) ?>" disabled>
                    <input type="hidden" name="detail_id" value="<?= $routing['detail_id'] ?>">
                    <?php else: ?>
                    <select name="detail_id" required>
                        <option value=""><?= $this->__('select_detail') ?></option>
                        <?php foreach ($details as $detail): ?>
                        <option value="<?= $detail['id'] ?>" <?= (string)$preselectedDetailId === (string)$detail['id'] ? 'selected' : '' ?>>
                            <?= $this->e($detail['sku']) ?> - <?= $this->e($detail['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <?php if ($this->hasError('detail_id')): ?>
                    <span class="error"><?= $this->error('detail_id') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label><?= $this->__('version') ?> *</label>
                    <input type="text" name="version" required
                           value="<?= $this->e($routing['version'] ?? '1.0') ?>">
                    <?php if ($this->hasError('version')): ?>
                    <span class="error"><?= $this->error('version') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><?= $this->__('effective_date') ?></label>
                    <input type="date" name="effective_date"
                           value="<?= $this->e($routing['effective_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><?= $this->__('name') ?></label>
                    <input type="text" name="name"
                           value="<?= $this->e($routing['name'] ?? '') ?>" placeholder="<?= $this->__('optional_name') ?>">
                </div>
            </div>

            <div class="form-group">
                <label><?= $this->__('notes') ?></label>
                <textarea name="notes" rows="2"><?= $this->e($routing['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label><?= $this->__('photo') ?></label>
                <?php if (!empty($routing['image_path'])): ?>
                <div class="form-image-preview">
                    <img src="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>">
                </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header"><?= $this->__('operations') ?></div>
        <div class="card-body">
            <?php if ($this->hasError('operations')): ?>
            <div class="alert alert-warning"><?= $this->error('operations') ?></div>
            <?php endif; ?>

            <table class="table" id="operations-table">
                <thead>
                    <tr>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th><?= $this->__('setup_time_minutes') ?></th>
                        <th><?= $this->__('run_time_minutes') ?></th>
                        <th><?= $this->__('labor_cost') ?></th>
                        <th><?= $this->__('overhead_cost') ?></th>
                        <th><?= $this->__('instructions') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $ops = $operations ?? []; ?>
                    <?php if (empty($ops)): ?>
                    <?php $ops = [['operation_number' => 10, 'name' => '', 'work_center' => '', 'setup_time_minutes' => 0, 'run_time_minutes' => 0, 'labor_cost' => 0, 'overhead_cost' => 0, 'instructions' => '']]; ?>
                    <?php endif; ?>
                    <?php foreach ($ops as $index => $op): ?>
                    <tr>
                        <td>
                            <input type="number" name="ops[<?= $index ?>][op_number]" value="<?= $this->e($op['operation_number'] ?? 10) ?>" min="1" style="width: 70px;">
                            <input type="text" name="ops[<?= $index ?>][name]" value="<?= $this->e($op['name'] ?? '') ?>" placeholder="<?= $this->__('operation_name') ?>" required>
                        </td>
                        <td>
                            <select name="ops[<?= $index ?>][work_center]">
                                <option value=""><?= $this->__('select_work_center') ?></option>
                                <?php foreach ($workCenters as $center): ?>
                                <option value="<?= $this->e($center['code']) ?>" <?= ($op['work_center'] ?? '') === $center['code'] ? 'selected' : '' ?>>
                                    <?= $this->e($center['code']) ?> - <?= $this->e($center['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="ops[<?= $index ?>][setup_time]" value="<?= $this->e($op['setup_time_minutes'] ?? 0) ?>" min="0"></td>
                        <td><input type="number" name="ops[<?= $index ?>][run_time]" value="<?= $this->e($op['run_time_minutes'] ?? 0) ?>" min="0"></td>
                        <td><input type="number" name="ops[<?= $index ?>][labor_cost]" value="<?= $this->e($op['labor_cost'] ?? 0) ?>" min="0" step="0.01"></td>
                        <td><input type="number" name="ops[<?= $index ?>][overhead_cost]" value="<?= $this->e($op['overhead_cost'] ?? 0) ?>" min="0" step="0.01"></td>
                        <td><input type="text" name="ops[<?= $index ?>][instructions]" value="<?= $this->e($op['instructions'] ?? '') ?>"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-op">&times;</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="button" class="btn btn-outline" id="add-op">+ <?= $this->__('add_operation') ?></button>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $routing ? $this->__('update') : $this->__('create') ?> <?= $this->__('routing') ?></button>
        <a href="/catalog/detail-routing" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
    </div>
</form>

<script>
document.getElementById('detail-routing-form').addEventListener('click', function(event) {
    if (event.target.classList.contains('remove-op')) {
        const row = event.target.closest('tr');
        const tbody = row.parentElement;
        if (tbody.querySelectorAll('tr').length > 1) {
            row.remove();
        }
    }
});

document.getElementById('add-op').addEventListener('click', function() {
    const tbody = document.querySelector('#operations-table tbody');
    const rows = tbody.querySelectorAll('tr');
    const index = rows.length;
    const template = rows[rows.length - 1].cloneNode(true);

    template.querySelectorAll('input, select').forEach(function(input) {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
        }
        if (input.tagName === 'INPUT') {
            input.value = input.type === 'number' ? 0 : '';
        }
        if (input.tagName === 'SELECT') {
            input.selectedIndex = 0;
        }
    });

    tbody.appendChild(template);
});
</script>

