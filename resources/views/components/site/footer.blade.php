@php
    $contact = config('site.contact');
@endphp

<footer class="border-t border-rispetto-border bg-rispetto-bg">
    <x-site.container class="pt-14 pb-14 sm:pt-16 sm:pb-16">
        <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between lg:gap-8">
            {{-- Logo i opis --}}
            <div class="flex max-w-[20rem] shrink-0 flex-col items-start gap-5">
                <x-site.footer-brand />
                <p class="text-sm leading-relaxed text-rispetto-text/90">
                    {{ config('site.footer.tagline') }}
                </p>
            </div>

            {{-- Navigacija --}}
            <div class="flex shrink-0 flex-col items-start gap-4">
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text">
                    Navigacija
                </h2>
                <ul class="flex flex-col gap-0.5">
                    @foreach (config('site.footer_navigation') as $link)
                        <li>
                            <a
                                href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}"
                                class="inline-flex min-h-12 items-center py-0.5 text-sm text-rispetto-text transition hover:text-rispetto-accent"
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Kontakt --}}
            <div class="flex shrink-0 flex-col items-start gap-4">
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text">
                    Kontaktirajte nas
                </h2>
                <ul class="flex flex-col gap-2">
                    <li>
                        <a href="{{ config('site.phone_href') }}" class="flex min-h-12 items-center gap-3 text-sm text-rispetto-text transition hover:text-rispetto-accent">
                            <x-site.icon-circle icon="phone" />
                            <span>{{ config('site.phone') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $contact['maps_link'] }}" target="_blank" rel="noopener noreferrer" class="flex min-h-12 items-center gap-3 text-sm text-rispetto-text transition hover:text-rispetto-accent">
                            <x-site.icon-circle icon="location" />
                            <span>{{ $contact['address'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Društvene mreže --}}
            <div class="flex shrink-0 flex-col items-start gap-4 lg:items-end">
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text lg:text-right">
                    Zapratite nas
                </h2>
                <x-site.social-links class="lg:justify-end" />
            </div>
        </div>

        <div class="mt-12 border-t border-rispetto-border pt-6">
            <p class="text-left text-xs text-rispetto-muted">
                &copy; Rispetto Ristorante {{ date('Y') }}
                <span class="mx-2 text-rispetto-border">|</span>
                Izrada sajta:
                <a
                    href="{{ config('site.credits.url') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="transition hover:text-rispetto-accent"
                >
                    {{ config('site.credits.label') }}
                </a>
            </p>
        </div>
    </x-site.container>
</footer>
