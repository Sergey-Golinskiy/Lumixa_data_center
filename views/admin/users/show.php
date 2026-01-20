<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/admin/users" class="btn btn-secondary">&laquo; <?= $this->__('back_to_users') ?></a>
    <?php if ($this->can('admin.users.edit')): ?>
    <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-primary"><?= $this->__('edit') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3><?= h($user['name'] ?? '') ?></h3>
    </div>
    <div class="card-body">
        <dl class="details-list">
            <dt><?= $this->__('email') ?></dt>
            <dd><?= h($user['email'] ?? '') ?></dd>

            <dt><?= $this->__('roles') ?></dt>
            <dd><?= h($user['role_names'] ?? $this->__('none')) ?></dd>

            <dt><?= $this->__('status') ?></dt>
            <dd>
                <?php if ($user['is_active'] ?? false): ?>
                <span class="badge badge-success"><?= $this->__('active') ?></span>
                <?php else: ?>
                <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                <?php endif; ?>
            </dd>

            <dt><?= $this->__('created_at') ?></dt>
            <dd><?= h($user['created_at'] ?? '-') ?></dd>

            <dt><?= $this->__('updated_at') ?></dt>
            <dd><?= h($user['updated_at'] ?? '-') ?></dd>
        </dl>
    </div>
</div>

<?php $this->endSection(); ?>
