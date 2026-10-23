<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Enums\ProductType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuItemForm
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
                        fn ($query) => $query->where('type', ProductType::Food)->orderBy('sort_order'),
                    )
                    ->required()
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
                    ->label('Gramaža')
                    ->placeholder('npr. 350 g')
                    ->maxLength(50)
                    ->helperText('Opciono — prikazuje se ispod naziva.'),
                TextInput::make('price')
                    ->label('Cena')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->suffix('RSD')
                    ->helperText('Za obična jela. Za pice ostavite prazno i unesite veličine ispod.'),
                Repeater::make('price_variants')
                    ->label('Veličine pice (opciono)')
                    ->schema([
                        TextInput::make('label')
                            ->label('Veličina')
                            ->required()
                            ->placeholder('25 cm')
                            ->maxLength(50),
                        TextInput::make('price')
                            ->label('Cena')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('RSD'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->defaultItems(0)
                    ->addActionLabel('Dodaj veličinu')
                    ->reorderable()
                    ->collapsible()
                    ->collapsed()
                    ->helperText('Samo za pice: npr. 25 cm, 35 cm, 50 cm — jedna slika i naziv, više cena na sajtu.'),
                FileUpload::make('image')
                    ->label('Slika')
                    ->image()
                    ->disk('public')
                    ->directory('menu-items')
                    ->visibility('public')
                    ->columnSpanFull(),
                Toggle::make('featured')
                    ->label('Istaknuto')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Vidljivo na sajtu')
                    ->default(true)
                    ->helperText('Isključeno sakriva samo ovo jelo. Skrivena kategorija sakriva jelo i kada je ovde uključeno.'),
                Textarea::make('contents')
                    ->label('Šta sve ide')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('tags')
                    ->label('Tagovi')
                    ->relationship(
                        'tags',
                        'name',
                        fn ($query) => $query->where('type', ProductType::Food)->orderBy('name'),
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
