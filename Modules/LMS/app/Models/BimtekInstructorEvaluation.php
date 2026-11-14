<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'overall_score',
        'feedback',
    ];

    protected $casts = [
        'rating_materi'      => 'integer',
        'rating_penyampaian' => 'integer',
        'rating_fasilitasi'  => 'integer',
        'overall_score'      => 'float',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(BimtekEvaluationAnswer::class, 'evaluation_id');
    }

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
        if (!is_null($this->overall_score) && $this->overall_score > 0) {
            return round((float) $this->overall_score, 1);
        }

        // Cek jika ada jawaban di tabel answers
        $scaleAnswers = $this->relationLoaded('answers')
            ? $this->answers->whereNotNull('rating')
            : $this->answers()->whereNotNull('rating')->get();

        if ($scaleAnswers->isNotEmpty()) {
            return round((float) $scaleAnswers->avg('rating'), 1);
        }

        // Fallback ke kolom statis
        $legacySum = ($this->rating_materi ?? 0) + ($this->rating_penyampaian ?? 0) + ($this->rating_fasilitasi ?? 0);
        return $legacySum > 0 ? round($legacySum / 3, 1) : 0.0;
    }
}
