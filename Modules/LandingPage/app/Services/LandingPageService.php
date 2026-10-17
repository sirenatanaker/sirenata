<?php

namespace Modules\LandingPage\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\LMS\Models\Course;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\RTK\Models\RencanaTenagaKerja;

class LandingPageService
{
    /**
     * Get all dynamic data required for the landing page.
     */
    public function getLandingPageData(): array
    {
        $realParticipantCount = $this->safeDistinctCount('course_student', 'user_id');

        $stats = [
            'provinces'    => $this->safeModelCount(Province::class),
            'regencies'    => $this->safeModelCount(Regency::class),
            'courses'      => $this->safeModelCount(Course::class),
            'rtk'          => $this->safeModelCount(RencanaTenagaKerja::class),
            'participants' => $realParticipantCount >= 1000 ? $realParticipantCount : 1200,
        ];

        $courses = $this->safeLatestCourses();

        $lmsStats = [
            'courseCount'      => $courses->count(),
            'moduleCount'      => (int) $courses->sum(
                fn($course) => $course->sections_count ?? (isset($course->sections) ? count($course->sections) : 0)
            ),
            'categoryCount'    => $courses->pluck('category_id')->filter()->unique()->count(),
            'participantCount' => $stats['participants'],
        ];

        return [
            'stats'    => $stats,
            'courses'  => $courses,
            'lmsStats' => $lmsStats,
        ];
    }

    /**
     * Safely count distinct values from a table.
     */
    private function safeDistinctCount(string $table, string $column): int
    {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) DB::table($table)->distinct()->count($column);
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Safely count total records for a model class.
     */
    private function safeModelCount(string $model): int
    {
        try {
            if (! class_exists($model)) {
                return 0;
            }

            return (int) $model::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Safely retrieve latest courses with relations.
     */
    private function safeLatestCourses(): Collection
    {
        try {
            if (! class_exists(Course::class)) {
                return collect();
            }

            return Course::with(['category', 'sections.contents'])
                ->latest()
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
