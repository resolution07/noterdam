<?php

use App\Models\Project;
use App\Models\User;

it('allows owner to manage project', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);

    expect($owner->can('update', $project))->toBeTrue();
    expect($owner->can('delete', $project))->toBeTrue();
    expect($owner->can('manageMembers', $project))->toBeTrue();
});

it('allows editor to create notes but not delete project', function () {
    $owner = User::factory()->create();
    $editor = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($editor->id, ['role' => 'editor']);

    expect($editor->can('createNote', $project))->toBeTrue();
    expect($editor->can('delete', $project))->toBeFalse();
});

it('allows viewer to view but not edit', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    $project->members()->attach($viewer->id, ['role' => 'viewer']);

    expect($viewer->can('view', $project))->toBeTrue();
    expect($viewer->can('createNote', $project))->toBeFalse();
});
