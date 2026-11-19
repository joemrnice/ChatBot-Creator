<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ChatbotService;
use App\Services\ImportService;

class ImportController
{
    private ChatbotService $chatbotService;
    private ImportService $importService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
        $this->importService = new ImportService();
    }

    public function show(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);

        if (!$bot) {
            Session::flash('error', 'Chatbot not found.');
            return Response::redirect('/studio/bots');
        }

        return Response::view('studio/bots/import', [
            'title' => "Import Knowledge & Config - {$bot['name']}",
            'bot' => $bot,
            'preview' => Session::getFlash('import_preview'),
        ], 'layouts/studio');
    }

    public function upload(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            Session::flash('error', 'Chatbot not found.');
            return Response::redirect('/studio/bots');
        }

        if (!$request->hasFile('import_file')) {
            Session::flash('error', 'Please choose a valid JSON or CSV file to upload.');
            return Response::back();
        }

        try {
            $file = $request->file('import_file');
            $preview = $this->importService->parseImportFile($userId, $botId, $file);

            Session::flash('import_preview', $preview);
            Session::flash('success', "File parsed successfully. Found {$preview['valid_count']} valid records. Review preview below and select import mode.");
            return Response::redirect("/studio/bots/{$botId}/import");
        } catch (\Throwable $e) {
            Session::flash('error', 'Import Parsing Error: ' . $e->getMessage());
            return Response::back();
        }
    }

    public function confirm(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $jobId = (int)$request->input('job_id');
        $mode = (string)$request->input('mode', 'add');

        try {
            $result = $this->importService->commitImport($jobId, $userId, $botId, $mode);
            Session::flash('success', "Successfully imported {$result['imported_count']} records into your knowledge base!");
            return Response::redirect("/studio/bots/{$botId}/knowledge");
        } catch (\Throwable $e) {
            Session::flash('error', 'Import Commit Failed: ' . $e->getMessage());
            return Response::redirect("/studio/bots/{$botId}/import");
        }
    }

    /**
     * Download sample templates
     */
    public function sampleJson(Request $request): Response
    {
        $sample = [
            'schema_version' => '1.0',
            'bot' => [
                'name' => 'Sample Assistant',
                'description' => 'Demonstration chatbot configuration',
                'category' => 'business',
                'language' => 'en',
                'welcome_message' => 'Welcome! How can I assist you?',
                'fallback_message' => 'I do not have enough information to answer that.',
            ],
            'behavior' => [
                'tone' => 'helpful',
                'response_style' => 'concise',
                'handoff_enabled' => true,
            ],
            'appearance' => [
                'theme' => 'light',
                'primary_color' => '#2563eb',
                'widget_position' => 'bottom-right',
            ],
            'knowledge' => [
                [
                    'type' => 'faq',
                    'question' => 'What are your support hours?',
                    'answer' => 'Our team is available Monday through Friday from 9 AM to 5 PM.',
                    'category' => 'Support',
                    'tags' => ['hours', 'support'],
                ],
                [
                    'type' => 'faq',
                    'question' => 'How can I reset my password?',
                    'answer' => 'Visit the login page and click Forgot Password.',
                    'category' => 'Account',
                    'tags' => ['password', 'login'],
                ],
            ],
        ];

        $jsonStr = json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return Response::make($jsonStr, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="sample-chatbot-config.json"',
        ]);
    }

    public function sampleCsv(Request $request): Response
    {
        $csv = "question,answer,category,tags\n"
            . "\"What services do you provide?\",\"We offer 24/7 automated customer support and AI integrations.\",\"Services\",\"offerings,support\"\n"
            . "\"Where are you located?\",\"We are headquartered in San Francisco, California.\",\"General\",\"location,office\"\n";

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample-knowledge.csv"',
        ]);
    }
}
