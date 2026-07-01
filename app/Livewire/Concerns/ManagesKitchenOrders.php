<?php

namespace App\Livewire\Concerns;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Ordering\OrderAutoStatusService;
use App\Services\Ordering\OrderStatusService;
use App\Services\Ordering\RestaurantSettingsService;
use App\Support\OrderingMetrics;

trait ManagesKitchenOrders
{
    public int $pendingCount = 0;

    /**
     * @var array<int, int>
     */
    public array $customEtaByOrder = [];

    public function syncKitchenState(): void
    {
        $previous = $this->pendingCount;

        app(OrderAutoStatusService::class)->processDueTransitions();

        $this->pendingCount = OrderingMetrics::pendingCount();

        if ($this->pendingCount !== $previous) {
            $this->dispatch('kitchen-pending-updated', pendingCount: $this->pendingCount);
        }
    }

    protected function afterKitchenBroadcast(): void
    {
        $this->syncKitchenState();
        $this->dispatch('kitchen-new-order', pendingCount: $this->pendingCount);
    }

    public function acceptOrder(int $orderId, int $etaMinutes): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->accept(
            $order,
            max(5, min(180, $etaMinutes)),
            auth()->user(),
        );

        unset($this->customEtaByOrder[$orderId]);

        session()->flash('dashboard_message', 'Porudžbina prihvaćena. ETA: '.max(5, min(180, $etaMinutes)).' min.');
        $this->syncKitchenState();
    }

    public function acceptOrderWithCustomEta(int $orderId): void
    {
        $defaultEta = (int) config('ordering.default_eta_minutes', 45);
        $etaMinutes = (int) ($this->customEtaByOrder[$orderId] ?? $defaultEta);

        $this->acceptOrder($orderId, $etaMinutes);
    }

    public function customEtaFor(Order $order): int
    {
        return (int) ($this->customEtaByOrder[$order->id] ?? config('ordering.default_eta_minutes', 45));
    }

    public function markPreparing(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markPreparing($order, auth()->user());

        session()->flash('dashboard_message', 'Porudžbina je u pripremi.');
        $this->syncKitchenState();
    }

    public function markReady(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markReady($order, auth()->user());

        session()->flash('dashboard_message', 'Porudžbina je spremna.');
        $this->syncKitchenState();
    }

    public function markOutForDelivery(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markOutForDelivery($order, auth()->user());

        session()->flash('dashboard_message', 'Porudžbina je na putu.');
        $this->syncKitchenState();
    }

    public function completeOrder(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->complete($order, auth()->user());

        session()->flash('dashboard_message', 'Porudžbina je završena.');
        $this->syncKitchenState();
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->cancel($order, auth()->user());

        session()->flash('dashboard_message', 'Porudžbina je otkazana.');
        $this->syncKitchenState();
    }

    public function orderItemLineLabel(OrderItem $item): string
    {
        $label = "{$item->quantity}× {$item->name}";

        if ($item->variant_label) {
            $label .= " ({$item->variant_label})";
        }

        return $label.' — '.number_format((float) $item->line_total, 0, ',', '.').' RSD';
    }

    public function orderItemShortLabel(OrderItem $item): string
    {
        $label = "{$item->quantity}× {$item->name}";

        if ($item->variant_label) {
            $label .= " ({$item->variant_label})";
        }

        return $label;
    }

    /**
     * @return list<string>
     */
    public function orderItemLabels(Order $order): array
    {
        return $order->items
            ->map(fn (OrderItem $item): string => $this->orderItemShortLabel($item))
            ->values()
            ->all();
    }

    public function orderItemsSummary(Order $order): string
    {
        return $order->items
            ->map(fn (OrderItem $item): string => $this->orderItemLineLabel($item))
            ->implode("\n");
    }

    public function isDelivery(Order $order): bool
    {
        return $order->fulfillment_type === FulfillmentType::Delivery;
    }

    public function isAutoStatusEnabled(): bool
    {
        return (bool) config('ordering.auto_status.enabled', true);
    }

    public function isPreparingDue(Order $order): bool
    {
        if (! $this->isAutoStatusEnabled()) {
            return false;
        }

        return app(OrderAutoStatusService::class)->shouldMarkPreparing($order);
    }

    public function isReadyDue(Order $order): bool
    {
        if (! $this->isAutoStatusEnabled()) {
            return false;
        }

        return app(OrderAutoStatusService::class)->shouldMarkReady($order);
    }

    public function preparingDueLabel(Order $order): ?string
    {
        $dueAt = app(OrderAutoStatusService::class)->preparingDueAt($order);

        if ($dueAt === null) {
            return null;
        }

        if (now()->greaterThanOrEqualTo($dueAt)) {
            return 'Automatski: U pripremi';
        }

        $minutes = max(0, (int) now()->diffInMinutes($dueAt, false));

        return $minutes > 0
            ? "Auto u pripremi za ~{$minutes} min"
            : 'Auto u pripremi uskoro';
    }

    public function readyDueLabel(Order $order): ?string
    {
        if ($order->estimated_ready_at === null) {
            return null;
        }

        if (now()->greaterThanOrEqualTo($order->estimated_ready_at)) {
            return 'Automatski: Spremno';
        }

        $minutes = max(0, (int) now()->diffInMinutes($order->estimated_ready_at, false));

        return $minutes > 0
            ? "Auto spremno za ~{$minutes} min"
            : 'Auto spremno uskoro';
    }

    public function completeDueLabel(Order $order): ?string
    {
        if (! $this->isAutoStatusEnabled()) {
            return null;
        }

        if (! in_array($order->status, [OrderStatus::Ready, OrderStatus::Preparing, OrderStatus::Accepted, OrderStatus::OutForDelivery], true)) {
            return null;
        }

        $dueAt = app(OrderAutoStatusService::class)->completeDueAt($order);

        if ($dueAt === null) {
            return null;
        }

        if (now()->greaterThanOrEqualTo($dueAt)) {
            return 'Automatski: Završeno';
        }

        $minutes = max(0, (int) now()->diffInMinutes($dueAt, false));

        return $minutes > 0
            ? "Auto završeno za ~{$minutes} min"
            : 'Auto završeno uskoro';
    }

    public function readyCountdownLabel(Order $order): string
    {
        if ($order->status === OrderStatus::Pending) {
            $waiting = max(0, (int) $order->created_at?->diffInMinutes(now()));

            return $waiting < 1 ? 'Upravo stigla' : "Čeka {$waiting} min";
        }

        if ($order->estimated_ready_at === null) {
            return '—';
        }

        $minutes = max(0, (int) now()->diffInMinutes($order->estimated_ready_at, false));

        return $minutes > 0
            ? "~{$minutes} min ({$order->estimated_ready_at->format('H:i')})"
            : 'Spremno uskoro';
    }

    public function orderDisplayId(Order $order): string
    {
        $token = strtoupper(\Illuminate\Support\Str::substr(str_replace('-', '', $order->tracking_token), 0, 5));

        return '#'.$order->created_at?->format('md').'-'.$token;
    }

    public function acceptingOrders(): bool
    {
        return app(RestaurantSettingsService::class)->acceptingOrders();
    }

    public function toggleAcceptingOrders(): void
    {
        $settings = app(RestaurantSettingsService::class);
        $next = ! $settings->acceptingOrders();
        $settings->updateAcceptingOrders($next);

        session()->flash(
            'dashboard_message',
            $next
                ? 'Prijem porudžbina je uključen.'
                : 'Prijem porudžbina je isključen.',
        );
    }

    /**
     * @return list<int>
     */
    public function etaPresets(): array
    {
        return array_map('intval', (array) config('ordering.eta_presets', [15, 30, 45, 60, 90]));
    }
}
