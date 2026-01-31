<?php $this->section('content'); ?>

<div class="admin-dashboard">
    <!-- Statistics Overview -->
    <div class="admin-stats-overview">
        <div class="admin-stats-row">
            <!-- User Management Stats -->
            <div class="admin-stat-group">
                <div class="stat-group-header">
                    <span class="stat-group-icon">&#128101;</span>
                    <span class="stat-group-title"><?= $this->__('user_management') ?></span>
                </div>
                <div class="stat-group-stats">
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['users'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('users') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['active_users'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('active') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['roles'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('roles') ?></span>
                    </div>
                </div>
                <div class="stat-group-actions">
                    <a href="/admin/users" class="btn btn-sm btn-outline"><?= $this->__('nav_users') ?></a>
                    <a href="/admin/roles" class="btn btn-sm btn-outline"><?= $this->__('nav_roles') ?></a>
                </div>
            </div>

            <!-- Catalog Stats -->
            <div class="admin-stat-group">
                <div class="stat-group-header">
                    <span class="stat-group-icon">&#128230;</span>
                    <span class="stat-group-title"><?= $this->__('catalog_settings') ?></span>
                </div>
                <div class="stat-group-stats">
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['products'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('products') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['categories'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('categories') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['collections'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('collections') ?></span>
                    </div>
                </div>
                <div class="stat-group-actions">
                    <a href="/admin/product-categories" class="btn btn-sm btn-outline"><?= $this->__('categories') ?></a>
                    <a href="/admin/product-collections" class="btn btn-sm btn-outline"><?= $this->__('collections') ?></a>
                </div>
            </div>
        </div>

        <div class="admin-stats-row">
            <!-- System Stats -->
            <div class="admin-stat-group">
                <div class="stat-group-header">
                    <span class="stat-group-icon">&#9881;</span>
                    <span class="stat-group-title"><?= $this->__('system') ?></span>
                </div>
                <div class="stat-group-stats">
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['backups'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('backups') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['audit_today'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('today') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['audit_entries'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('week') ?></span>
                    </div>
                </div>
                <div class="stat-group-actions">
                    <a href="/admin/backups" class="btn btn-sm btn-outline"><?= $this->__('backups') ?></a>
                    <a href="/admin/audit" class="btn btn-sm btn-outline"><?= $this->__('audit') ?></a>
                    <a href="/admin/diagnostics" class="btn btn-sm btn-outline"><?= $this->__('diagnostics') ?></a>
                </div>
            </div>

            <!-- Equipment Stats -->
            <div class="admin-stat-group">
                <div class="stat-group-header">
                    <span class="stat-group-icon">&#128424;</span>
                    <span class="stat-group-title"><?= $this->__('equipment_settings') ?></span>
                </div>
                <div class="stat-group-stats">
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['printers'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('printers') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['items'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('items') ?></span>
                    </div>
                    <div class="mini-stat">
                        <span class="mini-stat-value"><?= $this->e($stats['partners'] ?? 0) ?></span>
                        <span class="mini-stat-label"><?= $this->__('partners') ?></span>
                    </div>
                </div>
                <div class="stat-group-actions">
                    <a href="/admin/printers" class="btn btn-sm btn-outline"><?= $this->__('printers') ?></a>
                    <a href="/admin/item-options/materials" class="btn btn-sm btn-outline"><?= $this->__('materials') ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Admin Sections -->
    <div class="admin-main-sections">
        <!-- Admin Menu Cards -->
        <div class="admin-menu-grid">
            <!-- User Management -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">&#128101;</span>
                    <h3><?= $this->__('user_management') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('manage_users_roles_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/users"><span class="link-icon">&#128100;</span> <?= $this->__('nav_users') ?></a></li>
                    <li><a href="/admin/roles"><span class="link-icon">&#128101;</span> <?= $this->__('nav_roles') ?></a></li>
                </ul>
            </div>

            <!-- Catalog Settings -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">&#128230;</span>
                    <h3><?= $this->__('catalog_settings') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('catalog_settings_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/product-categories"><span class="link-icon">&#128193;</span> <?= $this->__('nav_product_categories') ?></a></li>
                    <li><a href="/admin/product-collections"><span class="link-icon">&#128218;</span> <?= $this->__('nav_product_collections') ?></a></li>
                </ul>
            </div>

            <!-- Orders Settings -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">&#128722;</span>
                    <h3><?= $this->__('orders_settings') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('orders_settings_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/order-statuses"><span class="link-icon">&#128203;</span> <?= $this->__('nav_order_statuses') ?></a></li>
                </ul>
            </div>

            <!-- Item Settings -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">&#9874;</span>
                    <h3><?= $this->__('item_settings') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('item_settings_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/item-options/materials"><span class="link-icon">&#128295;</span> <?= $this->__('nav_materials') ?></a></li>
                    <li><a href="/admin/item-options/manufacturers"><span class="link-icon">&#127981;</span> <?= $this->__('nav_manufacturers') ?></a></li>
                    <li><a href="/admin/item-options/plastic-types"><span class="link-icon">&#128204;</span> <?= $this->__('nav_plastic_types') ?></a></li>
                    <li><a href="/admin/item-options/filament-aliases"><span class="link-icon">&#127912;</span> <?= $this->__('nav_filament_aliases') ?></a></li>
                </ul>
            </div>

            <!-- Equipment -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">&#128424;</span>
                    <h3><?= $this->__('equipment_settings') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('equipment_settings_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/printers"><span class="link-icon">&#128424;</span> <?= $this->__('nav_printers') ?></a></li>
                </ul>
            </div>

            <!-- System -->
            <div class="admin-menu-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #64748b, #475569);">&#9881;</span>
                    <h3><?= $this->__('system') ?></h3>
                </div>
                <p class="admin-menu-desc"><?= $this->__('system_settings_desc') ?></p>
                <ul class="admin-menu-links">
                    <li><a href="/admin/integrations"><span class="link-icon">&#128279;</span> <?= $this->__('nav_integrations') ?></a></li>
                    <li><a href="/admin/audit"><span class="link-icon">&#128203;</span> <?= $this->__('nav_audit') ?></a></li>
                    <li><a href="/admin/backups"><span class="link-icon">&#128190;</span> <?= $this->__('nav_backups') ?></a></li>
                    <li><a href="/admin/diagnostics"><span class="link-icon">&#128295;</span> <?= $this->__('nav_diagnostics') ?></a></li>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="admin-menu-card admin-quick-actions-card">
                <div class="admin-menu-card-header">
                    <span class="admin-menu-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">&#9889;</span>
                    <h3><?= $this->__('quick_actions') ?></h3>
                </div>
                <div class="admin-quick-actions">
                    <a href="/admin/users/create" class="admin-quick-action">
                        <span class="qa-icon">+</span>
                        <span><?= $this->__('create_user') ?></span>
                    </a>
                    <a href="/admin/backups" class="admin-quick-action">
                        <span class="qa-icon">&#128190;</span>
                        <span><?= $this->__('create_backup') ?></span>
                    </a>
                    <a href="/admin/diagnostics" class="admin-quick-action">
                        <span class="qa-icon">&#128295;</span>
                        <span><?= $this->__('diagnostics') ?></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recentAudit)): ?>
        <div class="admin-recent-activity">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>&#128203; <?= $this->__('recent_activity') ?></span>
                        <a href="/admin/audit" class="btn btn-sm btn-outline"><?= $this->__('view_all') ?></a>
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th><?= $this->__('action') ?></th>
                                <th><?= $this->__('user') ?></th>
                                <th><?= $this->__('entity') ?></th>
                                <th><?= $this->__('time') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAudit as $entry): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?= $this->getAuditActionBadge($entry['action'] ?? '') ?>">
                                        <?= $this->e($entry['action'] ?? '-') ?>
                                    </span>
                                </td>
                                <td><?= $this->e($entry['user_name'] ?? '-') ?></td>
                                <td><?= $this->e($entry['entity_type'] ?? '-') ?></td>
                                <td>
                                    <span class="text-muted"><?= $this->e(date('d.m.Y H:i', strtotime($entry['created_at']))) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Admin Dashboard Styles */
.admin-dashboard {
    max-width: 1400px;
}

/* Stats Overview */
.admin-stats-overview {
    margin-bottom: 30px;
}

.admin-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.admin-stat-group {
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
}

.stat-group-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.stat-group-icon {
    font-size: 24px;
}

.stat-group-title {
    font-weight: 600;
    font-size: 16px;
    color: var(--text);
}

.stat-group-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 15px;
}

.mini-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.mini-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.2;
}

.mini-stat-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
}

.stat-group-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Admin Menu Grid */
.admin-menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.admin-menu-card {
    background: var(--bg-card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.admin-menu-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.admin-menu-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.admin-menu-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.admin-menu-card-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.admin-menu-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 15px;
    line-height: 1.5;
}

.admin-menu-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-menu-links li {
    margin-bottom: 8px;
}

.admin-menu-links li:last-child {
    margin-bottom: 0;
}

.admin-menu-links a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--bg);
    border-radius: var(--radius);
    color: var(--text);
    font-size: 14px;
    transition: all 0.2s;
}

.admin-menu-links a:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
}

.admin-menu-links .link-icon {
    font-size: 14px;
    width: 20px;
    text-align: center;
}

/* Quick Actions Card */
.admin-quick-actions-card {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.admin-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.admin-quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: white;
    border-radius: var(--radius);
    color: var(--text);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
}

.admin-quick-action:hover {
    background: var(--primary);
    color: white;
    text-decoration: none;
    transform: translateX(4px);
}

.admin-quick-action .qa-icon {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

/* Recent Activity */
.admin-recent-activity {
    margin-top: 20px;
}

/* Button Outline */
.btn-outline {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
}

.btn-outline:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

/* Badge Secondary */
.badge-secondary {
    background: #e2e8f0;
    color: #475569;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-stats-row {
        grid-template-columns: 1fr;
    }

    .stat-group-stats {
        justify-content: space-around;
    }

    .admin-menu-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php $this->endSection(); ?>
