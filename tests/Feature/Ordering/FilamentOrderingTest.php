<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Filament\Pages\KitchenBoard;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Widgets\Ordering\AcceptingOrdersWidget;
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

function createFilamentTestOrder(array $attributes = []): Order
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

test('guest cannot access filament orders list', function (): void {
    $this->get(route('filament.admin.resources.porudzbine.index'))
        ->assertRedirect();
});

test('authenticated admin can access orders list and kitchen board', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('filament.admin.resources.porudzbine.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('filament.admin.pages.kuhinja'))
        ->assertOk();
});

test('kitchen board accepts pending order with custom eta input', function (): void {
    $user = User::factory()->create();
    $order = createFilamentTestOrder();

    Livewire::actingAs($user)
        ->test(KitchenBoard::class)
        ->set('customEtaByOrder.'.$order->id, 60)
        ->call('acceptOrderWithCustomEta', $order->id)
        ->assertNotified();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Accepted);
});

test('kitchen board accepts pending order with eta', function (): void {
    $user = User::factory()->create();
    $order = createFilamentTestOrder();

    Livewire::actingAs($user)
        ->test(KitchenBoard::class)
        ->call('acceptOrder', $order->id, 45)
        ->assertNotified();

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Accepted)
        ->and($order->estimated_ready_at)->not->toBeNull();
});

test('accepting orders widget toggles restaurant settings', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AcceptingOrdersWidget::class)
        ->assertSet('data.accepting_orders', true)
        ->set('data.accepting_orders', false)
        ->assertNotified();

    expect(app(RestaurantSettingsService::class)->acceptingOrders())->toBeFalse();
});

test('orders list shows created order', function (): void {
    $user = User::factory()->create();
    $order = createFilamentTestOrder();

    Livewire::actingAs($user)
        ->test(ListOrders::class)
        ->assertCanSeeTableRecords([$order]);
});
