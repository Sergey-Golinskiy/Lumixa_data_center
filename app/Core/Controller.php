<?php

namespace App\Core;

/**
 * Base Controller Class
 */
abstract class Controller
{
    protected array $data = [];

    /**
     * Render a view
     */
    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = LMS_ROOT . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // If layout is set, wrap content
        if (isset($this->data['layout'])) {
            $layoutPath = LMS_ROOT . '/views/layouts/' . $this->data['layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }

        echo $content;
    }

    /**
     * Set layout
     */
    protected function setLayout(string $layout): self
    {
        $this->data['layout'] = $layout;
        return $this;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $code = 200): void
    {
        jsonResponse($data, $code);
    }

    /**
     * Redirect
     */
    protected function redirect(string $path, array $flash = []): void
    {
        foreach ($flash as $key => $value) {
            setFlash($key, $value);
        }
        redirect(url($path));
    }

    /**
     * Redirect back
     */
    protected function back(array $flash = []): void
    {
        foreach ($flash as $key => $value) {
            setFlash($key, $value);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }

    /**
     * Store old input for form repopulation
     */
    protected function storeOldInput(): void
    {
        $_SESSION['_old_input'] = $_POST;
    }

    /**
     * Clear old input
     */
    protected function clearOldInput(): void
    {
        unset($_SESSION['_old_input']);
    }

    /**
     * Validate CSRF and abort if invalid
     */
    protected function validateCsrfOrAbort(): void
    {
        if (!validateCsrf()) {
            abort(403, 'CSRF token mismatch');
        }
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!auth()) {
            $this->redirect('login');
        }
    }

    /**
     * Require permission
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();

        if (!can($permission)) {
            abort(403, 'Permission denied');
        }
    }

    /**
     * Get validated input
     */
    protected function validate(array $rules): array
    {
        $data = [];
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = input($field);

            // Parse rules
            $fieldRules = is_string($rule) ? explode('|', $rule) : $rule;

            foreach ($fieldRules as $r) {
                // Required
                if ($r === 'required' && ($value === null || $value === '')) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                    break;
                }

                // Email
                if ($r === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Invalid email address';
                    break;
                }

                // Numeric
                if ($r === 'numeric' && $value !== '' && !is_numeric($value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be numeric';
                    break;
                }

                // Integer
                if ($r === 'integer' && $value !== '' && !ctype_digit((string)$value)) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be an integer';
                    break;
                }

                // Min length
                if (preg_match('/^min:(\d+)$/', $r, $m) && strlen($value) < (int)$m[1]) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $m[1] . ' characters';
                    break;
                }

                // Max length
                if (preg_match('/^max:(\d+)$/', $r, $m) && strlen($value) > (int)$m[1]) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be at most ' . $m[1] . ' characters';
                    break;
                }
            }

            $data[$field] = $value;
        }

        if ($errors) {
            $this->storeOldInput();
            $_SESSION['_validation_errors'] = $errors;
            $this->back(['error' => 'Please fix the errors below']);
        }

        return $data;
    }

    /**
     * Get validation errors
     */
    protected function getValidationErrors(): array
    {
        $errors = $_SESSION['_validation_errors'] ?? [];
        unset($_SESSION['_validation_errors']);
        return $errors;
    }
}
