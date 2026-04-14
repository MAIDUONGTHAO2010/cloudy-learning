<?php

namespace App\Http\Controllers\Admin;

use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;

class InstructorController extends Controller
{
    public function index()
    {
        $instructors = User::where('role', UserRole::INSTRUCTOR)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json($instructors);
    }
}
