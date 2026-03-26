<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // 1. ส่ง User ไปหน้า Login ของ Google
    public function redirect()
    {
        // ขอสิทธิ์ (Scopes) เพิ่มเติมสำหรับจัดการ Google Calendar ด้วยเลย!
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
            ->stateless()
            ->redirect();
    }

    // 2. รับข้อมูลกลับมาจาก Google (ท่าที่ 1: รับแขกด้วย Gmail)
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();

            // แยกโดเมนออกมาเช็ค
            $domain = explode('@', $email)[1];

            // ค้นหาหรือสร้างบริษัท (ปรับให้สร้างอัตโนมัติสำหรับทุกโดเมนเพื่อความสะดวก)
            $company = Company::firstOrCreate(
                ['domain' => $domain],
                ['name' => ($domain === 'gmail.com' ? 'Guest Workspace' : ucfirst(explode('.', $domain)[0]) . ' Workspace')]
            );

            // ค้นหาหรือสร้าง User
            $user = User::updateOrCreate(
                ['email' => $email], // ค้นหาจากอีเมล
                [
                    'company_id' => $company->id,
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    // เก็บ Token ไว้ใช้กับ Google Calendar API (สำคัญมาก)
                    'google_access_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                ]
            );

            // สร้าง API Token สำหรับให้ React เอาไปใช้งานด้วย Sanctum
            $token = $user->createToken('react-app-token')->plainTextToken;

            // ส่ง Token กลับไปที่ React ผ่าน URL Parameter (React จะเก็บลง LocalStorage เอง)
            return redirect()->away(env('FRONTEND_URL') . '/auth/callback?token=' . $token);

        } catch (\Exception $e) {
            // ดักจับ Error เผื่อ User กดยกเลิก
            return redirect(env('FRONTEND_URL') . '/login?error=google_auth_failed');
        }
    }
}

