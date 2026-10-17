<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\LMS\Services\LibraryService;

class LibraryController extends Controller
{
    public function __construct(
        private LibraryService $libraryService
    ) {}

    public function index(Request $request)
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

    public function show($id)
    {
        $library = $this->libraryService->findOrFail((string) $id);
        $userId = Auth::id();

        if ($userId) {
            $this->libraryService->recordUserAccess($library, $userId);
        }

        return view('lms::user.library.show', compact('library'));
    }

    public function track($id)
    {
        $userId = Auth::id();

        if ($userId) {
            $library = $this->libraryService->findOrFail((string) $id);
            $this->libraryService->recordUserAccess($library, $userId);
        }

        return response()->json(['success' => true]);
    }
}