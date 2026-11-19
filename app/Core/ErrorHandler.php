<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(Throwable $e): void
    {
        self::logException($e);

        // Discard any partially buffered view output so error pages render cleanly
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $isDebug = (bool)Application::config('app.debug', false);
        $isAjax = false;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $isAjax = true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            $isAjax = true;
        }

        if (PHP_SAPI === 'cli') {
            echo "Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo $e->getTraceAsString() . "\n";
            exit(1);
        }

        if ($isAjax) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'success' => false,
                'message' => $isDebug ? $e->getMessage() : 'An unexpected server error occurred. Please try again later.',
                'errors' => $isDebug ? ['trace' => $e->getTraceAsString(), 'file' => $e->getFile(), 'line' => $e->getLine()] : []
            ]);
            exit;
        }

        if (!headers_sent()) {
            http_response_code(500);
        }
        if ($isDebug) {
            self::renderDebugError($e);
        } else {
            self::renderProductionError();
        }
        exit;
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::handleException($e);
        }
    }

    private static function logException(Throwable $e): void
    {
        try {
            $app = Application::getInstance();
            $logDir = $app->storagePath('logs');
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log';
            $entry = sprintf(
                "[%s] %s: %s in %s:%d\nStack Trace:\n%s\n\n",
                date('Y-m-d H:i:s'),
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $loggingErr) {
            // Ignore failure to log to file
        }
    }

    private static function renderDebugError(Throwable $e): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Application Error</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px; margin: 0; }
                .container { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid #334155; }
                h1 { color: #f43f5e; margin-top: 0; font-size: 24px; }
                .meta { background: #0f172a; padding: 12px 16px; border-radius: 8px; font-family: monospace; font-size: 14px; margin-bottom: 20px; color: #cbd5e1; border-left: 4px solid #f43f5e; }
                pre { background: #0f172a; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.5; color: #94a3b8; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Application Error (Debug Mode)</h1>
                <div class="meta">
                    <strong><?= htmlspecialchars(get_class($e)) ?></strong>: <?= htmlspecialchars($e->getMessage()) ?><br>
                    <small>in <?= htmlspecialchars($e->getFile()) ?>:<?= $e->getLine() ?></small>
                </div>
                <h3>Stack Trace</h3>
                <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
            </div>
        </body>
        </html>
        <?php
    }

    private static function renderProductionError(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>500 Internal Server Error - ChatBot Creator Studio</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .card { max-width: 480px; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center; border: 1px solid #e2e8f0; }
                h1 { font-size: 28px; margin-bottom: 12px; color: #0f172a; }
                p { color: #64748b; line-height: 1.6; margin-bottom: 24px; }
                a { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; }
            </style>
        </head>
        <body>
            <div class="card">
                <h1>Something Went Wrong</h1>
                <p>We are experiencing a temporary issue. Our engineering team has been notified. Please try again shortly.</p>
                <a href="<?= htmlspecialchars(url('/')) ?>">Return to Homepage</a>
            </div>
        </body>
        </html>
        <?php
    }
}
