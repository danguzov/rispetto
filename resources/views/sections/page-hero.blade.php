@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<x-site.section variant="hero" class="!py-12 sm:!py-16">
    <div class="pointer-events-none absolute inset-0 opacity-15" aria-hidden="true">
        <div class="h-full w-full bg-[radial-gradient(circle_at_top,_#00FF03_0%,_transparent_50%)]"></div>
    </div>

    <x-site.container narrow class="relative text-center">
        @if ($eyebrow)
            <p class="site-eyebrow mb-3">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-4xl font-bold text-rispetto-text sm:text-5xl">{{ $title }}</h1>
        @if ($description)
            <p class="site-subheading mx-auto mt-4 max-w-2xl">{{ $description }}</p>
        @endif
    </x-site.container>
</x-site.section>
