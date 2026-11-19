<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use App\Core\Database;
use ZipArchive;
use RuntimeException;

class ExportService
{
    private ChatbotService $chatbotService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
    }

    public function exportJsonConfig(int $botId, int $userId): array
    {
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            throw new RuntimeException('Chatbot not found or unauthorized.');
        }

        $knowledge = Database::fetchAll(
            "SELECT type, question, answer, title, content, category, tags 
             FROM chatbot_knowledge WHERE chatbot_id = :id AND is_active = 1",
            ['id' => $botId]
        );

        $data = [
            'schema_version' => '1.0',
            'exported_at' => date('c'),
            'bot' => [
                'name' => $bot['name'],
                'description' => $bot['description'],
                'category' => $bot['category'],
                'language' => $bot['language'],
                'welcome_message' => $bot['welcome_message'],
                'fallback_message' => $bot['fallback_message'],
            ],
            'behavior' => [
                'personality' => $bot['personality'],
                'tone' => $bot['tone'],
                'response_style' => $bot['response_style'],
                'contact_capture_enabled' => (bool)$bot['contact_capture_enabled'],
                'handoff_enabled' => (bool)$bot['handoff_enabled'],
                'suggested_questions' => $bot['suggested_questions_array'],
            ],
            'appearance' => [
                'theme' => $bot['theme'],
                'primary_color' => $bot['primary_color'],
                'secondary_color' => $bot['secondary_color'],
                'header_title' => $bot['header_title'],
                'header_subtitle' => $bot['header_subtitle'],
                'widget_position' => $bot['widget_position'],
                'widget_border_radius' => $bot['widget_border_radius'],
            ],
            'knowledge' => $knowledge,
        ];

        return $data;
    }

    public function exportKnowledgeCsv(int $botId, int $userId): string
    {
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            throw new RuntimeException('Chatbot not found or unauthorized.');
        }

        $items = Database::fetchAll(
            "SELECT question, answer, category, tags FROM chatbot_knowledge 
             WHERE chatbot_id = :id AND type = 'faq' AND is_active = 1",
            ['id' => $botId]
        );

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['question', 'answer', 'category', 'tags'], ',', '"', "\\");

        foreach ($items as $item) {
            fputcsv($fp, [
                $item['question'],
                $item['answer'],
                $item['category'],
                $item['tags'],
            ], ',', '"', "\\");
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent ?: '';
    }

    public function exportConversationsCsv(int $botId, int $userId): string
    {
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            throw new RuntimeException('Chatbot not found or unauthorized.');
        }

        $conversations = Database::fetchAll(
            "SELECT c.uuid, c.session_id, c.visitor_name, c.visitor_email, c.visitor_phone,
                    c.messages_count, c.status, c.created_at
             FROM conversations c
             WHERE c.chatbot_id = :id
             ORDER BY c.created_at DESC",
            ['id' => $botId]
        );

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['conversation_id', 'session_id', 'visitor_name', 'visitor_email', 'visitor_phone', 'messages_count', 'status', 'created_at'], ',', '"', "\\");

        foreach ($conversations as $c) {
            fputcsv($fp, [
                $c['uuid'],
                $c['session_id'],
                $c['visitor_name'],
                $c['visitor_email'],
                $c['visitor_phone'],
                $c['messages_count'],
                $c['status'],
                $c['created_at'],
            ], ',', '"', "\\");
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent ?: '';
    }

    /**
     * Generate complete downloadable standalone ZIP package
     */
    public function generateZipPackage(int $botId, int $userId): string
    {
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            throw new RuntimeException('Chatbot not found or unauthorized.');
        }

        $app = Application::getInstance();
        $exportsDir = $app->storagePath('exports');
        if (!is_dir($exportsDir)) {
            @mkdir($exportsDir, 0755, true);
        }

        $zipPath = $exportsDir . DIRECTORY_SEPARATOR . 'chatbot-' . $bot['slug'] . '-package.zip';
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create ZIP package on server.');
        }

        $publicToken = $bot['public_token'];
        $appUrl = rtrim(Application::config('app.url', 'http://localhost/ChatBot-Creator'), '/');
        $botName = htmlspecialchars($bot['name'], ENT_QUOTES, 'UTF-8');

        // 1. README.md
        $readme = "# {$bot['name']} - Integration Package\n\n"
            . "This package contains everything needed to integrate your ChatBot Creator Studio bot into any website.\n\n"
            . "## Quick Start (CDN Embed)\n"
            . "Paste the following script into your HTML before the closing `</body>` tag:\n\n"
            . "```html\n"
            . "<script src=\"{$appUrl}/embed/{$publicToken}.js\" defer></script>\n"
            . "```\n\n"
            . "## Self-Hosted / Standalone Usage\n"
            . "1. Copy `widget.js` and `widget.css` to your website's asset folder.\n"
            . "2. See `examples/html-example.html` for an HTML integration.\n"
            . "3. See `examples/php-example.php` for a PHP integration.\n\n"
            . "## Security & Domain Restrictions\n"
            . "If configured in your studio settings, only requests originating from your whitelisted domains will be allowed to communicate with this bot.\n";

        // 2. config.json (PUBLIC configuration only - strictly no secrets!)
        $configJson = json_encode([
            'bot_name' => $bot['name'],
            'public_token' => $publicToken,
            'api_endpoint' => "{$appUrl}/api/v1/chat/{$publicToken}/messages",
            'theme' => $bot['theme'],
            'primary_color' => $bot['primary_color'],
            'header_title' => $bot['header_title'],
            'header_subtitle' => $bot['header_subtitle'],
            'welcome_message' => $bot['welcome_message'],
            'suggested_questions' => $bot['suggested_questions_array'],
            'position' => $bot['widget_position'],
            'border_radius' => $bot['widget_border_radius'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // 3. embed.html
        $embedHtml = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n"
            . "    <meta charset=\"UTF-8\">\n    <title>{$botName} - Embed Preview</title>\n"
            . "</head>\n<body>\n"
            . "    <h1>Your Website Content</h1>\n"
            . "    <p>The chatbot widget will render at the configured position on this page.</p>\n\n"
            . "    <!-- ChatBot Creator Studio Embed Script -->\n"
            . "    <script src=\"{$appUrl}/embed/{$publicToken}.js\" defer></script>\n"
            . "</body>\n</html>";

        // 4. examples/html-example.html
        $htmlExample = "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n"
            . "    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n"
            . "    <title>{$botName} Integration Example</title>\n"
            . "    <link rel=\"stylesheet\" href=\"../widget.css\">\n"
            . "</head>\n<body>\n"
            . "    <div style=\"font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px;\">\n"
            . "        <h2>ChatBot Integration HTML Example</h2>\n"
            . "        <p>This page loads the widget locally using the provided configuration.</p>\n"
            . "    </div>\n\n"
            . "    <script src=\"../widget.js\"></script>\n"
            . "    <script>\n"
            . "        window.ChatBotWidget.init({ token: '{$publicToken}', host: '{$appUrl}' });\n"
            . "    </script>\n"
            . "</body>\n</html>";

        // 5. examples/php-example.php
        $phpExample = "<?php\n"
            . "// PHP Integration Example for {$botName}\n"
            . "\$botToken = '{$publicToken}';\n"
            . "\$appUrl = '{$appUrl}';\n"
            . "?>\n"
            . "<!DOCTYPE html>\n<html>\n<head>\n"
            . "    <title><?= htmlspecialchars('{$botName}') ?></title>\n"
            . "</head>\n<body>\n"
            . "    <h1>PHP Host Website</h1>\n"
            . "    <!-- Embedded Chatbot Widget -->\n"
            . "    <script src=\"<?= \$appUrl ?>/embed/<?= \$botToken ?>.js\" defer></script>\n"
            . "</body>\n</html>";

        // Read public widget source
        $widgetJsPath = $app->basePath('public-assets/widget/chatbot-widget.js');
        $widgetCssPath = $app->basePath('public-assets/widget/chatbot-widget.css');

        $widgetJs = file_exists($widgetJsPath) ? file_get_contents($widgetJsPath) : "// Widget JS";
        $widgetCss = file_exists($widgetCssPath) ? file_get_contents($widgetCssPath) : "/* Widget CSS */";

        $zip->addFromString('chatbot-integration/README.md', $readme);
        $zip->addFromString('chatbot-integration/config.json', $configJson);
        $zip->addFromString('chatbot-integration/embed.html', $embedHtml);
        $zip->addFromString('chatbot-integration/widget.js', $widgetJs);
        $zip->addFromString('chatbot-integration/widget.css', $widgetCss);
        $zip->addFromString('chatbot-integration/examples/html-example.html', $htmlExample);
        $zip->addFromString('chatbot-integration/examples/php-example.php', $phpExample);

        $zip->close();

        return $zipPath;
    }
}
