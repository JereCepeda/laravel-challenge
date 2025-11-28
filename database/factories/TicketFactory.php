<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_code' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'event_name' => $this->faker->sentence(3),
            'event_date' => $this->faker->dateTimeBetween('+1 days', '+1 year'),
            'sector' => $this->faker->randomElement(['VIP', 'General', 'Balcony']),
            'is_validated' => false,
            'validated_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
