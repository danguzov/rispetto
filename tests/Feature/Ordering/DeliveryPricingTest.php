<?php

use App\Enums\FulfillmentType;
use App\Support\DeliveryPricing;

test('delivery pricing returns batajnica fee below free threshold', function (): void {
    expect(DeliveryPricing::fee(1500, FulfillmentType::Delivery, 'batajnica'))->toBe(250.0)
        ->and(DeliveryPricing::total(1500, FulfillmentType::Delivery, 'batajnica'))->toBe(1750.0);
});

test('delivery pricing is free above threshold', function (): void {
    expect(DeliveryPricing::fee(3000, FulfillmentType::Delivery, 'batajnica'))->toBe(0.0)
        ->and(DeliveryPricing::fee(4200, FulfillmentType::Delivery, 'batajnica'))->toBe(0.0)
        ->and(DeliveryPricing::total(3000, FulfillmentType::Delivery, 'batajnica'))->toBe(3000.0);
});

test('delivery pricing ignores fee for pickup', function (): void {
    expect(DeliveryPricing::fee(1500, FulfillmentType::Pickup, 'batajnica'))->toBe(0.0);
});

test('delivery pricing only supports configured batajnica area', function (): void {
    expect(DeliveryPricing::deliveryAreaKey())->toBe('batajnica')
        ->and(DeliveryPricing::deliveryAreaLabel())->toBe('Batajnica')
        ->and(DeliveryPricing::deliveryIsConfigured())->toBeTrue()
        ->and(DeliveryPricing::areaFee('busije'))->toBeNull();
});
