@php
    $contact = config('site.contact');
@endphp

<footer class="bg-rispetto-bg">
    <x-site.container class="pt-14 pb-14 sm:pt-16 sm:pb-16">
        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
            {{-- Kolona 1: brend --}}
            <div class="flex flex-col items-start gap-5">
                <x-site.footer-brand />
                <p class="max-w-[17rem] text-sm leading-relaxed text-rispetto-text/90">
                    {{ config('site.footer.tagline') }}
                </p>
                <x-site.social-links />
            </div>

            {{-- Kolona 2: kontakt --}}
            <div class="flex flex-col items-start gap-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-rispetto-text">
                    Kontaktirajte nas
                </h2>
                <ul class="flex w-full flex-col gap-4">
                    <li>
                        <a href="{{ config('site.phone_href') }}" class="flex items-center gap-3 text-sm text-rispetto-text transition hover:text-rispetto-accent">
                            <x-site.icon-circle icon="phone" />
                            <span>{{ config('site.phone') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:{{ $contact['email'] }}" class="flex items-center gap-3 text-sm text-rispetto-text transition hover:text-rispetto-accent">
                            <x-site.icon-circle icon="email" />
                            <span>{{ $contact['email'] }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ $contact['maps_link'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 text-sm text-rispetto-text transition hover:text-rispetto-accent">
                            <x-site.icon-circle icon="location" />
                            <span>{{ $contact['address'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolona 3: navigacija --}}
            <div class="flex flex-col items-start gap-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-rispetto-text">
                    Navigacija
                </h2>
                <ul class="flex flex-col gap-3">
                    @foreach (config('site.footer_navigation') as $link)
                        <li>
                            <a
                                href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}"
                                class="text-sm text-rispetto-text transition hover:text-rispetto-accent"
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Kolona 4: partneri --}}
            <div class="flex flex-col items-start gap-5">
                <h2 class="text-sm font-bold uppercase tracking-wide text-rispetto-text">
                    Partneri sa:
                </h2>
                @if (file_exists(public_path(config('site.partner.logo'))))
                    <x-site.optimized-image
                        :src="config('site.partner.logo')"
                        :alt="config('site.partner.name')"
                        class="h-14 w-auto max-w-[11rem] object-contain object-left"
                        :width="200"
                        :height="80"
                        sizes="200px"
                    />
                @else
                    <div class="flex h-14 max-w-[11rem] items-center gap-2">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-rispetto-accent text-lg font-bold text-rispetto-on-accent" aria-hidden="true">A</span>
                        <span class="font-display text-xl font-bold text-rispetto-text">{{ config('site.partner.name') }}</span>
                    </div>
                @endif
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
