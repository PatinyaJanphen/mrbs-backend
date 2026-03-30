<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    public function index(Request $request)
    {
        return $this->bookingService->list(
            $request->user()->company_id,
            $request->all()
        );
    }

    public function show(Request $request, int $id)
    {
        return $this->bookingService->getById(
            $request->user()->company_id,
            $id
        );
    }

    public function myBookings(Request $request)
    {
        return $this->bookingService->listByUser(
            $request->user()->company_id,
            $request->user()->id
        );
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookingService->create(
            $request->user()->company_id,
            $request->user()->id,
            $request->validated()
        );

        return response()->json($booking->load('resource'), 201);
    }

    public function cancel(Request $request, int $id)
    {
        $booking = $this->bookingService->cancel(
            $request->user()->company_id,
            $request->user()->id,
            $id,
            $request->user()->role === 'admin'
        );

        return $booking;
    }
}
