<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ManagesKitchenOrders;
use App\Support\OrderHistory as OrderHistoryQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Istorija porudžbina')]
class OrderHistoryIndex extends Component
{
    use ManagesKitchenOrders;

    #[Url(as: 'mesec')]
    public string $selectedMonth = '';

    #[Url(as: 'dan')]
    public string $selectedDay = '';

    public function mount(): void
    {
        if ($this->selectedMonth === '' || ! OrderHistoryQuery::isValidMonthKey($this->selectedMonth)) {
            $this->selectedMonth = OrderHistoryQuery::defaultMonthKey();
        }

        $this->ensureValidDay();
    }

    public function selectMonth(string $monthKey): void
    {
        if (! OrderHistoryQuery::isValidMonthKey($monthKey)) {
            return;
        }

        $this->selectedMonth = $monthKey;
        $this->selectedDay = OrderHistoryQuery::defaultDayKey($monthKey) ?? '';
    }

    public function selectDay(string $dayKey): void
    {
        if (! OrderHistoryQuery::isValidDayKey($dayKey)) {
            return;
        }

        $this->selectedDay = $dayKey;
    }

    protected function ensureValidDay(): void
    {
        $days = $this->days;

        if ($days === []) {
            $this->selectedDay = '';

            return;
        }

        $validKeys = array_column($days, 'key');

        if ($this->selectedDay === '' || ! in_array($this->selectedDay, $validKeys, true)) {
            $this->selectedDay = OrderHistoryQuery::defaultDayKey($this->selectedMonth) ?? $validKeys[0];
        }
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    public function getMonthsProperty(): array
    {
        return OrderHistoryQuery::availableMonths();
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    public function getDaysProperty(): array
    {
        return OrderHistoryQuery::daysInMonth($this->selectedMonth);
    }

    /**
     * @return Collection<int, \App\Models\Order>
     */
    public function getOrdersProperty(): Collection
    {
        if ($this->selectedDay === '') {
            return new Collection;
        }

        return OrderHistoryQuery::ordersForDay($this->selectedDay);
    }

    public function getMonthSummaryProperty(): array
    {
        $days = $this->days;

        return [
            'orders' => array_sum(array_column($days, 'count')),
            'days' => count($days),
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.order-history', [
            'months' => $this->months,
            'days' => $this->days,
            'orders' => $this->orders,
            'monthSummary' => $this->monthSummary,
        ]);
    }
}
