@php
    use App\Support\SiteOrdering;
@endphp

@if (SiteOrdering::isEnabled())
    <div
        @class([
            'site-ordering-banner',
            'hidden' => SiteOrdering::isAcceptingOrders(),
        ])
        data-ordering-banner
        data-visible="{{ SiteOrdering::isAcceptingOrders() ? 'false' : 'true' }}"
        role="status"
        aria-live="polite"
    >
        <x-site.container class="flex min-h-12 items-center justify-center gap-3 py-3 text-center">
            <svg class="size-5 shrink-0 text-rispetto-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="text-sm font-medium text-rispetto-text" data-ordering-banner-message>
                {{ SiteOrdering::closedMessage() }}
            </p>
        </x-site.container>
    </div>
@endif
