<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Devrabiul\ToastMagic\Facades\ToastMagic;

class RegisterController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function register()
    {
        return redirect()->route('siapkerja.redirect');
    }

    public function store(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $this->authService->register($validated);

            Auth::login($user);
            $request->session()->regenerate();


            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;
            session(['api_token' => $token]);

            ToastMagic::success('User registered successfully');
            /** @var User $authenticatedUser */
            $authenticatedUser = Auth::user();

            return redirect()->route($authenticatedUser->getRedirectRoute());
        } catch (\Exception $e) {
            ToastMagic::error('Failed to register user');
            return back()->withInput();
        }
    }
}
