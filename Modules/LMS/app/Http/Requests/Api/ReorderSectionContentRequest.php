<?php

namespace Modules\LMS\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSectionContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contents'            => ['required', 'array'],
            'contents.*.id'       => ['required', 'uuid', 'exists:section_contents,id'],
            'contents.*.position' => ['required', 'integer', 'min:0'],
        ];
    }
}
