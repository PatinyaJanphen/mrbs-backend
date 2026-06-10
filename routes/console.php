<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Carbon\Carbon;

Schedule::call(function () {
    $cutoff = Carbon::now()->subMinutes(15);

    $bookingsToCancel = Booking::where('status', BookingStatus::APPROVED)
        ->whereNull('checked_in_at')
        ->where('start_time', '<=', $cutoff)
        ->get();

    foreach ($bookingsToCancel as $booking) {
        $booking->update([
            'status' => BookingStatus::REJECTED,
            'reject_reason' => 'ระบบยกเลิกอัตโนมัติเนื่องจากไม่ได้เช็คอินภายใน 15 นาที'
        ]);

        DB::table('logs')->insert([
            'user_id' => null,
            'action' => 'booking_auto_cancelled',
            'subject_type' => Booking::class,
            'subject_id' => $booking->id,
            'properties' => json_encode([
                'reason' => 'No-show (Over 15 minutes)',
                'start_time' => $booking->start_time->toIso8601String(),
            ]),
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
})->everyMinute();
