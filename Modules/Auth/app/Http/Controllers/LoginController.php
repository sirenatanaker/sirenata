<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Services\AuthService;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function login()
    {
        return redirect()->route('siapkerja.redirect');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if ($this->authService->authenticate($credentials, $remember)) {
            return redirect()->route(Auth::user()->getRedirectRoute());
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        return redirect(route('login'))->with('success', 'You have been logged out!');
    }
}
