<?php $this->section('content'); ?>

<div class="page-header">
    <h2><?= h($title) ?></h2>
    <?php if ($this->can('admin.roles.create')): ?>
    <a href="/admin/roles/create" class="btn btn-primary"><?= $this->__('create_role') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($roles)): ?>
        <p class="text-muted"><?= $this->__('no_results') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->__('name') ?></th>
                    <th><?= $this->__('description') ?></th>
                    <th><?= $this->__('users') ?></th>
                    <th><?= $this->__('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><strong><?= h($role['name'] ?? '') ?></strong></td>
                    <td><?= h($role['description'] ?? '-') ?></td>
                    <td><?= (int)($role['user_count'] ?? 0) ?></td>
                    <td>
                        <?php if ($this->can('admin.roles.edit')): ?>
                        <a href="/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
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
