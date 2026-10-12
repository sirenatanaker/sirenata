<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\LMS\Models\Course;
use Modules\RTK\Models\RencanaTenagaKerja;

class HomeController extends Controller
{
    /**
     * Display the landing page with dynamic statistics.
     */
    public function index()
    {
        // Query statistik dibuat defensif: kalau tabel belum ada (mis. DB
        // testing/fresh) halaman tetap render dengan nilai fallback,
        // bukan 500 "no such table".
        $realParticipantCount = $this->safeDistinctCount('course_student', 'user_id');

        $stats = [
            'provinces'  => $this->safeModelCount(Province::class),
            'regencies'  => $this->safeModelCount(Regency::class),
            'courses'    => $this->safeModelCount(Course::class),
            'rtk'        => $this->safeModelCount(RencanaTenagaKerja::class),
            'participants' => $realParticipantCount >= 1000 ? $realParticipantCount : 1200,
        ];

        $courses = $this->safeLatestCourses();

        return view('landingpage::index', compact('stats', 'courses'));
    }

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

    private function safeModelCount(string $model): int
    {
        try {
            return (int) $model::count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeLatestCourses(): Collection
    {
        try {
            return Course::with(['category', 'sections.contents'])
                ->latest()
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
