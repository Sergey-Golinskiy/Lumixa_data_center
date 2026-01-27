<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/product-collections" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $collection ? $this->__('edit_collection') : $this->__('create_collection') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $collection ? "/admin/product-collections/{$collection['id']}" : '/admin/product-collections' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= $this->e($collection['name'] ?? $this->old('name')) ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description"><?= $this->__('description') ?></label>
                <textarea id="description" name="description" rows="4"><?= $this->e($collection['description'] ?? $this->old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($collection['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $collection ? $this->__('save_changes') : $this->__('create_collection') ?>
                </button>
                <a href="/admin/product-collections" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
