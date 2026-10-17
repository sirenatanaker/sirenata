<?php

namespace Modules\LMS\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\LMS\Models\Category;

class CategoryService
{
    public function paginateFiltered(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Category::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function getAllOrdered(): Collection
    {
        return Category::select('id', 'name')->orderBy('name', 'asc')->get();
    }

    public function getCategoriesForCatalog(array $enrolledCourseIds = []): Collection
    {
        return Category::withCount([
            'courses' => function ($query) use ($enrolledCourseIds) {
                $query->whereNotIn('id', $enrolledCourseIds);
            },
        ])->orderBy('name', 'asc')->get();
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(Category $category, bool $checkCourses = true): bool
    {
        if ($checkCourses && $category->courses()->exists()) {
            return false;
        }

        $category->delete();

        return true;
    }
}
