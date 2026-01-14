<?php
/**
 * UsersController - User management
 */

namespace App\Controllers\Admin;

use App\Core\Controller;

class UsersController extends Controller
{
    /**
     * List users
     */
    public function index(): void
    {
        $this->requirePermission('admin.users.view');

        $page = max(1, (int)$this->get('page', 1));
        $perPage = 25;
        $search = trim($this->get('search', ''));

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->db()->fetchColumn("SELECT COUNT(*) FROM users WHERE {$whereClause}", $params);

        $offset = ($page - 1) * $perPage;
        $users = $this->db()->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.name) as role_names
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE {$whereClause}
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->view('admin/users/index', [
            'title' => 'Users',
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    /**
     * Show user details
     */
    public function show(string $id): void
    {
        $this->requirePermission('admin.users.view');

        $user = $this->db()->fetch(
            "SELECT u.*, GROUP_CONCAT(r.name) as role_names
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.id = ?
             GROUP BY u.id",
            [$id]
        );

        if (!$user) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'User Not Found']);
            return;
        }

        $this->view('admin/users/show', [
            'title' => $user['username'],
            'user' => $user
        ]);
    }

    /**
     * Create user form
     */
    public function create(): void
    {
        $this->requirePermission('admin.users.create');

        $roles = $this->db()->fetchAll("SELECT * FROM roles ORDER BY name");

        $this->view('admin/users/form', [
            'title' => 'Create User',
            'user' => null,
            'roles' => $roles,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        $this->requirePermission('admin.users.create');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/users/create');
            return;
        }

        $data = [
            'username' => trim($this->post('username', '')),
            'email' => trim($this->post('email', '')),
            'full_name' => trim($this->post('full_name', '')),
            'password' => password_hash($this->post('password', ''), PASSWORD_DEFAULT),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db()->insert('users', $data);

        // Assign roles
        $roleIds = $this->post('roles', []);
        foreach ($roleIds as $roleId) {
            $this->db()->insert('user_roles', ['user_id' => $id, 'role_id' => $roleId]);
        }

        $this->session->setFlash('success', 'User created successfully');
        $this->redirect('/admin/users/' . $id);
    }

    /**
     * Edit user form
     */
    public function edit(string $id): void
    {
        $this->requirePermission('admin.users.edit');

        $user = $this->db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);

        if (!$user) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'User Not Found']);
            return;
        }

        $roles = $this->db()->fetchAll("SELECT * FROM roles ORDER BY name");
        $userRoles = $this->db()->fetchAll(
            "SELECT role_id FROM user_roles WHERE user_id = ?",
            [$id]
        );
        $userRoleIds = array_column($userRoles, 'role_id');

        $this->view('admin/users/form', [
            'title' => 'Edit ' . $user['username'],
            'user' => $user,
            'roles' => $roles,
            'userRoleIds' => $userRoleIds,
            'csrfToken' => $this->csrfToken()
        ]);
    }

    /**
     * Update user
     */
    public function update(string $id): void
    {
        $this->requirePermission('admin.users.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        $data = [
            'username' => trim($this->post('username', '')),
            'email' => trim($this->post('email', '')),
            'full_name' => trim($this->post('full_name', '')),
            'is_active' => $this->post('is_active') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $password = $this->post('password', '');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->db()->update('users', $data, ['id' => $id]);

        // Update roles
        $this->db()->delete('user_roles', ['user_id' => $id]);
        $roleIds = $this->post('roles', []);
        foreach ($roleIds as $roleId) {
            $this->db()->insert('user_roles', ['user_id' => $id, 'role_id' => $roleId]);
        }

        $this->session->setFlash('success', 'User updated successfully');
        $this->redirect('/admin/users/' . $id);
    }

    /**
     * Delete user
     */
    public function delete(string $id): void
    {
        $this->requirePermission('admin.users.delete');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        // Prevent self-deletion
        if ((int)$id === $this->user()['id']) {
            $this->session->setFlash('error', 'Cannot delete yourself');
            $this->redirect('/admin/users');
            return;
        }

        $this->db()->delete('user_roles', ['user_id' => $id]);
        $this->db()->delete('users', ['id' => $id]);

        $this->session->setFlash('success', 'User deleted');
        $this->redirect('/admin/users');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(string $id): void
    {
        $this->requirePermission('admin.users.edit');

        if (!$this->validateCsrf()) {
            $this->session->setFlash('error', 'Invalid security token');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $user = $this->db()->fetch("SELECT is_active FROM users WHERE id = ?", [$id]);

        if ($user) {
            $this->db()->update('users', [
                'is_active' => $user['is_active'] ? 0 : 1,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);

            $this->session->setFlash('success', 'User status updated');
        }

        $this->redirect('/admin/users/' . $id);
    }
}
