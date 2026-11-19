<?php
declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function json(array $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return new self($encoded ?: '{}', $status, $headers);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        $redirectUrl = str_starts_with($url, 'http') ? $url : url($url);
        return new self('', $status, ['Location' => $redirectUrl]);
    }

    public static function back(): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        return self::redirect($referer);
    }

    public static function view(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): self
    {
        $app = Application::getInstance();
        $viewsDir = $app->basePath('app/Views');
        $viewFile = $viewsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        // Extract variables for view scope
        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If layout specified, render inside layout
        if ($layout !== null) {
            $layoutFile = $viewsDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $layout) . '.php';
            if (file_exists($layoutFile)) {
                $pageContent = $content;
                ob_start();
                include $layoutFile;
                $content = ob_get_clean();
            }
        }

        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
        ];

        return new self($content, 200, $headers);
    }

    public static function download(string $filePath, ?string $downloadName = null, string $contentType = 'application/octet-stream'): self
    {
        if (!file_exists($filePath)) {
            return self::make('File not found', 404);
        }

        $downloadName = $downloadName ?? basename($filePath);
        $fileSize = filesize($filePath);

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . addslashes($downloadName) . '"',
            'Content-Length' => (string)$fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $content = file_get_contents($filePath);
        return new self($content ?: '', 200, $headers);
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->content;
        exit;
    }
}
