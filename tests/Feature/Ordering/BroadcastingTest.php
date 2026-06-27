<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\Ordering\OrderCreated;
use App\Events\Ordering\OrderStatusUpdated;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\RestaurantSettings;
use App\Models\User;
use App\Services\Ordering\OrderStatusService;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    RestaurantSettings::query()->create([
        'accepting_orders' => true,
        'delivery_enabled' => true,
        'pickup_enabled' => true,
    ]);

    app(RestaurantSettingsService::class)->clearCache();
});

function createBroadcastTestOrder(array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-0200',
        'tracking_token' => (string) Str::uuid(),
        'status' => OrderStatus::Pending,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Milan',
        'phone' => '0611111111',
        'payment_method' => 'cash',
        'subtotal' => 1200,
        'total' => 1200,
    ], $attributes));

    OrderItem::query()->create([
        'order_id' => $order->id,
        'name' => 'Pizza',
        'quantity' => 1,
        'unit_price' => 1200,
        'line_total' => 1200,
    ]);

    OrderStatusLog::query()->create([
        'order_id' => $order->id,
        'from_status' => null,
        'to_status' => OrderStatus::Pending,
        'note' => 'Test.',
        'created_at' => now(),
    ]);

    return $order->fresh();
}

test('checkout dispatches order created broadcast event', function (): void {
    Event::fake([OrderCreated::class]);

    $category = Category::query()->create([
        'name' => 'Paste',
        'slug' => 'paste-broadcast',
        'type' => ProductType::Food,
        'sort_order' => 0,
    ]);

    $item = MenuItem::query()->create([
        'category_id' => $category->id,
        'name' => 'Carbonara',
        'price' => 890,
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->postJson(route('cart.add'), [
        'menu_item_id' => $item->id,
        'quantity' => 1,
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Marko',
        'phone' => '0631234567',
        'fulfillment_type' => FulfillmentType::Pickup->value,
    ])->assertRedirect();

    Event::assertDispatched(OrderCreated::class);
});

test('order status service dispatches order status updated event', function (): void {
    Event::fake([OrderStatusUpdated::class]);

    $order = createBroadcastTestOrder();

    app(OrderStatusService::class)->accept($order, 45);

    Event::assertDispatched(OrderStatusUpdated::class);
});

test('order created broadcasts on private kitchen channel', function (): void {
    $order = createBroadcastTestOrder();
    $event = new OrderCreated($order);

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastOn()[0]->name)->toBe('private-kitchen.orders')
        ->and($event->broadcastAs())->toBe('OrderCreated');
});

test('order status updated broadcasts on customer and kitchen channels', function (): void {
    $order = createBroadcastTestOrder();
    $event = new OrderStatusUpdated($order);

    expect($event->broadcastOn())->toHaveCount(2)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(Channel::class)
        ->and($event->broadcastOn()[0]->name)->toBe('order.'.$order->tracking_token)
        ->and($event->broadcastOn()[1])->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastAs())->toBe('OrderStatusUpdated');
});

test('order tracking status endpoint returns live payload', function (): void {
    $order = createBroadcastTestOrder([
        'status' => OrderStatus::Accepted,
        'estimated_ready_at' => now()->addMinutes(30),
        'accepted_at' => now(),
    ]);

    $this->getJson(route('order.tracking.status', $order->tracking_token))
        ->assertOk()
        ->assertJsonPath('status', OrderStatus::Accepted->value)
        ->assertJsonPath('order_number', $order->order_number)
        ->assertJsonStructure([
            'timeline',
            'current_index',
            'estimated_ready_at_time',
        ]);
});

test('authenticated user can access kitchen orders broadcast channel', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-kitchen.orders',
    ]);

    $response->assertOk();
});

test('guest cannot access kitchen orders broadcast channel', function (): void {
    config(['broadcasting.default' => 'reverb']);

    $this->postJson('/broadcasting/auth', [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-kitchen.orders',
    ])->assertForbidden();
});
