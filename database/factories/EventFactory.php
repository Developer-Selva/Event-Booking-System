<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(3, true),
            'date' => fake()->dateTimeBetween('+1 days', '+6 months'),
            'location' => fake()->city() . ', ' . fake()->country(),
            'created_by' => User::factory(), // This will be overridden in seeder
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function upcoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'date' => fake()->dateTimeBetween('+1 days', '+1 month'),
            ];
        });
    }

    public function past()
    {
        return $this->state(function (array $attributes) {
            return [
                'date' => fake()->dateTimeBetween('-6 months', '-1 days'),
            ];
        });
    }
}