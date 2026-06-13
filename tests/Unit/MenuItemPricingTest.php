<?php

use App\Models\MenuItem;

test('menu item exposes normalized pizza price variants', function (): void {
    $item = new MenuItem([
        'name' => 'Capricciosa',
        'price_variants' => [
            ['label' => '25 cm', 'price' => 850],
            ['label' => '35 cm', 'price' => 1150],
            ['label' => '', 'price' => 999],
        ],
    ]);

    expect($item->hasPriceVariants())->toBeTrue();
    expect($item->normalizedPriceVariants())->toHaveCount(2);
    expect($item->normalizedPriceVariants()[0])->toMatchArray(['label' => '25 cm', 'price' => 850.0]);
    expect($item->displayPrice())->toBeNull();
    expect($item->displayStartingPrice())->toBe('850');
});

test('menu item without variants uses single price', function (): void {
    $item = new MenuItem([
        'name' => 'Pasta',
        'price' => 720,
    ]);

    expect($item->hasPriceVariants())->toBeFalse();
    expect($item->displayPrice())->toBe('720');
});
