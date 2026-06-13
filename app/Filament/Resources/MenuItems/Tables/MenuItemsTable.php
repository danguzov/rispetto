<?php

namespace App\Filament\Resources\MenuItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('Slika')
                    ->disk('public'),
                TextColumn::make('category.name')
                    ->label('Kategorija')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Cena')
                    ->money('RSD')
                    ->sortable()
                    ->placeholder('Varijante')
                    ->description(fn ($record) => $record->hasPriceVariants()
                        ? collect($record->normalizedPriceVariants())->count().' var.'
                        : null),
                IconColumn::make('featured')
                    ->label('Istaknuto')
                    ->boolean(),
                TextColumn::make('tags.name')
                    ->label('Tagovi')
                    ->badge()
                    ->limitList(3),
                TextColumn::make('sort_order')
                    ->label('Redosled')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktivno')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Ažurirano')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategorija')
                    ->relationship('category', 'name'),
                TernaryFilter::make('featured')
                    ->label('Istaknuto'),
                TernaryFilter::make('is_active')
                    ->label('Aktivno'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
