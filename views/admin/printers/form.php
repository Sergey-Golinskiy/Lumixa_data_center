<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/printers" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header"><?= $printer ? $this->__('edit_printer') : $this->__('create_printer') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $printer ? "/admin/printers/{$printer['id']}" : '/admin/printers' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="name"><?= $this->__('name') ?> *</label>
                    <input type="text" id="name" name="name" required maxlength="150"
                           value="<?= $this->e($printer['name'] ?? $this->old('name')) ?>">
                    <?php if ($this->hasError('name')): ?>
                    <span class="error"><?= $this->error('name') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="model"><?= $this->__('model') ?></label>
                    <input type="text" id="model" name="model"
                           value="<?= $this->e($printer['model'] ?? $this->old('model')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="power_watts"><?= $this->__('power_watts') ?></label>
                    <input type="number" id="power_watts" name="power_watts" step="0.01" min="0"
                           value="<?= $this->e($printer['power_watts'] ?? $this->old('power_watts', 0)) ?>">
                </div>

                <div class="form-group">
                    <label for="electricity_cost_per_kwh"><?= $this->__('electricity_cost_per_kwh') ?></label>
                    <input type="number" id="electricity_cost_per_kwh" name="electricity_cost_per_kwh" step="0.0001" min="0"
                           value="<?= $this->e($printer['electricity_cost_per_kwh'] ?? $this->old('electricity_cost_per_kwh', 0)) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="amortization_per_hour"><?= $this->__('amortization_per_hour') ?></label>
                    <input type="number" id="amortization_per_hour" name="amortization_per_hour" step="0.0001" min="0"
                           value="<?= $this->e($printer['amortization_per_hour'] ?? $this->old('amortization_per_hour', 0)) ?>">
                </div>

                <div class="form-group">
                    <label for="maintenance_per_hour"><?= $this->__('maintenance_per_hour') ?></label>
                    <input type="number" id="maintenance_per_hour" name="maintenance_per_hour" step="0.0001" min="0"
                           value="<?= $this->e($printer['maintenance_per_hour'] ?? $this->old('maintenance_per_hour', 0)) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea id="notes" name="notes" rows="3"><?= $this->e($printer['notes'] ?? $this->old('notes')) ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($printer['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $printer ? $this->__('save_changes') : $this->__('create_printer') ?>
                </button>
                <a href="/admin/printers" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
