<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/bom" class="btn btn-secondary">&laquo; Back to BOMs</a>
    <a href="/catalog/variants/<?= $bom['variant_id'] ?>" class="btn btn-outline">View Variant</a>
    <?php if ($bom['status'] === 'draft' && $this->can('catalog.bom.edit')): ?>
    <a href="/catalog/bom/<?= $bom['id'] ?>/edit" class="btn btn-outline">Edit</a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- BOM Information -->
    <div class="card">
        <div class="card-header">BOM Information</div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label">Variant</span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $bom['variant_id'] ?>">
                        <?= $this->e($bom['variant_sku']) ?>
                    </a>
                    <br><small class="text-muted"><?= $this->e($bom['variant_name']) ?></small>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Version</span>
                <span class="detail-value"><strong><?= $this->e($bom['version']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= $this->e($bom['name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($bom['status']) {
                        'draft' => 'warning',
                        'active' => 'success',
                        'archived' => 'secondary',
                        default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($bom['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Effective Date</span>
                <span class="detail-value"><?= $bom['effective_date'] ? $this->date($bom['effective_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created By</span>
                <span class="detail-value"><?= $this->e($bom['created_by_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created At</span>
                <span class="detail-value"><?= $this->datetime($bom['created_at']) ?></span>
            </div>
            <?php if ($bom['notes']): ?>
            <div class="detail-row">
                <span class="detail-label">Notes</span>
                <span class="detail-value"><?= nl2br($this->e($bom['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card">
        <div class="card-header">Actions</div>
        <div class="card-body">
            <?php if ($bom['status'] === 'draft'): ?>
            <p>This BOM is in draft status. When ready, activate it to use in production.</p>
            <?php if ($this->can('catalog.bom.activate')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/activate" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Activate this BOM? This will archive any currently active BOM for this variant.')">
                    Activate BOM
                </button>
            </form>
            <?php endif; ?>
            <?php elseif ($bom['status'] === 'active'): ?>
            <p>This BOM is currently active and used for production costing.</p>
            <?php if ($this->can('catalog.bom.edit')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/archive">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('Archive this BOM?')">
                    Archive BOM
                </button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <p>This BOM is archived and no longer in use.</p>
            <?php if ($this->can('catalog.bom.activate')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/activate">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Re-activate this BOM?')">
                    Re-Activate
                </button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Materials -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">Materials</div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Name</th>
                        <th class="text-right">Quantity</th>
                        <th>Unit</th>
                        <th class="text-right">Unit Cost</th>
                        <th class="text-right">Waste %</th>
                        <th class="text-right">Total Cost</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lines)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">No materials defined</td>
                    </tr>
                    <?php else: ?>
                    <?php $totalCost = 0; foreach ($lines as $i => $line): ?>
                    <?php
                    $lineCost = $line['quantity'] * $line['unit_cost'] * (1 + $line['waste_percent']/100);
                    $totalCost += $lineCost;
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><a href="/warehouse/items/<?= $line['item_id'] ?>"><?= $this->e($line['sku']) ?></a></td>
                        <td><?= $this->e($line['item_name']) ?></td>
                        <td class="text-right"><?= number_format($line['quantity'], 4) ?></td>
                        <td><?= $this->e($line['unit']) ?></td>
                        <td class="text-right"><?= number_format($line['unit_cost'], 4) ?></td>
                        <td class="text-right"><?= number_format($line['waste_percent'], 2) ?>%</td>
                        <td class="text-right"><?= number_format($lineCost, 2) ?></td>
                        <td><?= $this->e($line['notes'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($lines)): ?>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-right"><strong>Total Material Cost:</strong></td>
                        <td class="text-right"><strong><?= number_format($totalCost, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
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
.text-right { text-align: right; }
</style>

<?php $this->endSection(); ?>
