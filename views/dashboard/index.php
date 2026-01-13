<div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <span>&#128230;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['total_items'] ?? 0) ?></div>
                <div class="stat-label">Active Items</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success">
                <span>&#128161;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['total_products'] ?? 0) ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <span>&#9881;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['active_orders'] ?? 0) ?></div>
                <div class="stat-label">Active Orders</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-info">
                <span>&#128196;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['pending_tasks'] ?? 0) ?></div>
                <div class="stat-label">Pending Tasks</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-secondary">
                <span>&#128424;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['print_queue'] ?? 0) ?></div>
                <div class="stat-label">Print Queue</div>
            </div>
        </div>

        <div class="stat-card <?= ($stats['low_stock'] ?? 0) > 0 ? 'stat-alert' : '' ?>">
            <div class="stat-icon bg-danger">
                <span>&#9888;</span>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= e($stats['low_stock'] ?? 0) ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Pending Tasks -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pending Tasks</h3>
                <?php if (can('production.tasks.view')): ?>
                <a href="<?= url('production/tasks') ?>" class="btn btn-sm btn-secondary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($pendingTasks)): ?>
                <p class="text-muted">No pending tasks</p>
                <?php else: ?>
                <div class="task-list">
                    <?php foreach ($pendingTasks as $task): ?>
                    <div class="task-item">
                        <div class="task-info">
                            <strong><?= e($task['name']) ?></strong>
                            <span class="badge" style="background-color: <?= e($task['status_color'] ?? '#6c757d') ?>">
                                <?= e($task['status_name']) ?>
                            </span>
                        </div>
                        <div class="task-meta">
                            <?php if ($task['operation_name']): ?>
                            <span class="task-operation"><?= e($task['operation_name']) ?></span>
                            <?php endif; ?>
                            <?php if ($task['planned_start_date']): ?>
                            <span class="task-date"><?= formatDateTime($task['planned_start_date'], 'd M H:i') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Low Stock Alert</h3>
                <?php if (can('warehouse.stock.view')): ?>
                <a href="<?= url('warehouse/stock') ?>" class="btn btn-sm btn-secondary">View Stock</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($lowStockItems)): ?>
                <p class="text-muted">All items are sufficiently stocked</p>
                <?php else: ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Type</th>
                            <th class="text-right">On Hand</th>
                            <th class="text-right">Min Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                        <tr>
                            <td>
                                <a href="<?= url('warehouse/items/' . $item['id']) ?>"><?= e($item['name']) ?></a>
                            </td>
                            <td><?= e($item['type_name']) ?></td>
                            <td class="text-right text-danger">
                                <?= formatNumber($item['total_on_hand']) ?> <?= e($item['unit_code']) ?>
                            </td>
                            <td class="text-right">
                                <?= formatNumber($item['min_stock_level']) ?> <?= e($item['unit_code']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card card-full">
            <div class="card-header">
                <h3 class="card-title">Recent Activity</h3>
                <?php if (can('admin.audit.view')): ?>
                <a href="<?= url('admin/audit') ?>" class="btn btn-sm btn-secondary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                <p class="text-muted">No recent activity</p>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon"></div>
                        <div class="activity-content">
                            <div class="activity-text">
                                <strong><?= e($activity['username'] ?? 'System') ?></strong>
                                <?= e($activity['description'] ?? $activity['action']) ?>
                            </div>
                            <div class="activity-time">
                                <?= formatDateTime($activity['created_at'], 'd M Y H:i') ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
