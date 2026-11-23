<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use App\Support\SmartCache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Category;
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
                    $dbLastCourse = Course::with('category')->where('slug', $lastCourse->slug)->first();
                    if ($dbLastCourse) {
                        $lastCourse->description = $dbLastCourse->description;
                        $lastCourse->category = $dbLastCourse->category;
                        $lastCourse->category_name = $dbLastCourse->category ? $dbLastCourse->category->name : null;
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
                    ->with('category')
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

                // 4. Gantt Chart Jadwal Event untuk User (tahun berjalan + event yang diikuti)
                $ganttEvents = [];
                foreach ($allYearEvents as $ev) {
                    $course = $ev->course;
                    if (!$course) {
                        continue;
                    }

                    $enr = $enrollByEventId->get($ev->id) ?? $enrollByCourseId->get($ev->course_id);
                    $isEnrolled = $enr !== null && ($enr->bimtek_event_id === $ev->id || $enr->bimtek_event_id === null);
                    $meta = $resolveEventMeta($ev);

                    $regStart  = $ev->registration_start_at ?? $ev->start_date?->copy()->subDays(7);
                    $regEnd    = $ev->registration_end_at ?? $ev->start_date?->copy()->subDay();
                    $execStart = $ev->start_date;
                    $execEnd   = $ev->end_date ?? $ev->start_date;
                    $evalStart = $ev->assessment_start_at ?? $execEnd;
                    $evalEnd   = $ev->assessment_end_at ?? $execEnd?->copy()->addDays(3);

                    if ($regStart && $regEnd && $regEnd->lt($regStart)) $regEnd = $regStart->copy();
                    if ($execStart && $execEnd && $execEnd->lt($execStart)) $execEnd = $execStart->copy();
                    if ($evalStart && $evalEnd && $evalEnd->lt($evalStart)) $evalEnd = $evalStart->copy();

                    $ganttEvents[] = [
                        'id'              => $ev->id,
                        'batch_name'      => $ev->batch_name,
                        'course_name'     => $course->name,
                        'status_key'      => $meta['status_key'],
                        'status_label'    => $meta['status_label'],
                        'is_enrolled'     => $isEnrolled,
                        'user_progress'   => $isEnrolled ? (int) ($enr->progress ?? 0) : 0,
                        'user_completed'  => $isEnrolled && ($enr->status === 'completed' || (int) ($enr->progress ?? 0) >= 100),
                        'is_certified'    => $isEnrolled && (!empty($enr->official_cert_number) || !empty($enr->certificate_file)),
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
                        'url'             => $isEnrolled
                            ? route('user.course.my-course.detail', $course->slug)
                            : route('user.catalog.index', ['search' => $course->name]),
                    ];
                }

                $ganttEvents = collect($ganttEvents)->sortBy('sort_date')->values()->all();
                $hasEnrolledInGantt = collect($ganttEvents)->contains('is_enrolled', true);

                $userEventGantt = [
                    'year'         => $currentYear,
                    'today'        => $now->format('Y-m-d'),
                    'today_label'  => $now->translatedFormat('d M Y'),
                    'has_enrolled' => $hasEnrolledInGantt,
                    'events'       => $ganttEvents,
                ];

                // 5. Agenda & Tindak Lanjut Terdekat (Action Center untuk LMS by Event)
                $actionItems = [];
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

                $enrolledCoursesById = $myCourses->keyBy('id');

                foreach ($userEnrollments as $enr) {
                    $course = $enrolledCoursesById->get($enr->course_id);
                    if (!$course) {
                        continue;
                    }
                    $ev = $enr->bimtek_event_id ? $eventsById->get($enr->bimtek_event_id) : null;
                    $meta = $resolveEventMeta($ev);
                    $batchLabel = $meta['batch_name']
                        ? preg_replace('/\bbatch\b/i', 'Gelombang', (string) $meta['batch_name'])
                        : null;
                    $progress = (int) ($enr->progress ?? 0);
                    $isDone = ($enr->status === 'completed') || $progress >= 100;
                    $isCertified = !empty($enr->official_cert_number) || !empty($enr->certificate_file);
                    $courseUrl = route('user.course.my-course.detail', $course->slug);

                    // 5a. Sesi Live Zoom Terdekat / Hari Ini
                    if ($ev && !$isDone) {
                        $nextSess = null;
                        if ($ev->relationLoaded('sessions') && $ev->sessions->isNotEmpty()) {
                            $nextSess = $ev->sessions
                                ->filter(function ($s) use ($now, $attendedSessionIds) {
                                    $endRef = $s->end_time ?? $s->start_time;
                                    return $endRef && $endRef->gte($now) && !$attendedSessionIds->has($s->id);
                                })
                                ->sortBy('start_time')
                                ->first();
                        }
                        if ($nextSess && $nextSess->start_time) {
                            $isToday = $nextSess->start_time->isToday();
                            $timeLabel = $nextSess->start_time->translatedFormat('d M Y, H:i')
                                . ($nextSess->end_time ? '–' . $nextSess->end_time->format('H:i') : '') . ' WIB';
                            $actionItems[] = [
                                'priority'    => $isToday ? 10 : 20,
                                'sort_ts'     => $nextSess->start_time->timestamp,
                                'type'        => 'zoom',
                                'icon'        => 'fas fa-video',
                                'badge'       => $isToday ? 'Zoom Hari Ini' : 'Live Zoom',
                                'batch_name'  => $batchLabel,
                                'title'       => $course->name,
                                'subtitle'    => ($isToday ? 'Zoom Hari Ini — ' : 'Live Zoom — ') . $timeLabel,
                                'action_text' => 'Masuk',
                                'url'         => $courseUrl,
                            ];
                        } elseif ($ev->zoom_start_at && $ev->zoom_start_at->gte($now)) {
                            $isToday = $ev->zoom_start_at->isToday();
                            $actionItems[] = [
                                'priority'    => $isToday ? 10 : 20,
                                'sort_ts'     => $ev->zoom_start_at->timestamp,
                                'type'        => 'zoom',
                                'icon'        => 'fas fa-video',
                                'badge'       => $isToday ? 'Zoom Hari Ini' : 'Live Zoom',
                                'batch_name'  => $batchLabel,
                                'title'       => $course->name,
                                'subtitle'    => ($isToday ? 'Zoom Hari Ini — ' : 'Live Zoom — ') . $ev->zoom_start_at->translatedFormat('d M Y, H:i') . ' WIB',
                                'action_text' => 'Masuk',
                                'url'         => $courseUrl,
                            ];
                        }
                    }

                    // 5b. Evaluasi yang Perlu Diselesaikan (untuk gelombang sedang berlangsung / evaluasi)
                    if (!$isDone && $meta['status_key'] !== 'open_registration' && isset($allPostTests)) {
                        $courseTests = $allPostTests->where('course_id', $course->id);
                        $pendingTest = $courseTests->first(function ($test) use ($submittedPostTestMap) {
                            $res = $submittedPostTestMap->get($test->id);
                            if (!$res) {
                                return true;
                            }
                            if ((int) ($res->pending_count ?? 0) > 0 && is_null($res->best_graded_score)) {
                                return false; // sudah dikumpulkan, menunggu penilaian instruktur
                            }
                            if (is_null($res->best_graded_score)) {
                                return true;
                            }
                            return (float) $res->best_graded_score < (float) $test->effectivePassingGrade();
                        });

                        if ($pendingTest) {
                            $shortEvalLabel = $pendingTest->course_section_id
                                ? (preg_match('/(?:modul|hari)\s*(\d+)/i', (string) $pendingTest->title, $m) ? 'Evaluasi Hari ' . $m[1] : 'Evaluasi Harian')
                                : 'Evaluasi Akhir';
                            $deadlineRef = $ev ? ($ev->assessment_end_at ?? $ev->end_date) : null;
                            $subInfo = $deadlineRef
                                ? $shortEvalLabel . ' — Batas ' . $deadlineRef->translatedFormat('d M Y')
                                : $shortEvalLabel;

                            $actionItems[] = [
                                'priority'    => 30,
                                'sort_ts'     => $deadlineRef ? $deadlineRef->timestamp : PHP_INT_MAX,
                                'type'        => 'evaluation',
                                'icon'        => 'fas fa-clipboard-check',
                                'badge'       => 'Evaluasi',
                                'batch_name'  => $batchLabel,
                                'title'       => $course->name,
                                'subtitle'    => $subInfo,
                                'action_text' => 'Kerjakan',
                                'url'         => $courseUrl,
                            ];
                        }
                    }

                    // 5c. Kuesioner Evaluasi Narasumber (jika sudah memasuki akhir pelaksanaan / evaluasi & belum diisi)
                    if ($ev && !$isCertified && !$filledInstructorEvalEventIds->has($ev->id) && $meta['status_key'] !== 'open_registration') {
                        $inEvalWindow = ($ev->end_date && $now->gte($ev->end_date->copy()->startOfDay()))
                            || ($ev->assessment_start_at && $now->gte($ev->assessment_start_at))
                            || $progress >= 75;
                        if ($inEvalWindow) {
                            $actionItems[] = [
                                'priority'    => 35,
                                'sort_ts'     => ($ev->assessment_end_at ?? $ev->end_date ?? $now)->timestamp,
                                'type'        => 'narasumber',
                                'icon'        => 'fas fa-comment-dots',
                                'badge'       => 'Kuesioner',
                                'batch_name'  => $batchLabel,
                                'title'       => $course->name,
                                'subtitle'    => 'Evaluasi narasumber wajib diisi',
                                'action_text' => 'Isi',
                                'url'         => route('user.course.my-course.evaluasi-narasumber', $course->slug),
                            ];
                        }
                    }

                    // 5d. e-Sertifikat Siap Diunduh
                    if ($isCertified) {
                        $actionItems[] = [
                            'priority'    => 50,
                            'sort_ts'     => 0,
                            'type'        => 'certificate',
                            'icon'        => 'fas fa-award',
                            'badge'       => 'Sertifikat',
                            'batch_name'  => $batchLabel,
                            'title'       => $course->name,
                            'subtitle'    => 'e-Sertifikat siap diunduh',
                            'action_text' => 'Unduh',
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

                $actionSummary = [
                    'total'       => $actionItemsCollection->count(),
                    'zoom'        => $actionItemsCollection->where('type', 'zoom')->count(),
                    'evaluation'  => $actionItemsCollection->whereIn('type', ['evaluation', 'narasumber'])->count(),
                    'certificate' => $actionItemsCollection->where('type', 'certificate')->count(),
                ];

                // 6. Gelombang Pendaftaran Dibuka (rekomendasi dari Katalog yang belum diikuti user)
                $openCandidateEvents = $allYearEvents->filter(function ($ev) use ($resolveEventMeta, $enrollByEventId, $enrollByCourseId) {
                    if (!$ev->course) {
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
                    ->map(function ($ev) use ($enrolledCountsByEvent, $fmtRange, $now) {
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
                    'userEventGantt'    => $userEventGantt,
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
     * Update user instansi via web form.
     */
    public function updateInstansiWeb(User $user, array $data): void
    {
        $profile = UserProfile::firstOrNew(['user_id' => $user->id]);

        if (($data['sector'] ?? '') === 'swasta') {
            $profile->institution_type = InstitutionType::SWASTA->value;
            $profile->instansi = trim($data['instansi']);
            $profile->unit_kerja = !empty($data['unit_kerja']) ? trim($data['unit_kerja']) : null;
            $profile->save();

            UserScope::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'province_code' => $data['province_code'] ?? null,
                    'regency_code' => $data['regency_code'] ?? null,
                ]
            );
            return;
        }

        $institutionType = match ($data['asalInstansi'] ?? '') {
            'pusat' => InstitutionType::PUSAT,
            'provinsi' => InstitutionType::PROVINSI,
            'kabkota' => InstitutionType::KAB_KOTA,
            default => InstitutionType::PUSAT,
        };

        $instansi = ($data['instansi'] ?? '') === 'lainnya'
            ? trim($data['instansi_lainnya'] ?? '')
            : ($data['instansi'] ?? '');

        if (($data['instansi'] ?? '') === 'lainnya') {
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
        $profile->unit_kerja = $data['unit_kerja'];
        $profile->save();

        UserScope::updateOrCreate(
            ['user_id' => $user->id],
            [
                'province_code' => $data['province_code'] ?? null,
                'regency_code' => $data['regency_code'] ?? null,
            ]
        );
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
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            })
            ->with('category')
            ->limit(4)
            ->get()
            ->map(function ($item) use ($getInitials) {
                return [
                    'title' => $item->name,
                    'subtitle' => $item->category->name ?? 'Kategori Umum',
                    'url' => route('user.course.my-course.detail', $item->slug),
                    'initials' => $getInitials($item->name),
                    'cover' => $item->thumbnail
                        ? (str_starts_with($item->thumbnail, 'http') ? $item->thumbnail : asset('storage/' . $item->thumbnail))
                        : null,
                    'color' => 'bg-[#184A78]',
                ];
            });

        $availableCatalogs = Course::whereNotIn('id', $enrolledCourseIds)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            })
            ->with('category')
            ->limit(4)
            ->get()
            ->map(function ($item) use ($getInitials) {
                return [
                    'title' => $item->name,
                    'subtitle' => 'Katalog: ' . ($item->category->name ?? 'Kategori Umum'),
                    'url' => route('user.course.index', $item->slug),
                    'initials' => $getInitials($item->name),
                    'cover' => $item->thumbnail
                        ? (str_starts_with($item->thumbnail, 'http') ? $item->thumbnail : asset('storage/' . $item->thumbnail))
                        : null,
                    'color' => 'bg-emerald-600',
                ];
            });

        // Pencarian Kategori Katalog Kursus
        $catalogCategories = Category::where('name', 'like', "%{$keyword}%")
            ->withCount('courses')
            ->limit(3)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'subtitle' => ($item->courses_count ?? 0) . ' Kursus dalam Kategori',
                    'url' => route('user.catalog.index', ['categories' => [$item->id]]),
                ];
            });

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
