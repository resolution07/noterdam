<?php

use App\Models\User;

it('logs in with valid credentials via api', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user'])
        ->assertJsonPath('user.email', $user->email);
});

it('throttles after 5 failed attempts via api', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);
    }

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
