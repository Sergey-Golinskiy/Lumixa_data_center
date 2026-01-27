<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <?php if ($this->can('catalog.products.edit')): ?>
    <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_product') ?></a>
    <?php endif; ?>
    <?php if ($this->can('catalog.products.create')): ?>
    <a href="/catalog/products/<?= $product['id'] ?>/copy" target="_blank" class="btn btn-outline" title="<?= $this->__('copy_product') ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <?= $this->__('copy') ?>
    </a>
    <?php endif; ?>
</div>

<div class="detail-grid">
    <!-- Product Information -->
    <div class="card">
        <div class="card-header"><?= $this->__('product_information') ?></div>
        <div class="card-body">
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('photo') ?></span>
                <span class="detail-value">
                    <?php if (!empty($product['image_path'])): ?>
                    <img src="/<?= $this->e(ltrim($product['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" style="width:100px;height:100px;" data-image-preview="/<?= $this->e(ltrim($product['image_path'], '/')) ?>">
                    <?php else: ?>
                    <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('code') ?></span>
                <span class="detail-value"><strong><?= $this->e($product['code'] ?? '') ?></strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('name') ?></span>
                <span class="detail-value"><?= $this->e($product['name'] ?? '') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('category') ?></span>
                <span class="detail-value"><?= $this->e($product['category_name'] ?? $product['category'] ?? '-') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('base_price') ?></span>
                <span class="detail-value"><?= $this->currency($product['base_price'] ?? 0) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('status') ?></span>
                <span class="detail-value">
                    <?php if ($product['is_active'] ?? false): ?>
                    <span class="badge badge-success"><?= $this->__('active') ?></span>
                    <?php else: ?>
                    <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($product['website_url'])): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('website_url') ?></span>
                <span class="detail-value">
                    <a href="<?= $this->e($product['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                        <?= $this->__('open_in_store') ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            <?php if ($product['description'] ?? false): ?>
            <div class="detail-row">
                <span class="detail-label"><?= $this->__('description') ?></span>
                <span class="detail-value"><?= nl2br($this->e($product['description'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Production Cost Summary -->
    <div class="card">
        <div class="card-header"><?= $this->__('production_cost') ?></div>
        <div class="card-body">
            <div class="cost-summary">
                <div class="cost-summary-item">
                    <span class="cost-label"><?= $this->__('details_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['details_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-item">
                    <span class="cost-label"><?= $this->__('components_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['items_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-item">
                    <span class="cost-label"><?= $this->__('labor_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['labor_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-item">
                    <span class="cost-label"><?= $this->__('assembly_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['assembly_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-total">
                    <span class="cost-label"><?= $this->__('total_production_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-item" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border);">
                    <span class="cost-label"><?= $this->__('packaging_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['packaging_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-total">
                    <span class="cost-label"><?= $this->__('total_price') ?></span>
                    <span class="cost-value" style="color: var(--success);"><?= $this->currency($costData['total_price'] ?? 0) ?></span>
                </div>
            </div>

            <?php if ($this->can('catalog.products.composition')): ?>
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/assembly-cost" class="assembly-cost-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                <div class="form-inline">
                    <label><?= $this->__('assembly_cost') ?>:</label>
                    <input type="number" name="assembly_cost" step="0.01" min="0"
                           value="<?= $this->e($product['assembly_cost'] ?? 0) ?>" style="width:120px;">
                    <button type="submit" class="btn btn-sm btn-secondary"><?= $this->__('update') ?></button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Composition / BOM -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('product_composition') ?>
        <span class="badge badge-secondary"><?= count($components ?? []) ?> <?= $this->__('items') ?></span>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.composition')): ?>
        <!-- Add Component Form -->
        <div class="add-component-section">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components" class="add-component-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label><?= $this->__('component_type') ?></label>
                        <select name="component_type" id="componentType" required>
                            <option value=""><?= $this->__('select') ?>...</option>
                            <option value="detail"><?= $this->__('detail_printed') ?></option>
                            <option value="item"><?= $this->__('purchased_component') ?></option>
                        </select>
                    </div>

                    <div class="form-group" id="detailSelectGroup" style="display:none;">
                        <label><?= $this->__('detail') ?></label>
                        <select name="detail_id" id="detailSelect">
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>

                    <div class="form-group" id="itemSelectGroup" style="display:none;">
                        <label><?= $this->__('component') ?></label>
                        <select name="item_id" id="itemSelect">
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?= $this->__('quantity') ?></label>
                        <input type="number" name="quantity" value="1" min="0.0001" step="0.0001" style="width:100px;" required>
                    </div>

                    <div class="form-group" style="align-self:flex-end;">
                        <button type="submit" class="btn btn-primary"><?= $this->__('add') ?></button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Composition Table -->
        <div class="table-container" style="margin-top:20px;">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;"><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('type') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                        <th class="text-right"><?= $this->__('total_cost') ?></th>
                        <?php if ($this->can('catalog.products.composition')): ?>
                        <th><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($components)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_components') ?></td>
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
                            <img src="/<?= $this->e(ltrim($imagePath, '/')) ?>" alt="" class="image-thumb-sm">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($component['component_type'] === 'detail'): ?>
                            <a href="/catalog/details/<?= $component['detail_id'] ?>">
                                <strong><?= $this->e($component['detail_sku'] ?? '') ?></strong>
                            </a>
                            <?php else: ?>
                            <a href="/warehouse/items/<?= $component['item_id'] ?>">
                                <strong><?= $this->e($component['item_sku'] ?? '') ?></strong>
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
                            <span class="badge badge-info"><?= $this->__('detail') ?></span>
                            <?php if (($component['detail_type'] ?? '') === 'printed'): ?>
                            <small class="text-muted">(<?= $this->__('detail_type_printed') ?>)</small>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('purchased') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <?php if ($this->can('catalog.products.composition')): ?>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components/<?= $component['id'] ?>" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <input type="number" name="quantity" value="<?= $this->e($component['quantity']) ?>"
                                       min="0.0001" step="0.0001" style="width:80px;text-align:right;"
                                       onchange="this.form.submit()">
                            </form>
                            <?php else: ?>
                            <?= $this->number($component['quantity'], 4) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= $this->currency($component['calculated_cost'] ?? 0) ?></td>
                        <td class="text-right"><strong><?= $this->currency($component['total_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.composition')): ?>
                        <td>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/components/<?= $component['id'] ?>/remove"
                                  style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_component') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-danger">&times;</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($components)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" class="text-right"><strong><?= $this->__('total_components_cost') ?>:</strong></td>
                        <td class="text-right"><strong><?= $this->currency(($costData['details_cost'] ?? 0) + ($costData['items_cost'] ?? 0)) ?></strong></td>
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
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('packaging') ?>
        <span class="badge badge-secondary"><?= count($packaging ?? []) ?> <?= $this->__('items') ?></span>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.packaging')): ?>
        <!-- Add Packaging Form -->
        <div class="add-component-section">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging" class="add-component-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label><?= $this->__('packaging_item') ?></label>
                        <select name="item_id" id="packagingSelect" required>
                            <option value=""><?= $this->__('select') ?>...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?= $this->__('quantity') ?></label>
                        <input type="number" name="quantity" value="1" min="0.0001" step="0.0001" style="width:100px;" required>
                    </div>

                    <div class="form-group" style="align-self:flex-end;">
                        <button type="submit" class="btn btn-primary"><?= $this->__('add') ?></button>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Packaging Table -->
        <div class="table-container" style="margin-top:20px;">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;"><?= $this->__('photo') ?></th>
                        <th><?= $this->__('sku') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th><?= $this->__('unit') ?></th>
                        <th class="text-right"><?= $this->__('quantity') ?></th>
                        <th class="text-right"><?= $this->__('unit_cost') ?></th>
                        <th class="text-right"><?= $this->__('total_cost') ?></th>
                        <?php if ($this->can('catalog.products.packaging')): ?>
                        <th><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packaging)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted"><?= $this->__('no_packaging') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($packaging as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['item_image'])): ?>
                            <img src="/<?= $this->e(ltrim($item['item_image'], '/')) ?>" alt="" class="image-thumb-sm">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/warehouse/items/<?= $item['item_id'] ?>">
                                <strong><?= $this->e($item['item_sku'] ?? '') ?></strong>
                            </a>
                        </td>
                        <td><?= $this->e($item['item_name'] ?? '') ?></td>
                        <td><?= $this->e($item['unit'] ?? 'pcs') ?></td>
                        <td class="text-right">
                            <?php if ($this->can('catalog.products.packaging')): ?>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging/<?= $item['id'] ?>" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <input type="number" name="quantity" value="<?= $this->e($item['quantity']) ?>"
                                       min="0.0001" step="0.0001" style="width:80px;text-align:right;"
                                       onchange="this.form.submit()">
                            </form>
                            <?php else: ?>
                            <?= $this->number($item['quantity'], 4) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= $this->currency($item['calculated_cost'] ?? 0) ?></td>
                        <td class="text-right"><strong><?= $this->currency($item['total_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.packaging')): ?>
                        <td>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/packaging/<?= $item['id'] ?>/remove"
                                  style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_packaging') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-danger">&times;</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($packaging)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" class="text-right"><strong><?= $this->__('total_packaging_cost') ?>:</strong></td>
                        <td class="text-right"><strong><?= $this->currency($costData['packaging_cost'] ?? 0) ?></strong></td>
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
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <?= $this->__('product_routing') ?>
        <span class="badge badge-secondary"><?= count($operations ?? []) ?> <?= $this->__('operations') ?></span>
        <?php if (($costData['total_time_minutes'] ?? 0) > 0): ?>
        <span class="badge badge-info"><?= $costData['total_time_minutes'] ?> <?= $this->__('minutes_short') ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($this->can('catalog.products.operations')): ?>
        <!-- Add Operation Form - Two Column Layout -->
        <div class="add-component-section">
            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/operations" class="add-operation-form">
                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">

                <div class="operation-form-columns">
                    <!-- Left Column: Name, Time, Rate -->
                    <div class="operation-form-left">
                        <div class="form-group">
                            <label><?= $this->__('operation_name') ?> *</label>
                            <input type="text" name="name" required placeholder="<?= $this->__('operation_name_placeholder') ?>">
                        </div>

                        <div class="form-row-inline">
                            <div class="form-group">
                                <label><?= $this->__('time_minutes') ?></label>
                                <input type="number" name="time_minutes" value="0" min="0" step="1">
                            </div>

                            <div class="form-group">
                                <label><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                                <input type="number" name="labor_rate" value="0" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Description -->
                    <div class="operation-form-right">
                        <div class="form-group" style="flex: 1;">
                            <label><?= $this->__('description') ?></label>
                            <textarea name="description" rows="3" placeholder="<?= $this->__('instructions_placeholder') ?>"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Components Selection with Checkboxes - Two Columns -->
                <div class="components-selection-container" style="margin-top: 15px;">
                    <div class="components-two-columns">
                        <!-- Left Column: Components (Details & Items) -->
                        <div class="components-column">
                            <label class="column-label"><?= $this->__('product_composition') ?></label>
                            <div class="components-list">
                                <!-- Product itself (assembled) -->
                                <label class="component-checkbox product-item">
                                    <input type="checkbox" name="component_ids[]" value="product">
                                    <span class="component-info">
                                        <span class="badge badge-success"><?= $this->__('product') ?></span>
                                        <strong><?= $this->e($product['code'] ?? '') ?></strong>
                                        <span class="component-name"><?= $this->e($product['name'] ?? '') ?></span>
                                    </span>
                                </label>
                                <?php foreach ($components ?? [] as $comp): ?>
                                <label class="component-checkbox">
                                    <input type="checkbox" name="component_ids[]" value="<?= $comp['id'] ?>">
                                    <span class="component-info">
                                        <span class="badge badge-<?= $comp['component_type'] === 'detail' ? 'info' : 'component' ?>">
                                            <?= $comp['component_type'] === 'detail' ? $this->__('detail') : $this->__('component') ?>
                                        </span>
                                        <strong><?= $this->e($comp['component_type'] === 'detail'
                                            ? ($comp['detail_sku'] ?? '')
                                            : ($comp['item_sku'] ?? '')) ?></strong>
                                        <span class="component-name"><?= $this->e($comp['component_type'] === 'detail'
                                            ? ($comp['detail_name'] ?? '')
                                            : ($comp['item_name'] ?? '')) ?></span>
                                        <?php if ($comp['component_type'] === 'detail' && !empty($comp['material_name'])): ?>
                                        <span class="component-material">
                                            <small class="badge badge-outline"><?= $this->e($comp['material_alias'] ?? $comp['material_name'] ?? '') ?></small>
                                        </span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Right Column: Packaging -->
                        <div class="components-column">
                            <label class="column-label"><?= $this->__('packaging') ?></label>
                            <div class="components-list">
                                <?php if (empty($packaging)): ?>
                                <div class="no-items-hint"><?= $this->__('no_packaging') ?></div>
                                <?php else: ?>
                                <?php foreach ($packaging ?? [] as $pack): ?>
                                <label class="component-checkbox packaging-item">
                                    <input type="checkbox" name="component_ids[]" value="packaging_<?= $pack['id'] ?>">
                                    <span class="component-info">
                                        <span class="badge badge-warning"><?= $this->__('packaging') ?></span>
                                        <strong><?= $this->e($pack['item_sku'] ?? '') ?></strong>
                                        <span class="component-name"><?= $this->e($pack['item_name'] ?? '') ?></span>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Button at Bottom -->
                <div class="form-actions-center" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        <?= $this->__('add_operation') ?>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Operations Table -->
        <div class="table-container" style="margin-top:20px;">
            <table id="operationsTable">
                <thead>
                    <tr>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <th style="width:60px;"><?= $this->__('order') ?></th>
                        <?php endif; ?>
                        <th style="width:40px;">#</th>
                        <th><?= $this->__('operation_name') ?></th>
                        <th><?= $this->__('description') ?></th>
                        <th><?= $this->__('components') ?></th>
                        <th class="text-right"><?= $this->__('time_minutes') ?></th>
                        <th class="text-right"><?= $this->__('labor_rate') ?></th>
                        <th class="text-right"><?= $this->__('operation_cost') ?></th>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <th><?= $this->__('actions') ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="operationsBody">
                    <?php if (empty($operations)): ?>
                    <tr>
                        <td colspan="<?= $this->can('catalog.products.operations') ? '10' : '8' ?>" class="text-center text-muted"><?= $this->__('no_operations') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php $opNum = 1; $opCount = count($operations); foreach ($operations as $index => $operation): ?>
                    <tr data-operation-id="<?= $operation['id'] ?>" data-sort="<?= $operation['sort_order'] ?? $index ?>">
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <td class="reorder-cell">
                            <div class="reorder-buttons">
                                <button type="button" class="btn btn-sm btn-outline reorder-btn move-up-btn"
                                        data-id="<?= $operation['id'] ?>" title="<?= $this->__('move_up') ?>"
                                        <?= $index === 0 ? 'disabled style="opacity: 0.3;"' : '' ?>>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 15l-6-6-6 6"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline reorder-btn move-down-btn"
                                        data-id="<?= $operation['id'] ?>" title="<?= $this->__('move_down') ?>"
                                        <?= $index === $opCount - 1 ? 'disabled style="opacity: 0.3;"' : '' ?>>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                        <td class="op-number"><strong><?= $opNum++ ?></strong></td>
                        <td><strong><?= $this->e($operation['name'] ?? '') ?></strong></td>
                        <td>
                            <?php if (!empty($operation['description'])): ?>
                            <small><?= nl2br($this->e($operation['description'])) ?></small>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($operation['components'])): ?>
                            <div class="operation-components">
                                <?php foreach ($operation['components'] as $comp): ?>
                                <span class="badge badge-<?= $comp['component_type'] === 'detail' ? 'info' : 'component' ?>" style="margin: 2px;">
                                    <?= $this->e($comp['component_type'] === 'detail'
                                        ? ($comp['detail_sku'] ?? '')
                                        : ($comp['item_sku'] ?? '')) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= (int)($operation['time_minutes'] ?? 0) ?> <?= $this->__('minutes_short') ?></td>
                        <td class="text-right"><?= $this->currency($operation['labor_rate'] ?? 0) ?>/<?= $this->__('hour_short') ?></td>
                        <td class="text-right"><strong><?= $this->currency($operation['operation_cost'] ?? 0) ?></strong></td>
                        <?php if ($this->can('catalog.products.operations')): ?>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline edit-operation-btn"
                                    data-id="<?= $operation['id'] ?>"
                                    data-name="<?= $this->e($operation['name'] ?? '') ?>"
                                    data-description="<?= $this->e($operation['description'] ?? '') ?>"
                                    data-time="<?= $operation['time_minutes'] ?? 0 ?>"
                                    data-rate="<?= $operation['labor_rate'] ?? 0 ?>"
                                    data-components="<?= $this->e(implode(',', array_column($operation['components'] ?? [], 'component_id'))) ?>">
                                <?= $this->__('edit') ?>
                            </button>
                            <form method="POST" action="/catalog/products/<?= $product['id'] ?>/operations/<?= $operation['id'] ?>/remove"
                                  style="display:inline;" onsubmit="return confirm('<?= $this->__('confirm_remove_operation') ?>');">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-danger">&times;</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($operations)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="<?= $this->can('catalog.products.operations') ? '5' : '4' ?>" class="text-right"><strong><?= $this->__('total_labor_cost') ?>:</strong></td>
                        <td class="text-right"><strong><?= $costData['total_time_minutes'] ?? 0 ?> <?= $this->__('minutes_short') ?></strong></td>
                        <td></td>
                        <td class="text-right"><strong><?= $this->currency($costData['labor_cost'] ?? 0) ?></strong></td>
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
<div id="editOperationModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= $this->__('edit_operation') ?></h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <form id="editOperationForm" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken ?? '') ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label><?= $this->__('operation_name') ?> *</label>
                    <input type="text" name="name" id="editOpName" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $this->__('time_minutes') ?></label>
                        <input type="number" name="time_minutes" id="editOpTime" min="0" step="1">
                    </div>
                    <div class="form-group">
                        <label><?= $this->__('labor_rate') ?> (<?= $this->__('hour_short') ?>)</label>
                        <input type="number" name="labor_rate" id="editOpRate" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $this->__('description') ?></label>
                    <textarea name="description" id="editOpDescription" rows="3"></textarea>
                </div>
                <!-- Components Selection with Checkboxes - Two Columns -->
                <div class="components-selection-container">
                    <div class="components-two-columns">
                        <!-- Left Column: Components -->
                        <div class="components-column">
                            <label class="column-label"><?= $this->__('product_composition') ?></label>
                            <div class="components-list">
                                <label class="component-checkbox product-item">
                                    <input type="checkbox" name="component_ids[]" value="product" class="edit-comp-checkbox">
                                    <span class="component-info">
                                        <span class="badge badge-success"><?= $this->__('product') ?></span>
                                        <strong><?= $this->e($product['code'] ?? '') ?></strong>
                                        <span class="component-name"><?= $this->e($product['name'] ?? '') ?></span>
                                    </span>
                                </label>
                                <?php foreach ($components ?? [] as $comp): ?>
                                <label class="component-checkbox">
                                    <input type="checkbox" name="component_ids[]" value="<?= $comp['id'] ?>" class="edit-comp-checkbox">
                                    <span class="component-info">
                                        <span class="badge badge-<?= $comp['component_type'] === 'detail' ? 'info' : 'secondary' ?>">
                                            <?= $comp['component_type'] === 'detail' ? $this->__('detail') : $this->__('component') ?>
                                        </span>
                                        <strong><?= $this->e($comp['component_type'] === 'detail'
                                            ? ($comp['detail_sku'] ?? '')
                                            : ($comp['item_sku'] ?? '')) ?></strong>
                                        <span class="component-name"><?= $this->e($comp['component_type'] === 'detail'
                                            ? ($comp['detail_name'] ?? '')
                                            : ($comp['item_name'] ?? '')) ?></span>
                                        <?php if ($comp['component_type'] === 'detail' && !empty($comp['material_name'])): ?>
                                        <span class="component-material">
                                            <small class="badge badge-outline"><?= $this->e($comp['material_alias'] ?? $comp['material_name'] ?? '') ?></small>
                                        </span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Right Column: Packaging -->
                        <div class="components-column">
                            <label class="column-label"><?= $this->__('packaging') ?></label>
                            <div class="components-list">
                                <?php if (empty($packaging)): ?>
                                <div class="no-items-hint"><?= $this->__('no_packaging') ?></div>
                                <?php else: ?>
                                <?php foreach ($packaging ?? [] as $pack): ?>
                                <label class="component-checkbox packaging-item">
                                    <input type="checkbox" name="component_ids[]" value="packaging_<?= $pack['id'] ?>" class="edit-comp-checkbox">
                                    <span class="component-info">
                                        <span class="badge badge-warning"><?= $this->__('packaging') ?></span>
                                        <strong><?= $this->e($pack['item_sku'] ?? '') ?></strong>
                                        <span class="component-name"><?= $this->e($pack['item_name'] ?? '') ?></span>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close"><?= $this->__('cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= $this->__('save') ?></button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
.detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
.detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid var(--border); }
.detail-row:last-child { border-bottom: none; }
.detail-label { flex: 0 0 120px; color: var(--text-muted); font-size: 13px; }
.detail-value { flex: 1; }

/* Cost Summary */
.cost-summary { margin-bottom: 20px; }
.cost-summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed var(--border);
}
.cost-summary-total {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    margin-top: 8px;
    border-top: 2px solid var(--border);
    font-size: 1.2rem;
    font-weight: bold;
}
.cost-label { color: var(--text-muted); }
.cost-value { font-family: monospace; }

/* Assembly Cost Form */
.assembly-cost-form { margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border); }
.form-inline { display: flex; gap: 10px; align-items: center; }
.form-inline label { margin: 0; color: var(--text-muted); }

/* Add Component Section */
.add-component-section {
    background: var(--bg-secondary);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.add-component-form .form-row {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.add-component-form .form-group {
    flex: 1;
    min-width: 150px;
}
.add-component-form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: var(--text-muted);
}
.add-component-form .form-group select,
.add-component-form .form-group input {
    width: 100%;
}

/* Table */
.image-thumb-sm {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}
.total-row {
    background: var(--bg-secondary);
    font-size: 1.05rem;
}
.text-right { text-align: right; }
.text-center { text-align: center; }

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: var(--bg-primary, #ffffff);
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid var(--border);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 { margin: 0; }
.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
}
.modal-body { padding: 20px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 20px;
    border-top: 1px solid var(--border);
}

/* Operations */
.add-operation-form .form-row {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.add-operation-form .form-group {
    flex: 1;
    min-width: 150px;
}
.add-operation-form .form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 0.9rem;
    color: var(--text-muted);
}
.add-operation-form .form-group input,
.add-operation-form .form-group textarea,
.add-operation-form .form-group select {
    width: 100%;
}
.operation-components {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

/* Components Selection Grid */
.components-selection {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
}
.components-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 8px;
}
.component-checkbox {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background: var(--bg-primary);
    border: 2px solid var(--border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.component-checkbox:hover {
    border-color: var(--primary);
    background: var(--bg-hover);
}
.component-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    cursor: pointer;
    accent-color: var(--primary);
}
.component-checkbox input[type="checkbox"]:checked + .component-info {
    opacity: 1;
}
.component-checkbox.product-item {
    border-color: var(--success);
    background: rgba(var(--success-rgb), 0.05);
}
.component-checkbox.product-item:hover {
    background: rgba(var(--success-rgb), 0.1);
}
.component-info {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}
.component-info .badge {
    flex-shrink: 0;
}
.component-info strong {
    flex-shrink: 0;
    font-size: 0.9rem;
}
.component-name {
    color: var(--text-muted);
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Two-Column Components Selection */
.components-selection-container {
    background: var(--bg-secondary);
    border-radius: 8px;
    padding: 15px;
}
.components-two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .components-two-columns {
        grid-template-columns: 1fr;
    }
}
.components-column {
    min-width: 0;
}
.column-label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 0.95rem;
    color: var(--text-primary);
    padding-bottom: 8px;
    border-bottom: 2px solid var(--border);
}
.components-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 5px;
}
.no-items-hint {
    color: var(--text-muted);
    font-size: 0.9rem;
    padding: 15px;
    text-align: center;
    background: var(--bg-primary);
    border-radius: 6px;
    border: 1px dashed var(--border);
}

/* Packaging item style */
.component-checkbox.packaging-item {
    border-color: var(--warning, #f0ad4e);
    background: rgba(240, 173, 78, 0.05);
}
.component-checkbox.packaging-item:hover {
    background: rgba(240, 173, 78, 0.1);
}

/* Material info for details */
.component-material {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    flex-shrink: 0;
}
.component-material small {
    font-size: 0.75rem;
}
.badge-outline {
    background: transparent;
    border: 1px solid var(--primary);
    color: var(--primary);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
}
.badge-warning {
    background: var(--warning, #f0ad4e);
    color: #fff;
}

/* Component badge - distinct style for purchased components */
.badge-component {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: #fff;
    border: 1px solid #5a6268;
}

/* Centered form actions */
.form-actions-center {
    display: flex;
    justify-content: center;
    padding-top: 10px;
    border-top: 1px dashed var(--border);
}
.form-actions-center .btn {
    display: inline-flex;
    align-items: center;
}

/* Two-Column Operation Form Layout */
.operation-form-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .operation-form-columns {
        grid-template-columns: 1fr;
    }
}
.operation-form-left,
.operation-form-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.operation-form-right {
    display: flex;
    flex-direction: column;
}
.operation-form-right .form-group:first-child {
    flex: 1;
}
.operation-form-right textarea {
    height: 100%;
    min-height: 80px;
}
.form-row-inline {
    display: flex;
    gap: 15px;
}
.form-row-inline .form-group {
    flex: 1;
}
.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
}

/* Reorder Buttons */
.reorder-cell {
    white-space: nowrap;
}
.reorder-buttons {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.reorder-btn {
    padding: 4px 8px;
    line-height: 1;
}
.reorder-btn svg {
    display: block;
}
.reorder-btn.disabled {
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Composition handling
    const componentType = document.getElementById('componentType');
    const detailGroup = document.getElementById('detailSelectGroup');
    const itemGroup = document.getElementById('itemSelectGroup');
    const detailSelect = document.getElementById('detailSelect');
    const itemSelect = document.getElementById('itemSelect');

    let detailsLoaded = false;
    let itemsLoaded = false;

    if (componentType) {
        componentType.addEventListener('change', function() {
            const value = this.value;

            detailGroup.style.display = value === 'detail' ? 'block' : 'none';
            itemGroup.style.display = value === 'item' ? 'block' : 'none';

            if (value === 'detail' && !detailsLoaded) {
                loadDetails();
            }
            if (value === 'item' && !itemsLoaded) {
                loadItems();
            }
        });
    }

    function loadDetails() {
        fetch('/catalog/api/products/details')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.details) {
                    detailSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                    data.details.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.sku + ' - ' + d.name + ' (' + (d.detail_type === 'printed' ? '<?= $this->__('detail_type_printed') ?>' : '<?= $this->__('detail_type_purchased') ?>') + ')';
                        detailSelect.appendChild(opt);
                    });
                    detailsLoaded = true;
                }
            });
    }

    function loadItems() {
        fetch('/catalog/api/products/items')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.items) {
                    itemSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                    data.items.forEach(i => {
                        const opt = document.createElement('option');
                        opt.value = i.id;
                        opt.textContent = i.sku + ' - ' + i.name + (i.avg_cost > 0 ? ' (' + parseFloat(i.avg_cost).toFixed(2) + ')' : '');
                        itemSelect.appendChild(opt);
                    });
                    itemsLoaded = true;
                }
            });
    }

    // Packaging handling - load on page load
    const packagingSelect = document.getElementById('packagingSelect');
    if (packagingSelect) {
        loadPackagingItems();
    }

    function loadPackagingItems() {
        fetch('/catalog/api/products/packaging-items')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.items) {
                    packagingSelect.innerHTML = '<option value=""><?= $this->__('select') ?>...</option>';
                    data.items.forEach(i => {
                        const opt = document.createElement('option');
                        opt.value = i.id;
                        opt.textContent = i.sku + ' - ' + i.name + (i.avg_cost > 0 ? ' (' + parseFloat(i.avg_cost).toFixed(2) + ')' : '');
                        packagingSelect.appendChild(opt);
                    });
                }
            });
    }

    // AJAX Operation Reordering
    const productId = '<?= $product['id'] ?>';
    const csrfToken = '<?= $this->e($csrfToken ?? '') ?>';

    function moveOperation(operationId, direction) {
        const url = `/catalog/products/${productId}/operations/${operationId}/move-${direction}`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => {
            if (response.ok) {
                // Reorder rows in DOM without page reload
                reorderTableRows(operationId, direction);
            }
        })
        .catch(err => console.error('Move error:', err));
    }

    function reorderTableRows(operationId, direction) {
        const tbody = document.getElementById('operationsBody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr[data-operation-id]'));
        const currentIndex = rows.findIndex(r => r.dataset.operationId === String(operationId));

        if (currentIndex === -1) return;

        const swapIndex = direction === 'up' ? currentIndex - 1 : currentIndex + 1;

        if (swapIndex < 0 || swapIndex >= rows.length) return;

        // Swap rows
        const currentRow = rows[currentIndex];
        const swapRow = rows[swapIndex];

        if (direction === 'up') {
            tbody.insertBefore(currentRow, swapRow);
        } else {
            tbody.insertBefore(swapRow, currentRow);
        }

        // Update row numbers
        updateRowNumbers();

        // Update button states
        updateReorderButtons();
    }

    function updateRowNumbers() {
        const tbody = document.getElementById('operationsBody');
        if (!tbody) return;

        tbody.querySelectorAll('tr[data-operation-id]').forEach((row, index) => {
            const numCell = row.querySelector('.op-number strong');
            if (numCell) {
                numCell.textContent = index + 1;
            }
        });
    }

    function updateReorderButtons() {
        const tbody = document.getElementById('operationsBody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr[data-operation-id]');
        const count = rows.length;

        rows.forEach((row, index) => {
            const upBtn = row.querySelector('.move-up-btn');
            const downBtn = row.querySelector('.move-down-btn');

            if (upBtn) {
                upBtn.disabled = index === 0;
                upBtn.style.opacity = index === 0 ? '0.3' : '1';
            }

            if (downBtn) {
                downBtn.disabled = index === count - 1;
                downBtn.style.opacity = index === count - 1 ? '0.3' : '1';
            }
        });
    }

    // Attach event handlers to reorder buttons
    document.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.disabled) {
                moveOperation(this.dataset.id, 'up');
            }
        });
    });

    document.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.disabled) {
                moveOperation(this.dataset.id, 'down');
            }
        });
    });

    // Operations modal handling
    const editModal = document.getElementById('editOperationModal');
    const editForm = document.getElementById('editOperationForm');
    const editOpName = document.getElementById('editOpName');
    const editOpDescription = document.getElementById('editOpDescription');
    const editOpTime = document.getElementById('editOpTime');
    const editOpRate = document.getElementById('editOpRate');

    // Edit button handlers
    document.querySelectorAll('.edit-operation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            const time = this.dataset.time;
            const rate = this.dataset.rate;
            const components = this.dataset.components ? this.dataset.components.split(',') : [];

            editForm.action = '/catalog/products/<?= $product['id'] ?>/operations/' + id;
            editOpName.value = name;
            editOpDescription.value = description;
            editOpTime.value = time;
            editOpRate.value = rate;

            // Set checkboxes for selected components
            document.querySelectorAll('.edit-comp-checkbox').forEach(checkbox => {
                checkbox.checked = components.includes(checkbox.value);
            });

            editModal.style.display = 'flex';
        });
    });

    // Close modal handlers
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Close modal on backdrop click
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }
});
</script>

<?php $this->endSection(); ?>
