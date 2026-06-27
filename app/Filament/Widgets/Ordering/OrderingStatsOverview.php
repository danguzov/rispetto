<?php

namespace App\Filament\Widgets\Ordering;

use App\Filament\Concerns\ListensForKitchenOrderBroadcasts;
use App\Filament\Pages\KitchenBoard;
use App\Filament\Support\OrderingMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderingStatsOverview extends StatsOverviewWidget
{
    use ListensForKitchenOrderBroadcasts;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Pregled porudžbina';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $revenue = OrderingMetrics::revenueToday();

        return [
            Stat::make('Na čekanju', (string) OrderingMetrics::pendingCount())
                ->description('Potrebna akcija u kuhinji')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning')
                ->url(KitchenBoard::getUrl()),
            Stat::make('Aktivne', (string) OrderingMetrics::activeCount())
                ->description('U toku obrade')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url(KitchenBoard::getUrl()),
            Stat::make('Završene danas', (string) OrderingMetrics::completedTodayCount())
                ->description('Uspešno isporučene')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Promet danas', number_format($revenue, 0, ',', '.').' RSD')
                ->description('Završene porudžbine')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),
        ];
    }
}
