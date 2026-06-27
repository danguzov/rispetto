<?php

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\RestaurantSettings;
use App\Services\Ordering\RestaurantSettingsService;
use App\Support\SitePageCache;
use App\Support\SiteScripts;
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

function createOrderingMenuItem(array $attributes = []): MenuItem
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

test('menu page renders add to cart controls when ordering is enabled', function (): void {
    createOrderingMenuItem();

    SitePageCache::menuCategories();

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-cart-add', false)
        ->assertSee('Dodaj u korpu')
        ->assertSee('data-cart-add-url', false)
        ->assertSee('defer-cart', false);
});

test('menu page hides add to cart controls when ordering is disabled', function (): void {
    config(['ordering.enabled' => false]);
    createOrderingMenuItem();

    $this->get(route('menu'))
        ->assertOk()
        ->assertDontSee('data-cart-add', false)
        ->assertDontSee('data-cart-add-url', false);
});

test('home page renders add to cart on featured menu items', function (): void {
    createOrderingMenuItem([
        'name' => 'Tiramisu',
        'featured' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-cart-add', false)
        ->assertSee('defer-cart', false);
});

test('header renders cart link with badge target', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-cart-link', false)
        ->assertSee('data-cart-badge', false);
});

test('cart page renders items from session cart', function (): void {
    $item = createOrderingMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 2,
    ])->assertCreated();

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertSee('Carbonara')
        ->assertSee('1.780 RSD')
        ->assertSee('data-cart-page', false)
        ->assertSee('data-cart-increase', false)
        ->assertSee('data-cart-decrease', false)
        ->assertSee('data-cart-remove', false)
        ->assertSee('Naruči');
});

test('cart page update and remove work without full page reload', function (): void {
    $item = createOrderingMenuItem();

    $addResponse = $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $key = $addResponse->json('items.0.key');

    $this->patchJson(route('cart.update', ['key' => $key]), [
        'quantity' => 3,
    ])->assertOk()
        ->assertJsonPath('item_count', 3)
        ->assertJsonPath('subtotal', 2670);

    $this->deleteJson(route('cart.remove', ['key' => $key]))
        ->assertOk()
        ->assertJsonPath('is_empty', true);
});

test('checkout page redirects to cart when empty', function (): void {
    $this->get(route('checkout.show'))
        ->assertRedirect(route('cart.show'));
});

test('checkout page is reachable with items in cart', function (): void {
    $item = createOrderingMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 1,
    ]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Završite porudžbinu')
        ->assertSee('Pošalji porudžbinu');
});

test('cart page disables checkout cta when restaurant is closed', function (): void {
    $item = createOrderingMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
    ]);

    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertSee('disabled', false)
        ->assertDontSee('href="'.route('checkout.show').'"', false);
});

test('site scripts include cart defer entry on ordering routes', function (): void {
    $this->get(route('home'))->assertOk();

    expect(SiteScripts::deferEntries())->toContain('resources/js/defer-cart.js');

    $this->get(route('menu'))->assertOk();

    expect(SiteScripts::deferEntries())->toContain('resources/js/defer-cart.js')
        ->and(SiteScripts::deferEntries())->toContain('resources/js/defer-catalog.js');
});

test('site scripts omit cart defer entry when ordering is disabled', function (): void {
    config(['ordering.enabled' => false]);

    $this->get(route('home'))->assertOk();

    expect(SiteScripts::deferEntries())->not->toContain('resources/js/defer-cart.js')
        ->and(SiteScripts::deferEntries())->not->toContain('resources/js/defer-ordering-availability.js');

    $this->get(route('menu'))->assertOk();

    expect(SiteScripts::deferEntries())->not->toContain('resources/js/defer-cart.js')
        ->and(SiteScripts::deferEntries())->toContain('resources/js/defer-catalog.js');
});
