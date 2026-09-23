<?php

namespace Database\Factories;

use App\Models\Cause;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Cause>
 */
class CauseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'organizer_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'goal_amount' => fake()->numberBetween(10000, 200000),
            'raised_amount' => 0,
            'status' => 'draft',
            'verified' => false,
        ];
    }
}
