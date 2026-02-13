<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Ticket;

class PreventDoubleBooking
{
    public function handle(Request $request, Closure $next)
    {
        $ticketId = $request->route('ticket');
        
        if ($ticketId instanceof Ticket) {
            $ticketId = $ticketId->id;
        }
        
        $userId = auth()->id();
        
        $existingBooking = Booking::where('user_id', $userId)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
            
        if ($existingBooking) {
            return response()->json([
                'message' => 'You have already booked this ticket.',
                'errors' => ['booking' => ['Double booking is not allowed.']]
            ], 409);
        }
        
        return $next($request);
    }
}