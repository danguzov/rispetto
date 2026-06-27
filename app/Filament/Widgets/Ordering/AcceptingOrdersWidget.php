<?php

namespace App\Filament\Widgets\Ordering;

use App\Services\Ordering\RestaurantSettingsService;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;

class AcceptingOrdersWidget extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.ordering.accepting-orders-widget';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'accepting_orders' => app(RestaurantSettingsService::class)->acceptingOrders(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Primamo porudžbine')
                    ->description('Isključite kada je kuhinja zauzeta. Kupci će videti obaveštenje na sajtu i neće moći da poruče.')
                    ->schema([
                        Toggle::make('accepting_orders')
                            ->label('Online porudžbine su aktivne')
                            ->live()
                            ->afterStateUpdated(function (bool $state): void {
                                app(RestaurantSettingsService::class)->updateAcceptingOrders($state);

                                Notification::make()
                                    ->title($state ? 'Primamo porudžbine' : 'Ne primamo porudžbine')
                                    ->success()
                                    ->send();
                            }),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getSchema('form'),
            ]);
    }
}
