<?php

namespace Modules\LMS\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:enrolled,in_progress,completed'],
        ];
    }
}
