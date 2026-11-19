<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middleware = []): void
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddleware = array_merge($groupMiddleware, $m);
            }
        }

        $fullPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        if ($fullPath !== '/') {
            $fullPath = rtrim($fullPath, '/');
        }

        $combinedMiddleware = array_merge($groupMiddleware, $middleware);

        // Convert route pattern with {param} or {param:regex} to regex
        $pattern = preg_replace_callback('#\{([a-zA-Z0-9_]+)(\:[^}]+)?\}#', function ($matches) {
            $param = $matches[1];
            $regex = isset($matches[2]) ? substr($matches[2], 1) : '[^/]+';
            return "(?P<{$param}>{$regex})";
        }, $fullPath);

        // Escape dots e.g. for .js in /embed/{token}.js
        $pattern = '#^' . str_replace('.js', '\.js', $pattern) . '$#u';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $combinedMiddleware,
        ];
    }

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function patch(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function any(string $path, mixed $handler, array $middleware = []): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->addRoute($method, $path, $handler, $middleware);
        }
    }

    public function dispatch(Request $request): Response
    {
        $requestMethod = $request->method();
        $requestPath = $request->path();

        $methodNotAllowed = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $requestPath, $matches)) {
                continue;
            }

            if ($route['method'] !== $requestMethod) {
                $methodNotAllowed = true;
                continue;
            }

            // Extract named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = urldecode($value);
                }
            }
            $request->setRouteParams($params);

            // Execute middleware pipeline
            $pipeline = $route['middleware'];
            return $this->executePipeline($pipeline, $request, function () use ($route, $request) {
                return $this->invokeHandler($route['handler'], $request);
            });
        }

        if ($methodNotAllowed) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => 'Method Not Allowed.'], 405);
            }
            return Response::make('405 Method Not Allowed', 405);
        }

        if ($request->isAjax() || $request->isJson()) {
            return Response::json(['success' => false, 'message' => 'Endpoint Not Found.'], 404);
        }

        return Response::view('public/404', ['title' => 'Page Not Found - ChatBot Creator Studio'], 'layouts/main')
            ->setStatusCode(404);
    }

    private function executePipeline(array $middlewareList, Request $request, callable $destination): Response
    {
        $runner = array_reduce(
            array_reverse($middlewareList),
            function ($next, $middlewareClass) {
                return function ($request) use ($next, $middlewareClass) {
                    $instance = new $middlewareClass();
                    return $instance->handle($request, $next);
                };
            },
            $destination
        );

        return $runner($request);
    }

    private function invokeHandler(mixed $handler, Request $request): Response
    {
        if (is_callable($handler)) {
            $result = call_user_func($handler, $request);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $result = call_user_func([$controller, $method], $request);
        } else {
            throw new \RuntimeException('Invalid route handler specified.');
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::make((string)$result);
    }
}
