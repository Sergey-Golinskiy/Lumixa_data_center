<?php $this->section('content'); ?>

<div class="page-header">
    <h1>Variants</h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.variants.create')): ?>
        <a href="/catalog/variants/create" class="btn btn-primary">+ New Variant</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search SKU or name..."
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="product_id">
                        <option value="">All Products</option>
                        <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= $productId == $product['id'] ? 'selected' : '' ?>>
                            <?= $this->e($product['code']) ?> - <?= $this->e($product['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="/catalog/variants" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Variants Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Product</th>
                        <th>Attributes</th>
                        <th class="text-center">BOM</th>
                        <th class="text-center">Routing</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($variants)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No variants found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($variants as $variant): ?>
                    <tr>
                        <td>
                            <a href="/catalog/variants/<?= $variant['id'] ?>">
                                <strong><?= $this->e($variant['sku']) ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($variant['name']) ?></td>
                        <td>
                            <a href="/catalog/products/<?= $variant['product_id'] ?>">
                                <?= $this->e($variant['product_code']) ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            $attrs = json_decode($variant['attributes'] ?? '{}', true);
                            if ($attrs): ?>
                            <small><?= implode(', ', array_map(fn($k, $v) => "{$k}: {$v}", array_keys($attrs), $attrs)) ?></small>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($variant['has_bom']): ?>
                            <span class="badge badge-success">Yes</span>
                            <?php else: ?>
                            <span class="badge badge-warning">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($variant['has_routing']): ?>
                            <span class="badge badge-success">Yes</span>
                            <?php else: ?>
                            <span class="badge badge-warning">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($variant['is_active']): ?>
                            <span class="badge badge-success">Active</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/variants/<?= $variant['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                            <?php if ($this->can('catalog.variants.edit')): ?>
                            <a href="/catalog/variants/<?= $variant['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
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
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; Prev</a>
            <?php endif; ?>
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
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
.text-center { text-align: center; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
