<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/roles" class="btn btn-secondary">&laquo; Back to Roles</a>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($title) ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $role ? '/admin/roles/' . $role['id'] . '/edit' : '/admin/roles/create' ?>">
            <input type="hidden" name="_token" value="<?= h($csrfToken) ?>">

            <div class="form-group">
                <label for="name">Role Name <span class="required">*</span></label>
                <input type="text" name="name" id="name" class="form-control"
                       value="<?= h($role['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="2"><?= h($role['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Permissions</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($permissions as $group => $perms): ?>
                    <div class="permission-group">
                        <h5 style="text-transform: capitalize; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><?= h($group) ?></h5>
                        <?php foreach ($perms as $key => $label): ?>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="permissions[]" value="<?= h($key) ?>"
                                       <?= in_array($key, $rolePermissions) ? 'checked' : '' ?>>
                                <?= h($label) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary"><?= $role ? 'Update' : 'Create' ?> Role</button>
                <a href="/admin/roles" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php $this->endSection(); ?>
