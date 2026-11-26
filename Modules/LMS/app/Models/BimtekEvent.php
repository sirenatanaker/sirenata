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
        'zoom_attendance_code',
        'zoom_scheduled_at',
        'zoom_end_at',
        'zoom_attendance_start_at',
        'zoom_attendance_end_at',
        'submission_drive_link',
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
        'zoom_scheduled_at'        => 'datetime',
        'zoom_end_at'              => 'datetime',
        'zoom_attendance_start_at' => 'datetime',
        'zoom_attendance_end_at'   => 'datetime',
        'assessment_start_at'      => 'datetime',
        'assessment_end_at'     => 'datetime',
        'quota'                 => 'integer',
    ];

    public function getIsUnlimitedQuotaAttribute(): bool
    {
        return is_null($this->quota) || $this->quota <= 0;
    }

    /**
     * Scope event yang masih aktif (belum selesai secara status maupun melewati batas akhir evaluasi/pelaksanaan).
     */
    public function scopeNotCompleted($query, $now = null)
    {
        $now = $now ? \Carbon\Carbon::parse($now) : now();

        return $query->whereIn('status', ['open_registration', 'ongoing'])
            ->where(function ($sub) use ($now) {
                $sub->where(function ($s1) use ($now) {
                    $s1->whereNotNull('assessment_end_at')
                        ->where('assessment_end_at', '>=', $now);
                })->orWhere(function ($s2) use ($now) {
                    $s2->whereNull('assessment_end_at')
                        ->where(function ($s3) use ($now) {
                            $s3->whereNull('end_date')
                                ->orWhere('end_date', '>=', $now->copy()->subDays(3));
                        });
                });
            });
    }

    /**
     * Subjudul standar kartu gelombang: "Gelombang [nomor] [wilayah] [tahun]"
     */
    public function getFormattedWaveSubtitleAttribute(): string
    {
        $rawName = trim((string) ($this->batch_name ?? ''));
        $rawCode = trim((string) ($this->batch_code ?? ''));
        $rawLoc  = trim((string) ($this->location_name ?? ''));

        // Jika batch_name sudah mengikuti template standar "Gelombang [nomor] [wilayah] [tahun]"
        if (preg_match('/^Gelombang\s+(\d+)\s+(.+?)\s+(20\d{2})$/i', $rawName, $std)) {
            $num = (int) $std[1];
            $reg = trim($std[2]);
            $yr  = $std[3];
            return "Gelombang {$num} {$reg} {$yr}";
        }

        $romanToInt = [
            'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5,
            'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10,
        ];

        // 1. Nomor gelombang
        $waveNumber = 1;
        if (preg_match('/\b(?:Gelombang|Batch|Angkatan)\s+([0-9]+|[IVX]+)\b/i', $rawName, $m)) {
            $token = strtoupper($m[1]);
            $waveNumber = is_numeric($token) ? (int) $token : ($romanToInt[$token] ?? 1);
        } elseif (preg_match('/(?:[-_](?:B|A|G)([0-9]+)|[-_](I{1,3}|IV|VI{0,3}|IX|X))$/i', $rawCode, $m)) {
            $token = strtoupper(!empty($m[1]) ? $m[1] : $m[2]);
            $waveNumber = is_numeric($token) ? (int) $token : ($romanToInt[$token] ?? 1);
        }

        // 2. Wilayah (ambil dari location_name jika ada, atau deteksi dari batch_name, default Nasional)
        $region = 'Nasional';
        if ($rawLoc !== '') {
            $region = $rawLoc;
        } elseif (preg_match('/\b(Sumatera|Sumatra|Jawa|Kalimantan|Sulawesi|Papua|Bali|Maluku|Nusa Tenggara|Aceh|Jakarta|Bandung|Surabaya|Medan|Makassar|Yogyakarta|Semarang|Nasional)\b/i', $rawName, $m)) {
            $matched = strtolower($m[1]);
            $region = $matched === 'sumatra' ? 'Sumatera' : ucwords($matched);
        }

        // 3. Tahun
        $year = null;
        if (preg_match('/\b(20\d{2})\b/', $rawName, $m)) {
            $year = $m[1];
        } elseif ($this->start_date) {
            $year = $this->start_date->format('Y');
        } else {
            $year = now()->format('Y');
        }

        return "Gelombang {$waveNumber} {$region} {$year}";
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function certificateSubmissions(): HasMany
    {
        return $this->hasMany(CertificateSubmission::class, 'bimtek_event_id');
    }

    /**
     * Sesi Zoom harian (Multi-Sesi Pelaksanaan)
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(BimtekEventSession::class, 'bimtek_event_id')->orderBy('session_date')->orderBy('start_time');
    }

    /**
     * Rekap riwayat presensi sesi
     */
    public function sessionAttendances(): HasMany
    {
        return $this->hasMany(BimtekSessionAttendance::class, 'bimtek_event_id');
    }

    /**
     * Evaluasi narasumber & penyelenggaraan oleh peserta
     */
    public function instructorEvaluations(): HasMany
    {
        return $this->hasMany(BimtekInstructorEvaluation::class, 'bimtek_event_id');
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
                'zoom_joined_at',
                'zoom_attended_at',
                'certificate_file',
            ])
            ->withTimestamps();
    }

    /**
     * Hitung status event secara otomatis berdasarkan rentang periode yang ditentukan
     */
    public static function determineStatusFromDates(?string $regStart, ?string $regEnd, string $execStart, string $execEnd, ?string $evalEnd = null): string
    {
        $now = now();
        $rStart = $regStart ? \Carbon\Carbon::parse($regStart) : null;
        $rEnd   = $regEnd ? \Carbon\Carbon::parse($regEnd) : null;
        $eStart = \Carbon\Carbon::parse($execStart);
        $eEnd   = \Carbon\Carbon::parse($execEnd);
        $evEnd  = $evalEnd ? \Carbon\Carbon::parse($evalEnd) : $eEnd;

        // 1. Jika sudah melewati batas akhir evaluasi/pelaksanaan => selesai
        if ($now->greaterThan($evEnd)) {
            return 'completed';
        }

        // 2. Jika sudah masuk masa pelaksanaan atau setelah pendaftaran ditutup => ongoing
        if ($now->greaterThanOrEqualTo($eStart) || ($rEnd && $now->greaterThan($rEnd))) {
            return 'ongoing';
        }

        // 3. Jika sedang dalam masa pendaftaran => open_registration
        if ($rStart && $rEnd && $now->between($rStart, $rEnd)) {
            return 'open_registration';
        }

        // 4. Jika sebelum masa pendaftaran dibuka => draft
        if ($rStart && $now->lessThan($rStart)) {
            return 'draft';
        }

        return 'open_registration';
    }

    public function getStatusBadgeAttribute(): array
    {
        // Gunakan status yang disinkronkan dengan rentang tanggal saat ini
        $effectiveStatus = $this->status;
        if ($this->start_date && $this->end_date) {
            $effectiveStatus = self::determineStatusFromDates(
                $this->registration_start_at?->toDateTimeString(),
                $this->registration_end_at?->toDateTimeString(),
                $this->start_date->toDateTimeString(),
                $this->end_date->toDateTimeString(),
                $this->assessment_end_at?->toDateTimeString()
            );
        }

        return match ($effectiveStatus) {
            'draft'             => ['label' => 'Mendatang / Draft', 'class' => 'bg-slate-600 text-white'],
            'open_registration' => ['label' => 'Pendaftaran Buka', 'class' => 'bg-[#0B5CFF] text-white'],
            'ongoing'           => ['label' => 'Sedang Berlangsung', 'class' => 'bg-amber-500 text-white'],
            'completed'         => ['label' => 'Selesai', 'class' => 'bg-emerald-600 text-white'],
            default             => ['label' => ucfirst($effectiveStatus), 'class' => 'bg-slate-600 text-white'],
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

        if ($this->status === 'completed' || ($evalEnd && $now->greaterThan($evalEnd))) {
            return [
                'phase'      => 'completed',
                'step'       => 3,
                'label'      => 'Selesai',
                'badge'      => 'Selesai',
                'badge_bg'   => 'bg-emerald-600',
                'icon'       => 'fa-check-circle',
                'date_range' => ($execStart ? $execStart->translatedFormat('d M') : '-') . ' - ' . ($evalEnd ? $evalEnd->translatedFormat('d M Y') : ($execEnd ? $execEnd->translatedFormat('d M Y') : '-')),
                'is_active'  => false,
            ];
        }

        if ($evalStart && $evalEnd && $now->between($evalStart, $evalEnd)) {
            return [
                'phase'      => 'assessment',
                'step'       => 3,
                'label'      => 'Evaluasi & Sertifikasi',
                'badge'      => 'Masa Evaluasi',
                'badge_bg'   => 'bg-teal-600',
                'icon'       => 'fa-clock',
                'date_range' => $evalStart->translatedFormat('d M') . ' - ' . $evalEnd->translatedFormat('d M Y'),
                'is_active'  => true,
            ];
        }

        if (($execStart && $execEnd && $now->between($execStart, $execEnd)) || $this->status === 'ongoing') {
            return [
                'phase'      => 'learning',
                'step'       => 2,
                'label'      => 'Pelaksanaan Pelatihan',
                'badge'      => 'Sedang Berlangsung',
                'badge_bg'   => 'bg-amber-500',
                'icon'       => 'fa-clock',
                'date_range' => ($execStart ? $execStart->translatedFormat('d M') : '-') . ' - ' . ($execEnd ? $execEnd->translatedFormat('d M Y') : '-'),
                'is_active'  => true,
            ];
        }

        if ($regStart && $regEnd && $now->between($regStart, $regEnd)) {
            return [
                'phase'      => 'registration',
                'step'       => 1,
                'label'      => 'Pendaftaran',
                'badge'      => 'Pendaftaran Buka',
                'badge_bg'   => 'bg-[#0B5CFF]',
                'icon'       => 'fa-clock',
                'date_range' => $regStart->translatedFormat('d M') . ' - ' . $regEnd->translatedFormat('d M Y'),
                'is_active'  => true,
            ];
        }

        return [
            'phase'      => 'registration',
            'step'       => 1,
            'label'      => 'Pendaftaran',
            'badge'      => 'Pendaftaran Buka',
            'badge_bg'   => 'bg-[#0B5CFF]',
            'icon'       => 'fa-clock',
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
                'title'       => 'Pelaksanaan',
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
                'title'       => 'Evaluasi Akhir',
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

    /**
     * Waktu mulai presensi efektif (menggunakan jam presensi khusus jika ditentukan)
     */
    public function getEffectiveAttendanceStartAtAttribute()
    {
        return $this->zoom_attendance_start_at ?? $this->zoom_scheduled_at ?? $this->start_date;
    }

    /**
     * Waktu selesai presensi efektif
     */
    public function getEffectiveAttendanceEndAtAttribute()
    {
        return $this->zoom_attendance_end_at ?? $this->zoom_end_at;
    }

    /**
     * Format rentang waktu pembukaan presensi
     */
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

    /**
     * Tanggal mulai program keseluruhan (pendaftaran / pelaksanaan)
     */
    public function getOverallStartAtAttribute()
    {
        return $this->registration_start_at ?? $this->start_date;
    }

    /**
     * Tanggal selesai program keseluruhan (evaluasi / pelaksanaan)
     */
    public function getOverallEndAtAttribute()
    {
        return $this->assessment_end_at ?? $this->end_date;
    }

    /**
     * Rentang tanggal program keseluruhan dalam format teks
     */
    public function getOverallTimelineFormattedAttribute(): string
    {
        $start = $this->overall_start_at;
        $end = $this->overall_end_at;

        if (!$start && !$end) {
            return 'Jadwal belum ditentukan';
        }

        if ($start && $end) {
            if ($start->isSameDay($end)) {
                return $start->translatedFormat('d F Y, H:i') . ' - ' . $end->translatedFormat('H:i') . ' WIB';
            }
            return $start->translatedFormat('d F Y') . ' s/d ' . $end->translatedFormat('d F Y');
        }

        return ($start ?? $end)->translatedFormat('d F Y');
    }
}
