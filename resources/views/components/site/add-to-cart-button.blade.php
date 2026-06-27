@props([
    'item',
    'pizzaSizeFilter' => false,
    'compact' => false,
])

@php
    /** @var \App\Models\MenuItem $item */
    use App\Support\SiteOrdering;

    $variants = $item->normalizedPriceVariants();
@endphp

@if (SiteOrdering::isEnabled())
    <button
        type="button"
        @class([
            'site-cart-add',
            'site-btn-secondary min-h-12 shrink-0' => ! $compact,
            'site-cart-add--compact mt-3 inline-flex min-h-12 w-full items-center justify-center rounded-full border border-rispetto-border px-4 text-sm font-semibold text-rispetto-text transition hover:border-rispetto-accent/50 hover:text-rispetto-accent' => $compact,
        ])
        data-cart-add
        data-menu-item-id="{{ $item->id }}"
        data-item-name="{{ $item->name }}"
        data-has-variants="{{ $item->hasPriceVariants() ? 'true' : 'false' }}"
        data-pizza-size-filter="{{ $pizzaSizeFilter ? 'true' : 'false' }}"
        @if ($item->hasPriceVariants())
            data-variants='@json($variants)'
        @endif
        @unless (SiteOrdering::isAcceptingOrders())
            disabled
            aria-disabled="true"
            title="{{ config('ordering.closed_message') }}"
        @endunless
    >
        <span data-cart-add-label>Dodaj u korpu</span>
        <span class="sr-only">{{ $item->name }}</span>
    </button>
@endif
