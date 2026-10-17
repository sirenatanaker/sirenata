<?php

namespace Modules\LMS\Http\Controllers\AdminKabKota;

use App\Exports\RekapUserCourseRegencyExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\LMS\Services\CourseService;
use Modules\LMS\Services\RekapitulasiService;

class RekapitulasiController extends Controller
{
    public function __construct(
        private RekapitulasiService $rekapitulasiService,
        private CourseService $courseService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasCompleteScope()) {
            abort(403, 'Admin provinsi belum memiliki wilayah.');
        }

        $regencyCode = $user->scopeArea?->regency_code;
        $regency = $this->rekapitulasiService->getRegencyByCode($regencyCode);
        $courses = $this->courseService->getCoursesForFilter();
        $data = $this->courseService->paginateCourseEnrollmentsByRegency(
            regencyCode: $regencyCode,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
            courseId: $request->string('course_id')->toString(),
        );

        return view('lms::admin-kab-kota.sdm.rekapitulasi-index', compact('data', 'regencyCode', 'regency', 'courses'));
    }

    public function exportRekapUserRegency(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasCompleteScope()) {
            abort(403, 'Admin provinsi belum memiliki wilayah.');
        }

        $regencyCode = $user->scopeArea?->regency_code;
        $regencyName = $this->rekapitulasiService->getRegencyByCode($regencyCode)?->name ?? '';
        $filename = 'rekapitulasi-' . str($regencyName)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapUserCourseRegencyExport(
                regencyName: $regencyName,
                courseService: $this->courseService,
                regencyCode: $regencyCode,
                courseId: $request->string('course_id')->toString() ?: null,
                search: $request->string('search')->toString() ?: null,
            ),
            $filename
        );
    }
}
