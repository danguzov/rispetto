@php
$iconSvg = 'components.site.icons.calendar-heart';
$iconImage = 'images/site/proslave-rezervacija-icon.svg';

if (! View::exists($iconSvg) && file_exists(public_path('images/site/proslave-rezervacija-icon.png'))) {
$iconImage = 'images/site/proslave-rezervacija-icon.png';
}
@endphp

<x-site.section>
    <x-site.container>
        <div class="rounded-3xl border border-rispetto-border bg-rispetto-elevated px-6 py-10 text-center sm:px-10 sm:py-12 lg:px-14 lg:py-14">
            <div
                class="mx-auto mb-6 flex size-10 items-center justify-center text-rispetto-accent sm:mb-8 lg:size-12"
                data-icon-slot="calendar-heart">
                @includeWhen(View::exists($iconSvg), $iconSvg)

                @unless (View::exists($iconSvg))
                @if (file_exists(public_path($iconImage)))
                <img
                    src="{{ asset($iconImage) }}"
                    alt="Ikona za rezervaciju stola"
                    class="size-10 object-contain lg:size-12"
                    width="48"
                    height="48" />
                @else
                <div class="flex size-full items-center justify-center rounded-2xl border border-dashed border-rispetto-border/80 p-3">
                    <span class="text-xs leading-snug text-rispetto-muted">Ikona</span>
                </div>
                @endif
                @endunless
            </div>

            <h2 class="font-display text-3xl font-medium leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                Rezervišite vaš
                <em class="font-display font-medium italic text-rispetto-accent">sto</em>
            </h2>

            <p class="site-subheading mx-auto mt-4 max-w-lg sm:mt-5">
                Za sve dodatne informacije, planiranje menija i rezervacije termina, stojimo vam na raspolaganju.
            </p>

            <a
                href="{{ config('site.phone_href') }}"
                class="site-btn-primary mt-8 inline-flex min-h-12 gap-2.5 px-8 py-4 text-xs uppercase tracking-[0.2em] sm:mt-10">
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
                Kontakt
            </a>
        </div>
    </x-site.container>
</x-site.section>