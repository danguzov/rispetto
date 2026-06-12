@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    @class([
        'text-sm font-medium transition',
        'text-rispetto-accent' => $active,
        'text-rispetto-muted hover:text-rispetto-text' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
    {{ $attributes }}
>
    {{ $slot }}
</a>
