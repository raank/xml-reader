<?php

namespace Database\Factories\V1;

use Raank\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @category database
 * @package Raank\Database\Factories
 * @subpackage V1\UserFactory
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => \sprintf(
                '%s%s%s%s',
                rand(100, 999),
                rand(100, 999),
                rand(100, 999),
                rand(10, 99)
            ),
            'password' => Str::random(10),
            'remember_token' => Str::random(32)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
