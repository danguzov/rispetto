@php
    $options = [
        [
            'image' => 'images/site/dostava-pickup.jpg',
            'image_alt' => 'Lično preuzimanje hrane u restoranu Rispetto',
            'title' => 'Lično preuzimanje',
            'description' => 'Kontaktirajte nas telefonom, naručite željena jela i preuzmite ih u restoranu. Ovo je najbrži način da dođete do svoje hrane, bez čekanja na dostavu.',
            'href' => config('site.phone_href'),
            'label' => 'Kontakt',
            'icon' => 'phone',
        ],
        [
            'image' => 'images/site/dostava-alideda.jpg',
            'image_alt' => 'Dostava preko Alideda platforme',
            'title' => 'Naruči preko Alidede',
            'description' => 'Trenutno nemamo sopstvenu dostavu. Posetite Alideda platformu ili mobilnu aplikaciju, odaberite svoja omiljena jela i sačekajte brzu isporuku na željenu adresu.',
            'href' => config('site.partner.url'),
            'label' => 'Alideda platforma',
            'icon' => 'delivery',
        ],
    ];
@endphp

<x-site.section>
    <x-site.container>
        <div class="grid gap-6 sm:gap-8 lg:grid-cols-2 lg:gap-10">
            @foreach ($options as $option)
                <article class="delivery-option-card">
                    <div class="delivery-option-card__media">
                        @if (file_exists(public_path($option['image'])))
                            <x-site.optimized-image
                                :src="$option['image']"
                                :alt="$option['image_alt']"
                                class="size-full object-cover"
                                :width="720"
                                :height="450"
                                sizes="(max-width: 1024px) 100vw, 50vw"
                            />
                        @else
                            <div class="flex size-full min-h-[12rem] items-center justify-center p-6 text-center">
                                <p class="text-sm text-rispetto-muted">
                                    Dodajte sliku na
                                    <code class="text-rispetto-accent">public/{{ $option['image'] }}</code>
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="delivery-option-card__body">
                        <h2 class="font-display text-2xl font-bold leading-tight text-rispetto-text sm:text-3xl lg:text-[2rem]">
                            {{ $option['title'] }}
                        </h2>

                        <p class="mt-4 flex-1 text-base leading-relaxed text-rispetto-muted sm:mt-5 sm:text-lg">
                            {{ $option['description'] }}
                        </p>

                        <a
                            href="{{ $option['href'] }}"
                            @if ($option['icon'] === 'delivery') target="_blank" rel="noopener noreferrer" @endif
                            class="delivery-option-card__cta"
                        >
                            @if ($option['icon'] === 'phone')
                                <svg class="size-4 shrink-0 sm:size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            @else
                                <svg class="size-4 shrink-0 sm:size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            @endif
                            {{ $option['label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </x-site.container>
</x-site.section>
