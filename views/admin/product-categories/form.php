<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/product-categories" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $category ? $this->__('edit_category') : $this->__('create_category') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $category ? "/admin/product-categories/{$category['id']}" : '/admin/product-categories' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name" required maxlength="150"
                       value="<?= $this->e($category['name'] ?? $this->old('name')) ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description"><?= $this->__('description') ?></label>
                <textarea id="description" name="description" rows="4"><?= $this->e($category['description'] ?? $this->old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($category['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $category ? $this->__('save_changes') : $this->__('create_category') ?>
                </button>
                <a href="/admin/product-categories" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
</style>

<?php $this->endSection(); ?>
