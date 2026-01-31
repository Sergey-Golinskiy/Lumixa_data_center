<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $status ? $this->__('edit_order_status') : $this->__('create_order_status') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $status ? "/admin/order-statuses/{$status['id']}" : '/admin/order-statuses' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label"><?= $this->__('code') ?> <span class="text-danger">*</span></label>
                                <input type="text" id="code" name="code" class="form-control" required maxlength="50"
                                       pattern="[a-zA-Z0-9_]+"
                                       placeholder="e.g. pending, in_progress"
                                       value="<?= $this->e($status['code'] ?? $this->old('code')) ?>">
                                <small class="text-muted"><?= $this->__('order_status_code_hint') ?></small>
                                <?php if ($this->hasError('code')): ?>
                                <div class="invalid-feedback d-block"><?= $this->error('code') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required maxlength="100"
                                       placeholder="e.g. Pending, In Progress"
                                       value="<?= $this->e($status['name'] ?? $this->old('name')) ?>">
                                <?php if ($this->hasError('name')): ?>
                                <div class="invalid-feedback d-block"><?= $this->error('name') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label"><?= $this->__('color') ?> <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <input type="color" id="color" name="color" class="form-control form-control-color"
                                           value="<?= $this->e($status['color'] ?? $this->old('color', '#6b7280')) ?>">
                                    <input type="text" id="color_text" class="form-control" maxlength="7" placeholder="#6b7280" style="width: 120px; font-family: monospace;"
                                           value="<?= $this->e($status['color'] ?? $this->old('color', '#6b7280')) ?>">
                                </div>
                                <?php if ($this->hasError('color')): ?>
                                <div class="invalid-feedback d-block"><?= $this->error('color') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label"><?= $this->__('sort_order') ?></label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control" min="0" max="9999"
                                       value="<?= $this->e($status['sort_order'] ?? $this->old('sort_order', $nextSortOrder)) ?>">
                                <small class="text-muted"><?= $this->__('sort_order_hint') ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label"><?= $this->__('description') ?></label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="<?= $this->__('order_status_description_placeholder') ?>"><?= $this->e($status['description'] ?? $this->old('description')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('preview') ?></label>
                        <div class="p-3 bg-light rounded">
                            <span class="badge rounded-pill px-3 py-2" id="status_preview" style="font-size: 14px; background-color: #6b7280; color: #fff; text-shadow: 0 1px 1px rgba(0,0,0,0.2);">
                                <?= $this->e($status['name'] ?? 'Status Name') ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('options') ?></label>
                        <div class="card border">
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                           <?= ($status['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                                    <small class="d-block text-muted"><?= $this->__('order_status_active_hint') ?></small>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" name="is_default" value="1" id="is_default" class="form-check-input"
                                           <?= ($status['is_default'] ?? $this->old('is_default')) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_default"><?= $this->__('default_status') ?></label>
                                    <small class="d-block text-muted"><?= $this->__('order_status_default_hint') ?></small>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_final" value="1" id="is_final" class="form-check-input"
                                           <?= ($status['is_final'] ?? $this->old('is_final')) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_final"><?= $this->__('final_status') ?></label>
                                    <small class="d-block text-muted"><?= $this->__('order_status_final_hint') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $status ? $this->__('save_changes') : $this->__('create_order_status') ?>
                        </button>
                        <a href="/admin/order-statuses" class="btn btn-soft-secondary">
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
                <a href="/admin/order-statuses" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_list') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color_text');
    const nameInput = document.getElementById('name');
    const preview = document.getElementById('status_preview');

    function updatePreview() {
        const color = colorInput.value;
        const name = nameInput.value || 'Status Name';
        preview.style.backgroundColor = color;
        preview.textContent = name;
    }

    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
        updatePreview();
    });

    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorInput.value = this.value;
            updatePreview();
        }
    });

    nameInput.addEventListener('input', updatePreview);

    updatePreview();
});
</script>

<?php $this->endSection(); ?>
