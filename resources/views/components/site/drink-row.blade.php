@props([
    'item',
])

@php
    /** @var \App\Models\Drink $item */
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
                <h3 class="font-sans text-lg font-medium text-rispetto-text sm:text-xl">{{ $item->name }}</h3>
                <x-site.item-weight :weight="$item->weight" />
            </div>

            @if ($item->price !== null)
                <span class="shrink-0 text-lg font-medium text-rispetto-accent sm:text-xl">{{ number_format((float) $item->price, 0, ',', '.') }} RSD</span>
            @endif
        </div>

        @if ($item->description)
            <p class="mt-1.5 text-sm leading-relaxed text-rispetto-muted sm:text-base">{{ $item->description }}</p>
        @endif

        @if ($item->contents)
            <p class="mt-1.5 text-xs text-rispetto-muted/80 sm:text-sm">
                <span class="font-medium text-rispetto-text">Napomena:</span> {{ $item->contents }}
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
