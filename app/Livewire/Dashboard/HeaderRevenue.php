<?php

namespace App\Livewire\Dashboard;

use App\Support\OrderingMetrics;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HeaderRevenue extends Component
{
    public float $revenueToday = 0;

    public function mount(): void
    {
        $this->refreshRevenue();
    }

    public function refreshRevenue(): void
    {
        $this->revenueToday = OrderingMetrics::revenueToday();
    }

    public function render(): View
    {
        return view('livewire.dashboard.header-revenue');
    }
}
