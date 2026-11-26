<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Support\SmartCache;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Services\CourseService;

class CatalogController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->get('search');
        $waveStatus = $request->get('status', $request->get('type', 'all'));
        $programType = $waveStatus;
        $page = (int) $request->get('page', 1);
        $userId = auth()->id();

        $cacheKey = 'user_catalog:' . md5(json_encode([
            'uid'        => $userId,
            'search'     => $search,
            'status'     => $waveStatus,
            'page'       => $page,
        ]));

        $cached = SmartCache::remember(
            [
                SmartCache::DOMAIN_LMS_EVENTS,
                SmartCache::DOMAIN_LMS_COURSES,
                SmartCache::userDomain($userId),
            ],
            $cacheKey,
            180,
            function () use ($search, $waveStatus, $userId): array {
                $excludedCourseIds = $this->courseService->getExcludedCatalogCourseIds();
                $enrolledCourseIds = $this->courseService->getEnrolledCourseIds();
                $courses = $this->courseService->getCatalogCourses(12, $search, [], $waveStatus);

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

        return view('lms::user.katalog.index', array_merge($cached, compact(
            'search',
            'programType',
            'waveStatus'
        )));
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