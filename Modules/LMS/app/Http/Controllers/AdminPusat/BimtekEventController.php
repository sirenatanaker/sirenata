<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Course;

class BimtekEventController extends Controller
{
    /**
     * Display a listing of Bimtek Events.
     */
    public function index(Request $request)
    {
        $query = BimtekEvent::with(['course'])->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_name', 'like', "%{$search}%")
                  ->orWhere('batch_code', 'like', "%{$search}%")
                  ->orWhereHas('course', function ($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('execution_type')) {
            $query->where('execution_type', $type);
        }

        $events = $query->paginate(10)->withQueryString();

        return view('lms::admin-pusat.bimtek-events.index', compact('events'));
    }

    /**
     * Show form to create a new Bimtek Event.
     */
    public function create()
    {
        $courses = Course::orderBy('name')->get(['id', 'name', 'slug']);
        return view('lms::admin-pusat.bimtek-events.create', compact('courses'));
    }

    /**
     * Store a newly created Bimtek Event in storage.
     */
    public function store(Request $request)
    {
        $isUnlimitedQuota = $request->boolean('is_unlimited_quota');
        if ($isUnlimitedQuota) {
            $request->merge(['quota' => null]);
        }

        $hasRegStart = $request->filled('registration_start_at');
        $hasRegEnd = $request->filled('registration_end_at');
        $hasAssessmentStart = $request->filled('assessment_start_at');
        $hasZoomStart = $request->filled('zoom_scheduled_at');
        $hasZoomEnd = $request->filled('zoom_end_at');
        $hasAttendanceStart = $request->filled('zoom_attendance_start_at');

        $validated = $request->validate([
            'course_id'                => 'required|exists:courses,id',
            'batch_name'               => 'required|string|max:255',
            'batch_code'               => 'nullable|string|max:100|unique:bimtek_events,batch_code',
            'quota'                    => $isUnlimitedQuota ? 'nullable' : 'required|integer|min:1',

            // 1. Periode Pendaftaran
            'registration_start_at'    => 'nullable|date',
            'registration_end_at'      => 'nullable|date' . ($hasRegStart ? '|after:registration_start_at' : ''),

            // 2. Periode Pelaksanaan (harus setelah pendaftaran)
            'start_date'               => 'required|date' . ($hasRegEnd ? '|after:registration_end_at' : ($hasRegStart ? '|after:registration_start_at' : '')),
            'end_date'                 => 'required|date|after:start_date',

            // Detail Sesi Zoom (dalam Pelaksanaan)
            'zoom_url'                 => 'nullable|url',
            'zoom_meeting_id'          => 'nullable|string|max:100',
            'zoom_passcode'            => 'nullable|string|max:100',
            'zoom_attendance_code'     => 'nullable|string|max:50',
            'zoom_scheduled_at'        => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'zoom_end_at'              => 'nullable|date|after:zoom_scheduled_at|before_or_equal:end_date',

            // Rentang Waktu Pembukaan Presensi (harus di dalam rentang sesi Zoom)
            'zoom_attendance_start_at' => 'nullable|date' . ($hasZoomStart ? '|after_or_equal:zoom_scheduled_at' : '') . ($hasZoomEnd ? '|before_or_equal:zoom_end_at' : ''),
            'zoom_attendance_end_at'   => 'nullable|date' . ($hasAttendanceStart ? '|after:zoom_attendance_start_at' : ($hasZoomStart ? '|after:zoom_scheduled_at' : '')) . ($hasZoomEnd ? '|before_or_equal:zoom_end_at' : ''),

            // 3. Periode Evaluasi & Sertifikat (setelah pelaksanaan)
            'assessment_start_at'      => 'nullable|date|after_or_equal:end_date',
            'assessment_end_at'        => 'nullable|date' . ($hasAssessmentStart ? '|after:assessment_start_at' : '|after:end_date'),

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_end_at.after'              => 'Tanggal & waktu selesai pendaftaran harus setelah tanggal mulai pendaftaran.',
            'start_date.after'                       => 'Tanggal & waktu mulai pelaksanaan harus setelah periode pendaftaran selesai.',
            'end_date.after'                         => 'Tanggal & waktu selesai pelaksanaan harus setelah tanggal mulai pelaksanaan.',
            'zoom_scheduled_at.after_or_equal'       => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan.',
            'zoom_scheduled_at.before_or_equal'      => 'Jadwal mulai Sesi Zoom tidak boleh melewati tanggal selesai pelaksanaan.',
            'zoom_end_at.after'                      => 'Jadwal selesai Sesi Zoom harus setelah jadwal mulai Sesi Zoom.',
            'zoom_end_at.before_or_equal'            => 'Jadwal selesai Sesi Zoom tidak boleh melewati tanggal selesai pelaksanaan.',
            'zoom_attendance_start_at.after_or_equal' => 'Jam buka presensi tidak boleh lebih awal dari jadwal mulai sesi Zoom.',
            'zoom_attendance_start_at.before_or_equal' => 'Jam buka presensi tidak boleh melewati jadwal selesai sesi Zoom.',
            'zoom_attendance_end_at.after'           => 'Jam tutup presensi harus setelah jam buka presensi.',
            'zoom_attendance_end_at.before_or_equal'  => 'Jam tutup presensi tidak boleh melewati jadwal selesai sesi Zoom.',
            'assessment_start_at.after_or_equal'     => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.after'                => 'Tanggal batas akhir evaluasi & sertifikat harus setelah tanggal mulai evaluasi.',
        ]);

        if (empty($validated['batch_code'])) {
            $course = Course::find($validated['course_id']);
            $slugPrefix = strtoupper(substr(Str::slug($course->name), 0, 8));
            $validated['batch_code'] = 'BIMTEK-' . $slugPrefix . '-' . date('Y') . '-' . strtoupper(Str::random(4));
        }

        $validated['quota'] = $isUnlimitedQuota ? null : (int) $request->input('quota');

        // Metode pelaksanaan default online (Zoom)
        $validated['execution_type'] = $request->input('execution_type', 'online');

        // Status dihitung otomatis sesuai rentang periode tanggal
        $validated['status'] = BimtekEvent::determineStatusFromDates(
            $validated['registration_start_at'] ?? null,
            $validated['registration_end_at'] ?? null,
            $validated['start_date'],
            $validated['end_date'],
            $validated['assessment_end_at'] ?? null
        );

        if (!empty($validated['zoom_url']) && empty($validated['zoom_attendance_code'])) {
            $validated['zoom_attendance_code'] = 'HADIR-' . strtoupper(Str::random(4));
        }

        $event = BimtekEvent::create($validated);

        return redirect()->route('admin-pusat.management-course.bimtek-events.show', $event->id)
            ->with('success', 'Gelombang Bimtek Online berhasil dibuat!');
    }

    /**
     * Display the specified Bimtek Event with monitoring details.
     */
    public function show($id)
    {
        $event = BimtekEvent::with(['course.sections', 'certificateSubmissions'])->findOrFail($id);

        // Ambil daftar peserta yang terdaftar pada batch ini atau kursus terkait
        $participants = \DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->where('course_student.course_id', $event->course_id)
            ->where(function ($q) use ($event) {
                $q->where('course_student.bimtek_event_id', $event->id)
                  ->orWhereNull('course_student.bimtek_event_id');
            })
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                'course_student.progress',
                'course_student.status',
                'course_student.completed_at',
                'course_student.bimtek_event_id',
                'course_student.zoom_joined_at',
                'course_student.zoom_attended',
                'course_student.zoom_attended_at',
                'course_student.cert_submission_status',
                'course_student.official_cert_number',
                'course_student.certificate_code',
                'course_student.certificate_file'
            )
            ->orderBy('users.name')
            ->get();

        // Hitung kelulusan
        $totalRegistered = $participants->count();
        $totalCompleted = $participants->where('status', 'completed')->count();
        $totalAttendedZoom = $participants->where('zoom_attended', true)->count();
        $totalCertified = $participants->whereNotNull('official_cert_number')->count();

        return view('lms::admin-pusat.bimtek-events.show', compact(
            'event',
            'participants',
            'totalRegistered',
            'totalCompleted',
            'totalAttendedZoom',
            'totalCertified'
        ));
    }

    /**
     * Show form to edit Bimtek Event.
     */
    public function edit($id)
    {
        $event = BimtekEvent::findOrFail($id);
        $courses = Course::orderBy('name')->get(['id', 'name', 'slug']);
        return view('lms::admin-pusat.bimtek-events.edit', compact('event', 'courses'));
    }

    /**
     * Update Bimtek Event.
     */
    public function update(Request $request, $id)
    {
        $event = BimtekEvent::findOrFail($id);

        $isUnlimitedQuota = $request->boolean('is_unlimited_quota');
        if ($isUnlimitedQuota) {
            $request->merge(['quota' => null]);
        }

        $hasRegStart = $request->filled('registration_start_at');
        $hasRegEnd = $request->filled('registration_end_at');
        $hasAssessmentStart = $request->filled('assessment_start_at');
        $hasZoomStart = $request->filled('zoom_scheduled_at');
        $hasZoomEnd = $request->filled('zoom_end_at');
        $hasAttendanceStart = $request->filled('zoom_attendance_start_at');

        $validated = $request->validate([
            'course_id'                => 'required|exists:courses,id',
            'batch_name'               => 'required|string|max:255',
            'batch_code'               => 'required|string|max:100|unique:bimtek_events,batch_code,' . $event->id,
            'quota'                    => $isUnlimitedQuota ? 'nullable' : 'required|integer|min:1',

            // 1. Periode Pendaftaran
            'registration_start_at'    => 'nullable|date',
            'registration_end_at'      => 'nullable|date' . ($hasRegStart ? '|after:registration_start_at' : ''),

            // 2. Periode Pelaksanaan (harus setelah pendaftaran)
            'start_date'               => 'required|date' . ($hasRegEnd ? '|after:registration_end_at' : ($hasRegStart ? '|after:registration_start_at' : '')),
            'end_date'                 => 'required|date|after:start_date',

            // Detail Sesi Zoom (dalam Pelaksanaan)
            'zoom_url'                 => 'nullable|url',
            'zoom_meeting_id'          => 'nullable|string|max:100',
            'zoom_passcode'            => 'nullable|string|max:100',
            'zoom_attendance_code'     => 'nullable|string|max:50',
            'zoom_scheduled_at'        => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'zoom_end_at'              => 'nullable|date|after:zoom_scheduled_at|before_or_equal:end_date',

            // Rentang Waktu Pembukaan Presensi (harus di dalam rentang sesi Zoom)
            'zoom_attendance_start_at' => 'nullable|date' . ($hasZoomStart ? '|after_or_equal:zoom_scheduled_at' : '') . ($hasZoomEnd ? '|before_or_equal:zoom_end_at' : ''),
            'zoom_attendance_end_at'   => 'nullable|date' . ($hasAttendanceStart ? '|after:zoom_attendance_start_at' : ($hasZoomStart ? '|after:zoom_scheduled_at' : '')) . ($hasZoomEnd ? '|before_or_equal:zoom_end_at' : ''),

            // 3. Periode Evaluasi & Sertifikat (setelah pelaksanaan)
            'assessment_start_at'      => 'nullable|date|after_or_equal:end_date',
            'assessment_end_at'        => 'nullable|date' . ($hasAssessmentStart ? '|after:assessment_start_at' : '|after:end_date'),

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_end_at.after'              => 'Tanggal & waktu selesai pendaftaran harus setelah tanggal mulai pendaftaran.',
            'start_date.after'                       => 'Tanggal & waktu mulai pelaksanaan harus setelah periode pendaftaran selesai.',
            'end_date.after'                         => 'Tanggal & waktu selesai pelaksanaan harus setelah tanggal mulai pelaksanaan.',
            'zoom_scheduled_at.after_or_equal'       => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan.',
            'zoom_scheduled_at.before_or_equal'      => 'Jadwal mulai Sesi Zoom tidak boleh melewati tanggal selesai pelaksanaan.',
            'zoom_end_at.after'                      => 'Jadwal selesai Sesi Zoom harus setelah jadwal mulai Sesi Zoom.',
            'zoom_end_at.before_or_equal'            => 'Jadwal selesai Sesi Zoom tidak boleh melewati tanggal selesai pelaksanaan.',
            'zoom_attendance_start_at.after_or_equal' => 'Jam buka presensi tidak boleh lebih awal dari jadwal mulai sesi Zoom.',
            'zoom_attendance_start_at.before_or_equal' => 'Jam buka presensi tidak boleh melewati jadwal selesai sesi Zoom.',
            'zoom_attendance_end_at.after'           => 'Jam tutup presensi harus setelah jam buka presensi.',
            'zoom_attendance_end_at.before_or_equal'  => 'Jam tutup presensi tidak boleh melewati jadwal selesai sesi Zoom.',
            'assessment_start_at.after_or_equal'     => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.after'                => 'Tanggal batas akhir evaluasi & sertifikat harus setelah tanggal mulai evaluasi.',
        ]);

        // Metode pelaksanaan dipertahankan atau default online
        $validated['execution_type'] = $request->input('execution_type', $event->execution_type ?: 'online');

        $validated['quota'] = $isUnlimitedQuota ? null : (int) $request->input('quota');

        // Status dihitung otomatis sesuai rentang periode tanggal
        $validated['status'] = BimtekEvent::determineStatusFromDates(
            $validated['registration_start_at'] ?? null,
            $validated['registration_end_at'] ?? null,
            $validated['start_date'],
            $validated['end_date'],
            $validated['assessment_end_at'] ?? null
        );

        if (!empty($validated['zoom_url']) && empty($validated['zoom_attendance_code'])) {
            $validated['zoom_attendance_code'] = $event->zoom_attendance_code ?: ('HADIR-' . strtoupper(Str::random(4)));
        }

        $event->update($validated);

        return redirect()->route('admin-pusat.management-course.bimtek-events.show', $event->id)
            ->with('success', 'Gelombang Bimtek berhasil diperbarui!');
    }

    /**
     * Toggle Zoom attendance for a participant.
     */
    public function toggleAttendance(Request $request, $eventId, $userId)
    {
        $event = BimtekEvent::findOrFail($eventId);

        $row = \DB::table('course_student')
            ->where('course_id', $event->course_id)
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            return back()->with('error', 'Peserta tidak ditemukan di kursus ini.');
        }

        $newAttended = !$row->zoom_attended;

        \DB::table('course_student')
            ->where('course_id', $event->course_id)
            ->where('user_id', $userId)
            ->update([
                'bimtek_event_id'  => $event->id,
                'zoom_attended'    => $newAttended,
                'zoom_attended_at' => $newAttended ? now() : null,
            ]);

        return back()->with('success', 'Presensi Zoom peserta berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $event = BimtekEvent::findOrFail($id);
        $event->delete();

        return redirect()->route('admin-pusat.management-course.bimtek-events.index')
            ->with('success', 'Gelombang Bimtek berhasil dihapus.');
    }
}
