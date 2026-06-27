<?php

namespace App\Filament\Pages;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Filament\Concerns\ListensForKitchenOrderBroadcasts;
use App\Filament\Support\OrderingMetrics;
use App\Filament\Widgets\Ordering\KitchenHeaderStatsWidget;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Ordering\OrderStatusService;
use App\Services\Ordering\RestaurantSettingsService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class KitchenBoard extends Page
{
    use ListensForKitchenOrderBroadcasts;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $navigationLabel = 'Kuhinja';

    protected static ?string $title = 'Kuhinja';

    protected static ?string $slug = 'kuhinja';

    protected static string|UnitEnum|null $navigationGroup = 'Porudžbine';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.kitchen-board';

    public int $lastPendingCount = 0;

    /**
     * @var array<int, int>
     */
    public array $customEtaByOrder = [];

    public function mount(): void
    {
        $this->lastPendingCount = $this->pendingCount;
    }

    /**
     * @return array<string, string>
     */
    protected function afterKitchenBroadcast(): void
    {
        $this->lastPendingCount = $this->pendingCount;
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            KitchenHeaderStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

    public function getPendingCountProperty(): int
    {
        return OrderingMetrics::pendingCount();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getActiveOrdersProperty(): Collection
    {
        return OrderingMetrics::liveOrdersQuery()->get();
    }

    public function getAcceptingOrdersProperty(): bool
    {
        return app(RestaurantSettingsService::class)->acceptingOrders();
    }

    /**
     * @return list<int>
     */
    public function getEtaPresetsProperty(): array
    {
        return array_map('intval', (array) config('ordering.eta_presets', [15, 30, 45, 60, 90]));
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()->where('status', OrderStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function refreshOrders(): void
    {
        $this->lastPendingCount = $this->pendingCount;
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

        Notification::make()
            ->title('Porudžbina prihvaćena')
            ->body('ETA: '.max(5, min(180, $etaMinutes)).' min')
            ->success()
            ->send();
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

    public function orderItemLineLabel(OrderItem $item): string
    {
        $label = "{$item->quantity}× {$item->name}";

        if ($item->variant_label) {
            $label .= " ({$item->variant_label})";
        }

        return $label.' — '.number_format((float) $item->line_total, 0, ',', '.').' RSD';
    }

    public function markPreparing(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markPreparing($order, auth()->user());

        Notification::make()->title('U pripremi')->success()->send();
    }

    public function markReady(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markReady($order, auth()->user());

        Notification::make()->title('Spremno')->success()->send();
    }

    public function markOutForDelivery(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->markOutForDelivery($order, auth()->user());

        Notification::make()->title('Na putu')->success()->send();
    }

    public function completeOrder(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->complete($order, auth()->user());

        Notification::make()->title('Završeno')->success()->send();
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);

        app(OrderStatusService::class)->cancel($order, auth()->user());

        Notification::make()->title('Otkazano')->warning()->send();
    }

    public function etaLabelFor(Order $order): ?string
    {
        if ($order->estimated_ready_at === null) {
            return null;
        }

        $minutes = max(0, (int) now()->diffInMinutes($order->estimated_ready_at, false));

        return $minutes > 0
            ? "za ~{$minutes} min ({$order->estimated_ready_at->format('H:i')})"
            : $order->estimated_ready_at->format('H:i');
    }

    public function statusColumnLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Pending => 'Nova',
            OrderStatus::Accepted => 'Prihvaćena',
            OrderStatus::Preparing => 'U pripremi',
            OrderStatus::Ready => 'Spremno',
            OrderStatus::OutForDelivery => 'Na putu',
            default => $status->label(),
        };
    }

    public function isDelivery(Order $order): bool
    {
        return $order->fulfillment_type === FulfillmentType::Delivery;
    }
}
