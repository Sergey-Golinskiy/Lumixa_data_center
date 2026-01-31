<?php $this->section('content'); ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">
        <i class="ri-route-line me-2"></i>
        <?= $this->__('detail_routing') ?>
    </h4>
    <?php if ($this->can('catalog.detail_routing.create')): ?>
    <a href="/catalog/detail-routing/create" class="btn btn-success">
        <i class="ri-add-line me-1"></i> <?= $this->__('new_detail_routing') ?>
    </a>
    <?php endif; ?>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="ri-filter-3-line me-2"></i>
        <?= $this->__('filters') ?>
    </div>
    <div class="card-body">
        <form method="GET" action="/catalog/detail-routing" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label"><?= $this->__('search') ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="<?= $this->__('search_routing') ?>" value="<?= $this->e($search) ?>">
                        <?php if ($search): ?>
                        <button type="button" class="btn btn-outline-secondary clear-search" title="<?= $this->__('clear') ?>">
                            <i class="ri-close-line"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label"><?= $this->__('status') ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>><?= $this->__('archived') ?></option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-soft-primary me-2">
                        <i class="ri-search-line me-1"></i> <?= $this->__('search') ?>
                    </button>
                    <?php if ($search || $status): ?>
                    <a href="/catalog/detail-routing" class="btn btn-soft-secondary">
                        <i class="ri-refresh-line me-1"></i> <?= $this->__('clear_filters') ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= $this->__('detail') ?></th>
                        <th><?= $this->__('version') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th class="text-center"><?= $this->__('operations') ?></th>
                        <th class="text-end"><?= $this->__('total_time_minutes') ?></th>
                        <th class="text-end"><?= $this->__('total_cost') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($routings)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="ri-route-line fs-1 text-muted"></i>
                            <p class="text-muted mb-0 mt-2"><?= $this->__('no_routings_found') ?></p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($routings as $routing): ?>
                    <tr>
                        <td>
                            <a href="/catalog/details/<?= $routing['detail_id'] ?>" class="text-decoration-none">
                                <strong class="text-primary"><?= $this->e($routing['detail_sku']) ?></strong>
                            </a>
                            <br><small class="text-muted"><?= $this->e($routing['detail_name']) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $this->e($routing['version']) ?></span>
                        </td>
                        <td><?= $this->e($routing['name'] ?? '-') ?></td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info"><?= $routing['op_count'] ?></span>
                        </td>
                        <td class="text-end"><?= number_format($routing['total_time'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-end"><?= number_format($routing['total_cost'] ?? 0, 2) ?></td>
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
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/catalog/detail-routing/<?= $routing['id'] ?>" class="btn btn-soft-secondary" title="<?= $this->__('view') ?>">
                                    <i class="ri-eye-line"></i>
                                </a>
                                <?php if ($routing['status'] === 'draft' && $this->can('catalog.detail_routing.edit')): ?>
                                <a href="/catalog/detail-routing/<?= $routing['id'] ?>/edit" class="btn btn-soft-primary" title="<?= $this->__('edit') ?>">
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

        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center mb-0">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                        <i class="ri-arrow-left-s-line"></i> <?= $this->__('previous') ?>
                    </a>
                </li>
                <?php endif; ?>

                <li class="page-item disabled">
                    <span class="page-link"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('detail_routing') ?>)</span>
                </li>

                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                        <?= $this->__('next') ?> <i class="ri-arrow-right-s-line"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear search button
    const clearBtn = document.querySelector('.clear-search');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input[name="search"]');
            if (input) {
                input.value = '';
                document.getElementById('filterForm').submit();
            }
        });
    }

    // Auto-submit on select change
    document.querySelector('select[name="status"]')?.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});
</script>

<?php $this->endSection(); ?>
