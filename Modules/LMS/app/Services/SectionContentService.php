<?php

namespace Modules\LMS\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\SectionContent;

class SectionContentService
{
    public function getCreateData(string $courseSlug, string $sectionId): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $section = CourseSection::findOrFail($sectionId);

        return compact('course', 'section');
    }

    public function getCourseAndContent(string $courseSlug, string $contentId): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $content = SectionContent::with('section')->findOrFail($contentId);

        return compact('course', 'content');
    }

    public function uploadEditorVideo($videoFile): string
    {
        $path = $videoFile->store('course-videos', 'public');

        return asset('storage/' . $path);
    }

    public function storeSectionContent(array $data, $file = null): array
    {
        try {
            $documentPath = null;

            if ($file) {
                $documentPath = $file->store('course-documents', 'public');
            }

            $lastPosition = SectionContent::where('course_section_id', $data['course_section_id'])->max('position');
            $newPosition = $lastPosition ? $lastPosition + 1 : 1;

            $content = SectionContent::create([
                'course_section_id' => $data['course_section_id'],
                'name'              => $data['name'],
                'video'             => $data['video'] ?? null,
                'content_text'      => $data['content_text'] ?? null,
                'document'          => $documentPath,
                'position'          => $newPosition,
            ]);

            return [
                'success' => true,
                'message' => 'Materi berhasil ditambahkan',
                'data'    => $content,
            ];
        } catch (\Exception $e) {
            Log::error('SectionContentService::storeSectionContent error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    public function updateContent(string $contentId, array $data, $file = null): array
    {
        try {
            $content = SectionContent::findOrFail($contentId);
            $documentPath = $content->document;

            if ($file) {
                if ($documentPath && Storage::disk('public')->exists($documentPath)) {
                    Storage::disk('public')->delete($documentPath);
                }

                $documentPath = $file->store('course-documents', 'public');
            }

            $content->update([
                'name'         => $data['name'],
                'video'        => $data['video'] ?? null,
                'content_text' => $data['content_text'] ?? null,
                'document'     => $documentPath,
            ]);

            return [
                'success' => true,
                'message' => 'Materi berhasil diperbarui',
            ];
        } catch (\Exception $e) {
            Log::error('SectionContentService::updateContent error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Kesalahan sistem: ' . $e->getMessage(),
            ];
        }
    }

    public function deleteContent(string $contentId): array
    {
        try {
            $content = SectionContent::findOrFail($contentId);

            if ($content->document && Storage::disk('public')->exists($content->document)) {
                Storage::disk('public')->delete($content->document);
            }

            $content->delete();

            return [
                'success' => true,
                'message' => 'Materi berhasil dihapus',
            ];
        } catch (\Exception $e) {
            Log::error('SectionContentService::deleteContent error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Kesalahan sistem: ' . $e->getMessage(),
            ];
        }
    }
}