<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/partners" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('suppliers')]) ?></a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $partner ? $this->__('edit_supplier') : $this->__('create_new_supplier') ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $partner ? "/warehouse/partners/{$partner['id']}" : '/warehouse/partners' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="code"><?= $this->__('code') ?></label>
                    <input type="text" id="code" name="code"
                           value="<?= $this->e($partner['code'] ?? $this->old('code')) ?>"
                           placeholder="<?= $this->__('auto_generated_if_empty') ?>">
                    <?php if ($this->hasError('code')): ?>
                    <span class="error"><?= $this->error('code') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="type"><?= $this->__('type') ?> *</label>
                    <select id="type" name="type" required>
                        <option value="supplier" <?= ($partner['type'] ?? $this->old('type')) === 'supplier' ? 'selected' : '' ?>><?= $this->__('supplier') ?></option>
                        <option value="customer" <?= ($partner['type'] ?? $this->old('type')) === 'customer' ? 'selected' : '' ?>><?= $this->__('customer') ?></option>
                        <option value="both" <?= ($partner['type'] ?? $this->old('type')) === 'both' ? 'selected' : '' ?>><?= $this->__('both') ?></option>
                    </select>
                    <?php if ($this->hasError('type')): ?>
                    <span class="error"><?= $this->error('type') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> *</label>
                <input type="text" id="name" name="name" required
                       value="<?= $this->e($partner['name'] ?? $this->old('name')) ?>"
                       placeholder="<?= $this->__('company_or_individual_name') ?>">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tax_id"><?= $this->__('tax_id') ?></label>
                    <input type="text" id="tax_id" name="tax_id"
                           value="<?= $this->e($partner['tax_id'] ?? $this->old('tax_id')) ?>"
                           placeholder="<?= $this->__('tax_identification_number') ?>">
                    <?php if ($this->hasError('tax_id')): ?>
                    <span class="error"><?= $this->error('tax_id') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="contact_person"><?= $this->__('contact_person') ?></label>
                    <input type="text" id="contact_person" name="contact_person"
                           value="<?= $this->e($partner['contact_person'] ?? $this->old('contact_person')) ?>"
                           placeholder="<?= $this->__('primary_contact_name') ?>">
                    <?php if ($this->hasError('contact_person')): ?>
                    <span class="error"><?= $this->error('contact_person') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email"><?= $this->__('email') ?></label>
                    <input type="email" id="email" name="email"
                           value="<?= $this->e($partner['email'] ?? $this->old('email')) ?>"
                           placeholder="email@example.com">
                    <?php if ($this->hasError('email')): ?>
                    <span class="error"><?= $this->error('email') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone"><?= $this->__('phone') ?></label>
                    <input type="text" id="phone" name="phone"
                           value="<?= $this->e($partner['phone'] ?? $this->old('phone')) ?>"
                           placeholder="+1 234 567 8900">
                    <?php if ($this->hasError('phone')): ?>
                    <span class="error"><?= $this->error('phone') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="address"><?= $this->__('address') ?></label>
                <textarea id="address" name="address" rows="3"
                          placeholder="<?= $this->__('full_address') ?>"><?= $this->e($partner['address'] ?? $this->old('address')) ?></textarea>
                <?php if ($this->hasError('address')): ?>
                <span class="error"><?= $this->error('address') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="notes"><?= $this->__('notes') ?></label>
                <textarea id="notes" name="notes" rows="2"
                          placeholder="<?= $this->__('additional_notes') ?>"><?= $this->e($partner['notes'] ?? $this->old('notes')) ?></textarea>
                <?php if ($this->hasError('notes')): ?>
                <span class="error"><?= $this->error('notes') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($partner['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $partner ? $this->__('update_supplier') : $this->__('create_supplier') ?></button>
                <a href="/warehouse/partners" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
</style>

<?php $this->endSection(); ?>
