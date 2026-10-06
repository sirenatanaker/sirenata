<?php

namespace Modules\LMS\Http\Controllers\AdminPusat\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\LMS\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'category' => $category->only(['id', 'name']),
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'category' => $category->only(['id', 'name']),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->courses()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh kursus.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
