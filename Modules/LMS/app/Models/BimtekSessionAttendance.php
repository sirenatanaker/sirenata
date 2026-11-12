<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BimtekSessionAttendance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_session_attendances';

    protected $fillable = [
        'bimtek_event_session_id',
        'bimtek_event_id',
        'user_id',
        'course_id',
        'attended_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(BimtekEventSession::class, 'bimtek_event_session_id');
    }

    public function bimtekEvent(): BelongsTo
    {
        return $this->belongsTo(BimtekEvent::class, 'bimtek_event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
