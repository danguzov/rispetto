@php
    use App\Support\SiteOrdering;

    $options = [];

    if (SiteOrdering::isEnabled()) {
        $options[] = [
            'image' => 'images/site/dostava-online.jpg',
            'image_alt' => 'Online poručivanje hrane iz restorana Rispetto',
            'title' => 'Poruči online',
            'description' => 'Izaberite jela sa jelovnika, dodajte u korpu i završite porudžbinu za nekoliko minuta. Dostupno za lično preuzimanje i dostavu na adresu.',
            'href' => route('menu'),
            'label' => 'Pogledaj jelovnik',
            'icon' => 'cart',
            'external' => false,
        ];
    }

    $options = array_merge($options, [
        [
            'image' => 'images/site/dostava-pickup.jpg',
            'image_alt' => 'Lično preuzimanje hrane u restoranu Rispetto',
            'title' => 'Lično preuzimanje',
            'description' => 'Poručite online i preuzmite jela u restoranu, ili nas pozovite telefonom ako vam je lakše.',
            'href' => config('site.phone_href'),
            'label' => 'Pozovite nas',
            'icon' => 'phone',
            'external' => false,
        ],
        [
            'image' => 'images/site/dostava-alideda.jpg',
            'image_alt' => 'Dostava preko Alideda platforme',
            'title' => 'Naruči preko Alidede',
            'description' => 'Alternativno, posetite Alideda platformu ili mobilnu aplikaciju i naručite omiljena jela za brzu isporuku.',
            'href' => config('site.partner.url'),
            'label' => 'Alideda platforma',
            'icon' => 'delivery',
            'external' => true,
        ],
    ]);
@endphp

<x-site.section>
    <x-site.container>
        <div @class([
            'grid gap-6 sm:gap-8 lg:gap-10',
            count($options) >= 3 ? 'md:grid-cols-2 xl:grid-cols-3' : 'lg:grid-cols-2',
        ])>
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
                                sizes="(max-width: 1024px) 100vw, 33vw"
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
                        <h2 class="font-display text-2xl font-medium leading-tight text-rispetto-text sm:text-3xl lg:text-[2rem]">
                            {{ $option['title'] }}
                        </h2>

                        <p class="mt-4 flex-1 text-base leading-relaxed text-rispetto-muted sm:mt-5 sm:text-lg">
                            {{ $option['description'] }}
                        </p>

                        <a
                            href="{{ $option['href'] }}"
                            @if ($option['external']) target="_blank" rel="noopener noreferrer" @endif
                            class="delivery-option-card__cta"
                        >
                            @if ($option['icon'] === 'phone')
                                <svg class="size-4 shrink-0 sm:size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                            @elseif ($option['icon'] === 'cart')
                                <svg class="size-4 shrink-0 sm:size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25h9.75m-9.75 0a3 3 0 1 0 0 6h9.75a3 3 0 1 0 0-6m-9.75 0H5.106a1.125 1.125 0 0 1-1.06-.75L3 6.75M7.5 14.25 5.106 5.25M17.25 14.25V6.75a2.25 2.25 0 0 0-2.25-2.25h-5.379" />
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
