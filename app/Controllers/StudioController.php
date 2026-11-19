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
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use App\Services\UsageService;

class StudioController
{
    private SubscriptionService $subscriptionService;
    private UsageService $usageService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService();
        $this->usageService = new UsageService();
        $this->notificationService = new NotificationService();
    }

    public function index(Request $request): Response
    {
        $userId = (int)Auth::id();
        $user = Auth::user();
        $plan = $this->subscriptionService->getUserPlan($userId);
        $usage = $this->usageService->getUserUsageSummary($userId);

        $maxBots = (int)($plan['max_chatbots'] ?? 1);
        $maxMessages = (int)($plan['monthly_messages'] ?? 150);

        $remainingBots = max(0, $maxBots - $usage['active_chatbots']);
        $remainingMessages = max(0, $maxMessages - $usage['monthly_messages']);
        $messageUsagePct = $maxMessages > 0 ? min(100, round(($usage['monthly_messages'] / $maxMessages) * 100)) : 100;

        // Recent Chatbots
        $chatbots = Database::fetchAll(
            "SELECT c.*, cfg.primary_color, cfg.header_title,
                    (SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = c.id) as knowledge_count
             FROM chatbots c
             LEFT JOIN chatbot_configurations cfg ON cfg.chatbot_id = c.id
             WHERE c.user_id = :uid
             ORDER BY c.updated_at DESC LIMIT 5",
            ['uid' => $userId]
        );

        // Recent Conversations across all user chatbots
        $recentConversations = Database::fetchAll(
            "SELECT conv.*, c.name as bot_name, c.slug as bot_slug
             FROM conversations conv
             JOIN chatbots c ON c.id = conv.chatbot_id
             WHERE c.user_id = :uid
             ORDER BY conv.updated_at DESC LIMIT 5",
            ['uid' => $userId]
        );

        // Notifications
        $notifications = $this->notificationService->getUserNotifications($userId, 5);

        return Response::view('studio/dashboard', [
            'title' => 'Personal Studio - Overview',
            'user' => $user,
            'plan' => $plan,
            'usage' => $usage,
            'maxBots' => $maxBots,
            'maxMessages' => $maxMessages,
            'remainingBots' => $remainingBots,
            'remainingMessages' => $remainingMessages,
            'messageUsagePct' => $messageUsagePct,
            'chatbots' => $chatbots,
            'recentConversations' => $recentConversations,
            'notifications' => $notifications,
        ], 'layouts/studio');
    }

    public function usage(Request $request): Response
    {
        $userId = (int)Auth::id();
        $plan = $this->subscriptionService->getUserPlan($userId);
        $usage = $this->usageService->getUserUsageSummary($userId);

        // Historical monthly usage for the last 6 months
        $history = Database::fetchAll(
            "SELECT period_year_month, metric_key, metric_value 
             FROM usage_records
             WHERE user_id = :uid
             ORDER BY period_year_month DESC",
            ['uid' => $userId]
        );

        return Response::view('studio/usage', [
            'title' => 'Resource Usage & Quotas - Personal Studio',
            'plan' => $plan,
            'usage' => $usage,
            'history' => $history,
        ], 'layouts/studio');
    }

    public function templates(Request $request): Response
    {
        $templates = Database::fetchAll("SELECT * FROM chatbot_templates ORDER BY is_featured DESC, name ASC");

        return Response::view('studio/templates', [
            'title' => 'Chatbot Templates - Personal Studio',
            'templates' => $templates,
        ], 'layouts/studio');
    }

    public function profile(Request $request): Response
    {
        $user = Auth::user();
        return Response::view('studio/profile', [
            'title' => 'Account Profile - Personal Studio',
            'user' => $user,
        ], 'layouts/studio');
    }

    public function updateProfile(Request $request): Response
    {
        $userId = (int)Auth::id();
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:100',
            'email' => "required|email|unique:users,email,{$userId}",
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        Database::update('users', [
            'name' => trim((string)$request->input('name')),
            'email' => strtolower(trim((string)$request->input('email'))),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);

        Session::flash('success', 'Profile updated successfully.');
        return Response::redirect('/studio/profile');
    }

    public function security(Request $request): Response
    {
        $userId = (int)Auth::id();
        $user = Auth::user();
        $oauthAccounts = Database::fetchAll("SELECT * FROM oauth_accounts WHERE user_id = :uid", ['uid' => $userId]);
        $recentLogins = Database::fetchAll("SELECT * FROM login_attempts WHERE email = :email ORDER BY attempted_at DESC LIMIT 5", ['email' => $user['email']]);

        return Response::view('studio/security', [
            'title' => 'Security Settings - Personal Studio',
            'user' => $user,
            'oauthAccounts' => $oauthAccounts,
            'recentLogins' => $recentLogins,
        ], 'layouts/studio');
    }

    public function updatePassword(Request $request): Response
    {
        $userId = (int)Auth::id();
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'required|matches:new_password',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return Response::back();
        }

        $user = Database::fetchOne("SELECT password_hash FROM users WHERE id = :id", ['id' => $userId]);
        if (!empty($user['password_hash']) && !Security::verifyPassword((string)$request->input('current_password'), $user['password_hash'])) {
            Session::flash('error', 'Current password is incorrect.');
            return Response::back();
        }

        Database::update('users', [
            'password_hash' => Security::hashPassword((string)$request->input('new_password')),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);

        Session::flash('success', 'Password updated successfully.');
        return Response::redirect('/studio/security');
    }

    public function notifications(Request $request): Response
    {
        $userId = (int)Auth::id();
        $notifications = $this->notificationService->getUserNotifications($userId, 50);
        $this->notificationService->markAllAsRead($userId);

        return Response::view('studio/notifications', [
            'title' => 'Notifications - Personal Studio',
            'notifications' => $notifications,
        ], 'layouts/studio');
    }
}
