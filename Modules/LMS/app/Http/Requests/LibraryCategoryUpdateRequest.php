<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LMS\Models\LibraryCategory;

class LibraryCategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $param = $this->route('library_category') ?? $this->route('libraryCategory') ?? $this->route('id');
        $categoryId = $param instanceof LibraryCategory ? $param->id : $param;

        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('library_categories', 'name')->ignore($categoryId)],
            'description' => ['nullable', 'string'],
        ];
    }
}
