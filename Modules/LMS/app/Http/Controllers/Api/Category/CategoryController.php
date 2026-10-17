<?php

namespace Modules\LMS\Http\Controllers\Api\Category;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginateResource;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\Api\Category\CategoryStoreRequest;
use Modules\LMS\Http\Requests\Api\Category\CategoryUpdateStoreRequest;
use Modules\LMS\Models\Category;
use Modules\LMS\Services\CategoryService;
use Modules\LMS\Transformers\Api\CategoryResource;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @tags Categories
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    /**
     * List semua kategori
     *
     * @unauthenticated
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = $this->categoryService->paginateFiltered(
                search: $request->input('search'),
                perPage: (int) $request->input('row_per_page', 10),
            );

            return ResponseHelper::success(
                status: true,
                message: 'Categories retrieved successfully',
                result: PaginateResource::make($categories, CategoryResource::class),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(message: $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Detail kategori
     *
     * @unauthenticated
     */
    public function show(Category $category): JsonResponse
    {
        try {
            return ResponseHelper::success(
                status: true,
                message: 'Category retrieved successfully',
                result: new CategoryResource($category),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(message: $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Buat kategori baru
     */
    #[BodyParameter(name: 'name', description: 'Nama kategori course', type: 'string', required: true, example: 'example')]
    public function store(CategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            return ResponseHelper::success(
                status: true,
                message: 'Category created successfully',
                result: new CategoryResource($category),
                statusCode: 201
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(message: $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Update kategori
     */
    #[BodyParameter(name: 'name', description: 'Nama kategori course', type: 'string', required: true, example: 'example')]
    public function update(CategoryUpdateStoreRequest $request, Category $category): JsonResponse
    {
        try {
            $this->categoryService->updateCategory($category, $request->validated());

            return ResponseHelper::success(
                status: true,
                message: 'Category updated successfully',
                result: new CategoryResource($category),
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(message: $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Hapus kategori
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($category, checkCourses: false);

            return ResponseHelper::success(
                status: true,
                message: 'Category deleted successfully',
                result: null,
                statusCode: 200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(message: $e->getMessage(), statusCode: 500);
        }
    }
}