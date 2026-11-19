<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Shared Hosting Entry Point
 * Designed for environments where public files live in 'htdocs'
 */

define('CHATBOT_CREATOR_START', microtime(true));

// Locate bootstrap/app.php in parent or adjacent directories
$possiblePaths = [
    dirname(__DIR__) . '/bootstrap/app.php',
    dirname(__DIR__) . '/chatbot-creator/bootstrap/app.php',
];

$bootstrapped = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $app = require_once $path;
        $app->run();
        $bootstrapped = true;
        break;
    }
}

if (!$bootstrapped) {
    http_response_code(500);
    echo "<h1>Configuration Error</h1><p>Could not locate the application core. Please verify your deployment path structure.</p>";
    exit;
}
