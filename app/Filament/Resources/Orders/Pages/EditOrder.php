<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\Ordering\OrderStatusService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['status'] = $this->record->status->value;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $status = OrderStatus::from((string) $data['status']);
        $note = filled($data['admin_note'] ?? null) ? trim((string) $data['admin_note']) : null;

        return app(OrderStatusService::class)->setStatusForAdmin(
            $record,
            $status,
            auth()->user(),
            $note,
        );
    }
}
