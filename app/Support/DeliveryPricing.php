<?php

namespace App\Support;

use App\Enums\FulfillmentType;

class DeliveryPricing
{
    public static function deliveryAreaKey(): string
    {
        return (string) config('ordering.delivery_area', 'batajnica');
    }

    public static function deliveryAreaLabel(): string
    {
        return self::areaLabel(self::deliveryAreaKey()) ?? 'Batajnica';
    }

    public static function deliveryIsConfigured(): bool
    {
        return self::hasConfiguredFee(self::deliveryAreaKey());
    }

    /**
     * @return array<string, array{label: string, fee?: float|int|null}|string>
     */
    public static function areas(): array
    {
        return config('ordering.delivery_areas', []);
    }

    /**
     * @return list<string>
     */
    public static function areaKeys(): array
    {
        return array_keys(self::areas());
    }

    /**
     * @return array<string, string>
     */
    public static function areaOptions(): array
    {
        $options = [];

        foreach (self::areas() as $key => $area) {
            $label = self::areaLabel((string) $key);

            if ($label !== null) {
                $options[(string) $key] = $label;
            }
        }

        return $options;
    }

    /**
     * @return array<string, float>
     */
    public static function configuredAreaFees(): array
    {
        $fees = [];

        foreach (self::areaKeys() as $key) {
            $fee = self::areaFee($key);

            if ($fee !== null) {
                $fees[$key] = $fee;
            }
        }

        return $fees;
    }

    public static function areaLabel(string $key): ?string
    {
        $area = self::areas()[$key] ?? null;

        if (is_string($area)) {
            return $area;
        }

        if (is_array($area) && is_string($area['label'] ?? null)) {
            return $area['label'];
        }

        return null;
    }

    public static function areaFee(string $key): ?float
    {
        $area = self::areas()[$key] ?? null;

        if (! is_array($area)) {
            return null;
        }

        $fee = $area['fee'] ?? null;

        if ($fee === null || $fee === '') {
            return null;
        }

        return (float) $fee;
    }

    public static function hasConfiguredFee(string $key): bool
    {
        return self::areaFee($key) !== null;
    }

    public static function freeAbove(): float
    {
        return (float) config('ordering.delivery_free_above', 3000);
    }

    public static function fee(float $subtotal, FulfillmentType $fulfillmentType, ?string $areaKey = null): float
    {
        if ($fulfillmentType !== FulfillmentType::Delivery) {
            return 0.0;
        }

        $areaKey = $areaKey ?: self::deliveryAreaKey();

        if ($areaKey !== self::deliveryAreaKey()) {
            return 0.0;
        }

        $areaFee = self::areaFee($areaKey);

        if ($areaFee === null) {
            return 0.0;
        }

        if ($subtotal >= self::freeAbove()) {
            return 0.0;
        }

        return $areaFee;
    }

    public static function total(float $subtotal, FulfillmentType $fulfillmentType, ?string $areaKey): float
    {
        return round($subtotal + self::fee($subtotal, $fulfillmentType, $areaKey), 2);
    }
}
