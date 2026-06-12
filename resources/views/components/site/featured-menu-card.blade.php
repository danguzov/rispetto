@props([
    'item',
])

@php
    /** @var \App\Models\MenuItem $item */
@endphp

<article {{ $attributes->class(['group flex flex-col']) }}>
    <div class="aspect-[4/3] overflow-hidden rounded-xl bg-rispetto-elevated">
        @if ($item->image)
            <x-site.optimized-image
                :src="$item->image"
                :alt="$item->name"
                class="size-full object-cover transition duration-300 group-hover:scale-105"
                :width="480"
                :height="360"
                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
            />
        @else
            <div class="flex size-full items-center justify-center text-xs text-rispetto-muted">Bez slike</div>
        @endif
    </div>

    <div class="mt-4 flex flex-1 flex-col space-y-2">
        @if ($item->category)
            <p class="text-xs font-medium uppercase tracking-[0.15em] text-rispetto-text">
                {{ $item->category->name }}
            </p>
        @endif

        <h3 class="text-base font-medium leading-snug text-rispetto-text sm:text-lg">
            {{ $item->name }}
        </h3>

        <p class="line-clamp-3 flex-1 text-sm leading-relaxed text-rispetto-muted">
            {{ $item->description }}
        </p>

        <p class="pt-1 text-base font-bold text-rispetto-accent sm:text-lg">
            {{ number_format((float) $item->price, 0, ',', '.') }}
        </p>
    </div>
</article>
