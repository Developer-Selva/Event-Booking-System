<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        $quantity = fake()->numberBetween(1, 5);
        
        return [
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
            'quantity' => $quantity,
            'total_amount' => 0, // Will be calculated
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Booking $booking) {
            if ($booking->ticket) {
                $booking->total_amount = $booking->ticket->price * $booking->quantity;
            }
        })->afterCreating(function (Booking $booking) {
            if ($booking->ticket && !$booking->total_amount) {
                $booking->total_amount = $booking->ticket->price * $booking->quantity;
                $booking->save();
            }
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
            ];
        });
    }

    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}