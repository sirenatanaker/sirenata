<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateSetting extends Model
{
    protected $fillable = [
        'signer_name',
        'signer_title',
        'signature_image',
        'background_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActive(): ?self
    {
        $setting = static::where('is_active', true)->first();
        if (!$setting) {
            $setting = static::first();
        }
        if (!$setting) {
            $setting = static::create([
                'signer_name' => 'Kepala Pusat Pengembangan SDM Aparatur',
                'signer_title' => 'Pembina Utama Madya / PPSDM',
                'is_active' => true,
            ]);
        }
        return $setting;
    }
}
