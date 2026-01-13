<?php

namespace App\Controllers\Warehouse;

use App\Core\Controller;
use App\Core\Database;
use App\Services\AuditService;

/**
 * Warehouse Item (SKU) Controller
 */
class ItemController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * List items
     */
    public function index(): void
    {
        $this->requirePermission('warehouse.items.view');
        $this->setLayout('main');

        $db = Database::getInstance();

        $page = max(1, (int) input('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $search = input('search', '');
        $typeFilter = input('type', '');
        $activeFilter = input('active', '');

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(i.sku LIKE ? OR i.name LIKE ?)';
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($typeFilter) {
            $where[] = 'it.code = ?';
            $params[] = $typeFilter;
        }

        if ($activeFilter !== '') {
            $where[] = 'i.is_active = ?';
            $params[] = (int) $activeFilter;
        }

        $whereClause = implode(' AND ', $where);

        $total = $db->fetchColumn(
            "SELECT COUNT(*) FROM items i
             INNER JOIN item_types it ON it.id = i.item_type_id
             WHERE {$whereClause}",
            $params
        );

        $items = $db->fetchAll(
            "SELECT i.*, it.name as type_name, it.code as type_code, u.code as unit_code,
                    COALESCE(SUM(s.on_hand), 0) as total_on_hand,
                    COALESCE(SUM(s.reserved), 0) as total_reserved
             FROM items i
             INNER JOIN item_types it ON it.id = i.item_type_id
             INNER JOIN units u ON u.id = i.unit_id
             LEFT JOIN stock s ON s.item_id = i.id
             WHERE {$whereClause}
             GROUP BY i.id
             ORDER BY i.name ASC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $itemTypes = $db->fetchAll("SELECT * FROM item_types ORDER BY sort_order");

        $this->view('warehouse/items/index', [
            'title' => 'Items (SKU)',
            'items' => $items,
            'itemTypes' => $itemTypes,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'activeFilter' => $activeFilter,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
        ]);
    }

    /**
     * Show create item form
     */
    public function create(): void
    {
        $this->requirePermission('warehouse.items.create');
        $this->setLayout('main');

        $db = Database::getInstance();

        $itemTypes = $db->fetchAll("SELECT * FROM item_types ORDER BY sort_order");
        $units = $db->fetchAll("SELECT * FROM units ORDER BY name");

        $this->view('warehouse/items/create', [
            'title' => 'Create Item',
            'itemTypes' => $itemTypes,
            'units' => $units,
            'errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Store new item
     */
    public function store(): void
    {
        $this->requirePermission('warehouse.items.create');
        $this->validateCsrfOrAbort();

        $db = Database::getInstance();

        $data = $this->validate([
            'sku' => 'required',
            'name' => 'required',
            'item_type_id' => 'required|integer',
            'unit_id' => 'required|integer',
        ]);

        // Check if SKU exists
        $existing = $db->fetch("SELECT id FROM items WHERE sku = ?", [$data['sku']]);
        if ($existing) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = ['sku' => 'SKU already exists'];
            $this->back(['error' => 'SKU already exists']);
        }

        $itemId = $db->insert('items', [
            'sku' => $data['sku'],
            'name' => $data['name'],
            'description' => input('description', ''),
            'item_type_id' => $data['item_type_id'],
            'unit_id' => $data['unit_id'],
            'is_active' => input('is_active') ? 1 : 0,
            'min_stock_level' => (float) input('min_stock_level', 0),
            'default_price' => (float) input('default_price', 0),
            'barcode' => input('barcode', ''),
            'notes' => input('notes', ''),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Save attributes
        $this->saveAttributes($itemId);

        $this->auditService->logCreate('item', $itemId, [
            'sku' => $data['sku'],
            'name' => $data['name'],
        ]);

        $this->redirect('warehouse/items/' . $itemId, ['success' => 'Item created successfully']);
    }

    /**
     * Show item details
     */
    public function show(int $id): void
    {
        $this->requirePermission('warehouse.items.view');
        $this->setLayout('main');

        $db = Database::getInstance();

        $item = $db->fetch(
            "SELECT i.*, it.name as type_name, it.code as type_code, u.code as unit_code, u.name as unit_name
             FROM items i
             INNER JOIN item_types it ON it.id = i.item_type_id
             INNER JOIN units u ON u.id = i.unit_id
             WHERE i.id = ?",
            [$id]
        );

        if (!$item) {
            abort(404);
        }

        // Get attributes
        $attributes = $db->fetchAll(
            "SELECT * FROM item_attributes WHERE item_id = ? ORDER BY attribute_name",
            [$id]
        );

        // Get stock by warehouse
        $stock = $db->fetchAll(
            "SELECT s.*, w.name as warehouse_name, l.lot_number, l.color
             FROM stock s
             INNER JOIN warehouses w ON w.id = s.warehouse_id
             LEFT JOIN lots l ON l.id = s.lot_id
             WHERE s.item_id = ?
             ORDER BY w.name, l.lot_number",
            [$id]
        );

        // Get recent movements
        $movements = $db->fetchAll(
            "SELECT sp.*, d.document_number, dt.name as document_type
             FROM stock_postings sp
             INNER JOIN documents d ON d.id = sp.document_id
             INNER JOIN document_types dt ON dt.id = d.document_type_id
             WHERE sp.item_id = ?
             ORDER BY sp.posted_at DESC
             LIMIT 20",
            [$id]
        );

        // Get audit history
        $history = $this->auditService->getEntityHistory('item', $id);

        $this->view('warehouse/items/show', [
            'title' => $item['name'],
            'item' => $item,
            'attributes' => $attributes,
            'stock' => $stock,
            'movements' => $movements,
            'history' => $history,
        ]);
    }

    /**
     * Show edit item form
     */
    public function edit(int $id): void
    {
        $this->requirePermission('warehouse.items.edit');
        $this->setLayout('main');

        $db = Database::getInstance();

        $item = $db->fetch("SELECT * FROM items WHERE id = ?", [$id]);
        if (!$item) {
            abort(404);
        }

        $itemTypes = $db->fetchAll("SELECT * FROM item_types ORDER BY sort_order");
        $units = $db->fetchAll("SELECT * FROM units ORDER BY name");
        $attributes = $db->fetchAll(
            "SELECT * FROM item_attributes WHERE item_id = ? ORDER BY attribute_name",
            [$id]
        );

        $this->view('warehouse/items/edit', [
            'title' => 'Edit Item',
            'item' => $item,
            'itemTypes' => $itemTypes,
            'units' => $units,
            'attributes' => $attributes,
            'errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Update item
     */
    public function update(int $id): void
    {
        $this->requirePermission('warehouse.items.edit');
        $this->validateCsrfOrAbort();

        $db = Database::getInstance();

        $item = $db->fetch("SELECT * FROM items WHERE id = ?", [$id]);
        if (!$item) {
            abort(404);
        }

        $data = $this->validate([
            'name' => 'required',
            'item_type_id' => 'required|integer',
            'unit_id' => 'required|integer',
        ]);

        $db->update('items', [
            'name' => $data['name'],
            'description' => input('description', ''),
            'item_type_id' => $data['item_type_id'],
            'unit_id' => $data['unit_id'],
            'is_active' => input('is_active') ? 1 : 0,
            'min_stock_level' => (float) input('min_stock_level', 0),
            'default_price' => (float) input('default_price', 0),
            'barcode' => input('barcode', ''),
            'notes' => input('notes', ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Update attributes
        $this->saveAttributes($id);

        $this->auditService->logUpdate('item', $id, $item, [
            'name' => $data['name'],
        ]);

        $this->redirect('warehouse/items/' . $id, ['success' => 'Item updated successfully']);
    }

    /**
     * Delete item
     */
    public function delete(int $id): void
    {
        $this->requirePermission('warehouse.items.delete');
        $this->validateCsrfOrAbort();

        $db = Database::getInstance();

        $item = $db->fetch("SELECT * FROM items WHERE id = ?", [$id]);
        if (!$item) {
            abort(404);
        }

        // Check if item has stock
        $hasStock = $db->fetchColumn(
            "SELECT COUNT(*) FROM stock WHERE item_id = ? AND on_hand != 0",
            [$id]
        );

        if ($hasStock > 0) {
            $this->back(['error' => 'Cannot delete item with existing stock']);
        }

        // Check if item is used in BOM
        $inBom = $db->fetchColumn(
            "SELECT COUNT(*) FROM bom_lines WHERE item_id = ?",
            [$id]
        );

        if ($inBom > 0) {
            $this->back(['error' => 'Cannot delete item used in BOM']);
        }

        // Soft delete - just deactivate
        $db->update('items', ['is_active' => 0], 'id = ?', [$id]);

        $this->auditService->logDelete('item', $id, $item);

        $this->redirect('warehouse/items', ['success' => 'Item deactivated successfully']);
    }

    /**
     * Save item attributes
     */
    private function saveAttributes(int $itemId): void
    {
        $db = Database::getInstance();

        // Delete existing attributes
        $db->delete('item_attributes', 'item_id = ?', [$itemId]);

        // Get attribute data from form
        $attrNames = input('attr_name', []);
        $attrValues = input('attr_value', []);

        if (!is_array($attrNames) || !is_array($attrValues)) {
            return;
        }

        foreach ($attrNames as $index => $name) {
            $name = trim($name);
            $value = trim($attrValues[$index] ?? '');

            if ($name && $value) {
                $db->insert('item_attributes', [
                    'item_id' => $itemId,
                    'attribute_name' => $name,
                    'attribute_value' => $value,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
