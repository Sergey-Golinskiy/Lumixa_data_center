<?php $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1"><?= $this->__('product_collections') ?></h5>
                <?php if ($this->can('admin.product_collections.create')): ?>
                <a href="/admin/product-collections/create" class="btn btn-success">
                    <i class="ri-add-line me-1"></i>
                    <?= $this->__('create_collection') ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($collections)): ?>
                <div class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-4">
                        <div class="avatar-title bg-light text-secondary rounded-circle fs-1">
                            <i class="ri-stack-line"></i>
                        </div>
                    </div>
                    <h5 class="text-muted"><?= $this->__('no_results') ?></h5>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= $this->__('name') ?></th>
                                <th><?= $this->__('description') ?></th>
                                <th><?= $this->__('products') ?></th>
                                <th><?= $this->__('status') ?></th>
                                <th><?= $this->__('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collections as $collection): ?>
                            <tr>
                                <td><span class="fw-medium"><?= $this->e($collection['name']) ?></span></td>
                                <td class="text-muted"><?= $this->e($collection['description'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info"><?= (int)$collection['product_count'] ?></span>
                                </td>
                                <td>
                                    <?php if ($collection['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success"><?= $this->__('active') ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $this->__('inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($this->can('admin.product_collections.edit')): ?>
                                        <a href="/admin/product-collections/<?= $collection['id'] ?>/edit" class="btn btn-soft-primary btn-sm">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->can('admin.product_collections.delete')): ?>
                                        <form method="POST" action="/admin/product-collections/<?= $collection['id'] ?>/delete" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                            <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('<?= $this->__('confirm_delete_collection') ?>')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
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
