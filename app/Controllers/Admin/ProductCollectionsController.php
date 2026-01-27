<?php
/**
 * ProductCollectionsController - Manage product collections
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class ProductCollectionsController extends Controller
{
    /**
     * List collections
     */
    public function index(): void
    {
        $this->requirePermission('admin.product_collections.view');
        if (!$this->ensureCollectionTable()) {
            return;
        }

        $collections = $this->db()->fetchAll(
            "SELECT pc.*,
                    (SELECT COUNT(*) FROM products p WHERE p.collection_id = pc.id) AS product_count
             FROM product_collections pc
             ORDER BY pc.name"
        );
        $translator = $this->app->getTranslator();

        $this->view('admin/product-collections/index', [
            'title' => $translator->get('product_collections'),
            'collections' => $collections
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('admin.product_collections.create');
        if (!$this->ensureCollectionTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        $this->view('admin/product-collections/form', [
            'title' => $translator->get('create_collection'),
            'collection' => null
        ]);
    }

    /**
     * Store collection
     */
    public function store(): void
    {
        $this->requirePermission('admin.product_collections.create');
        if (!$this->ensureCollectionTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/product-collections/create');
            return;
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'is_active' => $this->post('is_active') ? 1 : 0
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('collection_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM product_collections WHERE name = ?",
                [$data['name']]
            );
            if ($exists) {
                $errors['name'] = $translator->get('collection_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/admin/product-collections/create');
            return;
        }

        $id = $this->db()->insert('product_collections', [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->audit('product_collection.created', 'product_collections', $id, null, $data);
        $this->session->setFlash('success', $translator->get('collection_created_success'));
        $this->redirect('/admin/product-collections');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.product_collections.edit');
        if (!$this->ensureCollectionTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        $collection = $this->db()->fetch(
            "SELECT * FROM product_collections WHERE id = ?",
            [$id]
        );

        if (!$collection) {
            $this->notFound();
        }

        $this->view('admin/product-collections/form', [
            'title' => $translator->get('edit_collection_title', ['name' => $collection['name']]),
            'collection' => $collection
        ]);
    }

    /**
     * Update collection
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.product_collections.edit');
        if (!$this->ensureCollectionTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/product-collections/{$id}/edit");
            return;
        }

        $collection = $this->db()->fetch(
            "SELECT * FROM product_collections WHERE id = ?",
            [$id]
        );

        if (!$collection) {
            $this->notFound();
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'is_active' => $this->post('is_active') ? 1 : 0
        ];

        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('collection_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM product_collections WHERE name = ? AND id != ?",
                [$data['name'], $id]
            );
            if ($exists) {
                $errors['name'] = $translator->get('collection_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/product-collections/{$id}/edit");
            return;
        }

        $this->db()->update('product_collections', [
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'is_active' => $data['is_active'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('product_collection.updated', 'product_collections', $id, $collection, $data);
        $this->session->setFlash('success', $translator->get('collection_updated_success'));
        $this->redirect('/admin/product-collections');
    }

    /**
     * Delete collection
     */
    public function delete(string $id): void
    {
        $this->requirePermission('admin.product_collections.delete');
        if (!$this->ensureCollectionTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/product-collections');
            return;
        }

        $collection = $this->db()->fetch(
            "SELECT * FROM product_collections WHERE id = ?",
            [$id]
        );

        if (!$collection) {
            $this->notFound();
        }

        // Set collection_id to NULL for all products in this collection
        $this->db()->query(
            "UPDATE products SET collection_id = NULL WHERE collection_id = ?",
            [$id]
        );

        $this->db()->delete('product_collections', ['id' => $id]);
        $this->audit('product_collection.deleted', 'product_collections', $id, $collection, null);
        $this->session->setFlash('success', $translator->get('collection_deleted_success'));
        $this->redirect('/admin/product-collections');
    }

    private function ensureCollectionTable(): bool
    {
        if ($this->db()->tableExists('product_collections')) {
            return true;
        }

        $translator = $this->app->getTranslator();
        $this->session->setFlash('error', $translator->get('product_collections_missing'));
        $this->redirect('/admin/diagnostics');
        return false;
    }
}
