<?php

namespace Modules\LMS\Http\Controllers\AdminProvince;

use App\Exports\RekapUserCourseProvinceExport;
use App\Exports\RekapUserCourseRegencyExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\LMS\Services\CourseService;
use Modules\LMS\Services\RekapitulasiService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapitulasiController extends Controller
{
    public function __construct(
        private RekapitulasiService $rekapitulasiService,
        private CourseService $courseService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user->hasCompleteScope()) {
            abort(403, 'Admin provinsi belum memiliki wilayah.');
        }

        $data = $this->rekapitulasiService->paginateFilteredRekapitulasiRegency(
            provinceCode: $user->scopeArea->province_code,
            search: $request->search,
            limit: $request->per_page ?? 10,
        );

        return view('lms::admin-province.sdm.rekapitulasi-kab-kota', compact('data', 'user'));
    }

    public function rekapUserKabKota(Request $request, string $regencyCode): View
    {
        $regency = $this->rekapitulasiService->getRegencyByCode($regencyCode);
        $courses = $this->courseService->getCoursesForFilter();
        $data = $this->courseService->paginateCourseEnrollmentsByRegency(
            regencyCode: $regencyCode,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
            courseId: $request->string('course_id')->toString(),
        );

        return view('lms::admin-province.sdm.rekapitulasi-user-kab-kota', compact('data', 'regencyCode', 'regency', 'courses'));
    }

    public function exportRekapUserRegency(Request $request, string $regencyCode): BinaryFileResponse
    {
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

    public function rekapUserProvince(Request $request): View
    {
        $user = $request->user();
        if (!$user->hasCompleteScope()) {
            abort(403, 'Admin provinsi belum memiliki wilayah.');
        }

        $provinceCode = $user->scopeArea?->province_code;
        $provinceName = $this->rekapitulasiService->getProvinceByCode($provinceCode)?->name ?? '';
        $courses = $this->courseService->getCoursesForFilter();
        $data = $this->courseService->paginateCourseEnrollmentsByProvince(
            provinceCode: $provinceCode,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
            courseId: $request->string('course_id')->toString(),
        );

        return view('lms::admin-province.sdm.rekapitulasi-user-province', compact('data', 'provinceCode', 'provinceName', 'courses'));
    }

    public function exportRekapUserProvince(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        if (!$user->hasCompleteScope()) {
            abort(403, 'Admin provinsi belum memiliki wilayah.');
        }

        $provinceCode = $user->scopeArea?->province_code;
        $provinceName = $this->rekapitulasiService->getProvinceByCode($provinceCode)?->name ?? '';
        $filename = 'rekapitulasi-provinsi-' . str($provinceName)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapUserCourseProvinceExport(
                provinceName: $provinceName,
                courseService: $this->courseService,
                provinceCode: $provinceCode,
                search: $request->string('search')->toString() ?: null,
                courseId: $request->string('course_id')->toString() ?: null,
            ),
            $filename
        );
    }
}
