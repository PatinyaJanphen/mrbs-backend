<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\User;
use App\Notifications\BookingConfirmationNotification;
use App\Notifications\BookingRejectionNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use App\Traits\LogsActivity;
use App\Enums\BookingStatus;

class BookingService
{
    use LogsActivity;

    public function getById(int $id): Booking
    {
        return Booking::with(['resource', 'user'])->findOrFail($id);
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['resource', 'user']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['resource_id'])) {
            $query->where('resource_id', $filters['resource_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function listByUser(int $userId): LengthAwarePaginator
    {
        return Booking::with('resource')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(20);
    }

    public function create(int $userId, array $data): Booking
    {
        $resource = Resource::findOrFail($data['resource_id']);

        $this->checkCollision($data['resource_id'], $data['start_time'], $data['end_time']);

        $booking = Booking::create([
            'user_id' => $userId,
            'resource_id' => $data['resource_id'],
            'title' => $data['title'],
            'start_time' => Carbon::parse($data['start_time']),
            'end_time' => Carbon::parse($data['end_time']),
            'status' => $resource->requires_approval ? BookingStatus::PENDING : BookingStatus::APPROVED,
        ]);

        $this->logActivity('booking_created', $booking, [
            'resource_id' => $booking->resource_id,
            'start_time' => $booking->start_time,
            'end_time' => $booking->end_time,
        ]);

        // If auto-approved, send confirmation email
        if ($booking->status === BookingStatus::APPROVED) {
            $booking->user->notify(new BookingConfirmationNotification($booking));
        }

        return $booking;
    }

    private function checkCollision(int $resourceId, $start, $end, $excludeId = null): void
    {
        $query = Booking::where('resource_id', $resourceId)
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::APPROVED]); // Only check against pending or confirmed

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $collision = $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_time', [$start, $end])
                ->orWhereBetween('end_time', [$start, $end])
                ->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('start_time', '<=', $start)
                        ->where('end_time', '>=', $end);
                });
        })->exists();

        if ($collision) {
            throw ValidationException::withMessages([
                'start_time' => ['ช่วงเวลานี้มีการจองอยู่แล้ว'],
            ]);
        }
    }

    public function approve(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        Gate::authorize('approve', $booking);

        // Final check for collisions before approving
        $this->checkCollision($booking->resource_id, $booking->start_time, $booking->end_time, $booking->id);

        $booking->update(['status' => BookingStatus::APPROVED]);

        $this->logActivity('booking_approved', $booking);

        // Send confirmation email
        $booking->user->notify(new BookingConfirmationNotification($booking));

        return $booking;
    }

    public function reject(int $bookingId, string $rejectReason): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        Gate::authorize('reject', $booking);

        $booking->update([
            'status' => BookingStatus::REJECTED,
            'reject_reason' => $rejectReason
        ]);

        $this->logActivity('booking_rejected', $booking, [
            'reject_reason' => $rejectReason
        ]);

        $booking->user->notify(new BookingRejectionNotification($booking, $rejectReason));

        return $booking;
    }

    public function cancel(int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        Gate::authorize('cancel', $booking);

        $booking->update(['status' => BookingStatus::REJECTED]);

        $this->logActivity('booking_cancelled', $booking, [
            'id' => $bookingId,
        ]);

        return $booking;
    }

    public function checkIn(int $bookingId, int $userId): Booking
    {
        $booking = Booking::findOrFail($bookingId);

        $user = User::findOrFail($userId);
        $isOwner = $booking->user_id === $userId;
        $isAdmin = $user->role->value <= UserRole::ADMIN->value;

        if (!$isOwner && !$isAdmin) {
            throw ValidationException::withMessages([
                'booking' => ['คุณไม่มีสิทธิ์ในการเช็คอินการจองนี้'],
            ]);
        }

        if ($booking->status !== BookingStatus::APPROVED) {
            throw ValidationException::withMessages([
                'booking' => ['การจองนี้ไม่ได้อยู่ในสถานะที่สามารถเช็คอินได้'],
            ]);
        }

        if ($booking->checked_in_at !== null) {
            throw ValidationException::withMessages([
                'booking' => ['การจองนี้ได้ทำการเช็คอินไปแล้ว'],
            ]);
        }

        $now = Carbon::now();
        $startTime = Carbon::parse($booking->start_time);

        if ($now->lt($startTime->copy()->subMinutes(15))) {
            throw ValidationException::withMessages([
                'booking' => ['ยังไม่ถึงเวลาเช็คอิน (สามารถเช็คอินได้ก่อนเวลาเริ่ม 15 นาที)'],
            ]);
        }

        $booking->update([
            'checked_in_at' => $now
        ]);

        $this->logActivity('booking_checked_in', $booking, [
            'id' => $bookingId,
            'checked_in_at' => $now,
        ]);

        return $booking;
    }
}
