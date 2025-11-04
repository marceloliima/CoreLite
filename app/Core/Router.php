<?php

namespace App\Core;

use App\Core\FlashMessage;

class Router
{
    private array $routes = [];
    private const PARAM_REGEX = '[a-zA-Z0-9_\-\%\.]+';
    private array $currentGroup = [];

    /**
     * Registra uma rota GET
     */
    public function get(string $uri, callable|array $handler): self
    {
        return $this->add('GET', $uri, $handler);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $uri, callable|array $handler): self
    {
        return $this->add('POST', $uri, $handler);
    }

    /**
     * Registra uma rota PUT
     */
    public function put(string $uri, callable|array $handler): self
    {
        return $this->add('PUT', $uri, $handler);
    }

    /**
     * Registra uma rota DELETE
     */
    public function delete(string $uri, callable|array $handler): self
    {
        return $this->add('DELETE', $uri, $handler);
    }

    /**
     * Registra uma rota com método específico
     */
    private function add(string $method, string $uri, callable|array $handler): self
    {
        $method = strtoupper($method);
        $this->validateHandler($handler);

        $prefix = $this->currentGroup['prefix'] ?? '';
        $uri = '/' . trim($prefix . '/' . trim($uri, '/'), '/');

        $this->routes[$method][$uri] = [
            'handler' => $handler,
            'middleware' => $this->currentGroup['middleware'] ?? []
        ];

        return $this;
    }

    /**
     * Agrupa rotas com prefixo ou middleware
     */
    public function group(array $attributes, callable $callback): void
    {
        $parentGroup = $this->currentGroup;
        $this->currentGroup = array_merge($parentGroup, $attributes);
        $callback($this);
        $this->currentGroup = $parentGroup;
    }

    /**
     * Valida handler da rota
     */
    private function validateHandler(callable|array $handler): void
    {
        if (is_array($handler)) {
            if (count($handler) !== 2) {
                throw new \InvalidArgumentException('Handler deve ser [Controller, method]');
            }
            [$class, $method] = $handler;
            if (!class_exists($class) || !method_exists($class, $method)) {
                throw new \InvalidArgumentException("Controller ou método não encontrado: {$class}::{$method}");
            }
        } elseif (!is_callable($handler)) {
            throw new \InvalidArgumentException('Handler inválido');
        }
    }

    /**
     * Executa a rota correspondente
     */
    public function dispatch(string $uri, string $method): void
    {
        $method = strtoupper($method);
        $uri = '/' . trim(parse_url($uri, PHP_URL_PATH), '/');

        foreach ($this->routes[$method] ?? [] as $route => $info) {
            $pattern = preg_replace('#\{([\w]+)\}#', '(?P<$1>' . self::PARAM_REGEX . ')', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                // Extrai apenas parâmetros nomeados
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handler = $info['handler'];

                // Middleware
                foreach ($info['middleware'] as $mw) {
                    $mw($params); // cada middleware recebe params (ex: auth, csrf)
                }

                // Executa controller ou callback
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    echo $controller->{$methodName}(...array_values($params));
                    return;
                }

                echo call_user_func_array($handler, array_values($params));
                return;
            }
        }

        // Rota não encontrada
        http_response_code(404);
        FlashMessage::definir('erro', '404 - Rota não encontrada');
        header('Location: /');
        exit;
    }
}
