<?php
declare(strict_types=1);

namespace App\Providers;

interface AIProviderInterface
{
    /**
     * Generate response for chatbot interaction
     *
     * @param string $systemPrompt Base personality & role instructions
     * @param array $conversationHistory Recent message objects [{sender_type, content}]
     * @param string $userMessage Current user query
     * @param array $knowledgeContext Relevant retrieved knowledge documents / FAQs
     * @param array $options Additional model options (temperature, custom API key, endpoint)
     * @return array ['content' => string, 'model' => string, 'tokens' => int, 'source' => string]
     */
    public function generateResponse(
        string $systemPrompt,
        array $conversationHistory,
        string $userMessage,
        array $knowledgeContext = [],
        array $options = []
    ): array;
}
