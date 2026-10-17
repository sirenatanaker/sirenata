<?php

namespace Modules\LMS\Services\Api;

use Modules\LMS\Models\Course;
use Modules\LMS\Models\SectionContent;
use Modules\LMS\Models\StudentContentProgress;

class CourseProgressService
{
    /**
     * Tandai konten sebagai selesai ditonton
     * dan hitung ulang progress course otomatis
     */
    public function completeContent(SectionContent $content, ?int $userId = null): array
    {
        $userId   = $userId ?? auth()->id();
        $courseId = $content->section->course_id;
        $course   = Course::findOrFail($courseId);

        // Cek apakah user sudah enroll di course ini
        $enrollment = $course->students()
            ->wherePivot('user_id', $userId)
            ->first();

        if (! $enrollment) {
            return [
                'success' => false,
                'code'    => 403,
                'message' => 'Kamu belum terdaftar di course ini',
            ];
        }

        // Kalau course sudah completed, tidak perlu update
        if ($enrollment->pivot->status === 'completed') {
            return [
                'success' => false,
                'code'    => 422,
                'message' => 'Course ini sudah selesai',
            ];
        }

        // Simpan progress konten — ignore kalau sudah ada
        StudentContentProgress::firstOrCreate(
            [
                'user_id'            => $userId,
                'section_content_id' => $content->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        // Hitung ulang progress
        $progress = $this->calculateProgress($userId, $courseId);

        // Update pivot course_student
        $pivotData = [
            'progress' => $progress,
            'status'   => $progress >= 100 ? 'completed' : 'in_progress',
        ];

        if ($progress >= 100) {
            $pivotData['completed_at'] = now();
        }

        $course->students()->updateExistingPivot($userId, $pivotData);

        return [
            'success'      => true,
            'code'         => 200,
            'message'      => $progress >= 100
                ? 'Selamat! Kamu telah menyelesaikan course ini'
                : 'Progress berhasil diupdate',
            'progress'     => $progress,
            'status'       => $pivotData['status'],
            'is_completed' => $progress >= 100,
        ];
    }

    /**
     * Hitung progress: (konten selesai / total konten) * 100
     */
    public function calculateProgress(string $userId, string $courseId): int
    {
        // Total konten di semua section dalam course
        $totalContents = SectionContent::whereHas('section', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->count();

        if ($totalContents === 0) return 0;

        // Konten yang sudah diselesaikan user di course ini
        $completedContents = StudentContentProgress::where('user_id', $userId)
            ->whereHas('content.section', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->count();

        return (int) round(($completedContents / $totalContents) * 100);
    }

    /**
     * Ambil list content_id yang sudah selesai di course tertentu
     * Dipakai untuk highlight konten yang sudah ditonton di frontend
     */
    public function getCompletedContentIds(string $userId, string $courseId): array
    {
        return StudentContentProgress::where('user_id', $userId)
            ->whereHas('content.section', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->pluck('section_content_id')
            ->toArray();
    }

    /**
     * Detail progress course — untuk halaman course detail
     */
    public function getCourseProgress(string $userId, string $slug): array
    {
        $course = Course::where('slug', $slug)
            ->with(['sections.contents'])
            ->firstOrFail();

        $enrollment = $course->students()
            ->wherePivot('user_id', $userId)
            ->first();

        if (! $enrollment) {
            return [
                'success' => false,
                'code'    => 403,
                'message' => 'Kamu belum terdaftar di course ini',
            ];
        }

        $completedIds = $this->getCompletedContentIds($userId, $course->id);
        $totalContents = 0;
        $sections = [];

        foreach ($course->sections as $section) {
            $sectionContents = [];

            foreach ($section->contents as $content) {
                $totalContents++;
                $isCompleted = in_array($content->id, $completedIds);

                $sectionContents[] = [
                    'id'           => $content->id,
                    'name'         => $content->name,
                    'video_url'         => $content->video,
                    'document_url'     => $content->document_url,
                    'is_completed' => $isCompleted,
                ];
            }

            $sections[] = [
                'id'               => $section->id,
                'name'             => $section->name,
                'position'         => $section->position,
                'section_contents' => $sectionContents,
                'completed_count'  => count(array_filter($sectionContents, fn($c) => $c['is_completed'])),
                // 'total_count'      => count($sectionContents),
            ];
        }

        return [
            'success' => true,
            'data'    => [
                'course_id'       => $course->id,
                'course_name'     => $course->name,
                'progress'        => $enrollment->pivot->progress,
                'status'          => $enrollment->pivot->status,
                'completed_count' => count($completedIds),
                'certificate_code' => $enrollment->pivot->certificate_code,
                'certificate_file' => $enrollment->pivot->certificate_file ? \Illuminate\Support\Facades\Storage::disk('public')->url($enrollment->pivot->certificate_file) : null,
                'certificate_issued_at' => $enrollment->pivot->certificate_issued_at,
                // 'total_count'     => $totalContents,
                'sections'        => $sections,
            ],
        ];
    }
}
