<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    public function send(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): int
    {
        return Database::insert('notifications', [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getUnreadCount(int $userId): int
    {
        return (int)Database::fetchValue(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
    }

    public function getUserNotifications(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        Database::update(
            'notifications',
            ['is_read' => 1],
            'id = :id AND user_id = :uid',
            ['id' => $notificationId, 'uid' => $userId]
        );
    }

    public function markAllAsRead(int $userId): void
    {
        Database::update(
            'notifications',
            ['is_read' => 1],
            'user_id = :uid AND is_read = 0',
            ['uid' => $userId]
        );
    }
}
