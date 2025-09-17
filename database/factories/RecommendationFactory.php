<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recommendation>
 */
class RecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['artikel', 'aktivitas', 'nutrisi']),
            'title' => $this->faker->unique()->sentence(6),
            'description' => $this->faker->paragraph(),
            'related_url' => $this->faker->url(),
            'created_at' => now(),
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }
}
