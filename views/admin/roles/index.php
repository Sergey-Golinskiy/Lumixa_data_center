<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= h($title) ?></h5>
                <?php if ($this->can('admin.roles.create')): ?>
                <a href="/admin/roles/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_role') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($roles)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-shield-user-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_results') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
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
                                <td><span class="fw-medium"><?= h($role['name'] ?? '') ?></span></td>
                                <td class="text-muted"><?= h($role['description'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info"><?= (int)($role['user_count'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <?php if ($this->can('admin.roles.edit')): ?>
                                    <a href="/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                        <i class="ri-edit-line"></i>
                                        <?= $this->__('edit') ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
