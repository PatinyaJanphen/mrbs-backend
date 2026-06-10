<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any active user can request a booking
    }

    /**
     * Determine whether the user can approve the booking.
     */
    public function approve(User $user, Booking $booking): bool
    {
        return $user->role->value <= UserRole::ADMIN->value;
    }

    /**
     * Determine whether the user can reject the booking.
     */
    public function reject(User $user, Booking $booking): bool
    {
        return $user->role->value <= UserRole::ADMIN->value;
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Can cancel if they are the owner OR if they are an admin
        return $user->id === $booking->user_id || $user->role->value <= UserRole::ADMIN->value;
    }
}
