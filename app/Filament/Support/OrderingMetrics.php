<?php

namespace App\Filament\Support;

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

    public static function completedTodayCount(): int
    {
        return Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereDate('completed_at', today())
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
