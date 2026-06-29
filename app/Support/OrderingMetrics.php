<?php

namespace App\Support;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Ordering\RestaurantSettingsService;
use App\Services\Ordering\WebPushService;
use Illuminate\Database\Eloquent\Builder;

final class OrderingMetrics
{
    public static function pendingCount(): int
    {
        return Order::query()
            ->where('status', OrderStatus::Pending)
            ->count();
    }

    public static function activeCount(): int
    {
        return Order::query()
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->count();
    }

    public static function ordersTodayCount(): int
    {
        return Order::query()
            ->whereDate('created_at', today())
            ->count();
    }

    public static function preparingCount(): int
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::Accepted->value, OrderStatus::Preparing->value])
            ->count();
    }

    public static function deliveryActiveCount(): int
    {
        return Order::query()
            ->where('fulfillment_type', FulfillmentType::Delivery)
            ->whereIn('status', [
                OrderStatus::Ready->value,
                OrderStatus::OutForDelivery->value,
            ])
            ->count();
    }

    public static function pickupActiveCount(): int
    {
        return Order::query()
            ->where('fulfillment_type', FulfillmentType::Pickup)
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->count();
    }

    public static function completedTodayCount(): int
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereDate('completed_at', today())
            ->count();
    }

    public static function cancelledTodayCount(): int
    {
        return Order::query()
            ->where('status', OrderStatus::Cancelled)
            ->whereDate('updated_at', today())
            ->count();
    }

    public static function revenueToday(): float
    {
        return (float) Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereDate('completed_at', today())
            ->sum('total');
    }

    /**
     * @return array{
     *     today: int,
     *     pending: int,
     *     preparing: int,
     *     delivery: int,
     *     pickup: int,
     *     completed: int,
     *     cancelled: int
     * }
     */
    public static function sidebarCounts(): array
    {
        return [
            'today' => self::ordersTodayCount(),
            'pending' => self::pendingCount(),
            'preparing' => self::preparingCount(),
            'delivery' => self::deliveryActiveCount(),
            'pickup' => self::pickupActiveCount(),
            'completed' => self::completedTodayCount(),
            'cancelled' => self::cancelledTodayCount(),
        ];
    }

    /**
     * @return Builder<Order>
     */
    public static function todayOrdersQuery(): Builder
    {
        return Order::query()
            ->with('items')
            ->whereDate('created_at', today())
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [OrderStatus::Pending->value])
            ->orderByDesc('created_at');
    }

    /**
     * @return Builder<Order>
     */
    public static function liveOrdersQuery(): Builder
    {
        return Order::query()
            ->with('items')
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->orderBy('created_at');
    }

    /**
     * @return Builder<Order>
     */
    public static function historyOrdersQuery(): Builder
    {
        return Order::query()
            ->with('items')
            ->orderByDesc('created_at');
    }

    /**
     * @return array{accepting_orders: bool, email_ready: bool, push_ready: bool, live_ready: bool}
     */
    public static function channelStatus(): array
    {
        $broadcastConnection = (string) config('broadcasting.default', 'null');

        return [
            'accepting_orders' => app(RestaurantSettingsService::class)->acceptingOrders(),
            'email_ready' => config('mail.default') !== 'log' && filled(config('mail.from.address')),
            'push_ready' => app(WebPushService::class)->isConfigured(),
            'live_ready' => $broadcastConnection !== '' && $broadcastConnection !== 'null',
        ];
    }
}
