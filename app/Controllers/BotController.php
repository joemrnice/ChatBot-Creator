<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;
use App\Core\Validator;
use App\Services\ChatbotService;
use App\Services\SubscriptionService;

class BotController
{
    private ChatbotService $chatbotService;
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->chatbotService = new ChatbotService();
        $this->subscriptionService = new SubscriptionService();
    }

    public function index(Request $request): Response
    {
        $userId = (int)Auth::id();
        $bots = $this->chatbotService->getBotsForUser($userId);
        $plan = $this->subscriptionService->getUserPlan($userId);
        $canCreate = $this->subscriptionService->canCreateChatbot($userId);

        return Response::view('studio/bots/index', [
            'title' => 'My Chatbots - Personal Studio',
            'bots' => $bots,
            'plan' => $plan,
            'canCreate' => $canCreate,
        ], 'layouts/studio');
    }

    public function create(Request $request): Response
    {
        $userId = (int)Auth::id();
        if (!$this->subscriptionService->canCreateChatbot($userId)) {
            Session::flash('error', 'You have reached your chatbot creation limit. Upgrade your subscription to create more chatbots.');
            return Response::redirect('/studio/billing');
        }

        $templateId = (int)$request->query('template', 0);
        $template = null;
        if ($templateId > 0) {
            $template = Database::fetchOne("SELECT * FROM chatbot_templates WHERE id = :id", ['id' => $templateId]);
        }

        $templates = Database::fetchAll("SELECT * FROM chatbot_templates ORDER BY is_featured DESC, name ASC");

        return Response::view('studio/bots/create', [
            'title' => 'Create Chatbot Wizard - Personal Studio',
            'selectedTemplate' => $template,
            'templates' => $templates,
        ], 'layouts/studio');
    }

    public function store(Request $request): Response
    {
        $userId = (int)Auth::id();

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'category' => 'required',
            'welcome_message' => 'required',
            'fallback_message' => 'required',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        try {
            $data = $request->all();
            if (isset($data['suggested_questions']) && is_string($data['suggested_questions'])) {
                $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $data['suggested_questions'])));
                $data['suggested_questions'] = array_values($lines);
            }
            $botId = $this->chatbotService->createBot($userId, $data);
            Session::flash('success', 'Chatbot created successfully! Customize it in the studio builder below.');
            return Response::redirect("/studio/bots/{$botId}/builder");
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to create chatbot: ' . $e->getMessage());
            return Response::back();
        }
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

        $knowledgeCount = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = :id", ['id' => $botId]);
        $conversationsCount = (int)Database::fetchValue("SELECT COUNT(*) FROM conversations WHERE chatbot_id = :id", ['id' => $botId]);
        $messagesCount = (int)Database::fetchValue("SELECT COUNT(m.id) FROM messages m JOIN conversations c ON c.id = m.conversation_id WHERE c.chatbot_id = :id", ['id' => $botId]);

        return Response::view('studio/bots/show', [
            'title' => "{$bot['name']} - Overview",
            'bot' => $bot,
            'knowledgeCount' => $knowledgeCount,
            'conversationsCount' => $conversationsCount,
            'messagesCount' => $messagesCount,
        ], 'layouts/studio');
    }

    public function builder(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);

        if (!$bot) {
            Session::flash('error', 'Chatbot not found.');
            return Response::redirect('/studio/bots');
        }

        $plan = $this->subscriptionService->getUserPlan($userId);

        return Response::view('studio/bots/builder', [
            'title' => "Studio Builder - {$bot['name']}",
            'bot' => $bot,
            'plan' => $plan,
        ], 'layouts/studio');
    }

    public function updateBuilder(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        try {
            $data = $request->isJson() ? $request->json() : $request->all();

            // Format suggested questions if textarea string
            if (isset($data['suggested_questions']) && is_string($data['suggested_questions'])) {
                $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $data['suggested_questions'])));
                $data['suggested_questions'] = array_values($lines);
            }

            $this->chatbotService->updateBot($botId, $userId, $data);

            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => true, 'message' => 'Chatbot updated successfully.']);
            }

            Session::flash('success', 'Changes saved successfully.');
            return Response::redirect("/studio/bots/{$botId}/builder");
        } catch (\Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return Response::json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::flash('error', 'Failed to save changes: ' . $e->getMessage());
            return Response::back();
        }
    }

    public function knowledge(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);

        if (!$bot) {
            return Response::redirect('/studio/bots');
        }

        $items = Database::fetchAll(
            "SELECT * FROM chatbot_knowledge WHERE chatbot_id = :id ORDER BY id DESC",
            ['id' => $botId]
        );

        $plan = $this->subscriptionService->getUserPlan($userId);
        $maxKnowledge = (int)($plan['max_knowledge_entries'] ?? 25);

        return Response::view('studio/bots/knowledge', [
            'title' => "Knowledge Base - {$bot['name']}",
            'bot' => $bot,
            'items' => $items,
            'plan' => $plan,
            'maxKnowledge' => $maxKnowledge,
            'currentCount' => count($items),
        ], 'layouts/studio');
    }

    public function storeKnowledge(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) {
            return Response::redirect('/studio/bots');
        }

        if (!$this->subscriptionService->canAddKnowledge($userId, $botId, 1)) {
            Session::flash('error', 'Knowledge base limit reached for your plan. Please upgrade to add more knowledge items.');
            return Response::back();
        }

        $type = (string)$request->input('type', 'faq');
        $question = trim((string)$request->input('question', ''));
        $answer = trim((string)$request->input('answer', ''));
        $title = trim((string)$request->input('title', ''));
        $content = trim((string)$request->input('content', ''));
        $category = trim((string)$request->input('category', 'General'));
        $tags = trim((string)$request->input('tags', ''));

        if ($type === 'faq' && ($question === '' || $answer === '')) {
            Session::flash('error', 'Both question and answer are required.');
            return Response::back();
        }

        if ($type === 'document' && ($title === '' || $content === '')) {
            Session::flash('error', 'Both document title and content are required.');
            return Response::back();
        }

        Database::insert('chatbot_knowledge', [
            'chatbot_id' => $botId,
            'type' => $type,
            'question' => Security::sanitizeString($question),
            'answer' => Security::sanitizeHtml($answer),
            'title' => Security::sanitizeString($title),
            'content' => Security::sanitizeHtml($content),
            'category' => Security::sanitizeString($category),
            'tags' => Security::sanitizeString($tags),
            'source' => 'manual',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'Knowledge entry added successfully.');
        return Response::redirect("/studio/bots/{$botId}/knowledge");
    }

    public function deleteKnowledge(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $itemId = (int)$request->input('item_id');

        $bot = $this->chatbotService->getBotById($botId, $userId);
        if ($bot) {
            Database::delete('chatbot_knowledge', 'id = :id AND chatbot_id = :bid', ['id' => $itemId, 'bid' => $botId]);
            Session::flash('success', 'Knowledge item removed.');
        }

        return Response::redirect("/studio/bots/{$botId}/knowledge");
    }

    public function appearance(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        $plan = $this->subscriptionService->getUserPlan($userId);

        return Response::view('studio/bots/appearance', [
            'title' => "Appearance - {$bot['name']}",
            'bot' => $bot,
            'plan' => $plan,
        ], 'layouts/studio');
    }

    public function behavior(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        $plan = $this->subscriptionService->getUserPlan($userId);

        return Response::view('studio/bots/behavior', [
            'title' => "Behavior & AI - {$bot['name']}",
            'bot' => $bot,
            'plan' => $plan,
        ], 'layouts/studio');
    }

    public function embed(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        return Response::view('studio/bots/embed', [
            'title' => "Embed & Share - {$bot['name']}",
            'bot' => $bot,
        ], 'layouts/studio');
    }

    public function analytics(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        $totalConversations = (int)Database::fetchValue("SELECT COUNT(*) FROM conversations WHERE chatbot_id = :id", ['id' => $botId]);
        $totalMessages = (int)Database::fetchValue("SELECT COUNT(m.id) FROM messages m JOIN conversations c ON c.id = m.conversation_id WHERE c.chatbot_id = :id", ['id' => $botId]);
        $positiveFeedback = (int)Database::fetchValue("SELECT COUNT(cf.id) FROM conversation_feedback cf JOIN messages m ON m.id = cf.message_id JOIN conversations c ON c.id = m.conversation_id WHERE c.chatbot_id = :id AND cf.rating = 'up'", ['id' => $botId]);
        $negativeFeedback = (int)Database::fetchValue("SELECT COUNT(cf.id) FROM conversation_feedback cf JOIN messages m ON m.id = cf.message_id JOIN conversations c ON c.id = m.conversation_id WHERE c.chatbot_id = :id AND cf.rating = 'down'", ['id' => $botId]);

        $feedbackScore = ($positiveFeedback + $negativeFeedback > 0) ? round(($positiveFeedback / ($positiveFeedback + $negativeFeedback)) * 100) : 100;

        return Response::view('studio/bots/analytics', [
            'title' => "Analytics - {$bot['name']}",
            'bot' => $bot,
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'positiveFeedback' => $positiveFeedback,
            'negativeFeedback' => $negativeFeedback,
            'feedbackScore' => $feedbackScore,
        ], 'layouts/studio');
    }

    public function conversations(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        $conversations = Database::fetchAll(
            "SELECT * FROM conversations WHERE chatbot_id = :id ORDER BY created_at DESC LIMIT 50",
            ['id' => $botId]
        );

        return Response::view('studio/bots/conversations', [
            'title' => "Conversations - {$bot['name']}",
            'bot' => $bot,
            'conversations' => $conversations,
        ], 'layouts/studio');
    }

    public function settings(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');
        $bot = $this->chatbotService->getBotById($botId, $userId);
        if (!$bot) return Response::redirect('/studio/bots');

        return Response::view('studio/bots/settings', [
            'title' => "Bot Settings - {$bot['name']}",
            'bot' => $bot,
        ], 'layouts/studio');
    }

    public function delete(Request $request): Response
    {
        $userId = (int)Auth::id();
        $botId = (int)$request->param('botId');

        if ($this->chatbotService->deleteBot($botId, $userId)) {
            Session::flash('success', 'Chatbot deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete chatbot.');
        }

        return Response::redirect('/studio/bots');
    }
}
