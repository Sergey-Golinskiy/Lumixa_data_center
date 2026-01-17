<?php $this->section('content'); ?>

<a href="/catalog/detail-routing" class="btn btn-secondary">&laquo; <?= $this->__('back_to', ['name' => $this->__('detail_routing')]) ?></a>
<a href="/catalog/details/<?= $routing['detail_id'] ?>" class="btn btn-outline"><?= $this->__('view_detail') ?></a>
<?php if ($routing['status'] === 'draft' && $this->can('catalog.detail_routing.edit')): ?>
<a href="/catalog/detail-routing/<?= $routing['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit') ?></a>
<?php endif; ?>

<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('detail_routing_information') ?></div>
    <div class="card-body">
        <div class="detail-row">
            <span class="detail-label"><?= $this->__('detail') ?></span>
            <span class="detail-value">
                <a href="/catalog/details/<?= $routing['detail_id'] ?>">
                    <?= $this->e($routing['detail_sku']) ?>
                </a>
                <br><small class="text-muted"><?= $this->e($routing['detail_name']) ?></small>
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
                ?>
                <span class="badge badge-<?= $statusClass ?>"><?= $this->__('detail_routing_status_' . $routing['status']) ?></span>
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

<div class="card" style="margin-top: 20px;">
    <div class="card-header"><?= $this->__('operations') ?></div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('operation') ?></th>
                        <th><?= $this->__('work_center') ?></th>
                        <th><?= $this->__('setup_time_minutes') ?></th>
                        <th><?= $this->__('run_time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_cost') ?></th>
                        <th class="text-right"><?= $this->__('overhead_cost') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($operations as $op): ?>
                    <tr>
                        <td><?= $this->e($op['operation_number']) ?> - <?= $this->e($op['name']) ?></td>
                        <td><?= $this->e($op['work_center'] ?? '-') ?></td>
                        <td><?= $this->e($op['setup_time_minutes']) ?></td>
                        <td><?= $this->e($op['run_time_minutes']) ?></td>
                        <td class="text-right"><?= number_format($op['labor_cost'] ?? 0, 2) ?></td>
                        <td class="text-right"><?= number_format($op['overhead_cost'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($routing['status'] === 'draft'): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-body">
        <p><?= $this->__('routing_in_draft') ?></p>
        <?php if ($this->can('catalog.detail_routing.activate')): ?>
        <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/activate" style="margin-bottom: 10px;">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('activate_routing_confirm') ?>')"><?= $this->__('activate') ?></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php elseif ($routing['status'] === 'active'): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-body">
        <p><?= $this->__('routing_active') ?></p>
        <?php if ($this->can('catalog.detail_routing.edit')): ?>
        <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/archive">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <button type="submit" class="btn btn-warning" onclick="return confirm('<?= $this->__('archive_routing_confirm') ?>')"><?= $this->__('archive') ?></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-body">
        <p><?= $this->__('routing_archived') ?></p>
        <?php if ($this->can('catalog.detail_routing.activate')): ?>
        <form method="POST" action="/catalog/detail-routing/<?= $routing['id'] ?>/activate">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
            <button type="submit" class="btn btn-success" onclick="return confirm('<?= $this->__('reactivate_routing_confirm') ?>')"><?= $this->__('reactivate') ?></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

