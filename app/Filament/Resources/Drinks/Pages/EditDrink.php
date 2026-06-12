<?php

namespace App\Filament\Resources\Drinks\Pages;

use App\Filament\Resources\Drinks\DrinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDrink extends EditRecord
{
    protected static string $resource = DrinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
