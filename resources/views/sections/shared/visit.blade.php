@php
    $contact = config('site.contact');
    $hours = config('site.opening_hours');
@endphp

<x-site.section class="border-b border-rispetto-border !pb-16 !pt-0 sm:!pb-20">
    {{-- Mapa + kartica (tekst desno, kartica levo malo uvučena) --}}
    <x-site.container class="relative">
        {{-- Naslov — desno poravnat --}}
        <div class="mb-10 ml-auto max-w-2xl text-right sm:mb-12 lg:mb-14 lg:max-w-xl">
            <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Vaše novo
                <em class="font-display font-bold italic text-rispetto-accent">omiljeno mesto</em>
            </h2>
            <p class="site-subheading mt-4 lg:ml-auto">
                Napravite pauzu od svega i prepustite se ukusima koji vraćaju osmeh na lice. Vaš savršen beg od svakodnevice čeka vas svakog dana u nedelji.
            </p>
        </div>

        <div class="relative w-full overflow-visible">
            {{-- Radno vreme — levo, malo uvučena, preklapa mapu od sredine naviše --}}
            <div class="relative z-10 mb-6 w-full max-w-md max-lg:-mb-12 max-lg:ml-6 sm:max-lg:ml-8 lg:absolute lg:bottom-[12.5rem] lg:left-8 lg:mb-0 lg:max-w-[20rem] xl:left-12 xl:max-w-[22rem]">
                <div class="rounded-2xl border border-rispetto-border bg-rispetto-elevated p-6 shadow-xl sm:p-8">
                    <h3 class="font-display text-2xl font-bold text-rispetto-text">Radno vreme</h3>

                    <ul class="mt-6 space-y-0">
                        @foreach ($hours as $row)
                            <li class="flex items-center gap-3 border-b border-rispetto-border py-3 text-sm last:border-b-0 sm:text-base">
                                <span class="shrink-0 text-rispetto-muted">{{ $row['label'] }}</span>
                                <span class="h-px flex-1 bg-rispetto-border" aria-hidden="true"></span>
                                <span @class([
                                    'shrink-0 font-medium text-rispetto-text',
                                    'text-rispetto-muted' => $row['closed'] ?? false,
                                ])>
                                    {{ $row['hours'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6 space-y-3">
                        <a
                            href="{{ $contact['maps_link'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 rounded-xl border border-rispetto-border bg-rispetto-surface p-4 transition hover:border-rispetto-accent/40"
                        >
                            <x-site.icon-circle icon="location" />
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-rispetto-text">{{ $contact['name'] }}</span>
                                <span class="block text-sm text-rispetto-muted">{{ $contact['address'] }}</span>
                            </span>
                        </a>

                        <a
                            href="{{ config('site.phone_href') }}"
                            class="flex items-center gap-3 rounded-xl border border-rispetto-border bg-rispetto-surface p-4 transition hover:border-rispetto-accent/40"
                        >
                            <x-site.icon-circle icon="phone" />
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-rispetto-text">Kontakt</span>
                                <span class="block text-sm text-rispetto-muted">{{ config('site.phone') }}</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Mapa --}}
            <div class="-mx-4 h-[25rem] max-h-[25rem] w-[calc(100%+2rem)] overflow-hidden rounded-none border-y border-rispetto-border bg-rispetto-surface sm:-mx-6 sm:w-[calc(100%+3rem)] lg:mx-0 lg:w-full lg:rounded-3xl lg:border">
                <iframe
                    title="Lokacija restorana Rispetto"
                    src="{{ $contact['maps_embed_url'] }}"
                    class="size-full border-0"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </x-site.container>
</x-site.section>
