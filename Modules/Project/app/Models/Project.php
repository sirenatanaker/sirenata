<?php

namespace Modules\Project\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\LMS\Models\Course;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'duration',
        'team_leader',
        'team_members',
        'type',
        'status',
        'sk_document',
        'created_by',
        'prerequisite_course_id',
        'prerequisite_course_ids',
        'is_prerequisite_active',
        'approved_at', 
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'team_members' => 'array',
        'prerequisite_course_ids' => 'array',
        'is_prerequisite_active' => 'boolean',
    ];

    /**
     * Accessor Status: Pengecekan otomatis 14 hari tanpa command/scheduler.
     */
    public function getStatusAttribute($value)
    {
        // Jika status di database 'Menunggu Tim' dan approved_at sudah terisi
        if ($value === 'Menunggu Tim' && $this->approved_at) {
            // Hitung selisih hari dari approved_at ke hari ini
            if (now()->diffInDays($this->approved_at) >= 14) {
                // Otomatis update status ke database saat data diakses
                $this->attributes['status'] = 'Kedaluwarsa';
                $this->saveQuietly();

                return 'Kedaluwarsa';
            }
        }

        return $value;
    }

    /**
     * Helper Atribut untuk mengambil sisa hari penentuan tim
     */
    public function getSisaHariPenentuanTimAttribute()
    {
        if ($this->status !== 'Menunggu Tim' || !$this->approved_at) {
            return 0;
        }

        $deadline = $this->approved_at->copy()->addDays(14);
        $sisaHari = now()->diffInDays($deadline, false);

        return $sisaHari > 0 ? (int) $sisaHari : 0;
    }

    public function leader()
    {
        return $this->belongsTo(\App\Models\User::class, 'team_leader');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function prerequisiteCourse()
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    public function prerequisiteCourseIds(): array
    {
        $courseIds = $this->prerequisite_course_ids ?? [];

        if (! is_array($courseIds)) {
            $courseIds = [$courseIds];
        }

        if ($this->prerequisite_course_id && ! in_array($this->prerequisite_course_id, $courseIds, true)) {
            $courseIds[] = $this->prerequisite_course_id;
        }

        return array_values(array_unique(array_filter($courseIds)));
    }

    public function getProgressAttribute()
    {
        if ($this->status === 'Draft' || !$this->start_date || !$this->end_date) {
            return 0;
        }

        $now = now();

        if ($now->lessThan($this->start_date)) {
            return 0;
        }

        if ($now->greaterThan($this->end_date)) {
            return 100;
        }

        $totalDuration = $this->start_date->diffInDays($this->end_date);
        $elapsedDuration = $this->start_date->diffInDays($now);

        if ($totalDuration <= 0) {
            return 100;
        }

        return round(($elapsedDuration / $totalDuration) * 100);
    }
}
