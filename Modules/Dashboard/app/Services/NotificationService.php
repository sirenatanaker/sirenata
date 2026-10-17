<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Models\Notification;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Throwable;

/**
 * Pembuat notifikasi untuk Admin Provinsi & Admin Kab/Kota.
 *
 * Fokus konteks:
 *  1. Proyek daerah (Kab/Kota & Provinsi) disetujui Admin Pusat
 *  2. RTKD Kab/Kota & Provinsi sudah diverifikasi
 *  3. RTKD Kab/Kota & Provinsi sudah disetujui (dokumen berlaku)
 *  4. RTKD Kab/Kota & Provinsi ditolak (bonus, tetap satu alur)
 *
 * Seluruh proses pembuatan notifikasi dibungkus try/catch agar
 * kegagalan notifikasi tidak pernah merusak alur persetujuan.
 */
class NotificationService
{
    public const PROJECT_APPROVED = 'project.approved';
    public const PROJECT_COMPLETED = 'project.completed';
    public const RTKD_VERIFIED = 'rtkd.verified';
    public const RTKD_APPROVED = 'rtkd.approved';
    public const RTKD_REJECTED = 'rtkd.rejected';
    public const COURSE_PUBLISHED = 'course.published';
    public const LIBRARY_PUBLISHED = 'library.published';

    private const RECIPIENT_ROLES = ['admin-province', 'admin-kab-kota'];

    /* ------------------------------------------------------------------ */
    /* PROYEK                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Beri tahu pemilik & sesama admin wilayah bahwa proyek daerah disetujui.
     *
     * @param  int|string  $projectId
     * @param  string  $projectType  'Provinsi' | 'Kab/Kota' | 'Nasional'
     */
    public function projectApproved(
        int|string $projectId,
        string $projectName,
        string $projectType,
        ?string $creatorId = null,
        string $previousStatus = 'Draft',
        string $newStatus = 'On Progress',
        ?string $actorId = null,
    ): void {
        try {
            $isCompleted = $newStatus === 'Completed';

            $title = $isCompleted ? 'Proyek Selesai' : 'Proyek Disetujui';
            $message = $isCompleted
                ? "Proyek \"{$projectName}\" telah ditandai selesai oleh Admin Pusat."
                : "Proyek \"{$projectName}\" telah disetujui Admin Pusat dan siap dilaksanakan. Silakan lengkapi ketua serta anggota tim.";

            foreach ($this->projectRecipients($projectType, $creatorId, $actorId) as $user) {
                $this->push(
                    user: $user,
                    type: $isCompleted ? self::PROJECT_COMPLETED : self::PROJECT_APPROVED,
                    title: $title,
                    message: $message,
                    link: $this->projectLink($user, $projectName),
                    meta: [
                        'project_id'   => (string) $projectId,
                        'project_type' => $projectType,
                        'group'        => $isCompleted ? 'Proyek Selesai' : 'Proyek Disetujui',
                        'actor_id'     => $actorId,
                    ],
                );
            }
        } catch (Throwable $e) {
            Log::warning('Notifikasi persetujuan proyek gagal dibuat: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* RTKD                                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Notifikasi status RTKD (kab/kota & provinsi).
     *
     * @param  string  $event  verified | approved | rejected
     * @param  string  $rtkType  'Kab/Kota' | 'Provinsi'
     */
    public function rtkdStatus(
        string $event,
        string $rtkName,
        string $rtkType,
        ?string $provinceCode = null,
        ?string $regencyCode = null,
        ?string $creatorId = null,
        ?string $reason = null,
        ?string $actorId = null,
    ): void {
        $type = match ($event) {
            'verified' => self::RTKD_VERIFIED,
            'approved' => self::RTKD_APPROVED,
            'rejected' => self::RTKD_REJECTED,
            default    => 'system.general',
        };

        try {
            $scopeName = $this->scopeName($provinceCode, $regencyCode);
            $where = $scopeName ? " ({$scopeName})" : '';

            [$title, $message] = match ($event) {
                'verified' => [
                    'RTKD Diverifikasi',
                    "RTKD \"{$rtkName}\"{$where} telah diverifikasi dan menunggu persetujuan dokumen.",
                ],
                'approved' => [
                    'RTKD Disetujui',
                    "RTKD \"{$rtkName}\"{$where} telah disetujui dan dokumennya kini berstatus berlaku.",
                ],
                'rejected' => [
                    'RTKD Ditolak',
                    "RTKD \"{$rtkName}\"{$where} ditolak." .
                        ($reason ? " Alasan: {$reason}" : ''),
                ],
                default => ['Pembaruan RTKD', "RTKD \"{$rtkName}\"{$where} mengalami pembaruan status."],
            };

            $group = match ($event) {
                'verified' => 'Verifikasi RTKD',
                'approved' => 'Persetujuan RTKD',
                'rejected' => 'RTKD Ditolak',
                default    => Notification::typeMeta($type)['label'],
            };

            foreach ($this->rtkdRecipients($provinceCode, $regencyCode, $creatorId, $actorId) as $user) {
                $this->push(
                    user: $user,
                    type: $type,
                    title: $title,
                    message: $message,
                    link: $this->rtkdLink($user, $rtkName),
                    meta: [
                        'rtk_type'     => $rtkType,
                        'province'     => $provinceCode,
                        'regency'      => $regencyCode,
                        'scope_name'   => $scopeName,
                        'group'        => $group,
                        'actor_id'     => $actorId,
                    ],
                );
            }
        } catch (Throwable $e) {
            Log::warning('Notifikasi RTKD gagal dibuat: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* KATALOG KURSUS                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Beritahu semua user saat ada katalog kursus baru ditambahkan oleh admin.
     *
     * @param  mixed  $course
     */
    public function coursePublished($course): void
    {
        try {
            $users = User::role('user')->select('id')->get();
            if ($users->isEmpty()) {
                return;
            }

            $courseName = $course->name ?? 'Kursus Baru';
            $title = $courseName;
            $message = "Kursus baru \"{$courseName}\" telah ditambahkan ke katalog pembelajaran.";
            $link = route('user.catalog.index');

            $thumbnailUrl = null;
            if (!empty($course->thumbnail)) {
                $thumbnailUrl = str_starts_with($course->thumbnail, 'http')
                    ? $course->thumbnail
                    : asset('storage/' . $course->thumbnail);
            } else {
                $thumbnailUrl = 'https://ui-avatars.com/api/?name=' . urlencode(substr($courseName, 0, 2)) . '&background=13416B&color=fff&size=200';
            }

            $notifications = [];
            $now = now();
            $metaJson = json_encode([
                'course_id'   => (string) ($course->id ?? ''),
                'course_name' => $courseName,
                'thumbnail'   => $thumbnailUrl,
                'group'       => 'Katalog Baru',
                'icon'        => 'fa-regular fa-graduation-cap',
                'tile'        => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
            ]);

            foreach ($users as $user) {
                $notifications[] = [
                    'id'         => (string) \Illuminate\Support\Str::uuid(),
                    'user_id'    => $user->id,
                    'type'       => self::COURSE_PUBLISHED,
                    'title'      => $title,
                    'message'    => $message,
                    'link'       => $link,
                    'meta'       => $metaJson,
                    'read_at'    => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($notifications, 250) as $chunk) {
                Notification::insert($chunk);
            }
        } catch (Throwable $e) {
            Log::warning('Notifikasi kursus baru gagal dibuat: ' . $e->getMessage());
        }
    }

    /**
     * Beritahu semua user saat ada koleksi perpustakaan baru ditambahkan oleh admin.
     *
     * @param  mixed  $library
     */
    public function libraryPublished($library): void
    {
        try {
            $users = User::role('user')->select('id')->get();
            if ($users->isEmpty()) {
                return;
            }

            $libraryTitle = $library->title ?? 'Koleksi Baru';
            $title = $libraryTitle;
            $message = "Koleksi perpustakaan \"{$libraryTitle}\" baru saja ditambahkan.";
            $link = route('user.library.index') . '?open=' . ($library->id ?? '');

            $thumbnailUrl = null;
            if (!empty($library->cover_image_url)) {
                $thumbnailUrl = $library->cover_image_url;
            } elseif (!empty($library->cover_image)) {
                $thumbnailUrl = filter_var($library->cover_image, FILTER_VALIDATE_URL)
                    ? $library->cover_image
                    : asset('storage/' . $library->cover_image);
            } else {
                $thumbnailUrl = 'https://ui-avatars.com/api/?name=' . urlencode(substr($libraryTitle, 0, 2)) . '&background=547996&color=fff&size=512&bold=true&length=2';
            }

            $notifications = [];
            $now = now();
            $metaJson = json_encode([
                'library_id'    => (string) ($library->id ?? ''),
                'library_title' => $libraryTitle,
                'thumbnail'     => $thumbnailUrl,
                'group'         => 'Perpustakaan',
                'section'       => 'perpustakaan',
                'icon'          => 'fa-regular fa-bookmark',
                'tile'          => 'bg-sky-50 text-sky-600 ring-sky-100',
            ]);

            foreach ($users as $user) {
                $notifications[] = [
                    'id'         => (string) \Illuminate\Support\Str::uuid(),
                    'user_id'    => $user->id,
                    'type'       => self::LIBRARY_PUBLISHED,
                    'title'      => $title,
                    'message'    => $message,
                    'link'       => $link,
                    'meta'       => $metaJson,
                    'read_at'    => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($notifications, 250) as $chunk) {
                Notification::insert($chunk);
            }
        } catch (Throwable $e) {
            Log::warning('Notifikasi koleksi perpustakaan gagal dibuat: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Penerima                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Penerima notifikasi proyek: pembuat proyek + seluruh admin
     * se-wilayah yang sama (scope creator). Pelaku aksi dikecualikan.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function projectRecipients(string $projectType, ?string $creatorId, ?string $actorId = null)
    {
        $role = match ($projectType) {
            'Provinsi', 'provinsi', 'PROVINSI' => 'admin-province',
            default => 'admin-kab-kota',
        };

        $ids = collect();

        if ($creatorId) {
            $ids->push($creatorId);

            $creator = User::find($creatorId);
            $scope = $creator?->scopeArea;

            if ($scope) {
                $siblings = User::role($role)
                    ->whereHas('scopeArea', function ($query) use ($role, $scope) {
                        if ($role === 'admin-province') {
                            $query->where('province_code', $scope->province_code);
                        } else {
                            $query->where('regency_code', $scope->regency_code);
                        }
                    })
                    ->pluck('id');

                $ids = $ids->merge($siblings);
            }
        }

        return $this->resolveAdmins($ids, $actorId);
    }

    /**
     * Penerima notifikasi RTKD:
     *  - RTKD Kab/Kota  → Admin Kab/Kota wilayah itu + Admin Provinsi induk
     *  - RTKD Provinsi  → Admin Provinsi wilayah itu
     *
     * Pengguna yang MELAKUKAN aksi (memverifikasi / menyetujui / menolak)
     * selalu dikecualikan — tidak ada notifikasi untuk aksi sendiri.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function rtkdRecipients(?string $provinceCode, ?string $regencyCode, ?string $creatorId, ?string $actorId = null)
    {
        $ids = collect();

        if ($regencyCode) {
            $ids = $ids->merge(
                User::role('admin-kab-kota')
                    ->whereHas('scopeArea', fn ($query) => $query->where('regency_code', $regencyCode))
                    ->pluck('id')
            );
        }

        if ($provinceCode) {
            $ids = $ids->merge(
                User::role('admin-province')
                    ->whereHas('scopeArea', fn ($query) => $query->where('province_code', $provinceCode))
                    ->pluck('id')
            );
        }

        if ($creatorId) {
            $ids->push($creatorId);
        }

        return $this->resolveAdmins($ids, $actorId);
    }

    /**
     * Ambil user admin (role yang punya akses navbar notifikasi).
     *
     * $actorId — pelaku aksi: tidak pernah dimasukkan ke penerima.
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $ids
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function resolveAdmins($ids, ?string $actorId = null)
    {
        $ids = $ids->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $ids)
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole(self::RECIPIENT_ROLES))
            // Aksi sendiri tidak dikirimkan sebagai notifikasi.
            ->filter(fn (User $user) => $actorId === null || $user->id !== $actorId)
            ->values();
    }

    /* ------------------------------------------------------------------ */
    /* Tautan                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Tautan notifikasi proyek → HALAMAN TABEL proyek milik penerima,
     * sudah disaring (?search=) agar langsung menunjuk baris proyeknya.
     */
    private function projectLink(User $user, ?string $projectName = null): string
    {
        $route = $user->hasRole('admin-kab-kota')
            ? 'admin-kab-kota.project.index'
            : 'admin-province.project.index';

        return $projectName
            ? route($route, ['search' => $projectName])
            : route($route);
    }

    /**
     * Tautan notifikasi RTKD → HALAMAN TABEL RTKD milik penerima:
     *  - RTKD Provinsi  → /admin-province/rencana-tenaga-kerja-daerah-provinsi
     *  - RTKD Kab/Kota  → /admin-kab-kota/rencana-tenaga-kerja-daerah-kab-kota
     * Sudah disaring (?search=) agar langsung menunjuk baris RTKD-nya.
     */
    private function rtkdLink(User $user, ?string $rtkName = null): string
    {
        $route = $user->hasRole('admin-kab-kota')
            ? 'admin-kab-kota.rtkd.index'
            : 'admin-province.rtkdp.index';

        return $rtkName
            ? route($route, ['search' => $rtkName])
            : route($route);
    }

    private function scopeName(?string $provinceCode, ?string $regencyCode): ?string
    {
        if ($regencyCode) {
            return Regency::where('code', $regencyCode)->value('name');
        }

        if ($provinceCode) {
            return Province::where('code', $provinceCode)->value('name');
        }

        return null;
    }

    /* ------------------------------------------------------------------ */
    /* Penyimpanan                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * @param  array<string, mixed>  $meta
     */
    private function push(User $user, string $type, string $title, string $message, ?string $link, array $meta = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
            'meta'    => $meta ?: null,
            'read_at' => null,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Query & Pengelolaan                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Get paginated notifications for index view.
     */
    public function getPaginatedNotifications(string $userId, ?string $filter = null, int $perPage = 15)
    {
        $query = Notification::query()
            ->forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($filter === 'unread') {
            $query->unread();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get unread notification count.
     */
    public function getUnreadCount(string $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Snapshot notifikasi terbaru untuk dropdown navbar.
     */
    public function getLatestPayload(string $userId, int $perPage = 15): array
    {
        $items = Notification::forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($perPage)
            ->get();

        return [
            'items'        => $items->map(fn (Notification $n) => $n->toPayload())->values()->all(),
            'unread_count' => $this->getUnreadCount($userId),
            'server_time'  => now()->toIso8601String(),
        ];
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification, string $userId): int
    {
        abort_if($notification->user_id !== $userId, 404);

        $notification->markAsRead();

        return $this->getUnreadCount($userId);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(string $userId): void
    {
        Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     */
    public function destroyNotification(Notification $notification, string $userId): int
    {
        abort_if($notification->user_id !== $userId, 404);

        $notification->delete();

        return $this->getUnreadCount($userId);
    }
}

