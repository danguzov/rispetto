<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createHistoryOrder(array $attributes = []): Order
{
    return Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-'.random_int(100, 999),
        'tracking_token' => (string) Str::uuid(),
        'status' => OrderStatus::Completed,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Marko Marković',
        'phone' => '0631234567',
        'payment_method' => 'cash',
        'subtotal' => 1780,
        'total' => 1780,
        'completed_at' => now(),
    ], $attributes));
}

test('guest cannot access order history page', function (): void {
    $this->get(route('dashboard.orders.history'))
        ->assertRedirect();
});

test('authenticated user can access order history page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.orders.history'))
        ->assertOk()
        ->assertSee('Istorija porudžbina');
});

test('order history groups orders by month and day', function (): void {
    $user = User::factory()->create();

    $older = createHistoryOrder(['customer_name' => 'Stari Kupac']);
    $older->forceFill([
        'created_at' => now()->subMonth()->setDay(10)->setTime(12, 30),
        'completed_at' => now()->subMonth()->setDay(10)->setTime(13, 0),
    ])->save();

    $today = createHistoryOrder(['customer_name' => 'Današnji Kupac']);

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Dashboard\OrderHistoryIndex::class)
        ->assertSee(now()->locale('sr_Latn')->translatedFormat('F Y.'))
        ->call('selectMonth', now()->format('Y-m'))
        ->assertSee(now()->locale('sr_Latn')->translatedFormat('j. F'))
        ->assertSee('Današnji Kupac')
        ->assertSee(number_format((float) $today->total, 0, ',', '.').' din');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Dashboard\OrderHistoryIndex::class)
        ->call('selectMonth', now()->subMonth()->format('Y-m'))
        ->call('selectDay', now()->subMonth()->setDay(10)->format('Y-m-d'))
        ->assertSee('Stari Kupac')
        ->assertSee($older->phone);
});

test('orders index links to history page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.orders.index'))
        ->assertOk()
        ->assertSee(route('dashboard.orders.history'), false)
        ->assertSee('Istorija porudžbina');
});
