<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class NotificationRepositoryEloquent extends BaseRepository implements NotificationRepositoryInterface
{
    public function model(): string
    {
        return Notification::class;
    }

    public function getForUser(int $userId, int $limit = 20): mixed
    {
        return Notification::where('target', 'user')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getForAdmin(int $limit = 50): mixed
    {
        return Notification::where('target', 'admin')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function unreadCountForUser(int $userId): int
    {
        return Notification::where('target', 'user')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function unreadCountForAdmin(): int
    {
        return Notification::where('target', 'admin')
            ->where('is_read', false)
            ->count();
    }

    public function markRead(int $id): void
    {
        Notification::where('id', $id)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllReadForUser(int $userId): void
    {
        Notification::where('target', 'user')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function markAllReadForAdmin(): void
    {
        Notification::where('target', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }
}
