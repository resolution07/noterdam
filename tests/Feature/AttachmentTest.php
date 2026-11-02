<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

it('allows member to upload file under limit via api', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $note = Note::factory()->create(['project_id' => $project->id, 'author_id' => $owner->id]);

    Sanctum::actingAs($owner);

    $file = UploadedFile::fake()->create('doc.png', 10);

    $this->postJson("/api/projects/{$project->id}/notes/{$note->id}/attachments", ['file' => $file])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'original_name', 'mime_type', 'size']]);

    expect($note->fresh()->attachments)->toHaveCount(1);
});

it('rejects php file via api', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $note = Note::factory()->create(['project_id' => $project->id, 'author_id' => $owner->id]);

    Sanctum::actingAs($owner);

    $file = UploadedFile::fake()->create('shell.php', 1);

    $this->postJson("/api/projects/{$project->id}/notes/{$note->id}/attachments", ['file' => $file])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');
});
