<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekSessionAttendance;
use Modules\LMS\Models\SectionZoomProof;
use Modules\LMS\Services\CourseService;

class AttendanceManagementController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {}

    /**
     * Redirect tautan lama Absensi langsung ke Pusat Kendali Periode Event (Tab Absensi Harian).
     */
    public function index(Request $request): RedirectResponse
    {
        $eventId = $request->query('bimtek_event_id');
        if (!empty($eventId)) {
            return redirect()->route('admin-pusat.management-course.bimtek-events.show', array_filter([
                'bimtek_event' => $eventId,
                'tab'          => 'attendance',
                'section_id'   => $request->query('section_id'),
                'search'       => $request->query('search'),
                'filter'       => $request->query('filter'),
            ], fn($v) => $v !== null && $v !== ''));
        }

        return redirect()->route('admin-pusat.management-course.bimtek-events.index');
    }

    /**
     * Simpan / perbarui 1 Link Google Drive Master pada Event (berlaku untuk seluruh hari).
     */
    public function updateEventDriveLink(Request $request, string $eventId): RedirectResponse
    {
        $validated = $request->validate([
            'submission_drive_link' => 'nullable|url|max:1000',
        ], [
            'submission_drive_link.url' => 'Tautan Google Drive harus berupa URL yang valid (diawali https://).',
        ]);

        $event = BimtekEvent::findOrFail($eventId);
        $link = trim((string) ($validated['submission_drive_link'] ?? ''));

        $event->update([
            'submission_drive_link' => $link !== '' ? $link : null,
        ]);

        ToastMagic::success($link !== ''
            ? 'Tautan folder Google Drive master bukti Zoom berhasil disimpan.'
            : 'Tautan folder Google Drive event berhasil dikosongkan.');

        return redirect()->back();
    }

    /**
     * Tandai hadir atau batalkan presensi Zoom peserta pada agenda harian event terkait.
     */
    public function togglePresence(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bimtek_event_id' => 'required|string',
            'section_id'      => 'required|string',
            'user_id'         => 'required|string',
        ]);

        $event = BimtekEvent::with([
            'course.sections' => fn($q) => $q->orderBy('position'),
            'sessions'        => fn($q) => $q->orderBy('session_number')->orderBy('session_date'),
        ])->findOrFail($validated['bimtek_event_id']);

        $sections = $event->course?->sections?->values() ?? collect();
        $secIdx = $sections->search(fn($s) => (string) $s->id === (string) $validated['section_id']);
        $dayIndex = $secIdx !== false ? (int) $secIdx : 0;
        $session = $event->sessions->firstWhere('session_number', $dayIndex + 1)
            ?: $event->sessions->get($dayIndex);

        $userId = $validated['user_id'];
        $isNowAttended = false;

        if ($session) {
            $existing = BimtekSessionAttendance::where('bimtek_event_session_id', $session->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                $existing->delete();
                $isNowAttended = false;
            } else {
                BimtekSessionAttendance::create([
                    'bimtek_event_session_id' => $session->id,
                    'bimtek_event_id'         => $event->id,
                    'user_id'                 => $userId,
                    'course_id'               => $event->course_id,
                    'attended_at'             => now(),
                    'ip_address'              => $request->ip(),
                    'user_agent'              => 'Admin Override',
                ]);
                $isNowAttended = true;
            }

            $hasAnyAtt = BimtekSessionAttendance::where('bimtek_event_id', $event->id)
                ->where('user_id', $userId)
                ->exists();

            DB::table('course_student')
                ->where('course_id', $event->course_id)
                ->where('user_id', $userId)
                ->update([
                    'zoom_attended'    => $hasAnyAtt,
                    'zoom_attended_at' => $hasAnyAtt ? now() : null,
                    'updated_at'       => now(),
                ]);
        } else {
            $pivot = DB::table('course_student')
                ->where('course_id', $event->course_id)
                ->where('user_id', $userId)
                ->first();

            $isNowAttended = !((bool) ($pivot?->zoom_attended ?? false));
            DB::table('course_student')
                ->where('course_id', $event->course_id)
                ->where('user_id', $userId)
                ->update([
                    'zoom_attended'    => $isNowAttended,
                    'zoom_joined_at'   => $isNowAttended ? ($pivot?->zoom_joined_at ?: now()) : $pivot?->zoom_joined_at,
                    'zoom_attended_at' => $isNowAttended ? now() : null,
                    'updated_at'       => now(),
                ]);
        }

        if ($event->course) {
            $event->course->loadMissing('sections.contents');
            $newProgress = $this->courseService->calculateDynamicProgress($event->course, $userId);
            DB::table('course_student')
                ->where('course_id', $event->course_id)
                ->where('user_id', $userId)
                ->update([
                    'progress'     => $newProgress,
                    'status'       => $newProgress >= 100 ? 'completed' : 'in_progress',
                    'completed_at' => $newProgress >= 100 ? now() : null,
                    'updated_at'   => now(),
                ]);
        }

        ToastMagic::success($isNowAttended
            ? 'Peserta berhasil ditandai sudah melakukan presensi Zoom.'
            : 'Status presensi Zoom peserta dibatalkan.');

        return redirect()->back();
    }

    /**
     * Tandai hadir secara massal seluruh peserta pada agenda harian ini yang sudah mengunggah bukti Zoom
     * namun belum tercatat melakukan presensi.
     */
    public function bulkMarkPresenceWithProof(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bimtek_event_id' => 'required|string',
            'section_id'      => 'required|string',
        ]);

        $event = BimtekEvent::with([
            'course.sections' => fn($q) => $q->orderBy('position'),
            'sessions'        => fn($q) => $q->orderBy('session_number')->orderBy('session_date'),
        ])->findOrFail($validated['bimtek_event_id']);

        $sections = $event->course?->sections?->values() ?? collect();
        $secIdx = $sections->search(fn($s) => (string) $s->id === (string) $validated['section_id']);
        $dayIndex = $secIdx !== false ? (int) $secIdx : 0;
        $session = $event->sessions->firstWhere('session_number', $dayIndex + 1)
            ?: $event->sessions->get($dayIndex);

        // Ambil seluruh peserta gelombang ini
        $enrolledRows = DB::table('course_student')
            ->where('course_id', $event->course_id)
            ->where(function ($q) use ($event) {
                $q->where('bimtek_event_id', $event->id)
                  ->orWhereNull('bimtek_event_id');
            })
            ->select('user_id', 'zoom_attended', 'zoom_joined_at')
            ->get()
            ->keyBy('user_id');

        $enrolledIds = $enrolledRows->keys()->all();
        if (empty($enrolledIds)) {
            ToastMagic::info('Tidak ada peserta terdaftar pada periode event ini.');
            return redirect()->back();
        }

        // Ambil peserta yang sudah mengunggah bukti Zoom pada agenda harian ini
        $proofUserIds = SectionZoomProof::where('course_section_id', $validated['section_id'])
            ->whereIn('user_id', $enrolledIds)
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($proofUserIds->isEmpty()) {
            ToastMagic::info('Belum ada peserta yang mengunggah bukti screenshot Zoom pada agenda ini.');
            return redirect()->back();
        }

        // Saring hanya peserta yang BELUM tercatat presensi
        if ($session) {
            $alreadyAttendedIds = BimtekSessionAttendance::where('bimtek_event_session_id', $session->id)
                ->whereIn('user_id', $proofUserIds)
                ->pluck('user_id')
                ->all();
            $targetUserIds = $proofUserIds->reject(fn($uid) => in_array($uid, $alreadyAttendedIds, true))->values();
        } else {
            $targetUserIds = $proofUserIds->filter(function ($uid) use ($enrolledRows) {
                return !((bool) ($enrolledRows->get($uid)?->zoom_attended ?? false));
            })->values();
        }

        if ($targetUserIds->isEmpty()) {
            ToastMagic::info('Seluruh peserta yang sudah mengunggah bukti Zoom sudah tercatat presensi.');
            return redirect()->back();
        }

        $now = now();
        $ip = $request->ip();
        if ($event->course) {
            $event->course->loadMissing('sections.contents');
        }

        foreach ($targetUserIds as $userId) {
            if ($session) {
                BimtekSessionAttendance::firstOrCreate([
                    'bimtek_event_session_id' => $session->id,
                    'user_id'                 => $userId,
                ], [
                    'bimtek_event_id' => $event->id,
                    'course_id'       => $event->course_id,
                    'attended_at'     => $now,
                    'ip_address'      => $ip,
                    'user_agent'      => 'Admin Bulk Override (Bukti Zoom)',
                ]);

                DB::table('course_student')
                    ->where('course_id', $event->course_id)
                    ->where('user_id', $userId)
                    ->update([
                        'zoom_attended'    => true,
                        'zoom_attended_at' => $now,
                        'updated_at'       => $now,
                    ]);
            } else {
                $pivot = $enrolledRows->get($userId);
                DB::table('course_student')
                    ->where('course_id', $event->course_id)
                    ->where('user_id', $userId)
                    ->update([
                        'zoom_attended'    => true,
                        'zoom_joined_at'   => $pivot?->zoom_joined_at ?: $now,
                        'zoom_attended_at' => $now,
                        'updated_at'       => $now,
                    ]);
            }

            if ($event->course) {
                $newProgress = $this->courseService->calculateDynamicProgress($event->course, $userId);
                DB::table('course_student')
                    ->where('course_id', $event->course_id)
                    ->where('user_id', $userId)
                    ->update([
                        'progress'     => $newProgress,
                        'status'       => $newProgress >= 100 ? 'completed' : 'in_progress',
                        'completed_at' => $newProgress >= 100 ? $now : null,
                        'updated_at'   => $now,
                    ]);
            }
        }

        $markedCount = $targetUserIds->count();
        ToastMagic::success("{$markedCount} peserta yang sudah mengunggah bukti Zoom berhasil ditandai sudah presensi.");

        return redirect()->back();
    }

    /**
     * Perbarui / verifikasi status pengumpulan bukti mengikuti Zoom peserta.
     */
    public function updateProofStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|string',
            'user_id'    => 'required|string',
            'status'     => 'required|in:verified,submitted,rejected,missing',
            'notes'      => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'missing') {
            SectionZoomProof::where('course_section_id', $validated['section_id'])
                ->where('user_id', $validated['user_id'])
                ->delete();

            ToastMagic::success('Status bukti mengikuti Zoom peserta direset menjadi belum mengumpulkan.');

            return redirect()->back();
        }

        $proof = SectionZoomProof::firstOrNew([
            'course_section_id' => $validated['section_id'],
            'user_id'           => $validated['user_id'],
        ]);

        $proof->status = $validated['status'];
        if (!$proof->submitted_at) {
            $proof->submitted_at = now();
        }

        $noteInput = trim((string) ($validated['notes'] ?? ''));
        if ($noteInput !== '') {
            $proof->notes = $noteInput;
        } elseif (!$proof->exists && $validated['status'] === 'verified') {
            $proof->notes = 'Bukti mengikuti Zoom telah diperiksa dan diverifikasi oleh Admin pada folder Google Drive.';
        }

        $proof->save();

        $msg = match ($validated['status']) {
            'verified' => 'Bukti mengikuti Zoom peserta berhasil diverifikasi.',
            'rejected' => 'Bukti peserta ditandai perlu perbaikan.',
            default    => 'Status bukti mengikuti Zoom peserta ditandai sudah terkumpul.',
        };

        ToastMagic::success($msg);

        return redirect()->back();
    }
}
