<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;

/** @var \App\Core\Router $router */

$router->group(['prefix' => '/admin', 'middleware' => [AdminMiddleware::class]], function ($router) {
    // Control Center Dashboard
    $router->get('/', [AdminController::class, 'index']);

    // User Management
    $router->get('/users', [AdminController::class, 'users']);
    $router->get('/users/{userId}', [AdminController::class, 'userDetail']);
    $router->post('/users/{userId}/update', [AdminController::class, 'updateUser'], [CsrfMiddleware::class]);

    // Chatbot Management
    $router->get('/bots', [AdminController::class, 'bots']);
    $router->post('/bots/status', [AdminController::class, 'toggleBotStatus'], [CsrfMiddleware::class]);

    // Subscription & Plan Management
    $router->get('/plans', [AdminController::class, 'plans']);
    $router->get('/subscriptions', [AdminController::class, 'plans']);
    $router->post('/plans/update', [AdminController::class, 'updatePlan'], [CsrfMiddleware::class]);

    // Financial & Webhooks
    $router->get('/payments', [AdminController::class, 'payments']);
    $router->get('/transactions', [AdminController::class, 'payments']);
    $router->get('/webhooks', [AdminController::class, 'webhooks']);

    // Analytics & Platform Usage
    $router->get('/usage', [AdminController::class, 'usage']);
    $router->get('/analytics', [AdminController::class, 'analytics']);

    // Settings & Configuration
    $router->get('/settings', [AdminController::class, 'settings']);
    $router->post('/settings', [AdminController::class, 'updateSettings'], [CsrfMiddleware::class]);

    // Roles & Access Control
    $router->get('/roles', [AdminController::class, 'roles']);

    // Security & System Diagnostics
    $router->get('/security', [AdminController::class, 'security']);
    $router->get('/system', [AdminController::class, 'system']);
    $router->get('/audit-logs', [AdminController::class, 'auditLogs']);
    $router->get('/support', [AdminController::class, 'support']);
});
