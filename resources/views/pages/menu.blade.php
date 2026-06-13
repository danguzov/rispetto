<x-site.layout>
    @include('sections.shared.page-hero', [
        'title' => 'Ukusi koji se pamte',
        'description' => 'Sezonski sastojci, domaći ukusi i pažljivo kreirana jela za svaki apetit.',
    ])

    <x-site.section>
        <x-site.container>
            @php
                $catalogEntries = $categories
                    ->filter(fn ($category) => $category->menuItems->isNotEmpty())
                    ->map(fn ($category) => [
                        'slug' => $category->slug,
                        'name' => $category->name,
                        'items' => $category->menuItems,
                    ])
                    ->values();
            @endphp

            <x-site.category-catalog
                :entries="$catalogEntries"
                item-component="site.menu-row"
                empty-message="Jelovnik će uskoro biti dostupan." />
        </x-site.container>
    </x-site.section>

    @include('sections.shared.visit')
</x-site.layout>
