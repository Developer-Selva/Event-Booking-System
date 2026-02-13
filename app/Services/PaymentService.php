<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Jobs\SendBookingConfirmationJob;
use Illuminate\Support\Str;

class PaymentService
{
    public function processPayment(Booking $booking, array $paymentDetails)
    {
        // Check if using test card numbers for predictable testing
        $success = $this->determinePaymentSuccess($paymentDetails);
        
        $paymentData = [
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'status' => $success ? 'success' : 'failed',
            'transaction_id' => $success ? Str::uuid() : null,
            'payment_response' => [
                'timestamp' => now(),
                'method' => $paymentDetails['payment_method'] ?? 'card',
                'last_four' => substr($paymentDetails['card_number'] ?? '4242424242424242', -4),
                'success' => $success
            ]
        ];
        
        $payment = Payment::create($paymentData);
        
        if ($success) {
            $booking->markAsConfirmed();
            $booking->ticket->decreaseAvailability($booking->quantity);
            
            // Dispatch notification job
            SendBookingConfirmationJob::dispatch($booking);
        }
        
        return $payment;
    }
    
    /**
     * Determine if payment should succeed based on card number
     */
    private function determinePaymentSuccess(array $paymentDetails): bool
    {
        $cardNumber = $paymentDetails['card_number'] ?? '';
        
        // Test card numbers for predictable testing (similar to Stripe test cards)
        $failedCards = [
            '4000000000000002', // Generic decline
            '4000000000000069', // Expired card
            '4000000000000127', // Incorrect CVC
        ];
        
        // If it's a known failed test card, return false
        if (in_array($cardNumber, $failedCards)) {
            return false;
        }
        
        // If it's a known success test card, return true
        $successCards = [
            '4242424242424242', // Generic success
            '4000000000000077', // Success with review
        ];
        
        if (in_array($cardNumber, $successCards)) {
            return true;
        }
        
        // For any other card, use random success rate (80% success for testing)
        return rand(0, 10) > 2;
    }
    
    public function processRefund(Payment $payment)
    {
        // Mock refund processing
        $payment->update([
            'status' => 'refunded',
            'payment_response' => array_merge($payment->payment_response ?? [], [
                'refunded_at' => now(),
                'refund_id' => Str::uuid()
            ])
        ]);
        
        $payment->booking->markAsCancelled();
        $payment->booking->ticket->increaseAvailability($payment->booking->quantity);
        
        return $payment;
    }
}