<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Requests\Booking\RejectBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    /**
     * Display a listing of the booking.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $this->bookingService->list(
            $request->user()->company_id,
            $request->all()
        );

        return BookingResource::collection($bookings);
    }

    /**
     * Show the form for creating a new booking.
     */
    public function show(Request $request, int $id): BookingResource
    {
        $bookings = $this->bookingService->getById(
            $request->user()->company_id,
            $id
        );

        return new BookingResource($bookings);
    }

    /**
     * Show all my bookings
     */
    public function myBookings(Request $request): AnonymousResourceCollection
    {
        $bookings = $this->bookingService->listByUser(
            $request->user()->company_id,
            $request->user()->id
        );

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in booking.
     */
    public function store(StoreBookingRequest $request): BookingResource
    {
        $booking = $this->bookingService->create(
            $request->user()->company_id,
            $request->user()->id,
            $request->validated()
        );

        return new BookingResource($booking->load('resource'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, int $id): BookingResource
    {
        $booking = $this->bookingService->cancel(
            $request->user()->company_id,
            $id
        );

        return new BookingResource($booking->load('resource'));
    }

    /**
     * Approve a booking
     */
    public function approve(Request $request, int $id): BookingResource
    {
        $booking = $this->bookingService->approve(
            $request->user()->company_id,
            $id
        );

        return new BookingResource($booking->load('resource'));
    }

    /**
     * Reject a booking
     */
    public function reject(RejectBookingRequest $request, int $id): BookingResource
    {
        $booking = $this->bookingService->reject(
            $request->user()->company_id,
            $id,
            $request->validated()['reject_reason']
        );

        return new BookingResource($booking->load('resource'));
    }
}
