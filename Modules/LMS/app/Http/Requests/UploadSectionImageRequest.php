<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadSectionImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif,webp,svg',
                'max:10240', // 10MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'File gambar wajib dipilih.',
            'image.file'     => 'Unggahan harus berupa berkas valid.',
            'image.image'    => 'Berkas harus berupa gambar.',
            'image.mimes'    => 'Format gambar tidak didukung. Gunakan format PNG, JPG, JPEG, WEBP, GIF, atau SVG.',
            'image.max'      => 'Ukuran gambar melebihi batas maksimal 10MB.',
        ];
    }
}
