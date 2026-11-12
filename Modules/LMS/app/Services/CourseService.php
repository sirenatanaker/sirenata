<?php

namespace Modules\LMS\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\BimtekEvent;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Models\SectionZoomProof;
use Modules\LMS\Models\StudentContentProgress;
use Modules\User\Enums\InstitutionType;

class CourseService
{
    private const DEFAULT_SORT = 'desc';
    private const DEFAULT_LIMIT = 10;

    public function getCoursesForFilter()
    {
        return Course::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function queryUsersWithEnrollmentsByProvince(
        string $provinceCode,
        string $search = '',
        ?string $courseId = null,
    ) {
        return User::query()
            ->inProvince($provinceCode)
            ->provinceInstitution()
            ->whereHas('enrolledCourses')
            ->when($courseId, function ($q) use ($courseId) {
                $q->whereHas('enrolledCourses', function ($sub) use ($courseId) {
                    $sub->where('courses.id', $courseId);
                });
            })
            ->when($search, fn($q) => $q->search($search))
            ->with([
                'profile',
                'scopeArea',
                'enrolledCourses' => function ($q) use ($courseId) {
                    if ($courseId) {
                        $q->where('courses.id', $courseId);
                    }
                }
            ]);
    }

    public function paginateUsersWithEnrollmentsByProvince(string $provinceCode, string $search = '', int $limit = self::DEFAULT_LIMIT, ?string $courseId = null)
    {
        return $this->queryUsersWithEnrollmentsByProvince($provinceCode, $search, $courseId)->paginate($limit)->withQueryString();
    }

    private function baseEnrollmentsByProvinceQuery(
        string $provinceCode,
        ?string $courseId = null,
        ?string $search = null,
    ) {
        return DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->join('courses', 'courses.id', '=', 'course_student.course_id')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->join('user_scopes', 'user_scopes.user_id', '=', 'users.id')
            ->where('user_scopes.province_code', $provinceCode)
            ->where('user_profiles.institution_type', InstitutionType::PROVINSI)
            ->when($courseId, function ($q) use ($courseId) {
                $q->where('courses.id', $courseId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('user_profiles.instansi', 'like', "%{$search}%");
                });
            })
            ->select([
                'users.id',
                'users.name as user_name',
                'courses.name as course_name',
                'user_profiles.instansi',
                'user_profiles.full_name as user_full_name',
                'course_student.status',
                'course_student.progress'
            ]);
    }

    public function paginateCourseEnrollmentsByProvince(string $provinceCode, ?string $courseId = null, ?string $search = null, int $limit = 10)
    {
        return $this->baseEnrollmentsByProvinceQuery($provinceCode, $courseId, $search)->paginate($limit)->withQueryString();
    }

    public function exportCourseEnrollmentsByProvince(string $provinceCode, ?string $courseId = null, ?string $search = null)
    {
        return $this->baseEnrollmentsByProvinceQuery($provinceCode, $courseId, $search)->orderBy('users.name');
    }

    public function queryCourseByRegency(string $regencyCode, string $search = '', int $limit = self::DEFAULT_LIMIT, string $sort = self::DEFAULT_SORT)
    {
        return User::query()
            ->inRegency($regencyCode)
            ->regencyInstitution()
            ->where(function ($q) use ($search) {
                $q->hasEnrolledCourses($search);
                if ($search) {
                    $q->orWhere(fn($q2) => $q2->hasEnrolledCourses()->search($search));
                }
            })
            ->with([
                'profile',
                'scopeArea',
                'enrolledCourses' => fn($q) => $search ? $q->where('name', 'like', "%{$search}%") : $q,
            ]);
    }

    public function paginatedCourseByRegency(string $regencyCode, string $search = '', int $limit = self::DEFAULT_LIMIT, string $sort = self::DEFAULT_SORT)
    {
        return $this->queryCourseByRegency($regencyCode, $search, $limit, $sort)->paginate($limit)->withQueryString();
    }

    private function baseEnrollmentsByRegencyQuery(string $regencyCode, ?string $courseId = null, ?string $search = null)
    {
        return DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->join('courses', 'courses.id', '=', 'course_student.course_id')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->join('user_scopes', 'user_scopes.user_id', '=', 'users.id')
            ->where('user_scopes.regency_code', $regencyCode)
            ->where('user_profiles.institution_type', InstitutionType::KAB_KOTA)
            ->when($courseId, function ($q) use ($courseId) {
                $q->where('courses.id', $courseId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('user_profiles.instansi', 'like', "%{$search}%");
                });
            })
            ->select([
                'users.name as user_name',
                'courses.name as course_name',
                'user_profiles.instansi',
                'course_student.status',
                'course_student.progress',
            ]);
    }

    public function paginateCourseEnrollmentsByRegency(string $regencyCode, ?string $courseId = null, ?string $search = null, int $limit = 10)
    {
        return $this->baseEnrollmentsByRegencyQuery($regencyCode, $courseId, $search)->paginate($limit)->withQueryString();
    }

    private function baseEnrollmentsByInstansiQuery(
        string $instansi,
        ?string $courseId = null,
        ?string $search = null,
    ) {
        return DB::table('course_student')
            ->join('users', 'users.id', '=', 'course_student.user_id')
            ->join('courses', 'courses.id', '=', 'course_student.course_id')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->where('user_profiles.institution_type', InstitutionType::PUSAT)
            ->where('user_profiles.instansi', $instansi)
            ->when($courseId, function ($q) use ($courseId) {
                $q->where('courses.id', $courseId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('user_profiles.instansi', 'like', "%{$search}%");
                });
            })
            ->select([
                'users.id',
                'users.name as user_name',
                'courses.name as course_name',
                'user_profiles.instansi',
                'user_profiles.full_name as user_full_name',
                'course_student.status',
                'course_student.progress',
            ]);
    }

    public function paginateCourseEnrollmentsByInstansi(string $instansi, ?string $courseId = null, ?string $search = null, int $limit = 10)
    {
        return $this->baseEnrollmentsByInstansiQuery($instansi, $courseId, $search)->paginate($limit)->withQueryString();
    }

    public function exportCourseEnrollmentsByInstansi(string $instansi, ?string $courseId = null, ?string $search = null)
    {
        return $this->baseEnrollmentsByInstansiQuery($instansi, $courseId, $search)->orderBy('users.name');
    }

    public function exportCourseEnrollmentsByRegency(string $regencyCode, ?string $courseId = null, ?string $search = null)
    {
        return $this->baseEnrollmentsByRegencyQuery($regencyCode, $courseId, $search)->orderBy('users.name');
    }

    public function myCourseStats(): array
    {
        /** @var User $user */
        $user    = Auth::user();
        $courses = $user->enrolledCourses()->get();
        $startedCourses = $courses->where('pivot.progress', '>', 0);

        return [
            'total'        => $courses->count(),
            'aktif'        => $courses->where('pivot.status', 'in_progress')->count(),
            'terdaftar'    => $courses->where('pivot.status', 'enrolled')->count(),
            'selesai'      => $courses->where('pivot.status', 'completed')->count(),
            'sertifikat'   => $courses->whereNotNull('pivot.certificate_code')->count(),
            'avg_progress' => $startedCourses->count() > 0 ? (int) round($startedCourses->avg('pivot.progress')) : 0,
        ];
    }

    public function getLastAccessedCourse(): ?object
    {
        /** @var User $user */
        $user = Auth::user();

        $course = $user->enrolledCourses()
            ->wherePivotIn('status', ['enrolled', 'in_progress'])
            ->orderByPivot('updated_at', 'desc')
            ->first();

        if (! $course) return null;

        return (object) [
            'name'     => $course->name,
            'slug'     => $course->slug,
            'progress' => $course->pivot->progress,
            'status'   => $course->pivot->status,
        ];
    }

    public function getRecentCourses(int $limit = 3): \Illuminate\Support\Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->enrolledCourses()
            ->with('category')
            ->orderByPivot('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Helper untuk kalkulasi progress dinamis secara real-time
     */
    public function calculateDynamicProgress(Course $dbCourse, int|string|null $userId = null): int
    {
        $userId = $userId ?? Auth::id();
        $totalContents = 0;
        $completedContents = 0;
        $totalTests = 0;
        $passedTests = 0;

        foreach ($dbCourse->sections as $section) {
            $totalContents += $section->contents->count();

            $completedContents += StudentContentProgress::where('user_id', $userId)
                ->whereIn('section_content_id', $section->contents->pluck('id'))
                ->count();

            $postTestBab = PostTest::where('course_section_id', $section->id)->first();
            if ($postTestBab) {
                $totalTests++;
                $isPassed = DB::table('post_test_results')
                    ->where('user_id', $userId)
                    ->where('post_test_id', $postTestBab->id)
                    ->where('is_passed', 1)
                    ->exists();
                if ($isPassed) {
                    $passedTests++;
                }
            }
        }

        $evaluasiAkhir = PostTest::where('course_id', $dbCourse->id)->whereNull('course_section_id')->first();
        if ($evaluasiAkhir) {
            $totalTests++;
            $isEvaluasiAkhirCompleted = DB::table('post_test_results')
                ->where('user_id', $userId)
                ->where('post_test_id', $evaluasiAkhir->id)
                ->where('is_passed', 1)
                ->exists();
            if ($isEvaluasiAkhirCompleted) {
                $passedTests++;
            }
        }

        // Hitung Sesi Live Zoom Bimtek ke dalam Progress jika program Bimtek memiliki tautan Zoom
        $activeBimtek = BimtekEvent::where('course_id', $dbCourse->id)->latest()->first();
        $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
        $totalZoom = $hasZoom ? 1 : 0;
        $passedZoom = 0;
        if ($hasZoom) {
            $isZoomAttended = DB::table('course_student')
                ->where('course_id', $dbCourse->id)
                ->where('user_id', $userId)
                ->value('zoom_attended');
            if ($isZoomAttended) {
                $passedZoom = 1;
            }
        }

        $totalItems = $totalContents + $totalTests + $totalZoom;
        $completedItems = $completedContents + $passedTests + $passedZoom;

        return $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;
    }

    /**
     * Ambil course yang diikuti user lengkap dengan progress dinamis & metadata kartu,
     * difilter berdasarkan tab: 'all', 'upcoming', 'progress', 'finish'
     */
    public function myCourses(int $page = 1, int $perPage = 12, ?string $status = null, ?string $tab = null): array
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak terotentikasi',
                    'data'    => collect(),
                    'meta'    => [],
                    'links'   => [],
                ];
            }

            $activeTab = $tab ?? $status ?? 'all';
            if ($activeTab === 'in_progress') {
                $activeTab = 'progress';
            } elseif ($activeTab === 'completed') {
                $activeTab = 'finish';
            }

            $enrolledCourses = $user->enrolledCourses()
                ->with(['category', 'sections.contents'])
                ->get();

            $allCoursesData = collect();

            foreach ($enrolledCourses as $course) {
                $progress = $this->calculateDynamicProgress($course, $user->id);

                // Ambil data event bimtek terkait jika ada
                $bimtekEvent = null;
                if (!empty($course->pivot->bimtek_event_id)) {
                    $bimtekEvent = BimtekEvent::find($course->pivot->bimtek_event_id);
                }
                if (!$bimtekEvent && $course->bimtekEvents()->exists()) {
                    $bimtekEvent = $course->bimtekEvents()->latest()->first();
                }

                // Penentuan Tab Status:
                // 1. finish: progress >= 100 atau status pivot 'completed'
                // 2. progress: belum selesai (kursus sedang berjalan / aktif)
                if ($progress >= 100 || $course->pivot->status === 'completed') {
                    $tabStatus = 'finish';
                    $computedStatus = 'completed';
                } else {
                    $tabStatus = 'progress';
                    $computedStatus = $progress > 0 ? 'in_progress' : 'enrolled';
                }

                $thumbnailUrl = $course->thumbnail
                    ? (str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : Storage::url($course->thumbnail))
                    : null;

                $allContentIds = $course->sections->flatMap->contents->pluck('id');
                $completedMateriCount = StudentContentProgress::where('user_id', $user->id)
                    ->whereIn('section_content_id', $allContentIds)
                    ->count();

                // Penentuan Periode & Jadwal Zoom
                $periodeLabel = null;
                $periodeDates = null;
                $zoomScheduleText = null;
                $isRegistrationPhase = false;
                $isOngoingPhase = false;
                $isWaitingForStart = false;

                if ($bimtekEvent) {
                    $curPhase = $bimtekEvent->current_timeline_phase ?? [];
                    $phaseKey = $curPhase['phase'] ?? 'learning';

                    $isRegistrationPhase = ($phaseKey === 'registration' || $bimtekEvent->status === 'open_registration');
                    $isOngoingPhase = ($phaseKey === 'learning' || $bimtekEvent->status === 'ongoing');

                    if ($tabStatus !== 'finish') {
                        $isWaitingForStart = ($isRegistrationPhase || ($bimtekEvent->start_date && now()->lessThan($bimtekEvent->start_date)));
                    }

                    if ($phaseKey === 'registration') {
                        $periodeLabel = 'Periode Pendaftaran';
                        $regStart = $bimtekEvent->registration_start_at ?? $bimtekEvent->start_date?->copy()->subDays(7);
                        $regEnd   = $bimtekEvent->registration_end_at ?? $bimtekEvent->start_date?->copy()->subDay();
                        $periodeDates = ($regStart ? $regStart->translatedFormat('d M') : '-') . ' - ' . ($regEnd ? $regEnd->translatedFormat('d M Y') : '-');
                    } elseif ($phaseKey === 'assessment') {
                        $periodeLabel = 'Periode Evaluasi';
                        $evalStart = $bimtekEvent->assessment_start_at ?? $bimtekEvent->end_date;
                        $evalEnd   = $bimtekEvent->assessment_end_at ?? $bimtekEvent->end_date?->copy()->addDays(3);
                        $periodeDates = ($evalStart ? $evalStart->translatedFormat('d M') : '-') . ' - ' . ($evalEnd ? $evalEnd->translatedFormat('d M Y') : '-');
                    } else {
                        // learning, ongoing, atau completed
                        $periodeLabel = 'Periode Pelaksanaan';
                        $execStart = $bimtekEvent->start_date;
                        $execEnd   = $bimtekEvent->end_date;
                        $periodeDates = ($execStart ? $execStart->translatedFormat('d M') : '-') . ($execEnd ? ' - ' . $execEnd->translatedFormat('d M Y') : '');
                    }

                    // Tampilkan Jadwal Zoom jika status Bimtek sedang berlangsung
                    if ($isOngoingPhase) {
                        if ($bimtekEvent->zoom_scheduled_at) {
                            $startStr = $bimtekEvent->zoom_scheduled_at->translatedFormat('d M Y, H:i');
                            $endStr   = $bimtekEvent->zoom_end_at ? $bimtekEvent->zoom_end_at->translatedFormat('H:i') . ' WIB' : 'WIB';
                            $zoomScheduleText = $startStr . ' - ' . $endStr;
                        } elseif ($bimtekEvent->zoom_url) {
                            $zoomScheduleText = 'Sesi Live Zoom Terjadwal';
                        }
                    }
                } else {
                    $periodeLabel = 'Akses';
                    $periodeDates = 'Fleksibel (Kapan Saja)';
                }

                $bimtekDates = $periodeDates;

                $batchStudentsCount = $bimtekEvent
                    ? DB::table('course_student')->where('bimtek_event_id', $bimtekEvent->id)->count()
                    : (method_exists($course, 'students') ? $course->students()->count() : 0);

                $allCoursesData->push((object) [
                    'id'                    => $course->id,
                    'slug'                  => $course->slug,
                    'name'                  => $course->name,
                    'course_name'           => $course->name,
                    'has_bimtek'            => (bool) $bimtekEvent || $course->bimtekEvents()->exists(),
                    'bimtek_event'          => $bimtekEvent,
                    'bimtek_dates'          => $bimtekDates,
                    'periode_label'         => $periodeLabel,
                    'periode_dates'         => $periodeDates,
                    'zoom_schedule_text'    => $zoomScheduleText,
                    'is_registration_phase' => $isRegistrationPhase,
                    'is_ongoing_phase'      => $isOngoingPhase,
                    'is_waiting_for_start'  => $isWaitingForStart,
                    'batch_students_count'  => $batchStudentsCount,
                    'category'              => $course->category ? (object) $course->category->toArray() : (object) ['name' => '-'],
                    'thumbnail'             => $course->thumbnail,
                    'thumbnail_url'         => $thumbnailUrl,
                    'description'           => $course->description,
                    'students_count'        => $batchStudentsCount,
                    'total_modul'           => $course->sections->count(),
                    'total_materi'          => $course->sections->sum(fn($s) => $s->contents->count()),
                    'completed_materi'      => $completedMateriCount,
                    'progress'              => $progress,
                    'status'             => $computedStatus,
                    'tab_status'         => $tabStatus,
                ]);
            }

            // Filter berdasarkan activeTab
            $filtered = $allCoursesData;
            if ($activeTab && $activeTab !== 'all') {
                $filtered = $allCoursesData->where('tab_status', $activeTab)->values();
            }

            $total = $filtered->count();
            $sliced = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

            $paginator = new LengthAwarePaginator(
                $sliced,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return [
                'success' => true,
                'message' => 'Success',
                'data'    => collect($paginator->items()),
                'meta'    => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'from'         => $paginator->firstItem() ?? 0,
                    'to'           => $paginator->lastItem() ?? 0,
                    'total'        => $paginator->total(),
                ],
                'links'   => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'auth'    => [],
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::myCourses error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'data'    => collect(),
                'meta'    => [],
                'links'   => [],
            ];
        }
    }

    /**
     * Ambil detail course siswa beserta relasi sections, contents, post-test, sertifikat, dan status kuncian
     */
    public function getCourseDetailSlug(string $slug): array
    {
        try {
            $user = Auth::user();
            $course = Course::with([
                'category',
                'sections.contents' => fn($q) => $q->orderBy('position'),
            ])->where('slug', $slug)->first();

            if (!$course) {
                return [
                    'success' => false,
                    'message' => 'Course tidak ditemukan',
                    'data'    => null,
                ];
            }

            $completedIds = StudentContentProgress::where('user_id', $user?->id)
                ->whereHas('content.section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->pluck('section_content_id')
                ->toArray();

            $passedTestIds = DB::table('post_test_results')
                ->where('user_id', $user?->id)
                ->where('is_passed', 1)
                ->pluck('post_test_id')
                ->toArray();

            $totalCompletedCount = count($completedIds);

            $course->course_name = $course->name;
            $course->thumbnail_url = $course->thumbnail ? Storage::url($course->thumbnail) : null;
            $course->completed_count = $totalCompletedCount;

            $enrollment = $user ? $course->students()->wherePivot('user_id', $user->id)->first() : null;
            if ($enrollment) {
                $course->certificate_code = $enrollment->pivot->certificate_code;
                $course->certificate_issued_at = $enrollment->pivot->certificate_issued_at;
                $course->certificate_file = $enrollment->pivot->certificate_file
                    ? Storage::url($enrollment->pivot->certificate_file)
                    : null;
                $course->official_cert_number = $enrollment->pivot->official_cert_number;
                $course->cert_submission_status = $enrollment->pivot->cert_submission_status;
                $course->zoom_attended = $enrollment->pivot->zoom_attended;
                $course->zoom_joined_at = $enrollment->pivot->zoom_joined_at;
            }

            // Cari event Bimtek yang relevan: prioritaskan event yang diikuti user
            $activeBimtek = null;
            if ($enrollment && !empty($enrollment->pivot->bimtek_event_id)) {
                $activeBimtek = BimtekEvent::find($enrollment->pivot->bimtek_event_id);
            }
            if (!$activeBimtek) {
                $activeBimtek = BimtekEvent::where('course_id', $course->id)->latest()->first();
            }

            $isEventEnded = $activeBimtek && in_array($activeBimtek->status, ['closed', 'completed']);
            $isCourseAlreadyFinished = ($enrollment && ($enrollment->pivot->status === 'completed' || (int) $enrollment->pivot->progress >= 100));
            $canReviewAll = $isCourseAlreadyFinished || $isEventEnded;

            $isWaitingForStart = false;
            if ($activeBimtek && !$isCourseAlreadyFinished) {
                $curPhase = $activeBimtek->current_timeline_phase ?? [];
                $phaseKey = $curPhase['phase'] ?? 'learning';
                $isWaitingForStart = ($phaseKey === 'registration' || $activeBimtek->status === 'open_registration' || ($activeBimtek->start_date && now()->lessThan($activeBimtek->start_date)));
            }

            $totalContents = 0;
            $totalTests = 0;
            $passedTests = 0;
            $isPreviousSectionDone = true;
            $mappedSections = [];

            foreach ($course->sections as $section) {
                $sectionArray = $section->toArray();
                $sectionContents = [];
                $sectionCompletedCount = 0;
                $isPreviousContentDone = true;

                if ($section->contents) {
                    foreach ($section->contents as $content) {
                        $totalContents++;
                        $isCompleted = in_array($content->id, $completedIds);
                        if ($isCompleted) {
                            $sectionCompletedCount++;
                        }
                        $isContentLocked = $canReviewAll ? false : ($isWaitingForStart || (!$isPreviousContentDone && !$isCompleted));

                        $sectionContents[] = [
                            'id'           => $content->id,
                            'name'         => $content->name,
                            'video_url'    => $content->video,
                            'document_url' => $content->document_url,
                            'is_completed' => $isCompleted,
                            'is_locked'    => $isContentLocked,
                        ];

                        $isPreviousContentDone = $isCompleted;
                    }
                }

                $totalSectionCount = count($sectionContents);
                $isContentCompleted = $totalSectionCount === 0 || $sectionCompletedCount === $totalSectionCount;

                $postTestBab = PostTest::with('questions')->where('course_section_id', $section->id)->first();
                $isPostTestBabCompleted = true;
                if ($postTestBab) {
                    $totalTests++;
                    $isPostTestBabCompleted = in_array($postTestBab->id, $passedTestIds);
                    if ($isPostTestBabCompleted) {
                        $passedTests++;
                    }
                }

                $isSectionCompleted = $isContentCompleted && $isPostTestBabCompleted;
                $isLocked = $canReviewAll ? false : (!$isWaitingForStart && !$isPreviousSectionDone);

                $sectionArray['section_contents'] = $sectionContents;
                $sectionArray['contents'] = $sectionContents;
                $sectionArray['completed_count'] = $sectionCompletedCount;
                $sectionArray['post_test_bab'] = $postTestBab;
                $sectionArray['is_post_test_bab_completed'] = $isPostTestBabCompleted;
                $sectionArray['is_content_completed'] = $isContentCompleted;
                $sectionArray['is_section_completed'] = $isSectionCompleted;
                $sectionArray['is_locked'] = $isLocked;
                $sectionArray['is_post_test_bab_locked'] = $canReviewAll ? false : ($isWaitingForStart || (!$isContentCompleted && !$isPostTestBabCompleted));

                $mappedSections[] = (object) $sectionArray;
                $isPreviousSectionDone = $isSectionCompleted;
            }

            $evaluasiAkhir = PostTest::with('questions')->where('course_id', $course->id)->whereNull('course_section_id')->first();
            $isEvaluasiAkhirCompleted = false;
            if ($evaluasiAkhir) {
                $totalTests++;
                $isEvaluasiAkhirCompleted = in_array($evaluasiAkhir->id, $passedTestIds);
                if ($isEvaluasiAkhirCompleted) {
                    $passedTests++;
                }
            }

            // Hitung Sesi Live Zoom Bimtek ke dalam Progress jika ada
            $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
            $totalZoom = $hasZoom ? 1 : 0;
            $passedZoom = ($hasZoom && !empty($course->zoom_attended)) ? 1 : 0;

            $totalItems = $totalContents + $totalTests + $totalZoom;
            $completedItems = $totalCompletedCount + $passedTests + $passedZoom;
            $currentProgress = $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;
            $isFullyCompleted = $isCourseAlreadyFinished || ($currentProgress >= 100 && (!$evaluasiAkhir || $isEvaluasiAkhirCompleted) && (!$hasZoom || !empty($course->zoom_attended)));

            $sectionsList = collect($mappedSections);
            $totalSections = $sectionsList->count();

            // Cek apakah peserta sudah mencapai hari terakhir (seluruh hari sebelum hari terakhir tuntas)
            $isLastDayReached = true;
            if ($totalSections > 1) {
                $priorSections = $sectionsList->slice(0, $totalSections - 1);
                foreach ($priorSections as $priorSec) {
                    $pContentCompleted = data_get($priorSec, 'is_content_completed', false);
                    $pPostTestDone = data_get($priorSec, 'is_post_test_bab_completed', true);
                    if (!$pContentCompleted || !$pPostTestDone) {
                        $isLastDayReached = false;
                        break;
                    }
                }
            }

            // Opsi 1: Terkunci sampai hari terakhir tercapai DAN sesi Zoom hari terakhir diikuti
            $isZoomAttended = (bool) (!empty($course->zoom_attended));
            $isEvaluasiNarasumberLocked = $canReviewAll 
                ? false 
                : ($isWaitingForStart || !$isLastDayReached || ($hasZoom && !$isZoomAttended));

            $evaluasiNarasumberLockReason = 'Evaluasi narasumber dibuka pada hari terakhir setelah sesi tatap muka (Zoom) dihadiri.';
            if ($isWaitingForStart) {
                $evaluasiNarasumberLockReason = 'Evaluasi narasumber dibuka setelah periode pelaksanaan pelatihan resmi dimulai.';
            } elseif (!$isLastDayReached) {
                $evaluasiNarasumberLockReason = 'Evaluasi narasumber dibuka pada hari terakhir (Hari ke-' . $totalSections . ') setelah sesi tatap muka (Zoom) dihadiri.';
            } elseif ($hasZoom && !$isZoomAttended) {
                $evaluasiNarasumberLockReason = 'Silakan ikuti dan konfirmasi kehadiran sesi tatap muka (Zoom) hari terakhir untuk membuka evaluasi ini.';
            }

            $isEvaluasiAkhirLocked = $canReviewAll 
                ? false 
                : ($isWaitingForStart || !$isPreviousSectionDone || ($hasZoom && !$isZoomAttended));

            // Format tanggal periode bimtek
            $formattedPeriod = null;
            if ($activeBimtek && $activeBimtek->start_date && $activeBimtek->end_date) {
                $startDate = \Carbon\Carbon::parse($activeBimtek->start_date);
                $endDate = \Carbon\Carbon::parse($activeBimtek->end_date);
                if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
                    $formattedPeriod = $startDate->translatedFormat('d') . ' - ' . $endDate->translatedFormat('d F Y');
                } elseif ($startDate->format('Y') === $endDate->format('Y')) {
                    $formattedPeriod = $startDate->translatedFormat('d M') . ' - ' . $endDate->translatedFormat('d M Y');
                } else {
                    $formattedPeriod = $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y');
                }
            }
            $isBimtekEvent = (bool) ($activeBimtek && ($activeBimtek->batch_name || $formattedPeriod));

            // Cari modul pertama yang belum selesai dan tidak terkunci agar aksi 'Lanjutkan Belajar' langsung terarah
            $firstIncompleteSectionId = null;
            foreach ($sectionsList as $s) {
                $sId = data_get($s, 'id') ?? data_get($s, 'section_id');
                $sDone = data_get($s, 'is_section_completed', false);
                $sLocked = data_get($s, 'is_locked', false);
                if (!$sDone && !$sLocked) {
                    $firstIncompleteSectionId = $sId;
                    break;
                }
            }
            if (!$firstIncompleteSectionId && $totalSections > 0) {
                $firstIncompleteSectionId = data_get($sectionsList->first(), 'id') ?? data_get($sectionsList->first(), 'section_id');
            }

            $remainingItems = max(0, $totalItems - $completedItems);
            $hasJoinedZoom = !empty($course->zoom_joined_at);
            $isZoomStarted = !$activeBimtek?->zoom_scheduled_at || now()->greaterThanOrEqualTo($activeBimtek->zoom_scheduled_at);
            $isSubmittedToPPSDM = ($course->cert_submission_status === 'diajukan_ke_ppsdm');

            if ($currentProgress >= 100) {
                $progressBarClass = 'bg-emerald-500';
                $progressTextClass = 'text-slate-600';
            } elseif ($isWaitingForStart) {
                $progressBarClass = 'bg-[#0B5CFF]';
                $progressTextClass = 'text-slate-600';
            } else {
                $progressBarClass = 'bg-amber-500';
                $progressTextClass = 'text-slate-600';
            }

            $course->sections = $sectionsList;
            $course->course_sections = $sectionsList;
            $course->evaluasi_akhir = $evaluasiAkhir;
            $course->is_evaluasi_akhir_completed = $isEvaluasiAkhirCompleted;
            $course->is_evaluasi_akhir_locked = $isEvaluasiAkhirLocked;
            $course->total_contents = $totalContents;
            $course->total_tests = $totalTests;
            $course->passed_tests = $passedTests;
            $course->are_all_topics_completed = ($totalContents > 0 && $totalCompletedCount >= $totalContents);
            $course->total_items = $totalItems;
            $course->completed_items = $completedItems;
            $course->remaining_items = $remainingItems;
            $course->current_progress = $currentProgress;
            $course->is_fully_completed = $isFullyCompleted;
            $course->can_review_all = $canReviewAll;
            $course->is_course_already_finished = $isCourseAlreadyFinished;
            $course->is_event_ended = $isEventEnded;
            $course->active_bimtek = $activeBimtek;
            $course->is_waiting_for_start = $isWaitingForStart;
            $course->formatted_period = $formattedPeriod;
            $course->is_bimtek_event = $isBimtekEvent;
            $course->is_zoom_unlocked = true;
            $course->is_zoom_attended = $isZoomAttended;
            $course->has_joined_zoom = $hasJoinedZoom;
            $course->is_zoom_started = $isZoomStarted;
            $course->is_submitted_to_ppsdm = $isSubmittedToPPSDM;
            $course->is_last_day_reached = $isLastDayReached;
            $course->is_evaluasi_narasumber_locked = $isEvaluasiNarasumberLocked;
            $course->evaluasi_narasumber_lock_reason = $evaluasiNarasumberLockReason;
            $course->first_incomplete_section_id = $firstIncompleteSectionId;
            $course->progress_bar_class = $progressBarClass;
            $course->progress_text_class = $progressTextClass;
            $course->enrollment = $enrollment;

            return [
                'success' => true,
                'message' => 'Success',
                'data'    => $course,
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::getCourseDetailSlug error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'data'    => null,
            ];
        }
    }

    /**
     * Hitung detail section dan status progres aktivitas per section (untuk view section-show)
     */
    public function getSectionDetailForUser(Course $course, object|array $section, ?BimtekEvent $activeBimtek, ?Authenticatable $user, int $sectionIndex): array
    {
        $sectionId = data_get($section, 'id') ?? data_get($section, 'section_id');
        $rawSectionName = data_get($section, 'name', '');
        $cleanSectionName = preg_replace('/^(modul|hari)\s*\d+\s*[:\-–]\s*/i', '', $rawSectionName);
        $isBimtekEvent = (bool) ($activeBimtek && ($activeBimtek->batch_name || $activeBimtek->start_date));
        $hasZoom = (bool) ($activeBimtek && $activeBimtek->zoom_url);
        $adminDriveLink = $activeBimtek?->submission_drive_link ?? null;
        $defaultPrefix = $isBimtekEvent ? 'Hari ' : 'Modul ';
        $sectionName = !empty(trim($cleanSectionName)) ? trim($cleanSectionName) : ($rawSectionName ?: $defaultPrefix . ($sectionIndex + 1));

        $sectionContents = data_get($section, 'section_contents', []) ?: data_get($section, 'contents', []);
        $totalCount = count($sectionContents);
        $completedCount = data_get($section, 'completed_count', 0);

        $completedContentsCount = collect($sectionContents)->filter(fn($c) => (bool) data_get($c, 'is_completed', false))->count();
        $isContentCompleted = data_get($section, 'is_content_completed', $totalCount === 0 || $completedCount === $totalCount);
        $isMateriDone = $totalCount > 0 ? ($completedContentsCount >= $totalCount) : (bool) $isContentCompleted;

        $enrollment = $user ? $course->students()->wherePivot('user_id', $user->id)->first() : null;
        $hasJoinedZoom = (bool) ($enrollment?->pivot?->zoom_joined_at);

        // Multi-Session Zoom & Presensi (Skenario 1)
        $bimtekSessions = collect();
        $currentSession = null;
        $attendedSessionIds = collect();

        if ($activeBimtek) {
            $bimtekSessions = $activeBimtek->sessions()->orderBy('session_date')->orderBy('start_time')->get();
            if ($user) {
                $attendedSessionIds = \Modules\LMS\Models\BimtekSessionAttendance::where('bimtek_event_id', $activeBimtek->id)
                    ->where('user_id', $user->id)
                    ->pluck('bimtek_event_session_id');
            }
            if ($bimtekSessions->isNotEmpty()) {
                // Prioritaskan sesi yang cocok dengan indeks hari/modul ini
                $currentSession = $bimtekSessions->get($sectionIndex) ?: $bimtekSessions->first();
            }
        }

        if ($currentSession) {
            $isZoomAttended = $attendedSessionIds->contains($currentSession->id);
            $isPresensiOpen = $currentSession->is_presensi_open;
            $isPresensiEnded = $currentSession->is_presensi_ended;
            $attendanceScheduleFormatted = $currentSession->attendance_schedule_formatted;
            $zoomScheduleFormatted = $currentSession->schedule_formatted;
            $isZoomEnded = $currentSession->is_zoom_ended;

            $presensiLockReason = null;
            if ($currentSession->is_presensi_upcoming) {
                $presensiLockReason = 'Presensi dibuka pada jadwal sesi tatap muka (' . $currentSession->attendance_schedule_formatted . ')';
            } elseif ($currentSession->is_presensi_ended) {
                $presensiLockReason = 'Waktu presensi sesi ini telah berakhir pada ' . ($currentSession->effective_attendance_end_at ? $currentSession->effective_attendance_end_at->translatedFormat('d F Y, H:i') . ' WIB' : 'jadwal sesi');
            }
        } else {
            $isZoomAttended = (bool) ($enrollment?->pivot?->zoom_attended);
            $isZoomEnded = false;
            $zoomScheduleFormatted = $activeBimtek?->zoom_schedule_formatted;

            $now = now();
            $presensiStart = $activeBimtek?->zoom_attendance_start_at ?? $activeBimtek?->zoom_scheduled_at ?? $activeBimtek?->start_date;
            $presensiEnd = $activeBimtek?->zoom_attendance_end_at ?? $activeBimtek?->zoom_end_at;
            $attendanceScheduleFormatted = $activeBimtek?->attendance_schedule_formatted ?? ($presensiStart ? $presensiStart->translatedFormat('d M Y, H:i') . ' WIB' : null);

            $isPresensiOpen = true;
            $isPresensiEnded = false;
            $presensiLockReason = null;
            if ($presensiStart && $now->lessThan($presensiStart)) {
                $isPresensiOpen = false;
                $presensiLockReason = 'Presensi dibuka pada jadwal sesi tatap muka (' . ($attendanceScheduleFormatted ?? $presensiStart->translatedFormat('d M Y, H:i') . ' WIB') . ')';
            } elseif ($presensiEnd && $now->greaterThan($presensiEnd)) {
                $isPresensiOpen = false;
                $isPresensiEnded = true;
                $presensiLockReason = 'Waktu presensi tatap muka telah berakhir pada ' . $presensiEnd->translatedFormat('d M Y, H:i') . ' WIB';
            }
        }

        $isZoomDone = $isZoomAttended;

        $totalActivities = 2 + ($hasZoom ? 1 : 0);
        $completedActivities = ($isMateriDone ? 1 : 0)
            + ($hasZoom ? ($isZoomDone ? 1 : 0) : 0)
            + ($isEvaluasiDone ? 1 : 0);
        $progressPercent = $totalActivities > 0 ? (int) round(($completedActivities / $totalActivities) * 100) : 0;

        $dayDate = null;
        try {
            if ($activeBimtek && $activeBimtek->start_date) {
                $dayDate = \Carbon\Carbon::parse($activeBimtek->start_date)->addDays($sectionIndex);
            }
        } catch (\Exception $e) {}

        // Bobot progres section
        $totalSectionSteps = 1; // materi
        $completedSectionSteps = $isMateriDone ? 1 : 0;
        if ($hasZoom) {
            $totalSectionSteps += 2; // zoom + bukti
            if ($isZoomDone) $completedSectionSteps++;
            if ($hasProof) $completedSectionSteps++;
        }
        if ($postTestBab) {
            $totalSectionSteps += 1;
            if ($isEvaluasiDone) $completedSectionSteps++;
        }
        $progressPct = (int) round(($completedSectionSteps / $totalSectionSteps) * 100);

        $adminFileName = data_get($postTestBab, 'admin_file_name', 'Soal_Tugas_Hari_' . ($sectionIndex + 1) . '.pdf');
        $adminFileSize = data_get($postTestBab, 'admin_file_size', '2.4 MB');
        $adminFileUrl = data_get($postTestBab, 'admin_file_url') ?: asset('storage/' . data_get($postTestBab, 'admin_file_path', 'assignments/soal/Soal_Tugas_Studi_Kasus.pdf'));
        $instructions = data_get($postTestBab, 'instructions', 'Disediakan oleh Panitia Penyelenggara sebagai panduan pengerjaan studi kasus dan lembar kerja modul.');

        return compact(
            'sectionId',
            'rawSectionName',
            'cleanSectionName',
            'sectionName',
            'isBimtekEvent',
            'hasZoom',
            'adminDriveLink',
            'defaultPrefix',
            'sectionContents',
            'totalCount',
            'completedCount',
            'completedContentsCount',
            'isContentCompleted',
            'isMateriDone',
            'isZoomAttended',
            'hasJoinedZoom',
            'isZoomDone',
            'zoomProof',
            'hasProof',
            'postTestBab',
            'isPostTestBabCompleted',
            'evalResult',
            'currentEvalType',
            'isAssignmentDone',
            'isEssayDone',
            'isEvaluasiDone',
            'isEvaluasiLocked',
            'isSectionDone',
            'isPresensiOpen',
            'isPresensiEnded',
            'attendanceScheduleFormatted',
            'presensiLockReason',
            'totalActivities',
            'completedActivities',
            'progressPercent',
            'dayDate',
            'totalSectionSteps',
            'completedSectionSteps',
            'progressPct',
            'adminFileName',
            'adminFileSize',
            'adminFileUrl',
            'instructions',
            'bimtekSessions',
            'currentSession',
            'attendedSessionIds',
            'isZoomEnded',
            'zoomScheduleFormatted'
        );
    }

    /**
     * Ambil semua course untuk Admin Pusat (Database Lokal)
     */
    public function allCourses(int $page = 1, int $perPage = 12, ?string $search = null, ?string $categoryId = null): array
    {
        try {
            $query = Course::with('category')->orderBy('created_at', self::DEFAULT_SORT);

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            $courses = $query->paginate($perPage, ['*'], 'page', $page);

            $resultData = [];
            foreach ($courses as $course) {
                $resultData[] = [
                    'slug'           => $course->slug,
                    'name'           => $course->name,
                    'category'       => ['name' => $course->category ? $course->category->name : '-'],
                    'thumbnail'      => $course->thumbnail,
                    'description'    => $course->description,
                    'students_count' => method_exists($course, 'students') ? $course->students()->count() : 0,
                ];
            }

            return [
                'success' => true,
                'message' => 'Success',
                'data'    => $resultData,
                'meta'    => [
                    'current_page' => $courses->currentPage(),
                    'last_page'    => $courses->lastPage(),
                    'total'        => $courses->total(),
                ],
                'links'   => [
                    'prev' => $courses->previousPageUrl(),
                    'next' => $courses->nextPageUrl(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::allCourses error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'data'    => [],
                'meta'    => [],
                'links'   => [],
            ];
        }
    }

    /**
     * Simpan course baru (Database Lokal)
     */
    public function storeCourse(array $data, $thumbnailFile = null): array
    {
        try {
            if ($thumbnailFile) {
                $thumbnailPath = $thumbnailFile->store('courses/thumbnails', 'public');
            } else {
                $colors = [
                    '13416B',
                    '547996',
                    '8BB1CC',
                    '79A736',
                ];

                $selectedColor = $data['bg_color'] ?? $colors[array_rand($colors)];

                $rawName = $data['name'] ?? 'Course';
                $words = explode(' ', trim($rawName));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                $encodedInitials = urlencode($initials);
                $thumbnailPath = "https://ui-avatars.com/api/?name={$encodedInitials}&background={$selectedColor}&color=fff&size=512&bold=true&length=2";
            }

            $course = Course::create([
                'category_id' => $data['category_id'],
                'name'        => $data['name'],
                'audience'    => $data['audience'] ?? 'instansi',
                'description' => $data['description'] ?? '',
                'thumbnail'   => $thumbnailPath,
            ]);

            // Kirim notifikasi katalog baru ke seluruh user
            try {
                app(\Modules\Dashboard\Services\NotificationService::class)->coursePublished($course);
            } catch (\Throwable $e) {
                Log::warning('Gagal memicu notifikasi kursus baru: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'message' => 'Course berhasil ditambahkan',
                'data'    => $course,
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::storeCourse error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data',
                'data'    => [],
            ];
        }
    }

    /**
     * Update course (Database Lokal)
     */
    public function updateCourse(array $data, $thumbnailFile = null): array
    {
        try {
            $course = Course::where('slug', $data['slug'])->first();

            if (!$course) {
                return [
                    'success' => false,
                    'message' => 'Course tidak ditemukan',
                    'data'    => [],
                ];
            }

            $thumbnailPath = $course->thumbnail;

            if ($thumbnailFile) {
                if ($thumbnailPath && Storage::disk('public')->exists($thumbnailPath) && !str_starts_with($thumbnailPath, 'http')) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
                $thumbnailPath = $thumbnailFile->store('courses/thumbnails', 'public');
            } elseif (!empty($data['bg_color']) && str_starts_with($thumbnailPath ?? '', 'http')) {
                $rawName = $data['name'] ?? $course->name;
                $words = explode(' ', trim($rawName));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                $encodedInitials = urlencode($initials);

                $thumbnailPath = "https://ui-avatars.com/api/?name={$encodedInitials}&background={$data['bg_color']}&color=fff&size=512&bold=true&length=2";
            }

            $course->update([
                'category_id' => $data['category_id'] ?? $course->category_id,
                'name'        => $data['name'] ?? $course->name,
                'audience'    => $data['audience'] ?? $course->audience,
                'description' => $data['description'] ?? $course->description,
                'thumbnail'   => $thumbnailPath,
            ]);

            return [
                'success' => true,
                'message' => 'Course berhasil diperbarui',
                'data'    => $course,
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::updateCourse error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data',
                'data'    => [],
            ];
        }
    }

    /**
     * Hapus course (Database Lokal)
     */
    public function deleteCourse(string $slug): array
    {
        try {
            $course = Course::where('slug', $slug)->first();

            if (!$course) {
                return [
                    'success' => false,
                    'message' => 'Course tidak ditemukan',
                ];
            }

            if ($course->thumbnail && Storage::disk('public')->exists($course->thumbnail)) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $course->delete();

            return [
                'success' => true,
                'message' => 'Course berhasil dihapus',
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::deleteCourse error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghapus data',
            ];
        }
    }

    /**
     * Ambil detail course berdasarkan slug (Database Lokal) beserta data PostTest
     */
    public function getCourseBySlug(string $slug): array
    {
        try {
            $course = Course::with(['category', 'sections.contents'])->where('slug', $slug)->first();

            if (!$course) {
                return [
                    'success' => false,
                    'message' => 'Course tidak ditemukan',
                    'data'    => null,
                ];
            }

            $courseData = $course->toArray();
            $courseData['thumbnail_url'] = $course->thumbnail ? Storage::url($course->thumbnail) : null;

            $sectionsWithPostTests = [];
            foreach ($courseData['sections'] ?? [] as $sec) {
                $existingPostTest = PostTest::where('course_section_id', $sec['id'])->first();
                $sec['existing_post_test'] = $existingPostTest ? $existingPostTest->toArray() : null;
                $sectionsWithPostTests[] = $sec;
            }

            $existingFinalTest = PostTest::where('course_id', $course->id)
                ->whereNull('course_section_id')
                ->first();

            $courseData['sections'] = $sectionsWithPostTests;
            $courseData['course_sections'] = $sectionsWithPostTests;
            $courseData['existing_final_test'] = $existingFinalTest ? $existingFinalTest->toArray() : null;

            return [
                'success' => true,
                'message' => 'Success',
                'data'    => $courseData,
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::getCourseBySlug error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Submit Post Test secara lokal (Database Lokal)
     */
    public function submitPostTestResult(string $slug, string $postTestId, int $score, bool $isPassed): array
    {
        try {
            DB::table('post_test_results')->updateOrInsert(
                [
                    'user_id'      => Auth::id(),
                    'post_test_id' => $postTestId,
                ],
                [
                    'score'      => $score,
                    'is_passed'  => $isPassed,
                    'updated_at' => now(),
                ]
            );

            return [
                'success' => true,
                'message' => 'Hasil evaluasi berhasil disimpan.',
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::submitPostTestResult error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan hasil evaluasi.',
            ];
        }
    }

    public function getEnrolledCourseIds(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user ? $user->enrolledCourses()->pluck('courses.id')->toArray() : [];
    }

    /**
     * Ambil daftar course_id yang harus dikecualikan dari katalog untuk user yang sedang login.
     * Kursus dikecualikan jika:
     * 1. Sudah diselesaikan / lulus (progress 100% atau status completed).
     * 2. Kursus mandiri yang sudah terdaftar.
     * 3. Kursus bimtek yang sedang diikuti dan gelombangnya aktif (open_registration atau ongoing).
     * Kursus TIDAK dikecualikan (muncul di katalog untuk retake) jika:
     * - User pernah ikut gelombang lama yang sudah closed/completed tapi belum lulus,
     *   dan sekarang ada gelombang baru yang berstatus open_registration.
     */
    public function getExcludedCatalogCourseIds(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) {
            return [];
        }

        $enrollments = DB::table('course_student')
            ->where('user_id', $userId)
            ->get();

        $excludedIds = [];

        foreach ($enrollments as $enrollment) {
            // 1. Sudah lulus / selesai -> user tidak perlu & tidak bisa daftar lagi
            if ($enrollment->status === 'completed' || (int) $enrollment->progress >= 100) {
                $excludedIds[] = $enrollment->course_id;
                continue;
            }

            // 2. Kursus mandiri (tanpa bimtek_event_id) -> sudah terdaftar
            if (empty($enrollment->bimtek_event_id)) {
                $excludedIds[] = $enrollment->course_id;
                continue;
            }

            // 3. Kursus Bimtek: Cek status event yang diikuti user
            $currentEvent = DB::table('bimtek_events')->where('id', $enrollment->bimtek_event_id)->first();
            if ($currentEvent && in_array($currentEvent->status, ['open_registration', 'ongoing'])) {
                // Sedang terdaftar di gelombang aktif -> sembunyikan dari katalog
                $excludedIds[] = $enrollment->course_id;
                continue;
            }

            // 4. Gelombang lama sudah ditutup/selesai (closed/completed) dan user belum lulus:
            // Cek apakah ada gelombang baru yang buka pendaftaran (open_registration) untuk kursus ini:
            $hasNewOpenBatch = DB::table('bimtek_events')
                ->where('course_id', $enrollment->course_id)
                ->where('id', '!=', $enrollment->bimtek_event_id)
                ->where('status', 'open_registration')
                ->exists();

            if (!$hasNewOpenBatch) {
                // Tidak ada gelombang baru yang buka pendaftaran -> exclude dari katalog
                $excludedIds[] = $enrollment->course_id;
            }
        }

        return array_values(array_unique($excludedIds));
    }

    /**
     * Ambil semua kursus untuk halaman Katalog.
     * Hanya menampilkan kursus/event yang belum didaftarkan oleh user.
     */
    public function getCatalogCourses(int $perPage = 12, ?string $search = null, ?array $categoryIds = null, ?string $programType = null)
    {
        $excludedCourseIds = $this->getExcludedCatalogCourseIds();

        $query = Course::with([
            'category',
            'bimtekEvents' => fn($q) => $q->whereIn('status', ['open_registration', 'ongoing'])
                ->withCount('participants')
                ->orderByRaw("FIELD(status, 'open_registration', 'ongoing')")
                ->latest(),
            'sections.contents' => fn($q) => $q
                ->select('id', 'course_section_id', 'name', 'position', 'video', 'document')
                ->orderBy('position'),
        ])
            ->withCount(['sections', 'students'])
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(!empty($categoryIds), fn($q) => $q->whereIn('category_id', $categoryIds));

        // Kecualikan kursus yang sudah didaftarkan / diselesaikan
        if (!empty($excludedCourseIds)) {
            $query->whereNotIn('id', $excludedCourseIds);
        }

        // Katalog hanya menampilkan kursus berbasis event dengan Bimtek Event aktif (open_registration atau ongoing)
        $query->whereHas('bimtekEvents', fn($b) => $b->whereIn('status', ['open_registration', 'ongoing']));

        return $query->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mendaftarkan (Enroll) user ke dalam kursus atau gelombang Bimtek Event
     */
    public function enrollUser(string $slug, ?string $bimtekEventId = null): array
    {
        try {
            $course = Course::where('slug', $slug)->first();

            if (!$course) {
                return ['success' => false, 'message' => 'Kursus tidak ditemukan.'];
            }

            /** @var User $user */
            $user = Auth::user();

            // Cek apakah ada gelombang Bimtek aktif
            $activeBimtek = null;
            if ($bimtekEventId) {
                $activeBimtek = BimtekEvent::where('id', $bimtekEventId)->where('course_id', $course->id)->first();
            }
            if (!$activeBimtek) {
                $activeBimtek = BimtekEvent::where('course_id', $course->id)
                    ->whereIn('status', ['open_registration', 'ongoing'])
                    ->orderByRaw("FIELD(status, 'open_registration', 'ongoing')")
                    ->latest()
                    ->first();
            }

            $existingEnrollment = $user->enrolledCourses()->where('course_id', $course->id)->first();

            if ($existingEnrollment) {
                // Jika sudah lulus / completed, tolak pendaftaran ulang
                if ($existingEnrollment->pivot->status === 'completed' || (int) $existingEnrollment->pivot->progress >= 100) {
                    return [
                        'success' => false,
                        'message' => 'Anda telah menyelesaikan kursus ini dan dinyatakan lulus. Anda dapat mengulas materi kapan saja di Kursus Saya.',
                    ];
                }

                // User sudah punya kursus ini di "Kursus Saya"
                if ($activeBimtek) {
                    if ($existingEnrollment->pivot->bimtek_event_id === $activeBimtek->id) {
                        return [
                            'success' => true,
                            'message' => "Anda sudah terdaftar di gelombang {$activeBimtek->batch_name}. Selamat belajar!",
                        ];
                    }

                    // Jika gelombang sedang berlangsung atau tutup, tolak pendaftaran
                    $isPhaseNotReg = ($activeBimtek->current_timeline_phase['phase'] ?? 'registration') !== 'registration';
                    if ($activeBimtek->status !== 'open_registration' || $isPhaseNotReg) {
                        return [
                            'success' => false,
                            'message' => 'Pendaftaran untuk gelombang ini sudah ditutup karena pelatihan sedang berlangsung. Silakan nantikan pembukaan gelombang berikutnya.',
                        ];
                    }

                    // Retake / Bergabung ke gelombang Bimtek yang baru dibuka!
                    // Reset kehadiran zoom dan submission sertifikat untuk gelombang baru
                    $user->enrolledCourses()->updateExistingPivot($course->id, [
                        'bimtek_event_id'        => $activeBimtek->id,
                        'status'                 => 'enrolled',
                        'zoom_attended'          => 0,
                        'zoom_joined_at'         => null,
                        'zoom_attended_at'       => null,
                        'cert_submission_status' => 'belum_diajukan',
                        'certificate_code'       => null,
                        'official_cert_number'   => null,
                        'certificate_file'       => null,
                        'certificate_issued_at'  => null,
                        'completed_at'           => null,
                        'updated_at'             => now(),
                    ]);

                    return [
                        'success' => true,
                        'message' => "Selamat! Anda berhasil terdaftar pada {$activeBimtek->batch_name}.",
                    ];
                }

                return ['success' => false, 'message' => 'Anda sudah terdaftar di kursus ini.'];
            }

            // Pendaftaran baru (belum pernah enroll kursus ini)
            $isPhaseNotReg = ($activeBimtek?->current_timeline_phase['phase'] ?? 'registration') !== 'registration';
            if ($activeBimtek && ($activeBimtek->status !== 'open_registration' || $isPhaseNotReg)) {
                return [
                    'success' => false,
                    'message' => 'Pendaftaran untuk gelombang ini sudah ditutup karena pelatihan sedang berlangsung. Silakan nantikan pembukaan gelombang berikutnya.',
                ];
            }
            $user->enrolledCourses()->attach($course->id, [
                'status'                 => 'enrolled',
                'progress'               => 0,
                'bimtek_event_id'        => $activeBimtek?->id,
                'cert_submission_status' => $activeBimtek ? 'belum_diajukan' : null,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            $message = $activeBimtek
                ? "Selamat! Anda berhasil terdaftar pada gelombang {$activeBimtek->batch_name}."
                : 'Berhasil mendaftar kursus! Selamat belajar.';

            return ['success' => true, 'message' => $message];
        } catch (\Exception $e) {
            Log::error('Enrollment error: ' . $e->getMessage());

            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat mendaftar.'];
        }
    }

    public function getStudentContentForShow(string $contentId): SectionContent
    {
        $content = SectionContent::with(['documents', 'libraries.libraryCategory'])->findOrFail($contentId);
        $content->is_completed = $content->isCompletedByUser((string) Auth::id());

        return $content;
    }

    private function userProgressIds(): array
    {
        $completedContentIds = StudentContentProgress::where('user_id', Auth::id())
            ->pluck('section_content_id')->all();

        $passedTestIds = DB::table('post_test_results')
            ->where('user_id', Auth::id())
            ->where('is_passed', 1)
            ->pluck('post_test_id')->all();

        return [$completedContentIds, $passedTestIds];
    }

    private function isSectionDone($section, array $completedContentIds, array $passedTestIds): bool
    {
        foreach ($section->contents as $c) {
            if (!in_array($c->id, $completedContentIds)) {
                return false;
            }
        }

        $test = PostTest::where('course_section_id', $section->id)->first();

        return !$test || in_array($test->id, $passedTestIds);
    }

    private function loadCourseOrdered(string $courseId): ?Course
    {
        return Course::with(['sections.contents' => fn($q) => $q->orderBy('position')])->find($courseId);
    }

    public function canAccessContent(SectionContent $content): bool
    {
        $targetSection = $content->section;
        $course = $targetSection ? $this->loadCourseOrdered($targetSection->course_id) : null;
        if (!$course) {
            return false;
        }

        $userId = Auth::id();
        if ($userId) {
            $enrollment = DB::table('course_student')
                ->where('course_id', $course->id)
                ->where('user_id', $userId)
                ->first();

            // Jika kursus sudah berstatus completed atau progres 100%, izinkan akses ulas seluruh materi
            if ($enrollment && ($enrollment->status === 'completed' || (int) $enrollment->progress >= 100)) {
                return true;
            }

            // Jika event bimtek terkait sudah selesai atau ditutup, izinkan peserta mengulas materi
            if ($enrollment && !empty($enrollment->bimtek_event_id)) {
                $bimtek = DB::table('bimtek_events')->where('id', $enrollment->bimtek_event_id)->first();
                if ($bimtek) {
                    if (in_array($bimtek->status, ['closed', 'completed'])) {
                        return true;
                    }

                    // Jika pelatihan belum dimulai (masih tahap pendaftaran / belum masuk start_date), tolak akses materi
                    if ($bimtek->status === 'open_registration' || $bimtek->status === 'draft' || ($bimtek->start_date && now()->lessThan(\Carbon\Carbon::parse($bimtek->start_date)))) {
                        return false;
                    }
                }
            }
        }

        [$completed, $passed] = $this->userProgressIds();

        if (in_array($content->id, $completed)) {
            return true;
        }

        foreach ($course->sections as $section) {
            if ($section->id === $targetSection->id) {
                foreach ($section->contents as $c) {
                    if ($c->id === $content->id) {
                        return true;
                    }
                    if (!in_array($c->id, $completed)) {
                        return false;
                    }
                }

                return true;
            }

            if (!$this->isSectionDone($section, $completed, $passed)) {
                return false;
            }
        }

        return false;
    }

    public function canAccessTest(PostTest $test): bool
    {
        [$completed, $passed] = $this->userProgressIds();

        if (in_array($test->id, $passed)) {
            return true;
        }

        $courseId = $test->course_id;
        if ($test->course_section_id) {
            $courseId = CourseSection::whereKey($test->course_section_id)->value('course_id');
        }

        $course = $courseId ? $this->loadCourseOrdered($courseId) : null;
        if (!$course) {
            return false;
        }

        $userId = Auth::id();
        if ($userId) {
            $enrollment = DB::table('course_student')
                ->where('course_id', $course->id)
                ->where('user_id', $userId)
                ->first();

            // Jika kursus sudah selesai, izinkan akses ulas hasil
            if ($enrollment && ($enrollment->status === 'completed' || (int) $enrollment->progress >= 100)) {
                return true;
            }

            // Jika event bimtek terkait belum mulai tanggal pelaksanaan: tolak akses evaluasi
            if ($enrollment && !empty($enrollment->bimtek_event_id)) {
                $bimtek = DB::table('bimtek_events')->where('id', $enrollment->bimtek_event_id)->first();
                if ($bimtek && !in_array($bimtek->status, ['closed', 'completed'])) {
                    if ($bimtek->status === 'open_registration' || $bimtek->status === 'draft' || ($bimtek->start_date && now()->lessThan(\Carbon\Carbon::parse($bimtek->start_date)))) {
                        return false;
                    }
                }
            }
        }

        foreach ($course->sections as $section) {
            if ($test->course_section_id && $section->id === $test->course_section_id) {
                foreach ($section->contents as $c) {
                    if (!in_array($c->id, $completed)) {
                        return false;
                    }
                }

                return true;
            }

            if (!$this->isSectionDone($section, $completed, $passed)) {
                return false;
            }
        }

        if (!is_null($test->course_section_id)) {
            return false;
        }

        $activeBimtek = BimtekEvent::where('course_id', $course->id)->latest()->first();
        if ($activeBimtek && $activeBimtek->zoom_url) {
            $zoomAttended = DB::table('course_student')
                ->where('course_id', $course->id)
                ->where('user_id', Auth::id())
                ->value('zoom_attended');

            if (!$zoomAttended) {
                return false;
            }
        }

        return true;
    }
}