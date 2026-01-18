<?php
/**
 * DetailRoutingController - Routing/technology for details
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class DetailRoutingController extends Controller
{
    /**
     * List all detail routings
     */
    public function index(): void
    {
        $this->requirePermission('catalog.detail_routing.view');

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(d.sku LIKE ? OR dr.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $where[] = "dr.status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM detail_routing dr JOIN details d ON dr.detail_id = d.id WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $routings = $this->db()->fetchAll(
            "SELECT dr.*, d.sku as detail_sku, d.name as detail_name,
                    (SELECT COUNT(*) FROM detail_routing_operations WHERE routing_id = dr.id) as op_count,
                    (SELECT SUM(ro.setup_time_minutes + ro.run_time_minutes) FROM detail_routing_operations ro WHERE ro.routing_id = dr.id) as total_time,
                    (SELECT SUM(ro.labor_cost + ro.overhead_cost) FROM detail_routing_operations ro WHERE ro.routing_id = dr.id) as total_cost
             FROM detail_routing dr
             JOIN details d ON dr.detail_id = d.id
             WHERE {$whereClause}
             ORDER BY dr.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('catalog/detail-routing/index', [
            'title' => $this->app->getTranslator()->get('detail_routing'),
            'routings' => $routings,
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show detail routing
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.detail_routing.view');

        $routing = $this->db()->fetch(
            "SELECT dr.*, d.sku as detail_sku, d.name as detail_name,
                    u.name as created_by_name
             FROM detail_routing dr
             JOIN details d ON dr.detail_id = d.id
             LEFT JOIN users u ON dr.created_by = u.id
             WHERE dr.id = ?",
            [$id]
        );

        if (!$routing) {
            $this->notFound();
        }

        $operations = $this->db()->fetchAll(
            "SELECT * FROM detail_routing_operations WHERE routing_id = ? ORDER BY sort_order",
            [$id]
        );

        $this->render('catalog/detail-routing/show', [
            'title' => $this->app->getTranslator()->get('detail_routing_title', [
                'sku' => $routing['detail_sku'],
                'version' => $routing['version']
            ]),
            'routing' => $routing,
            'operations' => $operations
        ]);
    }

    /**
     * Create detail routing form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.detail_routing.create');

        $detailId = $_GET['detail_id'] ?? '';

        $details = $this->db()->fetchAll(
            "SELECT id, sku, name FROM details WHERE is_active = 1 ORDER BY sku"
        );

        $workCenters = $this->db()->fetchAll(
            "SELECT code, name FROM work_centers WHERE is_active = 1 ORDER BY code"
        );

        $this->render('catalog/detail-routing/form', [
            'title' => $this->app->getTranslator()->get('create_detail_routing'),
            'routing' => null,
            'operations' => [],
            'details' => $details,
            'workCenters' => $workCenters,
            'preselectedDetailId' => $detailId
        ]);
    }

    /**
     * Store detail routing
     */
    public function store(): void
    {
        $this->requirePermission('catalog.detail_routing.create');
        $this->validateCSRF();

        $data = [
            'detail_id' => (int)($_POST['detail_id'] ?? 0),
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $imagePath = $this->storeImageUpload('image', 'detail-routing');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $errors = [];

        if (empty($data['detail_id'])) {
            $errors['detail_id'] = $this->app->getTranslator()->get('detail_required');
        }

        if (empty($data['version'])) {
            $errors['version'] = $this->app->getTranslator()->get('version_required');
        }

        $operations = $this->parseOperations();
        if (empty($operations)) {
            $errors['operations'] = $this->app->getTranslator()->get('routing_operation_required');
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/detail-routing/create?detail_id=' . $data['detail_id']);
            return;
        }

        $this->db()->beginTransaction();

        try {
            $routingId = $this->db()->insert('detail_routing', array_merge($data, [
                'status' => 'draft',
                'created_by' => $this->user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]));

            foreach ($operations as $i => $op) {
                $this->db()->insert('detail_routing_operations', [
                    'routing_id' => $routingId,
                    'operation_number' => $op['operation_number'],
                    'name' => $op['name'],
                    'work_center' => $op['work_center'],
                    'setup_time_minutes' => $op['setup_time'],
                    'run_time_minutes' => $op['run_time'],
                    'labor_cost' => $op['labor_cost'],
                    'overhead_cost' => $op['overhead_cost'],
                    'instructions' => $op['instructions'],
                    'sort_order' => $i + 1
                ]);
            }

            $this->db()->commit();
            $this->audit('detail_routing.created', 'detail_routing', $routingId, null, $data);
            $this->session->setFlash('success', $this->app->getTranslator()->get('detail_routing_created_success'));
            $this->redirect("/catalog/detail-routing/{$routingId}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_create_failed', ['error' => $e->getMessage()]));
            $this->redirect('/catalog/detail-routing/create?detail_id=' . $data['detail_id']);
        }
    }

    /**
     * Edit detail routing form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.detail_routing.edit');

        $routing = $this->db()->fetch(
            "SELECT dr.*, d.sku as detail_sku, d.name as detail_name
             FROM detail_routing dr
             JOIN details d ON dr.detail_id = d.id
             WHERE dr.id = ?",
            [$id]
        );

        if (!$routing) {
            $this->notFound();
        }

        if ($routing['status'] !== 'draft') {
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_edit_draft_only'));
            $this->redirect("/catalog/detail-routing/{$id}");
            return;
        }

        $operations = $this->db()->fetchAll(
            "SELECT * FROM detail_routing_operations WHERE routing_id = ? ORDER BY sort_order",
            [$id]
        );

        $details = $this->db()->fetchAll(
            "SELECT id, sku, name FROM details WHERE is_active = 1 ORDER BY sku"
        );

        $workCenters = $this->db()->fetchAll(
            "SELECT code, name FROM work_centers WHERE is_active = 1 ORDER BY code"
        );

        $this->render('catalog/detail-routing/form', [
            'title' => $this->app->getTranslator()->get('detail_routing_edit_title', [
                'sku' => $routing['detail_sku'],
                'version' => $routing['version']
            ]),
            'routing' => $routing,
            'operations' => $operations,
            'details' => $details,
            'workCenters' => $workCenters,
            'preselectedDetailId' => $routing['detail_id']
        ]);
    }

    /**
     * Update detail routing
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.detail_routing.edit');
        $this->validateCSRF();

        $routing = $this->db()->fetch("SELECT * FROM detail_routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        if ($routing['status'] !== 'draft') {
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_edit_draft_only'));
            $this->redirect("/catalog/detail-routing/{$id}");
            return;
        }

        $data = [
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $imagePath = $this->storeImageUpload('image', 'detail-routing');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $operations = $this->parseOperations();
        if (empty($operations)) {
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_operation_required'));
            $this->redirect("/catalog/detail-routing/{$id}/edit");
            return;
        }

        $this->db()->beginTransaction();

        try {
            $this->db()->update('detail_routing', array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]), ['id' => $id]);

            $this->db()->delete('detail_routing_operations', ['routing_id' => $id]);

            foreach ($operations as $i => $op) {
                $this->db()->insert('detail_routing_operations', [
                    'routing_id' => $id,
                    'operation_number' => $op['operation_number'],
                    'name' => $op['name'],
                    'work_center' => $op['work_center'],
                    'setup_time_minutes' => $op['setup_time'],
                    'run_time_minutes' => $op['run_time'],
                    'labor_cost' => $op['labor_cost'],
                    'overhead_cost' => $op['overhead_cost'],
                    'instructions' => $op['instructions'],
                    'sort_order' => $i + 1
                ]);
            }

            $this->db()->commit();
            $this->audit('detail_routing.updated', 'detail_routing', $id, $routing, $data);
            $this->session->setFlash('success', $this->app->getTranslator()->get('detail_routing_updated_success'));
            $this->redirect("/catalog/detail-routing/{$id}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_update_failed', ['error' => $e->getMessage()]));
            $this->redirect("/catalog/detail-routing/{$id}/edit");
        }
    }

    /**
     * Activate detail routing
     */
    public function activate(string $id): void
    {
        $this->requirePermission('catalog.detail_routing.activate');
        $this->validateCSRF();

        $routing = $this->db()->fetch("SELECT * FROM detail_routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        $this->db()->beginTransaction();

        try {
            $this->db()->execute(
                "UPDATE detail_routing SET status = 'archived', updated_at = NOW() WHERE detail_id = ? AND status = 'active'",
                [$routing['detail_id']]
            );

            $this->db()->update('detail_routing', [
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            $this->db()->commit();
            $this->audit('detail_routing.activated', 'detail_routing', $id, ['status' => $routing['status']], ['status' => 'active']);
            $this->session->setFlash('success', $this->app->getTranslator()->get('detail_routing_activated_success'));

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('routing_activate_failed', ['error' => $e->getMessage()]));
        }

        $this->redirect("/catalog/detail-routing/{$id}");
    }

    /**
     * Archive detail routing
     */
    public function archive(string $id): void
    {
        $this->requirePermission('catalog.detail_routing.edit');
        $this->validateCSRF();

        $routing = $this->db()->fetch("SELECT * FROM detail_routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        $this->db()->update('detail_routing', [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('detail_routing.archived', 'detail_routing', $id, ['status' => $routing['status']], ['status' => 'archived']);
        $this->session->setFlash('success', $this->app->getTranslator()->get('detail_routing_archived_success'));
        $this->redirect("/catalog/detail-routing/{$id}");
    }

    private function parseOperations(): array
    {
        $result = [];
        $opsData = $_POST['ops'] ?? [];

        foreach ($opsData as $op) {
            if (empty($op['name'])) {
                continue;
            }

            $result[] = [
                'operation_number' => (int)($op['op_number'] ?? 10),
                'name' => trim($op['name']),
                'work_center' => trim($op['work_center'] ?? ''),
                'setup_time' => (int)($op['setup_time'] ?? 0),
                'run_time' => (int)($op['run_time'] ?? 0),
                'labor_cost' => (float)($op['labor_cost'] ?? 0),
                'overhead_cost' => (float)($op['overhead_cost'] ?? 0),
                'instructions' => trim($op['instructions'] ?? '')
            ];
        }

        return $result;
    }
}
