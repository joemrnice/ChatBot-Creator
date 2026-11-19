<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class UsageService
{
    public function getCurrentMonth(): string
    {
        return date('Y-m');
    }

    public function recordUsage(int $userId, string $metricKey, int $increment = 1): void
    {
        $period = $this->getCurrentMonth();
        $sql = "INSERT INTO usage_records (user_id, metric_key, metric_value, period_year_month, updated_at)
                VALUES (:user_id, :metric_key, :increment, :period, NOW())
                ON DUPLICATE KEY UPDATE metric_value = metric_value + :inc_update, updated_at = NOW()";

        Database::query($sql, [
            'user_id' => $userId,
            'metric_key' => $metricKey,
            'increment' => $increment,
            'period' => $period,
            'inc_update' => $increment,
        ]);
    }

    public function getMonthlyUsage(int $userId, string $metricKey): int
    {
        $period = $this->getCurrentMonth();
        $sql = "SELECT metric_value FROM usage_records 
                WHERE user_id = :user_id AND metric_key = :metric_key AND period_year_month = :period
                LIMIT 1";

        $val = Database::fetchValue($sql, [
            'user_id' => $userId,
            'metric_key' => $metricKey,
            'period' => $period,
        ]);

        return $val !== false ? (int)$val : 0;
    }

    public function getUserUsageSummary(int $userId): array
    {
        $messages = $this->getMonthlyUsage($userId, 'messages_count');
        $conversations = $this->getMonthlyUsage($userId, 'conversations_count');

        $activeBots = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM chatbots WHERE user_id = :user_id AND status != 'suspended'",
            ['user_id' => $userId]
        );

        $totalKnowledge = (int)Database::fetchValue(
            "SELECT COUNT(ck.id) FROM chatbot_knowledge ck
             JOIN chatbots c ON c.id = ck.chatbot_id
             WHERE c.user_id = :user_id",
            ['user_id' => $userId]
        );

        return [
            'active_chatbots' => $activeBots,
            'monthly_messages' => $messages,
            'monthly_conversations' => $conversations,
            'total_knowledge_entries' => $totalKnowledge,
            'period' => $this->getCurrentMonth(),
        ];
    }
}
