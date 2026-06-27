<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Ordering\AcceptingOrdersWidget;
use App\Filament\Widgets\Ordering\NotificationsStatusWidget;
use App\Filament\Widgets\Ordering\OrderingStatsOverview;
use App\Filament\Widgets\Ordering\RecentOrdersTableWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Pregled';

    protected static ?string $title = 'Operacije';

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            AcceptingOrdersWidget::class,
            OrderingStatsOverview::class,
            RecentOrdersTableWidget::class,
            NotificationsStatusWidget::class,
        ];
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }
}
