<?php
/**
 * Router - Simple routing system
 */

namespace App\Core;

class Router
{
    private Application $app;
    private array $routes = [];
    private array $namedRoutes = [];
    private array $groupStack = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Add GET route
     */
    public function get(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * Add POST route
     */
    public function post(string $path, $handler, ?string $name = null): self
    {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * Add route for any method
     */
    public function any(string $path, $handler, ?string $name = null): self
    {
        $this->addRoute('GET', $path, $handler, $name);
        $this->addRoute('POST', $path, $handler, null);
        return $this;
    }

    /**
     * Add route group with prefix
     */
    public function group(string $prefix, callable $callback, array $middleware = []): self
    {
        $this->groupStack[] = [
            'prefix' => $prefix,
            'middleware' => $middleware
        ];

        $callback($this);

        array_pop($this->groupStack);

        return $this;
    }

    /**
     * Add route
     */
    private function addRoute(string $method, string $path, $handler, ?string $name = null): self
    {
        // Apply group prefix
        $prefix = '';
        $middleware = [];
        foreach ($this->groupStack as $group) {
            $prefix .= $group['prefix'];
            $middleware = array_merge($middleware, $group['middleware']);
        }

        $fullPath = $prefix . $path;
        $fullPath = rtrim($fullPath, '/') ?: '/';

        // Convert path to regex pattern
        $pattern = $this->pathToPattern($fullPath);

        $this->routes[$method][$pattern] = [
            'handler' => $handler,
            'path' => $fullPath,
            'middleware' => $middleware
        ];

        if ($name) {
            $this->namedRoutes[$name] = $fullPath;
        }

        return $this;
    }

    /**
     * Convert path to regex pattern
     */
    private function pathToPattern(string $path): string
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);

        // Escape special regex characters (except our capture groups)
        $pattern = preg_replace('/\//', '\/', $pattern);

        return '/^' . $pattern . '$/';
    }

    /**
     * Dispatch request to handler
     */
    public function dispatch(string $method, string $uri): void
    {
        $uri = rtrim($uri, '/') ?: '/';

        // Find matching route
        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $route) {
            if (preg_match($pattern, $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $this->runMiddleware($middleware);
                }

                // Call handler
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->handleNotFound();
    }

    /**
     * Run middleware
     */
    private function runMiddleware(string $middleware): void
    {
        $class = "App\\Middleware\\{$middleware}";

        if (class_exists($class)) {
            $instance = new $class($this->app);
            $instance->handle();
        }
    }

    /**
     * Call route handler
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            // Closure handler
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler)) {
            // Controller@method format
            if (str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler);
            } else {
                $class = $handler;
                $method = 'index';
            }

            // Prepend namespace if needed
            if (!str_starts_with($class, 'App\\')) {
                $class = "App\\Controllers\\{$class}";
            }

            if (!class_exists($class)) {
                throw new \RuntimeException("Controller not found: {$class}");
            }

            $controller = new $class($this->app);

            if (!method_exists($controller, $method)) {
                throw new \RuntimeException("Method not found: {$class}@{$method}");
            }

            call_user_func_array([$controller, $method], $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (is_string($class)) {
                if (!str_starts_with($class, 'App\\')) {
                    $class = "App\\Controllers\\{$class}";
                }
                $class = new $class($this->app);
            }

            call_user_func_array([$class, $method], $params);
            return;
        }

        throw new \RuntimeException("Invalid route handler");
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);

        $view = new View($this->app);
        echo $view->render('errors/404', [
            'title' => '404 Not Found'
        ]);
    }

    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            return '#';
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace("{{$key}}", $value, $path);
        }

        return $path;
    }

    /**
     * Get all routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
