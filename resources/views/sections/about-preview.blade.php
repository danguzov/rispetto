<x-site.section variant="muted">
    <x-site.container>
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border border-rispetto-border bg-rispetto-elevated">
                @if (file_exists(public_path('images/site/about.jpg')))
                    <x-site.optimized-image
                        src="images/site/about.jpg"
                        alt="Tim restorana Rispetto"
                        class="size-full object-cover"
                        :width="1024"
                        :height="768"
                        sizes="(max-width: 1024px) 100vw, 50vw"
                    />
                @else
                    <div class="flex size-full items-center justify-center p-8 text-center text-sm text-rispetto-muted">
                        Slika — O nama
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <p class="site-eyebrow">O nama</p>
                <h2 class="site-heading">Više od običnog ručka</h2>
                <p class="site-subheading">
                    U Rispettu verujemo u kvalitetne sastojke, pažljivu pripremu i gostoprimstvo koje ostaje u sećanju.
                    Svaka poseta je prilika da podelimo ljubav prema hrani i piću.
                </p>
                <a href="{{ route('about') }}" class="site-btn-secondary">
                    Saznajte više
                </a>
            </div>
        </div>
    </x-site.container>
</x-site.section>
