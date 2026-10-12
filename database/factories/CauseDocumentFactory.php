<?php

namespace Database\Factories;

use App\Models\Cause;
use App\Models\CauseDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CauseDocument>
 */
class CauseDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cause_id' => Cause::factory(),
            'file_path' => 'causes/1/'.fake()->uuid().'.jpg',
        ];
    }
}
