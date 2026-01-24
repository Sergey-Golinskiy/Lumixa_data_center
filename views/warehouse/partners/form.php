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
                <label for="category_id"><?= $this->__('business_category') ?></label>
                <select id="category_id" name="category_id">
                    <option value=""><?= $this->__('select_category') ?></option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($partner['category_id'] ?? $this->old('category_id')) == $cat['id'] ? 'selected' : '' ?>>
                        <?= $this->e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($this->hasError('category_id')): ?>
                <span class="error"><?= $this->error('category_id') ?></span>
                <?php endif; ?>
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

            <!-- Additional Contacts -->
            <div class="form-section">
                <h3><?= $this->__('additional_contacts') ?></h3>
                <div id="contacts-container">
                    <?php if (!empty($contacts)): ?>
                        <?php foreach ($contacts as $index => $contact): ?>
                        <div class="contact-item" data-index="<?= $index ?>">
                            <div class="contact-header">
                                <span class="contact-number"><?= $this->__('contact') ?> #<?= $index + 1 ?></span>
                                <button type="button" class="btn-remove-contact" onclick="removeContact(this)">
                                    <i class="fas fa-times"></i> <?= $this->__('remove') ?>
                                </button>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?= $this->__('contact_name') ?></label>
                                    <input type="text" name="contacts[<?= $index ?>][name]" value="<?= $this->e($contact['name']) ?>">
                                </div>
                                <div class="form-group">
                                    <label><?= $this->__('position') ?></label>
                                    <input type="text" name="contacts[<?= $index ?>][position]" value="<?= $this->e($contact['position']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?= $this->__('phone') ?></label>
                                    <input type="text" name="contacts[<?= $index ?>][phone]" value="<?= $this->e($contact['phone']) ?>">
                                </div>
                                <div class="form-group">
                                    <label><?= $this->__('email') ?></label>
                                    <input type="email" name="contacts[<?= $index ?>][email]" value="<?= $this->e($contact['email']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?= $this->__('website') ?></label>
                                    <input type="text" name="contacts[<?= $index ?>][website]" value="<?= $this->e($contact['website']) ?>" placeholder="https://example.com">
                                </div>
                                <div class="form-group">
                                    <label><?= $this->__('social_media') ?></label>
                                    <input type="text" name="contacts[<?= $index ?>][social_media]" value="<?= $this->e($contact['social_media']) ?>" placeholder="@username">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="contacts[<?= $index ?>][is_primary]" value="1" <?= $contact['is_primary'] ? 'checked' : '' ?>>
                                    <?= $this->__('primary') ?>
                                </label>
                            </div>
                            <input type="hidden" name="contacts[<?= $index ?>][id]" value="<?= $contact['id'] ?? '' ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addContact()">
                    <i class="fas fa-plus"></i> <?= $this->__('add_contact') ?>
                </button>
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

<script>
let contactIndex = <?= !empty($contacts) ? count($contacts) : 0 ?>;

function addContact() {
    const container = document.getElementById('contacts-container');
    const contactHtml = `
        <div class="contact-item" data-index="${contactIndex}">
            <div class="contact-header">
                <span class="contact-number"><?= $this->__('contact') ?> #${contactIndex + 1}</span>
                <button type="button" class="btn-remove-contact" onclick="removeContact(this)">
                    <i class="fas fa-times"></i> <?= $this->__('remove') ?>
                </button>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= $this->__('contact_name') ?></label>
                    <input type="text" name="contacts[${contactIndex}][name]">
                </div>
                <div class="form-group">
                    <label><?= $this->__('position') ?></label>
                    <input type="text" name="contacts[${contactIndex}][position]">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= $this->__('phone') ?></label>
                    <input type="text" name="contacts[${contactIndex}][phone]">
                </div>
                <div class="form-group">
                    <label><?= $this->__('email') ?></label>
                    <input type="email" name="contacts[${contactIndex}][email]">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><?= $this->__('website') ?></label>
                    <input type="text" name="contacts[${contactIndex}][website]" placeholder="https://example.com">
                </div>
                <div class="form-group">
                    <label><?= $this->__('social_media') ?></label>
                    <input type="text" name="contacts[${contactIndex}][social_media]" placeholder="@username">
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contacts[${contactIndex}][is_primary]" value="1">
                    <?= $this->__('primary') ?>
                </label>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', contactHtml);
    contactIndex++;
    updateContactNumbers();
}

function removeContact(button) {
    if (confirm('<?= $this->__('confirm_delete') ?>')) {
        button.closest('.contact-item').remove();
        updateContactNumbers();
    }
}

function updateContactNumbers() {
    const items = document.querySelectorAll('.contact-item');
    items.forEach((item, index) => {
        item.querySelector('.contact-number').textContent = `<?= $this->__('contact') ?> #${index + 1}`;
    });
}
</script>

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
.form-section {
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}
.form-section h3 {
    margin: 0 0 20px 0;
    font-size: 1.2rem;
    color: #333;
}
.contact-item {
    background: white;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}
.contact-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}
.contact-number {
    font-weight: 600;
    color: #495057;
}
.btn-remove-contact {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
    transition: background 0.2s;
}
.btn-remove-contact:hover {
    background: #c82333;
}
.btn-remove-contact i {
    margin-right: 4px;
}
</style>

<?php $this->endSection(); ?>
