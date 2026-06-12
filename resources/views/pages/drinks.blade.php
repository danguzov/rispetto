<x-site.layout title="Karta pića" description="Karta pića restorana Rispetto.">
    @include('sections.page-hero', [
        'eyebrow' => 'Piće',
        'title' => 'Karta pića',
        'description' => 'Od domaćih vina do osvežavajućih napitaka — pronađite savršen par uz jelo.',
    ])

    <x-site.section class="!pt-0">
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
                empty-message="Karta pića će uskoro biti dostupna."
            />
        </x-site.container>
    </x-site.section>

    @include('sections.cta')
</x-site.layout>
