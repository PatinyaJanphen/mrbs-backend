<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\EmailAuthController;

use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\BookingController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::post('/auth/login', [EmailAuthController::class, 'login']);
Route::post('/auth/forgot-password', [EmailAuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [EmailAuthController::class, 'resetPassword']);

use App\Http\Controllers\Api\DashboardController;

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/auth/me',
        function (Request $request) {
            $user = $request->user();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => (int) $user->role,
                'company_id' => $user->company_id,
            ];
        }
    );

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Company Routes
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'store']);

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
});
