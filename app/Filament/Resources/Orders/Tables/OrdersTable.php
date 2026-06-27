<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Broj')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->filamentColor())
                    ->sortable(),
                TextColumn::make('fulfillment_type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn (FulfillmentType $state): string => $state->label())
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Kupac')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Ukupno')
                    ->money('RSD')
                    ->sortable(),
                TextColumn::make('estimated_ready_at')
                    ->label('ETA')
                    ->dateTime('H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Primljena')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn (OrderStatus $status): array => [$status->value => $status->label()]
                    )->all()),
                SelectFilter::make('fulfillment_type')
                    ->label('Tip')
                    ->options(collect(FulfillmentType::cases())->mapWithKeys(
                        fn (FulfillmentType $type): array => [$type->value => $type->label()]
                    )->all()),
                Filter::make('today')
                    ->label('Danas')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
