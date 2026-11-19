<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class SubscriptionService
{
    private UsageService $usageService;

    public function __construct()
    {
        $this->usageService = new UsageService();
    }

    public function getUserPlan(int $userId): array
    {
        $sql = "SELECT p.*, s.id as subscription_id, s.status as subscription_status,
                       s.paypal_subscription_id, s.current_period_end, s.billing_interval as sub_interval
                FROM users u
                LEFT JOIN subscriptions s ON s.user_id = u.id AND s.status IN ('active', 'trialing', 'past_due')
                LEFT JOIN plans p ON p.id = COALESCE(s.plan_id, u.plan_id, 1)
                WHERE u.id = :user_id
                LIMIT 1";

        $plan = Database::fetchOne($sql, ['user_id' => $userId]);

        if (!$plan) {
            // Default to free plan
            $plan = Database::fetchOne("SELECT * FROM plans WHERE slug = 'free' LIMIT 1");
        }

        return $plan ?: [
            'id' => 1,
            'name' => 'Free',
            'slug' => 'free',
            'max_chatbots' => 1,
            'monthly_messages' => 150,
            'monthly_conversations' => 50,
            'max_knowledge_entries' => 25,
            'max_upload_size_mb' => 5,
            'allow_custom_branding' => 0,
            'allow_api_access' => 0,
            'allow_ai_custom_keys' => 0,
        ];
    }

    public function getAllActivePlans(): array
    {
        $plans = Database::fetchAll("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC");
        foreach ($plans as &$plan) {
            $plan['features'] = Database::fetchAll(
                "SELECT * FROM plan_features WHERE plan_id = :plan_id ORDER BY sort_order ASC",
                ['plan_id' => $plan['id']]
            );
        }
        return $plans;
    }

    public function canCreateChatbot(int $userId): bool
    {
        $plan = $this->getUserPlan($userId);
        $maxBots = (int)($plan['max_chatbots'] ?? 1);

        $currentBots = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM chatbots WHERE user_id = :user_id AND status != 'suspended'",
            ['user_id' => $userId]
        );

        return $currentBots < $maxBots;
    }

    public function canSendMessage(int $userId): bool
    {
        $plan = $this->getUserPlan($userId);
        $maxMessages = (int)($plan['monthly_messages'] ?? 150);

        $currentMessages = $this->usageService->getMonthlyUsage($userId, 'messages_count');
        return $currentMessages < $maxMessages;
    }

    public function canAddKnowledge(int $userId, int $chatbotId, int $addingCount = 1): bool
    {
        $plan = $this->getUserPlan($userId);
        $maxKnowledge = (int)($plan['max_knowledge_entries'] ?? 25);

        $currentKnowledge = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM chatbot_knowledge WHERE chatbot_id = :bot_id",
            ['bot_id' => $chatbotId]
        );

        return ($currentKnowledge + $addingCount) <= $maxKnowledge;
    }

    public function canUseCustomBranding(int $userId): bool
    {
        $plan = $this->getUserPlan($userId);
        return (bool)($plan['allow_custom_branding'] ?? false);
    }

    public function canUseAiKeys(int $userId): bool
    {
        $plan = $this->getUserPlan($userId);
        return (bool)($plan['allow_ai_custom_keys'] ?? false);
    }

    public function assignPlan(int $userId, int $planId, ?string $paypalSubId = null, string $interval = 'month'): void
    {
        Database::transaction(function () use ($userId, $planId, $paypalSubId, $interval) {
            // Update user record
            Database::update('users', ['plan_id' => $planId], 'id = :id', ['id' => $userId]);

            // Deactivate any existing active subscriptions
            Database::query(
                "UPDATE subscriptions SET status = 'cancelled', cancelled_at = NOW() 
                 WHERE user_id = :uid AND status = 'active'",
                ['uid' => $userId]
            );

            // Calculate period end
            $periodEnd = date('Y-m-d H:i:s', strtotime($interval === 'year' ? '+1 year' : '+1 month'));

            // Insert new subscription
            Database::insert('subscriptions', [
                'user_id' => $userId,
                'plan_id' => $planId,
                'paypal_subscription_id' => $paypalSubId,
                'status' => 'active',
                'billing_interval' => $interval,
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => $periodEnd,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        });
    }

    public function cancelSubscription(int $userId, string $reason = 'User requested cancellation'): bool
    {
        $activeSub = Database::fetchOne(
            "SELECT * FROM subscriptions WHERE user_id = :uid AND status = 'active' LIMIT 1",
            ['uid' => $userId]
        );

        if (!$activeSub) {
            return false;
        }

        Database::update(
            'subscriptions',
            [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancel_reason' => $reason,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = :id',
            ['id' => $activeSub['id']]
        );

        return true;
    }
}
