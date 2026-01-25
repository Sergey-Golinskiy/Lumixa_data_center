<?php
/**
 * ProductsController - Product catalog management
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;
use App\Services\Catalog\ProductCostingService;

class ProductsController extends Controller
{
    private ?ProductCostingService $costingService = null;

    private function getCostingService(): ProductCostingService
    {
        if ($this->costingService === null) {
            $this->costingService = new ProductCostingService($this->app);
        }
        return $this->costingService;
    }

    /**
     * List all products
     */
    public function index(): void
    {
        $this->requirePermission('catalog.products.view');

        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(p.code LIKE ? OR p.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($category && $categoryMode === 'table') {
            $where[] = "p.category_id = ?";
            $params[] = $category;
        } elseif ($category && $categoryMode === 'legacy') {
            $where[] = "p.category = ?";
            $params[] = $category;
        }

        if ($status === 'active') {
            $where[] = "p.is_active = 1";
        } elseif ($status === 'inactive') {
            $where[] = "p.is_active = 0";
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $total = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM products p WHERE {$whereClause}",
            $params
        );

        // Get products with variant count
        $offset = ($page - 1) * $perPage;
        if ($categoryMode === 'table') {
            $products = $this->db()->fetchAll(
                "SELECT p.*, COALESCE(pc.name, p.category) as category_name,
                        (SELECT COUNT(*) FROM variants WHERE product_id = p.id) as variant_count
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE {$whereClause}
                 ORDER BY p.code
                 LIMIT {$perPage} OFFSET {$offset}",
                $params
            );
        } else {
            $products = $this->db()->fetchAll(
                "SELECT p.*, p.category as category_name,
                        (SELECT COUNT(*) FROM variants WHERE product_id = p.id) as variant_count
                 FROM products p
                 WHERE {$whereClause}
                 ORDER BY p.code
                 LIMIT {$perPage} OFFSET {$offset}",
                $params
            );
        }

        // Get categories for filter
        if ($categoryMode === 'table') {
            $categories = $this->db()->fetchAll(
                "SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY name"
            );
        } elseif ($categoryMode === 'legacy') {
            $categories = $this->db()->fetchAll(
                "SELECT DISTINCT category AS id, category AS name
                 FROM products
                 WHERE category IS NOT NULL AND category != ''
                 ORDER BY category"
            );
        } else {
            $categories = [];
        }

        $this->render('catalog/products/index', [
            'title' => 'Products',
            'products' => $products,
            'categories' => $categories,
            'categoryMode' => $categoryMode,
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show product details
     */
    public function show(string $id): void
    {
        $this->requirePermission('catalog.products.view');
        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');

        if ($categoryMode === 'table') {
            $product = $this->db()->fetch(
                "SELECT p.*, COALESCE(pc.name, p.category) as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.id = ?",
                [$id]
            );
        } else {
            $product = $this->db()->fetch(
                "SELECT p.*, p.category as category_name
                 FROM products p
                 WHERE p.id = ?",
                [$id]
            );
        }

        if (!$product) {
            $this->notFound();
        }

        // Get product composition and cost
        $costingService = $this->getCostingService();
        $costData = $costingService->calculateProductCost((int)$id);

        $this->render('catalog/products/show', [
            'title' => $product['name'],
            'product' => $product,
            'components' => $costData['components'],
            'costData' => $costData
        ]);
    }

    /**
     * Add component to product composition
     */
    public function addComponent(string $id): void
    {
        $this->requirePermission('catalog.products.composition');
        $this->validateCSRF();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            $this->notFound();
        }

        $componentType = $_POST['component_type'] ?? '';
        $detailId = (int)($_POST['detail_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 1);

        if (!in_array($componentType, ['detail', 'item'])) {
            $this->session->setFlash('error', 'Invalid component type');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        if ($componentType === 'detail' && $detailId <= 0) {
            $this->session->setFlash('error', 'Please select a detail');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        if ($componentType === 'item' && $itemId <= 0) {
            $this->session->setFlash('error', 'Please select a component');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        $costingService = $this->getCostingService();
        $costingService->addComponent((int)$id, [
            'component_type' => $componentType,
            'detail_id' => $detailId,
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);

        $this->audit('product.component_added', 'products', $id, null, [
            'component_type' => $componentType,
            'detail_id' => $detailId,
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);

        $this->session->setFlash('success', 'Component added successfully');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Update component in product composition
     */
    public function updateComponent(string $id, string $componentId): void
    {
        $this->requirePermission('catalog.products.composition');
        $this->validateCSRF();

        $quantity = (float)($_POST['quantity'] ?? 1);
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $costOverride = isset($_POST['cost_override']) ? 1 : 0;

        $costingService = $this->getCostingService();
        $costingService->updateComponent((int)$componentId, [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'cost_override' => $costOverride,
        ]);

        $this->audit('product.component_updated', 'products', $id, null, [
            'component_id' => $componentId,
            'quantity' => $quantity,
        ]);

        $this->session->setFlash('success', 'Component updated');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Remove component from product composition
     */
    public function removeComponent(string $id, string $componentId): void
    {
        $this->requirePermission('catalog.products.composition');
        $this->validateCSRF();

        $costingService = $this->getCostingService();
        $costingService->removeComponent((int)$componentId);

        $this->audit('product.component_removed', 'products', $id, null, [
            'component_id' => $componentId,
        ]);

        $this->session->setFlash('success', 'Component removed');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Update assembly cost
     */
    public function updateAssemblyCost(string $id): void
    {
        $this->requirePermission('catalog.products.composition');
        $this->validateCSRF();

        $assemblyCost = (float)($_POST['assembly_cost'] ?? 0);

        $this->db()->update('products', [
            'assembly_cost' => $assemblyCost,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        // Recalculate total cost
        $costingService = $this->getCostingService();
        $costingService->updateProductCost((int)$id);

        $this->session->setFlash('success', 'Assembly cost updated');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * API: Get available details for composition
     */
    public function apiGetDetails(): void
    {
        $this->requirePermission('catalog.products.view');

        $costingService = $this->getCostingService();
        $details = $costingService->getAvailableDetails();

        $this->json(['success' => true, 'details' => $details]);
    }

    /**
     * API: Get available items for composition
     */
    public function apiGetItems(): void
    {
        $this->requirePermission('catalog.products.view');

        $costingService = $this->getCostingService();
        $items = $costingService->getAvailableItems();

        $this->json(['success' => true, 'items' => $items]);
    }

    /**
     * Create product form
     */
    public function create(): void
    {
        $this->requirePermission('catalog.products.create');

        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');
        if ($categoryMode === 'table') {
            $categories = $this->db()->fetchAll(
                "SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY name"
            );
        } elseif ($categoryMode === 'legacy') {
            $categories = $this->db()->fetchAll(
                "SELECT DISTINCT category AS id, category AS name
                 FROM products
                 WHERE category IS NOT NULL AND category != ''
                 ORDER BY category"
            );
        } else {
            $categories = [];
        }

        $this->render('catalog/products/form', [
            'title' => 'Create Product',
            'product' => null,
            'categories' => $categories,
            'categoryMode' => $categoryMode
        ]);
    }

    /**
     * Store new product
     */
    public function store(): void
    {
        $this->requirePermission('catalog.products.create');
        $this->validateCSRF();
        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');

        $data = [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        if ($categoryMode === 'table') {
            $data['category_id'] = (int)($_POST['category_id'] ?? 0);
        } elseif ($categoryMode === 'legacy') {
            $data['category'] = trim($_POST['category'] ?? '');
        }
        $imagePath = $this->storeImageUpload('image', 'products');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        // Validation
        $errors = [];

        if (empty($data['code'])) {
            $errors['code'] = 'Product code is required';
        } else {
            $exists = $this->db()->fetch("SELECT id FROM products WHERE code = ?", [$data['code']]);
            if ($exists) {
                $errors['code'] = 'Product code already exists';
            }
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Product name is required';
        }

        if ($categoryMode === 'table') {
            if (empty($data['category_id'])) {
                $errors['category_id'] = 'Category is required';
            } else {
                $categoryRow = $this->db()->fetch(
                    "SELECT id, name FROM product_categories WHERE id = ? AND is_active = 1",
                    [$data['category_id']]
                );
                if (!$categoryRow) {
                    $errors['category_id'] = 'Invalid category';
                } else {
                    $data['category'] = $categoryRow['name'];
                }
            }
        } elseif ($categoryMode === 'legacy') {
            if ($data['category'] === '') {
                $errors['category'] = 'Category is required';
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/catalog/products/create');
            return;
        }

        // Create product
        $id = $this->db()->insert('products', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->audit('product.created', 'products', $id, null, $data);
        $this->session->setFlash('success', 'Product created successfully');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Edit product form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('catalog.products.edit');

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            $this->notFound();
        }

        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');
        if ($categoryMode === 'table') {
            $categories = $this->db()->fetchAll(
                "SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY name"
            );
        } elseif ($categoryMode === 'legacy') {
            $categories = $this->db()->fetchAll(
                "SELECT DISTINCT category AS id, category AS name
                 FROM products
                 WHERE category IS NOT NULL AND category != ''
                 ORDER BY category"
            );
        } else {
            $categories = [];
        }

        $this->render('catalog/products/form', [
            'title' => "Edit: {$product['name']}",
            'product' => $product,
            'categories' => $categories,
            'categoryMode' => $categoryMode
        ]);
    }

    /**
     * Update product
     */
    public function update(string $id): void
    {
        $this->requirePermission('catalog.products.edit');
        $this->validateCSRF();
        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            $this->notFound();
        }

        $data = [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        if ($categoryMode === 'table') {
            $data['category_id'] = (int)($_POST['category_id'] ?? 0);
        } elseif ($categoryMode === 'legacy') {
            $data['category'] = trim($_POST['category'] ?? '');
        }
        $imagePath = $this->storeImageUpload('image', 'products');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        // Validation
        $errors = [];

        if (empty($data['code'])) {
            $errors['code'] = 'Product code is required';
        } else {
            $exists = $this->db()->fetch("SELECT id FROM products WHERE code = ? AND id != ?", [$data['code'], $id]);
            if ($exists) {
                $errors['code'] = 'Product code already exists';
            }
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Product name is required';
        }

        if ($categoryMode === 'table') {
            if (empty($data['category_id'])) {
                $errors['category_id'] = 'Category is required';
            } else {
                $categoryRow = $this->db()->fetch(
                    "SELECT id, name FROM product_categories WHERE id = ? AND is_active = 1",
                    [$data['category_id']]
                );
                if (!$categoryRow) {
                    $errors['category_id'] = 'Invalid category';
                } else {
                    $data['category'] = $categoryRow['name'];
                }
            }
        } elseif ($categoryMode === 'legacy') {
            if ($data['category'] === '') {
                $errors['category'] = 'Category is required';
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/catalog/products/{$id}/edit");
            return;
        }

        // Update
        $this->db()->update('products', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        $this->audit('product.updated', 'products', $id, $product, $data);
        $this->session->setFlash('success', 'Product updated successfully');
        $this->redirect("/catalog/products/{$id}");
    }

    private function getCategorySupport(): array
    {
        $hasCategoryTable = $this->db()->tableExists('product_categories');
        $hasCategoryId = $this->db()->columnExists('products', 'category_id');
        $hasCategoryName = $this->db()->columnExists('products', 'category');

        return [
            'table' => $hasCategoryTable,
            'categoryId' => $hasCategoryId,
            'categoryName' => $hasCategoryName,
            'useTable' => $hasCategoryTable && $hasCategoryId,
        ];
    }
}
