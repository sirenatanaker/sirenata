<?php

namespace Modules\LMS\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\LMS\Models\Library;
use Modules\LMS\Models\LibraryCategory;

class LibraryService
{
    public function getFilteredQueryBuilder(
        ?string $search = null,
        ?string $libraryCategoryId = null,
        ?string $libraryCategoryName = null
    ) {
        return Library::with(['libraryCategory', 'creator'])
            ->when($search, fn($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($libraryCategoryId, fn($query) => $query->where('library_category_id', $libraryCategoryId))
            ->when($libraryCategoryName, fn($query) => $query->whereHas('libraryCategory', function ($q) use ($libraryCategoryName) {
                $q->where('name', $libraryCategoryName);
            }))
            ->latest();
    }

    public function paginateFiltered(
        ?string $search = null,
        ?string $libraryCategoryId = null,
        ?string $libraryCategoryName = null,
        int $limit = 10
    ) {
        return $this->getFilteredQueryBuilder($search, $libraryCategoryId, $libraryCategoryName)
            ->paginate($limit)
            ->withQueryString();
    }

    public function findOrFail(string $id): Library
    {
        return Library::findOrFail($id);
    }

    public function getAllCategoriesWithCounts(): Collection
    {
        return LibraryCategory::withCount('libraries')->orderBy('name')->get();
    }

    public function getLibraryStats(?Collection $categories = null): array
    {
        $categoriesCount = $categories ? $categories->count() : LibraryCategory::count();

        return [
            'totalKoleksi'  => Library::count(),
            'totalKategori' => $categoriesCount,
            'totalDokumen'  => Library::whereNotNull('file_path')->count(),
            'totalVideo'    => Library::where(function ($q) {
                $q->whereNotNull('video_path')
                    ->orWhere('external_link', 'like', '%youtube%')
                    ->orWhere('external_link', 'like', '%youtu.be%');
            })->count(),
        ];
    }

    public function createLibrary(array $data): Library
    {
        return DB::transaction(function () use ($data) {
            $coverImage = null;
            if (!empty($data['thumb_mode']) && $data['thumb_mode'] === 'auto') {
                $colors = ['13416B', '547996', '8BB1CC', '79A736'];
                $selectedColor = $data['bg_color'] ?? $colors[0];

                $rawTitle = $data['title'] ?? 'Pustaka';
                $words = explode(' ', trim($rawTitle));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                $encodedInitials = urlencode($initials);
                $coverImage = "https://ui-avatars.com/api/?name={$encodedInitials}&background={$selectedColor}&color=fff&size=512&bold=true&length=2";
            } elseif (!empty($data['cover_image'])) {
                $coverImage = $data['cover_image']->store('libraries/covers', 'public');
            }

            $filePath = null;
            if (!empty($data['file_path'])) {
                $filePath = $data['file_path']->store('libraries/files', 'public');
            }

            $videoPath = null;
            if (!empty($data['video_path'])) {
                $videoPath = $data['video_path']->store('libraries/videos', 'public');
            }

            $library = Library::create([
                'library_category_id' => $data['library_category_id'],
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'cover_image'         => $coverImage,
                'file_path'           => $filePath,
                'video_path'          => $videoPath,
                'external_link'       => $data['external_link'] ?? null,
                'created_by'          => Auth::id(),
            ]);

            // Kirim notifikasi koleksi perpustakaan baru ke seluruh user
            try {
                app(\Modules\Dashboard\Services\NotificationService::class)->libraryPublished($library);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal memicu notifikasi perpustakaan baru: ' . $e->getMessage());
            }

            return $library;
        });
    }

    public function updateLibrary(Library $library, array $data): Library
    {
        return DB::transaction(function () use ($library, $data) {
            $coverImage = $library->cover_image;

            if (!empty($data['thumb_mode']) && $data['thumb_mode'] === 'auto') {
                if ($coverImage && !str_starts_with($coverImage, 'http') && Storage::disk('public')->exists($coverImage)) {
                    Storage::disk('public')->delete($coverImage);
                }

                $colors = ['13416B', '547996', '8BB1CC', '79A736'];
                $selectedColor = $data['bg_color'] ?? $colors[0];

                $rawTitle = $data['title'] ?? $library->title;
                $words = explode(' ', trim($rawTitle));
                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

                $coverImage = "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background={$selectedColor}&color=fff&size=512&bold=true&length=2";
            } elseif (!empty($data['cover_image'])) {
                if ($library->cover_image && !str_starts_with($library->cover_image, 'http')) {
                    Storage::disk('public')->delete($library->cover_image);
                }
                $coverImage = $data['cover_image']->store('libraries/covers', 'public');
            }

            $filePath = $library->file_path;
            if (!empty($data['file_path'])) {
                if ($library->file_path) {
                    Storage::disk('public')->delete($library->file_path);
                }
                $filePath = $data['file_path']->store('libraries/files', 'public');
            }

            $videoPath = $library->video_path;
            if (!empty($data['video_path'])) {
                if ($library->video_path) {
                    Storage::disk('public')->delete($library->video_path);
                }
                $videoPath = $data['video_path']->store('libraries/videos', 'public');
            }

            $library->update([
                'library_category_id' => $data['library_category_id'],
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'cover_image'         => $coverImage,
                'file_path'           => $filePath,
                'video_path'          => $videoPath,
                'external_link'       => $data['external_link'] ?? null,
            ]);

            return $library;
        });
    }

    public function deleteLibrary(Library $library): void
    {
        if ($library->cover_image && !str_starts_with($library->cover_image, 'http')) {
            Storage::disk('public')->delete($library->cover_image);
        }

        if ($library->file_path) {
            Storage::disk('public')->delete($library->file_path);
        }

        if ($library->video_path) {
            Storage::disk('public')->delete($library->video_path);
        }

        $library->delete();
    }

    public function recordUserAccess(Library $library, $userId): void
    {
        DB::table('user_library_history')->updateOrInsert(
            [
                'user_id'    => $userId,
                'library_id' => $library->id,
            ],
            [
                'last_accessed_at' => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]
        );
    }
}