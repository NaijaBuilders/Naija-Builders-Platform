<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

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
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('080########'),
            'company' => fake()->company(),
            'location' => 'Lagos',
            'role' => 'builder',
            'password_hash' => static::$password ??= Hash::make('password'),
            'kyc_status' => 'approved',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_badge' => false,
        ]);
    }

    public function supplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'supplier',
            'kyc_status' => 'pending',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'kyc_status' => 'approved',
        ]);
    }
}
