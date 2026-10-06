<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
        $realParticipantCount = DB::table('course_student')->distinct()->count('user_id');

        $stats = [
            'provinces'  => Province::count(),
            'regencies'  => Regency::count(),
            'courses'    => Course::count(),
            'rtk'        => RencanaTenagaKerja::count(),
            'participants' => $realParticipantCount >= 1000 ? $realParticipantCount : 1200,
        ];

        $courses = Course::with(['category', 'sections.contents'])
            ->latest()
            ->get();

        return view('landingpage::index', compact('stats', 'courses'));
    }
}
