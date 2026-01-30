<?php
/**
 * OrdersController - Sales orders management (customer orders from all sources)
 */

namespace App\Controllers\Sales;

use App\Core\Controller;

class OrdersController extends Controller
{
    /**
     * List sales orders
     */
    public function index(): void
    {
        $this->requirePermission('sales.orders.view');

        if (!$this->ensureTable()) {
            return;
        }

        $search = trim($_GET['search'] ?? '');
        $source = $_GET['source'] ?? '';
        $status = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(so.order_number LIKE ? OR so.external_id LIKE ? OR so.customer_name LIKE ? OR so.customer_email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($source) {
            $where[] = "so.source = ?";
            $params[] = $source;
        }

        if ($status) {
            $where[] = "so.status = ?";
            $params[] = $status;
        }

        if ($dateFrom) {
            $where[] = "DATE(so.ordered_at) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $where[] = "DATE(so.ordered_at) <= ?";
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM sales_orders so WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $orders = $this->db()->fetchAll(
            "SELECT so.*,
                    (SELECT COUNT(*) FROM sales_order_items WHERE order_id = so.id) as item_count
             FROM sales_orders so
             WHERE {$whereClause}
             ORDER BY so.ordered_at DESC, so.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get summary stats
        $stats = $this->getOrderStats();

        $this->view('sales/orders/index', [
            'title' => $this->app->getTranslator()->get('sales_orders'),
            'orders' => $orders,
            'stats' => $stats,
            'search' => $search,
            'source' => $source,
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'sources' => ['woocommerce', 'instagram', 'offline', 'manual'],
            'statuses' => ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded']
        ]);
    }

    /**
     * Show order details
     */
    public function show(string $id): void
    {
        $this->requirePermission('sales.orders.view');

        if (!$this->ensureTable()) {
            return;
        }

        $order = $this->db()->fetch(
            "SELECT so.*, u.name as created_by_name
             FROM sales_orders so
             LEFT JOIN users u ON so.created_by = u.id
             WHERE so.id = ?",
            [$id]
        );

        if (!$order) {
            $this->notFound();
        }

        $items = $this->db()->fetchAll(
            "SELECT soi.*, p.name as product_name_local
             FROM sales_order_items soi
             LEFT JOIN products p ON soi.product_id = p.id
             WHERE soi.order_id = ?
             ORDER BY soi.id",
            [$id]
        );

        $this->view('sales/orders/show', [
            'title' => $this->app->getTranslator()->get('order') . ' ' . $order['order_number'],
            'order' => $order,
            'items' => $items
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('sales.orders.create');

        if (!$this->ensureTable()) {
            return;
        }

        // Get products for item selection
        $products = $this->getAvailableProducts();

        $this->view('sales/orders/form', [
            'title' => $this->app->getTranslator()->get('create_order'),
            'order' => null,
            'items' => [],
            'products' => $products
        ]);
    }

    /**
     * Store new order
     */
    public function store(): void
    {
        $this->requirePermission('sales.orders.create');

        if (!$this->ensureTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/sales/orders/create');
            return;
        }

        $errors = $this->validateOrder();
        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/sales/orders/create');
            return;
        }

        $this->db()->beginTransaction();

        try {
            $orderNumber = $this->generateOrderNumber();
            $source = $this->post('source', 'manual');

            $orderId = $this->db()->insert('sales_orders', [
                'order_number' => $orderNumber,
                'source' => $source,
                'customer_name' => trim($this->post('customer_name', '')),
                'customer_email' => trim($this->post('customer_email', '')),
                'customer_phone' => trim($this->post('customer_phone', '')),
                'shipping_address' => trim($this->post('shipping_address', '')),
                'shipping_city' => trim($this->post('shipping_city', '')),
                'shipping_country' => trim($this->post('shipping_country', '')),
                'shipping_postal_code' => trim($this->post('shipping_postal_code', '')),
                'shipping_method' => trim($this->post('shipping_method', '')),
                'shipping_cost' => (float)$this->post('shipping_cost', 0),
                'discount' => (float)$this->post('discount', 0),
                'notes' => trim($this->post('notes', '')),
                'internal_notes' => trim($this->post('internal_notes', '')),
                'status' => $this->post('status', 'pending'),
                'payment_status' => $this->post('payment_status', 'pending'),
                'payment_method' => trim($this->post('payment_method', '')),
                'currency' => $this->post('currency', 'UAH'),
                'ordered_at' => $this->post('ordered_at') ?: date('Y-m-d H:i:s'),
                'created_by' => $this->user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Add order items
            $itemSkus = $this->post('item_sku', []);
            $itemNames = $this->post('item_name', []);
            $itemQtys = $this->post('item_qty', []);
            $itemPrices = $this->post('item_price', []);
            $itemProductIds = $this->post('item_product_id', []);

            $subtotal = 0;
            foreach ($itemNames as $i => $name) {
                if (empty($name)) continue;

                $qty = (int)($itemQtys[$i] ?? 1);
                $price = (float)($itemPrices[$i] ?? 0);
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $this->db()->insert('sales_order_items', [
                    'order_id' => $orderId,
                    'product_id' => !empty($itemProductIds[$i]) ? (int)$itemProductIds[$i] : null,
                    'sku' => trim($itemSkus[$i] ?? ''),
                    'name' => trim($name),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                    'total' => $lineTotal,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Update order totals
            $shippingCost = (float)$this->post('shipping_cost', 0);
            $discount = (float)$this->post('discount', 0);
            $total = $subtotal + $shippingCost - $discount;

            $this->db()->update('sales_orders', [
                'subtotal' => $subtotal,
                'total' => $total
            ], ['id' => $orderId]);

            $this->db()->commit();
            $this->audit('sales_order.created', 'sales_orders', $orderId, null, ['order_number' => $orderNumber]);
            $this->session->setFlash('success', $translator->get('order_created_success'));
            $this->redirect("/sales/orders/{$orderId}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $translator->get('order_create_error') . ': ' . $e->getMessage());
            $this->redirect('/sales/orders/create');
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('sales.orders.edit');

        if (!$this->ensureTable()) {
            return;
        }

        $order = $this->db()->fetch("SELECT * FROM sales_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        $items = $this->db()->fetchAll(
            "SELECT * FROM sales_order_items WHERE order_id = ? ORDER BY id",
            [$id]
        );

        $products = $this->getAvailableProducts();

        $this->view('sales/orders/form', [
            'title' => $this->app->getTranslator()->get('edit_order') . ' ' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'products' => $products
        ]);
    }

    /**
     * Update order
     */
    public function update(string $id): void
    {
        $this->requirePermission('sales.orders.edit');

        if (!$this->ensureTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/sales/orders/{$id}/edit");
            return;
        }

        $order = $this->db()->fetch("SELECT * FROM sales_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        $errors = $this->validateOrder($id);
        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/sales/orders/{$id}/edit");
            return;
        }

        $this->db()->beginTransaction();

        try {
            $this->db()->update('sales_orders', [
                'customer_name' => trim($this->post('customer_name', '')),
                'customer_email' => trim($this->post('customer_email', '')),
                'customer_phone' => trim($this->post('customer_phone', '')),
                'shipping_address' => trim($this->post('shipping_address', '')),
                'shipping_city' => trim($this->post('shipping_city', '')),
                'shipping_country' => trim($this->post('shipping_country', '')),
                'shipping_postal_code' => trim($this->post('shipping_postal_code', '')),
                'shipping_method' => trim($this->post('shipping_method', '')),
                'shipping_cost' => (float)$this->post('shipping_cost', 0),
                'discount' => (float)$this->post('discount', 0),
                'notes' => trim($this->post('notes', '')),
                'internal_notes' => trim($this->post('internal_notes', '')),
                'status' => $this->post('status', 'pending'),
                'payment_status' => $this->post('payment_status', 'pending'),
                'payment_method' => trim($this->post('payment_method', '')),
                'tracking_number' => trim($this->post('tracking_number', '')),
                'tracking_url' => trim($this->post('tracking_url', '')),
                'updated_by' => $this->user()['id'],
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            // Delete existing items and re-add
            $this->db()->delete('sales_order_items', ['order_id' => $id]);

            $itemSkus = $this->post('item_sku', []);
            $itemNames = $this->post('item_name', []);
            $itemQtys = $this->post('item_qty', []);
            $itemPrices = $this->post('item_price', []);
            $itemProductIds = $this->post('item_product_id', []);

            $subtotal = 0;
            foreach ($itemNames as $i => $name) {
                if (empty($name)) continue;

                $qty = (int)($itemQtys[$i] ?? 1);
                $price = (float)($itemPrices[$i] ?? 0);
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $this->db()->insert('sales_order_items', [
                    'order_id' => $id,
                    'product_id' => !empty($itemProductIds[$i]) ? (int)$itemProductIds[$i] : null,
                    'sku' => trim($itemSkus[$i] ?? ''),
                    'name' => trim($name),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                    'total' => $lineTotal,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $shippingCost = (float)$this->post('shipping_cost', 0);
            $discount = (float)$this->post('discount', 0);
            $total = $subtotal + $shippingCost - $discount;

            $this->db()->update('sales_orders', [
                'subtotal' => $subtotal,
                'total' => $total
            ], ['id' => $id]);

            $this->db()->commit();
            $this->audit('sales_order.updated', 'sales_orders', $id, $order, $_POST);
            $this->session->setFlash('success', $translator->get('order_updated_success'));
            $this->redirect("/sales/orders/{$id}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $translator->get('order_update_error') . ': ' . $e->getMessage());
            $this->redirect("/sales/orders/{$id}/edit");
        }
    }

    /**
     * Update order status quickly
     */
    public function updateStatus(string $id): void
    {
        $this->requirePermission('sales.orders.edit');

        if (!$this->ensureTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/sales/orders/{$id}");
            return;
        }

        $order = $this->db()->fetch("SELECT * FROM sales_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        $newStatus = $this->post('status');
        $validStatuses = ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];

        if (!in_array($newStatus, $validStatuses)) {
            $this->session->setFlash('error', $translator->get('invalid_status'));
            $this->redirect("/sales/orders/{$id}");
            return;
        }

        $updateData = [
            'status' => $newStatus,
            'updated_by' => $this->user()['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Set timestamps for specific statuses
        if ($newStatus === 'shipped' && !$order['shipped_at']) {
            $updateData['shipped_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'delivered' && !$order['delivered_at']) {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        $this->db()->update('sales_orders', $updateData, ['id' => $id]);
        $this->audit('sales_order.status_changed', 'sales_orders', $id, ['status' => $order['status']], ['status' => $newStatus]);
        $this->session->setFlash('success', $translator->get('status_updated_success'));
        $this->redirect("/sales/orders/{$id}");
    }

    /**
     * Delete order
     */
    public function delete(string $id): void
    {
        $this->requirePermission('sales.orders.delete');

        if (!$this->ensureTable()) {
            return;
        }

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/sales/orders');
            return;
        }

        $order = $this->db()->fetch("SELECT * FROM sales_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        $this->db()->delete('sales_orders', ['id' => $id]);
        $this->audit('sales_order.deleted', 'sales_orders', $id, $order, null);
        $this->session->setFlash('success', $translator->get('order_deleted_success'));
        $this->redirect('/sales/orders');
    }

    /**
     * Validate order data
     */
    private function validateOrder(?string $id = null): array
    {
        $errors = [];
        $translator = $this->app->getTranslator();

        // At least one item is required
        $itemNames = $this->post('item_name', []);
        $hasItems = false;
        foreach ($itemNames as $name) {
            if (!empty(trim($name))) {
                $hasItems = true;
                break;
            }
        }

        if (!$hasItems) {
            $errors['items'] = $translator->get('order_items_required');
        }

        return $errors;
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        $seq = $this->db()->fetch("SELECT * FROM document_sequences WHERE type = 'sales_order' FOR UPDATE");

        if (!$seq) {
            $this->db()->insert('document_sequences', ['type' => 'sales_order', 'prefix' => 'SO', 'next_number' => 1]);
            return 'SO-' . date('Ymd') . '-0001';
        }

        $number = ($seq['prefix'] ?? 'SO') . '-' . date('Ymd') . '-' . str_pad($seq['next_number'] ?? 1, 4, '0', STR_PAD_LEFT);
        $this->db()->execute("UPDATE document_sequences SET next_number = next_number + 1 WHERE type = 'sales_order'");

        return $number;
    }

    /**
     * Get available products for selection
     */
    private function getAvailableProducts(): array
    {
        if (!$this->db()->tableExists('products')) {
            return [];
        }

        return $this->db()->fetchAll(
            "SELECT id, sku, name FROM products WHERE is_active = 1 ORDER BY name"
        );
    }

    /**
     * Get order statistics
     */
    private function getOrderStats(): array
    {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'today' => 0,
            'this_month_revenue' => 0
        ];

        try {
            $stats['total'] = (int)$this->db()->fetchColumn("SELECT COUNT(*) FROM sales_orders");
            $stats['pending'] = (int)$this->db()->fetchColumn("SELECT COUNT(*) FROM sales_orders WHERE status = 'pending'");
            $stats['processing'] = (int)$this->db()->fetchColumn("SELECT COUNT(*) FROM sales_orders WHERE status = 'processing'");
            $stats['completed'] = (int)$this->db()->fetchColumn("SELECT COUNT(*) FROM sales_orders WHERE status = 'completed'");
            $stats['today'] = (int)$this->db()->fetchColumn("SELECT COUNT(*) FROM sales_orders WHERE DATE(created_at) = CURDATE()");
            $stats['this_month_revenue'] = (float)$this->db()->fetchColumn(
                "SELECT COALESCE(SUM(total), 0) FROM sales_orders WHERE status IN ('completed', 'delivered', 'shipped') AND MONTH(ordered_at) = MONTH(CURDATE()) AND YEAR(ordered_at) = YEAR(CURDATE())"
            );
        } catch (\Exception $e) {
            // Tables may not exist yet
        }

        return $stats;
    }

    /**
     * Ensure tables exist
     */
    private function ensureTable(): bool
    {
        if ($this->db()->tableExists('sales_orders')) {
            return true;
        }

        $translator = $this->app->getTranslator();
        $this->session->setFlash('error', $translator->get('sales_orders_table_missing'));
        $this->redirect('/admin/diagnostics');
        return false;
    }
}
