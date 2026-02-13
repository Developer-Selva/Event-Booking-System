<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $status = fake()->randomElement(['success', 'failed', 'refunded']);
        
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 50, 500),
            'status' => $status,
            'transaction_id' => $status === 'success' ? fake()->uuid() : null,
            'payment_response' => [
                'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'payment_method' => fake()->randomElement(['card', 'bank_transfer', 'cash']),
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function successful()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'success',
                'transaction_id' => fake()->uuid(),
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'transaction_id' => null,
                'payment_response' => [
                    'error' => 'Insufficient funds',
                    'error_code' => 'insufficient_funds',
                ],
            ];
        });
    }

    public function refunded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'refunded',
                'payment_response' => [
                    'refunded_at' => now(),
                    'refund_reason' => 'Customer requested',
                ],
            ];
        });
    }
}