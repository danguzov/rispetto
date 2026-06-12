<?php

namespace App\Filament\Resources\Drinks;

use App\Filament\Resources\Drinks\Pages\CreateDrink;
use App\Filament\Resources\Drinks\Pages\EditDrink;
use App\Filament\Resources\Drinks\Pages\ListDrinks;
use App\Filament\Resources\Drinks\Schemas\DrinkForm;
use App\Filament\Resources\Drinks\Tables\DrinksTable;
use App\Models\Drink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DrinkResource extends Resource
{
    protected static ?string $model = Drink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Meni';

    protected static ?string $navigationLabel = 'Piće';

    protected static ?string $modelLabel = 'piće';

    protected static ?string $pluralModelLabel = 'piće';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DrinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DrinksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrinks::route('/'),
            'create' => CreateDrink::route('/create'),
            'edit' => EditDrink::route('/{record}/edit'),
        ];
    }
}
