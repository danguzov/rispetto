@props([
    'tag' => 'section',
    'variant' => 'default',
    'reveal' => true,
])

<{{ $tag }}
    @if ($reveal) data-site-reveal @endif
    {{ $attributes->class([
        'site-reveal' => $reveal,
        'site-section-padding',
        'bg-rispetto-bg' => $variant === 'default',
        'bg-rispetto-surface' => $variant === 'muted',
        'bg-rispetto-elevated' => $variant === 'elevated',
        'relative bg-rispetto-bg' => $variant === 'hero',
    ]) }}
>
    {{ $slot }}
</{{ $tag }}>
