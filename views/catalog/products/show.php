<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <?php if ($this->can('catalog.products.edit')): ?>
    <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_product') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Product Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('details') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('code') ?></span>
                <span class="detail-value"><strong><?= $this->e($product['code'] ?? '') ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($product['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($product['name'] ?? '') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('category') ?></span>
                <span class="detail-value"><?= $this->e($product['category_name'] ?? $product['category'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('base_price') ?></span>
                <span class="detail-value"><?= number_format($product['base_price'] ?? 0, 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php if ($product['is_active'] ?? false): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_at') ?></span>
                <span class="detail-value"><?= $this->datetime($product['created_at'] ?? '') ?></span>
            </div>
            <?php if ($product['description'] ?? false): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('description') ?></span>
                <span class="detail-value"><?= nl2br($this->e($product['description'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="card">
        <div class="card-header"><?= $this->__('system_info') ?></div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value"><?= count($variants ?? []) ?></div>
                    <div class="stat-label"><?= $this->__('variants') ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($variants ?? [], fn($v) => $v['has_bom'] ?? false)) ?></div>
                    <div class="stat-label"><?= $this->__('bom') ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= count(array_filter($variants ?? [], fn($v) => $v['has_routing'] ?? false)) ?></div>
                    <div class="stat-label"><?= $this->__('routing') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Variants -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('variants') ?>
        <?php if ($this->can('catalog.variants.create')): ?>
        <a href="/catalog/variants/create?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ <?= $this->__('new_variant') ?></a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('attributes_materials') ?></th>
                        <th class="text-right"><?= $this->__('base_price') ?></th>
                        <th class="text-center"><?= $this->__('bom') ?></th>
                        <th class="text-center"><?= $this->__('routing') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($variants)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($variants as $variant): ?>
                    <tr>
                        <td>
                            <a href="/catalog/variants/<?= $variant['id'] ?>">
                                <strong><?= $this->e($variant['sku'] ?? '') ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($variant['name'] ?? '') ?></td>
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
                        <td class="text-right"><?= number_format($variant['base_price'] ?? 0, 2) ?></td>
                        <td class="text-center">
                            <?php if ($variant['has_bom'] ?? false): ?>
                            <span class="badge badge-success"><?= $this->__('yes') ?></span>
                            <?php else: ?>
                            <span class="badge badge-warning"><?= $this->__('no') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($variant['has_routing'] ?? false): ?>
                            <span class="badge badge-success"><?= $this->__('yes') ?></span>
                            <?php else: ?>
                            <span class="badge badge-warning"><?= $this->__('no') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($variant['is_active'] ?? false): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/variants/<?= $variant['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
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
