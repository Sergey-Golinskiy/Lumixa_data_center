<?php $this->section('content'); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= h($user['name'] ?? '') ?></h5>
                <?php if ($this->can('admin.users.edit')): ?>
                <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-soft-primary">
                    <i class="ri-edit-line me-1"></i>
                    <?= $this->__('edit') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 200px;"><?= $this->__('email') ?></th>
                                <td class="text-muted"><?= h($user['email'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('roles') ?></th>
                                <td class="text-muted"><?= h($user['role_names'] ?? $this->__('none')) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('status') ?></th>
                                <td>
                                    <?php if ($user['is_active'] ?? false): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('created_at') ?></th>
                                <td class="text-muted"><?= h($user['created_at'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0"><?= $this->__('updated_at') ?></th>
                                <td class="text-muted"><?= h($user['updated_at'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
