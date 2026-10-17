<?php

namespace Modules\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstansiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'asal_instansi' => ['required', Rule::in(['pusat', 'provinsi', 'kab_kota'])],

            'instansi' => ['nullable', 'string', 'max:255'],
            'instansi_lainnya' => ['nullable', 'string', 'max:255'],

            'unit_kerja' => ['required', 'string', 'max:255'],

            'province_code' => ['nullable', 'required_if:asal_instansi,provinsi,kab_kota'],
            'regency_code' => ['nullable', 'required_if:asal_instansi,kab_kota'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
