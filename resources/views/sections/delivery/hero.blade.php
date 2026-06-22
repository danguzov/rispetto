@php
$heroImages = [
[
'src' => 'images/site/dostava-hero-1.jpg',
'alt' => 'Predjelo u restoranu Rispetto',
],
[
'src' => 'images/site/dostava-hero-2.jpg',
'alt' => 'Jelo sa pomfritom iz Rispetta',
'center' => true,
],
[
'src' => 'images/site/dostava-hero-3.jpg',
'alt' => 'Pasta u restoranu Rispetto',
],
];
@endphp

<x-site.section variant="hero" class="overflow-x-clip sm:overflow-visible">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="space-y-5 text-center lg:text-left">
                <h1 class="font-display text-4xl font-medium leading-tight text-rispetto-text sm:text-5xl lg:text-6xl">
                    Dostava hrane pravo do <span class="font-display text-4xl font-medium italic leading-tight text-rispetto-accent sm:text-5xl lg:text-6xl">vaših vrata</span>
                </h1>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Želimo da uživate u našim jelima gde god da se nalazite, zato smo organizovali dostavu kroz saradnju sa pouzdanim partnerom.
                </p>
            </div>

            <div class="delivery-hero-plates">
                @foreach ($heroImages as $image)
                <div @class([ 'delivery-hero-plate-slot' , 'delivery-hero-plate-slot--center'=> $image['center'] ?? false,
                    ])>
                    <div class="delivery-hero-plate">
                        <div class="delivery-hero-plate__spin">
                            @if (file_exists(public_path($image['src'])))
                            <div class="size-full [&_img]:size-full [&_img]:object-cover [&_picture]:block [&_picture]:size-full">
                                <x-site.optimized-image
                                    :src="$image['src']"
                                    :alt="$image['alt']"
                                    class="size-full object-cover"
                                    :width="320"
                                    :height="320"
                                    sizes="(max-width: 639px) 84px, (max-width: 1024px) 28vw, 200px" />
                            </div>
                            @else
                            <div class="flex size-full items-center justify-center p-4 text-center">
                                <p class="text-[0.625rem] leading-snug text-rispetto-muted sm:text-xs">
                                    {{ basename($image['src']) }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </x-site.container>
</x-site.section>