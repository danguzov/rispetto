<x-site.layout
    title="Stranica nije pronađena"
    description="Tražena stranica ne postoji ili je premeštena. Vratite se na početnu ili pogledajte jelovnik restorana Rispetto u Batajnici."
    robots="noindex, follow"
>
    <x-site.section variant="hero" :reveal="false">
        <x-site.container narrow class="text-center">
            <p class="site-eyebrow mb-4">Greška 404</p>

            <p
                class="font-display text-[5rem] font-medium leading-none text-rispetto-accent sm:text-[6.5rem] lg:text-[7.5rem]"
                aria-hidden="true"
            >
                404
            </p>

            <h1 class="mt-6 font-display text-3xl font-medium leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Ova stranica nije na
                <em class="font-display font-medium italic text-rispetto-accent">jelovniku</em>
            </h1>

            <p class="site-subheading mx-auto mt-4 max-w-xl">
                Možda je link zastareo ili ste zalutali. Kod nas u kuhinji sve je na svom mestu — vratite se i nastavite uživanje.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('home') }}" class="site-btn-primary min-h-12 px-8 py-4 text-xs uppercase tracking-[0.2em]">
                    Na početnu
                </a>
                <a href="{{ route('menu') }}" class="site-btn-secondary min-h-12 px-8 py-4 text-xs uppercase tracking-[0.2em]">
                    Jelovnik
                </a>
            </div>
        </x-site.container>
    </x-site.section>

    <x-site.section :reveal="false">
        <x-site.container narrow>
            <div class="site-panel mx-auto max-w-2xl p-6 text-center sm:p-8">
                <h2 class="font-sans text-sm font-semibold uppercase tracking-[0.2em] text-rispetto-muted">
                    Možda tražite
                </h2>

                <ul class="mt-6 flex flex-wrap justify-center gap-3">
                    @foreach (config('site.footer_navigation') as $link)
                        <li>
                            <a
                                href="{{ route($link['route']) }}"
                                class="inline-flex min-h-12 items-center rounded-full border border-rispetto-border px-5 py-3 text-sm font-medium text-rispetto-text transition hover:border-rispetto-accent/50 hover:text-rispetto-accent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rispetto-accent"
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-8 text-sm text-rispetto-muted">
                    Treba vam pomoć?
                    <a href="{{ config('site.phone_href') }}" class="font-medium text-rispetto-accent transition hover:text-rispetto-text">
                        {{ config('site.phone') }}
                    </a>
                </p>
            </div>
        </x-site.container>
    </x-site.section>
</x-site.layout>
