<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('bill_of_materials') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('catalog.bom.create')): ?>
        <a href="/catalog/bom/create" class="btn btn-primary">+ <?= $this->__('new_bom') ?></a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="<?= $this->__('search_variant_name') ?>"
                           value="<?= $this->e($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value=""><?= $this->__('all_statuses') ?></option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>><?= $this->__('draft') ?></option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= $this->__('active') ?></option>
                        <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>><?= $this->__('archived') ?></option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <a href="/catalog/bom" class="btn btn-outline"><?= $this->__('clear') ?></a>
            </div>
        </form>
    </div>
</div>

<!-- BOM Table -->
<div class="card">
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->__('photo') ?></th>
                        <th><?= $this->__('variant') ?></th>
                        <th><?= $this->__('version') ?></th>
                        <th><?= $this->__('name') ?></th>
                        <th class="text-center"><?= $this->__('bom_lines') ?></th>
                        <th class="text-right"><?= $this->__('total_cost') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('effective_date') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($boms)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted"><?= $this->__('no_boms_found') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($boms as $bom): ?>
                    <tr>
                        <td>
                            <?php if (!empty($bom['image_path'])): ?>
                            <img src="/<?= $this->e(ltrim($bom['image_path'], '/')) ?>" alt="<?= $this->__('photo') ?>" class="image-thumb" data-image-preview="/<?= $this->e(ltrim($bom['image_path'], '/')) ?>">
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/catalog/variants/<?= $bom['variant_id'] ?>">
                                <strong><?= $this->e($bom['variant_sku']) ?></strong>
                            </a>
                            <br><small class="text-muted"><?= $this->e($bom['variant_name']) ?></small>
                        </td>
                        <td><?= $this->e($bom['version']) ?></td>
                        <td><?= $this->e($bom['name'] ?? '-') ?></td>
                        <td class="text-center"><?= $bom['line_count'] ?></td>
                        <td class="text-right"><?= number_format($bom['total_cost'] ?? 0, 2) ?></td>
                        <td>
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
                        </td>
                        <td><?= $bom['effective_date'] ? $this->date($bom['effective_date'], 'Y-m-d') : '-' ?></td>
                        <td>
                            <a href="/catalog/bom/<?= $bom['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                            <?php if ($bom['status'] === 'draft' && $this->can('catalog.bom.edit')): ?>
                            <a href="/catalog/bom/<?= $bom['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-sm btn-outline">&laquo; <?= $this->__('prev') ?></a>
            <?php endif; ?>
            <span class="pagination-info"><?= $this->__('page_of', ['current' => $page, 'total' => $totalPages]) ?></span>
            <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-sm btn-outline"><?= $this->__('next') ?> &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }
.filter-form { margin: 0; }
.filter-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.filter-group { flex: 1; min-width: 150px; }
.filter-group input, .filter-group select { width: 100%; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
</style>

<?php $this->endSection(); ?>
