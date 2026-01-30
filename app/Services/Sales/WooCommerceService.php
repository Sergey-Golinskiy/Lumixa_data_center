<?php
/**
 * WooCommerceService - Sync orders from WooCommerce via REST API
 */

namespace App\Services\Sales;

use App\Core\Application;
use App\Core\Database;

class WooCommerceService
{
    private Application $app;
    private Database $db;
    private array $settings = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app->getDatabase();
        $this->loadSettings();
    }

    /**
     * Load WooCommerce settings from database
     */
    private function loadSettings(): void
    {
        if (!$this->db->tableExists('integration_settings')) {
            return;
        }

        $rows = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM integration_settings WHERE integration_type = 'woocommerce'"
        );

        foreach ($rows as $row) {
            $this->settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    /**
     * Test connection to WooCommerce API
     */
    public function testConnection(): array
    {
        $storeUrl = $this->settings['store_url'] ?? '';
        $consumerKey = $this->settings['consumer_key'] ?? '';
        $consumerSecret = $this->settings['consumer_secret'] ?? '';

        if (!$storeUrl || !$consumerKey || !$consumerSecret) {
            return ['success' => false, 'error' => 'Missing API credentials'];
        }

        try {
            $response = $this->makeRequest('GET', '/wp-json/wc/v3/system_status');

            if ($response['success']) {
                return [
                    'success' => true,
                    'store_info' => [
                        'version' => $response['data']['environment']['version'] ?? 'unknown',
                        'wc_version' => $response['data']['environment']['wc_version'] ?? 'unknown',
                    ]
                ];
            }

            return ['success' => false, 'error' => $response['error'] ?? 'Connection failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync orders from WooCommerce
     */
    public function syncOrders(?int $triggeredBy = null): array
    {
        $storeUrl = $this->settings['store_url'] ?? '';
        $consumerKey = $this->settings['consumer_key'] ?? '';
        $consumerSecret = $this->settings['consumer_secret'] ?? '';

        if (!$storeUrl || !$consumerKey || !$consumerSecret) {
            return ['success' => false, 'error' => 'Missing API credentials'];
        }

        // Start sync log
        $logId = $this->startSyncLog($triggeredBy);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $page = 1;
        $perPage = 50;

        try {
            // Get statuses to sync
            $statuses = explode(',', $this->settings['sync_order_statuses'] ?? 'processing,completed');
            $lastSyncOrderId = (int)($this->settings['last_sync_order_id'] ?? 0);

            // Fetch orders page by page
            do {
                $params = [
                    'per_page' => $perPage,
                    'page' => $page,
                    'orderby' => 'id',
                    'order' => 'asc',
                ];

                // Only fetch orders after last synced ID for incremental sync
                if ($lastSyncOrderId > 0) {
                    $params['after'] = date('Y-m-d\TH:i:s', strtotime('-30 days')); // Also limit by date for safety
                }

                $response = $this->makeRequest('GET', '/wp-json/wc/v3/orders', $params);

                if (!$response['success']) {
                    throw new \Exception($response['error'] ?? 'Failed to fetch orders');
                }

                $orders = $response['data'] ?? [];

                foreach ($orders as $wcOrder) {
                    // Skip if not in desired statuses
                    if (!in_array($wcOrder['status'], $statuses)) {
                        continue;
                    }

                    try {
                        $result = $this->importOrder($wcOrder);
                        if ($result['created']) {
                            $created++;
                        } else {
                            $updated++;
                        }

                        // Track highest order ID for next incremental sync
                        if ((int)$wcOrder['id'] > $lastSyncOrderId) {
                            $lastSyncOrderId = (int)$wcOrder['id'];
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $this->app->getLogger()->error('WooCommerce order import failed', [
                            'wc_order_id' => $wcOrder['id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $page++;
            } while (count($orders) === $perPage && $page <= 100); // Safety limit

            // Update last sync order ID
            $this->saveSetting('last_sync_order_id', (string)$lastSyncOrderId);

            // Complete sync log
            $this->completeSyncLog($logId, $created + $updated, $created, $updated, $failed);

            return [
                'success' => true,
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed
            ];

        } catch (\Exception $e) {
            $this->failSyncLog($logId, $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Import single order from WooCommerce data
     */
    private function importOrder(array $wcOrder): array
    {
        $externalId = (string)$wcOrder['id'];

        // Check if order already exists
        $existing = $this->db->fetch(
            "SELECT id, status FROM sales_orders WHERE source = 'woocommerce' AND external_id = ?",
            [$externalId]
        );

        $orderData = [
            'external_id' => $externalId,
            'source' => 'woocommerce',
            'source_url' => rtrim($this->settings['store_url'], '/') . '/wp-admin/post.php?post=' . $externalId . '&action=edit',

            // Customer info
            'customer_name' => trim(($wcOrder['billing']['first_name'] ?? '') . ' ' . ($wcOrder['billing']['last_name'] ?? '')),
            'customer_email' => $wcOrder['billing']['email'] ?? '',
            'customer_phone' => $wcOrder['billing']['phone'] ?? '',

            // Shipping address
            'shipping_address' => $this->formatAddress($wcOrder['shipping'] ?? []),
            'shipping_city' => $wcOrder['shipping']['city'] ?? '',
            'shipping_country' => $wcOrder['shipping']['country'] ?? '',
            'shipping_postal_code' => $wcOrder['shipping']['postcode'] ?? '',

            // Billing address
            'billing_address' => $this->formatAddress($wcOrder['billing'] ?? []),
            'billing_city' => $wcOrder['billing']['city'] ?? '',
            'billing_country' => $wcOrder['billing']['country'] ?? '',
            'billing_postal_code' => $wcOrder['billing']['postcode'] ?? '',

            // Totals
            'subtotal' => (float)($wcOrder['total'] ?? 0) - (float)($wcOrder['shipping_total'] ?? 0) + (float)($wcOrder['discount_total'] ?? 0),
            'shipping_cost' => (float)($wcOrder['shipping_total'] ?? 0),
            'discount' => (float)($wcOrder['discount_total'] ?? 0),
            'tax' => (float)($wcOrder['total_tax'] ?? 0),
            'total' => (float)($wcOrder['total'] ?? 0),
            'currency' => $wcOrder['currency'] ?? 'UAH',

            // Status mapping
            'status' => $this->mapWcStatus($wcOrder['status'] ?? 'pending'),
            'payment_status' => $this->mapPaymentStatus($wcOrder),
            'payment_method' => $wcOrder['payment_method_title'] ?? '',

            // Shipping method
            'shipping_method' => $this->extractShippingMethod($wcOrder),

            // Dates
            'ordered_at' => $this->formatDate($wcOrder['date_created'] ?? null),
            'paid_at' => $this->formatDate($wcOrder['date_paid'] ?? null),

            // Notes
            'notes' => $wcOrder['customer_note'] ?? '',

            // Metadata
            'meta_data' => json_encode([
                'wc_order_key' => $wcOrder['order_key'] ?? '',
                'wc_status' => $wcOrder['status'] ?? '',
                'wc_payment_method' => $wcOrder['payment_method'] ?? '',
            ]),

            'synced_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            // Update existing order
            $orderId = $existing['id'];
            $this->db->update('sales_orders', $orderData, ['id' => $orderId]);

            // Delete existing items and re-import
            $this->db->delete('sales_order_items', ['order_id' => $orderId]);
        } else {
            // Create new order
            $orderData['order_number'] = $this->generateOrderNumber();
            $orderData['created_at'] = date('Y-m-d H:i:s');

            $orderId = $this->db->insert('sales_orders', $orderData);
        }

        // Import order items
        foreach ($wcOrder['line_items'] ?? [] as $item) {
            $this->db->insert('sales_order_items', [
                'order_id' => $orderId,
                'external_product_id' => (string)($item['product_id'] ?? ''),
                'sku' => $item['sku'] ?? '',
                'name' => $item['name'] ?? 'Unknown Item',
                'variant_info' => $this->extractVariantInfo($item),
                'quantity' => (int)($item['quantity'] ?? 1),
                'unit_price' => (float)($item['price'] ?? 0),
                'subtotal' => (float)($item['subtotal'] ?? 0),
                'tax' => (float)($item['total_tax'] ?? 0),
                'total' => (float)($item['total'] ?? 0),
                'meta_data' => json_encode($item['meta_data'] ?? []),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return ['created' => !$existing, 'order_id' => $orderId];
    }

    /**
     * Make API request to WooCommerce
     */
    private function makeRequest(string $method, string $endpoint, array $params = []): array
    {
        $storeUrl = rtrim($this->settings['store_url'] ?? '', '/');
        $consumerKey = $this->settings['consumer_key'] ?? '';
        $consumerSecret = $this->settings['consumer_secret'] ?? '';

        $url = $storeUrl . $endpoint;

        if (!empty($params) && $method === 'GET') {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_USERPWD => $consumerKey . ':' . $consumerSecret,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        if ($httpCode >= 400) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? "HTTP {$httpCode} error";
            return ['success' => false, 'error' => $errorMessage];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response'];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Map WooCommerce status to internal status
     */
    private function mapWcStatus(string $wcStatus): string
    {
        $map = [
            'pending' => 'pending',
            'processing' => 'processing',
            'on-hold' => 'on_hold',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'failed' => 'cancelled',
        ];

        return $map[$wcStatus] ?? 'pending';
    }

    /**
     * Determine payment status from WooCommerce order
     */
    private function mapPaymentStatus(array $wcOrder): string
    {
        if (!empty($wcOrder['date_paid'])) {
            return 'paid';
        }

        if ($wcOrder['status'] === 'refunded') {
            return 'refunded';
        }

        if ($wcOrder['status'] === 'failed') {
            return 'failed';
        }

        return 'pending';
    }

    /**
     * Format address from WooCommerce address data
     */
    private function formatAddress(array $addr): string
    {
        $parts = array_filter([
            $addr['address_1'] ?? '',
            $addr['address_2'] ?? '',
        ]);

        return implode(', ', $parts);
    }

    /**
     * Extract shipping method from WooCommerce order
     */
    private function extractShippingMethod(array $wcOrder): string
    {
        $shippingLines = $wcOrder['shipping_lines'] ?? [];
        if (!empty($shippingLines)) {
            return $shippingLines[0]['method_title'] ?? '';
        }
        return '';
    }

    /**
     * Extract variant info from line item
     */
    private function extractVariantInfo(array $item): string
    {
        $meta = $item['meta_data'] ?? [];
        $parts = [];

        foreach ($meta as $m) {
            if (!empty($m['display_key']) && !empty($m['display_value'])) {
                $parts[] = $m['display_key'] . ': ' . $m['display_value'];
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Format date from WooCommerce format
     */
    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        try {
            $dt = new \DateTime($date);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        $seq = $this->db->fetch("SELECT * FROM document_sequences WHERE type = 'sales_order' FOR UPDATE");

        if (!$seq) {
            $this->db->insert('document_sequences', ['type' => 'sales_order', 'prefix' => 'SO', 'next_number' => 1]);
            return 'SO-' . date('Ymd') . '-0001';
        }

        $number = ($seq['prefix'] ?? 'SO') . '-' . date('Ymd') . '-' . str_pad($seq['next_number'] ?? 1, 4, '0', STR_PAD_LEFT);
        $this->db->execute("UPDATE document_sequences SET next_number = next_number + 1 WHERE type = 'sales_order'");

        return $number;
    }

    /**
     * Save integration setting
     */
    private function saveSetting(string $key, string $value): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM integration_settings WHERE integration_type = 'woocommerce' AND setting_key = ?",
            [$key]
        );

        if ($existing) {
            $this->db->update('integration_settings', [
                'setting_value' => $value,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $existing['id']]);
        } else {
            $this->db->insert('integration_settings', [
                'integration_type' => 'woocommerce',
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->settings[$key] = $value;
    }

    /**
     * Start sync log entry
     */
    private function startSyncLog(?int $triggeredBy): int
    {
        return $this->db->insert('integration_sync_log', [
            'integration_type' => 'woocommerce',
            'sync_type' => 'incremental',
            'status' => 'started',
            'started_at' => date('Y-m-d H:i:s'),
            'triggered_by' => $triggeredBy
        ]);
    }

    /**
     * Complete sync log entry
     */
    private function completeSyncLog(int $logId, int $processed, int $created, int $updated, int $failed): void
    {
        $this->db->update('integration_sync_log', [
            'status' => 'completed',
            'records_processed' => $processed,
            'records_created' => $created,
            'records_updated' => $updated,
            'records_failed' => $failed,
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $logId]);
    }

    /**
     * Fail sync log entry
     */
    private function failSyncLog(int $logId, string $error): void
    {
        $this->db->update('integration_sync_log', [
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $logId]);
    }
}
