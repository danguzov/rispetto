@props([
    'href' => null,
    'centered' => true,
])

@php
    $tag = $href ? 'a' : 'div';
    $logo = config('site.brand_logo', 'images/site/rispetto-logo.svg');
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'group inline-flex shrink-0',
        'items-center justify-center' => $centered,
        'items-center justify-start' => ! $centered,
    ]) }}
>
    @if (file_exists(public_path($logo)))
        <img
            src="{{ asset($logo) }}"
            alt="Rispetto — Italian Restaurant"
            class="h-9 w-auto sm:h-11"
            width="213"
            height="46"
        />
    @else
        <span class="font-display text-2xl font-bold uppercase leading-none tracking-wide text-rispetto-accent sm:text-3xl">
            Rispetto
        </span>
    @endif
</{{ $tag }}>
