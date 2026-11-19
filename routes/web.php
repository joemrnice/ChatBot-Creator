<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\BotController;
use App\Controllers\EmbedController;
use App\Controllers\ExportController;
use App\Controllers\HomeController;
use App\Controllers\ImportController;
use App\Controllers\OAuthController;
use App\Controllers\StudioController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\SubscriptionMiddleware;

/** @var \App\Core\Router $router */

// ------------------------------------------------------------------------------
// 1. PUBLIC MARKETING WEBSITE
// ------------------------------------------------------------------------------
$router->get('/', [HomeController::class, 'index']);
$router->get('/features', [HomeController::class, 'features']);
$router->get('/how-it-works', [HomeController::class, 'howItWorks']);
$router->get('/pricing', [HomeController::class, 'pricing']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/contact', [HomeController::class, 'contact']);
$router->get('/faq', [HomeController::class, 'faq']);
$router->get('/privacy', [HomeController::class, 'privacy']);
$router->get('/terms', [HomeController::class, 'terms']);

// ------------------------------------------------------------------------------
// 2. PUBLIC CHATBOT ACCESS & EMBEDS
// ------------------------------------------------------------------------------
$router->get('/b/{botSlug}', [EmbedController::class, 'landing']);
$router->get('/embed/{publicBotToken}.js', [EmbedController::class, 'script']);
$router->get('/embed/{publicBotToken}/config', [EmbedController::class, 'config']);

// ------------------------------------------------------------------------------
// 3. AUTHENTICATION & SOCIAL LOGIN
// ------------------------------------------------------------------------------
$router->group(['middleware' => [GuestMiddleware::class]], function ($router) {
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);

    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);

    $router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password', [AuthController::class, 'sendResetLink'], [CsrfMiddleware::class]);

    $router->get('/reset-password', [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class]);

    // OAuth Redirects
    $router->get('/auth/{provider}', [OAuthController::class, 'redirect']);
    $router->get('/auth/{provider}/callback', [OAuthController::class, 'callback']);
});

$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// ------------------------------------------------------------------------------
// 4. PERSONAL USER STUDIO (PROTECTED)
// ------------------------------------------------------------------------------
$router->group(['prefix' => '/studio', 'middleware' => [AuthMiddleware::class]], function ($router) {
    // Dashboard & Overview
    $router->get('/', [StudioController::class, 'index']);
    $router->get('/usage', [StudioController::class, 'usage']);
    $router->get('/templates', [StudioController::class, 'templates']);

    // Profile & Security
    $router->get('/profile', [StudioController::class, 'profile']);
    $router->post('/profile', [StudioController::class, 'updateProfile'], [CsrfMiddleware::class]);
    $router->get('/security', [StudioController::class, 'security']);
    $router->post('/security/password', [StudioController::class, 'updatePassword'], [CsrfMiddleware::class]);
    $router->get('/notifications', [StudioController::class, 'notifications']);

    // Billing & Subscriptions
    $router->get('/billing', [BillingController::class, 'index']);
    $router->get('/subscription', [BillingController::class, 'index']);
    $router->post('/billing/subscribe/{planSlug}', [BillingController::class, 'subscribe'], [CsrfMiddleware::class]);
    $router->get('/billing/return', [BillingController::class, 'return']);
    $router->post('/billing/cancel', [BillingController::class, 'cancel'], [CsrfMiddleware::class]);

    // Chatbot Management
    $router->get('/bots', [BotController::class, 'index']);
    $router->get('/bots/create', [BotController::class, 'create']);
    $router->post('/bots', [BotController::class, 'store'], [CsrfMiddleware::class, SubscriptionMiddleware::class]);

    $router->get('/bots/{botId}', [BotController::class, 'show']);
    $router->get('/bots/{botId}/builder', [BotController::class, 'builder']);
    $router->post('/bots/{botId}/builder', [BotController::class, 'updateBuilder'], [CsrfMiddleware::class]);

    $router->get('/bots/{botId}/knowledge', [BotController::class, 'knowledge']);
    $router->post('/bots/{botId}/knowledge', [BotController::class, 'storeKnowledge'], [CsrfMiddleware::class]);
    $router->post('/bots/{botId}/knowledge/delete', [BotController::class, 'deleteKnowledge'], [CsrfMiddleware::class]);

    $router->get('/bots/{botId}/appearance', [BotController::class, 'appearance']);
    $router->post('/bots/{botId}/appearance', [BotController::class, 'updateBuilder'], [CsrfMiddleware::class]);

    $router->get('/bots/{botId}/behavior', [BotController::class, 'behavior']);
    $router->post('/bots/{botId}/behavior', [BotController::class, 'updateBuilder'], [CsrfMiddleware::class]);

    $router->get('/bots/{botId}/embed', [BotController::class, 'embed']);
    $router->get('/bots/{botId}/integrations', [BotController::class, 'embed']);
    $router->post('/bots/{botId}/embed', [BotController::class, 'updateBuilder'], [CsrfMiddleware::class]);

    $router->get('/bots/{botId}/analytics', [BotController::class, 'analytics']);
    $router->get('/bots/{botId}/conversations', [BotController::class, 'conversations']);
    $router->get('/bots/{botId}/settings', [BotController::class, 'settings']);
    $router->post('/bots/{botId}/settings', [BotController::class, 'updateBuilder'], [CsrfMiddleware::class]);
    $router->post('/bots/{botId}/delete', [BotController::class, 'delete'], [CsrfMiddleware::class]);

    // Import Management
    $router->get('/bots/{botId}/import', [ImportController::class, 'show']);
    $router->post('/bots/{botId}/import/upload', [ImportController::class, 'upload'], [CsrfMiddleware::class]);
    $router->post('/bots/{botId}/import/confirm', [ImportController::class, 'confirm'], [CsrfMiddleware::class]);
    $router->get('/bots/{botId}/import/sample-json', [ImportController::class, 'sampleJson']);
    $router->get('/bots/{botId}/import/sample-csv', [ImportController::class, 'sampleCsv']);

    // Export Management
    $router->get('/bots/{botId}/export/json', [ExportController::class, 'json']);
    $router->get('/bots/{botId}/export/csv', [ExportController::class, 'csv']);
    $router->get('/bots/{botId}/export/conversations', [ExportController::class, 'conversations']);
    $router->get('/bots/{botId}/export/zip', [ExportController::class, 'zip']);
});
