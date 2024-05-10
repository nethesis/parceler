<?php

namespace Database\Factories;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'command' => fake()->sentence(),
            'delay' => fake()->numberBetween(1, 7),
        ];
    }

    /**
     * Indicate that the repository is frozen.
     *
     * @return Factory<Repository>
     */
    public function freeze(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'freeze' => fake()->filePath(),
            ];
        });
    }
}
