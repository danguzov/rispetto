<?php

use App\Support\Favicon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes favicon links on the public site', function (): void {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->toContain(Favicon::url('favicon.ico'));
    expect($html)->toContain(Favicon::url('favicon.svg'));
});

it('exposes favicon links on the dashboard', function (): void {
    $html = $this->get('/dashboard/login')->assertOk()->getContent();

    expect($html)->toContain(Favicon::url('favicon.ico'));
});

it('keeps favicon files in the public directory', function (): void {
    foreach (['favicon.ico', 'favicon.svg', 'favicon-32.png', 'favicon-16.png', 'apple-touch-icon.png'] as $file) {
        expect(public_path($file))->toBeReadableFile();
    }
});
