<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;

class InstructorController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index()
    {
        return response()->json($this->userService->listInstructors());
    }
}
