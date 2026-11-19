<?php
declare(strict_types=1);

// Require Composer Autoloader
$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
} else {
    // Fallback simple PSR-4 autoloader in case composer dump-autoload was not run
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = dirname(__DIR__) . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
    require_once dirname(__DIR__) . '/app/Helpers/functions.php';
}

$basePath = dirname(__DIR__);
$app = new \App\Core\Application($basePath);

// Register Error Handling
\App\Core\ErrorHandler::register();

// Load Routes
$router = $app->getRouter();

$webRoutes = $basePath . '/routes/web.php';
if (file_exists($webRoutes)) {
    require $webRoutes;
}

$apiRoutes = $basePath . '/routes/api.php';
if (file_exists($apiRoutes)) {
    require $apiRoutes;
}

$adminRoutes = $basePath . '/routes/admin.php';
if (file_exists($adminRoutes)) {
    require $adminRoutes;
}

return $app;
