<?php

namespace Modules\LMS\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;
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
     * Ambil course yang diikuti user (Pengganti myCourses API)
     */
    public function myCourses(int $page = 1, int $perPage = 12, ?string $status = null): array
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $query = $user->enrolledCourses()->with('category');

            if ($status) {
                $query->wherePivot('status', $status);
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
                'auth'    => [],
            ];
        } catch (\Exception $e) {
            Log::error('CourseService::myCourses error', ['error' => $e->getMessage()]);

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
     * Ambil detail course siswa beserta relasi sections dan contents lokal
     */
    public function getCourseDetailSlug(string $slug): array
    {
        try {
            $user = Auth::user();
            $course = Course::with([
                'category',
                // Urutan topik harus konsisten karena dipakai untuk penguncian berurutan
                'sections.contents' => fn($q) => $q->orderBy('position'),
            ])->where('slug', $slug)->first();

            if (!$course) {
                return [
                    'success' => false,
                    'message' => 'Course tidak ditemukan',
                    'data'    => null,
                ];
            }

            // Ambil ID konten yang sudah diselesaikan oleh user ini
            $completedIds = \Modules\LMS\Models\StudentContentProgress::where('user_id', $user?->id)
                ->whereHas('content.section', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->pluck('section_content_id')
                ->toArray();

            $totalCompletedCount = count($completedIds);

            // Sematkan alias properti pendukung untuk Blade siswa
            $course->course_name = $course->name;
            $course->course_sections = $course->sections;
            $course->completed_count = $totalCompletedCount;

            // Sediakan alias 'section_contents' di setiap section beserta status is_completed untuk setiap materinya
            $mappedSections = [];
            foreach ($course->sections as $section) {
                $sectionArray = $section->toArray();
                $sectionContents = [];
                $sectionCompletedCount = 0;

                if ($section->contents) {
                    foreach ($section->contents as $content) {
                        $isCompleted = in_array($content->id, $completedIds);
                        if ($isCompleted) {
                            $sectionCompletedCount++;
                        }

                        $sectionContents[] = [
                            'id'           => $content->id,
                            'name'         => $content->name,
                            'video_url'    => $content->video,
                            'document_url' => $content->document_url,
                            'is_completed' => $isCompleted,
                        ];
                    }
                }

                $sectionArray['section_contents'] = $sectionContents;
                $sectionArray['contents'] = $sectionContents;
                $sectionArray['completed_count'] = $sectionCompletedCount;
                $mappedSections[] = (object) $sectionArray;
            }

            $course->sections = collect($mappedSections);
            $course->course_sections = collect($mappedSections);

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
                // 4 Warna pilihan utama sesuai palet chart
                $colors = [
                    '13416B', // Navy
                    '547996', // Slate Blue
                    '8BB1CC', // Light Blue
                    '79A736', // Muted Green
                ];

                // Gunakan warna pilihan dari user jika ada, jika tidak pilih secara acak
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
                'description' => $data['description'],
                'thumbnail'   => $thumbnailPath,
            ]);

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
                // Jika tidak upload file baru tapi mengubah warna thumbnail otomatisnya
                $rawName = $data['name'] ?? $course->name;
                $words = explode(' ', trim($rawName));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                $encodedInitials = urlencode($initials);

                $thumbnailPath = "https://ui-avatars.com/api/?name={$encodedInitials}&background={$data['bg_color']}&color=fff&size=512&bold=true&length=2";
            }

            $course->update([
                'category_id' => $data['category_id'],
                'name'        => $data['name'],
                'description' => $data['description'],
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
     * Ambil detail course berdasarkan slug (Database Lokal)
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
            $courseData['course_sections'] = $courseData['sections'] ?? [];

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

    /**
     * Ambil semua kursus untuk halaman Katalog (Mengecualikan yang sudah diikuti user)
     */
    public function getCatalogCourses(int $perPage = 12, ?string $search = null, ?array $categoryIds = null)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $enrolledCourseIds = $user ? $user->enrolledCourses()->pluck('courses.id')->toArray() : [];

        return Course::with([
                'category',
                // Kurikulum ringkas untuk modal "ringkasan sebelum daftar" (hanya kolom yang dibutuhkan)
                'sections.contents' => fn($q) => $q
                    ->select('id', 'course_section_id', 'name', 'position', 'video', 'document')
                    ->orderBy('position'),
            ])
            ->withCount(['sections', 'students'])
            ->when($search, fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(!empty($categoryIds), fn($query) => $query->whereIn('category_id', $categoryIds))
            ->whereNotIn('id', $enrolledCourseIds)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mendaftarkan (Enroll) user ke dalam kursus
     */
    public function enrollUser(string $slug): array
    {
        try {
            $course = Course::where('slug', $slug)->first();

            if (!$course) {
                return ['success' => false, 'message' => 'Kursus tidak ditemukan.'];
            }

            // PERBAIKAN 1: Tambahkan PHPDoc agar Intelephense mengenali relasi enrolledCourses
            // PERBAIKAN 2: Sederhanakan pemanggilan namespace menjadi Auth::user()
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Cek apakah user sudah terdaftar
            if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
                return ['success' => false, 'message' => 'Anda sudah terdaftar di kursus ini.'];
            }

            // Daftarkan ke tabel pivot
            $user->enrolledCourses()->attach($course->id, [
                'status'     => 'enrolled',
                'progress'   => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['success' => true, 'message' => 'Berhasil mendaftar kursus! Selamat belajar.'];
        } catch (\Exception $e) {
            // PERBAIKAN 3: Sederhanakan pemanggilan namespace menjadi Log::error
            Log::error('Enrollment error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat mendaftar.'];
        }
    }
}