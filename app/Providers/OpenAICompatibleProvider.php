<?php
declare(strict_types=1);

namespace App\Providers;

use App\Core\Application;

class OpenAICompatibleProvider implements AIProviderInterface
{
    private FallbackChatProvider $fallbackProvider;

    public function __construct()
    {
        $this->fallbackProvider = new FallbackChatProvider();
    }

    public function generateResponse(
        string $systemPrompt,
        array $conversationHistory,
        string $userMessage,
        array $knowledgeContext = [],
        array $options = []
    ): array {
        $apiKey = $options['api_key'] ?? Application::config('chatbot.ai.api_key', '');
        $endpoint = $options['endpoint'] ?? Application::config('chatbot.ai.endpoint', 'https://api.openai.com/v1/chat/completions');
        $model = $options['model'] ?? Application::config('chatbot.ai.model', 'gpt-4o-mini');
        $temperature = (float)($options['temperature'] ?? 0.7);

        // If no API key configured, use fallback provider seamlessly
        if (empty($apiKey)) {
            return $this->fallbackProvider->generateResponse($systemPrompt, $conversationHistory, $userMessage, $knowledgeContext, $options);
        }

        // Build knowledge context block
        $contextText = "";
        if (!empty($knowledgeContext)) {
            $contextText = "\n\n### VERIFIED KNOWLEDGE BASE CONTEXT:\n";
            foreach ($knowledgeContext as $idx => $k) {
                $num = $idx + 1;
                if (!empty($k['question']) && !empty($k['answer'])) {
                    $contextText .= "[Doc {$num} - FAQ]: Q: {$k['question']} | A: {$k['answer']}\n";
                } elseif (!empty($k['title']) && !empty($k['content'])) {
                    $contextText .= "[Doc {$num} - {$k['title']}]: {$k['content']}\n";
                }
            }
            $contextText .= "\nInstruction for using knowledge: Prioritize the verified knowledge base above. If the information to answer the question is not present, use the configured fallback response.\n";
        }

        $fullSystemPrompt = $systemPrompt . $contextText;

        // Build messages array
        $messages = [
            ['role' => 'system', 'content' => $fullSystemPrompt]
        ];

        // Add recent conversation history (last 6 messages max)
        $recentHistory = array_slice($conversationHistory, -6);
        foreach ($recentHistory as $msg) {
            $role = ($msg['sender_type'] === 'user') ? 'user' : 'assistant';
            $messages[] = ['role' => $role, 'content' => (string)$msg['content']];
        }

        // Add current user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => 800,
        ];

        try {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$apiKey}",
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError || $httpCode !== 200) {
                error_log("OpenAI Provider Error ({$httpCode}): " . ($curlError ?: (string)$response));
                return $this->fallbackProvider->generateResponse($systemPrompt, $conversationHistory, $userMessage, $knowledgeContext, $options);
            }

            $result = json_decode((string)$response, true);
            $botReply = $result['choices'][0]['message']['content'] ?? null;
            $tokens = (int)($result['usage']['total_tokens'] ?? 0);

            if (!$botReply) {
                return $this->fallbackProvider->generateResponse($systemPrompt, $conversationHistory, $userMessage, $knowledgeContext, $options);
            }

            return [
                'content' => trim($botReply),
                'model' => $model,
                'tokens' => $tokens,
                'source' => 'ai',
                'matched_knowledge_id' => !empty($knowledgeContext[0]['id']) ? $knowledgeContext[0]['id'] : null,
            ];
        } catch (\Throwable $e) {
            error_log('AI Provider Exception: ' . $e->getMessage());
            return $this->fallbackProvider->generateResponse($systemPrompt, $conversationHistory, $userMessage, $knowledgeContext, $options);
        }
    }
}
