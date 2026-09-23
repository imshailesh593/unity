<?php

namespace Database\Factories;

use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SosAlert>
 */
class SosAlertFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'category' => fake()->randomElement(['blood', 'organ', 'medication', 'other']),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'location' => fake()->city(),
            'contact_info' => fake()->phoneNumber(),
            'status' => 'active',
            'expires_at' => now()->addDays(3),
        ];
    }
}
