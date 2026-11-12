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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'course_id'                => 'required|exists:courses,id',
            'batch_name'               => 'required|string|max:255',
            'batch_code'               => 'nullable|string|max:100|unique:bimtek_events,batch_code',
            'quota'                    => $isUnlimitedQuota ? 'nullable' : 'required|integer|min:1',

            // 1. Periode Pendaftaran (Hanya Tanggal)
            'registration_start_at'    => 'nullable|date_format:Y-m-d',
            'registration_end_at'      => 'nullable|date_format:Y-m-d' . ($hasRegStart ? '|after_or_equal:registration_start_at' : ''),

            // 2. Periode Pelaksanaan (Hanya Tanggal, harus berurutan setelah pendaftaran)
            'start_date'               => 'required|date_format:Y-m-d' . ($hasRegEnd ? '|after_or_equal:registration_end_at' : ($hasRegStart ? '|after_or_equal:registration_start_at' : '')),
            'end_date'                 => 'required|date_format:Y-m-d|after_or_equal:start_date',

            // Detail Sesi Zoom (Wajib Tanggal & Jam)
            'zoom_url'                 => 'nullable|url',
            'zoom_meeting_id'          => 'nullable|string|max:100',
            'zoom_passcode'            => 'nullable|string|max:100',
            'zoom_attendance_code'     => 'nullable|string|max:50',
            'zoom_scheduled_at'        => 'nullable|date',
            'zoom_end_at'              => 'nullable|date',

            // Rentang Waktu Pembukaan Presensi (Wajib Tanggal & Jam, harus di dalam rentang sesi Zoom)
            'zoom_attendance_start_at' => 'nullable|date',
            'zoom_attendance_end_at'   => 'nullable|date',

            // 3. Periode Evaluasi & Sertifikat (Hanya Tanggal, setelah pelaksanaan)
            'assessment_start_at'      => 'nullable|date_format:Y-m-d|after_or_equal:end_date',
            'assessment_end_at'        => 'nullable|date_format:Y-m-d' . ($hasAssessmentStart ? '|after_or_equal:assessment_start_at' : '|after_or_equal:end_date'),

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_end_at.after_or_equal' => 'Tanggal tutup pendaftaran harus sama atau setelah tanggal buka pendaftaran.',
            'start_date.after_or_equal'          => 'Tanggal mulai pelaksanaan harus setelah periode pendaftaran.',
            'end_date.after_or_equal'            => 'Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.',
            'assessment_start_at.after_or_equal' => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.after_or_equal'   => 'Tanggal batas akhir evaluasi & sertifikat harus setelah tanggal mulai evaluasi.',
        ]);

        // Validasi ketat sesi Zoom & Presensi di dalam rentang waktu yang diizinkan
        $validator->after(function ($v) use ($request) {
            $execStart = $request->input('start_date');
            $execEnd = $request->input('end_date');
            $zoomStart = $request->input('zoom_scheduled_at');
            $zoomEnd = $request->input('zoom_end_at');
            $attStart = $request->input('zoom_attendance_start_at');
            $attEnd = $request->input('zoom_attendance_end_at');

            // 1. Sesi Zoom wajib berada di dalam tanggal pelaksanaan
            if ($zoomStart && $execStart && $execEnd) {
                $zoomStartDate = \Carbon\Carbon::parse($zoomStart)->toDateString();
                if ($zoomStartDate < $execStart || $zoomStartDate > $execEnd) {
                    $v->errors()->add('zoom_scheduled_at', 'Jadwal sesi Zoom harus berada di dalam rentang tanggal pelaksanaan.');
                }
            }

            if ($zoomEnd) {
                if ($zoomStart && \Carbon\Carbon::parse($zoomEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_end_at', 'Jadwal selesai sesi Zoom harus setelah jadwal mulai sesi Zoom.');
                }
                if ($execEnd && \Carbon\Carbon::parse($zoomEnd)->toDateString() > $execEnd) {
                    $v->errors()->add('zoom_end_at', 'Jadwal selesai sesi Zoom tidak boleh melewati tanggal akhir pelaksanaan.');
                }
            }

            // 2. Presensi wajib berada di dalam rentang sesi Zoom
            if ($attStart) {
                if (!$zoomStart) {
                    $v->errors()->add('zoom_attendance_start_at', 'Tentukan jadwal mulai sesi Zoom terlebih dahulu sebelum mengatur jam buka presensi.');
                } elseif (\Carbon\Carbon::parse($attStart)->lessThan(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_attendance_start_at', 'Jam buka presensi tidak boleh lebih awal dari jadwal mulai sesi Zoom.');
                }
                if ($zoomEnd && \Carbon\Carbon::parse($attStart)->greaterThan(\Carbon\Carbon::parse($zoomEnd))) {
                    $v->errors()->add('zoom_attendance_start_at', 'Jam buka presensi tidak boleh melewati jadwal selesai sesi Zoom.');
                }
            }

            if ($attEnd) {
                if ($attStart && \Carbon\Carbon::parse($attEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($attStart))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi harus setelah jam buka presensi.');
                } elseif ($zoomStart && \Carbon\Carbon::parse($attEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi harus setelah jadwal mulai sesi Zoom.');
                }
                if ($zoomEnd && \Carbon\Carbon::parse($attEnd)->greaterThan(\Carbon\Carbon::parse($zoomEnd))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi tidak boleh melewati jadwal selesai sesi Zoom.');
                }
            }
        });

        $validated = $validator->validate();

        // Normalisasi tanggal murni menjadi datetime rentang hari (startOfDay & endOfDay)
        if (!empty($validated['registration_start_at'])) {
            $validated['registration_start_at'] = \Carbon\Carbon::parse($validated['registration_start_at'])->startOfDay();
        }
        if (!empty($validated['registration_end_at'])) {
            $validated['registration_end_at'] = \Carbon\Carbon::parse($validated['registration_end_at'])->endOfDay();
        }
        if (!empty($validated['start_date'])) {
            $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        }
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        }
        if (!empty($validated['assessment_start_at'])) {
            $validated['assessment_start_at'] = \Carbon\Carbon::parse($validated['assessment_start_at'])->startOfDay();
        }
        if (!empty($validated['assessment_end_at'])) {
            $validated['assessment_end_at'] = \Carbon\Carbon::parse($validated['assessment_end_at'])->endOfDay();
        }

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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'course_id'                => 'required|exists:courses,id',
            'batch_name'               => 'required|string|max:255',
            'batch_code'               => 'required|string|max:100|unique:bimtek_events,batch_code,' . $event->id,
            'quota'                    => $isUnlimitedQuota ? 'nullable' : 'required|integer|min:1',

            // 1. Periode Pendaftaran (Hanya Tanggal)
            'registration_start_at'    => 'nullable|date_format:Y-m-d',
            'registration_end_at'      => 'nullable|date_format:Y-m-d' . ($hasRegStart ? '|after_or_equal:registration_start_at' : ''),

            // 2. Periode Pelaksanaan (Hanya Tanggal, harus berurutan setelah pendaftaran)
            'start_date'               => 'required|date_format:Y-m-d' . ($hasRegEnd ? '|after_or_equal:registration_end_at' : ($hasRegStart ? '|after_or_equal:registration_start_at' : '')),
            'end_date'                 => 'required|date_format:Y-m-d|after_or_equal:start_date',

            // Detail Sesi Zoom (Wajib Tanggal & Jam)
            'zoom_url'                 => 'nullable|url',
            'zoom_meeting_id'          => 'nullable|string|max:100',
            'zoom_passcode'            => 'nullable|string|max:100',
            'zoom_attendance_code'     => 'nullable|string|max:50',
            'zoom_scheduled_at'        => 'nullable|date',
            'zoom_end_at'              => 'nullable|date',

            // Rentang Waktu Pembukaan Presensi (Wajib Tanggal & Jam, harus di dalam rentang sesi Zoom)
            'zoom_attendance_start_at' => 'nullable|date',
            'zoom_attendance_end_at'   => 'nullable|date',

            // 3. Periode Evaluasi & Sertifikat (Hanya Tanggal, setelah pelaksanaan)
            'assessment_start_at'      => 'nullable|date_format:Y-m-d|after_or_equal:end_date',
            'assessment_end_at'        => 'nullable|date_format:Y-m-d' . ($hasAssessmentStart ? '|after_or_equal:assessment_start_at' : '|after_or_equal:end_date'),

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_end_at.after_or_equal' => 'Tanggal tutup pendaftaran harus sama atau setelah tanggal buka pendaftaran.',
            'start_date.after_or_equal'          => 'Tanggal mulai pelaksanaan harus setelah periode pendaftaran.',
            'end_date.after_or_equal'            => 'Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.',
            'assessment_start_at.after_or_equal' => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.after_or_equal'   => 'Tanggal batas akhir evaluasi & sertifikat harus setelah tanggal mulai evaluasi.',
        ]);

        // Validasi ketat sesi Zoom & Presensi di dalam rentang waktu yang diizinkan
        $validator->after(function ($v) use ($request) {
            $execStart = $request->input('start_date');
            $execEnd = $request->input('end_date');
            $zoomStart = $request->input('zoom_scheduled_at');
            $zoomEnd = $request->input('zoom_end_at');
            $attStart = $request->input('zoom_attendance_start_at');
            $attEnd = $request->input('zoom_attendance_end_at');

            // 1. Sesi Zoom wajib berada di dalam tanggal pelaksanaan
            if ($zoomStart && $execStart && $execEnd) {
                $zoomStartDate = \Carbon\Carbon::parse($zoomStart)->toDateString();
                if ($zoomStartDate < $execStart || $zoomStartDate > $execEnd) {
                    $v->errors()->add('zoom_scheduled_at', 'Jadwal sesi Zoom harus berada di dalam rentang tanggal pelaksanaan.');
                }
            }

            if ($zoomEnd) {
                if ($zoomStart && \Carbon\Carbon::parse($zoomEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_end_at', 'Jadwal selesai sesi Zoom harus setelah jadwal mulai sesi Zoom.');
                }
                if ($execEnd && \Carbon\Carbon::parse($zoomEnd)->toDateString() > $execEnd) {
                    $v->errors()->add('zoom_end_at', 'Jadwal selesai sesi Zoom tidak boleh melewati tanggal akhir pelaksanaan.');
                }
            }

            // 2. Presensi wajib berada di dalam rentang sesi Zoom
            if ($attStart) {
                if (!$zoomStart) {
                    $v->errors()->add('zoom_attendance_start_at', 'Tentukan jadwal mulai sesi Zoom terlebih dahulu sebelum mengatur jam buka presensi.');
                } elseif (\Carbon\Carbon::parse($attStart)->lessThan(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_attendance_start_at', 'Jam buka presensi tidak boleh lebih awal dari jadwal mulai sesi Zoom.');
                }
                if ($zoomEnd && \Carbon\Carbon::parse($attStart)->greaterThan(\Carbon\Carbon::parse($zoomEnd))) {
                    $v->errors()->add('zoom_attendance_start_at', 'Jam buka presensi tidak boleh melewati jadwal selesai sesi Zoom.');
                }
            }

            if ($attEnd) {
                if ($attStart && \Carbon\Carbon::parse($attEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($attStart))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi harus setelah jam buka presensi.');
                } elseif ($zoomStart && \Carbon\Carbon::parse($attEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($zoomStart))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi harus setelah jadwal mulai sesi Zoom.');
                }
                if ($zoomEnd && \Carbon\Carbon::parse($attEnd)->greaterThan(\Carbon\Carbon::parse($zoomEnd))) {
                    $v->errors()->add('zoom_attendance_end_at', 'Jam tutup presensi tidak boleh melewati jadwal selesai sesi Zoom.');
                }
            }
        });

        $validated = $validator->validate();

        // Normalisasi tanggal murni menjadi datetime rentang hari (startOfDay & endOfDay)
        if (!empty($validated['registration_start_at'])) {
            $validated['registration_start_at'] = \Carbon\Carbon::parse($validated['registration_start_at'])->startOfDay();
        }
        if (!empty($validated['registration_end_at'])) {
            $validated['registration_end_at'] = \Carbon\Carbon::parse($validated['registration_end_at'])->endOfDay();
        }
        if (!empty($validated['start_date'])) {
            $validated['start_date'] = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        }
        if (!empty($validated['end_date'])) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        }
        if (!empty($validated['assessment_start_at'])) {
            $validated['assessment_start_at'] = \Carbon\Carbon::parse($validated['assessment_start_at'])->startOfDay();
        }
        if (!empty($validated['assessment_end_at'])) {
            $validated['assessment_end_at'] = \Carbon\Carbon::parse($validated['assessment_end_at'])->endOfDay();
        }

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
