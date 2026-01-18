<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/warehouse/partners" class="btn btn-secondary">&laquo; Back to Partners</a>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header"><?= $partner ? 'Edit Partner' : 'Create New Partner' ?></div>
    <div class="card-body">
        <form method="POST" action="<?= $partner ? "/warehouse/partners/{$partner['id']}" : '/warehouse/partners' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="code">Code</label>
                    <input type="text" id="code" name="code"
                           value="<?= $this->e($partner['code'] ?? $this->old('code')) ?>"
                           placeholder="Auto-generated if empty">
                    <?php if ($this->hasError('code')): ?>
                    <span class="error"><?= $this->error('code') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="type">Type *</label>
                    <select id="type" name="type" required>
                        <option value="supplier" <?= ($partner['type'] ?? $this->old('type')) === 'supplier' ? 'selected' : '' ?>>Supplier</option>
                        <option value="customer" <?= ($partner['type'] ?? $this->old('type')) === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="both" <?= ($partner['type'] ?? $this->old('type')) === 'both' ? 'selected' : '' ?>>Both</option>
                    </select>
                    <?php if ($this->hasError('type')): ?>
                    <span class="error"><?= $this->error('type') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" required
                       value="<?= $this->e($partner['name'] ?? $this->old('name')) ?>"
                       placeholder="Company or individual name">
                <?php if ($this->hasError('name')): ?>
                <span class="error"><?= $this->error('name') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tax_id">Tax ID</label>
                    <input type="text" id="tax_id" name="tax_id"
                           value="<?= $this->e($partner['tax_id'] ?? $this->old('tax_id')) ?>"
                           placeholder="Tax identification number">
                    <?php if ($this->hasError('tax_id')): ?>
                    <span class="error"><?= $this->error('tax_id') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person"
                           value="<?= $this->e($partner['contact_person'] ?? $this->old('contact_person')) ?>"
                           placeholder="Primary contact name">
                    <?php if ($this->hasError('contact_person')): ?>
                    <span class="error"><?= $this->error('contact_person') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= $this->e($partner['email'] ?? $this->old('email')) ?>"
                           placeholder="email@example.com">
                    <?php if ($this->hasError('email')): ?>
                    <span class="error"><?= $this->error('email') ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= $this->e($partner['phone'] ?? $this->old('phone')) ?>"
                           placeholder="+1 234 567 8900">
                    <?php if ($this->hasError('phone')): ?>
                    <span class="error"><?= $this->error('phone') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"
                          placeholder="Full address"><?= $this->e($partner['address'] ?? $this->old('address')) ?></textarea>
                <?php if ($this->hasError('address')): ?>
                <span class="error"><?= $this->error('address') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"
                          placeholder="Additional notes"><?= $this->e($partner['notes'] ?? $this->old('notes')) ?></textarea>
                <?php if ($this->hasError('notes')): ?>
                <span class="error"><?= $this->error('notes') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($partner['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $partner ? 'Update Partner' : 'Create Partner' ?></button>
                <a href="/warehouse/partners" class="btn btn-secondary">Cancel</a>
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
