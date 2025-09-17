<?php

namespace Database\Factories;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sender_type = $this->faker->randomElement(['user', 'ai']);

        return [
            'consultation_id' => Consultation::factory(),
            'sender_type' => $sender_type,
            'content' => $this->faker->sentence(2, true),
        ];
    }
}
