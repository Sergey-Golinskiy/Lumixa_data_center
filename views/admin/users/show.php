<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/users" class="btn btn-secondary">&laquo; Back to Users</a>
    <?php if ($this->can('admin.users.edit')): ?>
    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-primary">Edit</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($user['username']) ?></h3>
    </div>
    <div class="card-body">
        <dl class="details-list">
            <dt>Email</dt>
            <dd><?= h($user['email']) ?></dd>

            <dt>Full Name</dt>
            <dd><?= h($user['full_name'] ?: '-') ?></dd>

            <dt>Roles</dt>
            <dd><?= h($user['role_names'] ?: 'No roles assigned') ?></dd>

            <dt>Status</dt>
            <dd>
                <?php if ($user['is_active']): ?>
                <span class="badge badge-success">Active</span>
                <?php else: ?>
                <span class="badge badge-secondary">Inactive</span>
                <?php endif; ?>
            </dd>

            <dt>Created</dt>
            <dd><?= h($user['created_at']) ?></dd>

            <dt>Last Updated</dt>
            <dd><?= h($user['updated_at'] ?: '-') ?></dd>
        </dl>
    </div>
</div>

<?php $this->endSection(); ?>
