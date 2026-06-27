<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case OutForDelivery = 'out_for_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Na čekanju',
            self::Accepted => 'Prihvaćena',
            self::Preparing => 'U pripremi',
            self::Ready => 'Spremno',
            self::OutForDelivery => 'Na putu',
            self::Completed => 'Završena',
            self::Cancelled => 'Otkazana',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function filamentColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Accepted => 'info',
            self::Preparing => 'primary',
            self::Ready => 'success',
            self::OutForDelivery => 'gray',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
