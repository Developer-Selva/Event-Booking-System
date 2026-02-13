<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition()
    {
        $ticketTypes = ['VIP', 'Standard', 'Early Bird', 'Student'];
        $prices = [
            'VIP' => fake()->randomFloat(2, 200, 500),
            'Standard' => fake()->randomFloat(2, 50, 150),
            'Early Bird' => fake()->randomFloat(2, 30, 100),
            'Student' => fake()->randomFloat(2, 25, 80),
        ];
        
        $type = fake()->randomElement($ticketTypes);
        $quantity = fake()->numberBetween(50, 500);
        
        return [
            'type' => $type,
            'price' => $prices[$type],
            'quantity' => $quantity,
            'available_quantity' => fake()->numberBetween(10, $quantity),
            'event_id' => Event::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function vip()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'VIP',
                'price' => fake()->randomFloat(2, 200, 500),
            ];
        });
    }

    public function standard()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'Standard',
                'price' => fake()->randomFloat(2, 50, 150),
            ];
        });
    }

    public function soldOut()
    {
        return $this->state(function (array $attributes) {
            return [
                'available_quantity' => 0,
            ];
        });
    }
}