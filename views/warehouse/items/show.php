<?php $this->section('content'); ?>

<!-- Action buttons -->
<div class="mb-3 d-flex gap-2">
    <a href="/warehouse/items" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line me-1"></i> <?= $this->__('back_to_list') ?>
    </a>
    <?php if ($this->can('warehouse.items.edit')): ?>
    <a href="/warehouse/items/<?= $item['id'] ?>/edit" class="btn btn-primary">
        <i class="ri-pencil-line me-1"></i> <?= $this->__('edit_item') ?>
    </a>
    <?php endif; ?>
</div>

<div class="row">
    <!-- General Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-information-line me-2"></i><?= $this->__('details') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($item['image_path'])): ?>
                <div class="text-center mb-4">
                    <div class="avatar-xl bg-light rounded p-2 mx-auto">
                        <img src="/<?= $this->e(ltrim($item['image_path'], '/')) ?>" alt="" class="img-fluid rounded" style="cursor:pointer" data-image-preview="/<?= $this->e(ltrim($item['image_path'], '/')) ?>">
                    </div>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 35%;"><?= $this->__('sku') ?></th>
                                <td class="text-primary fw-semibold"><?= $this->e($item['sku'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('name') ?></th>
                                <td><?= $this->e($item['name'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('type') ?></th>
                                <td>
                                    <span class="badge badge-type-<?= $this->e($item['type'] ?? 'material') ?>">
                                        <?= $this->__('item_type_' . ($item['type'] ?? 'material')) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('unit') ?></th>
                                <td><?= $this->__('unit_' . ($item['unit'] ?? 'pcs')) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('status') ?></th>
                                <td>
                                    <?php if ($item['is_active'] ?? false): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($item['description'] ?? false): ?>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('description') ?></th>
                                <td><?= nl2br($this->e($item['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-settings-4-line me-2"></i><?= $this->__('settings') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0 text-muted" style="width: 50%;"><?= $this->__('min_stock') ?></th>
                                <td><?= $this->number($item['min_stock'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('reorder_point') ?></th>
                                <td><?= $this->number($item['reorder_point'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('created_at') ?></th>
                                <td><?= $this->date($item['created_at'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0 text-muted"><?= $this->__('updated_at') ?></th>
                                <td><?= $this->date($item['updated_at'] ?? '') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Stock Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-stack-line me-2"></i><?= $this->__('stock') ?></h5>
            </div>
            <div class="card-body">
                <?php
                $totalOnHand = 0;
                $totalReserved = 0;
                foreach ($stock ?? [] as $s) {
                    $totalOnHand += $s['on_hand'] ?? 0;
                    $totalReserved += $s['reserved'] ?? 0;
                }
                $totalAvailable = $totalOnHand - $totalReserved;
                ?>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="py-3">
                            <h2 class="mb-1 ff-secondary"><?= $this->number($totalOnHand) ?></h2>
                            <p class="text-muted mb-0"><?= $this->__('on_hand') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 border-start">
                        <div class="py-3">
                            <h2 class="mb-1 ff-secondary text-warning"><?= $this->number($totalReserved) ?></h2>
                            <p class="text-muted mb-0"><?= $this->__('reserved') ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 border-start">
                        <div class="py-3">
                            <h2 class="mb-1 ff-secondary <?= $totalAvailable < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= $this->number($totalAvailable) ?>
                            </h2>
                            <p class="text-muted mb-0"><?= $this->__('available') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attributes -->
        <?php if (!empty($attributes)): ?>
        <?php
            $materialAttributeOrder = [
                'material' => $this->__('material'),
                'manufacturer' => $this->__('manufacturer'),
                'plastic_type' => $this->__('plastic_type'),
                'filament_color' => $this->__('filament_color'),
                'filament_diameter' => $this->__('filament_diameter'),
                'filament_alias' => $this->__('filament_alias')
            ];
            $defaultAttributeOrder = [
                'color' => $this->__('color'),
                'diameter' => $this->__('diameter'),
                'brand' => $this->__('brand')
            ];
            $orderedAttributes = $item['type'] === 'material'
                ? $materialAttributeOrder
                : $defaultAttributeOrder;
            $displayAttributes = array_filter(
                $orderedAttributes,
                fn($label, $name) => !empty($attributes[$name]),
                ARRAY_FILTER_USE_BOTH
            );
        ?>
        <?php if (!empty($displayAttributes)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-flask-line me-2"></i>
                    <?= $item['type'] === 'material' ? $this->__('attributes_materials') : $this->__('attributes') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($displayAttributes as $name => $label): ?>
                    <?php if (!empty($attributes[$name])): ?>
                    <div class="col-md-4 mb-3">
                        <p class="text-muted mb-1"><?= $this->e($label) ?></p>
                        <h6><?= $this->e($attributes[$name]) ?></h6>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Movement History -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i><?= $this->__('stock_movements') ?></h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($history)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                    <?= $this->__('no_results') ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('date') ?></th>
                                <th><?= $this->__('document') ?></th>
                                <th><?= $this->__('type') ?></th>
                                <th class="text-end"><?= $this->__('quantity') ?></th>
                                <th class="text-end"><?= $this->__('total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                            <tr>
                                <td><?= $this->date($h['created_at'] ?? '') ?></td>
                                <td>
                                    <a href="/warehouse/documents/<?= $h['document_id'] ?? '' ?>" class="text-primary">
                                        <?= $this->e($h['document_number'] ?? '') ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (($h['movement_type'] ?? '') === 'in'): ?>
                                    <span class="badge bg-success-subtle text-success">IN</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning">OUT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (($h['movement_type'] ?? '') === 'in'): ?>
                                    <span class="text-success">+<?= $this->number($h['quantity'] ?? 0) ?></span>
                                    <?php else: ?>
                                    <span class="text-danger">-<?= $this->number($h['quantity'] ?? 0) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-medium"><?= $this->number($h['balance_after'] ?? 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
