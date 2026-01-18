<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('printers') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('admin.printers.create')): ?>
        <a href="/admin/printers/create" class="btn btn-primary">+ <?= $this->__('create_printer') ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('model') ?></th>
                        <th class="text-right"><?= $this->__('power_watts') ?></th>
                        <th class="text-right"><?= $this->__('electricity_cost_per_kwh') ?></th>
                        <th class="text-right"><?= $this->__('amortization_per_hour') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($printers)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($printers as $printer): ?>
                    <tr>
                        <td><strong><?= $this->e($printer['name']) ?></strong></td>
                        <td><?= $this->e($printer['model'] ?? '-') ?></td>
                        <td class="text-right"><?= $this->e($printer['power_watts'] ?? 0) ?></td>
                        <td class="text-right"><?= number_format($printer['electricity_cost_per_kwh'] ?? 0, 4) ?></td>
                        <td class="text-right"><?= number_format($printer['amortization_per_hour'] ?? 0, 2) ?></td>
                        <td>
                            <?php if ($printer['is_active']): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->can('admin.printers.edit')): ?>
                            <a href="/admin/printers/<?= $printer['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                            <?php if ($this->can('admin.printers.delete')): ?>
                            <form method="POST" action="/admin/printers/<?= $printer['id'] ?>/delete" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= $this->__('confirm_delete_printer') ?>')">
                                    <?= $this->__('delete') ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
