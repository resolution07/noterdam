<?php

namespace Database\Factories;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'code' => Str::random(32),
            'email' => fake()->optional()->safeEmail(),
            'created_by' => User::factory(),
            'used_at' => null,
            'expires_at' => null,
        ];
    }
}
