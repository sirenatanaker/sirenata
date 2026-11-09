<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\PostTestQuestion;

class PostTest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'course_section_id',
        'course_id',
        'title',
        'description',
        'passing_score',
        'duration',
        'type',
        'admin_file_path',
        'admin_file_name',
        'admin_file_size',
        'instructions',
        'max_words',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(PostTestQuestion::class);
    }

    public function getAdminFileUrlAttribute(): ?string
    {
        return $this->admin_file_path ? Storage::url($this->admin_file_path) : null;
    }

    public function getIsEssayAttribute(): bool
    {
        return in_array($this->type, ['essay', 'uraian']);
    }

    public function getIsFileUploadAttribute(): bool
    {
        return in_array($this->type, ['file_upload', 'upload']);
    }

    public function getIsMultipleChoiceAttribute(): bool
    {
        return in_array($this->type, ['multiple_choice', 'pilgan']) || empty($this->type);
    }
}
