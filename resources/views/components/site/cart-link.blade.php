@props([
    'showLabel' => false,
])

@php
    use App\Support\SiteOrdering;
@endphp

@if (SiteOrdering::isEnabled())
    <a
        href="{{ route('cart.show') }}"
        {{ $attributes->class([
            'site-cart-link relative inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-rispetto-border text-rispetto-text transition hover:border-rispetto-accent/40 hover:text-rispetto-accent',
            'min-w-12' => ! $showLabel,
            'w-full px-6' => $showLabel,
        ]) }}
        aria-label="Korpa"
        data-cart-link
    >
        <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
        </svg>
        @if ($showLabel)
            <span class="text-sm font-semibold uppercase tracking-[0.12em]">Korpa</span>
        @endif
        <span
            class="site-cart-link__badge absolute -end-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rispetto-accent px-1 text-[0.625rem] font-bold leading-none text-rispetto-on-accent"
            data-cart-badge
            hidden
        >0</span>
    </a>
@endif
