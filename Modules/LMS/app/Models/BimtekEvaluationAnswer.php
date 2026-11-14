<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BimtekEvaluationAnswer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_evaluation_answers';

    protected $fillable = [
        'evaluation_id',
        'question_id',
        'rating',
        'text_answer',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(BimtekInstructorEvaluation::class, 'evaluation_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(BimtekEvaluationQuestion::class, 'question_id');
    }
}
