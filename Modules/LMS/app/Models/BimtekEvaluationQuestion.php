<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BimtekEvaluationQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_evaluation_questions';

    protected $fillable = [
        'question',
        'description',
        'type',
        'min_label',
        'max_label',
        'scale_max',
        'section',
        'order',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'scale_max'   => 'integer',
        'order'       => 'integer',
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(BimtekEvaluationAnswer::class, 'question_id');
    }

    public function getAverageRatingAttribute(): float
    {
        if ($this->type !== 'scale') {
            return 0.0;
        }

        return round((float) $this->answers()->avg('rating'), 1);
    }
}
