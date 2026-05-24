<?php

namespace App\Policies;

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
        return $user->company_id === $booking->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any user in the company can request a booking
    }

    /**
     * Determine whether the user can approve the booking.
     */
    public function approve(User $user, Booking $booking): bool
    {
        // Must be in the same company and have admin role (role <= User::ROLE_ADMIN)
        return $user->company_id === $booking->company_id && $user->role <= User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can reject the booking.
     */
    public function reject(User $user, Booking $booking): bool
    {
        // Must be in the same company and have admin role (role <= User::ROLE_ADMIN)
        return $user->company_id === $booking->company_id && $user->role <= User::ROLE_ADMIN;
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Must be in the same company
        if ($user->company_id !== $booking->company_id) {
            return false;
        }

        // Can cancel if they are the owner OR if they are an admin
        return $user->id === $booking->user_id || $user->role <= User::ROLE_ADMIN;
    }
}
