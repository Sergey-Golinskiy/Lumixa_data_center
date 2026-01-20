<?php
/**
 * Documents Controller - Warehouse documents
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;
use App\Core\Application;
use App\Services\Warehouse\DocumentService;
use App\Services\Warehouse\ItemService;

class DocumentsController extends Controller
{
    private DocumentService $documentService;
    private ItemService $itemService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->documentService = new DocumentService($app);
        $this->itemService = new ItemService($app);
    }

    /**
     * List documents
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.view');

        $page = max(1, (int)$this->get('page', 1));
        $perPage = $this->app->config('items_per_page', 25);

        $result = $this->documentService->paginate($page, $perPage, [
            'type' => $this->get('type', ''),
            'status' => $this->get('status', ''),
            'search' => $this->get('search', '')
        ]);

        $types = $this->getDocumentTypes();

        $this->view('warehouse/documents/index', [
            'title' => 'Documents',
            'documents' => $result['documents'],
            'pagination' => $result['pagination'],
            'types' => $types,
            'filters' => [
                'type' => $this->get('type', ''),
                'status' => $this->get('status', ''),
                'search' => $this->get('search', '')
            ]
        ]);
    }

    /**
     * Show create form
     */
    public function create(?string $type = null): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.create');

        $types = $this->getDocumentTypes();
        $selectedType = $type ?? $this->get('type', '');
        if ($selectedType && !array_key_exists($selectedType, $types)) {
            $selectedType = '';
        }

        // Get items for dropdown
        $items = $this->db()->fetchAll(
            "SELECT id, sku, name, unit FROM items WHERE is_active = 1 ORDER BY sku"
        );

        // Get partners for dropdown
        $partners = $this->db()->fetchAll(
            "SELECT id, name, type FROM partners WHERE is_active = 1 ORDER BY name"
        );

        $this->view('warehouse/documents/form', [
            'title' => 'Create Document',
            'document' => null,
            'lines' => [],
            'items' => $items,
            'partners' => $partners,
            'csrfToken' => $this->csrfToken(),
            'types' => $types,
            'selectedType' => $selectedType,
            'costingMethods' => $this->getCostingMethods(),
            'defaultCostingMethod' => $this->getSetting('inventory_issue_method', 'FIFO'),
            'allowCostingOverride' => $this->getSetting('inventory_allow_issue_method_override', '1') === '1'
        ]);
    }

    /**
     * Store document
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.create');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/documents/create');
            return;
        }

        $type = $this->post('type', 'receipt');
        $types = $this->getDocumentTypes();

        if (!isset($types[$type])) {
            $this->session->setFlash('error', 'Invalid document type');
            $this->redirect('/warehouse/documents/create');
            return;
        }

        $data = [
            'type' => $type,
            'document_date' => $this->post('document_date', date('Y-m-d')),
            'partner_id' => $this->post('partner_id') ?: null,
            'notes' => $this->post('notes', ''),
            'costing_method' => $this->post('costing_method') ?: null,
            'issue_source_type' => $this->resolveIssueSourceType($type),
            'issue_source_id' => null
        ];

        // Parse lines
        $lines = $this->parseLines();

        if (empty($lines)) {
            $this->session->setFlash('error', 'Please add at least one line');
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/documents/create');
            return;
        }

        try {
            $doc = $this->documentService->create($data, $lines, $this->user()['id']);
            $this->session->setFlash('success', 'Document created successfully');
            $this->redirect('/warehouse/documents/' . $doc['id']);
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/documents/create');
        }
    }

    /**
     * Show document
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.view');

        $document = $this->documentService->findById((int)$id);

        if (!$document) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Document Not Found']);
            return;
        }

        $lines = $this->documentService->getLines((int)$id);
        $batchAllocations = $this->documentService->getBatchAllocations((int)$id);

        $this->view('warehouse/documents/show', [
            'title' => $document['document_number'],
            'document' => $document,
            'lines' => $lines,
            'batchAllocations' => $batchAllocations,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Edit document
     */
    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.edit');

        $document = $this->documentService->findById((int)$id);

        if (!$document) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Document Not Found']);
            return;
        }

        if ($document['status'] !== 'draft') {
            $this->session->setFlash('error', 'Only draft documents can be edited');
            $this->redirect('/warehouse/documents/' . $id);
            return;
        }

        $lines = $this->documentService->getLines((int)$id);

        // Get items for dropdown
        $items = $this->db()->fetchAll(
            "SELECT id, sku, name, unit FROM items WHERE is_active = 1 ORDER BY sku"
        );

        // Get partners for dropdown
        $partners = $this->db()->fetchAll(
            "SELECT id, name, type FROM partners WHERE is_active = 1 ORDER BY name"
        );

        $types = $this->getDocumentTypes();

        $this->view('warehouse/documents/form', [
            'title' => 'Edit ' . $document['document_number'],
            'document' => $document,
            'lines' => $lines,
            'items' => $items,
            'partners' => $partners,
            'csrfToken' => $this->csrfToken(),
            'types' => $types,
            'selectedType' => $document['type'] ?? '',
            'costingMethods' => $this->getCostingMethods(),
            'defaultCostingMethod' => $this->getSetting('inventory_issue_method', 'FIFO'),
            'allowCostingOverride' => $this->getSetting('inventory_allow_issue_method_override', '1') === '1'
        ]);
    }

    /**
     * Update document
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/documents/' . $id . '/edit');
            return;
        }

        $document = $this->documentService->findById((int)$id);

        if (!$document) {
            $this->session->setFlash('error', 'Document not found');
            $this->redirect('/warehouse/documents');
            return;
        }

        $data = [
            'document_date' => $this->post('document_date', date('Y-m-d')),
            'partner_id' => $this->post('partner_id') ?: null,
            'notes' => $this->post('notes', ''),
            'costing_method' => $this->post('costing_method') ?: null,
            'issue_source_type' => $this->resolveIssueSourceType($document['type'] ?? ''),
            'issue_source_id' => $document['issue_source_id'] ?? null
        ];

        $lines = $this->parseLines();

        if (empty($lines)) {
            $this->session->setFlash('error', 'Please add at least one line');
            $this->redirect('/warehouse/documents/' . $id . '/edit');
            return;
        }

        try {
            $this->documentService->update((int)$id, $data, $lines, $this->user()['id']);
            $this->session->setFlash('success', 'Document updated');
            $this->redirect('/warehouse/documents/' . $id);
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->redirect('/warehouse/documents/' . $id . '/edit');
        }
    }

    /**
     * Post document (execute/apply to inventory)
     */
    public function postDocument(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.post');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/documents/' . $id);
            return;
        }

        try {
            $this->documentService->post((int)$id, $this->user()['id']);
            $this->session->setFlash('success', 'Document posted successfully');
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/warehouse/documents/' . $id);
    }

    /**
     * Cancel document
     */
    public function cancel(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.cancel');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/documents/' . $id);
            return;
        }

        $reason = $this->post('cancel_reason', '');

        if (empty($reason)) {
            $this->session->setFlash('error', 'Please provide a cancellation reason');
            $this->redirect('/warehouse/documents/' . $id);
            return;
        }

        try {
            $this->documentService->cancel((int)$id, $reason, $this->user()['id']);
            $this->session->setFlash('success', 'Document cancelled');
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/warehouse/documents/' . $id);
    }

    /**
     * Parse document lines from POST data
     */
    private function parseLines(): array
    {
        $result = [];
        $linesData = $_POST['lines'] ?? [];

        foreach ($linesData as $line) {
            if (empty($line['item_id']) || empty($line['quantity'])) {
                continue;
            }

            $batchAllocations = $this->parseBatchAllocations($line['batch_allocations'] ?? '');

            $result[] = [
                'item_id' => (int)$line['item_id'],
                'quantity' => (float)$line['quantity'],
                'unit_price' => (float)($line['unit_price'] ?? 0),
                'notes' => $line['notes'] ?? '',
                'batch_allocations' => $batchAllocations
            ];
        }

        return $result;
    }

    private function getDocumentTypes(): array
    {
        $translator = $this->app->getTranslator();

        return [
            'receipt' => $translator->get('receipt'),
            'issue' => $translator->get('issue'),
            'transfer' => $translator->get('transfer'),
            'stocktake' => $translator->get('stocktake'),
            'adjustment' => $translator->get('adjustment')
        ];
    }

    private function getSetting(string $key, $default = null)
    {
        if (!$this->db()->tableExists('settings')) {
            return $default;
        }

        $value = $this->db()->fetchColumn(
            "SELECT value FROM settings WHERE `key` = ? LIMIT 1",
            [$key]
        );

        if ($value === false || $value === null) {
            return $default;
        }

        return $value;
    }

    private function getCostingMethods(): array
    {
        return [
            'FIFO' => $this->__('costing_method_fifo'),
            'LIFO' => $this->__('costing_method_lifo'),
            'AVG' => $this->__('costing_method_avg'),
            'MANUAL' => $this->__('costing_method_manual')
        ];
    }

    private function parseBatchAllocations(string $raw): array
    {
        $allocations = [];
        $pairs = array_filter(array_map('trim', explode(',', $raw)));
        foreach ($pairs as $pair) {
            [$batchId, $qty] = array_pad(array_map('trim', explode(':', $pair)), 2, null);
            if (!$batchId || !$qty) {
                continue;
            }
            $allocations[] = [
                'batch_id' => (int)$batchId,
                'quantity' => (float)$qty
            ];
        }
        return $allocations;
    }

    private function resolveIssueSourceType(string $type): ?string
    {
        if (in_array($type, ['issue', 'adjustment', 'stocktake'], true)) {
            return 'manual';
        }
        if ($type === 'receipt') {
            return 'receipt';
        }
        return null;
    }
}
