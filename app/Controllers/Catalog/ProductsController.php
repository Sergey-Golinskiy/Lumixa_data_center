<?php
/**
 * ProductsController - Product catalog management
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class ProductsController extends Controller
{
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

        // Get variants
        $variants = $this->db()->fetchAll(
            "SELECT v.*,
                    (SELECT COUNT(*) FROM bom WHERE variant_id = v.id AND status = 'active') as has_bom,
                    (SELECT COUNT(*) FROM routing WHERE variant_id = v.id AND status = 'active') as has_routing
             FROM variants v
             WHERE v.product_id = ?
             ORDER BY v.sku",
            [$id]
        );

        $this->render('catalog/products/show', [
            'title' => $product['name'],
            'product' => $product,
            'variants' => $variants
        ]);
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
