<?php
/**
 * TasksController - Production tasks management
 */

namespace App\Controllers\Production;

use App\Core\Controller;

class TasksController extends Controller
{
    /**
     * List tasks
     */
    public function index(): void
    {
        $this->requirePermission('production.tasks.view');

        $orderId = $_GET['order_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $assignedTo = $_GET['assigned_to'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($orderId) {
            $where[] = "pt.order_id = ?";
            $params[] = $orderId;
        }

        if ($status) {
            $where[] = "pt.status = ?";
            $params[] = $status;
        }

        if ($assignedTo === 'me') {
            $where[] = "pt.assigned_to = ?";
            $params[] = $this->user()['id'];
        } elseif ($assignedTo === 'unassigned') {
            $where[] = "pt.assigned_to IS NULL";
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM production_tasks pt
             JOIN production_orders po ON pt.order_id = po.id
             WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $tasks = $this->db->fetchAll(
            "SELECT pt.*, po.order_number, po.status as order_status,
                    v.sku as variant_sku, u.username as assigned_name
             FROM production_tasks pt
             JOIN production_orders po ON pt.order_id = po.id
             JOIN variants v ON po.variant_id = v.id
             LEFT JOIN users u ON pt.assigned_to = u.id
             WHERE {$whereClause}
             ORDER BY po.priority = 'urgent' DESC, po.priority = 'high' DESC, pt.order_id, pt.operation_number
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('production/tasks/index', [
            'title' => 'Production Tasks',
            'tasks' => $tasks,
            'orderId' => $orderId,
            'status' => $status,
            'assignedTo' => $assignedTo,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show task details
     */
    public function show(string $id): void
    {
        $this->requirePermission('production.tasks.view');

        $task = $this->db->fetch(
            "SELECT pt.*, po.order_number, po.status as order_status,
                    v.sku as variant_sku, v.name as variant_name,
                    u.username as assigned_name
             FROM production_tasks pt
             JOIN production_orders po ON pt.order_id = po.id
             JOIN variants v ON po.variant_id = v.id
             LEFT JOIN users u ON pt.assigned_to = u.id
             WHERE pt.id = ?",
            [$id]
        );

        if (!$task) {
            $this->notFound();
        }

        // Get workers for assignment
        $workers = $this->db->fetchAll(
            "SELECT id, username FROM users WHERE is_active = 1 ORDER BY username"
        );

        $this->render('production/tasks/show', [
            'title' => "Task: {$task['name']}",
            'task' => $task,
            'workers' => $workers
        ]);
    }

    /**
     * Start task
     */
    public function start(string $id): void
    {
        $this->requirePermission('production.tasks.edit');
        $this->validateCSRF();

        $task = $this->db->fetch("SELECT * FROM production_tasks WHERE id = ?", [$id]);
        if (!$task) {
            $this->notFound();
        }

        if ($task['status'] !== 'pending') {
            $this->session->setFlash('error', 'Task cannot be started');
            $this->redirect("/production/tasks/{$id}");
            return;
        }

        $this->db->update('production_tasks', [
            'status' => 'in_progress',
            'actual_start' => date('Y-m-d H:i:s'),
            'assigned_to' => $_POST['assigned_to'] ?: $this->user()['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('production_task.started', 'production_tasks', $id, ['status' => $task['status']], ['status' => 'in_progress']);
        $this->session->setFlash('success', 'Task started');
        $this->redirect("/production/tasks/{$id}");
    }

    /**
     * Complete task
     */
    public function complete(string $id): void
    {
        $this->requirePermission('production.tasks.edit');
        $this->validateCSRF();

        $task = $this->db->fetch("SELECT * FROM production_tasks WHERE id = ?", [$id]);
        if (!$task) {
            $this->notFound();
        }

        if ($task['status'] !== 'in_progress') {
            $this->session->setFlash('error', 'Task cannot be completed');
            $this->redirect("/production/tasks/{$id}");
            return;
        }

        $completedQty = (float)($_POST['completed_quantity'] ?? $task['planned_quantity']);

        $this->db->update('production_tasks', [
            'status' => 'completed',
            'completed_quantity' => $completedQty,
            'actual_end' => date('Y-m-d H:i:s'),
            'notes' => trim($_POST['notes'] ?? $task['notes']),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('production_task.completed', 'production_tasks', $id, ['status' => $task['status']], ['status' => 'completed']);
        $this->session->setFlash('success', 'Task completed');
        $this->redirect("/production/orders/{$task['order_id']}");
    }
}
