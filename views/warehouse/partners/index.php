<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('suppliers') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('warehouse.partners.create')): ?>
        <a href="/warehouse/partners/create" class="btn btn-primary">+ <?= $this->__('new_partner') ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_partner') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="type">
                        <option value=""><?= $this->__('all_types') ?></option>
                        <option value="supplier" <?= $type === 'supplier' ? 'selected' : '' ?>><?= $this->__('suppliers') ?></option>
                        <option value="customer" <?= $type === 'customer' ? 'selected' : '' ?>><?= $this->__('customers') ?></option>
                        <option value="both" <?= $type === 'both' ? 'selected' : '' ?>><?= $this->__('both') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/warehouse/partners" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- Partners Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table data-sortable>
                <thead>
                    <tr>
                        <th data-sort="id">ID</th>
                        <th data-sort="name"><?= $this->__('name') ?></th>
                        <th data-sort="type"><?= $this->__('type') ?></th>
                        <th data-sort="phone"><?= $this->__('phone') ?></th>
                        <th data-sort="city">City</th>
                        <th data-sort="actions"><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partners)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted"><?= $this->__('no_partners_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>">
                                <strong><?= $this->e($partner['id']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>">
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
                            <span class="badge badge-<?= $typeClass ?>"><?= $this->__($partner['type'] === 'supplier' ? 'suppliers' : ($partner['type'] === 'customer' ? 'customers' : 'both')) ?></span>
                        </td>
                        <td><?= $this->e($partner['phone'] ?? '-') ?></td>
                        <td>
                            <?= $this->e($partner['city'] ?? '-') ?>
                        </td>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($this->can('warehouse.partners.edit')): ?>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('previous') ?></a>
            <?php endif; ?>
            <span class="pagination-info"><?= $this->__('page') ?> <?= $page ?> <?= $this->__('of') ?> <?= $totalPages ?> (<?= $total ?> <?= $this->__('partners') ?>)</span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.filter-group { flex: 1; min-width: 150px; }
.filter-group input, .filter-group select { width: 100%; }
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}
</style>

<?php $this->endSection(); ?>
