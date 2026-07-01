<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantSettings;
use App\Services\Ordering\CartService;
use App\Services\Ordering\OrderService;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    session()->start();
    session()->flush();

    RestaurantSettings::query()->create([
        'accepting_orders' => true,
        'delivery_enabled' => true,
        'pickup_enabled' => true,
    ]);

    app(RestaurantSettingsService::class)->clearCache();
});

function createMenuItem(array $attributes = []): MenuItem
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

test('cart service adds single price item and calculates subtotal', function (): void {
    $item = createMenuItem();
    $cart = app(CartService::class);

    $cart->add($item, null, 2);

    expect($cart->items())->toHaveCount(1)
        ->and($cart->items()[0]['name'])->toBe('Carbonara')
        ->and($cart->items()[0]['quantity'])->toBe(2)
        ->and($cart->items()[0]['unit_price'])->toBe(890.0)
        ->and($cart->subtotal())->toBe(1780.0)
        ->and($cart->itemCount())->toBe(2);
});

test('cart service merges duplicate items by variant key', function (): void {
    $item = createMenuItem();
    $cart = app(CartService::class);

    $cart->add($item, null, 1);
    $cart->add($item, null, 2);

    expect($cart->items())->toHaveCount(1)
        ->and($cart->items()[0]['quantity'])->toBe(3)
        ->and($cart->subtotal())->toBe(2670.0);
});

test('cart service requires pizza variant label', function (): void {
    $item = createMenuItem([
        'name' => 'Margherita',
        'price' => null,
        'price_variants' => [
            ['label' => '25 cm', 'price' => 850],
            ['label' => '35 cm', 'price' => 1150],
        ],
    ]);

    $cart = app(CartService::class);

    expect(fn () => $cart->add($item))
        ->toThrow(ValidationException::class);
});

test('cart service adds pizza with selected variant price', function (): void {
    $item = createMenuItem([
        'name' => 'Margherita',
        'price' => null,
        'price_variants' => [
            ['label' => '25 cm', 'price' => 850],
            ['label' => '35 cm', 'price' => 1150],
        ],
    ]);

    $cart = app(CartService::class);
    $cart->add($item, '35 cm', 1);

    expect($cart->items()[0]['variant_label'])->toBe('35 cm')
        ->and($cart->items()[0]['unit_price'])->toBe(1150.0)
        ->and($cart->subtotal())->toBe(1150.0);
});

test('cart service blocks add when restaurant is not accepting orders', function (): void {
    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $item = createMenuItem();
    $cart = app(CartService::class);

    expect(fn () => $cart->add($item))
        ->toThrow(ValidationException::class);
});

test('cart service updates and removes items', function (): void {
    $item = createMenuItem();
    $cart = app(CartService::class);
    $cart->add($item, null, 1);

    $key = $cart->items()[0]['key'];

    $cart->update($key, 3);
    expect($cart->items()[0]['quantity'])->toBe(3);

    $cart->remove($key);
    expect($cart->isEmpty())->toBeTrue();
});

test('order service creates order with item snapshots and clears cart', function (): void {
    $item = createMenuItem([
        'name' => 'Margherita',
        'price' => null,
        'price_variants' => [
            ['label' => '35 cm', 'price' => 1150],
        ],
    ]);

    $cart = app(CartService::class);
    $cart->add($item, '35 cm', 2);

    $order = app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'fulfillment_type' => FulfillmentType::Pickup,
        'notes' => 'Bez luka',
    ]);

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->fulfillment_type)->toBe(FulfillmentType::Pickup)
        ->and($order->customer_name)->toBe('Marko Marković')
        ->and($order->total)->toBe('2300.00')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->name)->toBe('Margherita')
        ->and($order->items->first()->variant_label)->toBe('35 cm')
        ->and($order->items->first()->unit_price)->toBe('1150.00')
        ->and($order->statusLogs)->toHaveCount(1)
        ->and($cart->isEmpty())->toBeTrue();

    $item->update(['name' => 'Promenjeno ime']);

    $order->refresh();
    expect($order->items->first()->name)->toBe('Margherita');
});

test('order service requires address for delivery', function (): void {
    $item = createMenuItem();
    app(CartService::class)->add($item);

    expect(fn () => app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery,
    ]))->toThrow(ValidationException::class);
});

test('order service applies delivery fee for batajnica', function (): void {
    $item = createMenuItem(['price' => 1500]);
    app(CartService::class)->add($item, null, 1);

    $order = app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery,
        'address' => 'Ulica 1',
    ]);

    expect($order->subtotal)->toBe('1500.00')
        ->and($order->delivery_fee)->toBe('250.00')
        ->and($order->total)->toBe('1750.00')
        ->and($order->delivery_area)->toBe('batajnica');
});

test('order service enforces delivery minimum order amount', function (): void {
    config(['ordering.delivery_minimum_order_amount' => 2000]);

    $item = createMenuItem(['price' => 890]);
    app(CartService::class)->add($item, null, 1);

    expect(fn () => app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery,
        'address' => 'Ulica 1',
    ]))->toThrow(ValidationException::class);

    expect(fn () => app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Delivery,
        'delivery_area' => 'busije',
        'address' => 'Ulica 1',
    ]))->toThrow(ValidationException::class);

    expect(fn () => app(OrderService::class)->createFromCheckout([
        'customer_name' => 'Ana Anić',
        'phone' => '0641112233',
        'fulfillment_type' => FulfillmentType::Pickup,
    ]))->not->toThrow(ValidationException::class);
});

test('order service generates sequential order numbers per day', function (): void {
    $service = app(OrderService::class);

    expect($service->generateOrderNumber())->toMatch('/^R-\d{8}-0001$/');

    Order::query()->create([
        'order_number' => $service->generateOrderNumber(),
        'tracking_token' => (string) str()->uuid(),
        'status' => OrderStatus::Pending,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Test',
        'phone' => '0600000000',
        'payment_method' => 'cash',
        'subtotal' => 100,
        'total' => 100,
    ]);

    expect($service->generateOrderNumber())->toMatch('/^R-\d{8}-0002$/');
});
