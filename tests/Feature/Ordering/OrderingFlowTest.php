<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\Ordering\OrderCreated;
use App\Filament\Pages\KitchenBoard;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\RestaurantSettings;
use App\Models\User;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

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

function createFlowMenuItem(array $attributes = []): MenuItem
{
    $category = Category::query()->create([
        'name' => 'Glavna jela',
        'slug' => 'glavna-jela-'.uniqid(),
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    return MenuItem::query()->create(array_merge([
        'category_id' => $category->id,
        'name' => 'Risotto',
        'price' => 1290,
        'sort_order' => 0,
        'is_active' => true,
    ], $attributes));
}

test('full ordering flow from cart to completed pickup order', function (): void {
    Event::fake([OrderCreated::class]);

    $admin = User::factory()->create();
    $item = createFlowMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 2,
    ])->assertCreated()
        ->assertJsonPath('item_count', 2);

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertSee('Risotto')
        ->assertSee('Naruči');

    $checkoutResponse = $this->post(route('checkout.store'), [
        'customer_name' => 'Petar Petrović',
        'phone' => '0659988776',
        'email' => 'petar@example.com',
        'fulfillment_type' => FulfillmentType::Pickup->value,
        'notes' => 'Bez parmezana',
    ]);

    $order = Order::query()->with('items')->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->customer_name)->toBe('Petar Petrović')
        ->and($order->total)->toBe('2580.00')
        ->and($order->items)->toHaveCount(1);

    $checkoutResponse->assertRedirect(route('order.tracking', $order->tracking_token));

    Event::assertDispatched(OrderCreated::class);

    $this->get(route('order.tracking', $order->tracking_token))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Hvala! Porudžbina je poslata');

    $this->getJson(route('order.tracking.status', $order->tracking_token))
        ->assertOk()
        ->assertJsonPath('status', OrderStatus::Pending->value);

    Livewire::actingAs($admin)
        ->test(KitchenBoard::class)
        ->call('acceptOrder', $order->id, 40)
        ->assertNotified();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Accepted)
        ->and($order->estimated_ready_at)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(KitchenBoard::class)
        ->call('markPreparing', $order->id)
        ->call('markReady', $order->id)
        ->call('completeOrder', $order->id);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->completed_at)->not->toBeNull()
        ->and(OrderStatusLog::query()->where('order_id', $order->id)->count())->toBeGreaterThanOrEqual(5);

    $this->getJson(route('order.tracking.status', $order->tracking_token))
        ->assertOk()
        ->assertJsonPath('status', OrderStatus::Completed->value);

    $this->getJson(route('cart.show'))
        ->assertOk()
        ->assertJsonPath('is_empty', true);
});

test('full delivery ordering flow through kitchen board', function (): void {
    $admin = User::factory()->create();
    $item = createFlowMenuItem();

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 1,
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Ivana Ivić',
        'phone' => '0645566778',
        'fulfillment_type' => FulfillmentType::Delivery->value,
        'address' => 'Batajnica, Ulica 12',
    ])->assertRedirect();

    $order = Order::query()->firstOrFail();

    Livewire::actingAs($admin)
        ->test(KitchenBoard::class)
        ->call('acceptOrder', $order->id, 50)
        ->call('markPreparing', $order->id)
        ->call('markReady', $order->id)
        ->call('markOutForDelivery', $order->id)
        ->call('completeOrder', $order->id);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->fulfillment_type)->toBe(FulfillmentType::Delivery);
});
