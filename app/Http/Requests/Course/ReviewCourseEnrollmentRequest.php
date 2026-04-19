<?php

namespace App\Http\Requests\Course;

use App\Enums\Course\EnrollmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', Rule::in([
                EnrollmentStatus::APPROVED,
                EnrollmentStatus::CANCELED,
            ])],
            'note' => ['nullable', 'string', 'max:1000', 'required_if:status,' . EnrollmentStatus::CANCELED],
        ];
    }
}
