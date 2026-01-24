<?php
/**
 * Application Bootstrap
 *
 * Central class that initializes and runs the application
 */

namespace App\Core;

class Application
{
    private static ?Application $instance = null;
    private array $config;
    private ?Database $db = null;
    private ?Session $session = null;
    private ?Router $router = null;
    private ?Logger $logger = null;
    private ?Translator $translator = null;
    private string $requestId;
    private bool $isInstalled = false;

    private function __construct()
    {
        $this->requestId = $this->generateRequestId();
    }

    public static function getInstance(): Application
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bootstrap the application
     */
    public function boot(): void
    {
        // Load configuration
        $this->loadConfig();

        // Set timezone
        date_default_timezone_set($this->config['timezone'] ?? 'UTC');

        // Initialize error handler
        ErrorHandler::register($this);

        // Initialize logger
        $this->logger = new Logger($this->config['log_file'], $this->config['log_level']);
        $this->logger->setRequestId($this->requestId);

        // Initialize session
        $this->session = new Session($this->config);
        $this->session->start();

        // Initialize translator
        $this->translator = Translator::getInstance($this->basePath('lang'));
        $locale = $this->session->get('locale', $this->config['default_locale'] ?? 'en');
        $this->translator->setLocale($locale);

        // Check if installed
        $this->isInstalled = $this->checkInstalled();

        // Initialize router
        $this->router = new Router($this);
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            // Parse URI
            $uri = parse_url($uri, PHP_URL_PATH);
            $uri = rtrim($uri, '/') ?: '/';

            // Log request
            $this->logger->info("Request: {$method} {$uri}", [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // Check if needs setup
            if (!$this->isInstalled && $uri !== '/setup' && !str_starts_with($uri, '/assets')) {
                $this->redirect('/setup');
                return;
            }

            // Load routes
            $this->loadRoutes();

            // Dispatch request
            $this->router->dispatch($method, $uri);

        } catch (\Throwable $e) {
            ErrorHandler::handleException($e);
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/config.php';

        if (!file_exists($configFile)) {
            // Create minimal config for setup
            $this->config = [
                'app_env' => 'dev',
                'app_debug' => true,
                'app_name' => 'Lumixa LMS',
                'app_version' => '1.0.0',
                'timezone' => 'UTC',
                'log_level' => 'DEBUG',
                'log_file' => dirname(__DIR__, 2) . '/storage/logs/app.log',
                'base_path' => dirname(__DIR__, 2),
                'storage_path' => dirname(__DIR__, 2) . '/storage',
                'upload_path' => dirname(__DIR__, 2) . '/public/uploads',
            ];
            return;
        }

        $this->config = require $configFile;
    }

    /**
     * Load routes
     */
    private function loadRoutes(): void
    {
        $routesFile = dirname(__DIR__, 2) . '/config/routes.php';
        if (file_exists($routesFile)) {
            $routes = require $routesFile;
            if (is_callable($routes)) {
                $routes($this->router);
            }
        }
    }

    /**
     * Check if application is installed
     */
    private function checkInstalled(): bool
    {
        $installedFile = $this->config['storage_path'] . '/.installed';

        // Try to connect to database
        try {
            $db = $this->getDatabase();
            $userCount = (int)$db->fetchColumn("SELECT COUNT(*) FROM users");

            if (file_exists($installedFile)) {
                return $userCount > 0;
            }

            $appInstalled = $db->fetchColumn(
                "SELECT value FROM settings WHERE `key` = 'app_installed' LIMIT 1"
            );

            if ($userCount > 0 && (string)$appInstalled === '1') {
                file_put_contents($installedFile, date('Y-m-d H:i:s'));
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Get database connection
     */
    public function getDatabase(): Database
    {
        if ($this->db === null) {
            $this->db = new Database($this->config);
        }
        return $this->db;
    }

    /**
     * Get session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * Get logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Get router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get translator
     */
    public function getTranslator(): Translator
    {
        return $this->translator;
    }

    /**
     * Set locale
     */
    public function setLocale(string $locale): void
    {
        if (in_array($locale, Translator::SUPPORTED_LOCALES)) {
            $this->translator->setLocale($locale);
            $this->session->set('locale', $locale);
        }
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    /**
     * Get config value
     */
    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all config
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get request ID
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Check if installed
     */
    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    /**
     * Mark as installed
     */
    public function markInstalled(): void
    {
        $file = $this->config['storage_path'] . '/.installed';
        file_put_contents($file, date('Y-m-d H:i:s'));
        $this->isInstalled = true;
    }

    /**
     * Check if debug mode
     */
    public function isDebug(): bool
    {
        return (bool)($this->config['app_debug'] ?? false);
    }

    /**
     * Check if production
     */
    public function isProduction(): bool
    {
        return ($this->config['app_env'] ?? 'prod') === 'prod';
    }

    /**
     * Redirect to URL
     */
    public function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    /**
     * Generate request ID
     */
    private function generateRequestId(): string
    {
        return substr(bin2hex(random_bytes(8)), 0, 12);
    }

    /**
     * Get base path
     */
    public function basePath(string $path = ''): string
    {
        $base = $this->config['base_path'] ?? dirname(__DIR__, 2);
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }

    /**
     * Get storage path
     */
    public function storagePath(string $path = ''): string
    {
        $base = $this->config['storage_path'] ?? $this->basePath('storage');
        return $path ? $base . '/' . ltrim($path, '/') : $base;
    }
}
