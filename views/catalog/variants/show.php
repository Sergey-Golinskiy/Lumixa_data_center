<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/variants" class="btn btn-secondary">&laquo; Back to Variants</a>
    <a href="/catalog/products/<?= $variant['product_id'] ?>" class="btn btn-outline">View Product</a>
    <?php if ($this->can('catalog.variants.edit')): ?>
    <a href="/catalog/variants/<?= $variant['id'] ?>/edit" class="btn btn-outline">Edit Variant</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Variant Information -->
    <div class="card">
        <div class="card-header">Variant Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">SKU</span>
                <span class="detail-value"><strong><?= $this->e($variant['sku']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($variant['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Product</span>
                <span class="detail-value">
                    <a href="/catalog/products/<?= $variant['product_id'] ?>">
                        <?= $this->e($variant['product_code']) ?> - <?= $this->e($variant['product_name']) ?>
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Base Price</span>
                <span class="detail-value"><?= number_format($variant['base_price'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php if ($variant['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                    <?php else: ?>
                    <span class="badge badge-secondary">Inactive</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php
            $attrs = json_decode($variant['attributes'] ?? '{}', true);
            if ($attrs): ?>
            <div class="detail-row">
                <span class="detail-label">Attributes</span>
                <span class="detail-value">
                    <?php foreach ($attrs as $key => $value): ?>
                    <span class="attribute-tag"><?= $this->e($key) ?>: <?= $this->e($value) ?></span>
                    <?php endforeach; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Costing -->
    <div class="card">
        <div class="card-header">Costing</div>
        <div class="card-body">
            <?php if ($costing): ?>
            <div class="detail-row">
                <span class="detail-label">Material Cost</span>
                <span class="detail-value"><?= number_format($costing['material_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Labor Cost</span>
                <span class="detail-value"><?= number_format($costing['labor_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Overhead Cost</span>
                <span class="detail-value"><?= number_format($costing['overhead_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Cost</span>
                <span class="detail-value"><strong><?= number_format($costing['total_cost'], 2) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Calculated</span>
                <span class="detail-value"><?= $this->datetime($costing['calculated_at']) ?></span>
            </div>
            <?php else: ?>
            <p class="text-muted">No costing calculated yet. Add BOM and Routing to calculate costs.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BOM Section -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        Bill of Materials (BOM)
        <?php if ($this->can('catalog.bom.create')): ?>
        <a href="/catalog/bom/create?variant_id=<?= $variant['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ Create BOM</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($activeBom): ?>
        <div class="bom-header">
            <strong>Version <?= $this->e($activeBom['version']) ?></strong>
            <?= $activeBom['name'] ? " - {$this->e($activeBom['name'])}" : '' ?>
            <span class="badge badge-success">Active</span>
            <a href="/catalog/bom/<?= $activeBom['id'] ?>" class="btn btn-sm btn-outline" style="margin-left: 10px;">View Details</a>
        </div>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Name</th>
                        <th class="text-right">Quantity</th>
                        <th>Unit</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bomLines as $line): ?>
                    <tr>
                        <td><a href="/warehouse/items/<?= $line['item_id'] ?>"><?= $this->e($line['sku']) ?></a></td>
                        <td><?= $this->e($line['item_name']) ?></td>
                        <td class="text-right"><?= number_format($line['quantity'], 4) ?></td>
                        <td><?= $this->e($line['unit']) ?></td>
                        <td class="text-right"><?= number_format($line['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($line['quantity'] * $line['unit_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Total Material Cost:</strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_map(fn($l) => $l['quantity'] * $l['unit_cost'], $bomLines)), 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">No active BOM. Create one to define materials required for manufacturing.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Routing Section -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        Routing (Operations)
        <?php if ($this->can('catalog.routing.create')): ?>
        <a href="/catalog/routing/create?variant_id=<?= $variant['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ Create Routing</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($activeRouting): ?>
        <div class="routing-header">
            <strong>Version <?= $this->e($activeRouting['version']) ?></strong>
            <?= $activeRouting['name'] ? " - {$this->e($activeRouting['name'])}" : '' ?>
            <span class="badge badge-success">Active</span>
            <a href="/catalog/routing/<?= $activeRouting['id'] ?>" class="btn btn-sm btn-outline" style="margin-left: 10px;">View Details</a>
        </div>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th>Op #</th>
                        <th>Operation</th>
                        <th>Work Center</th>
                        <th class="text-right">Setup (min)</th>
                        <th class="text-right">Run (min)</th>
                        <th class="text-right">Labor</th>
                        <th class="text-right">Overhead</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routingOperations as $op): ?>
                    <tr>
                        <td><?= $op['operation_number'] ?></td>
                        <td><?= $this->e($op['name']) ?></td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($op['setup_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'], 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_column($routingOperations, 'labor_cost')), 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_column($routingOperations, 'overhead_cost')), 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted">No active routing. Create one to define manufacturing operations.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }
.attribute-tag {
    display: inline-block;
    padding: 3px 8px;
    background: var(--bg);
    border-radius: 4px;
    margin-right: 5px;
    font-size: 13px;
}
.bom-header, .routing-header { padding-bottom: 10px; border-bottom: 1px solid var(--border); }
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
