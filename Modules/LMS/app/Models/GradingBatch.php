<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingBatch extends Model
{
    use HasUuids;

    public const STATUS_RUNNING = 'RUNNING';
    public const STATUS_PAUSED_QUOTA = 'PAUSED_QUOTA';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_COMPLETED_WITH_ERRORS = 'COMPLETED_WITH_ERRORS';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'grading_batches';

    protected $fillable = [
        'post_test_id',
        'bimtek_event_id',
        'triggered_by',
        'rubric_snapshot_json',
        'total_targets',
        'processed_count',
        'failed_count',
        'consecutive_429_count',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'rubric_snapshot_json'  => 'array',
        'total_targets'         => 'integer',
        'processed_count'       => 'integer',
        'failed_count'          => 'integer',
        'consecutive_429_count' => 'integer',
        'started_at'            => 'datetime',
        'finished_at'           => 'datetime',
    ];

    public function postTest(): BelongsTo
    {
        return $this->belongsTo(PostTest::class);
    }

    public function bimtekEvent(): BelongsTo
    {
        return $this->belongsTo(BimtekEvent::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(PostTestResult::class, 'grading_batch_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_targets <= 0) {
            return 100;
        }

        $done = min($this->total_targets, $this->processed_count + $this->failed_count);

        return (int) round(($done / $this->total_targets) * 100);
    }

    public function getIsRunningAttribute(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function getIsPausedQuotaAttribute(): bool
    {
        return $this->status === self::STATUS_PAUSED_QUOTA;
    }
}
