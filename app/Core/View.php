<?php
/**
 * View - Simple template engine
 */

namespace App\Core;

class View
{
    private Application $app;
    private string $viewPath;
    private string $layoutPath;
    private ?string $layout = 'main';
    private array $sections = [];
    private ?string $currentSection = null;
    private array $shared = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->viewPath = $app->basePath('views');
        $this->layoutPath = $app->basePath('views/layouts');

        // Share common data
        $this->share('app', $app);
        $this->share('config', $app->getConfig());
        $this->share('requestId', $app->getRequestId());
        $this->share('currentLocale', $app->getLocale());
        $this->share('supportedLocales', Translator::SUPPORTED_LOCALES);
        $this->share('localeNames', Translator::LOCALE_NAMES);
    }

    /**
     * Render a view
     */
    public function render(string $view, array $data = []): string
    {
        // Merge shared data
        $data = array_merge($this->shared, $data);

        // Extract data to variables
        extract($data);

        // Render view content
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Render with layout if set
        if ($this->layout) {
            $layoutFile = $this->layoutPath . '/' . $this->layout . '.php';

            if (file_exists($layoutFile)) {
                // Only set content section if not already defined by section()/endSection()
                if (!isset($this->sections['content'])) {
                    $this->sections['content'] = $content;
                }
                ob_start();
                include $layoutFile;
                $content = ob_get_clean();
            }
        }

        return $content;
    }

    /**
     * Set layout
     */
    public function setLayout(?string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * No layout
     */
    public function withoutLayout(): self
    {
        $this->layout = null;
        return $this;
    }

    /**
     * Share data with all views
     */
    public function share(string $key, $value): self
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * Start a section
     */
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * End a section
     */
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    /**
     * Yield a section
     */
    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Include a partial
     */
    public function partial(string $view, array $data = []): string
    {
        $data = array_merge($this->shared, $data);
        extract($data);

        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            return '';
        }

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Escape HTML
     */
    public function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Output escaped HTML
     */
    public function echo($value): void
    {
        echo $this->e($value);
    }

    /**
     * Generate URL
     */
    public function url(string $path = ''): string
    {
        $base = rtrim((string)$this->app->config('app_url', ''), '/');
        if ($base === '' || str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            if ($host !== '') {
                $scheme = 'http';
                if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                    $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
                } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                    $scheme = 'https';
                }
                $base = $scheme . '://' . $host;
            } else {
                $base = '';
            }
        }

        if ($base === '') {
            return '/' . ltrim($path, '/');
        }

        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Generate asset URL
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }

    /**
     * Check if user can perform action
     */
    public function can(string $permission): bool
    {
        $session = $this->app->getSession();
        $user = $session->get('user');

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
     * Get authenticated user
     */
    public function user(): ?array
    {
        return $this->app->getSession()->get('user');
    }

    /**
     * Check if authenticated
     */
    public function auth(): bool
    {
        return $this->app->getSession()->get('user') !== null;
    }

    /**
     * Get flash message
     */
    public function flash(string $key): ?string
    {
        return $this->app->getSession()->flash($key);
    }

    /**
     * Get old input value
     */
    public function old(string $key, $default = ''): string
    {
        $old = $this->app->getSession()->get('_old_input', []);
        return $old[$key] ?? $default;
    }

    /**
     * Check for errors
     */
    public function hasError(string $field): bool
    {
        $errors = $this->app->getSession()->get('_errors', []);
        return isset($errors[$field]);
    }

    /**
     * Get error message
     */
    public function error(string $field): ?string
    {
        $errors = $this->app->getSession()->get('_errors', []);
        return $errors[$field] ?? null;
    }

    /**
     * Get all errors
     */
    public function errors(): array
    {
        return $this->app->getSession()->get('_errors', []);
    }

    /**
     * Format date
     */
    public function date($date, string $format = 'Y-m-d H:i'): string
    {
        if (!$date) {
            return '';
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date->format($format);
    }

    /**
     * Format datetime (alias for date)
     */
    public function datetime($date, string $format = 'Y-m-d H:i'): string
    {
        return $this->date($date, $format);
    }

    /**
     * Format number
     */
    public function number($value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', ' ');
    }

    /**
     * Format currency
     */
    public function currency($value): string
    {
        $symbol = $this->app->config('currency_symbol', '₴');
        return $this->number($value) . ' ' . $symbol;
    }

    /**
     * Translate a key
     */
    public function __($key, array $params = []): string
    {
        return $this->app->getTranslator()->get($key, $params);
    }

    /**
     * Alias for translate
     */
    public function trans($key, array $params = []): string
    {
        return $this->__($key, $params);
    }

    /**
     * Get current locale
     */
    public function locale(): string
    {
        return $this->app->getLocale();
    }

    /**
     * Get contrasting text color (black or white) for a given hex background
     */
    public function contrastColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luma = 0.299 * $r + 0.587 * $g + 0.114 * $b;
        return $luma > 160 ? '#000000' : '#ffffff';
    }

    /**
     * Get audit action badge color
     */
    public function getAuditActionBadge(string $action): string
    {
        if (strpos($action, 'created') !== false || strpos($action, 'create') !== false) {
            return 'success';
        }
        if (strpos($action, 'deleted') !== false || strpos($action, 'delete') !== false) {
            return 'danger';
        }
        if (strpos($action, 'updated') !== false || strpos($action, 'update') !== false) {
            return 'info';
        }
        return 'secondary';
    }
}
