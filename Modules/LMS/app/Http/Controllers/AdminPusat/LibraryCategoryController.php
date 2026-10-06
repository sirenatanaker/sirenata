<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\LMS\Models\LibraryCategory;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class LibraryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = LibraryCategory::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $libraryCategories = $query->latest()->paginate(10);
        return view('lms::admin-pusat.library-categories.index', compact('libraryCategories', 'search'));
    }

    public function store(Request $request) 
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:library_categories,name',
            'description' => 'nullable|string',
        ]);

        LibraryCategory::create($validated);
        
        ToastMagic::success('Kategori Perpustakaan berhasil ditambahkan!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('library_categories', 'name')],
        ]);

        $category = LibraryCategory::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'category' => $category->only(['id', 'name']),
        ], 201);
    }

    public function update(Request $request, string $id) 
    {
        $libraryCategory = LibraryCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:library_categories,name,' . $libraryCategory->id,
            'description' => 'nullable|string',
        ]);

        $libraryCategory->update($validated);
        
        ToastMagic::success('Kategori Perpustakaan berhasil diperbarui!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function updateInline(Request $request, LibraryCategory $libraryCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('library_categories', 'name')->ignore($libraryCategory->id),
            ],
        ]);

        $libraryCategory->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'category' => $libraryCategory->only(['id', 'name']),
        ]);
    }

    public function destroy(string $id) 
    {
        $libraryCategory = LibraryCategory::findOrFail($id);

        if ($libraryCategory->libraries()->exists()) {
            return redirect()->route('admin-pusat.library-categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh koleksi.');
        }

        $libraryCategory->delete();
        
        ToastMagic::success('Kategori Perpustakaan berhasil dihapus!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function destroyInline(LibraryCategory $libraryCategory): JsonResponse
    {
        if ($libraryCategory->libraries()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh koleksi.',
            ], 422);
        }

        $libraryCategory->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
