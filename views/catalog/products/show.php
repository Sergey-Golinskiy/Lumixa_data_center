<?php $this->section('content'); ?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="/catalog/products" class="btn btn-soft-secondary">
                <i class="ri-arrow-left-line me-1"></i><?= $this->__('back_to_list') ?>
            </a>
            <?php if ($this->can('catalog.products.edit')): ?>
            <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-soft-primary">
                <i class="ri-pencil-line me-1"></i><?= $this->__('edit_product') ?>
            </a>
            <?php endif; ?>
            <?php if ($this->can('catalog.products.create')): ?>
            <a href="/catalog/products/<?= $product['id'] ?>/copy" target="_blank" class="btn btn-soft-info" title="<?= $this->__('copy_product') ?>">
                <i class="ri-file-copy-line me-1"></i><?= $this->__('copy') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Product Information -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-information-line me-2"></i><?= $this->__('product_information') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php if (!empty($product['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->e($product['name']) ?>"
                         class="img-fluid rounded" style="max-width: 200px; cursor: pointer;"
                         data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
                    <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 200px; height: 200px;">
                        <span class="text-muted"><?= $this->__('no_photo') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 140px;"><?= $this->__('code') ?></td>
                            <td><strong class="text-primary fs-5"><?= $this->e($product['code'] ?? '') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><?= $this->__('name') ?></td>
                            <td><?= $this->e($product['name'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><?= $this->__('category') ?></td>
                            <td><?= $this->e($product['category_name'] ?? $product['category'] ?? '-') ?></td>
                        </tr>
                        <?php if (!empty($product['collection_name'])): ?>
                        <tr>
                            <td class="text-muted"><?= $this->__('collection') ?></td>
                            <td>
                                <a href="/catalog/products?collection_id=<?= $this->e($product['collection_id']) ?>" target="_blank" class="badge bg-primary-subtle text-primary text-decoration-none">
                                    <?= $this->e($product['collection_name']) ?>
                                    <i class="ri-external-link-line ms-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="text-muted"><?= $this->__('base_price') ?></td>
                            <td><strong class="text-success"><?= $this->currency($product['base_price'] ?? 0) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted"><?= $this->__('status') ?></td>
                            <td>
                                <?php if ($product['is_active'] ?? false): ?>
                                <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($product['website_url'])): ?>
                        <tr>
                            <td class="text-muted"><?= $this->__('website_url') ?></td>
                            <td>
                                <a href="<?= $this->e($product['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-soft-primary">
                                    <i class="ri-external-link-line me-1"></i><?= $this->__('open_in_store') ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($product['description'] ?? false): ?>
                        <tr>
                            <td class="text-muted"><?= $this->__('description') ?></td>
                            <td><?= nl2br($this->e($product['description'])) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Production Cost Summary -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-money-dollar-circle-line me-2"></i><?= $this->__('production_cost') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-dashed">
                    <span class="text-muted"><?= $this->__('details_cost') ?></span>
                    <span class="fw-medium"><?= $this->currency($costData['details_cost'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-dashed">
                    <span class="text-muted"><?= $this->__('components_cost') ?></span>
                    <span class="fw-medium"><?= $this->currency($costData['items_cost'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-dashed">
                    <span class="text-muted"><?= $this->__('labor_cost') ?></span>
                    <span class="fw-medium"><?= $this->currency($costData['labor_cost'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-3 border-bottom border-2">
                    <span class="fw-semibold"><?= $this->__('total_production_cost') ?></span>
                    <span class="fw-bold fs-5"><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-dashed mt-2">
                    <span class="text-muted"><?= $this->__('packaging_cost') ?></span>
                    <span class="fw-medium"><?= $this->currency($costData['packaging_cost'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold"><?= $this->__('total_price') ?></span>
                    <span class="fw-bold fs-5 text-success"><?= $this->currency($costData['total_price'] ?? 0) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Composition / BOM -->
<div class="card mt-3">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-stack-line me-2"></i><?= $this->__('product_composition') ?>
        </h5>
        <span class="badge bg-secondary-subtle text-secondary"><?= count($components ?? []) ?> <?= $this->__('items') ?></span>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.composition')): ?>
        <!-- Add Component Form -->
        <div class="bg-light rounded p-3 mb-3">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components" class="add-component-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label"><?= $this->__('component_type') ?></label>
                        <select name="component_type" id="componentType" class="form-select" required>
                            <option value=""><?= $this->__('select') ?>...</option>
                            <option value="detail"><?= $this->__('detail_printed') ?></option>
                            <option value="item"><?= $this->__('purchased_component') ?></option>
                        </select>
                    </div>
                    <div class="col-md-4" id="detailSelectGroup" style="display:none;">
                        <label class="form-label"><?= $this->__('detail') ?></label>
                        <select name="detail_id" id="detailSelect" class="form-select">
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="itemSelectGroup" style="display:none;">
                        <label class="form-label"><?= $this->__('component') ?></label>
                        <select name="item_id" id="itemSelect" class="form-select">
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('quantity') ?></label>
                        <input type="number" name="quantity" class="form-control" value="1" min="0.0001" step="0.0001" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-add-line me-1"></i><?= $this->__('add') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Composition Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;"><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th><?= $this->__('material') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                        <th class="text-end"><?= $this->__('total_cost') ?></th>
                        <?php if ($this->can('catalog.products.composition')): ?>
                        <th style="width:60px;"><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($components)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.products.composition') ? '9' : '8' ?>" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                            <?= $this->__('no_components') ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($components as $component): ?>
                    <tr>
                        <td>
                            <?php
                            $imagePath = $component['component_type'] === 'detail'
                                ? ($component['detail_image'] ?? '')
                                : ($component['item_image'] ?? '');
                            ?>
                            <?php if (!empty($imagePath)): ?>
                            <img src="/<?= $this->e(ltrim($imagePath, '/')) ?>" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($component['component_type'] === 'detail'): ?>
                            <a href="/catalog/details/<?= $component['detail_id'] ?>" class="fw-semibold text-primary">
                                <?= $this->e($component['detail_sku'] ?? '') ?>
                            </a>
                            <?php else: ?>
                            <a href="/warehouse/items/<?= $component['item_id'] ?>" class="fw-semibold text-primary">
                                <?= $this->e($component['item_sku'] ?? '') ?>
                            </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $this->e($component['component_type'] === 'detail'
                                ? ($component['detail_name'] ?? '')
                                : ($component['item_name'] ?? '')) ?>
                        </td>
                        <td>
                            <?php if ($component['component_type'] === 'detail'): ?>
                            <span class="badge bg-info-subtle text-info"><?= $this->__('detail') ?></span>
                            <?php if (($component['detail_type'] ?? '') === 'printed'): ?>
                            <small class="text-muted">(<?= $this->__('detail_type_printed') ?>)</small>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning"><?= $this->__('component') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($component['component_type'] === 'detail' && !empty($component['detail_materials']) && count($component['detail_materials']) > 1): ?>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($component['detail_materials'] as $dm): ?>
                                <span class="badge bg-primary-subtle text-primary"<?php if (!empty($dm['alias_color'])): ?> style="background: <?= $this->e($dm['alias_color']) ?> !important; color: <?= $this->contrastColor($dm['alias_color']) ?> !important"<?php endif; ?>><?= $this->e($dm['filament_alias'] ?: $dm['material_sku']) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php elseif ($component['component_type'] === 'detail' && !empty($component['material_name'])): ?>
                            <span class="badge bg-primary-subtle text-primary"<?php if (!empty($component['material_alias_color'])): ?> style="background: <?= $this->e($component['material_alias_color']) ?> !important; color: <?= $this->contrastColor($component['material_alias_color']) ?> !important"<?php endif; ?>><?= $this->e($component['material_alias'] ?? $component['material_sku'] ?? '') ?></span>
                            <small class="text-muted d-block"><?= $this->e($component['material_name']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($this->can('catalog.products.composition')): ?>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components/<?= $component['id'] ?>" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <input type="number" name="quantity" value="<?= $this->e($component['quantity']) ?>"
                                       min="0.0001" step="0.0001" class="form-control form-control-sm text-end" style="width:80px;"
                                       onchange="this.form.submit()">
                            </form>
                            <?php else: ?>
                            <?= $this->number($component['quantity'], 4) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= $this->currency($component['calculated_cost'] ?? 0) ?></td>
                        <td class="text-end"><strong><?= $this->currency($component['total_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.composition')): ?>
                        <td>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components/<?= $component['id'] ?>/remove"
                                  style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_component') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-soft-danger">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($components)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="7" class="text-end"><strong><?= $this->__('total_components_cost') ?>:</strong></td>
                        <td class="text-end"><strong><?= $this->currency(($costData['details_cost'] ?? 0) + ($costData['items_cost'] ?? 0)) ?></strong></td>
                        <?php if ($this->can('catalog.products.composition')): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Product Packaging -->
<div class="card mt-3">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-gift-line me-2"></i><?= $this->__('packaging') ?>
        </h5>
        <span class="badge bg-secondary-subtle text-secondary"><?= count($packaging ?? []) ?> <?= $this->__('items') ?></span>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.packaging')): ?>
        <!-- Add Packaging Form -->
        <div class="bg-light rounded p-3 mb-3">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging" class="add-component-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label"><?= $this->__('packaging_item') ?></label>
                        <select name="item_id" id="packagingSelect" class="form-select" required>
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= $this->__('quantity') ?></label>
                        <input type="number" name="quantity" class="form-control" value="1" min="0.0001" step="0.0001" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-add-line me-1"></i><?= $this->__('add') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Packaging Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;"><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('unit') ?></th>
                        <th class="text-end"><?= $this->__('quantity') ?></th>
                        <th class="text-end"><?= $this->__('unit_cost') ?></th>
                        <th class="text-end"><?= $this->__('total_cost') ?></th>
                        <?php if ($this->can('catalog.products.packaging')): ?>
                        <th style="width:60px;"><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packaging)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.products.packaging') ? '8' : '7' ?>" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                            <?= $this->__('no_packaging') ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($packaging as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['item_image'])): ?>
                            <img src="/<?= $this->e(ltrim($item['item_image'], '/')) ?>" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $item['item_id'] ?>" class="fw-semibold text-primary">
                                <?= $this->e($item['item_sku'] ?? '') ?>
                            </a>
                        </td>
                        <td><?= $this->e($item['item_name'] ?? '') ?></td>
                        <td><?= $this->__('unit_' . ($item['unit'] ?? 'pcs')) ?></td>
                        <td class="text-end">
                            <?php if ($this->can('catalog.products.packaging')): ?>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging/<?= $item['id'] ?>" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <input type="number" name="quantity" value="<?= $this->e($item['quantity']) ?>"
                                       min="0.0001" step="0.0001" class="form-control form-control-sm text-end" style="width:80px;"
                                       onchange="this.form.submit()">
                            </form>
                            <?php else: ?>
                            <?= $this->number($item['quantity'], 4) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= $this->currency($item['calculated_cost'] ?? 0) ?></td>
                        <td class="text-end"><strong><?= $this->currency($item['total_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.packaging')): ?>
                        <td>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging/<?= $item['id'] ?>/remove"
                                  style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_packaging') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-soft-danger">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($packaging)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end"><strong><?= $this->__('total_packaging_cost') ?>:</strong></td>
                        <td class="text-end"><strong><?= $this->currency($costData['packaging_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.packaging')): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Product Operations (Routing) -->
<div class="card mt-3">
    <div class="card-header d-flex align-items-center gap-2">
        <h5 class="card-title mb-0 flex-grow-1">
            <i class="ri-route-line me-2"></i><?= $this->__('product_routing') ?>
        </h5>
        <span class="badge bg-secondary-subtle text-secondary"><?= count($operations ?? []) ?> <?= $this->__('operations') ?></span>
        <?php if (($costData['total_time_minutes'] ?? 0) > 0): ?>
        <span class="badge bg-info-subtle text-info"><?= $costData['total_time_minutes'] ?> <?= $this->__('minutes_short') ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.operations')): ?>
        <!-- Add Operation Form -->
        <div class="bg-light rounded p-3 mb-3">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/operations" class="add-operation-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><?= $this->__('operation_name') ?> *</label>
                        <input type="text" name="name" class="form-control" required placeholder="<?= $this->__('operation_name_placeholder') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('time_minutes') ?></label>
                        <input type="number" name="time_minutes" class="form-control" value="0" min="0" step="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                        <input type="number" name="labor_rate" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= $this->__('description') ?></label>
                        <input type="text" name="description" class="form-control" placeholder="<?= $this->__('instructions_placeholder') ?>">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label"><?= $this->__('product_composition') ?></label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input type="checkbox" name="component_ids[]" value="product" class="form-check-input" id="comp_product">
                                <label class="form-check-label" for="comp_product">
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('product') ?></span>
                                    <?= $this->e($product['code'] ?? '') ?>
                                </label>
                            </div>
                            <?php foreach ($components ?? [] as $idx => $comp): ?>
                            <div class="form-check">
                                <input type="checkbox" name="component_ids[]" value="<?= $comp['id'] ?>" class="form-check-input" id="comp_<?= $comp['id'] ?>">
                                <label class="form-check-label" for="comp_<?= $comp['id'] ?>">
                                    <span class="badge bg-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>-subtle text-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>"><?= $comp['component_type'] === 'detail' ? $this->__('detail') : $this->__('component') ?></span>
                                    <?= $this->e($comp['component_type'] === 'detail' ? ($comp['detail_sku'] ?? '') : ($comp['item_sku'] ?? '')) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= $this->__('packaging') ?></label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (empty($packaging)): ?>
                            <span class="text-muted"><?= $this->__('no_packaging') ?></span>
                            <?php else: ?>
                            <?php foreach ($packaging ?? [] as $pack): ?>
                            <div class="form-check">
                                <input type="checkbox" name="component_ids[]" value="packaging_<?= $pack['id'] ?>" class="form-check-input" id="pack_<?= $pack['id'] ?>">
                                <label class="form-check-label" for="pack_<?= $pack['id'] ?>">
                                    <span class="badge bg-warning-subtle text-warning"><?= $this->__('packaging') ?></span>
                                    <?= $this->e($pack['item_sku'] ?? '') ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i><?= $this->__('add_operation') ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Operations Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="operationsTable">
                <thead class="table-light">
                    <tr>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <th style="width:60px;"><?= $this->__('order') ?></th>
                        <?php endif; ?>
                        <th style="width:40px;">#</th>
                        <th><?= $this->__('operation_name') ?></th>
                        <th><?= $this->__('description') ?></th>
                        <th><?= $this->__('components') ?></th>
                        <th class="text-end"><?= $this->__('time_minutes') ?></th>
                        <th class="text-end"><?= $this->__('labor_rate') ?></th>
                        <th class="text-end"><?= $this->__('operation_cost') ?></th>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <th style="width:100px;"><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="operationsBody">
                    <?php if (empty($operations)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.products.operations') ? '9' : '7' ?>" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                            <?= $this->__('no_operations') ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $opNum = 1; $opCount = count($operations); foreach ($operations as $index => $operation): ?>
                    <tr data-operation-id="<?= $operation['id'] ?>" data-sort="<?= $operation['sort_order'] ?? $index ?>">
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
                                <button type="button" class="btn btn-soft-secondary move-up-btn" data-id="<?= $operation['id'] ?>" title="<?= $this->__('move_up') ?>" <?= $index === 0 ? 'disabled' : '' ?>>
                                    <i class="ri-arrow-up-s-line"></i>
                                </button>
                                <button type="button" class="btn btn-soft-secondary move-down-btn" data-id="<?= $operation['id'] ?>" title="<?= $this->__('move_down') ?>" <?= $index === $opCount - 1 ? 'disabled' : '' ?>>
                                    <i class="ri-arrow-down-s-line"></i>
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td class="op-number"><strong class="text-primary"><?= $opNum++ ?></strong></td>
                        <td><strong><?= $this->e($operation['name'] ?? '') ?></strong></td>
                        <td>
                            <?php if (!empty($operation['description'])): ?>
                            <small class="text-muted"><?= nl2br($this->e($operation['description'])) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($operation['components'])): ?>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($operation['components'] as $comp): ?>
                                <span class="badge bg-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>-subtle text-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>">
                                    <?= $this->e($comp['component_type'] === 'detail' ? ($comp['detail_sku'] ?? '') : ($comp['item_sku'] ?? '')) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end"><?= (int)($operation['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-end"><?= $this->currency($operation['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                        <td class="text-end"><strong><?= $this->currency($operation['operation_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-soft-primary edit-operation-btn"
                                        data-id="<?= $operation['id'] ?>"
                                        data-name="<?= $this->e($operation['name'] ?? '') ?>"
                                        data-description="<?= $this->e($operation['description'] ?? '') ?>"
                                        data-time="<?= $operation['time_minutes'] ?? 0 ?>"
                                        data-rate="<?= $operation['labor_rate'] ?? 0 ?>"
                                        data-components="<?= $this->e(implode(',', array_column($operation['components'] ?? [], 'component_id'))) ?>">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <form method="POST" action="/catalog/products/<?= $product['id'] ?>/operations/<?= $operation['id'] ?>/remove"
                                      style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_operation') ?>');">
                                    <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-soft-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($operations)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="<?= $this->can('catalog.products.operations') ? '5' : '4' ?>" class="text-end"><strong><?= $this->__('total_labor_cost') ?>:</strong></td>
                        <td class="text-end"><strong><?= $costData['total_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td></td>
                        <td class="text-end"><strong><?= $this->currency($costData['labor_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <td></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Edit Operation Modal -->
<?php if ($this->can('catalog.products.operations')): ?>
<div class="modal fade" id="editOperationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $this->__('edit_operation') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editOperationForm" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('operation_name') ?> *</label>
                        <input type="text" name="name" id="editOpName" class="form-control" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('time_minutes') ?></label>
                            <input type="number" name="time_minutes" id="editOpTime" class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                            <input type="number" name="labor_rate" id="editOpRate" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $this->__('description') ?></label>
                        <textarea name="description" id="editOpDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('product_composition') ?></label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check">
                                    <input type="checkbox" name="component_ids[]" value="product" class="form-check-input edit-comp-checkbox" id="edit_comp_product">
                                    <label class="form-check-label" for="edit_comp_product">
                                        <span class="badge bg-success-subtle text-success"><?= $this->__('product') ?></span>
                                        <?= $this->e($product['code'] ?? '') ?>
                                    </label>
                                </div>
                                <?php foreach ($components ?? [] as $comp): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="component_ids[]" value="<?= $comp['id'] ?>" class="form-check-input edit-comp-checkbox" id="edit_comp_<?= $comp['id'] ?>">
                                    <label class="form-check-label" for="edit_comp_<?= $comp['id'] ?>">
                                        <span class="badge bg-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>-subtle text-<?= $comp['component_type'] === 'detail' ? 'info' : 'warning' ?>"><?= $comp['component_type'] === 'detail' ? $this->__('detail') : $this->__('component') ?></span>
                                        <?= $this->e($comp['component_type'] === 'detail' ? ($comp['detail_sku'] ?? '') : ($comp['item_sku'] ?? '')) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= $this->__('packaging') ?></label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if (empty($packaging)): ?>
                                <span class="text-muted"><?= $this->__('no_packaging') ?></span>
                                <?php else: ?>
                                <?php foreach ($packaging ?? [] as $pack): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="component_ids[]" value="packaging_<?= $pack['id'] ?>" class="form-check-input edit-comp-checkbox" id="edit_pack_<?= $pack['id'] ?>">
                                    <label class="form-check-label" for="edit_pack_<?= $pack['id'] ?>">
                                        <span class="badge bg-warning-subtle text-warning"><?= $this->__('packaging') ?></span>
                                        <?= $this->e($pack['item_sku'] ?? '') ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal"><?= $this->__('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= $this->__('save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productId = '<?= $product['id'] ?>';
    const csrfToken = '<?= $this->e($csrfToken ?? '') ?>';
    const canEditComposition = <?= $this->can('catalog.products.composition') ? 'true' : 'false' ?>;
    const canEditPackaging = <?= $this->can('catalog.products.packaging') ? 'true' : 'false' ?>;
    const canEditOperations = <?= $this->can('catalog.products.operations') ? 'true' : 'false' ?>;

    function ajaxPost(url, formData) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        }).then(r => r.json());
    }

    // Component type selection
    const componentType = document.getElementById('componentType');
    const detailGroup = document.getElementById('detailSelectGroup');
    const itemGroup = document.getElementById('itemSelectGroup');
    const detailSelect = document.getElementById('detailSelect');
    const itemSelect = document.getElementById('itemSelect');
    let detailsLoaded = false, itemsLoaded = false;

    if (componentType) {
        componentType.addEventListener('change', function() {
            const value = this.value;
            detailGroup.style.display = value === 'detail' ? 'block' : 'none';
            itemGroup.style.display = value === 'item' ? 'block' : 'none';
            if (value === 'detail' && !detailsLoaded) loadDetails();
            if (value === 'item' && !itemsLoaded) loadItems();
        });
    }

    function loadDetails() {
        fetch('/catalog/api/products/details').then(r => r.json()).then(data => {
            if (data.success && data.details) {
                detailSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                data.details.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.sku + ' - ' + d.name;
                    detailSelect.appendChild(opt);
                });
                detailsLoaded = true;
            }
        });
    }

    function loadItems() {
        fetch('/catalog/api/products/items').then(r => r.json()).then(data => {
            if (data.success && data.items) {
                itemSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                data.items.forEach(i => {
                    const opt = document.createElement('option');
                    opt.value = i.id;
                    opt.textContent = i.sku + ' - ' + i.name;
                    itemSelect.appendChild(opt);
                });
                itemsLoaded = true;
            }
        });
    }

    // Load packaging items
    const packagingSelect = document.getElementById('packagingSelect');
    if (packagingSelect) {
        fetch('/catalog/api/products/packaging-items').then(r => r.json()).then(data => {
            if (data.success && data.items) {
                packagingSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                data.items.forEach(i => {
                    const opt = document.createElement('option');
                    opt.value = i.id;
                    opt.textContent = i.sku + ' - ' + i.name;
                    packagingSelect.appendChild(opt);
                });
            }
        });
    }

    // Operation reordering
    function moveOperation(operationId, direction) {
        const url = `/catalog/products/${productId}/operations/${operationId}/move-${direction}`;
        const formData = new FormData();
        formData.append('_csrf_token', csrfToken);
        ajaxPost(url, formData).then(data => {
            if (data.success) location.reload();
        });
    }

    document.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.disabled) moveOperation(this.dataset.id, 'up');
        });
    });

    document.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.disabled) moveOperation(this.dataset.id, 'down');
        });
    });

    // Edit operation modal
    const editModal = document.getElementById('editOperationModal');
    const editForm = document.getElementById('editOperationForm');

    document.querySelectorAll('.edit-operation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            editForm.action = '/catalog/products/<?= $product['id'] ?>/operations/' + id;
            document.getElementById('editOpName').value = this.dataset.name || '';
            document.getElementById('editOpDescription').value = this.dataset.description || '';
            document.getElementById('editOpTime').value = this.dataset.time || 0;
            document.getElementById('editOpRate').value = this.dataset.rate || 0;
            const components = this.dataset.components ? this.dataset.components.split(',') : [];
            document.querySelectorAll('.edit-comp-checkbox').forEach(checkbox => {
                checkbox.checked = components.includes(checkbox.value);
            });
            new bootstrap.Modal(editModal).show();
        });
    });
});
</script>

<?php $this->endSection(); ?>
