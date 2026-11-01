<?php

use App\Models\Invite;
use App\Models\User;

it('requires a valid invite to register', function () {
    $response = $this->get(route('register', ['invite' => 'invalid']));
    $response->assertStatus(404);
});

it('registers a user with a valid invite', function () {
    $admin = User::factory()->create();
    $invite = Invite::factory()->create([
        'created_by' => $admin->id,
        'email' => null,
    ]);

    $response = $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'invite' => $invite->code,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    expect($invite->fresh()->isUsed())->toBeTrue();
});
