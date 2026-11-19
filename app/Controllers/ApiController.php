<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Application;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Services\ChatbotService;
use App\Services\ConversationService;
use App\Services\PayPalService;

class ApiController
{
    private ChatbotService $chatbotService;
    private ConversationService $conversationService;
    private PayPalService $payPalService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
        $this->conversationService = new ConversationService();
        $this->payPalService = new PayPalService();
    }

    public function sendMessage(Request $request): Response
    {
        $token = (string)$request->param('publicBotToken');
        $bot = $this->chatbotService->getBotByPublicToken($token);

        if (!$bot) {
            return Response::json(['success' => false, 'message' => 'Chatbot not found or inactive.'], 404, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        // Domain restriction verification
        if ($bot['visibility'] === 'domain_restricted') {
            $origin = $request->header('Origin') ?? $request->header('Referer');
            if ($origin) {
                $host = parse_url($origin, PHP_URL_HOST);
                $allowed = Database::fetchOne(
                    "SELECT id FROM chatbot_allowed_domains WHERE chatbot_id = :id AND (:host LIKE domain OR domain = :exact)",
                    ['id' => $bot['id'], 'host' => $host, 'exact' => $host]
                );
                if (!$allowed) {
                    return Response::json(['success' => false, 'message' => 'Domain access forbidden.'], 403, [
                        'Access-Control-Allow-Origin' => '*',
                    ]);
                }
            }
        }

        // Rate limiting: 30 messages per minute per IP
        $rateLimitKey = 'api_chat:' . $request->ip() . ':' . $token;
        if (!Security::checkRateLimit($rateLimitKey, 30, 60)) {
            return Response::json(['success' => false, 'message' => 'Too many messages. Please slow down.'], 429, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $input = $request->json() ?: $request->all();
        $message = trim((string)($input['message'] ?? ''));
        $sessionId = trim((string)($input['session_id'] ?? 'sid_default'));

        if ($message === '') {
            return Response::json(['success' => false, 'message' => 'Message cannot be empty.'], 422, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $visitorMeta = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $input['referrer'] ?? $request->header('Referer') ?? '',
        ];

        try {
            $reply = $this->conversationService->processMessage($bot, $sessionId, $message, $visitorMeta);
            return Response::json([
                'success' => true,
                'message' => 'Message processed.',
                'data' => $reply,
            ], 200, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        } catch (\Throwable $e) {
            return Response::json(['success' => false, 'message' => $e->getMessage()], 500, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
    }

    public function submitLead(Request $request): Response
    {
        $input = $request->json() ?: $request->all();
        $conversationId = (int)($input['conversation_id'] ?? 0);

        if (!$conversationId) {
            return Response::json(['success' => false, 'message' => 'Missing conversation ID.'], 422, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $success = $this->conversationService->recordLead($conversationId, $input);
        return Response::json([
            'success' => $success,
            'message' => $success ? 'Thank you! Your contact information has been received.' : 'Failed to save contact info.',
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function submitFeedback(Request $request): Response
    {
        $input = $request->json() ?: $request->all();
        $messageId = (int)($input['message_id'] ?? 0);
        $rating = (string)($input['rating'] ?? '');
        $comment = $input['comment'] ?? null;

        if (!$messageId || !in_array($rating, ['up', 'down'], true)) {
            return Response::json(['success' => false, 'message' => 'Invalid feedback parameters.'], 422, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $success = $this->conversationService->recordFeedback($messageId, $rating, $comment);
        return Response::json([
            'success' => $success,
            'message' => 'Feedback received. Thank you!',
        ], 200, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function paypalWebhook(Request $request): Response
    {
        $rawBody = file_get_contents('php://input');
        if (empty($rawBody)) {
            return Response::json(['success' => false, 'message' => 'Empty webhook payload'], 400);
        }

        $headers = function_exists('getallheaders') ? getallheaders() : $_SERVER;

        // Verify signature if webhook ID configured
        $webhookId = Application::config('paypal.webhook_id');
        if (!empty($webhookId)) {
            $isValid = $this->payPalService->verifyWebhookSignature($headers, $rawBody);
            if (!$isValid) {
                return Response::json(['success' => false, 'message' => 'Invalid PayPal webhook signature'], 400);
            }
        }

        $result = $this->payPalService->handleWebhookEvent($rawBody, $headers);
        return Response::json($result, $result['success'] ? 200 : 500);
    }
}
