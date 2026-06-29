<?php

namespace App\Services\Ordering;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class OrderAutoStatusService
{
    public function processDueTransitions(): int
    {
        if (! config('ordering.auto_status.enabled', true)) {
            return 0;
        }

        $processed = 0;

        /** @var Collection<int, Order> $orders */
        $orders = Order::query()
            ->whereIn('status', [
                OrderStatus::Accepted,
                OrderStatus::Preparing,
                OrderStatus::Ready,
                OrderStatus::OutForDelivery,
            ])
            ->whereNotNull('estimated_ready_at')
            ->get();

        foreach ($orders as $order) {
            if (in_array($order->status, [OrderStatus::Accepted, OrderStatus::Preparing], true)
                && $order->accepted_at === null) {
                continue;
            }

            $processed += $this->processOrder($order);
        }

        return $processed;
    }

    private function processOrder(Order $order): int
    {
        $processed = 0;
        $statusService = app(OrderStatusService::class);

        if ($this->shouldMarkPreparing($order)) {
            $statusService->markPreparing($order);
            $order->refresh();
            $processed++;
        }

        if ($this->shouldMarkReady($order)) {
            $statusService->markReady($order);
            $order->refresh();
            $processed++;
        }

        if ($this->shouldMarkOutForDelivery($order)) {
            $statusService->markOutForDelivery($order);
            $order->refresh();
            $processed++;
        }

        if ($this->shouldComplete($order)) {
            $statusService->complete($order);
            $processed++;
        }

        return $processed;
    }

    public function shouldMarkPreparing(Order $order): bool
    {
        if ($order->status !== OrderStatus::Accepted) {
            return false;
        }

        if ($order->accepted_at === null || $order->estimated_ready_at === null) {
            return false;
        }

        $totalSeconds = max(1, $order->accepted_at->diffInSeconds($order->estimated_ready_at));
        $elapsedSeconds = $order->accepted_at->diffInSeconds(now());
        $percent = (int) config('ordering.auto_status.preparing_after_percent', 15);
        $minSeconds = (int) config('ordering.auto_status.preparing_min_seconds', 60);
        $thresholdSeconds = max($minSeconds, (int) floor($totalSeconds * ($percent / 100)));

        return $elapsedSeconds >= $thresholdSeconds;
    }

    public function shouldMarkReady(Order $order): bool
    {
        if (! in_array($order->status, [OrderStatus::Accepted, OrderStatus::Preparing], true)) {
            return false;
        }

        if ($order->estimated_ready_at === null) {
            return false;
        }

        return now()->greaterThanOrEqualTo($order->estimated_ready_at);
    }

    public function shouldMarkOutForDelivery(Order $order): bool
    {
        if ($order->fulfillment_type !== FulfillmentType::Delivery) {
            return false;
        }

        if ($order->status !== OrderStatus::Ready) {
            return false;
        }

        if ($order->estimated_ready_at === null) {
            return false;
        }

        return now()->greaterThanOrEqualTo($order->estimated_ready_at);
    }

    public function shouldComplete(Order $order): bool
    {
        if (! in_array($order->status, [OrderStatus::Ready, OrderStatus::OutForDelivery], true)) {
            return false;
        }

        if ($order->estimated_ready_at === null) {
            return false;
        }

        if ($order->fulfillment_type === FulfillmentType::Delivery
            && $order->status === OrderStatus::Ready) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->completeDueAt($order));
    }

    public function preparingDueAt(Order $order): ?CarbonInterface
    {
        if ($order->accepted_at === null || $order->estimated_ready_at === null) {
            return null;
        }

        $totalSeconds = max(1, $order->accepted_at->diffInSeconds($order->estimated_ready_at));
        $percent = (int) config('ordering.auto_status.preparing_after_percent', 15);
        $minSeconds = (int) config('ordering.auto_status.preparing_min_seconds', 60);
        $thresholdSeconds = max($minSeconds, (int) floor($totalSeconds * ($percent / 100)));

        return $order->accepted_at->copy()->addSeconds($thresholdSeconds);
    }

    public function completeDueAt(Order $order): ?CarbonInterface
    {
        if ($order->estimated_ready_at === null) {
            return null;
        }

        $delayMinutes = (int) config('ordering.auto_status.complete_after_ready_minutes', 2);

        return $order->estimated_ready_at->copy()->addMinutes(max(0, $delayMinutes));
    }
}
