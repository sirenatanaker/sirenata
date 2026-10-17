<?php

namespace Modules\LMS\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReorderCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections'            => ['required', 'array'],
            'sections.*.id'       => ['required', 'uuid', 'exists:course_sections,id'],
            'sections.*.position' => ['required', 'integer', 'min:1'],
        ];
    }
}
