<?php

namespace App\Core;

/**
 * Router Class
 * Handles URL routing and dispatching to controllers
 */
class Router
{
    private array $routes = [];
    private array $middleware = [];
    private string $prefix = '';
    private array $groupMiddleware = [];

    /**
     * Add GET route
     */
    public function get(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add POST route
     */
    public function post(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Add route that matches any method
     */
    public function any(string $path, $handler, array $middleware = []): self
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $method) {
            $this->addRoute($method, $path, $handler, $middleware);
        }
        return $this;
    }

    /**
     * Create route group
     */
    public function group(array $options, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($options['prefix'])) {
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }

        if (isset($options['middleware'])) {
            $middleware = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;

        return $this;
    }

    /**
     * Add route
     */
    private function addRoute(string $method, string $path, $handler, array $middleware = []): self
    {
        $fullPath = $this->prefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');

        // Combine group middleware with route middleware
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[$method][$fullPath] = [
            'handler' => $handler,
            'middleware' => $allMiddleware
        ];

        return $this;
    }

    /**
     * Register middleware
     */
    public function registerMiddleware(string $name, string $class): self
    {
        $this->middleware[$name] = $class;
        return $this;
    }

    /**
     * Dispatch request to handler
     */
    public function dispatch(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');

        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            $this->executeRoute($this->routes[$method][$path], []);
            return;
        }

        // Try pattern matching
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            $params = $this->matchRoute($routePath, $path);
            if ($params !== false) {
                $this->executeRoute($route, $params);
                return;
            }
        }

        // Not found
        abort(404);
    }

    /**
     * Match route pattern against path
     */
    private function matchRoute(string $routePath, string $path): array|false
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $path, $matches)) {
            // Extract named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return false;
    }

    /**
     * Execute route
     */
    private function executeRoute(array $route, array $params): void
    {
        // Execute middleware
        foreach ($route['middleware'] as $middlewareName) {
            $this->executeMiddleware($middlewareName);
        }

        $handler = $route['handler'];

        // Handle closure
        if ($handler instanceof \Closure) {
            call_user_func_array($handler, $params);
            return;
        }

        // Handle [Controller, method] format
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
        }
        // Handle "Controller@method" format
        elseif (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerClass, $method] = explode('@', $handler);
        }
        // Handle "Controller::method" format
        elseif (is_string($handler) && strpos($handler, '::') !== false) {
            [$controllerClass, $method] = explode('::', $handler);
        } else {
            throw new \RuntimeException('Invalid route handler format');
        }

        // Add namespace if not present
        if (strpos($controllerClass, '\\') === false) {
            $controllerClass = 'App\\Controllers\\' . $controllerClass;
        }

        // Create controller instance and call method
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method {$method} not found in {$controllerClass}");
        }

        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware(string $name): void
    {
        if (!isset($this->middleware[$name])) {
            throw new \RuntimeException("Middleware {$name} not registered");
        }

        $class = $this->middleware[$name];

        if (!class_exists($class)) {
            throw new \RuntimeException("Middleware class {$class} not found");
        }

        $middleware = new $class();

        if (!method_exists($middleware, 'handle')) {
            throw new \RuntimeException("Middleware {$class} must have handle() method");
        }

        $middleware->handle();
    }
}
