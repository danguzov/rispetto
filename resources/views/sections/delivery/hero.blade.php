@php
    $heroImages = [
        [
            'src' => 'images/site/dostava-hero-1.jpg',
            'alt' => 'Predjelo u restoranu Rispetto',
            'size' => 'size-[8.5rem] sm:size-[10rem] lg:size-[11rem]',
            'position' => 'left-[4%] top-[6%] z-10',
        ],
        [
            'src' => 'images/site/dostava-hero-2.jpg',
            'alt' => 'Jelo sa pomfritom iz Rispetta',
            'size' => 'size-[9.5rem] sm:size-[11rem] lg:size-[12.5rem]',
            'position' => 'bottom-[2%] left-1/2 z-30 -translate-x-1/2',
        ],
        [
            'src' => 'images/site/dostava-hero-3.jpg',
            'alt' => 'Pasta u restoranu Rispetto',
            'size' => 'size-[8.5rem] sm:size-[10rem] lg:size-[11rem]',
            'position' => 'right-[4%] top-[10%] z-20',
        ],
    ];
@endphp

<x-site.section variant="hero" class="!overflow-visible !pb-12 !pt-8 sm:!pb-16 sm:!pt-10 lg:!pb-20">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="space-y-5 text-center lg:text-left">
                <h1 class="font-display text-4xl font-bold leading-tight text-rispetto-text sm:text-5xl lg:text-6xl">
                    Dostava hrane pravo do
                </h1>

                <p class="font-sans text-4xl font-medium italic leading-tight text-rispetto-accent sm:text-5xl lg:text-6xl">
                    vaših vrata
                </p>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Želimo da uživate u našim jelima gde god da se nalazite, zato smo organizovali dostavu kroz saradnju sa pouzdanim partnerom.
                </p>
            </div>

            <div class="relative mx-auto aspect-square w-full max-w-[20rem] sm:max-w-[24rem] lg:max-w-[28rem]">
                @foreach ($heroImages as $image)
                    <div @class([
                        'absolute overflow-hidden rounded-full border-4 border-rispetto-bg bg-rispetto-surface shadow-xl',
                        $image['size'],
                        $image['position'],
                    ])>
                        @if (file_exists(public_path($image['src'])))
                            <div class="size-full [&_img]:size-full [&_img]:object-cover [&_picture]:block [&_picture]:size-full">
                                <x-site.optimized-image
                                    :src="$image['src']"
                                    :alt="$image['alt']"
                                    class="size-full object-cover"
                                    :width="320"
                                    :height="320"
                                    sizes="(max-width: 1024px) 40vw, 200px"
                                />
                            </div>
                        @else
                            <div class="flex size-full items-center justify-center p-4 text-center">
                                <p class="text-[0.625rem] leading-snug text-rispetto-muted sm:text-xs">
                                    {{ basename($image['src']) }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </x-site.container>
</x-site.section>
