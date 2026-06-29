<?php

namespace App\Livewire\Dashboard;

use App\Enums\OrderStatus;
use App\Livewire\Concerns\ListensForKitchenOrderBroadcasts;
use App\Livewire\Concerns\ManagesKitchenOrders;
use App\Models\Order;
use App\Services\Ordering\OrderStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderShow extends Component
{
    use ListensForKitchenOrderBroadcasts;
    use ManagesKitchenOrders;

    public Order $order;

    public string $adminStatus = '';

    public string $adminNote = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items', 'statusLogs']);
        $this->adminStatus = $order->status->value;
        $this->syncKitchenState();
    }

    public function refreshOrder(): void
    {
        $this->syncKitchenState();
        $this->order->refresh()->load(['items', 'statusLogs']);
        $this->adminStatus = $this->order->status->value;
    }

    protected function afterKitchenBroadcast(): void
    {
        $this->refreshOrder();
        $this->dispatch('kitchen-new-order');
    }

    public function updateStatusManually(): void
    {
        $this->validate([
            'adminStatus' => ['required', 'string'],
            'adminNote' => ['nullable', 'string', 'max:500'],
        ]);

        $toStatus = OrderStatus::from($this->adminStatus);

        try {
            app(OrderStatusService::class)->setStatusForAdmin(
                $this->order,
                $toStatus,
                auth()->user(),
                filled($this->adminNote) ? $this->adminNote : null,
            );
        } catch (ValidationException $exception) {
            $this->addError('adminStatus', collect($exception->errors())->flatten()->first() ?? 'Status nije validan.');

            return;
        }

        session()->flash('dashboard_message', 'Status porudžbine je ažuriran.');
        $this->refreshOrder();
    }

    #[Title('Porudžbina')]
    public function render(): View
    {
        return view('livewire.dashboard.order-show', [
            'order' => $this->order,
        ]);
    }
}
