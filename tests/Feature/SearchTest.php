<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\User;

it('searches notes within user projects', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $note = Note::factory()->create([
        'project_id' => $project->id,
        'author_id' => $user->id,
        'title' => 'Unique search term',
    ]);

    $this->actingAs($user)
        ->get(route('search', ['q' => 'Unique']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Search/Index')
            ->has('notes', 1)
        );
});
