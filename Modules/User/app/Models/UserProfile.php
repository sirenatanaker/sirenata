<?php

namespace Modules\User\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\User\Database\Factories\UserProfileFactory;

class UserProfile extends Model
{
    use HasFactory, HasUuids;


    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'institution_type',
        'instansi',
        'unit_kerja',
        'surat_tugas',
        'nik',
        'phone',
        'avatar',
        'address',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // protected static function newFactory(): UserProfileFactory
    // {
    //     // return UserProfileFactory::new();
    // }
}
