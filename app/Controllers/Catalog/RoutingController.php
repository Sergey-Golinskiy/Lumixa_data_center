<?php
/**
 * RoutingController - Production routing/operations management
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class RoutingController extends Controller
{
    /**
     * List all routings
     */
    public function index(): void
    {
        $this->requirePermission('catalog.routing.view');

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(v.sku LIKE ? OR r.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $where[] = "r.status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Count
        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM routing r JOIN variants v ON r.variant_id = v.id WHERE {$whereClause}",
            $params
        );

        // Get routings
        $offset = ($page - 1) * $perPage;
        $routings = $this->db->fetchAll(
            "SELECT r.*, v.sku as variant_sku, v.name as variant_name,
                    (SELECT COUNT(*) FROM routing_operations WHERE routing_id = r.id) as op_count,
                    (SELECT SUM(ro.setup_time_minutes + ro.run_time_minutes) FROM routing_operations ro WHERE ro.routing_id = r.id) as total_time,
                    (SELECT SUM(ro.labor_cost + ro.overhead_cost) FROM routing_operations ro WHERE ro.routing_id = r.id) as total_cost
             FROM routing r
             JOIN variants v ON r.variant_id = v.id
             WHERE {$whereClause}
             ORDER BY r.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('catalog/routing/index', [
            'title' => 'Routing',
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
     * Show routing details
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.routing.view');

        $routing = $this->db->fetch(
            "SELECT r.*, v.sku as variant_sku, v.name as variant_name,
                    u.username as created_by_name
             FROM routing r
             JOIN variants v ON r.variant_id = v.id
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.id = ?",
            [$id]
        );

        if (!$routing) {
            $this->notFound();
        }

        // Get operations
        $operations = $this->db->fetchAll(
            "SELECT * FROM routing_operations WHERE routing_id = ? ORDER BY sort_order",
            [$id]
        );

        $this->render('catalog/routing/show', [
            'title' => "Routing: {$routing['variant_sku']} v{$routing['version']}",
            'routing' => $routing,
            'operations' => $operations
        ]);
    }

    /**
     * Create routing form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.routing.create');

        $variantId = $_GET['variant_id'] ?? '';

        $variants = $this->db->fetchAll(
            "SELECT v.id, v.sku, v.name FROM variants v WHERE v.is_active = 1 ORDER BY v.sku"
        );

        $workCenters = $this->db->fetchAll(
            "SELECT code, name FROM work_centers WHERE is_active = 1 ORDER BY code"
        );

        $this->render('catalog/routing/form', [
            'title' => 'Create Routing',
            'routing' => null,
            'operations' => [],
            'variants' => $variants,
            'workCenters' => $workCenters,
            'preselectedVariantId' => $variantId
        ]);
    }

    /**
     * Store new routing
     */
    public function store(): void
    {
        $this->requirePermission('catalog.routing.create');
        $this->validateCSRF();

        $data = [
            'variant_id' => (int)($_POST['variant_id'] ?? 0),
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];

        // Validation
        $errors = [];

        if (empty($data['variant_id'])) {
            $errors['variant_id'] = 'Variant is required';
        }

        if (empty($data['version'])) {
            $errors['version'] = 'Version is required';
        }

        $operations = $this->parseOperations();
        if (empty($operations)) {
            $errors['operations'] = 'At least one operation is required';
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/routing/create?variant_id=' . $data['variant_id']);
            return;
        }

        $this->db->beginTransaction();

        try {
            $routingId = $this->db->insert('routing', array_merge($data, [
                'status' => 'draft',
                'created_by' => $this->user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]));

            foreach ($operations as $i => $op) {
                $this->db->insert('routing_operations', [
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

            $this->db->commit();
            $this->audit('routing.created', 'routing', $routingId, null, $data);
            $this->session->setFlash('success', 'Routing created successfully');
            $this->redirect("/catalog/routing/{$routingId}");

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('error', 'Failed to create routing: ' . $e->getMessage());
            $this->redirect('/catalog/routing/create?variant_id=' . $data['variant_id']);
        }
    }

    /**
     * Edit routing form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.routing.edit');

        $routing = $this->db->fetch(
            "SELECT r.*, v.sku as variant_sku, v.name as variant_name
             FROM routing r
             JOIN variants v ON r.variant_id = v.id
             WHERE r.id = ?",
            [$id]
        );

        if (!$routing) {
            $this->notFound();
        }

        if ($routing['status'] !== 'draft') {
            $this->session->setFlash('error', 'Only draft routings can be edited');
            $this->redirect("/catalog/routing/{$id}");
            return;
        }

        $operations = $this->db->fetchAll(
            "SELECT * FROM routing_operations WHERE routing_id = ? ORDER BY sort_order",
            [$id]
        );

        $variants = $this->db->fetchAll(
            "SELECT v.id, v.sku, v.name FROM variants v WHERE v.is_active = 1 ORDER BY v.sku"
        );

        $workCenters = $this->db->fetchAll(
            "SELECT code, name FROM work_centers WHERE is_active = 1 ORDER BY code"
        );

        $this->render('catalog/routing/form', [
            'title' => "Edit Routing: {$routing['variant_sku']} v{$routing['version']}",
            'routing' => $routing,
            'operations' => $operations,
            'variants' => $variants,
            'workCenters' => $workCenters,
            'preselectedVariantId' => $routing['variant_id']
        ]);
    }

    /**
     * Update routing
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.routing.edit');
        $this->validateCSRF();

        $routing = $this->db->fetch("SELECT * FROM routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        if ($routing['status'] !== 'draft') {
            $this->session->setFlash('error', 'Only draft routings can be edited');
            $this->redirect("/catalog/routing/{$id}");
            return;
        }

        $data = [
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];

        $operations = $this->parseOperations();
        if (empty($operations)) {
            $this->session->setFlash('error', 'At least one operation is required');
            $this->redirect("/catalog/routing/{$id}/edit");
            return;
        }

        $this->db->beginTransaction();

        try {
            $this->db->update('routing', array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]), ['id' => $id]);

            // Delete and recreate operations
            $this->db->delete('routing_operations', ['routing_id' => $id]);

            foreach ($operations as $i => $op) {
                $this->db->insert('routing_operations', [
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

            $this->db->commit();
            $this->audit('routing.updated', 'routing', $id, $routing, $data);
            $this->session->setFlash('success', 'Routing updated');
            $this->redirect("/catalog/routing/{$id}");

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('error', 'Failed to update routing: ' . $e->getMessage());
            $this->redirect("/catalog/routing/{$id}/edit");
        }
    }

    /**
     * Activate routing
     */
    public function activate(string $id): void
    {
        $this->requirePermission('catalog.routing.activate');
        $this->validateCSRF();

        $routing = $this->db->fetch("SELECT * FROM routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        $this->db->beginTransaction();

        try {
            // Deactivate other routings for same variant
            $this->db->execute(
                "UPDATE routing SET status = 'archived', updated_at = NOW() WHERE variant_id = ? AND status = 'active'",
                [$routing['variant_id']]
            );

            // Activate this one
            $this->db->update('routing', [
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            // Recalculate variant costs
            $this->recalculateVariantCost($routing['variant_id']);

            $this->db->commit();
            $this->audit('routing.activated', 'routing', $id, ['status' => $routing['status']], ['status' => 'active']);
            $this->session->setFlash('success', 'Routing activated');

        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->session->setFlash('error', 'Failed to activate routing: ' . $e->getMessage());
        }

        $this->redirect("/catalog/routing/{$id}");
    }

    /**
     * Archive routing
     */
    public function archive(string $id): void
    {
        $this->requirePermission('catalog.routing.edit');
        $this->validateCSRF();

        $routing = $this->db->fetch("SELECT * FROM routing WHERE id = ?", [$id]);
        if (!$routing) {
            $this->notFound();
        }

        $this->db->update('routing', [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('routing.archived', 'routing', $id, ['status' => $routing['status']], ['status' => 'archived']);
        $this->session->setFlash('success', 'Routing archived');
        $this->redirect("/catalog/routing/{$id}");
    }

    /**
     * Parse operations from POST data
     */
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

    /**
     * Recalculate variant costs
     */
    private function recalculateVariantCost(int $variantId): void
    {
        // Get active BOM material cost
        $bom = $this->db->fetch(
            "SELECT id FROM bom WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        $materialCost = 0;
        if ($bom) {
            $materialCost = (float)$this->db->fetchColumn(
                "SELECT SUM(quantity * unit_cost * (1 + waste_percent/100)) FROM bom_lines WHERE bom_id = ?",
                [$bom['id']]
            );
        }

        // Get active routing costs
        $routing = $this->db->fetch(
            "SELECT id FROM routing WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        $laborCost = 0;
        $overheadCost = 0;
        if ($routing) {
            $costs = $this->db->fetch(
                "SELECT SUM(labor_cost) as labor, SUM(overhead_cost) as overhead FROM routing_operations WHERE routing_id = ?",
                [$routing['id']]
            );
            $laborCost = (float)($costs['labor'] ?? 0);
            $overheadCost = (float)($costs['overhead'] ?? 0);
        }

        // Update variant cost
        $existing = $this->db->fetch("SELECT id FROM variant_costs WHERE variant_id = ?", [$variantId]);

        $costData = [
            'variant_id' => $variantId,
            'bom_id' => $bom['id'] ?? null,
            'routing_id' => $routing['id'] ?? null,
            'material_cost' => $materialCost,
            'labor_cost' => $laborCost,
            'overhead_cost' => $overheadCost,
            'total_cost' => $materialCost + $laborCost + $overheadCost,
            'calculated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->update('variant_costs', $costData, ['id' => $existing['id']]);
        } else {
            $this->db->insert('variant_costs', $costData);
        }
    }
}
