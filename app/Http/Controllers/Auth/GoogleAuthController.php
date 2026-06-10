<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $result = $this->authService->handleGoogleCallback($googleUser);

            return redirect()->away(env('FRONTEND_URL') . '/auth/callback?token=' . $result['token']);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'https://mrbs-testsite.netlify.app');
            return redirect($frontendUrl . '/login?error=google_auth_failed');
        }
    }
}

