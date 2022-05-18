<?php

declare(strict_types=1);

namespace Rawilk\Webauthn\Tests\Support\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Rawilk\Webauthn\Tests\Support\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('secret'),
            'remember_token' => Str::random(20),
        ];
    }
}
