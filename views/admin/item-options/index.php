<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->e($groupLabel) ?></h1>
    <div class="page-actions">
        <?php if ($this->can('admin.item_options.create')): ?>
        <a href="/admin/item-options/<?= $this->e($group) ?>/create" class="btn btn-primary">+ <?= $this->__('create_item_option') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('name') ?></th>
                        <?php if ($showFilament): ?>
                        <th><?= $this->__('filament') ?></th>
                        <?php endif; ?>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($options)): ?>
                    <tr>
                        <td colspan="<?= $showFilament ? 4 : 3 ?>" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($options as $option): ?>
                    <tr>
                        <td><strong><?= $this->e($option['name']) ?></strong></td>
                        <?php if ($showFilament): ?>
                        <td>
                            <?= $option['is_filament'] ? $this->__('yes') : $this->__('no') ?>
                        </td>
                        <?php endif; ?>
                        <td>
                            <?php if ($option['is_active']): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->can('admin.item_options.edit')): ?>
                            <a href="/admin/item-options/<?= $this->e($group) ?>/<?= $option['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                            <?php if ($this->can('admin.item_options.delete')): ?>
                            <form method="POST" action="/admin/item-options/<?= $this->e($group) ?>/<?= $option['id'] ?>/delete" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= $this->__('confirm_delete_item_option') ?>')">
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

<?php $this->endSection(); ?>
