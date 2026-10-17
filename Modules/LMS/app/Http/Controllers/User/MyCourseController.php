<?php

namespace Modules\LMS\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyCourseController extends Controller
{
    /**
     * Display the user's enrolled courses.
     */
    public function index(Request $request): View
    {
        return view('lms::user.my-course');
    }
}
