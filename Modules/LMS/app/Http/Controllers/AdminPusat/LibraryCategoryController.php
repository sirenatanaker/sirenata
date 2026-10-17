<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Http\Requests\LibraryCategoryStoreRequest;
use Modules\LMS\Http\Requests\LibraryCategoryUpdateRequest;
use Modules\LMS\Models\LibraryCategory;
use Modules\LMS\Services\LibraryCategoryService;

class LibraryCategoryController extends Controller
{
    public function __construct(
        private LibraryCategoryService $libraryCategoryService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $libraryCategories = $this->libraryCategoryService->paginateFiltered($search, 10);

        return view('lms::admin-pusat.library-categories.index', compact('libraryCategories', 'search'));
    }

    public function store(LibraryCategoryStoreRequest $request)
    {
        $this->libraryCategoryService->createCategory($request->validated());
        ToastMagic::success('Kategori Perpustakaan berhasil ditambahkan!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function storeInline(LibraryCategoryStoreRequest $request): JsonResponse
    {
        $category = $this->libraryCategoryService->createCategory($request->validated());

        return response()->json([
            'message'  => 'Kategori berhasil ditambahkan.',
            'category' => $category->only(['id', 'name']),
        ], 201);
    }

    public function update(LibraryCategoryUpdateRequest $request, string $id)
    {
        $libraryCategory = $this->libraryCategoryService->findOrFail($id);
        $this->libraryCategoryService->updateCategory($libraryCategory, $request->validated());
        ToastMagic::success('Kategori Perpustakaan berhasil diperbarui!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function updateInline(LibraryCategoryUpdateRequest $request, LibraryCategory $libraryCategory): JsonResponse
    {
        $this->libraryCategoryService->updateCategory($libraryCategory, $request->validated());

        return response()->json([
            'message'  => 'Kategori berhasil diperbarui.',
            'category' => $libraryCategory->only(['id', 'name']),
        ]);
    }

    public function destroy(string $id)
    {
        $libraryCategory = $this->libraryCategoryService->findOrFail($id);

        if (!$this->libraryCategoryService->deleteCategory($libraryCategory)) {
            return redirect()->route('admin-pusat.library-categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh koleksi.');
        }

        ToastMagic::success('Kategori Perpustakaan berhasil dihapus!');

        return redirect()->route('admin-pusat.library-categories.index');
    }

    public function destroyInline(LibraryCategory $libraryCategory): JsonResponse
    {
        if (!$this->libraryCategoryService->deleteCategory($libraryCategory)) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh koleksi.',
            ], 422);
        }

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
