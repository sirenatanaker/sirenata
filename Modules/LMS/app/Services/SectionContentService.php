<?php

namespace Modules\LMS\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;
use Modules\LMS\Models\Library;
use Modules\LMS\Models\SectionContent;

class SectionContentService
{
    public function getCreateData(string $courseSlug, string $sectionId): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $section = CourseSection::findOrFail($sectionId);
        $libraries = Library::with('libraryCategory')->orderBy('title', 'asc')->get();

        return compact('course', 'section', 'libraries');
    }

    public function getCourseAndContent(string $courseSlug, string $contentId): array
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $content = SectionContent::with(['section', 'documents', 'libraries.libraryCategory'])->findOrFail($contentId);
        $libraries = Library::with('libraryCategory')->orderBy('title', 'asc')->get();

        return compact('course', 'content', 'libraries');
    }

    public function uploadEditorVideo($videoFile): string
    {
        $path = $videoFile->store('course-videos', 'public');

        return asset('storage/' . $path);
    }

    public function uploadEditorImage($imageFile): string
    {
        $path = $imageFile->store('course-images', 'public');

        return asset('storage/' . $path);
    }

    public function storeSectionContent(array $data, $files = null): array
    {
        try {
            $lastPosition = SectionContent::where('course_section_id', $data['course_section_id'])->max('position');
            $newPosition = $lastPosition ? $lastPosition + 1 : 1;

            // Kumpulkan IDs koleksi perpustakaan (bisa banyak)
            $libIds = [];
            if (!empty($data['library_ids']) && is_array($data['library_ids'])) {
                $libIds = array_values(array_filter($data['library_ids']));
            } elseif (!empty($data['library_id'])) {
                $libIds = [$data['library_id']];
            }

            $content = SectionContent::create([
                'course_section_id' => $data['course_section_id'],
                'name'              => $data['name'],
                'video'             => $data['video'] ?? null,
                'content_text'      => $data['content_text'] ?? null,
                'document'          => null,
                'library_id'        => $libIds[0] ?? null,
                'position'          => $newPosition,
            ]);

            // Sinkronisasi relasi perpustakaan banyak
            if (!empty($libIds)) {
                $content->libraries()->sync($libIds);
            }

            // Simpan berkas dokumen lampiran (bisa banyak)
            $fileList = is_array($files) ? $files : ($files ? [$files] : []);
            $firstDocPath = null;

            foreach ($fileList as $f) {
                if ($f && $f->isValid()) {
                    $path = $f->store('course-documents', 'public');
                    if (!$firstDocPath) {
                        $firstDocPath = $path;
                    }
                    $content->documents()->create([
                        'file_path' => $path,
                        'file_name' => $f->getClientOriginalName(),
                        'file_size' => $f->getSize(),
                    ]);
                }
            }

            if ($firstDocPath) {
                $content->update(['document' => $firstDocPath]);
            }

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

    public function updateContent(string $contentId, array $data, $files = null): array
    {
        try {
            $content = SectionContent::with('documents')->findOrFail($contentId);

            // 1. Hapus dokumen spesifik jika diminta
            if (!empty($data['delete_document_ids']) && is_array($data['delete_document_ids'])) {
                $docsToDelete = $content->documents()->whereIn('id', $data['delete_document_ids'])->get();
                foreach ($docsToDelete as $doc) {
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                    $doc->delete();
                }
            }

            // Hapus seluruh dokumen jika ada flag remove_document
            if (!empty($data['remove_document']) && filter_var($data['remove_document'], FILTER_VALIDATE_BOOLEAN)) {
                foreach ($content->documents as $doc) {
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                    $doc->delete();
                }
                if ($content->document && Storage::disk('public')->exists($content->document)) {
                    Storage::disk('public')->delete($content->document);
                }
                $content->document = null;
            }

            // 2. Tambah berkas baru jika diunggah
            $fileList = is_array($files) ? $files : ($files ? [$files] : []);
            foreach ($fileList as $f) {
                if ($f && $f->isValid()) {
                    $path = $f->store('course-documents', 'public');
                    $content->documents()->create([
                        'file_path' => $path,
                        'file_name' => $f->getClientOriginalName(),
                        'file_size' => $f->getSize(),
                    ]);
                }
            }

            // Update legacy column 'document' ke dokumen pertama yang tersisa
            $remainingFirstDoc = $content->documents()->first()?->file_path;

            // 3. Sinkronisasi relasi perpustakaan banyak
            $libIds = [];
            if (isset($data['library_ids']) && is_array($data['library_ids'])) {
                $libIds = array_values(array_filter($data['library_ids']));
            } elseif (!empty($data['library_id'])) {
                $libIds = [$data['library_id']];
            }

            $content->libraries()->sync($libIds);

            $content->update([
                'name'         => $data['name'],
                'video'        => $data['video'] ?? null,
                'content_text' => $data['content_text'] ?? null,
                'document'     => $remainingFirstDoc,
                'library_id'   => $libIds[0] ?? null,
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
            $content = SectionContent::with('documents')->findOrFail($contentId);

            foreach ($content->documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }

            if ($content->document && Storage::disk('public')->exists($content->document)) {
                Storage::disk('public')->delete($content->document);
            }

            $content->libraries()->detach();
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