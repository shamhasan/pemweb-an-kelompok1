<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
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
            'record_type' => $this->faker->randomElement(['alergi', 'penyakit', 'vaksinasi', 'operasi']),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'recorded_at' => Carbon::instance($this->faker->dateTimeThisYear()),
        ];
    }

    public function forUser(User $user)
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }
}
