<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dob = $this->faker->dateTimeBetween('-60 years', '-18 years');
        $gender = $this->faker->randomElement(['male', 'female']);


        $height = $this->faker->numberBetween(145, 195);
        $weight = $this->faker->randomFloat(1, 40, 120);

        return [
            'name'         => $this->faker->name($gender === 'male' ? 'male' : 'female'),
            'email'        => $this->faker->unique()->safeEmail(),

            'password'     => static::$password ??= Hash::make('password'),
            'date_of_birth' => Carbon::instance($dob)->toDateString(),
            'gender'       => $gender,
            'height_cm'    => $height,
            'weight_kg'    => $weight,
            'role'         => 'user',
            'created_at'   => $this->faker->dateTimeThisYear(),
            'updated_at'   => $this->faker->dateTimeThisYear(),
            'activity'     => $this->faker->randomElement(['jarang', 'olahraga_ringan', 'olahraga_sedang', 'olahraga_berat', 'sangat_berat']),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn() => ['role' => 'admin']);
    }

    /** State: pria/wanita (opsional) */
    public function male(): self
    {
        return $this->state(fn() => ['gender' => 'male']);
    }

    public function female(): self
    {
        return $this->state(fn() => ['gender' => 'femal']);
    }
}
