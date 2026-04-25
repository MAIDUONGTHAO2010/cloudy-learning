<?php

namespace App\Http\Requests\Auth;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Foundation\Http\FormRequest;

class PresignProfileAvatarUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_name'    => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'starts_with:image/'],
            'file_size'    => ['required', 'integer', 'min:1', 'max:' . AuthController::MAX_AVATAR_SIZE],
        ];
    }
}
