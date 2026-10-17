<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signer_name'      => ['required', 'string', 'max:255'],
            'signer_title'     => ['required', 'string', 'max:255'],
            'signature_image'  => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'background_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];
    }
}
