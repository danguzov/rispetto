<?php

namespace App\Filament\Widgets\Ordering;

use App\Filament\Support\OrderingMetrics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NotificationsStatusWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Obaveštenja i live';

    protected ?string $description = 'Status kanala koji obaveštavaju kupce i kuhinju.';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $channels = OrderingMetrics::channelStatus();

        return [
            Stat::make('Email', $channels['email_ready'] ? 'Spreman' : 'Nije podešen')
                ->description('Obaveštenje pri prihvatanju')
                ->descriptionIcon($channels['email_ready'] ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($channels['email_ready'] ? 'success' : 'gray'),
            Stat::make('Web Push', $channels['push_ready'] ? 'Spreman' : 'Nije podešen')
                ->description('Opt-in na tracking stranici')
                ->descriptionIcon($channels['push_ready'] ? 'heroicon-m-bell-alert' : 'heroicon-m-bell-slash')
                ->color($channels['push_ready'] ? 'success' : 'gray'),
            Stat::make('Live update', $channels['live_ready'] ? 'Aktivan' : 'Isključen')
                ->description('Reverb + Echo')
                ->descriptionIcon($channels['live_ready'] ? 'heroicon-m-signal' : 'heroicon-m-signal-slash')
                ->color($channels['live_ready'] ? 'success' : 'warning'),
        ];
    }
}
