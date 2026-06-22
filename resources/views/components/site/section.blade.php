@props([
    'tag' => 'section',
    'variant' => 'default',
    'reveal' => true,
])

@php
    $shouldReveal = $reveal && $variant !== 'hero';
@endphp

<{{ $tag }}
    @if ($shouldReveal) data-site-reveal @endif
    {{ $attributes->class([
        'site-reveal' => $shouldReveal,
        'site-section-padding-hero' => $variant === 'hero',
        'site-section-padding' => $variant !== 'hero',
        'bg-rispetto-bg' => $variant === 'default',
        'bg-rispetto-surface' => $variant === 'muted',
        'bg-rispetto-elevated' => $variant === 'elevated',
        'relative bg-rispetto-bg' => $variant === 'hero',
    ]) }}
>
    {{ $slot }}
</{{ $tag }}>
