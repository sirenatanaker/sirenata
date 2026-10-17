<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Project\Enums\ProjectType;
use Modules\Project\Models\Project;
use Modules\RTK\Enums\RTKStatusVerification;
use Modules\RTK\Enums\StatusDocument;
use Modules\RTK\Enums\TypeRtk;
use Modules\RTK\Models\RencanaTenagaKerja;
use Modules\User\Models\UserScope;

class AdminKabKotaDashboardService
{
    /**
     * Get aggregated dashboard data for Admin Kab/Kota.
     */
    public function getDashboardData(User $user, ?int $selectedYear = null): array
    {
        $userScope = UserScope::where('user_id', $user->id)->first();
        $regencyCode = $userScope ? $userScope->regency_code : null;

        // 1. DATA E-LEARNING (SDM & MODUL)
        $baseUserQuery = DB::table('user_scopes')
            ->join('users', 'user_scopes.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_uuid')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.uuid')
            ->where('roles.name', 'user')
            ->where('user_scopes.regency_code', $regencyCode);

        $currentYear = (int) date('Y');
        $selectedYear = $selectedYear ?: $currentYear;

        $years = [];
        for ($y = $currentYear; $y >= $currentYear - 15; $y--) {
            $years[] = $y;
        }

        $startYear = $selectedYear - 4;
        $endYear = $selectedYear;

        $usersByYear = (clone $baseUserQuery)
            ->whereYear('users.created_at', '>=', $startYear)
            ->whereYear('users.created_at', '<=', $endYear)
            ->select(DB::raw('YEAR(users.created_at) as year'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('YEAR(users.created_at)'))
            ->pluck('total', 'year');

        $sdmPerTahun = [];
        for ($y = $startYear; $y <= $endYear; $y++) {
            $sdmPerTahun[$y] = $usersByYear->get($y, 0);
        }

        $genders = (clone $baseUserQuery)
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('user_profiles.gender', DB::raw('count(*) as total'))
            ->groupBy('user_profiles.gender')
            ->pluck('total', 'gender');

        $genderMale = $genders->get('male', 0);
        $genderFemale = $genders->get('female', 0);

        $courses = (clone $baseUserQuery)
            ->join('course_student', 'users.id', '=', 'course_student.user_id')
            ->join('courses', 'course_student.course_id', '=', 'courses.id')
            ->select('courses.name as course_name', DB::raw('count(course_student.user_id) as total'))
            ->groupBy('courses.id', 'courses.name')
            ->pluck('total', 'course_name');

        // 2. DATA RTK ACUAN DAERAH
        $rtkAcuan = RencanaTenagaKerja::where('type', TypeRtk::KAB_KOTA->value)
            ->where('regency_code', $regencyCode)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->first();

        $rtkStatusInfo = [
            'hasAcuan' => (bool) $rtkAcuan,
            'isApproved' => false,
            'isPending' => false,
            'isValid' => false,
            'isRejected' => false,
            'statusBadgeText' => 'Belum Ada Acuan',
            'statusBadgeColor' => 'bg-slate-400 text-white',
            'sisaTahun' => null,
            'sisaWaktuTeks' => '-',
            'sisaWaktuColor' => 'text-slate-400',
            'isExpiringSoon' => false,
        ];

        if ($rtkAcuan) {
            $verifStatus = $rtkAcuan->status_verification;
            $docStatus = $rtkAcuan->status_document;

            $sisaTahun = (int) $rtkAcuan->end_date - (int) date('Y');
            $rtkStatusInfo['sisaTahun'] = $sisaTahun;

            if ($verifStatus === RTKStatusVerification::REJECTED) {
                $rtkStatusInfo['isRejected'] = true;
                $rtkStatusInfo['statusBadgeText'] = 'Ditolak / Perlu Revisi';
                $rtkStatusInfo['statusBadgeColor'] = 'bg-rose-600 text-white';
            } elseif ($verifStatus === RTKStatusVerification::PENDING) {
                $rtkStatusInfo['isPending'] = true;
                $rtkStatusInfo['statusBadgeText'] = 'Menunggu persetujuan';
                $rtkStatusInfo['statusBadgeColor'] = 'bg-amber-500 text-white';
            } elseif ($verifStatus === RTKStatusVerification::APPROVED) {
                $rtkStatusInfo['isApproved'] = true;
                if ($docStatus === StatusDocument::VALID) {
                    $rtkStatusInfo['isValid'] = true;
                    $rtkStatusInfo['statusBadgeText'] = 'Disahkan & Berlaku';
                    $rtkStatusInfo['statusBadgeColor'] = 'bg-emerald-600 text-white';
                } elseif ($docStatus === StatusDocument::EXPIRED) {
                    $rtkStatusInfo['statusBadgeText'] = 'Kedaluwarsa';
                    $rtkStatusInfo['statusBadgeColor'] = 'bg-rose-600 text-white';
                } else {
                    $rtkStatusInfo['statusBadgeText'] = 'Verifikasi Lolos';
                    $rtkStatusInfo['statusBadgeColor'] = 'bg-blue-600 text-white';
                }
            }

            if ($rtkStatusInfo['isValid']) {
                if ($sisaTahun > 1) {
                    $rtkStatusInfo['sisaWaktuTeks'] = $sisaTahun . ' Tahun';
                    $rtkStatusInfo['sisaWaktuColor'] = 'text-slate-700';
                } elseif ($sisaTahun === 1 || $sisaTahun === 0) {
                    $rtkStatusInfo['sisaWaktuTeks'] = $sisaTahun === 0 ? 'Berakhir Tahun Ini' : 'Sisa 1 Tahun';
                    $rtkStatusInfo['sisaWaktuColor'] = 'text-amber-600';
                    $rtkStatusInfo['isExpiringSoon'] = true;
                } else {
                    $rtkStatusInfo['sisaWaktuTeks'] = '0 Tahun (Kadaluarsa)';
                    $rtkStatusInfo['sisaWaktuColor'] = 'text-red-600';
                    $rtkStatusInfo['isExpiringSoon'] = true;
                }
            }
        }

        // 3. DATA PROJECT DAERAH
        $projectQuery = Project::where('type', ProjectType::KAB_KOTA->value)
            ->whereHas('leader.scopeArea', function($q) use ($regencyCode) {
                $q->where('regency_code', $regencyCode);
            });

        $totalProjects = (clone $projectQuery)->count();
        $allProjects = (clone $projectQuery)->with(['leader', 'prerequisiteCourse'])->latest()->get();

        $averageProgress = $allProjects->avg(fn($project) => $project->progress) ?? 0;

        $onProgressProjects = $allProjects->filter(function($project) {
            return $project->progress < 100;
        })->count();

        $completedProjects = $allProjects->filter(function($project) {
            return $project->progress >= 100;
        })->count();

        $recentProjects = $allProjects->take(4);

        return [
            'user' => $user,
            'sdmPerTahun' => $sdmPerTahun,
            'genderMale' => $genderMale,
            'genderFemale' => $genderFemale,
            'courses' => $courses,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'rtkAcuan' => $rtkAcuan,
            'rtkStatusInfo' => $rtkStatusInfo,
            'totalProjects' => $totalProjects,
            'averageProgress' => $averageProgress,
            'onProgressProjects' => $onProgressProjects,
            'completedProjects' => $completedProjects,
            'recentProjects' => $recentProjects,
        ];
    }
}
