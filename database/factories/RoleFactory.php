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
                ['view_statistics','view_reports','view_redemptions_history','validate_tickets','check_ticket_status'],
                $this->faker->numberBetween(1,5))
            ),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
