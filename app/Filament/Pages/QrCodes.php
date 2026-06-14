<?php

namespace App\Filament\Pages;

use App\Support\RispettoQrCode;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class QrCodes extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'QR kodovi';

    protected static ?string $title = 'QR kodovi';

    protected static string|UnitEnum|null $navigationGroup = 'Alati';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.qr-codes';

    /**
     * @return list<array{type: string, label: string, description: string, filename: string, url: string, configured: bool, download_url: string}>
     */
    public function getQrCodesProperty(): array
    {
        return collect(RispettoQrCode::definitions())
            ->map(function (array $definition, string $type): array {
                return [
                    'type' => $type,
                    'label' => $definition['label'],
                    'description' => $definition['description'],
                    'filename' => $definition['filename'],
                    'url' => $definition['url'],
                    'configured' => RispettoQrCode::isConfigured($type),
                    'download_url' => route('filament.admin.qr-codes.download', ['type' => $type]),
                ];
            })
            ->values()
            ->all();
    }
}
