<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\LMS\Http\Requests\CategoryStoreRequest;
use Modules\LMS\Http\Requests\CategoryUpdateRequest;
use Modules\LMS\Models\Category;
use Modules\LMS\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'message'  => 'Kategori berhasil ditambahkan.',
            'category' => $category->only(['id', 'name']),
        ], 201);
    }

    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return response()->json([
            'message'  => 'Kategori berhasil diperbarui.',
            'category' => $category->only(['id', 'name']),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if (!$this->categoryService->deleteCategory($category)) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh kursus.',
            ], 422);
        }

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
