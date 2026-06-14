@props([
    'entries',
    'itemComponent' => 'site.menu-row',
    'emptyMessage' => 'Sadržaj će uskoro biti dostupan.',
])

@php
    /** @var \Illuminate\Support\Collection<int, array{slug: string, name: string, items: \Illuminate\Support\Collection}> $entries */
    $hasEntries = $entries->isNotEmpty();
    $firstEntry = $entries->first();
    $pizzaCategorySlug = config('site.menu.pizza_category_slug');
    $pizzaSizes = config('site.menu.pizza_sizes', []);
    $defaultPizzaSize = $pizzaSizes[0]['key'] ?? '25';
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
                    @php
                        $isPizzaCategory = $entry['slug'] === $pizzaCategorySlug;
                    @endphp
                    <section
                        class="category-panel"
                        role="tabpanel"
                        id="catalog-panel-{{ $entry['slug'] }}"
                        data-category-panel="{{ $entry['slug'] }}"
                        aria-labelledby="catalog-tab-{{ $entry['slug'] }}"
                        @if ($isPizzaCategory)
                            data-pizza-size-panel="true"
                            data-active-pizza-size="{{ $defaultPizzaSize }}"
                        @endif
                        @if ($index !== 0) hidden @endif
                    >
                        <header class="category-panel__header mb-6 border-b border-rispetto-border pb-4 sm:mb-8">
                            <h2 class="font-display text-3xl font-medium text-rispetto-text sm:text-4xl">{{ $entry['name'] }}</h2>

                            @if ($isPizzaCategory)
                                <div
                                    class="flex gap-2 overflow-x-auto pb-0.5"
                                    role="tablist"
                                    aria-label="Veličine pice"
                                    data-pizza-size-tabs
                                >
                                    @foreach ($pizzaSizes as $sizeIndex => $size)
                                        <button
                                            type="button"
                                            class="catalog-tab shrink-0"
                                            role="tab"
                                            data-pizza-size-tab="{{ $size['key'] }}"
                                            data-active="{{ $sizeIndex === 0 ? 'true' : 'false' }}"
                                            aria-selected="{{ $sizeIndex === 0 ? 'true' : 'false' }}"
                                        >
                                            {{ $size['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </header>

                        @if ($isPizzaCategory)
                            <p
                                class="site-panel mb-6 hidden border-dashed p-8 text-center text-sm text-rispetto-muted sm:text-base"
                                data-pizza-size-empty
                                hidden
                            >
                                Nema pica u ovoj veličini.
                            </p>
                        @endif

                        <div @if ($isPizzaCategory) data-pizza-items @endif>
                            @foreach ($entry['items'] as $item)
                                @if ($isPizzaCategory && $item->hasPriceVariants())
                                    <div
                                        data-menu-item
                                        data-sizes="{{ implode(',', $item->availableSizeKeys()) }}"
                                        @foreach ($item->availableSizeKeys() as $sizeKey)
                                            data-size-{{ $sizeKey }}="true"
                                        @endforeach
                                    >
                                        <x-dynamic-component
                                            :component="$itemComponent"
                                            :item="$item"
                                            :pizza-size-filter="true"
                                        />
                                    </div>
                                @else
                                    <x-dynamic-component
                                        :component="$itemComponent"
                                        :item="$item"
                                        :pizza-size-filter="$isPizzaCategory && $item->hasPriceVariants()"
                                    />
                                @endif
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
