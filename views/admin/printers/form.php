<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $printer ? $this->__('edit_printer') : $this->__('create_printer') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $printer ? "/admin/printers/{$printer['id']}" : '/admin/printers' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required maxlength="150"
                                       value="<?= $this->e($printer['name'] ?? $this->old('name')) ?>">
                                <?php if ($this->hasError('name')): ?>
                                <div class="invalid-feedback d-block"><?= $this->error('name') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model" class="form-label"><?= $this->__('model') ?></label>
                                <input type="text" id="model" name="model" class="form-control"
                                       value="<?= $this->e($printer['model'] ?? $this->old('model')) ?>">
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($hasCodeColumn)): ?>
                    <div class="mb-3">
                        <label for="code" class="form-label"><?= $this->__('code') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" required maxlength="50"
                               value="<?= $this->e($printer['code'] ?? $this->old('code')) ?>">
                        <?php if ($this->hasError('code')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('code') ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="power_watts" class="form-label"><?= $this->__('power_watts') ?></label>
                                <input type="number" id="power_watts" name="power_watts" class="form-control" step="0.01" min="0"
                                       value="<?= $this->e($printer['power_watts'] ?? $this->old('power_watts', 0)) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="electricity_cost_per_kwh" class="form-label"><?= $this->__('electricity_cost_per_kwh') ?></label>
                                <input type="number" id="electricity_cost_per_kwh" name="electricity_cost_per_kwh" class="form-control" step="0.0001" min="0"
                                       value="<?= $this->e($printer['electricity_cost_per_kwh'] ?? $this->old('electricity_cost_per_kwh', 0)) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amortization_per_hour" class="form-label"><?= $this->__('amortization_per_hour') ?></label>
                                <input type="number" id="amortization_per_hour" name="amortization_per_hour" class="form-control" step="0.0001" min="0"
                                       value="<?= $this->e($printer['amortization_per_hour'] ?? $this->old('amortization_per_hour', 0)) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_per_hour" class="form-label"><?= $this->__('maintenance_per_hour') ?></label>
                                <input type="number" id="maintenance_per_hour" name="maintenance_per_hour" class="form-control" step="0.0001" min="0"
                                       value="<?= $this->e($printer['maintenance_per_hour'] ?? $this->old('maintenance_per_hour', 0)) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"><?= $this->e($printer['notes'] ?? $this->old('notes')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                   <?= ($printer['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $printer ? $this->__('save_changes') : $this->__('create_printer') ?>
                        </button>
                        <a href="/admin/printers" class="btn btn-soft-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <a href="/admin/printers" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
