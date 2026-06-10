<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function redirect()
    {
        return Socialite::driver('google')
            ->redirectUrl($this->callbackUrl())
            ->scopes(['openid', 'email', 'profile'])
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->callbackUrl())
                ->stateless()
                ->user();

            $result = $this->authService->handleGoogleCallback($googleUser);

            return redirect()->away(env('FRONTEND_URL') . '/auth/callback?token=' . $result['token']);

        } catch (Throwable $e) {
            Log::error('Google authentication failed', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            $frontendUrl = env('FRONTEND_URL', 'https://mrbs-testsite.netlify.app');
            return redirect($frontendUrl . '/login?error=google_auth_failed');
        }
    }

    private function callbackUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/api/auth/google/callback';
    }
}
