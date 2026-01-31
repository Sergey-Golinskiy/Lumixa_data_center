<?php
/**
 * OrderStatusesController - Manage order statuses
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class OrderStatusesController extends Controller
{
    /**
     * List all order statuses
     */
    public function index(): void
    {
        $this->requirePermission('admin.order_statuses.view');
        if (!$this->ensureTable()) {
            return;
        }

        $statuses = $this->db()->fetchAll(
            "SELECT * FROM order_statuses ORDER BY sort_order, name"
        );
        $translator = $this->app->getTranslator();

        $this->view('admin/order-statuses/index', [
            'title' => $translator->get('order_statuses'),
            'statuses' => $statuses
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requirePermission('admin.order_statuses.create');
        if (!$this->ensureTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        // Get next sort order
        $maxOrder = $this->db()->fetchColumn(
            "SELECT MAX(sort_order) FROM order_statuses"
        );

        $this->view('admin/order-statuses/form', [
            'title' => $translator->get('create_order_status'),
            'status' => null,
            'nextSortOrder' => ($maxOrder ?? 0) + 10
        ]);
    }

    /**
     * Store new order status
     */
    public function store(): void
    {
        $this->requirePermission('admin.order_statuses.create');
        if (!$this->ensureTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/order-statuses/create');
            return;
        }

        $data = $this->getFormData();
        $errors = $this->validateData($data);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect('/admin/order-statuses/create');
            return;
        }

        // If this is default, unset other defaults
        if ($data['is_default']) {
            $this->db()->execute("UPDATE order_statuses SET is_default = 0");
        }

        $id = $this->db()->insert('order_statuses', [
            'code' => $data['code'],
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'] ?: null,
            'sort_order' => $data['sort_order'],
            'is_default' => $data['is_default'],
            'is_final' => $data['is_final'],
            'is_active' => $data['is_active'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->audit('order_status.created', 'order_statuses', $id, null, $data);
        $this->session->setFlash('success', $translator->get('order_status_created_success'));
        $this->redirect('/admin/order-statuses');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.order_statuses.edit');
        if (!$this->ensureTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        $status = $this->db()->fetch(
            "SELECT * FROM order_statuses WHERE id = ?",
            [$id]
        );

        if (!$status) {
            $this->notFound();
        }

        $this->view('admin/order-statuses/form', [
            'title' => $translator->get('edit_order_status_title', ['name' => $status['name']]),
            'status' => $status,
            'nextSortOrder' => $status['sort_order']
        ]);
    }

    /**
     * Update order status
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.order_statuses.edit');
        if (!$this->ensureTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/order-statuses/{$id}/edit");
            return;
        }

        $status = $this->db()->fetch(
            "SELECT * FROM order_statuses WHERE id = ?",
            [$id]
        );

        if (!$status) {
            $this->notFound();
        }

        $data = $this->getFormData();
        $errors = $this->validateData($data, $id);

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/order-statuses/{$id}/edit");
            return;
        }

        // If this is default, unset other defaults
        if ($data['is_default']) {
            $this->db()->execute("UPDATE order_statuses SET is_default = 0 WHERE id != ?", [$id]);
        }

        $this->db()->update('order_statuses', [
            'code' => $data['code'],
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'] ?: null,
            'sort_order' => $data['sort_order'],
            'is_default' => $data['is_default'],
            'is_final' => $data['is_final'],
            'is_active' => $data['is_active'],
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $this->audit('order_status.updated', 'order_statuses', $id, $status, $data);
        $this->session->setFlash('success', $translator->get('order_status_updated_success'));
        $this->redirect('/admin/order-statuses');
    }

    /**
     * Delete order status
     */
    public function delete(string $id): void
    {
        $this->requirePermission('admin.order_statuses.delete');
        if (!$this->ensureTable()) {
            return;
        }
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/order-statuses');
            return;
        }

        $status = $this->db()->fetch(
            "SELECT * FROM order_statuses WHERE id = ?",
            [$id]
        );

        if (!$status) {
            $this->notFound();
        }

        // Check if status is in use
        $inUse = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM sales_orders WHERE status = ?",
            [$status['code']]
        );

        if ((int)$inUse > 0) {
            $this->session->setFlash('error', $translator->get('order_status_in_use', ['count' => $inUse]));
            $this->redirect('/admin/order-statuses');
            return;
        }

        // Don't allow deleting default status
        if ($status['is_default']) {
            $this->session->setFlash('error', $translator->get('cannot_delete_default_status'));
            $this->redirect('/admin/order-statuses');
            return;
        }

        $this->db()->delete('order_statuses', ['id' => $id]);
        $this->audit('order_status.deleted', 'order_statuses', $id, $status, null);
        $this->session->setFlash('success', $translator->get('order_status_deleted_success'));
        $this->redirect('/admin/order-statuses');
    }

    /**
     * Get form data from POST
     */
    private function getFormData(): array
    {
        return [
            'code' => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $this->post('code', '')))),
            'name' => trim($this->post('name', '')),
            'color' => $this->post('color', '#6b7280'),
            'description' => trim($this->post('description', '')),
            'sort_order' => (int)$this->post('sort_order', 0),
            'is_default' => $this->post('is_default') ? 1 : 0,
            'is_final' => $this->post('is_final') ? 1 : 0,
            'is_active' => $this->post('is_active') ? 1 : 0
        ];
    }

    /**
     * Validate form data
     */
    private function validateData(array $data, ?string $excludeId = null): array
    {
        $translator = $this->app->getTranslator();
        $errors = [];

        if ($data['code'] === '') {
            $errors['code'] = $translator->get('order_status_code_required');
        } elseif (strlen($data['code']) > 50) {
            $errors['code'] = $translator->get('order_status_code_too_long');
        } else {
            $query = "SELECT id FROM order_statuses WHERE code = ?";
            $params = [$data['code']];
            if ($excludeId) {
                $query .= " AND id != ?";
                $params[] = $excludeId;
            }
            $exists = $this->db()->fetch($query, $params);
            if ($exists) {
                $errors['code'] = $translator->get('order_status_code_exists');
            }
        }

        if ($data['name'] === '') {
            $errors['name'] = $translator->get('order_status_name_required');
        } elseif (strlen($data['name']) > 100) {
            $errors['name'] = $translator->get('order_status_name_too_long');
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            $errors['color'] = $translator->get('order_status_color_invalid');
        }

        return $errors;
    }

    /**
     * Ensure order_statuses table exists
     */
    private function ensureTable(): bool
    {
        if ($this->db()->tableExists('order_statuses')) {
            return true;
        }

        $translator = $this->app->getTranslator();
        $this->session->setFlash('error', $translator->get('order_statuses_table_missing'));
        $this->redirect('/admin/diagnostics');
        return false;
    }
}
