<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Ordering\OrderAutoStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createAutoStatusOrder(array $attributes = []): Order
{
    return Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-0100',
        'tracking_token' => (string) Str::uuid(),
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Test Kupac',
        'phone' => '0631234567',
        'payment_method' => 'cash',
        'subtotal' => 1000,
        'total' => 1000,
    ], $attributes));
}

test('auto status moves accepted order to preparing after threshold', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Accepted,
        'accepted_at' => now()->subMinutes(10),
        'estimated_ready_at' => now()->addMinutes(20),
    ]);

    $service = app(OrderAutoStatusService::class);

    expect($service->shouldMarkPreparing($order))->toBeTrue();

    $processed = $service->processDueTransitions();

    expect($processed)->toBe(1)
        ->and($order->refresh()->status)->toBe(OrderStatus::Preparing);
});

test('auto status moves preparing order to ready at eta', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Preparing,
        'accepted_at' => now()->subMinutes(45),
        'estimated_ready_at' => now()->subMinute(),
    ]);

    $service = app(OrderAutoStatusService::class);

    expect($service->shouldMarkReady($order))->toBeTrue();

    $processed = $service->processDueTransitions();

    expect($processed)->toBe(1)
        ->and($order->refresh()->status)->toBe(OrderStatus::Ready);
});

test('auto status skips accepted order before preparing threshold', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Accepted,
        'accepted_at' => now()->subSeconds(30),
        'estimated_ready_at' => now()->addMinutes(30),
    ]);

    $service = app(OrderAutoStatusService::class);

    expect($service->shouldMarkPreparing($order))->toBeFalse()
        ->and($service->processDueTransitions())->toBe(0)
        ->and($order->refresh()->status)->toBe(OrderStatus::Accepted);
});

test('auto status moves ready pickup order to completed after delay', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Ready,
        'accepted_at' => now()->subMinutes(47),
        'estimated_ready_at' => now()->subMinutes(3),
    ]);

    $service = app(OrderAutoStatusService::class);

    expect($service->shouldComplete($order))->toBeTrue();

    $processed = $service->processDueTransitions();

    expect($processed)->toBe(1)
        ->and($order->refresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->completed_at)->not->toBeNull();
});

test('auto status completes delivery order via out for delivery', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Ready,
        'fulfillment_type' => FulfillmentType::Delivery,
        'address' => 'Batajnica 12',
        'accepted_at' => now()->subMinutes(47),
        'estimated_ready_at' => now()->subMinutes(3),
    ]);

    $service = app(OrderAutoStatusService::class);

    expect($service->processDueTransitions())->toBe(2);

    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->completed_at)->not->toBeNull();
});

test('auto status does not complete ready order before delay', function (): void {
    config(['ordering.auto_status.enabled' => true]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Ready,
        'accepted_at' => now()->subMinutes(30),
        'estimated_ready_at' => now()->subMinute(),
    ]);

    expect(app(OrderAutoStatusService::class)->shouldComplete($order))->toBeFalse()
        ->and($order->refresh()->status)->toBe(OrderStatus::Ready);
});

test('auto status can be disabled', function (): void {
    config(['ordering.auto_status.enabled' => false]);

    $order = createAutoStatusOrder([
        'status' => OrderStatus::Preparing,
        'accepted_at' => now()->subMinutes(45),
        'estimated_ready_at' => now()->subMinute(),
    ]);

    expect(app(OrderAutoStatusService::class)->processDueTransitions())->toBe(0)
        ->and($order->refresh()->status)->toBe(OrderStatus::Preparing);
});
