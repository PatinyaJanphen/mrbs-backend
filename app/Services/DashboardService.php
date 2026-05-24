<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function getStats(?int $companyId): array
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $resourceQuery = Resource::query();
        $bookingQuery1 = Booking::whereDate('start_time', $today)->where('status', '!=', 2);
        $bookingQuery2 = Booking::where('status', 1)->where('start_time', '<=', $now)->where('end_time', '>=', $now);
        $userQuery = User::query();

        if ($companyId !== null) {
            $resourceQuery->where('company_id', $companyId);
            $bookingQuery1->where('company_id', $companyId);
            $bookingQuery2->where('company_id', $companyId);
            $userQuery->where('company_id', $companyId);
        }

        return [
            'total_rooms' => $resourceQuery->count(),
            'today_bookings' => $bookingQuery1->count(),
            'active_bookings' => $bookingQuery2->count(),
            'total_users' => $userQuery->count(),
        ];
    }
}
