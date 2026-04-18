<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    public function getForUser(int $userId, int $limit = 20): mixed;

    public function getForAdmin(int $limit = 50): mixed;

    public function unreadCountForUser(int $userId): int;

    public function unreadCountForAdmin(): int;

    public function markRead(int $id): void;

    public function markAllReadForUser(int $userId): void;

    public function markAllReadForAdmin(): void;
}
