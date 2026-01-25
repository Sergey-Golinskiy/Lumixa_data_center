<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/products" class="btn btn-secondary">&laquo; <?= $this->__('back_to_list') ?></a>
    <?php if ($this->can('catalog.products.edit')): ?>
    <a href="/catalog/products/<?= $product['id'] ?>/edit" class="btn btn-outline"><?= $this->__('edit_product') ?></a>
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
                    <span class="cost-label"><?= $this->__('assembly_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['assembly_cost'] ?? 0) ?></span>
                </div>
                <div class="cost-summary-total">
                    <span class="cost-label"><?= $this->__('total_production_cost') ?></span>
                    <span class="cost-value"><?= $this->currency($costData['total_cost'] ?? 0) ?></span>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const componentType = document.getElementById('componentType');
    const detailGroup = document.getElementById('detailSelectGroup');
    const itemGroup = document.getElementById('itemSelectGroup');
    const detailSelect = document.getElementById('detailSelect');
    const itemSelect = document.getElementById('itemSelect');

    let detailsLoaded = false;
    let itemsLoaded = false;

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
});
</script>

<?php $this->endSection(); ?>
