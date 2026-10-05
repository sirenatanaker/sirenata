<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Modules\Permission\Enums\StackHolder;

class SiapKerjaController extends Controller
{
    // Step 1: Redirect ke halaman login Kemnaker
    public function redirect()
    {
        return Socialite::driver('siapkerja')->redirect();
    }


    public function callback()
    {
        try {
            $socialUser = Socialite::driver('siapkerja')->user();
            $isNewUser = !User::firstWhere('siapkerja_id', $socialUser->getId());

            $user = User::updateOrCreate(
                ['siapkerja_id' => $socialUser->getId()],
                [
                    'name'                    => $socialUser->getName(),
                    'email'                   => $socialUser->getEmail(),
                    'siapkerja_token'         => $socialUser->token,
                    'siapkerja_refresh_token' => $socialUser->refreshToken,
                ]
            );

            if ($isNewUser) {
                $user->assignRole(StackHolder::USER->value);
            }

            Auth::login($user);

            session([
                'access_token'  => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
            ]);

            return redirect()->route($user->getRedirectRoute());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SiapKerja callback failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('login')->with('error', 'Login SIAPKerja gagal: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->forget(['access_token', 'refresh_token']);

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    }
}
