<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/variants" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('variants')]) ?></a>
    <a href="/catalog/products/<?= $variant['product_id'] ?>" class="btn btn-outline"><?= $this->__('view_product') ?></a>
    <?php if ($this->can('catalog.variants.edit')): ?>
    <a href="/catalog/variants/<?= $variant['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_variant') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Variant Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('variant_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('sku') ?></span>
                <span class="detail-value"><strong><?= $this->e($variant['sku']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($variant['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($variant['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($variant['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($variant['name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('product') ?></span>
                <span class="detail-value">
                    <a href="/catalog/products/<?= $variant['product_id'] ?>">
                        <?= $this->e($variant['product_code']) ?> - <?= $this->e($variant['product_name']) ?>
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('base_price') ?></span>
                <span class="detail-value"><?= number_format($variant['base_price'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php if ($variant['is_active']): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php
            $attrs = json_decode($variant['attributes'] ?? '{}', true);
            if ($attrs): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('attributes') ?></span>
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
        <div class="card-header"><?= $this->__('costing') ?></div>
        <div class="card-body">
            <?php if ($costing): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('material_cost') ?></span>
                <span class="detail-value"><?= number_format($costing['material_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('labor_cost') ?></span>
                <span class="detail-value"><?= number_format($costing['labor_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('overhead_cost') ?></span>
                <span class="detail-value"><?= number_format($costing['overhead_cost'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('total_cost') ?></span>
                <span class="detail-value"><strong><?= number_format($costing['total_cost'], 2) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('last_calculated') ?></span>
                <span class="detail-value"><?= $this->datetime($costing['calculated_at']) ?></span>
            </div>
            <?php else: ?>
            <p class="text-muted"><?= $this->__('no_costing_yet') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BOM Section -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('bill_of_materials') ?> (BOM)
        <?php if ($this->can('catalog.bom.create')): ?>
        <a href="/catalog/bom/create?variant_id=<?= $variant['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ <?= $this->__('create_bom') ?></a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($activeBom): ?>
        <div class="bom-header">
            <strong><?= $this->__('version') ?> <?= $this->e($activeBom['version']) ?></strong>
            <?= $activeBom['name'] ? " - {$this->e($activeBom['name'])}" : '' ?>
            <span class="badge badge-success"><?= $this->__('active') ?></span>
            <a href="/catalog/bom/<?= $activeBom['id'] ?>" class="btn btn-sm btn-outline" style="margin-left: 10px;"><?= $this->__('view_details') ?></a>
        </div>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('item') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th><?= $this->__('unit') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                        <th class="text-right"><?= $this->__('total') ?></th>
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
                        <td colspan="5" class="text-right"><strong><?= $this->__('total_material_cost') ?>:</strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_map(fn($l) => $l['quantity'] * $l['unit_cost'], $bomLines)), 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted"><?= $this->__('no_active_bom') ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Routing Section -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('routing_operations') ?>
        <?php if ($this->can('catalog.routing.create')): ?>
        <a href="/catalog/routing/create?variant_id=<?= $variant['id'] ?>" class="btn btn-primary btn-sm" style="float: right;">+ <?= $this->__('create_routing') ?></a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($activeRouting): ?>
        <div class="routing-header">
            <strong><?= $this->__('version') ?> <?= $this->e($activeRouting['version']) ?></strong>
            <?= $activeRouting['name'] ? " - {$this->e($activeRouting['name'])}" : '' ?>
            <span class="badge badge-success"><?= $this->__('active') ?></span>
            <a href="/catalog/routing/<?= $activeRouting['id'] ?>" class="btn btn-sm btn-outline" style="margin-left: 10px;"><?= $this->__('view_details') ?></a>
        </div>
        <div class="table-container" style="margin-top: 15px;">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('operation_number') ?></th>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-right"><?= $this->__('setup_minutes') ?></th>
                        <th class="text-right"><?= $this->__('run_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor') ?></th>
                        <th class="text-right"><?= $this->__('overhead') ?></th>
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
                        <td colspan="5" class="text-right"><strong><?= $this->__('total') ?>:</strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_column($routingOperations, 'labor_cost')), 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format(array_sum(array_column($routingOperations, 'overhead_cost')), 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-muted"><?= $this->__('no_active_routing') ?></p>
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
