<?php

use App\Models\Invite;
use App\Models\User;

it('creates invite via admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.invites.store'), ['email' => 'invited@example.com'])
        ->assertRedirect();

    expect(Invite::where('email', 'invited@example.com')->exists())->toBeTrue();
});


