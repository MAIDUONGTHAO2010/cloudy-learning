<?php

namespace App\Http\Requests\Admin\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
