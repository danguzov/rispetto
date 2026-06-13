<x-site.section>
    <x-site.container>
        <div class="grid items-center gap-8 rounded-3xl border border-rispetto-border bg-rispetto-elevated p-6 sm:gap-10 sm:p-8 lg:grid-cols-2 lg:gap-12 lg:p-10 xl:p-12">
            {{-- Leva kolona --}}
            <div class="space-y-6 text-center lg:text-left">
                <h2 class="font-display text-3xl font-medium leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Slavite
                    <span class="text-rispetto-accent">sa nama</span>
                </h2>

                <p class="site-subheading mx-auto max-w-lg lg:mx-0">
                    Rispetto čini svaki događaj posebnim. Vrhunska usluga i prostor koji ćete osećati kao svoj. Vaše je samo da uživate, ostalo prepustite nama.
                </p>

                <div class="flex justify-center lg:justify-start">
                    <a
                        href="{{ route('celebrations') }}"
                        class="site-btn-primary px-8 py-4 text-xs uppercase tracking-[0.2em]">
                        Saznaj više
                    </a>
                </div>
            </div>

            {{-- Desna kolona — slika --}}
            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-rispetto-surface sm:aspect-[5/4] lg:aspect-[4/3]">
                @if (file_exists(public_path('images/site/celebrations-promo.jpg')))
                <x-site.optimized-image
                    src="images/site/celebrations-promo.jpg"
                    alt="Proslave i ručavanje u restoranu Rispetto"
                    class="size-full object-cover"
                    :width="960"
                    :height="720"
                    sizes="(max-width: 1024px) 100vw, 50vw" />
                @else
                <div class="flex size-full min-h-[14rem] items-center justify-center p-8 text-center">
                    <p class="text-sm text-rispetto-muted">
                        Dodajte sliku na
                        <code class="text-rispetto-accent">public/images/site/celebrations-promo.jpg</code>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </x-site.container>
</x-site.section>