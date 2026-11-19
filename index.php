<?php
declare(strict_types=1);

/**
 * ChatBot Creator Studio - Application Entry Point
 * Native PHP 8.3+ Multi-Tenant SaaS
 */

define('CHATBOT_CREATOR_START', microtime(true));

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->run();
