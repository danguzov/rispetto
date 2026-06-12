@php
    $images = [
        [
            'src' => 'images/site/o-nama-story-1.jpg',
            'alt' => 'Jelo sa pomfritom u restoranu Rispetto',
            'bleed' => true,
        ],
        [
            'src' => 'images/site/o-nama-story-2.jpg',
            'alt' => 'Tortilja sa pomfritom u Rispettu',
            'bleed' => false,
        ],
    ];
@endphp

<x-site.section class="!overflow-visible !py-12 sm:!py-16 lg:!py-20">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="flex items-stretch gap-3 sm:gap-4 lg:gap-5">
                @foreach ($images as $image)
                    <div @class([
                        'overflow-hidden border border-rispetto-border bg-rispetto-surface',
                        'hidden aspect-[4/5] min-h-[14rem] w-[42%] shrink-0 rounded-e-2xl border-s-0 sm:block lg:-ms-16 lg:w-[38%] xl:-ms-24' => $image['bleed'],
                        'aspect-[3/4] w-full rounded-2xl sm:w-[58%] lg:w-[62%]' => ! $image['bleed'],
                    ])>
                        @if (file_exists(public_path($image['src'])))
                            <div class="size-full [&_img]:size-full [&_img]:object-cover [&_picture]:block [&_picture]:size-full">
                                <x-site.optimized-image
                                    :src="$image['src']"
                                    :alt="$image['alt']"
                                    class="size-full object-cover"
                                    :width="$image['bleed'] ? 480 : 560"
                                    :height="$image['bleed'] ? 600 : 720"
                                    sizes="(max-width: 1024px) 50vw, 320px"
                                />
                            </div>
                        @else
                            <div class="flex size-full min-h-[12rem] items-center justify-center p-4 text-center">
                                <p class="text-xs leading-snug text-rispetto-muted">
                                    Dodajte sliku na
                                    <code class="text-rispetto-accent">public/{{ $image['src'] }}</code>
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="space-y-6 text-center lg:text-left">
                <h2 class="font-sans text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Više o
                    <span class="text-rispetto-accent">restoranu</span>
                </h2>

                <div class="site-subheading mx-auto max-w-xl space-y-4 lg:mx-0">
                    <p>
                        Rispetto je otvoren 2025. godine sa idejom da Batajnici ponudi mesto gde se kvalitetna italijanska jela i prijatan ambijent podrazumevaju. Danas smo prostor u koji se dolazi zbog ukusa, ali se ostaje zbog osećaja.
                    </p>
                    <p>
                        Kod nas svaki obrok počinje od nule. Restoran radi po principu „á la carte”, što znači da se svako jelo priprema tek nakon porudžbine, sveže i na licu mesta. Pored italijanske kuhinje, jelovnik smo proširili tako da svako može da pronađe nešto za sebe, od pice i paste do domaćih klasika i laganijih obroka.
                    </p>
                    <p>
                        Porcije su obilne, ukusi puni, a odnos cene i onoga što dobijete je razlog zašto nam se gosti vraćaju. Kod nas je atmosfera topla i opuštena, uz tim kuvara sa dugogodišnjim iskustvom koji svako jelo priprema sa pažnjom i ljubavlju, tako da možete potpuno da se opustite i uživate sa svojom porodicom i prijateljima.
                    </p>
                </div>

                <div class="flex justify-center lg:justify-start">
                    <a
                        href="{{ route('menu') }}"
                        class="site-btn-primary px-8 py-4 text-xs uppercase tracking-[0.2em]"
                    >
                        Istraži jelovnik
                    </a>
                </div>
            </div>
        </div>
    </x-site.container>
</x-site.section>
