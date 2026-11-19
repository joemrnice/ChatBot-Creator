<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use RuntimeException;

class ChatbotService
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
    }

    public function getBotsForUser(int $userId): array
    {
        $sql = "SELECT c.*, 
                       cfg.theme, cfg.primary_color, cfg.header_title,
                       (SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = c.id) as knowledge_count
                FROM chatbots c
                LEFT JOIN chatbot_configurations cfg ON cfg.chatbot_id = c.id
                WHERE c.user_id = :user_id
                ORDER BY c.updated_at DESC";

        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    public function getBotById(int $botId, int $userId): ?array
    {
        $sql = "SELECT c.*, 
                       cfg.personality, cfg.tone, cfg.response_style, cfg.welcome_message, cfg.fallback_message,
                       cfg.system_instructions, cfg.suggested_questions, cfg.contact_capture_enabled,
                       cfg.contact_capture_prompt, cfg.handoff_enabled, cfg.handoff_email,
                       cfg.theme, cfg.primary_color, cfg.secondary_color, cfg.header_title, cfg.header_subtitle,
                       cfg.avatar_url, cfg.widget_position, cfg.widget_border_radius, cfg.hide_watermark,
                       cfg.custom_css, cfg.ai_provider, cfg.ai_model, cfg.ai_temperature, cfg.ai_custom_api_key
                FROM chatbots c
                LEFT JOIN chatbot_configurations cfg ON cfg.chatbot_id = c.id
                WHERE c.id = :id AND c.user_id = :user_id
                LIMIT 1";

        $bot = Database::fetchOne($sql, ['id' => $botId, 'user_id' => $userId]);
        if (!$bot) {
            return null;
        }

        // Parse JSON fields
        if (!empty($bot['suggested_questions'])) {
            $bot['suggested_questions_array'] = json_decode($bot['suggested_questions'], true) ?: [];
        } else {
            $bot['suggested_questions_array'] = [];
        }

        // Fetch allowed domains
        $domains = Database::fetchAll(
            "SELECT domain FROM chatbot_allowed_domains WHERE chatbot_id = :bot_id",
            ['bot_id' => $botId]
        );
        $bot['allowed_domains'] = array_column($domains, 'domain');

        return $bot;
    }

    public function getBotByPublicToken(string $token): ?array
    {
        $sql = "SELECT c.*, 
                       cfg.personality, cfg.tone, cfg.response_style, cfg.welcome_message, cfg.fallback_message,
                       cfg.system_instructions, cfg.suggested_questions, cfg.contact_capture_enabled,
                       cfg.contact_capture_prompt, cfg.handoff_enabled, cfg.handoff_email,
                       cfg.theme, cfg.primary_color, cfg.secondary_color, cfg.header_title, cfg.header_subtitle,
                       cfg.avatar_url, cfg.widget_position, cfg.widget_border_radius, cfg.hide_watermark,
                       cfg.custom_css, cfg.ai_provider, cfg.ai_model, cfg.ai_temperature, cfg.ai_custom_api_key,
                       u.plan_id, p.allow_custom_branding
                FROM chatbots c
                JOIN users u ON u.id = c.user_id
                LEFT JOIN plans p ON p.id = u.plan_id
                LEFT JOIN chatbot_configurations cfg ON cfg.chatbot_id = c.id
                WHERE c.public_token = :token AND c.status != 'suspended'
                LIMIT 1";

        $bot = Database::fetchOne($sql, ['token' => $token]);
        if (!$bot) {
            return null;
        }

        if (!empty($bot['suggested_questions'])) {
            $bot['suggested_questions_array'] = json_decode($bot['suggested_questions'], true) ?: [];
        } else {
            $bot['suggested_questions_array'] = [];
        }

        return $bot;
    }

    public function getBotBySlug(string $slug): ?array
    {
        $sql = "SELECT c.*, 
                       cfg.personality, cfg.tone, cfg.response_style, cfg.welcome_message, cfg.fallback_message,
                       cfg.suggested_questions, cfg.theme, cfg.primary_color, cfg.header_title, cfg.header_subtitle,
                       cfg.avatar_url, cfg.widget_border_radius, cfg.hide_watermark, cfg.custom_css,
                       p.allow_custom_branding
                FROM chatbots c
                JOIN users u ON u.id = c.user_id
                LEFT JOIN plans p ON p.id = u.plan_id
                LEFT JOIN chatbot_configurations cfg ON cfg.chatbot_id = c.id
                WHERE c.slug = :slug AND c.status = 'published'
                LIMIT 1";

        $bot = Database::fetchOne($sql, ['slug' => $slug]);
        if (!$bot) {
            return null;
        }

        if (!empty($bot['suggested_questions'])) {
            $bot['suggested_questions_array'] = json_decode($bot['suggested_questions'], true) ?: [];
        } else {
            $bot['suggested_questions_array'] = [];
        }

        return $bot;
    }

    public function createBot(int $userId, array $data): int
    {
        if (!$this->subscriptionService->canCreateChatbot($userId)) {
            throw new RuntimeException('You have reached the maximum chatbot creation limit for your subscription plan.');
        }

        return Database::transaction(function () use ($userId, $data) {
            $name = trim($data['name']);
            $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)) ?: 'bot';
            $baseSlug = trim($baseSlug, '-');

            // Ensure unique slug
            $slug = $baseSlug;
            $counter = 1;
            while (Database::fetchValue("SELECT id FROM chatbots WHERE slug = :s", ['s' => $slug])) {
                $slug = $baseSlug . '-' . (++$counter);
            }

            $uuid = Security::generateUuid();
            $publicToken = Security::generateBotToken('pub_');

            $botId = Database::insert('chatbots', [
                'uuid' => $uuid,
                'user_id' => $userId,
                'name' => $name,
                'slug' => $slug,
                'description' => $data['description'] ?? '',
                'category' => $data['category'] ?? 'business',
                'language' => $data['language'] ?? 'en',
                'public_token' => $publicToken,
                'status' => 'draft',
                'visibility' => 'public',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Insert Configuration
            $suggested = self::formatSuggestedQuestions($data['suggested_questions'] ?? null);

            Database::insert('chatbot_configurations', [
                'chatbot_id' => $botId,
                'personality' => $data['personality'] ?? 'friendly',
                'tone' => $data['tone'] ?? 'helpful',
                'response_style' => $data['response_style'] ?? 'concise',
                'welcome_message' => $data['welcome_message'] ?? "Hello! How can I assist you today?",
                'fallback_message' => $data['fallback_message'] ?? "I do not have enough information to answer that question.",
                'system_instructions' => $data['system_instructions'] ?? "You are a professional assistant.",
                'suggested_questions' => $suggested,
                'theme' => $data['theme'] ?? 'light',
                'primary_color' => $data['primary_color'] ?? '#2563eb',
                'header_title' => $name,
                'header_subtitle' => 'Typically replies instantly',
                'widget_position' => $data['widget_position'] ?? 'bottom-right',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // If template_id provided, copy sample knowledge
            if (!empty($data['template_id'])) {
                $template = Database::fetchOne("SELECT * FROM chatbot_templates WHERE id = :id", ['id' => $data['template_id']]);
                if ($template && !empty($template['sample_knowledge'])) {
                    $items = json_decode($template['sample_knowledge'], true);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            Database::insert('chatbot_knowledge', [
                                'chatbot_id' => $botId,
                                'type' => 'faq',
                                'question' => $item['question'] ?? '',
                                'answer' => $item['answer'] ?? '',
                                'tags' => $item['tags'] ?? '',
                                'source' => 'template',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }

            return $botId;
        });
    }

    public function updateBot(int $botId, int $userId, array $data): void
    {
        // Enforce ownership
        $bot = $this->getBotById($botId, $userId);
        if (!$bot) {
            throw new RuntimeException('Chatbot not found or access denied.');
        }

        Database::transaction(function () use ($botId, $userId, $data) {
            $botUpdate = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (isset($data['name'])) $botUpdate['name'] = trim($data['name']);
            if (isset($data['description'])) $botUpdate['description'] = $data['description'];
            if (isset($data['category'])) $botUpdate['category'] = $data['category'];
            if (isset($data['language'])) $botUpdate['language'] = $data['language'];
            if (isset($data['status']) && in_array($data['status'], ['draft', 'published'], true)) {
                $botUpdate['status'] = $data['status'];
            }
            if (isset($data['visibility'])) $botUpdate['visibility'] = $data['visibility'];

            Database::update('chatbots', $botUpdate, 'id = :id AND user_id = :uid', ['id' => $botId, 'uid' => $userId]);

            $cfgUpdate = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $cfgFields = [
                'personality', 'tone', 'response_style', 'welcome_message', 'fallback_message',
                'system_instructions', 'contact_capture_prompt', 'handoff_email',
                'theme', 'primary_color', 'secondary_color', 'header_title', 'header_subtitle',
                'avatar_url', 'widget_position', 'custom_css', 'ai_provider', 'ai_model'
            ];

            foreach ($cfgFields as $f) {
                if (isset($data[$f])) {
                    $cfgUpdate[$f] = $data[$f];
                }
            }

            if (isset($data['widget_border_radius'])) {
                $cfgUpdate['widget_border_radius'] = (int)$data['widget_border_radius'];
            }
            if (isset($data['contact_capture_enabled'])) {
                $cfgUpdate['contact_capture_enabled'] = $data['contact_capture_enabled'] ? 1 : 0;
            }
            if (isset($data['handoff_enabled'])) {
                $cfgUpdate['handoff_enabled'] = $data['handoff_enabled'] ? 1 : 0;
            }
            if (isset($data['hide_watermark']) && $this->subscriptionService->canUseCustomBranding($userId)) {
                $cfgUpdate['hide_watermark'] = $data['hide_watermark'] ? 1 : 0;
            }
            if (isset($data['ai_temperature'])) {
                $cfgUpdate['ai_temperature'] = (float)$data['ai_temperature'];
            }
            if (isset($data['suggested_questions'])) {
                $cfgUpdate['suggested_questions'] = self::formatSuggestedQuestions($data['suggested_questions']);
            }
            if (!empty($data['ai_custom_api_key']) && $this->subscriptionService->canUseAiKeys($userId)) {
                $cfgUpdate['ai_custom_api_key'] = Security::encrypt($data['ai_custom_api_key']);
            }

            Database::update('chatbot_configurations', $cfgUpdate, 'chatbot_id = :id', ['id' => $botId]);

            // Update allowed domains if provided
            if (isset($data['allowed_domains']) && is_array($data['allowed_domains'])) {
                Database::delete('chatbot_allowed_domains', 'chatbot_id = :id', ['id' => $botId]);
                foreach ($data['allowed_domains'] as $d) {
                    $d = trim(strtolower($d));
                    if (!empty($d)) {
                        Database::insert('chatbot_allowed_domains', [
                            'chatbot_id' => $botId,
                            'domain' => $d,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        });
    }

    public function deleteBot(int $botId, int $userId): bool
    {
        $bot = $this->getBotById($botId, $userId);
        if (!$bot) {
            return false;
        }

        Database::delete('chatbots', 'id = :id AND user_id = :uid', ['id' => $botId, 'uid' => $userId]);
        return true;
    }

    /**
     * Hybrid Knowledge Retrieval for Visitor Queries
     */
    public function retrieveKnowledge(int $chatbotId, string $query, int $limit = 4): array
    {
        $query = trim($query);
        if (empty($query)) {
            return [];
        }

        $intLimit = (int)$limit;

        // Try FULLTEXT search first
        try {
            $sql = "SELECT id, question, answer, title, content, category, tags,
                           MATCH(question, answer, title, content) AGAINST(:q1 IN NATURAL LANGUAGE MODE) as score
                    FROM chatbot_knowledge
                    WHERE chatbot_id = :bot_id AND is_active = 1
                      AND MATCH(question, answer, title, content) AGAINST(:q2 IN NATURAL LANGUAGE MODE)
                    ORDER BY score DESC
                    LIMIT {$intLimit}";

            $results = Database::fetchAll($sql, [
                'bot_id' => $chatbotId,
                'q1' => $query,
                'q2' => $query,
            ]);

            if (!empty($results)) {
                return $results;
            }
        } catch (\Throwable $e) {
            // Fulltext fallback if table does not support fulltext or error
        }

        // Tokenized keyword search
        $words = array_filter(explode(' ', strtolower($query)), fn($w) => strlen($w) >= 3);
        if (empty($words)) {
            $words = [$query];
        }

        $conditions = [];
        $params = ['bot_id' => $chatbotId];
        foreach ($words as $idx => $word) {
            $conditions[] = "(question LIKE :q_{$idx} OR answer LIKE :a_{$idx} OR content LIKE :c_{$idx} OR tags LIKE :t_{$idx})";
            $params["q_{$idx}"] = "%{$word}%";
            $params["a_{$idx}"] = "%{$word}%";
            $params["c_{$idx}"] = "%{$word}%";
            $params["t_{$idx}"] = "%{$word}%";
        }

        $whereClause = implode(' OR ', $conditions);
        $sql = "SELECT id, question, answer, title, content, category, tags
                FROM chatbot_knowledge
                WHERE chatbot_id = :bot_id AND is_active = 1 AND ({$whereClause})
                LIMIT {$intLimit}";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Normalize suggested question chips to a valid JSON array string for MySQL/MariaDB
     */
    public static function formatSuggestedQuestions(mixed $input): string
    {
        $default = ["What services do you offer?", "How can I contact support?"];

        if (empty($input)) {
            return json_encode($default, JSON_UNESCAPED_UNICODE);
        }

        if (is_array($input)) {
            $cleaned = array_values(array_filter(array_map('trim', $input), fn($q) => $q !== ''));
            return json_encode(!empty($cleaned) ? $cleaned : $default, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($input)) {
            $trimmed = trim($input);
            if ($trimmed === '') {
                return json_encode($default, JSON_UNESCAPED_UNICODE);
            }

            // Check if already a valid JSON array
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $cleaned = array_values(array_filter(array_map('trim', $decoded), fn($q) => $q !== ''));
                return json_encode(!empty($cleaned) ? $cleaned : $default, JSON_UNESCAPED_UNICODE);
            }

            // Otherwise parse line-by-line from textarea
            $lines = preg_split('/[\r\n]+/', $trimmed);
            $cleaned = array_values(array_filter(array_map('trim', $lines), fn($q) => $q !== ''));
            return json_encode(!empty($cleaned) ? $cleaned : $default, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($default, JSON_UNESCAPED_UNICODE);
    }
}
