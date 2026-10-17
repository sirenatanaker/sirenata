<?php

namespace Modules\Dashboard\Services;

use App\Models\User;
use Modules\MasterData\Models\Institution;
use Spatie\Activitylog\Models\Activity;

class SuperAdminDashboardService
{
    /**
     * Get aggregated data for Super Admin Dashboard.
     */
    public function getDashboardData(User $user): array
    {
        $totalUser = User::role('user')->count();
        $totalAdminPusat = User::role('admin-pusat')->count();
        $totalAdminProvinsi = User::role('admin-province')->count();
        $totalAdminKabKota = User::role('admin-kab-kota')->count();

        $totalLembaga = Institution::where('type', 'pusat')->count();
        $totalInstansi = Institution::where('type', 'daerah')->count();

        $totalCourses = 0;
        if (class_exists(\Modules\LMS\Models\Course::class)) {
            $totalCourses = \Modules\LMS\Models\Course::count();
        }

        $recentActivities = collect();
        if (class_exists(Activity::class)) {
            try {
                $recentActivities = Activity::with('causer')->latest()->take(5)->get();
            } catch (\Exception $e) {
                // Keep empty collection
            }
        }

        return [
            'user' => $user,
            'totalUser' => $totalUser,
            'totalAdminPusat' => $totalAdminPusat,
            'totalAdminProvinsi' => $totalAdminProvinsi,
            'totalAdminKabKota' => $totalAdminKabKota,
            'totalLembaga' => $totalLembaga,
            'totalInstansi' => $totalInstansi,
            'totalCourses' => $totalCourses,
            'recentActivities' => $recentActivities,
        ];
    }
}
