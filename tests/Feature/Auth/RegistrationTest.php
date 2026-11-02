<?php

it('registers a new user via api', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'user'])
        ->assertJsonPath('user.email', 'new@example.com');

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});
