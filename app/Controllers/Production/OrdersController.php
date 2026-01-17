<?php
/**
 * OrdersController - Production orders management
 */

namespace App\Controllers\Production;

use App\Core\Controller;

class OrdersController extends Controller
{
    /**
     * List production orders
     */
    public function index(): void
    {
        $this->requirePermission('production.orders.view');

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(po.order_number LIKE ? OR v.sku LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $where[] = "po.status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM production_orders po JOIN variants v ON po.variant_id = v.id WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $orders = $this->db()->fetchAll(
            "SELECT po.*, v.sku as variant_sku, v.name as variant_name,
                    (SELECT COUNT(*) FROM production_tasks WHERE order_id = po.id) as task_count,
                    (SELECT COUNT(*) FROM production_tasks WHERE order_id = po.id AND status = 'completed') as completed_tasks
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             WHERE {$whereClause}
             ORDER BY po.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('production/orders/index', [
            'title' => 'Production Orders',
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show order details
     */
    public function show(string $id): void
    {
        $this->requirePermission('production.orders.view');

        $order = $this->db()->fetch(
            "SELECT po.*, v.sku as variant_sku, v.name as variant_name, p.name as product_name,
                    b.version as bom_version, r.version as routing_version,
                    u.name as created_by_name
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             JOIN products p ON v.product_id = p.id
             LEFT JOIN bom b ON po.bom_id = b.id
             LEFT JOIN routing r ON po.routing_id = r.id
             LEFT JOIN users u ON po.created_by = u.id
             WHERE po.id = ?",
            [$id]
        );

        if (!$order) {
            $this->notFound();
        }

        $tasks = $this->db()->fetchAll(
            "SELECT pt.*, u.name as assigned_name
             FROM production_tasks pt
             LEFT JOIN users u ON pt.assigned_to = u.id
             WHERE pt.order_id = ?
             ORDER BY pt.operation_number",
            [$id]
        );

        $materials = $this->db()->fetchAll(
            "SELECT mc.*, i.sku, i.name as item_name, i.unit
             FROM material_consumption mc
             JOIN items i ON mc.item_id = i.id
             WHERE mc.order_id = ?
             ORDER BY i.sku",
            [$id]
        );

        $this->render('production/orders/show', [
            'title' => "Order: {$order['order_number']}",
            'order' => $order,
            'tasks' => $tasks,
            'materials' => $materials
        ]);
    }

    /**
     * Create order form
     */
    public function create(): void
    {
        $this->requirePermission('production.orders.create');

        $variants = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name, p.name as product_name,
                    (SELECT id FROM bom WHERE variant_id = v.id AND status = 'active' LIMIT 1) as bom_id,
                    (SELECT id FROM routing WHERE variant_id = v.id AND status = 'active' LIMIT 1) as routing_id
             FROM variants v
             JOIN products p ON v.product_id = p.id
             WHERE v.is_active = 1
             ORDER BY v.sku"
        );

        $this->render('production/orders/form', [
            'title' => 'Create Production Order',
            'order' => null,
            'variants' => $variants
        ]);
    }

    /**
     * Store new order
     */
    public function store(): void
    {
        $this->requirePermission('production.orders.create');
        $this->validateCSRF();

        $variantId = (int)($_POST['variant_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 0);

        if (!$variantId || $quantity <= 0) {
            $this->session->setFlash('error', 'Variant and quantity are required');
            $this->redirect('/production/orders/create');
            return;
        }

        // Get active BOM and routing
        $bom = $this->db()->fetch(
            "SELECT id FROM bom WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        $routing = $this->db()->fetch(
            "SELECT id FROM routing WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        // Generate order number
        $orderNumber = $this->generateOrderNumber();

        $this->db()->beginTransaction();

        try {
            // Create order
            $orderId = $this->db()->insert('production_orders', [
                'order_number' => $orderNumber,
                'variant_id' => $variantId,
                'bom_id' => $bom['id'] ?? null,
                'routing_id' => $routing['id'] ?? null,
                'quantity' => $quantity,
                'planned_start' => $_POST['planned_start'] ?: null,
                'planned_end' => $_POST['planned_end'] ?: null,
                'priority' => $_POST['priority'] ?? 'normal',
                'notes' => trim($_POST['notes'] ?? ''),
                'status' => 'draft',
                'created_by' => $this->user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Create tasks from routing
            if ($routing) {
                $operations = $this->db()->fetchAll(
                    "SELECT * FROM routing_operations WHERE routing_id = ? ORDER BY sort_order",
                    [$routing['id']]
                );

                foreach ($operations as $op) {
                    $this->db()->insert('production_tasks', [
                        'order_id' => $orderId,
                        'routing_operation_id' => $op['id'],
                        'operation_number' => $op['operation_number'],
                        'name' => $op['name'],
                        'work_center' => $op['work_center'],
                        'planned_quantity' => $quantity,
                        'setup_time_minutes' => $op['setup_time_minutes'],
                        'run_time_minutes' => $op['run_time_minutes'],
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // Create material consumption from BOM
            if ($bom) {
                $bomLines = $this->db()->fetchAll(
                    "SELECT * FROM bom_lines WHERE bom_id = ?",
                    [$bom['id']]
                );

                foreach ($bomLines as $line) {
                    $this->db()->insert('material_consumption', [
                        'order_id' => $orderId,
                        'item_id' => $line['item_id'],
                        'planned_quantity' => $line['quantity'] * $quantity * (1 + $line['waste_percent']/100),
                        'unit_cost' => $line['unit_cost']
                    ]);
                }
            }

            $this->db()->commit();
            $this->audit('production_order.created', 'production_orders', $orderId, null, ['order_number' => $orderNumber]);
            $this->session->setFlash('success', 'Production order created');
            $this->redirect("/production/orders/{$orderId}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', 'Failed to create order: ' . $e->getMessage());
            $this->redirect('/production/orders/create');
        }
    }

    /**
     * Start production order
     */
    public function start(string $id): void
    {
        $this->requirePermission('production.orders.edit');
        $this->validateCSRF();

        $order = $this->db()->fetch("SELECT * FROM production_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        if (!in_array($order['status'], ['draft', 'planned'])) {
            $this->session->setFlash('error', 'Order cannot be started');
            $this->redirect("/production/orders/{$id}");
            return;
        }

        $this->db()->update('production_orders', [
            'status' => 'in_progress',
            'actual_start' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('production_order.started', 'production_orders', $id, ['status' => $order['status']], ['status' => 'in_progress']);
        $this->session->setFlash('success', 'Production order started');
        $this->redirect("/production/orders/{$id}");
    }

    /**
     * Complete production order
     */
    public function complete(string $id): void
    {
        $this->requirePermission('production.orders.edit');
        $this->validateCSRF();

        $order = $this->db()->fetch("SELECT * FROM production_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        if ($order['status'] !== 'in_progress') {
            $this->session->setFlash('error', 'Order cannot be completed');
            $this->redirect("/production/orders/{$id}");
            return;
        }

        $completedQty = (float)($_POST['completed_quantity'] ?? $order['quantity']);

        $this->db()->update('production_orders', [
            'status' => 'completed',
            'completed_quantity' => $completedQty,
            'actual_end' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        // Mark all pending tasks as completed
        $this->db()->execute(
            "UPDATE production_tasks SET status = 'completed', actual_end = NOW() WHERE order_id = ? AND status IN ('pending', 'in_progress')",
            [$id]
        );

        $this->audit('production_order.completed', 'production_orders', $id, ['status' => $order['status']], ['status' => 'completed']);
        $this->session->setFlash('success', 'Production order completed');
        $this->redirect("/production/orders/{$id}");
    }

    /**
     * Cancel production order
     */
    public function cancel(string $id): void
    {
        $this->requirePermission('production.orders.edit');
        $this->validateCSRF();

        $order = $this->db()->fetch("SELECT * FROM production_orders WHERE id = ?", [$id]);
        if (!$order) {
            $this->notFound();
        }

        if ($order['status'] === 'completed') {
            $this->session->setFlash('error', 'Completed orders cannot be cancelled');
            $this->redirect("/production/orders/{$id}");
            return;
        }

        $reason = trim($_POST['reason'] ?? '');

        $this->db()->update('production_orders', [
            'status' => 'cancelled',
            'notes' => $order['notes'] . "\n[Cancelled: {$reason}]",
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('production_order.cancelled', 'production_orders', $id, ['status' => $order['status']], ['status' => 'cancelled', 'reason' => $reason]);
        $this->session->setFlash('success', 'Production order cancelled');
        $this->redirect("/production/orders/{$id}");
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        $seq = $this->db()->fetch("SELECT * FROM document_sequences WHERE type = 'production_order' FOR UPDATE");

        if (!$seq) {
            $this->db()->insert('document_sequences', ['type' => 'production_order', 'prefix' => 'PO', 'next_number' => 1]);
            return 'PO-' . date('Ymd') . '-0001';
        }

        $number = $seq['prefix'] . '-' . date('Ymd') . '-' . str_pad($seq['next_number'], 4, '0', STR_PAD_LEFT);
        $this->db()->execute("UPDATE document_sequences SET next_number = next_number + 1 WHERE type = 'production_order'");

        return $number;
    }
}
