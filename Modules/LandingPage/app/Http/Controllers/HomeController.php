<?php

namespace Modules\LandingPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\LandingPage\Services\LandingPageService;

class HomeController extends Controller
{
    public function __construct(
        private LandingPageService $landingPageService
    ) {}

    /**
     * Display the landing page with dynamic statistics.
     */
    public function index(): View
    {
        $data = $this->landingPageService->getLandingPageData();

        return view('landingpage::index', $data);
    }
}
