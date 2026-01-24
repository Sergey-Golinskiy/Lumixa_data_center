<?php $this->section('content'); ?>

<div class="page-actions" style="margin-bottom: 20px;">
    <a href="/catalog/details/<?= $detail['id'] ?>" class="btn btn-secondary">
        &laquo; <?= $this->__('view_detail') ?>
    </a>
    <a href="/catalog/details/<?= $detail['id'] ?>/configurations/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= $this->__('add_configuration') ?>
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2><?= $this->__('detail_configurations') ?>: <?= $this->e($detail['name']) ?></h2>
        <p class="text-muted"><?= $this->__('base_detail') ?>: <?= $this->e($detail['sku']) ?></p>
    </div>
    <div class="card-body">
        <?php if (empty($configurations)): ?>
            <p class="text-muted text-center" style="padding: 40px 0;">
                <?= $this->__('no_configurations_found') ?>
            </p>
        <?php else: ?>
            <div class="configurations-grid">
                <?php foreach ($configurations as $config): ?>
                    <div class="configuration-card">
                        <?php if ($config['image_path']): ?>
                            <div class="config-image">
                                <img src="/<?= $this->e(ltrim($config['image_path'], '/')) ?>" alt="<?= $this->e($config['name']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="config-image config-image-placeholder">
                                <i class="fas fa-cube"></i>
                            </div>
                        <?php endif; ?>

                        <div class="config-info">
                            <h4><?= $this->e($config['name']) ?></h4>
                            <p class="config-sku"><strong><?= $this->__('sku') ?>:</strong> <?= $this->e($config['sku']) ?></p>

                            <?php if ($config['material_name']): ?>
                                <p><strong><?= $this->__('material') ?>:</strong> <?= $this->e($config['material_name']) ?></p>
                            <?php endif; ?>

                            <?php if ($config['material_color']): ?>
                                <p><strong><?= $this->__('material_color') ?>:</strong> <?= $this->e($config['material_color']) ?></p>
                            <?php endif; ?>

                            <div class="config-status">
                                <span class="badge <?= $config['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $config['is_active'] ? $this->__('active') : $this->__('inactive') ?>
                                </span>
                            </div>
                        </div>

                        <div class="config-actions">
                            <a href="/catalog/details/<?= $detail['id'] ?>/configurations/<?= $config['id'] ?>/edit"
                               class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> <?= $this->__('edit') ?>
                            </a>
                            <form method="POST" action="/catalog/details/<?= $detail['id'] ?>/configurations/<?= $config['id'] ?>/delete"
                                  style="display: inline-block;"
                                  onsubmit="return confirm('<?= $this->__('confirm_delete') ?>')">
                                <input type="hidden" name="_csrf_token" value="<?= $this->e($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> <?= $this->__('delete') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.configurations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.configuration-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    transition: box-shadow 0.2s;
}

.configuration-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.config-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.config-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.config-image-placeholder {
    font-size: 4rem;
    color: #dee2e6;
}

.config-info {
    padding: 15px;
}

.config-info h4 {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
    color: #333;
}

.config-info p {
    margin: 5px 0;
    font-size: 0.9rem;
    color: #666;
}

.config-sku {
    color: #007bff !important;
    font-weight: 600;
}

.config-status {
    margin-top: 10px;
}

.config-actions {
    padding: 10px 15px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 10px;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}
</style>

<?php $this->endSection(); ?>
