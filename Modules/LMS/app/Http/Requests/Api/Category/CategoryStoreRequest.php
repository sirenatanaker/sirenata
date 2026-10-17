<?php

namespace Modules\LMS\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dokumentasi field untuk Scramble
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Nama kategori course',
                'example'     => 'Backend Development',
            ],
        ];
    }
}
