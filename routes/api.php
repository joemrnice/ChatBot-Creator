<?php
declare(strict_types=1);

use App\Controllers\ApiController;
use App\Middleware\RateLimitMiddleware;

/** @var \App\Core\Router $router */

$router->group(['prefix' => '/api/v1'], function ($router) {
    // Visitor chatbot messaging
    $router->post('/chat/{publicBotToken}/messages', [ApiController::class, 'sendMessage']);
    $router->post('/chat/{publicBotToken}/lead', [ApiController::class, 'submitLead']);
    $router->post('/chat/feedback', [ApiController::class, 'submitFeedback']);

    // PayPal Webhook endpoint
    $router->post('/paypal/webhook', [ApiController::class, 'paypalWebhook']);
});
