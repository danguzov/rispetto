<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Events\Ordering\OrderingAvailabilityChanged;
use App\Jobs\SendOrderAcceptedNotification;
use App\Jobs\SendOrderPushNotification;
use App\Mail\OrderAcceptedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPushSubscription;
use App\Models\OrderStatusLog;
use App\Models\RestaurantSettings;
use App\Services\Ordering\OrderStatusService;
use App\Services\Ordering\RestaurantSettingsService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
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

function createNotificationTestOrder(array $attributes = []): Order
{
    $order = Order::query()->create(array_merge([
        'order_number' => 'R-'.now()->format('Ymd').'-0300',
        'tracking_token' => (string) Str::uuid(),
        'status' => OrderStatus::Pending,
        'fulfillment_type' => FulfillmentType::Pickup,
        'customer_name' => 'Jelena Jovanović',
        'phone' => '0641234567',
        'email' => 'jelena@example.com',
        'payment_method' => 'cash',
        'subtotal' => 1500,
        'total' => 1500,
    ], $attributes));

    OrderItem::query()->create([
        'order_id' => $order->id,
        'name' => 'Tiramisu',
        'quantity' => 1,
        'unit_price' => 1500,
        'line_total' => 1500,
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

test('accepting orders toggle dispatches availability changed event', function (): void {
    Event::fake([OrderingAvailabilityChanged::class]);

    app(RestaurantSettingsService::class)->updateAcceptingOrders(false);

    Event::assertDispatched(OrderingAvailabilityChanged::class, function (OrderingAvailabilityChanged $event): bool {
        return $event->acceptingOrders === false
            && $event->message !== '';
    });
});

test('ordering availability changed broadcasts on public site channel', function (): void {
    $event = new OrderingAvailabilityChanged(false, 'Trenutno ne primamo porudžbine.');

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(Channel::class)
        ->and($event->broadcastOn()[0]->name)->toBe('site.ordering')
        ->and($event->broadcastAs())->toBe('OrderingAvailabilityChanged');
});

test('accepting order queues email and push notifications', function (): void {
    Queue::fake();
    $order = createNotificationTestOrder();

    app(OrderStatusService::class)->accept($order, 45);

    Queue::assertPushed(SendOrderAcceptedNotification::class);
    Queue::assertPushed(SendOrderPushNotification::class);
});

test('status update without email only queues push notification job', function (): void {
    Queue::fake();
    $order = createNotificationTestOrder(['email' => null]);
    $order = app(OrderStatusService::class)->accept($order, 45);

    Queue::assertNotPushed(SendOrderAcceptedNotification::class);
    Queue::assertPushed(SendOrderPushNotification::class);

    Queue::fake();
    app(OrderStatusService::class)->markPreparing($order);

    Queue::assertNotPushed(SendOrderAcceptedNotification::class);
    Queue::assertPushed(SendOrderPushNotification::class);
});

test('order accepted email is sent to customer', function (): void {
    Mail::fake();
    $order = createNotificationTestOrder([
        'status' => OrderStatus::Accepted,
        'accepted_at' => now(),
        'estimated_ready_at' => now()->addMinutes(45),
    ]);

    (new SendOrderAcceptedNotification($order))->handle();

    Mail::assertSent(OrderAcceptedMail::class, function (OrderAcceptedMail $mail) use ($order): bool {
        return $mail->hasTo($order->email)
            && $mail->order->is($order);
    });
});

test('guest can subscribe to order push notifications', function (): void {
    config([
        'ordering.webpush.vapid_public_key' => 'test-public-key',
        'ordering.webpush.vapid_private_key' => 'test-private-key',
    ]);

    $order = createNotificationTestOrder();

    $this->postJson(route('order.push.subscribe', $order->tracking_token), [
        'endpoint' => 'https://push.example.test/subscription/1',
        'keys' => [
            'auth' => 'auth-token',
            'p256dh' => 'public-key',
        ],
    ])->assertOk()
        ->assertJsonPath('subscribed', true);

    expect(OrderPushSubscription::query()->count())->toBe(1);
});

test('ordering banner is shown when restaurant is closed', function (): void {
    RestaurantSettings::query()->first()?->update(['accepting_orders' => false]);
    app(RestaurantSettingsService::class)->clearCache();

    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-ordering-banner', false)
        ->assertSee(config('ordering.closed_message'));
});

test('ordering banner is hidden when restaurant accepts orders', function (): void {
    $this->get(route('menu'))
        ->assertOk()
        ->assertSee('data-ordering-banner', false)
        ->assertSee('data-visible="false"', false);
});

test('tracking page shows notify me button when web push is configured', function (): void {
    config(['ordering.webpush.vapid_public_key' => 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrAqBZqP8Z6F3g8Yh2lD9oV0w']);

    $order = createNotificationTestOrder();

    $this->get(route('order.tracking', $order->tracking_token))
        ->assertOk()
        ->assertSee('Obavesti me')
        ->assertSee('data-order-push-subscribe', false);
});
