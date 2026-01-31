<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <a href="/costing" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to', ['name' => $this->__('costing')]) ?>
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="get" action="/costing/plan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" value="<?= h($search) ?>"
                               placeholder="<?= $this->__('search_sku_name') ?>"
                               class="form-control">
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search): ?>
                    <a href="/costing/plan" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Planned Costs Table -->
<div class="card">
    <div class="card-header align-items-center d-flex">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-file-list-3-line me-2"></i><?= $this->__('planned_costs_bom_routing') ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($variants)): ?>
        <div class="text-center text-muted py-5">
            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
            <?= $this->__('no_variants_found') ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('bom') ?></th>
                        <th><?= $this->__('routing') ?></th>
                        <th class="text-end"><?= $this->__('material_cost') ?></th>
                        <th class="text-end"><?= $this->__('labor_cost') ?></th>
                        <th class="text-end"><?= $this->__('overhead_cost') ?></th>
                        <th class="text-end"><?= $this->__('total_cost') ?></th>
                        <th><?= $this->__('calculated') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $v): ?>
                    <tr>
                        <td>
                            <a href="/costing/variant/<?= $v['id'] ?>" class="fw-medium text-primary">
                                <?= h($v['sku']) ?>
                            </a>
                        </td>
                        <td><?= h($v['name']) ?></td>
                        <td>
                            <?php if ($v['bom_id']): ?>
                            <a href="/catalog/bom/<?= $v['bom_id'] ?>" class="text-decoration-underline">
                                <?= h($v['bom_name']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['routing_id']): ?>
                            <a href="/catalog/routing/<?= $v['routing_id'] ?>" class="text-decoration-underline">
                                <?= h($v['routing_name']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($v['material_cost'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($v['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-end"><?= number_format($v['overhead_cost'] ?? 0, 2) ?></td>
                        <td class="text-end">
                            <?php if ($v['planned_cost']): ?>
                            <span class="fw-semibold"><?= number_format($v['planned_cost'], 2) ?></span>
                            <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="ri-alert-line me-1"></i><?= $this->__('not_calculated') ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['calculated_at']): ?>
                            <span class="text-muted">
                                <i class="ri-calendar-line me-1"></i>
                                <?= date('d.m.Y', strtotime($v['calculated_at'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                            <i class="ri-arrow-right-s-line"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
