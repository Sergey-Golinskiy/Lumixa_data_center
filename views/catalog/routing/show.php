<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/routing" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('routing')]) ?></a>
    <a href="/catalog/variants/<?= $routing['variant_id'] ?>" class="btn btn-outline"><?= $this->__('view_variant') ?></a>
    <?php if ($routing['status'] === 'draft' && $this->can('catalog.routing.edit')): ?>
    <a href="/catalog/routing/<?= $routing['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit') ?></a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Routing Info -->
    <div class="card">
        <div class="card-header"><?= $this->__('routing_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('variant') ?></span>
                <span class="detail-value">
                    <a href="/catalog/variants/<?= $routing['variant_id'] ?>"><?= $this->e($routing['variant_sku']) ?></a>
                    <br><small class="text-muted"><?= $this->e($routing['variant_name']) ?></small>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('version') ?></span>
                <span class="detail-value"><strong><?= $this->e($routing['version']) ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($routing['name'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($routing['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($routing['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php
                    $statusClass = match($routing['status']) {
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
                    <span class="badge badge-<?= $statusClass ?>"><?= $statusLabels[$routing['status']] ?? $this->e($routing['status']) ?></span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('effective_date') ?></span>
                <span class="detail-value"><?= $routing['effective_date'] ? $this->date($routing['effective_date'], 'Y-m-d') : '-' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('created_by') ?></span>
                <span class="detail-value"><?= $this->e($routing['created_by_name'] ?? '-') ?></span>
            </div>
            <?php if ($routing['notes']): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('notes') ?></span>
                <span class="detail-value"><?= nl2br($this->e($routing['notes'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <div class="card">
        <div class="card-header"><?= $this->__('actions') ?></div>
        <div class="card-body">
            <?php if ($routing['status'] === 'draft'): ?>
            <p><?= $this->__('routing_in_draft') ?></p>
            <?php if ($this->can('catalog.routing.activate')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/activate" style="margin-bottom: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('activate_routing_confirm') ?>')"><?= $this->__('activate') ?></button>
            </form>
            <?php endif; ?>
            <?php elseif ($routing['status'] === 'active'): ?>
            <p><?= $this->__('routing_active') ?></p>
            <?php if ($this->can('catalog.routing.edit')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/archive">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('<?= $this->__('archive_routing_confirm') ?>')"><?= $this->__('archive') ?></button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <p><?= $this->__('routing_archived') ?></p>
            <?php if ($this->can('catalog.routing.activate')): ?>
            <form method="POST" action="/catalog/routing/<?= $routing['id'] ?>/activate">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('reactivate_routing_confirm') ?>')"><?= $this->__('reactivate') ?></button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Operations -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('operations') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('operation_number') ?></th>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th class="text-right"><?= $this->__('setup_minutes') ?></th>
                        <th class="text-right"><?= $this->__('run_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_cost') ?></th>
                        <th class="text-right"><?= $this->__('overhead') ?></th>
                        <th class="text-right"><?= $this->__('total') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalSetup = 0; $totalRun = 0; $totalLabor = 0; $totalOverhead = 0;
                    foreach ($operations as $op):
                        $totalSetup += $op['setup_time_minutes'];
                        $totalRun += $op['run_time_minutes'];
                        $totalLabor += $op['labor_cost'];
                        $totalOverhead += $op['overhead_cost'];
                    ?>
                    <tr>
                        <td><?= $op['operation_number'] ?></td>
                        <td>
                            <strong><?= $this->e($op['name']) ?></strong>
                            <?php if ($op['instructions']): ?>
                            <br><small class="text-muted"><?= $this->e(substr($op['instructions'], 0, 100)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td class="text-right"><?= number_format($op['setup_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'], 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'], 2) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] + $op['overhead_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong><?= $this->__('totals') ?>:</strong></td>
                        <td class="text-right"><strong><?= number_format($totalSetup) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalRun) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalLabor, 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalOverhead, 2) ?></strong></td>
                        <td class="text-right"><strong><?= number_format($totalLabor + $totalOverhead, 2) ?></strong></td>
                    </tr>
                </tfoot>
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
