<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        return response()->json(
            $this->userService->list($request->only(['search', 'role']))
        );
    }

    public function stats()
    {
        return response()->json($this->userService->stats());
    }

    public function toggleActive(int $id)
    {
        $user = $this->userService->toggleActive($id);

        return response()->json($user);
    }
}
