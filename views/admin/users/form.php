<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $this->e($title) ?></h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= $user ? '/admin/users/' . $user['id'] . '/edit' : '/admin/users/create' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label"><?= $this->__('name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="<?= $this->e($user['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= $this->__('email') ?> <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="<?= $this->e($user['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <?= $this->__('password') ?>
                            <?= $user ? '<small class="text-muted">(' . $this->__('password_leave_blank') . ')</small>' : '<span class="text-danger">*</span>' ?>
                        </label>
                        <input type="password" name="password" id="password" class="form-control"
                               <?= $user ? '' : 'required' ?>>
                    </div>

                    <div class="mb-3">
                        <label for="locale" class="form-label"><?= $this->__('language') ?></label>
                        <select id="locale" name="locale" class="form-select">
                            <?php foreach ($localeNames ?? \App\Core\Translator::LOCALE_NAMES as $code => $name): ?>
                            <option value="<?= $code ?>" <?= ($user['locale'] ?? 'en') === $code ? 'selected' : '' ?>>
                                <?= $this->e($name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('roles') ?></label>
                        <div class="row g-2">
                            <?php foreach ($roles as $role): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                                           id="role_<?= $role['id'] ?>"
                                           class="form-check-input"
                                           <?= in_array($role['id'], $userRoleIds ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                        <?= $this->e($role['name']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                   class="form-check-input"
                                   <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active"><?= $this->__('active') ?></label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i>
                            <?= $user ? $this->__('update_user') : $this->__('create_user') ?>
                        </button>
                        <a href="/admin/users" class="btn btn-soft-secondary">
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
                <a href="/admin/users" class="btn btn-soft-primary w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    <?= $this->__('back_to_users') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
