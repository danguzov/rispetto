@props([
    'icon',
    'title',
    'description',
])

<article {{ $attributes->class(['flex flex-col rounded-2xl bg-rispetto-elevated p-6 sm:p-8']) }}>
    <div
        class="mb-5 flex size-12 items-center justify-center rounded-full bg-rispetto-bg text-rispetto-accent ring-1 ring-rispetto-border"
        data-icon-slot="{{ $icon }}"
        aria-hidden="true"
    >
        @includeWhen(
            View::exists('components.site.icons.'.$icon),
            'components.site.icons.'.$icon
        )
    </div>

    <h2 class="font-display text-xl font-bold text-rispetto-text sm:text-2xl">{{ $title }}</h2>
    <p class="mt-3 text-sm leading-relaxed text-rispetto-muted sm:text-base">{{ $description }}</p>
</article>
