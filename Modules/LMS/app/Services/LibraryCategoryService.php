<?php

namespace Modules\LMS\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\LMS\Models\LibraryCategory;

class LibraryCategoryService
{
    public function paginateFiltered(?string $search = null, int $limit = 10): LengthAwarePaginator
    {
        return LibraryCategory::query()
            ->when($search, fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($limit);
    }

    public function getAllOrdered(): Collection
    {
        return LibraryCategory::orderBy('name')->get();
    }

    public function getAllWithCounts(): Collection
    {
        return LibraryCategory::withCount('libraries')->orderBy('name')->get();
    }

    public function findOrFail(string $id): LibraryCategory
    {
        return LibraryCategory::findOrFail($id);
    }

    public function createCategory(array $data): LibraryCategory
    {
        return LibraryCategory::create($data);
    }

    public function updateCategory(LibraryCategory $category, array $data): LibraryCategory
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(LibraryCategory $category): bool
    {
        if ($category->libraries()->exists()) {
            return false;
        }

        $category->delete();

        return true;
    }
}
