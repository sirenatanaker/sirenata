<?php

namespace Modules\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebInstansiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sector' => ['required', Rule::in(['instansi', 'swasta'])],
            'asalInstansi' => ['required_if:sector,instansi', Rule::in(['pusat', 'provinsi', 'kabkota'])],
            'instansi' => ['required', 'string', 'max:255'],
            'instansi_lainnya' => ['required_if:instansi,lainnya', 'nullable', 'string', 'max:255'],
            'unit_kerja' => ['required_if:sector,instansi', 'nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'required_if:asalInstansi,provinsi,kabkota', 'required_if:sector,swasta'],
            'regency_code' => ['nullable', 'required_if:asalInstansi,kabkota', 'required_if:sector,swasta'],
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
