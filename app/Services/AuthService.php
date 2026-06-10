<?php

namespace App\Services;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Illuminate\Support\Str;

class AuthService
{
    use LogsActivity;

    /**
     * Handle email/password login.
     */
    public function loginWithEmail(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->password || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['ข้อมูลผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'],
            ]);
        }

        $token = $user->createToken('react-app-token')->plainTextToken;

        $this->logActivity('user_logged_in', $user, ['method' => 'email'], $user);

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Handle Google OAuth callback logic.
     */
    public function handleGoogleCallback(SocialiteUser $googleUser): array
    {
        $email = $googleUser->getEmail();

        // Update or create user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
            ]
        );

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['บัญชีนี้ถูกปิดการใช้งาน กรุณาติดต่อผู้ดูแลระบบ'],
            ]);
        }

        $token = $user->createToken('react-app-token')->plainTextToken;

        $this->logActivity('user_logged_in', $user, ['method' => 'google'], $user);

        return [
            'token' => $token,
            'user' => $user
        ];
    }

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->logActivity('password_reset_requested', $user);
            }
            return $status;
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                $this->logActivity('password_reset_completed', $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $status;
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
