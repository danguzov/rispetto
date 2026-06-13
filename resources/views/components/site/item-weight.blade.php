@props([
    'weight',
])

@if (filled($weight))
    <p {{ $attributes->class(['text-xs font-medium uppercase tracking-wide text-rispetto-muted sm:text-sm']) }}>
        {{ $weight }}
    </p>
@endif
