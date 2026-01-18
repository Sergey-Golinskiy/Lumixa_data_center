<?php
/**
 * Base Controller
 */

namespace App\Core;

abstract class Controller
{
    protected Application $app;
    protected View $view;
    protected Session $session;
    protected ?Database $db = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->view = new View($app);
        $this->session = $app->getSession();
    }

    /**
     * Get database connection
     */
    protected function db(): Database
    {
        if ($this->db === null) {
            $this->db = $this->app->getDatabase();
        }
        return $this->db;
    }

    /**
     * Render view
     */
    protected function view(string $template, array $data = []): void
    {
        echo $this->view->render($template, $data);
    }

    /**
     * Render view (alias for view() method)
     * Some controllers use render() instead of view() - this provides compatibility
     */
    protected function render(string $template, array $data = []): void
    {
        $this->view($template, $data);
    }

    /**
     * Render JSON response
     */
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    /**
     * Redirect back
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWithMessage(string $url, string $type, string $message): void
    {
        $this->session->setFlash($type, $message);
        $this->redirect($url);
    }

    /**
     * Get request input
     */
    protected function input(?string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);

        if ($key === null) {
            return $input;
        }

        return $input[$key] ?? $default;
    }

    /**
     * Get POST data
     */
    protected function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $this->post('_csrf_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $this->session->verifyCsrfToken($token);
    }

    /**
     * Get CSRF token
     */
    protected function csrfToken(): string
    {
        return $this->session->getCsrfToken();
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->session->setFlash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return $this->session->get('user') !== null;
    }

    /**
     * Get current user
     */
    protected function user(): ?array
    {
        return $this->session->get('user');
    }

    /**
     * Check permission
     */
    protected function authorize(string $permission): void
    {
        if (!$this->can($permission)) {
            http_response_code(403);
            $this->view('errors/403', [
                'title' => 'Access Denied',
                'message' => "You don't have permission to perform this action."
            ]);
            exit;
        }
    }

    /**
     * Require authentication and permission
     *
     * Convenience method that combines requireAuth() and authorize().
     * Use this in controller methods that need both authentication and permission check.
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();
        $this->authorize($permission);
    }

    /**
     * Check if user can perform action
     */
    protected function can(string $permission): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Admin can do anything
        if (in_array('admin', $user['roles'] ?? [])) {
            return true;
        }

        return in_array($permission, $user['permissions'] ?? []);
    }

    /**
     * Check if user has role
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        return in_array($role, $user['roles'] ?? []);
    }

    /**
     * Validate input
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $ruleItems = is_string($rule) ? explode('|', $rule) : $rule;

            foreach ($ruleItems as $ruleItem) {
                $params = [];

                if (str_contains($ruleItem, ':')) {
                    [$ruleItem, $paramStr] = explode(':', $ruleItem, 2);
                    $params = explode(',', $paramStr);
                }

                $error = $this->validateRule($field, $value, $ruleItem, $params);

                if ($error) {
                    $errors[$field] = $error;
                    break;
                }
            }

            if (!isset($errors[$field])) {
                $data[$field] = $value;
            }
        }

        if (!empty($errors)) {
            $this->session->setErrors($errors);
            $this->session->flashInput($_POST);
            $this->back();
        }

        return $data;
    }

    /**
     * Validate single rule
     */
    private function validateRule(string $field, $value, string $rule, array $params): ?string
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "{$label} is required";
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$label} must be a valid email";
                }
                break;

            case 'min':
                $min = (int)($params[0] ?? 0);
                if (strlen($value) < $min) {
                    return "{$label} must be at least {$min} characters";
                }
                break;

            case 'max':
                $max = (int)($params[0] ?? 255);
                if (strlen($value) > $max) {
                    return "{$label} must not exceed {$max} characters";
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return "{$label} must be a number";
                }
                break;

            case 'confirmed':
                $confirmation = $this->input($field . '_confirmation');
                if ($value !== $confirmation) {
                    return "{$label} confirmation does not match";
                }
                break;

            case 'unique':
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $exceptId = $params[2] ?? null;

                if ($value && $this->checkUnique($table, $column, $value, $exceptId)) {
                    return "{$label} already exists";
                }
                break;
        }

        return null;
    }

    /**
     * Check if value is unique
     */
    private function checkUnique(string $table, string $column, $value, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        if ($exceptId) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }

        return $this->db()->fetchColumn($sql, $params) > 0;
    }

    /**
     * Set flash message
     */
    protected function flash(string $type, string $message): void
    {
        $this->session->setFlash($type, $message);
    }

    /**
     * Log action
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        $this->app->getLogger()->log($level, $message, $context);
    }

    /**
     * Show 404 Not Found page
     */
    protected function notFound(string $message = 'Resource not found'): void
    {
        http_response_code(404);
        $this->view('errors/404', [
            'title' => 'Not Found',
            'message' => $message
        ]);
        exit;
    }

    /**
     * Log audit trail entry
     *
     * @param string $action Action performed (e.g., 'user.created', 'order.updated')
     * @param string $entityType Type of entity being modified
     * @param int|string|null $entityId ID of the entity
     * @param array|null $oldData Previous state of the entity
     * @param array|null $newData New state of the entity
     */
    protected function audit(
        string $action,
        string $entityType,
        $entityId = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $user = $this->user();
        $userId = $user['id'] ?? null;

        try {
            $this->db()->insert('audit_log', [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => (int)($entityId ?? 0),
                'old_values' => $oldData ? json_encode($oldData) : null,
                'new_values' => $newData ? json_encode($newData) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            $this->log('error', 'Failed to create audit log entry: ' . $e->getMessage(), [
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ]);
        }
    }
}
