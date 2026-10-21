<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Illuminate\Support\Collection;
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
     * Get aggregated user dashboard data.
     */
    public function getDashboardData(User $user): array
    {
        $profile = UserProfile::firstOrCreate(['user_id' => $user->id]);
        $provinces = Province::all();
        $stats = $this->courseService->myCourseStats();

        // 1. Thumbnail untuk last course
        $lastCourse = $this->courseService->getLastAccessedCourse();
        if ($lastCourse) {
            $dbLastCourse = Course::with('category')->where('slug', $lastCourse->slug)->first();
            if ($dbLastCourse) {
                $lastCourse->description = $dbLastCourse->description;
                $lastCourse->category_name = $dbLastCourse->category ? $dbLastCourse->category->name : null;
                $lastCourse->thumbnail = $dbLastCourse->thumbnail;
            }
        }

        // 2. Thumbnail untuk recent courses
        $recentCourses = $user->enrolledCourses()->latest()->take(5)->get();
        $recentCourses->transform(function ($course) {
            $dbCourse = Course::where('slug', $course->slug)->first();
            if ($dbCourse) {
                $course->description = $dbCourse->description;
                $course->thumbnail = $dbCourse->thumbnail;
            }
            return $course;
        });

        // 3. Chart data by course
        $myCourses = $user->enrolledCourses()->get();
        $chartDataByCourse = [];

        if ($myCourses->count() > 0) {
            $courseIds = $myCourses->pluck('id');

            $allPostTests = PostTest::whereIn('course_id', $courseIds)->orderBy('id')->get();
            $postTestIds = $allPostTests->pluck('id');

            $userResults = PostTestResult::selectRaw('post_test_id, MAX(score) as user_score')
                ->where('user_id', $user->id)
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

        // 4. Last accessed library
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
            'profile' => $profile,
            'provinces' => $provinces,
            'stats' => $stats,
            'lastCourse' => $lastCourse,
            'recentCourses' => $recentCourses,
            'chartDataByCourse' => $chartDataByCourse,
            'lastLibrary' => $lastAccessedLibrary,
        ];
    }

    /**
     * Get user profile page data.
     */
    public function getProfileData(User $user): array
    {
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
            'user' => $user,
            'provinces' => $provinces,
            'certificates' => $certificates,
            'stats' => $stats,
        ];
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
            $profile->unit_kerja = $data['unit_kerja'];
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
