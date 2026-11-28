<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use App\Support\SmartCache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\Library;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\CourseService;
use Modules\MasterData\Models\Institution;
use Modules\MasterData\Models\Province;
use Modules\User\Enums\InstitutionType;
use Modules\User\Models\UserProfile;
use Modules\User\Models\UserScope;

class UserDashboardService
{
    public function __construct(
        private CourseService $courseService
    ) {}

    /**
     * Get aggregated user dashboard data (SmartCached per-user + auto-invalidated on LMS/profile changes).
     */
    public function getDashboardData(User $user): array
    {
        return SmartCache::remember(
            [
                SmartCache::DOMAIN_LMS_EVENTS,
                SmartCache::DOMAIN_LMS_COURSES,
                SmartCache::DOMAIN_LMS_SUBMISSIONS,
                SmartCache::DOMAIN_LMS_LIBRARY,
                SmartCache::userDomain($user->id),
            ],
            "user_dashboard:{$user->id}:" . now()->format('Y-m-d'),
            180,
            function () use ($user): array {
                $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);
                $provinces = Province::all();
                $stats = $this->courseService->myCourseStats();
                $currentYear = (int) now()->year;
                $now = now();

                // Batch ambil seluruh data enrollment user (termasuk bimtek_event_id & sertifikat)
                $userEnrollments = DB::table('course_student')
                    ->where('user_id', $user->id)
                    ->get();
                $enrollByCourseId = $userEnrollments->keyBy('course_id');
                $enrollByEventId = $userEnrollments->whereNotNull('bimtek_event_id')->keyBy('bimtek_event_id');

                // Ambil seluruh event tahun berjalan + event yang diikuti user (beserta sesi Zoom)
                $enrolledEventIds = $userEnrollments->pluck('bimtek_event_id')->filter()->unique()->values()->all();
                $allYearEvents = BimtekEvent::with(['course', 'sessions'])
                    ->where(function ($q) use ($currentYear, $enrolledEventIds) {
                        $q->whereYear('start_date', $currentYear)
                          ->orWhere(function ($q2) use ($currentYear) {
                              $q2->whereNull('start_date')->whereYear('created_at', $currentYear);
                          });
                        if (!empty($enrolledEventIds)) {
                            $q->orWhereIn('id', $enrolledEventIds);
                        }
                    })
                    ->orderBy('start_date')
                    ->orderBy('created_at')
                    ->get();

                $eventsById = $allYearEvents->keyBy('id');

                $fmtRange = function ($s, $e): string {
                    if (!$s) return '-';
                    if (!$e || $s->isSameDay($e)) return $s->translatedFormat('d M Y');
                    if ($s->month === $e->month && $s->year === $e->year) {
                        return $s->format('d') . ' – ' . $e->translatedFormat('d M Y');
                    }
                    return $s->translatedFormat('d M') . ' – ' . $e->translatedFormat('d M Y');
                };

                $resolveEventMeta = function (?BimtekEvent $ev) use ($fmtRange, $now): array {
                    if (!$ev) {
                        return [
                            'batch_name'     => null,
                            'status_key'     => 'other',
                            'status_label'   => 'Mandiri',
                            'schedule_label' => null,
                            'next_zoom'      => null,
                        ];
                    }

                    $badge = $ev->status_badge;
                    $statusLabel = (string) ($badge['label'] ?? 'Draft');
                    $statusKey = match ($statusLabel) {
                        'Sedang Berlangsung' => 'ongoing',
                        'Pendaftaran Buka'   => 'open_registration',
                        'Selesai'            => 'completed',
                        default              => 'other',
                    };

                    $nextZoom = null;
                    if ($ev->relationLoaded('sessions') && $ev->sessions->isNotEmpty()) {
                        $upcomingSession = $ev->sessions
                            ->filter(fn ($s) => $s->end_time ? $s->end_time->gte($now) : ($s->start_time && $s->start_time->gte($now)))
                            ->sortBy('start_time')
                            ->first();
                        if ($upcomingSession && $upcomingSession->start_time) {
                            $nextZoom = $upcomingSession->title . ' • ' . $upcomingSession->start_time->translatedFormat('d M, H:i') . ' WIB';
                        }
                    } elseif ($ev->zoom_start_at && $ev->zoom_start_at->gte($now)) {
                        $nextZoom = 'Live Zoom • ' . $ev->zoom_start_at->translatedFormat('d M, H:i') . ' WIB';
                    }

                    return [
                        'batch_name'     => $ev->batch_name,
                        'status_key'     => $statusKey,
                        'status_label'   => $statusLabel,
                        'schedule_label' => $fmtRange($ev->start_date, $ev->end_date ?? $ev->start_date),
                        'next_zoom'      => $nextZoom,
                    ];
                };

                // Hitung statistik berbasis event untuk KPI Cards
                $eventStats = [
                    'total_enrolled' => $userEnrollments->count(),
                    'ongoing'        => 0,
                    'upcoming'       => 0,
                    'completed'      => (int) ($stats['selesai'] ?? 0),
                    'certified'      => $userEnrollments->filter(fn ($r) => !empty($r->official_cert_number) || !empty($r->certificate_file))->count(),
                ];

                foreach ($userEnrollments as $enr) {
                    $ev = $enr->bimtek_event_id ? $eventsById->get($enr->bimtek_event_id) : null;
                    $meta = $resolveEventMeta($ev);
                    if ($meta['status_key'] === 'ongoing') {
                        $eventStats['ongoing']++;
                    } elseif ($meta['status_key'] === 'open_registration') {
                        $eventStats['upcoming']++;
                    }
                }

                // 1. Last accessed course + Event context
                $lastCourse = $this->courseService->getLastAccessedCourse();
                if ($lastCourse) {
                    $dbLastCourse = Course::where('slug', $lastCourse->slug)->first();
                    if ($dbLastCourse) {
                        $lastCourse->description = $dbLastCourse->description;
                        $lastCourse->thumbnail = $dbLastCourse->thumbnail;

                        $enr = $enrollByCourseId->get($dbLastCourse->id);
                        $ev = ($enr && $enr->bimtek_event_id) ? $eventsById->get($enr->bimtek_event_id) : null;
                        $meta = $resolveEventMeta($ev);
                        $lastCourse->event_batch_name = $meta['batch_name'];
                        $lastCourse->event_status_key = $meta['status_key'];
                        $lastCourse->event_status_label = $meta['status_label'];
                        $lastCourse->event_schedule_label = $meta['schedule_label'];
                        $lastCourse->next_zoom_label = $meta['next_zoom'];
                        $lastCourse->is_certified = $enr && (!empty($enr->official_cert_number) || !empty($enr->certificate_file));
                    }
                }

                // 2. Recent enrolled courses + Event context (prioritaskan yang sedang berlangsung dulu)
                $recentCourses = $user->enrolledCourses()
                    ->latest('course_student.updated_at')
                    ->get()
                    ->map(function ($course) use ($enrollByCourseId, $eventsById, $resolveEventMeta) {
                        $enr = $enrollByCourseId->get($course->id);
                        $ev = ($enr && $enr->bimtek_event_id) ? $eventsById->get($enr->bimtek_event_id) : null;
                        $meta = $resolveEventMeta($ev);
                        $rawProgress = (int) ($enr->progress ?? $course->pivot->progress ?? 0);
                        $isUserCompleted = ($enr && $enr->status === 'completed') || $rawProgress >= 100;

                        $course->user_progress = $isUserCompleted ? 100 : min(100, max(0, $rawProgress));
                        $course->user_completed = $isUserCompleted;
                        $course->event_batch_name = $meta['batch_name']
                            ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $meta['batch_name'])
                            : null;
                        $course->event_status_key = $isUserCompleted ? 'completed' : $meta['status_key'];
                        $course->event_status_label = $isUserCompleted
                            ? 'Selesai'
                            : match ($meta['status_key']) {
                                'ongoing'           => 'Sedang Berlangsung',
                                'open_registration' => 'Menunggu Pelaksanaan',
                                'completed'         => 'Periode Berakhir',
                                default             => $meta['status_label'],
                            };
                        $course->event_schedule_label = $meta['schedule_label'];
                        $course->next_zoom_label = $meta['next_zoom'];
                        $course->is_certified = $enr && (!empty($enr->official_cert_number) || !empty($enr->certificate_file));
                        $course->sort_priority = match ($course->event_status_key) {
                            'ongoing'           => 1,
                            'open_registration' => 2,
                            'completed'         => $isUserCompleted ? 3 : 4,
                            default             => 5,
                        };
                        return $course;
                    })
                    ->sortBy('sort_priority')
                    ->take(6)
                    ->values();

                // 3. Chart data evaluasi harian & akhir per pelatihan (dukung skor AI/Excel yang sudah dipublikasikan + KKM asli)
                $myCourses = $user->enrolledCourses()->get();
                $chartDataByCourse = [];

                if ($myCourses->count() > 0) {
                    $courseIds = $myCourses->pluck('id');

                    $allPostTests = PostTest::whereIn('course_id', $courseIds)
                        ->orderByRaw('course_section_id IS NULL, id')
                        ->get();
                    $postTestIds = $allPostTests->pluck('id');

                    $userResults = PostTestResult::selectRaw("post_test_id, MAX(COALESCE(final_score, score, 0)) as user_score")
                        ->where('user_id', $user->id)
                        ->whereIn('post_test_id', $postTestIds)
                        ->where(function ($q) {
                            $q->where('grading_status', PostTestResult::GRADING_PUBLISHED)
                              ->orWhere('status', 'graded')
                              ->orWhere(function ($q2) {
                                  $q2->whereNull('grading_status')->whereNotNull('score');
                              });
                        })
                        ->groupBy('post_test_id')
                        ->pluck('user_score', 'post_test_id');

                    foreach ($myCourses as $c) {
                        $courseTests = $allPostTests->where('course_id', $c->id);
                        if ($courseTests->isEmpty()) {
                            continue;
                        }

                        $enr = $enrollByCourseId->get($c->id);
                        $ev = ($enr && $enr->bimtek_event_id) ? $eventsById->get($enr->bimtek_event_id) : null;

                        $labels = [];
                        $uScores = [];
                        $passingScores = [];

                        foreach ($courseTests as $test) {
                            $labels[] = $test->title;
                            $uScores[] = round((float) ($userResults[$test->id] ?? 0), 1);
                            $passingScores[] = (int) $test->effectivePassingGrade();
                        }

                        $chartDataByCourse[$c->id] = [
                            'course_id'      => $c->id,
                            'course_name'    => $ev ? $ev->batch_name : $c->name,
                            'labels'         => $labels,
                            'user_scores'    => $uScores,
                            'passing_scores' => $passingScores,
                        ];
                    }
                }

                // 4. Gantt Chart Jadwal Pelatihan Tersedia (HANYA pelatihan tersedia di Katalog / belum didaftarkan user)
                $ganttEvents = [];
                foreach ($allYearEvents as $ev) {
                    $course = $ev->course;
                    if (!$course) {
                        continue;
                    }
                    if (!$user->canAccessCourseAudience($course->audience)) {
                        continue;
                    }

                    // Hanya tampilkan pelatihan yang belum didaftarkan oleh user (tersedia di Katalog)
                    if ($enrollByEventId->has($ev->id) || $enrollByCourseId->has($ev->course_id)) {
                        continue;
                    }

                    $meta = $resolveEventMeta($ev);
                    $execStart = $ev->start_date;
                    $execEnd   = $ev->end_date ?? $ev->start_date;
                    $evalStart = $ev->assessment_start_at ?? $execEnd;
                    $evalEnd   = $ev->assessment_end_at ?? $execEnd?->copy()->addDays(3);

                    // Hilangkan event yang sudah selesai
                    $isEventCompleted = ($ev->status === 'completed' || $meta['status_key'] === 'completed')
                        && (!$evalEnd || $now->gt($evalEnd->copy()->endOfDay()));
                    if ($isEventCompleted) {
                        continue;
                    }

                    $regStart  = $ev->registration_start_at ?? $ev->start_date?->copy()->subDays(7);
                    $regEnd    = $ev->registration_end_at ?? $ev->start_date?->copy()->subDay();

                    if ($regStart && $regEnd && $regEnd->lt($regStart)) $regEnd = $regStart->copy();
                    if ($execStart && $execEnd && $execEnd->lt($execStart)) $execEnd = $execStart->copy();
                    if ($evalStart && $evalEnd && $evalEnd->lt($evalStart)) $evalEnd = $evalStart->copy();

                    $ganttEvents[] = [
                        'id'              => $ev->id,
                        'batch_name'      => preg_replace('/\bbatch\b/i', 'Gelombang', (string) $ev->batch_name),
                        'course_name'     => $course->name,
                        'status_key'      => $meta['status_key'],
                        'status_label'    => $meta['status_label'],
                        'is_enrolled'     => false,
                        'user_progress'   => 0,
                        'user_completed'  => false,
                        'is_certified'    => false,
                        'next_zoom_label' => $meta['next_zoom'],
                        'reg_start'       => $regStart?->format('Y-m-d'),
                        'reg_end'         => $regEnd?->format('Y-m-d'),
                        'reg_label'       => $fmtRange($regStart, $regEnd),
                        'exec_start'      => $execStart?->format('Y-m-d'),
                        'exec_end'        => $execEnd?->format('Y-m-d'),
                        'exec_label'      => $fmtRange($execStart, $execEnd),
                        'eval_start'      => $evalStart?->format('Y-m-d'),
                        'eval_end'        => $evalEnd?->format('Y-m-d'),
                        'eval_label'      => $fmtRange($evalStart, $evalEnd),
                        'sort_date'       => $execStart?->format('Y-m-d') ?? $regStart?->format('Y-m-d') ?? '9999-12-31',
                        'url'             => route('user.catalog.index', ['search' => $course->name]),
                    ];
                }

                $ganttEvents = collect($ganttEvents)->sortBy('sort_date')->values()->all();

                $userEventGantt = [
                    'year'         => $currentYear,
                    'today'        => $now->format('Y-m-d'),
                    'today_label'  => $now->translatedFormat('d M Y'),
                    'has_enrolled' => !empty($ganttEvents),
                    'events'       => $ganttEvents,
                ];

                // 4b. 1 Pelatihan Aktif / Mendekati Pelaksanaan (fokus single active training user)
                $enrolledCoursesLookup = $myCourses->keyBy('id');
                $featuredCandidates = [];
                foreach ($userEnrollments as $enr) {
                    $progress = (int) ($enr->progress ?? 0);
                    $isDone = ($enr->status === 'completed') || $progress >= 100;
                    if ($isDone) {
                        continue;
                    }

                    $course = $enrolledCoursesLookup->get($enr->course_id);
                    if (!$course) {
                        continue;
                    }

                    $ev = $enr->bimtek_event_id ? $eventsById->get($enr->bimtek_event_id) : null;
                    $meta = $resolveEventMeta($ev);
                    $execStart = $ev?->start_date;
                    $execEnd = $ev?->end_date ?? $execStart;
                    $isPastExec = $execEnd && $now->copy()->startOfDay()->gt($execEnd->copy()->startOfDay());

                    if ($meta['status_key'] === 'ongoing') {
                        $priority = 1;
                        $displayStatusKey = 'ongoing';
                        $displayStatusLabel = 'Sedang Berlangsung';
                        $ctaLabel = 'Lanjutkan Belajar';
                        $sortTs = ($execEnd ?? $now)->timestamp;
                    } elseif ($meta['status_key'] === 'open_registration' && $execStart && $execStart->copy()->startOfDay()->gt($now->copy()->startOfDay())) {
                        $priority = 2;
                        $displayStatusKey = 'open_registration';
                        $displayStatusLabel = 'Menunggu Pelaksanaan';
                        $ctaLabel = 'Lihat Jadwal & Detail';
                        $sortTs = $execStart->timestamp;
                    } elseif ($meta['status_key'] === 'completed' || $isPastExec) {
                        $priority = 3;
                        $displayStatusKey = 'evaluation';
                        $displayStatusLabel = 'Periode Evaluasi';
                        $ctaLabel = 'Kerjakan Evaluasi';
                        $sortTs = ($ev?->assessment_end_at ?? $execEnd ?? $now)->timestamp;
                    } else {
                        $priority = 4;
                        $displayStatusKey = $meta['status_key'];
                        $displayStatusLabel = $meta['status_label'];
                        $ctaLabel = 'Lanjutkan Belajar';
                        $sortTs = ($execStart ?? $now)->timestamp;
                    }

                    $countdownLabel = null;
                    if ($displayStatusKey === 'open_registration' && $execStart) {
                        $daysDiff = (int) $now->copy()->startOfDay()->diffInDays($execStart->copy()->startOfDay(), false);
                        if ($daysDiff === 0) {
                            $countdownLabel = 'Dimulai hari ini';
                        } elseif ($daysDiff === 1) {
                            $countdownLabel = 'Mulai besok';
                        } elseif ($daysDiff > 1) {
                            $countdownLabel = 'Mulai ' . $daysDiff . ' hari lagi';
                        }
                    }

                    $thumbUrl = !empty($course->thumbnail)
                        ? (str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : asset('storage/' . $course->thumbnail))
                        : null;

                    $featuredCandidates[] = [
                        'priority'        => $priority,
                        'sort_ts'         => $sortTs,
                        'course_id'       => $course->id,
                        'bimtek_event_id' => $ev?->id,
                        'course_name'     => $course->name,
                        'slug'            => $course->slug,
                        'thumbnail'       => $thumbUrl,
                        'batch_name'      => $meta['batch_name']
                            ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $meta['batch_name'])
                            : null,
                        'status_key'      => $displayStatusKey,
                        'status_label'    => $displayStatusLabel,
                        'schedule_label'  => $meta['schedule_label'],
                        'next_zoom_label' => $meta['next_zoom'],
                        'progress'        => min(100, max(0, $progress)),
                        'countdown_label' => $countdownLabel,
                        'cta_label'       => $ctaLabel,
                        'url'             => route('user.course.my-course.detail', $course->slug),
                    ];
                }

                $featuredActiveCourse = collect($featuredCandidates)
                    ->sort(function ($a, $b) {
                        if ($a['priority'] !== $b['priority']) {
                            return $a['priority'] <=> $b['priority'];
                        }
                        return $a['sort_ts'] <=> $b['sort_ts'];
                    })
                    ->first();

                // 4c. Sesi Terdekat (Prioritas utama: Live Zoom harian -> Pembukaan Kelas -> Evaluasi Harian -> Evaluasi Akhir -> Evaluasi Narasumber)
                $nearestSession = null;
                $attendedSessionIds = DB::table('bimtek_session_attendances')
                    ->where('user_id', $user->id)
                    ->pluck('bimtek_event_session_id')
                    ->filter()
                    ->flip();
                $filledInstructorEvalEventIds = DB::table('bimtek_instructor_evaluations')
                    ->where('user_id', $user->id)
                    ->pluck('bimtek_event_id')
                    ->filter()
                    ->flip();
                $submittedPostTestMap = isset($postTestIds) && $postTestIds->isNotEmpty()
                    ? PostTestResult::selectRaw("
                            post_test_id,
                            MAX(CASE WHEN grading_status = 'published' OR status = 'graded' OR (grading_status IS NULL AND score IS NOT NULL) THEN COALESCE(final_score, score, 0) ELSE NULL END) as best_graded_score,
                            SUM(CASE WHEN grading_status = 'pending' OR (grading_status IS NULL AND score IS NULL) THEN 1 ELSE 0 END) as pending_count,
                            COUNT(*) as attempts
                        ")
                        ->where('user_id', $user->id)
                        ->whereIn('post_test_id', $postTestIds)
                        ->groupBy('post_test_id')
                        ->get()
                        ->keyBy('post_test_id')
                    : collect();

                if (!empty($featuredActiveCourse)) {
                    $fcEv = !empty($featuredActiveCourse['bimtek_event_id'])
                        ? $eventsById->get($featuredActiveCourse['bimtek_event_id'])
                        : null;
                    $fcUrl = $featuredActiveCourse['url'];
                    $fcStatusKey = $featuredActiveCourse['status_key'];
                    $fcCourseId = $featuredActiveCourse['course_id'] ?? null;
                    $fcSlug = $featuredActiveCourse['slug'] ?? null;
                    $fcProgress = (int) ($featuredActiveCourse['progress'] ?? 0);

                    if ($fcEv) {
                        $eventDisplayName = $featuredActiveCourse['batch_name']
                            ?: ($fcEv->name ?: $featuredActiveCourse['course_name']);
                        $nextZoomSess = null;
                        if ($fcEv->relationLoaded('sessions') && $fcEv->sessions->isNotEmpty()) {
                            $nextZoomSess = $fcEv->sessions
                                ->filter(function ($s) use ($now, $attendedSessionIds) {
                                    $endRef = $s->end_time ?? $s->start_time;
                                    return $endRef && $endRef->gte($now) && !$attendedSessionIds->has($s->id);
                                })
                                ->sortBy('start_time')
                                ->first();
                        }

                        // Prioritas 1: Live Zoom Harian
                        if ($nextZoomSess && $nextZoomSess->start_time) {
                            $isToday = $nextZoomSess->start_time->isToday();
                            $sessStartId = $nextZoomSess->start_time->copy()->locale('id');
                            $zoomDayNumber = (int) ($nextZoomSess->session_number ?? 0);
                            if ($zoomDayNumber <= 0 && $fcEv->relationLoaded('sessions')) {
                                $sortedIds = $fcEv->sessions->sortBy('start_time')->pluck('id')->values();
                                $foundIdx = $sortedIds->search($nextZoomSess->id);
                                $zoomDayNumber = $foundIdx !== false ? ($foundIdx + 1) : 1;
                            }
                            if ($zoomDayNumber <= 0) {
                                $zoomDayNumber = 1;
                            }
                            $nearestSession = [
                                'kind'         => 'zoom',
                                'is_today'     => $isToday,
                                'status_label' => $isToday ? 'Hari Ini' : 'Live Zoom',
                                'subtitle'     => $eventDisplayName,
                                'title'        => 'Live Zoom Hari ke-' . $zoomDayNumber . ': ' . $featuredActiveCourse['course_name'],
                                'course_name'  => $featuredActiveCourse['course_name'],
                                'batch_name'   => $featuredActiveCourse['batch_name'],
                                'day_number'   => $sessStartId->format('d'),
                                'month_short'  => strtoupper($sessStartId->translatedFormat('M')),
                                'date_label'   => $sessStartId->translatedFormat('l, d M Y'),
                                'time_label'   => $nextZoomSess->start_time->format('H:i')
                                    . ($nextZoomSess->end_time ? ' – ' . $nextZoomSess->end_time->format('H:i') : '') . ' WIB',
                                'action_label' => ($isToday || $fcStatusKey === 'ongoing') ? 'Masuk Sesi' : 'Lihat Detail',
                                'url'          => $fcUrl,
                            ];
                        } elseif ($fcEv->zoom_start_at && $fcEv->zoom_start_at->gte($now)) {
                            $isToday = $fcEv->zoom_start_at->isToday();
                            $zoomStartId = $fcEv->zoom_start_at->copy()->locale('id');
                            $nearestSession = [
                                'kind'         => 'zoom',
                                'is_today'     => $isToday,
                                'status_label' => $isToday ? 'Hari Ini' : 'Live Zoom',
                                'subtitle'     => $eventDisplayName,
                                'title'        => 'Live Zoom Hari ke-1: ' . $featuredActiveCourse['course_name'],
                                'course_name'  => $featuredActiveCourse['course_name'],
                                'batch_name'   => $featuredActiveCourse['batch_name'],
                                'day_number'   => $zoomStartId->format('d'),
                                'month_short'  => strtoupper($zoomStartId->translatedFormat('M')),
                                'date_label'   => $zoomStartId->translatedFormat('l, d M Y'),
                                'time_label'   => $fcEv->zoom_start_at->format('H:i') . ' WIB',
                                'action_label' => ($isToday || $fcStatusKey === 'ongoing') ? 'Masuk Sesi' : 'Lihat Detail',
                                'url'          => $fcUrl,
                            ];
                        } elseif ($fcStatusKey === 'open_registration' && $fcEv->start_date && $fcEv->start_date->copy()->endOfDay()->gte($now)) {
                            // Prioritas 2: Pembukaan Kelas (menunggu jadwal mulai)
                            $isToday = $fcEv->start_date->isToday();
                            $startId = $fcEv->start_date->copy()->locale('id');
                            $nearestSession = [
                                'kind'         => 'start',
                                'is_today'     => $isToday,
                                'status_label' => $isToday ? 'Dimulai Hari Ini' : 'Pembukaan Kelas',
                                'subtitle'     => $eventDisplayName,
                                'title'        => 'Pembukaan Kelas: ' . $featuredActiveCourse['course_name'],
                                'course_name'  => $featuredActiveCourse['course_name'],
                                'batch_name'   => $featuredActiveCourse['batch_name'],
                                'day_number'   => $startId->format('d'),
                                'month_short'  => strtoupper($startId->translatedFormat('M')),
                                'date_label'   => $startId->translatedFormat('l, d M Y'),
                                'time_label'   => null,
                                'action_label' => 'Lihat Jadwal Kelas',
                                'url'          => $fcUrl,
                            ];
                        }

                        // Prioritas 3 & 4: Evaluasi Harian -> Evaluasi Akhir (jika tidak ada Live Zoom mendatang)
                        if ($nearestSession === null && $fcStatusKey !== 'open_registration' && $fcCourseId && isset($allPostTests)) {
                            $fcTests = $allPostTests->where('course_id', $fcCourseId);
                            $fcSectionTestsOrdered = $fcTests->whereNotNull('course_section_id')->values();
                            $fcPendingTests = $fcTests->filter(function ($test) use ($submittedPostTestMap) {
                                $res = $submittedPostTestMap->get($test->id);
                                if (!$res) {
                                    return true;
                                }
                                if ((int) ($res->pending_count ?? 0) > 0 && is_null($res->best_graded_score)) {
                                    return false;
                                }
                                if (is_null($res->best_graded_score)) {
                                    return true;
                                }
                                return (float) $res->best_graded_score < (float) $test->effectivePassingGrade();
                            });

                            $nextDailyEval = $fcPendingTests->first(fn ($t) => !empty($t->course_section_id));
                            $nextFinalEval = $fcPendingTests->first(fn ($t) => empty($t->course_section_id));
                            $chosenEval = $nextDailyEval ?? $nextFinalEval;

                            if ($chosenEval) {
                                $isDaily = !empty($chosenEval->course_section_id);
                                if ($isDaily) {
                                    if (preg_match('/(?:modul|hari|agenda)\s*(?:ke[\-\s]*)?(\d+)/i', (string) $chosenEval->title, $m)) {
                                        $agendaNum = (int) $m[1];
                                    } else {
                                        $pos = $fcSectionTestsOrdered->search(fn ($t) => $t->id === $chosenEval->id);
                                        $agendaNum = $pos !== false ? ($pos + 1) : 1;
                                    }
                                    $evalTitle = 'Evaluasi Agenda ke-' . $agendaNum . ': ' . $featuredActiveCourse['course_name'];
                                    $evalBadge = 'Evaluasi Harian';
                                } else {
                                    $evalTitle = 'Evaluasi Akhir: ' . $featuredActiveCourse['course_name'];
                                    $evalBadge = 'Evaluasi Akhir';
                                }

                                $evalDeadline = $fcEv->assessment_end_at ?? $fcEv->end_date;
                                $evalRef = $evalDeadline ?? $now;
                                $evalId = $evalRef->copy()->locale('id');
                                $isToday = $evalDeadline ? $evalDeadline->isToday() : false;

                                $nearestSession = [
                                    'kind'         => 'evaluation',
                                    'is_today'     => $isToday,
                                    'status_label' => $isToday ? 'Batas Hari Ini' : $evalBadge,
                                    'subtitle'     => $eventDisplayName,
                                    'title'        => $evalTitle,
                                    'course_name'  => $featuredActiveCourse['course_name'],
                                    'batch_name'   => $featuredActiveCourse['batch_name'],
                                    'day_number'   => $evalId->format('d'),
                                    'month_short'  => strtoupper($evalId->translatedFormat('M')),
                                    'date_label'   => $evalDeadline
                                        ? 'Batas ' . $evalId->translatedFormat('l, d M Y')
                                        : 'Tersedia untuk dikerjakan',
                                    'time_label'   => null,
                                    'action_label' => 'Kerjakan Evaluasi',
                                    'url'          => $fcUrl,
                                ];
                            }
                        }

                        // Prioritas 5: Evaluasi Narasumber
                        if ($nearestSession === null && $fcStatusKey !== 'open_registration' && !$filledInstructorEvalEventIds->has($fcEv->id)) {
                            $inEvalWindow = ($fcEv->end_date && $now->gte($fcEv->end_date->copy()->startOfDay()))
                                || ($fcEv->assessment_start_at && $now->gte($fcEv->assessment_start_at))
                                || $fcStatusKey === 'evaluation'
                                || $fcProgress >= 75;

                            if ($inEvalWindow) {
                                $naraDeadline = $fcEv->assessment_end_at ?? $fcEv->end_date;
                                $naraRef = $naraDeadline ?? $now;
                                $naraId = $naraRef->copy()->locale('id');
                                $isToday = $naraDeadline ? $naraDeadline->isToday() : false;

                                $nearestSession = [
                                    'kind'         => 'narasumber',
                                    'is_today'     => $isToday,
                                    'status_label' => $isToday ? 'Batas Hari Ini' : 'Evaluasi Narasumber',
                                    'subtitle'     => $eventDisplayName,
                                    'title'        => 'Evaluasi Narasumber: ' . $featuredActiveCourse['course_name'],
                                    'course_name'  => $featuredActiveCourse['course_name'],
                                    'batch_name'   => $featuredActiveCourse['batch_name'],
                                    'day_number'   => $naraId->format('d'),
                                    'month_short'  => strtoupper($naraId->translatedFormat('M')),
                                    'date_label'   => $naraDeadline
                                        ? 'Batas ' . $naraId->translatedFormat('l, d M Y')
                                        : 'Wajib diisi sebelum sertifikat terbit',
                                    'time_label'   => null,
                                    'action_label' => 'Isi Evaluasi',
                                    'url'          => $fcSlug
                                        ? route('user.course.my-course.evaluasi-narasumber', $fcSlug)
                                        : $fcUrl,
                                ];
                            }
                        }

                        // Prioritas 6: Fallback Pembukaan Kelas / Batas Evaluasi Umum
                        if ($nearestSession === null && $fcEv->start_date && $fcEv->start_date->copy()->endOfDay()->gte($now)) {
                            $isToday = $fcEv->start_date->isToday();
                            $startId = $fcEv->start_date->copy()->locale('id');
                            $nearestSession = [
                                'kind'         => 'start',
                                'is_today'     => $isToday,
                                'status_label' => $isToday ? 'Dimulai Hari Ini' : 'Pembukaan Kelas',
                                'subtitle'     => $eventDisplayName,
                                'title'        => 'Pembukaan Kelas: ' . $featuredActiveCourse['course_name'],
                                'course_name'  => $featuredActiveCourse['course_name'],
                                'batch_name'   => $featuredActiveCourse['batch_name'],
                                'day_number'   => $startId->format('d'),
                                'month_short'  => strtoupper($startId->translatedFormat('M')),
                                'date_label'   => $startId->translatedFormat('l, d M Y'),
                                'time_label'   => null,
                                'action_label' => 'Lihat Jadwal Kelas',
                                'url'          => $fcUrl,
                            ];
                        } elseif ($nearestSession === null && ($fcEv->assessment_end_at ?? $fcEv->end_date) && ($fcEv->assessment_end_at ?? $fcEv->end_date)->copy()->endOfDay()->gte($now)) {
                            $evalRef = $fcEv->assessment_end_at ?? $fcEv->end_date;
                            $isToday = $evalRef->isToday();
                            $evalId = $evalRef->copy()->locale('id');
                            $nearestSession = [
                                'kind'         => 'evaluation',
                                'is_today'     => $isToday,
                                'status_label' => $isToday ? 'Batas Hari Ini' : 'Batas Evaluasi',
                                'subtitle'     => $eventDisplayName,
                                'title'        => 'Evaluasi Pelatihan: ' . $featuredActiveCourse['course_name'],
                                'course_name'  => $featuredActiveCourse['course_name'],
                                'batch_name'   => $featuredActiveCourse['batch_name'],
                                'day_number'   => $evalId->format('d'),
                                'month_short'  => strtoupper($evalId->translatedFormat('M')),
                                'date_label'   => 'Batas ' . $evalId->translatedFormat('l, d M Y'),
                                'time_label'   => null,
                                'action_label' => 'Kerjakan Evaluasi',
                                'url'          => $fcUrl,
                            ];
                        }
                    }
                }

                // 5. Agenda & Tindak Lanjut (fokus pada 1 pelatihan aktif user agar tidak terpecah antar banyak kursus)
                $actionItems = [];

                $enrolledCoursesById = $myCourses->keyBy('id');

                $resolveCardColor = function (?string $thumbnail, ?string $seed = null): string {
                    if ($thumbnail && str_starts_with($thumbnail, 'http')) {
                        $query = parse_url($thumbnail, PHP_URL_QUERY);
                        if (is_string($query)) {
                            parse_str($query, $params);
                            $bg = ltrim((string) ($params['background'] ?? ''), '#');
                            if (preg_match('/^[0-9A-Fa-f]{6}$/', $bg)) {
                                return '#' . strtoupper($bg);
                            }
                        }
                    }

                    $palette = ['#13416B', '#547996', '#8BB1CC', '#79A736'];
                    $idx = abs(crc32((string) ($seed ?? 'course'))) % count($palette);

                    return $palette[$idx];
                };

                $focusedCourseId = $featuredActiveCourse['course_id'] ?? null;

                foreach ($userEnrollments as $enr) {
                    $course = $enrolledCoursesById->get($enr->course_id);
                    if (!$course) {
                        continue;
                    }

                    $progress = (int) ($enr->progress ?? 0);
                    $isDone = ($enr->status === 'completed') || $progress >= 100;
                    $isCertified = !empty($enr->official_cert_number) || !empty($enr->certificate_file);

                    // Jika ada pelatihan aktif, fokuskan agenda hanya pada 1 pelatihan aktif tersebut
                    if ($focusedCourseId !== null && $course->id !== $focusedCourseId) {
                        continue;
                    }

                    $courseCardColor = $resolveCardColor($course->thumbnail, $course->id);
                    $ev = $enr->bimtek_event_id ? $eventsById->get($enr->bimtek_event_id) : null;
                    $meta = $resolveEventMeta($ev);
                    $batchLabel = $meta['batch_name']
                        ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $meta['batch_name'])
                        : null;
                    $courseUrl = route('user.course.my-course.detail', $course->slug);

                    // 5a. Sesi Live Zoom Terdekat / Mendatang pada pelatihan aktif
                    $addedZoomForCourse = false;
                    if ($ev && !$isDone) {
                        $upcomingSessions = collect();
                        if ($ev->relationLoaded('sessions') && $ev->sessions->isNotEmpty()) {
                            $upcomingSessions = $ev->sessions
                                ->filter(function ($s) use ($now, $attendedSessionIds) {
                                    $endRef = $s->end_time ?? $s->start_time;
                                    return $endRef && $endRef->gte($now) && !$attendedSessionIds->has($s->id);
                                })
                                ->sortBy('start_time')
                                ->take(3)
                                ->values();
                        }

                        if ($upcomingSessions->isNotEmpty()) {
                            $hasMultiSessions = $upcomingSessions->count() > 1;
                            foreach ($upcomingSessions as $sIdx => $sess) {
                                if (!$sess->start_time) {
                                    continue;
                                }
                                $addedZoomForCourse = true;
                                $isToday = $sess->start_time->isToday();
                                $timeLabel = $sess->start_time->translatedFormat('d M Y, H:i')
                                    . ($sess->end_time ? '–' . $sess->end_time->format('H:i') : '') . ' WIB';
                                $zoomPrefix = $isToday
                                    ? 'Zoom Hari Ini'
                                    : ($hasMultiSessions ? 'Live Zoom Sesi ' . ($sIdx + 1) : 'Live Zoom');
                                $actionItems[] = [
                                    'priority'    => $isToday ? 10 : 20,
                                    'sort_ts'     => $sess->start_time->timestamp,
                                    'type'        => 'zoom',
                                    'icon'        => 'fas fa-video',
                                    'badge'       => $zoomPrefix,
                                    'batch_name'  => $batchLabel,
                                    'title'       => $zoomPrefix,
                                    'subtitle'    => $timeLabel,
                                    'action_text' => $meta['status_key'] === 'open_registration' ? 'Detail' : 'Masuk',
                                    'card_color'  => $courseCardColor,
                                    'url'         => $courseUrl,
                                ];
                            }
                        } elseif ($ev->zoom_start_at && $ev->zoom_start_at->gte($now)) {
                            $addedZoomForCourse = true;
                            $isToday = $ev->zoom_start_at->isToday();
                            $zoomPrefix = $isToday ? 'Zoom Hari Ini' : 'Live Zoom';
                            $actionItems[] = [
                                'priority'    => $isToday ? 10 : 20,
                                'sort_ts'     => $ev->zoom_start_at->timestamp,
                                'type'        => 'zoom',
                                'icon'        => 'fas fa-video',
                                'badge'       => $zoomPrefix,
                                'batch_name'  => $batchLabel,
                                'title'       => $zoomPrefix,
                                'subtitle'    => $ev->zoom_start_at->translatedFormat('d M Y, H:i') . ' WIB',
                                'action_text' => $meta['status_key'] === 'open_registration' ? 'Detail' : 'Masuk',
                                'card_color'  => $courseCardColor,
                                'url'         => $courseUrl,
                            ];
                        }

                        // Jika masih menunggu pelaksanaan & belum ada jadwal sesi Zoom spesifik, tampilkan jadwal mulai pelatihan
                        if (!$addedZoomForCourse && $meta['status_key'] === 'open_registration' && $ev->start_date) {
                            $actionItems[] = [
                                'priority'    => 25,
                                'sort_ts'     => $ev->start_date->timestamp,
                                'type'        => 'zoom',
                                'icon'        => 'fas fa-calendar-day',
                                'badge'       => 'Jadwal Mulai',
                                'batch_name'  => $batchLabel,
                                'title'       => 'Pelaksanaan Dimulai',
                                'subtitle'    => 'Mulai ' . $ev->start_date->translatedFormat('d M Y'),
                                'action_text' => 'Detail',
                                'card_color'  => $courseCardColor,
                                'url'         => $courseUrl,
                            ];
                        }
                    }

                    // 5b. Evaluasi yang Perlu Diselesaikan pada pelatihan aktif (bisa lebih dari 1 agenda pada kursus yang sama)
                    if (!$isDone && $meta['status_key'] !== 'open_registration' && isset($allPostTests)) {
                        $courseTests = $allPostTests->where('course_id', $course->id);
                        $sectionTestsOrdered = $courseTests->whereNotNull('course_section_id')->values();
                        $pendingTests = $courseTests->filter(function ($test) use ($submittedPostTestMap) {
                            $res = $submittedPostTestMap->get($test->id);
                            if (!$res) {
                                return true;
                            }
                            if ((int) ($res->pending_count ?? 0) > 0 && is_null($res->best_graded_score)) {
                                return false;
                            }
                            if (is_null($res->best_graded_score)) {
                                return true;
                            }
                            return (float) $res->best_graded_score < (float) $test->effectivePassingGrade();
                        })->take(4);

                        foreach ($pendingTests as $tIdx => $pendingTest) {
                            $isSectionEval = !empty($pendingTest->course_section_id);
                            if ($isSectionEval) {
                                if (preg_match('/(?:modul|hari|agenda)\s*(?:ke[\-\s]*)?(\d+)/i', (string) $pendingTest->title, $m)) {
                                    $agendaNumber = (int) $m[1];
                                } else {
                                    $pos = $sectionTestsOrdered->search(fn ($t) => $t->id === $pendingTest->id);
                                    $agendaNumber = $pos !== false ? ($pos + 1) : 1;
                                }
                                $evalPrefix = 'Evaluasi Agenda ke-' . $agendaNumber;
                            } else {
                                $evalPrefix = 'Evaluasi Akhir';
                            }

                            $deadlineRef = $ev ? ($ev->assessment_end_at ?? $ev->end_date) : null;
                            $subInfo = $deadlineRef
                                ? 'Batas ' . $deadlineRef->translatedFormat('d M Y')
                                : ($isSectionEval ? 'Evaluasi harian' : 'Evaluasi akhir');

                            $actionItems[] = [
                                'priority'    => $isSectionEval ? 30 : 32,
                                'sort_ts'     => ($deadlineRef ? $deadlineRef->timestamp : PHP_INT_MAX) - (10 - $tIdx),
                                'type'        => 'evaluation',
                                'icon'        => 'fas fa-clipboard-check',
                                'badge'       => $evalPrefix,
                                'batch_name'  => $batchLabel,
                                'title'       => $evalPrefix,
                                'subtitle'    => $subInfo,
                                'action_text' => 'Kerjakan',
                                'card_color'  => $courseCardColor,
                                'url'         => $courseUrl,
                            ];
                        }
                    }

                    // 5c. Kuesioner Evaluasi Narasumber
                    if ($ev && !$isCertified && !$filledInstructorEvalEventIds->has($ev->id) && $meta['status_key'] !== 'open_registration') {
                        $inEvalWindow = ($ev->end_date && $now->gte($ev->end_date->copy()->startOfDay()))
                            || ($ev->assessment_start_at && $now->gte($ev->assessment_start_at))
                            || $progress >= 75;
                        if ($inEvalWindow) {
                            $narasumberDeadline = $ev->assessment_end_at ?? $ev->end_date;
                            $actionItems[] = [
                                'priority'    => 35,
                                'sort_ts'     => ($narasumberDeadline ?? $now)->timestamp,
                                'type'        => 'narasumber',
                                'icon'        => 'fas fa-comment-dots',
                                'badge'       => 'Evaluasi Narasumber',
                                'batch_name'  => $batchLabel,
                                'title'       => 'Evaluasi Narasumber',
                                'subtitle'    => $narasumberDeadline
                                    ? 'Batas ' . $narasumberDeadline->translatedFormat('d M Y')
                                    : 'Wajib diisi sebelum sertifikat terbit',
                                'action_text' => 'Isi',
                                'card_color'  => $courseCardColor,
                                'url'         => route('user.course.my-course.evaluasi-narasumber', $course->slug),
                            ];
                        }
                    }

                    // 5d. E-sertifikat Siap Diunduh (hanya saat tidak ada pelatihan aktif lain yang sedang berjalan)
                    if ($isCertified && $focusedCourseId === null) {
                        $actionItems[] = [
                            'priority'    => 50,
                            'sort_ts'     => 0,
                            'type'        => 'certificate',
                            'icon'        => 'fas fa-award',
                            'badge'       => 'E-sertifikat',
                            'batch_name'  => $batchLabel,
                            'title'       => 'E-sertifikat: ' . $course->name,
                            'subtitle'    => 'Siap diunduh',
                            'action_text' => 'Unduh',
                            'card_color'  => $courseCardColor,
                            'url'         => $courseUrl,
                        ];
                    }
                }

                $actionItemsCollection = collect($actionItems)
                    ->sort(function ($a, $b) {
                        if ($a['priority'] !== $b['priority']) {
                            return $a['priority'] <=> $b['priority'];
                        }
                        return $a['sort_ts'] <=> $b['sort_ts'];
                    })
                    ->take(8)
                    ->values();

                // Tentukan tahapan / agenda berikutnya untuk ditampilkan di card Lanjutkan Belajar
                if (!empty($featuredActiveCourse)) {
                    $firstStep = $actionItemsCollection->first();
                    if ($featuredActiveCourse['status_key'] === 'open_registration') {
                        $featuredActiveCourse['next_step_label'] = !empty($featuredActiveCourse['countdown_label'])
                            ? 'Menunggu pelaksanaan (' . strtolower($featuredActiveCourse['countdown_label']) . ')'
                            : 'Menunggu jadwal pembukaan kelas';
                    } elseif ($firstStep) {
                        $featuredActiveCourse['next_step_label'] = 'Tahap berikutnya: ' . $firstStep['title'];
                    } else {
                        $featuredActiveCourse['next_step_label'] = 'Tahap berikutnya: Pelajari materi modul aktif';
                    }
                }

                $actionSummary = [
                    'total'       => $actionItemsCollection->count(),
                    'zoom'        => $actionItemsCollection->where('type', 'zoom')->count(),
                    'evaluation'  => $actionItemsCollection->whereIn('type', ['evaluation', 'narasumber'])->count(),
                    'certificate' => $actionItemsCollection->where('type', 'certificate')->count(),
                ];

                // 6. Gelombang Pendaftaran Dibuka (rekomendasi dari Katalog yang belum diikuti user)
                $openCandidateEvents = $allYearEvents->filter(function ($ev) use ($resolveEventMeta, $enrollByEventId, $enrollByCourseId, $user) {
                    if (!$ev->course) {
                        return false;
                    }
                    if (!$user->canAccessCourseAudience($ev->course->audience)) {
                        return false;
                    }
                    $meta = $resolveEventMeta($ev);
                    if ($meta['status_key'] !== 'open_registration') {
                        return false;
                    }
                    if ($enrollByEventId->has($ev->id) || $enrollByCourseId->has($ev->course_id)) {
                        return false;
                    }
                    return true;
                });

                $enrolledCountsByEvent = $openCandidateEvents->isNotEmpty()
                    ? DB::table('course_student')
                        ->selectRaw('bimtek_event_id, COUNT(*) as total')
                        ->whereIn('bimtek_event_id', $openCandidateEvents->pluck('id'))
                        ->groupBy('bimtek_event_id')
                        ->pluck('total', 'bimtek_event_id')
                    : collect();

                $openRegistrationEvents = $openCandidateEvents
                    ->map(function ($ev) use ($enrolledCountsByEvent, $fmtRange, $now, $resolveCardColor) {
                        $quota = (int) ($ev->quota ?? 0);
                        $enrolled = (int) ($enrolledCountsByEvent->get($ev->id) ?? 0);
                        $isFull = $quota > 0 && $enrolled >= $quota;
                        if ($isFull) {
                            return null;
                        }
                        $remaining = $quota > 0 ? max(0, $quota - $enrolled) : null;
                        $quotaPct = $quota > 0 ? min(100, (int) round(($enrolled / $quota) * 100)) : null;

                        $regEnd = $ev->registration_end_at ?? $ev->start_date?->copy()->subDay();
                        $daysLeft = $regEnd ? (int) $now->copy()->startOfDay()->diffInDays($regEnd->copy()->startOfDay(), false) : null;
                        $regBadge = match (true) {
                            !is_null($daysLeft) && $daysLeft <= 0 => 'Tutup Hari Ini',
                            !is_null($daysLeft) && $daysLeft <= 5 => 'Sisa ' . $daysLeft . ' Hari',
                            $regEnd !== null                      => 'Tutup ' . $regEnd->translatedFormat('d M'),
                            default                               => 'Pendaftaran Buka',
                        };

                        return [
                            'id'              => $ev->id,
                            'batch_name'      => preg_replace('/\bbatch\b/i', 'Gelombang', (string) $ev->batch_name),
                            'course_name'     => $ev->course->name,
                            'card_color'      => $resolveCardColor($ev->course->thumbnail, $ev->course->id),
                            'reg_badge'       => $regBadge,
                            'is_urgent'       => !is_null($daysLeft) && $daysLeft <= 5,
                            'exec_label'      => $fmtRange($ev->start_date, $ev->end_date ?? $ev->start_date),
                            'enrolled_count'  => $enrolled,
                            'quota'           => $quota,
                            'remaining_quota' => $remaining,
                            'quota_pct'       => $quotaPct,
                            'sort_ts'         => ($regEnd ?? $ev->start_date ?? $now)->timestamp,
                            'url'             => route('user.catalog.index', ['search' => $ev->course->name]),
                        ];
                    })
                    ->filter()
                    ->sortBy('sort_ts')
                    ->take(8)
                    ->values();

                // 7. Last accessed library
                $lastAccessedLibrary = Library::select('libraries.*', 'user_library_history.last_accessed_at')
                    ->join('user_library_history', 'libraries.id', '=', 'user_library_history.library_id')
                    ->where('user_library_history.user_id', $user->id)
                    ->with('libraryCategory')
                    ->orderBy('user_library_history.last_accessed_at', 'desc')
                    ->first();

                if ($lastAccessedLibrary) {
                    if ($lastAccessedLibrary->video_path) {
                        $lastAccessedLibrary->type = 'video';
                        $lastAccessedLibrary->icon = 'fas fa-video';
                    } elseif ($lastAccessedLibrary->file_path) {
                        $lastAccessedLibrary->type = 'document';
                        $lastAccessedLibrary->icon = 'fas fa-file-pdf';
                    } elseif ($lastAccessedLibrary->external_link) {
                        $lastAccessedLibrary->type = 'link';
                        $lastAccessedLibrary->icon = 'fas fa-link';
                    } else {
                        $lastAccessedLibrary->type = 'other';
                        $lastAccessedLibrary->icon = 'fas fa-book-open';
                    }
                }

                return [
                    'profile'           => $profile,
                    'provinces'         => $provinces,
                    'stats'             => $stats,
                    'eventStats'        => $eventStats,
                    'lastCourse'        => $lastCourse,
                    'recentCourses'     => $recentCourses,
                    'chartDataByCourse' => $chartDataByCourse,
                    'userEventGantt'         => $userEventGantt,
                    'featuredActiveCourse'   => $featuredActiveCourse,
                    'nearestSession'         => $nearestSession,
                    'actionItems'            => $actionItemsCollection,
                    'actionSummary'          => $actionSummary,
                    'openRegistrationEvents' => $openRegistrationEvents,
                    'lastLibrary'            => $lastAccessedLibrary,
                ];
            }
        );
    }

    /**
     * Get user profile page data (SmartCached per-user + auto-invalidated).
     */
    public function getProfileData(User $user): array
    {
        $cached = SmartCache::remember(
            [
                SmartCache::DOMAIN_LMS_COURSES,
                SmartCache::DOMAIN_LMS_SUBMISSIONS,
                SmartCache::userDomain($user->id),
            ],
            "user_profile:{$user->id}",
            180,
            function () use ($user): array {
                $provinces = Province::all();

                $certificates = $user->enrolledCourses()
                    ->wherePivot('status', 'completed')
                    ->orderByDesc('course_student.certificate_issued_at')
                    ->get()
                    ->filter(fn ($course) => ! empty($course->pivot->certificate_code))
                    ->values();

                $stats = [
                    'enrolled' => $user->enrolledCourses()->count(),
                    'completed' => $user->enrolledCourses()->wherePivot('status', 'completed')->count(),
                    'certificates' => $certificates->count(),
                ];

                return [
                    'provinces' => $provinces,
                    'certificates' => $certificates,
                    'stats' => $stats,
                ];
            }
        );

        return array_merge(['user' => $user], $cached);
    }

    /**
     * Get regencies list for a province.
     */
    public function getRegenciesByProvinceCode(string $provinceCode): Collection
    {
        $province = Province::where('code', $provinceCode)->firstOrFail();
        return $province->regencies;
    }

    /**
     * Update user instansi / sektor via web onboarding atau form lengkap.
     */
    public function updateInstansiWeb(User $user, array $data): void
    {
        $profile = UserProfile::firstOrNew(['user_id' => $user->id]);
        $sector = $data['sector'] ?? 'instansi';
        $hasDetail = !empty($data['instansi']) || !empty($data['asalInstansi']);

        // Mode quick onboarding (cukup pilih Instansi/Lembaga atau Swasta di awal login)
        if (!$hasDetail) {
            $profile->institution_type = $sector === 'swasta'
                ? InstitutionType::SWASTA->value
                : InstitutionType::INSTANSI->value;
            $profile->save();

            SmartCache::bump([
                SmartCache::userDomain($user->id),
            ]);

            return;
        }

        if ($sector === 'swasta') {
            $profile->institution_type = InstitutionType::SWASTA->value;
            $profile->instansi = trim((string) $data['instansi']);
            $profile->unit_kerja = !empty($data['unit_kerja']) ? trim((string) $data['unit_kerja']) : null;
            $profile->save();

            UserScope::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'province_code' => $data['province_code'] ?? null,
                    'regency_code' => $data['regency_code'] ?? null,
                ]
            );

            SmartCache::bump([
                SmartCache::userDomain($user->id),
            ]);
            return;
        }

        $institutionType = match ($data['asalInstansi'] ?? '') {
            'pusat' => InstitutionType::PUSAT,
            'provinsi' => InstitutionType::PROVINSI,
            'kabkota', 'kab_kota' => InstitutionType::KAB_KOTA,
            default => InstitutionType::PUSAT,
        };

        $instansi = ($data['instansi'] ?? '') === 'lainnya'
            ? trim((string) ($data['instansi_lainnya'] ?? ''))
            : ($data['instansi'] ?? '');

        if (($data['instansi'] ?? '') === 'lainnya' && $instansi !== '') {
            Institution::firstOrCreate(
                [
                    'name' => $instansi,
                    'type' => ($data['asalInstansi'] ?? '') === 'pusat' ? 'pusat' : 'daerah',
                    'province_code' => $data['province_code'] ?? null,
                    'regency_code' => ($data['asalInstansi'] ?? '') === 'provinsi' ? null : ($data['regency_code'] ?? null),
                ],
                [
                    'is_active' => true,
                ]
            );
        }

        $profile->institution_type = $institutionType->value;
        $profile->instansi = $instansi;
        $profile->unit_kerja = $data['unit_kerja'] ?? null;
        $profile->save();

        UserScope::updateOrCreate(
            ['user_id' => $user->id],
            [
                'province_code' => $data['province_code'] ?? null,
                'regency_code' => $data['regency_code'] ?? null,
            ]
        );

        SmartCache::bump([
            SmartCache::userDomain($user->id),
        ]);
    }

    /**
     * Search suggestions for user global search bar.
     */
    public function getSearchSuggestions(User $user, ?string $keyword): array
    {
        if (empty($keyword)) {
            return [
                'enrolled_courses' => [],
                'available_catalogs' => [],
                'catalog_categories' => [],
                'libraries' => [],
            ];
        }

        $getInitials = function ($name) {
            $words = explode(' ', trim($name));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $w) {
                if (!empty($w)) {
                    $initials .= strtoupper(substr($w, 0, 1));
                }
            }
            return strlen($initials) < 1 ? 'S' : (strlen($initials) > 2 ? substr($initials, 0, 2) : $initials);
        };

        $enrolledCourseIds = $user->enrolledCourses()->pluck('course_id')->toArray();

        $enrolledCourses = Course::whereIn('id', $enrolledCourseIds)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->limit(4)
            ->get()
            ->map(function ($item) use ($getInitials) {
                return [
                    'title' => $item->name,
                    'subtitle' => 'Kursus Saya',
                    'url' => route('user.course.my-course.detail', $item->slug),
                    'initials' => $getInitials($item->name),
                    'cover' => $item->thumbnail
                        ? (str_starts_with($item->thumbnail, 'http') ? $item->thumbnail : asset('storage/' . $item->thumbnail))
                        : null,
                    'color' => 'bg-[#184A78]',
                ];
            });

        $availableCatalogs = Course::whereNotIn('id', $enrolledCourseIds)
            ->when($user->isSwastaUser(), fn ($q) => $q->where('audience', 'swasta'))
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->limit(4)
            ->get()
            ->map(function ($item) use ($getInitials) {
                return [
                    'title' => $item->name,
                    'subtitle' => 'Katalog Pelatihan',
                    'url' => route('user.course.index', $item->slug),
                    'initials' => $getInitials($item->name),
                    'cover' => $item->thumbnail
                        ? (str_starts_with($item->thumbnail, 'http') ? $item->thumbnail : asset('storage/' . $item->thumbnail))
                        : null,
                    'color' => 'bg-emerald-600',
                ];
            });

        $catalogCategories = [];

        $libraries = Library::where('title', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%")
            ->with('libraryCategory')
            ->limit(3)
            ->get()
            ->map(function ($item) use ($getInitials) {
                return [
                    'title' => $item->title,
                    'subtitle' => $item->libraryCategory->name ?? 'Dokumen Perpustakaan',
                    'url' => route('user.library.index') . '?search=' . urlencode($item->title),
                    'initials' => $getInitials($item->title),
                    'cover' => $item->cover_image_url,
                    'color' => 'bg-amber-600',
                ];
            });

        return [
            'enrolled_courses' => $enrolledCourses,
            'available_catalogs' => $availableCatalogs,
            'catalog_categories' => $catalogCategories,
            'libraries' => $libraries,
        ];
    }
}
