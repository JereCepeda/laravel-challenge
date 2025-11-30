<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InvitationRedemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invitation_id' => $this->faker->unique()->regexify('INV-[A-Z0-9]{8}'),
            'event_name' => $this->faker->sentence(3),
            'event_date' => $this->faker->dateTimeBetween('+1 days', '+1 year'),
            'sector' => $this->faker->randomElement(['A1', 'B2', 'C3', 'D4']),
            'guest_count' => $this->faker->numberBetween(1, 10),
            'tickets_generated' => $this->faker->numberBetween(1, 10),
            'redeemed_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'redeemed_ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => [
                'notes' => $this->faker->sentence(),
                'source' => $this->faker->randomElement(['email', 'social_media', 'referral']),
            ],
        ];
    }
}
