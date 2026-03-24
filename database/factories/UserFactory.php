<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     *
     * @var string|null
     */
    protected static $password;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (static::$password === null) {
            static::$password = Hash::make('password');
        }

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified()
    {
        return $this->state(function () {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
