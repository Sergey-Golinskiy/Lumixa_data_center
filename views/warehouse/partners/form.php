<?php $this->section('content'); ?>

<!-- Back button -->
<div class="mb-3">
    <a href="/warehouse/partners" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('suppliers')]) ?>
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-user-add-line me-2"></i>
                    <?= $partner ? $this->__('edit_supplier') : $this->__('create_new_supplier') ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $partner ? "/warehouse/partners/{$partner['id']}" : '/warehouse/partners' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label"><?= $this->__('code') ?></label>
                            <input type="text" id="code" name="code" class="form-control"
                                   value="<?= $this->e($partner['code'] ?? $this->old('code')) ?>"
                                   placeholder="<?= $this->__('auto_generated_if_empty') ?>">
                            <?php if ($this->hasError('code')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('code') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label"><?= $this->__('type') ?> <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="supplier" <?= ($partner['type'] ?? $this->old('type')) === 'supplier' ? 'selected' : '' ?>><?= $this->__('supplier') ?></option>
                                <option value="customer" <?= ($partner['type'] ?? $this->old('type')) === 'customer' ? 'selected' : '' ?>><?= $this->__('customer') ?></option>
                                <option value="both" <?= ($partner['type'] ?? $this->old('type')) === 'both' ? 'selected' : '' ?>><?= $this->__('both') ?></option>
                            </select>
                            <?php if ($this->hasError('type')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('type') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label"><?= $this->__('business_category') ?></label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value=""><?= $this->__('select_category') ?></option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($partner['category_id'] ?? $this->old('category_id')) == $cat['id'] ? 'selected' : '' ?>>
                                <?= $this->e($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($this->hasError('category_id')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('category_id') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="<?= $this->e($partner['name'] ?? $this->old('name')) ?>"
                               placeholder="<?= $this->__('company_or_individual_name') ?>">
                        <?php if ($this->hasError('name')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('name') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tax_id" class="form-label"><?= $this->__('tax_id') ?></label>
                            <input type="text" id="tax_id" name="tax_id" class="form-control"
                                   value="<?= $this->e($partner['tax_id'] ?? $this->old('tax_id')) ?>"
                                   placeholder="<?= $this->__('tax_identification_number') ?>">
                            <?php if ($this->hasError('tax_id')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('tax_id') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label"><?= $this->__('contact_person') ?></label>
                            <input type="text" id="contact_person" name="contact_person" class="form-control"
                                   value="<?= $this->e($partner['contact_person'] ?? $this->old('contact_person')) ?>"
                                   placeholder="<?= $this->__('primary_contact_name') ?>">
                            <?php if ($this->hasError('contact_person')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('contact_person') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><?= $this->__('email') ?></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= $this->e($partner['email'] ?? $this->old('email')) ?>"
                                   placeholder="email@example.com">
                            <?php if ($this->hasError('email')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('email') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label"><?= $this->__('phone') ?></label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                   value="<?= $this->e($partner['phone'] ?? $this->old('phone')) ?>"
                                   placeholder="+1 234 567 8900">
                            <?php if ($this->hasError('phone')): ?>
                            <div class="invalid-feedback d-block"><?= $this->error('phone') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label"><?= $this->__('address') ?></label>
                        <textarea id="address" name="address" class="form-control" rows="3"
                                  placeholder="<?= $this->__('full_address') ?>"><?= $this->e($partner['address'] ?? $this->old('address')) ?></textarea>
                        <?php if ($this->hasError('address')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('address') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label"><?= $this->__('notes') ?></label>
                        <textarea id="notes" name="notes" class="form-control" rows="2"
                                  placeholder="<?= $this->__('additional_notes') ?>"><?= $this->e($partner['notes'] ?? $this->old('notes')) ?></textarea>
                        <?php if ($this->hasError('notes')): ?>
                        <div class="invalid-feedback d-block"><?= $this->error('notes') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Additional Contacts -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0"><i class="ri-contacts-book-line me-2"></i><?= $this->__('additional_contacts') ?></h6>
                        </div>
                        <div class="card-body">
                            <div id="contacts-container">
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $index => $contact): ?>
                                    <div class="contact-item card mb-3" data-index="<?= $index ?>">
                                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                                            <span class="contact-number fw-medium"><?= $this->__('contact') ?> #<?= $index + 1 ?></span>
                                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="removeContact(this)">
                                                <i class="ri-close-line me-1"></i> <?= $this->__('remove') ?>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('contact_name') ?></label>
                                                    <input type="text" name="contacts[<?= $index ?>][name]" class="form-control" value="<?= $this->e($contact['name']) ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('position') ?></label>
                                                    <input type="text" name="contacts[<?= $index ?>][position]" class="form-control" value="<?= $this->e($contact['position']) ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('phone') ?></label>
                                                    <input type="text" name="contacts[<?= $index ?>][phone]" class="form-control" value="<?= $this->e($contact['phone']) ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('email') ?></label>
                                                    <input type="email" name="contacts[<?= $index ?>][email]" class="form-control" value="<?= $this->e($contact['email']) ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('website') ?></label>
                                                    <input type="text" name="contacts[<?= $index ?>][website]" class="form-control" value="<?= $this->e($contact['website']) ?>" placeholder="https://example.com">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label"><?= $this->__('social_media') ?></label>
                                                    <input type="text" name="contacts[<?= $index ?>][social_media]" class="form-control" value="<?= $this->e($contact['social_media']) ?>" placeholder="@username">
                                                </div>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="contacts[<?= $index ?>][is_primary]" value="1" <?= $contact['is_primary'] ? 'checked' : '' ?>>
                                                <label class="form-check-label"><?= $this->__('primary') ?></label>
                                            </div>
                                            <input type="hidden" name="contacts[<?= $index ?>][id]" value="<?= $contact['id'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-soft-primary" onclick="addContact()">
                                <i class="ri-add-line me-1"></i> <?= $this->__('add_contact') ?>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   <?= ($partner['is_active'] ?? $this->old('is_active', 1)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $partner ? $this->__('update_supplier') : $this->__('create_supplier') ?>
                        </button>
                        <a href="/warehouse/partners" class="btn btn-soft-secondary"><?= $this->__('cancel') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let contactIndex = <?= !empty($contacts) ? count($contacts) : 0 ?>;

function addContact() {
    const container = document.getElementById('contacts-container');
    const contactHtml = `
        <div class="contact-item card mb-3" data-index="${contactIndex}">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span class="contact-number fw-medium"><?= $this->__('contact') ?> #${contactIndex + 1}</span>
                <button type="button" class="btn btn-sm btn-soft-danger" onclick="removeContact(this)">
                    <i class="ri-close-line me-1"></i> <?= $this->__('remove') ?>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('contact_name') ?></label>
                        <input type="text" name="contacts[${contactIndex}][name]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('position') ?></label>
                        <input type="text" name="contacts[${contactIndex}][position]" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('phone') ?></label>
                        <input type="text" name="contacts[${contactIndex}][phone]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('email') ?></label>
                        <input type="email" name="contacts[${contactIndex}][email]" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('website') ?></label>
                        <input type="text" name="contacts[${contactIndex}][website]" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $this->__('social_media') ?></label>
                        <input type="text" name="contacts[${contactIndex}][social_media]" class="form-control" placeholder="@username">
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="contacts[${contactIndex}][is_primary]" value="1">
                    <label class="form-check-label"><?= $this->__('primary') ?></label>
                </div>
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

<?php $this->endSection(); ?>
