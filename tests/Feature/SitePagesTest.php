<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders public site pages', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/',
    '/jelovnik',
    '/karta-pica',
    '/proslave',
    '/dostava',
    '/o-nama',
]);

it('renders branded 404 page for unknown routes', function (): void {
    $html = $this->get('/nepostojeca-stranica')->assertNotFound()->getContent();

    expect($html)
        ->toContain('Ova stranica nije na')
        ->toContain('Jelovnik')
        ->toContain('noindex, follow');
});
