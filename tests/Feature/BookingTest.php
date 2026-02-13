<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function test_customer_can_book_ticket()
    {
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create([
            'available_quantity' => 10,
            'price' => 100
        ]);
        
        // $response = $this->actingAs($customer)
        //     ->postJson("/api/tickets/{$ticket->id}/bookings", [
        //         'quantity' => 2
        //     ]);
            
        $response = $this->actingAs($customer, 'sanctum') // Add 'sanctum' guard
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('booking.ticket_id', $ticket->id)
            ->assertJsonPath('booking.user_id', $customer->id);
            

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200,
            'status' => 'pending'
        ]);
    }

    public function test_cannot_book_more_than_available()
    {
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create([
            'available_quantity' => 1,
            'quantity' => 5,
            'price' => 100
        ]);
        
        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 2
            ]);
            
        $response->assertStatus(422);
    }

    public function test_customer_can_cancel_booking()
    {
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create([
            'available_quantity' => 5,
            'quantity' => 10,
            'price' => 100
        ]);
        
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200,
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($customer)
            ->putJson("/api/bookings/{$booking->id}/cancel");
            
        $response->assertStatus(200)
            ->assertJsonPath('booking.status', 'cancelled');
            
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_customer_cannot_cancel_others_booking()
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $customer1->id,
            'ticket_id' => $ticket->id,
            'status' => 'pending'
        ]);
        
        $response = $this->actingAs($customer2)
            ->putJson("/api/bookings/{$booking->id}/cancel");
            
        $response->assertStatus(403);
    }

    public function test_double_booking_prevention()
    {
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create([
            'available_quantity' => 10,
            'price' => 100
        ]);
        
        // First booking
        $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 2
            ])
            ->assertStatus(201);
            
        // Second booking for same ticket
        $response = $this->actingAs($customer)
            ->postJson("/api/tickets/{$ticket->id}/bookings", [
                'quantity' => 3
            ]);
            
        $response->assertStatus(409);
    }
}