<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LMS\Models\PostTestChoice;

class PostTestQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'post_test_id',
        'question',
        'answer_key',
    ];

    public function postTest(): BelongsTo
    {
        return $this->belongsTo(PostTest::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(PostTestChoice::class);
    }
}