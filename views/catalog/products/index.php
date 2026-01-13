<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Products</h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.products.create')): ?>
        <a href="/catalog/products/create" class="btn btn-primary">+ New Product</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search code or name..."
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $this->e($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                            <?= $this->e($cat['category']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/catalog/products" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-right">Base Price</th>
                        <th class="text-center">Variants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No products found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <a href="/catalog/products/<?= $product['id'] ?>">
                                <strong><?= $this->e($product['code']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($product['name']) ?></td>
                        <td><?= $this->e($product['category'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($product['base_price'], 2) ?></td>
                        <td class="text-center">
                            <?php if ($product['variant_count'] > 0): ?>
                            <span class="badge badge-info"><?= $product['variant_count'] ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/products/<?= $product['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('catalog.products.edit')): ?>
                            <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
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
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> products)</span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group input, .filter-group select { width: 100%; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
