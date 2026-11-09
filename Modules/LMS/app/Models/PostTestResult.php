<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class PostTestResult extends Model
{
    use HasUuids;

    protected $table = 'post_test_results';

    protected $fillable = [
        'user_id',
        'post_test_id',
        'score',
        'is_passed',
        'completed_at',
        'answers',
        'file_path',
        'file_name',
        'notes',
        'status',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function postTest(): BelongsTo
    {
        return $this->belongsTo(PostTest::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}
