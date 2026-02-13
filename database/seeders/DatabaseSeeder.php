<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create 2 admins
        User::factory(2)->admin()->create();
        
        // Create 3 organizers
        $organizers = User::factory(3)->organizer()->create();
        
        // Create 10 customers
        $customers = User::factory(10)->customer()->create();
        
        // Create 5 events for each organizer
        foreach ($organizers as $organizer) {
            $events = Event::factory(5)->create([
                'created_by' => $organizer->id
            ]);
            
            // Create 15 tickets total across all events
            foreach ($events as $event) {
                Ticket::factory(3)->create([
                    'event_id' => $event->id
                ]);
            }
        }
        
        // Create 20 bookings from customers
        $tickets = Ticket::all();
        foreach (range(1, 20) as $index) {
            $customer = $customers->random();
            $ticket = $tickets->random();
            $quantity = rand(1, 3);
            
            $booking = Booking::create([
                'user_id' => $customer->id,
                'ticket_id' => $ticket->id,
                'quantity' => $quantity,
                'total_amount' => $ticket->price * $quantity,
                'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled'])
            ]);
            
            // Create payment for confirmed bookings
            if ($booking->status === 'confirmed') {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_amount,
                    'status' => 'success',
                    'transaction_id' => fake()->uuid(),
                    'payment_response' => ['processed_at' => now()]
                ]);
                
                $ticket->decreaseAvailability($quantity);
            }
        }
    }
}