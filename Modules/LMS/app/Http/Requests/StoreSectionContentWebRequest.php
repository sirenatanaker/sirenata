<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionContentWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_slug'       => ['required', 'string'],
            'course_section_id' => ['required', 'string'],
            'name'              => ['required', 'string', 'max:255'],
            'video'             => ['nullable', 'string'],
            'document'          => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'content_text'      => ['nullable', 'string'],
        ];
    }
}
