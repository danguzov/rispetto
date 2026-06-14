@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<x-site.section variant="hero">
    <x-site.container narrow class="relative text-center">
        @if ($eyebrow)
            <p class="site-eyebrow mb-3">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-4xl font-medium text-rispetto-text sm:text-5xl">{{ $title }}</h1>
        @if ($description)
            <p class="site-subheading mx-auto mt-4 max-w-2xl">{{ $description }}</p>
        @endif
    </x-site.container>
</x-site.section>
