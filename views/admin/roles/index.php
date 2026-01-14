<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
    <?php if ($this->can('admin.roles.create')): ?>
    <a href="/admin/roles/create" class="btn btn-primary">Create Role</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($roles)): ?>
        <p class="text-muted">No roles defined.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><strong><?= h($role['name']) ?></strong></td>
                    <td><?= h($role['description'] ?? '-') ?></td>
                    <td><?= (int)$role['user_count'] ?></td>
                    <td>
                        <?php if ($this->can('admin.roles.edit')): ?>
                        <a href="/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
