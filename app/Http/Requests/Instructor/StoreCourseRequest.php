<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'  => 'nullable|exists:categories,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'thumbnail'    => 'nullable|string|max:255',
            'is_active'    => 'nullable|boolean',
            'tags'         => 'nullable|array',
            'tags.*'       => 'string|max:50',
        ];
    }
}
