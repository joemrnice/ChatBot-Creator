<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use App\Core\Session;

class AdminController
{
    public function index(Request $request): Response
    {
        // 1. User Statistics
        $totalUsers = (int)Database::fetchValue("SELECT COUNT(*) FROM users");
        $activeUsers = (int)Database::fetchValue("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $suspendedUsers = (int)Database::fetchValue("SELECT COUNT(*) FROM users WHERE status = 'suspended'");

        // 2. Chatbot Statistics
        $totalBots = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbots");
        $publishedBots = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbots WHERE status = 'published'");
        $draftBots = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbots WHERE status = 'draft'");

        // 3. Conversations & Messages
        $totalConversations = (int)Database::fetchValue("SELECT COUNT(*) FROM conversations");
        $totalMessages = (int)Database::fetchValue("SELECT COUNT(*) FROM messages");

        // 4. Plan Subscriber Counts
        $freeCount = (int)Database::fetchValue("SELECT COUNT(*) FROM users u LEFT JOIN plans p ON p.id = u.plan_id WHERE p.slug = 'free' OR u.plan_id IS NULL");
        $standardCount = (int)Database::fetchValue("SELECT COUNT(*) FROM users u JOIN plans p ON p.id = u.plan_id WHERE p.slug = 'standard'");
        $proCount = (int)Database::fetchValue("SELECT COUNT(*) FROM users u JOIN plans p ON p.id = u.plan_id WHERE p.slug = 'pro'");
        $enterpriseCount = (int)Database::fetchValue("SELECT COUNT(*) FROM users u JOIN plans p ON p.id = u.plan_id WHERE p.slug = 'enterprise'");

        // 5. Financial & Transactions
        $totalRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(amount), 0) FROM payment_transactions WHERE status = 'completed'");
        $successfulPayments = (int)Database::fetchValue("SELECT COUNT(*) FROM payment_transactions WHERE status = 'completed'");
        $failedPayments = (int)Database::fetchValue("SELECT COUNT(*) FROM payment_transactions WHERE status = 'failed'");

        // Estimated MRR calculation
        $standardPrice = (float)Database::fetchValue("SELECT price_monthly FROM plans WHERE slug = 'standard'") ?: 19.00;
        $proPrice = (float)Database::fetchValue("SELECT price_monthly FROM plans WHERE slug = 'pro'") ?: 49.00;
        $enterprisePrice = (float)Database::fetchValue("SELECT price_monthly FROM plans WHERE slug = 'enterprise'") ?: 199.00;
        $estimatedMrr = ($standardCount * $standardPrice) + ($proCount * $proPrice) + ($enterpriseCount * $enterprisePrice);

        // Recent Activity
        $recentUsers = Database::fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
        $recentBots = Database::fetchAll("SELECT c.*, u.name as owner_name, u.email as owner_email FROM chatbots c JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC LIMIT 5");
        $recentLogs = Database::fetchAll("SELECT al.*, u.name as user_name FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 6");

        return Response::view('admin/dashboard', [
            'title' => 'Admin Center - Overview',
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'totalBots' => $totalBots,
            'publishedBots' => $publishedBots,
            'draftBots' => $draftBots,
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'freeCount' => $freeCount,
            'standardCount' => $standardCount,
            'proCount' => $proCount,
            'enterpriseCount' => $enterpriseCount,
            'totalRevenue' => $totalRevenue,
            'successfulPayments' => $successfulPayments,
            'failedPayments' => $failedPayments,
            'estimatedMrr' => $estimatedMrr,
            'recentUsers' => $recentUsers,
            'recentBots' => $recentBots,
            'recentLogs' => $recentLogs,
        ], 'layouts/admin');
    }

    public function users(Request $request): Response
    {
        $search = trim((string)$request->query('q', ''));
        $status = trim((string)$request->query('status', ''));

        $sql = "SELECT u.*, p.name as plan_name, p.slug as plan_slug,
                       (SELECT COUNT(*) FROM chatbots WHERE user_id = u.id) as bots_count
                FROM users u
                LEFT JOIN plans p ON p.id = u.plan_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if ($status !== '') {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT 100";
        $users = Database::fetchAll($sql, $params);

        return Response::view('admin/users/index', [
            'title' => 'User Management - Admin Center',
            'users' => $users,
            'search' => $search,
            'status' => $status,
        ], 'layouts/admin');
    }

    public function userDetail(Request $request): Response
    {
        $userId = (int)$request->param('userId');
        $user = Database::fetchOne(
            "SELECT u.*, p.name as plan_name FROM users u LEFT JOIN plans p ON p.id = u.plan_id WHERE u.id = :id",
            ['id' => $userId]
        );

        if (!$user) {
            Session::flash('error', 'User not found.');
            return Response::redirect('/admin/users');
        }

        $bots = Database::fetchAll("SELECT * FROM chatbots WHERE user_id = :id ORDER BY created_at DESC", ['id' => $userId]);
        $subscription = Database::fetchOne("SELECT * FROM subscriptions WHERE user_id = :id ORDER BY id DESC LIMIT 1", ['id' => $userId]);
        $transactions = Database::fetchAll("SELECT * FROM payment_transactions WHERE user_id = :id ORDER BY created_at DESC", ['id' => $userId]);
        $allPlans = Database::fetchAll("SELECT id, name, slug FROM plans ORDER BY sort_order ASC");

        return Response::view('admin/users/show', [
            'title' => "User: {$user['name']} - Admin Center",
            'user' => $user,
            'bots' => $bots,
            'subscription' => $subscription,
            'transactions' => $transactions,
            'plans' => $allPlans,
        ], 'layouts/admin');
    }

    public function updateUser(Request $request): Response
    {
        $userId = (int)$request->param('userId');
        $status = (string)$request->input('status');
        $planId = (int)$request->input('plan_id');

        Database::update('users', [
            'status' => $status,
            'plan_id' => $planId,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $userId]);

        // Audit Log
        Database::insert('audit_logs', [
            'user_id' => Auth::id(),
            'action' => 'admin_updated_user',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'details' => json_encode(['new_status' => $status, 'new_plan' => $planId]),
            'ip_address' => $request->ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'User updated successfully.');
        return Response::redirect("/admin/users/{$userId}");
    }

    public function bots(Request $request): Response
    {
        $search = trim((string)$request->query('q', ''));
        $category = trim((string)$request->query('category', ''));

        $sql = "SELECT c.*, u.name as owner_name, u.email as owner_email,
                       (SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = c.id) as knowledge_count
                FROM chatbots c
                JOIN users u ON u.id = c.user_id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (c.name LIKE :search OR c.slug LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        if ($category !== '') {
            $sql .= " AND c.category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT 100";
        $bots = Database::fetchAll($sql, $params);

        return Response::view('admin/bots/index', [
            'title' => 'Chatbot Management - Admin Center',
            'bots' => $bots,
            'search' => $search,
            'category' => $category,
        ], 'layouts/admin');
    }

    public function toggleBotStatus(Request $request): Response
    {
        $botId = (int)$request->input('bot_id');
        $status = (string)$request->input('status');

        if (in_array($status, ['draft', 'published', 'suspended'], true)) {
            Database::update('chatbots', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $botId]);

            Database::insert('audit_logs', [
                'user_id' => Auth::id(),
                'action' => 'admin_bot_status_changed',
                'entity_type' => 'chatbot',
                'entity_id' => $botId,
                'details' => json_encode(['status' => $status]),
                'ip_address' => $request->ip(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            Session::flash('success', "Chatbot status changed to {$status}.");
        }

        return Response::back();
    }

    public function plans(Request $request): Response
    {
        $plans = Database::fetchAll("SELECT * FROM plans ORDER BY sort_order ASC");
        foreach ($plans as &$p) {
            $p['paypal_mappings'] = Database::fetchAll("SELECT * FROM paypal_plans WHERE plan_id = :id", ['id' => $p['id']]);
        }

        return Response::view('admin/plans/index', [
            'title' => 'Plans & Entitlements - Admin Center',
            'plans' => $plans,
        ], 'layouts/admin');
    }

    public function updatePlan(Request $request): Response
    {
        $planId = (int)$request->input('plan_id');

        $update = [
            'name' => trim((string)$request->input('name')),
            'price_monthly' => (float)$request->input('price_monthly'),
            'price_yearly' => (float)$request->input('price_yearly'),
            'max_chatbots' => (int)$request->input('max_chatbots'),
            'monthly_messages' => (int)$request->input('monthly_messages'),
            'monthly_conversations' => (int)$request->input('monthly_conversations'),
            'max_knowledge_entries' => (int)$request->input('max_knowledge_entries'),
            'allow_custom_branding' => $request->input('allow_custom_branding') ? 1 : 0,
            'allow_api_access' => $request->input('allow_api_access') ? 1 : 0,
            'allow_ai_custom_keys' => $request->input('allow_ai_custom_keys') ? 1 : 0,
            'is_featured' => $request->input('is_featured') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        Database::update('plans', $update, 'id = :id', ['id' => $planId]);

        // Update PayPal plan ID mappings if provided
        $paypalMonth = trim((string)$request->input('paypal_plan_month', ''));
        if ($paypalMonth !== '') {
            Database::query(
                "INSERT INTO paypal_plans (plan_id, paypal_plan_id, billing_interval, environment, status, created_at)
                 VALUES (:pid, :ppid, 'MONTH', 'sandbox', 'ACTIVE', NOW())
                 ON DUPLICATE KEY UPDATE paypal_plan_id = VALUES(paypal_plan_id)",
                ['pid' => $planId, 'ppid' => $paypalMonth]
            );
        }

        $paypalYear = trim((string)$request->input('paypal_plan_year', ''));
        if ($paypalYear !== '') {
            Database::query(
                "INSERT INTO paypal_plans (plan_id, paypal_plan_id, billing_interval, environment, status, created_at)
                 VALUES (:pid, :ppid, 'YEAR', 'sandbox', 'ACTIVE', NOW())
                 ON DUPLICATE KEY UPDATE paypal_plan_id = VALUES(paypal_plan_id)",
                ['pid' => $planId, 'ppid' => $paypalYear]
            );
        }

        Session::flash('success', 'Plan details and PayPal mappings saved successfully.');
        return Response::redirect('/admin/plans');
    }

    public function payments(Request $request): Response
    {
        $transactions = Database::fetchAll(
            "SELECT pt.*, u.name as user_name, u.email as user_email
             FROM payment_transactions pt
             JOIN users u ON u.id = pt.user_id
             ORDER BY pt.created_at DESC LIMIT 100"
        );

        return Response::view('admin/billing/payments', [
            'title' => 'Transactions & Payments - Admin Center',
            'transactions' => $transactions,
        ], 'layouts/admin');
    }

    public function webhooks(Request $request): Response
    {
        $events = Database::fetchAll("SELECT * FROM paypal_webhook_events ORDER BY created_at DESC LIMIT 100");

        return Response::view('admin/billing/webhooks', [
            'title' => 'PayPal Webhook Events - Admin Center',
            'events' => $events,
        ], 'layouts/admin');
    }

    public function settings(Request $request): Response
    {
        $settings = Database::fetchAll("SELECT * FROM system_settings");
        $keyedSettings = [];
        foreach ($settings as $s) {
            $keyedSettings[$s['key_name']] = $s['value_content'];
        }

        return Response::view('admin/settings/index', [
            'title' => 'System & Integration Settings - Admin Center',
            'settings' => $keyedSettings,
        ], 'layouts/admin');
    }

    public function updateSettings(Request $request): Response
    {
        $fields = $request->all();
        unset($fields['csrf_token']);

        foreach ($fields as $key => $val) {
            Database::query(
                "INSERT INTO system_settings (setting_group, key_name, value_content, updated_at)
                 VALUES ('general', :k, :v, NOW())
                 ON DUPLICATE KEY UPDATE value_content = VALUES(value_content), updated_at = NOW()",
                ['k' => $key, 'v' => (string)$val]
            );
        }

        Database::insert('audit_logs', [
            'user_id' => Auth::id(),
            'action' => 'admin_settings_updated',
            'details' => json_encode(['keys_updated' => array_keys($fields)]),
            'ip_address' => $request->ip(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'System settings saved.');
        return Response::redirect('/admin/settings');
    }

    public function auditLogs(Request $request): Response
    {
        $logs = Database::fetchAll(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 150"
        );

        return Response::view('admin/audit/index', [
            'title' => 'Security & Audit Logs - Admin Center',
            'logs' => $logs,
        ], 'layouts/admin');
    }

    public function support(Request $request): Response
    {
        $tickets = Database::fetchAll(
            "SELECT st.*, u.name as user_name, u.email as user_email,
                    (SELECT COUNT(*) FROM support_messages WHERE ticket_id = st.id) as msg_count
             FROM support_tickets st
             JOIN users u ON u.id = st.user_id
             ORDER BY st.updated_at DESC LIMIT 100"
        );

        return Response::view('admin/support/index', [
            'title' => 'Customer Support - Admin Center',
            'tickets' => $tickets,
        ], 'layouts/admin');
    }

    public function usage(Request $request): Response
    {
        $totalBots = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbots");
        $activeBots = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbots WHERE status = 'published'");
        $totalMessages = (int)Database::fetchValue("SELECT COUNT(*) FROM messages");
        $totalKnowledge = (int)Database::fetchValue("SELECT COUNT(*) FROM chatbot_knowledge");
        $totalConversations = (int)Database::fetchValue("SELECT COUNT(*) FROM conversations");

        $topUsers = Database::fetchAll(
            "SELECT u.id, u.name, u.email, p.name as plan_name,
                    (SELECT COUNT(*) FROM chatbots WHERE user_id = u.id) as bots_count,
                    (SELECT COUNT(m.id) FROM messages m JOIN conversations c ON c.id = m.conversation_id JOIN chatbots cb ON cb.id = c.chatbot_id WHERE cb.user_id = u.id) as messages_count
             FROM users u
             LEFT JOIN plans p ON p.id = u.plan_id
             ORDER BY messages_count DESC, bots_count DESC
             LIMIT 10"
        );

        return Response::view('admin/usage/index', [
            'title' => 'Platform Usage & Quotas - Admin Center',
            'totalBots' => $totalBots,
            'activeBots' => $activeBots,
            'totalMessages' => $totalMessages,
            'totalKnowledge' => $totalKnowledge,
            'totalConversations' => $totalConversations,
            'topUsers' => $topUsers,
        ], 'layouts/admin');
    }

    public function analytics(Request $request): Response
    {
        $totalConversations = (int)Database::fetchValue("SELECT COUNT(*) FROM conversations");
        $totalMessages = (int)Database::fetchValue("SELECT COUNT(*) FROM messages");
        $positiveFeedback = (int)Database::fetchValue("SELECT COUNT(*) FROM conversation_feedback WHERE rating = 'up'");
        $negativeFeedback = (int)Database::fetchValue("SELECT COUNT(*) FROM conversation_feedback WHERE rating = 'down'");
        $feedbackScore = ($positiveFeedback + $negativeFeedback > 0) ? round(($positiveFeedback / ($positiveFeedback + $negativeFeedback)) * 100) : 100;

        $categories = Database::fetchAll(
            "SELECT category, COUNT(*) as count FROM chatbots GROUP BY category ORDER BY count DESC"
        );

        $recentConversations = Database::fetchAll(
            "SELECT c.*, b.name as bot_name, b.category as bot_category
             FROM conversations c
             JOIN chatbots b ON b.id = c.chatbot_id
             ORDER BY c.created_at DESC
             LIMIT 20"
        );

        return Response::view('admin/analytics/index', [
            'title' => 'Platform Analytics - Admin Center',
            'totalConversations' => $totalConversations,
            'totalMessages' => $totalMessages,
            'positiveFeedback' => $positiveFeedback,
            'negativeFeedback' => $negativeFeedback,
            'feedbackScore' => $feedbackScore,
            'categories' => $categories,
            'recentConversations' => $recentConversations,
        ], 'layouts/admin');
    }

    public function roles(Request $request): Response
    {
        $roles = Database::fetchAll(
            "SELECT r.*, COUNT(ur.user_id) as users_count
             FROM roles r
             LEFT JOIN user_roles ur ON ur.role_id = r.id
             GROUP BY r.id
             ORDER BY r.id ASC"
        );

        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY category ASC, name ASC");

        return Response::view('admin/roles/index', [
            'title' => 'Roles & Permissions - Admin Center',
            'roles' => $roles,
            'permissions' => $permissions,
        ], 'layouts/admin');
    }

    public function security(Request $request): Response
    {
        $loginAttempts = Database::fetchAll(
            "SELECT * FROM login_attempts ORDER BY attempted_at DESC LIMIT 30"
        );

        $recentSecurityLogs = Database::fetchAll(
            "SELECT al.*, u.name as user_name, u.email as user_email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.action LIKE '%security%' OR al.action LIKE '%login%' OR al.action LIKE '%password%'
             ORDER BY al.created_at DESC
             LIMIT 30"
        );

        $securityChecks = [
            'app_debug' => !\App\Core\Application::config('app.debug', false),
            'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443),
            'password_argon' => defined('PASSWORD_ARGON2ID'),
            'session_httponly' => (bool)ini_get('session.cookie_httponly'),
            'session_samesite' => ini_get('session.cookie_samesite') ?: 'Lax',
        ];

        return Response::view('admin/security/index', [
            'title' => 'Security Center - Admin Center',
            'loginAttempts' => $loginAttempts,
            'recentSecurityLogs' => $recentSecurityLogs,
            'securityChecks' => $securityChecks,
        ], 'layouts/admin');
    }

    public function system(Request $request): Response
    {
        $pdo = Database::pdo();
        $dbVersion = $pdo->query("SELECT VERSION()")->fetchColumn() ?: 'Unknown';

        $app = \App\Core\Application::getInstance();
        $storagePaths = [
            'storage/logs' => is_writable($app->storagePath('logs')),
            'storage/cache' => is_writable($app->storagePath('cache')),
            'storage/uploads' => is_writable($app->storagePath('uploads')),
            'storage/exports' => is_writable($app->storagePath('exports')),
            'storage/private' => is_writable($app->storagePath('private')),
        ];

        $extensions = [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'curl' => extension_loaded('curl'),
            'json' => extension_loaded('json'),
            'openssl' => extension_loaded('openssl'),
            'zip' => extension_loaded('zip'),
            'mbstring' => extension_loaded('mbstring'),
        ];

        $integrations = [
            'paypal' => [
                'mode' => \App\Core\Application::config('paypal.mode', 'sandbox'),
                'configured' => !empty(\App\Core\Application::config('paypal.client_id')),
            ],
            'github' => [
                'configured' => !empty(\App\Core\Application::config('oauth.github.client_id')),
            ],
            'google' => [
                'configured' => !empty(\App\Core\Application::config('oauth.google.client_id')),
            ],
            'facebook' => [
                'configured' => !empty(\App\Core\Application::config('oauth.facebook.client_id')),
            ],
            'ai_provider' => [
                'provider' => \App\Core\Application::config('chatbot.default_provider', 'knowledge_base'),
                'configured' => !empty(\App\Core\Application::config('chatbot.openai_api_key')),
            ],
        ];

        return Response::view('admin/system/index', [
            'title' => 'System Diagnostics & Health - Admin Center',
            'phpVersion' => PHP_VERSION,
            'dbVersion' => $dbVersion,
            'storagePaths' => $storagePaths,
            'extensions' => $extensions,
            'integrations' => $integrations,
            'memoryUsage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        ], 'layouts/admin');
    }
}
