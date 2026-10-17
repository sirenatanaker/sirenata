<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LibraryTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:library_types,name'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Tipe Perpustakaan',
        ];
    }
}
