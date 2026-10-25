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
            'document'          => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,csv', 'max:20480'],
            'documents'         => ['nullable', 'array'],
            'documents.*'       => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,csv', 'max:20480'],
            'library_id'        => ['nullable', 'string', 'exists:libraries,id'],
            'library_ids'       => ['nullable', 'array'],
            'library_ids.*'     => ['nullable', 'string', 'exists:libraries,id'],
            'content_text'      => ['nullable', 'string'],
        ];
    }
}
