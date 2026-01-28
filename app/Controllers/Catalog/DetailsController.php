<?php
/**
 * DetailsController - Catalog details management
 */

namespace App\Controllers\Catalog;

use App\Core\Application;
use App\Core\Controller;
use App\Services\Warehouse\ItemService;
use App\Services\Catalog\DetailCostingService;

class DetailsController extends Controller
{
    private ItemService $itemService;
    private DetailCostingService $costingService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->itemService = new ItemService($app);
        $this->costingService = new DetailCostingService($app);
    }

    /**
     * List details
     */
    public function index(): void
    {
        $this->requirePermission('catalog.details.view');

        $search = $_GET['search'] ?? '';
        $detailType = $_GET['detail_type'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(d.sku LIKE ? OR d.name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($detailType) {
            $where[] = 'd.detail_type = ?';
            $params[] = $detailType;
        }

        $whereClause = implode(' AND ', $where);

        $total = (int)$this->db()->fetchColumn(
            "SELECT COUNT(*) FROM details d WHERE {$whereClause}",
            $params
        );

        $offset = ($page - 1) * $perPage;
        $details = $this->db()->fetchAll(
            "SELECT d.*, m.sku AS material_sku, m.name AS material_name,
                    MAX(CASE WHEN ia.attribute_name = 'manufacturer' THEN ia.attribute_value END) as material_manufacturer,
                    MAX(CASE WHEN ia.attribute_name = 'plastic_type' THEN ia.attribute_value END) as material_plastic_type,
                    MAX(CASE WHEN ia.attribute_name = 'color' THEN ia.attribute_value END) as material_color,
                    MAX(CASE WHEN ia.attribute_name = 'filament_alias' THEN ia.attribute_value END) as material_filament_alias
             FROM details d
             LEFT JOIN items m ON d.material_item_id = m.id
             LEFT JOIN item_attributes ia ON m.id = ia.item_id
             WHERE {$whereClause}
             GROUP BY d.id, m.id, m.sku, m.name
             ORDER BY d.sku
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Batch-load multi-material data for all details
        $detailIds = array_column($details, 'id');
        $allDetailMaterials = $this->getDetailMaterialsBatch($detailIds);

        // Fetch alias colors and calculate production cost for each detail
        $aliasColors = $this->getAliasColors();
        foreach ($details as &$detail) {
            $detail['production_cost'] = null;
            if ($detail['detail_type'] === 'printed') {
                $costData = $this->costingService->calculateCost($detail);
                $detail['production_cost'] = $costData['total_cost'] ?? 0;
            }
            // Attach multi-material data
            $detail['detail_materials'] = $allDetailMaterials[$detail['id']] ?? [];
            foreach ($detail['detail_materials'] as &$dm) {
                $dm['alias_color'] = $aliasColors[$dm['filament_alias'] ?? ''] ?? null;
            }
            unset($dm);
            // Legacy single alias color
            $alias = $detail['material_filament_alias'] ?? '';
            $detail['material_alias_color'] = $aliasColors[$alias] ?? null;
        }
        unset($detail);

        $this->render('catalog/details/index', [
            'title' => $this->app->getTranslator()->get('details'),
            'details' => $details,
            'search' => $search,
            'detailType' => $detailType,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show detail
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.details.view');

        $printerSelect = 'p.name AS printer_name';
        if ($this->db()->columnExists('printers', 'model')) {
            $printerSelect .= ', p.model AS printer_model';
        } else {
            $printerSelect .= ", NULL AS printer_model";
        }

        $detail = $this->db()->fetch(
            "SELECT d.*, m.sku AS material_sku, m.name AS material_name,
                    {$printerSelect},
                    (SELECT ia.attribute_value FROM item_attributes ia
                     WHERE ia.item_id = m.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS material_filament_alias
             FROM details d
             LEFT JOIN items m ON d.material_item_id = m.id
             LEFT JOIN printers p ON d.printer_id = p.id
             WHERE d.id = ?",
            [$id]
        );

        if (!$detail) {
            $this->notFound();
        }

        $activeRouting = $this->db()->fetch(
            "SELECT * FROM detail_routing WHERE detail_id = ? AND status = 'active' LIMIT 1",
            [$id]
        );
        $routingOperations = [];
        if ($activeRouting) {
            $routingOperations = $this->db()->fetchAll(
                "SELECT * FROM detail_routing_operations WHERE routing_id = ? ORDER BY sort_order",
                [$activeRouting['id']]
            );
        }

        // Load detail operations and calculate labor cost
        $operations = $this->getDetailOperations((int)$id);
        $laborCost = $this->calculateLaborCost($operations);

        // Calculate production cost for printed details (including labor cost from operations)
        $costData = null;
        $costBreakdown = [];
        if ($detail['detail_type'] === 'printed') {
            $costData = $this->costingService->calculateCost($detail, $laborCost['total_cost']);
            // Add labor details for display
            if ($costData && $laborCost['total_minutes'] > 0) {
                $costData['calculation_details']['labor_details'] = sprintf(
                    '%d %s',
                    $laborCost['total_minutes'],
                    'min'
                );
            }
            $costBreakdown = $this->costingService->getCostBreakdown($costData);
        }

        // Load available resources for operations
        $materials = $this->getMaterials();
        $printers = $this->getPrinters();
        $tools = $this->getTools();

        // Get products that use this detail
        $usedInProducts = $this->getProductsUsingDetail((int)$id);

        // Get alias colors and detail materials
        $aliasColors = $this->getAliasColors();
        $detailMaterials = $this->getDetailMaterials((int)$id);

        // Enrich materials with alias colors
        foreach ($detailMaterials as &$dm) {
            $dm['alias_color'] = $aliasColors[$dm['filament_alias'] ?? ''] ?? null;
        }
        unset($dm);

        // Legacy single alias color for backward compat
        $aliasColor = $aliasColors[$detail['material_filament_alias'] ?? ''] ?? null;

        $this->render('catalog/details/show', [
            'title' => $detail['name'],
            'detail' => $detail,
            'activeRouting' => $activeRouting,
            'routingOperations' => $routingOperations,
            'costData' => $costData,
            'costBreakdown' => $costBreakdown,
            'operations' => $operations,
            'laborCost' => $laborCost,
            'materials' => $materials,
            'printers' => $printers,
            'tools' => $tools,
            'usedInProducts' => $usedInProducts,
            'aliasColor' => $aliasColor,
            'aliasColors' => $aliasColors,
            'detailMaterials' => $detailMaterials
        ]);
    }

    /**
     * Create detail form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.details.create');

        $materials = $this->getMaterials();
        $printers = $this->getPrinters();

        $this->render('catalog/details/form', [
            'title' => $this->app->getTranslator()->get('create_detail'),
            'detail' => null,
            'materials' => $materials,
            'printers' => $printers,
            'detailMaterials' => []
        ]);
    }

    /**
     * Copy detail - opens create form with pre-filled data from existing detail
     */
    public function copy(string $id): void
    {
        $this->requirePermission('catalog.details.create');

        $detail = $this->db()->fetch("SELECT * FROM details WHERE id = ?", [$id]);

        if (!$detail) {
            $this->notFound();
        }

        // Load materials from source detail
        $detailMaterials = $this->getDetailMaterials((int)$id);

        // Modify SKU to indicate it's a copy
        $detail['sku'] = $detail['sku'] . '-COPY';
        $detail['id'] = null; // Clear ID so form treats it as new

        $materials = $this->getMaterials();
        $printers = $this->getPrinters();

        $this->render('catalog/details/form', [
            'title' => $this->app->getTranslator()->get('copy_detail') . ': ' . $detail['name'],
            'detail' => $detail,
            'materials' => $materials,
            'printers' => $printers,
            'detailMaterials' => $detailMaterials,
            'isCopy' => true
        ]);
    }

    /**
     * Store detail
     */
    public function store(): void
    {
        $this->requirePermission('catalog.details.create');
        $this->validateCsrf();

        $data = $this->getDetailPayload();
        $errors = $this->validateDetailPayload($data);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/details/create');
            return;
        }

        $this->db()->beginTransaction();

        try {
            $itemId = $this->syncWarehouseItem(null, $data);
            $data['item_id'] = $itemId;

            $detailId = $this->db()->insert('details', array_merge($data, [
                'created_at' => date('Y-m-d H:i:s')
            ]));

            // Save multi-material data
            $this->saveDetailMaterials((int)$detailId, $_POST['materials'] ?? []);

            $this->audit('detail.created', 'details', $detailId, null, $data);
            $this->db()->commit();

            $this->session->setFlash('success', 'Detail created successfully');
            $this->redirect("/catalog/details/{$detailId}");
        } catch (\Exception $e) {
            $this->db()->rollback();
            $this->session->setFlash('error', $e->getMessage());
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/details/create');
        }
    }

    /**
     * Edit detail form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.details.edit');

        $detail = $this->db()->fetch("SELECT * FROM details WHERE id = ?", [$id]);

        if (!$detail) {
            $this->notFound();
        }

        $detailMaterials = $this->getDetailMaterials((int)$id);

        $materials = $this->getMaterials();
        $printers = $this->getPrinters();

        $this->render('catalog/details/form', [
            'title' => $this->app->getTranslator()->get('edit_detail'),
            'detail' => $detail,
            'materials' => $materials,
            'printers' => $printers,
            'detailMaterials' => $detailMaterials
        ]);
    }

    /**
     * Update detail
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCsrf();

        $detail = $this->db()->fetch("SELECT * FROM details WHERE id = ?", [$id]);

        if (!$detail) {
            $this->notFound();
        }

        $data = $this->getDetailPayload(true, $detail);
        $errors = $this->validateDetailPayload($data, (int)$id);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/catalog/details/{$id}/edit");
            return;
        }

        $this->db()->beginTransaction();

        try {
            $itemId = $this->syncWarehouseItem($detail['item_id'] ? (int)$detail['item_id'] : null, $data);
            $data['item_id'] = $itemId;

            $this->db()->update('details', $data, ['id' => $id]);

            // Save multi-material data
            $this->saveDetailMaterials((int)$id, $_POST['materials'] ?? []);

            $this->audit('detail.updated', 'details', $id, $detail, $data);
            $this->db()->commit();

            $this->session->setFlash('success', 'Detail updated successfully');
            $this->redirect("/catalog/details/{$id}");
        } catch (\Exception $e) {
            $this->db()->rollback();
            $this->session->setFlash('error', $e->getMessage());
            $this->session->flashInput($_POST);
            $this->redirect("/catalog/details/{$id}/edit");
        }
    }

    private function getMaterials(): array
    {
        return $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name,
                    MAX(CASE WHEN ia.attribute_name = 'manufacturer' THEN ia.attribute_value END) as manufacturer,
                    MAX(CASE WHEN ia.attribute_name = 'plastic_type' THEN ia.attribute_value END) as plastic_type,
                    MAX(CASE WHEN ia.attribute_name = 'color' THEN ia.attribute_value END) as color,
                    MAX(CASE WHEN ia.attribute_name = 'filament_alias' THEN ia.attribute_value END) as filament_alias
             FROM items i
             LEFT JOIN item_attributes ia ON i.id = ia.item_id
             WHERE i.type = 'material' AND i.is_active = 1
             GROUP BY i.id, i.sku, i.name
             ORDER BY i.name"
        );
    }

    private function getPrinters(): array
    {
        if (!$this->db()->tableExists('printers')) {
            return [];
        }

        $columns = ['id', 'name'];
        if ($this->db()->columnExists('printers', 'model')) {
            $columns[] = 'model';
        } else {
            $columns[] = 'NULL AS model';
        }

        return $this->db()->fetchAll(
            "SELECT " . implode(', ', $columns) . "
             FROM printers
             WHERE is_active = 1
             ORDER BY name"
        );
    }

    private function getDetailPayload(bool $isUpdate = false, ?array $existing = null): array
    {
        // Parse multi-material data from form
        $postMaterials = $_POST['materials'] ?? [];
        $firstMaterialId = null;
        $totalQtyGrams = 0;

        foreach ($postMaterials as $mat) {
            $matId = (int)($mat['item_id'] ?? 0);
            $matQty = (float)($mat['qty_grams'] ?? 0);
            if ($matId > 0) {
                if ($firstMaterialId === null) {
                    $firstMaterialId = $matId;
                }
                $totalQtyGrams += $matQty;
            }
        }

        // Fall back to legacy single-material fields if no multi-material data
        if ($firstMaterialId === null && !empty($_POST['material_item_id'])) {
            $firstMaterialId = (int)$_POST['material_item_id'];
            $totalQtyGrams = (float)($_POST['material_qty_grams'] ?? 0);
        }

        $data = [
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'detail_type' => $_POST['detail_type'] ?? '',
            'material_item_id' => $firstMaterialId,
            'printer_id' => (int)($_POST['printer_id'] ?? 0),
            'material_qty_grams' => $totalQtyGrams,
            'print_time_minutes' => (int)($_POST['print_time_minutes'] ?? 0),
            'print_parameters' => trim($_POST['print_parameters'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        if ($data['detail_type'] === 'purchased') {
            $data['material_item_id'] = null;
            $data['printer_id'] = null;
            $data['material_qty_grams'] = 0;
            $data['print_time_minutes'] = 0;
            $data['print_parameters'] = '';
        } elseif (empty($data['material_item_id'])) {
            $data['material_item_id'] = null;
        }

        if (empty($data['printer_id'])) {
            $data['printer_id'] = null;
        }

        $imagePath = $this->storeImageUpload('image', 'details');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        } elseif ($isUpdate && $existing) {
            $data['image_path'] = $existing['image_path'] ?? null;
        }

        $modelPath = $this->storeFileUpload('model_file', 'details/models', ['stl', 'step', 'stp']);
        if ($modelPath) {
            $data['model_path'] = $modelPath;
        } elseif ($isUpdate && $existing) {
            $data['model_path'] = $existing['model_path'] ?? null;
        }

        return $data;
    }

    private function validateDetailPayload(array $data, ?int $detailId = null): array
    {
        $errors = [];

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            $params = [$data['sku']];
            $sql = 'SELECT id FROM details WHERE sku = ?';
            if ($detailId) {
                $sql .= ' AND id != ?';
                $params[] = $detailId;
            }
            $exists = $this->db()->fetch($sql, $params);
            if ($exists) {
                $errors['sku'] = 'SKU already exists';
            }
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if (!in_array($data['detail_type'], ['purchased', 'printed'], true)) {
            $errors['detail_type'] = 'Detail type is required';
        }

        if ($data['detail_type'] === 'printed' && empty($data['material_item_id'])) {
            $errors['material_item_id'] = 'Material is required';
        }

        if ($data['detail_type'] === 'printed' && empty($data['printer_id'])) {
            $errors['printer_id'] = 'Printer is required';
        }

        if ($data['detail_type'] === 'printed') {
            if ($data['material_qty_grams'] <= 0) {
                $errors['material_qty_grams'] = 'Material grams must be greater than 0';
            }

            if ($data['print_time_minutes'] <= 0) {
                $errors['print_time_minutes'] = 'Print time must be greater than 0';
            }
        } else {
            if ($data['material_qty_grams'] < 0) {
                $errors['material_qty_grams'] = 'Material grams cannot be negative';
            }

            if ($data['print_time_minutes'] < 0) {
                $errors['print_time_minutes'] = 'Print time cannot be negative';
            }
        }

        return $errors;
    }

    private function syncWarehouseItem(?int $itemId, array $detailData): int
    {
        $existingItem = null;
        if ($itemId) {
            $existingItem = $this->itemService->findById($itemId);
        }

        if (!$existingItem) {
            $existingItem = $this->itemService->findBySku($detailData['sku']);
        }

        $itemData = [
            'sku' => $detailData['sku'],
            'name' => $detailData['name'],
            'type' => $detailData['detail_type'] === 'printed' ? 'part' : 'component',
            'unit' => 'pcs',
            'description' => $detailData['print_parameters'] ?? '',
            'min_stock' => 0,
            'reorder_point' => 0,
            'is_active' => $detailData['is_active'] ?? 1,
            'image_path' => $detailData['image_path'] ?? ($existingItem['image_path'] ?? null)
        ];

        $user = $this->user();
        $userId = $user['id'] ?? null;

        if ($existingItem) {
            $this->itemService->update((int)$existingItem['id'], $itemData, [], $userId);
            return (int)$existingItem['id'];
        }

        $item = $this->itemService->create($itemData, [], $userId);

        return (int)$item['id'];
    }

    /**
     * Get tools (items of type 'tool')
     */
    private function getTools(): array
    {
        return $this->db()->fetchAll(
            "SELECT i.id, i.sku, i.name
             FROM items i
             WHERE i.type = 'tool' AND i.is_active = 1
             ORDER BY i.name"
        );
    }

    /**
     * Get products that use this detail in their composition
     */
    private function getProductsUsingDetail(int $detailId): array
    {
        if (!$this->db()->tableExists('product_components')) {
            return [];
        }

        return $this->db()->fetchAll(
            "SELECT p.id, p.code, p.name, p.image_path, p.is_active,
                    pc.quantity,
                    c.name AS category_name
             FROM product_components pc
             JOIN products p ON pc.product_id = p.id
             LEFT JOIN product_categories c ON p.category_id = c.id
             WHERE pc.component_type = 'detail' AND pc.detail_id = ?
             ORDER BY p.code",
            [$detailId]
        );
    }

    /**
     * Get detail operations with resources
     */
    private function getDetailOperations(int $detailId): array
    {
        if (!$this->db()->tableExists('detail_operations')) {
            return [];
        }

        $operations = $this->db()->fetchAll(
            "SELECT o.*,
                    m.sku AS material_sku, m.name AS material_name,
                    p.name AS printer_name,
                    t.sku AS tool_sku, t.name AS tool_name
             FROM detail_operations o
             LEFT JOIN items m ON o.material_id = m.id
             LEFT JOIN printers p ON o.printer_id = p.id
             LEFT JOIN items t ON o.tool_id = t.id
             WHERE o.detail_id = ?
             ORDER BY o.sort_order, o.id",
            [$detailId]
        );

        // Load multi-material data for each operation
        if ($this->db()->tableExists('detail_operation_materials')) {
            $opIds = array_column($operations, 'id');
            $opMaterials = $this->getOperationMaterialsBatch($opIds);
            foreach ($operations as &$op) {
                $op['operation_materials'] = $opMaterials[$op['id']] ?? [];
            }
            unset($op);
        } else {
            foreach ($operations as &$op) {
                $op['operation_materials'] = [];
            }
            unset($op);
        }

        return $operations;
    }

    /**
     * Batch-load materials for multiple operations
     */
    private function getOperationMaterialsBatch(array $operationIds): array
    {
        if (empty($operationIds)) {
            return [];
        }

        $result = [];
        foreach ($operationIds as $id) {
            $result[$id] = [];
        }

        $placeholders = implode(',', array_fill(0, count($operationIds), '?'));
        $rows = $this->db()->fetchAll(
            "SELECT om.*, m.sku AS material_sku, m.name AS material_name,
                    (SELECT ia.attribute_value FROM item_attributes ia
                     WHERE ia.item_id = m.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS filament_alias
             FROM detail_operation_materials om
             JOIN items m ON om.material_id = m.id
             WHERE om.operation_id IN ({$placeholders})
             ORDER BY om.operation_id, om.sort_order, om.id",
            $operationIds
        );

        foreach ($rows as $row) {
            $result[$row['operation_id']][] = $row;
        }

        return $result;
    }

    /**
     * Save multiple materials for an operation
     */
    private function saveOperationMaterials(int $operationId, array $materialIds): void
    {
        if (!$this->db()->tableExists('detail_operation_materials')) {
            $this->ensureOperationMaterialsTable();
        }

        $this->db()->delete('detail_operation_materials', ['operation_id' => $operationId]);

        $sortOrder = 0;
        foreach ($materialIds as $matId) {
            $matId = (int)$matId;
            if ($matId > 0) {
                $this->db()->insert('detail_operation_materials', [
                    'operation_id' => $operationId,
                    'material_id' => $matId,
                    'sort_order' => $sortOrder++
                ]);
            }
        }
    }

    /**
     * Ensure detail_operation_materials table exists
     */
    private function ensureOperationMaterialsTable(): void
    {
        if ($this->db()->tableExists('detail_operation_materials')) {
            return;
        }

        $this->db()->exec("
            CREATE TABLE IF NOT EXISTS detail_operation_materials (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                operation_id INT NOT NULL,
                material_id INT UNSIGNED NOT NULL,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_op_materials_operation (operation_id),
                FOREIGN KEY (operation_id) REFERENCES detail_operations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Calculate total labor cost from operations
     */
    private function calculateLaborCost(array $operations): array
    {
        $totalMinutes = 0;
        $totalCost = 0;

        foreach ($operations as $op) {
            $minutes = (int)($op['time_minutes'] ?? 0);
            $rate = (float)($op['labor_rate'] ?? 0);
            $opCost = ($minutes / 60) * $rate;

            $totalMinutes += $minutes;
            $totalCost += $opCost;
        }

        return [
            'total_minutes' => $totalMinutes,
            'total_cost' => $totalCost
        ];
    }

    /**
     * Add operation to detail
     */
    public function addOperation(string $id): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCsrf();

        $detail = $this->db()->fetch("SELECT * FROM details WHERE id = ?", [$id]);
        if (!$detail) {
            $this->notFound();
        }

        // Ensure table exists
        $this->ensureOperationsTableExists();

        $maxSort = (int)$this->db()->fetchColumn(
            "SELECT COALESCE(MAX(sort_order), 0) FROM detail_operations WHERE detail_id = ?",
            [$id]
        );

        // Parse material IDs - support both multi-select and legacy single
        $materialIds = $_POST['material_ids'] ?? [];
        if (empty($materialIds) && !empty($_POST['material_id'])) {
            $materialIds = [(int)$_POST['material_id']];
        }

        $data = [
            'detail_id' => (int)$id,
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'time_minutes' => (int)($_POST['time_minutes'] ?? 0),
            'labor_rate' => (float)($_POST['labor_rate'] ?? 0),
            'material_id' => !empty($materialIds) ? (int)$materialIds[0] : null,
            'printer_id' => !empty($_POST['printer_id']) ? (int)$_POST['printer_id'] : null,
            'tool_id' => !empty($_POST['tool_id']) ? (int)$_POST['tool_id'] : null,
            'sort_order' => $maxSort + 1
        ];

        if (empty($data['name'])) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'error' => 'Operation name is required']);
            }
            $this->session->setFlash('error', 'Operation name is required');
            $this->redirect("/catalog/details/{$id}");
            return;
        }

        $operationId = $this->db()->insert('detail_operations', $data);

        // Save multi-material data
        if (!empty($materialIds)) {
            $this->saveOperationMaterials((int)$operationId, $materialIds);
        }

        $this->audit('detail.operation.added', 'detail_operations', $operationId, null, $data);

        if ($this->isAjax()) {
            $operations = $this->getDetailOperations((int)$id);
            $laborCost = $this->calculateLaborCost($operations);
            $this->jsonResponse([
                'success' => true,
                'operations' => $operations,
                'laborCost' => $laborCost
            ]);
        }

        $this->session->setFlash('success', 'Operation added');
        $this->redirect("/catalog/details/{$id}");
    }

    /**
     * Update operation
     */
    public function updateOperation(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCsrf();

        $operation = $this->db()->fetch(
            "SELECT * FROM detail_operations WHERE id = ? AND detail_id = ?",
            [$operationId, $id]
        );

        if (!$operation) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'error' => 'Operation not found']);
            }
            $this->notFound();
        }

        // Parse material IDs - support both multi-select and legacy single
        $materialIds = $_POST['material_ids'] ?? [];
        if (empty($materialIds) && !empty($_POST['material_id'])) {
            $materialIds = [(int)$_POST['material_id']];
        }

        $data = [
            'name' => trim($_POST['name'] ?? $operation['name']),
            'description' => trim($_POST['description'] ?? ''),
            'time_minutes' => (int)($_POST['time_minutes'] ?? 0),
            'labor_rate' => (float)($_POST['labor_rate'] ?? 0),
            'material_id' => !empty($materialIds) ? (int)$materialIds[0] : null,
            'printer_id' => !empty($_POST['printer_id']) ? (int)$_POST['printer_id'] : null,
            'tool_id' => !empty($_POST['tool_id']) ? (int)$_POST['tool_id'] : null
        ];

        $this->db()->update('detail_operations', $data, ['id' => $operationId]);

        // Save multi-material data
        $this->saveOperationMaterials((int)$operationId, $materialIds);

        $this->audit('detail.operation.updated', 'detail_operations', $operationId, $operation, $data);

        if ($this->isAjax()) {
            $operations = $this->getDetailOperations((int)$id);
            $laborCost = $this->calculateLaborCost($operations);
            $this->jsonResponse([
                'success' => true,
                'operations' => $operations,
                'laborCost' => $laborCost
            ]);
        }

        $this->session->setFlash('success', 'Operation updated');
        $this->redirect("/catalog/details/{$id}");
    }

    /**
     * Remove operation
     */
    public function removeOperation(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCsrf();

        $operation = $this->db()->fetch(
            "SELECT * FROM detail_operations WHERE id = ? AND detail_id = ?",
            [$operationId, $id]
        );

        if (!$operation) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'error' => 'Operation not found']);
            }
            $this->notFound();
        }

        $this->db()->delete('detail_operations', ['id' => $operationId]);

        $this->audit('detail.operation.removed', 'detail_operations', $operationId, $operation, null);

        if ($this->isAjax()) {
            $operations = $this->getDetailOperations((int)$id);
            $laborCost = $this->calculateLaborCost($operations);
            $this->jsonResponse([
                'success' => true,
                'operations' => $operations,
                'laborCost' => $laborCost
            ]);
        }

        $this->session->setFlash('success', 'Operation removed');
        $this->redirect("/catalog/details/{$id}");
    }

    /**
     * Move operation up
     */
    public function moveOperationUp(string $id, string $operationId): void
    {
        $this->moveOperation($id, $operationId, 'up');
    }

    /**
     * Move operation down
     */
    public function moveOperationDown(string $id, string $operationId): void
    {
        $this->moveOperation($id, $operationId, 'down');
    }

    /**
     * Move operation in specified direction
     */
    private function moveOperation(string $id, string $operationId, string $direction): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCsrf();

        $operations = $this->db()->fetchAll(
            "SELECT id, sort_order FROM detail_operations WHERE detail_id = ? ORDER BY sort_order, id",
            [$id]
        );

        $currentIndex = null;
        foreach ($operations as $i => $op) {
            if ($op['id'] == $operationId) {
                $currentIndex = $i;
                break;
            }
        }

        if ($currentIndex === null) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'error' => 'Operation not found']);
            }
            $this->notFound();
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($swapIndex < 0 || $swapIndex >= count($operations)) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => true, 'message' => 'Already at boundary']);
            }
            $this->redirect("/catalog/details/{$id}");
            return;
        }

        // Swap sort orders
        $currentOp = $operations[$currentIndex];
        $swapOp = $operations[$swapIndex];

        $this->db()->update('detail_operations', ['sort_order' => $swapOp['sort_order']], ['id' => $currentOp['id']]);
        $this->db()->update('detail_operations', ['sort_order' => $currentOp['sort_order']], ['id' => $swapOp['id']]);

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => true]);
        }

        $this->redirect("/catalog/details/{$id}");
    }

    /**
     * Get materials for a single detail from detail_materials table
     */
    private function getDetailMaterials(int $detailId): array
    {
        if (!$this->db()->tableExists('detail_materials')) {
            // Fall back to legacy single material
            return $this->getLegacyMaterial($detailId);
        }

        $materials = $this->db()->fetchAll(
            "SELECT dm.*, m.sku AS material_sku, m.name AS material_name,
                    (SELECT ia.attribute_value FROM item_attributes ia
                     WHERE ia.item_id = m.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS filament_alias
             FROM detail_materials dm
             JOIN items m ON dm.material_item_id = m.id
             WHERE dm.detail_id = ?
             ORDER BY dm.sort_order, dm.id",
            [$detailId]
        );

        if (empty($materials)) {
            return $this->getLegacyMaterial($detailId);
        }

        return $materials;
    }

    /**
     * Fall back to legacy single material from details table
     */
    private function getLegacyMaterial(int $detailId): array
    {
        $detail = $this->db()->fetch(
            "SELECT d.material_item_id, d.material_qty_grams,
                    m.sku AS material_sku, m.name AS material_name,
                    (SELECT ia.attribute_value FROM item_attributes ia
                     WHERE ia.item_id = m.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS filament_alias
             FROM details d
             LEFT JOIN items m ON d.material_item_id = m.id
             WHERE d.id = ? AND d.material_item_id IS NOT NULL",
            [$detailId]
        );

        if (!$detail || empty($detail['material_item_id'])) {
            return [];
        }

        return [[
            'material_item_id' => $detail['material_item_id'],
            'material_qty_grams' => $detail['material_qty_grams'],
            'material_sku' => $detail['material_sku'],
            'material_name' => $detail['material_name'],
            'filament_alias' => $detail['filament_alias'],
            'sort_order' => 0
        ]];
    }

    /**
     * Batch-load materials for multiple details
     */
    private function getDetailMaterialsBatch(array $detailIds): array
    {
        if (empty($detailIds)) {
            return [];
        }

        $result = [];
        foreach ($detailIds as $id) {
            $result[$id] = [];
        }

        if ($this->db()->tableExists('detail_materials')) {
            $placeholders = implode(',', array_fill(0, count($detailIds), '?'));
            $rows = $this->db()->fetchAll(
                "SELECT dm.*, m.sku AS material_sku, m.name AS material_name,
                        (SELECT ia.attribute_value FROM item_attributes ia
                         WHERE ia.item_id = m.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS filament_alias
                 FROM detail_materials dm
                 JOIN items m ON dm.material_item_id = m.id
                 WHERE dm.detail_id IN ({$placeholders})
                 ORDER BY dm.detail_id, dm.sort_order, dm.id",
                $detailIds
            );

            foreach ($rows as $row) {
                $result[$row['detail_id']][] = $row;
            }
        }

        return $result;
    }

    /**
     * Save multi-material data for a detail
     */
    private function saveDetailMaterials(int $detailId, array $postMaterials): void
    {
        if (!$this->db()->tableExists('detail_materials')) {
            $this->ensureDetailMaterialsTable();
        }

        // Delete existing
        $this->db()->delete('detail_materials', ['detail_id' => $detailId]);

        // Insert new materials
        $sortOrder = 0;
        foreach ($postMaterials as $mat) {
            $matId = (int)($mat['item_id'] ?? 0);
            $matQty = (float)($mat['qty_grams'] ?? 0);
            if ($matId > 0) {
                $this->db()->insert('detail_materials', [
                    'detail_id' => $detailId,
                    'material_item_id' => $matId,
                    'material_qty_grams' => $matQty,
                    'sort_order' => $sortOrder++
                ]);
            }
        }
    }

    /**
     * Ensure detail_materials table exists
     */
    private function ensureDetailMaterialsTable(): void
    {
        if ($this->db()->tableExists('detail_materials')) {
            return;
        }

        $this->db()->exec("
            CREATE TABLE IF NOT EXISTS detail_materials (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                detail_id INT UNSIGNED NOT NULL,
                material_item_id INT UNSIGNED NOT NULL,
                material_qty_grams DECIMAL(10,2) NOT NULL DEFAULT 0,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_detail_materials_detail (detail_id),
                FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Get alias color lookup map from item_option_values
     */
    private function getAliasColors(): array
    {
        if (!$this->db()->tableExists('item_option_values')
            || !$this->db()->columnExists('item_option_values', 'color')) {
            return [];
        }

        $rows = $this->db()->fetchAll(
            "SELECT name, color FROM item_option_values
             WHERE group_key = 'filament_alias' AND color IS NOT NULL AND color != ''"
        );

        $map = [];
        foreach ($rows as $row) {
            $map[$row['name']] = $row['color'];
        }
        return $map;
    }

    /**
     * Ensure detail_operations table exists
     */
    private function ensureOperationsTableExists(): void
    {
        if ($this->db()->tableExists('detail_operations')) {
            return;
        }

        $this->db()->exec("
            CREATE TABLE IF NOT EXISTS detail_operations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                detail_id INT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                time_minutes INT NOT NULL DEFAULT 0,
                labor_rate DECIMAL(15,4) DEFAULT 0,
                material_id INT UNSIGNED NULL,
                printer_id BIGINT UNSIGNED NULL,
                tool_id INT UNSIGNED NULL,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_detail_operations_detail (detail_id),
                FOREIGN KEY (detail_id) REFERENCES details(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
