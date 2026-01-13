<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Services\AuditService;

/**
 * Admin User Controller
 */
class UserController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * List users
     */
    public function index(): void
    {
        $this->setLayout('main');

        $db = Database::getInstance();

        $page = max(1, (int) input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $search = input('search', '');
        $where = '1=1';
        $params = [];

        if ($search) {
            $where .= ' AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)';
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }

        $total = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE {$where}", $params);

        $users = $db->fetchAll(
            "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as role_names
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             WHERE {$where}
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
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
        ]);
    }

    /**
     * Show create user form
     */
    public function create(): void
    {
        $this->setLayout('main');

        $db = Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles ORDER BY name");

        $this->view('admin/users/create', [
            'title' => 'Create User',
            'roles' => $roles,
            'errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        $this->validateCsrfOrAbort();

        $data = $this->validate([
            'username' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required',
            'last_name' => '',
        ]);

        // Check if username exists
        if (User::findByUsername($data['username'])) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = ['username' => 'Username already exists'];
            $this->back(['error' => 'Username already exists']);
        }

        // Check if email exists
        if (User::findByEmail($data['email'])) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = ['email' => 'Email already exists'];
            $this->back(['error' => 'Email already exists']);
        }

        $user = new User();
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->setPassword($data['password']);
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'] ?? '';
        $user->is_active = input('is_active') ? 1 : 0;
        $user->must_change_password = input('must_change_password') ? 1 : 0;
        $user->save();

        // Assign roles
        $roleIds = input('roles', []);
        if (is_array($roleIds) && !empty($roleIds)) {
            $user->assignRoles(array_map('intval', $roleIds));
        }

        $this->auditService->logCreate('user', $user->id, [
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $this->redirect('admin/users', ['success' => 'User created successfully']);
    }

    /**
     * Show user details
     */
    public function show(int $id): void
    {
        $this->setLayout('main');

        $user = User::findOrFail($id);
        $roles = $user->getRoles();
        $history = $this->auditService->getEntityHistory('user', $id);

        $this->view('admin/users/show', [
            'title' => $user->getFullName(),
            'user' => $user,
            'roles' => $roles,
            'history' => $history,
        ]);
    }

    /**
     * Show edit user form
     */
    public function edit(int $id): void
    {
        $this->setLayout('main');

        $user = User::findOrFail($id);

        $db = Database::getInstance();
        $allRoles = $db->fetchAll("SELECT * FROM roles ORDER BY name");
        $userRoleIds = array_column($user->getRoles(), 'id');

        $this->view('admin/users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $allRoles,
            'userRoleIds' => $userRoleIds,
            'errors' => $this->getValidationErrors(),
        ]);
    }

    /**
     * Update user
     */
    public function update(int $id): void
    {
        $this->validateCsrfOrAbort();

        $user = User::findOrFail($id);

        $data = $this->validate([
            'email' => 'required|email',
            'first_name' => 'required',
            'last_name' => '',
        ]);

        // Check if email exists (excluding current user)
        $existing = User::findByEmail($data['email']);
        if ($existing && $existing->id !== $user->id) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = ['email' => 'Email already exists'];
            $this->back(['error' => 'Email already exists']);
        }

        $oldData = $user->toArray();

        $user->email = $data['email'];
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'] ?? '';
        $user->is_active = input('is_active') ? 1 : 0;
        $user->save();

        // Update roles
        $roleIds = input('roles', []);
        if (is_array($roleIds)) {
            $user->assignRoles(array_map('intval', $roleIds));
        }

        $this->auditService->logUpdate('user', $user->id, $oldData, $user->toArray());

        $this->redirect('admin/users', ['success' => 'User updated successfully']);
    }

    /**
     * Toggle user active status
     */
    public function toggle(int $id): void
    {
        $this->validateCsrfOrAbort();

        $user = User::findOrFail($id);

        // Prevent self-deactivation
        if ($user->id === userId()) {
            $this->back(['error' => 'You cannot deactivate your own account']);
        }

        $user->is_active = $user->is_active ? 0 : 1;
        $user->save();

        $action = $user->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            $action,
            'user',
            $user->id,
            "User {$action}"
        );

        $this->back(['success' => "User {$action} successfully"]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(int $id): void
    {
        $this->validateCsrfOrAbort();

        $user = User::findOrFail($id);
        $newPassword = input('new_password', '');

        if (strlen($newPassword) < 8) {
            $this->back(['error' => 'Password must be at least 8 characters']);
        }

        $user->setPassword($newPassword);
        $user->must_change_password = 1;
        $user->unlock();

        $this->auditService->log(
            'password_reset',
            'user',
            $user->id,
            'Password reset by admin'
        );

        $this->back(['success' => 'Password reset successfully']);
    }
}
