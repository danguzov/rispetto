@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'text-sm font-medium transition',
        'text-rispetto-accent' => $active,
        'text-rispetto-muted hover:text-rispetto-accent' => ! $active,
    ]) }}
    @if ($active) aria-current="page" @endif
>
    {{ $slot }}
</a>
