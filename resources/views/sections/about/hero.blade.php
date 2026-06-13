@php
$images = [
[
'src' => 'images/site/o-nama-hero-1.jpg',
'alt' => 'Pečena pasta u restoranu Rispetto',
],
[
'src' => 'images/site/o-nama-hero-2.jpg',
'alt' => 'Pasta sa belim sosom u Rispettu',
],
[
'src' => 'images/site/o-nama-hero-3.jpg',
'alt' => 'Sendvič i pomfrit u Rispettu',
],
];
@endphp

<x-site.section variant="hero" class="!overflow-visible">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="space-y-5 text-center lg:text-left">
                <h1 class="font-display text-4xl font-medium leading-tight text-rispetto-text sm:text-5xl lg:text-6xl">
                    Mi smo
                    <span class="font-display font-medium text-rispetto-accent">Rispetto</span>
                </h1>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Italijanski restoran u Batajnici gde se prava italijanska kuhinja spaja sa opuštenom atmosferom. Svako jelo spremamo od svežih sastojaka, prateći tradicionalne recepte, ali na naš način.
                </p>
            </div>

            <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] items-stretch gap-3 sm:gap-4 lg:min-h-[24rem] lg:gap-5 xl:min-h-[28rem]">
                <div class="flex flex-col gap-3 sm:gap-4 lg:gap-5">
                    @foreach (array_slice($images, 0, 2) as $image)
                    <div class="overflow-hidden rounded-2xl border border-rispetto-border bg-rispetto-surface">
                        <div class="aspect-[4/3]">
                            @if (file_exists(public_path($image['src'])))
                            <div class="size-full [&_img]:size-full [&_img]:object-cover [&_picture]:block [&_picture]:size-full">
                                <x-site.optimized-image
                                    :src="$image['src']"
                                    :alt="$image['alt']"
                                    class="size-full object-cover"
                                    :width="640"
                                    :height="480"
                                    sizes="(max-width: 1024px) 45vw, 280px" />
                            </div>
                            @else
                            <div class="flex size-full min-h-[8rem] items-center justify-center p-4 text-center">
                                <p class="text-xs leading-snug text-rispetto-muted">
                                    Dodajte sliku na
                                    <code class="text-rispetto-accent">public/{{ $image['src'] }}</code>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="overflow-hidden rounded-2xl border border-rispetto-border bg-rispetto-surface lg:-me-8 lg:rounded-e-none xl:-me-16">
                    <div class="aspect-[3/4] h-full min-h-[14rem] sm:aspect-[4/5] lg:aspect-auto lg:min-h-full">
                        @if (file_exists(public_path($images[2]['src'])))
                        <div class="size-full [&_img]:size-full [&_img]:object-cover [&_picture]:block [&_picture]:size-full">
                            <x-site.optimized-image
                                :src="$images[2]['src']"
                                :alt="$images[2]['alt']"
                                class="size-full object-cover"
                                :width="720"
                                :height="960"
                                sizes="(max-width: 1024px) 50vw, 360px" />
                        </div>
                        @else
                        <div class="flex size-full min-h-[14rem] items-center justify-center p-4 text-center lg:min-h-[24rem]">
                            <p class="text-xs leading-snug text-rispetto-muted">
                                Dodajte sliku na
                                <code class="text-rispetto-accent">public/{{ $images[2]['src'] }}</code>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-site.container>
</x-site.section>