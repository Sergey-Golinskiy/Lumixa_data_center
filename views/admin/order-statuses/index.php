<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('order_statuses') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('admin.order_statuses.create')): ?>
        <a href="/admin/order-statuses/create" class="btn btn-primary">+ <?= $this->__('create_order_status') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
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
                    <?php if (empty($statuses)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($statuses as $status): ?>
                    <tr>
                        <td class="text-center text-muted"><?= $this->e($status['sort_order']) ?></td>
                        <td><code><?= $this->e($status['code']) ?></code></td>
                        <td>
                            <span class="status-badge" style="background-color: <?= $this->e($status['color']) ?>;">
                                <?= $this->e($status['name']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="color-preview" style="background-color: <?= $this->e($status['color']) ?>;"></span>
                            <?= $this->e($status['color']) ?>
                        </td>
                        <td>
                            <?php if ($status['is_default']): ?>
                            <span class="badge badge-primary"><?= $this->__('default') ?></span>
                            <?php endif; ?>
                            <?php if ($status['is_final']): ?>
                            <span class="badge badge-secondary"><?= $this->__('final') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status['is_active']): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->can('admin.order_statuses.edit')): ?>
                            <a href="/admin/order-statuses/<?= $status['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                            <?php if ($this->can('admin.order_statuses.delete') && !$status['is_default']): ?>
                            <form method="POST" action="/admin/order-statuses/<?= $status['id'] ?>/delete" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= $this->__('confirm_delete_order_status') ?>')">
                                    <?= $this->__('delete') ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    color: white;
    font-size: 13px;
    font-weight: 500;
    text-shadow: 0 1px 1px rgba(0,0,0,0.2);
}

.color-preview {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 4px;
    vertical-align: middle;
    margin-right: 6px;
    border: 1px solid rgba(0,0,0,0.1);
}

code {
    background: var(--bg);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}
</style>

<?php $this->endSection(); ?>
