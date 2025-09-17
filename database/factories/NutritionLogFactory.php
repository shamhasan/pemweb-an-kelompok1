<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NutritionLog>
 */
class NutritionLogFactory extends Factory
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
            'food_name' => $this->faker->word(),
            'calories' => $this->faker->numberBetween(50, 2000),
            'protein_g' => $this->faker->numberBetween(0, 100),
            'carbs_g' => $this->faker->numberBetween(0, 300),
            'fat_g' => $this->faker->numberBetween(0, 150),
            'meal_type' => $this->faker->randomElement(['sarapan', 'makan_siang', 'makan_malam', 'camilan']),
            'consumed_at' => Carbon::instance($this->faker->dateTimeThisYear()),
        ];
    }

    public function forUser(User $user)
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }
}
