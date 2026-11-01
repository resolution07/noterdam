<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->uuid()),
            'description' => fake()->sentence(),
            'color' => fake()->randomElement(['#34D399', '#60A5FA', '#F87171', '#FBBF24', '#A78BFA']),
            'is_archived' => false,
        ];
    }
}
