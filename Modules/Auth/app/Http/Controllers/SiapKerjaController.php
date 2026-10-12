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
        $email = mb_strtolower(trim((string) $socialUser->getEmail()));

        $user = User::firstWhere('siapkerja_id', $socialUser->getId());

        if (! $user && $email !== '') {
            // Tautkan ke akun lokal yang emailnya sama (mis. hasil seeder)
            // supaya tidak duplikat (kolom email unique) dan role yang
            // sudah ada tetap terjaga.
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user && ! $user->siapkerja_id) {
                $user->forceFill(['siapkerja_id' => $socialUser->getId()])->save();
            }
        }

        $isNewUser = $user === null;

        $user = User::updateOrCreate(
            ['siapkerja_id' => $socialUser->getId()],
            [
                'name'                    => $socialUser->getName(),
                'email'                   => $email,
                'siapkerja_token'         => $socialUser->token,
                'siapkerja_refresh_token' => $socialUser->refreshToken,
            ]
        );

        $superAdminEmails = array_map(
            static fn (string $email): string => mb_strtolower(trim($email)),
            config('services.siapkerja.super_admin_emails', [])
        );
        $isSuperAdmin = $email !== '' && in_array(
            $email,
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
