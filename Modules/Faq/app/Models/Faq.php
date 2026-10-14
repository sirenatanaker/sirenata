<?php

namespace Modules\Faq\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'created_by',
    ];


    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function roles()
    {
        return $this->belongsToMany(
            \Modules\Roles\Models\Role::class,
            'faq_role',
            'faq_id',
            'role_id',
            'id',
            'uuid'
        );
    }

    /**
     * Role yang boleh menjadi target FAQ: semua role kecuali pengelola
     * (super-admin & admin-pusat melihat semuanya). Diambil dinamis dari
     * tabel roles agar role baru otomatis ikut tersedia.
     *
     * @return list<string>
     */
    public static function targetableRoles(): array
    {
        return \Modules\Roles\Models\Role::query()
            ->whereNotIn('name', ['super-admin', 'admin-pusat'])
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @param list<string> $names
     * @return list<string>
     */
    public static function resolveRoleIds(array $names): array
    {
        return \Modules\Roles\Models\Role::whereIn('name', array_unique($names))->pluck('uuid')->all();
    }
}
