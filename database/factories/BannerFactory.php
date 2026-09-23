<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => 'banners/placeholder.jpg',
            'title' => fake()->sentence(3),
            'link' => fake()->url(),
            'sort_order' => 0,
            'active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
