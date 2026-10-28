<?php

namespace Modules\Dashboard\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of system activities and important events.
     */
    public function index(Request $request): View
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        // 1. Filter Pencarian Teks
        if ($search = trim((string) $request->input('search'))) {
            $matchingUserIds = User::where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($search, $matchingUserIds) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhere('subject_id', 'like', "%{$search}%");

                if (!empty($matchingUserIds)) {
                    $q->orWhereIn('causer_id', $matchingUserIds);
                }
            });
        }

        // 2. Filter Tipe Event (created, updated, deleted, dll.)
        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        // 3. Filter Log Name
        if ($logName = $request->input('log_name')) {
            $query->where('log_name', $logName);
        }

        // 4. Filter Administrator / Causer
        if ($causerId = $request->input('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        // 5. Filter Rentang Tanggal
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $activities = $query->paginate($perPage)->withQueryString();

        // Transform data agar mudah dibaca oleh superadmin pemula
        $activities->getCollection()->transform(function ($activity) {
            $formatted = $this->formatActivity($activity);
            $activity->friendly_action = $formatted['action_label'];
            $activity->friendly_badge = $formatted['action_badge'];
            $activity->friendly_description = $formatted['description'];
            $activity->friendly_subject_type = $formatted['subject_type'];
            $activity->friendly_subject_name = $formatted['subject_name'];
            $activity->friendly_time = $formatted['time'];
            $activity->friendly_relative_time = $formatted['relative_time'];
            $activity->clean_attributes = $formatted['attributes'];
            $activity->clean_old = $formatted['old'];
            $activity->modal_payload = $formatted;
            return $activity;
        });

        // Metrik Ringkasan untuk Kartu Statistik
        $totalActivities = Activity::count();
        $todayActivities = Activity::whereDate('created_at', today())->count();
        $uniqueAdminsCount = Activity::whereNotNull('causer_id')->distinct('causer_id')->count('causer_id');

        // Pilihan Dropdown Filter
        $events = Activity::whereNotNull('event')
            ->distinct()
            ->pluck('event')
            ->filter()
            ->values();

        $logNames = Activity::whereNotNull('log_name')
            ->distinct()
            ->pluck('log_name')
            ->filter()
            ->values();

        // Ambil daftar user yang pernah melakukan aktivitas atau administrator
        $causerIds = Activity::whereNotNull('causer_id')->distinct()->pluck('causer_id')->toArray();
        $admins = User::whereIn('id', $causerIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dashboard::pages.super-admin.activity-logs.index', compact(
            'activities',
            'totalActivities',
            'todayActivities',
            'uniqueAdminsCount',
            'events',
            'logNames',
            'admins'
        ));
    }

    /**
     * Format activity log menjadi format bahasa yang ramah dan mudah dipahami pemula.
     */
    public function formatActivity(Activity $activity): array
    {
        $subjectName = '-';
        $subjectTypeLabel = 'Data Sistem';

        // 1. Jenis Data / Entitas
        if ($activity->subject_type) {
            $base = class_basename($activity->subject_type);
            $subjectTypeLabel = match ($base) {
                'User' => 'Pengguna',
                'Role' => 'Peran (Role)',
                'Permission' => 'Hak Akses',
                'Institution' => 'Instansi / Lembaga',
                'Course' => 'Kursus Pembelajaran',
                'Chapter' => 'Bab Kursus',
                'Lesson' => 'Materi Kursus',
                'Quiz', 'Test', 'PostTest' => 'Evaluasi / Ujian',
                default => $base,
            };
        }

        // 2. Nama Objek yang Diubah
        if ($activity->subject) {
            $subjectName = $activity->subject->name 
                ?? $activity->subject->title 
                ?? ($activity->subject_id ? '#' . substr((string) $activity->subject_id, 0, 8) : '-');
        } else {
            $attrs = $activity->properties['attributes'] ?? $activity->properties['old'] ?? [];
            $subjectName = $attrs['name'] ?? $attrs['title'] ?? ($activity->subject_id ? '#' . substr((string) $activity->subject_id, 0, 8) : '-');
        }

        // 3. Label Aksi yang Ramah
        $event = strtolower((string) ($activity->event ?? $activity->description ?? 'action'));
        $actionLabel = match ($event) {
            'created' => 'Tambah Data',
            'updated' => 'Ubah Data',
            'deleted' => 'Hapus Data',
            'login'   => 'Login Masuk',
            default   => ucfirst($event),
        };

        $actionBadgeColor = match ($event) {
            'created' => 'emerald',
            'updated' => 'amber',
            'deleted' => 'rose',
            'login'   => 'blue',
            default   => 'slate',
        };

        // 4. Deskripsi Kalimat Bahasa Indonesia
        $rawDesc = strtolower(trim((string) $activity->description));
        if (in_array($rawDesc, ['created', 'updated', 'deleted'])) {
            $desc = match ($rawDesc) {
                'created' => "Menambahkan {$subjectTypeLabel} baru: {$subjectName}",
                'updated' => "Mengubah data {$subjectTypeLabel}: {$subjectName}",
                'deleted' => "Menghapus {$subjectTypeLabel}: {$subjectName}",
            };
        } else {
            $desc = ucfirst($activity->description);
        }

        // 5. Bersihkan data properties agar tidak ada password/token atau istilah rumit
        $cleanAttributes = [];
        $rawAttrs = $activity->properties['attributes'] ?? (!isset($activity->properties['old']) ? ($activity->properties ? $activity->properties->toArray() : []) : []);
        $hiddenFields = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'updated_at', 'created_at', 'id', 'uuid'];

        if (is_array($rawAttrs)) {
            foreach ($rawAttrs as $key => $val) {
                if (in_array($key, $hiddenFields) || is_array($val)) continue;
                $cleanAttributes[$this->humanizeFieldKey($key)] = (string) $val;
            }
        }

        $cleanOld = [];
        if (isset($activity->properties['old']) && is_array($activity->properties['old'])) {
            foreach ($activity->properties['old'] as $key => $val) {
                if (in_array($key, $hiddenFields) || is_array($val)) continue;
                $cleanOld[$this->humanizeFieldKey($key)] = (string) $val;
            }
        }

        return [
            'id' => $activity->id,
            'time' => $activity->created_at ? $activity->created_at->format('d M Y, H:i') . ' WIB' : '-',
            'relative_time' => $activity->created_at ? $activity->created_at->diffForHumans() : '-',
            'admin_name' => $activity->causer?->name ?? 'Sistem Otomatis',
            'admin_email' => $activity->causer?->email ?? '-',
            'action_label' => $actionLabel,
            'action_badge' => $actionBadgeColor,
            'subject_type' => $subjectTypeLabel,
            'subject_name' => $subjectName,
            'description' => $desc,
            'attributes' => $cleanAttributes,
            'old' => $cleanOld,
        ];
    }

    private function humanizeFieldKey(string $key): string
    {
        return match ($key) {
            'name' => 'Nama',
            'email' => 'Alamat Email',
            'phone', 'phone_number' => 'Nomor HP/Telepon',
            'title' => 'Judul',
            'description' => 'Keterangan',
            'status' => 'Status',
            'type' => 'Jenis / Tipe',
            'role' => 'Peran Akun',
            default => ucwords(str_replace(['_', '-'], ' ', $key)),
        };
    }

    /**
     * Show detail of a specific activity log (JSON response for modal or view).
     */
    public function show($id)
    {
        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name ?? 'default',
                    'description' => $activity->description,
                    'event' => $activity->event ?? 'action',
                    'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
                    'subject_id' => $activity->subject_id,
                    'subject_name' => $activity->subject?->name ?? $activity->subject_id,
                    'causer_name' => $activity->causer?->name ?? 'System / Anonymous',
                    'causer_email' => $activity->causer?->email ?? '-',
                    'properties' => $activity->properties ? $activity->properties->toArray() : [],
                    'created_at' => $activity->created_at ? $activity->created_at->format('d M Y, H:i:s') : '-',
                    'created_at_relative' => $activity->created_at ? $activity->created_at->diffForHumans() : '-',
                ]
            ]);
        }

        return view('dashboard::pages.super-admin.activity-logs.show', compact('activity'));
    }
}
