<?php
/**
 * Item Service - SKU business logic
 */

namespace App\Services\Warehouse;

use App\Core\Application;
use App\Core\Database;
use App\Services\AuditService;

class ItemService
{
    private Application $app;
    private Database $db;
    private AuditService $audit;

    private array $types = [
        'material' => 'Material (Plastic)',
        'component' => 'Component (Purchased)',
        'part' => 'Part (Manufactured)',
        'consumable' => 'Consumable',
        'packaging' => 'Packaging'
    ];

    private array $units = [
        'pcs' => 'Pieces',
        'g' => 'Grams',
        'kg' => 'Kilograms',
        'm' => 'Meters',
        'l' => 'Liters',
        'set' => 'Sets'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
        $this->audit = new AuditService($app);
    }

    /**
     * Get types
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get units
     */
    public function getUnits(): array
    {
        return $this->units;
    }

    /**
     * Get option values for item attributes
     */
    public function getOptionValues(string $groupKey): array
    {
        if (!$this->db->tableExists('item_option_values')) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT id, name, is_filament
             FROM item_option_values
             WHERE group_key = ? AND is_active = 1
             ORDER BY name",
            [$groupKey]
        );
    }

    /**
     * Paginated list
     */
    public function paginate(int $page, int $perPage, array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(sku LIKE ? OR name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['type'])) {
            $where[] = "type = ?";
            $params[] = $filters['type'];
        }

        $whereStr = implode(' AND ', $where);

        // Count total
        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM items WHERE {$whereStr}",
            $params
        );

        $totalPages = max(1, ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        // Get items
        $items = $this->db->fetchAll(
            "SELECT i.*,
                    COALESCE(SUM(sb.on_hand), 0) as total_on_hand,
                    COALESCE(SUM(sb.reserved), 0) as total_reserved
             FROM items i
             LEFT JOIN stock_balances sb ON i.id = sb.item_id
             WHERE {$whereStr}
             GROUP BY i.id
             ORDER BY i.name ASC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM items WHERE id = ?",
            [$id]
        );
    }

    /**
     * Find by SKU
     */
    public function findBySku(string $sku): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM items WHERE sku = ?",
            [$sku]
        );
    }

    /**
     * Create item
     */
    public function create(array $data, array $attributes = [], ?int $userId = null): array
    {
        $this->db->beginTransaction();

        try {
            $itemData = [
                'sku' => $data['sku'],
                'name' => $data['name'],
                'type' => $data['type'],
                'unit' => $data['unit'],
                'description' => $data['description'] ?? '',
                'min_stock' => $data['min_stock'] ?? 0,
                'reorder_point' => $data['reorder_point'] ?? 0,
                'is_active' => 1
            ];

            if ($this->db->columnExists('items', 'image_path')) {
                $itemData['image_path'] = $data['image_path'] ?? null;
            }

            $id = $this->db->insert('items', $itemData);

            // Save attributes
            foreach ($attributes as $name => $value) {
                if (!empty($value)) {
                    $this->db->insert('item_attributes', [
                        'item_id' => $id,
                        'attribute_name' => $name,
                        'attribute_value' => $value
                    ]);
                }
            }

            // Create initial stock balance
            $this->db->insert('stock_balances', [
                'item_id' => $id,
                'on_hand' => 0,
                'reserved' => 0,
                'avg_cost' => 0
            ]);

            // Audit
            $this->audit->log('item.created', 'item', $id, null, $data, $userId);

            $this->db->commit();

            return $this->findById($id);

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Update item
     */
    public function update(int $id, array $data, array $attributes = [], ?int $userId = null): void
    {
        $old = $this->findById($id);

        if (!$old) {
            throw new \Exception('Item not found');
        }

        $this->db->beginTransaction();

        try {
            $itemData = [
                'sku' => $data['sku'],
                'name' => $data['name'],
                'type' => $data['type'],
                'unit' => $data['unit'],
                'description' => $data['description'] ?? '',
                'min_stock' => $data['min_stock'] ?? 0,
                'reorder_point' => $data['reorder_point'] ?? 0,
                'is_active' => $data['is_active'] ?? 1
            ];

            if ($this->db->columnExists('items', 'image_path')) {
                $itemData['image_path'] = $data['image_path'] ?? $old['image_path'] ?? null;
            }

            $this->db->update('items', $itemData, ['id' => $id]);

            // Update attributes
            $this->db->delete('item_attributes', ['item_id' => $id]);
            foreach ($attributes as $name => $value) {
                if (!empty($value)) {
                    $this->db->insert('item_attributes', [
                        'item_id' => $id,
                        'attribute_name' => $name,
                        'attribute_value' => $value
                    ]);
                }
            }

            // Audit
            $this->audit->log('item.updated', 'item', $id, $old, $data, $userId);

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get item attributes
     */
    public function getAttributes(int $itemId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT attribute_name, attribute_value FROM item_attributes WHERE item_id = ?",
            [$itemId]
        );

        $attrs = [];
        foreach ($rows as $row) {
            $attrs[$row['attribute_name']] = $row['attribute_value'];
        }

        return $attrs;
    }

    /**
     * Get stock for item
     */
    public function getStock(int $itemId): array
    {
        return $this->db->fetchAll(
            "SELECT sb.*
             FROM stock_balances sb
             WHERE sb.item_id = ?
             ORDER BY sb.id",
            [$itemId]
        );
    }

    /**
     * Get total stock for item
     */
    public function getTotalStock(int $itemId): array
    {
        $result = $this->db->fetch(
            "SELECT
                COALESCE(SUM(on_hand), 0) as on_hand,
                COALESCE(SUM(reserved), 0) as reserved
             FROM stock_balances
             WHERE item_id = ?",
            [$itemId]
        );

        $result['available'] = $result['on_hand'] - $result['reserved'];

        return $result;
    }

    /**
     * Get movement history
     */
    public function getMovementHistory(int $itemId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT sm.*, d.document_number, d.type as doc_type
             FROM stock_movements sm
             JOIN documents d ON sm.document_id = d.id
             WHERE sm.item_id = ?
             ORDER BY sm.created_at DESC
             LIMIT {$limit}",
            [$itemId]
        );
    }

    /**
     * Search items for autocomplete
     */
    public function search(string $term, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT id, sku, name, type, unit
             FROM items
             WHERE is_active = 1 AND (sku LIKE ? OR name LIKE ?)
             ORDER BY name
             LIMIT {$limit}",
            ["%{$term}%", "%{$term}%"]
        );
    }
}
