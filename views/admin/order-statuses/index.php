<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('order_statuses') ?></h5>
                <?php if ($this->can('admin.order_statuses.create')): ?>
                <a href="/admin/order-statuses/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_order_status') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($statuses)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-list-check-2"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_results') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;"><?= $this->__('order') ?></th>
                                <th><?= $this->__('code') ?></th>
                                <th><?= $this->__('name') ?></th>
                                <th><?= $this->__('color') ?></th>
                                <th><?= $this->__('flags') ?></th>
                                <th><?= $this->__('status') ?></th>
                                <th><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statuses as $status): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $this->e($status['sort_order']) ?></td>
                                <td><code><?= $this->e($status['code']) ?></code></td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2" style="background-color: <?= $this->e($status['color']) ?>; color: #fff; text-shadow: 0 1px 1px rgba(0,0,0,0.2);">
                                        <?= $this->e($status['name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="d-inline-block rounded" style="width: 16px; height: 16px; background-color: <?= $this->e($status['color']) ?>; vertical-align: middle;"></span>
                                    <span class="ms-1 text-muted"><?= $this->e($status['color']) ?></span>
                                </td>
                                <td>
                                    <?php if ($status['is_default']): ?>
                                    <span class="badge bg-primary-subtle text-primary me-1"><?= $this->__('default') ?></span>
                                    <?php endif; ?>
                                    <?php if ($status['is_final']): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('final') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($this->can('admin.order_statuses.edit')): ?>
                                        <a href="/admin/order-statuses/<?= $status['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->can('admin.order_statuses.delete') && !$status['is_default']): ?>
                                        <form method="POST" action="/admin/order-statuses/<?= $status['id'] ?>/delete" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                            <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('<?= $this->__('confirm_delete_order_status') ?>')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
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
