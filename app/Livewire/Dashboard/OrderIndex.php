<?php

namespace App\Livewire\Dashboard;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Livewire\Concerns\ListensForKitchenOrderBroadcasts;
use App\Livewire\Concerns\ManagesKitchenOrders;
use App\Models\Order;
use App\Support\OrderingMetrics;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Porudžbine')]
class OrderIndex extends Component
{
    use ListensForKitchenOrderBroadcasts;
    use ManagesKitchenOrders;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'filter')]
    public string $statusFilter = 'new';

    public bool $sidebarOpen = false;

    public function mount(): void
    {
        $this->syncKitchenState();
    }

    public function refreshOrders(): void
    {
        $this->syncKitchenState();
    }

    public function setStatusFilter(string $filter): void
    {
        $this->statusFilter = $filter;
        $this->sidebarOpen = false;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrdersProperty(): Collection
    {
        $query = $this->ordersQuery();

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('order_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('address', 'like', $term);
            });
        }

        return $query->get();
    }

    /**
     * @return Builder<Order>
     */
    protected function ordersQuery(): Builder
    {
        return match ($this->statusFilter) {
            'new' => OrderingMetrics::newOrdersQuery(),
            'pending' => OrderingMetrics::liveOrdersQuery()
                ->where('status', OrderStatus::Pending),
            'preparing' => OrderingMetrics::liveOrdersQuery()
                ->whereIn('status', [OrderStatus::Accepted->value, OrderStatus::Preparing->value]),
            'delivery' => OrderingMetrics::liveOrdersQuery()
                ->where('fulfillment_type', FulfillmentType::Delivery)
                ->whereIn('status', [OrderStatus::Ready->value, OrderStatus::OutForDelivery->value]),
            'pickup' => OrderingMetrics::liveOrdersQuery()
                ->where('fulfillment_type', FulfillmentType::Pickup),
            'completed' => Order::query()
                ->with('items')
                ->where('status', OrderStatus::Completed)
                ->whereDate('completed_at', today())
                ->orderByDesc('completed_at'),
            'cancelled' => Order::query()
                ->with('items')
                ->where('status', OrderStatus::Cancelled)
                ->whereDate('updated_at', today())
                ->orderByDesc('updated_at'),
            default => OrderingMetrics::todayOrdersQuery(),
        };
    }

    public function getSidebarCountsProperty(): array
    {
        return OrderingMetrics::sidebarCounts();
    }

    public function filterLabel(): string
    {
        return match ($this->statusFilter) {
            'new' => 'Nove porudžbine',
            'pending' => 'Čeka potvrdu',
            'preparing' => 'U pripremi',
            'delivery' => 'U dostavi',
            'pickup' => 'Lično preuzimanje',
            'completed' => 'Isporučeno',
            'cancelled' => 'Odbijeno',
            default => 'Sve narudžbine',
        };
    }

    public function render(): View
    {
        return view('livewire.dashboard.order-index', [
            'orders' => $this->orders,
            'sidebarCounts' => $this->sidebarCounts,
            'channelStatus' => OrderingMetrics::channelStatus(),
        ]);
    }
}
