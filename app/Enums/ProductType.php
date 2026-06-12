<?php

namespace App\Enums;

enum ProductType: string
{
    case Food = 'food';
    case Drink = 'drink';

    public function label(): string
    {
        return match ($this) {
            self::Food => 'Hrana',
            self::Drink => 'Piće',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
