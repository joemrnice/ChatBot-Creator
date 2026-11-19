<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Application;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\ChatbotService;

class EmbedController
{
    private ChatbotService $chatbotService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
    }

    public function script(Request $request): Response
    {
        $token = (string)$request->param('publicBotToken');
        $bot = $this->chatbotService->getBotByPublicToken($token);

        if (!$bot) {
            return Response::make("console.error('ChatBot Widget: Invalid or suspended bot token.');", 404, [
                'Content-Type' => 'application/javascript; charset=utf-8',
            ]);
        }

        $app = Application::getInstance();
        $widgetJsPath = $app->basePath('public-assets/widget/chatbot-widget.js');
        $js = file_exists($widgetJsPath) ? file_get_contents($widgetJsPath) : '';

        // Prepend bootstrap auto-init
        $appUrl = rtrim(Application::config('app.url', 'http://localhost/ChatBot-Creator'), '/');
        $bootstrap = "window.CBC_DEFAULT_TOKEN = '{$token}'; window.CBC_DEFAULT_HOST = '{$appUrl}';\n";

        return Response::make($bootstrap . $js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function config(Request $request): Response
    {
        $token = (string)$request->param('publicBotToken');
        $bot = $this->chatbotService->getBotByPublicToken($token);

        if (!$bot) {
            return Response::json(['error' => 'Bot not found or unavailable'], 404, [
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $config = [
            'token' => $bot['public_token'],
            'name' => $bot['name'],
            'category' => $bot['category'],
            'language' => $bot['language'],
            'welcome_message' => $bot['welcome_message'],
            'suggested_questions' => $bot['suggested_questions_array'],
            'theme' => $bot['theme'],
            'primary_color' => $bot['primary_color'],
            'secondary_color' => $bot['secondary_color'],
            'header_title' => $bot['header_title'],
            'header_subtitle' => $bot['header_subtitle'],
            'widget_position' => $bot['widget_position'],
            'widget_border_radius' => $bot['widget_border_radius'],
            'hide_watermark' => (bool)$bot['hide_watermark'] && (bool)$bot['allow_custom_branding'],
            'contact_capture_enabled' => (bool)$bot['contact_capture_enabled'],
            'contact_capture_prompt' => $bot['contact_capture_prompt'],
        ];

        return Response::json($config, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function landing(Request $request): Response
    {
        $slug = (string)$request->param('botSlug');
        $bot = $this->chatbotService->getBotBySlug($slug);

        if (!$bot) {
            return Response::view('public/404', [
                'title' => 'Chatbot Not Found - ChatBot Creator Studio',
            ], 'layouts/main')->setStatusCode(404);
        }

        // Increment view count
        Database::query("UPDATE chatbots SET views_count = views_count + 1 WHERE id = :id", ['id' => $bot['id']]);

        return Response::view('chatbot/standalone', [
            'title' => "{$bot['name']} - Live Chat",
            'bot' => $bot,
        ], null); // standalone layout
    }
}
