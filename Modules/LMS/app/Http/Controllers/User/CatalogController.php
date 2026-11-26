<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Support\SmartCache;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Services\CourseService;

class CatalogController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $search = $request->get('search');
        $waveStatus = $request->get('status', $request->get('type', 'all'));
        $programType = $waveStatus;
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;
        $userId = auth()->id();

        $cacheKey = 'user_catalog:' . md5(json_encode([
            'uid'        => $userId,
            'search'     => $search,
            'status'     => $waveStatus,
            'page'       => $page,
            'per_page'   => $perPage,
        ]));

        $cached = SmartCache::remember(
            [
                SmartCache::DOMAIN_LMS_EVENTS,
                SmartCache::DOMAIN_LMS_COURSES,
                SmartCache::userDomain($userId),
            ],
            $cacheKey,
            180,
            function () use ($search, $waveStatus, $userId, $perPage): array {
                $excludedCourseIds = $this->courseService->getExcludedCatalogCourseIds();
                $enrolledCourseIds = $this->courseService->getEnrolledCourseIds();
                $courses = $this->courseService->getCatalogCourses($perPage, $search, [], $waveStatus);

                $userBimtekEventIds = [];
                if ($userId) {
                    $userBimtekEventIds = \Illuminate\Support\Facades\DB::table('course_student')
                        ->where('user_id', $userId)
                        ->whereNotNull('bimtek_event_id')
                        ->pluck('bimtek_event_id')
                        ->toArray();
                }

                return compact(
                    'courses',
                    'excludedCourseIds',
                    'enrolledCourseIds',
                    'userBimtekEventIds'
                );
            }
        );

        $courses = $cached['courses'];
        $coursePayload = $this->buildCoursePayload(
            $courses,
            $cached['enrolledCourseIds'],
            $cached['userBimtekEventIds']
        );
        $hasMore = $courses->hasMorePages();
        $nextPage = $hasMore ? $courses->currentPage() + 1 : null;

        if ($request->boolean('partial')) {
            $html = '';
            foreach ($courses as $course) {
                $html .= view('lms::user.katalog.partials.course-card', [
                    'course'             => $course,
                    'enrolledCourseIds'  => $cached['enrolledCourseIds'],
                    'userBimtekEventIds' => $cached['userBimtekEventIds'],
                ])->render();
            }

            return response()->json([
                'html'     => $html,
                'payload'  => $coursePayload,
                'hasMore'  => $hasMore,
                'nextPage' => $nextPage,
            ]);
        }

        return view('lms::user.katalog.index', array_merge($cached, compact(
            'search',
            'programType',
            'waveStatus',
            'coursePayload',
            'hasMore',
            'nextPage'
        )));
    }

    private function buildCoursePayload($courses, array $enrolledCourseIds, array $userBimtekEventIds): array
    {
        return $courses->mapWithKeys(function ($c) use ($enrolledCourseIds, $userBimtekEventIds) {
            $sections = $c->sections->values()->map(function ($s, $i) {
                $rawName = (string) ($s->name ?? $s->title ?? '');
                $cleanName = trim((string) preg_replace('/^(modul|hari)\s*(ke[\-\s]*)?\d+\s*[:\-–]\s*/i', '', $rawName));

                return [
                    'name'     => $cleanName !== '' ? $cleanName : ($rawName !== '' ? $rawName : 'Bagian ' . ($i + 1)),
                    'contents' => $s->contents->values()->map(fn($ct, $idx) => [
                        'letter' => chr(97 + ($idx % 26)),
                        'name'   => $ct->name,
                        'type'   => $ct->video ? 'video' : ($ct->document ? 'document' : 'text'),
                    ])->values(),
                ];
            });

            $activeB = $c->bimtekEvents->first();
            $isEnrolledInCourse = in_array($c->id, $enrolledCourseIds);
            $isEnrolledInThisBimtek = $activeB && in_array($activeB->id, $userBimtekEventIds);
            $curPhase = $activeB?->current_timeline_phase ?? [];
            $phaseKey = $curPhase['phase'] ?? ($activeB?->status === 'open_registration' ? 'registration' : 'learning');
            $isRegistrationOpen = ($phaseKey === 'registration' && $activeB?->status === 'open_registration');

            return [$c->id => [
                'name'                   => $c->name,
                'slug'                   => $c->slug,
                'myCourseUrl'            => route('user.course.my-course.detail', $c->slug),
                'description'            => $c->description,
                'students'               => $c->students_count,
                'modules'                => $c->sections_count,
                'topics'                 => $sections->sum(fn($s) => count($s['contents'])),
                'thumbnail'              => ! empty($c->thumbnail)
                    ? (str_starts_with($c->thumbnail, 'http') ? $c->thumbnail : asset('storage/' . $c->thumbnail))
                    : null,
                'audienceLabel'          => ($c->audience ?? 'instansi') === 'swasta' ? 'Swasta' : 'Instansi',
                'sections'               => $sections,
                'enrollUrl'              => route('user.catalog.enroll', $c->slug),
                'isEvent'                => (bool) $activeB,
                'isRegistrationOpen'     => $isRegistrationOpen,
                'bimtekId'               => $activeB?->id,
                'bimtekBatchName'        => $activeB?->formatted_wave_subtitle,
                'bimtekBatchCode'        => $activeB?->batch_code,
                'bimtekStatus'           => $activeB?->status,
                'bimtekStatusBadge'      => $activeB?->status_badge,
                'bimtekExecutionType'    => $activeB?->execution_type,
                'bimtekDates'            => $activeB ? ($activeB->start_date ? $activeB->start_date->format('d M') : '-') . ' s/d ' . ($activeB->end_date ? $activeB->end_date->format('d M Y') : '-') : null,
                'bimtekTime'             => $activeB?->start_date ? $activeB->start_date->format('H:i') . ' WIB' : '08:00 WIB',
                'bimtekQuota'            => (int) ($activeB?->quota ?? 0),
                'bimtekParticipants'     => (int) ($activeB?->participants_count ?? 0),
                'bimtekQuotaPct'         => ($activeB?->quota ?? 0) > 0 ? min(100, (int) round((($activeB?->participants_count ?? 0) / $activeB->quota) * 100)) : 0,
                'timelineSteps'          => $activeB ? $activeB->timeline_steps : [],
                'currentTimelinePhase'   => $activeB ? $activeB->current_timeline_phase : null,
                'isEnrolledInCourse'     => $isEnrolledInCourse,
                'isEnrolledInThisBimtek' => $isEnrolledInThisBimtek,
            ]];
        })->all();
    }

    public function enroll(Request $request, string $slug): RedirectResponse
    {
        $bimtekEventId = $request->input('bimtek_event_id');
        $result = $this->courseService->enrollUser($slug, $bimtekEventId);

        if ($result['success']) {
            ToastMagic::success($result['message']);

            return redirect()->route('user.course.my-course.detail', $slug);
        }

        ToastMagic::error($result['message']);

        return redirect()->back();
    }
}