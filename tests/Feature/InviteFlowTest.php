<?php

use App\Models\Invite;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates invite via admin api', function () {
    $admin = User::factory()->admin()->create();

    Sanctum::actingAs($admin);

    $this->postJson('/api/admin/invites', ['email' => 'invited@example.com'])
        ->assertCreated()
        ->assertJsonPath('data.email', 'invited@example.com');

    expect(Invite::where('email', 'invited@example.com')->exists())->toBeTrue();
});
