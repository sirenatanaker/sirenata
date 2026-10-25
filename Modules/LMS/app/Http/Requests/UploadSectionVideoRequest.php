<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadSectionVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimes:mp4,webm,ogg,mkv,mov,avi',
                'max:102400', // 100MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'video.required'  => 'File video wajib dipilih.',
            'video.file'      => 'Unggahan harus berupa berkas valid.',
            'video.mimes'     => 'Format video tidak didukung. Gunakan format MP4, WebM, OGG, MKV, atau MOV.',
            'video.max'       => 'Ukuran video melebihi batas maksimal 100MB.',
        ];
    }
}
