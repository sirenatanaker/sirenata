<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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

        $superAdminEmails = array_map(
            static fn (string $email): string => mb_strtolower(trim($email)),
            config('services.siapkerja.super_admin_emails', [])
        );
        $isSuperAdmin = in_array(
            mb_strtolower(trim((string) $socialUser->getEmail())),
            $superAdminEmails,
            true
        );

        if ($isSuperAdmin) {
            $user->syncRoles(StackHolder::SUPER_ADMIN->value);
        } elseif ($isNewUser) {
            $user->assignRole(StackHolder::USER->value);
        }

        Auth::login($user);

        session([
            'access_token'  => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
        ]);

        return redirect()->route($user->getRedirectRoute());
    }
}
