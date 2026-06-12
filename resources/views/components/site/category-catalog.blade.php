@props([
    'entries',
    'itemComponent' => 'site.menu-row',
    'emptyMessage' => 'Sadržaj će uskoro biti dostupan.',
])

@php
    /** @var \Illuminate\Support\Collection<int, array{slug: string, name: string, items: \Illuminate\Support\Collection}> $entries */
    $hasEntries = $entries->isNotEmpty();
@endphp

@if ($hasEntries)
    <div
        class="category-catalog"
        data-category-catalog
    >
        <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(12rem,16rem)_1fr] lg:items-start lg:gap-10 xl:gap-14">
            <nav
                class="lg:sticky lg:top-24 lg:self-start"
                aria-label="Kategorije"
            >
                <div
                    class="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0"
                    role="tablist"
                >
                    @foreach ($entries as $index => $entry)
                        <button
                            type="button"
                            class="catalog-tab shrink-0 lg:w-full"
                            role="tab"
                            id="catalog-tab-{{ $entry['slug'] }}"
                            data-category-tab="{{ $entry['slug'] }}"
                            data-active="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="catalog-panel-{{ $entry['slug'] }}"
                        >
                            {{ $entry['name'] }}
                        </button>
                    @endforeach
                </div>
            </nav>

            <div class="min-w-0">
                @foreach ($entries as $index => $entry)
                    <section
                        class="category-panel"
                        role="tabpanel"
                        id="catalog-panel-{{ $entry['slug'] }}"
                        data-category-panel="{{ $entry['slug'] }}"
                        aria-labelledby="catalog-tab-{{ $entry['slug'] }}"
                        @if ($index !== 0) hidden @endif
                    >
                        <header class="mb-6 border-b border-rispetto-border pb-4 sm:mb-8">
                            <h2 class="font-display text-3xl font-bold text-rispetto-text sm:text-4xl">{{ $entry['name'] }}</h2>
                        </header>

                        <div>
                            @foreach ($entry['items'] as $item)
                                <x-dynamic-component :component="$itemComponent" :item="$item" />
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@else
    <p class="site-panel border-dashed p-10 text-center text-rispetto-muted">
        {{ $emptyMessage }}
    </p>
@endif
