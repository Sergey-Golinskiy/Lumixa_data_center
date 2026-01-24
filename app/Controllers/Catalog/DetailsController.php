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
                    {$printerSelect}
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

        // Calculate production cost for printed details
        $costData = null;
        $costBreakdown = [];
        if ($detail['detail_type'] === 'printed') {
            $costData = $this->costingService->calculateCost($detail);
            $costBreakdown = $this->costingService->getCostBreakdown($costData);
        }

        $this->render('catalog/details/show', [
            'title' => $detail['name'],
            'detail' => $detail,
            'activeRouting' => $activeRouting,
            'routingOperations' => $routingOperations,
            'costData' => $costData,
            'costBreakdown' => $costBreakdown
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
            'printers' => $printers
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

        $materials = $this->getMaterials();
        $printers = $this->getPrinters();

        $this->render('catalog/details/form', [
            'title' => $this->app->getTranslator()->get('edit_detail'),
            'detail' => $detail,
            'materials' => $materials,
            'printers' => $printers
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
        $data = [
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'detail_type' => $_POST['detail_type'] ?? '',
            'material_item_id' => (int)($_POST['material_item_id'] ?? 0),
            'printer_id' => (int)($_POST['printer_id'] ?? 0),
            'material_qty_grams' => (float)($_POST['material_qty_grams'] ?? 0),
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
}
