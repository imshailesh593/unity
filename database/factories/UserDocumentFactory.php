<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDocument>
 */
class UserDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(UserDocument::TYPES),
            'file_path' => 'documents/1/'.fake()->uuid().'.jpg',
            'status' => 'pending',
        ];
    }
}
