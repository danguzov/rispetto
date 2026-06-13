<?php

namespace App\Filament\Resources\Drinks\Schemas;

use App\Enums\ProductType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DrinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('category_id')
                    ->label('Kategorija')
                    ->relationship(
                        'category',
                        'name',
                        fn ($query) => $query->where('type', ProductType::Drink)->orderBy('sort_order'),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('name')
                    ->label('Naziv')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Opis')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Opciono.'),
                TextInput::make('weight')
                    ->label('Gramaža / zapremina')
                    ->placeholder('npr. 0,33 l')
                    ->maxLength(50)
                    ->helperText('Opciono — prikazuje se ispod naziva pića.'),
                TextInput::make('price')
                    ->label('Cena')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('RSD'),
                FileUpload::make('image')
                    ->label('Slika')
                    ->image()
                    ->disk('public')
                    ->directory('drinks')
                    ->visibility('public')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktivno')
                    ->default(true),
                Textarea::make('contents')
                    ->label('Šta sve ide')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('tags')
                    ->label('Tagovi')
                    ->relationship(
                        'tags',
                        'name',
                        fn ($query) => $query->where('type', ProductType::Drink)->orderBy('name'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Redosled')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }
}
