<?php $this->section('content'); ?>

<div class="page-header">
    <h1><?= $this->__('sales_orders') ?></h1>
    <div class="page-actions">
        <a href="/sales/orders/create" class="btn btn-primary">+ <?= $this->__('create_order') ?></a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label"><?= $this->__('total_orders') ?></div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-value"><?= $stats['pending'] ?></div>
        <div class="stat-label"><?= $this->__('pending') ?></div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-value"><?= $stats['processing'] ?></div>
        <div class="stat-label"><?= $this->__('processing') ?></div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-value"><?= $stats['completed'] ?></div>
        <div class="stat-label"><?= $this->__('completed') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= number_format($stats['this_month_revenue'], 2) ?></div>
        <div class="stat-label"><?= $this->__('this_month_revenue') ?></div>
    </div>
</div>

<!-- Filters -->
<div class="card filters-card">
    <form method="GET" action="/sales/orders" class="filters-form">
        <div class="filter-row">
            <div class="filter-group">
                <input type="text" name="search" placeholder="<?= $this->__('search_orders') ?>"
                       value="<?= $this->e($search) ?>">
            </div>

            <div class="filter-group">
                <select name="source">
                    <option value=""><?= $this->__('all_sources') ?></option>
                    <?php foreach ($sources as $src): ?>
                        <option value="<?= $src ?>" <?= $source === $src ? 'selected' : '' ?>>
                            <?= $this->__('source_' . $src) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="status">
                    <option value=""><?= $this->__('all_statuses') ?></option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>>
                            <?= $this->__('order_status_' . $st) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" placeholder="<?= $this->__('from') ?>"
                       value="<?= $this->e($dateFrom) ?>">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" placeholder="<?= $this->__('to') ?>"
                       value="<?= $this->e($dateTo) ?>">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-secondary"><?= $this->__('filter') ?></button>
                <?php if ($search || $source || $status || $dateFrom || $dateTo): ?>
                    <a href="/sales/orders" class="btn btn-link"><?= $this->__('clear') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Orders List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <p class="text-muted text-center"><?= $this->__('no_orders_found') ?></p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= $this->__('order_number') ?></th>
                        <th><?= $this->__('source') ?></th>
                        <th><?= $this->__('customer') ?></th>
                        <th><?= $this->__('items') ?></th>
                        <th><?= $this->__('total') ?></th>
                        <th><?= $this->__('status') ?></th>
                        <th><?= $this->__('payment') ?></th>
                        <th><?= $this->__('date') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <a href="/sales/orders/<?= $order['id'] ?>" class="order-link">
                                <?= $this->e($order['order_number']) ?>
                            </a>
                            <?php if ($order['external_id']): ?>
                                <br><small class="text-muted">#<?= $this->e($order['external_id']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-source-<?= $order['source'] ?>">
                                <?= $this->__('source_' . $order['source']) ?>
                            </span>
                        </td>
                        <td>
                            <?= $this->e($order['customer_name'] ?: '-') ?>
                            <?php if ($order['customer_email']): ?>
                                <br><small class="text-muted"><?= $this->e($order['customer_email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $order['item_count'] ?></td>
                        <td class="text-right">
                            <strong><?= number_format($order['total'], 2) ?></strong>
                            <small><?= $this->e($order['currency']) ?></small>
                        </td>
                        <td>
                            <span class="badge badge-status-<?= $order['status'] ?>">
                                <?= $this->__('order_status_' . $order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-payment-<?= $order['payment_status'] ?>">
                                <?= $this->__('payment_status_' . $order['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= $this->date($order['ordered_at'] ?: $order['created_at']) ?>
                        </td>
                        <td class="actions">
                            <a href="/sales/orders/<?= $order['id'] ?>" class="btn btn-sm btn-secondary"><?= $this->__('view') ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $params = array_filter([
                        'page' => $p,
                        'search' => $search,
                        'source' => $source,
                        'status' => $status,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo
                    ]);
                    ?>
                    <a href="/sales/orders?<?= http_build_query($params) ?>"
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header h1 { margin: 0; }

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 15px;
    text-align: center;
}

.stat-card.stat-warning { border-left: 3px solid var(--warning); }
.stat-card.stat-info { border-left: 3px solid var(--info); }
.stat-card.stat-success { border-left: 3px solid var(--success); }

.stat-value { font-size: 1.8em; font-weight: bold; }
.stat-label { color: var(--text-muted); font-size: 0.85em; }

.filters-card { margin-bottom: 20px; }
.filters-card .card-body { padding: 15px; }

.filters-form .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.filter-group { flex: 1; min-width: 150px; }
.filter-group:last-child { flex: none; }

.order-link { font-weight: 500; }

.badge-source-woocommerce { background: #96588a; color: white; }
.badge-source-instagram { background: #c13584; color: white; }
.badge-source-offline { background: #6c757d; color: white; }
.badge-source-manual { background: #17a2b8; color: white; }

.badge-status-pending { background: #ffc107; color: #000; }
.badge-status-processing { background: #17a2b8; color: white; }
.badge-status-on_hold { background: #fd7e14; color: white; }
.badge-status-shipped { background: #6f42c1; color: white; }
.badge-status-delivered { background: #20c997; color: white; }
.badge-status-completed { background: #28a745; color: white; }
.badge-status-cancelled { background: #dc3545; color: white; }
.badge-status-refunded { background: #6c757d; color: white; }

.badge-payment-pending { background: #ffc107; color: #000; }
.badge-payment-paid { background: #28a745; color: white; }
.badge-payment-partial { background: #fd7e14; color: white; }
.badge-payment-refunded { background: #6c757d; color: white; }
.badge-payment-failed { background: #dc3545; color: white; }

.pagination { display: flex; gap: 5px; justify-content: center; margin-top: 20px; }
</style>

<?php $this->endSection(); ?>
