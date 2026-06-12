@php
    $features = config('site.features');
@endphp

<x-site.section class="!pt-0 sm:!pt-4">
    <x-site.container>
        <div class="grid gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
            @foreach ($features as $feature)
                <x-site.feature-card
                    :icon="$feature['icon']"
                    :title="$feature['title']"
                    :description="$feature['description']"
                />
            @endforeach
        </div>
    </x-site.container>
</x-site.section>
