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

        $this->view('warehouse/documents/index', [
            'title' => 'Documents',
            'documents' => $result['documents'],
            'pagination' => $result['pagination'],
            'types' => $this->documentService->getTypes(),
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
    public function create(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.documents.create');

        $types = $this->documentService->getTypes();

        // Get items for dropdown
        $items = $this->db->fetchAll(
            "SELECT id, sku, name, unit FROM items WHERE is_active = 1 ORDER BY sku"
        );

        // Get partners for dropdown
        $partners = $this->db->fetchAll(
            "SELECT id, name, type FROM partners WHERE is_active = 1 ORDER BY name"
        );

        $this->view('warehouse/documents/form', [
            'title' => 'Create Document',
            'document' => null,
            'lines' => [],
            'items' => $items,
            'partners' => $partners,
            'csrfToken' => $this->csrfToken()
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
        $types = $this->documentService->getTypes();

        if (!isset($types[$type])) {
            $this->session->setFlash('error', 'Invalid document type');
            $this->redirect('/warehouse/documents/create');
            return;
        }

        $data = [
            'type' => $type,
            'document_date' => $this->post('document_date', date('Y-m-d')),
            'partner_id' => $this->post('partner_id') ?: null,
            'notes' => $this->post('notes', '')
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

        $this->view('warehouse/documents/show', [
            'title' => $document['document_number'],
            'document' => $document,
            'lines' => $lines,
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
        $items = $this->db->fetchAll(
            "SELECT id, sku, name, unit FROM items WHERE is_active = 1 ORDER BY sku"
        );

        // Get partners for dropdown
        $partners = $this->db->fetchAll(
            "SELECT id, name, type FROM partners WHERE is_active = 1 ORDER BY name"
        );

        $this->view('warehouse/documents/form', [
            'title' => 'Edit ' . $document['document_number'],
            'document' => $document,
            'lines' => $lines,
            'items' => $items,
            'partners' => $partners,
            'csrfToken' => $this->csrfToken()
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
            'notes' => $this->post('notes', '')
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
     * Post document
     */
    public function post(string $id): void
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

            $result[] = [
                'item_id' => (int)$line['item_id'],
                'lot_number' => $line['lot_number'] ?? null,
                'quantity' => (float)$line['quantity'],
                'unit_price' => (float)($line['unit_price'] ?? 0),
                'notes' => $line['notes'] ?? ''
            ];
        }

        return $result;
    }
}
