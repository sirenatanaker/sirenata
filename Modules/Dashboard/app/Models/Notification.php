<?php

namespace Modules\Dashboard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notifikasi in-app untuk Admin Provinsi & Admin Kab/Kota.
 * Dibuat oleh listener saat kejadian (persetujuan/verifikasi) terjadi,
 * lalu disajikan ke navbar — tanpa koneksi streaming.
 */
class Notification extends Model
{
    use HasUuids;

    /**
     * Tipe notifikasi + meta tampilan (ikon, warna tile, label grup).
     *
     * Makna ikon sesuai kejadiannya:
     *  - Proyek disetujui   → diagram proyek (indigo)
     *  - Proyek selesai     → bendera finis  (teal)
     *  - RTKD diverifikasi  → centang ganda  (biru)    ✔ sudah dicek
     *  - RTKD disetujui     → lingkaran centang (hijau) ✔ disetujui
     *  - RTKD ditolak       → lingkaran silang (merah)  ✖ ditolak
     */
    public const TYPES = [
        'project.approved' => [
            'label' => 'Proyek',
            'icon'  => 'fas fa-diagram-project',
            'tile'  => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
        ],
        'project.completed' => [
            'label' => 'Proyek',
            'icon'  => 'fas fa-flag-checkered',
            'tile'  => 'bg-teal-50 text-teal-600 ring-teal-100',
        ],
        'rtkd.verified' => [
            'label' => 'Verifikasi RTKD',
            'icon'  => 'fas fa-check-double',
            'tile'  => 'bg-blue-50 text-blue-600 ring-blue-100',
        ],
        'rtkd.approved' => [
            'label' => 'Persetujuan RTKD',
            'icon'  => 'fas fa-circle-check',
            'tile'  => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        ],
        'rtkd.rejected' => [
            'label' => 'RTKD Ditolak',
            'icon'  => 'fas fa-circle-xmark',
            'tile'  => 'bg-rose-50 text-rose-600 ring-rose-100',
        ],
        'course.published' => [
            'label' => 'Katalog Baru',
            'icon'  => 'fa-regular fa-graduation-cap',
            'tile'  => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        ],
        'library.published' => [
            'label' => 'Perpustakaan',
            'icon'  => 'fa-regular fa-bookmark',
            'tile'  => 'bg-sky-50 text-sky-600 ring-sky-100',
        ],
        'system.general' => [
            'label' => 'Sistem',
            'icon'  => 'fas fa-bell',
            'tile'  => 'bg-slate-100 text-slate-600 ring-slate-200',
        ],
    ];

    protected $table = 'user_notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'meta',
        'read_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'meta'    => 'array',
    ];

    /* ------------------------------------------------------------------ */
    /* Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Meta tampilan sebuah tipe notifikasi (dipakai blade & payload JSON).
     *
     * @return array{label: string, icon: string, tile: string}
     */
    public static function typeMeta(string $type): array
    {
        return self::TYPES[$type] ?? self::TYPES['system.general'];
    }

    /**
     * Waktu relatif berbahasa Indonesia (APP_LOCALE = en, jadi manual).
     */
    public function getTimeAgoAttribute(): string
    {
        if (! $this->created_at) {
            return '-';
        }

        $seconds = abs(now()->diffInSeconds($this->created_at));

        if ($seconds < 60) {
            return 'Baru saja';
        }

        if ($seconds < 3600) {
            $minutes = (int) floor($seconds / 60);
            return "{$minutes} menit lalu";
        }

        if ($seconds < 86400) {
            $hours = (int) floor($seconds / 3600);
            return "{$hours} jam lalu";
        }

        if ($seconds < 604800) {
            $days = (int) floor($seconds / 86400);
            return "{$days} hari lalu";
        }

        return $this->created_at->format('d M Y');
    }

    /**
     * URL Thumbnail untuk notifikasi (misal thumbnail kursus katalog baru).
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!empty($this->meta['thumbnail'])) {
            return $this->meta['thumbnail'];
        }

        if ($this->type === 'course.published') {
            $courseId = $this->meta['course_id'] ?? null;
            if ($courseId) {
                try {
                    $course = \Modules\LMS\Models\Course::find($courseId);
                    if ($course && $course->thumbnail) {
                        return str_starts_with($course->thumbnail, 'http')
                            ? $course->thumbnail
                            : asset('storage/' . $course->thumbnail);
                    }
                } catch (\Throwable $e) {
                }
            }
            $name = $this->meta['course_name'] ?? $this->title ?? 'Kursus';
            return 'https://ui-avatars.com/api/?name=' . urlencode(substr($name, 0, 2)) . '&background=13416B&color=fff&size=512&bold=true&length=2';
        }

        if ($this->type === 'library.published') {
            $libraryId = $this->meta['library_id'] ?? null;
            if ($libraryId) {
                try {
                    $library = \Modules\LMS\Models\Library::find($libraryId);
                    if ($library && $library->cover_image_url) {
                        return $library->cover_image_url;
                    }
                } catch (\Throwable $e) {
                }
            }
            $name = $this->meta['library_title'] ?? $this->title ?? 'Perpustakaan';
            return 'https://ui-avatars.com/api/?name=' . urlencode(substr($name, 0, 2)) . '&background=547996&color=fff&size=512&bold=true&length=2';
        }

        return null;
    }

    /**
     * Deskripsi ringkas notifikasi (mengutamakan deskripsi kursus/perpustakaan bila ada).
     */
    public function getDescriptionAttribute(): string
    {
        if (!empty($this->meta['description'])) {
            return $this->meta['description'];
        }

        if ($this->type === 'course.published') {
            $courseId = $this->meta['course_id'] ?? null;
            if ($courseId) {
                try {
                    $desc = \Modules\LMS\Models\Course::where('id', $courseId)->value('description');
                    if ($desc) {
                        return strip_tags($desc);
                    }
                } catch (\Throwable $e) {
                }
            }
        } elseif ($this->type === 'library.published') {
            $libraryId = $this->meta['library_id'] ?? null;
            if ($libraryId) {
                try {
                    $desc = \Modules\LMS\Models\Library::where('id', $libraryId)->value('description');
                    if ($desc) {
                        return strip_tags($desc);
                    }
                } catch (\Throwable $e) {
                }
            }
        }

        return $this->message ?? '';
    }

    /**
     * Bentuk data siap kirim (dipakai render awal server maupun
     * endpoint refresh `notifications.latest`).
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $meta = self::typeMeta($this->type);

        $title = $this->title;
        if ($this->type === 'course.published') {
            $title = $this->meta['course_name'] ?? preg_replace('/^Katalog (Baru|Kursus Baru):\s*/i', '', $this->title);
        } elseif ($this->type === 'library.published') {
            $title = $this->meta['library_title'] ?? preg_replace('/^Koleksi (Baru|Perpustakaan Baru):\s*/i', '', $this->title);
        }

        $section = match ($this->type) {
            'course.published'  => 'katalog',
            'library.published' => 'perpustakaan',
            default             => 'lainnya',
        };

        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'section'     => $section,
            'group'       => $this->meta['group'] ?? $meta['label'],
            'icon'        => $this->meta['icon'] ?? $meta['icon'],
            'tile'        => $this->meta['tile'] ?? $meta['tile'],
            'title'       => $title,
            'description' => $this->description,
            'message'     => $this->message,
            'thumbnail'   => $this->thumbnail_url,
            'link'        => $this->link,
            'time'        => $this->time_ago,
            'read'        => $this->read_at !== null,
            'created'     => $this->created_at?->toIso8601String(),
        ];
    }

    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return true;
        }

        return (bool) $this->update(['read_at' => now()]);
    }

    /* ------------------------------------------------------------------ */
    /* Relations                                                           */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
