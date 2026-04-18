<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\SendContactMessageRequest;
use App\Services\NotificationService;

class ContactController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function store(SendContactMessageRequest $request)
    {
        $this->notificationService->notifyAdminContactMessage($request->validated());

        return response()->json([
            'message' => 'Message sent successfully.',
        ], 201);
    }
}
