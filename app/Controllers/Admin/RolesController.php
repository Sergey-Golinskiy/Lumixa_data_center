<?php
/**
 * RolesController - Role management
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class RolesController extends Controller
{
    /**
     * List roles
     */
    public function index(): void
    {
        $this->requirePermission('admin.roles.view');

        $roles = $this->db()->fetchAll(
            "SELECT r.*, COUNT(ur.user_id) as user_count
             FROM roles r
             LEFT JOIN user_roles ur ON r.id = ur.role_id
             GROUP BY r.id
             ORDER BY r.name"
        );

        $this->view('admin/roles/index', [
            'title' => 'Roles',
            'roles' => $roles
        ]);
    }

    /**
     * Create role form
     */
    public function create(): void
    {
        $this->requirePermission('admin.roles.create');

        $permissions = $this->getAvailablePermissions();

        $this->view('admin/roles/form', [
            'title' => 'Create Role',
            'role' => null,
            'permissions' => $permissions,
            'rolePermissions' => [],
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Store new role
     */
    public function store(): void
    {
        $this->requirePermission('admin.roles.create');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/roles/create');
            return;
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'slug' => $this->slugify($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db()->insert('roles', $data);

        // Assign permissions
        $permissions = $this->post('permissions', []);
        foreach ($permissions as $permission) {
            $this->db()->insert('role_permissions', [
                'role_id' => $id,
                'permission' => $permission
            ]);
        }

        $this->session->setFlash('success', 'Role created successfully');
        $this->redirect('/admin/roles');
    }

    /**
     * Edit role form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.roles.edit');

        $role = $this->db()->fetch("SELECT * FROM roles WHERE id = ?", [$id]);

        if (!$role) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Role Not Found']);
            return;
        }

        $permissions = $this->getAvailablePermissions();
        $rolePermissions = $this->db()->fetchAll(
            "SELECT permission FROM role_permissions WHERE role_id = ?",
            [$id]
        );
        $rolePermissions = array_column($rolePermissions, 'permission');

        $this->view('admin/roles/form', [
            'title' => 'Edit ' . $role['name'],
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Update role
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.roles.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/roles/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => trim($this->post('name', '')),
            'description' => trim($this->post('description', '')),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db()->update('roles', $data, ['id' => $id]);

        // Update permissions
        $this->db()->delete('role_permissions', ['role_id' => $id]);
        $permissions = $this->post('permissions', []);
        foreach ($permissions as $permission) {
            $this->db()->insert('role_permissions', [
                'role_id' => $id,
                'permission' => $permission
            ]);
        }

        $this->session->setFlash('success', 'Role updated successfully');
        $this->redirect('/admin/roles');
    }

    /**
     * Get available permissions
     */
    private function getAvailablePermissions(): array
    {
        return [
            'admin' => [
                'admin.dashboard.view' => 'View Admin Dashboard',
                'admin.users.view' => 'View Users',
                'admin.users.create' => 'Create Users',
                'admin.users.edit' => 'Edit Users',
                'admin.users.delete' => 'Delete Users',
                'admin.roles.view' => 'View Roles',
                'admin.roles.create' => 'Create Roles',
                'admin.roles.edit' => 'Edit Roles',
                'admin.diagnostics.view' => 'View Diagnostics',
                'admin.backups.view' => 'View Backups',
                'admin.backups.create' => 'Create Backups',
                'admin.backups.restore' => 'Restore Backups',
                'admin.backups.delete' => 'Delete Backups',
                'admin.audit.view' => 'View Audit Log'
            ],
            'warehouse' => [
                'warehouse.items.view' => 'View Items',
                'warehouse.items.create' => 'Create Items',
                'warehouse.items.edit' => 'Edit Items',
                'warehouse.documents.view' => 'View Documents',
                'warehouse.documents.create' => 'Create Documents',
                'warehouse.documents.edit' => 'Edit Documents',
                'warehouse.documents.post' => 'Post Documents',
                'warehouse.documents.cancel' => 'Cancel Documents',
                'warehouse.lots.view' => 'View Lots',
                'warehouse.lots.create' => 'Create Lots',
                'warehouse.lots.edit' => 'Edit Lots',
                'warehouse.stock.view' => 'View Stock',
                'warehouse.partners.view' => 'View Partners',
                'warehouse.partners.create' => 'Create Partners',
                'warehouse.partners.edit' => 'Edit Partners'
            ],
            'catalog' => [
                'catalog.products.view' => 'View Products',
                'catalog.products.create' => 'Create Products',
                'catalog.products.edit' => 'Edit Products',
                'catalog.variants.view' => 'View Variants',
                'catalog.variants.create' => 'Create Variants',
                'catalog.variants.edit' => 'Edit Variants',
                'catalog.bom.view' => 'View BOM',
                'catalog.bom.create' => 'Create BOM',
                'catalog.bom.edit' => 'Edit BOM',
                'catalog.routing.view' => 'View Routing',
                'catalog.routing.create' => 'Create Routing',
                'catalog.routing.edit' => 'Edit Routing'
            ],
            'production' => [
                'production.orders.view' => 'View Production Orders',
                'production.orders.create' => 'Create Production Orders',
                'production.orders.edit' => 'Edit Production Orders',
                'production.tasks.view' => 'View Tasks',
                'production.tasks.edit' => 'Edit Tasks',
                'production.print-queue.view' => 'View Print Queue',
                'production.print-queue.create' => 'Create Print Jobs',
                'production.print-queue.edit' => 'Edit Print Jobs'
            ],
            'costing' => [
                'costing.view' => 'View Cost Analysis'
            ]
        ];
    }

    /**
     * Create slug from string
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return preg_replace('~-+~', '-', $text);
    }
}
