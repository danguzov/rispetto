@props([
    'item',
    'pizzaSizeFilter' => false,
])

@php
    /** @var \App\Models\MenuItem $item */
@endphp

<article {{ $attributes->class(['flex gap-4 border-b border-rispetto-border py-5 last:border-b-0 sm:gap-6']) }}>
    <div class="aspect-square w-20 shrink-0 overflow-hidden rounded-xl bg-rispetto-surface sm:w-24">
        @if ($item->image)
            <x-site.optimized-image
                :src="$item->image"
                :alt="$item->name"
                class="size-full object-cover"
                :width="192"
                :height="192"
                sizes="(max-width: 640px) 5rem, 6rem"
            />
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0 space-y-1">
                <h3 class="font-display text-lg font-medium text-rispetto-text sm:text-xl">{{ $item->name }}</h3>
                <x-site.item-weight :weight="$item->weight" />
            </div>

            <x-site.menu-pricing :item="$item" :pizza-size-filter="$pizzaSizeFilter" class="shrink-0" />
        </div>

        @if ($item->description || $item->contents)
            <p class="mt-1.5 text-sm leading-relaxed text-rispetto-muted sm:text-base">
                @if ($item->description)
                    {{ $item->description }}
                @endif
                @if ($item->description && $item->contents)
                    —
                @endif
                @if ($item->contents)
                    {{ $item->contents }}
                @endif
            </p>
        @endif

        @if ($item->tags->isNotEmpty())
            <ul class="mt-2 flex flex-wrap gap-2">
                @foreach ($item->tags as $tag)
                    <li class="rounded-full border border-rispetto-border px-2.5 py-0.5 text-xs font-medium text-rispetto-muted">{{ $tag->name }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
