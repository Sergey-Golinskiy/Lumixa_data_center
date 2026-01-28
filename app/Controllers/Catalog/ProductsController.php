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
        $collectionId = $_GET['collection_id'] ?? '';
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

        if ($collectionId) {
            $where[] = "p.collection_id = ?";
            $params[] = $collectionId;
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
                "SELECT p.*, COALESCE(pc.name, p.category) as category_name, pcol.name as collection_name,
                        (SELECT COUNT(*) FROM variants WHERE product_id = p.id) as variant_count
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
                 WHERE {$whereClause}
                 ORDER BY p.code
                 LIMIT {$perPage} OFFSET {$offset}",
                $params
            );
        } else {
            $products = $this->db()->fetchAll(
                "SELECT p.*, p.category as category_name, pcol.name as collection_name,
                        (SELECT COUNT(*) FROM variants WHERE product_id = p.id) as variant_count
                 FROM products p
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
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

        // Get collections for filter
        $collections = [];
        $selectedCollection = null;
        if ($this->db()->tableExists('product_collections')) {
            $collections = $this->db()->fetchAll(
                "SELECT id, name FROM product_collections WHERE is_active = 1 ORDER BY name"
            );
            if ($collectionId) {
                $selectedCollection = $this->db()->fetch(
                    "SELECT id, name FROM product_collections WHERE id = ?",
                    [$collectionId]
                );
            }
        }

        $this->render('catalog/products/index', [
            'title' => $selectedCollection ? $selectedCollection['name'] : 'Products',
            'products' => $products,
            'categories' => $categories,
            'collections' => $collections,
            'categoryMode' => $categoryMode,
            'search' => $search,
            'category' => $category,
            'collectionId' => $collectionId,
            'selectedCollection' => $selectedCollection,
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
                "SELECT p.*, COALESCE(pc.name, p.category) as category_name, pcol.name as collection_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
                 WHERE p.id = ?",
                [$id]
            );
        } else {
            $product = $this->db()->fetch(
                "SELECT p.*, p.category as category_name, pcol.name as collection_name
                 FROM products p
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
                 WHERE p.id = ?",
                [$id]
            );
        }

        if (!$product) {
            $this->notFound();
        }

        // Get product composition, packaging, operations and cost
        $costingService = $this->getCostingService();
        $costData = $costingService->calculateProductCost((int)$id);

        $this->render('catalog/products/show', [
            'title' => $product['name'],
            'product' => $product,
            'components' => $costData['components'],
            'packaging' => $costData['packaging'] ?? [],
            'operations' => $costData['operations'] ?? [],
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
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $componentType = $_POST['component_type'] ?? '';
        $detailId = (int)($_POST['detail_id'] ?? 0);
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 1);

        if (!in_array($componentType, ['detail', 'item'])) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Invalid component type']);
                return;
            }
            $this->session->setFlash('error', 'Invalid component type');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        if ($componentType === 'detail' && $detailId <= 0) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Please select a detail']);
                return;
            }
            $this->session->setFlash('error', 'Please select a detail');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        if ($componentType === 'item' && $itemId <= 0) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Please select a component']);
                return;
            }
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

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'components' => $costData['components'],
                'message' => 'Component added successfully'
            ]);
            return;
        }

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
        $isAjax = $this->isAjax();

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

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'components' => $costData['components'],
                'message' => 'Component updated'
            ]);
            return;
        }

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
        $isAjax = $this->isAjax();

        $costingService = $this->getCostingService();
        $costingService->removeComponent((int)$componentId);

        $this->audit('product.component_removed', 'products', $id, null, [
            'component_id' => $componentId,
        ]);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'components' => $costData['components'],
                'message' => 'Component removed'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Component removed');
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
     * API: Get available packaging items
     */
    public function apiGetPackagingItems(): void
    {
        $this->requirePermission('catalog.products.view');

        $costingService = $this->getCostingService();
        $items = $costingService->getAvailablePackagingItems();

        $this->json(['success' => true, 'items' => $items]);
    }

    // ==================== PACKAGING METHODS ====================

    /**
     * Add packaging item to product
     */
    public function addPackaging(string $id): void
    {
        $this->requirePermission('catalog.products.packaging');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (float)($_POST['quantity'] ?? 1);

        if ($itemId <= 0) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Please select a packaging item']);
                return;
            }
            $this->session->setFlash('error', 'Please select a packaging item');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        $costingService = $this->getCostingService();
        $costingService->addPackaging((int)$id, [
            'item_id' => $itemId,
            'quantity' => $quantity,
        ]);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'packaging' => $costData['packaging'],
                'message' => 'Packaging item added'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Packaging item added');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Update packaging item quantity
     */
    public function updatePackaging(string $id, string $packagingId): void
    {
        $this->requirePermission('catalog.products.packaging');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $quantity = (float)($_POST['quantity'] ?? 1);

        $costingService = $this->getCostingService();
        $costingService->updatePackaging((int)$packagingId, [
            'quantity' => $quantity,
        ]);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'packaging' => $costData['packaging'],
                'message' => 'Packaging updated'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Packaging updated');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Remove packaging item from product
     */
    public function removePackaging(string $id, string $packagingId): void
    {
        $this->requirePermission('catalog.products.packaging');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $costingService = $this->getCostingService();
        $costingService->removePackaging((int)$packagingId);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'packaging' => $costData['packaging'],
                'message' => 'Packaging removed'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Packaging removed');
        $this->redirect("/catalog/products/{$id}");
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

        // Load collections if table exists
        $collections = [];
        if ($this->db()->tableExists('product_collections')) {
            $collections = $this->db()->fetchAll(
                "SELECT id, name FROM product_collections WHERE is_active = 1 ORDER BY name"
            );
        }

        $this->render('catalog/products/form', [
            'title' => 'Create Product',
            'product' => null,
            'categories' => $categories,
            'categoryMode' => $categoryMode,
            'collections' => $collections
        ]);
    }

    /**
     * Copy product - opens create form with pre-filled data from existing product
     */
    public function copy(string $id): void
    {
        $this->requirePermission('catalog.products.create');

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            $this->notFound();
        }

        // Load composition components to copy
        $components = $this->db()->fetchAll(
            "SELECT * FROM product_components WHERE product_id = ? ORDER BY sort_order, id",
            [$id]
        );

        // Store components in session for copying after new product is saved
        $this->session->set('copy_product_components', $components);
        $this->session->set('copy_product_source_id', $id);

        // Modify code to indicate it's a copy
        $product['code'] = $product['code'] . '-COPY';
        $product['id'] = null; // Clear ID so form treats it as new

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

        // Load collections if table exists
        $collections = [];
        if ($this->db()->tableExists('product_collections')) {
            $collections = $this->db()->fetchAll(
                "SELECT id, name FROM product_collections WHERE is_active = 1 ORDER BY name"
            );
        }

        $this->render('catalog/products/form', [
            'title' => 'Copy Product: ' . $product['name'],
            'product' => $product,
            'categories' => $categories,
            'categoryMode' => $categoryMode,
            'collections' => $collections,
            'isCopy' => true,
            'sourceProductId' => $id
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
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'website_url' => trim($_POST['website_url'] ?? '') ?: null
        ];
        if ($categoryMode === 'table') {
            $data['category_id'] = (int)($_POST['category_id'] ?? 0);
        } elseif ($categoryMode === 'legacy') {
            $data['category'] = trim($_POST['category'] ?? '');
        }
        // Handle collection_id if collections table exists
        if ($this->db()->tableExists('product_collections')) {
            $collectionId = (int)($_POST['collection_id'] ?? 0);
            $data['collection_id'] = $collectionId > 0 ? $collectionId : null;
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

        // Copy composition components if this was a copy operation
        $copyComponents = $this->session->get('copy_product_components');
        if ($copyComponents && is_array($copyComponents)) {
            foreach ($copyComponents as $component) {
                $this->db()->insert('product_components', [
                    'product_id' => $id,
                    'component_type' => $component['component_type'],
                    'detail_id' => $component['detail_id'],
                    'item_id' => $component['item_id'],
                    'quantity' => $component['quantity'],
                    'unit_cost' => $component['unit_cost'],
                    'cost_override' => $component['cost_override'],
                    'sort_order' => $component['sort_order'],
                    'notes' => $component['notes'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            // Clear session data
            $this->session->remove('copy_product_components');
            $this->session->remove('copy_product_source_id');
        }

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

        // Load collections if table exists
        $collections = [];
        if ($this->db()->tableExists('product_collections')) {
            $collections = $this->db()->fetchAll(
                "SELECT id, name FROM product_collections WHERE is_active = 1 ORDER BY name"
            );
        }

        $this->render('catalog/products/form', [
            'title' => "Edit: {$product['name']}",
            'product' => $product,
            'categories' => $categories,
            'categoryMode' => $categoryMode,
            'collections' => $collections
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
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'website_url' => trim($_POST['website_url'] ?? '') ?: null
        ];
        if ($categoryMode === 'table') {
            $data['category_id'] = (int)($_POST['category_id'] ?? 0);
        } elseif ($categoryMode === 'legacy') {
            $data['category'] = trim($_POST['category'] ?? '');
        }
        // Handle collection_id if collections table exists
        if ($this->db()->tableExists('product_collections')) {
            $collectionId = (int)($_POST['collection_id'] ?? 0);
            $data['collection_id'] = $collectionId > 0 ? $collectionId : null;
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

    // ==================== OPERATIONS METHODS ====================

    /**
     * Add operation to product
     */
    public function addOperation(string $id): void
    {
        $this->requirePermission('catalog.products.operations');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $timeMinutes = (int)($_POST['time_minutes'] ?? 0);
        $laborRate = (float)($_POST['labor_rate'] ?? 0);
        $componentIds = $_POST['component_ids'] ?? [];

        if (empty($name)) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Operation name is required']);
                return;
            }
            $this->session->setFlash('error', 'Operation name is required');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        $costingService = $this->getCostingService();
        $costingService->addOperation((int)$id, [
            'name' => $name,
            'description' => $description,
            'time_minutes' => $timeMinutes,
            'labor_rate' => $laborRate,
            'component_ids' => is_array($componentIds) ? $componentIds : [],
        ]);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'operations' => $costData['operations'],
                'message' => 'Operation added'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Operation added');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Update operation
     */
    public function updateOperation(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.products.operations');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $timeMinutes = (int)($_POST['time_minutes'] ?? 0);
        $laborRate = (float)($_POST['labor_rate'] ?? 0);
        $componentIds = $_POST['component_ids'] ?? [];

        $costingService = $this->getCostingService();
        $costingService->updateOperation((int)$operationId, [
            'name' => $name,
            'description' => $description,
            'time_minutes' => $timeMinutes,
            'labor_rate' => $laborRate,
            'component_ids' => is_array($componentIds) ? $componentIds : [],
        ]);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'operations' => $costData['operations'],
                'message' => 'Operation updated'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Operation updated');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Remove operation from product
     */
    public function removeOperation(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.products.operations');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        $costingService = $this->getCostingService();
        $costingService->removeOperation((int)$operationId);

        if ($isAjax) {
            $costData = $costingService->calculateProductCost((int)$id);
            $this->json([
                'success' => true,
                'costData' => $costData,
                'operations' => $costData['operations'],
                'message' => 'Operation removed'
            ]);
            return;
        }

        $this->session->setFlash('success', 'Operation removed');
        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Move operation up in sort order
     */
    public function moveOperationUp(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.products.operations');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        // Get current operation
        $current = $this->db()->fetch(
            "SELECT * FROM product_operations WHERE id = ? AND product_id = ?",
            [$operationId, $id]
        );

        if (!$current) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Operation not found']);
                return;
            }
            $this->session->setFlash('error', 'Operation not found');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        // Find the operation above (with smaller sort_order)
        $above = $this->db()->fetch(
            "SELECT * FROM product_operations
             WHERE product_id = ? AND sort_order < ?
             ORDER BY sort_order DESC LIMIT 1",
            [$id, $current['sort_order']]
        );

        if ($above) {
            // Swap sort_order values
            $this->db()->update('product_operations',
                ['sort_order' => $above['sort_order']],
                ['id' => $current['id']]
            );
            $this->db()->update('product_operations',
                ['sort_order' => $current['sort_order']],
                ['id' => $above['id']]
            );
        }

        if ($isAjax) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * Move operation down in sort order
     */
    public function moveOperationDown(string $id, string $operationId): void
    {
        $this->requirePermission('catalog.products.operations');
        $this->validateCSRF();
        $isAjax = $this->isAjax();

        $product = $this->db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Product not found']);
                return;
            }
            $this->notFound();
        }

        // Get current operation
        $current = $this->db()->fetch(
            "SELECT * FROM product_operations WHERE id = ? AND product_id = ?",
            [$operationId, $id]
        );

        if (!$current) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Operation not found']);
                return;
            }
            $this->session->setFlash('error', 'Operation not found');
            $this->redirect("/catalog/products/{$id}");
            return;
        }

        // Find the operation below (with larger sort_order)
        $below = $this->db()->fetch(
            "SELECT * FROM product_operations
             WHERE product_id = ? AND sort_order > ?
             ORDER BY sort_order ASC LIMIT 1",
            [$id, $current['sort_order']]
        );

        if ($below) {
            // Swap sort_order values
            $this->db()->update('product_operations',
                ['sort_order' => $below['sort_order']],
                ['id' => $current['id']]
            );
            $this->db()->update('product_operations',
                ['sort_order' => $current['sort_order']],
                ['id' => $below['id']]
            );
        }

        if ($isAjax) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirect("/catalog/products/{$id}");
    }

    /**
     * API: Get product components for operations
     */
    public function apiGetProductComponents(string $id): void
    {
        $this->requirePermission('catalog.products.view');

        $components = $this->db()->fetchAll(
            "SELECT pc.id, pc.component_type, pc.quantity,
                    COALESCE(d.sku, i.sku) AS sku,
                    COALESCE(d.name, i.name) AS name
             FROM product_components pc
             LEFT JOIN details d ON pc.detail_id = d.id AND pc.component_type = 'detail'
             LEFT JOIN items i ON pc.item_id = i.id AND pc.component_type = 'item'
             WHERE pc.product_id = ?
             ORDER BY pc.sort_order, pc.id",
            [$id]
        );

        $this->json(['success' => true, 'components' => $components]);
    }

    /**
     * View product specification (BOM)
     */
    public function specification(string $id): void
    {
        $this->requirePermission('catalog.products.view');

        $specData = $this->getSpecificationData($id);
        if (!$specData) {
            $this->notFound();
        }

        $this->render('catalog/products/specification', [
            'title' => $this->app->getTranslator()->get('product_specification') . ': ' . $specData['product']['code'],
            'product' => $specData['product'],
            'components' => $specData['components'],
            'packaging' => $specData['packaging'],
            'operations' => $specData['operations'],
            'costData' => $specData['costData'],
            'generatedAt' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Download product specification as PDF
     */
    public function specificationPdf(string $id): void
    {
        $this->requirePermission('catalog.products.view');

        $specData = $this->getSpecificationData($id);
        if (!$specData) {
            $this->notFound();
        }

        // Use View class without layout for PDF
        $this->view->setLayout(null);
        $html = $this->view->render('catalog/products/specification-pdf', [
            'product' => $specData['product'],
            'components' => $specData['components'],
            'packaging' => $specData['packaging'],
            'operations' => $specData['operations'],
            'costData' => $specData['costData'],
            'generatedAt' => date('Y-m-d H:i:s')
        ]);

        // Output as downloadable HTML (can be printed to PDF by browser)
        $filename = 'specification_' . $specData['product']['code'] . '_' . date('Y-m-d') . '.html';

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $html;
        exit;
    }

    /**
     * Get specification data for product
     */
    private function getSpecificationData(string $id): ?array
    {
        $categorySupport = $this->getCategorySupport();
        $categoryMode = $categorySupport['useTable'] ? 'table' : ($categorySupport['categoryName'] ? 'legacy' : 'none');

        if ($categoryMode === 'table') {
            $product = $this->db()->fetch(
                "SELECT p.*, COALESCE(pc.name, p.category) as category_name, pcol.name as collection_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
                 WHERE p.id = ?",
                [$id]
            );
        } else {
            $product = $this->db()->fetch(
                "SELECT p.*, p.category as category_name, pcol.name as collection_name
                 FROM products p
                 LEFT JOIN product_collections pcol ON p.collection_id = pcol.id
                 WHERE p.id = ?",
                [$id]
            );
        }

        if (!$product) {
            return null;
        }

        $costingService = $this->getCostingService();
        $costData = $costingService->calculateProductCost((int)$id);

        return [
            'product' => $product,
            'components' => $costData['components'] ?? [],
            'packaging' => $costData['packaging'] ?? [],
            'operations' => $costData['operations'] ?? [],
            'costData' => $costData
        ];
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
