<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Support\SmartCache;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\BimtekEventSession;
use Modules\LMS\Models\BimtekInstructorEvaluation;
use Modules\LMS\Models\BimtekSessionAttendance;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\GradingBatch;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Models\SectionZoomProof;

class BimtekEventController extends Controller
{
    /**
     * Display a listing of Bimtek Events.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = (string) $request->input('status', '');
        $type = (string) $request->input('execution_type', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 10;

        $cached = SmartCache::remember(
            [SmartCache::DOMAIN_LMS_EVENTS, SmartCache::DOMAIN_LMS_COURSES],
            "bimtek_events_index:" . md5(json_encode([$search, $status, $type, $page, $perPage])),
            300,
            function () use ($search, $status, $type, $page, $perPage) {
                $query = BimtekEvent::with(['course'])->latest();

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('batch_name', 'like', "%{$search}%")
                          ->orWhere('batch_code', 'like', "%{$search}%")
                          ->orWhereHas('course', function ($c) use ($search) {
                              $c->where('name', 'like', "%{$search}%");
                          });
                    });
                }

                if ($status !== '') {
                    $query->where('status', $status);
                }

                if ($type !== '') {
                    $query->where('execution_type', $type);
                }

                $total = (clone $query)->count();
                $items = $query->forPage($page, $perPage)->get();

                return compact('items', 'total');
            }
        );

        $events = new LengthAwarePaginator(
            $cached['items'],
            $cached['total'],
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

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

            // 1. Periode Pendaftaran (Hanya Tanggal - Wajib)
            'registration_start_at'    => 'required|date_format:Y-m-d',
            'registration_end_at'      => 'required|date_format:Y-m-d|after_or_equal:registration_start_at',

            // 2. Periode Pelaksanaan (Hanya Tanggal, harus berurutan setelah pendaftaran - Wajib)
            'start_date'               => 'required|date_format:Y-m-d|after_or_equal:registration_end_at',
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
            'submission_drive_link'    => 'nullable|url|max:1000',

            // Multi-Sesi Zoom & Presensi Harian (Skenario 1)
            'sessions'                         => 'nullable|array',
            'sessions.*.id'                    => 'nullable|string',
            'sessions.*.title'                 => 'required_with:sessions|string|max:255',
            'sessions.*.session_date'          => 'required_with:sessions|date_format:Y-m-d',
            'sessions.*.start_time'            => 'required_with:sessions|date',
            'sessions.*.end_time'              => 'nullable|date',
            'sessions.*.zoom_url'              => 'nullable|url',
            'sessions.*.zoom_meeting_id'       => 'nullable|string|max:100',
            'sessions.*.zoom_passcode'         => 'nullable|string|max:100',
            'sessions.*.attendance_start_at'   => 'nullable|date',
            'sessions.*.attendance_end_at'     => 'nullable|date',

            // 3. Periode Evaluasi & Sertifikat (Hanya Tanggal, setelah pelaksanaan - Wajib)
            'assessment_start_at'      => 'required|date_format:Y-m-d|after_or_equal:end_date',
            'assessment_end_at'        => 'required|date_format:Y-m-d|after_or_equal:assessment_start_at',

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_start_at.required'     => 'Tanggal buka pendaftaran wajib diisi.',
            'registration_end_at.required'       => 'Tanggal tutup pendaftaran wajib diisi.',
            'registration_end_at.after_or_equal' => 'Tanggal tutup pendaftaran harus sama atau setelah tanggal buka pendaftaran.',
            'start_date.required'                => 'Tanggal mulai pelaksanaan wajib diisi.',
            'start_date.after_or_equal'          => 'Tanggal mulai pelaksanaan harus setelah atau sama dengan tanggal tutup pendaftaran.',
            'end_date.required'                  => 'Tanggal selesai pelaksanaan wajib diisi.',
            'end_date.after_or_equal'            => 'Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.',
            'assessment_start_at.required'       => 'Tanggal mulai evaluasi wajib diisi.',
            'assessment_start_at.after_or_equal' => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.required'         => 'Tanggal batas akhir evaluasi wajib diisi.',
            'assessment_end_at.after_or_equal'   => 'Tanggal batas akhir evaluasi & sertifikat harus sama atau setelah tanggal mulai evaluasi.',
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

            // 3. Validasi Multi-Sesi Zoom & Presensi Harian (Skenario 1)
            $sessions = $request->input('sessions', []);
            if (is_array($sessions)) {
                foreach ($sessions as $i => $s) {
                    $idx = $i + 1;
                    $sTitle = !empty($s['title']) ? $s['title'] : "Sesi Hari {$idx}";
                    $sDate = $s['session_date'] ?? null;
                    $sStart = $s['start_time'] ?? null;
                    $sEnd = $s['end_time'] ?? null;
                    $sAttStart = $s['attendance_start_at'] ?? null;
                    $sAttEnd = $s['attendance_end_at'] ?? null;

                    if ($sDate && $execStart && $execEnd) {
                        if ($sDate < $execStart || $sDate > $execEnd) {
                            $v->errors()->add("sessions.{$i}.session_date", "Tanggal {$sTitle} harus berada di dalam rentang tanggal pelaksanaan ({$execStart} s/d {$execEnd}).");
                        }
                    }

                    if ($sStart && $sDate) {
                        $parsedDate = \Carbon\Carbon::parse($sStart)->toDateString();
                        if ($parsedDate !== $sDate) {
                            $v->errors()->add("sessions.{$i}.start_time", "Jam mulai {$sTitle} harus berada pada tanggal sesi yang sama ({$sDate}).");
                        }
                    }

                    if ($sEnd && $sStart) {
                        if (\Carbon\Carbon::parse($sEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.end_time", "Jam selesai {$sTitle} harus setelah jam mulai.");
                        }
                    }

                    if ($sAttStart) {
                        if (!$sStart) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Tentukan jam mulai {$sTitle} terlebih dahulu sebelum jam buka presensi.");
                        } elseif (\Carbon\Carbon::parse($sAttStart)->lessThan(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Jam buka presensi {$sTitle} tidak boleh lebih awal dari jam mulai sesi.");
                        }
                        if ($sEnd && \Carbon\Carbon::parse($sAttStart)->greaterThan(\Carbon\Carbon::parse($sEnd))) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Jam buka presensi {$sTitle} tidak boleh melewati jam selesai sesi.");
                        }
                    }

                    if ($sAttEnd) {
                        if ($sAttStart && \Carbon\Carbon::parse($sAttEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sAttStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} harus setelah jam buka presensi.");
                        } elseif ($sStart && \Carbon\Carbon::parse($sAttEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} harus setelah jam mulai sesi.");
                        }
                        if ($sEnd && \Carbon\Carbon::parse($sAttEnd)->greaterThan(\Carbon\Carbon::parse($sEnd))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} tidak boleh melewati jam selesai sesi.");
                        }
                    }
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

        // Ambil data sessions jika ada dan sinkronkan bounds utama
        $sessionsData = $request->input('sessions', []);
        if (!empty($sessionsData) && is_array($sessionsData)) {
            usort($sessionsData, function ($a, $b) {
                $da = ($a['session_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
                $db = ($b['session_date'] ?? '') . ' ' . ($b['start_time'] ?? '');
                return strcmp($da, $db);
            });

            $firstSession = reset($sessionsData);
            $lastSession = end($sessionsData);

            if (empty($validated['zoom_scheduled_at']) && !empty($firstSession['start_time'])) {
                $validated['zoom_scheduled_at'] = \Carbon\Carbon::parse($firstSession['start_time']);
            }
            if (empty($validated['zoom_end_at']) && !empty($lastSession['end_time'])) {
                $validated['zoom_end_at'] = \Carbon\Carbon::parse($lastSession['end_time']);
            }
            if (empty($validated['zoom_attendance_start_at']) && !empty($firstSession['attendance_start_at'])) {
                $validated['zoom_attendance_start_at'] = \Carbon\Carbon::parse($firstSession['attendance_start_at']);
            }
            if (empty($validated['zoom_attendance_end_at']) && !empty($lastSession['attendance_end_at'])) {
                $validated['zoom_attendance_end_at'] = \Carbon\Carbon::parse($lastSession['attendance_end_at']);
            }
        }

        unset($validated['sessions']);

        $event = BimtekEvent::create($validated);

        // Simpan setiap sesi ke tabel bimtek_event_sessions
        if (!empty($sessionsData)) {
            foreach ($sessionsData as $index => $s) {
                if (empty($s['session_date']) || empty($s['start_time'])) continue;

                BimtekEventSession::create([
                    'bimtek_event_id'     => $event->id,
                    'session_number'      => $index + 1,
                    'title'               => !empty($s['title']) ? $s['title'] : ('Sesi Hari ' . ($index + 1)),
                    'session_date'        => $s['session_date'],
                    'start_time'          => \Carbon\Carbon::parse($s['start_time']),
                    'end_time'            => !empty($s['end_time']) ? \Carbon\Carbon::parse($s['end_time']) : null,
                    'zoom_url'            => !empty($s['zoom_url']) ? $s['zoom_url'] : null,
                    'zoom_meeting_id'     => !empty($s['zoom_meeting_id']) ? $s['zoom_meeting_id'] : null,
                    'zoom_passcode'       => !empty($s['zoom_passcode']) ? $s['zoom_passcode'] : null,
                    'attendance_code'     => 'HADIR-S' . ($index + 1) . '-' . strtoupper(Str::random(3)),
                    'attendance_start_at' => !empty($s['attendance_start_at']) ? \Carbon\Carbon::parse($s['attendance_start_at']) : null,
                    'attendance_end_at'   => !empty($s['attendance_end_at']) ? \Carbon\Carbon::parse($s['attendance_end_at']) : null,
                ]);
            }
        }

        return redirect()->route('admin-pusat.management-course.bimtek-events.show', $event->id)
            ->with('success', 'Gelombang Bimtek Online berhasil dibuat!');
    }

    /**
     * Pusat Kendali 1 Event (4 Tab Berurutan):
     * 1. overview   : Ringkasan, Linimasa, Zoom, 1 Link Google Drive Master & Evaluasi Narasumber
     * 2. attendance : Absensi & Bukti Mengikuti Zoom Harian (Hari 1..N)
     * 3. evaluation : Penilaian Evaluasi Harian & Evaluasi Akhir (Uraian/Upload Berkas & CBT)
     * 4. graduation : Monitoring Kelulusan Lengkap & Pengajuan Sertifikat ke PPSDM
     */
    public function show(Request $request, $id)
    {
        $snapshot = SmartCache::remember(
            [SmartCache::DOMAIN_LMS_EVENTS, SmartCache::DOMAIN_LMS_COURSES, SmartCache::DOMAIN_LMS_SUBMISSIONS],
            "bimtek_event_show_snapshot:{$id}",
            300,
            function () use ($id) {
                $event = BimtekEvent::with([
                    'course.sections' => fn($q) => $q->orderBy('position'),
                    'certificateSubmissions',
                    'sessions'        => fn($q) => $q->orderBy('session_number')->orderBy('session_date')->orderBy('start_time'),
                    'sessions.attendances',
                ])->findOrFail($id);

                $sectionIds = ($event->course?->sections ?? collect())->pluck('id')->all();

                $participants = DB::table('course_student')
                    ->join('users', 'users.id', '=', 'course_student.user_id')
                    ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                    ->where('course_student.course_id', $event->course_id)
                    ->where(function ($q) use ($event) {
                        $q->where('course_student.bimtek_event_id', $event->id)
                          ->orWhereNull('course_student.bimtek_event_id');
                    })
                    ->select(
                        'users.id as user_id',
                        DB::raw('COALESCE(user_profiles.full_name, users.name) as user_name'),
                        'users.email as user_email',
                        'user_profiles.instansi as user_instansi',
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
                    ->orderBy('user_name')
                    ->get();

                $participantIds = $participants->pluck('user_id')->unique()->values();

                $allSessionAttendances = BimtekSessionAttendance::where('bimtek_event_id', $event->id)->get();

                $allProofs = (!empty($sectionIds) && $participantIds->isNotEmpty())
                    ? SectionZoomProof::whereIn('course_section_id', $sectionIds)
                        ->whereIn('user_id', $participantIds)
                        ->get()
                    : collect();

                $coursePostTests = PostTest::with('questions')
                    ->where('course_id', $event->course_id)
                    ->get();

                $allResults = ($coursePostTests->isNotEmpty() && $participantIds->isNotEmpty())
                    ? PostTestResult::whereIn('post_test_id', $coursePostTests->pluck('id'))
                        ->whereIn('user_id', $participantIds)
                        ->get()
                    : collect();

                $evaluations = BimtekInstructorEvaluation::with('user')
                    ->where('bimtek_event_id', $event->id)
                    ->latest()
                    ->get();

                return compact(
                    'event',
                    'participants',
                    'allSessionAttendances',
                    'allProofs',
                    'coursePostTests',
                    'allResults',
                    'evaluations'
                );
            }
        );

        $event = $snapshot['event'];
        $participants = $snapshot['participants']->map(fn($p) => clone $p);
        $allSessionAttendances = $snapshot['allSessionAttendances'];
        $allProofs = $snapshot['allProofs'];
        $coursePostTests = $snapshot['coursePostTests'];
        $allResults = $snapshot['allResults'];
        $evaluations = $snapshot['evaluations'];

        $activeTab = (string) $request->query('tab', 'overview');
        if (!in_array($activeTab, ['overview', 'attendance', 'evaluation', 'graduation'], true)) {
            $activeTab = 'overview';
        }
        if ($request->filled('post_test_id')) {
            $activeTab = 'evaluation';
        } elseif ($request->filled('section_id') && !$request->filled('tab')) {
            $activeTab = 'attendance';
        }

        $sections = $event->course?->sections?->values() ?? collect();
        $sessions = $event->sessions->values();
        $sectionIds = $sections->pluck('id')->all();
        $totalAgendaDays = $sections->count();
        $totalSessionsCount = $sessions->count();

        $participantIds = $participants->pluck('user_id')->unique()->values();
        $enrollmentByUser = $participants->keyBy('user_id');

        $sessionAttByUser = $allSessionAttendances->groupBy('user_id');
        $sessionAttBySession = $allSessionAttendances->groupBy('bimtek_event_session_id');

        $proofsBySection = $allProofs->groupBy('course_section_id');
        $proofsByUser = $allProofs->groupBy('user_id');

        $manualTypes = ['essay', 'uraian', 'file_upload', 'upload'];
        $resultsByTest = $allResults->groupBy('post_test_id');
        $resultsByUser = $allResults->groupBy('user_id');

        // Hitung total evaluasi non-pilgan yang masih menunggu penilaian / draf belum dipublikasikan
        $pendingEvalTotal = 0;
        foreach ($coursePostTests as $pt) {
            if (in_array($pt->type, $manualTypes, true)) {
                $pendingEvalTotal += $resultsByTest->get($pt->id, collect())
                    ->filter(fn($r) => $r->grading_status !== PostTestResult::GRADING_PUBLISHED)
                    ->count();
            }
        }

        $evaluationsCount = $evaluations->count();
        $evaluationsByUser = $evaluations->keyBy('user_id');

        $avgMateri = $evaluationsCount > 0 ? round((float) $evaluations->avg('rating_materi'), 1) : 0.0;
        $avgPenyampaian = $evaluationsCount > 0 ? round((float) $evaluations->avg('rating_penyampaian'), 1) : 0.0;
        $avgFasilitasi = $evaluationsCount > 0 ? round((float) $evaluations->avg('rating_fasilitasi'), 1) : 0.0;
        $avgOverall = $evaluationsCount > 0 ? round(($avgMateri + $avgPenyampaian + $avgFasilitasi) / 3, 1) : 0.0;

        $totalEvalTestsCount = $coursePostTests->count();
        foreach ($participants as $p) {
            $attendedSessions = isset($sessionAttByUser[$p->user_id])
                ? $sessionAttByUser[$p->user_id]->count()
                : ($p->zoom_attended ? ($totalSessionsCount ?: ($totalAgendaDays ?: 1)) : 0);
            $p->attended_sessions_count = $attendedSessions;

            $userProofs = $proofsByUser->get($p->user_id, collect());
            $p->proof_submitted_count = $userProofs->where('status', '!=', 'rejected')->count();
            $p->proof_verified_count = $userProofs->where('status', 'verified')->count();

            $userResults = $resultsByUser->get($p->user_id, collect());
            $p->eval_passed_count = $userResults->filter(function ($r) {
                return (bool) $r->is_passed && ($r->grading_status === PostTestResult::GRADING_PUBLISHED || $r->status === 'graded');
            })->count();
            $p->eval_total_count = $totalEvalTestsCount;

            $p->evaluation = $evaluationsByUser[$p->user_id] ?? null;
            $p->has_evaluated = isset($evaluationsByUser[$p->user_id]);
        }

        $totalRegistered = $participants->count();
        $totalCompleted = $participants->where('status', 'completed')->count();
        $totalAttendedZoom = $participants->where('zoom_attended', true)->count();
        $totalCertified = $participants->whereNotNull('official_cert_number')->count();
        $readyForPpsdmCount = $participants->filter(fn($p) => $p->status === 'completed' && empty($p->official_cert_number) && $p->cert_submission_status !== 'diajukan_ke_ppsdm')->count();

        // Helper mencocokkan sesi dengan indeks hari
        $resolveSessionForDay = function (int $idx) use ($sessions): ?BimtekEventSession {
            return $sessions->firstWhere('session_number', $idx + 1) ?: $sessions->get($idx);
        };

        // =========================================================================
        // TAB 2: DATA ABSENSI & BUKTI HARIAN (HARI 1..N)
        // =========================================================================
        $attSearch = trim((string) $request->query('att_search', $request->query('search', '')));
        $attFilter = (string) $request->query('att_filter', $request->query('filter', 'all'));

        $attendanceDays = $sections->map(function ($section, $idx) use (
            $event,
            $resolveSessionForDay,
            $sessionAttBySession,
            $proofsBySection,
            $totalRegistered,
            $totalAttendedZoom
        ) {
            $dayNum = $idx + 1;
            $session = $resolveSessionForDay($idx);
            $rawName = trim((string) $section->name);
            $cleanTitle = trim((string) preg_replace('/^(?:Modul|Hari(?:\s+ke-?)?)\s*\d+\s*[:\-—]\s*/i', '', $rawName)) ?: $rawName;

            $dayDate = null;
            if ($session?->session_date) {
                $dayDate = $session->session_date->translatedFormat('d M Y');
            } elseif ($event->start_date) {
                $dayDate = $event->start_date->copy()->addDays($idx)->translatedFormat('d M Y');
            }

            $attRows = $session ? $sessionAttBySession->get($session->id, collect()) : collect();
            $attendedCount = $session ? $attRows->pluck('user_id')->unique()->count() : $totalAttendedZoom;
            $proofRows = $proofsBySection->get($section->id, collect());

            return (object) [
                'day_number'         => $dayNum,
                'day_date'           => $dayDate,
                'section'            => $section,
                'session'            => $session,
                'title'              => $cleanTitle,
                'attended_count'     => $attendedCount,
                'proof_count'        => $proofRows->count(),
                'verified_count'     => $proofRows->where('status', 'verified')->count(),
                'total_participants' => $totalRegistered,
            ];
        });

        $requestedSectionId = $request->query('section_id');
        $activeAttendanceDay = $requestedSectionId
            ? $attendanceDays->first(fn($d) => (string) $d->section->id === (string) $requestedSectionId)
            : null;

        $attendanceSummary = [
            'all' => 0, 'complete' => 0, 'attended' => 0, 'not_attended' => 0,
            'proof_submitted' => 0, 'proof_verified' => 0, 'proof_missing' => 0,
            'proof_not_attended' => 0,
        ];
        $attendanceRows = null;

        if ($activeTab === 'attendance' && $activeAttendanceDay) {
            $activeSession = $activeAttendanceDay->session;
            $activeSecId = $activeAttendanceDay->section->id;
            $sessAttMap = $activeSession ? $sessionAttBySession->get($activeSession->id, collect())->keyBy('user_id') : collect();
            $secProofMap = $proofsBySection->get($activeSecId, collect())->keyBy('user_id');

            $allAttRows = $participants->map(function ($p) use ($activeSession, $sessAttMap, $secProofMap) {
                $sAtt = $sessAttMap->get($p->user_id);
                $proof = $secProofMap->get($p->user_id);

                $isAttended = $activeSession ? (bool) $sAtt : (bool) $p->zoom_attended;
                $attendedAt = $sAtt?->attended_at
                    ?? ($p->zoom_attended_at ? Carbon::parse($p->zoom_attended_at) : null);
                $joinedAt = $p->zoom_joined_at ? Carbon::parse($p->zoom_joined_at) : null;
                $hasProof = (bool) $proof;
                $proofStatus = $proof?->status ?? 'missing';

                return (object) [
                    'user_id'      => $p->user_id,
                    'name'         => $p->user_name,
                    'email'        => $p->user_email,
                    'instansi'     => $p->user_instansi,
                    'is_attended'  => $isAttended,
                    'attended_at'  => $attendedAt,
                    'joined_at'    => $joinedAt,
                    'has_proof'    => $hasProof,
                    'proof'        => $proof,
                    'proof_status' => $proofStatus,
                    'is_complete'  => $isAttended && $hasProof && $proofStatus !== 'rejected',
                ];
            });

            $attendanceSummary = [
                'all'                => $allAttRows->count(),
                'complete'           => $allAttRows->where('is_complete', true)->count(),
                'attended'           => $allAttRows->where('is_attended', true)->count(),
                'not_attended'       => $allAttRows->where('is_attended', false)->count(),
                'proof_submitted'    => $allAttRows->where('has_proof', true)->count(),
                'proof_verified'     => $allAttRows->where('proof_status', 'verified')->count(),
                'proof_missing'      => $allAttRows->where('has_proof', false)->count(),
                'proof_not_attended' => $allAttRows->where('is_attended', false)->where('has_proof', true)->count(),
            ];

            $filteredAtt = $allAttRows->filter(function ($row) use ($attSearch, $attFilter) {
                if ($attSearch !== '') {
                    $needle = mb_strtolower($attSearch);
                    $hay = mb_strtolower(($row->name ?? '') . ' ' . ($row->email ?? '') . ' ' . ($row->instansi ?? ''));
                    if (!str_contains($hay, $needle)) {
                        return false;
                    }
                }
                return match ($attFilter) {
                    'complete'           => $row->is_complete,
                    'attended'           => $row->is_attended,
                    'not_attended'       => !$row->is_attended,
                    'proof_submitted'    => $row->has_proof,
                    'proof_verified'     => $row->proof_status === 'verified',
                    'proof_missing'      => !$row->has_proof,
                    'proof_not_attended' => !$row->is_attended && $row->has_proof,
                    default              => true,
                };
            })->values();

            $perPage = 15;
            $page = max(1, (int) $request->query('att_page', 1));
            $attendanceRows = new LengthAwarePaginator(
                $filteredAtt->slice(($page - 1) * $perPage, $perPage)->values(),
                $filteredAtt->count(),
                $perPage,
                $page,
                [
                    'path'     => $request->url(),
                    'pageName' => 'att_page',
                    'query'    => $request->query(),
                ]
            );
        }

        // =========================================================================
        // TAB 3: PENILAIAN EVALUASI (HARIAN & EVALUASI AKHIR)
        // =========================================================================
        $testsBySection = $coursePostTests->whereNotNull('course_section_id')->keyBy('course_section_id');
        $finalTest = $coursePostTests->firstWhere('course_section_id', null);

        $evalDailyAgendas = $sections->map(function ($section, $index) use (
            $testsBySection,
            $resultsByTest,
            $manualTypes,
            $event,
            $totalRegistered
        ) {
            $pt = $testsBySection->get($section->id);
            $rows = $pt ? $resultsByTest->get($pt->id, collect()) : collect();
            $isManual = $pt && in_array($pt->type, $manualTypes, true);

            $rawName = trim((string) $section->name);
            $cleanTitle = trim((string) preg_replace('/^(?:Modul|Hari(?:\s+ke-?)?)\s*\d+\s*[:\-—]\s*/i', '', $rawName)) ?: $rawName;
            $dayDate = $event->start_date ? $event->start_date->copy()->addDays($index)->translatedFormat('d M Y') : null;

            return (object) [
                'day_number'         => $index + 1,
                'day_date'           => $dayDate,
                'title'              => $cleanTitle,
                'post_test'          => $pt,
                'is_manual'          => $isManual,
                'format_label'       => match ($pt?->type) {
                    'essay', 'uraian'       => 'Uraian / Esai',
                    'file_upload', 'upload' => 'Upload Berkas',
                    default                 => $pt ? 'Pilihan Ganda (CBT)' : 'Belum Ada Evaluasi',
                },
                'submitted_count'    => $rows->count(),
                'pending_count'      => $isManual
                    ? $rows->filter(fn($r) => $r->grading_status !== PostTestResult::GRADING_PUBLISHED)->count()
                    : 0,
                'graded_count'       => $isManual
                    ? $rows->filter(fn($r) => $r->grading_status === PostTestResult::GRADING_PUBLISHED)->count()
                    : $rows->count(),
                'total_participants' => $totalRegistered,
            ];
        });

        $finalRows = $finalTest ? $resultsByTest->get($finalTest->id, collect()) : collect();
        $finalIsManual = $finalTest && in_array($finalTest->type, $manualTypes, true);
        $evalFinalEvaluation = (object) [
            'title'              => $finalTest?->title ?? ('Evaluasi Akhir: ' . ($event->course?->name ?? 'Pelatihan')),
            'post_test'          => $finalTest,
            'is_manual'          => $finalIsManual,
            'format_label'       => match ($finalTest?->type) {
                'essay', 'uraian'       => 'Uraian / Esai',
                'file_upload', 'upload' => 'Upload Berkas',
                default                 => $finalTest ? 'Pilihan Ganda (CBT)' : 'Belum Ada Evaluasi',
            },
            'submitted_count'    => $finalRows->count(),
            'pending_count'      => $finalIsManual
                ? $finalRows->filter(fn($r) => $r->grading_status !== PostTestResult::GRADING_PUBLISHED)->count()
                : 0,
            'graded_count'       => $finalIsManual
                ? $finalRows->filter(fn($r) => $r->grading_status === PostTestResult::GRADING_PUBLISHED)->count()
                : $finalRows->count(),
            'total_participants' => $totalRegistered,
        ];

        $selectedPostTest = null;
        $isManualTest = false;
        $triageCounts = ['all' => 0, 'pure_pending' => 0, 'pending' => 0, 'needs_review' => 0, 'failed' => 0, 'draft_ready' => 0, 'published' => 0];
        $activeBatch = null;
        $evalSubmissions = null;
        $evalSearch = trim((string) $request->query('eval_search', ''));
        $evalStatus = (string) $request->query('eval_status', 'all');
        $selectedPostTestHeaderTitle = '';
        $selectedPostTestDayNum = 1;

        if ($activeTab === 'evaluation' && $request->filled('post_test_id')) {
            $selectedPostTest = PostTest::with(['questions', 'section'])->findOrFail($request->query('post_test_id'));
            $isManualTest = in_array($selectedPostTest->type, $manualTypes, true);

            if ($selectedPostTest->course_section_id) {
                $sIdx = $sections->search(fn($s) => (string) $s->id === (string) $selectedPostTest->course_section_id);
                $selectedPostTestDayNum = $sIdx !== false ? ($sIdx + 1) : 1;
                $rawT = trim((string) ($selectedPostTest->section?->name ?: $selectedPostTest->title ?: ''));
                $cleanT = preg_replace('/^(?:(?:Penugasan\s+Berkas|Evaluasi(?:\s+Uraian|\s+Harian|\s+Berkas)?)\s*:\s*)+/i', '', $rawT);
                $cleanT = trim((string) preg_replace('/^(?:Modul|Hari(?:\s+ke-?)?)\s*\d+\s*[:\-—]\s*/i', '', trim((string) $cleanT)));
                $selectedPostTestHeaderTitle = "Evaluasi Hari ke-{$selectedPostTestDayNum}" . ($cleanT !== '' ? ": {$cleanT}" : '');
            } else {
                $selectedPostTestHeaderTitle = $selectedPostTest->title ?? 'Evaluasi Akhir';
            }

            $baseSubQuery = PostTestResult::query()->where('post_test_id', $selectedPostTest->id);
            if ($participantIds->isNotEmpty()) {
                $baseSubQuery->whereIn('user_id', $participantIds);
            } else {
                $baseSubQuery->whereRaw('1 = 0');
            }

            $triageRows = (clone $baseSubQuery)->get(['grading_status', 'needs_manual_review', 'ai_score', 'final_score']);
            $triageCounts = [
                'all'          => $triageRows->count(),
                'pure_pending' => $triageRows->where('grading_status', PostTestResult::GRADING_PENDING)->count(),
                'pending'      => $triageRows->whereIn('grading_status', [PostTestResult::GRADING_PENDING, PostTestResult::GRADING_QUEUED, PostTestResult::GRADING_PROCESSING])->count(),
                'needs_review' => $triageRows->filter(fn($r) => (bool) $r->needs_manual_review && $r->grading_status !== PostTestResult::GRADING_PUBLISHED)->count(),
                'failed'       => $triageRows->where('grading_status', PostTestResult::GRADING_FAILED)->count(),
                'draft_ready'  => $triageRows->filter(fn($r) => in_array($r->grading_status, [PostTestResult::GRADING_DRAFT_GRADED, PostTestResult::GRADING_MANUAL_REVIEW], true) && (!is_null($r->final_score) || !is_null($r->ai_score)))->count(),
                'published'    => $triageRows->where('grading_status', PostTestResult::GRADING_PUBLISHED)->count(),
            ];

            $activeBatch = GradingBatch::where('post_test_id', $selectedPostTest->id)
                ->where(fn($q) => $q->where('bimtek_event_id', $event->id)->orWhereNull('bimtek_event_id'))
                ->latest('started_at')
                ->first();

            $subQuery = (clone $baseSubQuery)->with(['user.profile']);
            if ($evalSearch !== '') {
                $subQuery->whereHas('user', function ($uq) use ($evalSearch) {
                    $uq->where('name', 'like', "%{$evalSearch}%")
                       ->orWhere('email', 'like', "%{$evalSearch}%")
                       ->orWhereHas('profile', fn($pq) => $pq->where('full_name', 'like', "%{$evalSearch}%")->orWhere('instansi', 'like', "%{$evalSearch}%"));
                });
            }

            if ($isManualTest) {
                if (in_array($evalStatus, ['pending', 'submitted'], true)) {
                    $subQuery->whereIn('grading_status', [PostTestResult::GRADING_PENDING, PostTestResult::GRADING_QUEUED, PostTestResult::GRADING_PROCESSING]);
                } elseif ($evalStatus === 'needs_review') {
                    $subQuery->where('needs_manual_review', true)->where('grading_status', '!=', PostTestResult::GRADING_PUBLISHED);
                } elseif ($evalStatus === 'failed') {
                    $subQuery->where('grading_status', PostTestResult::GRADING_FAILED);
                } elseif ($evalStatus === 'draft_graded') {
                    $subQuery->whereIn('grading_status', [PostTestResult::GRADING_DRAFT_GRADED, PostTestResult::GRADING_MANUAL_REVIEW]);
                } elseif (in_array($evalStatus, ['published', 'graded'], true)) {
                    $subQuery->where('grading_status', PostTestResult::GRADING_PUBLISHED);
                }
            }

            $evalSubmissions = $subQuery
                ->orderByRaw("CASE
                    WHEN grading_status = 'FAILED' THEN 0
                    WHEN needs_manual_review = 1 AND grading_status != 'PUBLISHED' THEN 1
                    WHEN grading_status IN ('PENDING', 'QUEUED', 'PROCESSING') THEN 2
                    WHEN grading_status IN ('DRAFT_GRADED', 'MANUAL_REVIEW') THEN 3
                    ELSE 4 END")
                ->latest('completed_at')
                ->latest('updated_at')
                ->paginate(15, ['*'], 'eval_page')
                ->withQueryString();
        }

        return view('lms::admin-pusat.bimtek-events.show', compact(
            'event',
            'activeTab',
            'participants',
            'totalRegistered',
            'totalCompleted',
            'totalAttendedZoom',
            'totalCertified',
            'totalSessionsCount',
            'totalAgendaDays',
            'pendingEvalTotal',
            'readyForPpsdmCount',
            'evaluations',
            'evaluationsCount',
            'avgMateri',
            'avgPenyampaian',
            'avgFasilitasi',
            'avgOverall',
            'attendanceDays',
            'activeAttendanceDay',
            'attendanceSummary',
            'attendanceRows',
            'attSearch',
            'attFilter',
            'evalDailyAgendas',
            'evalFinalEvaluation',
            'selectedPostTest',
            'selectedPostTestHeaderTitle',
            'isManualTest',
            'triageCounts',
            'activeBatch',
            'evalSubmissions',
            'evalSearch',
            'evalStatus'
        ));
    }

    /**
     * Show form to edit Bimtek Event.
     */
    public function edit($id)
    {
        $event = BimtekEvent::with('sessions')->findOrFail($id);
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

            // 1. Periode Pendaftaran (Hanya Tanggal - Wajib)
            'registration_start_at'    => 'required|date_format:Y-m-d',
            'registration_end_at'      => 'required|date_format:Y-m-d|after_or_equal:registration_start_at',

            // 2. Periode Pelaksanaan (Hanya Tanggal, harus berurutan setelah pendaftaran - Wajib)
            'start_date'               => 'required|date_format:Y-m-d|after_or_equal:registration_end_at',
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
            'submission_drive_link'    => 'nullable|url|max:1000',

            // Multi-Sesi Zoom & Presensi Harian (Skenario 1)
            'sessions'                         => 'nullable|array',
            'sessions.*.id'                    => 'nullable|string',
            'sessions.*.title'                 => 'required_with:sessions|string|max:255',
            'sessions.*.session_date'          => 'required_with:sessions|date_format:Y-m-d',
            'sessions.*.start_time'            => 'required_with:sessions|date',
            'sessions.*.end_time'              => 'nullable|date',
            'sessions.*.zoom_url'              => 'nullable|url',
            'sessions.*.zoom_meeting_id'       => 'nullable|string|max:100',
            'sessions.*.zoom_passcode'         => 'nullable|string|max:100',
            'sessions.*.attendance_start_at'   => 'nullable|date',
            'sessions.*.attendance_end_at'     => 'nullable|date',

            // 3. Periode Evaluasi & Sertifikat (Hanya Tanggal, setelah pelaksanaan - Wajib)
            'assessment_start_at'      => 'required|date_format:Y-m-d|after_or_equal:end_date',
            'assessment_end_at'        => 'required|date_format:Y-m-d|after_or_equal:assessment_start_at',

            'location_name'            => 'nullable|string|max:255',
            'location_address'         => 'nullable|string',
        ], [
            'registration_start_at.required'     => 'Tanggal buka pendaftaran wajib diisi.',
            'registration_end_at.required'       => 'Tanggal tutup pendaftaran wajib diisi.',
            'registration_end_at.after_or_equal' => 'Tanggal tutup pendaftaran harus sama atau setelah tanggal buka pendaftaran.',
            'start_date.required'                => 'Tanggal mulai pelaksanaan wajib diisi.',
            'start_date.after_or_equal'          => 'Tanggal mulai pelaksanaan harus setelah atau sama dengan tanggal tutup pendaftaran.',
            'end_date.required'                  => 'Tanggal selesai pelaksanaan wajib diisi.',
            'end_date.after_or_equal'            => 'Tanggal selesai pelaksanaan harus sama atau setelah tanggal mulai pelaksanaan.',
            'assessment_start_at.required'       => 'Tanggal mulai evaluasi wajib diisi.',
            'assessment_start_at.after_or_equal' => 'Tanggal mulai masa evaluasi tidak boleh sebelum tanggal selesai pelaksanaan.',
            'assessment_end_at.required'         => 'Tanggal batas akhir evaluasi wajib diisi.',
            'assessment_end_at.after_or_equal'   => 'Tanggal batas akhir evaluasi & sertifikat harus sama atau setelah tanggal mulai evaluasi.',
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

            // 3. Validasi Multi-Sesi Zoom & Presensi Harian (Skenario 1)
            $sessions = $request->input('sessions', []);
            if (is_array($sessions)) {
                foreach ($sessions as $i => $s) {
                    $idx = $i + 1;
                    $sTitle = !empty($s['title']) ? $s['title'] : "Sesi Hari {$idx}";
                    $sDate = $s['session_date'] ?? null;
                    $sStart = $s['start_time'] ?? null;
                    $sEnd = $s['end_time'] ?? null;
                    $sAttStart = $s['attendance_start_at'] ?? null;
                    $sAttEnd = $s['attendance_end_at'] ?? null;

                    if ($sDate && $execStart && $execEnd) {
                        if ($sDate < $execStart || $sDate > $execEnd) {
                            $v->errors()->add("sessions.{$i}.session_date", "Tanggal {$sTitle} harus berada di dalam rentang tanggal pelaksanaan ({$execStart} s/d {$execEnd}).");
                        }
                    }

                    if ($sStart && $sDate) {
                        $parsedDate = \Carbon\Carbon::parse($sStart)->toDateString();
                        if ($parsedDate !== $sDate) {
                            $v->errors()->add("sessions.{$i}.start_time", "Jam mulai {$sTitle} harus berada pada tanggal sesi yang sama ({$sDate}).");
                        }
                    }

                    if ($sEnd && $sStart) {
                        if (\Carbon\Carbon::parse($sEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.end_time", "Jam selesai {$sTitle} harus setelah jam mulai.");
                        }
                    }

                    if ($sAttStart) {
                        if (!$sStart) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Tentukan jam mulai {$sTitle} terlebih dahulu sebelum jam buka presensi.");
                        } elseif (\Carbon\Carbon::parse($sAttStart)->lessThan(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Jam buka presensi {$sTitle} tidak boleh lebih awal dari jam mulai sesi.");
                        }
                        if ($sEnd && \Carbon\Carbon::parse($sAttStart)->greaterThan(\Carbon\Carbon::parse($sEnd))) {
                            $v->errors()->add("sessions.{$i}.attendance_start_at", "Jam buka presensi {$sTitle} tidak boleh melewati jam selesai sesi.");
                        }
                    }

                    if ($sAttEnd) {
                        if ($sAttStart && \Carbon\Carbon::parse($sAttEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sAttStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} harus setelah jam buka presensi.");
                        } elseif ($sStart && \Carbon\Carbon::parse($sAttEnd)->lessThanOrEqualTo(\Carbon\Carbon::parse($sStart))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} harus setelah jam mulai sesi.");
                        }
                        if ($sEnd && \Carbon\Carbon::parse($sAttEnd)->greaterThan(\Carbon\Carbon::parse($sEnd))) {
                            $v->errors()->add("sessions.{$i}.attendance_end_at", "Jam tutup presensi {$sTitle} tidak boleh melewati jam selesai sesi.");
                        }
                    }
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

        // Ambil data sessions jika ada dan sinkronkan bounds utama
        $sessionsData = $request->input('sessions', []);
        if (!empty($sessionsData) && is_array($sessionsData)) {
            usort($sessionsData, function ($a, $b) {
                $da = ($a['session_date'] ?? '') . ' ' . ($a['start_time'] ?? '');
                $db = ($b['session_date'] ?? '') . ' ' . ($b['start_time'] ?? '');
                return strcmp($da, $db);
            });

            $firstSession = reset($sessionsData);
            $lastSession = end($sessionsData);

            if (!empty($firstSession['start_time'])) {
                $validated['zoom_scheduled_at'] = \Carbon\Carbon::parse($firstSession['start_time']);
            }
            if (!empty($lastSession['end_time'])) {
                $validated['zoom_end_at'] = \Carbon\Carbon::parse($lastSession['end_time']);
            }
            if (!empty($firstSession['attendance_start_at'])) {
                $validated['zoom_attendance_start_at'] = \Carbon\Carbon::parse($firstSession['attendance_start_at']);
            }
            if (!empty($lastSession['attendance_end_at'])) {
                $validated['zoom_attendance_end_at'] = \Carbon\Carbon::parse($lastSession['attendance_end_at']);
            }
        }

        unset($validated['sessions']);

        $event->update($validated);

        // Update/create/sync sessions
        $keptSessionIds = [];
        if (!empty($sessionsData)) {
            foreach ($sessionsData as $index => $s) {
                if (empty($s['session_date']) || empty($s['start_time'])) continue;

                $sessionModel = null;
                if (!empty($s['id'])) {
                    $sessionModel = BimtekEventSession::where('bimtek_event_id', $event->id)->where('id', $s['id'])->first();
                }

                $sessionPayload = [
                    'session_number'      => $index + 1,
                    'title'               => !empty($s['title']) ? $s['title'] : ('Sesi Hari ' . ($index + 1)),
                    'session_date'        => $s['session_date'],
                    'start_time'          => \Carbon\Carbon::parse($s['start_time']),
                    'end_time'            => !empty($s['end_time']) ? \Carbon\Carbon::parse($s['end_time']) : null,
                    'zoom_url'            => !empty($s['zoom_url']) ? $s['zoom_url'] : null,
                    'zoom_meeting_id'     => !empty($s['zoom_meeting_id']) ? $s['zoom_meeting_id'] : null,
                    'zoom_passcode'       => !empty($s['zoom_passcode']) ? $s['zoom_passcode'] : null,
                    'attendance_start_at' => !empty($s['attendance_start_at']) ? \Carbon\Carbon::parse($s['attendance_start_at']) : null,
                    'attendance_end_at'   => !empty($s['attendance_end_at']) ? \Carbon\Carbon::parse($s['attendance_end_at']) : null,
                ];

                if ($sessionModel) {
                    $sessionModel->update($sessionPayload);
                } else {
                    $sessionPayload['bimtek_event_id'] = $event->id;
                    $sessionPayload['attendance_code'] = 'HADIR-S' . ($index + 1) . '-' . strtoupper(Str::random(3));
                    $sessionModel = BimtekEventSession::create($sessionPayload);
                }
                $keptSessionIds[] = $sessionModel->id;
            }

            // Hapus sesi yang telah dihapus admin di form
            BimtekEventSession::where('bimtek_event_id', $event->id)
                ->whereNotIn('id', $keptSessionIds)
                ->delete();
        } else {
            BimtekEventSession::where('bimtek_event_id', $event->id)->delete();
        }

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
