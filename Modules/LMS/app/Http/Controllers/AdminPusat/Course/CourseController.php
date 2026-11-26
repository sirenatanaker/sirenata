<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use App\Support\SmartCache;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Http\Requests\Api\StoreCourseRequest;
use Modules\LMS\Http\Requests\Api\UpdateCourseRequest;
use Modules\LMS\Services\CourseService;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
    ) {}

    public function index(Request $request): View
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = $request->integer('row_per_page', 12);
        $search = $request->get('search');

        $cached = SmartCache::remember(
            [SmartCache::DOMAIN_LMS_COURSES, SmartCache::DOMAIN_LMS_EVENTS],
            'admin_courses_index:' . md5(json_encode([$page, $perPage, $search])),
            300,
            function () use ($page, $perPage, $search) {
                return [
                    'result' => $this->courseService->allCourses(
                        page: $page,
                        perPage: $perPage,
                        search: $search,
                    ),
                ];
            }
        );

        $result = $cached['result'];

        return view('lms::admin-pusat.course.index', [
            'courses'    => collect(json_decode(json_encode($result['data']))),
            'meta'       => $result['meta'],
            'links'      => $result['links'],
            'success'    => $result['success'],
            'message'    => $result['message'],
        ]);
    }

    public function create(): View
    {
        return view('lms::admin-pusat.course.create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        try {
            $result = $this->courseService->storeCourse(
                data: $request->validated(),
                thumbnailFile: $request->file('thumbnail'),
            );

            if ($result['success']) {
                ToastMagic::success('Course berhasil ditambahkan!');

                return redirect()->route('admin-pusat.management-course.courses.index');
            }

            return back()->withInput()->with('error', $result['message']);
        } catch (\Exception $e) {
            ToastMagic::error($e->getMessage());

            return back()->withInput();
        }
    }

    public function show(string $slug): View
    {
        $courseResponse = $this->courseService->getCourseBySlug($slug);
        $course = json_decode(json_encode($courseResponse['data']));

        if (!$course) {
            abort(404, 'Course tidak ditemukan');
        }

        return view('lms::admin-pusat.course.show', compact('course'));
    }

    public function edit(string $slug): View
    {
        $courseResponse = $this->courseService->getCourseBySlug($slug);

        if (!$courseResponse['success'] || empty($courseResponse['data'])) {
            abort(404, 'Course tidak ditemukan di server');
        }

        $course = json_decode(json_encode($courseResponse['data']));

        return view('lms::admin-pusat.course.edit', compact('course'));
    }

    public function update(UpdateCourseRequest $request, string $slug): RedirectResponse
    {
        $payload = array_merge($request->validated(), [
            'slug'       => $slug,
            'thumb_mode' => $request->input('thumb_mode'),
        ]);
        $result = $this->courseService->updateCourse(
            data: $payload,
            thumbnailFile: $request->file('thumbnail'),
        );

        if ($result['success']) {
            ToastMagic::success($result['message']);

            if ($request->input('redirect_to') === 'show') {
                $updatedSlug = $result['data']->slug ?? $slug;

                return redirect()->route('admin-pusat.management-course.courses.show', $updatedSlug)
                    ->with('success', $result['message']);
            }

            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        ToastMagic::error($result['message']);

        return back()->withInput()->with('error', $result['message']);
    }

    public function destroy(string $slug): RedirectResponse
    {
        $result = $this->courseService->deleteCourse($slug);

        if ($result['success']) {
            return redirect()->route('admin-pusat.management-course.courses.index')
                ->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}