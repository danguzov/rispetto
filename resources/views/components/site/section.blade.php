@props([
    'tag' => 'section',
    'variant' => 'default',
])

<{{ $tag }} {{ $attributes->class([
    'py-12 sm:py-16 lg:py-20',
    'bg-rispetto-bg' => $variant === 'default',
    'bg-rispetto-surface' => $variant === 'muted',
    'bg-rispetto-elevated' => $variant === 'elevated',
    'relative overflow-hidden bg-rispetto-bg' => $variant === 'hero',
]) }}>
    {{ $slot }}
</{{ $tag }}>
