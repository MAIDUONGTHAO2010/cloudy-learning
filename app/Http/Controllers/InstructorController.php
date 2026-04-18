<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(): JsonResponse
    {
        return response()->json($this->userService->listPublicInstructors());
    }
}
