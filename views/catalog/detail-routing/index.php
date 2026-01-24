<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('detail_routing') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.detail_routing.create')): ?>
        <a href="/catalog/detail-routing/create" class="btn btn-primary">+ <?= $this->__('new_detail_routing') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_routing') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value=""><?= $this->__('all_status') ?></option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>><?= $this->__('archived') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/catalog/detail-routing" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table data-sortable>
                <thead>
                    <tr>
                        <th data-sort="detail"><?= $this->__('detail') ?></th>
                        <th data-sort="version"><?= $this->__('version') ?></th>
                        <th data-sort="name"><?= $this->__('name') ?></th>
                        <th class="text-center" data-sort="operations"><?= $this->__('operations') ?></th>
                        <th class="text-right" data-sort="total_time"><?= $this->__('total_time_minutes') ?></th>
                        <th class="text-right" data-sort="total_cost"><?= $this->__('total_cost') ?></th>
                        <th data-sort="status"><?= $this->__('status') ?></th>
                        <th data-sort="actions"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($routings)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_routings_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($routings as $routing): ?>
                    <tr>
                        <td>
                            <a href="/catalog/details/<?= $routing['detail_id'] ?>">
                                <strong><?= $this->e($routing['detail_sku']) ?></strong>
                            </a>
                            <br><small class="text-muted"><?= $this->e($routing['detail_name']) ?></small>
                        </td>
                        <td><?= $this->e($routing['version']) ?></td>
                        <td><?= $this->e($routing['name'] ?? '-') ?></td>
                        <td class="text-center"><?= $routing['op_count'] ?></td>
                        <td class="text-right"><?= number_format($routing['total_time'] ?? 0) ?></td>
                        <td class="text-right"><?= number_format($routing['total_cost'] ?? 0, 2) ?></td>
                        <td>
                            <?php
                            $statusClass = match($routing['status']) {
                                'draft' => 'warning',
                                'active' => 'success',
                                'archived' => 'secondary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge badge-<?= $statusClass ?>"><?= $this->__('detail_routing_status_' . $routing['status']) ?></span>
                        </td>
                        <td>
                            <a href="/catalog/detail-routing/<?= $routing['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($routing['status'] === 'draft' && $this->can('catalog.detail_routing.edit')): ?>
                            <a href="/catalog/detail-routing/<?= $routing['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('previous') ?></a>
            <?php endif; ?>
            <span class="pagination-info"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('detail_routing') ?>)</span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->endSection(); ?>
