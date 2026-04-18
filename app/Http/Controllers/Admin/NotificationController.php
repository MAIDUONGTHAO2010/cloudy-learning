<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        return response()->json(
            $this->notificationService->getForAdmin()
        );
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->unreadCountForAdmin(),
        ]);
    }

    public function markRead(int $id)
    {
        $this->notificationService->markRead($id);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead()
    {
        $this->notificationService->markAllReadForAdmin();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
