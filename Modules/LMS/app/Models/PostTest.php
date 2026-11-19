<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class PostTest extends Model
{
    use HasFactory, HasUuids;

    public const GRADING_AUTO_EXCEL = 'AUTO_EXCEL';
    public const GRADING_AUTO_AI_ESSAY = 'AUTO_AI_ESSAY';
    public const GRADING_AUTO_AI_REPORT = 'AUTO_AI_REPORT';
    public const GRADING_MANUAL = 'MANUAL';

    protected $fillable = [
        'course_section_id',
        'course_id',
        'title',
        'description',
        'passing_score',
        'passing_grade',
        'duration',
        'type',
        'grading_type',
        'rubric_json',
        'admin_file_path',
        'admin_file_name',
        'admin_file_size',
        'template_extracted_text',
        'answer_key_file_path',
        'answer_key_file_name',
        'answer_key_file_size',
        'key_extracted_text',
        'instructions',
        'max_words',
        'is_submission_locked',
    ];

    protected $casts = [
        'rubric_json'          => 'array',
        'passing_grade'        => 'float',
        'is_submission_locked' => 'boolean',
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

    public function gradingBatches(): HasMany
    {
        return $this->hasMany(GradingBatch::class, 'post_test_id');
    }

    public function latestGradingBatch(): HasOne
    {
        return $this->hasOne(GradingBatch::class, 'post_test_id')->latestOfMany('started_at');
    }

    public function getAdminFileUrlAttribute(): ?string
    {
        return $this->admin_file_path ? Storage::url($this->admin_file_path) : null;
    }

    public function getAnswerKeyFileUrlAttribute(): ?string
    {
        return $this->answer_key_file_path ? Storage::url($this->answer_key_file_path) : null;
    }

    public function effectivePassingGrade(): float
    {
        if (!is_null($this->passing_grade) && (float) $this->passing_grade > 0) {
            return (float) $this->passing_grade;
        }

        if ((int) ($this->passing_score ?? 0) > 0) {
            return (float) $this->passing_score;
        }

        return $this->is_file_upload ? 75.0 : 70.0;
    }

    public function resolvedGradingType(): string
    {
        if (!empty($this->grading_type) && $this->grading_type !== self::GRADING_MANUAL) {
            return $this->grading_type;
        }

        if ($this->is_file_upload) {
            $ext = strtolower(pathinfo((string) ($this->answer_key_file_path ?: $this->admin_file_path), PATHINFO_EXTENSION));
            if (in_array($ext, ['xlsx', 'xls', 'csv'], true) && !empty($this->answer_key_file_path)) {
                return self::GRADING_AUTO_EXCEL;
            }
            if (in_array($ext, ['pdf', 'docx', 'doc', 'txt'], true) && (!empty($this->rubric_json) || !empty($this->key_extracted_text) || !empty($this->answer_key_file_path))) {
                return self::GRADING_AUTO_AI_REPORT;
            }
        }

        if ($this->is_essay) {
            $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get();
            if (!empty($this->rubric_json) || $questions->contains(fn($q) => !empty(trim((string) $q->answer_key)))) {
                return self::GRADING_AUTO_AI_ESSAY;
            }
        }

        return self::GRADING_MANUAL;
    }

    public function getHasAutoGradingAttribute(): bool
    {
        if ($this->is_multiple_choice) {
            return true;
        }

        return in_array($this->resolvedGradingType(), [
            self::GRADING_AUTO_EXCEL,
            self::GRADING_AUTO_AI_ESSAY,
            self::GRADING_AUTO_AI_REPORT,
        ], true);
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
