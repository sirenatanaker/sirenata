<?php

namespace Modules\LMS\Services\Api;

use Modules\LMS\Models\Course;

class CourseStudentService
{
    public function getStudents(string $slug, int $perPage = 20)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $students = $course->students()
            ->select('users.id', 'users.name', 'users.email')
            ->paginate($perPage);

        return $students;
    }

    /**
     * Enroll user yang sedang login ke course
     */
    public function enroll(string $slug): array
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        $alreadyEnrolled = $course->students()
            ->wherePivot('user_id', $userId)
            ->exists();

        if ($alreadyEnrolled) {
            return [
                'success' => false,
                'code'    => 422,
                'message' => 'Kamu sudah terdaftar di course ini',
            ];
        }

        $course->students()->attach($userId, [
            'status'   => 'enrolled',
            'progress' => 0,
        ]);

        return [
            'enrolled' => true,
            'code'    => 201,
            'message'  => 'Berhasil enroll ke course',
        ];
    }


    /**
     * Update status enrollment student
     */
    public function updateStatus(string $slug, string $userId, string $status): array
    {
        $course  = Course::where('slug', $slug)->firstOrFail();
        $student = $course->students()
            ->wherePivot('user_id', $userId)
            ->first();

        if (!$student) {
            return [
                'success' => false,
                'code'    => 404,
                'message' => 'Student tidak ditemukan di course ini',
            ];
        }

        if ($student->pivot->status === 'completed' && $student->pivot->progress === 100) {
            return [
                'success' => false,
                'code'    => 422,
                'message' => 'Status student yang sudah completed tidak bisa diubah',
            ];
        }

        $pivotData = ['status' => $status];

        if ($status === 'completed') {
            $pivotData['completed_at'] = now();
            $pivotData['progress']     = 100;
        }

        $course->students()->updateExistingPivot($userId, $pivotData);

        return [
            'success' => true,
            'code'    => 200,
            'message' => 'Status student berhasil diupdate',
        ];
    }


    /**
     * Course yang diikuti user yang sedang login
     */
    // public function myCourses(int $perPage = 12)
    // {
    //     $courses = auth()->user()
    //         ->enrolledCourses()
    //         ->with(['category', 'benefits', 'testimonis'])
    //         ->paginate($perPage);

    //     return $courses;
    // }

    public function myCourses(int $perPage = 12, string $status = 'semua')
    {
        $query = auth()->user()->enrolledCourses()
            ->with(['benefits', 'testimonis']);

        if ($status === 'completed') {
            $query->wherePivot('status', 'completed');
        } elseif ($status === 'in_progress') {
            $query->wherePivotIn('status', ['enrolled', 'in_progress']);
        }

        return $query->latest('course_student.created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Unenroll user yang sedang login dari course
     */
    public function unenroll(string $slug): array
    {
        $course  = Course::where('slug', $slug)->firstOrFail();
        $userId  = auth()->id();
        $student = $course->students()
            ->wherePivot('user_id', $userId)
            ->first();

        if (! $student) {
            return [
                'success' => false,
                'code'    => 404,
                'message' => 'Kamu tidak terdaftar di course ini',
            ];
        }

        if ($student->pivot->status === 'completed') {
            return [
                'success' => false,
                'code'    => 422,
                'message' => 'Course yang sudah diselesaikan tidak bisa di-unenroll',
            ];
        }

        $course->students()->detach($userId);

        return [
            'success' => true,
            'code'    => 200,
            'message' => 'Berhasil keluar dari course',
        ];
    }
}
