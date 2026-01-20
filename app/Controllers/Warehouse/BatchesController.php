<?php
/**
 * Batches Controller - Inventory Batch Management
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;
use App\Core\Application;
use App\Services\Warehouse\BatchService;
use App\Services\Warehouse\ItemService;

class BatchesController extends Controller
{
    private BatchService $batchService;
    private ItemService $itemService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->batchService = new BatchService($app);
        $this->itemService = new ItemService($app);
    }

    /**
     * List batches for an item
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        $itemId = (int)$this->get('item_id', 0);
        $status = $this->get('status', 'active');

        if (!$itemId) {
            $this->session->setFlash('error', 'Item ID is required');
            $this->redirect('/warehouse/items');
            return;
        }

        $item = $this->itemService->findById($itemId);
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Item Not Found']);
            return;
        }

        $batches = $this->batchService->getAvailableBatches($itemId, $status);
        $history = $this->batchService->getBatchHistory($itemId, 100);
        $availableQty = $this->batchService->getAvailableQuantity($itemId);

        $this->view('warehouse/batches/index', [
            'title' => 'Batches - ' . $item['name'],
            'item' => $item,
            'batches' => $batches,
            'history' => $history,
            'availableQty' => $availableQty,
            'status' => $status
        ]);
    }

    /**
     * Show create batch form
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.create');

        $itemId = (int)$this->get('item_id', 0);

        if (!$itemId) {
            $this->session->setFlash('error', 'Item ID is required');
            $this->redirect('/warehouse/items');
            return;
        }

        $item = $this->itemService->findById($itemId);
        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Item Not Found']);
            return;
        }

        // Get suppliers
        $suppliers = $this->db()->fetchAll(
            "SELECT id, code, name FROM partners WHERE type IN ('supplier', 'both') AND is_active = 1 ORDER BY name"
        );

        $this->view('warehouse/batches/form', [
            'title' => 'Create Batch - ' . $item['name'],
            'item' => $item,
            'batch' => null,
            'suppliers' => $suppliers,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Store new batch
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.create');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/batches/create');
            return;
        }

        $itemId = (int)$this->post('item_id');
        $item = $this->itemService->findById($itemId);

        if (!$item) {
            $this->session->setFlash('error', 'Item not found');
            $this->redirect('/warehouse/items');
            return;
        }

        $data = [
            'item_id' => $itemId,
            'batch_code' => $this->post('batch_code', ''),
            'quantity' => (float)$this->post('quantity'),
            'unit_cost' => (float)$this->post('unit_cost'),
            'received_date' => $this->post('received_date', date('Y-m-d')),
            'supplier_id' => $this->post('supplier_id') ? (int)$this->post('supplier_id') : null,
            'expiry_date' => $this->post('expiry_date') ?: null,
            'notes' => $this->post('notes', ''),
            'source_type' => 'manual',
            'status' => 'active'
        ];

        try {
            $batch = $this->batchService->createBatch($data);
            $this->session->setFlash('success', 'Batch created successfully');
            $this->redirect('/warehouse/batches?item_id=' . $itemId);
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/batches/create?item_id=' . $itemId);
        }
    }

    /**
     * Show batch details
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        $batch = $this->batchService->getBatchById((int)$id);

        if (!$batch) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Batch Not Found']);
            return;
        }

        // Get batch movements
        $movements = $this->db()->fetchAll(
            "SELECT bm.*, d.document_number, d.type as document_type, d.document_date
             FROM inventory_batch_movements bm
             LEFT JOIN documents d ON bm.document_id = d.id
             WHERE bm.batch_id = ?
             ORDER BY bm.created_at DESC",
            [(int)$id]
        );

        // Get allocations
        $allocations = $this->db()->fetchAll(
            "SELECT ia.*, d.document_number, d.document_date, d.type as document_type,
                    u.name as allocated_by_name
             FROM inventory_issue_allocations ia
             JOIN documents d ON ia.document_id = d.id
             LEFT JOIN users u ON ia.allocated_by = u.id
             WHERE ia.batch_id = ?
             ORDER BY ia.created_at DESC",
            [(int)$id]
        );

        $this->view('warehouse/batches/show', [
            'title' => 'Batch ' . $batch['batch_code'],
            'batch' => $batch,
            'movements' => $movements,
            'allocations' => $allocations
        ]);
    }

    /**
     * AJAX: Get available batches for allocation
     */
    public function getAvailableForAllocation(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        $itemId = (int)$this->get('item_id', 0);
        $quantity = (float)$this->get('quantity', 0);

        if (!$itemId || $quantity <= 0) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid parameters'
            ]);
            return;
        }

        try {
            $batches = $this->batchService->getAvailableBatches($itemId, BatchService::STATUS_ACTIVE);
            $totalAvailable = $this->batchService->getAvailableQuantity($itemId);

            $this->jsonResponse([
                'success' => true,
                'batches' => $batches,
                'totalAvailable' => $totalAvailable,
                'sufficientStock' => $totalAvailable >= $quantity
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Calculate allocation preview
     */
    public function previewAllocation(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        if (!$this->validateCsrf()) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid security token'
            ]);
            return;
        }

        $itemId = (int)$this->post('item_id');
        $quantity = (float)$this->post('quantity');
        $method = $this->post('method', BatchService::METHOD_FIFO);
        $manualAllocation = json_decode($this->post('manual_allocation', '[]'), true);

        if (!$itemId || $quantity <= 0) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Invalid parameters'
            ]);
            return;
        }

        try {
            $allocations = $this->batchService->allocateBatches(
                $itemId,
                $quantity,
                $method,
                null,
                null,
                $method === BatchService::METHOD_MANUAL ? $manualAllocation : null
            );

            $totalCost = array_sum(array_column($allocations, 'total_cost'));
            $avgCost = $quantity > 0 ? $totalCost / $quantity : 0;

            $this->jsonResponse([
                'success' => true,
                'allocations' => $allocations,
                'totalCost' => round($totalCost, 4),
                'avgCost' => round($avgCost, 4),
                'quantity' => $quantity,
                'method' => $method
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update batch status
     */
    public function updateStatus(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirectBack();
            return;
        }

        $batch = $this->batchService->getBatchById((int)$id);

        if (!$batch) {
            $this->session->setFlash('error', 'Batch not found');
            $this->redirectBack();
            return;
        }

        $newStatus = $this->post('status');

        if (!in_array($newStatus, [BatchService::STATUS_ACTIVE, BatchService::STATUS_QUARANTINE, BatchService::STATUS_EXPIRED])) {
            $this->session->setFlash('error', 'Invalid status');
            $this->redirectBack();
            return;
        }

        try {
            $this->db()->update('inventory_batches', [
                'status' => $newStatus
            ], ['id' => (int)$id]);

            $this->session->setFlash('success', 'Batch status updated');
            $this->redirectBack();
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->redirectBack();
        }
    }
}
