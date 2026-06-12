<x-site.layout title="Jelovnik" description="Pogledajte kompletan jelovnik restorana Rispetto.">
    @include('sections.page-hero', [
        'eyebrow' => 'Jelovnik',
        'title' => 'Ukusi koji se pamte',
        'description' => 'Sezonski sastojci, domaći ukusi i pažljivo kreirana jela za svaki apetit.',
    ])

    <x-site.section class="!pt-0">
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
                empty-message="Jelovnik će uskoro biti dostupan."
            />
        </x-site.container>
    </x-site.section>

    @include('sections.cta')
</x-site.layout>
