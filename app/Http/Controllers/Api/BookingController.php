<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\RejectBookingRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    /**
     * Display a listing of the booking.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = $this->bookingService->list($request->all());

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ]);
    }

    /**
     * Get a single booking.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->getById($id);

        return response()->json([
            'success' => true,
            'data'    => $booking,
        ]);
    }

    /**
     * Show all my bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = $this->bookingService->listByUser($request->user()->id);

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ]);
    }

    /**
     * Store a newly created booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data'    => $booking->load('resource'),
        ], 201);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->cancel($id);

        return response()->json([
            'success' => true,
            'data'    => $booking->load('resource'),
        ]);
    }

    /**
     * Approve a booking
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->approve($id);

        return response()->json([
            'success' => true,
            'data'    => $booking->load('resource'),
        ]);
    }

    /**
     * Reject a booking
     */
    public function reject(RejectBookingRequest $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->reject(
            $id,
            $request->validated()['reject_reason']
        );

        return response()->json([
            'success' => true,
            'data'    => $booking->load('resource'),
        ]);
    }
}
