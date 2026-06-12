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
    '/o-nama',
]);
