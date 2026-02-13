<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'type' => 'required|in:VIP,Standard,Early Bird,Student',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticket = $event->tickets()->create([
            'type' => $request->type,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'available_quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => $ticket->load('event')
        ], 201);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'type' => 'sometimes|in:VIP,Standard,Early Bird,Student',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        if ($request->has('quantity')) {
            $oldQuantity = $ticket->quantity;
            $bookedQuantity = $ticket->quantity - $ticket->available_quantity;
            
            if ($request->quantity < $bookedQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Cannot reduce quantity below already booked tickets.']
                ]);
            }
            
            $ticket->quantity = $request->quantity;
            $ticket->available_quantity = $request->quantity - $bookedQuantity;
        }

        $ticket->fill($request->except('quantity'));
        $ticket->save();

        return response()->json([
            'message' => 'Ticket updated successfully.',
            'ticket' => $ticket->load('event')
        ]);
    }

    public function destroy(Ticket $ticket)
    {
        if ($ticket->bookings()->whereIn('status', ['pending', 'confirmed'])->exists()) {
            throw ValidationException::withMessages([
                'ticket' => ['Cannot delete ticket with active bookings.']
            ]);
        }

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.'
        ]);
    }
}