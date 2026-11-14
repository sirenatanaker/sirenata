<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BimtekInstructorEvaluation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_instructor_evaluations';

    protected $fillable = [
        'bimtek_event_id',
        'course_id',
        'user_id',
        'rating_materi',
        'rating_penyampaian',
        'rating_fasilitasi',
        'feedback',
    ];

    protected $casts = [
        'rating_materi' => 'integer',
        'rating_penyampaian' => 'integer',
        'rating_fasilitasi' => 'integer',
    ];

    public function bimtekEvent(): BelongsTo
    {
        return $this->belongsTo(BimtekEvent::class, 'bimtek_event_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Hitung rata-rata skor aspek (1.0 - 5.0)
     */
    public function getAverageRatingAttribute(): float
    {
        return round(($this->rating_materi + $this->rating_penyampaian + $this->rating_fasilitasi) / 3, 1);
    }
}
