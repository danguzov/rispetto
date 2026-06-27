<?php

namespace App\Services\Ordering;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Events\Ordering\OrderStatusUpdated;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    public function accept(Order $order, int $etaMinutes, ?User $user = null): Order
    {
        if ($etaMinutes < 5 || $etaMinutes > 180) {
            throw ValidationException::withMessages([
                'eta_minutes' => 'ETA mora biti između 5 i 180 minuta.',
            ]);
        }

        return $this->transition($order, OrderStatus::Accepted, $user, 'Porudžbina prihvaćena.', [
            'accepted_at' => now(),
            'estimated_ready_at' => now()->addMinutes($etaMinutes),
        ]);
    }

    public function markPreparing(Order $order, ?User $user = null): Order
    {
        return $this->transition($order, OrderStatus::Preparing, $user, 'Porudžbina u pripremi.');
    }

    public function markReady(Order $order, ?User $user = null): Order
    {
        return $this->transition($order, OrderStatus::Ready, $user, 'Porudžbina spremna.');
    }

    public function markOutForDelivery(Order $order, ?User $user = null): Order
    {
        if ($order->fulfillment_type !== FulfillmentType::Delivery) {
            throw ValidationException::withMessages([
                'status' => 'Status „Na putu“ je dostupan samo za dostavu.',
            ]);
        }

        return $this->transition($order, OrderStatus::OutForDelivery, $user, 'Porudžbina na putu.');
    }

    public function complete(Order $order, ?User $user = null): Order
    {
        return $this->transition($order, OrderStatus::Completed, $user, 'Porudžbina završena.', [
            'completed_at' => now(),
        ]);
    }

    public function cancel(Order $order, ?User $user = null, ?string $note = null): Order
    {
        return $this->transition($order, OrderStatus::Cancelled, $user, $note ?? 'Porudžbina otkazana.');
    }

    public function setStatusForAdmin(Order $order, OrderStatus $toStatus, ?User $user = null, ?string $note = null): Order
    {
        if (in_array($order->status, [OrderStatus::Completed, OrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'status' => 'Završene i otkazane porudžbine se ne mogu menjati.',
            ]);
        }

        $attributes = [];

        if ($toStatus === OrderStatus::Accepted && $order->accepted_at === null) {
            $attributes['accepted_at'] = now();

            if ($order->estimated_ready_at === null) {
                $attributes['estimated_ready_at'] = now()->addMinutes(
                    (int) config('ordering.default_eta_minutes', 45)
                );
            }
        }

        if ($toStatus === OrderStatus::Completed) {
            $attributes['completed_at'] = now();
        }

        return $this->transition(
            $order,
            $toStatus,
            $user,
            $note ?? 'Status ručno ažuriran u admin panelu.',
            $attributes,
            enforceWorkflow: false,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transition(
        Order $order,
        OrderStatus $toStatus,
        ?User $user = null,
        ?string $note = null,
        array $attributes = [],
        bool $enforceWorkflow = true,
    ): Order {
        $fromStatus = $order->status;

        if ($fromStatus === $toStatus) {
            return $order;
        }

        if ($enforceWorkflow && ! $this->canTransition($order, $fromStatus, $toStatus)) {
            throw ValidationException::withMessages([
                'status' => "Prelaz iz „{$fromStatus->label()}“ u „{$toStatus->label()}“ nije dozvoljen.",
            ]);
        }

        if (in_array($fromStatus, [OrderStatus::Completed, OrderStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'status' => 'Završene i otkazane porudžbine se ne mogu menjati.',
            ]);
        }

        return DB::transaction(function () use ($order, $fromStatus, $toStatus, $user, $note, $attributes): Order {
            $order->update(array_merge(['status' => $toStatus], $attributes));

            OrderStatusLog::query()->create([
                'order_id' => $order->id,
                'user_id' => $user?->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
                'created_at' => now(),
            ]);

            $order = $order->refresh()->load(['items', 'statusLogs']);

            OrderStatusUpdated::dispatch($order);

            return $order;
        });
    }

    private function canTransition(Order $order, OrderStatus $from, OrderStatus $to): bool
    {
        if ($to === OrderStatus::Cancelled) {
            return true;
        }

        return match ($from) {
            OrderStatus::Pending => $to === OrderStatus::Accepted,
            OrderStatus::Accepted => $to === OrderStatus::Preparing,
            OrderStatus::Preparing => $to === OrderStatus::Ready,
            OrderStatus::Ready => $order->fulfillment_type === FulfillmentType::Delivery
                ? in_array($to, [OrderStatus::OutForDelivery, OrderStatus::Completed], true)
                : $to === OrderStatus::Completed,
            OrderStatus::OutForDelivery => $to === OrderStatus::Completed,
            default => false,
        };
    }
}
