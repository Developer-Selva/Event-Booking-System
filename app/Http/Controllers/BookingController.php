<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Booking;
use App\Services\BookingService;
use App\Repositories\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $bookingService;
    protected $bookingRepository;

    public function __construct(
        BookingService $bookingService,
        BookingRepository $bookingRepository
    ) {
        $this->bookingService = $bookingService;
        $this->bookingRepository = $bookingRepository;
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $booking = $this->bookingService->bookTicket(
                $ticket,
                $request->only('quantity'),
                Auth::id()
            );

            return response()->json([
                'message' => 'Booking created successfully. Please complete payment.',
                'booking' => $booking->load(['ticket.event', 'ticket.event.organizer'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create booking.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function index()
    {
        $bookings = $this->bookingRepository->getUserBookings(Auth::id());

        return response()->json([
            'bookings' => $bookings
        ]);
    }

    public function cancel(Booking $booking)
    {
        // Check if booking belongs to user
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized. This booking does not belong to you.'
            ], 403);
        }

        // Check if booking is already cancelled
        if ($booking->isCancelled()) {
            return response()->json([
                'message' => 'Booking is already cancelled.'
            ], 400);
        }

        try {
            $booking = $this->bookingService->cancelBooking($booking->id);

            return response()->json([
                'message' => 'Booking cancelled successfully.',
                'booking' => $booking->load(['ticket.event', 'ticket.event.organizer'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel booking.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}