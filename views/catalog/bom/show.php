<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/bom" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('bom_list')]) ?></a>
    <a href="/catalog/variants/<?= $bom['variant_id'] ?>" class="btn btn-outline"><?= $this->__('view_variant') ?></a>
    <?php if ($bom['status'] === 'draft' && $this->can('catalog.bom.edit')): ?>
    <a href="/catalog/bom/<?= $bom['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- BOM Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('bom_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('variant') ?></span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $bom['variant_id'] ?>">
                        <?= $this->e($bom['variant_sku']) ?>
                    </a>
                    <br><small class="text-muted"><?= $this->e($bom['variant_name']) ?></small>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('version') ?></span>
                <span class="detail-value"><strong><?= $this->e($bom['version']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($bom['name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($bom['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($bom['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($bom['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($bom['status']) {
                        'draft' => 'warning',
                        'active' => 'success',
                        'archived' => 'secondary',
                        default => 'secondary'
                    };
                    $statusLabels = [
                        'draft' => $this->__('draft'),
                        'active' => $this->__('active'),
                        'archived' => $this->__('archived')
                    ];
                    ?>
                    <span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$bom['status']] ?? $this->e($bom['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('effective_date') ?></span>
                <span class="detail-value"><?= $bom['effective_date'] ? $this->date($bom['effective_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_by') ?></span>
                <span class="detail-value"><?= $this->e($bom['created_by_name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_at') ?></span>
                <span class="detail-value"><?= $this->datetime($bom['created_at']) ?></span>
            </div>
            <?php if ($bom['notes']): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('notes') ?></span>
                <span class="detail-value"><?= nl2br($this->e($bom['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card">
        <div class="card-header"><?= $this->__('actions') ?></div>
        <div class="card-body">
            <?php if ($bom['status'] === 'draft'): ?>
            <p><?= $this->__('bom_draft_message') ?></p>
            <?php if ($this->can('catalog.bom.activate')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/activate" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('activate_bom_confirm') ?>')">
                    <?= $this->__('activate_bom') ?>
                </button>
            </form>
            <?php endif; ?>
            <?php elseif ($bom['status'] === 'active'): ?>
            <p><?= $this->__('bom_active_message') ?></p>
            <?php if ($this->can('catalog.bom.edit')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/archive">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('<?= $this->__('archive_bom_confirm') ?>')">
                    <?= $this->__('archive_bom') ?>
                </button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <p><?= $this->__('bom_archived_message') ?></p>
            <?php if ($this->can('catalog.bom.activate')): ?>
            <form method="POST" action="/catalog/bom/<?= $bom['id'] ?>/activate">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('reactivate_bom_confirm') ?>')">
                    <?= $this->__('reactivate') ?>
                </button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Materials -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('materials') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= $this->__('item') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('configuration') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th><?= $this->__('unit') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                        <th class="text-right"><?= $this->__('waste_percent') ?></th>
                        <th class="text-right"><?= $this->__('total_cost') ?></th>
                        <th><?= $this->__('notes') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lines)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted"><?= $this->__('no_materials_defined') ?></td>
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
                        <td>
                            <?php if (!empty($line['config_id'])): ?>
                            <div style="font-size: 12px;">
                                <strong><?= $this->e($line['config_sku']) ?></strong><br>
                                <span class="text-muted"><?= $this->e($line['config_name']) ?></span>
                                <?php if ($line['material_color']): ?>
                                <br><span class="text-muted"><?= $this->e($line['material_color']) ?></span>
                                <?php endif; ?>
                                <?php if ($line['material_name']): ?>
                                <br><small class="text-muted">(<?= $this->e($line['material_name']) ?>)</small>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
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
                        <td colspan="8" class="text-right"><strong><?= $this->__('total_material_cost') ?>:</strong></td>
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
