@php
    $leftLinks = config('site.navigation.left');
    $rightLinks = config('site.navigation.right');
    $allLinks = array_merge($leftLinks, $rightLinks);
@endphp

<header class="sticky top-0 z-50 bg-rispetto-bg/95 backdrop-blur-md">
    <x-site.container tag="nav" class="relative flex h-20 items-center justify-between gap-4 lg:h-24" aria-label="Glavna navigacija">
        {{-- Desktop: leva navigacija --}}
        <div class="hidden flex-1 items-center gap-6 lg:flex xl:gap-10">
            @foreach ($leftLinks as $link)
                <x-site.nav-link
                    :href="isset($link['route']) ? route($link['route']) : $link['url']"
                    :active="isset($link['route']) && request()->routeIs($link['route'])"
                    class="uppercase tracking-[0.12em]"
                >
                    {{ $link['label'] }}
                </x-site.nav-link>
            @endforeach
        </div>

        {{-- Logo — centar na desktopu --}}
        <x-site.logo :href="route('home')" class="shrink-0 lg:absolute lg:left-1/2 lg:-translate-x-1/2" />

        {{-- Desktop: desna navigacija + telefon --}}
        <div class="hidden flex-1 items-center justify-end gap-6 lg:flex xl:gap-10">
            @foreach ($rightLinks as $link)
                <x-site.nav-link
                    :href="isset($link['route']) ? route($link['route']) : $link['url']"
                    :active="isset($link['route']) && request()->routeIs($link['route'])"
                    class="uppercase tracking-[0.12em]"
                >
                    {{ $link['label'] }}
                </x-site.nav-link>
            @endforeach

            <a href="{{ config('site.phone_href') }}" class="inline-flex items-center gap-2 text-sm font-medium text-rispetto-text transition hover:text-rispetto-accent">
                <svg class="size-4 text-rispetto-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
                {{ config('site.phone') }}
            </a>
        </div>

        {{-- Mobile: hamburger --}}
        <button
            type="button"
            class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl border border-rispetto-border text-rispetto-text transition hover:border-rispetto-accent/40 hover:text-rispetto-accent lg:hidden"
            data-mobile-nav-toggle
            aria-expanded="false"
            aria-controls="mobile-navigation"
        >
            <span class="sr-only" data-mobile-nav-toggle-label>Otvori meni</span>
            <svg class="size-6" data-mobile-nav-icon-open fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <svg class="hidden size-6" data-mobile-nav-icon-close fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </x-site.container>
</header>

{{-- Mobile drawer — mora biti van <header> jer backdrop-blur lomi position:fixed --}}
<div
    class="mobile-nav fixed inset-0 z-[60] lg:hidden"
    data-mobile-nav
    data-open="false"
    inert
>
    <button
        type="button"
        class="mobile-nav__backdrop absolute inset-0 bg-rispetto-bg/80 backdrop-blur-sm"
        data-mobile-nav-backdrop
        aria-label="Zatvori meni"
        tabindex="-1"
    ></button>

    <div
        id="mobile-navigation"
        class="mobile-nav__panel absolute inset-y-0 end-0 z-10 flex w-full max-w-[22rem] flex-col border-s border-rispetto-border bg-rispetto-surface shadow-2xl"
        data-mobile-nav-panel
        role="dialog"
        aria-modal="true"
        aria-label="Mobilna navigacija"
    >
        <div class="flex shrink-0 items-center justify-between border-b border-rispetto-border px-6 py-5">
            <span class="font-display text-xl font-bold uppercase tracking-wide text-rispetto-accent">Meni</span>
            <button
                type="button"
                class="inline-flex min-h-12 min-w-12 items-center justify-center rounded-xl border border-rispetto-border text-rispetto-text transition hover:border-rispetto-accent/40 hover:text-rispetto-accent"
                data-mobile-nav-close
                aria-label="Zatvori meni"
            >
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex min-h-0 flex-1 flex-col gap-3 overflow-y-auto px-4 py-6 sm:gap-4" aria-label="Stranice">
            @foreach ($allLinks as $link)
                <x-site.nav-link
                    :href="isset($link['route']) ? route($link['route']) : $link['url']"
                    :active="isset($link['route']) && request()->routeIs($link['route'])"
                    class="mobile-nav__link flex min-h-12 items-center rounded-xl px-4 py-4 !text-2xl !font-bold uppercase tracking-[0.08em] hover:bg-rispetto-elevated"
                >
                    {{ $link['label'] }}
                </x-site.nav-link>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-rispetto-border px-6 py-6">
            <a
                href="{{ config('site.phone_href') }}"
                class="inline-flex min-h-12 w-full items-center justify-center gap-3 rounded-full border border-rispetto-accent bg-rispetto-accent px-6 py-3 text-base font-bold uppercase tracking-[0.12em] text-rispetto-on-accent transition hover:bg-rispetto-accent-hover"
            >
                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
                {{ config('site.phone') }}
            </a>
        </div>
    </div>
</div>
