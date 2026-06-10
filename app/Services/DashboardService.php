<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function getStats(): array
    {
        $today = Carbon::today();
        $now = Carbon::now();

        return [
            'total_rooms' => Resource::count(),
            'today_bookings' => Booking::whereDate('start_time', $today)->where('status', '!=', 2)->count(),
            'active_bookings' => Booking::where('status', 1)->where('start_time', '<=', $now)->where('end_time', '>=', $now)->count(),
            'total_users' => User::count(),
        ];
    }
}
