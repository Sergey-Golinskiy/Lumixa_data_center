<?php
/**
 * ProductCategoriesController - Manage product categories
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class ProductCategoriesController extends Controller
{
    /**
     * List categories
     */
    public function index(): void
    {
        $this->requirePermission('admin.product_categories.view');

        $categories = $this->db()->fetchAll(
            "SELECT * FROM product_categories ORDER BY name"
        );
        $translator = $this->app->getTranslator();

        $this->view('admin/product-categories/index', [
            'title' => $translator->get('product_categories'),
            'categories' => $categories
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('admin.product_categories.create');
        $translator = $this->app->getTranslator();

        $this->view('admin/product-categories/form', [
            'title' => $translator->get('create_category'),
            'category' => null
        ]);
    }

    /**
     * Store category
     */
    public function store(): void
    {
        $this->requirePermission('admin.product_categories.create');
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/product-categories/create');
            return;
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'is_active' => $this->post('is_active') ? 1 : 0
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('category_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM product_categories WHERE name = ?",
                [$data['name']]
            );
            if ($exists) {
                $errors['name'] = $translator->get('category_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/admin/product-categories/create');
            return;
        }

        $id = $this->db()->insert('product_categories', [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->audit('product_category.created', 'product_categories', $id, null, $data);
        $this->session->setFlash('success', $translator->get('category_created_success'));
        $this->redirect('/admin/product-categories');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.product_categories.edit');
        $translator = $this->app->getTranslator();

        $category = $this->db()->fetch(
            "SELECT * FROM product_categories WHERE id = ?",
            [$id]
        );

        if (!$category) {
            $this->notFound();
        }

        $this->view('admin/product-categories/form', [
            'title' => $translator->get('edit_category_title', ['name' => $category['name']]),
            'category' => $category
        ]);
    }

    /**
     * Update category
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.product_categories.edit');
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/product-categories/{$id}/edit");
            return;
        }

        $category = $this->db()->fetch(
            "SELECT * FROM product_categories WHERE id = ?",
            [$id]
        );

        if (!$category) {
            $this->notFound();
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'is_active' => $this->post('is_active') ? 1 : 0
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('category_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM product_categories WHERE name = ? AND id != ?",
                [$data['name'], $id]
            );
            if ($exists) {
                $errors['name'] = $translator->get('category_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/product-categories/{$id}/edit");
            return;
        }

        $this->db()->update('product_categories', [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('product_category.updated', 'product_categories', $id, $category, $data);
        $this->session->setFlash('success', $translator->get('category_updated_success'));
        $this->redirect('/admin/product-categories');
    }

    /**
     * Delete category
     */
    public function delete(string $id): void
    {
        $this->requirePermission('admin.product_categories.delete');
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/product-categories');
            return;
        }

        $category = $this->db()->fetch(
            "SELECT * FROM product_categories WHERE id = ?",
            [$id]
        );

        if (!$category) {
            $this->notFound();
        }

        $inUse = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$id]
        );

        if ((int)$inUse > 0) {
            $this->session->setFlash('error', $translator->get('category_in_use'));
            $this->redirect('/admin/product-categories');
            return;
        }

        $this->db()->delete('product_categories', ['id' => $id]);
        $this->audit('product_category.deleted', 'product_categories', $id, $category, null);
        $this->session->setFlash('success', $translator->get('category_deleted_success'));
        $this->redirect('/admin/product-categories');
    }
}
