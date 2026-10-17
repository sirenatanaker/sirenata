<?php

namespace Modules\LMS\Http\Controllers\Api\Course;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Services\Api\CourseProgressService;

class CourseProgressController extends Controller
{
    public function __construct(
        private CourseProgressService $progressService
    ) {}
    
    /**
     * Tandai konten sebagai selesai
     *
     * content harus berdasarkan id dari section_contents.
     * Dipanggil ketika student selesai menonton video/konten.
     * Progress course akan otomatis dihitung ulang.
     * Jika semua konten selesai, status course berubah menjadi completed.
     *
     * @authenticated
     * @urlParam content string required UUID konten yang selesai ditonton. Example: uuid-xxx
     *
     * @response 200 scenario="Progress terupdate" {
     *   "status": true,
     *   "message": "Progress berhasil diupdate",
     *   "result": {
     *     "progress": 75,
     *     "status": "in_progress",
     *     "is_completed": false
     *   }
     * }
     * @response 200 scenario="Course selesai" {
     *   "status": true,
     *   "message": "Selamat! Kamu telah menyelesaikan course ini",
     *   "result": {
     *     "progress": 100,
     *     "status": "completed",
     *     "is_completed": true
     *   }
     * }
     * @response 403 scenario="Belum enroll" {
     *   "status": false,
     *   "message": "Kamu belum terdaftar di course ini"
     * }
     */
    public function complete(Request $request, SectionContent $content): JsonResponse
    {
        try {
            $result = $this->progressService->completeContent($content, $request->user()->id);

            if (! $result['success']) {
                return ResponseHelper::error(
                    message: $result['message'],
                    statusCode: $result['code']
                );
            }

            return ResponseHelper::success(
                status: true,
                message: $result['message'],
                result: [
                    'progress'     => $result['progress'],
                    'status'       => $result['status'],
                    'is_completed' => $result['is_completed'],
                ],
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: 'Gagal mengupdate progress',
                statusCode: 500
            );
        }
    }

    /**
     * Detail progress course
     *
     * Course Slug berdasarkan slug dari course yang akan di show.
     * Menampilkan semua section dan konten beserta status selesai/belum.
     * Dipakai untuk highlight konten yang sudah ditonton di halaman course detail.
     *
     * @authenticated
     * @urlParam slug string required Slug course. Example: laravel-dasar
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $result = $this->progressService->getCourseProgress($request->user()->id, $slug);

            if (! $result['success']) {
                return ResponseHelper::error(
                    message: $result['message'],
                    statusCode: $result['code']
                );
            }

            return ResponseHelper::success(
                status: true,
                message: 'Success',
                result: $result,
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                message: 'Gagal mengambil data progress',
                statusCode: 500
            );
        }
    }

    
}
