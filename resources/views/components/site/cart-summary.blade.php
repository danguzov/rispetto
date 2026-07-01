@props([
    'items',
    'subtotal',
    'itemCount',
    'acceptingOrders' => true,
    'interactive' => true,
    'showCheckoutCta' => true,
    'showOrderTotal' => false,
    'showDeliveryBreakdown' => false,
    'deliveryFee' => 0,
    'orderTotal' => null,
])

@php
    /** @var list<array{key: string, menu_item_id: int, name: string, variant_label: string|null, unit_price: float, quantity: int}> $items */
    $resolvedOrderTotal = $orderTotal ?? $subtotal;
@endphp

<div
    class="space-y-8"
    @if ($interactive) data-cart-page @endif
    data-cart-empty="{{ $items === [] ? 'true' : 'false' }}"
>
    @if ($items === [])
        <div class="site-panel border-dashed p-10 text-center" data-cart-empty-state>
            <p class="text-base text-rispetto-muted sm:text-lg">Korpa je prazna.</p>
            <a href="{{ route('menu') }}" class="site-btn-primary mt-6 min-h-12">
                Pogledaj jelovnik
            </a>
        </div>
    @else
        <div class="site-panel divide-y divide-rispetto-border" data-cart-items>
            @foreach ($items as $item)
                @php
                    $lineTotal = $item['unit_price'] * $item['quantity'];
                @endphp
                <article
                    @class([
                        'site-cart-item flex flex-col gap-4 p-5 sm:p-6',
                        'sm:flex-row sm:items-center sm:justify-between' => $interactive,
                    ])
                    @if ($interactive) data-cart-item data-cart-key="{{ $item['key'] }}" @endif
                >
                    <div class="min-w-0 flex-1 space-y-1">
                        <p class="text-lg font-medium text-rispetto-text">{{ $item['name'] }}</p>
                        @if ($item['variant_label'])
                            <p class="text-sm text-rispetto-muted">{{ $item['variant_label'] }}</p>
                        @endif
                        <p class="text-sm text-rispetto-muted">
                            {{ number_format($item['unit_price'], 0, ',', '.') }} RSD / kom
                            @unless ($interactive)
                                · {{ $item['quantity'] }} kom
                            @endunless
                        </p>
                    </div>

                    <div @class([
                        'flex flex-wrap items-center gap-4',
                        'justify-between sm:justify-end' => $interactive,
                        'justify-end' => ! $interactive,
                    ])>
                        @if ($interactive)
                            <div class="site-cart-qty" data-cart-qty>
                                <button
                                    type="button"
                                    class="site-cart-qty__btn"
                                    data-cart-decrease
                                    aria-label="Smanji količinu za {{ $item['name'] }}"
                                >
                                    <span aria-hidden="true">−</span>
                                </button>
                                <span class="site-cart-qty__value" data-cart-quantity>{{ $item['quantity'] }}</span>
                                <button
                                    type="button"
                                    class="site-cart-qty__btn"
                                    data-cart-increase
                                    aria-label="Povećaj količinu za {{ $item['name'] }}"
                                >
                                    <span aria-hidden="true">+</span>
                                </button>
                            </div>
                        @endif

                        <p @class([
                            'text-base font-bold text-rispetto-accent',
                            'min-w-[6.5rem] text-end' => $interactive,
                        ]) @if ($interactive) data-cart-line-total @endif>
                            {{ number_format($lineTotal, 0, ',', '.') }} RSD
                        </p>

                        @if ($interactive)
                            <button
                                type="button"
                                class="site-cart-remove min-h-12 text-sm font-medium text-rispetto-muted underline-offset-4 transition hover:text-rispetto-text hover:underline"
                                data-cart-remove
                            >
                                Ukloni
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="site-panel space-y-6 p-6 sm:p-8" data-cart-footer>
            <div class="space-y-3 border-b border-rispetto-border pb-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-base text-rispetto-muted">
                        Stavki u korpi: <span class="font-medium text-rispetto-text" @if ($interactive) data-cart-item-count @endif>{{ $itemCount }}</span>
                    </p>
                    @unless ($showOrderTotal)
                        <p class="text-xl font-medium text-rispetto-text">
                            Ukupno:
                            <span class="font-bold text-rispetto-accent" @if ($interactive) data-cart-subtotal @endif>{{ number_format($subtotal, 0, ',', '.') }} RSD</span>
                        </p>
                    @endunless
                </div>

                @if ($showOrderTotal)
                    <div class="space-y-2 text-sm sm:text-base">
                        <div class="flex items-center justify-between gap-4 text-rispetto-muted">
                            <span>Jela</span>
                            <span class="font-medium text-rispetto-text" data-checkout-summary-subtotal>{{ number_format($subtotal, 0, ',', '.') }} RSD</span>
                        </div>
                        <div
                            class="flex items-center justify-between gap-4 text-rispetto-muted"
                            data-checkout-summary-delivery-row
                            @unless ($showDeliveryBreakdown) hidden @endunless
                        >
                            <span>Dostava</span>
                            <span class="font-medium text-rispetto-text" data-checkout-summary-delivery>
                                @if ($deliveryFee > 0)
                                    {{ number_format($deliveryFee, 0, ',', '.') }} RSD
                                @else
                                    Besplatno
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-rispetto-border pt-3 text-lg font-medium text-rispetto-text">
                            <span>Ukupno</span>
                            <span class="font-bold text-rispetto-accent" data-checkout-summary-total>{{ number_format($resolvedOrderTotal, 0, ',', '.') }} RSD</span>
                        </div>
                    </div>
                @endif
            </div>

            @if ($showCheckoutCta || $interactive)
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    @if ($showCheckoutCta)
                        @if ($acceptingOrders)
                            <a href="{{ route('checkout.show') }}" class="site-btn-primary min-h-12 flex-1 sm:flex-none">
                                Naruči
                            </a>
                        @else
                            <button type="button" class="site-btn-primary min-h-12 flex-1 cursor-not-allowed opacity-50 sm:flex-none" disabled>
                                Naruči
                            </button>
                            <p class="w-full text-sm text-rispetto-muted">{{ config('ordering.closed_message') }}</p>
                        @endif
                    @endif

                    @if ($interactive)
                        <a href="{{ route('menu') }}" class="site-btn-secondary min-h-12 flex-1 sm:flex-none">
                            Dodaj još jela
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
