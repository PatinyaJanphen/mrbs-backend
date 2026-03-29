<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function list(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['resource', 'user'])
            ->where('company_id', $companyId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['resource_id'])) {
            $query->where('resource_id', $filters['resource_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function listByUser(int $companyId, int $userId): LengthAwarePaginator
    {
        return Booking::with('resource')
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->latest()
            ->paginate(20);
    }

    public function create(int $companyId, int $userId, array $data): Booking
    {
        $resource = Resource::where('company_id', $companyId)->findOrFail($data['resource_id']);

        $this->checkCollision($data['resource_id'], $data['start_time'], $data['end_time']);

        return Booking::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'resource_id' => $data['resource_id'],
            'title' => $data['title'],
            'start_time' => Carbon::parse($data['start_time']),
            'end_time' => Carbon::parse($data['end_time']),
            'status' => $resource->requires_approval ? 'pending' : 'confirmed',
        ]);
    }

    private function checkCollision(int $resourceId, $start, $end): void
    {
        $collision = Booking::where('resource_id', $resourceId)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
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

    public function cancel(int $companyId, int $userId, int $bookingId, bool $isAdmin = false): Booking
    {
        $booking = Booking::where('company_id', $companyId)->findOrFail($bookingId);

        if (!$isAdmin && $booking->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $booking->update(['status' => 'cancelled']);
        return $booking;
    }
}
