<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Dashboard\Http\Requests\UpdateProfileRequest;
use Modules\Dashboard\Services\DashboardService;
use Modules\LMS\Models\Library;
use Modules\LMS\Models\PostTest;
use Modules\LMS\Models\PostTestResult;
use Modules\LMS\Services\CourseService;
use Modules\MasterData\Models\Province;
use Modules\User\Enums\InstitutionType;
use Modules\User\Models\UserProfile;
use Modules\User\Models\UserScope;

class UserDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashbordService,
        private CourseService $courseService,
    ) {}

    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);
        $provinces = Province::all();
        $stats = $this->courseService->myCourseStats();

        // 1. PENGAMBILAN THUMBNAIL UNTUK LAST COURSE
        $lastCourse = $this->courseService->getLastAccessedCourse();
        if ($lastCourse) {
            $dbLastCourse = \Modules\LMS\Models\Course::with('category')->where('slug', $lastCourse->slug)->first();
            if ($dbLastCourse) {
                $lastCourse->description = $dbLastCourse->description;
                $lastCourse->category_name = $dbLastCourse->category ? $dbLastCourse->category->name : null;
                $lastCourse->thumbnail = $dbLastCourse->thumbnail; // Apa adanya dari DB
            }
        }

        // 2. PENGAMBILAN THUMBNAIL UNTUK RECENT COURSES (Dari Total Terdaftar)
        $recentCourses = $user->enrolledCourses()->latest()->take(5)->get();
        $recentCourses->transform(function ($course) {
            $dbCourse = \Modules\LMS\Models\Course::where('slug', $course->slug)->first();
            if ($dbCourse) {
                $course->description = $dbCourse->description;
                $course->thumbnail = $dbCourse->thumbnail;
            }
            return $course;
        });

        $userId = $user->id;
        $myCourses = $user->enrolledCourses()->get();

        $chartDataByCourse = [];

        if ($myCourses->count() > 0) {
            $courseIds = $myCourses->pluck('id');

            $allPostTests = PostTest::whereIn('course_id', $courseIds)->orderBy('id')->get();
            $postTestIds = $allPostTests->pluck('id');

            $userResults = PostTestResult::selectRaw('post_test_id, MAX(score) as user_score')
                ->where('user_id', $userId)
                ->whereIn('post_test_id', $postTestIds)
                ->groupBy('post_test_id')
                ->pluck('user_score', 'post_test_id');

            foreach ($myCourses as $c) {
                $courseTests = $allPostTests->where('course_id', $c->id);

                $labels = [];
                $uScores = [];

                foreach ($courseTests as $test) {
                    $labels[] = $test->title;
                    $uScores[] = $userResults[$test->id] ?? 0;
                }

                $chartDataByCourse[$c->id] = [
                    'course_id' => $c->id,
                    'course_name' => $c->name,
                    'labels' => $labels,
                    'user_scores' => $uScores,
                ];
            }
        }

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

        return view('dashboard::pages.user.index', [
            'profile' => $profile,
            'provinces' => $provinces,
            'stats' => $stats,
            'lastCourse' => $lastCourse,
            'recentCourses' => $recentCourses,
            'chartDataByCourse' => $chartDataByCourse,
            'lastLibrary' => $lastAccessedLibrary,
        ]);
    }

    public function getRegencies(Request $request)
    {
        $request->validate(['province_code' => 'required|string']);
        $province = Province::where('code', $request->province_code)->firstOrFail();
        return response()->json(['success' => true, 'data' => $province->regencies]);
    }

    public function updateInstansi(Request $request)
    {
        $request->validate([
            'asalInstansi' => 'required|in:pusat,provinsi,kabkota',
            'instansi' => 'required|string|max:255',
            'instansi_lainnya' => 'required_if:instansi,lainnya|nullable|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'province_code' => 'required_if:asalInstansi,provinsi,kabkota|nullable|string',
            'regency_code' => 'required_if:asalInstansi,kabkota|nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $profile = UserProfile::firstOrNew(['user_id' => $user->id]);

        $institutionType = match ($request->asalInstansi) {
            'pusat' => InstitutionType::PUSAT,
            'provinsi' => InstitutionType::PROVINSI,
            'kabkota' => InstitutionType::KAB_KOTA,
        };

        // Menentukan nama instansi final
        $instansi = $request->instansi === 'lainnya'
            ? trim($request->instansi_lainnya)
            : $request->instansi;

        if ($request->instansi === 'lainnya') {
            \Modules\MasterData\Models\Institution::firstOrCreate(
                [
                    'name' => $instansi,
                    'type' => $request->asalInstansi === 'pusat' ? 'pusat' : 'daerah',
                    'province_code' => $request->province_code,
                    'regency_code' => $request->asalInstansi === 'provinsi' ? null : $request->regency_code,
                ],
                [
                    'is_active' => true
                ]
            );
        }

        $profile->institution_type = $institutionType->value;
        $profile->instansi = $instansi;
        $profile->unit_kerja = $request->unit_kerja;
        $profile->save();

        UserScope::updateOrCreate(
            ['user_id' => $user->id],
            [
                'province_code' => $request->province_code ?? null,
                'regency_code' => $request->regency_code ?? null,
            ]
        );

        ToastMagic::success("Data instansi berhasil disimpan!");
        return redirect()->route('user.dashboard')->with('success', 'Data instansi berhasil disimpan.');
    }

    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $provinces = Province::all();
        return view('dashboard::pages.user.profile', compact('user', 'provinces'));
    }

    public function storeOrUpdateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validated();
        $data = $this->dashbordService->updateProfile($user, $validated);
        Log::info($data);
        ToastMagic::success("Profile berhasil diupdate!");
        return to_route('user.profile');
    }

    public function searchSuggest(Request $request)
    {
        $keyword = $request->input('q');

        if (empty($keyword)) {
            return response()->json(['enrolled_courses' => [], 'available_catalogs' => [], 'libraries' => []]);
        }

        $getInitials = function ($name) {
            $words = explode(' ', trim($name));
            $initials = '';
            foreach (array_slice($words, 0, 2) as $w) {
                if (!empty($w)) $initials .= strtoupper(substr($w, 0, 1));
            }
            return strlen($initials) < 1 ? 'S' : (strlen($initials) > 2 ? substr($initials, 0, 2) : $initials);
        };

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $enrolledCourseIds = $user->enrolledCourses()->pluck('course_id')->toArray();

        $enrolledCourses = \Modules\LMS\Models\Course::whereIn('id', $enrolledCourseIds)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
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
                    'color' => 'bg-[#184A78]'
                ];
            });

        $availableCatalogs = \Modules\LMS\Models\Course::whereNotIn('id', $enrolledCourseIds)
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
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
                    'color' => 'bg-emerald-600'
                ];
            });

        $libraries = \Modules\LMS\Models\Library::where('title', 'like', "%{$keyword}%")
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
                    'color' => 'bg-amber-600'
                ];
            });

        return response()->json([
            'enrolled_courses' => $enrolledCourses,
            'available_catalogs' => $availableCatalogs,
            'libraries' => $libraries,
        ]);
    }
}
