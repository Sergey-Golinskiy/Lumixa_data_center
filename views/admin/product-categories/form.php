<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $category ? $this->__('edit_category') : $this->__('create_category') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $category ? "/admin/product-categories/{$category['id']}" : '/admin/product-categories' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required maxlength="150"
                               value="<?= $this->e($category['name'] ?? $this->old('name')) ?>">
                        <?php if ($this->hasError('name')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label"><?= $this->__('description') ?></label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?= $this->e($category['description'] ?? $this->old('description')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                   <?= ($category['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $category ? $this->__('save_changes') : $this->__('create_category') ?>
                        </button>
                        <a href="/admin/product-categories" class="btn btn-soft-secondary">
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
                <a href="/admin/product-categories" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
