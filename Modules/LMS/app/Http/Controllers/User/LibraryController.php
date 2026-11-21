<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Support\SmartCache;
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
        $page = (int) $request->input('page', 1);

        $cacheKey = 'user_library_index:' . md5(json_encode([
            'search' => $search,
            'type'   => $type,
            'page'   => $page,
        ]));

        $cached = SmartCache::remember(
            [SmartCache::DOMAIN_LMS_LIBRARY, SmartCache::DOMAIN_LMS_COURSES],
            $cacheKey,
            300,
            function () use ($search, $type): array {
                $libraries = $this->libraryService->paginateFiltered(
                    search: $search,
                    libraryCategoryName: $type,
                    limit: 12,
                );
                $libraryCategories = $this->libraryService->getAllCategoriesWithCounts();
                $stats = $this->libraryService->getLibraryStats($libraryCategories);

                return array_merge(compact('libraries', 'libraryCategories'), $stats);
            }
        );

        return view('lms::user.library.index', array_merge(
            $cached,
            compact('type', 'search')
        ));
    }

    public function show(Request $request, string $id): View
    {
        $library = SmartCache::remember(
            [SmartCache::DOMAIN_LMS_LIBRARY, SmartCache::DOMAIN_LMS_COURSES],
            "user_library_show:{$id}",
            300,
            fn () => $this->libraryService->findOrFail($id)
        );

        $userId = $request->user()?->id;

        if ($userId) {
            $this->libraryService->recordUserAccess($library, $userId);
            SmartCache::bumpUser($userId);
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