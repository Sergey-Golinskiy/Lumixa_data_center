<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing" class="btn btn-secondary">&laquo; Cost Analysis</a>
    <a href="/catalog/variants/<?= $variant['id'] ?>" class="btn btn-outline">View Variant</a>
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
                <div style="color: #666;">Material Cost</div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #fff3e0; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #f57c00;">
                    <?= number_format($variant['labor_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;">Labor Cost</div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #f3e5f5; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #7b1fa2;">
                    <?= number_format($variant['overhead_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;">Overhead</div>
            </div>
            <div class="cost-item" style="text-align: center; padding: 20px; background: #e8f5e9; border-radius: 8px;">
                <div style="font-size: 1.5em; font-weight: bold; color: #388e3c;">
                    <?= number_format($variant['planned_cost'] ?? 0, 2) ?>
                </div>
                <div style="color: #666;">Total Planned</div>
            </div>
        </div>
        <?php if ($variant['calculated_at']): ?>
        <p class="text-muted" style="text-align: center;">
            Last calculated: <?= date('d.m.Y H:i', strtotime($variant['calculated_at'])) ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php if ($bom): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0;">BOM: <?= h($bom['name']) ?></h4>
        <a href="/catalog/bom/<?= $bom['id'] ?>" class="btn btn-outline btn-sm">View BOM</a>
    </div>
    <div class="card-body">
        <?php if (empty($bomLines)): ?>
        <p class="text-muted">No BOM lines defined.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th style="text-align: right;">Quantity</th>
                    <th>Unit</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Line Cost</th>
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
                    <td colspan="5" style="text-align: right;">Total Material Cost:</td>
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
        <h4 style="margin: 0;">Routing: <?= h($routing['name']) ?></h4>
        <a href="/catalog/routing/<?= $routing['id'] ?>" class="btn btn-outline btn-sm">View Routing</a>
    </div>
    <div class="card-body">
        <?php if (empty($operations)): ?>
        <p class="text-muted">No operations defined.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Operation</th>
                    <th>Work Center</th>
                    <th style="text-align: right;">Setup (min)</th>
                    <th style="text-align: right;">Run (min)</th>
                    <th style="text-align: right;">Rate/hr</th>
                    <th style="text-align: right;">Op. Cost</th>
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
                    <td colspan="6" style="text-align: right;">Total Labor Cost:</td>
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
        <h4 style="margin: 0;">Production History</h4>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th style="text-align: right;">Qty</th>
                    <th style="text-align: right;">Material</th>
                    <th style="text-align: right;">Labor</th>
                    <th style="text-align: right;">Total</th>
                    <th style="text-align: right;">Unit Cost</th>
                    <th>Completed</th>
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
