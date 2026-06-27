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
        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25h9.75m-9.75 0a2.25 2.25 0 0 0-2.25 2.25v.75a2.25 2.25 0 0 0 2.25 2.25h9.75a2.25 2.25 0 0 0 2.25-2.25v-.75a2.25 2.25 0 0 0-2.25-2.25m-9.75 0V9.75A2.25 2.25 0 0 1 9.75 7.5h4.5A2.25 2.25 0 0 1 16.5 9.75v4.5" />
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
