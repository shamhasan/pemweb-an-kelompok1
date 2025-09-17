<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['aktif', 'selesai']);
        $started = $this->faker->dateTimeBetween('-14 days', '-1 days');
        $ended = $status === 'selesai'
            ? $this->faker->dateTimeBetween($started, 'now')
            : null;

        return [
            'user_id' => User::factory(),
            'status' => $status,
            'started_at' => $started,
            'ended_at' => $ended,
        ];
    }

    public function forUser(User $user): self
    {
        return $this->state(fn() => ['user_id' => $user->id]);
    }
}
