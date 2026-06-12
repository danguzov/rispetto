<x-site.layout title="O nama" description="Upoznajte restoran Rispetto — našu priču, vrednosti i tim.">
    @include('sections.page-hero', [
        'eyebrow' => 'O nama',
        'title' => 'Naša priča',
        'description' => 'Rispetto je nastao iz ljubavi prema dobroj hrani i gostoprimstvu.',
    ])

    <x-site.section>
        <x-site.container>
            <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                <div class="space-y-5">
                    <h2 class="site-heading">Tradicija i savremenost</h2>
                    <p class="site-subheading">
                        Kombinujemo domaće recepte sa modernim pristupom serviranju. Svaki tanjir pričamo pažljivo —
                        od izbora sastojaka do poslednjeg detalja na stolu.
                    </p>
                    <p class="site-subheading">
                        Naš tim veruje da restoran nije samo mesto za jelo, već prostor za okupljanje, razgovor
                        i uspomene koje se prepričavaju godinama.
                    </p>
                </div>

                <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border border-rispetto-border bg-rispetto-elevated">
                    @if (file_exists(public_path('images/site/about.jpg')))
                        <x-site.optimized-image
                            src="images/site/about.jpg"
                            alt="Restoran Rispetto"
                            class="size-full object-cover"
                            :width="1024"
                            :height="768"
                            sizes="(max-width: 1024px) 100vw, 50vw"
                        />
                    @endif
                </div>
            </div>
        </x-site.container>
    </x-site.section>

    <x-site.section variant="muted">
        <x-site.container>
            <div class="grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['value' => 'Kvalitet', 'label' => 'Svežina i pažnja u svakom tanjiru'],
                    ['value' => 'Gostoprimstvo', 'label' => 'Svaki gost je deo naše porodice'],
                    ['value' => 'Poštovanje', 'label' => 'Prema hrani, gostima i tradiciji'],
                ] as $item)
                    <div class="site-panel p-6 text-center">
                        <p class="font-display text-2xl font-bold text-rispetto-accent">{{ $item['value'] }}</p>
                        <p class="mt-2 text-sm text-rispetto-muted">{{ $item['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-site.container>
    </x-site.section>

    @include('sections.cta')
</x-site.layout>
