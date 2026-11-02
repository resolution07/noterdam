<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('searches notes within user projects via api', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    $note = Note::factory()->create([
        'project_id' => $project->id,
        'author_id' => $user->id,
        'title' => 'Unique search term',
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/search?q=Unique')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $note->id);
});
