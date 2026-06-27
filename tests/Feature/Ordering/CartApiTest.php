<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
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

function createCartMenuItem(array $attributes = []): MenuItem
{
    $category = Category::query()->create([
        'name' => 'Paste',
        'slug' => 'paste-'.uniqid(),
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    return MenuItem::query()->create(array_merge([
        'category_id' => $category->id,
        'name' => 'Carbonara',
        'price' => 890,
        'sort_order' => 0,
        'is_active' => true,
    ], $attributes));
}

test('cart show returns empty cart json', function (): void {
    $response = $this->getJson(route('cart.show'));

    $response->assertOk()
        ->assertJson([
            'items' => [],
            'subtotal' => 0,
            'item_count' => 0,
            'is_empty' => true,
        ]);
});

test('cart add stores item in session and returns json', function (): void {
    $item = createCartMenuItem();

    $response = $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $response->assertCreated()
        ->assertJsonPath('item_count', 2)
        ->assertJsonPath('subtotal', 1780)
        ->assertJsonPath('is_empty', false)
        ->assertJsonPath('items.0.name', 'Carbonara');

    $showResponse = $this->getJson(route('cart.show'));

    $showResponse->assertOk()
        ->assertJsonPath('item_count', 2);
});

test('cart add requires pizza variant label', function (): void {
    $item = createCartMenuItem([
        'name' => 'Margherita',
        'price' => null,
        'price_variants' => [
            ['label' => '35 cm', 'price' => 1150],
        ],
    ]);

    $response = $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('variant_label');
});

test('cart update and remove work via api', function (): void {
    $item = createCartMenuItem();

    $addResponse = $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 1,
    ]);

    $key = $addResponse->json('items.0.key');

    $this->patchJson(route('cart.update', ['key' => $key]), [
        'quantity' => 3,
    ])->assertOk()
        ->assertJsonPath('item_count', 3)
        ->assertJsonPath('subtotal', 2670);

    $this->deleteJson(route('cart.remove', ['key' => $key]))
        ->assertOk()
        ->assertJson([
            'items' => [],
            'subtotal' => 0,
            'item_count' => 0,
            'is_empty' => true,
        ]);
});

test('cart mutations are blocked when restaurant is not accepting orders', function (): void {
    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $item = createCartMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
    ])->assertForbidden()
        ->assertJsonPath('message', config('ordering.closed_message'));

    $this->getJson(route('cart.show'))->assertOk();
});

test('cart mutations are blocked when ordering is disabled in config', function (): void {
    config(['ordering.enabled' => false]);

    $item = createCartMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
    ])->assertForbidden();

    $this->get(route('cart.show'))->assertNotFound();
    $this->get(route('checkout.show'))->assertNotFound();
    $this->getJson(route('cart.show'))->assertNotFound();
});
