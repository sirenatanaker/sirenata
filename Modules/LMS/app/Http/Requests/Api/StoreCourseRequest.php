<?php

namespace Modules\LMS\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
  public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
            // sometimes agar klien API lama yang belum mengirim tetap lolos (default di service: instansi).
            'audience'    => ['sometimes', 'in:instansi,swasta'],
        
            'thumbnail'   => ['nullable', 'image',  'mimes:png,jpg', 'max:2048'], 
            'description' => ['required', 'string'],
            'bg_color'    => ['nullable', 'string', 'max:20'],
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
