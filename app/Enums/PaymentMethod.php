<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Gotovina',
        };
    }
}
