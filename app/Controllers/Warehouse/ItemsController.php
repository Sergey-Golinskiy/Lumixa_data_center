<?php
/**
 * Items Controller - SKU Management
 */

namespace App\Controllers\Warehouse;

use App\Core\Controller;
use App\Core\Application;
use App\Services\Warehouse\ItemService;

class ItemsController extends Controller
{
    private ItemService $itemService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->itemService = new ItemService($app);
    }

    /**
     * List items
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        $page = max(1, (int)$this->get('page', 1));
        $perPage = $this->app->config('items_per_page', 25);
        $search = $this->get('search', '');
        $type = $this->get('type', '');

        $result = $this->itemService->paginate($page, $perPage, [
            'search' => $search,
            'type' => $type
        ]);

        $this->view('warehouse/items/index', [
            'title' => 'Items (SKU)',
            'items' => $result['items'],
            'pagination' => $result['pagination'],
            'search' => $search,
            'type' => $type,
            'types' => $this->itemService->getTypes()
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.create');

        $this->view('warehouse/items/form', [
            'title' => 'Create Item',
            'item' => null,
            'attributes' => [],
            'types' => $this->itemService->getTypes(),
            'units' => $this->itemService->getUnits(),
            'materialOptions' => $this->itemService->getOptionValues('material'),
            'manufacturerOptions' => $this->itemService->getOptionValues('manufacturer'),
            'plasticTypeOptions' => $this->itemService->getOptionValues('plastic_type'),
            'filamentAliasOptions' => $this->itemService->getOptionValues('filament_alias'),
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Store new item
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.create');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/items/create');
            return;
        }

        $data = $this->validate([
            'sku' => 'required|max:50|unique:items,sku',
            'name' => 'required|max:255',
            'type' => 'required',
            'unit' => 'required',
        ]);

        $data['description'] = $this->post('description', '');
        $data['min_stock'] = (float)$this->post('min_stock', 0);
        $data['reorder_point'] = (float)$this->post('reorder_point', 0);
        $data['costing_method'] = $this->post('costing_method', 'FIFO');
        $data['allow_method_override'] = $this->post('allow_method_override') ? 1 : 0;
        $imagePath = $this->storeImageUpload('image', 'items');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $attributes = $this->buildAttributes($data['type']);

        try {
            $item = $this->itemService->create($data, $attributes, $this->user()['id']);
            $this->session->setFlash('success', 'Item created successfully');
            $this->redirect('/warehouse/items/' . $item['id']);
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->session->flashInput($_POST);
            $this->redirect('/warehouse/items/create');
        }
    }

    /**
     * Show item details
     */
    public function show(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.view');

        $item = $this->itemService->findById((int)$id);

        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Item Not Found']);
            return;
        }

        $stock = $this->itemService->getStock((int)$id);
        $attributes = $this->itemService->getAttributes((int)$id);
        $history = $this->itemService->getMovementHistory((int)$id, 20);

        $this->view('warehouse/items/show', [
            'title' => $item['name'],
            'item' => $item,
            'stock' => $stock,
            'attributes' => $attributes,
            'history' => $history
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.edit');

        $item = $this->itemService->findById((int)$id);

        if (!$item) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Item Not Found']);
            return;
        }

        $attributes = $this->itemService->getAttributes((int)$id);

        $this->view('warehouse/items/form', [
            'title' => 'Edit Item',
            'item' => $item,
            'attributes' => $attributes,
            'types' => $this->itemService->getTypes(),
            'units' => $this->itemService->getUnits(),
            'materialOptions' => $this->itemService->getOptionValues('material'),
            'manufacturerOptions' => $this->itemService->getOptionValues('manufacturer'),
            'plasticTypeOptions' => $this->itemService->getOptionValues('plastic_type'),
            'filamentAliasOptions' => $this->itemService->getOptionValues('filament_alias'),
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Update item
     */
    public function update(string $id): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/warehouse/items/' . $id . '/edit');
            return;
        }

        $item = $this->itemService->findById((int)$id);

        if (!$item) {
            $this->session->setFlash('error', 'Item not found');
            $this->redirect('/warehouse/items');
            return;
        }

        $data = $this->validate([
            'sku' => 'required|max:50|unique:items,sku,' . $id,
            'name' => 'required|max:255',
            'type' => 'required',
            'unit' => 'required',
        ]);

        $data['description'] = $this->post('description', '');
        $data['min_stock'] = (float)$this->post('min_stock', 0);
        $data['reorder_point'] = (float)$this->post('reorder_point', 0);
        $data['costing_method'] = $this->post('costing_method', 'FIFO');
        $data['allow_method_override'] = $this->post('allow_method_override') ? 1 : 0;
        $data['is_active'] = $this->post('is_active') ? 1 : 0;
        $imagePath = $this->storeImageUpload('image', 'items');
        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $attributes = $this->buildAttributes($data['type']);

        try {
            $this->itemService->update((int)$id, $data, $attributes, $this->user()['id']);
            $this->session->setFlash('success', 'Item updated successfully');
            $this->redirect('/warehouse/items/' . $id);
        } catch (\Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->redirect('/warehouse/items/' . $id . '/edit');
        }
    }

    private function buildAttributes(string $type): array
    {
        $attributes = [];

        if ($type === 'material') {
            if ($this->post('attr_material')) {
                $attributes['material'] = $this->post('attr_material');
            }
            if ($this->post('attr_manufacturer')) {
                $attributes['manufacturer'] = $this->post('attr_manufacturer');
            }
            if ($this->post('attr_plastic_type')) {
                $attributes['plastic_type'] = $this->post('attr_plastic_type');
            }
            if ($this->post('attr_filament_color')) {
                $attributes['filament_color'] = $this->post('attr_filament_color');
            }
            if ($this->post('attr_filament_diameter')) {
                $attributes['filament_diameter'] = $this->post('attr_filament_diameter');
            }
            if ($this->post('attr_filament_alias')) {
                $attributes['filament_alias'] = $this->post('attr_filament_alias');
            }
            return $attributes;
        }

        if ($this->post('attr_color')) {
            $attributes['color'] = $this->post('attr_color');
        }
        if ($this->post('attr_diameter')) {
            $attributes['diameter'] = $this->post('attr_diameter');
        }
        if ($this->post('attr_brand')) {
            $attributes['brand'] = $this->post('attr_brand');
        }

        return $attributes;
    }

    /**
     * API: Generate SKU for given type
     */
    public function generateSku(): void
    {
        $this->requireAuth();
        $this->authorize('warehouse.items.create');

        $type = $this->get('type', '');

        if (empty($type)) {
            $this->json(['error' => 'Type is required'], 400);
            return;
        }

        // Check if this type requires manual SKU
        if ($this->itemService->requiresManualSku($type)) {
            $this->json(['error' => 'This type requires manual SKU input'], 400);
            return;
        }

        try {
            $sku = $this->itemService->generateNextSku($type);
            $this->json(['sku' => $sku]);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to generate SKU', [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            $this->json(['error' => 'Failed to generate SKU'], 500);
        }
    }

    /**
     * API: Check SKU uniqueness
     */
    public function checkSkuUniqueness(): void
    {
        $this->requireAuth();

        $sku = $this->get('sku', '');
        $excludeId = $this->get('exclude_id') ? (int)$this->get('exclude_id') : null;

        if (empty($sku)) {
            $this->json(['error' => 'SKU is required'], 400);
            return;
        }

        try {
            $isUnique = $this->itemService->isSkuUnique($sku, $excludeId);
            $this->json([
                'unique' => $isUnique,
                'message' => $isUnique ? 'SKU is available' : 'SKU already exists'
            ]);
        } catch (\Exception $e) {
            $this->app->getLogger()->error('Failed to check SKU uniqueness', [
                'sku' => $sku,
                'error' => $e->getMessage()
            ]);
            $this->json(['error' => 'Failed to check SKU'], 500);
        }
    }
}
