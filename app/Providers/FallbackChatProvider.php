<?php
declare(strict_types=1);

namespace App\Providers;

class FallbackChatProvider implements AIProviderInterface
{
    public function generateResponse(
        string $systemPrompt,
        array $conversationHistory,
        string $userMessage,
        array $knowledgeContext = [],
        array $options = []
    ): array {
        $fallbackMessage = $options['fallback_message'] ?? "I'm sorry, I don't have enough information to answer that question. Would you like to connect with our team?";
        $welcomeMessage = $options['welcome_message'] ?? "Hello! How can I help you today?";

        $cleanMsg = trim(strtolower($userMessage));

        // 1. Common greetings check
        $greetings = ['hi', 'hello', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening', 'start'];
        if (in_array($cleanMsg, $greetings, true) || $cleanMsg === '') {
            return [
                'content' => $welcomeMessage,
                'model' => 'knowledge-rules',
                'tokens' => 10,
                'source' => 'greeting',
                'matched_knowledge_id' => null,
            ];
        }

        // 2. If knowledge context matches were retrieved
        if (!empty($knowledgeContext)) {
            $bestMatch = $knowledgeContext[0];
            $answer = $bestMatch['answer'] ?? $bestMatch['content'] ?? null;
            if ($answer) {
                return [
                    'content' => $answer,
                    'model' => 'knowledge-base',
                    'tokens' => str_word_count($answer),
                    'source' => 'knowledge',
                    'matched_knowledge_id' => $bestMatch['id'] ?? null,
                ];
            }
        }

        // 3. Fallback response
        return [
            'content' => $fallbackMessage,
            'model' => 'system-fallback',
            'tokens' => str_word_count($fallbackMessage),
            'source' => 'fallback',
            'matched_knowledge_id' => null,
        ];
    }
}
