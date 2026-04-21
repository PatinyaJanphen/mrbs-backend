<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
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
        $bookings = $this->bookingService->list(
            $request->user()->company_id,
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * Show the form for creating a new booking.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $bookings = $this->bookingService->getById(
            $request->user()->company_id,
            $id
        );

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * Show all my bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = $this->bookingService->listByUser(
            $request->user()->company_id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }

    /**
     * Store a newly created resource in booking.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create(
            $request->user()->company_id,
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $booking->load('resource')
        ], 201);
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->cancel(
            $request->user()->company_id,
            $request->user()->id,
            $id,
            $request->user()->role <= 1
        );

        return response()->json([
            'success' => true,
            'data' => $booking->load('resource')
        ], 200);
    }

    /**
     * Approve a booking
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->approve(
            $request->user()->company_id,
            $id
        );

        return response()->json([
            'success' => true,
            'data' => $booking->load('resource')
        ], 200);
    }

    /**
     * Reject a booking
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $booking = $this->bookingService->reject(
            $request->user()->company_id,
            $id
        );

        return response()->json([
            'success' => true,
            'data' => $booking->load('resource')
        ], 200);
    }
}
