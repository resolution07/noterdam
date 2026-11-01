<?php

use App\Models\User;

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('throttles after 5 failed attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong',
        ]);
    }

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong',
    ]);

    $response->assertSessionHasErrors('email');
});
