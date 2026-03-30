<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Auth\GoogleAuthController;

use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\BookingController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

use App\Http\Controllers\Api\DashboardController;

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get(
        '/auth/me',
        function (Request $request) {
            return $request->user();
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

    // Booking Routes
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/my', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});
