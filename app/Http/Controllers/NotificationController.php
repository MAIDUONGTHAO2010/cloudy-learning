<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        return response()->json(
            $this->notificationService->getForUser(Auth::id())
        );
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->unreadCountForUser(Auth::id()),
        ]);
    }

    public function markRead(int $id)
    {
        $this->notificationService->markRead($id);

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead()
    {
        $this->notificationService->markAllReadForUser(Auth::id());

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
