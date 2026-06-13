@php
$image = 'images/site/proslave-radni-dani.jpg';
@endphp

<x-site.section>
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="order-2 overflow-hidden rounded-3xl border border-rispetto-border bg-rispetto-surface lg:order-1 lg:max-h-[35rem] lg:w-full">
                <div class="aspect-[4/5] sm:aspect-[3/4] lg:aspect-[4/5] lg:max-h-[35rem] lg:w-full">
                    @if (file_exists(public_path($image)))
                    <x-site.optimized-image
                        :src="$image"
                        alt="Gost u restoranu Rispetto pregleda jelovnik"
                        class="size-full object-cover"
                        :width="800"
                        :height="1000"
                        sizes="(max-width: 1024px) 100vw, 50vw" />
                    @else
                    <div class="flex size-full min-h-[16rem] items-center justify-center p-8 text-center">
                        <p class="text-sm text-rispetto-muted">
                            Dodajte sliku na
                            <code class="text-rispetto-accent">public/images/site/proslave-radni-dani.jpg</code>
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="order-1 space-y-5 text-center lg:order-2 lg:text-left">
                <h2 class="font-display text-3xl font-medium leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Manje i srednje proslave
                </h2>

                <p class="font-display text-3xl font-medium italic leading-tight text-rispetto-accent sm:text-4xl lg:text-5xl">
                    radnim danima
                </p>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Tokom radnih dana omogućavamo rezervacije stolova za manje i srednje proslave. Dovoljno je da nas kontaktirate na vreme, a mi ćemo se pobrinuti da vas dočeka spreman sto i prijatan ambijent.
                </p>
            </div>
        </div>
    </x-site.container>
</x-site.section>