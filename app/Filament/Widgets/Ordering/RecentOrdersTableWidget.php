<?php

namespace App\Filament\Widgets\Ordering;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Filament\Concerns\ListensForKitchenOrderBroadcasts;
use App\Filament\Pages\KitchenBoard;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\OrderingMetrics;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentOrdersTableWidget extends TableWidget
{
    use ListensForKitchenOrderBroadcasts;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Aktivne porudžbine')
            ->description('Brz pregled porudžbina koje zahtevaju pažnju.')
            ->query(fn () => OrderingMetrics::liveOrdersQuery()->limit(10))
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Broj')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('customer_name')
                    ->label('Kupac')
                    ->description(fn (Order $record): string => $record->phone),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->filamentColor()),
                TextColumn::make('fulfillment_type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (FulfillmentType $state): string => $state->label())
                    ->color('gray'),
                TextColumn::make('total')
                    ->label('Iznos')
                    ->money('RSD'),
                TextColumn::make('created_at')
                    ->label('Primljena')
                    ->since(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detalji')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Nema aktivnih porudžbina')
            ->emptyStateDescription('Kada stigne nova porudžbina, pojaviće se ovde i u kuhinji.')
            ->emptyStateActions([
                Action::make('openKitchen')
                    ->label('Otvori kuhinju')
                    ->url(KitchenBoard::getUrl()),
            ]);
    }
}
