<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">
        <i class="ri-route-line me-2"></i>
        <?= $this->__('detail_routing') ?>: <?= $this->e($routing['detail_sku']) ?>
    </h4>
    <div class="d-flex gap-2">
        <a href="/catalog/detail-routing" class="btn btn-soft-secondary">
            <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('detail_routing')]) ?>
        </a>
        <a href="/catalog/details/<?= $routing['detail_id'] ?>" class="btn btn-soft-info">
            <i class="ri-shape-2-line me-1"></i> <?= $this->__('view_detail') ?>
        </a>
        <?php if ($routing['status'] === 'draft' && $this->can('catalog.detail_routing.edit')): ?>
        <a href="/catalog/detail-routing/<?= $routing['id'] ?>/edit" class="btn btn-soft-primary">
            <i class="ri-pencil-line me-1"></i> <?= $this->__('edit') ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Routing Information -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="ri-information-line me-2"></i>
                <?= $this->__('detail_routing_information') ?>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width: 140px;"><?= $this->__('detail') ?></th>
                            <td>
                                <a href="/catalog/details/<?= $routing['detail_id'] ?>" class="text-decoration-none">
                                    <strong class="text-primary"><?= $this->e($routing['detail_sku']) ?></strong>
                                </a>
                                <br><small class="text-muted"><?= $this->e($routing['detail_name']) ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('version') ?></th>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= $this->e($routing['version']) ?></span></td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('name') ?></th>
                            <td><?= $this->e($routing['name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('photo') ?></th>
                            <td>
                                <?php if (!empty($routing['image_path'])): ?>
                                <img src="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>"
                                     class="img-thumbnail" style="max-height: 100px; cursor: pointer;"
                                     data-image-preview="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>">
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('status') ?></th>
                            <td>
                                <?php
                                $statusClass = match($routing['status']) {
                                    'draft' => 'warning',
                                    'active' => 'success',
                                    'archived' => 'secondary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= $this->__('detail_routing_status_' . $routing['status']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('effective_date') ?></th>
                            <td><?= $routing['effective_date'] ? $this->date($routing['effective_date'], 'Y-m-d') : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted"><?= $this->__('created_by') ?></th>
                            <td><?= $this->e($routing['created_by_name'] ?? '-') ?></td>
                        </tr>
                        <?php if ($routing['notes']): ?>
                        <tr>
                            <th class="text-muted"><?= $this->__('notes') ?></th>
                            <td><?= nl2br($this->e($routing['notes'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Actions -->
    <div class="col-lg-6 mb-4">
        <?php if ($routing['status'] === 'draft'): ?>
        <div class="card border-warning h-100">
            <div class="card-header bg-warning-subtle text-warning">
                <i class="ri-draft-line me-2"></i>
                <?= $this->__('draft_status') ?>
            </div>
            <div class="card-body">
                <p class="mb-3"><?= $this->__('routing_in_draft') ?></p>
                <?php if ($this->can('catalog.detail_routing.activate')): ?>
                <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/activate">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('activate_routing_confirm') ?>')">
                        <i class="ri-check-line me-1"></i> <?= $this->__('activate') ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($routing['status'] === 'active'): ?>
        <div class="card border-success h-100">
            <div class="card-header bg-success-subtle text-success">
                <i class="ri-check-double-line me-2"></i>
                <?= $this->__('active_status') ?>
            </div>
            <div class="card-body">
                <p class="mb-3"><?= $this->__('routing_active') ?></p>
                <?php if ($this->can('catalog.detail_routing.edit')): ?>
                <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/archive">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('<?= $this->__('archive_routing_confirm') ?>')">
                        <i class="ri-archive-line me-1"></i> <?= $this->__('archive') ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-secondary h-100">
            <div class="card-header bg-secondary-subtle text-secondary">
                <i class="ri-archive-line me-2"></i>
                <?= $this->__('archived_status') ?>
            </div>
            <div class="card-body">
                <p class="mb-3"><?= $this->__('routing_archived') ?></p>
                <?php if ($this->can('catalog.detail_routing.activate')): ?>
                <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/activate">
                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                    <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('reactivate_routing_confirm') ?>')">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('reactivate') ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Operations Card -->
<div class="card">
    <div class="card-header">
        <i class="ri-list-ordered me-2"></i>
        <?= $this->__('operations') ?>
        <span class="badge bg-secondary-subtle text-secondary ms-2"><?= count($operations) ?></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-end"><?= $this->__('setup_time_minutes') ?></th>
                        <th class="text-end"><?= $this->__('run_time_minutes') ?></th>
                        <th class="text-end"><?= $this->__('labor_cost') ?></th>
                        <th class="text-end"><?= $this->__('overhead_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($operations)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="ri-list-check fs-1 text-muted"></i>
                            <p class="text-muted mb-0 mt-2"><?= $this->__('no_operations') ?></p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($operations as $op): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary-subtle text-primary me-2"><?= $this->e($op['operation_number']) ?></span>
                            <strong><?= $this->e($op['name']) ?></strong>
                            <?php if (!empty($op['instructions'])): ?>
                            <br><small class="text-muted"><?= $this->e($op['instructions']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($op['work_center'])): ?>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $this->e($op['work_center']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= $this->e($op['setup_time_minutes']) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-end"><?= $this->e($op['run_time_minutes']) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-end"><?= number_format($op['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($op['overhead_cost'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($operations)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2" class="text-end"><strong><?= $this->__('total') ?>:</strong></td>
                        <td class="text-end"><strong><?= array_sum(array_column($operations, 'setup_time_minutes')) ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td class="text-end"><strong><?= array_sum(array_column($operations, 'run_time_minutes')) ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td class="text-end"><strong><?= number_format(array_sum(array_column($operations, 'labor_cost')), 2) ?></strong></td>
                        <td class="text-end"><strong><?= number_format(array_sum(array_column($operations, 'overhead_cost')), 2) ?></strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
