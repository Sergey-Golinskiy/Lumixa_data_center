<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <?php if ($this->can('warehouse.items.create')): ?>
            <a href="/warehouse/items/create" class="btn btn-success">
                <i class="ri-add-line me-1"></i> <?= $this->__('new_item') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Live Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/warehouse/items">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('search_sku_name') ?>"
                               value="<?= $this->e($search) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('type') ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?= $this->__('all_types') ?></option>
                        <?php foreach ($types as $value => $label): ?>
                        <option value="<?= $this->e($value) ?>" <?= $type === $value ? 'selected' : '' ?>>
                            <?= $this->e($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all') ?></option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= $this->__('inactive') ?></option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search || $type || ($status ?? '')): ?>
                    <a href="/warehouse/items" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Items Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= $this->__('items_sku') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;"><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('unit') ?></th>
                        <th class="text-end"><?= $this->__('on_hand') ?></th>
                        <th class="text-end"><?= $this->__('reserved') ?></th>
                        <th class="text-end"><?= $this->__('available') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th style="width: 120px;"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="10" class="text-muted text-center py-5">
                            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                            <?= $this->__('no_items_found') ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_path'])): ?>
                                <div class="avatar-sm bg-light rounded p-1">
                                    <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="img-fluid rounded" style="cursor:pointer" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                                </div>
                                <?php else: ?>
                                <div class="avatar-sm bg-light rounded d-flex align-items-center justify-content-center">
                                    <i class="ri-image-line text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/warehouse/items/<?= $item['id'] ?>" class="fw-medium text-primary">
                                    <?= $this->e($item['sku']) ?>
                                </a>
                            </td>
                            <td><?= $this->e($item['name']) ?></td>
                            <td>
                                <span class="badge badge-type-<?= $this->e($item['type']) ?>">
                                    <?= $this->e($types[$item['type']] ?? $this->__('item_type_' . $item['type'])) ?>
                                </span>
                            </td>
                            <td><?= $this->__('unit_' . $item['unit']) ?></td>
                            <td class="text-end"><?= $this->number($item['total_on_hand'] ?? 0) ?></td>
                            <td class="text-end"><?= $this->number($item['total_reserved'] ?? 0) ?></td>
                            <td class="text-end">
                                <?php $available = ($item['total_on_hand'] ?? 0) - ($item['total_reserved'] ?? 0); ?>
                                <?php if ($available <= 0): ?>
                                <span class="badge bg-danger"><?= $this->number($available) ?></span>
                                <?php elseif ($available <= ($item['min_stock'] ?? 0)): ?>
                                <span class="badge bg-warning"><?= $this->number($available) ?></span>
                                <?php else: ?>
                                <span class="text-success fw-medium"><?= $this->number($available) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="/warehouse/items/<?= $item['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('view') ?>">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <?php if ($this->can('warehouse.items.edit')): ?>
                                    <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-sm btn-soft-secondary" title="<?= $this->__('edit') ?>">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $pagination['current_page'] ?> <?= $this->__('of') ?> <?= $pagination['total_pages'] ?>
                (<?= $pagination['total'] ?> <?= $this->__('items') ?>)
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status ?? '') ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php
                    $startPage = max(1, $pagination['current_page'] - 2);
                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status ?? '') ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>&status=<?= urlencode($status ?? '') ?>">
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
