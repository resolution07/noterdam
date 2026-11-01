<?php

use App\Services\MarkdownService;

it('renders markdown to html', function () {
    $service = app(MarkdownService::class);
    $html = $service->toHtml('# Hello');

    expect($html)->toContain('<h1>Hello</h1>');
});

it('escapes script tags', function () {
    $service = app(MarkdownService::class);
    $html = $service->toHtml('<script>alert(1)</script>');

    expect($html)->not->toContain('<script>');
});
