<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class EmailAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->loginWithEmail(
            $request->email,
            $request->password
        );

        return response()->json([
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'token' => $result['token'],
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
                'avatar' => $result['user']->avatar,
                'role' => (int) $result['user']->role,
                'is_active' => $result['user']->is_active,
                'company_id' => $result['user']->company_id,
                'phone' => $result['user']->phone,
                'department' => $result['user']->department,
            ]
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendPasswordResetLink($request->email);

        return response()->json(['message' => __($status)]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->authService->resetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token')
        );

        return response()->json(['message' => __($status)]);
    }
}
