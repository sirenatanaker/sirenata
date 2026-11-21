<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Versioned smart cache helper compatible with all Laravel cache drivers
 * (database, file, redis) without requiring tag-capable stores.
 *
 * When any domain is bumped via bump(), all cached queries belonging to that
 * domain immediately miss and regenerate fresh data on the next request.
 */
class SmartCache
{
    public const DOMAIN_DASHBOARD_RTK   = 'dashboard_rtk';
    public const DOMAIN_LMS_EVENTS      = 'lms_events';
    public const DOMAIN_LMS_COURSES     = 'lms_courses';
    public const DOMAIN_LMS_SUBMISSIONS = 'lms_submissions';
    public const DOMAIN_LMS_LIBRARY     = 'lms_library';

    public const ALL_DOMAINS = [
        self::DOMAIN_DASHBOARD_RTK,
        self::DOMAIN_LMS_EVENTS,
        self::DOMAIN_LMS_COURSES,
        self::DOMAIN_LMS_SUBMISSIONS,
        self::DOMAIN_LMS_LIBRARY,
    ];

    /**
     * Domain khusus per pengguna agar mutasi data satu peserta tidak
     * menghapus cache milik peserta lain.
     */
    public static function userDomain(int|string|null $userId): string
    {
        return 'user_' . ($userId ?: 'guest');
    }

    /**
     * Invalidasi cache khusus milik satu pengguna secara instan.
     */
    public static function bumpUser(int|string|null $userId): void
    {
        if ($userId !== null && $userId !== '') {
            self::bump(self::userDomain($userId));
        }
    }

    private const VERSION_TTL_SECONDS = 2592000; // 30 hari

    /**
     * Ambil versi aktif domain cache (default '1').
     */
    public static function version(string $domain): string
    {
        try {
            $key = "smart_cache:v:{$domain}";
            $ver = Cache::get($key);
            if ($ver === null) {
                $ver = '1';
                Cache::put($key, $ver, self::VERSION_TTL_SECONDS);
            }

            return (string) $ver;
        } catch (Throwable) {
            return '0';
        }
    }

    /**
     * Simpan/ambil hasil query dengan kunci berversi otomatis.
     * Jika cache store mengalami gangguan, fallback langsung mengeksekusi callback.
     *
     * @param  string|array<int, string>  $domains
     */
    public static function remember(string|array $domains, string $key, int $ttlSeconds, Closure $callback): mixed
    {
        $domainList = array_values(array_unique((array) $domains));
        sort($domainList);

        try {
            $verParts = [];
            foreach ($domainList as $domain) {
                $verParts[] = "{$domain}=" . self::version($domain);
            }

            $signature = implode('|', $verParts) . '::' . $key;
            $cacheKey = 'smart_cache:d:' . md5($signature);

            return Cache::remember($cacheKey, $ttlSeconds, $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    /**
     * Naikkan versi satu atau beberapa domain untuk menginvalidasi cache terkait secara instan.
     *
     * @param  string|array<int, string>  $domains
     */
    public static function bump(string|array $domains): void
    {
        $domainList = array_values(array_unique((array) $domains));
        $newVersion = (string) (int) round(microtime(true) * 10000);

        foreach ($domainList as $domain) {
            try {
                Cache::put("smart_cache:v:{$domain}", $newVersion, self::VERSION_TTL_SECONDS);
            } catch (Throwable) {
                // Abaikan bila cache store sementara tidak tersedia
            }
        }
    }

    /**
     * Invalidasi seluruh domain smart cache.
     */
    public static function bumpAll(): void
    {
        self::bump(self::ALL_DOMAINS);
    }
}
