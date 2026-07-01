<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
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

function createCheckoutMenuItem(array $attributes = []): MenuItem
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

test('checkout page shows form when cart has items', function (): void {
    $item = createCheckoutMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 1,
    ]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Završite porudžbinu')
        ->assertSee('Pošalji porudžbinu')
        ->assertSee('Dostava je dostupna isključivo u Batajnica')
        ->assertSee('Adresa u Batajnici')
        ->assertSee('Besplatna dostava za porudžbine preko 3.000 RSD')
        ->assertSee('defer-checkout', false);
});

test('checkout page redirects to cart when empty', function (): void {
    $this->get(route('checkout.show'))
        ->assertRedirect(route('cart.show'));
});

test('checkout creates pickup order and redirects to tracking', function (): void {
    $item = createCheckoutMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 2,
    ]);

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'fulfillment_type' => FulfillmentType::Pickup->value,
        'notes' => 'Bez luka',
    ]);

    $order = Order::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->fulfillment_type)->toBe(FulfillmentType::Pickup)
        ->and($order->customer_name)->toBe('Marko Marković')
        ->and($order->total)->toBe('1780.00')
        ->and($order->items)->toHaveCount(1);

    $response->assertRedirect(route('order.tracking', $order->tracking_token));

    $this->getJson(route('cart.show'))
        ->assertOk()
        ->assertJsonPath('is_empty', true);
});

test('checkout requires address for delivery', function (): void {
    $item = createCheckoutMenuItem();
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'customer_name' => 'Ana Anić',
            'phone' => '0641112233',
            'fulfillment_type' => FulfillmentType::Delivery->value,
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['address']);
});

test('checkout creates delivery order with address', function (): void {
    $item = createCheckoutMenuItem(['price' => 750]);
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery->value,
        'address' => 'Ulica 1',
    ])->assertRedirect();

    expect(Order::query()->first())
        ->fulfillment_type->toBe(FulfillmentType::Delivery)
        ->delivery_area->toBe('batajnica')
        ->subtotal->toBe('750.00')
        ->delivery_fee->toBe('250.00')
        ->total->toBe('1000.00')
        ->address->toBe('Batajnica, Ulica 1');
});

test('checkout applies free delivery above threshold for batajnica', function (): void {
    $item = createCheckoutMenuItem(['price' => 3200]);
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery->value,
        'address' => 'Ulica 1',
    ])->assertRedirect();

    expect(Order::query()->first())
        ->delivery_fee->toBe('0.00')
        ->total->toBe('3200.00');
});

test('checkout rejects delivery outside batajnica when area is tampered', function (): void {
    $item = createCheckoutMenuItem(['price' => 1200]);
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'customer_name' => 'Ana Anić',
            'phone' => '0641112233',
            'fulfillment_type' => FulfillmentType::Delivery->value,
            'delivery_area' => 'busije',
            'address' => 'Ulica 5',
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['fulfillment_type']);
});

test('checkout allows pickup below delivery minimum', function (): void {
    $item = createCheckoutMenuItem(['price' => 350]);
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'fulfillment_type' => FulfillmentType::Pickup->value,
    ])->assertRedirect();

    expect(Order::query()->first())
        ->fulfillment_type->toBe(FulfillmentType::Pickup)
        ->total->toBe('350.00');
});

test('checkout rejects delivery below minimum amount', function (): void {
    $item = createCheckoutMenuItem(['price' => 350]);
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'customer_name' => 'Ana Anić',
            'phone' => '0641112233',
            'fulfillment_type' => FulfillmentType::Delivery->value,
            'address' => 'Ulica 1',
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['cart']);
});

test('tracking page shows order details', function (): void {
    $item = createCheckoutMenuItem();
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'fulfillment_type' => FulfillmentType::Pickup->value,
    ]);

    $order = Order::query()->firstOrFail();

    $this->get(route('order.tracking', $order->tracking_token))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Carbonara')
        ->assertSee('Marko Marković')
        ->assertSee('noindex, nofollow', false);
});

test('checkout submit is blocked when restaurant is closed', function (): void {
    $item = createCheckoutMenuItem();
    $this->postJson(route('cart.add'), ['menu_item_id' => $item->id]);

    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $this->postJson(route('checkout.store'), [
        'customer_name' => 'Marko',
        'phone' => '0600000000',
        'fulfillment_type' => FulfillmentType::Pickup->value,
    ])->assertForbidden();
});
