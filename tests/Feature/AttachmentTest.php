<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('allows member to upload file under limit', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $note = Note::factory()->create(['project_id' => $project->id, 'author_id' => $owner->id]);

    $file = UploadedFile::fake()->create('doc.png', 10);

    $this->actingAs($owner)
        ->post(route('attachments.store', [$project, $note]), ['file' => $file])
        ->assertRedirect();

    expect($note->fresh()->attachments)->toHaveCount(1);
});

it('rejects php file', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $note = Note::factory()->create(['project_id' => $project->id, 'author_id' => $owner->id]);

    $file = UploadedFile::fake()->create('shell.php', 1);

    $this->actingAs($owner)
        ->post(route('attachments.store', [$project, $note]), ['file' => $file])
        ->assertSessionHasErrors('file');
});
