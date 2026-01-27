<?php
/**
 * ProductCostingService - Calculate production cost for products
 */

namespace App\Services\Catalog;

use App\Core\Application;
use App\Core\Database;

class ProductCostingService
{
    private Database $db;
    private DetailCostingService $detailCostingService;

    public function __construct(Application $app)
    {
        $this->db = $app->getDatabase();
        $this->detailCostingService = new DetailCostingService($app);
    }

    /**
     * Get product composition with costs
     */
    public function getComposition(int $productId): array
    {
        if (!$this->db->tableExists('product_components')) {
            return [];
        }

        $components = $this->db->fetchAll(
            "SELECT pc.*,
                    d.sku AS detail_sku, d.name AS detail_name, d.detail_type, d.image_path AS detail_image,
                    d.material_item_id, d.material_qty_grams, d.print_time_minutes,
                    i.sku AS item_sku, i.name AS item_name, i.image_path AS item_image,
                    COALESCE(sb.avg_cost, 0) AS item_avg_cost,
                    mat.name AS material_name, mat.sku AS material_sku,
                    (SELECT ia.attribute_value FROM item_attributes ia
                     WHERE ia.item_id = mat.id AND ia.attribute_name = 'filament_alias' LIMIT 1) AS material_alias
             FROM product_components pc
             LEFT JOIN details d ON pc.detail_id = d.id AND pc.component_type = 'detail'
             LEFT JOIN items i ON pc.item_id = i.id AND pc.component_type = 'item'
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             LEFT JOIN items mat ON d.material_item_id = mat.id
             WHERE pc.product_id = ?
             ORDER BY pc.sort_order, pc.id",
            [$productId]
        );

        // Calculate costs for each component
        foreach ($components as &$component) {
            $component['calculated_cost'] = $this->calculateComponentCost($component);
            $component['total_cost'] = $component['calculated_cost'] * (float)$component['quantity'];
        }

        return $components;
    }

    /**
     * Calculate cost for a single component
     */
    public function calculateComponentCost(array $component): float
    {
        // If cost is overridden, use the override value
        if (!empty($component['cost_override']) && $component['unit_cost'] > 0) {
            return (float)$component['unit_cost'];
        }

        if ($component['component_type'] === 'detail') {
            // Calculate detail production cost
            $detailData = [
                'detail_type' => $component['detail_type'] ?? 'printed',
                'material_item_id' => $component['material_item_id'] ?? null,
                'printer_id' => $component['printer_id'] ?? null,
                'material_qty_grams' => $component['material_qty_grams'] ?? 0,
                'print_time_minutes' => $component['print_time_minutes'] ?? 0,
            ];

            $costData = $this->detailCostingService->calculateCost($detailData);
            return $costData['total_cost'];

        } elseif ($component['component_type'] === 'item') {
            // Use weighted average cost from stock
            return (float)($component['item_avg_cost'] ?? 0);
        }

        return 0;
    }

    /**
     * Calculate full product cost
     */
    public function calculateProductCost(int $productId): array
    {
        $components = $this->getComposition($productId);
        $packaging = $this->getPackaging($productId);
        $operations = $this->getOperations($productId);

        $result = [
            'components' => $components,
            'packaging' => $packaging,
            'operations' => $operations,
            'details_cost' => 0,
            'items_cost' => 0,
            'assembly_cost' => 0,
            'labor_cost' => 0,
            'packaging_cost' => 0,
            'total_cost' => 0,
            'total_price' => 0, // Including packaging
            'component_count' => count($components),
            'packaging_count' => count($packaging),
            'operations_count' => count($operations),
            'total_time_minutes' => 0,
        ];

        foreach ($components as $component) {
            if ($component['component_type'] === 'detail') {
                $result['details_cost'] += $component['total_cost'];
            } else {
                $result['items_cost'] += $component['total_cost'];
            }
        }

        foreach ($packaging as $item) {
            $result['packaging_cost'] += $item['total_cost'];
        }

        // Calculate labor cost from operations
        foreach ($operations as $operation) {
            $result['labor_cost'] += $operation['operation_cost'];
            $result['total_time_minutes'] += (int)$operation['time_minutes'];
        }

        // Get assembly cost from product (legacy field, may be deprecated)
        $product = $this->db->fetch(
            "SELECT assembly_cost FROM products WHERE id = ?",
            [$productId]
        );
        $result['assembly_cost'] = (float)($product['assembly_cost'] ?? 0);

        // Production cost (without packaging): components + labor + assembly
        $result['total_cost'] = $result['details_cost'] + $result['items_cost'] + $result['labor_cost'] + $result['assembly_cost'];

        // Total price (including packaging)
        $result['total_price'] = $result['total_cost'] + $result['packaging_cost'];

        return $result;
    }

    /**
     * Update product production cost in database
     */
    public function updateProductCost(int $productId): float
    {
        $costData = $this->calculateProductCost($productId);

        // Check if columns exist and update
        $updateData = [];
        if ($this->db->columnExists('products', 'production_cost')) {
            $updateData['production_cost'] = $costData['total_cost'];
        }
        if ($this->db->columnExists('products', 'packaging_cost')) {
            $updateData['packaging_cost'] = $costData['packaging_cost'];
        }

        if (!empty($updateData)) {
            $this->db->update('products', $updateData, ['id' => $productId]);
        }

        return $costData['total_cost'];
    }

    /**
     * Add component to product
     */
    public function addComponent(int $productId, array $data): int
    {
        $insertData = [
            'product_id' => $productId,
            'component_type' => $data['component_type'],
            'detail_id' => $data['component_type'] === 'detail' ? ($data['detail_id'] ?? null) : null,
            'item_id' => $data['component_type'] === 'item' ? ($data['item_id'] ?? null) : null,
            'quantity' => (float)($data['quantity'] ?? 1),
            'unit_cost' => (float)($data['unit_cost'] ?? 0),
            'cost_override' => !empty($data['cost_override']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ];

        $id = $this->db->insert('product_components', $insertData);

        // Recalculate product cost
        $this->updateProductCost($productId);

        return $id;
    }

    /**
     * Update component
     */
    public function updateComponent(int $componentId, array $data): void
    {
        $component = $this->db->fetch(
            "SELECT product_id FROM product_components WHERE id = ?",
            [$componentId]
        );

        if (!$component) {
            return;
        }

        $updateData = [
            'quantity' => (float)($data['quantity'] ?? 1),
            'unit_cost' => (float)($data['unit_cost'] ?? 0),
            'cost_override' => !empty($data['cost_override']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ];

        $this->db->update('product_components', $updateData, ['id' => $componentId]);

        // Recalculate product cost
        $this->updateProductCost((int)$component['product_id']);
    }

    /**
     * Remove component from product
     */
    public function removeComponent(int $componentId): void
    {
        $component = $this->db->fetch(
            "SELECT product_id FROM product_components WHERE id = ?",
            [$componentId]
        );

        if (!$component) {
            return;
        }

        $this->db->delete('product_components', ['id' => $componentId]);

        // Recalculate product cost
        $this->updateProductCost((int)$component['product_id']);
    }

    /**
     * Get available details for adding to composition
     */
    public function getAvailableDetails(): array
    {
        return $this->db->fetchAll(
            "SELECT id, sku, name, detail_type, image_path
             FROM details
             WHERE is_active = 1
             ORDER BY sku"
        );
    }

    /**
     * Get available items (components) for adding to composition
     */
    public function getAvailableItems(): array
    {
        return $this->db->fetchAll(
            "SELECT i.id, i.sku, i.name, i.type, i.image_path, COALESCE(sb.avg_cost, 0) AS avg_cost
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE i.is_active = 1 AND i.type IN ('component', 'fasteners')
             ORDER BY i.sku"
        );
    }

    // ==================== PACKAGING METHODS ====================

    /**
     * Get product packaging with costs
     */
    public function getPackaging(int $productId): array
    {
        if (!$this->db->tableExists('product_packaging')) {
            return [];
        }

        $packaging = $this->db->fetchAll(
            "SELECT pp.*,
                    i.sku AS item_sku, i.name AS item_name, i.image_path AS item_image, i.unit,
                    COALESCE(sb.avg_cost, 0) AS item_avg_cost
             FROM product_packaging pp
             LEFT JOIN items i ON pp.item_id = i.id
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE pp.product_id = ?
             ORDER BY pp.sort_order, pp.id",
            [$productId]
        );

        // Calculate costs for each packaging item
        foreach ($packaging as &$item) {
            $item['calculated_cost'] = $this->calculatePackagingItemCost($item);
            $item['total_cost'] = $item['calculated_cost'] * (float)$item['quantity'];
        }

        return $packaging;
    }

    /**
     * Calculate cost for a single packaging item
     */
    public function calculatePackagingItemCost(array $item): float
    {
        // If cost is overridden, use the override value
        if (!empty($item['cost_override']) && $item['unit_cost'] > 0) {
            return (float)$item['unit_cost'];
        }

        // Use weighted average cost from stock
        return (float)($item['item_avg_cost'] ?? 0);
    }

    /**
     * Add packaging item to product
     */
    public function addPackaging(int $productId, array $data): int
    {
        $insertData = [
            'product_id' => $productId,
            'item_id' => (int)$data['item_id'],
            'quantity' => (float)($data['quantity'] ?? 1),
            'unit_cost' => (float)($data['unit_cost'] ?? 0),
            'cost_override' => !empty($data['cost_override']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ];

        $id = $this->db->insert('product_packaging', $insertData);

        // Recalculate product cost
        $this->updateProductCost($productId);

        return $id;
    }

    /**
     * Update packaging item
     */
    public function updatePackaging(int $packagingId, array $data): void
    {
        $packaging = $this->db->fetch(
            "SELECT product_id FROM product_packaging WHERE id = ?",
            [$packagingId]
        );

        if (!$packaging) {
            return;
        }

        $updateData = [
            'quantity' => (float)($data['quantity'] ?? 1),
            'unit_cost' => (float)($data['unit_cost'] ?? 0),
            'cost_override' => !empty($data['cost_override']) ? 1 : 0,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ];

        $this->db->update('product_packaging', $updateData, ['id' => $packagingId]);

        // Recalculate product cost
        $this->updateProductCost((int)$packaging['product_id']);
    }

    /**
     * Remove packaging item from product
     */
    public function removePackaging(int $packagingId): void
    {
        $packaging = $this->db->fetch(
            "SELECT product_id FROM product_packaging WHERE id = ?",
            [$packagingId]
        );

        if (!$packaging) {
            return;
        }

        $this->db->delete('product_packaging', ['id' => $packagingId]);

        // Recalculate product cost
        $this->updateProductCost((int)$packaging['product_id']);
    }

    /**
     * Get available packaging items from warehouse
     */
    public function getAvailablePackagingItems(): array
    {
        return $this->db->fetchAll(
            "SELECT i.id, i.sku, i.name, i.unit, i.image_path, COALESCE(sb.avg_cost, 0) AS avg_cost
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE i.is_active = 1 AND i.type = 'packaging'
             ORDER BY i.sku"
        );
    }

    // ==================== OPERATIONS METHODS ====================

    /**
     * Get product operations (routing/assembly steps) with costs
     */
    public function getOperations(int $productId): array
    {
        if (!$this->db->tableExists('product_operations')) {
            return [];
        }

        $operations = $this->db->fetchAll(
            "SELECT po.*
             FROM product_operations po
             WHERE po.product_id = ?
             ORDER BY po.sort_order, po.id",
            [$productId]
        );

        // Get linked components for each operation
        foreach ($operations as &$operation) {
            $operation['components'] = $this->getOperationComponents((int)$operation['id']);
            $operation['operation_cost'] = $this->calculateOperationCost($operation);
        }

        return $operations;
    }

    /**
     * Get components linked to an operation
     */
    public function getOperationComponents(int $operationId): array
    {
        if (!$this->db->tableExists('product_operation_components')) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT poc.*, pc.component_type, pc.quantity,
                    d.sku AS detail_sku, d.name AS detail_name,
                    i.sku AS item_sku, i.name AS item_name
             FROM product_operation_components poc
             JOIN product_components pc ON poc.component_id = pc.id
             LEFT JOIN details d ON pc.detail_id = d.id AND pc.component_type = 'detail'
             LEFT JOIN items i ON pc.item_id = i.id AND pc.component_type = 'item'
             WHERE poc.operation_id = ?
             ORDER BY poc.id",
            [$operationId]
        );
    }

    /**
     * Calculate cost for a single operation
     */
    public function calculateOperationCost(array $operation): float
    {
        $timeMinutes = (int)($operation['time_minutes'] ?? 0);
        $laborRate = (float)($operation['labor_rate'] ?? 0);

        if ($timeMinutes <= 0 || $laborRate <= 0) {
            return 0;
        }

        // Labor rate is per hour, convert minutes to hours
        return ($timeMinutes / 60) * $laborRate;
    }

    /**
     * Add operation to product
     */
    public function addOperation(int $productId, array $data): int
    {
        $insertData = [
            'product_id' => $productId,
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? null,
            'time_minutes' => (int)($data['time_minutes'] ?? 0),
            'labor_rate' => (float)($data['labor_rate'] ?? 0),
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ];

        $operationId = $this->db->insert('product_operations', $insertData);

        // Add linked components if provided
        if (!empty($data['component_ids']) && is_array($data['component_ids'])) {
            foreach ($data['component_ids'] as $componentId) {
                $this->addOperationComponent($operationId, (int)$componentId);
            }
        }

        // Recalculate product cost
        $this->updateProductCost($productId);

        return $operationId;
    }

    /**
     * Update operation
     */
    public function updateOperation(int $operationId, array $data): void
    {
        $operation = $this->db->fetch(
            "SELECT product_id FROM product_operations WHERE id = ?",
            [$operationId]
        );

        if (!$operation) {
            return;
        }

        $updateData = [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? null,
            'time_minutes' => (int)($data['time_minutes'] ?? 0),
            'labor_rate' => (float)($data['labor_rate'] ?? 0),
            'sort_order' => (int)($data['sort_order'] ?? 0),
        ];

        $this->db->update('product_operations', $updateData, ['id' => $operationId]);

        // Update linked components if provided
        if (isset($data['component_ids']) && is_array($data['component_ids'])) {
            // Remove existing links
            $this->db->delete('product_operation_components', ['operation_id' => $operationId]);

            // Add new links
            foreach ($data['component_ids'] as $componentId) {
                $this->addOperationComponent($operationId, (int)$componentId);
            }
        }

        // Recalculate product cost
        $this->updateProductCost((int)$operation['product_id']);
    }

    /**
     * Remove operation from product
     */
    public function removeOperation(int $operationId): void
    {
        $operation = $this->db->fetch(
            "SELECT product_id FROM product_operations WHERE id = ?",
            [$operationId]
        );

        if (!$operation) {
            return;
        }

        // Components links will be deleted by CASCADE
        $this->db->delete('product_operations', ['id' => $operationId]);

        // Recalculate product cost
        $this->updateProductCost((int)$operation['product_id']);
    }

    /**
     * Add component to operation
     */
    public function addOperationComponent(int $operationId, int $componentId): void
    {
        // Check if already linked
        $existing = $this->db->fetch(
            "SELECT id FROM product_operation_components WHERE operation_id = ? AND component_id = ?",
            [$operationId, $componentId]
        );

        if ($existing) {
            return;
        }

        $this->db->insert('product_operation_components', [
            'operation_id' => $operationId,
            'component_id' => $componentId,
        ]);
    }

    /**
     * Remove component from operation
     */
    public function removeOperationComponent(int $operationId, int $componentId): void
    {
        $this->db->delete('product_operation_components', [
            'operation_id' => $operationId,
            'component_id' => $componentId,
        ]);
    }
}
