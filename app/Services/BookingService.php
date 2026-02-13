<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    protected $bookingRepository;
    protected $paymentService;

    public function __construct(
        BookingRepository $bookingRepository,
        PaymentService $paymentService
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->paymentService = $paymentService;
    }

    public function bookTicket(Ticket $ticket, array $data, $userId)
    {
        return DB::transaction(function () use ($ticket, $data, $userId) {
            // Check ticket availability
            if (!$ticket->isAvailable($data['quantity'])) {
                throw ValidationException::withMessages([
                    'quantity' => ['Not enough tickets available.']
                ]);
            }

            // Create booking
            $booking = $this->bookingRepository->create([
                'user_id' => $userId,
                'ticket_id' => $ticket->id,
                'quantity' => $data['quantity'],
                'total_amount' => $ticket->price * $data['quantity'],
                'status' => 'pending'
            ]);

            return $booking;
        });
    }

    public function cancelBooking($bookingId)
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = $this->bookingRepository->findById($bookingId);
            
            if ($booking->isCancelled()) {
                throw ValidationException::withMessages([
                    'booking' => ['Booking is already cancelled.']
                ]);
            }

            if ($booking->isConfirmed()) {
                // If payment was made, process refund
                if ($booking->payment && $booking->payment->isSuccessful()) {
                    $this->paymentService->processRefund($booking->payment);
                } else {
                    $booking->ticket->increaseAvailability($booking->quantity);
                    $booking->markAsCancelled();
                }
            } else {
                $booking->markAsCancelled();
            }

            return $booking;
        });
    }
}