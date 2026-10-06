<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Library extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $fillable = [
        'library_category_id',
        'title',
        'description',
        'cover_image',
        'file_path',
        'video_path',
        'external_link',
        'created_by',
    ];

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    public function libraryCategory()
    {
        return $this->belongsTo(LibraryCategory::class, 'library_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Modules/LMS/Models/Library.php
    public function usersAccessed()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_library_history')
            ->withPivot('last_accessed_at')
            ->orderByPivot('last_accessed_at', 'desc');
    }
}
