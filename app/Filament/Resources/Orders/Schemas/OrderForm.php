<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status')
                    ->description('Ručna promena statusa za izuzetne slučajeve.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(collect(OrderStatus::cases())->mapWithKeys(
                                fn (OrderStatus $status): array => [$status->value => $status->label()]
                            )->all())
                            ->required()
                            ->native(false),
                        Textarea::make('admin_note')
                            ->label('Napomena u istoriji')
                            ->rows(3)
                            ->maxLength(500),
                    ]),
            ]);
    }
}
