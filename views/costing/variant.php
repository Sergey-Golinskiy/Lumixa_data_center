<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('costing')]) ?></a>
    <a href="/catalog/variants/<?= $variant['id'] ?>" class="btn btn-outline"><?= $this->__('view_variant') ?></a>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;"><?= h($variant['sku']) ?> - <?= h($variant['name']) ?></h3>
    </div>
    <div class="card-body">
        <div class="cost-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="cost-item" style="text-align: center; padding: 20px; background: #e3f2fd; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #1976d2;">
                    <?= number_format($variant['material_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;"><?= $this->__('material_cost') ?></div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #fff3e0; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #f57c00;">
                    <?= number_format($variant['labor_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;"><?= $this->__('labor_cost') ?></div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #f3e5f5; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #7b1fa2;">
                    <?= number_format($variant['overhead_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;"><?= $this->__('overhead') ?></div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #e8f5e9; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #388e3c;">
                    <?= number_format($variant['planned_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;"><?= $this->__('total_planned') ?></div>
            </div>
        </div>
        <?php if ($variant['calculated_at']): ?>
        <p class="text-muted" style="text-align: center;">
            <?= $this->__('last_calculated_label') ?>: <?= date('d.m.Y H:i', strtotime($variant['calculated_at'])) ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php if ($bom): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0;"><?= $this->__('bom') ?>: <?= h($bom['name']) ?></h4>
        <a href="/catalog/bom/<?= $bom['id'] ?>" class="btn btn-outline btn-sm"><?= $this->__('view_bom') ?></a>
    </div>
    <div class="card-body">
        <?php if (empty($bomLines)): ?>
        <p class="text-muted"><?= $this->__('no_bom_lines_defined') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= $this->__('item') ?></th>
                    <th style="text-align: right;"><?= $this->__('quantity') ?></th>
                    <th><?= $this->__('unit') ?></th>
                    <th style="text-align: right;"><?= $this->__('price') ?></th>
                    <th style="text-align: right;"><?= $this->__('line_cost') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalMaterial = 0;
                foreach ($bomLines as $line):
                    $totalMaterial += $line['line_cost'];
                ?>
                <tr>
                    <td><?= (int)$line['sequence'] ?></td>
                    <td>
                        <a href="/warehouse/items/<?= $line['item_id'] ?>"><?= h($line['sku']) ?></a>
                        - <?= h($line['name']) ?>
                    </td>
                    <td style="text-align: right;"><?= number_format($line['quantity'], 4) ?></td>
                    <td><?= h($line['unit']) ?></td>
                    <td style="text-align: right;"><?= number_format($line['price'] ?? 0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($line['line_cost'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td colspan="5" style="text-align: right;"><?= $this->__('total_material_cost') ?>:</td>
                    <td style="text-align: right;"><?= number_format($totalMaterial, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($routing): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0;"><?= $this->__('routing') ?>: <?= h($routing['name']) ?></h4>
        <a href="/catalog/routing/<?= $routing['id'] ?>" class="btn btn-outline btn-sm"><?= $this->__('view_routing') ?></a>
    </div>
    <div class="card-body">
        <?php if (empty($operations)): ?>
        <p class="text-muted"><?= $this->__('no_operations_defined') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= $this->__('operation') ?></th>
                    <th><?= $this->__('work_center') ?></th>
                    <th style="text-align: right;"><?= $this->__('setup_minutes') ?></th>
                    <th style="text-align: right;"><?= $this->__('run_minutes') ?></th>
                    <th style="text-align: right;"><?= $this->__('rate_per_hour') ?></th>
                    <th style="text-align: right;"><?= $this->__('operation_cost') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalLabor = 0;
                foreach ($operations as $op):
                    $totalLabor += $op['operation_cost'];
                ?>
                <tr>
                    <td><?= (int)$op['sequence'] ?></td>
                    <td><?= h($op['name']) ?></td>
                    <td><?= h($op['work_center_name'] ?? '-') ?></td>
                    <td style="text-align: right;"><?= number_format($op['setup_time'], 1) ?></td>
                    <td style="text-align: right;"><?= number_format($op['run_time'], 1) ?></td>
                    <td style="text-align: right;"><?= number_format($op['hour_rate'] ?? 0, 2) ?></td>
                    <td style="text-align: right;"><?= number_format($op['operation_cost'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td colspan="6" style="text-align: right;"><?= $this->__('total_labor_cost') ?>:</td>
                    <td style="text-align: right;"><?= number_format($totalLabor, 2) ?></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($history)): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h4 style="margin: 0;"><?= $this->__('production_history') ?></h4>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->__('order') ?></th>
                    <th style="text-align: right;"><?= $this->__('quantity') ?></th>
                    <th style="text-align: right;"><?= $this->__('material_cost') ?></th>
                    <th style="text-align: right;"><?= $this->__('labor_cost') ?></th>
                    <th style="text-align: right;"><?= $this->__('total') ?></th>
                    <th style="text-align: right;"><?= $this->__('unit_cost') ?></th>
                    <th><?= $this->__('completed') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td>
                        <a href="/production/orders/<?= $h['id'] ?>"><?= h($h['order_number']) ?></a>
                    </td>
                    <td style="text-align: right;"><?= number_format($h['completed_quantity'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($h['actual_material'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($h['actual_labor'], 2) ?></td>
                    <td style="text-align: right; font-weight: bold;"><?= number_format($h['total_actual'], 2) ?></td>
                    <td style="text-align: right;">
                        <?php
                        $variance = $h['unit_cost'] - ($variant['planned_cost'] ?? 0);
                        $class = '';
                        if ($variant['planned_cost'] > 0) {
                            $class = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                        }
                        ?>
                        <span class="<?= $class ?>"><?= number_format($h['unit_cost'], 2) ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($h['completed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
</style>

<?php $this->endSection(); ?>
