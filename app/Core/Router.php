<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal regex router with {param} placeholders.
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, params:array<int,string>, handler:callable|array}> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $params = [];
        $parts = preg_split('#(\{[a-zA-Z_][a-zA-Z0-9_]*\})#', '/' . trim($pattern, '/'), -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        $regex = '';
        foreach ($parts as $part) {
            if (preg_match('#^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$#', $part, $m) === 1) {
                $params[] = $m[1];
                $regex .= '([^/]+)';
            } else {
                $regex .= preg_quote($part, '#');
            }
        }

        $this->routes[] = [
            'method'  => strtoupper($method),
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path   = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            $methodMatches = $route['method'] === $method
                || ($route['method'] === 'GET' && $method === 'HEAD');

            if (!$methodMatches) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $index => $name) {
                $args[] = urldecode($matches[$index + 1]);
            }

            $handler = $route['handler'];
            if (is_array($handler) && is_string($handler[0])) {
                [$class, $action] = $handler;
                (new $class())->{$action}(...$args);
            } else {
                $handler(...$args);
            }
            return;
        }

        http_response_code(404);
        (new \App\Controllers\ErrorController())->notFound();
    }
}
