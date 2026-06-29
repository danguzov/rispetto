<?php

use App\Models\RestaurantSettings;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['ordering.enabled' => true]);

    RestaurantSettings::query()->create([
        'accepting_orders' => true,
        'delivery_enabled' => true,
        'pickup_enabled' => true,
    ]);

    app(RestaurantSettingsService::class)->clearCache();
});

test('delivery page shows online ordering cta when ordering is enabled', function (): void {
    $this->get(route('delivery'))
        ->assertOk()
        ->assertSee('Dostava')
        ->assertSee('Poruči online')
        ->assertDontSee('Naruči preko Alidede')
        ->assertSee(route('menu'), false);
});

test('delivery page hides online ordering cta when ordering is disabled', function (): void {
    config(['ordering.enabled' => false]);

    $this->get(route('delivery'))
        ->assertOk()
        ->assertDontSee('Poruči online')
        ->assertSee('Lično preuzimanje');
});

test('delivery hero shows closed message when restaurant is not accepting orders', function (): void {
    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $this->get(route('delivery'))
        ->assertOk()
        ->assertSee(config('ordering.closed_message'));
});
