<?php
/**
 * ItemOptionsController - Manage item option lists
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class ItemOptionsController extends Controller
{
    private array $groups = [
        'materials' => [
            'key' => 'material',
            'label' => 'materials',
            'singular' => 'material',
            'supports_filament' => true
        ],
        'manufacturers' => [
            'key' => 'manufacturer',
            'label' => 'manufacturers',
            'singular' => 'manufacturer',
            'supports_filament' => false
        ],
        'plastic-types' => [
            'key' => 'plastic_type',
            'label' => 'plastic_types',
            'singular' => 'plastic_type',
            'supports_filament' => false
        ],
        'filament-aliases' => [
            'key' => 'filament_alias',
            'label' => 'filament_aliases',
            'singular' => 'filament_alias',
            'supports_filament' => false,
            'supports_color' => true
        ]
    ];

    /**
     * List options
     */
    public function index(string $group): void
    {
        $this->requirePermission('admin.item_options.view');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        $options = $this->db()->fetchAll(
            "SELECT * FROM item_option_values WHERE group_key = ? ORDER BY name",
            [$config['key']]
        );

        $this->view('admin/item-options/index', [
            'title' => $translator->get($config['label']),
            'options' => $options,
            'group' => $group,
            'groupLabel' => $translator->get($config['label']),
            'showFilament' => $config['supports_filament'],
            'showColor' => !empty($config['supports_color']) && $this->db()->columnExists('item_option_values', 'color')
        ]);
    }

    /**
     * Show create form
     */
    public function create(string $group): void
    {
        $this->requirePermission('admin.item_options.create');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        $titleKey = $config['singular'] === 'material' ? 'create_material' : 'create_item_option';

        $this->view('admin/item-options/form', [
            'title' => $translator->get($titleKey),
            'option' => null,
            'group' => $group,
            'groupLabel' => $translator->get($config['label']),
            'showFilament' => $config['supports_filament'],
            'showColor' => !empty($config['supports_color']) && $this->db()->columnExists('item_option_values', 'color')
        ]);
    }

    /**
     * Store option
     */
    public function store(string $group): void
    {
        $this->requirePermission('admin.item_options.create');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/item-options/{$group}/create");
            return;
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'is_filament' => $config['supports_filament'] && $this->post('is_filament') ? 1 : 0
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = $translator->get('item_option_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM item_option_values WHERE group_key = ? AND name = ?",
                [$config['key'], $data['name']]
            );
            if ($exists) {
                $errors['name'] = $translator->get('item_option_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/item-options/{$group}/create");
            return;
        }

        $insertData = [
            'group_key' => $config['key'],
            'name' => $data['name'],
            'is_active' => $data['is_active'],
            'is_filament' => $data['is_filament'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($config['supports_color']) && $this->db()->columnExists('item_option_values', 'color')) {
            $insertData['color'] = !empty($_POST['color']) ? trim($_POST['color']) : null;
        }

        $id = $this->db()->insert('item_option_values', $insertData);

        $this->audit('item_option.created', 'item_option_values', $id, null, $data);
        $this->session->setFlash('success', $translator->get('item_option_created_success'));
        $this->redirect("/admin/item-options/{$group}");
    }

    /**
     * Show edit form
     */
    public function edit(string $group, string $id): void
    {
        $this->requirePermission('admin.item_options.edit');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        $option = $this->db()->fetch(
            "SELECT * FROM item_option_values WHERE id = ? AND group_key = ?",
            [$id, $config['key']]
        );

        if (!$option) {
            $this->notFound();
        }

        $titleKey = $config['singular'] === 'material' ? 'edit_material' : 'edit_item_option';

        $this->view('admin/item-options/form', [
            'title' => $translator->get($titleKey),
            'option' => $option,
            'group' => $group,
            'groupLabel' => $translator->get($config['label']),
            'showFilament' => $config['supports_filament'],
            'showColor' => !empty($config['supports_color']) && $this->db()->columnExists('item_option_values', 'color')
        ]);
    }

    /**
     * Update option
     */
    public function update(string $group, string $id): void
    {
        $this->requirePermission('admin.item_options.edit');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/item-options/{$group}/{$id}/edit");
            return;
        }

        $option = $this->db()->fetch(
            "SELECT * FROM item_option_values WHERE id = ? AND group_key = ?",
            [$id, $config['key']]
        );

        if (!$option) {
            $this->notFound();
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'is_filament' => $config['supports_filament'] && $this->post('is_filament') ? 1 : 0
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = $translator->get('item_option_name_required');
        } else {
            $exists = $this->db()->fetch(
                "SELECT id FROM item_option_values WHERE group_key = ? AND name = ? AND id != ?",
                [$config['key'], $data['name'], $id]
            );
            if ($exists) {
                $errors['name'] = $translator->get('item_option_name_exists');
            }
        }

        if ($errors) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->redirect("/admin/item-options/{$group}/{$id}/edit");
            return;
        }

        $oldName = $option['name'];
        $newName = $data['name'];

        $updateData = [
            'name' => $newName,
            'is_active' => $data['is_active'],
            'is_filament' => $data['is_filament'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($config['supports_color']) && $this->db()->columnExists('item_option_values', 'color')) {
            $updateData['color'] = !empty($_POST['color']) ? trim($_POST['color']) : null;
        }

        $this->db()->update('item_option_values', $updateData, ['id' => $id]);

        // If name changed, update all item_attributes records that use this value
        if ($oldName !== $newName) {
            $this->db()->query(
                "UPDATE item_attributes SET attribute_value = ? WHERE attribute_name = ? AND attribute_value = ?",
                [$newName, $config['key'], $oldName]
            );
        }

        $this->audit('item_option.updated', 'item_option_values', $id, $option, $data);
        $this->session->setFlash('success', $translator->get('item_option_updated_success'));
        $this->redirect("/admin/item-options/{$group}");
    }

    /**
     * Delete option
     */
    public function delete(string $group, string $id): void
    {
        $this->requirePermission('admin.item_options.delete');
        if (!$this->ensureOptionsTable()) {
            return;
        }

        $config = $this->getGroupConfig($group);
        $translator = $this->app->getTranslator();

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', $translator->get('invalid_security_token'));
            $this->redirect("/admin/item-options/{$group}");
            return;
        }

        $option = $this->db()->fetch(
            "SELECT * FROM item_option_values WHERE id = ? AND group_key = ?",
            [$id, $config['key']]
        );

        if (!$option) {
            $this->notFound();
        }

        $attributeName = $config['key'];
        $inUse = $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM item_attributes WHERE attribute_name = ? AND attribute_value = ?",
            [$attributeName, $option['name']]
        );

        if ((int)$inUse > 0) {
            $this->session->setFlash('error', $translator->get('item_option_in_use'));
            $this->redirect("/admin/item-options/{$group}");
            return;
        }

        $this->db()->delete('item_option_values', ['id' => $id]);
        $this->audit('item_option.deleted', 'item_option_values', $id, $option, null);
        $this->session->setFlash('success', $translator->get('item_option_deleted_success'));
        $this->redirect("/admin/item-options/{$group}");
    }

    private function getGroupConfig(string $group): array
    {
        if (!array_key_exists($group, $this->groups)) {
            $this->notFound();
        }

        return $this->groups[$group];
    }

    private function ensureOptionsTable(): bool
    {
        if ($this->db()->tableExists('item_option_values')) {
            return true;
        }

        $translator = $this->app->getTranslator();
        $this->session->setFlash('error', $translator->get('item_options_missing'));
        $this->redirect('/admin/diagnostics');
        return false;
    }
}
