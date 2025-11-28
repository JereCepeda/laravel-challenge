<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'slug' => $this->faker->unique()->randomElement(['admin','checker']),
            'description' => $this->faker->sentence(),
            'permissions' => json_encode($this->faker->randomElements(
                ['manage_events', 'manage_users', 'validate_tickets', 'scan_qr_codes', 'view_ticket_details', 'generate_reports', 'manage_settings'],
                $this->faker->numberBetween(1,7))
            ),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
