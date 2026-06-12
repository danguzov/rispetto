<?php

namespace App\Filament\Resources\Drinks\Pages;

use App\Filament\Resources\Drinks\DrinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDrinks extends ListRecords
{
    protected static string $resource = DrinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
