<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Modules\LMS\Database\Factories\SectionContentFactory;

class SectionContent extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_section_id',
        'name',
        'video',
        'content_text',
        'document',
        'library_id',
        'position',
    ];

    // protected static function newFactory(): SectionContentFactory
    // {
    //     // return SectionContentFactory::new();
    // }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class, 'library_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SectionContentDocument::class, 'section_content_id');
    }

    public function libraries(): BelongsToMany
    {
        return $this->belongsToMany(Library::class, 'section_content_libraries', 'section_content_id', 'library_id')->withTimestamps();
    }

    public function getAllDocumentsAttribute()
    {
        if ($this->relationLoaded('documents') && $this->documents->isNotEmpty()) {
            return $this->documents;
        }

        $docs = $this->documents()->get();
        if ($docs->isNotEmpty()) {
            return $docs;
        }

        if ($this->document) {
            return collect([
                (object) [
                    'id'                  => 'legacy',
                    'file_path'           => $this->document,
                    'file_name'           => basename($this->document),
                    'file_url'            => asset('storage/' . $this->document),
                    'file_extension'      => strtolower(pathinfo($this->document, PATHINFO_EXTENSION)),
                    'formatted_file_size' => '',
                ]
            ]);
        }

        return collect();
    }

    public function getAllLibrariesAttribute()
    {
        if ($this->relationLoaded('libraries') && $this->libraries->isNotEmpty()) {
            return $this->libraries;
        }

        $libs = $this->libraries()->with('libraryCategory')->get();
        if ($libs->isNotEmpty()) {
            return $libs;
        }

        if ($this->library) {
            return collect([$this->library]);
        }

        return collect();
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentContentProgress::class, 'section_content_id');
    }

    // Cek apakah konten sudah selesai ditonton oleh user tertentu
    public function isCompletedByUser(string $userId): bool
    {
        return $this->studentProgress()
            ->where('user_id', $userId)
            ->exists();
    }

    // public function getVideoUrlAttribute(): ?string
    // {
    //     return $this->video
    //         ? asset('storage/' . $this->video)
    //         : null;
    // }
    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document
            ? asset('storage/' . $this->document)
            : null;
    }
}
