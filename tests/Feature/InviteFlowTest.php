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

it('respects email restriction on invite', function () {
    $admin = User::factory()->admin()->create();
    $invite = Invite::factory()->create([
        'created_by' => $admin->id,
        'email' => 'specific@example.com',
    ]);

    $response = $this->post(route('register'), [
        'name' => 'User',
        'email' => 'other@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite' => $invite->code,
    ]);

    $response->assertSessionHasErrors('email');
});
