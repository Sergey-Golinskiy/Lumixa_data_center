<?php
/**
 * Detail Configurations Controller - Manage detail configurations/variants
 */

namespace App\Controllers\Catalog;

use App\Core\Controller;

class DetailConfigurationsController extends Controller
{
    /**
     * List configurations for a detail
     */
    public function index(string $detailId): void
    {
        $this->requirePermission('catalog.details.view');

        // Get detail
        $detail = $this->db()->fetch(
            "SELECT * FROM details WHERE id = ?",
            [$detailId]
        );

        if (!$detail) {
            $this->notFound();
        }

        // Get configurations
        $configurations = $this->db()->fetchAll(
            "SELECT dc.*, iov.name as material_name
             FROM detail_configurations dc
             LEFT JOIN item_option_values iov ON dc.material_id = iov.id
             WHERE dc.detail_id = ?
             ORDER BY dc.is_active DESC, dc.sku",
            [$detailId]
        );

        $this->render('catalog/detail_configurations/index', [
            'title' => str_replace(':name', $detail['name'], $this->app->getTranslator()->get('configurations_for_detail')),
            'detail' => $detail,
            'configurations' => $configurations
        ]);
    }

    /**
     * Create configuration form
     */
    public function create(string $detailId): void
    {
        $this->requirePermission('catalog.details.edit');

        // Get detail
        $detail = $this->db()->fetch(
            "SELECT * FROM details WHERE id = ?",
            [$detailId]
        );

        if (!$detail) {
            $this->notFound();
        }

        // Get materials
        $materials = $this->db()->fetchAll(
            "SELECT * FROM item_option_values
             WHERE group_key = 'material' AND is_active = 1
             ORDER BY name"
        );

        $this->render('catalog/detail_configurations/form', [
            'title' => $this->app->getTranslator()->get('create_configuration'),
            'detail' => $detail,
            'configuration' => null,
            'materials' => $materials
        ]);
    }

    /**
     * Store new configuration
     */
    public function store(string $detailId): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCSRF();

        // Verify detail exists
        $detail = $this->db()->fetch("SELECT * FROM details WHERE id = ?", [$detailId]);
        if (!$detail) {
            $this->notFound();
        }

        $data = [
            'detail_id' => $detailId,
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'material_id' => !empty($_POST['material_id']) ? (int)$_POST['material_id'] : null,
            'material_color' => trim($_POST['material_color'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validation
        $errors = [];

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            // Check SKU uniqueness
            $exists = $this->db()->fetch(
                "SELECT id FROM detail_configurations WHERE sku = ?",
                [$data['sku']]
            );
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
            $this->redirect("/catalog/details/{$detailId}/configurations/create");
            return;
        }

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $data['image_path'] = $uploadResult['path'];
            } else {
                $this->session->setFlash('error', $uploadResult['error']);
                $this->session->flashInput($_POST);
                $this->redirect("/catalog/details/{$detailId}/configurations/create");
                return;
            }
        }

        // Create configuration
        $id = $this->db()->insert('detail_configurations', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->audit('detail_configuration.created', 'detail_configurations', $id, null, $data);
        $this->session->setFlash('success', 'Configuration created successfully');
        $this->redirect("/catalog/details/{$detailId}/configurations");
    }

    /**
     * Edit configuration form
     */
    public function edit(string $detailId, string $id): void
    {
        $this->requirePermission('catalog.details.edit');

        // Get configuration
        $configuration = $this->db()->fetch(
            "SELECT * FROM detail_configurations WHERE id = ? AND detail_id = ?",
            [$id, $detailId]
        );

        if (!$configuration) {
            $this->notFound();
        }

        // Get detail
        $detail = $this->db()->fetch(
            "SELECT * FROM details WHERE id = ?",
            [$detailId]
        );

        // Get materials
        $materials = $this->db()->fetchAll(
            "SELECT * FROM item_option_values
             WHERE group_key = 'material' AND is_active = 1
             ORDER BY name"
        );

        $this->render('catalog/detail_configurations/form', [
            'title' => $this->app->getTranslator()->get('edit_configuration'),
            'detail' => $detail,
            'configuration' => $configuration,
            'materials' => $materials
        ]);
    }

    /**
     * Update configuration
     */
    public function update(string $detailId, string $id): void
    {
        $this->requirePermission('catalog.details.edit');
        $this->validateCSRF();

        // Get configuration
        $configuration = $this->db()->fetch(
            "SELECT * FROM detail_configurations WHERE id = ? AND detail_id = ?",
            [$id, $detailId]
        );

        if (!$configuration) {
            $this->notFound();
        }

        $data = [
            'sku' => strtoupper(trim($_POST['sku'] ?? '')),
            'name' => trim($_POST['name'] ?? ''),
            'material_id' => !empty($_POST['material_id']) ? (int)$_POST['material_id'] : null,
            'material_color' => trim($_POST['material_color'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Validation
        $errors = [];

        if (empty($data['sku'])) {
            $errors['sku'] = 'SKU is required';
        } else {
            // Check SKU uniqueness
            $exists = $this->db()->fetch(
                "SELECT id FROM detail_configurations WHERE sku = ? AND id != ?",
                [$data['sku'], $id]
            );
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
            $this->redirect("/catalog/details/{$detailId}/configurations/{$id}/edit");
            return;
        }

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                // Delete old image if exists
                if (!empty($configuration['image_path'])) {
                    $oldPath = $this->app->basePath() . '/public/' . ltrim($configuration['image_path'], '/');
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $data['image_path'] = $uploadResult['path'];
            } else {
                $this->session->setFlash('error', $uploadResult['error']);
                $this->session->flashInput($_POST);
                $this->redirect("/catalog/details/{$detailId}/configurations/{$id}/edit");
                return;
            }
        }

        // Update
        $this->db()->update('detail_configurations', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]), ['id' => $id]);

        $this->audit('detail_configuration.updated', 'detail_configurations', $id, $configuration, $data);
        $this->session->setFlash('success', 'Configuration updated successfully');
        $this->redirect("/catalog/details/{$detailId}/configurations");
    }

    /**
     * Delete configuration
     */
    public function delete(string $detailId, string $id): void
    {
        $this->requirePermission('catalog.details.delete');
        $this->validateCSRF();

        // Get configuration
        $configuration = $this->db()->fetch(
            "SELECT * FROM detail_configurations WHERE id = ? AND detail_id = ?",
            [$id, $detailId]
        );

        if (!$configuration) {
            $this->notFound();
        }

        // Check if used in BOMs
        $bomCount = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM bom_lines WHERE detail_configuration_id = ?",
            [$id]
        );

        if ($bomCount > 0) {
            $this->session->setFlash('error', "Cannot delete configuration: it is used in {$bomCount} BOM(s)");
            $this->redirect("/catalog/details/{$detailId}/configurations");
            return;
        }

        // Delete image if exists
        if (!empty($configuration['image_path'])) {
            $imagePath = $this->app->basePath() . '/public/' . ltrim($configuration['image_path'], '/');
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Delete
        $this->db()->delete('detail_configurations', ['id' => $id]);
        $this->audit('detail_configuration.deleted', 'detail_configurations', $id, $configuration, null);
        $this->session->setFlash('success', 'Configuration deleted successfully');
        $this->redirect("/catalog/details/{$detailId}/configurations");
    }

    /**
     * API endpoint to fetch configurations for a detail (used by BOM form)
     */
    public function getConfigurationsForDetail(string $detailId): void
    {
        header('Content-Type: application/json');

        $configurations = $this->db()->fetchAll(
            "SELECT dc.id, dc.sku, dc.name, dc.material_color, dc.is_active,
                    iov.name as material_name
             FROM detail_configurations dc
             LEFT JOIN item_option_values iov ON dc.material_id = iov.id
             WHERE dc.detail_id = ? AND dc.is_active = 1
             ORDER BY dc.sku",
            [$detailId]
        );

        echo json_encode([
            'success' => true,
            'configurations' => $configurations
        ]);
        exit;
    }

    /**
     * Handle image upload
     */
    private function handleImageUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid image type. Only JPG, PNG, GIF, WEBP allowed.'];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Image size exceeds 5MB limit.'];
        }

        $uploadDir = $this->app->basePath() . '/public/uploads/detail_configurations';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('config_') . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Failed to upload image.'];
        }

        return ['success' => true, 'path' => '/uploads/detail_configurations/' . $filename];
    }
}
