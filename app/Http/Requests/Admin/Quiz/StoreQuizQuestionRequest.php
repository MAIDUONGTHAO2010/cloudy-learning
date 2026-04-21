<?php

namespace App\Http\Requests\Admin\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'              => 'required|string',
            'type'                 => 'required|integer|in:1,2,3,4',
            'answer_type'          => 'required|integer|in:1,2',
            'options'              => 'required|array|size:4',
            'options.*.label'      => 'required|integer|in:1,2,3,4',
            'options.*.content'    => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ];
    }
}
