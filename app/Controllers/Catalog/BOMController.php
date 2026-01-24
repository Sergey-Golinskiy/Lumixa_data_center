<?php
/**
 * BOMController - Bill of Materials management
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class BOMController extends Controller
{
    /**
     * List all BOMs
     */
    public function index(): void
    {
        $this->requirePermission('catalog.bom.view');

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $variantId = $_GET['variant_id'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(v.sku LIKE ? OR b.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $where[] = "b.status = ?";
            $params[] = $status;
        }

        if ($variantId) {
            $where[] = "b.variant_id = ?";
            $params[] = $variantId;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM bom b JOIN variants v ON b.variant_id = v.id WHERE {$whereClause}",
            $params
        );

        // Get BOMs
        $offset = ($page - 1) * $perPage;
        $boms = $this->db()->fetchAll(
            "SELECT b.*, v.sku as variant_sku, v.name as variant_name,
                    (SELECT COUNT(*) FROM bom_lines WHERE bom_id = b.id) as line_count,
                    (SELECT SUM(bl.quantity * bl.unit_cost) FROM bom_lines bl WHERE bl.bom_id = b.id) as total_cost
             FROM bom b
             JOIN variants v ON b.variant_id = v.id
             WHERE {$whereClause}
             ORDER BY b.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('catalog/bom/index', [
            'title' => $this->app->getTranslator()->get('bom'),
            'boms' => $boms,
            'search' => $search,
            'status' => $status,
            'variantId' => $variantId,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show BOM details
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.bom.view');

        $bom = $this->db()->fetch(
            "SELECT b.*, v.sku as variant_sku, v.name as variant_name, v.product_id,
                    u.name as created_by_name
             FROM bom b
             JOIN variants v ON b.variant_id = v.id
             LEFT JOIN users u ON b.created_by = u.id
             WHERE b.id = ?",
            [$id]
        );

        if (!$bom) {
            $this->notFound();
        }

        // Get lines
        $lines = $this->db()->fetchAll(
            "SELECT bl.*, i.sku, i.name as item_name, i.unit,
                    dc.id as config_id, dc.sku as config_sku, dc.name as config_name,
                    dc.material_color, iov.name as material_name
             FROM bom_lines bl
             JOIN items i ON bl.item_id = i.id
             LEFT JOIN detail_configurations dc ON bl.detail_configuration_id = dc.id
             LEFT JOIN item_option_values iov ON dc.material_id = iov.id
             WHERE bl.bom_id = ?
             ORDER BY bl.sort_order",
            [$id]
        );

        $this->render('catalog/bom/show', [
            'title' => $this->app->getTranslator()->get('bom_title', [
                'sku' => $bom['variant_sku'],
                'version' => $bom['version']
            ]),
            'bom' => $bom,
            'lines' => $lines
        ]);
    }

    /**
     * Create BOM form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.bom.create');

        $variantId = $_GET['variant_id'] ?? '';

        $variants = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name, p.name as product_name
             FROM variants v
             JOIN products p ON v.product_id = p.id
             WHERE v.is_active = 1
             ORDER BY v.sku"
        );

        $items = $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name, i.unit, d.id as detail_id
             FROM items i
             LEFT JOIN details d ON i.id = d.item_id
             WHERE i.is_active = 1
             ORDER BY i.sku"
        );

        $this->render('catalog/bom/form', [
            'title' => $this->app->getTranslator()->get('create_bom'),
            'bom' => null,
            'lines' => [],
            'variants' => $variants,
            'items' => $items,
            'preselectedVariantId' => $variantId
        ]);
    }

    /**
     * Store new BOM
     */
    public function store(): void
    {
        $this->requirePermission('catalog.bom.create');
        $this->validateCSRF();

        $data = [
            'variant_id' => (int)($_POST['variant_id'] ?? 0),
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $imagePath = $this->storeImageUpload('image', 'bom');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        // Validation
        $errors = [];

        if (empty($data['variant_id'])) {
            $errors['variant_id'] = $this->app->getTranslator()->get('variant_required');
        }

        if (empty($data['version'])) {
            $errors['version'] = $this->app->getTranslator()->get('version_required');
        }

        // Parse lines
        $lines = $this->parseLines();
        if (empty($lines)) {
            $errors['lines'] = $this->app->getTranslator()->get('bom_lines_required');
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/bom/create?variant_id=' . $data['variant_id']);
            return;
        }

        $this->db()->beginTransaction();

        try {
            // Create BOM
            $bomId = $this->db()->insert('bom', array_merge($data, [
                'status' => 'draft',
                'created_by' => $this->user()['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]));

            // Create lines
            foreach ($lines as $i => $line) {
                $lineData = [
                    'bom_id' => $bomId,
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'waste_percent' => $line['waste_percent'],
                    'notes' => $line['notes'],
                    'sort_order' => $i + 1
                ];

                // Add detail_configuration_id if present
                if (isset($line['detail_configuration_id'])) {
                    $lineData['detail_configuration_id'] = $line['detail_configuration_id'];
                }

                $this->db()->insert('bom_lines', $lineData);
            }

            $this->db()->commit();
            $this->audit('bom.created', 'bom', $bomId, null, $data);
            $this->session->setFlash('success', $this->app->getTranslator()->get('bom_created_success'));
            $this->redirect("/catalog/bom/{$bomId}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_create_failed', ['error' => $e->getMessage()]));
            $this->redirect('/catalog/bom/create?variant_id=' . $data['variant_id']);
        }
    }

    /**
     * Edit BOM form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.bom.edit');

        $bom = $this->db()->fetch(
            "SELECT b.*, v.sku as variant_sku, v.name as variant_name
             FROM bom b
             JOIN variants v ON b.variant_id = v.id
             WHERE b.id = ?",
            [$id]
        );

        if (!$bom) {
            $this->notFound();
        }

        if ($bom['status'] !== 'draft') {
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_edit_draft_only'));
            $this->redirect("/catalog/bom/{$id}");
            return;
        }

        $lines = $this->db()->fetchAll(
            "SELECT bl.*, i.sku, i.name as item_name, bl.detail_configuration_id
             FROM bom_lines bl
             JOIN items i ON bl.item_id = i.id
             WHERE bl.bom_id = ?
             ORDER BY bl.sort_order",
            [$id]
        );

        $variants = $this->db()->fetchAll(
            "SELECT v.id, v.sku, v.name FROM variants v WHERE v.is_active = 1 ORDER BY v.sku"
        );

        $items = $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name, i.unit, d.id as detail_id
             FROM items i
             LEFT JOIN details d ON i.id = d.item_id
             WHERE i.is_active = 1
             ORDER BY i.sku"
        );

        $this->render('catalog/bom/form', [
            'title' => $this->app->getTranslator()->get('bom_edit_title', [
                'sku' => $bom['variant_sku'],
                'version' => $bom['version']
            ]),
            'bom' => $bom,
            'lines' => $lines,
            'variants' => $variants,
            'items' => $items,
            'preselectedVariantId' => $bom['variant_id']
        ]);
    }

    /**
     * Update BOM
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.bom.edit');
        $this->validateCSRF();

        $bom = $this->db()->fetch("SELECT * FROM bom WHERE id = ?", [$id]);
        if (!$bom) {
            $this->notFound();
        }

        if ($bom['status'] !== 'draft') {
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_edit_draft_only'));
            $this->redirect("/catalog/bom/{$id}");
            return;
        }

        $data = [
            'version' => trim($_POST['version'] ?? '1.0'),
            'name' => trim($_POST['name'] ?? ''),
            'effective_date' => $_POST['effective_date'] ?: null,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $imagePath = $this->storeImageUpload('image', 'bom');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $lines = $this->parseLines();
        if (empty($lines)) {
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_lines_required'));
            $this->redirect("/catalog/bom/{$id}/edit");
            return;
        }

        $this->db()->beginTransaction();

        try {
            // Update BOM
            $this->db()->update('bom', array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]), ['id' => $id]);

            // Delete old lines and recreate
            $this->db()->delete('bom_lines', ['bom_id' => $id]);

            foreach ($lines as $i => $line) {
                $lineData = [
                    'bom_id' => $id,
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'],
                    'waste_percent' => $line['waste_percent'],
                    'notes' => $line['notes'],
                    'sort_order' => $i + 1
                ];

                // Add detail_configuration_id if present
                if (isset($line['detail_configuration_id'])) {
                    $lineData['detail_configuration_id'] = $line['detail_configuration_id'];
                }

                $this->db()->insert('bom_lines', $lineData);
            }

            $this->db()->commit();
            $this->audit('bom.updated', 'bom', $id, $bom, $data);
            $this->session->setFlash('success', $this->app->getTranslator()->get('bom_updated_success'));
            $this->redirect("/catalog/bom/{$id}");

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_update_failed', ['error' => $e->getMessage()]));
            $this->redirect("/catalog/bom/{$id}/edit");
        }
    }

    /**
     * Activate BOM
     */
    public function activate(string $id): void
    {
        $this->requirePermission('catalog.bom.activate');
        $this->validateCSRF();

        $bom = $this->db()->fetch("SELECT * FROM bom WHERE id = ?", [$id]);
        if (!$bom) {
            $this->notFound();
        }

        if ($bom['status'] === 'active') {
            $this->session->setFlash('info', $this->app->getTranslator()->get('bom_already_active'));
            $this->redirect("/catalog/bom/{$id}");
            return;
        }

        $this->db()->beginTransaction();

        try {
            // Deactivate other BOMs for same variant
            $this->db()->execute(
                "UPDATE bom SET status = 'archived', updated_at = NOW() WHERE variant_id = ? AND status = 'active'",
                [$bom['variant_id']]
            );

            // Activate this BOM
            $this->db()->update('bom', [
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            // Recalculate variant costs
            $this->recalculateVariantCost($bom['variant_id']);

            $this->db()->commit();
            $this->audit('bom.activated', 'bom', $id, ['status' => $bom['status']], ['status' => 'active']);
            $this->session->setFlash('success', $this->app->getTranslator()->get('bom_activate_success'));

        } catch (\Exception $e) {
            $this->db()->rollBack();
            $this->session->setFlash('error', $this->app->getTranslator()->get('bom_activate_failed', ['error' => $e->getMessage()]));
        }

        $this->redirect("/catalog/bom/{$id}");
    }

    /**
     * Archive BOM
     */
    public function archive(string $id): void
    {
        $this->requirePermission('catalog.bom.edit');
        $this->validateCSRF();

        $bom = $this->db()->fetch("SELECT * FROM bom WHERE id = ?", [$id]);
        if (!$bom) {
            $this->notFound();
        }

        $this->db()->update('bom', [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('bom.archived', 'bom', $id, ['status' => $bom['status']], ['status' => 'archived']);
        $this->session->setFlash('success', $this->app->getTranslator()->get('bom_archived_success'));
        $this->redirect("/catalog/bom/{$id}");
    }

    /**
     * Parse BOM lines from POST data
     */
    private function parseLines(): array
    {
        $result = [];
        $linesData = $_POST['lines'] ?? [];

        foreach ($linesData as $line) {
            if (empty($line['item_id']) || empty($line['quantity'])) {
                continue;
            }

            $lineData = [
                'item_id' => (int)$line['item_id'],
                'quantity' => (float)$line['quantity'],
                'unit_cost' => (float)($line['unit_cost'] ?? 0),
                'waste_percent' => (float)($line['waste_percent'] ?? 0),
                'notes' => $line['notes'] ?? ''
            ];

            // Add detail_configuration_id if present
            if (!empty($line['detail_configuration_id'])) {
                $lineData['detail_configuration_id'] = (int)$line['detail_configuration_id'];
            }

            $result[] = $lineData;
        }

        return $result;
    }

    /**
     * Recalculate variant costs
     */
    private function recalculateVariantCost(int $variantId): void
    {
        // Get active BOM
        $bom = $this->db()->fetch(
            "SELECT id FROM bom WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        $materialCost = 0;
        if ($bom) {
            $materialCost = (float)$this->db()->fetchColumn(
                "SELECT SUM(quantity * unit_cost * (1 + waste_percent/100)) FROM bom_lines WHERE bom_id = ?",
                [$bom['id']]
            );
        }

        // Get active routing
        $routing = $this->db()->fetch(
            "SELECT id FROM routing WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$variantId]
        );

        $laborCost = 0;
        $overheadCost = 0;
        if ($routing) {
            $costs = $this->db()->fetch(
                "SELECT SUM(labor_cost) as labor, SUM(overhead_cost) as overhead FROM routing_operations WHERE routing_id = ?",
                [$routing['id']]
            );
            $laborCost = (float)($costs['labor'] ?? 0);
            $overheadCost = (float)($costs['overhead'] ?? 0);
        }

        // Update or insert variant cost
        $existing = $this->db()->fetch("SELECT id FROM variant_costs WHERE variant_id = ?", [$variantId]);

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
            $this->db()->update('variant_costs', $costData, ['id' => $existing['id']]);
        } else {
            $this->db()->insert('variant_costs', $costData);
        }
    }
}
