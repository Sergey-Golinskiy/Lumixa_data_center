<?php
/**
 * VariantsController - Product variants management
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class VariantsController extends Controller
{
    /**
     * List all variants
     */
    public function index(): void
    {
        $this->requirePermission('catalog.variants.view');

        $search = $_GET['search'] ?? '';
        $productId = $_GET['product_id'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(v.sku LIKE ? OR v.name LIKE ? OR p.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($productId) {
            $where[] = "v.product_id = ?";
            $params[] = $productId;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM variants v JOIN products p ON v.product_id = p.id WHERE {$whereClause}",
            $params
        );

        // Get variants
        $offset = ($page - 1) * $perPage;
        $variants = $this->db()->fetchAll(
            "SELECT v.*, p.name as product_name, p.code as product_code,
                    (SELECT COUNT(*) FROM bom WHERE variant_id = v.id AND status = 'active') as has_bom,
                    (SELECT COUNT(*) FROM routing WHERE variant_id = v.id AND status = 'active') as has_routing
             FROM variants v
             JOIN products p ON v.product_id = p.id
             WHERE {$whereClause}
             ORDER BY v.sku
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get products for filter
        $products = $this->db()->fetchAll("SELECT id, code, name FROM products ORDER BY code");

        $this->render('catalog/variants/index', [
            'title' => $this->app->getTranslator()->get('variants'),
            'variants' => $variants,
            'products' => $products,
            'search' => $search,
            'productId' => $productId,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show variant details
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.variants.view');

        $variant = $this->db()->fetch(
            "SELECT v.*, p.name as product_name, p.code as product_code
             FROM variants v
             JOIN products p ON v.product_id = p.id
             WHERE v.id = ?",
            [$id]
        );

        if (!$variant) {
            $this->notFound();
        }

        // Get active BOM
        $activeBom = $this->db()->fetch(
            "SELECT * FROM bom WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$id]
        );

        $bomLines = [];
        if ($activeBom) {
            $bomLines = $this->db()->fetchAll(
                "SELECT bl.*, i.sku, i.name as item_name, i.unit
                 FROM bom_lines bl
                 JOIN items i ON bl.item_id = i.id
                 WHERE bl.bom_id = ?
                 ORDER BY bl.sort_order",
                [$activeBom['id']]
            );
        }

        // Get active routing
        $activeRouting = $this->db()->fetch(
            "SELECT * FROM routing WHERE variant_id = ? AND status = 'active' LIMIT 1",
            [$id]
        );

        $routingOperations = [];
        if ($activeRouting) {
            $routingOperations = $this->db()->fetchAll(
                "SELECT * FROM routing_operations WHERE routing_id = ? ORDER BY sort_order",
                [$activeRouting['id']]
            );
        }

        // Get costing
        $costing = $this->db()->fetch(
            "SELECT * FROM variant_costs WHERE variant_id = ? ORDER BY calculated_at DESC LIMIT 1",
            [$id]
        );

        $this->render('catalog/variants/show', [
            'title' => $variant['sku'],
            'variant' => $variant,
            'activeBom' => $activeBom,
            'bomLines' => $bomLines,
            'activeRouting' => $activeRouting,
            'routingOperations' => $routingOperations,
            'costing' => $costing
        ]);
    }

    /**
     * Create variant form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.variants.create');

        $productId = $_GET['product_id'] ?? '';
        $products = $this->db()->fetchAll("SELECT id, code, name FROM products WHERE is_active = 1 ORDER BY code");

        $this->render('catalog/variants/form', [
            'title' => $this->app->getTranslator()->get('create_variant'),
            'variant' => null,
            'products' => $products,
            'preselectedProductId' => $productId
        ]);
    }

    /**
     * Store new variant
     */
    public function store(): void
    {
        $this->requirePermission('catalog.variants.create');
        $this->validateCSRF();

        $data = [
            'product_id' => (int)($_POST['product_id'] ?? 0),
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Parse attributes
        $attributes = [];
        $attrKeys = $_POST['attr_key'] ?? [];
        $attrValues = $_POST['attr_value'] ?? [];
        foreach ($attrKeys as $i => $key) {
            $key = trim($key);
            $value = trim($attrValues[$i] ?? '');
            if ($key && $value) {
                $attributes[$key] = $value;
            }
        }
        $data['attributes'] = !empty($attributes) ? json_encode($attributes) : null;

        // Validation
        $errors = [];

        if (empty($data['product_id'])) {
            $errors['product_id'] = 'Product is required';
        }

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            $exists = $this->db()->fetch("SELECT id FROM variants WHERE sku = ?", [$data['sku']]);
            if ($exists) {
                $errors['sku'] = 'SKU already exists';
            }
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/variants/create?product_id=' . $data['product_id']);
            return;
        }

        // Create variant
        $id = $this->db()->insert('variants', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->audit('variant.created', 'variants', $id, null, $data);
        $this->session->setFlash('success', 'Variant created successfully');
        $this->redirect("/catalog/variants/{$id}");
    }

    /**
     * Edit variant form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.variants.edit');

        $variant = $this->db()->fetch(
            "SELECT v.*, p.name as product_name
             FROM variants v
             JOIN products p ON v.product_id = p.id
             WHERE v.id = ?",
            [$id]
        );

        if (!$variant) {
            $this->notFound();
        }

        $products = $this->db()->fetchAll("SELECT id, code, name FROM products ORDER BY code");

        $this->render('catalog/variants/form', [
            'title' => $this->app->getTranslator()->get('edit_variant_title', ['sku' => $variant['sku']]),
            'variant' => $variant,
            'products' => $products,
            'preselectedProductId' => $variant['product_id']
        ]);
    }

    /**
     * Update variant
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.variants.edit');
        $this->validateCSRF();

        $variant = $this->db()->fetch("SELECT * FROM variants WHERE id = ?", [$id]);
        if (!$variant) {
            $this->notFound();
        }

        $data = [
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Parse attributes
        $attributes = [];
        $attrKeys = $_POST['attr_key'] ?? [];
        $attrValues = $_POST['attr_value'] ?? [];
        foreach ($attrKeys as $i => $key) {
            $key = trim($key);
            $value = trim($attrValues[$i] ?? '');
            if ($key && $value) {
                $attributes[$key] = $value;
            }
        }
        $data['attributes'] = !empty($attributes) ? json_encode($attributes) : null;

        // Validation
        $errors = [];

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            $exists = $this->db()->fetch("SELECT id FROM variants WHERE sku = ? AND id != ?", [$data['sku'], $id]);
            if ($exists) {
                $errors['sku'] = 'SKU already exists';
            }
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/catalog/variants/{$id}/edit");
            return;
        }

        // Update
        $this->db()->update('variants', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        $this->audit('variant.updated', 'variants', $id, $variant, $data);
        $this->session->setFlash('success', 'Variant updated successfully');
        $this->redirect("/catalog/variants/{$id}");
    }
}
