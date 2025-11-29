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
            'ticket_code' => $this->faker->unique()->regexify('TCK-[A-Z0-9]{8}'),
            'invitation_id' => $this->faker->unique()->regexify('INV-[A-Z0-9]{8}'),
            'event_name' => $this->faker->sentence(3),
            'event_date' => $this->faker->dateTimeBetween('+1 days', '+1 year'),
            'sector' => $this->faker->randomElement(['A1', 'B2', 'C3', 'D4']),
            'is_validated' => false,
            'redeemed_ip' => $this->faker->ipv4()
        ];
    }
}
