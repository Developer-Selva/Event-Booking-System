<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendBookingConfirmationJob;

class PaymentServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_processing_success()
    {
        Queue::fake();
        
        $paymentService = new PaymentService();
        
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'available_quantity' => 5,
            'price' => 100,
            'quantity' => 10
        ]);
        
        $customer = User::factory()->customer()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200,
            'status' => 'pending'
        ]);
        
        $payment = $paymentService->processPayment($booking, [
            'payment_method' => 'card',
            'card_number' => '4242424242424242' // This card always succeeds
        ]);
        
        // Assert payment was created and succeeded
        $this->assertNotNull($payment);
        $this->assertEquals($booking->id, $payment->booking_id);
        $this->assertEquals('success', $payment->status);
        
        // Assert booking was confirmed
        $this->assertEquals('confirmed', $booking->fresh()->status);
        
        // Assert ticket availability decreased
        $this->assertEquals(3, $ticket->fresh()->available_quantity);
        
        // Assert confirmation email was queued
        Queue::assertPushed(SendBookingConfirmationJob::class, function ($job) use ($booking) {
            return $job->booking->id === $booking->id;
        });
    }

    public function test_payment_processing_failure()
    {
        Queue::fake();
        
        $paymentService = new PaymentService();
        
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'available_quantity' => 5,
            'price' => 100,
            'quantity' => 10
        ]);
        
        $customer = User::factory()->customer()->create();
        
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200,
            'status' => 'pending'
        ]);
        
        $payment = $paymentService->processPayment($booking, [
            'payment_method' => 'card',
            'card_number' => '4000000000000002' // This card always fails
        ]);
        
        // Assert payment failed
        $this->assertEquals('failed', $payment->status);
        
        // Assert booking is still pending
        $this->assertEquals('pending', $booking->fresh()->status);
        
        // Assert ticket availability unchanged
        $this->assertEquals(5, $ticket->fresh()->available_quantity);
        
        // Assert no confirmation email was sent
        Queue::assertNotPushed(SendBookingConfirmationJob::class);
    }

    public function test_process_refund()
    {
        Queue::fake();
        
        $paymentService = new PaymentService();
        
        $organizer = User::factory()->organizer()->create();
        $event = Event::factory()->create(['created_by' => $organizer->id]);
        
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'available_quantity' => 3,
            'price' => 100,
            'quantity' => 10
        ]);
        
        $customer = User::factory()->customer()->create();
        
        $booking = Booking::factory()->confirmed()->create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'total_amount' => 200
        ]);
        
        $payment = \App\Models\Payment::factory()->successful()->create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount
        ]);
        
        $refundedPayment = $paymentService->processRefund($payment);
        
        // Assert payment was refunded
        $this->assertEquals('refunded', $refundedPayment->status);
        
        // Assert booking was cancelled
        $this->assertEquals('cancelled', $booking->fresh()->status);
        
        // Assert ticket availability increased
        $this->assertEquals(5, $ticket->fresh()->available_quantity);
    }
}