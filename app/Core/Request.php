<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array $query;
    private array $post;
    private array $files;
    private array $server;
    private array $headers;
    private ?array $jsonBody = null;
    private array $routeParams = [];

    public function __construct(
        string $method,
        string $uri,
        array $query = [],
        array $post = [],
        array $files = [],
        array $server = [],
        array $headers = []
    ) {
        $this->server = $server;
        $this->method = strtoupper($post['_method'] ?? $server['REQUEST_METHOD'] ?? $method);
        $this->uri = $uri;
        $this->query = $query;
        $this->post = $post;
        $this->files = $files;
        $this->headers = $headers;
        $this->path = $this->parsePath($uri);
    }

    public static function capture(): self
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : self::extractHeaders($_SERVER);

        return new self(
            $method,
            $uri,
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
            $headers
        );
    }

    private static function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public static function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        if ($dir === '/' || $dir === '.') {
            return '';
        }
        return rtrim($dir, '/');
    }

    private function parsePath(string $uri): string
    {
        $parsed = parse_url($uri, PHP_URL_PATH) ?? '/';
        $base = self::basePath();

        if ($base !== '' && str_starts_with($parsed, $base)) {
            $parsed = substr($parsed, strlen($base));
        }

        $parsed = '/' . trim($parsed, '/');
        return $parsed === '//' ? '/' : $parsed;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->jsonBody === null) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $this->jsonBody = is_array($decoded) ? $decoded : [];
        }

        if ($key === null) {
            return $this->jsonBody;
        }
        return $this->jsonBody[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        if (isset($this->query[$key])) {
            return $this->query[$key];
        }
        $json = $this->json();
        if (isset($json[$key])) {
            return $json[$key];
        }
        return $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->post, $this->json());
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $normalized = strtolower($name);
        foreach ($this->headers as $key => $val) {
            if (strtolower($key) === $normalized) {
                return (string)$val;
            }
        }
        return $default;
    }

    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type') ?? '';
        return str_contains(strtolower($contentType), 'application/json');
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest' || $this->isJson();
    }

    public function ip(): string
    {
        return $this->server['HTTP_CF_CONNECTING_IP']
            ?? $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->routeParams;
    }
}
