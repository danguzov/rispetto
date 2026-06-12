<x-site.section>
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16 xl:gap-20">
            {{-- Slika levo --}}
            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-rispetto-elevated sm:aspect-[5/4] lg:aspect-auto lg:min-h-[28rem]">
                @if (file_exists(public_path('images/site/drinks-promo.jpg')))
                    <x-site.optimized-image
                        src="images/site/drinks-promo.jpg"
                        alt="Karta pića — vino i osveženja u restoranu Rispetto"
                        class="size-full object-cover"
                        :width="960"
                        :height="800"
                        sizes="(max-width: 1024px) 100vw, 50vw"
                    />
                @else
                    <div class="flex size-full min-h-[16rem] items-center justify-center p-8 text-center">
                        <p class="text-sm text-rispetto-muted">
                            Dodajte sliku na
                            <code class="text-rispetto-accent">public/images/site/drinks-promo.jpg</code>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Tekst desno --}}
            <div class="space-y-6 text-center lg:text-left">
                <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Pronađite svom tanjiru
                    <em class="mt-1 block font-display font-bold italic text-rispetto-accent">dobar par</em>
                </h2>

                <p class="site-subheading mx-auto max-w-lg lg:mx-0">
                    Uz vrhunski obrok ide i pravo osveženje. Istražite našu ponudu pića za kompletan ugođaj.
                </p>

                <div class="flex justify-center lg:justify-start">
                    <a
                        href="{{ route('drinks') }}"
                        class="site-btn-primary px-8 py-4 text-xs uppercase tracking-[0.2em]"
                    >
                        Karta pića
                    </a>
                </div>
            </div>
        </div>
    </x-site.container>
</x-site.section>
