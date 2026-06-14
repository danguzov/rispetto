@props([
    'item',
    'compact' => false,
    'pizzaSizeFilter' => false,
])

@php
    /** @var \App\Models\MenuItem $item */
    $variants = $item->normalizedPriceVariants();
    $pizzaSizes = config('site.menu.pizza_sizes', []);
@endphp

@if ($item->hasPriceVariants() && $pizzaSizeFilter)
    @foreach ($pizzaSizes as $size)
        @if ($item->hasSizeKey($size['key']))
            <span
                data-pizza-size-price="{{ $size['key'] }}"
                @class([
                    'font-semibold text-rispetto-accent tabular-nums',
                    'text-lg sm:text-xl' => ! $compact,
                    'text-base font-bold sm:text-lg' => $compact,
                ])
            >
                {{ $item->formattedPrice($item->priceForSizeKey($size['key'])) }} RSD
            </span>
        @endif
    @endforeach
@elseif ($item->hasPriceVariants())
    <ul @class([
        'menu-price-variants',
        'menu-price-variants--compact' => $compact,
    ])>
        @foreach ($variants as $variant)
            <li class="menu-price-variants__row">
                <span class="menu-price-variants__label">{{ $variant['label'] }}</span>
                <span class="menu-price-variants__price">{{ $item->formattedPrice($variant['price']) }} RSD</span>
            </li>
        @endforeach
    </ul>
@elseif ($item->displayPrice())
    <span @class([
        'font-semibold text-rispetto-accent',
        'text-lg sm:text-xl' => ! $compact,
        'text-base font-bold sm:text-lg' => $compact,
    ])>
        {{ $item->displayPrice() }} RSD
    </span>
@endif
