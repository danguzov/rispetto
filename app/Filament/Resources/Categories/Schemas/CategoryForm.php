<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\ProductType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Naziv')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                        if ($operation !== 'create') {
                            return;
                        }

                        $set('slug', Str::slug($state ?? ''));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Tip')
                    ->options(ProductType::options())
                    ->required()
                    ->native(false),
                TextInput::make('sort_order')
                    ->label('Redosled')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Toggle::make('is_active')
                    ->label('Vidljivo na sajtu')
                    ->default(true)
                    ->columnSpanFull()
                    ->helperText('Isključeno sakriva celu kategoriju i sve stavke u njoj. Pojedinačna jela i dalje ostaju u adminu.'),
            ]);
    }
}
