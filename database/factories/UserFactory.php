<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'registered',
            'has_paid' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_paid' => true,
            'status' => 'pending',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_paid' => true,
            'status' => 'active',
        ]);
    }

    public function author(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_tier' => 'author',
        ]);
    }

    public function organizer(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_tier' => 'organizer',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
