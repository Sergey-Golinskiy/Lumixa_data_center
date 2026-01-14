<?php
/**
 * PrintQueueController - 3D Print queue management
 */

namespace App\Controllers\Production;

use App\Core\Controller;

class PrintQueueController extends Controller
{
    /**
     * List print jobs
     */
    public function index(): void
    {
        $this->requirePermission('production.print-queue.view');

        $status = $_GET['status'] ?? '';
        $printer = $_GET['printer'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($status) {
            $where[] = "pq.status = ?";
            $params[] = $status;
        }

        if ($printer) {
            $where[] = "pq.printer = ?";
            $params[] = $printer;
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn("SELECT COUNT(*) FROM print_queue pq WHERE {$whereClause}", $params);

        $offset = ($page - 1) * $perPage;
        $jobs = $this->db()->fetchAll(
            "SELECT pq.*, v.sku as variant_sku, v.name as variant_name,
                    po.order_number
             FROM print_queue pq
             LEFT JOIN variants v ON pq.variant_id = v.id
             LEFT JOIN production_orders po ON pq.order_id = po.id
             WHERE {$whereClause}
             ORDER BY pq.status = 'printing' DESC, pq.priority DESC, pq.created_at
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $printers = $this->db()->fetchAll("SELECT DISTINCT printer FROM print_queue WHERE printer IS NOT NULL ORDER BY printer");

        $this->render('production/print-queue/index', [
            'title' => 'Print Queue',
            'jobs' => $jobs,
            'printers' => $printers,
            'status' => $status,
            'printer' => $printer,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show job details
     */
    public function show(string $id): void
    {
        $this->requirePermission('production.print-queue.view');

        $job = $this->db()->fetch(
            "SELECT pq.*, v.sku as variant_sku, v.name as variant_name,
                    po.order_number, u.username as created_by_name
             FROM print_queue pq
             LEFT JOIN variants v ON pq.variant_id = v.id
             LEFT JOIN production_orders po ON pq.order_id = po.id
             LEFT JOIN users u ON pq.created_by = u.id
             WHERE pq.id = ?",
            [$id]
        );

        if (!$job) {
            $this->notFound();
        }

        $printers = $this->db()->fetchAll("SELECT * FROM printers WHERE is_active = 1 ORDER BY code");

        $this->render('production/print-queue/show', [
            'title' => "Print Job: {$job['job_number']}",
            'job' => $job,
            'printers' => $printers
        ]);
    }

    /**
     * Create print job form
     */
    public function create(): void
    {
        $this->requirePermission('production.print-queue.create');

        $variants = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name FROM variants v WHERE v.is_active = 1 ORDER BY v.sku"
        );

        $orders = $this->db()->fetchAll(
            "SELECT po.id, po.order_number, v.sku
             FROM production_orders po
             JOIN variants v ON po.variant_id = v.id
             WHERE po.status IN ('planned', 'in_progress')
             ORDER BY po.order_number DESC"
        );

        $printers = $this->db()->fetchAll("SELECT * FROM printers WHERE is_active = 1 ORDER BY code");

        $this->render('production/print-queue/form', [
            'title' => 'Create Print Job',
            'job' => null,
            'variants' => $variants,
            'orders' => $orders,
            'printers' => $printers
        ]);
    }

    /**
     * Store new print job
     */
    public function store(): void
    {
        $this->requirePermission('production.print-queue.create');
        $this->validateCSRF();

        $jobNumber = $this->generateJobNumber();

        $data = [
            'job_number' => $jobNumber,
            'order_id' => $_POST['order_id'] ?: null,
            'variant_id' => $_POST['variant_id'] ?: null,
            'printer' => trim($_POST['printer'] ?? ''),
            'material' => trim($_POST['material'] ?? ''),
            'quantity' => max(1, (int)($_POST['quantity'] ?? 1)),
            'estimated_time_minutes' => (int)($_POST['estimated_time'] ?? 0),
            'file_path' => trim($_POST['file_path'] ?? ''),
            'priority' => (int)($_POST['priority'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
            'status' => 'queued',
            'created_by' => $this->user()['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db()->insert('print_queue', $data);

        $this->audit('print_job.created', 'print_queue', $id, null, $data);
        $this->session->setFlash('success', 'Print job created');
        $this->redirect("/production/print-queue/{$id}");
    }

    /**
     * Start printing
     */
    public function start(string $id): void
    {
        $this->requirePermission('production.print-queue.edit');
        $this->validateCSRF();

        $job = $this->db()->fetch("SELECT * FROM print_queue WHERE id = ?", [$id]);
        if (!$job) {
            $this->notFound();
        }

        if ($job['status'] !== 'queued') {
            $this->session->setFlash('error', 'Job cannot be started');
            $this->redirect("/production/print-queue/{$id}");
            return;
        }

        $printer = trim($_POST['printer'] ?? $job['printer']);

        $this->db()->update('print_queue', [
            'status' => 'printing',
            'printer' => $printer,
            'started_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('print_job.started', 'print_queue', $id, ['status' => $job['status']], ['status' => 'printing']);
        $this->session->setFlash('success', 'Print job started');
        $this->redirect("/production/print-queue/{$id}");
    }

    /**
     * Complete print job
     */
    public function complete(string $id): void
    {
        $this->requirePermission('production.print-queue.edit');
        $this->validateCSRF();

        $job = $this->db()->fetch("SELECT * FROM print_queue WHERE id = ?", [$id]);
        if (!$job) {
            $this->notFound();
        }

        if ($job['status'] !== 'printing') {
            $this->session->setFlash('error', 'Job cannot be completed');
            $this->redirect("/production/print-queue/{$id}");
            return;
        }

        $actualTime = (int)($_POST['actual_time'] ?? 0);
        if (!$actualTime && $job['started_at']) {
            $start = strtotime($job['started_at']);
            $actualTime = round((time() - $start) / 60);
        }

        $this->db()->update('print_queue', [
            'status' => 'completed',
            'actual_time_minutes' => $actualTime,
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('print_job.completed', 'print_queue', $id, ['status' => $job['status']], ['status' => 'completed']);
        $this->session->setFlash('success', 'Print job completed');
        $this->redirect("/production/print-queue/{$id}");
    }

    /**
     * Cancel print job
     */
    public function cancel(string $id): void
    {
        $this->requirePermission('production.print-queue.edit');
        $this->validateCSRF();

        $job = $this->db()->fetch("SELECT * FROM print_queue WHERE id = ?", [$id]);
        if (!$job) {
            $this->notFound();
        }

        if ($job['status'] === 'completed') {
            $this->session->setFlash('error', 'Completed jobs cannot be cancelled');
            $this->redirect("/production/print-queue/{$id}");
            return;
        }

        $this->db()->update('print_queue', [
            'status' => 'cancelled',
            'notes' => $job['notes'] . "\n[Cancelled: " . trim($_POST['reason'] ?? '') . "]",
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('print_job.cancelled', 'print_queue', $id, ['status' => $job['status']], ['status' => 'cancelled']);
        $this->session->setFlash('success', 'Print job cancelled');
        $this->redirect("/production/print-queue/{$id}");
    }

    /**
     * Generate job number
     */
    private function generateJobNumber(): string
    {
        $seq = $this->db()->fetch("SELECT * FROM document_sequences WHERE type = 'print_job' FOR UPDATE");

        if (!$seq) {
            $this->db()->insert('document_sequences', ['type' => 'print_job', 'prefix' => 'PJ', 'next_number' => 1]);
            return 'PJ-' . date('Ymd') . '-0001';
        }

        $number = $seq['prefix'] . '-' . date('Ymd') . '-' . str_pad($seq['next_number'], 4, '0', STR_PAD_LEFT);
        $this->db()->execute("UPDATE document_sequences SET next_number = next_number + 1 WHERE type = 'print_job'");

        return $number;
    }
}
