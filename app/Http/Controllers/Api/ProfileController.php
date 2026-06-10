<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Update user profile information.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated(),
            $request->hasFile('avatar') ? $request->file('avatar') : null
        );

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว',
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'avatar'     => $user->avatar,
                'role'       => (int) $user->role,
                'phone'      => $user->phone,
                'department' => $user->department,
            ],
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // If the user has a password set (i.e. not purely Google login),
        // we require them to verify their current password first.
        if (!empty($user->password) && !Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['รหัสผ่านเดิมไม่ถูกต้อง'],
            ]);
        }

        $this->userService->updatePassword($user, $request->password);

        return response()->json([
            'success' => true,
            'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
        ]);
    }
}
