@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\MenuItem> $items */
    $items = $items ?? collect();
@endphp

<x-site.section>
    <x-site.container>
        <div class="mb-10 flex flex-col gap-8 lg:mb-12 lg:flex-row lg:items-end lg:justify-between lg:gap-12">
            <div class="max-w-2xl space-y-4">
                <h2 class="font-display text-3xl font-bold leading-tight text-rispetto-text sm:text-4xl lg:text-5xl">
                    Izdvajamo iz
                    <em class="font-display font-bold italic text-rispetto-accent">menija</em>
                </h2>
                <p class="site-subheading">
                    Pažljivo odabrani specijaliteti koji predstavljaju srž naše kulinarske vizije. Spoj autentičnih namirnica i modernog pristupa.
                </p>
            </div>

            <a
                href="{{ route('menu') }}"
                class="site-btn-primary shrink-0 self-start px-8 py-4 text-xs uppercase tracking-[0.2em] lg:self-end"
            >
                Pogledaj jelovnik
            </a>
        </div>

        @if ($items->isEmpty())
            <p class="site-panel border-dashed p-8 text-center text-sm text-rispetto-muted">
                Nema istaknutih jela. Označite do 4 stavke kao „Istaknuto” u admin panelu.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-4">
                @foreach ($items as $item)
                    <x-site.featured-menu-card :item="$item" />
                @endforeach
            </div>
        @endif
    </x-site.container>
</x-site.section>
