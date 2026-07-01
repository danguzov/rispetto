<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Livewire\Dashboard\OrderIndex;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\RestaurantSettings;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    RestaurantSettings::query()->create([
        'accepting_orders' => true,
        'delivery_enabled' => true,
        'pickup_enabled' => true,
    ]);

    app(RestaurantSettingsService::class)->clearCache();
});

function createDashboardTestOrder(array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-0099',
        'tracking_token' => (string) Str::uuid(),
        'status' => OrderStatus::Pending,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'payment_method' => 'cash',
        'subtotal' => 1780,
        'total' => 1780,
    ], $attributes));

    OrderItem::query()->create([
        'order_id' => $order->id,
        'name' => 'Margherita',
        'variant_label' => '35 cm',
        'quantity' => 2,
        'unit_price' => 890,
        'line_total' => 1780,
    ]);

    OrderStatusLog::query()->create([
        'order_id' => $order->id,
        'from_status' => null,
        'to_status' => OrderStatus::Pending,
        'note' => 'Porudžbina kreirana.',
        'created_at' => now(),
    ]);

    return $order;
}

test('filament echo is configured when broadcasting uses reverb', function (): void {
    config([
        'broadcasting.default' => 'reverb',
        'filament.broadcasting.echo' => null,
    ]);

    $method = new ReflectionMethod(AppServiceProvider::class, 'configureFilamentEcho');
    $method->invoke(app()->getProvider(AppServiceProvider::class));

    expect(config('filament.broadcasting.echo'))
        ->toBeArray()
        ->and(config('filament.broadcasting.echo.broadcaster'))->toBe('reverb')
        ->and(config('filament.broadcasting.echo.authEndpoint'))->toBe('/broadcasting/auth');
});

test('guest cannot access dashboard orders', function (): void {
    $this->get(route('dashboard.orders.index'))
        ->assertRedirect();
});

test('authenticated user can access dashboard orders', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.orders.index'))
        ->assertOk();
});

test('dashboard accepts pending order with custom eta input', function (): void {
    $user = User::factory()->create();
    $order = createDashboardTestOrder();

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->set('customEtaByOrder.'.$order->id, 60)
        ->call('acceptOrderWithCustomEta', $order->id);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Accepted);
});

test('dashboard accepts pending order with eta', function (): void {
    $user = User::factory()->create();
    $order = createDashboardTestOrder();

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->call('acceptOrder', $order->id, 45);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Accepted)
        ->and($order->estimated_ready_at)->not->toBeNull();
});

test('dashboard toggles accepting orders', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->call('toggleAcceptingOrders');

    expect(app(RestaurantSettingsService::class)->acceptingOrders())->toBeFalse();

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->call('toggleAcceptingOrders');

    expect(app(RestaurantSettingsService::class)->acceptingOrders())->toBeTrue();
});

test('dashboard order index shows created order', function (): void {
    $user = User::factory()->create();
    $order = createDashboardTestOrder();

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->assertSee($order->customer_name)
        ->assertSee('Odbij');
});

test('new orders tab shows pending and in-eta orders only', function (): void {
    $user = User::factory()->create();

    $pending = createDashboardTestOrder(['customer_name' => 'Nova Pending']);
    $inEta = createDashboardTestOrder([
        'order_number' => 'R-'.now()->format('Ymd').'-0100',
        'customer_name' => 'Nova U ETA',
        'status' => OrderStatus::Accepted,
        'estimated_ready_at' => now()->addMinutes(30),
        'accepted_at' => now(),
    ]);
    $expiredEta = createDashboardTestOrder([
        'order_number' => 'R-'.now()->format('Ymd').'-0101',
        'customer_name' => 'Stara Posle ETA',
        'status' => OrderStatus::Preparing,
        'estimated_ready_at' => now()->subMinutes(5),
        'accepted_at' => now()->subMinutes(40),
    ]);

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->assertSet('statusFilter', 'new')
        ->assertSee('Nova Pending')
        ->assertSee('Nova U ETA')
        ->assertDontSee('Stara Posle ETA');

    Livewire::actingAs($user)
        ->test(OrderIndex::class)
        ->call('setStatusFilter', 'today')
        ->assertSee('Nova Pending')
        ->assertSee('Nova U ETA')
        ->assertSee('Stara Posle ETA');
});
