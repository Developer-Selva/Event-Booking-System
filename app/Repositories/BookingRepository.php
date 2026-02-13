<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data)
    {
        return Booking::create($data);
    }

    public function findById($id)
    {
        return Booking::with(['user', 'ticket.event', 'payment'])->findOrFail($id);
    }

    public function getUserBookings($userId)
    {
        return Booking::with(['ticket.event', 'payment'])
            ->where('user_id', $userId)
            ->paginate(10);
    }

    public function updateStatus($id, $status)
    {
        $booking = $this->findById($id);
        $booking->update(['status' => $status]);
        return $booking;
    }

    public function cancelBooking($id)
    {
        $booking = $this->findById($id);
        
        if ($booking->isConfirmed()) {
            $booking->ticket->increaseAvailability($booking->quantity);
        }
        
        $booking->update(['status' => 'cancelled']);
        return $booking;
    }
}