<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Демо Пользователь',
            'email' => 'demo@noterdam.local',
            'password' => bcrypt('password'),
            'is_admin' => true,
            'settings' => ['theme' => 'dark'],
        ]);

        $project = Project::factory()->create([
            'owner_id' => $demo->id,
            'name' => 'Демо проект',
            'slug' => 'demo-project',
            'description' => 'Первый проект для знакомства с Noterdam.',
            'color' => '#34D399',
        ]);

        $tags = Tag::factory()->count(3)->create();

        $notes = Note::factory()->count(3)->create([
            'project_id' => $project->id,
            'author_id' => $demo->id,
        ]);

        foreach ($notes as $note) {
            $note->tags()->attach($tags->random(rand(1, 2)));
        }
    }
}
