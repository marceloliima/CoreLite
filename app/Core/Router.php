<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[$method][] = compact('path', 'handler', 'middleware');
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['path'], $path);
            if ($params === null) continue;

            $this->runMiddleware($route['middleware']);
            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $controller = new $class();
                echo $controller->{$action}(...array_values($params));
            } else {
                echo $handler(...array_values($params));
            }
            return;
        }

        Response::abort(404, 'A página solicitada não existe.');
    }

    private function match(string $route, string $path): ?array
    {
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static fn(array $m): string => '(?P<' . $m[1] . '>\d+)', $route);
        if (preg_match('#^' . $pattern . '$#', $path, $matches) !== 1) return null;
        return array_filter($matches, static fn($k): bool => is_string($k), ARRAY_FILTER_USE_KEY);
    }

    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $item) {
            if ($item === 'auth' && !Auth::check()) {
                Flash::add('warning', 'Entre para continuar.');
                redirect('/login');
            }
            if ($item === 'guest' && Auth::check()) {
                redirect('/');
            }
            if ($item === 'admin' && !Auth::hasRole('admin')) {
                Response::abort(403, 'Acesso restrito a administradores.');
            }
            if ($item === 'manager' && !Auth::hasRole('admin', 'manager')) {
                Response::abort(403, 'Você não possui permissão para esta área.');
            }
        }
    }
}
