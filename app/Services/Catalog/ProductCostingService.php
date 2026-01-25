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
                    d.material_item_id, d.printer_id, d.material_qty_grams, d.print_time_minutes,
                    i.sku AS item_sku, i.name AS item_name, i.image_path AS item_image,
                    COALESCE(sb.avg_cost, 0) AS item_avg_cost
             FROM product_components pc
             LEFT JOIN details d ON pc.detail_id = d.id AND pc.component_type = 'detail'
             LEFT JOIN items i ON pc.item_id = i.id AND pc.component_type = 'item'
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
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

        $result = [
            'components' => $components,
            'details_cost' => 0,
            'items_cost' => 0,
            'assembly_cost' => 0,
            'total_cost' => 0,
            'component_count' => count($components),
        ];

        foreach ($components as $component) {
            if ($component['component_type'] === 'detail') {
                $result['details_cost'] += $component['total_cost'];
            } else {
                $result['items_cost'] += $component['total_cost'];
            }
        }

        // Get assembly cost from product
        $product = $this->db->fetch(
            "SELECT assembly_cost FROM products WHERE id = ?",
            [$productId]
        );
        $result['assembly_cost'] = (float)($product['assembly_cost'] ?? 0);

        $result['total_cost'] = $result['details_cost'] + $result['items_cost'] + $result['assembly_cost'];

        return $result;
    }

    /**
     * Update product production cost in database
     */
    public function updateProductCost(int $productId): float
    {
        $costData = $this->calculateProductCost($productId);

        // Check if column exists
        if ($this->db->columnExists('products', 'production_cost')) {
            $this->db->query(
                "UPDATE products SET production_cost = ? WHERE id = ?",
                [$costData['total_cost'], $productId]
            );
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
}
