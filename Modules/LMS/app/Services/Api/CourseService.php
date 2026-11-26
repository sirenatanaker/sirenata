<?php

namespace Modules\LMS\Services\Api;

use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;

class CourseService
{
    public function queryCourses(
        ?string $search = null,
    ) {
        return Course::with(['benefits', 'testimonis', 'sections', 'sections.contents'])
                ->withCount(['benefits', 'students', 'sections'])
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest();
    }
    

    public function queryCourseDetail(string $slug) {
        return Course::with(['benefits', 'testimonis', 'sections', 'sections.contents'])
            ->withCount('students')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function findBySlugOrFail(string $slug): Course
    {
        return Course::where('slug', $slug)->firstOrFail();
    }

    public function findBySlug(string $slug): ?Course
    {
        return Course::where('slug', $slug)->first();
    }

    public function CourseStore(array $data) {

        $documentPath = null;
        if (isset($data['thumbnail'])) {
            $documentPath = $data['thumbnail']->store('courses/thumbnails', 'public');
        }
        return Course::create([
            'name' => $data['name'],
            'thumbnail' => $documentPath,
            'description' => $data['description'],
        ]);
    }

    public function CourseUpdate( array $data, Course $course) {
        $documentPath = $course->thumbnail;

        if (isset($data['thumbnail'])) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $documentPath = $data['thumbnail']->store('courses/thumbnails', 'public');
        }
        return $course->update([
                'name'        => $data['name'] ?? $course->name,
                'thumbnail'   => $documentPath,
                'description' => $data['description'] ?? $course->description,
        ]);
    }

    public function CourseDelete(Course $course) {
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        return $course->delete();
    }
}
