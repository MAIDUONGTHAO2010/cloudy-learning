<?php

namespace App\Http\Requests\Admin\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class PresignLessonVideoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'string', 'starts_with:video/'],
            'file_size' => ['required', 'integer', 'min:1', 'max:1610612736'],
        ];
    }
}
