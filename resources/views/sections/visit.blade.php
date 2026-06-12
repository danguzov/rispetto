@php
    $contact = config('site.contact');
    $hours = config('site.opening_hours');
@endphp

<x-site.section class="border-b border-rispetto-border !pb-16 !pt-0 sm:!pb-20">
    <x-site.container>
        {{-- Naslov centriran --}}
        <div class="mx-auto mb-10 max-w-2xl text-center sm:mb-12 lg:mb-14">
            <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Vaše novo
                <em class="font-display font-bold italic text-rispetto-accent">omiljeno mesto</em>
            </h2>
            <p class="site-subheading mx-auto mt-4">
                Napravite pauzu od svega i prepustite se ukusima koji vraćaju osmeh na lice. Vaš savršen beg od svakodnevice čeka vas svakog dana u nedelji.
            </p>
        </div>

        {{-- Kartica + mapa (u normalnom toku — bez absolute) --}}
        <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)] lg:gap-6 xl:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] xl:gap-8">
            {{-- Radno vreme --}}
            <div class="relative z-10 w-full max-w-md lg:max-w-none lg:-mr-10 xl:-mr-14">
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
            <div class="h-[20rem] overflow-hidden rounded-3xl border border-rispetto-border bg-rispetto-surface sm:h-[26rem] lg:h-[32rem] xl:h-[36rem]">
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
