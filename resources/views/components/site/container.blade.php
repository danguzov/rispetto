@props([
    'tag' => 'div',
    'narrow' => false,
])

<{{ $tag }} {{ $attributes->class([
    'mx-auto w-full px-4 sm:px-6 lg:px-8',
    'max-w-[1400px]' => ! $narrow,
    'max-w-4xl' => $narrow,
]) }}>
    {{ $slot }}
</{{ $tag }}>
