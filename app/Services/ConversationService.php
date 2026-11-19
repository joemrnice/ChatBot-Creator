<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use App\Providers\FallbackChatProvider;
use App\Providers\OpenAICompatibleProvider;
use RuntimeException;

class ConversationService
{
    private ChatbotService $chatbotService;
    private SubscriptionService $subscriptionService;
    private UsageService $usageService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
        $this->subscriptionService = new SubscriptionService();
        $this->usageService = new UsageService();
    }

    public function getOrCreateConversation(array $bot, string $sessionId, array $visitorMeta = []): array
    {
        $conv = Database::fetchOne(
            "SELECT * FROM conversations WHERE chatbot_id = :bot_id AND session_id = :sid LIMIT 1",
            ['bot_id' => $bot['id'], 'sid' => $sessionId]
        );

        if ($conv) {
            return $conv;
        }

        $uuid = Security::generateUuid();
        $convId = Database::insert('conversations', [
            'uuid' => $uuid,
            'chatbot_id' => $bot['id'],
            'session_id' => $sessionId,
            'visitor_ip' => $visitorMeta['ip'] ?? '127.0.0.1',
            'visitor_user_agent' => substr($visitorMeta['user_agent'] ?? '', 0, 500),
            'referrer_url' => substr($visitorMeta['referrer'] ?? '', 0, 500),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Increment chatbot conversation counter & owner usage counter
        Database::query("UPDATE chatbots SET conversations_count = conversations_count + 1 WHERE id = :id", ['id' => $bot['id']]);
        $this->usageService->recordUsage((int)$bot['user_id'], 'conversations_count', 1);

        return Database::fetchOne("SELECT * FROM conversations WHERE id = :id", ['id' => $convId]);
    }

    public function processMessage(array $bot, string $sessionId, string $userMessage, array $visitorMeta = []): array
    {
        $userMessage = trim($userMessage);
        if ($userMessage === '') {
            throw new RuntimeException('Message cannot be empty.');
        }

        // 1. Check if owner has message quota
        $ownerId = (int)$bot['user_id'];
        if (!$this->subscriptionService->canSendMessage($ownerId)) {
            return [
                'sender' => 'bot',
                'content' => "This chatbot has reached its monthly message limit. Please contact the site owner.",
                'source' => 'system_limit',
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        // 2. Resolve Conversation
        $conversation = $this->getOrCreateConversation($bot, $sessionId, $visitorMeta);

        // 3. Save User Message
        Database::insert('messages', [
            'conversation_id' => $conversation['id'],
            'sender_type' => 'user',
            'content' => $userMessage,
            'response_source' => 'user_input',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 4. Retrieve Knowledge Context
        $knowledgeMatches = $this->chatbotService->retrieveKnowledge((int)$bot['id'], $userMessage, 4);

        // 5. Select Provider
        $providerType = $bot['ai_provider'] ?? 'fallback';
        $systemInstructions = !empty($bot['system_instructions'])
            ? $bot['system_instructions']
            : "You are {$bot['name']}, a helpful assistant. Provide polite, accurate answers.";

        $options = [
            'welcome_message' => $bot['welcome_message'],
            'fallback_message' => $bot['fallback_message'],
            'temperature' => (float)($bot['ai_temperature'] ?? 0.7),
            'model' => $bot['ai_model'] ?? 'gpt-4o-mini',
        ];

        // Decrypt custom API key if present and authorized
        if (!empty($bot['ai_custom_api_key'])) {
            $decryptedKey = Security::decrypt($bot['ai_custom_api_key']);
            if ($decryptedKey) {
                $options['api_key'] = $decryptedKey;
            }
        }

        // Fetch recent conversation history
        $recentMessages = Database::fetchAll(
            "SELECT sender_type, content FROM messages WHERE conversation_id = :cid ORDER BY id DESC LIMIT 8",
            ['cid' => $conversation['id']]
        );
        $recentMessages = array_reverse($recentMessages);

        if ($providerType === 'openai') {
            $provider = new OpenAICompatibleProvider();
        } else {
            $provider = new FallbackChatProvider();
        }

        $response = $provider->generateResponse(
            $systemInstructions,
            $recentMessages,
            $userMessage,
            $knowledgeMatches,
            $options
        );

        // 6. Save Bot Reply
        $replyId = Database::insert('messages', [
            'conversation_id' => $conversation['id'],
            'sender_type' => 'bot',
            'content' => $response['content'],
            'matched_knowledge_id' => $response['matched_knowledge_id'] ?? null,
            'response_source' => $response['source'] ?? 'knowledge',
            'ai_model_used' => $response['model'] ?? null,
            'tokens_used' => $response['tokens'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // If matched knowledge record, increment hit count
        if (!empty($response['matched_knowledge_id'])) {
            Database::query(
                "UPDATE chatbot_knowledge SET hits_count = hits_count + 1 WHERE id = :id",
                ['id' => $response['matched_knowledge_id']]
            );
        }

        // 7. Update usage counters
        Database::query(
            "UPDATE conversations SET messages_count = messages_count + 2, updated_at = NOW() WHERE id = :id",
            ['id' => $conversation['id']]
        );
        $this->usageService->recordUsage($ownerId, 'messages_count', 1);

        return [
            'id' => $replyId,
            'message_id' => $replyId,
            'sender' => 'bot',
            'content' => $response['content'],
            'source' => $response['source'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function recordLead(int $conversationId, array $contactInfo): bool
    {
        $update = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $name = $contactInfo['visitor_name'] ?? $contactInfo['name'] ?? null;
        $email = $contactInfo['visitor_email'] ?? $contactInfo['email'] ?? null;
        $phone = $contactInfo['visitor_phone'] ?? $contactInfo['phone'] ?? null;

        if (!empty($name)) $update['visitor_name'] = Security::sanitizeString((string)$name);
        if (!empty($email)) $update['visitor_email'] = filter_var((string)$email, FILTER_VALIDATE_EMAIL) ? (string)$email : null;
        if (!empty($phone)) $update['visitor_phone'] = Security::sanitizeString((string)$phone);

        return Database::update('conversations', $update, 'id = :id', ['id' => $conversationId]) >= 0;
    }

    public function recordFeedback(int $messageId, string $rating, ?string $comment = null): bool
    {
        if (!in_array($rating, ['up', 'down'], true)) {
            return false;
        }

        Database::query(
            "INSERT INTO conversation_feedback (message_id, rating, comment, created_at)
             VALUES (:mid, :r, :c, NOW())
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)",
            [
                'mid' => $messageId,
                'r' => $rating,
                'c' => $comment ? Security::sanitizeString($comment) : null,
            ]
        );

        return true;
    }
}
