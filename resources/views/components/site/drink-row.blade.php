@props([
    'item',
])

@php
    /** @var \App\Models\Drink $item */
@endphp

<article {{ $attributes->class(['flex flex-col gap-4 border-b border-rispetto-border py-5 last:border-b-0 sm:flex-row sm:items-start sm:gap-6']) }}>
    @if ($item->image)
        <div class="aspect-square w-full shrink-0 overflow-hidden rounded-xl border border-rispetto-border bg-rispetto-surface sm:w-24">
            <x-site.optimized-image
                :src="$item->image"
                :alt="$item->name"
                class="size-full object-cover"
                :width="192"
                :height="192"
                sizes="96px"
            />
        </div>
    @endif
    <div class="min-w-0 flex-1 space-y-2">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <h3 class="font-display text-lg font-bold text-rispetto-text sm:text-xl">{{ $item->name }}</h3>
            <span class="shrink-0 text-lg font-semibold text-rispetto-accent sm:text-xl">{{ number_format((float) $item->price, 0, ',', '.') }}</span>
        </div>
        @if ($item->description)
            <p class="text-sm leading-relaxed text-rispetto-muted">{{ $item->description }}</p>
        @endif
        @if ($item->contents)
            <p class="text-xs text-rispetto-muted/80 sm:text-sm"><span class="font-medium text-rispetto-text">Napomena:</span> {{ $item->contents }}</p>
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
