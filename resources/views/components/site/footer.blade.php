@php
    $contact = config('site.contact');
@endphp

<footer class="site-reveal bg-rispetto-bg" data-site-reveal>
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
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text">
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
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text">
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
            @php
                $partner = config('site.partner');
                $partnerLogo = $partner['logo'];
                $partnerLogoExists = file_exists(public_path($partnerLogo));
                $partnerLogoIsSvg = str_ends_with(strtolower($partnerLogo), '.svg');
            @endphp
            <div class="flex flex-col items-start gap-5">
                <h2 class="text-sm font-medium uppercase tracking-wide text-rispetto-text">
                    Partneri sa:
                </h2>
                @if ($partnerLogoExists)
                    <a
                        href="{{ $partner['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-14 max-w-[11rem] items-center transition opacity-90 hover:opacity-100"
                        aria-label="{{ $partner['name'] }}"
                    >
                        @if ($partnerLogoIsSvg)
                            <img
                                src="{{ asset($partnerLogo) }}"
                                alt="{{ $partner['name'] }}"
                                class="h-14 w-auto max-w-[11rem] object-contain object-left"
                                width="200"
                                height="80"
                            />
                        @else
                            <x-site.optimized-image
                                :src="$partnerLogo"
                                :alt="$partner['name']"
                                class="h-14 w-auto max-w-[11rem] object-contain object-left"
                                :width="200"
                                :height="80"
                                sizes="200px"
                            />
                        @endif
                    </a>
                @else
                    <div class="flex h-14 w-full max-w-[11rem] items-center justify-center rounded-xl border border-dashed border-rispetto-border/80 px-4">
                        <span class="text-center text-xs leading-snug text-rispetto-muted">
                            Logo partnera<br>
                            <span class="text-rispetto-accent">partner-alideda.svg</span>
                        </span>
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
