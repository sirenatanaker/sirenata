<?php

namespace Modules\LMS\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'audience'    => ['sometimes', 'in:instansi,swasta'],
        
            'thumbnail'   => ['nullable', 'image', 'mimes:png,jpg', 'max:2048'],
            'description' => ['sometimes', 'string'],
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
     * Get custom messages for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori Course',
            'name' => 'Nama Course',
            'audience' => 'Peruntukan Course',
            'thumbnail' => 'Thumbnail Course',
            'description' => 'Deskripsi Course',
        ];
    }
}
