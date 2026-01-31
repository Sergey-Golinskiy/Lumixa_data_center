<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/stock" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_stock') ?>
    </a>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                            <i class="ri-arrow-down-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_in') ?></p>
                        <h4 class="mb-0 text-success">+<?= number_format($inTotal['qty'] ?? 0, 0) ?></h4>
                        <small class="text-muted"><?= number_format($inTotal['value'] ?? 0, 2) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                            <i class="ri-arrow-up-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('total_out') ?></p>
                        <h4 class="mb-0 text-danger">-<?= number_format($outTotal['qty'] ?? 0, 0) ?></h4>
                        <small class="text-muted"><?= number_format($outTotal['value'] ?? 0, 2) ?></small>
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
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                            <i class="ri-scales-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1"><?= $this->__('net_change') ?></p>
                        <h4 class="mb-0"><?= number_format(($inTotal['qty'] ?? 0) - ($outTotal['qty'] ?? 0), 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/warehouse/stock/movements">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('date_from') ?></label>
                    <input type="date" name="date_from" class="form-control" value="<?= $this->e($dateFrom) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('date_to') ?></label>
                    <input type="date" name="date_to" class="form-control" value="<?= $this->e($dateTo) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('item') ?></label>
                    <select name="item_id" class="form-select">
                        <option value=""><?= $this->__('all_items') ?></option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= $itemId == $item['id'] ? 'selected' : '' ?>>
                            <?= $this->e($item['sku']) ?> - <?= $this->e($item['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><?= $this->__('direction') ?></label>
                    <select name="direction" class="form-select">
                        <option value=""><?= $this->__('all') ?></option>
                        <option value="in" <?= $direction === 'in' ? 'selected' : '' ?>><?= $this->__('in') ?></option>
                        <option value="out" <?= $direction === 'out' ? 'selected' : '' ?>><?= $this->__('out') ?></option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($dateFrom || $dateTo || $itemId || $direction): ?>
                    <a href="/warehouse/stock/movements" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Movements Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i><?= $this->__('stock_movements') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('date') ?></th>
                        <th><?= $this->__('document') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('item') ?></th>
                        <th><?= $this->__('direction') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                        <th class="text-end"><?= $this->__('value') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movements)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="ri-inbox-line fs-1 d-block mb-2 text-secondary"></i>
                            <span class="text-muted"><?= $this->__('no_movements_found') ?></span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?= $this->datetime($movement['created_at']) ?></td>
                        <td>
                            <a href="/warehouse/documents/<?= $movement['document_id'] ?>" class="text-primary">
                                <?= $this->e($movement['document_number']) ?>
                            </a>
                        </td>
                        <td><?= ucfirst($movement['document_type']) ?></td>
                        <td>
                            <a href="/warehouse/stock/<?= $movement['item_id'] ?>" class="text-primary">
                                <?= $this->e($movement['sku']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="badge bg-success-subtle text-success"><?= $this->__('in') ?></span>
                            <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger"><?= $this->__('out') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($movement['movement_type'] === 'in'): ?>
                            <span class="text-success">+<?= number_format($movement['quantity'], 3) ?></span>
                            <?php else: ?>
                            <span class="text-danger">-<?= number_format($movement['quantity'], 3) ?></span>
                            <?php endif; ?>
                            <small class="text-muted"><?= $this->__('unit_' . ($movement['unit'] ?? 'pcs')) ?></small>
                        </td>
                        <td class="text-end"><?= number_format($movement['unit_cost'], 4) ?></td>
                        <td class="text-end fw-medium"><?= number_format($movement['quantity'] * $movement['unit_cost'], 2) ?></td>
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
                <?= $this->__('page_x_of_y', ['page' => $page, 'total' => $totalPages, 'count' => $total, 'type' => $this->__('movements')]) ?>
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
