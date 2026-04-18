<?php

namespace App\Http\Requests\Auth;

use App\Enums\User\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:'.UserRole::STUDENT.','.UserRole::INSTRUCTOR,
            'date_of_birth' => 'nullable|date|before:today',
            'sex' => 'nullable|in:0,1,2',
            'categories' => 'nullable|array|max:3',
            'categories.*' => 'integer|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Please select a role.',
            'role.in' => 'Role must be either student or instructor.',
        ];
    }
}
