<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ids = [2, 11, 17, 22, 29, 31, 36, 40, 42, 47, 103, 104, 108];
        return [
            'customer_id' => random_int(1, 20),
            'hotel_id' => fake()->randomElement($ids),
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'number' => random_int(10, 10)
        ];
    }
}