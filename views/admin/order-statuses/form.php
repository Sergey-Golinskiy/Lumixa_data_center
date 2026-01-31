<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/order-statuses" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $status ? $this->__('edit_order_status') : $this->__('create_order_status') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $status ? "/admin/order-statuses/{$status['id']}" : '/admin/order-statuses' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="code"><?= $this->__('code') ?> *</label>
                    <input type="text" id="code" name="code" required maxlength="50"
                           pattern="[a-zA-Z0-9_]+"
                           placeholder="e.g. pending, in_progress"
                           value="<?= $this->e($status['code'] ?? $this->old('code')) ?>">
                    <small class="form-hint"><?= $this->__('order_status_code_hint') ?></small>
                    <?php if ($this->hasError('code')): ?>
                    <span class="error"><?= $this->error('code') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="name"><?= $this->__('name') ?> *</label>
                    <input type="text" id="name" name="name" required maxlength="100"
                           placeholder="e.g. Pending, In Progress"
                           value="<?= $this->e($status['name'] ?? $this->old('name')) ?>">
                    <?php if ($this->hasError('name')): ?>
                    <span class="error"><?= $this->error('name') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="color"><?= $this->__('color') ?> *</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="color" name="color"
                               value="<?= $this->e($status['color'] ?? $this->old('color', '#6b7280')) ?>">
                        <input type="text" id="color_text" maxlength="7" placeholder="#6b7280"
                               value="<?= $this->e($status['color'] ?? $this->old('color', '#6b7280')) ?>">
                    </div>
                    <?php if ($this->hasError('color')): ?>
                    <span class="error"><?= $this->error('color') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="sort_order"><?= $this->__('sort_order') ?></label>
                    <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                           value="<?= $this->e($status['sort_order'] ?? $this->old('sort_order', $nextSortOrder)) ?>">
                    <small class="form-hint"><?= $this->__('sort_order_hint') ?></small>
                </div>
            </div>

            <div class="form-group">
                <label for="description"><?= $this->__('description') ?></label>
                <textarea id="description" name="description" rows="3"
                          placeholder="<?= $this->__('order_status_description_placeholder') ?>"><?= $this->e($status['description'] ?? $this->old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label><?= $this->__('preview') ?></label>
                <div class="status-preview-container">
                    <span class="status-preview" id="status_preview">
                        <?= $this->e($status['name'] ?? 'Status Name') ?>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label><?= $this->__('options') ?></label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1"
                               <?= ($status['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                        <?= $this->__('active') ?>
                        <small class="checkbox-hint"><?= $this->__('order_status_active_hint') ?></small>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="is_default" value="1"
                               <?= ($status['is_default'] ?? $this->old('is_default')) ? 'checked' : '' ?>>
                        <?= $this->__('default_status') ?>
                        <small class="checkbox-hint"><?= $this->__('order_status_default_hint') ?></small>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox" name="is_final" value="1"
                               <?= ($status['is_final'] ?? $this->old('is_final')) ? 'checked' : '' ?>>
                        <?= $this->__('final_status') ?>
                        <small class="checkbox-hint"><?= $this->__('order_status_final_hint') ?></small>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $status ? $this->__('save_changes') : $this->__('create_order_status') ?>
                </button>
                <a href="/admin/order-statuses" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: flex;
    gap: 20px;
}

.form-hint {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
}

.color-input-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-input-wrapper input[type="color"] {
    width: 50px;
    height: 38px;
    padding: 2px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
}

.color-input-wrapper input[type="text"] {
    width: 100px;
    font-family: monospace;
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 15px;
    background: var(--bg);
    border-radius: var(--radius);
}

.checkbox-label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.checkbox-hint {
    width: 100%;
    font-size: 12px;
    color: var(--text-muted);
    margin-left: 26px;
}

.status-preview-container {
    padding: 15px;
    background: var(--bg);
    border-radius: var(--radius);
}

.status-preview {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 16px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    background-color: #6b7280;
}

@media (max-width: 600px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>

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
