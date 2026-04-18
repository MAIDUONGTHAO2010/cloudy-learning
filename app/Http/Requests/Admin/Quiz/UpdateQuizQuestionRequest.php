<?php

namespace App\Http\Requests\Admin\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'type' => 'required|integer|in:1,2',
            'options' => 'required|array|size:4',
            'options.*.id' => 'required|integer|exists:question_options,id',
            'options.*.content' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }
}
