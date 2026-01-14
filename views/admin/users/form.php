<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/users" class="btn btn-secondary">&laquo; Back to Users</a>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($title) ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $user ? '/admin/users/' . $user['id'] . '/edit' : '/admin/users/create' ?>">
            <input type="hidden" name="_token" value="<?= h($csrfToken) ?>">

            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" name="username" id="username" class="form-control"
                       value="<?= h($user['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= h($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control"
                       value="<?= h($user['full_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password <?= $user ? '(leave blank to keep current)' : '<span class="required">*</span>' ?></label>
                <input type="password" name="password" id="password" class="form-control"
                       <?= $user ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label>Roles</label>
                <?php foreach ($roles as $role): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                               <?= in_array($role['id'], $userRoleIds ?? []) ? 'checked' : '' ?>>
                        <?= h($role['name']) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                           <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $user ? 'Update' : 'Create' ?> User</button>
                <a href="/admin/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
