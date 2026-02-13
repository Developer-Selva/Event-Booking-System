<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function process(Request $request, Booking $booking)
    {
        $request->validate([
            'payment_method' => 'required|in:card,bank_transfer,cash',
            'card_number' => 'required_if:payment_method,card|nullable|string|size:16',
            'cvv' => 'required_if:payment_method,card|nullable|string|size:3',
            'expiry' => 'required_if:payment_method,card|nullable|string'
        ]);

        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($booking->isConfirmed()) {
            throw ValidationException::withMessages([
                'booking' => ['Booking is already confirmed.']
            ]);
        }

        if ($booking->isCancelled()) {
            throw ValidationException::withMessages([
                'booking' => ['Booking is cancelled.']
            ]);
        }

        $payment = $this->paymentService->processPayment($booking, $request->all());

        return response()->json([
            'message' => $payment->isSuccessful() ? 'Payment successful.' : 'Payment failed.',
            'payment' => $payment->load('booking')
        ]);
    }

    public function show(Payment $payment)
    {
        if ($payment->booking->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json($payment->load('booking.ticket.event'));
    }
}