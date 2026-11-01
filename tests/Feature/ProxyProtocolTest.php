<?php

it('trusts X-Forwarded-Proto: https', function () {
    $response = $this->withHeaders([
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-For' => '192.168.1.1',
    ])->get('/login');

    $response->assertOk();
    expect(request()->isSecure())->toBeTrue();
    expect(str_starts_with(url('/'), 'https://'))->toBeTrue();
});
