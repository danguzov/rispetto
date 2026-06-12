@php
    $image = 'images/site/proslave-vikendom.jpg';
@endphp

<x-site.section class="!py-12 sm:!py-16 lg:!py-20">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            <div class="order-2 space-y-5 text-center lg:order-1 lg:text-left">
                <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Manje i srednje proslave
                </h2>

                <p class="font-sans text-3xl font-medium italic leading-tight text-rispetto-accent sm:text-4xl lg:text-5xl">
                    vikendom
                </p>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Vikendom je prostor posebno živahan, pa preporučujemo da nas kontaktirate na vreme radi rezervacije stola za manje i srednje proslave. Pripremićemo sve kako biste vi i vaši gosti uživali bez brige.
                </p>
            </div>

            <div class="order-1 overflow-hidden rounded-3xl border border-rispetto-border bg-rispetto-surface lg:order-2 lg:max-h-[35rem] lg:w-full">
                <div class="aspect-[4/5] sm:aspect-[3/4] lg:aspect-[4/5] lg:max-h-[35rem] lg:w-full">
                    @if (file_exists(public_path($image)))
                        <x-site.optimized-image
                            :src="$image"
                            alt="Proslava vikendom u restoranu Rispetto"
                            class="size-full object-cover"
                            :width="800"
                            :height="1000"
                            sizes="(max-width: 1024px) 100vw, 50vw"
                        />
                    @else
                        <div class="flex size-full min-h-[16rem] items-center justify-center p-8 text-center">
                            <p class="text-sm text-rispetto-muted">
                                Dodajte sliku na
                                <code class="text-rispetto-accent">public/images/site/proslave-vikendom.jpg</code>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-site.container>
</x-site.section>
