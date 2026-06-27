@props([
    'item',
])

@php
    /** @var \App\Models\MenuItem $item */
@endphp

<article {{ $attributes->class(['group flex flex-col']) }}>
    <div class="aspect-[4/3] overflow-hidden rounded-xl bg-rispetto-elevated">
        <x-site.optimized-image
            :src="$item->displayImage()"
            :alt="$item->name"
            class="size-full object-cover transition duration-300 group-hover:scale-105"
            :width="480"
            :height="360"
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw"
        />
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

        <x-site.item-weight :weight="$item->weight" />

        @if ($item->description)
            <p class="line-clamp-3 flex-1 text-sm leading-relaxed text-rispetto-muted">
                {{ $item->description }}
            </p>
        @endif

        @if ($item->hasPriceVariants())
            <x-site.menu-pricing :item="$item" compact />
        @elseif ($item->displayPrice())
            <p class="pt-1 text-base font-bold text-rispetto-accent sm:text-lg">
                {{ $item->displayPrice() }} RSD
            </p>
        @endif

        <x-site.add-to-cart-button :item="$item" compact />
    </div>
</article>
