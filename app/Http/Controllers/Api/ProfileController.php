<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use LogsActivity;

    /**
     * Update user profile information.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->department = $request->department;

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Ensure directory exists
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $file->move($path, $filename);
            $user->avatar = url('uploads/avatars/' . $filename);
        }

        $user->save();

        $this->logActivity('profile_updated', $user, [
            'name' => $user->name,
            'phone' => $user->phone,
            'department' => $user->department,
        ], $user);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตข้อมูลโปรไฟล์เรียบร้อยแล้ว',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => (int) $user->role,
                'company_id' => $user->company_id,
                'phone' => $user->phone,
                'department' => $user->department,
            ]
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
        $hasPassword = !empty($user->password);

        if ($hasPassword && !Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['รหัสผ่านเดิมไม่ถูกต้อง'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $this->logActivity('password_changed', $user, [], $user);

        return response()->json([
            'success' => true,
            'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว',
        ]);
    }
}
