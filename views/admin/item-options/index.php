<?php $this->section('content'); ?>

<?php $colCount = 3 + ($showFilament ? 1 : 0) + (!empty($showColor) ? 1 : 0); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->e($groupLabel) ?></h5>
                <?php if ($this->can('admin.item_options.create')): ?>
                <a href="/admin/item-options/<?= $this->e($group) ?>/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_item_option') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($options)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-list-settings-line"></i>
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
                                <?php if (!empty($showColor)): ?>
                                <th><?= $this->__('alias_color') ?></th>
                                <?php endif; ?>
                                <?php if ($showFilament): ?>
                                <th><?= $this->__('filament') ?></th>
                                <?php endif; ?>
                                <th><?= $this->__('status') ?></th>
                                <th><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($options as $option): ?>
                            <tr>
                                <td><span class="fw-medium"><?= $this->e($option['name']) ?></span></td>
                                <?php if (!empty($showColor)): ?>
                                <td>
                                    <?php if (!empty($option['color'])): ?>
                                    <span class="badge rounded-pill" style="background: <?= $this->e($option['color']) ?>; color: <?= $this->contrastColor($option['color']) ?>;">
                                        <?= $this->e($option['name']) ?>
                                    </span>
                                    <small class="text-muted ms-2"><?= $this->e($option['color']) ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <?php if ($showFilament): ?>
                                <td>
                                    <?php if ($option['is_filament']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('yes') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('no') ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($option['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($this->can('admin.item_options.edit')): ?>
                                        <a href="/admin/item-options/<?= $this->e($group) ?>/<?= $option['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->can('admin.item_options.delete')): ?>
                                        <form method="POST" action="/admin/item-options/<?= $this->e($group) ?>/<?= $option['id'] ?>/delete" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                            <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('<?= $this->__('confirm_delete_item_option') ?>')">
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
