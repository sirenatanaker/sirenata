<?php

namespace Modules\Dashboard\Traits\Validation;

use Illuminate\Validation\Rule;

trait ProfileValidationRules {
    
    /**
     * Main rules
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'full_name' => $this->fullNameRules(),
            'phone' => $this->phoneRules(),
            'gender' => $this->genderRules(),
            'sector' => ['nullable', Rule::in(['instansi', 'swasta'])],
            'asal_instansi' => ['nullable', Rule::in(['pusat', 'provinsi', 'kabkota', 'kab_kota'])],
            'instansi' => $this->instansiRules(),
            'instansi_lainnya' => ['nullable', 'string', 'max:255'],
            'unit_kerja' => $this->unitKerjaRules(),

            'province_code' => $this->provinceRules(),
            'regency_code' => $this->regencyRules(),
            'return_to' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Full Name
     */
    protected function fullNameRules(): array
    {
        // Nullable agar partial update lolos; service fallback ke data lama.
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Phone
     */
    protected function phoneRules(): array
    {
        return ['nullable', 'string', 'regex:/^(\+62|08)[0-9]{8,13}$/'];
    }

    /**
     * Gender
     */
    protected function genderRules(): array
    {
        return ['nullable', Rule::in(['male', 'female'])];
    }

    /**
     * Instansi
     */
    protected function instansiRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    protected function unitKerjaRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }


    /**
     * Scope Area
     */
    protected function provinceRules(): array
    {
        return ['nullable', 'required_if:asal_instansi,provinsi,kabkota,kab_kota'];
    }

    protected function regencyRules(): array
    {
        return ['nullable', 'required_if:asal_instansi,kabkota,kab_kota'];
    }


    protected function profileMessages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid',
        ];
    }
}
