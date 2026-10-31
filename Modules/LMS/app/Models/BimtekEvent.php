<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BimtekEvent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bimtek_events';

    protected $fillable = [
        'course_id',
        'batch_name',
        'batch_code',
        'execution_type',
        'start_date',
        'end_date',
        'quota',
        'registration_start_at',
        'registration_end_at',
        'zoom_url',
        'zoom_meeting_id',
        'zoom_passcode',
        'zoom_scheduled_at',
        'zoom_end_at',
        'assessment_start_at',
        'assessment_end_at',
        'location_name',
        'location_address',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date'            => 'datetime',
        'end_date'              => 'datetime',
        'registration_start_at' => 'datetime',
        'registration_end_at'   => 'datetime',
        'zoom_scheduled_at'     => 'datetime',
        'zoom_end_at'           => 'datetime',
        'assessment_start_at'   => 'datetime',
        'assessment_end_at'     => 'datetime',
        'quota'                 => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function certificateSubmissions(): HasMany
    {
        return $this->hasMany(CertificateSubmission::class, 'bimtek_event_id');
    }

    /**
     * Peserta yang terdaftar pada batch bimtek ini lewat pivot course_student
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student', 'bimtek_event_id', 'user_id')
            ->withPivot([
                'course_id',
                'progress',
                'status',
                'completed_at',
                'certificate_code',
                'official_cert_number',
                'cert_submission_status',
                'zoom_attended',
                'zoom_attended_at',
                'certificate_file',
            ])
            ->withTimestamps();
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft'             => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
            'open_registration' => ['label' => 'Pendaftaran Buka', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'ongoing'           => ['label' => 'Sedang Berlangsung', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'completed'         => ['label' => 'Selesai', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
            default             => ['label' => ucfirst($this->status), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
        };
    }

    /**
     * Dapatkan detail fase timeline yang sedang aktif saat ini
     */
    public function getCurrentTimelinePhaseAttribute(): array
    {
        $now = now();

        $regStart = $this->registration_start_at ?? $this->start_date?->copy()->subDays(7);
        $regEnd   = $this->registration_end_at ?? $this->start_date?->copy()->subDay();

        $execStart = $this->start_date;
        $execEnd   = $this->end_date;

        $evalStart = $this->assessment_start_at ?? $this->end_date;
        $evalEnd   = $this->assessment_end_at ?? $this->end_date?->copy()->addDays(3);

        if ($regStart && $regEnd && $now->between($regStart, $regEnd)) {
            return [
                'phase'      => 'registration',
                'step'       => 1,
                'label'      => 'Pendaftaran',
                'badge'      => 'Pendaftaran Buka',
                'badge_bg'   => 'bg-emerald-600',
                'icon'       => 'fa-calendar-check',
                'date_range' => $regStart->translatedFormat('d M') . ' - ' . $regEnd->translatedFormat('d M Y'),
                'is_active'  => true,
            ];
        }

        if ($execStart && $execEnd && $now->between($execStart, $execEnd)) {
            return [
                'phase'      => 'learning',
                'step'       => 2,
                'label'      => 'Pelaksanaan Pelatihan',
                'badge'      => 'Sedang Berlangsung',
                'badge_bg'   => 'bg-blue-600',
                'icon'       => 'fa-clock',
                'date_range' => $execStart->translatedFormat('d M') . ' - ' . $execEnd->translatedFormat('d M Y'),
                'is_active'  => true,
            ];
        }

        if ($evalStart && $evalEnd && $now->between($evalStart, $evalEnd)) {
            return [
                'phase'      => 'assessment',
                'step'       => 3,
                'label'      => 'Evaluasi & Sertifikasi',
                'badge'      => 'Masa Evaluasi',
                'badge_bg'   => 'bg-purple-600',
                'icon'       => 'fa-clipboard-check',
                'date_range' => $evalStart->translatedFormat('d M') . ' - ' . $evalEnd->translatedFormat('d M Y'),
                'is_active'  => true,
            ];
        }

        if ($evalEnd && $now->greaterThan($evalEnd)) {
            return [
                'phase'      => 'completed',
                'step'       => 3,
                'label'      => 'Selesai',
                'badge'      => 'Pelatihan Selesai',
                'badge_bg'   => 'bg-slate-600',
                'icon'       => 'fa-check-double',
                'date_range' => ($execStart ? $execStart->translatedFormat('d M') : '-') . ' - ' . $evalEnd->translatedFormat('d M Y'),
                'is_active'  => false,
            ];
        }

        return [
            'phase'      => 'registration',
            'step'       => 1,
            'label'      => 'Pendaftaran',
            'badge'      => 'Pendaftaran Buka',
            'badge_bg'   => 'bg-emerald-600',
            'icon'       => 'fa-calendar-check',
            'date_range' => ($regStart ? $regStart->translatedFormat('d M') : '-') . ' - ' . ($regEnd ? $regEnd->translatedFormat('d M Y') : '-'),
            'is_active'  => true,
        ];
    }

    /**
     * Dapatkan ketiga tahapan timeline untuk tampilan detail
     */
    public function getTimelineStepsAttribute(): array
    {
        $now = now();

        $regStart  = $this->registration_start_at ?? $this->start_date?->copy()->subDays(7);
        $regEnd    = $this->registration_end_at ?? $this->start_date?->copy()->subDay();
        $execStart = $this->start_date;
        $execEnd   = $this->end_date;
        $evalStart = $this->assessment_start_at ?? $this->end_date;
        $evalEnd   = $this->assessment_end_at ?? $this->end_date?->copy()->addDays(3);

        $calcStatus = function ($start, $end) use ($now) {
            if (!$start || !$end) return 'upcoming';
            if ($now->greaterThan($end)) return 'completed';
            if ($now->between($start, $end)) return 'active';
            return 'upcoming';
        };

        return [
            [
                'step'        => 1,
                'title'       => 'Pendaftaran & Registrasi',
                'subtitle'    => 'Pendaftaran gelombang peserta Pelatihan',
                'icon'        => 'fa-user-check',
                'start_date'  => $regStart ? $regStart->translatedFormat('d M Y') : '-',
                'end_date'    => $regEnd ? $regEnd->translatedFormat('d M Y') : '-',
                'date_range'  => ($regStart ? $regStart->translatedFormat('d M') : '-') . ' s/d ' . ($regEnd ? $regEnd->translatedFormat('d M Y') : '-'),
                'status'      => $calcStatus($regStart, $regEnd),
            ],
            [
                'step'        => 2,
                'title'       => 'Pembelajaran Mandiri & Live Zoom',
                'subtitle'    => 'Pelajari materi topik di LMS dan ikuti tatap muka virtual Zoom interaktif',
                'icon'        => 'fa-video',
                'start_date'  => $execStart ? $execStart->translatedFormat('d M Y') : '-',
                'end_date'    => $execEnd ? $execEnd->translatedFormat('d M Y') : '-',
                'zoom_time'   => $this->zoom_schedule_formatted,
                'date_range'  => ($execStart ? $execStart->translatedFormat('d M') : '-') . ' s/d ' . ($execEnd ? $execEnd->translatedFormat('d M Y') : '-'),
                'status'      => $calcStatus($execStart, $execEnd),
            ],
            [
                'step'        => 3,
                'title'       => 'Evaluasi Akhir & Sertifikasi PPSDM',
                'subtitle'    => 'Pengerjaan evaluasi akhir dan proses penerbitan e-Sertifikat resmi',
                'icon'        => 'fa-award',
                'start_date'  => $evalStart ? $evalStart->translatedFormat('d M Y') : '-',
                'end_date'    => $evalEnd ? $evalEnd->translatedFormat('d M Y') : '-',
                'date_range'  => ($evalStart ? $evalStart->translatedFormat('d M') : '-') . ' s/d ' . ($evalEnd ? $evalEnd->translatedFormat('d M Y') : '-'),
                'status'      => $calcStatus($evalStart, $evalEnd),
            ],
        ];
    }

    /**
     * Dapatkan format jadwal spesifik tatap muka online Zoom
     */
    public function getZoomScheduleFormattedAttribute(): string
    {
        if (!$this->zoom_scheduled_at) {
            return $this->start_date
                ? $this->start_date->translatedFormat('d M Y, H:i') . ' WIB'
                : 'Jadwal belum ditentukan';
        }

        $start = $this->zoom_scheduled_at;
        $end = $this->zoom_end_at;

        if (!$end) {
            return $start->translatedFormat('d M Y, H:i') . ' WIB';
        }

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('d M Y, H:i') . ' - ' . $end->format('H:i') . ' WIB';
        }

        return $start->translatedFormat('d M Y, H:i') . ' WIB s/d ' . $end->translatedFormat('d M Y, H:i') . ' WIB';
    }
}
