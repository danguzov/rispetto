@props([
    'name',
    'slug',
    'tag' => 'h2',
])

@php
    $hoursNote = config('site.menu.category_hours_note.'.$slug);
@endphp

<{{ $tag }} {{ $attributes->class(['font-sans text-3xl font-medium text-rispetto-text sm:text-4xl']) }}>
    {{ $name }}@if ($hoursNote)<span class="font-display font-medium italic text-rispetto-accent"> ({{ $hoursNote }})</span>@endif
</{{ $tag }}>
