@props([
    'entries',
    'itemComponent' => 'site.menu-row',
    'emptyMessage' => 'Sadržaj će uskoro biti dostupan.',
])

@php
    /** @var \Illuminate\Support\Collection<int, array{slug: string, name: string, items: \Illuminate\Support\Collection}> $entries */
    $hasEntries = $entries->isNotEmpty();
    $firstEntry = $entries->first();
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
                {{-- Mobilni / tablet: dropdown --}}
                <div class="category-catalog__dropdown lg:hidden" data-category-dropdown>
                    <button
                        type="button"
                        class="category-catalog__trigger"
                        data-category-dropdown-toggle
                        aria-expanded="false"
                        aria-haspopup="listbox"
                    >
                        <span data-category-dropdown-label>{{ $firstEntry['name'] }}</span>
                        <svg class="category-catalog__chevron size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <ul
                        class="category-catalog__menu"
                        data-category-dropdown-menu
                        role="listbox"
                        hidden
                    >
                        @foreach ($entries as $index => $entry)
                            <li role="presentation">
                                <button
                                    type="button"
                                    class="category-catalog__option catalog-tab w-full"
                                    role="option"
                                    data-category-tab="{{ $entry['slug'] }}"
                                    data-active="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                >
                                    {{ $entry['name'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Desktop: vertikalni tabovi --}}
                <div
                    class="hidden gap-2 lg:flex lg:flex-col"
                    role="tablist"
                >
                    @foreach ($entries as $index => $entry)
                        <button
                            type="button"
                            class="catalog-tab w-full"
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
