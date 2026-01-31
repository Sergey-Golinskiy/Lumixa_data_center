<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">
        <i class="ri-route-line me-2"></i>
        <?= $routing ? $this->__('edit_detail_routing') : $this->__('create_new_detail_routing') ?>
    </h4>
    <a href="/catalog/detail-routing" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('detail_routing')]) ?>
    </a>
</div>

<form method="POST" enctype="multipart/form-data" action="<?= $routing ? "/catalog/detail-routing/{$routing['id']}" : '/catalog/detail-routing' ?>" id="detail-routing-form">
    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

    <!-- Basic Information Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="ri-information-line me-2"></i>
            <?= $this->__('basic_information') ?>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('detail') ?> <span class="text-danger">*</span></label>
                    <?php if ($routing): ?>
                    <input type="text" class="form-control" value="<?= $this->e($routing['detail_sku']) ?> - <?= $this->e($routing['detail_name']) ?>" disabled>
                    <input type="hidden" name="detail_id" value="<?= $routing['detail_id'] ?>">
                    <?php else: ?>
                    <select name="detail_id" class="form-select <?= $this->hasError('detail_id') ? 'is-invalid' : '' ?>" required>
                        <option value=""><?= $this->__('select_detail') ?></option>
                        <?php foreach ($details as $detail): ?>
                        <option value="<?= $detail['id'] ?>" <?= (string)$preselectedDetailId === (string)$detail['id'] ? 'selected' : '' ?>>
                            <?= $this->e($detail['sku']) ?> - <?= $this->e($detail['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($this->hasError('detail_id')): ?>
                    <div class="invalid-feedback"><?= $this->error('detail_id') ?></div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('version') ?> <span class="text-danger">*</span></label>
                    <input type="text" name="version" class="form-control <?= $this->hasError('version') ? 'is-invalid' : '' ?>" required
                           value="<?= $this->e($routing['version'] ?? '1.0') ?>">
                    <?php if ($this->hasError('version')): ?>
                    <div class="invalid-feedback"><?= $this->error('version') ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('effective_date') ?></label>
                    <input type="date" name="effective_date" class="form-control"
                           value="<?= $this->e($routing['effective_date'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('name') ?></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= $this->e($routing['name'] ?? '') ?>" placeholder="<?= $this->__('optional_name') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label"><?= $this->__('notes') ?></label>
                    <textarea name="notes" class="form-control" rows="2"><?= $this->e($routing['notes'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('photo') ?></label>
                    <?php if (!empty($routing['image_path'])): ?>
                    <div class="mb-2">
                        <img src="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>"
                             class="img-thumbnail" style="max-height: 100px;" data-image-preview="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
        </div>
    </div>

    <!-- Operations Card -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="ri-list-ordered me-2"></i>
            <?= $this->__('operations') ?>
        </div>
        <div class="card-body">
            <?php if ($this->hasError('operations')): ?>
            <div class="alert alert-warning">
                <i class="ri-error-warning-line me-2"></i>
                <?= $this->error('operations') ?>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="operations-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 180px;"><?= $this->__('operation') ?></th>
                            <th><?= $this->__('work_center') ?></th>
                            <th style="width: 100px;"><?= $this->__('setup_time_minutes') ?></th>
                            <th style="width: 100px;"><?= $this->__('run_time_minutes') ?></th>
                            <th style="width: 100px;"><?= $this->__('labor_cost') ?></th>
                            <th style="width: 100px;"><?= $this->__('overhead_cost') ?></th>
                            <th><?= $this->__('instructions') ?></th>
                            <th style="width: 50px;"></th>
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
                                <div class="d-flex gap-2">
                                    <input type="number" name="ops[<?= $index ?>][op_number]" class="form-control form-control-sm"
                                           value="<?= $this->e($op['operation_number'] ?? 10) ?>" min="1" style="width: 70px;">
                                    <input type="text" name="ops[<?= $index ?>][name]" class="form-control form-control-sm"
                                           value="<?= $this->e($op['name'] ?? '') ?>" placeholder="<?= $this->__('operation_name') ?>" required>
                                </div>
                            </td>
                            <td>
                                <select name="ops[<?= $index ?>][work_center]" class="form-select form-select-sm">
                                    <option value=""><?= $this->__('select_work_center') ?></option>
                                    <?php foreach ($workCenters as $center): ?>
                                    <option value="<?= $this->e($center['code']) ?>" <?= ($op['work_center'] ?? '') === $center['code'] ? 'selected' : '' ?>>
                                        <?= $this->e($center['code']) ?> - <?= $this->e($center['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="ops[<?= $index ?>][setup_time]" class="form-control form-control-sm"
                                       value="<?= $this->e($op['setup_time_minutes'] ?? 0) ?>" min="0">
                            </td>
                            <td>
                                <input type="number" name="ops[<?= $index ?>][run_time]" class="form-control form-control-sm"
                                       value="<?= $this->e($op['run_time_minutes'] ?? 0) ?>" min="0">
                            </td>
                            <td>
                                <input type="number" name="ops[<?= $index ?>][labor_cost]" class="form-control form-control-sm"
                                       value="<?= $this->e($op['labor_cost'] ?? 0) ?>" min="0" step="0.01">
                            </td>
                            <td>
                                <input type="number" name="ops[<?= $index ?>][overhead_cost]" class="form-control form-control-sm"
                                       value="<?= $this->e($op['overhead_cost'] ?? 0) ?>" min="0" step="0.01">
                            </td>
                            <td>
                                <input type="text" name="ops[<?= $index ?>][instructions]" class="form-control form-control-sm"
                                       value="<?= $this->e($op['instructions'] ?? '') ?>">
                            </td>
                            <td>
                                <button type="button" class="btn btn-soft-danger btn-sm remove-op" title="<?= $this->__('delete') ?>">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-soft-primary" id="add-op">
                    <i class="ri-add-line me-1"></i> <?= $this->__('add_operation') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">
            <i class="ri-save-line me-1"></i>
            <?= $routing ? $this->__('update') : $this->__('create') ?> <?= $this->__('routing') ?>
        </button>
        <a href="/catalog/detail-routing" class="btn btn-soft-secondary">
            <?= $this->__('cancel') ?>
        </a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('detail-routing-form');
    const tbody = document.querySelector('#operations-table tbody');

    // Remove operation row
    form.addEventListener('click', function(event) {
        if (event.target.closest('.remove-op')) {
            const row = event.target.closest('tr');
            if (tbody.querySelectorAll('tr').length > 1) {
                row.remove();
            }
        }
    });

    // Add operation row
    document.getElementById('add-op').addEventListener('click', function() {
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
});
</script>

<?php $this->endSection(); ?>
