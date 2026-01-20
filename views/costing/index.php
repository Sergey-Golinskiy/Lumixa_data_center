<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/costing/plan" class="btn btn-primary"><?= $this->__('planned_costs') ?></a>
    <a href="/costing/actual" class="btn btn-secondary"><?= $this->__('actual_costs') ?></a>
    <a href="/costing/compare" class="btn btn-outline"><?= $this->__('plan_vs_actual') ?></a>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
        <div style="font-size: 2em; font-weight: bold; color: #007bff;"><?= (int)$totalVariants ?></div>
        <div style="color: #666;"><?= $this->__('active_variants') ?></div>
    </div>
    <div class="stat-card" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
        <div style="font-size: 2em; font-weight: bold; color: #28a745;"><?= (int)$variantsWithCost ?></div>
        <div style="color: #666;"><?= $this->__('with_calculated_costs') ?></div>
    </div>
    <div class="stat-card" style="padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
        <div style="font-size: 2em; font-weight: bold; color: #17a2b8;"><?= (int)$completedOrders ?></div>
        <div style="color: #666;"><?= $this->__('completed_orders') ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 style="margin: 0;"><?= $this->__('recent_cost_variances') ?></h4>
    </div>
    <div class="card-body">
        <?php if (empty($recentVariances)): ?>
        <p class="text-muted"><?= $this->__('no_completed_orders') ?></p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->__('order') ?></th>
                    <th><?= $this->__('variant') ?></th>
                    <th><?= $this->__('quantity') ?></th>
                    <th style="text-align: right;"><?= $this->__('planned') ?></th>
                    <th style="text-align: right;"><?= $this->__('actual_cost') ?></th>
                    <th style="text-align: right;"><?= $this->__('variance') ?></th>
                    <th><?= $this->__('completed') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentVariances as $row): ?>
                <tr>
                    <td>
                        <a href="/production/orders/<?= $row['order_number'] ?>"><?= h($row['order_number']) ?></a>
                    </td>
                    <td>
                        <a href="/costing/variant/<?= $row['sku'] ?>"><?= h($row['sku']) ?></a>
                        <small style="display: block; color: #666;"><?= h($row['name']) ?></small>
                    </td>
                    <td><?= number_format($row['quantity'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format(($row['planned_cost'] ?? 0) * $row['quantity'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format($row['actual_cost'] ?? 0, 2) ?></td>
                    <td style="text-align: right;">
                        <?php
                        $variance = $row['variance'];
                        $class = $variance > 0 ? 'text-danger' : ($variance < 0 ? 'text-success' : '');
                        $sign = $variance > 0 ? '+' : '';
                        ?>
                        <span class="<?= $class ?>">
                            <?= $sign ?><?= number_format($variance, 2) ?>
                            (<?= $sign ?><?= number_format($row['variance_percent'], 1) ?>%)
                        </span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($row['completed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
.text-danger { color: #dc3545; }
.text-success { color: #28a745; }
</style>

<?php $this->endSection(); ?>
