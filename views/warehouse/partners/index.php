<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Partners</h1>
    <div class="page-actions">
        <?php if ($this->can('warehouse.partners.create')): ?>
        <a href="/warehouse/partners/create" class="btn btn-primary">+ New Partner</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search name, code, email..."
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="supplier" <?= $type === 'supplier' ? 'selected' : '' ?>>Suppliers</option>
                        <option value="customer" <?= $type === 'customer' ? 'selected' : '' ?>>Customers</option>
                        <option value="both" <?= $type === 'both' ? 'selected' : '' ?>>Both</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/warehouse/partners" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Partners Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Documents</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partners)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No partners found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>">
                                <strong><?= $this->e($partner['code']) ?></strong>
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
                            <span class="badge badge-<?= $typeClass ?>"><?= ucfirst($partner['type']) ?></span>
                        </td>
                        <td>
                            <?= $this->e($partner['contact_person'] ?? '-') ?>
                            <?php if ($partner['email']): ?>
                            <br><small class="text-muted"><?= $this->e($partner['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($partner['phone'] ?? '-') ?></td>
                        <td>
                            <?php if ($partner['document_count'] > 0): ?>
                            <span class="badge badge-secondary"><?= $partner['document_count'] ?></span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($partner['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('warehouse.partners.edit')): ?>
                            <a href="/warehouse/partners/<?= $partner['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
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
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; Previous</a>
            <?php endif; ?>
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> partners)</span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline">Next &raquo;</a>
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
