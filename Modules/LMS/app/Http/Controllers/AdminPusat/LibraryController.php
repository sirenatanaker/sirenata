<?php

namespace Modules\LMS\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\LMS\Http\Requests\LibraryStoreRequest;
use Modules\LMS\Http\Requests\LibraryUpdateRequest;
use Modules\LMS\Services\LibraryCategoryService;
use Modules\LMS\Services\LibraryService;
use Spatie\Permission\Middleware\PermissionMiddleware;

class LibraryController extends Controller implements HasMiddleware
{
    public function __construct(
        private LibraryService $libraryService,
        private LibraryCategoryService $libraryCategoryService,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('library-view|library-create|library-edit|library-delete'), only: ['index']),
            new Middleware(PermissionMiddleware::using('library-create'), only: ['store']),
            new Middleware(PermissionMiddleware::using('library-edit'), only: ['update']),
            new Middleware(PermissionMiddleware::using('library-delete'), only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $libraryCategoryId = $request->input('library_category_id');

        $libraries = $this->libraryService->paginateFiltered(
            search: $search,
            libraryCategoryId: $libraryCategoryId ? (string) $libraryCategoryId : null,
        );
        $libraryCategories = $this->libraryCategoryService->getAllOrdered();

        return view('lms::admin-pusat.libraries.index', compact('libraries', 'search', 'libraryCategoryId', 'libraryCategories'));
    }

    public function store(LibraryStoreRequest $request)
    {
        $this->libraryService->createLibrary($request->validated());
        ToastMagic::success('Materi Perpustakaan berhasil ditambahkan.');

        return redirect()->route('admin-pusat.libraries.index');
    }

    public function update(LibraryUpdateRequest $request, string $id)
    {
        $library = $this->libraryService->findOrFail($id);
        $this->libraryService->updateLibrary($library, $request->validated());
        ToastMagic::success('Materi Perpustakaan berhasil diperbarui.');

        return redirect()->route('admin-pusat.libraries.index');
    }

    public function destroy(string $id)
    {
        $library = $this->libraryService->findOrFail($id);
        $this->libraryService->deleteLibrary($library);
        ToastMagic::success('Materi Perpustakaan berhasil dihapus.');

        return redirect()->route('admin-pusat.libraries.index');
    }
}
