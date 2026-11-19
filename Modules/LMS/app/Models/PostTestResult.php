<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostTestResult extends Model
{
    use HasUuids;

    public const GRADING_PENDING = 'PENDING';
    public const GRADING_QUEUED = 'QUEUED';
    public const GRADING_PROCESSING = 'PROCESSING';
    public const GRADING_DRAFT_GRADED = 'DRAFT_GRADED';
    public const GRADING_MANUAL_REVIEW = 'MANUAL_REVIEW';
    public const GRADING_FAILED = 'FAILED';
    public const GRADING_PUBLISHED = 'PUBLISHED';

    protected $table = 'post_test_results';

    protected $fillable = [
        'user_id',
        'post_test_id',
        'grading_batch_id',
        'score',
        'ai_score',
        'final_score',
        'is_passed',
        'completed_at',
        'answers',
        'file_path',
        'file_name',
        'extracted_text',
        'is_truncated',
        'notes',
        'ai_feedback_json',
        'needs_manual_review',
        'retry_count',
        'last_error_message',
        'graded_at',
        'status',
        'grading_status',
    ];

    protected $casts = [
        'is_passed'           => 'boolean',
        'is_truncated'        => 'boolean',
        'needs_manual_review' => 'boolean',
        'ai_score'            => 'float',
        'final_score'         => 'float',
        'retry_count'         => 'integer',
        'completed_at'        => 'datetime',
        'graded_at'           => 'datetime',
        'answers'             => 'array',
        'ai_feedback_json'    => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function postTest(): BelongsTo
    {
        return $this->belongsTo(PostTest::class);
    }

    public function gradingBatch(): BelongsTo
    {
        return $this->belongsTo(GradingBatch::class, 'grading_batch_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getEffectiveScoreAttribute(): ?float
    {
        if (!is_null($this->final_score)) {
            return (float) $this->final_score;
        }
        if (!is_null($this->ai_score)) {
            return (float) $this->ai_score;
        }

        return $this->grading_status === self::GRADING_PUBLISHED || $this->status === 'graded'
            ? (float) $this->score
            : null;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->grading_status === self::GRADING_PUBLISHED;
    }

    public function getIsDraftGradedAttribute(): bool
    {
        return $this->grading_status === self::GRADING_DRAFT_GRADED;
    }
}
