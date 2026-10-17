<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LMS\Services\LibraryService;

class LibraryController extends Controller
{
    public function __construct(
        private LibraryService $libraryService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $libraries = $this->libraryService->paginateFiltered(
            search: $search,
            libraryCategoryName: $type,
            limit: 12,
        );
        $libraryCategories = $this->libraryService->getAllCategoriesWithCounts();
        $stats = $this->libraryService->getLibraryStats($libraryCategories);

        return view('lms::user.library.index', array_merge(
            compact('libraries', 'libraryCategories', 'type', 'search'),
            $stats
        ));
    }

    public function show(Request $request, string $id): View
    {
        $library = $this->libraryService->findOrFail($id);
        $userId = $request->user()?->id;

        if ($userId) {
            $this->libraryService->recordUserAccess($library, $userId);
        }

        return view('lms::user.library.show', compact('library'));
    }

    public function track(Request $request, string $id): JsonResponse
    {
        $userId = $request->user()?->id;

        if ($userId) {
            $library = $this->libraryService->findOrFail($id);
            $this->libraryService->recordUserAccess($library, $userId);
        }

        return response()->json(['success' => true]);
    }
}