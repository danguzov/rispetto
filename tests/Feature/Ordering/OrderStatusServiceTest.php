<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Services\Ordering\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createStatusTestOrder(array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-0001',
        'tracking_token' => (string) Str::uuid(),
        'status' => OrderStatus::Pending,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Ana Anić',
        'phone' => '0612345678',
        'payment_method' => 'cash',
        'subtotal' => 890,
        'total' => 890,
    ], $attributes));

    OrderItem::query()->create([
        'order_id' => $order->id,
        'name' => 'Carbonara',
        'quantity' => 1,
        'unit_price' => 890,
        'line_total' => 890,
    ]);

    OrderStatusLog::query()->create([
        'order_id' => $order->id,
        'from_status' => null,
        'to_status' => OrderStatus::Pending,
        'note' => 'Test porudžbina.',
        'created_at' => now(),
    ]);

    return $order->fresh(['items', 'statusLogs']);
}

test('accept sets eta and logs status change', function (): void {
    $order = createStatusTestOrder();

    $updated = app(OrderStatusService::class)->accept($order, 45);

    expect($updated->status)->toBe(OrderStatus::Accepted)
        ->and($updated->accepted_at)->not->toBeNull()
        ->and($updated->estimated_ready_at)->not->toBeNull()
        ->and($updated->statusLogs)->toHaveCount(2)
        ->and($updated->statusLogs->last()->to_status)->toBe(OrderStatus::Accepted);
});

test('pickup order follows kitchen workflow', function (): void {
    $service = app(OrderStatusService::class);
    $order = createStatusTestOrder();

    $order = $service->accept($order, 30);
    $order = $service->markPreparing($order);
    $order = $service->markReady($order);
    $order = $service->complete($order);

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->completed_at)->not->toBeNull();
});

test('delivery order can go out for delivery before completion', function (): void {
    $service = app(OrderStatusService::class);
    $order = createStatusTestOrder(['fulfillment_type' => FulfillmentType::Delivery, 'address' => 'Bulevar 1']);

    $order = $service->accept($order, 30);
    $order = $service->markPreparing($order);
    $order = $service->markReady($order);
    $order = $service->markOutForDelivery($order);
    $order = $service->complete($order);

    expect($order->status)->toBe(OrderStatus::Completed);
});

test('invalid transition is rejected', function (): void {
    $order = createStatusTestOrder();

    app(OrderStatusService::class)->markPreparing($order);
})->throws(ValidationException::class);

test('admin can override workflow for edge cases', function (): void {
    $order = createStatusTestOrder();

    $updated = app(OrderStatusService::class)->setStatusForAdmin($order, OrderStatus::Preparing);

    expect($updated->status)->toBe(OrderStatus::Preparing);
});

test('completed orders cannot be changed', function (): void {
    $service = app(OrderStatusService::class);
    $order = createStatusTestOrder();

    $order = $service->accept($order, 30);
    $order = $service->markPreparing($order);
    $order = $service->markReady($order);
    $order = $service->complete($order);

    $service->cancel($order);
})->throws(ValidationException::class);
