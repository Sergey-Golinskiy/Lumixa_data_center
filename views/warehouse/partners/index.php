<?php $this->section('content'); ?>

<!-- Page Header with Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div></div>
            <?php if ($this->can('warehouse.partners.create')): ?>
            <a href="/warehouse/partners/create" class="btn btn-success">
                <i class="ri-add-line me-1"></i> <?= $this->__('new_partner') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i><?= $this->__('filters') ?></h5>
    </div>
    <div class="card-body">
        <form method="GET" action="/warehouse/partners">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="<?= $this->__('search_partner') ?>"
                               value="<?= $this->e($search) ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('type') ?></label>
                    <select name="type" class="form-select">
                        <option value=""><?= $this->__('all_types') ?></option>
                        <option value="supplier" <?= $type === 'supplier' ? 'selected' : '' ?>><?= $this->__('suppliers') ?></option>
                        <option value="customer" <?= $type === 'customer' ? 'selected' : '' ?>><?= $this->__('customers') ?></option>
                        <option value="both" <?= $type === 'both' ? 'selected' : '' ?>><?= $this->__('both') ?></option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search || $type): ?>
                    <a href="/warehouse/partners" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Partners Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="ri-user-3-line me-2"></i><?= $this->__('suppliers') ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('id') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('phone') ?></th>
                        <th><?= $this->__('city') ?></th>
                        <th style="width: 150px;"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partners)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="ri-user-3-line fs-1 d-block mb-2 text-secondary"></i>
                            <span class="text-muted"><?= $this->__('no_partners_found') ?></span>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>" class="fw-medium text-primary">
                                <?= $this->e($partner['id']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>" class="text-body">
                                <?= $this->e($partner['name']) ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $typeClass = match($partner['type']) {
                                'supplier' => 'info',
                                'customer' => 'success',
                                'both' => 'warning',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $typeClass ?>-subtle text-<?= $typeClass ?>">
                                <?= $this->__($partner['type'] === 'supplier' ? 'suppliers' : ($partner['type'] === 'customer' ? 'customers' : 'both')) ?>
                            </span>
                        </td>
                        <td><?= $this->e($partner['phone'] ?? '-') ?></td>
                        <td><?= $this->e($partner['city'] ?? '-') ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/warehouse/partners/<?= $partner['id'] ?>" class="btn btn-sm btn-soft-primary" title="<?= $this->__('view') ?>">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <?php if ($this->can('warehouse.partners.edit')): ?>
                                <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-sm btn-soft-secondary" title="<?= $this->__('edit') ?>">
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

    <?php if ($totalPages > 1): ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted">
                <?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?>
                (<?= $total ?> <?= $this->__('partners') ?>)
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
