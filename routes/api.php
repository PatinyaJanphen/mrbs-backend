<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\EmailAuthController;

use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::post('/auth/login', [EmailAuthController::class, 'login']);
Route::post('/auth/forgot-password', [EmailAuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [EmailAuthController::class, 'resetPassword']);

use App\Http\Controllers\Api\DashboardController;

// Protected Routes
Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    Route::get(
        '/auth/me',
        function (Request $request) {
            $user = $request->user();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role->value,
                'is_active' => $user->is_active,
                'phone' => $user->phone,
                'department' => $user->department,
            ];
        }
    );

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // User Management Routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::post('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::get('/users/{id}/bookings', [UserController::class, 'bookings']);

    // Profile Routes
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Resource (Room) Routes
    Route::get('/rooms', [ResourceController::class, 'index']);
    Route::get('/rooms/{id}', [ResourceController::class, 'show']);
    Route::post('/rooms', [ResourceController::class, 'store']); // Admin only ideally
    Route::put('/rooms/{id}', [ResourceController::class, 'update']);

    // Booking Routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/my', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn']);
});
