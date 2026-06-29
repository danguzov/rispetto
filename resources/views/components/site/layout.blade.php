@props([
    'title' => null,
    'description' => null,
    'ogImage' => null,
    'robots' => null,
    'lcpImage' => null,
])

@php
    use App\Support\SiteOrdering;
@endphp

<!DOCTYPE html>
<html lang="sr">
    <head>
        @include('partials.site-head', [
            'title' => $title,
            'description' => $description,
            'ogImage' => $ogImage,
            'robots' => $robots,
            'lcpImage' => $lcpImage,
        ])
    </head>
    <body
        class="min-h-screen flex flex-col"
        @if (config('ordering.enabled', true))
            data-cart-show-url="{{ route('cart.show') }}"
            data-cart-add-url="{{ route('cart.add') }}"
            data-cart-update-url="{{ url('/korpa/stavka') }}"
            data-ordering-accepting="{{ SiteOrdering::isAcceptingOrders() ? 'true' : 'false' }}"
            data-ordering-message="{{ SiteOrdering::closedMessage() }}"
        @endif
    >
        <div class="site-sticky-header sticky top-0 z-50">
            @if (config('ordering.enabled', true))
                <x-site.ordering-banner />
            @endif

            <x-site.header />
        </div>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-site.footer />

        @if (config('ordering.enabled', true))
            <x-site.cart-variant-dialog />
        @endif
    </body>
</html>
