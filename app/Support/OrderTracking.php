<?php

namespace App\Support;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;

final class OrderTracking
{
    /**
     * @return array{
     *     order_number: string,
     *     status: string,
     *     status_label: string,
     *     cancelled: bool,
     *     estimated_ready_at: string|null,
     *     estimated_ready_at_time: string|null,
     *     timeline: list<array{status: string, label: string, customer: string}>,
     *     current_index: int,
     * }
     */
    public static function payload(Order $order): array
    {
        $timeline = self::timelineFor($order);
        $currentIndex = collect($timeline)->search(
            fn (array $step): bool => $step['status'] === $order->status->value,
        );

        if ($currentIndex === false && $order->status === OrderStatus::Cancelled) {
            $currentIndex = -1;
        }

        return [
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'cancelled' => $order->status === OrderStatus::Cancelled,
            'estimated_ready_at' => $order->estimated_ready_at?->toIso8601String(),
            'estimated_ready_at_time' => $order->estimated_ready_at?->format('H:i'),
            'timeline' => $timeline,
            'current_index' => is_int($currentIndex) ? $currentIndex : -1,
        ];
    }

    /**
     * @return list<array{status: string, label: string, customer: string}>
     */
    public static function timelineFor(Order $order): array
    {
        $steps = $order->fulfillment_type === FulfillmentType::Delivery
            ? [
                [OrderStatus::Pending, 'Porudžbina primljena', 'Čekamo potvrdu restorana'],
                [OrderStatus::Accepted, 'Porudžbina prihvaćena', 'Restoran je prihvatio vašu porudžbinu'],
                [OrderStatus::Preparing, 'U pripremi', 'Vaše jelo se priprema'],
                [OrderStatus::Ready, 'Spremno', 'Jelo je spremno'],
                [OrderStatus::OutForDelivery, 'Na putu', 'Porudžbina je na putu ka vama'],
                [OrderStatus::Completed, 'Završeno', 'Prijatno!'],
            ]
            : [
                [OrderStatus::Pending, 'Porudžbina primljena', 'Čekamo potvrdu restorana'],
                [OrderStatus::Accepted, 'Porudžbina prihvaćena', 'Restoran je prihvatio vašu porudžbinu'],
                [OrderStatus::Preparing, 'U pripremi', 'Vaše jelo se priprema'],
                [OrderStatus::Ready, 'Spremno za preuzimanje', 'Možete doći po porudžbinu'],
                [OrderStatus::Completed, 'Završeno', 'Prijatno!'],
            ];

        return array_map(fn (array $step): array => [
            'status' => $step[0]->value,
            'label' => $step[1],
            'customer' => $step[2],
        ], $steps);
    }
}
