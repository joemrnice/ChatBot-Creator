<?php
declare(strict_types=1);

namespace App\Core;

class Application
{
    private static ?self $instance = null;
    private static array $env = [];
    private static array $config = [];
    private string $basePath;
    private Router $router;

    public function __construct(string $basePath)
    {
        self::$instance = $this;
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $this->loadEnvironment();
        $this->loadConfigurations();
        $this->setTimezone();
        $this->router = new Router();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been initialized.');
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envFile = $this->basePath . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Strip surrounding quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                self::$env[$key] = $value;
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private function loadConfigurations(): void
    {
        $configDir = $this->basePath . DIRECTORY_SEPARATOR . 'config';
        if (!is_dir($configDir)) {
            return;
        }

        foreach (glob($configDir . DIRECTORY_SEPARATOR . '*.php') as $file) {
            $key = basename($file, '.php');
            self::$config[$key] = require $file;
        }
    }

    private function setTimezone(): void
    {
        $tz = self::config('app.timezone', 'UTC');
        date_default_timezone_set($tz);
    }

    public static function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$env)) {
            $val = self::$env[$key];
            return match (strtolower((string)$val)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $val,
            };
        }
        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }
        return $default;
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $current = self::$config;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }

    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public-assets' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        Session::start();
        $request = Request::capture();
        $response = $this->router->dispatch($request);
        $response->send();
    }
}
