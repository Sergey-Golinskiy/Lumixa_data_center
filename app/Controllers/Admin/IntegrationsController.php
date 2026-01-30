<?php
/**
 * IntegrationsController - Manage external integrations (WooCommerce, etc.)
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class IntegrationsController extends Controller
{
    /**
     * Show integrations settings
     */
    public function index(): void
    {
        $this->requirePermission('admin.access');

        $woocommerceSettings = $this->getIntegrationSettings('woocommerce');
        $syncLogs = $this->getRecentSyncLogs('woocommerce', 10);
        $woocommerceStatuses = $this->getExternalOrderStatuses('woocommerce');
        $internalStatuses = ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];

        $this->view('admin/integrations/index', [
            'title' => $this->app->getTranslator()->get('integrations'),
            'woocommerceSettings' => $woocommerceSettings,
            'syncLogs' => $syncLogs,
            'woocommerceStatuses' => $woocommerceStatuses,
            'internalStatuses' => $internalStatuses
        ]);
    }

    /**
     * Update WooCommerce settings
     */
    public function updateWooCommerce(): void
    {
        $this->requirePermission('admin.access');

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/integrations');
            return;
        }

        $settings = [
            'store_url' => trim($this->post('woo_store_url', '')),
            'consumer_key' => trim($this->post('woo_consumer_key', '')),
            'consumer_secret' => trim($this->post('woo_consumer_secret', '')),
            'sync_interval_minutes' => max(5, (int)$this->post('woo_sync_interval', 30)),
            'auto_sync_enabled' => $this->post('woo_auto_sync') ? '1' : '0',
            'sync_order_statuses' => implode(',', $this->post('woo_sync_statuses', ['processing', 'completed'])),
            'last_sync_order_id' => $this->post('woo_last_sync_order_id', '0')
        ];

        // Validate required fields if auto-sync is enabled
        if ($settings['auto_sync_enabled'] === '1') {
            $errors = [];
            if (empty($settings['store_url'])) {
                $errors['woo_store_url'] = $translator->get('store_url_required');
            }
            if (empty($settings['consumer_key'])) {
                $errors['woo_consumer_key'] = $translator->get('consumer_key_required');
            }
            if (empty($settings['consumer_secret'])) {
                $errors['woo_consumer_secret'] = $translator->get('consumer_secret_required');
            }

            if ($errors) {
                $this->session->setErrors($errors);
                $this->session->flashInput($_POST);
                $this->redirect('/admin/integrations');
                return;
            }
        }

        // Save settings
        foreach ($settings as $key => $value) {
            $this->saveIntegrationSetting('woocommerce', $key, $value, in_array($key, ['consumer_key', 'consumer_secret']));
        }

        $this->audit('integration.woocommerce.updated', 'integration_settings', null, null, ['keys' => array_keys($settings)]);
        $this->session->setFlash('success', $translator->get('settings_saved_success'));
        $this->redirect('/admin/integrations');
    }

    /**
     * Test WooCommerce connection
     */
    public function testWooCommerce(): void
    {
        $this->requirePermission('admin.access');

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('invalid_security_token')]);
            return;
        }

        $settings = $this->getIntegrationSettings('woocommerce');

        if (empty($settings['store_url']) || empty($settings['consumer_key']) || empty($settings['consumer_secret'])) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('woocommerce_settings_incomplete')]);
            return;
        }

        try {
            $service = new \App\Services\Sales\WooCommerceService($this->app);
            $result = $service->testConnection();

            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $translator->get('woocommerce_connection_success'),
                    'store_info' => $result['store_info'] ?? null
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['error'] ?? $translator->get('woocommerce_connection_failed')
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Trigger manual WooCommerce sync
     */
    public function syncWooCommerce(): void
    {
        $this->requirePermission('admin.access');

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect('/admin/integrations');
            return;
        }

        try {
            $service = new \App\Services\Sales\WooCommerceService($this->app);
            $result = $service->syncOrders($this->user()['id']);

            if ($result['success']) {
                $this->session->setFlash('success', $translator->get('woocommerce_sync_success', [
                    'created' => $result['created'],
                    'updated' => $result['updated']
                ]));
            } else {
                $this->session->setFlash('error', $result['error'] ?? $translator->get('woocommerce_sync_failed'));
            }
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/integrations');
    }

    /**
     * Get integration settings
     */
    private function getIntegrationSettings(string $type): array
    {
        if (!$this->db()->tableExists('integration_settings')) {
            return [];
        }

        $rows = $this->db()->fetchAll(
            "SELECT setting_key, setting_value, is_encrypted FROM integration_settings WHERE integration_type = ?",
            [$type]
        );

        $settings = [];
        foreach ($rows as $row) {
            $value = $row['setting_value'];
            // For encrypted values, we just show masked version in UI
            if ($row['is_encrypted'] && $value) {
                $value = str_repeat('*', min(strlen($value), 20)) . substr($value, -4);
            }
            $settings[$row['setting_key']] = $value;
        }

        return $settings;
    }

    /**
     * Save integration setting
     */
    private function saveIntegrationSetting(string $type, string $key, string $value, bool $encrypt = false): void
    {
        if (!$this->db()->tableExists('integration_settings')) {
            return;
        }

        // For encrypted fields, only update if a new value is provided (not masked)
        if ($encrypt && strpos($value, '*') === 0) {
            return; // Skip updating masked values
        }

        $existing = $this->db()->fetch(
            "SELECT id FROM integration_settings WHERE integration_type = ? AND setting_key = ?",
            [$type, $key]
        );

        $data = [
            'setting_value' => $value,
            'is_encrypted' => $encrypt ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db()->update('integration_settings', $data, ['id' => $existing['id']]);
        } else {
            $this->db()->insert('integration_settings', array_merge($data, [
                'integration_type' => $type,
                'setting_key' => $key,
                'created_at' => date('Y-m-d H:i:s')
            ]));
        }
    }

    /**
     * Get recent sync logs
     */
    private function getRecentSyncLogs(string $type, int $limit = 10): array
    {
        if (!$this->db()->tableExists('integration_sync_log')) {
            return [];
        }

        return $this->db()->fetchAll(
            "SELECT isl.*, u.name as triggered_by_name
             FROM integration_sync_log isl
             LEFT JOIN users u ON isl.triggered_by = u.id
             WHERE isl.integration_type = ?
             ORDER BY isl.started_at DESC
             LIMIT ?",
            [$type, $limit]
        );
    }

    /**
     * Get external order statuses for an integration
     */
    private function getExternalOrderStatuses(string $type): array
    {
        if (!$this->db()->tableExists('external_order_statuses')) {
            return [];
        }

        return $this->db()->fetchAll(
            "SELECT * FROM external_order_statuses WHERE integration_type = ? ORDER BY external_name",
            [$type]
        );
    }

    /**
     * Update WooCommerce status mapping
     */
    public function updateWooCommerceStatus(): void
    {
        $this->requirePermission('admin.access');

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('invalid_security_token')]);
            return;
        }

        $statusId = (int)$this->post('status_id');
        $internalStatus = $this->post('internal_status');
        $isActive = $this->post('is_active') ? 1 : 0;

        if (!$statusId) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('invalid_status_id')]);
            return;
        }

        // Validate internal status
        $validStatuses = ['pending', 'processing', 'on_hold', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded'];
        if ($internalStatus && !in_array($internalStatus, $validStatuses)) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('invalid_internal_status')]);
            return;
        }

        try {
            $this->db()->update('external_order_statuses', [
                'internal_status' => $internalStatus ?: null,
                'is_active' => $isActive,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $statusId, 'integration_type' => 'woocommerce']);

            $this->audit('integration.woocommerce.status_updated', 'external_order_statuses', $statusId, null, [
                'internal_status' => $internalStatus,
                'is_active' => $isActive
            ]);

            $this->jsonResponse(['success' => true, 'message' => $translator->get('status_mapping_updated')]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Sync WooCommerce statuses manually
     */
    public function syncWooCommerceStatuses(): void
    {
        $this->requirePermission('admin.access');

        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->jsonResponse(['success' => false, 'message' => $translator->get('invalid_security_token')]);
            return;
        }

        try {
            $service = new \App\Services\Sales\WooCommerceService($this->app);
            $result = $service->syncOrderStatuses();

            if (isset($result['error'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['error']
                ]);
            } else {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $translator->get('statuses_synced_success', [
                        'synced' => $result['synced'],
                        'added' => $result['added']
                    ]),
                    'synced' => $result['synced'],
                    'added' => $result['added']
                ]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
