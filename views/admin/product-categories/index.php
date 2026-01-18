<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('product_categories') ?></h1>
    <div class="page-actions">
        <?php if ($this->can('admin.product_categories.create')): ?>
        <a href="/admin/product-categories/create" class="btn btn-primary">+ <?= $this->__('create_category') ?></a>
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
                        <th><?= $this->__('description') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted"><?= $this->__('no_results') ?></td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><strong><?= $this->e($category['name']) ?></strong></td>
                        <td><?= $this->e($category['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($category['is_active']): ?>
                            <span class="badge badge-success"><?= $this->__('active') ?></span>
                            <?php else: ?>
                            <span class="badge badge-secondary"><?= $this->__('inactive') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->can('admin.product_categories.edit')): ?>
                            <a href="/admin/product-categories/<?= $category['id'] ?>/edit" class="btn btn-sm btn-outline"><?= $this->__('edit') ?></a>
                            <?php endif; ?>
                            <?php if ($this->can('admin.product_categories.delete')): ?>
                            <form method="POST" action="/admin/product-categories/<?= $category['id'] ?>/delete" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= $this->__('confirm_delete_category') ?>')">
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
