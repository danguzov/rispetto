@props([
    'href' => null,
    'centered' => true,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'group inline-flex flex-col',
        'items-center text-center' => $centered,
        'items-start text-left' => ! $centered,
    ]) }}
>
    <span class="text-[0.6rem] font-medium uppercase tracking-[0.35em] text-rispetto-text sm:text-[0.65rem]">
        Italian Restaurant
    </span>
    <span class="mt-1 flex items-center gap-2">
        <span class="font-display text-2xl font-bold uppercase leading-none tracking-wide text-rispetto-accent sm:text-3xl">
            Rispetto
        </span>
        <svg class="size-8 shrink-0 sm:size-9" viewBox="0 0 36 36" fill="none" aria-hidden="true">
            <ellipse cx="18" cy="24" rx="12" ry="5" fill="#c41e3a" opacity="0.9"/>
            <path d="M10 20c2-6 6-10 8-10s6 4 8 10" stroke="#f5f5f5" stroke-width="1.5" fill="none"/>
            <path d="M26 14c1-3 2-5 3-6M28 12c1-2 2-3 3-4" stroke="#a3a3a3" stroke-width="1" stroke-linecap="round"/>
            <rect x="24" y="18" width="3" height="10" rx="1" fill="#d4d4d4"/>
            <ellipse cx="18" cy="20" rx="10" ry="4" fill="#8b1a2d"/>
        </svg>
    </span>
</{{ $tag }}>
