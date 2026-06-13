@props([
'item',
])

@php
/** @var \App\Models\MenuItem $item */
@endphp

<article {{ $attributes->class(['site-card']) }}>
    <div class="aspect-[4/3] bg-rispetto-surface sm:aspect-[16/10]">
        @if ($item->image)
        <x-site.optimized-image
            :src="$item->image"
            :alt="$item->name"
            class="size-full object-cover"
            :width="640"
            :height="400"
            sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw" />
        @endif
    </div>
    <div class="space-y-3 p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div class="min-w-0 space-y-1">
                <h3 class="font-display text-xl font-medium text-rispetto-text sm:text-2xl">{{ $item->name }}</h3>
                <x-site.item-weight :weight="$item->weight" />
            </div>
            <x-site.menu-pricing :item="$item" class="shrink-0 font-medium" />
        </div>

        @if ($item->description)
        <p class="text-sm leading-relaxed text-rispetto-muted sm:text-base">{{ $item->description }}</p>
        @endif
        @if ($item->contents)
        <p class="text-xs leading-relaxed text-rispetto-muted/80 sm:text-sm"><span class="font-medium text-rispetto-text">Sastojci:</span> {{ $item->contents }}</p>
        @endif
        @if ($item->tags->isNotEmpty())
        <ul class="flex flex-wrap gap-2">
            @foreach ($item->tags as $tag)
            <li class="rounded-full border border-rispetto-border px-2.5 py-0.5 text-xs font-medium text-rispetto-muted">{{ $tag->name }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</article>
