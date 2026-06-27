<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pregled')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Broj porudžbine'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                            ->color(fn (OrderStatus $state): string => $state->filamentColor()),
                        TextEntry::make('fulfillment_type')
                            ->label('Tip')
                            ->formatStateUsing(fn (FulfillmentType $state): string => $state->label()),
                        TextEntry::make('payment_method')
                            ->label('Plaćanje')
                            ->formatStateUsing(fn (PaymentMethod $state): string => $state->label()),
                        TextEntry::make('total')
                            ->label('Ukupno')
                            ->money('RSD'),
                        TextEntry::make('created_at')
                            ->label('Primljena')
                            ->dateTime('d.m.Y H:i'),
                        TextEntry::make('accepted_at')
                            ->label('Prihvaćena')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('estimated_ready_at')
                            ->label('Spremno za')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('completed_at')
                            ->label('Završena')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make('Kupac')
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Ime'),
                        TextEntry::make('phone')
                            ->label('Telefon'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('—'),
                        TextEntry::make('address')
                            ->label('Adresa')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Napomena')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Section::make('Stavke')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Jelo'),
                                TextEntry::make('variant_label')
                                    ->label('Varijanta')
                                    ->placeholder('—'),
                                TextEntry::make('quantity')
                                    ->label('Kol.'),
                                TextEntry::make('line_total')
                                    ->label('Iznos')
                                    ->money('RSD'),
                            ])
                            ->columns(4),
                    ]),
                Section::make('Istorija statusa')
                    ->schema([
                        RepeatableEntry::make('statusLogs')
                            ->label('')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Vreme')
                                    ->dateTime('d.m.Y H:i'),
                                TextEntry::make('from_status')
                                    ->label('Od')
                                    ->formatStateUsing(fn (?OrderStatus $state): string => $state?->label() ?? '—'),
                                TextEntry::make('to_status')
                                    ->label('U')
                                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),
                                TextEntry::make('note')
                                    ->label('Napomena')
                                    ->placeholder('—'),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
