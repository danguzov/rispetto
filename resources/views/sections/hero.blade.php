<x-site.section variant="hero" class="!pb-16 !pt-8 sm:!pb-20 sm:!pt-10 lg:!pb-24">
    <x-site.container class="relative">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-12 xl:gap-16">
            {{-- Leva kolona --}}
            <div class="order-2 space-y-6 text-center lg:order-1 lg:text-left">
                <div class="inline-flex items-center gap-2 rounded-full border border-rispetto-border bg-rispetto-surface px-4 py-2">
                    <span class="size-2 shrink-0 rounded-full bg-rispetto-accent" aria-hidden="true"></span>
                    <span class="text-sm text-rispetto-text">Autentična italijanska kuhinja</span>
                </div>

                <h1 class="font-display text-4xl font-bold leading-[1.1] text-rispetto-text sm:text-5xl lg:text-[3.5rem] xl:text-6xl">
                    Umetnost
                    <em class="font-display font-bold italic text-rispetto-accent">italijanske</em>
                    kuhinje na vašem tanjiru
                </h1>

                <p class="site-subheading mx-auto max-w-xl lg:mx-0">
                    Minimalistički prostor, pažljivo birane namirnice i jela koja spajaju tradiciju sa savremenim pristupom serviranju.
                </p>

                <div class="flex justify-center lg:justify-start">
                    <a href="{{ route('menu') }}" class="site-btn-primary px-8 py-4 text-xs uppercase tracking-[0.2em]">
                        Istraži jelovnik
                    </a>
                </div>
            </div>

            {{-- Desna kolona --}}
            <div class="relative order-1 mx-auto w-full max-w-md lg:order-2 lg:mx-0 lg:max-w-none">
                <div class="relative aspect-[4/5] overflow-hidden rounded-3xl border border-rispetto-border sm:aspect-[3/4]">
                    @if (file_exists(public_path('images/site/hero.jpg')))
                        <x-site.optimized-image
                            src="images/site/hero.jpg"
                            alt="Italijansko jelo u restoranu Rispetto"
                            class="size-full object-cover"
                            :width="800"
                            :height="1000"
                            :priority="true"
                            sizes="(max-width: 1024px) 90vw, 50vw"
                        />
                    @else
                        <div class="flex size-full items-center justify-center bg-rispetto-elevated p-8 text-center">
                            <p class="text-sm text-rispetto-muted">
                                Dodajte sliku na
                                <code class="text-rispetto-accent">public/images/site/hero.jpg</code>
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Testimonial kartica --}}
                <div class="relative z-10 mx-4 -mt-16 rounded-2xl border border-rispetto-border bg-rispetto-elevated/95 p-5 shadow-2xl backdrop-blur-sm sm:absolute sm:bottom-6 sm:left-0 sm:mx-0 sm:max-w-[85%] sm:-translate-x-4 lg:max-w-[90%] lg:-translate-x-8">
                    <h2 class="font-display text-lg font-bold leading-snug text-rispetto-text sm:text-xl">
                        Vrhunska hrana i ukusi Italije u Batajnici
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-rispetto-muted">
                        Restoran u kome zaista možete osetiti pažnju posvećenu gostu i ljubav prema kuvanju. Toplo preporučujem svima koji žele gastronomsko iskustvo!
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <div class="flex -space-x-2" aria-hidden="true">
                            @foreach (['bg-amber-600', 'bg-emerald-600', 'bg-sky-600'] as $color)
                                <span class="inline-flex size-8 items-center justify-center rounded-full border-2 border-rispetto-elevated {{ $color }} text-xs font-medium text-white">
                                    ★
                                </span>
                            @endforeach
                        </div>
                        <span class="text-lg font-semibold text-rispetto-text">4,5</span>
                        <div class="flex items-center gap-1.5">
                            <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <div class="flex text-amber-400" aria-label="Ocena 5 od 5">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="size-4 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-site.container>
</x-site.section>
