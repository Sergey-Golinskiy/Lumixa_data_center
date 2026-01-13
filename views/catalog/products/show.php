<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; Back to Products</a>
    <?php if ($this->can('catalog.products.edit')): ?>
    <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-outline">Edit Product</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Product Information -->
    <div class="card">
        <div class="card-header">Product Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Code</span>
                <span class="detail-value"><strong><?= $this->e($product['code']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($product['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Category</span>
                <span class="detail-value"><?= $this->e($product['category'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Base Price</span>
                <span class="detail-value"><?= number_format($product['base_price'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php if ($product['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">Inactive</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created</span>
                <span class="detail-value"><?= $this->datetime($product['created_at']) ?></span>
            </div>
            <?php if ($product['description']): ?>
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value"><?= nl2br($this->e($product['description'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="card">
        <div class="card-header">Statistics</div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value"><?= count($variants) ?></div>
                    <div class="stat-label">Variants</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($variants, fn($v) => $v['has_bom'])) ?></div>
                    <div class="stat-label">With BOM</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($variants, fn($v) => $v['has_routing'])) ?></div>
                    <div class="stat-label">With Routing</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Variants -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        Variants
        <?php if ($this->can('catalog.variants.create')): ?>
        <a href="/catalog/variants/create?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ Add Variant</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Attributes</th>
                        <th class="text-right">Price</th>
                        <th class="text-center">BOM</th>
                        <th class="text-center">Routing</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($variants)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No variants. Create one to define manufacturing specifications.</td>
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
                            <?php
                            $attrs = json_decode($variant['attributes'] ?? '{}', true);
                            if ($attrs): ?>
                            <small>
                                <?= implode(', ', array_map(fn($k, $v) => "{$k}: {$v}", array_keys($attrs), $attrs)) ?>
                            </small>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($variant['base_price'], 2) ?></td>
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
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
.stat-box { text-align: center; padding: 15px; background: var(--bg); border-radius: 8px; }
.stat-value { font-size: 28px; font-weight: bold; color: var(--primary); }
.stat-label { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
.text-right { text-align: right; }
.text-center { text-align: center; }
</style>

<?php $this->endSection(); ?>
