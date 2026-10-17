<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Exports\RekapUserCourseInstansiExport;
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

    public function pusat(Request $request): View
    {
        $data = $this->rekapitulasiService->paginateFilteredRekapitulasiPusat(
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
        );

        return view('lms::admin-pusat.sdm.rekapitulasi-pusat', compact('data'));
    }

    public function pusatUsers(Request $request, string $instansi): View
    {
        $courses = $this->courseService->getCoursesForFilter();
        $data = $this->courseService->paginateCourseEnrollmentsByInstansi(
            instansi: $instansi,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
            courseId: $request->string('course_id')->toString(),
        );

        return view('lms::admin-pusat.sdm.rekapitulasi-pusat-users', compact('data', 'instansi', 'courses'));
    }

    public function exportPusatUsers(Request $request, string $instansi): BinaryFileResponse
    {
        $filename = 'rekapitulasi-pusat-' . str($instansi)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapUserCourseInstansiExport(
                instansi: $instansi,
                courseService: $this->courseService,
                courseId: $request->string('course_id')->toString() ?: null,
                search: $request->string('search')->toString() ?: null,
            ),
            $filename
        );
    }

    public function index(Request $request): View
    {
        $data = $this->rekapitulasiService->paginateFilteredRekapitulasiProvince(
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
        );

        return view('lms::admin-pusat.sdm.rekapitulasi-index', compact('data'));
    }

    public function kabKota(Request $request, string $provinceCode): View
    {
        $data = $this->rekapitulasiService->paginateFilteredRekapitulasiRegency(
            provinceCode: $provinceCode,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
        );

        return view('lms::admin-pusat.sdm.rekapitulasi-kab-kota', compact('data', 'provinceCode'));
    }

    public function rekapUserProvince(Request $request, string $provinceCode): View
    {
        $provinceName = $this->rekapitulasiService->getProvinceByCode($provinceCode)?->name ?? '';
        $courses = $this->courseService->getCoursesForFilter();
        $data = $this->courseService->paginateCourseEnrollmentsByProvince(
            provinceCode: $provinceCode,
            search: $request->string('search')->toString(),
            limit: $request->integer('per_page', 10),
            courseId: $request->string('course_id')->toString(),
        );

        return view('lms::admin-pusat.sdm.rekapitulasi-user-province', compact('data', 'provinceCode', 'provinceName', 'courses'));
    }

    public function exportRekapUserProvince(Request $request, string $provinceCode): BinaryFileResponse
    {
        $provinceName = $this->rekapitulasiService->getProvinceByCode($provinceCode)?->name ?? '';
        $filename = 'rekapitulasi-provinsi-' . str($provinceName)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapUserCourseProvinceExport(
                provinceName: $provinceName,
                courseService: $this->courseService,
                provinceCode: $provinceCode,
                courseId: $request->string('course_id')->toString() ?: null,
                search: $request->string('search')->toString() ?: null,
            ),
            $filename
        );
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

        return view('lms::admin-pusat.sdm.rekapitulasi-user-kab-kota', compact('data', 'regencyCode', 'regency', 'courses'));
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
}
