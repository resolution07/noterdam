<?php

it('renders the registration page', function () {
    $this->get(route('register'))->assertOk();
});

it('registers a new user', function () {
    $response = $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    $this->assertAuthenticated();
});
