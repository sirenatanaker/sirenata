<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_slug'                => ['required', 'string'],
            'course_section_id'          => ['nullable', 'uuid'],
            'course_id'                  => ['nullable', 'uuid'],
            'title'                      => ['required', 'string', 'max:255'],
            'passing_score'              => ['required', 'integer', 'min:0', 'max:100'],
            'duration'                   => ['required', 'integer', 'min:1'],
            'description'                => ['nullable', 'string'],
            'questions'                  => ['required', 'array', 'min:1'],
            'questions.*.question'       => ['required', 'string'],
            'questions.*.choices'        => ['required', 'array', 'min:2'],
            'questions.*.choices.*'      => ['required', 'string'],
            'questions.*.correct_choice' => ['required', 'integer'],
        ];
    }
}
