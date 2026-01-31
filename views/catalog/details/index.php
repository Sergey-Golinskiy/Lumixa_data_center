<?php $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?= $this->__('details') ?></h4>
            <?php if ($this->can('catalog.details.create')): ?>
            <a href="/catalog/details/create" class="btn btn-success">
                <i class="ri-add-line me-1"></i><?= $this->__('new_detail') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Live Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/catalog/details">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('search_sku_name') ?>"
                               value="<?= $this->e($search) ?>">
                        <?php if ($search): ?>
                        <button type="button" class="btn btn-outline-secondary live-filter-clear-search">
                            <i class="ri-close-line"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('detail_type') ?></label>
                    <select name="detail_type" class="form-select">
                        <option value=""><?= $this->__('all_detail_types') ?></option>
                        <option value="purchased" <?= $detailType === 'purchased' ? 'selected' : '' ?>><?= $this->__('detail_type_purchased') ?></option>
                        <option value="printed" <?= $detailType === 'printed' ? 'selected' : '' ?>><?= $this->__('detail_type_printed') ?></option>
                    </select>
                </div>

                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-line me-1"></i><?= $this->__('filter') ?>
                        </button>
                        <?php if ($search || $detailType): ?>
                        <a href="/catalog/details" class="btn btn-soft-secondary">
                            <i class="ri-refresh-line me-1"></i><?= $this->__('clear_filters') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($details)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ri-inbox-line fs-1 text-muted"></i>
        <h5 class="mt-3 text-muted"><?= $this->__('no_details_found') ?></h5>
        <?php if ($this->can('catalog.details.create')): ?>
        <a href="/catalog/details/create" class="btn btn-primary mt-3">
            <i class="ri-add-line me-1"></i><?= $this->__('new_detail') ?>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="row row-cols-1 row-cols-xl-2 g-3">
    <?php foreach ($details as $detail): ?>
    <div class="col">
        <div class="card h-100 <?= empty($detail['is_active']) ? 'border-secondary opacity-75' : '' ?>">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-shrink-0" style="width: 140px; height: 140px;">
                        <?php if (!empty($detail['image_path'])): ?>
                        <img src="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>"
                             alt="<?= $this->e($detail['name']) ?>"
                             class="img-fluid rounded object-fit-cover w-100 h-100"
                             data-image-preview="/<?= $this->e(ltrim($detail['image_path'], '/')) ?>"
                             style="cursor: pointer;">
                        <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center w-100 h-100">
                            <span class="text-muted small"><?= $this->__('no_photo') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="/catalog/details/<?= $detail['id'] ?>" class="fw-semibold text-primary fs-5 text-decoration-none font-monospace">
                                <?= $this->e($detail['sku']) ?>
                            </a>
                            <?php if (!empty($detail['is_active'])): ?>
                            <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted mb-2"><?= $this->e($detail['name']) ?></p>

                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('detail_type') ?>:</span>
                                <span class="fw-medium">
                                    <?= $detail['detail_type'] === 'printed'
                                        ? $this->__('detail_type_printed')
                                        : $this->__('detail_type_purchased') ?>
                                </span>
                            </div>

                            <?php if ($detail['detail_type'] === 'printed'): ?>
                            <?php if (!empty($detail['detail_materials']) && count($detail['detail_materials']) > 1): ?>
                            <!-- Multi-material display -->
                            <div class="mb-2">
                                <span class="text-muted d-block mb-1"><?= $this->__('materials') ?>:</span>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($detail['detail_materials'] as $dm): ?>
                                    <span class="badge bg-info-subtle text-info"<?php if (!empty($dm['alias_color'])): ?> style="background: <?= $this->e($dm['alias_color']) ?> !important; color: <?= $this->contrastColor($dm['alias_color']) ?> !important"<?php endif; ?>>
                                        <?= $this->e($dm['filament_alias'] ?: $dm['material_sku']) ?>
                                        <small>(<?= $this->e($dm['material_qty_grams'] ?? 0) ?> <?= $this->__('grams_short') ?>)</small>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Single material display -->
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('material') ?>:</span>
                                <span class="fw-medium">
                                    <?php if ($detail['material_item_id']): ?>
                                    <?php if (!empty($detail['material_filament_alias'])): ?>
                                    <span class="badge bg-info-subtle text-info"<?php if (!empty($detail['material_alias_color'])): ?> style="background: <?= $this->e($detail['material_alias_color']) ?> !important; color: <?= $this->contrastColor($detail['material_alias_color']) ?> !important"<?php endif; ?>><?= $this->e($detail['material_filament_alias']) ?></span>
                                    <?php else: ?>
                                    <?= $this->e($detail['material_sku'] ?? '') ?>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('material_qty_grams') ?>:</span>
                                <span class="fw-medium"><?= $detail['material_qty_grams'] ? $this->e($detail['material_qty_grams']) . ' g' : '-' ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted"><?= $this->__('print_time_minutes') ?>:</span>
                                <span class="fw-medium"><?= $detail['print_time_minutes'] ? $this->e($detail['print_time_minutes']) . ' min' : '-' ?></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><?= $this->__('production_cost') ?>:</span>
                                <span class="fw-medium">
                                    <?php if ($detail['production_cost'] !== null && $detail['production_cost'] > 0): ?>
                                    <strong class="text-success"><?= number_format($detail['production_cost'], 2) ?></strong>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="/catalog/details/<?= $detail['id'] ?>" class="btn btn-sm btn-soft-secondary">
                        <i class="ri-eye-line me-1"></i><?= $this->__('view') ?>
                    </a>
                    <?php if ($this->can('catalog.details.edit')): ?>
                    <a href="/catalog/details/<?= $detail['id'] ?>/edit" class="btn btn-sm btn-soft-primary">
                        <i class="ri-pencil-line me-1"></i><?= $this->__('edit') ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($this->can('catalog.details.create')): ?>
                    <a href="/catalog/details/<?= $detail['id'] ?>/copy" target="_blank" class="btn btn-sm btn-soft-info" title="<?= $this->__('copy') ?>">
                        <i class="ri-file-copy-line"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
<div class="card mt-3">
    <div class="card-body">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                        <i class="ri-arrow-left-s-line"></i> <?= $this->__('prev') ?>
                    </a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><?= $this->__('page_of', ['current' => $page, 'total' => $totalPages]) ?></span>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                        <?= $this->__('next') ?> <i class="ri-arrow-right-s-line"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php endif; ?>

<?php $this->endSection(); ?>
