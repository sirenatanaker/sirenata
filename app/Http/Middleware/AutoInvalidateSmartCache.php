<?php

namespace App\Http\Middleware;

use App\Support\SmartCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Secara otomatis menginvalidasi SmartCache domain terkait setiap kali ada request
 * mutasi data (POST/PUT/PATCH/DELETE) yang berhasil, sehingga halaman berat selalu
 * memuat data terbaru begitu ada perubahan data tanpa perlu menunggu TTL habis.
 */
class AutoInvalidateSmartCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        // Hanya invalidasi jika request tidak gagal total (status < 400)
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        $path = strtolower($request->path());

        // Lewati aksi autentikasi/pencarian murni yang tidak mengubah data laporan/LMS
        if (
            in_array($routeName, ['login', 'logout', 'register', 'password.email', 'password.update'], true)
            || str_contains($path, 'search')
            || str_contains($path, 'notifications')
        ) {
            return $response;
        }

        $target = strtolower($routeName . ' ' . $path . ' ' . (string) $request->headers->get('referer', ''));
        $domainsToBump = [];

        // Selalu invalidasi cache personal milik user yang melakukan perubahan data
        if ($request->user()?->id) {
            $domainsToBump[] = SmartCache::userDomain($request->user()->id);
        }

        if (
            str_contains($target, 'rtk')
            || str_contains($target, 'project')
            || str_contains($target, 'survey')
            || str_contains($target, 'pemanfaatan')
        ) {
            $domainsToBump[] = SmartCache::DOMAIN_DASHBOARD_RTK;
        }

        if (str_contains($target, 'library')) {
            $domainsToBump[] = SmartCache::DOMAIN_LMS_LIBRARY;
            if (str_contains($target, 'admin-pusat')) {
                $domainsToBump[] = SmartCache::DOMAIN_LMS_COURSES;
            }
        }

        if (
            str_contains($target, 'bimtek-event')
            || str_contains($target, 'attendance')
            || str_contains($target, 'post-test')
            || str_contains($target, 'evaluation')
            || str_contains($target, 'evaluasi')
            || str_contains($target, 'course')
            || str_contains($target, 'katalog')
            || str_contains($target, 'catalog')
            || str_contains($target, 'enroll')
            || str_contains($target, 'section')
            || str_contains($target, 'categor')
            || str_contains($target, 'certificate')
            || str_contains($target, 'zoom')
            || str_contains($target, 'submit')
        ) {
            // Mutasi LMS oleh admin atau mutasi peserta yang berdampak ke rekap admin
            // (enroll, presensi zoom, kumpul tugas/esai/tes, evaluasi, sertifikat)
            $isPureUserContentTick = str_starts_with($path, 'user/')
                && (str_contains($path, 'complete') || str_contains($path, 'track'));

            if (!$isPureUserContentTick) {
                $domainsToBump[] = SmartCache::DOMAIN_LMS_EVENTS;
                $domainsToBump[] = SmartCache::DOMAIN_LMS_COURSES;
                $domainsToBump[] = SmartCache::DOMAIN_LMS_SUBMISSIONS;
            }
        }

        if (!empty($domainsToBump)) {
            SmartCache::bump($domainsToBump);
        } elseif (str_contains($target, 'admin-pusat') || str_contains($target, 'livewire/update')) {
            // Fallback aman untuk mutasi Livewire / Admin Pusat lainnya
            SmartCache::bumpAll();
        }

        return $response;
    }
}
