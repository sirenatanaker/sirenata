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

        // Metrik Ringkasan
        $totalEvents = BimtekEvent::count();
        $ongoingEvents = BimtekEvent::where('status', 'ongoing')->count();
        $totalParticipants = \DB::table('course_student')->whereNotNull('bimtek_event_id')->count();

        return view('lms::admin-pusat.bimtek-events.index', compact(
            'events',
            'totalEvents',
            'ongoingEvents',
            'totalParticipants'
        ));
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
        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'batch_name'      => 'required|string|max:255',
            'batch_code'      => 'nullable|string|max:100|unique:bimtek_events,batch_code',
            'execution_type'  => 'required|in:online,offline,hybrid',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'quota'           => 'required|integer|min:1',
            'zoom_url'          => 'nullable|url',
            'zoom_meeting_id'   => 'nullable|string|max:100',
            'zoom_passcode'     => 'nullable|string|max:100',
            'zoom_scheduled_at' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'zoom_end_at'       => 'nullable|date|after_or_equal:zoom_scheduled_at|before_or_equal:end_date',
            'location_name'     => 'nullable|string|max:255',
            'location_address'  => 'nullable|string',
            'status'            => 'required|in:draft,open_registration,ongoing,completed',
            'description'       => 'nullable|string',
        ], [
            'zoom_scheduled_at.after_or_equal'  => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh sebelum tanggal mulai).',
            'zoom_scheduled_at.before_or_equal' => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh melewati tanggal selesai).',
            'zoom_end_at.after_or_equal'        => 'Jadwal selesai Sesi Zoom tidak boleh sebelum jadwal mulai Sesi Zoom.',
            'zoom_end_at.before_or_equal'       => 'Jadwal selesai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh melewati tanggal selesai).',
        ]);

        if (empty($validated['batch_code'])) {
            $course = Course::find($validated['course_id']);
            $slugPrefix = strtoupper(substr(Str::slug($course->name), 0, 8));
            $validated['batch_code'] = 'BIMTEK-' . $slugPrefix . '-' . date('Y') . '-' . strtoupper(Str::random(4));
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

        $validated = $request->validate([
            'course_id'       => 'required|exists:courses,id',
            'batch_name'      => 'required|string|max:255',
            'batch_code'      => 'required|string|max:100|unique:bimtek_events,batch_code,' . $event->id,
            'execution_type'  => 'required|in:online,offline,hybrid',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'quota'           => 'required|integer|min:1',
            'zoom_url'          => 'nullable|url',
            'zoom_meeting_id'   => 'nullable|string|max:100',
            'zoom_passcode'     => 'nullable|string|max:100',
            'zoom_scheduled_at' => 'nullable|date|after_or_equal:start_date|before_or_equal:end_date',
            'zoom_end_at'       => 'nullable|date|after_or_equal:zoom_scheduled_at|before_or_equal:end_date',
            'location_name'     => 'nullable|string|max:255',
            'location_address'  => 'nullable|string',
            'status'            => 'required|in:draft,open_registration,ongoing,completed',
            'description'       => 'nullable|string',
        ], [
            'zoom_scheduled_at.after_or_equal'  => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh sebelum tanggal mulai).',
            'zoom_scheduled_at.before_or_equal' => 'Jadwal mulai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh melewati tanggal selesai).',
            'zoom_end_at.after_or_equal'        => 'Jadwal selesai Sesi Zoom tidak boleh sebelum jadwal mulai Sesi Zoom.',
            'zoom_end_at.before_or_equal'       => 'Jadwal selesai Sesi Zoom harus berada di dalam rentang waktu pelaksanaan (tidak boleh melewati tanggal selesai).',
        ]);

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
