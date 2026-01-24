<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<?php
    $action = $option
        ? "/admin/item-options/{$group}/{$option['id']}"
        : "/admin/item-options/{$group}";
?>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $option ? $this->__('edit_item_option') : $this->__('create_item_option') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $this->e($action) ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= $this->e($option['name'] ?? $this->old('name')) ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <?php if ($showFilament): ?>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_filament" value="1"
                           <?= ($option['is_filament'] ?? $this->old('is_filament')) ? 'checked' : '' ?>>
                    <?= $this->__('filament') ?>
                </label>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($option['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $option ? $this->__('save_changes') : $this->__('create_item_option') ?>
                </button>
                <a href="/admin/item-options/<?= $this->e($group) ?>" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
