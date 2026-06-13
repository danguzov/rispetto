<x-site.layout>
    @include('sections.shared.page-hero', [
        'title' => 'Karta pića',
        'description' => 'Od domaćih vina do osvežavajućih napitaka — pronađite savršen par uz jelo.',
    ])

    <x-site.section>
        <x-site.container>
            @php
                $catalogEntries = $categories
                    ->filter(fn ($category) => $category->drinks->isNotEmpty())
                    ->map(fn ($category) => [
                        'slug' => $category->slug,
                        'name' => $category->name,
                        'items' => $category->drinks,
                    ])
                    ->values();

                if ($uncategorizedDrinks->isNotEmpty()) {
                    $catalogEntries->push([
                        'slug' => 'ostala-pica',
                        'name' => 'Ostala pića',
                        'items' => $uncategorizedDrinks,
                    ]);
                }
            @endphp

            <x-site.category-catalog
                :entries="$catalogEntries"
                item-component="site.drink-row"
                empty-message="Karta pića će uskoro biti dostupna." />
        </x-site.container>
    </x-site.section>

    @include('sections.shared.visit')
</x-site.layout>
