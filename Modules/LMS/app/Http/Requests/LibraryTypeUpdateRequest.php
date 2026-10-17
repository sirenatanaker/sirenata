<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LibraryTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:library_types,name,' . $this->route('library_type')],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Tipe Perpustakaan',
        ];
    }
}
