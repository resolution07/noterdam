<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('updates password via api', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Пароль обновлён.']);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
