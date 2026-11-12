<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BimtekEventSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_event_sessions';

    protected $fillable = [
        'bimtek_event_id',
        'session_number',
        'title',
        'session_date',
        'start_time',
        'end_time',
        'zoom_url',
        'zoom_meeting_id',
        'zoom_passcode',
        'attendance_code',
        'attendance_start_at',
        'attendance_end_at',
    ];

    protected $casts = [
        'session_number'      => 'integer',
        'session_date'        => 'date',
        'start_time'          => 'datetime',
        'end_time'            => 'datetime',
        'attendance_start_at' => 'datetime',
        'attendance_end_at'   => 'datetime',
    ];

    public function bimtekEvent(): BelongsTo
    {
        return $this->belongsTo(BimtekEvent::class, 'bimtek_event_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(BimtekSessionAttendance::class, 'bimtek_event_session_id');
    }

    public function isAttendedBy(string $userId): bool
    {
        return $this->attendances()->where('user_id', $userId)->exists();
    }

    /**
     * URL Zoom efektif (fallback ke parent BimtekEvent jika di sesi kosong)
     */
    public function getEffectiveZoomUrlAttribute(): ?string
    {
        return $this->zoom_url ?: $this->bimtekEvent?->zoom_url;
    }

    public function getEffectiveMeetingIdAttribute(): ?string
    {
        return $this->zoom_meeting_id ?: $this->bimtekEvent?->zoom_meeting_id;
    }

    public function getEffectivePasscodeAttribute(): ?string
    {
        return $this->zoom_passcode ?: $this->bimtekEvent?->zoom_passcode;
    }

    public function getEffectiveAttendanceStartAtAttribute()
    {
        return $this->attendance_start_at ?? $this->start_time;
    }

    public function getEffectiveAttendanceEndAtAttribute()
    {
        return $this->attendance_end_at ?? $this->end_time;
    }

    public function getScheduleFormattedAttribute(): string
    {
        if (!$this->start_time) {
            return '-';
        }

        $start = $this->start_time;
        $end = $this->end_time;

        if (!$end) {
            return $start->translatedFormat('d M Y, H:i') . ' WIB';
        }

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('d M Y, H:i') . ' - ' . $end->format('H:i') . ' WIB';
        }

        return $start->translatedFormat('d M Y, H:i') . ' WIB s/d ' . $end->translatedFormat('d M Y, H:i') . ' WIB';
    }

    public function getAttendanceScheduleFormattedAttribute(): string
    {
        $start = $this->effective_attendance_start_at;
        $end = $this->effective_attendance_end_at;

        if (!$start && !$end) {
            return 'Mengikuti jadwal sesi Zoom';
        }

        if ($start && $end) {
            if ($start->isSameDay($end)) {
                return $start->translatedFormat('d M Y, H:i') . ' - ' . $end->format('H:i') . ' WIB';
            }
            return $start->translatedFormat('d M Y, H:i') . ' WIB s/d ' . $end->translatedFormat('d M Y, H:i') . ' WIB';
        }

        return ($start ?? $end)->translatedFormat('d M Y, H:i') . ' WIB';
    }

    public function getIsZoomEndedAttribute(): bool
    {
        return $this->end_time ? now()->greaterThan($this->end_time) : false;
    }

    public function getIsPresensiOpenAttribute(): bool
    {
        $start = $this->effective_attendance_start_at;
        $end = $this->effective_attendance_end_at;

        if (!$start && !$end) {
            return true;
        }

        $now = now();
        $isAfterStart = !$start || $now->greaterThanOrEqualTo($start);
        $isBeforeEnd  = !$end || $now->lessThanOrEqualTo($end);

        return $isAfterStart && $isBeforeEnd;
    }

    public function getIsPresensiEndedAttribute(): bool
    {
        $end = $this->effective_attendance_end_at;
        return $end ? now()->greaterThan($end) : false;
    }

    public function getIsPresensiUpcomingAttribute(): bool
    {
        $start = $this->effective_attendance_start_at;
        return $start ? now()->lessThan($start) : false;
    }
}
