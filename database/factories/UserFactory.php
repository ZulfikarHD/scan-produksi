<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => fake()->name(),
            // NP 4-6 karakter kombinasi huruf dan angka (contoh: 7426 atau T0087)
            'nip' => fake()->unique()->regexify('[A-Z0-9]{4,6}'),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::PETUGAS,
            'is_active' => true,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State for supervisor role.
     */
    public function supervisor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SUPERVISOR,
        ]);
    }

    /**
     * State for petugas role.
     */
    public function petugas(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PETUGAS,
        ]);
    }

    /**
     * State for inactive status.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => 'secret',
            'two_factor_recovery_codes' => '[]',
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
