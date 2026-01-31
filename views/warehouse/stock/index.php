<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex gap-2">
                <a href="/warehouse/stock/movements" class="btn btn-soft-secondary">
                    <i class="ri-arrow-left-right-line me-1"></i> <?= $this->__('movements') ?>
                </a>
                <a href="/warehouse/stock/low-stock" class="btn btn-soft-warning">
                    <i class="ri-alert-line me-1"></i> <?= $this->__('low_stock') ?>
                </a>
                <a href="/warehouse/stock/valuation" class="btn btn-soft-primary">
                    <i class="ri-money-dollar-circle-line me-1"></i> <?= $this->__('valuation') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<?php if ($summary): ?>
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                            <i class="ri-stack-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('items_in_stock') ?></p>
                        <h4 class="mb-0"><?= number_format($summary['item_count'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                            <i class="ri-box-3-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_units') ?></p>
                        <h4 class="mb-0"><?= number_format($summary['total_quantity'] ?? 0, 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_value') ?></p>
                        <h4 class="mb-0"><?= number_format($summary['total_value'] ?? 0, 2) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/warehouse/stock">
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
                    <label class="form-label"><?= $this->__('category') ?></label>
                    <select name="category" class="form-select">
                        <option value=""><?= $this->__('all_categories') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <?php $categoryValue = $cat['category'] ?? ''; ?>
                            <?php if ($categoryValue === ''): continue; endif; ?>
                            <option value="<?= $this->e($categoryValue) ?>" <?= $category === $categoryValue ? 'selected' : '' ?>>
                                <?= $this->e($categoryValue) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="show_empty" name="show_empty" <?= $showEmpty ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_empty"><?= $this->__('show_zero_stock') ?></label>
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search || $category || $showEmpty): ?>
                    <a href="/warehouse/stock" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= $this->__('stock_balances') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('category') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('reserved') ?></th>
                        <th class="text-end"><?= $this->__('available') ?></th>
                        <th class="text-end"><?= $this->__('value') ?></th>
                        <th style="width: 100px;"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stocks)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                            <span class="text-muted"><?= $this->__('no_stock_found') ?></span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($stocks as $stock): ?>
                    <?php
                    $available = $stock['total_quantity'] - $stock['total_reserved'];
                    ?>
                    <tr>
                        <td>
                            <a href="/warehouse/stock/<?= $stock['id'] ?>" class="fw-medium text-primary">
                                <?= $this->e($stock['sku']) ?>
                            </a>
                        </td>
                        <td><?= $this->e($stock['name']) ?></td>
                        <td><?= $this->e($stock['category'] ?? '-') ?></td>
                        <td class="text-end">
                            <?= number_format($stock['total_quantity'], 3) ?>
                            <small class="text-muted"><?= $this->__('unit_' . ($stock['unit'] ?? 'pcs')) ?></small>
                        </td>
                        <td class="text-end">
                            <?php if ($stock['total_reserved'] > 0): ?>
                            <span class="text-warning"><?= number_format($stock['total_reserved'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($available < 0): ?>
                            <span class="badge bg-danger"><?= number_format($available, 3) ?></span>
                            <?php else: ?>
                            <span class="text-success fw-medium"><?= number_format($available, 3) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= number_format($stock['total_value'], 2) ?></td>
                        <td>
                            <a href="/warehouse/stock/<?= $stock['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('details') ?>">
                                <i class="ri-eye-line"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?>
                (<?= $total ?> <?= $this->__('items') ?>)
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="ri-arrow-left-s-line"></i>
                        </a>
                    </li>
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
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
