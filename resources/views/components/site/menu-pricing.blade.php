@props([
    'item',
    'compact' => false,
])

@php
    /** @var \App\Models\MenuItem $item */
    $variants = $item->normalizedPriceVariants();
@endphp

@if ($item->hasPriceVariants())
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
