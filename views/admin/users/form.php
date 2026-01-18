<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/users" class="btn btn-secondary">&laquo; <?= $this->__('back_to_users') ?></a>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= $this->e($title) ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $user ? '/admin/users/' . $user['id'] . '/edit' : '/admin/users/create' ?>">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">

            <div class="form-group">
                <label for="name"><?= $this->__('name') ?> <span class="required">*</span></label>
                <input type="text" name="name" id="name" class="form-control"
                       value="<?= $this->e($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email"><?= $this->__('email') ?> <span class="required">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= $this->e($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><?= $this->__('password') ?> <?= $user ? $this->__('password_leave_blank') : '<span class="required">*</span>' ?></label>
                <input type="password" name="password" id="password" class="form-control"
                       <?= $user ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label for="locale"><?= $this->__('language') ?></label>
                <select id="locale" name="locale" class="form-control">
                    <?php foreach ($localeNames ?? \App\Core\Translator::LOCALE_NAMES as $code => $name): ?>
                    <option value="<?= $code ?>" <?= ($user['locale'] ?? 'en') === $code ? 'selected' : '' ?>>
                        <?= $this->e($name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><?= $this->__('roles') ?></label>
                <?php foreach ($roles as $role): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                               <?= in_array($role['id'], $userRoleIds ?? []) ? 'checked' : '' ?>>
                        <?= $this->e($role['name']) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <?= $this->__('active') ?>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $user ? $this->__('update_user') : $this->__('create_user') ?></button>
                <a href="/admin/users" class="btn btn-secondary"><?= $this->__('cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
