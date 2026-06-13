@php
$heroImage = 'images/site/proslave-hero.jpg';
@endphp

<x-site.section variant="hero">
    <x-site.container class="text-center">
        <h1 class="font-display text-4xl font-medium leading-tight text-rispetto-text sm:text-5xl lg:text-6xl">
            Proslave i
            <span class="font-display font-medium text-rispetto-accent">rezervacije</span>
        </h1>

        <p class="site-subheading mx-auto mt-4 max-w-2xl sm:mt-6">
            Bilo da planirate rođendan, okupljanje sa prijateljima ili neku posebnu priliku, Rispetto je mesto gde se proslave pretvaraju u lepe uspomene.
        </p>

        <div class="mt-10 overflow-hidden rounded-3xl border border-rispetto-border bg-rispetto-surface sm:mt-12 lg:mt-14">
            <div class="aspect-[16/10] sm:aspect-[2/1]">
                @if (file_exists(public_path($heroImage)))
                <x-site.optimized-image
                    :src="$heroImage"
                    alt="Porodična proslava u restoranu Rispetto"
                    class="size-full object-cover"
                    :width="1400"
                    :height="700"
                    :priority="true"
                    sizes="(max-width: 1400px) 100vw, 1400px" />
                @else
                <div class="flex size-full min-h-[14rem] items-center justify-center p-8 text-center">
                    <p class="text-sm text-rispetto-muted">
                        Dodajte sliku na
                        <code class="text-rispetto-accent">public/images/site/proslave-hero.jpg</code>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </x-site.container>
</x-site.section>