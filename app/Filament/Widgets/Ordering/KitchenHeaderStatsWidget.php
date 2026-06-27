<?php

namespace App\Filament\Widgets\Ordering;

use App\Filament\Concerns\ListensForKitchenOrderBroadcasts;
use App\Filament\Support\OrderingMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenHeaderStatsWidget extends StatsOverviewWidget
{
    use ListensForKitchenOrderBroadcasts;

    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = '15s';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $channels = OrderingMetrics::channelStatus();

        return [
            Stat::make('Na čekanju', (string) OrderingMetrics::pendingCount())
                ->color('warning'),
            Stat::make('Aktivne', (string) OrderingMetrics::activeCount())
                ->color('primary'),
            Stat::make('Prijem', $channels['accepting_orders'] ? 'Uključen' : 'Isključen')
                ->color($channels['accepting_orders'] ? 'success' : 'danger'),
        ];
    }
}
