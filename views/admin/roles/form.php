<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= h($title) ?></h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= $role ? '/admin/roles/' . $role['id'] . '/edit' : '/admin/roles/create' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= h($csrfToken ?? '') ?>">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><?= $this->__('role_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="<?= h($role['name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="description" class="form-label"><?= $this->__('description') ?></label>
                                <textarea name="description" id="description" class="form-control" rows="2"><?= h($role['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('permissions') ?></label>
                        <div class="row g-4">
                            <?php foreach ($permissions ?? [] as $group => $perms): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0 text-capitalize"><?= h($group) ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($perms as $key => $label): ?>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="permissions[]" value="<?= h($key) ?>"
                                                   id="perm_<?= h($key) ?>"
                                                   class="form-check-input"
                                                   <?= in_array($key, $rolePermissions ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="perm_<?= h($key) ?>">
                                                <?= h($label) ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $role ? $this->__('update') : $this->__('create') ?>
                        </button>
                        <a href="/admin/roles" class="btn btn-soft-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            <?= $this->__('cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
